<?
// Default the items index to an empty array
$mmrpg_index['items'] = array();

// Define the cache and index paths for items
$items_index_path = MMRPG_CONFIG_ROOTDIR.'data/items/';
$items_cache_path = MMRPG_CONFIG_ROOTDIR.'data/cache/'.'cache.items.'.MMRPG_CONFIG_CACHE_DATE.'.php';

// If caching is turned OFF, or a cache has not been created
if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists($items_cache_path)){

  // Default the items markup index to an empty array
  $items_cache_markup = array();

  // Open the type data directory for scanning
  $data_items  = opendir($items_index_path);

  // Loop through all the files in the directory
  while (false !== ($filename = readdir($data_items))) {

    // Ensure the file matches the naming format
    if ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
      // Collect the item token from the filename
      $this_item_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
      // Read the file into memory as a string and crop slice out the imporant part
      $this_item_markup = trim(file_get_contents($items_index_path.$filename));
      $this_item_markup = explode("\n", $this_item_markup);
      $this_item_markup = array_slice($this_item_markup, 1, -1);
      // Replace the first line with the appropriate index key
      $this_item_markup[1] = preg_replace('#\$item = array\(#i', "\$mmrpg_index['items']['{$this_item_token}'] = array(\n  'item_token' => '{$this_item_token}', 'item_functions' => 'items/{$filename}',", $this_item_markup[1]);
      // Implode the markup into a single string
      $this_item_markup = implode("\n", $this_item_markup);
      // Copy this item's data to the markup cache
      $items_cache_markup[] = $this_item_markup;
    }

  }

  // Close the item data directory
  closedir($data_items);

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
//echo('check 1 <pre>'.print_r($mmrpg_index['items'], true).'</pre>'); //DEBUG

// DEBUG DEBUG DEBUG
//if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
?>