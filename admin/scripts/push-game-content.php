<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../../top.php');
require_once(MMRPG_CONFIG_ROOTDIR.'classes/cms_admin.php');

// Require the common git actions file
$request_action = 'publish';
$allow_empty_subkind = true;
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/common_git_header.php');
//debug_echo('push-game-content'.PHP_EOL);

// Only the "all" request is supported for updating
if ($request_token !== 'all'){ exit_action('error|Only the "all" request type is supported for updates!'); }

// Define the git path for this type of content
$mmrpg_git_path = constant('MMRPG_CONFIG_'.strtoupper($request_kind).'_CONTENT_PATH');
//debug_echo('$mmrpg_git_path = '.$mmrpg_git_path);

// Ensure there are no uncommitted changes for this repo
if (!cms_admin::git_pull_allowed($mmrpg_git_path)){ exit_action('error|'.ucfirst($request_kind).' changes must be committed before publish'); }

// Collect an index of changes files via git and filter
$mmrpg_git_committed_changes = cms_admin::git_get_committed_changes($mmrpg_git_path);
//debug_echo('$mmrpg_git_committed_changes = '.print_r($mmrpg_git_committed_changes, true).'');
if (empty($mmrpg_git_committed_changes)){ exit_action('error|There are no committed '.strtolower($request_kind_singular).' changes to publish'); }

// Pull and merge any remove changes first just-in-case
$git_commands = '';
$git_commands .= 'cd '.$mmrpg_git_path.' ';
$git_commands .= '&& git pull -s recursive -X theirs --no-edit 2>&1';
//debug_echo('$git_commands = '.print_r($git_commands, true).'');
$git_output = shell_exec($git_commands);
//debug_echo('$git_output = '.print_r($git_output, true).'');

// Push and publish local committed changes (and any merging from above) to the origin
$git_commands = '';
$git_commands .= 'cd '.$mmrpg_git_path.' ';
$git_commands .= '&& git push origin master 2>&1';
//debug_echo('$git_commands = '.print_r($git_commands, true).'');
$git_output = shell_exec($git_commands);
//debug_echo('$git_output = '.print_r($git_output, true).'');

// Assuming we got this far, we can print a success message
$success_kind = !empty($request_subkind) ? $request_subkind : $request_kind;
exit_action('success|Changes to all '.$success_kind.' were pushed and published successfully!');

?>