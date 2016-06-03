<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url, $DB;
global $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_abilities, $mmrpg_database_types;
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
}

// Collect the robot sprite dimensions
$robot_image_size = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
$robot_image_size_text = $robot_image_size.'x'.$robot_image_size;
$robot_image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];

// Collect the robot's type for background display
$robot_header_types = 'robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none').' ';

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

// Start the output buffer
ob_start();
?>
<div class="database_container database_<?= $robot_info['robot_class'] == 'mecha' ? 'mecha' : 'robot' ?>_container" data-token="<?=$robot_info['robot_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

  <? if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
    <a class="anchor" id="<?=$robot_info['robot_token']?>">&nbsp;</a>
  <? endif; ?>

  <div class="subbody event event_triple event_visible" data-token="<?=$robot_info['robot_token']?>" style="<?= ($print_options['layout_style'] == 'event' ? 'margin: 0 !important; ' : '').($print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important; ' : '') ?>">

    <? if($print_options['show_mugshot']): ?>

      <div class="this_sprite sprite_left" style="height: 40px;">
        <? if($print_options['show_mugshot']): ?>
          <? if($print_options['show_key'] !== false): ?>
            <div class="mugshot robot_type <?= $robot_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
          <? endif; ?>
          <? if ($robot_image_token != 'robot'){ ?>
            <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: url(images/robots/<?= $robot_image_token ?>/mug_right_<?= $robot_image_size_text ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active"><?=$robot_info['robot_name']?>'s Mugshot</div></div>
          <? } else { ?>
            <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active">No Image</div></div>
          <? } ?>
        <? endif; ?>
      </div>

    <? endif; ?>

    <? if($print_options['show_basics']): ?>

      <h2 class="header header_left <?= $robot_header_types ?>" style="margin-right: 0; <?= (!$print_options['show_mugshot']) ? 'margin-left: 0;' : '' ?>">
        <? if($print_options['layout_style'] == 'website_compact'): ?>
          <a href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_token'] ?>/"><?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?></a>
        <? else: ?>
          <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Data
        <? endif; ?>
        <div class="header_core robot_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></div>
      </h2>
      <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px; <?= (!$print_options['show_mugshot']) ? 'margin-left: 0; ' : '' ?><?= $print_options['layout_style'] == 'event' ? 'font-size: 10px; min-height: 150px; ' : '' ?>">
        <table class="full" style="<?= $print_options['layout_style'] == 'website' ? 'margin: 5px auto 10px;' : 'margin: 5px auto -2px;' ?>">
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
                  <label style="display: block; float: left;">Name :</label>
                  <span class="robot_type" style="width: auto;"><?=$robot_info['robot_name']?></span>
                  <? if (!empty($robot_info['robot_generation'])){ ?><span class="robot_type" style="width: auto;"><?=$robot_info['robot_generation']?> Gen</span><? } ?>
                </td>
                <td class="center">&nbsp;</td>
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
                  <label style="display: block; float: left;">Source :</label>
                  <span class="robot_type"><?= $temp_source_string ?></span>
                </td>
              </tr>
            <? endif; ?>
            <tr>
              <td  class="right">
                <label style="display: block; float: left;">Model :</label>
                <span class="robot_type"><?=$robot_info['robot_number']?></span>
              </td>
              <td class="center">&nbsp;</td>
              <td  class="right">
                <label style="display: block; float: left;">Class :</label>
                <span class="robot_type"><?= !empty($robot_info['robot_description']) ? $robot_info['robot_description'] : '&hellip;' ?></span>
              </td>
            </tr>
            <tr>
              <td  class="right">
                <label style="display: block; float: left;">Type :</label>
                <? if($print_options['layout_style'] != 'event'): ?>
                  <? if(!empty($robot_info['robot_core2'])): ?>
                    <span class="robot_type robot_type_<?= $robot_info['robot_core'].'_'.$robot_info['robot_core2'] ?>">
                      <a href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_core'] ?>/"><?= ucfirst($robot_info['robot_core']) ?></a> /
                      <a href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_core2'] ?>/"><?= ucfirst($robot_info['robot_core2']) ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                    </span>
                  <? else: ?>
                    <a href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/" class="robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                  <? endif; ?>
                <? else: ?>
                  <span class="robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></span>
                <? endif; ?>
              </td>
              <td class="center">&nbsp;</td>
              <td  class="right">
                <label style="display: block; float: left;"><?= empty($field_info_array) || count($field_info_array) == 1 ? 'Field' : 'Fields' ?> :</label>
                <?
                /*

                <? if($print_options['layout_style'] != 'event'): ?>

                <? else: ?>

                <? endif; ?>


                 */

                // Loop through the robots fields if available
                if (!empty($field_info_array)){
                  foreach ($field_info_array AS $key => $field_info){
                    ?>
                      <? if($print_options['layout_style'] != 'event'): ?>
                        <a href="database/fields/<?= $field_info['field_token'] ?>/" class="field_type field_type_<?= (!empty($field_info['field_type']) ? $field_info['field_type'] : 'none').(!empty($field_info['field_type2']) ? '_'.$field_info['field_type2'] : '') ?>" <?= $key > 0 ? 'title="'.$field_info['field_name'].'"' : '' ?>><?= $key == 0 ? $field_info['field_name'] : preg_replace('/^([a-z0-9]+)\s([a-z0-9]+)$/i', '$1&hellip;', $field_info['field_name']) ?></a>
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
                <label style="display: block; float: left;">Energy :</label>
                <span class="robot_stat robot_type robot_type_energy" style="padding-left: <?= ceil($robot_info['robot_energy'] * ($print_options['layout_style'] == 'website' ? 1 : 0.9)) ?>px;"><?= $robot_info['robot_energy'] ?></span>
              </td>
              <td class="center">&nbsp;</td>
              <td class="right">
                <label style="display: block; float: left;">Weaknesses :</label>
                <?
                if (!empty($robot_info['robot_weaknesses'])){
                  $temp_string = array();
                  foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                    if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="database/abilities/'.$robot_weakness.'/" class="robot_weakness robot_type robot_type_'.$robot_weakness.'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</a>'; }
                    else { $temp_string[] = '<span class="robot_weakness robot_type robot_type_'.$robot_weakness.'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</span>'; }
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span class="robot_weakness robot_type robot_type_none">None</span>';
                }
                ?>
              </td>
            </tr>
            <tr>
              <td  class="right">
                <label style="display: block; float: left;">Attack :</label>
                <span class="robot_stat robot_type robot_type_attack" style="padding-left: <?= ceil($robot_info['robot_attack'] * ($print_options['layout_style'] == 'website' ? 1 : 0.9)) ?>px;"><?= $robot_info['robot_attack'] ?></span>
              </td>
              <td class="center">&nbsp;</td>
              <td class="right">
                <label style="display: block; float: left;">Resistances :</label>
                <?
                if (!empty($robot_info['robot_resistances'])){
                  $temp_string = array();
                  foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                    if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="database/abilities/'.$robot_resistance.'/" class="robot_resistance robot_type robot_type_'.$robot_resistance.'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</a>'; }
                    else { $temp_string[] = '<span class="robot_resistance robot_type robot_type_'.$robot_resistance.'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</span>'; }
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span class="robot_resistance robot_type robot_type_none">None</span>';
                }
                ?>
              </td>
            </tr>
            <tr>
              <td  class="right">
                <label style="display: block; float: left;">Defense :</label>
                <span class="robot_stat robot_type robot_type_defense" style="padding-left: <?= ceil($robot_info['robot_defense'] * ($print_options['layout_style'] == 'website' ? 1 : 0.9)) ?>px;"><?= $robot_info['robot_defense'] ?></span>
              </td>
              <td class="center">&nbsp;</td>
              <td class="right">
                <label style="display: block; float: left;">Affinities :</label>
                <?
                if (!empty($robot_info['robot_affinities'])){
                  $temp_string = array();
                  foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                    if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="database/abilities/'.$robot_affinity.'/" class="robot_affinity robot_type robot_type_'.$robot_affinity.'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</a>'; }
                    else { $temp_string[] = '<span class="robot_affinity robot_type robot_type_'.$robot_affinity.'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</span>'; }
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span class="robot_affinity robot_type robot_type_none">None</span>';
                }
                ?>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Speed :</label>
                <span class="robot_stat robot_type robot_type_speed" style="padding-left: <?= ceil($robot_info['robot_speed'] * ($print_options['layout_style'] == 'website' ? 1 : 0.9)) ?>px;"><?= $robot_info['robot_speed'] ?></span>
              </td>
              <td class="center">&nbsp;</td>
              <td class="right">
                <label style="display: block; float: left;">Immunities :</label>
                <?
                if (!empty($robot_info['robot_immunities'])){
                  $temp_string = array();
                  foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                    if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="database/abilities/'.$robot_immunity.'/" class="robot_immunity robot_type robot_type_'.$robot_immunity.'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</a>'; }
                    else { $temp_string[] = '<span class="robot_immunity robot_type robot_type_'.$robot_immunity.'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</span>'; }
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span class="robot_immunity robot_type robot_type_none">None</span>';
                }
                ?>
              </td>
            </tr>

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

    <? if($print_options['show_quotes']): ?>

      <h2 id="quotes" class="header header_left <?= $robot_header_types ?>" style="margin-right: 0;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Quotes
      </h2>
      <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
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
                <label style="display: block; float: left;">Start Quote : </label>
                <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_start']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_start']) : '&hellip;' ?>&quot;</span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Taunt Quote : </label>
                <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_taunt']) : '&hellip;' ?>&quot;</span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Victory Quote : </label>
                <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_victory']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_victory']) : '&hellip;' ?>&quot;</span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Defeat Quote : </label>
                <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_defeat']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_defeat']) : '&hellip;' ?>&quot;</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    <? endif; ?>

    <? if($print_options['show_description'] && !empty($robot_info['robot_description2'])): ?>

      <h2 class="header header_left <?= $robot_header_types ?>" style="margin-right: 0;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Description
      </h2>
      <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
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

    <? endif; ?>

    <? if($print_options['show_sprites'] && (!isset($robot_info['robot_image_sheets']) || $robot_info['robot_image_sheets'] !== 0) && $robot_image_token != 'robot' ): ?>
      <h2 id="sprites" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Sprites
      </h2>
      <div class="body body_full" style="margin: 0; padding: 10px; min-height: 10px;">
        <div style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #4d4d4d url(images/sprite-grid.gif) scroll repeat -10px -30px; overflow: hidden; padding: 10px 30px;">
          <?
          // Collect the number of sheets
          $temp_sheet_number = !empty($robot_info['robot_image_sheets']) ? $robot_info['robot_image_sheets'] : 1;
          // Loop through the different frames and print out the sprite sheets
          for ($temp_sheet = 1; $temp_sheet <= $temp_sheet_number; $temp_sheet++){
            foreach (array('right', 'left') AS $temp_direction){
              $temp_title = $robot_sprite_title.' | Mugshot Sprite '.ucfirst($temp_direction);
              $temp_label = 'Mugshot '.ucfirst(substr($temp_direction, 0, 1));
              echo '<div style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$robot_sprite_size.'px; height: '.$robot_sprite_size.'px; overflow: hidden;">';
                echo '<img style="margin-left: 0;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/robots/'.$robot_image_token.($temp_sheet > 1 ? '-'.$temp_sheet : '').'/mug_'.$temp_direction.'_'.$robot_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
              echo '</div>';
            }
          }
          // Loop through the different frames and print out the sprite sheets
          for ($temp_sheet = 1; $temp_sheet <= $temp_sheet_number; $temp_sheet++){
            foreach ($robot_sprite_frames AS $this_key => $this_frame){
              $margin_left = ceil((0 - $this_key) * $robot_sprite_size);
              $frame_relative = $this_frame;
              //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($robot_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
              $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
              foreach (array('right', 'left') AS $temp_direction){
                $temp_title = $robot_sprite_title.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                $image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
                if ($temp_sheet > 1){ $image_token .= '-'.$temp_sheet; }
                echo '<div style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$robot_sprite_size.'px; height: '.$robot_sprite_size.'px; overflow: hidden;">';
                  echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/robots/'.$image_token.'/sprite_'.$temp_direction.'_'.$robot_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                  echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                echo '</div>';
              }
            }
          }
          ?>
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
          elseif ($robot_info['robot_image_editor'] == 4117 && in_array($robot_info['robot_token'], array('splash-woman'))){ $temp_break = true; $temp_editor_title = 'Jonathan Backstrom / Rhythm_BCA</strong> <span style="color: #565656;"> | </span> Assembly by <strong>MegaBossMan / milansaponja'; }
          elseif ($robot_info['robot_image_editor'] == 4117){ $temp_break = true; $temp_editor_title = 'Jonathan Backstrom / Rhythm_BCA</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
          elseif ($robot_info['robot_image_editor'] == 3842){ $temp_break = true; $temp_editor_title = 'MegaBossMan / milansaponja'; }
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
    <? endif; ?>

    <? if($print_options['show_abilities']): ?>

      <h2 id="abilities" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Abilities
      </h2>
      <div class="body body_full" style="margin: 0; padding: 2px 3px;">
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
                    if (!empty($this_ability_type)){ $this_ability_title_html .= '<span class="type">'.$this_ability_type.'</span>'; }
                    if (!empty($this_ability_damage)){ $this_ability_title_html .= '<span class="damage">'.$this_ability_damage.(!empty($this_ability_damage_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'D' : 'Damage').'</span>'; }
                    if (!empty($this_ability_recovery)){ $this_ability_title_html .= '<span class="recovery">'.$this_ability_recovery.(!empty($this_ability_recovery_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'R' : 'Recovery').'</span>'; }
                    if (!empty($this_ability_accuracy)){ $this_ability_title_html .= '<span class="accuracy">'.$this_ability_accuracy.'% Accuracy</span>'; }
                    $this_ability_sprite_path = 'images/abilities/'.$this_ability_image.'/icon_left_40x40.png';
                    if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_ability_sprite_path)){ $this_ability_image = 'ability'; $this_ability_sprite_path = 'images/abilities/ability/icon_left_40x40.png'; }
                    $this_ability_sprite_html = '<span class="icon"><img src="'.$this_ability_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE.'" alt="'.$this_ability_name.' Icon" /></span>';
                    $this_ability_title_html = '<span class="label">'.$this_ability_title_html.'</span>';
                    //$this_ability_title_html = (is_numeric($this_level) && $this_level > 1 ? 'Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).' : ' : $this_level.' : ').$this_ability_title_html;
                    // Show the ability method separator if necessary
                    if ($ability_method != $this_ability_method){
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
                  echo '<span class="robot_ability robot_type_none"><span class="chrome">None</span></span>';
                }
                ?>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    <? endif; ?>

    <? if($print_options['show_records']): ?>

      <h2 id="records" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Records
      </h2>
      <div class="body body_full" style="margin: 0 auto 5px; padding: 2px 0; min-height: 10px;">
        <?
        // Collect the database records for this robot
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
        ?>
        <table class="full" style="margin: 5px auto 10px;">
          <colgroup>
            <col width="100%" />
          </colgroup>
          <tbody>
            <? if($robot_info['robot_class'] == 'master'): ?>
              <tr>
                <td class="right">
                  <label style="display: block; float: left;">Unlocked By : </label>
                  <span class="robot_quote"><?= $temp_robot_records['robot_unlocked'] == 1 ? '1 Player' : number_format($temp_robot_records['robot_unlocked'], 0, '.', ',').' Players' ?></span>
                </td>
              </tr>
            <? endif; ?>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Encountered : </label>
                <span class="robot_quote"><?= $temp_robot_records['robot_encountered'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_encountered'], 0, '.', ',').' Times' ?></span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Summoned : </label>
                <span class="robot_quote"><?= $temp_robot_records['robot_summoned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_summoned'], 0, '.', ',').' Times' ?></span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Defeated : </label>
                <span class="robot_quote"><?= $temp_robot_records['robot_defeated'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_defeated'], 0, '.', ',').' Times' ?></span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Scanned : </label>
                <span class="robot_quote"><?= $temp_robot_records['robot_scanned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_scanned'], 0, '.', ',').' Times' ?></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    <? endif; ?>

    <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

      <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
      <a class="link link_permalink permalink" href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_token'] ?>/" rel="permalink">+ Permalink</a>

    <? elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

      <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
      <a class="link link_permalink permalink" href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_token'] ?>/" rel="permalink">+ View More</a>

    <? endif; ?>
  </div>
</div>
<?
// Collect the outbut buffer contents
$this_markup = trim(ob_get_clean());
?>