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
    $this_settings = !empty($this->values['robot_settings']) ? $this->values['robot_settings'] : array();
    $this_rewards = !empty($this->values['robot_rewards']) ? $this->values['robot_rewards'] : array();

}

// If the robot experience is over 1000 points, level up and reset
if ($this->robot_experience > 1000){
    $level_boost = floor($this->robot_experience / 1000);
    $this->robot_level += $level_boost;
    $this->robot_base_level = $this->robot_level;
    $this->robot_experience -= $level_boost * 1000;
    $this->robot_base_experience = $this->robot_experience;
}

// Fix the level if it's over 100
if ($this->robot_level > 100){ $this->robot_level = 100;  }
if ($this->robot_base_level > 100){ $this->robot_base_level = 100;  }

// Collect this robot's stat values for later reference
$this_index_info = self::get_index_info($this->robot_token);
$this_robot_stats = self::calculate_stat_values($this->robot_level, $this_index_info, $this_rewards);

// Update the robot's stat values with calculated totals
$stat_tokens = array('energy', 'attack', 'defense', 'speed');
foreach ($stat_tokens AS $stat){
    $prop_stat = 'robot_'.$stat;
    $prop_stat_base = 'robot_base_'.$stat;
    $prop_stat_max = 'robot_max_'.$stat;
    $this->$prop_stat = $this_robot_stats[$stat]['current'];
    $this->$prop_stat_base = $this_robot_stats[$stat]['current'];
    $this->$prop_stat_max = $this_robot_stats[$stat]['max'];

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