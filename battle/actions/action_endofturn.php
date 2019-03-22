<?

// -- END OF TURN ACTIONS -- //

// If the battle has not concluded, check the robot attachments
if ($this_battle->battle_status != 'complete'){

    // DEBUG
    if (empty($this_robot)){
        die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
    } elseif (empty($target_robot)){
        die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
    }

    // Collect both player's robots to check for inactive
    $this_player_robots = $this_player->get_robots();
    $target_player_robots = $target_player->get_robots();

    // Loop through this player's robots and check to see if any disabled have been missed
    foreach ($this_player_robots AS $key => $active_robot){
        if ($active_robot->get_id() == $this_robot->get_id()){ $active_robot = $this_robot; }
        if ($active_robot->robot_energy > 0){ continue; }
        if (empty($active_robot->flags['apply_disabled_state'])){
            $active_robot->trigger_disabled($target_robot);
        } else {
            $active_robot->set_status('disabled');
            $active_robot->set_flag('hidden', true);
        }
    }

    // Loop through the target player's robots and check to see if any disabled have been missed
    foreach ($target_player_robots AS $key => $active_robot){
        if ($active_robot->get_id() == $target_robot->get_id()){ $active_robot = $target_robot; }
        if ($active_robot->robot_energy > 0){ continue; }
        if (empty($active_robot->flags['apply_disabled_state'])){
            $active_robot->trigger_disabled($this_robot);
        } else {
            $active_robot->set_status('disabled');
            $active_robot->set_flag('hidden', true);
        }
    }

    // Collect both player's active robots
    $this_robots_active = $this_player->get_robots_active();
    $target_robots_active = $target_player->get_robots_active();

    // Create a temp key index of robots that are still active
    $this_robot_keys_active = array();

    // Loop through this player's robots and apply end-turn checks
    foreach ($this_robots_active AS $key => $active_robot){
        if ($active_robot->get_id() == $this_robot->get_id()){ $active_robot = $this_robot; }
        $active_robot->check_history($target_player, $target_robot);
        $active_robot->check_items($target_player, $target_robot);
        $active_robot->check_attachments($target_player, $target_robot);
        $active_robot->check_weapons($target_player, $target_robot);
        $this_robot_keys_active[] = 'left_'.$active_robot->robot_key;
        if ($active_robot->robot_position == 'active'){ $this_robot_keys_active[] = 'left_-1'; }
    }

    // Loop through the target player's robots and apply end-turn checks
    foreach ($target_robots_active AS $key => $active_robot){
        if ($active_robot->get_id() == $target_robot->get_id()){ $active_robot = $target_robot; }
        $active_robot->check_history($this_player, $this_robot);
        $active_robot->check_items($this_player, $this_robot);
        $active_robot->check_attachments($this_player, $this_robot);
        $active_robot->check_weapons($this_player, $this_robot);
        $this_robot_keys_active[] = 'right_'.$active_robot->robot_key;
        if ($active_robot->robot_position == 'active'){ $this_robot_keys_active[] = 'right_-1'; }
    }

    // Re-collect both player's active robots
    $this_robots_active = $this_player->get_robots_active();
    $target_robots_active = $target_player->get_robots_active();

    // Check the durations of stray attachments if they exist
    $player_sides = array('left', 'right');
    $player_bench_sizes = array('left' => $this_player->counters['robots_total'], 'right' => $target_player->counters['robots_total']);
    $max_bench_size = max($this_player->counters['robots_total'], $target_player->counters['robots_total']);
    $battle_has_updated = false;
    foreach ($player_sides AS $player_side){
        $bench_size = $player_bench_sizes[$player_side];
        for ($key = -1; $key < $max_bench_size; $key++){
            if (in_array($player_side.'_'.$key, $this_robot_keys_active)){ continue; }
            if ($key == -1){ $static_key = $player_side.'-active'; }
            else { $static_key = $player_side.'-bench-'.$key; }
            if (!empty($this_battle->battle_attachments[$static_key])){
                $attachments = $this_battle->battle_attachments[$static_key];
                if (!empty($attachments)){
                    foreach ($attachments AS $key2 => $attachment){
                        if (isset($attachment['attachment_duration'])){ $attachment['attachment_duration'] -= 1; }
                        if ($attachment['attachment_duration'] > 0){ $this_battle->battle_attachments[$static_key][$key2] = $attachment; }
                        else { unset($this_battle->battle_attachments[$static_key][$key2]); }
                        $battle_has_updated = true;
                    }
                }
            }
        }
    }

    // Update the battle session if anything has changed
    if ($battle_has_updated){ $this_battle->update_session(); }

    // Create an empty field to remove any leftover frames
    $this_battle->events_create();

    // If this the player's last robot
    if ($this_player->counters['robots_active'] == 0){
        // Trigger the battle complete event
        $this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot, '', '');
    }
    // Else if the target player's on their last robot
    elseif ($target_player->counters['robots_active'] == 0){
        // Trigger the battle complete event
        $this_battle->battle_complete_trigger($this_player, $this_robot, $target_player, $target_robot, '', '');
    }

}

?>