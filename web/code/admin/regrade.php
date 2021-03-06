<?php
require_once(__DIR__ . '/../db/brain.php');
require_once(__DIR__ . '/../entities/grader.php');
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../common.php');
require_once(__DIR__ . '/../page.php');
require_once(__DIR__ . '/../entities/submit.php');

class AdminQueuePage extends Page {
    public function getTitle() {
        return 'O(N)::Admin - Regrade';
    }
    
    private function getQueueTable($data) {
        $list = '';
        for ($i = 0; $i < count($data); $i = $i + 1) {
            $entry = $data[$i];
            $list .= '
                <tr>
                    <td title="' . $entry['submitId'] . '">' . ($i + 1) . '</td>
                    <td>' . getUserLink($entry['userName']) . '</td>
                    <td>' . getProblemLink($entry['problemId'], $entry['problemName']) . '</td>
                    <td>' . explode(' ', $entry['time'])[1] . '</td>
                    <td>' . intval($entry['progress'] * 100) . '%</td>
                    <td>' . $GLOBALS['STATUS_DISPLAY_NAME'][$entry['status']] . '</td>
                </tr>
            ';
        }

        $table = '
            <table class="default">
                <tr>
                    <th style="width: 20px;">#</th>
                    <th style="width: 190px;">Потребител</th>
                    <th style="width: 190px;">Задача</th>
                    <th style="width: 70px;">Час</th>
                    <th style="width: 70px;">Прогрес</th>
                    <th>Статус</th>
                </tr>
                ' . $list . '
            </table>
        ';

        return $table;
    }

    private function regradeSubmit($submitId) {
        $submit = Submit::get($submitId);
        $submit->reset();
        $submit->send();
    }

    private function regradePending() {
        $brain = new Brain();
        $pending = $brain->getPendingSubmits();
        echo "Regrading " . count($pending) . " submissions.";
        foreach ($pending as $submit) {
            $this->regradeSubmit($submit['id']);
        }
    }

    public function getContent() {
        if (isset($_GET['submitId'])) {
            if ($_GET['submitId'] == 'pending') {
                $this->regradePending();
            } else {
                $this->regradeSubmit($_GET['submitId']);
            }
            redirect('/admin/regrade');
        }

        $head = inBox('
            <h1>Опашка</h1>
            Информация за системата и опашката от решения.
        ');

        $graderStatus ='<i id="graderStatus" class="fa fa-question-circle yellow" title="Проверка на грейдъра..."></i>';

        $time = '
            <div class="right smaller italic" style="padding-right: 4px;">
                Текущо време на системата: ' . date('H:i') . ' | ' . $graderStatus .'
            </div>
        ';

        $brain = new Brain();

        $latest = inBox('
            <h2>Последно тествани</h2>
            ' . $this->getQueueTable($brain->getLatest()) . '
        ');

        $pending = inBox('
            <h2>Изчакващи тестване</h2>
            ' . $this->getQueueTable($brain->getPending()) . '
        ');

        $compilers = '<div class="center" style="margin-top: -6px; margin-bottom: 6px;">Информация за ползваните
                <a href="help#compilation">компилатори</a> и конфигурацията на <a href="help#grader">тестващата машина</a>.</div>';

        $invokeGraderCheck = '<script>updateGraderStatus();</script>';

        return $head . $time . $pending . $latest . $compilers . $invokeGraderCheck;
    }
    
}

?>
