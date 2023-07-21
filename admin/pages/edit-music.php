<? ob_start(); ?>

    <?

    //<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/js/main.js"></script>

    // Require the music track class for this page only
    require_once(MMRPG_CONFIG_ROOTDIR.'classes/rpg_music_track.php');

    // Pre-check access permissions before continuing
    if (!rpg_user::current_user_has_permission('edit-music')){
        $form_messages[] = array('error', 'You do not have permission to edit music!');
        redirect_form_action('admin/home/');
    }

    /* -- Collect Dependant Indexes -- */

    // Collect indexes for required object types
    $mmrpg_sources_index = rpg_game::get_source_index();

    // Collect an index of all existing music from the database for reference
    $mmrpg_music_dbname = MMRPG_CONFIG_CDN_DBNAME;
    $mmrpg_music_fields = rpg_music_track::get_index_fields(true);
    $mmrpg_music_index = $db->get_array_list("SELECT {$mmrpg_music_fields} FROM {$mmrpg_music_dbname}.mmrpg_index_music WHERE music_id <> 0;", 'music_id');

    // Collect a list of all uploaded music files form the server for reference
    $mmrpg_music_path = 'prototype/sounds/';
    $mmrpg_music_rootdir = MMRPG_CONFIG_CDN_ROOTDIR.$mmrpg_music_path;
    $mmrpg_music_rooturl = MMRPG_CONFIG_CDN_ROOTURL.$mmrpg_music_path;
    $mmrpg_music_file_types = array('mp3', 'ogg');
    $mmrpg_music_file_types_mime = array('mp3' => 'audio/mpeg', 'ogg' => 'audio/ogg');
    $mmrpg_music_file_list = array();
    if (is_dir($mmrpg_music_rootdir)){
        $mmrpg_music_file_list = getDirContents($mmrpg_music_rootdir);
        if (!empty($mmrpg_music_file_list)){
            foreach ($mmrpg_music_file_list AS $key => $path){
                $name = basename($path);
                $is_file = preg_match('/\.([a-z0-9]{2,})$/i', $name, $ext) ? true : false;
                if (substr($name, 0, 1) === '.' || ($is_file && !in_array($ext[1], $mmrpg_music_file_types))){
                    unset($mmrpg_music_file_list[$key]);
                    continue;
                }
                $clean_path = str_replace($mmrpg_music_rootdir, '', $path);
                if (!$is_file){ $clean_path = rtrim($clean_path, '/').'/'; }
                $mmrpg_music_file_list[$key] = $clean_path;
            }
            $mmrpg_music_file_list = array_values($mmrpg_music_file_list);
        }
    }
    //error_log('$mmrpg_music_file_list ='.print_r($mmrpg_music_file_list, true));


    /* -- Generate Select Option Markup -- */

    // Pre-generate a list of all sources so we can re-use it over and over
    $last_option_group = false;
    $source_options_markup = array();
    $source_options_markup[] = '<option value="">-</option>';
    foreach ($mmrpg_sources_index AS $source_token => $source_info){
        $class_group = ucfirst($source_info['source_series']).' Series';
        if ($last_option_group !== $class_group){
            if (!empty($last_option_group)){ $source_options_markup[] = '</optgroup>'; }
            $last_option_group = $class_group;
            $source_options_markup[] = '<optgroup label="'.$class_group.'">';
        }
        $source_name = !empty($source_info['source_name']) ? $source_info['source_name'] : $source_info['source_name_aka'];
        $source_systems = !empty($source_info['source_systems']) ? $source_info['source_systems'] : 'Unknown';
        $source_options_markup[] = '<option value="'.$source_token.'">'.$source_name.' ('.$source_systems.')</option>';
    }
    if (!empty($last_option_group)){ $source_options_markup[] = '</optgroup>'; }
    $source_options_count = count($source_options_markup);
    $source_options_markup = implode(PHP_EOL, $source_options_markup);


    /* -- Page Script/Style Dependencies  -- */

    // Require codemirror scripts and styles for this page
    $admin_include_common_styles[] = 'howler';
    $admin_include_common_scripts[] = 'howler';


    /* -- Form Setup Actions -- */

    // Define a function for exiting a music edit action
    function exit_music_edit_action($music_id = false){
        if ($music_id !== false){ $location = 'admin/edit-music/editor/music_id='.$music_id; }
        else { $location = 'admin/edit-music/search/'; }
        redirect_form_action($location);
    }

    /* -- Admin Submusic Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the music name
    $this_page_tabtitle = 'Edit Music Tracks | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['music_id'])){

        // Collect form data for processing
        $delete_data['music_id'] = !empty($_GET['music_id']) && is_numeric($_GET['music_id']) ? trim($_GET['music_id']) : '';

        // Collect DB details so we know where the files are
        $delete_data = $db->get_array("SELECT {$mmrpg_music_fields} FROM {$mmrpg_music_dbname}.mmrpg_index_music WHERE music_id = {$delete_data['music_id']};");
        $this_music_path = $delete_data['music_album'].'/'.$delete_data['music_token'].'/';
        $this_music_dir = $mmrpg_music_rootdir.$this_music_path;
        if (file_exists($this_music_dir)){
            $shell_cmd = 'rm -rf "'.$this_music_dir.'"';
            $shell_output = shell_exec($shell_cmd);
            //error_log($shell_cmd.PHP_EOL.$shell_output);
        }

        // Let's delete all of this music's data from the database
        $db->delete($mmrpg_music_dbname.'.mmrpg_index_music', array('music_id' => $delete_data['music_id']));
        $form_messages[] = array('success', 'The requested music track has been deleted from the database');
        exit_form_action('success');

    }

    // If we're in search mode, we might need to scan for results
    $search_data = array();
    $search_query = '';
    $search_results = array();
    $search_results_count = 0;
    $search_results_limit = 300;
    if ($sub_action == 'search'){

        // Collect the sorting order and direction
        $sort_data = array('name' => 'music_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['music_id'] = !empty($_GET['music_id']) && is_numeric($_GET['music_id']) ? trim($_GET['music_id']) : '';
        $search_data['music_token'] = !empty($_GET['music_token']) && preg_match('/[-_0-9a-z]+/i', $_GET['music_token']) ? trim(strtolower($_GET['music_token'])) : '';
        $search_data['music_album'] = !empty($_GET['music_album']) && preg_match('/[-_0-9a-z]+/i', $_GET['music_album']) ? trim($_GET['music_album']) : '';
        $search_data['music_game'] = !empty($_GET['music_game']) && preg_match('/[-_0-9a-z]+/i', $_GET['music_game']) ? trim($_GET['music_game']) : '';
        $search_data['music_name'] = !empty($_GET['music_name']) ? trim($_GET['music_name']) : '';
        $search_data['music_link'] = !empty($_GET['music_link']) && preg_match('/^https?:\/\//i', $_GET['music_link']) ? trim($_GET['music_link']) : '';
        $search_data['music_flag_haslink'] = isset($_GET['music_flag_haslink']) && $_GET['music_flag_haslink'] !== '' ? (!empty($_GET['music_flag_haslink']) ? 1 : 0) : '';
        $search_data['music_flag_hasloop'] = isset($_GET['music_flag_hasloop']) && $_GET['music_flag_hasloop'] !== '' ? (!empty($_GET['music_flag_hasloop']) ? 1 : 0) : '';
        $search_data['music_flag_hasfiles'] = isset($_GET['music_flag_hasfiles']) && $_GET['music_flag_hasfiles'] !== '' ? (!empty($_GET['music_flag_hasfiles']) ? 1 : 0) : '';

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_now_date = date('Y-m-d');
        $temp_music_fields = rpg_music_track::get_index_fields(true, 'music');
        $search_query = "SELECT
            {$temp_music_fields},
            CONCAT(music.music_album, '/', music.music_token) AS music_path,
            CONCAT(music.legacy_music_album, '/', music.legacy_music_token) AS legacy_music_path,
            (CASE WHEN music_link <> '' THEN 1 ELSE 0 END) AS music_flag_haslink,
            (CASE WHEN music_loop <> '' THEN 1 ELSE 0 END) AS music_flag_hasloop,
            0 AS music_flag_hasfiles
            FROM {$mmrpg_music_dbname}.mmrpg_index_music AS music
            WHERE 1=1
            AND music.music_id <> 0
            ";

        // If the music ID was provided, we can search by exact match
        if (!empty($search_data['music_id'])){
            $music_id = $search_data['music_id'];
            $search_query .= "AND music_id = {$music_id} ";
            $search_results_limit = false;
        }

        // Else if the music token was provided, we can use wildcards
        if (!empty($search_data['music_token'])){
            $music_token = $search_data['music_token'];
            $music_token = str_replace(array(' ', '*', '%'), '%', $music_token);
            $music_token = preg_replace('/%+/', '%', $music_token);
            $music_token = '%'.$music_token.'%';
            $search_query .= "AND music_token LIKE '{$music_token}' ";
            $search_results_limit = false;
        }

        // Else if the music album was provided, we can use wildcards
        if (!empty($search_data['music_album'])){
            $music_album = $search_data['music_album'];
            if ($music_album !== 'none'){ $search_query .= "AND music_album = '{$music_album}' "; }
            else { $search_query .= "AND music_album = '' "; }
            $search_results_limit = false;
        }

        // Else if the music game was provided, we can use wildcards
        if (!empty($search_data['music_game'])){
            $music_game = $search_data['music_game'];
            if ($music_game !== 'none'){ $search_query .= "AND music_game = '{$music_game}' "; }
            else { $search_query .= "AND music_game = '' "; }
            $search_results_limit = false;
        }

        // Else if the music name was provided, we can use wildcards
        if (!empty($search_data['music_name'])){
            $music_name = $search_data['music_name'];
            $music_name = str_replace(array(' ', '*', '%'), '%', $music_name);
            $music_name = preg_replace('/%+/', '%', $music_name);
            $music_name = '%'.$music_name.'%';
            $search_query .= "AND music_name LIKE '{$music_name}' ";
            $search_results_limit = false;
        }

        // Else if the music link was provided, we can use wildcards
        if (!empty($search_data['music_link'])){
            $music_link = $search_data['music_link'];
            $music_link = str_replace(array(' ', '*', '%'), '%', $music_link);
            $music_link = preg_replace('/%+/', '%', $music_link);
            $music_link = '%'.$music_link.'%';
            $search_query .= "AND music_link LIKE '{$music_link}' ";
            $search_results_limit = false;
        }

        // If the music enabled flag was provided
        if ($search_data['music_flag_haslink'] !== ''){
            $flag_value = $search_data['music_flag_haslink'];
            if ($flag_value){ $search_query .= "AND music_link <> '' "; }
            else { $search_query .= "AND music_link = '' "; }
            $search_results_limit = false;
        }

        // If the music enabled flag was provided
        if ($search_data['music_flag_hasloop'] !== ''){
            $flag_value = $search_data['music_flag_hasloop'];
            if ($flag_value){ $search_query .= "AND music_loop <> '' "; }
            else { $search_query .= "AND music_loop = '' "; }
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "music_order DESC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string} ";

        // Impose a limit on the search results
        if (!empty($search_results_limit)){ $search_query .= "LIMIT {$search_results_limit} "; }

        // End the query now that we're done
        $search_query .= ";";

        // Collect search results from the database
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;

        // Loop through results and update the "hasfiles" flag given the files list
        if (!empty($search_results)){
            foreach ($search_results AS $key => $info){
                if (empty($info['music_path'])){ continue; }
                $hasfiles = 0;
                if (in_array($info['music_path'].'/audio.mp3', $mmrpg_music_file_list)){ $hasfiles += 1; }
                if (in_array($info['music_path'].'/audio.ogg', $mmrpg_music_file_list)){ $hasfiles += 1; }
                $search_results[$key]['music_flag_hasfiles'] = $hasfiles;
            }
        }

        //error_log('$search_results = '.print_r($search_results, true));

        // If the music enabled flag was provided
        if ($search_data['music_flag_hasfiles'] !== ''){
            $flag_value = $search_data['music_flag_hasfiles'];
             if (!empty($search_results)){
                foreach ($search_results AS $key => $info){
                    if (($flag_value && $info['music_flag_hasfiles'] == 0)
                        || (!$flag_value && $info['music_flag_hasfiles'] > 0)){
                        unset($search_results[$key]);
                    }
                }
                $search_results = array_values($search_results);
            }
            $search_results_limit = false;
        }

        // Collect a total number from the database
        $search_results_total = $db->get_value("SELECT COUNT(music_id) AS total FROM {$mmrpg_music_dbname}.mmrpg_index_music WHERE 1=1 AND music_id <> 0;", 'total');

    }

    // If we're in editor mode, we should collect music info from database
    $music_data = array();
    $music_data_is_new = false;
    $editor_data = array();
    if ($sub_action == 'editor'
        && isset($_GET['music_id'])
        ){

        // Collect form data for processing
        $editor_data['music_id'] = !empty($_GET['music_id']) && is_numeric($_GET['music_id']) ? trim($_GET['music_id']) : '';

        /* -- Collect Music Data -- */

        // Collect music details from the database
        $temp_music_fields = rpg_music_track::get_fields(true);
        if (!empty($editor_data['music_id'])){
            $music_data = $db->get_array("SELECT {$temp_music_fields} FROM {$mmrpg_music_dbname}.mmrpg_index_music WHERE music_id = {$editor_data['music_id']};");
        } else {

            // Generate temp data structure for the new challenge
            $music_data_is_new = true;
            $admin_id = $_SESSION['admin_id'];
            $music_data = array(
                'music_id' => 0,
                'music_token' => '',
                'music_album' => '',
                'music_game' => '',
                'music_name' => '',
                'music_link' => '',
                'music_loop' => '',
                'music_order' => 0,
                'legacy_music_token' => '',
                'legacy_music_album' => '',
                'legacy_music_game' => ''
                );

            // Overwrite temp data with any backup data provided
            if (!empty($backup_form_data)){
                foreach ($backup_form_data AS $f => $v){
                    $music_data[$f] = $v;
                }
            }

        }


        // If music data could not be found, produce error and exit
        if (empty($music_data)){ exit_music_edit_action(); }

        // Collect the music's name(s) for display
        $music_name_display = !empty($music_data['music_id']) ? $music_data['music_name'] : '';
        if ($music_data_is_new){ $this_page_tabtitle = 'Add New Music | '.$this_page_tabtitle; }
        else { $this_page_tabtitle = $music_name_display.' | '.$this_page_tabtitle; }

        // If form data has been submit for this music, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit-music'){

            // Collect form data from the request and parse out simple rules

            $form_data['music_id'] = !empty($_POST['music_id']) && is_numeric($_POST['music_id']) ? trim($_POST['music_id']) : 0;

            $old_music_token = !empty($_POST['old_music_token']) && preg_match('/[-_0-9a-z]+/i', $_POST['old_music_token']) ? trim(strtolower($_POST['old_music_token'])) : '';
            $old_music_album = !empty($_POST['old_music_album']) && preg_match('/[-_0-9a-z]+/i', $_POST['old_music_album']) ? trim(strtolower($_POST['old_music_album'])) : '';

            $form_data['music_token'] = !empty($_POST['music_token']) && preg_match('/[-_0-9a-z]+/i', $_POST['music_token']) ? trim(strtolower($_POST['music_token'])) : '';
            $form_data['music_album'] = !empty($_POST['music_album']) && preg_match('/[-_0-9a-z]+/i', $_POST['music_album']) ? trim(strtolower($_POST['music_album'])) : '';
            $form_data['music_game'] = !empty($_POST['music_game']) && preg_match('/[-_0-9a-z]+/i', $_POST['music_game']) ? trim($_POST['music_game']) : '';
            $form_data['music_name'] = !empty($_POST['music_name']) ? trim($_POST['music_name']) : '';
            $form_data['music_link'] = !empty($_POST['music_link']) && preg_match('/^https?:\/\//i', $_POST['music_link']) ? trim($_POST['music_link']) : '';
            $form_data['music_loop'] = !empty($_POST['music_loop']) && is_array($_POST['music_loop']) ? $_POST['music_loop'] : array();
            $form_data['music_order'] = !empty($_POST['music_order']) && is_numeric($_POST['music_order']) ? trim($_POST['music_order']) : 0;

            // If we're creating a new music, merge form data with the temp music data
            if (empty($form_data['music_id'])){ foreach ($form_data AS $f => $v){ $music_data[$f] = $v; } }

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');

            // If the required USER ID field was empty, complete form failure
            if (!$music_data_is_new && empty($form_data['music_id'])){
                $form_messages[] = array('error', 'Music ID was not provided');
                $form_success = false;
            }

            // If the required MUSIC TOKEN field was empty, complete form failure
            if (!$music_data_is_new && empty($old_music_token)){
                $form_messages[] = array('error', 'Original Music Token was not provided or was invalid');
                $form_success = false;
            } elseif (empty($form_data['music_token'])){
                $form_messages[] = array('error', 'Music Token was not provided or was invalid');
                $form_success = false;
            } else {
                $form_data['music_token'] = strtolower($form_data['music_token']);
                $form_data['music_token'] = preg_replace('/[^-a-z0-9]+/i', '-', $form_data['music_token']);
                $form_data['music_token'] = trim(preg_replace('/-+/i', '-', $form_data['music_token']), '-');
            }

            // If the required MUSIC ALBUM field was empty, complete form failure
            if (!$music_data_is_new && empty($old_music_album)){
                $form_messages[] = array('error', 'Original Music Album was not provided or was invalid');
                $form_success = false;
            } elseif (empty($form_data['music_album'])){
                $form_messages[] = array('error', 'Music Album was not provided or was invalid');
                $form_success = false;
            }

            // If the required MUSIC GAME field was empty, complete form failure
            if (empty($form_data['music_game'])){
                $form_messages[] = array('error', 'Music Game was not provided or was invalid');
                $form_success = false;
            }

            // If the required MUSIC NAME field was empty, complete form failure
            if (empty($form_data['music_name'])){
                $form_messages[] = array('error', 'Music Name was not provided or was invalid');
                $form_success = false;
            }

            // If there were errors, we should exit now
            if (!$form_success){ exit_music_edit_action($form_data['music_id']); }


            // REFORMAT or OPTIMIZE data for provided fields where necessary

            // Parse and reformat the MUSIC LOOP field
            $raw_loop_data = $form_data['music_loop'];
            $parsed_music_loop = array();
            $form_data['music_loop'] = '';
            if (!empty($raw_loop_data)
                && !empty($raw_loop_data['start'])
                && !empty($raw_loop_data['end'])){
                //error_log('we appear to have everything we need');
                //error_log('$raw_loop_data = '.print_r($raw_loop_data, true));
                $timestamp_regex = '/^([0-5]?\d):([0-5]?\d):([0-9]{1,2})$/';
                $parsed_music_loop['start'] = preg_match($timestamp_regex, $raw_loop_data['start']) ? get_milliseconds_from_timestamp($raw_loop_data['start']) : 0;
                $parsed_music_loop['end'] = preg_match($timestamp_regex, $raw_loop_data['end']) ? get_milliseconds_from_timestamp($raw_loop_data['end']) : 0;
                //error_log('$parsed_music_loop = '.print_r($parsed_music_loop, true));
                if (!empty($parsed_music_loop['start'])
                    && !empty($parsed_music_loop['end'])){
                    $form_data['music_loop'] = json_encode($parsed_music_loop);
                }
            }

            // Only parse the following fields if NOT new object data
            if (!$music_data_is_new){

                // ...

            }

            // Loop through fields to create an update string
            $update_data = $form_data;
            unset($update_data['music_id']);

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$update_data = '.print_r($update_data, true).'</pre>');

            // If this is a new music we insert, otherwise we update the existing
            if ($music_data_is_new){

                // Update the main database index with changes to this music's data
                $insert_results = $db->insert($mmrpg_music_dbname.'.mmrpg_index_music', $update_data);

                // If we made it this far, the update must have been a success
                if ($insert_results !== false){ $form_success = true; $form_messages[] = array('success', 'Music Track data was created successfully!'); }
                else { $form_success = false; $form_messages[] = array('error', 'Music Track data could not be created...'); }

                // If the form was a success, collect the new ID and redirect
                if ($form_success){
                    $new_music_id = $db->get_value("SELECT MAX(music_id) AS max FROM {$mmrpg_music_dbname}.mmrpg_index_music;", 'max');
                    $form_data['music_id'] = $new_music_id;
                }

            } else {

                // Update the main database index with changes to this music's data
                $update_results = $db->update($mmrpg_music_dbname.'.mmrpg_index_music', $update_data, array('music_id' => $form_data['music_id']));

                // If we made it this far, the update must have been a success
                if ($update_results !== false){ $form_messages[] = array('success', 'Music Track data was updated successfully!'); }
                else { $form_messages[] = array('error', 'Music Track data could not be updated...'); }

            }

            // If this is NOT new music data, we should check file-related actions now
            if ($form_success && !$music_data_is_new){

                // Preset variable to allow file actions by default
                $allow_file_actions = true;

                // Define the folder this track's files should be stored in
                $this_music_path = $update_data['music_album'].'/'.$update_data['music_token'].'/';
                $this_music_dir = $mmrpg_music_rootdir.$this_music_path;
                $this_music_url = $mmrpg_music_rooturl.$this_music_path;

                // If the old and new music tokens are not the same, move the folder
                if ($allow_file_actions
                    && ($old_music_token !== $update_data['music_token']
                        || $old_music_album !== $update_data['music_album'])){
                    //error_log('rename old music file path');
                    $old_music_path = $old_music_album.'/'.$old_music_token.'/';
                    $old_music_dir = $mmrpg_music_rootdir.$old_music_path;
                    if (file_exists($old_music_dir)){
                        $shell_cmd = 'mv -r "'.$old_music_dir.'" "'.$this_music_dir.'"';
                        $shell_output = shell_exec($shell_cmd);
                        //error_log($shell_cmd.PHP_EOL.$shell_output);
                        if (!file_exists($old_music_dir) && file_exists($this_music_dir)){
                            $form_messages[] = array('alert', 'Music directory successfully renamed ('.$old_music_path.' => '.$this_music_path.')');
                        } else {
                            $form_messages[] = array('error', 'There was a problem renaming the music directory...');
                            $allow_file_actions = false;
                        }
                    }
                }

                // Collect any uploaded files and make sure they're valid, add to array if approved
                $upload_music_files = array();
                if ($allow_file_actions && !empty($_FILES)){
                    //error_log('$_FILES = '.print_r($_FILES, true));
                    foreach ($mmrpg_music_file_types AS $type){
                        if (!isset($_FILES['music_files_'.$type])){ continue; }
                        $type_uc = strtoupper($type);
                        $type_regex = '/\.'.$type.'$/i';
                        $file = $_FILES['music_files_'.$type];
                        if (empty($file['name'])
                            || empty($file['type'])
                            || empty($file['tmp_name'])
                            || empty($file['size'])){
                            continue;
                        }
                        if (!isset($file['error'])){
                            $form_messages[] = array('warning', $type_uc.' file data structure invalid');
                            continue;
                        } elseif ($file['error'] !== UPLOAD_ERR_OK){
                            if ($file['error'] === UPLOAD_ERR_INI_SIZE){ $reason = 'too big / exceeds upload_max_filesize '.ini_get('upload_max_filesize'); }
                            elseif ($file['error'] === UPLOAD_ERR_FORM_SIZE){ $reason = 'too big / exceeds MAX_FILE_SIZE'; }
                            elseif ($file['error'] === UPLOAD_ERR_PARTIAL){ $reason = 'partial upload'; }
                            elseif ($file['error'] === UPLOAD_ERR_NO_FILE){ $reason = 'no file'; }
                            elseif ($file['error'] === UPLOAD_ERR_NO_TMP_DIR){ $reason = 'missing tmp dir'; }
                            elseif ($file['error'] === UPLOAD_ERR_CANT_WRITE){ $reason = 'cannot write to disk'; }
                            elseif ($file['error'] === UPLOAD_ERR_EXTENSION){ $reason = 'php extension error'; }
                            else { $reason = 'error:'.$file['error'].''; }
                            $form_messages[] = array('warning', $type_uc.' file could not be uploaded ('.$reason.')');
                            continue;
                        } elseif ($file['size'] === 0){
                            $form_messages[] = array('warning', $type_uc.' file was literally empty (0 bytes)');
                            continue;
                        } elseif (!preg_match($type_regex, $file['name'])){
                            $form_messages[] = array('warning', $type_uc.' file was the wrong file type');
                            continue;
                        } else {
                            $upload_music_files[$type] = $file;
                        }
                    }
                    //error_log('$upload_music_files = '.print_r($upload_music_files, true));
                }

                // If approved music files were provided, loop through and process 'em now
                if ($allow_file_actions && !empty($upload_music_files)){

                    //error_log('time to upload some files! '.print_r($upload_music_files, true));

                    // If the target directory does not exist yet, create it now
                    if (!file_exists($this_music_dir)){
                        $shell_cmd = 'mkdir "'.$this_music_dir.'"';
                        $shell_output = shell_exec($shell_cmd);
                        //error_log($shell_cmd.PHP_EOL.$shell_output);
                    }

                    // If the target directory still doesn't exist, we have a problem
                    if (!file_exists($this_music_dir)){
                        $form_messages[] = array('error', 'Music directory not exists and could not be created');
                        $allow_file_actions = false;
                    }

                    // Assuming the directory exists, we can upload files to it
                    if ($allow_file_actions){
                        foreach ($upload_music_files AS $type => $file_info){
                            $type_uc = strtoupper($type);
                            $dst_path = $this_music_dir.'audio.'.$type;
                            //error_log('uploading to '.$dst_path);
                            if (file_exists($dst_path)){ unlink($dst_path); }
                            move_uploaded_file($file_info['tmp_name'], $dst_path);
                            if (file_exists($dst_path)){ $form_messages[] = array('alert', $type_uc.' file uploaded to '.str_replace($mmrpg_music_rootdir, '/', $dst_path)); }
                            else { $form_messages[] = array('error', $type_uc.' file could not be moved to destination'); }
                        }
                    }

                }

            }

            // Update cache timestamp if changes were successful
            if ($form_success){
                list($date, $time) = explode('-', date('Ymd-Hi'));
                $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
                $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
            }

            // If successful, we need to update the JSON file
            if ($form_success){
                if ($music_data_is_new){ $music_data['music_id'] = $new_music_id; }
            }

            // We're done processing the form, we can exit
            if (empty($form_data['music_id'])){ exit_music_edit_action(false); }
            else { exit_music_edit_action($form_data['music_id']); }

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }


    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="admin/edit-music/">Edit Music Tracks</a>
        <? if ($sub_action == 'editor' && !empty($music_data)): ?>
            &raquo; <a href="admin/edit-music/editor/music_id=<?= $music_data['music_id'] ?>"><?= !empty($music_name_display) ? $music_name_display : 'Add New Track' ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-music" data-baseurl="admin/edit-music/" data-object="music" data-xobject="music">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search Music</h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <input type="hidden" name="subaction" value="search" />

                    <? /*
                    <div class="field threesize">
                        <strong class="label">By ID</strong>
                        <input class="textbox" type="text" name="music_id" value="<?= !empty($search_data['music_id']) ? $search_data['music_id'] : '' ?>" />
                    </div>
                    */ ?>

                    <div class="field halfsize">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="music_name" placeholder="" value="<?= !empty($search_data['music_name']) ? htmlentities($search_data['music_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field halfsize">
                        <strong class="label">By Token</strong>
                        <input class="textbox" type="text" name="music_token" placeholder="" value="<?= !empty($search_data['music_token']) ? htmlentities($search_data['music_token'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field halfsize">
                        <strong class="label">By Album</strong>
                        <select class="select" name="music_album"><option value="">-</option><?
                            $music_albums_tokens = $db->get_array_list("SELECT DISTINCT(music_album) AS album_token FROM {$mmrpg_music_dbname}.mmrpg_index_music ORDER BY FIELD(music_album, 'sega-remix') DESC, music_album ASC;", 'album_token');
                            $music_albums_tokens = !empty($music_albums_tokens) ? array_keys($music_albums_tokens) : array();
                            foreach ($music_albums_tokens AS $album_key => $album_token){
                                $album_name = $album_token;
                                $album_name = str_replace('-', ' ', $album_name);
                                if (strstr($album_name, ' ost')){ $album_name = strtoupper($album_name); }
                                else { $album_name = ucwords($album_name); }
                                ?><option value="<?= $album_token ?>"<?= !empty($search_data['music_album']) && $search_data['music_album'] === $album_token ? ' selected="selected"' : '' ?>><?= $album_name ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field halfsize">
                        <strong class="label">By Game</strong>
                        <?
                        $current_value = !empty($search_data['music_game']) ? $search_data['music_game'] : '';
                        $temp_options_markup = $source_options_markup;
                        $temp_options_markup = str_replace('<option value="', '<option disabled value="', $temp_options_markup);
                        $temp_options_markup = str_replace('<option disabled value=""', '<option value=""', $temp_options_markup);
                        $temp_allowed_options = $db->get_array_list("SELECT DISTINCT (music_game) AS game_token FROM {$mmrpg_music_dbname}.mmrpg_index_music ORDER BY music_game ASC;", 'game_token');
                        $temp_allowed_options = !empty($temp_allowed_options) ? array_keys($temp_allowed_options) : array();
                        foreach ($temp_allowed_options AS $value){ $temp_options_markup = str_replace('<option disabled value="'.$value.'"', '<option value="'.$value.'"', $temp_options_markup); }
                        ?>
                        <select class="select" name="music_game">
                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $temp_options_markup) ?>
                        </select><span></span>
                    </div>

                    <div class="field fullsize has5cols flags">
                    <?
                    $flag_names = array(
                        'haslink' => array('icon' => 'fas fa-link', 'yes' => 'Has Link', 'no' => 'Missing Link', 'label' => 'Has Credits Link'),
                        'hasfiles' => array('icon' => 'fas fa-file-audio', 'yes' => 'Has Files', 'no' => 'Missing Files', 'label' => 'Has Audio Files'),
                        'hasloop' => array('icon' => 'fas fa-infinity', 'yes' => 'Has Loop', 'no' => 'Missing Loop', 'label' => 'Has Loop Data'),
                        );
                    foreach ($flag_names AS $flag_token => $flag_info){
                        if (isset($flag_info['break'])){ echo('<div class="break"></div>'); continue; }
                        $flag_name = 'music_flag_'.$flag_token;
                        $flag_label = isset($flag_info['label']) ? $flag_info['label'] : ucfirst($flag_token);
                        ?>
                        <div class="subfield">
                            <strong class="label"><?= $flag_label ?> <span class="<?= $flag_info['icon'] ?>"></span></strong>
                            <select class="select" name="<?= $flag_name ?>">
                                <option value=""<?= !isset($search_data[$flag_name]) || $search_data[$flag_name] === '' ? ' selected="selected"' : '' ?>>-</option>
                                <option value="1"<?= isset($search_data[$flag_name]) && $search_data[$flag_name] === 1 ? ' selected="selected"' : '' ?>><?= $flag_info['yes'] ?></option>
                                <option value="0"<?= isset($search_data[$flag_name]) && $search_data[$flag_name] === 0 ? ' selected="selected"' : '' ?>><?= $flag_info['no'] ?></option>
                            </select><span></span>
                        </div>
                        <?
                    }
                    ?>
                    </div>

                    <div class="buttons">
                        <input class="button" type="submit" value="Search" />
                        <input class="button" type="reset" value="Reset" onclick="javascript:window.location.href='admin/edit-music/';" />
                        <a class="button new" href="admin/edit-music/editor/music_id=0">Add New Track</a>
                    </div>

                </form>

            </div>

            <? if (!empty($search_results)): ?>

                <!-- SEARCH RESULTS -->

                <div class="results">

                    <table class="list" style="width: 100%;">
                        <colgroup>
                            <col class="id" width="60" />
                            <col class="name" width="" />
                            <col class="token" width="160" />
                            <col class="album" width="90" />
                            <col class="game" width="80" />
                            <col class="flag hasfiles" width="80" />
                            <col class="flag hasloop" width="80" />
                            <col class="flag haslink" width="80" />
                            <col class="actions" width="100" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('music_id', 'ID') ?></th>
                                <th class="name"><?= cms_admin::get_sort_link('music_name', 'Name') ?></th>
                                <th class="token"><?= cms_admin::get_sort_link('music_token', 'Token') ?></th>
                                <th class="album"><?= cms_admin::get_sort_link('music_album', 'Album') ?></th>
                                <th class="game"><?= cms_admin::get_sort_link('music_game', 'Game') ?></th>
                                <th class="flag hasfiles"><?= cms_admin::get_sort_link('music_flag_haslink', 'Files') ?></th>
                                <th class="flag hasloop"><?= cms_admin::get_sort_link('music_flag_hasloop', 'Loop') ?></th>
                                <th class="flag haslink"><?= cms_admin::get_sort_link('music_flag_haslink', 'Link') ?></th>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head id"></th>
                                <th class="head name"></th>
                                <th class="head token"></th>
                                <th class="head album"></th>
                                <th class="head game"></th>
                                <th class="head flag hasfiles"></th>
                                <th class="head flag hasloop"></th>
                                <th class="head flag haslink"></th>
                                <th class="head count"><?= cms_admin::get_totals_markup() ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot id"></td>
                                <td class="foot name"></td>
                                <td class="foot token"></td>
                                <td class="foot album"></td>
                                <td class="foot game"></td>
                                <td class="foot flag hasfiles"></td>
                                <td class="foot flag hasloop"></td>
                                <td class="foot flag haslink"></td>
                                <td class="foot count"><?= cms_admin::get_totals_markup() ?></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?
                            foreach ($search_results AS $key => $music_data){

                                $music_id = $music_data['music_id'];
                                $music_name = $music_data['music_name'];
                                $music_token = $music_data['music_token'];
                                $music_album = $music_data['music_album'];
                                $music_album = str_replace('-', ' ', $music_album);
                                if (strstr($music_album, ' ost')){ $music_album = strtoupper($music_album); }
                                else { $music_album = ucwords($music_album); }
                                $music_game = $music_data['music_game'];
                                $music_loop = !empty($music_data['music_loop']) ? json_decode($music_data['music_loop'], true) : array();

                                $music_flag_haslink = !empty($music_data['music_flag_haslink']) ? '<i class="fas fa-link"></i>' : '-';
                                $music_flag_hasloop = !empty($music_data['music_flag_hasloop']) ? '<i class="fas fa-infinity"></i>' : '-';
                                $music_flag_hasfiles = !empty($music_data['music_flag_hasfiles']) ? '<i class="fas fa-'.($music_data['music_flag_hasfiles'] > 1 ? 'check-double' : 'check').'"></i>' : '-';

                                if (!empty($music_data['music_flag_haslink'])){ $music_flag_haslink = '<a href="'.$music_data['music_link'].'" target="_blank">'.$music_flag_haslink.'</a>'; }

                                $music_player_markup = '';
                                if ($music_flag_hasfiles){
                                    $this_music_path = $music_data['music_album'].'/'.$music_data['music_token'].'/';
                                    $this_music_loop = !empty($music_data['music_loop']) ? json_decode($music_data['music_loop'], true) : array();
                                    $this_music_dir = $mmrpg_music_rootdir.$this_music_path;
                                    $this_music_url = $mmrpg_music_rooturl.$this_music_path;
                                    $this_file_urls = array();
                                    $this_file_urls_w_cache = array();
                                    foreach ($mmrpg_music_file_types AS $file_type){
                                        $file_exists = false;
                                        $file_dir = $this_music_dir.'audio.'.$file_type;
                                        $file_url = $this_music_url.'audio.'.$file_type;
                                        if (file_exists($file_dir)){
                                            $this_file_urls[] = $file_url;
                                            $this_file_urls_w_cache[] = $file_url.'?'.MMRPG_CONFIG_CACHE_DATE;
                                        }
                                    }
                                    $this_data_attrs = '';
                                    $this_data_attrs .= 'data-kind="music" ';
                                    $this_data_attrs .= 'data-path="'.$this_file_urls_w_cache[0].'" ';
                                    if (isset($this_file_urls_w_cache[1])){ $this_data_attrs .= 'data-backup-path="'.$this_file_urls_w_cache[1].'" '; }
                                    if (!empty($this_music_loop['start'])){ $this_data_attrs .= 'data-loop-start="'.$this_music_loop['start'].'" '; }
                                    if (!empty($this_music_loop['end'])){ $this_data_attrs .= 'data-loop-end="'.$this_music_loop['end'].'" '; }
                                    $music_player_markup .= '<br />';
                                    $music_player_markup .= '<span class="audio-player light-theme no-preload" '.$this_data_attrs.' style="margin: 3px auto 0 0;">';
                                        $music_player_markup .= '<i class="loading fa fas fa-music"></i>';
                                    $music_player_markup .= '</span>';
                                }

                                $music_edit = 'admin/edit-music/editor/music_id='.$music_id;

                                $music_actions = '';
                                $music_actions .= '<a class="link edit" href="'.$music_edit.'"><span>edit</span></a>';
                                $music_actions .= '<a class="link delete" data-delete="music" data-music-id="'.$music_id.'"><span>delete</span></a>';

                                //$music_range_link = '<a class="link" href="'.$music_edit.'">'.$music_date_range.'</a>';
                                $music_name_link = '<a class="link" href="'.$music_edit.'">'.$music_name.'</a>';

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$music_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div>'.$music_name_link.$music_player_markup.'</div></td>'.PHP_EOL;
                                    echo '<td class="token"><div>'.$music_token.'</div></td>'.PHP_EOL;
                                    echo '<td class="album"><div>'.$music_album.'</div></td>'.PHP_EOL;
                                    echo '<td class="game"><div class="wrap">'.$music_game.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag hasfiles"><div>'.$music_flag_hasfiles.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag hasloop"><div>'.$music_flag_hasloop.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag haslink"><div>'.$music_flag_haslink.'</div></td>'.PHP_EOL;
                                    echo '<td class="actions"><div>'.$music_actions.'</div></td>'.PHP_EOL;
                                echo '</tr>'.PHP_EOL;

                            }
                            ?>
                        </tbody>
                    </table>

                </div>

            <? endif; ?>

            <?

            //echo('<pre>$search_query = '.(!empty($search_query) ? htmlentities($search_query, ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');
            //echo('<pre>$search_results = '.print_r($search_results, true).'</pre>');

            ?>

        <? endif; ?>

        <? if ($sub_action == 'editor'
            && isset($_GET['music_id'])
            ){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_none">
                        <span class="title"><?= !empty($music_name_display) ? 'Edit Track &quot;'.$music_name_display.'&quot;' : 'Add New Music' ?></span>
                    </h3>

                    <? print_form_messages() ?>

                    <form class="form" method="post" enctype="multipart/form-data">

                        <input type="hidden" name="action" value="edit-music" />
                        <input type="hidden" name="subaction" value="editor" />

                        <div class="field halfsize">
                            <strong class="label">Music ID</strong>
                            <input type="hidden" name="music_id" value="<?= $music_data['music_id'] ?>" />
                            <input class="textbox" type="text" name="music_id" value="<?= $music_data['music_id'] ?>" disabled="disabled" />
                        </div>

                        <div class="field halfsize">
                            <div class="label">
                                <strong>Music Token</strong>
                                <em>avoid changing</em>
                            </div>
                            <input type="hidden" name="old_music_token" value="<?= $music_data['music_token'] ?>" />
                            <input class="textbox" type="text" name="music_token" value="<?= $music_data['music_token'] ?>" maxlength="128" />
                        </div>

                        <div class="field halfsize">
                            <div class="label">Music Album</div>
                            <input type="hidden" name="old_music_album" value="<?= $music_data['music_album'] ?>" />
                            <? $current_value = !empty($music_data['music_album']) ? $music_data['music_album'] : ''; ?>
                            <select class="select" name="music_album"><option value="">-</option><?
                                $music_albums_tokens = $db->get_array_list("SELECT DISTINCT(music_album) AS album_token FROM {$mmrpg_music_dbname}.mmrpg_index_music ORDER BY FIELD(music_album, 'sega-remix') DESC, music_album ASC;", 'album_token');
                                $music_albums_tokens = !empty($music_albums_tokens) ? array_keys($music_albums_tokens) : array();
                                foreach ($music_albums_tokens AS $album_key => $album_token){
                                    $album_name = $album_token;
                                    $album_name = str_replace('-', ' ', $album_name);
                                    if (strstr($album_name, ' ost')){ $album_name = strtoupper($album_name); }
                                    else { $album_name = ucwords($album_name); }
                                    ?><option value="<?= $album_token ?>"<?= !empty($current_value) && $current_value === $album_token ? ' selected="selected"' : '' ?>><?= $album_name ?></option><?
                                    } ?>
                            </select><span></span>
                        </div>

                        <div class="field halfsize">
                            <div class="label">Music Game</div>
                            <? $current_value = !empty($music_data['music_game']) ? $music_data['music_game'] : ''; ?>
                            <select class="select" name="music_game">
                                <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $source_options_markup) ?>
                            </select><span></span>
                        </div>

                        <div class="field halfsize">
                            <strong class="label">Music Name</strong>
                            <input class="textbox" type="text" name="music_name" value="<?= $music_data['music_name'] ?>" maxlength="256" />
                        </div>

                        <div class="field halfsize">
                            <div class="label">
                                <strong>Music Link</strong>
                                <em>YouTube link for this track</em>
                            </div>
                            <input class="textbox" type="text" name="music_link" value="<?= $music_data['music_link'] ?>" maxlength="256" />
                        </div>

                        <? if (!$music_data_is_new){ ?>

                            <hr />

                            <?

                            // Define an array to hold confirmed uploaded music files
                            $uploaded_music_files = array();

                            // Loop through and print upload inputs for all type
                            $this_music_path = $music_data['music_album'].'/'.$music_data['music_token'].'/';
                            $this_music_dir = $mmrpg_music_rootdir.$this_music_path;
                            $this_music_url = $mmrpg_music_rooturl.$this_music_path;
                            foreach ($mmrpg_music_file_types AS $file_type){
                                $file_dir = $this_music_dir.'audio.'.$file_type;
                                $file_url = $this_music_url.'audio.'.$file_type;
                                $file_url_w_cache = $file_url.'?'.time();
                                $file_exists = false;
                                if (file_exists($file_dir)){ $file_exists = true;  }
                                if ($file_exists){ $file_status = '<a class="status yes" href="'.$file_url_w_cache.'" target="_blank"><i class="fa fas fa-check-circle"></i></a>'; }
                                else { $file_status = '<span class="status no"><i class="fa fas fa-times-circle"></i></span>'; }
                                if ($file_exists){ $uploaded_music_files[] = $file_url_w_cache; }
                                ?>
                                <div class="field">
                                    <div class="label">
                                        <strong>Music <?= strtoupper($file_type) ?> File</strong>
                                        <?= $file_status ?>
                                    </div>
                                    <div class="filewrap">
                                        <input class="fileinput" type="file" name="music_files_<?= $file_type ?>" value="" />
                                    </div>
                                    <? if ($file_exists){ ?>
                                        <div
                                            class="audio-player light-theme"
                                            style="margin: 5px auto 0 0;"
                                            data-kind="music"
                                            data-path="<?= $file_url_w_cache ?>"
                                            ><i class="loading fa fas fa-music"></i>
                                        </div>
                                    <? } ?>
                                </div>
                                <?
                            }

                            // If files were uploaded, we can display the music loop input
                            if (!empty($uploaded_music_files)){

                                ?>

                                <hr />

                                <div class="field halfsize" style="clear: left; margin-top: 10px;">
                                    <?
                                    $timestamp_placeholder = '00:00:00';
                                    $music_loop_data = !empty($music_data['music_loop']) ? json_decode($music_data['music_loop'], true) : array();
                                    //error_log('$music_loop_data = '.print_r($music_loop_data, true));
                                    ?>
                                    <div class="subfield">
                                        <div class="label">
                                            <strong>Music Loop</strong>
                                            <em>mm:ss:ff</em>
                                        </div>
                                        <input class="textbox" type="text" name="music_loop[start]" value="<?= !empty($music_loop_data['start']) ? get_timestamp_from_milliseconds($music_loop_data['start']) : '' ?>" maxlength="8" placeholder="<?= $timestamp_placeholder ?>" style="float: left; width: 75px; margin: 0 5px 0 0;" />
                                        <input class="textbox" type="text" name="music_loop[end]" value="<?= !empty($music_loop_data['end']) ? get_timestamp_from_milliseconds($music_loop_data['end']) : '' ?>" maxlength="8" placeholder="<?= $timestamp_placeholder ?>" style="float: left; width: 75px; margin: 0 5px 0 0;" />
                                    </div>
                                    <div
                                        class="audio-player light-theme"
                                        style="margin: 5px auto 0 0;"
                                        data-kind="music"
                                        <?= !empty($uploaded_music_files[0]) ? 'data-path="'.$uploaded_music_files[0].'"' : '' ?>
                                        <?= !empty($uploaded_music_files[1]) ? 'data-backup-path="'.$uploaded_music_files[1].'"' : '' ?>
                                        <?= !empty($music_loop_data['start']) ? 'data-loop-start="'.$music_loop_data['start'].'"' : '' ?>
                                        <?= !empty($music_loop_data['end']) ? 'data-loop-end="'.$music_loop_data['end'].'"' : '' ?>
                                        ><i class="loading fa fas fa-music"></i>
                                    </div>
                                </div>

                                <?

                            }

                            ?>

                            <hr />

                            <div class="options">

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Order</strong>
                                        <input class="textbox" type="number" name="music_order" value="<?= $music_data['music_order'] ?>" maxlength="2" style="width: 75px; margin-top: -8px; top: -2px;" />
                                    </label>
                                    <p class="subtext">relative order of track</p>
                                </div>

                            </div>

                        <? } ?>

                        <hr />

                        <div class="formfoot">

                            <div class="buttons">
                                <input class="button save" type="submit" value="<?= $music_data_is_new ? 'Add New Track' : 'Save Changes' ?>" />
                                <? if (!$music_data_is_new){ ?>
                                    <input class="button delete" type="button" value="Delete Track" data-delete="music" data-music-id="<?= $music_data['music_id'] ?>" />
                                <? } ?>
                            </div>

                        </div>

                    </form>

                </div>

                <?

                //$debug_music_data = $music_data;
                //echo('<pre>$music_data = '.(!empty($debug_music_data) ? htmlentities(print_r($debug_music_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                ?>

                <?

                $temp_edit_markup = ob_get_clean();
                echo($temp_edit_markup).PHP_EOL;
            }

        }

        ?>

    </div>

<? $this_page_markup .= ob_get_clean(); ?>

