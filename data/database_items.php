<?
// Require the types database
if (!isset($mmrpg_database_types)){ require(MMRPG_CONFIG_ROOTDIR.'data/database_types.php'); }

// ITEM DATABASE

// Define the index of hidden items to not appear in the database
$hidden_database_items = array();
$hidden_database_items = array_merge($hidden_database_items, array('item-heart', 'item-star',
  'item-core-empty', 'item-shard-empty', 'item-star-empty',
  'item-core-energy', 'item-shard-energy', 'item-star-energy',
  'item-core-attack', 'item-shard-attack', 'item-star-attack',
  'item-core-defense', 'item-shard-defense', 'item-star-defense',
  'item-core-speed', 'item-shard-speed', 'item-star-speed',
  'item-star-none', 'item-star-copy'
  ));
$hidden_database_items_count = !empty($hidden_database_items) ? count($hidden_database_items) : 0;

// Define the hidden item query condition
$temp_condition = '';
$temp_condition .= "AND ability_class = 'item' ";
if (!empty($hidden_database_items)){
  $temp_tokens = array();
  foreach ($hidden_database_items AS $token){ $temp_tokens[] = "'".$token."'"; }
  $temp_condition .= 'AND ability_token NOT IN ('.implode(',', $temp_tokens).') ';
}

// Collect the database items
$mmrpg_database_items = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_published = 1 {$temp_condition} ORDER BY ability_order ASC", 'ability_token');

// Remove unallowed items from the database, and increment counters
foreach ($mmrpg_database_items AS $temp_token => $temp_info){
  if (true){

    // Send this data through the item index parser
    $temp_info = mmrpg_ability::parse_index_info($temp_info);

    // Ensure this item's image exists, else default to the placeholder
    $temp_image_token = isset($temp_info['ability_image']) ? $temp_info['ability_image'] : $temp_token;
    if ($temp_info['ability_flag_complete']){ $mmrpg_database_items[$temp_token]['ability_image'] = $temp_image_token; }
    else { $mmrpg_database_items[$temp_token]['ability_image'] = 'ability'; }
    // HIDE INCOMPLETE
    if (false && $mmrpg_database_items[$temp_token]['ability_image'] == 'ability'){
      unset($mmrpg_database_items[$temp_token]);
      continue;
    }
  }
}

// Determine the token for the very first item in the database
$temp_item_tokens = array_values($mmrpg_database_items);
$first_item_token = array_shift($temp_item_tokens);
$first_item_token = $first_item_token['ability_token'];
unset($temp_item_tokens);

// Count the number of items collected and filtered
$mmrpg_database_items_count = count($mmrpg_database_items);
$mmrpg_database_items_count_complete = 0;

// Loop through the database and generate the links for these items
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_items_links = '';
$mmrpg_database_items_links_counter = 0;
foreach ($mmrpg_database_items AS $item_key => $item_info){

  //if (!preg_match('/^item-star-/i', $item_info['ability_token'])){ continue; }

  // If a type filter has been applied to the ability page
  $temp_item_types = array();
  if (!empty($item_info['ability_type'])){ $temp_item_types[] = $item_info['ability_type']; }
  if (!empty($item_info['ability_type2'])){ $temp_item_types[] = $item_info['ability_type2']; }
  if (preg_match('/^item-score-ball-(red|blue|green|purple)$/i', $item_info['ability_token'])){ $temp_item_types[] = 'bonus'; }
  elseif (preg_match('/^item-super-(pellet|capsule)$/i', $item_info['ability_token'])){ $temp_item_types[] = 'multi'; }
  if (empty($temp_item_types)){ $temp_item_types[] = 'none'; }
  if (isset($this_current_filter) && !in_array($this_current_filter, $temp_item_types)){ $key_counter++; continue; }

  // If this is the first in a new group
  $game_code = !empty($item_info['ability_group']) ? $item_info['ability_group'] : (!empty($item_info['ability_game']) ? $item_info['ability_game'] : 'MMRPG');
  if ($game_code != $last_game_code){
    if ($key_counter != 0){ $mmrpg_database_items_links .= '</div>'; }
    $mmrpg_database_items_links .= '<div class="float link group" data-game="'.$game_code.'">';
    $last_game_code = $game_code;
  }

  // Collect the item sprite dimensions
  $item_flag_complete = !empty($item_info['ability_flag_complete']) ? true : false;
  $item_image_size = !empty($item_info['ability_image_size']) ? $item_info['ability_image_size'] : 40;
  $item_image_size_text = $item_image_size.'x'.$item_image_size;
  $item_image_token = !empty($item_info['ability_image']) ? $item_info['ability_image'] : $item_info['ability_token'];
  $item_image_incomplete = $item_image_token == 'ability' ? true : false;
  $item_is_active = !empty($this_current_token) && $this_current_token == $item_info['ability_token'] ? true : false;
  $item_title_text = $item_info['ability_name']; //.' | '.$item_info['ability_game'].' | '.$item_info['ability_group'];;
  //$item_image_path = 'images/abilities/'.$item_image_token.'/icon_right_'.$item_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  if (false && !empty($ability_info['ability_description'])){
    $temp_description = $item_info['ability_description'];
    $temp_description = str_replace('{DAMAGE}', $item_info['ability_damage'], $temp_description);
    $temp_description = str_replace('{DAMAGE2}', $item_info['ability_damage2'], $temp_description);
    $temp_description = str_replace('{RECOVERY}', $item_info['ability_recovery'], $temp_description);
    $temp_description = str_replace('{RECOVERY2}', $item_info['ability_recovery2'], $temp_description);
    $item_title_text .= '|| [['.$temp_description.']]';
  }
  $item_image_path = 'i/a/'.$item_image_token.'/ir'.$item_image_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  $item_type_class = !empty($item_info['ability_type']) ? $item_info['ability_type'] : 'none';
  if ($item_type_class != 'none' && !empty($item_info['ability_type2'])){ $item_type_class .= '_'.$item_info['ability_type2']; }
  elseif ($item_type_class == 'none' && !empty($item_info['ability_type2'])){ $item_type_class = $item_info['ability_type2'];  }
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $item_title_text ?>" data-token="<?= $item_info['ability_token'] ?>" class="float left link type <?= ($item_image_incomplete ? 'inactive ' : '').($item_type_class) ?>">
    <a class="sprite ability link mugshot size<?= $item_image_size.($item_key == $first_item_token ? ' current' : '') ?>" href="<?= 'database/items/'.preg_replace('/^item-/i', '', $item_info['ability_token']) ?>/" rel="<?= $item_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($item_image_token != 'ability'): ?>
        <img src="<?= $item_image_path ?>" width="<?= $item_image_size ?>" height="<?= $item_image_size ?>" alt="<?= $item_title_text ?>" />
      <? else: ?>
        <span><?= $item_info['ability_name'] ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  if ($item_flag_complete){ $mmrpg_database_items_count_complete++; }
  $mmrpg_database_items_links .= ob_get_clean();
  $mmrpg_database_items_links_counter++;
  $key_counter++;
}

// End the groups, however many there were
$mmrpg_database_items_links .= '</div>';

?>