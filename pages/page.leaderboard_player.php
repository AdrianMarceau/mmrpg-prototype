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
  $temp_item_path = 'images/abilities/item-'.(!empty($this_playerinfo['role_icon']) ? $this_playerinfo['role_icon'] : 'energy-pellet' ).'/icon_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
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
$temp_robots_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1 AND robot_token NOT IN (".implode(',', $hidden_database_robots).");", 'robot_token');

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
    //$robot_info = mmrpg_robot::parse_index_info($robot_info);
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

  /*
  foreach ($this_playerinfo['save_values_robot_database'] AS $token => $array){
    $temp_index = mmrpg_robot::parse_index_info($temp_robots_index[$token]);
    if ($temp_index['robot_class'] == 'master' && !empty($temp_counter_robots[$token])){ $temp_counter_database['unlocked'] += 1; }
    elseif ($temp_index['robot_class'] == 'mecha' && !empty($array[''])){ $temp_counter_database['unlocked'] += 1; }

    $temp_counter_database['encountered'] += 1;
    $temp_counter_database['total'] += 1;
  }
  */
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
      $temp_index = mmrpg_robot::parse_index_info($temp_robots_index[$token]);
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
      $temp_index = mmrpg_robot::parse_index_info($temp_robots_index[$token]);
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
require('data/leaderboard.php');

// Define whether or not the players or starforce tabs should be open
$temp_remote_session = $this_playerinfo['user_id'] != $_SESSION['GAME']['USER']['userid'] ? true : false;
$temp_show_players = true;
$temp_show_starforce = $this_playerinfo['save_values_battle_stars'] > 0 ? true : false;

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
$this_markup_header = '<span class="hideme">'.$temp_display_name.' | </span>';
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
      <?/*
      <div class="userblock" style="margin-top: 5px;" title="Community Forum Stats">
        <strong class="counter">Community Stats</strong>
        <div class="counter thread_counter" style=""><?= $this_playerinfo['thread_count'] == 1 ? '1 Thread' : $this_playerinfo['thread_count'].' Threads' ?></div>
        <div class="counter post_counter"><?= $this_playerinfo['post_count'] == 1 ? '1 Post' : $this_playerinfo['post_count'].' Posts' ?></div>
      </div>
      <div class="userblock" style="margin-top: 5px;" title="Player Battle Stats">
        <strong class="counter">Battle Stats</strong>
        <div class="counter victory_counter"><?= $this_playerinfo['victory_count'] == 1 ? '1 Victory' : $this_playerinfo['victory_count'].' Victories' ?></div>
        <div class="counter defeat_counter"><?= $this_playerinfo['defeat_count'] == 1 ? '1 Defeat' : $this_playerinfo['defeat_count'].' Defeats' ?></div>
      </div>
      */?>
    </div>
    <div class="bodytext">
      <p class="text" style="color: rgb(157, 220, 255);">
        <strong><?= $temp_display_name ?></strong> is <?= $temp_is_contributor ? 'a contributor and ' : '' ?><?= $temp_display_active  ?> of the <strong>Mega Man RPG Prototype</strong> with a current battle point total of <strong><?= number_format($temp_display_points) ?></strong><?= $temp_display_zenny > 0 ? ' and a zenny total of <strong>'.number_format($temp_display_zenny).'</strong>' : '' ?>.
        <strong><?= $temp_display_name ?></strong> created <?= $temp_gender_pronoun ?> account on <?= ($temp_display_created <= 1357016400 ? 'or before ' : '').date('F jS, Y', $temp_display_created) ?> and has since completed <?= $temp_counter_missions['total_complete'] ?> different missions, unlocked <?= $temp_counter_players['total'] ?> playable characters, <?= $temp_counter_robots['total'] ?> robot fighters, <?= $temp_counter_abilities['total'] ?> special abilities, and <?= count($this_playerinfo['save_values_battle_stars']) == 1 ? '1 field star' : count($this_playerinfo['save_values_battle_stars']).' field stars' ?>.
        <strong><?= $temp_display_name ?></strong>'s most-used playable character is <?= $temp_top_player ?>, and <?= $temp_gender_pronoun ?> <?= count($temp_top_robots) > 1 ? 'top '.count($temp_top_robots).' '.$temp_top_robots_method.' robots appear' : $temp_top_robots_method.' robot appears' ?> to be <?= implode(', ', array_slice($temp_top_robots, 0, -1)).(count($temp_top_robots) > 1 ? ' and ' : '').$temp_top_robots[count($temp_top_robots) - 1] ?>.
      </p>
    </div>
  </div>

  <? $temp_colour_token = !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none'; ?>
  <h2 class="subheader field_type_<?= $temp_colour_token ?>" style="margin: 10px 0 4px; text-align: left;">
    <?=$temp_display_name?>&#39;s Leaderboard
    <span class="count" style="position: relative; bottom: 1px;"><?
      // Add the prototype complete flags if applicable
      if (count($this_playerinfo['board_battles_dr_light']) >= 17){ echo '<span class="prototype_complete prototype_complete_dr-light" title="Dr. Light Prototype Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span> '; }
      if (count($this_playerinfo['board_battles_dr_wily']) >= 17){ echo '<span class="prototype_complete prototype_complete_dr-wily" title="Dr. Wily Prototype Complete!" data-tooltip-type="player_type player_type_attack">&clubs;</span> '; }
      if (count($this_playerinfo['board_battles_dr_cossack']) >= 17){ echo '<span class="prototype_complete prototype_complete_dr-cossack" title="Dr. Cossack Prototype Complete!" data-tooltip-type="player_type player_type_speed">&diams;</span> '; }
    ?></span>
  </h2>

  <div class="subbody thread_subbody thread_subbody_full thread_subbody_full_right thread_right event event_triple event_visible" style="text-align: left; position: relative; padding-bottom: 6px; margin-bottom: 4px;">

    <div id="game_buttons" data-fieldtype="<?= !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none' ?>" class="field" style="margin: 0 auto 20px; <?= $this_current_token == 'threads' || $this_current_token == 'posts' ? 'padding-bottom: 6px;' : '' ?>">
      <a class="link_button field_type <?= empty($this_current_token) ? 'field_type_'.$temp_colour_token.' link_button_active' : 'field_type_empty' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/' ?>">Profile</a>
      <a class="link_button field_type <?= $this_current_token == 'robots' ? 'field_type_'.$temp_colour_token.' link_button_active' : 'field_type_empty' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/robots/' ?>">Robots</a>
      <? if(!empty($temp_show_players)): ?>
        <a class="link_button field_type <?= $this_current_token == 'players' ? 'field_type_'.$temp_colour_token.' link_button_active' : 'field_type_empty' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/players/' ?>">Players</a>
      <? endif; ?>
      <? if(!empty($temp_show_starforce)): ?>
        <a class="link_button field_type <?= $this_current_token == 'starforce' ? 'field_type_'.$temp_colour_token.' link_button_active' : 'field_type_empty' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/starforce/' ?>">Starforce</a>
      <? endif; ?>
      <a class="link_button field_type <?= $this_current_token == 'database' ? 'field_type_'.$temp_colour_token.' link_button_active' : 'field_type_empty' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/database/' ?>">Database</a>
      <? if(!empty($this_playerinfo['thread_count'])): ?>
        <a class="link_button field_type <?= $this_current_token == 'threads' ? 'field_type_'.$temp_colour_token.' link_button_active' : 'field_type_empty' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/threads/' ?>">Threads</a>
      <? endif; ?>
      <? if(!empty($this_playerinfo['post_count'])): ?>
        <a class="link_button field_type <?= $this_current_token == 'posts' ? 'field_type_'.$temp_colour_token.' link_button_active' : 'field_type_empty' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/posts/' ?>">Posts</a>
      <? endif; ?>
      <? if (!empty($this_playerinfo['user_website_address']) && preg_match('/^([^@]+)@([^@]+)$/i', $this_playerinfo['user_website_address'])): ?>
        <a class="link_button field_type field_type_empty" href="mailto:<?= $this_playerinfo['user_website_address'] ?>" target="_blank">Email</a>
      <? elseif (!empty($this_playerinfo['user_website_address'])): ?>
        <a class="link_button field_type field_type_empty" href="<?= $this_playerinfo['user_website_address'] ?>" target="_blank">Website</a>
      <? endif; ?>
      <? if ($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_userid != $this_playerinfo['user_id'] && !empty($this_userinfo['user_flag_postprivate'])): ?>
        <a class="link_button field_type field_type_empty" href="community/personal/0/new/<?= $this_playerinfo['user_name_clean'] ?>/">Message</a>
      <? endif; ?>
    </div>

    <?

    // -- LEADERBOARD PAGES -- //

    // Define the allowable pages
    $temp_allowed_pages = array('robots', 'players', 'starforce', 'database', 'threads', 'posts');

    // If this is the View Profile page, show the appropriate content
    if (empty($this_current_token) || !in_array($this_current_token, $temp_allowed_pages)){
      ?>

        <div class="bodytext" style="overflow: hidden; margin-bottom: 0;">
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
            <strong class="label">Player Battle Stats</strong>
            <ul class="records">
              <li class="stat"><span class="counter victory_counter"><?= $this_playerinfo['victory_count'] == 1 ? '1 Victory' : $this_playerinfo['victory_count'].' Victories' ?></span></li>
              <li class="stat"><span class="counter defeat_counter"><?= $this_playerinfo['defeat_count'] == 1 ? '1 Defeat' : $this_playerinfo['defeat_count'].' Defeats' ?></span></li>
              <? $this_playerinfo['battle_count'] = $this_playerinfo['victory_count'] + $this_playerinfo['defeat_count']; ?>
              <? $this_playerinfo['battle_rating'] = round(($this_playerinfo['victory_count'] * 2) - ($this_playerinfo['defeat_count'] / 2)); ?>
              <? if($this_playerinfo['battle_rating'] != 0): ?>
                <li class="stat"><span class="counter rating_counter"><?= ($this_playerinfo['battle_rating'] > 0 ? '+' : '-').$this_playerinfo['battle_rating'] ?> Rating</span></li>
              <? else: ?>
                <li class="stat"><span class="counter rating_counter">0 Rating</span></li>
              <? endif; ?>
            </ul>
          </div>
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
          <div class="text player_stats">
            <strong class="label">Robot Database Stats</strong>
            <ul class="records">
              <? $temp_unlocked_summoned_total = $temp_counter_database['unlocked']['total'] + $temp_counter_database['summoned']['total']; ?>
              <? if(!empty($temp_counter_database['total'])): ?>
                <li class="stat"><span class="counter summoned_counter"><?= $temp_unlocked_summoned_total ?> Summoned</span></li>
                <li class="stat"><span class="counter scanned_counter"><?= $temp_counter_database['scanned']['total'] ?> Scanned</span></li>
                <li class="stat"><span class="counter encountered_counter"><?= $temp_counter_database['encountered']['total'] ?> Encountered</span></li>
              <? else: ?>
                <li class="stat"><span class="counter summoned_counter">0 Summoned</span></li>
                <li class="stat"><span class="counter scanned_counter">0 Scanned</span></li>
                <li class="stat"><span class="counter encountered_counter">0 Encountered</span></li>
              <? endif; ?>
            </ul>
          </div>
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

      <div id="game_frames" class="field" style="height: 600px;">
        <iframe name="edit_robots" src="frames/edit_robots.php?action=robots&amp;1=1&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
      </div>

      <?
    }
    // Else if this is the View Players page, show the appropriate content
    elseif ($this_current_token == 'players'){
      ?>

      <div id="game_frames" class="field" style="height: 600px;">
        <iframe name="edit_players" src="frames/edit_players.php?action=players&amp;1=1&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
      </div>

      <?
    }
    // Else if this is the View Starforce page, show the appropriate content
    elseif ($this_current_token == 'starforce'){
      ?>

      <div id="game_frames" class="field" style="height: 600px;">
        <iframe name="edit_starforce" src="frames/starforce.php?1=1&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
      </div>

      <?
    }
    // Else if this is the View Database page, show the appropriate content
    elseif ($this_current_token == 'database'){
      ?>

      <div id="game_frames" class="field" style="height: 600px;">
        <iframe name="database" src="frames/database.php?1=1&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
      </div>

      <?
    }

    ?>

  </div>

  <?

  // -- LEADERBOARD PAGES CONTINUED -- //

  if ($this_current_token == 'threads'){

    // Define the moderator flag to false to prevent errors
    define('COMMUNITY_VIEW_MODERATOR', 0);

    // Collect any sorting or limit variables from the request
    $temp_thread_sort = !empty($_GET['sort']) && in_array($_GET['sort'], array('asc', 'desc')) ? $_GET['sort'] : 'desc';
    $temp_thread_sort_string = strtoupper($temp_thread_sort);
    $temp_thread_show = !empty($_GET['show']) && ($_GET['show'] == 'all' || is_numeric($_GET['show'])) ? $_GET['show'] : 10;

    // Collect all the threads created by this user from the database
    $this_threads_query = "SELECT threads.*, users.*, users2.*, users3.*, roles.*, categories.*, posts.post_count FROM mmrpg_threads AS threads
      LEFT JOIN mmrpg_users AS users ON threads.user_id = users.user_id
      LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
      LEFT JOIN (SELECT user_id AS mod_user_id, user_name AS mod_user_name, user_name_public AS mod_user_name_public, user_name_clean AS mod_user_name_clean, user_colour_token AS mod_user_colour_token FROM mmrpg_users) AS users2 ON threads.thread_mod_user = users2.mod_user_id
      LEFT JOIN (SELECT user_id AS target_user_id, user_name AS target_user_name, user_name_public AS target_user_name_public, user_name_clean AS target_user_name_clean, user_colour_token AS target_user_colour_token, user_image_path AS target_user_image_path, user_background_path AS target_user_background_path FROM mmrpg_users) AS users3 ON threads.thread_target = users3.target_user_id
      LEFT JOIN mmrpg_categories AS categories ON threads.category_id = categories.category_id
      LEFT JOIN (
      SELECT posts.thread_id, count(1) AS post_count
      FROM mmrpg_posts AS posts
      GROUP BY posts.thread_id) AS posts ON threads.thread_id = posts.thread_id
      WHERE threads.category_id <> 0 && threads.thread_published = 1 AND users.user_id = {$this_playerinfo['user_id']}
      ORDER BY threads.thread_date {$temp_thread_sort_string} ";
    $this_threads_array = $DB->get_array_list($this_threads_query);
    $this_threads_index = array();
    $this_threads_count = !empty($this_threads_array) ? count($this_threads_array) : 0;
    if ($temp_thread_show > $this_threads_count){ $temp_thread_show = $this_threads_count; }

    // Define the current date group
    $this_date_group = '';

    // Define the temporary timeout variables
    $this_time = time();
    $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

    // Define the fake category info for the leaderboard
    $this_category_info = array('category_id' => 99, 'category_token' => 'leaderboard');

    // Loop through the thread array and display its contents
    if (!empty($this_threads_array)){
      ?>
      <div class="subbody posts_body thread_right event event_triple event_visible" style="margin-top: -6px;">
        <p class="text" style="font-size: 110%; padding: 6px 0 0;">
          <strong><?= $temp_display_name ?></strong> has created a total of <strong><?= $this_playerinfo['thread_count'] == 1 ? '1 Thread' : $this_playerinfo['thread_count'].' Threads' ?></strong>
          <span style="font-size: 80%; float: right; margin-left: 20px; text-align: right; ">
            <strong>Limit To</strong><br />
            <? if($this_threads_count > 10): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/threads/?sort='.$temp_thread_sort.'&amp;show=10' ?>" rel="nofollow" style="text-decoration: <?= $temp_thread_show == 10 ? 'underline' : 'none' ?>;">10</a> <span class="pipe">|</span><? endif; ?>
            <? if($this_threads_count > 50): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/threads/?sort='.$temp_thread_sort.'&amp;show=50' ?>" rel="nofollow" style="text-decoration: <?= $temp_thread_show == 50 ? 'underline' : 'none' ?>;">50</a> <span class="pipe">|</span><? endif; ?>
            <? if($this_threads_count > 100): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/threads/?sort='.$temp_thread_sort.'&amp;show=100' ?>" rel="nofollow" style="text-decoration: <?= $temp_thread_show == 100 ? 'underline' : 'none' ?>;">100</a> <span class="pipe">|</span><? endif; ?>
            <? if($this_threads_count > 200): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/threads/?sort='.$temp_thread_sort.'&amp;show=200' ?>" rel="nofollow" style="text-decoration: <?= $temp_thread_show == 200 ? 'underline' : 'none' ?>;">200</a> <span class="pipe">|</span><? endif; ?>
            <? if($this_threads_count > 500): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/threads/?sort='.$temp_thread_sort.'&amp;show=500' ?>" rel="nofollow" style="text-decoration: <?= $temp_thread_show == 500 ? 'underline' : 'none' ?>;">500</a> <span class="pipe">|</span><? endif; ?>
            <a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/threads/?sort='.$temp_thread_sort.'&amp;show=all' ?>" rel="nofollow" style="text-decoration: <?= $temp_thread_show == 'all' ? 'underline' : 'none' ?>;">All</a>
          </span>
          <span style="font-size: 80%; float: right; margin-left: 20px; text-align: right; ">
            <strong>Sort By</strong><br />
            <a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/threads/?sort=desc&amp;show='.$temp_thread_show ?>" rel="nofollow" style="text-decoration: <?= $temp_thread_sort == 'desc' ? 'underline' : 'none' ?>;">New</a> <span class="pipe">|</span>
            <a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/threads/?sort=asc&amp;show='.$temp_thread_show ?>" rel="nofollow" style="text-decoration: <?= $temp_thread_sort == 'asc' ? 'underline' : 'none' ?>;">Old</a>
          </span>
        </p>
        <? if(!empty($this_threads_count)): ?>
          <p class="text" style="margin-top: -4px;">
            <?= $temp_thread_show != 'all' && $temp_thread_show < $this_threads_count ? 'Showing '.$temp_thread_show.' '.($temp_thread_sort == 'desc' ? 'Newest' : 'Oldest').' Threads' : 'Showing All Threads' ?>
          </span>
        <? endif; ?>
        <?
        // Collect markup for this thread from the function
        foreach ($this_threads_array AS $this_thread_key => $this_thread_info){
          $temp_markup = mmrpg_website_community_thread_linkblock($this_thread_info, $this_category_info);
          echo $temp_markup."\n";
          if ($temp_thread_show != 'all' && ($this_thread_key + 1) >= $temp_thread_show){ break; }
        }
        ?>
        <p class="text post_subbody" style="text-align: right; min-height: 16px;"><a class="postscroll" href="#top" style="right: 12px;">^ Top</a></p>
      </div>
      <?
    } else {
      ?>
      <div class="subbody posts_body thread_left event event_triple event_visible" style="margin-top: -6px;">
        <p class="text">- there are no threads to display -</p>
        <p class="text post_subbody" style="text-align: right; min-height: 16px;"><a class="postscroll" href="#top" style="right: 12px;">^ Top</a></p>
      </div>
      <?
    }

  }
  // Else if this is the View Posts page, show the appropriate content
  elseif ($this_current_token == 'posts'){

    // Define the moderator flag to false to prevent errors
    define('COMMUNITY_VIEW_MODERATOR', 0);

    // Collect any sorting or limit variables from the request
    $temp_post_sort = !empty($_GET['sort']) && in_array($_GET['sort'], array('asc', 'desc')) ? $_GET['sort'] : 'desc';
    $temp_post_sort_string = strtoupper($temp_post_sort);
    $temp_post_show = !empty($_GET['show']) && ($_GET['show'] == 'all' || is_numeric($_GET['show'])) ? $_GET['show'] : 10;

    // Collect all the posts for this category from the database
    $this_posts_query = "SELECT posts.*, users.*, roles.*, categories.* FROM mmrpg_posts AS posts
      LEFT JOIN mmrpg_users AS users ON posts.user_id = users.user_id
      LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
      LEFT JOIN mmrpg_threads AS threads ON threads.thread_id = posts.thread_id
      LEFT JOIN mmrpg_categories AS categories ON posts.category_id = categories.category_id
      WHERE threads.category_id <> 0 AND posts.post_deleted = 0 AND users.user_id = {$this_playerinfo['user_id']}
      ORDER BY posts.post_date {$temp_post_sort_string}"; //WHERE posts.post_deleted = 0
    $this_posts_array = $DB->get_array_list($this_posts_query);
    $this_posts_count = !empty($this_posts_array) ? count($this_posts_array) : 0;

    // Loop through the posts and collect all referenced thread IDs for the index
    $temp_required_ids = array();
    $this_user_ids_array = array();
    if (!empty($this_posts_array)){
      foreach ($this_posts_array AS $key => $info){
        if (!in_array($info['thread_id'], $temp_required_ids)){ $temp_required_ids[] = $info['thread_id']; }
        if (!in_array($info['user_id'], $this_user_ids_array)){ $this_user_ids_array[] = $info['user_id']; }
      }
    }

    // Collect collect an index of threads from the database for reference
    if (!empty($temp_required_ids)){
      $temp_include_ids = implode(',', $temp_required_ids);
      $this_threads_query = "SELECT threads.*, users.*, users2.*, users3.*, categories.*, posts.post_count FROM mmrpg_threads AS threads
        LEFT JOIN mmrpg_users AS users ON threads.user_id = users.user_id
        LEFT JOIN (SELECT user_id AS mod_user_id, user_name AS mod_user_name, user_name_public AS mod_user_name_public, user_name_clean AS mod_user_name_clean, user_colour_token AS mod_user_colour_token FROM mmrpg_users) AS users2 ON threads.thread_mod_user = users2.mod_user_id
        LEFT JOIN (SELECT user_id AS target_user_id, user_name AS target_user_name, user_name_public AS target_user_name_public, user_name_clean AS target_user_name_clean, user_colour_token AS target_user_colour_token, user_image_path AS target_user_image_path, user_background_path AS target_user_background_path FROM mmrpg_users) AS users3 ON threads.thread_target = users3.target_user_id
        LEFT JOIN mmrpg_categories AS categories ON threads.category_id = categories.category_id
        LEFT JOIN (
        SELECT posts.thread_id, count(1) AS post_count
        FROM mmrpg_posts AS posts
        GROUP BY posts.thread_id) AS posts ON threads.thread_id = posts.thread_id
        WHERE threads.category_id <> 0 && threads.thread_published = 1 AND threads.thread_id IN ({$temp_include_ids})
        ORDER BY threads.thread_date ASC";
      $this_threads_index = $DB->get_array_list($this_threads_query, 'thread_id');
      $this_threads_count = !empty($this_threads_index) ? count($this_threads_index) : 0;
    } else {
      $this_threads_query = '';
      $this_threads_index = array();
      $this_threads_count = 0;
    }

    // Collect the user post and thread count index plus leaderboard points for display
    $temp_id_includes = !empty($this_user_ids_array) ? 'AND mmrpg_users.user_id IN ('.implode(', ', $this_user_ids_array).')' : '';
    if (!empty($temp_id_includes)){
      $this_user_countindex = $DB->get_array_list('SELECT
        mmrpg_users.user_id,
        mmrpg_leaderboard.board_points,
        mmrpg_threads.thread_count,
        mmrpg_posts.post_count
        FROM mmrpg_users
        LEFT JOIN mmrpg_leaderboard ON mmrpg_leaderboard.user_id = mmrpg_users.user_id
        LEFT JOIN (
        SELECT user_id, COUNT(thread_id) AS thread_count FROM mmrpg_threads WHERE mmrpg_threads.thread_target = 0 AND thread_published = 1 GROUP BY mmrpg_threads.user_id
        ) mmrpg_threads ON mmrpg_threads.user_id = mmrpg_users.user_id
        LEFT JOIN (
        SELECT user_id, COUNT(post_id) AS post_count FROM mmrpg_posts WHERE mmrpg_posts.post_target = 0 AND post_deleted = 0 GROUP BY mmrpg_posts.user_id
        ) mmrpg_posts ON mmrpg_posts.user_id = mmrpg_users.user_id
        WHERE mmrpg_leaderboard.board_points > 0 '.$temp_id_includes, 'user_id');
    } else {
      $this_user_countindex = array();
    }


    // Define the current date group
    $this_date_group = '';

    // Define the temporary timeout variables
    $this_time = time();
    $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

    // Define the fake category info for the leaderboard
    $this_category_info = array('category_id' => 99, 'category_token' => 'leaderboard');

    // Loop through the post array and display its contents
    if (!empty($this_posts_array)){
      ?>
      <div class="subbody posts_body post_left event event_triple event_visible" style="margin-top: -6px;">
        <p class="text" style="font-size: 110%; padding: 6px 3px 0;">
          <strong><?= $temp_display_name ?></strong> has created a total of <strong><?= $this_playerinfo['post_count'] == 1 ? '1 Post' : $this_playerinfo['post_count'].' Posts' ?></strong>
          <span style="font-size: 80%; float: right; margin-left: 20px; text-align: right; ">
            <strong>Limit To</strong><br />
            <? if($this_posts_count > 10): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/posts/?sort='.$temp_post_sort.'&amp;show=10' ?>" rel="nofollow" style="text-decoration: <?= $temp_post_show == 10 ? 'underline' : 'none' ?>;">10</a> <span class="pipe">|</span><? endif; ?>
            <? if($this_posts_count > 50): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/posts/?sort='.$temp_post_sort.'&amp;show=50' ?>" rel="nofollow" style="text-decoration: <?= $temp_post_show == 50 ? 'underline' : 'none' ?>;">50</a> <span class="pipe">|</span><? endif; ?>
            <? if($this_posts_count > 100): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/posts/?sort='.$temp_post_sort.'&amp;show=100' ?>" rel="nofollow" style="text-decoration: <?= $temp_post_show == 100 ? 'underline' : 'none' ?>;">100</a> <span class="pipe">|</span><? endif; ?>
            <? if($this_posts_count > 200): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/posts/?sort='.$temp_post_sort.'&amp;show=200' ?>" rel="nofollow" style="text-decoration: <?= $temp_post_show == 200 ? 'underline' : 'none' ?>;">200</a> <span class="pipe">|</span><? endif; ?>
            <? if($this_posts_count > 500): ?><a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/posts/?sort='.$temp_post_sort.'&amp;show=100' ?>" rel="nofollow" style="text-decoration: <?= $temp_post_show == 500 ? 'underline' : 'none' ?>;">500</a> <span class="pipe">|</span><? endif; ?>
            <a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/posts/?sort='.$temp_post_sort.'&amp;show=all' ?>" rel="nofollow" style="text-decoration: <?= $temp_post_show == 'all' ? 'underline' : 'none' ?>;">All</a>
          </span>
          <span style="font-size: 80%; float: right; margin-left: 20px; text-align: right; ">
            <strong>Sort By</strong><br />
            <a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/posts/?sort=desc&amp;show='.$temp_post_show ?>" rel="nofollow" style="text-decoration: <?= $temp_post_sort == 'desc' ? 'underline' : 'none' ?>;">New</a> <span class="pipe">|</span>
            <a href="<?= MMRPG_CONFIG_ROOTURL.'leaderboard/'.$this_playerinfo['user_name_clean'].'/posts/?sort=asc&amp;show='.$temp_post_show ?>" rel="nofollow" style="text-decoration: <?= $temp_post_sort == 'asc' ? 'underline' : 'none' ?>;">Old</a>
          </span>
        </p>
        <? if(!empty($this_posts_count)): ?>
          <p class="text" style="margin-top: -4px;">
            <?= $temp_post_show != 'all' && $temp_post_show < $this_posts_count ? 'Showing '.$temp_post_show.' '.($temp_post_sort == 'desc' ? 'Newest' : 'Oldest').' Posts' : 'Showing All Posts' ?>
          </span>
        <? endif; ?>
        <?
        // Collect markup for this post from the function
        foreach ($this_posts_array AS $this_post_key => $this_post_info){
          if (!isset($this_threads_index[$this_post_info['thread_id']])){ continue; }
          $this_thread_info = $this_threads_index[$this_post_info['thread_id']];
          $temp_markup = mmrpg_website_community_postblock($this_thread_info, $this_post_info, $this_category_info);
          echo $temp_markup."\n";
          if ($temp_post_show != 'all' && ($this_post_key + 1) >= $temp_post_show){ break; }
        }
        ?>
      </div>
      <?
    } else {
      ?>
      <div class="subbody posts_body post_right event event_triple event_visible" style="margin-top: -6px;">
        <p class="text">- there are no posts to display -</p>
        <p class="text" style="text-align: right;"><a class="postscroll" href="#top" style="right: 12px;">^ Top</a></p>
      </div>
      <?
    }

  }
  ?>

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
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>