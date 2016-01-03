<?php

// Require the application top file
require_once('_top.php');

// If someone is not supposed to be here...
if (!MMRPG_CONFIG_ADMIN_MODE){ die('You shouldn\'t be here...'); }

// Require the database top include file
//require_once('includes/include.database.php');

// Define the page title and markup variables
$this_page_title = 'Admin Panel';
$this_page_markup = '';

// Collect the current action from the URL if set
$this_page_action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'home';

/*
 * SAVE UPDATES REQUEST
 * If this is a save updating request, get to it!
 */

// Prevent timeouts and memory leakages
@ini_set('memory_limit', '128M'); //100MB
@ini_set('max_execution_time', 300); //300 seconds = 5 minutes

// If this is an EMPTY request
if ($this_page_action == 'home'){
  // Require the admin home file
  require(MMRPG_CONFIG_ROOTDIR.'admin/admin.home.php');
}
// Else if this is an UPDATE request
elseif ($this_page_action == 'update'){
  // Require the update file
  require(MMRPG_CONFIG_ROOTDIR.'admin/admin.update.php');
}
// Else if this is a PRURGE request
elseif ($this_page_action == 'purge'){
  // Require the purge file
  require(MMRPG_CONFIG_ROOTDIR.'admin/admin.purge.php');
}
// Else if this is an IMPORT ROBOTS request
elseif ($this_page_action == 'import_robots'){
  // Require the import robots file
  require(MMRPG_CONFIG_ROOTDIR.'admin/admin.import-robots.php');
}
// Else if this is an IMPORT ABILITIES request
elseif ($this_page_action == 'import_abilities'){
  // Require the import abilities file
  require(MMRPG_CONFIG_ROOTDIR.'admin/admin.import-abilities.php');
}
// Else if this is an IMPORT FIELDS request
elseif ($this_page_action == 'import_fields'){
  // Require the import fields file
  require(MMRPG_CONFIG_ROOTDIR.'admin/admin.import-fields.php');
}
// Else if this is an DELETE CACHE request
elseif ($this_page_action == 'delete_cache'){
  // Require the delete cache file
  require(MMRPG_CONFIG_ROOTDIR.'admin/admin.delete-cache.php');
}
// Otherwise, not a valid page
else {
  // Print out error 404 text
  $this_error_markup = '<strong>Error 404</strong><br />Page Not Found';
  // Require the admin home file
  require(MMRPG_CONFIG_ROOTDIR.'admin/admin.home.php');
}

// Unset the database variable
unset($db);


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Admin | Mega Man RPG Prototype | Blank | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?= MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/style.master.css?<?= MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/style.prototype.css?<?= MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/style.file.css?<?= MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style.mobile.css?<?= MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/style.prototype_mobile.css?<?= MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<script type="text/javascript" src="scripts/jquery.js?<?= MMRPG_CONFIG_CACHE_DATE?>"></script>
</head>
<body id="mmrpg" style="background-color: #F2F2F2;">
  <div id="admin" style="margin: 20px;">
    <h1 class="header" style="font-size: 20px; line-height: 40px; "><?= $this_page_title ?></h1>
    <div class="content" style="text-align: left; padding: 20px; background-color: #FFFFFF;">
      <?= $this_page_markup ?>
    </div>
  </div>
  &nbsp;
  <?php if(false){ ?>
    <pre style="text-align: left; padding: 20px;">
    <?php foreach ($_SESSION['GAME']['values']['battle_settings'] AS $player_token => $battle_settings){
      echo '<h1>'.$player_token.'</h1>'."\n";
      echo htmlentities(print_r($battle_settings), ENT_QUOTES, 'UTF-8', true);
    } ?>
    <?= htmlentities(print_r($_REQUEST), ENT_QUOTES, 'UTF-8', true) ?>
    </pre>
  <?php } ?>
  &nbsp;
</body>
</html>