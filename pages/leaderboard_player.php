<?
/*
 * INDEX PAGE : LEADERBOARD PLAYER
 */

// Define the avatar class and path variables
$temp_display_name = !empty($this_playerinfo['user_name_public']) ? $this_playerinfo['user_name_public'] : $this_playerinfo['user_name'];
$temp_display_points = $this_playerinfo['board_points'];
$temp_display_zenny = !empty($this_playerinfo['save_counters']['battle_zenny']) ? $this_playerinfo['save_counters']['battle_zenny'] : 0;
$temp_display_text = !empty($this_playerinfo['user_profile_text']) ? $this_playerinfo['user_profile_text'] : '';
$temp_avatar_path = !empty($this_playerinfo['user_image_path']) ? $this_playerinfo['user_image_path'] : 'robots/mega-man/40';
$temp_background_path = !empty($this_playerinfo['user_background_path']) ? $this_playerinfo['user_background_path'] : 'fields/intro-field';
$temp_is_contributor = in_array($this_playerinfo['role_token'], array('developer', 'administrator', 'moderator', 'contributor')) ? true : false;
if ($temp_is_contributor){
    $temp_item_class = 'sprite sprite_80x80 sprite_80x80_00';
    $temp_item_path = 'images/items/'.(!empty($this_playerinfo['role_icon']) ? $this_playerinfo['role_icon'] : 'energy-pellet' ).'/icon_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
    $temp_item_title = !empty($this_playerinfo['role_name']) ? $this_playerinfo['role_name'] : 'Contributor';
}
$temp_last_login = !empty($this_playerinfo['user_date_accessed']) ? $this_playerinfo['user_date_accessed'] : $this_playerinfo['user_date_created'];
$temp_last_login_diff = time() - $temp_last_login;
$temp_display_created = !empty($this_playerinfo['user_date_created']) ? $this_playerinfo['user_date_created'] : time();
list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
$temp_avatar_frame = str_pad(mt_rand(0, 2), 2, '0', STR_PAD_LEFT);
$temp_avatar_size = $temp_avatar_size * 2;
$temp_avatar_class = 'avatar avatar_80x80 float float_right ';
$temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
$temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_left_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
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

// Collect the robot index for later use
$hidden_database_robots = array('robot', 'mega-man-ds', 'proto-man-ds', 'bass-ds', 'rock', 'cache', 'bond-man', 'fake-man');
foreach ($hidden_database_robots AS $key => $token){ $hidden_database_robots[$key] = "'".$token."'"; }
$db_robot_fields = rpg_robot::get_index_fields(true);
$temp_robots_index = $db->get_array_list("SELECT {$db_robot_fields} FROM mmrpg_index_robots WHERE robot_flag_complete = 1 AND robot_token NOT IN (".implode(',', $hidden_database_robots).");", 'robot_token');

// Generate this player's record numbers
$temp_counter_players = array('total' => 0);
$temp_counter_points = array('total' => 0);
$temp_counter_missions = array('total_complete' => 0, 'total_failure' => 0);
$temp_counter_robots = array('total' => 0);
$temp_counter_abilities = array('total' => 0);
$temp_counter_stars = array('total' => 0, 'field' => 0, 'fusion' => 0);
$temp_counter_database = array();
$temp_counter_database['total'] = 0;
$temp_counter_database['encountered'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
$temp_counter_database['scanned'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
$temp_counter_database['summoned'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
$temp_counter_database['unlocked'] = array('total' => 0, 'master' => 0, 'mecha' => 0, 'boss' => 0);
$temp_counter_levels = array();
// Loop through the completed battles
if (!empty($this_playerinfo['save_values_battle_complete'])){
    foreach ($this_playerinfo['save_values_battle_complete'] AS $token => $array){
        foreach ($array AS $token2 => $array2){
            $temp_counter_missions[$token] = !isset($temp_counter_missions[$token]) ? 1 : $temp_counter_missions[$token] + 1;
            $temp_counter_missions['total_complete'] = !isset($temp_counter_missions['total_complete']) ? 1 : $temp_counter_missions['total_complete'] + 1;
        }
    }
}
// Loop through the failed battles
if (!empty($this_playerinfo['save_values_battle_failure'])){
    foreach ($this_playerinfo['save_values_battle_failure'] AS $token => $array){
        foreach ($array AS $token2 => $array2){
            $temp_counter_missions[$token] = !isset($temp_counter_missions[$token]) ? 1 : $temp_counter_missions[$token] + 1;
            $temp_counter_missions['total_failure'] = !isset($temp_counter_missions['total_failure']) ? 1 : $temp_counter_missions['total_failure'] + 1;
        }
    }
}
// Loop through the battle stars
if (!empty($this_playerinfo['save_values_battle_stars'])){
    foreach ($this_playerinfo['save_values_battle_stars'] AS $token => $array){
        $temp_counter_stars[$array['star_kind']] = !isset($temp_counter_stars[$array['star_kind']]) ? 1 : $temp_counter_stars[$array['star_kind']] + 1;
        $temp_counter_stars[$array['star_player']] = !isset($temp_counter_stars[$array['star_player']]) ? 1 : $temp_counter_stars[$array['star_player']] + 1;
        $temp_counter_stars['total'] = !isset($temp_counter_stars['total']) ? 1 : $temp_counter_stars['total'] + 1;
    }
}
// Loop through the reward array
if (!empty($this_playerinfo['save_values_battle_rewards'])){
    foreach ($this_playerinfo['save_values_battle_rewards'] AS $token => $array){
        if (!isset($array['player_points'])){ $array['player_points'] = 0; }
        $temp_counter_players['total'] = !isset($temp_counter_players['total']) ? 1 : $temp_counter_players['total'] + 1;
        $temp_counter_points[$token] = !isset($temp_counter_points[$token]) ? $array['player_points'] : $temp_counter_points[$token] + $array['player_points'];
        $temp_counter_points['total'] = !isset($temp_counter_points['total']) ? $array['player_points'] : $temp_counter_points['total'] + $array['player_points'];
        $array['player_robots'] = !empty($array['player_robots']) ? $array['player_robots'] : array();
        foreach ($array['player_robots'] AS $token2 => $array2){
            $temp_counter_robots[$token] = !isset($temp_counter_robots[$token]) ? 1 : $temp_counter_robots[$token] + 1;
            $temp_counter_robots['total'] = !isset($temp_counter_robots['total']) ? 1 : $temp_counter_robots['total'] + 1;
            $temp_level_total = !empty($array2['robot_level']) ? $array2['robot_level'] * 1000 : 0;
            $temp_experience_total = !empty($array2['robot_experience']) ? $array2['robot_experience'] : 0;
            $temp_stats_total = 0;
            if (!empty($array2['robot_attack'])){ $temp_stats_total += $array2['robot_attack']; }
            if (!empty($array2['robot_attack_pending'])){ $temp_stats_total += $array2['robot_attack_pending']; }
            if (!empty($array2['robot_defense'])){ $temp_stats_total += $array2['robot_defense']; }
            if (!empty($array2['robot_defense_pending'])){ $temp_stats_total += $array2['robot_defense_pending']; }
            if (!empty($array2['robot_speed'])){ $temp_stats_total += $array2['robot_speed']; }
            if (!empty($array2['robot_speed_pending'])){ $temp_stats_total += $array2['robot_speed_pending']; }
            if (!empty($temp_stats_total)){ $temp_stats_total = $temp_stats_total / 1000;}
            $temp_counter_levels[$token2] = $temp_level_total + $temp_experience_total + $temp_stats_total;
        }
        if (!empty($array['player_abilities'])){
            foreach ($array['player_abilities'] AS $token2 => $array2){
                if (!isset($temp_counter_abilities[$token2])){ $temp_counter_abilities['total'] = !isset($temp_counter_abilities['total']) ? 1 : $temp_counter_abilities['total'] + 1; }
                $temp_counter_abilities[$token] = !isset($temp_counter_abilities[$token]) ? 1 : $temp_counter_abilities[$token] + 1;
                $temp_counter_abilities[$token2] = !isset($temp_counter_abilities[$token2]) ? 1 : $temp_counter_abilities[$token2] + 1;
            }
        }
    }
}
// Loop through the robot database
if (!empty($this_playerinfo['save_values_robot_database'])){

    // Loop through all of the robots, one by one, formatting their info
    foreach($temp_robots_index AS $robot_key => $robot_info){

        // Update and/or define the encountered, scanned, summoned, and unlocked flags
        //die('dance <pre>'.print_r($this_playerinfo['save_values_robot_database'], true).'</pre>');
        //$robot_info = rpg_robot::parse_index_info($robot_info);
        if (!isset($robot_info['robot_visible'])){ $robot_info['robot_visible'] = !empty($this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]) ? true : false; }
        if (!isset($robot_info['robot_encountered'])){ $robot_info['robot_encountered'] = !empty($this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_encountered']) ? $this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_encountered'] : 0; }
        if (!isset($robot_info['robot_scanned'])){ $robot_info['robot_scanned'] = !empty($this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_scanned']) ? $this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_scanned'] : 0; }
        if (!isset($robot_info['robot_summoned'])){ $robot_info['robot_summoned'] = !empty($this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_summoned']) ? $this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_summoned'] : 0; }
        if (!isset($robot_info['robot_unlocked'])){ $robot_info['robot_unlocked'] = !empty($this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_unlocked']) ? $this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_unlocked'] : 0; }
        if (!isset($robot_info['robot_defeated'])){ $robot_info['robot_defeated'] = !empty($this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_defeated']) ? $this_playerinfo['save_values_robot_database'][$robot_info['robot_token']]['robot_defeated'] : 0; }

        // Increment the global robots counters
        $temp_counter_database['total']++;
        if ($robot_info['robot_encountered']){ $temp_counter_database['encountered']['total']++; $temp_counter_database['encountered'][$robot_info['robot_class']]++; }
        if ($robot_info['robot_scanned']){ $temp_counter_database['scanned']['total']++; $temp_counter_database['scanned'][$robot_info['robot_class']]++; }
        if ($robot_info['robot_unlocked']){ $temp_counter_database['unlocked']['total']++; $temp_counter_database['unlocked'][$robot_info['robot_class']]++; }
        elseif ($robot_info['robot_summoned']){ $temp_counter_database['summoned']['total']++; $temp_counter_database['summoned'][$robot_info['robot_class']]++; }

    }
    unset($robot_info);
}
// Sort the points array for most-used
asort($temp_counter_points);
$temp_counter_points = array_reverse($temp_counter_points);
// Sort the levels array for most-used
asort($temp_counter_levels);
$temp_counter_levels = array_reverse($temp_counter_levels);
// Collect the most users player and most used robots
$temp_top_player = '';
foreach ($temp_counter_points AS $token => $value){
    if ($token != 'total'){
        $temp_top_player = '<strong>'.$mmrpg_index['players'][$token]['player_name'].'</strong>';
        break;
    }
}
$temp_top_robots = array();

// Collect the favourite robots if there are set
$temp_top_robots = array();
$temp_top_robots_tokens = array();
$temp_top_robots_counter = 5;
if (!empty($this_playerinfo['save_values']['robot_favourites'])){
    //die('we have favourites, people!');
    $temp_top_robots_method = 'favourite';
    foreach ($temp_counter_levels AS $token => $value){
        if (in_array($token, $temp_top_robots_tokens)){ continue; }
        elseif (!in_array($token, $this_playerinfo['save_values']['robot_favourites'])){ continue; }
        if (count($temp_top_robots) < $temp_top_robots_counter){
            $temp_top_robots_tokens[] = $token;
            $temp_index = rpg_robot::parse_index_info($temp_robots_index[$token]);
            $temp_top_robots[] = '<strong>'.$temp_index['robot_name'].'</strong>';
            unset($temp_index);
        } else {
            break;
        }
    }
} else {
    $temp_top_robots_method = 'most-used';
}
if (count($temp_top_robots) < $temp_top_robots_counter){
    foreach ($temp_counter_levels AS $token => $value){
        if (in_array($token, $temp_top_robots_tokens)){ continue; }
        if (count($temp_top_robots) < $temp_top_robots_counter){
            $temp_top_robots_tokens[] = $token;
            $temp_index = rpg_robot::parse_index_info($temp_robots_index[$token]);
            $temp_top_robots[] = '<strong>'.$temp_index['robot_name'].'</strong>';
            unset($temp_index);
        } else {
            break;
        }
    }
}

//die('<pre>$temp_top_robots = '.print_r($temp_top_robots, true).'</pre>');

// Require the leaderboard data file
define('MMRPG_SKIP_MARKUP', true);
define('MMRPG_SHOW_MARKUP_'.$this_playerinfo['user_id'], true);
require(MMRPG_CONFIG_ROOTDIR.'includes/leaderboard.php');

// Define whether or not the players or star tabs should be open
$temp_remote_session = $this_playerinfo['user_id'] != $_SESSION['GAME']['USER']['userid'] ? true : false;
$temp_show_players = true;
$temp_show_items = !empty($this_playerinfo['save_values_battle_items']) ? true : false;
$temp_show_stars = !empty($this_playerinfo['save_values_battle_stars']) ? true : false;

// Define the prototype complete flags for this player
$this_playerinfo['board_battles_dr_light'] = !empty($this_playerinfo['board_battles_dr_light']) ? explode(',', $this_playerinfo['board_battles_dr_light']) : array();
$this_playerinfo['board_battles_dr_wily'] = !empty($this_playerinfo['board_battles_dr_wily']) ? explode(',', $this_playerinfo['board_battles_dr_wily']) : array();
$this_playerinfo['board_battles_dr_cossack'] = !empty($this_playerinfo['board_battles_dr_cossack']) ? explode(',', $this_playerinfo['board_battles_dr_cossack']) : array();

// Define the SEO variables for this page
$this_seo_title = $temp_display_name.' | Leaderboard | '.$this_seo_title;
$this_seo_description = $temp_display_name.' is a player of the Mega Man RPG Prototype with a total of '.$temp_display_points.' battle points. The Mega Man RPG Prototype currently has '.(!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 player' : $this_leaderboard_count.' players') : '0 players').' and that number is growing all the time. During the course of the game, players collect Battle Points on completion of a mission and those points build up over time to unlock new abilities and other new content. Not all players are created equal, however, and some clearly stand above the rest in terms of their commitment to the game and their skill at exploiting the battle system\'s mechanics. In the spirit of competition, all players have been ranked by their total battle point scores and listed from from highest to lowest. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = $temp_display_name.' | Battle Points Leaderboard';
$this_graph_data['description'] = $temp_display_name.' is a player of the Mega Man RPG Prototype with a total of '.$temp_display_points.' battle points. The Mega Man RPG Prototype currently has '.(!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 player' : $this_leaderboard_count.' players') : '0 players').' and that number is growing all the time. During the course of the game, players collect Battle Points on completion of a mission and those points build up over time to unlock new abilities and other new content. Not all players are created equal, however, and some clearly stand above the rest in terms of their commitment to the game and their skill at exploiting the battle system\'s mechanics. In the spirit of competition, all players have been ranked by their total battle point scores and listed from from highest to lowest.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png?'.MMRPG_CONFIG_CACHE_DATE;
//$this_graph_data['type'] = 'website';

//die('<pre>'.print_r($_GET, true).'</pre>');

// Update the GET variables with the current page num
$this_num_offset = $this_current_num - 1;
$_GET['start'] = 0 + ($this_num_offset * 50);
$_GET['limit'] = 50 + ($this_num_offset * 50);

// Define the MARKUP variables for this page
$this_markup_header .= 'Mega Man RPG Prototype Leaderboard';
$this_markup_counter = '<span class="count count_header">( '.(!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : '0 Players').($this_leaderboard_online_count > 0 ? ' <span style="opacity: 0.25;">|</span> <span style="text-shadow: 0 0 5px lime;">'.$this_leaderboard_online_count.' Online</span>' : '').' )</span>';

// Start generating the page markup
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
        <div class="<?= $temp_avatar_class ?> avatar_fieldback player_type_<?= !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none' ?>" style="border-width: 1px; margin-top: 0; margin-right: 0;">
            <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_preview.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 80px 80px;">
                &nbsp;
            </div>
        </div>
        <div class="<?= $temp_avatar_class ?> avatar_userimage" style="margin-top: 0;"  title="<?= isset($temp_item_title) ? $temp_item_title : 'Member' ?>" data-tooltip-type="type player_type_<?= !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none' ?>">
            <? if($temp_is_contributor): ?><div class="<?= $temp_item_class ?>" style="background-image: url(<?= $temp_item_path ?>); position: absolute; top: -22px; right: -30px;" alt="<?= $temp_item_title ?>"><?= $temp_item_title ?></div><? endif; ?>
            <div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>);"><?= $temp_display_name ?></div>
        </div>
        <div class="bodytext">
            <p class="text" style="color: rgb(157, 220, 255);">
                <strong><?= $temp_display_name ?></strong> is <?= $temp_is_contributor ? 'a contributor and ' : '' ?><?= $temp_display_active  ?> of the <strong>Mega Man RPG Prototype</strong> with a current battle point total of <strong><?= number_format($temp_display_points) ?></strong><?= $temp_display_zenny > 0 ? ' and a zenny total of <strong>'.number_format($temp_display_zenny).'</strong>' : '' ?>.
                <strong><?= $temp_display_name ?></strong> created <?= $temp_gender_pronoun ?> account on <?= ($temp_display_created <= 1357016400 ? 'or before ' : '').date('F jS, Y', $temp_display_created) ?> and has since completed <?= $temp_counter_missions['total_complete'] ?> different missions, unlocked <?= $temp_counter_players['total'] ?> playable characters, <?= $temp_counter_robots['total'] ?> robot fighters, <?= $temp_counter_abilities['total'] ?> special abilities, and <?= count($this_playerinfo['save_values_battle_stars']) == 1 ? '1 field star' : count($this_playerinfo['save_values_battle_stars']).' field stars' ?>.
                <strong><?= $temp_display_name ?></strong>'s most-used playable character is <?= $temp_top_player ?>, and <?= $temp_gender_pronoun ?> <?= count($temp_top_robots) > 1 ? 'top '.count($temp_top_robots).' '.$temp_top_robots_method.' robots appear' : $temp_top_robots_method.' robot appears' ?> to be <?= implode(', ', array_slice($temp_top_robots, 0, -1)).(count($temp_top_robots) > 1 ? ' and ' : '').$temp_top_robots[count($temp_top_robots) - 1] ?>.
                <? if (!empty($this_playerinfo['board_points_legacy'])){ ?>
                    Prior to the game-changing battle point reboot of 2016, <strong><?= $temp_display_name ?></strong> had amassed a grand total of <strong><?= number_format($this_playerinfo['board_points_legacy'], 0, '.', ',') ?></strong> battle points and reached <?= mmrpg_number_suffix(mmrpg_prototype_leaderboard_rank_legacy($this_playerinfo['user_id'])) ?> place.
                <? } ?>
            </p>
        </div>
    </div>

    <? $temp_colour_token = !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none'; ?>
    <h2 class="subheader field_type_<?= $temp_colour_token ?>" style="margin: 10px 0 4px; text-align: left;">
        <?=$temp_display_name?>&#39;s Leaderboard
        <span class="count" style="position: relative; bottom: 1px;"><?
            // Add the prototype complete flags if applicable
            if (count($this_playerinfo['board_battles_dr_light']) >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){ echo '<span class="prototype_complete prototype_complete_dr-light" title="Dr. Light Prototype Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span> '; }
            if (count($this_playerinfo['board_battles_dr_wily']) >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){ echo '<span class="prototype_complete prototype_complete_dr-wily" title="Dr. Wily Prototype Complete!" data-tooltip-type="player_type player_type_attack">&clubs;</span> '; }
            if (count($this_playerinfo['board_battles_dr_cossack']) >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){ echo '<span class="prototype_complete prototype_complete_dr-cossack" title="Dr. Cossack Prototype Complete!" data-tooltip-type="player_type player_type_speed">&diams;</span> '; }
        ?></span>
    </h2>

    <div id="game_container" class="subbody thread_subbody thread_subbody_full thread_subbody_full_right thread_right event event_triple event_visible <?= in_array($this_current_token, array('robots', 'players', 'database', 'items', 'stars')) ? 'has_iframe' : '' ?>" style="text-align: left; position: relative; padding-bottom: 6px; margin-bottom: 4px;">

        <div id="game_buttons" data-fieldtype="<?= !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none' ?>" class="field">

            <a class="link_button profile field_type field_type_<?= $temp_colour_token ?> <?= empty($this_current_token) ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/' ?>">Profile</a>

            <? if(!empty($temp_display_points)): ?>
                <a class="link_button points field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'points' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/points/' ?>">Points</a>
            <? endif; ?>

            <a class="link_button robots field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'robots' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/robots/' ?>">Robots</a>

            <? if(!empty($temp_show_players)): ?>
                <a class="link_button players field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'players' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/players/' ?>">Players</a>
            <? endif; ?>

            <a class="link_button database field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'database' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/database/' ?>">Database</a>

            <? if(!empty($temp_show_items)): ?>
                <a class="link_button items field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'items' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/items/' ?>">Items</a>
            <? endif; ?>
            <? if(!empty($temp_show_stars)): ?>
                <a class="link_button stars field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'stars' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/stars/' ?>">Stars</a>
            <? endif; ?>

            <? if (!empty($this_playerinfo['user_website_address']) && preg_match('/^([^@]+)@([^@]+)$/i', $this_playerinfo['user_website_address'])): ?>
                <a class="link_button website field_type field_type_<?= $temp_colour_token ?>" href="mailto:<?= $this_playerinfo['user_website_address'] ?>" target="_blank">Email</a>
            <? elseif (!empty($this_playerinfo['user_website_address'])): ?>
                <a class="link_button field_type field_type_<?= $temp_colour_token ?>" href="<?= $this_playerinfo['user_website_address'] ?>" target="_blank">Website</a>
            <? endif; ?>

            <? if ($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_userid != $this_playerinfo['user_id'] && !empty($this_userinfo['user_flag_postprivate'])): ?>
                <a class="link_button message field_type field_type_<?= $temp_colour_token ?>" href="community/personal/0/new/<?= $this_playerinfo['user_name_clean'] ?>/">Message</a>
            <? endif; ?>

        </div>

        <?

        // -- LEADERBOARD PAGES -- //

        // Define the allowable pages
        $temp_allowed_pages = array('robots', 'players', 'database', 'stars', 'items', 'points');

        // If this is the View Profile page, show the appropriate content
        if (empty($this_current_token) || !in_array($this_current_token, $temp_allowed_pages)){
            ?>

                <div class="bodytext community_stats">
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
                    <div class="text player_stats">
                        <strong class="label">Robot Database Stats</strong>
                        <ul class="records">
                            <? $temp_unlocked_summoned_total = $temp_counter_database['unlocked']['total'] + $temp_counter_database['summoned']['total']; ?>
                            <? if(!empty($temp_counter_database['total'])): ?>
                                <li class="stat"><span class="counter summoned_counter"><?= $temp_counter_database['unlocked']['total'] ?> Unlocked</span></li>
                                <li class="stat"><span class="counter scanned_counter"><?= $temp_counter_database['scanned']['total'] ?> Scanned</span></li>
                                <li class="stat"><span class="counter encountered_counter"><?= $temp_counter_database['encountered']['total'] ?> Encountered</span></li>
                            <? else: ?>
                                <li class="stat"><span class="counter summoned_counter">0 Summoned</span></li>
                                <li class="stat"><span class="counter scanned_counter">0 Scanned</span></li>
                                <li class="stat"><span class="counter encountered_counter">0 Encountered</span></li>
                            <? endif; ?>
                        </ul>
                    </div>
                    <? /*
                    <div class="text player_stats">
                        <strong class="label">Star Force Stats</strong>
                        <ul class="records">
                            <? if(!empty($temp_counter_stars['total'])): ?>
                                <li class="stat"><span class="counter field_counter"><?= $temp_counter_stars['field'] == 1 ? '1 Field Star' : $temp_counter_stars['field'].' Field Stars' ?></span></li>
                                <li class="stat"><span class="counter fusion_counter"><?= $temp_counter_stars['fusion'] == 1 ? '1 Fusion Star' : $temp_counter_stars['fusion'].' Fusion Stars' ?></span></li>
                                <li class="stat"><span class="counter boost_counter">+<?= number_format((($temp_counter_stars['field'] * 10) + ($temp_counter_stars['fusion'] * 20)), 0, '.', ',') ?>% Boost</span></li>
                            <? else: ?>
                                <li class="stat"><span class="counter field_counter">0 Field Stars</span></li>
                                <li class="stat"><span class="counter fusion_counter">0 Fusion Stars</span></li>
                                <li class="stat"><span class="counter boost_counter">0% Boost</span></li>
                            <? endif; ?>
                        </ul>
                    </div>
                    */ ?>
                </div>

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
                                <h3 class="table_head field_type field_type_<?= $temp_colour_token ?>">
                                    <span>Battle Points Overview</span>
                                </h3>
                                <a class="toggle"><span>+ / -</span></a>
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <h4 class="table_foot field_type field_type_<?= $temp_colour_token ?>">
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
                        foreach ($battle_points_categories AS $key => $category_token){

                            // Define the category name using the token as reference
                            $category_nth = $key % 2 === 0 ? 'even' : 'odd';
                            $category_name = ucwords(str_replace('_', ' ', $category_token));
                            $category_name = str_replace('Robots Unlocked ', 'Robots w/ ', $category_name);
                            $category_name = str_replace('Database Robots ', 'Robots ', $category_name);
                            $category_list = $battle_points_index[$category_token];
                            $category_count = count($category_list);
                            $category_points = $battle_points_index[$category_token.'_points'];

                            // If the category list isn't empty and is an array, we need to loop for details
                            if (!empty($category_list) && is_array($category_list)){

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
                                if ($category_token === 'robots_unlocked_alt_outfits'
                                    || $category_token === 'items_unlocked'){
                                    foreach ($category_list AS $key => $data){
                                        if (strstr($data, ' x')){ list($data, $num) = explode(' x', $data); $num = (int)($num); }
                                        else { $num = 1; }
                                        $category_real_count += $num;
                                    }

                                }

                                // Loop through elements in the details list and add to markup array
                                $details_markup = array();
                                foreach ($category_list AS $key => $data){

                                    // Process the individual items in the category list differently depending on type
                                    if ($category_token === 'doctors_unlocked'){
                                        $token = $data;
                                        $info = $mmrpg_index_players[$token];
                                        $details_markup[] = '<li><a class="player_type type_'.$info['player_type'].'" href="database/players/'.$info['player_token'].'/">'.$info['player_name'].'</a></li>';
                                    } elseif ($category_token === 'chapters_unlocked'){
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
                                        $details_markup[] = '<li><a class="robot_type type_'.$type.'" href="database/robots/'.$info['robot_token'].'/">'.$info['robot_name'].($num !== 1 ? ' x'.$num : '').'</a></li>';
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
                                    }  else {
                                        $details_markup[] = '<li><span class="field_type type_none">'.print_r($data, true).'</span></li>';
                                    }

                                }
                                $details_markup = implode(' ', $details_markup);

                            }
                            // Otherwise we show an empty span with no data
                            elseif (!empty($category_list) && is_string($category_list)) {
                                $details_markup = '<li><span>'.$category_list.'</span></li>';
                            }
                            // Otherwise we show an empty span with no data
                            else {
                                $details_markup = '<li><span class="field_type type_none">- no data -</span></li>';
                            }

                            // Display a table row for this categories name and details
                            ?>
                            <tr data-key="<?= $key ?>" class="<?= $category_nth ?> <?= $category_token ?> main">
                                <td class="category"><h5><?= $category_name ?></h5> <a class="toggle"><span>+</span></a></td>
                                <td class="counter"><div><?= 'x '.(!empty($category_real_count) ? $category_real_count : $category_count) ?></div></td>
                                <td class="points"><div>+ <?= number_format($category_points, 0, '.', ',') ?> BP</div></td>
                            </tr>
                            <tr data-key="<?= $key ?>" class="<?= $category_nth ?> <?= $category_token ?> details hidden">
                                <td class="details" colspan="3"><?= '<ul>'.$details_markup.'</ul>' ?></td>
                            </tr>
                            <?

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

<? if(false): ?>
    <div style="padding: 10px; color: white;">
        <pre>$temp_counter_players : <?= print_r($temp_counter_players, true) ?></pre>
        <hr />
        <pre>$temp_counter_points : <?= print_r($temp_counter_points, true) ?>
        <hr />
        <pre>$temp_counter_missions : <?= print_r($temp_counter_missions, true) ?>
        <hr />
        <pre>$temp_counter_robots : <?= print_r($temp_counter_robots, true) ?>
        <hr />
        <pre>$temp_counter_abilities : <?= print_r($temp_counter_abilities, true) ?>
        <hr />
        <pre>$temp_counter_levels : <?= print_r($temp_counter_levels, true) ?>
        <hr />
    </div>
<? endif; ?>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(ob_get_clean());
?>