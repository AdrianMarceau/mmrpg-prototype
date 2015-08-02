<?
// Default the battles index to an empty array
$mmrpg_index['battles'] = array();

// Define the cache and index paths for battles
define('MMRPG_CONFIG_BATTLES_INDEX_PATH', MMRPG_CONFIG_ROOTDIR.'data/battles/');
define('MMRPG_CONFIG_BATTLES_CACHE_PATH', MMRPG_CONFIG_ROOTDIR.'data/cache/'.'cache.battles.'.MMRPG_CONFIG_CACHE_DATE.'.php');

// If caching is turned OFF, or a cache has not been created
if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists(MMRPG_CONFIG_BATTLES_CACHE_PATH)){

  // Start indexing the battle data files
  $battles_cache_markup = mmrpg_battle::index_battle_data();

  // Implode the markup into a single string and enclose in PHP tags
  $battles_cache_markup = implode('', $battles_cache_markup);
  $battles_cache_markup = "<?\n".$battles_cache_markup."\n?>";

  // Write the index to a cache file, if caching is enabled
  $battles_cache_file = @fopen(MMRPG_CONFIG_BATTLES_CACHE_PATH, 'w');
  if (!empty($battles_cache_file)){
    @fwrite($battles_cache_file, $battles_cache_markup);
    @fclose($battles_cache_file);
  }

}

// Include the cache file so it can be evaluated
require_once(MMRPG_CONFIG_BATTLES_CACHE_PATH);

// Additionally, include any dynamic session-based battles
if (!empty($_SESSION['GAME']['values']['battle_index'])){
  // The session-based battles exist, so merge them with the index
  $mmrpg_index['battles'] = array_merge($mmrpg_index['battles'], $_SESSION['GAME']['values']['battle_index']);
}

// If debug is requested, print the cache data
if (!empty($_GET['debug']) && $_GET['debug'] == 'index_battles'){
  die('<pre>'.print_r($mmrpg_index['battles'], true).'</pre>'); //DEBUG
}

?>