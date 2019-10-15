<?php

// Require the global top file
require('../../../top.php');

// Define the for the API cache file, then include API functionality
$api_request_path = 'fields';
require(MMRPG_CONFIG_ROOTDIR.'api/api-common.php');

// Include the database file for fields and then parse necessary data
$mmrpg_database_fields_filter = "AND field_flag_published = 1 ";
if (!$api_include_hidden){ $mmrpg_database_fields_filter = "AND field_flag_hidden = 0 "; }
if (!$api_include_incomplete){ $mmrpg_database_fields_filter = "AND field_flag_complete = 1 "; }
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'database/fields.php');
if (empty($mmrpg_database_fields)){ print_error_and_quit('The field database could not be loaded'); }
$field_tokens = !empty($mmrpg_database_fields) ? array_keys($mmrpg_database_fields) : array();

// Print out the field tokens as JSON so others can use them
if (!empty($field_tokens)){ print_success_and_update_api_cache(array('fields' => $field_tokens, 'total' => count($field_tokens))); }
else { print_error_and_quit('The field token array was empty'); }

?>
