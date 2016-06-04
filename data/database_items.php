<?

// ITEM DATABASE

// Define the index of hidden items to not appear in the database
$hidden_database_items = array();
$hidden_database_items = array_merge($hidden_database_items, array('item-heart'));
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
$mmrpg_database_items = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_published = 1 {$temp_condition}", 'ability_token');

// Remove unallowed items from the database, and increment counters
foreach ($mmrpg_database_items AS $temp_token => $temp_info){
  if (true){

    // Send this data through the item index parser
    $temp_info = rpg_ability::parse_index_info($temp_info);

    // Ensure this item's image exists, else default to the placeholder
    $temp_image_token = isset($temp_info['ability_image']) ? $temp_info['ability_image'] : $temp_token;
    if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/abilities/'.$temp_image_token.'/')){ $mmrpg_database_items[$temp_token]['ability_image'] = $temp_image_token; }
    elseif (file_exists(MMRPG_CONFIG_ROOTDIR.'images/abilities/'.$temp_token.'/')){ $mmrpg_database_items[$temp_token]['ability_image'] = $temp_token; }
    else { $mmrpg_database_items[$temp_token]['ability_image'] = 'ability'; }
    // DEBUG DEBUG DEBUG | HIDE INCOMPLETE
    if (false && $mmrpg_database_items[$temp_token]['ability_image'] == 'ability'){
      unset($mmrpg_database_items[$temp_token]);
      continue;
    }
    //$mmrpg_database_items[$temp_token]['ability_speed'] = isset($mmrpg_database_items[$temp_token]['ability_speed']) ? $mmrpg_database_items[$temp_token]['ability_speed'] + 3 : 3;
    //$mmrpg_database_items[$temp_token]['ability_energy'] = isset($mmrpg_database_items[$temp_token]['ability_energy']) ? $mmrpg_database_items[$temp_token]['ability_energy'] : 10;
  }
}

// Sort the item index based on item number
$temp_pattern_first = array();
$temp_pattern_first[] = '/^item-screw-small$/i';
$temp_pattern_first[] = '/^item-screw-large$/i';
$temp_pattern_first[] = '/^item-energy-pellet$/i';
$temp_pattern_first[] = '/^item-energy-capsule$/i';
$temp_pattern_first[] = '/^item-weapon-pellet$/i';
$temp_pattern_first[] = '/^item-weapon-capsule$/i';
$temp_pattern_first[] = '/^item-energy-tank$/i';
$temp_pattern_first[] = '/^item-weapon-tank$/i';
$temp_pattern_first[] = '/^item-extra-life$/i';
$temp_pattern_first[] = '/^item-yashichi$/i';
$temp_pattern_first[] = '/^item-attack-pellet$/i';
$temp_pattern_first[] = '/^item-attack-capsule$/i';
$temp_pattern_first[] = '/^item-defense-pellet$/i';
$temp_pattern_first[] = '/^item-defense-capsule$/i';
$temp_pattern_first[] = '/^item-speed-pellet$/i';
$temp_pattern_first[] = '/^item-speed-capsule$/i';
$temp_pattern_first[] = '/^item-super-pellet$/i';
$temp_pattern_first[] = '/^item-super-capsule$/i';
$temp_pattern_last = array();
$temp_pattern_last[] = '/^item-core-none$/i';
$temp_pattern_last[] = '/^item-core-copy$/i';
$temp_pattern_last[] = '/^item-core-(crystal|cutter|earth|electric|explode|flame|freeze|impact|laser|missile|nature|shadow|shield|space|swift|time|water|wind)$/i';
$temp_pattern_last[] = '/^item-heart$/i';
$temp_pattern_last[] = '/^item-star$/i';
$temp_pattern_last = array_reverse($temp_pattern_last);
function mmrpg_index_sort_items($item_one, $item_two){
  // Pull in global variables
  global $temp_pattern_first, $temp_pattern_last;
  // Loop through all the temp patterns and compare them one at a time
  foreach ($temp_pattern_first AS $key => $pattern){
    // Check if either of these two items matches the current pattern
    if (preg_match($pattern, $item_one['ability_token']) && !preg_match($pattern, $item_two['ability_token'])){ return -1; }
    elseif (!preg_match($pattern, $item_one['ability_token']) && preg_match($pattern, $item_two['ability_token'])){ return 1; }
  }
  foreach ($temp_pattern_last AS $key => $pattern){
    // Check if either of these two items matches the current pattern
    if (preg_match($pattern, $item_one['ability_token']) && !preg_match($pattern, $item_two['ability_token'])){ return 1; }
    elseif (!preg_match($pattern, $item_one['ability_token']) && preg_match($pattern, $item_two['ability_token'])){ return -1; }
  }
  // If only one of the two items has a type, the one with goes first
  if (!empty($item_one['ability_token']) && empty($item_two['ability_token'])){ return 1; }
  elseif (empty($item_one['ability_token']) && !empty($item_two['ability_token'])){ return -1; }
  else {
    // If only one of the two items has a type, the one with goes first
    if ($item_one['ability_token'] > $item_two['ability_token']){ return 1; }
    elseif ($item_one['ability_token'] < $item_two['ability_token']){ return -1; }
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
$first_item_token = $first_item_token['ability_token'];
unset($temp_item_tokens);

// Count the number of items collected and filtered
$mmrpg_database_items_count = count($mmrpg_database_items);

// Loop through the database and generate the links for these items
$key_counter = 0;
$mmrpg_database_items_links = '';
$mmrpg_database_items_links_counter = 0;
$mmrpg_database_items_links_counter_incomplete = 0;
foreach ($mmrpg_database_items AS $item_key => $item_info){
  // If a type filter has been applied to the ability page
  $temp_item_types = array();
  if (!empty($item_info['ability_type'])){ $temp_item_types[] = $item_info['ability_type']; }
  if (!empty($item_info['ability_type2'])){ $temp_item_types[] = $item_info['ability_type2']; }
  if (empty($temp_item_types)){ $temp_item_types[] = 'none'; }
  if (isset($this_current_filter) && !in_array($this_current_filter, $temp_item_types)){ $key_counter++; continue; }
  // Collect the item sprite dimensions
  $item_image_size = !empty($item_info['ability_image_size']) ? $item_info['ability_image_size'] : 40;
  $item_image_size_text = $item_image_size.'x'.$item_image_size;
  $item_image_token = !empty($item_info['ability_image']) ? $item_info['ability_image'] : $item_info['ability_token'];
  $item_image_incomplete = $item_image_token == 'item' ? true : false;
  $item_is_active = !empty($this_current_token) && $this_current_token == $item_info['ability_token'] ? true : false;
  $item_title_text = $item_info['ability_name'];
  $item_image_path = 'images/abilities/'.$item_image_token.'/icon_right_'.$item_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
  // Start the output buffer and collect the generated markup
  ob_start();
  ?>
  <div title="<?= $item_title_text ?>" data-token="<?= $item_info['ability_token'] ?>" class="float float_left float_link ability_type ability_type_<?= (!empty($item_info['ability_type']) ? $item_info['ability_type'] : 'none').(!empty($item_info['ability_type2']) ? '_'.$item_info['ability_type2'] : '') ?><?= $ability_image_incomplete  ? ' incomplete' : '' ?>">
    <a class="sprite sprite_ability_link sprite_ability sprite_ability_sprite sprite_40x40 sprite_40x40_mugshot sprite_size_<?= $item_image_size_text ?>  item_status_active item_position_active <?= $item_key == $first_item_token ? 'sprite_ability_current ' : '' ?>" href="<?= 'database/items/'.preg_replace('/^item-/i', '', $item_info['ability_token']) ?>/" rel="<?= $item_image_incomplete ? 'nofollow' : 'follow' ?>">
      <? if($item_image_token != 'item'): ?>
        <img src="<?= $item_image_path ?>" width="<?= $item_image_size ?>" height="<?= $item_image_size ?>" alt="<?= $item_title_text ?>" />
      <? else: ?>
        <span><?= preg_replace('/\s+([a-z0-9]+)$/i', '<br />$1', $item_info['ability_name']) ?></span>
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