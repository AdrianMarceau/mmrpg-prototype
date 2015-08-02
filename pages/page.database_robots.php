<?
/*
 * ROBOTS DATABASE AJAX
 */

// If an explicit return request for the index was provided
if (!empty($_REQUEST['return']) && $_REQUEST['return'] == 'index'){
  // Exit with only the database link markup
  exit($mmrpg_database_robots_links);
}



/*
 * ROBOT DATABASE PAGE
 */

// Define the SEO variables for this page
$this_seo_title = 'Robots '.(!empty($this_current_filter) ? '('.$this_current_filter_name.' Core) ' : '').'| Database | '.$this_seo_title;
$this_seo_description = 'The robot database contains detailed information about the Mega Man RPG Prototype\'s unlockable robots including their equippable abilities, battle quotes, base stats, weaknesses, resistances, affinities, immunities, and sprite sheets. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Robot Database'.(!empty($this_current_filter) ? ' ('.$this_current_filter_name.' Core) ' : '');
$this_graph_data['description'] = 'The robot database contains detailed information about the Mega Man RPG Prototype\'s unlockable robots including their equippable abilities, battle quotes, base stats, weaknesses, resistances, affinities, immunities, and sprite sheets.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
//$this_markup_header = 'Mega Man RPG Prototype Robot Database <span class="count">( '.(!empty($mmrpg_database_robots_count) ? ($mmrpg_database_robots_count == 1 ? '1 Robot' : $mmrpg_database_robots_count.' Robots') : '0 Robots').' )';
$this_markup_header = 'Mega Man RPG Prototype Robot Database';
$this_markup_counter = '<span class="count count_header">( '.(!empty($mmrpg_database_robots_links_counter) ? ($mmrpg_database_robots_links_counter == 1 ? '1 Robot' : $mmrpg_database_robots_links_counter.' Robots') : '0 Robots').' )</span>';

// If a specific robot has NOT been defined, show the quick-switcher
reset($mmrpg_database_robots);
if (!empty($this_current_token)){ $first_robot_key = $this_current_token; }
else { $first_robot_key = key($mmrpg_database_robots); }

/*
// GENERATE ROBOT QUOTES CSV
$temp_quotes = array('start', 'taunt', 'victory', 'defeat');
$temp_csv = array();
$temp_csv[] = array('Robot Name', 'Robot Token', 'Start Quote', 'Taunt Quote', 'Victory Quote', 'Defeat Quote');
foreach ($mmrpg_database_robots AS $key => $info){
  $row = array($info['robot_number'].' '.$info['robot_name'], $info['robot_token']);
  foreach ($temp_quotes AS $type){ $row[] = $info['robot_quotes']['battle_'.$type]; }
  $temp_csv[] = $row;
}
foreach ($temp_csv AS $key => $row){
  $row2 = array();
  foreach ($row AS $val){ $row2[] = '"'.str_replace('"', '\\"', $val).'"'; }
  $temp_csv[$key] = implode(',', $row2);
}
$temp_csv = implode(",\n", $temp_csv);
header('Content-type: text/plain; charset=UTF-8');
die(print_r($temp_csv, true));
*/

/*
// GENERATE ROBOT DESCRIPITIONS CSV
$temp_csv = array();
$temp_csv[] = array('Robot Name', 'Robot Token', 'Robot Class', 'Robot Description');
foreach ($mmrpg_database_robots AS $key => $info){
  $row = array($info['robot_number'].' '.$info['robot_name'], $info['robot_token'], $info['robot_description'], $info['robot_description2']);
  $temp_csv[] = $row;
}
foreach ($temp_csv AS $key => $row){
  $row2 = array();
  foreach ($row AS $val){ $row2[] = '"'.str_replace('"', '\\"', $val).'"'; }
  $temp_csv[$key] = implode(',', $row2);
}
$temp_csv = implode(",\n", $temp_csv);
header('Content-type: text/plain; charset=UTF-8');
die(print_r($temp_csv, true));
*/

// Only show the next part of a specific robot was requested
if (!empty($this_current_token)){

  // Loop through the robot database and display the appropriate data
  $key_counter = 0;
  foreach($mmrpg_database_robots AS $robot_key => $robot_info){

    // If a specific robot has been requested and it's not this one
    if (!empty($this_current_token) && $this_current_token != $robot_info['robot_token']){ $key_counter++; continue; }
    //elseif ($key_counter > 0){ continue; }

    // If this is THE specific robot requested (and one was specified)
    if (!empty($this_current_token) && $this_current_token == $robot_info['robot_token']){

      $this_robot_image = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
      $this_robot_image_size = (!empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40) * 2;
      $this_robot_image_size_text = $this_robot_image_size.'x'.$this_robot_image_size;
      if ($this_robot_image == 'robot'){ $this_seo_robots = 'noindex'; }
      // Update the markup header with the robot
      $this_markup_header = '<span class="hideme">'.$robot_info['robot_number'].' '.$robot_info['robot_name'].' | </span>'.$this_markup_header;
      // Check if this is a mecha and prepare extra text
      $robot_info['robot_name_append'] = '';
      if (!empty($robot_info['robot_class']) && $robot_info['robot_class'] == 'mecha'){
        $robot_info['robot_generation'] = '1st';
        if (preg_match('/-2$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '2nd'; $robot_info['robot_name_append'] = ' 2'; }
        elseif (preg_match('/-3$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '3rd'; $robot_info['robot_name_append'] = ' 3'; }
      }
      // Define the SEO variables for this page
      $this_seo_title_backup = $this_seo_title;
      $this_seo_title = $robot_info['robot_name'].$robot_info['robot_name_append'].' | '.$this_seo_title;
      $this_seo_description = $robot_info['robot_number'].' '.$robot_info['robot_name'].', a '.(!empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')).' core' : 'special').' robot master in the Mega Man RPG Prototype.  '.$this_seo_description;
      // Define the Open Graph variables for this page
      $this_graph_data['title'] .= ' | '.$robot_info['robot_name'];
      $this_graph_data['description'] = $robot_info['robot_number'].' '.$robot_info['robot_name'].', a '.(!empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')).' core' : 'special').' robot master in the Mega Man RPG Prototype. '.$this_graph_data['description'];
      $this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/robots/'.$robot_info['robot_token'].'/mug_right_'.$this_robot_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;

    }

    // Collect the markup for this robot and print it to the browser
    $temp_robot_markup = mmrpg_robot::print_database_markup($robot_info, array('show_key' => $key_counter));
    echo $temp_robot_markup;
    $key_counter++;
    break;

  }

}


// Only show the header if a specific robot has not been selected
if (empty($this_current_token)){
  ?>
  <h2 class="subheader field_type_<?= isset($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
    Robot Index
    <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Core )</span>' : '' ?>
  </h2>
  <?
}

?>

<div class="subbody subbody_databaselinks <?= empty($this_current_token) ? 'subbody_databaselinks_noajax' : '' ?>" data-class="robots" data-class-single="robot" data-basetitle="<?= isset($this_seo_title_backup) ? $this_seo_title_backup : $this_seo_title ?>" data-current="<?= !empty($this_current_token) ? $this_current_token : '' ?>">
  <div class="<?= !empty($this_current_token) ? 'toggle_body' : '' ?>" style="<?= !empty($this_current_token) ? 'display: none;' : '' ?>">
    <? if(empty($this_current_token)): ?>
      <p class="text" style="clear: both;">
        The robot database contains detailed information on <?= $mmrpg_database_robots_links_counter == 1 ? 'the' : 'all' ?> <?= isset($this_current_filter) ? $mmrpg_database_robots_links_counter.' <span class="type_span robot_type robot_type_'.$this_current_filter.'">'.$this_current_filter_name.' Core</span> ' : $mmrpg_database_robots_links_counter.' ' ?><?= $mmrpg_database_robots_links_counter == 1 ? 'robot master that appears ' : 'robot masters that appear ' ?> or will appear in the prototype, including <?= $mmrpg_database_robots_links_counter == 1 ? 'its' : 'each robot\'s' ?> base stats, weaknesses, resistances, affinities, immunities, unlockable abilities, battle quotes, sprite sheets, and more.
        Click <?= $mmrpg_database_robots_links_counter == 1 ? 'the mugshot below to scroll to the' : 'any of the mugshots below to scroll to a' ?> robot's summarized database entry and click the more link to see its full page with sprites and extended info. <?= isset($this_current_filter) ? 'If you wish to reset the core type filter, <a href="database/robots/">please click here</a>.' : '' ?>
      </p>
      <div class="text iconwrap"><?= preg_replace('/data-token="([-_a-z0-9]+)"/', 'data-anchor="$1"', $mmrpg_database_robots_links) ?></div>
    <? else: ?>
      <div class="text iconwrap"><?= $mmrpg_database_robots_links ?></div>
    <? endif; ?>
  </div>
  <? if(!empty($this_current_token)): ?>
    <a class="link link_toggle" data-state="collapsed">- Show Robot Index -</a>
  <? else: ?>
    <div style="clear: both;">&nbsp;</div>
  <? endif; ?>
</div>

<?

// Only show the header if a specific robot has not been selected
if (empty($this_current_token)){
  ?>
  <h2 class="subheader field_type_<?= isset($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
    Robot Listing
    <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Core )</span>' : '' ?>
  </h2>
  <?
}

// If we're in the index view, loop through and display all robots
if (empty($this_current_token)){
  // Loop through the robot database and display the appropriate data
  $key_counter = 0;
  foreach($mmrpg_database_robots AS $robot_key => $robot_info){
    // If a type filter has been applied to the robot page
  if (isset($this_current_filter) && $this_current_filter == 'none' && $robot_info['robot_core'] != ''){ $key_counter++; continue; }
  elseif (isset($this_current_filter) && $this_current_filter != 'none' && $robot_info['robot_core'] != $this_current_filter && $robot_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }
    // Collect information about this robot
    $this_robot_image = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
    if ($this_robot_image == 'robot'){ $this_seo_robots = 'noindex'; }
    // Collect the markup for this robot and print it to the browser
    $temp_robot_markup = mmrpg_robot::print_database_markup($robot_info, array('layout_style' => 'website_compact', 'show_key' => $key_counter));
    echo $temp_robot_markup;
    $key_counter++;
  }
}

?>