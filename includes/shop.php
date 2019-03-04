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

// Collect an item and ability index for reference
$mmrpg_items = rpg_item::get_index();
$mmrpg_abilities = rpg_ability::get_index();

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
    global $mmrpg_abilities;
    $ability_prices = array();
    foreach ($ability_tokens AS $ability_token){
        if (!isset($mmrpg_abilities[$ability_token])){ continue; }
        $ability_info = $mmrpg_abilities[$ability_token];
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
            'alts' => 'Great news! I designed some alternate outfits for the robots on our team. Interested in a new look?'
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
            'damage-booster', 'damage-breaker'
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
        'shop_kind_selling' => array('items'),
        'shop_kind_buying' => array(),
        'shop_quote_selling' => array('items' => 'Greetings and welcome to Kalinka\'s Shop! I think you\'ll enjoy the new hold items I\'m developing.'),
        'shop_quote_buying' => array(),
        'shop_items' => array(
            'items_selling' => get_items_with_prices(
                'energy-upgrade', 'weapon-upgrade',
                'attack-booster', 'defense-booster',
                'growth-module', 'fortune-module'
                ),
            'items_selling2' => get_items_with_prices(
                'energy-upgrade', 'weapon-upgrade',
                'attack-booster', 'defense-booster',
                'speed-booster', 'field-booster',
                'growth-module', 'fortune-module',
                'target-module', 'charge-module'
                ),
            'items_selling3' => get_items_with_prices(
                'energy-upgrade', 'weapon-upgrade',
                'attack-booster', 'defense-booster',
                'speed-booster', 'field-booster',
                'growth-module', 'fortune-module',
                'target-module', 'charge-module',
                'reverse-module'
                ),
            'items_selling4' => get_items_with_prices(
                'energy-upgrade', 'weapon-upgrade',
                'attack-booster', 'defense-booster',
                'speed-booster', 'field-booster',
                'growth-module', 'fortune-module',
                'target-module', 'charge-module',
                'reverse-module', 'battery-circuit',
                'sponge-circuit', 'forge-circuit',
                'sapling-circuit'
                )
            )
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

    // If Auto's Shop has reached sufficient levels, expand the inventory
    if ($this_shop_index['auto']['shop_level'] >= 10){
        $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling2'];
        unset($this_shop_index['auto']['shop_items']['items_selling2']);
    }
    if ($this_shop_index['auto']['shop_level'] >= 20){
        $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling3'];
        unset($this_shop_index['auto']['shop_items']['items_selling3']);
    }
    if ($this_shop_index['auto']['shop_level'] >= 30){
        $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling4'];
        unset($this_shop_index['auto']['shop_items']['items_selling4']);
    }

    // If the player has unlocked the Dress Codes, Auto's Shop also sells alts
    if (mmrpg_prototype_item_unlocked('dress-codes')){

        // Generate the max tier of alts to sell based on level
        $max_alt_tier_key = floor($this_shop_index['auto']['shop_level'] / 10);

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
            $allowed_tokens_string = "'".implode("', '", $allowed_tokens)."'";
            $unlocked_robot_data = $db->get_array_list("SELECT
                robot_token,
                robot_image_alts
                FROM mmrpg_index_robots
                WHERE
                robot_token IN ({$allowed_tokens_string})
                AND robot_image_alts <> ''
                AND robot_image_alts <> '[]'
                ORDER BY robot_order ASC
                ;");

            // If alts were found, loop through and collect their details
            if (!empty($unlocked_robot_data)){
                foreach ($unlocked_robot_data AS $key => $robot_info){
                    // Collect the alt data and decompress its fields
                    $robot_token = $robot_info['robot_token'];
                    $alt_array = json_decode($robot_info['robot_image_alts'], true);
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

            //echo('<pre>$unlocked_robot_data = '.print_r($unlocked_robot_data, true).'</pre>');
            //echo('<pre>$unlocked_alts_list = '.print_r($unlocked_alts_list, true).'</pre>');
            //exit();

            // If any alts were unlocked, add them to the parent shop array
            if (!empty($unlocked_alts_list)){
                $this_shop_index['auto']['shop_kind_selling'][] = 'alts';
                $this_shop_index['auto']['shop_alts']['alts_selling'] = $unlocked_alts_list;
            }

        }

    }


    // Loop through Auto's shop and remove items you do not yet own from the buying list
    $key_items = array('small-screw', 'large-screw');
    if (!empty($this_shop_index['auto']['shop_items']['items_buying'])){
        foreach ($this_shop_index['auto']['shop_items']['items_buying'] AS $token => $price){
            if (!in_array($token, $key_items) && !in_array($token, $global_unlocked_items_tokens)){
                unset($this_shop_index['auto']['shop_items']['items_buying'][$token]);
            }
        }
    }

    // If Robots or Abilities have been unlocked, increase the core selling prices
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

        // If Reggae's Shop has reached sufficient levels, expand the inventory
        if ($this_shop_index['reggae']['shop_level'] >= 20){ $this_shop_index['reggae']['shop_abilities']['abilities_selling'] = $this_shop_index['reggae']['shop_abilities']['abilities_selling2']; }
        if ($this_shop_index['reggae']['shop_level'] >= 30){ $this_shop_index['reggae']['shop_abilities']['abilities_selling'] = $this_shop_index['reggae']['shop_abilities']['abilities_selling3']; }
        if ($this_shop_index['reggae']['shop_level'] >= 40){ $this_shop_index['reggae']['shop_abilities']['abilities_selling'] = $this_shop_index['reggae']['shop_abilities']['abilities_selling4']; }
        if ($this_shop_index['reggae']['shop_level'] >= 50){ $this_shop_index['reggae']['shop_abilities']['abilities_selling'] = $this_shop_index['reggae']['shop_abilities']['abilities_selling5']; }
        if ($this_shop_index['reggae']['shop_level'] >= 60){ $this_shop_index['reggae']['shop_abilities']['abilities_selling'] = $this_shop_index['reggae']['shop_abilities']['abilities_selling6']; }
        unset($this_shop_index['reggae']['shop_abilities']['abilities_selling2']);
        unset($this_shop_index['reggae']['shop_abilities']['abilities_selling3']);
        unset($this_shop_index['reggae']['shop_abilities']['abilities_selling4']);
        unset($this_shop_index['reggae']['shop_abilities']['abilities_selling5']);
        unset($this_shop_index['reggae']['shop_abilities']['abilities_selling6']);

        // If the player has unlocked the Weapon Codes, Reggae's Shop also sells weapons
        if (mmrpg_prototype_item_unlocked('weapon-codes')){

            // Update the shop parameters to include code-based weapons
            array_unshift($this_shop_index['reggae']['shop_kind_selling'], 'weapons');
            $this_shop_index['reggae']['shop_quote_selling']['weapons'] = 'Reggae use cores make new weapons! Squaaak! Heroes use weapons defeat bad guys! Squaaak!';

            // Define the weapon selling array and start it empty
            $this_shop_index['reggae']['shop_weapons']['weapons_selling'] = array();

            // If the player has sold any cores, loop through them and add associated abilities
            $level_discount = $this_battle_shops['reggae']['shop_level'] > 1 ? $this_battle_shops['reggae']['shop_level'] / 101 : 0;
            if (!empty($this_battle_shops['reggae']['cores_bought'])){
                foreach ($this_battle_shops['reggae']['cores_bought'] AS $item_token => $item_quantity){
                    if (preg_match('/^item-core-/i', $item_token)){ $type_token = preg_replace('/^item-core-/i', '', $item_token); }
                    else { $type_token = preg_replace('/-core$/i', '', $item_token); }
                    $type_info = $mmrpg_index['types'][$type_token];
                    if (!isset($core_level_index[$type_token])){ $core_level_index[$type_token] = 0; }
                    $core_level_index[$type_token] += $item_quantity;
                }
            }

            // Collect a list of all abilities already unlocked
            $unlocked_ability_tokens = rpg_game::ability_tokens_unlocked();

            // Unlock the Copy Abilities when the user has maxed out their Copy Gauge in Reggae's Shop
            if (!empty($core_level_index['copy']) && $core_level_index['copy'] >= 9){
                $prices = get_abilities_with_prices('copy-shot', 'copy-soul');
                foreach ($prices AS $token => $price){ $this_shop_index['reggae']['shop_weapons']['weapons_selling'][$token] = $price; }
            }

            // Unlock the Core Abilities when the user has maxed out their Neutral Gauge in Reggae's Shop
            if (!empty($core_level_index['none']) && $core_level_index['none'] >= 9){
                $prices = get_abilities_with_prices('core-shield', 'core-laser');
                foreach ($prices AS $token => $price){ $this_shop_index['reggae']['shop_weapons']['weapons_selling'][$token] = $price; }
            }

            // Unlock the Omega Abilities as soon as the player has unlocked the Omega Seed item
            if (mmrpg_prototype_item_unlocked('omega-seed')){
                $prices = get_abilities_with_prices('omega-pulse', 'omega-wave');
                foreach ($prices AS $token => $price){ $this_shop_index['reggae']['shop_weapons']['weapons_selling'][$token] = $price; }
            }

            /*
            echo('<pre>$core_level_index = '.print_r($core_level_index, true).'</pre><hr />');
            echo('<pre>rpg_game::robots_unlocked(dr-light) = '.print_r(rpg_game::robots_unlocked('dr-light', true), true).'</pre><hr />');
            echo('<pre>rpg_game::robots_unlocked(dr-wily) = '.print_r(rpg_game::robots_unlocked('dr-wily', true), true).'</pre><hr />');
            echo('<pre>rpg_game::robots_unlocked(dr-cossack) = '.print_r(rpg_game::robots_unlocked('dr-cossack', true), true).'</pre><hr />');
            echo('<pre>$this_shop_index[reggae][shop_weapons][weapons_selling] = '.print_r($this_shop_index['reggae']['shop_weapons']['weapons_selling'], true).'</pre><hr />');
            exit();
            */

            // Define an array to keep track of all relevant ability types
            $temp_ability_types = array();
            $temp_ability_order = array_keys($mmrpg_database_abilities);
            $temp_core_abilities = array();
            $temp_other_abilities = array();

            // Loop through and add any SHOT/BUSTER/OVERDRIVE abilities that have the required cores
            foreach ($mmrpg_database_abilities AS $ability_token => $ability_info){

                // Index the typing for this ability for later reference
                $temp_ability_types[$ability_token] = array($ability_info['ability_type'], $ability_info['ability_type2']);

                // Skip if this ability is incomplete
                if (!$ability_info['ability_flag_complete']){ continue; }
                // Skip if this ability is not of the master class
                elseif ($ability_info['ability_class'] != 'master'){ continue; }
                // Skip if this ability has no primary type or its copy
                elseif (empty($ability_info['ability_type']) || $ability_info['ability_type'] == 'copy'){ continue; }
                // Skip if this is a hero ability from MM00 group
                elseif (strstr($ability_info['ability_group'], 'MM00/')){ continue; }
                // Skip if this is not a shot/buster/overdrive ability from the MMRPG/Weapons/ group
                elseif (!strstr($ability_info['ability_group'], 'MMRPG/Weapons/')){ continue; }

                // Calculate this ability's tier based on weapon energy
                if (strstr($ability_token, '-shot')){ $ability_tier = 1; }
                elseif (strstr($ability_token, '-buster')){ $ability_tier = 2; }
                elseif (strstr($ability_token, '-overdrive')){ $ability_tier = 3; }
                else { continue; }

                // Add this ability to the core list
                $temp_core_abilities[] = $ability_token;

                // Define the price based on its weapon energy
                $ability_price = $ability_info['ability_energy'] * MMRPG_SETTINGS_SHOP_ABILITY_PRICE;
                if (empty($ability_price)){ $ability_price = MMRPG_SETTINGS_SHOP_ABILITY_PRICE; }

                // Define the cores required based on its tier
                $cores_required = $ability_tier * 3;

                // If the user has not sold enough cores, skip this ability
                $cores_sold = !empty($core_level_index[$ability_info['ability_type']]) ? $core_level_index[$ability_info['ability_type']] : 0;
                if ($cores_sold < $cores_required){ continue; }

                // Apply a discount to the price if the shop is a high enough level
                if (!empty($level_discount)){ $ability_price -= floor(($ability_price / 2) * $level_discount); }

                // Append this ability to the parent weapon selling array
                $this_shop_index['reggae']['shop_weapons']['weapons_selling'][$ability_token] = $ability_price;

            }

            // Loop through abilities again and add any remaining elemental abilities
            foreach ($mmrpg_database_abilities AS $ability_token => $ability_info){

                // Index the typing for this ability for later reference
                $temp_ability_types[$ability_token] = array($ability_info['ability_type'], $ability_info['ability_type2']);

                // Skip if this ability is incomplete
                if (!$ability_info['ability_flag_complete']){ continue; }
                // Skip if this ability is not of the master class
                elseif ($ability_info['ability_class'] != 'master'){ continue; }
                // Skip if this ability has no primary type
                elseif (empty($ability_info['ability_type']) || $ability_info['ability_type'] == 'copy'){ continue; }
                // Skip if this is a hero ability from MM00 group
                elseif (strstr($ability_info['ability_group'], 'MM00/')){ continue; }
                // Skip if this is a shot/buster/overdrive ability from the MMRPG/Weapons/ group
                elseif (strstr($ability_info['ability_group'], 'MMRPG/Weapons/')){ continue; }

                // Add this ability to the other list
                $temp_other_abilities[] = $ability_token;

                // Calculate this ability's tier based on weapon energy
                $ability_tier = 1;
                if ($ability_info['ability_energy'] > 4){ $ability_tier += 1; }
                if ($ability_info['ability_energy'] > 8){ $ability_tier += 1; }

                // Define the price based on its weapon energy
                $ability_price = $ability_info['ability_energy'] * MMRPG_SETTINGS_SHOP_ABILITY_PRICE;
                if (empty($ability_price)){ $ability_price = MMRPG_SETTINGS_SHOP_ABILITY_PRICE; }

                // Define the cores required based on its tier
                $cores_required = $ability_tier * 3;

                // If the user has not sold enough cores, skip this ability
                $cores_sold = !empty($core_level_index[$ability_info['ability_type']]) ? $core_level_index[$ability_info['ability_type']] : 0;
                if ($cores_sold < $cores_required){ continue; }

                // Apply a discount to the price if the shop is a high enough level
                if (!empty($level_discount)){ $ability_price -= floor($level_discount * $ability_price); }

                // Append this ability to the parent weapon selling array
                $this_shop_index['reggae']['shop_weapons']['weapons_selling'][$ability_token] = $ability_price;

            }

        }

        // If not empty, always sort the weapons by their core type so it matches sidebar
        if (!empty($this_shop_index['reggae']['shop_weapons']['weapons_selling'])){
            $new_weapons_selling = array();
            $old_weapons_selling = $this_shop_index['reggae']['shop_weapons']['weapons_selling'];
            $ordered_type_tokens = array_keys($mmrpg_database_types);
            $weapons_selling_tokens = array_keys($old_weapons_selling);
            usort($weapons_selling_tokens, function($a, $b) use($ordered_type_tokens, $temp_ability_types, $temp_ability_order, $temp_core_abilities){
                $atpos1 = array_search($temp_ability_types[$a][0], $ordered_type_tokens);
                $btpos1 = array_search($temp_ability_types[$b][0], $ordered_type_tokens);
                $acore = in_array($a, $temp_core_abilities) ? true : false;
                $bcore = in_array($b, $temp_core_abilities) ? true : false;
                $apos1 = array_search($a, $temp_ability_order);
                $bpos1 = array_search($b, $temp_ability_order);
                if ($atpos1 < $btpos1){ return -1; }
                elseif ($atpos1 > $btpos1){ return 1; }
                elseif ($acore && !$bcore){ return -1; }
                elseif (!$acore && $bcore){ return 1; }
                if ($apos1 < $bpos1){ return -1; }
                elseif ($apos1 > $bpos1){ return 1; }
                else { return 0; }
                });
            foreach ($weapons_selling_tokens AS $key => $token){ $new_weapons_selling[$token] = $old_weapons_selling[$token]; }
            $this_shop_index['reggae']['shop_weapons']['weapons_selling'] = $new_weapons_selling;
            //echo('<pre>$ordered_type_tokens = '.implode(', ', $ordered_type_tokens).'</pre>');
            //echo('<pre>$old_weapons_selling = '.implode(', ', array_keys($old_weapons_selling)).'</pre>');
            //echo('<pre>$new_weapons_selling = '.implode(', ', array_keys($new_weapons_selling)).'</pre>');
            //exit();
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

}


// -- KALINKA SHOP UNLOCKS -- //

// Only continue if the shop has been unlocked
if (!empty($this_shop_index['kalinka'])){

    // If Kalinka's Shop has reached sufficient levels, expand the inventory
    if ($this_shop_index['kalinka']['shop_level'] >= 25){ $this_shop_index['kalinka']['shop_items']['items_selling'] = $this_shop_index['kalinka']['shop_items']['items_selling2']; }
    if ($this_shop_index['kalinka']['shop_level'] >= 50){ $this_shop_index['kalinka']['shop_items']['items_selling'] = $this_shop_index['kalinka']['shop_items']['items_selling3']; }
    if ($this_shop_index['kalinka']['shop_level'] >= 75){ $this_shop_index['kalinka']['shop_items']['items_selling'] = $this_shop_index['kalinka']['shop_items']['items_selling4']; }
    unset($this_shop_index['kalinka']['shop_items']['items_selling2'], $this_shop_index['kalinka']['shop_items']['items_selling3'], $this_shop_index['kalinka']['shop_items']['items_selling4']);

    // Collect values for all of Kalinka's parts so we can add them to Auto's shop
    $kalinka_part_values = call_user_func_array('get_items_with_values', array_keys($this_shop_index['kalinka']['shop_items']['items_selling']));
    if (!empty($kalinka_part_values)){
        foreach ($kalinka_part_values AS $token => $value){
            if (isset($this_shop_index['auto']['shop_items']['items_buying'][$token])){ continue; }
            elseif (empty($value)){ continue; }
            $this_shop_index['auto']['shop_items']['items_buying'][$token] = $value;
        }
    }

    // If the player has unlocked the Legacy Codes, Kalinka's Shop also sells legacy fields & robots
    if (mmrpg_prototype_item_unlocked('legacy-codes')
        || mmrpg_prototype_item_unlocked('robot-codes')
        || mmrpg_prototype_item_unlocked('field-codes')){

        // Collect a list of robot masters that we're allowed to sell
        $buyable_robots = $db->get_array_list("SELECT
            robot_token
            FROM mmrpg_index_robots
            WHERE
            robot_flag_published = 1
            AND robot_flag_complete = 1
            AND robot_flag_unlockable = 1
            AND robot_number NOT LIKE 'RPG-%'
            AND robot_number NOT LIKE 'PCR-%'
            AND robot_game NOT IN ('MM00', 'MM01', 'MM02', 'MM04')
            ORDER BY
            robot_order ASC
            ;", 'robot_token');

        // Ensure there are robots to see before showing them
        if (!empty($buyable_robots)){

            // Add robot data to Kalinka's Shop
            $this_shop_index['kalinka']['shop_kind_selling'][] = 'robots';
            $this_shop_index['kalinka']['shop_quote_selling']['robots'] = 'Would you like me to build you a new robot or two? I created a few blueprints using your scan data.';
            $this_shop_index['kalinka']['shop_robots']['robots_selling'] = array();
            $buyable_robots = array_keys($buyable_robots);
            foreach ($buyable_robots AS $token){
                $this_shop_index['kalinka']['shop_robots']['robots_selling'][$token] = MMRPG_SETTINGS_SHOP_ROBOT_PRICE;
            }

        }

        // Add field data to Kalinka's Shop
        $this_shop_index['kalinka']['shop_kind_selling'][] = 'fields';
        $this_shop_index['kalinka']['shop_quote_selling']['fields'] = 'I\'ve discovered that we can generate new stars using the data of legacy battle fields. Interested?';
        $this_shop_index['kalinka']['shop_fields']['fields_selling'] = array(
            'construction-site' => MMRPG_SETTINGS_SHOP_FIELD_PRICE, 'magnetic-generator' => MMRPG_SETTINGS_SHOP_FIELD_PRICE,
            'reflection-chamber' => MMRPG_SETTINGS_SHOP_FIELD_PRICE, 'rocky-plateau' => MMRPG_SETTINGS_SHOP_FIELD_PRICE,
            'spinning-greenhouse' => MMRPG_SETTINGS_SHOP_FIELD_PRICE, 'serpent-column' => MMRPG_SETTINGS_SHOP_FIELD_PRICE,
            'power-plant' => MMRPG_SETTINGS_SHOP_FIELD_PRICE, 'septic-system' => MMRPG_SETTINGS_SHOP_FIELD_PRICE
            );

    }

    // If the player has unlocked the Cossack Program, Kalinka's Shop also sells fields
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

        // If her shop is selling fields, discount their prices
        if (!empty($this_shop_index['kalinka']['shop_fields']['fields_selling'])){
            foreach ($this_shop_index['kalinka']['shop_fields']['fields_selling'] AS $field_kind => $field_price){
                $field_price -= round(($field_price / 2) * $level_discount);
                $this_shop_index['kalinka']['shop_fields']['fields_selling'][$field_kind] = $field_price;
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

}

//echo('<pre>$this_shop_index = '.print_r($this_shop_index, true).'</pre>');
//echo('<pre>$this_battle_shops = '.print_r($this_battle_shops, true).'</pre>');
//exit();

// Update the session with any changes to the character shops
$_SESSION[$session_token]['values']['battle_shops'] = $this_battle_shops;

?>