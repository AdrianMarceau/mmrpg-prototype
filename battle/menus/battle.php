<?

// Generate the markup for the action battle panel
ob_start();

    // Define the flag for whether items have been unlocked
    if (mmrpg_prototype_item_unlocked('item-codes') && mmrpg_prototype_items_unlocked() > 0){ $items_unlocked = true; }
    elseif (rpg_game::is_demo()){ $items_unlocked = true; }
    else { $items_unlocked = false; }

    // If the current robot is not disabled and is active
    if ($this_robot->robot_energy > 0 && $this_robot->robot_position == 'active'){

        // Define the order counter and start at one
        $dataOrder = 1;

        // Display available main actions
        ?><div class="main_actions"><?
            if (!empty($temp_player_ability_actions) || $this_robot->robot_class == 'mecha'){
                ?><a class="button action_ability" type="button" data-panel="ability" data-order="<?= $dataOrder ?>"><label>Ability</label></a><?
            } else {
                ?><a class="button button_disabled action_ability" type="button" data-action="ability_8_action-noweapons" data-order="<?= $dataOrder ?>"><label style="text-decoration: line-through;">Ability</label></a><?
            } $dataOrder++;
        ?></div><?

        // Display the available sub options
        ?><div class="sub_actions" data-size="<?= $items_unlocked ? 4 : 3 ?>"><?

            // Display the SCAN option
            if ($target_player->counters['robots_active'] > 1){
                ?><a class="button action_scan" type="button" <?= $target_player->counters['robots_active'] > 1 ? 'data-panel="scan"' : 'data-action="scan_'.$target_robot->robot_id.'_'.$target_robot->robot_token.'"' ?> data-order="<?= $dataOrder ?>"><label>Scan</label></a><?
            } else {
                foreach ($target_player->values['robots_active'] AS $key => $info){
                    if ($info['robot_position'] != 'active'){ continue; }
                    ?><a class="button action_scan" type="button" data-action="scan_<?= $info['robot_id'].'_'.$info['robot_token'] ?>" data-order="<?= $dataOrder ?>"><label>Scan</label></a><?
                    break;
                }
            }
            $dataOrder++;

            // Display the ITEM option if it's been unlocked
            if ($items_unlocked){
                $temp_disabled = false;
                ?><a class="button action_item <?= $temp_disabled ? 'button_disabled' : '' ?>" type="button" <?= !$temp_disabled ? 'data-panel="item"' : '' ?> <?= !$temp_disabled ? 'data-order="'.$dataOrder.'"' : '' ?>><label>Item</label></a><?
                $dataOrder++;
            }

            // Display the OPTION option
            ?><a class="button action_option" type="button" data-panel="option" data-order="<?= $dataOrder ?>"><label>Option</label></a><?
            $dataOrder++;

            // Display the SWITCH option
            ?><a class="button action_switch" type="button" data-panel="switch" data-order="<?= $dataOrder ?>"><label>Switch</label></a><?
            $dataOrder++;

        ?></div><?
    }
    // Otherwise if this robot has been disabled
    else {
        // Display available main actions
        ?><div class="main_actions"><?
            ?><a class="button action_ability button_disabled" type="button"><label>Ability</label></a><?
        ?></div><?
        // Display the available sub options
        ?><div class="sub_actions" data-size="<?= $items_unlocked ? 4 : 3 ?>"><?
            ?><a class="button action_scan button_disabled" type="button"><label>Scan</label></a><?
            if ($items_unlocked){ ?><a class="button action_item button_disabled" type="button"><label>Item</label></a><? }
            ?><a class="button action_option" type="button" data-panel="option" data-order="1"><label>Option</label></a><?
            ?><a class="button action_switch" type="button" data-panel="switch" data-order="2"><label>Switch</label></a><?
        ?></div><?
    }

$actions_markup['battle'] = trim(ob_get_clean());
$actions_markup['battle'] = preg_replace('#\s+#', ' ', $actions_markup['battle']);

?>