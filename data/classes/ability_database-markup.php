<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url;
global $mmrpg_database_abilities, $mmrpg_database_robots, $mmrpg_database_abilities, $mmrpg_database_types;
global $DB;

// Define the print style defaults
if (!isset($print_options['layout_style'])){ $print_options['layout_style'] = 'website'; }
if ($print_options['layout_style'] == 'website'){
    if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
    if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = true; }
    if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
    if (!isset($print_options['show_robots'])){ $print_options['show_robots'] = true; }
    if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
    if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
    if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
} elseif ($print_options['layout_style'] == 'website_compact'){
    if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
    if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = true; }
    if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
    if (!isset($print_options['show_robots'])){ $print_options['show_robots'] = false; }
    if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
    if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
    if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
} elseif ($print_options['layout_style'] == 'event'){
    if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
    if (!isset($print_options['show_icon'])){ $print_options['show_icon'] = false; }
    if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
    if (!isset($print_options['show_robots'])){ $print_options['show_robots'] = false; }
    if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
    if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = false; }
    if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
}

// Collect the ability sprite dimensions
$ability_image_size = !empty($ability_info['ability_image_size']) ? $ability_info['ability_image_size'] : 40;
$ability_image_size_text = $ability_image_size.'x'.$ability_image_size;
$ability_image_token = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];

// Collect the ability's type for background display
$ability_type_class = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
if ($ability_type_class != 'none' && !empty($ability_info['ability_type2'])){ $ability_type_class .= '_'.$ability_info['ability_type2']; }
elseif ($ability_type_class == 'none' && !empty($ability_info['ability_type2'])){ $ability_type_class = $ability_info['ability_type2'];  }
$ability_header_types = 'ability_type_'.$ability_type_class.' ';
// If this is a special category of item, it's a special type
if (preg_match('/^item-score-ball-(red|blue|green|purple)$/i', $ability_info['ability_token'])){ $ability_info['ability_type_special'] = 'bonus'; }
elseif (preg_match('/^item-super-(pellet|capsule)$/i', $ability_info['ability_token'])){ $ability_info['ability_type_special'] = 'multi'; }

// Define the sprite sheet alt and title text
$ability_sprite_size = $ability_image_size * 2;
$ability_sprite_size_text = $ability_sprite_size.'x'.$ability_sprite_size;
$ability_sprite_title = $ability_info['ability_name'];
//$ability_sprite_title = $ability_info['ability_number'].' '.$ability_info['ability_name'];
//$ability_sprite_title .= ' Sprite Sheet | Robot Database | Mega Man RPG Prototype';

// Define the sprite frame index for robot images
$ability_sprite_frames = array('frame_01','frame_02','frame_03','frame_04','frame_05','frame_06','frame_07','frame_08','frame_09','frame_10');

// Limit any damage or recovery percents to 100%
if (!empty($ability_info['ability_damage_percent']) && $ability_info['ability_damage'] > 100){ $ability_info['ability_damage'] = 100; }
if (!empty($ability_info['ability_damage2_percent']) && $ability_info['ability_damage2'] > 100){ $ability_info['ability_damage2'] = 100; }
if (!empty($ability_info['ability_recovery_percent']) && $ability_info['ability_recovery'] > 100){ $ability_info['ability_recovery'] = 100; }
if (!empty($ability_info['ability_recovery2_percent']) && $ability_info['ability_recovery2'] > 100){ $ability_info['ability_recovery2'] = 100; }

// Start the output buffer
ob_start();
?>
<div class="database_container database_<?= $ability_info['ability_class'] == 'item' ? 'item' : 'ability' ?>_container" data-token="<?=$ability_info['ability_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

    <? if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
        <a class="anchor" id="<?=$ability_info['ability_token']?>">&nbsp;</a>
    <? endif; ?>

    <div class="subbody event event_triple event_visible" data-token="<?= $ability_info['ability_token']?>" style="<?= ($print_options['layout_style'] == 'event' ? 'margin: 0 !important; ' : '').($print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important; ' : '') ?>">

        <? if($print_options['show_icon']): ?>

            <div class="this_sprite sprite_left" style="height: 40px;">
                <? if($print_options['show_icon']): ?>
                    <? if($print_options['show_key'] !== false): ?>
                        <div class="icon ability_type <?= $ability_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
                    <? endif; ?>
                    <? if ($ability_image_token != 'ability'){ ?>
                        <div class="icon ability_type <?= $ability_header_types ?>"><div style="background-image: url(images/abilities/<?= $ability_image_token ?>/icon_right_<?= $ability_image_size_text ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon ability_status_active ability_position_active"><?=$ability_info['ability_name']?>'s Mugshot</div></div>
                    <? } else { ?>
                        <div class="icon ability_type <?= $ability_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); " class="sprite sprite_ability sprite_40x40 sprite_40x40_icon sprite_size_<?= $ability_image_size_text ?> sprite_size_<?= $ability_image_size_text ?>_icon ability_status_active ability_position_active">No Image</div></div>
                    <? } ?>
                <? endif; ?>
            </div>

        <? endif; ?>

        <? if($print_options['show_basics']): ?>

            <h2 class="header header_left <?= $ability_header_types ?> <?= (!$print_options['show_icon']) ? 'noicon' : 'hasicon' ?>">
                <? if($print_options['layout_style'] == 'website_compact'): ?>
                    <a href="<?= preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $ability_info['ability_token']).'/' : 'database/abilities/'.$ability_info['ability_token'].'/' ?>"><?= $ability_info['ability_name'] ?></a>
                <? else: ?>
                    <?= $ability_info['ability_name'] ?>&#39;s Data
                <? endif; ?>
                <? if (!empty($ability_info['ability_type_special'])){ ?>
                    <div class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($ability_info['ability_type_special']) ?> Type</div>
                <? } elseif (!empty($ability_info['ability_type']) && !empty($ability_info['ability_type2'])){ ?>
                    <div class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($ability_info['ability_type']).' / '.ucfirst($ability_info['ability_type2']) ?> Type</div>
                <? } elseif (!empty($ability_info['ability_type'])){ ?>
                    <div class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= ucfirst($ability_info['ability_type']) ?> Type</div>
                <? } else { ?>
                    <div class="header_core ability_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;">Neutral Type</div>
                <? } ?>
            </h2>
            <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px; <?= (!$print_options['show_icon']) ? 'margin-left: 0; ' : '' ?><?= $print_options['layout_style'] == 'event' ? 'font-size: 10px; min-height: 150px; ' : '' ?>">
                <table class="full" style="margin: 5px auto 10px;">
                    <colgroup>
                        <col width="48%" />
                        <col width="1%" />
                        <col width="48%" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <td  class="right">
                                <label style="display: block; float: left;">Name :</label>
                                <span class="ability_type ability_type_"><?=$ability_info['ability_name']?></span>
                            </td>
                            <td class="center">&nbsp;</td>
                            <td class="right">
                                <label style="display: block; float: left;">Type :</label>
                                <? if($print_options['layout_style'] != 'event'): ?>
                                    <?
                                    if (!empty($ability_info['ability_type_special'])){
                                        echo '<a href="'.((preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/' : 'database/abilities/').$ability_info['ability_type_special'].'/').'" class="ability_type '.$ability_header_types.'">'.ucfirst($ability_info['ability_type_special']).'</a>';
                                    }
                                    elseif (!empty($ability_info['ability_type'])){
                                        $temp_string = array();
                                        $ability_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
                                        $temp_string[] = '<a href="'.((preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/' : 'database/abilities/').$ability_type.'/').'" class="ability_type ability_type_'.$ability_type.'">'.$mmrpg_index['types'][$ability_type]['type_name'].'</a>';
                                        if (!empty($ability_info['ability_type2'])){
                                            $ability_type2 = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : 'none';
                                            $temp_string[] = '<a href="'.((preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/' : 'database/abilities/').$ability_type2.'/').'" class="ability_type ability_type_'.$ability_type2.'">'.$mmrpg_index['types'][$ability_type2]['type_name'].'</a>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<a href="'.((preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/' : 'database/abilities/').'none/').'" class="ability_type ability_type_none">Neutral</a>';
                                    }
                                    ?>
                                <? else: ?>
                                    <?
                                    if (!empty($ability_info['ability_type_special'])){
                                        echo '<span class="ability_type '.$ability_header_types.'">'.ucfirst($ability_info['ability_type_special']).'</span>';
                                    }
                                    elseif (!empty($ability_info['ability_type'])){
                                        $temp_string = array();
                                        $ability_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';
                                        $temp_string[] = '<span class="ability_type ability_type_'.$ability_type.'">'.$mmrpg_index['types'][$ability_type]['type_name'].'</span>';
                                        if (!empty($ability_info['ability_type2'])){
                                            $ability_type2 = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : 'none';
                                            $temp_string[] = '<span class="ability_type ability_type_'.$ability_type2.'">'.$mmrpg_index['types'][$ability_type2]['type_name'].'</span>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<span class="ability_type ability_type_none">Neutral</span>';
                                    }
                                    ?>
                                <? endif; ?>
                            </td>
                        </tr>
                        <? if($ability_info['ability_class'] != 'item'): ?>

                            <? if($ability_image_token != 'ability'): ?>

                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Power :</label>
                                        <? if(!empty($ability_info['ability_damage']) || !empty($ability_info['ability_recovery'])): ?>
                                            <? if(!empty($ability_info['ability_damage'])){ ?><span class="ability_stat"><?= $ability_info['ability_damage'].(!empty($ability_info['ability_damage_percent']) ? '%' : '') ?> Damage</span><? } ?>
                                            <? if(!empty($ability_info['ability_recovery'])){ ?><span class="ability_stat"><?= $ability_info['ability_recovery'].(!empty($ability_info['ability_recovery_percent']) ? '%' : '') ?> Recovery</span><? } ?>
                                        <? else: ?>
                                            <span class="ability_stat">-</span>
                                        <? endif; ?>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Accuracy :</label>
                                        <span class="ability_stat"><?= $ability_info['ability_accuracy'].'%' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Energy :</label>
                                        <span class="ability_stat"><?= !empty($ability_info['ability_energy']) ? $ability_info['ability_energy'] : '-' ?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Speed :</label>
                                        <span class="ability_stat"><?= !empty($ability_info['ability_speed']) ? $ability_info['ability_speed'] : '1' ?></span>
                                    </td>
                                </tr>

                            <? else: ?>

                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Power :</label>
                                        <span class="ability_stat">-</span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Accuracy :</label>
                                        <span class="ability_stat">-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Energy :</label>
                                        <span class="ability_stat">-</span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Speed :</label>
                                        <span class="ability_stat">-</span>
                                    </td>
                                </tr>

                            <? endif; ?>

                        <? endif; ?>
                    </tbody>
                </table>
                <table class="full" style="margin: 5px auto 10px;">
                    <colgroup>
                        <col width="100%" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <td class="right">
                                <label style="display: block; float: left;">Description :</label>
                                <div class="description_container"><?
                                // Define the search/replace pairs for the description
                                $temp_find = array('{DAMAGE}', '{RECOVERY}', '{DAMAGE2}', '{RECOVERY2}', '{}');
                                $temp_replace = array(
                                    (!empty($ability_info['ability_damage']) ? $ability_info['ability_damage'] : 0), // {DAMAGE}
                                    (!empty($ability_info['ability_recovery']) ? $ability_info['ability_recovery'] : 0), // {RECOVERY}
                                    (!empty($ability_info['ability_damage2']) ? $ability_info['ability_damage2'] : 0), // {DAMAGE2}
                                    (!empty($ability_info['ability_recovery2']) ? $ability_info['ability_recovery2'] : 0), // {RECOVERY2}
                                    '' // {}
                                    );
                                echo !empty($ability_info['ability_description']) ? str_replace($temp_find, $temp_replace, $ability_info['ability_description']) : '&hellip;'
                                ?></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <? endif; ?>

        <? if($print_options['show_sprites'] && (!isset($ability_info['ability_image_sheets']) || $ability_info['ability_image_sheets'] !== 0) && $ability_image_token != 'ability' ): ?>

            <h2 class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                <?=$ability_info['ability_name']?>&#39;s Sprites
            </h2>
            <div class="body body_full" style="margin: 0; padding: 10px; min-height: auto;">
                <div style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #4d4d4d url(images/sprite-grid.gif) scroll repeat -10px -30px; overflow: hidden; padding: 10px 30px;">
                    <?
                    // Show the ability mugshot sprite
                    foreach (array('right', 'left') AS $temp_direction){
                        $temp_title = $ability_sprite_title.' | Icon Sprite '.ucfirst($temp_direction);
                        $temp_label = 'Icon '.ucfirst(substr($temp_direction, 0, 1));
                        echo '<div style="'.($ability_sprite_size <= 80 ? 'padding-top: 20px; ' : '').'float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$ability_sprite_size.'px; height: '.$ability_sprite_size.'px; overflow: hidden;">';
                            echo '<img style="margin-left: 0;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/abilities/'.$ability_image_token.'/icon_'.$temp_direction.'_'.$ability_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                            echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                        echo '</div>';
                    }
                    // Loop through the different frames and print out the sprite sheets
                    $temp_sheet_number = !empty($ability_info['ability_image_sheets']) ? $ability_info['ability_image_sheets'] : 1;
                    for ($temp_sheet = 1; $temp_sheet <= $temp_sheet_number; $temp_sheet++){
                        foreach ($ability_sprite_frames AS $this_key => $this_frame){
                            $margin_left = ceil((0 - $this_key) * $ability_sprite_size);
                            $frame_relative = $this_frame;
                            if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($ability_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                            $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                            foreach (array('right', 'left') AS $temp_direction){
                                $temp_title = $ability_sprite_title.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                $image_token = !empty($ability_info['ability_image']) ? $ability_info['ability_image'] : $ability_info['ability_token'];
                                if ($temp_sheet > 1){ $image_token .= '-'.$temp_sheet; }
                                echo '<div style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$ability_sprite_size.'px; height: '.$ability_sprite_size.'px; overflow: hidden;">';
                                    echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/abilities/'.$image_token.'/sprite_'.$temp_direction.'_'.$ability_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
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
                if (!empty($ability_info['ability_image_editor'])){
                    if ($ability_info['ability_image_editor'] == 412){ $temp_editor_title = 'Adrian Marceau / Ageman20XX'; }
                    elseif ($ability_info['ability_image_editor'] == 110){ $temp_editor_title = 'MetalMarioX100 / EliteP1'; }
                    elseif ($ability_info['ability_image_editor'] == 18){ $temp_editor_title = 'Sean Adamson / MetalMan'; }
                } elseif ($ability_image_token != 'ability'){
                    $temp_editor_title = 'Adrian Marceau / Ageman20XX';
                }
                ?>
                <p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 10px; margin-top: 6px;">Sprite Editing by <strong><?= $temp_editor_title ?></strong> <span style="color: #565656;"> | </span> Original Artwork by <strong>Capcom</strong></p>
            </div>

        <? endif; ?>

        <? if($print_options['show_robots'] && $ability_info['ability_class'] != 'item'): ?>

            <h2 class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                <?=$ability_info['ability_name']?>&#39;s Robots
            </h2>
            <div class="body body_full" style="margin: 0; padding: 2px 3px;">
                <table class="full" style="margin: 5px auto 10px;">
                    <colgroup>
                        <col width="100%" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <td class="right">
                                <div class="robot_container">
                                <?
                                $ability_type_one = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : false;
                                $ability_type_two = !empty($ability_info['ability_type2']) ? $ability_info['ability_type2'] : false;
                                $ability_robot_rewards = array();
                                $ability_robot_rewards_level = array();
                                $ability_robot_rewards_core = array();
                                $ability_robot_rewards_player = array();

                                // Loop through and remove any robots that do not learn the ability
                                foreach ($mmrpg_database_robots AS $robot_token => $robot_info){

                                    // Define the match flah to prevent doubling up
                                    $temp_match_flag = false;

                                    // Loop through this robot's ability rewards one by one
                                    foreach ($robot_info['robot_rewards']['abilities'] AS $temp_info){
                                        // If the temp info's type token matches this ability
                                        if ($temp_info['token'] == $ability_info['ability_token']){
                                            // Add this ability to the rewards list
                                            $ability_robot_rewards_level[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => $temp_info['level']));
                                            $temp_match_flag = true;
                                            break;
                                        }
                                    }

                                    // If a type match was found, continue
                                    if ($temp_match_flag){ continue; }

                                    // If this ability's type matches the robot's first
                                    if (!empty($robot_info['robot_core']) && ($robot_info['robot_core'] == $ability_type_one || $robot_info['robot_core'] == $ability_type_two)){
                                        // Add this ability to the rewards list
                                        $ability_robot_rewards_core[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => 'core'));
                                        continue;
                                    }

                                    // If this ability's type matches the robot's second
                                    if (!empty($robot_info['robot_core2']) && ($robot_info['robot_core2'] == $ability_type_one || $robot_info['robot_core2'] == $ability_type_two)){
                                        // Add this ability to the rewards list
                                        $ability_robot_rewards_core[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => 'core'));
                                        continue;
                                    }

                                    // If a type match was found, continue
                                    if ($temp_match_flag){ continue; }

                                    // If this ability's in the robot's list of player-only abilities
                                    if (
                                        (!empty($robot_info['robot_abilities']) && in_array($ability_info['ability_token'], $robot_info['robot_abilities'])) ||
                                        (!empty($robot_info['robot_core']) && $robot_info['robot_core'] == 'copy') ||
                                        (!empty($robot_info['robot_core2']) && $robot_info['robot_core2'] == 'copy')
                                        ){
                                        // Add this ability to the rewards list
                                        $ability_robot_rewards_player[] = array_merge($robot_info, array('token' => $robot_info['robot_token'], 'level' => 'player'));
                                        continue;
                                    }

                                    // If a type match was found, continue
                                    if ($temp_match_flag){ continue; }

                                }

                                // Combine the arrays together into one
                                $ability_robot_rewards = array_merge($ability_robot_rewards_level, $ability_robot_rewards_core, $ability_robot_rewards_player);

                                // Loop through the collected robots if there are any
                                if (!empty($ability_robot_rewards)){
                                    $temp_string = array();
                                    $robot_key = 0;
                                    $robot_method_key = 0;
                                    $robot_method = '';
                                    $temp_global_abilities = array(
                                        'light-buster', 'wily-buster', 'cossack-buster',
                                        'energy-boost', 'attack-boost', 'defense-boost', 'speed-boost',
                                        'energy-break', 'attack-break', 'defense-break', 'speed-break',
                                        'energy-swap', 'attack-swap', 'defense-swap', 'speed-swap',
                                        'repair-mode', 'attack-mode', 'defense-mode', 'speed-mode',
                                        'field-support', 'mecha-support'
                                        );
                                    foreach ($ability_robot_rewards AS $this_info){
                                        $this_level = $this_info['level'];
                                        $this_robot = $mmrpg_database_robots[$this_info['token']];
                                        $this_robot_token = $this_robot['robot_token'];
                                        $this_robot_name = $this_robot['robot_name'];
                                        $this_robot_image = !empty($this_robot['robot_image']) ? $this_robot['robot_image']: $this_robot['robot_token'];
                                        $this_robot_energy = !empty($this_robot['robot_energy']) ? $this_robot['robot_energy'] : 0;
                                        $this_robot_attack = !empty($this_robot['robot_attack']) ? $this_robot['robot_attack'] : 0;
                                        $this_robot_defense = !empty($this_robot['robot_defense']) ? $this_robot['robot_defense'] : 0;
                                        $this_robot_speed = !empty($this_robot['robot_speed']) ? $this_robot['robot_speed'] : 0;
                                        $this_robot_method = 'level';
                                        $this_robot_method_text = 'Level Up';
                                        $this_robot_title_html = '<strong class="name">'.$this_robot_name.'</strong>';
                                        if (is_numeric($this_level)){
                                            if ($this_level > 1){ $this_robot_title_html .= '<span class="level">Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).'</span>'; }
                                            else { $this_robot_title_html .= '<span class="level">Start</span>'; }
                                        } else {
                                            if ($this_level == 'core'){
                                                $this_robot_method = 'core';
                                                $this_robot_method_text = 'Core Match';
                                            } elseif ($this_level == 'player'){
                                                $this_robot_method = 'player';
                                                $this_robot_method_text = 'Player Only';
                                            }
                                            $this_robot_title_html .= '<span class="level">&nbsp;</span>';
                                        }
                                        $this_stat_base_total = $this_robot_energy + $this_robot_attack + $this_robot_defense + $this_robot_speed;
                                        $this_stat_width_total = 84;
                                        if (!empty($this_robot['robot_core'])){ $this_robot_title_html .= '<span class="type">'.ucwords($this_robot['robot_core'].(!empty($this_robot['robot_core2']) ? ' / '.$this_robot['robot_core2'] : '')).' Core</span>'; }
                                        else { $this_robot_title_html .= '<span class="type">Neutral Core</span>'; }
                                        $this_robot_title_html .= '<span class="class">'.(!empty($this_robot['robot_description']) ? $this_robot['robot_description'] : '&hellip;').'</span>';
                                        if (!empty($this_robot_speed)){ $temp_speed_width = floor($this_stat_width_total * ($this_robot_speed / $this_stat_base_total)); }
                                        if (!empty($this_robot_defense)){ $temp_defense_width = floor($this_stat_width_total * ($this_robot_defense / $this_stat_base_total)); }
                                        if (!empty($this_robot_attack)){ $temp_attack_width = floor($this_stat_width_total * ($this_robot_attack / $this_stat_base_total)); }
                                        if (!empty($this_robot_energy)){ $temp_energy_width = $this_stat_width_total - ($temp_speed_width + $temp_defense_width + $temp_attack_width); }
                                        if (!empty($this_robot_energy)){ $this_robot_title_html .= '<span class="energy robot_type robot_type_energy" style="width: '.$temp_energy_width.'%;" title="'.$this_robot_energy.' Energy">'.$this_robot_energy.'</span>'; }
                                        if (!empty($this_robot_attack)){ $this_robot_title_html .= '<span class="attack robot_type robot_type_attack" style="width: '.$temp_attack_width.'%;" title="'.$this_robot_attack.' Attack">'.$this_robot_attack.'</span>'; }
                                        if (!empty($this_robot_defense)){ $this_robot_title_html .= '<span class="defense robot_type robot_type_defense" style="width: '.$temp_defense_width.'%;" title="'.$this_robot_defense.' Defense">'.$this_robot_defense.'</span>'; }
                                        if (!empty($this_robot_speed)){ $this_robot_title_html .= '<span class="speed robot_type robot_type_speed" style="width: '.$temp_speed_width.'%;" title="'.$this_robot_speed.' Speed">'.$this_robot_speed.'</span>'; }
                                        $this_robot_sprite_size = !empty($this_robot['robot_image_size']) ? $this_robot['robot_image_size'] : 40;
                                        $this_robot_sprite_path = 'images/robots/'.$this_robot_image.'/mug_left_'.$this_robot_sprite_size.'x'.$this_robot_sprite_size.'.png';
                                        if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_robot_sprite_path)){ $this_robot_image = 'robot'; $this_robot_sprite_path = 'images/robots/robot/mug_left_40x40.png'; }
                                        $this_robot_sprite_html = '<span class="mug"><img class="size_'.$this_robot_sprite_size.'x'.$this_robot_sprite_size.'" src="'.$this_robot_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE.'" alt="'.$this_robot_name.' Mug" /></span>';
                                        $this_robot_title_html = '<span class="label">'.$this_robot_title_html.'</span>';
                                        //$this_robot_title_html = (is_numeric($this_level) && $this_level > 1 ? 'Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).' : ' : $this_level.' : ').$this_robot_title_html;
                                        if ($robot_method != $this_robot_method){
                                            $temp_separator = '<div class="robot_separator">'.$this_robot_method_text.'</div>';
                                            $temp_string[] = $temp_separator;
                                            $robot_method = $this_robot_method;
                                            $robot_method_key++;
                                            // Print out the disclaimer if a global ability
                                            if ($this_robot_method == 'level' && $ability_info['ability_token'] == 'buster-shot'){
                                                $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">'.$ability_info['ability_name'].' is known by <em>all</em> robot masters from the start!</div>';
                                            } elseif ($this_robot_method != 'level' && in_array($ability_info['ability_token'], $temp_global_abilities)){
                                                $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">'.$ability_info['ability_name'].' can be equipped by <em>any</em> robot master!</div>';
                                            }
                                        }
                                        // If this is a global ability, don't bother showing EVERY compatible robot
                                        if ($this_robot_method == 'level' && $ability_info['ability_token'] == 'buster-shot' || $this_robot_method != 'level' && in_array($ability_info['ability_token'], $temp_global_abilities)){ continue; }
                                        if ($this_level >= 0){
                                            //title="'.$this_robot_title_plain.'"
                                            $temp_markup = '<a href="'.MMRPG_CONFIG_ROOTURL.'database/robots/'.$this_robot['robot_token'].'/"  class="robot_name robot_type robot_type_'.(!empty($this_robot['robot_core']) ? $this_robot['robot_core'].(!empty($this_robot['robot_core2']) ? '_'.$this_robot['robot_core2'] : '') : 'none').'" style="'.($this_robot_image == 'robot' ? 'opacity: 0.3; ' : '').'">';
                                            $temp_markup .= '<span class="chrome">'.$this_robot_sprite_html.$this_robot_title_html.'</span>';
                                            $temp_markup .= '</a>';
                                            $temp_string[] = $temp_markup;
                                            $robot_key++;
                                            continue;
                                        }
                                    }
                                    echo implode(' ', $temp_string);
                                } else {
                                    echo '<span class="robot_ability robot_type_none"><span class="chrome">Neutral</span></span>';
                                }
                                ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <? endif; ?>

        <? if ($print_options['show_records'] && $ability_info['ability_class'] == 'master'): ?>

          <h2 id="records" class="header header_full <?= $ability_header_types ?>" style="margin: 10px 0 0; text-align: left;">
            <?= $ability_info['ability_name'] ?>&#39;s Records
          </h2>
          <div class="body body_full" style="margin: 0 auto 5px; padding: 2px 0; min-height: 10px;">
            <?

            // Collect the database records for this ability
            global $DB;
            $temp_ability_records = array('ability_unlocked' => 0, 'ability_equipped');
            $temp_record_query = "SELECT
                COUNT(*) AS unlock_count,
                SUM(ROUND((
                LENGTH(saves.save_values_battle_settings)
                - LENGTH(REPLACE(saves.save_values_battle_settings, '\"{$ability_info['ability_token']}\"', ''))
                ) / LENGTH('\"{$ability_info['ability_token']}\"')
                )) AS equip_count
                FROM mmrpg_saves AS saves
                LEFT JOIN mmrpg_users AS users ON users.user_id = saves.user_id
                LEFT JOIN mmrpg_leaderboard AS points ON points.user_id = saves.user_id
                WHERE
                saves.save_values_battle_abilities LIKE '%\"{$ability_info['ability_token']}\"%'
                AND points.board_points <> 0
                AND users.user_id <> 0
                ;";
            $temp_record_values = $DB->get_array($temp_record_query);
            if (!empty($temp_record_values)){
                $temp_ability_records['ability_unlocked'] = $temp_record_values['unlock_count'];
                $temp_ability_records['ability_equipped'] = $temp_record_values['equip_count'];
            }

            ?>
            <table class="full" style="margin: 5px auto 10px;">
              <colgroup>
                <col width="100%" />
              </colgroup>
              <tbody>
                  <tr>
                    <td class="right">
                      <label style="display: block; float: left;">Unlocked By : </label>
                      <span class="ability_quote"><?= $temp_ability_records['ability_unlocked'] == 1 ? '1 Player' : number_format($temp_ability_records['ability_unlocked'], 0, '.', ',').' Players' ?></span>
                    </td>
                  </tr>
                  <tr>
                    <td class="right">
                      <label style="display: block; float: left;">Equipped To : </label>
                      <span class="ability_quote"><?= $temp_ability_records['ability_equipped'] == 1 ? '1 Robot' : number_format($temp_ability_records['ability_equipped'], 0, '.', ',').' Robots' ?></span>
                    </td>
                  </tr>
              </tbody>
            </table>
          </div>

        <? endif; ?>

        <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
            <a class="link link_permalink permalink" href="<?= preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $ability_info['ability_token']).'/' : 'database/abilities/'.$ability_info['ability_token'].'/' ?>" rel="permalink">+ Permalink</a>

        <? elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
            <a class="link link_permalink permalink" href="<?= preg_match('/^item-/', $ability_info['ability_token']) ? 'database/items/'.preg_replace('/^item-/i', '', $ability_info['ability_token']).'/' : 'database/abilities/'.$ability_info['ability_token'].'/' ?>" rel="permalink">+ View More</a>

        <? endif; ?>

    </div>
</div>
<?
// Collect the outbut buffer contents
$this_markup = trim(ob_get_clean());
?>