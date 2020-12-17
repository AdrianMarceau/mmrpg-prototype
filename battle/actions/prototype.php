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

// Check if this was an ENDLESS ATTACK MODE mission and we're exiting
if (!empty($this_battle->flags['challenge_battle'])
    && !empty($this_battle->flags['endless_battle'])){

    // We need to clear any savestate data from the waveboard so it doesn't infinitly redirect
    $db->update('mmrpg_challenges_waveboard', array('challenge_wave_savestate' => ''), array('user_id' => $this_user_id));

}

?>