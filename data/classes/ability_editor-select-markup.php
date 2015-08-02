<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url, $DB;
global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
global $key_counter, $player_rewards, $player_ability_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
global $mmrpg_database_abilities;
global $session_token;
// Collect values for potentially missing global variables
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

if (empty($robot_info)){ return false; }
if (empty($ability_info)){ return false; }

$ability_info_id = $ability_info['ability_id'];
$ability_info_token = $ability_info['ability_token'];
$ability_info_name = $ability_info['ability_name'];
$ability_info_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : false;
$ability_info_type2 = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : false;
if (!empty($ability_info_type) && !empty($mmrpg_index['types'][$ability_info_type])){
  $ability_info_type = $mmrpg_index['types'][$ability_info_type]['type_name'].' Type';
  if (!empty($ability_info_type2) && !empty($mmrpg_index['types'][$ability_info_type2])){
    $ability_info_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$ability_info_type2]['type_name'].' Type', $ability_info_type);
  }
} else {
  $ability_info_type = '';
}
$ability_info_energy = isset($ability_info['ability_energy']) ? $ability_info['ability_energy'] : 4;
$ability_info_damage = !empty($ability_info['ability_damage']) ? $ability_info['ability_damage'] : 0;
$ability_info_damage2 = !empty($ability_info['ability_damage2']) ? $ability_info['ability_damage2'] : 0;
$ability_info_damage_percent = !empty($ability_info['ability_damage_percent']) ? true : false;
$ability_info_damage2_percent = !empty($ability_info['ability_damage2_percent']) ? true : false;
if ($ability_info_damage_percent && $ability_info_damage > 100){ $ability_info_damage = 100; }
if ($ability_info_damage2_percent && $ability_info_damage2 > 100){ $ability_info_damage2 = 100; }
$ability_info_recovery = !empty($ability_info['ability_recovery']) ? $ability_info['ability_recovery'] : 0;
$ability_info_recovery2 = !empty($ability_info['ability_recovery2']) ? $ability_info['ability_recovery2'] : 0;
$ability_info_recovery_percent = !empty($ability_info['ability_recovery_percent']) ? true : false;
$ability_info_recovery2_percent = !empty($ability_info['ability_recovery2_percent']) ? true : false;
if ($ability_info_recovery_percent && $ability_info_recovery > 100){ $ability_info_recovery = 100; }
if ($ability_info_recovery2_percent && $ability_info_recovery2 > 100){ $ability_info_recovery2 = 100; }
$ability_info_accuracy = !empty($ability_info['ability_accuracy']) ? $ability_info['ability_accuracy'] : 0;
$ability_info_description = !empty($ability_info['ability_description']) ? $ability_info['ability_description'] : '';
$ability_info_description = str_replace('{DAMAGE}', $ability_info_damage, $ability_info_description);
$ability_info_description = str_replace('{RECOVERY}', $ability_info_recovery, $ability_info_description);
$ability_info_description = str_replace('{DAMAGE2}', $ability_info_damage2, $ability_info_description);
$ability_info_description = str_replace('{RECOVERY2}', $ability_info_recovery2, $ability_info_description);
$ability_info_title = mmrpg_ability::print_editor_title_markup($robot_info, $ability_info);
$ability_info_title_plain = strip_tags(str_replace('<br />', '//', $ability_info_title));
$ability_info_title_tooltip = htmlentities($ability_info_title, ENT_QUOTES, 'UTF-8');
$ability_info_title_html = str_replace(' ', '&nbsp;', $ability_info_name);
$temp_select_options = str_replace('value="'.$ability_info_token.'"', 'value="'.$ability_info_token.'" selected="selected" disabled="disabled"', $ability_rewards_options);
$ability_info_title_html = '<label style="background-image: url(i/a/'.$ability_info_token.'/il40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$ability_info_title_html.'<span class="arrow">&#8711;</span></label>';
//if ($global_allow_editing){ $ability_info_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'">'.$temp_select_options.'</select>'; }
$this_select_markup = '<a class="ability_name ability_type ability_type_'.(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').(!empty($ability_info['ability_type2']) ? '_'.$ability_info['ability_type2'] : '').'" style="'.(!$global_allow_editing ? 'cursor: default; ' : '').'" data-id="'.$ability_info_id.'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="'.$ability_info_token.'" data-type="'.(!empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none').'" data-type2="'.(!empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : '').'" title="'.$ability_info_title_plain.'" data-tooltip="'.$ability_info_title_tooltip.'">'.$ability_info_title_html.'</a>';

?>