<?php

// Test if the git command is available
exec('which git', $which_git_output, $which_git_return_code);
if ($which_git_return_code !== 0) {
    echo "Git command not found. Make sure it's installed and available in the PATH.<br>";
    exit;
}

// Set the content directory path
$content_dir = __DIR__;

// Read the immediate subdirectories
$dirs = glob($content_dir . '/*', GLOB_ONLYDIR);

// Prepare the git config setting
$git_safe_directory_config = "safe.directory=*";

// Loop through each subdirectory and run "git status"
foreach ($dirs as $dir) {
    echo "<strong>Scanning directory</strong>: {$dir}<br>";

    $git_cmd_list = array();
    $git_cmd_list[] = "whoami";
    $git_cmd_list[] = "git --version";
    $git_cmd_list[] = "cd {$dir} && git status";
    $git_cmd_list[] = "cd {$dir} && git config --get safe.directory";
    $git_cmd_list[] = "cd {$dir} && git config --global --add safe.directory '{$dir}'";
    $git_cmd_list[] = "cd {$dir} && git config --add safe.directory '{$dir}'";
    $git_cmd_list[] = "cd {$dir} && git config --get safe.directory";
    $git_cmd_list[] = "cd {$dir} && git status";
    //$git_cmd_list[] = "cd {$dir} && git config --add safe.directory '{$dir}' && git status";
    foreach ($git_cmd_list AS $cmd_key => $cmd_str){

        // Capture the output of the git status command
        $output = [];
        $return_code = 0;
        $git_status_cmd = $cmd_str.' 2>&1';
        exec($git_status_cmd, $output, $return_code);
        $output_str = implode("\n", $output);

        // Print the output
        $git_status_cmd_print = $git_status_cmd;
        $git_status_cmd_print = str_replace(' && ', PHP_EOL.'  && ', $git_status_cmd_print);
        $git_status_cmd_print = str_replace(' 2>&1', PHP_EOL.'  2>&1', $git_status_cmd_print);
        echo "<pre style=\"padding: 3px; background-color: #efefef;\">$ ".$git_status_cmd_print."</pre>".PHP_EOL;
        if ($return_code === 0) {
            echo "<pre>";
            echo "Output:".PHP_EOL;
            echo $output_str;
            echo "</pre>";
        } else {
            echo "<pre>";
            echo "Error: Git command exited with code {$return_code}.".PHP_EOL;
            echo "Error message:".PHP_EOL;
            echo $output_str;
            echo "</pre>";
        }

        if (strstr($output_str, 'On branch master')){
            echo "<pre style=\"padding: 3px; background-color: #e2f6dc\">Git Success!</pre>".PHP_EOL;
            break;
        }

    }

    echo "<hr>";
}

?>
