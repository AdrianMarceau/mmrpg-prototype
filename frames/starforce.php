<?
// Require the application top file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
define('MMRPG_REMOTE_SKIP_INDEX', true);
define('MMRPG_REMOTE_SKIP_COMPLETE', true);
define('MMRPG_REMOTE_SKIP_FAILURE', true);
define('MMRPG_REMOTE_SKIP_SETTINGS', true);
define('MMRPG_REMOTE_SKIP_ITEMS', true);
define('MMRPG_REMOTE_SKIP_DATABASE', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$session_token = mmrpg_game_token();

// Require the prototype data file
//require_once('../data/prototype.php');
// Require the starforce data file
require_once('../data/starforce.php');
// Collect the editor flag if set
$global_allow_editing = isset($_GET['edit']) && $_GET['edit'] == 'false' ? false : true;

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Rulebook | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/starforce.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/starforce.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
gameSettings.fadeIn = <?= isset($_GET['fadein']) ? $_GET['fadein'] : 'false' ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?= MMRPG_CONFIG_CACHE_DATE ?>';
gameSettings.autoScrollTop = false;
</script>
</head>
<body id="mmrpg" class="iframe" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">

  <div id="prototype" class="<?= empty($this_start_key) ? 'hidden' : '' ?>" style="<?= !$global_allow_editing ? 'width: 100% !important; ' : '' ?>">

    <div class="menu">

      <?
      $this_battle_stars_boost = 0;
      foreach ($this_star_force AS $force_type => $force_count){ $this_battle_stars_boost += ($force_count * 10); }
      $temp_field_stars_text = $this_battle_stars_field_count == 1 ? '1 Field Star' : $this_battle_stars_field_count.' Field Stars';
      $temp_fusion_stars_text = $this_battle_stars_fusion_count == 1 ? '1 Fusion Star' : $this_battle_stars_fusion_count.' Fusion Stars';
      $temp_total_stars_label = $this_battle_stars_count == 1 ? '1 Star' : $this_battle_stars_count.' Stars';
      $temp_total_stars_text = $temp_total_stars_label.' Total';
      $temp_total_boost_label = '+'.$this_battle_stars_boost.'% Boost';
      $temp_total_boost_text = '+'.$this_battle_stars_boost.'% Starforce Boost';
      ?>
      <span class="header block_1">Star Force <span style="opacity: 0.25;">(
        <span title="<?= $temp_field_stars_text ?>" data-tooltip-type="field_type field_type_none"><?= $this_battle_stars_field_count ?></span> /
        <span title="<?= $temp_fusion_stars_text ?>" data-tooltip-type="field_type field_type_none"><?= $this_battle_stars_fusion_count ?></span> /
        <span title="<?= $temp_total_stars_text ?>" data-tooltip-type="field_type field_type_none"><?= $temp_total_stars_label ?></span> /
        <span title="<?= $temp_total_boost_text ?>" data-tooltip-type="field_type field_type_none"><?= $temp_total_boost_label ?></span>
        )</span></span>

      <div class="starforce">
        <div class="wrapper" style="<?= $flag_wap ? 'margin-right: 0;' : '' ?>">

          <div class="types_container" style="">
            <?

            // Loop through all the field stars and print them out one-by-one
            if (!empty($this_star_force)){

              //echo('[total] => '.$this_star_force_total);
              //echo('<pre>'.print_r($this_star_force, true).'</pre>');

              $temp_max_force = 0;
              $temp_max_padding = 30;
              foreach ($this_star_force AS $force_type => $force_count){
                $temp_padding_amount = $force_count * 2;
                if ($force_count > $temp_max_force){ $temp_max_force = $force_count; }
                if ($temp_padding_amount > 117){ $temp_max_padding = 117; }
              }


              foreach ($this_star_force AS $force_type => $force_count){
                //$temp_padding_amount = 6 + ceil(200 * ($force_count / $this_star_force_total));
                //if ($temp_padding_amount > 117){ $temp_padding_amount = 117; }
                $temp_padding_amount = $force_count * 2;
                $temp_padding_amount = ceil(($force_count / $temp_max_force) * $temp_max_padding);
                $force_count_strict = $this_star_force_strict[$force_type];
                echo '<div data-tooltip="'.ucfirst($force_type).' +'.($force_count * 10).'% | '.$force_count_strict.' / '.$this_battle_stars_count.' Stars" class="field_type field_type_'.$force_type.'" style="padding-right: '.$temp_padding_amount.'px;" data-padding="'.$temp_padding_amount.'">'.ucfirst($force_type).' +'.($force_count * 10).'%</div>';
              }


            }


            ?>
          </div>

          <div class="stars_container <?= ($this_battle_stars_count > 200 ? 'stars_container_plus200' : ($this_battle_stars_count > 100 ? 'stars_container_plus100' : ''))  ?>" style="">
            <?

            // Loop through all the field stars and print them out one-by-one
            if (!empty($this_battle_stars)){

              function mmrpg_prototype_sort_stars($starA, $starB){
                global $this_star_force;
                $this_star_force_keys = array_keys($this_star_force);
                $this_star_force_keys[] = '';
                $typeA1Key = array_search($starA['star_type'], $this_star_force_keys);
                $typeA2Key = array_search($starA['star_type2'], $this_star_force_keys);
                $typeB1Key = array_search($starB['star_type'], $this_star_force_keys);
                $typeB2Key = array_search($starB['star_type2'], $this_star_force_keys);
                if ($typeA1Key < $typeB1Key){ return -1; }
                elseif ($typeA1Key > $typeB1Key){ return 1; }
                else {
                  if ($typeA2Key < $typeB2Key){ return -1; }
                  elseif ($typeA2Key > $typeB2Key){ return 1; }
                  else { return 0; }
                }
              }
              
              //die(print_r($this_battle_stars, true));
              uasort($this_battle_stars, 'mmrpg_prototype_sort_stars');
              //die('<pre>'.print_r($this_battle_stars, true).'</pre>');
              $temp_key = 0;
              foreach ($this_battle_stars AS $star_token => $star_data){

                // Collect the star image info from the index based on type
                $temp_star_kind = $star_data['star_kind'];
                $temp_star_date = !empty($star_data['star_date']) ? $star_data['star_date']: 0;
                $temp_field_type_1 = !empty($star_data['star_type']) ? $star_data['star_type'] : 'none';
                $temp_field_type_2 = !empty($star_data['star_type2']) ? $star_data['star_type2'] : $temp_field_type_1;
                $temp_star_back_info = mmrpg_prototype_star_image($temp_field_type_2);
                $temp_star_front_info = mmrpg_prototype_star_image($temp_field_type_1);
                $temp_star_front = array('path' => 'images/abilities/item-star-base-'.$temp_star_front_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => str_pad($temp_star_front_info['frame'], 2, '0', STR_PAD_LEFT));
                $temp_star_back = array('path' => 'images/abilities/item-star-'.$temp_star_kind.'-'.$temp_star_back_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => str_pad($temp_star_back_info['frame'], 2, '0', STR_PAD_LEFT));
                $temp_star_title = $star_data['star_name'].' Star <br />';
                $temp_star_title .= '<span style="font-size:80%;">';
                if ($temp_field_type_1 != $temp_field_type_2){ $temp_star_title .= ''.ucfirst($temp_field_type_1).(!empty($temp_field_type_2) ? ' / '.ucfirst($temp_field_type_2) : '').' Type'; }
                else { $temp_star_title .= ''.ucfirst($temp_field_type_1).' Type'; }
                $temp_star_title .= ' | '.ucfirst($temp_star_kind).' Star';
                if ($temp_field_type_1 != 'none'){
                  if ($temp_star_kind == 'field'){
                    $temp_star_title .= ' <br />'.ucfirst($temp_field_type_1).' +10%';
                  } elseif ($temp_star_kind == 'fusion'){
                    if ($temp_field_type_1 != $temp_field_type_2){
                      $temp_star_title .= ' <br />'.ucfirst($temp_field_type_1).' +10%';
                      $temp_star_title .= ' | '.ucfirst($temp_field_type_2).' +10%';
                    } else {
                      $temp_star_title .= ' <br />'.ucfirst($temp_field_type_1).' +20%';
                    }
                  }
                }
                if (!empty($temp_star_date)){
                  $temp_star_title .= ' <br />Found '.date('Y/m/d', $temp_star_date);
                }
                $temp_star_title .= '</span>';
                $temp_star_title = htmlentities($temp_star_title, ENT_QUOTES, 'UTF-8');
                echo '<a href="#" data-key="'.$temp_key.'" data-tooltip="'.$temp_star_title.'" data-tooltip-type="field_type field_type_'.$temp_field_type_1.(!empty($temp_field_type_2) ? '_'.$temp_field_type_2 : '').'" class="sprite sprite_40x40 sprite_star" style="">';
                  echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_'.$temp_star_back['frame'].'" style="background-image: url('.$temp_star_back['path'].'); z-index: 10;">&nbsp;</div>';
                  echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_'.$temp_star_front['frame'].'" style="background-image: url('.$temp_star_front['path'].'); z-index: 20;">&nbsp;</div>';
                echo '</a>';
                $temp_key++;

              }

            }
            ?>
          </div>

        </div>
      </div>

    </div>

  </div>
<script type="text/javascript">
$(document).ready(function(){
<?
// Define a reference to the game's session flag variable
if (empty($_SESSION[$session_token]['flags']['events'])){ $_SESSION[$session_token]['flags']['events'] = array(); }
$temp_game_flags = &$_SESSION[$session_token]['flags']['events'];
// If this is the first time using the editor, display the introductory area
$temp_event_flag = 'mmrpg-event-01_starforce-viewer-intro';
if (empty($temp_game_flags[$temp_event_flag]) && $global_allow_editing){
  $temp_game_flags[$temp_event_flag] = true;
  ?>
  // Generate a first-time event canvas that explains how the editor works
  gameSettings.windowEventsCanvas = [
    '<div class="sprite sprite_80x80" style="background-image: url(images/fields/field/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80 sprite_80x80_05" style="background-image: url(images/abilities/item-star/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); top: 50px; left: 100px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80 sprite_80x80_07" style="background-image: url(images/abilities/item-star/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); top: 25px; left: 250px; width: 80px; height: 80px;">&nbsp;</div>'+
    '<div class="sprite sprite_80x80 sprite_80x80_08" style="background-image: url(images/abilities/item-star/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); top: 50px; right: 100px; width: 80px; height: 80px;">&nbsp;</div>'+
    ''
    ];
  // Generate a first-time event message that explains how the editor works
  gameSettings.windowEventsMessages = [
    '<p>A strange new energy source called <strong>Star Force</strong> has appeared in the prototype in the form of <em>Field Stars</em> and <em>Fusion Stars</em>. The elemental energy that these stars radiate dramatically boosts robots of the same type, giving them more power and more experience points in battle.</p>'+
    '<p>Recent studies estimate that the total number of stars in the prototype may be well over nine hundred, but we may never know the real number until you\'ve collected them all.  Searching for Star Force may seem daunting at first, but there are really only few things to keep in mind.</p>'+
    '<p>Field Stars can be found in any of the chapter two base fields, and are relatively easy to find.  Fusion Stars, on the other hand, appear in chapter four fusion stages and are a bit more complicated to find.  Every unique combination of field factors has it\'s own fusion star, so heavy use of the Player Editor will be required to track them all down.</p>'+
    ''
    ];
  // Push this event to the parent window and display to the user
  top.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
  <?
}
?>
});
</script>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'data/analytics.php'); }
?>
</body>
</html>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_bottom.php');
// Unset the database variable
unset($DB);
?>