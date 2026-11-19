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

// Just in case this battle is left-over from a larger one, check if its only rescue bots (and end early if true)
if (!empty($target_player->counters['robots_to_rescue'])){
    //error_log('Checking in battlestart if all-rescue-bot and thus battle is over early...');
    //error_log('$target_player->player_token = '.$target_player->player_token);
    //error_log('$target_player->counters[\'robots_active\'] = '.$target_player->counters['robots_active']);
    //error_log('$target_player->counters[\'robots_to_rescue\'] = '.$target_player->counters['robots_to_rescue']);
    if ($target_player->counters['robots_active'] <= $target_player->counters['robots_to_rescue']){
        //error_log('All of '.$target_player->player_name.'\'s remaining robots are rescue units!');
        // Trigger the battle complete action to update status and result
        $this_battle->battle_complete_trigger($this_player, $this_robot, $target_player, $target_robot, '', '');
    }
}

?>