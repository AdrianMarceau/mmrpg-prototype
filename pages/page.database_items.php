<?
/*
 * ITEMS DATABASE AJAX
 */

// If an explicit return request for the index was provided
if (!empty($_REQUEST['return']) && $_REQUEST['return'] == 'index'){
  // Exit with only the database link markup
  exit($mmrpg_database_items_links);
}



/*
 * ITEM DATABASE PAGE
 */

// Define the SEO variables for this page
$this_seo_title = 'Items '.(!empty($this_current_filter) ? '('.$this_current_filter_name.' Type) ' : '').'| Database | '.$this_seo_title;
$this_seo_description = 'The item database contains detailed information about the Mega Man RPG Prototype\'s equippable abilities including their base stats, types, descriptions, and sprite sheets. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Item Database'.(!empty($this_current_filter) ? ' ('.$this_current_filter_name.' Type) ' : '');
$this_graph_data['description'] = 'The item database contains detailed information about the Mega Man RPG Prototype\'s collectable items including their stats, descriptions, and sprite sheets.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Item Database';
$this_markup_counter = '<span class="count count_header">( '.(!empty($mmrpg_database_items_links_counter) ? ($mmrpg_database_items_links_counter == 1 ? '1 Item' : $mmrpg_database_items_links_counter.' Items') : '0 Items').' )</span>';

// Prepend the token with the item word
if (!empty($this_current_token)){ $this_current_token = 'item-'.$this_current_token; }

// If a specific ability has NOT been defined, show the quick-switcher
reset($mmrpg_database_items);
if (!empty($this_current_token)){ $first_ability_key = $this_current_token; }
else { $first_ability_key = key($mmrpg_database_items); }

// Only show the next part of a specific ability was requested
if (!empty($this_current_token)){

  // Loop through the item database and display the appropriate data
  $key_counter = 0;
  foreach($mmrpg_database_items AS $ability_key => $ability_info){

    // If a specific ability has been requested and it's not this one
    if (!empty($this_current_token) && $this_current_token != $ability_info['ability_token']){ $key_counter++; continue; }
    //elseif ($key_counter > 0){ continue; }

    // If this is THE specific ability requested (and one was specified)
    if (!empty($this_current_token) && $this_current_token == $ability_info['ability_token']){

      $this_ability_image = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];
      if ($this_ability_image == 'ability'){ $this_seo_robots = 'noindex'; }
      $this_temp_description = 'The '.$ability_info['ability_name'].' is ';
      if (!empty($ability_info['ability_type'])){
        if (empty($ability_info['ability_type2'])){ $this_temp_description .= (preg_match('/^(a|e|i|o|u)/', $ability_info['ability_type']) ? 'an ' : 'a ').ucfirst($ability_info['ability_type']).' type'; }
        else { $this_temp_description .= (preg_match('/^(a|e|i|o|u)/', $ability_info['ability_type']) ? 'an ' : 'a ').ucfirst($ability_info['ability_type']).' and '.ucfirst($ability_info['ability_type2']).' type'; }
      } else {
        $this_temp_description .= 'a neutral type';
      }
      $this_temp_description .= ' ability in the Mega Man RPG Prototype.';
      // Define the SEO variables for this page
      $this_seo_title_backup = $this_seo_title;
      $this_seo_title = $ability_info['ability_name'].' | '.$this_seo_title;
      $this_seo_description = $this_temp_description.'  '.$ability_info['ability_description'].'  '.$this_seo_description;
      // Update the markup header with the robot
      $this_markup_header = '<span class="hideme">'.$ability_info['ability_name'].' | </span>'.$this_markup_header;
      // Define the Open Graph variables for this page
      $this_graph_data['title'] .= ' | '.$ability_info['ability_name'];
      $this_graph_data['description'] = $this_temp_description.'  '.$ability_info['ability_description'].'  '.$this_graph_data['description'];
      $this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/abilities/'.$ability_info['ability_token'].'/icon_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;

    }

    // Collect the markup for this ability and print it to the browser
    $temp_ability_markup = mmrpg_ability::print_database_markup($ability_info, array('show_key' => $key_counter));
    echo $temp_ability_markup;
    $key_counter++;
    break;

  }

}

// Only show the header if a specific item has not been selected
if (empty($this_current_token)){
  ?>
  <h2 class="subheader field_type_<?= isset($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
    Item Index
    <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Type )</span>' : '' ?>
  </h2>
  <?
}

?>

<div class="subbody subbody_databaselinks <?= empty($this_current_token) ? 'subbody_databaselinks_noajax' : '' ?>" data-class="items" data-class-single="item" data-basetitle="<?= isset($this_seo_title_backup) ? $this_seo_title_backup : $this_seo_title ?>" data-current="<?= !empty($this_current_token) ? $this_current_token : '' ?>">
  <div class="<?= !empty($this_current_token) ? 'toggle_body' : '' ?>" style="<?= !empty($this_current_token) ? 'display: none;' : '' ?>">
    <? if(empty($this_current_token)): ?>
      <p class="text" style="clear: both;">
        The item database contains detailed information on <?= $mmrpg_database_items_links_counter == 1 ? 'the' : 'all' ?> <?= isset($this_current_filter) ? $mmrpg_database_items_links_counter.' <span class="type_span robot_type robot_type_'.($this_current_filter == 'multi' ? 'shield' : ($this_current_filter == 'bonus' ? 'laser' : $this_current_filter)).'">'.$this_current_filter_name.' Type</span> ' : $mmrpg_database_items_links_counter.' ' ?><?= $mmrpg_database_items_links_counter == 1 ? 'collectable item that appears ' : 'collectable items that appear ' ?> or will appear in the prototype, including <?= $mmrpg_database_items_links_counter == 1 ? 'its' : 'each item\'s' ?> stats, description, and sprite sheets.
        Click <?= $mmrpg_database_items_links_counter == 1 ? 'the icon below to scroll to the' : 'any of the icons below to scroll to an' ?> item's summarized database entry and click the more link to see its full page with sprites and extended info. <?= isset($this_current_filter) ? 'If you wish to reset the item type filter, <a href="database/items/">please click here</a>.' : '' ?>
      </p>
      <div class="text iconwrap"><?= preg_replace('/data-token="([-_a-z0-9]+)"/', 'data-anchor="$1"', $mmrpg_database_items_links) ?></div>
    <? else: ?>
      <div class="text iconwrap"><?= $mmrpg_database_items_links ?></div>
    <? endif; ?>
  </div>
  <? if(!empty($this_current_token)): ?>
    <a class="link link_toggle" data-state="collapsed">- Show Item Index -</a>
  <? else: ?>
    <div style="clear: both;">&nbsp;</div>
  <? endif; ?>
</div>

<?

// Only show the header if a specific item has not been selected
if (empty($this_current_token)){
  ?>
  <h2 class="subheader field_type_<?= isset($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
    Item Listing
    <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Type )</span>' : '' ?>
  </h2>
  <?
}

// If we're in the index view, loop through and display all abilities
if (empty($this_current_token)){
  // Loop through the ability database and display the appropriate data
  $key_counter = 0;
  foreach($mmrpg_database_items AS $item_key => $item_info){
    // If a type filter has been applied to the ability page
    $temp_item_types = array();
    if (!empty($item_info['ability_type'])){ $temp_item_types[] = $item_info['ability_type']; }
    if (!empty($item_info['ability_type2'])){ $temp_item_types[] = $item_info['ability_type2']; }
	  if (preg_match('/^item-score-ball-(red|blue|green|purple)$/i', $item_info['ability_token'])){ $temp_item_types[] = 'bonus'; }
	  elseif (preg_match('/^item-super-(pellet|capsule)$/i', $item_info['ability_token'])){ $temp_item_types[] = 'multi'; }
    if (empty($temp_item_types)){ $temp_item_types[] = 'none'; }
    if (isset($this_current_filter) && !in_array($this_current_filter, $temp_item_types)){ $key_counter++; continue; }
    // Collect information about this ability
    $this_ability_image = !empty($item_info['ability_image']) ? $item_info['ability_image'] : $item_info['ability_token'];
    if ($this_ability_image == 'ability'){ $this_seo_abilities = 'noindex'; }
    // Collect the markup for this ability and print it to the browser
    $temp_item_markup = mmrpg_ability::print_database_markup($item_info, array('layout_style' => 'website_compact', 'show_key' => $key_counter));
    echo $temp_item_markup;
    $key_counter++;
  }
}


?>