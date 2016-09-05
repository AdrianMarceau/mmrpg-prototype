<?php

// Collect the POST header type
$request_type = !empty($_POST['requestType']) ? $_POST['requestType'] : false;
// Collect the POST header data
$request_data = !empty($_POST['requestData']) ? $_POST['requestData'] : false;

// If request type is SESSION variable updater
if ($request_type == 'session' && $request_data !== false){

    // Start the session object
    session_start();

    // Trim the request data for extra semicolons
    $request_data = trim($request_data, ';');

    // Break apart the data into an array for mulipart support
    $request_data = strstr($request_data, ';') ? explode(';', $request_data) : array($request_data);

    // Loop through all the requests and update session variables
    foreach ($request_data AS $key => $this_request_data){

        // Break apart the request data into variables
        $this_request_data = explode(',', $this_request_data);
        if (count($this_request_data) == 3){
            // Update the appropriate session variable
            list($data_index, $data_token, $data_value) = $this_request_data;
            $_SESSION['GAME'][$data_index][$data_token] = $data_value;
            echo '$_SESSION[\'GAME\'][\''.$data_index.'\'][\''.$data_token.'\'] = \''.$data_value.'\';'."\n";
        } elseif (count($this_request_data) == 2){
            // Update the appropriate session variable
            list($data_token, $data_value) = $this_request_data;
            $_SESSION['GAME'][$data_token] = $data_value;
            echo '$_SESSION[\'GAME\'][\''.$data_token.'\'] = \''.$data_value.'\';'."\n";
        } else {
            echo 'Not enough arguments... '.print_r($this_request_data, true);
        }

    }

}

?>