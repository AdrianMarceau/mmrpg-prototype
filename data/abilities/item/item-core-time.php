<?
// ITEM : TIME CORE
$ability = array(
  'ability_name' => 'Time Core',
  'ability_token' => 'item-core-time',
  'ability_game' => 'MMRPG',
  'ability_class' => 'item',
  'ability_type' => 'time',
  'ability_description' => 'A mysterious elemental core that radiates with the Time type energy of a defeated robot master. When used in battle, this item can be thrown at any target to deal Time type damage of up to {DAMAGE}%!',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_damage' => 10,
  'ability_damage_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'select_target',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_core($objects);
  }
  );
?>