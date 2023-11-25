<?
// Generate the markup for the action ability panel
ob_start();

    // Define and start the order counter
    $temp_order_counter = 1;

    // Decide whether or not we should show the STAR SUPPORT button in the menu
    $show_star_support = false;
    $star_support_cooldown = rpg_prototype::get_star_support_cooldown_max();
    $num_robots_active = $this_player->counters['robots_active'];
    if ($num_robots_active < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX
        && empty($this_battle->flags['challenge_battle'])
        && empty($this_battle->flags['player_battle'])
        && empty($this_player->flags['star_support_summoned'])
        && rpg_prototype::star_support_unlocked()){
        $star_support_force = rpg_prototype::get_star_support_force();
        if (!empty($this_battle->flags['star_support_required'])){
            $show_star_support = true;
            $star_support_cooldown = 0;
            $star_support_charge = 100;
        } elseif (isset($this_battle->flags['star_support_allowed']) && empty($this_battle->flags['star_support_allowed'])){
            $show_star_support = false;
            $star_support_cooldown = 100;
            $star_support_charge = 0;
        } else {
            $show_star_support = true;
            $star_support_cooldown = rpg_prototype::get_star_support_cooldown();
            $star_support_charge = rpg_prototype::get_star_support_charge();
        }
    }

    // Pre-collect the bulwark robots from the players to see if the bench is protected
    $temp_thisplayer_bulwark_robots = $this_player->get_value('bulwark_robots');
    $temp_targetplayer_bulwark_robots = $target_player->get_value('bulwark_robots');

    // Display container for the main actions
    echo('<div class="main_actions main_actions_hastitle">');

        // Display the actual title for the abilities submenu
        echo('<span class="main_actions_title">');
            echo('<span class="float_title">Select Ability</span>');
            if ($show_star_support){
                echo '<span class="float_links">';
                    if (empty($star_support_cooldown)){
                        $text = 'Duo';
                        if (!empty($this_battle->flags['star_support_is_new'])){ $text .= '?'; }
                        elseif ($this_battle->has_endgame_context()){ $text .= '!'; }
                        $turn = !empty($this_battle->counters['battle_turn']) ? $this_battle->counters['battle_turn'] : 0;
                        $power = min((1 + $turn), 6);
                        echo '<a class="button star-support type space" data-action="ability_13_star-support" data-power="'.$power.'">';
                            echo '<span class="label">'.$text.'</span>';
                            echo '<i class="fx fx1"></i>';
                            echo '<i class="fx fx2"></i>';
                            echo '<i class="fx fx3"></i>';
                            echo str_repeat('<i class="star fa fas fa-star"></i>', $power);
                        echo '</a>';
                    } else {
                        $num_cooldown_pips = ceil($star_support_cooldown / 10);
                        $num_charge_pips = ceil($star_support_charge / 10);
                        $num_force_pips = ceil($star_support_force / 10);
                        $cooldown_pips_text = str_repeat('.', $num_cooldown_pips);
                        $charge_pips_text = str_repeat('.', $num_charge_pips);
                        $force_pips_text = str_repeat('.', $num_force_pips);
                        echo '<a class="button button_disabled star-support type empty">';
                            echo '<span class="cooldown">'.$cooldown_pips_text.'</span>';
                            echo '<span class="charge">'.$charge_pips_text.'</span>';
                            echo '<span class="force">'.$force_pips_text.'</span>';
                            echo '<i class="star fa fas fa-star-half-alt"></i>';
                        echo '</a>';
                    }
                echo '</span>';
            }
        echo('</span>');


    // Collect the abilities for this actual unlocked user robot, by whatever means
    if ($this_robot->robot_class === 'master'){

        $this_robot_settings = rpg_game::robot_settings($this_player->player_token, $this_robot->robot_token);

        if (!empty($this_robot_settings['robot_abilities'])){ $current_robot_abilities = $this_robot_settings['robot_abilities']; }
        //elseif (!empty($this_robot->robot_abilities)){ $current_robot_abilities = $this_robot->robot_abilities; }
        else { $current_robot_abilities = array(); }

        // If this robot has more than eight abilities, slice to only eight
        if (count($current_robot_abilities) > 8){
            $current_robot_abilities = array_slice($current_robot_abilities, 0, 8);
            $_SESSION['GAME']['values']['battle_settings'][$this_player->player_token]['player_robots'][$this_robot->robot_token]['robot_abilities'] = $current_robot_abilities;
        }

        // Collect the robot's held item if any
        //if (!empty($_SESSION['GAME']['values']['battle_settings'][$this_player->player_token]['player_robots'][$this_robot->robot_token]['robot_item'])){ $current_robot_item = $_SESSION['GAME']['values']['battle_settings'][$this_player->player_token]['player_robots'][$this_robot->robot_token]['robot_item']; }
        //else { $current_robot_item = ''; }

    } elseif ($this_robot->robot_class !== 'master'){

        // Collect the temp ability index
        $current_robot_abilities = array();
        foreach ($this_robot->robot_abilities AS $token){
            $current_robot_abilities[$token] = array('ability_token' => $token);
        }

        // Set the robot item to nothing
        //$current_robot_item = '';

    }

    // Collect the robot's held item if any
    $current_robot_item = $this_robot->robot_item;

    // Ensure this robot has abilities to display
    if (!empty($current_robot_abilities)){

        // Count the total number of abilities
        $num_abilities = count($this_robot->robot_abilities);
        $robot_direction = $this_player->player_side == 'left' ? 'right' : 'left';

        // Define the ability display counter
        $equipped_abilities_count = 0;

        // Collect the temp ability and robot indexes
        $mmrpg_robots_index = rpg_robot::get_index(true);
        $mmrpg_abilities_index = rpg_ability::get_index(true);
        $temp_robotinfo = $mmrpg_robots_index[$this_robot->robot_token];
        $temp_robotinfo['robot_core'] = $this_robot->robot_core;
        $temp_robotinfo['robot_core2'] = $this_robot->robot_core2;

        // Loop through each ability and display its button
        $ability_key = 0;
        //$temp_robot_array = $this_robot->export_array();
        foreach ($current_robot_abilities AS $ability_token => $ability_info){
            // Ensure this is an actual ability in the index
            if (!empty($ability_token)){

                // If this ability is invalid, continue
                if (!isset($mmrpg_abilities_index[$ability_token])){ continue; }

                // Check if this ability has been unlocked
                $this_ability_unlocked = true;
                if ($this_ability_unlocked){ $equipped_abilities_count++; }
                else { continue; }
                $block_num = $equipped_abilities_count > 8 ? $equipped_abilities_count % 8 : $equipped_abilities_count;

                // Create the ability object using the session/index data
                $temp_abilityinfo = $mmrpg_abilities_index[$ability_token];
                $temp_abilityinfo['ability_id'] = $this_robot->robot_id.str_pad($temp_abilityinfo['ability_id'], 3, '0', STR_PAD_LEFT);
                $temp_ability = rpg_game::get_ability($this_battle, $this_player, $this_robot, $temp_abilityinfo);
                $temp_ability->trigger_onload(true);
                $temp_type = $temp_ability->ability_type;
                $temp_type2 = $temp_ability->ability_type2;
                $temp_type_or_none = !empty($temp_type) ? $temp_type : 'none';
                if ($temp_type_or_none === 'none' && !empty($temp_type2)){ $temp_type_or_none = $temp_type2; }
                $temp_damage = $temp_ability->ability_damage;
                $temp_damage2 = $temp_ability->ability_damage2;
                $temp_damage_unit = $temp_ability->ability_damage_percent ? '%' : '';
                $temp_damage2_unit = $temp_ability->ability_damage2_percent ? '%' : '';
                $temp_recovery = $temp_ability->ability_recovery;
                $temp_recovery2 = $temp_ability->ability_recovery2;
                $temp_recovery_unit = $temp_ability->ability_recovery_percent ? '%' : '';
                $temp_recovery2_unit = $temp_ability->ability_recovery2_percent ? '%' : '';
                $temp_accuracy = $temp_ability->ability_accuracy;
                $temp_kind = !empty($temp_damage) && empty($temp_recovery) ? 'damage' : (!empty($temp_recovery) && empty($temp_damage) ? 'recovery' : (!empty($temp_damage) && !empty($temp_recovery) ? 'multi' : ''));
                $temp_target = 'auto';
                $temp_target_text = '';
                $temp_target_target = $temp_ability->ability_target;
                if ($temp_target_target == 'select_target' && !empty($temp_targetplayer_bulwark_robots)){ $temp_target_target = 'auto'; }
                elseif ($temp_target_target == 'select_target' && $target_player->counters['robots_active'] === 1){ $temp_target_target = 'auto'; }
                if ($temp_target_target == 'select_target'){ $temp_target = 'select_target'; $temp_target_text = 'Select Target'; }
                elseif ($temp_target_target == 'select_this'){ $temp_target = 'select_this'; $temp_target_text = 'Select Target'; }
                elseif ($temp_target_target == 'select_this_disabled'){ $temp_target = 'select_this_disabled'; $temp_target_text = 'Select Target'; }
                elseif ($temp_target_target == 'select_this_ally'){ $temp_target = 'select_this_ally'; $temp_target_text = 'Select Target'; }

                $temp_multiplier = 1;
                if (!empty($temp_damage) || !empty($temp_recovery)){

                    // Collect this ability's type tokens if they exist
                    $ability_type_token = !empty($temp_ability->ability_type) ? $temp_ability->ability_type : 'none';
                    $ability_type_token2 = !empty($temp_ability->ability_type2) ? $temp_ability->ability_type2 : '';

                    // Collect this robot's core type tokens if they exist
                    $core_type_token = !empty($this_robot->robot_core) ? $this_robot->robot_core : 'none';
                    $core_type_token2 = !empty($this_robot->robot_core2) ? $this_robot->robot_core2 : '';

                    // Collect this robot's held robot core if it exists
                    $core_type_token3 = '';
                    if (!empty($this_robot->robot_item) && strstr($this_robot->robot_item, '-core')){
                        $core_type_token3 = str_replace('-core', '', $this_robot->robot_item);
                    }

                    // Collect this omega core type tokens if they exist
                    $omega_type_token = !empty($this_robot->robot_omega) ? $this_robot->robot_omega : '';
                    $omega_type_token2 = !empty($this_robot->robot_omega2) ? $this_robot->robot_omega2 : '';

                    // Check this ability's FIRST type for multiplier matches
                    if (!empty($ability_type_token)){

                        // Apply primary robot core multipliers if they exist
                        if ($ability_type_token == $core_type_token){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_COREBOOST_MULTIPLIER; }
                        // Apply secondary robot core multipliers if they exist
                        elseif ($ability_type_token == $core_type_token2){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_COREBOOST_MULTIPLIER; }

                        // Apply held robot core multipliers if they exist
                        if ($ability_type_token == $core_type_token3){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_SUBCOREBOOST_MULTIPLIER; }

                        // Apply robot omega core multipliers if they exist
                        if ($ability_type_token == $omega_type_token){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_OMEGACOREBOOST_MULTIPLIER; }
                        // Apply player omega core multipliers if they exist
                        if ($ability_type_token == $omega_type_token2){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_OMEGACOREBOOST_MULTIPLIER; }

                        // Apply any field multiplier matches if they exist
                        if (!empty($this_battle->battle_field->field_multipliers[$ability_type_token])){
                            $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers[$ability_type_token];
                        }

                    }

                    // Check this ability's SECOND type for multiplier matches
                    if (!empty($ability_type_token2)){

                        // Apply primary robot core multipliers if they exist
                        if ($ability_type_token2 == $core_type_token){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_COREBOOST_MULTIPLIER; }
                        // Apply secondary robot core multipliers if they exist
                        elseif ($ability_type_token2 == $core_type_token2){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_COREBOOST_MULTIPLIER; }

                        // Apply held robot core multipliers if they exist
                        if ($ability_type_token2 == $core_type_token3){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_SUBCOREBOOST_MULTIPLIER; }

                        // Apply robot omega core multipliers if they exist
                        if ($ability_type_token2 == $omega_type_token){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_OMEGACOREBOOST_MULTIPLIER; }
                        // Apply player omega core multipliers if they exist
                        if ($ability_type_token2 == $omega_type_token2){ $temp_multiplier = $temp_multiplier * MMRPG_SETTINGS_OMEGACOREBOOST_MULTIPLIER; }

                        // Apply any field multiplier matches if they exist
                        if (!empty($this_battle->battle_field->field_multipliers[$ability_type_token2])){
                            $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers[$ability_type_token2];
                        }

                    }

                    // Apply any overall damage multipliers if applicable
                    $temp_damage = ceil($temp_damage * $temp_multiplier);
                    if (!preg_match('/-(booster|breaker)$/i', $ability_token) && !empty($this_battle->battle_field->field_multipliers['damage'])){ $temp_damage = ceil($temp_damage * $this_battle->battle_field->field_multipliers['damage']); }

                    // Apply any overall recovery multipliers if applicable
                    $temp_recovery = ceil($temp_recovery * $temp_multiplier);
                    if (!preg_match('/-(booster|breaker)$/i', $ability_token) && !empty($this_battle->battle_field->field_multipliers['recovery'])){ $temp_recovery = ceil($temp_recovery * $this_battle->battle_field->field_multipliers['recovery']); }

                }

                // Define the amount of weapon energy for this ability
                $temp_robot_weapons = $this_robot->robot_weapons;
                $temp_ability_energy = $this_robot->calculate_weapon_energy($temp_ability, $temp_ability_energy_base, $temp_ability_energy_mods);
                $temp_ability_speed = $temp_ability->ability_speed;

                // Define the ability title details text
                $temp_ability_details = $temp_ability->ability_name;
                $temp_ability_details .= ' ('.(!empty($temp_ability->ability_type) ? $battle_types_index[$temp_ability->ability_type]['type_name'] : 'Neutral');
                if (!empty($temp_ability->ability_type2)){ $temp_ability_details .= ' / '.$battle_types_index[$temp_ability->ability_type2]['type_name']; }
                else { $temp_ability_details .= ' Type'; }
                $temp_ability_details .= ') <br />';
                if ($temp_kind == 'damage' && !empty($temp_damage)){ $temp_ability_details .= $temp_damage.$temp_damage_unit.' Damage'; }
                elseif ($temp_kind == 'recovery' && !empty($temp_recovery)){ $temp_ability_details .= $temp_recovery.$temp_recovery_unit.' Recovery'; }
                elseif ($temp_kind == 'multi' && (!empty($temp_damage) || !empty($temp_recovery))){ $temp_ability_details .= $temp_damage.$temp_damage_unit.' Damage / '.$temp_recovery.$temp_recovery_unit.' Recovery'; }
                else { $temp_ability_details .= 'Support'; }
                $temp_ability_details .= ' | '.$temp_ability->ability_accuracy.'% Accuracy';
                if (!empty($temp_ability_energy)){ $temp_ability_details .= ' | '.$temp_ability_energy.' Energy'; }
                if (!empty($temp_target_text)){ $temp_ability_details .= ' | '.$temp_target_text; }

                if (!empty($temp_ability_speed) && $temp_ability_speed !== 1){
                    if ($temp_ability_speed > 1){ $temp_ability_details .= ' | Fast <sup>(+'.($temp_ability_speed - 1).')'; }
                    elseif ($temp_ability_speed < 1){ $temp_ability_details .= ' | Slow <sup>('.($temp_ability_speed + 1).')'; }
                }

                $temp_ability_description = $temp_ability->get_parsed_description();
                $temp_ability_details .= ' <br />'.$temp_ability_description;
                $temp_ability_details_plain = strip_tags(str_replace('<br />', '&#10;', $temp_ability_details));
                $temp_ability_details_tooltip = htmlentities($temp_ability_details, ENT_QUOTES, 'UTF-8');

                //$temp_ability_details .= ' | x'.$temp_multiplier.' '.$this_robot->robot_core.' '.count($this_battle->battle_field->field_multipliers);

                // Define a quick function for printing a big digit given damage/recovery values
                $get_big_digit_markup = function($temp_kind, $temp_type, $temp_damage, $temp_damage_unit, $temp_recovery, $temp_recovery_unit, $temp_multiplier = 1, $temp_times = 1){
                        $temp_big_digit_markup = '';
                        if (!empty($temp_damage) || !empty($temp_recovery)){
                            $temp_big_digits = '';
                                if (($temp_kind == 'multi' || $temp_kind == 'damage') & !empty($temp_damage)){
                                    $temp_big_digits .= '<span class="big-digit">';
                                        $amount = $temp_damage.($temp_damage_unit ? '<sup>'.$temp_damage_unit.'</sup>' : '');
                                        if ($temp_type === 'energy'){ $icon = '<i class="fa fas fa-fist-raised"></i>'; }
                                        elseif ($temp_type === 'weapons'){ $icon = '<i class="fa fas fa-battery-half"></i>'; }
                                        elseif (in_array($temp_type, array('attack', 'defense', 'speed'))) { { $icon = '<i class="fa fas fa-caret-square-down"></i>'; } }
                                        else { { $icon = '<i class="fa fas fa-fist-raised"></i>'; } }
                                        $mods = '';
                                        if ($temp_multiplier > 1){ $mods .= '<i class="fa fas fa-angle-double-up"></i>'; }
                                        elseif ($temp_multiplier < 1){ $mods .= '<i class="fa fas fa-angle-double-down"></i>'; }
                                        if ($temp_times > 1){ $mods .= ' <i class="fa fas fa-times"></i> '.$temp_times; }
                                        $temp_big_digits .= '<span class="amount damage">'.$icon.' '.$amount.$mods.'</span>';
                                    $temp_big_digits .= '</span>';
                                }
                                if (($temp_kind == 'multi' || $temp_kind == 'recovery') && !empty($temp_recovery)){
                                    $temp_big_digits .= '<span class="big-digit">';
                                        $amount = $temp_recovery.($temp_recovery_unit ? '<sup>'.$temp_recovery_unit.'</sup>' : '');
                                        if ($temp_type === 'energy'){ $icon = '<i class="fa fas fa-heart"></i>'; }
                                        elseif ($temp_type === 'weapons'){ $icon = '<i class="fa fas fa-battery-full"></i>'; }
                                        elseif (in_array($temp_type, array('attack', 'defense', 'speed'))) { { $icon = '<i class="fa fas fa-caret-square-up"></i>'; } }
                                        else { { $icon = '<i class="fa fas fa-heart"></i>'; } }
                                        $mods = '';
                                        if ($temp_multiplier > 1){ $mods .= '<i class="fa fas fa-angle-double-up"></i>'; }
                                        elseif ($temp_multiplier < 1){ $mods .= '<i class="fa fas fa-angle-double-down"></i>'; }
                                        if ($temp_times > 1){ $mods .= ' <i class="fa fas fa-times"></i> '.$temp_times; }
                                        $temp_big_digits .= '<span class="amount recovery">'.$icon.' '.$amount.$mods.'</span>';
                                    $temp_big_digits .= '</span>';
                                }
                            $temp_big_digit_markup .= $temp_big_digits;
                        }
                        return $temp_big_digit_markup;
                    };

                // Check to see if this ability falls into any preestablished categories
                $flag_is_boost_ability = preg_match('/^(energy|attack|defense|speed)-(boost|support)$/i', $ability_token) ? true : false;
                $flag_is_break_ability = preg_match('/^(energy|attack|defense|speed)-(break|assault)$/i', $ability_token) ? true : false;
                $flag_is_stat_ability = preg_match('/^(attack|defense|speed)-(boost|break|support|assault|mode|swap)$/i', $ability_token) ? true : false;
                $flag_is_multi_stat_ability = preg_match('/^(attack|defense|speed)-(support|assault)$/i', $ability_token) ? true : false;

                // debug debug debug
                //$flag_string = ($flag_is_boost_ability ? 'boost ' : '').($flag_is_break_ability ? 'break ' : '').($flag_is_stat_ability ? 'stat ' : '');
                //error_log('Ability: '.$temp_ability->ability_name.' ('.$flag_string.')');

                // Define the ability button text variables
                $temp_ability_label = '<span class="multi">';
                    $temp_ability_label .= '<span class="maintext">'.$temp_ability->ability_name.'</span>';
                    $temp_ability_label .= '<span class="subtext">';
                        $temp_ability_label .= (!empty($temp_type) ? $battle_types_index[$temp_ability->ability_type]['type_name'].' ' : 'Neutral ');
                        if (!empty($temp_type2)){ $temp_ability_label .= ' / '.$battle_types_index[$temp_ability->ability_type2]['type_name']; }
                        else { $temp_ability_label .= ($temp_kind == 'damage' ? 'Damage' : ($temp_kind == 'recovery' ? 'Recovery' : ($temp_kind == 'multi' ? 'Effects' : 'Special'))); }
                    $temp_ability_label .= '</span>';
                    $temp_ability_label .= '<span class="subtext">';
                        $icon = strstr($temp_target_target, 'select_') ? 'crosshairs' : 'bullseye';
                        $temp_ability_label .= '<span class="accuracy"><i class="fa fas fa-'.$icon.'"></i> '.$temp_accuracy.'%</span> ';
                        if ($flag_is_stat_ability && ($flag_is_boost_ability || $flag_is_break_ability)){
                            // If this is a stat ability, show the stat change from the hidden value
                            $temp_stat_kind = $flag_is_boost_ability ? 'recovery' : 'damage';
                            $temp_stat_type = preg_replace('/^(attack|defense|speed)-(boost|break|support|assault|mode|swap)$/i', '$1', $ability_token);
                            $temp_stat_times = 1;
                            if ($flag_is_multi_stat_ability){ $temp_stat_times = $flag_is_boost_ability ? $this_player->counters['robots_active'] : $target_player->counters['robots_active']; }
                            $temp_ability_label .= $get_big_digit_markup($temp_stat_kind, $temp_stat_type, $temp_damage2, $temp_damage2_unit, $temp_recovery2, $temp_recovery2_unit, $temp_multiplier, $temp_stat_times);

                            // debug debug debug
                            //error_log('Stat Ability: '.$temp_ability->ability_name.' ('.print_r($temp_ability->export_array(), true).')');
                            //error_log('get_big_digit_markup('.$temp_stat_kind.', '.$temp_stat_type.', '.$temp_damage2.', '.$temp_damage2_unit.', '.$temp_recovery2.', '.$temp_recovery2_unit.', '.$temp_multiplier.')');

                        } elseif (!empty($temp_type2) && (!empty($temp_damage2) || !empty($temp_recovery2))){
                            // If this is a multi-ability, show both damage/recovery values
                            $temp_ability_label .= $get_big_digit_markup($temp_kind, $temp_type, $temp_damage, $temp_damage_unit, $temp_recovery, $temp_recovery_unit, $temp_multiplier);
                            $temp_ability_label .= $get_big_digit_markup($temp_kind, $temp_type2, $temp_damage2, $temp_damage2_unit, $temp_recovery2, $temp_recovery2_unit, $temp_multiplier);
                        } else {
                            $temp_ability_label .= $get_big_digit_markup($temp_kind, $temp_type, $temp_damage, $temp_damage_unit, $temp_recovery, $temp_recovery_unit, $temp_multiplier);
                        }
                    $temp_ability_label .= '</span>';
                $temp_ability_label .= '</span>';

                // Define whether or not this ability button should be enabled
                $allow_button = $temp_robot_weapons >= $temp_ability_energy ? true : false;

                // If the ability is not actually compatible with this robot, disable it
                //$temp_robot_array = $this_robot->export_array();
                //error_log('$temp_abilityinfo = '.print_r($temp_abilityinfo, true));
                //error_log($this_robot->robot_token.' // $this_robot->robot_persona = '.print_r($this_robot->robot_persona, true));
                //error_log($this_robot->robot_token.' // $ability_token = '.print_r($ability_token, true));
                $temp_ability_array = $temp_ability->export_array();
                $temp_button_compatible = rpg_robot::has_ability_compatibility($temp_robotinfo, $temp_abilityinfo, $current_robot_item);
                if (!empty($this_robot->robot_persona) && $ability_token === 'copy-style'){ $temp_button_compatible = true; }
                if (!$temp_button_compatible){ $allow_button = false; }

                // If this button is enabled, add it to the global ability options array
                if ($allow_button){ $temp_player_ability_actions[] = $temp_ability->ability_token; }

                // Define the ability sprite variables
                $temp_ability_sprite = array();
                $temp_ability_sprite['name'] = $temp_ability->ability_name;
                if ($temp_ability->ability_class == 'master'){

                    $temp_ability_sprite['image'] = $temp_ability->ability_image;
                    $temp_ability_sprite['image_size'] = $temp_ability->ability_image_size;
                    $temp_ability_sprite['image_size_text'] = $temp_ability_sprite['image_size'].'x'.$temp_ability_sprite['image_size'];
                    $temp_ability_sprite['image_size_zoom'] = $temp_ability->ability_image_size * 2;
                    $temp_ability_sprite['image_size_zoom_text'] = $temp_ability_sprite['image_size'].'x'.$temp_ability_sprite['image_size'];
                    $temp_ability_sprite['url'] = 'images/abilities/'.$temp_ability_sprite['image'].'/icon_'.$robot_direction.'_'.$temp_ability_sprite['image_size_text'].'.png';
                    $temp_ability_sprite['class'] = 'sprite sprite_'.$temp_ability_sprite['image_size_text'].' sprite_'.$temp_ability_sprite['image_size_text'].'_base ';
                    $temp_ability_sprite['style'] = 'background-image: url('.$temp_ability_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.'); ';
                    $temp_ability_sprite['markup'] = '<span class="'.$temp_ability_sprite['class'].' sprite_40x40_ability" style="'.$temp_ability_sprite['style'].'"></span>';
                    if (!empty($temp_ability->ability_image2)){ $temp_ability_sprite['markup'] .= '<span class="'.$temp_ability_sprite['class'].' sprite2 sprite_40x40_ability" style="'.str_replace('/'.$temp_ability->ability_image.'/', '/'.$temp_ability->ability_image2.'/', $temp_ability_sprite['style']).'"></span>'; }
                    $temp_ability_sprite['markup'] .= '<span class="'.$temp_ability_sprite['class'].' sprite_40x40_cost" style="'.($temp_ability_energy == $temp_ability_energy_base ? '' : ($temp_ability_energy_mods <= 1 ? 'color: #80A280; ' : 'color: #68B968; ')).'">'.$temp_ability_energy.' WE</span>';


                } elseif ($temp_ability->ability_class == 'mecha'){

                    $temp_ability_sprite['image'] = $this_robot->robot_image;
                    $temp_ability_sprite['image_size'] = $this_robot->robot_image_size;
                    $temp_ability_sprite['image_size_text'] = $temp_ability_sprite['image_size'].'x'.$temp_ability_sprite['image_size'];
                    $temp_ability_sprite['image_size_zoom'] = $this_robot->robot_image_size * 2;
                    $temp_ability_sprite['image_size_zoom_text'] = $temp_ability_sprite['image_size'].'x'.$temp_ability_sprite['image_size'];
                    $temp_ability_sprite['url'] = 'images/robots/'.$temp_ability_sprite['image'].'/mug_'.$robot_direction.'_'.$temp_ability_sprite['image_size_text'].'.png';
                    $temp_ability_sprite['class'] = 'sprite sprite_'.$temp_ability_sprite['image_size_text'].' sprite_'.$temp_ability_sprite['image_size_text'].'_base ';
                    $temp_ability_sprite['style'] = 'background-image: url('.$temp_ability_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.'); top: 7px; left: 5px; height: 43px; background-position: center center !important; background-size: auto !important; ';
                    $temp_ability_sprite['markup'] = '<span class="'.$temp_ability_sprite['class'].' sprite_40x40_ability" style="'.$temp_ability_sprite['style'].'">'.$temp_ability_sprite['name'].'</span>';
                    if (!empty($temp_ability->ability_image2)){ $temp_ability_sprite['markup'] .= '<span class="'.$temp_ability_sprite['class'].' sprite2  sprite_40x40_ability" style="'.str_replace('/'.$temp_ability->ability_image.'/', '/'.$temp_ability->ability_image2.'/', $temp_ability_sprite['style']).'"></span>'; }
                    if ($temp_ability_energy > 0){ $temp_ability_sprite['markup'] .= '<span class="'.$temp_ability_sprite['class'].' sprite_40x40_cost" style="'.($temp_ability_energy == $temp_ability_energy_base ? '' : ($temp_ability_energy_mods <= 1 ? 'color: #80A280; ' : 'color: #68B968; ')).'">'.$temp_ability_energy.' WE</span>'; }

                }

                $temp_ability_sprite['preload'] = 'images/abilities/'.$temp_ability_sprite['image'].'/sprite_'.$robot_direction.'_'.$temp_ability_sprite['image_size_zoom_text'].'.png';

                // Update the order button if necessary
                $order_button_markup = $allow_button ? 'data-order="'.$temp_order_counter.'"' : '';
                $temp_order_counter += $allow_button ? 1 : 0;

                // Now use the new object to generate a snapshot of this ability button
                $btn_type = 'ability_type ability_type_'.(!empty($temp_ability->ability_type) ? $temp_ability->ability_type : 'none').(!empty($temp_ability->ability_type2) ? '_'.$temp_ability->ability_type2 : '');
                $btn_class = 'button action_ability ability_'.$temp_ability->ability_token.' '.$btn_type.' block_'.$block_num.' ';
                $btn_action = 'ability_'.$temp_ability->ability_id.'_'.$temp_ability->ability_token;

                $btn_info_circle = '<span class="info color" data-click-tooltip="'.$temp_ability_details_tooltip.'" data-tooltip-type="'.$btn_type.'">';
                    $btn_info_circle .= '<i class="fa fas fa-info-circle color '.$temp_type_or_none.'"></i>';
                    //if (!empty($temp_type2)){ $btn_info_circle .= '<i class="fa fas fa-info-circle color '.$temp_type2.'"></i>'; }
                $btn_info_circle .= '</span>';

                if ($allow_button){
                    echo('<a type="button" class="'.$btn_class.'" data-action="'.$btn_action.'" data-target="'.$temp_target.'" '.$order_button_markup.'>'.
                            '<label>'.
                                $btn_info_circle.
                                $temp_ability_sprite['markup'].
                                $temp_ability_label.
                            '</label>'.
                        '</a>');
                } else {
                    $btn_class .= 'button_disabled ';
                    echo('<a type="button" class="'.$btn_class.'">'.
                            '<label>'.
                                $btn_info_circle.
                                $temp_ability_sprite['markup'].
                                $temp_ability_label.
                            '</label>'.
                        '</a>');
                }

            }
            $ability_key++;
        }

        // If there were less than 8 abilities, fill in the empty spaces
        if ($equipped_abilities_count < 8){
            for ($i = $equipped_abilities_count; $i < 8; $i++){
                // Display an empty button placeholder
                $btn_class = 'button action_ability button_disabled block_'.($i + 1);
                echo('<a type="button" class="'.$btn_class.'">&nbsp;</a>');
            }
        }

    }

    // End the main action container tag
    //echo 'Abilities : ['.print_r($this_robot->robot_abilities, true).']';
    //echo preg_replace('#\s+#', ' ', print_r($this_robot_settings, true));
    echo('</div>');

    // Display the back button by default
    ?><div class="sub_actions"><a data-order="<?=$temp_order_counter?>" class="button action_back" type="button" data-panel="battle"><label>Back</label></a></div><?

    // Increment the order counter
    $temp_order_counter++;

$actions_markup['ability'] = trim(ob_get_clean());
$actions_markup['ability'] = preg_replace('#\s+#', ' ', $actions_markup['ability']);
?>