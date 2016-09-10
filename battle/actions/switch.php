<?

// -- SWITCH BATTLE ACTION -- //

// DEBUG
//$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'switch to '.$this_action_token);

/*
// Define whether to skip the target's turn based on
// if this player is replacing a fainted robot
$skip_target_turn = $this_robot->robot_status == 'disabled' || ($this_robot->robot_status == 'active' && $this_robot->robot_position == 'bench') ? true : false;
*/

// Increment the battle's turn counter by 1 if zero
if (empty($this_battle->counters['battle_turn'])){
    $this_battle->counters['battle_turn'] += 1;
    $this_battle->update_session();
}

// DEBUG
//$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'now switching to '.$this_action_token.' from '.$this_robot->robot_id.'_'.$this_robot->robot_token.'?');

// Switching should not take a turn - let's encourage it!
$skip_target_turn = true;
// Queue up this robot's switch action first
$this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, 'switch', $this_action_token);

// Execute the battle actions
$this_battle->actions_execute();

// Now loop through the player's active robot to collect the new active robot
list($temp_robot_id, $temp_robot_token) = explode('_', $this_action_token);
foreach ($this_player->values['robots_active'] AS $key => $info){
    if ($info['robot_id'] == $temp_robot_id){
        $this_robot->robot_load(array('robot_id' => $info['robot_id'], 'robot_token' => $info['robot_token']));
        //$this_robot->robot_load($info);
        $this_robot->update_session();
        break;
     }
}


// Otherwise if the target robot is disabled we have no choice
if ($target_robot->robot_energy < 1 || $target_robot->robot_status == 'disabled'){
    // Then queue up an the target robot's action first, because it's faster and/or switching
    $this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, 'switch', '');
    // Now execute the stored actions
    $this_battle->actions_execute();
    $this_battle->update_session();
}

// Create a flag on this player, preventing multiple switches per turn
$this_player->flags['switch_used_this_turn'] = true;
$this_player->update_session();

// DEBUG
//$this_battle->events_create($this_robot, $target_robot, 'DEBUG', 'so now i am '.$this_robot->robot_id.'_'.$this_robot->robot_token.'? also '.$this_robot->robot_position.'');




?>