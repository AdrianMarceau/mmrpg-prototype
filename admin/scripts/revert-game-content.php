<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../../top.php');
require_once(MMRPG_CONFIG_ROOTDIR.'classes/cms_admin.php');

// Require the common git actions file
$request_action = 'revert';
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/git_common.php');
//debug_echo('revert-game-content');

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

// Define an array to hold all object tokens and file paths to be reverted
$revert_tokens = array();
$revert_paths = array();
$revert_paths_bytoken = array();

// If the "all" token was explicitly provided, we're going to revert everything
if ($request_token === 'all'){
    // All all git changes to the list of revert paths
    foreach ($mmrpg_git_changes AS $key => $path){ list($token) = explode('/', $path); if (!in_array($token, $revert_tokens)){ $revert_tokens[] = $token; } }
    foreach ($revert_tokens AS $key => $token){
        $filtered_paths = cms_admin::git_filter_list_by_path($mmrpg_git_changes, $token.'/');
        $revert_paths = array_merge($revert_paths, $filtered_paths);
        $revert_paths_bytoken[$token] = $filtered_paths;
    }
    //debug_echo('revert everything!');
}
// Else we're only going to revert items that match the provided token
else {
    // Only add changes starting with requested token to the list of revert paths
    $revert_tokens = array($request_token);
    $filtered_paths = cms_admin::git_filter_list_by_path($mmrpg_git_changes, $request_token.'/');
    $revert_paths = array_merge($revert_paths, $filtered_paths);
    $revert_paths_bytoken[$request_token] = $filtered_paths;
    //debug_echo('revert only '.$request_token.'!');
}

// Break early if the revert tokens or paths are empty
if (empty($revert_tokens)){ exit_action('error|The revert_tokens were empty (there was nothing to revert)'); }
if (empty($revert_paths)){ exit_action('error|The revert_paths were empty (there was nothing to revert)'); }

//debug_echo('$revert_tokens = '.print_r($revert_tokens, true).'');
//debug_echo('$revert_paths = '.print_r($revert_paths, true).'');
//debug_echo('$revert_paths_bytoken = '.print_r($revert_paths_bytoken, true).'');

// Loop through all the revert tokens to undo relevant file and database changes
foreach ($revert_tokens  AS $object_key => $object_token){
    //debug_echo('processing object-token '.$object_token);

    // Collect the object (primary key) token value in case it's different than (folder name) token value
    if ($content_type_info['primary_key'] === 'id'){ $object_token_field_value = intval(preg_replace('/^(.*?)-([0-9]+)$/i', '$2', $object_token)); }
    elseif ($content_type_info['primary_key'] === 'url'){ $object_token_field_value = trim(str_replace('_', '/', $object_token), '/').'/'; }
    else { $object_token_field_value = $object_token; }
    //debug_echo('$object_token_field = '.print_r($object_token_field, true).'');
    //debug_echo('$object_token_field_value = '.print_r($object_token_field_value, true).'');

    // Collect the file paths to be reverted
    $object_paths = $revert_paths_bytoken[$object_token];

    // First, revert the actual changes with git
    $git_commands = '';
    $git_commands .= 'cd '.$mmrpg_git_path.' ';
    $git_commands .= '&& git checkout -- "'.$object_token.'/" ';
    //debug_echo('$git_commands = '.print_r($git_commands, true).'');
    $git_output = shell_exec($git_commands);
    //debug_echo('$git_output = '.print_r($git_output, true).'');

    // If a JSON data file exists for this token, overwrite DB info with contents
    $json_data_path = $mmrpg_git_path.$object_token.'/data.json';
    //debug_echo('$json_data_path = '.print_r($json_data_path, true).'');
    if (file_exists($json_data_path)){
        // Collect the markup from the file and decode it into an array
        $json_data_markup = file_get_contents($json_data_path);
        //debug_echo('$json_data_markup = '.print_r($json_data_markup, true).'');
        if (!empty($json_data_markup)){
            $json_data_array = json_decode($json_data_markup, true);
            foreach ($json_data_array AS $f => $v){ if (is_array($v)){ $json_data_array[$f] = !empty($v) ? json_encode($v, JSON_NUMERIC_CHECK) : ''; } }
            //debug_echo('$json_data_array = '.print_r($json_data_array, true).'');
            if (!empty($json_data_array)){
                // Create an update array from the data, unset the PK, then translate the editor name(s) to IDs
                $object_update_data = $json_data_array;
                unset($object_update_data[$object_token_field]);
                foreach ($image_editor_fields AS $image_editor_field){
                    if (!isset($object_update_data[$image_editor_field])){ continue; }
                    $editor_name_value = $object_update_data[$image_editor_field];
                    if (!empty($editor_name_value) && !empty($mmrpg_contributors_name_to_id[$editor_name_value])){
                        $object_update_data[$image_editor_field] = $mmrpg_contributors_name_to_id[$editor_name_value];
                    } else {
                        $object_update_data[$image_editor_field] = 0;
                    }
                }
                // Check to make sure the object still exists in the database so we know if we should update or insert
                $object_exists = $db->get_value("SELECT {$object_token_field} FROM {$object_table_name} WHERE {$object_token_field} = '{$object_token_field_value}';", $object_token_field);
                //debug_echo('$object_exists = '.print_r($object_exists, true).'');
                if (!empty($object_exists)){
                    // Update the database object with above values given the object token
                    //debug_echo('$object_update_data = '.print_r($object_update_data, true).'');
                    //debug_echo('condition = '.print_r(array($object_token_field => $object_token_field_value), true).'');
                    $db->update($object_table_name, $object_update_data, array($object_token_field => $object_token_field_value));
                } else {
                    // Re-insert the database object with above values given the object token
                    $object_insert_data = array_merge($object_update_data, array($object_token_field => $object_token_field_value));
                    //debug_echo('$object_insert_data = '.print_r($object_insert_data, true).'');
                    $db->insert($object_table_name, $object_insert_data);
                }

            }
        }
    }

    // If an HTML content file exists for this token, overwrite DB info with contents
    if ($request_kind === 'pages'){
        $html_content_path = $mmrpg_git_path.$object_token.'/content.html';
        //debug_echo('$html_content_path = '.print_r($html_content_path, true).'');
        if (file_exists($html_content_path)){
            // Collect the markup from the file and decode it into an array
            $html_content_markup = file_get_contents($html_content_path);
            //debug_echo('$html_content_markup = '.print_r($html_content_markup, true).'');
            if (!empty($html_content_markup)){
                // Create an update array from the content, unset the PK, then translate the editor name(s) to IDs
                $object_update_data = array($request_kind_singular.'_content' => $html_content_markup);
                //debug_echo('$object_update_data = '.print_r($object_update_data, true).'');
                //debug_echo('condition = '.print_r(array($object_token_field => $object_token_field_value), true).'');
                // Update the database object with above value given the object token
                $db->update($object_table_name, $object_update_data, array($object_token_field => $object_token_field_value));
            }
        }
    }

}

// Assuming we got this far, we can print a success message
$num_reverted = count($revert_tokens);
$success_kind = !empty($request_subkind) ? $request_subkind : $request_kind;
$success_kind_singular = !empty($request_subkind_singular) ? $request_subkind_singular : $request_kind_singular;
if ($request_token === 'all'){ exit_action('success|Changes to all '.$success_kind.' were reverted!'); }
else { exit_action('success|Changes to '.($num_reverted === 1 ? ('this '.$success_kind_singular) : ($num_reverted.' '.$success_kind)).' were reverted!'); }

?>