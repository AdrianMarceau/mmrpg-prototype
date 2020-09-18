<?

// -- RESTART BATTLE ACTION -- //

// Recollect the user id, player id, etc.
$restart_user_id = $this_player->user_id;
$restart_player_id = $battle_players_index[$this_player->player_token]['player_id'];
$restart_player_token = $this_player->player_token;

// Redefine the player's robots string
$this_player_robots = !empty($_REQUEST['this_player_robots']) ? $_REQUEST['this_player_robots'] : '00_robot';

// Redirect the user back to the prototype screen
$this_redirect = 'battle.php?'.
    ($flag_wap ? 'wap=true' : 'wap=false').
    '&this_user_id='.$restart_user_id.
    '&this_player_id='.$restart_player_id.
    '&this_player_token='.$restart_player_token.
    //'&this_battle_id='.$this_battle->battle_id.
    '&this_battle_token='.$this_battle->battle_token.
    '&this_player_robots='.$this_player_robots.
    //'&this_field_id='.$this_field->field_id.
    //'&this_field_token='.$this_field->field_token.
    //'&target_player_id='.$target_player->player_id.
    //'&target_player_token='.$target_player->player_token.
    (!empty($_SESSION['BATTLES_CHAIN']) ? '&flag_skip_fadein=true' : '').
    '';

?>