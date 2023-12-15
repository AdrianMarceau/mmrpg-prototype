<?php

// ROBOT ACTIONS : SORT ROBOTS

// Include the necessary database files
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
//require(MMRPG_CONFIG_ROOTDIR.'database/robots.php');
$mmrpg_database_robots = rpg_robot::get_index(true);

//
// Collect the ability variables from the request header, if they exist
$temp_token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
$temp_order = !empty($_REQUEST['order']) ? $_REQUEST['order'] : '';
$temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
$temp_robots = !empty($_REQUEST['robots']) ? $_REQUEST['robots'] : '';
$temp_token_order = $temp_token.'_'.$temp_order;
// If key variables are not provided, kill the script in error
if (empty($temp_token) || empty($temp_order) || empty($temp_player)){
    die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true)));
}

// Check for any robots that are locked in the endless attack or otherwise
$player_robots_locked = array();
$endless_attack_savedata = mmrpg_prototype_get_endless_sessions($temp_player);
//error_log('$endless_attack_savedata for '.$temp_player.': '.print_r(array_keys($endless_attack_savedata), true));
if (!empty($endless_attack_savedata)
    && !empty($endless_attack_savedata['robots'])){
    $endless_robot_robots = $endless_attack_savedata['robots'];
    $player_robots_locked = array_merge($player_robots_locked, $endless_robot_robots);
    $player_robots_locked = array_unique($player_robots_locked);
}

// Ensure this player's robots exist in the current game session
if (!empty($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'])
    && !empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'])){

        // Make a copy of the player robots array
        $temp_player_robots = $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'];
        $temp_player_robots_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'];
        if (!empty($temp_player_robots)){
            foreach ($temp_player_robots AS $token => $info){
                // Update the current and session arrays to make absolutely sure the robot token is in the right place
                if (empty($info['robot_token'])){ $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$token]['robot_token'] = $temp_player_robots[$token]['robot_token'] = $token; }
                $temp_player_robots[$token]['robot_core'] = !empty($mmrpg_database_robots[$token]['robot_core']) ? $mmrpg_database_robots[$token]['robot_core'] : '';
            }
        }

        // Pre-collect the copy-style persona info for we can compare
        $copy_style_personas = array();
        if (!empty($temp_player_robots)){
            foreach ($temp_player_robots AS $token => $info){
                if (empty($info['robot_persona'])){ continue; }
                if (empty($info['robot_abilities']['copy-style'])){ continue; }
                if (empty($mmrpg_database_robots[$info['robot_persona']])){ continue; }
                $persona_info = $mmrpg_database_robots[$info['robot_persona']];
                $copy_style_personas[$token] = array(
                    'robot_token' => $persona_info['robot_token'],
                    'robot_core' => $persona_info['robot_core'],
                    'robot_core2' => $persona_info['robot_core2'],
                    );
            }
        }
        //error_log('$copy_style_personas = '.print_r($copy_style_personas, true));

        // Define a temporarily function for sorting the robots
        $mmrpg_database_robots_keys = array_keys($mmrpg_database_robots);
        $mmrpg_database_types_keys = array_keys($mmrpg_database_types);
        $mmrpg_database_types_keys = array_unique(array_merge(array('copy', 'none'), $mmrpg_database_types_keys));

        // If the sort token was by number and asc
        if ($temp_token_order == 'number_asc'){
            // Define the sort function that uses these keys
            function temp_player_robots_sort($r1, $r2){
                global $mmrpg_database_robots_keys;
                if (empty($r1) || empty($r2)){ return 0; }
                $robot1_number_position = array_search($r1['robot_token'], $mmrpg_database_robots_keys);
                $robot2_number_position = array_search($r2['robot_token'], $mmrpg_database_robots_keys);
                if ($robot1_number_position === false && $robot2_number_position !== false){ return -1; }
                elseif ($robot1_number_position !== false && $robot2_number_position === false){ return 1; }
                elseif ($robot1_number_position < $robot2_number_position){ return -1; }
                elseif ($robot1_number_position > $robot2_number_position){ return 1; }
                else { return 0; }
            }
        }
        // Else if the sort token was by number and desc
        elseif ($temp_token_order == 'number_desc'){
            // Define the sort function that uses these keys
            function temp_player_robots_sort($r1, $r2){
                global $mmrpg_database_robots_keys;
                if (empty($r1) || empty($r2)){ return 0; }
                $robot1_number_position = array_search($r1['robot_token'], $mmrpg_database_robots_keys);
                $robot2_number_position = array_search($r2['robot_token'], $mmrpg_database_robots_keys);
                if ($robot1_number_position === false && $robot2_number_position !== false){ return 1; }
                elseif ($robot1_number_position !== false && $robot2_number_position === false){ return -1; }
                elseif ($robot1_number_position < $robot2_number_position){ return 1; }
                elseif ($robot1_number_position > $robot2_number_position){ return -1; }
                else { return 0; }
            }
        }
        // Else if the sort token was by level and asc
        elseif ($temp_token_order == 'level_asc'){
            // Define the sort function that uses these keys
            function temp_player_robots_sort($r1, $r2){
                global $mmrpg_database_robots_keys, $temp_player_robots_rewards;
                if (empty($r1) || empty($r2)){ return 0; }
                $robot1_number_position = array_search($r1['robot_token'], $mmrpg_database_robots_keys);
                $robot2_number_position = array_search($r2['robot_token'], $mmrpg_database_robots_keys);
                $r1['robot_level'] = $temp_player_robots_rewards[$r1['robot_token']]['robot_level'];
                $r2['robot_level'] = $temp_player_robots_rewards[$r2['robot_token']]['robot_level'];
                if ($robot1_number_position === false && $robot2_number_position !== false){ return -1; }
                elseif ($robot1_number_position !== false && $robot2_number_position === false){ return 1; }
                elseif ($r1['robot_level'] < $r2['robot_level']){ return -1; }
                elseif ($r1['robot_level'] > $r2['robot_level']){ return 1; }
                elseif ($robot1_number_position < $robot2_number_position){ return -1; }
                elseif ($robot1_number_position > $robot2_number_position){ return 1; }
                else { return 0; }
            }
        }
        // Else if the sort token was by level and desc
        elseif ($temp_token_order == 'level_desc'){
            // Define the sort function that uses these keys
            function temp_player_robots_sort($r1, $r2){
                global $mmrpg_database_robots_keys, $temp_player_robots_rewards;
                if (empty($r1) || empty($r2)){ return 0; }
                $robot1_number_position = array_search($r1['robot_token'], $mmrpg_database_robots_keys);
                $robot2_number_position = array_search($r2['robot_token'], $mmrpg_database_robots_keys);
                $r1['robot_level'] = $temp_player_robots_rewards[$r1['robot_token']]['robot_level'];
                $r2['robot_level'] = $temp_player_robots_rewards[$r2['robot_token']]['robot_level'];
                if ($robot1_number_position === false && $robot2_number_position !== false){ return 1; }
                elseif ($robot1_number_position !== false && $robot2_number_position === false){ return -1; }
                elseif ($r1['robot_level'] < $r2['robot_level']){ return 1; }
                elseif ($r1['robot_level'] > $r2['robot_level']){ return -1; }
                elseif ($robot1_number_position < $robot2_number_position){ return 1; }
                elseif ($robot1_number_position > $robot2_number_position){ return -1; }
                else { return 0; }
            }
        }
        // If the sort token was by core and asc
        elseif ($temp_token_order == 'core_asc'){
            //die($temp_token_order.' <pre>'.print_r(implode(',', $mmrpg_database_types_keys), true).'</pre>');
            // Define the sort function that uses these keys
            function temp_player_robots_sort($r1, $r2){
                global $temp_token_order, $mmrpg_database_types_keys, $mmrpg_database_robots_keys;
                if (empty($r1) || empty($r2)){ return 0; }
                global $copy_style_personas;
                if (!empty($copy_style_personas[$r1['robot_token']])){ $r1 = $copy_style_personas[$r1['robot_token']]; }
                if (!empty($copy_style_personas[$r2['robot_token']])){ $r2 = $copy_style_personas[$r2['robot_token']]; }
                $robot1_token = !empty($r1['robot_token']) ? $r1['robot_token'] : 'robot';
                $robot2_token = !empty($r2['robot_token']) ? $r2['robot_token'] : 'robot';
                $robot1_core = !empty($r1['robot_core']) ? $r1['robot_core'] : 'none';
                $robot2_core = !empty($r2['robot_core']) ? $r2['robot_core'] : 'none';
                $robot1_core_position = array_search($robot1_core, $mmrpg_database_types_keys);
                $robot2_core_position = array_search($robot2_core, $mmrpg_database_types_keys);
                $robot1_number_position = array_search($robot1_token, $mmrpg_database_robots_keys);
                $robot2_number_position = array_search($robot2_token, $mmrpg_database_robots_keys);
                //die($temp_token_order.' ('.$robot1_token.' vs '.$robot2_token.') ('.$robot1_core.' vs '.$robot2_core.') <pre>'.print_r(implode(',', $mmrpg_database_types_keys), true).'</pre>');
                if ($robot1_core_position === false && $robot2_core_position !== false){ return -1; }
                elseif ($robot1_core_position !== false && $robot2_core_position === false){ return 1; }
                elseif ($robot1_core_position < $robot2_core_position){ return -1; }
                elseif ($robot1_core_position > $robot2_core_position){ return 1; }
                elseif ($robot1_number_position === false && $robot2_number_position !== false){ return -1; }
                elseif ($robot1_number_position !== false && $robot2_number_position === false){ return 1; }
                elseif ($robot1_number_position < $robot2_number_position){ return -1; }
                elseif ($robot1_number_position > $robot2_number_position){ return 1; }
                else { return 0; }
            }
        }
        // Else if the sort token was by core and desc
        elseif ($temp_token_order == 'core_desc'){
            //die($temp_token_order.' <pre>'.print_r(implode(',', $mmrpg_database_types_keys), true).'</pre>');
            // Define the sort function that uses these keys
            function temp_player_robots_sort($r1, $r2){
                global $temp_token_order, $mmrpg_database_types_keys, $mmrpg_database_robots_keys;
                if (empty($r1) || empty($r2)){ return 0; }
                global $copy_style_personas;
                if (!empty($copy_style_personas[$r1['robot_token']])){ $r1 = $copy_style_personas[$r1['robot_token']]; }
                if (!empty($copy_style_personas[$r2['robot_token']])){ $r2 = $copy_style_personas[$r2['robot_token']]; }
                $robot1_token = !empty($r1['robot_token']) ? $r1['robot_token'] : 'robot';
                $robot2_token = !empty($r2['robot_token']) ? $r2['robot_token'] : 'robot';
                $robot1_core = !empty($r1['robot_core']) ? $r1['robot_core'] : 'none';
                $robot2_core = !empty($r2['robot_core']) ? $r2['robot_core'] : 'none';
                $robot1_core_position = array_search($robot1_core, $mmrpg_database_types_keys);
                $robot2_core_position = array_search($robot2_core, $mmrpg_database_types_keys);
                $robot1_number_position = array_search($robot1_token, $mmrpg_database_robots_keys);
                $robot2_number_position = array_search($robot2_token, $mmrpg_database_robots_keys);
                //die($temp_token_order.' ('.$robot1_token.' vs '.$robot2_token.') ('.$robot1_core.' vs '.$robot2_core.') <pre>'.print_r(implode(',', $mmrpg_database_types_keys), true).'</pre>');
                if ($robot1_core_position === false && $robot2_core_position !== false){ return 1; }
                elseif ($robot1_core_position !== false && $robot2_core_position === false){ return -1; }
                elseif ($robot1_core_position < $robot2_core_position){ return 1; }
                elseif ($robot1_core_position > $robot2_core_position){ return -1; }
                elseif ($robot1_number_position === false && $robot2_number_position !== false){ return 1; }
                elseif ($robot1_number_position !== false && $robot2_number_position === false){ return -1; }
                elseif ($robot1_number_position < $robot2_number_position){ return 1; }
                elseif ($robot1_number_position > $robot2_number_position){ return -1; }
                else { return 0; }
            }
        }
        // Else if the sort token was a manual drag
        elseif ($temp_token_order == 'manual_auto'){

            // Explode the provided robot keys, assuming there were...
            $mmrpg_manual_robots_keys = !empty($temp_robots) ? explode(',', $temp_robots) : array();

            // Define the sort function that uses these keys
            function temp_player_robots_sort($r1, $r2){
                global $mmrpg_manual_robots_keys, $mmrpg_database_robots_keys;
                if (empty($r1) || empty($r2)){ return 0; }

                $robot1_token = !empty($r1['robot_token']) ? $r1['robot_token'] : 'robot';
                $robot2_token = !empty($r2['robot_token']) ? $r2['robot_token'] : 'robot';
                $robot1_manual_position = array_search($robot1_token, $mmrpg_manual_robots_keys);
                $robot2_manual_position = array_search($robot2_token, $mmrpg_manual_robots_keys);
                $robot1_number_position = array_search($robot1_token, $mmrpg_database_robots_keys);
                $robot2_number_position = array_search($robot2_token, $mmrpg_database_robots_keys);

                if ($robot1_manual_position === false && $robot2_manual_position !== false){ return -1; }
                elseif ($robot1_manual_position !== false && $robot2_manual_position === false){ return 1; }
                elseif ($robot1_number_position === false && $robot2_number_position !== false){ return -1; }
                elseif ($robot1_number_position !== false && $robot2_number_position === false){ return 1; }
                elseif ($robot1_manual_position < $robot2_manual_position){ return -1; }
                elseif ($robot1_manual_position > $robot2_manual_position){ return 1; }
                elseif ($robot1_number_position < $robot2_number_position){ return -1; }
                elseif ($robot1_number_position > $robot2_number_position){ return 1; }
                else { return 0; }

            }
        }



        // Sort the robots and maintain index association
        uasort($temp_player_robots, 'temp_player_robots_sort');

        // If there are any locked robots, make sure we anchor them to the bottom without hurting the existing order
        if (!empty($player_robots_locked)){
            $temp_locked_robots = array();
            $temp_unlocked_robots = array();
            foreach ($temp_player_robots AS $token => $info){
                if (in_array($token, $player_robots_locked)){ $temp_locked_robots[$token] = $info; }
                else { $temp_unlocked_robots[$token] = $info; }
            }
            $temp_player_robots = array_merge($temp_unlocked_robots, $temp_locked_robots);
        }

        // Ensure nothing went wrong with the array before copying
        if (!empty($temp_player_robots)){
            $temp_robot_tokens = implode(',', array_keys($temp_player_robots));
            $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'] = $temp_player_robots;
            exit('success|array-sorted|'.$temp_robot_tokens);
        }
        // Otherwise produce an error
        else {
            // Produce the error message
            exit('error|array-corrupted|false');
        }


    }
// Otherwise, produce an error
else {

    // Produce the error message
    exit('error|robots-undefined|false');

}

?>