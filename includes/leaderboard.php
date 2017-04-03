<?php

// Collect and define the display limit if set
if (!isset($this_display_limit_default)){ $this_display_limit_default = 50; }
$this_display_limit = !empty($_GET['limit']) ? trim($_GET['limit']) : $this_display_limit_default;
$this_start_key = !empty($_GET['start']) ? trim($_GET['start']) : 0;

// Define a function for parsing the leaderboard data
function mmrpg_leaderboard_parse_index($board_key, $board_info, $quick_parse = false){
    global $mmrpg_index;
    global $this_cache_stamp, $this_cache_filename, $this_cache_filedir;
    global $this_leaderboard_count, $this_leaderboard_online_count;
    global $this_leaderboard_online_players, $this_leaderboard_online_pages;
    global $this_leaderboard_index, $this_leaderboard_ranks_index;
    global $this_userid, $this_userinfo, $this_boardinfo;
    global $this_display_limit, $this_display_limit_default, $this_num_offset;
    global $this_time, $this_online_timeout, $place_counter, $points_counter, $this_start_key;

    // Collect this user's overall rank, regardless of sorting
    if (!empty($this_leaderboard_ranks_index[$board_info['user_id']])){
        //$place_counter = $this_leaderboard_ranks_index[$board_info['user_id']]['board_rank'];
        //$place_counter = (int)($place_counter);
    }

    // Collect this player's base info
    $this_players = !empty($board_info['user_battle_players']) ? $board_info['user_battle_players'] : 0;
    $this_robots = !empty($board_info['user_battle_robots']) ? $board_info['user_battle_robots'] : 0;
    $this_stars = !empty($board_info['user_battle_stars']) ? $board_info['user_battle_stars'] : 0;
    $this_abilities = !empty($board_info['user_battle_abilities']) ? $board_info['user_battle_abilities'] : 0;
    $this_items = !empty($board_info['user_battle_items']) ? $board_info['user_battle_items'] : 0;
    $this_missions = !empty($board_info['user_battle_missions']) ? $board_info['user_battle_missions'] : 0;
    $this_stars = !empty($board_info['user_battle_stars']) ? $board_info['user_battle_stars'] : 0;
    $this_database = !empty($board_info['user_database_percent']) ? $board_info['user_database_percent'] : 0;
    //$this_database = !empty($board_info['user_database_total']) ? $board_info['user_database_total'] : 0;
    $this_awards = !empty($board_info['board_awards']) ? explode(',', $board_info['board_awards']) : array();
    $this_first_save = !empty($board_info['board_date_created']) ? $board_info['board_date_created'] : 0;
    $this_last_save = !empty($board_info['board_date_modified']) ? $board_info['board_date_modified'] : 0;
    $this_last_access = !empty($board_info['user_date_accessed']) ? $board_info['user_date_accessed'] : 0;
    $this_is_online = !empty($this_last_access) && (($this_time - $this_last_access) <= $this_online_timeout) ? true : false;
    $this_last_save = !empty($this_last_save) ? date('Y/m/d @ H:i', $this_last_save) : '????-??-?? ??:??';
    $this_style = $this_is_online ? 'border-color: green; ' : '';
    $this_place = mmrpg_number_suffix($place_counter, true, true);
    $this_username = !empty($board_info['user_name_public']) ? $board_info['user_name_public'] : $board_info['user_name'];
    $this_username = htmlentities($this_username, ENT_QUOTES, 'UTF-8', true);
    $this_user_id = !empty($board_info['user_id']) ? $board_info['user_id'] : 0;
    if ($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_user_id == $_SESSION['GAME']['USER']['userid']){
        $this_boardinfo['board_rank'] = $place_counter;
        $_SESSION['GAME']['BOARD']['boardrank'] = $this_boardinfo['board_rank'];
    }

    //die('<pre>'.print_r($this_battles, true).'</pre>');

    // Define the current page this player is on
    $this_current_page_number = ceil($board_key / $this_display_limit_default);
    //echo('$board_key = '.$board_key.'; $this_display_limit_default = '.$this_display_limit_default.'; $this_current_page_number = '.$this_current_page_number);

    // If online, add this player to the array
    if ($this_is_online){
        $this_leaderboard_online_count++;
        $this_leaderboard_online_players[] = array('id' => $this_user_id, 'name' => $this_username, 'token' => $board_info['user_name_clean'], 'place' => $this_place, 'placeint' => $place_counter, 'colour' => $board_info['user_colour_token'], 'image' => $board_info['user_image_path']);
        //$this_current_page_number = ceil($board_key / $this_display_limit);
        //echo('$board_key = '.$board_key.'; $this_display_limit = '.$this_display_limit.'; $this_current_page_number = '.$this_current_page_number);
        //$this_leaderboard_online_pages[] = $board_key;
        if (!in_array($this_current_page_number, $this_leaderboard_online_pages)){ $this_leaderboard_online_pages[] = $this_current_page_number; }
    }

    // If quick parse was requested, return now
    if ($quick_parse){ return ''; }

    // Collect the points and increment the counter if necessary
    $this_points = $board_info['board_points'];
    if ($this_points != $points_counter){
        $points_counter = $this_points;
    }

    // Define the awards strong and default to empty
    $this_user_awards = ' ';

    // Break apart the battle and battle values into arrays
    $temp_battles = !empty($board_info['board_battles']) ? explode(',', $board_info['board_battles']) : array();
    $board_info['board_battles'] = $temp_battles;

    // Loop through the available players
    $mmrpg_index_players = rpg_player::get_index();
    foreach ($mmrpg_index_players AS $ptoken => $pinfo){
        $ptoken2 = str_replace('-', '_', $ptoken);
        $temp_battles = !empty($board_info['board_battles_'.$ptoken2]) ? explode(',', $board_info['board_battles_'.$ptoken2]) : array();
        $board_info['board_battles_'.$ptoken2] = $temp_battles;
    }

    // Start the output buffer
    ob_start();

    // Only continue if markup is special constants have not been defined
    if (!defined('MMRPG_SKIP_MARKUP') || defined('MMRPG_SHOW_MARKUP_'.$this_user_id)){

        // Only generate markup if we're withing the viewing range
        if ($board_key >= $this_start_key && $board_key < $this_display_limit || defined('MMRPG_SHOW_MARKUP_'.$this_user_id)){

            // Define a string variable to hold any record markup
            $this_records_html = '';

            // Print out the player count if the user has unlocked any
            if (!empty($this_players)){
                $this_players_count = $this_players == 1 ? '1 Player' : $this_players.' Players';
                if (!empty($this_records_html)){ $this_records_html .= '<span class="pipe">|</span> '; }
                $this_records_html .= '<span class="count players">'.$this_players_count.'</span> ';

            }

            // Print out the robot count if the user has unlocked any
            if (!empty($this_robots)){
                $this_robots_count = $this_robots == 1 ? '1 Robot' : $this_robots.' Robots';
                if (!empty($this_records_html)){ $this_records_html .= '<span class="pipe">|</span> '; }
                $this_records_html .= '<span class="count robots">'.$this_robots_count.'</span> ';
            }

            // Print out the ability count if the user has unlocked any
            if (!empty($this_abilities)){
                $this_abilities_count = $this_abilities == 1 ? '1 Ability' : $this_abilities.' Abilities';
                if (!empty($this_records_html)){ $this_records_html .= '<span class="pipe">|</span> '; }
                $this_records_html .= '<span class="count abilities">'.$this_abilities_count.'</span> ';
            }

            // Print out the item count if the user has collected any
            if (!empty($this_items)){
                $this_items_count = $this_items == 1 ? '1 Item' : $this_items.' Items';
                if (!empty($this_records_html)){ $this_records_html .= '<span class="pipe">|</span> '; }
                $this_records_html .= '<span class="count items">'.$this_items_count.'</span> ';
            }

            // Print out the star count if the user has collected any
            if (!empty($this_stars)){
                $this_stars_count = $this_stars == 1 ? '1 Star' : $this_stars.' Stars';
                if (!empty($this_records_html)){ $this_records_html .= '<span class="pipe">|</span> '; }
                $this_records_html .= '<span class="count stars">'.$this_stars_count.'</span> ';
            }

            // Print out the mission count if the user has completed any
            if (!empty($this_missions)){
                $this_missions_count = $this_missions == 1 ? '1 Mission' : $this_missions.' Missions';
                //if (!empty($this_records_html)){ $this_records_html .= '<span class="pipe">|</span> '; }
                //$this_records_html .= '<span class="count missions">'.$this_missions_count.'</span>';
            }

            /*
            // Print ou tthe database complete percent if the user has any
            if (!empty($this_database)){
                $this_database_percent = round($this_database).'% Database';
                //$this_database_count = $this_database == 1 ? '1 Data Entry' : $this_database.' Data Entries';
                if (!empty($this_records_html)){ $this_records_html .= '<span class="pipe">|</span> '; }
                $this_records_html .= '<span class="count database">'.$this_database_percent.'</span> ';
                //$this_records_html .= '<span class="count database">'.$this_database_count.'</span> ';
            }
            */

            // Generate the actual battle point total for display
            $this_points_html = '<span class="value">'.(!empty($this_points) ? number_format($this_points, 0, '.', ',') : 0).'</span>'.' BP';
            $this_points_plain = (!empty($this_points) ? number_format($this_points, 0, '.', ',') : 0).' BP';

            // Append the last save date to to the details markup
            $this_details = ''.$this_last_save;

            // -- LEADERBOARD MARKUP -- //

            // Add the prototype complete flags if applicable
            if (count($board_info['board_battles_dr_light']) >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){ $this_user_awards .= '<span class="prototype_complete prototype_complete_dr-light" data-tooltip="Completed Dr. Light\'s Game" data-tooltip-type="player_type player_type_defense">&hearts;</span>'; }
            if (count($board_info['board_battles_dr_wily']) >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){ $this_user_awards .= '<span class="prototype_complete prototype_complete_dr-wily" data-tooltip="Completed Dr. Wily\'s Game" data-tooltip-type="player_type player_type_attack">&clubs;</span>'; }
            if (count($board_info['board_battles_dr_cossack']) >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){ $this_user_awards .= '<span class="prototype_complete prototype_complete_dr-cossack" data-tooltip="Completed Dr. Cossack\'s Game" data-tooltip-type="player_type player_type_speed">&diams;</span>'; }
            if (in_array('ranking_first_place', $this_awards)){ $this_user_awards .= '<span class="prototype_complete prototype_complete_firstplace" data-tooltip="Reached First Place" data-tooltip-type="player_type player_type_level">&#9733;</span>'; }

            //die('$this_awards = '.print_r($this_awards, true));

            // Display the user's save file listing
            //echo '<a data-id="'.$board_info['user_id'].'" data-player="'.$board_info['user_name_clean'].'" class="file file_'.$this_place.'" name="file_'.$board_key.'" style="'.$this_style.'" title="'.$this_title.'" href="leaderboard/'.$board_info['user_name_clean'].'/">'."\n";
            echo '<a data-id="'.$board_info['user_id'].'" data-player="'.$board_info['user_name_clean'].'" class="file file_'.strip_tags($this_place).'" name="file_'.$board_key.'" style="'.$this_style.'" href="leaderboard/'.$board_info['user_name_clean'].'/">'."\n";
                echo '<div class="inset player_type_'.(!empty($board_info['user_colour_token']) ? $board_info['user_colour_token'] : 'none').'">'."\n";
                    echo '<span class="place">'.$this_place.'</span>'."\n";
                    echo '<span class="userinfo"><strong class="username">'.$this_username.$this_user_awards.'</strong><span class="details">'.$this_details.'</span></span>'."\n";
                    echo '<span class="points">'.$this_points_html.'</span>'."\n";
                    echo '<span class="records">'.$this_records_html.'</span>'."\n";
                echo '</div>'."\n";
                if (!empty($board_info['user_image_path'])){ list($avatar_class, $avatar_token, $avatar_size) = explode('/', $board_info['user_image_path']); }
                else { $avatar_class = 'robots'; $avatar_token = 'mega-man'; $avatar_size = 40; }
                if (!empty($board_info['user_background_path'])){ list($background_class, $background_token) = explode('/', $board_info['user_background_path']); }
                else { $background_class = 'fields'; $background_token = 'intro-field'; }
                $avatar_size = $avatar_size * 2;
                $avatar_path = 'images/'.$avatar_class.'/'.$avatar_token.'/sprite_left_'.$avatar_size.'x'.$avatar_size.'.png';
                $shadow_path = 'images/'.$avatar_class.'_shadows/'.preg_replace('/^([-a-z0-9]+)(_[a-z]+)?$/i', '$1', $avatar_token).'/sprite_left_'.$avatar_size.'x'.$avatar_size.'.png';
                if (!file_exists($shadow_path)){ $shadow_path = 'images/'.$avatar_class.'_shadows/'.preg_replace('/^([-a-z0-9]+)(_[a-z0-9]+)?$/i', '$1', $avatar_token).'/sprite_left_'.$avatar_size.'x'.$avatar_size.'.png'; }
                echo '<span class="avatar"><span class="avatar_wrapper">';
                echo '<span class="sprite sprite_shadow sprite_'.$avatar_size.'x'.$avatar_size.' sprite_shadow_'.$avatar_size.'x'.$avatar_size.' sprite_'.$avatar_size.'x'.$avatar_size.'_'.($place_counter > 3 ? 'base' : 'victory').'" style="background-image: url('.$shadow_path.'?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_username.'</span>';
                echo '<span class="sprite sprite_'.$avatar_size.'x'.$avatar_size.' sprite_'.$avatar_size.'x'.$avatar_size.'_'.($place_counter > 3 ? 'base' : 'victory').'" style="background-image: url('.$avatar_path.'?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_username.'</span>';
                echo '</span></span>'."\n";
            echo '</a>'."\n";



        }

    }

    // Collect the output into the buffer
    return preg_replace('/\s+/', ' ', ob_get_clean());

}

// Define a list of allowable sort paramaters for the leaderboard
$allowed_sort_types = array();
$allowed_sort_types['points'] = array('Point', 'Battle Points');
$allowed_sort_types['players'] = array('Player', 'Players Unlocked');
$allowed_sort_types['robots'] = array('Robot', 'Robots Unlocked');
$allowed_sort_types['abilities'] = array('Ability', 'Abilities Unlocked');
$allowed_sort_types['items'] = array('Item', 'Items Collected');
$allowed_sort_types['stars'] = array('Star', 'Stars Collected');
//$allowed_sort_types['database'] = array('Dataase', 'Database Completion');

// Collect any sorting arguments in the query URL
$leaderboard_sort_by = !empty($_REQUEST['sort']) && isset($allowed_sort_types[$_REQUEST['sort']]) ? trim($_REQUEST['sort']) : 'points';
$leaderboard_sort_order = !empty($_REQUEST['order']) ? trim($_REQUEST['order']) : 'desc';
$leaderboard_sort_by = strtolower($leaderboard_sort_by);
$leaderboard_sort_order = strtoupper($leaderboard_sort_order);

// Predefine the sorting query string based on the request
$temp_query_order = array();
if ($leaderboard_sort_by == 'players'){ $temp_query_order[] = 'user_battle_players '.$leaderboard_sort_order.' '; }
if ($leaderboard_sort_by == 'robots'){ $temp_query_order[] = 'user_battle_robots '.$leaderboard_sort_order.' '; }
if ($leaderboard_sort_by == 'abilities'){ $temp_query_order[] = 'user_battle_abilities '.$leaderboard_sort_order.' '; }
if ($leaderboard_sort_by == 'items'){ $temp_query_order[] = 'user_battle_items '.$leaderboard_sort_order.' '; }
if ($leaderboard_sort_by == 'stars'){ $temp_query_order[] = 'user_battle_stars '.$leaderboard_sort_order.' '; }
if ($leaderboard_sort_by == 'database'){ $temp_query_order[] = 'user_database_total '.$leaderboard_sort_order.' '; }
$temp_query_order[] = 'board.board_points DESC ';
$temp_query_order = implode(', ', $temp_query_order);

// Define a query for collecting all leaderboard players and their stats
$this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
$temp_leaderboard_query = "SELECT
    users.user_id,
    board.board_points,
    board.board_battles,
    board.board_battles_dr_light,
    board.board_battles_dr_wily,
    board.board_battles_dr_cossack,
    board.board_points AS user_battle_points,
    uplayers.user_players AS user_battle_players,
    urobots.user_robots AS user_battle_robots,
    ustars.user_stars AS user_battle_stars,
    uabilities.user_abilities AS user_battle_abilities,
    uitems.user_items AS user_battle_items,
    board.board_missions AS user_battle_missions,
    board.board_awards,
    board.board_date_created,
    board.board_date_modified,
    users.user_name,
    users.user_name_clean,
    users.user_name_public,
    users.user_colour_token,
    users.user_image_path,
    users.user_background_path,
    users.user_date_accessed,
    (users.user_date_accessed > 0 AND ((UNIX_TIMESTAMP() - users.user_date_accessed) <= {$this_online_timeout})) AS user_is_online
    FROM mmrpg_users AS users
    LEFT JOIN mmrpg_leaderboard AS board ON users.user_id = board.user_id
    LEFT JOIN (SELECT
        uplayers.user_id,
        COUNT(*) AS user_players
        FROM mmrpg_users_players AS uplayers
        GROUP BY user_id
        ) AS uplayers ON uplayers.user_id = users.user_id
    LEFT JOIN (SELECT
        urobots.user_id,
        COUNT(*) AS user_robots
        FROM mmrpg_users_robots AS urobots
        GROUP BY user_id
        ) AS urobots ON urobots.user_id = users.user_id
    LEFT JOIN (SELECT
        ustars.user_id,
        COUNT(*) AS user_stars
        FROM mmrpg_users_stars AS ustars
        GROUP BY user_id
        ) AS ustars ON ustars.user_id = users.user_id
    LEFT JOIN (SELECT
        uabilities.user_id,
        COUNT(*) AS user_abilities
        FROM mmrpg_users_abilities AS uabilities
        GROUP BY user_id
        ) AS uabilities ON uabilities.user_id = users.user_id
    LEFT JOIN (SELECT
        uitems.user_id,
        COUNT(*) AS user_items
        FROM mmrpg_users_items AS uitems
        GROUP BY user_id
        ) AS uitems ON uitems.user_id = users.user_id
    WHERE board.board_points > 0
    ORDER BY
    {$temp_query_order}
    ;";

/*

    udatabase.user_robots AS user_database_total,
    rtotal.total_robots AS global_database_total,
    ((udatabase.user_robots / rtotal.total_robots) * 100) AS user_database_percent,

    LEFT JOIN (SELECT
        urobots.user_id,
        COUNT(*) AS user_robots
        FROM mmrpg_users_robots_database AS urobots
        GROUP BY user_id
        ) AS udatabase ON udatabase.user_id = users.user_id
    LEFT JOIN (SELECT
        COUNT(*) AS total_robots
        FROM mmrpg_index_robots AS robots
        WHERE robots.robot_flag_published = 1 AND robots.robot_flag_hidden <> 1) AS rtotal ON rtotal.total_robots IS NOT NULL

 */

// Define a query for specifically selecting leaderboard ranks
$temp_leaderboard_ranks_query = "SELECT
    users.user_id,
    users.user_name_clean,
    ranks.board_points,
    ranks.board_rank
    FROM mmrpg_users AS users
    LEFT JOIN mmrpg_leaderboard AS board ON users.user_id = board.user_id
    LEFT JOIN (SELECT
        board.board_points AS board_points,
        @rank:=@rank+1 AS board_rank
        FROM mmrpg_leaderboard AS board
        GROUP BY board.board_points
        ORDER BY board.board_points DESC) AS ranks ON ranks.board_points = board.board_points
    WHERE board.board_points > 0
    ORDER BY board.board_points DESC
    ;";

// Query the database and collect the array list of all non-bogus players
$db->query("SET @rank=0;");
$this_leaderboard_index = $db->get_array_list($temp_leaderboard_query);
$this_leaderboard_ranks_index = $db->get_array_list($temp_leaderboard_ranks_query, 'user_id');

//echo('<pre>$temp_leaderboard_query = '.htmlentities($temp_leaderboard_query, ENT_QUOTES, 'UTF-8', true).'</pre>');
//echo('<pre>$this_leaderboard_index = '.print_r($this_leaderboard_index, true).'</pre>');
//echo('<pre>$temp_leaderboard_ranks_query = '.htmlentities($temp_leaderboard_ranks_query, ENT_QUOTES, 'UTF-8', true).'</pre>');
//echo('<pre>$this_leaderboard_ranks_index = '.print_r($this_leaderboard_ranks_index, true).'</pre>');
//exit();

// Loop through the save file directory and generate an index
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
    if (!empty($this_leaderboard_index)){
        $this_time = time();
        $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
        $place_counter = 0;
        $points_counter = 0;
        foreach ($this_leaderboard_index AS $board_key => $board_info){
            $place_counter += 1;
            $quick_parse = true;
            if (defined('MMRPG_SHOW_MARKUP_'.$board_info['user_id'])){ $quick_parse = false; }
            elseif ($board_key >= $this_start_key && $board_key < ($this_start_key + $this_display_limit)){ $quick_parse = false; }
            $this_leaderboard_markup[] = mmrpg_leaderboard_parse_index($board_key, $board_info, $quick_parse);
        }
    }
}

/*
die(
'$_REQUEST = <pre>'.print_r($_REQUEST, true).'</pre>'.
//'$this_leaderboard_index = <pre>'.print_r($this_leaderboard_index, true).'</pre>'.
'$this_leaderboard_markup = <pre>'.print_r($this_leaderboard_markup, true).'</pre>'
);
*/

?>