<?php

// Collect a list of tables from the database
$db_tables_list = $db->table_list();

// Define a list of tables to rename in this patch
$rename_db_tables = array();
$rename_db_tables['mmrpg_users_records_robots'] = 'mmrpg_users_robots_records';
$rename_db_tables['mmrpg_users_unlocked_abilities'] = 'mmrpg_users_abilities_unlocked';
$rename_db_tables['mmrpg_users_unlocked_items'] = 'mmrpg_users_items_unlocked';
$rename_db_tables['mmrpg_users_unlocked_stars'] = 'mmrpg_users_stars_unlocked';

// Loop through the tables we are to rename and process them
foreach ($rename_db_tables AS $old_table_name => $new_table_name){

    // If `old_table_name` exists, we should rename it now
    if (in_array($old_table_name, $db_tables_list)){

        // Create the new table in the database
        echo('- rename database table `'.$old_table_name.'` to `'.$new_table_name.'`'.PHP_EOL);
        $db->query("RENAME TABLE `{$old_table_name}` TO `{$new_table_name}`;");

    }

}

?>