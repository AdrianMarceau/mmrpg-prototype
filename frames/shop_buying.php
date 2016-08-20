<?

// -- BUYING MARKUP -- //

// Loop through the buying tokens and display tabs for them
foreach ($shop_buying_tokens AS $buying_token){

    ?>

        <div class="tab_container tab_container_buying" data-tab="buying" data-tab-type="<?= $buying_token ?>">

            <div class="shop_quote shop_quote_buying">&quot;<?= isset($shop_info['shop_quote_buying'][$buying_token]) ? $shop_info['shop_quote_buying'][$buying_token] : $shop_info['shop_quote_buying'] ?>&quot;</div>

            <?

            // If this shop has items for buying, print them out
            if (in_array($buying_token, array('items', 'cores')) && !empty($shop_info['shop_items']['items_buying'])){

                // Include the buying markup for items and cores
                require(MMRPG_CONFIG_ROOTDIR.'frames/shop_buying_items.php');

            }
            // If this shop has items for buying, print them out
            elseif ($buying_token == 'stars' && !empty($shop_info['shop_stars']['stars_buying'])){

                // Include the buying markup for items and cores
                require(MMRPG_CONFIG_ROOTDIR.'frames/shop_buying_stars.php');

            }

            ?>

            <table class="full" style="margin-bottom: 5px;">
                <colgroup>
                    <col width="100%" />
                </colgroup>
                <tbody>
                    <tr>
                        <td colspan="2" class="left item_cell_confirm" data-kind="" data-action="" data-token="" data-price="" data-quantity="">
                            <div class="placeholder">&hellip;</div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

    <?

}

?>