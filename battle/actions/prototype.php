<?

// -- PROTOTYPE BATTLE ACTION -- //

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();

// Redirect the user back to the prototype screen
$this_redirect = 'prototype.php?'.($flag_wap ? 'wap=true' : '');

?>