<?

// Collect and define the display limit if set
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
if (!isset($this_display_limit_default)){ $this_display_limit_default = 50; }
$this_display_limit = !empty($_GET['limit']) ? trim($_GET['limit']) : $this_display_limit_default;
$this_start_key = !empty($_GET['start']) ? trim($_GET['start']) : 0;

// Define a function for parsing the leaderboard data
function mmrpg_leaderboard_parse_index($key, $board){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'mmrpg_leaderboard_parse_index('.$key.', $board:'.$board['user_name_clean'].')');  }
  global $DB, $mmrpg_index;
  global $this_cache_stamp, $this_cache_filename, $this_cache_filedir;
  global $this_leaderboard_count, $this_leaderboard_online_count;
  global $this_leaderboard_online_players, $this_leaderboard_online_pages;
  global $this_leaderboard_index, $this_leaderboard_markup;
  global $this_userid, $this_userinfo, $this_boardinfo;
  global $this_display_limit, $this_display_limit_default, $this_num_offset;
  global $this_time, $this_online_timeout, $place_counter, $points_counter, $this_start_key;

  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

  $board_key = $key;

  // Start the output buffer
  ob_start();

  // Collect the points and increment the counter if necessary
  $this_points = $board['board_points'];
  if ($this_points != $points_counter){
    $points_counter = $this_points;
    $place_counter += 1;
  }

  // Define the awards strong and default to empty
  $this_user_awards = ' ';

  // Break apart the battle and battle values into arrays
  $temp_battles = !empty($board['board_battles']) ? explode(',', $board['board_battles']) : array();
  $board['board_battles'] = $temp_battles;

  // Loop through the available players
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  foreach ($mmrpg_index['players'] AS $ptoken => $pinfo){
    $ptoken2 = str_replace('-', '_', $ptoken);
    $temp_battles = !empty($board['board_battles_'.$ptoken2]) ? explode(',', $board['board_battles_'.$ptoken2]) : array();
    $board['board_battles_'.$ptoken2] = $temp_battles;
  }

  // Break apart the robot and battle values into arrays
  $temp_robots = !empty($board['board_robots']) ? $board['board_robots'] : array();
  if (!empty($temp_robots)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    $temp_robots = explode(',', $temp_robots);
    foreach ($temp_robots AS $key => $string){
      list($token, $level) = explode(':', substr($string, 1, -1));
      $temp_info = array('robot_token' => $token, 'robot_level' => $level);
      $temp_robots[$key] = $temp_info;
    }
  }
  // Collect this player's robots
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this_robots = $temp_robots;
  $this_stars = !empty($board['board_stars']) ? $board['board_stars'] : 0;
  $this_abilities = !empty($board['board_abilities']) ? $board['board_abilities'] : 0;
  $this_missions = !empty($board['board_missions']) ? $board['board_missions'] : 0;
  $this_awards = !empty($board['board_awards']) ? explode(',', $board['board_awards']) : array();
  $this_first_save = !empty($board['board_date_created']) ? $board['board_date_created'] : 0;
  $this_last_save = !empty($board['board_date_modified']) ? $board['board_date_modified'] : 0;
  $this_last_access = !empty($board['user_date_accessed']) ? $board['user_date_accessed'] : 0;
  $this_is_online = !empty($this_last_access) && (($this_time - $this_last_access) <= $this_online_timeout) ? true : false;
  $this_last_save = !empty($this_last_save) ? date('Y/m/d @ H:i', $this_last_save) : '????-??-?? ??:??';
  $this_style = $this_is_online ? 'border-color: green; ' : '';
  $this_place = mmrpg_number_suffix($place_counter, true, true); //str_pad(($place_counter), 2, '0', STR_PAD_LEFT);
  $this_username = !empty($board['user_name_public']) ? $board['user_name_public'] : $board['user_name'];
  $this_username = htmlentities($this_username, ENT_QUOTES, 'UTF-8', true);
  $this_user_id = !empty($board['user_id']) ? $board['user_id'] : 0;
  if ($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_user_id == $_SESSION['GAME']['USER']['userid']){
    $this_boardinfo['board_rank'] = $place_counter;
    $_SESSION['GAME']['BOARD']['boardrank'] = $this_boardinfo['board_rank'];
  }

  // If online, add this player to the array
  if ($this_is_online){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    $this_leaderboard_online_count++;
    $this_leaderboard_online_players[] = array('id' => $this_user_id, 'name' => $this_username, 'token' => $board['user_name_clean'], 'place' => $this_place, 'placeint' => $place_counter, 'colour' => $board['user_colour_token'], 'image' => $board['user_image_path']);
    $this_current_page_number = ceil($board_key / $this_display_limit);
    //$this_leaderboard_online_pages[] = $board_key;
    if (!in_array($this_current_page_number, $this_leaderboard_online_pages)){ $this_leaderboard_online_pages[] = $this_current_page_number; }
  }

  // Only continue if markup is special constants have not been defined
  if (!defined('MMRPG_SKIP_MARKUP') || defined('MMRPG_SHOW_MARKUP_'.$this_user_id)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

    // Only generate markup if we're withing the viewing range
    if ($board_key >= $this_start_key && $board_key < $this_display_limit || defined('MMRPG_SHOW_MARKUP_'.$this_user_id)){
      if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

      $this_robots_count = (!empty($this_robots) ? count($this_robots) : 0);
      $this_robots_count = $this_robots_count == 1 ? '1 Robot' : $this_robots_count.' Robots';
      $this_stars_count = $this_stars;
      $this_abilities_count = $this_abilities;
      $this_missions_count = $this_missions;

      $this_stars_count = $this_stars_count == 1 ? '1 Star' : $this_stars_count.' Stars';
      $this_abilities_count = $this_abilities_count == 1 ? '1 Ability' : $this_abilities_count.' Abilities';
      $this_missions_count = $this_missions_count == 1 ? '1 Mission' : $this_missions_count.' Missions';

      //$this_points_html = preg_replace('#^([0]+)([0-9]+)$#', '<span class="padding">$1</span><span class="value">$2</span>', str_pad((!empty($this_points) ? $this_points : 0), 13, '0', STR_PAD_LEFT)).' BP';
      $this_records_html = '<span class="count">'.$this_missions_count.'</span>';
      $this_records_html .= ' <span class="pipe">|</span> <span class="count">'.$this_robots_count.'</span>';
      $this_records_html .= ' <span class="pipe">|</span> <span class="count">'.$this_abilities_count.'</span>';
      $this_records_html .= ' <span class="pipe">|</span> <span class="count">'.$this_stars_count.'</span>';
      $this_points_html = '<span class="value">'.(!empty($this_points) ? number_format($this_points, 0, '.', ',') : 0).'</span>'.' BP';
      $this_points_plain = (!empty($this_points) ? number_format($this_points, 0, '.', ',') : 0).' BP';

      $this_details = ''.$this_last_save;

      // If this player is in first/second/third place but hasn't received the award...
      $this_awards_string = '';
      if ($this_place == 1 && !in_array('ranking_first_place', $this_awards)){
        // FIRST PLACE
        $this_awards[] = 'ranking_first_place';
        $this_awards_string = implode(',', $this_awards);
      } elseif ($this_place == 2 && !in_array('ranking_second_place', $this_awards)){
        // SECOND PLACE
        $this_awards[] = 'ranking_second_place';
        $this_awards_string = implode(',', $this_awards);
      } elseif ($this_place == 3 && !in_array('ranking_third_place', $this_awards)){
        // THIRD PLACE
        $this_awards[] = 'ranking_third_place';
        $this_awards_string = implode(',', $this_awards);
      }
      if (!empty($this_awards_string)){
        $DB->query("UPDATE mmrpg_leaderboard SET board_awards = '{$this_awards_string}' WHERE user_id = {$board['user_id']};");
      }

      // -- LEADERBOARD MARKUP -- //

      // Add the prototype complete flags if applicable
      if (count($board['board_battles_dr_light']) >= 17){ $this_user_awards .= '<span class="prototype_complete prototype_complete_dr-light" data-tooltip="Completed Dr. Light\'s Game" data-tooltip-type="player_type player_type_defense">&hearts;</span>'; }
      if (count($board['board_battles_dr_wily']) >= 17){ $this_user_awards .= '<span class="prototype_complete prototype_complete_dr-wily" data-tooltip="Completed Dr. Wily\'s Game" data-tooltip-type="player_type player_type_attack">&clubs;</span>'; }
      if (count($board['board_battles_dr_cossack']) >= 17){ $this_user_awards .= '<span class="prototype_complete prototype_complete_dr-cossack" data-tooltip="Completed Dr. Cossack\'s Game" data-tooltip-type="player_type player_type_speed">&diams;</span>'; }
      if (in_array('ranking_first_place', $this_awards)){ $this_user_awards .= '<span class="prototype_complete prototype_complete_firstplace" data-tooltip="Reached First Place" data-tooltip-type="player_type player_type_level">&#9733;</span>'; }

      //die('$this_awards = '.print_r($this_awards, true));

      // Display the user's save file listing
      //echo '<a data-id="'.$board['user_id'].'" data-player="'.$board['user_name_clean'].'" class="file file_'.$this_place.'" name="file_'.$key.'" style="'.$this_style.'" title="'.$this_title.'" href="leaderboard/'.$board['user_name_clean'].'/">'."\n";
      echo '<a data-id="'.$board['user_id'].'" data-player="'.$board['user_name_clean'].'" class="file file_'.strip_tags($this_place).'" name="file_'.$key.'" style="'.$this_style.'" href="leaderboard/'.$board['user_name_clean'].'/">'."\n";
        echo '<div class="inset player_type_'.(!empty($board['user_colour_token']) ? $board['user_colour_token'] : 'none').'">'."\n";
          echo '<span class="place">'.$this_place.'</span>'."\n";
          echo '<span class="userinfo"><span class="username">'.$this_username.$this_user_awards.'</span><span class="details">'.$this_details.'</span></span>'."\n";
          echo '<span class="points">'.$this_points_html.'</span>'."\n";
          echo '<span class="records">'.$this_records_html.'</span>'."\n";
        echo '</div>'."\n";
        if (!empty($board['user_image_path'])){ list($avatar_class, $avatar_token, $avatar_size) = explode('/', $board['user_image_path']); }
        else { $avatar_class = 'robots'; $avatar_token = 'mega-man'; $avatar_size = 40; }
        if (!empty($board['user_background_path'])){ list($background_class, $background_token) = explode('/', $board['user_background_path']); }
        else { $background_class = 'fields'; $background_token = 'intro-field'; }
        $avatar_size = $avatar_size * 2;
        echo '<span class="avatar"><span class="avatar_wrapper">';
        echo '<span class="sprite sprite_shadow sprite_'.$avatar_size.'x'.$avatar_size.' sprite_shadow_'.$avatar_size.'x'.$avatar_size.' sprite_'.$avatar_size.'x'.$avatar_size.'_'.($place_counter > 3 ? 'base' : 'victory').'" style="background-image: url(images/'.$avatar_class.'_shadows/'.preg_replace('/^([-a-z0-9]+)(_[a-z]+)?$/i', '$1', $avatar_token).'/sprite_left_'.$avatar_size.'x'.$avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_username.'</span>';
        echo '<span class="sprite sprite_'.$avatar_size.'x'.$avatar_size.' sprite_'.$avatar_size.'x'.$avatar_size.'_'.($place_counter > 3 ? 'base' : 'victory').'" style="background-image: url(images/'.$avatar_class.'/'.$avatar_token.'/sprite_left_'.$avatar_size.'x'.$avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_username.'</span>';
        echo '</span></span>'."\n";
      echo '</a>'."\n";

    }

  }

  // Collect the output into the buffer
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  $this_leaderboard_markup[] = preg_replace('/\s+/', ' ', ob_get_clean());

  /*
  // -- LEADERBOARD XML -- //

  // Start the output buffer
  ob_start();

  // Display the user's save file listing
  echo '<player>'."\n";
    echo '<nickname>'.$this_username.'</nickname>'."\n";
    echo '<join_date>'.date('Y-m-d H:i:s', $this_first_save).'</join_date>'."\n";
    echo '<score>'.$this_points.'</score>'."\n";
  echo '</player>'."\n";

  // Collect the output into the buffer
  $this_leaderboard_xml[] = trim(ob_get_clean())."\n";
  */

  /*
  die(
  '$this_leaderboard_markup['.$key.'] = <pre style="border-left: 5px solid yellow;">'.htmlentities(print_r($this_leaderboard_markup[$key], true), ENT_QUOTES, 'UTF-8', true).'</pre><hr /><hr />'.
  '$this_robots = <pre style="border-left: 5px solid green;">'.htmlentities(print_r($this_robots, true), ENT_QUOTES, 'UTF-8', true).'</pre><hr /><hr />'.
  '$board = <pre style="border-left: 5px solid yellow;">'.print_r($board, true).'</pre><hr /><hr />'.
  '$this_leaderboard_index = <pre style="border-left: 5px solid blue;">'.print_r($this_leaderboard_index, true).'</pre><hr /><hr />'.
  '$this_leaderboard_markup = <pre style="border-left: 5px solid red;">'.print_r($this_leaderboard_markup, true).'</pre><hr /><hr />'
  );
  */

  // Return true on success
  return true;

}

// Define the array for pulling all the leaderboard data
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$temp_leaderboard_query = 'SELECT
  mmrpg_users.user_id,
  mmrpg_users.user_name,
  mmrpg_users.user_name_clean,
  mmrpg_users.user_name_public,
  mmrpg_users.user_colour_token,
  mmrpg_users.user_image_path,
  mmrpg_users.user_background_path,
  mmrpg_users.user_date_accessed,
  mmrpg_leaderboard.board_id,
  mmrpg_leaderboard.board_points,
  mmrpg_leaderboard.board_points_dr_light,
  mmrpg_leaderboard.board_points_dr_wily,
  mmrpg_leaderboard.board_points_dr_cossack,
  mmrpg_leaderboard.board_robots,
  mmrpg_leaderboard.board_robots_dr_light,
  mmrpg_leaderboard.board_robots_dr_wily,
  mmrpg_leaderboard.board_robots_dr_cossack,
  mmrpg_leaderboard.board_battles,
  mmrpg_leaderboard.board_battles_dr_light,
  mmrpg_leaderboard.board_battles_dr_wily,
  mmrpg_leaderboard.board_battles_dr_cossack,
  mmrpg_leaderboard.board_stars,
  mmrpg_leaderboard.board_stars_dr_light,
  mmrpg_leaderboard.board_stars_dr_wily,
  mmrpg_leaderboard.board_stars_dr_cossack,
  mmrpg_leaderboard.board_abilities,
  mmrpg_leaderboard.board_abilities_dr_light,
  mmrpg_leaderboard.board_abilities_dr_wily,
  mmrpg_leaderboard.board_abilities_dr_cossack,
  mmrpg_leaderboard.board_missions,
  mmrpg_leaderboard.board_missions_dr_light,
  mmrpg_leaderboard.board_missions_dr_wily,
  mmrpg_leaderboard.board_missions_dr_cossack,
  mmrpg_leaderboard.board_awards,
  mmrpg_leaderboard.board_date_created,
  mmrpg_leaderboard.board_date_modified
  FROM mmrpg_users
  LEFT JOIN mmrpg_leaderboard ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
  WHERE mmrpg_leaderboard.board_points > 0
  ORDER BY mmrpg_leaderboard.board_points DESC
  '; //.(!empty($this_display_limit) ? 'LIMIT '.$this_display_limit : '');
// Query the database and collect the array list of all non-bogus players
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$this_leaderboard_index = $DB->get_array_list($temp_leaderboard_query);

// Loop through the save file directory and generate an index
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$this_cache_stamp = MMRPG_CONFIG_CACHE_DATE.'_'.substr(date('YmdHi'), 0, 11); //2013 01 01 23 59 (12 length)
$this_cache_filename = 'cache.leaderboard.'.$this_cache_stamp.'.php';
$this_cache_filedir = $this_cache_dir.$this_cache_filename;
$this_leaderboard_count = count($this_leaderboard_index);
$this_leaderboard_online_count = 0;
$this_leaderboard_online_players = array();
$this_leaderboard_online_pages = array();
$this_leaderboard_markup = array();
//$this_leaderboard_xml = array();
if (true){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$this_display_limit = '.$this_display_limit.'; $this_start_key = '.$this_start_key.'; ');  }
  if (!empty($this_leaderboard_index)){
    $this_time = time();
    $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
    $place_counter = 0;
    $points_counter = 0;
    foreach ($this_leaderboard_index AS $key => $board){
      mmrpg_leaderboard_parse_index($key, $board);
    }
  }
}

/*
die(
'$this_leaderboard_index = <pre>'.print_r($this_leaderboard_index, true).'</pre>'.
'$this_leaderboard_markup = <pre>'.print_r($this_leaderboard_markup, true).'</pre>'
);
*/

if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
?>