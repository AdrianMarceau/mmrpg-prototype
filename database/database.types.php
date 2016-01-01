<?php
// TYPES DATABASE

// Define the index of types for the game
$mmrpg_database_types = rpg_type::get_index();
uasort($mmrpg_database_types, function($t1, $t2){
  if ($t1['type_order'] > $t2['type_order']){ return 1; }
  elseif ($t1['type_order'] < $t2['type_order']){ return -1; }
  else { return 0; }
});
$mmrpg_database_types_count = count($mmrpg_database_types);
$mmrpg_database_types_count_added = 1;
$mmrpg_database_types_count_actual = count($mmrpg_database_types);

?>