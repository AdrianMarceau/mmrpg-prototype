<?

// Require the top file for all admin scripts
require_once('common/top.php');

// Require the git parameters file so we know which pulls are allowed
require_once('common/git-params.php');

// Require the global content type index for reference
require_once(MMRPG_CONFIG_CONTENT_PATH.'index.php');

// If this is the first run, we should queue up content updates and wait for a refresh
if (empty($_GET['complete']) || $_GET['complete'] !== 'true'){

    // Loop through the content types one-by-one and queue pulling updates for each
    session_write_close();
    foreach ($content_types_index AS $content_key => $content_type_info){

        // Collect the content kind as we'll use it a lot
        $content_kind = $content_type_info['xtoken'];

        // If this is not an allowed kind, skip now
        if (!in_array($content_kind, $allowed_kinds)){ continue; }

        // Append this content directory to the git update queue
        $file_token = "git-pull";
        $project_path = MMRPG_CONFIG_CONTENT_PATH.$content_type_info['content_path'];
        $project_path_clean = (MMRPG_CONFIG_IS_LIVE === true ? str_replace(MMRPG_CONFIG_ROOTDIR, '/', $project_path) : $project_path);
        echo('$ '.$file_token.' '.$project_path_clean.' '.PHP_EOL);
        queue_git_updates($file_token, $project_path);

    }

    // If the request was made via a regular browser tab, print out javascript status checker
    if ($return_kind === 'html'){
        print_cron_status_checker('git-pull', true, true);
    }

    // Print the success message with the returned output
    exit_action('success|MMRPG Game Content Updates Have Been Queued');

}
// Otherwise, we can run any post-update functionality now that pulling is complete
elseif ($_GET['complete'] === 'true') {

    // Pre-collect a list of contributors so we can match usernames to IDs later
    $contributor_fields = rpg_user::get_contributor_index_fields(true, 'contributors');
    if (MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD === 'contributor_id'){ $contributor_sql = "SELECT {$contributor_fields} FROM mmrpg_users_contributors AS contributors ORDER BY contributors.contributor_id ASC;"; }
    elseif (MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD === 'user_id'){ $contributor_sql = "SELECT {$contributor_fields}, users.user_id FROM mmrpg_users_contributors AS contributors LEFT JOIN mmrpg_users AS users ON users.user_name_clean = contributors.user_name_clean ORDER BY users.user_id ASC;"; }
    $contributor_index = $db->get_array_list($contributor_sql, MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD);
    $contributor_usernames_to_ids = array();
    foreach ($contributor_index AS $key => $data){ $contributor_usernames_to_ids[$data['user_name_clean']] = $data[MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD]; }
    $contributor_field_pattern = '/^([a-z0-9]+)_image_editor([0-9]+)?$/i';
    //debug_echo('$contributor_usernames_to_ids = '.print_r($contributor_usernames_to_ids, true).'');

    // Loop through the content types one-by-one and queue pulling updates for each
    session_write_close();
    $request_kind = '';
    $request_kind_singular = '';
    foreach ($content_types_index AS $content_key => $content_type_info){

        // Collect refs to the content type tokens, table, etc.
        $request_kind = $content_type_info['xtoken'];
        $request_kind_singular = $content_type_info['token'];
        $ctype_token = $content_type_info['token'];
        $ctype_xtoken = $content_type_info['xtoken'];
        $ctype_table_name = $content_type_info['database_table'];
        //debug_echo('$ctype_token = '.print_r($ctype_token, true).'');
        //debug_echo('$ctype_xtoken = '.print_r($ctype_xtoken, true).'');
        //debug_echo('$ctype_table_name = '.print_r($ctype_table_name, true).'');

        // Define field names for later usage
        $id_field_name = $ctype_token.'_id';
        $token_field_name = $ctype_token.'_token';
        $parent_id_field_name = 'parent_id';
        $parent_token_field_name = 'parent_token';
        //debug_echo('$id_field_name = '.print_r($id_field_name, true).'');
        //debug_echo('$token_field_name = '.print_r($token_field_name, true).'');
        //debug_echo('$parent_id_field_name = '.print_r($parent_id_field_name, true).'');
        //debug_echo('$parent_token_field_name = '.print_r($parent_token_field_name, true).'');

        // If this content type doesn't have a db table to update, we should skip this one
        if (empty($ctype_table_name)){
            echo('All "'.$request_kind.'" data files are up to date.'.PHP_EOL);
            continue;
        }

        // Collect a list of all the seed data for the database tables
        $json_data_dir = MMRPG_CONFIG_CONTENT_PATH.$content_type_info['content_path'];
        //debug_echo('$json_data_dir = '.print_r($json_data_dir, true).'');
        $json_data_dirs = scandir($json_data_dir);
        $json_data_dirs = array_filter($json_data_dirs, function($d) use($json_data_dir){ if ($d !== '.' && $d !== '..' && file_exists($json_data_dir.$d.'/data.json')){ return true; } else { return false; } });
        //debug_echo('$json_data_dir = '.print_r($json_data_dir, true).'');
        //debug_echo('$json_data_dirs = '.print_r($json_data_dirs, true).'');

        // If this content type doesn't have any JSON files to import, we should skip this one
        if (empty($json_data_dirs)){
            //exit_action('error|Request kind "'.$request_kind.'" had no file directories to import');
            //debug_echo('Request kind "'.$request_kind.'" had no file directories to import');
            echo('All "'.$request_kind.'" data files are up to date.'.PHP_EOL);
            continue;
        }

        // Otherwise, we can state that we are parsing this content type
        echo('Parsing "'.$request_kind.'" data files for new content...'.PHP_EOL);

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
            debug_echo($echo_text);
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

        // Otherwise, we can state that we are parsing this content type
        echo('...done!'.PHP_EOL);


    }

    // Update the global cache timestamp to ensure things are refreshed
    $cache_date = date('Ymd');
    $cache_time = date('Hi');
    $db->update('mmrpg_config', array('config_value' => $cache_date), array('config_group' => 'global', 'config_name' => 'cache_date'));
    $db->update('mmrpg_config', array('config_value' => $cache_time), array('config_group' => 'global', 'config_name' => 'cache_time'));

    // We are not done so we can print the cache date and time
    echo('MMRPG is now on version '.$cache_date.'-'.$cache_time.PHP_EOL);

    // Print the success message with the returned output
    exit_action('success|MMRPG Game Content Has Been Updated');

}

?>