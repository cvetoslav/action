<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../db/brain.php');
require_once(__DIR__ . '/problem.php');
require_once(__DIR__ . '/widgets.php');
require_once(__DIR__ . '/grader.php');

class Submit {
    public $id = -1;
    public $submitted = '';
    public $graded = 0.0;
    public $userId = -1;
    public $userName = '';
    public $problemId = -1;
    public $problemName = '';
    public $source = '';
    public $language = '';
    public $results = array();
    public $exec_time = array();
    public $exec_memory = array();
    public $progress = 0;
    public $status = -1;
    public $message = '';

    public static function newSubmit($user, $problemId, $language, $source) {
        $brain = new Brain();
        $submit = new Submit();

        $problem = Problem::get($problemId);

        // The submission doesn't have an ID until it is inserted in the database
        $submit->id = -1;

        // Mark the time of the submission
        $submit->submitted = date('Y-m-d H:i:s');
        $submit->graded = 0.0;

        // Populate the remaining submission info
        $submit->userId = $user->id;
        $submit->userName = $user->username;
        $submit->problemId = $problem->id;
        $submit->problemName = $problem->name;
        $submit->source = $source;
        $submit->language = $language;
        $submit->results = array();
        $submit->exec_time = array();
        $submit->exec_memory = array();
        $numTests = count($brain->getProblemTests($problem->id));
        for ($i = 0; $i < $numTests; $i = $i + 1) {
            $submit->results[$i] = $GLOBALS['STATUS_WAITING'];
            $submit->exec_time[$i] = 0;
            $submit->exec_memory[$i] = 0;
        }
        $submit->progress = 0;
        $submit->status = $GLOBALS['STATUS_WAITING'];
        $submit->message = '';
        return $submit;
    }

    public function reset() {
        $brain = new Brain();
        $this->results = array();
        $this->exec_time = array();
        $this->exec_memory = array();
        $numTests = count($brain->getProblemTests($this->problemId));
        for ($i = 0; $i < $numTests; $i = $i + 1) {
            $this->results[$i] = $GLOBALS['STATUS_WAITING'];
            $this->exec_time[$i] = 0;
            $this->exec_memory[$i] = 0;
        }
        $this->progress = 0;
        $this->status = $GLOBALS['STATUS_WAITING'];
        $this->message = '';
        $brain->updateSubmit($this);
        $brain->erasePending($this->id);
        $brain->eraseLatest($this->id);
    }

    public function write() {
        $brain = new Brain();
        $this->id = $brain->addSubmit($this);
        $brain->addSource($this);
        return $this->id >= 0;
    }

    public function send() {
        // Record the request in the submission queue
        $brain = new Brain();
        $brain->addPending($this);
        $problem = Problem::get($this->problemId);

        $updateEndpoint = $GLOBALS['WEB_ENDPOINT_UPDATE'];
        $testsEndpoint = sprintf($GLOBALS['WEB_ENDPOINT_TESTS'], $problem->folder);
        $tests = $brain->getProblemTests($this->problemId);

        // Remove unnecessary data
        for ($i = 0; $i < count($tests); $i = $i + 1) {
            unset($tests[$i]['id']);
            unset($tests[$i]['problem']);
            unset($tests[$i]['score']);
        }

        // Convert strings to numbers where needed
        for ($i = 0; $i < count($tests); $i = $i + 1) {
            $tests[$i]['position'] = intval($tests[$i]['position']);
        }

        // Compile all the data, required by the grader to evaluate the solution
        $data = array(
            'id' => $this->id,
            'source' => $this->source,
            'language' => $this->language,
            'checker' => $problem->checker,
            'tester' => $problem->tester,
            'timeLimit' => $problem->timeLimit,
            'memoryLimit' => $problem->memoryLimit,
            'tests' => $tests,
            'testsEndpoint' => $testsEndpoint,
            'updateEndpoint' => $updateEndpoint
        );

        $grader = new Grader();
        return $grader->evaluate($data);
    }

    private static function instanceFromArray($info, $source) {
        $submit = new Submit();
        $submit->id = intval(getValue($info, 'id'));
        $submit->submitted = getValue($info, 'submitted');
        $submit->graded = floatval(getValue($info, 'graded'));
        $submit->userId = intval(getValue($info, 'userId'));
        $submit->userName = getValue($info, 'userName');
        $submit->problemId = intval(getValue($info, 'problemId'));
        $submit->problemName = getValue($info, 'problemName');
        $submit->language = getValue($info, 'language');
        $submit->source = getValue($source, 'source');
        $submit->results = explode(',', getValue($info, 'results'));
        $submit->exec_time = explode(',', getValue($info, 'exec_time'));
        $submit->exec_memory = explode(',', getValue($info, 'exec_memory'));
        $submit->status = getValue($info, 'status');
        $submit->message = getValue($info, 'message');
        return $submit;
    }

    public static function get($id) {
        $brain = new Brain();
        try {
            $info = $brain->getSubmit($id);
            $source = $brain->getSource($id);
            if ($info == null || $source == null) {
                error_log('Could not get submit or source with id ' . $id . '!');
                return null;
            }
            return Submit::instanceFromArray($info, $source);
        } catch (Exception $ex) {
            error_log('Could not get submit ' . $id . '. Exception: ' . $ex->getMessage());
        }
        return null;
    }

    public static function getUserSubmits($userId, $problemId) {
        $brain = new Brain();
        $submitMaps = $brain->getUserSubmits($userId, $problemId);
        $sourcesMaps = $brain->getUserSources($userId, $problemId);
        $submits = array();
        // This can be done better than O(N^2) if need be.
        foreach ($submitMaps as $submitMap) {
            foreach ($sourcesMaps as $sourceMap) {
                if ($submitMap['id'] == $sourceMap['submitId']) {
                    array_push($submits, Submit::instanceFromArray($submitMap, $sourceMap));
                    break;
                }
            }
        }
        return $submits;
    }

    public function calcStatus() {
        // Handle the case where there are no results (i.e. no tests)
        // This is an exceptional scenario and shouldn't happen, so return INTERNAL_ERROR
        if (count($this->results) == 0)
            return $GLOBALS['STATUS_INTERNAL_ERROR'];

        $passedTests = array_filter($this->results, function($el) {return is_numeric($el);});
        // If all results are numeric, then the problem has been accepted
        if (count($passedTests) == count($this->results))
            return $GLOBALS['STATUS_ACCEPTED'];

        $failedTests = array_filter($this->results, function($el) {return !is_numeric($el) && strlen($el) == 2;});
        // If all tests are processed (either numeric or two-letter), then the grading has been completed
        if (count($passedTests) + count($failedTests) == count($this->results))
            return array_values($failedTests)[0]; // Return the status code of the first error

        // If none of the tests are processed (either numeric or two-letter), return the status of the first test
        if (count($passedTests) + count($failedTests) == 0)
            return $this->results[0];

        // If none of the above, the solution is still being graded
        return $GLOBALS['STATUS_TESTING'];
    }

    public function calcScores() {
        $brain = new Brain();
        $tests = $brain->getProblemTests($this->problemId);

        if (count($this->results) != count($tests)) {
            error_log('Number of tests of problem ' . $this->problemId . ' differs from results in submission ' . $this->id . '!');
        }

        $scores = [];
        $maxScore = 0.0;
        for ($i = 0; $i < count($this->results); $i = $i + 1) {
            $maxScore += $tests[$i]['score'];
            // The grader assigns 0/1 value for each test of IOI- and ACM-style problems and [0, 1] real fraction of the score
            // for games and relative problems. In both cases, multiplying the score of the test by this value is correct.
            array_push($scores, (is_numeric($this->results[$i]) ? $this->results[$i] : 0.0) * $tests[$i]['score']);
        }
        return array_map(function($num) use($maxScore) {return 100.0 * $num / $maxScore;}, $scores);
    }

    public function calcScore() {
        return array_sum($this->calcScores());
    }
}

?>