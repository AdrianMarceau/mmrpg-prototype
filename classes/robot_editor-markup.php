<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url, $DB;
global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
global $key_counter, $player_counter, $player_rewards, $player_ability_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup;
global $mmrpg_database_abilities;
$session_token = mmrpg_game_token();

// If either fo empty, return error
if (empty($player_info)){ return 'error:player-empty'; }
if (empty($robot_info)){ return 'error:robot-empty'; }

// Collect the approriate database indexes
if (empty($mmrpg_database_abilities)){ $mmrpg_database_abilities = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token'); }

// Define the quick-access variables for later use
$player_token = $player_info['player_token'];
$robot_token = $robot_info['robot_token'];
if (!isset($first_robot_token)){ $first_robot_token = $robot_token; }

// Start the output buffer
ob_start();

    // Check how many robots this player has and see if they should be able to transfer
    $counter_player_robots = !empty($player_info['player_robots']) ? count($player_info['player_robots']) : false;
    $counter_player_missions = mmrpg_prototype_battles_complete($player_info['player_token']);
    $allow_player_selector = $player_counter > 1 && $counter_player_missions > 0 ? true : false;

    // If this player has fewer robots than any other player
    //$temp_flag_most_robots = true;
    foreach ($temp_robot_totals AS $temp_player => $temp_total){
        //if ($temp_player == $player_token){ continue; }
        //elseif ($temp_total > $counter_player_robots){ $allow_player_selector = false; }
    }

    // Update the robot key to the current counter
    $robot_key = $key_counter;
    // Make a backup of the player selector
    $allow_player_selector_backup = $allow_player_selector;
    // Collect or define the image size
    $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
    $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
    $robot_image_size_text = $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'];
    $robot_image_offset_top = -1 * $robot_image_offset;
    // Collect the robot level and experience
    $robot_info['robot_level'] = mmrpg_prototype_robot_level($player_info['player_token'], $robot_info['robot_token']);
    $robot_info['robot_experience'] = mmrpg_prototype_robot_experience($player_info['player_token'], $robot_info['robot_token']);
    // Collect the rewards for this robot
    $robot_rewards = mmrpg_prototype_robot_rewards($player_token, $robot_token);
    // Collect the settings for this robot
    $robot_settings = mmrpg_prototype_robot_settings($player_token, $robot_token);
    // Collect the database for this robot
    $robot_database = !empty($player_robot_database[$robot_token]) ? $player_robot_database[$robot_token] : array();
    // Collect the stat details for this robot
    $robot_stats = mmrpg_robot::calculate_stat_values($robot_info['robot_level'], $robot_info, $robot_rewards, true);
    // Collect the robot ability core if it exists
    $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
    // Check if this robot has the copy shot ability
    $robot_flag_copycore = $robot_ability_core == 'copy' ? true : false;

    // Loop through and update this robot's stats with calculated values
    $stat_tokens = array('energy', 'weapons', 'attack', 'defense', 'speed');
    foreach ($stat_tokens As $stat_token){
        // Update this robot's stat with the calculated current totals
        $robot_info['robot_'.$stat_token] = $robot_stats[$stat_token]['current'];
        $robot_info['robot_'.$stat_token.'_base'] = $robot_stats[$stat_token]['current_noboost'];
        $robot_info['robot_'.$stat_token.'_rewards'] = $robot_stats[$stat_token]['bonus'];
        if (!empty($player_info['player_'.$stat_token])){
            $robot_stats[$stat_token]['player'] = ceil($robot_info['robot_'.$stat_token] * ($player_info['player_'.$stat_token] / 100));
            $robot_info['robot_'.$stat_token.'_player'] = $robot_stats[$stat_token]['player'];
            $robot_info['robot_'.$stat_token] += $robot_stats[$stat_token]['player'];
        }
    }

    // Define a temp function for printing out robot stat blocks
    $print_robot_stat_function = function($stat_token) use($robot_info, $robot_stats, $player_info){

        $level_max = $robot_stats['level'] >= 100 ? true : false;
        $is_maxed = $robot_stats[$stat_token]['bonus'] >= $robot_stats[$stat_token]['bonus_max'] ? true : false;

        if ($stat_token == 'energy' || $stat_token == 'weapons'){ echo '<span class="robot_stat robot_type_'.$stat_token.'"> '; }
        elseif ($level_max && $is_maxed){ echo '<span class="robot_stat robot_type_'.$stat_token.'"> '; }
        else { echo '<span class="robot_stat"> '; }

            if ($stat_token != 'energy' && $stat_token != 'weapons'){
                echo $is_maxed ? ($level_max ? '<span>&#9733;</span> ' : '<span>&bull;</span> ') : '';
                echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;">';
                    $base_text = 'Base '.ucfirst($stat_token).' <br /> <span style="font-size: 90%">'.number_format($robot_stats[$stat_token]['base'], 0, '.', ',').' <span style="font-size: 90%">@</span>  Lv.'.$robot_stats['level'].' = '.number_format($robot_stats[$stat_token]['current_noboost'], 0, '.', ',').'</span>';
                    echo '<span data-tooltip="'.htmlentities($base_text, ENT_QUOTES, 'UTF-8', true).'" data-tooltip-type="robot_type robot_type_none">'.$robot_stats[$stat_token]['current_noboost'].'</span> ';
                    if (!empty($robot_stats[$stat_token]['bonus'])){
                        $robot_bonus_text = 'Robot Bonuses <br /> <span style="font-size: 90%">'.number_format($robot_stats[$stat_token]['bonus'], 0, '.', ',').' / '.number_format($robot_stats[$stat_token]['bonus_max'], 0, '.', ',').' Max</span>';
                        echo '+ <span data-tooltip="'.htmlentities($robot_bonus_text, ENT_QUOTES, 'UTF-8', true).'" class="statboost_robot" data-tooltip-type="robot_stat robot_type_shield">'.$robot_stats[$stat_token]['bonus'].'</span> ';
                    }
                    if (!empty($robot_stats[$stat_token]['player'])){
                        $player_bonus_text = 'Player Bonuses <br /> <span style="font-size: 90%">'.number_format(($robot_stats[$stat_token]['current']), 0, '.', ',').' x '.$player_info['player_'.$stat_token].'% = '.number_format($robot_stats[$stat_token]['player'], 0, '.', ',').'</span>';
                        echo '+ <span data-tooltip="'.htmlentities($player_bonus_text, ENT_QUOTES, 'UTF-8', true).'" class="statboost_player_'.$player_info['player_token'].'" data-tooltip-type="robot_stat robot_type_'.$stat_token.'">'.$robot_stats[$stat_token]['player'].'</span> ';
                    }
                echo ' = </span>';
                echo preg_replace('/^(0+)/', '<span style="color: rgba(255, 255, 255, 0.05); text-shadow: 0 0 0 transparent; ">$1</span>', str_pad($robot_info['robot_'.$stat_token], 4, '0', STR_PAD_LEFT));
            } else {
                echo $robot_info['robot_'.$stat_token];
            }

            if ($stat_token == 'energy'){ echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> LE</span>'; }
            elseif ($stat_token == 'weapons'){ echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> WE</span>'; }

        echo '</span>'."\n";
        };

    // Collect this robot's ability rewards and add them to the dropdown
    $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
    $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
    foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }

    // Collect the summon count from the session if it exists
    $robot_info['robot_summoned'] = !empty($robot_database['robot_summoned']) ? $robot_database['robot_summoned'] : 0;

    // Collect the alt images if there are any that are unlocked
    $robot_alt_count = 1 + (!empty($robot_info['robot_image_alts']) ? count($robot_info['robot_image_alts']) : 0);
    $robot_alt_options = array();
    if (!empty($robot_info['robot_image_alts'])){
        foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
            if ($robot_info['robot_summoned'] < $alt_info['summons']){ continue; }
            $robot_alt_options[] = $alt_info['token'];
        }
    }

    // Collect the current unlock image token for this robot
    $robot_image_unlock_current = 'base';
    if (!empty($robot_settings['robot_image']) && strstr($robot_settings['robot_image'], '_')){
        list($token, $robot_image_unlock_current) = explode('_', $robot_settings['robot_image']);
    }

    // Define the offsets for the image tokens based on count
    $token_first_offset = 2;
    $token_other_offset = 6;
    if ($robot_alt_count == 1){ $token_first_offset = 17; }
    elseif ($robot_alt_count == 3){ $token_first_offset = 10; }

    // Loop through and generate the robot image display token markup
    $robot_image_unlock_tokens = '';
    $temp_total_alts_count = 0;
    for ($i = 0; $i < 6; $i++){
        $temp_enabled = true;
        $temp_active = false;
        if ($i + 1 > $robot_alt_count){ break; }
        if ($i > 0 && !isset($robot_alt_options[$i - 1])){ $temp_enabled = false; }
        if ($temp_enabled && $i == 0 && $robot_image_unlock_current == 'base'){ $temp_active = true; }
        elseif ($temp_enabled && $i >= 1 && $robot_image_unlock_current == $robot_alt_options[$i - 1]){ $temp_active = true; }
        $robot_image_unlock_tokens .= '<span class="token token_'.($temp_enabled ? 'enabled' : 'disabled').' '.($temp_active ? 'token_active' : '').'" style="left: '.($token_first_offset + ($i * $token_other_offset)).'px;">&bull;</span>';
        $temp_total_alts_count += 1;
    }
    $temp_unlocked_alts_count = count($robot_alt_options) + 1;
    $temp_image_alt_title = '';
    if ($temp_total_alts_count > 1){
        $temp_image_alt_title = '<strong>'.$temp_unlocked_alts_count.' / '.$temp_total_alts_count.' Outfits Unlocked</strong><br />';
        //$temp_image_alt_title .= '<span style="font-size: 90%;">';
            $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$robot_info['robot_name'].'</span><br />';
            foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
                if ($robot_info['robot_summoned'] >= $alt_info['summons']){
                    $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$alt_info['name'].'</span><br />';
                } else {
                    $temp_image_alt_title .= '&#9702; <span style="font-size: 90%;">???</span><br />';
                }
            }
        //$temp_image_alt_title .= '</span>';
        $temp_image_alt_title = htmlentities($temp_image_alt_title, ENT_QUOTES, 'UTF-8', true);
    }

    // Define whether or not this robot has coreswap enabled
    $temp_allow_coreswap = $robot_info['robot_level'] >= 100 ? true : false;

    //echo $robot_info['robot_token'].' robot_image_unlock_current = '.$robot_image_unlock_current.' | robot_alt_options = '.implode(',',array_keys($robot_alt_options)).'<br />';

    ?>
    <div class="event event_double event_<?= $robot_key == $first_robot_token ? 'visible' : 'hidden' ?>" data-token="<?=$player_info['player_token'].'_'.$robot_info['robot_token']?>">

        <div class="this_sprite sprite_left event_robot_mugshot" style="">
            <?php $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
            <div class="sprite_wrapper robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="width: 33px;">
                <div class="sprite_wrapper robot_type robot_type_empty" style="position: absolute; width: 27px; height: 34px; left: 2px; top: 2px;"></div>
                <div style="left: <?= $temp_offset ?>; bottom: <?= $temp_offset ?>; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/mug_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_mug robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
            </div>
        </div>

        <div class="this_sprite sprite_left event_robot_images" style="">
            <?php if($global_allow_editing && !empty($robot_alt_options)): ?>
                <a class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                    <?php $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                    <span class="sprite_wrapper" style="">
                        <?= $robot_image_unlock_tokens ?>
                        <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                    </span>
                </a>
            <?php else: ?>
                <span class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                    <?php $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                    <span class="sprite_wrapper" style="">
                        <?= $robot_image_unlock_tokens ?>
                        <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                    </span>
                </span>
            <?php endif; ?>
        </div>

        <div class="this_sprite sprite_left event_robot_summons" style="">
            <div class="robot_summons">
                <span class="summons_count"><?= $robot_info['robot_summoned'] ?></span>
                <span class="summons_label"><?= $robot_info['robot_summoned'] == 1 ? 'Summon' : 'Summons' ?></span>
            </div>
        </div>

        <div class="this_sprite sprite_left event_robot_favourite" style="" >
            <?php if($global_allow_editing): ?>
                <a class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" title="Toggle Favourite?">&hearts;</a>
            <?php else: ?>
                <span class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>">&hearts;</span>
            <?php endif; ?>
        </div>

        <div class="header header_left robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="margin-right: 0;">
            <span class="title robot_type"><?= $robot_info['robot_name']?></span>
            <span class="core robot_type">
                <span class="wrap"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/abilities/item-core-<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/icon_left_40x40.png);"></span></span>
                <span class="text"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?> Core</span>
            </span>
        </div>

        <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">
            <table class="full" style="margin-bottom: 5px;">
                <colgroup>
                    <col width="64%" />
                    <col width="1%" />
                    <col width="35%" />
                </colgroup>
                <tbody>
                    <tr>
                        <td  class="right">
                            <label style="display: block; float: left;">Name :</label>
                            <span class="robot_name robot_type robot_type_none"><?=$robot_info['robot_name']?></span>
                        </td>
                        <td class="center">&nbsp;</td>
                        <td  class="right">
                            <label style="display: block; float: left;">Level :</label>
                            <? if($robot_info['robot_level'] >= 100): ?>
                                <a data-tooltip-align="center" data-tooltip="<?= htmlentities(('Congratulations! '.$robot_info['robot_name'].' has reached Level 100!<br /> <span style="font-size: 90%;">Stat bonuses will now be awarded immediately when this robot lands the finishing blow on a target! Try to max out your other stats!</span>'), ENT_QUOTES, 'UTF-8') ?>" class="robot_stat robot_type_electric"><span>&#9733;</span> <?= $robot_info['robot_level'] ?></a>
                            <? else: ?>
                                <span class="robot_stat robot_level_reset robot_type_<?= !empty($robot_rewards['flags']['reached_max_level']) ? 'electric' : 'none' ?>"><?= !empty($robot_rewards['flags']['reached_max_level']) ? '<span>&#9733;</span>' : '' ?> <?= $robot_info['robot_level'] ?></span>
                            <? endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="player_select_block right">
                            <?
                            $player_style = '';
                            $robot_info['original_player'] = !empty($robot_info['original_player']) ? $robot_info['original_player'] : $player_info['player_token'];
                            if ($player_info['player_token'] != $robot_info['original_player']){
                                if ($counter_player_robots > 1){ $allow_player_selector = true; }
                            }
                            ?>
                            <? if($robot_info['original_player'] != $player_info['player_token']): ?>
                                <label title="<?= 'Transferred from Dr. '.ucfirst(str_replace('dr-', '', $robot_info['original_player'])) ?>"  class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                            <? else: ?>
                                <label class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                            <? endif; ?>

                            <?if($global_allow_editing && $allow_player_selector):?>
                                <a title="Transfer Robot?" class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>"><label style="background-image: url(images/players/<?=$player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?=$player_info['player_name']?><span class="arrow">&#8711;</span></label><select class="player_name" <?= !$allow_player_selector ? 'disabled="disabled"' : '' ?> data-player="<?=$player_info['player_token']?>" data-robot="<?=$robot_info['robot_token']?>"><?= str_replace('value="'.$player_info['player_token'].'"', 'value="'.$player_info['player_token'].'" selected="selected"', $player_options_markup) ?></select></a>
                            <?elseif(!$global_allow_editing && $allow_player_selector):?>
                                <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="cursor: default; "><label style="background-image: url(images/players/<?=$player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); cursor: default; "><?=$player_info['player_name']?></label></a>
                            <?else:?>
                                <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="opacity: 0.5; filter: alpha(opacity=50); cursor: default;"><label style="background-image: url(images/players/<?=$player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?=$player_info['player_name']?></label><select class="player_name" disabled="disabled" data-player="<?=$player_info['player_token']?>" data-robot="<?=$robot_info['robot_token']?>"><?= str_replace('value="'.$player_info['player_token'].'"', 'value="'.$player_info['player_token'].'" selected="selected"', $player_options_markup) ?></select></a>
                            <?endif;?>
                        </td>
                        <td class="center">&nbsp;</td>
                        <td  class="right">
                            <label style="display: block; float: left;">Exp :</label>
                            <? if($robot_info['robot_level'] >= 100): ?>
                                <span class="robot_stat robot_type_experience" title="Max Experience!"><span>&#8734;</span> / 1000</span>
                            <? else: ?>
                                <span class="robot_stat"><?= $robot_info['robot_experience'] ?> / 1000</span>
                            <? endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td  class="right">
                            <label style="display: block; float: left;">Weaknesses :</label>
                            <?
                            if (!empty($robot_info['robot_weaknesses'])){
                                $temp_string = array();
                                foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                                    $temp_string[] = '<span class="robot_weakness robot_type robot_type_'.(!empty($robot_weakness) ? $robot_weakness : 'none').'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</span>';
                                }
                                echo implode(' ', $temp_string);
                            } else {
                                echo '<span class="robot_weakness">None</span>';
                            }
                            ?>
                        </td>
                        <td class="center">&nbsp;</td>
                        <td class="right">
                            <label class="<?= !empty($player_info['player_energy']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Energy :</label>
                            <?
                            // Print out the energy stat breakdown
                            $print_robot_stat_function('energy');
                            $print_robot_stat_function('weapons');
                            ?>
                        </td>

                    </tr>
                    <tr>
                        <td  class="right">
                            <label style="display: block; float: left;">Resistances :</label>
                            <?
                            if (!empty($robot_info['robot_resistances'])){
                                $temp_string = array();
                                foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                                    $temp_string[] = '<span class="robot_resistance robot_type robot_type_'.(!empty($robot_resistance) ? $robot_resistance : 'none').'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</span>';
                                }
                                echo implode(' ', $temp_string);
                            } else {
                                echo '<span class="robot_resistance">None</span>';
                            }
                            ?>
                        </td>
                        <td class="center">&nbsp;</td>
                        <td class="right">
                            <label class="<?= !empty($player_info['player_attack']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Attack :</label>
                            <?
                            // Print out the attack stat breakdown
                            $print_robot_stat_function('attack');
                            ?>
                    </tr>
                    <tr>
                        <td  class="right">
                            <label style="display: block; float: left;">Affinities :</label>
                            <?
                            if (!empty($robot_info['robot_affinities'])){
                                $temp_string = array();
                                foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                                    $temp_string[] = '<span class="robot_affinity robot_type robot_type_'.(!empty($robot_affinity) ? $robot_affinity : 'none').'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</span>';
                                }
                                echo implode(' ', $temp_string);
                            } else {
                                echo '<span class="robot_affinity">None</span>';
                            }
                            ?>
                        </td>
                        <td class="center">&nbsp;</td>
                        <td class="right">
                            <label class="<?= !empty($player_info['player_defense']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Defense :</label>
                            <?
                            // Print out the defense stat breakdown
                            $print_robot_stat_function('defense');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="right">
                            <label style="display: block; float: left;">Immunities :</label>
                            <?
                            if (!empty($robot_info['robot_immunities'])){
                                $temp_string = array();
                                foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                                    $temp_string[] = '<span class="robot_immunity robot_type robot_type_'.(!empty($robot_immunity) ? $robot_immunity : 'none').'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</span>';
                                }
                                echo implode(' ', $temp_string);
                            } else {
                                echo '<span class="robot_immunity">None</span>';
                            }
                            ?>
                        </td>
                        <td class="center">&nbsp;</td>
                        <td class="right">
                            <label class="<?= !empty($player_info['player_speed']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Speed :</label>
                            <?
                            // Print out the speed stat breakdown
                            $print_robot_stat_function('speed');
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="full">
                <colgroup>
                    <col width="100%" />
                </colgroup>
                <tbody>
                    <tr>
                        <td class="right" style="padding-top: 4px;">
                            <label style="display: block; float: left; font-size: 12px;">Abilities :</label>
                            <div class="ability_container" style="height: auto;">
                            <?

                            // Define the array to hold ALL the reward option markup
                            $ability_rewards_options = '';

                            // Sort the ability index based on ability number
                            uasort($player_ability_rewards, array('mmrpg_player', 'abilities_sort_for_editor'));

                            // Dont' bother generating option dropdowns if editing is disabled
                            if ($global_allow_editing){

                                $player_ability_rewards_options = array();
                                foreach ($player_ability_rewards AS $temp_ability_key => $temp_ability_info){
                                    if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
                                    $temp_token = $temp_ability_info['ability_token'];
                                    $temp_ability_info = rpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
                                    $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);
                                    if (!empty($temp_option_markup)){ $player_ability_rewards_options[] = $temp_option_markup; }
                                }
                                $player_ability_rewards_options = '<optgroup label="Player Abilities">'.implode('', $player_ability_rewards_options).'</optgroup>';
                                $ability_rewards_options .= $player_ability_rewards_options;

                                // Collect this robot's ability rewards and add them to the dropdown
                                $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
                                $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
                                foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }
                                if (!empty($robot_ability_rewards)){ sort($robot_ability_rewards); }
                                $robot_ability_rewards_options = array();
                                foreach ($robot_ability_rewards AS $temp_ability_info){
                                    if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
                                    $temp_token = $temp_ability_info['ability_token'];
                                    $temp_ability_info = rpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
                                    $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);
                                    if (!empty($temp_option_markup)){ $robot_ability_rewards_options[] = $temp_option_markup; }
                                }
                                $robot_ability_rewards_options = '<optgroup label="Robot Abilities">'.implode('', $robot_ability_rewards_options).'</optgroup>';
                                $ability_rewards_options .= $robot_ability_rewards_options;

                                // Add an option at the bottom to remove the ability
                                $ability_rewards_options .= '<optgroup label="Ability Actions">';
                                $ability_rewards_options .= '<option value="" title="">- Remove Ability -</option>';
                                $ability_rewards_options .= '</optgroup>';

                            }

                            // Loop through the robot's current abilities and list them one by one
                            $empty_ability_counter = 0;
                            if (!empty($robot_info['robot_abilities'])){
                                $temp_string = array();
                                $temp_inputs = array();
                                $ability_key = 0;

                                // DEBUG
                                //echo 'robot-ability:';
                                foreach ($robot_info['robot_abilities'] AS $robot_ability){
                                    if (empty($robot_ability['ability_token'])){ continue; }
                                    elseif ($robot_ability['ability_token'] == '*'){ continue; }
                                    elseif ($robot_ability['ability_token'] == 'ability'){ continue; }
                                    elseif (!isset($mmrpg_database_abilities[$robot_ability['ability_token']])){ continue; }
                                    elseif ($ability_key > 7){ continue; }
                                    $this_ability = rpg_ability::parse_index_info($mmrpg_database_abilities[$robot_ability['ability_token']]);
                                    if (empty($this_ability)){ continue; }
                                    $this_ability_token = $this_ability['ability_token'];
                                    $this_ability_name = $this_ability['ability_name'];
                                    $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                                    $this_ability_type2 = !empty($this_ability['ability_type2']) ? $this_ability['ability_type2'] : false;
                                    if (!empty($this_ability_type) && !empty($mmrpg_index['types'][$this_ability_type])){
                                        $this_ability_type = $mmrpg_index['types'][$this_ability_type]['type_name'].' Type';
                                        if (!empty($this_ability_type2) && !empty($mmrpg_index['types'][$this_ability_type2])){
                                            $this_ability_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$this_ability_type2]['type_name'].' Type', $this_ability_type);
                                        }
                                    } else {
                                        $this_ability_type = '';
                                    }
                                    $this_ability_energy = isset($this_ability['ability_energy']) ? $this_ability['ability_energy'] : 4;
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
                                    $this_ability_title = rpg_ability::print_editor_title_markup($robot_info, $this_ability);
                                    $this_ability_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_ability_title));
                                    $this_ability_title_tooltip = htmlentities($this_ability_title, ENT_QUOTES, 'UTF-8');
                                    $this_ability_title_html = str_replace(' ', '&nbsp;', $this_ability_name);
                                    $temp_select_options = str_replace('value="'.$this_ability_token.'"', 'value="'.$this_ability_token.'" selected="selected" disabled="disabled"', $ability_rewards_options);
                                    $this_ability_title_html = '<label style="background-image: url(images/abilities/'.$this_ability_token.'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_ability_title_html.'</label>';
                                    if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'">'.$temp_select_options.'</select>'; }
                                    $temp_string[] = '<a class="ability_name ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').(!empty($this_ability['ability_type2']) ? '_'.$this_ability['ability_type2'] : '').'" style="'.(($ability_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="'.$this_ability_token.'" title="'.$this_ability_title_plain.'" data-tooltip="'.$this_ability_title_tooltip.'">'.$this_ability_title_html.'</a>';
                                    $ability_key++;
                                }

                                if ($ability_key <= 7){
                                    for ($ability_key; $ability_key <= 7; $ability_key++){
                                        $empty_ability_counter++;
                                        if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                                        else { $empty_ability_disable = false; }
                                        $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $ability_rewards_options);
                                        $this_ability_title_html = '<label>-</label>';
                                        if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                        $temp_string[] = '<a class="ability_name " style="'.(($ability_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="" title="" data-tooltip="">'.$this_ability_title_html.'</a>';
                                    }
                                }


                            } else {

                                for ($ability_key = 0; $ability_key <= 7; $ability_key++){
                                    $empty_ability_counter++;
                                    if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                                    else { $empty_ability_disable = false; }
                                    $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $ability_rewards_options);
                                    $this_ability_title_html = '<label>-</label>';
                                    if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                    $temp_string[] = '<a class="ability_name " style="'.(($ability_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="">'.$this_ability_title_html.'</a>';
                                }

                            }

                            echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                            echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';

                            ?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?
    $key_counter++;

    // Return the backup of the player selector
    $allow_player_selector = $allow_player_selector_backup;




// Collect the outbut buffer contents
$this_markup = trim(ob_get_clean());
?>