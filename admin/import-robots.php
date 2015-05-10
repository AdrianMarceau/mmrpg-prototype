<?

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while importing!');  }

// Collect any extra request variables for the import
$this_import_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=import-robots&limit=<?=$this_import_limit?>">Update Robot Database</a> &raquo;
</div>
<?
$this_page_markup .= ob_get_clean();

// Require the MMRPG database file
//define('DATA_DATABASE_SHOW_MECHAS', true);
//define('DATA_DATABASE_SHOW_CACHE', true);
//define('DATA_DATABASE_SHOW_HIDDEN', true);
//require_once('data/database.php');

// TYPES DATABASE

// Define the index of types for the game
$mmrpg_database_types = $mmrpg_index['types'];
$temp_remove_types = array('attack', 'defense', 'speed', 'energy', 'weapons', 'empty', 'light', 'wily', 'cossack', 'damage', 'recovery', 'experience', 'level');
foreach ($temp_remove_types AS $token){ unset($mmrpg_database_types[$token]); }
uasort($mmrpg_database_types, function($t1, $t2){
  if ($t1['type_order'] > $t2['type_order']){ return 1; }
  elseif ($t1['type_order'] < $t2['type_order']){ return -1; }
  else { return 0; }
});

// Truncate any robots currently in the database
$DB->query('TRUNCATE TABLE mmrpg_index_robots');

// Require the robots index file
require(MMRPG_CONFIG_ROOTDIR.'data/fields/_index.php');
require(MMRPG_CONFIG_ROOTDIR.'data/robots/_index.php');
// Require the spreadsheet functions file
require(MMRPG_CONFIG_ROOTDIR.'admin/spreadsheets.php');

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_robots</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_index['robots']) ? count($mmrpg_index['robots']) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_robots, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';


// Collect all the spreadsheet content for the robots
$spreadsheet_robot_stats = mmrpg_spreadsheet_robot_stats();
$spreadsheet_mecha_stats = mmrpg_spreadsheet_mecha_stats();
$spreadsheet_boss_stats = mmrpg_spreadsheet_boss_stats();
$spreadsheet_robot_quotes = mmrpg_spreadsheet_robot_quotes();
$spreadsheet_mecha_quotes = mmrpg_spreadsheet_mecha_quotes();
$spreadsheet_boss_quotes = mmrpg_spreadsheet_boss_quotes();
$spreadsheet_robot_descriptions = mmrpg_spreadsheet_robot_descriptions();
$spreadsheet_mecha_descriptions = mmrpg_spreadsheet_mecha_descriptions();
$spreadsheet_boss_descriptions = mmrpg_spreadsheet_boss_descriptions();

/*
header('Content-type: text/plain; charset=UTF-8');
die($this_page_markup."\n\n".
  '$spreadsheet_robot_stats = <pre>'.print_r($spreadsheet_robot_stats, true).'</pre>'."\n\n".
  //'$spreadsheet_mecha_stats = <pre>'.print_r($spreadsheet_mecha_stats, true).'</pre>'."\n\n".
  //'$spreadsheet_boss_stats = <pre>'.print_r($spreadsheet_boss_stats, true).'</pre>'."\n\n".
  //'$spreadsheet_robot_quotes = <pre>'.print_r($spreadsheet_robot_quotes, true).'</pre>'."\n\n".
  //'$spreadsheet_mecha_quotes = <pre>'.print_r($spreadsheet_mecha_quotes, true).'</pre>'."\n\n".
  //'$spreadsheet_boss_quotes = <pre>'.print_r($spreadsheet_boss_quotes, true).'</pre>'."\n\n".
  //'$spreadsheet_robot_descriptions = <pre>'.print_r($spreadsheet_robot_descriptions, true).'</pre>'."\n\n".
  //'$spreadsheet_mecha_descriptions = <pre>'.print_r($spreadsheet_mecha_descriptions, true).'</pre>'."\n\n".
  //'$spreadsheet_boss_descriptions = <pre>'.print_r($spreadsheet_boss_descriptions, true).'</pre>'."\n\n".
  '---');
*/

// Create the items and system array (subsets of abilities) for populating
//$mmrpg_index['system'] = array();
$mmrpg_index['mechas'] = array();
$mmrpg_index['bosses'] = array();

// Fill in potentially missing fields with defaults for sorting
if (!empty($mmrpg_index['robots'])){
  foreach ($mmrpg_index['robots'] AS $token => $robot){
    $robot['robot_class'] = isset($robot['robot_class']) ? $robot['robot_class'] : 'master';
    $robot['robot_number'] = !empty($robot['robot_number']) ? $robot['robot_number'] : '';
    $robot['robot_game'] = isset($robot['robot_game']) ? $robot['robot_game'] : 'MMRPG';
    $robot['robot_group'] = isset($robot['robot_group']) ? $robot['robot_group'] : $robot['robot_game'];
    $robot['robot_energy'] = isset($robot['robot_energy']) ? $robot['robot_energy'] : 10;
    $robot['robot_gender'] = !empty($robot['robot_gender']) ? $robot['robot_gender'] : ($robot['robot_class'] == 'master' ? 'male' : 'none');
    $robot['robot_core'] = !empty($robot['robot_core']) ? $robot['robot_core'] : '';
    $robot['robot_core2'] = !empty($robot['robot_core2']) ? $robot['robot_core2'] : '';
    if ($robot['robot_class'] == 'mecha'){
      $temp_field_token = $robot['robot_field'];
      $temp_field_info = $mmrpg_index['fields'][$temp_field_token];
      $temp_master_token = !empty($temp_field_info['field_master']) ? $temp_field_info['field_master'] : 'met';
      $temp_master_number = isset($mmrpg_index['robots'][$temp_master_token]) ? $mmrpg_index['robots'][$temp_master_token]['robot_number'] : $mmrpg_index['mechas'][$temp_master_token]['robot_number'];
      $robot['robot_master_number'] = $temp_master_number;
    } elseif ($robot['robot_class'] == 'master'){
      $robot['robot_master_number'] = $robot['robot_number'];
    } elseif ($robot['robot_class'] == 'boss'){
      $robot['robot_master_number'] = $robot['robot_number'];
    }
    $mmrpg_index['robots'][$token] = $robot;
    /*if ($robot['robot_class'] == 'system'){
      $mmrpg_index['system'][$token] = $robot;
      unset($mmrpg_index['robots'][$token]);
    } else*/
    if ($robot['robot_class'] == 'mecha'){
      $mmrpg_index['mechas'][$token] = $robot;
      unset($mmrpg_index['robots'][$token]);
    } elseif ($robot['robot_class'] == 'boss'){
      $mmrpg_index['bosses'][$token] = $robot;
      unset($mmrpg_index['robots'][$token]);
    }
  }
}




// -- MMRPG IMPORT ROBOTS -- //

// Sort the robot index based on robot number
$temp_first_robots = array('mega-man', 'bass', 'proto-man', 'roll', 'disco', 'rhythm');
$temp_last_robots = array('laser-man', 'shield-man', 'portal-man', 'prism-man', 'hallow-man', 'target-man', 'zephyr-woman', 'desert-man', 'blossom-woman', 'shark-man', 'magic-man', 'burner-man', 'pirate-man', 'ground-man', 'cold-man', 'dynamo-man');
$temp_serial_ordering = array(
	'DLN', // Dr. Light Number
	'DWN', // Dr. Wily Number
	'DCN', // Dr. Cossack Number
  'DLM'  // Dr. Light Mecha
  );
function mmrpg_index_sort_robots($robot_one, $robot_two){
  global $temp_first_robots, $temp_last_robots, $temp_serial_ordering;
  $robot_one['robot_game'] = !empty($robot_one['robot_game']) ? $robot_one['robot_game'] : 'MM00';
  $robot_two['robot_game'] = !empty($robot_two['robot_game']) ? $robot_two['robot_game'] : 'MM00';
  $robot_one['robot_class'] = !empty($robot_one['robot_class']) ? $robot_one['robot_class'] : 'master';
  $robot_two['robot_class'] = !empty($robot_two['robot_class']) ? $robot_two['robot_class'] : 'master';
  $robot_one['robot_token_position'] = array_search($robot_one['robot_token'], $temp_first_robots);
  $robot_two['robot_token_position'] = array_search($robot_two['robot_token'], $temp_first_robots);
  $robot_one['robot_token_position2'] = array_search($robot_one['robot_token'], $temp_last_robots);
  $robot_two['robot_token_position2'] = array_search($robot_two['robot_token'], $temp_last_robots);
  if ($robot_one['robot_token_position'] !== false && $robot_two['robot_token_position'] !== false){
    if ($robot_one['robot_token_position'] > $robot_two['robot_token_position']){ return 1; }
    elseif ($robot_one['robot_token_position'] < $robot_two['robot_token_position']){ return -1; }
    else { return 0; }
  }
  elseif ($robot_one['robot_token_position'] !== false || $robot_two['robot_token_position'] !== false){
    if ($robot_one['robot_token_position'] === false){ return 1; }
    elseif ($robot_two['robot_token_position'] === false){ return -1; }
    else { return 0; }
  }
  elseif ($robot_one['robot_token_position2'] !== false && $robot_two['robot_token_position2'] !== false){
    if ($robot_one['robot_token_position2'] > $robot_two['robot_token_position2']){ return -1; }
    elseif ($robot_one['robot_token_position2'] < $robot_two['robot_token_position2']){ return 1; }
    else { return 0; }
  }
  elseif ($robot_one['robot_token_position2'] !== false || $robot_two['robot_token_position2'] !== false){
    if ($robot_one['robot_token_position2'] === false){ return -1; }
    elseif ($robot_two['robot_token_position2'] === false){ return 1; }
    else { return 0; }
  }
  elseif ($robot_one['robot_token_position'] === false && $robot_two['robot_token_position'] === false){
    if ($robot_one['robot_class'] > $robot_two['robot_class']){ return 1; }
    elseif ($robot_one['robot_class'] < $robot_two['robot_class']){ return -1; }
    elseif ($robot_one['robot_game'] > $robot_two['robot_game']){ return 1; }
    elseif ($robot_one['robot_game'] < $robot_two['robot_game']){ return -1; }
    elseif ($robot_one['robot_master_number'] > $robot_two['robot_master_number']){ return 1; }
    elseif ($robot_one['robot_master_number'] < $robot_two['robot_master_number']){ return -1; }
    elseif ($robot_one['robot_number'] > $robot_two['robot_number']){ return 1; }
    elseif ($robot_one['robot_number'] < $robot_two['robot_number']){ return -1; }
    else { return 0; }
  }
  return 0;
}
uasort($mmrpg_index['robots'], 'mmrpg_index_sort_robots');

// Loop through each of the robot info arrays
$robot_key = 0;
$robot_order = 0;
$temp_empty = $mmrpg_index['robots']['robot'];
unset($mmrpg_index['robots']['robot']);
array_unshift($mmrpg_index['robots'], $temp_empty);
if (!empty($mmrpg_index['robots'])){
  foreach ($mmrpg_index['robots'] AS $robot_token => $robot_data){

    // If this robot's image exists, assign it
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$robot_token.'/')){ $robot_data['robot_image'] = $robot_data['robot_token']; }
    else { $robot_data['robot_image'] = !empty($robot_data['robot_class']) && $robot_data['robot_class'] != 'master' ? $robot_data['robot_class'] : 'robot'; }

    // Define the insert array and start populating it with basic details
    $temp_insert_array = array();
    //$temp_insert_array['robot_id'] = isset($robot_data['robot_id']) ? $robot_data['robot_id'] : $robot_key;
    $temp_insert_array['robot_token'] = $robot_data['robot_token'];
    $temp_insert_array['robot_number'] = !empty($robot_data['robot_number']) ? $robot_data['robot_number'] : '';
    $temp_insert_array['robot_name'] = !empty($robot_data['robot_name']) ? $robot_data['robot_name'] : '';
    $temp_insert_array['robot_game'] = !empty($robot_data['robot_game']) ? $robot_data['robot_game'] : '';
    $temp_insert_array['robot_group'] = !empty($robot_data['robot_group']) ? $robot_data['robot_group'] : '';
    $temp_insert_array['robot_field'] = !empty($robot_data['robot_field']) ? $robot_data['robot_field'] : 'field';
    $temp_insert_array['robot_field2'] = !empty($robot_data['robot_field2']) ? json_encode($robot_data['robot_field2']) : '';
    $temp_insert_array['robot_class'] = !empty($robot_data['robot_class']) ? $robot_data['robot_class'] : 'master';
    $temp_insert_array['robot_gender'] = !empty($robot_data['robot_gender']) ? $robot_data['robot_gender'] : ($temp_insert_array['robot_class'] == 'master' ? 'male' : 'none');
    $temp_insert_array['robot_image'] = !empty($robot_data['robot_image']) ? $robot_data['robot_image'] : '';
    $temp_insert_array['robot_image_size'] = !empty($robot_data['robot_image_size']) ? $robot_data['robot_image_size'] : 40;
    $temp_insert_array['robot_image_editor'] = !empty($robot_data['robot_image_editor']) ? $robot_data['robot_image_editor'] : 0;
    $temp_insert_array['robot_image_alts'] = json_encode(!empty($robot_data['robot_image_alts']) ? $robot_data['robot_image_alts'] : array());
    $temp_insert_array['robot_core'] = !empty($robot_data['robot_core']) ? $robot_data['robot_core'] : '';
    $temp_insert_array['robot_core2'] = !empty($robot_data['robot_core2']) ? $robot_data['robot_core2'] : '';
    $temp_insert_array['robot_description'] = !empty($robot_data['robot_description']) ? $robot_data['robot_description'] : '';
    $temp_insert_array['robot_description2'] = !empty($robot_data['robot_description2']) ? $robot_data['robot_description2'] : '';
    $temp_insert_array['robot_energy'] = !empty($robot_data['robot_energy']) ? $robot_data['robot_energy'] : 100;
    $temp_insert_array['robot_weapons'] = !empty($robot_data['robot_weapons']) ? $robot_data['robot_weapons'] : 10;
    $temp_insert_array['robot_attack'] = !empty($robot_data['robot_attack']) ? $robot_data['robot_attack'] : 100;
    $temp_insert_array['robot_defense'] = !empty($robot_data['robot_defense']) ? $robot_data['robot_defense'] : 100;
    $temp_insert_array['robot_speed'] = !empty($robot_data['robot_speed']) ? $robot_data['robot_speed'] : 100;
    $temp_insert_array['robot_functions'] = !empty($robot_data['robot_functions']) ? $robot_data['robot_functions'] : 'robots/robot.php';

    // Define weaknesses for this robot
    $temp_insert_array['robot_weaknesses'] = json_encode(!empty($robot_data['robot_weaknesses']) ? $robot_data['robot_weaknesses'] : array());
    //$temp_insert_array['robot_weaknesses'] = array();
    //if (!empty($robot_data['robot_weaknesses'])){ foreach ($robot_data['robot_weaknesses'] AS $key => $token){ $temp_insert_array['robot_weaknesses'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_weaknesses'] = implode(',', $temp_insert_array['robot_weaknesses']);

    // Define resistances for this robot
    $temp_insert_array['robot_resistances'] = json_encode(!empty($robot_data['robot_resistances']) ? $robot_data['robot_resistances'] : array());
    //$temp_insert_array['robot_resistances'] = array();
    //if (!empty($robot_data['robot_resistances'])){ foreach ($robot_data['robot_resistances'] AS $key => $token){ $temp_insert_array['robot_resistances'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_resistances'] = implode(',', $temp_insert_array['robot_resistances']);

    // Define affinities for this robot
    $temp_insert_array['robot_affinities'] = json_encode(!empty($robot_data['robot_affinities']) ? $robot_data['robot_affinities'] : array());
    //$temp_insert_array['robot_affinities'] = array();
    //if (!empty($robot_data['robot_affinities'])){ foreach ($robot_data['robot_affinities'] AS $key => $token){ $temp_insert_array['robot_affinities'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_affinities'] = implode(',', $temp_insert_array['robot_affinities']);

    // Define immunities for this robot
    $temp_insert_array['robot_immunities'] = json_encode(!empty($robot_data['robot_immunities']) ? $robot_data['robot_immunities'] : array());
    //$temp_insert_array['robot_immunities'] = array();
    //if (!empty($robot_data['robot_immunities'])){ foreach ($robot_data['robot_immunities'] AS $key => $token){ $temp_insert_array['robot_immunities'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_immunities'] = implode(',', $temp_insert_array['robot_immunities']);

    // Define the ability rewards for this robot
    $temp_insert_array['robot_abilities_rewards'] = json_encode(!empty($robot_data['robot_rewards']['abilities']) ? $robot_data['robot_rewards']['abilities'] : array());
    //$temp_insert_array['robot_abilities_rewards'] = array();
    //if (!empty($robot_data['robot_rewards']['abilities'])){ foreach ($robot_data['robot_rewards']['abilities'] AS $key => $info){ $temp_insert_array['robot_abilities_rewards'][] = '['.$info['level'].':'.$info['token'].']'; } }
    //$temp_insert_array['robot_abilities_rewards'] = implode(',', $temp_insert_array['robot_abilities_rewards']);

    // Define immunities for this robot
    $temp_insert_array['robot_abilities_compatible'] = json_encode(!empty($robot_data['robot_abilities']) ? $robot_data['robot_abilities'] : array());
    //$temp_insert_array['robot_abilities_compatible'] = array();
    //if (!empty($robot_data['robot_abilities'])){ foreach ($robot_data['robot_abilities'] AS $key => $token){ $temp_insert_array['robot_abilities_compatible'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_abilities_compatible'] = implode(',', $temp_insert_array['robot_abilities_compatible']);


    // Define the battle quotes for this robot
    if (!empty($robot_data['robot_quotes'])){ foreach ($robot_data['robot_quotes'] AS $key => $quote){ $robot_data['robot_quotes'][$key] = html_entity_decode($quote, ENT_QUOTES, 'UTF-8'); } }
    $temp_insert_array['robot_quotes_start'] = !empty($robot_data['robot_quotes']['battle_start']) && $robot_data['robot_quotes']['battle_start'] != '...' ? $robot_data['robot_quotes']['battle_start'] : '';
    $temp_insert_array['robot_quotes_taunt'] = !empty($robot_data['robot_quotes']['battle_taunt']) && $robot_data['robot_quotes']['battle_taunt'] != '...' ? $robot_data['robot_quotes']['battle_taunt'] : '';
    $temp_insert_array['robot_quotes_victory'] = !empty($robot_data['robot_quotes']['battle_victory']) && $robot_data['robot_quotes']['battle_victory'] != '...' ? $robot_data['robot_quotes']['battle_victory'] : '';
    $temp_insert_array['robot_quotes_defeat'] = !empty($robot_data['robot_quotes']['battle_defeat']) && $robot_data['robot_quotes']['battle_defeat'] != '...' ? $robot_data['robot_quotes']['battle_defeat'] : '';


    // Collect applicable spreadsheets for this robot
    if ($temp_insert_array['robot_class'] == 'master'){
      $robot_data['robot_class'] = 'master';
      $spreadsheet_stats = !empty($spreadsheet_robot_stats[$robot_data['robot_token']]) ? $spreadsheet_robot_stats[$robot_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_robot_quotes[$robot_data['robot_token']]) ? $spreadsheet_robot_quotes[$robot_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_robot_descriptions[$robot_data['robot_token']]) ? $spreadsheet_robot_descriptions[$robot_data['robot_token']] : array();
    } elseif ($temp_insert_array['robot_class'] == 'mecha'){
      $robot_data['robot_class'] = 'mecha';
      $spreadsheet_stats = !empty($spreadsheet_mecha_stats[$robot_data['robot_token']]) ? $spreadsheet_mecha_stats[$robot_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_mecha_quotes[$robot_data['robot_token']]) ? $spreadsheet_mecha_quotes[$robot_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_mecha_descriptions[$robot_data['robot_token']]) ? $spreadsheet_mecha_descriptions[$robot_data['robot_token']] : array();
    } elseif ($temp_insert_array['robot_class'] == 'boss'){
      $robot_data['robot_class'] = 'boss';
      $spreadsheet_stats = !empty($spreadsheet_robot_stats[$robot_data['robot_token']]) ? $spreadsheet_robot_stats[$robot_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_robot_quotes[$robot_data['robot_token']]) ? $spreadsheet_robot_quotes[$robot_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_robot_descriptions[$robot_data['robot_token']]) ? $spreadsheet_robot_descriptions[$robot_data['robot_token']] : array();
    }
    /*elseif ($temp_insert_array['robot_class'] == 'boss'){
      $spreadsheet_stats = !empty($spreadsheet_boss_stats[$robot_data['robot_token']]) ? $spreadsheet_boss_stats[$robot_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_boss_quotes[$robot_data['robot_token']]) ? $spreadsheet_boss_quotes[$robot_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_boss_descriptions[$robot_data['robot_token']]) ? $spreadsheet_boss_descriptions[$robot_data['robot_token']] : array();
    }*/

    // Collect any user-contributed data for this robot
    if (!empty($spreadsheet_stats['energy'])){ $temp_insert_array['robot_energy'] = $spreadsheet_stats['energy']; }
    if (!empty($spreadsheet_stats['attack'])){ $temp_insert_array['robot_attack'] = $spreadsheet_stats['attack']; }
    if (!empty($spreadsheet_stats['defense'])){ $temp_insert_array['robot_defense'] = $spreadsheet_stats['defense']; }
    if (!empty($spreadsheet_stats['speed'])){ $temp_insert_array['robot_speed'] = $spreadsheet_stats['speed']; }
    if (!empty($spreadsheet_quotes['battle_start'])){ $temp_insert_array['robot_quotes_start'] = trim($spreadsheet_quotes['battle_start']); }
    if (!empty($spreadsheet_quotes['battle_taunt'])){ $temp_insert_array['robot_quotes_taunt'] = trim($spreadsheet_quotes['battle_taunt']); }
    if (!empty($spreadsheet_quotes['battle_victory'])){ $temp_insert_array['robot_quotes_victory'] = trim($spreadsheet_quotes['battle_victory']); }
    if (!empty($spreadsheet_quotes['battle_defeat'])){ $temp_insert_array['robot_quotes_defeat'] = trim($spreadsheet_quotes['battle_defeat']); }
    if ($temp_insert_array['robot_class'] == 'master'){
      if (!empty($spreadsheet_descriptions['robot_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['robot_class']); }
      if (!empty($spreadsheet_descriptions['robot_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['robot_description']); }
    } elseif ($temp_insert_array['robot_class'] == 'mecha'){
      if (!empty($spreadsheet_descriptions['mecha_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['mecha_class']); }
      if (!empty($spreadsheet_descriptions['mecha_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['mecha_description']); }
    } elseif ($temp_insert_array['robot_class'] == 'boss'){
      if (!empty($spreadsheet_descriptions['robot_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['robot_class']); }
      if (!empty($spreadsheet_descriptions['robot_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['robot_description']); }
    }
     /*elseif ($temp_insert_array['robot_class'] == 'boss'){
      if (!empty($spreadsheet_descriptions['boss_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['boss_class']); }
      if (!empty($spreadsheet_descriptions['boss_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['boss_description']); }
    }*/

    // Define the flags
    $temp_insert_array['robot_flag_hidden'] = $temp_insert_array['robot_class'] == 'mecha' || $temp_insert_array['robot_class'] == 'boss' || in_array($temp_insert_array['robot_token'], array('bond-man', 'fake-man', 'cache', 'rock')) ? 1 : 0;
    $temp_insert_array['robot_flag_complete'] = $robot_data['robot_image'] != 'robot' && $robot_data['robot_image'] != $robot_data['robot_class'] ? 1 : 0;
    $temp_insert_array['robot_flag_published'] = 1;

    // Define the order counter
    if ($temp_insert_array['robot_class'] != 'system'){
      $temp_insert_array['robot_order'] = $robot_order;
      $robot_order++;
    } else {
      $temp_insert_array['robot_order'] = 0;
    }

    // Check if this robot already exists in the database
    $temp_success = true;
    $temp_exists = $DB->get_array("SELECT robot_token FROM mmrpg_index_robots WHERE robot_token LIKE '{$temp_insert_array['robot_token']}' LIMIT 1") ? true : false;
    if (!$temp_exists){ $temp_success = $DB->insert('mmrpg_index_robots', $temp_insert_array); }
    else { $temp_success = $DB->update('mmrpg_index_robots', $temp_insert_array, array('robot_token' => $temp_insert_array['robot_token'])); }

    // Print out the generated insert array
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
    $this_page_markup .= '<strong>$mmrpg_database_robots['.$robot_token.']</strong><br />';
    //$this_page_markup .= '<pre>'.print_r($robot_data, true).'</pre><br /><hr /><br />';
    $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
    //$this_page_markup .= '<pre>'.print_r(mmrpg_robot::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
    $this_page_markup .= '</p><hr />';

    $robot_key++;

    //if ($robot_data['robot_token'] == 'met'){ die('met = <pre>'.print_r($temp_insert_array, true).'</pre>'); }
    //die('end');

  }
}
// Otherwise, if empty, we're done!
else {
  $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL ROBOT HAVE BEEN IMPORTED UPDATED!</strong></p>';
}




// -- MMRPG IMPORT MECHAS -- //

// Sort the mecha index based on mecha number
$temp_first_mechas = array('met', 'sniper-joe', 'skeleton-joe', 'crystal-joe');
$temp_last_mechas = array('beak', 'dark-frag', 'dark-spire', 'dark-tower');
$temp_last_mechas = array_reverse($temp_last_mechas);
$temp_serial_ordering = array(
	'DLN', // Dr. Light Number
	'DWN', // Dr. Wily Number
	'DCN', // Dr. Cossack Number
  'DLM'  // Dr. Light Robot
  );
function mmrpg_index_sort_mechas($mecha_one, $mecha_two){
  global $temp_first_mechas, $temp_last_mechas, $temp_serial_ordering;
  $mecha_one['robot_game'] = !empty($mecha_one['robot_game']) ? $mecha_one['robot_game'] : 'MM00';
  $mecha_two['robot_game'] = !empty($mecha_two['robot_game']) ? $mecha_two['robot_game'] : 'MM00';
  $mecha_one['robot_class'] = !empty($mecha_one['robot_class']) ? $mecha_one['robot_class'] : 'master';
  $mecha_two['robot_class'] = !empty($mecha_two['robot_class']) ? $mecha_two['robot_class'] : 'master';
  $mecha_one['robot_first_position'] = array_search($mecha_one['robot_token'], $temp_first_mechas);
  $mecha_two['robot_first_position'] = array_search($mecha_two['robot_token'], $temp_first_mechas);
  $mecha_one['robot_last_position'] = array_search($mecha_one['robot_token'], $temp_last_mechas);
  $mecha_two['robot_last_position'] = array_search($mecha_two['robot_token'], $temp_last_mechas);
  if ($mecha_one['robot_first_position'] !== false && $mecha_two['robot_first_position'] !== false){
    if ($mecha_one['robot_first_position'] > $mecha_two['robot_first_position']){ return 1; }
    elseif ($mecha_one['robot_first_position'] < $mecha_two['robot_first_position']){ return -1; }
    else { return 0; }
  }
  elseif ($mecha_one['robot_first_position'] !== false || $mecha_two['robot_first_position'] !== false){
    if ($mecha_one['robot_first_position'] === false){ return 1; }
    elseif ($mecha_two['robot_first_position'] === false){ return -1; }
    else { return 0; }
  }
  elseif ($mecha_one['robot_last_position'] !== false && $mecha_two['robot_last_position'] !== false){
    if ($mecha_one['robot_last_position'] > $mecha_two['robot_last_position']){ return -1; }
    elseif ($mecha_one['robot_last_position'] < $mecha_two['robot_last_position']){ return 1; }
    else { return 0; }
  }
  elseif ($mecha_one['robot_last_position'] !== false || $mecha_two['robot_last_position'] !== false){
    if ($mecha_one['robot_last_position'] === false){ return -1; }
    elseif ($mecha_two['robot_last_position'] === false){ return 1; }
    else { return 0; }
  }
  elseif ($mecha_one['robot_first_position'] === false && $mecha_two['robot_first_position'] === false){
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
uasort($mmrpg_index['mechas'], 'mmrpg_index_sort_mechas');

// Loop through each of the robot info arrays
$mecha_key = 0;
$mecha_order = 0;
//$temp_empty = $mmrpg_index['mechas']['robot'];
//unset($mmrpg_index['mechas']['robot']);
//array_unshift($mmrpg_index['mechas'], $temp_empty);
if (!empty($mmrpg_index['mechas'])){
  foreach ($mmrpg_index['mechas'] AS $mecha_token => $mecha_data){

    // If this robot's image exists, assign it
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$mecha_token.'/')){ $mecha_data['robot_image'] = $mecha_data['robot_token']; }
    else { $mecha_data['robot_image'] = !empty($mecha_data['robot_class']) && $mecha_data['robot_class'] != 'master' ? $mecha_data['robot_class'] : 'robot'; }

    // Define the insert array and start populating it with basic details
    $temp_insert_array = array();
    //$temp_insert_array['robot_id'] = isset($mecha_data['robot_id']) ? $mecha_data['robot_id'] : $mecha_key;
    $temp_insert_array['robot_token'] = $mecha_data['robot_token'];
    $temp_insert_array['robot_number'] = !empty($mecha_data['robot_number']) ? $mecha_data['robot_number'] : '';
    $temp_insert_array['robot_name'] = !empty($mecha_data['robot_name']) ? $mecha_data['robot_name'] : '';
    $temp_insert_array['robot_game'] = !empty($mecha_data['robot_game']) ? $mecha_data['robot_game'] : '';
    $temp_insert_array['robot_field'] = !empty($mecha_data['robot_field']) ? $mecha_data['robot_field'] : 'field';
    $temp_insert_array['robot_field2'] = !empty($mecha_data['robot_field2']) ? json_encode($mecha_data['robot_field2']) : '';
    $temp_insert_array['robot_class'] = !empty($mecha_data['robot_class']) ? $mecha_data['robot_class'] : 'master';
    $temp_insert_array['robot_gender'] = !empty($mecha_data['robot_gender']) ? $mecha_data['robot_gender'] : ($temp_insert_array['robot_class'] == 'master' ? 'male' : 'none');
    $temp_insert_array['robot_image'] = !empty($mecha_data['robot_image']) ? $mecha_data['robot_image'] : '';
    $temp_insert_array['robot_image_size'] = !empty($mecha_data['robot_image_size']) ? $mecha_data['robot_image_size'] : 40;
    $temp_insert_array['robot_image_editor'] = !empty($mecha_data['robot_image_editor']) ? $mecha_data['robot_image_editor'] : 0;
    $temp_insert_array['robot_image_alts'] = json_encode(!empty($mecha_data['robot_image_alts']) ? $mecha_data['robot_image_alts'] : array());
    $temp_insert_array['robot_core'] = !empty($mecha_data['robot_core']) ? $mecha_data['robot_core'] : '';
    $temp_insert_array['robot_core2'] = !empty($mecha_data['robot_core2']) ? $mecha_data['robot_core2'] : '';
    $temp_insert_array['robot_description'] = !empty($mecha_data['robot_description']) ? $mecha_data['robot_description'] : '';
    $temp_insert_array['robot_description2'] = !empty($mecha_data['robot_description2']) ? $mecha_data['robot_description2'] : '';
    $temp_insert_array['robot_energy'] = !empty($mecha_data['robot_energy']) ? $mecha_data['robot_energy'] : 100;
    $temp_insert_array['robot_weapons'] = !empty($mecha_data['robot_weapons']) ? $mecha_data['robot_weapons'] : 5;
    $temp_insert_array['robot_attack'] = !empty($mecha_data['robot_attack']) ? $mecha_data['robot_attack'] : 100;
    $temp_insert_array['robot_defense'] = !empty($mecha_data['robot_defense']) ? $mecha_data['robot_defense'] : 100;
    $temp_insert_array['robot_speed'] = !empty($mecha_data['robot_speed']) ? $mecha_data['robot_speed'] : 100;
    $temp_insert_array['robot_functions'] = !empty($mecha_data['robot_functions']) ? $mecha_data['robot_functions'] : 'robots/robot.php';

    // Define weaknesses for this robot
    $temp_insert_array['robot_weaknesses'] = json_encode(!empty($mecha_data['robot_weaknesses']) ? $mecha_data['robot_weaknesses'] : array());
    //$temp_insert_array['robot_weaknesses'] = array();
    //if (!empty($mecha_data['robot_weaknesses'])){ foreach ($mecha_data['robot_weaknesses'] AS $key => $token){ $temp_insert_array['robot_weaknesses'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_weaknesses'] = implode(',', $temp_insert_array['robot_weaknesses']);

    // Define resistances for this robot
    $temp_insert_array['robot_resistances'] = json_encode(!empty($mecha_data['robot_resistances']) ? $mecha_data['robot_resistances'] : array());
    //$temp_insert_array['robot_resistances'] = array();
    //if (!empty($mecha_data['robot_resistances'])){ foreach ($mecha_data['robot_resistances'] AS $key => $token){ $temp_insert_array['robot_resistances'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_resistances'] = implode(',', $temp_insert_array['robot_resistances']);

    // Define affinities for this robot
    $temp_insert_array['robot_affinities'] = json_encode(!empty($mecha_data['robot_affinities']) ? $mecha_data['robot_affinities'] : array());
    //$temp_insert_array['robot_affinities'] = array();
    //if (!empty($mecha_data['robot_affinities'])){ foreach ($mecha_data['robot_affinities'] AS $key => $token){ $temp_insert_array['robot_affinities'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_affinities'] = implode(',', $temp_insert_array['robot_affinities']);

    // Define immunities for this robot
    $temp_insert_array['robot_immunities'] = json_encode(!empty($mecha_data['robot_immunities']) ? $mecha_data['robot_immunities'] : array());
    //$temp_insert_array['robot_immunities'] = array();
    //if (!empty($mecha_data['robot_immunities'])){ foreach ($mecha_data['robot_immunities'] AS $key => $token){ $temp_insert_array['robot_immunities'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_immunities'] = implode(',', $temp_insert_array['robot_immunities']);

    // Define the ability rewards for this robot
    $temp_insert_array['robot_abilities_rewards'] = json_encode(!empty($mecha_data['robot_rewards']['abilities']) ? $mecha_data['robot_rewards']['abilities'] : array());
    //$temp_insert_array['robot_abilities_rewards'] = array();
    //if (!empty($mecha_data['robot_rewards']['abilities'])){ foreach ($mecha_data['robot_rewards']['abilities'] AS $key => $info){ $temp_insert_array['robot_abilities_rewards'][] = '['.$info['level'].':'.$info['token'].']'; } }
    //$temp_insert_array['robot_abilities_rewards'] = implode(',', $temp_insert_array['robot_abilities_rewards']);

    // Define immunities for this robot
    $temp_insert_array['robot_abilities_compatible'] = json_encode(!empty($mecha_data['robot_abilities']) ? $mecha_data['robot_abilities'] : array());
    //$temp_insert_array['robot_abilities_compatible'] = array();
    //if (!empty($mecha_data['robot_abilities'])){ foreach ($mecha_data['robot_abilities'] AS $key => $token){ $temp_insert_array['robot_abilities_compatible'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_abilities_compatible'] = implode(',', $temp_insert_array['robot_abilities_compatible']);


    // Define the battle quotes for this robot
    if (!empty($mecha_data['robot_quotes'])){ foreach ($mecha_data['robot_quotes'] AS $key => $quote){ $mecha_data['robot_quotes'][$key] = html_entity_decode($quote, ENT_QUOTES, 'UTF-8'); } }
    $temp_insert_array['robot_quotes_start'] = !empty($mecha_data['robot_quotes']['battle_start']) && $mecha_data['robot_quotes']['battle_start'] != '...' ? $mecha_data['robot_quotes']['battle_start'] : '';
    $temp_insert_array['robot_quotes_taunt'] = !empty($mecha_data['robot_quotes']['battle_taunt']) && $mecha_data['robot_quotes']['battle_taunt'] != '...' ? $mecha_data['robot_quotes']['battle_taunt'] : '';
    $temp_insert_array['robot_quotes_victory'] = !empty($mecha_data['robot_quotes']['battle_victory']) && $mecha_data['robot_quotes']['battle_victory'] != '...' ? $mecha_data['robot_quotes']['battle_victory'] : '';
    $temp_insert_array['robot_quotes_defeat'] = !empty($mecha_data['robot_quotes']['battle_defeat']) && $mecha_data['robot_quotes']['battle_defeat'] != '...' ? $mecha_data['robot_quotes']['battle_defeat'] : '';


    // Collect applicable spreadsheets for this robot
    if ($temp_insert_array['robot_class'] == 'master'){
      $mecha_data['robot_class'] = 'master';
      $spreadsheet_stats = !empty($spreadsheet_robot_stats[$mecha_data['robot_token']]) ? $spreadsheet_robot_stats[$mecha_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_robot_quotes[$mecha_data['robot_token']]) ? $spreadsheet_robot_quotes[$mecha_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_robot_descriptions[$mecha_data['robot_token']]) ? $spreadsheet_robot_descriptions[$mecha_data['robot_token']] : array();
    } elseif ($temp_insert_array['robot_class'] == 'mecha'){
      $mecha_data['robot_class'] = 'mecha';
      $spreadsheet_stats = !empty($spreadsheet_mecha_stats[$mecha_data['robot_token']]) ? $spreadsheet_mecha_stats[$mecha_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_mecha_quotes[$mecha_data['robot_token']]) ? $spreadsheet_mecha_quotes[$mecha_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_mecha_descriptions[$mecha_data['robot_token']]) ? $spreadsheet_mecha_descriptions[$mecha_data['robot_token']] : array();
    } elseif ($temp_insert_array['robot_class'] == 'boss'){
      $mecha_data['robot_class'] = 'boss';
      $spreadsheet_stats = !empty($spreadsheet_robot_stats[$mecha_data['robot_token']]) ? $spreadsheet_robot_stats[$mecha_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_robot_quotes[$mecha_data['robot_token']]) ? $spreadsheet_robot_quotes[$mecha_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_robot_descriptions[$mecha_data['robot_token']]) ? $spreadsheet_robot_descriptions[$mecha_data['robot_token']] : array();
    }
    /*elseif ($temp_insert_array['robot_class'] == 'boss'){
      $spreadsheet_stats = !empty($spreadsheet_boss_stats[$mecha_data['robot_token']]) ? $spreadsheet_boss_stats[$mecha_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_boss_quotes[$mecha_data['robot_token']]) ? $spreadsheet_boss_quotes[$mecha_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_boss_descriptions[$mecha_data['robot_token']]) ? $spreadsheet_boss_descriptions[$mecha_data['robot_token']] : array();
    }*/

    // Collect any user-contributed data for this robot
    if (!empty($spreadsheet_stats['energy'])){ $temp_insert_array['robot_energy'] = $spreadsheet_stats['energy']; }
    if (!empty($spreadsheet_stats['attack'])){ $temp_insert_array['robot_attack'] = $spreadsheet_stats['attack']; }
    if (!empty($spreadsheet_stats['defense'])){ $temp_insert_array['robot_defense'] = $spreadsheet_stats['defense']; }
    if (!empty($spreadsheet_stats['speed'])){ $temp_insert_array['robot_speed'] = $spreadsheet_stats['speed']; }
    if (!empty($spreadsheet_quotes['battle_start'])){ $temp_insert_array['robot_quotes_start'] = trim($spreadsheet_quotes['battle_start']); }
    if (!empty($spreadsheet_quotes['battle_taunt'])){ $temp_insert_array['robot_quotes_taunt'] = trim($spreadsheet_quotes['battle_taunt']); }
    if (!empty($spreadsheet_quotes['battle_victory'])){ $temp_insert_array['robot_quotes_victory'] = trim($spreadsheet_quotes['battle_victory']); }
    if (!empty($spreadsheet_quotes['battle_defeat'])){ $temp_insert_array['robot_quotes_defeat'] = trim($spreadsheet_quotes['battle_defeat']); }
    if ($temp_insert_array['robot_class'] == 'master'){
      if (!empty($spreadsheet_descriptions['robot_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['robot_class']); }
      if (!empty($spreadsheet_descriptions['robot_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['robot_description']); }
    } elseif ($temp_insert_array['robot_class'] == 'mecha'){
      if (!empty($spreadsheet_descriptions['mecha_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['mecha_class']); }
      if (!empty($spreadsheet_descriptions['mecha_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['mecha_description']); }
    } elseif ($temp_insert_array['robot_class'] == 'boss'){
      if (!empty($spreadsheet_descriptions['robot_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['robot_class']); }
      if (!empty($spreadsheet_descriptions['robot_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['robot_description']); }
    }
     /*elseif ($temp_insert_array['robot_class'] == 'boss'){
      if (!empty($spreadsheet_descriptions['boss_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['boss_class']); }
      if (!empty($spreadsheet_descriptions['boss_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['boss_description']); }
    }*/

    // Define the flags
    $temp_insert_array['robot_flag_hidden'] = $temp_insert_array['robot_class'] == 'mecha' || $temp_insert_array['robot_class'] == 'boss' || in_array($temp_insert_array['robot_token'], array('bond-man', 'fake-man', 'cache', 'rock')) ? 1 : 0;
    $temp_insert_array['robot_flag_complete'] = $mecha_data['robot_image'] != 'robot' && $mecha_data['robot_image'] != $mecha_data['robot_class'] ? 1 : 0;
    $temp_insert_array['robot_flag_published'] = 1;

    // Define the order counter
    if ($temp_insert_array['robot_class'] != 'system'){
      $temp_insert_array['robot_order'] = $mecha_order;
      $mecha_order++;
    } else {
      $temp_insert_array['robot_order'] = 0;
    }

    // Check if this robot already exists in the database
    $temp_success = true;
    $temp_exists = $DB->get_array("SELECT robot_token FROM mmrpg_index_robots WHERE robot_token LIKE '{$temp_insert_array['robot_token']}' LIMIT 1") ? true : false;
    if (!$temp_exists){ $temp_success = $DB->insert('mmrpg_index_robots', $temp_insert_array); }
    else { $temp_success = $DB->update('mmrpg_index_robots', $temp_insert_array, array('robot_token' => $temp_insert_array['robot_token'])); }

    // Print out the generated insert array
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
    $this_page_markup .= '<strong>$mmrpg_database_mechas['.$mecha_token.']</strong><br />';
    //$this_page_markup .= '<pre>'.print_r($mecha_data, true).'</pre><br /><hr /><br />';
    $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
    //$this_page_markup .= '<pre>'.print_r(mmrpg_robot::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
    $this_page_markup .= '</p><hr />';

    $mecha_key++;

    //if ($mecha_data['robot_token'] == 'met'){ die('met = <pre>'.print_r($temp_insert_array, true).'</pre>'); }
    //die('end');

  }
}
// Otherwise, if empty, we're done!
else {
  $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL MECHA HAVE BEEN IMPORTED UPDATED!</strong></p>';
}




// -- MMRPG IMPORT BOSSES -- //

// Sort the boss index based on boss number
$temp_first_bosses = array('doc-robot', 'enker', 'punk', 'ballade', 'quint', 'mega-man-ds', 'proto-man-ds', 'bass-ds', 'roll-ds', 'rhythm-ds', 'disco-ds', 'dark-man', 'dark-man-2', 'dark-man-3', 'dark-man-4', 'king', 'buster-rod-g', 'mega-water-s', 'hyper-storm-h');
//$temp_last_bosses = array('ra-moon', 'cosmo-man', 'planet-man', 'slur', 'trill', 'solo', 'duo-2', 'duo', 'trio-3', 'trio-2', 'trio');
$temp_last_bosses = array('trill', 'slur', 'planet-man', 'cosmo-man', 'trio', 'trio-2', 'trio-3', 'duo', 'duo-2', 'solo');
$temp_last_bosses = array_reverse($temp_last_bosses);
$temp_serial_ordering = array(
	'DLN', // Dr. Light Number
	'DWN', // Dr. Wily Number
	'DCN', // Dr. Cossack Number
  'DLM'  // Dr. Light Robot
  );
function mmrpg_index_sort_bosses($boss_one, $boss_two){
  global $temp_first_bosses, $temp_last_bosses, $temp_serial_ordering;
  $boss_one['robot_game'] = !empty($boss_one['robot_game']) ? $boss_one['robot_game'] : 'MM00';
  $boss_two['robot_game'] = !empty($boss_two['robot_game']) ? $boss_two['robot_game'] : 'MM00';
  $boss_one['robot_class'] = !empty($boss_one['robot_class']) ? $boss_one['robot_class'] : 'master';
  $boss_two['robot_class'] = !empty($boss_two['robot_class']) ? $boss_two['robot_class'] : 'master';
  $boss_one['robot_token_position'] = array_search($boss_one['robot_token'], $temp_first_bosses);
  $boss_two['robot_token_position'] = array_search($boss_two['robot_token'], $temp_first_bosses);
  $boss_one['robot_token_position2'] = array_search($boss_one['robot_token'], $temp_last_bosses);
  $boss_two['robot_token_position2'] = array_search($boss_two['robot_token'], $temp_last_bosses);
  if ($boss_one['robot_token_position'] !== false && $boss_two['robot_token_position'] !== false){
    if ($boss_one['robot_token_position'] > $boss_two['robot_token_position']){ return 1; }
    elseif ($boss_one['robot_token_position'] < $boss_two['robot_token_position']){ return -1; }
    else { return 0; }
  }
  elseif ($boss_one['robot_token_position'] !== false || $boss_two['robot_token_position'] !== false){
    if ($boss_one['robot_token_position'] === false){ return 1; }
    elseif ($boss_two['robot_token_position'] === false){ return -1; }
    else { return 0; }
  }
  elseif ($boss_one['robot_token_position2'] !== false && $boss_two['robot_token_position2'] !== false){
    if ($boss_one['robot_token_position2'] > $boss_two['robot_token_position2']){ return -1; }
    elseif ($boss_one['robot_token_position2'] < $boss_two['robot_token_position2']){ return 1; }
    else { return 0; }
  }
  elseif ($boss_one['robot_token_position2'] !== false || $boss_two['robot_token_position2'] !== false){
    if ($boss_one['robot_token_position2'] === false){ return -1; }
    elseif ($boss_two['robot_token_position2'] === false){ return 1; }
    else { return 0; }
  }
  elseif ($boss_one['robot_token_position'] === false && $boss_two['robot_token_position'] === false){
    if ($boss_one['robot_class'] > $boss_two['robot_class']){ return 1; }
    elseif ($boss_one['robot_class'] < $boss_two['robot_class']){ return -1; }
    elseif ($boss_one['robot_game'] > $boss_two['robot_game']){ return 1; }
    elseif ($boss_one['robot_game'] < $boss_two['robot_game']){ return -1; }
    elseif ($boss_one['robot_master_number'] > $boss_two['robot_master_number']){ return 1; }
    elseif ($boss_one['robot_master_number'] < $boss_two['robot_master_number']){ return -1; }
    elseif ($boss_one['robot_number'] > $boss_two['robot_number']){ return 1; }
    elseif ($boss_one['robot_number'] < $boss_two['robot_number']){ return -1; }
    else { return 0; }
  }
  return 0;
}
uasort($mmrpg_index['bosses'], 'mmrpg_index_sort_bosses');

// Loop through each of the robot info arrays
$boss_key = 0;
$boss_order = 0;
//$temp_empty = $mmrpg_index['bosses']['robot'];
//unset($mmrpg_index['bosses']['robot']);
//array_unshift($mmrpg_index['bosses'], $temp_empty);
if (!empty($mmrpg_index['bosses'])){
  foreach ($mmrpg_index['bosses'] AS $boss_token => $boss_data){

    // If this robot's image exists, assign it
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$boss_token.'/')){ $boss_data['robot_image'] = $boss_data['robot_token']; }
    else { $boss_data['robot_image'] = !empty($boss_data['robot_class']) && $boss_data['robot_class'] != 'master' ? $boss_data['robot_class'] : 'robot'; }

    // Define the insert array and start populating it with basic details
    $temp_insert_array = array();
    //$temp_insert_array['robot_id'] = isset($boss_data['robot_id']) ? $boss_data['robot_id'] : $boss_key;
    $temp_insert_array['robot_token'] = $boss_data['robot_token'];
    $temp_insert_array['robot_number'] = !empty($boss_data['robot_number']) ? $boss_data['robot_number'] : '';
    $temp_insert_array['robot_name'] = !empty($boss_data['robot_name']) ? $boss_data['robot_name'] : '';
    $temp_insert_array['robot_game'] = !empty($boss_data['robot_game']) ? $boss_data['robot_game'] : '';
    $temp_insert_array['robot_field'] = !empty($boss_data['robot_field']) ? $boss_data['robot_field'] : 'field';
    $temp_insert_array['robot_field2'] = !empty($boss_data['robot_field2']) ? json_encode($boss_data['robot_field2']) : '';
    $temp_insert_array['robot_class'] = !empty($boss_data['robot_class']) ? $boss_data['robot_class'] : 'master';
    $temp_insert_array['robot_gender'] = !empty($boss_data['robot_gender']) ? $boss_data['robot_gender'] : ($temp_insert_array['robot_class'] == 'master' ? 'male' : 'none');
    $temp_insert_array['robot_image'] = !empty($boss_data['robot_image']) ? $boss_data['robot_image'] : '';
    $temp_insert_array['robot_image_size'] = !empty($boss_data['robot_image_size']) ? $boss_data['robot_image_size'] : 40;
    $temp_insert_array['robot_image_editor'] = !empty($boss_data['robot_image_editor']) ? $boss_data['robot_image_editor'] : 0;
    $temp_insert_array['robot_image_alts'] = json_encode(!empty($boss_data['robot_image_alts']) ? $boss_data['robot_image_alts'] : array());
    $temp_insert_array['robot_core'] = !empty($boss_data['robot_core']) ? $boss_data['robot_core'] : '';
    $temp_insert_array['robot_core2'] = !empty($boss_data['robot_core2']) ? $boss_data['robot_core2'] : '';
    $temp_insert_array['robot_description'] = !empty($boss_data['robot_description']) ? $boss_data['robot_description'] : '';
    $temp_insert_array['robot_description2'] = !empty($boss_data['robot_description2']) ? $boss_data['robot_description2'] : '';
    $temp_insert_array['robot_energy'] = !empty($boss_data['robot_energy']) ? $boss_data['robot_energy'] : 100;
    $temp_insert_array['robot_weapons'] = !empty($boss_data['robot_weapons']) ? $boss_data['robot_weapons'] : 20;
    $temp_insert_array['robot_attack'] = !empty($boss_data['robot_attack']) ? $boss_data['robot_attack'] : 100;
    $temp_insert_array['robot_defense'] = !empty($boss_data['robot_defense']) ? $boss_data['robot_defense'] : 100;
    $temp_insert_array['robot_speed'] = !empty($boss_data['robot_speed']) ? $boss_data['robot_speed'] : 100;
    $temp_insert_array['robot_functions'] = !empty($boss_data['robot_functions']) ? $boss_data['robot_functions'] : 'robots/robot.php';

    // Define weaknesses for this robot
    $temp_insert_array['robot_weaknesses'] = json_encode(!empty($boss_data['robot_weaknesses']) ? $boss_data['robot_weaknesses'] : array());
    //$temp_insert_array['robot_weaknesses'] = array();
    //if (!empty($boss_data['robot_weaknesses'])){ foreach ($boss_data['robot_weaknesses'] AS $key => $token){ $temp_insert_array['robot_weaknesses'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_weaknesses'] = implode(',', $temp_insert_array['robot_weaknesses']);

    // Define resistances for this robot
    $temp_insert_array['robot_resistances'] = json_encode(!empty($boss_data['robot_resistances']) ? $boss_data['robot_resistances'] : array());
    //$temp_insert_array['robot_resistances'] = array();
    //if (!empty($boss_data['robot_resistances'])){ foreach ($boss_data['robot_resistances'] AS $key => $token){ $temp_insert_array['robot_resistances'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_resistances'] = implode(',', $temp_insert_array['robot_resistances']);

    // Define affinities for this robot
    $temp_insert_array['robot_affinities'] = json_encode(!empty($boss_data['robot_affinities']) ? $boss_data['robot_affinities'] : array());
    //$temp_insert_array['robot_affinities'] = array();
    //if (!empty($boss_data['robot_affinities'])){ foreach ($boss_data['robot_affinities'] AS $key => $token){ $temp_insert_array['robot_affinities'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_affinities'] = implode(',', $temp_insert_array['robot_affinities']);

    // Define immunities for this robot
    $temp_insert_array['robot_immunities'] = json_encode(!empty($boss_data['robot_immunities']) ? $boss_data['robot_immunities'] : array());
    //$temp_insert_array['robot_immunities'] = array();
    //if (!empty($boss_data['robot_immunities'])){ foreach ($boss_data['robot_immunities'] AS $key => $token){ $temp_insert_array['robot_immunities'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_immunities'] = implode(',', $temp_insert_array['robot_immunities']);

    // Define the ability rewards for this robot
    $temp_insert_array['robot_abilities_rewards'] = json_encode(!empty($boss_data['robot_rewards']['abilities']) ? $boss_data['robot_rewards']['abilities'] : array());
    //$temp_insert_array['robot_abilities_rewards'] = array();
    //if (!empty($boss_data['robot_rewards']['abilities'])){ foreach ($boss_data['robot_rewards']['abilities'] AS $key => $info){ $temp_insert_array['robot_abilities_rewards'][] = '['.$info['level'].':'.$info['token'].']'; } }
    //$temp_insert_array['robot_abilities_rewards'] = implode(',', $temp_insert_array['robot_abilities_rewards']);

    // Define immunities for this robot
    $temp_insert_array['robot_abilities_compatible'] = json_encode(!empty($boss_data['robot_abilities']) ? $boss_data['robot_abilities'] : array());
    //$temp_insert_array['robot_abilities_compatible'] = array();
    //if (!empty($boss_data['robot_abilities'])){ foreach ($boss_data['robot_abilities'] AS $key => $token){ $temp_insert_array['robot_abilities_compatible'][] = '['.$token.']'; } }
    //$temp_insert_array['robot_abilities_compatible'] = implode(',', $temp_insert_array['robot_abilities_compatible']);


    // Define the battle quotes for this robot
    if (!empty($boss_data['robot_quotes'])){ foreach ($boss_data['robot_quotes'] AS $key => $quote){ $boss_data['robot_quotes'][$key] = html_entity_decode($quote, ENT_QUOTES, 'UTF-8'); } }
    $temp_insert_array['robot_quotes_start'] = !empty($boss_data['robot_quotes']['battle_start']) && $boss_data['robot_quotes']['battle_start'] != '...' ? $boss_data['robot_quotes']['battle_start'] : '';
    $temp_insert_array['robot_quotes_taunt'] = !empty($boss_data['robot_quotes']['battle_taunt']) && $boss_data['robot_quotes']['battle_taunt'] != '...' ? $boss_data['robot_quotes']['battle_taunt'] : '';
    $temp_insert_array['robot_quotes_victory'] = !empty($boss_data['robot_quotes']['battle_victory']) && $boss_data['robot_quotes']['battle_victory'] != '...' ? $boss_data['robot_quotes']['battle_victory'] : '';
    $temp_insert_array['robot_quotes_defeat'] = !empty($boss_data['robot_quotes']['battle_defeat']) && $boss_data['robot_quotes']['battle_defeat'] != '...' ? $boss_data['robot_quotes']['battle_defeat'] : '';


    // Collect applicable spreadsheets for this robot
    if ($temp_insert_array['robot_class'] == 'master'){
      $boss_data['robot_class'] = 'master';
      $spreadsheet_stats = !empty($spreadsheet_robot_stats[$boss_data['robot_token']]) ? $spreadsheet_robot_stats[$boss_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_robot_quotes[$boss_data['robot_token']]) ? $spreadsheet_robot_quotes[$boss_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_robot_descriptions[$boss_data['robot_token']]) ? $spreadsheet_robot_descriptions[$boss_data['robot_token']] : array();
    } elseif ($temp_insert_array['robot_class'] == 'mecha'){
      $boss_data['robot_class'] = 'mecha';
      $spreadsheet_stats = !empty($spreadsheet_mecha_stats[$boss_data['robot_token']]) ? $spreadsheet_mecha_stats[$boss_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_mecha_quotes[$boss_data['robot_token']]) ? $spreadsheet_mecha_quotes[$boss_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_mecha_descriptions[$boss_data['robot_token']]) ? $spreadsheet_mecha_descriptions[$boss_data['robot_token']] : array();
    } elseif ($temp_insert_array['robot_class'] == 'boss'){
      $boss_data['robot_class'] = 'boss';
      $spreadsheet_stats = !empty($spreadsheet_robot_stats[$boss_data['robot_token']]) ? $spreadsheet_robot_stats[$boss_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_robot_quotes[$boss_data['robot_token']]) ? $spreadsheet_robot_quotes[$boss_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_robot_descriptions[$boss_data['robot_token']]) ? $spreadsheet_robot_descriptions[$boss_data['robot_token']] : array();
    }
    /*elseif ($temp_insert_array['robot_class'] == 'boss'){
      $spreadsheet_stats = !empty($spreadsheet_boss_stats[$boss_data['robot_token']]) ? $spreadsheet_boss_stats[$boss_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_boss_quotes[$boss_data['robot_token']]) ? $spreadsheet_boss_quotes[$boss_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_boss_descriptions[$boss_data['robot_token']]) ? $spreadsheet_boss_descriptions[$boss_data['robot_token']] : array();
    }*/

    // Collect any user-contributed data for this robot
    if (!empty($spreadsheet_stats['energy'])){ $temp_insert_array['robot_energy'] = $spreadsheet_stats['energy']; }
    if (!empty($spreadsheet_stats['attack'])){ $temp_insert_array['robot_attack'] = $spreadsheet_stats['attack']; }
    if (!empty($spreadsheet_stats['defense'])){ $temp_insert_array['robot_defense'] = $spreadsheet_stats['defense']; }
    if (!empty($spreadsheet_stats['speed'])){ $temp_insert_array['robot_speed'] = $spreadsheet_stats['speed']; }
    if (!empty($spreadsheet_quotes['battle_start'])){ $temp_insert_array['robot_quotes_start'] = trim($spreadsheet_quotes['battle_start']); }
    if (!empty($spreadsheet_quotes['battle_taunt'])){ $temp_insert_array['robot_quotes_taunt'] = trim($spreadsheet_quotes['battle_taunt']); }
    if (!empty($spreadsheet_quotes['battle_victory'])){ $temp_insert_array['robot_quotes_victory'] = trim($spreadsheet_quotes['battle_victory']); }
    if (!empty($spreadsheet_quotes['battle_defeat'])){ $temp_insert_array['robot_quotes_defeat'] = trim($spreadsheet_quotes['battle_defeat']); }
    if ($temp_insert_array['robot_class'] == 'master'){
      if (!empty($spreadsheet_descriptions['robot_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['robot_class']); }
      if (!empty($spreadsheet_descriptions['robot_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['robot_description']); }
    } elseif ($temp_insert_array['robot_class'] == 'mecha'){
      if (!empty($spreadsheet_descriptions['mecha_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['mecha_class']); }
      if (!empty($spreadsheet_descriptions['mecha_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['mecha_description']); }
    } elseif ($temp_insert_array['robot_class'] == 'boss'){
      if (!empty($spreadsheet_descriptions['robot_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['robot_class']); }
      if (!empty($spreadsheet_descriptions['robot_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['robot_description']); }
    }
     /*elseif ($temp_insert_array['robot_class'] == 'boss'){
      if (!empty($spreadsheet_descriptions['boss_class'])){ $temp_insert_array['robot_description'] = trim($spreadsheet_descriptions['boss_class']); }
      if (!empty($spreadsheet_descriptions['boss_description'])){ $temp_insert_array['robot_description2'] = trim($spreadsheet_descriptions['boss_description']); }
    }*/

    // Define the flags
    $temp_insert_array['robot_flag_hidden'] = $temp_insert_array['robot_class'] == 'mecha' || $temp_insert_array['robot_class'] == 'boss' || in_array($temp_insert_array['robot_token'], array('bond-man', 'fake-man', 'cache', 'rock')) ? 1 : 0;
    $temp_insert_array['robot_flag_complete'] = $boss_data['robot_image'] != 'robot' && $boss_data['robot_image'] != $boss_data['robot_class'] ? 1 : 0;
    $temp_insert_array['robot_flag_published'] = 1;

    // Define the order counter
    if ($temp_insert_array['robot_class'] != 'system'){
      $temp_insert_array['robot_order'] = $boss_order;
      $boss_order++;
    } else {
      $temp_insert_array['robot_order'] = 0;
    }

    // Check if this robot already exists in the database
    $temp_success = true;
    $temp_exists = $DB->get_array("SELECT robot_token FROM mmrpg_index_robots WHERE robot_token LIKE '{$temp_insert_array['robot_token']}' LIMIT 1") ? true : false;
    if (!$temp_exists){ $temp_success = $DB->insert('mmrpg_index_robots', $temp_insert_array); }
    else { $temp_success = $DB->update('mmrpg_index_robots', $temp_insert_array, array('robot_token' => $temp_insert_array['robot_token'])); }

    // Print out the generated insert array
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
    $this_page_markup .= '<strong>$mmrpg_database_bosses['.$boss_token.']</strong><br />';
    //$this_page_markup .= '<pre>'.print_r($boss_data, true).'</pre><br /><hr /><br />';
    $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
    //$this_page_markup .= '<pre>'.print_r(mmrpg_robot::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
    $this_page_markup .= '</p><hr />';

    $boss_key++;

    //if ($boss_data['robot_token'] == 'met'){ die('met = <pre>'.print_r($temp_insert_array, true).'</pre>'); }
    //die('end');

  }
}
// Otherwise, if empty, we're done!
else {
  $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL BOSS HAVE BEEN IMPORTED UPDATED!</strong></p>';
}

?>