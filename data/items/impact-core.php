<?
// ITEM : IMPACT CORE
$item = array(
  'item_name' => 'Impact Core',
  'item_token' => 'impact-core',
  'item_game' => 'MMRPG',
  'item_class' => 'item',
  'item_type' => 'impact',
  'item_description' => 'A mysterious elemental core that radiates with the Impact type energy of a defeated robot master. When used in battle, this item can be thrown at any target to deal Impact type damage of up to {DAMAGE}%!',
  'item_energy' => 0,
  'item_speed' => 10,
  'item_damage' => 10,
  'item_damage_percent' => true,
  'item_accuracy' => 100,
  'item_target' => 'select_target',
  'item_function' => function($objects){
    return rpg_item::item_function_core($objects);
  }
  );
?>