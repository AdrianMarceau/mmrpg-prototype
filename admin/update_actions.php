<?

// Define a function for updating user save files
function mmrpg_admin_update_save_file($key, $data, $patch_token){
    global $db;
    global $update_patch_tokens, $update_patch_names, $update_patch_details;
    global $this_request_force, $this_request_print, $this_ajax_request_feedback;

    // Change content type of print was requested
    if ($this_request_print){ header('Content-type: text/plain;'); }

    //echo('<pre>---------------------------------------------</pre>'.PHP_EOL);
    //echo('<pre>mmrpg_admin_update_save_file($key, $data, $patch_token)</pre>'.PHP_EOL);
    //echo('<pre>$key = '.print_r($key, true).'</pre>'.PHP_EOL);
    //echo('<pre>$data = '.print_r($data, true).'</pre>'.PHP_EOL);
    //echo('<pre>$patch_token = '.print_r($patch_token, true).'</pre>'.PHP_EOL);

    // Start the markup variable
    $this_page_markup = '';

    //echo('<pre>---------------------------------------------</pre>'.PHP_EOL);
    //echo('<pre>Loading game data into the update session...</pre>'.PHP_EOL);

    // Load the game session into a special var
    define('MMRPG_UPDATE_GAME', $data['user_id']);
    $session_token = 'UPDATE_GAME_'.$data['user_id'];
    $_SESSION[$session_token]['PENDING_LOGIN_ID'] = $data['user_id'];
    $_SESSION[$session_token]['DEMO'] = 0;
    mmrpg_load_game_session();
    //echo('<pre>mmrpg_load_game_session();</pre>'.PHP_EOL);
    //echo('<pre>$_SESSION[\''.$session_token.'\'] = '.print_r($_SESSION[$session_token], true).'</pre>'.PHP_EOL);

    // If session was empty or board points were zero, reset now
    //echo('<pre>Validating session and board points not empty, else reset</pre>'.PHP_EOL);
    if (empty($_SESSION[$session_token]) || empty($data['board_points'])){
        mmrpg_reset_game_session();
        //echo('<pre>mmrpg_reset_game_session();</pre>'.PHP_EOL);
        //echo('<pre>$_SESSION[\''.$session_token.'\'] = '.print_r($_SESSION[$session_token], true).'</pre>'.PHP_EOL);
    }

    // Collect the GAME variable from the session then remove it
    $_GAME = !empty($_SESSION[$session_token]) ? $_SESSION[$session_token] : array();

    // Hard-code the current user and save ID into the game object
    $_GAME['user_id'] = $data['user_id'];
    $_GAME['save_id'] = $data['save_id'];

    // Expand this save files data into full arrays and update the session
    $_GAME['CACHE_DATE'] = $data['save_cache_date'];
    $cache_date_backup = $data['save_cache_date'];

    //echo('<pre>---------------------------------------------</pre>'.PHP_EOL);
    //echo('<pre>Game is prepared and ready to start updating...</pre>'.PHP_EOL);
    //echo('<pre>$_GAME = '.print_r($_GAME, true).'</pre>'.PHP_EOL);

    // Only apply the patch if it's not already applied
    if ($this_request_force || !in_array($patch_token, $_GAME['patches'])){

        // Define variables to hold patch details
        $patch_name = '';
        $patch_details = '';
        $patch_notes = '';

        //echo('<pre>---------------------------------------------</pre>'.PHP_EOL);
        //echo('<pre>Time to patch the game!</pre>'.PHP_EOL);

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
                //header('Content-type: text/plain;');
                echo($patch_notes.PHP_EOL);
                echo($patch_function.'()');
                exit();
            }
        }

        //echo('<pre>---------------------------------------------</pre>'.PHP_EOL);
        //echo('<pre>Update is done for now...</pre>'.PHP_EOL);
        //echo('<pre>---------------------------------------------</pre>'.PHP_EOL);
        //exit();

        // If the any key fields were empty, abort mission!
        if (empty($_GAME['user_id'])){ die('Something happened to the user ID...'); }

        // If a patch was found and applied, update save file and generate notes
        if (!empty($patch_name) && !empty($patch_details) && !empty($patch_notes)){

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

    // Make sure only unique patch values make it through
    $_GAME['patches'] = array_unique($_GAME['patches']);

    // Update session with modified game values and save
    $_SESSION[$session_token] = $_GAME;
    mmrpg_save_game_session();

    // Unset the temporary session array
    unset($_SESSION[$session_token]);

    // Update the database with the recent changes
    $temp_success = $db->update('mmrpg_saves', array(
        'save_cache_date' => MMRPG_CONFIG_CACHE_DATE,
        'save_patches_applied' => mmrpg_admin_encode_save_data($_GAME['patches']),
        'save_date_modified' => time()
        ), "save_id = {$data['save_id']}");

    // Add this patch token to the database list
    $temp_success2 = false;
    if (!in_array($patch_token, $_GAME['patches'])){
        // Add this patch token to the array list
        $_GAME['patches'][] = $patch_token;
        // Save the new patch info to the database
        $temp_success2 = $db->insert('mmrpg_saves_patches_users', array(
            'user_id' => $_GAME['user_id'],
            'patch_token' => $patch_token
            ));
    }

    // DEBUG
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';

        // Print the debug headers
        $this_page_markup .= '<strong>$this_update_list['.$key.']['.$data['user_name_clean'].']</strong><br />';
        $this_page_markup .= 'User ID:'.$data['user_id'].'<br />';
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
        if (!empty($this_ajax_request_feedback)){
            $this_page_markup .= nl2br(trim($this_ajax_request_feedback)).'<br />';
        }
        if (!empty($_GAME['patches'])){
            $this_page_markup .= 'Save patches applied : '.implode(', ', $_GAME['patches']).'<br />';
        }
        if ($temp_success === false){
            $this_page_markup .= '...Failure!';
        } else {
            $this_page_markup .= $temp_success ? '.' : '';
            $this_page_markup .= $temp_success2 ? '.' : '';
            $this_page_markup .= '...Success!';
        }
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