<?php

// Include the TOP file
require_once('top.php');

// Require the starforce data file
require_once(MMRPG_CONFIG_ROOTDIR.'includes/starforce.php');

//$GLOBALS['DEBUG']['checkpoint_line'] = 'battle.php : line 5';

// Automatically unset the session variable
// on index page load (for testing)
//session_unset();

//// Automatically empty all temporary battle variables
$_SESSION['BATTLES'] = array();
$_SESSION['FIELDS'] = array();
$_SESSION['PLAYERS'] = array();
$_SESSION['ROBOTS'] = array();
$_SESSION['ABILITIES'] = array();
$_SESSION['ITEMS'] = array();
$_SESSION['SKILLS'] = array();
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
$flag_skip_fadein = isset($_GET['flag_skip_fadein']) && $_GET['flag_skip_fadein'] == 'true' ? true : false;

// Collect the battle index data if available
if (!empty($this_battle_token)){
    $this_battle_data = rpg_battle::get_index_info($this_battle_token);
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
$mmrpg_index_fields = rpg_field::get_index(true);
// Collect the field index data if available
if (!empty($this_field_token) && isset($mmrpg_index_fields[$this_field_token])){
    $this_field_data = rpg_field::parse_index_info($mmrpg_index_fields[$this_field_token]);
    if (empty($this_field_data['field_id'])){
        $this_field_id = !empty($this_field_id) ? $this_field_id : 1;
        $this_field_data['field_id'] = $this_field_id;
    }
}
elseif (!empty($this_battle_data['battle_field_base']['field_token']) && isset($mmrpg_index_fields[$this_battle_data['battle_field_base']['field_token']])){
    $this_field_data1 = rpg_field::parse_index_info($mmrpg_index_fields[$this_battle_data['battle_field_base']['field_token']]);
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
$temp_this_robot_classes = array();
if (!empty($this_player_token)){
    $this_player_data = rpg_player::get_index_info($this_player_token);
    $temp_user_id = rpg_user::get_current_userid();
    $temp_player_id = rpg_game::unique_player_id($temp_user_id, $this_player_data['player_id']);
    $this_player_data['user_id'] = $temp_user_id;
    $this_player_data['player_id'] = $temp_player_id;
    if (!empty($this_player_robots)){
        $allowed_robots = strstr($this_player_robots, ',') ? explode(',', $this_player_robots) : array($this_player_robots);
        $allowed_robots_parsed = array();
        $this_player_data['player_robots'] = array();
        foreach ($allowed_robots AS $key => $robot_string){
            list($robot_id, $robot_token) = explode('_', $robot_string);
            $temp_robot_data = rpg_robot::get_index_info($robot_token);
            $temp_robot_class = $temp_robot_data['robot_class'];
            if (!isset($temp_this_robot_classes[$temp_robot_class])){ $temp_this_robot_classes[$temp_robot_class] = 0; }
            $temp_this_robot_classes[$temp_robot_class] += 1;
            if (mmrpg_prototype_robot_unlocked($this_player_token, $robot_token)){
                $temp_robot_id = strstr($robot_id, $temp_player_id) ? $robot_id : rpg_game::unique_robot_id($temp_player_id, $temp_robot_data['robot_id'], ($key + 1));
                $this_player_data['player_robots'][] = array('robot_id' => $temp_robot_id, 'robot_token' => $robot_token);
                $allowed_robots_parsed[] = $temp_robot_id.'_'.$robot_token;
            }
        }
        $this_player_robots = implode(',', $allowed_robots_parsed);
        $this_player_data['player_robots'] = array_values($this_player_data['player_robots']);
    }
}
else {
    $this_player_data = false;
}

// If the current player has no robots, we can't proceed
if (empty($this_player_robots)){

    // Ensure we have the correct user ID before proceeding
    $this_user_id = rpg_user::get_current_userid();

    // Automatically empty all temporary battle variables
    $_SESSION['BATTLES'] = array();
    $_SESSION['FIELDS'] = array();
    $_SESSION['PLAYERS'] = array();
    $_SESSION['ROBOTS'] = array();
    $_SESSION['ABILITIES'] = array();
    $_SESSION['ITEMS'] = array();
    $_SESSION['SKILLS'] = array();

    // Redirect the user back to the prototype screen
    $this_redirect = 'prototype.php?'.($flag_wap ? 'wap=true' : '');

    // Check if this was an ENDLESS ATTACK MODE mission and we're exiting
    if (!empty($this_battle_data['flags']['challenge_battle'])
        && !empty($this_battle_data['flags']['endless_battle'])){

        // If the player was trying to bring non-masters here, create a message
        if (empty($temp_this_robot_classes['master'])
            && (!empty($temp_this_robot_classes['mecha'])
                || !empty($temp_this_robot_classes['boss']))){

            // Define the canvas markup for the error message
            $temp_canvas_markup = '';
            $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/gentle-countryside/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
            $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/gentle-countryside/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
            $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/met/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 30px; left: 100px;"></div>';

            // Define the console markup for the error message
            $temp_console_markup = '';
            $temp_console_markup .= '<p>';
                if (!empty($temp_this_robot_classes['mecha'])){
                    $temp_console_markup .= 'Support Mechas <em>cannot</em> participate in Endless Attack Mode without their Robot Master operators present. ';
                } elseif (!empty($temp_this_robot_classes['boss'])) {
                    $temp_console_markup .= 'Fortress Bosses <em>cannot</em> participate in Endless Attack Mode and we\'re not sure how you managed to summon one in the first place. ';
                }
                $temp_console_markup .= 'As such, your progress has been halted and you have been returned to the main menu. ';
                $temp_console_markup .= 'Better luck next time, but great work out there either way! ';
            $temp_console_markup .= '</p>';

            // Push this error message into the event queue
            array_push($_SESSION[$session_token]['EVENTS'], array(
                'canvas_markup' => $temp_canvas_markup,
                'console_markup' => $temp_console_markup,
                'player_token' => !empty($this_player_token) ? $this_player_token : 'player',
                'event_type' => 'critical-issue'
                ));
        }

        // We need to clear any savestate data from the waveboard so it doesn't infinitly redirect
        $db->update('mmrpg_challenges_waveboard', array('challenge_wave_savestate' => ''), array('user_id' => $this_user_id));

    }

    // We have to redirect back to the home page of the prototype
    header('Location: '.$this_redirect);
    exit();
}


// Collect the target player's index data if available
if (!empty($target_player_token)){
    $target_player_data = rpg_player::get_index_info($target_player_token);
    $temp_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
    $temp_player_id = rpg_game::unique_player_id($temp_user_id, ($target_player_data['player_token'] !== 'player' ? $target_player_data['player_id'] : 0));
    $target_player_data['user_id'] = $temp_user_id;
    $target_player_data['player_id'] = $temp_player_id;
}
elseif (!empty($this_battle_data['battle_target_player']['player_token'])){
    $indexed_target_player_data = rpg_player::get_index_info($this_battle_data['battle_target_player']['player_token']);
    $target_player_data = array_merge($indexed_target_player_data, $this_battle_data['battle_target_player']);
    $temp_user_id = !empty($target_player_data['user_id']) ? $target_player_data['user_id'] : MMRPG_SETTINGS_TARGET_PLAYERID;
    $temp_player_id = rpg_game::unique_player_id($temp_user_id, ($indexed_target_player_data['player_token'] !== 'player' ? $indexed_target_player_data['player_id'] : 0));
    $target_player_data['user_id'] = $temp_user_id;
    $target_player_data['player_id'] = $temp_player_id;
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

// Collect the battle records if they exist in the session
$this_battle_data['battle_complete'] = mmrpg_prototype_battle_complete($this_player_token, $this_battle_token);
$this_battle_data['battle_failure'] = mmrpg_prototype_battle_failure($this_player_token, $this_battle_token);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Battle Engine | Prototype | Mega Man RPG Prototype | Last Updated <?= mmrpg_print_cache_date() ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />
<link rel="shortcut icon" type="image/x-icon" href="images/assets/<?= mmrpg_get_favicon() ?>">
<link type="text/css" href="styles/reset.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.css" rel="stylesheet" />
<link type="text/css" href="styles/battle.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="battle <?= 'env_'.MMRPG_CONFIG_SERVER_ENV ?>">
<div id="battle" class="hidden <?= $flag_skip_fadein ? 'fastfade' : '' ?>">

    <form id="engine" action="battle_loop.php<?= $flag_wap ? '?wap=true' : '' ?>" target="connect" method="post">

        <input type="hidden" name="this_action" value="" />
        <input type="hidden" name="next_action" value="loading" />

        <input type="hidden" name="this_battle_id" value="<?= $this_battle_data['battle_id'] ?>" />
        <input type="hidden" name="this_battle_token" value="<?= $this_battle_data['battle_token'] ?>" />
        <input type="hidden" name="this_battle_status" value="active" />
        <input type="hidden" name="this_battle_result" value="pending" />

        <input type="hidden" name="this_field_id" value="<?= $this_field_data['field_id'] ?>" />
        <input type="hidden" name="this_field_token" value="<?= $this_field_data['field_token'] ?>" />

        <input type="hidden" name="this_user_id" value="<?= $this_player_data['user_id'] ?>" />
        <input type="hidden" name="this_player_id" value="<?= $this_player_data['player_id'] ?>" />
        <input type="hidden" name="this_player_token" value="<?= $this_player_data['player_token'] ?>" />
        <input type="hidden" name="this_player_robots" value="<?= $this_player_robots ?>" />
        <input type="hidden" name="this_robot_id" value="auto" />
        <input type="hidden" name="this_robot_token" value="auto" />

        <input type="hidden" name="target_user_id" value="<?= $target_player_data['user_id'] ?>" />
        <input type="hidden" name="target_player_id" value="<?= $target_player_data['player_id'] ?>" />
        <input type="hidden" name="target_player_token" value="<?= $target_player_data['player_token'] ?>" />
        <input type="hidden" name="target_robot_id" value="auto" />
        <input type="hidden" name="target_robot_token" value="auto" />

    </form>

    <div id="canvas">
        <div class="wrapper">

            <div class="canvas_overlay_header canvas_overlay_hidden" style="">&nbsp;</div>

            <div id="animate" style="opacity: 0;"><a class="toggle paused" href="#" onclick=""><span><span>loading&hellip;</span></span></a></div>
            <div class="event event_fieldback sticky" style="z-index: 1;">
                <?

                // If field data was provided, preload the background/foreground
                if (!empty($this_field_data)){

                    // Define an index to cache robot/mecha info
                    $this_robot_index = array();

                    // Define the paths for the different attachment types
                    $class_paths = array('ability' => 'abilities', 'item' => 'items', 'battle' => 'battles', 'field' => 'fields', 'player' => 'players', 'robot' => 'robots', 'object' => 'objects');

                    // Define the background layer properties
                    $background_animate = array();
                    if (!empty($this_field_data['field_background_frame'])){
                        if (is_array($this_field_data['field_background_frame'])){ foreach ($this_field_data['field_background_frame'] AS $frame){ $background_animate[] = str_pad($frame, 2, '0', STR_PAD_LEFT);  } }
                        else { $background_animate[] = str_pad($this_field_data['field_background_frame'], 2, '0', STR_PAD_LEFT); }
                    }
                    $background_data_animate = count($background_animate) > 1 ? implode(',', $background_animate) : false;

                    // Display the markup of the background layer
                    $field_background_class = 'background_canvas has_pixels background background_00'.(!$flag_skip_fadein ? ' animate_fadein' : '');
                    $field_background_style = 'background-color: #000000;';
                    if (!empty($this_field_data['field_background'])){
                        $image_name = 'battle-field_background_base';
                        $image_path = 'images/fields/'.$this_field_data['field_background'].'/';
                        $image_path_full = $image_path.$image_name.'.gif';
                        //error_log('$image_path_full = '.print_r($image_path_full, true));
                        if (!empty($this_field_data['field_background_variant'])){
                            $new_image_name = $image_name.'_'.$this_field_data['field_background_variant'];
                            $new_image_path_full = $image_path.$new_image_name.'.gif';
                            //error_log('$new_image_path_full = '.print_r($new_image_path_full, true));
                            if (rpg_game::sprite_exists($new_image_path_full)){
                                //error_log(basename($new_image_path_full).' exists!');
                                $image_name = $new_image_name;
                                $image_path_full = $new_image_path_full;
                            }
                        }
                        $field_background_style .= ' background-image: url('.$image_path_full.'?'.MMRPG_CONFIG_CACHE_DATE.');';
                    }
                    echo '<div class="'.$field_background_class.'" style="'.$field_background_style.'" data-frame="00">&nbsp;</div>';


                    // Loop through and display the markup of any background attachments
                    if (!empty($this_field_data['field_background_attachments'])){
                        echo '<div class="'.(!$flag_skip_fadein ? 'animate_fadein ' : '').' background_event event clearback sticky" style="z-index: 30; border-color: transparent;">';
                        $this_key = -1;
                        foreach ($this_field_data['field_background_attachments'] AS $this_id => $this_info){
                            if ($flag_wap && preg_match('/^mecha/i', $this_id)){ continue; }
                            $this_key++;
                            $this_class = $this_info['class'];
                            $this_size = intval($this_info['size']);
                            $this_boxsize = $this_size.'x'.$this_size;
                            $this_offset_x = $this_info['offset_x'];
                            $this_offset_y = $this_info['offset_y'];
                            $this_offset_z = $this_key + 1;
                            $this_token = $this_info[$this_class.'_token'];
                            $this_path = $class_paths[$this_class];
                            if (isset($this_info['is_shadow']) && $this_info['is_shadow'] === true){ $this_path .= '_shadows'; }
                            $this_image = !empty($this_info[$this_class.'_image']) ? $this_info[$this_class.'_image'] : $this_token;
                            if ($this_class === 'object' && (!isset($this_info['subclass']) || $this_info['subclass'] !== 'common_object')){
                                $this_path = $class_paths['field'];
                                $this_image = $this_field_data['field_background'].'_'.$this_image;
                            }
                            $this_frames = $this_info[$this_class.'_frame'];
                            if ($this_class == 'robot' && !empty($this_field_data['field_mechas']) && preg_match('/^mecha/i', $this_id)){
                                $this_token = $this_field_data['field_mechas'][array_rand($this_field_data['field_mechas'])];
                                if (!isset($this_robot_index[$this_token])){ $this_robot_index[$this_token] = rpg_robot::get_index_info($this_token); }
                                $this_index = $this_robot_index[$this_token];
                                $this_image = $this_token;
                                if (!empty($this_battle_data['battle_complete']['battle_count'])
                                    && !empty($this_index['robot_image_alts'])){
                                    $images = array($this_token);
                                    foreach ($this_index['robot_image_alts'] AS $alt){
                                        if (count($images) > $this_battle_data['battle_complete']['battle_count']){ break; }
                                        $images[] = $this_token.'_'.$alt['token'];
                                    }
                                    shuffle($images);
                                    $this_image = array_shift($images);
                                }
                                $temp_size = intval($this_index['robot_image_size']);
                                if ($temp_size !== $this_size){
                                    $tmp_diff = $temp_size - $this_size;
                                    $this_size = $temp_size;
                                    $this_boxsize = $temp_size.'x'.$temp_size;
                                    $this_offset_x -= round($tmp_diff / 2);
                                }
                                $this_frames = array();
                                $temp_frames = mt_rand(1, 20);
                                $temp_options = array(0, 1, 2, 8);
                                for ($i = 0; $i < $temp_frames; $i++){ $this_frames[] = $temp_options[array_rand($temp_options)]; }
                                foreach ($temp_options AS $i){ if (!in_array($i, $this_frames)){ $this_frames[] = $i; } }
                            }
                            $this_frames_shift = isset($this_info[$this_class.'_frame_shift']) ? $this_info[$this_class.'_frame_shift'] : array();
                            foreach ($this_frames AS $key => $frame){ if (is_numeric($frame)){ $this_frames[$key] = str_pad($frame, 2, '0', STR_PAD_LEFT); } }
                            $this_frame = $this_frames[0];
                            $this_animate = implode(',', $this_frames);
                            $this_animate_shift = implode('|', $this_frames_shift);
                            $this_direction = $this_info[$this_class.'_direction'];
                            $this_float = $this_direction == 'left' ? 'right' : 'left';
                            $this_image_url = 'images/'.$this_path.'/'.$this_image.'/sprite_'.$this_direction.'_'.$this_boxsize.'.png';
                            echo '<div '.
                                'data-id="background_attachment_'.$this_key.'" '.
                                'class="sprite sprite_'.$this_boxsize.' sprite_'.$this_boxsize.'_'.$this_direction.' sprite_'.$this_boxsize.'_'.$this_frame.'" '.
                                'data-type="attachment" '.
                                'data-position="background" '.
                                'data-size="'.$this_size.'" '.
                                'data-direction="'.$this_direction.'" '.
                                'data-frame="'.$this_frame.'" '.
                                'data-animate="'.$this_animate.'" '.
                                (!empty($this_animate_shift) ? 'data-animate-shift="'.$this_animate_shift.'" ' : '').
                                'style="'.$this_float.': '.$this_offset_x.'px; bottom: '.$this_offset_y.'px; z-index: '.$this_offset_z.'; background-image: url('.$this_image_url.'?'.MMRPG_CONFIG_CACHE_DATE.'); '.($debug_flag_spriteboxes ? 'background-color: rgba(255, 0, 0, 0.5); opacity: 0.75; ' : '').'" '.
                                '>&nbsp;</div>';
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

                    // Display the markup of the background layer
                    $field_foreground_class = 'foreground_canvas has_pixels foreground foreground_00'.(!$flag_skip_fadein ? ' animate_fadein' : '');
                    $field_foreground_style = '';
                    if (!empty($this_field_data['field_foreground'])){
                        $image_name = 'battle-field_foreground_base';
                        $image_path = 'images/fields/'.$this_field_data['field_foreground'].'/';
                        $image_path_full = $image_path.$image_name.'.png';
                        //error_log('$image_path_full = '.print_r($image_path_full, true));
                        if (!empty($this_field_data['field_foreground_variant'])){
                            $new_image_name = $image_name.'_'.$this_field_data['field_foreground_variant'];
                            $new_image_path_full = $image_path.$new_image_name.'.png';
                            //error_log('$new_image_path_full = '.print_r($new_image_path_full, true));
                            if (rpg_game::sprite_exists($new_image_path_full)){
                                //error_log(basename($new_image_path_full).' exists!');
                                $image_name = $new_image_name;
                                $image_path_full = $new_image_path_full;
                            }
                        }
                        $field_foreground_style .= ' background-image: url('.$image_path_full.'?'.MMRPG_CONFIG_CACHE_DATE.');';
                    }
                    echo '<div class="'.$field_foreground_class.'" style="'.$field_foreground_style.'" data-frame="00">&nbsp;</div>';

                    /*
                    // Display the markup of the foreground layer
                    echo '<div class="'.(!$flag_skip_fadein ? 'animate_fadein ' : '').' foreground_canvas has_pixels foreground foreground_00" data-frame="00" style="background-image: url(images/fields/'.$this_field_data['field_foreground'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');">&nbsp;</div>';
                    */

                    // Check if this field has a field or fusion star in it
                    if (!empty($this_battle_data['values']['field_star'])){

                        // Check if this is a field star or fusion star
                        $temp_star_kind = !empty($this_field_data['field_type2']) ? 'fusion' : 'field';
                        $temp_star_name = $this_field_data['field_name'].' Star';
                        $temp_field_type_1 = !empty($this_field_data['field_type']) ? $this_field_data['field_type'] : 'none';
                        $temp_field_type_2 = !empty($this_field_data['field_type2']) ? $this_field_data['field_type2'] : $temp_field_type_1;
                        if ($temp_field_type_1 == $temp_field_type_2){ $temp_star_text = ucfirst($temp_field_type_1).' Type | '; }
                        else { $temp_star_text = ucfirst($temp_field_type_1).' / '.ucfirst($temp_field_type_1).' Type | '; }
                        $temp_star_text .= ucfirst($temp_star_kind).' Class';

                        // Generate the star image info based on the kind and type(s)
                        $temp_star_image = $temp_star_kind.'-star';
                        if (!empty($temp_field_type_1)){ $temp_star_image .= '_'.$temp_field_type_1; }
                        if (!empty($temp_field_type_2) && $temp_field_type_2 != $temp_field_type_1){ $temp_star_image .= '-'.$temp_field_type_2; }

                        // Append the new field star to the foreground attachment array
                        $this_field_data['field_foreground_attachments'][$temp_star_kind.'-star'] = array(
                            'class' => 'item',
                            'size' => 80,
                            'offset_x' => 331,
                            'offset_y' => 130,
                            'offset_z' => -1,
                            'item_token' => 'star',
                            'item_image' => $temp_star_image,
                            'item_frame' => array(0, 0, 0, 0),
                            'item_frame_shift' => array('331,130', '331,125', '331,130', '331,135'),
                            'item_direction' => 'left',
                            'item_text' => $temp_star_text
                            );

                        // Append the new field star to the foreground attachment array
                        $temp_shadow_image = $temp_star_kind.'-star';
                        $this_field_data['field_foreground_attachments'][$temp_star_kind.'-star_shadow'] = array(
                            'class' => 'item',
                            'is_shadow' => true,
                            'size' => 80,
                            'offset_x' => 331,
                            'offset_y' => 85,
                            'offset_z' => -1,
                            'item_token' => 'star',
                            'item_image' => $temp_shadow_image,
                            'item_frame' => array(2, 1, 0, 1),
                            'item_frame_shift' => array('331,85', '331,85', '331,85', '331,85'),
                            'item_direction' => 'left',
                            'item_text' => ''
                            );

                    }
                    // Check if this field has a challenge marker in it
                    if (!empty($this_battle_data['values']['challenge_marker'])){

                        // Collect the marker kind and define the image name
                        $temp_skull_kind = $this_battle_data['values']['challenge_marker'];
                        $temp_skull_image = 'challenge-markers/'.$temp_skull_kind;

                        // Append the new field star to the foreground attachment array
                        $this_field_data['field_foreground_attachments']['challenge-marker'] = array(
                            'class' => 'object',
                            'subclass' => 'common_object',
                            'size' => 80,
                            'offset_x' => 331,
                            'offset_y' => 130,
                            'offset_z' => -1,
                            'object_token' => 'challenge-marker',
                            'object_image' => $temp_skull_image,
                            'object_frame' => array(0, 0, 0, 0),
                            'object_frame_shift' => array('331,130', '331,125', '331,130', '331,135'),
                            'object_direction' => 'left',
                            'object_text' => ''
                            );

                        // Append the new field star to the foreground attachment array
                        $temp_shadow_image = 'challenge-markers/shadow';
                        $this_field_data['field_foreground_attachments']['challenge-marker_shadow'] = array(
                            'class' => 'object',
                            'subclass' => 'common_object',
                            'size' => 80,
                            'offset_x' => 331,
                            'offset_y' => 85,
                            'offset_z' => -1,
                            'object_token' => 'challenge-marker',
                            'object_image' => $temp_shadow_image,
                            'object_frame' => array(2, 1, 0, 1),
                            'object_frame_shift' => array('331,85', '331,85', '331,85', '331,85'),
                            'object_direction' => 'left',
                            'object_text' => ''
                            );

                    }

                    // Loop through and display the markup of any foreground attachments
                    if (!empty($this_field_data['field_foreground_attachments'])){
                        $attachments_by_layer = array();
                        $attachments = $this_field_data['field_foreground_attachments'];
                        foreach ($attachments AS $id => $info){
                            $layer = isset($info['offset_z']) ? $info['offset_z'] : 0;
                            if (!isset($attachments_by_layer[$layer])){ $attachments_by_layer[$layer] = array(); }
                            $attachments_by_layer[$layer][$id] = $info;
                        }
                        foreach ($attachments_by_layer AS $layer_key => $attachments_list){
                            $layer_offset_z = 60 + $layer_key;
                            echo '<div class="'.(!$flag_skip_fadein ? 'animate_fadein ' : '').' foreground_event '.($layer_key < 0 ? 'foreground_subevent' : '').' event clearback sticky" data-layer="'.$layer_key.'" style="z-index: '.$layer_offset_z.'; border-color: transparent;">';
                            $this_key = -1;
                            foreach ($attachments_list AS $this_id => $this_info){
                                if ($flag_wap && preg_match('/^mecha/i', $this_id)){ continue; }
                                $this_key++;
                                $this_class = $this_info['class'];
                                $this_size = intval($this_info['size']);
                                $this_boxsize = $this_size.'x'.$this_size;
                                $this_offset_x = $this_info['offset_x'];
                                $this_offset_y = $this_info['offset_y'];
                                $this_offset_z = isset($this_info['offset_z']) ? $this_info['offset_z'] : $this_key + 1;
                                $this_token = $this_info[$this_class.'_token'];
                                $this_text = !empty($this_info[$this_class.'_text']) ? $this_info[$this_class.'_text'] : '&nbsp;';
                                $this_path = $class_paths[$this_class];
                                if (isset($this_info['is_shadow']) && $this_info['is_shadow'] === true){ $this_path .= '_shadows'; }
                                $this_image = !empty($this_info[$this_class.'_image']) ? $this_info[$this_class.'_image'] : $this_token;
                                if ($this_class === 'object' && (!isset($this_info['subclass']) || $this_info['subclass'] !== 'common_object')){
                                    $this_path = $class_paths['field'];
                                    $this_image = $this_field_data['field_foreground'].'_'.$this_image;
                                }
                                $this_frames = $this_info[$this_class.'_frame'];
                                // We want mechas, but not actual robots replaced
                                if ($this_class == 'robot' && !empty($this_field_data['field_mechas']) && preg_match('/^mecha/i', $this_id)){
                                    $this_token = $this_field_data['field_mechas'][array_rand($this_field_data['field_mechas'])];
                                    if (!isset($this_robot_index[$this_token])){ $this_robot_index[$this_token] = rpg_robot::get_index_info($this_token); }
                                    $this_index = $this_robot_index[$this_token];
                                    $this_image = $this_token;
                                    if (!empty($this_battle_data['battle_complete']['battle_count'])
                                        && !empty($this_index['robot_image_alts'])){
                                        $images = array($this_token);
                                        foreach ($this_index['robot_image_alts'] AS $alt){
                                            if (count($images) > $this_battle_data['battle_complete']['battle_count']){ break; }
                                            $images[] = $this_token.'_'.$alt['token'];
                                        }
                                        shuffle($images);
                                        $this_image = array_shift($images);
                                    }
                                    $temp_size = (intval($this_index['robot_image_size']) * 2);
                                    if ($temp_size !== $this_size){
                                        $tmp_diff = $temp_size - $this_size;
                                        $this_size = $temp_size;
                                        $this_boxsize = $temp_size.'x'.$temp_size;
                                        $this_offset_x -= round($tmp_diff / 2);
                                    }
                                    $this_frames = array();
                                    $temp_frames = mt_rand(1, 20);
                                    $temp_options = array(0, 1, 2, 8);
                                    for ($i = 0; $i < $temp_frames; $i++){ $this_frames[] = $temp_options[array_rand($temp_options)]; }
                                    foreach ($temp_options AS $i){ if (!in_array($i, $this_frames)){ $this_frames[] = $i; } }
                                }

                                $this_frames_shift = isset($this_info[$this_class.'_frame_shift']) ? $this_info[$this_class.'_frame_shift'] : array();
                                foreach ($this_frames AS $key => $frame){ if (is_numeric($frame)){ $this_frames[$key] = str_pad($frame, 2, '0', STR_PAD_LEFT); } }
                                $this_frame_styles = isset($this_info[$this_class.'_frame_styles']) ? $this_info[$this_class.'_frame_styles'] : '';
                                $this_frame = $this_frames[0];
                                $this_animate = implode(',', $this_frames);
                                $this_animate_shift = implode('|', $this_frames_shift);
                                $this_animate_enabled = count($this_frames) > 1 ? true : false;
                                $this_direction = $this_info[$this_class.'_direction'];
                                $this_float = $this_direction == 'left' ? 'right' : 'left';
                                $this_image_url = 'images/'.$this_path.'/'.$this_image.'/sprite_'.$this_direction.'_'.$this_boxsize.'.png';
                                $this_style_attr = '';
                                $this_style_attr .= $this_float.': '.$this_offset_x.'px; ';
                                $this_style_attr .= 'bottom: '.$this_offset_y.'px; ';
                                $this_style_attr .= 'z-index: '.$this_offset_z.'; ';
                                $this_style_attr .= 'background-image: url('.$this_image_url.'?'.MMRPG_CONFIG_CACHE_DATE.'); ';
                                $this_style_attr .= ($debug_flag_spriteboxes ? 'background-color: rgba(255, 0, 0, 0.5); opacity: 0.75; ' : '');
                                $this_style_attr .= (!empty($this_frame_styles) ? trim($this_frame_styles).' ' : '');
                                echo '<div '.
                                    'data-id="foreground_attachment_'.$this_id.'" '.
                                    'data-type="attachment" '.
                                    'data-position="foreground" '.
                                    'data-size="'.$this_size.'" '.
                                    'data-direction="'.$this_direction.'" '.
                                    'data-frame="'.$this_frame.'" '.
                                    ''.(!empty($this_animate_enabled) ? 'data-animate="'.$this_animate.'"' : '').' '.
                                    ''.(!empty($this_animate_shift) ? 'data-animate-shift="'.$this_animate_shift.'"' : '').' '.
                                    'class="sprite sprite_'.$this_boxsize.' sprite_'.$this_boxsize.'_'.$this_direction.' sprite_'.$this_boxsize.'_'.$this_frame.'" '.
                                    'style="'.$this_style_attr.'" '.
                                    '>'.$this_text.'</div>';
                            }
                            echo '</div>';
                        }

                    }

                }
                // Otherwise, simply print the ready message
                else {
                    echo 'Ready?';
                }

                // Check to see if a Rogue Star is currently in orbit
                if (empty($this_battle_data['flags']['player_battle'])
                    && empty($this_battle_data['flags']['challenge_battle'])){
                    $this_rogue_star = mmrpg_prototype_get_current_rogue_star();
                    if (!empty($this_rogue_star)){
                        $star_type = $this_rogue_star['star_type'];
                        $star_name = ucfirst($star_type);
                        $now_time = time();
                        $star_from_time = strtotime($this_rogue_star['star_from_date'].'T'.$this_rogue_star['star_from_date_time']);
                        $star_to_time = strtotime($this_rogue_star['star_to_date'].'T'.$this_rogue_star['star_to_date_time']);
                        $star_time_duration = $star_to_time - $star_from_time;
                        $star_time_elapsed = $now_time - $star_from_time;
                        $star_time_elapsed_percent = ($star_time_elapsed / $star_time_duration) * 100;
                        $star_time_remaining = $star_time_duration - $star_time_elapsed;
                        $star_position_right = (100 - $star_time_elapsed_percent) + 1;
                        $star_minutes_left = ($star_time_remaining / 60);
                        $star_hours_left = ($star_minutes_left / 60);
                        $star_tooltip = '&raquo; Rogue Star Event! &laquo; || A '.$star_name.'-type Rogue Star has appeared! This star grants +'.$this_rogue_star['star_power'].' '.$star_name.'-type Starforce for a limited time. Take advantage of its power before it\'s gone! ';
                        if ($star_hours_left >= 1){ $star_tooltip .= 'You have less than '.($star_hours_left > 1 ? ceil($star_hours_left).' hours' : '1 hour').' remaining! '; }
                        elseif ($star_hours_left < 1){ $star_tooltip .= 'You have only '.($star_minutes_left > 1 ? ceil($star_minutes_left).' minutes' : '1 minute').' remaining! ';  }
                        ?>
                        <div class="sprite rogue_star loading <?= $flag_skip_fadein ? 'hidelabel' : '' ?>"
                            data-star-type="<?= $star_type ?>"
                            data-from-date="<?= $this_rogue_star['star_from_date'] ?>"
                            data-from-date-time="<?= $this_rogue_star['star_from_date_time'] ?>"
                            data-to-date="<?= $this_rogue_star['star_to_date'] ?>"
                            data-to-date-time="<?= $this_rogue_star['star_to_date_time'] ?>"
                            data-star-power="<?= $this_rogue_star['star_power'] ?>"
                            data-tooltip="<?= $star_tooltip ?>"
                            data-tooltip-type="type_<?= $star_type ?>">
                            <div class="wrap">
                                <div class="label">
                                    <div class="name type_empty">Rogue Star!</div>
                                    <div class="effect type_<?= $star_type ?>"><?= ucfirst($star_type) ?> +<?= $this_rogue_star['star_power'] ?></div>
                                </div>
                                <div class="sprite track type_<?= $star_type ?>"></div>
                                <div class="sprite trail type_<?= $star_type ?>" style="right: <?= $star_position_right ?>%;"></div>
                                <div class="sprite ruler"></div>
                                <div class="sprite star" style="background-image: url(images/items/fusion-star_<?= $star_type ?>/sprite_right_40x40.png); right: <?= $star_position_right ?>%; right: calc(<?= $star_position_right ?>% - 20px);"></div>
                            </div>
                        </div>
                        <?
                    }
                }

                ?>
            </div>

            <div class="event event_details clearback sticky" style="">
                <?
                // Display the scanline layer if enabled
                if ($debug_flag_scanlines){ echo '<div class="foreground scanlines" style="background-image: url(images/assets/canvas-scanlines.png?'.MMRPG_CONFIG_CACHE_DATE.'); opacity: 1;">&nbsp;</div>'; }
                ?>
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
                <a class="button action_loading button_disabled" type="button"><label><strong>Loading...</strong></label></a>
            </div>
        </div>
        <div id="actions_battle" class="actions_battle wrapper"></div>
        <div id="actions_ability" class="actions_ability wrapper"></div>
        <div id="actions_item" class="actions_ability actions_item wrapper"></div>
        <div id="actions_switch" class="actions_switch wrapper"></div>
        <div id="actions_scan" class="actions_scan wrapper"></div>
        <div id="actions_target_this" class="actions_target action_target_this wrapper"></div>
        <div id="actions_target_this_disabled" class="actions_target action_target_this_disabled wrapper"></div>
        <div id="actions_target_this_ally" class="actions_target action_target_this_ally wrapper"></div>
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
        <div id="actions_settings_animationEffects" class="actions_settings actions_settings_animationEffects wrapper">
            <?

            // Precollect the animation effects index so we can use it later
            $animation_effects_index = rpg_canvas::get_animation_effects_index();

            // Predefine the game speeds index so we can use it later
            $game_speeds_index = array(
                1600 => array('token' => 'super-slow', 'name' => 'Super Slow', 'value' => 1600),
                1250 => array('token' => 'medium-slow', 'name' => 'Medium Slow', 'value' => 1250),
                1000 => array('token' => 'normal-slow', 'name' => 'Normal Slow', 'value' => 1000),
                900 => array('token' => 'normal', 'name' => 'Normal', 'value' => 900),
                800 => array('token' => 'normal-fast', 'name' => 'Normal Fast', 'value' => 800),
                700 => array('token' => 'medium-fast', 'name' => 'Medium Fast', 'value' => 700),
                600 => array('token' => 'super-fast', 'name' => 'Super Fast', 'value' => 600),
                250 => array('token' => 'ultra-fast', 'name' => 'Ultra Fast', 'value' => 250)
                );

            // Predefine the render modes index
            $render_modes_index = array(
                'default' => array('token' => 'default', 'name' => 'Default', 'label' => 'Default'),
                //'auto' => array('token' => 'auto', 'name' => 'Auto', 'label' => 'Browser "Auto"'),
                //'smooth' => array('token' => 'smooth', 'name' => 'Smooth', 'label' => 'Browser "Smooth"'),
                //'high-quality' => array('token' => 'high-quality', 'name' => 'High-Quality', 'label' => 'Browser "High-Quality"'),
                'crisp-edges' => array('token' => 'crisp-edges', 'name' => 'Crisp-Edges', 'label' => 'Browser "Crisp-Edges"'),
                'pixelated' => array('token' => 'pixelated', 'name' => 'Pixelated', 'label' => 'Browser "Pixelated"')
                );

            ?>
            <div class="main_actions main_actions_hastitle">
                <span class="main_actions_title">Customize Animation Settings</span>
                <?

                // Predefine a block number counter for later
                $block_num = 0;

                // Manually add buttons for sub-menus related to animation
                if (true){

                    // Add a button for the GAME SPEED submenu
                    $block_num++;
                    $setting_name = 'Game Speed';
                    $setting_token = 'eventTimeout';
                    $default_value = 'normal';
                    $current_value = $default_value;
                    if (isset($_SESSION['GAME']['battle_settings'][$setting_token])){
                        $value = $_SESSION['GAME']['battle_settings'][$setting_token];
                        if (empty($value) || $value === 'false'){ $value = false; }
                        elseif (!empty($value) && $value === 'true'){ $value = true; }
                        $current_value = $value;
                    }
                    $current_value_title = $current_value;
                    if (isset($game_speeds_index[$current_value]['name'])){ $current_value_title = $game_speeds_index[$current_value]['name']; }
                    echo('<a data-order="'.$block_num.'" class="button action_option action_setting block_'.$block_num.'" type="button" data-panel="settings_'.$setting_token.'">');
                        echo('<label><span class="multi">');
                            echo('<span class="title">'.$setting_name.'</span>');
                            echo('<br />');
                            echo('<span class="value type type_time">');
                                echo($current_value_title);
                            echo('</span>');
                        echo('</span></label>');
                    echo('</a>');


                }

                // Loop through the defined ON/OFF animation effects and print buttons for each
                foreach ($animation_effects_index AS $effect_key => $effect_info){
                    $block_num++;
                    $setting_name = $effect_info['name'];
                    $setting_token = $effect_info['token'];
                    $default_value = $effect_info['default'];
                    $current_value = $default_value;
                    if (isset($_SESSION['GAME']['battle_settings'][$setting_token])){
                        $value = $_SESSION['GAME']['battle_settings'][$setting_token];
                        if (empty($value) || $value === 'false'){ $value = false; }
                        elseif (!empty($value) && $value === 'true'){ $value = true; }
                        $current_value = $value;
                    }
                    $next_value = !$current_value ? true : false;
                    $data_attrs = '';
                    $data_attrs .= 'data-order="'.$block_num.'" ';
                    $data_attrs .= 'onclick="mmrpg_toggle_settings_option(this);"  data-setting-token="'.$setting_token.'" data-setting-value="'.($current_value ? 1 : 0).'" ';
                    echo('<a class="button action_option action_setting block_'.$block_num.'" type="button" '.$data_attrs.'>');
                        echo('<label><span class="multi">');
                            echo('<span class="title">'.$setting_name.'</span>');
                            echo('<br />');
                            echo('<span class="value type type_'.($current_value ? 'nature' : 'flame').'">');
                                echo($current_value ? 'ON' : 'OFF');
                            echo('</span>');
                        echo('</span></label>');
                    echo('</a>');
                }

                // Manually add buttons for sub-menus related to animation
                if (true){

                    // Add a button for the SPRITE RENDERING submenu
                    $block_num++;
                    $setting_name = 'Sprite Rendering';
                    $setting_token = 'spriteRenderMode';
                    $default_value = 'default';
                    $current_value = $default_value;
                    if (isset($_SESSION['GAME']['battle_settings'][$setting_token])){
                        $value = $_SESSION['GAME']['battle_settings'][$setting_token];
                        if (empty($value) || $value === 'false'){ $value = false; }
                        elseif (!empty($value) && $value === 'true'){ $value = true; }
                        $current_value = $value;
                    }
                    $current_value_title = ucwords(str_replace('-', ' ', $current_value));
                    if (isset($render_modes_index[$current_value]['name'])){ $current_value_title = $render_modes_index[$current_value]['name']; }
                    echo('<a data-order="'.$block_num.'" class="button action_option action_setting block_'.$block_num.'" type="button" data-panel="settings_'.$setting_token.'">');
                        echo('<label><span class="multi">');
                            echo('<span class="title">'.$setting_name.'</span>');
                            echo('<br />');
                            echo('<span class="value type type_explode">');
                                echo($current_value_title);
                            echo('</span>');
                        echo('</span></label>');
                    echo('</a>');


                }

                // If there were less than eight buttons, we should print spacers
                while ($block_num < 8){
                    $block_num++;
                    echo('<a class="button action_setting button_disabled block_'.$block_num.'" type="button">&nbsp;</a>');
                }

                ?>
            </div>
            <div class="sub_actions"><a data-order="9" class="button action_back" type="button" data-panel="option"><label>Back</label></a></div>
        </div>
        <div id="actions_settings_eventTimeout" class="actions_settings actions_settings_eventTimeout wrapper">
            <div class="main_actions">
                <? $block_num = 0;
                foreach ($game_speeds_index as $value => $speed){ $block_num++; ?>
                    <a data-order="<?= $block_num ?>" class="button action_setting block_<?= $block_num ?>" type="button" data-action="settings_eventTimeout_<?= $speed['value'] ?>"><label><span class="multi"><?= $speed['name'] ?><br />(1f/<?= $speed['value'] ?>ms)</span></label></a>
                <? } ?>
            </div>
            <div class="sub_actions"><a data-order="9" class="button action_back" type="button" data-panel="settings_animationEffects"><label>Back</label></a></div>
        </div>
        <div id="actions_settings_spriteRenderMode" class="actions_settings actions_settings_spriteRenderMode wrapper">
            <div class="main_actions">
                <? $block_num = 0;
                foreach ($render_modes_index as $token => $mode){ $block_num++; ?>
                    <a data-order="<?= $block_num ?>" class="button action_setting block_<?= $block_num ?>" type="button" data-action="settings_spriteRenderMode_<?= $mode['token'] ?>"><label><span class="multi"><?= str_replace(' ', '<br />', str_replace('"', '&quot;', $mode['label'])) ?></span></label></a>
                <? } ?>
            </div>
            <!--
            <div class="main_actions">
                <a data-order="1" class="button action_setting block_1" type="button" data-action="settings_spriteRenderMode_default"><label><span>Default</span></label></a>
                <a data-order="2" class="button action_setting block_2" type="button" data-action="settings_spriteRenderMode_auto"><label><span class="multi">Browser<br />&quot;Auto&quot;</span></label></a>
                <a data-order="3" class="button action_setting block_3" type="button" data-action="settings_spriteRenderMode_smooth"><label><span class="multi">Browser<br />&quot;Smooth&quot;</span></label></a>
                <a data-order="4" class="button action_setting block_4" type="button" data-action="settings_spriteRenderMode_pixelated"><label><span class="multi">Browser<br />&quot;Pixelated&quot;</span></label></a>
                <a data-order="5" class="button action_setting block_5" type="button" data-action="settings_spriteRenderMode_high-quality"><label><span class="multi">Browser<br />&quot;High-Quality&quot;</span></label></a>
                <a data-order="6" class="button action_setting block_6" type="button" data-action="settings_spriteRenderMode_crisp-edges"><label><span class="multi">Browser<br />&quot;Crisp-Edges&quot;</span></label></a>
                <a class="button action_setting button_disabled block_7" type="button">&nbsp;</a>
                <a class="button action_setting button_disabled block_8" type="button">&nbsp;</a>
            </div>
            -->
            <div class="sub_actions"><a data-order="9" class="button action_back" type="button" data-panel="settings_animationEffects"><label>Back</label></a></div>
        </div>
        <div id="actions_event" class="actions_event wrapper">
            <div class="main_actions">
                <a data-order="1" class="button action_continue" type="button" data-action="continue"><label>Continue</label></a>
            </div>
        </div>
    </div>

    <div id="effects">
        <div class="wrapper">
            <?
            $temp_button_colours = array();
            $temp_button_colours[] = !empty($this_player_data['player_type']) ? $this_player_data['player_type'] : 'none';
            if (!empty($this_field_data['field_type'])){ $temp_button_colours[] = $this_field_data['field_type']; }
            $temp_button_colours = array_unique($temp_button_colours);
            $temp_button_colours_string1 = 'type_'.implode('_', $temp_button_colours);
            $temp_button_colours_string2 = 'type_'.implode('_', array_reverse($temp_button_colours));
            ?>
            <div class="canvas_border type <?= $temp_button_colours_string1 ?>"></div>
        </div>
    </div>

    <iframe id="connect" name="connect" src="about:blank">
    </iframe>

    <div id="event_console_backup" style="display: none;">
    </div>

</div>
<script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
<script type="text/javascript" src=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/battle.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">

// Update relevent game settings and flags
<? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>
gameSettings.idleAnimation = <?= $debug_flag_animation ? 'true' : 'false' ?>;
gameSettings.fieldMusic = '<?= !strstr($this_field_data['field_music'], '/') ? 'fields/'.$this_field_data['field_music'] : $this_field_data['field_music'] ?>';
gameSettings.customIndex.animationEffects = <?= json_encode($animation_effects_index) ?>;
gameSettings.customIndex.gameSpeeds = <?= json_encode($game_speeds_index) ?>;
gameSettings.customIndex.renderModes = <?= json_encode($render_modes_index) ?>;
gameSettings.currentBattleData = <?= json_encode($this_battle_data) ?>;

// Create the document ready events
$(document).ready(function(){

    // Make sure the music button is in the appropriate place
    top.mmrpg_music_context('battle');

<?
// Preload the target player robot sprites first because we see them first
if (!empty($target_player_data) && !empty($target_player_data['player_robots'])){
    foreach ($target_player_data['player_robots'] AS $temp_key => $temp_robotinfo){
        $temp_robot_indexinfo = rpg_robot::get_index_info($temp_robotinfo['robot_token']);
        $temp_robot_imagesize =!empty($temp_robot_indexinfo) && !empty($temp_robot_indexinfo['robot_image_size']) ? $temp_robot_indexinfo['robot_image_size'] : 40;
        $temp_robot_zoomsize = $temp_robot_imagesize * 2;
        echo "  top.mmrpg_preload_robot_sprites('{$temp_robotinfo['robot_token']}', 'left', {$temp_robot_zoomsize});\n";
    }
}
// Preload this player robot sprites second as we see them after the target
if (!empty($this_player_data) && !empty($this_player_data['player_robots'])){
    foreach ($this_player_data['player_robots'] AS $temp_key => $temp_robotinfo){
        $temp_robot_indexinfo = rpg_robot::get_index_info($temp_robotinfo['robot_token']);
        $temp_robot_imagesize =!empty($temp_robot_indexinfo) && !empty($temp_robot_indexinfo['robot_image_size']) ? $temp_robot_indexinfo['robot_image_size'] : 40;
        $temp_robot_zoomsize = $temp_robot_imagesize * 2;
        echo "  top.mmrpg_preload_robot_sprites('{$temp_robotinfo['robot_token']}', 'right', {$temp_robot_zoomsize});\n";
    }
}
// Preload the field sprites last as they're mostly taken care of by the battle script
if (!empty($this_battle_data)){
    echo "  top.mmrpg_preload_field_sprites('background', '{$this_field_data['field_background']}');\n";
    echo "  top.mmrpg_preload_field_sprites('foreground', '{$this_field_data['field_foreground']}');\n";
}
?>

<? if (rpg_game::is_user()){ ?>
    // The user is logged-in so let's keep the session alive
    mmrpg_keep_session_alive(<?= rpg_game::get_userid() ?>);
<? } ?>

});

</script>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php');
// Unset the database variable
unset($db);
?>
</body>
</html>