<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'players';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for players and then parse necessary data
define('FORCE_INCLUDE_HIDDEN_PLAYERS', $api_include_hidden);
define('FORCE_INCLUDE_INCOMPLETE_PLAYERS', $api_include_incomplete);
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/players.php');
if (empty($mmrpg_database_players)){ print_error_and_quit('The player database could not be loaded'); }
$player_tokens = !empty($mmrpg_database_players) ? array_keys($mmrpg_database_players) : array();

// Print out the player tokens as JSON so others can use them
if (!empty($player_tokens)){ print_success_and_update_api_cache(array('players' => $player_tokens, 'total' => count($player_tokens))); }
else { print_error_and_quit('The player token array was empty'); }

?>
