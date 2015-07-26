<?
// Pull in global variables
global $mmrpg_index;
global $session_token;
// Collect values for potentially missing global variables
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

// Generate the ability option markup
if (empty($robot_info)){ return false; }
if (empty($ability_info)){ return false; }
//$ability_info = mmrpg_ability::get_index_info($temp_ability_token);
$temp_robot_token = $robot_info['robot_token'];
$temp_ability_token = $ability_info['ability_token'];
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, $temp_robot_token.'/'.$temp_ability_token.'/'."\nrobot_abilities = ".array_keys($robot_info['robot_abilities'])."\nrobot_index_abilities = ".array_keys($robot_info['robot_index_abilities']));  }
$robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
$robot_flag_copycore = !empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy' ? true : false;
$temp_ability_type = !empty($ability_info['ability_type']) ? $mmrpg_index['types'][$ability_info['ability_type']] : false;
$temp_ability_type2 = !empty($ability_info['ability_type2']) ? $mmrpg_index['types'][$ability_info['ability_type2']] : false;
$temp_ability_energy = mmrpg_robot::calculate_weapon_energy_static($robot_info, $ability_info);
$temp_type_array = array();
$temp_incompatible = false;
$temp_global_abilities = self::get_global_abilities();
$temp_index_abilities = !empty($robot_info['robot_index_abilities']) ? $robot_info['robot_index_abilities'] : array();
$temp_current_abilities = !empty($robot_info['robot_abilities']) ? array_keys($robot_info['robot_abilities']) : array();
$temp_compatible_abilities = array_merge($temp_global_abilities, $temp_index_abilities, $temp_current_abilities);
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, $temp_robot_token.'/'.$temp_ability_token.'/'."\nindex_abilities = ".implode(',', $temp_index_abilities)."\ncurrent_abilities = ".implode(',', $temp_current_abilities)."\ncompatible_abilities = ".implode(',', $temp_compatible_abilities));  }
//while (!in_array($temp_ability_token, $robot_info['robot_abilities'])){
while (!in_array($temp_ability_token, $temp_compatible_abilities)){
  if (!$robot_flag_copycore){
    if (empty($robot_ability_core)){ $temp_incompatible = true; break; }
    elseif (empty($temp_ability_type) && empty($temp_ability_type2)){ $temp_incompatible = true; break; }
    else {
      if (!empty($temp_ability_type)){ $temp_type_array[] = $temp_ability_type['type_token']; }
      if (!empty($temp_ability_type2)){ $temp_type_array[] = $temp_ability_type2['type_token']; }
      if (!in_array($robot_ability_core, $temp_type_array)){ $temp_incompatible = true; break; }
    }
  }
  break;
}
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, $temp_robot_token.'/'.$temp_ability_token.'/'.($temp_incompatible ? 'incompatible' : 'compatible'));  }
if ($temp_incompatible == true){ return false; }
$temp_ability_label = $ability_info['ability_name'];
$temp_ability_title = mmrpg_ability::print_editor_title_markup($robot_info, $ability_info);
$temp_ability_title_plain = strip_tags(str_replace('<br />', '&#10;', $temp_ability_title));
$temp_ability_title_tooltip = htmlentities($temp_ability_title, ENT_QUOTES, 'UTF-8');
$temp_ability_option = $ability_info['ability_name'];
if (!empty($temp_ability_type)){ $temp_ability_option .= ' | '.$temp_ability_type['type_name']; }
if (!empty($temp_ability_type2)){ $temp_ability_option .= ' / '.$temp_ability_type2['type_name']; }
if (!empty($ability_info['ability_damage'])){ $temp_ability_option .= ' | D:'.$ability_info['ability_damage']; }
if (!empty($ability_info['ability_recovery'])){ $temp_ability_option .= ' | R:'.$ability_info['ability_recovery']; }
if ($ability_info['ability_class'] != 'item' && !empty($ability_info['ability_accuracy'])){ $temp_ability_option .= ' | A:'.$ability_info['ability_accuracy']; }
elseif ($ability_info['ability_class'] == 'item' && !empty($_SESSION[$session_token]['values']['battle_items'][$temp_ability_token])){ $temp_ability_option .= ' | U:'.$_SESSION[$session_token]['values']['battle_items'][$temp_ability_token]; }
elseif ($ability_info['ability_class'] == 'item'){ $temp_ability_option .= ' | U:0'; }
if (!empty($temp_ability_energy)){ $temp_ability_option .= ' | E:'.$temp_ability_energy; }

// Return the generated option markup
$this_option_markup = '<option value="'.$temp_ability_token.'" data-label="'.$temp_ability_label.'" data-type="'.(!empty($temp_ability_type) ? $temp_ability_type['type_token'] : 'none').'" data-type2="'.(!empty($temp_ability_type2) ? $temp_ability_type2['type_token'] : '').'" title="'.$temp_ability_title_plain.'" data-tooltip="'.$temp_ability_title_tooltip.'">'.$temp_ability_option.'</option>';

?>