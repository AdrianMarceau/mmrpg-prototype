<?

// Define the common data directory, taking into account possible rename
$data_dir = MMRPG_CONFIG_ROOTDIR.'data/';
if (!file_exists($data_dir)){ $data_dir = MMRPG_CONFIG_ROOTDIR.'xxx_data/'; }
if (!file_exists($data_dir)){ exit('At least one /data/ directory MUST exist to migrate!'.PHP_EOL); }
define('MMRPG_MIGRATE_OLD_DATA_DIR', $data_dir);

// Define a quick function for immediately printing an echo statement
function ob_echo($echo, $silent = false){ if (!$silent){ echo($echo.PHP_EOL); } ob_flush(); }
function ob_echo_nobreak($echo, $silent = false){ if (!$silent){ echo($echo); } ob_flush(); }

// Define a function for cleaning a path of the root dir for printing
function clean_path($path){ return str_replace(MMRPG_CONFIG_ROOTDIR, '/', $path); }

// Define a quick function for copying rpg object sprites from one directory to another
function copy_sprites_to_new_dir($base_token, $count_string, $new_sprite_path, $exclude_sprites = array(), $delete_existing = true, $silent_mode = false, $short_mode = false){
    global $migration_kind, $migration_kind_singular, $migration_limit;
    $kind = $migration_kind_singular;
    $kind_plural = $migration_kind;
    if (!$short_mode){
        ob_echo('----------', $silent_mode);
        ob_echo('Processing '.$kind.' sprites for "'.$base_token.'" '.$count_string, $silent_mode);
    }
    ob_flush();
    if (!strstr($new_sprite_path, MMRPG_CONFIG_ROOTDIR)){ $new_sprite_path = MMRPG_CONFIG_ROOTDIR.ltrim($new_sprite_path, '/'); }
    $base_sprite_path = MMRPG_CONFIG_ROOTDIR.'images/'.$kind_plural.'/'.$base_token.'/';
    //ob_echo('-- $base_sprite_path = '.clean_path($base_sprite_path), $silent_mode);
    if (!file_exists($base_sprite_path)){
        $base_sprite_path = MMRPG_CONFIG_ROOTDIR.'images/xxx_'.$kind_plural.'/'.$base_token.'/';
        //ob_echo('-- $base_sprite_path(2) = '.clean_path($base_sprite_path), $silent_mode);
    }
    if (!file_exists($base_sprite_path)){
        ob_echo('- '.clean_path($base_sprite_path).' does not exist', $silent_mode);
        return false;
    }
    //ob_echo('-- $new_sprite_path = '.clean_path($new_sprite_path), $silent_mode);
    if ($delete_existing && file_exists($new_sprite_path)){ deleteDir($new_sprite_path); }
    if (!file_exists($new_sprite_path)){ mkdir($new_sprite_path); }
    ob_echo('- copy '.clean_path($base_sprite_path).'* to '.clean_path($new_sprite_path), $silent_mode);
    recurseCopy($base_sprite_path, $new_sprite_path, $exclude_sprites);
    $global_image_directories_copied = $kind.'_image_directories_copied';
    global $$global_image_directories_copied;
    ${$global_image_directories_copied}[] = basename($base_sprite_path);
    return true;
    };

// Define a function for parsing an object file's markup into actual data vs functions
function get_parsed_object_file_markup($object_file_path){
    // First make sure the file actually exists
    if (!file_exists($object_file_path)){
        ob_echo('- object file '.clean_path($object_file_path).' does not exist');
        return false;
    }
    // Now open the file and collect its contents into a line-by-line array
    $file_contents = trim(file_get_contents($object_file_path));
    $file_contents_array = explode(PHP_EOL, $file_contents);
    $file_contents_array_size = count($file_contents_array);
    // Pre-populate the markup arrays for the data vs functions lines, we'll clean later
    $data_markup_array = $file_contents_array;
    $functions_markup_array = $file_contents_array;
    // Define the object kinds pattern for use in strings below
    $okinds = '(?:ability|battle|field|item|player|robot|type)';
    // Remove all the non-function markup from the function markup arrow
    foreach ($functions_markup_array AS $line_key => $line_markup){
        if ($line_markup == '<?' || $line_markup == '<?php' || $line_markup == '?>'){ continue; }
        if (preg_match('/^\/\/ [A-Z]+/', $line_markup)){
            unset($data_markup_array[$line_key]);
            unset($functions_markup_array[$line_key]);
            continue;
        }
        if (preg_match('/^\$'.$okinds.' = array\(/', $line_markup)){
            $data_markup_array[$line_key] = '$data = array(';
            $functions_markup_array[$line_key] = '$functions = array(';
            continue;
        }
        if (preg_match('/^\s+(\/\/)?\''.$okinds.'_([_a-z0-9]+)\' =>\s/', $line_markup)){
            if (!preg_match('/^\s+(\/\/)?\''.$okinds.'_function(_[a-z0-9]+)?\' =>\s/', $line_markup)){
                unset($functions_markup_array[$line_key]);
                continue;
            } else {
                $data_markup_array[$line_key - 1] = rtrim($data_markup_array[$line_key - 1], ',');
                for ($i = $line_key; $i < ($file_contents_array_size - 2); $i++){ unset($data_markup_array[$i]); }
                $data_markup_array[$file_contents_array_size - 2] = ltrim($data_markup_array[$file_contents_array_size - 2], ' ');
                $functions_markup_array[$file_contents_array_size - 2] = ltrim($functions_markup_array[$file_contents_array_size - 2], ' ');
                break;
            }
        }
    }
    //ob_echo('- $file_contents_array = '.print_r($file_contents_array, true));
    //ob_echo('- $data_markup_array = '.print_r($data_markup_array, true));
    //ob_echo('- $functions_markup_array = '.print_r($functions_markup_array, true));
    return array(
        'data' => trim(implode(PHP_EOL, $data_markup_array)).PHP_EOL,
        'functions' => trim(implode(PHP_EOL, $functions_markup_array)).PHP_EOL
        );
}

// Define a function for generating empty object data vs functions files
function get_empty_functions_file_markup($kind){
    $empty_file_markup = $GLOBALS['empty_function_file_markup'];
    $empty_file_markup = str_replace('{{kind}}', $kind, $empty_file_markup);
    return trim($empty_file_markup).PHP_EOL;
}
$empty_function_file_markup = <<<'PHP'
<?
$functions = array(
    '{{kind}}_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Return true on success
        return true;

    }
);
?>
PHP;

// Define a function for cleaning a JSON array for migration
// (example: set pseudo-empty fields to empty strings)
function clean_json_content_array($kind, $content_json_data, $remove_id_field = true, $remove_functions_field = true){
    // Make a copy of the origin al JSON data
    $cleaned_json_data = $content_json_data;
    // Remove any known unnecessary or deprecated fields from the data
    if ($remove_id_field){ unset($cleaned_json_data[$kind.'_id']); }
    if ($remove_functions_field){ unset($cleaned_json_data[$kind.'_functions']); }
    // Loop through fields and set any psudeo-empty fields to actally empty
    foreach ($cleaned_json_data AS $k => $v){ if ($v === '[]'){ $cleaned_json_data[$k] = ''; } }
    // If not empty, loop through any encoded sub-fields and re-compress
    if (method_exists('rpg_'.$kind, 'get_json_index_fields')){
        $encoded_sub_fields = call_user_func(array('rpg_'.$kind, 'get_json_index_fields'));
        if (!empty($encoded_sub_fields)){
            foreach ($encoded_sub_fields AS $sub_field_name){
                $sub_field_value = $cleaned_json_data[$sub_field_name];
                if (!empty($sub_field_value)){
                    $sub_field_value = json_decode($sub_field_value, true);
                    $cleaned_json_data[$sub_field_name] = json_encode($sub_field_value, JSON_NUMERIC_CHECK);
                }
            }
        }
    }
    // If there are an image editor fields, translate them to contributor IDs
    global $user_ids_to_contributor_usernames;
    $image_fields = array($kind.'_image_editor', $kind.'_image_editor2');
    foreach ($image_fields AS $image_field){
        if (!isset($cleaned_json_data[$image_field])){ continue; }
        if (!empty($cleaned_json_data[$image_field])){
            $user_id = $cleaned_json_data[$image_field];
            if (!empty($user_ids_to_contributor_usernames[$user_id])){
                $contributor_name = $user_ids_to_contributor_usernames[$user_id];
                $cleaned_json_data[$image_field] = $contributor_name;
            }
        } else {
            $cleaned_json_data[$image_field] = '';
        }
    }
    // Return the cleaned JSON data
    return $cleaned_json_data;
}

// Define a function for getting a table definition for export to an SQL file
function mmrpg_get_create_table_sql($table_name, $table_settings, $use_prod_if_available = false){
    if (!isset($table_settings['export_table']) || $table_settings['export_table'] !== true){ return false; }
    global $db, $table_def_sql_template;
    $table_name_string = "`{$table_name}`";
    if ($use_prod_if_available
        && defined('MMRPG_CONFIG_PULL_LIVE_DATA_FROM')
        && MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== false){
        $prod_db_name = 'mmrpg_'.(defined('MMRPG_CONFIG_IS_LIVE') && MMRPG_CONFIG_IS_LIVE === true ? 'live' : 'local');
        $prod_db_name .= '_'.MMRPG_CONFIG_PULL_LIVE_DATA_FROM;
        $table_name_string = "`{$prod_db_name}`.{$table_name_string}";
        }
    $table_def_sql = $db->get_value("SHOW CREATE TABLE {$table_name_string};", 'Create Table');
    $table_def_sql = preg_replace('/(\s+AUTO_INCREMENT)=(?:[0-9]+)(\s+)/', '$1=0$2', $table_def_sql);
    $table_def_sql = preg_replace('/^CREATE TABLE `/', 'CREATE TABLE IF NOT EXISTS `', $table_def_sql);
    $table_def_sql = rtrim($table_def_sql, ';').';';
    $final_table_def_sql = $table_def_sql_template;
    $final_table_def_sql = str_replace('{{TABLE_NAME}}', $table_name, $final_table_def_sql);
    $final_table_def_sql = str_replace('{{TABLE_DEF_SQL}}', $table_def_sql, $final_table_def_sql);
    return $final_table_def_sql;
}

// Define a function for getting a table definition for export to an SQL file
function mmrpg_get_insert_table_data_sql($table_name, $table_settings, $use_prod_if_available = false){
    if (!isset($table_settings['export_data']) || $table_settings['export_data'] !== true){ return false; }
    global $db, $table_row_sql_template;
    $table_name_string = "`{$table_name}`";
    if ($use_prod_if_available
        && defined('MMRPG_CONFIG_PULL_LIVE_DATA_FROM')
        && MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== false){
        $prod_db_name = 'mmrpg_'.(defined('MMRPG_CONFIG_IS_LIVE') && MMRPG_CONFIG_IS_LIVE === true ? 'live' : 'local');
        $prod_db_name .= '_'.MMRPG_CONFIG_PULL_LIVE_DATA_FROM;
        $table_name_string = "`{$prod_db_name}`.{$table_name_string}";
        }
    $select_query = "SELECT * FROM {$table_name_string};";
    if (!empty($table_settings['export_filter'])){
        $row_filter = array('1=1');
        $filters = $table_settings['export_filter'];
        foreach ($filters AS $fkey => $fval){ $row_filter[] = "{$fkey} = ".(is_numeric($fval) ? $fval : "'{$fval}'"); }
        $row_filter = implode(' AND ', $row_filter);
        $select_query = str_replace(';', " WHERE {$row_filter};", $select_query);
    }
    $table_rows = $db->get_array_list($select_query);
    if (empty($table_rows)){ return false; }
    $table_rows_sql = $db->get_bulk_insert_sql($table_name, $table_rows);
    if (empty($table_rows_sql)){ return false; }
    $final_table_rows_sql = $table_row_sql_template;
    $final_table_rows_sql = str_replace('{{TABLE_NAME}}', $table_name, $final_table_rows_sql);
    $final_table_rows_sql = str_replace('{{TABLE_ROWS_SQL}}', $table_rows_sql, $final_table_rows_sql);
    return $final_table_rows_sql;
}

// Define a function for getting sample table data (if exists) for export to an SQL file
$sql_sample_data_base = MMRPG_CONFIG_ROOTDIR.'admin/.setup/sample-data/';
function mmrpg_get_sample_table_data_sql($table_name, $table_settings){
    if (!isset($table_settings['sample_data']) || $table_settings['sample_data'] !== true){ return false; }
    global $sql_sample_data_base;
    $sample_data_path = $sql_sample_data_base.$table_name.'.sql';
    if (!file_exists($sample_data_path)){ return false; }
    $table_rows_sql = file_get_contents($sample_data_path);
    return $table_rows_sql;
}

// Define templates for use with the SQL export functions
$table_def_sql_template = <<<'XSQL'
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

{{TABLE_DEF_SQL}}

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
XSQL;
$table_row_sql_template = <<<'XSQL'
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40000 ALTER TABLE `{{TABLE_NAME}}` DISABLE KEYS */;
{{TABLE_ROWS_SQL}}
/*!40000 ALTER TABLE `{{TABLE_NAME}}` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
XSQL;


?>