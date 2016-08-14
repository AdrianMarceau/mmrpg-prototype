<?
// PIPE STATION
$field = array(
  'field_name' => 'Pipe Station',
  'field_token' => 'pipe-station',
  'field_type' => 'explode',
  'field_game' => 'MM02',
  'field_number' => 'DLN-006',
  'field_multipliers' => array('explode' => 2.0, 'missile' => 0.7, 'flame' => 0.6),
  'field_description' => 'Crash Man\'s Favourite Field',
  'field_background' => 'pipe-station',
  'field_foreground' => 'pipe-station',
  'field_music' => 'pipe-station',
  'field_music_name' => 'Crash Man (Sega Genesis)',
  'field_music_link' => 'http://youtu.be/zVgdFg9jQtQ',
  'field_master' => 'crash-man',
  'field_mechas' => array('killer-bullet'),
  'field_background_frame' => array(0), //array(0,1),
  'field_foreground_frame' => array(0),
  'field_background_attachments' => array(
    'mecha-01' => array('class' => 'robot', 'size' => 40, 'offset_x' => 76, 'offset_y' => 139, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'right'),
    'mecha-02' => array('class' => 'robot', 'size' => 40, 'offset_x' => 298, 'offset_y' => 170, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left'),
    'mecha-03' => array('class' => 'robot', 'size' => 40, 'offset_x' => 47, 'offset_y' => 202, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left'),
    'mecha-04' => array('class' => 'robot', 'size' => 40, 'offset_x' => 166, 'offset_y' => 188, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left'),
    'mecha-05' => array('class' => 'robot', 'size' => 40, 'offset_x' => 479, 'offset_y' => 203, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left')
    ),
  'field_foreground_attachments' => array(
    'mecha-06' => array('class' => 'robot', 'size' => 80, 'offset_x' => -100, 'offset_y' => 0, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'right'),
    'mecha-07' => array('class' => 'robot', 'size' => 80, 'offset_x' => -100, 'offset_y' => 0, 'robot_token' => 'met', 'robot_frame' => array(0), 'robot_direction' => 'left')
    )
  );
?>