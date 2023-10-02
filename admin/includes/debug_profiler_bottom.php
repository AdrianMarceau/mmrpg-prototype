<?php
// Initialize an array to hold the profiling data
global $profiler_data;

// DEBUG: End metrics
$end_time = microtime(true);
$end_memory = memory_get_usage();

// Get final CPU usage
$final_cpu = trim(shell_exec("ps -p $pid -o %cpu | grep -v CPU"));

// Calculate elapsed time and memory used
global $profiler_data;
$elapsed_time = round(($end_time - $profiler_data['start_time']) * 1000, 2); // in milliseconds
$memory_used = round(($end_memory - $profiler_data['start_memory']) / 1024, 2); // in KB

// Store the end metrics in the array
$profiler_data['end_time'] = $end_time;
$profiler_data['end_memory'] = $end_memory;
$profiler_data['final_cpu'] = $final_cpu;
error_log('debug-profiler:'.print_r($profiler_data, true));

// Generate JSON and save it to a file
$cache_dir = MMRPG_CONFIG_ROOTDIR . ".cache/profiler/";
$filename = $profiler_data['profile_name'] . '_' . date('Y-m-d') . '.json';
$file_exists = file_exists($cache_dir.$filename);
if ($file_exists){ unset($profiler_data['profile_name'], $profiler_data['current_script'], $profiler_data['current_query']); }
$profiler_json = json_encode($profiler_data, JSON_PRETTY_PRINT);
if (!is_dir($cache_dir)) { mkdir($cache_dir, 0777, true); }
if ($file_exists) {
    file_put_contents($cache_dir . $filename, ','.PHP_EOL.$profiler_json, FILE_APPEND);
} else {
    file_put_contents($cache_dir . $filename, $profiler_json);
}

?>
