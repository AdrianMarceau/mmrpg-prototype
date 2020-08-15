<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../../top.php');
require_once(MMRPG_CONFIG_ROOTDIR.'classes/cms_admin.php');

// Require the common git actions file
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/git_common.php');
//debug_echo('push-game-content'.PHP_EOL);


// Define the table name and token field for this object
$object_table_name = 'mmrpg_index_'.$request_kind;
$object_token_field = $request_kind_singular.'_token';
//debug_echo('$object_table_name = '.print_r($object_table_name, true).'');
//debug_echo('$object_token_field = '.print_r($object_token_field, true).'');

// Collect an index of changes files via git
$mmrpg_git_path = constant('MMRPG_CONFIG_'.strtoupper($request_kind).'_CONTENT_PATH');
//debug_echo('$mmrpg_git_path = '.$mmrpg_git_path);
$mmrpg_git_changes = cms_admin::git_get_changes($mmrpg_git_path);
//debug_echo('$mmrpg_git_changes = '.print_r($mmrpg_git_changes, true).'');
if ($request_kind === 'robots'){
    $mmrpg_git_changes = cms_admin::git_filter_list_by_data($mmrpg_git_changes, array(
        'table' => $object_table_name,
        'token' => $object_token_field,
        'extra' => array($request_kind_singular.'_class' => $request_subkind_singular)
        ));
    //debug_echo('$mmrpg_git_changes(B) = '.print_r($mmrpg_git_changes, true).'');
}

// Define an array to hold all object tokens and file paths to be committed
$commit_tokens = array();
$commit_paths = array();
$commit_paths_bytoken = array();

// If the "all" token was explicitly provided, we're going to commit everything
if ($request_token === 'all'){
    // All all git changes to the list of commit paths
    foreach ($mmrpg_git_changes AS $key => $path){ list($token) = explode('/', $path); if (!in_array($token, $commit_tokens)){ $commit_tokens[] = $token; } }
    foreach ($commit_tokens AS $key => $token){
        $filtered_paths = cms_admin::git_filter_list_by_path($mmrpg_git_changes, $token.'/');
        $commit_paths = array_merge($commit_paths, $filtered_paths);
        $commit_paths_bytoken[$token] = $filtered_paths;
    }
    //debug_echo('commit everything!');
}
// Else we're only going to commit items that match the provided token
else {
    // Only add changes starting with requested token to the list of commit paths
    $commit_tokens = array($request_token);
    $filtered_paths = cms_admin::git_filter_list_by_path($mmrpg_git_changes, $request_token.'/');
    $commit_paths = array_merge($commit_paths, $filtered_paths);
    $commit_paths_bytoken[$request_token] = $filtered_paths;
    //debug_echo('commit only '.$request_token.'!');
}

// Break early if the commit tokens or paths are empty
if (empty($commit_tokens)){ exit_action('error|The commit_tokens were empty (there was nothing to commit)'); }
if (empty($commit_paths)){ exit_action('error|The commit_paths were empty (there was nothing to commit)'); }

//debug_echo('$commit_tokens = '.print_r($commit_tokens, true).'');
//debug_echo('$commit_paths = '.print_r($commit_paths, true).'');
//debug_echo('$commit_paths_bytoken = '.print_r($commit_paths_bytoken, true).'');

// Pre-collect object name kinds for later commit messages
$object_name_kind = !empty($request_subkind) ? $request_subkind : $request_kind;
$object_name_kind_singular = !empty($request_subkind_singular) ? $request_subkind_singular : $request_kind_singular;

// Loop through all the commit tokens to undo relevant file and database changes
foreach ($commit_tokens  AS $object_key => $object_token){
    //debug_echo('processing object-token '.$object_token);

    // Collect the file paths to be committed
    $object_paths = $commit_paths_bytoken[$object_token];

    // Collect JSON data from the file for the commit message
    $object_name = ucwords(str_replace('-', ' ', $object_token));
    $json_data_path = $mmrpg_git_path.$object_token.'/data.json';
    //debug_echo('$json_data_path = '.print_r($json_data_path, true).'');
    if (file_exists($json_data_path)){
        // Collect the markup from the file and decode it into an array
        $json_data_markup = file_get_contents($json_data_path);
        //debug_echo('$json_data_markup = '.print_r($json_data_markup, true).'');
        if (!empty($json_data_markup)){
            $json_data_array = json_decode($json_data_markup, true);
            //debug_echo('$json_data_array = '.print_r($json_data_array, true).'');
            if (!empty($json_data_array)){
                $object_name = $json_data_array[$request_kind_singular.'_name'];
                //debug_echo('$object_name = '.print_r($object_name, true).'');
            }
        }
    }

    // Check to see which files and/or assets are being updated here
    $updating_what = array();
    foreach ($object_paths AS $key => $path){
        if (strstr($path, '/data.json')){ $updating_what[] = 'data'; }
        elseif (strstr($path, '/functions.php')){ $updating_what[] = 'functions'; }
        elseif (strstr($path, '/sprites')){ $updating_what[] = 'sprites'; }
        elseif (strstr($path, '/shadows')){ $updating_what[] = 'shadows'; }
    }
    $updating_what = array_unique($updating_what);
    if (count($updating_what) >= 3){ $updating_what_string = implode(', ', array_slice($updating_what, 0, -1)).', and '.array_slice($updating_what, -1, 1); }
    else { $updating_what_string = implode(' and ', $updating_what);  }

    // Define the commit message for these file changes
    $commit_name = str_replace('"', '\\"', $git_publish_name);
    $commit_email = str_replace('"', '\\"', $git_publish_email);
    $commit_message = 'Updated ';
    if (in_array($object_name_kind_singular, array('mecha', 'ability', 'item'))){ $commit_message .= 'the '; }
    $commit_message .= $object_name.'\''.(substr($object_name, -1, 1) !== 's' ? 's' : '').' ';
    $commit_message .= $updating_what_string;
    $commit_message = str_replace('"', '\\"', $commit_message);
    /* $commit_message = str_replace('"', '\\"', 'Publishing changes to the '.
        $object_name.' '.
        $object_name_kind_singular.'\''.(substr($object_name_kind_singular, -1, 1) !== 's' ? 's' : '').' '.
        $updating_what_string
        ); */

    // Commit the relevant file changes as this user and then push
    $git_commands = '';
    $git_commands .= 'cd '.$mmrpg_git_path.' ';
    $git_commands .= '&& git add "'.$object_token.'/" ';
    $git_commands .= '&& git -c "user.name='.$commit_name.'" -c "user.email='.$commit_email.'" commit -m "'.$commit_message.'" ';
    //debug_echo('$git_commands = '.print_r($git_commands, true).'');
    $git_output = shell_exec($git_commands);
    //debug_echo('$git_output = '.print_r($git_output, true).'');

}

// Assuming we got this far, we can print a success message
$num_committed = count($commit_tokens);
$success_kind = !empty($request_subkind) ? $request_subkind : $request_kind;
$success_kind_singular = !empty($request_subkind_singular) ? $request_subkind_singular : $request_kind_singular;
if ($request_token === 'all'){ exit_action('success|Changes to all '.$success_kind.' were committed!'); }
else { exit_action('success|Changes to '.($num_committed === 1 ? ('this '.$success_kind_singular) : ($num_committed.' '.$success_kind)).' were committed!'); }

?>