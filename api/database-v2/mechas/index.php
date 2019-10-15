<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'mechas/index';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Include the database file for mechas and then parse necessary data
$mmrpg_database_mechas_filter = "AND robot_flag_hidden = 0 AND robot_flag_complete = 1 AND robot_flag_published = 1 ";
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/mechas.php');
if (empty($mmrpg_database_mechas)){ print_error_and_quit('The mecha database could not be loaded'); }
$mechas_index = !empty($mmrpg_database_mechas) ? $mmrpg_database_mechas : array();

// If not empty, go through and remove or reformat api-incompatible data
if (!empty($mechas_index)){
    foreach ($mechas_index AS $mecha_token => $mecha_data){
        unset($mecha_data['_parsed']);
        unset($mecha_data['robot_id']);
        unset($mecha_data['robot_functions']);
        $mecha_data['robot_abilities_rewards'] = !empty($mecha_data['robot_rewards']['abilities']) ? $mecha_data['robot_rewards']['abilities'] : array();
        $mecha_data['robot_abilities_compatible'] = !empty($mecha_data['robot_abilities']) ? $mecha_data['robot_abilities'] : array();
        unset($mecha_data['robot_abilities'], $mecha_data['robot_rewards']);
        $backup_mecha_data = $mecha_data; $mecha_data = array();
        foreach ($backup_mecha_data AS $k => $v){ $mecha_data[str_replace('robot_', '', $k)] = $v; }
        $mechas_index[$mecha_token] = $mecha_data;
    }
}

// Print out the mechas index as JSON so others can use them
if (!empty($mechas_index)){ print_success_and_update_api_cache(array('mechas' => $mechas_index, 'total' => count($mechas_index))); }
else { print_error_and_quit('The mecha index array was empty'); }

?>
