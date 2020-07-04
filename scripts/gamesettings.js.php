<?
// Print out common game settings (the script wrappers are assumed there already)
echo('gameSettings.baseHref = "'.MMRPG_CONFIG_ROOTURL.'";'.PHP_EOL);
echo('gameSettings.wapFlag = '.($flag_wap ? 'true' : 'false').';'.PHP_EOL);
echo('gameSettings.cacheTime = "'.MMRPG_CONFIG_CACHE_DATE.'";'.PHP_EOL);
echo('gameSettings.eventTimeout = '.($flag_wap ? 700 : 900).';'.PHP_EOL);
echo('gameSettings.fadeIn = '.(isset($_GET['fadein']) ? $_GET['fadein'] : 'false').';'.PHP_EOL);
if (defined('MMRPG_CONFIG_CDN_ENABLED') && MMRPG_CONFIG_CDN_ENABLED === true){
    $audioBaseHref = MMRPG_CONFIG_CDN_ROOTURL.MMRPG_CONFIG_CDN_PROJECT.'/';
    echo('gameSettings.audioBaseHref = "'.$audioBaseHref.'";'.PHP_EOL);
}
?>