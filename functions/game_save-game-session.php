<?
// Define a function for saving the game session
function mmrpg_save_game_session(){
    //debug_profiler_checkpoint('func/save-game-session/before/');
    //error_log('mmrpg_save_game_session()');

    // Reference global variables
    global $db;
    $session_token = mmrpg_game_token();
    $mmrpg_index_players = rpg_player::get_index(true);
    $mmrpg_index_robots = rpg_robot::get_index(true);

    // Do NOT load, save, or otherwise alter the game file while viewing remote
    if (defined('MMRPG_REMOTE_GAME')){ return false; }
    elseif (!empty($_SESSION['GAME']['DEMO'])){ return false; }
    elseif (!empty($_SESSION['WORLD']['DEMO'])){ return false; }

    // If the required USER or FILE arrays do not exist, reset
    if (!isset($_SESSION[$session_token]['USER'])){ mmrpg_reset_game_session(); }

    // Update the last saved value
    $_SESSION[$session_token]['values']['last_save'] = time();

    // Collect the save info
    $save = $_SESSION[$session_token];
    $this_user = $save['USER'];
    $GAME_SESSION = !empty($_SESSION['GAME']) ? $_SESSION['GAME'] : array();
    $WORLD_SESSION = !empty($_SESSION['WORLD']) ? $_SESSION['WORLD'] : array();
    //error_log('$this_user = '.print_r($this_user, true));

    // Define the flag for whether this is a new user
    $is_new_user = false;

    // -- NORMAL MODE SAVE GAME SESSION -- //
    if (!empty($GAME_SESSION)){

        //error_log('Saving game session for user ID '.$this_user['userid']);

        // UPDATE DATABASE INFO

        // Collect the save info
        $this_cache_date = !empty($GAME_SESSION['CACHE_DATE']) ? $GAME_SESSION['CACHE_DATE'] : MMRPG_CONFIG_CACHE_DATE;
        $this_counters = !empty($GAME_SESSION['counters']) ? $GAME_SESSION['counters'] : array();
        $this_values = !empty($GAME_SESSION['values']) ? $GAME_SESSION['values'] : array();
        $this_flags = !empty($GAME_SESSION['flags']) ? $GAME_SESSION['flags'] : array();
        $this_settings = !empty($GAME_SESSION['battle_settings']) ? $GAME_SESSION['battle_settings'] : array();
        $this_stars = !empty($GAME_SESSION['values']['battle_stars']) ? $GAME_SESSION['values']['battle_stars'] : array();
        unset($save);

        // Define a flag for if this is a freshly reset game
        $reset_in_progress = !empty($GAME_SESSION['RESET']) ? true : false;

        // Collect this user's ID from the database if not set
        if (!isset($this_user['userid'])){

            // Attempt to collect the user ID from the database
            $temp_query = "SELECT user_id FROM mmrpg_users WHERE user_name_clean = '{$this_user['username_clean']}' LIMIT 1";
            $temp_value = $db->get_value($temp_query, 'user_id');

            // If the user ID was found, collect it and proceed as normal
            if (!empty($temp_value)){
                //echo('!empty($temp_value) = '.$temp_value.';<br /> ');
                //echo('$is_new_user = false;<br /> ');

                // Update the ID in the user array and continue
                $this_user['userid'] = $temp_value;

            }
            // Otherwise, create database rows for this new file
            else {
                //echo('empty($temp_value)<br />');
                //echo('$is_new_user = true;<br /> ');

                // This is a new user so update the flag
                $is_new_user = true;

                // Generate new user, save, and board IDs for this listing
                $temp_user_id = $db->get_value('SELECT MAX(user_id) AS user_id FROM mmrpg_users', 'user_id') + 1;
                $temp_save_id = $db->get_value('SELECT MAX(save_id) AS save_id FROM mmrpg_saves', 'save_id') + 1;
                $temp_board_id = $db->get_value('SELECT MAX(board_id) AS board_id FROM mmrpg_leaderboard', 'board_id') + 1;

                // Generate the USER details for import
                $this_user_array = array();
                $this_user_array['user_id'] = $temp_user_id;
                $this_user_array['role_id'] = isset($this_user['roleid']) ? $this_user['roleid'] : 3;
                $this_user_array['user_name'] = $this_user['username'];
                $this_user_array['user_name_clean'] = $this_user['username_clean'];
                $this_user_array['user_name_public'] = !empty($this_user['displayname']) ? $this_user['displayname'] : '';
                if (!empty($this_user['password_encoded'])){ $this_user_array['user_password_encoded'] = $this_user['password_encoded']; }
                if (!empty($this_user['omega'])){ $this_user_array['user_omega'] = $this_user['omega']; }
                $this_user_array['user_profile_text'] = !empty($this_user['profiletext']) ? $this_user['profiletext'] : '';
                $this_user_array['user_credit_text'] = !empty($this_user['creditstext']) ? $this_user['creditstext'] : '';
                $this_user_array['user_credit_line'] = !empty($this_user['creditsline']) ? $this_user['creditsline'] : '';
                $this_user_array['user_image_path'] = !empty($this_user['imagepath']) ? $this_user['imagepath'] : '';
                $this_user_array['user_background_path'] = !empty($this_user['backgroundpath']) ? $this_user['backgroundpath'] : '';
                $this_user_array['user_colour_token'] = !empty($this_user['colourtoken']) ? $this_user['colourtoken'] : '';
                $this_user_array['user_colour_token2'] = !empty($this_user['colourtoken2']) ? $this_user['colourtoken2'] : '';
                $this_user_array['user_gender'] = !empty($this_user['gender']) ? $this_user['gender'] : '';
                $this_user_array['user_email_address'] = !empty($this_user['emailaddress']) ? $this_user['emailaddress'] : '';
                $this_user_array['user_website_address'] = !empty($this_user['websiteaddress']) ? $this_user['websiteaddress'] : '';
                $this_user_array['user_date_created'] = time();
                $this_user_array['user_date_accessed'] = time();
                $this_user_array['user_date_modified'] = time();
                $this_user_array['user_date_birth'] = !empty($this_user['dateofbirth']) ? $this_user['dateofbirth'] : 0;
                $this_user_array['user_flag_approved'] = 1;

                // Generate the BOARD details for import
                $this_board_array = array();
                $this_board_array['board_id'] = $temp_board_id;
                $this_board_array['user_id'] = $temp_user_id;
                $this_board_array['save_id'] = $temp_save_id;
                $this_board_array['board_points'] = !empty($this_counters['battle_points']) ? $this_counters['battle_points'] : 0;
                $this_board_array['board_zenny'] = !empty($this_counters['battle_zenny']) ? $this_counters['battle_zenny'] : 0;
                $this_board_array['board_robots_count'] = 0;
                $this_board_array['board_robots'] = array();
                $this_board_array['board_stars'] = 0;
                $this_board_array['board_stars_dr_light'] = 0;
                $this_board_array['board_stars_dr_wily'] = 0;
                $this_board_array['board_stars_dr_cossack'] = 0;
                $this_board_array['board_abilities'] = mmrpg_prototype_abilities_unlocked();
                if (!empty($this_values['battle_rewards'])){
                    foreach ($mmrpg_index_players AS $player_token => $player_array){
                        if ($player_token == 'player'){ continue; }
                        elseif (!empty($player_array['player_flag_hidden'])){ continue; }
                        $player_reward_array = !empty($this_values['battle_rewards'][$player_token]) ? $this_values['battle_rewards'][$player_token] : array();
                        $player_battles_array = !empty($this_values['battle_complete'][$player_token]) ? $this_values['battle_complete'][$player_token] : array();
                        $player_database_token = str_replace('-', '_', $player_token);
                        if (!empty($player_reward_array)){
                            $this_board_array['board_robots_'.$player_database_token] = array();
                            if (!empty($player_reward_array['player_robots'])){
                                foreach ($player_reward_array['player_robots'] AS $robot_token => $robot_array){
                                    if (!isset($mmrpg_index_robots[$robot_token])){ continue; }
                                    elseif (!mmrpg_prototype_robot_unlocked($player_token, $robot_token)){ continue; }
                                    else { $robot_index = $mmrpg_index_robots[$robot_token]; }
                                    if (empty($robot_index['robot_flag_published'])){ continue; }
                                    elseif (empty($robot_index['robot_flag_complete'])){ continue; }
                                    elseif (empty($robot_index['robot_flag_unlockable'])){ continue; }
                                    $temp_token = $robot_array['robot_token'];
                                    $temp_level = !empty($robot_array['robot_level']) ? $robot_array['robot_level'] : 1;
                                    $temp_robot_info = array('robot_token' => $temp_token, $temp_level);
                                    $this_board_array['board_robots'][] = '['.$temp_token.':'.$temp_level.']';
                                    $this_board_array['board_robots_'.$player_database_token][] = '['.$temp_token.':'.$temp_level.']';
                                }
                            }
                        } else {
                            $this_board_array['board_robots_'.$player_database_token] = array();
                        }
                        $this_board_array['board_robots_'.$player_database_token] = !empty($this_board_array['board_robots_'.$player_database_token]) ? implode(',', $this_board_array['board_robots_'.$player_database_token]) : '';
                    }
                }

                if (!empty($this_stars)){
                    foreach ($this_stars AS $temp_star_token => $temp_star_info){
                        $temp_star_player = str_replace('-', '_', $temp_star_info['star_player']);
                        $this_board_array['board_stars'] += 1;
                        $this_board_array['board_stars_'.$temp_star_player] += 1;
                    }
                }
                $this_board_array['board_robots_count'] = !empty($this_board_array['board_robots']) ? count($this_board_array['board_robots']) : 0;
                $this_board_array['board_robots'] = !empty($this_board_array['board_robots']) ? implode(',', $this_board_array['board_robots']) : '';
                $this_board_array['board_date_created'] = $this_user_array['user_date_created'];
                $this_board_array['board_date_modified'] = $this_user_array['user_date_modified'];

                // Generate the SAVE details for import
                $this_save_array = array();

                /*
                if (!empty($this_values['battle_index'])){
                    unset($this_values['battle_index']);
                }
                */
                if (!empty($this_values['battle_complete']) || $reset_in_progress){
                    $this_save_array['save_values_battle_complete'] = json_encode(!empty($this_values['battle_complete']) ? $this_values['battle_complete'] : array());
                    $temp_hash = md5($this_save_array['save_values_battle_complete']);
                    if (isset($this_values['battle_complete_hash']) && $this_values['battle_complete_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_complete']); }
                    unset($this_values['battle_complete'], $this_values['battle_complete_hash']);
                }
                if (!empty($this_values['battle_failure']) || $reset_in_progress){
                    $this_save_array['save_values_battle_failure'] = json_encode(!empty($this_values['battle_failure']) ? $this_values['battle_failure'] : array());
                    $temp_hash = md5($this_save_array['save_values_battle_failure']);
                    if (isset($this_values['battle_failure_hash']) && $this_values['battle_failure_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_failure']); }
                    unset($this_values['battle_failure'], $this_values['battle_failure_hash']);
                }
                if (!empty($this_values['battle_rewards']) || $reset_in_progress){
                    $this_save_array['save_values_battle_rewards'] = json_encode(!empty($this_values['battle_rewards']) ? $this_values['battle_rewards'] : array());
                    $temp_hash = md5($this_save_array['save_values_battle_rewards']);
                    if (isset($this_values['battle_rewards_hash']) && $this_values['battle_rewards_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_rewards']); }
                    unset($this_values['battle_rewards'], $this_values['battle_rewards_hash']);
                }
                if (!empty($this_values['battle_settings']) || $reset_in_progress){
                    $this_save_array['save_values_battle_settings'] = json_encode(!empty($this_values['battle_settings']) ? $this_values['battle_settings'] : array());
                    $temp_hash = md5($this_save_array['save_values_battle_settings']);
                    if (isset($this_values['battle_settings_hash']) && $this_values['battle_settings_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_settings']); }
                    unset($this_values['battle_settings'], $this_values['battle_settings_hash']);
                }
                /*
                if (!empty($this_values['battle_items']) || $reset_in_progress){
                    $this_save_array['save_values_battle_items'] = json_encode(!empty($this_values['battle_items']) ? $this_values['battle_items'] : array());
                    $temp_hash = md5($this_save_array['save_values_battle_items']);
                    if (isset($this_values['battle_items_hash']) && $this_values['battle_items_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_items']); }
                    unset($this_values['battle_items'], $this_values['battle_items_hash']);
                }
                */
                /*
                if (!empty($this_values['battle_abilities']) || $reset_in_progress){
                    $this_save_array['save_values_battle_abilities'] = json_encode(!empty($this_values['battle_abilities']) ? $this_values['battle_abilities'] : array());
                    $temp_hash = md5($this_save_array['save_values_battle_abilities']);
                    if (isset($this_values['battle_abilities_hash']) && $this_values['battle_abilities_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_abilities']); }
                    unset($this_values['battle_abilities'], $this_values['battle_abilities_hash']);
                }
                */
                /*
                if (!empty($this_values['battle_stars']) || $reset_in_progress){
                    $this_save_array['save_values_battle_stars'] = json_encode(!empty($this_values['battle_stars']) ? $this_values['battle_stars'] : array());
                    $temp_hash = md5($this_save_array['save_values_battle_stars']);
                    if (isset($this_values['battle_stars_hash']) && $this_values['battle_stars_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_stars']); }
                    unset($this_values['battle_stars'], $this_values['battle_stars_hash']);
                }
                */
                if (!empty($this_values['robot_alts']) || $reset_in_progress){
                    $this_save_array['save_values_robot_alts'] = json_encode(!empty($this_values['robot_alts']) ? $this_values['robot_alts'] : array());
                    $temp_hash = md5($this_save_array['save_values_robot_alts']);
                    if (isset($this_values['robot_alts_hash']) && $this_values['robot_alts_hash'] == $temp_hash){ unset($this_save_array['save_values_robot_alts']); }
                    unset($this_values['robot_alts'], $this_values['robot_alts_hash']);
                }
                /*
                if (!empty($this_values['robot_database']) || $reset_in_progress){
                    $this_save_array['save_values_robot_database'] = json_encode(!empty($this_values['robot_database']) ? $this_values['robot_database'] : array());
                    $temp_hash = md5($this_save_array['save_values_robot_database']);
                    if (isset($this_values['robot_database_hash']) && $this_values['robot_database_hash'] == $temp_hash){ unset($this_save_array['save_values_robot_database']); }
                    unset($this_values['robot_database'], $this_values['robot_database_hash']);
                }
                */

                unset($this_values['battle_index']);
                unset($this_values['battle_items'], $this_values['battle_items_hash']);
                unset($this_values['battle_abilities'], $this_values['battle_abilities_hash']);
                unset($this_values['battle_stars'], $this_values['battle_stars_hash']);
                unset($this_values['robot_database'], $this_values['robot_database_hash']);

                $this_save_array['save_id'] = $temp_save_id;
                $this_save_array['user_id'] = $temp_user_id;
                $this_save_array['save_counters'] = json_encode($this_counters);
                $this_save_array['save_values'] = json_encode($this_values);
                $this_save_array['save_flags'] = json_encode($this_flags);
                $this_save_array['save_settings'] = json_encode($this_settings);
                $this_save_array['save_cache_date'] = $this_cache_date;
                $this_save_array['save_date_created'] = $this_user_array['user_date_created'];
                $this_save_array['save_date_accessed'] = $this_user_array['user_date_accessed'];
                $this_save_array['save_date_modified'] = $this_user_array['user_date_modified'];

                // Insert these users into the database
                //echo('<hr /><pre>NEW DB USER/SAVE/BOARD UPDATES ($temp_user_id = '.$temp_user_id.')</pre>');
                //echo('<pre>$this_user_array = '.print_r($this_user_array, true).'</pre>');
                //echo('<pre>$this_save_array = '.print_r($this_save_array, true).'</pre>');
                //echo('<pre>$this_board_array = '.print_r($this_board_array, true).'</pre>');
                $this_user_array_return = $db->insert('mmrpg_users', $this_user_array);
                $this_save_array_return = $db->insert('mmrpg_saves', $this_save_array);
                $this_board_array_return = $db->insert('mmrpg_leaderboard', $this_board_array);
                unset($this_user_array, $this_save_array, $this_board_array);

                // Make sure relevant user-tables in the database are updated with any unlocks
                mmrpg_save_game_session_user_tables($session_token, $temp_user_id);

                // Update the ID in the user array and continue
                $this_user['userid'] = $temp_user_id;
                $_SESSION['GAME']['PENDING_LOGIN_ID'] = $temp_user_id;

                // We're done, we should return now
                return true;

            }
        }

        // If the user ID has not been set we cannot save
        if (empty($this_user['userid'])){
            error_log('mmrpg_save_game_session() failure!');
            error_log('ERROR: User ID not set for saving game session.');
            error_log('$this_user = '.print_r($this_user, true));
            return false;
        }

        // DEBUG
        $DEBUG = '';

        // Collect user IDs from the various tables to ensure/check they exist
        $check_tables = $db->get_array("SELECT
            `users`.`user_id`,
            `saves`.`save_id`,
            `saves`.`user_id` AS `save_user_id`,
            `board`.`user_id` AS `board_user_id`,
            (CASE WHEN `users`.`user_id` IS NOT NULL THEN 1 ELSE 0 END) AS `has_user`,
            (CASE WHEN `saves`.`user_id` IS NOT NULL THEN 1 ELSE 0 END) AS `has_save`,
            (CASE WHEN `board`.`user_id` IS NOT NULL THEN 1 ELSE 0 END) AS `has_board`
            FROM `mmrpg_users` AS `users`
            LEFT JOIN `mmrpg_saves` AS `saves` ON `saves`.`user_id` = `users`.`user_id`
            LEFT JOIN `mmrpg_leaderboard` AS `board` ON `board`.`user_id` = `users`.`user_id`
            WHERE
            `users`.`user_id` = {$this_user['userid']}
            ;");
        //error_log('$check_tables = '.print_r($check_tables, true));

        // Update the user modified and accessed date (everything else is saved via profile settings pages)
        $db->update('mmrpg_users', array(
            'user_date_modified' => time(),
            'user_date_accessed' => time()
            ), 'user_id = '.$this_user['userid']);

        // Define the save database update array and populate
        $this_save_array = array();
        /*
        if (!empty($this_values['battle_index']) || $reset_in_progress){
            unset($this_values['battle_index']);
        }
        */
        if (!empty($this_values['battle_complete']) || $reset_in_progress){
            $this_save_array['save_values_battle_complete'] = json_encode(!empty($this_values['battle_complete']) ? $this_values['battle_complete'] : array());
            $temp_hash = md5($this_save_array['save_values_battle_complete']);
            if (isset($this_values['battle_complete_hash']) && $this_values['battle_complete_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_complete']); }
            unset($this_values['battle_complete'], $this_values['battle_complete_hash']);
        }
        if (!empty($this_values['battle_failure']) || $reset_in_progress){
            $this_save_array['save_values_battle_failure'] = json_encode(!empty($this_values['battle_failure']) ? $this_values['battle_failure'] : array());
            $temp_hash = md5($this_save_array['save_values_battle_failure']);
            if (isset($this_values['battle_failure_hash']) && $this_values['battle_failure_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_failure']); }
            unset($this_values['battle_failure'], $this_values['battle_failure_hash']);
        }
        if (!empty($this_values['battle_rewards']) || $reset_in_progress){
            $this_save_array['save_values_battle_rewards'] = json_encode(!empty($this_values['battle_rewards']) ? $this_values['battle_rewards'] : array());
            $temp_hash = md5($this_save_array['save_values_battle_rewards']);
            if (isset($this_values['battle_rewards_hash']) && $this_values['battle_rewards_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_rewards']); }
            unset($this_values['battle_rewards'], $this_values['battle_rewards_hash']);
        }
        if (!empty($this_values['battle_settings']) || $reset_in_progress){
            $this_save_array['save_values_battle_settings'] = json_encode(!empty($this_values['battle_settings']) ? $this_values['battle_settings'] : array());
            $temp_hash = md5($this_save_array['save_values_battle_settings']);
            if (isset($this_values['battle_settings_hash']) && $this_values['battle_settings_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_settings']); }
            unset($this_values['battle_settings'], $this_values['battle_settings_hash']);
        }
        /*
        if (!empty($this_values['battle_items']) || $reset_in_progress){
            $this_save_array['save_values_battle_items'] = json_encode(!empty($this_values['battle_items']) ? $this_values['battle_items'] : array());
            $temp_hash = md5($this_save_array['save_values_battle_items']);
            if (isset($this_values['battle_items_hash']) && $this_values['battle_items_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_items']); }
            unset($this_values['battle_items'], $this_values['battle_items_hash']);
        }
        */
        /*
        if (!empty($this_values['battle_abilities']) || $reset_in_progress){
            $this_save_array['save_values_battle_abilities'] = json_encode(!empty($this_values['battle_abilities']) ? $this_values['battle_abilities'] : array());
            $temp_hash = md5($this_save_array['save_values_battle_abilities']);
            if (isset($this_values['battle_abilities_hash']) && $this_values['battle_abilities_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_abilities']); }
            unset($this_values['battle_abilities'], $this_values['battle_abilities_hash']);
        }
        */
        /*
        if (!empty($this_values['battle_stars']) || $reset_in_progress){
            $this_save_array['save_values_battle_stars'] = json_encode(!empty($this_values['battle_stars']) ? $this_values['battle_stars'] : array());
            $temp_hash = md5($this_save_array['save_values_battle_stars']);
            if (isset($this_values['battle_stars_hash']) && $this_values['battle_stars_hash'] == $temp_hash){ unset($this_save_array['save_values_battle_stars']); }
            unset($this_values['battle_stars'], $this_values['battle_stars_hash']);
        }
        */
        if (!empty($this_values['robot_alts']) || $reset_in_progress){
            $this_save_array['save_values_robot_alts'] = json_encode(!empty($this_values['robot_alts']) ? $this_values['robot_alts'] : array());
            $temp_hash = md5($this_save_array['save_values_robot_alts']);
            if (isset($this_values['robot_alts_hash']) && $this_values['robot_alts_hash'] == $temp_hash){ unset($this_save_array['save_values_robot_alts']); }
            unset($this_values['robot_alts'], $this_values['robot_alts_hash']);
        }
        /*
        if (!empty($this_values['robot_database']) || $reset_in_progress){
            $this_save_array['save_values_robot_database'] = json_encode(!empty($this_values['robot_database']) ? $this_values['robot_database'] : array());
            $temp_hash = md5($this_save_array['save_values_robot_database']);
            if (isset($this_values['robot_database_hash']) && $this_values['robot_database_hash'] == $temp_hash){ unset($this_save_array['save_values_robot_database']); }
            unset($this_values['robot_database'], $this_values['robot_database_hash']);
        }
        */

        unset($this_values['battle_index']);
        unset($this_values['battle_items'], $this_values['battle_items_hash']);
        unset($this_values['battle_abilities'], $this_values['battle_abilities_hash']);
        unset($this_values['battle_stars'], $this_values['battle_stars_hash']);
        unset($this_values['robot_database'], $this_values['robot_database_hash']);

        $this_save_array['save_counters'] = json_encode($this_counters);
        $this_save_array['save_values'] = json_encode($this_values);
        $this_save_array['save_flags'] = json_encode($this_flags);
        $this_save_array['save_settings'] = json_encode($this_settings);
        $this_save_array['save_cache_date'] = $this_cache_date;
        $this_save_array['save_date_modified'] = time();

        // Update this save's info in the database
        //echo('<hr /><pre>FINAL DB SAVES UPDATE (user_id = '.$this_user['userid'].')</pre>');
        //echo('<pre>$this_save_array = '.print_r($this_save_array, true).'</pre>');
        $db->update('mmrpg_saves', $this_save_array, 'user_id = '.$this_user['userid']);
        unset($this_save_array);

        // Make sure relevant user-tables in the database are updated with any unlocks
        mmrpg_save_game_session_user_tables($session_token, $this_user['userid']);


        // -- UPDATE LEADERBOARD RANKINGS -- //

        // Call the global battle points function to collect progress details
        mmrpg_prototype_calculate_battle_points_2k19($this_user['userid'], $battle_points_index);
        //error_log('<pre>$battle_points_index : '.print_r($battle_points_index, true).'</pre>');

        // Define the tokens for updating legacy player fields to ZERO
        $legacy_player_field_tokens = array('dr_light', 'dr_wily', 'dr_cossack');

        // Define the board database update array and populate
        $temp_robots_unlocked = !empty($battle_points_index['robots_unlocked']) ? $battle_points_index['robots_unlocked'] : array();
        $temp_abilities_unlocked = !empty($battle_points_index['abilities_unlocked']) ? $battle_points_index['abilities_unlocked'] : array();
        $temp_field_stars_collected = !empty($battle_points_index['field_stars_collected']) ? $battle_points_index['field_stars_collected'] : array();
        $temp_fusion_stars_collected = !empty($battle_points_index['fusion_stars_collected']) ? $battle_points_index['fusion_stars_collected'] : array();
        $temp_items_unlocked = !empty($battle_points_index['items_unlocked']) ? $battle_points_index['items_unlocked'] : array();
        $this_board_array = array();
        $this_board_array['board_points'] = $battle_points_index['total_battle_points'];
            foreach ($legacy_player_field_tokens AS $ptoken){ $this_board_array['board_points_'.$ptoken] = 0; }
        $this_board_array['board_robots'] = implode(',', $temp_robots_unlocked);
        $this_board_array['board_robots_count'] = count($temp_robots_unlocked);
            foreach ($legacy_player_field_tokens AS $ptoken){ $this_board_array['board_robots_'.$ptoken] = ''; }
        $this_board_array['board_abilities'] = count($temp_abilities_unlocked);
            foreach ($legacy_player_field_tokens AS $ptoken){ $this_board_array['board_abilities_'.$ptoken] = 0; }
        $this_board_array['board_stars'] = count($temp_field_stars_collected) + count($temp_fusion_stars_collected);
            foreach ($legacy_player_field_tokens AS $ptoken){ $this_board_array['board_stars_'.$ptoken] = 0; }
        $this_board_array['board_items'] = count($temp_items_unlocked);
        $this_board_array['board_battles'] = 0;
            foreach ($legacy_player_field_tokens AS $ptoken){ $this_board_array['board_battles_'.$ptoken] = 0; }
        $this_board_array['board_missions'] = 0;
            foreach ($legacy_player_field_tokens AS $ptoken){ $this_board_array['board_missions_'.$ptoken] = 0; }
        $this_board_array['board_awards'] = !empty($this_values['prototype_awards']) ? array_keys($this_values['prototype_awards']) : '';
        $this_board_array['board_awards'] = !empty($this_board_array['board_awards']) ? implode(',', $this_board_array['board_awards']) : '';
        $this_board_array['board_zenny'] = !empty($this_counters['battle_zenny']) ? $this_counters['battle_zenny'] : 0;
        $this_board_array['board_date_modified'] = time();

        // Update this board's info in the database
        //error_log('<pre>$this_board_array : '.print_r($this_board_array, true).'</pre>');
        if (!empty($check_tables['has_board'])){
            $db->update('mmrpg_leaderboard', $this_board_array, 'user_id = '.$this_user['userid']);
            unset($this_board_array);
        } else {
            $this_board_array['user_id'] = $check_tables['user_id'];
            $this_board_array['save_id'] = $check_tables['save_id'];
            $db->insert('mmrpg_leaderboard', $this_board_array);
            unset($this_board_array);
        }

        // Update the session points to the new value if set
        if (!empty($battle_points_index['total_battle_points'])){
            $session_battle_points = $battle_points_index['total_battle_points'];
            $session_board_rank = mmrpg_prototype_leaderboard_rank($this_user['userid']);
            $GAME_SESSION['counters']['battle_points'] = $session_battle_points;
            $GAME_SESSION['BOARD']['boardrank'] = $session_board_rank;
        }

    }

    // -- NORMAL MODE SAVE WORLD SESSION -- //
    if (!empty($WORLD_SESSION)){

        //error_log('Saving world session for user ID '.$this_user['userid']);

        // UPDATE DATABASE INFO

        // Collect the world ID if it exists, else create one for this user
        $get_world_id_query = "SELECT `world_id` FROM `mmrpg_users_worlds` WHERE `user_id` = {$this_user['userid']};";
        $world_id = $db->get_value($get_world_id_query, 'world_id');
        $user_id = $this_user['userid'];
        if (empty($world_id)){
            $db->insert('mmrpg_users_worlds', array(
                'user_id' => $user_id,
                'world_date_created' => time(),
                'world_date_accessed' => time(),
                'world_date_modified' => time()
                ));
            $world_id = $db->get_value($get_world_id_query, 'world_id');
            if (empty($world_id)){
                error_log('mmrpg_save_game_session() failure!');
                error_log('ERROR: World ID does not exist and could not be created for user_id '.$user_id.'!');
                return false;
                }
            }
        //error_log('$world_id = '.print_r($world_id, true));
        //error_log('$user_id = '.print_r($user_id, true));

        // Wrao the collection of world data in a function to limit scope and reduce markup
        $get_json_encoded = function($array){ return json_encode($array, JSON_NUMERIC_CHECK); };
        $get_world_save_data = function($WORLD_SESSION, $world_id, $user_id) use ($GAME_SESSION, $get_json_encoded){

            // Collect the rest of the world info from the session
            $world_cache_date = MMRPG_CONFIG_CACHE_DATE;
            $world_date_created = !empty($WORLD_SESSION['date_created']) ? $WORLD_SESSION['date_created'] : time();
            $world_date_accessed = time();
            $world_date_modified = time();
            //error_log('$world_cache_date = '.print_r($world_cache_date, true));
            //error_log('$world_date_created = '.print_r($world_date_created, true));
            //error_log('$world_date_accessed = '.print_r($world_date_accessed, true));
            //error_log('$world_date_modified = '.print_r($world_date_modified, true));
            $last_world_token = !empty($WORLD_SESSION['last_world_token']) ? $WORLD_SESSION['last_world_token'] : '';
            $last_map_token = !empty($WORLD_SESSION['last_map_token']) ? $WORLD_SESSION['last_map_token'] : '';
            $last_player_token = !empty($WORLD_SESSION['last_player_token']) ? $WORLD_SESSION['last_player_token'] : '';
            if (empty($last_player_token) && !empty($WORLD_SESSION['player_sessions']['last_player'])){ $last_player_token = $WORLD_SESSION['player_sessions']['last_player']; }
            //error_log('$last_world_token = '.print_r($last_world_token, true));
            //error_log('$last_map_token = '.print_r($last_map_token, true));
            //error_log('$last_player_token = '.print_r($last_player_token, true));
            $player_sessions = !empty($WORLD_SESSION['player_sessions']) ? $WORLD_SESSION['player_sessions'] : array();
            $robot_sessions = !empty($WORLD_SESSION['robot_sessions']) ? $WORLD_SESSION['robot_sessions'] : array();
            $mecha_sessions = !empty($WORLD_SESSION['mecha_sessions']) ? $WORLD_SESSION['mecha_sessions'] : array();
            //error_log('$player_sessions = '.print_r($player_sessions, true));
            //error_log('$robot_sessions = '.print_r($robot_sessions, true));
            //error_log('$mecha_sessions = '.print_r($mecha_sessions, true));
            $world_maps = !empty($WORLD_SESSION['world_maps']) ? $WORLD_SESSION['world_maps'] : array();
            $world_buttons = !empty($WORLD_SESSION['world_buttons']) ? $WORLD_SESSION['world_buttons'] : array();
            $world_switches = !empty($WORLD_SESSION['world_switches']) ? $WORLD_SESSION['world_switches'] : array();
            $world_gates = !empty($WORLD_SESSION['world_gates']) ? $WORLD_SESSION['world_gates'] : array();
            $world_locks = !empty($WORLD_SESSION['world_locks']) ? $WORLD_SESSION['world_locks'] : array();
            $world_blocks = !empty($WORLD_SESSION['world_blocks']) ? $WORLD_SESSION['world_blocks'] : array();
            $world_hazards = !empty($WORLD_SESSION['world_hazards']) ? $WORLD_SESSION['world_hazards'] : array();
            $world_items = !empty($WORLD_SESSION['world_items']) ? $WORLD_SESSION['world_items'] : array();
            $world_abilities = !empty($WORLD_SESSION['world_abilities']) ? $WORLD_SESSION['world_abilities'] : array();
            $world_encounters = !empty($WORLD_SESSION['world_encounters']) ? $WORLD_SESSION['world_encounters'] : array();
            $world_pickups = !empty($WORLD_SESSION['world_pickups']) ? $WORLD_SESSION['world_pickups'] : array();
            $world_actors = !empty($WORLD_SESSION['world_actors']) ? $WORLD_SESSION['world_actors'] : array();
            $world_symbols = !empty($WORLD_SESSION['world_symbols']) ? $WORLD_SESSION['world_symbols'] : array();
            $world_events = !empty($WORLD_SESSION['world_events']) ? $WORLD_SESSION['world_events'] : array();
            $world_battles = !empty($GAME_SESSION['values']['battle_index']) ? $GAME_SESSION['values']['battle_index'] : array();
            foreach ($world_battles AS $token => $battle){
                if (substr($token, 0, 13) !== 'world-battle_'){
                    unset($world_battles[$token]);
                    continue;
                    } else {
                    $world_battles[$token] = json_decode($battle, true);
                    }
                }
            //error_log('$world_maps = '.print_r($world_maps, true));
            //error_log('$world_buttons = '.print_r($world_buttons, true));
            //error_log('$world_switches = '.print_r($world_switches, true));
            //error_log('$world_gates = '.print_r($world_gates, true));
            //error_log('$world_locks = '.print_r($world_locks, true));
            //error_log('$world_blocks = '.print_r($world_blocks, true));
            //error_log('$world_hazards = '.print_r($world_hazards, true));
            //error_log('$world_items = '.print_r($world_items, true));
            //error_log('$world_abilities = '.print_r($world_abilities, true));
            //error_log('$world_encounters = '.print_r($world_encounters, true));
            //error_log('$world_pickups = '.print_r($world_pickups, true));
            //error_log('$world_actors = '.print_r($world_actors, true));
            //error_log('$world_symbols = '.print_r($world_symbols, true));
            //error_log('$world_events = '.print_r($world_events, true));
            //error_log('$world_battles = '.print_r($world_battles, true));
            // Generate the return array with any encoding necessary
            $return_array = array(
                'world_cache_date' => $world_cache_date,
                'world_date_accessed' => $world_date_accessed,
                'world_date_modified' => $world_date_modified,
                'last_world_token' => $last_world_token,
                'last_map_token' => $last_map_token,
                'last_player_token' => $last_player_token,
                'player_sessions' => $get_json_encoded($player_sessions),
                'robot_sessions' => $get_json_encoded($robot_sessions),
                'mecha_sessions' => $get_json_encoded($mecha_sessions),
                'world_maps' => $get_json_encoded($world_maps),
                'world_buttons' => $get_json_encoded($world_buttons),
                'world_switches' => $get_json_encoded($world_switches),
                'world_gates' => $get_json_encoded($world_gates),
                'world_locks' => $get_json_encoded($world_locks),
                'world_blocks' => $get_json_encoded($world_blocks),
                'world_hazards' => $get_json_encoded($world_hazards),
                'world_items' => $get_json_encoded($world_items),
                'world_abilities' => $get_json_encoded($world_abilities),
                'world_encounters' => $get_json_encoded($world_encounters),
                'world_pickups' => $get_json_encoded($world_pickups),
                'world_actors' => $get_json_encoded($world_actors),
                'world_symbols' => $get_json_encoded($world_symbols),
                'world_events' => $get_json_encoded($world_events),
                'world_battles' => $get_json_encoded($world_battles),
                );
            // Return the generated world save data array
            return $return_array;
            };
        $world_save_data = $get_world_save_data($WORLD_SESSION, $world_id, $user_id);
        //error_log('$world_save_data = '.print_r($world_save_data, true));

        // Update the database with the new session data (if not empty of course)
        $success = false;
        if (!empty($world_save_data)){
            $success = $db->update('mmrpg_users_worlds', $world_save_data, array(
                'world_id' => $world_id,
                'user_id' => $user_id
                ));
            if ($success === false){
                //error_log('mmrpg_save_game_session() failure!');
                //error_log('ERROR: Unable to save world data to database for user_id '.$user_id.'!');
                }
            } else {
            //error_log('mmrpg_save_game_session() failure!');
            //error_log('ERROR: There was no world data to save for user_id '.$user_id.'!');
            }

    }

    // Unset the reset flag in the session
    unset($GAME_SESSION['RESET']);

    // Sync back to the actual session variable
    $_SESSION['GAME'] = $GAME_SESSION;
    $_SESSION['WORLD'] = $WORLD_SESSION;

    //echo('GAME has been saved!');
    //exit();

    // Return true on success
    //debug_profiler_checkpoint('func/save-game-session/after/');
    return true;

}

// Define a function for scanning session save data and updating relevant user tables in the database
function mmrpg_save_game_session_user_tables($session_token, $user_id){
    //error_log('mmrpg_save_game_session_user_tables('.$session_token.', '.$user_id.')');

    // Ensure the requested session array actually exists
    if (empty($_SESSION[$session_token])){ return false; }
    $GAME_SESSION = $_SESSION[$session_token];

    // If the save counters were not empty, we should update them in the database table
    if (!empty($GAME_SESSION['counters'])){
        $user_save_counters = $GAME_SESSION['counters'];
        //error_log('$user_save_counters = '.(!empty($user_save_counters) ? json_encode($user_save_counters) : '[]'));
        rpg_user::update_save_counters($user_id, $user_save_counters);
    }

    // If the robot database records were not empty, we should update them in the database table
    if (!empty($GAME_SESSION['values']['robot_database'])){
        $user_robot_records = $GAME_SESSION['values']['robot_database'];
        //error_log('$user_robot_records = '.(!empty($user_robot_records) ? json_encode($user_robot_records) : '[]'));
        rpg_user::update_robot_records($user_id, $user_robot_records);
    }

    // If the unlocked item list was not empty, we should update them in the database table
    if (!empty($GAME_SESSION['values']['battle_items'])){
        $user_unlocked_items = $GAME_SESSION['values']['battle_items'];
        //error_log('$user_unlocked_items = '.(!empty($user_unlocked_items) ? json_encode($user_unlocked_items) : '[]'));
        rpg_user::update_unlocked_items($user_id, $user_unlocked_items);
    }

    // If the unlocked ability list was not empty, we should update them in the database table
    if (!empty($GAME_SESSION['values']['battle_abilities'])){
        $user_unlocked_abilities = $GAME_SESSION['values']['battle_abilities'];
        //error_log('$user_unlocked_abilities = '.(!empty($user_unlocked_abilities) ? json_encode($user_unlocked_abilities) : '[]'));
        rpg_user::update_unlocked_abilities($user_id, $user_unlocked_abilities);
    }

    // If the unlocked star list was not empty, we should update them in the database table
    if (!empty($GAME_SESSION['values']['battle_stars'])){
        $user_unlocked_stars = $GAME_SESSION['values']['battle_stars'];
        //error_log('$user_unlocked_stars = '.(!empty($user_unlocked_stars) ? json_encode($user_unlocked_stars) : '[]'));
        rpg_user::update_unlocked_stars($user_id, $user_unlocked_stars);
    }

}

?>