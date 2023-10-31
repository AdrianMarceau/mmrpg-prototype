<?

// If the session token has not been set
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

// -- COLLECT ENVIRONMENT VARIABLES -- //

// Include the starforce values so we can alter shop prices
require(MMRPG_CONFIG_ROOTDIR.'includes/starforce.php');

// Collect the field stars from the session variable
if (!isset($_SESSION[$session_token]['values']['battle_shops'])){ $_SESSION[$session_token]['values']['battle_shops'] = array(); }
$this_battle_shops = !empty($_SESSION[$session_token]['values']['battle_shops']) ? $_SESSION[$session_token]['values']['battle_shops'] : array();
$this_battle_shops_count = !empty($this_battle_shops) ? count($this_battle_shops) : 0;

// Define the array to hold all the item quantities
$global_item_quantities = array();
$global_item_prices = array();
$global_zenny_counter = !empty($_SESSION[$session_token]['counters']['battle_zenny']) ? $_SESSION[$session_token]['counters']['battle_zenny'] : 0;
$global_points_counter = !empty($_SESSION[$session_token]['counters']['battle_points']) ? $_SESSION[$session_token]['counters']['battle_points'] : 0;

// Define the global counters for unlocked robot cores and ability types
$global_unlocked_robots = mmrpg_game_robot_tokens_unlocked();
$global_unlocked_abilities = mmrpg_game_ability_tokens_unlocked();
$global_unlocked_items = !empty($_SESSION[$session_token]['values']['battle_items']) ? $_SESSION[$session_token]['values']['battle_items'] : array();
$global_unlocked_alts = !empty($_SESSION[$session_token]['values']['robot_alts']) ? $_SESSION[$session_token]['values']['robot_alts'] : array();
$global_unlocked_robots_cores = array();
$global_unlocked_abilities_types = array();
$global_unlocked_items_tokens = !empty($global_unlocked_items) ? array_keys($global_unlocked_items) : 0;

// -- DEFINE SHOP INDEXES -- //

// Collect an item index for reference
$mmrpg_items = rpg_item::get_index();

// Collect the abilities array from the database so we can control its contents
$deprecated_abilities = rpg_ability::get_global_deprecated_abilities();
$mmrpg_database_abilities = rpg_ability::get_index(true, false, 'master');
$mmrpg_database_abilities = array_filter($mmrpg_database_abilities, function($ability_info) use($deprecated_abilities){
    if (in_array($ability_info['ability_token'], $deprecated_abilities)){ return false; }
    return true;
    });
$mmrpg_database_abilities_count = count($mmrpg_database_abilities);


// -- COUNT/CATALOGUE SHOP INDEXES -- //

// Create type counters for each element
if (!empty($mmrpg_database_types)){
    foreach ($mmrpg_database_types AS $type => $info){
        $global_unlocked_robots_cores[$type] = 0;
        $global_unlocked_abilities_types[$type] = 0;
    }
}

// Loop through robots and count the number of cores represented
if (!empty($global_unlocked_robots)){
    foreach ($global_unlocked_robots AS $token){
        if (!isset($mmrpg_database_robots[$token])){ continue; }
        $info = $mmrpg_database_robots[$token];
        $core1 = !empty($info['robot_core']) ? $info['robot_core'] : 'none';
        $core2 = !empty($info['robot_core2']) ? $info['robot_core2'] : '';
        if (!empty($core1)){ $global_unlocked_robots_cores[$core1]++; }
        if (!empty($core2)){ $global_unlocked_robots_cores[$core2]++; }
    }
}

// Loop through abilities and count the number of types represented
if (!empty($global_unlocked_abilities)){
    foreach ($global_unlocked_abilities AS $token){
        if (!isset($mmrpg_database_abilities[$token])){ continue; }
        $info = $mmrpg_database_abilities[$token];
        $type1 = !empty($info['ability_type']) ? $info['ability_type'] : 'none';
        $type2 = !empty($info['ability_type2']) ? $info['ability_type2'] : '';
        if (!empty($type1)){ $global_unlocked_abilities_types[$type1]++; }
        if (!empty($type2)){ $global_unlocked_abilities_types[$type2]++; }
    }
}

/*
die('<hr />'.
    '<pre>$global_unlocked_robots = '.print_r($global_unlocked_robots, true).'</pre><hr />'.
    '<pre>$global_unlocked_robots_cores = '.print_r($global_unlocked_robots_cores, true).'</pre><hr />'.
    '<pre>$global_unlocked_abilities = '.print_r($global_unlocked_abilities, true).'</pre><hr />'.
    '<pre>$global_unlocked_abilities_types = '.print_r($global_unlocked_abilities_types, true).'</pre><hr />'
    );
*/


// -- DEFINE SHOP INDEXES -- //

// Define a function for collecting an array or items with prices given tokens
function get_items_with_prices(){
    $item_tokens = func_get_args();
    if (empty($item_tokens)){ return array(); }
    global $mmrpg_items;
    $item_prices = array();
    foreach ($item_tokens AS $item_token){
        if (!isset($mmrpg_items[$item_token])){ continue; }
        $item_info = $mmrpg_items[$item_token];
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
    global $mmrpg_items;
    $item_values = array();
    foreach ($item_tokens AS $item_token){
        if (!isset($mmrpg_items[$item_token])){ continue; }
        $item_info = $mmrpg_items[$item_token];
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
    'shop_source' => 'robots',
    'shop_name' => 'Auto\'s Shop',
    'shop_owner' => 'Auto',
    'shop_unlock' => 'Complete the first chapter as Dr. Light.',
    'shop_seeking' => 'screws',
    'shop_seeking_text' => 'Junk Lover',
    'shop_seeking_image' => 'large-screw',
    'shop_colour' => 'nature',
    'shop_field' => 'light-laboratory',
    'shop_player' => 'dr-light',
    'shop_number' => 'SHOP-001',
    'shop_kind_selling' => array('items'),
    'shop_kind_buying' => array('items'),
    'shop_quote_selling' => array(
        'items' => 'Welcome to Auto\'s Shop! I\'ve got lots of useful items for sale, so let me know if you need anything.'
        ),
    'shop_quote_buying' => array(
        'items' => 'So you wanna sell something, eh? Let\'s see what you\'ve collected so far! Hopefully lots of screws!'
        ),
    'shop_items' => array(
        'items_selling' => array(),
        'items_buying' => array()
        )
    );

// REGGAE'S SHOP
$this_shop_index['reggae'] = array(
    'shop_token' => 'reggae',
    'shop_source' => 'robots',
    'shop_name' => 'Reggae\'s Shop',
    'shop_owner' => 'Reggae',
    'shop_unlock' => 'Complete the first chapter as Dr. Wily.',
    'shop_seeking' => 'cores',
    'shop_seeking_text' => 'Core Expert',
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
        'abilities_selling' => array()
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
    'shop_source' => 'players',
    'shop_name' => 'Kalinka\'s Shop',
    'shop_owner' => 'Kalinka',
    'shop_unlock' => 'Complete the prototype as any of the three playable characters.',
    'shop_seeking' => 'stars',
    'shop_seeking_text' => 'Star Seeker',
    'shop_seeking_image' => 'field-star',
    'shop_colour' => 'electric',
    'shop_field' => 'cossack-citadel',
    'shop_player' => 'dr-cossack',
    'shop_number' => 'SHOP-003',
    'shop_kind_selling' => array(),
    'shop_kind_buying' => array(),
    'shop_quote_selling' => array(),
    'shop_quote_buying' => array(),
    'shop_alts' => array()
    );


// -- UPDATE SHOP INVENTORY/PRICES -- //

// Loop through the shop index and prepare to create history arrays where necessary
$this_shop_index_raw = $this_shop_index;
if (!empty($this_shop_index)){
    foreach ($this_shop_index AS $shop_token => $shop_info){

        // Default this shop's level to zero for later
        $this_shop_index[$shop_token]['shop_level'] = 0;

        // Unlock the shop if the associated doctor has completed chapter one
        $shop_player = $shop_info['shop_player'];
        $shop_selling = $shop_info['shop_kind_selling'];
        $shop_buying = $shop_info['shop_kind_buying'];

        // Only show this shop if the appropriate link has been established
        if (mmrpg_prototype_item_unlocked($shop_token.'-link')){

            // If the shop has not been created, define its defaults
            if (!isset($this_battle_shops[$shop_token])){
                $shop_array = array();
                $shop_array['shop_level'] = 1;
                $shop_array['shop_experience'] = 0;
                $shop_array['zenny_earned'] = 0;
                $shop_array['zenny_spent'] = 0;
                if (is_array($shop_selling)){ foreach($shop_selling AS $token){ $shop_array[$token.'_sold'] = array(); } }
                else { $shop_array[$shop_selling.'_sold'] = array(); }
                if (is_array($shop_buying)){ foreach($shop_buying AS $token){ $shop_array[$token.'_bought'] = array(); } }
                else { $shop_array[$shop_buying.'_bought'] = array(); }
                $this_battle_shops[$shop_token] = $shop_array;
            }
            // Otherwise, refresh the shop's level based on experience
            else {
                $shop_array = $this_battle_shops[$shop_token];
                $temp_experience = !empty($shop_array['shop_experience']) ? $shop_array['shop_experience'] : 1;
                $temp_level = mmrpg_prototype_calculate_shop_level_by_experience($temp_experience);
                $temp_level = floor($temp_level);
                if ($temp_level > 100){ $temp_level = 100; }
                $shop_array['shop_level'] = $temp_level;
                $this_battle_shops[$shop_token] = $shop_array;
            }
            // Either way, update this shop's level from the session
            $this_shop_index[$shop_token]['shop_level'] = $this_battle_shops[$shop_token]['shop_level'];

        }
        // Otherwise set the shop level to zero, it's not unlocked yet
        else {

            // Remove this shop from the index to prevent errors
            unset($this_shop_index[$shop_token]);
            // Update this shop's level in the index
            //$this_shop_index[$shop_token]['shop_level'] = 0;

        }

    }
}


// Loop through the shop index and collect omega values
foreach ($this_shop_index AS $shop_token => $shop_info){

    // Only generate a hidden power if we've unlocked them
    if (mmrpg_prototype_item_unlocked('omega-seed')){

        // Collect possible hidden power types
        $hidden_power_kind = $shop_token == 'auto' ? 'stats' : 'elements';
        $hidden_power_types = rpg_type::get_hidden_powers($hidden_power_kind);

        // Generate this shop's omega string, collect it's hidden power
        $shop_omega_string = rpg_game::get_omega_shop_string($shop_token);
        $shop_hidden_power = rpg_game::select_omega_value($shop_omega_string, $hidden_power_types);
        $this_shop_index[$shop_token]['shop_omega_string'] = $shop_omega_string;
        $this_shop_index[$shop_token]['shop_hidden_power'] = $shop_hidden_power;

    } else {

        // Default this shop's omega string and hidden power to empty
        $this_shop_index[$shop_token]['shop_omega_string'] = '';
        $this_shop_index[$shop_token]['shop_hidden_power'] = '';

    }

}


// -- AUTO SHOP UNLOCKS -- //

// Only continue if the shop has been unlocked
if (!empty($this_shop_index['auto'])){

    // Define base lists for sellable and buyable items
    $base_items_selling = array();
    $base_items_buying = array('small-screw', 'large-screw');

    // Create arrays to hold combined sellable and buyable items
    $auto_items_selling = $base_items_selling;
    $auto_items_buying = $base_items_buying;

    // Collect the list of items Auto is selling based on his level
    $unlocked_items_query = "SELECT
        items.item_token,
        items.item_shop_level
        FROM mmrpg_index_items AS items
        LEFT JOIN mmrpg_index_items_groups_tokens AS tokens ON tokens.item_token = items.item_token
        LEFT JOIN mmrpg_index_items_groups AS groups ON groups.group_class = 'item' AND groups.group_token = tokens.group_token
        WHERE
        items.item_flag_published = 1
        AND items.item_flag_complete = 1
        AND items.item_flag_unlockable = 1
        AND items.item_shop_tab = 'auto/items'
        AND items.item_price > 0
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC,
        items.item_token ASC
        ;";
    $cache_token = md5($unlocked_items_query);
    $cached_index = rpg_object::load_cached_index('shop.auto', $cache_token);
    if (!empty($cached_index)){
        $unlocked_items = $cached_index;
        unset($cached_index);
    } else {
        $unlocked_items = $db->get_array_list($unlocked_items_query, 'item_token');
        rpg_object::save_cached_index('shop.auto', $cache_token, $unlocked_items);
    }
    $level = $this_shop_index['auto']['shop_level'];
    $unlocked_items = !empty($unlocked_items) ? array_filter($unlocked_items, function($info) use ($level){
        if (empty($info['item_shop_level'])){ return true; }
        elseif ($level >= $info['item_shop_level']){ return true; }
        return false;
        }) : array();

    // Use the pulled list of unlocked items to expand Auto's shop
    if (!empty($unlocked_items)){
        $unlocked_items_tokens = array_keys($unlocked_items);
        $auto_items_selling = array_merge($auto_items_selling, $unlocked_items_tokens);
        $auto_items_buying = array_merge($auto_items_buying, $unlocked_items_tokens);
    }

    // Update the actual shop index with our finalized items we're selling
    $this_shop_index['auto']['shop_items']['items_selling'] = call_user_func_array('get_items_with_prices', $auto_items_selling);

    // If the player has unlocked the Equip Codes, Auto's kiosk also has a Part Shop tab
    if (mmrpg_prototype_item_unlocked('equip-codes')){

        // Add parts to the list of selling kinds and define the quote shown at the top
        $this_shop_index['auto']['shop_kind_selling'][] = 'parts';
        $this_shop_index['auto']['shop_quote_selling']['parts'] = 'Great news! I\'ve cracked the code on holdable items and created new parts! See anything you like?';

        // Define base lists for sellable parts
        $base_parts_selling = array();

        // Create arrays to hold combined sellable parts
        $auto_parts_selling = $base_parts_selling;

        // Collect the list of items Auto is selling based on his level
        $unlocked_parts_query = "SELECT
            items.item_token,
            items.item_shop_level
            FROM mmrpg_index_items AS items
            LEFT JOIN mmrpg_index_items_groups_tokens AS tokens ON tokens.item_token = items.item_token
            LEFT JOIN mmrpg_index_items_groups AS groups ON groups.group_class = 'item' AND groups.group_token = tokens.group_token
            WHERE
            items.item_flag_published = 1
            AND items.item_flag_complete = 1
            AND items.item_flag_unlockable = 1
            AND items.item_shop_tab = 'auto/parts'
            AND items.item_price > 0
            ORDER BY
            groups.group_order ASC,
            tokens.token_order ASC,
            items.item_token ASC
            ;";
        $cache_token = md5($unlocked_parts_query);
        $cached_index = rpg_object::load_cached_index('shop.auto', $cache_token);
        if (!empty($cached_index)){
            $unlocked_parts = $cached_index;
            unset($cached_index);
        } else {
            $unlocked_parts = $db->get_array_list($unlocked_parts_query, 'item_token');
            rpg_object::save_cached_index('shop.auto', $cache_token, $unlocked_parts);
        }
        $level = $this_shop_index['auto']['shop_level'];
        $unlocked_parts = !empty($unlocked_parts) ? array_filter($unlocked_parts, function($info) use ($level){
            if (empty($info['item_shop_level'])){ return true; }
            elseif ($level >= $info['item_shop_level']){ return true; }
            return false;
            }) : array();

        // Use the pulled list of unlocked items to expand Auto's shop
        if (!empty($unlocked_parts)){
            $unlocked_parts_tokens = array_keys($unlocked_parts);
            $auto_parts_selling = array_merge($auto_parts_selling, $unlocked_parts_tokens);
            $auto_items_buying = array_merge($auto_items_buying, $unlocked_parts_tokens);
        }

        // Update the actual shop index with our finalized parts we're selling
        $this_shop_index['auto']['shop_parts']['parts_selling'] = call_user_func_array('get_items_with_prices', $auto_parts_selling);

    }

    // Loop through the buyable items and remove those that the player hasn't unlocked yet
    if (!empty($auto_items_buying)){
        foreach ($auto_items_buying AS $key => $token){
            if (!in_array($token, $base_items_buying)
                && !in_array($token, $global_unlocked_items_tokens)){
                unset($auto_items_buying[$token]);
            }
        }
    }

    // Update the actual shop index with our finalized items and/or parts we're buying
    $this_shop_index['auto']['shop_items']['items_buying'] = call_user_func_array('get_items_with_values', $auto_items_buying);

    // If this shop has a hidden power, loop through and increase sell prices
    if (!empty($this_shop_index['auto']['shop_hidden_power'])){
        if (!empty($this_shop_index['auto']['shop_items']['items_buying'])){
            $items_list = $this_shop_index['auto']['shop_items']['items_buying'];
            foreach ($items_list AS $item_token => $item_price){
                if (!isset($mmrpg_database_items[$item_token])){ continue; }
                $item_info = $mmrpg_database_items[$item_token];
                $type_token = !empty($item_info['item_type']) ? $item_info['item_type'] : '';
                $omega_boost = $this_shop_index['auto']['shop_hidden_power'] == $type_token ? true : false;
                if (!empty($omega_boost)){ $item_price = ceil($item_price * 1.5); }
                $this_shop_index['auto']['shop_items']['items_buying'][$item_token] = $item_price;
            }
        }
    }

}


// -- REGGAE SHOP UNLOCKS -- //

// Only continue if the shop has been unlocked
$core_level_index = array();
if (!empty($this_shop_index['reggae'])){

    // Define variables to hold the total number of available collectibles
    $num_available_new_abilities = 0;
    $num_available_new_weapons = 0;

    // Collect a list of all abilities already unlocked
    $unlocked_ability_tokens = rpg_game::ability_tokens_unlocked();

    // Collect the list of abilities Reggae is selling based on his level
    $unlocked_abilities_query = "SELECT
        abilities.ability_token,
        abilities.ability_type,
        abilities.ability_type2,
        abilities.ability_shop_level
        FROM mmrpg_index_abilities AS abilities
        LEFT JOIN mmrpg_index_abilities_groups_tokens AS tokens ON tokens.ability_token = abilities.ability_token
        LEFT JOIN mmrpg_index_abilities_groups AS groups ON groups.group_class = 'master' AND groups.group_token = tokens.group_token
        WHERE
        abilities.ability_flag_published = 1
        AND abilities.ability_flag_complete = 1
        AND abilities.ability_flag_unlockable = 1
        AND abilities.ability_shop_tab = 'reggae/abilities'
        AND abilities.ability_price > 0
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC,
        abilities.ability_token ASC
        ;";
    $cache_token = md5($unlocked_abilities_query);
    $cached_index = rpg_object::load_cached_index('shop.reggae', $cache_token);
    if (!empty($cached_index)){
        $unlocked_abilities = $cached_index;
        unset($cached_index);
    } else {
        $unlocked_abilities = $db->get_array_list($unlocked_abilities_query, 'ability_token');
        rpg_object::save_cached_index('shop.reggae', $cache_token, $unlocked_abilities);
    }
    $level = $this_shop_index['reggae']['shop_level'];
    $unlocked_abilities = !empty($unlocked_abilities) ? array_filter($unlocked_abilities, function($info) use ($level){
        if (empty($info['ability_shop_level'])){ return true; }
        elseif ($level >= $info['ability_shop_level']){ return true; }
        return false;
        }) : array();

    // Update the actual shop index with our finalized abilities we're selling
    $reggae_abilities_selling = array_keys($unlocked_abilities);
    $this_shop_index['reggae']['shop_abilities']['abilities_selling'] = call_user_func_array('get_abilities_with_prices', $reggae_abilities_selling);

    // Finally, sort the abilities again so that ones which are NOT unlocked appear first (but keep the order the same otherwise)
    if (!empty($unlocked_ability_tokens)
        && !empty($this_shop_index['reggae']['shop_weapons']['abilities_selling'])){
        $old_abilities_selling = $this_shop_index['reggae']['shop_weapons']['abilities_selling'];
        $new_abilities_selling = array();
        foreach ($old_abilities_selling AS $token => $price){ if (!in_array($token, $unlocked_ability_tokens)){ $new_abilities_selling[$token] = $price; } }
        $num_available_new_abilities = count($new_abilities_selling);
        foreach ($old_abilities_selling AS $token => $price){ if (in_array($token, $unlocked_ability_tokens)){ $new_abilities_selling[$token] = $price; } }
        $this_shop_index['reggae']['shop_weapons']['abilities_selling'] = $new_abilities_selling;
    }

    // If the player has unlocked the Weapon Codes, Reggae's Shop also sells weapons
    if (mmrpg_prototype_item_unlocked('weapon-codes')){

        // Add the Weapons Shop token to the selling array if not there already
        if (!in_array('parts', $this_shop_index['reggae']['shop_kind_selling'])){ $this_shop_index['reggae']['shop_kind_selling'][] = 'weapons'; }
        $this_shop_index['reggae']['shop_quote_selling']['weapons'] = 'Reggae use cores make new weapons! Squaaak! Heroes use weapons defeat bad guys! Squaaak!';

        // Define the weapon selling array and start it empty
        $this_shop_index['reggae']['shop_weapons']['weapons_selling'] = array();

        // Preset the level of all core types to zero before continuing
        $mmrpg_index_types = rpg_type::get_index(false, false, false, true);
        foreach ($mmrpg_index_types AS $type_token => $type_info){ $core_level_index[$type_token] = 0; }

        // If the player has sold any cores, loop through them and add associated abilities
        $level_discount = $this_battle_shops['reggae']['shop_level'] > 1 ? $this_battle_shops['reggae']['shop_level'] / 101 : 0;
        if (!empty($this_battle_shops['reggae']['cores_bought'])){
            foreach ($this_battle_shops['reggae']['cores_bought'] AS $item_token => $item_quantity){
                if (preg_match('/^item-core-/i', $item_token)){ $type_token = preg_replace('/^item-core-/i', '', $item_token); }
                else { $type_token = preg_replace('/-core$/i', '', $item_token); }
                $type_info = $mmrpg_database_types[$type_token];
                if (!isset($core_level_index[$type_token])){ $core_level_index[$type_token] = 0; }
                $core_level_index[$type_token] += $item_quantity;
            }
        }

        // Collect the list of weapons Reggae is selling based on his level
        $unlocked_weapons_query = "SELECT
            abilities.ability_token,
            abilities.ability_type,
            abilities.ability_type2,
            abilities.ability_shop_level
            FROM mmrpg_index_abilities AS abilities
            LEFT JOIN mmrpg_index_abilities_groups_tokens AS tokens ON tokens.ability_token = abilities.ability_token
            LEFT JOIN mmrpg_index_abilities_groups AS groups ON groups.group_class = 'master' AND groups.group_token = tokens.group_token
            LEFT JOIN mmrpg_index_types AS types ON (types.type_token = abilities.ability_type OR types.type_token = 'none' AND abilities.ability_type = '')
            LEFT JOIN mmrpg_index_types AS types2 ON (types2.type_token = abilities.ability_type2 OR types2.type_token = 'none' AND abilities.ability_type2 = '')
            WHERE
            abilities.ability_flag_published = 1
            AND abilities.ability_flag_complete = 1
            AND abilities.ability_flag_unlockable = 1
            AND abilities.ability_shop_tab = 'reggae/weapons'
            AND abilities.ability_price > 0
            ORDER BY
            types.type_order ASC,
            abilities.ability_token LIKE '%-shot' DESC,
            abilities.ability_token LIKE '%-buster' DESC,
            abilities.ability_token LIKE '%-overdrive' DESC,
            abilities.ability_shop_level ASC,
            groups.group_order ASC,
            tokens.token_order ASC,
            abilities.ability_token ASC
            ;";
        $cache_token = md5($unlocked_weapons_query);
        $cached_index = rpg_object::load_cached_index('shop.reggae', $cache_token);
        if (!empty($cached_index)){
            $unlocked_weapons = $cached_index;
            unset($cached_index);
        } else {
            $unlocked_weapons = $db->get_array_list($unlocked_weapons_query, 'ability_token');
            rpg_object::save_cached_index('shop.reggae', $cache_token, $unlocked_weapons);
        }
        $level = $this_shop_index['reggae']['shop_level'];
        $levels = $core_level_index;
        $unlocked_weapons = !empty($unlocked_weapons) ? array_filter($unlocked_weapons, function($info) use ($level, $levels){
            $type = !empty($info['ability_type']) ? $info['ability_type'] : '';
            $required = !empty($info['ability_shop_level']) ? $info['ability_shop_level'] : 0;
            $current = !empty($levels[$type]) ? $levels[$type] : 0;
            if (empty($required)){ return true; }
            elseif ($current >= $required){ return true; }
            return false;
            }) : array();

        // Update the actual shop index with our finalized weapons we're selling
        $reggae_weapons_selling = !empty($unlocked_weapons) ? array_keys($unlocked_weapons) : array();
        $this_shop_index['reggae']['shop_weapons']['weapons_selling'] = call_user_func_array('get_abilities_with_prices', $reggae_weapons_selling);

        // If the Omega Seed is not unlocked yet, prevent those abilities from being purchased
        if (!mmrpg_prototype_item_unlocked('omega-seed')){
            unset($this_shop_index['reggae']['shop_weapons']['weapons_selling']['omega-pulse']);
            unset($this_shop_index['reggae']['shop_weapons']['weapons_selling']['omega-wave']);
        }

        // Loop through unlocked abilities and apply the level discount, if any
        if (!empty($level_discount)
            && isset($this_shop_index['reggae']['shop_weapons']['weapons_selling'])){
            foreach ($this_shop_index['reggae']['shop_weapons']['weapons_selling'] AS $token => $price){
                $new_price = $price - floor(($price / 2) * $level_discount);
                $this_shop_index['reggae']['shop_weapons']['weapons_selling'][$token] = $new_price;
            }
        }

        // Finally, sort the weapons again so that ones which are NOT unlocked appear first (but keep the order the same otherwise)
        if (!empty($unlocked_ability_tokens)
            && !empty($this_shop_index['reggae']['shop_weapons']['weapons_selling'])){
            $old_weapons_selling = $this_shop_index['reggae']['shop_weapons']['weapons_selling'];
            $new_weapons_selling = array();
            foreach ($old_weapons_selling AS $token => $price){ if (!in_array($token, $unlocked_ability_tokens)){ $new_weapons_selling[$token] = $price; } }
            $num_available_new_weapons = count($new_weapons_selling);
            foreach ($old_weapons_selling AS $token => $price){ if (in_array($token, $unlocked_ability_tokens)){ $new_weapons_selling[$token] = $price; } }
            $this_shop_index['reggae']['shop_weapons']['weapons_selling'] = $new_weapons_selling;
        }

    }

    // If Robots or Abilities have been unlocked, increase the core selling prices
    if (!empty($global_unlocked_robots_cores) || !empty($global_unlocked_abilities_types) || !empty($this_star_force)){
        if (!empty($this_shop_index['reggae']['shop_items']['items_buying'])){
            $items_list = $this_shop_index['reggae']['shop_items']['items_buying'];
            foreach ($items_list AS $item_token => $item_price){
                if (!isset($mmrpg_database_items[$item_token])){ continue; }
                $item_info = $mmrpg_database_items[$item_token];
                $type_token = !empty($item_info['item_type']) ? $item_info['item_type'] : '';
                $star_boost = !empty($this_star_force[$type_token]) ? $this_star_force[$type_token] : 0;
                $ability_boost = !empty($global_unlocked_abilities_types[$type_token]) ? $global_unlocked_abilities_types[$type_token] : 0;
                $robot_boost = !empty($global_unlocked_robots_cores[$type_token]) ? $global_unlocked_robots_cores[$type_token] : 0;
                $star_price_boost = ceil($star_boost * 25);
                $ability_price_boost = ceil($ability_boost * 50);
                $robot_price_boost = ceil($robot_boost * 100);
                $item_price += $star_price_boost;
                $item_price += $ability_price_boost;
                $item_price += $robot_price_boost;
                $omega_boost = $this_shop_index['reggae']['shop_hidden_power'] == $type_token ? true : false;
                if (!empty($omega_boost)){ $item_price = ceil($item_price * 1.5); }
                $this_shop_index['reggae']['shop_items']['items_buying'][$item_token] = $item_price;
            }
        }
    }

    // If Reggae's Shop has reached sufficient levels, decrease his selling prices
    if ($this_shop_index['reggae']['shop_level'] > 1){
        $level_discount = $this_battle_shops['reggae']['shop_level'] / 101;
        if (!empty($this_shop_index['reggae']['shop_abilities']['abilities_selling'])){
            foreach ($this_shop_index['reggae']['shop_abilities']['abilities_selling'] AS $ability_kind => $ability_price){
                $ability_price -= round(($ability_price / 2) * $level_discount);
                $this_shop_index['reggae']['shop_abilities']['abilities_selling'][$ability_kind] = $ability_price;
            }
        }
    }

    // If there are no new abilities to purchase but there are weapons, make sure we reorder the tabs
    if ($num_available_new_abilities == 0
        && $num_available_new_weapons > 0){
        $this_shop_index['reggae']['shop_kind_selling'] = array('weapons', 'abilities');
    }

}


// -- KALINKA SHOP UNLOCKS -- //

// Only continue if the shop has been unlocked
if (!empty($this_shop_index['kalinka'])){

    // Define variables to hold the total number of available collectibles
    $num_available_new_robots = 0;
    $num_available_new_alts = 0;

    // If the player has unlocked the Master Codes, Kalinka's Shop also has a Robot Shop and a Field Shop tab
    if (mmrpg_prototype_item_unlocked('master-codes')){

        // Collect a list of all robots already unlocked
        $unlocked_robot_tokens = rpg_game::robot_tokens_unlocked();
        $unlocked_robot_records = rpg_game::robot_database();

        // Collect a list of robot masters that we're allowed to sell
        $buyable_robots_query = "SELECT
            robots.robot_token
            FROM mmrpg_index_robots AS robots
            LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
            LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = robots.robot_class
            LEFT JOIN mmrpg_index_players AS players1 ON players1.player_robot_hero = robots.robot_token
            LEFT JOIN mmrpg_index_players AS players2 ON players2.player_robot_support = robots.robot_token
            WHERE
            robots.robot_flag_published = 1
            AND robots.robot_flag_complete = 1
            AND robots.robot_flag_unlockable = 1
            AND robots.robot_number NOT LIKE 'RPG-%'
            AND robots.robot_number NOT LIKE 'PCR-%'
            AND players1.player_robot_hero IS NULL
            AND players2.player_robot_support IS NULL
            ORDER BY
            FIELD(robots.robot_class, 'master', 'mecha', 'boss'),
            groups.group_order ASC,
            tokens.token_order ASC
            ;";
        $cache_token = md5($buyable_robots_query);
        $cached_index = rpg_object::load_cached_index('shop.kalinka', $cache_token);
        if (!empty($cached_index)){
            $buyable_robots = $cached_index;
            unset($cached_index);
        } else {
            $buyable_robots = $db->get_array_list($buyable_robots_query, 'robot_token');
            rpg_object::save_cached_index('shop.kalinka', $cache_token, $buyable_robots);
        }

        // Ensure there are robots to see before showing them
        if (!empty($buyable_robots)){

            // Add robot data to Kalinka's Shop
            if (!in_array('robots', $this_shop_index['kalinka']['shop_kind_selling'])){ $this_shop_index['kalinka']['shop_kind_selling'][] = 'robots'; }
            $this_shop_index['kalinka']['shop_quote_selling']['robots'] = 'Greetings and welcome to Kalinka\'s Shop! I can create new robots from your battle data!';
            $this_shop_index['kalinka']['shop_robots']['robots_selling'] = array();
            $buyable_robots = array_keys($buyable_robots);
            foreach ($buyable_robots AS $token){
                //error_log('------');
                //error_log('$buyable_robots('.$token.')');
                $level = 1;
                if (!empty($_SESSION[$session_token]['values']['robot_database'][$token])){
                    $records = $_SESSION[$session_token]['values']['robot_database'][$token];
                    //error_log('$records = '.print_r($records, true));
                    if (!empty($records['robot_defeated'])){ $level += $records['robot_defeated']; }
                    if ($level >= 100){ $level = 99; }
                }
                //error_log('$level = '.print_r($level, true));
                $price = MMRPG_SETTINGS_SHOP_ROBOT_PRICE;
                //error_log('$price(before) = '.print_r($price, true));
                $price += ceil(($level / 100) * $price);
                //error_log('$price(after) = '.print_r($price, true));
                $this_shop_index['kalinka']['shop_robots']['robots_selling'][$token] = $price;
            }

            // Finally, sort the robots again so that ones which are NOT unlocked appear first (but keep the order the same otherwise)
            if (!empty($unlocked_robot_tokens)
                && !empty($this_shop_index['kalinka']['shop_robots']['robots_selling'])){
                $old_robots_selling = $this_shop_index['kalinka']['shop_robots']['robots_selling'];
                $new_robots_selling = array();
                foreach ($old_robots_selling AS $token => $price){
                    $info = $mmrpg_database_robots[$token];
                    if ($info['robot_flag_exclusive'] === 0
                        && !in_array($token, $unlocked_robot_tokens)){
                        $new_robots_selling[$token] = $price;
                    }
                }
                foreach ($old_robots_selling AS $token => $price){
                    $info = $mmrpg_database_robots[$token];
                    if ($info['robot_flag_exclusive'] === 1
                        && !in_array($token, $unlocked_robot_tokens)){
                        $new_robots_selling[$token] = $price;
                    }
                }
                $num_available_new_robots = count($new_robots_selling);
                foreach ($old_robots_selling AS $token => $price){
                    $info = $mmrpg_database_robots[$token];
                    if (!isset($new_robots_selling[$token])
                        || in_array($token, $unlocked_robot_tokens)){
                        $new_robots_selling[$token] = $price;
                    }
                }
                $this_shop_index['kalinka']['shop_robots']['robots_selling'] = $new_robots_selling;
            }

        }

    }

    // If the player has unlocked the Dress Codes, Kalinka's kiosk also has an Alt Shop tab
    if (mmrpg_prototype_item_unlocked('dress-codes')){

        // Generate the max tier of alts to sell based on level
        $max_alt_tier_key = floor($this_shop_index['kalinka']['shop_level'] / 10);

        // Collect the unlocked alts for this game file
        $alt_list_unlocked = !empty($_SESSION[$session_token]['values']['robot_alts']) ? $_SESSION[$session_token]['values']['robot_alts'] : array();
        //error_log('$alt_list_unlocked = '.print_r($alt_list_unlocked, true));

        // Create an array to hold any alts unlocked for selling
        $unlocked_alts_list = array();

        // Collect all the robot tokens unlocked by the player so far and filter special
        $banned_tokens = array('mega-man', 'proto-man', 'bass');
        $discount_tokens = array('roll', 'disco', 'rhythm');
        $allowed_tokens = array_values($global_unlocked_robots);
        $allowed_tokens = array_diff_key($allowed_tokens, array_flip($banned_tokens));

        // Pull alt images from the database for the player's unlocked robots
        if (!empty($allowed_tokens)){

            // Generate the allowed token string and pull alt data from the database
            $unlocked_robot_data_query = "SELECT
                robots.robot_token,
                robots.robot_image_alts
                FROM mmrpg_index_robots AS robots
                LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
                LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_class = 'master' AND groups.group_token = tokens.group_token
                WHERE
                robots.robot_flag_published = 1
                AND robots.robot_flag_complete = 1
                AND robots.robot_flag_unlockable = 1
                AND robots.robot_class = 'master'
                AND robots.robot_image_alts <> ''
                AND robots.robot_image_alts <> '[]'
                ORDER BY
                groups.group_order ASC,
                tokens.token_order ASC,
                robots.robot_order ASC
                ;";
            $cache_token = md5($unlocked_robot_data_query);
            $cached_index = rpg_object::load_cached_index('shop.kalinka', $cache_token);
            if (!empty($cached_index)){
                $unlocked_robot_data = $cached_index;
                unset($cached_index);
            } else {
                $unlocked_robot_data = $db->get_array_list($unlocked_robot_data_query, 'robot_token');
                foreach ($unlocked_robot_data AS $key => $info){ $unlocked_robot_data[$key]['robot_image_alts'] = json_decode($info['robot_image_alts'], true); }
                rpg_object::save_cached_index('shop.kalinka', $cache_token, $unlocked_robot_data);
            }
            $unlocked_robot_data = array_filter($unlocked_robot_data, function($info) use ($allowed_tokens){
                if (in_array($info['robot_token'], $allowed_tokens)){ return true; }
                return false;
                });

            // If alts were found, loop through and collect their details
            $unlocked_alts_index = array();
            if (!empty($unlocked_robot_data)){
                foreach ($unlocked_robot_data AS $key => $robot_info){
                    // Collect the alt data and decompress its fields
                    $robot_token = $robot_info['robot_token'];
                    $alt_array = $robot_info['robot_image_alts'];
                    $alt_array_indexed = array();
                    foreach ($alt_array AS $k => $alt){ $alt_array_indexed[$alt['token']] = $alt; }
                    $unlocked_alts_index[$robot_token] = $alt_array_indexed;
                    // Loop through the alts themselves and add any with prices
                    foreach ($alt_array AS $key2 => $alt_info){
                        // Skip alts without defined prices
                        if (!isset($alt_info['summons'])){ continue; }
                        // Generate the token and then add to the parent shop
                        $alt_token = $robot_token.'_'.$alt_info['token'];
                        $alt_rate = in_array($robot_token, $discount_tokens) ? 10 : 20;
                        $alt_price = $alt_info['summons'] * $alt_rate;
                        $unlocked_alts_list[$alt_info['token']][$alt_token] = $alt_price;
                    }
                }
            }

            //error_log('<pre>$unlocked_alts_list = '.print_r($unlocked_alts_list, true).'</pre>');

            // If any alts groups were unlocked, loop through and extract into parent array
            if (!empty($unlocked_alts_list)){
                $backup_alts_list = $unlocked_alts_list;
                $unlocked_alts_list = array();
                $group_key = 0;
                foreach ($backup_alts_list AS $group_token => $group_list){
                    if ($group_key > $max_alt_tier_key){ break; }
                    foreach ($group_list AS $alt_token => $alt_price){
                        $unlocked_alts_list[$alt_token] = $alt_price;
                    }
                    $group_key++;
                }
            }

            //error_log('$unlocked_robot_data = '.print_r($unlocked_robot_data, true));
            //error_log('$unlocked_alts_list = '.print_r($unlocked_alts_list, true));
            //error_log('$unlocked_alts_index = '.print_r($unlocked_alts_index, true));

            // If any alts were unlocked, add them to the parent shop array
            if (!empty($unlocked_alts_list)){
                if (!in_array('alts', $this_shop_index['kalinka']['shop_kind_selling'])){ $this_shop_index['kalinka']['shop_kind_selling'][] = 'alts'; }
                $this_shop_index['kalinka']['shop_quote_selling']['alts'] = 'Would you be interested in new outfits for your robots? I\'ve already designed so many great looks!';
                $this_shop_index['kalinka']['shop_alts']['alts_selling'] = $unlocked_alts_list;
            }

            // Finally, sort the robots again so that ones which are NOT unlocked appear first (but keep the order the same otherwise)
            if (!empty($unlocked_alts_list)
                && !empty($this_shop_index['kalinka']['shop_alts']['alts_selling'])){
                $old_alts_selling = $this_shop_index['kalinka']['shop_alts']['alts_selling'];
                //error_log('$old_alts_selling = '.print_r($old_alts_selling, true));
                $new_alts_selling = array();
                foreach ($old_alts_selling AS $token => $price){
                    list($robot, $alt) = explode('_', $token);
                    $robot_info = $mmrpg_database_robots[$robot];
                    $alt_info = $unlocked_alts_index[$robot][$alt];
                    $unlocked = !empty($alt_list_unlocked[$robot]) && in_array($alt, $alt_list_unlocked[$robot]) ? true : false;
                    $summons = !empty($unlocked_robot_records[$robot]['robot_summoned']) ? $unlocked_robot_records[$robot]['robot_summoned'] : 0;
                    if (empty($alt_info['summons']) || $summons >= $alt_info['summons']){ $unlocked = true; }
                    //error_log('$robot_info = '.print_r($robot_info, true));
                    //error_log('$alt_info = '.print_r($alt_info, true));
                    //error_log('$summons = '.print_r($summons, true));
                    //error_log('$unlocked = '.($unlocked ? 'true' : 'false'));
                    if (!$unlocked){ $new_alts_selling[$token] = $price; }
                }
                $num_available_new_alts = count($new_alts_selling);
                foreach ($old_alts_selling AS $token => $price){
                    if (!isset($new_alts_selling[$token])){
                        $new_alts_selling[$token] = $price;
                    }
                }
                $this_shop_index['kalinka']['shop_alts']['alts_selling'] = $new_alts_selling;
            }

        }

    }

    // If the player has unlocked the Cossack Program, Kalinka's kiosk also has a Show Stars tab
    if ($this_battle_stars_count > 0
        && mmrpg_prototype_item_unlocked('cossack-program')){

        // Add starshow data to Kalinka's Shop
        $this_shop_index['kalinka']['shop_kind_buying'][] = 'stars';
        $this_shop_index['kalinka']['shop_quote_buying']['stars'] = 'Do you have any field or fusion stars? I\'m studying the effects of starforce and need to scan a few.';
        $this_shop_index['kalinka']['shop_stars']['stars_buying'] = array(
            'field' => ceil($mmrpg_items['field-star']['item_value'] / 2),
            'fusion' => ceil($mmrpg_items['fusion-star']['item_value'] / 2)
            );
    }

    // If Kalinka's Shop has reached sufficient levels, decrease her selling prices
    if ($this_shop_index['kalinka']['shop_level'] > 1){
        $level_discount = $this_battle_shops['kalinka']['shop_level'] / 101;

        // If her shop is selling items, discount their prices
        if (!empty($this_shop_index['kalinka']['shop_items']['items_selling'])){
            foreach ($this_shop_index['kalinka']['shop_items']['items_selling'] AS $field_kind => $field_price){
                $field_price -= round(($field_price / 2) * $level_discount);
                $this_shop_index['kalinka']['shop_items']['items_selling'][$field_kind] = $field_price;
            }
        }

        // If her shop is selling robots, discount their prices
        if (!empty($this_shop_index['kalinka']['shop_robots']['robots_selling'])){
            foreach ($this_shop_index['kalinka']['shop_robots']['robots_selling'] AS $robot_kind => $robot_price){
                $robot_price -= round(($robot_price / 2) * $level_discount);
                $this_shop_index['kalinka']['shop_robots']['robots_selling'][$robot_kind] = $robot_price;
            }
        }

    }

    // If there are no new robots to purchase but there are alts, make sure we reorder the tabs
    if ($num_available_new_robots == 0
        && $num_available_new_alts > 0){
        $this_shop_index['kalinka']['shop_kind_selling'] = array('alts', 'robots');
    }

}

//echo('<pre>$this_shop_index = '.print_r($this_shop_index, true).'</pre>');
//echo('<pre>$this_battle_shops = '.print_r($this_battle_shops, true).'</pre>');
//exit();

// Update the session with any changes to the character shops
$_SESSION[$session_token]['values']['battle_shops'] = $this_battle_shops;

?>