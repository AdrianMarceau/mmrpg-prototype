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
//require(MMRPG_CONFIG_ROOTDIR.'database/items.php');
//require(MMRPG_CONFIG_ROOTDIR.'includes/starforce.php');
$mmrpg_database_items = rpg_item::get_index(true);

// Collect the editor flag if set
$global_allow_editing = !defined('MMRPG_REMOTE_GAME') ? true : false;
if (isset($_GET['edit']) && $_GET['edit'] == 'false'){ $global_allow_editing = false; }
$global_frame_source = !empty($_GET['source']) ? trim($_GET['source']) : 'prototype';


// -- GENERATE EDITOR MARKUP

// Define which items we're allowed to see
$shopkeeper_unlocked = mmrpg_prototype_item_unlocked('auto-link') ? true : false;
$global_battle_items = !empty($_SESSION[$session_token]['values']['battle_items']) ? $_SESSION[$session_token]['values']['battle_items'] : array();
$global_battle_item_categories = array();
if ($shopkeeper_unlocked){
    $global_battle_item_categories['all'] = array(
    'category_name' => 'All Items',
        'category_quote' => 'The prototype is home to many different items.  Which ones have you collected so far?',
        'category_image' => 'robots/auto',
        'category_image_size' => 80
        );
} else {
    $global_battle_item_categories['all'] = array(
    'category_name' => 'All Items',
        'category_quote' => '▪▪▪▪▪ ▪▪ ▪▪▪▪? ▪▪ ▪▪▪ ▪▪▪▪...', // where is auto? im not sure...
        'category_image' => 'robots/met',
        'category_image_size' => 40
        );
}

// Filter out items that are not complete or otherwise attainable yet
$mmrpg_database_items = array_filter($mmrpg_database_items, function($item_info){
    if (empty($item_info['item_flag_published'])){ return false; }
    elseif (empty($item_info['item_flag_complete'])){ return false; }
    elseif (in_array($item_info['item_token'], array('field-star', 'fusion-star'))){ return false; }
    return true;
    });
$global_battle_items = array_filter($global_battle_items, function($item_token) use ($mmrpg_database_items){
    if (!isset($mmrpg_database_items[$item_token])){ return false; }
    return true;
    }, ARRAY_FILTER_USE_KEY);

// Pre-loop through and check to see what the max item is that is NOT hidden or at least unlocked
$tmp_counter = 0;
$max_non_hidden_counter = 0;
foreach ($mmrpg_database_items AS $item_token => $item_info){
    $tmp_counter++;
    if (!empty($item_info['item_flag_hidden'])
        && !mmrpg_prototype_item_unlocked($item_token)){
        continue;
        }
    $max_non_hidden_counter = $tmp_counter;
}

// If the user has collected any stars, make sure those contribute to the count
//if (!empty($this_battle_stars_field_count)){ $global_battle_items['field-star'] = $this_battle_stars_field_count; }
//if (!empty($this_battle_stars_fusion_count)){ $global_battle_items['fusion-star'] = $this_battle_stars_fusion_count; }


/*
error_log("\n---------------------------");

error_log('$global_battle_items(count) = '.print_r(count($global_battle_items), true));
error_log('$mmrpg_database_items(count) = '.print_r(count($mmrpg_database_items), true));

error_log('$global_battle_items vs $mmrpg_database_items = '.print_r(array_diff(
    array_keys($global_battle_items),
    array_keys($mmrpg_database_items)
    ), true));
error_log('$mmrpg_database_items vs $global_battle_items = '.print_r(array_diff(
    array_keys($mmrpg_database_items),
    array_keys($global_battle_items)
    ), true));

error_log('$global_battle_items = '.print_r($global_battle_items, true));
error_log('$mmrpg_database_items = '.print_r($mmrpg_database_items, true));
*/


// CONSOLE MARKUP

// Generate the console markup for this page
if (true){

    // Start the output buffer
    ob_start();

        // Update the player key to the current counter
        $item_key = $key_counter;
        //$item_info['item_image'] = $item_info['item_token'];
        //$item_info['item_image_size'] = 40;

        // Collect a temp robot object for printing items
        $player_info = $mmrpg_database_players['dr-light'];
        $robot_info = $mmrpg_database_robots['mega-man'];

        // Collect the category details for printing this tab
        $category_info = $global_battle_item_categories['all'];
        $category_image_size = $category_info['category_image_size'];
        $category_image_sizex = $category_image_size.'x'.$category_image_size;
        $category_image_class = 'sprite sprite_player sprite_player_sprite sprite_'.$category_image_sizex.' sprite_'.$category_image_sizex.'_00';
        $category_image_path = 'images/'.$category_info['category_image'].'/sprite_right_'.$category_image_sizex.'.png?'.MMRPG_CONFIG_CACHE_DATE;

        // Collect and print the editor markup for this player
        ?>
        <div class="event event_double event_visible">

            <div class="this_sprite sprite_left" style="top: 4px; left: 4px; width: 36px; height: 36px; background-image: url(images/fields/prototype-complete/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center center; border: 1px solid #1A1A1A;">
                <div class="<?= $category_image_class ?>" style="background-image: url(<?= $category_image_path ?>); "></div>
            </div>
            <div class="header header_left item_type item_type_none" style="margin-right: 0;">
                Item Inventory
            </div>

            <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">

                <div class="item_tabs_links" style="margin: 0 auto; color: #FFFFFF; ">
                    <span class="tab_spacer"><span class="inset">&nbsp;</span></span>
                    <?php
                    // Define a counter for the number of tabs
                    $tab_counter = 0;
                    // Loop through the item categories and display them
                    foreach ($global_battle_item_categories AS $category_token => $category_info){
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

                <div class="item_tabs_containers" style="margin: 0 auto 10px;">
                    <?

                    // Collect the array of unseen menu frame items if there is one, then clear it
                    $frame_token = 'items';
                    $menu_frame_content_unseen = rpg_prototype::get_menu_frame_content_unseen($frame_token);
                    rpg_prototype::clear_menu_frame_content_unseen($frame_token);

                    // Loop through the item categories and display tab containers for them
                    foreach ($global_battle_item_categories AS $category_token => $category_info){
                        $category_name = $category_info['category_name'];
                        $category_quote = $category_info['category_quote'];

                        ?>
                        <div class="tab_container tab_container_<?= $category_token ?>" data-tab="<?= $category_token ?>">

                            <div class="item_quote item_quote_<?= $category_token ?>">&quot;<?= $category_quote ?>&quot;</div>

                            <? /*
                            <table class="full" style="margin-bottom: 5px;">
                                <colgroup>
                                    <col width="50%" />
                                    <col width="50%" />
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="left">
                                            <span class="item_button item_button_header">&nbsp;</span>
                                            <label class="item_action item_action_header">Use</label>
                                            <label class="item_quantity item_quantity_header">Own</label>
                                        </th>
                                        <th class="right">
                                            <span class="item_button item_button_header">&nbsp;</span>
                                            <label class="item_action item_action_header">Use</label>
                                            <label class="item_quantity item_quantity_header">Own</label>
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                            */ ?>

                            <div class="scroll_wrapper">
                                <table class="full" style="margin-bottom: 5px;">
                                    <colgroup>
                                        <col width="50%" />
                                        <col width="50%" />
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                        <?
                                        // Loop through the items and print them one by one
                                        $item_counter = 0;
                                        $item_counter_total = count($mmrpg_database_items);
                                        foreach ($mmrpg_database_items AS $item_token => $item_info){

                                            $item_counter++;
                                            if ($max_non_hidden_counter > 0 && $item_counter > $max_non_hidden_counter){
                                                $item_counter--;
                                                break;
                                            }

                                            // Define basic item slot details
                                            $item_info_token = $item_info['item_token'];
                                            $item_info_name = $item_info['item_name'];
                                            $item_primary_type = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
                                            $item_info_type = $item_primary_type;
                                            if ($item_info_type != 'none' && !empty($item_info['item_type2'])){ $item_info_type .= '_'.$item_info['item_type2']; }
                                            elseif ($item_info_type == 'none' && !empty($item_info['item_type2'])){ $item_info_type = $item_info['item_type2']; }
                                            $item_sprite_image = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_info['item_token'];
                                            $item_cell_float = $item_counter % 2 == 0 ? 'right' : 'left';
                                            $temp_is_disabled = false;
                                            $temp_is_comingsoon = false;
                                            $temp_is_new = in_array($item_info_token, $menu_frame_content_unseen) ? true : false;

                                            // Check to see if this is a special item type
                                            $item_is_shard = strstr($item_token, '-shard') ? true : false;
                                            $item_is_core = strstr($item_token, '-core') ? true : false;
                                            //$item_is_star = strstr($item_token, '-star') ? true : false;

                                            // Define the editor title markup print options
                                            $item_print_options = array('show_quantity' => false);
                                            if (!mmrpg_prototype_item_unlocked('item-codes')){
                                                $item_print_options['show_use_desc'] = false;
                                                $item_print_options['show_shop_desc'] = false;
                                            }
                                            if (!mmrpg_prototype_item_unlocked('equip-codes')){
                                                $item_print_options['show_hold_desc'] = false;
                                            }

                                            // Only collect detailed info if this item has been seen
                                            if (isset($global_battle_items[$item_token])){

                                                // Collect the items details and current quantity
                                                $item_info_quantity = !empty($global_battle_items[$item_token]) ? $global_battle_items[$item_token] : 0;
                                                $temp_info_tooltip = rpg_item::print_editor_title_markup($robot_info, $item_info, $item_print_options);
                                                $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);

                                            }
                                            /*
                                            // Otherwise if this is a Field Star, use the total number as quantity
                                            elseif ($item_token == 'field-star' && !empty($this_battle_stars_field_count)){

                                                // Collect the items details and current quantity
                                                $item_info_quantity = !empty($this_battle_stars_field_count) ? $this_battle_stars_field_count : 0;
                                                $temp_info_tooltip = rpg_item::print_editor_title_markup($robot_info, $item_info, $item_print_options);
                                                $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);

                                            }
                                            // Otherwise if this is a Fusion Star, use the total number as quantity
                                            elseif ($item_token == 'fusion-star' && !empty($this_battle_stars_fusion_count)){

                                                // Collect the items details and current quantity
                                                $item_info_quantity = !empty($this_battle_stars_fusion_count) ? $this_battle_stars_fusion_count : 0;
                                                if (!empty($this_battle_stars_perfect_fusion_count)){ $item_info_quantity += $this_battle_stars_perfect_fusion_count; }
                                                $temp_info_tooltip = rpg_item::print_editor_title_markup($robot_info, $item_info, $item_print_options);
                                                $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);

                                            }
                                            */
                                            // Otherwise this item slot is mysterious
                                            else {

                                                // Define placeholder, mysterious details for the user
                                                $item_info_name = preg_replace('/[-_a-z0-9]/i', '?', $item_info['item_name']);
                                                $item_info_type = 'empty';
                                                $item_info_quantity = 0;
                                                $temp_info_tooltip = '';
                                                $temp_is_disabled = true;
                                                $temp_is_comingsoon = true;

                                            }

                                            ?>
                                            <td class="<?= $item_cell_float ?> item_cell <?= $temp_is_disabled ? 'item_cell_disabled' : '' ?>" data-kind="item" data-action="use-item" data-token="<?= !$temp_is_comingsoon ? $item_info_token : 'comingsoon' ?>" data-unlocked="<?= $temp_is_comingsoon ? 'coming-soon' : 'true' ?>" data-count="<?= $item_info_quantity ?>">
                                                <span class="item_number item_type item_type_empty"><span>No. <?= str_replace(' ', '&nbsp;', str_pad($item_counter, 2, ' ', STR_PAD_LEFT)); ?></span><?= ($temp_is_new ? '<i class="new type electric"></i>' : '') ?></span>
                                                <span class="item_name item_type item_type_<?= $item_info_type ?>" <?= !empty($temp_info_tooltip) ? 'data-click-tooltip="'.$temp_info_tooltip.'"' : '' ?>><?= $item_info_name ?></span>
                                                <? /* <a class="use_button item_type item_type_none" href="#">Use</a> */ ?>
                                                <? if (!$temp_is_comingsoon): ?>
                                                    <span class="item_sprite item_type item_type_empty"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/items/<?= $item_sprite_image ?>/icon_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE?>);"></span></span>
                                                <? else: ?>
                                                    <span class="item_sprite item_type item_type_empty"><span class="sprite sprite_40x40 sprite_40x40_00"></span></span>
                                                <? endif; ?>
                                                <label class="item_quantity" data-quantity="<?= $item_info_quantity ?>">x <?= $item_info_quantity ?></label>
                                            </td>
                                            <?

                                            if ($item_cell_float == 'right' && $item_counter < $item_counter_total){ echo '</tr><tr>'; }
                                        }
                                        if ($item_counter % 2 != 0){

                                            ?>
                                            <td class="right item_cell item_cell_disabled">
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
    $item_console_markup = ob_get_clean();
    $item_console_markup = preg_replace('/\s+/', ' ', trim($item_console_markup));

}

// Generate the edit markup using the battles settings and rewards
$this_item_markup = '';
if (true){
    // Prepare the output buffer
    ob_start();

    // Determine the token for the very first player in the edit
    $temp_item_tokens = array_keys($mmrpg_database_items);
    $first_item_token = array_shift($temp_item_tokens);
    $first_item_token = isset($first_item_token['item_token']) ? $first_item_token['item_token'] : $first_item_token;
    unset($temp_item_tokens);

    // Start generating the edit markup
    $num_items_collected = count($global_battle_items);
    $num_items_total = !empty($max_non_hidden_counter) ? $max_non_hidden_counter : count($mmrpg_database_items);
    ?>

    <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <span class="count">
            <i class="fa fas fa-briefcase"></i>
            Item Inventory
            <span class="progress">(<span id="item_counter">
                <?= number_format($num_items_collected, 0, '.', ',') ?> /
                <?= number_format($num_items_total, 0, '.', ',') ?>
                </span> Items)</span>
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
                        <div id="items" class="wrapper"></div>
                    </div>

                </td>
            </tr>
        </tbody>
    </table>
    </div>

    <?php

    // Collect the output buffer content
    $this_item_markup = preg_replace('#\s+#', ' ', trim(ob_get_clean()));
}

// DEBUG DEBUG DEBUG
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Items | Prototype | Mega Man RPG Prototype</title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="items" content="noindex,nofollow" />
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />
<link type="text/css" href=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/items.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="iframe" data-frame="items" data-mode="<?= $global_allow_editing ? 'editor' : 'viewer' ?>" data-source="<?= $global_frame_source ?>">
    <div id="prototype" class="hidden" style="opacity: 0;">
        <div id="item" class="menu">
            <div id="item_overlay">&nbsp;</div>
            <?= $this_item_markup ?>
        </div>
    </div>
    <script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
    <script type="text/javascript" src=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script>
    <script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/items.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript">
    // Update game settings for this page
    <? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>
    gameSettings.autoScrollTop = false;
    gameSettings.allowShopping = true;
    // Define the global arrays to hold the item console markup
    var itemConsoleMarkup = '<?= str_replace("'", "\'", $item_console_markup) ?>';
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