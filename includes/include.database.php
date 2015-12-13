<?php

// Require the types database
if (!defined('DATABASE_SKIP_TYPES')){
  require(MMRPG_CONFIG_ROOTDIR.'database/database.types.php');
}

// Require the player database
if (!defined('DATABASE_SKIP_PLAYERS')){
  require(MMRPG_CONFIG_ROOTDIR.'database/database.players.php');
}

// Require the robots database
if (!defined('DATABASE_SKIP_ROBOTS')){
  require(MMRPG_CONFIG_ROOTDIR.'database/database.robots.php');
}

// Require the mechas database
if (!defined('DATABASE_SKIP_MECHAS')){
  require(MMRPG_CONFIG_ROOTDIR.'database/database.mechas.php');
}

// Require the bosses database
if (!defined('DATABASE_SKIP_BOSSES')){
  require(MMRPG_CONFIG_ROOTDIR.'database/database.bosses.php');
}

// Require the abilities database
if (!defined('DATABASE_SKIP_ABILITIES')){
  require(MMRPG_CONFIG_ROOTDIR.'database/database.abilities.php');
}

// Require the fields database
if (!defined('DATABASE_SKIP_FIELDS')){
  require(MMRPG_CONFIG_ROOTDIR.'database/database.fields.php');
}

// Require the items database
if (!defined('DATABASE_SKIP_ITEMS')){
  require(MMRPG_CONFIG_ROOTDIR.'database/database.items.php');
}

?>