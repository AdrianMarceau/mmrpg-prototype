<?
// Define the global variables
global $mmrpg_index, $this_current_uri, $this_current_url, $DB;
global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
global $key_counter, $player_counter, $player_rewards, $player_ability_rewards, $player_robot_favourites, $temp_robot_totals, $player_options_markup;
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
    // Collect the stat details for this robot
    $robot_stats = mmrpg_robot::calculate_stat_values($robot_info['robot_level'], $robot_info, $robot_rewards, true);
    // Collect the robot ability core if it exists
    $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
    // Check if this robot has the copy shot ability
    $robot_flag_copycore = $robot_ability_core == 'copy' ? true : false;

    // Loop through and update this robot's stats with calculated values
    $stat_tokens = array('energy', 'attack', 'defense', 'speed');
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
        $is_maxed = $robot_stats['level'] >= 100 && $robot_stats[$stat_token]['bonus'] >= $robot_stats[$stat_token]['bonus_max'] ? true : false;
        if ($is_maxed){ echo '<span class="robot_stat robot_type_'.$stat_token.'"><span title="Max '.ucfirst($stat_token).'!">&#9733;</span> '; }
        else { echo '<span class="robot_stat">'; }
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
        echo '</span>'."\n";
        };

    ?>
    <div class="event event_double event_<?= $robot_key == $first_robot_token ? 'visible' : 'hidden' ?>" data-token="<?=$player_info['player_token'].'_'.$robot_info['robot_token']?>">
        <div class="this_sprite sprite_left" style="height: 40px;">
            <? $temp_margin = -1 * ceil(($robot_info['robot_image_size'] - 40) * 0.5); ?>
            <div style="margin-top: <?= $temp_margin ?>px; margin-bottom: <?= $temp_margin * 3 ?>px; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/mug_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_mug robot_status_active robot_position_active"><?=$robot_info['robot_name']?></div>
        </div>
        <div class="this_sprite sprite_left" style="margin-top: 48px; height: 14px;">
            <? if($global_allow_editing): ?>
                <a data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>" href="#">&hearts;</a>
            <? else: ?>
                <span class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>">&hearts;</a>
            <? endif; ?>
        </div>
        <div class="header header_left robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="margin-right: 0;"><?=$robot_info['robot_name']?>&#39;s Data <span class="robot_type"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?> Core</span></div>
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
                                <span class="robot_stat robot_type_experience" title="Max Experience!"><span>&#9733;</span> <span>&#8734;</span> / 1000</span>
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
                            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $ability_rewards_options = '.htmlentities($ability_rewards_options, ENT_QUOTES, 'UTF-8', true));  }

                            // Collect this player's ability rewards and add them to the dropdown
                            //$player_ability_rewards = !empty($player_rewards['player_abilities']) ? $player_rewards['player_abilities'] : array();
                            //if (!empty($player_ability_rewards)){ sort($player_ability_rewards); }

                            // DEBUG
                            //$debug_tokens = array();
                            //foreach ($player_ability_rewards AS $info){ $debug_tokens[] = $info['ability_token']; }
                            //echo 'before:'.implode(',', array_keys($debug_tokens)).'<br />';

                            // Sort the ability index based on ability number
                            uasort($player_ability_rewards, array('mmrpg_player', 'abilities_sort_for_editor'));

                            // DEBUG
                            //echo 'after:'.implode(',', array_keys($player_ability_rewards)).'<br />';

                            // DEBUG
                            //$debug_tokens = array();
                            //foreach ($player_ability_rewards AS $info){ $debug_tokens[] = $info['ability_token']; }
                            //echo 'after:'.implode(',', $debug_tokens).'<br />';

                            // Dont' bother generating option dropdowns if editing is disabled
                            if ($global_allow_editing){

                                $player_ability_rewards_options = array();
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $player_ability_rewards = '.implode(',', array_keys($player_ability_rewards)));  }
                                foreach ($player_ability_rewards AS $temp_ability_key => $temp_ability_info){
                                    if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
                                    $temp_token = $temp_ability_info['ability_token'];
                                    $temp_ability_info = mmrpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
                                    $temp_option_markup = mmrpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);
                                    if (!empty($temp_option_markup)){ $player_ability_rewards_options[] = $temp_option_markup; }
                                }
                                $player_ability_rewards_options = '<optgroup label="Player Abilities">'.implode('', $player_ability_rewards_options).'</optgroup>';
                                $ability_rewards_options .= $player_ability_rewards_options;
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $ability_rewards_options = '.htmlentities($ability_rewards_options, ENT_QUOTES, 'UTF-8', true));  }

                                // Collect this robot's ability rewards and add them to the dropdown
                                $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
                                $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
                                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $robot_ability_rewards = '.implode(',', array_keys($robot_ability_rewards)));  }
                                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $robot_ability_settings = '.implode(',', array_keys($robot_ability_settings)));  }
                                foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }
                                if (!empty($robot_ability_rewards)){ sort($robot_ability_rewards); }
                                $robot_ability_rewards_options = array();
                                foreach ($robot_ability_rewards AS $temp_ability_info){
                                    if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
                                    $temp_token = $temp_ability_info['ability_token'];
                                    $temp_ability_info = mmrpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
                                    $temp_option_markup = mmrpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);
                                    if (!empty($temp_option_markup)){ $robot_ability_rewards_options[] = $temp_option_markup; }
                                }
                                $robot_ability_rewards_options = '<optgroup label="Robot Abilities">'.implode('', $robot_ability_rewards_options).'</optgroup>';
                                $ability_rewards_options .= $robot_ability_rewards_options;
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $ability_rewards_options = '.htmlentities($ability_rewards_options, ENT_QUOTES, 'UTF-8', true));  }

                                // Add an option at the bottom to remove the ability
                                $ability_rewards_options .= '<optgroup label="Ability Actions">';
                                $ability_rewards_options .= '<option value="" title="">- Remove Ability -</option>';
                                $ability_rewards_options .= '</optgroup>';
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'player_token:'.$player_info['player_token'].' | robot_token:'.$robot_info['robot_token'].' | $ability_rewards_options = '.htmlentities($ability_rewards_options, ENT_QUOTES, 'UTF-8', true));  }

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
                                    elseif ($ability_key > 7){ continue; }
                                    $this_ability = mmrpg_ability::parse_index_info($mmrpg_database_abilities[$robot_ability['ability_token']]);
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
                                    $this_ability_title = mmrpg_ability::print_editor_title_markup($robot_info, $this_ability);
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
                            // DEBUG
                            //echo 'temp-string:';
                            echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                            // DEBUG
                            //echo '<br />temp-inputs:';
                            echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';
                            // DEBUG
                            //echo '<br />';



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