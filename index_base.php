<?php

// MAINTENANCE
if (MMRPG_CONFIG_MAINTENANCE_MODE){
    die('<div style="font-family: Arial; font-size: 16px; line-height: 21px; margin: 0; padding: 20px 25%; background-color: #c58e00; color: #FFFFFF; text-align: left;">'.
        (defined('MMRPG_CONFIG_MAINTENANCE_MODE_MESSAGE') ? MMRPG_CONFIG_MAINTENANCE_MODE_MESSAGE : 'SERVER MAINTENANCE IN PROGRESS<br />Please stand by...').
        '</div>');
}

// Include the TOP file
require_once('top.php');

// Define strings to hold custom script and style links and/or markup
$website_include_stylesheets = '';
$website_include_javascript = '';

// Only process session updates if we're NOT in critical error mode
if (!defined('MMRPG_CRITICAL_ERROR')){

    // If this is a ping request, simply exit now that we've loaded session
    if (!empty($_POST['ping']) && preg_match('/^[-_a-z0-9\.\s]+$/i', $_POST['ping'])){
        $ping_text = $_POST['ping'];
        $ping_page = !empty($_POST['page']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['page']) ? $_POST['page'] : '';
        // If the ping page is not empty and we're logged in
        if (!empty($ping_page) && !rpg_user::is_guest()){
            // Update the database with the user's last page so we can keep track
            mmrpg_website_session_update($ping_page);
            //echo 'ping_page='.$ping_page."\n";
        }
        // Exit and print the ping relay
        exit($ping_text);
    }
    // Otherwise, if this is a regular request and we're logged in
    elseif (!rpg_user::is_guest()){
        // Update the database with the user's last page so we can keep track
        mmrpg_website_session_update($this_current_uri);
    }

}

// Clear the prototype temp session var
$_SESSION['PROTOTYPE_TEMP'] = array();

// Define the default SEO and markup variables
$this_seo_robots = 'index,follow';
$this_seo_title = 'Mega Man RPG Prototype';
$this_seo_keywords = 'megaman,mega man,protoman,proto man,bass,rpg,prototype,dr.light,dr.wily,dr.cossack,battle,browser,pbbg,ipad,firefox,chrome,safari';
$this_seo_description = 'Battle through more than 100 robot masters in classic RPG style with either Dr. Light and Mega Man, Dr. Wily and Bass, or Dr. Cossack and Proto Man!  The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster. Last updated '.mmrpg_print_cache_date().'.';
$this_markup_header = '';
$this_markup_counter = '';
$this_markup_body = '';
$this_markup_jsready = '';

// Only collect info if we're NOT in critical error mode
if (!defined('MMRPG_CRITICAL_ERROR')){

    // Define the default Open Graph tag variables
    $this_graph_data = array(
        'title' => 'Mega Man RPG Prototype',
        'type' => 'website',
        'url' => $this_current_url,
        'image' => MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo-2k19.png?'.MMRPG_CONFIG_CACHE_DATE,
        'site_name' => 'Mega Man RPG Prototype',
        'description' => $this_seo_description,
        );

    // Collect the recently updated posts for this player / guest
    if (!rpg_user::is_guest() && !empty($this_userinfo['user_backup_login'])){ $temp_last_login = $this_userinfo['user_backup_login']; }
    else { $temp_last_login = time() - MMRPG_SETTINGS_UPDATE_TIMEOUT; }
    $temp_user_id = !rpg_user::is_guest() ? rpg_user::get_current_userid() : MMRPG_SETTINGS_GUEST_ID;
    $temp_new_threads = $db->get_array_list("SELECT category_id, CONCAT(thread_id, '_', thread_mod_date) AS thread_session_token FROM mmrpg_threads WHERE thread_locked = 0 AND (thread_target = 0 OR thread_target = {$temp_user_id} OR user_id = {$temp_user_id}) AND thread_mod_date > {$temp_last_login}".(!rpg_user::is_guest() ? "  AND thread_mod_user <> {$temp_user_id}" : ''));
    if (empty($_SESSION['COMMUNITY']['threads_viewed'])){ $_SESSION['COMMUNITY']['threads_viewed'] = array(); }
    if (!empty($temp_new_threads)){ foreach ($temp_new_threads AS $key => $array){
        if (in_array($array['thread_session_token'], $_SESSION['COMMUNITY']['threads_viewed'])){ unset($temp_new_threads[$key]); }  }
    }
    $_SESSION['COMMUNITY']['threads_new'] = !empty($temp_new_threads) ? $temp_new_threads : array();
    $temp_new_threads_categories = array();
    $temp_new_threads_ids = array();
    if (!empty($temp_new_threads)){
        foreach ($temp_new_threads AS $info){
            if (!isset($temp_new_threads_categories[$info['category_id']])){ $temp_new_threads_categories[$info['category_id']] = 0; }
            if (in_array($info['thread_session_token'], $_SESSION['COMMUNITY']['threads_viewed'])){ unset($_SESSION['COMMUNITY']['threads_viewed'][array_search($info['thread_session_token'], $_SESSION['COMMUNITY']['threads_viewed'])]); }
            list($temp_id, $temp_mod) = explode('_', $info['thread_session_token']);
            $temp_new_threads_ids[] = $temp_id;
            $temp_new_threads_categories[$info['category_id']] += 1;
        }
    }
    $_SESSION['COMMUNITY']['threads_new_categories'] = $temp_new_threads_categories;
    //die('<pre>'.print_r($temp_new_threads_categories, true).'</pre>');
    // Collect the online leaderboard data for the currently online players
    $temp_leaderboard_online = mmrpg_prototype_leaderboard_online();

    // Collect any members that are currently viewing the community page
    $temp_viewing_community = mmrpg_website_sessions_active('community/', 3, true);

}

// Predefine the variable to hold nav menu markip
$index_nav_markup = '';

// Predefine a markup variable for the post and player counters too (for mobile)
$responsive_nav_counters = '';

// Only generate the menu if we're NOT in critical error mode
if (!defined('MMRPG_CRITICAL_ERROR')){

    // Start the output buffer to collect content
    ob_start();

    // Collect the main menu links from the database to be displayed below
    $main_menu_links = cms_website_page::get_frontend_menu_links();

    ?>
    <ul class="main">
        <?

        // Loop through the main menu links and print their markup
        ob_start();
        foreach ($main_menu_links AS $parent_token => $parent_info){

            // Collect basic info about this link
            $name = !empty($parent_info['name']) ? $parent_info['name'] : ucfirst($parent_token);
            $url = !empty($parent_info['url']) ? $parent_info['url'] : $parent_token.'/';
            $target = !empty($parent_info['target']) ? $parent_info['target'] : '_self';
            $active = $this_current_page == $parent_token ? true : false;
            $before = !empty($parent_info['before']) ? $parent_info['before'] : '';
            $after = !empty($parent_info['after']) ? $parent_info['after'] : '';
            $sub_menu_links = !empty($parent_info['subs']) ? $parent_info['subs'] : array();

            // Define menu item and link classes for styling
            $item_class = 'item '.($active ? 'item_active ' : '');
            $link_class = 'link '.($active ? 'link_active field_type_empty' : '');

            // If this the PROTOTYPE link, make sure we prefix the "play" text as requested
            if ($parent_token == 'prototype'){
                $name = '<i class="fa fas fa-gamepad"></i> Play Prototype';
            }

            // If this the COMMUNITY link, dynamically collect update counts
            if ($parent_token == 'community'){
                // Generate update count markup for this page's after string
                $after .= '';
                if (!empty($temp_new_threads)){
                    $counter = '<sup class="sup field_type field_type_electric" title="'.count($temp_new_threads).' New Comments">'.count($temp_new_threads).'</sup>';
                    $after .= $counter;
                    $responsive_nav_counters .= $counter;
                }
                if (!empty($temp_viewing_community)){
                    $counter = '<sup class="sup field_type field_type_nature" title="'.count($temp_viewing_community).' Members Viewing" style="'.(!empty($temp_new_threads) ? 'margin-left: -3px;' : '').'">'.count($temp_viewing_community).'</sup>';
                    $after .= $counter;
                }
            }

            // If this the LEADERBOARD link, dynamically collect online counts
            if ($parent_token == 'leaderboard'){
                // Generate update count markup for this page's after string
                $after .= '';
                if (!empty($temp_leaderboard_online)){
                    $counter = '<sup class="sup field_type field_type_nature" title="'.count($temp_leaderboard_online).' Players Online">'.count($temp_leaderboard_online).'</sup>';
                    $after .= $counter;
                    $responsive_nav_counters .= $counter;
                }
            }

            // Check to see if this page has sub-links in it
            $has_sub_links = !empty($sub_menu_links) ? true : false;
            if ($has_sub_links){ $item_class .= 'has_subs'; }

            // Print out the menu item markup
            ?>
            <li class="<?= $item_class ?>" data-token="<?= $parent_token ?>">
                <a href="<?= $url ?>" class="<?= $link_class ?>" target="<?= $target ?>">
                    <?= $before ?><span><?= $name ?></span><?= $after ?>
                </a>
                <?

                // If there were sub-pages to display, loop through and generate markup
                if (!empty($sub_menu_links)){
                    $base_url = $url;
                    $base_active = $active;
                    ?>
                    <ul class="subs field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                        <?
                        foreach ($sub_menu_links AS $sub_token => $sub_info){

                            // Collect basic info about this link
                            $name = !empty($sub_info['name']) ? $sub_info['name'] : ucfirst($sub_token);
                            $url = !empty($sub_info['url']) ? $sub_info['url'] : $parent_token.'/'.$sub_token.'/';
                            $target = !empty($sub_info['target']) ? $sub_info['target'] : '_self';
                            $before = !empty($sub_info['before']) ? $sub_info['before'] : '';
                            $after = !empty($sub_info['after']) ? $sub_info['after'] : '';
                            if ($parent_token == 'community'){
                                $active = $this_current_cat == $sub_token ? true : false;
                                if ($base_active && empty($this_current_cat) && $sub_token == 'home'){ $active = true; }
                            } else {
                                $active = $this_current_sub == $sub_token ? true : false;
                                if ($base_active && empty($this_current_sub) && $sub_token == 'home'){ $active = true; }
                            }

                            // Define menu item and link classes for styling
                            $item_class = 'item '.($active ? 'item_active ' : '');
                            $link_class = 'link '.($active ? 'link_active field_type_empty' : '');

                            // Print out the menu item markup
                            ?>
                            <li class="<?= $item_class ?>">
                                <a href="<?= $url ?>" class="<?= $link_class ?>" target="<?= $target ?>">
                                    <?= $before ?><span><?= $name ?></span><?= $after ?>
                                </a>
                            </li>
                            <?

                        }
                        ?>
                    </ul>
                    <?
                }

                ?>
            </li>
            <?

        }
        // Collect link markup and format for easier debugging
        $menu_links_markup = trim(ob_get_clean());
        $menu_links_markup = preg_replace('/\s+/', ' ', $menu_links_markup);
        $menu_links_markup = str_replace('<ul', PHP_EOL.'<ul', $menu_links_markup);
        $menu_links_markup = str_replace('</ul>', PHP_EOL.'</ul>', $menu_links_markup);
        echo PHP_EOL.$menu_links_markup.PHP_EOL;

        ?>
    </ul>
<?

// Collect output buffer content into the markup variable
$index_nav_markup .= trim(ob_get_clean()).PHP_EOL;

}

//echo('$this_current_uri = '.$this_current_uri.'<br />'.PHP_EOL);
//echo(preg_match('/^database\/(players|robots|mechas|bosses|abilities|items|fields|types)\//', $this_current_uri) ? 'true<br />' : 'false<br />');
//echo('$this_current_sub = '.$this_current_sub.'<br />'.PHP_EOL);
//echo('$this_current_cat = '.$this_current_cat.'<br />'.PHP_EOL);

// Check to see if this is a database-driver page first
$db_page_info = false;
if (empty($this_current_uri)
    && !empty($mmrpg_page_index['home/'])){
    $db_page_info = $mmrpg_page_index['home/'];
} elseif (!empty($this_current_uri)
    && preg_match('/^leaderboard\//', $this_current_uri)
    && !empty($mmrpg_page_index['leaderboard/'])){
    $db_page_info = $mmrpg_page_index['leaderboard/'];
} elseif (!empty($this_current_uri)
    && preg_match('/^database\//', $this_current_uri)){
    if (!empty($this_current_sub) && !empty($mmrpg_page_index['database/'.$this_current_sub.'/'])){
        $db_page_info = $mmrpg_page_index['database/'.$this_current_sub.'/'];
    } elseif (!empty($mmrpg_page_index['database/'])){
        $db_page_info = $mmrpg_page_index['database/'];
    }
} elseif (!empty($this_current_uri)
    && preg_match('/^community\//', $this_current_uri)){
    if (!empty($this_current_cat) && !empty($mmrpg_page_index['community/'.$this_current_cat.'/'])){
        $db_page_info = $mmrpg_page_index['community/'.$this_current_cat.'/'];
    } elseif (!empty($mmrpg_page_index['community/'])){
        $db_page_info = $mmrpg_page_index['community/'];
    }
} elseif (!empty($this_current_uri)
    && preg_match('/^file\//', $this_current_uri)){
    if (!empty($this_current_sub) && !empty($mmrpg_page_index['file/'.$this_current_sub.'/'])){
        $db_page_info = $mmrpg_page_index['file/'.$this_current_sub.'/'];
    } elseif (!empty($mmrpg_page_index['file/'])){
        $db_page_info = $mmrpg_page_index['file/'];
    }
} elseif (!empty($this_current_uri)
    && !empty($mmrpg_page_index[$this_current_uri])){
    $db_page_info = $mmrpg_page_index[$this_current_uri];
}
// Include db-page if exists, else include page logic from preset files
if (!empty($db_page_info)){
    require_once('pages/db-page.php');
} elseif (file_exists('pages/'.$this_current_page.'.php')){
    require_once('pages/'.$this_current_page.'.php');
}

//echo('<pre>$db_page_info = '.print_r($db_page_info, true).'</pre><br />'.PHP_EOL);
//exit();

// If no body markup was generated, there must be an error
if (empty($this_markup_body)
    || (defined('MMRPG_PAGE_NOT_FOUND')
        && MMRPG_PAGE_NOT_FOUND === true)){
    http_response_code(404);
    $this_markup_body = '';
    $this_markup_body .= '<div class="header"><div class="header_wrapper"><h1 class="title">404 Page Not Found</h1></div></div>';
    $this_markup_body .= '<div class="subbody">';
        $this_markup_body .= '<p class="text" style="text-align: center; padding: 20px; font-size: 120%; line-height: 1.6;">';
        $this_markup_body .= 'The page you were looking for could not be found. <br /> ';
        $this_markup_body .= 'Please return to <a class="link_inline" href="'.MMRPG_CONFIG_ROOTURL.'">the home page</a> and try again. ';
        $this_markup_body .= '</p>';
    $this_markup_body .= '</div>';
}

?>
<!DOCTYPE html>
<html lang="en" xmlns:og="http://opengraphprotocol.org/schema/" data-index="base">
<head>

<meta charset="UTF-8" />

<title><?= $this_seo_title ?></title>

<meta name="keywords" content="<?= $this_seo_keywords ?>" />
<meta name="description" content="<?= $this_seo_description ?>" />
<? if (MMRPG_CONFIG_IS_LIVE === true && MMRPG_CONFIG_SERVER_ENV === 'prod'){ ?>
    <meta name="robots" content="<?= !defined('MMRPG_CRITICAL_ERROR') && empty($_REQUEST['action']) && !empty($this_seo_robots) ? $this_seo_robots : 'noindex,nofollow' ?>,noodp" />
<? } else { ?>
    <meta name="robots" content="noindex,nofollow,noodp" />
<? } ?>
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />

<base href="<?= MMRPG_CONFIG_ROOTURL ?>">

<link rel="sitemap" type="application/xml" title="Sitemap" href="<?= MMRPG_CONFIG_ROOTURL ?>sitemap.xml" />

<? if(!defined('MMRPG_CRITICAL_ERROR')){  foreach ($this_graph_data AS $token => $value){ echo '<meta property="og:'.str_replace('__', ':', $token).'" content="'.$value.'"/>'."\n"; } } ?>

<link rel="browser-game-info" href="<?= MMRPG_CONFIG_ROOTURL ?>mmrpg-info.xml" />

<link rel="shortcut icon" type="image/x-icon" href="images/assets/<?= mmrpg_get_favicon() ?>">

<link type="text/css" href="styles/reset.css" rel="stylesheet" />

<style type="text/css"> html, body { background-color: #262626; } </style>

<? if ($this_current_page == 'home' || $this_current_page == 'gallery'): ?>
    <link type="text/css" href=".libs/colorbox/example3/colorbox.css" rel="stylesheet" />
<? endif; ?>

<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />

<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.css" rel="stylesheet" />
<link type="text/css" href="styles/index.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/index-responsive.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />

<? if ($this_current_page == 'dev'): ?>
    <link type="text/css" href="styles/dev.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
    <link type="text/css" href="styles/robots.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<? endif; ?>

<? if (!empty($website_include_stylesheets)){ ?>
    <?= $website_include_stylesheets ?>
<? } ?>

<link rel="apple-touch-icon" sizes="72x72" href="images/assets/ipad-icon-2k19_72x72.png" />
<?
// Define whether not this page/subpage combination require the legacy viewport settings
$legacy_viewport_required = false;
if ($this_current_page == 'leaderboard' // Leaderboard sub-pages
    && in_array($this_current_token, array('robots', 'players', 'database', 'items', 'stars', 'missions'))){
    $legacy_viewport_required = true;
}
if ($this_current_page == 'file' // File sub-pages
    && $this_current_sub == 'game'){
    $legacy_viewport_required = true;
}
?>
<? if ($legacy_viewport_required): ?>
    <meta name="viewport" content="user-scalable=yes, width=768, height=1004">
<? else: ?>
    <meta name="viewport" content="user-scalable=yes, width=device-width, initial-scale=1.0">
<? endif; ?>

</head>
<? $temp_window_flag = !empty($_SESSION['GAME']['index_settings']['windowFlag']) ? $_SESSION['GAME']['index_settings']['windowFlag'] : false; ?>
<body id="mmrpg" data-page="<?= trim(str_replace('/', '_', $this_current_uri), '_') ?>" class="index <?= !empty($temp_window_flag) ? 'windowFlag_'.$temp_window_flag : '' ?>">

    <div id="window" style="position: relative; height: auto !important;">

        <div class="banner <?= defined('MMRPG_INDEX_COMPACT_MODE') ? 'compact' : '' ?>">

            <?

            // Define variables based on login status
            if (!defined('MMRPG_CRITICAL_ERROR') && !rpg_user::is_guest()){
                // Define the avatar class and path variables
                $temp_avatar_path = !empty($this_userinfo['user_image_path']) ? $this_userinfo['user_image_path'] : 'robots/mega-man/40';
                //$temp_colour_token = !empty($this_userinfo['user_colour_token']) ? $this_userinfo['user_colour_token'] : '';
                list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
                $temp_avatar_class = 'avatar avatar_40x40';
                $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_00';
                $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_left_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $temp_background_path = !empty($this_userinfo['user_background_path']) ? $this_userinfo['user_background_path'] : 'fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN;
                $temp_foreground_path = !empty($this_userinfo['user_foreground_path']) ? $this_userinfo['user_foreground_path'] : 'fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN;
                list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
                list($temp_foreground_kind, $temp_foreground_token) = explode('/', $temp_foreground_path);
                $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE;
                $temp_foreground_path = 'images/'.$temp_foreground_kind.'/'.$temp_foreground_token.'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE;
                if (defined('MMRPG_INDEX_COMPACT_MODE')){ $temp_background_path = str_replace('.gif', '.png', $temp_background_path); }
                // Define the user name variables
                $temp_user_name = !empty($this_userinfo['user_name_public']) && !empty($this_userinfo['user_flag_postpublic']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name'];
                //echo '<div class="avatar avatar_40x40" style=""><div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/robot/sprite_left_40x40.png);">Guest</div></div>';
            } else {
                $temp_background_path = 'fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN;
                $temp_foreground_path = 'fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN;
                list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
                list($temp_foreground_kind, $temp_foreground_token) = explode('/', $temp_foreground_path);
                $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_background_base.gif';
                $temp_foreground_path = 'images/'.$temp_foreground_kind.'/'.$temp_foreground_token.'/battle-field_foreground_base.png';
                if (defined('MMRPG_INDEX_COMPACT_MODE')){ $temp_background_path = str_replace('.gif', '.png', $temp_background_path); }

            }
            //die($temp_background_path);

            // Define a variable to hold the background image style
            $temp_background_style = 'background-image: url('.(!empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_background_base.gif').'?'.MMRPG_CONFIG_CACHE_DATE.');';
            $temp_foreground_style = 'background-image: url('.(!empty($temp_foreground_path) ? $temp_foreground_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_foreground_base.png').'?'.MMRPG_CONFIG_CACHE_DATE.');';

            ?>
            <a class="anchor" id="top">&nbsp;</a>
            <div class="sprite background banner_background" style="<?= $temp_background_style ?>"></div>
            <div class="sprite foreground banner_foreground" style="<?= $temp_foreground_style ?>"></div>
            <div class="foreground scanlines" style="background-image: url(images/assets/canvas-scanlines.png?<?=MMRPG_CONFIG_CACHE_DATE?>);">&nbsp;</div>
            <div class="sprite credits banner_credits"><a href="<?= MMRPG_CONFIG_ROOTURL ?>"><h1>Mega Man RPG Prototype | Browser-Based Battle Simulator</h1></a></div>
            <div class="sprite overlay banner_overlay" style="">&nbsp;</div>

            <div class="header_social_icons x16">
                <a class="link" href="https://www.facebook.com/megamanrpgprototype/" target="_blank"><i class="icon facebook">Facebook</i></a>
                <a class="link" href="https://www.reddit.com/r/mmrpg/" target="_blank"><i class="icon reddit">Reddit</i></a>
                <a class="link" href="https://discord.gg/SCt8ccu" target="_blank"><i class="icon discord">Discord</i></a>
                <a class="link" href="https://github.com/AdrianMarceau/mmrpg-prototype" target="_blank"><i class="icon github">Github</i></a>
            </div>

            <? if(!defined('MMRPG_CRITICAL_ERROR')): ?>
                <div class="userinfo" style="">
                    <a class="expand" rel="nofollow"><span>+</span></a>
                    <span class="xcounters"><?= $responsive_nav_counters ?></span>
                    <div class="xover field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="">&nbsp;</div>

                    <? if(rpg_user::is_guest()): ?>
                        <div class="avatar avatar_40x40" style=""><div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/robot/sprite_left_40x40.png);">Guest</div></div>
                        <div class="info" style="">
                            <strong class="username" style="">Welcome, Guest</strong>
                            <a class="file file_new <?= $this_current_page == 'file' && $this_current_sub == 'new' ? 'file_active ' : '' ?>" href="file/new/" rel="nofollow" style="">new game</a> <span class="pipe">|</span>
                            <a class="file file_load <?= $this_current_page == 'file' && $this_current_sub == 'load' ? 'file_active ' : '' ?>" href="file/load/" rel="nofollow" style="">load game</a>
                        </div>
                    <? else: ?>
                        <div class="<?= $temp_avatar_class ?>" style=""><div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>);"><?= $temp_user_name ?></div></div>
                        <div class="info" style="">
                            <strong class="username" style="">Welcome, <?= $temp_user_name ?> <span class="pipe">|</span> <a class="place <?= $this_current_page == 'leaderboard' && $this_current_sub == $this_userinfo['user_name_clean'] ? 'place_active ' : '' ?>" href="leaderboard/<?= $this_userinfo['user_name_clean'] ?>/" rel="nofollow"><?= mmrpg_number_suffix($this_boardinfo['board_rank']) ?> Place</a></strong>
                            <a class="file file_view <?= $this_current_page == 'file' && $this_current_sub == 'game' ? 'file_active ' : '' ?>" href="file/game/" rel="nofollow" style="">view game</a> <span class="pipe">|</span>
                            <a class="file file_edit <?= $this_current_page == 'file' && $this_current_sub == 'profile' ? 'file_active ' : '' ?>" href="file/profile/" rel="nofollow" style="">edit profile</a> <span class="pipe">|</span>
                            <a class="file file_exit <?= $this_current_page == 'file' && $this_current_sub == 'exit' ? 'file_active ' : '' ?>" href="file/exit/" rel="nofollow" style="">exit game</a>
                        </div>
                    <? endif; ?>

                </div>
            <? endif; ?>

            <?
            // Check if an admin-compatible user is viewing (by IP)
            $is_admin = in_array($_SERVER['REMOTE_ADDR'], $dev_whitelist) ? true : false;
            if ($is_admin && rpg_game::is_demo()){
                ?>
                <div class="adminlink">
                    <a class="link" href="admin/" target="_blank"><span>Admin</span></a>
                </div>
                <?
            }
            ?>

        </div>

        <div class="menu field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <?= !empty($index_nav_markup) ? $index_nav_markup : '&hellip;&gt;_&lt;&hellip;' ?>
        </div>

        <div class="page page_<?= $this_current_page ?>">
            <? if (!empty($this_markup_header)): ?>
                <div class="header">
                    <div class="header_wrapper">
                        <div class="title">
                            <div class="wrap">
                                <i class="head-icon left"></i>
                                <i class="head-icon right"></i>
                                <h1><?= $this_current_page != 'home' ? preg_replace('/((?:the )?Mega Man RPG Prototype)/', '<span class="brand">$1</span>', $this_markup_header) : $this_markup_header ?></h1>
                            </div>
                            <?= !empty($this_markup_counter) ? $this_markup_counter."\n" : '' ?>
                        </div>
                    </div>
                </div>
            <? endif; ?>
            <div class="body">
                <div class="body_wrapper">
                    <?= $this_markup_body ?>
                </div>
            </div>
        </div>

    </div>

    <?
    // Require the common footer
    $footer_context = 'base';
    require(MMRPG_CONFIG_ROOTDIR.'includes/footer.php');
    ?>

    <a id="topscroll" href="<?= $this_current_url ?>"></a>

    <script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
    <script type="text/javascript" src=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script>
    <? if($this_current_page == 'home' || $this_current_page == 'gallery'): ?>
        <script type="text/javascript" src=".libs/colorbox/jquery.colorbox.js"></script>
    <? endif; ?>
    <? if($this_current_page == 'database' && $this_current_sub == 'types'): ?>
        <script type="text/javascript" src=".libs/chart-js/Chart-2.4.0.min.js"></script>
    <? endif; ?>
    <script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/index.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <? if ($this_current_page == 'dev'): ?>
    <script type="text/javascript" src="scripts/dev.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <? endif; ?>
    <script type="text/javascript">
    // Define the key client variables
    <? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>
    gameSettings.autoScrollTop = false;
    gameSettings.autoResizeHeight = false;
    <? if($this_current_page == 'community' && $this_current_cat == 'chat'): ?>
    gameSettings.autoKeepAlive = true;
    <? endif; ?>
    websiteSettings.currentHref = '<?= !empty($this_current_uri) ? $this_current_uri : '' ?>';
    websiteSettings.currentPage = '<?= !empty($this_current_page) ? $this_current_page : '' ?>';
    websiteSettings.currentSub = '<?= !empty($this_current_sub) ? $this_current_sub : '' ?>';
    websiteSettings.currentCat = '<?= !empty($this_current_cat) ? $this_current_cat : '' ?>';
    websiteSettings.currentToken = '<?= !empty($this_current_token) ? $this_current_token : '' ?>';
    websiteSettings.currentNum = <?= !empty($this_current_num) ? $this_current_num : 0 ?>;
    websiteSettings.currentId = <?= !empty($this_current_id) ? $this_current_id : 0 ?>;
    </script>
    <script type="text/javascript">

    // Define a function for fading in social buttons
    window.fadeInSocial = function(){
        $('#header_social_facebook')
            .css({opacity:0})
            .removeClass('fadein')
            .animate({opacity:1},600)
            ;
        $('#header_social_github')
            .css({opacity:0})
            .removeClass('fadein')
            .animate({opacity:1},600)
            ;
    }

    // When the document is ready for event binding
    $(document).ready(function(){
        var timeout = setTimeout(function(){ fadeInSocial() }, 2000);
        <? if($this_current_page == 'contact'): ?>
            $('.form .buttons').append('<input class="button button_submit" type="submit" value="Submit" />');
        <? endif; ?>
        <? if(!empty($this_markup_jsready)): ?>
            <?= $this_markup_jsready."\n" ?>
        <? endif; ?>
    });

    </script>
    <? if (!empty($website_include_javascript)){ ?>
        <?= $website_include_javascript ?>
    <? } ?>
    <?
    // Require the remote bottom in case we're in viewer mode
    require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php');
    ?>
</body>
</html>
<?
// If we're NOT in demo mode, automatically update the date-accessed for their database entry
if (rpg_game::is_user() && !empty($_SESSION['GAME']['USER']['userid'])){
    $temp_query = 'UPDATE mmrpg_users SET user_date_accessed = '.time().' WHERE user_id = '.$_SESSION['GAME']['USER']['userid'];
    $temp_result = $db->query($temp_query);
}
?>