<?

ob_echo('');
ob_echo('=============================');
ob_echo('|  START ROBOT MIGRATION  |');
ob_echo('=============================');
ob_echo('');

// Predefine any deprecated robots or robot sprites so we can ignore them
$deprecated_robots = array(
    'robot2',
    'test-man', 'test-woman',
    'ageman20xx', 'megabossman', 'rhythmbca'
    );

// Collect an index of all valid robots from the database
$robot_fields = rpg_robot::get_index_fields(false);
if (!in_array('robot_functions', $robot_fields)){ $robot_fields[] = 'robot_functions'; }
if (!in_array('robot_group', $robot_fields)){ $robot_fields[] = 'robot_group'; }
if (!in_array('robot_order', $robot_fields)){ $robot_fields[] = 'robot_order'; }
$robot_fields = implode(', ', $robot_fields);
$robot_index = $db->get_array_list("SELECT {$robot_fields} FROM mmrpg_index_robots ORDER BY robot_token ASC", 'robot_token');

// If there's a filter present, remove all tokens not in the filter
if (!empty($migration_filter)){
    $old_robot_index = $robot_index;
    $robot_index = array();
    foreach ($migration_filter AS $robot_token){
        if (isset($old_robot_index[$robot_token])){
            $robot_index[$robot_token] = $old_robot_index[$robot_token];
        }
    }
    unset($old_robot_index);
}

// Pre-define the base robot image dir and the new robot content dir
define('MMRPG_ROBOTS_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/robots/');
if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/')){ define('MMRPG_ROBOTS_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/robots/'); }
elseif (file_exists(MMRPG_CONFIG_ROOTDIR.'images/xxx_robots/')){ define('MMRPG_ROBOTS_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/xxx_robots/'); }
else { exit('Required directory /images/robots/ does not exist!'); }
define('MMRPG_ROBOTS_OLD_SHADOW_IMAGES_DIR', rtrim(MMRPG_ROBOTS_OLD_IMAGES_DIR, '/').'_shadows/');

// Pre-collect an index of robot sprite alts so we don't have to scan each later
$robot_sprites_list = scandir(MMRPG_ROBOTS_OLD_IMAGES_DIR);
$robot_sprites_list = array_filter($robot_sprites_list, function($s){ if ($s !== '.' && $s !== '..' && substr($s, 0, 1) !== '.'){ return true; } else { return false; } });
//echo('$robot_sprites_list = '.print_r($robot_sprites_list, true));

// Pre-collect an index of robot shadow alts so we don't have to scan each later
$robot_shadows_list = scandir(MMRPG_ROBOTS_OLD_SHADOW_IMAGES_DIR);
$robot_shadows_list = array_filter($robot_shadows_list, function($s){ if ($s !== '.' && $s !== '..' && substr($s, 0, 1) !== '.'){ return true; } else { return false; } });
//echo('$robot_shadows_list = '.print_r($robot_shadows_list, true));

// Manually remove deprecated robots from the sprite and index lists
foreach ($deprecated_robots AS $token){
    $rm_key = array_search($token, $robot_sprites_list);
    if ($rm_key !== false){ unset($robot_sprites_list[$rm_key]); }
    $rm_key = array_search($token, $robot_shadows_list);
    if ($rm_key !== false){ unset($robot_shadows_list[$rm_key]); }
    if (isset($robot_index[$token])){ unset($robot_index[$token]); }
    if (file_exists(MMRPG_ROBOTS_NEW_CONTENT_DIR.$token.'/')){
        deletedir_or_exit(MMRPG_ROBOTS_NEW_CONTENT_DIR.$token.'/');
    }
}

// Loop through sprites and pre-collect any alts for later looping
$robot_sprites_alts_list = array();
foreach ($robot_sprites_list AS $key => $token){
    if (strstr($token, '_')){
        list($token1, $token2) = explode('_', $token);
        if (!isset($robot_sprites_alts_list[$token1])){ $robot_sprites_alts_list[$token1] = array(); }
        $robot_sprites_alts_list[$token1][] = $token2;
    }
}
//echo('$robot_sprites_alts_list = '.print_r($robot_sprites_alts_list, true));
//exit();

// Loop through shadows and pre-collect any alts for later looping
$robot_shadows_alts_list = array();
foreach ($robot_shadows_list AS $key => $token){
    if (strstr($token, '_')){
        list($token1, $token2) = explode('_', $token);
        if (!isset($robot_shadows_alts_list[$token1])){ $robot_shadows_alts_list[$token1] = array(); }
        $robot_shadows_alts_list[$token1][] = $token2;
    }
}
//echo('$robot_sprites_alts_list = '.print_r($robot_sprites_alts_list, true));
//exit();

// Predefine a size-agnostic whitelist of sprites we can copy over for robots
$robot_sprite_whitelist = array(
    'mug_left_{{size}}.png', 'mug_right_{{size}}.png',
    'sprite_left_{{size}}.png', 'sprite_right_{{size}}.png'
    );

// Count the number of robots that we'll be looping through
$robot_index_size = count($robot_index);
$robot_sprite_directories_total = count($robot_sprites_list);
$robot_shadow_directories_total = count($robot_shadows_list);
$count_pad_length = strlen($robot_index_size);

// Print out the stats before we start
ob_echo('Total Robots in Database: '.$robot_index_size);
ob_echo('Total Sprites in ImageDir: '.$robot_sprite_directories_total);
ob_echo('Total Shadows in ImageDir: '.$robot_shadow_directories_total);
ob_echo('');

sleep(1);

$robot_data_files_copied = array();
$robot_image_directories_copied = array();
$robot_shadow_image_directories_copied = array();

// MIGRATE ACTUAL ROBOTS
$robot_key = -1; $robot_num = 0;
foreach ($robot_index AS $robot_token => $robot_data){
    $robot_key++; $robot_num++;
    $count_string = '('.$robot_num.' of '.$robot_index_size.')';

    ob_echo('----------');
    ob_echo('Processing robot data and sprites "'.$robot_token.'" '.$count_string);
    ob_flush();

    $content_path = MMRPG_ROBOTS_NEW_CONTENT_DIR.($robot_token === 'robot' ? '.robot' : $robot_token).'/';
    //ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deletedir_or_exit($content_path); }
    mkdir_or_exit($content_path);

    // Generate a whitelist of sprites we can copy over for this robot
    $base_size = !empty($robot_data['robot_image_size']) ? $robot_data['robot_image_size'] : 40;
    $zoom_size = $base_size * 2;
    $sprite_whitelist = array();
    foreach ($robot_sprite_whitelist AS $sprite){
        $sprite_whitelist[] = str_replace('{{size}}', $base_size.'x'.$base_size, $sprite);
        $sprite_whitelist[] = str_replace('{{size}}', $zoom_size.'x'.$zoom_size, $sprite);
    }

    $sprite_path = MMRPG_ROBOTS_OLD_IMAGES_DIR.$robot_token.'/';
    //ob_echo('-- $sprite_path = '.clean_path($sprite_path));

    $shadow_path = MMRPG_ROBOTS_OLD_SHADOW_IMAGES_DIR.$robot_token.'/';
    //ob_echo('-- $shadow_path = '.clean_path($shadow_path));

    // Ensure the base sprite exists first and copy if so
    if (file_exists($sprite_path)){
        $content_images_path = $content_path.'sprites/';
        if (file_exists($content_images_path)){ deletedir_or_exit($content_images_path); }
        mkdir_or_exit($content_images_path);
        ob_echo('- copy '.clean_path($sprite_path).'* to '.clean_path($content_images_path));
        recurseCopyWithWhitelist($sprite_path, $content_images_path, $sprite_whitelist);
        $robot_image_directories_copied[] = basename($sprite_path);
        // Ensure the shadow sprite exists first and copy if so
        if (file_exists($shadow_path)){
            $content_shadow_images_path = $content_path.'shadows/';
            if (file_exists($content_shadow_images_path)){ deletedir_or_exit($content_shadow_images_path); }
            mkdir_or_exit($content_shadow_images_path);
            ob_echo('- copy '.clean_path($shadow_path).'* to '.clean_path($content_shadow_images_path));
            recurseCopyWithWhitelist($shadow_path, $content_shadow_images_path, $sprite_whitelist);
            $robot_shadow_image_directories_copied[] = basename($shadow_path);
        }
    }

    // Loop through and copy any named alts for this sprite as well
    if (isset($robot_sprites_alts_list[$robot_token])){
        $alt_tokens = $robot_sprites_alts_list[$robot_token];
        foreach ($alt_tokens AS $akey => $atoken){
            // Ensure the sub sprite exists before attempting to copy
            $sub_sprite_path = rtrim($sprite_path, '/').'_'.$atoken.'/';
            if (file_exists($sub_sprite_path)){
                $sub_content_images_path = rtrim($content_images_path, '/').'_'.$atoken.'/';
                if (file_exists($sub_content_images_path)){ deletedir_or_exit($sub_content_images_path); }
                mkdir_or_exit($sub_content_images_path);
                ob_echo('-- copy '.clean_path($sub_sprite_path).'* to '.clean_path($sub_content_images_path));
                recurseCopyWithWhitelist($sub_sprite_path, $sub_content_images_path, $sprite_whitelist);
                $robot_image_directories_copied[] = basename($sub_sprite_path);
                // Ensure the sub shadow exists before attempting to copy
                $sub_shadow_path = rtrim($shadow_path, '/').'_'.$atoken.'/';
                if (file_exists($sub_shadow_path)){
                    $sub_content_shadow_images_path = rtrim($content_shadow_images_path, '/').'_'.$atoken.'/';
                    if (file_exists($sub_content_shadow_images_path)){ deletedir_or_exit($sub_content_shadow_images_path); }
                    mkdir_or_exit($sub_content_shadow_images_path);
                    ob_echo('-- copy '.clean_path($sub_shadow_path).'* to '.clean_path($sub_content_shadow_images_path));
                    recurseCopyWithWhitelist($sub_shadow_path, $sub_content_shadow_images_path, $sprite_whitelist);
                    $robot_shadow_image_directories_copied[] = basename($sub_shadow_path);
                    }
                } else {
                break;
                }
            }
        }

    $function_path = rtrim(dirname($robot_data['robot_functions']), '/').'/';
    //ob_echo('-- $function_path = '.$function_path);
    $data_path = MMRPG_MIGRATE_OLD_DATA_DIR.$function_path.$robot_token.'.php';
    //ob_echo('-- $data_path = '.clean_path($data_path));

    // Ensure the data file exists before attempting to extract functions from it
    if (true){
        $functions_file_markup = get_empty_functions_file_markup('robot');
        if (!empty($functions_file_markup)){
            $content_data_path = $content_path.'functions.php';
            ob_echo('- extract '.clean_path($data_path).' functions into '.clean_path($content_data_path));
            $h = fopen($content_data_path, 'w');
            fwrite($h, $functions_file_markup);
            fclose($h);
        }
        $robot_data_files_copied[] = basename($data_path); // not actually copied but here for tracking
    }

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('robot', $robot_data);
    ob_echo('- export all other data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, normalize_file_markup(json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)));
    fclose($h);

    if ($migration_limit && $robot_num >= $migration_limit){ break; }

}

// Delete directories that we don't want kept around
$dot_robot_content_dir = MMRPG_ROBOTS_NEW_CONTENT_DIR.'.robot/';
if (file_exists($dot_robot_content_dir.'sprites_legacy/')){ deletedir_or_exit($dot_robot_content_dir.'sprites_legacy/'); }
if (file_exists($dot_robot_content_dir.'shadows_legacy/')){ deletedir_or_exit($dot_robot_content_dir.'shadows_legacy/'); }

ob_echo('----------');

ob_echo('');
ob_echo_nobreak('Generating robot groups data file... ');
$object_groups = cms_admin::generate_object_groups_from_index($robot_index, 'robot');
cms_admin::save_object_groups_to_json($object_groups, 'robot');
ob_echo('...done!');
ob_echo('');

ob_echo('----------');

$robot_image_directories_copied = array_unique($robot_image_directories_copied);

ob_echo('');
ob_echo('Robot Data Files Copied: '.count($robot_data_files_copied).' / '.$robot_index_size);
ob_echo('Robot Image Directories Copied: '.count($robot_image_directories_copied).' / '.$robot_sprite_directories_total);
ob_echo('Robot Shadow Image Directories Copied: '.count($robot_shadow_image_directories_copied).' / '.$robot_shadow_directories_total);
if (!($migration_limit > 0) && empty($migration_filter)){
    ob_echo('');
    ob_echo('Robot Images Not Copied: '.print_r(array_diff($robot_sprites_list, $robot_image_directories_copied), true));
    ob_echo('Robot Shadows Not Copied: '.print_r(array_diff($robot_shadows_list, $robot_shadow_image_directories_copied), true));
}
//ob_echo('$robot_sprites_list: '.print_r($robot_sprites_list, true));
//ob_echo('$robot_shadows_list: '.print_r($robot_shadows_list, true));
//b_echo('$robot_image_directories_copied: '.print_r($robot_image_directories_copied, true));

sleep(1);

ob_echo('');
ob_echo('=============================');
ob_echo('|   END ROBOT MIGRATION     |');
ob_echo('=============================');
ob_echo('');

?>