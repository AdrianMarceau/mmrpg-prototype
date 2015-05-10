<?
// RHYTHM SHUFFLE
$ability = array(
  'ability_name' => 'Rhythm Shuffle',
  'ability_token' => 'rhythm-shuffle',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MM00/Weapons/Rhythm',
  'ability_description' => 'The user triggers an exploit in the battle system that glitches out and shuffles the positions of all robots on both sides of the field!',
  'ability_energy' => 16,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'summon',
      'success' => array(9, 0, 0, -10, $this_robot->print_robot_name().' uses '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Collect an array of all active robots on this side of the field
    $temp_active_robots = $this_player->player_robots;
    shuffle($temp_active_robots);
    foreach ($temp_active_robots AS $key => $info){
      if ($info['robot_id'] == $this_robot->robot_id){
        $this_robot->robot_key = $key;
        $this_robot->update_session();
      } else {
        $temp_this_robot = new mmrpg_robot($this_battle, $this_player, $info);
        $temp_this_robot->robot_key = $key;
        $temp_this_robot->update_session();
      }
    }

    // Collect an array of all active robots on this side of the field
    $temp_active_robots = $target_player->player_robots;
    shuffle($temp_active_robots);
    foreach ($temp_active_robots AS $key => $info){
      if ($info['robot_id'] == $target_robot->robot_id){
        $target_robot->robot_key = $key;
        $target_robot->update_session();
      } else {
        $temp_target_robot = new mmrpg_robot($this_battle, $target_player, $info);
        $temp_target_robot->robot_key = $key;
        $temp_target_robot->update_session();
      }
    }

    // Target this robot's self
    $this_ability->target_options_update(array(
      'frame' => 'defend',
      'success' => array(9, 0, 0, -10, 'The positions of all robots on the field have been shuffled!')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Return true on success
    return true;

  }
  );
?>