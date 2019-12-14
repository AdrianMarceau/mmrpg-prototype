<?

// Require the leaderboard data for display
$this_display_limit_default = 3;
require(MMRPG_CONFIG_ROOTDIR.'includes/leaderboard.php');
$temp_leaderboard_online = mmrpg_prototype_leaderboard_online();
$temp_leaderboard_online_count = !empty($temp_leaderboard_online) ? count($temp_leaderboard_online) : 0;

// Parse the pseudo-code tag <!-- MMRPG_HOME_LEADERBOARD_COUNT -->
$find = '<!-- MMRPG_HOME_LEADERBOARD_COUNT -->';
if (strstr($page_content_parsed, $find)){
    $replace = (!empty($this_leaderboard_count) ? ($this_leaderboard_count == 1 ? '1 Player' : $this_leaderboard_count.' Players') : '0 Players').($temp_leaderboard_online_count > 0 ? ' <span style="opacity: 0.25;">|</span> <span style="text-shadow: 0 0 5px lime;">'.$temp_leaderboard_online_count.' Online</span>' : '');
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

// Parse the pseudo-code tag <!-- MMRPG_HOME_LEADERBOARD_MARKUP -->
$find = '<!-- MMRPG_HOME_LEADERBOARD_MARKUP -->';
if (strstr($page_content_parsed, $find)){
    ob_start();
    // Define the leaderboard displauy limit
    $leaderboard_display_limit = $this_display_limit_default;
    // Print out the generated leaderboard markup
    $displayed = 0;
    if (!empty($this_leaderboard_markup)){
        foreach ($this_leaderboard_markup AS $key => $leaderboard_markup){
            // If there was not markup in this slot, continue
            if (empty($leaderboard_markup)){ continue; }
            // Display this leaderboard image's markup
            echo $leaderboard_markup;
            $displayed++;
            // If over the display limit we can break
            if ($displayed >= $this_display_limit_default){ break; }
        }
        unset($this_leaderboard_markup);
    }
    $replace = ob_get_clean();
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

?>