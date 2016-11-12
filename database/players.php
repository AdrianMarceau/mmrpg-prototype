<?

// PLAYER DATABASE

// Define the index of hidden players to not appear in the database
$hidden_database_players = array();
$hidden_database_players = array_merge($hidden_database_players, array('player'));
$hidden_database_players_count = !empty($hidden_database_players) ? count($hidden_database_players) : 0;

// Define the hidden robot query condition
$temp_condition = '';
if (!empty($hidden_database_robots)){
    $temp_tokens = array();
    foreach ($hidden_database_robots AS $token){ $temp_tokens[] = "'".$token."'"; }
    $temp_condition .= 'AND player_token NOT IN ('.implode(',', $temp_tokens).') ';
}
// If additional database filters were provided
$temp_condition_unfiltered = $temp_condition;
if (isset($mmrpg_database_players_filter)){
    if (!preg_match('/^\s?(AND|OR)\s+/i', $mmrpg_database_players_filter)){ $temp_condition .= 'AND ';  }
    $temp_condition .= $mmrpg_database_players_filter;
}

// Collect the database players and fields
$player_fields = rpg_player::get_index_fields(true);
$db->query("SET @player_row_number = 0;");
$mmrpg_database_players = $db->get_array_list("SELECT {$player_fields} FROM mmrpg_index_players WHERE player_flag_published = 1 AND (player_flag_hidden = 0 OR player_token = '{$this_current_token}') {$temp_condition} ORDER BY player_flag_hidden ASC, player_order ASC;", 'player_token');
$mmrpg_database_players_count = $db->get_value("SELECT COUNT(player_id) AS player_count FROM mmrpg_index_players WHERE player_flag_published = 1 AND player_flag_hidden = 0 {$temp_condition_unfiltered};", 'player_count');
$mmrpg_database_players_numbers = $db->get_array_list("SELECT player_token, (@player_row_number:=@player_row_number + 1) AS player_key FROM mmrpg_index_players WHERE player_flag_published = 1 {$temp_condition_unfiltered} ORDER BY player_flag_hidden ASC, player_order ASC;", 'player_token');

// Remove unallowed players from the database
foreach ($mmrpg_database_players AS $temp_token => $temp_info){

    // Define first player token if not set
    if (!isset($first_player_token)){ $first_player_token = $temp_token; }

    // Send this data through the player index parser
    $temp_info = rpg_player::parse_index_info($temp_info);

    // Collect this player's key in the index
    $temp_info['player_key'] = $mmrpg_database_players_numbers[$temp_token]['player_key'];

    // Ensure this player's image exists, else default to the placeholder
    $mmrpg_database_players[$temp_token]['player_image'] = $temp_token;

    // Update the main database array with the changes
    $mmrpg_database_players[$temp_token] = $temp_info;

}

// Loop through the database and generate the links for these players
$key_counter = 0;
$mmrpg_database_players_links = '';
$mmrpg_database_players_links_index = array();
$mmrpg_database_players_links .= '<div class="float link group" data-game="MM00">';
$mmrpg_database_players_links_counter = 0;
$mmrpg_database_players_count_complete = 0;

// Loop through the results and generate the links for these players
foreach ($mmrpg_database_players AS $player_key => $player_info){

    // If a type filter has been applied to the player page
    if (isset($this_current_filter) && $this_current_filter == 'none' && $player_info['player_type'] != ''){ $key_counter++; continue; }
    elseif (isset($this_current_filter) && $this_current_filter != 'none' && $player_info['player_type'] != $this_current_filter){ $key_counter++; continue; }

    // Collect the player sprite dimensions
    $player_flag_complete = true; //!empty($player_info['player_flag_complete']) ? true : false;
    $player_image_size = !empty($player_info['player_image_size']) ? $player_info['player_image_size'] : 40;
    $player_image_size_text = $player_image_size.'x'.$player_image_size;
    $player_image_token = !empty($player_info['player_image']) ? $player_info['player_image'] : $player_info['player_token'];
    $player_image_incomplete = $player_image_token == 'player' ? true : false;
    $player_is_active = !empty($this_current_token) && $this_current_token == $player_info['player_token'] ? true : false;
    $player_title_text = $player_info['player_name']; //.' | '.(!empty($player_info['player_type']) ? ucfirst($player_info['player_type']).' Type' : 'Neutral Type');
    $player_title_text .= '|| [['.ucfirst($player_info['player_type']).' +25%]]';
    $player_image_path = 'images/players/'.$player_image_token.'/mug_right_'.$player_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
    $player_type_token = $player_info['player_type'];

    // Start the output buffer and collect the generated markup
    ob_start();
    ?>
    <div title="<?= $player_title_text ?>" data-token="<?= $player_info['player_token'] ?>" class="float left link type <?= ($player_image_incomplete ? 'inactive ' : '').($player_type_token) ?>">
        <a class="sprite player link mugshot size40 <?= $player_key == $first_player_token ? ' current' : '' ?>" href="<?= 'database/players/'.$player_info['player_token'].'/'?>" rel="<?= $player_image_incomplete ? 'nofollow' : 'follow' ?>">
            <?php if($player_image_token != 'player'): ?>
                <img src="<?= $player_image_path ?>" width="<?= $player_image_size ?>" height="<?= $player_image_size ?>" alt="<?= $player_title_text ?>" />
            <?php else: ?>
                <span><?= $player_info['player_name'] ?></span>
            <?php endif; ?>
        </a>
    </div>
    <?php
    if ($player_flag_complete){ $mmrpg_database_players_count_complete++; }
    $temp_markup = ob_get_clean();
    $mmrpg_database_players_links_index[$player_key] = $temp_markup;
    $mmrpg_database_players_links .= $temp_markup;
    $mmrpg_database_players_links_counter++;
    $key_counter++;

}
$mmrpg_database_players_links .= '</div>';

?>