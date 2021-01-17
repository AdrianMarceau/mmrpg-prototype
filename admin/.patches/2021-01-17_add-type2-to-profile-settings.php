<?php

// Collect a list of existing field names in the users index table
$users_table = 'mmrpg_users';
$users_table_columns = $db->table_column_list($users_table);

// If `user_colour_token2` does not exist yet
if (!in_array('user_colour_token2', $users_table_columns)){
    echo('- adding new column `user_colour_token2` to '.$users_table.PHP_EOL);
    $db->query("ALTER TABLE `{$users_table}`
      ADD `user_colour_token2` varchar(100) NOT NULL DEFAULT '' COMMENT 'User Colour Token 2'
        AFTER `user_colour_token`
        ;");
}

// Collect a list of existing field names in the contributors index table
$contributors_table = 'mmrpg_users_contributors';
$contributors_table_columns = $db->table_column_list($contributors_table);

// If `user_colour_token2` does not exist yet
if (!in_array('user_colour_token2', $contributors_table_columns)){
    echo('- adding new column `user_colour_token2` to '.$contributors_table.PHP_EOL);
    $db->query("ALTER TABLE `{$contributors_table}`
      ADD `user_colour_token2` varchar(100) NOT NULL DEFAULT '' COMMENT 'User Colour Token 2'
        AFTER `user_colour_token`
        ;");
}

?>
