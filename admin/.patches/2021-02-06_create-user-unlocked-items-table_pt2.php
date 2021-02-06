<?php

// Collect a list of tables from the database
$db_tables_list = $db->table_list();

// If `mmrpg_users_unlocked_items` table exists now
if (in_array('mmrpg_users_unlocked_items', $db_tables_list)
    && method_exists('rpg_user', 'update_unlocked_items')){

    // Check to see if the newly created table is completely empty
    $existing_records = $db->get_value("SELECT
        COUNT(*) AS num_records
        FROM mmrpg_users_unlocked_items
        WHERE user_id > 0
        ;", 'num_records');

    // If there are NO records, we should populate them now
    if (empty($existing_records)){

        // Collect a list of all users and their save data so we can populate the table
        echo('- populating `mmrpg_users_unlocked_items` table with user data'.PHP_EOL);
        $user_unlocked_items_records = $db->get_array_list("SELECT
            saves.user_id,
            saves.save_values_battle_items
            FROM mmrpg_saves AS saves
            LEFT JOIN mmrpg_leaderboard AS board ON board.user_id = saves.user_id
            WHERE board.board_points > 0 AND saves.user_id > 0
            ORDER BY board.board_date_modified DESC
            ;", 'user_id');

        // Loop through all the users found and update their database tables
        if (!empty($user_unlocked_items_records)){
            foreach ($user_unlocked_items_records AS $user_id => $save_data){
                if (empty($save_data['save_values_battle_items'])){ continue; }
                $user_unlocked_items = json_decode($save_data['save_values_battle_items'], true);
                if (!empty($user_unlocked_items)){
                    echo('- adding records for user_id '.$user_id.PHP_EOL);
                    //error_log('$user_id = '.print_r($user_id, true));
                    //error_log('$user_unlocked_items = '.print_r($user_unlocked_items, true));
                    rpg_user::update_unlocked_items($user_id, $user_unlocked_items);
                    //break;
                }
            }
        }

    }

}

?>