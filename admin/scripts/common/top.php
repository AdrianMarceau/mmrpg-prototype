<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../../top.php');
require_once(MMRPG_CONFIG_ROOTDIR.'classes/cms_admin.php');

// Require the common parameters and functions files for admin scripts
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/common/params.php');
require_once(MMRPG_CONFIG_ROOTDIR.'admin/scripts/common/functions.php');

// Ensure the user is actually logged in as an admin
if (!defined('MMRPG_CONFIG_ADMIN_MODE')
    || MMRPG_CONFIG_ADMIN_MODE !== true){
    exit_action('error|user not logged in or not admin');
}

?>