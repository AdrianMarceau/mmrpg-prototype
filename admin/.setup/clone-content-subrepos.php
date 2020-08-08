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

// Require the function definitions needed for clone stuff
//require($setup_dir.'clone-objects_xfunctions.php');

// Define a quick function for immediately printing an echo statement
function ob_echo($echo, $silent = false){ if (!$silent){ echo($echo.PHP_EOL); } ob_flush(); }

// Define a quick function for executing a shell command and printing the output
function ob_echo_shell_exec($cmd){ ob_echo('$ '.$cmd); $output = shell_exec($cmd); ob_echo($output); }

// Define a function for cleaning a path of the root dir for printing
function clean_path($path){ return str_replace(MMRPG_CONFIG_ROOTDIR, '/', $path); }

// Proceed based on the KIND of object we're migrating
$allowed_clone_types = array_keys($content_types_index);
$clone_kind = !empty($_REQUEST['kind']) && ($_REQUEST['kind'] === 'all' || in_array($_REQUEST['kind'], $allowed_clone_types)) ? trim($_REQUEST['kind']) : 'all';
$clone_overwrite = !empty($_REQUEST['overwrite']) && $_REQUEST['overwrite'] === 'true' ? true : false;
if (!empty($clone_kind)){ $clone_kind_singular = substr($clone_kind, -3, 3) === 'ies' ? str_replace('ies', 'y', $clone_kind) : rtrim($clone_kind, 's'); }
else { $clone_kind_singular = false; }
if (!empty($clone_kind)){
    $clone_these_types = $clone_kind === 'all' ? $allowed_clone_types : array($clone_kind);

    ob_echo('');
    if ($clone_kind === 'all'){ $title =('|  CLONE ALL REPOSITORIES  |'); }
    else { $title = ('|  CLONE '.strtoupper($clone_kind).' REPOSITORY  |'); }
    ob_echo(str_repeat('=', strlen($title)));
    ob_echo($title);
    ob_echo(str_repeat('=', strlen($title)));
    ob_echo('');
    sleep(1);

    foreach ($clone_these_types AS $type_key => $type_token){

        $type_details = $content_types_index[$type_token];
        $repo_details = $type_details['github_repo'];

        ob_echo('Cloning the "'.$repo_details['name'].'" repo...');

        $repo_content_dir = MMRPG_CONFIG_CONTENT_PATH.$type_details['content_path'];
        $repo_content_exists = file_exists($repo_content_dir);
        if (!$repo_content_exists || $clone_overwrite){

            $cmds = '';
            $cmds .= 'cd '.MMRPG_CONFIG_CONTENT_PATH.' ';
            if ($repo_content_exists){ $cmds .= '&& rm -r -f '.rtrim($type_details['content_path'], '/').' '; }
            $cmds .= '&& mkdir '.rtrim($type_details['content_path'], '/').' ';
            $cmds .= '&& git clone '.$repo_details['http'].' '.$type_details['content_path'].' ';
            ob_echo_shell_exec($cmds);

        } else {

            ob_echo('(!) The '.clean_path($repo_content_dir).' directory already exists!');

        }

        $cmds = '';
        $cmds .= 'cd '.$repo_content_dir.' ';
        $cmds .= '&& git status ';
        ob_echo_shell_exec($cmds);

    }

    sleep(1);
    ob_echo('');
    ob_echo('...Done!');
    ob_echo('');

} elseif (!empty($clone_kind)) {

    ob_echo('Repo kind "'.$clone_kind.'" not supported or repo not ready yet!');

} else {

    ob_echo('Repo kind not provided!');

}

// Empty the output buffer (or whatever is left)
ob_end_flush();


?>