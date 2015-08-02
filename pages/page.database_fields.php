<?
/*
 * FIELDS DATABASE AJAX
 */

// If an explicit return request for the index was provided
if (!empty($_REQUEST['return']) && $_REQUEST['return'] == 'index'){
  // Exit with only the database link markup
  exit($mmrpg_database_fields_links);
}



/*
 * FIELDS DATABASE PAGE
 */

// Define the SEO variables for this page
$this_seo_title = 'Fields '.(!empty($this_current_filter) ? '('.$this_current_filter_name.' Type) ' : '').'| Database | '.$this_seo_title;
$this_seo_description = 'The field database contains detailed information about the Mega Man RPG Prototype\'s playable characters including their unlockable abilities, battle quotes, and sprite sheets. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Field Database'.(!empty($this_current_filter) ? ' ('.$this_current_filter_name.' Type) ' : '');
$this_graph_data['description'] = 'The field database contains detailed information about the Mega Man RPG Prototype\'s playable characters including their unlockable abilities, battle quotes, and sprite sheets.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Field Database';
$this_markup_counter = '<span class="count count_header">( '.(!empty($mmrpg_database_fields_links_counter) ? ($mmrpg_database_fields_links_counter == 1 ? '1 Field' : $mmrpg_database_fields_links_counter.' Fields') : '0 Fields').' )</span>';

// If a specific field has NOT been defined, show the quick-switcher
reset($mmrpg_database_fields);
if (!empty($this_current_token)){ $first_field_key = $this_current_token; }
else { $first_field_key = key($mmrpg_database_fields); }

// Only show the next part of a specific field was requested
if (!empty($this_current_token)){

  // Loop through the field database and display the appropriate data
  $key_counter = 0;
  foreach($mmrpg_database_fields AS $field_key => $field_info){

    // If a specific field has been requested and it's not this one
    if (!empty($this_current_token) && $this_current_token != $field_info['field_token']){ $key_counter++; continue; }
    //elseif ($key_counter > 0){ continue; }

    // If this is THE specific field requested (and one was specified)
    if (!empty($this_current_token) && $this_current_token == $field_info['field_token']){

      $this_field_image = !empty($field_info['field_image']) ? $field_info['field_image'] : $field_info['field_token'];
      if ($this_field_image == 'field'){ $this_seo_robots = 'noindex'; }
      // Define the SEO variables for this page
      $this_seo_title_backup = $this_seo_title;
      $this_seo_title = $field_info['field_name'].' | '.$this_seo_title;
      $this_seo_description = $field_info['field_name'].', one of the playable characters in the Mega Man RPG Prototype. '.$this_seo_description;
      // Update the markup header with the robot
      $this_markup_header = '<span class="hideme">'.$field_info['field_name'].' | </span>'.$this_markup_header;
      // Define the Open Graph variables for this page
      $this_graph_data['title'] .= ' | '.$field_info['field_name'];
      $this_graph_data['description'] = $field_info['field_name'].', one of the playable characters in the Mega Man RPG Prototype. '.$this_graph_data['description'];
      $this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/fields/'.$field_info['field_token'].'/mug_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;

    }

    // Collect the markup for this field and print it to the browser
    $temp_field_markup = mmrpg_field::print_database_markup($field_info, array('show_key' => $key_counter));
    echo $temp_field_markup;
    $key_counter++;
    break;

  }

}

// Only show the header if a specific field has not been selected
if (empty($this_current_token)){
  ?>
  <h2 class="subheader field_type_<?= isset($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
    Field Index
    <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Type )</span>' : '' ?>
  </h2>
  <?
}

?>

<div class="subbody subbody_databaselinks <?= empty($this_current_token) ? 'subbody_databaselinks_noajax' : '' ?>" data-class="fields" data-class-single="field" data-basetitle="<?= isset($this_seo_title_backup) ? $this_seo_title_backup : $this_seo_title ?>" data-current="<?= !empty($this_current_token) ? $this_current_token : '' ?>">
  <div class="<?= !empty($this_current_token) ? 'toggle_body' : '' ?>" style="<?= !empty($this_current_token) ? 'display: none;' : '' ?>">
    <? if(empty($this_current_token)): ?>
      <p class="text" style="clear: both;">
        The field database contains detailed information on <?= $mmrpg_database_fields_links_counter == 1 ? 'the' : 'all' ?> <?= isset($this_current_filter) ? $mmrpg_database_fields_links_counter.' <span class="type_span robot_type robot_type_'.$this_current_filter.'">'.$this_current_filter_name.' Type</span> ' : $mmrpg_database_fields_links_counter.' ' ?><?= $mmrpg_database_fields_links_counter == 1 ? 'battle field that appears ' : 'battle fields that appear ' ?> or will appear in the prototype, including <?= $mmrpg_database_fields_links_counter == 1 ? 'its' : 'each field\'s' ?> robot masters, mechas, stats, sprite sheets, and more.
        Click <?= $mmrpg_database_fields_links_counter == 1 ? 'the icon below to scroll to the' : 'any of the icons below to scroll to an' ?> field's summarized database entry and click the more link to see its full page with sprites and extended info. <?= isset($this_current_filter) ? 'If you wish to reset the field type filter, <a href="database/fields/">please click here</a>.' : '' ?>
      </p>
      <div class="text iconwrap"><?= preg_replace('/data-token="([-_a-z0-9]+)"/', 'data-anchor="$1"', $mmrpg_database_fields_links) ?></div>
    <? else: ?>
      <div class="text iconwrap"><?= $mmrpg_database_fields_links ?></div>
    <? endif; ?>
  </div>
  <? if(!empty($this_current_token)): ?>
    <a class="link link_toggle" data-state="collapsed">- Show Field Index -</a>
  <? else: ?>
    <div style="clear: both;">&nbsp;</div>
  <? endif; ?>
</div>

<?

// Only show the header if a specific field has not been selected
if (empty($this_current_token)){
  ?>
  <h2 class="subheader field_type_<?= isset($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
    Field Listing
    <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Type )</span>' : '' ?>
  </h2>
  <?
}

// If we're in the index view, loop through and display all fields
if (empty($this_current_token)){
  // Loop through the field database and display the appropriate data
  $key_counter = 0;
  foreach($mmrpg_database_fields AS $field_key => $field_info){
    // If a type filter has been applied to the field page
    $temp_field_types = array();
    if (!empty($field_info['field_type'])){ $temp_field_types[] = $field_info['field_type']; }
    if (!empty($field_info['field_type2'])){ $temp_field_types[] = $field_info['field_type2']; }
    if (empty($temp_field_types)){ $temp_field_types[] = 'none'; }
    if (isset($this_current_filter) && !in_array($this_current_filter, $temp_field_types)){ $key_counter++; continue; }
    // Collect information about this field
    $this_field_image = !empty($field_info['field_image']) ? $field_info['field_image'] : $field_info['field_token'];
    if ($this_field_image == 'field'){ $this_seo_fields = 'noindex'; }
    // Collect the markup for this field and print it to the browser
    $temp_field_markup = mmrpg_field::print_database_markup($field_info, array('layout_style' => 'website_compact', 'show_key' => $key_counter));
    echo $temp_field_markup;
    $key_counter++;
  }
}

?>