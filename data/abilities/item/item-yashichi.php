<?
// ITEM : YASHICHI
$ability = array(
  'ability_name' => 'Yashichi',
  'ability_token' => 'item-yashichi',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/Energy',
  'ability_class' => 'item',
  'ability_subclass' => 'consumable',
  'ability_type' => 'energy',
  'ability_type2' => 'weapons',
  'ability_description' => 'A strange pinwheel-shaped generator that restore {RECOVERY}% life and weapon energy to one robot on the user\'s side of the field.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_recovery' => 100,
  'ability_recovery_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'select_this',
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    /*
    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$target_robot->robot_id;
    $this_attachment_info = array(
    	'class' => 'ability',
    	'ability_token' => $this_ability->ability_token,
    	'attachment_duration' => 0,
      'attachment_damage_breaker' => 0.0,
      'attachment_switch_disabled' => true,
      'ability_frame' => 9,
      'ability_frame_animate' => array(9),
      'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 0),
    	'attachment_destroy' => array(
        'trigger' => 'special',
        'kind' => '',
        'type' => '',
        'type2' => '',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'taunt',
        'rates' => array(100, 0, 0),
        'success' => array(9, 0, -9999, 0,  'The '.$this_ability->print_ability_name().'&#39;s power faded away&hellip;<br /> '.$target_robot->print_robot_name().' is no longer protected!'),
        'failure' => array(9, 0, -9999, 0, 'The '.$this_ability->print_ability_name().'&#39;s power faded away&hellip;<br /> '.$target_robot->print_robot_name().' is no longer protected!')
        ),
      );
    */

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(0, 40, -2, 99,
        $this_player->print_player_name().' uses an item from the inventory&hellip; <br />'.
        $target_robot->print_robot_name().' is given the '.$this_ability->print_ability_name().'!'
        )
      ));
    $target_robot->trigger_target($target_robot, $this_ability);

    /*
    // Attach this ability attachment to the robot using it
    $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
    $target_robot->update_session();
    */

    // Increase this robot's life energy stat
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'percent' => true,
      'modifiers' => false,
      'frame' => 'taunt',
      'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s life energy was fully restored!'),
      'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s life energy was not affected&hellip;')
      ));
    $energy_recovery_amount = ceil($target_robot->robot_base_energy * ($this_ability->ability_recovery / 100));
    $target_robot->trigger_recovery($target_robot, $this_ability, $energy_recovery_amount);

    // Increase this robot's weapon energy stat
    $this_ability->recovery_options_update(array(
      'kind' => 'weapons',
      'percent' => true,
      'frame' => 'taunt',
      'success' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s weapon energy was fully restored!'),
      'failure' => array(9, 0, 0, -9999, $target_robot->print_robot_name().'&#39;s weapon energy was not affected&hellip;')
      ));
    $weapons_recovery_amount = ceil($target_robot->robot_base_weapons * ($this_ability->ability_recovery / 100));
    $target_robot->trigger_recovery($target_robot, $this_ability, $weapons_recovery_amount);

    /*
    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'taunt',
      'success' => array(9, 0, 0, -9999,
        $target_robot->print_robot_name().'&#39;s power levels are in overdrive!<br /> '.
        $target_robot->print_robot_name().' is protected from all damage!'
        )
      ));
    $target_robot->trigger_target($target_robot, $this_ability);
    */

    // Return true on success
    return true;

  }
  );
?>