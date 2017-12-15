<?php

// Require the global top file
require('../../top.php');

// Get a list of all field tokens from the database
$field_tokens = rpg_field::get_index_tokens(true, false, false);

// Print out the field tokens as JSON so others can use them
header('Content-type: text/json; charset=UTF-8');
if (!empty($field_tokens)){ $return_array = array('success' => true, 'field_tokens' => $field_tokens); }
else { $return_array = array('error' => true, 'message' => 'field tokens could not be found'); }
echo(json_encode($return_array));
exit();

?>
