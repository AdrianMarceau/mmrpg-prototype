<?php

// Collect a list of tables from the database
$db_tables_list = $db->table_list();

// If `mmrpg_users_records_robots` table does not exist yet
if (!in_array('mmrpg_users_records_robots', $db_tables_list)
    && !in_array('mmrpg_users_robots_records', $db_tables_list)){

    // Create the new table in the database
    echo('- creating new table `mmrpg_users_records_robots` in the database'.PHP_EOL);
    $db->query("CREATE TABLE `mmrpg_users_records_robots` (
        `record_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
        `user_id` mediumint(8) unsigned DEFAULT '0' COMMENT 'User ID',
        `robot_token` varchar(128) NOT NULL DEFAULT '' COMMENT 'Robot Token',
        `robot_encountered` int(11) unsigned DEFAULT '0' COMMENT 'Robot Encountered',
        `robot_defeated` int(11) unsigned DEFAULT '0' COMMENT 'Robot Defeated',
        `robot_unlocked` int(11) unsigned DEFAULT '0' COMMENT 'Robot Unlocked',
        `robot_summoned` int(11) unsigned DEFAULT '0' COMMENT 'Robot Summoned',
        `robot_scanned` int(11) unsigned DEFAULT '0' COMMENT 'Robot Scanned',
        PRIMARY KEY (`record_id`),
        UNIQUE KEY `user_id_robot_token` (`user_id`,`robot_token`),
        KEY `user_id` (`user_id`),
        KEY `robot_token` (`robot_token`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC
        ;");

}

?>