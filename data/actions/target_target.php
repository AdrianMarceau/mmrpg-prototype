<?
// Generate the markup for the action target panel
ob_start();
  // Define and start the order counter
  $temp_order_counter = 1;
  // Display container for the main actions
  ?><div class="main_actions main_actions_hastitle"><span class="main_actions_title">Select {thisPanel} Target</span><?
  // Ensure there are robots to display
  if (!empty($target_player->player_robots)){
    // Count the total number of robots
    $num_robots = count($target_player->player_robots);
    $robot_direction = $target_player->player_side == 'left' ? 'right' : 'left';
    // Define the sorting function for the target robots
    if (!function_exists('mmrpg_action_sort_target')){
      function mmrpg_action_sort_target($info1, $info2){
        if ($info1['robot_position'] == 'active'){ return -1; }
        elseif ($info2['robot_position'] == 'active'){ return 1; }
        elseif ($info1['robot_key'] < $info2['robot_key']){ return -1; }
        elseif ($info1['robot_key'] > $info2['robot_key']){ return 1; }
        else { return 0; }
      }
    }
    // Collect the target robot options and sort them
    $target_player_robots = $target_player->player_robots;
    usort($target_player_robots, 'mmrpg_action_sort_target');
    // Loop through each robot and display its target button
    foreach ($target_player_robots AS $robot_key => $scan_robotinfo){
      // Ensure this is an actual switch in the index
      if (!empty($switch_robotinfo['robot_token'])){
        // Create the scan object using the session/index data
        //$GLOBALS['DEBUG']['checkpoint_line'] = 'data.php : line 655';
        $temp_robot = new mmrpg_robot($this_battle, $target_player, $scan_robotinfo);
        // Default the allow button flag to true
        $allow_button = true;
        // If this robot is disabled, disable the button
        if ($temp_robot->robot_status == 'disabled'){ $allow_button = false; }
        // If this robot is not active, disable the button
        //if ($temp_robot->robot_position != 'active'){ $allow_button = false; }
        // Define the title hover for the robot
        $temp_robot_title = $temp_robot->robot_name.'  (Lv. '.$temp_robot->robot_level.')';
        //$temp_robot_title .= ' | '.$temp_robot->robot_id.'';
        $temp_robot_title .= ' <br />'.(!empty($temp_robot->robot_core) ? ucfirst($temp_robot->robot_core).' Core' : 'Neutral Core').' | '.ucfirst($temp_robot->robot_position).' Position';
        // Display the robot's item if it exists
        if (!empty($temp_robot->robot_item) && !empty($temp_abilities_index[$temp_robot->robot_item])){ $temp_robot_title .= ' | + '.$temp_abilities_index[$temp_robot->robot_item]['ability_name'].' '; }
        // Display the robot's life and weapon energy current and base
        $temp_robot_title .= ' <br />'.$temp_robot->robot_energy.' / '.$temp_robot->robot_base_energy.' LE';
        $temp_robot_title .= ' | '.$temp_robot->robot_weapons.' / '.$temp_robot->robot_base_weapons.' WE';
        $temp_required_experience = mmrpg_prototype_calculate_experience_required($temp_robot->robot_level);
        if ($robot_direction == 'right' && $temp_robot->robot_class != 'mecha'){
          $temp_robot_title .= ' | '.$temp_robot->robot_experience.' / '.$temp_required_experience.' EXP';
        } elseif ($temp_robot->robot_class == 'mecha'){
          $temp_generation = '1st';
          if (preg_match('/-2$/', $temp_robot->robot_token)){ $temp_generation = '2nd'; }
          elseif (preg_match('/-3$/', $temp_robot->robot_token)){ $temp_generation = '3rd'; }
          $temp_robot_title .= ' | '.$temp_generation.' Gen';
        }
        $temp_robot_title .= ' <br />'.$temp_robot->robot_attack.' / '.$temp_robot->robot_base_attack.' AT';
        $temp_robot_title .= ' | '.$temp_robot->robot_defense.' / '.$temp_robot->robot_base_defense.' DF';
        $temp_robot_title .= ' | '.$temp_robot->robot_speed.' / '.$temp_robot->robot_base_speed.' SP';
        $temp_robot_title_plain = strip_tags(str_replace('<br />', '&#10;', $temp_robot_title));
        $temp_robot_title_tooltip = htmlentities($temp_robot_title, ENT_QUOTES, 'UTF-8');

        // Collect the robot's core types for display
        $temp_robot_core_type = !empty($temp_robot->robot_core) ? $temp_robot->robot_core : 'none';
        $temp_robot_core2_type = !empty($temp_robot->robot_core2) ? $temp_robot->robot_core2 : '';
        if (!empty($temp_robot->robot_item) && preg_match('/^item-core-/', $temp_robot->robot_item)){
          $temp_item_core_type = preg_replace('/^item-core-/', '', $temp_robot->robot_item);
          if (empty($temp_robot_core2_type) && $temp_robot_core_type != $temp_item_core_type){ $temp_robot_core2_type = $temp_item_core_type; }
        }

        // Define the robot button text variables
        $temp_robot_label = '<span class="multi">';
        $temp_robot_label .= '<span class="maintext">'.$temp_robot->robot_name.'</span>';
        $temp_robot_label .= '<span class="subtext">';
          $temp_robot_label .= $temp_robot->robot_energy.'/'.$temp_robot->robot_base_energy.' Energy';
        $temp_robot_label .= '</span>';
        $temp_robot_label .= '<span class="subtext">';
          $temp_robot_label .= 'A:'.$temp_robot->robot_attack;
          $temp_robot_label .= '&nbsp;';
          $temp_robot_label .= 'D:'.$temp_robot->robot_defense;
          $temp_robot_label .= '&nbsp;';
          $temp_robot_label .= 'S:'.$temp_robot->robot_speed;
        $temp_robot_label .= '</span>';
        $temp_robot_label .= '</span>';

        // Define the robot sprite variables
        $temp_robot_sprite = array();
        $temp_robot_sprite['name'] = $temp_robot->robot_name;
        $temp_robot_sprite['core'] = !empty($temp_robot->robot_core) ? $temp_robot->robot_core : 'none';
        $temp_robot_sprite['image'] = $temp_robot->robot_image;
        $temp_robot_sprite['image_size'] = $temp_robot->robot_image_size;
        $temp_robot_sprite['image_size_text'] = $temp_robot_sprite['image_size'].'x'.$temp_robot_sprite['image_size'];
        $temp_robot_sprite['image_size_zoom'] = $temp_robot->robot_image_size * 2;
        $temp_robot_sprite['image_size_zoom_text'] = $temp_robot_sprite['image_size'].'x'.$temp_robot_sprite['image_size'];
        $temp_robot_sprite['url'] = 'images/robots/'.$temp_robot_sprite['image'].'/sprite_'.$robot_direction.'_'.$temp_robot_sprite['image_size_text'].'.png';
        $temp_robot_sprite['preload'] = 'images/robots/'.$temp_robot_sprite['image'].'/sprite_'.$robot_direction.'_'.$temp_robot_sprite['image_size_zoom_text'].'.png';
        $temp_robot_sprite['class'] = 'sprite size'.$temp_robot_sprite['image_size'].' '.($temp_robot->robot_energy > 0 ? ($temp_robot->robot_energy > ($temp_robot->robot_base_energy/2) ? 'base' : 'defend') : 'defeat').' ';
        $temp_robot_sprite['style'] = 'background-image: url('.$temp_robot_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.');  top: 6px; left: 5px; ';
        if ($temp_robot->robot_position == 'active'){ $temp_robot_sprite['style'] .= 'border-color: #ababab; '; }
        $temp_energy_percent = ceil(($temp_robot->robot_energy / $temp_robot->robot_base_energy) * 100);
        if ($temp_energy_percent > 50){ $temp_robot_sprite['class'] .= 'energy high ';  }
        elseif ($temp_energy_percent > 25){ $temp_robot_sprite['class'] .= 'energy medium ';  }
        elseif ($temp_energy_percent > 0){ $temp_robot_sprite['class'] .= 'energy low '; }
        $temp_robot_sprite['markup'] = '<span class="'.$temp_robot_sprite['class'].'" style="'.$temp_robot_sprite['style'].'">'.$temp_robot_sprite['name'].'</span>';
        // Update the order button if necessary
        $order_button_markup = $allow_button ? 'data-order="'.$temp_order_counter.'"' : '';
        $temp_order_counter += $allow_button ? 1 : 0;
        // Now use the new object to generate a snapshot of this target button
        /*?><a <?=$order_button_markup?> title="<?=$temp_robot_title_plain?>" data-tooltip="<?=$temp_robot_title_tooltip?>" class="button <?= !$allow_button ? 'button_disabled' : '' ?> action_target target_<?= $temp_robot->robot_token ?> status_<?= $temp_robot->robot_status ?> robot_type robot_type_<?= !empty($temp_robot->robot_core) ? $temp_robot->robot_core : 'none' ?> block_<?= $robot_key + 1 ?>" type="button" <?if($allow_button):?>data-action="target_<?= $temp_robot->robot_id.'_'.$temp_robot->robot_token ?>"<?endif;?> data-preload="<?= $temp_robot_sprite['preload'] ?>"><label><?= $temp_robot_sprite['markup'] ?><?= $temp_robot_label ?></label></a><?*/
        ?><a <?=$order_button_markup?> data-tooltip="<?=$temp_robot_title_tooltip?>" class="button <?= !$allow_button ? 'button_disabled' : '' ?> action_target target_<?= $temp_robot->robot_token ?> status_<?= $temp_robot->robot_status ?> robot_type robot_type_<?= $temp_robot_core_type.(!empty($temp_robot_core2_type) ? '_'.$temp_robot_core2_type : '') ?> block_<?= $robot_key + 1 ?>" type="button" <?if($allow_button):?>data-action="target_<?= $temp_robot->robot_id.'_'.$temp_robot->robot_token ?>"<?endif;?> data-preload="<?= $temp_robot_sprite['preload'] ?>"><label><?= $temp_robot_sprite['markup'] ?><?= $temp_robot_label ?></label></a><?
      }
    }
    // If there were less than 8 robots, fill in the empty spaces
    if ($num_robots < 8){
      for ($i = $num_robots; $i < 8; $i++){
        // Display an empty button placeholder
        ?><a class="button action_target button_disabled block_<?= $i + 1 ?>" type="button">&nbsp;</a><?
      }
    }
  }
  // End the main action container tag
  ?></div><?
  // Display the back button by default
  ?><div class="sub_actions"><a data-order="<?=$temp_order_counter?>" class="button action_back" type="button" data-panel="ability"><label>Back</label></a></div><?
  // Increment the order counter
  $temp_order_counter++;
$actions_markup['target_target'] = trim(ob_get_clean());
$actions_markup['target_target'] = preg_replace('#\s+#', ' ', $actions_markup['target_target']);
?>