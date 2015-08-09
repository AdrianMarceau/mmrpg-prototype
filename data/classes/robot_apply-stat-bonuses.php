<?
/*
 * ROBOT CLASS FUNCTION APPLY STAT BONUSES
 * public function apply_stat_bonuses(){}
 */

// If this is robot's player is human controlled
if ($this->player->player_autopilot != true && $this->robot_class != 'mecha'){
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
  // Create an empty reward array to prevent errors
  $this_rewards = !empty($this->values['robot_rewards']) ? $this->values['robot_rewards'] : array();

}

// Calculate required experience for this robot
$required_experience = mmrpg_prototype_calculate_experience_required($this->robot_level);

// If this is a player battle, automatically set all robot levels to the same value
if (!empty($this->battle->values['player_battle_level'])){

  // Update this robot's level with any session rewards
  $this->robot_base_experience = $this->robot_experience = $required_experience;
  $this->robot_base_level = $this->robot_level = $this->battle->values['player_battle_level'];

}

// If the robot experience is over the required points, level up and reset
if ($this->robot_experience > $required_experience){
  $level_boost = floor($this->robot_experience / $required_experience);
  $this->robot_level += $level_boost;
  $this->robot_base_level = $this->robot_level;
  $this->robot_experience -= $level_boost * $required_experience;
  $this->robot_base_experience = $this->robot_experience;
}

// Fix the level if it's over 100
if ($this->robot_level > 100){ $this->robot_level = 100;  }
if ($this->robot_base_level > 100){ $this->robot_base_level = 100;  }

// Collect references to the base energy, attack, and defense for later
$index_stats = array(
  'energy' => $this->robot_energy, 'base_energy' => $this->robot_base_energy,
  'attack' => $this->robot_attack, 'base_attack' => $this->robot_base_attack,
  'defense' => $this->robot_defense, 'base_defense' => $this->robot_base_defense,
  'speed' => $this->robot_speed, 'base_speed' => $this->robot_base_speed,
  );
// Collect the maximum values for each of these stats for later
$index_stats['max_energy'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($index_stats['energy'], $this->robot_level);
$index_stats['max_attack'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($index_stats['attack'], $this->robot_level);
$index_stats['max_defense'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($index_stats['defense'], $this->robot_level);
$index_stats['max_speed'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($index_stats['speed'], $this->robot_level);

// Update the robot stats based on their current level
if (!empty($this->robot_level) || !empty($this->robot_base_level)){
  // Define the ytemp level for later calculations
  $temp_level = $this->robot_level - 1;

  // If the robot's level is greater than one, increase stats
  if (!empty($temp_level)){

    // If this robot's level is at the max value or greater, set a flag for later
    if ($this->robot_level >= 100){ $this->flags['robot_stat_max_level'] = 1;  }

    // Update the robot energy with a small boost based on experience level
    $this->robot_energy = $this->robot_energy + MMRPG_SETTINGS_STATS_GET_LEVELBOOST($index_stats['energy'], $this->robot_level);
    $this->robot_base_energy = $this->robot_base_energy + MMRPG_SETTINGS_STATS_GET_LEVELBOOST($index_stats['base_energy'], $this->robot_level);

    // Update the robot attack with a small boost based on experience level
    $this->robot_attack = $this->robot_attack + MMRPG_SETTINGS_STATS_GET_LEVELBOOST($index_stats['attack'], $this->robot_level);
    $this->robot_base_attack = $this->robot_base_attack + MMRPG_SETTINGS_STATS_GET_LEVELBOOST($index_stats['base_attack'], $this->robot_level);
    // Update the robot defense with a small boost based on experience level
    $this->robot_defense = $this->robot_defense + MMRPG_SETTINGS_STATS_GET_LEVELBOOST($index_stats['defense'], $this->robot_level);
    $this->robot_base_defense = $this->robot_base_defense + MMRPG_SETTINGS_STATS_GET_LEVELBOOST($index_stats['base_defense'], $this->robot_level);
    // Update the robot speed with a small boost based on experience level
    $this->robot_speed = $this->robot_speed + MMRPG_SETTINGS_STATS_GET_LEVELBOOST($index_stats['speed'], $this->robot_level);
    $this->robot_base_speed = $this->robot_base_speed + MMRPG_SETTINGS_STATS_GET_LEVELBOOST($index_stats['base_speed'], $this->robot_level);

  }

}

// Loop through each of the four stats and collect bonuses
$four_stats = array('speed', 'defense', 'attack'); // In reverse order for more intuitive overflow
$max_stat_overflow = 0; // If player robot has gone over limits, we need to overflow stats
foreach ($four_stats AS $this_stat){
  // If the robot has earned any stat points, apply them
  if (!isset($this_rewards['robot_'.$this_stat])){ $this_rewards['robot_'.$this_stat] = 0; }
  if (!empty($this_rewards['robot_'.$this_stat]) || $this_stat == 'energy'){
    $this->{'robot_'.$this_stat} += $this_rewards['robot_'.$this_stat];
    $this->{'robot_base_'.$this_stat} += $this_rewards['robot_'.$this_stat];
    $max_stat = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($index_stats[$this_stat], $this->robot_level);
    if ($this->{'robot_'.$this_stat} > $max_stat){
      $max_stat_overflow += $this->{'robot_'.$this_stat} - $max_stat;
      $this->{'robot_'.$this_stat} = $max_stat;
      $this->{'robot_base_'.$this_stat} = $max_stat;
    }
  }
}

// Ensure this robot is being used by its original player before applying bonuses
//if (!empty($this->robot_original_player) && $this->robot_original_player == $this->player->player_token){}

if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$index_stats '.print_r($index_stats, true));  }
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$this_stats '.print_r(array('energy' => $this->robot_energy, 'attack' => $this->robot_attack, 'defense' => $this->robot_defense, 'speed' => $this->robot_speed), true));  }
// If this robot's energy rating is at the max value or greater, set a flag for later
if ($this->robot_energy >= $index_stats['max_energy']){ $this->flags['robot_stat_max_energy'] = 1;  }
// If this robot's attack rating is at the max value or greater, set a flag for later
if ($this->robot_attack >= $index_stats['max_attack']){ $this->flags['robot_stat_max_attack'] = 1;  }
// If this robot's defense rating is at the max value or greater, set a flag for later
if ($this->robot_defense >= $index_stats['max_defense']){ $this->flags['robot_stat_max_defense'] = 1;  }
// If this robot's speed rating is at the max value or greater, set a flag for later
if ($this->robot_speed >= $index_stats['max_speed']){ $this->flags['robot_stat_max_speed'] = 1;  }

// Apply stat bonuses to this robot based on its current player and their own stats
if (true){
  // If this robot's player has any stat bonuses, apply them as well
  if (!empty($this->player->player_energy)){
    $temp_boost = ceil($this->robot_energy * ($this->player->player_energy / 100));
    $this->robot_energy += $temp_boost;
    $this->robot_base_energy += $temp_boost;
  }
  if (!empty($this->player->player_attack)){
    $temp_boost = ceil($this->robot_attack * ($this->player->player_attack / 100));
    $this->robot_attack += $temp_boost;
    $this->robot_base_attack += $temp_boost;
  }
  if (!empty($this->player->player_defense)){
    $temp_boost = ceil($this->robot_defense * ($this->player->player_defense / 100));
    $this->robot_defense += $temp_boost;
    $this->robot_base_defense += $temp_boost;
  }
  if (!empty($this->player->player_speed)){
    $temp_boost = ceil($this->robot_speed * ($this->player->player_speed / 100));
    $this->robot_speed += $temp_boost;
    $this->robot_base_speed += $temp_boost;
  }

}

// If this robot is holder a relavant item, apply stat upgrades
if (!empty($this->robot_item)){

  // If this robot is holding an Energy Upgrade, double the life energy stat
  if ($this->robot_item == 'item-energy-upgrade'){
    $this->robot_energy += $this->robot_energy;
    $this->robot_base_energy += $this->robot_base_energy;
  }

  // Else if this robot is holding a Weapon Upgrade, double the life energy stat
  elseif ($this->robot_item == 'item-weapon-upgrade'){
    $this->robot_weapons += $this->robot_weapons;
    $this->robot_base_weapons += $this->robot_base_weapons;
  }

}


// Limit all stats to maximums for display purposes
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