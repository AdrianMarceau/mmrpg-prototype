<?

// -- PROTOTYPE WORLD ACTION -- //

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();
$_SESSION['SKILLS'] = array();

// Redirect the user back to the prototype screen
$this_redirect = 'world.php?'.($flag_wap ? 'wap=true' : '');
if ($this_player && $this_player->player_token !== 'player'){
    $this_redirect .= '&player='.$this_player->player_token;
}

?>