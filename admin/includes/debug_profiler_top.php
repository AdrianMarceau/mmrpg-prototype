<?php
// Globalize the array to hold the profiling data
global $profiler_data;

// Initialize an array to hold the profiling data
$profiler_data = [];

// DEBUG: Log the current page
//$current_page = basename($_SERVER["SCRIPT_FILENAME"]).'?'.json_encode($_GET);
$profiler_data['profile_name'] = '';
$profiler_data['current_script'] = basename($_SERVER["SCRIPT_FILENAME"]);
$profiler_data['profile_name'] .= str_replace('.php', '', $profiler_data['current_script']);
$profiler_data['current_query'] = http_build_query($_GET);
$profiler_data['profile_name'] .= '-'.implode('-', array_values($_GET));
$profiler_data['profile_name'] = preg_replace('/[-]{2,}/', '-', trim($profiler_data['profile_name'], '-'));

// Get the PID of the current PHP process
$pid = getmypid();

// Get initial CPU usage
$initial_cpu = trim(shell_exec("ps -p $pid -o %cpu | grep -v CPU"));

// Store the initial CPU usage in the array
$profiler_data['initial_cpu'] = $initial_cpu;

// DEBUG: Start metrics
$start_time = microtime(true);
$start_memory = memory_get_usage();

// Store the start metrics in the array
$profiler_data['start_time'] = $start_time;
$profiler_data['start_memory'] = $start_memory;

// Initialize checkpoints array
$profiler_data['checkpoints'] = [];

// Define a function for easily appending checkpoints to the data
function debug_profiler_checkpoint($checkpoint){

    // Initialize an array to hold the profiling data
    global $profiler_data;

    // Get the PID of the current PHP process
    $pid = getmypid();

    // Get CPU usage
    $checkpoint_cpu = trim(shell_exec("ps -p $pid -o %cpu | grep -v CPU"));

    // DEBUG: Metrics
    $checkpoint_time = microtime(true);
    $checkpoint_memory = memory_get_usage();

    // Calculate elapsed time and memory used
    $elapsed_time = round(($checkpoint_time - $profiler_data['start_time']) * 1000, 2); // in milliseconds
    $memory_used = round(($checkpoint_memory - $profiler_data['start_memory']) / 1024, 2); // in KB

    // Append to checkpoints array
    error_log('checkpoint: '.$checkpoint);
    $profiler_data['checkpoints'][$checkpoint] = [
        'elapsed_time' => $elapsed_time,
        'memory_used' => $memory_used,
        'cpu_usage' => $checkpoint_cpu
        ];

}

?>
