<?php

// Collect a list of existing field names in the leaderboard table
$leaderboard_table = 'mmrpg_leaderboard';
$leaderboard_table_columns = $db->table_column_list($leaderboard_table);

// If `board_zenny` does not exist yet we create it
if (!in_array('board_zenny', $leaderboard_table_columns)){

    // Add the new column to the robot index table
    echo('- adding new column `board_zenny` to '.$leaderboard_table.PHP_EOL);
    $db->query("ALTER TABLE `{$leaderboard_table}`
        ADD `board_zenny` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Board Zenny'
        AFTER `board_missions_dr_cossack`
        ;");

    // Re-collect the field names for this table
    $leaderboard_table_columns = $db->table_column_list($leaderboard_table);

}

// If `board_zenny` exists we can populate it
if (in_array('board_zenny', $leaderboard_table_columns)){

    // Check to see if the newly created table is completely empty
    $existing_records = $db->get_value("SELECT
        COUNT(*) AS num_records
        FROM mmrpg_leaderboard
        WHERE board_points > 0 AND board_zenny > 0
        ;", 'num_records');

    $max_zenny_amount = 0;
    $max_zenny_user = 0;

    // If there are NO records, we should populate them now
    if (empty($existing_records)){

        // Collect a list of all users and their save data so we can populate the table
        echo('- populating `board_zenny` field in `mmrpg_leaderboard` table with user data'.PHP_EOL);
        $user_save_counters_records = $db->get_array_list("SELECT
            saves.user_id,
            saves.save_counters
            FROM mmrpg_saves AS saves
            LEFT JOIN mmrpg_leaderboard AS board ON board.user_id = saves.user_id
            WHERE board.board_points > 0 AND saves.user_id > 0
            ORDER BY board.board_date_modified DESC
            ;", 'user_id');

        // Loop through all the users found and update their database tables
        if (!empty($user_save_counters_records)){
            foreach ($user_save_counters_records AS $user_id => $save_data){
                if (empty($save_data['save_counters'])){ continue; }
                $user_save_counters = json_decode($save_data['save_counters'], true);
                if (!empty($user_save_counters['battle_zenny'])){
                    $zenny_amount = $user_save_counters['battle_zenny'];
                    echo('- adding zenny to leaderboard for user_id '.$user_id.PHP_EOL);
                    //error_log('$user_id = '.print_r($user_id, true));
                    //error_log('$zenny_amount = '.print_r($zenny_amount, true));
                    $db->update('mmrpg_leaderboard', array(
                        'board_zenny' => $zenny_amount
                        ), array(
                        'user_id' => $user_id
                        ));
                    //break;
                }
            }
        }

    }

}

?>
