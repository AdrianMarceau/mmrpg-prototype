<?php

// Require the top file for all admin scripts
require_once('common/top.php');

// Collect the cron request kind and then validate it for security
$allowed_cron_kinds = array('git-pull', 'git-push');
$cron_kind = !empty($_REQUEST['kind']) && preg_match('/^[\.\-_a-z0-9]+$/', $_REQUEST['kind']) ? $_REQUEST['kind'] : false;
if (!in_array($cron_kind, $allowed_cron_kinds)){ exit('invalid request'); }

// Define the path to the cron list file for checking
$list_file = MMRPG_CONFIG_ROOTDIR.".cache/admin/cron_{$cron_kind}-pending.list";
$list_status = 'pending';
if (!file_exists($list_file)) {
    $list_status = 'completed';
}

// Automatically increment the config timestamp to force-refresh assets
if ($list_status === 'completed'){ cms_admin::refresh_global_cache(); }

// Exit now that we're done checking (simple, right?)
exit($list_status);

?>
