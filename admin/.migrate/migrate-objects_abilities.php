<?

ob_echo('');
ob_echo('=============================');
ob_echo('|  START ABILITY MIGRATION  |');
ob_echo('=============================');
ob_echo('');

// Predefine any deprecated abilities or ability sprites so we can ignore them
$deprecated_abilities = array(
    'attachment-defeat', 'struggle-charge', 'fullscreen-black',
    '_bubble-lead_legacy', '_jewel-satellite-legacy',
    'attack-blaze', 'attack-burn',
    'defense-blaze', 'defense-burn',
    'speed-blaze', 'speed-burn',
    'time-stopper',
    );

// Collect an index of all valid abilities from the database
$ability_fields = rpg_ability::get_index_fields(false);
if (!in_array('ability_functions', $ability_fields)){ $ability_fields[] = 'ability_functions'; }
$ability_fields = implode(', ', $ability_fields);
$ability_index = $db->get_array_list("SELECT {$ability_fields} FROM mmrpg_index_abilities ORDER BY ability_token ASC", 'ability_token');

// If there's a filter present, remove all tokens not in the filter
if (!empty($migration_filter)){
    $old_ability_index = $ability_index;
    $ability_index = array();
    foreach ($migration_filter AS $ability_token){
        if (isset($old_ability_index[$ability_token])){
            $ability_index[$ability_token] = $old_ability_index[$ability_token];
        }
    }
    unset($old_ability_index);
}

// Pre-define the base ability image dir and the new ability content dir
define('MMRPG_ABILITIES_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/abilities/');
if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/abilities/')){ define('MMRPG_ABILITIES_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/abilities/'); }
elseif (file_exists(MMRPG_CONFIG_ROOTDIR.'images/xxx_abilities/')){ define('MMRPG_ABILITIES_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/xxx_abilities/'); }
else { exit('Required directory /images/abilities/ does not exist!'); }

// Pre-collect an index of ability alts so we don't have to scan each later
$ability_sprites_list = scandir(MMRPG_ABILITIES_OLD_IMAGES_DIR);
$ability_sprites_list = array_filter($ability_sprites_list, function($s){ if ($s !== '.' && $s !== '..'){ return true; } else { return false; } });
//echo('$ability_sprites_list = '.print_r($ability_sprites_list, true));

// Manually remove deprecated abilities from the sprite and index lists
foreach ($deprecated_abilities AS $token){
    $rm_key = array_search($token, $ability_sprites_list);
    if ($rm_key !== false){ unset($ability_sprites_list[$rm_key]); }
    if (isset($ability_index[$token])){ unset($ability_index[$token]); }
}

// Loop through sprites and pre-collect any alts for later looping
$ability_sprites_alts_list = array();
foreach ($ability_sprites_list AS $key => $token){
    if (strstr($token, '_')){
        list($token1, $token2) = explode('_', $token);
        if (!isset($ability_sprites_alts_list[$token1])){ $ability_sprites_alts_list[$token1] = array(); }
        $ability_sprites_alts_list[$token1][] = $token2;
    }
}
//echo('$ability_sprites_alts_list = '.print_r($ability_sprites_alts_list, true));
//exit();

// Predefine the icon sprite and sprite-sprite filenames now
$icon_sprite_filenames = array('icon_left_40x40.png', 'icon_right_40x40.png', 'icon_left_80x80.png', 'icon_right_80x80.png');
$sprite_sprite_filenames = array('sprite_left_40x40.png', 'sprite_right_40x40.png', 'sprite_left_80x80.png', 'sprite_right_80x80.png');

// Pre-create special effect directory for later
$special_ability_dirs = array();
$special_ability_dirs[] = $special_effect_abilities_dir = MMRPG_ABILITIES_NEW_CONTENT_DIR.'_effects/';
foreach ($special_ability_dirs AS $special_ability_dir){
    //ob_echo('-- $special_ability_dir = '.clean_path($special_ability_dir));
    if (empty($migration_filter) && file_exists($special_ability_dir)){ deletedir_or_exit($special_ability_dir); }
    if (!file_exists($special_ability_dir)){ mkdir_or_exit($special_ability_dir); }
}

// Count the number of abilities that we'll be looping through
$ability_index_size = count($ability_index);
$ability_sprite_directories_total = count($ability_sprites_list);
$count_pad_length = strlen($ability_index_size);

// Print out the stats before we start
ob_echo('Total Abilities in Database: '.$ability_index_size);
ob_echo('Total Sprites in ImageDir: '.$ability_sprite_directories_total);
ob_echo('');

sleep(1);

$ability_data_files_copied = array();
$ability_image_directories_copied = array();

// MIGRATE ACTUAL ABILITIES
$ability_key = -1; $ability_num = 0;
foreach ($ability_index AS $ability_token => $ability_data){
    $ability_key++; $ability_num++;
    $count_string = '('.$ability_num.' of '.$ability_index_size.')';

    ob_echo('----------');
    ob_echo('Processing ability data and sprites "'.$ability_token.'" '.$count_string);
    ob_flush();

    $function_path = rtrim(dirname($ability_data['ability_functions']), '/').'/';
    //ob_echo('-- $function_path = '.$function_path);

    $sprite_path = MMRPG_ABILITIES_OLD_IMAGES_DIR.$ability_token.'/';
    //ob_echo('-- $sprite_path = '.clean_path($sprite_path));
    $data_path = MMRPG_MIGRATE_OLD_DATA_DIR.$function_path.$ability_token.'.php';
    //ob_echo('-- $data_path = '.clean_path($data_path));

    $content_path = MMRPG_ABILITIES_NEW_CONTENT_DIR.($ability_token === 'ability' ? '.ability' : $ability_token).'/';
    //ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deletedir_or_exit($content_path); }
    mkdir_or_exit($content_path);

    // Ensure the base sprite exists first and copy if so
    if (file_exists($sprite_path)){
        $content_images_path = $content_path.'sprites/';
        if (file_exists($content_images_path)){ deletedir_or_exit($content_images_path); }
        mkdir_or_exit($content_images_path);
        ob_echo('- copy '.clean_path($sprite_path).'* to '.clean_path($content_images_path));
        recurseCopy($sprite_path, $content_images_path);
        $ability_image_directories_copied[] = basename($sprite_path);
    }

    // Check if there's a "b" version of this base sprite
    $sub_sprite_path = rtrim($sprite_path, '/').'-b/';
    if (file_exists($sub_sprite_path)){
        $sub_content_images_path = rtrim($content_images_path, '/').'_'.$i.'/';
        if (file_exists($sub_content_images_path)){ deletedir_or_exit($sub_content_images_path); }
        mkdir_or_exit($sub_content_images_path);
        ob_echo('-- copy '.clean_path($sub_sprite_path).'* to '.clean_path($sub_content_images_path));
        recurseCopy($sub_sprite_path, $sub_content_images_path);
        $ability_image_directories_copied[] = basename($sub_sprite_path);
        }

    // loop through and copy any eligible sub sprites
    for ($i = 1; $i < 99; $i++){
        $sub_sprite_path = rtrim($sprite_path, '/').'-'.$i.'/';
        if (file_exists($sub_sprite_path)){
            $sub_content_images_path = rtrim($content_images_path, '/').'_'.$i.'/';
            if (file_exists($sub_content_images_path)){ deletedir_or_exit($sub_content_images_path); }
            mkdir_or_exit($sub_content_images_path);
            ob_echo('-- copy '.clean_path($sub_sprite_path).'* to '.clean_path($sub_content_images_path));
            recurseCopy($sub_sprite_path, $sub_content_images_path);
            $ability_image_directories_copied[] = basename($sub_sprite_path);
            // Check if there's a "b" version of this sub sprite
            $sub_sub_sprite_path = rtrim($sub_sprite_path, '/').'-b/';
            if (file_exists($sub_sub_sprite_path)){
                $sub_sub_content_images_path = rtrim($sub_content_images_path, '/').'-'.$i.'/';
                if (file_exists($sub_sub_content_images_path)){ deletedir_or_exit($sub_sub_content_images_path); }
                mkdir_or_exit($sub_sub_content_images_path);
                ob_echo('-- copy '.clean_path($sub_sub_sprite_path).'* to '.clean_path($sub_sub_content_images_path));
                recurseCopy($sub_sub_sprite_path, $sub_sub_content_images_path);
                $ability_image_directories_copied[] = basename($sub_sub_sprite_path);
                }
            } else {
            if ($i > 1){ break; }
            }
        }

    // Loop through and copy any named alts for this sprite as well
    if (isset($ability_sprites_alts_list[$ability_token])){
        $alt_tokens = $ability_sprites_alts_list[$ability_token];
        foreach ($alt_tokens AS $akey => $atoken){
            $sub_sprite_path = rtrim($sprite_path, '/').'_'.$atoken.'/';
            if (file_exists($sub_sprite_path)){
                $sub_content_images_path = rtrim($content_images_path, '/').'_'.$atoken.'/';
                if (file_exists($sub_content_images_path)){ deletedir_or_exit($sub_content_images_path); }
                mkdir_or_exit($sub_content_images_path);
                ob_echo('-- copy '.clean_path($sub_sprite_path).'* to '.clean_path($sub_content_images_path));
                recurseCopy($sub_sprite_path, $sub_content_images_path);
                $ability_image_directories_copied[] = basename($sub_sprite_path);
                } else {
                break;
                }
            }
        }

    // Ensure the data file exists before attempting to extract functions from it
    if (file_exists($data_path)){
        $split_markup = get_parsed_object_file_markup($data_path);
        /* if (!empty($split_markup['data'])){
            $content_data_path = $content_path.'data.php';
            ob_echo('- extract '.clean_path($data_path).' into '.clean_path($content_data_path));
            $h = fopen($content_data_path, 'w');
            fwrite($h, $split_markup['data']);
            fclose($h);
        } */
        if (!empty($split_markup['functions'])){
            $content_data_path = $content_path.'functions.php';
            ob_echo('- extract '.clean_path($data_path).' functions into '.clean_path($content_data_path));
            $h = fopen($content_data_path, 'w');
            fwrite($h, $split_markup['functions']);
            fclose($h);
        }
        $ability_data_files_copied[] = basename($data_path);
    }

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('ability', $ability_data);
    ob_echo('- export all other data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
    fclose($h);

    if ($migration_limit && $ability_num >= $migration_limit){ break; }

}



// MIGRATE OTHER ABILITIES

// Only migrate other abilities if a filter isn't present
if (empty($migration_filter)){

    // Define the objects base path
    $object_base_path = MMRPG_CONFIG_ROOTDIR.'images/objects/';

    // Migrate the various effect sprites into the "_effects" directory instead
    ob_echo('----------');
    $copy_effect_sprites = array();
    $copy_effect_sprites[] = array('ability-effect_black-overlay', $special_effect_abilities_dir.'black-overlay/');
    $copy_effect_sprites[] = array('bright-burst-2', $special_effect_abilities_dir.'yellow-overlay/');
    $copy_effect_sprites[] = array('ability-results', $special_effect_abilities_dir.'stat-arrows/');
    $copy_effect_sprites[] = array('field-support', $special_effect_abilities_dir.'type-arrows_boost-1/');
    $copy_effect_sprites[] = array('field-support-2', $special_effect_abilities_dir.'type-arrows_boost-2/');
    $copy_effect_sprites[] = array('field-support-3', $special_effect_abilities_dir.'type-arrows_break-1/');
    $copy_effect_sprites[] = array('field-support-4', $special_effect_abilities_dir.'type-arrows_break-2/');
    foreach ($copy_effect_sprites AS $copy_key => $copy_info){
        list($sprite_token, $export_path) = $copy_info;
        copy_sprites_to_new_dir(
            $sprite_token,
            '('.($copy_key + 1).' of '.count($copy_effect_sprites).')',
            $export_path,
            $icon_sprite_filenames
            );
        copy_sprites_to_new_dir('ability', '', $export_path, $sprite_sprite_filenames, false, true);
        if ($migration_limit && ($copy_key + 1) >= $migration_limit){ break; }
    }

    // Delete migrated ability effects if they've been copied over properly
    if (file_exists(MMRPG_ABILITIES_NEW_CONTENT_DIR.'bright-burst/sprites_2/')){ // if original exists
        if (file_exists($special_effect_abilities_dir.'yellow-overlay/')){ // but has been copied
            ob_echo('----------');
            ob_echo('Removing redundant sprites for bright-burst ability');
            ob_echo('- delete '.clean_path(MMRPG_ABILITIES_NEW_CONTENT_DIR.'bright-burst/sprites_2/').'*');
            deletedir_or_exit(MMRPG_ABILITIES_NEW_CONTENT_DIR.'bright-burst/sprites_2/'); // delete the original
        }
    }

    // Overwrite redundant field booster sprites, overwrite base ones with empty images
    if (file_exists(MMRPG_ABILITIES_NEW_CONTENT_DIR.'field-support/')){
        $flag_print_removed = false;
        for ($i = 1; $i <= 4; $i++){
            $sprites_path = $i > 1 ? 'sprites_'.$i : 'sprites';
            if (!file_exists(MMRPG_ABILITIES_NEW_CONTENT_DIR.'field-support/'.$sprites_path.'/')){ continue; }
            if (!$flag_print_removed){
                ob_echo('----------');
                ob_echo('Removing redundant sprites for field-support ability');
                $flag_print_removed = true;
            }
            ob_echo('- delete '.clean_path(MMRPG_ABILITIES_NEW_CONTENT_DIR.'field-support/'.$sprites_path.'/').'*');
            deletedir_or_exit(MMRPG_ABILITIES_NEW_CONTENT_DIR.'field-support/'.$sprites_path.'/');
        }
        if (!$flag_print_removed){
            ob_echo('----------');
            ob_echo('Manually copying required sprites for field-support ability');
        } else {
            ob_echo('And replacing with required sprites from other directories');
        }
        $export_path = MMRPG_ABILITIES_NEW_CONTENT_DIR.'field-support/sprites/';
        copy_sprites_to_new_dir('field-support', '', $export_path, $sprite_sprite_filenames, false, false, true);
        copy_sprites_to_new_dir('mecha-support', '', $export_path, $icon_sprite_filenames, false, false, true);
    }

}


ob_echo('----------');

$ability_image_directories_copied = array_unique($ability_image_directories_copied);

ob_echo('');
ob_echo('Ability Data Files Copied: '.count($ability_data_files_copied).' / '.$ability_index_size);
ob_echo('Ability Image Directories Copied: '.count($ability_image_directories_copied).' / '.$ability_sprite_directories_total);
if (!($migration_limit > 0) && empty($migration_filter)){
    ob_echo('');
    ob_echo('Ability Images Not Copied: '.print_r(array_diff($ability_sprites_list, $ability_image_directories_copied), true));
}
//ob_echo('$ability_sprites_list: '.print_r($ability_sprites_list, true));
//b_echo('$ability_image_directories_copied: '.print_r($ability_image_directories_copied, true));

sleep(1);

ob_echo('');
ob_echo('=============================');
ob_echo('|   END ABILITY MIGRATION   |');
ob_echo('=============================');
ob_echo('');

?>