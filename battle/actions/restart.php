<?

// -- RESTART BATTLE ACTION -- //

// Define the player's robots string
$this_player_robots = array();
if (!empty($this_player->player_robots)){
    foreach ($this_player->player_robots AS $key => $temp_robotinfo){
        $this_player_robots[] = $temp_robotinfo['robot_id'].'_'.$temp_robotinfo['robot_token'];
    }
}
$this_player_robots = implode(',', $this_player_robots);

// Redirect the user back to the prototype screen
$this_redirect = 'battle.php?'.
    ($flag_wap ? 'wap=true' : 'wap=false').
    '&this_battle_id='.$this_battle->battle_id.
    '&this_battle_token='.$this_battle->battle_token.
    //'&this_field_id='.$this_field->field_id.
    //'&this_field_token='.$this_field->field_token.
    '&this_player_id='.$this_player->player_id.
    '&this_player_token='.$this_player->player_token.
    '&this_player_robots='.$this_player_robots.
    //'&target_player_id='.$target_player->player_id.
    //'&target_player_token='.$target_player->player_token.
    (!empty($_SESSION['BATTLES_CHAIN']) ? '&flag_skip_fadein=true' : '').
    '';

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();



?>