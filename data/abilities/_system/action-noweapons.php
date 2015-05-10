<?
// ACTION : NO WEAPONS
$ability = array(
  'ability_name' => 'Recharging',
  'ability_token' => 'action-noweapons',
  'ability_class' => 'system',
  'ability_description' => 'Critically low on weapon energy and unable to use an ability, the active robot waits to recharge...',
  'ability_energy' => 0,
  'ability_damage' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    /*
    // Target this robot's self and show the ability triggering
    $temp_females = array('roll', 'disco', 'rhythm', 'splash-woman');
    $temp_undefined = array('met');
    $temp_pronoun = (in_array($this_robot->robot_token, $temp_females) ? 'her' : (in_array($this_robot->robot_token, $temp_undefined) ? 'its' : 'his'));
    $temp_energy_percent = ceil($this_robot->robot_weapons / $this_robot->robot_base_weapons);
    $this_ability->target_options_update(array(
      'frame' => 'defend',
      'success' => array(9, 0, 0, -10,
        $this_robot->print_robot_name().' has does not have enough weapon energy to use abilities&hellip;<br />'.
        $this_robot->print_robot_name().' waits for '.$temp_pronoun.' power to recharge.'
        )
      ));
    $this_robot->trigger_target($this_robot, $this_ability);
    */
    
    // Return true on success
    return true;
      
    }
  );
?>