<?
// Default the items index to an empty array
$mmrpg_index['items'] = array();

// Define the cache and index paths for items
$items_index_path = MMRPG_CONFIG_ROOTDIR.'data/items/';
$items_cache_path = MMRPG_CONFIG_CACHE_PATH.'cache.items.'.MMRPG_CONFIG_CACHE_DATE.'.php';

// Define the function used for scanning the item directory
$data_key = 0;
function index_item_data($this_path = ''){

    // Define references to the global variables
    global $items_index_path, $items_cache_path, $data_key;

    // Default the items markup index to an empty array
    $items_cache_markup = array();

    // Open the type data directory for scanning
    $data_items  = opendir($items_index_path.$this_path);

    //echo 'Scanning '.$items_index_path.$this_path.'<br />';

    // Loop through all the files in the directory
    while (false !== ($filename = readdir($data_items))) {

        // If this is a directory, initiate a recusive scan
        if (is_dir($items_index_path.$this_path.$filename.'/') && $filename != '.' && $filename != '..'){
            // Collect the markup from the recursive scan
            $append_cache_markup = index_item_data($this_path.$filename.'/');
            // If markup was found, append if to the main container
            if (!empty($append_cache_markup)){ $items_cache_markup = array_merge($items_cache_markup, $append_cache_markup); }
        }
        // Else, ensure the file matches the naming format
        elseif ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
            // Increment the data key
            $data_key++;
            // Collect the item token from the filename
            $this_item_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
            //if (!empty($this_path)){ $this_item_token = trim(str_replace('/', '-', $this_path), '-').'-'.$this_item_token; }

            //echo '+ Adding item token '.$this_item_token.'...<br />';

            // Read the file into memory as a string and crop slice out the imporant part
            $this_item_markup = trim(file_get_contents($items_index_path.$this_path.$filename));
            $this_item_markup = explode("\n", $this_item_markup);
            $this_item_markup = array_slice($this_item_markup, 1, -1);
            // Replace the first line with the appropriate index key
            $this_item_markup[1] = preg_replace('#\$item = array\(#i', "\$mmrpg_index['items']['{$this_item_token}'] = array(\n  'item_id' => '{$data_key}',\n  'item_token' => '{$this_item_token}', 'item_functions' => 'items/{$this_path}{$filename}',", $this_item_markup[1]);
            // Implode the markup into a single string
            $this_item_markup = implode("\n", $this_item_markup);
            // Copy this item's data to the markup cache
            $items_cache_markup[] = $this_item_markup;
        }

    }

    // Close the item data directory
    closedir($data_items);

    // Return the generated cache markup
    return $items_cache_markup;

}

// If caching is turned OFF, or a cache has not been created
if (true){ //!MMRPG_CONFIG_CACHE_INDEXES || !file_exists($items_cache_path)

    // Start indexing the item data files
    $items_cache_markup = index_item_data();

    // Implode the markup into a single string and enclose in PHP tags
    $items_cache_markup = implode('', $items_cache_markup);
    $items_cache_markup = "<?\n".$items_cache_markup."\n?>";

    // Write the index to a cache file, if caching is enabled
    $items_cache_file = @fopen($items_cache_path, 'w');
    if (!empty($items_cache_file)){
        @fwrite($items_cache_file, $items_cache_markup);
        @fclose($items_cache_file);
    }

}

// Include the cache file so it can be evaluated
require_once($items_cache_path);

// Additionally, include any dynamic session-based items
if (!empty($_SESSION['GAME']['values']['item_index'])){
    // The session-based items exist, so merge them with the index
    $mmrpg_index['items'] = array_merge($mmrpg_index['items'], $_SESSION['GAME']['values']['item_index']);
}

// If debug is requested, print the cache data
if (!empty($_GET['debug']) && $_GET['debug'] == 'index_items'){
    die('<pre>'.print_r($mmrpg_index['items'], true).'</pre>'); //DEBUG
}
?>