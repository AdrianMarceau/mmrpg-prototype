<?
// Generate the markup for the action ability panel
ob_start();
  // Define and start the order counter
  $temp_order_counter = 1;
  // Display container for the main actions
  ?><div class="main_actions main_actions_hastitle"><span class="main_actions_title">Select Ability</span><?
  // Collect the abilities for this robot, by whatever means
  if ($this_robot->robot_class == 'master'){

    $this_robot_settings = mmrpg_prototype_robot_settings($this_player->player_token, $this_robot->robot_token);

    if (!empty($this_robot_settings['robot_abilities'])){ $current_robot_abilities = $this_robot_settings['robot_abilities']; }
    //elseif (!empty($this_robot->robot_abilities)){ $current_robot_abilities = $this_robot->robot_abilities; }
    else { $current_robot_abilities = array(); }

    // If this robot has more than eight abilities, slice to only eight
    if (count($current_robot_abilities) > 8){
      $current_robot_abilities = array_slice($current_robot_abilities, 0, 8);
      $_SESSION['GAME']['values']['battle_settings'][$this_player->player_token]['player_robots'][$this_robot->robot_token]['robot_abilities'] = $current_robot_abilities;
    }

    // Collect the robot's held item if any
    if (!empty($_SESSION['GAME']['values']['battle_settings'][$this_player->player_token]['player_robots'][$this_robot->robot_token]['robot_item'])){ $current_robot_item = $_SESSION['GAME']['values']['battle_settings'][$this_player->player_token]['player_robots'][$this_robot->robot_token]['robot_item']; }
    else { $current_robot_item = ''; }

  } elseif ($this_robot->robot_class == 'mecha'){
    // Collect the temp ability index
    $temp_index_info = mmrpg_robot::get_index_info($this_robot->robot_token);
    $current_robot_abilities = array();
    foreach ($temp_index_info['robot_abilities'] AS $token){ $current_robot_abilities[$token] = array('ability_token' => $token); }
    $current_robot_item = '';

  }
  // Ensure this robot has abilities to display
  if (!empty($current_robot_abilities)){
    // Count the total number of abilities
    $num_abilities = count($this_robot->robot_abilities);
    $robot_direction = $this_player->player_side == 'left' ? 'right' : 'left';
    // Define the ability display counter
    $unlocked_abilities_count = 0;
    // Collect the temp ability index
    $temp_robots_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
    $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
    $temp_robotinfo = $temp_robots_index[$this_robot->robot_token];
    $temp_robotinfo = mmrpg_robot::parse_index_info($temp_robotinfo);
    if ($temp_robotinfo['robot_core'] != $this_robot->robot_core){ $temp_robotinfo['robot_core'] = $this_robot->robot_core; }
    $temp_robotinfo['robot_core2'] = preg_match('/^item-core-/i', $current_robot_item) ? preg_replace('/^item-core-/i', '', $current_robot_item) : '';
    if ($temp_robotinfo['robot_core2'] == 'none'){ $temp_robotinfo['robot_core2'] = ''; }
    //if (empty($temp_abilities_index)){ $temp_abilities_index = array(); }
    // Loop through each ability and display its button
    $ability_key = 0;
    //$temp_robot_array = $this_robot->export_array();
    foreach ($current_robot_abilities AS $ability_token => $ability_info){
      if (empty($ability_token) || !isset($temp_abilities_index[$ability_token])){ continue; }

      // Check if this ability has been unlocked
      $this_ability_unlocked = true;
      if ($this_ability_unlocked){ $unlocked_abilities_count++; }
      else { continue; }

      // Create the ability object using the session/index data
      $temp_abilityinfo = $temp_abilities_index[$ability_token];
      $temp_abilityinfo = mmrpg_ability::parse_index_info($temp_abilityinfo);
      $temp_abilityinfo['ability_id'] = $this_robot->robot_id.str_pad($temp_abilityinfo['ability_id'], 3, '0', STR_PAD_LEFT);
      $temp_ability = new mmrpg_ability($this_battle, $this_player, $this_robot, $temp_abilityinfo);
      $temp_type = $temp_ability->ability_type;
      $temp_type2 = $temp_ability->ability_type2;
      $temp_damage = $temp_ability->ability_damage;
      $temp_damage2 = $temp_ability->ability_damage2;
      $temp_damage_unit = $temp_ability->ability_damage_percent ? '%' : '';
      $temp_damage2_unit = $temp_ability->ability_damage2_percent ? '%' : '';
      $temp_recovery = $temp_ability->ability_recovery;
      $temp_recovery2 = $temp_ability->ability_recovery2;
      $temp_recovery_unit = $temp_ability->ability_recovery_percent ? '%' : '';
      $temp_recovery2_unit = $temp_ability->ability_recovery2_percent ? '%' : '';
      $temp_accuracy = $temp_ability->ability_accuracy;
      $temp_kind = !empty($temp_damage) && empty($temp_recovery) ? 'damage' : (!empty($temp_recovery) && empty($temp_damage) ? 'recovery' : (!empty($temp_damage) && !empty($temp_recovery) ? 'multi' : ''));
      $temp_target = 'auto';
      $temp_target_text = '';
      if ($temp_ability->ability_target == 'select_target' && $target_player->counters['robots_active'] > 1){ $temp_target = 'select_target'; $temp_target_text = 'Select Target'; }
      elseif ($temp_ability->ability_target == 'select_this'){ $temp_target = 'select_this'; $temp_target_text = 'Select Target'; }
      //elseif ($temp_ability->ability_target == 'select_this' && $this_player->counters['robots_active'] > 1){ $temp_target = 'select_this'; $temp_target_text = 'Select Target'; }
      //$temp_target = $target_player->counters['robots_active'] > 1 ? $temp_ability->ability_target : 'auto';

      $temp_multiplier = 1;
      if (!empty($this_robot->robot_core) && ($this_robot->robot_core == $temp_type || $this_robot->robot_core == $temp_type2)){ $temp_multiplier = $temp_multiplier * 1.5; }
      if (!empty($temp_type) && !empty($this_battle->battle_field->field_multipliers[$temp_type])){ $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers[$temp_type]; }
      elseif (!empty($this_battle->battle_field->field_multipliers['none'])){ $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers['none']; }
      if (!empty($temp_type2) && !empty($this_battle->battle_field->field_multipliers[$temp_type2])){ $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers[$temp_type2]; }
      //elseif (!empty($this_battle->battle_field->field_multipliers['none'])){ $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers['none']; }
      //if (!empty($temp_type) && !empty($_SESSION['GAME']['values']ar_force'][$temp_type])){ $temp_multiplier += $temp_multiplier * ($_SESSION['GAME']['values']['star_force'][$temp_type] / 10); }
      //elseif (!empty($_SESSION['GAME']['values']['star_force']['none'])){ $temp_multiplier += $temp_multiplier * ($_SESSION['GAME']['values']['star_force']['none'] / 10); }
      //if (!empty($temp_type2) && !empty($_SESSION['GAME']['values']['star_force'][$temp_type2])){ $temp_multiplier += $temp_multiplier * ($_SESSION['GAME']['values']['star_force'][$temp_type2] / 10); }
      //elseif (!empty($_SESSION['GAME']['values']['star_force']['none'])){ $temp_multiplier += $temp_multiplier * ($_SESSION['GAME']['values']['star_force']['none'] / 10); }

      $temp_damage = ceil($temp_damage * $temp_multiplier);
      if (!preg_match('/-(booster|breaker)$/i', $ability_token) && !empty($this_battle->battle_field->field_multipliers['damage'])){ $temp_damage = ceil($temp_damage * $this_battle->battle_field->field_multipliers['damage']); }
      if ($temp_damage_unit == '%' && $temp_damage > 100){ $temp_damage = 100; }
      $temp_damage2 = ceil($temp_damage2 * $temp_multiplier);
      if (!preg_match('/-(booster|breaker)$/i', $ability_token) && !empty($this_battle->battle_field->field_multipliers['damage'])){ $temp_damage2 = ceil($temp_damage2 * $this_battle->battle_field->field_multipliers['damage']); }
      if ($temp_damage2_unit == '%' && $temp_damage2 > 100){ $temp_damage2 = 100; }

      $temp_recovery = ceil($temp_recovery * $temp_multiplier);
      if (!preg_match('/-(booster|breaker)$/i', $ability_token) && !empty($this_battle->battle_field->field_multipliers['recovery'])){ $temp_recovery = ceil($temp_recovery * $this_battle->battle_field->field_multipliers['recovery']); }
      if ($temp_recovery_unit == '%' && $temp_recovery > 100){ $temp_recovery = 100; }
      $temp_recovery2 = ceil($temp_recovery2 * $temp_multiplier);
      if (!preg_match('/-(booster|breaker)$/i', $ability_token) && !empty($this_battle->battle_field->field_multipliers['recovery'])){ $temp_recovery2 = ceil($temp_recovery2 * $this_battle->battle_field->field_multipliers['recovery']); }
      if ($temp_recovery2_unit == '%' && $temp_recovery2 > 100){ $temp_recovery2 = 100; }

      // Define the amount of weapon energy for this ability
      $temp_robot_weapons = $this_robot->robot_weapons;
      $temp_ability_energy = $this_robot->calculate_weapon_energy($temp_ability, $temp_ability_energy_base, $temp_ability_energy_mods);

      // Define the ability title details text
      $temp_ability_details = $temp_ability->ability_name;
      $temp_ability_details .= ' ('.(!empty($temp_ability->ability_type) ? $mmrpg_index['types'][$temp_ability->ability_type]['type_name'] : 'Neutral');
      if (!empty($temp_ability->ability_type2)){ $temp_ability_details .= ' / '.$mmrpg_index['types'][$temp_ability->ability_type2]['type_name']; }
      else { $temp_ability_details .= ' Type'; }
      $temp_ability_details .= ') <br />';
      //if ($temp_kind == 'damage'){ $temp_ability_details .= ($temp_multiplier != 1 ? '<del>'.$temp_ability->ability_damage.'</del> ' : '').$temp_damage.$temp_damage_unit.' Damage'; }
      //elseif ($temp_kind == 'recovery'){ $temp_ability_details .= ($temp_multiplier != 1 ? '<del>'.$temp_ability->ability_recovery.'</del> ' : '').$temp_recovery.$temp_recovery_unit.' Recovery'; }
      //elseif ($temp_kind == 'multi'){ $temp_ability_details .= ($temp_multiplier != 1 ? '<del>'.$temp_ability->ability_damage.'</del> ' : '').$temp_damage.$temp_damage_unit.' Damage / '.($temp_multiplier != 1 ? '<del>'.$temp_ability->ability_recovery.'</del> ' : '').$temp_recovery.$temp_recovery_unit.' Recovery'; }
      if ($temp_kind == 'damage'){ $temp_ability_details .= $temp_damage.$temp_damage_unit.' Damage'; }
      elseif ($temp_kind == 'recovery'){ $temp_ability_details .= $temp_recovery.$temp_recovery_unit.' Recovery'; }
      elseif ($temp_kind == 'multi'){ $temp_ability_details .= $temp_damage.$temp_damage_unit.' Damage / '.($temp_multiplier != 1 ? '<del>'.$temp_ability->ability_recovery.'</del> ' : '').$temp_recovery.$temp_recovery_unit.' Recovery'; }
      else { $temp_ability_details .= 'Support'; }
      $temp_ability_details .= ' | '.$temp_ability->ability_accuracy.'% Accuracy';
      //if (!empty($temp_ability_energy)){ $temp_ability_details .= ' | '.(!empty($temp_ability_energy_mods) ? '<del>'.$temp_ability_energy_base.'</del> ' : '').$temp_ability_energy.' Energy'; }
      if (!empty($temp_ability_energy)){ $temp_ability_details .= ' | '.$temp_ability_energy.' Energy'; }
      if (!empty($temp_target_text)){ $temp_ability_details .= ' | '.$temp_target_text; }
      $temp_ability_description = $temp_ability->ability_description;
      $temp_ability_description = str_replace('{DAMAGE}', $temp_damage, $temp_ability_description);
      $temp_ability_description = str_replace('{RECOVERY}', $temp_recovery, $temp_ability_description);
      $temp_ability_description = str_replace('{DAMAGE2}', $temp_damage2, $temp_ability_description);
      $temp_ability_description = str_replace('{RECOVERY2}', $temp_recovery2, $temp_ability_description);
      $temp_ability_details .= ' <br />'.$temp_ability_description;
      $temp_ability_details_plain = strip_tags(str_replace('<br />', '&#10;', $temp_ability_details));
      $temp_ability_details_tooltip = htmlentities($temp_ability_details, ENT_QUOTES, 'UTF-8');

      //$temp_ability_details .= ' | x'.$temp_multiplier.' '.$this_robot->robot_core.' '.count($this_battle->battle_field->field_multipliers);

      // Define the ability button text variables
      $temp_ability_label = '<span class="multi">';
      $temp_ability_label .= '<span class="maintext">'.$temp_ability->ability_name.'</span>';
      $temp_ability_label .= '<span class="subtext">';
        $temp_ability_label .= (!empty($temp_type) ? $mmrpg_index['types'][$temp_ability->ability_type]['type_name'].' ' : 'Neutral ');
        if (!empty($temp_type2)){ $temp_ability_label .= ' / '.$mmrpg_index['types'][$temp_ability->ability_type2]['type_name']; }
        else { $temp_ability_label .= ($temp_kind == 'damage' ? 'Damage' : ($temp_kind == 'recovery' ? 'Recovery' : ($temp_kind == 'multi' ? 'Effects' : 'Special'))); }
      $temp_ability_label .= '</span>';
      $temp_ability_label .= '<span class="subtext">';
        $temp_ability_label .= '<span style="'.($temp_multiplier != 1 ? ($temp_multiplier > 1 ? 'color: rgb(161, 255, 124); ' : 'color: rgb(255, 150, 150); ') : '').'">P:'.($temp_kind == 'damage' ? $temp_damage.$temp_damage_unit.' ' : ($temp_kind == 'recovery' ? $temp_recovery.$temp_recovery_unit.' ' : ($temp_kind == 'multi' ? $temp_damage.$temp_damage_unit.'/'.$temp_recovery.$temp_recovery_unit.' ' : '0'))).'</span>';
        $temp_ability_label .= '&nbsp;';
        $temp_ability_label .= 'A:'.$temp_accuracy.'%';
      $temp_ability_label .= '</span>';
      $temp_ability_label .= '</span>';

      // Define whether or not this ability button should be enabled
      $temp_button_enabled = $temp_robot_weapons >= $temp_ability_energy ? true : false;

      // If the ability is not actually compatible with this robot, disable it
      //$temp_robot_array = $this_robot->export_array();
      $temp_ability_array = $temp_ability->export_array();
      $temp_button_compatible = mmrpg_robot::has_ability_compatibility($temp_robotinfo, $temp_abilityinfo, $current_robot_item);
      if (!$temp_button_compatible){ $temp_button_enabled = false; }

      // If this button is enabled, add it to the global ability options array
      if ($temp_button_enabled){ $temp_player_ability_actions[] = $temp_ability->ability_token; }

      // Define the ability sprite variables
      $temp_ability_sprite = array();
      $temp_ability_sprite['name'] = $temp_ability->ability_name;
      if ($this_robot->robot_class == 'master'){

        $temp_ability_sprite['image'] = $temp_ability->ability_image;
        $temp_ability_sprite['image_size'] = $temp_ability->ability_image_size;
        $temp_ability_sprite['image_size_text'] = $temp_ability_sprite['image_size'].'x'.$temp_ability_sprite['image_size'];
        $temp_ability_sprite['image_size_zoom'] = $temp_ability->ability_image_size * 2;
        $temp_ability_sprite['image_size_zoom_text'] = $temp_ability_sprite['image_size'].'x'.$temp_ability_sprite['image_size'];
        $temp_ability_sprite['url'] = 'images/abilities/'.$temp_ability_sprite['image'].'/icon_'.$robot_direction.'_'.$temp_ability_sprite['image_size_text'].'.png';
        $temp_ability_sprite['class'] = 'sprite size'.$temp_ability_sprite['image_size'].' base ';
        $temp_ability_sprite['style'] = 'background-image: url('.$temp_ability_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.'); top: 5px; left: 5px; ';
        $temp_ability_sprite['markup'] = '<span class="'.$temp_ability_sprite['class'].' ability" style="'.$temp_ability_sprite['style'].'">'.$temp_ability_sprite['name'].'</span>';
        $temp_ability_sprite['markup'] .= '<span class="'.$temp_ability_sprite['class'].' weapons" style="top: 35px; left: 5px; '.($temp_ability_energy == $temp_ability_energy_base ? '' : ($temp_ability_energy_mods <= 1 ? 'color: #80A280; ' : 'color: #68B968; ')).'">'.$temp_ability_energy.' WE</span>';

      } elseif ($this_robot->robot_class == 'mecha'){

        $temp_ability_sprite['image'] = $this_robot->robot_image;
        $temp_ability_sprite['image_size'] = $this_robot->robot_image_size;
        $temp_ability_sprite['image_size_text'] = $temp_ability_sprite['image_size'].'x'.$temp_ability_sprite['image_size'];
        $temp_ability_sprite['image_size_zoom'] = $this_robot->robot_image_size * 2;
        $temp_ability_sprite['image_size_zoom_text'] = $temp_ability_sprite['image_size'].'x'.$temp_ability_sprite['image_size'];
        $temp_ability_sprite['url'] = 'images/robots/'.$temp_ability_sprite['image'].'/mug_'.$robot_direction.'_'.$temp_ability_sprite['image_size_text'].'.png';
        $temp_ability_sprite['class'] = 'sprite size'.$temp_ability_sprite['image_size'].' base ';
        $temp_ability_sprite['style'] = 'background-image: url('.$temp_ability_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.'); top: 7px; left: 5px; height: 43px; background-position: center center !important; background-size: 50% 50% !important; ';
        $temp_ability_sprite['markup'] = '<span class="'.$temp_ability_sprite['class'].' ability" style="'.$temp_ability_sprite['style'].'">'.$temp_ability_sprite['name'].'</span>';
        //$temp_ability_sprite['markup'] .= '<span class="'.$temp_ability_sprite['class'].' weapons" style="top: 35px; left: 5px; '.($temp_ability_energy == $temp_ability_energy_base ? '' : ($temp_ability_energy_mods <= 1 ? 'color: #80A280; ' : 'color: #68B968; ')).'">'.$temp_ability_energy.' WE</span>';

      }

      $temp_ability_sprite['preload'] = 'images/abilities/'.$temp_ability_sprite['image'].'/sprite_'.$robot_direction.'_'.$temp_ability_sprite['image_size_zoom_text'].'.png';

      // Now use the new object to generate a snapshot of this ability button
      if ($temp_button_enabled){ ?><a data-order="<?=$temp_order_counter?>" class="button action_ability ability_<?= $temp_ability->ability_token ?> ability_type ability_type_<?= (!empty($temp_ability->ability_type) ? $temp_ability->ability_type : 'none').(!empty($temp_ability->ability_type2) ? '_'.$temp_ability->ability_type2 : '') ?> block_<?= $unlocked_abilities_count ?>" type="button" data-action="ability_<?= $temp_ability->ability_id.'_'.$temp_ability->ability_token ?>" data-tooltip="<?= $temp_ability_details_tooltip ?>" data-target="<?= $temp_target ?>"><label class=""><?= $temp_ability_sprite['markup'] ?><?= $temp_ability_label ?></label></a><? }
      else { ?><a data-order="<?=$temp_order_counter?>" class="button button_disabled action_ability ability_<?= $temp_ability->ability_token ?> ability_type ability_type_<?= (!empty($temp_ability->ability_type) ? $temp_ability->ability_type : 'none').(!empty($temp_ability->ability_type2) ? '_'.$temp_ability->ability_type2 : '') ?> block_<?= $unlocked_abilities_count ?>" type="button"><label class=""><?= $temp_ability_sprite['markup'] ?><?= $temp_ability_label ?></label></a><? }
      // Increment the order counter
      $temp_order_counter++;

      $ability_key++;
    }

    // If there were less than 8 abilities, fill in the empty spaces
    if ($unlocked_abilities_count < 8){
      for ($i = $unlocked_abilities_count; $i < 8; $i++){
        // Display an empty button placeholder
        ?><a class="button action_ability button_disabled block_<?= $i + 1 ?>" type="button">&nbsp;</a><?
      }
    }

    // Unset the temp abilities index
    $temp_abilities_index = false;
    unset($temp_abilities_index);
  }
  // End the main action container tag
  //echo 'Abilities : ['.print_r($this_robot->robot_abilities, true).']';
  //echo preg_replace('#\s+#', ' ', print_r($this_robot_settings, true));
  ?></div><?
  // Display the back button by default
  ?><div data-debug="<?= $temp_robot_weapons ?>" class="sub_actions"><a data-order="<?=$temp_order_counter?>" class="button action_back" type="button" data-panel="battle"><label>Back</label></a></div><?
  // Increment the order counter
  $temp_order_counter++;
$actions_markup['ability'] = trim(ob_get_clean());
$actions_markup['ability'] = preg_replace('#\s+#', ' ', $actions_markup['ability']);
?>