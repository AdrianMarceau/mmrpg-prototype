<?php
// Define a class for the type objects
class rpg_type {

  // Define the internal database cache
  public static $database_index = array();

  // Define the constructor class
  public function __construct(){ }


  // -- INDEX FUNCTIONS -- //

  /**
   * Get the entire type index array with parsed info
   * @param bool $session
   * @return array
   */
  public static function get_index($session = true){
    // Load the type index if not
    self::load_type_index();
    $this_index = self::$database_index;
    return $this_index;
  }

  /**
   * Request type info from the global index via type token
   * @param string $type_token
   * @return array
   */
  public static function get_index_info($type_token, $session = true){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_index = self::get_index($session);

    // If the requested type is in the index, return the entry
    if (!empty($this_index[$type_token])){
      // Decode the info and return the array
      $type_info = $this_index[$type_token]; //json_decode($this_index[$type_token], true);
    }
    // Otherwise if the type index doesn't exist at all
    else {
      // Return empty array on failure
      $type_info = array();
    }

    //die('<pre>get_index_info('.$type_token.') = '.print_r($type_info, true).'</pre>');

    // Return the type info
    return $type_info;

  }

  /**
   * Load the type index from the session or cache file and return success
   * @param bool include_session
   * @return bool
   */
  public static function load_type_index($include_session = true){

    // Collect references to global objects
    $this_database = cms_database::get_database();
    $this_index = array();

    // Default the types index to an empty array
    $types_index = array();

    // If caching is turned OFF, or a cache has not been created
    if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists(MMRPG_CONFIG_TYPES_CACHE_PATH)){
      // Start indexing the type data files
      $types_cache_markup = self::index_type_data();
      // Implode the markup into a single string and enclose in PHP tags
      $types_cache_markup = implode('', $types_cache_markup);
      $types_cache_markup = "<?php\n".$types_cache_markup."\n?>";
      // Write the index to a cache file, if caching is enabled
      $types_cache_file = @fopen(MMRPG_CONFIG_TYPES_CACHE_PATH, 'w');
      if (!empty($types_cache_file)){
        @fwrite($types_cache_file, $types_cache_markup);
        @fclose($types_cache_file);
      }
    }

    // Include the cache file so it can be evaluated
    require_once(MMRPG_CONFIG_TYPES_CACHE_PATH);

    //die('<pre>$types_index => '.print_r($types_index, true).'</pre>');

    // Return false if we got nothing from the index
    if (empty($types_index)){ return array(); }

    // Loop through the types and index them after serializing
    foreach ($types_index AS $token => $array){
      $this_index[$token] = $array; //json_encode($array);
    }

    // Additionally, include any dynamic session-based types
    if (!empty($include_session) && !empty($_SESSION['GAME']['values']['type_index'])){
      // The session-based types exist, so merge them with the index
      $this_index = array_merge($this_index, $_SESSION['GAME']['values']['type_index']);
    }

    //echo('<pre>self::$database_index = $this_index => '.print_r($this_index, true).'</pre>');

    // Update the internal index
    self::$database_index = $this_index;

    // Return the index on success
    return true;

  }

  /**
   * Generate the type index cache file by scanning the filesystem and return markup
   * @param string $this_path
   * @return string
   */
  public static function index_type_data($this_path = ''){

    // Default the types markup index to an empty array
    $types_cache_markup = array();

    // Open the type data directory for scanning
    $data_types  = opendir(MMRPG_CONFIG_TYPES_INDEX_PATH.$this_path);

    // Loop through all the files in the directory
    while (false !== ($filename = readdir($data_types))) {

      // Skip if invalid directory
      if ($filename == '.' || $filename == '..'){ continue; }

      // If this is a directory, initiate a recusive scan
      if (is_dir(MMRPG_CONFIG_TYPES_INDEX_PATH.$this_path.$filename.'/') && $filename != '.' && $filename != '..'){
        // Collect the markup from the recursive scan
        $append_cache_markup = self::index_type_data($this_path.$filename.'/');
        // If markup was found, append if to the main container
        if (!empty($append_cache_markup)){ $types_cache_markup = array_merge($types_cache_markup, $append_cache_markup); }
      }
      // Else, ensure the file matches the naming format
      elseif ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
        // Collect the type token from the filename
        $this_type_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
        if (!empty($this_path)){ $this_type_token = trim(str_replace('/', '-', $this_path), '-').'-'.$this_type_token; }

        // Read the file into memory as a string and crop slice out the imporant part
        $this_type_markup = trim(file_get_contents(MMRPG_CONFIG_TYPES_INDEX_PATH.$this_path.$filename));
        $this_type_markup = explode("\n", $this_type_markup);
        $this_type_markup = array_slice($this_type_markup, 1, -1);
        // Replace the first line with the appropriate index key
        $this_type_markup[1] = preg_replace('#\$type = array\(#i', "\$types_index['{$this_type_token}'] = array(\n  'type_token' => '{$this_type_token}',\n  'type_functions' => 'types/{$this_path}{$filename}',", $this_type_markup[1]);
        // Implode the markup into a single string
        $this_type_markup = implode("\n", $this_type_markup);
        // Copy this type's data to the markup cache
        $types_cache_markup[] = $this_type_markup;
      }

    }

    // Close the type data directory
    closedir($data_types);

    // Return the generated cache markup
    return $types_cache_markup;

  }

}
?>