<?php

// RESET PROTOTYPE (OTHER)

exit('NOT READY YET!!!');

// Collect a reference to the user object
$this_user = $_SESSION[$session_token]['USER'];
$user_id = rpg_user::get_current_userid();

// Collect the content tokens we'll be resetting in this action
$reset_tokens = strstr($_REQUEST['reset'], ',') ? explode(',', $_REQUEST['reset']) : array($_REQUEST['reset']);

// If the user has a request to RESET MISSIONS we should apply that now
if (in_array('missions', $reset_tokens)){
    error_log('resetting missions');

    // Pull necessary indexes for this action
    $mmrpg_index_players = rpg_player::get_index(true);

    // Pull backups of the battles complete and failure so we can reward them later
    $this_battle_complete = !empty($_SESSION[$session_token]['values']['battle_complete']) ? $_SESSION[$session_token]['values']['battle_complete'] : array();
    $this_battle_failure = !empty($_SESSION[$session_token]['values']['battle_failure']) ? $_SESSION[$session_token]['values']['battle_failure'] : array();
    $this_turns_total = !empty($_SESSION[$session_token]['counters']['battle_turns_total']) ? $_SESSION[$session_token]['counters']['battle_turns_total'] : 0;

    // Reset the battle complete and failure arrays to empty and clear the database
    $clear_event_flags = array(
        '-event-97_phase-one-complete',
        '-event-97_phase-two-complete',
        '-event-97_phase-three-complete'
        );
    $_SESSION[$session_token]['values']['battle_index'] = array();
    $_SESSION[$session_token]['values']['battle_complete'] = array();
    $_SESSION[$session_token]['values']['battle_failure'] = array();
    $_SESSION[$session_token]['counters']['battle_turns_total'] = 0;
    foreach ($mmrpg_index_players AS $ptoken => $info){
        $pxtoken = str_replace('dr-', '', $ptoken);
        //error_log('resetting battle settings for '.$ptoken.' / '.$pxtoken);
        $_SESSION[$session_token]['counters']['battle_turns_'.$ptoken.'_total'] = 0;
        $temp_omega_key = $ptoken.'_target-robot-omega_prototype';
        //error_log('-- reset ['.$session_token.'][values]['.$temp_omega_key.']');
        $_SESSION[$session_token]['values'][$temp_omega_key] = array();
        foreach ($clear_event_flags AS $event_flag){
            $clear_event_flag = $ptoken.$event_flag;
            //error_log('-- unset ['.$session_token.'][flags][events]['.$clear_event_flag.']');
            unset($_SESSION[$session_token]['flags']['events'][$clear_event_flag]);
        }
        for ($i = 0; $i <= 10; $i++){
            $clear_event_flag = $ptoken.'_chapter-'.$i.'-unlocked';
            //error_log('-- unset ['.$session_token.'][flags][events]['.$clear_event_flag.']');
            unset($_SESSION[$session_token]['flags']['events'][$clear_event_flag]);
        }
        $clear_event_flag = $pxtoken.'_current_chapter';
        //error_log('-- reset ['.$session_token.'][battle_settings]['.$clear_event_flag.']');
        $_SESSION[$session_token]['battle_settings'][$clear_event_flag] = 0;
    }
    if (!empty($_SESSION[$session_token]['battle_settings']['flags'])){
        foreach ($_SESSION[$session_token]['battle_settings']['flags'] AS $flag => $value){
            if (!preg_match('/^([a-z0-9]+)_unlocked_chapter_([0-9]+)$/i', $flag)){ continue; }
            //error_log('-- unset ['.$session_token.'][battle_settings][flags]['.$flag.']');
            unset($_SESSION[$session_token]['battle_settings']['flags'][$flag]);
        }
    }
    //error_log('-- unset ['.$session_token.'][battle_settings][this_player_token]');
    unset($_SESSION[$session_token]['battle_settings']['this_player_token']);

    // Define some compensation values for the battles complete, failure, and total turns
    $recycled_battle_complete_value = !empty($this_battle_complete) ? ceil(count($this_battle_complete) * MMRPG_SETTINGS_BATTLEPOINTS_PERMISSION) : 0;
    $recycled_battle_failure_value = !empty($this_battle_failure) ? ceil(count($this_battle_failure) * (MMRPG_SETTINGS_BATTLEPOINTS_PERMISSION / 2)) : 0;
    $recycled_battle_turns_value = !empty($this_turns_total) ? ceil($this_turns_total * (MMRPG_SETTINGS_BATTLEPOINTS_PERMISSION / 3)) : 0;
    error_log('$recycled_battle_complete_value: '.number_format($recycled_battle_complete_value, 0, '.', ','));
    error_log('$recycled_battle_failure_value: '.number_format($recycled_battle_failure_value, 0, '.', ','));
    error_log('$recycled_battle_turns_value: '.number_format($recycled_battle_turns_value, 0, '.', ','));

    // Compensate the player for all the lost mission progress with some extra money for fun
    $current_zenny = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
    $new_zenny = $current_zenny + $recycled_battle_complete_value + $recycled_battle_failure_value + $recycled_battle_turns_value;
    $_SESSION[$session_token]['counters']['battle_zenny'] = $new_zenny;
    error_log('$current_zenny: '.number_format($current_zenny, 0, '.', ','));
    error_log('$new_zenny: '.number_format($new_zenny, 0, '.', ','));
    error_log(' => +'.number_format($new_zenny - $current_zenny, 0, '.', ','));

    // Make sure we also clear any endless mode savestates so things don't get weird
    $db->update('mmrpg_challenges_waveboard',
        array('challenge_wave_savestate' => ''),
        array('user_id' => $user_id)
        );

}

// If the user has a request to RESET ROBOTS we should apply that now
if (in_array('robots', $reset_tokens)){
    error_log('resetting robots');

    // Pull necessary indexes for this action
    $mmrpg_index_players = rpg_player::get_index(true);
    $mmrpg_index_robots = rpg_robot::get_index(true);
    $mmrpg_index_abilities = rpg_ability::get_index(true);

    // Reset the battle robots array to empty and clear the database
    $_SESSION[$session_token]['values']['battle_robots'] = array();
    $db->delete('mmrpg_users_robots_records', array('user_id' => $user_id));
    //error_log('-- unset ['.$session_token.'][battle_settings][menu_frame_robots_unseen]');
    unset($_SESSION[$session_token]['battle_settings']['menu_frame_robots_unseen']);

    // Collect any already-unlocked abilities at the player level
    $unlocked_battle_abilities = !empty($_SESSION[$session_token]['values']['battle_abilities']) ? $_SESSION[$session_token]['values']['battle_abilities'] : array();

    // Loop through players and reset their robots
    foreach ($mmrpg_index_players AS $ptoken => $info){

        // Collect the current rewards and settings for this player
        $rewards = !empty($_SESSION[$session_token]['values']['battle_rewards'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_rewards'][$ptoken] : array();
        $settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_settings'][$ptoken] : array();

        // Loop through this player's robots one-by-one and do the same thing for their data too but settings instead of rewards
        $probot_rewards = !empty($rewards['player_robots']) ? $rewards['player_robots'] : array();
        $probot_settings = !empty($settings['player_robots']) ? $settings['player_robots'] : array();
        if (empty($probot_rewards) && empty($probot_settings)){ continue; }
        $probot_tokens = array_unique(array_merge(array_keys($probot_rewards), array_keys($probot_settings)));
        foreach ($probot_tokens AS $prkey => $prtoken){
            // reset this robot's level to one and it's abilities to level-up
            $prinfo = $mmrpg_index_robots[$prtoken];
            $prabilities = array();
            $prabilities['buster-shot'] = array('ability_token' => 'buster-shot');
            if (!empty($prinfo['robot_rewards']['abilities'])){
                foreach ($prinfo['robot_rewards']['abilities'] AS $akey => $ainfo){
                    if (!empty($ainfo['level']) && $ainfo['level'] > 1){ continue; }
                    $unlocked = in_array($ainfo['token'], $unlocked_battle_abilities) ? true : false;
                    if (!$unlocked){ continue; }
                    $prabilities[$ainfo['token']] = array('ability_token' => $ainfo['token']);
                }
            }
            if (!empty($probot_rewards[$prtoken])){
                $_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$prtoken]['robot_level'] = 1;
                $_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$prtoken]['robot_experience'] = 999;
                $_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$prtoken]['robot_abilities'] = $prabilities;
                //error_log('-- set ['.$session_token.'][values][battle_rewards]['.$ptoken.'][player_robots]['.$prtoken.'][robot_level] to 1');
                //error_log('-- set ['.$session_token.'][values][battle_rewards]['.$ptoken.'][player_robots]['.$prtoken.'][robot_experience] to 999');
                //error_log('-- set ['.$session_token.'][values][battle_rewards]['.$ptoken.'][player_robots]['.$prtoken.'][robot_abilities] to '.print_r($prabilities, true));
            }
            if (!empty($probot_settings[$prtoken])){
                //unset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$prtoken]['robot_item']);
                //unset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$prtoken]['robot_support']);
                $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$prtoken]['robot_abilities'] = $prabilities;
                //error_log('-- unset ['.$session_token.'][values][battle_settings]['.$ptoken.'][player_robots]['.$prtoken.'][robot_item]');
                //error_log('-- unset ['.$session_token.'][values][battle_settings]['.$ptoken.'][player_robots]['.$prtoken.'][robot_support]');
                //error_log('-- set ['.$session_token.'][values][battle_settings]['.$ptoken.'][player_robots]['.$prtoken.'][robot_abilities] to '.print_r($prabilities, true));
            }
        }

    }

    // Loop through master robots array, plucking player's unlocked into new array
    $unlocked_user_robots = array();
    foreach ($mmrpg_index_robots AS $rtoken => $rinfo){
        $rewards = array();
        $settings = array();
        $ptoken = '';
        foreach ($mmrpg_index_players AS $ptoken => $pinfo){
            $rewards = !empty($_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken]) ? $_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken] : array();
            $settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]) ? $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken] : array();
            if (!empty($rewards) || !empty($settings)){ break; }
        }
        if (empty($rewards) && empty($settings)){ continue; }
        $current_player = !empty($settings['current_player']) ? $settings['current_player'] : $ptoken;
        $original_player = !empty($settings['original_player']) ? $settings['original_player'] : $ptoken;
        if ($rtoken === 'mega-man'){ $original_player = 'dr-light'; }
        elseif ($rtoken === 'bass'){ $original_player = 'dr-wily'; }
        elseif ($rtoken === 'proto-man'){ $original_player = 'dr-cossack'; }
        $unlocked_user_robots[$rtoken] = array(
            'robot_token' => $rtoken,
            'current_player' => $current_player,
            'original_player' => $original_player,
            'rewards' => $rewards,
            'settings' => $settings
            );
    }

    // Loop through each unlocked player, clear the player_robots arrays in their rewards and settings,
    // then re-populate according to original player data collected above
    foreach ($mmrpg_index_players AS $ptoken => $info){

        // Collect the current rewards and settings for this player
        $rewards = !empty($_SESSION[$session_token]['values']['battle_rewards'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_rewards'][$ptoken] : array();
        $settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_settings'][$ptoken] : array();

        // Loop through this player's robots one-by-one and do the same thing for their data too but settings instead of rewards
        $probots = !empty($settings['player_robots']) ? $settings['player_robots'] : array();
        if (empty($probots)){ continue; }
        foreach ($probots AS $rkey => $rinfo){
            unset($_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$rkey]);
            unset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rkey]);
            //error_log('-- unset ['.$session_token.'][values][battle_rewards]['.$ptoken.'][player_robots]['.$rkey.']');
            //error_log('-- unset ['.$session_token.'][values][battle_settings]['.$ptoken.'][player_robots]['.$rkey.']');
        }

        // If the user has request to PURGE (via DELETE ALL ROBOTS) we should apply that now
        if (in_array('purge', $reset_tokens)){
            error_log('and also purging and deleting them from '.$ptoken);

            // Loop through the unlocked robots and re-populate the player_robots arrays
            foreach ($unlocked_user_robots AS $rtoken => $rinfo){
                unset($_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken]);
                unset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]);
                unset($_SESSION[$session_token]['values']['robot_database'][$rtoken]['robot_unlocked']);
                unset($_SESSION[$session_token]['flags']['events']['unlocked-robot_'.$rtoken]);
                unset($_SESSION[$session_token]['flags']['events']['unlocked-robot_'.$ptoken.'_'.$rtoken]);
                //error_log('-- unset ['.$session_token.'][values][battle_rewards]['.$ptoken.'][player_robots]['.$rtoken.']');
                //error_log('-- unset ['.$session_token.'][values][battle_settings]['.$ptoken.'][player_robots]['.$rtoken.']');
                //error_log('-- unset ['.$session_token.'][values][robot_database]['.$rtoken.'][robot_unlocked]');
                //error_log('-- unset ['.$session_token.'][flags][events][unlocked-robot_'.$rtoken.']');
                //error_log('-- unset ['.$session_token.'][flags][events][unlocked-robot_'.$ptoken.'_'.$rtoken.']');
            }

        }
        // Otherwise we will simply MOVE (via REBOOT ALL ROBOTS) them to their original owners
        else {
            error_log('and them moving them to original owner '.$ptoken);

            // Loop through the unlocked robots and re-populate the player_robots arrays
            foreach ($unlocked_user_robots AS $rtoken => $rinfo){
                if ($rinfo['original_player'] !== $ptoken){ continue; }
                $_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken] = $rinfo['rewards'];
                $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken] = $rinfo['settings'];
                //error_log('-- set ['.$session_token.'][values][battle_rewards]['.$ptoken.'][player_robots]['.$rtoken.'] to '.print_r($rinfo['rewards'], true));
                //error_log('-- set ['.$session_token.'][values][battle_settings]['.$ptoken.'][player_robots]['.$rtoken.'] to '.print_r($rinfo['settings'], true));
                unset($unlocked_user_robots[$rtoken]);
            }

            // If there are any leftover robots, they should be added to the player's rewards and settings
            if (!empty($unlocked_user_robots)){
                foreach ($unlocked_user_robots AS $rtoken => $rinfo){
                    if ($rinfo['current_player'] !== $ptoken){ continue; }
                    $_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_robots'][$rtoken] = $rinfo['rewards'];
                    $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken] = $rinfo['settings'];
                    //error_log('-- set ['.$session_token.'][values][battle_rewards]['.$ptoken.'][player_robots]['.$rtoken.'] to '.print_r($rinfo['rewards'], true));
                    //error_log('-- set ['.$session_token.'][values][battle_settings]['.$ptoken.'][player_robots]['.$rtoken.'] to '.print_r($rinfo['settings'], true));
                    unset($unlocked_user_robots[$rtoken]);
                }
            }

        }

    }

}

// If the user has request to RESET ABILITIES we should apply that now
if (in_array('abilities', $reset_tokens)){
    error_log('resetting abilities');

    // Pull necessary indexes for this action
    $mmrpg_index_players = rpg_player::get_index(true);
    $mmrpg_index_robots = rpg_robot::get_index(true);
    $mmrpg_index_abilities = rpg_ability::get_index(true);

    // Collect the current battle settings and rewards for reference
    $this_battle_settings = !empty($_SESSION[$session_token]['values']['battle_settings']) ? $_SESSION[$session_token]['values']['battle_settings'] : array();
    $this_battle_rewards = !empty($_SESSION[$session_token]['values']['battle_rewards']) ? $_SESSION[$session_token]['values']['battle_rewards'] : array();

    // Define a new list of unlocked abilities based on the current robots
    $new_unlocked_abilities = array();
    $new_unlocked_abilities[] = 'buster-shot';
    if (!empty($this_battle_rewards)){
        foreach ($this_battle_rewards AS $ptoken => $pinfo){
            $new_unlocked_abilities[] = $ptoken.'-buster';
            if (empty($pinfo['player_robots'])){ continue; }
            foreach ($pinfo['player_robots'] AS $rtoken => $rinfo){
                $level = !empty($rinfo['robot_level']) ? $rinfo['robot_level'] : 1;
                $rindexinfo = $mmrpg_index_robots[$rtoken];
                if (!empty($rindexinfo['robot_rewards']['abilities'])){
                    foreach ($rindexinfo['robot_rewards']['abilities'] AS $akey => $ainfo){
                        if ($level < $ainfo['level']){ continue; }
                        $new_unlocked_abilities[] = $ainfo['token'];
                    }
                }
            }
        }
    }
    $new_unlocked_abilities = array_unique($new_unlocked_abilities);
    //error_log('$new_unlocked_abilities: '.print_r($new_unlocked_abilities, true));

    // Loop through players (and their robots) and remove any abilities that are not part of the above set
    $removed_abilities = array();
    foreach ($mmrpg_index_players AS $ptoken => $info){

        // Collect the current rewards and settings for this player
        $rewards = !empty($_SESSION[$session_token]['values']['battle_rewards'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_rewards'][$ptoken] : array();
        $settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_settings'][$ptoken] : array();

        // Check this player's abilities and remove any that are not unlocked via the new array above
        $current = !empty($rewards['player_abilities']) ? $rewards['player_abilities'] : array();
        if (empty($current)){ continue; }
        $changed = false;
        foreach ($current AS $atoken => $ainfo){
            if (in_array($atoken, $new_unlocked_abilities)){ continue; }
            $removed_abilities[] = $atoken;
            unset($current[$atoken]);
            $changed = true;
        }
        if ($changed){
            $_SESSION[$session_token]['values']['battle_rewards'][$ptoken]['player_abilities'] = $current;
            //error_log('-- set ['.$session_token.'][values][battle_rewards]['.$ptoken.'][player_abilities] to '.print_r($current, true));
        }

        // Loop through this player's robots one-by-one and do the same thing for their data too but settings instead of rewards
        $probots = !empty($settings['player_robots']) ? $settings['player_robots'] : array();
        if (empty($probots)){ continue; }
        foreach ($probots AS $rkey => $rinfo){
            $current = !empty($settings['player_robots'][$rkey]['robot_abilities']) ? $settings['player_robots'][$rkey]['robot_abilities'] : array();
            if (empty($current)){ continue; }
            $changed = false;
            foreach ($current AS $atoken => $ainfo){
                if (in_array($atoken, $new_unlocked_abilities)){ continue; }
                $removed_abilities[] = $atoken;
                unset($current[$atoken]);
                $changed = true;
            }
            if ($changed){
                $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rkey]['robot_abilities'] = $current;
                //error_log('-- set ['.$session_token.'][values][battle_settings]['.$ptoken.'][player_robots]['.$rkey.'][robot_abilities] to '.print_r($current, true));
            }
        }

    }
    $removed_abilities = array_unique($removed_abilities);
    //error_log('$removed_abilities: '.print_r($removed_abilities, true));

    // Calculate the removed ability value so we can give the player some zenny back
    $removed_ability_value = 0;
    foreach ($removed_abilities AS $key => $token){
        $ability_price = !empty($mmrpg_index_abilities[$token]['ability_price']) ? $mmrpg_index_abilities[$token]['ability_price'] : 0;
        $ability_value = !empty($mmrpg_index_abilities[$token]['ability_value']) ? $mmrpg_index_abilities[$token]['ability_value'] : 0;
        $removed_ability_value += !empty($ability_price) ? $ability_price : $ability_value;
    }
    //error_log('$removed_ability_value: '.print_r($removed_ability_value, true));
    $recycled_ability_value = floor($removed_ability_value * 0.5);
    //error_log('$recycled_ability_value: '.print_r($recycled_ability_value, true));

    // Add the recycled ability value to the battle zenny counter
    $current_zenny = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
    $new_zenny = $current_zenny + $recycled_ability_value;
    $_SESSION[$session_token]['counters']['battle_zenny'] = $new_zenny;
    error_log('$current_zenny: '.number_format($current_zenny, 0, '.', ','));
    error_log('$new_zenny: '.number_format($new_zenny, 0, '.', ','));
    error_log(' => +'.number_format($new_zenny - $current_zenny, 0, '.', ','));

    // Reset the battle abilities array to empty and then attempt to repopulate via robots
    $_SESSION[$session_token]['values']['battle_abilities'] = $new_unlocked_abilities;
    $db->delete('mmrpg_users_abilities_unlocked', array('user_id' => $user_id));
    //error_log('-- unset ['.$session_token.'][battle_settings][menu_frame_abilities_unseen]');
    unset($_SESSION[$session_token]['battle_settings']['menu_frame_abilities_unseen']);

}

// If the user has request to RESET ITEMS we should apply that now
if (in_array('items', $reset_tokens)){
    error_log('resetting items');

    // Pull necessary indexes for this action
    $mmrpg_index_players = rpg_player::get_index(true);
    $mmrpg_index_robots = rpg_robot::get_index(true);
    $mmrpg_index_items = rpg_item::get_index(true);
    $mmrpg_index_abilities = rpg_ability::get_index(true);

    // Define a list of which items we should keep before resetting (only event items)
    $keep_items = array();
    $existing_items = !empty($_SESSION[$session_token]['values']['battle_items']) ? $_SESSION[$session_token]['values']['battle_items'] : array();
    foreach ($existing_items AS $token => $quantity){
        if (empty($mmrpg_index_items[$token])){ continue; }
        if ($mmrpg_index_items[$token]['item_subclass'] !== 'event'){ continue; }
        $keep_items[$token] = $quantity;
    }
    $removed_items = array_diff_key($existing_items, $keep_items);
    //error_log('$existing_items: '.print_r($existing_items, true));
    //error_log('keep_items: '.print_r($keep_items, true));
    //error_log('removed_items: '.print_r($removed_items, true));

    // Loop through players (and their robots) and remove any equipped items
    foreach ($mmrpg_index_players AS $ptoken => $info){

        // Collect the current rewards and settings for this player
        $rewards = !empty($_SESSION[$session_token]['values']['battle_rewards'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_rewards'][$ptoken] : array();
        $settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_settings'][$ptoken] : array();

        // Loop through this player's robots one-by-one and do the same thing for their data too but settings instead of rewards
        $probots = !empty($settings['player_robots']) ? $settings['player_robots'] : array();
        if (empty($probots)){ continue; }
        foreach ($probots AS $rkey => $rinfo){
            $current_item = !empty($settings['player_robots'][$rkey]['robot_item']) ? $settings['player_robots'][$rkey]['robot_item'] : '';
            if (empty($current_item)){ continue; }
            $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rkey]['robot_item'] = '';
            //error_log('-- unset ['.$session_token.'][values][battle_settings]['.$ptoken.'][player_robots]['.$rkey.'][robot_item]');
            if (!isset($removed_items[$current_item])){ $removed_items[$current_item] = 0; }
            $removed_items[$current_item]++;
        }

    }

    // Calculate the removed item value so we can give the player some zenny back
    $removed_item_value = 0;
    foreach ($removed_items AS $token => $quantity){
        $item_price = !empty($mmrpg_index_items[$token]['item_price']) ? $mmrpg_index_items[$token]['item_price'] : 0;
        $item_value = !empty($mmrpg_index_items[$token]['item_value']) ? $mmrpg_index_items[$token]['item_value'] : 0;
        $removed_item_value += !empty($item_price) ? ($item_price * $quantity) : ($item_value * $quantity);
    }
    //error_log('$removed_item_value: '.print_r($removed_item_value, true));
    $recycled_item_value = floor($removed_item_value * 0.5);
    //error_log('$recycled_item_value: '.print_r($recycled_item_value, true));

    // Add the recycled item value to the battle zenny counter
    $current_zenny = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
    $new_zenny = $current_zenny + $recycled_item_value;
    $_SESSION[$session_token]['counters']['battle_zenny'] = $new_zenny;
    error_log('$current_zenny: '.number_format($current_zenny, 0, '.', ','));
    error_log('$new_zenny: '.number_format($new_zenny, 0, '.', ','));
    error_log(' => +'.number_format($new_zenny - $current_zenny, 0, '.', ','));

    // Reset the battle items array to empty but make sure we keep all the event items
    $_SESSION[$session_token]['values']['battle_items'] = $keep_items;
    $db->delete('mmrpg_users_items_unlocked', array('user_id' => $user_id));
    //error_log('-- unset ['.$session_token.'][battle_settings][menu_frame_items_unseen]');
    unset($_SESSION[$session_token]['battle_settings']['menu_frame_items_unseen']);

}

// If the user has request to RESET STARS we should apply that now
if (in_array('stars', $reset_tokens)){
    error_log('resetting stars');

    // Pull necessary indexes for this action
    $mmrpg_index_items = rpg_item::get_index(true);

    // Pull the currently collected stars so we can reference them
    $num_field = 0;
    $num_fusion = 0;
    $existing_stars = !empty($_SESSION[$session_token]['values']['battle_stars']) ? $_SESSION[$session_token]['values']['battle_stars'] : array();
    if (!empty($existing_stars)){
        foreach ($existing_stars AS $stoken => $sinfo){
            if ($sinfo['star_kind'] === 'fusion'){ $num_fusion++; }
            else { $num_field++; }
        }
    }
    //error_log('$num_field: '.print_r($num_field, true));
    //error_log('$num_fusion: '.print_r($num_fusion, true));

    // Calculate the value of all the stars we're about to remove
    $removed_star_value = 0;
    $removed_star_value += $num_field * $mmrpg_index_items['field-star']['item_value'];
    $removed_star_value += $num_fusion * $mmrpg_index_items['fusion-star']['item_value'];
    //error_log('$removed_star_value: '.print_r($removed_star_value, true));
    $recycled_star_value = floor($removed_star_value * 0.5);
    //error_log('$recycled_star_value: '.print_r($recycled_star_value, true));

    // Add the recycled star value to the battle zenny counter
    $current_zenny = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
    $new_zenny = $current_zenny + $recycled_star_value;
    $_SESSION[$session_token]['counters']['battle_zenny'] = $new_zenny;
    error_log('$current_zenny: '.number_format($current_zenny, 0, '.', ','));
    error_log('$new_zenny: '.number_format($new_zenny, 0, '.', ','));
    error_log(' => +'.number_format($new_zenny - $current_zenny, 0, '.', ','));

    // Reset the battle stars array to empty and clear the database
    $_SESSION[$session_token]['values']['battle_stars'] = array();
    $db->delete('mmrpg_users_stars_unlocked', array('user_id' => $user_id));

}

// If the user has request to RESET DATABASE we should apply that now
if (in_array('database', $reset_tokens)){
    error_log('resetting database');

    // Pull necessary indexes for this action
    $mmrpg_index_players = rpg_player::get_index(true);
    $mmrpg_index_robots = rpg_robot::get_index(true);
    $mmrpg_index_abilities = rpg_ability::get_index(true);

    // Pull a copy of the existing database to start so we can keep certain entries
    $existing_database = !empty($_SESSION[$session_token]['values']['robot_database']) ? $_SESSION[$session_token]['values']['robot_database'] : array();
    error_log('$existing_database: '.print_r($existing_database, true));

    // Calculate how valuable the records are so we can give the user some kickback
    $existing_database_value = 0;
    $existing_database_stats = array('discovered' => 0, 'encountered' => 0, 'defeated' => 0, 'summoned' => 0, 'scanned' => 0);
    if (!empty($existing_database)){
        foreach ($existing_database AS $robot => $records){
            $existing_database_stats['discovered']++;
            $existing_database_stats['encountered'] += !empty($records['robot_encountered']) ? $records['robot_encountered'] : 0;
            $existing_database_stats['defeated'] += !empty($records['robot_defeated']) ? $records['robot_defeated'] : 0;
            $existing_database_stats['summoned'] += !empty($records['robot_summoned']) ? $records['robot_summoned'] : 0;
            $existing_database_stats['scanned'] += !empty($records['robot_scanned']) ? $records['robot_scanned'] : 0;
        }
        $existing_database_value += $existing_database_stats['discovered'] * 1000;
        $existing_database_value += $existing_database_stats['encountered'] * 25;
        $existing_database_value += $existing_database_stats['scanned'] * 50;
        $existing_database_value += $existing_database_stats['summoned'] * 75;
        $existing_database_value += $existing_database_stats['defeated'] * 100;
    }
    error_log('$existing_database_stats: '.print_r($existing_database_stats, true));
    error_log('$existing_database_value: '.print_r($existing_database_value, true));

    // Loop through players (and their robots) to collect basic details for all of them
    $existing_robots = array();
    foreach ($mmrpg_index_players AS $ptoken => $pinfo){

        // Collect the current rewards and settings for this player
        $rewards = !empty($_SESSION[$session_token]['values']['battle_rewards'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_rewards'][$ptoken] : array();
        $settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]) ? $_SESSION[$session_token]['values']['battle_settings'][$ptoken] : array();

        // Loop through this player's robots one-by-one and merge rewards plus settings into a single array
        $unlocked_tokens = array();
        if (!empty($rewards['player_robots'])){ $unlocked_tokens = array_merge($unlocked_tokens, array_keys($rewards['player_robots'])); }
        if (!empty($settings['player_robots'])){ $unlocked_tokens = array_merge($unlocked_tokens, array_keys($settings['player_robots'])); }
        $unlocked_tokens = array_unique($unlocked_tokens);
        if (empty($unlocked_tokens)){ continue; }
        if (!empty($unlocked_tokens)){
            foreach ($unlocked_tokens AS $rtoken){
                $rinfo = array();
                if (isset($rewards['player_robots'][$rtoken])){ $rinfo = array_merge($rinfo, $rewards['player_robots'][$rtoken]); }
                if (isset($settings['player_robots'][$rtoken])){ $rinfo = array_merge($rinfo, $settings['player_robots'][$rtoken]); }
                $existing_robots[$rtoken] = $rinfo;
            }
        }

    }
    error_log('$existing_robots: '.print_r($existing_robots, true));

    // Generate a new database containing only basic details for unlocked robots
    $new_database = array();
    foreach ($existing_robots AS $rtoken => $rinfo){
        $record = array();
        $record['robot_token'] = $rtoken;
        $record['robot_unlocked'] = 1;
        $record['robot_encountered'] = 0;
        $record['robot_defeated'] = 0;
        $record['robot_summoned'] = 0;
        $record['robot_scanned'] = 0;
        /*
        if (!empty($existing_database[$rtoken])
            && !empty($existing_database[$rtoken]['robot_summoned'])){
            $record['robot_summoned'] = $existing_database[$rtoken]['robot_summoned'];
        }
        */
        $new_database[$rtoken] = $record;
    }
    error_log('$new_database: '.print_r($new_database, true));

    // Add the existing database value to the battle zenny counter
    $current_zenny = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
    $new_zenny = $current_zenny + $existing_database_value;
    $_SESSION[$session_token]['counters']['battle_zenny'] = $new_zenny;
    error_log('$current_zenny: '.number_format($current_zenny, 0, '.', ','));
    error_log('$new_zenny: '.number_format($new_zenny, 0, '.', ','));
    error_log(' => +'.number_format($new_zenny - $current_zenny, 0, '.', ','));

    // Reset the database records array to empty and clear the database
    $_SESSION[$session_token]['values']['robot_database'] = $new_database;
    $db->delete('mmrpg_users_robots_records', array('user_id' => $user_id));

}


// Update the appropriate session variables
$_SESSION[$session_token]['USER'] = $this_user;

// Load the save file into memory and overwrite the session
mmrpg_save_game_session();

//header('Location: prototype.php');
unset($db);
exit('success');



?>
