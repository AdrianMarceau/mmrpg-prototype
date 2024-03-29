<?

// Pull in the player index so we can use later in the script
if (!isset($mmrpg_index_players) || empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }

// Define the avatar class and path variables
$temp_display_name = !empty($this_playerinfo['user_name_public']) && !empty($this_playerinfo['user_flag_postpublic']) ? $this_playerinfo['user_name_public'] : $this_playerinfo['user_name'];
$temp_display_points = $this_playerinfo['board_points'];
$temp_display_zenny = !empty($this_playerinfo['save_counters']['battle_zenny']) ? $this_playerinfo['save_counters']['battle_zenny'] : 0;
$temp_display_text = !empty($this_playerinfo['user_profile_text']) && !empty($this_playerinfo['user_flag_postpublic']) ? $this_playerinfo['user_profile_text'] : '';
$temp_avatar_path = !empty($this_playerinfo['user_image_path']) ? $this_playerinfo['user_image_path'] : 'robots/mega-man/40';
$temp_background_path = !empty($this_playerinfo['user_background_path']) ? $this_playerinfo['user_background_path'] : 'fields/'.rpg_player::get_intro_field();
$temp_is_contributor = in_array($this_playerinfo['role_token'], array('developer', 'administrator', 'moderator', 'contributor')) ? true : false;
if ($temp_is_contributor){
    $temp_item_class = 'sprite sprite_80x80 sprite_80x80_00';
    $temp_item_path = 'images/items/'.(!empty($this_playerinfo['role_icon']) ? $this_playerinfo['role_icon'] : 'energy-pellet' ).'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
    $temp_item_title = !empty($this_playerinfo['role_name']) ? $this_playerinfo['role_name'] : 'Contributor';
}
$temp_last_login = !empty($this_playerinfo['user_date_accessed']) ? $this_playerinfo['user_date_accessed'] : $this_playerinfo['user_date_created'];
$temp_last_login_diff = time() - $temp_last_login;
$temp_display_created = !empty($this_playerinfo['user_date_created']) ? $this_playerinfo['user_date_created'] : time();
list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_base_size) = explode('/', $temp_avatar_path);
list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
$temp_avatar_frame = str_pad(mt_rand(0, 2), 2, '0', STR_PAD_LEFT);
$temp_avatar_size = $temp_avatar_base_size * 2;
$temp_avatar_class = 'avatar avatar_80x80 float float_right ';
$temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
$temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_left_'.$temp_avatar_base_size.'x'.$temp_avatar_base_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
$temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;
if ($this_playerinfo['user_gender'] == 'male'){ $temp_gender_pronoun = 'his'; }
elseif ($this_playerinfo['user_gender'] == 'female'){ $temp_gender_pronoun = 'her'; }
else { $temp_gender_pronoun = 'their'; }
//$temp_display_active = $temp_display_points > 0 && $temp_last_login_diff < MMRPG_SETTINGS_ACTIVE_TIMEOUT ? true : false;
$temp_display_active = 'a player';
if ($temp_display_points <= 0 && $temp_last_login_diff >= MMRPG_SETTINGS_ACTIVE_TIMEOUT){ $temp_display_active = 'a forgotten player'; }
elseif ($temp_display_points <= 0 && $temp_last_login_diff < MMRPG_SETTINGS_ACTIVE_TIMEOUT){ $temp_display_active = 'a new player'; }
elseif ($temp_display_points > 0 && $temp_last_login_diff >= MMRPG_SETTINGS_LEGACY_TIMEOUT){ $temp_display_active = 'a legacy player'; }
elseif ($temp_display_points > 0 && $temp_last_login_diff >= MMRPG_SETTINGS_ACTIVE_TIMEOUT){ $temp_display_active = 'an inactive player'; }
elseif ($temp_display_points > 0 && $temp_last_login_diff < MMRPG_SETTINGS_ACTIVE_TIMEOUT){ $temp_display_active = 'an active player'; }

// Determine the header colour(s) to show for this leaderboard page
$temp_header_colour_token = !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none';
if (!empty($this_playerinfo['user_colour_token2'])){ $temp_header_colour_token .= '_'.$this_playerinfo['user_colour_token2']; }

// Determine the subheader colour(s) to show for this leaderboard page
$temp_subheader_colour_token = !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none';
$temp_subheader_colour_token2 = !empty($this_playerinfo['user_colour_token2']) ? $this_playerinfo['user_colour_token2'] : $temp_subheader_colour_token;

// Collect the player and robot index for later use
$mmrpg_index_players = rpg_player::get_index();
$mmrpg_index_robots = rpg_robot::get_index();

// Generate an array of unlocked robot data for this user
$user_robot_data = array();
if (!empty($this_playerinfo['save_values_battle_rewards'])
    && !empty($this_playerinfo['save_values_battle_settings'])){
    foreach ($mmrpg_index_robots AS $robot_token => $robot_info){
        if (empty($robot_info['robot_flag_published']) || empty($robot_info['robot_flag_unlockable'])){ continue; }
        $robot_data = array();
        foreach ($mmrpg_index_players AS $player_token => $player_info){
            $rewards = $settings = array();
            $flags = $values = $counters = array();
            if (!empty($this_playerinfo['save_values_battle_rewards'][$player_token]['player_robots'][$robot_token])){
                $rewards = $this_playerinfo['save_values_battle_rewards'][$player_token]['player_robots'][$robot_token];
            }
            if (!empty($this_playerinfo['save_values_battle_settings'][$player_token]['player_robots'][$robot_token])){
                $settings = $this_playerinfo['save_values_battle_settings'][$player_token]['player_robots'][$robot_token];
            }
            if (isset($rewards['flags'])){ $flags = array_merge($flags, $rewards['flags']); }
            if (isset($settings['flags'])){ $flags = array_merge($flags, $settings['flags']); }
            if (isset($rewards['values'])){ $values = array_merge($values, $rewards['values']); }
            if (isset($settings['values'])){ $values = array_merge($values, $settings['values']); }
            if (isset($rewards['counters'])){ $counters = array_merge($counters, $rewards['counters']); }
            if (isset($settings['counters'])){ $counters = array_merge($counters, $settings['counters']); }
            $robot_data = array_merge($robot_data, $rewards, $settings);
            $robot_data = array_merge($robot_data, array('flags' => $flags, 'values' => $values, 'counters' => $counters));
        }
        if (!empty($robot_data)){ $user_robot_data[$robot_token] = $robot_data; }
    }
}
//error_log('$user_robot_data = '.print_r($user_robot_data, true));

// Generate an array of robot masters with their summon counters for the most-used section
$most_used_robot_masters = array();
$most_used_robot_masters_text = '';
if (!empty($this_playerinfo['save_values_robot_database'])){
    $most_used_robot_masters = array_map(function($r){
        return !empty($r['robot_summoned']) ? $r['robot_summoned'] : 0;
        }, $this_playerinfo['save_values_robot_database']);
    $most_used_robot_masters = array_filter($most_used_robot_masters, function($e, $t) use($mmrpg_index_robots){
        if (empty($e) || empty($mmrpg_index_robots[$t])){ return false; }
        $r = $mmrpg_index_robots[$t];
        if (empty($r['robot_flag_published']) || empty($r['robot_flag_unlockable'])){ return false; }
        if ($r['robot_class'] !== 'master'){ return false; }
        return true;
        }, ARRAY_FILTER_USE_BOTH);
    asort($most_used_robot_masters);
    $most_used_robot_masters = array_reverse($most_used_robot_masters);
}
if (!empty($most_used_robot_masters)){
    $temp_robot_names = array_map(function($r) use($mmrpg_index_robots){ return $mmrpg_index_robots[$r]['robot_name']; }, array_keys($most_used_robot_masters));
    if (count($temp_robot_names) > 5){
        $temp_robot_names = array_slice($temp_robot_names, 0, 5);
        $most_used_robot_masters_text .= 'top five ';
    }
    $temp_robot_names = array_map(function($r){ return '<strong>'.$r.'</strong>'; }, $temp_robot_names);
    $most_used_robot_masters_text .= 'most-used robot master'.(count($most_used_robot_masters) !== 1 ? 's are' : ' is').' ';
    $most_used_robot_masters_text .= implode_with_oxford_comma($temp_robot_names);
}
//error_log('$most_used_robot_masters = '.print_r($most_used_robot_masters, true));
//error_log('$most_used_robot_masters_text = '.print_r($most_used_robot_masters_text, true));

// Generate an array of favourite robots master if any have been set
$favourite_robot_masters = array();
$favourite_robot_masters_text = '';
if (!empty($this_playerinfo['save_values']['robot_favourites'])){
    $favourite_robot_masters = $this_playerinfo['save_values']['robot_favourites'];
    if (!empty($most_used_robot_masters)){
        usort($favourite_robot_masters, function($r1, $r2) use($most_used_robot_masters){
            $r1e = !empty($most_used_robot_masters[$r1]) ? $most_used_robot_masters[$r1] : 0;
            $r2e = !empty($most_used_robot_masters[$r2]) ? $most_used_robot_masters[$r2] : 0;
            if ($r1e > $r2e){ return -1; }
            elseif ($r1e < $r2e){ return 1; }
            else { return 0; }
            });
    }
}
if (!empty($favourite_robot_masters)){
    $temp_robot_names = array_map(function($r) use($mmrpg_index_robots){ return $mmrpg_index_robots[$r]['robot_name']; }, $favourite_robot_masters);
    if (count($temp_robot_names) > 5){
        $temp_robot_names = array_slice($temp_robot_names, 0, 5);
        $favourite_robot_masters_text .= 'top five ';
    }
    $temp_robot_names = array_map(function($r){ return '<strong>'.$r.'</strong>'; }, $temp_robot_names);
    $favourite_robot_masters_text .= 'favourite robot master'.(count($favourite_robot_masters) !== 1 ? 's are' : ' is').' ';
    $favourite_robot_masters_text .= implode_with_oxford_comma($temp_robot_names);
}
//error_log('$favourite_robot_masters = '.print_r($favourite_robot_masters, true));
//error_log('$favourite_robot_masters_text = '.print_r($favourite_robot_masters_text, true));

// Attempt to generate the user's achievement text for their profile
$user_profile_achievement_text = array();
if (!empty($this_playerinfo['board_awards'])){
    $board_awards = explode(',', $this_playerinfo['board_awards']);
    $campaigns_complete = 0;
    if (in_array('prototype_complete_light', $board_awards)){ $campaigns_complete += 1; }
    if (in_array('prototype_complete_wily', $board_awards)){ $campaigns_complete += 1; }
    if (in_array('prototype_complete_cossack', $board_awards)){ $campaigns_complete += 1; }
    $user_profile_achievement_text[] = 'completed '.$campaigns_complete.' player campaign'.($campaigns_complete !== 1 ? 's' : '');
}
if (!empty($this_playerinfo['board_robots_count'])){
    $count = $this_playerinfo['board_robots_count'];
    $user_profile_achievement_text[] = 'unlocked '.$count.' robot master'.($count !== 1 ? 's' : '');
}
if (!empty($this_playerinfo['board_abilities'])){
    $count = $this_playerinfo['board_abilities'];
    $user_profile_achievement_text[] = 'learned '.$count.' special '.($count !== 1 ? 'abilities' : 'ability');
}
if (!empty($this_playerinfo['board_items'])){
    $count = $this_playerinfo['board_items'];
    $user_profile_achievement_text[] = 'found '.$count.($count > 1 ? ' different' : '').' inventory item'.($count !== 1 ? 's' : '');
}
if (!empty($this_playerinfo['board_stars'])){
    $count = $this_playerinfo['board_stars'];
    $user_profile_achievement_text[] = 'collected '.$count.' elemental star'.($count !== 1 ? 's' : '');
}
if (!empty($user_profile_achievement_text)){
    $user_profile_achievement_text = implode_with_oxford_comma($user_profile_achievement_text);
} else {
    $user_profile_achievement_text = '';
}

// Attempt to generate favourite and most-used text for this player's robots
$user_profile_robot_text = array();
if (!empty($favourite_robot_masters_text)){ $user_profile_robot_text[] = $favourite_robot_masters_text; }
if (!empty($most_used_robot_masters_text)){ $user_profile_robot_text[] = $most_used_robot_masters_text; }
if (!empty($user_profile_robot_text)){
    $user_profile_robot_text = implode(' and '.$temp_gender_pronoun.' ', $user_profile_robot_text);
    $user_profile_robot_text = '<strong>'.$temp_display_name.'</strong>\'s '.$user_profile_robot_text.'.'.PHP_EOL;
} else {
    $user_profile_robot_text = '';
}

// Now let's put it all together into one big blog of player profile text
$user_profile_text_complete = '';
$user_profile_text_complete .= '<strong>'.$temp_display_name.'</strong> is '.($temp_is_contributor ? 'a contributor and ' : '').$temp_display_active.' of the <strong>Mega Man RPG Prototype</strong> with a current battle point total of <strong>'.number_format($temp_display_points).'</strong>'.($temp_display_zenny > 0 ? ' and a zenny total of <strong>'.number_format($temp_display_zenny).'</strong>' : '').'.  ';
$user_profile_text_complete .= '<strong>'.$temp_display_name.'</strong> created '.$temp_gender_pronoun.' account on '.($temp_display_created <= 1357016400 ? 'or before ' : '').date('F jS, Y', $temp_display_created).(!empty($user_profile_achievement_text) ? ' and has since '.$user_profile_achievement_text : '').'.  ';
if ($user_profile_robot_text){ $user_profile_text_complete .= $user_profile_robot_text.'  '; }

// Require the leaderboard data file
define('MMRPG_SKIP_MARKUP', true);
define('MMRPG_SHOW_MARKUP_'.$this_playerinfo['user_id'], true);
require(MMRPG_CONFIG_ROOTDIR.'includes/leaderboard.php');

// Define whether or not the players or star tabs should be open
$temp_remote_session = $this_playerinfo['user_id'] != $_SESSION['GAME']['USER']['userid'] ? true : false;
$temp_show_players = true;
$temp_show_items = !empty($this_playerinfo['save_values_battle_items']) ? true : false;
$temp_show_stars = !empty($this_playerinfo['save_values_battle_stars']) ? true : false;
$temp_show_threads = !empty($this_playerinfo['thread_count']) ? true : false;
$temp_show_posts = !empty($this_playerinfo['post_count']) ? true : false;

// Define the SEO variables for this page
$this_seo_title = $temp_display_name.' | '.$this_seo_title;
$this_seo_description = preg_replace('/\s+/', ' ', strip_tags($user_profile_text_complete)).' '.$this_seo_description;

// Define the Open Graph variables for this page
$this_graph_data['title'] = $temp_display_name.' | '.$this_graph_data['title'];
$this_graph_data['description'] = preg_replace('/\s+/', ' ', strip_tags($user_profile_text_complete)).' '.$this_graph_data['description'];

// Update the GET variables with the current page num
$this_num_offset = $this_current_num - 1;
$_GET['start'] = 0 + ($this_num_offset * 50);
$_GET['limit'] = 50 + ($this_num_offset * 50);

// Update the MARKUP variables for this page
$this_markup_counter = '<span class="count count_header">( '.(!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : '0 Players').($this_leaderboard_online_count > 0 ? ' <span style="opacity: 0.25;">|</span> <span style="text-shadow: 0 0 5px lime;">'.$this_leaderboard_online_count.' Online</span>' : '').' )</span>';

// Parse the pseudo-code tag <!-- MMRPG_LEADERBOARD_PLAYER_MARKUP -->
$find = '<!-- MMRPG_LEADERBOARD_PLAYER_MARKUP -->';
if (true || strstr($page_content_parsed, $find)){
    ob_start();
    ?>
        <div class="leaderboard" style="overflow: visible; ">
            <div class="wrapper" style="margin: 2px 0 4px; overflow: visible;">
            <?

            // Print out the generated leaderboard markup
            //echo $this_leaderboard_markup;
            //die('<pre>'.print_r($this_leaderboard_markup, true).'</pre>');
            if (!empty($this_leaderboard_markup)){

                // COLLECT DATA

                // Start the output buffer and start looping
                ob_start();
                foreach ($this_leaderboard_markup AS $key => $leaderboard_markup){
                    // Display this save file's markup if allowed
                    if (strstr($leaderboard_markup, 'data-player="'.$this_playerinfo['user_name_clean'].'"')){
                        $leaderboard_markup = preg_replace('/<span class="username">([^<>]+)?<\/span>/', '<h2 class="username">$1</h2>', $leaderboard_markup);
                        $leaderboard_markup = preg_replace('/href="([^<>]+)"/', '', $leaderboard_markup);
                        echo $leaderboard_markup;
                        break;
                    } else {
                        continue;
                    }
                }
                // Collect the page listing markup
                $pagelisting_markup = trim(ob_get_clean());

                // MAIN LEADEBOARD AREA

                // Print out the opening tag for the container dig
                echo '<div class="container container_numbers" style="text-align: center; margin: 0; ">';
                // Display the pregenerated pagelisting data
                echo $pagelisting_markup;
                // Print out the closing container div
                echo '</div>';

            }

            ?>

            </div>
        </div>

        <div class="community leaderboard">
            <div class="subbody thread_subbody thread_subbody_full thread_subbody_full_right thread_right" style="text-align: left; position: relative; padding-bottom: 0; margin-bottom: 4px;">
                <div class="<?= $temp_avatar_class ?> avatar_fieldback player_type_<?= $temp_header_colour_token ?>" style="border-width: 1px; margin-top: 0; margin-right: 0;">
                    <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_preview.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 80px 80px;">
                        &nbsp;
                    </div>
                </div>
                <div class="<?= $temp_avatar_class ?> avatar_userimage" style="margin-top: 0;"  data-click-tooltip="<?= isset($temp_item_title) ? $temp_item_title : 'Member' ?>" data-tooltip-type="type player_type_<?= $temp_header_colour_token ?>">
                    <? if($temp_is_contributor): ?><div class="<?= $temp_item_class ?>" style="background-image: url(<?= $temp_item_path ?>); background-size: auto 80px; position: absolute; top: -22px; right: -30px;" alt="<?= $temp_item_title ?>"><?= $temp_item_title ?></div><? endif; ?>
                    <div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>); background-size: auto <?= $temp_avatar_size ?>px;"><?= $temp_display_name ?></div>
                </div>
                <div class="bodytext">
                    <p class="text" style="color: rgb(157, 220, 255);">
                        <?= $user_profile_text_complete ?>
                    </p>
                    <?
                    // Define a quick function for formatting a legacy ranking span
                    function format_legacy_rank($legacy_rank){
                        if ($legacy_rank === '1st'){ $legacy_rank = '<span class="type_span field_type type_electric" style="padding: 0 6px; color: #ffffff;">'.$legacy_rank.'</span>'; }
                        elseif ($legacy_rank === '2nd'){ $legacy_rank = '<span class="type_span field_type type_cutter" style="padding: 0 6px; color: #ffffff;">'.$legacy_rank.'</span>'; }
                        elseif ($legacy_rank === '3rd'){ $legacy_rank = '<span class="type_span field_type type_earth" style="padding: 0 6px; color: #ffffff;">'.$legacy_rank.'</span>'; }
                        else { $legacy_rank = '<span class="type_span field_type type_none" style="padding: 0 6px; color: #ffffff;">'.$legacy_rank.'</span>'; }
                        return $legacy_rank;
                    }
                    // Print out legacy rankings from 2016 if they exist
                    if (!empty($this_playerinfo['board_points_legacy'])){
                        ?>
                        <p class="text" style="color: rgb(157, 220, 255); border-top: 1px solid rgba(0, 0, 0, 0.3); padding-top: 5px; margin-top: 10px;">
                            Prior to the battle point reboot of 2016,
                            <strong><?= $temp_display_name ?></strong> had amassed a grand total of
                            <strong><?= number_format($this_playerinfo['board_points_legacy'], 0, '.', ',') ?></strong> battle points and reached
                            <?= format_legacy_rank(mmrpg_number_suffix(mmrpg_prototype_leaderboard_rank_legacy($this_playerinfo['user_id'], 2016))) ?> place.
                        </p>
                        <?
                    }
                    // Print out legacy rankings from 2019 if they exist
                    if (!empty($this_playerinfo['board_points_legacy2'])){
                        ?>
                        <p class="text" style="color: rgb(157, 220, 255); border-top: 1px solid rgba(0, 0, 0, 0.3); padding-top: 5px; margin-top: 10px; padding-bottom: 5px;">
                            Prior to the battle point reboot of 2019,
                            <strong><?= $temp_display_name ?></strong> had amassed a grand total of
                            <strong><?= number_format($this_playerinfo['board_points_legacy2'], 0, '.', ',') ?></strong> battle points and reached
                            <?= format_legacy_rank(mmrpg_number_suffix(mmrpg_prototype_leaderboard_rank_legacy($this_playerinfo['user_id'], 2019))) ?> place.
                        </p>
                        <?
                    }
                    ?>
                </div>
                <div class="bodytext community_stats" style="clear: both; border-top: 1px solid rgba(0, 0, 0, 0.3); padding-top: 10px;">
                    <div class="text player_stats">
                        <strong class="label">Community Forum Stats</strong>
                        <ul class="records">
                            <li class="stat"><span class="counter thread_counter" style=""><?= $this_playerinfo['thread_count'] == 1 ? '1 Thread' : $this_playerinfo['thread_count'].' Threads' ?></span></li>
                            <li class="stat"><span class="counter post_counter"><?= $this_playerinfo['post_count'] == 1 ? '1 Post' : $this_playerinfo['post_count'].' Posts' ?></span></li>
                            <?/*<li class="stat"><span class="counter like_counter"><?= $this_playerinfo['like_count'] == 1 ? '1 Like' : $this_playerinfo['like_count'].' Likes' ?></span></li>*/?>
                            <? $this_playerinfo['comment_count'] = $this_playerinfo['post_count'] + $this_playerinfo['thread_count']; ?>
                            <? $this_playerinfo['comment_rating'] = round(($this_playerinfo['post_count'] * 2) - ($this_playerinfo['thread_count'] / 2)); ?>
                            <? if($this_playerinfo['comment_rating'] != 0): ?>
                                <li class="stat"><span class="counter rating_counter"><?= ($this_playerinfo['comment_rating'] > 0 ? '+' : '-').$this_playerinfo['comment_rating'] ?> Rating</span></li>
                            <? else: ?>
                                <li class="stat"><span class="counter rating_counter">0 Rating</span></li>
                            <? endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <h2 class="subheader field_type_<?= $temp_header_colour_token ?>" style="margin: 10px 0 4px; text-align: left;">
                <?=$temp_display_name?>&#39;s Leaderboard
            </h2>

            <div id="game_container" class="subbody thread_subbody thread_subbody_full thread_subbody_full_right thread_right event event_triple event_visible <?= in_array($this_current_token, array('robots', 'players', 'database', 'items', 'stars')) ? 'has_iframe' : '' ?>" style="text-align: left; position: relative; padding-bottom: 6px; margin-bottom: 4px;">

                <div id="game_buttons" data-fieldtype="<?= $temp_header_colour_token ?>" class="field">

                    <div class="row top">

                        <a class="link_button profile field_type field_type_<?= $temp_subheader_colour_token ?> <?= empty($this_current_token) ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/' ?>">View Profile</a>

                        <? if(!empty($temp_show_threads)): ?>
                            <a class="link_button threads field_type field_type_<?= $temp_subheader_colour_token ?> <?= $this_current_token == 'threads' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'community/search/&player='.$this_playerinfo['user_name_clean'].'&player_strict=true&display=threads&limit=threads' ?>">View Threads</a>
                        <? endif; ?>

                        <? if(!empty($temp_show_posts)): ?>
                            <a class="link_button posts field_type field_type_<?= $temp_subheader_colour_token ?> <?= $this_current_token == 'posts' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'community/search/&player='.$this_playerinfo['user_name_clean'].'&player_strict=true&display=posts&limit=posts' ?>">View Posts</a>
                        <? endif; ?>

                        <? if (!rpg_user::is_guest() && $this_userid != $this_playerinfo['user_id'] && !empty($this_userinfo['user_flag_postprivate'])): ?>
                            <a class="link_button message field_type field_type_<?= $temp_subheader_colour_token ?>" href="community/personal/0/new/<?= $this_playerinfo['user_name_clean'] ?>/">Send Message</a>
                        <? endif; ?>

                         <? if (!empty($this_playerinfo['user_website_address'])
                            && preg_match('/^https?\:\/\//i', $this_playerinfo['user_website_address'])): ?>
                            <a class="link_button field_type field_type_<?= $temp_subheader_colour_token ?>" href="<?= $this_playerinfo['user_website_address'] ?>" target="_blank">Visit Website <i class="fas fa fa-external-link-alt"></i></a>
                        <? endif; ?>

                    </div>

                    <div class="row bottom">

                        <? if(!empty($temp_display_points)): ?>
                            <a class="link_button points field_type field_type_<?= $temp_subheader_colour_token2 ?> <?= $this_current_token == 'points' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/points/' ?>">Points</a>
                        <? endif; ?>

                        <a class="link_button robots field_type field_type_<?= $temp_subheader_colour_token2 ?> <?= $this_current_token == 'robots' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/robots/' ?>">Robots</a>

                        <? if(!empty($temp_show_players)): ?>
                            <a class="link_button players field_type field_type_<?= $temp_subheader_colour_token2 ?> <?= $this_current_token == 'players' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/players/' ?>">Players</a>
                        <? endif; ?>

                        <a class="link_button database field_type field_type_<?= $temp_subheader_colour_token2 ?> <?= $this_current_token == 'database' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/database/' ?>">Database</a>

                        <? if(!empty($temp_show_items)): ?>
                            <a class="link_button items field_type field_type_<?= $temp_subheader_colour_token2 ?> <?= $this_current_token == 'items' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/items/' ?>">Items</a>
                        <? endif; ?>
                        <? if(!empty($temp_show_stars)): ?>
                            <a class="link_button stars field_type field_type_<?= $temp_subheader_colour_token2 ?> <?= $this_current_token == 'stars' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/stars/' ?>">Stars</a>
                        <? endif; ?>

                    </div>

                </div>

                <?

                // -- LEADERBOARD PAGES -- //

                // Define the allowable pages
                $temp_allowed_pages = array('robots', 'players', 'database', 'stars', 'items', 'points');

                // If this is the View Profile page, show the appropriate content
                if (empty($this_current_token) || !in_array($this_current_token, $temp_allowed_pages)){
                    ?>

                        <div class="bodytext" style="margin-top: 15px;">
                            <? if(!empty($temp_display_text)): ?>
                                <?= str_replace('<p>', '<p class="text">', mmrpg_formatting_decode($temp_display_text))."\n" ?>
                            <? else: ?>
                                <p class="text" style="color: #505050; padding: 2px;">- no profile data -</p>
                            <? endif; ?>
                        </div>

                    <?
                }
                // Else if this is the View Robots page, show the appropriate content
                elseif ($this_current_token == 'robots'){
                    ?>

                    <div id="game_frames" class="field view_robots">
                        <iframe name="view_robots" src="frames/edit_robots.php?source=website&amp;action=robots&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>

                    <?
                }
                // Else if this is the View Players page, show the appropriate content
                elseif ($this_current_token == 'players'){
                    ?>

                    <div id="game_frames" class="field view_players">
                        <iframe name="view_players" src="frames/edit_players.php?source=website&amp;action=players&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>

                    <?
                }
                // Else if this is the View Items page, show the appropriate content
                elseif ($this_current_token == 'items'){
                    ?>

                    <div id="game_frames" class="field view_items">
                        <iframe name="view_items" src="frames/items.php?source=website&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>

                    <?
                }
                // Else if this is the View Stars page, show the appropriate content
                elseif ($this_current_token == 'stars'){
                    ?>

                    <div id="game_frames" class="field view_stars">
                        <iframe name="view_stars" src="frames/starforce.php?source=website&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>

                    <?
                }
                // Else if this is the View Database page, show the appropriate content
                elseif ($this_current_token == 'database'){
                    ?>

                    <div id="game_frames" class="field view_database">
                        <iframe name="view_database" src="frames/database.php?source=website&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>

                    <?
                }
                // Else if this is the View Points page, show the appropriate content
                elseif ($this_current_token == 'points'){

                    // Collect reference indexes for players, robots, abilities, items, and fields
                    $mmrpg_index_players = rpg_player::get_index();
                    $mmrpg_index_robots = rpg_robot::get_index();
                    $mmrpg_index_abilities = rpg_ability::get_index();
                    $mmrpg_index_items = rpg_item::get_index();
                    $mmrpg_index_fields = rpg_field::get_index();
                    $mmrpg_index_players_tokens = array_keys($mmrpg_index_players);
                    $mmrpg_index_robots_tokens = array_keys($mmrpg_index_robots);
                    $mmrpg_index_abilities_tokens = array_keys($mmrpg_index_abilities);
                    $mmrpg_index_items_tokens = array_keys($mmrpg_index_items);
                    $mmrpg_index_fields_tokens = array_keys($mmrpg_index_fields);

                    // Create an index of field name parts matched to their relative types
                    $mmrpg_index_fields_types = array();
                    foreach ($mmrpg_index_fields AS $token => $info){
                        if (empty($info['field_type'])){ continue; }
                        list($token1, $token2) = explode('-', $token);
                        $mmrpg_index_fields_types[$token1] = $info['field_type'];
                        $mmrpg_index_fields_types[$token2] = $info['field_type'];
                    }

                    // Collect a detailed points breakdown for this user given their ID
                    $battle_points_index = array();
                    mmrpg_prototype_calculate_battle_points_2k19($this_playerinfo['user_id'], $battle_points_index);

                    // Collect a list of point headers for display in the table rows below
                    $battle_points_categories = array_values(array_filter(array_keys($battle_points_index), function($c){ return !strstr($c, '_points'); }));

                    ?>

                    <div class="field view_points">

                        <table class="points_table">
                            <colgroup>
                                <col width="" />
                                <col width="100" />
                                <col width="150" />
                            </colgroup>
                            <thead>
                                <tr>
                                    <th colspan="3">
                                        <h3 class="table_head field_type field_type_<?= $temp_header_colour_token ?>">
                                            <span>Battle Points Overview</span>
                                        </h3>
                                        <a class="toggle"><span>+ / -</span></a>
                                    </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="3">
                                        <h4 class="table_foot field_type field_type_<?= $temp_header_colour_token ?>">
                                            <span>
                                                <span class="label">Total Battle Points</span>
                                                <span class="total"><?= number_format($battle_points_index['total_battle_points'], 0, '.', ',') ?> BP</span>
                                            </span>
                                        </h4>
                                    </td>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?
                                // Loop through the different point categories and display them
                                $display_key = 0;
                                foreach ($battle_points_categories AS $key => $category_token){

                                    // Define the category name using the token as reference
                                    $category_nth = $display_key % 2 === 0 ? 'even' : 'odd';
                                    $category_name = ucwords(str_replace('_', ' ', $category_token));
                                    $category_name = str_replace('Robots Unlocked ', 'Robots w/ ', $category_name);
                                    $category_name = str_replace('Database Robots ', 'Robots ', $category_name);
                                    $category_name = str_replace('Players Defeated', 'Player Battle Tokens', $category_name);
                                    $category_name = str_replace('Challenges Completed', 'Challenge Mode Victories', $category_name);
                                    $category_name = str_replace('Endless Waves Completed', 'Endless Attack Waves', $category_name);
                                    $category_list = $battle_points_index[$category_token];
                                    $category_count = is_array($category_list) ? count($category_list) : (int)($category_list);
                                    $category_points = $battle_points_index[$category_token.'_points'];

                                    // If this category has nothing, we should just skip it
                                    if (empty($category_count)){ continue; }

                                    // If the category list isn't empty and is an array, we need to loop for details
                                    if (!empty($category_list)
                                        && is_array($category_list)
                                        && !in_array($category_token, array('endless_waves_completed'))
                                        ){

                                        // Pre-sort certain lists by their index orders
                                        $sort_index_tokens = false;
                                        if (strstr($category_token, 'doctors_unlocked')){
                                            $sort_index_tokens = $mmrpg_index_players_tokens;
                                        } elseif (strstr($category_token, 'robots_unlocked')){
                                            $sort_index_tokens = $mmrpg_index_robots_tokens;
                                        } elseif (strstr($category_token, 'abilities_unlocked')){
                                            $sort_index_tokens = $mmrpg_index_abilities_tokens;
                                        } elseif (strstr($category_token, 'items_unlocked')){
                                            $sort_index_tokens = $mmrpg_index_items_tokens;
                                        } elseif (strstr($category_token, 'fields_unlocked')){
                                            $sort_index_tokens = $mmrpg_index_items_tokens;
                                        } elseif (strstr($category_token, 'stars_collected')){
                                            $sort_index_tokens = array_keys($mmrpg_index_fields_types);
                                        }

                                        // If a sort token was defined, run the sort algorithm
                                        if (!empty($sort_index_tokens)){
                                            usort($category_list, function($a, $b) use($category_token){
                                                global $sort_index_tokens;
                                                if (strstr($category_token, 'stars_collected')){
                                                    list($a1, $a2) = explode('-', $a);
                                                    list($b1, $b2) = explode('-', $b);
                                                    $apos1 = array_search($a1, $sort_index_tokens);
                                                    $bpos1 = array_search($b1, $sort_index_tokens);
                                                    $apos2 = array_search($a2, $sort_index_tokens);
                                                    $bpos2 = array_search($b2, $sort_index_tokens);
                                                    if ($apos1 < $bpos1){ return -1; }
                                                    elseif ($apos1 > $bpos1){ return 1; }
                                                    else {
                                                        if ($apos2 < $bpos2){ return -1; }
                                                        elseif ($apos2 > $bpos2){ return 1; }
                                                        else { return 0; }
                                                    }
                                                } else {
                                                    if (strstr($a, ' x')){ list($a) = explode(' x', $a); }
                                                    if (strstr($b, ' x')){ list($b) = explode(' x', $b); }
                                                    $apos = array_search($a, $sort_index_tokens);
                                                    $bpos = array_search($b, $sort_index_tokens);
                                                    if ($apos < $bpos){ return -1; }
                                                    elseif ($apos > $bpos){ return 1; }
                                                    else { return 0; }
                                                }
                                            });
                                        }

                                        // If this category has counters within, we need to define the "real" count
                                        $category_real_count = 0;

                                        // Loop through elements in the details list and add to markup array
                                        $details_markup = array();
                                        foreach ($category_list AS $key2 => $data){

                                            // Process the individual items in the category list differently depending on type
                                            if ($category_token === 'doctors_unlocked'){
                                                $token = $data;
                                                $info = $mmrpg_index_players[$token];
                                                $details_markup[] = '<li><a class="player_type type_'.$info['player_type'].'" href="database/players/'.$info['player_token'].'/">'.$info['player_name'].'</a></li>';
                                            } elseif ($category_token === 'chapters_completed'){
                                                list($token, $chapter) = explode('_', $data);
                                                $info = $mmrpg_index_players[$token];
                                                $chapter = ucwords(str_replace('-', ' ', $chapter));
                                                $details_markup[] = '<li><a class="player_type type_'.$info['player_type'].'">'.$info['player_name'].' '.$chapter.'</a></li>';
                                            } elseif ($category_token === 'campaigns_completed'){
                                                $token = $data;
                                                $info = $mmrpg_index_players[$token];
                                                $details_markup[] = '<li><a class="player_type type_'.$info['player_type'].'">'.$info['player_name'].'\'s Story</a></li>';
                                            }  elseif (strstr($category_token, 'robots_unlocked')
                                                || strstr($category_token, 'database_robots')){
                                                if (strstr($data, ' x')){ list($token, $num) = explode(' x', $data); $num = (int)($num); }
                                                else { $token = $data; $num = 1; }
                                                $info = $mmrpg_index_robots[$token];
                                                $type = !empty($info['robot_core']) ? $info['robot_core'] : 'none';
                                                if (!empty($info['robot_core2'])){ $type = ($type !== 'none' ? $type.'_'.$info['robot_core2'] : $info['robot_core2']); }
                                                if ($info['robot_class'] == 'boss'){ $cls = 'bosses'; }
                                                elseif ($info['robot_class'] == 'mecha'){ $cls = 'mechas'; }
                                                else { $cls = 'robots'; }
                                                $details_markup[] = '<li><a class="robot_type type_'.$type.'" href="database/'.$cls.'/'.$info['robot_token'].'/">'.$info['robot_name'].($num !== 1 ? ' x'.$num : '').'</a></li>';
                                            } elseif ($category_token === 'abilities_unlocked'){
                                                $token = $data;
                                                $info = $mmrpg_index_abilities[$token];
                                                $type = !empty($info['ability_type']) ? $info['ability_type'] : 'none';
                                                if (!empty($info['ability_type2'])){ $type = ($type !== 'none' ? $type.'_'.$info['ability_type2'] : $info['ability_type2']); }
                                                $details_markup[] = '<li><a class="ability_type type_'.$type.'" href="database/abilities/'.$info['ability_token'].'/">'.$info['ability_name'].'</a></li>';
                                            } elseif ($category_token === 'items_unlocked'){
                                                if (strstr($data, ' x')){ list($token, $num) = explode(' x', $data); $num = (int)($num); }
                                                else { $token = $data; $num = 1; }
                                                $info = $mmrpg_index_items[$token];
                                                $type = !empty($info['item_type']) ? $info['item_type'] : 'none';
                                                if (!empty($info['item_type2'])){ $type = ($type !== 'none' ? $type.'_'.$info['item_type2'] : $info['item_type2']); }
                                                $details_markup[] = '<li><a class="item_type type_'.$type.'" href="database/items/'.$info['item_token'].'/">'.$info['item_name'].($num !== 1 ? ' x'.$num : '').'</a></li>';
                                            } elseif ($category_token === 'fields_unlocked'){
                                                $token = $data;
                                                $info = $mmrpg_index_fields[$token];
                                                $type = !empty($info['field_type']) ? $info['field_type'] : 'none';
                                                $details_markup[] = '<li><a class="field_type type_'.$type.'" href="database/fields/'.$info['field_token'].'/">'.$info['field_name'].'</a></li>';
                                            } elseif ($category_token === 'field_stars_collected'
                                                || $category_token === 'fusion_stars_collected'){
                                                list($token1, $token2) = explode('-', $data);
                                                $type1 = $mmrpg_index_fields_types[$token1];
                                                $type2 = $mmrpg_index_fields_types[$token2];
                                                $type = !empty($type1) && !empty($type2) && $type1 !== $type2 ? $type1.'_'.$type2 : $type1;
                                                $name = ucwords($token1.' '.$token2);
                                                $details_markup[] = '<li><a class="field_type type_'.$type.'">'.$name.'</a></li>';
                                            } elseif ($category_token === 'players_defeated'){
                                                $type = !empty($data['target_user_colour']) ? $data['target_user_colour'] : 'none';
                                                $details_markup[] = '<li><a class="field_type type_'.$type.'" href="leaderboard/'.$data['target_user_token'].'/">'.$data['target_user_name'].'</a></li>';
                                            } elseif ($category_token === 'challenges_completed'){
                                                $type = $data['challenge_kind'] == 'event' ? 'electric' : 'cutter';
                                                $title = $data['challenge_name'].' //'.
                                                    'Turns: '.$data['challenge_turns_used'].'/'.$data['challenge_turn_limit'].' '.
                                                    '| Robots: '.$data['challenge_robots_used'].'/'.$data['challenge_robot_limit'].' '.
                                                    '| Reward: '.number_format($data['challenge_victory_points'], 0, '.', ',').' BP ('.$data['challenge_victory_percent'].'%) '.
                                                    '// '.$data['challenge_victory_rank'].'-Rank Victory! '.
                                                    '';
                                                $details_markup[] = '<li><a class="field_type type_'.$type.'" data-click-tooltip="'.$title.'" data-href="challenges/'.$data['challenge_id'].'/">'.
                                                    $data['challenge_name'].' ('.$data['challenge_victory_rank'].')'.
                                                    '</a></li>';
                                            } elseif ($category_token === 'endless_waves_completed'){

                                                $details_markup[] = '<li><span class="field_type type_none">$data = '.print_r($data, true).' | $category_list = '.print_r($category_list, true).'</span></li>';

                                            }  else {
                                                $details_markup[] = '<li><span class="field_type type_none">'.print_r($data, true).'</span></li>';
                                            }

                                        }
                                        $details_markup = implode(' ', $details_markup);

                                    }
                                    // Otherwise if this is an endless challenge mission record
                                    elseif ($category_token === 'endless_waves_completed'){

                                        // Ensure challenge waves have actually been completed first, then collect the counter
                                        if (!empty($category_list['challenge_waves_completed'])){ $category_real_count = $category_list['challenge_waves_completed']; }
                                        else { continue; }

                                        // Collect the base values for this battle point calculation
                                        $wave_value = MMRPG_SETTINGS_BATTLEPOINTS_PERWAVE;
                                        $num_waves = (int)($category_list['challenge_waves_completed']);
                                        $num_robots = (int)($category_list['challenge_robots_used']);
                                        $num_turns = (int)($category_list['challenge_turns_used']);
                                        $team_config = $category_list['challenge_team_config'];

                                        $base_points = $num_waves * $wave_value;
                                        $robot_points = ceil($base_points / $num_robots);
                                        $turn_points = ceil($base_points / ($num_turns / $num_waves));
                                        $total_points = $base_points + $robot_points + $turn_points;

                                        $print_wave_value = number_format($wave_value, 0, '.', ',');
                                        $print_num_robots = number_format($num_robots, 0, '.', ',');
                                        $print_num_turns = number_format($num_turns, 0, '.', ',');
                                        $print_base_points = number_format($base_points, 0, '.', ',');
                                        $print_robot_points = number_format($robot_points, 0, '.', ',');
                                        $print_turn_points = number_format($turn_points, 0, '.', ',');
                                        $print_total_points = number_format($total_points, 0, '.', ',');

                                        $team_config_markup = array();
                                        if (!empty($team_config)){
                                            //$team_config_list = json_decode($team_config, true);
                                            list($team_doctor, $team_config_list) = explode('::', $team_config);
                                            $team_config_list = strstr($team_config_list, ',') ? explode(',',$team_config_list) : array($team_config_list);
                                            //$team_config_list = array_reverse($team_config_list, true);
                                            foreach ($team_config_list AS $key => $data){
                                                $data = strstr($data, '@') ? explode('@', $data) : array($data);
                                                $robot_token = $data[0];
                                                $robot_alt_token = '';
                                                if (strstr($robot_token, '_')){ list($robot_token, $robot_alt_token) = explode('_', $robot_token); }
                                                $robot_info = $mmrpg_index_robots[$robot_token];
                                                $robot_frame = $key == 0 ? 'victory' : ($key % 2 != 0 ? 'taunt' : 'base');
                                                $item_token = !empty($data[1]) ? $data[1] : '';
                                                $team_config_markup[] = mmrpg_website_text_float_robot_markup(
                                                    $robot_token,
                                                    'left',
                                                    $robot_frame,
                                                    $robot_info['robot_image_size'],
                                                    $robot_alt_token,
                                                    $item_token,
                                                    'player_type player_type_'.str_replace('dr-', '', $team_doctor)
                                                    );
                                            }

                                        }


                                        $lines = array();
                                        $lines[] = rpg_type::print_span('copy', 'Endless Attack Record: '.$num_waves.' '.($num_waves === 1 ? 'Wave' : 'Waves'));
                                        if (!empty($team_config_markup)){ $lines[] = '<div class="robot_team">'.implode(' ', $team_config_markup).'</div>'; }
                                        $lines[] = '<strong>Base Points</strong>: '.$print_wave_value.' &times; Waves('.$num_waves.') = '.$print_base_points;
                                        $lines[] = '<strong>Robot Points</strong>: BasePoints('.$print_base_points.') &divide; Robots('.$print_num_robots.') = '.$print_robot_points;
                                        $lines[] = '<strong>Turn Points</strong>: BasePoints('.$print_base_points.') &divide; (Turns('.$print_num_turns.') &divide; Waves('.$num_waves.')) = '.$print_turn_points;
                                        $lines[] = '<strong>Total Points</strong>: BasePoints('.$print_base_points.') &plus; RobotPoints('.$print_robot_points.') &plus; TurnPoints('.$print_turn_points.') = '.$print_total_points;
                                        //$lines[] = rpg_type::print_span('copy', 'Endless Attack Reward: '.$print_total_points.' '.($total_points === 1 ? 'Point' : 'Points'));

                                        $details_markup = '<li>'.implode('</li><li>', $lines).'</li>';
                                        $details_markup_class = 'autoheight';

                                    }
                                    // Otherwise we show an empty span with no data
                                    elseif (!empty($category_list) && (is_string($category_list) || is_numeric($category_list))) {
                                        $details_markup = '<li><span>'.$category_list.'</span></li>';
                                    }
                                    // Otherwise we show an empty span with no data
                                    else {
                                        $details_markup = '<li><span class="field_type type_none">- no data -</span></li>';
                                    }

                                    // Display a table row for this categories name and details
                                    if (!isset($details_markup_class)){ $details_markup_class = ''; }
                                    ?>
                                    <tr data-key="<?= $display_key ?>" class="<?= $category_nth ?> <?= $category_token ?> main">
                                        <td class="category"><h5><?= $category_name ?></h5> <a class="toggle"><span>+</span></a></td>
                                        <td class="counter"><div><?= 'x '.(!empty($category_real_count) ? $category_real_count : $category_count) ?></div></td>
                                        <td class="points"><div>+ <?= number_format($category_points, 0, '.', ',') ?> BP</div></td>
                                    </tr>
                                    <tr data-key="<?= $display_key ?>" class="<?= $category_nth ?> <?= $category_token ?> details hidden">
                                        <td class="details" colspan="3"><?= strstr($details_markup, '</li>')
                                            ? '<ul class="'.$details_markup_class.'">'.$details_markup.'</ul>'
                                            : '<div class="'.$details_markup_class.'">'.$details_markup.'</div>'
                                            ?></td>
                                    </tr>
                                    <?
                                    $display_key++;

                                }
                                ?>
                            </tbody>
                        </table>

                        <? /*
                        <hr />
                        <pre>$battle_points_categories = <?= print_r($battle_points_categories, true) ?></pre>
                        <? $debug_battle_points_index = array_map(function($v){ if (is_array($v)){ return json_encode($v); } else { return $v; } }, $battle_points_index); ?>
                        <pre>$debug_battle_points_index = <?= print_r($debug_battle_points_index, true) ?></pre>
                        */ ?>

                    </div>

                    <?
                }
                ?>

            </div>

        </div>

    <?
    $replace = ob_get_clean();
    //$page_content_parsed = str_replace($find, $replace, $page_content_parsed);
    $page_content_parsed = $replace;
}
?>