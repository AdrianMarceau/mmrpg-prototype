<?

// CANVAS MARKUP : ITEMS

// Include the necessary database files
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require(MMRPG_CONFIG_ROOTDIR.'database/players.php');

// Start the output buffer
ob_start();

echo '<div class="wrapper no_sort">';
    echo '<div class="wrapper_header player_type player_type_experience">Select Player</div>';
    echo '<div class="wrapper_overflow">';

        // Loop through and print players
        $key_counter = 0;
        if (!empty($mmrpg_database_players)){
            $row_count = 4;
            $column_count = ceil(count($mmrpg_database_players) / $row_count);

            // Collect unlocked players and add them to the dropdown
            if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){ $unlocked_players = array_keys($_SESSION[$session_token]['values']['battle_rewards']); }
            else { $unlocked_players = array('dr-light'); }
            foreach ($mmrpg_database_players AS $player_token => $player_info){

                // Skip if this player isn't unlocked yet
                if (!in_array($player_token, $unlocked_players)){ continue; }

                // Collect this player's session rewards and settings
                $player_rewards = !empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]) ? $_SESSION[$session_token]['values']['battle_rewards'][$player_token] : array();
                $player_settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]) ? $_SESSION[$session_token]['values']['battle_settings'][$player_token] : array();

                // Collect the select markup for this player
                $temp_select_markup = rpg_player::print_editor_select_markup($player_info, $key_counter, $player_rewards, $player_settings);

                // Echo the generated select markup
                echo $temp_select_markup.' ';

                // Increment the key counter
                $key_counter++;

            }

        }

    echo '</div>';
echo '</div>';

// Collect the contents of the buffer
$edit_canvas_markup = ob_get_clean();
$edit_canvas_markup = preg_replace('/\s+/', ' ', trim($edit_canvas_markup));
exit($edit_canvas_markup);

?>