<?

// ITEM DATABASE

// Define the index of counters for robot types
$mmrpg_database_items_types = array();
foreach ($mmrpg_database_types AS $token => $info){
  $mmrpg_database_items_types[$token] = 0;
}

// Define the index of hidden items to not appear in the database
$hidden_database_items = array();
$hidden_database_items = array_merge($hidden_database_items, array('heart'));
$hidden_database_items_count = !empty($hidden_database_items) ? count($hidden_database_items) : 0;

// Define the hidden item query condition
$temp_condition = '';
$temp_condition .= "AND item_class = 'item' ";
if (!empty($hidden_database_items)){
  $temp_tokens = array();
  foreach ($hidden_database_items AS $token){ $temp_tokens[] = "'".$token."'"; }
  $temp_condition .= 'AND item_token NOT IN ('.implode(',', $temp_tokens).') ';
}

// Collect the database items
$mmrpg_database_items = $db->get_array_list("SELECT * FROM mmrpg_index_items WHERE item_flag_published = 1 {$temp_condition}", 'item_token');

// Remove unallowed items from the database, and increment counters
foreach ($mmrpg_database_items AS $temp_token => $temp_info){
  if (true){

    // Send this data through the item index parser
    $temp_info = rpg_item::parse_index_info($temp_info);

    // Ensure this item's image exists, else default to the placeholder
    $temp_image_token = isset($temp_info['item_image']) ? $temp_info['item_image'] : $temp_token;
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/items/'.$temp_image_token.'/')){ $mmrpg_database_items[$temp_token]['item_image'] = $temp_image_token; }
    elseif (file_exists(MMRPG_CONFIG_ROOTDIR.'images/items/'.$temp_token.'/')){ $mmrpg_database_items[$temp_token]['item_image'] = $temp_token; }
    else { $mmrpg_database_items[$temp_token]['item_image'] = 'item'; }
    // DEBUG DEBUG DEBUG | HIDE INCOMPLETE
    if (false && $mmrpg_database_items[$temp_token]['item_image'] == 'item'){
      unset($mmrpg_database_items[$temp_token]);
      continue;
    }
    //$mmrpg_database_items[$temp_token]['item_speed'] = isset($mmrpg_database_items[$temp_token]['item_speed']) ? $mmrpg_database_items[$temp_token]['item_speed'] + 3 : 3;
    //$mmrpg_database_items[$temp_token]['item_energy'] = isset($mmrpg_database_items[$temp_token]['item_energy']) ? $mmrpg_database_items[$temp_token]['item_energy'] : 10;
  }
}

// Sort the item index based on item number
$temp_pattern_first = array();
$temp_pattern_first[] = '/^small-screw$/i';
$temp_pattern_first[] = '/^large-screw$/i';
$temp_pattern_first[] = '/^energy-pellet$/i';
$temp_pattern_first[] = '/^energy-capsule$/i';
$temp_pattern_first[] = '/^weapon-pellet$/i';
$temp_pattern_first[] = '/^weapon-capsule$/i';
$temp_pattern_first[] = '/^energy-tank$/i';
$temp_pattern_first[] = '/^weapon-tank$/i';
$temp_pattern_first[] = '/^extra-life$/i';
$temp_pattern_first[] = '/^yashichi$/i';
$temp_pattern_first[] = '/^attack-pellet$/i';
$temp_pattern_first[] = '/^attack-capsule$/i';
$temp_pattern_first[] = '/^defense-pellet$/i';
$temp_pattern_first[] = '/^defense-capsule$/i';
$temp_pattern_first[] = '/^speed-pellet$/i';
$temp_pattern_first[] = '/^speed-capsule$/i';
$temp_pattern_first[] = '/^super-pellet$/i';
$temp_pattern_first[] = '/^super-capsule$/i';
$temp_pattern_last = array();
$temp_pattern_last[] = '/^none-core$/i';
$temp_pattern_last[] = '/^copy-core$/i';
$temp_pattern_last[] = '/^(crystal|cutter|earth|electric|explode|flame|freeze|impact|laser|missile|nature|shadow|shield|space|swift|time|water|wind)-core$/i';
$temp_pattern_last[] = '/^heart$/i';
$temp_pattern_last[] = '/^star$/i';
$temp_pattern_last = array_reverse($temp_pattern_last);
function mmrpg_index_sort_items($item_one, $item_two){
  // Pull in global variables
  global $temp_pattern_first, $temp_pattern_last;
  // Loop through all the temp patterns and compare them one at a time
  foreach ($temp_pattern_first AS $key => $pattern){
    // Check if either of these two items matches the current pattern
    if (preg_match($pattern, $item_one['item_token']) && !preg_match($pattern, $item_two['item_token'])){ return -1; }
    elseif (!preg_match($pattern, $item_one['item_token']) && preg_match($pattern, $item_two['item_token'])){ return 1; }
  }
  foreach ($temp_pattern_last AS $key => $pattern){
    // Check if either of these two items matches the current pattern
    if (preg_match($pattern, $item_one['item_token']) && !preg_match($pattern, $item_two['item_token'])){ return 1; }
    elseif (!preg_match($pattern, $item_one['item_token']) && preg_match($pattern, $item_two['item_token'])){ return -1; }
  }
  // If only one of the two items has a type, the one with goes first
  if (!empty($item_one['item_token']) && empty($item_two['item_token'])){ return 1; }
  elseif (empty($item_one['item_token']) && !empty($item_two['item_token'])){ return -1; }
  else {
    // If only one of the two items has a type, the one with goes first
    if ($item_one['item_token'] > $item_two['item_token']){ return 1; }
    elseif ($item_one['item_token'] < $item_two['item_token']){ return -1; }
    else {
      // Return 0 by default
      return 0;
    }
  }
}
uasort($mmrpg_database_items, 'mmrpg_index_sort_items');

// Determine the token for the very first item in the database
$temp_item_tokens = array_values($mmrpg_database_items);
$first_item_token = array_shift($temp_item_tokens);
$first_item_token = $first_item_token['item_token'];
unset($temp_item_tokens);

// Count the number of items collected and filtered
$mmrpg_database_items_count = count($mmrpg_database_items);

// Loop through the database and generate the links for these items
$key_counter = 0;
$mmrpg_database_items_links = '';
$mmrpg_database_items_links_counter = 0;
$mmrpg_database_items_links_counter_incomplete = 0;
foreach ($mmrpg_database_items AS $item_key => $item_info){
  // If a type filter has been applied to the item page
  $temp_item_types = array();
  if (!empty($item_info['item_type'])){ $temp_item_types[] = $item_info['item_type']; }
  if (!empty($item_info['item_type2'])){ $temp_item_types[] = $item_info['item_type2']; }
  if (empty($temp_item_types)){ $temp_item_types[] = 'none'; }
  if (isset($this_current_filter) && !in_array($this_current_filter, $temp_item_types)){ $key_counter++; continue; }
  // Collect the item sprite dimensions
  $item_image_size = !empty($item_info['item_image_size']) ? $item_info['item_image_size'] : 40;
  $item_image_size_text = $item_image_size.'x'.$item_image_size;
  $item_image_token = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_info['item_token'];
  $item_image_incomplete = $item_image_token == 'item' ? true : false;
  $item_is_active = !empty($this_current_token) && $this_current_token == $item_info['item_token'] ? true : false;
  $item_title_text = $item_info['item_name'];
  $item_image_path = 'images/items/'.$item_image_token.'/icon_right_'.$item_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $item_title_text ?>" data-token="<?= $item_info['item_token'] ?>" class="float float_left float_link item_type item_type_<?= (!empty($item_info['item_type']) ? $item_info['item_type'] : 'none').(!empty($item_info['item_type2']) ? '_'.$item_info['item_type2'] : '') ?><?= $item_image_incomplete  ? ' incomplete' : '' ?>">
    <a class="sprite sprite_item_link sprite_item sprite_item_sprite sprite_40x40 sprite_40x40_mugshot sprite_size_<?= $item_image_size_text ?>  item_status_active item_position_active <?= $item_key == $first_item_token ? 'sprite_item_current ' : '' ?>" href="<?= 'database/items/'.preg_replace('/^/i', '', $item_info['item_token']) ?>/" rel="<?= $item_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($item_image_token != 'item'): ?>
        <img src="<?= $item_image_path ?>" width="<?= $item_image_size ?>" height="<?= $item_image_size ?>" alt="<?= $item_title_text ?>" />
      <? else: ?>
        <span><?= preg_replace('/\s+([a-z0-9]+)$/i', '<br />$1', $item_info['item_name']) ?></span>
      <? endif; ?>
    </a>
  </div>
  <?
  $mmrpg_database_items_links .= preg_replace('/\s+/', ' ', trim(ob_get_clean()))."\n";
  $mmrpg_database_items_links_counter++;
  if ($item_image_incomplete){ $mmrpg_database_items_links_counter_incomplete++; }
  $key_counter++;
}

?>