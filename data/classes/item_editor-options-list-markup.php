<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url, $DB;
global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
global $key_counter, $player_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
global $mmrpg_database_abilities;
global $session_token;
// Collect values for potentially missing global variables
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

if (empty($player_info)){ return false; }
if (empty($robot_info)){ return false; }

$player_item_options = array();
$player_items_unlocked = array_keys($player_item_rewards);
if (!empty($mmrpg_database_abilities)){
  $temp_category = 'special-weapons';
  foreach ($mmrpg_database_abilities AS $item_token => $item_info){
    if ($item_token == 'energy-boost'){ $temp_category = 'support-abilities'; }
    if (!in_array($item_token, $player_items_unlocked)){ continue; }
    $item_info = mmrpg_item::parse_index_info($item_info);
    $option_markup = mmrpg_item::print_editor_option_markup($robot_info, $item_info);
    $player_item_options[$temp_category][] = $option_markup;
  }
}
if (!empty($player_item_options)){
  foreach ($player_item_options AS $category_token => $item_options){
    $category_name = ucwords(str_replace('-', ' ', $category_token));
    $this_options_markup .= '<optgroup label="'.$category_name.'">'.implode('', $item_options).'</optgroup>';
  }
}

/*
$robot_ability_rewards_options = array();
foreach ($robot_ability_rewards AS $temp_ability_info){
  if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
  $temp_token = $temp_ability_info['ability_token'];
  $temp_ability_info = mmrpg_item::parse_index_info($mmrpg_database_abilities[$temp_token]);
  $temp_option_markup = mmrpg_item::print_editor_option_markup($robot_info, $temp_ability_info);
  if (!empty($temp_option_markup)){ $robot_ability_rewards_options[] = $temp_option_markup; }
}
$robot_ability_rewards_options = '<optgroup label="Robot Abilities">'.implode('', $robot_ability_rewards_options).'</optgroup>';
$this_options_markup .= $robot_ability_rewards_options;
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $this_options_markup = '.htmlentities($this_options_markup, ENT_QUOTES, 'UTF-8', true));  }
*/

/*
$player_item_weapon_options = array();
$player_item_support_options = array();
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $player_item_rewards = '.implode(',', array_keys($player_item_rewards)));  }
foreach ($player_item_rewards AS $temp_ability_key => $temp_ability_info){
  if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
  $temp_token = $temp_ability_info['ability_token'];
  $temp_ability_info = mmrpg_item::parse_index_info($mmrpg_database_abilities[$temp_token]);
  $temp_option_markup = mmrpg_item::print_editor_option_markup($robot_info, $temp_ability_info);

  if (!empty($temp_option_markup)){
    if ($temp_category == 'weapon'){ $player_item_weapon_options[] = $temp_option_markup; }
    elseif ($temp_category == 'support'){ $player_item_support_options[] = $temp_option_markup; }
  }
}
$player_item_weapon_options = '<optgroup label="Special Weapons">'.implode('', $player_item_weapon_options).'</optgroup>';
$player_item_support_options = '<optgroup label="Support Abilities">'.implode('', $player_item_support_options).'</optgroup>';
$this_options_markup .= $player_item_weapon_options;
$this_options_markup .= $player_item_support_options;
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $this_options_markup = '.htmlentities($this_options_markup, ENT_QUOTES, 'UTF-8', true));  }
*/

// Add an option at the bottom to remove the ability
$this_options_markup .= '<optgroup label="Ability Actions">';
$this_options_markup .= '<option value="" title="">- Remove Ability -</option>';
$this_options_markup .= '</optgroup>';
?>