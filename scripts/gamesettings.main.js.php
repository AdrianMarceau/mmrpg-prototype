<?

// Require the top file if not already included
define('READ_ONLY_SESSION', true);
require_once(dirname(dirname(__FILE__)).'/top.php');
$GAME_SESSION = $_SESSION['GAME'];
session_write_close();

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

// Print out common game settings (the script wrappers are assumed there already)
echo('gameSettings.baseHref = "'.MMRPG_CONFIG_ROOTURL.'";'.PHP_EOL);
echo('gameSettings.wapFlag = '.($flag_wap ? 'true' : 'false').';'.PHP_EOL);
echo('gameSettings.cacheTime = "'.MMRPG_CONFIG_CACHE_DATE.'";'.PHP_EOL);
echo('gameSettings.fadeIn = '.(isset($_GET['fadein']) ? $_GET['fadein'] : 'false').';'.PHP_EOL);
echo('gameSettings.allowEditing = '.(!defined('MMRPG_REMOTE_GAME_ID') || MMRPG_REMOTE_GAME_ID === 0 ? 'true' : 'false').';'.PHP_EOL);
if (defined('MMRPG_CONFIG_CDN_ENABLED') && MMRPG_CONFIG_CDN_ENABLED === true){
    $audioBaseHref = MMRPG_CONFIG_CDN_ROOTURL.MMRPG_CONFIG_CDN_PROJECT.'/';
    echo('gameSettings.audioBaseHref = "'.$audioBaseHref.'";'.PHP_EOL);
}

// Update the event timeout setting if set
$event_timeout = !empty($GAME_SESSION['battle_settings']['eventTimeout']) ? $GAME_SESSION['battle_settings']['eventTimeout'] : 0;
if (!empty($event_timeout)){ echo "gameSettings.eventTimeout = {$event_timeout};\n"; }

// Update the audio balance config setting if set
$audio_balance_config = !empty($GAME_SESSION['battle_settings']['audioBalanceConfig']) ? $GAME_SESSION['battle_settings']['audioBalanceConfig'] : '';
if (!empty($audio_balance_config)){
    echo "gameSettings.audioBalanceConfig = ".json_encode($audio_balance_config).";\n";
    echo "gameSettings.masterVolume = ".$audio_balance_config['masterVolume'].";\n";
    echo "gameSettings.musicVolume = ".$audio_balance_config['musicVolume'].";\n";
    echo "gameSettings.effectVolume = ".$audio_balance_config['effectVolume'].";\n";
}

// Update any animation effects that have been defined in the session
$animation_effects_index = rpg_canvas::get_animation_effects_index();
if (!empty($animation_effects_index)){
    foreach ($animation_effects_index AS $effect_key => $effect_info){
        $setting_token = $effect_info['token'];
        $setting_value = $effect_info['default'];
        if (isset($GAME_SESSION['battle_settings'][$setting_token])){
            $value = $GAME_SESSION['battle_settings'][$setting_token];
            $setting_value = $value === 'true' ? true : false;
        }
        $setting_value_js = $setting_value ? 'true' : 'false';
        echo "gameSettings.{$setting_token} = {$setting_value_js};\n";
    }
}

?>