<?

// PLAYER DATABASE

// Define the index of hidden players to not appear in the database
$hidden_database_players = array();
$hidden_database_players = array_merge($hidden_database_players, array('player'));
$hidden_database_players_count = !empty($hidden_database_players) ? count($hidden_database_players) : 0;

// Collect the player database files from the cache or manually
$cache_token = md5('database/players/website');
$cached_index = rpg_object::load_cached_index('database.players', $cache_token);
if (!empty($cached_index)){

    // Collect the cached data for players, player count, and player numbers
    $mmrpg_database_players = $cached_index['mmrpg_database_players'];
    $mmrpg_database_players_count = $cached_index['mmrpg_database_players_count'];
    $mmrpg_database_players_numbers = $cached_index['mmrpg_database_players_numbers'];
    unset($cached_index);

} else {

    // Collect the database players
    $player_fields = rpg_player::get_index_fields(true, 'players');
    $mmrpg_database_players = $db->get_array_list("SELECT
        {$player_fields},
        groups.group_token AS player_group,
        tokens.token_order AS player_order
        FROM mmrpg_index_players AS players
        LEFT JOIN mmrpg_index_players_groups_tokens AS tokens ON tokens.player_token = players.player_token
        LEFT JOIN mmrpg_index_players_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = 'player'
        WHERE players.player_token <> 'player'
        AND players.player_flag_published = 1
        ORDER BY
        players.player_class ASC,
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'player_token');

    // Count the database players in total (without filters)
    $mmrpg_database_players_count = $db->get_value("SELECT
        COUNT(players.player_id) AS player_count
        FROM mmrpg_index_players AS players
        WHERE players.player_flag_published = 1 AND players.player_flag_hidden = 0
        ;", 'player_count');

    // Select an ordered list of all players and then assign row numbers to them
    $mmrpg_database_players_numbers = $db->get_array_list("SELECT
        players.player_token,
        0 AS player_key
        FROM mmrpg_index_players AS players
        LEFT JOIN mmrpg_index_players_groups_tokens AS tokens ON tokens.player_token = players.player_token
        LEFT JOIN mmrpg_index_players_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = 'player'
        WHERE players.player_token <> 'player'
        AND players.player_flag_published = 1
        ORDER BY
        players.player_class ASC,
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'player_token');
    $player_key = 1;
    foreach ($mmrpg_database_players_numbers AS $token => $info){
        $mmrpg_database_players_numbers[$token]['player_key'] = $player_key++;
    }

    // Remove unallowed players from the database
    if (!empty($mmrpg_database_players)){
        foreach ($mmrpg_database_players AS $temp_token => $temp_info){

            // Send this data through the player index parser
            $temp_info = rpg_player::parse_index_info($temp_info);

            // Collect this player's key in the index
            $temp_info['player_key'] = $mmrpg_database_players_numbers[$temp_token]['player_key'];

            // Ensure this player's image exists, else default to the placeholder
            $mmrpg_database_players[$temp_token]['player_image'] = $temp_token;

            // Update the main database array with the changes
            $mmrpg_database_players[$temp_token] = $temp_info;

        }
    }

    // Save the cached data for players, player count, and player numbers
    rpg_object::save_cached_index('database.players', $cache_token, array(
        'mmrpg_database_players' => $mmrpg_database_players,
        'mmrpg_database_players_count' => $mmrpg_database_players_count,
        'mmrpg_database_players_numbers' => $mmrpg_database_players_numbers
        ));

}

// If a filter function has been provided for this context, run it now
if (isset($filter_mmrpg_database_players)
    && is_callable($filter_mmrpg_database_players)){
    $mmrpg_database_players = array_filter($mmrpg_database_players, $filter_mmrpg_database_players);
}

// Loop through and remove hidden players unless they're being viewed explicitly
if (!empty($mmrpg_database_players)){
    foreach ($mmrpg_database_players AS $temp_token => $temp_info){
        if (!empty($temp_info['player_flag_hidden'])
            && $temp_info['player_token'] !== $this_current_token
            && !defined('FORCE_INCLUDE_HIDDEN_PLAYERS')){
            unset($mmrpg_database_players[$temp_token]);
        }
    }
}

// Loop through the database and generate the links for these players
$key_counter = 0;
$mmrpg_database_players_links = '';
$mmrpg_database_players_links_index = array();
$mmrpg_database_players_links .= '<div class="float link group" data-game="MMRPG">';
$mmrpg_database_players_links_counter = 0;
$mmrpg_database_players_count_complete = 0;

// Loop through the results and generate the links for these players
if (!empty($mmrpg_database_players)){
    foreach ($mmrpg_database_players AS $player_key => $player_info){
        if (!isset($first_player_key)){ $first_player_key = $player_key; }

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
            <a class="sprite player link mugshot size40 <?= $player_key == $first_player_key ? ' current' : '' ?>" href="<?= 'database/players/'.$player_info['player_token'].'/'?>" rel="<?= $player_image_incomplete ? 'nofollow' : 'follow' ?>">
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
}

$mmrpg_database_players_links .= '</div>';

?>