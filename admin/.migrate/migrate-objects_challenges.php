<?

ob_echo('');
ob_echo('=======================================');
ob_echo('|   START EVENT CHALLENGE MIGRATION   |');
ob_echo('=======================================');
ob_echo('');

// Collect an index of all valid challenges from the database
$challenge_index = $db->get_array_list("SELECT * FROM mmrpg_challenges ORDER BY challenge_id ASC;", 'challenge_id');
//echo('<pre>$challenge_index = '.print_r($challenge_index, true).'</pre>');
//exit();

// Create a "fake" event challenge with empty felds as is standard
$pseudo_challenge = array(
    'challenge_id' => 0,
    'challenge_kind' => 'event',
    'challenge_creator' => 0,
    'challenge_name' => 'Event Challenge',
    'challenge_description' => 'This is the default event challenge object.',
    'challenge_robot_limit' => 0,
    'challenge_turn_limit' => 0,
    'challenge_field_data' => '',
    'challenge_target_data' => '',
    'challenge_reward_data' => '',
    'challenge_flag_published' => 0,
    'challenge_flag_hidden' => 0,
    'challenge_times_accessed' => 0,
    'challenge_times_concluded' => 0,
    'challenge_user_victories' => 0,
    'challenge_user_defeats' => 0,
    'challenge_date_created' => 0,
    'challenge_date_modified' => 0,
    );

// Prepend the empty event challenge to beginning of array
$challenge_index = array_merge(array(0 => $pseudo_challenge), $challenge_index);

//echo('<pre>$challenge_index = '.print_r($challenge_index, true).'</pre>');
//exit();

// Pre-define the base challenge content dir
define('MMRPG_CHALLENGES_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/challenges/');

// Count the number of challenges that we'll be looping through
$challenge_index_size = count($challenge_index);
$count_pad_length = strlen($challenge_index_size);

// Print out the stats before we start
ob_echo('Total Event Challenges in Index: '.$challenge_index_size);
ob_echo('');

sleep(1);

$event_challenges_exported = array();

// Manually define challenge fields that are in JSON format
$json_challenge_fields = array('challenge_field_data', 'challenge_target_data', 'challenge_reward_data');

// MIGRATE ACTUAL CHALLENGES
$challenge_key = -1; $challenge_num = 0;
foreach ($challenge_index AS $challenge_data){
    $challenge_key++; $challenge_num++;
    $count_string = '('.$challenge_num.' of '.$challenge_index_size.')';

    $challenge_id = $challenge_data['challenge_id'];
    $challenge_token = 'challenge-'.str_pad($challenge_id, 4, '0', STR_PAD_LEFT);

    ob_echo('----------');
    ob_echo('Processing event challenge ID '.$challenge_id.' '.$count_string);
    ob_flush();

    $content_path = MMRPG_CHALLENGES_NEW_CONTENT_DIR.(empty($challenge_id) ? '.challenge' : $challenge_token).'/';
    ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deletedir_or_exit($content_path); }
    mkdir_or_exit($content_path);

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('challenge', $challenge_data, false, true, $json_challenge_fields);
    ob_echo('- export all challenge data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, normalize_file_markup(json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)));
    fclose($h);
    if (file_exists($content_json_path)){ $event_challenges_exported[] = basename($content_json_path); }

    if ($migration_limit && $challenge_num >= $migration_limit){ break; }

}


ob_echo('----------');

ob_echo('');
ob_echo('Event Challenges Exported: '.count($event_challenges_exported).' / '.$challenge_index_size);

sleep(1);

ob_echo('');
ob_echo('=======================================');
ob_echo('|    END EVENT CHALLENGE MIGRATION    |');
ob_echo('=======================================');
ob_echo('');

?>