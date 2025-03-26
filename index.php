<?php

// If a 404 NOT FOUND FILE request has someone made it to this page, stop it here
if (!empty($_SERVER['REQUEST_URI'])
    && preg_match('/^\/(images|sounds)\//i', $_SERVER['REQUEST_URI'])
    && preg_match('/(\.[a-z0-9]{3,4})($|\?)/i', $_SERVER['REQUEST_URI'])){
    http_response_code(404);
    exit();
}

// Include the TOP file
require_once('top.php');

// Check to see if we are currently viewing this page in print mode
$is_print_view = !empty($_GET['view']) && $_GET['view'] === 'print' ? true : false;
define('MMRPG_INDEX_PRINT_VIEW', $is_print_view);
define('MMRPG_INDEX_FULL_VIEW', !$is_print_view);

// Require the appropriate index file
if ($this_current_page == 'prototype'){ define('MMRPG_INDEX_GAME', true); require_once('index_game.php'); }
else { define('MMRPG_INDEX_BASE', true); require_once('index_base.php'); }

// Unset the database variable
unset($db);

?>