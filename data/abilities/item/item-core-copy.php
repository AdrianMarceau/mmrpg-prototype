<?
// ITEM : COPY CORE
$ability = array(
  'ability_name' => 'Copy Core',
  'ability_token' => 'item-core-copy',
  'ability_game' => 'MMRPG',
  'ability_class' => 'item',
  'ability_type' => 'copy',
  'ability_description' => 'A mysterious elemental core that radiates with the Copy type energy of a defeated robot master. When used in battle, this item increases the field multiplier for Copy type moves by {RECOVERY}%. This item can also be used to return a Copy Core robot to its base form.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_recovery' => 100,
  'ability_recovery_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    return mmrpg_ability::item_function_core($objects);
  }
  );
?>