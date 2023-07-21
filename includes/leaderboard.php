<?

// Define the leaderboard metric index in order of sorting priority
$leaderboard_metric_index = array();
$leaderboard_metric_index['battle_points'] = array(
    'name' => 'Battle Points',
    'shortname' => 'Points',
    'text' => 'Players earn battle points throughout the course of the game by completing certain events, collecting items, unlocking new robots or abilities, and by doing or collecting lots of other things as well.',
    'url' => 'points',
    'key' => 'board_points',
    'col' => 'board.board_points',
    'icon' => 'fa-trophy',
    'unit' => 'BP'
    );
$leaderboard_metric_index['battle_zenny'] = array(
    'name' => 'Zenny',
    'shortname' => 'Zenny',
    'text' => 'Players earn zenny throughout the course of the game by completing missions, selling items, or other miscellaneous tasks.',
    'url' => 'zenny',
    'key' => 'board_zenny',
    'col' => 'board.board_zenny',
    'icon' => 'fa-dollar-sign',
    'unit' => '&#438;',
    'unit_plain' => 'z'
    );
$leaderboard_metric_index['robots_unlocked'] = array(
    'name' => 'Robots Unlocked',
    'shortname' => 'Robots',
    'text' => 'Players unlock new robots throughout the course of the game by completing missions, buying blueprints in the shop, or other secret methods.',
    'url' => 'robots',
    'key' => 'board_robots_count',
    'col' => 'board.board_robots_count',
    'icon' => 'fa-robot',
    'label' => 'Robot/Robots'
    );
$leaderboard_metric_index['abilities_unlocked'] = array(
    'name' => 'Abilities Unlocked',
    'shortname' => 'Abilities',
    'text' => 'Players unlock new abilities throughout the course of the game by completing missions, unlocking new robots, buying them in the shop, and even other more elusive methods.',
    'url' => 'abilities',
    'key' => 'board_abilities',
    'col' => 'board.board_abilities',
    'icon' => 'fa-fire-alt',
    'label' => 'Ability/Abilities'
    );
$leaderboard_metric_index['items_cataloged'] = array(
    'name' => 'Items Cataloged',
    'shortname' => 'Items',
    'text' => 'Players discover new items throughout the course of the game by completing missions, completing quests, buying them in the shop, stealing them from targets, and even other methods.',
    'url' => 'items',
    'key' => 'board_items',
    'col' => 'board.board_items',
    'icon' => 'fa-briefcase',
    'label' => 'Item/Items'
    );
$leaderboard_metric_index['stars_collected'] = array(
    'name' => 'Stars Collected',
    'shortname' => 'Stars',
    'text' => 'Players collect elemental field stars and fusion stars in special post-game "Star Field" missions, battles against powerful hordes of robots enemies controlled by the alien energy known as "star force".',
    'url' => 'stars',
    'key' => 'board_stars',
    'col' => 'board.board_stars',
    'icon' => 'fa-star',
    'label' => 'Star/Stars'
    );
$leaderboard_metric_index['player_tokens'] = array(
    'name' => 'Player Tokens',
    'shortname' => 'Players',
    'text' => 'Players can acquire player tokens by claiming victory in special post-game "Player Battle" missions, battles against the ghost-data of other human players and their customized robots.',
    'url' => 'tokens',
    'key' => 'board_tokens_count',
    'col' => 'battles.player_tokens_collected',
    'icon' => 'fa-stop-circle',
    'label' => 'Token/Tokens'
    );
$leaderboard_metric_index['challenges_medals'] = array(
    'name' => 'Challenge Medals',
    'shortname' => 'Challenges',
    'text' => 'Players can earn challenge medals by claiming victory in special post-game "Challenge Missions", battles designed by the developers to be as difficult as possible.',
    'url' => 'medals',
    'key' => 'board_medals_count',
    'col' => 'challenges.challenge_medals_collected',
    'icon' => 'fa-skull',
    'label' => 'Medal/Medals'
    );
$leaderboard_metric_index['endless_waves'] = array(
    'name' => 'Endless Waves',
    'shortname' => 'Waves',
    'text' => 'Players can fight their way through an infinite number of waves in a special post-game "Endless Attack Mode", battles with targets that get more powerful with every round.',
    'url' => 'waves',
    'key' => 'board_waves_count',
    'col' => 'endless.challenge_waves_completed',
    'icon' => 'fa-infinity',
    'label' => 'Wave/Waves'
    );
$leaderboard_metric_priority = array_keys($leaderboard_metric_index);
$leaderboard_metric_index_urls = array();
foreach ($leaderboard_metric_index as $key => $value) {
    $leaderboard_metric_index_urls[$value['url']] = $key;
}

// Check to see which metric we'll be sorting based on
if (!isset($this_leaderboard_metric)){ $this_leaderboard_metric = MMRPG_SETTINGS_CURRENT_LEADERBOARD_METRIC; }
if (!empty($_GET['metric'])
    && is_string($_GET['metric'])
    && !empty($leaderboard_metric_index_urls[$_GET['metric']])){
    $this_leaderboard_metric = $leaderboard_metric_index_urls[$_GET['metric']];
}

// Collect current leaderboard metric info
$this_leaderboard_metric_info = $leaderboard_metric_index[$this_leaderboard_metric];

// Define a variable to hold the base leaderboard URL in case of filters
$base_leaderboard_url = 'leaderboard/';
$current_leaderboard_url = $base_leaderboard_url;
if (!empty($this_leaderboard_metric)
    && $this_leaderboard_metric !== MMRPG_SETTINGS_DEFAULT_LEADERBOARD_METRIC){
    // Update current page URL w/ filter fragment
    $current_leaderboard_url .= 'by-'.$this_leaderboard_metric_info['url'].'/';
    // Update the page title w/ filter text
    $this_seo_title = str_replace('Leaderboard | ', 'Leaderboard ('.$this_leaderboard_metric_info['name'].') | ', $this_seo_title);
    // Update the page header w/ filter text
    $this_markup_header = $this_seo_title = str_replace('Leaderboard', ucfirst($this_leaderboard_metric_info['url']).' Leaderboard', $this_markup_header);
}

//error_log('$_GET = '.print_r($_GET, true));
//error_log('$leaderboard_metric_index_urls = '.print_r($leaderboard_metric_index_urls, true));
//error_log('$this_leaderboard_metric = '.print_r($this_leaderboard_metric, true));
//error_log('$this_leaderboard_metric_info = '.print_r($this_leaderboard_metric_info, true));

// Collect and define the display limit if set
$this_start_key = !empty($_GET['start']) ? trim($_GET['start']) : 0;
if (!isset($this_display_limit_default)){ $this_display_limit_default = 50; }
$this_display_limit = !empty($_GET['limit']) ? trim($_GET['limit']) : $this_display_limit_default;

// Define a function for parsing the leaderboard data
function mmrpg_leaderboard_parse_index($key, $board, $place_counter){

    global $db;
    global $this_userid, $this_userinfo, $this_boardinfo;
    global $this_display_limit, $this_num_offset;
    global $this_time, $this_start_key, $this_display_limit_default;
    global $leaderboard_metric_index, $current_leaderboard_url;
    global $this_leaderboard_metric, $this_leaderboard_metric_info;

    global $mmrpg_index_players;
    if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }

    static $z_index;
    if (empty($z_index)){ $z_index = $this_display_limit_default + 1; }
    $z_index -= 1;


    $board_key = $key;

    // Collect the points/zenny/etc.
    $this_points = 0;
    $this_points = $board[$this_leaderboard_metric_info['key']];

    // Define the awards strong and default to empty
    $this_user_awards = ' ';

    // Collect this player's robots
    $board_points_count = !empty($board['board_points']) ? $board['board_points'] : 0;
    $board_zenny_count = !empty($board['board_zenny']) ? $board['board_zenny'] : 0;
    $board_robots_count = !empty($board['board_robots_count']) ? $board['board_robots_count'] : 0;
    $board_items_count = !empty($board['board_items']) ? $board['board_items'] : 0;
    $board_abilities_count = !empty($board['board_abilities']) ? $board['board_abilities'] : 0;
    $board_stars_count = !empty($board['board_stars']) ? $board['board_stars'] : 0;
    $board_tokens_count = !empty($board['board_tokens_count']) ? $board['board_tokens_count'] : 0;
    $board_medals_count = !empty($board['board_medals_count']) ? $board['board_medals_count'] : 0;
    $board_waves_count = !empty($board['board_waves_count']) ? $board['board_waves_count'] : 0;
    $this_awards = !empty($board['board_awards']) ? explode(',', $board['board_awards']) : array();
    $this_first_save = !empty($board['board_date_created']) ? $board['board_date_created'] : 0;
    $this_last_save = !empty($board['board_date_modified']) ? $board['board_date_modified'] : 0;
    $this_last_access = !empty($board['user_date_accessed']) ? $board['user_date_accessed'] : 0;
    $this_is_online = !empty($board['user_is_online']) ? true : false;
    $this_last_save = !empty($this_last_save) ? date('Y/m/d @ H:i', $this_last_save) : '????-??-?? ??:??';
    $this_style = $this_is_online ? 'border-color: green; ' : '';
    $this_style .= 'z-index: '.$z_index.'; ';
    $this_username = !empty($board['user_name_public']) && !empty($board['user_flag_postpublic']) ? $board['user_name_public'] : $board['user_name'];
    $this_username = htmlentities($this_username, ENT_QUOTES, 'UTF-8', true);
    $this_user_id = !empty($board['user_id']) ? $board['user_id'] : 0;
    if (rpg_user::is_guest()
        && $this_user_id == $_SESSION['GAME']['USER']['userid']
        && $this_leaderboard_metric === MMRPG_SETTINGS_DEFAULT_LEADERBOARD_METRIC){
        $this_boardinfo['board_rank'] = $place_counter;
        $_SESSION['GAME']['BOARD']['boardrank'] = $this_boardinfo['board_rank'];
    }

    // Only continue if markup is special constants have not been defined
    if (!defined('MMRPG_SKIP_MARKUP') || defined('MMRPG_SHOW_MARKUP_'.$this_user_id)){

        // Define a quick function for formatting numbers
        $func_format_thousands_value = function($count){ return number_format($count, 0, '.', ','); };
        $func_format_millions_value = function($count) {
            if ($count < 1000) {
                return number_format($count, 0, '.', ',');
            } elseif ($count < 1000000) {
                return number_format($count/1000, 1, '.', ',').'k';
            } elseif ($count < 1000000000) {
                return number_format($count/1000000, 1, '.', ',').'m';
            } else {
                return number_format($count/1000000000, 1, '.', ',').'b';
            }
        };

        // Only generate markup if we're withing the viewing range
        if ($board_key >= $this_start_key && $board_key < $this_display_limit || defined('MMRPG_SHOW_MARKUP_'.$this_user_id)){

            $points_count_text = ($board_points_count === 1 ? '1 Battle Point' : $func_format_thousands_value($board_points_count).' Battle Points');
            $zenny_count_text = ($board_zenny_count === 1 ? '1 Zenny' : $func_format_thousands_value($board_zenny_count).' Zenny');
            $robots_count_text = ($board_robots_count === 1 ? '1 Robot' : $board_robots_count.' Robots').' Unlocked';
            $abilities_count_text = ($board_abilities_count === 1 ? '1 Ability' : $board_abilities_count.' Abilities').' Unlocked';
            $items_count_text = ($board_items_count === 1 ? '1 Item' : $board_items_count.' Items').' Cataloged';
            $stars_count_text = ($board_stars_count === 1 ? '1 Star' : $board_stars_count.' Stars').' Collected';
            $tokens_count_text = $board_tokens_count === 1 ? '1 Player Token' : $board_tokens_count.' Player Tokens';
            $medals_count_text = $board_medals_count === 1 ? '1 Challenge Medal' : $board_medals_count.' Challenge Medals';
            $waves_count_text = $board_waves_count === 1 ? '1 Endless Wave' : $board_waves_count.' Endless Waves';

            $this_records_html = array();
            if ($this_leaderboard_metric !== 'robots_unlocked' && !empty($board_robots_count)){
                $icon = $leaderboard_metric_index['robots_unlocked']['icon'];
                $this_records_html[] = '<span class="count robots" title="'.$robots_count_text.'">'.$board_robots_count.' <i class="fa fa-fw '.$icon.'"></i></span>';
            }
            if ($this_leaderboard_metric !== 'abilities_unlocked' && !empty($board_abilities_count)){
                $icon = $leaderboard_metric_index['abilities_unlocked']['icon'];
                $this_records_html[] = '<span class="count abilities" title="'.$abilities_count_text.'">'.$board_abilities_count.' <i class="fa fa-fw '.$icon.'"></i></span>';
            }
            if ($this_leaderboard_metric !== 'items_cataloged' && !empty($board_items_count)){
                $icon = $leaderboard_metric_index['items_cataloged']['icon'];
                $this_records_html[] = '<span class="count items" title="'.$items_count_text.'">'.$board_items_count.' <i class="fa fa-fw '.$icon.'"></i></span>';
            }
            if ($this_leaderboard_metric !== 'stars_collected' && !empty($board_stars_count)){
                $icon = $leaderboard_metric_index['stars_collected']['icon'];
                $this_records_html[] = '<span class="count stars" title="'.$stars_count_text.'">'.$board_stars_count.' <i class="fa fa-fw '.$icon.'"></i></span>';
            }
            if ($this_leaderboard_metric !== 'player_tokens' && !empty($board_tokens_count)){
                $icon = $leaderboard_metric_index['player_tokens']['icon'];
                $this_records_html[] = '<span class="count tokens" title="'.$tokens_count_text.'">'.$board_tokens_count.' <i class="fa fa-fw '.$icon.'"></i></span>';
            }
            if ($this_leaderboard_metric !== 'challenges_medals' && !empty($board_medals_count)){
                $icon = $leaderboard_metric_index['challenges_medals']['icon'];
                $this_records_html[] = '<span class="count medals" title="'.$medals_count_text.'">'.$board_medals_count.' <i class="fa fa-fw '.$icon.'"></i></span>';
            }
            if ($this_leaderboard_metric !== 'endless_waves' && !empty($board_waves_count)){
                $icon = $leaderboard_metric_index['endless_waves']['icon'];
                $this_records_html[] = '<span class="count waves" title="'.$waves_count_text.'">'.$func_format_thousands_value($board_waves_count).' <i class="fa fa-fw '.$icon.'"></i></span>';
            }
            if ($this_leaderboard_metric !== 'battle_points' && !empty($board_points_count)){
                $unit = $leaderboard_metric_index['battle_points']['unit'];
                $this_records_html[] = '<span class="count bpoints" title="'.$points_count_text.'">'.$func_format_millions_value($board_points_count).' '.$unit.'</span>';
            }
            if (false && $this_leaderboard_metric !== 'battle_zenny' && !empty($board_zenny_count)){
                $unit = $leaderboard_metric_index['battle_points']['unit'];
                $this_records_html[] = '<span class="count zenny" title="'.$zenny_count_text.'">'.$func_format_millions_value($board_zenny_count).' '.$unit.'</span>';
            }
            $this_records_html = implode(' <span class="pipe">|</span> ', $this_records_html);

            $this_points_html = '<span class="value">'.(!empty($this_points) ? $func_format_thousands_value($this_points) : 0).'</span>';
            $this_points_plain = (!empty($this_points) ? $func_format_thousands_value($this_points) : 0);

            if (!empty($this_leaderboard_metric_info['unit'])){
                $this_points_html .= ' '.$this_leaderboard_metric_info['unit'];
                $this_points_plain .= ' '.(!empty($this_leaderboard_metric_info['unit_plain']) ? $this_leaderboard_metric_info['unit_plain'] : $this_leaderboard_metric_info['unit']);
            } else {
                if (!empty($this_leaderboard_metric_info['icon'])){
                    $this_points_html .= ' <i class="fa fa-fw '.$this_leaderboard_metric_info['icon'].'"></i>';
                }
                if (!empty($this_leaderboard_metric_info['label'])){
                    list($singular, $plural) = strstr($this_leaderboard_metric_info['label'], '/') ? explode('/', $this_leaderboard_metric_info['label']) : array($this_leaderboard_metric_info['label'], $this_leaderboard_metric_info['label']);
                    if (empty($this_leaderboard_metric_info['icon'])){ $this_points_html .= ' '.($this_points == 1 ? $singular : $plural); }
                    $this_points_plain .= ' '.($this_points == 1 ? $singular : $plural);
                }
            }

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
            if (!empty($this_awards_string)
                && $this_leaderboard_metric === MMRPG_SETTINGS_DEFAULT_LEADERBOARD_METRIC){
                $db->query("UPDATE mmrpg_leaderboard SET board_awards = '{$this_awards_string}' WHERE user_id = {$board['user_id']};");
            }

            // -- LEADERBOARD MARKUP -- //

            // Add the prototype complete flags if applicable
            $pos = 0;
            if (in_array('prototype_complete_light', $this_awards)){ $pos++; $this_user_awards .= '<span class="sprite achievement_icon achievement_dr-light-complete'.($pos ? ' pos'.$pos : '').'" data-tooltip="Light Campaign Complete!" data-tooltip-type="player_type player_type_defense">&hearts;</span>'; }
            if (in_array('prototype_complete_wily', $this_awards)){ $pos++; $this_user_awards .= '<span class="sprite achievement_icon achievement_dr-wily-complete'.($pos ? ' pos'.$pos : '').'" data-tooltip="Wily Campaign Complete!" data-tooltip-type="player_type player_type_attack">&clubs;</span>'; }
            if (in_array('prototype_complete_cossack', $this_awards)){ $pos++; $this_user_awards .= '<span class="sprite achievement_icon achievement_dr-cossack-complete'.($pos ? ' pos'.$pos : '').'" data-tooltip="Cossack Campaign Complete!" data-tooltip-type="player_type player_type_speed">&diams;</span>'; }
            // Add the first place flag if applicable
            $this_user_awards_sticky = '';
            if (in_array('ranking_first_place', $this_awards)){ $this_user_awards_sticky .= '<span class="sprite achievement_icon achievement_'.($place_counter == 1 ? 'is' : 'reached').'-first-place" data-tooltip="Reached First Place!" data-tooltip-type="player_type player_type_level">&#9733;</span>'; }


            // Start the output buffer
            ob_start();

            // Display the user's save file listing
            $this_place = mmrpg_number_suffix($place_counter, true, true);
            $this_colour = !empty($board['user_colour_token']) ? $board['user_colour_token'] : '';
            if (!empty($this_colour) && !empty($board['user_colour_token2'])){ $this_colour .= '_'.$board['user_colour_token2']; }
            if (empty($this_colour)){ $this_colour = 'none'; }
            echo '<a data-id="'.$board['user_id'].'" data-player="'.$board['user_name_clean'].'" class="file file_'.strip_tags($this_place).'" name="file_'.$key.'" style="'.$this_style.'" href="'.$current_leaderboard_url.$board['user_name_clean'].'/">'."\n";
                echo '<div class="inset player_type type_'.$this_colour.'">'."\n";
                    echo '<span class="place">'.$this_place.'</span>'."\n";
                    echo '<span class="userinfo"><span class="username">'.$this_username.$this_user_awards.'</span><span class="details">'.$this_details.'</span></span>'."\n";
                    echo '<span class="points">'.$this_points_html.'</span>'."\n";
                    echo '<span class="records">'.$this_records_html.'</span>'."\n";
                echo '</div>'."\n";
                if (!empty($this_user_awards_sticky)){ echo $this_user_awards_sticky."\n"; }
                if (!empty($board['user_image_path'])){ list($avatar_class, $avatar_token, $avatar_base_size) = explode('/', $board['user_image_path']); }
                else { $avatar_class = 'robots'; $avatar_token = 'mega-man'; $avatar_base_size = 40; }
                if (!empty($board['user_background_path'])){ list($background_class, $background_token) = explode('/', $board['user_background_path']); }
                else { $background_class = 'fields'; $background_token = rpg_player::get_intro_field('player'); }
                $avatar_size = $avatar_base_size * 2;
                $place_frame = 'base';
                if ($place_counter == 3){ $place_frame = 'taunt'; }
                elseif ($place_counter == 2){ $place_frame = 'summon'; }
                elseif ($place_counter == 1){ $place_frame = 'victory'; }
                $y_offset = 0;
                if (strstr($avatar_token, 'astro-man') && $this_place > 1){ $y_offset = -16; }
                echo '<span class="avatar"><span class="avatar_wrapper"'.(!empty($y_offset) ? 'style="bottom: '.$y_offset.'px;"' : '').'>';
                    echo '<span class="sprite sprite_shadow sprite_'.$avatar_size.'x'.$avatar_size.' sprite_shadow_'.$avatar_size.'x'.$avatar_size.' sprite_'.$avatar_size.'x'.$avatar_size.'_'.$place_frame.'" style="background-image: url(images/'.$avatar_class.'/'.preg_replace('/^([-a-z0-9]+)(_[a-z0-9]+)?$/i', '$1', $avatar_token).'/sprite_left_'.$avatar_base_size.'x'.$avatar_base_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-size: auto '.$avatar_size.'px;">'.$this_username.'</span>';
                    echo '<span class="sprite sprite_'.$avatar_size.'x'.$avatar_size.' sprite_'.$avatar_size.'x'.$avatar_size.'_'.$place_frame.'" style="background-image: url(images/'.$avatar_class.'/'.$avatar_token.'/sprite_left_'.$avatar_base_size.'x'.$avatar_base_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-size: auto '.$avatar_size.'px;">'.$this_username.'</span>';
                echo '</span></span>'."\n";
            echo '</a>'."\n";

            // Collect the output from the buffer and return
            $this_leaderboard_markup = preg_replace('/\s+/', ' ', ob_get_clean());
            return $this_leaderboard_markup;

        }

    }

}

// Define the array for pulling all the leaderboard data
$this_limit_query = '';
if ($this_current_page == 'home'){ $this_limit_query = "LIMIT {$this_display_limit_default} "; }
$this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
$this_sort_field = $this_leaderboard_metric_info['col'];
$this_sort_field2 = 'board.board_points';
$temp_leaderboard_query = "SELECT
    users.user_id,
    users.user_name,
    users.user_name_clean,
    users.user_name_public,
    users.user_colour_token,
    users.user_colour_token2,
    users.user_image_path,
    users.user_background_path,
    users.user_date_accessed,
    users.user_flag_postpublic,
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
    IF(battles.player_tokens_collected IS NOT NULL, battles.player_tokens_collected, 0) AS board_tokens_count,
    IF(challenges.challenge_medals_collected IS NOT NULL, challenges.challenge_medals_collected, 0) AS board_medals_count,
    IF(endless.challenge_waves_completed IS NOT NULL, endless.challenge_waves_completed, 0) AS board_waves_count,
    board.board_abilities,
    board.board_abilities_dr_light,
    board.board_abilities_dr_wily,
    board.board_abilities_dr_cossack,
    board.board_missions,
    board.board_missions_dr_light,
    board.board_missions_dr_wily,
    board.board_missions_dr_cossack,
    board.board_awards,
    board.board_zenny,
    board.board_date_created,
    board.board_date_modified
    FROM mmrpg_users AS users
    LEFT JOIN mmrpg_leaderboard AS board ON users.user_id = board.user_id
    LEFT JOIN mmrpg_saves AS saves ON saves.user_id = board.user_id
    LEFT JOIN (
        SELECT
            battles.this_user_id AS user_id,
            COUNT(DISTINCT(battles.target_user_id)) AS player_tokens_collected
        FROM mmrpg_battles AS battles
        LEFT JOIN mmrpg_users AS users ON battles.target_user_id = users.user_id
        WHERE
            battles.this_player_result = 'victory'
            AND users.user_flag_approved = 1
        GROUP BY battles.this_user_id
        ) AS battles ON battles.user_id = board.user_id
    LEFT JOIN (
        SELECT
            scores.user_id AS user_id,
            COUNT(DISTINCT(scores.challenge_id)) AS challenge_medals_collected
        FROM mmrpg_challenges_leaderboard AS scores
        LEFT JOIN mmrpg_users AS users ON users.user_id = scores.user_id
        LEFT JOIN mmrpg_challenges AS challenges ON challenges.challenge_id = scores.challenge_id
        WHERE
            challenges.challenge_kind = 'event'
            AND scores.challenge_result = 'victory'
            AND users.user_flag_approved = 1
        GROUP BY scores.user_id
        ) AS challenges ON challenges.user_id = board.user_id
    LEFT JOIN mmrpg_challenges_waveboard
        AS endless ON endless.user_id = board.user_id
    WHERE
    {$this_sort_field2} > 0
    ORDER BY
    {$this_sort_field} DESC,
    {$this_sort_field2} DESC,
    saves.save_date_modified DESC
    {$this_limit_query}
    ;";

// Query the database and collect the array list of all non-bogus players
$this_leaderboard_index = $db->get_array_list($temp_leaderboard_query);
//error_log('$temp_leaderboard_query = '.print_r($temp_leaderboard_query, true));
//error_log('$this_leaderboard_index('.count($this_leaderboard_index).') = [...]');

// Loop through the save file directory and generate an index
$this_leaderboard_count = count($this_leaderboard_index);
$this_leaderboard_online_count = 0;
$this_leaderboard_online_players = array();
$this_leaderboard_online_pages = array();
$this_leaderboard_markup = array();

// If we're on the home page, we need to collect count independantly
if ($this_current_page == 'home'){
    $this_leaderboard_count = $db->get_value("SELECT
    COUNT(*) AS num_players
    FROM mmrpg_users AS users
    LEFT JOIN mmrpg_leaderboard AS board ON users.user_id = board.user_id
    LEFT JOIN mmrpg_saves AS saves ON saves.user_id = board.user_id
    WHERE
    {$this_sort_field2} > 0
    ORDER BY
    {$this_sort_field} DESC,
    {$this_sort_field2} DESC,
    saves.save_date_modified DESC
    ;", 'num_players');
}

// Ensure the leaderboard array is not empty before continuing
if (!empty($this_leaderboard_index)){
    $this_time = time();
    $last_points = 0;
    $place_counter = 0;

    // Loop through the leaderboard array and print out any markup
    foreach ($this_leaderboard_index AS $key => $board){
        //echo("\n\n<!-- \$this_leaderboard_index[{$key}] -->\n");

        // Collect the points and increment the place counter if necessary
        //$this_points = $board['board_points'];
        $this_points = $board[$this_leaderboard_metric_info['key']];
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
            $this_current_page_number = ceil(($key + 1) / $this_display_limit_default);
            if (!in_array($this_current_page_number, $this_leaderboard_online_pages)){ $this_leaderboard_online_pages[] = $this_current_page_number; }
            $this_leaderboard_online_players[] = array(
                'id' => $board['user_id'],
                'name' => !empty($board['user_name_public']) ? $board['user_name_public'] : $board['user_name'],
                'token' => $board['user_name_clean'],
                'place' => mmrpg_number_suffix($place_counter, true, true),
                'placeint' => $place_counter,
                'colour' => $board['user_colour_token'],
                'colour2' => $board['user_colour_token2'],
                'image' => $board['user_image_path'],
                'page' => $this_current_page_number
                );
        }

        // If this user was requested specifically, generate markup
        if (defined('MMRPG_SHOW_MARKUP_'.$board['user_id'])){
            //echo("<!-- defined('MMRPG_SHOW_MARKUP_{$board['user_id']}') -->\n");
            $this_markup = mmrpg_leaderboard_parse_index($key, $board, $place_counter);
        }
        // Otherwise if the page is in range and can be shown normally
        elseif (!defined('MMRPG_SKIP_MARKUP') && $key >= $this_start_key && $key < $this_display_limit){
            //echo("<!-- !defined('MMRPG_SKIP_MARKUP') && {$key} >= {$this_start_key} && {$key} < {$this_display_limit} -->\n");
            $this_markup = mmrpg_leaderboard_parse_index($key, $board, $place_counter);
        }

        // Add this markup to the leaderboard array
        $this_leaderboard_markup[] = $this_markup;

    }
}

?>