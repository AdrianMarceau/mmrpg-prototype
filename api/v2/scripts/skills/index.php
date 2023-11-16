<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'skills/index';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Get an index of all skills from the database
$skills_index = rpg_skill::get_index($api_include_hidden, false);

// If not empty, loop through and remove api-incompatible data
if (!empty($skills_index)){
    foreach ($skills_index AS $skill_token => $skill_data){
        unset($skill_data['_parsed']);
        unset($skill_data['skill_id']);
        unset($skill_data['skill_functions']);
        $backup_skill_data = $skill_data; $skill_data = array();
        foreach ($backup_skill_data AS $k => $v){ $skill_data[str_replace('skill_', '', $k)] = $v; }
        $skills_index[$skill_token] = $skill_data;
    }
}

// Correct the name of the "none" skill to "Neutral"
if (isset($skills_index['none'])){ $skills_index['none']['name'] = 'Neutral'; }

// Print out the skills index as JSON so others can use them
if (!empty($skills_index)){ print_success_and_update_api_cache(array('skills' => $skills_index, 'total' => count($skills_index))); }
else { print_success_and_update_api_cache('The skill index array was empty'); }

?>
