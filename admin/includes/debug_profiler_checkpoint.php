<?php
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
$profiler_data['checkpoints'][$checkpoint] = [
    'elapsed_time' => $elapsed_time,
    'memory_used' => $memory_used,
    'cpu_usage' => $checkpoint_cpu
];
?>
