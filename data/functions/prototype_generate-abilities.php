<?
// Define a function for generating an ability set for a given robot
function mmrpg_prototype_generate_abilities($robot_info, $robot_level = 1, $ability_num = 1, $robot_item = ''){
  global $DB;
  // Define the static variables for the ability lists
  static $mmrpg_prototype_core_abilities;
  static $mmrpg_prototype_master_support_abilities;
  static $mmrpg_prototype_mecha_support_abilities;
  static $mmrpg_prototype_darkness_abilities;
  // Define all the core and support abilities to be used in generating
  if (empty($mmrpg_prototype_core_abilities)){
    $mmrpg_prototype_core_abilities = array(
      array(
        'rolling-cutter', 'super-throw', 'ice-breath', 'hyper-bomb', 'fire-storm', 'thunder-strike', 'time-arrow', 'oil-shooter',
        'metal-blade', 'air-shooter', 'bubble-spray', 'quick-boomerang', 'crash-bomber', 'flash-stopper', 'atomic-fire', 'leaf-shield',
        'needle-cannon', 'magnet-missile', 'gemini-laser', 'hard-knuckle', 'top-spin', 'search-snake', 'spark-shock', 'shadow-blade',
        'bright-burst', 'rain-flush', 'drill-blitz', 'pharaoh-soul', 'ring-boomerang', 'dust-crusher', 'dive-missile', 'skull-barrier',
        'cutter-shot', 'cutter-buster',
        'freeze-shot', 'freeze-buster',
        'crystal-shot', 'crystal-buster',
        'flame-shot', 'flame-buster',
        'electric-shot', 'electric-buster',
        'space-shot', 'space-buster',
        'laser-shot', 'laser-buster'
        ),
      array(
        'rising-cutter', 'super-arm', 'ice-slasher', 'danger-bomb', 'fire-chaser', 'thunder-beam', 'time-slow', 'oil-slider',
        'bubble-lead',
        'bubble-bomb'
        ),
      array(
        'cutter-overdrive',
        'freeze-overdrive',
        'crystal-overdrive',
        'flame-overdrive',
        'electric-overdrive',
        'space-overdrive',
        'laser-overdrive'
        )
      );
  }
  if (empty($mmrpg_prototype_master_support_abilities)){
    $mmrpg_prototype_master_support_abilities = array(
      array(
        'buster-shot'
        ),
      array(
        'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
        ),
      array(
        'attack-break', 'defense-break', 'speed-break', 'energy-break',
        ),
      array(
        'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
        ),
      array(
        'attack-mode', 'defense-mode', 'speed-mode', 'repair-mode',
        ),
      array(
        'attack-support', 'defense-support', 'speed-support', 'energy-support',
        'attack-assault', 'defense-assault', 'speed-assault', 'energy-assault',
        ),
      array(
        'attack-shuffle', 'defense-shuffle', 'speed-shuffle', 'energy-shuffle'
        ),
      array(
        'mecha-support', 'field-support'
        ),
      array(
        'experience-booster', 'recovery-booster', 'damage-booster',
        'experience-breaker', 'recovery-breaker', 'damage-breaker',
        )
      );
  }
  if (empty($mmrpg_prototype_mecha_support_abilities)){
    $mmrpg_prototype_mecha_support_abilities = array(
      array(
        'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
        ),
      array(
        'attack-break', 'defense-break', 'speed-break', 'energy-break',
        ),
      array(
        'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
        )
      );
  }
  if (empty($mmrpg_prototype_darkness_abilities)){
    $mmrpg_prototype_darkness_abilities = array(
      array(
        'dark-boost', 'dark-break', 'dark-drain'
        )
      );
  }

  // Define the array for holding all of this robot's abilities
  $this_robot_abilities = array();
  //$temp_core_abilities = $mmrpg_prototype_core_abilities;
  //$temp_support_abilities = $mmrpg_prototype_master_support_abilities;

  // Loop through this robot's level-up abilities looking for one
  $this_robot_index = $robot_info;
  if (!empty($this_robot_index['robot_rewards']['abilities'])){
    foreach ($this_robot_index['robot_rewards']['abilities'] AS $info){
      // If this is the buster shot or too high of a level, continue
      if ($info['token'] == 'buster-shot' || $info['level'] > $robot_level){ continue; }
      // If this is an incomplete master ability, continue
      if ($this_robot_index['robot_class'] == 'master'){
        if (!in_array($info['token'], $mmrpg_prototype_core_abilities[0]) && !in_array($info['token'], $mmrpg_prototype_core_abilities[1])){
          continue;
        }
      }
      // Add this ability token the list
      $this_robot_abilities[] = $info['token'];
    }
  }

  // Define a new array to hold all the addon abilities
  $this_robot_abilities_addons = array('base' => $this_robot_abilities, 'weapons' => array(), 'support' => array());

  // If we have already enough abilities, we have nothing more to do
  if (count($this_robot_abilities) >= $ability_num){

    // Simple slice to make sure we don't go over eight
    $this_robot_abilities = array_slice($this_robot_abilities, 0, $ability_num);

  }
  // Otherwise, if we need more abilities, we generate them dynamically
  else {

    // Define the number of additional abilities to add
    $remaining_abilities = $ability_num - count($this_robot_abilities);

    // Collect the ability index for calculation purposes
    static $this_ability_index;
    if (empty($this_ability_index)){ $this_ability_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token'); }

    // Check if this robot is holding a core
    $robot_item_core = !empty($robot_item) && preg_match('/^item-core-/i', $robot_item) ? preg_replace('/^item-core-/i', '', $robot_item) : '';

    // Define the number of core and support abilities for the robot
    if ($this_robot_index['robot_class'] == 'master'){
      foreach ($mmrpg_prototype_core_abilities AS $group_key => $group_abilities){
        if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
        foreach ($group_abilities AS $ability_key => $ability_token){
          if (in_array($ability_token, $this_robot_abilities)){ continue; }
          $ability_info = $this_ability_index[$ability_token];
          $is_compatible = false;
          if (!$is_compatible && in_array($ability_token, $this_robot_index['robot_abilities'])){
            $is_compatible = true;
          }
          if (!$is_compatible && !empty($this_robot_index['robot_core'])){
            if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
          }
          if (!$is_compatible && !empty($robot_item_core)){
            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
          }
          if ($is_compatible){ $this_robot_abilities_addons['weapons'][] = $ability_token; }
        }
        unset($ability_info);
      }
    }

    // Define the number of core and master support abilities for the robot
    if ($this_robot_index['robot_class'] == 'master' && $this_robot_index['robot_core'] != 'empty'){
      foreach ($mmrpg_prototype_master_support_abilities AS $group_key => $group_abilities){
        if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
        foreach ($group_abilities AS $ability_key => $ability_token){
          if (in_array($ability_token, $this_robot_abilities)){ continue; }
          $ability_info = $this_ability_index[$ability_token];
          $is_compatible = false;
          if (!$is_compatible && in_array($ability_token, $this_robot_index['robot_abilities'])){
            $is_compatible = true;
          }
          if (!$is_compatible && !empty($this_robot_index['robot_core'])){
            if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
          }
          if (!$is_compatible && !empty($robot_item_core)){
            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
          }
          if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
        }
        unset($ability_info);
      }
    }
    // Define the number of core and mecha support abilities for the robot
    elseif ($this_robot_index['robot_class'] == 'mecha' && $this_robot_index['robot_core'] != 'empty'){
      foreach ($mmrpg_prototype_mecha_support_abilities AS $group_key => $group_abilities){
        if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
        foreach ($group_abilities AS $ability_key => $ability_token){
          if (in_array($ability_token, $this_robot_abilities)){ continue; }
          $ability_info = $this_ability_index[$ability_token];
          $is_compatible = false;
          if (!$is_compatible && empty($ability_info['ability_type'])){
            $is_compatible = true;
          }
          if (!$is_compatible && !empty($ability_info['ability_type'])){
            if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
          }
          if (!$is_compatible && !empty($robot_item_core)){
            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
          }
          if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
        }
        unset($ability_info);
      }
    }

    // Define the number of darkness abilities for the robot
    if ($this_robot_index['robot_core'] == 'empty' && $this_robot_index['robot_class'] == 'master'){
      foreach ($mmrpg_prototype_darkness_abilities AS $group_key => $group_abilities){
        if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
        foreach ($group_abilities AS $ability_key => $ability_token){
          if (in_array($ability_token, $this_robot_abilities)){ continue; }
          $ability_info = mmrpg_ability::parse_index_info($this_ability_index[$ability_token]);
          $is_compatible = false;
          if (!$is_compatible && in_array($ability_token, $this_robot_index['robot_abilities'])){
            $is_compatible = true;
          }
          if (!$is_compatible && !empty($this_robot_index['robot_core'])){
            if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
          }
          if (!$is_compatible && !empty($robot_item_core)){
            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
          }
          if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
        }
        unset($ability_info);
      }
    }

    // Shuffle the weapons and support arrays
    $this_robot_abilities_addons['weapons'] = array_unique($this_robot_abilities_addons['weapons']);
    $this_robot_abilities_addons['support'] = array_unique($this_robot_abilities_addons['support']);
    shuffle($this_robot_abilities_addons['weapons']);
    shuffle($this_robot_abilities_addons['support']);

    // If there were no main abilities, give them an addons
    if (empty($this_robot_abilities) && !empty($this_robot_abilities_addons['weapons'])){
      $temp_token = array_shift($this_robot_abilities_addons['weapons']);
      $this_robot_abilities[] = $temp_token;
      $this_robot_abilities_addons['base'][] = $temp_token;
    }

    // Define the last addon array which will have alternating values
    $temp_addons_final = array();
    $temp_count_limit = count($this_robot_abilities_addons['weapons']) + count($this_robot_abilities_addons['support']);
    for ($i = 0; $i < $temp_count_limit; $i++){
      if (isset($this_robot_abilities_addons['weapons'][$i]) || isset($this_robot_abilities_addons['support'][$i])){
        if (isset($this_robot_abilities_addons['support'][$i])){ $temp_addons_final[] = $this_robot_abilities_addons['support'][$i]; }
        if (isset($this_robot_abilities_addons['weapons'][$i])){ $temp_addons_final[] = $this_robot_abilities_addons['weapons'][$i]; }
      } else {
        break;
      }
    }

    // Combine the two arrays into one again
    //$this_robot_abilities = array_merge($this_robot_abilities_addons['base'], $this_robot_abilities_addons['weapons'], $this_robot_abilities_addons['support']);
    $this_robot_abilities = array_merge($this_robot_abilities, $temp_addons_final);
    // Crop the array to the requested length
    $this_robot_abilities = array_slice($this_robot_abilities, 0, $ability_num);

  }

  // Return the ability array, whatever it was
  return $this_robot_abilities;

}
?>