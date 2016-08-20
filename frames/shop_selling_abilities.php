<?

// -- SHOP SELLING ABILITIES -- //

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
                if ($selling_token == 'abilities'){ $ability_list_array = $shop_info['shop_abilities']['abilities_selling']; }
                elseif ($selling_token == 'weapons'){ $ability_list_array = $shop_info['shop_weapons']['weapons_selling']; }
                $ability_list_array_count = count($ability_list_array);
                $ability_list_max = 20;

                // Collect the unlocked abilities for all three players
                $ability_list_unlocked = array();
                if (!empty($_SESSION[$session_token]['values']['battle_rewards']['dr-light']['player_abilities'])){ $ability_list_unlocked['dr-light'] = array_keys($_SESSION[$session_token]['values']['battle_rewards']['dr-light']['player_abilities']); }
                if (!empty($_SESSION[$session_token]['values']['battle_rewards']['dr-wily']['player_abilities'])){ $ability_list_unlocked['dr-wily'] = array_keys($_SESSION[$session_token]['values']['battle_rewards']['dr-wily']['player_abilities']); }
                if (!empty($_SESSION[$session_token]['values']['battle_rewards']['dr-cossack']['player_abilities'])){ $ability_list_unlocked['dr-cossack'] = array_keys($_SESSION[$session_token]['values']['battle_rewards']['dr-cossack']['player_abilities']); }

                // Count how any of these abilities have been unlocked already
                $ability_list_unlocked_completely = 0;
                foreach ($ability_list_array AS $token => $price){
                    if (!isset($mmrpg_database_abilities[$token])){ unset($ability_list_array[$token]); continue; }
                    if (empty($mmrpg_database_abilities[$token]['ability_flag_complete']) || mmrpg_game_ability_unlocked('', '', $token)){
                        $ability_list_unlocked_completely += 1;
                    }
                }

                // Re-count the ability list after recent changes
                $ability_list_array_count = count($ability_list_array);

                // Reverse the order with newest on top
                $ability_list_array = array_reverse($ability_list_array, true);

                // Re-count the ability list after recent changes
                $ability_list_array_count = count($ability_list_array);
                $ability_list_array = array_reverse($ability_list_array, true);

                // Loop through the items and print them one by one
                $ability_counter = 0;
                if (!empty($ability_list_array)){
                    foreach ($ability_list_array AS $token => $price){

                        $ability_info = $mmrpg_database_abilities[$token];
                        $ability_info_token = $token;
                        $ability_info_price = $price;
                        $ability_info_name = $ability_info['ability_name'];
                        $ability_info_type = !empty($ability_info['ability_type']) ? $ability_info['ability_type'] : 'none';

                        if ($ability_info_type != 'none' && !empty($ability_info['ability_type2'])){ $ability_info_type .= '_'.$ability_info['ability_type2']; }
                        elseif ($ability_info_type == 'none' && !empty($ability_info['ability_type2'])){ $ability_info_type = $ability_info['ability_type2']; }

                        $ability_info_quantity = 0;
                        $ability_info_unlocked = array();

                        if (mmrpg_game_ability_unlocked('', '', $token)){
                            $ability_info_quantity = 3;
                            $ability_info_unlocked = array('dr-light', 'dr-wily', 'dr-cossack');
                            $ability_info_price = 0;
                        }

                        if (empty($ability_info['ability_flag_complete'])){
                            $ability_info_quantity = -1;
                            $ability_info_unlocked = array('coming-soon');
                            $ability_info_name = preg_replace('/[a-z0-9]/i', '?', $ability_info_name);
                            $ability_info_price = 0;
                        }

                        $global_item_quantities[$ability_info_token] = $ability_info_quantity;
                        $global_item_prices['buy'][$ability_info_token] = $ability_info_price;
                        $temp_info_tooltip = !empty($ability_info['ability_flag_complete']) ? rpg_ability::print_editor_title_markup($robot_info, $ability_info) : 'Coming Soon! <br /> <span style="font-size:80%;">This ability is still in development and cannot be purchased yet. <br /> Apologies for the inconveinece, and please check back later!</span>';
                        $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);
                        $ability_counter++;
                        $ability_cell_float = $ability_counter % 2 == 0 ? 'right' : 'left';

                        ?>
                            <td class="<?= $ability_cell_float ?> item_cell" data-kind="ability" data-action="buy" data-token="<?= $ability_info_token ?>" data-unlocked="<?= implode(',', $ability_info_unlocked) ?>">
                                <span class="item_name ability_type ability_type_<?= $ability_info_type ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $ability_info_name ?></span>
                                <a class="buy_button ability_type ability_type_none" href="#">Buy</a>
                                <label class="item_quantity" data-quantity="0"><?= !empty($ability_info_quantity) ? '&#10004;' : '-' ?></label>
                                <label class="item_price" data-price="<?= $ability_info_price ?>">&hellip; <?= $ability_info_price ?>z</label>
                            </td>
                        <?

                        if ($ability_cell_float == 'right'){ echo '</tr><tr>'; }

                    }

                    if ($ability_counter % 2 != 0){
                        ?>
                            <td class="right item_cell item_cell_disabled">
                                &nbsp;
                            </td>
                        <?
                    }

                } else {

                    ?>
                        <td class="right item_cell item_cell_disabled" colspan="2" style="text-align: center">
                            <span class="item_name ability_type ability_type_empty" style="float: none; width: 100px; margin: 6px auto; text-align: center;">Sold Out!</span>
                        </td>
                    <?

                }

                /*
                die('<hr />'.
                    '<pre>$backup_unlocked_abilities = '.print_r($backup_unlocked_abilities, true).'</pre><hr />'.
                    '<pre>$ability_list_array = '.print_r($ability_list_array, true).'</pre><hr />'
                    );
                */

                ?>

            </tr>
        </tbody>
    </table>

</div>