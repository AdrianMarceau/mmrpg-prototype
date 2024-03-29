<?php
// Include the TOP file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
define('MMRPG_REMOTE_SKIP_DATABASE', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
$session_token = mmrpg_game_token();

// Include the DATABASE file
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require(MMRPG_CONFIG_ROOTDIR.'database/players.php');
require(MMRPG_CONFIG_ROOTDIR.'database/robots.php');

// Collect the abilities array from the database so we can control its contents
$deprecated_abilities = rpg_ability::get_global_deprecated_abilities();
$mmrpg_database_abilities = rpg_ability::get_index(true, false, 'master');
$mmrpg_database_abilities = array_filter($mmrpg_database_abilities, function($ability_info) use($deprecated_abilities){
    if (in_array($ability_info['ability_token'], $deprecated_abilities)){ return false; }
    return true;
    });
$mmrpg_database_abilities_count = count($mmrpg_database_abilities);
foreach ($mmrpg_database_abilities AS $ability_token => $ability_info){
    if (!empty($ability_info['ability_flag_hidden'])
        && !mmrpg_prototype_ability_unlocked('', '', $ability_token)){
        $mmrpg_database_abilities_count--;
        unset($mmrpg_database_abilities[$ability_token]);
    }
}

// Collect the editor flag if set
$global_allow_editing = !defined('MMRPG_REMOTE_GAME') ? true : false;
if (isset($_GET['edit']) && $_GET['edit'] == 'false'){ $global_allow_editing = false; }
$global_frame_source = !empty($_GET['source']) ? trim($_GET['source']) : 'prototype';


// -- GENERATE EDITOR MARKUP

// Define which abilities we're allowed to see
$shopkeeper_unlocked = mmrpg_prototype_item_unlocked('reggae-link') ? true : false;
$global_battle_abilities = !empty($_SESSION[$session_token]['values']['battle_abilities']) ? $_SESSION[$session_token]['values']['battle_abilities'] : array();
$global_battle_ability_categories = array();
if ($shopkeeper_unlocked){
    $global_battle_ability_categories['all'] = array(
        'category_name' => 'All Abilities',
        'category_quote' => 'Many different abilities! Squawk! How many have you unlocked so far? Squaaaawk!',
        'category_image' => 'robots/reggae',
        'category_image_size' => 40
        );
} else {
    $global_battle_ability_categories['all'] = array(
        'category_name' => 'All Abilities',
        'category_quote' => 'Those are some nice abilities ya got there! A little bird told me there\'s a way to get more though...',
        'category_image' => 'robots/sniper-joe',
        'category_image_size' => 40
        );
}


// Filter out abilities that are not complete or otherwise attainable yet
$mmrpg_database_abilities = array_filter($mmrpg_database_abilities, function($ability_info){
    if (empty($ability_info['ability_flag_published'])){ return false; }
    elseif (empty($ability_info['ability_flag_complete'])){ return false; }
    return true;
    });
$global_battle_abilities = array_filter($global_battle_abilities, function($ability_token) use ($mmrpg_database_abilities){
    if (!isset($mmrpg_database_abilities[$ability_token])){ return false; }
    return true;
    });
$global_battle_abilities = array_values(array_unique($global_battle_abilities));

/*
error_log("\n---------------------------");

error_log('$global_battle_abilities(count) = '.print_r(count($global_battle_abilities), true));
error_log('$mmrpg_database_abilities(count) = '.print_r(count($mmrpg_database_abilities), true));

error_log('$global_battle_abilities vs $mmrpg_database_abilities = '.print_r(array_diff(
    array_values($global_battle_abilities),
    array_keys($mmrpg_database_abilities)
    ), true));
error_log('$mmrpg_database_abilities vs $global_battle_abilities = '.print_r(array_diff(
    array_keys($mmrpg_database_abilities),
    array_values($global_battle_abilities)
    ), true));

error_log('$global_battle_abilities = '.print_r($global_battle_abilities, true));
error_log('$mmrpg_database_abilities = '.print_r($mmrpg_database_abilities, true));
*/

// CONSOLE MARKUP

// Generate the console markup for this page
if (true){

    // Start the output buffer
    ob_start();

        // Update the player key to the current counter
        $ability_key = $key_counter;
        $ability_info['ability_image'] = $ability_info['ability_token'];
        $ability_info['ability_image_size'] = 40;

        // Collect a temp robot object for printing abilities
        $player_info = $mmrpg_database_players['dr-light'];
        $robot_info = $mmrpg_database_robots['mega-man'];

        // Collect the category details for printing this tab
        $category_info = $global_battle_ability_categories['all'];
        $category_image_size = $category_info['category_image_size'];
        $category_image_sizex = $category_image_size.'x'.$category_image_size;
        $category_image_class = 'sprite sprite_player sprite_player_sprite sprite_'.$category_image_sizex.' sprite_'.$category_image_sizex.'_00';
        $category_image_path = 'images/'.$category_info['category_image'].'/sprite_right_'.$category_image_sizex.'.png?'.MMRPG_CONFIG_CACHE_DATE;

        // Collect and print the editor markup for this ability tab
        ?>
        <div class="event event_double event_visible">

            <div class="this_sprite sprite_left" style="top: 4px; left: 4px; width: 36px; height: 36px; background-image: url(images/fields/prototype-complete/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center center; border: 1px solid #1A1A1A;">
                <div class="<?= $category_image_class ?>" style="background-image: url(<?= $category_image_path ?>); "></div>
            </div>
            <div class="header header_left ability_type ability_type_none" style="margin-right: 0;">
                Ability Arsenal
            </div>

            <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">

                <div class="ability_tabs_links" style="margin: 0 auto; color: #FFFFFF; ">
                    <span class="tab_spacer"><span class="inset">&nbsp;</span></span>
                    <?php
                    // Define a counter for the number of tabs
                    $tab_counter = 0;
                    // Loop through the ability categories and display them
                    foreach ($global_battle_ability_categories AS $category_token => $category_info){
                        $category_name = $category_info['category_name'];
                        $category_quote = $category_info['category_quote'];
                        ?>
                        <span class="tab_spacer"><span class="inset">&nbsp;</span></span>
                        <a class="tab_link tab_link_<?= $category_token ?>" href="#" data-tab="<?= $category_token ?>"><span class="inset"><?= $category_name ?></span></a>
                        <?
                        $tab_counter++;
                    }
                    // Define the tab width total
                    $tab_width = $tab_counter * (1 + 20);
                    $line_width = 96 - $tab_width;
                    ?>
                    <span class="tab_line" style="width: 76%;"><span class="inset">&nbsp;</span></span>
                </div>

                <div class="ability_tabs_containers" style="margin: 0 auto 10px;">
                    <?

                    // Collect the array of unseen menu frame abilities if there is one, then clear it
                    $frame_token = 'abilities';
                    $menu_frame_content_unseen = rpg_prototype::get_menu_frame_content_unseen($frame_token);
                    rpg_prototype::clear_menu_frame_content_unseen($frame_token);

                    // Loop through the ability categories and display tab containers for them
                    foreach ($global_battle_ability_categories AS $category_token => $category_info){
                        $category_name = $category_info['category_name'];
                        $category_quote = $category_info['category_quote'];

                        ?>
                        <div class="tab_container tab_container_<?= $category_token ?>" data-tab="<?= $category_token ?>">

                            <div class="ability_quote ability_quote_<?= $category_token ?>">&quot;<?= $category_quote ?>&quot;</div>

                            <div class="scroll_wrapper">
                                <table class="full" style="margin-bottom: 5px;">
                                    <colgroup>
                                        <col width="50%" />
                                        <col width="50%" />
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                        <?
                                        // Loop through the abilities and print them one by one
                                        $ability_counter = 0;
                                        $ability_counter_total = count($mmrpg_database_abilities);
                                        foreach ($mmrpg_database_abilities AS $ability_token => $ability_info){
                                            $ability_counter++;

                                            // Define basic ability slot details
                                            $ability_info_token = $ability_info['ability_token'];
                                            $ability_info_name = $ability_info['ability_name'];
                                            $ability_primary_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
                                            $ability_info_type = $ability_primary_type;
                                            if ($ability_info_type != 'none' && !empty($ability_info['ability_type2'])){ $ability_info_type .= '_'.$ability_info['ability_type2']; }
                                            elseif ($ability_info_type == 'none' && !empty($ability_info['ability_type2'])){ $ability_info_type = $ability_info['ability_type2']; }
                                            $ability_sprite_image = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];
                                            $ability_cell_float = $ability_counter % 2 == 0 ? 'right' : 'left';
                                            $temp_is_disabled = false;
                                            $temp_is_comingsoon = false;
                                            $temp_is_complete = !empty($ability_info['ability_flag_complete']) ? $ability_info['ability_flag_complete'] : false;
                                            $temp_is_new = in_array($ability_info_token, $menu_frame_content_unseen) ? true : false;

                                            // Define the editor title markup print options
                                            $ability_print_options = array('show_accuracy' => false);

                                            // Only collect detailed info if this ability has been unlocked
                                            if (mmrpg_prototype_ability_unlocked('', '', $ability_token)){

                                                // Collect the abilities details and current quantity
                                                $temp_info_tooltip = rpg_ability::print_editor_title_markup($robot_info, $ability_info, $ability_print_options);
                                                $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);

                                            }
                                            // Otherwise this ability slot is mysterious
                                            else {

                                                // Define placeholder, mysterious details for the user
                                                $ability_info_name = preg_replace('/[-_a-z0-9]/i', '?', $ability_info['ability_name']);
                                                $ability_info_type = 'empty';
                                                $temp_info_tooltip = '';
                                                $temp_is_disabled = true;
                                                $temp_is_comingsoon = true;

                                            }

                                            ?>
                                            <td class="<?= $ability_cell_float ?> ability_cell<?= $temp_is_disabled ? ' ability_cell_disabled' : '' ?><?= !$temp_is_complete ? ' ability_cell_incomplete' : '' ?>" data-kind="ability" data-action="use-ability" data-token="<?= !$temp_is_comingsoon ? $ability_info_token : 'comingsoon' ?>" data-unlocked="<?= $temp_is_comingsoon ? 'coming-soon' : 'true' ?>">
                                                <span class="ability_number ability_type ability_type_empty"><span>No. <?= str_replace(' ', '&nbsp;', str_pad($ability_counter, 2, ' ', STR_PAD_LEFT)); ?></span><?= ($temp_is_new ? '<i class="new type electric"></i>' : '') ?></span>
                                                <span class="ability_name ability_type ability_type_<?= $ability_info_type ?>" <?= !empty($temp_info_tooltip) ? 'data-click-tooltip="'.$temp_info_tooltip.'"' : '' ?>><?= $ability_info_name ?></span>
                                                <? if (!$temp_is_comingsoon): ?>
                                                    <span class="ability_sprite ability_type ability_type_empty"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/abilities/<?= $ability_sprite_image ?>/icon_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE?>);"></span></span>
                                                <? else: ?>
                                                    <span class="ability_sprite ability_type ability_type_empty"><span class="sprite sprite_40x40 sprite_40x40_00"></span></span>
                                                <? endif; ?>
                                            </td>
                                            <?

                                            if ($ability_cell_float == 'right' && $ability_counter < $ability_counter_total){ echo '</tr><tr>'; }
                                        }
                                        if ($ability_counter % 2 != 0){

                                            ?>
                                            <td class="right ability_cell ability_cell_disabled">
                                                &nbsp;
                                            </td>
                                            <?

                                        }
                                        ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <?

                    }

                    ?>
                </div>

            </div>
        </div>
        <?

    // Collect the contents of the buffer
    $ability_console_markup = ob_get_clean();
    $ability_console_markup = preg_replace('/\s+/', ' ', trim($ability_console_markup));

}

// Generate the edit markup using the battles settings and rewards
$this_ability_markup = '';
if (true){
    // Prepare the output buffer
    ob_start();

    // Determine the token for the very first player in the edit
    $temp_ability_tokens = array_keys($mmrpg_database_abilities);
    $first_ability_token = array_shift($temp_ability_tokens);
    $first_ability_token = isset($first_ability_token['ability_token']) ? $first_ability_token['ability_token'] : $first_ability_token;
    unset($temp_ability_tokens);

    // Start generating the edit markup
    ?>

    <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <span class="count">
            <i class="fa fas fa-fire-alt"></i>
            Ability Arsenal
            <span class="progress">(<span id="ability_counter">
                <?= number_format(count($global_battle_abilities), 0, '.', ',') ?> /
                <?= number_format(count($mmrpg_database_abilities), 0, '.', ',') ?>
                </span> Abilities)</span>
        </span>
    </span>

    <div style="float: left; width: 100%;">
    <table class="formatter" style="width: 100%; table-layout: fixed;">
        <colgroup>
            <col width="" />
        </colgroup>
        <tbody>
            <tr>
                <td class="console" style="vertical-align: top;">

                    <div id="console" class="noresize" style="height: auto;">
                        <div id="abilities" class="wrapper"></div>
                    </div>

                </td>
            </tr>
        </tbody>
    </table>
    </div>

    <?php

    // Collect the output buffer content
    $this_ability_markup = preg_replace('#\s+#', ' ', trim(ob_get_clean()));
}

// DEBUG DEBUG DEBUG
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Abilities | Prototype | Mega Man RPG Prototype</title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="abilities" content="noindex,nofollow" />
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />
<link type="text/css" href=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/abilities.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="iframe" data-frame="abilities" data-mode="<?= $global_allow_editing ? 'editor' : 'viewer' ?>" data-source="<?= $global_frame_source ?>">
    <div id="prototype" class="hidden" style="opacity: 0;">
        <div id="ability" class="menu">
            <div id="ability_overlay">&nbsp;</div>
            <?= $this_ability_markup ?>
        </div>
    </div>
    <script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
    <script type="text/javascript" src=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script>
    <script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/abilities.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript">
    // Update game settings for this page
    <? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>
    gameSettings.autoScrollTop = false;
    gameSettings.allowShopping = true;
    // Define the global arrays to hold the ability console markup
    var abilityConsoleMarkup = '<?= str_replace("'", "\'", $ability_console_markup) ?>';
    </script>
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