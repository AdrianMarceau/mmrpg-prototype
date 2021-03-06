<?

// Prevent game-related logic from running
define('MMRPG_EXCLUDE_GAME_LOGIC', true);

// Require the top file for paths and stuff
$setup_dir = str_replace('\\', '/', dirname(__FILE__)).'/';
$base_dir = dirname(dirname($setup_dir)).'/';
require($base_dir.'top.php');

// Require the repository index for looping
require(MMRPG_CONFIG_ROOTDIR.'content/index.php');

// Define the header type so it's easier to display stuff
header('Content-type: text/plain;');

// ONLY allow this file to run in CLI mode
if (php_sapi_name() !== 'cli'){
    die('This setup script can ONLY be run in CLI mode!!!');
}

// Start the output buffer now, we'll flush manually as we go
ob_implicit_flush(true);
ob_start();

// Require the function definitions needed for pull stuff
//require($setup_dir.'pull-objects_xfunctions.php');

// Define a quick function for immediately printing an echo statement
function ob_echo($echo, $silent = false){ if (!$silent){ echo($echo.PHP_EOL); } ob_flush(); }

// Define a quick function for executing a shell command and printing the output
function ob_echo_shell_exec($cmd){ ob_echo('$ '.$cmd); $output = shell_exec($cmd); ob_echo($output); }

// Define a function for cleaning a path of the root dir for printing
function clean_path($path){ return str_replace(MMRPG_CONFIG_ROOTDIR, '/', $path); }

// Proceed based on the KIND of object we're migrating
$allowed_pull_types = array_keys($content_types_index);
$pull_kind = !empty($_REQUEST['kind']) && ($_REQUEST['kind'] === 'all' || in_array($_REQUEST['kind'], $allowed_pull_types)) ? trim($_REQUEST['kind']) : 'all';
$pull_branch = !empty($_REQUEST['branch']) && preg_match('/^[-_a-z0-9\/]+$/i', $_REQUEST['branch']) ? trim($_REQUEST['branch']) : 'master';
if (!empty($pull_kind)){ $pull_kind_singular = substr($pull_kind, -3, 3) === 'ies' ? str_replace('ies', 'y', $pull_kind) : rtrim($pull_kind, 's'); }
else { $pull_kind_singular = false; }
if (!empty($pull_kind)){
    $pull_these_types = $pull_kind === 'all' ? $allowed_pull_types : array($pull_kind);

    ob_echo('');
    if ($pull_kind === 'all'){ $title =('|  PULL ALL REPOSITORIES  |'); }
    else { $title = ('|  PULL '.strtoupper($pull_kind).' REPOSITORY  |'); }
    ob_echo(str_repeat('=', strlen($title)));
    ob_echo($title);
    ob_echo(str_repeat('=', strlen($title)));
    ob_echo('');
    sleep(1);

    foreach ($pull_these_types AS $type_key => $type_token){

        $type_details = $content_types_index[$type_token];
        $repo_details = $type_details['github_repo'];

        ob_echo('Pulling the "'.$repo_details['name'].'" repo...');

        $repo_content_dir = MMRPG_CONFIG_CONTENT_PATH.$type_details['content_path'];
        $repo_content_exists = file_exists($repo_content_dir);
        if (file_exists($repo_content_dir)){

            if (file_exists($repo_content_dir.'.git/')){

                $cmds = '';
                $cmds .= 'cd '.MMRPG_CONFIG_CONTENT_PATH.$type_details['content_path'].' ';
                $cmds .= '&& git pull origin '.$pull_branch.' --allow-unrelated-histories ';
                ob_echo_shell_exec($cmds);

            } else {

                ob_echo('(!) The '.clean_path($repo_content_dir).' directory isn\'t a git repo!');

            }

        } else {

            ob_echo('(!) The '.clean_path($repo_content_dir).' directory doesn\'t exist!');

        }

    }

    sleep(1);
    ob_echo('');
    ob_echo('...Done!');
    ob_echo('');

} elseif (!empty($pull_kind)) {

    ob_echo('Repo kind "'.$pull_kind.'" not supported or repo not ready yet!');

} else {

    ob_echo('Repo kind not provided!');

}

// Empty the output buffer (or whatever is left)
ob_end_flush();


?>