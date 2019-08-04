<?

// -- NEXT BATTLE ACTION -- //

// Pre-collect the redirect token (in case we need to change it)
if (!empty($this_battle->battle_complete_redirect_token)){ $battle_complete_redirect_token = $this_battle->battle_complete_redirect_token; }
$battle_complete_redirect_token = $this_battle->battle_token;

// If this is a STAR FIELD battle, break apart the next action
if (!empty($this_battle->flags['starfield_mission'])){

    // Break apart the action token into the requested star type, else return to home if we can't
    if (empty($this_action_token) || !strstr($this_action_token, '-star')){ $this_redirect = 'prototype.php?'.($flag_wap ? 'wap=true' : ''); return; }
    list($next_star_type) = explode('-', $this_action_token);

    //echo('$_GET = '.print_r($_GET, true).PHP_EOL);
    //echo('$this_action = '.print_r($this_action, true).PHP_EOL);
    //echo('$this_action_token = '.print_r($this_action_token, true).PHP_EOL);
    //echo('$next_star_type = '.print_r($next_star_type, true).PHP_EOL.PHP_EOL);

    // Collect a list of available stars still left for the player to encounter
    $temp_remaining_stars = mmrpg_prototype_remaining_stars(true);
    $temp_remaining_stars_types = array();
    if (!empty($temp_remaining_stars)){
        foreach ($temp_remaining_stars AS $token => $details){
            if (!empty($details['info1']['type'])){
                $type1 = $details['info1']['type'];
                if (!isset($temp_remaining_stars_types[$type1])){ $temp_remaining_stars_types[$type1] = array(); }
                $temp_remaining_stars_types[$type1][] = $token;
            }
            if (!empty($details['info2']['type'])){
                $type2 = $details['info2']['type'];
                if (!isset($temp_remaining_stars_types[$type2])){ $temp_remaining_stars_types[$type2] = array(); }
                $temp_remaining_stars_types[$type2][] = $token;
            }
        }
    }

    // Ensure there is a star for the requested type, else return to home if we can't
    if (empty($temp_remaining_stars_types[$next_star_type])){ $this_redirect = 'prototype.php?'.($flag_wap ? 'wap=true' : ''); return; }
    $next_star_options = $temp_remaining_stars_types[$next_star_type];
    $next_star_token = $next_star_options[mt_rand(0, (count($next_star_options) - 1))];
    $next_star_info = $temp_remaining_stars[$next_star_token];

    //echo('$next_star_token = '.print_r($next_star_token, true).PHP_EOL);
    //echo('$next_star_info = '.print_r($next_star_info, true).PHP_EOL.PHP_EOL);

    // Collect basic star variables necessary to generating next battle
    $info = $next_star_info['info1'];
    $info2 = $next_star_info['info2'];
    $next_star_level = $this_battle->battle_level + 1;
    if ($next_star_level > 100){ $next_star_level = 100; }

    // Generate the pseudo prototype data needed for generating next battle
    $this_prototype_data = array();
    $this_prototype_data['this_player_token'] = $this_player->player_token;
    $this_prototype_data['battle_phase'] = 2;
    $this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
    $this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];
    $this_prototype_data['battles_complete'] = mmrpg_prototype_battles_complete($this_prototype_data['this_player_token']);
    $this_prototype_data['this_current_chapter'] = '7';

    //echo('$info = '.print_r($info, true).PHP_EOL);
    //echo('$info2 = '.print_r($info2, true).PHP_EOL);
    //echo('$next_star_level = '.print_r($next_star_level, true).PHP_EOL);
    //echo('$this_prototype_data = '.print_r($this_prototype_data, true).PHP_EOL.PHP_EOL);

    // Include relevant dependent files like starforce and omega factors
    include(MMRPG_CONFIG_ROOTDIR.'prototype/omega.php');

    // Generate the actual battle given the provided star info, whether fusion or field variety
    if (!empty($info) && !empty($info2) && $info['field'] != $info2['field']){
        $temp_battle_omega = rpg_mission_double::generate($this_prototype_data, array($info['robot'], $info2['robot']), array($info['field'], $info2['field']), $next_star_level, true, false, true);
    } elseif (!empty($info)){
        $temp_battle_omega = rpg_mission_single::generate($this_prototype_data, $info['robot'], $info['field'], $next_star_level, true, false, true);
    }

    //echo('$temp_battle_token = '.print_r($temp_battle_omega['battle_token'], true).PHP_EOL.PHP_EOL);
    //echo('$temp_battle_omega = '.print_r($temp_battle_omega, true).PHP_EOL.PHP_EOL);

    // Update the chapter number and then save this data to the temp index
    $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
    rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

    // Update the redirect token to that of the new star field mission
    $battle_complete_redirect_token = $temp_battle_omega['battle_token'];

    //exit();

}

// Create the battle chain array if not exists
$is_first_mission = false;
if (!isset($_SESSION['BATTLES_CHAIN'])){ $_SESSION['BATTLES_CHAIN'] = array(); }
if (empty($_SESSION['BATTLES_CHAIN'])){ $is_first_mission = true; }
$this_chain_record = array(
    'battle_token' => $this_battle->battle_token,
    'battle_turns_used' => $this_battle->counters['battle_turn'],
    'battle_robots_used' => (!empty($this_player->counters['robots_start_total']) ? $this_player->counters['robots_start_total'] : 0),
    'battle_zenny_earned' => (!empty($this_battle->counters['final_zenny_reward']) ? $this_battle->counters['final_zenny_reward'] : 0)
    );
if ($is_first_mission){
    $this_team_config = $this_player->player_token.'::'.implode(',', $this_player->values['robots_start_team']);
    $this_chain_record['battle_team_config'] = $this_team_config;
}
$_SESSION['BATTLES_CHAIN'][] = $this_chain_record;

// Pre-generate active robots string and save any buffs/debuffs/etc.
$active_robot_array = array();
$active_robot_array_first = array();
$_SESSION['ROBOTS_PRELOAD'] = array();
$temp_player_active_robots = $this_player->values['robots_active'];
usort($temp_player_active_robots, function($r1, $r2){
    if ($r1['robot_position'] == 'active'){ return -1; }
    elseif ($r2['robot_position'] == 'active'){ return 1; }
    elseif ($r1['robot_key'] < $r2['robot_key']){ return -1; }
    elseif ($r1['robot_key'] > $r2['robot_key']){ return 1; }
    else { return 0; }
    });
foreach ($temp_player_active_robots AS $key => $robot){

    // Add this robot's ID + token to the list
    $robot_string = $robot['robot_id'].'_'.$robot['robot_token'];
    $active_robot_array[] = $robot_string;
    if (empty($active_robot_array_first)){
        $active_robot_array_first = array($robot['robot_id'], $robot['robot_token']);
    }

    // Recover Weapon Energy between battles, one if active two if bench (as if a turn had passed)
    $old_weapon_energy = $robot['robot_weapons'];
    $new_weapon_energy = $old_weapon_energy + ($robot['robot_position'] == 'active' ? 1 : 2);
    if ($new_weapon_energy > $robot['robot_base_weapons']){ $new_weapon_energy = $robot['robot_base_weapons']; }

    // Loop through attack/defense/speed mods and normalize them if not zero by one point where applicable
    $stat_mods = array('attack_mods', 'defense_mods', 'speed_mods');
    $new_mod_values = array();
    foreach ($stat_mods AS $mod_token){
        $new_mod_value = (int)($robot['counters'][$mod_token]);
        if ($new_mod_value > 0){ $new_mod_value -= 1; }
        elseif ($new_mod_value < 0){ $new_mod_value += 1; }
        $new_mod_values[$mod_token] = $new_mod_value;
    }

    // Reduce any durations by one if we have to so things don't persist forever
    $new_robot_attachments = $robot['robot_attachments'];
    foreach ($new_robot_attachments AS $key => $info){
        if (isset($info['attachment_duration']) && $info['attachment_duration'] > 0){
            $info['attachment_duration'] -= 1;
            $new_robot_attachments[$key] = $info;
            if ($info['attachment_duration'] <= 0){ unset($new_robot_attachments[$key]); }
        }
    }

    // Save this robot's current energy, weapons, attack/defense/speed mods, etc. to the session
    $_SESSION['ROBOTS_PRELOAD'][$battle_complete_redirect_token][$robot_string] = array(
        'robot_energy' => $robot['robot_energy'],
        'robot_weapons' => $new_weapon_energy,
        'robot_attack_mods' => $new_mod_values['attack_mods'],
        'robot_defense_mods' => $new_mod_values['defense_mods'],
        'robot_speed_mods' => $new_mod_values['speed_mods'],
        'robot_image' => $robot['robot_image'],
        'robot_item' => $robot['robot_item'],
        'robot_attachments' => $new_robot_attachments
        );

}
$active_robot_string = implode(',', $active_robot_array);

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();

// Generate the URL for the next mission with provided token
$next_battle_id = $this_battle->battle_id + 1;
$next_battle_token = $battle_complete_redirect_token;
$next_mission_href = 'battle.php?wap='.($flag_wap ? 'true' : 'false');
$next_mission_href .= '&this_battle_id='.$next_battle_id;
$next_mission_href .= '&this_battle_token='.$next_battle_token;
$next_mission_href .= '&this_player_id='.$this_player->player_id;
$next_mission_href .= '&this_player_token='.$this_player->player_token;
$next_mission_href .= '&this_player_robots='.$active_robot_string;
$next_mission_href .= '&flag_skip_fadein=true';

// If we're in the middle of an ENDLESS ATTACK MODE challene, regenerate the mission
if (!empty($this_battle->flags['challenge_battle'])
    && !empty($this_battle->flags['endless_battle'])){

    // Generate the first ENDLESS ATTACK MODE mission and append it to the list
    $this_mission_number = count($_SESSION['BATTLES_CHAIN']);
    $next_mission_number = $this_mission_number + 1;
    $this_prototype_data = array();
    $this_prototype_data['this_player_token'] = $this_player->player_token;
    $this_prototype_data['this_current_chapter'] = '8';
    $this_prototype_data['battle_phase'] = 4;
    $temp_battle_sigma = rpg_mission_endless::generate_endless_mission($this_prototype_data, $next_mission_number);
    rpg_battle::update_index_info($temp_battle_sigma['battle_token'], $temp_battle_sigma);

    // We should also save this data in the DB in case we need to restore later
    $save_state = array(
        'BATTLES_CHAIN' => $_SESSION['BATTLES_CHAIN'],
        'ROBOTS_PRELOAD' => $_SESSION['ROBOTS_PRELOAD'],
        'NEXT_MISSION' => array(
            'this_battle_id' => $next_battle_id,
            'this_battle_token' => $next_battle_token,
            'this_player_id' => $this_player->player_id,
            'this_player_token' => $this_player->player_token,
            'this_player_robots' => $active_robot_string
            )
        );
    $save_state_encoded = json_encode($save_state, JSON_HEX_QUOT);
    $save_state_encoded = str_replace('\\u0022', '\\\\"', $save_state_encoded);
    //$save_state_encoded = json_encode($save_state, JSON_HEX_QUOT | JSON_HEX_TAG);
    //$save_state_encoded = serialize($save_state);
    $update_array = array('challenge_wave_savestate' => $save_state_encoded);
    $db->update('mmrpg_challenges_waveboard', $update_array, array('user_id' => $this_user_id));

    // And just-in-case the user closes the window, let's save the actual game now too
    mmrpg_save_game_session();

}

// If this is a STAR FIELD battle, break apart the next action
if (!empty($this_battle->flags['starfield_mission'])){

    // And just-in-case the user closes the window, let's save the actual game now too
    mmrpg_save_game_session();

}

// Redirect the user back to the next mission
$this_redirect = $next_mission_href;

/*

// Generate the URL for the next mission with provided token
$next_mission_href = 'battle_loop.php?wap='.($flag_wap ? 'true' : 'false');
$next_mission_href .= '&this_battle_id='.($this_battle->battle_id + 1);
$next_mission_href .= '&this_battle_token='.$battle_complete_redirect_token;
$next_mission_href .= '&this_field_id='.$this_field_id;
$next_mission_href .= '&this_field_token='.$this_field_token;
$next_mission_href .= '&this_user_id='.$this_user_id;
$next_mission_href .= '&this_player_id='.$this_player->player_id;
$next_mission_href .= '&this_player_token='.$this_player->player_token;
$next_mission_href .= '&this_player_robots='.$active_robot_string;
$next_mission_href .= '&this_robot_id='.$active_robot_array_first[0];
$next_mission_href .= '&this_robot_token='.$active_robot_array_first[1];
$next_mission_href .= '&target_user_id='.$target_user_id;
$next_mission_href .= '&target_player_id='.$target_player_id;
$next_mission_href .= '&target_player_token='.$target_player_token;
$next_mission_href .= '&target_robot_id=auto';
$next_mission_href .= '&target_robot_token=auto';
$next_mission_href .= '&this_action=start';
$next_mission_href .= '&target_action=start';

// Redirect to a new battle loop with new targets
header('Location: '.$next_mission_href);
exit();

*/

?>