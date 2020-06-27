<?

// Define some API error codes for later
define('MMRPG_API_ERROR_NOTFOUND', 404);

// Define a function for printing out a return array and then exiting
function print_return_array($return_array, $return_markup = false){
    $new_return_array = array();
    if (isset($return_array['status'])){ $new_return_array['status'] = $return_array['status']; }
    if (isset($return_array['message'])){ $new_return_array['message'] = $return_array['message']; }
    if (isset($return_array['code'])){ $new_return_array['code'] = $return_array['code']; }
    if (isset($return_array['status']) && $return_array['status'] === 'success'){
        list($new_cache_date, $new_cache_time) = explode('-', MMRPG_CONFIG_CACHE_DATE);
        $yyyy = substr($new_cache_date, 0, 4); $mm = substr($new_cache_date, 4, 2); $dd = substr($new_cache_date, 6, 2);
        $hh = substr($new_cache_time, 0, 2); $ii = substr($new_cache_time, 2, 2);
        $new_return_array['updated'] = mktime($hh, $ii, 0, $mm, $dd, $yyyy);
    }
    $new_return_array = array_merge($new_return_array, $return_array);
    $cache_file_markup = json_encode($new_return_array);
    if ($return_markup){ return $cache_file_markup; }
    header('Content-type: text/json; charset=UTF-8');
    echo($cache_file_markup);
    exit();
}

// Define a function printing a return array, updating the API cache, and then exiting
function print_and_update_api_cache($cache_file_path, $return_array, $return_markup = false){
    $cache_file_markup = print_return_array($return_array, true);
    if (MMRPG_CONFIG_CACHE_INDEXES && isset($return_array['status']) && $return_array['status'] === 'success'){
        $cache_file_handler = fopen($cache_file_path, 'w');
        fwrite($cache_file_handler, $cache_file_markup);
        fclose($cache_file_handler);
    }
    if ($return_markup){ return $cache_file_markup; }
    header('Content-type: text/json; charset=UTF-8');
    echo($cache_file_markup);
    exit();
}

// Define a function for easily printing a successful request's data and then exiting
function print_success_and_update_api_cache($success_data){
    global $cache_file_path;
    $return_array = array('status' => 'success', 'data' => $success_data);
    print_and_update_api_cache($cache_file_path, $return_array);
}

// Define a function for easily printing a successful request's data and then exiting
function print_error_and_quit($error_message, $error_code = false){
    global $cache_file_path;
    $return_array = array('status' => 'error');
    $return_array['message'] = $error_message;
    if (!empty($error_code)){ $return_array['code'] = $error_code; }
    if ($error_code === MMRPG_API_ERROR_NOTFOUND){ header("HTTP/1.0 404 Not Found"); }
    print_return_array($return_array);
}

// Define a function for printing a critical error and exiting
function critical_api_error($error_message, $file = '', $line = 0, $code = 0){
    $return_array = array('status' => 'error');
    $return_array['message'] = $error_message;
    if (!empty($code)){ $return_array['code'] = $code; }
    if (MMRPG_CONFIG_IS_LIVE === false){
        $debug_info = '';
        if (!empty($file)){ $file = str_replace(MMRPG_CONFIG_ROOTDIR, '/', str_replace('\\', '/', $file)); }
        if (!empty($file)){ $debug_info .= $file; }
        if (!empty($line)){ $debug_info .= ' on line '.$line; }
        if (!empty($debug_info)){ $return_array['data']['debug'] = $debug_info; }
    }
    print_return_array($return_array);
}

?>