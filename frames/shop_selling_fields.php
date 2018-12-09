<?

// -- SHOP SELLING FIELDS -- //

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

                // Collect the abilities for buying and slice/shuffle if nessary
                $field_list_array = $shop_info['shop_fields']['fields_selling'];

                // Collect the unlocked fields for this game file
                $field_list_unlocked = !empty($_SESSION[$session_token]['values']['battle_fields']) ? $_SESSION[$session_token]['values']['battle_fields'] : array();

                // Loop through the items and print them one by one
                $field_counter = 0;
                foreach ($field_list_array AS $token => $price){
                    if (isset($mmrpg_database_fields[$token])){ $field_info = rpg_field::parse_index_info($mmrpg_database_fields[$token]); }
                    else { continue; }

                    $field_info_token = $token;
                    $field_info_price = $price;
                    $field_info_name = $field_info['field_name'];
                    $field_info_master = array();
                    $field_info_type = !empty($field_info['field_type']) ? $field_info['field_type'] : 'none';
                    if (!empty($field_info['field_type2'])){ $field_info_type .= '_'.$field_info['field_type2']; }
                    if (!empty($field_info['field_master']) && !empty($mmrpg_database_robots[$field_info['field_master']])){ $field_info_master = $mmrpg_database_robots[$field_info['field_master']]; }
                    $field_info_unlocked = in_array($field_info_token, $field_list_unlocked) ? true : false;
                    $global_item_quantities['field-'.$field_info_token] = $field_info_unlocked ? 1 : 0;
                    $global_item_prices['buy']['field-'.$field_info_token] = $field_info_unlocked ? 0 : $field_info_price;
                    $temp_info_tooltip = rpg_field::print_editor_title_markup($player_info, $field_info);
                    $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);
                    if ($field_info_unlocked){ $field_info_price = 0; }

                    $field_counter++;
                    $field_cell_float = $field_counter % 2 == 0 ? 'right' : 'left';

                    ?>
                        <td class="<?= $field_cell_float ?> item_cell" data-kind="field" data-action="buy" data-token="<?= 'field-'.$field_info_token ?>">
                            <span class="item_name field_name field_type field_type_<?= $field_info_type ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $field_info_name ?></span>
                            <a class="buy_button field_type field_type_none" href="#">Buy</a>
                            <label class="item_quantity" data-quantity="0"><?= !empty($field_info_quantity) ? '&#10004;' : '-' ?></label>
                            <label class="item_price" data-price="<?= $field_info_price ?>">&hellip; <?= $field_info_price ?>z</label>
                        </td>
                    <?

                    if ($field_cell_float == 'right'){ echo '</tr><tr>'; }

                }

                if ($field_counter % 2 != 0){
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