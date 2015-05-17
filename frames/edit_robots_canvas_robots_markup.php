<?

// Start the output buffer
ob_start();

// Loop through the allowed edit data for all players
$key_counter = 0;
$player_counter = 0;
$player_keys = array_keys($allowed_edit_data);
foreach($allowed_edit_data AS $player_token => $player_info){
  $player_counter++;
  $player_colour = 'energy';
  if (!empty($player_info['player_attack'])){ $player_colour = 'attack'; }
  elseif (!empty($player_info['player_defense'])){ $player_colour = 'defense'; }
  elseif (!empty($player_info['player_speed'])){ $player_colour = 'speed'; }
echo '<td style="width: '.floor(100 / $allowed_edit_player_count).'%;">'."\n";
  echo '<div class="wrapper wrapper_'.($player_counter % 2 != 0 ? 'left' : 'right').' wrapper_'.$player_token.'" data-select="robots" data-player="'.$player_info['player_token'].'">'."\n";
    echo '<div class="wrapper_header player_type player_type_'.$player_colour.'">'.$player_info['player_name'].' <span class="count">'.count($player_info['player_robots']).'</span></div>';
    echo '<div class="wrapper_overflow">';
      foreach ($player_info['player_robots'] AS $robot_token => $robot_info){
        $robot_key = $key_counter;
        $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
        $temp_robot_rewards = array();

        if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])){
          $temp_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token];
        }

        foreach ($player_keys AS $this_player_key){
          if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$this_player_key]['player_robots'][$robot_token])){
            $temp_array = $_SESSION[$session_token]['values']['battle_rewards'][$this_player_key]['player_robots'][$robot_token];
            $temp_robot_rewards = array_merge($temp_robot_rewards, $temp_array);
          }
        }

        if (!empty($temp_robot_rewards) && $global_allow_editing){
          $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token] = $temp_robot_rewards;
        }

        //$temp_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token];
        $robot_info['robot_level'] = !empty($temp_robot_rewards['robot_level']) ? $temp_robot_rewards['robot_level'] : 1;
        $robot_info['robot_experience'] = !empty($temp_robot_rewards['robot_experience']) ? $temp_robot_rewards['robot_experience'] : 0;
        if ($robot_info['robot_level'] >= 100){ $robot_info['robot_experience'] = '&#8734;'; }
        $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
        $robot_image_offset_x = -6 - $robot_image_offset;
        $robot_image_offset_y = -10 - $robot_image_offset;
        echo '<a data-number="'.$robot_info['robot_number'].'" data-level="'.$robot_info['robot_level'].'" data-token="'.$player_info['player_token'].'_'.$robot_info['robot_token'].'" data-robot="'.$robot_info['robot_token'].'" data-player="'.$player_info['player_token'].'" title="'.$robot_info['robot_name'].'" data-tooltip="'.$robot_info['robot_name'].' ('.(!empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']).' Core' : 'Neutral Core').') &lt;br /&gt;Lv '.$robot_info['robot_level'].' | '.$robot_info['robot_experience'].' Exp" style="background-image: url(i/r/'.(!empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token']).'/mr'.$robot_info['robot_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: '.$robot_image_offset_x.'px '.$robot_image_offset_y.'px;" class="sprite sprite_robot sprite_robot_'.$player_token.' sprite_robot_sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot robot_status_active robot_position_active '.($robot_key == 0 ? 'sprite_robot_current sprite_robot_'.$player_token.'_current ' : '').' robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').'">'.$robot_info['robot_name'].'</a>'."\n";
        $key_counter++;
      }
    echo '</div>'."\n";
    if ($global_allow_editing){
      ?>
      <div class="sort_wrapper">
        <label class="label">sort</label>
        <a class="sort sort_level" data-sort="level" data-order="asc" data-player="<?= $player_info['player_token'] ?>">level</a>
        <a class="sort sort_number" data-sort="number" data-order="asc" data-player="<?= $player_info['player_token'] ?>">number</a>
        <a class="sort sort_core" data-sort="core" data-order="asc" data-player="<?= $player_info['player_token'] ?>">core</a>
      </div>
      <?
    }
  echo '</div>'."\n";
echo '</td>'."\n";
}

// Collect the contents of the buffer
$edit_canvas_markup = ob_get_clean();
$edit_canvas_markup = preg_replace('/\s+/', ' ', trim($edit_canvas_markup));
exit($edit_canvas_markup);

?>