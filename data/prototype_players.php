<?

/*
 * DEMO PLAYER SELECT
 */
if (!empty($_SESSION[$session_token]['DEMO'])){
  /*
  // Print out the demo mode's player select screen for Dr. Light
  $text_robots_unlocked = $prototype_data['demo']['robots_unlocked'].' Robot'.($prototype_data['demo']['robots_unlocked'] != 1 ? 's' : '');
  $text_points_unlocked = number_format($prototype_data['demo']['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['demo']['points_unlocked'] != 1 ? 's' : '');
  $text_player_special = $prototype_data['demo']['demo_complete'] ? true : false;
  echo '<a class="option option_1x4 option_this-player-select option_this-dr-light-player-select option_dr-light block_1" data-token="dr-light">';
  echo '<div class="platform"><div class="chrome"><div class="inset"><label class="has_image"><span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url(images/players/dr-light/sprite_right_40x40.png); top: -2px; right: 14px;">Dr. Light</span><span class="multi"><span class="maintext">Dr. Light'.(!empty($text_player_special) ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! :D">&hearts;</span>' : '').'</span><span class="subtext">'.$text_robots_unlocked.'</span><span class="subtext2">'.$text_points_unlocked.'</span></span><span class="arrow">&#9658;</span></label></div></div></div>';
  echo '</a>'."\n";
  */

  // Define the button size based on player count
  $this_button_size = '1x4';

  // Print out the normal mode's player select screen for Dr. Light
  if ($unlock_flag_light){
    $text_robots_unlocked = $prototype_data['demo']['robots_unlocked'].' Robot'.($prototype_data['demo']['robots_unlocked'] != 1 ? 's' : '');
    $text_points_unlocked = number_format($prototype_data['demo']['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['demo']['points_unlocked'] != 1 ? 's' : '');
    $text_battles_complete = $prototype_data['demo']['battles_complete'].' Mission'.($prototype_data['demo']['battles_complete'] != 1 ? 's' : '');
    $text_player_special = $prototype_data['demo']['battles_complete'] >= 4 ? true : false;
    $text_sprites_markup = '<span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url(images/players/dr-light/sprite_right_40x40.png); top: -2px; right: 14px;">Dr. Light</span>';
    $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites('dr-light', $session_token);
    $text_player_music = 'player-select';
    $text_player_subtext = $text_robots_unlocked;
    if ($prototype_data['demo']['battles_complete'] > 0){ $text_player_subtext .= ' <span class="pipe" style="color: #616161;">|</span> '.($prototype_data['demo']['battles_complete'] == 1 ? '1 Mission' : $prototype_data['demo']['battles_complete'].' Missions'); }
    //if ($ability_counter_light > 0){ $text_player_subtext .= ' <span class="pipe" style="color: #616161;">|</span> '.($ability_counter_light == 1 ? '1 Ability' : $ability_counter_light.' Abilities'); }
    //if ($star_counter_light > 0){ $text_player_subtext .= ' <span class="pipe" style="color: #616161;">|</span> '.($star_counter_light == 1 ? '1 Star' : $star_counter_light.' Stars'); }
    echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_light.'" class="option option_'.$this_button_size.' option_this-player-select option_this-dr-light-player-select option_dr-light block_1" data-token="dr-light">';
    echo '<div class="platform"><div class="chrome"><div class="inset">';
    echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">Dr. Light'.(!empty($text_player_special) ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! :D">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_subtext.'</span><span class="subtext2">'.$text_points_unlocked.'</span></span><span class="arrow">&#9658;</span></label>';
    echo '</div></div></div>';
    echo '</a>'."\n";
  }

}
/*
 * NORMAL PLAYER SELECT
 */
else {
  // Define the button size based on player count
  $this_button_size = '1x4';

  // Print out the normal mode's player select screen for Dr. Light
  if ($unlock_flag_light){
    $text_robots_unlocked = $prototype_data['dr-light']['robots_unlocked'].' Robot'.($prototype_data['dr-light']['robots_unlocked'] != 1 ? 's' : '');
    $text_points_unlocked = number_format($prototype_data['dr-light']['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['dr-light']['points_unlocked'] != 1 ? 's' : '');
    $text_battles_complete = $prototype_data['dr-light']['battles_complete'].' Mission'.($prototype_data['dr-light']['battles_complete'] != 1 ? 's' : '');
    $text_player_special = $prototype_data['dr-light']['prototype_complete'] ? true : false;
    $text_sprites_markup = '<span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url(images/players/dr-light/sprite_right_40x40.png); top: -2px; right: 14px;">Dr. Light</span>';
    $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites('dr-light', $session_token);
    $text_player_music = mmrpg_prototype_get_player_mission_music('dr-light', $session_token);
    $text_player_subtext = $text_robots_unlocked;
    if ($ability_counter_light > 0){ $text_player_subtext .= ' <span class="pipe" style="color: #616161;">|</span> '.($ability_counter_light == 1 ? '1 Ability' : $ability_counter_light.' Abilities'); }
    //if ($star_counter_light > 0){ $text_player_subtext .= ' <span class="pipe" style="color: #616161;">|</span> '.($star_counter_light == 1 ? '1 Star' : $star_counter_light.' Stars'); }
    echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_light.'" class="option option_'.$this_button_size.' option_this-player-select option_this-dr-light-player-select option_dr-light block_1" data-token="dr-light">';
    echo '<div class="platform"><div class="chrome"><div class="inset">';
    echo '<label class="has_image"><span class="multi">'.$text_sprites_markup.'<span class="maintext">Dr. Light'.(!empty($text_player_special) ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! :D">&hearts;</span>' : '').'</span><span class="subtext">'.$text_player_subtext.'</span><span class="subtext2">'.$text_points_unlocked.'</span></span><span class="arrow">&#9658;</span></label>';
    echo '</div></div></div>';
    echo '</a>'."\n";
  }

  // Print out the normal mode's player select screen for Dr. Wily
  if ($unlock_flag_wily){
    $text_robots_unlocked = $prototype_data['dr-wily']['robots_unlocked'].' Robot'.($prototype_data['dr-wily']['robots_unlocked'] != 1 ? 's' : '');
    $text_points_unlocked = number_format($prototype_data['dr-wily']['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['dr-wily']['points_unlocked'] != 1 ? 's' : '');
    $text_battles_complete = $prototype_data['dr-wily']['battles_complete'].' Mission'.($prototype_data['dr-light']['battles_complete'] != 1 ? 's' : '');
    $text_player_special = $prototype_data['dr-wily']['prototype_complete'] ? true : false;
    $text_sprites_markup = '<span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url(images/players/dr-wily/sprite_right_40x40.png); top: -2px; right: 14px;">Dr. Wily</span>';
    $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites('dr-wily', $session_token);
    $text_player_music = mmrpg_prototype_get_player_mission_music('dr-wily', $session_token);
    $text_player_subtext = $text_robots_unlocked;
    if ($ability_counter_wily > 0){ $text_player_subtext .= ' <span class="pipe" style="color: #616161;">|</span> '.($ability_counter_wily == 1 ? '1 Ability' : $ability_counter_wily.' Abilities'); }
    //if ($star_counter_wily > 0){ $text_player_subtext .= ' <span class="pipe" style="color: #616161;">|</span> '.($star_counter_wily == 1 ? '1 Star' : $star_counter_wily.' Stars'); }
    echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_wily.'" class="option option_'.$this_button_size.' option_this-player-select option_this-dr-wily-player-select option_dr-wily block_1" data-token="dr-wily">';
    echo '<div class="platform"><div class="chrome"><div class="inset">';
    echo '<label class="has_image">'.$text_sprites_markup.'<span class="multi"><span class="maintext">Dr. Wily'.(!empty($text_player_special) ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! >:D">&clubs;</span>' : '').'</span><span class="subtext">'.$text_player_subtext.'</span><span class="subtext2">'.$text_points_unlocked.'</span></span><span class="arrow">&#9658;</span></label>';
    echo '</div></div></div>';
    echo '</a>'."\n";
  }

  // Print out the normal mode's player select screen for Dr. Cossack
  if ($unlock_flag_cossack){
    $text_robots_unlocked = $prototype_data['dr-cossack']['robots_unlocked'].' Robot'.($prototype_data['dr-cossack']['robots_unlocked'] != 1 ? 's' : '');
    $text_points_unlocked = number_format($prototype_data['dr-cossack']['points_unlocked'], 0, '.', ',').' Point'.($prototype_data['dr-cossack']['points_unlocked'] != 1 ? 's' : '');
    $text_battles_complete = $prototype_data['dr-cossack']['battles_complete'].' Mission'.($prototype_data['dr-light']['battles_complete'] != 1 ? 's' : '');
    $text_player_special = $prototype_data['dr-cossack']['prototype_complete'] ? true : false;
    $text_sprites_markup = '<span class="sprite sprite_player sprite_40x40 sprite_40x40_base" style="background-image: url(images/players/dr-cossack/sprite_right_40x40.png); top: -2px; right: 14px;">Dr. Cossack</span>';
    $text_sprites_markup .= mmrpg_prototype_get_player_robot_sprites('dr-cossack', $session_token);
    $text_player_music = mmrpg_prototype_get_player_mission_music('dr-cossack', $session_token);
    $text_player_subtext = $text_robots_unlocked;
    if ($ability_counter_cossack > 0){ $text_player_subtext .= ' <span class="pipe" style="color: #616161;">|</span> '.($ability_counter_cossack == 1 ? '1 Ability' : $ability_counter_cossack.' Abilities'); }
    //if ($star_counter_cossack > 0){ $text_player_subtext .= ' <span class="pipe" style="color: #616161;">|</span> '.($star_counter_cossack == 1 ? '1 Star' : $star_counter_cossack.' Stars'); }
    echo '<a data-music-token="'.$text_player_music.'" data-battle-complete="'.$battle_complete_counter_cossack.'" class="option option_'.$this_button_size.' option_this-player-select option_this-dr-cossack-player-select option_dr-cossack block_1" data-token="dr-cossack">';
    echo '<div class="platform"><div class="chrome"><div class="inset">';
    echo '<label class="has_image">'.$text_sprites_markup.'<span class="multi"><span class="maintext">Dr. Cossack'.(!empty($text_player_special) ? ' <span style="position: relative; bottom: 2px;" title="Thank you for playing!!! >:D">&diams;</span>' : '').'</span><span class="subtext">'.$text_player_subtext.'</span><span class="subtext2">'.$text_points_unlocked.'</span></span><span class="arrow">&#9658;</span></label>';
    echo '</div></div></div>';
    echo '</a>'."\n";
  }

}

?>