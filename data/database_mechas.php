<?
// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// ROBOT DATABASE

// Define the index of counters for mecha types
$mmrpg_database_mechas_types = array('cores' => array(), 'weaknesses' => array(), 'resistances' => array(), 'affinities' => array(), 'immunities' => array());
foreach ($mmrpg_database_types AS $token => $info){
  $mmrpg_database_mechas_types['cores'][$token] = 0;
  $mmrpg_database_mechas_types['weaknesses'][$token] = 0;
  $mmrpg_database_mechas_types['resistances'][$token] = 0;
  $mmrpg_database_mechas_types['affinities'][$token] = 0;
  $mmrpg_database_mechas_types['immunities'][$token] = 0;
}

// Define the index of hidden mechas to not appear in the database
$hidden_database_mechas = array();
//$hidden_database_mechas = array_merge($hidden_database_mechas, array('mecha'));
//if (!defined('DATA_DATABASE_SHOW_HIDDEN')){ $hidden_database_mechas[] = ''; }
//$hidden_database_mechas = array_merge($hidden_database_mechas, array('bomb-man', 'cut-man', 'elec-man', 'fire-man', 'guts-man', 'ice-man', 'oil-man', 'time-man'));
//$hidden_database_mechas = array_merge($hidden_database_mechas, array('air-man', 'bubble-man', 'crash-man', 'flash-man', 'heat-man', 'metal-man', 'quick-man', 'wood-man'));
//$hidden_database_mechas = array_merge($hidden_database_mechas, array('needle-man', 'magnet-man', 'gemini-man', 'hard-man', 'top-man', 'snake-man', 'spark-man', 'shadow-man'));
$hidden_database_mechas_count = !empty($hidden_database_mechas) ? count($hidden_database_mechas) : 0;


// Define the hidden mecha query condition
$temp_condition = '';
$temp_condition .= "AND robot_class = 'mecha' ";
if (!empty($hidden_database_mechas)){
  $temp_tokens = array();
  foreach ($hidden_database_mechas AS $token){ $temp_tokens[] = "'".$token."'"; }
  $temp_condition .= 'AND robot_token NOT IN ('.implode(',', $temp_tokens).') ';
}


// Collect the database mechas and fields
$mmrpg_database_fields = $DB->get_array_list("SELECT * FROM mmrpg_index_fields WHERE field_flag_published = 1;", 'field_token');
$mmrpg_database_mechas = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_published = 1 {$temp_condition};", 'robot_token');

// Remove unallowed mechas from the database, and increment type counters
foreach ($mmrpg_database_mechas AS $temp_token => $temp_info){

  // Remove hidden mechas from the the array (assuming they're still here)
  if (true){

    // Send this data through the mecha index parser
    $temp_info = rpg_robot::parse_index_info($temp_info);

    // Ensure this mecha's image exists, else default to the placeholder
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$temp_token.'/')){ $temp_info['robot_image'] = $temp_token; }
    else { $temp_info['robot_image'] = 'mecha'; }

    // Modify the name of this mecha if it is of the mecha class
    if ($temp_info['robot_class'] == 'mecha'){
      // Collect this mecha's field token, then mecha master token, then mecha master number
      $temp_field_token = !is_string($temp_info['robot_field']) ? array_shift($temp_info['robot_field']) : $temp_info['robot_field'];
      //echo($temp_info['robot_token'].' $temp_field_token = '.print_r($temp_field_token, true).' | ');
      $temp_field_info = mmrpg_field::parse_index_info($mmrpg_database_fields[$temp_field_token]);
      //echo($temp_info['robot_token'].' $temp_field_token = '.print_r($temp_field_token, true).' | ');
      $temp_master_token = !empty($temp_field_info['field_master']) ? $temp_field_info['field_master'] : 'met';
      $temp_master_number = !empty($mmrpg_database_robots[$temp_master_token]) ? $mmrpg_database_robots[$temp_master_token]['robot_number'] : $temp_info['robot_number'];
      $temp_info['robot_master_number'] = $temp_master_number;
    }


    // Increment the mecha core counter if not empty
    if (!empty($temp_info['robot_core'])){ $mmrpg_database_mechas_types['cores'][$temp_info['robot_core']]++; }
    else { $mmrpg_database_mechas_types['cores']['none']++; }
    if (!empty($temp_info['robot_core2'])){ $mmrpg_database_mechas_types['cores'][$temp_info['robot_core2']]++; }
    //else { $mmrpg_database_mechas_types['cores']['none']++; }

    // Loop through the mecha weaknesses if there are any to loop through
    if (!empty($temp_info['robot_weaknesses'])){
      foreach ($temp_info['robot_weaknesses'] AS $weakness){ $mmrpg_database_mechas_types['weaknesses'][$weakness]++; }
    } else {
      $mmrpg_database_mechas_types['weaknesses']['none']++;
    }
    // Loop through the mecha resistances if there are any to loop through
    if (!empty($temp_info['robot_resistances'])){
      foreach ($temp_info['robot_resistances'] AS $weakness){ $mmrpg_database_mechas_types['resistances'][$weakness]++; }
    } else {
      $mmrpg_database_mechas_types['resistances']['none']++;
    }
    // Loop through the mecha affinities if there are any to loop through
    if (!empty($temp_info['robot_affinities'])){
      foreach ($temp_info['robot_affinities'] AS $weakness){ $mmrpg_database_mechas_types['affinities'][$weakness]++; }
    } else {
      $mmrpg_database_mechas_types['affinities']['none']++;
    }
    // Loop through the mecha immunities if there are any to loop through
    if (!empty($temp_info['robot_immunities'])){
      foreach ($temp_info['robot_immunities'] AS $weakness){ $mmrpg_database_mechas_types['immunities'][$weakness]++; }
    } else {
      $mmrpg_database_mechas_types['immunities']['none']++;
    }

    // Update the main database array with the changes
    $mmrpg_database_mechas[$temp_token] = $temp_info;

  }
}

// DEBUG DEBUG DEBUG
//die('<pre>$mmrpg_database_mechas : '.print_r($mmrpg_database_mechas, true).'</pre>');

// Sort the mecha index based on mecha number
$temp_last_mechas = array('met');
$temp_serial_ordering = array(
	'DLN', // Dr. Light Number
	'DWN', // Dr. Wily Number
	'DCN', // Dr. Cossack Number
  'DLM'  // Dr. Light Robot
  );
function mmrpg_index_sort_mechas($mecha_one, $mecha_two){
  global $temp_last_mechas, $temp_serial_ordering;
  $mecha_one['robot_game'] = !empty($mecha_one['robot_game']) ? $mecha_one['robot_game'] : 'MM00';
  $mecha_two['robot_game'] = !empty($mecha_two['robot_game']) ? $mecha_two['robot_game'] : 'MM00';
  $mecha_one['robot_class'] = !empty($mecha_one['robot_class']) ? $mecha_one['robot_class'] : 'master';
  $mecha_two['robot_class'] = !empty($mecha_two['robot_class']) ? $mecha_two['robot_class'] : 'master';
  $mecha_one['robot_token_position'] = array_search($mecha_one['robot_token'], $temp_last_mechas);
  $mecha_two['robot_token_position'] = array_search($mecha_two['robot_token'], $temp_last_mechas);
  if ($mecha_one['robot_token_position'] !== false && $mecha_two['robot_token_position'] !== false){
    if ($mecha_one['robot_token_position'] > $mecha_two['robot_token_position']){ return -1; }
    elseif ($mecha_one['robot_token_position'] < $mecha_two['robot_token_position']){ return 1; }
    else { return 0; }
  }
  elseif ($mecha_one['robot_token_position'] !== false || $mecha_two['robot_token_position'] !== false){
    if ($mecha_one['robot_token_position'] === false){ return -1; }
    elseif ($mecha_two['robot_token_position'] === false){ return 1; }
    else { return 0; }
  }
  elseif ($mecha_one['robot_token_position'] === false && $mecha_two['robot_token_position'] === false){
    if ($mecha_one['robot_class'] > $mecha_two['robot_class']){ return 1; }
    elseif ($mecha_one['robot_class'] < $mecha_two['robot_class']){ return -1; }
    elseif ($mecha_one['robot_game'] > $mecha_two['robot_game']){ return 1; }
    elseif ($mecha_one['robot_game'] < $mecha_two['robot_game']){ return -1; }
    elseif ($mecha_one['robot_master_number'] > $mecha_two['robot_master_number']){ return 1; }
    elseif ($mecha_one['robot_master_number'] < $mecha_two['robot_master_number']){ return -1; }
    elseif ($mecha_one['robot_number'] > $mecha_two['robot_number']){ return 1; }
    elseif ($mecha_one['robot_number'] < $mecha_two['robot_number']){ return -1; }
    else { return 0; }
  }
  return 0;
}
uasort($mmrpg_database_mechas, 'mmrpg_index_sort_mechas');

// Sort the mecha type index based on values
foreach ($mmrpg_database_mechas_types AS $token => $array){
  asort($array);
  $array = array_reverse($array, true);
  //$mmrpg_database_mechas_types[$token] = $array;
}

// Determine the token for the very first mecha in the database
$temp_mecha_tokens = array_values($mmrpg_database_mechas);
$first_mecha_token = array_shift($temp_mecha_tokens);
$first_mecha_token = $first_mecha_token['robot_token'];
unset($temp_mecha_tokens);

// Count the number of mechas collected and filtered
$mmrpg_database_mechas_count = count($mmrpg_database_mechas);

// Loop through the database and generate the links for these mechas
$key_counter = 0;
$mmrpg_database_mechas_links = '';
$mmrpg_database_mechas_links_counter = 0;
$mmrpg_database_mechas_links_counter_incomplete = 0;
foreach ($mmrpg_database_mechas AS $mecha_key => $mecha_info){
  // If a type filter has been applied to the robot page
  if (isset($this_current_filter) && $this_current_filter == 'none' && $mecha_info['robot_core'] != ''){ $key_counter++; continue; }
  elseif (isset($this_current_filter) && $this_current_filter != 'none' && $mecha_info['robot_core'] != $this_current_filter){ $key_counter++; continue; }
  // Check if this is a mecha and prepare extra text
  $mecha_info['robot_name_append'] = '';
  if (!empty($mecha_info['robot_class']) && $mecha_info['robot_class'] == 'mecha'){
    $mecha_info['robot_generation'] = '1st';
    if (preg_match('/-2$/', $mecha_info['robot_token'])){ $mecha_info['robot_generation'] = '2nd'; $mecha_info['robot_name_append'] = ' 2'; }
    elseif (preg_match('/-3$/', $mecha_info['robot_token'])){ $mecha_info['robot_generation'] = '3rd'; $mecha_info['robot_name_append'] = ' 3'; }
  }
  // Collect the mecha sprite dimensions
  $mecha_image_size = !empty($mecha_info['robot_image_size']) ? $mecha_info['robot_image_size'] : 40;
  $mecha_image_size_text = $mecha_image_size.'x'.$mecha_image_size;
  $mecha_image_token = !empty($mecha_info['robot_image']) ? $mecha_info['robot_image'] : $mecha_info['robot_token'];
  $mecha_image_incomplete = $mecha_image_token == 'mecha' ? true : false;
  $mecha_is_active = !empty($this_current_token) && $this_current_token == $mecha_info['robot_token'] ? true : false;
  $mecha_title_text = $mecha_info['robot_name'].$mecha_info['robot_name_append'].' | '.(!empty($mecha_info['robot_core']) ? ucwords($mecha_info['robot_core'].(!empty($mecha_info['robot_core2']) ? ' / '.$mecha_info['robot_core2'] : '')) : 'Neutral').' Type';
  $mecha_image_path = 'images/robots/'.$mecha_image_token.'/mug_right_'.$mecha_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $mecha_title_text ?>" data-token="<?= $mecha_info['robot_token'] ?>" class="float float_left float_link robot_type robot_type_<?= !empty($mecha_info['robot_core']) ? $mecha_info['robot_core'] : 'none' ?><?= $robot_image_incomplete  ? ' incomplete' : '' ?>">
    <a class="sprite sprite_robot_link sprite_robot sprite_robot_sprite sprite_40x40 sprite_40x40_mugshot sprite_size_<?= $mecha_image_size_text ?>  robot_status_active robot_position_active <?= $mecha_key == $first_mecha_token ? 'sprite_robot_current ' : '' ?>" href="<?='database/mechas/'.$mecha_info['robot_token']?>/" rel="<?= $mecha_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($mecha_image_token != 'mecha'): ?>
        <img src="<?= $mecha_image_path ?>" width="<?= $mecha_image_size ?>" height="<?= $mecha_image_size ?>" alt="<?= $mecha_title_text ?>" />
      <? else: ?>
        <span><?= $mecha_info['robot_name'].$mecha_info['robot_name_append'] ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  $mmrpg_database_mechas_links .= preg_replace('/\s+/', ' ', trim(ob_get_clean()))."\n";
  $mmrpg_database_mechas_links_counter++;
  if ($mecha_image_incomplete){ $mmrpg_database_mechas_links_counter_incomplete++; }
  $key_counter++;
}

?>