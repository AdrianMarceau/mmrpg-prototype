<?php

// -- PRIMARY START-OF-TURN CHECKS -- //

// Collect both player's active robots
$this_robots_active = $this_player->get_robots_active();
$target_robots_active = $target_player->get_robots_active();

// Create a temp key index of robots that are still active
$this_robot_keys_active = array();

// Loop through this player's robots and apply end-turn checks
foreach ($this_robots_active AS $key => $active_robot){
    $temp_turnstart_function = $active_robot->robot_function_onturnstart;
    $temp_result = $temp_turnstart_function(array(
        'this_battle' => $active_robot->player->battle,
        'this_field' => $active_robot->player->battle->battle_field,
        'this_player' => $active_robot->player,
        'this_robot' => $active_robot,
        'target_player' => $target_player,
        'target_robot' => $target_robot
        ));
    $this_robot_keys_active[] = 'left_'.$active_robot->robot_key;
    if ($active_robot->robot_position == 'active'){ $this_robot_keys_active[] = 'left_-1'; }
    if ($active_robot->robot_id === $this_robot->robot_id){ $this_robot->robot_reload(); }
}

// Loop through the target player's robots and apply end-turn checks
foreach ($target_robots_active AS $key => $active_robot){
    $temp_turnstart_function = $active_robot->robot_function_onturnstart;
    $temp_result = $temp_turnstart_function(array(
        'this_battle' => $active_robot->player->battle,
        'this_field' => $active_robot->player->battle->battle_field,
        'this_player' => $active_robot->player,
        'this_robot' => $active_robot,
        'target_player' => $this_player,
        'target_robot' => $this_robot
        ));
    $this_robot_keys_active[] = 'right_'.$active_robot->robot_key;
    if ($active_robot->robot_position == 'active'){ $this_robot_keys_active[] = 'right_-1'; }
    if ($active_robot->robot_id === $target_robot->robot_id){ $target_robot->robot_reload(); }
}

// -- SECONDARY TURN-START CHECKS -- //

// Loop through this player's robots and apply end-turn checks
foreach ($this_robots_active AS $key => $active_robot){
    $active_robot->check_skills($target_player, $target_robot, 'turn-start');
    $active_robot->check_items($target_player, $target_robot, 'turn-start');
}

// Loop through the target player's robots and apply end-turn checks
foreach ($target_robots_active AS $key => $active_robot){
    $active_robot->check_skills($this_player, $this_robot, 'turn-start');
    $active_robot->check_items($this_player, $this_robot, 'turn-start');
}

?>