<?php

// Collect a list of tables from the database
$db_tables_list = $db->table_list();

// If `mmrpg_users_records_robots` table exists now
if (in_array('mmrpg_users_records_robots', $db_tables_list)
    && method_exists('rpg_user', 'update_robot_records')){

    // Check to see if the newly created table is completely empty
    $existing_records = $db->get_value("SELECT
        COUNT(*) AS num_records
        FROM mmrpg_users_records_robots
        WHERE user_id > 0
        ;", 'num_records');

    // If there are NO records, we should populate them now
    if (empty($existing_records)){

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
                    rpg_user::update_robot_records($user_id, $user_robot_database);
                    //break;
                }
            }
        }

    }

}

?>