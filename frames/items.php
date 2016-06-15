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
//require_once('../data/database.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_types.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_players.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_robots.php');
require(MMRPG_CONFIG_ROOTDIR.'data/database_items.php');
require(MMRPG_CONFIG_ROOTDIR.'data/starforce.php');

// Collect the editor flag if set
$global_allow_editing = !defined('MMRPG_REMOTE_GAME_ID') ? true : false;


// -- GENERATE EDITOR MARKUP

// Manually remove items that should not show here
unset($mmrpg_database_items['heart']);
unset($mmrpg_database_items['star']);

// Define which items we're allowed to see
$global_battle_items = !empty($_SESSION[$session_token]['values']['battle_items']) ? $_SESSION[$session_token]['values']['battle_items'] : 0;
$global_battle_item_categories = array();
$global_battle_item_categories['all'] = array('category_name' => 'All Items', 'category_quote' => 'The prototype is home to many different items.  Which ones have you collected so far?');

//echo('<pre>$global_battle_items = '.print_r($global_battle_items, true).'</pre>'."\n");
//echo('<pre>$mmrpg_database_items = '.print_r($mmrpg_database_items, true).'</pre>'."\n");
//exit();

// -- PROCESS SHOP SELL ACTION -- //

// Check if an action request has been sent with an sell type
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'use-item'){

    // Collect the action variables from the request header, if they exist
    $temp_action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $temp_token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
    $temp_quantity = !empty($_REQUEST['quantity']) ? $_REQUEST['quantity'] : 0;
    $temp_target_player = !empty($_REQUEST['target_player']) ? $_REQUEST['target_player'] : '';
    $temp_target_robot = !empty($_REQUEST['target_robot']) ? $_REQUEST['target_robot'] : '';

    // If key variables are not provided, kill the script in error
    if (empty($temp_action)){ die('error|request-error|action-missing'); }
    elseif (empty($temp_token)){ die('error|request-error|token-missing'); }
    elseif (empty($temp_quantity)){ die('error|request-error|quantity-missing'); }

    // Check to ensure the provided item actually exists
    if (isset($mmrpg_database_items[$temp_token]) && isset($global_battle_items[$temp_token])){

        // Collect the current count for this item
        $temp_current_quantity = $global_battle_items[$temp_token];

        // Check to ensure the provided item quantity is not exceeded
        if ($temp_current_quantity >= $temp_quantity){

            // Use the item and run its code
            // ...
            // ...

            // Subtrack this item's quantity from current and update parent
            $temp_current_quantity -= $temp_quantity;
            $global_battle_items[$temp_token] = $temp_current_quantity;
            $_SESSION[$session_token]['values']['battle_items'][$temp_token] = $temp_current_quantity;

            // Save, produce the success message with the new field order
            mmrpg_save_game_session();
            exit('success|item-used|'.$temp_quantity.'|'.$temp_current_quantity);

        }
        // Otherwise if undefined kind
        else {

            // Print an error message and kill the script
            exit('error|invalid-quantity|'.$temp_quantity.'|'.$temp_current_quantity);

        }

    }
    // Otherwise if undefined kind
    else {

        // Print an error message and kill the script
        exit('error|invalid-item|'.$temp_token);

    }

}


// CONSOLE MARKUP

// Generate the console markup for this page
if (true){

    // Start the output buffer
    ob_start();

        // Update the player key to the current counter
        $item_key = $key_counter;
        $item_info['item_image'] = $item_info['item_token'];
        $item_info['item_image_size'] = 40;

        // Collect a temp robot object for printing items
        $player_info = $mmrpg_index['players']['dr-light'];
        $robot_info = $mmrpg_database_robots['mega-man'];
        // Collect and print the editor markup for this player
        ?>
        <div class="event event_double event_visible">

            <div class="this_sprite sprite_left" style="top: 4px; left: 4px; width: 36px; height: 36px; background-image: url(images/fields/prototype-complete/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center center; border: 1px solid #1A1A1A;">
                <div class="sprite sprite_player sprite_player_sprite sprite_40x40 sprite_40x40_00" style="margin-top: -4px; margin-left: -2px; background-image: url(images/robots/met/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE?>); "></div>
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

                                            // Define basic item slot details
                                            $item_info_token = $item_info['item_token'];
                                            $item_sprite_image = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_info['item_token'];
                                            $item_cell_float = $item_counter % 2 == 0 ? 'right' : 'left';
                                            $temp_is_disabled = false;
                                            $temp_is_comingsoon = false;

                                            // Only collect detailed info if this item has been seen
                                            if (isset($global_battle_items[$item_token])){

                                                // Collect the items details and current quantity
                                                $item_info_name = $item_info['item_name'];
                                                $item_info_type = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
                                                if ($item_info_type != 'none' && !empty($item_info['item_type2'])){ $item_info_type .= '_'.$item_info['item_type2']; }
                                                elseif ($item_info_type == 'none' && !empty($item_info['item_type2'])){ $item_info_type = $item_info['item_type2']; }
                                                $item_info_quantity = !empty($global_battle_items[$item_token]) ? $global_battle_items[$item_token] : 0;
                                                $temp_info_tooltip = rpg_item::print_editor_title_markup($robot_info, $item_info, array('show_quantity' => false));
                                                $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);
                                                //if ($item_info_quantity == 0){ $temp_is_disabled = true; }

                                            }
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
                                                <span class="item_number item_type item_type_empty">No. <?= str_replace(' ', '&nbsp;', str_pad($item_counter, 2, ' ', STR_PAD_LEFT)); ?></span>
                                                <span class="item_name item_type item_type_<?= $item_info_type ?>" <?= !empty($temp_info_tooltip) ? 'data-tooltip="'.$temp_info_tooltip.'"' : '' ?>><?= $item_info_name ?></span>
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
    ?>

    <span class="header block_1">Item Inventory (<span id="item_counter"><?= number_format(count($global_battle_items), 0, '.', ',') ?> / <?= number_format(count($mmrpg_database_items), 0, '.', ',') ?></span> Items)</span>

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
<title>View Items | Mega Man RPG Prototype | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="items" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/jquery.scrollbar.min.css?<?= MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/items.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="iframe" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">
    <div id="prototype" class="hidden" style="opacity: 0; <?= !$global_allow_editing ? 'width: 100% !important; ' : '' ?>">
        <div id="item" class="menu" style="position: relative;">
            <div id="item_overlay" style="border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background-color: rgba(0, 0, 0, 0.75); position: absolute; top: 50px; left: 6px; right: 4px; height: 340px; z-index: 9999; display: none;">&nbsp;</div>
            <?= $this_item_markup ?>
        </div>
    </div>
    <script type="text/javascript" src="scripts/jquery.js"></script>
    <script type="text/javascript" src="scripts/jquery.scrollbar.min.js"></script>
    <script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/items.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript">
    // Update game settings for this page
    gameSettings.fadeIn = <?= isset($_GET['fadein']) ? $_GET['fadein'] : 'true' ?>;
    gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
    gameSettings.cacheTime = '<?= MMRPG_CONFIG_CACHE_DATE ?>';
    gameSettings.autoScrollTop = false;
    gameSettings.allowShopping = true;
    // Define the global arrays to hold the item console markup
    var itemConsoleMarkup = '<?= str_replace("'", "\'", $item_console_markup) ?>';
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