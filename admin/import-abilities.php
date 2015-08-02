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
<a href="admin.php?action=import-abilities&limit=<?=$this_import_limit?>">Update Ability Database</a> &raquo;
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
$DB->query('TRUNCATE TABLE mmrpg_index_abilities');

// Require the abilities index file
//$mmrpg_index = array();
require(MMRPG_CONFIG_ROOTDIR.'data/abilities/_index.php');
//die('$mmrpg_index[types] = <pre>'.print_r($mmrpg_database_types, true).'</pre>');

// Create the items and system array (subsets of abilities) for populating
//$mmrpg_index['system'] = array();
$mmrpg_index['items'] = array();

// Fill in potentially missing fields with defaults for sorting
if (!empty($mmrpg_index['abilities'])){
  foreach ($mmrpg_index['abilities'] AS $token => $ability){
    $ability['ability_class'] = isset($ability['ability_class']) ? $ability['ability_class'] : 'master';
    $ability['ability_subclass'] = isset($ability['ability_subclass']) ? $ability['ability_subclass'] : '';
    $ability['ability_game'] = isset($ability['ability_game']) ? $ability['ability_game'] : 'MMRPG';
    $ability['ability_group'] = isset($ability['ability_group']) ? $ability['ability_group'] : 'MMRPG';
    $ability['ability_master'] = isset($ability['ability_master']) ? $ability['ability_master'] : '';
    $ability['ability_number'] = isset($ability['ability_number']) ? $ability['ability_number'] : '';
    $ability['ability_energy'] = isset($ability['ability_energy']) ? $ability['ability_energy'] : 1;
    $ability['ability_type'] = isset($ability['ability_type']) ? $ability['ability_type'] : '';
    $ability['ability_type2'] = isset($ability['ability_type2']) ? $ability['ability_type2'] : '';
    $mmrpg_index['abilities'][$token] = $ability;
    /*if ($ability['ability_class'] == 'system'){
      $mmrpg_index['system'][$token] = $ability;
      unset($mmrpg_index['abilities'][$token]);
    } else*/
    if ($ability['ability_class'] == 'item'){
      $mmrpg_index['items'][$token] = $ability;
      unset($mmrpg_index['abilities'][$token]);
    }
  }
}




// -- MMRPG IMPORT ABILTIIES -- //

// Sort the ability index based on ability number
$temp_pattern_first = array();
$temp_pattern_first[] = '/^(buster-shot)$/i';
$temp_pattern_first[] = '/^(buster-charge)$/i';
$temp_pattern_first[] = '/^(buster-relay)$/i';
$temp_pattern_first[] = '/^mega-buster$/i';
$temp_pattern_first[] = '/^mega-ball$/i';
$temp_pattern_first[] = '/^mega-slide$/i';
$temp_pattern_first[] = '/^bass-buster$/i';
$temp_pattern_first[] = '/^bass-crush$/i';
$temp_pattern_first[] = '/^bass-baroque$/i';
$temp_pattern_first[] = '/^proto-buster$/i';
$temp_pattern_first[] = '/^proto-shield$/i';
$temp_pattern_first[] = '/^proto-strike$/i';
$temp_pattern_first[] = '/^roll-buster$/i';
$temp_pattern_first[] = '/^roll-swing$/i';
$temp_pattern_first[] = '/^roll-support$/i';
$temp_pattern_first[] = '/^disco-buster$/i';
$temp_pattern_first[] = '/^disco-fever$/i';
$temp_pattern_first[] = '/^disco-assault$/i';
$temp_pattern_first[] = '/^rhythm-buster$/i';
$temp_pattern_first[] = '/^rhythm-heaven$/i';
$temp_pattern_first[] = '/^rhythm-shuffle$/i';
//$temp_pattern_first = array_reverse($temp_pattern_first);
$temp_pattern_last = array();
$temp_element_types = implode('|', array_keys($mmrpg_database_types));
$temp_pattern_last[] = '/^light-buster$/i';
$temp_pattern_last[] = '/^wily-buster$/i';
$temp_pattern_last[] = '/^cossack-buster$/i';
$temp_pattern_last[] = '/^('.$temp_element_types.')-(shot|buster|overdrive)$/i';
$temp_pattern_last[] = '/^copy-shot$/i';
$temp_pattern_last[] = '/^copy-soul$/i';
$temp_pattern_last[] = '/^copy-shield$/i';
$temp_pattern_last[] = '/^copy-laser$/i';
$temp_pattern_last[] = '/^(energy|repair)-(boost|break|swap)$/i';
$temp_pattern_last[] = '/^(energy|repair)-(support)$/i';
$temp_pattern_last[] = '/^(energy|repair)-(assault|shuffle)$/i';
$temp_pattern_last[] = '/^(attack|weapon)-(boost|break|swap)$/i';
$temp_pattern_last[] = '/^(attack|weapon)-(support)$/i';
$temp_pattern_last[] = '/^(attack|weapon)-(assault|shuffle)$/i';
$temp_pattern_last[] = '/^(defense|shield)-(boost|break|swap)$/i';
$temp_pattern_last[] = '/^(defense|shield)-(support)$/i';
$temp_pattern_last[] = '/^(defense|shield)-(assault|shuffle)$/i';
$temp_pattern_last[] = '/^(speed|mobility)-(boost|break|swap)$/i';
$temp_pattern_last[] = '/^(speed|mobility)-(support)$/i';
$temp_pattern_last[] = '/^(speed|mobility)-(assault|shuffle)$/i';
$temp_pattern_last[] = '/^(energy|repair)-(mode)$/i';
$temp_pattern_last[] = '/^(attack|weapon)-(mode)$/i';
$temp_pattern_last[] = '/^(defense|shield)-(mode)$/i';
$temp_pattern_last[] = '/^(speed|mobility)-(mode)$/i';
//$temp_pattern_last[] = '/^(energy|attack|defense|speed|repair|weapon|shield|mobility)-(booster|breaker)$/i';
//$temp_pattern_last[] = '/^(energy|attack|defense|speed|repair|weapon|shield|mobility)-([a-z0-9]+)$/i';
$temp_stat_types = 'energy|repair|attack|weapon|defense|shield|speed|mobility';
$temp_boost_types = 'charge|haste|temper|hone|blast|cool|blaze|harden|cosmos|growth|breeze|douse|surge|rocket|polish|charm|guard|glow';
$temp_break_types = 'shock|slow|hammer|blunt|burst|chill|burn|crumble|chaos|decay|squall|drench|stall|torpedo|tarnish|curse|block|fade';
$temp_pattern_last[] = '/^('.$temp_stat_types.')-('.$temp_boost_types.'|'.$temp_break_types.')$/i';
//$temp_pattern_last[] = '/^(energy|repair)-('.$temp_boost_types.'|'.$temp_break_types.')$/i';
//$temp_pattern_last[] = '/^(attack|weapon)-('.$temp_boost_types.'|'.$temp_break_types.')$/i';
//$temp_pattern_last[] = '/^(defense|shield)-('.$temp_boost_types.'|'.$temp_break_types.')$/i';
//$temp_pattern_last[] = '/^(speed|mobility)-('.$temp_boost_types.'|'.$temp_break_types.')$/i';
//$temp_pattern_last[] = '/^(energy|repair)-('.$temp_boost_types.')$/i';
//$temp_pattern_last[] = '/^(energy|repair)-('.$temp_break_types.')$/i';
//$temp_pattern_last[] = '/^(attack|weapon)-('.$temp_boost_types.')$/i';
//$temp_pattern_last[] = '/^(attack|weapon)-('.$temp_break_types.')$/i';
//$temp_pattern_last[] = '/^(defense|shield)-('.$temp_boost_types.')$/i';
//$temp_pattern_last[] = '/^(defense|shield)-('.$temp_break_types.')$/i';
//$temp_pattern_last[] = '/^(speed|mobility)-('.$temp_boost_types.')$/i';
//$temp_pattern_last[] = '/^(speed|mobility)-('.$temp_break_types.')$/i';
$temp_pattern_last[] = '/^(damage|recovery)-(booster|breaker)$/i';
$temp_pattern_last[] = '/^(experience|item)-(booster|breaker)$/i';
$temp_pattern_last[] = '/^(mecha|field)-(support|assault)$/i';

$temp_energy_pattern = '/^(energy|repair)-('.$temp_boost_types.'|'.$temp_break_types.')$/i';
$temp_attack_pattern = '/^(attack|weapon)-('.$temp_boost_types.'|'.$temp_break_types.')$/i';
$temp_defense_pattern = '/^(defense|shield)-('.$temp_boost_types.'|'.$temp_break_types.')$/i';
$temp_speed_pattern = '/^(speed|mobility)-('.$temp_boost_types.'|'.$temp_break_types.')$/i';

$temp_boost_pattern = '/^('.$temp_stat_types.')-('.$temp_boost_types.')$/i';
$temp_break_pattern = '/^('.$temp_stat_types.')-('.$temp_boost_types.')$/i';

$temp_pattern_last = array_reverse($temp_pattern_last);
function mmrpg_index_sort_abilities($ability_one, $ability_two){
  // Pull in global variables
  global $mmrpg_index, $temp_pattern_first, $temp_pattern_last, $temp_element_types, $temp_boost_types, $temp_break_types;
  global $temp_energy_pattern, $temp_attack_pattern, $temp_defense_pattern, $temp_speed_pattern, $temp_boost_pattern, $temp_break_pattern;
  // Loop through all the temp patterns and compare them one at a time
  foreach ($temp_pattern_first AS $key => $pattern){
    // Check if either of these two abilities matches the current pattern
    if (preg_match($pattern, $ability_one['ability_token']) && !preg_match($pattern, $ability_two['ability_token'])){ return -1; }
    elseif (!preg_match($pattern, $ability_one['ability_token']) && preg_match($pattern, $ability_two['ability_token'])){ return 1; }
  }
  foreach ($temp_pattern_last AS $key => $pattern){
    // Check if either of these two abilities matches the current pattern
    if (preg_match($pattern, $ability_one['ability_token']) && !preg_match($pattern, $ability_two['ability_token'])){ return 1; }
    elseif (!preg_match($pattern, $ability_one['ability_token']) && preg_match($pattern, $ability_two['ability_token'])){ return -1; }
  }
  // Collect the name prefixes for sorting purposes
  $prefix_one = preg_replace('/^([a-z0-9]+)-(.*)$/i', '$1', $ability_one['ability_token']);
  $prefix_two = preg_replace('/^([a-z0-9]+)-(.*)$/i', '$1', $ability_two['ability_token']);
  $suffix_one = preg_replace('/^(.*)-([a-z0-9]+)$/i', '$2', $ability_one['ability_token']);
  $suffix_two = preg_replace('/^(.*)-([a-z0-9]+)$/i', '$2', $ability_two['ability_token']);
  // Define the shot flags for sorting purposes
  $shot_buster_overdrive_index = array('shot' => 1, 'buster' => 2, 'overdrive' => 3);
  $shot_buster_overdrive_one = isset($shot_buster_overdrive_index[$suffix_one]) ? $shot_buster_overdrive_index[$suffix_one] : 0;
  $shot_buster_overdrive_two = isset($shot_buster_overdrive_index[$suffix_two]) ? $shot_buster_overdrive_index[$suffix_two] : 0;
  // Collect the primary type orders for sorting purposes
  $type_one = !empty($ability_one['ability_type']) ? $ability_one['ability_type'] : 'none';
  $type_two = !empty($ability_two['ability_type']) ? $ability_two['ability_type'] : 'none';
  $type_order_one = !empty($mmrpg_database_types[$type_one]) ? $mmrpg_database_types[$type_one]['type_order'] : -1;
  $type_order_two = !empty($mmrpg_database_types[$type_two]) ? $mmrpg_database_types[$type_two]['type_order'] : -1;
  // Collect the group prefixes for sorting purposes
  $group_prefix_one = !empty($ability_one['ability_group']) ? $ability_one['ability_group'] : 'MMRPG';
  $group_prefix_two = !empty($ability_two['ability_group']) ? $ability_two['ability_group'] : 'MMRPG';
  // Collect the game prefixes for sorting purposes
  $game_prefix_one = !empty($ability_one['ability_game']) ? $ability_one['ability_game'] : 'MMRPG';
  $game_prefix_two = !empty($ability_two['ability_game']) ? $ability_two['ability_game'] : 'MMRPG';
  // Collect the serial number for sorting purposes
  $serial_number_one = !empty($ability_one['ability_number']) ? $ability_one['ability_number'] : '';
  $serial_number_two = !empty($ability_two['ability_number']) ? $ability_two['ability_number'] : '';
  // If the abilities have groups, order them by their token alphabetically
  if ($group_prefix_one > $group_prefix_two){ return 1; }
  elseif ($group_prefix_one < $group_prefix_two){ return -1; }
  // Else If the abilities have games, order them by their token alphabetically
  elseif ($game_prefix_one > $game_prefix_two){ return 1; }
  elseif ($game_prefix_one < $game_prefix_two){ return -1; }
  // If the abilities have serials, order them by their token alphabetically
  elseif ($serial_number_one > $serial_number_two){ return 1; }
  elseif ($serial_number_one < $serial_number_two){ return -1; }
  // If the abilities are of the shot/buster/overdrive type sort by power
  elseif ($shot_buster_overdrive_one > $shot_buster_overdrive_two){ return 1; }
  elseif ($shot_buster_overdrive_one < $shot_buster_overdrive_two){ return -1; }
  // If the abilities have energy/attack/defense/speed prefixes, favour them first
  elseif (preg_match($temp_energy_pattern, $ability_one['ability_token']) && !preg_match($temp_energy_pattern, $ability_two['ability_token'])){ return -1; }
  elseif (!preg_match($temp_energy_pattern, $ability_one['ability_token']) && preg_match($temp_energy_pattern, $ability_two['ability_token'])){ return 1; }
  elseif (preg_match($temp_attack_pattern, $ability_one['ability_token']) && !preg_match($temp_attack_pattern, $ability_two['ability_token'])){ return -1; }
  elseif (!preg_match($temp_attack_pattern, $ability_one['ability_token']) && preg_match($temp_attack_pattern, $ability_two['ability_token'])){ return 1; }
  elseif (preg_match($temp_defense_pattern, $ability_one['ability_token']) && !preg_match($temp_defense_pattern, $ability_two['ability_token'])){ return -1; }
  elseif (!preg_match($temp_defense_pattern, $ability_one['ability_token']) && preg_match($temp_defense_pattern, $ability_two['ability_token'])){ return 1; }
  elseif (preg_match($temp_speed_pattern, $ability_one['ability_token']) && !preg_match($temp_speed_pattern, $ability_two['ability_token'])){ return -1; }
  elseif (!preg_match($temp_speed_pattern, $ability_one['ability_token']) && preg_match($temp_speed_pattern, $ability_two['ability_token'])){ return 1; }
  elseif (preg_match($temp_boost_pattern, $ability_one['ability_token']) && !preg_match($temp_boost_pattern, $ability_two['ability_token'])){ return -1; }
  elseif (!preg_match($temp_boost_pattern, $ability_one['ability_token']) && preg_match($temp_boost_pattern, $ability_two['ability_token'])){ return 1; }
  elseif (preg_match($temp_break_pattern, $ability_one['ability_token']) && !preg_match($temp_break_pattern, $ability_two['ability_token'])){ return -1; }
  elseif (!preg_match($temp_break_pattern, $ability_one['ability_token']) && preg_match($temp_break_pattern, $ability_two['ability_token'])){ return 1; }
  // If neither ability has a type, order albabetically
  elseif (empty($ability_one['ability_type']) && empty($ability_two['ability_type'])){
    if ($ability_one['ability_game'] > $ability_two['ability_game']){ return 1; }
    elseif ($ability_one['ability_game'] < $ability_two['ability_game']){ return -1; }
    elseif ($prefix_one > $prefix_two){ return 1; }
    elseif ($prefix_one < $prefix_two){ return -1; }
    elseif ($suffix_one > $suffix_two){ return 1; }
    elseif ($suffix_one < $suffix_two){ return -1; }
    elseif ($ability_one['ability_energy'] > $ability_two['ability_energy']){ return 1; }
    elseif ($ability_one['ability_energy'] < $ability_two['ability_energy']){ return -1; }
    elseif ($ability_one['ability_token'] > $ability_two['ability_token']){ return 1; }
    elseif ($ability_one['ability_token'] < $ability_two['ability_token']){ return -1; }
    else { return 0; }
  }
  // If the abilities have types, order them by their types alphabetically
  elseif ($type_order_one > $type_order_two){ return 1; }
  elseif ($type_order_one < $type_order_two){ return -1; }
  elseif ($ability_one['ability_energy'] > $ability_two['ability_energy']){ return 1; }
  elseif ($ability_one['ability_energy'] < $ability_two['ability_energy']){ return -1; }
  // If the abilities have the same first type, compare further
  elseif ($ability_one['ability_type'] == $ability_two['ability_type']){
    // If only one of the two abilities has a second type, that one goes last
    if (preg_match('/-shot$/', $ability_one['ability_token']) && !preg_match('/-shot$/', $ability_two['ability_token'])){ return -1; }
    elseif (!preg_match('/-shot$/', $ability_one['ability_token']) && preg_match('/-shot$/', $ability_two['ability_token'])){ return 1; }
    elseif (preg_match('/-buster$/', $ability_one['ability_token']) && !preg_match('/-buster$/', $ability_two['ability_token'])){ return -1; }
    elseif (!preg_match('/-buster$/', $ability_one['ability_token']) && preg_match('/-buster$/', $ability_two['ability_token'])){ return 1; }
    elseif (preg_match('/-overdrive$/', $ability_one['ability_token']) && !preg_match('/-overdrive$/', $ability_two['ability_token'])){ return -1; }
    elseif (!preg_match('/-overdrive$/', $ability_one['ability_token']) && preg_match('/-overdrive$/', $ability_two['ability_token'])){ return 1; }
    elseif ($ability_one['ability_game'] > $ability_two['ability_game']){ return 1; }
    elseif ($ability_one['ability_game'] < $ability_two['ability_game']){ return -1; }
    elseif ($prefix_one > $prefix_two){ return -1; }
    elseif ($prefix_one < $prefix_two){ return 1; }
    elseif ($suffix_one > $suffix_two){ return 1; }
    elseif ($suffix_one < $suffix_two){ return -1; }
    elseif ($ability_one['ability_token'] > $ability_two['ability_token']){ return 1; }
    elseif ($ability_one['ability_token'] < $ability_two['ability_token']){ return -1; }
    else { return 0; }
  }
  else {
    // Return 0 by default
    return 0;
  }
}
uasort($mmrpg_index['abilities'], 'mmrpg_index_sort_abilities');

//die('abilities : <pre>'.print_r(array_keys($mmrpg_index['abilities']), true).'</pre>');

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_abilities</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_index['abilities']) ? count($mmrpg_index['abilities']) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_abilities, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';

// Loop through each of the ability info arrays
$ability_key = 0;
$ability_order = 0;
$temp_empty = $mmrpg_index['abilities']['ability'];
unset($mmrpg_index['abilities']['ability']);
array_unshift($mmrpg_index['abilities'], $temp_empty);
if (!empty($mmrpg_index['abilities'])){
  foreach ($mmrpg_index['abilities'] AS $ability_token => $ability_data){

    // If this ability's image exists, assign it
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/abilities/'.$ability_token.'/')){ $ability_data['ability_image'] = $ability_data['ability_token']; }
    else { $ability_data['ability_image'] = 'ability'; }

    // Define the insert array and start populating it with basic details
    $temp_insert_array = array();
    //$temp_insert_array['ability_id'] = isset($ability_data['ability_id']) ? $ability_data['ability_id'] : $ability_key;
    $temp_insert_array['ability_token'] = $ability_data['ability_token'];
    $temp_insert_array['ability_name'] = !empty($ability_data['ability_name']) ? $ability_data['ability_name'] : '';
    $temp_insert_array['ability_game'] = !empty($ability_data['ability_game']) ? $ability_data['ability_game'] : '';
    $temp_insert_array['ability_group'] = !empty($ability_data['ability_group']) ? $ability_data['ability_group'] : '';
    $temp_insert_array['ability_class'] = !empty($ability_data['ability_class']) ? $ability_data['ability_class'] : 'master';
    $temp_insert_array['ability_subclass'] = !empty($ability_data['ability_subclass']) ? $ability_data['ability_subclass'] : '';
    $temp_insert_array['ability_master'] = !empty($ability_data['ability_master']) ? $ability_data['ability_master'] : '';
    $temp_insert_array['ability_number'] = !empty($ability_data['ability_number']) ? $ability_data['ability_number'] : '';
    $temp_insert_array['ability_image'] = !empty($ability_data['ability_image']) ? $ability_data['ability_image'] : '';
    $temp_insert_array['ability_image_sheets'] = isset($ability_data['ability_image_sheets']) ? $ability_data['ability_image_sheets'] : 1;
    $temp_insert_array['ability_image_size'] = !empty($ability_data['ability_image_size']) ? $ability_data['ability_image_size'] : 40;
    $temp_insert_array['ability_image_editor'] = !empty($ability_data['ability_image_editor']) ? $ability_data['ability_image_editor'] : ($ability_data['ability_image'] != 'ability' ? 412 : 0);
    $temp_insert_array['ability_type'] = !empty($ability_data['ability_type']) ? $ability_data['ability_type'] : '';
    $temp_insert_array['ability_type2'] = !empty($ability_data['ability_type2']) ? $ability_data['ability_type2'] : '';
    $temp_insert_array['ability_description'] = !empty($ability_data['ability_description']) && $ability_data['ability_description'] != '...' ? $ability_data['ability_description'] : '';
    $temp_insert_array['ability_description2'] = !empty($ability_data['ability_description2']) && $ability_data['ability_description2'] != '...' ? $ability_data['ability_description2'] : '';
    $temp_insert_array['ability_speed'] = !empty($ability_data['ability_speed']) ? $ability_data['ability_speed'] : 1;
    $temp_insert_array['ability_energy'] = isset($ability_data['ability_energy']) ? $ability_data['ability_energy'] : 1;
    $temp_insert_array['ability_energy_percent'] = !empty($ability_data['ability_energy_percent']) ? 1 : 0;
    $temp_insert_array['ability_damage'] = !empty($ability_data['ability_damage']) ? $ability_data['ability_damage'] : 0;
    $temp_insert_array['ability_damage_percent'] = !empty($ability_data['ability_damage_percent']) ? 1 : 0;
    $temp_insert_array['ability_damage2'] = !empty($ability_data['ability_damage2']) ? $ability_data['ability_damage2'] : 0;
    $temp_insert_array['ability_damage2_percent'] = !empty($ability_data['ability_damage2_percent']) ? 1 : 0;
    $temp_insert_array['ability_recovery'] = !empty($ability_data['ability_recovery']) ? $ability_data['ability_recovery'] : 0;
    $temp_insert_array['ability_recovery_percent'] = !empty($ability_data['ability_recovery_percent']) ? 1 : 0;
    $temp_insert_array['ability_recovery2'] = !empty($ability_data['ability_recovery2']) ? $ability_data['ability_recovery2'] : 0;
    $temp_insert_array['ability_recovery2_percent'] = !empty($ability_data['ability_recovery2_percent']) ? 1 : 0;
    $temp_insert_array['ability_accuracy'] = !empty($ability_data['ability_accuracy']) ? $ability_data['ability_accuracy'] : 0;
    $temp_insert_array['ability_target'] = !empty($ability_data['ability_target']) ? $ability_data['ability_target'] : 'auto';
    $temp_insert_array['ability_functions'] = !empty($ability_data['ability_functions']) ? $ability_data['ability_functions'] : 'abilities/ability.php';

    // Define the ability frame properties
    $temp_insert_array['ability_frame'] = !empty($ability_data['ability_frame']) ? $ability_data['ability_frame'] : 'base';
    $temp_insert_array['ability_frame_animate'] = json_encode(!empty($ability_data['ability_frame_animate']) ? $ability_data['ability_frame_animate'] : array());
    $temp_insert_array['ability_frame_index'] = json_encode(!empty($ability_data['ability_frame_index']) ? $ability_data['ability_frame_index'] : array());
    $temp_insert_array['ability_frame_offset'] = json_encode(!empty($ability_data['ability_frame_offset']) ? $ability_data['ability_frame_offset'] : array());
    //$temp_insert_array['ability_frame_animate'] = array();
    //if (!empty($ability_data['ability_frame_animate'])){ foreach ($ability_data['ability_frame_animate'] AS $key => $token){ $temp_insert_array['ability_frame_animate'][] = '['.$token.']'; } }
    //$temp_insert_array['ability_frame_animate'] = implode(',', $temp_insert_array['ability_frame_animate']);
    //$temp_insert_array['ability_frame_index'] = array();
    //if (!empty($ability_data['ability_frame_index'])){ foreach ($ability_data['ability_frame_index'] AS $key => $token){ $temp_insert_array['ability_frame_index'][] = '['.$token.']'; } }
    //$temp_insert_array['ability_frame_index'] = implode(',', $temp_insert_array['ability_frame_index']);
    //$temp_insert_array['ability_frame_offset'] = array();
    //if (!empty($ability_data['ability_frame_offset'])){ foreach ($ability_data['ability_frame_offset'] AS $key => $token){ $temp_insert_array['ability_frame_offset'][] = '['.$key.':'.$token.']'; } }
    //$temp_insert_array['ability_frame_offset'] = implode(',', $temp_insert_array['ability_frame_offset']);
    $temp_insert_array['ability_frame_styles'] = !empty($ability_data['ability_frame_styles']) ? $ability_data['ability_frame_styles'] : '';
    $temp_insert_array['ability_frame_classes'] = !empty($ability_data['ability_frame_classes']) ? $ability_data['ability_frame_classes'] : '';

    // Define the ability frame properties
    $temp_insert_array['attachment_frame'] = !empty($ability_data['attachment_frame']) ? $ability_data['attachment_frame'] : 'base';
    $temp_insert_array['attachment_frame_animate'] = json_encode(!empty($ability_data['attachment_frame_animate']) ? $ability_data['attachment_frame_animate'] : array());
    $temp_insert_array['attachment_frame_index'] = json_encode(!empty($ability_data['attachment_frame_index']) ? $ability_data['attachment_frame_index'] : array());
    $temp_insert_array['attachment_frame_offset'] = json_encode(!empty($ability_data['attachment_frame_offset']) ? $ability_data['attachment_frame_offset'] : array());
    //$temp_insert_array['attachment_frame_animate'] = array();
    //if (!empty($ability_data['attachment_frame_animate'])){ foreach ($ability_data['attachment_frame_animate'] AS $key => $token){ $temp_insert_array['attachment_frame_animate'][] = '['.$token.']'; } }
    //$temp_insert_array['attachment_frame_animate'] = implode(',', $temp_insert_array['attachment_frame_animate']);
    //$temp_insert_array['attachment_frame_index'] = array();
    //if (!empty($ability_data['attachment_frame_index'])){ foreach ($ability_data['attachment_frame_index'] AS $key => $token){ $temp_insert_array['attachment_frame_index'][] = '['.$token.']'; } }
    //$temp_insert_array['attachment_frame_index'] = implode(',', $temp_insert_array['attachment_frame_index']);
    //$temp_insert_array['attachment_frame_offset'] = array();
    //if (!empty($ability_data['attachment_frame_offset'])){ foreach ($ability_data['attachment_frame_offset'] AS $key => $token){ $temp_insert_array['attachment_frame_offset'][] = '['.$key.':'.$token.']'; } }
    //$temp_insert_array['attachment_frame_offset'] = implode(',', $temp_insert_array['attachment_frame_offset']);
    $temp_insert_array['attachment_frame_styles'] = !empty($ability_data['attachment_frame_styles']) ? $ability_data['attachment_frame_styles'] : '';
    $temp_insert_array['attachment_frame_classes'] = !empty($ability_data['attachment_frame_classes']) ? $ability_data['attachment_frame_classes'] : '';

    // Define the flags
    $temp_insert_array['ability_flag_hidden'] = $temp_insert_array['ability_class'] != 'master' || in_array($temp_insert_array['ability_token'], array('ability', 'attachment-defeat')) ? 1 : 0;
    $temp_insert_array['ability_flag_complete'] = $temp_insert_array['ability_class'] == 'system' || $ability_data['ability_image'] != 'ability' ? 1 : 0;
    $temp_insert_array['ability_flag_published'] = 1;

    // Define the order counter
    if ($temp_insert_array['ability_class'] != 'system'){
      $temp_insert_array['ability_order'] = $ability_order;
      $ability_order++;
    } else {
      $temp_insert_array['ability_order'] = 0;
    }

    // Check if this ability already exists in the database
    $temp_success = true;
    $temp_exists = $DB->get_array("SELECT ability_token FROM mmrpg_index_abilities WHERE ability_token LIKE '{$temp_insert_array['ability_token']}' LIMIT 1") ? true : false;
    if (!$temp_exists){ $temp_success = $DB->insert('mmrpg_index_abilities', $temp_insert_array); }
    else { $temp_success = $DB->update('mmrpg_index_abilities', $temp_insert_array, array('ability_token' => $temp_insert_array['ability_token'])); }

    // Print out the generated insert array
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
    $this_page_markup .= '<strong>$mmrpg_database_abilities['.$ability_token.']</strong><br />';
    //$this_page_markup .= '<pre>'.print_r($ability_data, true).'</pre><br /><hr /><br />';
    $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
    //$this_page_markup .= '<pre>'.print_r(mmrpg_ability::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
    $this_page_markup .= '</p><hr />';

    $ability_key++;
  }
}
// Otherwise, if empty, we're done!
else {
  $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL ROBOT HAVE BEEN IMPORTED UPDATED!</strong></p>';
}




// -- MMRPG IMPORT ITEMS -- //


// Sort the item index based on item number
$temp_pattern_first = array();
$temp_pattern_first[] = '/^item-screw-small$/i';
$temp_pattern_first[] = '/^item-screw-large$/i';
$temp_pattern_first[] = '/^item-energy-pellet$/i';
$temp_pattern_first[] = '/^item-energy-capsule$/i';
$temp_pattern_first[] = '/^item-weapon-pellet$/i';
$temp_pattern_first[] = '/^item-weapon-capsule$/i';
$temp_pattern_first[] = '/^item-energy-tank$/i';
$temp_pattern_first[] = '/^item-weapon-tank$/i';
$temp_pattern_first[] = '/^item-extra-life$/i';
$temp_pattern_first[] = '/^item-yashichi$/i';
$temp_pattern_first[] = '/^item-attack-pellet$/i';
$temp_pattern_first[] = '/^item-attack-capsule$/i';
$temp_pattern_first[] = '/^item-defense-pellet$/i';
$temp_pattern_first[] = '/^item-defense-capsule$/i';
$temp_pattern_first[] = '/^item-speed-pellet$/i';
$temp_pattern_first[] = '/^item-speed-capsule$/i';
$temp_pattern_first[] = '/^item-super-pellet$/i';
$temp_pattern_first[] = '/^item-super-capsule$/i';
//die('$mmrpg_index[\'types\'] = <pre>'.print_r($mmrpg_database_types, true).'</pre>');
//$temp_element_types = $mmrpg_database_types; //array('none', 'copy', 'crystal', 'cutter', 'earth', 'electric', 'explode', 'flame', 'freeze', 'impact', 'laser', 'missile', 'nature', 'shadow', 'shield', 'space', 'swift', 'time', 'water', 'wind');
foreach ($mmrpg_database_types AS $type_token => $type_info){
  if ($type_token == 'none' || $type_token == 'copy'){ continue; }
  if (!empty($type_info['type_class']) && $type_info['type_class'] == 'special'){ continue; }
  $temp_pattern_first[] = '/^item-shard-'.$type_token.'$/i';
  $temp_pattern_first[] = '/^item-core-'.$type_token.'$/i';
  $temp_pattern_first[] = '/^item-star-'.$type_token.'$/i';
}
$temp_pattern_first[] = '/^item-shard-none$/i';
$temp_pattern_first[] = '/^item-core-none$/i';
$temp_pattern_first[] = '/^item-star-none$/i';
$temp_pattern_first[] = '/^item-shard-copy$/i';
$temp_pattern_first[] = '/^item-core-copy$/i';
$temp_pattern_first[] = '/^item-star-copy$/i';
$temp_pattern_first[] = '/^item-energy-upgrade$/i';
$temp_pattern_first[] = '/^item-weapon-upgrade$/i';
$temp_pattern_first[] = '/^item-attack-booster$/i';
$temp_pattern_first[] = '/^item-defense-booster$/i';
$temp_pattern_first[] = '/^item-speed-booster$/i';
$temp_pattern_first[] = '/^item-field-booster$/i';
$temp_pattern_first[] = '/^item-target-module$/i';
$temp_pattern_first[] = '/^item-charge-module$/i';
$temp_pattern_first[] = '/^item-growth-module$/i';
$temp_pattern_first[] = '/^item-fortune-module$/i';
$temp_pattern_first[] = '/^item-score-ball-red$/i';
$temp_pattern_first[] = '/^item-score-ball-blue$/i';
$temp_pattern_first[] = '/^item-score-ball-green$/i';
$temp_pattern_first[] = '/^item-score-ball-purple$/i';
//die('$temp_pattern_first = <pre>'.print_r($temp_pattern_first, true).'</pre>');
$temp_pattern_last = array();
//$temp_pattern_last[] = '/^item-heart$/i';
//$temp_pattern_last[] = '/^item-star$/i';
$temp_pattern_last = array_reverse($temp_pattern_last);
function mmrpg_index_sort_items($item_one, $item_two){
  // Pull in global variables
  global $temp_pattern_first, $temp_pattern_last;
  // Loop through all the temp patterns and compare them one at a time
  foreach ($temp_pattern_first AS $key => $pattern){
    // Check if either of these two items matches the current pattern
    if (preg_match($pattern, $item_one['ability_token']) && !preg_match($pattern, $item_two['ability_token'])){ return -1; }
    elseif (!preg_match($pattern, $item_one['ability_token']) && preg_match($pattern, $item_two['ability_token'])){ return 1; }
  }
  foreach ($temp_pattern_last AS $key => $pattern){
    // Check if either of these two items matches the current pattern
    if (preg_match($pattern, $item_one['ability_token']) && !preg_match($pattern, $item_two['ability_token'])){ return 1; }
    elseif (!preg_match($pattern, $item_one['ability_token']) && preg_match($pattern, $item_two['ability_token'])){ return -1; }
  }
  // If only one of the two items has a type, the one with goes first
  if (!empty($item_one['ability_token']) && empty($item_two['ability_token'])){ return 1; }
  elseif (empty($item_one['ability_token']) && !empty($item_two['ability_token'])){ return -1; }
  else {
    // If only one of the two items has a type, the one with goes first
    if ($item_one['ability_token'] > $item_two['ability_token']){ return 1; }
    elseif ($item_one['ability_token'] < $item_two['ability_token']){ return -1; }
    else {
      // Return 0 by default
      return 0;
    }
  }
}
uasort($mmrpg_index['items'], 'mmrpg_index_sort_items');

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_items</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_index['items']) ? count($mmrpg_index['items']) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_items, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';

// Loop through each of the ability info arrays
$item_key = 0;
$item_order = 0;
//$temp_empty = $mmrpg_index['items']['ability'];
//unset($mmrpg_index['items']['ability']);
//array_unshift($mmrpg_index['items'], $temp_empty);
if (!empty($mmrpg_index['items'])){
  foreach ($mmrpg_index['items'] AS $item_token => $item_data){

    // If this ability's image exists, assign it
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/abilities/'.$item_token.'/')){ $item_data['ability_image'] = $item_data['ability_token']; }
    else { $item_data['ability_image'] = 'ability'; }

    // Define the insert array and start populating it with basic details
    $temp_insert_array = array();
    //$temp_insert_array['ability_id'] = isset($item_data['ability_id']) ? $item_data['ability_id'] : $item_key;
    $temp_insert_array['ability_token'] = $item_data['ability_token'];
    $temp_insert_array['ability_name'] = !empty($item_data['ability_name']) ? $item_data['ability_name'] : '';
    $temp_insert_array['ability_game'] = !empty($item_data['ability_game']) ? $item_data['ability_game'] : '';
    $temp_insert_array['ability_group'] = !empty($item_data['ability_group']) ? $item_data['ability_group'] : '';
    $temp_insert_array['ability_class'] = !empty($item_data['ability_class']) ? $item_data['ability_class'] : 'master';
    $temp_insert_array['ability_subclass'] = !empty($item_data['ability_subclass']) ? $item_data['ability_subclass'] : '';
    $temp_insert_array['ability_image'] = !empty($item_data['ability_image']) ? $item_data['ability_image'] : '';
    $temp_insert_array['ability_image_sheets'] = isset($item_data['ability_image_sheets']) ? $item_data['ability_image_sheets'] : 1;
    $temp_insert_array['ability_image_size'] = !empty($item_data['ability_image_size']) ? $item_data['ability_image_size'] : 40;
    $temp_insert_array['ability_image_editor'] = !empty($item_data['ability_image_editor']) ? $item_data['ability_image_editor'] : ($item_data['ability_image'] != 'ability' ? 412 : 0);
    $temp_insert_array['ability_type'] = !empty($item_data['ability_type']) ? $item_data['ability_type'] : '';
    $temp_insert_array['ability_type2'] = !empty($item_data['ability_type2']) ? $item_data['ability_type2'] : '';
    $temp_insert_array['ability_description'] = !empty($item_data['ability_description']) && $item_data['ability_description'] != '...' ? $item_data['ability_description'] : '';
    $temp_insert_array['ability_description2'] = !empty($item_data['ability_description2']) && $item_data['ability_description2'] != '...' ? $item_data['ability_description2'] : '';
    $temp_insert_array['ability_speed'] = !empty($item_data['ability_speed']) ? $item_data['ability_speed'] : 1;
    $temp_insert_array['ability_energy'] = isset($item_data['ability_energy']) ? $item_data['ability_energy'] : 1;
    $temp_insert_array['ability_energy_percent'] = !empty($item_data['ability_energy_percent']) ? 1 : 0;
    $temp_insert_array['ability_damage'] = !empty($item_data['ability_damage']) ? $item_data['ability_damage'] : 0;
    $temp_insert_array['ability_damage_percent'] = !empty($item_data['ability_damage_percent']) ? 1 : 0;
    $temp_insert_array['ability_damage2'] = !empty($item_data['ability_damage2']) ? $item_data['ability_damage2'] : 0;
    $temp_insert_array['ability_damage2_percent'] = !empty($item_data['ability_damage2_percent']) ? 1 : 0;
    $temp_insert_array['ability_recovery'] = !empty($item_data['ability_recovery']) ? $item_data['ability_recovery'] : 0;
    $temp_insert_array['ability_recovery_percent'] = !empty($item_data['ability_recovery_percent']) ? 1 : 0;
    $temp_insert_array['ability_recovery2'] = !empty($item_data['ability_recovery2']) ? $item_data['ability_recovery2'] : 0;
    $temp_insert_array['ability_recovery2_percent'] = !empty($item_data['ability_recovery2_percent']) ? 1 : 0;
    $temp_insert_array['ability_accuracy'] = !empty($item_data['ability_accuracy']) ? $item_data['ability_accuracy'] : 0;
    $temp_insert_array['ability_target'] = !empty($item_data['ability_target']) ? $item_data['ability_target'] : 'auto';
    $temp_insert_array['ability_functions'] = !empty($item_data['ability_functions']) ? $item_data['ability_functions'] : 'abilities/ability.php';

    // Define the ability frame properties
    $temp_insert_array['ability_frame'] = !empty($item_data['ability_frame']) ? $item_data['ability_frame'] : 'base';
    $temp_insert_array['ability_frame_animate'] = json_encode(!empty($item_data['ability_frame_animate']) ? $item_data['ability_frame_animate'] : array());
    $temp_insert_array['ability_frame_index'] = json_encode(!empty($item_data['ability_frame_index']) ? $item_data['ability_frame_index'] : array());
    $temp_insert_array['ability_frame_offset'] = json_encode(!empty($item_data['ability_frame_offset']) ? $item_data['ability_frame_offset'] : array());
    //$temp_insert_array['ability_frame_animate'] = array();
    //if (!empty($item_data['ability_frame_animate'])){ foreach ($item_data['ability_frame_animate'] AS $key => $token){ $temp_insert_array['ability_frame_animate'][] = '['.$token.']'; } }
    //$temp_insert_array['ability_frame_animate'] = implode(',', $temp_insert_array['ability_frame_animate']);
    //$temp_insert_array['ability_frame_index'] = array();
    //if (!empty($item_data['ability_frame_index'])){ foreach ($item_data['ability_frame_index'] AS $key => $token){ $temp_insert_array['ability_frame_index'][] = '['.$token.']'; } }
    //$temp_insert_array['ability_frame_index'] = implode(',', $temp_insert_array['ability_frame_index']);
    //$temp_insert_array['ability_frame_offset'] = array();
    //if (!empty($item_data['ability_frame_offset'])){ foreach ($item_data['ability_frame_offset'] AS $key => $token){ $temp_insert_array['ability_frame_offset'][] = '['.$key.':'.$token.']'; } }
    //$temp_insert_array['ability_frame_offset'] = implode(',', $temp_insert_array['ability_frame_offset']);
    $temp_insert_array['ability_frame_styles'] = !empty($item_data['ability_frame_styles']) ? $item_data['ability_frame_styles'] : '';
    $temp_insert_array['ability_frame_classes'] = !empty($item_data['ability_frame_classes']) ? $item_data['ability_frame_classes'] : '';

    // Define the ability frame properties
    $temp_insert_array['attachment_frame'] = !empty($item_data['attachment_frame']) ? $item_data['attachment_frame'] : 'base';
    $temp_insert_array['attachment_frame_animate'] = json_encode(!empty($item_data['attachment_frame_animate']) ? $item_data['attachment_frame_animate'] : array());
    $temp_insert_array['attachment_frame_index'] = json_encode(!empty($item_data['attachment_frame_index']) ? $item_data['attachment_frame_index'] : array());
    $temp_insert_array['attachment_frame_offset'] = json_encode(!empty($item_data['attachment_frame_offset']) ? $item_data['attachment_frame_offset'] : array());
    //$temp_insert_array['attachment_frame_animate'] = array();
    //if (!empty($item_data['attachment_frame_animate'])){ foreach ($item_data['attachment_frame_animate'] AS $key => $token){ $temp_insert_array['attachment_frame_animate'][] = '['.$token.']'; } }
    //$temp_insert_array['attachment_frame_animate'] = implode(',', $temp_insert_array['attachment_frame_animate']);
    //$temp_insert_array['attachment_frame_index'] = array();
    //if (!empty($item_data['attachment_frame_index'])){ foreach ($item_data['attachment_frame_index'] AS $key => $token){ $temp_insert_array['attachment_frame_index'][] = '['.$token.']'; } }
    //$temp_insert_array['attachment_frame_index'] = implode(',', $temp_insert_array['attachment_frame_index']);
    //$temp_insert_array['attachment_frame_offset'] = array();
    //if (!empty($item_data['attachment_frame_offset'])){ foreach ($item_data['attachment_frame_offset'] AS $key => $token){ $temp_insert_array['attachment_frame_offset'][] = '['.$key.':'.$token.']'; } }
    //$temp_insert_array['attachment_frame_offset'] = implode(',', $temp_insert_array['attachment_frame_offset']);
    $temp_insert_array['attachment_frame_styles'] = !empty($item_data['attachment_frame_styles']) ? $item_data['attachment_frame_styles'] : '';
    $temp_insert_array['attachment_frame_classes'] = !empty($item_data['attachment_frame_classes']) ? $item_data['attachment_frame_classes'] : '';

    // Define the flags
    $temp_insert_array['ability_flag_hidden'] = $temp_insert_array['ability_class'] != 'master' || in_array($temp_insert_array['ability_token'], array('ability', 'attachment-defeat')) ? 1 : 0;
    $temp_insert_array['ability_flag_complete'] = $temp_insert_array['ability_class'] == 'system' || $item_data['ability_image'] != 'ability' ? 1 : 0;
    $temp_insert_array['ability_flag_published'] = 1;

    // Define the order counter
    if ($temp_insert_array['ability_class'] != 'system'){
      $temp_insert_array['ability_order'] = $item_order;
      $item_order++;
    } else {
      $temp_insert_array['ability_order'] = 0;
    }

    // Check if this ability already exists in the database
    $temp_success = true;
    $temp_exists = $DB->get_array("SELECT ability_token FROM mmrpg_index_abilities WHERE ability_token LIKE '{$temp_insert_array['ability_token']}' LIMIT 1") ? true : false;
    if (!$temp_exists){ $temp_success = $DB->insert('mmrpg_index_abilities', $temp_insert_array); }
    else { $temp_success = $DB->update('mmrpg_index_abilities', $temp_insert_array, array('ability_token' => $temp_insert_array['ability_token'])); }

    // Print out the generated insert array
    $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
    $this_page_markup .= '<strong>$mmrpg_database_items['.$item_token.']</strong><br />';
    //$this_page_markup .= '<pre>'.print_r($item_data, true).'</pre><br /><hr /><br />';
    $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
    //$this_page_markup .= '<pre>'.print_r(mmrpg_ability::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
    $this_page_markup .= '</p><hr />';

    $item_key++;
  }
}
// Otherwise, if empty, we're done!
else {
  $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL ITEM HAVE BEEN IMPORTED UPDATED!</strong></p>';
}



?>