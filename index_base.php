<?php

// MAINTENANCE
if (MMRPG_CONFIG_MAINTENANCE_MODE && !in_array($_SERVER['REMOTE_ADDR'], array('99.226.253.166', '127.0.0.1', '99.226.238.61', '72.137.208.122'))){
    die('<div style="font-family: Arial; font-size: 16px; line-height: 21px; margin: 0; padding: 20px 25%; background-color: rgb(0, 122, 0); color: #FFFFFF; text-align: left; border-bottom: 1px solid #090909;">
        UPDATE IN PROGRESS<br /> The Mega Man RPG Prototype is currently being updated.  Please stand by until further notice.  Several parts of the website are being taken offline during this process and any progress made during will likely be lost, so please hold tight before trying to log in again.  I apologize for the inconvenience and thank you for your patience.<br /> - Adrian
        </div>');
}

// Include the TOP file
require_once('top.php');

// Only process session updates if we're NOT in critical error mode
if (!defined('MMRPG_CRITICAL_ERROR')){

    // If this is a ping request, simply exit now that we've loaded session
    if (!empty($_POST['ping']) && preg_match('/^[-_a-z0-9\.\s]+$/i', $_POST['ping'])){
        $ping_text = $_POST['ping'];
        $ping_page = !empty($_POST['page']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['page']) ? $_POST['page'] : '';
        // If the ping page is not empty and we're logged in
        if (!empty($ping_page) && $this_userid != MMRPG_SETTINGS_GUEST_ID){
            // Update the database with the user's last page so we can keep track
            mmrpg_website_session_update($ping_page);
            //echo 'ping_page='.$ping_page."\n";
        }
        // Exit and print the ping relay
        exit($ping_text);
    }
    // Otherwise, if this is a regular request and we're logged in
    elseif ($this_userid != MMRPG_SETTINGS_GUEST_ID){
        // Update the database with the user's last page so we can keep track
        mmrpg_website_session_update($this_current_uri);
    }

}

// Clear the prototype temp session var
$_SESSION['PROTOTYPE_TEMP'] = array();

// Define the default SEO and markup variables
$this_seo_robots = 'index,follow';
$this_seo_title = 'Mega Man RPG Prototype | Last Updated '.preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE);
$this_seo_keywords = 'megaman,mega man,protoman,proto man,bass,rpg,prototype,dr.light,dr.wily,dr.cossack,battle,browser,pbbg,ipad,firefox,chrome,safari';
$this_seo_description = 'Battle through more than thirty robot masters in classic RPG style with either Dr. Light and Mega Man, Dr. Wily and Bass, or Dr. Cossack and Proto Man!  The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';
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
        'image' => MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png?'.MMRPG_CONFIG_CACHE_DATE,
        'site_name' => 'Mega Man RPG Prototype',
        'description' => $this_seo_description,
        );

    // Collect the recently updated posts for this player / guest
    if ($this_userinfo['user_id'] != MMRPG_SETTINGS_GUEST_ID && !empty($this_userinfo['user_backup_login'])){ $temp_last_login = $this_userinfo['user_backup_login']; }
    else { $temp_last_login = time() - MMRPG_SETTINGS_UPDATE_TIMEOUT; }
    $temp_new_threads = $db->get_array_list("SELECT category_id, CONCAT(thread_id, '_', thread_mod_date) AS thread_session_token FROM mmrpg_threads WHERE thread_locked = 0 AND (thread_target = 0 OR thread_target = {$this_userinfo['user_id']} OR user_id = {$this_userinfo['user_id']}) AND thread_mod_date > {$temp_last_login}".($this_userid != MMRPG_SETTINGS_GUEST_ID ? "  AND thread_mod_user <> {$this_userid}" : ''));
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

    // Collect the number of people currently in chat
    $chat_online = $db->get_array_list("SELECT userID, userName, userRole, channel, dateTime, ip FROM ajax_chat_online WHERE 1 = 1;");
    // Increment the community count by the number in the chat room
    if (!empty($chat_online)){
        foreach ($chat_online AS $online){
            $temp_viewing_community[] = array('user_id' => $online['userID'], 'session_href' => 'chat/');
        }
    }

}

// Include the required page logic files
require_once('pages/'.$this_current_page.'.php');

?>
<!DOCTYPE html>
<html lang="en" xmlns:og="http://opengraphprotocol.org/schema/">
<head>

<meta charset="UTF-8" />

<title><?= $this_seo_title ?></title>

<meta name="keywords" content="<?= $this_seo_keywords ?>" />
<meta name="description" content="<?= $this_seo_description ?>" />
<meta name="robots" content="<?= !defined('MMRPG_CRITICAL_ERROR') && empty($_REQUEST['action']) && !empty($this_seo_robots) ? $this_seo_robots : 'noindex,nofollow' ?>,noodp" />

<base href="<?= MMRPG_CONFIG_ROOTURL ?>">

<link rel="sitemap" type="application/xml" title="Sitemap" href="<?= MMRPG_CONFIG_ROOTURL ?>sitemap.xml" />

<? if(!defined('MMRPG_CRITICAL_ERROR')){  foreach ($this_graph_data AS $token => $value){ echo '<meta property="og:'.str_replace('__', ':', $token).'" content="'.$value.'"/>'."\n"; } } ?>

<link rel="browser-game-info" href="<?= MMRPG_CONFIG_ROOTURL ?>mmrpg-info.xml" />

<link rel="shortcut icon" type="image/x-icon" href="images/assets/favicon<?= !MMRPG_CONFIG_IS_LIVE ? '-local' : '' ?>.ico">

<link type="text/css" href="styles/reset.css" rel="stylesheet" />

<style type="text/css"> html, body { background-color: #262626; } </style>

<? if($this_current_page == 'home' || $this_current_page == 'gallery'): ?>
    <link type="text/css" href="_ext/colorbox/jquery.colorbox.css" rel="stylesheet" />
<? endif; ?>

<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/index.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/index-responsive.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />

<meta name="format-detection" content="telephone=no" />
<link rel="apple-touch-icon" sizes="72x72" href="images/assets/ipad-icon_72x72.png" />
<meta name="viewport" content="user-scalable=yes, width=device-width, initial-scale=1.0">

</head>
<? $temp_window_flag = !empty($_SESSION['GAME']['index_settings']['windowFlag']) ? $_SESSION['GAME']['index_settings']['windowFlag'] : false; ?>
<body id="mmrpg" data-page="<?= trim(str_replace('/', '_', $this_current_uri), '_') ?>" class="index <?= !empty($temp_window_flag) ? 'windowFlag_'.$temp_window_flag : '' ?>">

<div id="fb-root"></div>
    <div id="window" style="position: relative; height: auto !important;">
        <div class="banner">
            <?
            // Collect the current user's info from the database
            //$this_userinfo = $db->get_array("SELECT users.*, roles.* FROM mmrpg_users AS users LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id WHERE users.user_id = '{$this_userid}' LIMIT 1");
            // Define variables based on login status
            if (!defined('MMRPG_CRITICAL_ERROR') && $this_userid != MMRPG_SETTINGS_GUEST_ID){
                // Define the avatar class and path variables
                $temp_avatar_path = !empty($this_userinfo['user_image_path']) ? $this_userinfo['user_image_path'] : 'robots/mega-man/40';
                $temp_background_path = !empty($this_userinfo['user_background_path']) ? $this_userinfo['user_background_path'] : 'fields/intro-field';
                //$temp_colour_token = !empty($this_userinfo['user_colour_token']) ? $this_userinfo['user_colour_token'] : '';
                list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
                list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
                $temp_avatar_class = 'avatar avatar_40x40';
                $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_00';
                $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_left_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE;
                // Define the user name variables
                $temp_user_name = !empty($this_userinfo['user_name_public']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name'];
                //echo '<div class="avatar avatar_40x40" style=""><div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/robot/sprite_left_40x40.png);">Guest</div></div>';
            } else {
                $temp_background_path = 'fields/intro-field';
                list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
                $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_background_base.gif';
            }
            //die($temp_background_path);
            ?>
            <a class="anchor" id="top">&nbsp;</a>
            <div class="sprite background banner_background" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_background_base.gif' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>);"><?= ucwords(str_replace('-', ' ', MMRPG_SETTINGS_CURRENT_FIELDTOKEN)) ?></div>
            <?
            // Only continue if we're NOT in critical error mode
            if (!defined('MMRPG_CRITICAL_ERROR')){

                // Define the paths for the different attachment types
                $class_paths = array('ability' => 'abilities', 'battle' => 'battles', 'field' => 'fields', 'player' => 'players', 'robot' => 'robots', 'object' => 'objects');
                // Loop through and display the markup of any background attachments
                if (!empty($temp_field_data['field_background_attachments']) && !empty($temp_field_data['field_mechas'])){
                    echo '<div class="background_event event clearback sticky" style="z-index: 15; border-color: transparent;">';
                    foreach ($temp_field_data['field_background_attachments'] AS $this_key => $this_info){
                        $this_class = $this_info['class'];
                        $this_size = $this_info['size'];
                        $this_boxsize = $this_size.'x'.$this_size;
                        $this_path = $class_paths[$this_class];
                        $this_offset_x = $this_info['offset_x'];
                        $this_offset_y = $this_info['offset_y'];
                        $this_offset_z = $this_key + 1;
                        if ($this_class == 'robot'){
                            $this_token = $temp_field_data['field_mechas'][array_rand($temp_field_data['field_mechas'])]; //$this_info[$this_class.'_token'];
                            $temp_sprite_frame = array('base', 'defend', 'taunt', 'victory');
                            $temp_sprite_frame = $temp_sprite_frame[array_rand($temp_sprite_frame)];
                            $this_frames = array($temp_sprite_frame); //$this_info[$this_class.'_frame'];
                        } else {
                            $this_token = $this_info[$this_class.'_token'];
                            $this_frames = $this_info[$this_class.'_frame'];
                        }
                        foreach ($this_frames AS $key => $frame){ if (is_numeric($frame)){ $this_frames[$key] = str_pad($frame, 2, '0', STR_PAD_LEFT); } }
                        $this_frame = $this_frames[0];
                        //if ($debug_flag_animation){ $this_animate = implode(',', $this_frames); }
                        //else { $this_animate = $this_frame; }
                        $this_animate = implode(',', $this_frames);
                        $this_direction = $this_info[$this_class.'_direction'];
                        $this_float = $this_direction == 'left' ? 'right' : 'left';
                        echo '<div data-id="background_attachment_'.$this_key.'" class="sprite sprite_'.$this_boxsize.' sprite_'.$this_boxsize.'_'.$this_direction.' sprite_'.$this_boxsize.'_'.$this_frame.'" data-type="attachment" data-position="background" data-size="'.$this_size.'" data-direction="'.$this_direction.'" data-frame="'.$this_frame.'" data-animate="'.$this_animate.'" style="'.$this_float.': '.$this_offset_x.'px; bottom: '.$this_offset_y.'px; z-index: '.$this_offset_z.'; background-image: url(images/'.$this_path.'/'.$this_token.'/sprite_'.$this_direction.'_'.$this_boxsize.'.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.ucwords(str_replace('-', ' ', $this_token)).'</div>';
                    }
                    echo '</div>';
                }

            }
            ?>
            <div class="foreground scanlines" style="background-image: url(images/gui/canvas-scanlines.png?<?=MMRPG_CONFIG_CACHE_DATE?>);">&nbsp;</div>
            <div class="sprite credits banner_credits" style="background-image: url(images/menus/menu-banner_credits.png?<?=MMRPG_CONFIG_CACHE_DATE?>);">Mega Man RPG Prototype | PlutoLighthouse.NET</div>
            <div class="sprite overlay banner_overlay" style="">&nbsp;</div>

            <? if(!defined('MMRPG_CRITICAL_ERROR') && MMRPG_CONFIG_IS_LIVE): ?>
                <? if($this_current_page != 'file'): ?>

                <!-- FACEBOOK -->
                <div id="header_social_facebook" class="sprite fadein">
                    <iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fmegamanpoweredup.net%2Frpg2k11%2F&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=409819409099131" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>
                </div>

                <!-- GITHUB -->
                <div id="header_social_github" class="sprite fadein">
                    <a class="github-button" href="https://github.com/AdrianMarceau/mmrpg-prototype" data-count-href="/AdrianMarceau/followers" data-count-api="/users/AdrianMarceau#followers" data-count-aria-label="# followers on GitHub" aria-label="Follow @AdrianMarceau and MMRPG-Prototype on GitHub">Follow</a>
                </div>

                <? /*
                <!-- GOOGLE+ -->
                <div id="header_social_google" class="sprite fadein">
                    <div class="g-plusone" data-annotation="none" data-href="<?= MMRPG_CONFIG_ROOTURL ?>"></div>
                </div>
                */ ?>

                <? endif; ?>
            <? endif; ?>

            <? if(!defined('MMRPG_CRITICAL_ERROR')): ?>
                <div class="userinfo" style="">
                    <a class="expand" rel="nofollow"><span>+</span></a>
                    <div class="field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="">&nbsp;</div>
                    <?/*
                    <? if($this_userid == MMRPG_SETTINGS_GUEST_ID || !MMRPG_CONFIG_ADMIN_MODE): ?>
                        <div class="avatar avatar_40x40" style=""><div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/robot/sprite_left_40x40.png);">Guest</div></div>
                        <div class="info" style="">
                            <strong class="username" style="">Welcome, Guest</strong>
                            <? if(MMRPG_CONFIG_ADMIN_MODE): ?>
                                <a class="file file_new <?= $this_current_page == 'file' && $this_current_sub == 'new' ? 'file_active ' : '' ?>" href="file/new/" rel="nofollow" style="">new game</a> <span class="pipe">|</span>
                                <a class="file file_load <?= $this_current_page == 'file' && $this_current_sub == 'load' ? 'file_active ' : '' ?>" href="file/load/" rel="nofollow" style="">load game</a>
                            <? else: ?>
                                <a class="file file_new <?= $this_current_page == 'file' && $this_current_sub == 'new' ? 'file_active ' : '' ?>" style="text-decoration: line-through;">new game</a> <span class="pipe">|</span>
                                <a class="file file_load <?= $this_current_page == 'file' && $this_current_sub == 'load' ? 'file_active ' : '' ?>" style="text-decoration: line-through;">load game</a>
                            <? endif; ?>
                        </div>
                    <? else: ?>
                        <div class="<?= $temp_avatar_class ?>" style=""><div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>);"><?= $temp_user_name ?></div></div>
                        <div class="info" style="">
                            <strong class="username" style="">Welcome, <?= $temp_user_name ?> <span class="pipe">|</span> <a class="place <?= $this_current_page == 'leaderboard' && $this_current_sub == $this_userinfo['user_name_clean'] ? 'place_active ' : '' ?>" href="leaderboard/<?= $this_userinfo['user_name_clean'] ?>/" rel="nofollow"><?= mmrpg_number_suffix($this_boardinfo['board_rank']) ?> Place</a></strong>
                            <a class="file file_save <?= $this_current_page == 'file' && $this_current_sub == 'game' ? 'file_active ' : '' ?>" href="file/game/" rel="nofollow" style="">view game</a> <span class="pipe">|</span>
                            <a class="file file_save <?= $this_current_page == 'file' && $this_current_sub == 'profile' ? 'file_active ' : '' ?>" href="file/profile/" rel="nofollow" style="">edit profile</a> <span class="pipe">|</span>
                            <a class="file file_exit <?= $this_current_page == 'file' && $this_current_sub == 'exit' ? 'file_active ' : '' ?>" href="file/exit/" rel="nofollow" style="">exit game</a>
                        </div>
                    <? endif; ?>
                    */?>

                    <? if($this_userid == MMRPG_SETTINGS_GUEST_ID): ?>
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
                            <a class="file file_save <?= $this_current_page == 'file' && $this_current_sub == 'game' ? 'file_active ' : '' ?>" href="file/game/" rel="nofollow" style="">view game</a> <span class="pipe">|</span>
                            <a class="file file_save <?= $this_current_page == 'file' && $this_current_sub == 'profile' ? 'file_active ' : '' ?>" href="file/profile/" rel="nofollow" style="">edit profile</a> <span class="pipe">|</span>
                            <a class="file file_exit <?= $this_current_page == 'file' && $this_current_sub == 'exit' ? 'file_active ' : '' ?>" href="file/exit/" rel="nofollow" style="">exit game</a>
                        </div>
                    <? endif; ?>

                </div>
            <? endif; ?>

        </div>


        <div class="menu field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
            <?
                // Only generate the menu if we're NOT in critical error mode
                if (!defined('MMRPG_CRITICAL_ERROR')){

                    // Define the basic array of pages to show in the main menu (we'll do subs later)
                    $main_menu_links = array();
                    $main_menu_links['home'] = array('name' => 'Home', 'url' => '/');
                    $main_menu_links['about'] = array('name' => 'About');
                    $main_menu_links['gallery'] = array('name' => 'Gallery');
                    $main_menu_links['database'] = array('name' => 'Database', 'subs' => array(
                        'players' => array('name' => 'Players'),
                        'robots' => array('name' => 'Robots'),
                        'mechas' => array('name' => 'Mechas'),
                        'bosses' => array('name' => 'Bosses'),
                        'abilities' => array('name' => 'Abilities'),
                        'items' => array('name' => 'Items'),
                        'fields' => array('name' => 'Fields'),
                        'types' => array('name' => 'Types')
                        ));
                    $main_menu_links['community'] = array('name' => 'Community');
                    $main_menu_links['leaderboard'] = array('name' => 'Leaderboard');
                    $main_menu_links['prototype'] = array('name' => 'Prototype', 'target' => '_blank');
                    $main_menu_links['credits'] = array('name' => 'Credits');
                    $main_menu_links['contact'] = array('name' => 'Contact');

                    ?>
                    <ul class="main">
                        <?

                        // Loop through the main menu links and print their markup
                        ob_start();
                        foreach ($main_menu_links AS $token => $info){

                            // Collect basic info about this link
                            $name = !empty($info['name']) ? $info['name'] : ucfirst($token);
                            $url = !empty($info['url']) ? MMRPG_CONFIG_ROOTURL.ltrim($info['url'], '/') : $token.'/';
                            $target = !empty($info['target']) ? $info['target'] : '_self';
                            $active = $this_current_page == $token ? true : false;
                            $before = !empty($info['before']) ? $info['before'] : '';
                            $after = !empty($info['after']) ? $info['after'] : '';
                            $sub_menu_links = !empty($info['subs']) ? $info['subs'] : array();

                            // Define menu item and link classes for styling
                            $item_class = 'item '.($active ? 'item_active ' : '');
                            $link_class = 'link '.($active ? 'link_active field_type_empty' : '');

                            // If this the COMMUNITY link, dynamically collect update counts
                            if ($token == 'community'){
                                // Generate update count markup for this page's after string
                                $after .= '';
                                if (!empty($temp_new_threads)){ $after .= '<sup class="sup field_type field_type_electric" title="'.count($temp_new_threads).' New Comments">'.count($temp_new_threads).'</sup>'; }
                                if (!empty($temp_viewing_community)){ $after .= '<sup class="sup field_type field_type_nature" title="'.count($temp_viewing_community).' Members Viewing" style="'.(!empty($temp_new_threads) ? 'margin-left: -3px;' : '').'">'.count($temp_viewing_community).'</sup>'; }
                            }

                            // Print out the menu item markup
                            ?>
                            <li class="<?= $item_class ?>">
                                <a href="<?= $url ?>" class="<?= $link_class ?>" target="<?= $target ?>">
                                    <?= $before ?><span><?= $name ?></span><?= $after ?>
                                </a>
                                <?

                                // If this the COMMUNITY link, dynamically collect sub-pages
                                if ($token == 'community'){
                                    // Loop through the community index and print out links
                                    $this_categories_index = mmrpg_website_community_index();
                                    if (!empty($this_categories_index)){
                                        foreach ($this_categories_index AS $temp_token => $temp_category){
                                            $temp_id = $temp_category['category_id'];
                                            if (($temp_id == 0) && $this_userid == MMRPG_SETTINGS_GUEST_ID){ continue; }
                                            if (($temp_token == 'personal' || $temp_token == 'chat') && empty($this_userinfo['user_flag_postprivate'])){ continue; }
                                            $temp_update_count = !empty($temp_new_threads_categories[$temp_id]) ? $temp_new_threads_categories[$temp_id] : 0;
                                            $temp_viewing_list = $temp_token != 'personal' ? mmrpg_website_sessions_active('community/'.$temp_category['category_token'].'/', 3, true) : array();
                                            if ($temp_token == 'chat' && !empty($chat_online)){ $temp_viewing_list = $chat_online; }
                                            $temp_viewing_count = !empty($temp_viewing_list) ? count($temp_viewing_list) : 0;
                                            $after = '';
                                            if ($temp_update_count > 0){ $after .= '<sup class="sup field_type field_type_electric" title="'.($temp_update_count == 1 ? '1 Updated Thread' : $temp_update_count.' Updated Threads').'">'.$temp_update_count.'</sup>'; }
                                            if ($temp_viewing_count > 0){ $after .= '<sup class="sup field_type field_type_nature" title="'.($temp_viewing_count == 1 ? '1 Member Viewing' : $temp_viewing_count.' Members Viewing').'" style="'.($temp_viewing_count > 0 ? 'margin-left: -3px;' : '').'">'.$temp_viewing_count.'</sup>'; }
                                            $sub_link = array('name' => ucfirst($temp_token), 'after' => $after);
                                            $sub_menu_links[$temp_token] = $sub_link;
                                        }
                                    }
                                }

                                // If there were sub-pages to display, loop through and generate markup
                                if (!empty($sub_menu_links)){
                                    $base_url = $url;
                                    $base_active = $active;
                                    ?>
                                    <ul class="subs field_type field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                                        <?
                                        foreach ($sub_menu_links AS $token => $info){

                                            // Collect basic info about this link
                                            $name = !empty($info['name']) ? $info['name'] : ucfirst($token);
                                            $url = !empty($info['url']) ? MMRPG_CONFIG_ROOTURL.ltrim($info['url'], '/') : $base_url.$token.'/';
                                            $target = !empty($info['target']) ? $info['target'] : '_self';
                                            $before = !empty($info['before']) ? $info['before'] : '';
                                            $after = !empty($info['after']) ? $info['after'] : '';
                                            $active = $this_current_sub == $token ? true : false;

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
            <? } else { ?>
                &hellip;&gt;_&lt;&hellip;
            <? } ?>
        </div>

        <div class="page page_<?= $this_current_page ?>">
            <h1 class="header"><span class="header_wrapper"><?= $this_markup_header ?></span></h1>
            <span style="display: none;"><?= !empty($this_markup_counter) ? $this_markup_counter."\n" : '' ?></span>
            <div class="body"><div class="body_wrapper"><?= $this_markup_body ?></div></div>
            <?= false ? '<pre>'.print_r($_GET, true).'</pre>' : '' ?>
        </div>

    </div>
    <div id="credits">
        Mega Man and all related names and characters are &copy; <a href="http://www.capcom.com/" target="_blank" rel="nofollow">Capcom</a> 1986 - <?= date('Y') ?>.<br />
        This game is fan-made by <a href="https://plus.google.com/113336469005774860291?rel=author" target="_blank">Adrian Marceau</a>, not affiliated or endorsed by Capcom at all, and is in no way official. Any and all <a href="contact/">feedback</a> is appreciated. :)
    </div>
    <script type="text/javascript" src="scripts/jquery.js"></script>
    <script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/index.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script async defer id="github-bjs" src="https://buttons.github.io/buttons.js"></script>
    <? if($this_current_page == 'home' || $this_current_page == 'gallery'): ?>
        <script type="text/javascript" src="_ext/colorbox/jquery.colorbox.js"></script>
    <? endif; ?>
    <script type="text/javascript">
    // Define the key client variables
    gameSettings.baseHref = '<?= MMRPG_CONFIG_ROOTURL ?>';
    gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
    gameSettings.cacheTime = '<?=MMRPG_CONFIG_CACHE_DATE?>';
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
    <?
    // Require the remote bottom in case we're in viewer mode
    require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php');
    ?>
</body>
</html>
<?
// If we're NOT in demo mode, automatically update the date-accessed for their database entry
if (empty($_SESSION['GAME']['DEMO'])){
    $temp_query = 'UPDATE mmrpg_users SET user_date_accessed = '.time().' WHERE user_id = '.$_SESSION['GAME']['USER']['userid'];
    $temp_result = $db->query($temp_query);
}
?>