<?

ob_echo('');
ob_echo('============================');
ob_echo('|   START BATTLE MIGRATION   |');
ob_echo('============================');
ob_echo('');

// Collect an index of all valid battles from the database
// Load the complete battle index with the class function
load_legacy_battle_index();
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

// Pre-define the base battle content dir
define('MMRPG_BATTLES_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/battles/');

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

    $content_path = MMRPG_BATTLES_NEW_CONTENT_DIR.($battle_token === 'battle' ? '.battle' : $battle_token).'/';
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
    fwrite($h, json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
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
ob_echo('');


// -- LEGACY FUNCTIONS! -- //

// Define a function for loading the battle index cache file
function load_legacy_battle_index(){
    global $db;
    // Create the index as an empty array
    $db->INDEX['BATTLES'] = array();
    // Default the battles index to an empty array
    $mmrpg_battles_index = array();
    $mmrpg_battles_cache_path = MMRPG_CONFIG_CACHE_PATH.'cache.legacy-battles.'.MMRPG_CONFIG_CACHE_DATE.'.php';
    // If caching is turned OFF, or a cache has not been created
    if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists($mmrpg_battles_cache_path)){
        // Start indexing the battle data files
        $battles_cache_markup = index_legacy_battle_data();
        // Implode the markup into a single string and enclose in PHP tags
        $battles_cache_markup = implode('', $battles_cache_markup);
        $battles_cache_markup = "<?\n".$battles_cache_markup."\n?>";
        // Write the index to a cache file, if caching is enabled
        $battles_cache_file = @fopen($mmrpg_battles_cache_path, 'w');
        if (!empty($battles_cache_file)){
            @fwrite($battles_cache_file, $battles_cache_markup);
            @fclose($battles_cache_file);
        }
    }
    // Include the cache file so it can be evaluated
    require_once($mmrpg_battles_cache_path);
    // Return false if we got nothing from the index
    if (empty($mmrpg_battles_index)){ return false; }
    // Loop through the battles and index them after serializing
    foreach ($mmrpg_battles_index AS $token => $array){ $db->INDEX['BATTLES'][$token] = json_encode($array); }
    $db->INDEX['BATTLES_RAW'] = $db->INDEX['BATTLES'];
    // Additionally, include any dynamic session-based battles
    if (!empty($_SESSION['GAME']['values']['battle_index'])){
        // The session-based battles exist, so merge them with the index
        $db->INDEX['BATTLES'] = array_merge($db->INDEX['BATTLES'], $_SESSION['GAME']['values']['battle_index']);
    }
    // Return true on success
    return true;
}

// Define the function used for scanning the battle directory
function index_legacy_battle_data($this_path = ''){

    // Default the battles markup index to an empty array
    $battles_cache_markup = array();

    // Open the type data directory for scanning
    $battles_index_path = MMRPG_CONFIG_ROOTDIR.'data/battles/';
    $data_battles  = opendir($battles_index_path.$this_path);

    //echo 'Scanning '.$battles_index_path.$this_path.'<br />';

    // Loop through all the files in the directory
    while (false !== ($filename = readdir($data_battles))) {

        // If this is a directory, initiate a recusive scan
        if (is_dir($battles_index_path.$this_path.$filename.'/') && $filename != '.' && $filename != '..'){
            // Collect the markup from the recursive scan
            $append_cache_markup = index_legacy_battle_data($this_path.$filename.'/');
            // If markup was found, append if to the main container
            if (!empty($append_cache_markup)){ $battles_cache_markup = array_merge($battles_cache_markup, $append_cache_markup); }
        }
        // Else, ensure the file matches the naming format
        elseif (substr($filename, 0, 1) != '_' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
            // Collect the battle token from the filename
            $this_battle_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
            if (!empty($this_path)){ $this_battle_token = trim(str_replace('/', '-', $this_path), '-').'-'.$this_battle_token; }

            //echo '+ Adding battle token '.$this_battle_token.'...<br />';

            // Read the file into memory as a string and crop slice out the imporant part
            $this_battle_markup = trim(file_get_contents($battles_index_path.$this_path.$filename));
            $this_battle_markup = explode("\n", $this_battle_markup);
            $this_battle_markup = array_slice($this_battle_markup, 1, -1);
            // Replace the first line with the appropriate index key
            $this_battle_markup[1] = preg_replace('#\$battle = array\(#i', "\$mmrpg_battles_index['{$this_battle_token}'] = array(\n  'battle_token' => '{$this_battle_token}', 'battle_functions' => 'battles/{$this_path}{$filename}',", $this_battle_markup[1]);
            // Implode the markup into a single string
            $this_battle_markup = implode("\n", $this_battle_markup);
            // Copy this battle's data to the markup cache
            $battles_cache_markup[] = $this_battle_markup;
        }

    }

    // Close the battle data directory
    closedir($data_battles);

    // Return the generated cache markup
    return $battles_cache_markup;

}



?>