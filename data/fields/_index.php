<?
// Default the fields index to an empty array
$mmrpg_index['fields'] = array();

// Define the cache and index paths for fields
$fields_index_path = MMRPG_CONFIG_ROOTDIR.'data/fields/';
$fields_cache_path = MMRPG_CONFIG_ROOTDIR.'data/cache/'.'cache.fields.'.MMRPG_CONFIG_CACHE_DATE.'.php';

// If caching is turned OFF, or a cache has not been created
if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists($fields_cache_path)){

  // Default the fields markup index to an empty array
  $fields_cache_markup = array();

  // Open the type data directory for scanning
  $data_fields  = opendir($fields_index_path);

  // Loop through all the files in the directory
  while (false !== ($filename = readdir($data_fields))) {

    // Ensure the file matches the naming format
    if ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
      // Collect the field token from the filename
      $this_field_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
      // Read the file into memory as a string and crop slice out the imporant part
      $this_field_markup = trim(file_get_contents($fields_index_path.$filename));
      $this_field_markup = explode("\n", $this_field_markup);
      $this_field_markup = array_slice($this_field_markup, 1, -1);
      // Replace the first line with the appropriate index key
      $this_field_markup[1] = preg_replace('#\$field = array\(#i', "\$mmrpg_index['fields']['{$this_field_token}'] = array(\n  'field_token' => '{$this_field_token}', 'field_functions' => 'fields/{$filename}',", $this_field_markup[1]);
      // Implode the markup into a single string
      $this_field_markup = implode("\n", $this_field_markup);
      // Copy this field's data to the markup cache
      $fields_cache_markup[] = $this_field_markup;
    }

  }

  // Close the field data directory
  closedir($data_fields);

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
//echo('check 1 <pre>'.print_r($mmrpg_index['fields'], true).'</pre>'); //DEBUG

?>