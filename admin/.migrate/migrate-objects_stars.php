<?

ob_echo('');
ob_echo('==================================');
ob_echo('|   START ROGUE STAR MIGRATION   |');
ob_echo('==================================');
ob_echo('');

// Collect an index of all valid stars from the database
$star_index = $db->get_array_list("SELECT * FROM mmrpg_rogue_stars ORDER BY star_id ASC;", 'star_id');
//echo('<pre>$star_index = '.print_r($star_index, true).'</pre>');
//exit();

// Create a "fake" rogue star with empty felds as is standard
$pseudo_star = array(
    'star_id' => 0,
    'star_type' => '',
    'star_from_date' => '0000-00-00',
    'star_from_date_time' => '00:00:00',
    'star_to_date' => '0000-00-00',
    'star_to_date_time' => '00:00:00',
    'star_power' => 0,
    'star_active' => 0,
    'star_flag_enabled' => 0
    );

// Prepend the empty rogue star to beginning of array
$star_index = array_merge(array(0 => $pseudo_star), $star_index);
//echo('<pre>$star_index = '.print_r($star_index, true).'</pre>');
//exit();

// Pre-define the base star content dir
define('MMRPG_STARS_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/stars/');

// Count the number of stars that we'll be looping through
$star_index_size = count($star_index);
$count_pad_length = strlen($star_index_size);

// Print out the stats before we start
ob_echo('Total Rogue Stars in Index: '.$star_index_size);
ob_echo('');

sleep(1);

$rogue_stars_exported = array();

// MIGRATE ACTUAL STARS
$star_key = -1; $star_num = 0;
foreach ($star_index AS $star_data){
    $star_key++; $star_num++;
    $count_string = '('.$star_num.' of '.$star_index_size.')';

    $star_id = $star_data['star_id'];
    $star_token = 'star-'.str_pad($star_id, 4, '0', STR_PAD_LEFT);

    ob_echo('----------');
    ob_echo('Processing rogue star ID '.$star_id.' '.$count_string);
    ob_flush();

    $content_path = MMRPG_STARS_NEW_CONTENT_DIR.(empty($star_id) ? '.star' : $star_token).'/';
    ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deleteDir($content_path); }
    mkdir($content_path);

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('star', $star_data, false);
    ob_echo('- export all star data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
    fclose($h);
    if (file_exists($content_json_path)){ $rogue_stars_exported[] = basename($content_json_path); }

    if ($migration_limit && $star_num >= $migration_limit){ break; }

}


ob_echo('----------');

ob_echo('');
ob_echo('Rogue Stars Exported: '.count($rogue_stars_exported).' / '.$star_index_size);

sleep(1);

ob_echo('');
ob_echo('==================================');
ob_echo('|    END ROGUE STAR MIGRATION    |');
ob_echo('==================================');
ob_echo('');

?>