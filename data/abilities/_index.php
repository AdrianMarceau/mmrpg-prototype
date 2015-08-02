<?
// Default the abilities index to an empty array
$mmrpg_index['abilities'] = array();

// Define the cache and index paths for abilities
$abilities_index_path = MMRPG_CONFIG_ROOTDIR.'data/abilities/';
$abilities_cache_path = MMRPG_CONFIG_ROOTDIR.'data/cache/'.'cache.abilities.'.MMRPG_CONFIG_CACHE_DATE.'.php';

// Define the function used for scanning the ability directory
$data_key = 0;
function index_ability_data($this_path = ''){

  // Define references to the global variables
  global $abilities_index_path, $abilities_cache_path, $data_key;

  // Default the abilities markup index to an empty array
  $abilities_cache_markup = array();

  // Open the type data directory for scanning
  $data_abilities  = opendir($abilities_index_path.$this_path);

  // Loop through all the files in the directory
  while (false !== ($filename = readdir($data_abilities))) {

    // If this is a directory, initiate a recusive scan
    if (is_dir($abilities_index_path.$this_path.$filename.'/') && $filename != '.' && $filename != '..'){
      // Collect the markup from the recursive scan
      $append_cache_markup = index_ability_data($this_path.$filename.'/');
      // If markup was found, append if to the main container
      if (!empty($append_cache_markup)){ $abilities_cache_markup = array_merge($abilities_cache_markup, $append_cache_markup); }
    }
    // Else, ensure the file matches the naming format
    elseif ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
      // Increment the data key
      $data_key++;
      // Collect the ability token from the filename
      $this_ability_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
      //if (!empty($this_path)){ $this_ability_token = trim(str_replace('/', '-', $this_path), '-').'-'.$this_ability_token; }

      // Read the file into memory as a string and crop slice out the imporant part
      $this_ability_markup = trim(file_get_contents($abilities_index_path.$this_path.$filename));
      $this_ability_markup = explode("\n", $this_ability_markup);
      $this_ability_markup = array_slice($this_ability_markup, 1, -1);
      // Replace the first line with the appropriate index key
      $this_ability_markup[1] = preg_replace('#\$ability = array\(#i', "\$mmrpg_index['abilities']['{$this_ability_token}'] = array(\n  'ability_id' => '{$data_key}',\n  'ability_token' => '{$this_ability_token}', 'ability_functions' => 'abilities/{$this_path}{$filename}',", $this_ability_markup[1]);
      // Implode the markup into a single string
      $this_ability_markup = implode("\n", $this_ability_markup);
      // Copy this ability's data to the markup cache
      $abilities_cache_markup[] = $this_ability_markup;
    }

  }

  // Close the ability data directory
  closedir($data_abilities);

  // Return the generated cache markup
  return $abilities_cache_markup;

}

// If caching is turned OFF, or a cache has not been created
if (true){

  // Start indexing the ability data files
  $abilities_cache_markup = index_ability_data();

  // Implode the markup into a single string and enclose in PHP tags
  $abilities_cache_markup = implode('', $abilities_cache_markup);
  $abilities_cache_markup = "<?\n".$abilities_cache_markup."\n?>";

  // Write the index to a cache file, if caching is enabled
  $abilities_cache_file = @fopen($abilities_cache_path, 'w');
  if (!empty($abilities_cache_file)){
    @fwrite($abilities_cache_file, $abilities_cache_markup);
    @fclose($abilities_cache_file);
  }

}

// Include the cache file so it can be evaluated
require_once($abilities_cache_path);

// Additionally, include any dynamic session-based abilities
if (!empty($_SESSION['GAME']['values']['ability_index'])){
  // The session-based abilities exist, so merge them with the index
  $mmrpg_index['abilities'] = array_merge($mmrpg_index['abilities'], $_SESSION['GAME']['values']['ability_index']);
}

?>