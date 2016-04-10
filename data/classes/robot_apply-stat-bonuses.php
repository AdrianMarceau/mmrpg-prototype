<?
/*
 * ROBOT CLASS FUNCTION APPLY STAT BONUSES
 * public function apply_stat_bonuses(){}
 */

// If this is robot's player is human controlled
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
if ($this->player->player_autopilot != true && $this->robot_class != 'mecha'){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

  // Collect this robot's rewards and settings
  $this_settings = mmrpg_prototype_robot_settings($this->player_token, $this->robot_token);
  $this_rewards = mmrpg_prototype_robot_rewards($this->player_token, $this->robot_token);

  // Update this robot's original player with any session settings
  $this->robot_original_player = mmrpg_prototype_robot_original_player($this->player_token, $this->robot_token);

  // Update this robot's level with any session rewards
  $this->robot_base_experience = $this->robot_experience = mmrpg_prototype_robot_experience($this->player_token, $this->robot_token);
  $this->robot_base_level = $this->robot_level = mmrpg_prototype_robot_level($this->player_token, $this->robot_token);

}
// Otherwise, if this player is on autopilot
else {
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

  // Create an empty reward array to prevent errors
  $this_rewards = !empty($this->values['robot_rewards']) ? $this->values['robot_rewards'] : array();

}

// If the robot experience is over 1000 points, level up and reset
if ($this->robot_experience > 1000){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $level_boost = floor($this->robot_experience / 1000);
  $this->robot_level += $level_boost;
  $this->robot_base_level = $this->robot_level;
  $this->robot_experience -= $level_boost * 1000;
  $this->robot_base_experience = $this->robot_experience;
}

// Fix the level if it's over 100
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
if ($this->robot_level > 100){ $this->robot_level = 100;  }
if ($this->robot_base_level > 100){ $this->robot_base_level = 100;  }

// Update the robot stats based on their current level
if (!empty($this->robot_level) || !empty($this->robot_base_level)){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

  // Define the ytemp level for later calculations
  $temp_level = $this->robot_level - 1;

  // If the robot's level is greater than one, increase stats
  if (!empty($temp_level)){

    /*
    // If this is a computer controlled robot, calculate energy normally
    if ($this->player->player_side == 'right'){
      if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
      // Update the robot energy with a small boost based on experience level
      $this->robot_energy = $this->robot_energy + ceil($temp_level * (0.05 * $this->robot_energy));
      $this->robot_base_energy = $this->robot_base_energy + ceil($temp_level * (0.05 * $this->robot_base_energy));
    }
    // Otherwise, calculate energy boosts based on the player's heart total
    elseif ($this->player->player_side == 'left'){
      if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
      // Update the robot energy with a small boost based on experience level
      $temp_player_energy = 0; // zero for now only
      $this->robot_energy = $this->robot_energy + $temp_player_energy;
      $this->robot_base_energy = $this->robot_base_energy + $temp_player_energy;
    }
    */

    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    // Update the robot energy with a small boost based on experience level
    $this->robot_energy = $this->robot_energy + ceil($temp_level * (0.05 * $this->robot_energy));
    $this->robot_base_energy = $this->robot_base_energy + ceil($temp_level * (0.05 * $this->robot_base_energy));

    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    // Update the robot attack with a small boost based on experience level
    $this->robot_attack = $this->robot_attack + ceil($temp_level * (0.05 * $this->robot_attack));
    $this->robot_base_attack = $this->robot_base_attack + ceil($temp_level * (0.05 * $this->robot_base_attack));
    // Update the robot defense with a small boost based on experience level
    $this->robot_defense = $this->robot_defense + ceil($temp_level * (0.05 * $this->robot_defense));
    $this->robot_base_defense = $this->robot_base_defense + ceil($temp_level * (0.05 * $this->robot_base_defense));
    // Update the robot speed with a small boost based on experience level
    $this->robot_speed = $this->robot_speed + ceil($temp_level * (0.05 * $this->robot_speed));
    $this->robot_base_speed = $this->robot_base_speed + ceil($temp_level * (0.05 * $this->robot_base_speed));

  }

}

// If the robot has earned any energy stat points, apply them
if (!empty($this_rewards['robot_energy'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this->robot_energy += $this_rewards['robot_energy'];
  $this->robot_base_energy += $this_rewards['robot_energy'];
}
// If the robot has earned any attack stat points, apply them
if (!empty($this_rewards['robot_attack'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this->robot_attack += $this_rewards['robot_attack'];
  $this->robot_base_attack += $this_rewards['robot_attack'];
}
// If the robot has earned any defense stat points, apply them
if (!empty($this_rewards['robot_defense'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this->robot_defense += $this_rewards['robot_defense'];
  $this->robot_base_defense += $this_rewards['robot_defense'];
}
// If the robot has earned any speed stat points, apply them
if (!empty($this_rewards['robot_speed'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this->robot_speed += $this_rewards['robot_speed'];
  $this->robot_base_speed += $this_rewards['robot_speed'];
}

// Ensure this robot is being used by its original player before applying bonuses
//if (!empty($this->robot_original_player) && $this->robot_original_player == $this->player->player_token){}

// Apply stat bonuses to this robot based on its current player and their own stats
if (true){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

  // If this robot's player has any stat bonuses, apply them as well
  if (!empty($this->player->player_energy)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    $temp_boost = ceil($this->robot_energy * ($this->player->player_energy / 100));
    $this->robot_energy += $temp_boost;
    $this->robot_base_energy += $temp_boost;
  }
  if (!empty($this->player->player_attack)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    $temp_boost = ceil($this->robot_attack * ($this->player->player_attack / 100));
    $this->robot_attack += $temp_boost;
    $this->robot_base_attack += $temp_boost;
  }
  if (!empty($this->player->player_defense)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    $temp_boost = ceil($this->robot_defense * ($this->player->player_defense / 100));
    $this->robot_defense += $temp_boost;
    $this->robot_base_defense += $temp_boost;
  }
  if (!empty($this->player->player_speed)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    $temp_boost = ceil($this->robot_speed * ($this->player->player_speed / 100));
    $this->robot_speed += $temp_boost;
    $this->robot_base_speed += $temp_boost;
  }

}

// Limit all stats to 9999 for display purposes
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
if ($this->robot_energy > MMRPG_SETTINGS_STATS_MAX){ $this->robot_energy = MMRPG_SETTINGS_STATS_MAX; }
if ($this->robot_base_energy > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_energy = MMRPG_SETTINGS_STATS_MAX; }
if ($this->robot_attack > MMRPG_SETTINGS_STATS_MAX){ $this->robot_attack = MMRPG_SETTINGS_STATS_MAX; }
if ($this->robot_base_attack > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_attack = MMRPG_SETTINGS_STATS_MAX; }
if ($this->robot_defense > MMRPG_SETTINGS_STATS_MAX){ $this->robot_defense = MMRPG_SETTINGS_STATS_MAX; }
if ($this->robot_base_defense > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_defense = MMRPG_SETTINGS_STATS_MAX; }
if ($this->robot_speed > MMRPG_SETTINGS_STATS_MAX){ $this->robot_speed = MMRPG_SETTINGS_STATS_MAX; }
if ($this->robot_base_speed > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_speed = MMRPG_SETTINGS_STATS_MAX; }

// Create the stat boost flag
$this->flags['apply_stat_bonuses'] = true;

// Update the session variable
$this->update_session();

?>