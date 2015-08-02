<?
// TYPES DATABASE

// Define the index of types for the game
$mmrpg_database_types = $mmrpg_index['types'];
$temp_remove_types = array('attack', 'defense', 'speed', 'energy', 'weapons', 'empty', 'light', 'wily', 'cossack', 'damage', 'recovery', 'experience', 'level');
foreach ($temp_remove_types AS $token){ unset($mmrpg_database_types[$token]); }
uasort($mmrpg_database_types, function($t1, $t2){
  if ($t1['type_order'] > $t2['type_order']){ return 1; }
  elseif ($t1['type_order'] < $t2['type_order']){ return -1; }
  else { return 0; }
});
$mmrpg_database_types_count = count($mmrpg_database_types);
$mmrpg_database_types_count_added = 1;
$mmrpg_database_types_count_actual = count($mmrpg_index['types']);

?>