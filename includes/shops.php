<?php

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
        'items_buying' => array()
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



?>