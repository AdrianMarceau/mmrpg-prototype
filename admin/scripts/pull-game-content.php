<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../../top.php');
require_once(MMRPG_CONFIG_ROOTDIR.'classes/cms_admin.php');

// Require the common git actions file
$request_action = 'update';
$allow_empty_subkind = true;
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/common_git_header.php');
//debug_echo('push-game-content'.PHP_EOL);

// Only the "all" request is supported for updating
if ($request_token !== 'all'){ exit_action('error|Only the "all" request type is supported for updates!'); }

// Collect an index of changes files via git
$mmrpg_git_path = constant('MMRPG_CONFIG_'.strtoupper($request_kind).'_CONTENT_PATH');
//debug_echo('$mmrpg_git_path = '.$mmrpg_git_path);

// Pre-collect object name kinds for later commit messages
$object_name_kind = !empty($request_subkind) ? $request_subkind : $request_kind;
$object_name_kind_singular = !empty($request_subkind_singular) ? $request_subkind_singular : $request_kind_singular;
//debug_echo('$object_name_kind = '.print_r($object_name_kind, true).'');
//debug_echo('$object_name_kind_singular = '.print_r($object_name_kind_singular, true).'');

// Navigate to the git repo and run a git pull to collect updates
$git_commands = '';
$git_commands .= 'cd '.$mmrpg_git_path.' ';
$git_commands .= '&& git pull -s recursive -X theirs --no-edit 2>&1';
//debug_echo('$git_commands = '.print_r($git_commands, true).'');
$git_output = shell_exec($git_commands);
//debug_echo('$git_output = '.print_r($git_output, true).'');

// Pre-collect a list of contributors so we can match usernames to IDs later
$contributor_fields = rpg_user::get_contributor_index_fields(true, 'contributors');
if (MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD === 'contributor_id'){ $contributor_sql = "SELECT {$contributor_fields} FROM mmrpg_users_contributors AS contributors ORDER BY contributors.contributor_id ASC;"; }
elseif (MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD === 'user_id'){ $contributor_sql = "SELECT {$contributor_fields}, users.user_id FROM mmrpg_users_contributors AS contributors LEFT JOIN mmrpg_users AS users ON users.user_name_clean = contributors.user_name_clean ORDER BY users.user_id ASC;"; }
$contributor_index = $db->get_array_list($contributor_sql, MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD);
$contributor_usernames_to_ids = array();
foreach ($contributor_index AS $key => $data){ $contributor_usernames_to_ids[$data['user_name_clean']] = $data[MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD]; }
$contributor_field_pattern = '/^([a-z0-9]+)_image_editor([0-9]+)?$/i';
//debug_echo('$contributor_usernames_to_ids = '.print_r($contributor_usernames_to_ids, true).'');

// Collect refs to the content type tokens, table, etc.
$ctype_token = $content_type_info['token'];
$ctype_xtoken = $content_type_info['xtoken'];
$ctype_table_name = $content_type_info['database_table'];
//debug_echo('$ctype_token = '.print_r($ctype_token, true).'');
//debug_echo('$ctype_xtoken = '.print_r($ctype_xtoken, true).'');

// Define field names for later usage
$id_field_name = $ctype_token.'_id';
$token_field_name = $ctype_token.'_token';
$parent_id_field_name = 'parent_id';
$parent_token_field_name = 'parent_token';
//debug_echo('$id_field_name = '.print_r($id_field_name, true).'');
//debug_echo('$token_field_name = '.print_r($token_field_name, true).'');
//debug_echo('$parent_id_field_name = '.print_r($parent_id_field_name, true).'');
//debug_echo('$parent_token_field_name = '.print_r($parent_token_field_name, true).'');

// Collect a list of all the seed data for the database tables
$json_data_dir = MMRPG_CONFIG_CONTENT_PATH.$content_type_info['content_path'];
$json_data_dirs = scandir($json_data_dir);
$json_data_dirs = array_filter($json_data_dirs, function($d) use($json_data_dir){ if ($d !== '.' && $d !== '..' && file_exists($json_data_dir.$d.'/data.json')){ return true; } else { return false; } });
if (empty($json_data_dirs)){ exit_action('error|Request kind "'.$request_kind.'" had no file directories to import'); }
//debug_echo('$json_data_dir = '.print_r($json_data_dir, true).'');
//debug_echo('$json_data_dirs = '.print_r($json_data_dirs, true).'');

// We made it this far, so let's truncate existing data from the table
$truncate_sql = "TRUNCATE TABLE {$ctype_table_name};";
//debug_echo('$truncate_sql = '.print_r($truncate_sql, true).'');
$db->query($truncate_sql);

// Define an index to keep track of which tokens are associated with which IDs
if ($content_type_info['primary_key'] === 'token'){
    $token_to_id_index = array();
    $child_needs_parent_for_token = array();
}

// Loop through the data files and import them into the database
//debug_echo('Looping through JSON data files and importing into database table "'.$ctype_table_name.'":');
foreach ($json_data_dirs AS $object_key => $object_git_token){
    // Skip if this is just a filler object/file and not token-related
    if ($content_type_info['primary_key'] !== 'token' && substr($object_git_token, 0, 1) === '.'){ continue; }
    // Open the json file and decode it's contents to collect details
    $json_file = $object_git_token.'/data.json';
    $json_markup = file_get_contents($json_data_dir.$json_file);
    $json_data = json_decode($json_markup, true);
    foreach ($json_data AS $f => $v){ if (is_array($v)){ $json_data[$f] = !empty($v) ? json_encode($v, JSON_NUMERIC_CHECK) : ''; } }
    // Collect the primary key value for this object and generate echo text
    $real_object_pk = $ctype_token.'_'.$content_type_info['primary_key'];
    $real_object_token = $json_data[$real_object_pk];
    $echo_text = '- Importing '.$ctype_token.' data for "'.$real_object_token.'" ';
    if ($object_git_token !== $real_object_token){ $echo_text .= '('.$object_git_token.') '; }
    $echo_text .= 'into database table "'.$ctype_table_name.'" ... ';
    // If there is an html content file, we should import that and include with data
    if ($request_kind === 'pages'){
        $html_file = $object_git_token.'/content.html';
        if (file_exists($json_data_dir.$html_file)){
            $html_markup = file_get_contents($json_data_dir.$html_file);
            if (!empty($html_markup)){ $json_data[$request_kind_singular.'_content'] = trim($html_markup).PHP_EOL; }
        }
    }
    // Check if this the json data has a parent_id set that needs translated to an object ID later
    $temp_child_to_parent_info = false;
    if (isset($json_data[$parent_token_field_name])){
        $child_token_field_value = $json_data[$token_field_name];
        $parent_token_field_value = $json_data[$parent_token_field_name];
        if (!empty($parent_token_field_value)){
            $temp_child_to_parent_info = array(
                'child_token' => $child_token_field_value,
                'parent_token' => $parent_token_field_value
                );
        }
        unset($json_data[$parent_token_field_name]);
    }
    // Check if there are image editor usernames that need ot be translated to contributor IDs
    foreach ($json_data AS $jkey => $jvalue){
        if (preg_match($contributor_field_pattern, $jkey)){
            if (!empty($jvalue) && isset($contributor_usernames_to_ids[$jvalue])){ $json_data[$jkey] = $contributor_usernames_to_ids[$jvalue]; }
            else { $json_data[$jkey] = 0; }
        }
    }
    // Now check to see if the data exists in the db already and insert if it doesn't exist yet
    $data_check_sql = "SELECT {$id_field_name} FROM {$ctype_table_name} WHERE {$real_object_pk} = '{$real_object_token}';";
    $data_check_return = $db->get_value($data_check_sql, $id_field_name);
    if (empty($data_check_return)){
        $db->insert($ctype_table_name, $json_data); // attempt to insert the data
        $data_check_return = $db->get_value($data_check_sql, $id_field_name);
        if (!empty($data_check_return)){
            $echo_text .= 'Data imported w/ '.$id_field_name.'='.$data_check_return.'!';
            if ($content_type_info['primary_key'] === 'token'){
                $token_to_id_index[$real_object_token] = $data_check_return;
                if (!empty($temp_child_to_parent_info)){ $child_needs_parent_for_token[] = $temp_child_to_parent_info; }
            }
        } else {
            $echo_text .= 'Data NOT imported!';
        }
    } else {
        $echo_text .= 'Data already exists w/ '.$id_field_name.'='.$data_check_return.'!';
        if ($content_type_info['primary_key'] === 'token'){ $token_to_id_index[$real_object_token] = $data_check_return; }
    }
    //debug_echo($echo_text);
}

// If there were child-to-parent associations, we need to loop through and update the database
if ($content_type_info['primary_key'] === 'token'){
    //debug_echo('$token_to_id_index = '.print_r($token_to_id_index, true).'');
    //debug_echo('$child_needs_parent_for_token = '.print_r($child_needs_parent_for_token, true).'');
    if (!empty($child_needs_parent_for_token)){
        //debug_echo('Looping through child-to-parent associations and updating database table rows:');
        foreach ($child_needs_parent_for_token AS $key => $tokens){

            $child_token = $tokens['child_token'];
            $child_id = isset($token_to_id_index[$child_token]) ? $token_to_id_index[$child_token] : false;
            $parent_token = $tokens['parent_token'];
            $parent_id = isset($token_to_id_index[$parent_token]) ? $token_to_id_index[$parent_token] : false;

            $echo_text = '- Associating child '.$ctype_token.' ';
                $echo_text .= '('.$child_token.'/'.($child_id !== false ? $child_id : 'null').') ';
                $echo_text .= 'to parent '.$ctype_token.' ';
                $echo_text .= '('.$parent_token.'/'.($parent_id !== false ? $parent_id : 'null').') ... ';

            if ($child_id !== false && $parent_id !== false){
                $db->update($ctype_table_name, array($parent_id_field_name => $parent_id), array($id_field_name => $child_id));
                $data_check_sql = "SELECT {$id_field_name} FROM {$ctype_table_name} WHERE {$id_field_name} = {$child_id} AND {$parent_id_field_name} = {$parent_id};";
                if (!empty($db->get_value($data_check_sql, $id_field_name))){
                    $echo_text .= 'Data updated!';
                } else {
                    $echo_text .= 'Data NOT updated!';
                }
            } else {
                $echo_text .= 'Data missing one or both ID(s)!';
            }

            //debug_echo($echo_text);

        }
    }
}

// Check to see if a groups folder exists and has data
$groups_data_dir = $json_data_dir.'_groups/';
$groups_data_dirs = file_exists($groups_data_dir) ? scandir($groups_data_dir) : array();
$groups_data_dirs = array_filter($groups_data_dirs, function($d) use($groups_data_dir){ if ($d !== '.' && $d !== '..' && file_exists($groups_data_dir.$d.'/data.json')){ return true; } else { return false; } });
//debug_echo('$groups_data_dir = '.print_r($groups_data_dir, true));
//debug_echo('$groups_data_dirs = '.print_r($groups_data_dirs, true));

// Check to make sure group data was actually collected
if (!empty($groups_data_dirs)){

    // Print out the list of tables that will be created
    //debug_echo('JSON object group import data was found for the following classes:');
    //debug_echo('- '.implode(PHP_EOL.'- ', $groups_data_dirs));

    // Define the group/token table names to populate
    $ctype_group_table_name = $ctype_table_name.'_groups';
    $ctype_group_token_table_name = $ctype_table_name.'_groups_tokens';
    //debug_echo('$ctype_group_table_name = '.print_r($ctype_group_table_name, true));
    //debug_echo('$ctype_group_token_table_name = '.print_r($ctype_group_token_table_name, true));

    // We made it this far, so let's truncate existing group/token from the tables
    $db->query("TRUNCATE TABLE {$ctype_group_table_name};");
    $db->query("TRUNCATE TABLE {$ctype_group_token_table_name};");

    // Loop through the data files and import them into the database
    //debug_echo('Looping through JSON group data files and importing into database tables "'.$ctype_group_table_name.'" and "'.$ctype_group_token_table_name.'":');
    $object_groups = array();
    foreach ($groups_data_dirs AS $group_class){
        // Open the json file and decode it's contents to collect details
        $json_file = $group_class.'/data.json';
        $json_markup = file_get_contents($groups_data_dir.$json_file);
        $json_data = json_decode($json_markup, true);
        // Append this group list to the parent array
        $group_list = $json_data;
        $object_groups[$group_class] = $group_list;
        //debug_echo('- Importing data and tokens for "'.$group_class.'" sort groups');
    }
    //debug_echo('$object_groups = '.print_r($object_groups, true));

    // If not empty, save the groups to the database
    if (!empty($object_groups)){ cms_admin::save_object_groups_to_database($object_groups, $ctype_token); }

}

// Update the global cache timestamp to ensure things are refreshed
$db->update('mmrpg_config', array('config_value' => date('Ymd')), array('config_group' => 'global', 'config_name' => 'cache_date'));
$db->update('mmrpg_config', array('config_value' => date('Hi')), array('config_group' => 'global', 'config_name' => 'cache_time'));

// Assuming we got this far, we can print a success message
$success_kind = !empty($request_subkind) ? $request_subkind : $request_kind;
exit_action('success|Changes to '.$success_kind.' were pulled and updated successfully!');

?>