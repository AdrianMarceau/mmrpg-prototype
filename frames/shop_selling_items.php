<?

// -- SHOP SELLING ITEMS / CORES -- //

?>

<table class="full" style="margin-bottom: 5px;">
    <colgroup>
        <col width="50%" />
        <col width="50%" />
    </colgroup>
    <thead>
        <tr>
            <th class="left">
                <span class="buy_button buy_button_header">&nbsp;</span>
                <label class="item_quantity item_quantity_header">Own</label>
                <label class="item_price item_price_header">Buy</label>
            </th>
            <th class="right">
                <span class="buy_button buy_button_header">&nbsp;</span>
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
                $item_list_array = $shop_info['shop_items']['items_selling'];

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

                    ?>
                        <td class="<?= $item_cell_float ?> item_cell" data-kind="item" data-action="buy" data-token="<?= $item_info_token ?>">
                            <span class="item_name ability_type ability_type_<?= $item_info_type ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $item_info_name ?></span>
                            <a class="buy_button ability_type ability_type_none" href="#">Buy</a>
                            <label class="item_quantity" data-quantity="0">x 0</label>
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