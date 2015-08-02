<?
// ROBOT DATABASE

// Define the index of counters for robot types
$mmrpg_database_robots_types = array('cores' => array(), 'weaknesses' => array(), 'resistances' => array(), 'affinities' => array(), 'immunities' => array());
foreach ($mmrpg_database_types AS $token => $info){
  $mmrpg_database_robots_types['cores'][$token] = 0;
  $mmrpg_database_robots_types['weaknesses'][$token] = 0;
  $mmrpg_database_robots_types['resistances'][$token] = 0;
  $mmrpg_database_robots_types['affinities'][$token] = 0;
  $mmrpg_database_robots_types['immunities'][$token] = 0;
}

// Define the index of hidden robots to not appear in the database
$hidden_database_robots = array();
$hidden_database_robots = array_merge($hidden_database_robots, array('robot', 'rock')); //'mega-man-ds', 'proto-man-ds', 'bass-ds',
if (!defined('DATA_DATABASE_SHOW_CACHE')){ $hidden_database_robots[] = 'cache'; }
if (!defined('DATA_DATABASE_SHOW_HIDDEN')){ $hidden_database_robots[] = 'bond-man'; $hidden_database_robots[] = 'fake-man'; $hidden_database_robots[] = 'pulse-man'; }
//$hidden_database_robots = array_merge($hidden_database_robots, array('bomb-man', 'cut-man', 'elec-man', 'fire-man', 'guts-man', 'ice-man', 'oil-man', 'time-man'));
//$hidden_database_robots = array_merge($hidden_database_robots, array('air-man', 'bubble-man', 'crash-man', 'flash-man', 'heat-man', 'metal-man', 'quick-man', 'wood-man'));
//$hidden_database_robots = array_merge($hidden_database_robots, array('needle-man', 'magnet-man', 'gemini-man', 'hard-man', 'top-man', 'snake-man', 'spark-man', 'shadow-man'));
$hidden_database_robots_count = !empty($hidden_database_robots) ? count($hidden_database_robots) : 0;


// Define the hidden robot query condition
$temp_condition = '';
$temp_condition .= "AND robot_class = 'master' ";
if (!empty($hidden_database_robots)){
  $temp_tokens = array();
  foreach ($hidden_database_robots AS $token){ $temp_tokens[] = "'".$token."'"; }
  $temp_condition .= 'AND robot_token NOT IN ('.implode(',', $temp_tokens).') ';
}


// Collect the database robots and fields
$mmrpg_database_fields = $DB->get_array_list("SELECT * FROM mmrpg_index_fields WHERE field_flag_published = 1;", 'field_token');
$mmrpg_database_robots = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_published = 1 {$temp_condition} ORDER BY robot_order ASC;", 'robot_token');

// Remove unallowed robots from the database, and increment type counters
foreach ($mmrpg_database_robots AS $temp_token => $temp_info){

  // Remove hidden robots from the the array (assuming they're still here)
  if (true){

    // Send this data through the robot index parser
    $temp_info = mmrpg_robot::parse_index_info($temp_info);

    // Ensure this robot's image exists, else default to the placeholder
    if ($temp_info['robot_flag_complete']){ $temp_info['robot_image'] = $temp_token; }
    else { $temp_info['robot_image'] = 'robot'; }

    // Modify the name of this robot if it is of the mecha class
    if ($temp_info['robot_class'] == 'mecha' && defined('DATA_DATABASE_SHOW_MECHAS')){
      // Collect this mecha's field token, then robot master token, then robot master number
      $temp_field_token = $temp_info['robot_field'];
      $temp_field_info = mmrpg_field::parse_index_info($mmrpg_database_fields[$temp_field_token]);
      $temp_master_token = !empty($temp_field_info['field_master']) ? $temp_field_info['field_master'] : 'met';
      $temp_master_number = $mmrpg_database_robots[$temp_master_token]['robot_number'];
      $temp_info['robot_master_number'] = $temp_master_number;
    } elseif ($temp_info['robot_class'] == 'master'){
      $temp_info['robot_master_number'] = $temp_info['robot_number'];
    } elseif ($temp_info['robot_class'] == 'boss'){
      $temp_info['robot_master_number'] = $temp_info['robot_number'];
    }


    // Increment the robot core counter if not empty
    if (!empty($temp_info['robot_core'])){ $mmrpg_database_robots_types['cores'][$temp_info['robot_core']]++; }
    else { $mmrpg_database_robots_types['cores']['none']++; }
    if (!empty($temp_info['robot_core2'])){ $mmrpg_database_robots_types['cores'][$temp_info['robot_core2']]++; }
    //else { $mmrpg_database_robots_types['cores']['none']++; }


    // Loop through the robot weaknesses if there are any to loop through
    if (!empty($temp_info['robot_weaknesses'])){
      foreach ($temp_info['robot_weaknesses'] AS $weakness){ $mmrpg_database_robots_types['weaknesses'][$weakness]++; }
    } else {
      $mmrpg_database_robots_types['weaknesses']['none']++;
    }
    // Loop through the robot resistances if there are any to loop through
    if (!empty($temp_info['robot_resistances'])){
      foreach ($temp_info['robot_resistances'] AS $weakness){ $mmrpg_database_robots_types['resistances'][$weakness]++; }
    } else {
      $mmrpg_database_robots_types['resistances']['none']++;
    }
    // Loop through the robot affinities if there are any to loop through
    if (!empty($temp_info['robot_affinities'])){
      foreach ($temp_info['robot_affinities'] AS $weakness){ $mmrpg_database_robots_types['affinities'][$weakness]++; }
    } else {
      $mmrpg_database_robots_types['affinities']['none']++;
    }
    // Loop through the robot immunities if there are any to loop through
    if (!empty($temp_info['robot_immunities'])){
      foreach ($temp_info['robot_immunities'] AS $weakness){ $mmrpg_database_robots_types['immunities'][$weakness]++; }
    } else {
      $mmrpg_database_robots_types['immunities']['none']++;
    }

    // Update the main database array with the changes
    $mmrpg_database_robots[$temp_token] = $temp_info;

  }
}

// Determine the token for the very first robot in the database
$temp_robot_tokens = array_values($mmrpg_database_robots);
$first_robot_token = array_shift($temp_robot_tokens);
$first_robot_token = $first_robot_token['robot_token'];
unset($temp_robot_tokens);

// Count the number of robots collected and filtered
$mmrpg_database_robots_count = count($mmrpg_database_robots);
$mmrpg_database_robots_count_complete = 0;

// Define the max stat value before we filter and update
if (!isset($mmrpg_stat_base_max_value)){ $mmrpg_stat_base_max_value = array(); }
$mmrpg_stat_base_max_value['master'] = 0;


// Define database variables we'll be using to generate links
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_robots_links = '';
$mmrpg_database_robots_links_counter = 0;

// Loop through the database and generate the links for these robots
foreach ($mmrpg_database_robots AS $robot_key => $robot_info){
  // If a type filter has been applied to the robot page
  if (isset($this_current_filter) && $this_current_filter == 'none' && $robot_info['robot_core'] != ''){ $key_counter++; continue; }
  elseif (isset($this_current_filter) && $this_current_filter != 'none' && $robot_info['robot_core'] != $this_current_filter && $robot_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }

  // If this is the first in a new group
  $game_code = !empty($robot_info['robot_group']) ? $robot_info['robot_group'] : (!empty($robot_info['robot_game']) ? $robot_info['robot_game'] : 'MMRPG');
  if ($game_code != $last_game_code){
    if ($key_counter != 0){ $mmrpg_database_robots_links .= '</div>'; }
    $mmrpg_database_robots_links .= '<div class="float link group" data-game="'.$game_code.'">';
    $last_game_code = $game_code;
  }

  // Check if this is a mecha and prepare extra text
  $robot_info['robot_name_append'] = '';
  if (!empty($robot_info['robot_class']) && $robot_info['robot_class'] == 'mecha'){
    $robot_info['robot_generation'] = '1st';
    if (preg_match('/-2$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '2nd'; $robot_info['robot_name_append'] = ' 2'; }
    elseif (preg_match('/-3$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '3rd'; $robot_info['robot_name_append'] = ' 3'; }
  }

  // Collect the robot sprite dimensions
  $robot_flag_complete = !empty($robot_info['robot_flag_complete']) ? true : false;
  $robot_image_size = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
  $robot_image_size_text = $robot_image_size.'x'.$robot_image_size;
  $robot_image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
  $robot_image_incomplete = $robot_image_token == 'robot' ? true : false;
  $robot_is_active = !empty($this_current_token) && $this_current_token == $robot_info['robot_token'] ? true : false;
  $robot_title_text = $robot_info['robot_name'].$robot_info['robot_name_append'].' | '.$robot_info['robot_number'].' | '.(!empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral').' Core';
  $robot_title_text .= '|| [[E:'.$robot_info['robot_energy'].' | W:'.$robot_info['robot_weapons'].' | A:'.$robot_info['robot_attack'].' | D:'.$robot_info['robot_defense'].' | S:'.$robot_info['robot_speed'].']]';
  //$robot_title_text .= ' | game:'.$robot_info['robot_game'].' | group:'.$robot_info['robot_group'];
  //$robot_image_path = 'images/robots/'.$robot_image_token.'/mug_right_'.$robot_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  $robot_image_path = 'i/r/'.$robot_image_token.'/mr'.$robot_image_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  $robot_stat_max = $robot_info['robot_energy'] + $robot_info['robot_attack'] + $robot_info['robot_defense'] + $robot_info['robot_speed'];
  if ($robot_stat_max > $mmrpg_stat_base_max_value['master']){ $mmrpg_stat_base_max_value['master'] = $robot_stat_max; }
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $robot_title_text ?>" data-token="<?= $robot_info['robot_token'] ?>" class="float left link type <?= ($robot_image_incomplete ? 'inactive ' : '').(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') ?>">
    <a class="sprite robot link mugshot size<?= $robot_image_size.($robot_key == $first_robot_token ? ' current' : '') ?>" href="<?='database/robots/'.$robot_info['robot_token']?>/" rel="<?= $robot_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($robot_image_token != 'robot'): ?>
        <img src="<?= $robot_image_path ?>" width="<?= $robot_image_size ?>" height="<?= $robot_image_size ?>" alt="<?= $robot_title_text ?>" />
      <? else: ?>
        <span><?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  if ($robot_flag_complete){ $mmrpg_database_robots_count_complete++; }
  $mmrpg_database_robots_links .= ob_get_clean();
  $mmrpg_database_robots_links_counter++;
  $key_counter++;
}

// End the groups, however many there were
$mmrpg_database_robots_links .= '</div>';

?>