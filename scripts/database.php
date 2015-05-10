<?
// Require the application top file
require_once('../top.php');

// Collect the request variables for this database query
$this_class = !empty($_REQUEST['class']) ? $_REQUEST['class'] : false;
$this_token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : false;

// If either are empty, kill the script
if (empty($this_class)){ die('error : no class defined'); }
elseif (empty($this_token)){ die('error : no token defined'); }

// Require the database top include file
if ($this_class == 'mechas' || $this_class == 'fields'){ define('DATA_DATABASE_SHOW_MECHAS', true); }
if ($this_class == 'bosses' || $this_class == 'fields'){ define('DATA_DATABASE_SHOW_BOSSES', true); }
require_once('../data/database.php');

// Proceed based on the type of class the request is
switch ($this_class){
  // If this was a player request
  case 'players': {
    $key_counter = array_search($this_token, array_keys($mmrpg_database_players));
    $temp_player_info = $mmrpg_database_players[$this_token];
    $temp_player_markup = mmrpg_player::print_database_markup($temp_player_info, array('show_key' => $key_counter));
    $temp_player_markup = preg_replace('/\s+/', ' ', $temp_player_markup);
    echo 'success : '.$temp_player_markup;
    break;
  }
  // If this was a robot request
  case 'robots': {
    $key_counter = array_search($this_token, array_keys($mmrpg_database_robots));
    $temp_robot_info = $mmrpg_database_robots[$this_token];
    $temp_robot_markup = mmrpg_robot::print_database_markup($temp_robot_info, array('show_key' => $key_counter));
    $temp_robot_markup = preg_replace('/\s+/', ' ', $temp_robot_markup);
    echo 'success : '.$temp_robot_markup;
    break;
  }
  // If this was a mecha request
  case 'mechas': {
    $key_counter = array_search($this_token, array_keys($mmrpg_database_mechas));
    $temp_mecha_info = $mmrpg_database_mechas[$this_token];
    $temp_mecha_markup = mmrpg_robot::print_database_markup($temp_mecha_info, array('show_key' => $key_counter));
    $temp_mecha_markup = preg_replace('/\s+/', ' ', $temp_mecha_markup);
    echo 'success : '.$temp_mecha_markup;
    break;
  }
  // If this was a ability request
  case 'abilities': {
    $key_counter = array_search($this_token, array_keys($mmrpg_database_abilities));
    $temp_ability_info = $mmrpg_database_abilities[$this_token];
    $temp_ability_markup = mmrpg_ability::print_database_markup($temp_ability_info, array('show_key' => $key_counter));
    $temp_ability_markup = preg_replace('/\s+/', ' ', $temp_ability_markup);
    echo 'success : '.$temp_ability_markup;
    break;
  }
  // If this was a field request
  case 'fields': {
    $key_counter = array_search($this_token, array_keys($mmrpg_database_fields));
    $temp_field_info = $mmrpg_database_fields[$this_token];
    $temp_field_markup = mmrpg_field::print_database_markup($temp_field_info, array('show_key' => $key_counter));
    $temp_field_markup = preg_replace('/\s+/', ' ', $temp_field_markup);
    echo 'success : '.$temp_field_markup;
    break;
  }
  // If this was a item request
  case 'items': {
    $key_counter = array_search($this_token, array_keys($mmrpg_database_items));
    $temp_item_info = $mmrpg_database_items[$this_token];
    $temp_item_markup = mmrpg_ability::print_database_markup($temp_item_info, array('show_key' => $key_counter));
    $temp_item_markup = preg_replace('/\s+/', ' ', $temp_item_markup);
    echo 'success : '.$temp_item_markup;
    break;
  }
  // If this was a type request
  case 'types': {
    $temp_type_info = $mmrpg_database_types[$this_token];
    $temp_type_markup = mmrpg_type::print_database_markup($temp_type_info);
    $temp_type_markup = preg_replace('/\s+/', ' ', $temp_type_markup);
    echo 'success : '.$temp_type_markup;
    break;
  }
  // If this was an invalid request
  default: {
    die('error : invalid class requested');
    break;
  }
}

// Exit the script gracefully if it gets this far
exit();

?>