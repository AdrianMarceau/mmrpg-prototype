<?
// ABILITY
$ability = array(
  'ability_name' => 'Ability',
  'ability_token' => 'ability',
  'ability_image' => 'ability',
  'ability_class' => 'system',
  'ability_description' => 'The default ability object.',
  'ability_type' => '',
  'ability_damage' => 0,
  'ability_accuracy' => 0,
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Generate an event to show nothing happened
    $event_header = $this_robot->robot_name.'&#39;s '.$this_ability->ability_name;
    $event_body = 'Nothing happened&hellip;';
    $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, array('this_ability' => $this_ability));
    
    // Return true on success
    return true;
      
  }
  );
?>