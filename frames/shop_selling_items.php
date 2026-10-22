<?

// -- SHOP SELLING ITEMS / PARTS / CORES -- //

?>

<table class="full" style="margin-bottom: 5px;">
    <colgroup>
        <col width="50%" />
        <col width="50%" />
    </colgroup>
    <thead>
        <tr>
            <th class="left">
                <span class="button buy_button buy_button_header">&nbsp;</span>
                <label class="item_quantity item_quantity_header">Own</label>
                <label class="item_price item_price_header">Buy</label>
            </th>
            <th class="right">
                <span class="button buy_button buy_button_header">&nbsp;</span>
                <label class="item_quantity item_quantity_header">Own</label>
                <label class="item_price item_price_header">Buy</label>
            </th>
        </tr>
    </thead>
</table>

<div class="scroll_wrapper">

    <table class="full" style="margin-bottom: 5px;">
        <colgroup>
            <col width="50%" />
            <col width="50%" />
        </colgroup>
        <tbody>
            <tr>

                <?

                // Collect the items for buying and slice/shuffle if nessary
                $item_list_array = array();
                if ($selling_token == 'items' && !empty($shop_info['shop_items']['items_selling'])){ $item_list_array = $shop_info['shop_items']['items_selling']; }
                elseif ($selling_token == 'parts' && !empty($shop_info['shop_parts']['parts_selling'])){ $item_list_array = $shop_info['shop_parts']['parts_selling']; }
                elseif ($selling_token == 'cores' && !empty($shop_info['shop_cores']['cores_selling'])){ $item_list_array = $shop_info['shop_cores']['cores_selling']; }

                // Define the base URL for all shop item images
                $composite_sprite_config = array('kind' => 'items', 'image' => 'icon_right_40x40', 'size' => 40, 'frame' => 0);
                $composite_sprite_image = rpg_game::get_sprite_composite_path($composite_sprite_config);
                $composite_sprite_index = rpg_game::get_sprite_composite_index($composite_sprite_config);
                $composite_sprite_image_markup = '<span class="icon_sprite"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url('.$composite_sprite_image.'); background-position: 0 0;"></span></span>';
                //error_log('$composite_sprite_image_markup = '.print_r($composite_sprite_image_markup, true));

                /*
                // Loop through the items and print them one by one
                $item_counter = 0;
                $item_counter_total = count($item_list_array);
                foreach ($item_list_array AS $token => $price){
                    if (isset($mmrpg_database_items[$token])){ $item_info = $mmrpg_database_items[$token]; }
                    else { continue; }

                    $item_counter++;
                    $item_info_token = $token;
                    $item_info_price = $price;
                    $item_info_name = $item_info['item_name'];
                    $item_info_type = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
                    if ($item_info_type != 'none' && !empty($item_info['item_type2'])){ $item_info_type .= '_'.$item_info['item_type2']; }
                    elseif ($item_info_type == 'none' && !empty($item_info['item_type2'])){ $item_info_type = $item_info['item_type2']; }
                    $item_info_quantity = !empty($_SESSION[$session_token]['values']['battle_items'][$token]) ? $_SESSION[$session_token]['values']['battle_items'][$token] : 0;
                    $global_item_quantities[$item_info_token] = $item_info_quantity;
                    $global_item_prices['buy'][$item_info_token] = $item_info_price;
                    $item_cell_float = $item_counter % 2 == 0 ? 'right' : 'left';
                    $temp_info_tooltip = rpg_item::print_editor_title_markup($robot_info, $item_info, array('show_quantity' => false));
                    $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);

                    $item_sprite_token = $token;
                    $item_sprite_offset = !empty($composite_sprite_index[$item_sprite_token]['offset']) ? $composite_sprite_index[$item_sprite_token]['offset'] : array('x' => 9999, 'y' => 9999);
                    $item_sprite_image_markup = str_replace('background-position: 0 0;', 'background-position: -'.$item_sprite_offset['x'].'px -'.$item_sprite_offset['y'].'px;', $composite_sprite_image_markup);
                    $item_info_name = $item_sprite_image_markup.'<span class="wrap">'.$item_info_name.'</span>';

                    ?>
                        <td class="<?= $item_cell_float ?> item_cell" data-kind="item" data-action="buy" data-token="<?= $item_info_token ?>">
                            <span class="item_name ability_type ability_type_<?= $item_info_type ?>" data-click-tooltip="<?= $temp_info_tooltip ?>"><?= $item_info_name ?></span>
                            <a class="button buy_button ability_type ability_type_none" href="#">Buy</a>
                            <label class="item_quantity" data-quantity="0">x 0</label>
                            <label class="item_price" data-price="<?= $item_info_price ?>">&hellip; <?= $item_info_price ?>z</label>
                        </td>
                    <?

                    if ($item_cell_float == 'right' && $item_counter < $item_counter_total){ echo '</tr><tr>'; }

                }
                */

                // Loop through the items and print them one by one
                $item_counter = 0;
                $item_counter_total = count($item_list_array);
                foreach ($item_list_array AS $token => $price){
                    if (isset($mmrpg_database_items[$token])){ $item_info = $mmrpg_database_items[$token]; }
                    else { continue; }
                    $item_counter++;
                    $item_info_token = $token;
                    $item_info_price = $price;
                    $item_info_name = $item_info['item_name'];
                    $item_info_type = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
                    if ($item_info_type != 'none' && !empty($item_info['item_type2'])){ $item_info_type .= '_'.$item_info['item_type2']; }
                    elseif ($item_info_type == 'none' && !empty($item_info['item_type2'])){ $item_info_type = $item_info['item_type2']; }
                    $item_info_quantity = !empty($_SESSION[$session_token]['values']['battle_items'][$token]) ? $_SESSION[$session_token]['values']['battle_items'][$token] : 0;
                    $item_cell_float = $item_counter % 2 == 0 ? 'right' : 'left';
                    // If we're in the parts shop, make sure we auto-hide parts the player has not seen yet
                    $item_block_purchase = false;
                    if ($selling_token === 'parts'){
                        // Check if the part has been unlocked (handles both flat array and associative array formats)
                        $part_unlocked = isset($items_unlocked[$item_info_token]) ? true : false;
                        if (!$part_unlocked){
                            $item_block_purchase = true;
                            $item_info_name = preg_replace('/[a-z]{1}/i', '?', $item_info_name);
                            $item_info_price = 0;
                            $temp_info_tooltip = 'Sorry, but I can\'t make copies of this part yet. If you can find one in the overworld, I can reverse-engineer it for you!';
                        }
                    }
                    // Define global quantities and prices now that the price may have been zeroed out
                    $global_item_quantities[$item_info_token] = $item_info_quantity;
                    $global_item_prices['buy'][$item_info_token] = $item_info_price;
                    // Generate the default tooltip if it isn't blocked
                    if (!$item_block_purchase){
                        $temp_info_tooltip = rpg_item::print_editor_title_markup($robot_info, $item_info, array('show_quantity' => false));
                        $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);
                    }
                    // ---------------------------------
                    $item_sprite_token = $token;
                    $item_sprite_offset = !empty($composite_sprite_index[$item_sprite_token]['offset']) ? $composite_sprite_index[$item_sprite_token]['offset'] : array('x' => 9999, 'y' => 9999);
                    $item_sprite_image_markup = str_replace('background-position: 0 0;', 'background-position: -'.$item_sprite_offset['x'].'px -'.$item_sprite_offset['y'].'px;', $composite_sprite_image_markup);
                    $item_info_name = $item_sprite_image_markup.'<span class="wrap">'.$item_info_name.'</span>';

                    ?>
                        <td class="<?= $item_cell_float ?> item_cell<?= ($item_block_purchase ? ' item_cell_blocked' : '') ?>" data-kind="item" data-action="buy" data-token="<?= $item_info_token ?>">
                            <span class="item_name ability_type ability_type_<?= $item_info_type ?>" data-click-tooltip="<?= $temp_info_tooltip ?>"<?= ($item_block_purchase ? ' style="text-decoration: line-through;"' : '') ?>><?= $item_info_name ?></span>
                            <a class="button buy_button ability_type ability_type_none" href="#">Buy</a>
                            <label class="item_quantity" data-quantity="0"<?= ($item_block_purchase ? ' style="visibility: hidden;"' : '') ?>>x 0</label>
                            <label class="item_price" data-price="<?= $item_info_price ?>">&hellip; <?= $item_info_price ?>z</label>
                        </td>
                    <?

                    if ($item_cell_float == 'right' && $item_counter < $item_counter_total){ echo '</tr><tr>'; }

                }

                if ($item_counter % 2 != 0){
                    ?>
                        <td class="right item_cell item_cell_disabled">
                            &nbsp;
                        </td>
                    <?
                }

                ?>

            </tr>
        </tbody>
    </table>

</div>