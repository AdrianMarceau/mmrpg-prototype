<?php

// Collect a list of tables from the database
$db_tables_list = $db->table_list();

// If `mmrpg_users_unlocked_stars` table does not exist yet
if (!in_array('mmrpg_users_unlocked_stars', $db_tables_list)){

    // Create the new table in the database
    echo('- creating new table `mmrpg_users_unlocked_stars` in the database'.PHP_EOL);
    $db->query("CREATE TABLE `mmrpg_users_unlocked_stars` (
        `record_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
        `user_id` mediumint(8) unsigned DEFAULT '0' COMMENT 'User ID',
        `star_token` varchar(128) DEFAULT '' COMMENT 'Star Token',
        `star_name` varchar(128) DEFAULT '' COMMENT 'Star Name',
        `star_kind` varchar(8) DEFAULT '' COMMENT 'Star Kind',
        `star_type` varchar(16) DEFAULT '' COMMENT 'Star Type',
        `star_type2` varchar(16) DEFAULT '' COMMENT 'Star Type 2',
        `star_field` varchar(128) DEFAULT '' COMMENT 'Star Field',
        `star_field2` varchar(128) DEFAULT '' COMMENT 'Star Field 2',
        `star_player` varchar(64) DEFAULT '' COMMENT 'Star Player',
        `star_date` int(11) unsigned DEFAULT '0' COMMENT 'Star Date',
        PRIMARY KEY (`record_id`),
        UNIQUE KEY `user_id_star_token` (`user_id`,`star_token`),
        KEY `user_id` (`user_id`),
        KEY `star_token` (`star_token`),
        KEY `star_kind` (`star_kind`),
        KEY `star_type` (`star_type`),
        KEY `star_type2` (`star_type2`),
        KEY `star_field` (`star_field`),
        KEY `star_field2` (`star_field2`),
        KEY `star_player` (`star_player`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
        ;");

}

?>