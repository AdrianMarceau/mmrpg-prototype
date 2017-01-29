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

// Require the prototype data file
//require_once('../prototype/include.php');

// Require the prototype omega data file
require_once('../prototype/omega.php');
$unlocked_factor_one_robots = false;
$unlocked_factor_two_robots = false;
$unlocked_factor_three_robots = false;
$unlocked_factor_four_robots = false;
$temp_omega_factor_options = array();
$temp_omega_factor_options_unlocked = array();
if (mmrpg_prototype_player_unlocked('dr-light')){
    $temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_one);
    $unlocked_factor_one_robots = true;
}
if (mmrpg_prototype_player_unlocked('dr-wily')){
    $temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_two);
    $unlocked_factor_two_robots = true;
}
if (mmrpg_prototype_player_unlocked('dr-cossack')){
    $temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_three);
    $unlocked_factor_three_robots = true;
}

// Collect any fields unlocked via other means
$temp_unlocked_fields = !empty($_SESSION[$session_token]['values']['battle_fields']) ? $_SESSION[$session_token]['values']['battle_fields'] : array();

// Loop through unlockable system fields with no type
foreach ($this_omega_factors_system AS $key => $factor){
    if (in_array($factor['field'], $temp_unlocked_fields)){
        $temp_omega_factor_options[] = $factor;
    }
}
// Loop through the unlockable MM3 fields (from omega factor four)
foreach ($this_omega_factors_four AS $key => $factor){
    if (in_array($factor['field'], $temp_unlocked_fields)){
        $temp_omega_factor_options[] = $factor;
        $unlocked_factor_four_robots = true;
    }
}

// Loop through the collected options and pull just the robot tokens
foreach ($temp_omega_factor_options AS $key => $factor){
    $temp_omega_factor_options_unlocked[] = $factor['field'];
}

// Require the starforce data file
require_once(MMRPG_CONFIG_ROOTDIR.'includes/starforce.php');

// Collect the editor flag if set
$global_allow_editing = isset($_GET['edit']) && $_GET['edit'] == 'false' ? false : true;

// Collect the robot's index for names and fields
$rpg_robots_index = rpg_robot::get_index();

// Collect all the robots that have been unlocked by the player
$rpg_robots_encountered = array();
if (!empty($_SESSION[$session_token]['values']['robot_database'])){
    $rpg_robots_encountered = array_keys($_SESSION[$session_token]['values']['robot_database']);
}

// Collect the omega factors that we should be printing links for
$temp_omega_factors_unlocked = array();
if ($unlocked_factor_one_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_one); }
if ($unlocked_factor_two_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_two); }
if ($unlocked_factor_four_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_four); }
if ($unlocked_factor_three_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_three); }
$temp_omega_factors_unlocked_total = count($temp_omega_factors_unlocked);

// Collect the omega groups that we should be printing links for
$temp_omega_groups_unlocked = array();
if ($unlocked_factor_one_robots){ $temp_omega_groups_unlocked[] = array('token' => 'MM01', 'omega' => $this_omega_factors_one); }
if ($unlocked_factor_two_robots){ $temp_omega_groups_unlocked[] = array('token' => 'MM02', 'omega' => $this_omega_factors_two); }
if ($unlocked_factor_four_robots){ $temp_omega_groups_unlocked[] = array('token' => 'MM04', 'omega' => $this_omega_factors_four); }
if ($unlocked_factor_three_robots){ $temp_omega_groups_unlocked[] = array('token' => 'MM03', 'omega' => $this_omega_factors_three); }

// Define a function for printing out the robot links
function print_starchart_omega($info, $key, $kind){
    global $rpg_robots_encountered, $rpg_robots_index;
    $robot = $info['robot'];
    $type = $info['type'];
    $field = $info['field'];
    if (in_array($robot, $rpg_robots_encountered)){

        $info = $rpg_robots_index[$robot];
        $name = $info['robot_name'];
        $size = $info['robot_image_size'] ? $info['robot_image_size'] : 40;
        list($field_one, $field_two) = explode('-', $field);

        $title = '<div style="text-align: center;">';
            $title .= $name.' <br /> ';
            $title .= '<span style="font-size: 10px;">'.ucfirst($field_one).' '.ucfirst($field_two).'</span>';
        $title .= '</div>';
        $title = htmlentities($title, ENT_QUOTES, 'UTF-8');

        $sprite_class = 'sprite sprite_'.$size.'x'.$size.' robot_type robot_type_empty';
        $sprite_style = 'background-image: url(images/robots/'.$robot.'/mug_left_'.$size.'x'.$size.'.png); ';
        $sprite_markup = '<span class="'.$sprite_class.'" style="'.$sprite_style.'">&nbsp;</span>';

        $icon_class = 'icon robot_type robot_type_'.$type.' ';
        $icon_markup = '<a class="'.$icon_class.'" data-'.$kind.'-key="'.$key.'" title="'.$title.'">%s</a>';

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

// Define a function for counting permutations
function temp_combination_number($k,$n){
    $n = intval($n);
    $k = intval($k);
    if ($k > $n){
            return 0;
    } elseif ($n == $k) {
            return 1;
    } else {
            if ($k >= $n - $k){
                    $l = $k+1;
                    for ($i = $l+1 ; $i <= $n ; $i++)
                            $l *= $i;
                    $m = 1;
                    for ($i = 2 ; $i <= $n-$k ; $i++)
                            $m *= $i;
            } else {
                    $l = ($n-$k) + 1;
                    for ($i = $l+1 ; $i <= $n ; $i++)
                            $l *= $i;
                    $m = 1;
                    for ($i = 2 ; $i <= $k ; $i++)
                            $m *= $i;
            }
    }
    return $l/$m;
}

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
<link type="text/css" href="styles/starforce-responsive.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
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

            <?php
            $temp_total_stars_label = $this_battle_stars_count;
            $temp_potential_count = ((temp_combination_number(2, $temp_omega_factors_unlocked_total) * 2) + $temp_omega_factors_unlocked_total);
            $temp_potential_stars_label = $temp_potential_count == 1 ? '1 Star' : $temp_potential_count.' Stars';

            ?>
            <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                <span class="count">
                    Starforce <span style="opacity: 0.25;">(
                        <span><?= $temp_total_stars_label ?></span> /
                        <span><?= $temp_potential_stars_label ?></span>
                        )</span>
                </span>
            </span>

            <div class="stars">
                <div class="wrapper">

                    <div class="starchart">
                        <div class="wrapper">

                            <div class="corner"></div>

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
                                        <ul class="robots">
                                            <?
                                            // Loop through and print omega robots
                                            foreach ($group_omega AS $key2 => $omega){
                                                $omega_robot = $omega['robot'];
                                                $omega_field = $omega['field'];
                                                $omega_cell = print_starchart_omega($omega, $chart_keys_counter, $bar_kind);
                                                if ($group_current){ $chart_keys_visible[$bar_kind][] = $chart_keys_counter; }
                                                ?>
                                                <li class="robot">
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
                                    if (!empty($this_battle_stars)){

                                        // Loop through all the omega factors firstly to create the side fields
                                        $temp_key = 0;
                                        foreach ($temp_omega_factors_unlocked AS $side_key => $side_field_info){

                                            // Define the tokens for this field
                                            $side_field_token = $side_field_info['field'];
                                            list($side_field_token_one, $side_field_token_two) = explode('-', $side_field_token);

                                            // Loop through all the omega factors firstly to create the side fields
                                            foreach ($temp_omega_factors_unlocked AS $top_key => $top_field_info){

                                                // Define the tokens for this field
                                                $top_field_token = $top_field_info['field'];
                                                list($top_field_token_one, $top_field_token_two) = explode('-', $top_field_token);

                                                // Generate the star token based on the two field tokens
                                                $star_token = $side_field_token_one.'-'.$top_field_token_two;
                                                //echo '$side_field_token_one = '.$side_field_token_one.' / $top_field_token_two = '.$top_field_token_two."\n";
                                                $star_data = !empty($this_battle_stars[$star_token]) ? $this_battle_stars[$star_token] : false;

                                                // If the star data exists, print out the star info
                                                if (!empty($star_data)){

                                                    // Collect the star image info from the index based on type
                                                    $temp_star_kind = $star_data['star_kind'];
                                                    $temp_star_date = !empty($star_data['star_date']) ? $star_data['star_date']: 0;
                                                    $temp_field_type_1 = !empty($star_data['star_type']) ? $star_data['star_type'] : 'none';
                                                    $temp_field_type_2 = !empty($star_data['star_type2']) ? $star_data['star_type2'] : $temp_field_type_1;
                                                    /*
                                                    if ($temp_star_kind == 'field'){
                                                        $temp_star_front = array('path' => 'images/items/field-star_'.$temp_field_type_1.'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => '02', 'size' => 40);
                                                        $temp_star_back = array('path' => 'images/items/field-star_'.$temp_field_type_2.'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => '01', 'size' => 40);
                                                    } elseif ($temp_star_kind == 'fusion'){
                                                        $temp_star_front = array('path' => 'images/items/fusion-star_'.$temp_field_type_1.'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => '02', 'size' => 40);
                                                        $temp_star_back = array('path' => 'images/items/fusion-star_'.$temp_field_type_2.'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => '01', 'size' => 40);
                                                    }
                                                    */
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
                                                    if ($temp_field_type_1 != $temp_field_type_2){ $temp_star_title .= ''.ucfirst($temp_field_type_1).(!empty($temp_field_type_2) ? ' / '.ucfirst($temp_field_type_2) : '').' Type'; }
                                                    else { $temp_star_title .= ''.ucfirst($temp_field_type_1).' Type'; }
                                                    $temp_star_title .= ' | '.ucfirst($temp_star_kind).' Star';
                                                    /*
                                                    if ($temp_field_type_1 != 'none'){
                                                        if ($temp_star_kind == 'field'){
                                                            $temp_star_title .= ' <br />'.ucfirst($temp_field_type_1).' +'.(MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT);
                                                        } elseif ($temp_star_kind == 'fusion'){
                                                            if ($temp_field_type_1 != $temp_field_type_2){
                                                                $temp_star_title .= ' <br />'.ucfirst($temp_field_type_1).' +'.(MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT);
                                                                $temp_star_title .= ' | '.ucfirst($temp_field_type_2).' +'.(MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT);
                                                            } else {
                                                                $temp_star_title .= ' <br />'.ucfirst($temp_field_type_1).' +'.(MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT * 2);
                                                            }
                                                        }
                                                    }
                                                    */
                                                    if (!empty($temp_star_date)){
                                                        $temp_star_title .= ' <br />Found '.date('Y/m/d', $temp_star_date);
                                                    }
                                                    $temp_star_title .= '</span>';
                                                    $temp_star_title = htmlentities($temp_star_title, ENT_QUOTES, 'UTF-8');

                                                    // Print out the markup for the field or fusion star
                                                    $is_visible = in_array($side_key, $chart_keys_visible['side']) && in_array($top_key, $chart_keys_visible['top']) ? true : false;
                                                    echo '<a href="#" data-side-key="'.$side_key.'" data-top-key="'.$top_key.'" data-tooltip="'.$temp_star_title.'" data-tooltip-type="field_type field_type_'.$temp_field_type_1.(!empty($temp_field_type_2) && ($temp_field_type_1 != $temp_field_type_2) ? '_'.$temp_field_type_2 : '').'" class="sprite sprite_40x40 sprite_star '.($is_visible ? 'visible' : '').'" style="">';
                                                        echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_'.$temp_star_back['frame'].'" style="background-image: url('.$temp_star_back['path'].'); z-index: 10;">&nbsp;</div>';
                                                        //echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_'.$temp_star_front['frame'].'" style="background-image: url('.$temp_star_front['path'].'); z-index: 20;">&nbsp;</div>';
                                                    echo '</a>';


                                                    //echo('<pre>$star_data = $this_battle_stars['.$star_token.'] = '.print_r($star_data, true).'</pre>');
                                                    //echo('<pre>$this_star_force = '.print_r($this_star_force, true).'</pre>');
                                                    //echo('<pre>$this_battle_stars = '.print_r($this_battle_stars, true).'</pre>');
                                                    //echo('<pre>$chart_keys_visible = '.print_r($chart_keys_visible, true).'</pre>');
                                                    //exit();

                                                }
                                                // Otherwise, print out an empty star placeholder
                                                else {

                                                    // Print out the markup for the field or fusion star
                                                    $is_visible = in_array($side_key, $chart_keys_visible['side']) && in_array($top_key, $chart_keys_visible['top']) ? true : false;
                                                    echo '<a href="#" data-side-key="'.$side_key.'" data-top-key="'.$top_key.'" data-tooltip-type="field_type field_type_empty" class="sprite sprite_40x40 sprite_star empty_star '.($is_visible ? 'visible' : '').'" style="">';
                                                        echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_00" style="">&nbsp;</div>';
                                                        echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_00" style="">&nbsp;</div>';
                                                    echo '</a>';

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

                    <div class="starforce">
                        <div class="wrapper">

                            star force list

                            <?

                            echo('<pre>$this_star_force = '.print_r($this_star_force, true).'</pre>');
                            echo('<pre>$this_star_force_strict = '.print_r($this_star_force_strict, true).'</pre>');

                            ?>

                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
<script type="text/javascript">
$(document).ready(function(){

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
unset($db);
?>