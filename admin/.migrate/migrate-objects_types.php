<?

ob_echo('');
ob_echo('============================');
ob_echo('|   START TYPE MIGRATION   |');
ob_echo('============================');
ob_echo('');

// Collect an index of all valid types from the database
$type_fields = rpg_type::get_index_fields(true);
$type_index = $db->get_array_list("SELECT {$type_fields} FROM mmrpg_index_types ORDER BY type_token ASC", 'type_token');

// Manually add a template "type" to match the other repos
$template_type = $type_index['none'];
$template_type['type_token'] = 'type';
$template_type['type_name'] = 'Type';
$template_type['type_class'] = 'system';
$template_type['type_order'] = -1;
$type_index['type'] = $template_type;

// If there's a filter present, remove all tokens not in the filter
if (!empty($migration_filter)){
    $old_type_index = $type_index;
    $type_index = array();
    foreach ($migration_filter AS $type_token){
        if (isset($old_type_index[$type_token])){
            $type_index[$type_token] = $old_type_index[$type_token];
        }
    }
    unset($old_type_index);
}

// Pre-define the base type content dir
define('MMRPG_TYPES_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/types/');

// Count the number of types that we'll be looping through
$type_index_size = count($type_index);
$count_pad_length = strlen($type_index_size);

// Print out the stats before we start
ob_echo('Total Types in Database: '.$type_index_size);
ob_echo('');

sleep(1);

$type_data_files_copied = array();

// MIGRATE ACTUAL TYPES
$type_key = -1; $type_num = 0;
foreach ($type_index AS $type_token => $type_data){
    $type_key++; $type_num++;
    $count_string = '('.$type_num.' of '.$type_index_size.')';

    ob_echo('----------');
    ob_echo('Processing type "'.$type_token.'" '.$count_string);
    ob_flush();

    $data_path = MMRPG_MIGRATE_OLD_DATA_DIR.$type_token.'.php';
    //ob_echo('-- $data_path = '.clean_path($data_path));

    $content_path = MMRPG_TYPES_NEW_CONTENT_DIR.($type_token === 'type' ? '.type' : $type_token).'/';
    //ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deletedir_or_exit($content_path); }
    mkdir_or_exit($content_path);


    // Ensure the data file exists before adding it to the copied list
    // (we don't actually copy it though, this is just for tracking)
    if (file_exists($data_path)){
        $type_data_files_copied[] = basename($data_path);
    }

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('type', $type_data);
    ob_echo('- export all other data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, normalize_file_markup(json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)));
    fclose($h);

    if ($migration_limit && $type_num >= $migration_limit){ break; }

}


ob_echo('----------');

ob_echo('');
ob_echo('Type Data Files Copied: '.count($type_data_files_copied).' / '.$type_index_size);


sleep(1);

ob_echo('');
ob_echo('============================');
ob_echo('|    END TYPE MIGRATION    |');
ob_echo('============================');
ob_echo('');

?>