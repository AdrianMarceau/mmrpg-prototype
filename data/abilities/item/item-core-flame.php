<?
// ITEM : FLAME CORE
$ability = array(
  'ability_name' => 'Flame Core',
  'ability_token' => 'item-core-flame',
  'ability_game' => 'MMRPG',
  'ability_class' => 'item',
  'ability_type' => 'flame',
  'ability_description' => 'A mysterious elemental core that radiates with the Flame type energy of a defeated robot master. When used in battle, this item increases the field multiplier for Flame type moves by {RECOVERY}%. This item can also be used to change a Copy Core robot into a Flame Core form.',
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