<?
// Default the types index to an empty array
$mmrpg_index['types'] = array();

// Define the cache and index paths for types
$types_index_path = MMRPG_CONFIG_ROOTDIR.'data/types/';
$types_cache_path = MMRPG_CONFIG_ROOTDIR.'data/cache/'.'cache.types.'.MMRPG_CONFIG_CACHE_DATE.'.php';

// If caching is turned OFF, or a cache has not been created
if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists($types_cache_path)){

  // Default the types markup index to an empty array
  $types_cache_markup = array();

  // Open the type data directory for scanning
  $data_types  = opendir($types_index_path);

  // Loop through all the files in the directory
  while (false !== ($filename = readdir($data_types))) {

    // Ensure the file matches the naming format
    if ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
      // Collect the type token from the filename
      $this_type_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
      // Read the file into memory as a string and crop slice out the imporant part
      $this_type_markup = trim(file_get_contents($types_index_path.$filename));
      $this_type_markup = explode("\n", $this_type_markup);
      $this_type_markup = array_slice($this_type_markup, 1, -1);
      // Replace the first line with the appropriate index key
      $this_type_markup[1] = preg_replace('#\$type = array\(#i', "\$mmrpg_index['types']['{$this_type_token}'] = array(\n  'type_token' => '{$this_type_token}', 'type_functions' => 'types/{$filename}',", $this_type_markup[1]);
      // Implode the markup into a single string
      $this_type_markup = implode("\n", $this_type_markup);
      // Copy this type's data to the markup cache
      $types_cache_markup[] = $this_type_markup;
    }

  }

  // Close the type data directory
  closedir($data_types);

  // Implode the markup into a single string and enclose in PHP tags
  $types_cache_markup = implode('', $types_cache_markup);
  $types_cache_markup = "<?\n".$types_cache_markup."\n?>";

  // Write the index to a cache file, if caching is enabled
  $types_cache_file = @fopen($types_cache_path, 'w');
  if (!empty($types_cache_file)){
    @fwrite($types_cache_file, $types_cache_markup);
    @fclose($types_cache_file);
  }

}

// Include the cache file so it can be evaluated
require_once($types_cache_path);

?>