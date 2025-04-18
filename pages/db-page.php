<?
/*
 * INDEX PAGE : DB PAGE (TEMPLATE)
 */

// Define the SEO variables for this page
if ($db_page_info['page_url'] === 'home/'){
    $this_seo_title = $db_page_info['page_seo_title'].' | '.$this_seo_title;
    $this_seo_title = implode(' | ', array_unique(explode(' | ', $this_seo_title)));
} elseif (!empty($db_page_info['page_name'])){
    $this_seo_title = $db_page_info['page_name'].' | '.$this_seo_title;
    $this_seo_title = implode(' | ', array_unique(explode(' | ', $this_seo_title)));
}
if (!empty($db_page_info['page_seo_keywords'])){
    $this_seo_keywords = explode(',', $db_page_info['page_seo_keywords'].','.$this_seo_keywords);
    $this_seo_keywords = array_unique(array_map(function($s){ return trim($s); }, $this_seo_keywords));
    $this_seo_keywords = implode(', ', $this_seo_keywords);
}
if (!empty($db_page_info['page_seo_description'])){
    $this_seo_description = $db_page_info['page_seo_description'].' '.$this_seo_description;
}

// Define the Open Graph variables for this page
$this_graph_data['title'] = $db_page_info['page_seo_title'];
$this_graph_data['description'] = $db_page_info['page_seo_description'];

// Define the MARKUP variables for this page
$this_markup_header = $db_page_info['page_title'];

// Start generating the page markup
ob_start();

    // Collect the raw page content for processing later
    $page_content_raw = $db_page_info['page_content'];

    // Parse any dynamic PHP tags from the markup and replace with content
    $page_content_parsed = $page_content_raw;
    if (!empty($page_content_parsed)){

        // -- GLOBAL PSEUDO-CODES -- //

        // Parse the pseudo-code tag <!-- MMRPG_CONFIG_ROOTURL -->
        $find = '<!-- MMRPG_CONFIG_ROOTURL -->';
        $replace = MMRPG_SETTINGS_CURRENT_FIELDTYPE;
        $page_content_parsed = str_replace($find, $replace, $page_content_parsed);

        // Parse the pseudo-code tag <!-- MMRPG_CONFIG_CACHE_DATE -->
        $find = '<!-- MMRPG_CONFIG_CACHE_DATE -->';
        $replace = MMRPG_SETTINGS_CURRENT_FIELDTYPE;
        $page_content_parsed = str_replace($find, $replace, $page_content_parsed);

        // Parse the pseudo-code tag <!-- MMRPG_CURRENT_FIELD_TYPE -->
        $find = '<!-- MMRPG_CURRENT_FIELD_TYPE -->';
        $replace = MMRPG_SETTINGS_CURRENT_FIELDTYPE;
        $page_content_parsed = str_replace($find, $replace, $page_content_parsed);

        // Parse the pseudo-code tag <!-- MMRPG_CURRENT_FIELD_MECHA -->
        $find = '<!-- MMRPG_CURRENT_FIELD_MECHA -->';
        $replace = MMRPG_SETTINGS_CURRENT_FIELDMECHA;
        $page_content_parsed = str_replace($find, $replace, $page_content_parsed);

        // Parse the pseudo-code tag <!-- MMRPG_PLAYER_FLOAT_SPRITE(player, direction, frame, [size]) -->
        $temp_float_player_matches = array();
        preg_match_all('/<!--\s+MMRPG_PLAYER_FLOAT_SPRITE\(([-_a-z0-9\'",\s\|]+)\)\s+-->/im', $page_content_parsed, $temp_float_player_matches);
        if (!empty($temp_float_player_matches[0])){
            foreach ($temp_float_player_matches[0] AS $key => $find){
                $args = $temp_float_player_matches[1][$key];
                $args = array_map(function($s){ return trim($s, '\'" '); }, explode(',', $args));
                $num_args = count($args);
                if ($num_args < 3){ continue; }
                $player = $direction = $frame = $size = false;
                if ($num_args === 4){ list($player, $direction, $frame, $size) = $args; }
                elseif ($num_args === 3){ list($player, $direction, $frame) = $args; }
                if (strstr($frame, '|')){ $frames = explode('|',$frame); $frame = $frames[mt_rand(0, (count($frames) - 1))]; }
                if ($size !== false){ $replace = mmrpg_website_text_float_player_markup($player, $direction, $frame, $size); }
                else { $replace = mmrpg_website_text_float_player_markup($player, $direction, $frame); }
                $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
            }
        }

        // Parse the pseudo-code tag <!-- MMRPG_ROBOT_FLOAT_SPRITE(robot, direction, frame, [size]) -->
        $temp_float_robot_matches = array();
        preg_match_all('/<!--\s+MMRPG_ROBOT_FLOAT_SPRITE\(([-_a-z0-9\'",\s\|]+)\)\s+-->/im', $page_content_parsed, $temp_float_robot_matches);
        if (!empty($temp_float_robot_matches[0])){
            foreach ($temp_float_robot_matches[0] AS $key => $find){
                $args = $temp_float_robot_matches[1][$key];
                $args = array_map(function($s){ return trim($s, '\'" '); }, explode(',', $args));
                $num_args = count($args);
                if ($num_args < 3){ continue; }
                $robot = $direction = $frame = $size = false;
                if ($num_args === 4){ list($robot, $direction, $frame, $size) = $args; }
                elseif ($num_args === 3){ list($robot, $direction, $frame) = $args; }
                if ($robot === 'MMRPG_CURRENT_FIELD_MECHA'){ $robot = MMRPG_SETTINGS_CURRENT_FIELDMECHA; }
                if (strstr($frame, '|')){ $frames = explode('|',$frame); $frame = $frames[mt_rand(0, (count($frames) - 1))]; }
                if ($size !== false){ $replace = mmrpg_website_text_float_robot_markup($robot, $direction, $frame, $size); }
                else { $replace = mmrpg_website_text_float_robot_markup($robot, $direction, $frame); }
                $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
            }
        }

        // -- PAGE-SPECIFIC PSEUDO-CODES -- //

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_HOME_PAGE() -->
        $find = '<!-- MMRPG_LOAD_HOME_PAGE() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require_once(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/home.php');
        }

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_GALLERY_PAGE() -->
        $find = '<!-- MMRPG_LOAD_GALLERY_PAGE() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require_once(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/gallery.php');
        }

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_DATABASE_PAGE() -->
        $find = '<!-- MMRPG_LOAD_DATABASE_PAGE() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require_once(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database.php');
        }

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_COMMUNITY_PAGE() -->
        $find = '<!-- MMRPG_LOAD_COMMUNITY_PAGE() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require_once(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/community.php');
        }

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_LEADERBOARD_PAGE() -->
        $find = '<!-- MMRPG_LOAD_LEADERBOARD_PAGE() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require_once(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/leaderboard.php');
        }

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_CONTACT_PAGE() -->
        $find = '<!-- MMRPG_LOAD_CONTACT_PAGE() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require_once(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/contact.php');
        }

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_CREDITS_PAGE() -->
        $find = '<!-- MMRPG_LOAD_CREDITS_PAGE() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require_once(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/credits.php');
        }

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_FILE_PAGE() -->
        $find = '<!-- MMRPG_LOAD_FILE_PAGE() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require_once(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/file.php');
        }

    }

    // Echo out the parsed content now that we're done with it
    echo($page_content_parsed);

// Collect the buffer and define the page markup
$this_markup_body = trim(ob_get_clean());
?>