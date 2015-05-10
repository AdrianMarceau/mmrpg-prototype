<?
// Define a class for the abilities
class mmrpg_item extends mmrpg_ability {

  function __construct() {

  }

  // Define a static function for printing out the item's select options markup
  public static function print_editor_options_list_markup($player_item_rewards, $robot_item_rewards, $player_info, $robot_info){
    // Require the function file
    $this_options_markup = '';
    require(MMRPG_CONFIG_ROOTDIR.'data/classes/item_editor-options-list-markup.php');
    // Return the generated select markup
    return $this_options_markup;
  }

  // Define a static function for printing out the item select markup
  public static function print_editor_select_markup($item_rewards_options, $player_info, $robot_info, $item_info, $item_key = 0){
    // Require the function file
    $this_select_markup = '';
    require(MMRPG_CONFIG_ROOTDIR.'data/classes/item_editor-select-markup.php');
    // Return the generated select markup
    return $this_select_markup;
  }

}
?>