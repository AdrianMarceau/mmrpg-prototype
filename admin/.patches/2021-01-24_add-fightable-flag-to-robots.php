<?php

// Collect a list of existing field names in the robots index table
$robots_table = 'mmrpg_index_robots';
$robots_table_columns = $db->table_column_list($robots_table);

// If `robot_flag_fightable` does not exist yet
if (!in_array('robot_flag_fightable', $robots_table_columns)){

    // Add the new column to the robot index table
    echo('- adding new column `robot_flag_fightable` to '.$robots_table.PHP_EOL);
    $db->query("ALTER TABLE `{$robots_table}`
        ADD `robot_flag_fightable` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Robot Flag Fightable'
        AFTER `robot_flag_complete`
        ;");

    // Update all the robot masters with an appropriate flag value
    echo('- updating in-game robot masters w/ appropriate `robot_flag_fightable` value'.PHP_EOL);
    $db->query("UPDATE `{$robots_table}`
        SET `robot_flag_fightable` = 1
        WHERE
        `robot_flag_published` = 1
        AND `robot_flag_complete` = 1
        AND `robot_class` = 'master'
        ;");

    // Update all the support mechas with an appropriate flag value
    echo('- updating in-game support mechas w/ appropriate `robot_flag_fightable` value'.PHP_EOL);
    $db->query("UPDATE `{$robots_table}`
        SET `robot_flag_fightable` = 1
        WHERE
        `robot_flag_published` = 1
        AND `robot_flag_complete` = 1
        AND `robot_class` = 'mecha'
        AND `robot_token` NOT IN ('sniper-joe', 'skeleton-joe', 'crystal-joe')
        ;");

    // Update all the fortress bosses with an appropriate flag value
    echo('- updating in-game fortress bosses w/ appropriate `robot_flag_fightable` value'.PHP_EOL);
    $db->query("UPDATE `{$robots_table}`
        SET `robot_flag_fightable` = 1
        WHERE
        `robot_flag_published` = 1
        AND `robot_flag_complete` = 1
        AND `robot_class` = 'boss'
        AND `robot_token` IN (
            'mega-man-ds', 'bass-ds', 'proto-man-ds',
            'enker', 'punk', 'ballade', 'quint'
            )
        ;");

    // Manually update the JSON data for all the robots (masters, mechas, bosses) that have had their data updates
    echo('- updating JSON data for all robots that have had `robot_flag_fightable` set to true'.PHP_EOL);
    $temp_robot_fields = rpg_robot::get_index_fields(true);
    $updated_robots_list = $db->get_array_list("SELECT
        {$temp_robot_fields}
        FROM `{$robots_table}`
        WHERE `robot_flag_fightable` = 1
        ;", 'robot_token');
    foreach ($updated_robots_list AS $robot_token => $robot_info){
        cms_admin::object_editor_update_json_data_file('robot', $robot_info);
    }

}

?>
