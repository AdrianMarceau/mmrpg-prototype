<?

ob_echo('');
ob_echo('============================');
ob_echo('|   START BATTLE MIGRATION   |');
ob_echo('============================');
ob_echo('');

// Collect an index of all valid battles from the database
// Load the complete battle index with the class function
rpg_battle::load_legacy_battle_index();
$battle_index = $db->INDEX['BATTLES'];
//echo('<pre>$battle_index = '.print_r($battle_index, true).'</pre>');
//exit();

// If there's a filter present, remove all tokens not in the filter
if (!empty($migration_filter)){
    $old_battle_index = $battle_index;
    $battle_index = array();
    foreach ($migration_filter AS $battle_token){
        if (isset($old_battle_index[$battle_token])){
            $battle_index[$battle_token] = $old_battle_index[$battle_token];
        }
    }
    unset($old_battle_index);
}

define('MMRPG_BATTLES_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/battles/');

// Count the number of battles that we'll be looping through
$battle_index_size = count($battle_index);
$count_pad_length = strlen($battle_index_size);

// Print out the stats before we start
ob_echo('Total Battles in Index: '.$battle_index_size);
ob_echo('');

sleep(1);

$battle_data_files_copied = array();

// MIGRATE ACTUAL BATTLES
$battle_key = -1; $battle_num = 0;
foreach ($battle_index AS $battle_token => $battle_data){
    $battle_key++; $battle_num++;
    $count_string = '('.$battle_num.' of '.$battle_index_size.')';

    $battle_data = json_decode($battle_data, true);

    ob_echo('----------');
    ob_echo('Processing battle "'.$battle_token.'" '.$count_string);
    ob_flush();

    $content_path = MMRPG_BATTLES_CONTENT_DIR.($battle_token === 'battle' ? '.battle' : $battle_token).'/';
    //ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deleteDir($content_path); }
    mkdir($content_path);

    $data_path = MMRPG_CONFIG_ROOTDIR.'data/'.$battle_data['battle_functions'];
    //ob_echo('-- $data_path = '.clean_path($data_path));

    // Ensure the data file exists before adding it to the copied list
    // (we don't actually copy it though, this is just for tracking)
    if (file_exists($data_path)){
        $battle_data_files_copied[] = basename($data_path);
    }

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = $battle_data;
    unset($content_json_data['battle_id']);
    unset($content_json_data['battle_functions']);
    ob_echo('- export all other data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, json_encode($content_json_data, JSON_PRETTY_PRINT));
    fclose($h);

    if ($migration_limit && $battle_num >= $migration_limit){ break; }

}

ob_echo('----------');

ob_echo('');
ob_echo('Battle Data Files Copied: '.count($battle_data_files_copied).' / '.$battle_index_size);


sleep(1);

ob_echo('');
ob_echo('============================');
ob_echo('|    END BATTLE MIGRATION    |');
ob_echo('============================');



?>