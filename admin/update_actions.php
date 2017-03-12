<?

// Define a function for updating user save files
function mmrpg_admin_update_save_file($key, $data, $patch_token){
    global $db;
    global $update_patch_tokens, $update_patch_names, $update_patch_details;
    global $this_request_force, $this_request_print;

    // Start the markup variable
    $this_page_markup = '';

    // Define a parent GAME variable to hold all save data in
    $_GAME = array();

    // Expand this save files data into full arrays and update the session
    $_GAME['CACHE_DATE'] = $data['save_cache_date'];
    $cache_date_backup = $data['save_cache_date'];

    // Expand the flags and values to their appropriate battle values
    $_GAME['user_id'] = $data['user_id'];
    $_GAME['save_id'] = $data['save_id'];

    $_GAME['flags'] = !empty($data['save_flags']) ? json_decode($data['save_flags'], true) : array();
    $_GAME['values'] = !empty($data['save_values']) ? json_decode($data['save_values'], true) : array();
    $_GAME['counters'] = !empty($data['save_counters']) ? json_decode($data['save_counters'], true) : array();
    $_GAME['patches'] = !empty($data['save_patches_applied']) ? json_decode($data['save_patches_applied'], true) : array();

    if (!empty($data['save_values_battle_index'])){ $_GAME['values']['battle_index'] = array(); }
    elseif (!isset($_GAME['values']['battle_index'])){ $_GAME['values']['battle_index'] = array(); }

    if (!empty($data['save_values_battle_complete'])){ $_GAME['values']['battle_complete'] = json_decode($data['save_values_battle_complete'], true); }
    elseif (!isset($_GAME['values']['battle_complete'])){ $_GAME['values']['battle_complete'] = array(); }

    if (!empty($data['save_values_battle_failure'])){ $_GAME['values']['battle_failure'] = json_decode($data['save_values_battle_failure'], true); }
    elseif (!isset($_GAME['values']['battle_failure'])){ $_GAME['values']['battle_failure'] = array(); }

    if (!empty($data['save_values_battle_settings'])){ $_GAME['values']['battle_settings'] = json_decode($data['save_values_battle_settings'], true); }
    elseif (!isset($_GAME['values']['battle_settings'])){ $_GAME['values']['battle_settings'] = array(); }

    if (!empty($data['save_values_battle_rewards'])){ $_GAME['values']['battle_rewards'] = json_decode($data['save_values_battle_rewards'], true); }
    elseif (!isset($_GAME['values']['battle_rewards'])){ $_GAME['values']['battle_rewards'] = array(); }

    if (!empty($data['save_values_battle_items'])){ $_GAME['values']['battle_items'] = json_decode($data['save_values_battle_items'], true); }
    elseif (!isset($_GAME['values']['battle_items'])){ $_GAME['values']['battle_items'] = array(); }

    if (!empty($data['save_values_battle_abilities'])){ $_GAME['values']['battle_abilities'] = json_decode($data['save_values_battle_abilities'], true); }
    elseif (!isset($_GAME['values']['battle_abilities'])){ $_GAME['values']['battle_abilities'] = array(); }

    if (!empty($data['save_values_battle_stars'])){ $_GAME['values']['battle_stars'] = json_decode($data['save_values_battle_stars'], true); }
    elseif (!isset($_GAME['values']['battle_stars'])){ $_GAME['values']['battle_stars'] = array(); }

    if (!empty($data['save_values_robot_alts'])){ $_GAME['values']['robot_alts'] = json_decode($data['save_values_robot_alts'], true); }
    elseif (!isset($_GAME['values']['robot_alts'])){ $_GAME['values']['robot_alts'] = array(); }

    if (!empty($data['save_values_robot_database'])){ $_GAME['values']['robot_database'] = json_decode($data['save_values_robot_database'], true); }
    elseif (!isset($_GAME['values']['robot_database'])){ $_GAME['values']['robot_database'] = array(); }

    // Only apply the patch if it's not already applied
    if ($this_request_force || !in_array($patch_token, $_GAME['patches'])){

        // Define variables to hold patch details
        $patch_name = '';
        $patch_details = '';
        $patch_notes = '';

        // Use the patch token to deterine which function to run on save data
        if (in_array($patch_token, $update_patch_tokens)){
            ob_start();
            $patch_name = $update_patch_names[$patch_token];
            $patch_details = $update_patch_details[$patch_token];
            $patch_function = 'mmrpg_patch_'.$patch_token;
            if (!function_exists($patch_function)){ exit('The patch function "'.$patch_function.'" doesn\'t exist...'); }
            $_GAME = call_user_func($patch_function, $_GAME);
            $patch_notes = trim(ob_get_clean());
            $patch_notes = strip_tags(html_entity_decode($patch_notes, ENT_QUOTES, 'UTF-8'));
            // If print was requested, do not actually update file
            if ($this_request_print){
                // Print out debug info and exit now
                header('Content-type: text/plain;');
                echo($patch_notes.PHP_EOL);
                echo($patch_function.'()');
                exit();
            }
        }

        // If the any key fields were empty, abort mission!
        if (empty($_GAME['user_id'])){ die('Something happened to the user ID...'); }

        // If a patch was found and applied, update save file and generate notes
        if (!empty($patch_name) && !empty($patch_details) && !empty($patch_notes)){

            // Add this patch token to the array list
            $_GAME['patches'][] = $patch_token;

            // Generate the header for this update patch's notes including details
            ob_start();
            echo("[b]MMRPG Prototype Patch Notes[/b]\n");
            echo("User    : {$_GAME['user_id']}\n");
            echo("Name    : {$patch_name}\n");
            echo("Token   : {$patch_token}\n");
            echo("Date    : ".date('Y/m/d @ H:i:s')."\n");
            echo("{$patch_details}\n");
            $patch_header = trim(ob_get_clean());

            // Concatenate patch header and notes at once and collect as markup
            ob_start();
            echo("[align-left]\n");
            echo("[system]\n");
            echo("----------------------------------------\n");
            echo $patch_header."\n";
            echo("----------------------------------------\n");
            echo("\nYour save file is being updated...\n\n");
            echo $patch_notes."\n";
            echo("\n...your save file has been updated!\n\n");
            echo("----------------------------------------\n");
            echo("[/system]\n");
            echo("[/align]\n");
            $patch_markup = trim(ob_get_clean());

            // Parse and print the message formatting and print details
            //echo mmrpg_formatting_decode($patch_markup)."\n";

            // Define the patch thread parameters
            $patch_thread_category = 0;
            $patch_thread_name = 'Mega Man RPG Prototype Updates';
            $patch_thread_token = 'mega-man-rpg-prototype-updates';
            $patch_thread_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
            $patch_thread_target = $_GAME['user_id'];

            // Define the thread select query
            $thread_select_query = "SELECT *
                FROM mmrpg_threads
                WHERE
                category_id = {$patch_thread_category} AND
                thread_token = '{$patch_thread_token}' AND
                user_id = {$patch_thread_user_id} AND
                thread_target = {$patch_thread_target}
                ;";

            // Collect the thread ID for this user, if there is one
            $update_thread_data = $db->get_array($thread_select_query);

            // If the thread does not exist, create it now in the database
            if (empty($update_thread_data)){

                // Generate the details for the new update thread
                $update_thread_data = array();
                $update_thread_data['category_id'] = $patch_thread_category;
                $update_thread_data['user_id'] = $patch_thread_user_id;
                $update_thread_data['user_ip'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
                $update_thread_data['thread_name'] = $patch_thread_name;
                $update_thread_data['thread_token'] = $patch_thread_token;
                $update_thread_data['thread_body'] = "[system]\n";
                $update_thread_data['thread_body'] .= "This thread is an auto-generated system message and cannot be responded to.\n\n";
                $update_thread_data['thread_body'] .= "Please use this thread to review patch notes and game updates automatically applied to your save file.\n\n";
                $update_thread_data['thread_body'] .= "Thank you for playing the Mega Man RPG Prototype.\n\n";
                $update_thread_data['thread_body'] .= "----------------------------------------\n";
                $update_thread_data['thread_body'] .= "[b]Update History[/b]\n";
                $update_thread_data['thread_body'] .= "----------------------------------------\n";
                $update_thread_data['thread_body'] .= "[/system]\n";
                $update_thread_data['thread_frame'] = '02';
                $update_thread_data['thread_colour'] = 'laser';
                $update_thread_data['thread_date'] = time();
                $update_thread_data['thread_mod_date'] = time();
                $update_thread_data['thread_mod_user'] = MMRPG_SETTINGS_TARGET_PLAYERID;
                $update_thread_data['thread_published'] = 1;
                $update_thread_data['thread_target'] = $patch_thread_target;

                // Insert into the database with generated information
                $db->insert('mmrpg_threads', $update_thread_data);
                // Recollect the thread from the DB to refresh its values
                $update_thread_data = $db->get_array($thread_select_query);

            }

            // Update the thread and append this patch to the bottom
            $append_to_thread = date('Y/m/d @ H:i:s')." : {$patch_name}\n";
            $update_thread_data['thread_body'] = str_replace('[/system]', $append_to_thread.'[/system]', $update_thread_data['thread_body']);
            $db->update('mmrpg_threads', array(
                'thread_body' => $update_thread_data['thread_body'],
                'thread_mod_date' => time(),
                'thread_mod_user' => $patch_thread_user_id
                ), array('thread_id' => $update_thread_data['thread_id']));

            // Append a new comment on this player's update thread
            $update_post_data = array();
            $update_post_data['category_id'] = $update_thread_data['category_id'];
            $update_post_data['thread_id'] = $update_thread_data['thread_id'];
            $update_post_data['user_id'] = $update_thread_data['user_id'];
            $update_post_data['user_ip'] = $update_thread_data['user_ip'];
            $update_post_data['post_body'] = $patch_markup;
            $update_post_data['post_frame'] = '02';
            $update_post_data['post_date'] = time();
            $update_post_data['post_target'] = $update_thread_data['thread_target'];
            $db->insert('mmrpg_posts', $update_post_data);

        }

    }

    // Recompress and prepare the save data for the database
    $temp_values = $_GAME['values'];
    unset($temp_values['battle_index'], $temp_values['battle_complete'], $temp_values['battle_failure'],
    $temp_values['battle_rewards'], $temp_values['battle_settings'], $temp_values['battle_items'],
    $temp_values['battle_stars'], $temp_values['robot_database']);
    $update_array = array(
        'save_cache_date' => MMRPG_CONFIG_CACHE_DATE,
        'save_flags' => mmrpg_admin_encode_save_data($_GAME['flags']),
        'save_values' => mmrpg_admin_encode_save_data($temp_values),
        'save_values_battle_index' => mmrpg_admin_encode_save_data($_GAME['values']['battle_index']),
        'save_values_battle_complete' => mmrpg_admin_encode_save_data($_GAME['values']['battle_complete']),
        'save_values_battle_failure' => mmrpg_admin_encode_save_data($_GAME['values']['battle_failure']),
        'save_values_battle_rewards' => mmrpg_admin_encode_save_data($_GAME['values']['battle_rewards']),
        'save_values_battle_settings' => mmrpg_admin_encode_save_data($_GAME['values']['battle_settings']),
        'save_values_battle_items' => mmrpg_admin_encode_save_data($_GAME['values']['battle_items']),
        'save_values_battle_abilities' => mmrpg_admin_encode_save_data($_GAME['values']['battle_abilities']),
        'save_values_battle_stars' => mmrpg_admin_encode_save_data($_GAME['values']['battle_stars']),
        'save_values_robot_database' => mmrpg_admin_encode_save_data($_GAME['values']['robot_database']),
        'save_counters' => mmrpg_admin_encode_save_data($_GAME['counters']),
        'save_patches_applied' => mmrpg_admin_encode_save_data($_GAME['patches'])
        );

    // Update the database with the recent changes
    $temp_success = $db->update('mmrpg_saves', $update_array, "save_id = {$data['save_id']}");

    // DEBUG
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';

        // Print the debug headers
        $this_page_markup .= '<strong>$this_update_list['.$key.']['.$data['user_name_clean'].']</strong><br />';
        $this_page_markup .= 'Save ID:'.$data['save_id'].'<br />';

        // Check to see which fields have been updated
        if ($update_array['save_cache_date'] != $data['save_cache_date']){ $this_page_markup .= 'Save cache date has been changed...<br />'; }
        if ($update_array['save_flags'] != $data['save_flags']){ $this_page_markup .= 'Save flags have been changed...<br />'; }
        if ($update_array['save_values'] != $data['save_values']){ $this_page_markup .= 'Save values have been changed...<br />'; }
        if ($update_array['save_values_battle_index'] != $data['save_values_battle_index']){ $this_page_markup .= 'Save values battle index has been changed...<br />'; }
        if ($update_array['save_values_battle_complete'] != $data['save_values_battle_complete']){ $this_page_markup .= 'Save values battle completes have been changed...<br />'; }
        if ($update_array['save_values_battle_failure'] != $data['save_values_battle_failure']){ $this_page_markup .= 'Save values battle failures have been changed...<br />'; }
        if ($update_array['save_values_battle_rewards'] != $data['save_values_battle_rewards']){ $this_page_markup .= 'Save values battle rewards have been changed...<br />'; }
        if ($update_array['save_values_battle_settings'] != $data['save_values_battle_settings']){ $this_page_markup .= 'Save values battle settings have been changed...<br />'; }
        if ($update_array['save_values_battle_items'] != $data['save_values_battle_items']){ $this_page_markup .= 'Save values battle items have been changed...<br />'; }
        if ($update_array['save_values_battle_abilities'] != $data['save_values_battle_abilities']){ $this_page_markup .= 'Save values battle abilities have been changed...<br />'; }
        if ($update_array['save_values_battle_stars'] != $data['save_values_battle_stars']){ $this_page_markup .= 'Save values battle stars have been changed...<br />'; }
        if ($update_array['save_values_robot_database'] != $data['save_values_robot_database']){ $this_page_markup .= 'Save values robot database has been changed...<br />'; }
        if ($update_array['save_counters'] != $data['save_counters']){ $this_page_markup .= 'Save counters have been changed...<br />'; }
        if ($temp_success === false){ $this_page_markup .= '...Failure!'; }
        else { $this_page_markup .= '...'.(!empty($temp_success) ? 'Success!' : 'Skipped!'); }
        unset($update_array);

    $this_page_markup .= '</p><hr />';

    // Return generated page markup
    return $this_page_markup;
}

// Define a function for search and replacing typos before re-encoding
function mmrpg_admin_encode_save_data($data){
    if (is_array($data)){ $data = json_encode($data); }
    return $data;
}

?>