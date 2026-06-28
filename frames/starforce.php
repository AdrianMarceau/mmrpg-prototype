<?
// Require the application top file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
define('MMRPG_REMOTE_SKIP_COMPLETE', true);
define('MMRPG_REMOTE_SKIP_FAILURE', true);
define('MMRPG_REMOTE_SKIP_SETTINGS', true);
define('MMRPG_REMOTE_SKIP_ITEMS', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
$session_token = mmrpg_game_token();

// Collect the editor flag if set
$global_allow_editing = !defined('MMRPG_REMOTE_GAME') ? true : false;
if (isset($_GET['edit']) && $_GET['edit'] == 'false'){ $global_allow_editing = false; }
$global_frame_source = !empty($_GET['source']) ? trim($_GET['source']) : 'prototype';

// Require the prototype omega data file
require_once('../prototype/omega.php');
$unlocked_factor_one_robots = false;
$unlocked_factor_two_robots = false;
$unlocked_factor_three_robots = false;
$unlocked_factor_four_robots = false;
$temp_omega_factor_options = array();
$temp_omega_factor_options_unlocked = array();
$temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_one); $unlocked_factor_one_robots = true;
$temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_two); $unlocked_factor_two_robots = true;
$temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_three); $unlocked_factor_three_robots = true;
$temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_four); $unlocked_factor_four_robots = true;
//error_log('$temp_omega_factor_options = '.print_r($temp_omega_factor_options, true));

// Collect any fields unlocked via other means
$temp_unlocked_fields = mmrpg_prototype_unlocked_field_tokens();
//error_log('$temp_unlocked_fields = '.print_r($temp_unlocked_fields, true));

// Loop through the collected options and pull just the robot tokens
foreach ($temp_omega_factor_options AS $key => $factor){
    $temp_omega_factor_options_unlocked[] = $factor['field'];
}
//error_log('$temp_omega_factor_options_unlocked = '.print_r($temp_omega_factor_options_unlocked, true));

// Require the types and starforce data files
require_once(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require_once(MMRPG_CONFIG_ROOTDIR.'includes/starforce.php');

// Collect the type, robot, and field indexes for names and such
$rpg_types_index = rpg_type::get_index();
$rpg_types_index_tokens = array_keys($rpg_types_index);
//error_log('$rpg_types_index_tokens = '.print_r($rpg_types_index_tokens, true));
$rpg_robots_index = rpg_robot::get_index();
$rpg_robots_index_tokens = array_keys($rpg_robots_index);
//error_log('$rpg_robots_index_tokens = '.print_r($rpg_robots_index_tokens, true));
$rpg_fields_index = rpg_field::get_index();
$rpg_fields_index_tokens = array_keys($rpg_fields_index);
//error_log('$rpg_fields_index_tokens = '.print_r($rpg_fields_index_tokens, true));

//error_log('$this_battle_stars = '.print_r($this_battle_stars, true));
//error_log('$this_star_kind_counts = '.print_r($this_star_kind_counts, true));
// Check if the user has unlocked any boss or any field/fusion stars beforehand
$user_has_boss_stars = !empty($this_star_kind_counts['boss']) ? true : false;
$user_has_field_stars = !empty($this_star_kind_counts['field']) ? true : false;
$user_has_fusion_stars = !empty($this_star_kind_counts['fusion']) || !empty($this_star_kind_counts['perfect-fusion']) ? true : false;
$hide_field_fusion_stars = !$user_has_field_stars && !$user_has_fusion_stars ? true : false;

// Collect all the robots that have been unlocked by the player
$rpg_robots_encountered = array();
if (!empty($_SESSION[$session_token]['values']['robot_database'])){
    $rpg_robots_encountered = array_keys($_SESSION[$session_token]['values']['robot_database']);
}

// Count the number of unlockable robots so we can calculate the number of boss stars
$temp_boss_stars_available = array_filter($rpg_robots_index_tokens,
    function($token) use ($rpg_robots_index){
        $info = $rpg_robots_index[$token];
        if ($info['robot_class'] !== 'master'){ return false; }
        else if (empty($info['robot_flag_published'])){ return false; }
        else if (empty($info['robot_flag_complete'])){ return false; }
        //else if (empty($info['robot_flag_unlockable'])){ return false; }
        if ($info['robot_core'] === ''){ return false; }
        else if ($info['robot_core'] === 'copy'){ return false; }
        return true;
    });
$temp_boss_stars_available = array_values($temp_boss_stars_available);
$temp_boss_stars_total = !empty($temp_boss_stars_available) ? count($temp_boss_stars_available) : 0;
//error_log('$temp_boss_stars_total = '.print_r($temp_boss_stars_total, true));
//error_log('$temp_boss_stars_available = '.print_r($temp_boss_stars_available, true));

// Collect the omega factors that we should be printing links for
$temp_omega_factors_unlocked = array();
if ($unlocked_factor_one_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_one); }
if ($unlocked_factor_two_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_two); }
if ($unlocked_factor_four_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_four); }
if ($unlocked_factor_three_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_three); }
$temp_omega_factors_unlocked_total = count($temp_omega_factors_unlocked);
//error_log('$temp_omega_factors_unlocked = '.print_r($temp_omega_factors_unlocked, true));
//error_log('$temp_omega_factors_unlocked_total = '.print_r($temp_omega_factors_unlocked_total, true));

// Collect the omega groups that we should be printing links for
$temp_omega_groups_unlocked = array();
if ($unlocked_factor_one_robots){ $temp_omega_groups_unlocked[] = array('token' => 'MM1', 'omega' => $this_omega_factors_one); }
if ($unlocked_factor_two_robots){ $temp_omega_groups_unlocked[] = array('token' => 'MM2', 'omega' => $this_omega_factors_two); }
if ($unlocked_factor_four_robots){ $temp_omega_groups_unlocked[] = array('token' => 'MM4', 'omega' => $this_omega_factors_four); }
if ($unlocked_factor_three_robots){ $temp_omega_groups_unlocked[] = array('token' => 'MM3', 'omega' => $this_omega_factors_three); }
//error_log('$temp_omega_groups_unlocked = '.print_r($temp_omega_groups_unlocked, true));

// Define a function for printing robots in the boss stars list
function print_starlist_omega_robot($robot, $key = 0, $visible = true){
    global $rpg_robots_encountered, $rpg_robots_index, $this_battle_stars;
    $robot_info = !empty($robot) && !empty($rpg_robots_index[$robot]) ? $rpg_robots_index[$robot] : false;
    $star_info = !empty($robot) && !empty($this_battle_stars[$robot]) ? $this_battle_stars[$robot] : false;
    if (!empty($robot) && !empty($robot_info)){

        $robot_name = $robot_info['robot_name'];
        $robot_type = $robot_info['robot_core'];
        $robot_size = $robot_info['robot_image_size'] ? $robot_info['robot_image_size'] : 40;

        $robot_was_encountered = in_array($robot, $rpg_robots_encountered) ? true : false;
        $star_was_collected = !empty($star_info) ? true : false;

        $tooltip_title = '';
        $tooltip_type = '';
        if ($robot_was_encountered && $star_was_collected){
            $tooltip_title = '<div style="text-align: center;">';
                $tooltip_title .= $robot_name.' Star <br /> ';
                $tooltip_title .= '<span style="font-size: 80%;">+1 '.ucfirst($robot_type).' | Boss Star</span> <br />';
                if (!empty($star_info['star_date'])){ $tooltip_title .= '<span style="font-size: 80%;">Found '.date('Y/m/d', $star_info['star_date']).'</span> '; }
            $tooltip_title .= '</div>';
            $tooltip_title = htmlentities($tooltip_title, ENT_QUOTES, 'UTF-8');
            $tooltip_type = 'robot_type robot_type_'.$robot_type;
        } elseif ($robot_was_encountered){
            $tooltip_title = '<div style="text-align: center;">';
                $tooltip_title .= $robot_name.' Star <br /> ';
                $tooltip_title .= '<span style="font-size: 80%; opacity: 0.6;">Not Yet Found</span>';
            $tooltip_title .= '</div>';
            $tooltip_title = htmlentities($tooltip_title, ENT_QUOTES, 'UTF-8');
            $tooltip_type = 'robot_type robot_type_'.$robot_type;
        } else {
            $tooltip_title = false;
            $tooltip_type = false;
        }

        $icon_class = '';
        $icon_sprite_class = '';
        $icon_sprite_style = '';
        $icon_sprite_markup = '';
        if ($robot_was_encountered){
            $icon_class = 'icon robot_type robot_type_'.$robot_type.' ';
            $icon_sprite_class = 'sprite sprite_'.$robot_size.'x'.$robot_size.' robot_type robot_type_empty';
            $icon_sprite_style = 'background-image: url(images/robots/'.$robot.'/mug_right_'.$robot_size.'x'.$robot_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';
            $icon_sprite_markup = '<span class="'.$icon_sprite_class.'" style="'.$icon_sprite_style.'">&nbsp;</span>';
        } else {
            $icon_class = 'icon robot_type robot_type_empty shadow ';
            $icon_sprite_class = 'sprite sprite_40x40 robot_type robot_type_empty';
            $icon_sprite_markup = '<span class="'.$icon_sprite_class.'">&nbsp;</span>';
        }

        $star_class = '';
        $star_sprite_class = '';
        $star_sprite_style = '';
        $star_sprite_markup = '';
        if ($star_was_collected){
            $star_class = 'star sprite sprite_40x40 sprite_star visible ';
            $star_sprite_class = 'sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_00';
            $star_sprite_style = 'background-image: url(images/items/field-star_'.$robot_type.'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');';
            $star_sprite_markup = '<div class="'.$star_sprite_class.'" style="'.$star_sprite_style.' z-index: 2;">&nbsp;</div>';
            $star_sprite_markup .= '<div class="'.$star_sprite_class.'" style="'.$star_sprite_style.' z-index: 1;">&nbsp;</div>'; // shadow sprite
        } else {
            $star_class = 'star sprite sprite_40x40 sprite_star visible shadow ';
            $star_sprite_class = 'sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_00';
            $star_sprite_style = 'background-image: url(images/items/field-star_empty/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');';
            $star_sprite_markup .= '<div class="'.$star_sprite_class.'" style="'.$star_sprite_style.' z-index: 1;">&nbsp;</div>'; // shadow sprite
        }

        $return_markup = '';
            $return_markup .= '<li class="robot'.
                    ($robot_was_encountered ? ' encountered' : '').
                    ($star_was_collected ? ' collected' : '').
                    (!$visible ? ' hidden' : '').
                    '"'.
                ($tooltip_title ? ' data-click-tooltip="'.$tooltip_title.'"' : '').
                ($tooltip_type ? ' data-tooltip-type="'.$tooltip_type.'' : '').
                '" data-key="'.$key.'">';
                $return_markup .= '<div class="'.$icon_class.'">'.$icon_sprite_markup.'</div>';
                $return_markup .= '<div class="'.$star_class.'">'.$star_sprite_markup.'</div>';
            $return_markup .= '</li>';
        $return_markup .= "\n";

        return $return_markup;

    } else {

        $icon_class = 'icon robot_type robot_type_empty ';
        $icon_sprite_class = 'sprite sprite_40x40 robot_type robot_type_empty';
        $icon_sprite_markup = '<span class="'.$icon_sprite_class.'">&nbsp;</span>';

        $star_class = 'star sprite sprite_40x40 sprite_star visible shadow ';
        $star_sprite_class = 'sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_00';
        $star_sprite_style = 'background-image: url(images/items/field-star_empty/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');';
        $star_sprite_markup = '<div class="'.$star_sprite_class.'" style="'.$star_sprite_style.' z-index: 1;">&nbsp;</div>'; // shadow sprite

        $return_markup = '';
            $return_markup .= '<li class="robot unknown '.(!$visible ? ' hidden' : '').'" data-key="'.$key.'">';
                $return_markup .= '<div class="'.$icon_class.'">'.$icon_sprite_markup.'</div>';
                $return_markup .= '<div class="'.$star_class.'">'.$star_sprite_markup.'</div>';
            $return_markup .= '</li>';
        $return_markup .= "\n";

        return $return_markup;

    }


}

// Define a function for printing out the robot links
function print_starchart_omega_robot($info, $key, $kind){
    global $rpg_robots_encountered, $rpg_robots_index;
    $robot = $info['robot'];
    $type = $info['type'];
    $field = $info['field'];
    if (in_array($robot, $rpg_robots_encountered)){

        $info = $rpg_robots_index[$robot];
        $name = $info['robot_name'];
        $size = $info['robot_image_size'] ? $info['robot_image_size'] : 40;
        if (!empty($field)){ list($field_one, $field_two) = explode('-', $field); }
        else { $field_one = ''; $field_two = ''; }

        $title = '<div style="text-align: center;">';
            $title .= $name.' <br /> ';
            $title .= '<span style="font-size: 10px;">'.ucfirst($field_one).' '.ucfirst($field_two).'</span>';
        $title .= '</div>';
        $title = htmlentities($title, ENT_QUOTES, 'UTF-8');

        $mug_dir = $kind == 'side' ? 'right' : 'left';
        $sprite_class = 'sprite sprite_'.$size.'x'.$size.' robot_type robot_type_empty';
        $sprite_style = 'background-image: url(images/robots/'.$robot.'/mug_'.$mug_dir.'_'.$size.'x'.$size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';
        $sprite_markup = '<span class="'.$sprite_class.'" style="'.$sprite_style.'">&nbsp;</span>';

        $icon_class = 'icon robot_type robot_type_'.$type.' ';
        $icon_markup = '<a class="'.$icon_class.'" data-'.$kind.'-key="'.$key.'" data-click-tooltip="'.$title.'">%s</a>';

        $return_markup = sprintf($icon_markup, $sprite_markup)."\n";

        return $return_markup;

    } else {

        $sprite_class = 'sprite sprite_40x40 robot_type robot_type_empty';
        $sprite_markup = '<span class="'.$sprite_class.'">&nbsp;</span>';

        $icon_class = 'icon robot_type robot_type_empty ';
        $icon_markup = '<a class="'.$icon_class.'" data-'.$kind.'-key="'.$key.'">%s</a>';

        $return_markup = sprintf($icon_markup, $sprite_markup)."\n";

        return $return_markup;

    }
}

// Define a function for printing out the field links
function print_starchart_omega_field($info, $key, $kind){
    global $rpg_robots_encountered, $rpg_robots_index, $rpg_fields_index;
    $field = $info['field'];
    $robot = $info['robot'];
    $type = $info['type'];
    if (in_array($robot, $rpg_robots_encountered)){

        $field_info = $rpg_fields_index[$field];
        $field_name = $field_info['field_name'];
        $robot_info = $rpg_robots_index[$robot];
        $robot_name = $robot_info['robot_name'];
        $size = 100;
        if (!empty($field)){ list($field_one, $field_two) = explode('-', $field); }
        else { $field_one = ''; $field_two = ''; }

        $title = '<div style="text-align: center;">';
            $title .= $field_name.' <br /> ';
            $title .= '<span style="font-size: 10px;">('.$robot_name.')</span>';
        $title .= '</div>';
        $title = htmlentities($title, ENT_QUOTES, 'UTF-8');

        $mug_dir = $kind == 'side' ? 'right' : 'left';
        $sprite_class = 'sprite sprite_'.$size.'x'.$size.' field_type field_type_empty';
        $sprite_style = 'background-image: url(images/fields/'.$field.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';
        $sprite_markup = '<span class="'.$sprite_class.'" style="'.$sprite_style.'">&nbsp;</span>';

        $icon_class = 'icon field_type field_type_'.$type.' ';
        $icon_markup = '<a class="'.$icon_class.'" data-'.$kind.'-key="'.$key.'" data-click-tooltip="'.$title.'">%s</a>';

        $return_markup = sprintf($icon_markup, $sprite_markup)."\n";

        return $return_markup;

    } else {

        $sprite_class = 'sprite sprite_40x40 field_type field_type_empty';
        $sprite_markup = '<span class="'.$sprite_class.'">&nbsp;</span>';

        $icon_class = 'icon field_type field_type_empty ';
        $icon_markup = '<a class="'.$icon_class.'" data-'.$kind.'-key="'.$key.'">%s</a>';

        $return_markup = sprintf($icon_markup, $sprite_markup)."\n";

        return $return_markup;

    }
}

// Define a function for counting permutations
function temp_combination_number($k,$n){
    $n = intval($n);
    $k = intval($k);
    if ($k > $n){ return 0; }
    elseif ($n == $k){ return 1; }
    if ($k >= $n - $k){
        $l = $k+1; for ($i = $l+1 ; $i <= $n ; $i++){ $l *= $i; }
        $m = 1; for ($i = 2 ; $i <= $n-$k ; $i++){ $m *= $i; }
        }
    else {
        $l = ($n-$k) + 1; for ($i = $l+1 ; $i <= $n ; $i++){ $l *= $i; }
        $m = 1; for ($i = 2 ; $i <= $k ; $i++){ $m *= $i; }
        }
    return $l/$m;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Stars | Prototype | Mega Man RPG Prototype</title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/events.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/starforce.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/starforce-responsive.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<? $game_chartsRequired = true; ?>
<? require(MMRPG_CONFIG_ROOTDIR.'scripts/gamescripts.prototype.php'); ?>
<script type="text/javascript" src="scripts/starforce.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<? require(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.all.php'); ?>
</head>
<body id="mmrpg" class="iframe" data-frame="stars" data-mode="<?= $global_allow_editing ? 'editor' : 'viewer' ?>" data-source="<?= $global_frame_source ?>" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">

    <div id="prototype" class="hidden" style="<?= !$global_allow_editing ? 'width: 100% !important; ' : '' ?>">

        <div class="menu">

            <?php
            $temp_total_stars_label = $this_battle_stars_count;
            $temp_potential_count = ((temp_combination_number(2, $temp_omega_factors_unlocked_total) * 2) + $temp_omega_factors_unlocked_total + $temp_boss_stars_total);
            $temp_potential_stars_label = $temp_potential_count == 1 ? '1 Star' : $temp_potential_count.' Stars';
            ?>
            <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                <? /*
                <span class="count">
                    <i class="fa fas fa-star"></i>
                    Star Collection <span class="progress">(
                        <span><?= $temp_total_stars_label ?></span> /
                        <span><?= $temp_potential_stars_label ?></span>
                        )</span>
                </span>
                */ ?>
                <span class="count">
                    <i class="fa fas fa-star"></i>
                    Star Collection
                    <span class="progress">(
                        <span><?= $this_battle_stars_count.' '.($this_battle_stars_count === 1 ? 'Star' : 'Stars') ?></span>
                        )</span>
                </span>
                <div class="toggle" data-view="list">
                    <a class="option" data-view="list"><i class="fa fas fa-th"></i> <strong>List</strong></a>
                    <a class="option" data-view="stats"><i class="fa fas fa-signal"></i> <strong>Stats</strong></a>
                </div>
            </span>

            <div class="content stars fullsize" data-view="list">
                <div class="wrapper">

                    <?
                    $bosses_per_row = 8;
                    $bosses_per_page = 32;
                    $num_boss_pages = ceil($temp_boss_stars_total / $bosses_per_page);
                    $current_boss_page = 1;
                    ?>
                    <div class="container starlist<?= !$user_has_boss_stars ? ' none-found' : ''?><?= $hide_field_fusion_stars ? ' fullsize' : '' ?>">
                        <strong class="title"><?= $user_has_boss_stars || !$hide_field_fusion_stars ? 'Boss Stars' : '???' ?></strong>
                        <div class="wrapper">
                            <div class="pages" data-per-page="<?= $bosses_per_page ?>" data-current-page="<?= $current_boss_page ?>">
                                <a class="arrow prev" data-page="prev"></a>
                                <?
                                for ($key = 0; $key < $num_boss_pages; $key++){
                                    $page = $key + 1; $active = $page === $current_boss_page ? true : false;
                                    echo('<a class="page'.($active ? ' active' : '').'" data-page="'.$page.'">Page '.$page.'</a>');
                                }
                                ?>
                                <a class="arrow next" data-page="next"></a>
                            </div>
                            <ul class="robots">
                                <?
                                $robot_key = 0;
                                $last_game = '';
                                $game_counts = array();
                                foreach ($temp_boss_stars_available AS $key => $robot){
                                    $info = !empty($rpg_robots_index[$robot]) ? $rpg_robots_index[$robot] : false;
                                    $new_last_game = $info['robot_game']; if ($new_last_game === 'MMPU'){ $new_last_game = 'MM1'; }
                                    $last_game_count = !empty($last_game) ? $game_counts[$last_game] : -1;
                                    $last_game_changed = false;
                                    if (empty($last_game)
                                        || $last_game !== $new_last_game){
                                        if (!isset($game_counts[$new_last_game])){ $game_counts[$new_last_game] = 0; }
                                        $last_game_changed = !empty($last_game) ? true : false;
                                        $last_game = $new_last_game;
                                    }
                                    $game_counts[$last_game]++;
                                    if ($last_game_changed && $last_game_count < $bosses_per_row){
                                        $spacers_required = $bosses_per_row - $last_game_count;
                                        //error_log('$last_game_changed | $last_game_count '.$last_game_count.' | $spacers_required = '.$spacers_required);
                                        for ($i = 0; $i < $spacers_required; $i++){
                                            echo(print_starlist_omega_robot(false, $robot_key, ($robot_key < $bosses_per_page)));
                                            $robot_key++;
                                        }
                                    }
                                    echo(print_starlist_omega_robot($robot, $robot_key, ($robot_key < $bosses_per_page)));
                                    $robot_key++;
                                }
                                //error_log('$game_counts = '.print_r($game_counts, true));
                                ?>
                            </ul>
                        </div>
                    </div>

                    <div class="container starchart<?= !$user_has_field_stars && !$user_has_fusion_stars ? ' none-found' : ''?><?= $hide_field_fusion_stars ? ' hidden' : '' ?>">
                        <strong class="title"><?= $user_has_field_stars || $user_has_fusion_stars ? 'Field / Fusion Stars' : '???' ?></strong>
                        <div class="wrapper">

                            <div class="corner">
                                <?
                                // Generate and print out group bullets for the corner wedge
                                $bullets = '';
                                $bullcount = count($temp_omega_groups_unlocked);
                                foreach ($temp_omega_groups_unlocked AS $key => $group){
                                    $group_token = $group['token'];
                                    $group_class = 'bull ';
                                    $group_style = '';
                                    $group_current = $key == 0 ? true : false;
                                    if ($group_current){ $group_class .= 'current '; }
                                    $bullets .= '<span class="'.$group_class.'" data-group="'.$group_token.'">&nbsp;</span>';
                                }
                                echo '<div class="bullets topbar" data-count="'.$bullcount.'">'.$bullets.'</div>'.PHP_EOL;
                                echo '<div class="bullets sidebar" data-count="'.$bullcount.'">'.$bullets.'</div>'.PHP_EOL;
                                ?>
                            </div>

                            <?
                            // Loop through the top and side bar sections
                            $chart_keys_visible = array();
                            $chart_bar_kinds = array('top', 'side');
                            foreach ($chart_bar_kinds AS $bar_kind){

                                // Create an array for holding visible keys
                                $chart_keys_counter = 0;
                                $chart_keys_visible[$bar_kind] = array();

                                // Generate robot groups markup to print out
                                $current_token = '';
                                $groups_markup = array();
                                foreach ($temp_omega_groups_unlocked AS $key => $group){
                                    $group_token = $group['token'];
                                    $group_omega = $group['omega'];
                                    $group_size = count($group_omega);
                                    $group_current = $key == 0 ? true : false;
                                    if ($group_current){ $current_token = $group_token; }
                                    ob_start();
                                    ?>
                                    <div class="group <?= $group_current ? 'current' : '' ?>" data-group="<?= $group_token ?>" data-size="<?= $group_size ?>">
                                        <ul class="options fields">
                                            <?
                                            // Loop through and print omega fields
                                            foreach ($group_omega AS $key2 => $omega){
                                                $omega_robot = $omega['robot'];
                                                $omega_field = $omega['field'];
                                                $omega_cell = print_starchart_omega_field($omega, $chart_keys_counter, $bar_kind);
                                                if ($group_current){ $chart_keys_visible[$bar_kind][] = $chart_keys_counter; }
                                                ?>
                                                <li class="option field">
                                                    <?= $omega_cell ?>
                                                </li>
                                                <?
                                                $chart_keys_counter++;
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                    <?
                                    $groups_markup[] = trim(ob_get_clean());
                                }

                                // Print out the generated bar markup now
                                ?>
                                <div class="grouplist <?= $bar_kind.'bar' ?>" data-current="<?= $current_token ?>">
                                    <div class="wrapper">
                                        <a class="arrow prev" data-dir="prev" href="#"></a>
                                        <a class="arrow next" data-dir="next" href="#"></a>
                                        <?= implode(PHP_EOL, $groups_markup) ?>
                                    </div>
                                </div>
                                <?


                            }
                            ?>

                            <div class="starlist">
                                <div class="wrapper">
                                    <?php

                                    // Loop through all the field stars and print them out one-by-one
                                    if (!empty($temp_omega_factors_unlocked)){ //!empty($this_battle_stars)

                                        // Define the minimum grid size (rows/columns)
                                        $grid_size = 8;

                                        // Pad the array with dummy data if it has fewer than 8 elements
                                        $padded_omega_factors = array_pad($temp_omega_factors_unlocked, $grid_size, ['field' => 'dummy-dummy']);

                                        // Loop through all the omega factors firstly to create the side fields
                                        $temp_key = 0;
                                        foreach ($temp_omega_factors_unlocked AS $side_key => $side_field_info){

                                            // Define the tokens for this field
                                            $side_field_token = $side_field_info['field'];
                                            if (!empty($side_field_token)){ list($side_field_token_one, $side_field_token_two) = explode('-', $side_field_token); }
                                            else { $side_field_token_one = ''; $side_field_token_two = ''; }

                                            // Loop through all the omega factors firstly to create the top fields
                                            foreach ($temp_omega_factors_unlocked AS $top_key => $top_field_info){

                                                // Define the tokens for this field
                                                $top_field_token = $top_field_info['field'];
                                                if (!empty($top_field_token)){ list($top_field_token_one, $top_field_token_two) = explode('-', $top_field_token); }
                                                else { $top_field_token_one = ''; $top_field_token_two = ''; }


                                                // Generate the star token based on the two field tokens
                                                $star_token = $side_field_token_one.'-'.$top_field_token_two;
                                                //echo '$side_field_token_one = '.$side_field_token_one.' / $top_field_token_two = '.$top_field_token_two."\n";
                                                $star_data = !empty($this_battle_stars[$star_token]) ? $this_battle_stars[$star_token] : false;

                                                // If the star data exists, print out the star info
                                                if (!empty($star_data)){

                                                    // Collect the star image info from the index based on type
                                                    $temp_star_date = !empty($star_data['star_date']) ? $star_data['star_date']: 0;
                                                    $temp_field_type_1 = !empty($star_data['star_type']) ? $star_data['star_type'] : 'none';
                                                    $temp_field_type_2 = !empty($star_data['star_type2']) ? $star_data['star_type2'] : $temp_field_type_1;

                                                    // Collect the star kind and determine its name
                                                    $temp_star_kind = $star_data['star_kind'];
                                                    $temp_star_kind_name = ucfirst($temp_star_kind);
                                                    if ($temp_star_kind == 'fusion' && $temp_field_type_1 == $temp_field_type_2){
                                                        //$temp_star_kind_name = 'Perfect '.$temp_star_kind_name;
                                                    }

                                                    if ($temp_star_kind == 'field'){
                                                        $type = $temp_field_type_1;
                                                        $temp_star_back = array('class' => 'back', 'path' => 'images/items/field-star_'.$type.'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => '00', 'size' => 40);
                                                        //$temp_star_front = array('class' => 'front', 'path' => 'images/items/field-star_'.$type.'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => '00', 'size' => 40);
                                                    } elseif ($temp_star_kind == 'fusion'){
                                                        $type = $temp_field_type_1;
                                                        if ($temp_field_type_1 != $temp_field_type_2){ $type .= '-'.$temp_field_type_2; }
                                                        $temp_star_back = array('class' => 'back', 'path' => 'images/items/fusion-star_'.$type.'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => '00', 'size' => 40);
                                                        //$temp_star_front = array('class' => 'front', 'path' => 'images/items/fusion-star_'.$type.'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => '00', 'size' => 40);
                                                    }
                                                    $temp_star_title = $star_data['star_name'].' Star <br />';
                                                    $temp_star_title .= '<span style="font-size:80%;">';

                                                        if ($temp_field_type_1 != $temp_field_type_2){
                                                            $temp_star_title .= '+1 '.ucfirst($temp_field_type_1).(!empty($temp_field_type_2) ? ' / +1 '.ucfirst($temp_field_type_2) : '').'';
                                                        } else {
                                                            if ($temp_star_kind == 'field'){ $temp_star_title .= '+1 '.ucfirst($temp_field_type_1).''; }
                                                            elseif ($temp_star_kind == 'fusion'){ $temp_star_title .= '+2 '.ucfirst($temp_field_type_1).''; }

                                                        }


                                                        $temp_star_title .= ' | '.$temp_star_kind_name.' Star';

                                                        if (!empty($temp_star_date)){
                                                            $temp_star_title .= ' <br />Found '.date('Y/m/d', $temp_star_date);
                                                        }

                                                    $temp_star_title .= '</span>';
                                                    $temp_star_title = htmlentities($temp_star_title, ENT_QUOTES, 'UTF-8');

                                                    // Print out the markup for the field or fusion star
                                                    $is_visible = in_array($side_key, $chart_keys_visible['side']) && in_array($top_key, $chart_keys_visible['top']) ? true : false;
                                                    echo '<a data-side-key="'.$side_key.'" data-top-key="'.$top_key.'" data-click-tooltip="'.$temp_star_title.'" data-tooltip-type="field_type field_type_'.$temp_field_type_1.(!empty($temp_field_type_2) && ($temp_field_type_1 != $temp_field_type_2) ? '_'.$temp_field_type_2 : '').'" class="sprite sprite_40x40 sprite_star '.($is_visible ? 'visible' : '').'" style="">';
                                                        echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_'.$temp_star_back['frame'].'" style="background-image: url('.$temp_star_back['path'].'); z-index: 10;">&nbsp;</div>';
                                                        echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_'.$temp_star_back['frame'].'" style="background-image: url('.$temp_star_back['path'].'); z-index: 8;">&nbsp;</div>';
                                                    echo '</a>'."\n";

                                                }
                                                // Otherwise, print out an empty star placeholder
                                                else {

                                                    // Print out the markup for the field or fusion star
                                                    $is_visible = in_array($side_key, $chart_keys_visible['side']) && in_array($top_key, $chart_keys_visible['top']) ? true : false;
                                                    echo '<a data-side-key="'.$side_key.'" data-top-key="'.$top_key.'" data-tooltip-type="field_type field_type_empty" class="sprite sprite_40x40 sprite_star empty_star '.($is_visible ? 'visible' : '').'" style="">';
                                                        echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_00" style="">&nbsp;</div>';
                                                        echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_00" style="">&nbsp;</div>';
                                                    echo '</a>'."\n";

                                                }

                                                // Increment the key either way
                                                $temp_key++;

                                            }


                                        }

                                    }
                                    ?>

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="container starforce">
                        <div class="wrapper">
                            <a class="size_toggle">
                                <span class="maximize"><i class="fa fas fa-window-maximize"></i></span>
                                <span class="restore"><i class="fa fas fa-window-restore"></i></span>
                            </a>

                            <?

                            // Create an image helper for colour stuff
                            $cms_image = new cms_image();

                            // Collect the data for this type chart
                            $star_type_labels = array();
                            $star_force_labels = array();

                            $star_type_counts = array();
                            $star_type_backgrounds = array();
                            $star_type_borders = array();

                            $force_type_counts = array();
                            $force_type_counts_total = 0;
                            $force_type_backgrounds = array();
                            $force_type_borders = array();

                            $sorted_type_tokens = $this_star_force_strict;
                            asort($sorted_type_tokens, SORT_NUMERIC);
                            $sorted_type_tokens = array_keys($sorted_type_tokens);
                            //$sorted_type_tokens = array_reverse($sorted_type_tokens);

                            $ordered_type_tokens = array_filter($rpg_types_index_tokens,
                                function($type_token) use ($this_star_force_strict){
                                    return !empty($this_star_force_strict[$type_token]);
                                });

                            $star_kind_tokens = array('boss', 'field', 'fusion', 'perfect-fusion');
                            $star_type_counts['all'] = array();
                            $star_type_backgrounds['all'] = array();
                            $star_type_borders['all'] = array();
                            foreach ($star_kind_tokens AS $kind){
                                $star_type_counts[$kind] = array();
                                $star_type_backgrounds[$kind] = array();
                                $star_type_borders[$kind] = array();
                            }
                            //error_log('$star_type_counts = '.print_r($star_type_counts, true));

                            // First we grab the data for stars-collected overall in type-index order
                            foreach($ordered_type_tokens AS $type_key => $type_token){

                                $type_info = $mmrpg_database_types[$type_token];

                                $light_colour = implode(', ', $type_info['type_colour_light']);
                                $dark_colour = implode(', ', $type_info['type_colour_dark']);

                                $star_type_labels[] = $type_info['type_name'];

                                $star_total_kind_count = 0;
                                $star_total_background = 'rgba('.$light_colour.', 1.0)';
                                $star_total_border = 'rgba('.$dark_colour.', 1.0)';
                                foreach ($star_kind_tokens AS $kind_token){

                                    /*
                                    if ($kind_token == 'boss'){ $alpha = '1.0'; }
                                    elseif ($kind_token == 'field'){ $alpha = '0.9'; }
                                    elseif ($kind_token == 'perfect-fusion'){ $alpha = '0.8'; }
                                    elseif ($kind_token == 'fusion'){ $alpha = '0.7'; }

                                    $star_background = 'rgba('.$light_colour.', '.$alpha.')';
                                    $star_border = 'rgba('.$dark_colour.', 1.0)';
                                    */

                                    if ($kind_token == 'boss'){ $darken = 0; }
                                    elseif ($kind_token == 'field'){ $darken = 10; }
                                    elseif ($kind_token == 'perfect-fusion'){ $darken = 20; }
                                    elseif ($kind_token == 'fusion'){ $darken = 40; }
                                    //error_log('$light_colour = '.print_r($light_colour, true));
                                    $adjusted_bar_colour = $cms_image->colour_darken(explode(',', $light_colour), $darken);
                                    //error_log('$adjusted_bar_colour = '.print_r($adjusted_bar_colour, true));
                                    $adjusted_bar_colour = implode(',', array($adjusted_bar_colour[0], $adjusted_bar_colour[1], $adjusted_bar_colour[2]));
                                    //error_log('$adjusted_bar_colour = '.print_r($adjusted_bar_colour, true));

                                    $star_background = 'rgba('.$adjusted_bar_colour.', 1.0)';
                                    $star_border = 'rgba('.$dark_colour.', 1.0)';

                                    $star_kind_count = !empty($this_star_kind_counts[$kind_token][$type_token]) ? $this_star_kind_counts[$kind_token][$type_token] : 0;
                                    $star_total_kind_count += $star_kind_count;

                                    $star_type_counts[$kind_token][] = !empty($star_kind_count) ? $star_kind_count : null;
                                    $star_type_backgrounds[$kind_token][] = $star_background;
                                    $star_type_borders[$kind_token][] = $star_border;

                                }

                                $star_type_counts['all'][] = !empty($star_total_kind_count) ? $star_total_kind_count : null;
                                $star_type_backgrounds['all'][] = $star_total_background;
                                $star_type_borders['all'][] = $star_total_border;

                            }

                            // Then we grab the data for the starforce-levels using least-to-most order
                            foreach($ordered_type_tokens AS $type_key => $type_token){

                                $type_info = $mmrpg_database_types[$type_token];

                                $star_force = $this_star_force[$type_token];
                                $star_force *= 10;

                                $light_colour = implode(', ', $type_info['type_colour_light']);
                                $dark_colour = implode(', ', $type_info['type_colour_dark']);

                                $force_background = 'rgba('.$light_colour.', 0.9)';
                                $force_border = 'rgba('.$dark_colour.', 1.0)';

                                $star_force_labels[] = $type_info['type_name'];

                                $force_type_counts[] = $star_force;
                                $force_type_counts_total += $star_force;
                                $force_type_backgrounds[] = $force_background;
                                $force_type_borders[] = $force_border;

                            }

                            ?>

                            <script type="text/javascript">
                                var baseChartOptions = {
                                    title: {
                                        display: true,
                                        text: '<title>'
                                        },
                                    legend: {
                                        display: false,
                                        },
                                    skipNull: true,
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    borderSkipped: 'bottom',
                                    };

                            </script>

                            <div class="chart_wrapper star_totals">
                                <canvas class="chart_canvas" data-source="starData" width="350" height="180"></canvas>
                                <script type="text/javascript">

                                    var thisChartOptions = jQuery.extend(true, {}, baseChartOptions);
                                    //thisChartOptions.title.text = '<?= $this_battle_stars_count.' '.($this_battle_stars_count === 1 ? 'Star' : 'Stars') ?>';
                                    thisChartOptions.title.text = 'Stars Collected';
                                    thisChartOptions.tooltips = {
                                        callbacks: {
                                            title: function (tooltipItem, data) {
                                                //console.log('-------------------------');
                                                //console.log('TITLE');
                                                //console.log('tooltipItem[0]', tooltipItem[0]);
                                                //console.log('data.datasets', data.datasets);
                                                //console.log('data.datasets[0].label', data.datasets[0].label);
                                                var returnText = data.labels[tooltipItem[0].index];
                                                //if (tooltipItem[0].datasetIndex == 0){ returnText += ' +1'; }
                                                //else if (tooltipItem[0].datasetIndex == 1){ returnText += ' +1'; }
                                                //else if (tooltipItem[0].datasetIndex == 2){ returnText += ' +2'; }
                                                //console.log('returnText', returnText);
                                                return returnText;
                                                },
                                            label: function(tooltipItems, data) {
                                                //console.log('LABEL');
                                                //console.log('tooltipItems', tooltipItems);
                                                //console.log('data.datasets', data.datasets);
                                                var returnText =  'x' + data.datasets[tooltipItems.datasetIndex].data[tooltipItems.index] + ' ' + data.datasets[tooltipItems.datasetIndex].label;
                                                //console.log('returnText', returnText);
                                                return returnText;
                                                }
                                            }
                                        };
                                    thisStarSettings.starData = {
                                        type: 'pie',
                                        data: {
                                            labels: <?= json_encode($star_type_labels) ?>,
                                            datasets: [/*{
                                                label: 'Stars',
                                                data: <?= json_encode($star_type_counts['all']) ?>,
                                                backgroundColor: <?= json_encode($star_type_backgrounds['all']) ?>,
                                                borderColor: <?= json_encode($star_type_borders['all']) ?>,
                                                borderWidth: 1
                                                },*/{
                                                label: 'Boss Stars',
                                                data: <?= json_encode($star_type_counts['boss']) ?>,
                                                backgroundColor: <?= json_encode($star_type_backgrounds['boss']) ?>,
                                                borderColor: <?= json_encode($star_type_borders['boss']) ?>,
                                                borderWidth: 0,
                                                },{
                                                label: 'Field Stars',
                                                data: <?= json_encode($star_type_counts['field']) ?>,
                                                backgroundColor: <?= json_encode($star_type_backgrounds['field']) ?>,
                                                borderColor: <?= json_encode($star_type_borders['field']) ?>,
                                                borderWidth: 0,
                                                },{
                                                label: 'Fusion Stars (Perfect)',
                                                data: <?= json_encode($star_type_counts['perfect-fusion']) ?>,
                                                backgroundColor: <?= json_encode($star_type_backgrounds['perfect-fusion']) ?>,
                                                borderColor: <?= json_encode($star_type_borders['perfect-fusion']) ?>,
                                                borderWidth: 0,
                                                },{
                                                label: 'Fusion Stars (Mixed)',
                                                data: <?= json_encode($star_type_counts['fusion']) ?>,
                                                backgroundColor: <?= json_encode($star_type_backgrounds['fusion']) ?>,
                                                borderColor: <?= json_encode($star_type_borders['fusion']) ?>,
                                                borderWidth: 0,
                                                }]
                                            },
                                        options: thisChartOptions,
                                        };

                                </script>
                            </div>

                            <div class="chart_wrapper star_forces">
                                <canvas class="chart_canvas" data-source="forceData" width="350" height="180"></canvas>
                                <script type="text/javascript">

                                    var thisChartOptions = jQuery.extend(true, {}, baseChartOptions);
                                    thisChartOptions.title.text = 'Starforce Boosts';
                                    thisChartOptions.tooltips = {
                                        callbacks: {
                                            title: function (tooltipItem, data) {
                                                var returnText = data.labels[tooltipItem[0].index];
                                                return returnText;
                                                },
                                            label: function(tooltipItems, data) {
                                                var returnText =  '+' + tooltipItems.yLabel + ' ' + data.datasets[tooltipItems.datasetIndex].label;
                                                return returnText;
                                                }
                                            }
                                        };
                                    thisChartOptions.scale = {
                                            ticks: { beginAtZero:true, precision: 0 }
                                          };
                                    thisChartOptions.scales = {
                                            xAxes: [{
                                                stacked:true,
                                                ticks: { beginAtZero:true, precision: 0 }
                                                }],
                                            yAxes: [{
                                                stacked:true,
                                                ticks: { beginAtZero:true, precision: 0 }
                                                }]
                                            };
                                    thisStarSettings.forceData = {
                                        type: 'bar',
                                        data: {
                                            labels: <?= json_encode($star_force_labels) ?>,
                                            datasets: [{
                                                label: 'All Stats',
                                                data: <?= json_encode($force_type_counts) ?>,
                                                backgroundColor: <?= json_encode($force_type_backgrounds) ?>,
                                                borderColor: <?= json_encode($force_type_borders) ?>,
                                                borderWidth: 1
                                                }]
                                            },
                                        options: thisChartOptions
                                        };

                                </script>
                            </div>

                            <?

                            //echo('<pre>$this_star_force = '.print_r($this_star_force, true).'</pre>');
                            //echo('<pre>$this_star_force_strict = '.print_r($this_star_force_strict, true).'</pre>');

                            ?>

                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php'); }
?>
</body>
</html>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_bottom.php');
// Unset the database variable
unset($db);
?>