<?php

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while importing!');  }

// Collect any extra request variables for the import
$this_import_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=import-players&limit=<?= $this_import_limit?>">Update Player Database</a> &raquo;
</div>
<?php
$this_page_markup .= ob_get_clean();


// Truncate any robots currently in the database
$db->query('TRUNCATE TABLE mmrpg_index_players');

// Require the players index file
if (empty($mmrpg_index['players'])){ require(MMRPG_CONFIG_ROOTDIR.'data/players/_index.php'); }
//die('check 2 <pre>'.print_r($mmrpg_index['players'], true).'</pre>'); //DEBUG
// Require the spreadsheet functions file
require(MMRPG_CONFIG_ROOTDIR.'admin/spreadsheets.php');

// Fill in potentially missing players with defaults for sorting
if (!empty($mmrpg_index['players'])){
    foreach ($mmrpg_index['players'] AS $token => $player){
        $player['player_class'] = isset($player['player_class']) ? $player['player_class'] : 'master';
        $player['player_game'] = isset($player['player_game']) ? $player['player_game'] : 'MMRPG';
        $player['player_group'] = isset($player['player_group']) ? $player['player_group'] : 'MMRPG';
        $player['player_type'] = isset($player['player_type']) ? $player['player_type'] : '';
        $player['player_type2'] = isset($player['player_type2']) ? $player['player_type2'] : '';
        $mmrpg_index['players'][$token] = $player;
    }
}

// DEBUG
$this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$mmrpg_database_players</strong><br />';
$this_page_markup .= 'Count:'.(!empty($mmrpg_index['players']) ? count($mmrpg_index['players']) : 0).'<br />';
//$this_page_markup .= '<pre>'.htmlentities(print_r($mmrpg_database_players, true), ENT_QUOTES, 'UTF-8', true).'</pre><br />';
$this_page_markup .= '</p>';

$spreadsheet_player_stats = array(); //mmrpg_spreadsheet_player_stats();
$spreadsheet_player_descriptions = array(); //mmrpg_spreadsheet_player_descriptions();


/*
header('Content-type: text/plain; charset=UTF-8');
die($this_page_markup."\n\n".
    //'$mmrpg_index[\'players\'] = <pre>'.print_r($mmrpg_index['players'], true).'</pre>'."\n\n".
    '$spreadsheet_player_stats = <pre>'.print_r($spreadsheet_player_stats, true).'</pre>'."\n\n".
    '$spreadsheet_player_descriptions = <pre>'.print_r($spreadsheet_player_descriptions, true).'</pre>'."\n\n".
    '---');
*/

// Sort the player index based on player number
$temp_pattern_first = array();
$temp_pattern_first[] = '/^dr-light$/i';
$temp_pattern_first[] = '/^dr-wily$/i';
$temp_pattern_first[] = '/^dr-cossack/i';
//$temp_pattern_first = array_reverse($temp_pattern_first);
$temp_pattern_last = array();
//$temp_pattern_last = array_reverse($temp_pattern_last);
// Sort the player index based on player number
function mmrpg_index_sort_players($player_one, $player_two){
    // Pull in global variables
    global $temp_pattern_first, $temp_pattern_last;
    // Loop through all the temp patterns and compare them one at a time
    foreach ($temp_pattern_first AS $key => $pattern){
        // Check if either of these two players matches the current pattern
        if (preg_match($pattern, $player_one['player_token']) && !preg_match($pattern, $player_two['player_token'])){ return -1; }
        elseif (!preg_match($pattern, $player_one['player_token']) && preg_match($pattern, $player_two['player_token'])){ return 1; }
    }
    foreach ($temp_pattern_last AS $key => $pattern){
        // Check if either of these two players matches the current pattern
        if (preg_match($pattern, $player_one['player_token']) && !preg_match($pattern, $player_two['player_token'])){ return 1; }
        elseif (!preg_match($pattern, $player_one['player_token']) && preg_match($pattern, $player_two['player_token'])){ return -1; }
    }
    if ($player_one['player_game'] > $player_two['player_game']){ return 1; }
    elseif ($player_one['player_game'] < $player_two['player_game']){ return -1; }
    elseif ($player_one['player_token'] > $player_two['player_token']){ return 1; }
    elseif ($player_one['player_token'] < $player_two['player_token']){ return -1; }
    elseif ($player_one['player_token'] > $player_two['player_token']){ return 1; }
    elseif ($player_one['player_token'] < $player_two['player_token']){ return -1; }
    else { return 0; }
}
uasort($mmrpg_index['players'], 'mmrpg_index_sort_players');

// Loop through each of the player info arrays
$player_key = 0;
$player_order = 0;
$temp_empty = $mmrpg_index['players']['player'];
unset($mmrpg_index['players']['player']);
array_unshift($mmrpg_index['players'], $temp_empty);
if (!empty($mmrpg_index['players'])){
    foreach ($mmrpg_index['players'] AS $player_token => $player_data){

        // If this player's image exists, assign it
        if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/players/'.$player_token.'/')){ $player_data['player_image'] = $player_data['player_token']; }
        else { $player_data['player_image'] = 'player'; }

        // Define the insert array and start populating it with basic details
        $temp_insert_array = array();
        //$temp_insert_array['player_id'] = isset($player_data['player_id']) ? $player_data['player_id'] : $player_key;
        $temp_insert_array['player_token'] = $player_data['player_token'];
        $temp_insert_array['player_number'] = !empty($player_data['player_number']) ? $player_data['player_number'] : '';
        $temp_insert_array['player_name'] = !empty($player_data['player_name']) ? $player_data['player_name'] : '';
        $temp_insert_array['player_game'] = !empty($player_data['player_game']) ? $player_data['player_game'] : '';
        $temp_insert_array['player_group'] = !empty($player_data['player_group']) ? $player_data['player_group'] : '';

        $temp_insert_array['player_class'] = !empty($player_data['player_class']) ? $player_data['player_class'] : 'player';

        $temp_insert_array['player_image'] = !empty($player_data['player_image']) ? $player_data['player_image'] : '';
        $temp_insert_array['player_image_size'] = !empty($player_data['player_image_size']) ? $player_data['player_image_size'] : 40;
        $temp_insert_array['player_image_editor'] = !empty($player_data['player_image_editor']) ? $player_data['player_image_editor'] : 0;
        $temp_insert_array['player_image_alts'] = json_encode(!empty($player_data['player_image_alts']) ? $player_data['player_image_alts'] : array());

        $temp_insert_array['player_type'] = !empty($player_data['player_type']) ? $player_data['player_type'] : '';
        $temp_insert_array['player_type2'] = !empty($player_data['player_type2']) ? $player_data['player_type2'] : '';

        $temp_insert_array['player_description'] = !empty($player_data['player_description']) ? trim($player_data['player_description']) : '';
        $temp_insert_array['player_description2'] = !empty($player_data['player_description2']) ? trim($player_data['player_description2']) : '';

        $temp_insert_array['player_energy'] = !empty($player_data['player_energy']) ? $player_data['player_energy'] : 0;
        $temp_insert_array['player_weapons'] = !empty($player_data['player_weapons']) ? $player_data['player_weapons'] : 0;
        $temp_insert_array['player_attack'] = !empty($player_data['player_attack']) ? $player_data['player_attack'] : 0;
        $temp_insert_array['player_defense'] = !empty($player_data['player_defense']) ? $player_data['player_defense'] : 0;
        $temp_insert_array['player_speed'] = !empty($player_data['player_speed']) ? $player_data['player_speed'] : 0;

        // Define the rewardss for this player
        $temp_insert_array['player_robots_rewards'] = json_encode(!empty($player_data['player_rewards']['robots']) ? $player_data['player_rewards']['robots'] : array());
        $temp_insert_array['player_abilities_rewards'] = json_encode(!empty($player_data['player_rewards']['abilities']) ? $player_data['player_rewards']['abilities'] : array());

        // Define compatibilities for this player
        $temp_insert_array['player_robots_compatible'] = json_encode(!empty($player_data['player_robots_unlockable']) ? $player_data['player_robots_unlockable'] : array());
        $temp_insert_array['player_abilities_compatible'] = json_encode(!empty($player_data['player_abilities']) ? $player_data['player_abilities'] : array());

        // Define the battle quotes for this player
        if (!empty($player_data['player_quotes'])){ foreach ($player_data['player_quotes'] AS $key => $quote){ $player_data['player_quotes'][$key] = html_entity_decode($quote, ENT_QUOTES, 'UTF-8'); } }
        $temp_insert_array['player_quotes_start'] = !empty($player_data['player_quotes']['battle_start']) && $player_data['player_quotes']['battle_start'] != '...' ? $player_data['player_quotes']['battle_start'] : '';
        $temp_insert_array['player_quotes_taunt'] = !empty($player_data['player_quotes']['battle_taunt']) && $player_data['player_quotes']['battle_taunt'] != '...' ? $player_data['player_quotes']['battle_taunt'] : '';
        $temp_insert_array['player_quotes_victory'] = !empty($player_data['player_quotes']['battle_victory']) && $player_data['player_quotes']['battle_victory'] != '...' ? $player_data['player_quotes']['battle_victory'] : '';
        $temp_insert_array['player_quotes_defeat'] = !empty($player_data['player_quotes']['battle_defeat']) && $player_data['player_quotes']['battle_defeat'] != '...' ? $player_data['player_quotes']['battle_defeat'] : '';


        $temp_insert_array['player_functions'] = !empty($player_data['player_functions']) ? $player_data['player_functions'] : 'players/player.php';

        // Collect applicable spreadsheets for this player
        $spreadsheet_stats = !empty($spreadsheet_player_stats[$player_data['player_token']]) ? $spreadsheet_player_stats[$player_data['player_token']] : array();
        $spreadsheet_descriptions = !empty($spreadsheet_player_descriptions[$player_data['player_token']]) ? $spreadsheet_player_descriptions[$player_data['player_token']] : array();

        // Collect any user-contributed data for this player
        if (!empty($spreadsheet_descriptions['player_description'])){ $temp_insert_array['player_description2'] = trim($spreadsheet_descriptions['player_description']); }

        // Define the flags
        $temp_insert_array['player_flag_hidden'] = in_array($temp_insert_array['player_token'], array('player')) || !empty($player_data['player_flag_hidden']) ? 1 : 0;
        $temp_insert_array['player_flag_complete'] = $player_data['player_image'] != 'player' ? 1 : 0;
        $temp_insert_array['player_flag_unlockable'] = $temp_insert_array['player_flag_complete'] && !empty($player_data['player_flag_unlockable']) ? 1 : 0;
        $temp_insert_array['player_flag_published'] = 1;

        // Define the order counter
        if ($temp_insert_array['player_class'] != 'system'){
            $temp_insert_array['player_order'] = $player_order;
            $player_order++;
        } else {
            $temp_insert_array['player_order'] = 0;
        }


        // Check if this player already exists in the database
        $temp_success = true;
        $temp_exists = $db->get_array("SELECT player_token FROM mmrpg_index_players WHERE player_token LIKE '{$temp_insert_array['player_token']}' LIMIT 1") ? true : false;
        if (!$temp_exists){ $temp_success = $db->insert('mmrpg_index_players', $temp_insert_array); }
        else { $temp_success = $db->update('mmrpg_index_players', $temp_insert_array, array('player_token' => $temp_insert_array['player_token'])); }

        // Print out the generated insert array
        $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';
        $this_page_markup .= '<strong>$mmrpg_database_players['.$player_token.']</strong><br />';
        //$this_page_markup .= '<pre>'.print_r($player_data, true).'</pre><br /><hr /><br />';
        $this_page_markup .= '<pre>'.print_r($temp_insert_array, true).'</pre><br /><hr /><br />';
        //$this_page_markup .= '<pre>'.print_r(rpg_player::parse_index_info($temp_insert_array), true).'</pre><br /><hr /><br />';
        $this_page_markup .= '</p><hr />';

        $player_key++;

        //die('end');

    }
}
// Otherwise, if empty, we're done!
else {
    $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL ROBOT HAVE BEEN IMPORTED UPDATED!</strong></p>';
}

?>