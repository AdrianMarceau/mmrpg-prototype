<?
// ITEM : DEFENSE PELLET
$ability = array(
  'ability_name' => 'Defense Pellet',
  'ability_token' => 'item-defense-pellet',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Items/Defense',
  'ability_class' => 'item',
  'ability_type' => 'defense',
  'ability_description' => 'A small shield pellet that boosts the defense stat of one robot on the user\'s side of the field by {RECOVERY}%. This item\'s effects appear to be permanent, though only up until the target has reached its max stat limit.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_recovery' => 10,
  'ability_recovery_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'select_this',
  'ability_function' => function($objects){

      // Call the global stat booster item function
      return rpg_ability::item_function_stat_booster($objects);

    }
  );
?>