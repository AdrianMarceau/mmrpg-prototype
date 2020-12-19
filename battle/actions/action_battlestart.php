<?

// -- BATTLE START ACTIONS -- //

// Define a quick inline function for checking battle-start actions
function action_battlestart_check_actions($this_player, $target_player, $target_robot){
    $this_robots_active = $this_player->get_robots_active();
    foreach ($this_robots_active AS $key => $active_robot){
        $active_robot->check_skills($target_player, $target_robot, 'battle-start');
        $active_robot->check_items($target_player, $target_robot, 'battle-start');
    }
}

// Loop through both players' robots and apply battle-start checks
action_battlestart_check_actions($this_player, $target_player, $target_robot);
action_battlestart_check_actions($target_player, $this_player, $this_robot);

// Reload both active robots in case anything has changed
$this_robot->robot_reload();
$target_robot->robot_reload();

// Create an empty field to remove any leftover frames
//$this_battle->events_create();

?>