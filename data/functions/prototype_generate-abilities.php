<?
// Define all the core and support abilities to be used in generating
$mmrpg_prototype_core_abilities = array(
  array(
    'rolling-cutter', 'super-throw', 'ice-breath', 'hyper-bomb', 'fire-storm', 'thunder-strike', 'time-arrow', 'oil-shooter',
    'metal-blade', 'air-shooter', 'bubble-spray', 'quick-boomerang', 'crash-bomber', 'flash-stopper', 'atomic-fire', 'leaf-shield',
    'needle-cannon', 'magnet-missile', 'gemini-laser', 'hard-knuckle', 'top-spin', 'search-snake', 'spark-shock', 'shadow-blade',
    'bright-burst', 'rain-flush', 'drill-blitz', 'pharaoh-soul', 'ring-boomerang', 'dust-crusher', 'dive-missile', 'skull-barrier',
    'flame-shot', 'flame-buster', 'freeze-shot', 'freeze-buster',
    'electric-shot', 'electric-buster', 'space-shot', 'space-buster',
    'cutter-shot', 'cutter-buster',
    ),
  array(
    'rising-cutter', 'super-arm', 'ice-slasher', 'danger-bomb', 'fire-chaser', 'thunder-beam', 'time-slow', 'oil-slider',
    'bubble-lead', 'bubble-bomb'
    )
  );
$mmrpg_prototype_master_support_abilities = array(
  array(
    'buster-shot'
    ),
  array(
    'attack-blaze', 'defense-blaze', 'speed-blaze',
    'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
    ),
  array(
    'attack-burn', 'defense-burn', 'speed-burn',
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
$mmrpg_prototype_mecha_support_abilities = array(
  array(
    'attack-blaze', 'defense-blaze', 'speed-blaze',
    'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
    ),
  array(
    'attack-burn', 'defense-burn', 'speed-burn',
    'attack-break', 'defense-break', 'speed-break', 'energy-break',
    ),
  array(
    'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
    )
  );
$mmrpg_prototype_darkness_abilities = array(
  array(
    'dark-boost', 'dark-break', 'dark-drain'
    )
  );

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

if ($this_robot_index['robot_class'] == 'mecha'){
  //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre>');
}

// Define a new array to hold all the addon abilities
$this_robot_abilities_addons = array('base' => $this_robot_abilities, 'weapons' => array(), 'support' => array());

if ($this_robot_index['robot_class'] == 'mecha'){
  //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre><hr />$this_robot_abilities_addons = <pre>'.print_r($this_robot_abilities_addons, true).'</pre>');
}

// If we have already enough abilities, we have nothing more to do
if (count($this_robot_abilities) >= $ability_num){

  // Simple slice to make sure we don't go over eight
  $this_robot_abilities = array_slice($this_robot_abilities, 0, $ability_num);

  if ($this_robot_index['robot_class'] == 'mecha'){
    //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre>');
  }

}
// Otherwise, if we need more abilities, we generate them dynamically
else {

  // Define the number of additional abilities to add
  $remaining_abilities = $ability_num - count($this_robot_abilities);

  // Collect the ability index for calculation purposes
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this_ability_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

  // Define the number of core and support abilities for the robot
  if ($this_robot_index['robot_class'] == 'master'){
    foreach ($mmrpg_prototype_core_abilities AS $group_key => $group_abilities){
      if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
      foreach ($group_abilities AS $ability_key => $ability_token){
        if (in_array($ability_token, $this_robot_abilities) || in_array($ability_token, $this_robot_abilities_addons['weapons']) || in_array($ability_token, $this_robot_abilities_addons['support'])){ continue; }
        $ability_info = mmrpg_ability::parse_index_info($this_ability_index[$ability_token]);
        $is_compatible = false;
        if (in_array($ability_token, $this_robot_index['robot_abilities'])){
          $is_compatible = true;
        } elseif (!empty($this_robot_index['robot_core'])){
          if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
          elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
          elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
        }
        // DEBUG
        //if ($robot_token == 'jewel-man'){ echo('Testing ability '.$ability_token.'... '.($is_compatible ? 'compatible!' : 'not compatible...').'<br />'); }
        if ($is_compatible){ $this_robot_abilities_addons['weapons'][] = $ability_token; }
      }
      unset($ability_info);
    }
  }

  if ($this_robot_index['robot_class'] == 'mecha'){
    //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre><hr />$this_robot_abilities_addons = <pre>'.print_r($this_robot_abilities_addons, true).'</pre>');
  }

  // Define the number of core and master support abilities for the robot
  if ($this_robot_index['robot_class'] == 'master' && $this_robot_index['robot_core'] != 'empty'){
    foreach ($mmrpg_prototype_master_support_abilities AS $group_key => $group_abilities){
      if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
      foreach ($group_abilities AS $ability_key => $ability_token){
        if (in_array($ability_token, $this_robot_abilities) || in_array($ability_token, $this_robot_abilities_addons['support'])){ continue; }
        $ability_info = mmrpg_ability::parse_index_info($this_ability_index[$ability_token]);
        $is_compatible = false;
        if (in_array($ability_token, $this_robot_index['robot_abilities'])){ $is_compatible = true; }
        elseif (!empty($this_robot_index['robot_core'])){
          if ($this_robot_index['robot_core'] == 'copy'){ $is_compatible = true; }
          elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
          elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
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
        if (in_array($ability_token, $this_robot_abilities) || in_array($ability_token, $this_robot_abilities_addons['support'])){ continue; }
        $ability_info = mmrpg_ability::parse_index_info($this_ability_index[$ability_token]);
        $is_compatible = false;
        if (empty($ability_info['ability_type'])){ $is_compatible = true; }
        elseif (!empty($ability_info['ability_type'])){
          if ($this_robot_index['robot_core'] == 'copy'){ $is_compatible = true; }
          elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
          elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
        }
        if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
      }
      unset($ability_info);
    }
  }

  if ($this_robot_index['robot_class'] == 'mecha'){
    //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre><hr />$this_robot_abilities_addons = <pre>'.print_r($this_robot_abilities_addons, true).'</pre>');
  }

  // Define the number of darkness abilities for the robot
  if ($this_robot_index['robot_core'] == 'empty' && $this_robot_index['robot_class'] == 'master'){
    foreach ($mmrpg_prototype_darkness_abilities AS $group_key => $group_abilities){
      if (floor($robot_level / 10) < ($group_key + 1)){ continue; }
      foreach ($group_abilities AS $ability_key => $ability_token){
        if (in_array($ability_token, $this_robot_abilities) || in_array($ability_token, $this_robot_abilities_addons['support'])){ continue; }
        $ability_info = mmrpg_ability::parse_index_info($this_ability_index[$ability_token]);
        $is_compatible = false;
        if (in_array($ability_token, $this_robot_index['robot_abilities'])){ $is_compatible = true; }
        elseif (!empty($this_robot_index['robot_core'])){
          if ($this_robot_index['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
          elseif (!empty($ability_info['ability_type']) && $this_robot_index['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
          elseif (!empty($ability_info['ability_type2']) && $this_robot_index['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
        }
        if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
      }
      unset($ability_info);
    }
  }

  if ($this_robot_index['robot_class'] == 'mecha'){
    //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre><hr />$this_robot_abilities_addons = <pre>'.print_r($this_robot_abilities_addons, true).'</pre>');
  }

  // Shuffle the weapons and support arrays
  shuffle($this_robot_abilities_addons['weapons']);
  shuffle($this_robot_abilities_addons['support']);

  if ($this_robot_index['robot_class'] == 'mecha'){
    //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre><hr />$this_robot_abilities_addons = <pre>'.print_r($this_robot_abilities_addons, true).'</pre>');
  }

  // If there were no main abilities, give them an addons
  if (empty($this_robot_abilities) && !empty($this_robot_abilities_addons['weapons'])){
    $temp_token = array_shift($this_robot_abilities_addons['weapons']);
    $this_robot_abilities[] = $temp_token;
    $this_robot_abilities_addons['base'][] = $temp_token;
  }

  if ($this_robot_index['robot_class'] == 'mecha'){
    //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre><hr />$this_robot_abilities_addons = <pre>'.print_r($this_robot_abilities_addons, true).'</pre>');
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

  if ($this_robot_index['robot_class'] == 'mecha'){
    //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre><hr />$this_robot_abilities_addons = <pre>'.print_r($this_robot_abilities_addons, true).'</pre>');
  }

  // Combine the two arrays into one again
  //$this_robot_abilities = array_merge($this_robot_abilities_addons['base'], $this_robot_abilities_addons['weapons'], $this_robot_abilities_addons['support']);
  $this_robot_abilities = array_merge($this_robot_abilities, $temp_addons_final);
  // Crop the array to the requested length
  $this_robot_abilities = array_slice($this_robot_abilities, 0, $ability_num);

  if ($this_robot_index['robot_class'] == 'mecha'){
    //die('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre><hr />$this_robot_abilities_addons = <pre>'.print_r($this_robot_abilities_addons, true).'</pre>');
  }

}

if ($this_robot_index['robot_class'] == 'mecha'){
  //echo('On line '.__LINE__.' with $ability_num = '.$ability_num.' <hr />$this_robot_index = <pre>'.print_r($this_robot_index, true).'</pre><hr />$this_robot_abilities = <pre>'.print_r($this_robot_abilities, true).'</pre><hr />$this_robot_abilities_addons = <pre>'.print_r($this_robot_abilities_addons, true).'</pre>');
}

?>