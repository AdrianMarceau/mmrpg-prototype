<?

// Define the global variables
global $DB;
global $mmrpg_index, $this_current_uri, $this_current_url;
global $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_mechas, $mmrpg_database_abilities, $mmrpg_database_types;

// Define the print style defaults
if (!isset($print_options['layout_style'])){ $print_options['layout_style'] = 'website'; }
if ($print_options['layout_style'] == 'website'){
  if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
  if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = true; }
  if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
  if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
  if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
  if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
  if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
} elseif ($print_options['layout_style'] == 'website_compact'){
  if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
  if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = true; }
  if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
  if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
  if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
  if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
  if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
}

// Collect the field sprite dimensions
$field_image_size = !empty($field_info['field_image_size']) ? $field_info['field_image_size'] : 40;
$field_image_size_text = $field_image_size.'x'.$field_image_size;
$field_image_token = !empty($field_info['field_image']) ? $field_info['field_image'] : $field_info['field_token'];
$field_type_token = !empty($field_info['field_type']) ? $field_info['field_type'] : 'none';
if (!empty($field_info['field_type2'])){ $field_type_token .= '_'.$field_info['field_type2']; }

// Define the sprite sheet alt and title text
$field_sprite_size = $field_image_size * 2;
$field_sprite_size_text = $field_sprite_size.'x'.$field_sprite_size;
$field_sprite_title = $field_info['field_name'];
//$field_sprite_title = $field_info['field_number'].' '.$field_info['field_name'];
//$field_sprite_title .= ' Sprite Sheet | Robot Database | Mega Man RPG Prototype';

// Define the sprite frame index for robot images
$field_sprite_frames = array('base','taunt','victory','defeat','command','damage');

// Collect the robot master info if applicable
$robot_master_info = array();
if (!empty($field_info['field_master']) && !empty($mmrpg_database_robots[$field_info['field_master']])){ $robot_master_info = $mmrpg_database_robots[$field_info['field_master']]; }

// Collect the robot master info if applicable
$robot_master_info_array = array();
$temp_robot_masters = array();
if (!empty($field_info['field_master']) && $field_info['field_master'] != 'robot'){ $temp_robot_masters[] = $field_info['field_master']; }
if (!empty($field_info['field_master2'])){ $temp_robot_masters = array_merge($temp_robot_masters, $field_info['field_master2']); }
if (!empty($temp_robot_masters)){
  foreach ($temp_robot_masters AS $key => $token){
    if (!empty($mmrpg_database_robots[$token])){
      $robot_master_info = $mmrpg_database_robots[$token];
      $robot_master_info_array[] = $robot_master_info;
    }
  }
}

// Collect the robot master info if applicable
$robot_mecha_info_array = array();
if (!empty($field_info['field_mechas'])){
  foreach ($field_info['field_mechas'] AS $key => $token){
    if (!empty($mmrpg_database_mechas[$token])){
      $robot_mecha_info = $mmrpg_database_mechas[$token];
      $robot_mecha_info_array[] = $robot_mecha_info;
    }
  }
}

// Start the output buffer
ob_start();
?>
<div class="database_container database_field_container" data-token="<?=$field_info['field_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">
  <a class="anchor" id="<?=$field_info['field_token']?>">&nbsp;</a>

  <div class="subbody event event_triple event_visible" data-token="<?=$field_info['field_token']?>" style="min-height: 90px; <?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

    <? if($print_options['show_icon']): ?>
      <div class="this_sprite sprite_left" style="height: 40px;">
        <? if($print_options['show_key'] !== false): ?>
          <div class="mugshot field_type field_type_<?= !empty($field_info['field_type']) ? $field_info['field_type'] : 'none' ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
        <? endif; ?>
        <? if ($field_image_token != 'field'){ ?>
          <div class="mugshot field_type field_type_<?= !empty($field_info['field_type']) ? $field_info['field_type'] : 'none' ?>"><div style="background-image: url(i/f/<?= $field_image_token ?>/bfa.png?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 50px 50px; background-position: -5px -5px;" class="sprite sprite_field sprite_40x40 sprite_40x40_mug sprite_size_<?= $field_image_size_text ?> sprite_size_<?= $field_image_size_text ?>_mug field_status_active field_position_active"><?=$field_info['field_name']?>'s Avatar</div></div>
        <? } else { ?>
          <div class="mugshot field_type field_type_<?= !empty($field_info['field_type']) ? $field_info['field_type'] : 'none' ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); " class="sprite sprite_field sprite_40x40 sprite_40x40_mug sprite_size_<?= $field_image_size_text ?> sprite_size_<?= $field_image_size_text ?>_mug field_status_active field_position_active">No Image</div></div>
        <? }?>
      </div>
    <? endif; ?>

    <? if($print_options['show_basics']): ?>
      <h2 class="header header_left field_type_<?= $field_type_token ?>" style="margin-right: 0;">
        <? if($print_options['layout_style'] == 'website_compact'): ?>
          <a href="database/fields/<?= $field_info['field_token'] ?>/"><?= $field_info['field_name'] ?></a>
        <? else: ?>
          <?= $field_info['field_name'].(!preg_match('/s$/i', $field_info['field_name']) ? '&#39;s' : '&#39;') ?> Data
        <? endif; ?>
        <? if (!empty($field_info['field_type'])): ?>
          <span class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($field_info['field_type']) ?> Type</span>
        <? else: ?>
          <span class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;">Neutral Type</span>
        <? endif; ?>
      </h2>
      <div class="body body_left" style="margin-right: 0; padding: 2px 3px; min-height: 100px;">
        <table class="full" style="margin: 5px auto 10px;">
          <colgroup>
            <col width="48%" />
            <col width="1%" />
            <col width="48%" />
          </colgroup>
          <tbody>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Name :</label>
                <span class="field_type"><?= $field_info['field_name'] ?></span>
              </td>
              <td class="middle">&nbsp;</td>
              <td class="right">
                <?
                // Define the source game string
                if ($field_info['field_token'] == 'intro-field'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                elseif ($field_info['field_token'] == 'light-laboratory' || $field_info['field_token'] == 'wily-castle'){ $temp_source_string = 'Mega Man'; }
                elseif ($field_info['field_token'] == 'cossack-citadel'){ $temp_source_string = 'Mega Man 4'; }
                elseif ($field_info['field_token'] == 'oil-wells' || $field_info['field_token'] == 'clock-citadel'){ $temp_source_string = 'Mega Man Powered Up'; }
                elseif ($field_info['field_game'] == 'MM01'){ $temp_source_string = 'Mega Man'; }
                elseif ($field_info['field_game'] == 'MM00' || $field_info['field_game'] == 'MMRPG'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                elseif (preg_match('/^MM([0-9]{2})$/', $field_info['field_game'])){ $temp_source_string = 'Mega Man '.ltrim(str_replace('MM', '', $field_info['field_game']), '0'); }
                else { $temp_source_string = '&hellip;'; }
                ?>
                <label style="display: block; float: left;">Source :</label>
                <span class="field_type"><?= $temp_source_string ?></span>
              </td>
            </tr>
            <tr>
              <td class="right">
                <label style="display: block; float: left;">Type :</label>
                <a href="database/fields/<?= !empty($field_info['field_type']) ? $field_info['field_type'] : 'none' ?>/" class="field_type field_type_<?= !empty($field_info['field_type']) ? $field_info['field_type'] : 'none' ?>"><?= !empty($field_info['field_type']) ? ucfirst($field_info['field_type']) : 'Neutral' ?> Type</a>
              </td>
              <td class="middle">&nbsp;</td>
              <td class="right">
                <label style="display: block; float: left;">Music :</label>
                <? if(!empty($field_info['field_music_name']) && !empty($field_info['field_music_link'])): ?>
                  <? if(is_array($field_info['field_music_link'])):?>
                    <? foreach($field_info['field_music_link'] AS $key => $link): ?>
                      <a href="<?= $link ?>" target="_blank" class="field_type field_type_<?= !empty($field_info['field_type']) ? $field_info['field_type'] : 'none' ?>"><?= $key == 0 ? $field_info['field_music_name'] : $key + 1 ?></a>
                    <? endforeach; ?>
                  <? else: ?>
                    <a href="<?= $field_info['field_music_link'] ?>" target="_blank" class="field_type field_type_<?= !empty($field_info['field_type']) ? $field_info['field_type'] : 'none' ?>"><?= $field_info['field_music_name'] ?></a>
                  <? endif; ?>
                <? else: ?>
                  <span class="field_type">???</span>
                <? endif; ?>
              </td>
            </tr>
            <tr>
              <td  class="right">
                <label style="display: block; float: left;"><?= empty($robot_master_info_array) || count($robot_master_info_array) == 1 ? 'Master' : 'Masters' ?> :</label>
                <?
                // Define the special stages
                $temp_special_stages = array('final-destination', 'final-destination-2', 'final-destination-3');
                // Loop through and display support master links
                if (!empty($robot_master_info_array)){
                  foreach ($robot_master_info_array AS $key => $robot_master_info){
                     if ($robot_master_info['robot_class'] == 'master' && preg_match('/-[0-9]+$/', $robot_master_info['robot_token'])){ $robot_master_info['robot_name'] .= ' '.(preg_replace('/^(.*?)-([0-9]+)$/', '$2', $robot_master_info['robot_token'])); }
                     ?>
                       <a href="database/robots/<?= $robot_master_info['robot_token'] ?>/" class="field_type field_type_<?= (!empty($robot_master_info['robot_core']) ? $robot_master_info['robot_core'] : 'none').(!empty($robot_master_info['robot_type2']) ? '_'.$robot_master_info['robot_type2'] : '') ?>"><?= $robot_master_info['robot_name'] ?></a>
                     <?
                  }
                }
                // Else if this was a special stage
                elseif (in_array($field_info['field_token'], $temp_special_stages)){
                  ?>
                    <span class="field_type">???</span>
                  <?
                }
                // Else if there are none to display
                else {
                  ?>
                    <span class="field_type">None</span>
                  <?
                }
                ?>
              </td>
              <td class="center">&nbsp;</td>
              <td  class="right">
                <label style="display: block; float: left;"><?= count($robot_mecha_info_array) == 1 ? 'Mecha' : 'Mechas' ?> :</label>
                <?
                // Define the special stages
                $temp_special_stages = array('final-destination', 'final-destination-2', 'final-destination-3', 'prototype-complete');
                // Loop through and display support mecha links
                if (!in_array($field_info['field_token'], $temp_special_stages) && !empty($robot_mecha_info_array)){
                  foreach ($robot_mecha_info_array AS $key => $robot_mecha_info){
                     if ($robot_mecha_info['robot_class'] == 'mecha' && preg_match('/-[0-9]+$/', $robot_mecha_info['robot_token'])){ $robot_mecha_info['robot_name'] = (preg_replace('/^(.*?)-([0-9]+)$/', '$2', $robot_mecha_info['robot_token'])); }
                     ?>
                       <a href="database/mechas/<?= $robot_mecha_info['robot_token'] ?>/" class="robot_type robot_type_<?= (!empty($robot_mecha_info['robot_core']) ? $robot_mecha_info['robot_core'] : 'none').(!empty($robot_mecha_info['robot_type2']) ? '_'.$robot_mecha_info['robot_type2'] : '') ?>"><?= $robot_mecha_info['robot_name'] ?></a>
                     <?
                  }
                }
                // Else if this was a special stage
                elseif (in_array($field_info['field_token'], $temp_special_stages)){
                  ?>
                    <span class="robot_type">???</span>
                  <?
                }
                // Else if there are none to display
                else {
                  ?>
                    <span class="robot_type">None</span>
                  <?
                }
                ?>
              </td>
            </tr>
            <tr>
              <td class="right" colspan="3" style="padding: 8px 5px;">
                <label style="display: block; float: left;">Multipliers :</label>
                <?
                if (!empty($field_info['field_multipliers'])){
                  $temp_string = array();
                  asort($field_info['field_multipliers']);
                  $field_info['field_multipliers'] = array_reverse($field_info['field_multipliers']);
                  foreach ($field_info['field_multipliers'] AS $temp_token => $temp_value){
                    $temp_string[] = '<span style="padding: 4px 8px; line-height: 24px; " class="field_multiplier field_type field_type_'.$temp_token.'">'.$mmrpg_index['types'][$temp_token]['type_name'].' x '.number_format($temp_value, 1).'</span>';
                  }
                  echo implode(' ', $temp_string);
                } else {
                  echo '<span style="padding: 4px 8px; line-height: 24px; " class="field_multiplier field_type field_type_none">None</span>';
                }
                ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    <? endif; ?>

    <? if($print_options['show_description'] && !empty($field_info['field_description2'])): ?>

      <h2 class="header header_left field_type_<?= $field_type_token ?>" style="margin-right: 0;">
        <?= $field_info['field_name'] ?>&#39;s Description
      </h2>
      <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
        <table class="full" style="margin: 5px auto 10px;">
          <colgroup>
            <col width="100%" />
          </colgroup>
          <tbody>
            <tr>
              <td class="right">
                <div class="field_description" style="text-align: justify; padding: 0 4px;"><?= $field_info['field_description2'] ?></div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    <? endif; ?>


    <? if($print_options['show_sprites'] && $field_image_token != 'field'): ?>

      <h2 class="header header_full field_type_<?= $field_type_token ?>" style="margin: 10px 0 0; text-align: left;">
        <?= $field_info['field_name'].(!preg_match('/s$/i', $field_info['field_name']) ? '&#39;s' : '&#39;') ?> Sprites
      </h2>
      <div class="body body_full" style="margin: 0; padding: 10px; min-height: auto;">
        <div id="sprite_container" style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #191919 none scroll repeat -10px -30px; overflow: hidden; padding: 0; margin-bottom: 10px;">
          <div class="sprite_background" style="border: 0 none transparent; border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: transparent url(i/f/<?= $field_info['field_background'] ?>/bfbb.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>) scroll repeat center center; overflow: hidden; height: 244px;">
            <div class="sprite_foreground" style="border: 0 none transparent; border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: transparent url(i/f/<?= $field_info['field_background'] ?>/bffb.png?<?= MMRPG_CONFIG_CACHE_DATE ?>) scroll repeat center center; overflow: hidden; height: 244px;">
              &nbsp;
            </div>
          </div>
        </div>
        <?
        // Define the editor title based on ID
        $temp_editor_title = 'Undefined';
        $temp_final_divider = '<span style="color: #565656;"> | </span>';
        if (empty($field_info['field_image_editor'])){ $field_info['field_image_editor'] = 412; }
        if (!empty($field_info['field_image_editor'])){
          $temp_break = false;
          if ($field_info['field_image_editor'] == 412){ $temp_editor_title = 'Adrian Marceau / Ageman20XX'; }
          elseif ($field_info['field_image_editor'] == 110){ $temp_break = true; $temp_editor_title = 'MetalMarioX100 / EliteP1</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
          elseif ($field_info['field_image_editor'] == 18){ $temp_break = true; $temp_editor_title = 'Sean Adamson / MetalMan</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
          elseif ($field_info['field_image_editor'] == 4117){ $temp_break = true; $temp_editor_title = 'Jonathan Backstrom / Rhythm_BCA</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
          if ($temp_break){ $temp_final_divider = '<br />'; }
        }
        $temp_is_capcom = true;
        $temp_is_original = array();
        if (in_array($field_info['field_token'], $temp_is_original)){ $temp_is_capcom = false; }
        if ($temp_is_capcom){
          echo '<p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 13px; margin-top: 6px;">Sprite Editing by <strong>'.$temp_editor_title.'</strong> '.$temp_final_divider.' Original Artwork by <strong>Capcom</strong></p>'."\n";
        } else {
          echo '<p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 13px; margin-top: 6px;">Sprite Editing by <strong>'.$temp_editor_title.'</strong> '.$temp_final_divider.' Original Field by <strong>Adrian Marceau</strong></p>'."\n";
        }
        ?>
      </div>

    <? endif; ?>

    <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

      <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
      <a class="link link_permalink permalink" href="database/fields/<?= $field_info['field_token'] ?>/" rel="permalink">+ Permalink</a>

    <? elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

      <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
      <a class="link link_permalink permalink" href="database/fields/<?= $field_info['field_token'] ?>/" rel="permalink">+ View More</a>

    <? endif; ?>

  </div>
</div>
<?
// Collect the outbut buffer contents
$this_markup = trim(ob_get_clean());

?>