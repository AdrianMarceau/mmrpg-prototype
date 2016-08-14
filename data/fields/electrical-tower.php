<?
// ELECTRICAL TOWER
$field = array(
  'field_name' => 'Electrical Tower',
  'field_token' => 'electrical-tower',
  'field_type' => 'electric',
  'field_game' => 'MM01',
  'field_number' => 'DLN-008',
  'field_multipliers' => array('electric' => 2.0, 'wind' => 0.5, 'impact' => 1.1),
  'field_description' => 'Elec Man\'s Favourite Field',
  'field_background' => 'electrical-tower',
  'field_foreground' => 'electrical-tower',
  'field_music' => 'electrical-tower',
  'field_music_name' => 'Elec Man (Sega Genesis)',
  'field_music_link' => 'http://youtu.be/TgzTXna40VM',
  'field_master' => 'elec-man',
  'field_mechas' => array('spine'),
  'field_background_frame' => array(0), //array(0,1,2,3),
  'field_foreground_frame' => array(0),
  'field_background_attachments' => array(
    'mecha-01' => array('class' => 'robot', 'size' => 40, 'offset_x' => 725, 'offset_y' => 192, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left'),
    'mecha-02' => array('class' => 'robot', 'size' => 40, 'offset_x' => 206, 'offset_y' => 160, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left'),
    'mecha-03' => array('class' => 'robot', 'size' => 40, 'offset_x' => 1, 'offset_y' => 170, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left')
    ),
  'field_foreground_attachments' => array(
    'mecha-04' => array('class' => 'robot', 'size' => 80, 'offset_x' => -100, 'offset_y' => 0, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'right'),
    'mecha-05' => array('class' => 'robot', 'size' => 80, 'offset_x' => -100, 'offset_y' => 0, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left')
    )
  );
?>