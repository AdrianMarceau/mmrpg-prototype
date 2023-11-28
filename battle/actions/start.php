<?

// -- START BATTLE ACTION -- //

// Define the battle's turn counter and start at 0
$this_battle->counters['battle_turn'] = 0;
$this_battle->update_session();

// Check if there are any field hazards to place beneath this robot
if (!empty($this_battle->battle_field_base['values']['hazards'])){

    // Loop through field hazards and create any battle attachments
    $player_sides = array('left', 'right');
    $player_bench_sizes = array('left' => $this_player->counters['robots_total'], 'right' => $target_player->counters['robots_total']);
    $max_bench_size = max($this_player->counters['robots_total'], $target_player->counters['robots_total']);
    foreach ($this_battle->battle_field_base['values']['hazards'] AS $hazard_token => $hazard_value){
        if (empty($hazard_value)){ continue; }
        $method_name = 'get_static_'.$hazard_token;
        if (!method_exists('rpg_ability', $method_name)){ $method_name = preg_replace('/ies$/i', 'y', $method_name); }
        if (!method_exists('rpg_ability', $method_name)){ $method_name = preg_replace('/s$/i', '', $method_name); }
        if (!method_exists('rpg_ability', $method_name)){ continue; }
        foreach ($player_sides AS $player_side){
            if ($hazard_value != 'both' // not [both]
                && $hazard_value != 'both-active' // not [both-active]
                && $hazard_value != 'both-bench' // not [both-bench]
                && $hazard_value != $player_side // not [left/right]
                && $hazard_value != $player_side.'-active' // not [left/right]-[active]
                && $hazard_value != $player_side.'-bench' // not [left/right]-[bench]
                ){ continue; } // doesn't match any criteria, so skip this player side
            $bench_size = $player_bench_sizes[$player_side];
            $hazards_added = 0;
            $hazards_to_add = $bench_size == 1 ? 1 : $bench_size;
            if ($hazard_value == $player_side.'-bench'){ $hazards_to_add -= 1; }
            for ($key = -1; $key < $max_bench_size; $key++){
                if ($key == 0){ continue; }
                elseif ($key == -1 && $hazard_value == ($player_side.'-bench')){ continue; } // skip if active but bench-only
                elseif ($key != -1 && $hazard_value == ($player_side.'-active')){ continue; } // skip of bench but active-only    // Add this check for both-active and both-bench
                if ($hazard_value == 'both-active' && $key != -1) { continue; }  // skip bench
                if ($hazard_value == 'both-bench' && $key == -1) { continue; }  // skip active
                if ($hazards_added >= $hazards_to_add){ break; }
                if ($key == -1){  $static_key = $player_side.'-active';  }
                else { $static_key = $player_side.'-bench-'.$key; }
                $existing = !empty($this_battle->battle_attachments[$static_key]) ? count($this_battle->battle_attachments[$static_key]) : 0;
                $hazard_info = call_user_func('rpg_ability::'.$method_name, $static_key, 99, $existing);
                $this_battle->battle_attachments[$static_key][$hazard_info['attachment_token']] = $hazard_info;
                $hazards_added++;
            }
        }
    }

    // Update the session with any changes
    $this_battle->update_session();

}

// Check for any robot details preloaded into session from a prev mission
$allow_chain_bonuses = false;
$this_battle_chain = !empty($_SESSION['BATTLES_CHAIN'][$this_battle->battle_chain_token]) ? $_SESSION['BATTLES_CHAIN'][$this_battle->battle_chain_token] : array();
if (!empty($this_battle->flags['starfield_mission'])){ $allow_chain_bonuses = true; }
if (!empty($this_battle->flags['challenge_battle']) && !empty($this_battle->flags['endless_battle'])){ $allow_chain_bonuses = true; }
if ($allow_chain_bonuses && !empty($this_battle_chain)){
    // Increase the zenny reward based on the current wave
    $base_zenny = $this_battle->battle_zenny;
    $current_chain = count($this_battle_chain) + 1;
    $this_battle->battle_zenny = $current_chain * $base_zenny;
} elseif (!$allow_chain_bonuses){
    $this_battle_chain = array();
}
$_SESSION['BATTLES_CHAIN'][$this_battle->battle_chain_token] = $this_battle_chain;

// Check for any robot details preloaded into session from a prev mission
//error_log('loading ROBOTS_PRELOAD: '.print_r($_SESSION['ROBOTS_PRELOAD'], true));
if (!empty($_SESSION['ROBOTS_PRELOAD'][$this_battle->battle_token])){
    //$this_battle->events_create(false, false, 'debug', ('$_SESSION[\'ROBOTS_PRELOAD\'] = '.preg_replace('/\s+/', ' ', print_r($_SESSION['ROBOTS_PRELOAD'], true)).'<br />'));

    // Loop through this player's robots and apply any save states from the session
    foreach ($this_player->values['robots_active'] AS $key => $info){
        if ($this_robot->robot_id == $info['robot_id']){ $temp_robot = $this_robot; }
        else { $temp_robot = rpg_game::get_robot($this_battle, $this_player, $info); }
        $temp_robot_string = $temp_robot->robot_id.'_'.$temp_robot->robot_token;
        if (!empty($_SESSION['ROBOTS_PRELOAD'][$this_battle->battle_token][$temp_robot_string])){
            $temp_preload_data = $_SESSION['ROBOTS_PRELOAD'][$this_battle->battle_token][$temp_robot_string];
            //$this_battle->events_create(false, false, 'debug', ('$_SESSION[\'ROBOTS_PRELOAD\']('.$temp_robot_string.') = '.preg_replace('/\s+/', ' ', print_r($temp_preload_data, true)).'<br />'));
            if (isset($temp_preload_data['robot_energy'])){ $temp_robot->robot_energy = $temp_preload_data['robot_energy']; }
            if (isset($temp_preload_data['robot_weapons'])){ $temp_robot->robot_weapons = $temp_preload_data['robot_weapons']; }
            if (isset($temp_preload_data['robot_attack_mods'])){ $temp_robot->counters['attack_mods'] = $temp_preload_data['robot_attack_mods']; }
            if (isset($temp_preload_data['robot_defense_mods'])){ $temp_robot->counters['defense_mods'] = $temp_preload_data['robot_defense_mods']; }
            if (isset($temp_preload_data['robot_speed_mods'])){ $temp_robot->counters['speed_mods'] = $temp_preload_data['robot_speed_mods']; }
            if (isset($temp_preload_data['robot_item'])){ $temp_robot->robot_item = $temp_preload_data['robot_item']; }
            if (isset($temp_preload_data['robot_abilities'])){ $temp_robot->robot_abilities = array_merge($temp_preload_data['robot_abilities'], $temp_robot->robot_abilities); }
            if (isset($temp_preload_data['robot_attachments'])){ $temp_robot->robot_attachments = array_merge($temp_preload_data['robot_attachments'], $temp_robot->robot_attachments); }
            if (isset($temp_preload_data['robot_persona'])){ $temp_robot->robot_persona = $temp_preload_data['robot_persona']; }
            if (isset($temp_preload_data['robot_persona_image'])){ $temp_robot->robot_persona_image = $temp_preload_data['robot_persona_image']; }
            if (isset($temp_preload_data['robot_support'])){ $temp_robot->robot_support = $temp_preload_data['robot_support']; }
            if (isset($temp_preload_data['robot_support_image'])){ $temp_robot->robot_support_image = $temp_preload_data['robot_support_image']; }
            if (isset($temp_preload_data['robot_image'])){ $temp_robot->robot_image = $temp_preload_data['robot_image']; }
            $temp_robot->update_session();
        }
    }

}

// Check if this is a player battle
$flag_player_battle = $target_player_id != MMRPG_SETTINGS_TARGET_PLAYERID ? true : false;

// Check if this battles records should be saved
$flag_battle_counts = $this_battle->battle_counts ? true : false;

// Define the first event body markup, regardless of player type
$first_event_header = $this_battle->battle_name.' <span style="opacity:0.25;">|</span> '.$this_battle->battle_field->field_name;
$first_event_body = $this_battle->battle_description.'<br />';
if (!empty($this_battle->flags['player_battle'])){
    $first_event_body = str_replace(
        'Defeat '.$target_player->player_name,
        'Defeat '.$target_player->print_name(true),
        $first_event_body
        );
}

// If this is an ENDLESS ATTACK MODE mission, display the counter
if (!empty($this_battle->flags['challenge_battle'])
    && !empty($this_battle->flags['endless_battle'])){

    // Generate the first ENDLESS ATTACK MODE mission and append it to the list
    $this_loop_size = 18;
    $this_mission_number = count($this_battle_chain) + 1;
    $this_phase_number = $this_mission_number > $this_loop_size ? ceil($this_mission_number / $this_loop_size) : 1;
    $this_battle_number = $this_mission_number > $this_loop_size ? ($this_mission_number % $this_loop_size) : $this_mission_number;
    //$first_event_body .= 'Wave : '.number_format($this_mission_number, 0, '.', ',').' ';
    //$first_event_body .= '('.number_format($this_phase_number, 0, '.', ',').'-'.number_format($this_battle_number, 0, '.', ',').') ';
    //$first_event_body .= '<span style="opacity:0.25;">|</span> ';
    $first_event_body .= 'Wave : '.number_format($this_mission_number, 0, '.', ',').' ';
    $first_event_body .= '<span style="opacity:0.25;">|</span> ';
    $first_event_body .= 'Phase : '.number_format($this_phase_number, 0, '.', ',').'-'.number_format($this_battle_number, 0, '.', ',').' ';
    $first_event_body .= '<span style="opacity:0.25;">|</span> ';
    //$first_event_body .= 'Phase : '.number_format($this_phase_number, 0, '.', ',').' ';
    //$first_event_body .= '<span style="opacity:0.25;">|</span> ';
    $first_event_body = preg_replace('/(^|\s)Select\s([^\.]+)\sfight\s/i', 'Fight ', $first_event_body);

}

// Print out the goals for this mission
$first_event_body .= 'Goal : '.$this_battle->battle_turns.($this_battle->battle_turns > 1 ? ' Turns' : ' Turn').' ';

// Print out the rewards for this mission
$first_event_body .= '<span style="opacity:0.25;">|</span> ';
$first_event_body .= 'Reward : '.number_format($this_battle->battle_zenny, 0, '.', ',').' Zenny ';

$first_event_body .= '<br />';

//$first_event_body .= '$this_battle->values = '.preg_replace('/\s+/', ' ', print_r($this_battle->values, true)).'<br />';
//$first_event_body .= '$this_battle->flags = '.preg_replace('/\s+/', ' ', print_r($this_battle->flags, true)).'<br />';
//$first_event_body .= '$this_battle->battle_field_base = '.preg_replace('/\s+/', ' ', print_r($this_battle->battle_field_base, true)).'<br />';
//$first_event_body .= '$this->counters[\'robots_perside_max\'] = '.preg_replace('/\s+/', ' ', print_r($this_battle->counters['robots_perside_max'], true)).'<br />';

//$this_battle->events_create(false, false, 'debug', ('$_SESSION[\'ROBOTS_PRELOAD\'] = '.preg_replace('/\s+/', ' ', print_r($_SESSION['ROBOTS_PRELOAD'], true)).'<br />'));
//$this_battle->events_create(false, false, 'debug', ('$this_robot = '.preg_replace('/\s+/', ' ', print_r($this_robot->export_array(), true)).'<br />'));

// Update the summon counts for all this player's robots
foreach ($this_player->values['robots_active'] AS $key => $info){
    $token = $info['robot_pseudo_token'];
    //error_log('this robot_token = '.$info['robot_token'].' vs $token = '.$token);
    if (!isset($_SESSION['GAME']['values']['robot_database'][$token])){ $_SESSION['GAME']['values']['robot_database'][$token] = array('robot_token' => $token); }
    if (!isset($_SESSION['GAME']['values']['robot_database'][$token]['robot_summoned'])){ $_SESSION['GAME']['values']['robot_database'][$token]['robot_summoned'] = 0; }
    $_SESSION['GAME']['values']['robot_database'][$token]['robot_summoned'] += 1;
}

// Update the encounter counts for all target player's robots
foreach ($target_player->values['robots_active'] AS $key => $info){
    $token = $info['robot_pseudo_token'];
    //error_log('target robot_token = '.$info['robot_token'].' vs $token = '.$token);
    if (!isset($_SESSION['GAME']['values']['robot_database'][$token])){ $_SESSION['GAME']['values']['robot_database'][$token] = array('robot_token' => $token); }
    if (!isset($_SESSION['GAME']['values']['robot_database'][$token]['robot_encountered'])){ $_SESSION['GAME']['values']['robot_database'][$token]['robot_encountered'] = 0; }
    $_SESSION['GAME']['values']['robot_database'][$token]['robot_encountered'] += 1;
}

// Hide all this player's robots by default
foreach ($this_player->values['robots_active'] AS $key => $info){
    if ($this_robot->robot_id == $info['robot_id']){
        $this_robot->robot_frame_styles = 'display: none; ';
        $this_robot->robot_detail_styles = 'display: none; ';
        $this_robot->update_session();
    } else {
        $temp_robot = rpg_game::get_robot($this_battle, $this_player, $info);
        $temp_robot->robot_frame_styles = 'display: none; ';
        $temp_robot->robot_detail_styles = 'display: none; ';
        $temp_robot->update_session();
    }
}

// Hide all the target player's robots by default
foreach ($target_player->values['robots_active'] AS $key => $info){
    if ($target_robot->robot_id == $info['robot_id']){
        $target_robot->robot_frame_styles = 'display: none; ';
        $target_robot->robot_detail_styles = 'display: none; ';
        $target_robot->update_session();
    } else {
        $temp_robot = rpg_game::get_robot($this_battle, $target_player, $info);
        $temp_robot->robot_frame_styles = 'display: none; ';
        $temp_robot->robot_detail_styles = 'display: none; ';
        $temp_robot->update_session();
    }
}

// Create the battle start event, showing the zenny and amount of turns
$event_header = $first_event_header;
$event_body = $first_event_body;
$event_options = array();
$event_options['this_header_float'] = $event_options['this_body_float'] = 'center';
$event_options['event_flag_is_special'] = true;
$event_options['canvas_show_this'] = $event_options['console_show_this'] = false;
$event_options['canvas_show_this_robots'] = false;
$event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
$event_options['canvas_show_target_robots'] = false;
if ($target_player->player_token == 'player'){
    // If there is no target player, have the target robots camera focused
    $event_options['event_flag_camera_action'] = true;
    $event_options['event_flag_camera_side'] = $target_player->player_side;
    $event_options['event_flag_camera_focus'] = 'active';
    $event_options['event_flag_camera_depth'] = 0;
}
$this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
//$this_battle->events_create(false, false, '', '');

// Require the common battle-start action file
require(MMRPG_CONFIG_ROOTDIR.'battle/actions/action_battlestart.php');

// Queue up an the target robot's startup action
$this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, 'start', '');
// Execute the battle actions
$this_battle->actions_execute();

// Queue up this robot's startup action first
$this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, 'start', '');
// Execute the battle actions
$this_battle->actions_execute();

// Execute the battle actions again just-in-case
$this_battle->actions_execute();

// Change all this player's robot sprite back to their base sprites in case something happened
foreach ($this_player->values['robots_active'] AS $key => $info){
    if ($this_robot->robot_id == $info['robot_id']){
        $this_robot->set_frame('base');
    } else {
        $temp_robot = rpg_game::get_robot($this_battle, $this_player, $info);
        $temp_robot->set_frame('base');
    }
}

// Require the common battle-start action file
//require(MMRPG_CONFIG_ROOTDIR.'battle/actions/action_battlestart.php');

// Create a final frame before giving control to the user
$sfx_delay = 600;
if (!empty($_SESSION['GAME']['battle_settings']['eventTimeout'])){
    $event_timeout = $_SESSION['GAME']['battle_settings']['eventTimeout'];
    $sfx_delay = $event_timeout;
}
$event_options = array();
$this_battle->queue_sound_effect(
    array('name' => 'battle-start-sound', 'delay' => $sfx_delay)
    );
$this_battle->events_create(false, false, '', '', $event_options);

?>