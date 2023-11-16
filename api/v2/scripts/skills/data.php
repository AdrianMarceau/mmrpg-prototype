<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'skills/index/{token}';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Get an index of all skills from the database
$skill_data = rpg_skill::get_index_info($api_request_token);

// If not empty, go through and remove api-incompatible data
if (!empty($skill_data)){
    unset($skill_data['_parsed']);
    unset($skill_data['skill_id']);
    unset($skill_data['skill_functions']);
    $backup_skill_data = $skill_data; $skill_data = array();
    foreach ($backup_skill_data AS $k => $v){ $skill_data[str_replace('skill_', '', $k)] = $v; }
}

// Correct the name of the "none" skill to "Neutral"
if (!empty($skill_data) && $skill_data['token'] === 'none'){ $skill_data['name'] = 'Neutral'; }

// Print out the skills index as JSON so others can use them
if (!empty($skill_data)){ print_success_and_update_api_cache(array('skill' => $skill_data)); }
else { print_error_and_quit('Type data for `'.$api_request_token.'` does not exist', MMRPG_API_ERROR_NOTFOUND); }

?>
