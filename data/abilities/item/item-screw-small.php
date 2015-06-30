<?
// ITEM : SMALL SCREW
$ability = array(
  'ability_name' => 'Small Screw',
  'ability_token' => 'item-screw-small',
  'ability_game' => 'MM07',
  'ability_group' => 'MM00/Items/Screws',
  'ability_class' => 'item',
  'ability_subclass' => 'treasure',
  'ability_description' => 'A small metal screw dropped by a defeated mecha.  This item is loved by a certain character and can be traded in for a moderate amount of Zenny. ',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 40, -2, 99,
        $this_player->print_player_name().' uses an item from the inventory&hellip; <br />'.
        $target_robot->print_robot_name().' is given the '.$this_ability->print_ability_name().'!'
        )
      ));
    $target_robot->trigger_target($target_robot, $this_ability);

    // Target this robot's self and show the ability failing
    $this_ability->target_options_update(array(
      'frame' => 'defend',
      'success' => array(9, 0, 0, -10,
        'Nothing happened&hellip;<br />'
        )
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Return true on success
    return true;

  }
  );
?>