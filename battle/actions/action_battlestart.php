<?

// -- BATTLE START ACTIONS -- //

// Define a quick inline function for checking battle-start actions
function action_battlestart_check_actions($this_player, $target_player, $target_robot){
    $this_robots_active = $this_player->get_robots_active();
    foreach ($this_robots_active AS $key => $active_robot){
        $temp_battlestart_function = $active_robot->robot_function_onbattlestart;
        $temp_result = $temp_battlestart_function(array(
            'this_battle' => $active_robot->player->battle,
            'this_field' => $active_robot->player->battle->battle_field,
            'this_player' => $active_robot->player,
            'this_robot' => $active_robot,
            'target_player' => $target_player,
            'target_robot' => $target_robot
            ));
        $active_robot->check_skills($target_player, $target_robot, 'battle-start');
        $active_robot->check_items($target_player, $target_robot, 'battle-start');
    }
}

// Loop through both players' robots and apply battle-start checks
action_battlestart_check_actions($this_player, $target_player, $target_robot);
action_battlestart_check_actions($target_player, $this_player, $this_robot);

// Reload both active robots in case anything has changed
$this_robot = rpg_game::get_robot_by_id($this_robot->robot_id);
$target_robot = rpg_game::get_robot_by_id($target_robot->robot_id);

// Create an empty field to remove any leftover frames
//$this_battle->events_create();

?>