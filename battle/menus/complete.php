<?
// Generate the markup for the action switch panel
ob_start();
    // If the current robot is not disabled (WE WIN!)
    if ($this_player->counters['robots_active'] > 0
        && $this_battle->battle_result != 'defeat'){

        // Check to see if there are any battle complete redirects, otherwise print button normally
        if (!empty($this_battle->battle_complete_redirect_token)
            || !empty($this_battle->batle_complete_redirect_seed)){

            // Display available main actions
            ?><div class="main_actions"><?
                ?><a class="button action_ability" data-action="next" type="button" data-order="1"><label>Continue</label></a><?
            ?></div><?
            // Display the available sub options
            ?><div class="sub_actions"><?
                ?><a class="button action_scan button_disabled" type="button">&nbsp;</a><?
                ?><a class="button action_item button_disabled" type="button">&nbsp;</a><?
                ?><a class="button action_option button_disabled" type="button">&nbsp;</a><?
                ?><a class="button action_switch button_disabled" type="button">&nbsp;</a><?
            ?></div><?


        } else {

            // Display available main actions
            ?><div class="main_actions"><?
                ?><a class="button action_ability" data-action="prototype" type="button" data-order="1"><label>Mission Complete!</label></a><?
            ?></div><?
            // Display the available sub options
            ?><div class="sub_actions"><?

                // If this is a STAR FIELD mission, display customized menu options
                if (!empty($this_battle->flags['starfield_mission'])){

                    // Collect a list of available stars still left for the player to encounter
                    $temp_remaining_stars = mmrpg_prototype_remaining_stars(true);
                    $temp_remaining_stars_types = array();
                    if (!empty($temp_remaining_stars)){
                        foreach ($temp_remaining_stars AS $token => $details){
                            if (!empty($details['info1']['type'])){
                                $type1 = $details['info1']['type'];
                                if (!isset($temp_remaining_stars_types[$type1])){ $temp_remaining_stars_types[$type1] = 0; }
                                $temp_remaining_stars_types[$type1] += 1;
                            }
                            if (!empty($details['info2']['type'])){
                                $type2 = $details['info2']['type'];
                                if (!isset($temp_remaining_stars_types[$type2])){ $temp_remaining_stars_types[$type2] = 0; }
                                $temp_remaining_stars_types[$type2] += 1;
                            }
                        }
                    }

                    // Collect the first and second field type if applicable so we know which buttons to show
                    $star_type_one = !empty($this_battle->battle_field_base['field_type']) ? $this_battle->battle_field_base['field_type'] : false;
                    $star_type_two = !empty($this_battle->battle_field_base['field_type2']) ? $this_battle->battle_field_base['field_type2'] : false;
                    if (empty($temp_remaining_stars_types[$star_type_one])){ $star_type_one = false; }
                    if (empty($temp_remaining_stars_types[$star_type_two])){ $star_type_two = false; }

                    ?><a class="button action_scan button_disabled" type="button">&nbsp;</a><?
                    if (!empty($star_type_one)){ ?><a class="button action_item ability_type type_<?= $star_type_one ?>" data-action="next_<?= $star_type_one ?>-star" type="button" data-order="3"><label style="font-size: 80%;">Next <?= ucfirst($star_type_one) ?> Star</label></a><? }
                    else { ?><a class="button action_item button_disabled" type="button">&nbsp;</a><? }
                    if (!empty($star_type_two)){ ?><a class="button action_option ability_type type_<?= $star_type_two ?>" data-action="next_<?= $star_type_two ?>-star" type="button" data-order="3"><label style="font-size: 80%;">Next <?= ucfirst($star_type_two) ?> Star</label></a><? }
                    else { ?><a class="button action_option button_disabled" type="button">&nbsp;</a><? }
                    ?><a class="button action_switch button_disabled" type="button">&nbsp;</a><?

                }
                // Else if this is a PLAYER BATTLE mission, display customized menu options
                elseif (!empty($this_battle->flags['player_battle'])){
                    ?><a class="button action_scan button_disabled" type="button">&nbsp;</a><?
                    ?><a class="button action_item button_disabled" type="button">&nbsp;</a><?
                    ?><a class="button action_option button_disabled" type="button">&nbsp;</a><?
                    ?><a class="button action_switch button_disabled" type="button">&nbsp;</a><?
                }
                // Else if this is any other kind of mission, display the normal menu options
                else {
                    ?><a class="button action_scan button_disabled" type="button">&nbsp;</a><?
                    ?><a class="button action_item button_disabled" type="button">&nbsp;</a><?
                    ?><a class="button action_option button_disabled" type="button">&nbsp;</a><?
                    ?><a class="button action_switch button_disabled" type="button">&nbsp;</a><?
                }
            ?></div><?

        }

    }
    // Otherwise if this robot has been disabled (WE FAIL!)
    else {
        // If this is an ENDLESS ATTACK MODE battle, we show a slightly different end screen
        if (!empty($this_battle->flags['challenge_battle']) && !empty($this_battle->flags['endless_battle'])){
            // Display available main actions
            ?><div class="main_actions"><?
            ?><a class="button action_ability button_disabled" type="button"><label>Wave Failure&hellip;</label></a><?
            ?></div><?
            // Display the available sub options
            ?><div class="sub_actions"><?
            ?><a class="button action_scan button_disabled" type="button">&nbsp;</a><?
            ?><a class="button action_item" data-action="prototype" type="button" data-order="1"><label>Exit Mission</label></a><?
            ?><a class="button action_option" data-action="restart" type="button" data-order="2"><label>Retry Wave</label></a><?
            ?><a class="button action_switch button_disabled" type="button">&nbsp;</a><?
            ?></div><?
        }
        // Otherwise if this is any other kind of battle, we can display the default end screen
        else {
            // Display available main actions
            ?><div class="main_actions"><?
            ?><a class="button action_ability button_disabled" type="button"><label>Mission Failure&hellip;</label></a><?
            ?></div><?
            // Display the available sub options
            ?><div class="sub_actions"><?
            ?><a class="button action_scan button_disabled" type="button">&nbsp;</a><?
            ?><a class="button action_item" data-action="prototype" type="button" data-order="1"><label>Exit Mission</label></a><?
            ?><a class="button action_option" data-action="restart" type="button" data-order="2"><label>Retry Battle</label></a><?
            ?><a class="button action_switch button_disabled" type="button">&nbsp;</a><?
            ?></div><?
        }
    }
$actions_markup['battle'] = trim(ob_get_clean());
$actions_markup['battle'] = preg_replace('#\s+#', ' ', $actions_markup['battle']);
?>