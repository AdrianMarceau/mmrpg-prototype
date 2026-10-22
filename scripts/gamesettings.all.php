<?

// Require the top file if not already included
require_once(dirname(dirname(__FILE__)).'/top.php');

// If the return value was set explicitly as javascript, update headers
$return_type = !empty($_REQUEST['return']) && is_string($_REQUEST['return']) ? $_REQUEST['return'] : 'default';
if ($return_type === 'javascript'){
    // Explicitly set the content type as javascript
    header('Content-type: text/javascript;');
    // Ensure this settings file is never cached as it changes
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

// Define default include flags and check for existence of override variables
$gamesettings_autoScrollTop = false;
if (isset($gamesettings_autoScrollTop) && !empty($gamesettings_autoScrollTop)){ $gamesettings_autoScrollTop = true; }

// Print out the include markup for each of the game settings scripts
if ($return_type === 'default'){
    ?>
    <script type="text/javascript" src="/scripts/gamesettings.main.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="/scripts/gamesettings.music.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="/scripts/gamesettings.sounds.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript"> gameSettings.autoScrollTop = <?= $gamesettings_autoScrollTop ? 'true' : 'false'; ?>; </script>
    <?
} else if ($return_type === 'javascript'){
    require(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.main.js.php');
    require(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.music.js.php');
    require(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.sounds.js.php');
    echo('gameSettings.autoScrollTop = '.($gamesettings_autoScrollTop ? 'true' : 'false').';'.PHP_EOL);
} else {
    http_response_code(404);
    echo('# 404 Not Found');
    echo('# Unrecognized return type');
    exit();
}
?>
