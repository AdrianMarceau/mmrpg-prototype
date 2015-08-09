<?php
// Include the TOP file
require_once('top.php');
// Require the starforce data files
require_once('data/starforce.php');

// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['PROTOTYPE_TEMP'] = array();

// Define the animate flag for debug purposes
$debug_flag_spriteboxes = false;
$debug_flag_animation = true;
$debug_flag_scanlines = true;
//$debug_flag_spriteboxes = true;
//$debug_flag_animation = false;
//$debug_flag_scanlines = false;

// Collect the battle tokens from the URL
$this_battle_id = isset($_GET['this_battle_id']) ? $_GET['this_battle_id'] : 0;
$this_battle_token = isset($_GET['this_battle_token']) ? $_GET['this_battle_token'] : '';
$this_field_id = isset($_GET['this_field_id']) ? $_GET['this_field_id'] : 0;
$this_field_token = isset($_GET['this_field_token']) ? $_GET['this_field_token'] : '';
$this_player_id = isset($_GET['this_player_id']) ? $_GET['this_player_id'] : 0;
$this_player_token = isset($_GET['this_player_token']) ? $_GET['this_player_token'] : '';
$this_player_robots = isset($_GET['this_player_robots']) ? $_GET['this_player_robots'] : '';
$target_player_id = isset($_GET['target_player_id']) ? $_GET['target_player_id'] : 0;
$target_player_token = isset($_GET['target_player_token']) ? $_GET['target_player_token'] : '';

// Collect the battle index data if available
if (!empty($this_battle_token)){
  $this_battle_data = mmrpg_battle::get_index_info($this_battle_token);
  if (empty($this_battle_data['battle_id'])){
    $this_battle_id = !empty($this_battle_id) ? $this_battle_id : 1;
    $this_battle_data['battle_id'] = $this_battle_id;
  }
}
else {
  $this_battle_id = 0;
  $this_battle_token = '';
  $this_battle_data = array();
}

// Collect the field index if available
$mmrpg_index_fields = mmrpg_field::get_index();
// Collect the field index data if available
if (!empty($this_field_token) && isset($mmrpg_index_fields[$this_field_token])){
  $this_field_data = mmrpg_field::parse_index_info($mmrpg_index_fields[$this_field_token]);
  if (empty($this_field_data['field_id'])){
    $this_field_id = !empty($this_field_id) ? $this_field_id : 1;
    $this_field_data['field_id'] = $this_field_id;
  }
}
elseif (!empty($this_battle_data['battle_field_base']['field_token']) && isset($mmrpg_index_fields[$this_battle_data['battle_field_base']['field_token']])){
  $this_field_data1 = mmrpg_field::parse_index_info($mmrpg_index_fields[$this_battle_data['battle_field_base']['field_token']]);
  $this_field_data2 = $this_battle_data['battle_field_base'];
  $this_field_data = array_merge($this_field_data1, $this_field_data2);
  if (empty($this_field_data['field_id'])){
    $this_field_id = !empty($this_field_id) ? $this_field_id : 1;
    $this_field_data['field_id'] = $this_field_id;
  }
}
else {
  $this_field_id = 0;
  $this_field_token = '';
  $this_field_data = array();
}

// Collect this player's index data if available
if (!empty($this_player_token) && isset($mmrpg_index['players'][$this_player_token])){
  $this_player_data = $mmrpg_index['players'][$this_player_token];
  if (empty($this_player_data['user_id'])){
    $this_player_data['user_id'] = 1;
  }
  if (empty($this_player_data['player_id'])){
    $this_player_id = !empty($this_player_id) ? $this_player_id : 1;
    $this_player_data['player_id'] = $this_player_id;
  }
  if (!empty($this_player_robots)){
    $allowed_robots = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
    foreach ($this_player_data['player_robots'] AS $key => $data){
      if (!in_array($data['robot_id'].'_'.$data['robot_token'], $allowed_robots)){
        unset($this_player_data['player_robots'][$key]);
      } elseif (!mmrpg_prototype_robot_unlocked($this_player_token, $data['robot_token'])){
        unset($allowed_robots[array_search($data['robot_id'].'_'.$data['robot_token'], $allowed_robots)]);
        unset($this_player_data['player_robots'][$key]);
      }
    }
    $this_player_robots = implode(',', $allowed_robots);
    $this_player_data['player_robots'] = array_values($this_player_data['player_robots']);
  }
}
else {
  $this_player_data = false;
}

// Collect the target player's index data if available
if (!empty($target_player_token) && isset($mmrpg_index['players'][$target_player_token])){
  $target_player_data = $mmrpg_index['players'][$target_player_token];
  if (empty($target_player_data['user_id'])){
    $target_player_data['user_id'] = 2;
  }
  if (empty($target_player_data['player_id'])){
    $target_player_id = !empty($target_player_id) ? $target_player_id : 2;
    $target_player_data['player_id'] = $target_player_id;
  }
}
elseif (!empty($this_battle_data['battle_target_player']['player_token']) && isset($mmrpg_index['players'][$this_battle_data['battle_target_player']['player_token']])){
  $target_player_data = array_merge($mmrpg_index['players'][$this_battle_data['battle_target_player']['player_token']], $this_battle_data['battle_target_player']);
  if (empty($target_player_data['user_id'])){
    $target_player_data['user_id'] = 2;
  }
  if (empty($target_player_data['player_id'])){
    $target_player_id = !empty($target_player_id) ? $target_player_id : 2;
    $target_player_data['player_id'] = $target_player_id;
  }
  if (empty($target_player_robots) && !empty($this_battle_data['battle_target_player'])){
    $target_player_data['player_robots'] = array();
    foreach ($this_battle_data['battle_target_player']['player_robots'] AS $key => $data){
      $target_player_data['player_robots'][] = $data;
    }
  }
}
else {
  $target_player_id = 0;
  $target_player_token = '';
  $target_player_data = array();
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Battle Engine | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link rel="shortcut icon" type="image/x-icon" href="images/assets/favicon<?= !MMRPG_CONFIG_IS_LIVE ? '-local' : '' ?>.ico">
<link type="text/css" href="styles/reset.css" rel="stylesheet" />
<link type="text/css" href="styles/jquery.scrollbar.min.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/battle.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.scrollbar.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/battle.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game setting flags
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.eventTimeout = <?= $flag_wap ? 700 : 900 ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
gameSettings.idleAnimation = <?= $debug_flag_animation ? 'true' : 'false' ?>;
gameSettings.fieldMusic = 'fields/<?=$this_field_data['field_music']?>';
//gameSettings.eventCrossFade = true;
<?
// Update the event timeout setting if set
$event_timeout = !empty($_SESSION['GAME']['battle_settings']['eventTimeout']) ? $_SESSION['GAME']['battle_settings']['eventTimeout'] : 0;
if (!empty($event_timeout)){ echo 'gameSettings.eventTimeout ='.$event_timeout.";\n"; }
?>
// Create the document ready events
$(document).ready(function(){
<?
// Preload battle related image files
if (!empty($this_battle_data)){
  echo "  mmrpg_preload_field_sprites('background', '{$this_field_data['field_background']}');\n";
  echo "  mmrpg_preload_field_sprites('foreground', '{$this_field_data['field_foreground']}');\n";
}
if (!empty($this_player_data) && !empty($this_player_data['player_robots'])){
  // Preload this player robot sprites
  foreach ($this_player_data['player_robots'] AS $temp_key => $temp_robotinfo){
    echo "  mmrpg_preload_robot_sprites('{$temp_robotinfo['robot_token']}', 'right', 80);\n";
  }
}
if (!empty($target_player_data) && !empty($target_player_data['player_robots'])){
  // Preload the target player robot sprites
  foreach ($target_player_data['player_robots'] AS $temp_key => $temp_robotinfo){
    echo "  mmrpg_preload_robot_sprites('{$temp_robotinfo['robot_token']}', 'left', 80);\n";
  }
}
?>

});
</script>
</head>
<body id="mmrpg" class="battle">
<div id="battle" class="hidden">

  <form id="engine" action="data.php<?= $flag_wap ? '?wap=true' : '' ?>" target="connect" method="post">

    <input type="hidden" name="this_action" value="" />
    <input type="hidden" name="next_action" value="loading" />

    <input type="hidden" name="this_battle_id" value="<?=$this_battle_data['battle_id']?>" />
    <input type="hidden" name="this_battle_token" value="<?=$this_battle_data['battle_token']?>" />
    <input type="hidden" name="this_battle_status" value="active" />
    <input type="hidden" name="this_battle_result" value="pending" />

    <input type="hidden" name="this_field_id" value="<?=$this_field_data['field_id']?>" />
    <input type="hidden" name="this_field_token" value="<?=$this_field_data['field_token']?>" />

    <input type="hidden" name="this_user_id" value="<?=$this_player_data['user_id']?>" />
    <input type="hidden" name="this_player_id" value="<?=$this_player_data['player_id']?>" />
    <input type="hidden" name="this_player_token" value="<?=$this_player_data['player_token']?>" />
    <input type="hidden" name="this_player_robots" value="<?=$this_player_robots?>" />
    <input type="hidden" name="this_robot_id" value="auto" />
    <input type="hidden" name="this_robot_token" value="auto" />

    <input type="hidden" name="target_user_id" value="<?=$target_player_data['user_id']?>" />
    <input type="hidden" name="target_player_id" value="<?=$target_player_data['player_id']?>" />
    <input type="hidden" name="target_player_token" value="<?=$target_player_data['player_token']?>" />
    <input type="hidden" name="target_robot_id" value="auto" />
    <input type="hidden" name="target_robot_token" value="auto" />

  </form>

  <div id="canvas">
    <div class="wrapper">

      <div class="canvas_overlay_header canvas_overlay_hidden" style="">&nbsp;</div>

      <div id="animate" style="opacity: 0;"><a class="toggle paused" href="#" onclick=""><span><span>loading&hellip;</span></span></a></div>
      <div class="event sticky" style="z-index: 1;">
        <?

        // If field data was provided, preload the background/foreground
        if (!empty($this_field_data)){

          // Define the paths for the different attachment types
          $class_paths = array('ability' => 'abilities', 'battle' => 'battles', 'field' => 'fields', 'player' => 'players', 'robot' => 'robots', 'object' => 'objects');

          // Define the background layer properties
          $background_animate = array();
          if (!empty($this_field_data['field_background_frame'])){
            if (is_array($this_field_data['field_background_frame'])){ foreach ($this_field_data['field_background_frame'] AS $frame){ $background_animate[] = str_pad($frame, 2, '0', STR_PAD_LEFT);  } }
            else { $background_animate[] = str_pad($this_field_data['field_background_frame'], 2, '0', STR_PAD_LEFT); }
          }
          $background_data_animate = count($background_animate) > 1 ? implode(',', $background_animate) : false;
          // Display the markup of the background layer
          //echo '<div class="animate_fadein background_canvas background background_'.$background_animate[0].'" data-frame="'.$background_animate[0].'" '.(!empty($background_data_animate) ? 'data-animate="'.$background_data_animate.'"' : '').' style="background-image: url(images/fields/'.$this_field_data['field_background'].'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');">&nbsp;</div>';
          echo '<div class="animate_fadein background_canvas background background_00" data-frame="00" style="background-image: url(images/fields/'.$this_field_data['field_background'].'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');">&nbsp;</div>';

          // Loop through and display the markup of any background attachments
          if (!empty($this_field_data['field_background_attachments'])){
            echo '<div class="animate_fadein background_event event clearback sticky" style="z-index: 30; border-color: transparent;">';
            $this_key = -1;
            foreach ($this_field_data['field_background_attachments'] AS $this_id => $this_info){
              if ($flag_wap && preg_match('/^(mecha|object)/i', $this_id)){ continue; }
              $this_key++;
              $this_class = $this_info['class'];
              $this_size = $this_info['size'];
              $this_boxsize = $this_size.'x'.$this_size;
              $this_path = $class_paths[$this_class];
              $this_offset_x = $this_info['offset_x'];
              $this_offset_y = $this_info['offset_y'];
              $this_offset_z = $this_key + 1;
              $this_token = $this_info[$this_class.'_token'];
              $this_frames = $this_info[$this_class.'_frame'];
              if ($this_class == 'robot' && !empty($this_field_data['field_mechas']) && preg_match('/^mecha/i', $this_id)){
                $this_token = $this_field_data['field_mechas'][array_rand($this_field_data['field_mechas'])];
                //$this_token = 'met';
                $this_frames = array();
                $temp_frames = mt_rand(1, 20);
                $temp_options = array(0, 1, 2, 8);
                for ($i = 0; $i < $temp_frames; $i++){ $this_frames[] = $temp_options[array_rand($temp_options)]; }
                foreach ($temp_options AS $i){ if (!in_array($i, $this_frames)){ $this_frames[] = $i; } }
              }
              $this_frames_shift = isset($this_info[$this_class.'_frame_shift']) ? $this_info[$this_class.'_frame_shift'] : array();
              foreach ($this_frames AS $key => $frame){ if (is_numeric($frame)){ $this_frames[$key] = str_pad($frame, 2, '0', STR_PAD_LEFT); } }
              $this_frame = $this_frames[0];
              //if ($debug_flag_animation){ $this_animate = implode(',', $this_frames); }
              //else { $this_animate = $this_frame; }
              $this_animate = implode(',', $this_frames);
              $this_animate_shift = implode('|', $this_frames_shift);
              $this_direction = $this_info[$this_class.'_direction'];
              $this_float = $this_direction == 'left' ? 'right' : 'left';
              echo '<div data-id="background_attachment_'.$this_key.'" class="sprite sprite_'.$this_boxsize.' sprite_'.$this_boxsize.'_'.$this_direction.' sprite_'.$this_boxsize.'_'.$this_frame.'" data-type="attachment" data-position="background" data-size="'.$this_size.'" data-direction="'.$this_direction.'" data-frame="'.$this_frame.'" data-animate="'.$this_animate.'" '.(!empty($this_animate_shift) ? 'data-animate-shift="'.$this_animate_shift.'"' : '').' style="'.$this_float.': '.$this_offset_x.'px; bottom: '.$this_offset_y.'px; z-index: '.$this_offset_z.'; background-image: url(images/'.$this_path.'/'.$this_token.'/sprite_'.$this_direction.'_'.$this_boxsize.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); '.($debug_flag_spriteboxes ? 'background-color: rgba(255, 0, 0, 0.5); opacity: 0.75; ' : '').'">&nbsp;</div>';
            }
            echo '</div>';
          }

          // Define the foreground layer properties
          $foreground_animate = array();
          if (!empty($this_field_data['field_foreground_frame'])){
            if (is_array($this_field_data['field_foreground_frame'])){ foreach ($this_field_data['field_foreground_frame'] AS $frame){ $foreground_animate[] = str_pad($frame, 2, '0', STR_PAD_LEFT);  } }
            else { $foreground_animate[] = str_pad($this_field_data['field_foreground_frame'], 2, '0', STR_PAD_LEFT); }
          }
          $foreground_data_animate = count($foreground_animate) > 1 ? implode(',', $foreground_animate) : false;
          // Display the markup of the foreground layer
          echo '<div class="animate_fadein foreground_canvas foreground foreground_00" data-frame="00" style="background-image: url(images/fields/'.$this_field_data['field_foreground'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');">&nbsp;</div>';

          // Check if this field has a fusion star in it
          if (!empty($this_battle_data['values']['field_star'])){

            // Check if this is a field star or fusion star
            $temp_star_kind = !empty($this_field_data['field_type2']) ? 'fusion' : 'field';
            $temp_star_name = $this_field_data['field_name'].' Star';
            $temp_field_type_1 = !empty($this_field_data['field_type']) ? $this_field_data['field_type'] : 'none';
            $temp_field_type_2 = !empty($this_field_data['field_type2']) ? $this_field_data['field_type2'] : $temp_field_type_1;
            if ($temp_field_type_1 == $temp_field_type_2){ $temp_star_text = ucfirst($temp_field_type_1).' Type | '; }
            else { $temp_star_text = ucfirst($temp_field_type_1).' / '.ucfirst($temp_field_type_1).' Type | '; }
            $temp_star_text .= ucfirst($temp_star_kind).' Class';
            // Collect the star image info from the index based on type
            $temp_star_back_info = mmrpg_prototype_star_image($temp_field_type_2);
            $temp_star_front_info = mmrpg_prototype_star_image($temp_field_type_1);

            // Append the new field star to the foreground attachment array
            $this_field_data['field_foreground_attachments']['field-star-back'] = array(
              'class' => 'ability',
              'size' => 80,
              'offset_x' => 325,
              'offset_y' => 75,
              'ability_token' => 'item-star',
              'ability_image' => 'item-star-'.$temp_star_kind.'-'.$temp_star_back_info['sheet'],
              'ability_frame' => array($temp_star_back_info['frame'], $temp_star_back_info['frame'], $temp_star_back_info['frame'], $temp_star_back_info['frame']),
              'ability_frame_shift' => array('325,75', '325,80', '325,85', '325,80'),
              'ability_direction' => 'left',
              'ability_text' => $temp_star_text
              );
            $this_field_data['field_foreground_attachments']['field-star-front'] = array(
              'class' => 'ability',
              'size' => 80,
              'offset_x' => 325,
              'offset_y' => 75,
              'ability_token' => 'item-star',
              'ability_image' => 'item-star-base-'.$temp_star_front_info['sheet'],
              'ability_frame' => array($temp_star_front_info['frame'], $temp_star_front_info['frame'], $temp_star_front_info['frame'], $temp_star_front_info['frame']),
              'ability_frame_shift' => array('325,75', '325,80', '325,85', '325,80'),
              'ability_direction' => 'left',
              'ability_text' => $temp_star_text
              );

          }

          // Loop through and display the markup of any foreground attachments
          if (!empty($this_field_data['field_foreground_attachments'])){
            echo '<div class="animate_fadein foreground_event event clearback sticky" style="z-index: 60; border-color: transparent;">';
            $this_key = -1;
            foreach ($this_field_data['field_foreground_attachments'] AS $this_id => $this_info){
              if ($flag_wap && preg_match('/^(mecha|object)/i', $this_id)){ continue; }
              $this_key++;
              $this_class = $this_info['class'];
              $this_size = $this_info['size'];
              $this_boxsize = $this_size.'x'.$this_size;
              $this_path = $class_paths[$this_class];
              $this_offset_x = $this_info['offset_x'];
              $this_offset_y = $this_info['offset_y'];
              $this_offset_z = isset($this_info['offset_z']) ? $this_info['offset_z'] : $this_key + 1;
              $this_token = $this_info[$this_class.'_token'];
              $this_text = !empty($this_info[$this_class.'_text']) ? $this_info[$this_class.'_text'] : '&nbsp;';
              $this_image = !empty($this_info[$this_class.'_image']) ? $this_info[$this_class.'_image'] : $this_token;
              $this_frames = $this_info[$this_class.'_frame'];
              // We want mechas, but not actual robots replaced
              if ($this_class == 'robot' && !empty($this_field_data['field_mechas']) && preg_match('/^mecha/i', $this_id)){
                $this_token = $this_field_data['field_mechas'][array_rand($this_field_data['field_mechas'])];
                $this_image = $this_token;
                //$this_token = 'met';
                $this_frames = array();
                $temp_frames = mt_rand(1, 20);
                $temp_options = array(0, 1, 2, 8);
                for ($i = 0; $i < $temp_frames; $i++){ $this_frames[] = $temp_options[array_rand($temp_options)]; }
                foreach ($temp_options AS $i){ if (!in_array($i, $this_frames)){ $this_frames[] = $i; } }
              }

              $this_frames_shift = isset($this_info[$this_class.'_frame_shift']) ? $this_info[$this_class.'_frame_shift'] : array();
              foreach ($this_frames AS $key => $frame){ if (is_numeric($frame)){ $this_frames[$key] = str_pad($frame, 2, '0', STR_PAD_LEFT); } }
              $this_frame = $this_frames[0];
              //if ($debug_flag_animation){ $this_animate = implode(',', $this_frames); }
              //else { $this_animate = $this_frame; }
              $this_animate = implode(',', $this_frames);
              $this_animate_shift = implode('|', $this_frames_shift);
              $this_animate_enabled = count($this_frames) > 1 ? true : false;
              $this_direction = $this_info[$this_class.'_direction'];
              $this_float = $this_direction == 'left' ? 'right' : 'left';
              echo '<div data-id="foreground_attachment_'.$this_id.'" ';
              echo 'class="sprite sprite_'.$this_boxsize.' sprite_'.$this_boxsize.'_'.$this_direction.' sprite_'.$this_boxsize.'_'.$this_frame.'" data-type="attachment" data-position="foreground" data-size="'.$this_size.'" data-direction="'.$this_direction.'" data-frame="'.$this_frame.'" '.(!empty($this_animate_enabled) ? 'data-animate="'.$this_animate.'"' : '').' '.(!empty($this_animate_shift) ? 'data-animate-shift="'.$this_animate_shift.'"' : '').' style="'.$this_float.': '.$this_offset_x.'px; bottom: '.$this_offset_y.'px; z-index: '.$this_offset_z.'; background-image: url(images/'.$this_path.'/'.$this_image.'/sprite_'.$this_direction.'_'.$this_boxsize.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); '.($debug_flag_spriteboxes ? 'background-color: rgba(255, 0, 0, 0.5); opacity: 0.75; ' : '').'">'.$this_text.'</div>';
            }
            echo '</div>';
          }

        }
        // Otherwise, simply print the ready message
        else {
          echo 'Ready?';
        }
        ?>
      </div>

      <div class="event event_details clearback sticky" style="">
        <?
        // Display the scanline layer if enabled
        if ($debug_flag_scanlines){ echo '<div class="foreground scanlines" style="background-image: url(images/gui/canvas-scanlines.png?'.MMRPG_CONFIG_CACHE_DATE.'); opacity: 1;">&nbsp;</div>'; }
        ?>
        <?/*

        <div class="sprite ability_damage ability_damage_energy" style="left: 0; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">-9</div>
        <div class="sprite ability_recovery ability_recovery_energy" style="right: 0; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">+9</div>

        <div class="sprite ability_damage ability_damage_attack" style="left: 50px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">-99</div>
        <div class="sprite ability_recovery ability_recovery_attack" style="right: 50px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">+99</div>

        <div class="sprite ability_damage ability_damage_defense" style="left: 100px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">-9999</div>
        <div class="sprite ability_recovery ability_recovery_defense" style="right: 100px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">+9999</div>

        <div class="sprite ability_damage ability_damage_speed" style="left: 150px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">-99</div>
        <div class="sprite ability_recovery ability_recovery_speed" style="right: 150px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">+99 </div>

        <div class="sprite ability_damage ability_damage_experience" style="left: 200px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">-9999</div>
        <div class="sprite ability_recovery ability_recovery_experience" style="right: 200px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">+9999</div>

        <div class="sprite ability_damage ability_damage_level" style="left: 300px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">-1</div>
        <div class="sprite ability_recovery ability_recovery_level" style="right: 300px; top: 200px; background-color: rgba(255, 0, 0, 0.30); ">+1</div>



        <div class="sprite ability_damage ability_damage_energy_super" style="left: 0; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">-9</div>
        <div class="sprite ability_recovery ability_recovery_energy_super" style="right: 0; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">+9</div>

        <div class="sprite ability_damage ability_damage_attack_super" style="left: 50px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">-99</div>
        <div class="sprite ability_recovery ability_recovery_attack_super" style="right: 50px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">+99</div>

        <div class="sprite ability_damage ability_damage_defense_super" style="left: 100px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">-9999</div>
        <div class="sprite ability_recovery ability_recovery_defense_super" style="right: 100px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">+9999</div>

        <div class="sprite ability_damage ability_damage_speed_super" style="left: 150px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">-99</div>
        <div class="sprite ability_recovery ability_recovery_speed_super" style="right: 150px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">+99 </div>

        <div class="sprite ability_damage ability_damage_experience_super" style="left: 200px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">-9999</div>
        <div class="sprite ability_recovery ability_recovery_experience_super" style="right: 200px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">+9999</div>

        <div class="sprite ability_damage ability_damage_level_super" style="left: 300px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">-1</div>
        <div class="sprite ability_recovery ability_recovery_level_super" style="right: 300px; top: 100px; background-color: rgba(255, 0, 0, 0.30); ">+1</div>



        <div class="sprite ability_damage ability_damage_energy_critical" style="left: 0; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">-9</div>
        <div class="sprite ability_recovery ability_recovery_energy_critical" style="right: 0; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">+9</div>

        <div class="sprite ability_damage ability_damage_attack_critical" style="left: 50px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">-99</div>
        <div class="sprite ability_recovery ability_recovery_attack_critical" style="right: 50px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">+99</div>

        <div class="sprite ability_damage ability_damage_defense_critical" style="left: 100px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">-9999</div>
        <div class="sprite ability_recovery ability_recovery_defense_critical" style="right: 100px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">+9999</div>

        <div class="sprite ability_damage ability_damage_speed_critical" style="left: 150px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">-99</div>
        <div class="sprite ability_recovery ability_recovery_speed_critical" style="right: 150px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">+99 </div>

        <div class="sprite ability_damage ability_damage_experience_critical" style="left: 200px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">-9999</div>
        <div class="sprite ability_recovery ability_recovery_experience_critical" style="right: 200px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">+9999</div>

        <div class="sprite ability_damage ability_damage_level_critical" style="left: 300px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">-1</div>
        <div class="sprite ability_recovery ability_recovery_level_critical" style="right: 300px; top: 0px; background-color: rgba(255, 0, 0, 0.30); ">+1</div>
       */?>

      </div>

    </div>
  </div>

  <div id="console">
    <div class="wrapper">
    </div>
  </div>

  <div id="actions">
    <a id="actions_resend" class="actions_resend button">RESEND</a>
    <div id="actions_loading" class="actions_loading wrapper">
      <div class="main_actions">
        <a class="button action_loading button_disabled" type="button"><label>Loading...</label></a>
      </div>
    </div>
    <div id="actions_battle" class="actions_battle wrapper"></div>
    <div id="actions_ability" class="actions_ability wrapper"></div>
    <div id="actions_item" class="actions_ability actions_item wrapper"></div>
    <div id="actions_switch" class="actions_switch wrapper"></div>
    <div id="actions_scan" class="actions_scan wrapper"></div>
    <div id="actions_target_this" class="actions_target action_target_this wrapper"></div>
    <div id="actions_target_this_disabled" class="actions_target action_target_this_disabled wrapper"></div>
    <div id="actions_target_target" class="actions_target action_target_target wrapper"></div>
    <div id="actions_option" class="actions_option wrapper"></div>
    <div id="actions_settings" class="actions_settings wrapper">
      <div class="main_actions">
        <a data-order="1" class="button action_setting block_1" type="button" data-panel="settings_eventTimeout"><label><span class="multi">Game<br />Speed</span></label></a>
        <a class="button action_setting button_disabled block_2" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_3" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_4" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_5" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_6" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_7" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_8" type="button">&nbsp;</a>
      </div>
      <div class="sub_actions"><a data-order="2" class="button action_back" type="button" data-panel="option"><label>Back</label></a></div>
    </div>
    <div id="actions_settings_autoScan" class="actions_settings actions_settings_autoScan wrapper">
      <div class="main_actions">
        <a data-order="1" class="button action_setting block_1" type="button" data-action="settings_autoScan_true"><label><span class="multi">Auto Scan<br />On</span></label></a>
        <a data-order="2" class="button action_setting block_2" type="button" data-action="settings_autoScan_false"><label><span class="multi">Auto Scan<br />Off</span></label></a>
        <a class="button action_setting button_disabled block_3" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_4" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_5" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_6" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_7" type="button">&nbsp;</a>
        <a class="button action_setting button_disabled block_8" type="button">&nbsp;</a>
      </div>
      <div class="sub_actions"><a data-order="3" class="button action_back" type="button" data-panel="option"><label>Back</label></a></div>
    </div>
    <div id="actions_settings_eventTimeout" class="actions_settings actions_settings_eventTimeout wrapper">
      <div class="main_actions main_actions_hastitle"><span class="main_actions_title">Select Speed</span>
        <a data-order="1" class="button action_setting block_1" type="button" data-action="settings_eventTimeout_1600"><label><span class="multi">Super&nbsp;Slow<br />(1f/1600ms)</span></label></a>
        <a data-order="2" class="button action_setting block_2" type="button" data-action="settings_eventTimeout_1250"><label><span class="multi">Medium&nbsp;Slow<br />(1f/1250ms)</span></label></a>
        <a data-order="3" class="button action_setting block_3" type="button" data-action="settings_eventTimeout_1000"><label><span class="multi">Normal&nbsp;Slow<br />(1f/1000ms)</span></label></a>
        <a data-order="4" class="button action_setting block_4" type="button" data-action="settings_eventTimeout_900"><label><span class="multi">Normal<br />(1f/900ms)</span></label></a>
        <a data-order="5" class="button action_setting block_5" type="button" data-action="settings_eventTimeout_800"><label><span class="multi">Normal&nbsp;Fast<br />(1f/800ms)</span></label></a>
        <a data-order="6" class="button action_setting block_6" type="button" data-action="settings_eventTimeout_700"><label><span class="multi">Medium&nbsp;Fast<br />(1f/700ms)</span></label></a>
        <a data-order="7" class="button action_setting block_7" type="button" data-action="settings_eventTimeout_600"><label><span class="multi">Super&nbsp;Fast<br />(1f/600ms)</span></label></a>
        <a data-order="8" class="button action_setting block_8" type="button" data-action="settings_eventTimeout_500"><label><span class="multi">Ultra&nbsp;Fast<br />(1f/500ms)</span></label></a>
      </div>
      <div class="sub_actions"><a data-order="9" class="button action_back" type="button" data-panel="option"><label>Back</label></a></div>
    </div>
    <div id="actions_event" class="actions_event wrapper">
      <div class="main_actions">
        <a data-order="1" class="button action_continue" type="button" data-action="continue"><label>Continue</label></a>
      </div>
    </div>
  </div>

  <iframe id="connect" name="connect" src="about:blank">
  </iframe>

  <div id="event_console_backup" style="display: none;">
  </div>

</div>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/data/analytics.php');
// Unset the database variable
unset($DB);
?>
</body>
</html>