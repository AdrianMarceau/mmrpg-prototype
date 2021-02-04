<?php

// Collect a list of tables from the database
$db_tables_list = $db->table_list();

// If `mmrpg_users_records_robots` table does not exist yet
if (!in_array('mmrpg_users_records_robots', $db_tables_list)){

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

    // Refresh the list of tables from the database
    $db_tables_list = $db->table_list();

    // If `mmrpg_users_records_robots` table exists now
    if (in_array('mmrpg_users_records_robots', $db_tables_list)){

        // Collect a list of all users and their save data so we can populate the table
        echo('- populating `mmrpg_users_records_robots` table with user data'.PHP_EOL);
        $user_robot_database_records = $db->get_array_list("SELECT
            saves.user_id,
            saves.save_values_robot_database
            FROM mmrpg_saves AS saves
            LEFT JOIN mmrpg_leaderboard AS board ON board.user_id = saves.user_id
            WHERE board.board_points > 0 AND saves.user_id > 0
            ORDER BY board.board_date_modified DESC
            ;", 'user_id');

        // Loop through all the users found and update their database tables
        if (!empty($user_robot_database_records)){
            foreach ($user_robot_database_records AS $user_id => $save_data){
                if (empty($save_data['save_values_robot_database'])){ continue; }
                $user_robot_database = json_decode($save_data['save_values_robot_database'], true);
                if (!empty($user_robot_database)){
                    echo('- adding records for user_id '.$user_id.PHP_EOL);
                    //error_log('$user_id = '.print_r($user_id, true));
                    //error_log('$user_robot_database = '.print_r($user_robot_database, true));
                    mmrpg_update_user_robot_records($user_id, $user_robot_database);
                    //break;
                }
            }
        }

    }

}

?>