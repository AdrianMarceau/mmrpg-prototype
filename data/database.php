<?
// DEBUG DEBUG DEBUG
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

// Require the types database
require(MMRPG_CONFIG_ROOTDIR.'data/database_types.php');

// Require the player database
require(MMRPG_CONFIG_ROOTDIR.'data/database_players.php');

// Require the robots database
require(MMRPG_CONFIG_ROOTDIR.'data/database_robots.php');

// Require the mechas database
require(MMRPG_CONFIG_ROOTDIR.'data/database_mechas.php');

// Require the bosses database
require(MMRPG_CONFIG_ROOTDIR.'data/database_bosses.php');

// Require the abilities database
require(MMRPG_CONFIG_ROOTDIR.'data/database_abilities.php');

// Require the fields database
require(MMRPG_CONFIG_ROOTDIR.'data/database_fields.php');

// Require the items database
require(MMRPG_CONFIG_ROOTDIR.'data/database_items.php');

?>