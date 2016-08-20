<?

// -- SHOP SELLING ALTS -- //

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
                $alt_list_array = $shop_info['shop_alts']['alts_selling'];

                // Collect the unlocked alts for this game file
                $alt_list_unlocked = !empty($_SESSION[$session_token]['values']['robot_alts']) ? $_SESSION[$session_token]['values']['robot_alts'] : array();

                //echo('<pre>$alt_list_array = '.print_r($alt_list_array, true).'</pre>');
                //echo('<pre>$alt_list_unlocked = '.print_r($alt_list_unlocked, true).'</pre>');

                //exit();

                // Define an index of symbols for the alts
                $alt_symbol_index = array();
                $alt_symbol_index['alt'] = 'α';
                $alt_symbol_index['alt2'] = 'β';
                $alt_symbol_index['alt3'] = 'δ';
                $alt_symbol_index['alt4'] = 'ε';
                $alt_symbol_index['alt5'] = 'θ';
                $alt_symbol_index['alt6'] = 'λ';
                $alt_symbol_index['alt7'] = 'μ';
                $alt_symbol_index['alt8'] = 'π';
                $alt_symbol_index['alt9'] = 'Σ';

                // Loop through the items and print them one by one
                $alt_counter = 0;
                foreach ($alt_list_array AS $token => $price){

                    list($robot_token, $alt_token) = explode('_', $token);

                    if (!isset($mmrpg_database_robots[$robot_token])){ continue; }
                    $robot_info = rpg_robot::parse_index_info($mmrpg_database_robots[$robot_token]);
                    $robot_summons = mmrpg_prototype_database_summoned($robot_token);

                    if (empty($robot_info['robot_image_alts'])){ continue; }
                    foreach ($robot_info['robot_image_alts'] AS $alt_info){ if ($alt_info['token'] == $alt_token){ break; } }
                    if (empty($alt_info)){ continue; }

                    //echo('<pre>$robot_token = '.print_r($robot_token, true).'</pre>');
                    //echo('<pre>$alt_token = '.print_r($alt_token, true).'</pre>');
                    //echo('<pre>$robot_info = '.print_r($robot_info, true).'</pre>');
                    //echo('<pre>$alt_info = '.print_r($alt_info, true).'</pre>');

                    //exit();

                    $alt_info_token = $token;
                    $alt_info_price = $price;
                    $alt_info_letter = $alt_symbol_index[$alt_token];
                    //$alt_info_name = $robot_info['robot_name'];
                    $alt_info_name = $robot_info['robot_name'].' '.ucfirst($alt_token);

                    /*
                    if (in_array($robot_token, array('roll', 'disco', 'rhythm'))){
                        $alt_info_name = $robot_info['robot_name'].' '.ucfirst($alt_token);
                    } else {
                        $alt_info_name = $robot_info['robot_name'].' '.$alt_info_letter;
                    }
                    */

                    if (!empty($alt_info['colour'])){
                        $alt_info_type = !empty($alt_info['colour']) ? $alt_info['colour'] : 'none';
                    } else {
                        $alt_info_type = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none';
                        if (!empty($robot_info['robot_core2'])){ $alt_info_type .= '_'.$robot_info['robot_core2']; }
                    }

                    $alt_info_unlocked = false;
                    if (isset($alt_list_unlocked[$robot_token]) && in_array($alt_token, $alt_list_unlocked[$robot_token])){ $alt_info_unlocked = true; }
                    elseif ($robot_summons >= $alt_info['summons']){ $alt_info_unlocked = true; }

                    //echo("\n\$alt_info_unlocked = isset(\$alt_list_unlocked[{$robot_token}]) && in_array({$alt_token}, ".print_r($alt_list_unlocked[$robot_token], true).") ? true : false;\n");
                    //echo("\$alt_info_unlocked = ".($alt_info_unlocked ? 'true' : 'fakse')."\n");
                    //exit();

                    $global_item_quantities['alt-'.$alt_info_token] = $alt_info_unlocked ? 1 : 0;
                    $global_item_prices['buy']['alt-'.$alt_info_token] = $alt_info_unlocked ? 0 : $alt_info_price;
                    $temp_info_tooltip = $alt_info['name'];
                    $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);

                    if ($alt_info_unlocked){ $alt_info_price = 0; }
                    //if ($alt_counter >= 24){ break; }

                    $alt_counter++;
                    $alt_cell_float = $alt_counter % 2 == 0 ? 'right' : 'left';

                    ?>
                        <td class="<?= $alt_cell_float ?> item_cell" data-kind="alt" data-action="buy" data-token="<?= 'alt-'.$alt_info_token ?>">
                            <span class="item_name robot_type robot_type_<?= $alt_info_type ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $alt_info_name ?></span>
                            <a class="buy_button robot_type robot_type_none" href="#">Buy</a>
                            <label class="item_quantity" data-quantity="0"><?= !empty($alt_info_quantity) ? '&#10004;' : '-' ?></label>
                            <label class="item_price" data-price="<?= $alt_info_price ?>">&hellip; <?= $alt_info_price ?>z</label>
                        </td>
                    <?

                    if ($alt_cell_float == 'right'){ echo '</tr><tr>'; }

                }

                if ($alt_counter % 2 != 0){
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