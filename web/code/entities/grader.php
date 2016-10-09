<?php
require_once(__DIR__ . '/../config.php');

class Grader {
    private static $METHOD_GET = 'GET';
    private static $METHOD_POST = 'POST';

    function call($path, $data, $method) {
        $curl = curl_init();

        // Setup the connection
        if ($method == Grader::$METHOD_POST) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array('data' => json_encode($data)));
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $GLOBALS['GRADER_URL'] . $path);

        // Setup authentication
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $hashedUsername = sha1($GLOBALS['GRADER_USERNAME']);
        $hashedPassword = sha1($GLOBALS['GRADER_PASSWORD']);
        curl_setopt($curl, CURLOPT_USERPWD, $hashedUsername . ':' . $hashedPassword);

        // Execute the request
        $response = curl_exec($curl);

        // Convert the response to array (no matter whether successful or not)
        if (!$response) {
            $response = [];
        } else {
            $response = json_decode($response, true);
        }

        // Attach the HTTP status code to the response
        $response['status'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Close the connection and return
        curl_close($curl);
        return $response;
    }

    function healthy() {
        $response = $this->call($GLOBALS['GRADER_ENDPOINT_HEALTHCHECK'], [], Grader::$METHOD_GET);
        return $response['status'] == 200;
    }

    function evaluate($data) {
        $response = $this->call($GLOBALS['GRADER_ENDPOINT_EVALUATE'], $data, Grader::$METHOD_POST);
        return true;
    }
}

?>
