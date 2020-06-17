<?

// Require the top file for paths and stuff
require('../top.php');

// Define the header type so it's easier to display stuff
header('Content-type: text/plain;');

// ONLY allow this file to run locally
if (defined('MMRPG_CONFIG_IS_LIVE') && MMRPG_CONFIG_IS_LIVE === true){
    die('This migration script can ONLY be run locally!!!');
}

// Start the output buffer now, we'll flush manually as we go
ob_implicit_flush(true);
ob_start();

// Require the function definitions needed for migration stuff
require('migrate-objects_xfunctions.php');

// Proceed based on the KIND of object we're migrating
$allowed_modes = array('full', 'update');
$allowed_migration_kinds = array('abilities', 'battles', 'fields', 'items', 'players', 'robots', 'types');
$migration_kind = !empty($_REQUEST['kind']) && in_array($_REQUEST['kind'], $allowed_migration_kinds) ? trim($_REQUEST['kind']) : false;
$migration_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) && $_REQUEST['limit'] > 0 ? (int)(trim($_REQUEST['limit'])) : 0;
$migration_filter = !empty($_REQUEST['filter']) && is_string($_REQUEST['filter']) ? explode(',', strtolower(trim($_REQUEST['filter']))) : array();
$migration_mode = !empty($_REQUEST['mode']) ? strtolower(trim($_REQUEST['mode'])) : $allowed_modes[0];
if (!empty($migration_kind)){ $migration_kind_singular = substr($migration_kind, -3, 3) === 'ies' ? str_replace('ies', 'y', $migration_kind) : rtrim($migration_kind, 's'); }
else { $migration_kind_singular = false; }
if (!empty($migration_kind) && file_exists('migrate-objects_'.$migration_kind.'.php')){
    require_once('migrate-objects_'.$migration_kind.'.php');
} else {
    ob_echo('Migration kind "'.$migration_kind.'" not supported or file not ready yet!');
}

// Empty the output buffer (or whatever is left)
ob_end_flush();


?>