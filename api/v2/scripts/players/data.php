<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'players/index/{token}';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for players and then parse necessary data
define('FORCE_INCLUDE_HIDDEN_PLAYERS', $api_include_hidden);
define('FORCE_INCLUDE_INCOMPLETE_PLAYERS', $api_include_incomplete);
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/players.php');
if (empty($mmrpg_database_players)){ print_error_and_quit('The player database could not be loaded'); }
$player_data = !empty($mmrpg_database_players[$api_request_token]) ? $mmrpg_database_players[$api_request_token] : false;

// If not empty, go through and remove api-incompatible data
if (!empty($player_data)){
    unset($player_data['_parsed']);
    unset($player_data['player_id']);
    unset($player_data['player_functions']);
    $backup_player_data = $player_data; $player_data = array();
    foreach ($backup_player_data AS $k => $v){ $player_data[str_replace('player_', '', $k)] = $v; }
}

// Print out the players index as JSON so others can use them
if (!empty($player_data)){ print_success_and_update_api_cache(array('player' => $player_data)); }
else { print_error_and_quit('Player data for `'.$api_request_token.'` does not exist', MMRPG_API_ERROR_NOTFOUND); }

?>
