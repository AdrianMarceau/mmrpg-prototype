<?

// -- START BATTLE ACTION -- //

// Define the battle's turn counter and start at 0
$this_battle->counters['battle_turn'] = 0;
$this_battle->update_session();

// Check if this is a player battle
$flag_player_battle = $target_player_id != MMRPG_SETTINGS_TARGET_PLAYERID ? true : false;

// Check if this battle's points count
$flag_battle_counts = $this_battle->battle_counts ? true : false;

// Define the first event body markup, regardless of player type
$first_event_header = $this_battle->battle_name.' <span style="opacity:0.25;">|</span> '.$this_battle->battle_field->field_name;
$first_event_body = $this_battle->battle_description.'<br />';

// Print out the goals for this mission
$first_event_body .= 'Goal : '.$this_battle->battle_turns.($this_battle->battle_turns > 1 ? ' Turns' : ' Turn').' ';
$first_event_body .= '<span style="opacity:0.25;">|</span> ';

// Print out the rewards for this mission
if ($flag_battle_counts){
    $first_event_body .= 'Reward : '.($this_battle->battle_points == 1 ? '1 Battle Points' :  number_format($this_battle->battle_points, 0, '.', ',').' Points').' ';
} else {
    $first_event_body .= 'Reward : '.number_format($this_battle->battle_points, 0, '.', ',').' Zenny ';
}

$first_event_body .= '<br />';

//$first_event_body .= '$this_battle->flags = '.preg_replace('/\s+/', ' ', print_r($this_battle->flags, true)).'<br />';

// Update the summon counts for all this player's robots
foreach ($this_player->values['robots_active'] AS $key => $info){
    if (!isset($_SESSION['GAME']['values']['robot_database'][$info['robot_token']])){ $_SESSION['GAME']['values']['robot_database'][$info['robot_token']] = array('robot_token' => $info['robot_token']); }
    if (!isset($_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_summoned'])){ $_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_summoned'] = 0; }
    $_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_summoned'] += 1;
}

// Update the encounter counts for all target player's robots
foreach ($target_player->values['robots_active'] AS $key => $info){
    if (!isset($_SESSION['GAME']['values']['robot_database'][$info['robot_token']])){ $_SESSION['GAME']['values']['robot_database'][$info['robot_token']] = array('robot_token' => $info['robot_token']); }
    if (!isset($_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_encountered'])){ $_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_encountered'] = 0; }
    $_SESSION['GAME']['values']['robot_database'][$info['robot_token']]['robot_encountered'] += 1;
}

// Hide all this player's robots by default
foreach ($this_player->values['robots_active'] AS $key => $info){
    if ($this_robot->robot_id == $info['robot_id']){
        $this_robot->set_frame_styles('display: none; ');
        $this_robot->set_detail_styles('display: none; ');
    } else {
        $temp_robot = rpg_game::get_robot($this_battle, $this_player, $info);
        $temp_robot->set_frame_styles('display: none; ');
        $temp_robot->set_detail_styles('display: none; ');
    }
}

// Hide all the target player's robots by default
foreach ($target_player->values['robots_active'] AS $key => $info){
    if ($target_robot->robot_id == $info['robot_id']){
        $target_robot->set_frame_styles('display: none; ');
        $target_robot->set_detail_styles('display: none; ');
    } else {
        $temp_robot = rpg_game::get_robot($this_battle, $target_player, $info);
        $temp_robot->set_frame_styles('display: none; ');
        $temp_robot->set_detail_styles('display: none; ');
    }
}

// If there is a target player, have this player's robots display first
if ($target_player->player_token != 'player'){

    // Create the battle start event, showing the points and amount of turns
    $event_header = $first_event_header;
    $event_body = $first_event_body;
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'center';
    $event_options['canvas_show_this'] = $event_options['console_show_this'] = false;
    $event_options['canvas_show_this_robots'] = false;
    $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
    $event_options['canvas_show_target_robots'] = false;
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_battle->events_create(false, false, '', '');

    // Create the enter event for the target player's robots
    $event_header = $target_player->player_name.'&#39;s '.($target_player->counters['robots_active'] > 1 ? 'Robots' : 'Robot');
    $event_body = $target_player->print_name().'&#39;s '.($target_player->counters['robots_active'] > 1 ? 'robots appear' : 'robot appears').' on the battle field!<br />';
    if ($target_player->player_token != 'player'
        && isset($target_player->player_quotes['battle_start'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($this_player->player_name, $this_robot->robot_name, $target_player->player_name, $target_robot->robot_name);
        $event_body .= $target_player->print_quote('battle_start', $this_find, $this_replace);
    }
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'right';
    $event_options['console_show_this_player'] = true;
    $event_options['console_show_target'] = false;
    $event_options['console_show_target_player'] = false;
    $target_player->set_frame('taunt');
    $target_robot->set_frame('taunt');
    $target_robot->set_frame_styles('');
    $target_robot->set_detail_styles('');
    $target_robot->set_position('active');
    $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);
    $target_player->set_frame('base');
    $target_robot->set_frame('base');

    // Then queue up an the target robot's startup action
    $this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, 'start', '');
    // Execute the battle actions
    $this_battle->actions_execute();

    // Create the enter event for this player's robots
    $event_header = "{$this_player->player_name}&#39;s ".($this_player->counters['robots_active'] > 1 ? 'Robots' : 'Robot');
    $event_body = $this_player->print_name().'&#39;s '.($this_player->counters['robots_active'] > 1 ? 'robots appear' : 'robot appears').' on the battle field!<br />';
    //if (isset($this_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$this_player->player_quotes['battle_start'].'</em>&quot;'; }
    if ($this_player->player_token != 'player'
        && isset($this_player->player_quotes['battle_start'])){
        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
        $event_body .= $this_player->print_quote('battle_start', $this_find, $this_replace);
    }
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'left';
    $event_options['canvas_show_this'] = true;
    $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
    $event_options['console_show_this_player'] = true;
    $event_options['console_show_target_player'] = false;
    $event_options['canvas_show_target_robots'] = true;
    $this_player->set_frame('taunt');
    $this_robot->set_frame('taunt');
    $this_robot->set_frame_styles('');
    $this_robot->set_detail_styles('');
    $this_robot->set_position('active');
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_player->set_frame('base');
    $this_robot->set_frame('base');
    //if ($this_player->counters['robots_active'] == 1){ $this_battle->events_create(false, false, __LINE__.'', __LINE__.'', $event_options); }

    // Queue up this robot's startup action first
    $this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, 'start', '');
    // Execute the battle actions
    $this_battle->actions_execute();


}
// Otherwise, if there is no target player, have the target's robots display first
elseif ($target_player->player_token == 'player'){

    // Create the battle start event, showing the points and amount of turns
    $event_header = $first_event_header;
    $event_body = $first_event_body;
    $event_options = array();
    $event_options['this_header_float'] = $event_options['this_body_float'] = 'center';
    $event_options['canvas_show_this'] = $event_options['console_show_this'] = false;
    $event_options['canvas_show_this_robots'] = false;
    $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
    $event_options['canvas_show_target_robots'] = false;
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_battle->events_create(false, false, '', '');

    // Queue up an the target robot's startup action
    $this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, 'start', '');
    // Execute the battle actions
    $this_battle->actions_execute();

    // Check to see if this player has more than one robot
    if ($this_player->counters['robots_active'] > 1){

        // Create the enter event for this player's robots
        $event_header = "{$this_player->player_name}&#39;s Robots";
        $event_body = $this_player->print_name().'&#39;s '.($this_player->counters['robots_active'] > 1 ? 'robots appear' : 'robot appears').' on the battle field!<br />';
        //if (isset($this_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$this_player->player_quotes['battle_start'].'</em>&quot;'; }
        if ($this_player->player_token != 'player'
            && isset($this_player->player_quotes['battle_start'])){
            $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
            $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
            $event_body .= $this_player->print_quote('battle_start', $this_find, $this_replace);
        }
        $event_options = array();
        $event_options['this_header_float'] = $event_options['this_body_float'] = 'left';
        $event_options['canvas_show_this'] = true;
        $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
        $event_options['console_show_this_player'] = true;
        $event_options['console_show_target_player'] = false;
        //$event_options['canvas_show_target_robots'] = false;

    }
    // Otherwise if this player brought a single robot
    else {

        // Create the enter event for this player's robots
        $event_header = "{$this_player->player_name}&#39;s {$this_robot->robot_name}";
        //$event_body = $this_player->print_name().'&#39;s '.$this_robot->print_name().' appears the battle field!<br />';
        $event_body = $this_robot->print_name().' enters the battle!<br />';
        //if (isset($this_player->player_quotes['battle_start'])){ $event_body .= '&quot;<em>'.$this_player->player_quotes['battle_start'].'</em>&quot;'; }
        if ($this_robot->robot_token != 'robot'
            && isset($this_robot->robot_quotes['battle_start'])){
            $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
            $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
            $event_body .= $this_robot->print_quote('battle_start', $this_find, $this_replace);
        }
        $event_options = array();
        $event_options['this_header_float'] = $event_options['this_body_float'] = 'left';
        $event_options['canvas_show_this'] = true;
        $event_options['canvas_show_target'] = $event_options['console_show_target'] = false;
        $event_options['console_show_this_robot'] = true;
        $event_options['console_show_target_player'] = false;
        //$event_options['canvas_show_target_robots'] = false;

    }

    // Update player and robot frames then show the event
    $this_player->set_frame('taunt');
    $this_robot->set_frame('taunt');
    $this_robot->set_frame_styles('');
    $this_robot->set_detail_styles('');
    $this_robot->set_position('active');
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);
    $this_player->set_frame('base');
    $this_robot->set_frame('base');

}

// Execute the battle actions
$this_battle->actions_execute();

// Show the player's other robots one by one
foreach ($this_player->values['robots_active'] AS $key => $info){
    if (!preg_match('/display:\s?none;/i', $info['robot_frame_styles'])){ continue; }
    if ($this_robot->robot_id == $info['robot_id']){
        $this_robot->set_frame('taunt');
        $this_robot->set_frame_styles('');
        $this_robot->set_detail_styles('');
        $this_battle->events_create(false, false, '', '');
        $this_robot->set_frame('base');
    } else {
        $temp_robot = rpg_game::get_robot($this_battle, $this_player, $info);
        $temp_robot->set_frame('taunt');
        $temp_robot->set_frame_styles('');
        $temp_robot->set_detail_styles('');
        $this_battle->events_create(false, false, '', '');
        $temp_robot->set_frame('base');
    }
}

// Create a final frame before giving control to the user
$this_battle->events_create(false, false, '', '');

?>