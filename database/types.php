<?php
// TYPES DATABASE

// Define the index of types for the game
$mmrpg_database_types = rpg_type::get_index(true);
uasort($mmrpg_database_types, function($t1, $t2){
  if ($t1['type_order'] > $t2['type_order']){ return 1; }
  elseif ($t1['type_order'] < $t2['type_order']){ return -1; }
  else { return 0; }
});

// Extract and separate all the special types from the list
$mmrpg_database_types_hidden = array();
foreach ($mmrpg_database_types AS $token => $info){
    if ($info['type_class'] != 'normal' && $info['type_token'] != 'none'){
        $mmrpg_database_types_hidden[$token] = $info;
        unset($mmrpg_database_types[$token]);
    }
}

// Count the number of normal, public types to display
$mmrpg_database_types_count = count($mmrpg_database_types);
$mmrpg_database_types_count_added = 1;
$mmrpg_database_types_count_actual = count($mmrpg_database_types);

// Count the number of hidden special types to not-display
$mmrpg_database_types_hidden_count = count($mmrpg_database_types);
$mmrpg_database_types_hidden_count_added = 1;
$mmrpg_database_types_hidden_count_actual = count($mmrpg_database_types);

?>