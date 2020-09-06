<?

ob_echo('');
ob_echo('=============================');
ob_echo('|  START PLAYER MIGRATION  |');
ob_echo('=============================');
ob_echo('');

// Predefine any deprecated players or player sprites so we can ignore them
$deprecated_players = array(
    );

// Collect an index of all valid players from the database
$player_fields = rpg_player::get_index_fields(false);
if (!in_array('player_functions', $player_fields)){ $player_fields[] = 'player_functions'; }
$player_fields = implode(', ', $player_fields);
$player_index = $db->get_array_list("SELECT {$player_fields} FROM mmrpg_index_players ORDER BY player_token ASC", 'player_token');

// If there's a filter present, remove all tokens not in the filter
if (!empty($migration_filter)){
    $old_player_index = $player_index;
    $player_index = array();
    foreach ($migration_filter AS $player_token){
        if (isset($old_player_index[$player_token])){
            $player_index[$player_token] = $old_player_index[$player_token];
        }
    }
    unset($old_player_index);
}

// Pre-define the base player image dir and the new player content dir
define('MMRPG_PLAYERS_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/players/');
if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/players/')){ define('MMRPG_PLAYERS_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/players/'); }
elseif (file_exists(MMRPG_CONFIG_ROOTDIR.'images/xxx_players/')){ define('MMRPG_PLAYERS_OLD_IMAGES_DIR', MMRPG_CONFIG_ROOTDIR.'images/xxx_players/'); }
else { exit('Required directory /images/players/ does not exist!'); }
define('MMRPG_PLAYERS_OLD_SHADOW_IMAGES_DIR', rtrim(MMRPG_PLAYERS_OLD_IMAGES_DIR, '/').'_shadows/');

// Pre-collect an index of player alts so we don't have to scan each later
$player_sprites_list = scandir(MMRPG_PLAYERS_OLD_IMAGES_DIR);
$player_sprites_list = array_filter($player_sprites_list, function($s){ if ($s !== '.' && $s !== '..'){ return true; } else { return false; } });
//echo('$player_sprites_list = '.print_r($player_sprites_list, true));

// Manually remove deprecated players from the sprite and index lists
foreach ($deprecated_players AS $token){
    $rm_key = array_search($token, $player_sprites_list);
    if ($rm_key !== false){ unset($player_sprites_list[$rm_key]); }
    if (isset($player_index[$token])){ unset($player_index[$token]); }
}

// Loop through sprites and pre-collect any alts for later looping
$player_sprites_alts_list = array();
foreach ($player_sprites_list AS $key => $token){
    if (strstr($token, '_')){
        list($token1, $token2) = explode('_', $token);
        if (!isset($player_sprites_alts_list[$token1])){ $player_sprites_alts_list[$token1] = array(); }
        $player_sprites_alts_list[$token1][] = $token2;
    }
}
//echo('$player_sprites_alts_list = '.print_r($player_sprites_alts_list, true));
//exit();

// Predefine the icon sprite and sprite-sprite filenames now
$mug_sprite_filenames = array('mug_left_40x40.png', 'mug_right_40x40.png', 'mug_left_80x80.png', 'mug_right_80x80.png');
$sprite_sprite_filenames = array('sprite_left_40x40.png', 'sprite_right_40x40.png', 'sprite_left_80x80.png', 'sprite_right_80x80.png');

// Predefine the icon sprites and sprite-sprites that should be skipped
$skip_player_sprite_filenames = array('mug_left_160x160.png', 'mug_right_160x160.png', 'sprite_left_160x160.png', 'sprite_right_160x160.png');

// Count the number of players that we'll be looping through
$player_index_size = count($player_index);
$player_sprite_directories_total = count($player_sprites_list);
$count_pad_length = strlen($player_index_size);

// Print out the stats before we start
ob_echo('Total Players in Database: '.$player_index_size);
ob_echo('Total Sprites in ImageDir: '.$player_sprite_directories_total);
ob_echo('');

sleep(1);

$player_data_files_copied = array();
$player_image_directories_copied = array();

// MIGRATE ACTUAL PLAYERS
$player_key = -1; $player_num = 0;
foreach ($player_index AS $player_token => $player_data){
    $player_key++; $player_num++;
    $count_string = '('.$player_num.' of '.$player_index_size.')';

    ob_echo('----------');
    ob_echo('Processing player data and sprites "'.$player_token.'" '.$count_string);
    ob_flush();

    $content_path = MMRPG_PLAYERS_NEW_CONTENT_DIR.($player_token === 'player' ? '.player' : $player_token).'/';
    //ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deletedir_or_exit($content_path); }
    mkdir_or_exit($content_path);

    $sprite_path = MMRPG_PLAYERS_OLD_IMAGES_DIR.$player_token.'/';
    //ob_echo('-- $sprite_path = '.clean_path($sprite_path));

    // Ensure the base sprite exists first and copy if so
    if (file_exists($sprite_path)){
        $content_images_path = $content_path.'sprites/';
        if (file_exists($content_images_path)){ deletedir_or_exit($content_images_path); }
        mkdir_or_exit($content_images_path);
        ob_echo('- copy '.clean_path($sprite_path).'* to '.clean_path($content_images_path));
        recurseCopy($sprite_path, $content_images_path, $skip_player_sprite_filenames);
        $player_image_directories_copied[] = basename($sprite_path);
    }

    // Loop through and copy any named alts for this sprite as well
    if (isset($player_sprites_alts_list[$player_token])){
        $alt_tokens = $player_sprites_alts_list[$player_token];
        foreach ($alt_tokens AS $akey => $atoken){
            $sub_sprite_path = rtrim($sprite_path, '/').'_'.$atoken.'/';
            if (file_exists($sub_sprite_path)){
                $sub_content_images_path = rtrim($content_images_path, '/').'_'.$atoken.'/';
                if (file_exists($sub_content_images_path)){ deletedir_or_exit($sub_content_images_path); }
                mkdir_or_exit($sub_content_images_path);
                ob_echo('-- copy '.clean_path($sub_sprite_path).'* to '.clean_path($sub_content_images_path));
                recurseCopy($sub_sprite_path, $sub_content_images_path, $skip_player_sprite_filenames);
                $player_image_directories_copied[] = basename($sub_sprite_path);
                } else {
                break;
                }
            }
        }

    $shadows_path = MMRPG_PLAYERS_OLD_SHADOW_IMAGES_DIR.$player_token.'/';
    //ob_echo('-- $shadows_path = '.clean_path($shadows_path));

    // Ensure the shadow sprite exists first and copy if so
    if (file_exists($shadows_path)){
        $content_shadows_path = $content_path.'shadows/';
        if (file_exists($content_shadows_path)){ deletedir_or_exit($content_shadows_path); }
        mkdir_or_exit($content_shadows_path);
        ob_echo('- copy '.clean_path($shadows_path).'* to '.clean_path($content_shadows_path));
        recurseCopy($shadows_path, $content_shadows_path, $skip_player_sprite_filenames);
        $player_image_directories_copied[] = basename($shadows_path);
    }

    $data_path = MMRPG_MIGRATE_OLD_DATA_DIR.$player_token.'.php';
    ob_echo('-- $data_path = '.clean_path($data_path));

    // Ensure the data file exists before attempting to extract functions from it
    if (true){
        $functions_file_markup = get_empty_functions_file_markup('player');
        if (!empty($functions_file_markup)){
            $content_data_path = $content_path.'functions.php';
            //ob_echo('- write default functions into '.clean_path($content_data_path));
            $h = fopen($content_data_path, 'w');
            fwrite($h, $functions_file_markup);
            fclose($h);
        }
        $player_data_files_copied[] = basename($data_path); // not actually copied but here for tracking
    }

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('player', $player_data);
    ob_echo('- export all other data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, normalize_file_markup(json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)));
    fclose($h);

    if ($migration_limit && $player_num >= $migration_limit){ break; }

}


ob_echo('----------');

$player_image_directories_copied = array_unique($player_image_directories_copied);

ob_echo('');
ob_echo('Player Data Files Copied: '.count($player_data_files_copied).' / '.$player_index_size);
ob_echo('Player Image Directories Copied: '.count($player_image_directories_copied).' / '.$player_sprite_directories_total);
if (!($migration_limit > 0) && empty($migration_filter)){
    ob_echo('');
    ob_echo('Player Images Not Copied: '.print_r(array_diff($player_sprites_list, $player_image_directories_copied), true));
}
//ob_echo('$player_sprites_list: '.print_r($player_sprites_list, true));
//b_echo('$player_image_directories_copied: '.print_r($player_image_directories_copied, true));

sleep(1);

ob_echo('');
ob_echo('=============================');
ob_echo('|   END PLAYER MIGRATION    |');
ob_echo('=============================');
ob_echo('');

?>