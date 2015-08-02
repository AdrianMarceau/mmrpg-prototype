<?
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
$mmrpg_database_mechas = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_published = 1 {$temp_condition} ORDER BY robot_order ASC;", 'robot_token');

// Remove unallowed mechas from the database, and increment type counters
foreach ($mmrpg_database_mechas AS $temp_token => $temp_info){

  // Remove hidden mechas from the the array (assuming they're still here)
  if (true){

    // Send this data through the mecha index parser
    $temp_info = mmrpg_robot::parse_index_info($temp_info);

    // Ensure this mecha's image exists, else default to the placeholder
    if ($temp_info['robot_flag_complete']){ $temp_info['robot_image'] = $temp_token; }
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
    if (!empty($temp_info['robot_core'])){
      if (!isset($mmrpg_database_mechas_types['cores'][$temp_info['robot_core']])){ $mmrpg_database_mechas_types['cores'][$temp_info['robot_core']] = 0; }
      $mmrpg_database_mechas_types['cores'][$temp_info['robot_core']]++;
    }
    else {
      $mmrpg_database_mechas_types['cores']['none']++;
    }
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

// Determine the token for the very first mecha in the database
$temp_mecha_tokens = array_values($mmrpg_database_mechas);
$first_mecha_token = array_shift($temp_mecha_tokens);
$first_mecha_token = $first_mecha_token['robot_token'];
unset($temp_mecha_tokens);

// Count the number of mechas collected and filtered
$mmrpg_database_mechas_count = count($mmrpg_database_mechas);
$mmrpg_database_mechas_count_complete = 0;

// Define the max stat value before we filter and update
if (!isset($mmrpg_stat_base_max_value)){ $mmrpg_stat_base_max_value = array(); }
$mmrpg_stat_base_max_value['mecha'] = 0;

// Loop through the database and generate the links for these mechas
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_mechas_links = '';
$mmrpg_database_mechas_links_counter = 0;
foreach ($mmrpg_database_mechas AS $mecha_key => $mecha_info){
  // If a type filter has been applied to the robot page
  if (isset($this_current_filter) && $this_current_filter == 'none' && $mecha_info['robot_core'] != ''){ $key_counter++; continue; }
  elseif (isset($this_current_filter) && $this_current_filter != 'none' && $mecha_info['robot_core'] != $this_current_filter && $mecha_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }

  // If this is the first in a new group
  $game_code = !empty($mecha_info['robot_game']) ? $mecha_info['robot_game'] : 'MMRPG';
  if ($game_code != $last_game_code){
    if ($key_counter != 0){ $mmrpg_database_mechas_links .= '</div>'; }
    $mmrpg_database_mechas_links .= '<div class="float link group" data-game="'.$game_code.'">';
    $last_game_code = $game_code;
  }

  // Check if this is a mecha and prepare extra text
  $mecha_info['robot_name_append'] = '';
  if (!empty($mecha_info['robot_class']) && $mecha_info['robot_class'] == 'mecha'){
    $mecha_info['robot_generation'] = '1st';
    if (preg_match('/-2$/', $mecha_info['robot_token'])){ $mecha_info['robot_generation'] = '2nd'; $mecha_info['robot_name_append'] = ' 2'; }
    elseif (preg_match('/-3$/', $mecha_info['robot_token'])){ $mecha_info['robot_generation'] = '3rd'; $mecha_info['robot_name_append'] = ' 3'; }
  }
  // Collect the mecha sprite dimensions
  $mecha_flag_complete = !empty($mecha_info['robot_flag_complete']) ? true : false;
  $mecha_image_size = !empty($mecha_info['robot_image_size']) ? $mecha_info['robot_image_size'] : 40;
  $mecha_image_size_text = $mecha_image_size.'x'.$mecha_image_size;
  $mecha_image_token = !empty($mecha_info['robot_image']) ? $mecha_info['robot_image'] : $mecha_info['robot_token'];
  $mecha_image_incomplete = $mecha_image_token == 'mecha' ? true : false;
  $mecha_is_active = !empty($this_current_token) && $this_current_token == $mecha_info['robot_token'] ? true : false;
  $mecha_title_text = $mecha_info['robot_name'].$mecha_info['robot_name_append'].' | '.$mecha_info['robot_number'].' | '.(!empty($mecha_info['robot_core']) ? ucwords($mecha_info['robot_core'].(!empty($mecha_info['robot_core2']) ? ' / '.$mecha_info['robot_core2'] : '')) : 'Neutral').' Type';
  $mecha_title_text .= '|| [[E:'.$mecha_info['robot_energy'].' | W:'.$mecha_info['robot_weapons'].' | A:'.$mecha_info['robot_attack'].' | D:'.$mecha_info['robot_defense'].' | S:'.$mecha_info['robot_speed'].']]';
  //$mecha_image_path = 'images/robots/'.$mecha_image_token.'/mug_right_'.$mecha_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  $mecha_image_path = 'i/r/'.$mecha_image_token.'/mr'.$mecha_image_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  $mecha_stat_max = $mecha_info['robot_energy'] + $mecha_info['robot_attack'] + $mecha_info['robot_defense'] + $mecha_info['robot_speed'];
  if ($mecha_stat_max > $mmrpg_stat_base_max_value['mecha']){ $mmrpg_stat_base_max_value['mecha'] = $mecha_stat_max; }
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $mecha_title_text ?>" data-token="<?= $mecha_info['robot_token'] ?>" class="float left link type <?= ($mecha_image_incomplete  ? 'inactive ' : '').(!empty($mecha_info['robot_core']) ? $mecha_info['robot_core'] : 'none') ?>">
    <a class="sprite robot link mugshot size<?= $mecha_image_size.($mecha_key == $first_mecha_token ? ' current' : '') ?>" href="<?='database/mechas/'.$mecha_info['robot_token']?>/" rel="<?= $mecha_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($mecha_image_token != 'mecha'): ?>
        <img src="<?= $mecha_image_path ?>" width="<?= $mecha_image_size ?>" height="<?= $mecha_image_size ?>" alt="<?= $mecha_title_text ?>" />
      <? else: ?>
        <span><?= $mecha_info['robot_name'].$mecha_info['robot_name_append'] ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  if ($mecha_flag_complete){ $mmrpg_database_mechas_count_complete++; }
  $mmrpg_database_mechas_links .= ob_get_clean();
  $mmrpg_database_mechas_links_counter++;
  $key_counter++;
}

// End the groups, however many there were
$mmrpg_database_mechas_links .= '</div>';

?>