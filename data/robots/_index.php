<?
// Default the robots index to an empty array
$mmrpg_index['robots'] = array();

// Define the cache and index paths for robots
$robots_index_path = MMRPG_CONFIG_ROOTDIR.'data/robots/';
$robots_cache_path = MMRPG_CONFIG_ROOTDIR.'data/cache/'.'cache.robots.'.MMRPG_CONFIG_CACHE_DATE.'.php';

// Define the function used for scanning the robot directory
function index_robot_data($this_path = ''){

  // Define references to the global variables
  global $robots_index_path, $robots_cache_path;

  // Default the robots markup index to an empty array
  $robots_cache_markup = array();

  // Open the type data directory for scanning
  $data_robots  = opendir($robots_index_path.$this_path);

  //echo 'Scanning '.$robots_index_path.$this_path.'<br />';

  // Loop through all the files in the directory
  while (false !== ($filename = readdir($data_robots))) {

    // If this is a directory, initiate a recusive scan
    if (is_dir($robots_index_path.$this_path.$filename.'/') && $filename != '.' && $filename != '..'){
      // Collect the markup from the recursive scan
      $append_cache_markup = index_robot_data($this_path.$filename.'/');
      // If markup was found, append if to the main container
      if (!empty($append_cache_markup)){ $robots_cache_markup = array_merge($robots_cache_markup, $append_cache_markup); }
    }
    // Else, ensure the file matches the naming format
    elseif ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
      // Collect the robot token from the filename
      $this_robot_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
      //if (!empty($this_path)){ $this_robot_token = trim(str_replace('/', '-', $this_path), '-').'-'.$this_robot_token; }

      //echo '+ Adding robot token '.$this_robot_token.'...<br />';

      // Read the file into memory as a string and crop slice out the imporant part
      $this_robot_markup = trim(file_get_contents($robots_index_path.$this_path.$filename));
      $this_robot_markup = explode("\n", $this_robot_markup);
      $this_robot_markup = array_slice($this_robot_markup, 1, -1);
      // Replace the first line with the appropriate index key
      $this_robot_markup[1] = preg_replace('#\$robot = array\(#i', "\$mmrpg_index['robots']['{$this_robot_token}'] = array(\n  'robot_token' => '{$this_robot_token}', 'robot_functions' => 'robots/{$this_path}{$filename}',", $this_robot_markup[1]);
      // Implode the markup into a single string
      $this_robot_markup = implode("\n", $this_robot_markup);
      // Copy this robot's data to the markup cache
      $robots_cache_markup[] = $this_robot_markup;
    }

  }

  // Close the robot data directory
  closedir($data_robots);

  // Return the generated cache markup
  return $robots_cache_markup;

}

// If caching is turned OFF, or a cache has not been created
if (true){ // !MMRPG_CONFIG_CACHE_INDEXES || !file_exists($robots_cache_path) robot data is cached in the database now, so always recreate the file first

  // Start indexing the robot data files
  $robots_cache_markup = index_robot_data();

  // Implode the markup into a single string and enclose in PHP tags
  $robots_cache_markup = implode('', $robots_cache_markup);
  $robots_cache_markup = "<?\n".$robots_cache_markup."\n?>";

  // Write the index to a cache file, if caching is enabled
  $robots_cache_file = @fopen($robots_cache_path, 'w');
  if (!empty($robots_cache_file)){
    @fwrite($robots_cache_file, $robots_cache_markup);
    @fclose($robots_cache_file);
  }

}

// Include the cache file so it can be evaluated
require_once($robots_cache_path);

// Additionally, include any dynamic session-based robots
if (!empty($_SESSION[mmrpg_game_token()]['values']['robot_index'])){
  // The session-based robots exist, so merge them with the index
  $mmrpg_index['robots'] = array_merge($mmrpg_index['robots'], $_SESSION[mmrpg_game_token()]['values']['robot_index']);
}

// If debug is requested, print the cache data
if (!empty($_GET['debug']) && $_GET['debug'] == 'index_robots'){
  die('<pre>'.print_r($mmrpg_index['robots'], true).'</pre>'); //DEBUG
}

?>