<?

// Pull in required indexes
$mmrpg_database_types = rpg_type::get_index();
$mmrpg_database_abilities = rpg_ability::get_index();
$mmrpg_database_items = rpg_item::get_index();

// -- DEFINE SHOP INDEXES -- //
// Below data copy/pasted from "includes/shop.php" on 2020/11/08 before
// the structure of the file is changed and can no longer be used for
// migration purposes.  Post-migration details may change.

// Define a function for collecting an array or items with prices given tokens
function get_items_with_prices(){
    $item_tokens = func_get_args();
    if (empty($item_tokens)){ return array(); }
    global $mmrpg_database_items;
    $item_prices = array();
    foreach ($item_tokens AS $item_token){
        if (!isset($mmrpg_database_items[$item_token])){ continue; }
        $item_info = $mmrpg_database_items[$item_token];
        $item_price = 0;
        if (!empty($item_info['item_price'])){ $item_price = $item_info['item_price']; }
        if (empty($item_price)){ continue; }
        $item_prices[$item_token] = $item_price;
    }
    return $item_prices;
}
// Define a function for collecting an array or items with values given tokens
function get_items_with_values(){
    $item_tokens = func_get_args();
    if (empty($item_tokens)){ return array(); }
    global $mmrpg_database_items;
    $item_values = array();
    foreach ($item_tokens AS $item_token){
        if (!isset($mmrpg_database_items[$item_token])){ continue; }
        $item_info = $mmrpg_database_items[$item_token];
        $item_value = 0;
        if (!empty($item_info['item_value'])){ $item_value = ceil($item_info['item_value'] / 2); }
        elseif (!empty($item_info['item_price'])){ $item_value = ceil($item_info['item_price'] / 2); }
        if (empty($item_value)){ continue; }
        $item_values[$item_token] = $item_value;
    }
    return $item_values;
}

// Define a function for collecting an array of abilities with prices given tokens
function get_abilities_with_prices(){
    $ability_tokens = func_get_args();
    if (empty($ability_tokens)){ return array(); }
    global $mmrpg_database_abilities;
    $ability_prices = array();
    foreach ($ability_tokens AS $ability_token){
        if (!isset($mmrpg_database_abilities[$ability_token])){ continue; }
        $ability_info = $mmrpg_database_abilities[$ability_token];
        $ability_price = 0;
        if (!empty($ability_info['ability_energy'])){ $ability_price = ceil($ability_info['ability_energy'] * MMRPG_SETTINGS_SHOP_ABILITY_PRICE); }
        else { $ability_price = MMRPG_SETTINGS_SHOP_ABILITY_PRICE; }
        if (empty($ability_price)){ continue; }
        $ability_prices[$ability_token] = $ability_price;
    }
    return $ability_prices;
}

// Define the array to hold all the shop data
$this_shop_index = array();

// AUTO'S SHOP
$this_shop_index['auto'] = array(
        'shop_token' => 'auto',
        'shop_name' => 'Auto\'s Shop',
        'shop_owner' => 'Auto',
        'shop_unlock' => 'Complete the first chapter as Dr. Light.',
        'shop_seeking' => 'screws',
        'shop_seeking_image' => 'large-screw',
        'shop_colour' => 'nature',
        'shop_field' => 'light-laboratory',
        'shop_player' => 'dr-light',
        'shop_number' => 'SHOP-001',
        'shop_kind_selling' => array('items'),
        'shop_kind_buying' => array('items'),
        'shop_quote_selling' => array(
            'items' => 'Welcome to Auto\'s Shop! I\'ve got lots of useful items for sale, so let me know if you need anything.',
            'parts' => 'Great news! I\'ve cracked the code on holdable items and created new parts! See anything you like?'
            ),
        'shop_quote_buying' => array(
            'items' => 'So you wanna sell something, eh? Let\'s see what you\'ve collected so far! Hopefully lots of screws!'
            ),
        'shop_items' => array(
            'items_selling' => get_items_with_prices(
                'energy-pellet', 'weapon-pellet',
                'energy-capsule', 'weapon-capsule',
                'energy-tank', 'weapon-tank'
                ),
            'items_selling2' => get_items_with_prices(
                'energy-pellet', 'weapon-pellet',
                'energy-capsule', 'weapon-capsule',
                'energy-tank', 'weapon-tank',
                'attack-pellet', 'defense-pellet',
                'attack-capsule', 'defense-capsule',
                'speed-pellet', 'super-pellet',
                'speed-capsule', 'super-capsule'
                ),
            'items_selling3' => get_items_with_prices(
                'energy-pellet', 'weapon-pellet',
                'energy-capsule', 'weapon-capsule',
                'energy-tank', 'weapon-tank',
                'attack-pellet', 'defense-pellet',
                'attack-capsule', 'defense-capsule',
                'speed-pellet', 'super-pellet',
                'speed-capsule', 'super-capsule',
                'extra-life', 'yashichi'
                ),
            'items_selling4' => get_items_with_prices(
                'energy-pellet', 'weapon-pellet',
                'energy-capsule', 'weapon-capsule',
                'energy-tank', 'weapon-tank',
                'attack-pellet', 'defense-pellet',
                'attack-capsule', 'defense-capsule',
                'speed-pellet', 'super-pellet',
                'speed-capsule', 'super-capsule',
                'extra-life', 'yashichi'
                ),
            'items_buying' => get_items_with_values(
                'small-screw', 'large-screw',
                'energy-pellet', 'weapon-pellet',
                'energy-capsule', 'weapon-capsule',
                'energy-tank', 'weapon-tank',
                'attack-pellet', 'defense-pellet',
                'attack-capsule', 'defense-capsule',
                'speed-pellet', 'super-pellet',
                'speed-capsule', 'super-capsule',
                'extra-life', 'yashichi',
                'speed-booster'
                )
            ),
        'shop_parts' => array(
            'parts_selling' => get_items_with_values(
                'energy-upgrade', 'weapon-upgrade',
                'attack-booster', 'defense-booster',
                'growth-module', 'fortune-module'
                ),
            'parts_selling2' => get_items_with_values(
                'energy-upgrade', 'weapon-upgrade',
                'attack-booster', 'defense-booster',
                'speed-booster', 'field-booster',
                'target-module', 'charge-module',
                'growth-module', 'fortune-module'
                ),
            'parts_selling3' => get_items_with_values(
                'energy-upgrade', 'weapon-upgrade',
                'attack-booster', 'defense-booster',
                'speed-booster', 'field-booster',
                'target-module', 'charge-module',
                'guard-module', 'reverse-module',
                'xtreme-module', 'growth-module',
                'fortune-module'
                ),
            'parts_selling4' => get_items_with_values(
                'energy-upgrade', 'weapon-upgrade',
                'attack-booster', 'defense-booster',
                'speed-booster', 'field-booster',
                'target-module', 'charge-module',
                'guard-module', 'reverse-module',
                'xtreme-module', 'growth-module',
                'fortune-module', 'battery-circuit',
                'sponge-circuit', 'forge-circuit',
                'sapling-circuit'
                )
            )
        );

// REGGAE'S SHOP
$this_shop_index['reggae'] = array(
    'shop_token' => 'reggae',
    'shop_name' => 'Reggae\'s Shop',
    'shop_owner' => 'Reggae',
    'shop_unlock' => 'Complete the first chapter as Dr. Wily.',
    'shop_seeking' => 'cores',
    'shop_seeking_image' => 'none-core',
    'shop_colour' => 'explode',
    'shop_field' => 'wily-castle',
    'shop_player' => 'dr-wily',
    'shop_number' => 'SHOP-002',
    'shop_kind_selling' => array('abilities'),
    'shop_kind_buying' => array('cores'),
    'shop_quote_selling' => array(
        'abilities' => 'Reggae\'s Shop this is! Squawk! New abilities you want! Squaaawk! Give me your zenny! Squaaaawk!'
        ),
    'shop_quote_buying' => array(
        'cores' => 'Reggae wants robot cores, robot cores! Squawk! No other items will do, will do! Squaaaaawk!'
        ),
    'shop_abilities' => array(
        'abilities_selling' => get_abilities_with_prices(
            'buster-charge', 'buster-relay',
            'energy-boost', 'attack-boost',
            'defense-boost', 'speed-boost',
            'energy-break', 'attack-break',
            'defense-break', 'speed-break'
            ),
        'abilities_selling2' => get_abilities_with_prices(
            'buster-charge', 'buster-relay',
            'energy-boost', 'attack-boost',
            'defense-boost', 'speed-boost',
            'energy-break', 'attack-break',
            'defense-break', 'speed-break',
            'energy-swap', 'attack-swap',
            'defense-swap', 'speed-swap'
            ),
        'abilities_selling3' => get_abilities_with_prices(
            'buster-charge', 'buster-relay',
            'energy-boost', 'attack-boost',
            'defense-boost', 'speed-boost',
            'energy-break', 'attack-break',
            'defense-break', 'speed-break',
            'energy-swap', 'attack-swap',
            'defense-swap', 'speed-swap',
            'attack-support', 'defense-support',
            'speed-support', 'energy-support',
            'attack-assault', 'defense-assault',
            'speed-assault', 'energy-assault'
            ),
        'abilities_selling4' => get_abilities_with_prices(
            'buster-charge', 'buster-relay',
            'energy-boost', 'attack-boost',
            'defense-boost', 'speed-boost',
            'energy-break', 'attack-break',
            'defense-break', 'speed-break',
            'energy-swap', 'attack-swap',
            'defense-swap', 'speed-swap',
            'attack-support', 'defense-support',
            'speed-support', 'energy-support',
            'attack-assault', 'defense-assault',
            'speed-assault', 'energy-assault',
            'attack-mode', 'defense-mode',
            'speed-mode', 'energy-mode'
            ),
        'abilities_selling5' => get_abilities_with_prices(
            'buster-charge', 'buster-relay',
            'energy-boost', 'attack-boost',
            'defense-boost', 'speed-boost',
            'energy-break', 'attack-break',
            'defense-break', 'speed-break',
            'energy-swap', 'attack-swap',
            'defense-swap', 'speed-swap',
            'attack-support', 'defense-support',
            'speed-support', 'energy-support',
            'attack-assault', 'defense-assault',
            'speed-assault', 'energy-assault',
            'attack-mode', 'defense-mode',
            'speed-mode', 'energy-mode',
            'field-support', 'mecha-support'
            ),
        'abilities_selling6' => get_abilities_with_prices(
            'buster-charge', 'buster-relay',
            'energy-boost', 'attack-boost',
            'defense-boost', 'speed-boost',
            'energy-break', 'attack-break',
            'defense-break', 'speed-break',
            'energy-swap', 'attack-swap',
            'defense-swap', 'speed-swap',
            'attack-support', 'defense-support',
            'speed-support', 'energy-support',
            'attack-assault', 'defense-assault',
            'speed-assault', 'energy-assault',
            'attack-mode', 'defense-mode',
            'speed-mode', 'energy-mode',
            'field-support', 'mecha-support',
            'experience-booster', 'experience-breaker',
            'recovery-booster', 'recovery-breaker',
            'damage-booster', 'damage-breaker',
            'item-swap'
            )
        ),
    'shop_items' => array(
        'items_buying' => get_items_with_values(
            'cutter-core', 'impact-core', 'freeze-core', 'explode-core',
            'flame-core', 'electric-core', 'time-core', 'earth-core',
            'wind-core', 'water-core', 'swift-core', 'nature-core',
            'missile-core', 'crystal-core', 'shadow-core', 'space-core',
            'shield-core', 'laser-core',
            'copy-core', 'none-core'
            )
        )
    );


// KALINKA'S SHOP
$this_shop_index['kalinka'] = array(
        'shop_token' => 'kalinka',
        'shop_name' => 'Kalinka\'s Shop',
        'shop_owner' => 'Kalinka',
        'shop_unlock' => 'Complete the prototype as any of the three playable characters.',
        'shop_seeking' => 'stars',
        'shop_seeking_image' => 'field-star',
        'shop_colour' => 'electric',
        'shop_field' => 'final-destination',
        'shop_player' => 'dr-cossack',
        'shop_number' => 'SHOP-003',
        'shop_kind_selling' => array(),
        'shop_kind_buying' => array(),
        'shop_quote_selling' => array(),
        'shop_quote_buying' => array(),
        'shop_alts' => array()
        );

// Hard-code the levels Auto currently unlocks stuff in his shop
$auto_shop_levels = array();
$auto_shop_levels['items'] = array(0, 10, 20, 30);
$auto_shop_levels['parts'] = array(0, 40, 50, 60);

// Hard-code the levels Reggae currently unlocks stuff in his shop
$reggae_shop_levels = array();
$reggae_shop_levels['abilities'] = array(0, 20, 30, 40, 50, 60);

// Create the shop migration array to populate
$shop_migration_data = array();
$shop_migration_data['items'] = array();
$shop_migration_data['abilities'] = array();

// Loop through all the item/parts and add them with info to the index
$item_shop_tabs = array('items', 'parts');
foreach ($item_shop_tabs AS $key => $item_shop_tab){
    $list_key = -1;
    if (!isset($this_shop_index['auto']['shop_'.$item_shop_tab])){ continue; }
    foreach ($this_shop_index['auto']['shop_'.$item_shop_tab] AS $list_token => $list_items){
        if (strstr($list_token, '_buying')){ continue; }
        $list_key++;
        $item_shop_level = $auto_shop_levels[$item_shop_tab][$list_key];
        foreach ($list_items AS $item_token => $item_price){
            if (isset($shop_migration_data['items'][$item_token])){ continue; }
            $shop_migration_data['items'][$item_token] = array('item_shop_tab' => 'auto/'.$item_shop_tab, 'item_shop_level' => $item_shop_level, 'item_price' => $item_price);
        }

    }
}

// Loop through all the ability/weapons and add them with info to the index
$ability_shop_tabs = array('abilities', 'weapons');
foreach ($ability_shop_tabs AS $key => $ability_shop_tab){
    $list_key = -1;
    if (!isset($this_shop_index['reggae']['shop_'.$ability_shop_tab])){ continue; }
    foreach ($this_shop_index['reggae']['shop_'.$ability_shop_tab] AS $list_token => $list_abilities){
        if (strstr($list_token, '_buying')){ continue; }
        $list_key++;
        $ability_shop_level = $reggae_shop_levels[$ability_shop_tab][$list_key];
        foreach ($list_abilities AS $ability_token => $ability_price){
            if (isset($shop_migration_data['abilities'][$ability_token])){ continue; }
            $shop_migration_data['abilities'][$ability_token] = array('ability_shop_tab' => 'reggae/'.$ability_shop_tab, 'ability_shop_level' => $ability_shop_level, 'ability_price' => $ability_price);
        }

    }
}

// Manually add data for the special COPY abilities (copy-cores)
$level = 3;
$prices = get_abilities_with_prices('copy-shot', 'copy-soul');
foreach ($prices AS $token => $price){ $level += 3; $shop_migration_data['abilities'][$token] = array('ability_shop_tab' => 'reggae/weapons', 'ability_shop_level' => $level, 'ability_price' => $price); }

// Manually add data for the special OMEGA abilities (none-cores)
$level = 3;
$prices = get_abilities_with_prices('omega-pulse', 'omega-wave');
foreach ($prices AS $token => $price){ $level += 3; $shop_migration_data['abilities'][$token] = array('ability_shop_tab' => 'reggae/weapons', 'ability_shop_level' => $level, 'ability_price' => $price); }

// Manually add data for the special CORE abilities (shield/laser-cores)
$level = 12;
$prices = get_abilities_with_prices('core-shield', 'core-laser');
foreach ($prices AS $token => $price){ $shop_migration_data['abilities'][$token] = array('ability_shop_tab' => 'reggae/weapons', 'ability_shop_level' => $level, 'ability_price' => $price); }

// Loop through and add any SHOT/BUSTER/OVERDRIVE abilities that have the required cores
foreach ($mmrpg_database_abilities AS $ability_token => $ability_info){
    // Skip if this ability is incomplete or not applicable
    if (!$ability_info['ability_flag_complete']){ continue; }
    elseif ($ability_info['ability_class'] != 'master'){ continue; }
    elseif (empty($ability_info['ability_type']) || $ability_info['ability_type'] == 'copy'){ continue; }
    elseif (strstr($ability_info['ability_group'], 'MM00/')){ continue; }
    elseif (!strstr($ability_info['ability_group'], 'MMRPG/Weapons/')){ continue; }
    // Calculate this ability's tier based on weapon energy
    if (strstr($ability_token, '-shot')){ $ability_tier = 1; }
    elseif (strstr($ability_token, '-buster')){ $ability_tier = 2; }
    elseif (strstr($ability_token, '-overdrive')){ $ability_tier = 3; }
    else { continue; }
    // Define the price based on its weapon energy
    $ability_price = $ability_info['ability_energy'] * MMRPG_SETTINGS_SHOP_ABILITY_PRICE;
    if (empty($ability_price)){ $ability_price = MMRPG_SETTINGS_SHOP_ABILITY_PRICE; }
    // Define the cores required based on its tier
    $cores_required = $ability_tier * 3;
    // Append this ability to the parent weapon selling array
    $shop_migration_data['abilities'][$ability_token] = array('ability_shop_tab' => 'reggae/weapons', 'ability_shop_level' => $cores_required, 'ability_price' => $ability_price);
}

// Loop through abilities again and add any remaining elemental abilities
foreach ($mmrpg_database_abilities AS $ability_token => $ability_info){
    // Skip if this ability is incomplete or not applicable
    if (!$ability_info['ability_flag_complete']){ continue; }
    elseif ($ability_info['ability_class'] != 'master'){ continue; }
    elseif (empty($ability_info['ability_type']) || $ability_info['ability_type'] == 'copy'){ continue; }
    elseif (strstr($ability_info['ability_group'], 'MM00/')){ continue; }
    elseif (strstr($ability_info['ability_group'], 'MMRPG/Weapons/')){ continue; }
    // Calculate this ability's tier based on weapon energy
    $ability_tier = 1;
    if ($ability_info['ability_energy'] > 4){ $ability_tier += 1; }
    if ($ability_info['ability_energy'] > 8){ $ability_tier += 1; }
    // Define the price based on its weapon energy
    $ability_price = $ability_info['ability_energy'] * MMRPG_SETTINGS_SHOP_ABILITY_PRICE;
    if (empty($ability_price)){ $ability_price = MMRPG_SETTINGS_SHOP_ABILITY_PRICE; }
    // Define the cores required based on its tier
    $cores_required = $ability_tier * 3;
    // Append this ability to the parent weapon selling array
    $shop_migration_data['abilities'][$ability_token] = array('ability_shop_tab' => 'reggae/weapons', 'ability_shop_level' => $cores_required, 'ability_price' => $ability_price);
}


?>