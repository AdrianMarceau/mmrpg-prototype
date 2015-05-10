<?
// ITEM : NEUTRAL CORE
$ability = array(
  'ability_name' => 'Neutral Core',
  'ability_token' => 'item-core-none',
  'ability_game' => 'MMRPG',
  'ability_class' => 'item',
  'ability_type' => '',
  'ability_description' => 'A mysterious elemental core that radiates with the energy of a defeated robot master. When used in battle, this item has no effect.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 220, 20, 99,
        $this_player->print_player_name().' uses an item from the inventory&hellip; <br />'.
        $this_robot->print_robot_name().' releases the '.$this_ability->print_ability_name().'&#39;s energy!'
        )
      ));
    $this_robot->trigger_target($this_robot, $this_ability);
    
    // Target this robot's self and show the ability failing
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(9, 0, 0, -10,
        'But nothing happened&hellip;<br />'
        )
      ));
    $this_robot->trigger_target($this_robot, $this_ability);
    
    // Return true on success (well, failure, but whatever)
    return true;
      
  }
  );
?>