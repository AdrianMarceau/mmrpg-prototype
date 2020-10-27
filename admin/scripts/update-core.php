<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../../top.php');
require_once(MMRPG_CONFIG_ROOTDIR.'classes/cms_admin.php');

// Require the common git files
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/git_common_variables.php');
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/git_common_functions.php');

// Navigate to the git repo and run a git pull to collect updates
$git_commands = '';
$git_commands .= 'cd '.MMRPG_CONFIG_ROOTDIR.' ';
$git_commands .= '&& git pull -s recursive -X theirs --no-edit 2>&1';
//debug_echo('$git_commands = '.print_r($git_commands, true).'');
$git_output = shell_exec($git_commands);
//debug_echo('$git_output = '.print_r($git_output, true).'');

// Automatically increment the config timestamp to force-refresh assets
list($date, $time) = explode('-', date('Ymd-Hi'));
$db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
$db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");

// Print the success message with the returned output
echo('Cache timestamp updated to '.$date.'-'.$time.PHP_EOL);
if (strstr($git_output, 'Already up to date')){ $git_output = str_replace('Already', 'MMRPG already', $git_output); }
echo(trim($git_output).PHP_EOL);
exit_action('success|Using Git to pull updates to MMRPG '.ucfirst(MMRPG_CONFIG_SERVER_ENV).' build...');

?>