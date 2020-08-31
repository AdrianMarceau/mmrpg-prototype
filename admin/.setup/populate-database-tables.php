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

// Define a quick function for immediately printing an echo statement
function ob_echo($echo, $silent = false){ if (!$silent){ echo($echo.PHP_EOL); } ob_flush(); }

// Define a quick function for executing a shell command and printing the output
function ob_echo_shell_exec($cmd){ ob_echo('$ '.$cmd); $output = shell_exec($cmd); ob_echo($output); }

// Define a function for cleaning a path of the root dir for printing
function clean_path($path){ return str_replace(MMRPG_CONFIG_ROOTDIR, '/', $path); }

// Print the script header for display purposes
ob_echo('');
ob_echo('============================');
ob_echo('| POPULATE DATABASE TABLES |');
ob_echo('============================');
ob_echo('');

// Check to see which types of population we're allowed to do during this run
$allow_import_sql = isset($_REQUEST['import_sql']) && $_REQUEST['import_sql'] === 'false' ? false : true;
$allow_import_json = isset($_REQUEST['import_json']) && $_REQUEST['import_json'] === 'false' ? false : true;
$allow_post_setup = isset($_REQUEST['post_setup']) && $_REQUEST['post_setup'] === 'false' ? false : true;

// -- IMPORT SQL FILES -- //

if ($allow_import_sql){

    ob_echo('IMPORT SQL FILES:');
    ob_echo('');

    // Collect a list of all the seed data for the database tables
    $sql_data_dir = MMRPG_CONFIG_SQL_CONTENT_PATH.'data/';
    $sql_data_files = scandir($sql_data_dir);
    $sql_data_files = array_filter($sql_data_files, function($f){ if ($f !== '.' && $f !== '..' && substr($f, -4, 4) === '.sql'){ return true; } else { return false; } });

    // Collect another list of all the sample data for the database tables
    $sample_data_dir = $setup_dir.'sample-data/';
    $sample_data_files = scandir($sample_data_dir);
    $sample_data_files = array_filter($sample_data_files, function($f){ if ($f !== '.' && $f !== '..' && substr($f, -4, 4) === '.sql'){ return true; } else { return false; } });

    // Check to make sure seed data for the tables was actually collected
    if (!empty($sql_data_files)){

        // Print out the list of tables that will be created
        ob_echo('SQL import data was found for the following database tables:');
        ob_echo('- '.implode(PHP_EOL.'- ', $sql_data_files));
        ob_echo('- '.implode(PHP_EOL.'- ', $sample_data_files));
        ob_echo('');

        // Loop through the data files and import them into the database
        ob_echo('Looping through SQL data files and importing into database tables:');
        function mmrpg_setup_import_sql_files($data_files, $data_dir){
            global $db;
            foreach ($data_files AS $sql_key => $sql_file){
                $table_name = str_replace('.sql', '', $sql_file);
                $data_check_sql = "SELECT 1 FROM {$table_name} LIMIT 1;";
                $echo_text = '- Importing seed data for database table "'.$table_name.'" ... ';
                if (empty($db->get_array($data_check_sql))){
                    $db->import_sql_file($data_dir.$sql_file); // attempt to import the file
                    if (!empty($db->get_array($data_check_sql))){ $echo_text .= 'Data imported!'; }
                    else { $echo_text .= 'Data NOT imported!'; }
                } else {
                    $echo_text .= 'Data already exists!';
                }
                ob_echo($echo_text);
            }
        }
        mmrpg_setup_import_sql_files($sql_data_files, $sql_data_dir);
        mmrpg_setup_import_sql_files($sample_data_files, $sample_data_dir);
        ob_echo('');

    } else {

        // Print out an error message as nothing was found
        ob_echo('Unable to find any data files in '.clean_path($sql_data_dir).'...');
        ob_echo('');

    }

    ob_echo('----------------------------');
    ob_echo('');

}


// -- IMPORT JSON FILES -- //

if ($allow_import_json){

    ob_echo('IMPORT JSON FILES:');
    ob_echo('');

    // Pre-collect a list of contributors so we can match usernames to IDs later
    $contributor_fields = rpg_user::get_contributor_index_fields(true);
    $contributor_index = $db->get_array_list("SELECT {$contributor_fields} FROM mmrpg_users_contributors ORDER BY contributor_id ASC;", 'contributor_id');
    $contributor_usernames_to_ids = array();
    foreach ($contributor_index AS $key => $data){ $contributor_usernames_to_ids[$data['user_name_clean']] = $data['contributor_id']; }
    $contributor_field_pattern = '/^([a-z0-9]+)_image_editor([0-9]+)?$/i';
    ob_echo('The following contributor usernames-to-IDs will be used:');
    foreach ($contributor_usernames_to_ids AS $username => $id){ ob_echo($username.' => '.$id); }
    ob_echo('');

    // Loop through the content types one-by-one to check for JSON files
    foreach ($content_types_index AS $content_key => $content_info){

        // Collect refs to the content type tokens, table, etc.
        $ctype_token = $content_info['token'];
        $ctype_xtoken = $content_info['xtoken'];
        $ctype_table_name = $content_info['database_table'];

        // If this content doesn't have a table, skip it
        if (empty($ctype_table_name)){ continue; }

        // Define field names for later usage
        $id_field_name = $ctype_token.'_id';
        $token_field_name = $ctype_token.'_token';
        $parent_id_field_name = 'parent_id';
        $parent_token_field_name = 'parent_token';

        // Collect a list of all the seed data for the database tables
        $json_data_dir = MMRPG_CONFIG_CONTENT_PATH.$content_info['content_path'];
        $json_data_dirs = scandir($json_data_dir);
        $json_data_dirs = array_filter($json_data_dirs, function($d) use($json_data_dir){ if ($d !== '.' && $d !== '..' && file_exists($json_data_dir.$d.'/data.json')){ return true; } else { return false; } });

        // We made it this far, so let's truncate existing data from the table
        $truncate_sql = "TRUNCATE TABLE {$ctype_table_name};";
        $db->query($truncate_sql);

        // Check to make sure seed data for the tables was actually collected
        if (!empty($json_data_dirs)){

            // Print out the list of tables that will be created
            ob_echo('JSON import data was found for the following '.$ctype_xtoken.':');
            ob_echo('- '.implode(PHP_EOL.'- ', $json_data_dirs));
            ob_echo('');

            // Define an index to keep track of which tokens are associated with which IDs
            if ($content_info['primary_key'] === 'token'){
                $token_to_id_index = array();
                $child_needs_parent_for_token = array();
            }

            // Loop through the data files and import them into the database
            ob_echo('Looping through JSON data files and importing into database table "'.$ctype_table_name.'":');
            foreach ($json_data_dirs AS $object_key => $object_git_token){
                // Skip if this is just a filler object/file and not token-related
                if ($content_info['primary_key'] !== 'token' && substr($object_git_token, 0, 1) === '.'){ continue; }
                // Open the json file and decode it's contents to collect details
                $json_file = $object_git_token.'/data.json';
                $json_markup = file_get_contents($json_data_dir.$json_file);
                $json_data = json_decode($json_markup, true);
                foreach ($json_data AS $f => $v){ if (is_array($v)){ $json_data[$f] = !empty($v) ? json_encode($v, JSON_NUMERIC_CHECK) : ''; } }
                // Collect the primary key value for this object and generate echo text
                $real_object_pk = $ctype_token.'_'.$content_info['primary_key'];
                $real_object_token = $json_data[$real_object_pk];
                $echo_text = '- Importing '.$ctype_token.' data for "'.$real_object_token.'" ';
                if ($object_git_token !== $real_object_token){ $echo_text .= '('.$object_git_token.') '; }
                $echo_text .= 'into database table "'.$ctype_table_name.'" ... ';
                // If there is an html content file, we should import that and include with data
                if ($ctype_xtoken === 'pages'){
                    $html_file = $object_git_token.'/content.html';
                    if (file_exists($json_data_dir.$html_file)){
                        $html_markup = file_get_contents($json_data_dir.$html_file);
                        if (!empty($html_markup)){ $json_data[$ctype_token.'_content'] = trim($html_markup).PHP_EOL; }
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
                        if ($content_info['primary_key'] === 'token'){
                            $token_to_id_index[$real_object_token] = $data_check_return;
                            if (!empty($temp_child_to_parent_info)){ $child_needs_parent_for_token[] = $temp_child_to_parent_info; }
                        }
                    } else {
                        $echo_text .= 'Data NOT imported!';
                    }
                } else {
                    $echo_text .= 'Data already exists w/ '.$id_field_name.'='.$data_check_return.'!';
                    if ($content_info['primary_key'] === 'token'){ $token_to_id_index[$real_object_token] = $data_check_return; }
                }
                ob_echo($echo_text);
            }
            ob_echo('');

            // If there were child-to-parent associations, we need to loop through and update the database
            if ($content_info['primary_key'] === 'token'){
                if (!empty($child_needs_parent_for_token)){
                    ob_echo('Looping through child-to-parent associations and updating database table rows:');
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

                        ob_echo($echo_text);

                    }
                }
            }

        } else {

            // Print out an error message as nothing was found
            ob_echo('Unable to find any data files in '.clean_path($json_data_dir).'...');
            ob_echo('');

        }

    }

    ob_echo('----------------------------');
    ob_echo('');

}


// -- POST-MIGRATION SETUP QUERIES -- //

if ($allow_post_setup){

    ob_echo('POST-MIGRATION SETUP QUERIES:');
    ob_echo('');

    // Switch the meaning of image editor IDs in objects from user to contributor
    ob_echo('Updating global config value for `image_editor_id_field` to "contributor_id" ...');
    $db->update('mmrpg_config', array('config_value' => 'contributor_id'), array('config_group' => 'global', 'config_name' => 'image_editor_id_field'));
    ob_echo('');

    // Update global config value with current timestamp
    ob_echo('Updating global config values for `cache_date` and `cache_time` to right now ...');
    $db->update('mmrpg_config', array('config_value' => date('Ymd')), array('config_group' => 'global', 'config_name' => 'cache_date'));
    $db->update('mmrpg_config', array('config_value' => date('Hi')), array('config_group' => 'global', 'config_name' => 'cache_time'));
    ob_echo('');

    ob_echo('----------------------------');
    ob_echo('');

}

ob_echo('...Done!');
ob_echo('');

exit();

?>