<?
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
//require_once('../database/include.php');
require(MMRPG_CONFIG_ROOTDIR.'prototype/omega.php');
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require(MMRPG_CONFIG_ROOTDIR.'database/players.php');
require(MMRPG_CONFIG_ROOTDIR.'database/robots.php');
require(MMRPG_CONFIG_ROOTDIR.'database/abilities.php');
require(MMRPG_CONFIG_ROOTDIR.'database/fields.php');
require(MMRPG_CONFIG_ROOTDIR.'database/items.php');
require(MMRPG_CONFIG_ROOTDIR.'includes/starforce.php');

// Collect the editor flag if set
$global_allow_editing = !defined('MMRPG_REMOTE_GAME') ? true : false;
if (isset($_GET['edit']) && $_GET['edit'] == 'false'){ $global_allow_editing = false; }
$global_frame_source = !empty($_GET['source']) ? trim($_GET['source']) : 'prototype';


// -- GENERATE EDITOR MARKUP

// Require the shop index so we can use it's data
require(MMRPG_CONFIG_ROOTDIR.'includes/shop.php');

// Define which shops we're allowed to see
$allowed_edit_data = $this_shop_index;
$prototype_player_counter = !empty($_SESSION[$session_token]['values']['battle_rewards']) ? count($_SESSION[$session_token]['values']['battle_rewards']) : 0;
$prototype_complete_counter = mmrpg_prototype_complete();
$prototype_battle_counter = mmrpg_prototype_battles_complete('dr-light');
$allowed_edit_data_count = count($allowed_edit_data);

// HARD-CODE ZENNY FOR TESTING
//$_SESSION[$session_token]['counters']['battle_zenny'] = 500000;

// Define the array to hold all the item quantities
$global_item_quantities = array();
$global_item_prices = array();
$global_zenny_counter = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;

// Require the shop actions file to process requests
require(MMRPG_CONFIG_ROOTDIR.'frames/shop_actions.php');


// CANVAS MARKUP

// Generate the canvas markup for this page
if (true){

    // Start the output buffer
    ob_start();

    // Loop through the allowed edit data for all shops
    $key_counter = 0;
    $shop_counter = 0;
    foreach($allowed_edit_data AS $shop_token => $shop_info){
        $shop_counter++;
        //echo '<td style="width: '.floor(100 / $allowed_edit_shop_count).'%;">'."\n";
        echo '<div class="wrapper wrapper_'.($shop_counter % 2 != 0 ? 'left' : 'right').' player_type player_type_empty" data-select="shops" data-shop="'.$shop_info['shop_token'].'">'."\n";
        echo '<div class="wrapper_header player_type player_type_'.(!empty($shop_info['shop_colour']) ? $shop_info['shop_colour'] : 'none').'">'.$shop_info['shop_owner'].'</div>';
        $shop_key = $key_counter;
        $shop_info['shop_image'] = $shop_info['shop_token'];
        $shop_info['shop_image_size'] = 80;
        $shop_image_offset = 0;
        $shop_image_offset_x = -14 - $shop_image_offset;
        $shop_image_offset_y = -14 - $shop_image_offset;
        echo '<a data-token="'.$shop_info['shop_token'].'" data-shop="'.$shop_info['shop_token'].'" style="background-image: url(images/shops/'.(!empty($shop_info['shop_image']) ? $shop_info['shop_image'] : $shop_info['shop_token']).'/mug_right_'.$shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: '.$shop_image_offset_x.'px '.$shop_image_offset_y.'px;" class="sprite sprite_player sprite_shop_'.$shop_token.' sprite_shop_sprite sprite_'.$shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'].' sprite_'.$shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'].'_mugshot shop_status_active shop_position_active '.($shop_key == 0 ? 'sprite_shop_current ' : '').' player_type player_type_'.(!empty($shop_info['shop_colour']) ? $shop_info['shop_colour'] : 'none').'">'.$shop_info['shop_name'].'</a>'."\n";
        $key_counter++;
        //echo '<a class="sort" data-shop="'.$shop_info['shop_token'].'">sort</a>';
        echo '</div>'."\n";
        //echo '</td>'."\n";
    }

    // Collect the contents of the buffer
    $shop_canvas_markup = ob_get_clean();
    $shop_canvas_markup = preg_replace('/\s+/', ' ', trim($shop_canvas_markup));

}


// CONSOLE MARKUP

// Generate the console markup for this page
if (true){

    // Start the output buffer
    ob_start();

    // Loop through the shops in the field edit data
    $robot_info = rpg_robot::get_index_info('robot');
    foreach($allowed_edit_data AS $shop_token => $shop_info){

        // Update the player key to the current counter
        $shop_key = $key_counter;
        $shop_info['shop_image'] = $shop_info['shop_token'];
        $shop_info['shop_image_size'] = 40;

        // Collect a temp robot object for printing items
        $player_info = $mmrpg_index['players'][$shop_info['shop_player']];

        // Collect the tokens for all this shop's selling and buying tabs
        $shop_selling_tokens = is_array($shop_info['shop_kind_selling']) ? $shop_info['shop_kind_selling'] : array($shop_info['shop_kind_selling']);
        $shop_buying_tokens = is_array($shop_info['shop_kind_buying']) ? $shop_info['shop_kind_buying'] : array($shop_info['shop_kind_buying']);

        // Collect and print the editor markup for this player
        ?>

            <div class="event event_double event_<?= $shop_key == 0 ? 'visible' : 'hidden' ?>" data-token="<?= $shop_info['shop_token']?>">

                <div class="this_sprite sprite_left" style="top: 4px; left: 4px; width: 36px; height: 36px; background-image: url(images/fields/<?= $shop_info['shop_field']?>/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: center center; border: 1px solid #1A1A1A;">
                    <div class="sprite sprite_player sprite_shop_sprite sprite_<?= $shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'] ?> sprite_<?= $shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'] ?>_00" style="margin-top: -4px; margin-left: -2px; background-image: url(images/shops/<?= !empty($shop_info['shop_image']) ? $shop_info['shop_image'] : $shop_info['shop_token'] ?>/sprite_right_<?= $shop_info['shop_image_size'].'x'.$shop_info['shop_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE?>); "><?= $shop_info['shop_name']?></div>
                </div>

                <?
                // If this is someone with a core shop, we should show the core gauges
                if (mmrpg_prototype_item_unlocked('weapon-codes')
                    && in_array('cores', $shop_buying_tokens)){
                    ?>
                    <div class="gauge cores">
                        <?
                        // Loop through all elements and display gauge's for relevant ones
                        foreach ($mmrpg_database_types AS $type_token => $type_info){
                            if ($type_token === 'none' || $type_token === 'copy'){ continue; }
                            $core_level = !empty($core_level_index[$type_token]) ? $core_level_index[$type_token] : 0;
                            $core_opacity = 0.1 + (($core_level < 3 ? $core_level / 3 : 1) * 0.9);
                            ?>
                            <div class="element" style="opacity: <?= $core_opacity ?>;" data-type="<?= $type_token ?>" data-tooltip="<?= $type_info['type_name'].' Cores &times; '.$core_level ?>" data-tooltip-type="item_type type_<?= $type_token ?>">
                                <div class="sprite sprite_left sprite_left_40x40" style="background-image: url(images/items/<?= $type_token ?>-core/icon_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div>
                                <div class="counter"><?= $core_level >= 9 ? '&bigstar;' : $core_level ?></div>
                            </div>
                            <?
                        }
                        ?>
                    </div>
                    <?
                }
                ?>

                <div class="header header_left player_type player_type_<?= $shop_info['shop_colour'] ?>" style="margin-right: 0;">
                    <span class="title player_type">
                        <?= $shop_info['shop_name']?>
                    </span>
                    <?

                    // Only show omega indicators if the the Omega Seed has been unlocked
                    if (mmrpg_prototype_item_unlocked('omega-seed')){

                        // Print out the omega indicators for the shop
                        echo '<span class="omega player_type type_'.$shop_info['shop_hidden_power'].'" title="Omega Influence || [['.ucfirst($shop_info['shop_hidden_power']).' Type]]"></span>'.PHP_EOL;
                        //title="Omega Influence || [['.ucfirst($shop_info['shop_hidden_power']).' Type]]"
                        //echo 'omega('.$shop_info['shop_omega_string'].')';

                    }

                    ?>
                    <span class="core player_type">
                        <span class="wrap"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/items/<?= !empty($shop_info['shop_seeking_image']) ? $shop_info['shop_seeking_image'] : 'item' ?>/icon_left_40x40.png);"></span></span>
                        <span class="text"><?= ucfirst(rtrim($shop_info['shop_seeking'], 's')) ?> Seeker</span>
                    </span>
                </div>

                <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">

                    <div class="shop_tabs_links" style="margin: 0 auto; color: #FFFFFF; ">
                        <span class="tab_spacer"><span class="inset">&nbsp;</span></span>

                        <?

                        // Define a counter for the number of tabs
                        $tab_counter = 0;

                        // Loop through the selling tokens and display tabs for them
                        foreach ($shop_selling_tokens AS $selling_token){
                            if ($selling_token == 'items' && $shop_token == 'kalinka'){ $selling_name = 'Parts'; }
                            else { $selling_name = ucfirst($selling_token); }
                            ?>
                                <span class="tab_spacer"><span class="inset">&nbsp;</span></span>
                                <a class="tab_link tab_link_selling" href="#" data-tab="selling" data-tab-type="<?= $selling_token ?>"><span class="inset">Buy <?= $selling_name ?></span></a>
                            <?
                            $tab_counter++;
                        }

                        // Loop through the buying tokens and display tabs for them
                        foreach ($shop_buying_tokens AS $buying_token){
                            if ($buying_token === 'stars'){ $buying_label = 'Show '.ucfirst($buying_token); }
                            else { $buying_label = 'Sell '.ucfirst($buying_token); }
                            ?>
                                <span class="tab_spacer"><span class="inset">&nbsp;</span></span>
                                <a class="tab_link tab_link_buying" href="#" data-tab="buying" data-tab-type="<?= $buying_token ?>"><span class="inset"><?= $buying_label ?></span></a>
                            <?
                            $tab_counter++;
                        }

                        // Define the tab width total
                        $tab_width = $tab_counter * (1 + 20);
                        $line_width = 96 - $tab_width;

                        ?>

                        <span class="tab_line" style="width: <?= $line_width ?>%;"><span class="inset">&nbsp;</span></span>
                        <span class="tab_level"><span class="wrap">Level <?= $shop_info['shop_level'] ?></span></span>

                    </div>

                    <div class="shop_tabs_containers" style="margin: 0 auto 10px;">

                        <?

                        // Include the selling and buying markup for the shop
                        require(MMRPG_CONFIG_ROOTDIR.'frames/shop_selling.php');
                        require(MMRPG_CONFIG_ROOTDIR.'frames/shop_buying.php');

                        ?>

                    </div>

                </div>
            </div>

        <?

        // Increment the key counter
        $key_counter++;

    }

    // Collect the contents of the buffer
    $shop_console_markup = ob_get_clean();
    $shop_console_markup = preg_replace('/\s+/', ' ', trim($shop_console_markup));

}

// Generate the edit markup using the battles settings and rewards
$this_shop_markup = '';
if (true){

    // Prepare the output buffer
    ob_start();

    // Determine the token for the very first player in the edit
    $temp_shop_tokens = array_keys($allowed_edit_data);
    $first_shop_token = array_shift($temp_shop_tokens);
    $first_shop_token = isset($first_shop_token['shop_token']) ? $first_shop_token['shop_token'] : $first_shop_token;
    unset($temp_shop_tokens);

    // Start generating the edit markup
    ?>

        <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <span class="count">
                Item Shop (<span id="zenny_counter"><?= number_format($global_zenny_counter, 0, '.', ',') ?></span> Zenny)
            </span>
        </span>

        <div style="float: left; width: 100%;">
            <table class="formatter" style="width: 100%; table-layout: fixed;">
                <colgroup>
                    <col width="70" />
                    <col width="" />
                </colgroup>
                <tbody>
                    <tr>
                        <td class="canvas" style="vertical-align: top;">
                            <div id="canvas" class="shop_counter_<?= $shop_counter ?>">
                                <div id="links"></div>
                            </div>
                        </td>
                        <td class="console" style="vertical-align: top;">
                            <div id="console" class="noresize" style="height: auto;">
                                <div id="shops" class="wrapper"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    <?

    // Collect the output buffer content
    $this_shop_markup = preg_replace('#\s+#', ' ', trim(ob_get_clean()));

}

// DEBUG DEBUG DEBUG
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>View Shop | Mega Man RPG Prototype | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="shops" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link rel="shortcut icon" type="image/x-icon" href="images/assets/favicon<?= !MMRPG_CONFIG_IS_LIVE ? '-local' : '' ?>.ico">
<link type="text/css" href="styles/jquery.scrollbar.min.css?<?= MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/shop.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="iframe" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">
    <div id="prototype" class="hidden" style="opacity: 0; <?= !$global_allow_editing ? 'width: 100% !important; ' : '' ?>">
        <div id="shop" class="menu" style="position: relative;">
            <div id="shop_overlay" style="border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background-color: rgba(0, 0, 0, 0.75); position: absolute; top: 50px; left: 6px; right: 4px; height: 340px; z-index: 9999; display: none;">&nbsp;</div>
            <?= $this_shop_markup ?>
        </div>
    </div>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.scrollbar.min.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/shop.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">

// Update game settings for this page
gameSettings.fadeIn = <?= isset($_GET['fadein']) ? $_GET['fadein'] : 'true' ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
gameSettings.autoScrollTop = false;
gameSettings.allowShopping = true;

// Update the player and player count by counting elements
thisShopData.unlockedPlayers = <?= json_encode(array_keys($_SESSION[$session_token]['values']['battle_rewards'])) ?>;
thisShopData.zennyCounter = <?= $global_zenny_counter ?>;
thisShopData.itemPrices = <?= json_encode($global_item_prices) ?>;
thisShopData.itemQuantities = <?= json_encode($global_item_quantities) ?>;

// Define the global arrays to hold the shop console and canvas markup
var shopCanvasMarkup = '<?= str_replace("'", "\'", $shop_canvas_markup) ?>';
var shopConsoleMarkup = '<?= str_replace("'", "\'", $shop_console_markup) ?>';

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
