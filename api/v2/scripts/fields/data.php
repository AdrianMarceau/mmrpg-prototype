<?php

// Define the for the API cache file, then include API functionality
$api_request_path = 'fields/index/{token}';
require(MMRPG_CONFIG_API_ROOTDIR.'api-common.php');

// Include the database file for fields and then parse necessary data
define('FORCE_INCLUDE_HIDDEN_FIELDS', $api_include_hidden);
define('FORCE_INCLUDE_INCOMPLETE_FIELDS', $api_include_incomplete);
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/fields.php');
if (empty($mmrpg_database_fields)){ print_error_and_quit('The field database could not be loaded'); }
$field_data = !empty($mmrpg_database_fields[$api_request_token]) ? $mmrpg_database_fields[$api_request_token] : false;

// If not empty, go through and remove api-incompatible data
if (!empty($field_data)){
    unset($field_data['_parsed']);
    unset($field_data['field_id']);
    unset($field_data['field_functions']);
    $backup_field_data = $field_data; $field_data = array();
    foreach ($backup_field_data AS $k => $v){ $field_data[str_replace('field_', '', $k)] = $v; }
}

// Print out the fields index as JSON so others can use them
if (!empty($field_data)){ print_success_and_update_api_cache(array('field' => $field_data)); }
else { print_error_and_quit('Field data for `'.$api_request_token.'` does not exist', MMRPG_API_ERROR_NOTFOUND); }

?>
