<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url, $DB;
global $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_abilities, $mmrpg_database_types;
global $mmrpg_stat_base_max_value;
static $mmrpg_database_fields;
if (empty($mmrpg_database_fields)){ $mmrpg_database_fields = mmrpg_field::get_index(); }

// Define the print style defaults
if (!isset($print_options['layout_style'])){ $print_options['layout_style'] = 'website'; }
if ($print_options['layout_style'] == 'website'){
  if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
  if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
  if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = true; }
  if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
  if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
  if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = true; }
  if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
  if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
  if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
  if (!isset($print_options['show_stats'])){ $print_options['show_stats'] = true; }
} elseif ($print_options['layout_style'] == 'website_compact'){
  if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
  if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
  if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = false; }
  if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
  if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
  if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = false; }
  if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
  if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
  if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
  if (!isset($print_options['show_stats'])){ $print_options['show_stats'] = false; }
} elseif ($print_options['layout_style'] == 'event'){
  if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
  if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = false; }
  if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = false; }
  if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
  if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
  if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = false; }
  if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
  if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = false; }
  if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
  if (!isset($print_options['show_stats'])){ $print_options['show_stats'] = false; }
}

// Collect the robot sprite dimensions
$robot_image_size = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
$robot_image_size_text = $robot_image_size.'x'.$robot_image_size;
$robot_image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
//die('<pre>$robot_info = '.print_r($robot_info, true).'</pre>');

// Collect the robot's type for background display
$robot_header_types = 'type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none').' ';

// Define the sprite sheet alt and title text
$robot_sprite_size = $robot_image_size * 2;
$robot_sprite_size_text = $robot_sprite_size.'x'.$robot_sprite_size;
$robot_sprite_title = $robot_info['robot_name'];
//$robot_sprite_title = $robot_info['robot_number'].' '.$robot_info['robot_name'];
//$robot_sprite_title .= ' Sprite Sheet | Robot Database | Mega Man RPG Prototype';

// If this is a mecha, define it's generation for display
$robot_info['robot_name_append'] = '';
if (!empty($robot_info['robot_class']) && $robot_info['robot_class'] == 'mecha'){
  $robot_info['robot_generation'] = '1st';
  if (preg_match('/-2$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '2nd'; $robot_info['robot_name_append'] = ' 2'; }
  elseif (preg_match('/-3$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '3rd'; $robot_info['robot_name_append'] = ' 3'; }
} elseif (preg_match('/^duo/i', $robot_info['robot_token'])){

}

// Define the sprite frame index for robot images
$robot_sprite_frames = array('base','taunt','victory','defeat','shoot','throw','summon','slide','defend','damage','base2');

// Collect the field info if applicable
$field_info_array = array();
$temp_robot_fields = array();
if (!empty($robot_info['robot_field']) && $robot_info['robot_field'] != 'field'){ $temp_robot_fields[] = $robot_info['robot_field']; }
if (!empty($robot_info['robot_field2'])){ $temp_robot_fields = array_merge($temp_robot_fields, $robot_info['robot_field2']); }
if ($temp_robot_fields){
  foreach ($temp_robot_fields AS $key => $token){
    if (!empty($mmrpg_database_fields[$token])){
      $field_info_array[] = mmrpg_field::parse_index_info($mmrpg_database_fields[$token]);
    }
  }
}

// Define the class token for this robot
$robot_class_token = '';
$robot_class_token_plural = '';
if ($robot_info['robot_class'] == 'master'){
  $robot_class_token = 'robot';
  $robot_class_token_plural = 'robots';
} elseif ($robot_info['robot_class'] == 'mecha'){
  $robot_class_token = 'mecha';
  $robot_class_token_plural = 'mechas';
} elseif ($robot_info['robot_class'] == 'boss'){
  $robot_class_token = 'boss';
  $robot_class_token_plural = 'bosses';
}
// Define the default class tokens for "empty" images
$default_robot_class_tokens = array('robot', 'mecha', 'boss');

// Automatically disable sections if content is unavailable
if (empty($robot_info['robot_description2'])){ $print_options['show_description'] = false;  }
if (isset($robot_info['robot_image_sheets']) && $robot_info['robot_image_sheets'] === 0){ $print_options['show_sprites'] = false; }
elseif (in_array($robot_image_token, $default_robot_class_tokens)){ $print_options['show_sprites'] = false; }

// Define the base URLs for this robot
$database_url = 'database/';
$database_category_url = $database_url;
if ($robot_info['robot_class'] == 'master'){ $database_category_url .= 'robots/'; }
elseif ($robot_info['robot_class'] == 'mecha'){ $database_category_url .= 'mechas/'; }
elseif ($robot_info['robot_class'] == 'boss'){ $database_category_url .= 'bosses/'; }
$database_category_robot_url = $database_category_url.$robot_info['robot_token'].'/';

// Calculate the robot base stat total
$robot_info['robot_total'] = 0;
$robot_info['robot_total'] += $robot_info['robot_energy'];
$robot_info['robot_total'] += $robot_info['robot_attack'];
$robot_info['robot_total'] += $robot_info['robot_defense'];
$robot_info['robot_total'] += $robot_info['robot_speed'];

// Calculate this robot's maximum base stat for reference
$robot_info['robot_max_stat_name'] = 'unknown';
$robot_info['robot_max_stat_value'] = 0;
$temp_types = array('energy', 'attack', 'defense', 'speed');
foreach ($temp_types AS $type){
  if ($robot_info['robot_'.$type] > $robot_info['robot_max_stat_value']){
    $robot_info['robot_max_stat_value'] = $robot_info['robot_'.$type];
    $robot_info['robot_max_stat_name'] = $type;
  }
}


// Collect the database records for this robot
if ($print_options['show_records']){

  global $DB;
  $temp_robot_records = array('robot_encountered' => 0, 'robot_defeated' => 0, 'robot_unlocked' => 0, 'robot_summoned' => 0, 'robot_scanned' => 0);
  //$temp_robot_records['player_count'] = $DB->get_value("SELECT COUNT(board_id) AS player_count  FROM mmrpg_leaderboard WHERE board_robots LIKE '%[".$robot_info['robot_token'].":%' AND board_points > 0", 'player_count');
  $temp_player_query = "SELECT
    mmrpg_saves.user_id,
    mmrpg_saves.save_values_robot_database,
    mmrpg_leaderboard.board_points
  	FROM mmrpg_saves
    LEFT JOIN mmrpg_leaderboard ON mmrpg_leaderboard.user_id = mmrpg_saves.user_id
    WHERE mmrpg_saves.save_values_robot_database LIKE '%\"{$robot_info['robot_token']}\"%' AND mmrpg_leaderboard.board_points > 0;";
  $temp_player_list = $DB->get_array_list($temp_player_query);
  if (!empty($temp_player_list)){
    foreach ($temp_player_list AS $temp_data){
      $temp_values = !empty($temp_data['save_values_robot_database']) ? json_decode($temp_data['save_values_robot_database'], true) : array();
      $temp_entry = !empty($temp_values[$robot_info['robot_token']]) ? $temp_values[$robot_info['robot_token']] : array();
      foreach ($temp_robot_records AS $temp_record => $temp_count){
        if (!empty($temp_entry[$temp_record])){ $temp_robot_records[$temp_record] += $temp_entry[$temp_record]; }
      }
    }
  }
  $temp_values = array();
  //echo '<pre>'.print_r($temp_robot_records, true).'</pre>';

}

// Define the common stat container variables
$stat_container_percent = 74;
$stat_base_max_value = 2000;
$stat_padding_area = 76;
if (!empty($mmrpg_stat_base_max_value[$robot_info['robot_class']])){ $stat_base_max_value = $mmrpg_stat_base_max_value[$robot_info['robot_class']]; }
elseif ($robot_info['robot_class'] == 'master'){ $stat_base_max_value = 400; }
elseif ($robot_info['robot_class'] == 'mecha'){ $stat_base_max_value = 400; }
elseif ($robot_info['robot_class'] == 'boss'){ $stat_base_max_value = 2000; }

// If this is a mecha class, do not show potential stat totals... for now
//if ($robot_info['robot_class'] != 'master'){
//  $print_options['show_stats'] = false;
//}

// Define the variable to hold compact footer link markup
$compact_footer_link_markup = array();
//$compact_footer_link_markup[] = '<a class="link link_permalink" href="'.$database_category_robot_url.'">+ Huh?</a>';

// Add a link to the sprites in the compact footer markup
if (!in_array($robot_image_token, $default_robot_class_tokens)){ $compact_footer_link_markup[] = '<a class="link '.$robot_header_types.'" href="'.$database_category_robot_url.'#sprites">#Sprites</a>'; }
if (!empty($robot_info['robot_quotes']['battle_start'])){ $compact_footer_link_markup[] = '<a class="link '.$robot_header_types.'" href="'.$database_category_robot_url.'#quotes">#Quotes</a>'; }
if (!empty($robot_info['robot_description2'])){ $compact_footer_link_markup[] = '<a class="link '.$robot_header_types.'" href="'.$database_category_robot_url.'#description">#Description</a>'; }
if (!empty($robot_info['robot_abilities'])){ $compact_footer_link_markup[] = '<a class="link '.$robot_header_types.'" href="'.$database_category_robot_url.'#abilities">#Abilities</a>'; }
$compact_footer_link_markup[] = '<a class="link '.$robot_header_types.'" href="'.$database_category_robot_url.'#stats">#Stats</a>';
$compact_footer_link_markup[] = '<a class="link '.$robot_header_types.'" href="'.$database_category_robot_url.'#records">#Records</a>';

/*
$compact_footer_link_markup[] = '<a class="link '.$robot_header_types.'" href="'.$database_category_robot_url.'">View More</a>';
*/

// Start the output buffer
ob_start();
/*<div class="database_container database_<?= $robot_class_token ?>_container database_<?= $print_options['layout_style'] ?>_container" data-token="<?=$robot_info['robot_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">*/
?>
<div class="database_container layout_<?= str_replace('website_', '', $print_options['layout_style']) ?>" data-token="<?=$robot_info['robot_token']?>">

  <? if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
    <a class="anchor" id="<?=$robot_info['robot_token']?>"></a>
  <? endif; ?>

  <div class="subbody event event_triple event_visible" data-token="<?=$robot_info['robot_token']?>">

    <? if($print_options['show_mugshot']): ?>

      <div class="this_sprite sprite_left" style="height: 40px;">
        <? if($print_options['show_mugshot']): ?>
          <? if($print_options['show_key'] !== false): ?>
            <div class="mugshot robot_type <?= $robot_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
          <? endif; ?>
          <? if (!in_array($robot_image_token, $default_robot_class_tokens)){ ?>
            <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: url(i/r/<?= $robot_image_token ?>/mr<?= $robot_image_size ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active"><?=$robot_info['robot_name']?>'s Mugshot</div></div>
          <? } else { ?>
            <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active">No Image</div></div>
          <? } ?>
        <? endif; ?>
      </div>

    <? endif; ?>

    <? if($print_options['show_basics']): ?>

      <h2 class="header header_left <?= $robot_header_types ?> <?= (!$print_options['show_mugshot']) ? 'nomug' : '' ?>" style="<?= (!$print_options['show_mugshot']) ? 'margin-left: 0;' : '' ?>">
        <? if($print_options['layout_style'] == 'website_compact'): ?>
          <a href="<?= $database_category_robot_url ?>"><?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?></a>
        <? else: ?>
          <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Data
        <? endif; ?>
        <div class="header_core robot_type"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'mecha' ? ' Type' : ' Core' ?></div>
      </h2>
      <div class="body body_left <?= !$print_options['show_mugshot'] ? 'fullsize' : '' ?>">
        <table class="full">
          <colgroup>
            <? if($print_options['layout_style'] == 'website'): ?>
              <col width="48%" />
              <col width="1%" />
              <col width="48%" />
            <? else: ?>
              <col width="40%" />
              <col width="1%" />
              <col width="59%" />
            <? endif; ?>
          </colgroup>
          <tbody>
            <? if($print_options['layout_style'] != 'event'): ?>
              <tr>
                <td  class="right">
                  <label>Name :</label>
                  <span class="robot_type" style="width: auto;"><?=$robot_info['robot_name']?></span>
                  <? if (!empty($robot_info['robot_generation'])){ ?><span class="robot_type" style="width: auto;"><?=$robot_info['robot_generation']?> Gen</span><? } ?>
                </td>
                <td></td>
                <td class="right">
                  <?
                  // Define the source game string
                  if ($robot_info['robot_token'] == 'mega-man' || $robot_info['robot_token'] == 'roll'){ $temp_source_string = 'Mega Man'; }
                  elseif ($robot_info['robot_token'] == 'proto-man'){ $temp_source_string = 'Mega Man 3'; }
                  elseif ($robot_info['robot_token'] == 'bass'){ $temp_source_string = 'Mega Man 7'; }
                  elseif ($robot_info['robot_token'] == 'disco' || $robot_info['robot_token'] == 'rhythm'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                  elseif (preg_match('/^flutter-fly/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man RPG Prototype'; }
                  elseif (preg_match('/^beetle-borg/i', $robot_info['robot_token'])){ $temp_source_string = '<span title="Rockman &amp; Forte 2 : Challenger from the Future (JP)">Mega Man &amp; Bass 2</span>'; }
                  elseif ($robot_info['robot_token'] == 'bond-man'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                  elseif ($robot_info['robot_token'] == 'enker'){ $temp_source_string = 'Mega Man : Dr. Wily\'s Revenge'; }
                  elseif ($robot_info['robot_token'] == 'punk'){ $temp_source_string = 'Mega Man III'; }
                  elseif ($robot_info['robot_token'] == 'ballade'){ $temp_source_string = 'Mega Man IV'; }
                  elseif ($robot_info['robot_token'] == 'quint'){ $temp_source_string = 'Mega Man II'; }
                  elseif ($robot_info['robot_token'] == 'oil-man' || $robot_info['robot_token'] == 'time-man'){ $temp_source_string = 'Mega Man Powered Up'; }
                  elseif ($robot_info['robot_token'] == 'solo'){ $temp_source_string = 'Mega Man Star Force 3'; }
                  elseif (preg_match('/^duo-2/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man 8'; }
                  elseif (preg_match('/^duo/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man Power Battles'; }
                  elseif (preg_match('/^trio/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man RPG Prototype'; }
                  elseif ($robot_info['robot_token'] == 'cosmo-man' || $robot_info['robot_token'] == 'lark-man'){ $temp_source_string = 'Mega Man Battle Network 5'; }
                  elseif ($robot_info['robot_token'] == 'laser-man'){ $temp_source_string = 'Mega Man Battle Network 4'; }
                  elseif ($robot_info['robot_token'] == 'desert-man'){ $temp_source_string = 'Mega Man Battle Network 3'; }
                  elseif ($robot_info['robot_token'] == 'planet-man' || $robot_info['robot_token'] == 'gate-man'){ $temp_source_string = 'Mega Man Battle Network 2'; }
                  elseif ($robot_info['robot_token'] == 'shark-man' || $robot_info['robot_token'] == 'number-man' || $robot_info['robot_token'] == 'color-man'){ $temp_source_string = 'Mega Man Battle Network'; }
                  elseif ($robot_info['robot_token'] == 'trill' || $robot_info['robot_token'] == 'slur'){ $temp_source_string = '<span title="Rockman.EXE Stream (JP)">Mega Man NT Warrior</span>'; }
                  elseif ($robot_info['robot_game'] == 'MM085'){ $temp_source_string = '<span title="Rockman &amp; Forte (JP)">Mega Man &amp; Bass</span>'; }
                  elseif ($robot_info['robot_game'] == 'MM30'){ $temp_source_string = 'Mega Man V'; }
                  elseif ($robot_info['robot_game'] == 'MM21'){ $temp_source_string = 'Mega Man : The Wily Wars'; }
                  elseif ($robot_info['robot_game'] == 'MM19'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                  elseif ($robot_info['robot_game'] == 'MMEXE'){ $temp_source_string = 'Mega Man EXE'; }
                  elseif ($robot_info['robot_game'] == 'MM00' || $robot_info['robot_game'] == 'MM01'){ $temp_source_string = 'Mega Man'; }
                  elseif (preg_match('/^MM([0-9]{2})$/', $robot_info['robot_game'])){ $temp_source_string = 'Mega Man '.ltrim(str_replace('MM', '', $robot_info['robot_game']), '0'); }
                  elseif (!empty($robot_info['robot_game'])){ $temp_source_string = $robot_info['robot_game']; }
                  else { $temp_source_string = '???'; }
                  ?>
                  <label>Source :</label>
                  <span class="robot_type"><?= $temp_source_string ?></span>
                </td>
              </tr>
            <? endif; ?>
            <tr>
              <td  class="right">
                <label>Model :</label>
                <span class="robot_type"><?=$robot_info['robot_number']?></span>
              </td>
              <td></td>
              <td  class="right">
                <label>Class :</label>
                <span class="robot_type"><?= !empty($robot_info['robot_description']) ? $robot_info['robot_description'] : '&hellip;' ?></span>
              </td>
            </tr>
            <tr>
              <td  class="right">
                <label>Type :</label>
                <? if($print_options['layout_style'] != 'event'): ?>
                  <? if(!empty($robot_info['robot_core2'])): ?>
                    <span class="robot_type type_<?= $robot_info['robot_core'].'_'.$robot_info['robot_core2'] ?>">
                      <a href="<?= $database_category_url ?><?= $robot_info['robot_core'] ?>/"><?= ucfirst($robot_info['robot_core']) ?></a> /
                      <a href="<?= $database_category_url ?><?= $robot_info['robot_core2'] ?>/"><?= ucfirst($robot_info['robot_core2']) ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                    </span>
                  <? else: ?>
                    <a href="<?= $database_category_url ?><?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/" class="robot_type type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                  <? endif; ?>
                <? else: ?>
                  <span class="robot_type type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></span>
                <? endif; ?>
              </td>
              <td></td>
              <td  class="right">
                <label><?= empty($field_info_array) || count($field_info_array) == 1 ? 'Field' : 'Fields' ?> :</label>
                <?
                // Loop through the robots fields if available
                if (!empty($field_info_array)){
                  foreach ($field_info_array AS $key => $field_info){
                    ?>
                      <? if($print_options['layout_style'] != 'event'): ?>
                        <a href="<?= $database_url ?>fields/<?= $field_info['field_token'] ?>/" class="field_type field_type_<?= (!empty($field_info['field_type']) ? $field_info['field_type'] : 'none').(!empty($field_info['field_type2']) ? '_'.$field_info['field_type2'] : '') ?>" <?= $key > 0 ? 'title="'.$field_info['field_name'].'"' : '' ?>><?= $key == 0 ? $field_info['field_name'] : preg_replace('/^([a-z0-9]+)\s([a-z0-9]+)$/i', '$1&hellip;', $field_info['field_name']) ?></a>
                      <? else: ?>
                        <span class="field_type field_type_<?= (!empty($field_info['field_type']) ? $field_info['field_type'] : 'none').(!empty($field_info['field_type2']) ? '_'.$field_info['field_type2'] : '') ?>" <?= $key > 0 ? 'title="'.$field_info['field_name'].'"' : '' ?>><?= $key == 0 ? $field_info['field_name'] : preg_replace('/^([a-z0-9]+)\s([a-z0-9]+)$/i', '$1&hellip;', $field_info['field_name']) ?></span>
                      <? endif; ?>
                    <?
                  }
                }
                // Otherwise, print an empty field
                else {
                  ?>
                    <span class="field_type">&hellip;</span>
                  <?
                }
                ?>
              </td>
            </tr>
            <tr>
              <td  class="right">
                <label>Energy :</label>
                <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                  <? if(false && $print_options['layout_style'] == 'website_compact'): ?>
                    <span class="robot_stat type_energy" style="padding-left: <?= round( ( ($robot_info['robot_energy'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_energy'] ?></span></span>
                  <? else: ?>
                    <span class="robot_stat type_energy" style="padding-left: <?= round( ( ($robot_info['robot_energy'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_energy'] ?></span></span>
                  <? endif; ?>
                </span>
              </td>
              <td></td>
              <td class="right">
                <label>Weaknesses :</label>
                <?
                if (!empty($robot_info['robot_weaknesses'])){
                  $temp_string = array();
                  foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                    if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_weakness.'/" class="robot_weakness robot_type type_'.$robot_weakness.'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</a>'; }
                    else { $temp_string[] = '<span class="robot_weakness robot_type type_'.$robot_weakness.'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</span>'; }
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span class="robot_weakness robot_type type_none">None</span>';
                }
                ?>
              </td>
            </tr>
            <tr>
              <td  class="right">
                <label>Attack :</label>
                <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                  <? if(false && $print_options['layout_style'] == 'website_compact'): ?>
                    <span class="robot_stat type_attack" style="padding-left: <?= round( ( ($robot_info['robot_attack'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_attack'] ?></span></span>
                  <? else: ?>
                    <span class="robot_stat type_attack" style="padding-left: <?= round( ( ($robot_info['robot_attack'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_attack'] ?></span></span>
                  <? endif; ?>
                </span>
              </td>
              <td></td>
              <td class="right">
                <label>Resistances :</label>
                <?
                if (!empty($robot_info['robot_resistances'])){
                  $temp_string = array();
                  foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                    if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_resistance.'/" class="robot_resistance robot_type type_'.$robot_resistance.'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</a>'; }
                    else { $temp_string[] = '<span class="robot_resistance robot_type type_'.$robot_resistance.'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</span>'; }
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span class="robot_resistance robot_type type_none">None</span>';
                }
                ?>
              </td>
            </tr>
            <tr>
              <td  class="right">
                <label>Defense :</label>
                <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                  <? if(false && $print_options['layout_style'] == 'website_compact'): ?>
                    <span class="robot_stat type_defense" style="padding-left: <?= round( ( ($robot_info['robot_defense'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_defense'] ?></span></span>
                  <? else: ?>
                    <span class="robot_stat type_defense" style="padding-left: <?= round( ( ($robot_info['robot_defense'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_defense'] ?></span></span>
                  <? endif; ?>
                </span>
              </td>
              <td></td>
              <td class="right">
                <label>Affinities :</label>
                <?
                if (!empty($robot_info['robot_affinities'])){
                  $temp_string = array();
                  foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                    if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_affinity.'/" class="robot_affinity robot_type type_'.$robot_affinity.'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</a>'; }
                    else { $temp_string[] = '<span class="robot_affinity robot_type type_'.$robot_affinity.'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</span>'; }
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span class="robot_affinity robot_type type_none">None</span>';
                }
                ?>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label>Speed :</label>
                <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                  <? if(false && $print_options['layout_style'] == 'website_compact'): ?>
                    <span class="robot_stat type_speed" style="padding-left: <?= round( ( ($robot_info['robot_speed'] / $robot_info['robot_total']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_speed'] ?></span></span>
                  <? else: ?>
                    <span class="robot_stat type_speed" style="padding-left: <?= round( ( ($robot_info['robot_speed'] / $robot_info['robot_max_stat_value']) * $stat_padding_area ), 4) ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_speed'] ?></span></span>
                  <? endif; ?>
                </span>
              </td>
              <td></td>
              <td class="right">
                <label>Immunities :</label>
                <?
                if (!empty($robot_info['robot_immunities'])){
                  $temp_string = array();
                  foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                    if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_immunity.'/" class="robot_immunity robot_type type_'.$robot_immunity.'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</a>'; }
                    else { $temp_string[] = '<span class="robot_immunity robot_type type_'.$robot_immunity.'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</span>'; }
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span class="robot_immunity robot_type type_none">None</span>';
                }
                ?>
              </td>
            </tr>

            <? if(false && ($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact')): ?>

              <tr>
                <td class="right">
                  <label>Total :</label>
                  <span class="stat" style="width: <?= $stat_container_percent ?>%;">
                    <? if($print_options['layout_style'] == 'website_compact' && $robot_info['robot_total'] < $stat_base_max_value): ?>
                      <span class="robot_stat type_empty">
                        <span class="robot_stat type_none" style="padding-left: <?= round( ( ($robot_info['robot_total'] / $stat_base_max_value) * $stat_padding_area ), 4) ?>%;"><span><?= $robot_info['robot_total'] ?></span></span>
                      </span>
                    <? else: ?>
                      <span class="robot_stat type_none" style="padding-left: <?= $stat_padding_area ?>%;"><span style="display: inline-block; width: 35px;"><?= $robot_info['robot_total'] ?></span></span>
                    <? endif; ?>
                  </span>
                </td>
                <td></td>
                <td class="right"><?/*
                  <label>Immunities :</label>
                  <?
                  if (!empty($robot_info['robot_immunities'])){
                    $temp_string = array();
                    foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                      if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="'.$database_url.'abilities/'.$robot_immunity.'/" class="robot_immunity robot_type type_'.$robot_immunity.'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</a>'; }
                      else { $temp_string[] = '<span class="robot_immunity robot_type type_'.$robot_immunity.'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</span>'; }
                    }
                    echo implode(' ', $temp_string);
                  } else {
                    echo '<span class="robot_immunity robot_type type_none">None</span>';
                  }
                  ?>*/?>
                </td>
              </tr>

            <? endif; ?>

            <? if($print_options['layout_style'] == 'event'): ?>

              <?
              // Define the search and replace arrays for the robot quotes
              $temp_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
              $temp_replace = array('Doctor', $robot_info['robot_name'], 'Doctor', 'Robot');
              ?>
              <tr>
                <td colspan="3" class="center" style="font-size: 13px; padding: 5px 0; ">
                  <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_taunt']) : '&hellip;' ?>&quot;</span>
                </td>
              </tr>

            <? endif; ?>

          </tbody>
        </table>
      </div>

    <? endif; ?>

    <? if($print_options['layout_style'] == 'website'): ?>

      <?
      // Define the various tabs we are able to scroll to
      $section_tabs = array();
      if ($print_options['show_sprites']){ $section_tabs[] = array('sprites', 'Sprites', false); }
      if ($print_options['show_quotes']){ $section_tabs[] = array('quotes', 'Quotes', false); }
      if ($print_options['show_description']){ $section_tabs[] = array('description', 'Description', false); }
      if ($print_options['show_abilities']){ $section_tabs[] = array('abilities', 'Abilities', false); }
      if ($print_options['show_stats']){ $section_tabs[] = array('stats', 'Stats', false); }
      if ($print_options['show_records']){ $section_tabs[] = array('records', 'Records', false); }
      // Automatically mark the first element as true or active
      $section_tabs[0][2] = true;
      // Define the current URL for this robot or mecha page
      $temp_url = 'database/';
      if ($robot_info['robot_class'] == 'mecha'){ $temp_url .= 'mechas/'; }
      elseif ($robot_info['robot_class'] == 'master'){ $temp_url .= 'robots/'; }
      elseif ($robot_info['robot_class'] == 'boss'){ $temp_url .= 'bosses/'; }
      $temp_url .= $robot_info['robot_token'].'/';
      ?>
      <div class="section_tabs">
        <? foreach($section_tabs AS $tab){
          echo '<a class="link_inline link_'.$tab[0].' '.($tab[2] ? 'active' : '').'" href="'.$temp_url.'#'.$tab[0].'" data-tab="'.$tab[0].'"><span class="wrap">'.$tab[1].'</span></a>';
          } ?>
      </div>

    <? endif; ?>

    <? if($print_options['show_sprites']): ?>

      <?

      // Start the output buffer and prepare to collect sprites
      ob_start();

      // Define the alts we'll be looping through for this robot
      $temp_alts_array = array();
      $temp_alts_array[] = array('token' => '', 'name' => $robot_info['robot_name'], 'summons' => 0);
      // Append predefined alts automatically, based on the robot image alt array
      if (!empty($robot_info['robot_image_alts'])){
        $temp_alts_array = array_merge($temp_alts_array, $robot_info['robot_image_alts']);
      }
      // Otherwise, if this is a copy robot, append based on all the types in the index
      elseif ($robot_info['robot_core'] == 'copy' && preg_match('/^(mega-man|proto-man|bass)$/i', $robot_info['robot_token'])){
        foreach ($mmrpg_database_types AS $type_token => $type_info){
          if (empty($type_token) || $type_token == 'none' || $type_token == 'copy'){ continue; }
          $temp_alts_array[] = array('token' => $type_token, 'name' => $robot_info['robot_name'].' ('.ucfirst($type_token).' Core)', 'summons' => 0);
        }
      }
      // Otherwise, if this robot has multiple sheets, add them as alt options
      elseif (!empty($robot_info['robot_image_sheets'])){
        for ($i = 2; $i <= $robot_info['robot_image_sheets']; $i++){
          $temp_alts_array[] = array('sheet' => $i, 'name' => $robot_info['robot_name'].' (Sheet #'.$i.')', 'summons' => 0);
        }
      }

      // Loop through the alts and display images for them (yay!)
      foreach ($temp_alts_array AS $alt_key => $alt_info){

        // Define the current image token with alt in mind
        $temp_robot_image_token = $robot_image_token;
        $temp_robot_image_token .= !empty($alt_info['token']) ? '_'.$alt_info['token'] : '';
        $temp_robot_image_token .= !empty($alt_info['sheet']) ? '-'.$alt_info['sheet'] : '';
        $temp_robot_image_name = $alt_info['name'];
        // Update the alt array with this info
        $temp_alts_array[$alt_key]['image'] = $temp_robot_image_token;

        // Collect the number of sheets
        $temp_sheet_number = !empty($robot_info['robot_image_sheets']) ? $robot_info['robot_image_sheets'] : 1;

        // Loop through the different frames and print out the sprite sheets
        foreach (array('right', 'left') AS $temp_direction){
          $temp_direction2 = substr($temp_direction, 0, 1);
          $temp_embed = '[robot:'.$temp_direction.']{'.$temp_robot_image_token.'}';
          $temp_title = $temp_robot_image_name.' | Mugshot Sprite '.ucfirst($temp_direction);
          $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
          $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
          $temp_label = 'Mugshot '.ucfirst(substr($temp_direction, 0, 1));
          echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_robot_image_token.'" data-frame="mugshot" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$robot_sprite_size.'px; height: '.$robot_sprite_size.'px; overflow: hidden;">';
            echo '<img style="margin-left: 0;" data-tooltip="'.$temp_title.'" src="i/r/'.$temp_robot_image_token.'/m'.$temp_direction2.$robot_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
            echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
          echo '</div>';
        }


        // Loop through the different frames and print out the sprite sheets
        foreach ($robot_sprite_frames AS $this_key => $this_frame){
          $margin_left = ceil((0 - $this_key) * $robot_sprite_size);
          $frame_relative = $this_frame;
          //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($robot_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
          $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
          foreach (array('right', 'left') AS $temp_direction){
            $temp_direction2 = substr($temp_direction, 0, 1);
            $temp_embed = '[robot:'.$temp_direction.':'.$frame_relative.']{'.$temp_robot_image_token.'}';
            $temp_title = $temp_robot_image_name.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
            $temp_title .= '<div style="margin-top: 4px; letting-spacing: 1px; font-size: 90%; font-family: Courier New; color: rgb(159, 150, 172);">'.$temp_embed.'</div>';
            $temp_title = htmlentities($temp_title, ENT_QUOTES, 'UTF-8', true);
            $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
            //$image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
            //if ($temp_sheet > 1){ $temp_robot_image_token .= '-'.$temp_sheet; }
            echo '<div class="frame_container" data-clickcopy="'.$temp_embed.'" data-direction="'.$temp_direction.'" data-image="'.$temp_robot_image_token.'" data-frame="'.$frame_relative.'" style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$robot_sprite_size.'px; height: '.$robot_sprite_size.'px; overflow: hidden;">';
              echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="i/r/'.$temp_robot_image_token.'/s'.$temp_direction2.$robot_sprite_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
              echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
            echo '</div>';
          }
        }

      }

      // Collect the sprite markup from the output buffer for later
      $this_sprite_markup = ob_get_clean();

      ?>

      <h2 id="sprites" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left; overflow: hidden; height: auto;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Sprites
        <span class="header_links image_link_container">
          <span class="images" style="<?= count($temp_alts_array) == 1 ? 'visibility: hidden;' : '' ?>"><?
            // Loop though and print links for the alts
            $alt_type_base = 'robot_type type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').' ';
            foreach ($temp_alts_array AS $alt_key => $alt_info){
              $alt_type = '';
              $alt_style = '';
              $alt_title = $alt_info['name'];
              $alt_type2 = $alt_type_base;
              if (preg_match('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', $alt_info['name'])){
                $alt_type = strtolower(preg_replace('/^(?:[-_a-z0-9\s]+)\s\(([a-z0-9]+)\sCore\)$/i', '$1', $alt_info['name']));
                $alt_name = '&bull;'; //ucfirst($alt_type); //substr(ucfirst($alt_type), 0, 2);
                $alt_type = 'robot_type type_'.$alt_type.' core_type ';
                $alt_type2 = 'robot_type type_'.$alt_type.' ';
                $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; ';
              }
              else {
                $alt_name = $alt_key == 0 ? $robot_info['robot_name'] : 'Alt'.($alt_key > 1 ? ' '.$alt_key : ''); //$alt_key == 0 ? $robot_info['robot_name'] : $robot_info['robot_name'].' Alt'.($alt_key > 1 ? ' '.$alt_key : '');
                $alt_type = 'robot_type type_empty ';
                $alt_style = 'border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ';
                //if ($robot_info['robot_core'] == 'copy' && $alt_key == 0){ $alt_type = 'robot_type type_empty '; }
              }

              echo '<a href="#" data-tooltip="'.$alt_title.'" data-tooltip-type="'.$alt_type2.'" class="link link_image '.($alt_key == 0 ? 'link_active ' : '').'" data-image="'.$alt_info['image'].'">';
              echo '<span class="'.$alt_type.'" style="'.$alt_style.'">'.$alt_name.'</span>';
              echo '</a>';
            }
            ?></span>
          <span class="pipe" style="<?= count($temp_alts_array) == 1 ? 'visibility: hidden;' : '' ?>">|</span>
          <span class="directions"><?
            // Loop though and print links for the alts
            foreach (array('right', 'left') AS $temp_key => $temp_direction){
              echo '<a href="#" data-tooltip="'.ucfirst($temp_direction).' Facing Sprites" data-tooltip-type="'.$alt_type_base.'" class="link link_direction '.($temp_key == 0 ? 'link_active' : '').'" data-direction="'.$temp_direction.'">';
              echo '<span class="ability_type ability_type_empty" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important; ">'.ucfirst($temp_direction).'</span>';
              echo '</a>';
            }
            ?></span>
        </span>
      </h2>
      <div id="sprites_body" class="body body_full" style="margin: 0; padding: 10px; min-height: 10px;">
        <div style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #4d4d4d url(images/sprite-grid.gif) scroll repeat -10px -30px; overflow: hidden; padding: 10px 30px;">
          <?= $this_sprite_markup ?>
        </div>
        <?
        // Define the editor title based on ID
        $temp_editor_title = 'Undefined';
        $temp_final_divider = '<span style="color: #565656;"> | </span>';
        if (!empty($robot_info['robot_image_editor'])){
          $temp_break = false;
          if ($robot_info['robot_image_editor'] == 412){ $temp_editor_title = 'Adrian Marceau / Ageman20XX'; }
          elseif ($robot_info['robot_image_editor'] == 110){ $temp_break = true; $temp_editor_title = 'MetalMarioX100 / EliteP1</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
          elseif ($robot_info['robot_image_editor'] == 18){ $temp_break = true; $temp_editor_title = 'Sean Adamson / MetalMan</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
          elseif ($robot_info['robot_image_editor'] == 4117){ $temp_break = true; $temp_editor_title = 'Jonathan Backstrom / Rhythm_BCA</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
          elseif ($robot_info['robot_image_editor'] == 3842){ $temp_break = true; $temp_editor_title = 'Miki Bossman / MegaBossMan</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
          elseif ($robot_info['robot_image_editor'] == 5161){ $temp_break = true; $temp_editor_title = 'The Zion / maistir1234</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
          if ($temp_break){ $temp_final_divider = '<br />'; }
        }
        $temp_is_capcom = true;
        $temp_is_original = array('disco', 'rhythm', 'flutter-fly', 'flutter-fly-2', 'flutter-fly-3');
        if (in_array($robot_info['robot_token'], $temp_is_original)){ $temp_is_capcom = false; }
        if ($temp_is_capcom){
          echo '<p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 13px; margin-top: 6px;">Sprite Editing by <strong>'.$temp_editor_title.'</strong> '.$temp_final_divider.' Original Artwork by <strong>Capcom</strong></p>'."\n";
        } else {
          echo '<p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 13px; margin-top: 6px;">Sprite Editing by <strong>'.$temp_editor_title.'</strong> '.$temp_final_divider.' Original Character by <strong>Adrian Marceau</strong></p>'."\n";
        }
        ?>
      </div>

      <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
        <div class="link_wrapper">
          <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
          <a class="link link_permalink" href="<?= $database_category_robot_url ?>#sprites" rel="permalink">+ Permalink</a>
        </div>
      <? endif; ?>

    <? endif; ?>

    <? if($print_options['show_quotes']): ?>

      <h2 id="quotes" class="header header_left <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Quotes
      </h2>
      <div class="body body_left" style="margin-right: 0; margin-left: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
        <?
        // Define the search and replace arrays for the robot quotes
        $temp_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
        $temp_replace = array('Doctor', $robot_info['robot_name'], 'Doctor', 'Robot');
        ?>
        <table class="full" style="margin: 5px auto 10px;">
          <colgroup>
            <col width="100%" />
          </colgroup>
          <tbody>
            <tr>
              <td class="right">
                <label>Start Quote : </label>
                <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_start']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_start']) : '&hellip;' ?>&quot;</span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label>Taunt Quote : </label>
                <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_taunt']) : '&hellip;' ?>&quot;</span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label>Victory Quote : </label>
                <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_victory']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_victory']) : '&hellip;' ?>&quot;</span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label>Defeat Quote : </label>
                <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_defeat']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_defeat']) : '&hellip;' ?>&quot;</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
        <div class="link_wrapper">
          <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
          <a class="link link_permalink" href="<?= $database_category_robot_url ?>#quotes" rel="permalink">+ Permalink</a>
        </div>
      <? endif; ?>

    <? endif; ?>

    <? if($print_options['show_description'] && !empty($robot_info['robot_description2'])): ?>

      <h2 id="description" class="header header_left <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left; ">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Description
      </h2>
      <div class="body body_left" style="margin-right: 0; margin-left: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
        <table class="full" style="margin: 5px auto 10px;">
          <colgroup>
            <col width="100%" />
          </colgroup>
          <tbody>
            <tr>
              <td class="right">
                <div class="robot_description" style="text-align: left; padding: 0 4px;"><?= $robot_info['robot_description2'] ?></div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
        <div class="link_wrapper">
          <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
          <a class="link link_permalink" href="<?= $database_category_robot_url ?>#description" rel="permalink">+ Permalink</a>
        </div>
      <? endif; ?>

    <? endif; ?>

    <? if($print_options['show_abilities']): ?>

      <h2 id="abilities" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Abilities
      </h2>
      <div class="body body_full" style="margin: 0; padding: 2px 3px; min-height: 10px;">
        <table class="full" style="margin: 5px auto 10px;">
          <colgroup>
            <col width="100%" />
          </colgroup>
          <tbody>
            <tr>
              <td class="right">
                <div class="ability_container">
                <?
                $robot_ability_class = !empty($robot_info['robot_class']) ? $robot_info['robot_class'] : 'master';
                $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
                $robot_ability_core2 = !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : false;
                $robot_ability_list = !empty($robot_info['robot_abilities']) ? $robot_info['robot_abilities'] : array();
                $robot_ability_rewards = !empty($robot_info['robot_rewards']['abilities']) ? $robot_info['robot_rewards']['abilities'] : array();
                $new_ability_rewards = array();
                foreach ($robot_ability_rewards AS $this_info){
                  $new_ability_rewards[$this_info['token']] = $this_info;
                }
                $robot_copy_program = $robot_ability_core == 'copy' || $robot_ability_core2 == 'copy' ? true : false;
                //if ($robot_copy_program){ $robot_ability_list = $temp_all_ability_tokens; }
                $robot_ability_core_list = array();
                if ((!empty($robot_ability_core) || !empty($robot_ability_core2))
                  && $robot_ability_class != 'mecha'){ // only robot masters can core match abilities
                  foreach ($mmrpg_database_abilities AS $token => $info){
                    if (
                      (!empty($info['ability_type']) && ($robot_copy_program || $info['ability_type'] == $robot_ability_core || $info['ability_type'] == $robot_ability_core2)) ||
                      (!empty($info['ability_type2']) && ($info['ability_type2'] == $robot_ability_core || $info['ability_type2'] == $robot_ability_core2))
                      ){
                      $robot_ability_list[] = $info['ability_token'];
                      $robot_ability_core_list[] = $info['ability_token'];
                    }
                  }
                }
                foreach ($robot_ability_list AS $this_token){
                  if ($this_token == '*'){ continue; }
                  if (!isset($new_ability_rewards[$this_token])){
                    if (in_array($this_token, $robot_ability_core_list)){ $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }
                    else { $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }

                  }
                }
                $robot_ability_rewards = $new_ability_rewards;

                //die('<pre>'.print_r($robot_ability_rewards, true).'</pre>');

                if (!empty($robot_ability_rewards)){
                  $temp_string = array();
                  $ability_key = 0;
                  $ability_method_key = 0;
                  $ability_method = '';
                  foreach ($robot_ability_rewards AS $this_info){
                    $this_level = $this_info['level'];
                    $this_ability = $mmrpg_database_abilities[$this_info['token']];
                    $this_ability_token = $this_ability['ability_token'];
                    $this_ability_name = $this_ability['ability_name'];
                    $this_ability_class = !empty($this_ability['ability_class']) ? $this_ability['ability_class'] : 'master';
                    $this_ability_image = !empty($this_ability['ability_image']) ? $this_ability['ability_image']: $this_ability['ability_token'];
                    $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                    $this_ability_type2 = !empty($this_ability['ability_type2']) ? $this_ability['ability_type2'] : false;
                    if (!empty($this_ability_type) && !empty($mmrpg_index['types'][$this_ability_type])){ $this_ability_type = $mmrpg_index['types'][$this_ability_type]['type_name'].' Type'; }
                    else { $this_ability_type = ''; }
                    if (!empty($this_ability_type2) && !empty($mmrpg_index['types'][$this_ability_type2])){ $this_ability_type = str_replace('Type', '/ '.$mmrpg_index['types'][$this_ability_type2]['type_name'], $this_ability_type); }
                    $this_ability_damage = !empty($this_ability['ability_damage']) ? $this_ability['ability_damage'] : 0;
                    $this_ability_damage2 = !empty($this_ability['ability_damage2']) ? $this_ability['ability_damage2'] : 0;
                    $this_ability_damage_percent = !empty($this_ability['ability_damage_percent']) ? true : false;
                    $this_ability_damage2_percent = !empty($this_ability['ability_damage2_percent']) ? true : false;
                    if ($this_ability_damage_percent && $this_ability_damage > 100){ $this_ability_damage = 100; }
                    if ($this_ability_damage2_percent && $this_ability_damage2 > 100){ $this_ability_damage2 = 100; }
                    $this_ability_recovery = !empty($this_ability['ability_recovery']) ? $this_ability['ability_recovery'] : 0;
                    $this_ability_recovery2 = !empty($this_ability['ability_recovery2']) ? $this_ability['ability_recovery2'] : 0;
                    $this_ability_recovery_percent = !empty($this_ability['ability_recovery_percent']) ? true : false;
                    $this_ability_recovery2_percent = !empty($this_ability['ability_recovery2_percent']) ? true : false;
                    if ($this_ability_recovery_percent && $this_ability_recovery > 100){ $this_ability_recovery = 100; }
                    if ($this_ability_recovery2_percent && $this_ability_recovery2 > 100){ $this_ability_recovery2 = 100; }
                    $this_ability_accuracy = !empty($this_ability['ability_accuracy']) ? $this_ability['ability_accuracy'] : 0;
                    $this_ability_description = !empty($this_ability['ability_description']) ? $this_ability['ability_description'] : '';
                    $this_ability_description = str_replace('{DAMAGE}', $this_ability_damage, $this_ability_description);
                    $this_ability_description = str_replace('{RECOVERY}', $this_ability_recovery, $this_ability_description);
                    $this_ability_description = str_replace('{DAMAGE2}', $this_ability_damage2, $this_ability_description);
                    $this_ability_description = str_replace('{RECOVERY2}', $this_ability_recovery2, $this_ability_description);
                    //$this_ability_title_plain = $this_ability_name;
                    //if (!empty($this_ability_type)){ $this_ability_title_plain .= ' | '.$this_ability_type; }
                    //if (!empty($this_ability_damage)){ $this_ability_title_plain .= ' | '.$this_ability_damage.' Damage'; }
                    //if (!empty($this_ability_recovery)){ $this_ability_title_plain .= ' | '.$this_ability_recovery.' Recovery'; }
                    //if (!empty($this_ability_accuracy)){ $this_ability_title_plain .= ' | '.$this_ability_accuracy.'% Accuracy'; }
                    //if (!empty($this_ability_description)){ $this_ability_title_plain .= ' | '.$this_ability_description; }
                    $this_ability_title_plain = mmrpg_ability::print_editor_title_markup($robot_info, $this_ability);
                    $this_ability_method = 'level';
                    $this_ability_method_text = 'Level Up';
                    $this_ability_title_html = '<strong class="name">'.$this_ability_name.'</strong>';
                    if (is_numeric($this_level)){
                      if ($this_level > 1){ $this_ability_title_html .= '<span class="level">Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).'</span>'; }
                      else { $this_ability_title_html .= '<span class="level">Start</span>'; }
                    } else {
                      $this_ability_method = 'player';
                      $this_ability_method_text = 'Player Only';
                      if (!in_array($this_ability_token, $robot_info['robot_abilities'])){
                        $this_ability_method = 'core';
                        $this_ability_method_text = 'Core Match';
                      }
                      $this_ability_title_html .= '<span class="level">&nbsp;</span>';
                    }

                    // If this is a boss, don't bother showing player or core match abilities
                    if ($this_ability_method != 'level' && $robot_info['robot_class'] == 'boss'){ continue; }

                    if (!empty($this_ability_type)){ $this_ability_title_html .= '<span class="type">'.$this_ability_type.'</span>'; }
                    if (!empty($this_ability_damage)){ $this_ability_title_html .= '<span class="damage">'.$this_ability_damage.(!empty($this_ability_damage_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'D' : 'Damage').'</span>'; }
                    if (!empty($this_ability_recovery)){ $this_ability_title_html .= '<span class="recovery">'.$this_ability_recovery.(!empty($this_ability_recovery_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'R' : 'Recovery').'</span>'; }
                    if (!empty($this_ability_accuracy)){ $this_ability_title_html .= '<span class="accuracy">'.$this_ability_accuracy.'% Accuracy</span>'; }
                    $this_ability_sprite_path = 'images/abilities/'.$this_ability_image.'/icon_left_40x40.png';
                    if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_ability_sprite_path)){ $this_ability_image = 'ability'; $this_ability_sprite_path = 'i/a/ability/il40.png'; }
                    else { $this_ability_sprite_path = 'i/a/'.$this_ability_image.'/il40.png'; }
                    $this_ability_sprite_html = '<span class="icon"><img src="'.$this_ability_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE.'" alt="'.$this_ability_name.' Icon" /></span>';
                    $this_ability_title_html = '<span class="label">'.$this_ability_title_html.'</span>';
                    //$this_ability_title_html = (is_numeric($this_level) && $this_level > 1 ? 'Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).' : ' : $this_level.' : ').$this_ability_title_html;

                    // Show the ability method separator if necessary
                    if ($ability_method != $this_ability_method && $robot_info['robot_class'] == 'master'){
                      $temp_separator = '<div class="ability_separator">'.$this_ability_method_text.'</div>';
                      $temp_string[] = $temp_separator;
                      $ability_method = $this_ability_method;
                      $ability_method_key++;
                      // Print out the disclaimer if a copy-core robot
                      if ($this_ability_method != 'level' && $robot_copy_program){
                        $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">Copy Core robots can equip <em>any</em> '.($this_ability_method == 'player' ? 'player' : 'type').' ability!</div>';
                      }
                    }
                    // If this is a copy core robot, don't bother showing EVERY core-match ability
                    if ($this_ability_method != 'level' && $robot_copy_program){ continue; }
                    // Only show if this ability is greater than level 0 OR it's not copy core (?)
                    elseif ($this_level >= 0 || !$robot_copy_program){
                      $temp_element = $this_ability_class != 'mecha' ? 'a' : 'span';
                      $temp_markup = '<'.$temp_element.' '.($this_ability_class != 'mecha' ? 'href="'.MMRPG_CONFIG_ROOTURL.'database/abilities/'.$this_ability['ability_token'].'/"' : '').' class="ability_name ability_class_'.$this_ability_class.' ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').(!empty($this_ability['ability_type2']) ? '_'.$this_ability['ability_type2'] : '').'" title="'.$this_ability_title_plain.'" style="'.($this_ability_image == 'ability' ? 'opacity: 0.3; ' : '').'">';
                      $temp_markup .= '<span class="chrome">'.$this_ability_sprite_html.$this_ability_title_html.'</span>';
                      $temp_markup .= '</'.$temp_element.'>';
                      $temp_string[] = $temp_markup;
                      $ability_key++;
                      continue;
                    }
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span class="robot_ability type_none"><span class="chrome">None</span></span>';
                }
                ?>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
        <div class="link_wrapper">
          <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
          <a class="link link_permalink" href="<?= $database_category_robot_url ?>#abilities" rel="permalink">+ Permalink</a>
        </div>
      <? endif; ?>

    <? endif; ?>

    <? if($print_options['show_stats']): ?>

      <h2 id="stats" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Stats
      </h2>
      <div class="body body_full" style="margin: 0 auto 5px; padding: 2px 0; min-height: 10px;">
        <?
        // Define the various levels we'll display in this chart
        $display_levels = array(1, 5, 10, 50, 100); //range(1, 100, 1);
        ?>
        <table class="full stat_container" style="">
          <colgroup>
            <col width="20%" />
            <col width="10%" />
            <col width="10%" />
            <col width="10%" />
            <col width="10%" />
            <col width="10%" />
            <col width="10%" />
            <col width="10%" />
            <col width="10%" />
          </colgroup>
          <thead>
            <tr>
              <th class="top left level">Level</th>
              <th class="top center energy" colspan="1">Energy</th>
              <th class="top center weapons" colspan="1">Weapons</th>
              <th class="top center attack" colspan="2">Attack</th>
              <th class="top center defense" colspan="2">Defense</th>
              <th class="top center speed" colspan="2">Speed</th>
            </tr>
            <tr>
              <th class="sub left level" >&nbsp;</th>
              <th class="sub center energy max">-</th>
              <th class="sub center weapons max">-</th>
              <th class="sub center attack min">Min</th>
              <th class="sub center attack max">Max</th>
              <th class="sub center defense min">Min</th>
              <th class="sub center defense max">Max</th>
              <th class="sub center speed min">Min</th>
              <th class="sub center speed max">Max</th>
            </tr>
          </thead>
          <tbody>
            <?
            // Define or collect the base stats for this robot, ready to be modified
            $base_stats = array();
            $base_stats['energy'] = $robot_info['robot_energy'];
            $base_stats['weapons'] = $robot_info['robot_weapons'];
            $base_stats['attack'] = $robot_info['robot_attack'];
            $base_stats['defense'] = $robot_info['robot_defense'];
            $base_stats['speed'] = $robot_info['robot_speed'];
            // Loop through the display levels and calculate stat adjustments
            foreach ($display_levels AS $level){
              // Calculate the minimum stat values for this robot with only level-based stat boosts
              $min_stats = array();
              $min_stats['energy'] = MMRPG_SETTINGS_STATS_GET_ROBOTMIN($base_stats['energy'], $level);
              $min_stats['attack'] = MMRPG_SETTINGS_STATS_GET_ROBOTMIN($base_stats['attack'], $level);
              $min_stats['defense'] = MMRPG_SETTINGS_STATS_GET_ROBOTMIN($base_stats['defense'], $level);
              $min_stats['speed'] = MMRPG_SETTINGS_STATS_GET_ROBOTMIN($base_stats['speed'], $level);
              // Calculate the maximum stat values for this robot considering both level and overkill-based stat boosts
              $max_stats = array();
              //$max_stats['energy'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($base_stats['energy'], $level);
              $max_stats['attack'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($base_stats['attack'], $level);
              $max_stats['defense'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($base_stats['defense'], $level);
              $max_stats['speed'] = MMRPG_SETTINGS_STATS_GET_ROBOTMAX($base_stats['speed'], $level);
              ?>
              <tr>
                <td class="left level">Lv <?= $level ?></td>
                <td class="center energy max"><?= number_format($min_stats['energy'], 0, '.', ',') ?></td>
                <td class="center weapons max"><?= number_format($base_stats['weapons'], 0, '.', ',') ?></td>
                <td class="center attack min"><?= number_format($min_stats['attack'], 0, '.', ',') ?></td>
                <td class="center attack max"><?= number_format($max_stats['attack'], 0, '.', ',') ?></td>
                <td class="center defense min"><?= number_format($min_stats['defense'], 0, '.', ',') ?></td>
                <td class="center defense max"><?= number_format($max_stats['defense'], 0, '.', ',') ?></td>
                <td class="center speed min"><?= number_format($min_stats['speed'], 0, '.', ',') ?></td>
                <td class="center speed max"><?= number_format($max_stats['speed'], 0, '.', ',') ?></td>
              </tr>
              <?
            }
            ?>
            <tr>
              <td class="left help" colspan="9">
                <? if ($robot_info['robot_class'] == 'master'): ?>
                  * Min stats represent a robot's base values without any knockout bonuses applied.<br />
                  ** Max stats represent a robot's potential values with maximum knockout bonuses applied.
                <? elseif ($robot_info['robot_class'] == 'mecha'): ?>
                  * Min stats represent a mecha's base values without any difficulty mods applied.<br />
                  ** Max stats represent a mecha's potential values with maximum difficulty mods applied.
                <? elseif ($robot_info['robot_class'] == 'boss'): ?>
                  * Min stats represent a boss's base values without any difficulty mods applied.<br />
                  ** Max stats represent a boss's potential values with maximum difficulty mods applied.
                <? endif; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
        <div class="link_wrapper">
          <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
          <a class="link link_permalink" href="<?= $database_category_robot_url ?>#stats" rel="permalink">+ Permalink</a>
        </div>
      <? endif; ?>

    <? endif; ?>

    <? if($print_options['show_records']): ?>

      <h2 id="records" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Records
      </h2>
      <div class="body body_full" style="margin: 0 auto 5px; padding: 2px 0; min-height: 10px;">
        <table class="full" style="margin: 5px auto 10px;">
          <colgroup>
            <col width="100%" />
          </colgroup>
          <tbody>
            <? if($robot_info['robot_class'] == 'master'): ?>
              <tr>
                <td class="right">
                  <label>Unlocked By : </label>
                  <span class="robot_quote"><?= $temp_robot_records['robot_unlocked'] == 1 ? '1 Player' : number_format($temp_robot_records['robot_unlocked'], 0, '.', ',').' Players' ?></span>
                </td>
              </tr>
            <? endif; ?>
            <tr>
              <td class="right">
                <label>Encountered : </label>
                <span class="robot_quote"><?= $temp_robot_records['robot_encountered'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_encountered'], 0, '.', ',').' Times' ?></span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label>Summoned : </label>
                <span class="robot_quote"><?= $temp_robot_records['robot_summoned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_summoned'], 0, '.', ',').' Times' ?></span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label>Defeated : </label>
                <span class="robot_quote"><?= $temp_robot_records['robot_defeated'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_defeated'], 0, '.', ',').' Times' ?></span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label>Scanned : </label>
                <span class="robot_quote"><?= $temp_robot_records['robot_scanned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_scanned'], 0, '.', ',').' Times' ?></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>
        <div class="link_wrapper">
          <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
          <a class="link link_permalink" href="<?= $database_category_robot_url ?>#records" rel="permalink">+ Permalink</a>
        </div>
      <? endif; ?>

    <? endif; ?>

    <? if(false && $print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

      <div class="link_wrapper">
        <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
        <a class="link link_permalink" href="<?= $database_category_robot_url ?>" rel="permalink">+ Permalink</a>
      </div>

    <? elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>


      <div class="link_wrapper"><a class="link link_top" data-href="#top" rel="nofollow">^ Top</a></div>
      <span class="link_container"><?= !empty($compact_footer_link_markup) ? implode("\n", $compact_footer_link_markup) : ''  ?></span>
      <?= false ? '<pre>$compact_footer_link_markup = '.print_r($compact_footer_link_markup, true).'</pre>' : '' ?>

    <? endif; ?>
  </div>
</div>
<?
// Collect the outbut buffer contents
$this_markup = trim(ob_get_clean());
?>