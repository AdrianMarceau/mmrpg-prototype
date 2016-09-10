<?

// -- ABILITY-ITEM BATTLE ACTION -- //

// Increment the battle's turn counter by 1
$this_battle->counters['battle_turn'] += 1;
$this_battle->update_session();


// -- This Item Action -- //

// Create the temporary item object for this player's robot
$temp_iteminfo = array();
list($temp_iteminfo['item_id'], $temp_iteminfo['item_token']) = explode('_', $this_action_token); //array('item_token' => $this_action_token);
$temp_thisitem = new rpg_item($this_battle, $this_player, $this_robot, $temp_iteminfo);

// Queue up an this robot's action first, because it's using an item
$this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_action_token);

// Now execute the stored actions (and any created in the process of executing them!)
$this_battle->actions_execute();

// Update the sesions I guess
$this_robot->update_session();
$target_robot->update_session();

// If this item was an ITEM, decrease it's quantity in the player's session
if (preg_match('/^([0-9]+)_/i', $this_action_token)){
    // Decrease the quantity of this item from the player's inventory
    list($temp_item_id, $temp_item_token) = explode('_', $this_action_token);
    if (!empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){
        $temp_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_item_token];
        $temp_quantity -= 1;
        if ($temp_quantity < 0){ $temp_quantity = 0; }
        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = $temp_quantity;
    }
}

// Create a flag on this player, preventing multiple items per turn
$this_player->flags['item_used_this_turn'] = true;
$this_player->update_session();

// Now execute the stored actions (and any created in the process of executing them!)
$this_battle->actions_execute();


// -- Target Ability Actions -- //

// Backup the data for the this robot for later reference
$backup_this_robot_id = $this_robot->robot_id;
$backup_this_robot_token = $this_robot->robot_token;
$backup_this_robot_position = $this_robot->robot_position;

// Backup the data for the targetted robot for later reference
$backup_target_robot_id = $target_robot->robot_id;
$backup_target_robot_token = $target_robot->robot_token;
$backup_target_robot_position = $target_robot->robot_position;

// DEBUG
if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
} elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
}

// If the current target robot is the active one as well
if ($this_robot->robot_id != $target_robot->robot_id
    && $target_robot->robot_position == 'active'){
    $active_target_robot = $target_robot;
}
// Otherwise, if the target was a benched robot
else {
    $active_target_robot = false;
    foreach ($target_player->values['robots_active'] AS $temp_robotinfo){
        if ($temp_robotinfo['robot_position'] == 'active'){
            $temp_robotinfo = array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token']);
            $active_target_robot = rpg_game::get_robot($this_battle, $target_player, $temp_robotinfo);
            $active_target_robot->update_session();
            break;
        }
    }
    if (empty($active_target_robot)){
        $temp_robotinfo = array_slice($target_player->values['robots_active'], 0, 1);
        $temp_robotinfo = array_shift($temp_robotinfo);
        $temp_robotinfo = array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token']);
        $active_target_robot = rpg_game::get_robot($this_battle, $target_player, $target_player->player_robots[0]);
        $active_target_robot->robot_position = 'active';
        $active_target_robot->update_session();
    }
}

// DEBUG
if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
} elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
}

// Define the switch change based on remaining energy
$target_energy_percent = ceil(($active_target_robot->robot_energy / $active_target_robot->robot_base_energy) * 100);
$target_energy_damage_percent = 100 - $target_energy_percent;

// Define the switch change based on remaining weapons
$target_weapons_percent = ceil(($active_target_robot->robot_weapons / $active_target_robot->robot_base_weapons) * 100);
$target_weapons_damage_percent = 100 - $target_weapons_percent;

// Collect the target player's last action if it exists
if (!empty($target_player->history['actions'])){
    end($target_player->history['actions']);
    $this_last_action = current($target_player->history['actions']);
    $this_recent_actions = array_slice($target_player->history['actions'], -1, 1, false); //array_slice($target_player->history['actions'], -3, 3, false);
    foreach ($this_recent_actions AS $key => $info){
        $this_recent_actions[$key] = $info['this_action'];
    }
}
// Otherwise define an empty action
else {
    $this_last_action = array('this_action' => '', 'this_action_token' => '');
    $this_recent_actions = array();
}

// One in ten chance of switching
if ($target_energy_damage_percent > 0){ $temp_critical_chance = ceil($target_energy_damage_percent / 3); }
elseif ($target_weapons_damage_percent > 0){ $temp_critical_chance = ceil($target_weapons_damage_percent / 3); }
else { $temp_critical_chance = 1; }
if ($target_player->player_switch != 1){ $temp_critical_chance = ceil($temp_critical_chance * $target_player->player_switch); }
if ($temp_critical_chance > 100){ $temp_critical_chance = 100; }
$temp_critical_chance = (int)($temp_critical_chance);

// Check if the switch should be disabled
$temp_switch_disabled = false;
if ($active_target_robot->robot_status != 'disabled' && !empty($active_target_robot->robot_attachments)){
    foreach ($active_target_robot->robot_attachments AS $attachment_token => $attachment_info){
        if (!empty($attachment_info['attachment_switch_disabled'])){ $temp_switch_disabled = true; }
    }
}

// Check if switch was successful, else we do ability
if (!$temp_switch_disabled
    && $target_player->counters['robots_active'] > 1
    && $target_energy_damage_percent > 0
    && !in_array('start', $this_recent_actions)
    && !in_array('switch', $this_recent_actions)
    && $this_battle->critical_chance($temp_critical_chance)){
    // Set the target action to the switch type
    $target_action = 'switch';
}
// Otherwise default to ability
else {
    // Set the target action to the ability type
    $target_action = 'ability';
}

// Back up this temp robot's abilities for later
$temp_active_target_robot_abilities = $active_target_robot->robot_abilities;

// Loop through the target robot's current abilities and check weapon energy
$temp_ability_tokens = "'".implode("', '", array_values($active_target_robot->robot_abilities))."'";
$temp_abilities_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 AND ability_token IN ({$temp_ability_tokens});", 'ability_token');
foreach ($active_target_robot->robot_abilities AS $key => $token){

    // Collect the data for this ability from the index
    $info = rpg_ability::parse_index_info($temp_abilities_index[$token]);
    if (empty($info)){ unset($active_target_robot->robot_abilities[$key]); continue; }
    $temp_ability = rpg_game::get_ability($this_battle, $target_player, $active_target_robot, $info);
    // Determine how much weapon energy this should take
    $temp_ability_energy = $active_target_robot->calculate_weapon_energy($temp_ability);
    // If this robot does not have enough energy for the move, remove it
    if ($active_target_robot->robot_weapons < $temp_ability_energy){ unset($active_target_robot->robot_abilities[$key]); continue; }

}

// If there are no abilities left to use, the robot will automatically enter a recharge state
if (empty($active_target_robot->robot_abilities)){ $active_target_robot->robot_abilities[] = 'action-noweapons'; }

// Update the robot's session with ability changes
$active_target_robot->update_session();

// Collect the ability choice from the robot
$temp_token = rpg_robot::robot_choices_abilities(array(
    'this_index' => $mmrpg_index,
    'this_battle' => $this_battle,
    'this_field' => $this_battle->battle_field,
    'this_player' => $target_player,
    'this_robot' => $active_target_robot,
    'target_player' => $this_player,
    'target_robot' => $this_robot
    ));
$temp_id = array_search($temp_token, $active_target_robot->robot_abilities);
$target_action_token = $temp_id.'_'.$temp_token;

// Now that we're done selecting an ability, reset to normal
$active_target_robot->robot_abilities = $temp_active_target_robot_abilities;
$active_target_robot->update_session();

// DEBUG
if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
} elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
}

// Create the temporary ability object for the target player's robot
$temp_ability_info = array();
list($temp_ability_info['ability_id'], $temp_ability_info['ability_token']) = explode('_', $target_action_token);
$temp_index_info = rpg_ability::get_index_info($temp_ability_info['ability_token']);
$temp_ability_info = array_merge($temp_index_info, $temp_ability_info);
$temp_targetability = rpg_game::get_ability($this_battle, $target_player, $active_target_robot, $temp_ability_info);

// If the target player's temporary ability allows target selection
if ($temp_targetability->ability_target == 'select_target'){

    // Select a random active robot on this player's side of the field
    $temp_activerobots = $this_player->values['robots_active'];
    shuffle($temp_activerobots);
    $temp_targetability_targetinfo = array_shift($temp_activerobots);
    if ($temp_targetability_targetinfo['robot_id'] == $this_robot->robot_id){
        $temp_targetability_targetplayer = $this_player;
        $temp_targetability_targetrobot = $this_robot;
    } else {
        $temp_targetability_targetplayer = $this_player;
        $temp_targetability_targetrobot = rpg_game::get_robot($this_battle, $this_player, $temp_targetability_targetinfo);
    }

} elseif ($temp_targetability->ability_target == 'select_this'){

    // Select a random active robot on this player's side of the field
    $temp_activerobots = $target_player->values['robots_active'];
    shuffle($temp_activerobots);
    $temp_targetability_targetinfo = array_shift($temp_activerobots);
    if ($temp_targetability_targetinfo['robot_id'] == $active_target_robot->robot_id){
        $temp_targetability_targetplayer = $target_player;
        $temp_targetability_targetrobot = $active_target_robot;
    } else {
        $temp_targetability_targetplayer = $target_player;
        $temp_targetability_targetrobot = rpg_game::get_robot($this_battle, $target_player, $temp_targetability_targetinfo);
    }

} else {

    // Otherwise target the opposing robot directly
    $temp_targetability_targetplayer = $this_player;
    $temp_targetability_targetrobot = $this_robot;

}

// Queue up an the target robot's action now that we're done deciding what it is
$this_battle->actions_append($target_player, $active_target_robot, $temp_targetability_targetplayer, $temp_targetability_targetrobot, $target_action, $target_action_token);

// Refresh the backed up target robot
$target_robot = rpg_game::get_robot($this_battle, $target_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
if ($target_robot->robot_status == 'disabled'){

    // Recollect the active target robot for the sake of auto targetting
    $target_robot = rpg_game::get_robot($this_battle, $target_player, array('robot_id' => $active_target_robot->robot_id, 'robot_token' => $active_target_robot->robot_token));

}

// Loop through the target robots and hide any disabled robots
foreach ($target_player->player_robots AS $temp_robotinfo){
    if ($temp_robotinfo['robot_status'] == 'disabled'){
        $temp_robot = rpg_game::get_robot($this_battle, $target_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token']));
        $temp_robot->flags['apply_disabled_state'] = true;
        $temp_robot->update_session();
    }
}

// DEBUG
if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
} elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
}

// If the target's was a switch action, also queue up an ability
if ($target_action == 'switch'){

    // Now execute the stored actions
    $this_battle->actions_execute();

    // Update the active robot reference just in case it has changed
    foreach ($target_player->player_robots AS $temp_robotinfo){
        if ($temp_robotinfo['robot_position'] == 'active'){
            $active_target_robot = rpg_game::get_robot($this_battle, $target_player, $temp_robotinfo);
            $active_target_robot->robot_position = 'active';
            $active_target_robot->update_session();
        } else {
            $temp_robot = rpg_game::get_robot($this_battle, $target_player, $temp_robotinfo);
            $temp_robot->robot_position = 'bench';
            $temp_robot->update_session();
        }
    }

    // Use the first target robot as active if one could not be found
    if (empty($active_target_robot)){
        $active_target_robot = rpg_game::get_robot($this_battle, $target_player, $target_player->player_robots[0]);
        $active_target_robot->robot_position = 'active';
        $active_target_robot->update_session();
    }

    // Collect the ability choice from the robot
    $temp_token = rpg_robot::robot_choices_abilities(array(
        'this_index' => $mmrpg_index,
        'this_battle' => $this_battle,
        'this_field' => $this_field,
        'this_player' => $target_player,
        'this_robot' => $active_target_robot,
        'target_player' => $this_player,
        'target_robot' => $this_robot
        ));
    $temp_id = array_search($temp_token, $active_target_robot->robot_abilities);
    $target_action_token = $temp_id.'_'.$temp_token;

    // Create the temporary ability object for the target player's robot
    $temp_ability_info = array();
    list($temp_ability_info['ability_id'], $temp_ability_info['ability_token']) = explode('_', $target_action_token);
    $temp_index_info = rpg_ability::get_index_info($temp_ability_info['ability_token']);
    $temp_ability_info = array_merge($temp_index_info, $temp_ability_info);
    $temp_targetability = rpg_game::get_ability($this_battle, $target_player, $active_target_robot, $temp_ability_info);

    // If this robot was targetting itself
    if ($this_robot->robot_id == $target_robot->robot_id){

        // And when the switch is done, queue up an ability for this new target robot to use
        if ($active_target_robot->robot_status != 'disabled' && $active_target_robot->robot_position != 'bench'){
            $this_battle->actions_append($target_player, $active_target_robot, $this_player, $this_robot, 'ability', $target_action_token);
        }

    }
    // Else if this robot was tartetting a team mate
    elseif ($temp_ability_info['ability_target'] == 'select_this'){

        // And when the switch is done, queue up an ability for this new target robot to use
        if ($active_target_robot->robot_status != 'disabled' && $active_target_robot->robot_position != 'bench'){
            $this_battle->actions_append($target_player, $active_target_robot, $this_player, $this_robot, 'ability', $target_action_token);
        }

    }
    // Otherwise if this was a normal switch by the target
    else {

        // And when the switch is done, queue up an ability for this new target robot to use
        if ($target_robot->robot_status != 'disabled' && $target_robot->robot_position != 'bench'){
            $this_battle->actions_append($target_player, $target_robot, $this_player, $this_robot, 'ability', $target_action_token);
        }

    }

}

// DEBUG
if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
} elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
}

// Now execute the stored actions (and any created in the process of executing them!)
$this_battle->actions_execute();


// -- END OF TURN ACTIONS -- //

// If the battle has not concluded, check the robot attachments
if ($this_battle->battle_status != 'complete'){

    // DEBUG
    if (empty($this_robot)){
        die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
    } elseif (empty($target_robot)){
        die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
    }


    // Loop through all this player's robots and carry out any end-turn events
    rpg_battle::temp_check_robot_attachments($this_battle, $this_player, $this_robot, $target_player, $target_robot);
    rpg_battle::temp_check_robot_weapons($this_battle, $this_player, $this_robot, $target_player, $target_robot);

    // Loop through all the target player's robots and carry out any end-turn events
    rpg_battle::temp_check_robot_attachments($this_battle, $target_player, $target_robot, $this_player, $this_robot);
    rpg_battle::temp_check_robot_weapons($this_battle, $target_player, $target_robot, $this_player, $this_robot);

    // Create an empty field to remove any leftover frames
    $this_battle->events_create(false, false, '', '');

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

// Unset any item use flags for this player, so they can use one again next turn
if (isset($this_player->flags['item_used_this_turn'])){
    unset($this_player->flags['item_used_this_turn']);
    $this_player->update_session();
}

// Unset any switch use flags for this player, so they can use one again next turn
if (isset($this_player->flags['switch_used_this_turn'])){
    unset($this_player->flags['switch_used_this_turn']);
    $this_player->update_session();
}



?>