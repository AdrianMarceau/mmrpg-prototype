<?
/*
 * INDEX PAGE : DB PAGE (TEMPLATE)
 */

// Define the SEO variables for this page
$this_seo_title = $db_page_info['page_name'].' | '.$this_seo_title;
$this_seo_keywords = implode(array_unique(array_map(function($s){ return trim($s); }, explode(',', $db_page_info['page_seo_keywords'].','.$this_seo_keywords))));
$this_seo_description = $db_page_info['page_seo_description'].' '.$this_seo_description;

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

        // Parse the pseudo-code tag <!-- MMRPG_CURRENT_FIELD_TYPE -->
        $find = '<!-- MMRPG_CURRENT_FIELD_TYPE -->';
        $replace = MMRPG_SETTINGS_CURRENT_FIELDTYPE;
        $page_content_parsed = str_replace($find, $replace, $page_content_parsed);

        // Parse the pseudo-code tag <!-- MMRPG_ROBOT_FLOAT_SPRITE(robot, direction, frame, [size]) -->
        $temp_float_robot_matches = array();
        preg_match_all('/<!--\s+MMRPG_ROBOT_FLOAT_SPRITE\(([-_a-z0-9\'",\s]+)\)\s+-->/im', $page_content_parsed, $temp_float_robot_matches);
        if (!empty($temp_float_robot_matches[0])){
            foreach ($temp_float_robot_matches[0] AS $key => $find){
                $args = $temp_float_robot_matches[1][$key];
                $args = array_map(function($s){ return trim($s, '\'" '); }, explode(',', $args));
                $num_args = count($args);
                if ($num_args < 3){ continue; }
                elseif ($num_args === 3){
                    list($robot, $direction, $frame) = $args;
                    $replace = mmrpg_website_text_float_robot_markup($robot, $direction, $frame);
                } elseif ($num_args === 4){
                    list($robot, $direction, $frame, $size) = $args;
                    $replace = mmrpg_website_text_float_robot_markup($robot, $direction, $frame, $size);
                }
                $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
            }
        }

        // -- PAGE-SPECIFIC PSEUDO-CODES -- //

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_SCREENSHOT_GALLERY() -->
        $find = '<!-- MMRPG_LOAD_SCREENSHOT_GALLERY() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/gallery.php');
        }

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_CONTACT_FORM() -->
        $find = '<!-- MMRPG_LOAD_CONTACT_FORM() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/contact.php');
        }

        // Parse the pseudo-code tag <!-- MMRPG_LOAD_CONTRIBUTORS_INDEX() -->
        $find = '<!-- MMRPG_LOAD_CONTRIBUTORS_INDEX() -->';
        if (strstr($page_content_parsed, $find)){
            $page_content_parsed = str_replace($find, '', $page_content_parsed);
            require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/contributors.php');
        }

    }

    // Echo out the parsed content now that we're done with it
    echo($page_content_parsed);

// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>