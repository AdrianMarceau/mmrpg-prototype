<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url, $DB;
global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
global $key_counter, $player_rewards, $player_item_rewards, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
global $mmrpg_database_abilities;
global $session_token;
// Collect values for potentially missing global variables
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

if (empty($robot_info)){ return false; }
if (empty($item_info)){ return false; }

$item_info_token = $item_info['ability_token'];
$item_info_count = !empty($_SESSION[$session_token]['values']['battle_items'][$item_info_token]) ? $_SESSION[$session_token]['values']['battle_items'][$item_info_token] : 0;
$item_info_name = $item_info['ability_name'];
$item_info_type = !empty($item_info['ability_type']) ? $item_info['ability_type'] : false;
$item_info_type2 = !empty($item_info['ability_type2']) ? $item_info['ability_type2'] : false;
if (!empty($item_info_type) && !empty($mmrpg_index['types'][$item_info_type])){
  $item_info_type = $mmrpg_index['types'][$item_info_type]['type_name'].' Type';
  if (!empty($item_info_type2) && !empty($mmrpg_index['types'][$item_info_type2])){
    $item_info_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$item_info_type2]['type_name'].' Type', $item_info_type);
  }
} else {
  $item_info_type = '';
}
$item_info_energy = isset($item_info['ability_energy']) ? $item_info['ability_energy'] : 4;
$item_info_damage = !empty($item_info['ability_damage']) ? $item_info['ability_damage'] : 0;
$item_info_damage2 = !empty($item_info['ability_damage2']) ? $item_info['ability_damage2'] : 0;
$item_info_damage_percent = !empty($item_info['ability_damage_percent']) ? true : false;
$item_info_damage2_percent = !empty($item_info['ability_damage2_percent']) ? true : false;
if ($item_info_damage_percent && $item_info_damage > 100){ $item_info_damage = 100; }
if ($item_info_damage2_percent && $item_info_damage2 > 100){ $item_info_damage2 = 100; }
$item_info_recovery = !empty($item_info['ability_recovery']) ? $item_info['ability_recovery'] : 0;
$item_info_recovery2 = !empty($item_info['ability_recovery2']) ? $item_info['ability_recovery2'] : 0;
$item_info_recovery_percent = !empty($item_info['ability_recovery_percent']) ? true : false;
$item_info_recovery2_percent = !empty($item_info['ability_recovery2_percent']) ? true : false;
if ($item_info_recovery_percent && $item_info_recovery > 100){ $item_info_recovery = 100; }
if ($item_info_recovery2_percent && $item_info_recovery2 > 100){ $item_info_recovery2 = 100; }
$item_info_accuracy = !empty($item_info['ability_accuracy']) ? $item_info['ability_accuracy'] : 0;
$item_info_description = !empty($item_info['ability_description']) ? $item_info['ability_description'] : '';
$item_info_description = str_replace('{DAMAGE}', $item_info_damage, $item_info_description);
$item_info_description = str_replace('{RECOVERY}', $item_info_recovery, $item_info_description);
$item_info_description = str_replace('{DAMAGE2}', $item_info_damage2, $item_info_description);
$item_info_description = str_replace('{RECOVERY2}', $item_info_recovery2, $item_info_description);
$item_info_class_type = !empty($item_info['ability_type']) ? $item_info['ability_type'] : 'none';
if (!empty($item_info['ability_type2'])){ $item_info_class_type = $item_info_class_type != 'none' ? $item_info_class_type.'_'.$item_info['ability_type2'] : $item_info['ability_type2']; }
$item_info_title = mmrpg_item::print_editor_title_markup($robot_info, $item_info);
$item_info_title_plain = strip_tags(str_replace('<br />', '//', $item_info_title));
$item_info_title_tooltip = htmlentities($item_info_title, ENT_QUOTES, 'UTF-8');
$item_info_title_html = str_replace(' ', '&nbsp;', $item_info_name);
$item_info_title_html .= '<span class="count">x '.$item_info_count.'</span>';
$temp_select_options = str_replace('value="'.$item_info_token.'"', 'value="'.$item_info_token.'" selected="selected" disabled="disabled"', $item_rewards_options);
$item_info_title_html = '<label style="background-image: url(i/a/'.$item_info_token.'/il40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$item_info_title_html.'</label>';
//if ($global_allow_editing){ $item_info_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'">'.$temp_select_options.'</select>'; }
$this_select_markup = '<a class="item_name type type_'.$item_info_class_type.'" style="'.(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-item="'.$item_info_token.'" data-count="'.$item_info_count.'" title="'.$item_info_title_plain.'" data-tooltip="'.$item_info_title_tooltip.'">'.$item_info_title_html.'</a>';

?>