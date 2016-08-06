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
//require_once('database/include.php');

// Require the robots index file
require(MMRPG_CONFIG_ROOTDIR.'data/robots/_index.php');
// Require the spreadsheet functions file
require(MMRPG_CONFIG_ROOTDIR.'admin/spreadsheets.php');

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_robots</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_database_robots) ? count($mmrpg_database_robots) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_robots, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';



$spreadsheet_robot_stats = mmrpg_spreadsheet_robot_stats();
$spreadsheet_mecha_stats = mmrpg_spreadsheet_mecha_stats();
$spreadsheet_robot_quotes = mmrpg_spreadsheet_robot_quotes();
$spreadsheet_mecha_quotes = mmrpg_spreadsheet_mecha_quotes();
$spreadsheet_robot_descriptions = mmrpg_spreadsheet_robot_descriptions();
$spreadsheet_mecha_descriptions = mmrpg_spreadsheet_mecha_descriptions();


/*
header('Content-type: text/plain; charset=UTF-8');
die($this_page_markup."\n\n".
  //'$spreadsheet_robot_stats = <pre>'.print_r($spreadsheet_robot_stats, true).'</pre>'."\n\n".
  //'$spreadsheet_mecha_stats = <pre>'.print_r($spreadsheet_mecha_stats, true).'</pre>'."\n\n".
  //'$spreadsheet_robot_quotes = <pre>'.print_r($spreadsheet_robot_quotes, true).'</pre>'."\n\n".
  //'$spreadsheet_mecha_quotes = <pre>'.print_r($spreadsheet_mecha_quotes, true).'</pre>'."\n\n".
  //'$spreadsheet_robot_descriptions = <pre>'.print_r($spreadsheet_robot_descriptions, true).'</pre>'."\n\n".
  '$spreadsheet_mecha_descriptions = <pre>'.print_r($spreadsheet_mecha_descriptions, true).'</pre>'."\n\n".
  '---');
*/




// Loop through each of the robot info arrays
$robot_key = 0;
$temp_empty = $mmrpg_index['robots']['robot'];
unset($mmrpg_index['robots']['robot']);
array_unshift($mmrpg_index['robots'], $temp_empty);
if (!empty($mmrpg_index['robots'])){
  foreach ($mmrpg_index['robots'] AS $robot_token => $robot_data){

    // If this robot's image exists, assign it
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$robot_token.'/')){ $robot_data['robot_image'] = $robot_data['robot_token']; }
    else { $robot_data['robot_image'] = 'robot'; }

    // Define the insert array and start populating it with basic details
    $temp_insert_array = array();
    //$temp_insert_array['robot_id'] = isset($robot_data['robot_id']) ? $robot_data['robot_id'] : $robot_key;
    $temp_insert_array['robot_token'] = $robot_data['robot_token'];
    $temp_insert_array['robot_number'] = !empty($robot_data['robot_number']) ? $robot_data['robot_number'] : '';
    $temp_insert_array['robot_name'] = !empty($robot_data['robot_name']) ? $robot_data['robot_name'] : '';
    $temp_insert_array['robot_game'] = !empty($robot_data['robot_game']) ? $robot_data['robot_game'] : '';
    $temp_insert_array['robot_field'] = !empty($robot_data['robot_field']) ? $robot_data['robot_field'] : 'field';
    $temp_insert_array['robot_field2'] = !empty($robot_data['robot_field2']) ? json_encode($robot_data['robot_field2']) : '';
    $temp_insert_array['robot_class'] = !empty($robot_data['robot_class']) ? $robot_data['robot_class'] : 'master';
    $temp_insert_array['robot_image'] = !empty($robot_data['robot_image']) ? $robot_data['robot_image'] : '';
    $temp_insert_array['robot_image_size'] = !empty($robot_data['robot_image_size']) ? $robot_data['robot_image_size'] : 40;
    $temp_insert_array['robot_image_editor'] = !empty($robot_data['robot_image_editor']) ? $robot_data['robot_image_editor'] : 0;
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
      $spreadsheet_stats = !empty($spreadsheet_mecha_stats[$robot_data['robot_token']]) ? $spreadsheet_mecha_stats[$robot_data['robot_token']] : array();
      $spreadsheet_quotes = !empty($spreadsheet_mecha_quotes[$robot_data['robot_token']]) ? $spreadsheet_mecha_quotes[$robot_data['robot_token']] : array();
      $spreadsheet_descriptions = !empty($spreadsheet_mecha_descriptions[$robot_data['robot_token']]) ? $spreadsheet_mecha_descriptions[$robot_data['robot_token']] : array();
    }

    /*
    $spreadsheet_stats = !empty($spreadsheet_robot_stats[$robot_data['robot_token']]) ? $spreadsheet_robot_stats[$robot_data['robot_token']] : (!empty($spreadsheet_mecha_stats[$robot_data['robot_token']]) ? $spreadsheet_mecha_stats[$robot_data['robot_token']] : array());
    $spreadsheet_quotes = !empty($spreadsheet_robot_quotes[$robot_data['robot_token']]) ? $spreadsheet_robot_quotes[$robot_data['robot_token']] : (!empty($spreadsheet_mecha_quotes[$robot_data['robot_token']]) ? $spreadsheet_mecha_quotes[$robot_data['robot_token']] : array());
    $spreadsheet_descriptions = !empty($spreadsheet_robot_descriptions[$robot_data['robot_token']]) ? $spreadsheet_robot_descriptions[$robot_data['robot_token']] : (!empty($spreadsheet_mecha_descriptions[$robot_data['robot_token']]) ? $spreadsheet_mecha_descriptions[$robot_data['robot_token']] : array());
    */

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
    }

    // Define the flags
    $temp_insert_array['robot_flag_hidden'] = $temp_insert_array['robot_class'] == 'mecha' || in_array($temp_insert_array['robot_token'], array('bond-man', 'fake-man', 'cache', 'rock')) ? 1 : 0;
    $temp_insert_array['robot_flag_complete'] = $robot_data['robot_image'] != 'robot' ? 1 : 0;
    $temp_insert_array['robot_flag_published'] = 1;

    // Check if this robot already exists in the database
    $temp_success = true;
    $temp_exists = $db->get_array("SELECT robot_token FROM mmrpg_index_robots WHERE robot_token LIKE '{$temp_insert_array['robot_token']}' LIMIT 1") ? true : false;
    if (!$temp_exists){ $temp_success = $db->insert('mmrpg_index_robots', $temp_insert_array); }
    else { $temp_success = $db->update('mmrpg_index_robots', $temp_insert_array, array('robot_token' => $temp_insert_array['robot_token'])); }

    // Print out the generated insert array
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
    $this_page_markup .= '<strong>$mmrpg_database_robots['.$robot_token.']</strong><br />';
    //$this_page_markup .= '<pre>'.print_r($robot_data, true).'</pre><br /><hr /><br />';
    $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
    //$this_page_markup .= '<pre>'.print_r(rpg_robot::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
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

?>