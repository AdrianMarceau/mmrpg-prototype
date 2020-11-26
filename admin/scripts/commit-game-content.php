<?

// Require the top file for all admin scripts
require_once('common/top.php');

// Require the top file for all admin git scripts
$request_action = 'commit';
require_once('common/git-top.php');
//debug_echo('push-game-content'.PHP_EOL);

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

// Collect a separate list of untracked file (just in case) so we can see what's "new"
$mmrpg_git_untracked = cms_admin::git_get_untracked($mmrpg_git_path);
$mmrpg_git_untracked_tokens = array();
foreach ($mmrpg_git_untracked AS $key => $path){ list($token) = explode('/', $path); if (!in_array($token, $mmrpg_git_untracked_tokens)){ $mmrpg_git_untracked_tokens[] = $token; } }
//debug_echo('$mmrpg_git_untracked = '.print_r($mmrpg_git_untracked, true).'');
//debug_echo('$mmrpg_git_untracked_tokens = '.print_r($mmrpg_git_untracked_tokens, true).'');

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
if ($request_kind === 'robots' && $request_subkind === 'masters'){ $object_full_name_kind_singular = 'robot master'; }
elseif ($request_kind === 'robots' && $request_subkind === 'mechas'){ $object_full_name_kind_singular = 'support mecha'; }
elseif ($request_kind === 'robots' && $request_subkind === 'bosses'){ $object_full_name_kind_singular = 'fortress boss'; }
elseif ($request_kind === 'stars'){ $object_full_name_kind_singular = 'rogue star'; }
elseif ($request_kind === 'challenges'){ $object_full_name_kind_singular = 'event challenge'; }
elseif ($request_kind === 'pages'){ $object_full_name_kind_singular = 'website page'; }
elseif ($request_kind === 'fields'){ $object_full_name_kind_singular = 'battle field'; }
else { $object_full_name_kind_singular = $object_name_kind_singular; }

// Loop through all the commit tokens to undo relevant file and database changes
foreach ($commit_tokens  AS $object_key => $object_token){
    //debug_echo('processing object-token '.$object_token);

    // Collect the object (primary key) token value in case it's different than (folder name) token value
    if ($content_type_info['primary_key'] === 'id'){ $object_token_field_value = intval(preg_replace('/^(.*?)-([0-9]+)$/i', '$2', $object_token)); }
    elseif ($content_type_info['primary_key'] === 'url'){ $object_token_field_value = trim(str_replace('_', '/', $object_token), '/').'/'; }
    else { $object_token_field_value = $object_token; }
    //debug_echo('$object_token_field = '.print_r($object_token_field, true).'');
    //debug_echo('$object_token_field_value = '.print_r($object_token_field_value, true).'');

    // Collect the file paths to be committed
    $object_paths = $commit_paths_bytoken[$object_token];

    // Collect JSON data from the file for the commit message
    $object_name = ucwords(str_replace('-', ' ', $object_token));
    $json_data_path = $mmrpg_git_path.$object_token.'/data.json';
    //debug_echo('$json_data_path = '.print_r($json_data_path, true).'');
    if (file_exists($json_data_path)
        && !strstr($object_token, '/')){
        // Collect the markup from the file and decode it into an array
        $json_data_markup = file_get_contents($json_data_path);
        //debug_echo('$json_data_markup = '.print_r($json_data_markup, true).'');
        if (!empty($json_data_markup)){
            $json_data_array = json_decode($json_data_markup, true);
            //debug_echo('$json_data_array = '.print_r($json_data_array, true).'');
            if (!empty($json_data_array)){
                if ($object_name_kind_singular === 'star'
                    && isset($json_data_array[$request_kind_singular.'_from_date'])){
                    $object_name = $json_data_array[$request_kind_singular.'_from_date'];
                } elseif (isset($json_data_array[$request_kind_singular.'_title'])){
                    $object_name = $json_data_array[$request_kind_singular.'_title'];
                } elseif (isset($json_data_array[$request_kind_singular.'_name'])){
                    $object_name = $json_data_array[$request_kind_singular.'_name'];
                }
                //debug_echo('$object_name = '.print_r($object_name, true).'');
            }
        }
    }

    // Check to see if this is a new object being commited
    $is_new_object = in_array($object_token, $mmrpg_git_untracked_tokens) ? true : false;
    $is_deleted_object = !file_exists($json_data_path) ? true : false;

    // Check to see which files and/or assets are being updated here
    $updating_what = array();
    foreach ($object_paths AS $key => $path){
        if (strstr($path, '/data.json')){ $updating_what[] = 'data'; }
        elseif (strstr($path, '/functions.php')){ $updating_what[] = 'functions'; }
        elseif (strstr($path, '/content.html')){ $updating_what[] = 'content'; }
        elseif (strstr($path, '/sprites')){ $updating_what[] = 'sprites'; }
        elseif (strstr($path, '/shadows')){ $updating_what[] = 'shadows'; }
    }
    $updating_what = array_unique($updating_what);
    if (count($updating_what) >= 3){ $updating_what_string = implode(', ', array_slice($updating_what, 0, -1)).', and '.implode('', array_slice($updating_what, -1, 1)); }
    else { $updating_what_string = implode(' and ', $updating_what);  }

    // Define the commit message for these file changes
    $commit_name = str_replace('"', '\\"', $git_publish_name);
    $commit_email = str_replace('"', '\\"', $git_publish_email);
    if ($is_deleted_object){
        $commit_message = 'Deleted the ';
        $commit_message .= '\''.$object_name.'\' '.$object_full_name_kind_singular;
    } elseif ($is_new_object){
        $commit_message = 'Created new ';
        $commit_message .= $updating_what_string.' ';
        $commit_message .= 'for ';
        $commit_message .= preg_match('/^(a|e|i|o|u)/i', $object_full_name_kind_singular) ? 'an ' : 'a ';
        $commit_message .= $object_full_name_kind_singular.' ';
        if ($object_name_kind_singular === 'star'){
            $commit_message .= 'on \''.$object_name.'\' ';
        } elseif ($object_name_kind_singular === 'challenge'){
            $commit_message .= 'titled \''.$object_name.'\' ';
        } else {
            $commit_message .= 'named \''.$object_name.'\' ';
        }
    } else {
        // when [object] is player, master, boss, field = "Updated XXX's data, functions, etc."
        // else when [object] is mecha, ability, item = "Updated the XXX's data, functions, etc."
        // else when [object] is other = Updated data, functions, etc. for the XXX [object]
        $commit_message = 'Updated ';
        if (strstr($object_token, '_groups/')){
            $commit_message .= 'sorting groups for ';
            if ($request_kind === 'abilities'){ $commit_message .= $request_subkind_singular.' '.$request_kind; }
            else { $commit_message .= !empty($request_subkind) ? $request_subkind : $request_kind; }
        } elseif (in_array($object_name_kind_singular, array('player', 'master', 'boss', 'field', 'skill'))){
            $commit_message .= $object_name.'\''.(substr($object_name, -1, 1) !== 's' ? 's' : '').' ';
            $commit_message .= $updating_what_string;
        } elseif (in_array($object_name_kind_singular, array('mecha', 'ability', 'item'))){
            $commit_message .= 'the ';
            $commit_message .= $object_name.'\''.(substr($object_name, -1, 1) !== 's' ? 's' : '').' ';
            $commit_message .= $updating_what_string;
        } else {
            $commit_message .= 'the ';
            $commit_message .= $updating_what_string;
            $commit_message .= ' for the ';
            $commit_message .= '\''.$object_name.'\' '.$object_full_name_kind_singular;
        }
    }

    $commit_message = str_replace('"', '\\"', $commit_message);
    //debug_echo('$commit_message = '.print_r($commit_message, true).'');

    // If this database table is protected, we need to update the DB flag for this object
    if (!empty($content_type_info['database_table_protected'])
        && !strstr($object_token, '/')){
        //debug_echo('Mark this object as protected now!');
        $update_data = array($request_kind_singular.'_flag_protected' => 1);
        $update_condition = array($object_token_field => $object_token_field_value);
        //debug_echo('$update_data = '.print_r($update_data, true).'');
        //debug_echo('$update_condition = '.print_r($update_condition, true).'');
        $db->update($object_table_name, $update_data, $update_condition);
    }

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
elseif (strstr($request_token, '_groups/')){ exit_action('success|Sort group changes for '.$success_kind.' were committed!'); }
else { exit_action('success|Changes to '.($num_committed === 1 ? ('this '.$success_kind_singular) : ($num_committed.' '.$success_kind)).' were committed!'); }

?>