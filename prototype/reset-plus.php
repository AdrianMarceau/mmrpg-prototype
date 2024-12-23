<?php

// RESET PROTOTYPE (NEW GAME PLUS)

// Collect a reference to the user object
$this_user = $_SESSION[$session_token]['USER'];
$user_id = rpg_user::get_current_userid();

// Make sure we add the new-game-plus flag to each of the unlocked players
$mmrpg_index_players = rpg_player::get_index(true);
$game_session = $_SESSION[$session_token];
if (!isset($game_session['flags'])){ $game_session['flags'] = array(); }
if (!isset($game_session['flags']['prototype_events'])){ $game_session['flags']['prototype_events'] = array(); }
$game_session['flags']['prototype_events']['new_game_plus'] = 1;
$_SESSION[$session_token] = $game_session;

// Create the reset object we'll use to modify save data
require(MMRPG_CONFIG_ROOTDIR.'classes/rpg_reset.php');
$reset = new rpg_reset($user_id, $session_token);

// First we'll reset the missions in this save file
$reset->reset_missions();

// Then we'll reset any event flags in this save file
$reset->reset_events();

// Lastly, we'll regroup their robots to original owners
$reset->regroup_robots();

// Lastly, we'll reboot all their robots to level 1
//$reset->reset_robots();

// Now that we're done, re-inset the user data into the session
$_SESSION[$session_token]['USER'] = $this_user;

// Save the game given what's in the session now
mmrpg_save_game_session();

// And then we can exit w/ a success code
//header('Location: prototype.php');
unset($db);
exit('success');

?>
