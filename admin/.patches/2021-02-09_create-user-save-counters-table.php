<?php

// Collect a list of tables from the database
$db_tables_list = $db->table_list();

// If `mmrpg_users_save_counters` table does not exist yet
if (!in_array('mmrpg_users_save_counters', $db_tables_list)){

    // Create the new table in the database
    echo('- creating new table `mmrpg_users_save_counters` in the database'.PHP_EOL);
    $db->query("CREATE TABLE `mmrpg_users_save_counters` (
        `record_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
        `user_id` mediumint(8) unsigned DEFAULT '0' COMMENT 'User ID',
        `counter_token` varchar(128) DEFAULT '' COMMENT 'Counter Token',
        `counter_value` int(11) unsigned DEFAULT '0' COMMENT 'Counter Value',
        PRIMARY KEY (`record_id`),
        UNIQUE KEY `user_id_2` (`user_id`,`counter_token`),
        KEY `user_id` (`user_id`),
        KEY `counter_token` (`counter_token`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ;");

}

?>