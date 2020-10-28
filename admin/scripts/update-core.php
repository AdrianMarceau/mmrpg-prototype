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

// Print the success message with the returned output
exit_action('success|Pulled Git Updates to MMRPG Core');

?>