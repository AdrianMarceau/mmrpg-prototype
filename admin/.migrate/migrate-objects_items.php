<?

ob_echo('');
ob_echo('============================');
ob_echo('|   START ITEM MIGRATION   |');
ob_echo('============================');
ob_echo('');

// Collect an index of all valid items from the database
$item_fields = rpg_item::get_index_fields(false);
if (!in_array('item_functions', $item_fields)){ $item_fields[] = 'item_functions'; }
$item_fields = implode(', ', $item_fields);
$item_index = $db->get_array_list("SELECT {$item_fields} FROM mmrpg_index_items ORDER BY item_token ASC", 'item_token');

// Collect unnecessary fields to remove from the generated json data file
$skip_fields_on_json_export = rpg_item::get_fields_excluded_from_json_export(false);

// If there's a filter present, remove all tokens not in the filter
if (!empty($migration_filter)){
    $old_item_index = $item_index;
    $item_index = array();
    foreach ($migration_filter AS $item_token){
        if (isset($old_item_index[$item_token])){
            $item_index[$item_token] = $old_item_index[$item_token];
        }
    }
    unset($old_item_index);
}

// Pre-define the base item image dir and the new item content dir
define('MMRPG_ITEMS_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/items/');
if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/items/')){ define('MMRPG_ITEMS_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/items/'); }
elseif (file_exists(MMRPG_CONFIG_ROOTDIR.'images/xxx_items/')){ define('MMRPG_ITEMS_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/xxx_items/'); }
else { exit('Required directory /images/items/ does not exist!'); }

// Pre-collect an index of item alts so we don't have to scan each later
$item_sprites_list = scandir(MMRPG_ITEMS_OLD_IMAGES_DIR);
$item_sprites_list = array_filter($item_sprites_list, function($s){ if ($s !== '.' && $s !== '..'){ return true; } else { return false; } });
//echo('$item_sprites_list = '.print_r($item_sprites_list, true));
$item_sprites_alts_list = array();
foreach ($item_sprites_list AS $key => $token){
    if (strstr($token, '_')){
        list($token1, $token2) = explode('_', $token);
        if (!isset($item_sprites_alts_list[$token1])){ $item_sprites_alts_list[$token1] = array(); }
        $item_sprites_alts_list[$token1][] = $token2;
    }
}
//echo('$item_sprites_alts_list = '.print_r($item_sprites_alts_list, true));
//exit();

// Predefine the icon sprite and sprite-sprite filenames now
$icon_sprite_filenames = array('icon_left_40x40.png', 'icon_right_40x40.png', 'icon_left_80x80.png', 'icon_right_80x80.png');
$sprite_sprite_filenames = array('sprite_left_40x40.png', 'sprite_right_40x40.png', 'sprite_left_80x80.png', 'sprite_right_80x80.png');

// Pre-create special action and effect directories for later
$special_item_dirs = array();
$special_item_dirs[] = $special_action_items_dir = MMRPG_ITEMS_NEW_CONTENT_DIR.'_actions/';
$special_item_dirs[] = $special_effect_items_dir = MMRPG_ITEMS_NEW_CONTENT_DIR.'_effects/';
foreach ($special_item_dirs AS $special_item_dir){
    //ob_echo('-- $special_item_dir = '.clean_path($special_item_dir));
    if (empty($migration_filter) && file_exists($special_item_dir)){ deletedir_or_exit($special_item_dir); }
    if (!file_exists($special_item_dir)){ mkdir_or_exit($special_item_dir); }
}

// Count the number of items that we'll be looping through
$item_index_size = count($item_index);
$item_sprite_directories_total = count($item_sprites_list);
$count_pad_length = strlen($item_index_size);

// Print out the stats before we start
ob_echo('Total Items in Database: '.$item_index_size);
ob_echo('Total Sprites in ImageDir: '.$item_sprite_directories_total);
ob_echo('');

sleep(1);

$item_data_files_copied = array();
$item_image_directories_copied = array();

// MIGRATE ACTUAL ITEMS
$item_key = -1; $item_num = 0;
foreach ($item_index AS $item_token => $item_data){
    $item_key++; $item_num++;
    $count_string = '('.$item_num.' of '.$item_index_size.')';

    ob_echo('----------');
    ob_echo('Processing item "'.$item_token.'" '.$count_string);
    ob_flush();

    $function_path = rtrim(dirname($item_data['item_functions']), '/').'/';
    //ob_echo('-- $function_path = '.$function_path);

    $sprite_path = MMRPG_ITEMS_OLD_IMAGES_DIR.$item_token.'/';
    //ob_echo('-- $sprite_path = '.clean_path($sprite_path));
    $data_path = MMRPG_MIGRATE_OLD_DATA_DIR.$function_path.$item_token.'.php';
    //ob_echo('-- $data_path = '.clean_path($data_path));

    $content_path = MMRPG_ITEMS_NEW_CONTENT_DIR.($item_token === 'item' ? '.item' : $item_token).'/';
    //ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deletedir_or_exit($content_path); }
    mkdir_or_exit($content_path);

    if (file_exists($sprite_path)){
        $content_images_path = $content_path.'sprites/';
        if (file_exists($content_images_path)){ deletedir_or_exit($content_images_path); }
        mkdir_or_exit($content_images_path);
        ob_echo('- copy '.clean_path($sprite_path).'* to '.clean_path($content_images_path));
        recurseCopy($sprite_path, $content_images_path);
        $item_image_directories_copied[] = basename($sprite_path);
    }

    for ($i = 2; $i < 99; $i++){
        $sub_sprite_path = rtrim($sprite_path, '/').'-'.$i.'/';
        if (file_exists($sub_sprite_path)){
            $sub_content_images_path = rtrim($content_images_path, '/').'_'.$i.'/';
            if (file_exists($sub_content_images_path)){ deletedir_or_exit($sub_content_images_path); }
            mkdir_or_exit($sub_content_images_path);
            ob_echo('-- copy '.clean_path($sub_sprite_path).'* to '.clean_path($sub_content_images_path));
            recurseCopy($sub_sprite_path, $sub_content_images_path);
            $item_image_directories_copied[] = basename($sub_sprite_path);
            } else {
            break;
            }
        }

    if (isset($item_sprites_alts_list[$item_token])){
        $alt_tokens = $item_sprites_alts_list[$item_token];
        foreach ($alt_tokens AS $akey => $atoken){
            $sub_sprite_path = rtrim($sprite_path, '/').'_'.$atoken.'/';
            if (file_exists($sub_sprite_path)){
                $sub_content_images_path = rtrim($content_images_path, '/').'_'.$atoken.'/';
                if (file_exists($sub_content_images_path)){ deletedir_or_exit($sub_content_images_path); }
                mkdir_or_exit($sub_content_images_path);
                ob_echo('-- copy '.clean_path($sub_sprite_path).'* to '.clean_path($sub_content_images_path));
                recurseCopy($sub_sprite_path, $sub_content_images_path);
                $item_image_directories_copied[] = basename($sub_sprite_path);
                } else {
                break;
                }
            }
        }

    // Ensure the data file exists before attempting to extra functions from it
    if (file_exists($data_path)){
        $split_markup = get_parsed_object_file_markup($data_path);
        if (!empty($split_markup['functions'])){
            $content_data_path = $content_path.'functions.php';
            ob_echo('- extract '.clean_path($data_path).' functions into '.clean_path($content_data_path));
            $h = fopen($content_data_path, 'w');
            fwrite($h, $split_markup['functions']);
            fclose($h);
        }
        $item_data_files_copied[] = basename($data_path);
    }

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('item', $item_data);
    if (!empty($skip_fields_on_json_export)){ foreach ($skip_fields_on_json_export AS $field){ unset($content_json_data[$field]); } }
    ob_echo('- export all other data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, normalize_file_markup(json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)));
    fclose($h);

    if ($migration_limit && $item_num >= $migration_limit){ break; }

}


// MIGRATE OTHER ITEMS

// Only migrate other items if a filter isn't present
if (empty($migration_filter)){

    // Define the objects base path
    $object_base_path = MMRPG_CONFIG_ROOTDIR.'images/objects/';

    // Migrate all the elemental heart sprites to the "heart-cores" directory instead
    ob_echo('----------');
    $type_fields = rpg_type::get_index_fields(true);
    $type_index = $db->get_array_list("SELECT {$type_fields} FROM mmrpg_index_types ORDER BY type_token ASC", 'type_token');
    $type_index_tokens = array_keys($type_index);
    array_unshift($type_index_tokens, '');
    $this_object_base_path = $object_base_path.'heart-cores/';
    if (file_exists($this_object_base_path)){ deletedir_or_exit($this_object_base_path); }
    mkdir_or_exit($this_object_base_path);
    $copy_heart_sprites = array();
    foreach ($type_index_tokens AS $type_token){
        $base_token = !empty($type_token) ? $type_token.'-heart' : 'heart';
        $export_token = !empty($type_token) ? $type_token : 'base';
        if (!file_exists(MMRPG_ITEMS_OLD_IMAGES_DIR.$base_token.'/')){ continue; }
        $copy_heart_sprites[] = array($base_token, $this_object_base_path.$export_token.'/');
    }
    foreach ($copy_heart_sprites AS $copy_key => $copy_info){
        list($sprite_token, $export_path) = $copy_info;
        $count_string = '('.($copy_key + 1).' of '.count($copy_heart_sprites).')';
        copy_sprites_to_new_dir($sprite_token, $count_string, $export_path);
        if ($migration_limit && ($copy_key + 1) >= $migration_limit){ break; }
    }

    // Migrate all the challenge marker sprites into the "challenge-markers" directory instead
    ob_echo('----------');
    $marker_kind_tokens = array('base', 'bronze', 'silver', 'gold', 'glass', 'shadow');
    $this_object_base_path = $object_base_path.'challenge-markers/';
    if (file_exists($this_object_base_path)){ deletedir_or_exit($this_object_base_path); }
    mkdir_or_exit($this_object_base_path);
    $copy_marker_sprites = array();
    foreach ($marker_kind_tokens AS $kind_token){
        $copy_marker_sprites[] = array('challenge-marker_'.$kind_token, $this_object_base_path.$kind_token.'/');
    }
    foreach ($copy_marker_sprites AS $copy_key => $copy_info){
        list($sprite_token, $export_path) = $copy_info;
        $count_string = '('.($copy_key + 1).' of '.count($copy_marker_sprites).')';
        copy_sprites_to_new_dir($sprite_token, $count_string, $export_path);
        if ($migration_limit && ($copy_key + 1) >= $migration_limit){ break; }
    }

    // Migrate the star shadow marker to the field and fusion star directories instead
    ob_echo('----------');
    $copy_shadow_sprites = array();
    $field_star_content_dir = MMRPG_ITEMS_NEW_CONTENT_DIR.'field-star/';
    $fusion_star_content_dir = MMRPG_ITEMS_NEW_CONTENT_DIR.'fusion-star/';
    if (file_exists($field_star_content_dir)){ $copy_shadow_sprites[] = array('star-marker_shadow', $field_star_content_dir.'shadows/'); }
    if (file_exists($fusion_star_content_dir)){ $copy_shadow_sprites[] = array('star-marker_shadow', $fusion_star_content_dir.'shadows/'); }
    foreach ($copy_shadow_sprites AS $copy_key => $copy_info){
        list($sprite_token, $export_path) = $copy_info;
        $count_string = '('.($copy_key + 1).' of '.count($copy_shadow_sprites).')';
        copy_sprites_to_new_dir($sprite_token, $count_string, $export_path);
        if ($migration_limit && ($copy_key + 1) >= $migration_limit){ break; }
    }

    // Migrate the various effect sprites into the "_effects" directory instead
    ob_echo('----------');
    $copy_effect_sprites = array();
    $copy_effect_sprites[] = array('field-support', $special_effect_items_dir.'type-arrows_boost-1/');
    $copy_effect_sprites[] = array('field-support-2', $special_effect_items_dir.'type-arrows_boost-2/');
    $copy_effect_sprites[] = array('field-support-3', $special_effect_items_dir.'type-arrows_break-1/');
    $copy_effect_sprites[] = array('field-support-4', $special_effect_items_dir.'type-arrows_break-2/');
    foreach ($copy_effect_sprites AS $copy_key => $copy_info){
        list($sprite_token, $export_path) = $copy_info;
        $count_string = '('.($copy_key + 1).' of '.count($copy_effect_sprites).')';
        copy_sprites_to_new_dir($sprite_token, $count_string, $export_path, $icon_sprite_filenames);
        copy_sprites_to_new_dir('item', '', $export_path, $sprite_sprite_filenames, false, true);
        if ($migration_limit && ($copy_key + 1) >= $migration_limit){ break; }
    }

    // Migrate the 'attachment-defeat' sprites into the "defeat-explosion" directory instead
    ob_echo('----------');
    copy_sprites_to_new_dir('attachment-defeat', '(1 of 1)', $object_base_path.'defeat-explosion/');

}

ob_echo('----------');

ob_echo('');
ob_echo_nobreak('Generating item groups data file... ');
$object_groups = cms_admin::generate_object_groups_from_index($ability_index, 'item');
cms_admin::save_object_groups_to_json($object_groups, 'item');
ob_echo('...done!');
ob_echo('');

ob_echo('----------');

$item_image_directories_copied = array_unique($item_image_directories_copied);

ob_echo('');
ob_echo('Item Data Files Copied: '.count($item_data_files_copied).' / '.$item_index_size);
ob_echo('Item Image Directories Copied: '.count($item_image_directories_copied).' / '.$item_sprite_directories_total);
if (!($migration_limit > 0) && empty($migration_filter)){
    ob_echo('');
    ob_echo('Item Images Not Copied: '.print_r(array_diff($item_sprites_list, $item_image_directories_copied), true));
}
//ob_echo('$item_sprites_list: '.print_r($item_sprites_list, true));
//b_echo('$item_image_directories_copied: '.print_r($item_image_directories_copied, true));

sleep(1);

ob_echo('');
ob_echo('============================');
ob_echo('|    END ITEM MIGRATION    |');
ob_echo('============================');
ob_echo('');

?>