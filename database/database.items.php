<?php
// Require the types database
if (!isset($mmrpg_database_types)){ require(MMRPG_CONFIG_ROOTDIR.'database/database.types.php'); }

// ITEM DATABASE

// Define the index of hidden items to not appear in the database
$hidden_database_items = array();
$hidden_database_items = array_merge($hidden_database_items, array('heart', 'star',
  'empty-core', 'empty-shard', 'empty-star',
  'energy-core', 'energy-shard', 'energy-star',
  'attack-core', 'attack-shard', 'attack-star',
  'defense-core', 'defense-shard', 'defense-star',
  'speed-core', 'speed-shard', 'speed-star',
  'none-star', 'copy-star'
  ));
$hidden_database_items_count = !empty($hidden_database_items) ? count($hidden_database_items) : 0;

// Define the hidden item query condition
$temp_condition = '';
$temp_condition .= "AND item_class <> 'system' ";
if (!empty($hidden_database_items)){
  $temp_tokens = array();
  foreach ($hidden_database_items AS $token){ $temp_tokens[] = "'".$token."'"; }
  $temp_condition .= 'AND item_token NOT IN ('.implode(',', $temp_tokens).') ';
}
// If additional database filters were provided
$temp_condition_unfiltered = $temp_condition;
if (isset($mmrpg_database_items_filter)){
  if (!preg_match('/^\s?(AND|OR)\s+/i', $mmrpg_database_items_filter)){ $temp_condition .= 'AND ';  }
  $temp_condition .= $mmrpg_database_items_filter;
}

// Collect the database items
$mmrpg_database_items = $this_database->get_array_list("SELECT * FROM mmrpg_index_items WHERE item_flag_published = 1 {$temp_condition} ORDER BY item_order ASC", 'item_token');
$mmrpg_database_items_count = $this_database->get_value("SELECT COUNT(item_id) AS item_count FROM mmrpg_index_items WHERE item_flag_published = 1 {$temp_condition_unfiltered};", 'item_count');

// Remove unallowed items from the database, and increment counters
foreach ($mmrpg_database_items AS $temp_token => $temp_info){
  if (true){

    // Send this data through the item index parser
    $temp_info = rpg_item::parse_index_info($temp_info);

    // Ensure this item's image exists, else default to the placeholder
    $temp_image_token = isset($temp_info['item_image']) ? $temp_info['item_image'] : $temp_token;
    if ($temp_info['item_flag_complete']){ $mmrpg_database_items[$temp_token]['item_image'] = $temp_image_token; }
    else { $mmrpg_database_items[$temp_token]['item_image'] = 'item'; }
    // HIDE INCOMPLETE
    if (false && $mmrpg_database_items[$temp_token]['item_image'] == 'item'){
      unset($mmrpg_database_items[$temp_token]);
      continue;
    }
  }
}

// Determine the token for the very first item in the database
$temp_item_tokens = array_values($mmrpg_database_items);
$first_item_token = array_shift($temp_item_tokens);
$first_item_token = $first_item_token['item_token'];
unset($temp_item_tokens);

// Count the number of items collected and filtered
//$mmrpg_database_items_count = count($mmrpg_database_items);
$mmrpg_database_items_count_complete = 0;

// Loop through the database and generate the links for these items
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_items_links = '';
$mmrpg_database_items_links_counter = 0;
foreach ($mmrpg_database_items AS $item_key => $item_info){

  //if (!preg_match('/^star-/i', $item_info['item_token'])){ continue; }

  // If a type filter has been applied to the item page
  $temp_item_types = array();
  if (!empty($item_info['item_type'])){ $temp_item_types[] = $item_info['item_type']; }
  if (!empty($item_info['item_type2'])){ $temp_item_types[] = $item_info['item_type2']; }
  if (preg_match('/^(red|blue|green|purple)-score-ball$/i', $item_info['item_token'])){ $temp_item_types[] = 'bonus'; }
  elseif (preg_match('/^super-(pellet|capsule)$/i', $item_info['item_token'])){ $temp_item_types[] = 'multi'; }
  if (empty($temp_item_types)){ $temp_item_types[] = 'none'; }
  if (isset($this_current_filter) && !in_array($this_current_filter, $temp_item_types)){ $key_counter++; continue; }

  // If this is the first in a new group
  $game_code = !empty($item_info['item_group']) ? $item_info['item_group'] : (!empty($item_info['item_game']) ? $item_info['item_game'] : 'MMRPG');
  if ($game_code != $last_game_code){
    if ($key_counter != 0){ $mmrpg_database_items_links .= '</div>'; }
    $mmrpg_database_items_links .= '<div class="float link group" data-game="'.$game_code.'">';
    $last_game_code = $game_code;
  }

  // Collect the item sprite dimensions
  $item_flag_complete = !empty($item_info['item_flag_complete']) ? true : false;
  $item_image_size = !empty($item_info['item_image_size']) ? $item_info['item_image_size'] : 40;
  $item_image_size_text = $item_image_size.'x'.$item_image_size;
  $item_image_token = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_info['item_token'];
  $item_image_incomplete = $item_image_token == 'item' ? true : false;
  $item_is_active = !empty($this_current_token) && $this_current_token == $item_info['item_token'] ? true : false;
  $item_title_text = $item_info['item_name']; //.' | '.$item_info['item_game'].' | '.$item_info['item_group'];;
  //$item_image_path = 'images/items/'.$item_image_token.'/icon_right_'.$item_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  if (false && !empty($item_info['item_description'])){
    $temp_description = $item_info['item_description'];
    $temp_description = str_replace('{DAMAGE}', $item_info['item_damage'], $temp_description);
    $temp_description = str_replace('{DAMAGE2}', $item_info['item_damage2'], $temp_description);
    $temp_description = str_replace('{RECOVERY}', $item_info['item_recovery'], $temp_description);
    $temp_description = str_replace('{RECOVERY2}', $item_info['item_recovery2'], $temp_description);
    $item_title_text .= '|| [['.$temp_description.']]';
  }
  $item_image_path = 'i/i/'.$item_image_token.'/ir'.$item_image_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  $item_type_class = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
  if ($item_type_class != 'none' && !empty($item_info['item_type2'])){ $item_type_class .= '_'.$item_info['item_type2']; }
  elseif ($item_type_class == 'none' && !empty($item_info['item_type2'])){ $item_type_class = $item_info['item_type2'];  }
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $item_title_text ?>" data-token="<?= $item_info['item_token'] ?>" class="float left link type <?= ($item_image_incomplete ? 'inactive ' : '').($item_type_class) ?>">
    <a class="sprite item link mugshot size<?= $item_image_size.($item_key == $first_item_token ? ' current' : '') ?>" href="<?= 'database/items/'.preg_replace('/^/i', '', $item_info['item_token']) ?>/" rel="<?= $item_image_incomplete ? 'nofollow' : 'follow' ?>">
      <?php if($item_image_token != 'item'): ?>
        <img src="<?= $item_image_path ?>" width="<?= $item_image_size ?>" height="<?= $item_image_size ?>" alt="<?= $item_title_text ?>" />
      <?php else: ?>
        <span><?= $item_info['item_name'] ?></span>
      <?php endif; ?>
    </a>
  </div>
  <?php
  if ($item_flag_complete){ $mmrpg_database_items_count_complete++; }
  $mmrpg_database_items_links .= ob_get_clean();
  $mmrpg_database_items_links_counter++;
  $key_counter++;
}

// End the groups, however many there were
$mmrpg_database_items_links .= '</div>';

?>