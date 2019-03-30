<?

// Collect and define the display limit if set
if (!isset($this_display_limit_default)){ $this_display_limit_default = 50; }
$this_display_limit = !empty($_GET['limit']) ? trim($_GET['limit']) : $this_display_limit_default;
$this_start_key = !empty($_GET['start']) ? trim($_GET['start']) : 0;

// Define a function for parsing the leaderboard data
function mmrpg_leaderboard_parse_index($key, $board, $place_counter){
    global $db, $mmrpg_index;
    global $this_userid, $this_userinfo, $this_boardinfo;
    global $this_display_limit, $this_num_offset;
    global $this_time, $this_start_key;

    $board_key = $key;

    // Collect the points
    $this_points = $board['board_points'];

    // Define the awards strong and default to empty
    $this_user_awards = ' ';

    // Break apart the battle and battle values into arrays
    $temp_battles = !empty($board['board_battles']) ? explode(',', $board['board_battles']) : array();
    $board['board_battles'] = $temp_battles;

    // Loop through the available players
    foreach ($mmrpg_index['players'] AS $ptoken => $pinfo){
        $ptoken2 = str_replace('-', '_', $ptoken);
        $temp_battles = !empty($board['board_battles_'.$ptoken2]) ? explode(',', $board['board_battles_'.$ptoken2]) : array();
        $board['board_battles_'.$ptoken2] = $temp_battles;
    }

    // Collect this player's robots
    $this_robots = !empty($board['board_robots_count']) ? $board['board_robots_count'] : 0;
    $this_items = !empty($board['board_items']) ? $board['board_items'] : 0;
    $this_abilities = !empty($board['board_abilities']) ? $board['board_abilities'] : 0;
    $this_stars = !empty($board['board_stars']) ? $board['board_stars'] : 0;
    $this_awards = !empty($board['board_awards']) ? explode(',', $board['board_awards']) : array();
    $this_first_save = !empty($board['board_date_created']) ? $board['board_date_created'] : 0;
    $this_last_save = !empty($board['board_date_modified']) ? $board['board_date_modified'] : 0;
    $this_last_access = !empty($board['user_date_accessed']) ? $board['user_date_accessed'] : 0;
    $this_is_online = !empty($board['user_is_online']) ? true : false;
    $this_last_save = !empty($this_last_save) ? date('Y/m/d @ H:i', $this_last_save) : '????-??-?? ??:??';
    $this_style = $this_is_online ? 'border-color: green; ' : '';
    $this_username = !empty($board['user_name_public']) && !empty($board['user_flag_postpublic']) ? $board['user_name_public'] : $board['user_name'];
    $this_username = htmlentities($this_username, ENT_QUOTES, 'UTF-8', true);
    $this_user_id = !empty($board['user_id']) ? $board['user_id'] : 0;
    if ($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_user_id == $_SESSION['GAME']['USER']['userid']){
        $this_boardinfo['board_rank'] = $place_counter;
        $_SESSION['GAME']['BOARD']['boardrank'] = $this_boardinfo['board_rank'];
    }

    // Only continue if markup is special constants have not been defined
    if (!defined('MMRPG_SKIP_MARKUP') || defined('MMRPG_SHOW_MARKUP_'.$this_user_id)){

        // Only generate markup if we're withing the viewing range
        if ($board_key >= $this_start_key && $board_key < $this_display_limit || defined('MMRPG_SHOW_MARKUP_'.$this_user_id)){



            $this_robots_count = $this_robots === 1 ? '1 Robot' : $this_robots.' Robots';
            $this_abilities_count = $this_abilities === 1 ? '1 Ability' : $this_abilities.' Abilities';
            $this_items_count = $this_items === 1 ? '1 Item' : $this_items.' Items';
            $this_stars_count = $this_stars === 1 ? '1 Star' : $this_stars.' Stars';

            $this_records_html = array();
            if (!empty($this_robots_count)){ $this_records_html[] = '<span class="count robots">'.$this_robots_count.'</span>'; }
            if (!empty($this_abilities_count)){ $this_records_html[] = '<span class="count abilities">'.$this_abilities_count.'</span>'; }
            if (!empty($this_items_count)){ $this_records_html[] = '<span class="count items">'.$this_items_count.'</span>'; }
            if (!empty($this_stars_count)){ $this_records_html[] = '<span class="count stars">'.$this_stars_count.'</span>'; }
            $this_records_html = implode(' <span class="pipe">|</span> ', $this_records_html);

            $this_points_html = '<span class="value">'.(!empty($this_points) ? number_format($this_points, 0, '.', ',') : 0).'</span>'.' BP';
            $this_points_plain = (!empty($this_points) ? number_format($this_points, 0, '.', ',') : 0).' BP';

            $this_details = ''.$this_last_save;

            // If this player is in first/second/third place but hasn't received the award...
            $this_awards_string = '';
            if ($place_counter == 1 && !in_array('ranking_first_place', $this_awards)){
                // FIRST PLACE
                $this_awards[] = 'ranking_first_place';
                $this_awards_string = implode(',', $this_awards);
            } elseif ($place_counter == 2 && !in_array('ranking_second_place', $this_awards)){
                // SECOND PLACE
                $this_awards[] = 'ranking_second_place';
                $this_awards_string = implode(',', $this_awards);
            } elseif ($place_counter == 3 && !in_array('ranking_third_place', $this_awards)){
                // THIRD PLACE
                $this_awards[] = 'ranking_third_place';
                $this_awards_string = implode(',', $this_awards);
            }
            if (!empty($this_awards_string)){
                $db->query("UPDATE mmrpg_leaderboard SET board_awards = '{$this_awards_string}' WHERE user_id = {$board['user_id']};");
            }

            // -- LEADERBOARD MARKUP -- //

            // Add the prototype complete flags if applicable
            if (in_array('ranking_first_place', $this_awards)){ $this_user_awards .= '<span class="prototype_complete prototype_complete_firstplace" data-tooltip="Reached First Place" data-tooltip-type="player_type player_type_level">&#9733;</span>'; }
            if (in_array('prototype_complete_light', $this_awards)){ $this_user_awards .= '<span class="prototype_complete prototype_complete_dr-light" data-tooltip="Completed Dr. Light\'s Game" data-tooltip-type="player_type player_type_defense">&hearts;</span>'; }
            if (in_array('prototype_complete_wily', $this_awards)){ $this_user_awards .= '<span class="prototype_complete prototype_complete_dr-wily" data-tooltip="Completed Dr. Wily\'s Game" data-tooltip-type="player_type player_type_attack">&clubs;</span>'; }
            if (in_array('prototype_complete_cossack', $this_awards)){ $this_user_awards .= '<span class="prototype_complete prototype_complete_dr-cossack" data-tooltip="Completed Dr. Cossack\'s Game" data-tooltip-type="player_type player_type_speed">&diams;</span>'; }

            // Start the output buffer
            ob_start();

            // Display the user's save file listing
            $this_place = mmrpg_number_suffix($place_counter, true, true);
            echo '<a data-id="'.$board['user_id'].'" data-player="'.$board['user_name_clean'].'" class="file file_'.strip_tags($this_place).'" name="file_'.$key.'" style="'.$this_style.'" href="leaderboard/'.$board['user_name_clean'].'/">'."\n";
                echo '<div class="inset player_type_'.(!empty($board['user_colour_token']) ? $board['user_colour_token'] : 'none').'">'."\n";
                    echo '<span class="place">'.$this_place.'</span>'."\n";
                    echo '<span class="userinfo"><span class="username">'.$this_username.$this_user_awards.'</span><span class="details">'.$this_details.'</span></span>'."\n";
                    echo '<span class="points">'.$this_points_html.'</span>'."\n";
                    echo '<span class="records">'.$this_records_html.'</span>'."\n";
                echo '</div>'."\n";
                if (!empty($board['user_image_path'])){ list($avatar_class, $avatar_token, $avatar_base_size) = explode('/', $board['user_image_path']); }
                else { $avatar_class = 'robots'; $avatar_token = 'mega-man'; $avatar_base_size = 40; }
                if (!empty($board['user_background_path'])){ list($background_class, $background_token) = explode('/', $board['user_background_path']); }
                else { $background_class = 'fields'; $background_token = 'intro-field'; }
                $avatar_size = $avatar_base_size * 2;
                echo '<span class="avatar"><span class="avatar_wrapper">';
                echo '<span class="sprite sprite_shadow sprite_'.$avatar_size.'x'.$avatar_size.' sprite_shadow_'.$avatar_size.'x'.$avatar_size.' sprite_'.$avatar_size.'x'.$avatar_size.'_'.($place_counter > 3 ? 'base' : 'victory').'" style="background-image: url(images/'.$avatar_class.'_shadows/'.preg_replace('/^([-a-z0-9]+)(_[a-z0-9]+)?$/i', '$1', $avatar_token).'/sprite_left_'.$avatar_size.'x'.$avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-size: auto '.$avatar_size.'px;">'.$this_username.'</span>';
                echo '<span class="sprite sprite_'.$avatar_size.'x'.$avatar_size.' sprite_'.$avatar_size.'x'.$avatar_size.'_'.($place_counter > 3 ? 'base' : 'victory').'" style="background-image: url(images/'.$avatar_class.'/'.$avatar_token.'/sprite_left_'.$avatar_size.'x'.$avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-size: auto '.$avatar_size.'px;">'.$this_username.'</span>';
                echo '</span></span>'."\n";
            echo '</a>'."\n";

            // Collect the output from the buffer and return
            $this_leaderboard_markup = preg_replace('/\s+/', ' ', ob_get_clean());
            return $this_leaderboard_markup;

        }

    }

}

// Define the array for pulling all the leaderboard data
$this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
$temp_leaderboard_query = "SELECT
    users.user_id,
    users.user_name,
    users.user_name_clean,
    users.user_name_public,
    users.user_colour_token,
    users.user_image_path,
    users.user_background_path,
    users.user_date_accessed,
    (users.user_date_accessed > 0 AND ((UNIX_TIMESTAMP() - users.user_date_accessed) <= {$this_online_timeout})) AS user_is_online,
    board.board_id,
    board.board_points,
    board.board_points_dr_light,
    board.board_points_dr_wily,
    board.board_points_dr_cossack,
    board.board_items,
    board.board_robots,
    board.board_robots_dr_light,
    board.board_robots_dr_wily,
    board.board_robots_dr_cossack,
    board.board_robots_count,
    board.board_battles,
    board.board_battles_dr_light,
    board.board_battles_dr_wily,
    board.board_battles_dr_cossack,
    board.board_stars,
    board.board_stars_dr_light,
    board.board_stars_dr_wily,
    board.board_stars_dr_cossack,
    board.board_abilities,
    board.board_abilities_dr_light,
    board.board_abilities_dr_wily,
    board.board_abilities_dr_cossack,
    board.board_missions,
    board.board_missions_dr_light,
    board.board_missions_dr_wily,
    board.board_missions_dr_cossack,
    board.board_awards,
    board.board_date_created,
    board.board_date_modified
    FROM mmrpg_users AS users
    LEFT JOIN mmrpg_leaderboard AS board ON users.user_id = board.user_id
    WHERE board.board_points > 0
    ORDER BY board.board_points DESC
    ";

// Query the database and collect the array list of all non-bogus players
$this_leaderboard_index = $db->get_array_list($temp_leaderboard_query);

// Loop through the save file directory and generate an index
$this_cache_stamp = MMRPG_CONFIG_CACHE_DATE.'_'.substr(date('YmdHi'), 0, 11); //2013 01 01 23 59 (12 length)
$this_cache_filename = 'cache.leaderboard.'.$this_cache_stamp.'.php';
$this_cache_filedir = $this_cache_dir.$this_cache_filename;
$this_leaderboard_count = count($this_leaderboard_index);
$this_leaderboard_online_count = 0;
$this_leaderboard_online_players = array();
$this_leaderboard_online_pages = array();
$this_leaderboard_markup = array();

// Ensure the leaderboard array is not empty before continuing
if (!empty($this_leaderboard_index)){
    $this_time = time();
    $last_points = 0;
    $place_counter = 0;

    // Loop through the leaderboard array and print out any markup
    foreach ($this_leaderboard_index AS $key => $board){
        //echo("\n\n<!-- \$this_leaderboard_index[{$key}] -->\n");

        // Collect the points and increment the place counter if necessary
        $this_points = $board['board_points'];
        if ($this_points != $last_points){
            $last_points = $this_points;
            $place_counter += 1;
        }

        // Define the variable for this leaderboard markup
        $this_markup = '';

        // If this user is online, at least track it's data
        if (!empty($board['user_is_online'])){
            //echo("<!-- !empty(\$board['user_is_online']) -->\n");
            $this_leaderboard_online_count++;
            $this_current_page_number = ceil($key / $this_display_limit_default);
            if (!in_array($this_current_page_number, $this_leaderboard_online_pages)){ $this_leaderboard_online_pages[] = $this_current_page_number; }
            $this_leaderboard_online_players[] = array(
                'id' => $board['user_id'],
                'name' => !empty($board['user_name_public']) ? $board['user_name_public'] : $board['user_name'],
                'token' => $board['user_name_clean'],
                'place' => mmrpg_number_suffix($place_counter, true, true),
                'placeint' => $place_counter,
                'colour' => $board['user_colour_token'],
                'image' => $board['user_image_path'],
                'page' => $this_current_page_number
                );
        }

        // If this user was requested specifically, generate markup
        if (defined('MMRPG_SHOW_MARKUP_'.$board['user_id'])){
            //echo("<!-- defined('MMRPG_SHOW_MARKUP_{$board['user_id']}') -->\n");
            $this_leaderboard_markup[] = mmrpg_leaderboard_parse_index($key, $board, $place_counter);
        }
        // Otherwise if the page is in range and can be shown normally
        elseif (!defined('MMRPG_SKIP_MARKUP') && $key >= $this_start_key && $key < $this_display_limit){
            //echo("<!-- !defined('MMRPG_SKIP_MARKUP') && {$key} >= {$this_start_key} && {$key} < {$this_display_limit} -->\n");
            $this_leaderboard_markup[] = mmrpg_leaderboard_parse_index($key, $board, $place_counter);
        }

        // Add this markup to the leaderboard array
        $this_leaderboard_markup[] = $this_markup;

    }
}

?>