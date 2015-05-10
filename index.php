<?php
// Include the TOP file
require_once('top.php');
// Require the appropriate index file
if ($this_current_page == 'prototype'){ define('MMRPG_INDEX_GAME', true); require_once('index_game.php'); }
else { define('MMRPG_INDEX_BASE', true); require_once('index_base.php'); }
// Unset the database variable
unset($DB);
?>