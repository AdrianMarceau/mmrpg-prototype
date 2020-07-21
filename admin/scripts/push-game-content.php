<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../../top.php');
require_once(MMRPG_CONFIG_ROOTDIR.'classes/cms_admin.php');

// Require the common git actions file
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/git_common.php');
debug_echo('push-game-content'.PHP_EOL);

// If the "all" token was explicitly provided, we're going to commit and push everything
if ($request_token === 'all'){

    debug_echo('push everything!'.PHP_EOL);

}
// Else we're only going to commit and push items that match the provided token
else {

    debug_echo('push only '.$request_token.'!'.PHP_EOL);

}

?>