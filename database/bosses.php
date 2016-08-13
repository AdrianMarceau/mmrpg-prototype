<?

// FORTRESS BOSS DATABASE

// Define the index of counters for boss types
$mmrpg_database_bosses_types = array('cores' => array(), 'weaknesses' => array(), 'resistances' => array(), 'affinities' => array(), 'immunities' => array());
foreach ($mmrpg_database_types AS $token => $info){
  $mmrpg_database_bosses_types['cores'][$token] = 0;
  $mmrpg_database_bosses_types['weaknesses'][$token] = 0;
  $mmrpg_database_bosses_types['resistances'][$token] = 0;
  $mmrpg_database_bosses_types['affinities'][$token] = 0;
  $mmrpg_database_bosses_types['immunities'][$token] = 0;
}

// Define the index of hidden bosses to not appear in the database
$hidden_database_bosses = array();
$hidden_database_bosses = array_merge($hidden_database_bosses, array('boss'));
if (!defined('DATA_DATABASE_SHOW_CACHE')){ $hidden_database_bosses[] = 'cache'; }
if (!defined('DATA_DATABASE_SHOW_HIDDEN')){
  $hidden_database_bosses = array_merge($hidden_database_bosses, array('planet-man', 'cosmo-man'));
  $hidden_database_bosses = array_merge($hidden_database_bosses, array('solo', 'duo', 'duo-2', 'trio', 'trio-2', 'trio-3'));
}
//$hidden_database_bosses = array_merge($hidden_database_bosses, array('bomb-man', 'cut-man', 'elec-man', 'fire-man', 'guts-man', 'ice-man', 'oil-man', 'time-man'));
//$hidden_database_bosses = array_merge($hidden_database_bosses, array('air-man', 'bubble-man', 'crash-man', 'flash-man', 'heat-man', 'metal-man', 'quick-man', 'wood-man'));
//$hidden_database_bosses = array_merge($hidden_database_bosses, array('needle-man', 'magnet-man', 'gemini-man', 'hard-man', 'top-man', 'snake-man', 'spark-man', 'shadow-man'));
$hidden_database_bosses_count = !empty($hidden_database_bosses) ? count($hidden_database_bosses) : 0;


// Define the hidden boss query condition
$temp_condition = '';
$temp_condition .= "AND robot_class = 'boss' ";
if (!empty($hidden_database_bosses)){
  $temp_tokens = array();
  foreach ($hidden_database_bosses AS $token){ $temp_tokens[] = "'".$token."'"; }
  $temp_condition .= 'AND robot_token NOT IN ('.implode(',', $temp_tokens).') ';
}


// Collect the database bosses and fields
$mmrpg_database_fields = $db->get_array_list("SELECT * FROM mmrpg_index_fields WHERE field_flag_published = 1;", 'field_token');
$mmrpg_database_bosses = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_published = 1 {$temp_condition} ORDER BY robot_order ASC;", 'robot_token');

// Remove unallowed bosses from the database, and increment type counters
foreach ($mmrpg_database_bosses AS $temp_token => $temp_info){

  // Remove hidden bosses from the the array (assuming they're still here)
  if (true){

    // Send this data through the boss index parser
    $temp_info = rpg_robot::parse_index_info($temp_info);

    // Ensure this boss's image exists, else default to the placeholder
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$temp_token.'/')){ $temp_info['robot_image'] = $temp_token; }
    else { $temp_info['robot_image'] = 'boss'; }

    // Modify the name of this boss if it is of the boss class
    if ($temp_info['robot_class'] == 'boss'){
      // Collect this boss's field token, then boss master token, then boss master number
      $temp_field_token = !is_string($temp_info['robot_field']) ? array_shift($temp_info['robot_field']) : $temp_info['robot_field'];
      //echo($temp_info['robot_token'].' $temp_field_token = '.print_r($temp_field_token, true).' | ');
      $temp_field_info = rpg_field::parse_index_info($mmrpg_database_fields[$temp_field_token]);
      //echo($temp_info['robot_token'].' $temp_field_token = '.print_r($temp_field_token, true).' | ');
      $temp_master_token = !empty($temp_field_info['field_master']) ? $temp_field_info['field_master'] : 'met';
      $temp_master_number = !empty($mmrpg_database_robots[$temp_master_token]) ? $mmrpg_database_robots[$temp_master_token]['robot_number'] : $temp_info['robot_number'];
      $temp_info['robot_master_number'] = $temp_master_number;
    }


    // Increment the boss core counter if not empty
    if (!empty($temp_info['robot_core'])){ $mmrpg_database_bosses_types['cores'][$temp_info['robot_core']]++; }
    else { $mmrpg_database_bosses_types['cores']['none']++; }
    if (!empty($temp_info['robot_core2'])){ $mmrpg_database_bosses_types['cores'][$temp_info['robot_core2']]++; }
    //else { $mmrpg_database_bosses_types['cores']['none']++; }

    // Loop through the boss weaknesses if there are any to loop through
    if (!empty($temp_info['robot_weaknesses'])){
      foreach ($temp_info['robot_weaknesses'] AS $weakness){ $mmrpg_database_bosses_types['weaknesses'][$weakness]++; }
    } else {
      $mmrpg_database_bosses_types['weaknesses']['none']++;
    }
    // Loop through the boss resistances if there are any to loop through
    if (!empty($temp_info['robot_resistances'])){
      foreach ($temp_info['robot_resistances'] AS $weakness){ $mmrpg_database_bosses_types['resistances'][$weakness]++; }
    } else {
      $mmrpg_database_bosses_types['resistances']['none']++;
    }
    // Loop through the boss affinities if there are any to loop through
    if (!empty($temp_info['robot_affinities'])){
      foreach ($temp_info['robot_affinities'] AS $weakness){ $mmrpg_database_bosses_types['affinities'][$weakness]++; }
    } else {
      $mmrpg_database_bosses_types['affinities']['none']++;
    }
    // Loop through the boss immunities if there are any to loop through
    if (!empty($temp_info['robot_immunities'])){
      foreach ($temp_info['robot_immunities'] AS $weakness){ $mmrpg_database_bosses_types['immunities'][$weakness]++; }
    } else {
      $mmrpg_database_bosses_types['immunities']['none']++;
    }

    // Update the main database array with the changes
    $mmrpg_database_bosses[$temp_token] = $temp_info;

  }
}

// DEBUG DEBUG DEBUG
//die('<pre>$mmrpg_database_bosses : '.print_r($mmrpg_database_bosses, true).'</pre>');

// Determine the token for the very first boss in the database
$temp_boss_tokens = array_values($mmrpg_database_bosses);
$first_boss_token = array_shift($temp_boss_tokens);
$first_boss_token = $first_boss_token['robot_token'];
unset($temp_boss_tokens);

// Count the number of bosses collected and filtered
$mmrpg_database_bosses_count = count($mmrpg_database_bosses);

// Loop through the database and generate the links for these bosses
$key_counter = 0;
$mmrpg_database_bosses_links = '';
$mmrpg_database_bosses_links_counter = 0;
$mmrpg_database_bosses_links_counter_incomplete = 0;
foreach ($mmrpg_database_bosses AS $boss_key => $boss_info){
  // If a type filter has been applied to the robot page
  if (isset($this_current_filter) && $this_current_filter == 'none' && $boss_info['robot_core'] != ''){ $key_counter++; continue; }
  elseif (isset($this_current_filter) && $this_current_filter != 'none' && $boss_info['robot_core'] != $this_current_filter){ $key_counter++; continue; }
  // Check if this is a boss and prepare extra text
  $boss_info['robot_name_append'] = '';
  // Collect the boss sprite dimensions
  $boss_image_size = !empty($boss_info['robot_image_size']) ? $boss_info['robot_image_size'] : 40;
  $boss_image_size_text = $boss_image_size.'x'.$boss_image_size;
  $boss_image_token = !empty($boss_info['robot_image']) ? $boss_info['robot_image'] : $boss_info['robot_token'];
  $boss_image_incomplete = $boss_image_token == 'boss' ? true : false;
  $boss_is_active = !empty($this_current_token) && $this_current_token == $boss_info['robot_token'] ? true : false;
  $boss_title_text = $boss_info['robot_name'].$boss_info['robot_name_append'].' | '.(!empty($boss_info['robot_core']) ? ucwords($boss_info['robot_core'].(!empty($boss_info['robot_core2']) ? ' / '.$boss_info['robot_core2'] : '')) : 'Neutral').' Type';
  $boss_image_path = 'images/robots/'.$boss_image_token.'/mug_right_'.$boss_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $boss_title_text ?>" data-token="<?= $boss_info['robot_token'] ?>" class="float float_left float_link robot_type robot_type_<?= !empty($boss_info['robot_core']) ? $boss_info['robot_core'] : 'none' ?><?= $boss_image_incomplete  ? ' incomplete' : '' ?>">
    <a class="sprite sprite_robot_link sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot sprite_size_<?= $boss_image_size_text ?>  robot_status_active robot_position_active <?= $boss_key == $first_boss_token ? 'sprite_robot_current ' : '' ?>" href="<?='database/bosses/'.$boss_info['robot_token']?>/" rel="<?= $boss_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($boss_image_token != 'boss'): ?>
        <img src="<?= $boss_image_path ?>" width="<?= $boss_image_size ?>" height="<?= $boss_image_size ?>" alt="<?= $boss_title_text ?>" />
      <? else: ?>
        <span><?= $boss_info['robot_name'].$boss_info['robot_name_append'] ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  $mmrpg_database_bosses_links .= preg_replace('/\s+/', ' ', trim(ob_get_clean()))."\n";
  $mmrpg_database_bosses_links_counter++;
  if ($boss_image_incomplete){ $mmrpg_database_bosses_links_counter_incomplete++; }
  $key_counter++;
}

?>