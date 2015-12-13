<?php

/*
 * Mega Man RPG Prototype
 * This is the MASTER config file!!!
 */

// Collect the current domain for environment testing
@preg_match('#^([-~a-z0-9\.]+)#i', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']), $temp_domain);
$temp_domain = isset($temp_domain[0]) ? $temp_domain[0] : false;

// Define the global path variables for this installation
if ($temp_domain === false || $temp_domain == 'local.rpg.megamanpoweredup.net'){
  define('MMRPG_CONFIG_ROOTDIR', 'C:/wamp/www/rpg.megamanpoweredup.net/www/');
  define('MMRPG_CONFIG_ROOTURL', 'http://'.$temp_domain.'/');
  define('MMRPG_CONFIG_CACHE_INDEXES', false);
  define('MMRPG_CONFIG_IS_LIVE', false);
} elseif (preg_match('/^10\.0\.1/', $temp_domain)){
  define('MMRPG_CONFIG_ROOTDIR', 'C:/wamp/www/rpg.megamanpoweredup.net/www/');
  define('MMRPG_CONFIG_ROOTURL', 'http://'.$temp_domain.'/rpg.megamanpoweredup.net/www/');
  define('MMRPG_CONFIG_CACHE_INDEXES', false);
  define('MMRPG_CONFIG_IS_LIVE', false);
} elseif ($temp_domain == 'dev.megamanpoweredup.net'){
  define('MMRPG_CONFIG_ROOTDIR', '/var/www/_developer/');
  define('MMRPG_CONFIG_ROOTURL', 'http://'.$temp_domain.'/');
  define('MMRPG_CONFIG_CACHE_INDEXES', true);
  define('MMRPG_CONFIG_IS_LIVE', false);
} elseif ($temp_domain == 'rpg.megamanpoweredup.net' || $temp_domain == '107.170.13.42'){
  define('MMRPG_CONFIG_ROOTDIR', '/var/www/');
  define('MMRPG_CONFIG_ROOTURL', 'http://'.$temp_domain.'/');
  define('MMRPG_CONFIG_CACHE_INDEXES', true);
  define('MMRPG_CONFIG_IS_LIVE', true);
}

// Define the global database credentials for this installation
if ($temp_domain === false || $temp_domain == 'local.rpg.megamanpoweredup.net'){
  define('MMRPG_CONFIG_DBHOST', 'localhost');
  define('MMRPG_CONFIG_DBUSERNAME', 'root');
  define('MMRPG_CONFIG_DBPASSWORD', 'P0L1WH1RL2kTEN');
  define('MMRPG_CONFIG_DBCHARSET', 'utf8');
  define('MMRPG_CONFIG_DBNAME', 'pluto1_'.MMRPG_BUILD);
} elseif (preg_match('/^10\.0\.1/', $temp_domain)){
  define('MMRPG_CONFIG_DBHOST', 'localhost');
  define('MMRPG_CONFIG_DBUSERNAME', 'root');
  define('MMRPG_CONFIG_DBPASSWORD', 'P0L1WH1RL2kTEN');
  define('MMRPG_CONFIG_DBCHARSET', 'utf8');
  define('MMRPG_CONFIG_DBNAME', 'pluto1_'.MMRPG_BUILD);
} elseif ($temp_domain == 'dev.megamanpoweredup.net'){
  define('MMRPG_CONFIG_DBHOST', 'localhost');
  define('MMRPG_CONFIG_DBUSERNAME', 'mmrpg');
  define('MMRPG_CONFIG_DBPASSWORD', 'DUG7R102k14');
  define('MMRPG_CONFIG_DBCHARSET', 'utf8');
  define('MMRPG_CONFIG_DBNAME', MMRPG_BUILD);
} elseif ($temp_domain == 'rpg.megamanpoweredup.net' || $temp_domain == '107.170.13.42'){
  define('MMRPG_CONFIG_DBHOST', 'localhost');
  define('MMRPG_CONFIG_DBUSERNAME', 'mmrpg');
  define('MMRPG_CONFIG_DBPASSWORD', 'DUG7R102k14');
  define('MMRPG_CONFIG_DBCHARSET', 'utf8');
  define('MMRPG_CONFIG_DBNAME', MMRPG_BUILD);
}

// Define the global credentials for any web analytics accounts
define('MMRPG_ANALYTICS_ACCOUNT', 'UA-28757226-2');
define('MMRPG_ANALYTICS_DOMAIN', 'megamanpoweredup.net');

// Define the list of administrator-approved remote addresses
$temp_list = array('99.226.238.61', '99.226.253.166', '127.0.0.1', '76.188.253.244');
define('MMRPG_CONFIG_ADMIN_LIST', implode(',', $temp_list));

?>