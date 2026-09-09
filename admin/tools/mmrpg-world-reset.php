<?

// Require the global top file
require('../../top.php');

// Print out the title regardless of if confirmed or not
echo('<strong>MMRPG World Reset Script</strong><br />'.PHP_EOL);

// Make sure this request is confirmed before proceeding
$request_confirmed = !empty($_GET['confirm']) && $_GET['confirm'] === 'true' ? true : false;
if (!$request_confirmed){
    $confirm_link = '/admin/tools/mmrpg-world-reset.php?confirm=true';
    echo('<p>Are you 100% sure you want to use this script?</p>'.PHP_EOL);
    echo('<p>Doing so will reset all encounters and all pickups for all areas in your game.</p>'.PHP_EOL);
    echo('<p>Are you REALLY sure?</p>'.PHP_EOL);
    echo('<a href="'.$confirm_link.'">Yes, I\'m Sure! Reset My World!!</a>'.PHP_EOL);
    exit();
}

// Collect the session keys we'll be working with
$game_session_key = rpg_game::session_token();
$world_session_key = rpg_world::session_token();

// Now create reference variables for the sessions
$GAME_SESSION = &$_SESSION[$game_session_key];
$WORLD_SESSION = &$_SESSION[$world_session_key];

// Pull the player's battle index if it exists
if (!isset($GAME_SESSION['values']['battle_index'])){ die('Error! A battle_index array doesn\'t exist!'); }
$BATTLE_INDEX = &$GAME_SESSION['values']['battle_index'];

// Collect the encounter and pickup arrays if they exist
if (!isset($WORLD_SESSION['world_encounters'])){ die('Error! A world_encounters array doesn\'t exist!'); }
if (!isset($WORLD_SESSION['world_pickups'])){ die('Error! A world_pickups array doesn\'t exist!'); }
$WORLD_ENCOUNTERS = &$WORLD_SESSION['world_encounters'];
$WORLD_PICKUPS = &$WORLD_SESSION['world_pickups'];

echo('<pre>'.PHP_EOL);
{

    // Now loop through encounters and clear them from both parent array and battle index
    foreach ($WORLD_ENCOUNTERS AS $world_map_token => $area_encounters){
        foreach ($area_encounters AS $key => $encounter_info){
            list($kind, $token, $alt, $position, $battle, $label) = $encounter_info;
            unset($BATTLE_INDEX[$battle]);
        }
        $WORLD_ENCOUNTERS[$world_map_token] = array();
    }

    // Now loop through and clear all the pickup arrays for each area
    foreach ($WORLD_PICKUPS AS $world_map_token => $area_pickups){
        $WORLD_PICKUPS[$world_map_token] = array();
    }

    // Clear any other world-battle stragglers from the battle index if they exist
    foreach ($BATTLE_INDEX AS $battle_token => $battle_info){
        if (strpos($battle_token, 'world-battle_') === 0){
            unset($BATTLE_INDEX[$battle_token]);
        }
    }

}
echo('</pre>'.PHP_EOL);


exit('...Done!');

?>