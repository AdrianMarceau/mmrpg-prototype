<?php
// Include the TOP file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
define('MMRPG_REMOTE_SKIP_COMPLETE', true);
define('MMRPG_REMOTE_SKIP_FAILURE', true);
define('MMRPG_REMOTE_SKIP_SETTINGS', true);
define('MMRPG_REMOTE_SKIP_ITEMS', true);
define('MMRPG_REMOTE_SKIP_STARS', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
$session_token = mmrpg_game_token();


// Collect the editor flag if set
$global_allow_editing = isset($_GET['edit']) && $_GET['edit'] == 'false' ? false : true;

// Collect the number of completed battles for each player
$unlock_flag_light = mmrpg_prototype_player_unlocked('dr-light');
$battles_complete_light = $unlock_flag_light ? mmrpg_prototype_battles_complete('dr-light') : 0;
$unlock_flag_wily = mmrpg_prototype_player_unlocked('dr-wily');
$battles_complete_wily = $unlock_flag_wily ? mmrpg_prototype_battles_complete('dr-wily') : 0;
$unlock_flag_cossack = mmrpg_prototype_player_unlocked('dr-cossack');
$battles_complete_cossack = $unlock_flag_cossack ? mmrpg_prototype_battles_complete('dr-cossack') : 0;
$prototype_complete_flag = mmrpg_prototype_complete();

// Count the number of players unlocked
$unlock_count_players = 0;
if ($unlock_flag_light){ $unlock_count_players++; }
if ($unlock_flag_wily){ $unlock_count_players++; }
if ($unlock_flag_cossack){ $unlock_count_players++; }

// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags']['events'])){ $_SESSION[$session_token]['flags']['events'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags']['events'];

// Require the appropriate database files
define('DATA_DATABASE_SHOW_MECHAS', true);
//define('DATA_DATABASE_SHOW_BOSSES', true);
define('DATA_DATABASE_SHOW_CACHE', true);
define('DATA_DATABASE_SHOW_HIDDEN', true);
//require_once('../data/database.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_types.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_players.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_robots.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_mechas.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_bosses.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_abilities.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_fields.php');
//require(MMRPG_CONFIG_ROOTDIR.'data/database_items.php');

// Merge the robots and mechas
$mmrpg_database_robots = array_merge($mmrpg_database_robots, $mmrpg_database_mechas, $mmrpg_database_bosses);

// Preloop through all of the robots in the database session and count the games
$session_robot_database = !empty($_SESSION[$session_token]['values']['robot_database']) ? $_SESSION[$session_token]['values']['robot_database'] : array();
$database_game_counters = array();
foreach ($session_robot_database AS $temp_token => $temp_info){
  if (!isset($mmrpg_database_robots[$temp_token])){ continue; }
  $temp_info = $mmrpg_database_robots[$temp_token];

  if (!isset($database_game_counters[$temp_info['robot_game']])){ $database_game_counters[$temp_info['robot_game']] = array($temp_token); }
  elseif (!in_array($temp_token, $database_game_counters[$temp_info['robot_game']])){ $database_game_counters[$temp_info['robot_game']][] = $temp_token; }

}

// Define the index of allowable robots to appear in the database
$allowed_database_robots = array();
//$allowed_database_robots[] = 'met';
$allowed_database_robots[] = 'mega-man';
$temp_skip_games = array();
foreach ($mmrpg_database_robots AS $temp_token => $temp_info){
  if (in_array($temp_info['robot_game'], $temp_skip_games)){ continue; }
  $allowed_database_robots[] = $temp_token;
}
//if (true){ $allowed_database_robots = array_merge($allowed_database_robots, array('needle-man', 'magnet-man', 'gemini-man', 'hard-man', 'top-man', 'snake-man', 'spark-man', 'shadow-man')); }
$allowed_database_robots_count = !empty($allowed_database_robots) ? count($allowed_database_robots) : 0;

// Define the index of allowable robots to appear in the database
$visible_database_robots = array();
$temp_skip_games = array();
foreach ($mmrpg_database_robots AS $temp_token => $temp_info){
  if (in_array($temp_info['robot_game'], $temp_skip_games)){ continue; }
  $visible_database_robots[] = $temp_token;
}
/*
// DEMO MODE
if (!empty($_SESSION[$session_token]['DEMO'])){
  $visible_database_robots = array_merge($visible_database_robots, array('mega-man', 'proto-man', 'roll'));
  if (mmrpg_prototype_battles_complete('dr-light') >= 1){ $visible_database_robots = array_merge($visible_database_robots, array('cut-man', 'metal-man')); }
  if (mmrpg_prototype_battles_complete('dr-light') >= 2){ $visible_database_robots = array_merge($visible_database_robots, array('crash-man', 'ice-man', 'bomb-man', 'wood-man')); }
  if (mmrpg_prototype_battles_complete('dr-light') >= 3){ $visible_database_robots = array_merge($visible_database_robots, array('oil-man', 'bubble-man', 'fire-man', 'elec-man', 'heat-man')); }
  if (mmrpg_prototype_battles_complete('dr-light') >= 4){ $visible_database_robots = array_merge($visible_database_robots, array('bass', 'guts-man', 'time-man', 'quick-man', 'air-man', 'flash-man')); }
}
// NORMAL MODE
else {
  $temp_skip_games = array();
  foreach ($mmrpg_database_robots AS $temp_token => $temp_info){
    if (in_array($temp_info['robot_game'], $temp_skip_games)){ continue; }
    $visible_database_robots[] = $temp_token;
  }
}
*/
$visible_database_robots_count = !empty($visible_database_robots) ? count($visible_database_robots) : 0;

// Remove unallowed robots from the database
foreach ($mmrpg_database_robots AS $temp_key => $temp_info){
  if (!in_array($temp_key, $allowed_database_robots)){
    unset($mmrpg_database_robots[$temp_key]);
  }
}

//die('<pre>'.print_r($allowed_database_robots, true).'</pre>');

//die('<pre>'.print_r($mmrpg_database_robots, true).'</pre>');

/*
// Sort the robot index based on robot number
function mmrpg_index_sort_robots_visible($robot_one, $robot_two){
  global $visible_database_robots;
  if (in_array($robot_one['robot_token'], $visible_database_robots) && !in_array($robot_two['robot_token'], $visible_database_robots)){ return -1; }
  elseif (!in_array($robot_one['robot_token'], $visible_database_robots) && in_array($robot_two['robot_token'], $visible_database_robots)){ return 1; }
  //else { return 0; }
  else { return mmrpg_index_sort_robots($robot_one, $robot_two); }
}
uasort($mmrpg_database_robots, 'mmrpg_index_sort_robots_visible');
//uasort($mmrpg_database_robots, 'mmrpg_index_sort_robots');
 * */

//uasort($mmrpg_database_robots, 'mmrpg_index_sort_robots');

//die('<pre>'.print_r($mmrpg_database_robots, true).'</pre>');


// Count the robots groups for each page
$database_page_groups = array();
$database_page_groups[0] = array('MM00', 'MM20', 'MMRPG');
$database_page_groups[1] = array('MM01');
$database_page_groups[2] = array('MM02');
$database_page_groups[3] = array('MM03');
$database_page_groups[4] = array('MM04');
$database_page_groups[5] = array('MM05');
$database_page_groups[6] = array('MM06');
$database_page_groups[7] = array('MM07');
$database_page_groups[8] = array('MM08', 'MM085');
$database_page_groups[9] = array('MM09');
$database_page_groups[10] = array('MM10');
$database_page_groups[11] = array('MM19');
$database_page_groups[12] = array('MM30');
$database_page_groups[13] = array('MMEXE', 'MM21', 'MMRPG2');

// Count the robots for each page
$database_page_counters = array();
foreach ($database_page_groups AS $page_key => $group_array){
  $database_page_counters[$page_key] = false;
  foreach ($group_array AS $group_token){
    if (!empty($database_game_counters[$group_token])){
      $database_page_counters[$page_key] = true;
      continue;
    }
  }
}

// Collect the database markup from the session if set, otherwise generate it
$this_cache_stamp = MMRPG_CONFIG_CACHE_DATE.'_'.$allowed_database_robots_count;
$this_database_markup = '';
if (true){

  // Prepare the output buffer
  ob_start();

  // Determine the token for the very first robot in the database
  $temp_robot_tokens = array_values($mmrpg_database_robots);
  $first_robot_token = array_shift($temp_robot_tokens);
  $first_robot_token = $first_robot_token['robot_token'];
  unset($temp_robot_tokens);

  // Define the header/base counters for the database
  $global_robots_counters = array();
  $global_robots_counters['total'] = 0;
  $global_robots_counters['encountered'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
  $global_robots_counters['scanned'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
  $global_robots_counters['summoned'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
  $global_robots_counters['unlocked'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);

  // Define a function for looping through the robots and counting/updating them
  function temp_process_robots(&$mmrpg_database_robots, &$database_game_counters, &$database_page_groups, &$global_robots_counters, $session_token){
    // Loop through all of the robots, one by one, formatting their info
    foreach($mmrpg_database_robots AS $robot_key => &$robot_info){

      // Update the global game counters
      $temp_token = $robot_info['robot_token'];
      if (!isset($database_game_counters[$robot_info['robot_game']])){ $database_game_counters[$robot_info['robot_game']] = array($temp_token); }
      elseif (!in_array($temp_token, $database_game_counters[$robot_info['robot_game']])){ $database_game_counters[$robot_info['robot_game']][] = $temp_token; }

      // Update and/or define the encountered, scanned, summoned, and unlocked flags
      if (!isset($robot_info['robot_visible'])){ $robot_info['robot_visible'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]) ? true : false; }
      if (!isset($robot_info['robot_encountered'])){ $robot_info['robot_encountered'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_encountered']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_encountered'] : 0; }
      if (!isset($robot_info['robot_scanned'])){ $robot_info['robot_scanned'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_scanned']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_scanned'] : 0; }
      if (!isset($robot_info['robot_summoned'])){ $robot_info['robot_summoned'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_summoned']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_summoned'] : 0; }
      if (!isset($robot_info['robot_unlocked'])){ $robot_info['robot_unlocked'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_unlocked']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_unlocked'] : 0; }
      if (!isset($robot_info['robot_defeated'])){ $robot_info['robot_defeated'] = !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_defeated']) ? $_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]['robot_defeated'] : 0; }

      // Define the page token based on this robot's game of origin
      if (!isset($robot_info['robot_page_token'])){
        $temp_this_page_token = '?';
        foreach ($database_page_groups AS $page_key => $group_array){
          if (in_array($robot_info['robot_game'], $group_array)){ $temp_this_page_token = $page_key; break; }
          else { continue; }
        }
        $robot_info['robot_page_token'] = $temp_this_page_token;
      }

      // Increment the global robots counters
      $global_robots_counters['total']++;
      if ($robot_info['robot_encountered']){ $global_robots_counters['encountered']['total']++; $global_robots_counters['encountered'][$robot_info['robot_class']]++; }
      if ($robot_info['robot_scanned']){ $global_robots_counters['scanned']['total']++; $global_robots_counters['scanned'][$robot_info['robot_class']]++; }
      if ($robot_info['robot_unlocked']){ $global_robots_counters['unlocked']['total']++; $global_robots_counters['unlocked'][$robot_info['robot_class']]++; }
      elseif ($robot_info['robot_summoned']){ $global_robots_counters['summoned']['total']++; $global_robots_counters['summoned'][$robot_info['robot_class']]++; }

    }
    // Return true on success
    return true;
  }
  // Now to call upon the temp function, passing in appropriate variables
  temp_process_robots($mmrpg_database_robots, $database_game_counters, $database_page_groups, $global_robots_counters, $session_token);

  // Start generating the database markup
  ?>

  <span class="header block_1">Robot Database
    <span style="opacity: 0.25;">(
      <? $temp_unlocked_summoned_total = $global_robots_counters['unlocked']['total'] + $global_robots_counters['summoned']['total']; ?>
      <?= $temp_unlocked_summoned_total == 1 ? '<span title="1 Robot Summoned">1</span>' : '<span title="'.$temp_unlocked_summoned_total.' Robots Summoned">'.$temp_unlocked_summoned_total.'</span>' ?>
      / <?= $global_robots_counters['scanned']['total'] == 1 ? '<span title="1 Robot Scanned">1</span>' : '<span title="'.$global_robots_counters['scanned']['total'].' Robots Scanned">'.$global_robots_counters['scanned']['total'].'</span>' ?>
      / <?= $global_robots_counters['encountered']['total'] == 1 ? '<span title="1 Robot Encountered">1</span>' : '<span title="'.$global_robots_counters['encountered']['total'].' Robots Encountered">'.$global_robots_counters['encountered']['total'].'</span>' ?>
      / <?= $global_robots_counters['total'] == 1 ? '<span title="1 Robot">1 Robot Total</span>' : '<span title="'.$global_robots_counters['total'].' Robots Total">'.$global_robots_counters['total'].' Robots</span>' ?>
    )</span>
  </span>

  <table style="width: 100%;">
    <colgroup><col width="165" /><col /></colgroup>
    <tr>
    <td style="width: 165px; vertical-align: top;">

      <div id="canvas" style="">
        <?
        // START THE DATABASE CANVAS BUFFER
        ob_start();
        ?>
          <strong class="wrapper_header wrapper_subheader">Pages</strong>
          <div id="robot_games" class="wrapper_links">
            <?
            // Print out page links for all the pages that are enabled and placeholder otherwise
            $temp_current_page_page_key = !empty($_SESSION[$session_token]['battle_settings']['current_database_page_key']) ? $_SESSION[$session_token]['battle_settings']['current_database_page_key'] : 0;
            $temp_current_page_robot_key = !empty($_SESSION[$session_token]['battle_settings']['current_database_robot_token']) ? $_SESSION[$session_token]['battle_settings']['current_database_robot_token'] : false;
            foreach ($database_page_counters AS $page_key => $page_unlocked){
              $temp_is_current = $page_key == $temp_current_page_page_key ? true : false;
              if ($page_unlocked){ echo '<a class="game_link '.($temp_is_current ? 'game_link_active ' : '').'" href="#" data-game="'.$page_key.'">'.$page_key.'</a>'."\n"; }
              else { echo '<a class="game_link game_link_disabled">?</a>'."\n"; }
            }
            ?>
          </div>
          <strong class="wrapper_header wrapper_header_masters">Robot Masters</strong>
          <div class="wrapper wrapper_robots wrapper_robots_masters" data-select="robots" data-kind="masters">
            <?
            // Loop through all of the robots, one by one, displaying their buttons
            $key_counter = 0;
            foreach($mmrpg_database_robots AS $robot_key => $robot_info){
              // Skip if not the correct robot class
              if ($robot_info['robot_class'] != 'master'){ continue; }
              $temp_robot_type_class = 'robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none');
              // If this robot is visible, display normally
              if ($robot_info['robot_visible'] && in_array($robot_info['robot_token'], $visible_database_robots)){
                $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
                $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
                $robot_image_offset_x = -6 - $robot_image_offset;
                $robot_image_offset_y = -6 - $robot_image_offset;
                $robot_complete_markup = $robot_info['robot_unlocked'] ? '<span class="complete '.$temp_robot_type_class.'">&#10022;</span>' : '';
                echo '<a data-token="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" title="'.$robot_info['robot_number'].' '.$robot_info['robot_name'].'" style="background-position: '.$robot_image_offset_x.'px '.$robot_image_offset_y.'px; background-image: url(images/robots/'.(!empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token']).'/mug_right_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE.');" class="sprite sprite_robot sprite_robot_sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot robot_status_active robot_position_active '.($robot_key == $first_robot_token ? 'sprite_robot_current ' : '').' '.$temp_robot_type_class.'">'.$robot_info['robot_name'].$robot_complete_markup.'</a>';
              }
              // Otherwise, show a placeholder box for later
              else {
                //echo '<a data-token-locked="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_game'].'" title="???" style="background-position: -6px -6px; background-image: url(images/robots/robot/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');" class="sprite sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot robot_status_active robot_position_active">???</a>';
                echo '<a data-token-locked="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" title="'.$robot_info['robot_number'].' ???" style="background-color: #202020; background-image: none;" class="sprite sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot robot_status_active robot_position_active">???</a>';
              }
              // Increment the key counter
              $key_counter++;
            }
            ?>
          </div>
          <strong class="wrapper_header wrapper_header_mechas">Mecha Support</strong>
          <div class="wrapper wrapper_robots wrapper_robots_mechas" data-select="robots" data-kind="mechas">
            <?
            // Loop through all of the robots, one by one, displaying their buttons
            //$key_counter = 0;
            foreach($mmrpg_database_robots AS $robot_key => $robot_info){
              // Skip if not the correct robot class
              if ($robot_info['robot_class'] != 'mecha'){ continue; }
              $temp_robot_type_class = 'robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none');
              $robot_info['robot_name'] .= preg_match('/^([-a-z0-9]+)-(2|3)$/i', $robot_info['robot_token']) ? ' '.preg_replace('/^([-a-z0-9]+)-(2|3)$/i', '$2', $robot_info['robot_token']) : '';
              // If this robot is visible, display normally
              if ($robot_info['robot_visible'] && in_array($robot_info['robot_token'], $visible_database_robots)){
                $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
                $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
                $robot_image_offset_x = -5 - $robot_image_offset;
                $robot_image_offset_y = -5 - $robot_image_offset;
                $robot_complete_markup = $robot_info['robot_summoned'] ? '<span class="complete '.$temp_robot_type_class.'">&#10022;</span>' : '';
                echo '<a data-token="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" title="'.$robot_info['robot_number'].' '.$robot_info['robot_name'].'" style="background-position: '.$robot_image_offset_x.'px '.$robot_image_offset_y.'px; background-image: url(images/robots/'.(!empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token']).'/mug_right_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE.');" class="sprite sprite_robot sprite_robot_sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot robot_status_active robot_position_active '.($robot_key == $first_robot_token ? 'sprite_robot_current ' : '').' '.$temp_robot_type_class.'">'.$robot_info['robot_name'].$robot_complete_markup.'</a>';
              }
              // Otherwise, show a placeholder box for later
              else {
                //echo '<a data-token-locked="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_game'].'" title="???" style="background-position: -4px -4px; background-image: url(images/robots/robot/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');" class="sprite sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot robot_status_active robot_position_active">???</a>';
                echo '<a data-token-locked="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" title="'.$robot_info['robot_number'].' ???" style="background-color: #202020; background-image: none;" class="sprite sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot robot_status_active robot_position_active">???</a>';
              }
              // Increment the key counter
              $key_counter++;
            }
            ?>
          </div>
          <strong class="wrapper_header wrapper_header_bosses">Fortress Bosses</strong>
          <div class="wrapper wrapper_robots wrapper_robots_bosses" data-select="robots" data-kind="bosses">
            <?
            // Loop through all of the robots, one by one, displaying their buttons
            //$key_counter = 0;
            foreach($mmrpg_database_robots AS $robot_key => $robot_info){
              // Skip if not the correct robot class
              if ($robot_info['robot_class'] != 'boss'){ continue; }
              $temp_robot_type_class = 'robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none');
              $robot_info['robot_name'] .= preg_match('/^([-a-z0-9]+)-(2|3)$/i', $robot_info['robot_token']) ? ' '.preg_replace('/^([-a-z0-9]+)-(2|3)$/i', '$2', $robot_info['robot_token']) : '';
              // If this robot is visible, display normally
              if ($robot_info['robot_visible'] && in_array($robot_info['robot_token'], $visible_database_robots)){
                $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
                $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
                $robot_image_offset_x = -5 - $robot_image_offset;
                $robot_image_offset_y = -5 - $robot_image_offset;
                $robot_complete_markup = $robot_info['robot_summoned'] ? '<span class="complete '.$temp_robot_type_class.'">&#10022;</span>' : '';
                echo '<a data-token="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" title="'.$robot_info['robot_number'].' '.$robot_info['robot_name'].'" style="background-position: '.$robot_image_offset_x.'px '.$robot_image_offset_y.'px; background-image: url(images/robots/'.(!empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token']).'/mug_right_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE.');" class="sprite sprite_robot sprite_robot_sprite sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].' sprite_'.$robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'].'_mugshot robot_status_active robot_position_active '.($robot_key == $first_robot_token ? 'sprite_robot_current ' : '').' '.$temp_robot_type_class.'">'.$robot_info['robot_name'].$robot_complete_markup.'</a>';
              }
              // Otherwise, show a placeholder box for later
              else {
                //echo '<a data-token-locked="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_game'].'" title="???" style="background-position: -4px -4px; background-image: url(images/robots/robot/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');" class="sprite sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot robot_status_active robot_position_active">???</a>';
                echo '<a data-token-locked="'.$robot_info['robot_token'].'" data-kind="'.$robot_info['robot_class'].'" data-game="'.$robot_info['robot_page_token'].'" title="'.$robot_info['robot_number'].' ???" style="background-color: #202020; background-image: none;" class="sprite sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot robot_status_active robot_position_active">???</a>';
              }
              // Increment the key counter
              $key_counter++;
            }
            ?>
          </div>
        <?
        // COLLECT THE DATABASE CANVAS MARKUP
        $database_canvas_markup = preg_replace('/\s+/', ' ', trim(ob_get_clean()));
        ?>
      </div>

    </td>
    <td style="vertical-align: top;">

      <div id="console" class="noresize" style="height: auto;">
        <?
        // START THE DATABASE CONSOLE BUFFER
        ob_start();
        ?>
          <div id="robots" class="wrapper">
            <?$key_counter = 0;?>
            <?
            // Loop through all the robots again and display them
            foreach($mmrpg_database_robots AS $robot_key => $robot_info):
              // Define whether this robot has been scanned and/or unlocked
              //$robot_info['robot_unlocked'] = mmrpg_prototype_robot_unlocked(false, $robot_info['robot_token']);
              //$robot_info['robot_scanned'] = $robot_info['robot_unlocked'] || !empty($_SESSION[$session_token]['values']['robot_database'][$robot_info['robot_token']]) ? true : false;
              //if ($robot_info['robot_scanned'] && $robot_info['robot_class'] == 'mecha'){ $robot_info['robot_unlocked'] = true; }
              // If this is a mecha, define it's generation for display
              if (!empty($robot_info['robot_class']) && $robot_info['robot_class'] == 'mecha'){
                $robot_info['robot_generation'] = '1st';
                if (preg_match('/-2$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '2nd'; $robot_info['robot_name'] .= ' 2'; }
                elseif (preg_match('/-3$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '3rd'; $robot_info['robot_name'] .= ' 3'; }
              }
            ?>
            <? if (empty($robot_info['robot_image_size'])){ $robot_info['robot_image_size'] = 40; } ?>
            <div class="event event_triple event_<?= $robot_key == $first_robot_token ? 'visible' : 'hidden' ?>" data-token="<?=$robot_info['robot_token']?>">
              <div class="this_sprite sprite_left" style="height: 40px;">
                <? $temp_margin = -1 * ceil(($robot_info['robot_image_size'] - 40) * 0.5); ?>
                <div style="margin-top: <?= $temp_margin ?>px; margin-bottom: <?= $temp_margin * 3 ?>px; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/mug_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_mug robot_status_active robot_position_active"><?=$robot_info['robot_name']?></div>
                <?/*
                <div title="<?=$robot_info['robot_name']?>" style="background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?=$robot_info['robot_name']?></div>
                <div title="<?=$robot_info['robot_name']?>" style="background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_taunt robot_status_active robot_position_active"><?=$robot_info['robot_name']?></div>
                <div title="<?=$robot_info['robot_name']?>" style="background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_victory robot_status_active robot_position_active"><?=$robot_info['robot_name']?></div>
                */?>
              </div>
              <div class="header header_left robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="margin-right: 0;">
                <?=$robot_info['robot_name']?>&#39;s Data
                <?
                if ($robot_info['robot_class'] == 'master' && $robot_info['robot_unlocked']){ echo '<span data-tooltip-type="robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').'" title="Database Entry Complete!" style="font-weight: normal; position: relative; bottom: 1px; padding-left: 2px; ">&#10022;</span>'; }
                elseif ($robot_info['robot_class'] == 'mecha' && $robot_info['robot_summoned']){ echo '<span data-tooltip-type="robot_type robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').'" title="Database Entry Complete!" style="font-weight: normal; position: relative; bottom: 1px; padding-left: 2px; ">&#10023;</span>'; }
                ?>
                <? if(!empty($robot_info['robot_core'])): ?>
                  <span class="robot_type robot_core"><?=ucfirst($robot_info['robot_core'])?> Core</span>
                <? else: ?>
                  <span class="robot_type robot_core">Neutral Core</span>
                <? endif; ?>
              </div>
              <div class="body body_left" style="margin-right: 0; padding: 2px 3px;">
                <table class="full" style="margin-bottom: 5px;">
                  <colgroup>
                    <col width="35%" />
                    <col width="1%" />
                    <col width="64%" />
                  </colgroup>
                  <tbody>
                    <tr>
                      <td  class="right">
                        <label style="display: block; float: left;">Model :</label>
                        <span class="robot_number"><?=$robot_info['robot_number']?></span>
                      </td>
                      <td class="center">&nbsp;</td>
                      <td  class="right">
                        <label style="display: block; float: left;">Name :</label>
                        <span class="robot_name robot_type"><?=$robot_info['robot_name']?></span>
                        <? if (!empty($robot_info['robot_generation'])){ ?><span class="robot_name robot_type" style="width: auto;"><?=$robot_info['robot_generation']?> Gen</span><? } ?>
                      </td>
                    </tr>
                    <tr>
                      <td  class="right">
                        <label style="display: block; float: left;">Type :</label>
                        <? if(!empty($robot_info['robot_core'])): ?>
                          <span class="robot_name robot_type robot_type_<?=$robot_info['robot_core']?>"><?=ucfirst($robot_info['robot_core'])?> Core</span>
                        <? else: ?>
                          <span class="robot_name robot_type robot_type_none">Neutral Core</span>
                        <? endif; ?>
                      </td>
                      <td class="center">&nbsp;</td>
                      <td  class="right">
                        <label style="display: block; float: left;">Class :</label>
                        <span class="robot_number robot_description"><?= !empty($robot_info['robot_description']) ? $robot_info['robot_description'] : '&hellip;' ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td  class="right">
                        <label style="display: block; float: left;">Energy :</label>
                        <? if($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']): ?>
                          <span class="robot_stat robot_type robot_type_energy" style="padding-left: <?= ceil($robot_info['robot_energy'] * 0.4) ?>px;"><?= $robot_info['robot_energy'] ?></span>
                        <? else: ?>
                          <span class="robot_stat">?</span>
                        <? endif; ?>
                      </td>
                      <td class="center">&nbsp;</td>
                      <td class="right">
                        <label style="display: block; float: left;">Weaknesses :</label>
                        <?
                        if ($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']){
                          if (!empty($robot_info['robot_weaknesses'])){
                            $temp_string = array();
                            foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                              $temp_string[] = '<span class="robot_weakness robot_type robot_type_'.$robot_weakness.'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</span>';
                            }
                            echo implode(' ', $temp_string);
                          } else {
                            echo '<span class="robot_weakness robot_type robot_type_none">None</span>';
                          }
                        } else {
                          echo '<span class="robot_weakness">?</span>';
                        }
                        ?>
                      </td>

                    </tr>
                    <tr>
                      <td  class="right">
                        <label style="display: block; float: left;">Attack :</label>
                        <? if($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']): ?>
                          <span class="robot_stat robot_type robot_type_attack" style="padding-left: <?= ceil($robot_info['robot_attack'] * 0.4) ?>px;"><?= $robot_info['robot_attack'] ?></span>
                        <? else: ?>
                          <span class="robot_stat">?</span>
                        <? endif; ?>
                      </td>
                      <td class="center">&nbsp;</td>
                      <td class="right">
                        <label style="display: block; float: left;">Resistances :</label>
                        <?
                        if ($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']){
                          if (!empty($robot_info['robot_resistances'])){
                            $temp_string = array();
                            foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                              $temp_string[] = '<span class="robot_resistance robot_type robot_type_'.$robot_resistance.'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</span>';
                            }
                            echo implode(' ', $temp_string);
                          } else {
                            echo '<span class="robot_resistance robot_type robot_type_none">None</span>';
                          }
                        } else {
                          echo '<span class="robot_resistance">?</span>';
                        }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td  class="right">
                        <label style="display: block; float: left;">Defense :</label>
                        <? if($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']): ?>
                          <span class="robot_stat robot_type robot_type_defense" style="padding-left: <?= ceil($robot_info['robot_defense'] * 0.4) ?>px;"><?= $robot_info['robot_defense'] ?></span>
                        <? else: ?>
                          <span class="robot_stat">?</span>
                        <? endif; ?>
                      </td>
                      <td class="center">&nbsp;</td>
                      <td class="right">
                        <label style="display: block; float: left;">Affinities :</label>
                        <?
                        if ($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']){
                          if (!empty($robot_info['robot_affinities'])){
                            $temp_string = array();
                            foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                              $temp_string[] = '<span class="robot_affinity robot_type robot_type_'.$robot_affinity.'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</span>';
                            }
                            echo implode(' ', $temp_string);
                          } else {
                            echo '<span class="robot_affinity robot_type robot_type_none">None</span>';
                          }
                        } else {
                          echo '<span class="robot_affinity">?</span>';
                        }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="right">
                        <label style="display: block; float: left;">Speed :</label>
                        <? if($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']): ?>
                          <span class="robot_stat robot_type robot_type_speed" style="padding-left: <?= ceil($robot_info['robot_speed'] * 0.4) ?>px;"><?= $robot_info['robot_speed'] ?></span>
                        <? else: ?>
                          <span class="robot_stat">?</span>
                        <? endif; ?>
                      </td>
                      <td class="center">&nbsp;</td>
                      <td class="right">
                        <label style="display: block; float: left;">Immunities :</label>
                        <?
                        if ($robot_info['robot_scanned'] || $robot_info['robot_unlocked'] || $robot_info['robot_summoned']){
                          if (!empty($robot_info['robot_immunities'])){
                            $temp_string = array();
                            foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                              $temp_string[] = '<span class="robot_immunity robot_type robot_type_'.$robot_immunity.'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</span>';
                            }
                            echo implode(' ', $temp_string);
                          } else {
                            echo '<span class="robot_immunity robot_type robot_type_none">None</span>';
                          }
                        } else {
                          echo '<span class="robot_immunity">?</span>';
                        }
                        ?>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <?
                // Collect the robot field if not empty
                if (!empty($robot_info['robot_field']) && $robot_info['robot_field'] != 'field'){
                  //echo $robot_info['robot_field'];
                  $temp_robot_field = !empty($mmrpg_database_fields[$robot_info['robot_field']]) ? $mmrpg_database_fields[$robot_info['robot_field']] : array();
                  $temp_field_title = $temp_robot_field['field_name'];
                  $temp_field_title .= !empty($temp_robot_field['field_type']) ? ' ('.ucfirst($temp_robot_field['field_type']).' Type)' : ' (Neutral Type)';
                  if (!empty($temp_robot_field['field_multipliers'])){
                    $temp_field_title .= '&lt;br /&gt;';
                    $count = 0;
                    foreach ($temp_robot_field['field_multipliers'] AS $type => $value){
                      if ($count > 0){ $temp_field_title .= ' | '; }
                      $temp_field_title .= $type == 'none' ? 'Neutral' : ucfirst($type).' x '.number_format($value, 1);
                      $count++;
                    }
                  }
                }
                ?>
                <table class="full">
                  <colgroup>
                    <col width="100%" />
                  </colgroup>
                  <tbody>
                    <tr>
                      <td class="right">
                        <label style="display: block; float: left;">Field :</label>
                        <div class="field_container">
                          <? if(!empty($temp_robot_field) && ($robot_info['robot_unlocked'] || $robot_info['robot_summoned'])): ?>
                            <span class="ability_name ability_type ability_type_<?= !empty($temp_robot_field['field_type']) ? $temp_robot_field['field_type'] : 'none' ?> field_name" title="<?= $temp_field_title ?>"><?= $temp_robot_field['field_name'] ?></span>
                          <? else: ?>
                            <span class="ability_name ability_type ability_type_empty field_name">???</span>
                          <? endif; ?>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <table class="full">
                  <colgroup>
                    <col width="100%" />
                  </colgroup>
                  <tbody>
                    <tr>
                      <td class="right">
                        <label style="display: block; float: left;">Abilities :</label>
                        <div class="ability_container">
                        <?
                        $robot_ability_rewards = $robot_info['robot_rewards']['abilities'];
                        if (
                          !empty($robot_ability_rewards) &&
                          (($robot_info['robot_class'] == 'master' && $robot_info['robot_unlocked'])
                          || ($robot_info['robot_class'] == 'mecha' && $robot_info['robot_summoned']))
                          ){
                          $temp_string = array();
                          $ability_key = 0;

                          //$temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

                          foreach ($robot_ability_rewards AS $this_info){
                            $this_level = $this_info['level'];
                            $this_ability = mmrpg_ability::parse_index_info($mmrpg_database_abilities[$this_info['token']]);
                            $this_ability_token = $this_ability['ability_token'];
                            $this_ability_name = $this_ability['ability_name'];
                            $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                            $this_ability_type2 = !empty($this_ability['ability_type2']) ? $this_ability['ability_type2'] : false;
                            if (!empty($this_ability_type) && !empty($mmrpg_index['types'][$this_ability_type])){ $this_ability_type = $mmrpg_index['types'][$this_ability_type]['type_name'].' Type'; }
                            else { $this_ability_type = ''; }
                            $this_ability_damage = !empty($this_ability['ability_damage']) ? $this_ability['ability_damage'] : 0;
                            $this_ability_recovery = !empty($this_ability['ability_recovery']) ? $this_ability['ability_recovery'] : 0;
                            $this_ability_accuracy = !empty($this_ability['ability_accuracy']) ? $this_ability['ability_accuracy'] : 0;
                            $this_ability_description = !empty($this_ability['ability_description']) ? $this_ability['ability_description'] : '';
                            //if (!empty($this_ability_type)){ $this_ability_title_plain .= ' | '.$this_ability_type; }
                            //if (!empty($this_ability_damage)){ $this_ability_title_plain .= ' | '.$this_ability_damage.' Damage'; }
                            //if (!empty($this_ability_recovery)){ $this_ability_title_plain .= ' | '.$this_ability_recovery.' Recovery'; }
                            //if (!empty($this_ability_accuracy)){ $this_ability_title_plain .= ' | '.$this_ability_accuracy.'% Accuracy'; }
                            //if (!empty($this_ability_description)){ $this_ability_title_plain .= ' | '.$this_ability_description; }
                            $this_ability_title_html = str_replace(' ', '&nbsp;', $this_ability_name);
                            $this_ability_title_html = ($this_level > 1 ? 'Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).' : ' : 'Start : ').$this_ability_title_html;
                            $this_ability_title = mmrpg_ability::print_editor_title_markup($robot_info, $this_ability);
                            $this_ability_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_ability_title));
                            $this_ability_title_tooltip = htmlentities($this_ability_title, ENT_QUOTES, 'UTF-8');
                            $temp_string[] = '<span title="'.$this_ability_title_plain.'" data-tooltip="'.$this_ability_title_tooltip.'" class="ability_name ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').(!empty($this_ability['ability_type2']) ? '_'.$this_ability['ability_type2'] : '').'">'.$this_ability_title_html.'</span>';  //.(($ability_key + 1) % 3 == 0 ? '<br />' : '');
                            $ability_key++;
                          }
                          echo implode(' ', $temp_string);
                        } elseif (!$robot_info['robot_unlocked']){
                          echo '<span class="ability_name ability_type ability_type_empty">???</span>';
                        } else {
                          echo '<span class="robot_ability robot_type_none">None</span>';
                        }
                        ?>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <table class="full">
                  <colgroup>
                    <col width="100%" />
                  </colgroup>
                  <tbody>
                    <tr>
                      <td class="right">
                        <label style="display: block; float: left;">Records :</label>
                        <div class="record_container">
                          <span class="ability_name ability_type ability_empty record_name">Summoned : <?= $robot_info['robot_summoned'] == 1 ? '1 Times' : $robot_info['robot_summoned'].' Times' ?></span>
                          <span class="ability_name ability_type ability_empty record_name">Encountered : <?= $robot_info['robot_encountered'] == 1 ? '1 Times' : $robot_info['robot_encountered'].' Times' ?></span>
                          <span class="ability_name ability_type ability_empty record_name">Defeated : <?= $robot_info['robot_defeated'] == 1 ? '1 Times' : $robot_info['robot_defeated'].' Times' ?></span>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>

              </div>
            </div>
            <?$key_counter++;?>
            <?endforeach;?>
          </div>
          <?
          // COLLECT THE DATABASE CONSOLE MARKUP
          $database_console_markup = preg_replace('/\s+/', ' ', trim(ob_get_clean()));
          ?>
      </div>

    </td>
    </tr>
  </table>





  <?

  // Collect the output buffer content
  $this_database_markup = preg_replace('#\s+#', ' ', trim(ob_get_clean()));

  // Update the session cache
  //$_SESSION['DATABASE'][$this_cache_stamp] = $this_database_markup;
}


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title><?= !MMRPG_CONFIG_IS_LIVE ? '@ ' : '' ?>View Database | Mega Man RPG Prototype | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/database.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
gameSettings.fadeIn = <?= isset($_GET['fadein']) ? $_GET['fadein'] : 'false' ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
gameSettings.firstRobot = <?= !empty($temp_current_page_robot_key) ? "'{$temp_current_page_robot_key}'" : 'false' ?>;
gameSettings.autoScrollTop = false;
// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
$(document).ready(function(){

  // Update global reference variables
  thisBody = $('#mmrpg');
  thisPrototype = $('#prototype', thisBody);
  thisWindow = $(window);

  //alert('I, the database, have a wap setting of '+(gameSettings.wapFlag ? 'true' : 'false')+'?! and my body has a class of '+$('body').attr('class')+'!');

  // Start playing the appropriate stage music
  //top.mmrpg_music_load('misc/data-base');

  // Fade in the leaderboard screen slowly
  thisBody.waitForImages(function(){
    var tempTimeout = setTimeout(function(){
      if (gameSettings.fadeIn){ thisBody.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing'); }
      else { thisBody.removeClass('hidden').css({opacity:1}); }
      // Let the parent window know the menu has loaded
      parent.prototype_menu_loaded();
      }, 1000);
    }, false, true);

  // Append the canvas and console markup to the body now that we're ready
  gameCanvas.append('<?= str_replace("'", "\\'", $database_canvas_markup) ?>');
  gameConsole.append('<?= str_replace("'", "\\'", $database_console_markup) ?>');

  // Create the click event for canvas sprites
  $('.sprite[data-token]', gameCanvas).live('click', function(){

    var dataSprite = $(this);
    var dataParent = dataSprite.closest('.wrapper');

    var dataToken = dataSprite.attr('data-token');
    var dataSelect = dataParent.attr('data-select');
    var dataSelectorCurrent = '#'+dataSelect+' .event_visible';
    var dataSelectorNext = '#'+dataSelect+' .event[data-token='+dataToken+']';

    var isAlreadyCurrent = dataSprite.hasClass('sprite_robot_current') ? true : false;
    $('.sprite_robot_current', gameCanvas).removeClass('sprite_robot_current');
    dataSprite.addClass('sprite_robot_current');
    dataParent.css({display:'block'});

    // Check if there is already robot event data on-screen, and either fade it out or skip to the new one
    if ($(dataSelectorCurrent, gameConsole).length && !isAlreadyCurrent){

      // Fade out the current visible events before manually removing them from view
      $(dataSelectorCurrent, gameConsole).stop().animate({opacity:0},250,'swing',function(){
        // Remove the visible class, add the hidden one, then reset the opacity to 1
        $(this).removeClass('event_visible').addClass('event_hidden').css({opacity:1});
        // Fade the new robot data into view by setting opacity to zero, switching classes, then animating back to 1
        $(dataSelectorNext, gameConsole).css({opacity:0}).removeClass('event_hidden').addClass('event_visible').animate({opacity:1.0},250,'swing');
        });

      } else {

        // Fade the new robot data into view by setting opacity to zero, switching classes, then animating back to 1
        $(dataSelectorNext, gameConsole).removeClass('event_hidden').addClass('event_visible').css({opacity:1});

      }

    // Update the session variable with the current page link number
    $.post('scripts/script.php',{requestType:'session',requestData:'battle_settings,current_database_robot_token,'+dataToken});

    });
  // Trigger a click on the first robot
  //$('.sprite[data-token]:first-child', gameCanvas).trigger('click');

  // Create the click event for canvas game links
  $('.game_link[data-game]', gameCanvas).live('click', function(e){
    // Collect references to the link object and properties
    e.preventDefault();
    var dataLink = $(this);
    var dataGame = dataLink.attr('data-game');
    // Remove the active link from the other link and add it to this one
    $('.game_link[data-game!='+dataGame+']', gameCanvas).removeClass('game_link_active');
    $('.game_link[data-game='+dataGame+']', gameCanvas).addClass('game_link_active');
    // Hide all robot links that are not from the selected game and show the ones that are
    $('.sprite[data-game!='+dataGame+']', gameCanvas).addClass('sprite_robot_hidden');
    $('.sprite[data-game='+dataGame+']', gameCanvas).removeClass('sprite_robot_hidden');
    // Count the number of master and mecha robots currently visible
    var visibleRobots = $('.sprite', gameCanvas).not('.sprite_robot_hidden');
    var visibleRobotsCount = visibleRobots.length;
    var visibleRobotMasters = visibleRobots.filter('.sprite[data-kind=master]').length;
    var visibleRobotMechas = visibleRobots.filter('.sprite[data-kind=mecha]').length;
    var visibleRobotBosses = visibleRobots.filter('.sprite[data-kind=boss]').length;
    //console.log('Switched to '+dataGame+'! Total = '+visibleRobotsCount+'; Robot Masters = '+visibleRobotMasters+'; Mecha Support = '+visibleRobotMechas);
    // Hide or show the robot master container based on count
    if (visibleRobotMasters > 0){ $('.wrapper_header_masters, .wrapper_robots_masters', gameCanvas).css({display:'block'}); }
    else { $('.wrapper_header_masters, .wrapper_robots_masters', gameCanvas).css({display:'none'}); }
    // Hide or show the robot mecha container based on count
    if (visibleRobotMechas > 0){ $('.wrapper_header_mechas, .wrapper_robots_mechas', gameCanvas).css({display:'block'}); }
    else { $('.wrapper_header_mechas, .wrapper_robots_mechas', gameCanvas).css({display:'none'}); }
    // Hide or show the robot boss container based on count
    if (visibleRobotBosses > 0){ $('.wrapper_header_bosses, .wrapper_robots_bosses', gameCanvas).css({display:'block'}); }
    else { $('.wrapper_header_bosses, .wrapper_robots_bosses', gameCanvas).css({display:'none'}); }
    // Auto-click the first visible robot sprite in the canvas
    if (gameSettings.firstRobot !== false){
      var firstVisibleSprite = $('.sprite[data-token='+gameSettings.firstRobot+']', gameCanvas);
      gameSettings.firstRobot = false;
      } else {
      var firstVisibleSprite = $('.sprite[data-token][data-game='+dataGame+']', gameCanvas).first();
      }
    //console.log(firstVisibleSprite.text());
    firstVisibleSprite.trigger('click');
    // Update the session variable with the current page link number
    $.post('scripts/script.php',{requestType:'session',requestData:'battle_settings,current_database_page_key,'+dataGame});
    // Return true on succes
    return true;
    });
  // Click the first game link, whatever it is
  if ($('.game_link_active[data-game]', gameCanvas).length){ var tempFirstLink = $('.game_link_active[data-game]', gameCanvas); }
  else { var tempFirstLink = $('.game_link[data-game]', gameCanvas).first(); }
  tempFirstLink.trigger('click');

  // Create the click event for the back button
  $('a.back', gameCanvas).click(function(e){
    e.preventDefault();
    window.location = 'prototype.php';
    });

  // Attach resize events to the window
  thisWindow.resize(function(){ windowResizeFrame(); });
  setTimeout(function(){ windowResizeFrame(); }, 1000);
  windowResizeFrame();

  var windowHeight = $(window).height();
  var htmlHeight = $('html').height();
  var htmlScroll = $('html').scrollTop();
  //alert('windowHeight = '+windowHeight+'; htmlHeight = '+htmlHeight+'; htmlScroll = '+htmlScroll+'; ');


});

// Create the windowResize event for this page
function windowResizeFrame(){

  var windowWidth = thisWindow.width();
  var windowHeight = thisWindow.height();
  var headerHeight = $('.header', thisBody).outerHeight(true);

  var newBodyHeight = windowHeight;
  var newFrameHeight = newBodyHeight - headerHeight;

  if (windowWidth > 800){ thisBody.addClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
  else { thisBody.removeClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }

  thisBody.css({height:newBodyHeight+'px'});
  thisPrototype.css({height:newBodyHeight+'px'});

  //console.log('windowWidth = '+windowWidth+'; parentWidth = '+parentWidth+'; thisTypeContainerWidth = '+thisTypeContainerWidth+'; thisStarContainerWidth = '+thisStarContainerWidth+'; ');

}
</script>
</head>
<body id="mmrpg" class="iframe hidden" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">
  <div id="prototype" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">
    <div id="database" class="menu">

      <?= $this_database_markup ?>

    </div>

  </div>
<script type="text/javascript">
$(document).ready(function(){
<?
// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags']['events'])){ $_SESSION[$session_token]['flags']['events'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags']['events'];
// If this is the first time using the editor, display the introductory area
$temp_event_flag = 'unlocked-tooltip_robot-database-intro';
if (empty($_SESSION[$session_token]['DEMO']) && empty($temp_game_flags[$temp_event_flag]) && $global_allow_editing){
  $temp_game_flags[$temp_event_flag] = true;
  ?>
  // Generate a first-time event canvas that explains how the editor works
  gameSettings.windowEventsCanvas = [
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/field/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/robots/picket-man/mug_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 20px; left: 25px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/robots/bubble-man/mug_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 35px; left: 130px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/robots/mega-man/mug_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 50px; left: 240px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/robots/fire-man/mug_right_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 35px; right: 130px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80" style="background-image: url(images/robots/spring-head/mug_right_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 0; top: 20px; right: 25px; width: 80px; height: 80px;">&nbsp;</div>'+
    ''
    ];
  // Generate a first-time event message that explains how the editor works
  gameSettings.windowEventsMessages = [
    '<p>The <strong>Robot Database</strong> contains detailed records of all of the robot masters and support mechas that appear in the prototype.  Robots encountered in game are automatically added to the database, with more information being filled in when you scan, summon, and/or unlock the robot for use in battle.</p>'+
    '<p>Click on any of any of the visible pages to scroll through the different generations of robots, and check the area on the right for their stats, types, class, weaknesses, abilties, records, and more. Try to fill in as many pages as you can - it might come in handy.</p>'+
    ''
    ];
  // Push this event to the parent window and display to the user
  top.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
  <?
}
?>
});
</script>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'data/analytics.php'); }
?>
</body>
</html>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_bottom.php');
// Unset the database variable
unset($DB);
?>