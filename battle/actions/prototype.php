<?

// -- PROTOTYPE BATTLE ACTION -- //

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();
$_SESSION['SKILLS'] = array();

// Redirect the user back to the prototype screen
$this_redirect = 'prototype.php?'.($flag_wap ? 'wap=true' : '');

// If this battle was completed, check to see if we should remove it from the index
if ($this_battle->battle_status == 'complete'){
    //error_log('Prototype battle '.$this_battle->battle_token.' completed by '.$this_player->player_token, 0);
    //error_log('Battle info = '.print_r($this_battle->battle_info, true), 0);
    if (!empty($this_battle->flags['remove_on_complete'])){
        //error_log('Removing prototype battle '.$this_battle->battle_token.' from index', 0);
        rpg_battle::unset_index_info($this_battle->battle_token);
    }
}

?>