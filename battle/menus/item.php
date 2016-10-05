<?

// Define all the available player items in a handy index array
$current_player_items = !empty($_SESSION['GAME']['values']['battle_items']) ? $_SESSION['GAME']['values']['battle_items'] : array();

// Require the item database page for sorting purposes
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require(MMRPG_CONFIG_ROOTDIR.'database/items.php');

// Filter out items that should not be shown
foreach ($current_player_items AS $token => $quantity){
    if (!isset($mmrpg_database_items[$token])
        || $mmrpg_database_items[$token]['item_subclass'] != 'consumable'){
        unset($current_player_items[$token]);
    }
}

// Count the number of items the player has and determine pages
$current_player_items_count = count($current_player_items);
$current_player_items_pages = ceil($current_player_items_count / 8);

// Generate the markup for the action item panel
ob_start();

    // Collect this and the target player's starforce levels
    $target_starforce = $target_player->player_starforce;
    $this_starforce = $this_player->player_starforce;

    // Define and start the order counter
    $temp_order_counter = 1;

    // Display container for the main actions
    ?>
    <div class="main_actions main_actions_hastitle">
        <span class="main_actions_title <?= $target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID || !empty($this_player->flags['item_used_this_turn']) ? 'main_actions_title_disabled' : '' ?>">
            <?
            // If there were more than eight items, print the page numbers
            if ($current_player_items_count > 8){
                $temp_selected_page = 1; //!empty($_SESSION['GAME']['battle_settings']['action_item_page_num']) ? $_SESSION['GAME']['battle_settings']['action_item_page_num'] : 1;
                echo '<span class="float_title">Select Item</span>';
                echo '<span class="float_links"><span class="page">Page</span>';
                for ($i = 1; $i <= $current_player_items_pages; $i++){ echo '<a class="num'.($i == $temp_selected_page ? ' active' : '').'" href="#'.$i.'">'.$i.'</a>'; }
                echo '</span>';
            }
            // Otherwise, simply print the item select text label
            else {
                echo 'Select Item';
            }
            ?>
        </span>
        <?

        // Ensure this player has items to display
        if (!empty($current_player_items)){

            // Count the total number of items
            $item_direction = $this_player->player_side == 'left' ? 'right' : 'left';
            // Define the item display counter
            $equipped_items_count = 0;

            // Define the default button enabled option
            //if (!empty($_SESSION['GAME']['DEMO']) || $target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID || !isset($_SESSION['GAME']['values']['battle_items'])){ $temp_button_enabled_base = false; }
            if ($target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID){ $temp_button_enabled_base = false; }
            else { $temp_button_enabled_base = true; }
            // If this player has already used an item this turn
            if (!empty($this_player->flags['item_used_this_turn'])){ $temp_button_enabled_base = false; }

            // Loop through each item and display its button
            $item_key = 0;
            $item_token_list = array_keys($mmrpg_database_items);
            foreach ($item_token_list AS $item_key => $item_token){

                // If this item is not in the player list, skip
                if (!isset($current_player_items[$item_token])){ continue; }

                // Create the item object using the session/index data
                $temp_iteminfo = $mmrpg_database_items[$item_token];

                // Define the amount of weapon energy for this item
                $temp_item_quantity = !empty($_SESSION['GAME']['values']['battle_items'][$item_token]) ? $_SESSION['GAME']['values']['battle_items'][$item_token] : 0;
                $temp_item_cost = 1;

                // If this player has never aquired this item, do not display it
                if (!isset($_SESSION['GAME']['values']['battle_items'][$item_token])){ continue; }
                //$temp_item_quantity = 99; // CHANGEME! COMMENT ME OUT!

                // Increment the equipped items count
                $equipped_items_count++;

                // Define the button enabled flag
                $temp_button_enabled = $temp_button_enabled_base;

                //$temp_iteminfo = rpg_item::parse_index_info($temp_iteminfo);
                //$temp_iteminfo['item_id'] = $this_player->player_id.str_pad($temp_iteminfo['item_id'], 3, '0', STR_PAD_LEFT);
                $temp_item = rpg_game::get_item($this_battle, $this_player, $this_robot, $temp_iteminfo);
                $temp_type = $temp_item->item_type;
                $temp_type2 = $temp_item->item_type2;
                $temp_damage = $temp_item->item_damage;
                $temp_damage2 = $temp_item->item_damage2;
                $temp_damage_unit = $temp_item->item_damage_percent ? '%' : '';
                $temp_damage2_unit = $temp_item->item_damage2_percent ? '%' : '';
                $temp_recovery = $temp_item->item_recovery;
                $temp_recovery2 = $temp_item->item_recovery2;
                $temp_recovery_unit = $temp_item->item_recovery_percent ? '%' : '';
                $temp_recovery2_unit = $temp_item->item_recovery2_percent ? '%' : '';
                $temp_accuracy = $temp_item->item_accuracy;
                $temp_kind = !empty($temp_damage) && empty($temp_recovery) ? 'damage' : (!empty($temp_recovery) && empty($temp_damage) ? 'recovery' : (!empty($temp_damage) && !empty($temp_recovery) ? 'multi' : ''));
                $temp_target = 'select_this';
                if ($temp_item->item_target == 'select_target' && $target_player->counters['robots_active'] >= 1){ $temp_target = 'select_target'; }
                elseif ($temp_item->item_target == 'select_this' && $this_player->counters['robots_active'] >= 1){ $temp_target = 'select_this'; }
                elseif ($temp_item->item_target == 'select_this_disabled'){ $temp_target = 'select_this_disabled'; }
                elseif ($temp_item->item_target == 'auto'){ $temp_target = 'auto'; }
                //elseif ($temp_item->item_target == 'select_this_disabled' && $this_player->counters['robots_disabled'] >= 1){ $temp_target = 'select_this_disabled'; }
                //elseif ($temp_item->item_target == 'select_this_disabled' && $this_player->counters['robots_disabled'] < 1){ $temp_button_enabled = false; }
                //$temp_target = $target_player->counters['robots_active'] > 1 ? $temp_item->item_target : 'auto';

                $temp_multiplier = 1;
                if (!empty($this_robot->robot_core) && ($this_robot->robot_core == $temp_type || $this_robot->robot_core == $temp_type2)){ $temp_multiplier = $temp_multiplier * 1.5; }
                if (!empty($this_battle->battle_field->field_multipliers[$temp_type])){ $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers[$temp_type]; }
                if (!empty($this_battle->battle_field->field_multipliers[$temp_type2])){ $temp_multiplier = $temp_multiplier * $this_battle->battle_field->field_multipliers[$temp_type2]; }

                $temp_damage = ceil($temp_damage * $temp_multiplier);
                $temp_recovery = ceil($temp_recovery * $temp_multiplier);

                // Define the item title details text
                $temp_item_details = $temp_item->item_name.' <br />';
                //$temp_item_details .= ' ('.(!empty($temp_item->item_type) ? $mmrpg_index['types'][$temp_item->item_type]['type_name'] : 'Neutral');
                //if (!empty($temp_item->item_type2)){ $temp_item_details .= ' / '.$mmrpg_index['types'][$temp_item->item_type2]['type_name']; }
                //else { $temp_item_details .= ' Type'; }
                //$temp_item_details .= ') <br />';
                if ($temp_kind == 'damage'){ $temp_item_details .= $temp_damage.$temp_damage_unit.' Damage'; }
                elseif ($temp_kind == 'recovery'){ $temp_item_details .= $temp_recovery.$temp_recovery_unit.' Recovery'; }
                elseif ($temp_kind == 'multi'){ $temp_item_details .= $temp_damage.$temp_damage_unit.' Damage / '.$temp_recovery.$temp_recovery_unit.' Recovery'; }
                else { $temp_item_details .= 'Support'; }
                //$temp_item_details .= ' | '.$temp_item->item_accuracy.'% Accuracy';
                if (!empty($temp_item_quantity)){
                    if ($temp_item_quantity != 1){ $temp_item_details .= ' | '.$temp_item_quantity.' Units'; }
                    else { $temp_item_details .= ' | 1 Unit'; }
                }
                $temp_item_description = $temp_item->item_description;
                $temp_item_description = str_replace('{DAMAGE}', $temp_damage, $temp_item_description);
                $temp_item_description = str_replace('{RECOVERY}', $temp_recovery, $temp_item_description);
                $temp_item_description = str_replace('{DAMAGE2}', $temp_damage2, $temp_item_description);
                $temp_item_description = str_replace('{RECOVERY2}', $temp_recovery2, $temp_item_description);
                $temp_item_details .= ' <br />'.$temp_item_description;
                //$temp_item_details .= ' <br />subclass:'.$temp_iteminfo['item_subclass'];
                $temp_item_details_plain = strip_tags(str_replace('<br />', '&#10;', $temp_item_details));
                $temp_item_details_tooltip = htmlentities($temp_item_details, ENT_QUOTES, 'UTF-8');

                //$temp_item_details .= ' | x'.$temp_multiplier.' '.$this_robot->robot_core.' '.count($this_battle->battle_field->field_multipliers);

                // Define the item button text variables
                $temp_item_label = '<span class="multi">';
                $temp_item_label .= '<span class="maintext">'.$temp_item->item_name.'</span>';
                $temp_item_label .= '<span class="subtext">';
                    $temp_item_label .= (!empty($temp_type) ? $mmrpg_index['types'][$temp_item->item_type]['type_name'].' ' : 'Neutral ');
                    if (!empty($temp_type2)){ $temp_item_label .= ' / '.$mmrpg_index['types'][$temp_item->item_type2]['type_name']; }
                    else { $temp_item_label .= ($temp_kind == 'damage' ? 'Damage' : ($temp_kind == 'recovery' ? 'Recovery' : ($temp_kind == 'multi' ? 'Effects' : 'Special'))); }
                $temp_item_label .= '</span>';
                $temp_item_label .= '<span class="subtext">';
                    $temp_item_label .= '<span style="'.($temp_multiplier != 1 ? ($temp_multiplier > 1 ? 'color: rgb(161, 255, 124); ' : 'color: rgb(255, 150, 150); ') : '').'">Power :'.($temp_kind == 'damage' ? $temp_damage.$temp_damage_unit.' ' : ($temp_kind == 'recovery' ? $temp_recovery.$temp_recovery_unit.' ' : ($temp_kind == 'multi' ? $temp_damage.$temp_damage_unit.'/'.$temp_recovery.$temp_recovery_unit.' ' : '0'))).'</span>';
                    $temp_item_label .= '&nbsp;';
                    //$temp_item_label .= 'A:'.$temp_accuracy.'%';
                $temp_item_label .= '</span>';
                $temp_item_label .= '</span>';

                // Define whether or not this item button should be enabled
                if ($temp_item_quantity < $temp_item_cost){ $temp_button_enabled = false; }
                //$temp_button_enabled = $temp_item_quantity >= $temp_item_cost ? true : false;
                // If this button is enabled, add it to the global item options array
                //if ($temp_button_enabled){ $temp_player_item_actions[] = $temp_item->item_token; }

                // All items appear in YELLOW
                //$temp_item_type_backup = $temp_item->item_type;
                //$temp_item->item_type = 'electric';

                // Define the item sprite variables
                $temp_item_sprite = array();
                $temp_item_sprite['name'] = $temp_item->item_name;
                $temp_item_sprite['image'] = $temp_item->item_image;
                if ($temp_item->item_token == 'extra-life'){
                    // Automatically change this item's image based on player
                    if ($this_player->player_token == 'dr-light'){ $temp_item_sprite['image'] = 'extra-life'; }
                    elseif ($this_player->player_token == 'dr-wily'){ $temp_item_sprite['image'] = 'extra-life-2'; }
                    elseif ($this_player->player_token == 'dr-cossack'){ $temp_item_sprite['image'] = 'extra-life-3'; }
                    //elseif ($this_player->player_token == 'dr-lalinde'){ $temp_item_sprite['image'] = 'extra-life-4'; }
                }
                $temp_item_sprite['image_size'] = $temp_item->item_image_size;
                $temp_item_sprite['image_size_text'] = $temp_item_sprite['image_size'].'x'.$temp_item_sprite['image_size'];
                $temp_item_sprite['image_size_zoom'] = $temp_item->item_image_size * 2;
                $temp_item_sprite['image_size_zoom_text'] = $temp_item_sprite['image_size'].'x'.$temp_item_sprite['image_size'];
                $temp_item_sprite['url'] = 'images/items/'.$temp_item_sprite['image'].'/icon_'.$item_direction.'_'.$temp_item_sprite['image_size_text'].'.png';
                $temp_item_sprite['preload'] = 'images/items/'.$temp_item_sprite['image'].'/sprite_'.$item_direction.'_'.$temp_item_sprite['image_size_zoom_text'].'.png';
                $temp_item_sprite['class'] = 'sprite sprite_'.$temp_item_sprite['image_size_text'].' sprite_'.$temp_item_sprite['image_size_text'].'_base '; // item_type item_type_'.(!empty($temp_item->item_type) ? $temp_item->item_type : 'none');
                $temp_item_sprite['style'] = 'background-image: url('.$temp_item_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.');  top: 5px; left: 5px; ';
                $temp_item_sprite['markup'] = '<span class="'.$temp_item_sprite['class'].' sprite_40x40_item" style="'.$temp_item_sprite['style'].'">'.$temp_item_sprite['name'].'</span>';
                $temp_item_sprite['markup'] .= '<span class="'.$temp_item_sprite['class'].' sprite_40x40_weapons" style="top: 35px; left: 5px; '.($temp_item_quantity > 1 ? '' : ($temp_item_quantity > 0 ? 'color: #AA9393; ' : 'color: #A77D7D; ')).'"><sup style="position: relative: bottom: 1px;">x</sup> '.$temp_item_quantity.'</span>';

                // Now use the new object to generate a snapshot of this item button
                /*if ($temp_button_enabled){ ?><a data-order="<?=$temp_order_counter?>" class="button action_item action_item item_<?= $temp_item->item_token ?> item_type item_type_electric block_<?= $equipped_items_count ?>" type="button" data-action="item_<?= $temp_item->item_id.'_'.$temp_item->item_token ?>" title="<?= $temp_item_details_plain ?>" data-tooltip="<?= $temp_item_details_tooltip ?>" data-preload="<?= $temp_item_sprite['preload'] ?>" data-actualtarget="<?= $temp_item->item_target ?>" data-target="<?= $temp_target ?>"><label class=""><?= $temp_item_sprite['markup'] ?><?= $temp_item_label ?></label></a><? }*/
                if ($temp_button_enabled){ ?><a data-order="<?=$temp_order_counter?>" class="button action_item action_item item_<?= $temp_item->item_token ?> item_type item_type_<?= (!empty($temp_item->item_type) ? $temp_item->item_type : 'none').(!empty($temp_item->item_type2) ? '_'.$temp_item->item_type2 : '') ?> block_<?= $equipped_items_count ?>" type="button" data-action="item_<?= $temp_item->item_id.'_'.$temp_item->item_token ?>" data-tooltip="<?= $temp_item_details_tooltip ?>" data-preload="<?= $temp_item_sprite['preload'] ?>" data-actualtarget="<?= $temp_item->item_target ?>" data-target="<?= $temp_target ?>"><label class=""><?= $temp_item_sprite['markup'] ?><?= $temp_item_label ?></label></a><? }
                else { ?><a data-order="<?=$temp_order_counter?>" class="button button_disabled action_item action_item item_<?= $temp_item->item_token ?> item_type item_type_<?= (!empty($temp_item->item_type) ? $temp_item->item_type : 'none').(!empty($temp_item->item_type2) ? '_'.$temp_item->item_type2 : '') ?> block_<?= $equipped_items_count ?>" type="button"><label class=""><?= $temp_item_sprite['markup'] ?><?= $temp_item_label ?></label></a><? }

                // Increment the order counter
                $temp_order_counter++;

                $item_key++;
            }
            // If there were less than 8 items, fill in the empty spaces
            if ($equipped_items_count % 8 != 0){
                $temp_padding_amount = 8 - ($equipped_items_count % 8);
                $temp_last_key = $equipped_items_count + $temp_padding_amount;
                for ($i = $equipped_items_count; $i < $temp_last_key; $i++){
                    // Display an empty button placeholder
                    ?><a class="button action_item action_item button_disabled block_<?= $i + 1 ?>" type="button">&nbsp;</a><?
                }
            }
        }
        ?>
    </div>
    <?
    // Display the back button by default
    ?><div class="sub_actions"><a data-order="<?=$temp_order_counter?>" class="button action_back" type="button" data-panel="battle"><label>Back</label></a></div><?
    // Increment the order counter
    $temp_order_counter++;
$actions_markup['item'] = trim(ob_get_clean());
$actions_markup['item'] = preg_replace('#\s+#', ' ', $actions_markup['item']);
?>