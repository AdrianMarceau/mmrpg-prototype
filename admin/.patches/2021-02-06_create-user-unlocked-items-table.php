<?php

// Collect a list of tables from the database
$db_tables_list = $db->table_list();

// If `mmrpg_users_unlocked_items` table does not exist yet
if (!in_array('mmrpg_users_unlocked_items', $db_tables_list)
    && !in_array('mmrpg_users_items_unlocked', $db_tables_list)){

    // Create the new table in the database
    echo('- creating new table `mmrpg_users_unlocked_items` in the database'.PHP_EOL);
    $db->query("CREATE TABLE `mmrpg_users_unlocked_items` (
        `record_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
        `user_id` mediumint(8) unsigned DEFAULT '0' COMMENT 'User ID',
        `item_token` varchar(100) DEFAULT '' COMMENT 'Item Token',
        `item_quantity` mediumint(8) unsigned DEFAULT '0' COMMENT 'Item Quantity',
        PRIMARY KEY (`record_id`),
        UNIQUE KEY `user_id_item_token` (`user_id`,`item_token`),
        KEY `user_id` (`user_id`),
        KEY `item_token` (`item_token`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ;");

}

?>