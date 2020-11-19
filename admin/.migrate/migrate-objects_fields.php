<?

ob_echo('');
ob_echo('=============================');
ob_echo('|  START FIELD MIGRATION  |');
ob_echo('=============================');
ob_echo('');

// Predefine any deprecated fields or field sprites so we can ignore them
$deprecated_fields = array(
    );

// Define the table name we'll be using to pull this data
$table_name = 'mmrpg_index_fields';
$table_name_string = "`{$table_name}`";
if (defined('MMRPG_CONFIG_PULL_LIVE_DATA_FROM')
    && MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== false){
    $prod_db_name = 'mmrpg_'.(defined('MMRPG_CONFIG_IS_LIVE') && MMRPG_CONFIG_IS_LIVE === true ? 'live' : 'local');
    $prod_db_name .= '_'.MMRPG_CONFIG_PULL_LIVE_DATA_FROM;
    $table_name_string = "`{$prod_db_name}`.{$table_name_string}";
    }

// Collect an index of all valid fields from the database
$field_fields = rpg_field::get_index_fields();
if (!in_array('field_functions', $field_fields)){ $field_fields[] = 'field_functions'; }
if (!in_array('field_group', $field_fields)){ $field_fields[] = 'field_group'; }
if (!in_array('field_order', $field_fields)){ $field_fields[] = 'field_order'; }
$field_fields = implode(', ', $field_fields);
$field_index = $db->get_array_list("SELECT {$field_fields} FROM {$table_name_string} ORDER BY field_token ASC", 'field_token');

// If there's a filter present, remove all tokens not in the filter
if (!empty($migration_filter)){
    $old_field_index = $field_index;
    $field_index = array();
    foreach ($migration_filter AS $field_token){
        if (isset($old_field_index[$field_token])){
            $field_index[$field_token] = $old_field_index[$field_token];
        }
    }
    unset($old_field_index);
}

// Pre-define the base field image dir and the new field content dir
define('MMRPG_FIELDS_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/fields/');
if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/fields/')){ define('MMRPG_FIELDS_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/fields/'); }
elseif (file_exists(MMRPG_CONFIG_ROOTDIR.'images/xxx_fields/')){ define('MMRPG_FIELDS_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/xxx_fields/'); }
else { exit('Required directory /images/fields/ does not exist!'); }

// Pre-collect an index of field images and objects so we don't have to scan each later
$field_sprites_list = scandir(MMRPG_FIELDS_OLD_IMAGES_DIR);
$field_sprites_list = array_filter($field_sprites_list, function($s){ if ($s !== '.' && $s !== '..' && substr($s, 0, 1) !== '.'){ return true; } else { return false; } });
//echo('$field_sprites_list = '.print_r($field_sprites_list, true));

// Manually remove deprecated fields from the sprite and index lists
foreach ($deprecated_fields AS $token){
    $rm_key = array_search($token, $field_sprites_list);
    if ($rm_key !== false){ unset($field_sprites_list[$rm_key]); }
    if (isset($field_index[$token])){ unset($field_index[$token]); }
}

// Loop through sprites and pre-collect any objects for later looping
$field_sprites_objects_list = array();
foreach ($field_sprites_list AS $key => $token){
    if (strstr($token, '_')){
        list($token1, $token2) = explode('_', $token);
        if (!isset($field_sprites_objects_list[$token1])){ $field_sprites_objects_list[$token1] = array(); }
        $field_sprites_objects_list[$token1][] = $token2;
    }
}
//echo('$field_sprites_objects_list = '.print_r($field_sprites_objects_list, true));
//exit();

// Predefine a whitelist of sprites we can copy over for the fields
$field_sprite_whitelist = array(
    'battle-field_avatar.png',
    'battle-field_background_base.gif', 'battle-field_background_base.png',
    'battle-field_foreground_base.png',
    'battle-field_preview.png'
    );

// Predefine a conversion array for legacy-to-new music tokens
$music_conversation_table = array(
    'field' => '', // (Unknown)
    // ---
    'intro-field' => 'sega-remix/boss-theme-mm1', // (Boss Theme (Sega Genesis))
    // ---
    'clock-citadel' => 'sega-remix/time-man-mmpu', // (MM&B Astro Man (Sega Genesis))
    'oil-wells' => 'sega-remix/tengu-man-rnf', // (MM&B Tengu Man (Sega Genesis))
    // ---
    'light-laboratory' => 'sega-remix/special-stage-mm9', // (MM9 Special Stage (Sega Genesis))
    'wily-castle' => 'sega-remix/wily-fortress-1-mm1', // (MM1 Wily Fortress 1 (Sega Genesis))
    'cossack-citadel' => 'sega-remix/cossack-fortress-2-mm4', // (MM4 Cossack Fortess 2 (Sega Genesis))
    // ---
    'final-destination' => 'sega-remix/special-stage-mm9', // (MM10 Special (Sega Genesis))
    'final-destination-2' => 'sega-remix/wily-fortress-1-mm9', // (MM9 Wily Fortress (Sega Genesis))
    'final-destination-3' => 'sega-remix/wily-fortress-2-mm9', // (MM9 Wily Fortress 2 (Sega Genesis))
    // ---
    'prototype-complete' => '', // (Unknown)
    );

// Count the number of fields that we'll be looping through
$field_index_size = count($field_index);
$field_sprite_directories_total = count($field_sprites_list);
$count_pad_length = strlen($field_index_size);

// Print out the stats before we start
ob_echo('Total Fields in Database: '.$field_index_size);
ob_echo('Total Sprites in ImageDir: '.$field_sprite_directories_total);
ob_echo('');

sleep(1);

$field_data_files_copied = array();
$field_image_directories_copied = array();

// MIGRATE ACTUAL FIELDS
$field_key = -1; $field_num = 0;
foreach ($field_index AS $field_token => $field_data){
    $field_key++; $field_num++;
    $count_string = '('.$field_num.' of '.$field_index_size.')';

    ob_echo('----------');
    ob_echo('Processing field data and sprites "'.$field_token.'" '.$count_string);
    ob_flush();

    $sprite_path = MMRPG_FIELDS_OLD_IMAGES_DIR.$field_token.'/';
    //ob_echo('-- $sprite_path = '.clean_path($sprite_path));
    $data_path = MMRPG_MIGRATE_OLD_DATA_DIR.$field_token.'.php';
    //ob_echo('-- $data_path = '.clean_path($data_path));

    $content_path = MMRPG_FIELDS_NEW_CONTENT_DIR.($field_token === 'field' ? '.field' : $field_token).'/';
    //ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deletedir_or_exit($content_path); }
    mkdir_or_exit($content_path);

    // Ensure the base sprite exists first and copy if so
    if (file_exists($sprite_path)){
        $content_images_path = $content_path.'sprites/';
        if (file_exists($content_images_path)){ deletedir_or_exit($content_images_path); }
        mkdir_or_exit($content_images_path);
        ob_echo('- copy '.clean_path($sprite_path).'* to '.clean_path($content_images_path));
        recurseCopyWithWhitelist($sprite_path, $content_images_path, $field_sprite_whitelist);
        $field_image_directories_copied[] = basename($sprite_path);
    }

    // Loop through and copy any named objects for this sprite as well
    if (isset($field_sprites_objects_list[$field_token])){
        $alt_tokens = $field_sprites_objects_list[$field_token];
        foreach ($alt_tokens AS $akey => $otoken){
            $sub_sprite_path = rtrim($sprite_path, '/').'_'.$otoken.'/';
            if (file_exists($sub_sprite_path)){
                $sub_content_images_path = rtrim($content_images_path, '/').'_'.$otoken.'/';
                if (file_exists($sub_content_images_path)){ deletedir_or_exit($sub_content_images_path); }
                mkdir_or_exit($sub_content_images_path);
                ob_echo('-- copy '.clean_path($sub_sprite_path).'* to '.clean_path($sub_content_images_path));
                recurseCopy($sub_sprite_path, $sub_content_images_path);
                $field_image_directories_copied[] = basename($sub_sprite_path);
                } else {
                break;
                }
            }
        }

    // Ensure the data file exists before attempting to extract functions from it
    if (true){
        $functions_file_markup = get_empty_functions_file_markup('field');
        if (!empty($functions_file_markup)){
            $content_data_path = $content_path.'functions.php';
            //ob_echo('- write default functions into '.clean_path($content_data_path));
            $h = fopen($content_data_path, 'w');
            fwrite($h, $functions_file_markup);
            fclose($h);
        }
        $player_data_files_copied[] = basename($data_path); // not actually copied but here for tracking
    }

    // If this field has music defined, we may need to update its token
    if (!empty($field_data['field_music'])){
        // If the defined music has had a name change, apply it now
        if (isset($music_paths_legacy_to_new[$field_data['field_music']])){
            $field_data['field_music'] = $music_paths_legacy_to_new[$field_data['field_music']];
        } elseif (isset($music_conversation_table[$field_data['field_token']])){
            $field_data['field_music'] = $music_conversation_table[$field_data['field_token']];
            $field_data['field_music_name'] = '';
            $field_data['field_music_link'] = '';
        }
        // If this music is in legacy format, attempt to upgrade to new
        if (!strstr($field_data['field_music'], '/')){
            if (!empty($field_data['field_master'])
                && !empty($field_data['field_game'])){
                $temp_master = $field_data['field_master'];
                $temp_game = isset($game_conversion_table[$field_data['field_game']]) ? $game_conversion_table[$field_data['field_game']] : $field_data['field_game'];
                $temp_new_music_path = 'sega-remix/'.$temp_master.'-'.strtolower($temp_game);
                if (isset($music_data_index[$temp_new_music_path])){
                    $field_data['field_music'] = $temp_new_music_path;
                }
            }
        }
        // If the defined music has associated link/name data, apply it now
        if (isset($music_data_index[$field_data['field_music']])){
            $temp_music_data = $music_data_index[$field_data['field_music']];
            if (!empty($temp_music_data['music_name'])){ $field_data['field_music_name'] = $temp_music_data['music_name']; }
            if (!empty($temp_music_data['music_link'])){ $field_data['field_music_link'] = $temp_music_data['music_link']; }
        }
    }
    // If the music path was empty, clear the other two related fields
    if (empty($field_data['field_music'])){
        $field_data['field_music_name'] = '';
        $field_data['field_music_link'] = '';
    }
    // Clean the field music field if it's weird
    $field_data['field_music_link'] = trim($field_data['field_music_link'], '"');

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('field', $field_data);
    if (!empty($content_json_data['field_music_link']) && count($content_json_data['field_music_link']) === 1){ $content_json_data['field_music_link'] = implode('', $content_json_data['field_music_link']); }
    ob_echo('- export all other data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, normalize_file_markup(json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)));
    fclose($h);

    if ($migration_limit && $field_num >= $migration_limit){ break; }

}

ob_echo('----------');

ob_echo('');
ob_echo_nobreak('Generating field groups data file... ');
$object_groups = cms_admin::generate_object_groups_from_index($field_index, 'field');
cms_admin::save_object_groups_to_json($object_groups, 'field');
ob_echo('...done!');
ob_echo('');

ob_echo('----------');

$field_image_directories_copied = array_unique($field_image_directories_copied);

ob_echo('');
ob_echo('Field Data Files Copied: '.count($field_data_files_copied).' / '.$field_index_size);
ob_echo('Field Image Directories Copied: '.count($field_image_directories_copied).' / '.$field_sprite_directories_total);
if (!($migration_limit > 0) && empty($migration_filter)){
    ob_echo('');
    ob_echo('Field Images Not Copied: '.print_r(array_diff($field_sprites_list, $field_image_directories_copied), true));
}
//ob_echo('$field_sprites_list: '.print_r($field_sprites_list, true));
//b_echo('$field_image_directories_copied: '.print_r($field_image_directories_copied, true));

sleep(1);

ob_echo('');
ob_echo('=============================');
ob_echo('|   END FIELD MIGRATION   |');
ob_echo('=============================');
ob_echo('');

?>