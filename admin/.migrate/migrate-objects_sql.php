<?

ob_echo('');
ob_echo('===========================');
ob_echo('|   START SQL MIGRATION   |');
ob_echo('===========================');
ob_echo('');

// Define a list of all database tables that should be migrated and whether we should include their data
$sql_table_list = array();
if (true){

    // global
    $sql_table_list[] = array('name' => 'mmrpg_config');

    // users & saves & leaderboard
    $sql_table_list[] = array('name' => 'mmrpg_users', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_users_contributors');
    $sql_table_list[] = array('name' => 'mmrpg_users_permissions', 'export_data' => false, 'export_using_prod' => false);
    $sql_table_list[] = array('name' => 'mmrpg_roles');
    $sql_table_list[] = array('name' => 'mmrpg_saves', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_sessions', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_leaderboard', 'export_data' => false);

    // website & pages
    $sql_table_list[] = array('name' => 'mmrpg_website_pages', 'export_data' => false);

    // content index
    $sql_table_list[] = array('name' => 'mmrpg_index_types', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_index_players', 'export_data' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_players_groups', 'export_data' => false, 'export_using_prod' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_players_groups_tokens', 'export_data' => false, 'export_using_prod' => false);
    $sql_table_list[] = array('name' => 'mmrpg_index_robots', 'export_data' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_robots_groups', 'export_data' => false, 'export_using_prod' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_robots_groups_tokens', 'export_data' => false, 'export_using_prod' => false);
    $sql_table_list[] = array('name' => 'mmrpg_index_abilities', 'export_data' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_abilities_groups', 'export_data' => false, 'export_using_prod' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_abilities_groups_tokens', 'export_data' => false, 'export_using_prod' => false);
    $sql_table_list[] = array('name' => 'mmrpg_index_items', 'export_data' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_items_groups', 'export_data' => false, 'export_using_prod' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_items_groups_tokens', 'export_data' => false, 'export_using_prod' => false);
    $sql_table_list[] = array('name' => 'mmrpg_index_fields', 'export_data' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_fields_groups', 'export_data' => false, 'export_using_prod' => false);
        $sql_table_list[] = array('name' => 'mmrpg_index_fields_groups_tokens', 'export_data' => false, 'export_using_prod' => false);
    $sql_table_list[] = array('name' => 'mmrpg_index_sources', 'export_using_prod' => false);

    // content records
    $sql_table_list[] = array('name' => 'mmrpg_records_abilities', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_records_robots', 'export_data' => false);

    // community
    $sql_table_list[] = array('name' => 'mmrpg_categories');
    $sql_table_list[] = array('name' => 'mmrpg_threads', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_posts', 'export_data' => false);

    // rogue stars
    $sql_table_list[] = array('name' => 'mmrpg_rogue_stars', 'export_data' => false);

    // player battles
    $sql_table_list[] = array('name' => 'mmrpg_battles', 'export_data' => false);

    // challenge missions
    $sql_table_list[] = array('name' => 'mmrpg_challenges', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_challenges_leaderboard', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_challenges_waveboard', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_users_challenges', 'export_data' => false);
    $sql_table_list[] = array('name' => 'mmrpg_users_challenges_leaderboard', 'export_data' => false);

}

// Reformat into a proper index for easier reference
$sql_table_index = array();
foreach ($sql_table_list AS $key => $data){
    if (!isset($data['name'])){ continue; }
    $new_data = array();
    $new_data['name'] = $data['name'];
    $new_data['export_table'] = isset($data['export_table']) ? $data['export_table'] : true;
    $new_data['export_data'] = isset($data['export_data']) ? $data['export_data'] : true;
    $new_data['export_filter'] = isset($data['export_filter']) ? $data['export_filter'] : false;
    $new_data['export_using_prod'] = isset($data['export_using_prod']) ? $data['export_using_prod'] : true;
    $sql_table_index[$new_data['name']] = $new_data;
}
unset($sql_table_list);

//ob_echo('$sql_table_index = '.print_r($sql_table_index, true));
//exit();

// Pre-define the base SQL content dir
define('MMRPG_SQL_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/.sql/');

// Delete existing table and data directories so we can start over
$sql_tables_export_dir = MMRPG_SQL_NEW_CONTENT_DIR.'tables/';
if (file_exists($sql_tables_export_dir)){ deletedir_or_exit($sql_tables_export_dir); }
if (!file_exists($sql_tables_export_dir)){ mkdir_or_exit($sql_tables_export_dir); }
$sql_data_export_dir = MMRPG_SQL_NEW_CONTENT_DIR.'data/';
if (file_exists($sql_data_export_dir)){ deletedir_or_exit($sql_data_export_dir); }
if (!file_exists($sql_data_export_dir)){ mkdir_or_exit($sql_data_export_dir); }

// Count the number of pages that we'll be looping through
$sql_index_size = count($sql_table_index);
$count_pad_length = strlen($sql_index_size);

// Print out the stats before we start
ob_echo('Total Database Tables: '.$sql_index_size);
ob_echo('');

sleep(1);

$sql_tables_exported = array();

$sql_data_required = array();
$sql_data_exported = array();

// MIGRATE ACTUAL SQL TABLES
$table_key = -1; $table_num = 0;
foreach ($sql_table_index AS $table_name => $table_settings){
    $table_key++; $table_num++;
    $count_string = '('.$table_num.' of '.$sql_index_size.')';

    ob_echo('----------');
    ob_echo('Processing table "'.$table_name.'" '.$count_string);
    ob_flush();

    // Export this table def if explicitly allowed to do so
    if ($table_settings['export_table'] === true){
        $success = false;
        $export_path = $sql_tables_export_dir.$table_name.'.sql';
        ob_echo_nobreak('- exporting CREATE table def to '.clean_path($export_path).' ... ');
        $table_def_sql = mmrpg_get_create_table_sql($table_name, $table_settings, $table_settings['export_using_prod']);
        if (!empty($table_def_sql)){
            $f = fopen($export_path, 'w');
            fwrite($f, $table_def_sql);
            fclose($f);
            if (file_exists($export_path)){ ob_echo('success!'); $success = true; }
            else { ob_echo('write failure!'); }
        } else {
            ob_echo('gen failure!');
        }
        if ($success){ $sql_tables_exported[] = basename($table_name); }
    }

    // Export this table data if explicitly allowed to do so
    if ($table_settings['export_data'] === true){
        $sql_data_required[] = $table_name;
        $success = false;
        $export_path = $sql_data_export_dir.$table_name.'.sql';
        $is_filtered = !empty($table_settings['export_filter']) ? true : false;
        ob_echo_nobreak('- exporting '.($is_filtered ? 'FILTERED' : 'FULL').' table data to '.clean_path($export_path).' ... ');
        $table_rows_sql = mmrpg_get_insert_table_data_sql($table_name, $table_settings, $table_settings['export_using_prod']);
        if (!empty($table_rows_sql)){
            $f = fopen($export_path, 'w');
            fwrite($f, $table_rows_sql);
            fclose($f);
            if (file_exists($export_path)){ ob_echo('success!'); $success = true; }
            else { ob_echo('write failure!'); }
        } else {
            ob_echo('gen failure!');
        }
        if ($success){ $sql_data_exported[] = $table_name; }
    }

    if ($migration_limit && $table_num >= $migration_limit){ break; }

}

ob_echo('----------');

ob_echo('');
ob_echo('Tables Exported: '.count($sql_tables_exported).' / '.$sql_index_size);
ob_echo('Table Data Exported: '.count($sql_data_exported).' / '.count($sql_data_required));
if (!($migration_limit > 0)){
    ob_echo('');
    ob_echo('Table Data Not Exported: '.print_r(array_diff($sql_data_required, $sql_data_exported), true));
}

sleep(1);

ob_echo('');
ob_echo('===========================');
ob_echo('|    END SQL MIGRATION    |');
ob_echo('===========================');
ob_echo('');

?>