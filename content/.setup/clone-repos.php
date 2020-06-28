<?

// Prevent game-related logic from running
define('MMRPG_EXCLUDE_GAME_LOGIC', true);

// Require the top file for paths and stuff
$clone_dir = str_replace('\\', '/', dirname(__FILE__)).'/';
$base_dir = dirname(dirname($clone_dir)).'/';
require($base_dir.'top.php');

// Require the repository index for looping
require($clone_dir.'_repo-index.php');

// Define the header type so it's easier to display stuff
header('Content-type: text/plain;');

// ONLY allow this file to run locally
if (defined('MMRPG_CONFIG_IS_LIVE') && MMRPG_CONFIG_IS_LIVE === true){
    die('This setup script can ONLY be run locally!!!');
}

// Start the output buffer now, we'll flush manually as we go
ob_implicit_flush(true);
ob_start();

// Require the function definitions needed for clone stuff
//require($clone_dir.'clone-objects_xfunctions.php');

// Define a quick function for immediately printing an echo statement
function ob_echo($echo, $silent = false){ if (!$silent){ echo($echo.PHP_EOL); } ob_flush(); }

// Define a quick function for executing a shell command and printing the output
function ob_echo_shell_exec($cmd){ ob_echo('$ '.$cmd); $output = shell_exec($cmd); ob_echo($output); }

// Define a function for cleaning a path of the root dir for printing
function clean_path($path){ return str_replace(MMRPG_CONFIG_ROOTDIR, '/', $path); }

// Define the directory where all content is cloned into
define('MMRPG_BASE_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/');

// Proceed based on the KIND of object we're migrating
$allowed_clone_types = array_keys($content_repos_index);
$clone_kind = !empty($_REQUEST['kind']) && ($_REQUEST['kind'] === 'all' || in_array($_REQUEST['kind'], $allowed_clone_types)) ? trim($_REQUEST['kind']) : 'all';
$clone_overwrite = !empty($_REQUEST['overwrite']) && $_REQUEST['overwrite'] === 'true' ? true : false;
if (!empty($clone_kind)){ $clone_kind_singular = substr($clone_kind, -3, 3) === 'ies' ? str_replace('ies', 'y', $clone_kind) : rtrim($clone_kind, 's'); }
else { $clone_kind_singular = false; }
if (!empty($clone_kind)){
    $clone_these_repos = $clone_kind === 'all' ? $allowed_clone_types : array($clone_kind);

    ob_echo('');
    if ($clone_kind === 'all'){ $title =('|  CLONE ALL REPOSITORIES  |'); }
    else { $title = ('|  CLONE '.strtoupper($clone_kind).' REPOSITORY  |'); }
    ob_echo(str_repeat('=', strlen($title)));
    ob_echo($title);
    ob_echo(str_repeat('=', strlen($title)));
    ob_echo('');
    sleep(1);

    foreach ($clone_these_repos AS $repo_key => $repo_token){

        $repo_details = $content_repos_index[$repo_token];

        ob_echo('Cloning the "'.$repo_details['name'].'" repo...');

        $repo_content_dir = MMRPG_BASE_CONTENT_DIR.$repo_details['path'];
        $repo_content_exists = file_exists($repo_content_dir);
        if (!$repo_content_exists || $clone_overwrite){

            $cmds = '';
            $cmds .= 'cd '.MMRPG_BASE_CONTENT_DIR.' ';
            if ($repo_content_exists){ $cmds .= '&& rm -r -f '.rtrim($repo_details['path'], '/').' '; }
            $cmds .= '&& mkdir '.rtrim($repo_details['path'], '/').' ';
            $cmds .= '&& git clone '.$repo_details['src'].' '.$repo_details['path'].' ';
            ob_echo_shell_exec($cmds);

        } else {

            ob_echo('(!) The '.clean_path($repo_content_dir).' directory already exists!');

        }

    }

    sleep(1);
    ob_echo('');
    if ($clone_kind === 'all'){ $subtitle =('|  ALL CLONES COMPLETE  |'); }
    else { $subtitle = ('|  '.strtoupper($clone_kind).' CLONE COMPLETE  |'); }
    ob_echo(str_repeat('=', strlen($subtitle)));
    ob_echo($subtitle);
    ob_echo(str_repeat('=', strlen($subtitle)));

} elseif (!empty($clone_kind)) {

    ob_echo('Repo kind "'.$clone_kind.'" not supported or repo not ready yet!');

} else {

    ob_echo('Repo kind not provided!');

}

// Empty the output buffer (or whatever is left)
ob_end_flush();


?>