<?
// ATTACHMENT : DEFEAT
$ability = array(
  'ability_name' => 'Defeat Explosion',
  'ability_token' => 'attachment-defeat',
  'ability_class' => 'system',
  'ability_function' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);

    // Return true on success
    return true;
        
  },
  'ability_frame' => 0,
  'ability_frame_animate' => array(0,4,1,5,2,6,3,7,4,8,5,9),
  'ability_frame_offset' => array('x' => 0, 'y' => -10, 'z' => -10)
  );
?>