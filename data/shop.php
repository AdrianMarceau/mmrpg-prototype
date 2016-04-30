<?
// Define the array to hold all the shop data
$this_shop_index = array();

// AUTO'S SHOP
$this_shop_index['auto'] = array(
    'shop_token' => 'auto',
    'shop_name' => 'Auto\'s Shop',
    'shop_owner' => 'Auto',
    'shop_unlock' => 'Complete intro field as any player.',
    'shop_seeking' => 'screws',
    'shop_colour' => 'nature',
    'shop_field' => 'light-laboratory',
    'shop_player' => 'dr-light',
    'shop_number' => 'SHOP-001',
    'shop_kind_selling' => 'items',
    'shop_kind_buying' => 'items',
    'shop_quote_selling' => 'Welcome to Auto\'s Shop! I\'ve got lots of useful items for sale, so let me know if you need anything.',
    'shop_quote_buying' => 'So you wanna sell something, eh? Let\'s see what you\'ve collected so far! Hopefully lots of screws!',
    'shop_items' => array(
        'items_selling' => array(

            'item-energy-pellet' => 100,
            'item-weapon-pellet' => 100,
            'item-energy-capsule' => 200,
            'item-weapon-capsule' => 200,
            'item-energy-tank' => 500,
            'item-weapon-tank' => 500,
            'item-extra-life' => 2000,
            'item-yashichi' => 2000,

            'item-attack-pellet' => 1000,
            'item-attack-capsule' => 2000,
            'item-defense-pellet' => 1000,
            'item-defense-capsule' => 2000,
            'item-speed-pellet' => 1000,
            'item-speed-capsule' => 2000,
            'item-super-pellet' => 2500,
            'item-super-capsule' => 5000

            ),
        'items_buying' => array(

            'item-energy-pellet' => 50,
            'item-weapon-pellet' => 50,
            'item-energy-capsule' => 100,
            'item-weapon-capsule' => 100,
            'item-energy-tank' => 250,
            'item-weapon-tank' => 250,
            'item-extra-life' => 1000,
            'item-yashichi' => 1000,

            'item-attack-pellet' => 500,
            'item-attack-capsule' => 1000,
            'item-defense-pellet' => 500,
            'item-defense-capsule' => 1000,
            'item-speed-pellet' => 500,
            'item-speed-capsule' => 1000,
            'item-super-pellet' => 1250,
            'item-super-capsule' => 2500,

            'item-screw-small' => 100,
            'item-screw-large' => 1000
            )
        )
    );

// REGGAE'S SHOP
$this_shop_index['reggae'] = array(
    'shop_token' => 'reggae',
    'shop_name' => 'Reggae\'s Shop',
    'shop_owner' => 'Reggae',
    'shop_unlock' => 'Unlock all three playable characters.',
    'shop_seeking' => 'cores',
    'shop_colour' => 'explode',
    'shop_field' => 'wily-castle',
    'shop_player' => 'dr-wily',
    'shop_number' => 'SHOP-002',
    'shop_kind_selling' => 'abilities',
    'shop_kind_buying' => 'cores',
    'shop_quote_selling' => 'Reggae\'s Shop this is! Squawk! New abilities you want! Squaaawk! Give me your zenny! Squaaaaawk!',
    'shop_quote_buying' => 'Reggae wants robot cores, robot cores! Squawk! No other items will do, will do! Squaaaaaaawk!',
    'shop_abilities' => array(
        'abilities_selling' => array(

            'energy-boost' => 3000, 'attack-boost' => 3000,
            'defense-boost' => 3000, 'speed-boost' => 3000,
            'energy-break' => 3000, 'attack-break' => 3000,
            'defense-break' => 3000, 'speed-break' => 3000,
            'energy-swap' => 3000, 'attack-swap' => 3000,
            'defense-swap' => 3000, 'speed-swap' => 3000,

            'energy-support' => 9000, 'attack-support' => 9000,
            'defense-support' => 9000, 'speed-support' => 9000,
            'energy-assault' => 9000, 'attack-assault' => 9000,
            'defense-assault' => 9000, 'speed-assault' => 9000,
            'energy-shuffle' => 9000, 'attack-shuffle' => 9000,
            'defense-shuffle' => 9000, 'speed-shuffle' => 9000,

            'damage-booster' => 12000, 'damage-breaker' => 12000,
            'recovery-booster' => 12000, 'recovery-breaker' => 12000,
            'experience-booster' => 12000, 'experience-breaker' => 12000,
            'field-support' => 12000, 'mecha-support' => 12000,

            'energy-mode' => 6000, 'attack-mode' => 6000,
            'defense-mode' => 6000, 'speed-mode' => 6000,

            'attack-blaze' => 3000, 'defense-blaze' => 3000, 'speed-blaze' => 3000,
            'attack-burn' => 3000, 'defense-burn' => 3000, 'speed-burn' => 3000,

            /*

            'rolling-cutter' => 6000, 'hyper-bomb' => 6000,
            'ice-breath' => 6000, 'fire-storm' => 6000,
            'oil-shooter' => 6000, 'thunder-strike' => 6000,
            'time-arrow' => 6000, 'super-throw' => 6000,

            'air-shooter' => 6000, 'crash-bomber' => 6000,
            'flash-stopper' => 6000, 'quick-boomerang' => 6000,
            'metal-blade' => 6000, 'bubble-spray' => 6000,
            'atomic-fire' => 6000, 'leaf-shield' => 6000,

            'rain-flush' => 6000, 'bright-burst' => 6000,
            'pharaoh-shot' => 6000, 'ring-boomerang' => 6000,
            'dust-crusher' => 6000, 'skull-barrier' => 6000,
            'dive-torpedo' => 6000, 'drill-blitz' => 6000,

            'needle-cannon' => 6000, 'magnet-missile' => 6000,
            'gemini-laser' => 6000, 'hard-knuckle' => 6000,
            'top-spin' => 6000, 'search-snake' => 6000,
            'spark-shot' => 6000, 'shadow-blade' => 6000,

            */

            )
        ),
    'shop_items' => array(
        'items_buying' => array(
            'item-core-copy' => 1000, 'item-core-crystal' => 1000, 'item-core-cutter' => 1000, 'item-core-earth' => 1000,
            'item-core-electric' => 1000, 'item-core-explode' => 1000, 'item-core-flame' => 1000, 'item-core-freeze' => 1000,
            'item-core-impact' => 1000, 'item-core-laser' => 1000, 'item-core-missile' => 1000, 'item-core-nature' => 1000,
            'item-core-shadow' => 1000, 'item-core-shield' => 1000, 'item-core-space' => 1000, 'item-core-swift' => 1000,
            'item-core-time' => 1000, 'item-core-water' => 1000, 'item-core-wind' => 1000, 'item-core-none' => 1000
            )
        )
    );

// KALINKA'S SHOP
$this_shop_index['kalinka'] = array(
    'shop_token' => 'kalinka',
    'shop_name' => 'Kalinka\'s Shop',
    'shop_owner' => 'Kalinka',
    'shop_unlock' => 'Complete the game as all three playable characters.',
    'shop_seeking' => 'stars',
    'shop_colour' => 'electric',
    'shop_field' => 'cossack-citadel',
    'shop_player' => 'dr-cossack',
    'shop_number' => 'SHOP-003',
    'shop_kind_selling' => 'fields',
    'shop_kind_buying' => 'stars',
    'shop_quote_selling' => 'Greetings and welcome to Kalinka\'s Shop! I think you\'ll enjoy the new battle fields I\'m programming.',
    'shop_quote_buying' => 'Do you have any field or fusion stars? I\'m studying the effects of starforce and need some samples.',
    'shop_fields' => array(
        'fields_selling' => array(
            'construction-site' => 48000,
            'magnetic-generator' => 48000,
            'reflection-chamber' => 48000,
            'rocky-plateau' => 48000,
            'spinning-greenhouse' => 48000,
            'serpent-column' => 48000,
            'power-plant' => 48000,
            'septic-system' => 48000
            )
        ),
    'shop_stars' => array(
        'stars_buying' => array(
            'field' => 3000,
            'fusion' => 6000
            )
        )
    );

?>