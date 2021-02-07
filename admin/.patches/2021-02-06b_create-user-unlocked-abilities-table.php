<?php

// Collect a list of tables from the database
$db_tables_list = $db->table_list();

// If `mmrpg_users_unlocked_abilities` table does not exist yet
if (!in_array('mmrpg_users_unlocked_abilities', $db_tables_list)){

    // Create the new table in the database
    echo('- creating new table `mmrpg_users_unlocked_abilities` in the database'.PHP_EOL);
    $db->query("CREATE TABLE `mmrpg_users_unlocked_abilities` (
        `record_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Record ID',
        `user_id` mediumint(8) unsigned DEFAULT '0' COMMENT 'User ID',
        `ability_token` varchar(100) DEFAULT '' COMMENT 'Ability Token',
        PRIMARY KEY (`record_id`),
        UNIQUE KEY `user_id_ability_token` (`user_id`,`ability_token`),
        KEY `user_id` (`user_id`),
        KEY `ability_token` (`ability_token`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ;");

}

?>