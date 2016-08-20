<?

// If the session token has not been set
if (!isset($session_token)){ $session_token = mmrpg_game_token(); }

// -- COLLECT ENVIRONMENT VARIABLES -- //

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

// Define the array to hold all the shop data
$this_shop_index = array();

// AUTO'S SHOP
$this_shop_index['auto'] = array(
        'shop_token' => 'auto',
        'shop_name' => 'Auto\'s Shop',
        'shop_owner' => 'Auto',
        'shop_unlock' => 'Complete the first chapter as Dr. Light.',
        'shop_seeking' => 'screws',
        'shop_colour' => 'nature',
        'shop_field' => 'light-laboratory',
        'shop_player' => 'dr-light',
        'shop_number' => 'SHOP-001',
        'shop_kind_selling' => array('items', 'alts'),
        'shop_kind_buying' => array('items'),
        'shop_quote_selling' => array(
            'items' => 'Welcome to Auto\'s Shop! I\'ve got lots of useful items for sale, so let me know if you need anything.',
            'alts' => 'Great news! I figured out how to pallet swap the armor of our robot master team mates. Interested?'
            ),
        'shop_quote_buying' => array(
            'items' => 'So you wanna sell something, eh? Let\'s see what you\'ve collected so far! Hopefully lots of screws!'
            ),
        'shop_items' => array(
                'items_selling' => array(

                        'energy-pellet' => 200, 'weapon-pellet' => 200,
                        'energy-capsule' => 400, 'weapon-capsule' => 400,
                        'energy-tank' => 800, 'weapon-tank' => 800

                        ),
                'items_selling2' => array(

                        'energy-pellet' => 200, 'weapon-pellet' => 200,
                        'energy-capsule' => 400, 'weapon-capsule' => 400,
                        'energy-tank' => 800, 'weapon-tank' => 800,

                        'attack-pellet' => 900, 'defense-pellet' => 900,
                        'attack-capsule' => 1800, 'defense-capsule' => 1800,
                        'speed-pellet' => 900, 'super-pellet' => 2700,
                        'speed-capsule' => 1800, 'super-capsule' => 5400

                        ),
                'items_selling3' => array(

                        'energy-pellet' => 200, 'weapon-pellet' => 200,
                        'energy-capsule' => 400, 'weapon-capsule' => 400,
                        'energy-tank' => 800, 'weapon-tank' => 800,

                        'attack-pellet' => 900, 'defense-pellet' => 900,
                        'attack-capsule' => 1800, 'defense-capsule' => 1800,
                        'speed-pellet' => 900, 'super-pellet' => 2700,
                        'speed-capsule' => 1800, 'super-capsule' => 5400,

                        'extra-life' => 1600, 'yashichi' => 1600

                        ),
                'items_selling4' => array(

                        'energy-pellet' => 200, 'weapon-pellet' => 200,
                        'energy-capsule' => 400, 'weapon-capsule' => 400,
                        'energy-tank' => 800, 'weapon-tank' => 800,

                        'attack-pellet' => 900, 'defense-pellet' => 900,
                        'attack-capsule' => 1800, 'defense-capsule' => 1800,
                        'speed-pellet' => 900, 'super-pellet' => 2700,
                        'speed-capsule' => 1800, 'super-capsule' => 5400,

                        'extra-life' => 1600, 'yashichi' => 1600,

                        /*
                        'red-score-ball' => 5000, 'blue-score-ball' => 10000,
                        'green-score-ball' => 5000, 'purple-score-ball' => 10000
                        */

                        ),
                'items_buying' => array(

                        'small-screw' => 100, 'large-screw' => 1000,

                        'energy-pellet' => 100, 'weapon-pellet' => 100,
                        'energy-capsule' => 200, 'weapon-capsule' => 200,
                        'energy-tank' => 400, 'weapon-tank' => 400,

                        'attack-pellet' => 450, 'defense-pellet' => 450,
                        'attack-capsule' => 900, 'defense-capsule' => 900,
                        'speed-pellet' => 450, 'super-pellet' => 1350,
                        'speed-capsule' => 900, 'super-capsule' => 2800,

                        'extra-life' => 800, 'yashichi' => 800,

                        /*
                        'red-score-ball-' => 1750, 'blue-score-ball' => 2500,
                        'green-score-ball' => 1750, 'purple-score-ball' => 2500,

                        'energy-upgrade' => 4000, 'weapon-upgrade' => 4000,
                        'attack-booster' => 4000, 'defense-booster' => 4000,
                        'speed-booster' => 4000, 'field-booster' => 4000,
                        'target-module' => 4000, 'charge-module' => 4000,
                        'growth-module' => 4000, 'fortune-module' => 4000
                        */

                        )
                ),
        'shop_alts' => array(
            'alts_selling' => array(
                'cut-man_alt' => 3000, 'cut-man_alt2' => 3000,
                'cut-man_alt9' => 6000, 'bomb-man_alt' => 6000,
                'bomb-man_alt2' => 6000, 'bomb-man_alt9' => 6000
                ),
            )
        );

// REGGAE'S SHOP
$this_shop_index['reggae'] = array(
    'shop_token' => 'reggae',
    'shop_name' => 'Reggae\'s Shop',
    'shop_owner' => 'Reggae',
    'shop_unlock' => 'Complete the first chapter as Dr. Wily.',
    'shop_seeking' => 'cores',
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
        'abilities_selling' => array(
            'buster-charge' => 3000, 'buster-relay' => 3000,
            'energy-boost' => 6000, 'attack-boost' => 6000,
            'defense-boost' => 6000, 'speed-boost' => 6000,
            'energy-break' => 9000, 'attack-break' => 9000,
            'defense-break' => 9000, 'speed-break' => 9000
            ),
        'abilities_selling2' => array(
            'buster-charge' => 3000, 'buster-relay' => 3000,
            'energy-boost' => 6000, 'attack-boost' => 6000,
            'defense-boost' => 6000, 'speed-boost' => 6000,
            'energy-break' => 9000, 'attack-break' => 9000,
            'defense-break' => 9000, 'speed-break' => 9000,
            'energy-swap' => 12000, 'attack-swap' => 12000,
            'defense-swap' => 12000, 'speed-swap' => 12000,
            'attack-mode' => 15000, 'defense-mode' => 15000,
            'speed-mode' => 15000, 'repair-mode' => 15000
            ),
        'abilities_selling3' => array(
            'buster-charge' => 3000, 'buster-relay' => 3000,
            'energy-boost' => 6000, 'attack-boost' => 6000,
            'defense-boost' => 6000, 'speed-boost' => 6000,
            'energy-break' => 9000, 'attack-break' => 9000,
            'defense-break' => 9000, 'speed-break' => 9000,
            'energy-swap' => 12000, 'attack-swap' => 12000,
            'defense-swap' => 12000, 'speed-swap' => 12000,
            'attack-mode' => 15000, 'defense-mode' => 15000,
            'speed-mode' => 15000, 'repair-mode' => 15000,
            'experience-booster' => 18000, 'experience-breaker' => 18000,
            'recovery-booster' => 18000, 'recovery-breaker' => 18000,
            'damage-booster' => 18000, 'damage-breaker' => 18000,
            'field-support' => 16000, 'mecha-support' => 16000
            )
        ),
    'shop_items' => array(
        'items_buying' => array(

            'none-core' => 3000, 'cutter-core' => 3000,
            'impact-core' => 3000,  'freeze-core' => 3000,
            'explode-core' => 3000, 'flame-core' => 3000,
            'electric-core' => 3000, 'time-core' => 3000,
            'earth-core' => 3000, 'wind-core' => 3000,
            'water-core' => 3000, 'swift-core' => 3000,
            'nature-core' => 3000, 'missile-core' => 3000,
            'crystal-core' => 3000, 'shadow-core' => 3000,
            'space-core' => 3000, 'shield-core' => 3000,
            'laser-core' => 3000, 'copy-core' => 3000

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
        'shop_colour' => 'electric',
        'shop_field' => 'final-destination',
        'shop_player' => 'dr-cossack',
        'shop_number' => 'SHOP-003',
        'shop_kind_selling' => array('items'),
        'shop_kind_buying' => array('stars'),
        'shop_quote_selling' => array('items' => 'Greetings and welcome to Kalinka\'s Shop! I think you\'ll enjoy the new hold items I\'m developing.'),
        'shop_quote_buying' => array('stars' => 'Do you have any field or fusion stars? I\'m studying the effects of starforce and need some samples.'),
        'shop_items' => array(
                'items_selling' => array(
                        'energy-upgrade' => 32000, 'weapon-upgrade' => 32000,
                        'attack-booster' => 32000, 'defense-booster' => 32000,
                        'target-module' => 32000, 'growth-module' => 32000
                        ),
                'items_selling2' => array(
                        'energy-upgrade' => 32000, 'weapon-upgrade' => 32000,
                        'attack-booster' => 32000, 'defense-booster' => 32000,
                        'speed-booster' => 32000, 'field-booster' => 32000,
                        'target-module' => 32000, 'charge-module' => 32000,
                        'growth-module' => 32000, 'fortune-module' => 32000
                        )
                ),
        'shop_stars' => array(
                'stars_buying' => array(
                        'field' => 6000,
                        'fusion' => 9000
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
        if (mmrpg_prototype_battles_complete($shop_player) >= MMRPG_SETTINGS_CHAPTER1_MISSIONCOUNT){
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


// -- AUTO SHOP UNLOCKS -- //

// Only continue if the shop has been unlocked
if (!empty($this_shop_index['auto'])){

    // If Auto's Shop has reached sufficient levels, expand the inventory
    if ($this_shop_index['auto']['shop_level'] >= 10){ $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling2']; }
    if ($this_shop_index['auto']['shop_level'] >= 20){ $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling3']; }
    if ($this_shop_index['auto']['shop_level'] >= 30){ $this_shop_index['auto']['shop_items']['items_selling'] = $this_shop_index['auto']['shop_items']['items_selling4']; }
    unset($this_shop_index['auto']['shop_items']['items_selling2']);
    unset($this_shop_index['auto']['shop_items']['items_selling3']);
    unset($this_shop_index['auto']['shop_items']['items_selling4']);

    // Loop through Auto's shop and remove items you do not yet own from the buying list
    $key_items = array('small-screw', 'large-screw');
    if (!empty($this_shop_index['auto']['shop_items']['items_buying'])){
        foreach ($this_shop_index['auto']['shop_items']['items_buying'] AS $token => $price){
            if (!in_array($token, $key_items) && !in_array($token, $global_unlocked_items_tokens)){
                unset($this_shop_index['auto']['shop_items']['items_buying'][$token]);
            }
        }
    }

}


// -- REGGAE SHOP UNLOCKS -- //

// Only continue if the shop has been unlocked
if (!empty($this_shop_index['reggae'])){

        // If Reggae's Shop has reached sufficient levels, expand the inventory
        if ($this_shop_index['reggae']['shop_level'] >= 20){ $this_shop_index['reggae']['shop_abilities']['abilities_selling'] = $this_shop_index['reggae']['shop_abilities']['abilities_selling2']; }
        if ($this_shop_index['reggae']['shop_level'] >= 30){ $this_shop_index['reggae']['shop_abilities']['abilities_selling'] = $this_shop_index['reggae']['shop_abilities']['abilities_selling3']; }
        unset($this_shop_index['reggae']['shop_abilities']['abilities_selling2']);
        unset($this_shop_index['reggae']['shop_abilities']['abilities_selling3']);

        /*
        // If the player has X, Reggae's Shop also sells weapons
        if (!empty($this_battle_shops['reggae']['cores_bought'])){
                array_unshift($this_shop_index['reggae']['shop_kind_selling'], 'weapons');
                $this_shop_index['reggae']['shop_quote_selling']['weapons'] = 'Reggae use cores make new weapons! Squaaak! Heroes use weapons defeat bad guys! Squaaak!';
                $this_shop_index['reggae']['shop_weapons']['weapons_selling'] = array();
        }
        */

        /*
        // Loop through all the sold cores and add associated abilities
        if (!empty($this_battle_shops['reggae']['cores_bought'])){
                foreach ($this_battle_shops['reggae']['cores_bought'] AS $item_token => $item_quantity){
                        $type_token = preg_replace('/-core$/i', '', $item_token);
                        $type_info = $mmrpg_index['types'][$type_token];
                        $level_discount = $this_battle_shops['reggae']['shop_level'] > 1 ? $this_battle_shops['reggae']['shop_level'] / 100 : 0;
                        if ($type_token == 'none'){ continue; }
                        // Unlock the Elemental Shot ability if at least one core
                        if ($item_quantity >= 1){
                                $ability_price = 6000;
                                $ability_token = $type_token.'-shot';
                                if (!empty($level_discount)){ $ability_price -= floor($level_discount * $ability_price); }
                                $this_shop_index['reggae']['shop_weapons']['weapons_selling'][$ability_token] = $ability_price;
                        }
                        // Unlock the Elemental Buster ability if at least three cores
                        if ($item_quantity >= 3){
                                $ability_price = 9000;
                                $ability_token = $type_token.'-buster';
                                if (!empty($level_discount)){ $ability_price -= floor($level_discount * $ability_price); }
                                $this_shop_index['reggae']['shop_weapons']['weapons_selling'][$ability_token] = $ability_price;
                        }
                        // Unlock the Elemental Overdrive ability if at least nine cores
                        if ($item_quantity >= 9){
                                $ability_price = 12000;
                                $ability_token = $type_token.'-overdrive';
                                if (!empty($level_discount)){ $ability_price -= floor($level_discount * $ability_price); }
                                $this_shop_index['reggae']['shop_weapons']['weapons_selling'][$ability_token] = $ability_price;
                        }
                }
        }
        */

        // If Robots or Abilities have been unlocked, increase the core selling prices
        if (!empty($global_unlocked_robots_cores) || !empty($global_unlocked_abilities_types)){
                if (!empty($this_shop_index['reggae']['shop_items']['items_buying'])){
                        $items_list = $this_shop_index['reggae']['shop_items']['items_buying'];
                        foreach ($items_list AS $item_token => $item_price){
                                $type_token = preg_replace('/-core$/', '', $item_token);
                                $robot_boost = !empty($global_unlocked_robots_cores[$type_token]) ? $global_unlocked_robots_cores[$type_token] : 0;
                                $ability_boost = !empty($global_unlocked_abilities_types[$type_token]) ? $global_unlocked_abilities_types[$type_token] : 0;
                                $robot_price_boost = ceil($robot_boost * 100);
                                $ability_price_boost = ceil($ability_boost * 10);
                                $item_price += $robot_price_boost;
                                $item_price += $ability_price_boost;
                                $this_shop_index['reggae']['shop_items']['items_buying'][$item_token] = $item_price;
                        }
                }
        }

        // If Reggae's Shop has reached sufficient levels, decrease his selling prices
        if ($this_shop_index['reggae']['shop_level'] > 1){
                $level_discount = $this_battle_shops['reggae']['shop_level'] / 100;
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

        // Do not allow Kalinka to sell items yet
        $this_shop_index['kalinka']['shop_kind_selling'] = array();
        unset($this_shop_index['kalinka']['shop_quote_selling']['items']);
        unset($this_shop_index['kalinka']['shop_items']);

        /*
        // If Kalinka's Shop has reached sufficient levels, expand the inventory
        if ($this_shop_index['kalinka']['shop_level'] >= 30){ $this_shop_index['kalinka']['shop_items']['items_selling'] = $this_shop_index['kalinka']['shop_items']['items_selling2']; }
        unset($this_shop_index['kalinka']['shop_items']['items_selling2']);
        */

        // If the player has completed the prototype, Kalinka's Shop also sells fields
        if (mmrpg_prototype_complete()){
                $this_shop_index['kalinka']['shop_kind_selling'][] = 'fields';
                $this_shop_index['kalinka']['shop_quote_selling']['fields'] = 'I think I\'ve discoved a way to generate new starforce, but it\'ll require additional research. Interested?';
                $this_shop_index['kalinka']['shop_fields']['fields_selling'] = array(
                        'construction-site' => 48000, 'magnetic-generator' => 48000,
                        'reflection-chamber' => 48000, 'rocky-plateau' => 48000,
                        'spinning-greenhouse' => 48000, 'serpent-column' => 48000,
                        'power-plant' => 48000, 'septic-system' => 48000
                        );
        }

        // If Kalinka's Shop has reached sufficient levels, decrease her selling prices
        if ($this_shop_index['kalinka']['shop_level'] > 1){
                $level_discount = $this_battle_shops['kalinka']['shop_level'] / 100;

                /*
                // If her shop is selling items, discount their prices
                if (!empty($this_shop_index['kalinka']['shop_items']['items_selling'])){
                        foreach ($this_shop_index['kalinka']['shop_items']['items_selling'] AS $field_kind => $field_price){
                                $field_price -= round(($field_price / 2) * $level_discount);
                                $this_shop_index['kalinka']['shop_items']['items_selling'][$field_kind] = $field_price;
                        }
                }
                */

                // If her shop is selling fields, discount their prices
                if (!empty($this_shop_index['kalinka']['shop_fields']['fields_selling'])){
                        foreach ($this_shop_index['kalinka']['shop_fields']['fields_selling'] AS $field_kind => $field_price){
                                $field_price -= round(($field_price / 2) * $level_discount);
                                $this_shop_index['kalinka']['shop_fields']['fields_selling'][$field_kind] = $field_price;
                        }
                }

        }

}

// Update the session with any changes to the character shops
$_SESSION[$session_token]['values']['battle_shops'] = $this_battle_shops;

?>