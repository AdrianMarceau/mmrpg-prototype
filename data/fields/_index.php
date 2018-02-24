<?
// Default the fields index to an empty array
$mmrpg_index['fields'] = array();

// Define the cache and index paths for fields
$fields_index_path = MMRPG_CONFIG_ROOTDIR.'data/fields/';
$fields_cache_path = MMRPG_CONFIG_CACHE_PATH.'cache.fields.'.MMRPG_CONFIG_CACHE_DATE.'.php';

// Define the function used for scanning the field directory
$data_key = 0;
function index_field_data($this_path = ''){

    // Define references to the global variables
    global $fields_index_path, $fields_cache_path, $data_key;

    // Default the fields markup index to an empty array
    $fields_cache_markup = array();

    // Open the type data directory for scanning
    $data_fields  = opendir($fields_index_path.$this_path);

    //echo 'Scanning '.$fields_index_path.$this_path.'<br />';

    // Loop through all the files in the directory
    while (false !== ($filename = readdir($data_fields))) {

        // If this is a directory, initiate a recusive scan
        if (is_dir($fields_index_path.$this_path.$filename.'/') && $filename != '.' && $filename != '..'){
            // Collect the markup from the recursive scan
            $append_cache_markup = index_field_data($this_path.$filename.'/');
            // If markup was found, append if to the main container
            if (!empty($append_cache_markup)){ $fields_cache_markup = array_merge($fields_cache_markup, $append_cache_markup); }
        }
        // Else, ensure the file matches the naming format
        elseif ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
            // Increment the data key
            $data_key++;
            // Collect the field token from the filename
            $this_field_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
            //if (!empty($this_path)){ $this_field_token = trim(str_replace('/', '-', $this_path), '-').'-'.$this_field_token; }

            //echo '+ Adding field token '.$this_field_token.'...<br />';

            // Read the file into memory as a string and crop slice out the imporant part
            $this_field_markup = trim(file_get_contents($fields_index_path.$this_path.$filename));
            $this_field_markup = explode("\n", $this_field_markup);
            $this_field_markup = array_slice($this_field_markup, 1, -1);
            // Replace the first line with the appropriate index key
            $this_field_markup[1] = preg_replace('#\$field = array\(#i', "\$mmrpg_index['fields']['{$this_field_token}'] = array(\n  'field_id' => '{$data_key}',\n  'field_token' => '{$this_field_token}', 'field_functions' => 'fields/{$this_path}{$filename}',", $this_field_markup[1]);
            // Implode the markup into a single string
            $this_field_markup = implode("\n", $this_field_markup);
            // Copy this field's data to the markup cache
            $fields_cache_markup[] = $this_field_markup;
        }

    }

    // Close the field data directory
    closedir($data_fields);

    // Return the generated cache markup
    return $fields_cache_markup;

}

// If caching is turned OFF, or a cache has not been created
if (true){ //!MMRPG_CONFIG_CACHE_INDEXES || !file_exists($fields_cache_path)

    // Start indexing the field data files
    $fields_cache_markup = index_field_data();

    // Implode the markup into a single string and enclose in PHP tags
    $fields_cache_markup = implode('', $fields_cache_markup);
    $fields_cache_markup = "<?\n".$fields_cache_markup."\n?>";

    // Write the index to a cache file, if caching is enabled
    $fields_cache_file = @fopen($fields_cache_path, 'w');
    if (!empty($fields_cache_file)){
        @fwrite($fields_cache_file, $fields_cache_markup);
        @fclose($fields_cache_file);
    }

}

// Include the cache file so it can be evaluated
require_once($fields_cache_path);

// Additionally, include any dynamic session-based fields
if (!empty($_SESSION['GAME']['values']['field_index'])){
    // The session-based fields exist, so merge them with the index
    $mmrpg_index['fields'] = array_merge($mmrpg_index['fields'], $_SESSION['GAME']['values']['field_index']);
}

// If debug is requested, print the cache data
if (!empty($_GET['debug']) && $_GET['debug'] == 'index_fields'){
    die('<pre>'.print_r($mmrpg_index['fields'], true).'</pre>'); //DEBUG
}
?>