<?
// Pull in global variables
global $mmrpg_index;
global $session_token;
// Collect values for potentially missing global variables
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

if (empty($robot_info)){ return false; }
if (empty($ability_info)){ return false; }

$print_options['show_accuracy'] = isset($print_options['show_accuracy']) ? $print_options['show_accuracy'] : true;
$print_options['show_quantity'] = isset($print_options['show_quantity']) ? $print_options['show_quantity'] : true;

$robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
$temp_ability_token = $ability_info['ability_token'];
$temp_ability_type = !empty($ability_info['ability_type']) ? $mmrpg_index['types'][$ability_info['ability_type']] : false;
$temp_ability_type2 = !empty($ability_info['ability_type2']) ? $mmrpg_index['types'][$ability_info['ability_type2']] : false;
$temp_ability_energy = mmrpg_robot::calculate_weapon_energy_static($robot_info, $ability_info);
$temp_ability_damage = !empty($ability_info['ability_damage']) ? $ability_info['ability_damage'] : 0;
$temp_ability_damage2 = !empty($ability_info['ability_damage2']) ? $ability_info['ability_damage2'] : 0;
$temp_ability_recovery = !empty($ability_info['ability_recovery']) ? $ability_info['ability_recovery'] : 0;
$temp_ability_recovery2 = !empty($ability_info['ability_recovery2']) ? $ability_info['ability_recovery2'] : 0;
$temp_ability_target = !empty($ability_info['ability_target']) ? $ability_info['ability_target'] : 'auto';
while (!in_array($ability_info['ability_token'], $robot_info['robot_abilities'])){
  if (!$robot_flag_copycore){
    if (empty($robot_ability_core)){ break; }
    elseif (empty($temp_ability_type) && empty($temp_ability_type2)){ break; }
    else {
      $temp_type_array = array();
      if (!empty($temp_ability_type)){ $temp_type_array[] = $temp_ability_type['type_token']; }
      if (!empty($temp_ability_type2)){ $temp_type_array[] = $temp_ability_type2['type_token']; }
      if (!in_array($robot_ability_core, $temp_type_array)){ break; }
    }
  }
  break;
}

$temp_ability_title = $ability_info['ability_name'];
if (!empty($temp_ability_type)){ $temp_ability_title .= ' ('.$temp_ability_type['type_name'].' Type)'; }
if (!empty($temp_ability_type2)){ $temp_ability_title = str_replace('Type', '/ '.$temp_ability_type2['type_name'].' Type', $temp_ability_title); }

if ($ability_info['ability_class'] != 'item'){
  if (!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])){ $temp_ability_title .= '  // '; }
  elseif (empty($ability_info['ability_damage']) && empty($ability_info['ability_recovery'])){ $temp_ability_title .= '  // '; }
  if (!empty($ability_info['ability_damage']) && !empty($ability_info['ability_recovery'])){ $temp_ability_title .= $ability_info['ability_damage'].' Damage | '.$ability_info['ability_recovery'].' Recovery'; }
  elseif (!empty($ability_info['ability_damage'])){ $temp_ability_title .= $ability_info['ability_damage'].' Damage'; }
  elseif (!empty($ability_info['ability_recovery'])){ $temp_ability_title .= $ability_info['ability_recovery'].' Recovery '; }
  if (!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])){ $temp_ability_title .= '  | '; }
}

//if (empty($ability_info['ability_damage']) && empty($ability_info['ability_recovery'])){ $temp_ability_title .= 'Special'; }

// If show accuracy or quantity
if (($ability_info['ability_class'] != 'item' && $print_options['show_accuracy'])
  || ($ability_info['ability_class'] == 'item' && $print_options['show_quantity'])){

  $temp_ability_title .= '  | ';
  if ($ability_info['ability_class'] != 'item' && !empty($ability_info['ability_accuracy'])){ $temp_ability_title .= ' '.$ability_info['ability_accuracy'].'% Accuracy'; }
  elseif ($ability_info['ability_class'] == 'item' && !empty($_SESSION[$session_token]['values']['battle_items'][$temp_ability_token])){ $temp_ability_title .= ' '.($_SESSION[$session_token]['values']['battle_items'][$temp_ability_token] == 1 ? '1 Unit' : $_SESSION[$session_token]['values']['battle_items'][$temp_ability_token].' Units'); }
  elseif ($ability_info['ability_class'] == 'item' ){ $temp_ability_title .= ' 0 Units'; }

}

if ($ability_info['ability_class'] != 'item' && !empty($temp_ability_energy)){ $temp_ability_title .= ' | '.$temp_ability_energy.' Energy'; }
if ($ability_info['ability_class'] != 'item' && $temp_ability_target != 'auto'){ $temp_ability_title .= ' | Select Target'; }

if (!empty($ability_info['ability_description'])){
  $temp_find = array('{RECOVERY}', '{RECOVERY2}', '{DAMAGE}', '{DAMAGE2}');
  $temp_replace = array($temp_ability_recovery, $temp_ability_recovery2, $temp_ability_damage, $temp_ability_damage2);
  $temp_description = str_replace($temp_find, $temp_replace, $ability_info['ability_description']);
  $temp_ability_title .= ' // '.$temp_description;
}

?>