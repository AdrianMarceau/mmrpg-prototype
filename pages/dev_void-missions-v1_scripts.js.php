(function(){

    // Predefine some object arrays to hold our information
    gameSettings.customIndex.contentIndex = {};
    var mmrpgIndex = gameSettings.customIndex.contentIndex;
    var mmrpgQueue = {};
    var mmrpgMission = {};

    // Add the void mission data to the global object
    mmrpgIndex.types = <?= json_encode($mmrpg_index_types) ?>;
    //mmrpgIndex.players = <?= json_encode($mmrpg_index_players) ?>;
    mmrpgIndex.robots = <?= json_encode($mmrpg_index_robots) ?>;
    //mmrpgIndex.abilities = <?= json_encode($mmrpg_index_abilities) ?>;
    mmrpgIndex.items = <?= json_encode($mmrpg_index_items) ?>;
    mmrpgIndex.fields = <?= json_encode($mmrpg_index_fields) ?>;
    console.log('mmrpgIndex:', typeof mmrpgIndex, mmrpgIndex);

    // Predefine the item groups to be used in the void cauldron item palette
    let voidRecipeItemGroups = <?= json_encode($void_item_groups_index, JSON_NUMERIC_CHECK) ?>;
    let voidRecipeItemsDisabled = <?= json_encode($void_items_disabled) ?>;
    let voidRecipeItemsQuantities = <?= json_encode($void_items_quantities) ?>;
    //console.log('+++ voidRecipeItemGroups:', voidRecipeItemGroups);
    //console.log('+++ voidRecipeItemsDisabled:', voidRecipeItemsDisabled);
    //console.log('+++ voidRecipeItemsQuantities:', voidRecipeItemsQuantities);

    // Check to see if the Void Recipe calculator is available
    let $voidRecipeWizard = $('#void-recipe');
    let voidRecipeWizard = window.mmrpgVoidCauldron || false;
    if ($voidRecipeWizard.length > 0
        && voidRecipeWizard !== false){
        (function(){

            //console.log('voidRecipeWizard:', $voidRecipeWizard);

            // Initialize the void recipe calculator
            console.log('%c' + 'Initializing the voidRecipeWizard()', 'color: orange;');
            voidRecipeWizard.init($voidRecipeWizard, {
                types: mmrpgIndex.types,
                robots: mmrpgIndex.robots,
                items: mmrpgIndex.items,
                itemsGroups: voidRecipeItemGroups,
                itemsDisabled: voidRecipeItemsDisabled,
                itemsQuantities: voidRecipeItemsQuantities,
                fields: mmrpgIndex.fields,
                });

            })();
        }

})();
