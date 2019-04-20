<?

// -- ABILITY BATTLE ACTION -- //

// Increment the battle's turn counter by 1
$this_battle->counters['battle_turn'] += 1;
$this_battle->update_session();

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
$active_target_robot = $target_player->get_active_robot();
if (empty($active_target_robot)){
    $temp_active = $target_player->values['robots_active'];
    $temp_info = array_shift($temp_active);
    $temp_robotinfo = array('robot_id' => $temp_info['robot_id'], 'robot_token' => $temp_info['robot_token']);
    $active_target_robot = rpg_game::get_robot($this_battle, $target_player, $temp_robotinfo);
    $active_target_robot->robot_position = 'active';
    $active_target_robot->update_session();
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

// Collect this player's last action if it exists
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
if (!empty($active_target_robot->values['robot_switch'])){
    if ($active_target_robot->values['robot_switch'] > 1){ $temp_critical_chance = ceil($temp_critical_chance * $active_target_robot->values['robot_switch']);  }
    elseif ($active_target_robot->values['robot_switch'] < 1){ $temp_critical_chance = ceil($temp_critical_chance * (1 / ($active_target_robot->values['robot_switch'] * -1)));  }
}
if ($temp_critical_chance > 100){ $temp_critical_chance = 100; }
//$temp_critical_chance = (int)($temp_critical_chance);

// Check if the switch should be disabled
$temp_switch_disabled = false;
if ($active_target_robot->robot_status != 'disabled'){
    $active_target_attachments = $active_target_robot->get_current_attachments();
    if (!empty($active_target_attachments)){
        foreach ($active_target_attachments AS $attachment_token => $attachment_info){
            if (!empty($attachment_info['attachment_switch_disabled'])){ $temp_switch_disabled = true; }
        }
    }
}

// Check if switch was allowed and successful, else we do ability
if (!$temp_switch_disabled
    && $target_player->counters['robots_active'] > 1
    && $target_energy_damage_percent > 0
    && $target_weapons_damage_percent > 0
    && !in_array('start', $this_recent_actions)
    && !in_array('switch', $this_recent_actions)){

    // Multiply the switch chance if the target is low on life energy
    if ($target_energy_damage_percent >= 60){ $temp_critical_chance = $temp_critical_chance * 1.50; }
    elseif ($target_energy_damage_percent >= 30){ $temp_critical_chance = $temp_critical_chance * 1.25; }

    // Multiply the switch chance if the target is low on weapon energy
    if ($target_weapons_damage_percent >= 60){ $temp_critical_chance = $temp_critical_chance * 1.50; }
    elseif ($target_weapons_damage_percent >= 30){ $temp_critical_chance = $temp_critical_chance * 1.25; }

    // Round the chance and ensure it's not over 100
    $temp_critical_chance = round($temp_critical_chance);
    if ($temp_critical_chance > 100){ $temp_critical_chance = 100; }

    // Switch only on weighted critical chance
    if ($this_battle->critical_chance($temp_critical_chance)){

        // Set the target action to the switch type
        $target_action = 'switch';

    }
    // Otherwise the target will use an ability
    else {

        // Set the target action to the ability type
        $target_action = 'ability';

    }

}
// Otherwise default to ability
else {

    // Set the target action to the ability type
    $target_action = 'ability';

}

// Create the temporary ability object for this player's robot
list($temp_id, $temp_token) = explode('_', $this_action_token); //array('ability_token' => $this_action_token);
$temp_abilityinfo = rpg_ability::get_index_info($temp_token);
$temp_abilityinfo['ability_id'] = $temp_id;
$temp_thisability = rpg_game::get_ability($this_battle, $this_player, $this_robot, $temp_abilityinfo);

// DEBUG
if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
} elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
}

// Back up this temp robot's abilities for later
$temp_active_target_robot_abilities = $active_target_robot->robot_abilities;

// Loop through the target robot's current abilities and check weapon energy
$db_ability_fields = rpg_ability::get_index_fields(true);
$temp_ability_tokens = "'".implode("', '", array_values($active_target_robot->robot_abilities))."'";
$temp_abilities_index = $db->get_array_list("SELECT {$db_ability_fields} FROM mmrpg_index_abilities WHERE ability_flag_complete = 1 AND ability_token IN ({$temp_ability_tokens});", 'ability_token');
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
$temp_abilityinfo = array();
list($temp_abilityinfo['ability_id'], $temp_abilityinfo['ability_token']) = explode('_', $target_action_token);
$temp_indexinfo = rpg_ability::get_index_info($temp_abilityinfo['ability_token']);
$temp_abilityinfo = array_merge($temp_indexinfo, $temp_abilityinfo);
$temp_targetability = rpg_game::get_ability($this_battle, $target_player, $active_target_robot, $temp_abilityinfo);

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
        if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "\$temp_targetability_targetrobot = rpg_game::get_robot(\$this_battle, \$target_player, \$temp_targetability_targetinfo); on line ".__LINE__." {$temp_targetability_targetinfo['robot_token']} ";  }
        $temp_targetability_targetrobot = rpg_game::get_robot($this_battle, $target_player, $temp_targetability_targetinfo);
    }

} elseif ($temp_targetability->ability_target == 'select_this_ally'){

    // Select a random active robot on this player's side of the field
    $temp_activerobots = $target_player->values['robots_active'];
    shuffle($temp_activerobots);
    $temp_targetability_targetinfo = array_shift($temp_activerobots);
    if ($temp_targetability_targetinfo['robot_id'] == $active_target_robot->robot_id){
        $temp_targetability_targetinfo = array_shift($temp_activerobots);
    }
    $temp_targetability_targetplayer = $target_player;
    if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "\$temp_targetability_targetrobot = rpg_game::get_robot(\$this_battle, \$target_player, \$temp_targetability_targetinfo); on line ".__LINE__." {$temp_targetability_targetinfo['robot_token']} ";  }
    $temp_targetability_targetrobot = rpg_game::get_robot($this_battle, $target_player, $temp_targetability_targetinfo);

} else {

    $temp_targetability_targetplayer = $this_player;
    $temp_targetability_targetrobot = $this_robot;

}

// DEBUG
if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
} elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
}

// If this robot is faster than the target
if ($target_action != 'switch' && (
($this_robot->robot_speed >= $active_target_robot->robot_speed && $temp_targetability->ability_speed <= $temp_thisability->ability_speed) ||
($temp_thisability->ability_speed > $temp_targetability->ability_speed)
)){

    // Queue up an this robot's action first, because it's faster
    if ($this_robot->robot_id != $target_robot->robot_id
        && ($temp_thisability->ability_target != 'select_this'
            && $temp_thisability->ability_target != 'select_this_ally')){
        $this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_action_token);
    }
    elseif ($this_robot->robot_id != $target_robot->robot_id
        && ($temp_thisability->ability_target == 'select_this'
            || $temp_thisability->ability_target == 'select_this_ally')){
        $this_battle->actions_append($this_player, $this_robot, $this_player, $target_robot, $this_action, $this_action_token);
    }
    else {
        $this_battle->actions_append($this_player, $this_robot, $this_player, $this_robot, $this_action, $this_action_token);
    }

    // Then queue up an the target robot's action second, because it's slower
    $this_battle->actions_append($target_player, $active_target_robot, $temp_targetability_targetplayer, $temp_targetability_targetrobot, $target_action, $target_action_token);

}
// Else if the target robot is faster than this one or it's switching
else {

    // Then queue up an the target robot's action first, because it's faster and/or switching
    if ($target_action == 'switch'){ $target_action_token = ''; }
    $this_battle->actions_append($target_player, $active_target_robot, $temp_targetability_targetplayer, $temp_targetability_targetrobot, $target_action, $target_action_token);

    // Now execute the stored actions
    $this_battle->actions_execute();
    $this_battle->update_session();

    // Collect the user ability info if set
    $temp_ability_id = false;
    $temp_ability_token = false;
    $temp_ability_info = array();
    if ($this_action == 'ability'){
        list($temp_ability_id, $temp_ability_token) = explode('_', $this_action_token);
        $temp_ability_info = array('ability_id' => $temp_ability_id, 'ability_token' => $temp_ability_token);
        $temp_ability_object = rpg_game::get_ability($this_battle, $this_player, $this_robot, $temp_ability_info);
        $temp_ability_info = $temp_ability_object->export_array();
    }

    // Define the new target robot based on the previous target
    $new_target_robot = false;

    // If this is a special SELECT THIS or SELECT THIS ALLY target ability
    if ($temp_ability_info['ability_target'] == 'select_this'
        || $temp_ability_info['ability_target'] == 'select_this_ally'){

        // Check if this robot is targetting itself or a team mate
        if ($this_robot->robot_id == $backup_target_robot_id){

            // Define the new target robot which is actually a team mate
            $new_target_robot = $this_robot;
            // Update the target robot's session
            $new_target_robot->update_session();
            // Queue up an this robot's action second, because its slower
            $this_battle->actions_append($this_player, $this_robot, $this_player, $new_target_robot, $this_action, $this_action_token);

        } else {

            // Define the new target robot which is actually a team mate
            $new_target_robot = rpg_game::get_robot($this_battle, $this_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
            // Update the target robot's session
            $new_target_robot->update_session();
            // Queue up an this robot's action second, because its slower
            $this_battle->actions_append($this_player, $this_robot, $this_player, $new_target_robot, $this_action, $this_action_token);

        }

    }
    // If this is a special SELECT TARGET ability
    elseif ($temp_ability_info['ability_target'] == 'select_target'){

        // Define the new target robot which is actually a team mate
        $new_target_robot = rpg_game::get_robot($this_battle, $target_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
        // Update the target robot's session
        $new_target_robot->update_session();
        // Queue up an this robot's action second, because its slower
        $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);

    }
    // Else if the target was originally active or the ability is set to auto
    elseif ($backup_target_robot_position == 'active' || (!empty($temp_ability_info) && $temp_ability_info['ability_target'] == 'auto')){

        // Define the new target robot which is the current active target robot
        $new_target_robot = $target_player->get_active_robot(); //rpg_game::get_robot($this_battle, $target_player, array('robot_id' => $active_target_robot->robot_id, 'robot_token' => $active_target_robot->robot_token));
        // Update the target robot's session
        $new_target_robot->update_session();
        // Queue up an this robot's action second, because its slower
        $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);

    }
    // Otherwise, if a normal case of targetting
    else {

        // Define the new target robot which is the original request
        $new_target_robot = rpg_game::get_robot($this_battle, $target_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
        // Update the target robot's session
        $new_target_robot->update_session();
        // Queue up an this robot's action second, because its slower
        $this_battle->actions_append($this_player, $this_robot, $target_player, $new_target_robot, $this_action, $this_action_token);

    }

}

// Now execute the stored actions
$this_battle->actions_execute();

// If the target has been disabled but for some reason hasn't switched
$active_target_robot = $target_player->get_active_robot();
if (!empty($active_target_robot)
    && ($active_target_robot->robot_status == 'disabled'
        || $active_target_robot->robot_energy == 0)){

    // Prepend a switch action for the target robot
    $this_battle->actions_append(
        $target_player,
        $active_target_robot,
        $this_player,
        $this_robot,
        'switch',
        ''
        );

    // Now execute the stored actions
    $this_battle->actions_execute();

}

// Execute any remaining end-of-turn actions that were queued
$this_battle->actions_execute(true);

// DEBUG
if (empty($this_robot)){
    die('<pre>$this_robot is empty on line '.__LINE__.'! :'.print_r($this_robot, true).'</pre>');
} elseif (empty($target_robot)){
    die('<pre>$target_robot is empty on line '.__LINE__.'! :'.print_r($target_robot, true).'</pre>');
}

// If empty, replace active target robot
if (empty($active_target_robot)){ $active_target_robot = $target_player->get_active_robot(); }

// Refresh the backed up target robot
$target_robot = rpg_game::get_robot($this_battle, $target_player, array('robot_id' => $backup_target_robot_id, 'robot_token' => $backup_target_robot_token));
if ($target_robot->robot_status == 'disabled'
    && !empty($active_target_robot)){

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

    // If this robot was targetting itself
    if ($this_robot->robot_id == $target_robot->robot_id){

        // And when the switch is done, queue up an ability for this new target robot to use
        if ($active_target_robot->robot_status != 'disabled' && $active_target_robot->robot_position != 'bench'){
            $this_battle->actions_append($target_player, $active_target_robot, $this_player, $this_robot, 'ability', $target_action_token);
        }

    }
    // Else if this robot was tartetting a team mate
    elseif ($temp_ability_info['ability_target'] == 'select_this'
        || $temp_ability_info['ability_target'] == 'select_this_ally'){

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

// Require the common end-of-turn action file
require(MMRPG_CONFIG_ROOTDIR.'battle/actions/action_endofturn.php');

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