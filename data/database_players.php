<?
// PLAYER DATABASE

// Define the index of hidden players to not appear in the database
$hidden_database_players = array();
$hidden_database_players = array_merge($hidden_database_players, array('player'));
$hidden_database_players_count = !empty($hidden_database_players) ? count($hidden_database_players) : 0;

// Collect the database players
$mmrpg_database_players = $mmrpg_index['players'];

// Remove unallowed players from the database
foreach ($mmrpg_database_players AS $temp_token => $temp_info){
  if (in_array($temp_token, $hidden_database_players)){
    unset($mmrpg_database_players[$temp_token]);
  } else {
    // Ensure this player's image exists, else default to the placeholder
    $mmrpg_database_players[$temp_token]['player_image'] = $temp_token;
  }
}

// Sort the player index based on player number
function mmrpg_index_sort_players($player_one, $player_two){
  if ($player_one['player_number'] > $player_two['player_number']){ return 1; }
  elseif ($player_one['player_number'] < $player_two['player_number']){ return -1; }
  elseif ($player_one['player_token'] > $player_two['player_token']){ return 1; }
  elseif ($player_one['player_token'] < $player_two['player_token']){ return -1; }
  else { return 0; }
}
uasort($mmrpg_database_players, 'mmrpg_index_sort_players');

// Determine the token for the very first player in the database
$temp_player_tokens = array_values($mmrpg_database_players);
$first_player_token = array_shift($temp_player_tokens);
$first_player_token = $first_player_token['player_token'];
unset($temp_player_tokens);

// Count the number of players collected and filtered
$mmrpg_database_players_count = count($mmrpg_database_players);
$mmrpg_database_players_count_complete = 0;

// Loop through the database and generate the links for these players
$key_counter = 0;
$mmrpg_database_players_links = '';
$mmrpg_database_players_links .= '<div class="float link group" data-game="MM00">';
$mmrpg_database_players_links_counter = 0;
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
  //$player_image_path = 'images/players/'.$player_image_token.'/mug_right_'.$player_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  $player_image_path = 'i/p/'.$player_image_token.'/mr'.$player_image_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  $player_type_token = $player_info['player_type'];

  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $player_title_text ?>" data-token="<?= $player_info['player_token'] ?>" class="float left link type <?= ($player_image_incomplete ? 'inactive ' : '').($player_type_token) ?>">
    <a class="sprite player link mugshot size40 <?= $player_key == $first_player_token ? ' current' : '' ?>" href="<?='database/players/'.$player_info['player_token'].'/'?>" rel="<?= $player_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($player_image_token != 'player'): ?>
        <img src="<?= $player_image_path ?>" width="<?= $player_image_size ?>" height="<?= $player_image_size ?>" alt="<?= $player_title_text ?>" />
      <? else: ?>
        <span><?= $player_info['player_name'] ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  if ($player_flag_complete){ $mmrpg_database_players_count_complete++; }
  $mmrpg_database_players_links .= ob_get_clean();
  $mmrpg_database_players_links_counter++;
  $key_counter++;
}
$mmrpg_database_players_links .= '</div>';

?>