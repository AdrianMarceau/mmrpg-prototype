<?php

// Define the array to hold all the shop data
$this_shop_index = array();

// AUTO'S SHOP
$this_shop_index['auto'] = array(
    'shop_token' => 'auto',
    'shop_source' => 'robots',
    'shop_name' => 'Auto\'s Shop',
    'shop_owner' => 'Auto',
    'shop_unlock' => 'Find a piece of Auto\'s source code in Free-Roam.',
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
        ),
    'shop_max_level' => 10,
    'shop_base_cost' => 1000,
    'shop_has_frame' => true,
    'shop_in_overworld' => true
    );

// REGGAE'S SHOP
$this_shop_index['reggae'] = array(
    'shop_token' => 'reggae',
    'shop_source' => 'robots',
    'shop_name' => 'Reggae\'s Shop',
    'shop_owner' => 'Reggae',
    'shop_unlock' => 'Find a piece of Reggae\'s source code in Free-Roam.',
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
        'items_buying' => array()
        ),
    'shop_max_level' => 20,
    'shop_base_cost' => 15000,
    'shop_has_frame' => true,
    'shop_in_overworld' => true
    );


// KALINKA'S SHOP
$this_shop_index['kalinka'] = array(
    'shop_token' => 'kalinka',
    'shop_source' => 'players',
    'shop_name' => 'Kalinka\'s Shop',
    'shop_owner' => 'Kalinka',
    'shop_unlock' => 'Find a piece of Kalinka\'s source code in Free-Roam.',
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
    'shop_alts' => array(),
    'shop_max_level' => 30,
    'shop_base_cost' => 30000,
    'shop_has_frame' => true,
    'shop_in_overworld' => true
    );


// PLUM'S GARAGE
$this_shop_index['plum'] = array(
    'shop_token' => 'plum',
    'shop_source' => 'robots',
    'shop_name' => 'Plum\'s Garage',
    'shop_owner' => 'Plum',
    'shop_unlock' => 'Find a piece of Plum\'s source code in Free-Roam.',
    'shop_unlock_message' => 'You Unlocked Plum\'s Garage!',
    'shop_seeking' => 'pellets',
    'shop_seeking_text' => 'Stat Mechanic',
    'shop_seeking_image' => 'super-pellet',
    'shop_colour' => 'crystal',
    'shop_field' => 'sonic-highway',
    'shop_player' => 'dr-lalinde',
    'shop_number' => 'SHOP-004',
    'shop_kind_selling' => array(),
    'shop_kind_buying' => array(),
    'shop_quote_selling' => array(),
    'shop_quote_buying' => array(),
    'shop_alts' => array(),
    'shop_max_level' => 99,
    'shop_base_cost' => 999999,
    'shop_has_frame' => false,
    'shop_in_overworld' => true
    );



?>