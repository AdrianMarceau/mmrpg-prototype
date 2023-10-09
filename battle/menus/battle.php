<?

// Generate the markup for the action battle panel
ob_start();

    // Define the flag for whether items have been unlocked
    if (mmrpg_prototype_item_unlocked('item-codes') && mmrpg_prototype_items_unlocked() > 0){ $items_unlocked = true; }
    elseif (rpg_game::is_demo()){ $items_unlocked = true; }
    else { $items_unlocked = false; }

    // Decide whether or not we should show the STAR SUPPORT button in the menu
    $show_star_support = false;
    $star_support_cooldown = 9999;
    $num_robots_active = $this_player->counters['robots_active'];
    if ($num_robots_active < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX
        && empty($this_battle->flags['challenge_battle'])
        && empty($this_battle->flags['player_battle'])
        && empty($this_player->flags['star_support_summoned'])
        && rpg_prototype::star_support_unlocked()){
        $show_star_support = true;
        $star_support_force = rpg_prototype::get_star_support_force();
        if (!empty($this_battle->flags['star_support_required'])){
            $show_star_support = true;
            $star_support_cooldown = 0;
        } elseif (isset($this_battle->flags['star_support_allowed']) && empty($this_battle->flags['star_support_allowed'])){
            $show_star_support = false;
            $star_support_cooldown = 100;
        } else {
            $show_star_support = true;
            $star_support_cooldown = rpg_prototype::get_star_support_cooldown();
        }
    }

    // Check to see whether or not switching is allowed right now
    $switch_allowed = true;
    //if ($this_battle->counters['battle_turn'] < 1){ $switch_allowed = false; }

    // If the current robot is not disabled and is active
    if ($this_robot->robot_energy > 0 && $this_robot->robot_position == 'active'){

        // Define the order counter and start at one
        $dataOrder = 1;

        // Display available main actions
        ?><div class="main_actions"><?
            if (!empty($temp_player_ability_actions) || $this_robot->robot_class !== 'master'){
                $icon = 'fire-alt';
                $class = 'button action_ability';
                if ($show_star_support && empty($star_support_cooldown)){ $icon = 'star'; $class .= ' type space'; }
                ?><a class="<?= $class ?>" type="button" data-panel="ability" data-order="<?= $dataOrder ?>"><label><i class="fa fas fa-<?= $icon ?>"></i> <strong>Ability</strong></label></a><?
            } else {
                ?><a class="button button_disabled action_ability" type="button" data-action="ability_8_action-noweapons" data-order="<?= $dataOrder ?>"><label><i class="fa fas fa-battery-empty"></i> <strong style="text-decoration: line-through;">Ability</strong></label></a><?
            } $dataOrder++;
        ?></div><?

        // Display the available sub options
        ?><div class="sub_actions" data-size="<?= $items_unlocked && $switch_allowed ? 4 : 3 ?>"><?

            // Display the SWITCH option
            if ($switch_allowed){
                $temp_disabled = false;
                ?><a class="button action_switch <?= $temp_disabled ? 'button_disabled' : '' ?>" type="button" <?= !$temp_disabled ? 'data-panel="switch"' : '' ?> <?= !$temp_disabled ? 'data-order="'.$dataOrder.'"' : '' ?>><label><i class="fa fas fa-sync-alt"></i> <strong>Switch</strong></label></a><?
                $dataOrder++;
            }

            // Display the ITEM option if it's been unlocked
            if ($items_unlocked){
                $temp_disabled = false;
                ?><a class="button action_item <?= $temp_disabled ? 'button_disabled' : '' ?>" type="button" <?= !$temp_disabled ? 'data-panel="item"' : '' ?> <?= !$temp_disabled ? 'data-order="'.$dataOrder.'"' : '' ?>><label><i class="fa fas fa-briefcase"></i> <strong>Item</strong></label></a><?
                $dataOrder++;
            }

            // Display the OPTION option
            ?><a class="button action_option" type="button" data-panel="option" data-order="<?= $dataOrder ?>"><label><i class="fa fas fa-cog"></i> <strong>Option</strong></label></a><?
            $dataOrder++;

            // Display the SCAN option
            if ($target_player->counters['robots_active'] > 1){
                ?><a class="button action_scan" type="button" <?= $target_player->counters['robots_active'] > 1 ? 'data-panel="scan"' : 'data-action="scan_'.$target_robot->robot_id.'_'.$target_robot->robot_token.'"' ?> data-order="<?= $dataOrder ?>"><label><i class="fa fas fa-crosshairs"></i> <strong>Scan</strong></label></a><?
            } else {
                foreach ($target_player->values['robots_active'] AS $key => $info){
                    if ($info['robot_position'] != 'active'){ continue; }
                    ?><a class="button action_scan" type="button" data-action="scan_<?= $info['robot_id'].'_'.$info['robot_token'] ?>" data-order="<?= $dataOrder ?>"><label><i class="fa fas fa-crosshairs"></i> <strong>Scan</strong></label></a><?
                    break;
                }
            }
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
            ?><a class="button action_switch" type="button" data-panel="switch" data-order="2"><label>Switch</label></a><?
            if ($items_unlocked){ ?><a class="button action_item button_disabled" type="button"><label>Item</label></a><? }
            ?><a class="button action_option" type="button" data-panel="option" data-order="1"><label>Option</label></a><?
            ?><a class="button action_scan button_disabled" type="button"><label>Scan</label></a><?
        ?></div><?
    }

$actions_markup['battle'] = trim(ob_get_clean());
$actions_markup['battle'] = preg_replace('#\s+#', ' ', $actions_markup['battle']);

?>