<?

// -- RESTART BATTLE ACTION -- //

// Recollect the user id, player id, etc.
$restart_user_id = $this_player->user_id;
$restart_player_id = $battle_players_index[$this_player->player_token]['player_id'];
$restart_player_token = $this_player->player_token;

// Collect the battle token in case we need to change it
$restart_battle_token = $this_battle->battle_token;

// Redefine the player's robots string
$this_player_robots = !empty($_REQUEST['this_player_robots']) ? $_REQUEST['this_player_robots'] : '00_robot';

// If rotation was requested, we should break apart the csv player robots and rotate positions first
if ($this_action == 'restart_with-rotate'){
    $list_robots = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
    $first_robot = array_shift($list_robots);
    $list_robots[] = $first_robot;
    $this_player_robots = implode(',', $list_robots);
}

// If we're restarting the entire mission, check to see what token we should use
if ($this_action == 'restart_whole-mission'){
    $battle_index = !empty($_SESSION['GAME']['values']['battle_index']) ? $_SESSION['GAME']['values']['battle_index'] : array();
    $alpha_battle_token = $this_battle->battle_token.'-alpha';
    if (isset($battle_index[$alpha_battle_token])){
        $restart_battle_token = $alpha_battle_token;
    }
}

// Redirect the user back to the prototype screen
$this_redirect = 'battle.php?'.
    ($flag_wap ? 'wap=true' : 'wap=false').
    '&this_user_id='.$restart_user_id.
    '&this_player_id='.$restart_player_id.
    '&this_player_token='.$restart_player_token.
    //'&this_battle_id='.$this_battle->battle_id.
    '&this_battle_token='.$restart_battle_token.
    '&this_player_robots='.$this_player_robots.
    //'&this_field_id='.$this_field->field_id.
    //'&this_field_token='.$this_field->field_token.
    //'&target_player_id='.$target_player->player_id.
    //'&target_player_token='.$target_player->player_token.
    (!empty($_SESSION['BATTLES_CHAIN'][$this_battle->battle_token]) ? '&flag_skip_fadein=true' : '').
    '';

?>