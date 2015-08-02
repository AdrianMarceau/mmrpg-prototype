<?
// Define the console markup string
$this_markup = '';

// If this robot was not provided or allowed by the function
if (empty($eventinfo['this_player']) || empty($eventinfo['this_robot']) || $options['canvas_show_this'] == false){
  // Set both this player and robot to false
  $eventinfo['this_player'] = false;
  $eventinfo['this_robot'] = false;
  // Collect the target player ID if set
  $target_player_id = !empty($eventinfo['target_player']) ? $eventinfo['target_player']->player_id : false;
  // Loop through the players index looking for this player
  foreach ($this->values['players'] AS $this_player_id => $this_playerinfo){
    if (empty($target_player_id) || $target_player_id != $this_player_id){
      $eventinfo['this_player'] = new mmrpg_player($this, $this_playerinfo);
      break;
    }
  }
  // Now loop through this player's robots looking for an active one
  foreach ($eventinfo['this_player']->player_robots AS $this_key => $this_robotinfo){
    if ($this_robotinfo['robot_position'] == 'active' && $this_robotinfo['robot_status'] != 'disabled'){
      $eventinfo['this_robot'] = new mmrpg_robot($this, $eventinfo['this_player'], $this_robotinfo);
      break;
    }
  }
}

// If this robot was targetting itself, set the target to false
if (!empty($eventinfo['this_robot']) && !empty($eventinfo['target_robot'])){
  if ($eventinfo['this_robot']->robot_id == $eventinfo['target_robot']->robot_id
    || ($eventinfo['this_robot']->robot_id < MMRPG_SETTINGS_TARGET_PLAYERID && $eventinfo['target_robot']->robot_id < MMRPG_SETTINGS_TARGET_PLAYERID)
    || ($eventinfo['this_robot']->robot_id >= MMRPG_SETTINGS_TARGET_PLAYERID && $eventinfo['target_robot']->robot_id >= MMRPG_SETTINGS_TARGET_PLAYERID)
    ){
    $eventinfo['target_robot'] = array();
  }
}

// If the target robot was not provided or allowed by the function
if (empty($eventinfo['target_player']) || empty($eventinfo['target_robot']) || $options['canvas_show_target'] == false){
  // Set both this player and robot to false
  $eventinfo['target_player'] = false;
  $eventinfo['target_robot'] = false;
  // Collect this player ID if set
  $this_player_id = !empty($eventinfo['this_player']) ? $eventinfo['this_player']->player_id : false;
  // Loop through the players index looking for this player
  foreach ($this->values['players'] AS $target_player_id => $target_playerinfo){
    if (empty($this_player_id) || $this_player_id != $target_player_id){
      $eventinfo['target_player'] = new mmrpg_player($this, $target_playerinfo);
      break;
    }
  }
  // Now loop through the target player's robots looking for an active one
  foreach ($eventinfo['target_player']->player_robots AS $target_key => $target_robotinfo){
    if ($target_robotinfo['robot_position'] == 'active' && $target_robotinfo['robot_status'] != 'disabled'){
      $eventinfo['target_robot'] = new mmrpg_robot($this, $eventinfo['target_player'], $target_robotinfo);
      break;
    }
  }
}

// Collect this player's markup data
$this_player_data = $eventinfo['this_player']->canvas_markup($options);
// Append this player's markup to the main markup array
$this_markup .= $this_player_data['player_markup'];

// Loop through and display this player's robots
if ($options['canvas_show_this_robots'] && !empty($eventinfo['this_player']->player_robots)){
  $num_player_robots = count($eventinfo['this_player']->player_robots);
  foreach ($eventinfo['this_player']->player_robots AS $this_key => $this_robotinfo){
    $this_robot = new mmrpg_robot($this, $eventinfo['this_player'], $this_robotinfo);
    $this_options = $options;
    //if ($this_robot->robot_status == 'disabled' && $this_robot->robot_position == 'bench'){ continue; }
    if (!empty($this_robot->flags['hidden'])){ continue; }
    elseif (!empty($eventinfo['this_robot']->robot_id) && $eventinfo['this_robot']->robot_id != $this_robot->robot_id){ $this_options['this_ability'] = false; }
    elseif (!empty($eventinfo['this_robot']->robot_id) && $eventinfo['this_robot']->robot_id == $this_robot->robot_id && $options['canvas_show_this'] != false){ $this_robot->robot_frame =  $eventinfo['this_robot']->robot_frame; }
    $this_robot->robot_key = $this_robot->robot_key !== false ? $this_robot->robot_key : ($this_key > 0 ? $this_key : $num_player_robots);
    $this_robot_data = $this_robot->canvas_markup($this_options, $this_player_data);
    $this_robot_id_token = $this_robot_data['robot_id'].'_'.$this_robot_data['robot_token'];

    // ABILITY OVERLAY STUFF
    if (!empty($this_options['this_ability_results']) && $this_options['this_ability_target'] == $this_robot_id_token){
      $this_markup .= '<div class="ability_overlay overlay1" data-target="'.$this_options['this_ability_target'].'" data-key="'.$this_robot_data['robot_key'].'" style="z-index: '.(($this_robot_data['robot_position'] == 'active' ? 5052 : (4900 - ($this_robot_data['robot_key'] * 100)))).';">&nbsp;</div>';
    }
    elseif ($this_robot_data['robot_position'] != 'bench' && !empty($this_options['this_ability']) && !empty($options['canvas_show_this_ability'])){
      $this_markup .= '<div class="ability_overlay overlay2" data-target="'.$this_options['this_ability_target'].'" data-key="'.$this_robot_data['robot_key'].'" style="z-index: '.(($this_options['this_ability_target_position'] == 'active' ? 5051 : (4900 - ($this_options['this_ability_target_key'] * 100)))).';">&nbsp;</div>';
    }
    elseif ($this_robot_data['robot_position'] != 'bench' && !empty($options['canvas_show_this_ability_overlay'])){
      $this_markup .= '<div class="ability_overlay overlay3" style="z-index: 100;">&nbsp;</div>';
    }

    // RESULTS ANIMATION STUFF
    if (!empty($this_options['this_ability_results']) && $this_options['this_ability_target'] == $this_robot_id_token){
      /*
       * ABILITY EFFECT OFFSETS
       * Frame 01 : Energy +
       * Frame 02 : Energy -
       * Frame 03 : Attack +
       * Frame 04 : Attack -
       * Frame 05 : Defense +
       * Frame 06 : Defense -
       * Frame 07 : Speed +
       * Frame 08 : Speed -
       */

      // Define the results data array and populate with basic fields
      $this_results_data = array();
      $this_results_data['results_amount_markup'] = '';
      $this_results_data['results_effect_markup'] = '';

      // Calculate the results effect canvas offsets
      $this_results_data['canvas_offset_x'] = ceil($this_robot_data['canvas_offset_x'] - (4 * $this_options['this_ability_results']['total_actions']));
      $this_results_data['canvas_offset_y'] = ceil($this_robot_data['canvas_offset_y'] + 0);
      $this_results_data['canvas_offset_z'] = ceil($this_robot_data['canvas_offset_z'] - 20);
      $temp_size_diff = $this_robot_data['robot_size'] > 80 ? ceil(($this_robot_data['robot_size'] - 80) * 0.5) : 0;
      $this_results_data['canvas_offset_x'] += $temp_size_diff;
      if ($this_robot_data['robot_position'] == 'bench' && $this_robot_data['robot_size'] >= 80){
        $this_results_data['canvas_offset_x'] += ceil($this_robot_data['robot_size'] / 2);
      }


      // Define the style and class variables for these results
      $base_image_size = 40;
      $this_results_data['ability_size'] = $this_robot_data['robot_position'] == 'active' ? ($base_image_size * 2) : $base_image_size;
      $this_results_data['ability_scale'] = isset($this_robot_data['robot_scale']) ? $this_robot_data['robot_scale'] : ($this_robot_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $this_robot_data['robot_key']) / 8) * 0.5));
      $zoom_size = $base_image_size * 2;
      $this_results_data['ability_sprite_size'] = ceil($this_results_data['ability_scale'] * $zoom_size);
      $this_results_data['ability_sprite_width'] = ceil($this_results_data['ability_scale'] * $zoom_size);
      $this_results_data['ability_sprite_height'] = ceil($this_results_data['ability_scale'] * $zoom_size);
      $this_results_data['ability_image_width'] = ceil($this_results_data['ability_scale'] * $zoom_size * 10);
      $this_results_data['ability_image_height'] = ceil($this_results_data['ability_scale'] * $zoom_size);
      $this_results_data['results_amount_class'] = 'sprite ';
      $this_results_data['results_amount_canvas_offset_y'] = $this_robot_data['canvas_offset_y'] + 50;
      $this_results_data['results_amount_canvas_offset_x'] = $this_robot_data['canvas_offset_x'] - 40;
      $this_results_data['results_amount_canvas_offset_z'] = $this_robot_data['canvas_offset_z'] + 100;
      if (!empty($this_options['this_ability_results']['total_actions'])){
        $total_actions = $this_options['this_ability_results']['total_actions'];
        if ($this_options['this_ability_results']['trigger_kind'] == 'damage'){
          $this_results_data['results_amount_canvas_offset_y'] -= ceil((1.5 * $total_actions) * $total_actions);
          $this_results_data['results_amount_canvas_offset_x'] -= $total_actions * 4;
        } elseif ($this_options['this_ability_results']['trigger_kind'] == 'recovery'){
          $this_results_data['results_amount_canvas_offset_y'] = $this_robot_data['canvas_offset_y'] + 20;
          $this_results_data['results_amount_canvas_offset_x'] = $this_robot_data['canvas_offset_x'] - 40;
          $this_results_data['results_amount_canvas_offset_y'] += ceil((1.5 * $total_actions) * $total_actions);
          $this_results_data['results_amount_canvas_offset_x'] -= $total_actions * 4;
        }
      }
      $this_results_data['results_amount_canvas_opacity'] = 1.00;
      if ($this_robot_data['robot_position'] == 'bench'){
        $this_results_data['results_amount_canvas_offset_x'] += 105; //$this_results_data['results_amount_canvas_offset_x'] * -1;
        $this_results_data['results_amount_canvas_offset_y'] += 5; //10;
        $this_results_data['results_amount_canvas_offset_z'] = $this_robot_data['canvas_offset_z'] + 1000;
        $this_results_data['results_amount_canvas_opacity'] -= 0.10;
      } else {
        $this_results_data['canvas_offset_x'] += mt_rand(0, 10); //jitter
        $this_results_data['canvas_offset_y'] += mt_rand(0, 10); //jitter
      }
      $this_results_data['results_amount_style'] = 'bottom: '.$this_results_data['results_amount_canvas_offset_y'].'px; '.$this_robot_data['robot_float'].': '.$this_results_data['results_amount_canvas_offset_x'].'px; z-index: '.$this_results_data['results_amount_canvas_offset_z'].'; opacity: '.$this_results_data['results_amount_canvas_opacity'].'; ';
      $this_results_data['results_effect_class'] = 'sprite sprite_'.$this_results_data['ability_sprite_size'].'x'.$this_results_data['ability_sprite_size'].' ability_status_active ability_position_active '; //sprite_'.$this_robot_data['robot_size'].'x'.$this_robot_data['robot_size'].'
      $this_results_data['results_effect_style'] = 'z-index: '.$this_results_data['canvas_offset_z'].'; '.$this_robot_data['robot_float'].': '.$this_results_data['canvas_offset_x'].'px; bottom: '.$this_results_data['canvas_offset_y'].'px; background-image: url(images/abilities/ability-results/sprite_'.$this_robot_data['robot_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';

      // Ensure a damage/recovery trigger has been sent and actual damage/recovery was done
      if (!empty($this_options['this_ability_results']['this_amount'])
        && in_array($this_options['this_ability_results']['trigger_kind'], array('damage', 'recovery'))){
        // Define the results effect index
        $this_results_data['results_effect_index'] = array();
        // Check if the results effect index was already generated
        if (!empty($this->index['results_effects'])){
          // Collect the results effect index from the battle index
          $this_results_data['results_effect_index'] = $this->index['results_effects'];
        }
        // Otherwise, generate the results effect index
        else {
          // Define the results effect index for quick programatic lookups
          $this_results_data['results_effect_index']['recovery']['energy'] = '00';
          $this_results_data['results_effect_index']['damage']['energy'] = '01';
          $this_results_data['results_effect_index']['recovery']['attack'] = '02';
          $this_results_data['results_effect_index']['damage']['attack'] = '03';
          $this_results_data['results_effect_index']['recovery']['defense'] = '04';
          $this_results_data['results_effect_index']['damage']['defense'] = '05';
          $this_results_data['results_effect_index']['recovery']['speed'] = '06';
          $this_results_data['results_effect_index']['damage']['speed'] = '07';
          $this_results_data['results_effect_index']['recovery']['weapons'] = '04';
          $this_results_data['results_effect_index']['damage']['weapons'] = '05';
          $this_results_data['results_effect_index']['recovery']['experience'] = '10';
          $this_results_data['results_effect_index']['damage']['experience'] = '10';
          $this_results_data['results_effect_index']['recovery']['level'] = '10';
          $this_results_data['results_effect_index']['damage']['level'] = '10';
          $this->index['results_effects'] = $this_results_data['results_effect_index'];
        }


        // Check if a damage trigger was sent with the ability results
        if ($this_options['this_ability_results']['trigger_kind'] == 'damage'){
          // Append the ability damage kind to the class
          $temp_smalltext_class = strlen($this_options['this_ability_results']['this_amount']) > 4 ? 'ability_damage_smalltext' : '';
          $this_results_data['results_amount_class'] .= 'ability_damage '.$temp_smalltext_class.' ability_damage_'.$this_options['this_ability_results']['damage_kind'].' ';
          if (!empty($this_options['this_ability_results']['flag_resistance'])){ $this_results_data['results_amount_class'] .= 'ability_damage_'.$this_options['this_ability_results']['damage_kind'].'_low '; }
          elseif (!empty($this_options['this_ability_results']['flag_weakness']) || !empty($this_options['this_ability_results']['flag_critical'])){ $this_results_data['results_amount_class'] .= 'ability_damage_'.$this_options['this_ability_results']['damage_kind'].'_high '; }
          else { $this_results_data['results_amount_class'] .= 'ability_damage_'.$this_options['this_ability_results']['damage_kind'].'_base '; }
          $frame_number = $this_results_data['results_effect_index']['damage'][$this_options['this_ability_results']['damage_kind']];
          $frame_int = (int)$frame_number;
          $frame_offset = $frame_int > 0 ? '-'.($frame_int * $this_results_data['ability_sprite_size']) : 0;
          $frame_position = $frame_int;
          if ($frame_position === false){ $frame_position = 0; }
          $frame_background_offset = -1 * ceil(($this_results_data['ability_sprite_size'] * $frame_position));
          $this_results_data['results_effect_class'] .= 'sprite_'.$this_results_data['ability_sprite_size'].'x'.$this_results_data['ability_sprite_size'].'_'.$frame_number.' ';
          $this_results_data['results_effect_style'] .= 'width: '.$this_results_data['ability_sprite_size'].'px; height: '.$this_results_data['ability_sprite_size'].'px; background-size: '.$this_results_data['ability_image_width'].'px '.$this_results_data['ability_image_height'].'px; background-position: '.$frame_background_offset.'px 0; ';
          // Append the final damage results markup to the markup array
          $this_results_data['results_amount_markup'] .= '<div class="'.$this_results_data['results_amount_class'].'" style="'.$this_results_data['results_amount_style'].'">-'.$this_options['this_ability_results']['this_amount'].'</div>';
          $this_results_data['results_effect_markup'] .= '<div class="'.$this_results_data['results_effect_class'].'" style="'.$this_results_data['results_effect_style'].'">-'.$this_options['this_ability_results']['damage_kind'].'</div>';

        }
        // Check if a recovery trigger was sent with the ability results
        elseif ($this_options['this_ability_results']['trigger_kind'] == 'recovery'){
          // Append the ability recovery kind to the class
          $temp_smalltext_class = strlen($this_options['this_ability_results']['this_amount']) > 4 ? 'ability_recovery_smalltext' : '';
          $this_results_data['results_amount_class'] .= 'ability_recovery '.$temp_smalltext_class.' ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].' ';
          if (!empty($this_options['this_ability_results']['flag_resistance'])){ $this_results_data['results_amount_class'] .= 'ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].'_low '; }
          elseif (!empty($this_options['this_ability_results']['flag_affinity']) || !empty($this_options['this_ability_results']['flag_critical'])){ $this_results_data['results_amount_class'] .= 'ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].'_high '; }
          else { $this_results_data['results_amount_class'] .= 'ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].'_base '; }
          $frame_number = $this_results_data['results_effect_index']['recovery'][$this_options['this_ability_results']['recovery_kind']];
          $frame_int = (int)$frame_number;
          $frame_offset = $frame_int > 0 ? '-'.($frame_int * $this_results_data['ability_size']) : 0;
          $frame_position = $frame_int;
          if ($frame_position === false){ $frame_position = 0; }
          $frame_background_offset = -1 * ceil(($this_results_data['ability_sprite_size'] * $frame_position));
          $this_results_data['results_effect_class'] .= 'sprite_'.$this_results_data['ability_sprite_size'].'x'.$this_results_data['ability_sprite_size'].'_'.$frame_number.' ';
          $this_results_data['results_effect_style'] .= 'width: '.$this_results_data['ability_sprite_size'].'px; height: '.$this_results_data['ability_sprite_size'].'px; background-size: '.$this_results_data['ability_image_width'].'px '.$this_results_data['ability_image_height'].'px; background-position: '.$frame_background_offset.'px 0; ';
          // Append the final recovery results markup to the markup array
          $this_results_data['results_amount_markup'] .= '<div class="'.$this_results_data['results_amount_class'].'" style="'.$this_results_data['results_amount_style'].'">+'.$this_options['this_ability_results']['this_amount'].'</div>';
          $this_results_data['results_effect_markup'] .= '<div class="'.$this_results_data['results_effect_class'].'" style="'.$this_results_data['results_effect_style'].'">+'.$this_options['this_ability_results']['recovery_kind'].'</div>';

        }

      }

      // Append this result's markup to the main markup array
      $this_markup .= $this_results_data['results_amount_markup'];
      $this_markup .= $this_results_data['results_effect_markup'];

    }

    // ATTACHMENT ANIMATION STUFF
    if (empty($this_robot->flags['apply_disabled_state']) && !empty($this_robot->robot_attachments)){

      // Loop through each attachment and process it
      foreach ($this_robot->robot_attachments AS $attachment_token => $attachment_info){
        // If this is an ability attachment
        if ($attachment_info['class'] == 'ability'){
          // Create the temporary ability object using the provided data and generate its markup data
          $this_ability = new mmrpg_ability($this, $eventinfo['this_player'], $this_robot, $attachment_info);
          // Define this ability data array and generate the markup data
          $this_attachment_options = $this_options;
          $this_attachment_options['sticky'] = isset($attachment_info['sticky']) ? $attachment_info['sticky'] : false;
          $this_attachment_options['data_sticky'] = $this_attachment_options['sticky'];
          $this_attachment_options['data_type'] = 'attachment';
          $this_attachment_options['data_debug'] = ''; //$attachment_token;
          $this_attachment_options['ability_image'] = isset($attachment_info['ability_image']) ? $attachment_info['ability_image'] : $this_ability->ability_image;
          $this_attachment_options['ability_frame'] = isset($attachment_info['ability_frame']) ? $attachment_info['ability_frame'] : $this_ability->ability_frame;
          $this_attachment_options['ability_frame_span'] = isset($attachment_info['ability_frame_span']) ? $attachment_info['ability_frame_span'] : $this_ability->ability_frame_span;
          $this_attachment_options['ability_frame_animate'] = isset($attachment_info['ability_frame_animate']) ? $attachment_info['ability_frame_animate'] : $this_ability->ability_frame_animate;
          $attachment_frame_count = !empty($this_attachment_options['ability_frame_animate']) ? sizeof($this_attachment_options['ability_frame_animate']) : sizeof($this_attachment_options['ability_frame']);
          $temp_event_frame = $this->counters['event_frames'];
          if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
          elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
          elseif ($temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
          if (isset($this_attachment_options['ability_frame_animate'][$attachment_frame_key])){ $this_attachment_options['ability_frame'] = $this_attachment_options['ability_frame_animate'][$attachment_frame_key]; }
          $this_attachment_options['ability_frame_offset'] = isset($attachment_info['ability_frame_offset']) ? $attachment_info['ability_frame_offset'] : $this_ability->ability_frame_offset;
          $this_attachment_options['ability_frame_styles'] = isset($attachment_info['ability_frame_styles']) ? $attachment_info['ability_frame_styles'] : $this_ability->ability_frame_styles;
          $this_attachment_options['ability_frame_classes'] = isset($attachment_info['ability_frame_classes']) ? $attachment_info['ability_frame_classes'] : $this_ability->ability_frame_classes;
          $this_ability_data = $this_ability->canvas_markup($this_attachment_options, $this_player_data, $this_robot_data);
          // Append this ability's markup to the main markup array
          if (!preg_match('/display:\s?none;/i', $this_robot->robot_frame_styles)){
            $this_markup .= $this_ability_data['ability_markup'];
          }
        }

      }

    }

    // ABILITY ANIMATION STUFF
    if (/*true //$this_robot_data['robot_id'] == $this_options['this_ability_target']
      && $this_robot_data['robot_position'] != 'bench'
      &&*/ !empty($this_options['this_ability'])
      && !empty($options['canvas_show_this_ability'])){
      // Define the ability data array and generate markup data
      $attachment_options['data_type'] = 'ability';
      $this_ability_data = $this_options['this_ability']->canvas_markup($this_options, $this_player_data, $this_robot_data);

      // Display the ability's mugshot sprite
      if (empty($this_options['this_ability_results']['total_actions'])){
        $this_mugshot_markup_left = '<div class="sprite ability_icon ability_icon_left" style="background-image: url(images/abilities/'.(!empty($this_options['this_ability']->ability_image) ? $this_options['this_ability']->ability_image : $this_options['this_ability']->ability_token).'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');"></div>';
        $this_mugshot_markup_right = '<div class="sprite ability_icon ability_icon_right" style="background-image: url(images/abilities/'.(!empty($this_options['this_ability']->ability_image) ? $this_options['this_ability']->ability_image : $this_options['this_ability']->ability_token).'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');"></div>';
        if (!empty($eventinfo['this_robot']) && !empty($eventinfo['target_robot']) && ($eventinfo['this_robot']->robot_id != $eventinfo['target_robot']->robot_id)){

          // Check to make sure starforce is enabled right now
          $temp_starforce_enabled = true;
          if (!empty($eventinfo['this_player']->counters['dark_elements'])){ $temp_starforce_enabled = false; }
          if (!empty($eventinfo['target_player']->counters['dark_elements'])){ $temp_starforce_enabled = false; }

          // Collect the attack value from this robot
          $temp_attack_value = $eventinfo['this_robot']->robot_attack;
          $temp_attack_markup = $temp_attack_value.' AT';

          // If this player has starforce, increase the attack amount appropriately
          if ($temp_starforce_enabled && !empty($eventinfo['this_player']->player_starforce)){

            // Check to ensure this ability actually has a type before proceeding
            if (!empty($this_options['this_ability']->ability_type)){
              // Define the boost value and start at zero
              $temp_boost_value = 0;
              // If the player has a matching starforce amount, add the value
              if (!empty($eventinfo['this_player']->player_starforce[$this_options['this_ability']->ability_type])){
                // Collect the force value for the first ability type
                $temp_force_value = $eventinfo['this_player']->player_starforce[$this_options['this_ability']->ability_type];
                // Increase the attack with the value times the boost constant
                $temp_boost_value = $temp_force_value * MMRPG_SETTINGS_STARS_ATTACKBOOST;
                $temp_attack_value += $temp_boost_value;
              }
              // And if the ability has a second type, process that too
              if (!empty($this_options['this_ability']->ability_type2)){
                // If the player has a matching starforce amount, add the value
                if (!empty($eventinfo['this_player']->player_starforce[$this_options['this_ability']->ability_type2])){
                  // Collect the force value for the second ability type
                  $temp_force_value = $eventinfo['this_player']->player_starforce[$this_options['this_ability']->ability_type2];
                  // Increase the attack with the value times the boost constant
                  $temp_boost_value = $temp_force_value * MMRPG_SETTINGS_STARS_ATTACKBOOST;
                  $temp_attack_value += $temp_boost_value;
                }
              }
              // If there was a starforce boost, display it
              if ($temp_boost_value > 0){
                // Append a star to the markup so people know it's boosted
                $temp_attack_markup .= ' +'.$temp_boost_value.'<span class="star">&#9733;</span>';
              }
            }

          }

          // Collect the defense value for the target robot
          $temp_defense_value = $eventinfo['target_robot']->robot_defense;
          $temp_defense_markup = $temp_defense_value.' DF';

          // If the target player has starforce, increase the defense amount appropriately
          if ($temp_starforce_enabled && !empty($eventinfo['target_player']->player_starforce)){

            // Check to ensure this ability actually has a type before proceeding
            if (!empty($this_options['this_ability']->ability_type)){
              // Define the boost value and start at zero
              $temp_boost_value = 0;
              // If the player has a matching starforce amount, add the value
              if (!empty($eventinfo['target_player']->player_starforce[$this_options['this_ability']->ability_type])){
                // Collect the force value for the first ability type
                $temp_force_value = $eventinfo['target_player']->player_starforce[$this_options['this_ability']->ability_type];
                // Increase the defense with the value times the boost constant
                $temp_boost_value = $temp_force_value * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
                $temp_defense_value += $temp_boost_value;
              }
              // And if the ability has a second type, process that too
              if (!empty($this_options['this_ability']->ability_type2)){
                // If the player has a matching starforce amount, add the value
                if (!empty($eventinfo['target_player']->player_starforce[$this_options['this_ability']->ability_type2])){
                  // Collect the force value for the second ability type
                  $temp_force_value = $eventinfo['target_player']->player_starforce[$this_options['this_ability']->ability_type2];
                  // Increase the defense with the value times the boost constant
                  $temp_boost_value = $temp_force_value * MMRPG_SETTINGS_STARS_DEFENSEBOOST;
                  $temp_defense_value += $temp_boost_value;
                }
              }
              // If there was a starforce boost, display it
              if ($temp_boost_value > 0){
                // Append a star to the markup so people know it's boosted
                $temp_defense_markup .= ' +'.$temp_boost_value.'<span class="star">&#9733;</span>';
              }
            }

          }

          // Position the attack and defense values to right/left depending on player side
          if ($eventinfo['this_player']->player_side == 'left'){
            $this_stat_markup_left = '<span class="robot_stat robot_stat_left type_attack">'.$temp_attack_markup.'</span>';
            $this_stat_markup_right = '<span class="robot_stat robot_stat_right type_defense">'.$temp_defense_markup.'</span>';
          } elseif ($eventinfo['this_player']->player_side == 'right'){
            $this_stat_markup_left = '<span class="robot_stat robot_stat_left type_defense">'.$temp_defense_markup.'</span>';
            $this_stat_markup_right = '<span class="robot_stat robot_stat_right type_attack">'.$temp_attack_markup.'</span>';
          }

          // Always show the attack name and type at this point
          $this_markup .=  '<div class="'.$this_ability_data['ability_markup_class'].' canvas_ability_details ability_type ability_type_'.(!empty($this_options['this_ability']->ability_type) ? $this_options['this_ability']->ability_type : 'none').(!empty($this_options['this_ability']->ability_type2) ? '_'.$this_options['this_ability']->ability_type2 : '').'">'.$this_mugshot_markup_left.'<div class="ability_name" style="">'.$this_ability_data['ability_title'].'</div>'.$this_mugshot_markup_right.'</div>';

          // Only show stat amounts if we're not targetting ourselves
          if ($this_options['canvas_show_ability_stats'] && $eventinfo['this_robot']->robot_id != $this_options['this_ability_results']['trigger_target_id']){
            $this_markup .= '<div class="'.$this_ability_data['ability_markup_class'].' canvas_ability_stats"><div class="wrap">'.$this_stat_markup_left.'<span class="vs">vs</span>'.$this_stat_markup_right.'</div></div>';
          }

        }
      }

      // Append this ability's markup to the main markup array
      $this_markup .= $this_ability_data['ability_markup'];

    }

    // Append this robot's markup to the main markup array
    $this_markup .= $this_robot_data['robot_markup'];

  }
}

// Collect the target player's markup data
$target_player_data = $eventinfo['target_player']->canvas_markup($options);
// Append the target player's markup to the main markup array
$this_markup .= $target_player_data['player_markup'];

// Loop through and display the target player's robots
if ($options['canvas_show_target_robots'] && !empty($eventinfo['target_player']->player_robots)){
  // Count the number of robots on the target's side of the field
  $num_player_robots = count($eventinfo['target_player']->player_robots);

  // Loop through each target robot and generate it's markup
  foreach ($eventinfo['target_player']->player_robots AS $target_key => $target_robotinfo){
    // Create the temporary target robot ovject
    $target_robot = new mmrpg_robot($this, $eventinfo['target_player'], $target_robotinfo);
    $target_options = $options;
    //if ($target_robot->robot_status == 'disabled' && $target_robot->robot_position == 'bench'){ continue; }
    if (!empty($target_robot->flags['hidden'])){ continue; }
    elseif (!empty($eventinfo['target_robot']->robot_id) && $eventinfo['target_robot']->robot_id != $target_robot->robot_id){ $target_options['this_ability'] = false;  }
    elseif (!empty($eventinfo['target_robot']->robot_id) && $eventinfo['target_robot']->robot_id == $target_robot->robot_id && $options['canvas_show_target'] != false){ $target_robot->robot_frame =  $eventinfo['target_robot']->robot_frame; }
    $target_robot->robot_key = $target_robot->robot_key !== false ? $target_robot->robot_key : ($target_key > 0 ? $target_key : $num_player_robots);
    $target_robot_data = $target_robot->canvas_markup($target_options, $target_player_data);

    // ATTACHMENT ANIMATION STUFF
    if (empty($target_robot->flags['apply_disabled_state']) && !empty($target_robot->robot_attachments)){
      // Loop through each attachment and process it
      foreach ($target_robot->robot_attachments AS $attachment_token => $attachment_info){
        // If this is an ability attachment
        if ($attachment_info['class'] == 'ability'){
          // Create the target's temporary ability object using the provided data
          $target_ability = new mmrpg_ability($this, $eventinfo['target_player'], $target_robot, $attachment_info);
          // Define this ability data array and generate the markup data
          $target_attachment_options = $target_options;
          $target_attachment_options['sticky'] = isset($attachment_info['sticky']) ? $attachment_info['sticky'] : false;
          $target_attachment_options['data_sticky'] = $target_attachment_options['sticky'];
          $target_attachment_options['data_type'] = 'attachment';
          $target_attachment_options['data_debug'] = ''; //$attachment_token;
          $target_attachment_options['ability_image'] = isset($attachment_info['ability_image']) ? $attachment_info['ability_image'] : $target_ability->ability_image;
          $target_attachment_options['ability_frame'] = isset($attachment_info['ability_frame']) ? $attachment_info['ability_frame'] : $target_ability->ability_frame;
          $target_attachment_options['ability_frame_span'] = isset($attachment_info['ability_frame_span']) ? $attachment_info['ability_frame_span'] : $target_ability->ability_frame_span;
          $target_attachment_options['ability_frame_animate'] = isset($attachment_info['ability_frame_animate']) ? $attachment_info['ability_frame_animate'] : $target_ability->ability_frame_animate;
          $attachment_frame_key = 0;
          $attachment_frame_count = sizeof($target_attachment_options['ability_frame_animate']);
          $temp_event_frame = $this->counters['event_frames'];
          if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
          elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
          elseif ($attachment_frame_count > 0 && $temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
          if (isset($target_attachment_options['ability_frame_animate'][$attachment_frame_key])){ $target_attachment_options['ability_frame'] = $target_attachment_options['ability_frame_animate'][$attachment_frame_key]; }
          else { $target_attachment_options['ability_frame'] = 0; }
          $target_attachment_options['ability_frame_offset'] = isset($attachment_info['ability_frame_offset']) ? $attachment_info['ability_frame_offset'] : $target_ability->ability_frame_offset;
          $target_attachment_options['ability_frame_styles'] = isset($attachment_info['ability_frame_styles']) ? $attachment_info['ability_frame_styles'] : $target_ability->ability_frame_styles;
          $target_attachment_options['ability_frame_classes'] = isset($attachment_info['ability_frame_classes']) ? $attachment_info['ability_frame_classes'] : $target_ability->ability_frame_classes;
          $target_ability_data = $target_ability->canvas_markup($target_attachment_options, $target_player_data, $target_robot_data);
          // Append this target's ability's markup to the main markup array
          if (!preg_match('/display:\s?none;/i', $target_robot->robot_frame_styles)){
            $this_markup .= $target_ability_data['ability_markup'];
          }
        }

      }

    }

    $this_markup .= $target_robot_data['robot_markup'];

  }

}

// Append the field multipliers to the canvas markup
if (!empty($this->battle_field->field_multipliers)){
  $temp_multipliers = $this->battle_field->field_multipliers;
  asort($temp_multipliers);
  $temp_multipliers = array_reverse($temp_multipliers, true);
  $temp_multipliers_count = count($temp_multipliers);
  $this_special_types = array('experience', 'damage', 'recovery', 'items');
  $multiplier_markup_left = '';
  $multiplier_markup_right = '';
  foreach ($temp_multipliers AS $this_type => $this_multiplier){
    if ($this_type == 'experience' && !empty($_SESSION['GAME']['DEMO'])){ continue; }
    if ($this_multiplier == 1){ continue; }
    if ($this_multiplier < MMRPG_SETTINGS_MULTIPLIER_MIN){ $this_multiplier = MMRPG_SETTINGS_MULTIPLIER_MIN; }
    elseif ($this_multiplier > MMRPG_SETTINGS_MULTIPLIER_MAX){ $this_multiplier = MMRPG_SETTINGS_MULTIPLIER_MAX; }
    $temp_name = $this_type != 'none' ? ucfirst($this_type) : 'Neutral';
    $temp_number = number_format($this_multiplier, 1);
    $temp_title = $temp_name.' x '.$temp_number;
    if ($temp_multipliers_count >= 8){ $temp_name = substr($temp_name, 0, 2); }
    $temp_markup = '<span title="'.$temp_title.'" data-tooltip-align="center" class="field_multiplier field_multiplier_'.$this_type.' field_multiplier_count_'.$temp_multipliers_count.' field_type field_type_'.$this_type.'"><span class="text"><span class="name">'.$temp_name.' </span><span class="cross">x</span><span class="number"> '.$temp_number.'</span></span></span>';
    if (in_array($this_type, $this_special_types)){ $multiplier_markup_left .= $temp_markup; }
    else { $multiplier_markup_right .= $temp_markup; }
  }
  if (!empty($multiplier_markup_left) || !empty($multiplier_markup_right)){
    $this_markup .= '<div class="canvas_overlay_footer"><strong class="overlay_label">Field Multipliers</strong><span class="overlay_multiplier_count_'.$temp_multipliers_count.'">'.$multiplier_markup_left.$multiplier_markup_right.'</div></div>';
  }

}

// If this battle is over, display the mission complete/failed result
if ($this->battle_status == 'complete'){
  if ($this->battle_result == 'victory'){
    $result_text = 'Mission Complete!';
    $result_class = 'nature';
  }
  elseif ($this->battle_result == 'defeat') {
    $result_text = 'Mission Failure&hellip;';
    $result_class = 'flame';
  }
  if (!empty($this_markup) && $this->battle_status == 'complete' || $this->battle_result == 'defeat'){
    $this_mugshot_markup_left = '<div class="sprite ability_icon ability_icon_left">&nbsp;</div>';
    $this_mugshot_markup_right = '<div class="sprite ability_icon ability_icon_right">&nbsp;</div>';
    $this_markup =  '<div class="sprite canvas_ability_details ability_type ability_type_'.$result_class.'">'.$this_mugshot_markup_left.'<div class="ability_name">'.$result_text.'</div>'.$this_mugshot_markup_right.'</div>'.$this_markup;
  }
}
?>