<?

// TYPES DATABASE

// Define the index of types for the game
$mmrpg_database_types = $mmrpg_index['types'];
$temp_remove_types = array('attack', 'defense', 'speed', 'energy', 'weapons', 'empty', 'light', 'wily', 'cossack', 'damage', 'recovery', 'experience', 'level');
foreach ($temp_remove_types AS $token){ unset($mmrpg_database_types[$token]); }
ksort($mmrpg_database_types);
$mmrpg_database_types['none'] = array();
$mmrpg_database_types_count = count($mmrpg_database_types);
$mmrpg_database_types_count_added = 1;
$mmrpg_database_types_count_actual = count($mmrpg_index['types']);
//die('$mmrpg_database_abilities_types = <pre>'.print_r($mmrpg_database_abilities_types, true).'</pre>');

?>