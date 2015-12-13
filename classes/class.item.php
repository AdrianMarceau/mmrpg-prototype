<?php
/**
 * Mega Man RPG Item
 * <p>The object class for all items in the Mega Man RPG Prototype.</p>
 */
class rpg_item extends rpg_ability {

  /**
   * Create a new RPG item object
   * @param rpg_player $this_player
   * @param rpg_robot $this_robot
   * @param array $item_info (optional)
   * @return rpg_item
   */
  function rpg_item(rpg_player $this_player, rpg_robot $this_robot, $item_info = array()) {

    // Update the session keys for this object
    $this->session_key = 'ITEMS';
    $this->session_token = 'ability_token';
    $this->session_id = 'ability_id';
    $this->class = 'ability';

    // Collect any provided arguments
    $args = func_get_args();

    // Define the internal battle pointer
    $this->battle = rpg_battle::get_battle();
    $this->battle_id = $this->battle->battle_id;
    $this->battle_token = $this->battle->battle_token;

    // Define the internal battle pointer
    $this->field = rpg_field::get_field;
    $this->field_id = $this->field->field_id;
    $this->field_token = $this->field->field_token;

    // Define the internal player values using the provided array
    $this->player = $this_player;
    $this->player_id = $this_player->player_id;
    $this->player_token = $this_player->player_token;

    // Define the internal robot values using the provided array
    $this->robot = $this_robot;
    $this->robot_id = $this_robot->robot_id;
    $this->robot_token = $this_robot->robot_token;

    // Collect current ability data from the function if available
    $item_info = !empty($item_info) ? $item_info : array('ability_id' => 0, 'ability_token' => 'ability');
    // Load the ability data based on the ID and fallback token
    $item_info = $this->ability_load($item_info['ability_id'], $item_info['ability_token']);

    // Now load the ability data from the session or index
    if (empty($item_info)){
      // Item data could not be loaded
      die('Item data could not be loaded :<br />$item_info = <pre>'.print_r($item_info, true).'</pre>');
      return false;
    }

    // Update the session variable
    $this->update_session();

    // Return true on success
    return true;

  }

  /**
   * Generate an item ID based on the robot owner and the item slot
   * @param int $robot_id
   * @param int $item_slot (optional)
   * @return int
   */
  public static function generate_id($robot_id, $item_slot = 0){
    $ability_id = $robot_id.str_pad(($item_slot + 1), 3, '0', STR_PAD_LEFT);
    return $ability_id;
  }

  // Define a static function for printing out the item's select options markup
  public static function print_editor_options_list_markup($player_item_rewards, $robot_item_rewards, $player_info, $robot_info){

    // Define the global variables
    global $mmrpg_index, $this_current_uri, $this_current_url, $this_database;
    global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
    global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
    global $key_counter, $player_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
    global $mmrpg_database_abilities;
    global $session_token;

    // Require the function file
    $this_options_markup = '';

    // Collect values for potentially missing global variables
    if (!isset($session_token)){ $session_token = rpg_game::session_token(); }

    if (empty($player_info)){ return false; }
    if (empty($robot_info)){ return false; }

    $player_item_options = array();
    $player_items_unlocked = array_keys($player_item_rewards);
    if (!empty($mmrpg_database_abilities)){
      $temp_category = 'special-weapons';
      foreach ($mmrpg_database_abilities AS $item_token => $item_info){
        if ($item_token == 'energy-boost'){ $temp_category = 'support-abilities'; }
        if (!in_array($item_token, $player_items_unlocked)){ continue; }
        $item_info = rpg_item::parse_index_info($item_info);
        $option_markup = rpg_item::print_editor_option_markup($robot_info, $item_info);
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
      $temp_ability_info = rpg_item::parse_index_info($mmrpg_database_abilities[$temp_token]);
      $temp_option_markup = rpg_item::print_editor_option_markup($robot_info, $temp_ability_info);
      if (!empty($temp_option_markup)){ $robot_ability_rewards_options[] = $temp_option_markup; }
    }
    $robot_ability_rewards_options = '<optgroup label="Robot Abilities">'.implode('', $robot_ability_rewards_options).'</optgroup>';
    $this_options_markup .= $robot_ability_rewards_options;
    */

    /*
    $player_item_weapon_options = array();
    $player_item_support_options = array();
    foreach ($player_item_rewards AS $temp_ability_key => $temp_ability_info){
      if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
      $temp_token = $temp_ability_info['ability_token'];
      $temp_ability_info = rpg_item::parse_index_info($mmrpg_database_abilities[$temp_token]);
      $temp_option_markup = rpg_item::print_editor_option_markup($robot_info, $temp_ability_info);

      if (!empty($temp_option_markup)){
        if ($temp_category == 'weapon'){ $player_item_weapon_options[] = $temp_option_markup; }
        elseif ($temp_category == 'support'){ $player_item_support_options[] = $temp_option_markup; }
      }
    }
    $player_item_weapon_options = '<optgroup label="Special Weapons">'.implode('', $player_item_weapon_options).'</optgroup>';
    $player_item_support_options = '<optgroup label="Support Abilities">'.implode('', $player_item_support_options).'</optgroup>';
    $this_options_markup .= $player_item_weapon_options;
    $this_options_markup .= $player_item_support_options;
    */

    // Add an option at the bottom to remove the ability
    $this_options_markup .= '<optgroup label="Ability Actions">';
    $this_options_markup .= '<option value="" title="">- Remove Ability -</option>';
    $this_options_markup .= '</optgroup>';

    // Return the generated select markup
    return $this_options_markup;

  }

  // Define a static function for printing out the item select markup
  public static function print_editor_select_markup($item_rewards_options, $player_info, $robot_info, $item_info, $item_key = 0){

    // Define the global variables
    global $mmrpg_index, $this_current_uri, $this_current_url, $this_database;
    global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
    global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
    global $key_counter, $player_rewards, $player_item_rewards, $player_robot_database, $temp_robot_totals, $player_options_markup, $item_options_markup;
    global $mmrpg_database_abilities;
    global $session_token;

    // Require the function file
    $this_select_markup = '';

    // Collect values for potentially missing global variables
    if (!isset($session_token)){ $session_token = rpg_game::session_token(); }

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
    $item_info_title = rpg_item::print_editor_title_markup($robot_info, $item_info);
    $item_info_title_plain = strip_tags(str_replace('<br />', '//', $item_info_title));
    $item_info_title_tooltip = htmlentities($item_info_title, ENT_QUOTES, 'UTF-8');
    $item_info_title_html = str_replace(' ', '&nbsp;', $item_info_name);
    $item_info_title_html .= '<span class="count">x '.$item_info_count.'</span>';
    $temp_select_options = str_replace('value="'.$item_info_token.'"', 'value="'.$item_info_token.'" selected="selected" disabled="disabled"', $item_rewards_options);
    $item_info_title_html = '<label style="background-image: url(i/a/'.$item_info_token.'/il40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$item_info_title_html.'</label>';
    //if ($global_allow_editing){ $item_info_title_html .= '<select class="ability_name" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'">'.$temp_select_options.'</select>'; }
    $this_select_markup = '<a class="item_name type type_'.$item_info_class_type.'" style="'.(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$item_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-item="'.$item_info_token.'" data-count="'.$item_info_count.'" title="'.$item_info_title_plain.'" data-tooltip="'.$item_info_title_tooltip.'">'.$item_info_title_html.'</a>';

    // Return the generated select markup
    return $this_select_markup;

  }

}
?>