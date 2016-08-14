<?
// STEEL MILL
$field = array(
  'field_name' => 'Steel Mill',
  'field_token' => 'steel-mill',
  'field_type' => 'flame',
  'field_game' => 'MM01',
  'field_number' => 'DLN-007',
  'field_multipliers' => array('flame' => 2.0, 'water' => 0.6, 'nature' => 0.4),
  'field_description' => 'Fire Man\'s Favourite Field',
  'field_background' => 'steel-mill',
  'field_foreground' => 'steel-mill',
  'field_music' => 'steel-mill',
  'field_music_name' => 'Fire Man (Sega Genesis)',
  'field_music_link' => 'http://youtu.be/_ooiqUD6UFM',
  'field_master' => 'fire-man',
  'field_mechas' => array('tackle-fire'),
  'field_background_frame' => array(0), //array(0,1,2,3),
  'field_foreground_frame' => array(0),
  'field_background_attachments' => array(
    'object-01A' => array('class' => 'object', 'size' => 40, 'offset_x' => 182, 'offset_y' => 210, 'object_token' => 'fire-bolt', 'object_frame' => array(9,0,2,0,9,2,0,9,9,9), 'object_direction' => 'left'),
    'object-01B' => array('class' => 'object', 'size' => 40, 'offset_x' => 222, 'offset_y' => 210, 'object_token' => 'fire-bolt', 'object_frame' => array(9,1,3,1,9,3,1,9,9,9), 'object_direction' => 'left'),
    'object-02A' => array('class' => 'object', 'size' => 40, 'offset_x' => 282, 'offset_y' => 106, 'object_token' => 'fire-bolt', 'object_frame' => array(9,9,9,9,6,4,9,9,6,4), 'object_direction' => 'right'),
    'object-02B' => array('class' => 'object', 'size' => 40, 'offset_x' => 282, 'offset_y' => 146, 'object_token' => 'fire-bolt', 'object_frame' => array(9,9,9,9,7,5,9,9,7,5), 'object_direction' => 'right'),
    'mecha-01' => array('class' => 'robot', 'size' => 40, 'offset_x' => 378, 'offset_y' => 169, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'right'),
    'mecha-02' => array('class' => 'robot', 'size' => 40, 'offset_x' => 83, 'offset_y' => 138, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'right'),
    'mecha-03' => array('class' => 'robot', 'size' => 40, 'offset_x' => 54, 'offset_y' => 222, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left')
    ),
  'field_foreground_attachments' => array(
    'mecha-04' => array('class' => 'robot', 'size' => 80, 'offset_x' => -100, 'offset_y' => 0, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'right'),
    'mecha-05' => array('class' => 'robot', 'size' => 80, 'offset_x' => -100, 'offset_y' => 0, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left')
    )
  );
?>