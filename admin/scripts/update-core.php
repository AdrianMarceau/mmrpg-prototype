<?

// Require the top file for all admin scripts
require_once('common/top.php');

// Navigate to the git repo and run a git pull to collect updates
$git_commands = '';
$git_commands .= 'cd '.MMRPG_CONFIG_ROOTDIR.' ';
$git_commands .= '&& git pull -s recursive -X theirs --no-edit 2>&1';
//debug_echo('$git_commands = '.print_r($git_commands, true).'');
$git_output = shell_exec($git_commands);
//debug_echo('$git_output = '.print_r($git_output, true).'');

// Print the returned output (modified if appropriate)
$print_output = $git_output;
if (strstr($print_output, 'Already up to date')){ $print_output = str_replace('Already', 'MMRPG already', $print_output); }
echo(trim($print_output).PHP_EOL);

// Automatically increment the config timestamp to force-refresh assets
if (!strstr($git_output, 'Already up to date')){
    list($date, $time) = explode('-', date('Ymd-Hi'));
    $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
    $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
    echo('Cache timestamp updated to '.$date.'-'.$time.PHP_EOL);
}

// Check for patch files and run them one-by-one
$patch_dir = MMRPG_CONFIG_ROOTDIR.'admin/.patches/';
$patch_files = getSortedDirContents($patch_dir, 'name');
if (!empty($patch_files)){ foreach ($patch_files AS $path){ include_patch_file($path); } }
function include_patch_file($path){
    global $db;
    ob_start();
    require_once($path);
    $output = ob_get_clean();
    if (!empty($output)){
        list($date, $name) = explode('_', str_replace('.php', '', basename($path)));
        echo(PHP_EOL);
        echo('<strong>patch name</strong>: '.$name.''.PHP_EOL);
        echo('<strong>patch date</strong>: '.$date.''.PHP_EOL);
        echo(trim($output).PHP_EOL);
    }
}

// Print the success message with the returned output
exit_action('success|Pulled Git Updates to MMRPG Core');

?>