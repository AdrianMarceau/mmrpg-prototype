<?
// Default the players index to an empty array
$mmrpg_index['players'] = array();

// Define the cache and index paths for players
$players_index_path = MMRPG_CONFIG_ROOTDIR.'data/players/';
$players_cache_path = MMRPG_CONFIG_ROOTDIR.'data/cache/'.'cache.players.'.MMRPG_CONFIG_CACHE_DATE.'.php';

// If caching is turned OFF, or a cache has not been created
if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists($players_cache_path)){

  // Default the players markup index to an empty array
  $players_cache_markup = array();

  // Open the type data directory for scanning
  $data_players  = opendir($players_index_path);

  // Loop through all the files in the directory
  while (false !== ($filename = readdir($data_players))) {

    // Ensure the file matches the naming format
    if ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
      // Collect the player token from the filename
      $this_player_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
      // Read the file into memory as a string and crop slice out the imporant part
      $this_player_markup = trim(file_get_contents($players_index_path.$filename));
      $this_player_markup = explode("\n", $this_player_markup);
      $this_player_markup = array_slice($this_player_markup, 1, -1);
      // Replace the first line with the appropriate index key
      $this_player_markup[1] = preg_replace('#\$player = array\(#i', "\$mmrpg_index['players']['{$this_player_token}'] = array(\n  'player_token' => '{$this_player_token}', 'player_functions' => 'players/{$filename}',", $this_player_markup[1]);
      // Implode the markup into a single string
      $this_player_markup = implode("\n", $this_player_markup);
      // Copy this player's data to the markup cache
      $players_cache_markup[] = $this_player_markup;
    }

  }

  // Close the player data directory
  closedir($data_players);

  // Implode the markup into a single string and enclose in PHP tags
  $players_cache_markup = implode('', $players_cache_markup);
  $players_cache_markup = "<?\n".$players_cache_markup."\n?>";

  // Write the index to a cache file, if caching is enabled
  $players_cache_file = @fopen($players_cache_path, 'w');
  if (!empty($players_cache_file)){
    @fwrite($players_cache_file, $players_cache_markup);
    @fclose($players_cache_file);
  }

}

// Include the cache file so it can be evaluated
if (!file_exists($players_cache_path)){ die('Fatal Error! The file '.$players_cache_path.' could not be created!'); }
require_once($players_cache_path);

?>