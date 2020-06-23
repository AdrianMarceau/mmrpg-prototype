<?



// Define a quick function for immediately printing an echo statement
function ob_echo($echo, $silent = false){ if (!$silent){ echo($echo.PHP_EOL); } ob_flush(); }

// Define a function for cleaning a path of the root dir for printing
function clean_path($path){ return str_replace(MMRPG_CONFIG_ROOTDIR, '/', $path); }

// Define a quick function for copying rpg object sprites from one directory to another
function copy_sprites_to_new_dir($base_token, $count_string, $new_sprite_path, $exclude_sprites = array(), $delete_existing = true, $silent_mode = false){
    global $migration_kind, $migration_kind_singular, $migration_limit;
    $kind = $migration_kind_singular;
    $kind_plural = $migration_kind;
    ob_echo('----------', $silent_mode);
    ob_echo('Processing '.$kind.' sprites for "'.$base_token.'" '.$count_string, $silent_mode);
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
        'data' => implode(PHP_EOL, $data_markup_array),
        'functions' => implode(PHP_EOL, $functions_markup_array)
        );
}

// Define a function for generating empty object data vs functions files
function get_empty_functions_file_markup($kind){
    $empty_file_markup = $GLOBALS['empty_function_file_markup'];
    return str_replace('{{kind}}', $kind, $empty_file_markup);
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
function clean_json_content_array($kind, $content_json_data){
    // Make a copy of the origin al JSON data
    $cleaned_json_data = $content_json_data;
    // Remove any known unnecessary or deprecated fields from the data
    unset($cleaned_json_data[$kind.'_id']);
    unset($cleaned_json_data[$kind.'_functions']);
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
    // Return the cleaned JSON data
    return $cleaned_json_data;
}

?>