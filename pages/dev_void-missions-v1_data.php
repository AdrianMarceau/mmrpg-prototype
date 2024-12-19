<?

// Predefine the item groups to be used in the void cauldron item palette
$void_item_groups_index = array(

    // -- STEP (MANIFEST [DATA]) -- //
    array(
        'name' => 'Manifest',
        'label' => 'Data',
        'groups' => array(
            'quanta-screws' => array(
                'name' => 'Quanta Screws',
                'color' => 'water',
                'rowline' => 1,
                'colspan' => 3,
                'items' => array(
                    'small-screw', 'large-screw', 'hyper-screw',
                    )
                ),
            'spread-cores' => array(
                'name' => 'Spread Cores',
                'color' => 'laser',
                'rowline' => 2,
                'colspan' => 5,
                'items' => array(
                    'cutter-core', 'impact-core', 'freeze-core', 'explode-core', 'flame-core',
                    'electric-core', 'time-core', 'earth-core', 'wind-core', 'water-core',
                    'swift-core', 'nature-core', 'missile-core', 'crystal-core', 'shadow-core',
                    'space-core', 'shield-core', 'laser-core', 'copy-core', 'none-core',
                    )
                ),
            'data-modules' => array(
                'name' => 'Data Modules',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 3,
                'items' => array(
                    'charge-module', 'spreader-module', 'target-module',
                    )
                ),
            ),
        ),

    // -- STEP (UPGRADE [POWER]) -- //
    array(
        'name' => 'Upgrade',
        'label' => 'Power',
        'groups' => array(
            'level-boost' => array(
                'name' => 'Level-Boost Edibles',
                'color' => 'energy_electric',
                'rowline' => 1,
                'colspan' => 3,
                'items' => array(
                    'energy-pellet', 'energy-capsule', 'energy-tank',
                    )
                ),
            'max-level-boost' => array(
                'name' => 'Max Level-Boost Redirects',
                'color' => 'electric',
                'rowline' => 1,
                'colspan' => 1,
                'items' => array(
                    'energy-upgrade',
                    )
                ),
            'forte-boost' => array(
                'name' => 'Forte-Boost Edibles',
                'color' => 'weapons_shield',
                'rowline' => 2,
                'colspan' => 3,
                'items' => array(
                    'weapon-pellet', 'weapon-capsule', 'weapon-tank',
                    )
                ),
            'max-forte-boost' => array(
                'name' => 'Max Level-Boost Redirects',
                'color' => 'shield',
                'rowline' => 2,
                'colspan' => 1,
                'items' => array(
                    'weapon-upgrade',
                    )
                ),
            'power-balancers' => array(
                'name' => 'Level Balancers',
                'color' => 'copy',
                'rowline' => 5,
                'colspan' => 3,
                'items' => array(
                    'fortune-module', 'growth-module', 'salvage-module',
                    )
                ),
            ),
        ),

    // -- STEP (BOOST [STATS]) -- //
    array(
        'name' => 'Boost',
        'label' => 'Stats',
        'groups' => array(
            'attack-boost' => array(
                'name' => 'Attack-Boost Edibles',
                'color' => 'attack',
                'rowline' => 1,
                'colspan' => 3,
                'items' => array(
                    'attack-pellet', 'attack-capsule', 'attack-booster',
                    )
                ),
            'defense-boost' => array(
                'name' => 'Defense-Boost Edibles',
                'color' => 'defense',
                'rowline' => 2,
                'colspan' => 3,
                'items' => array(
                    'defense-pellet',  'defense-capsule', 'defense-booster',
                    )
                ),
            'speed-boost' => array(
                'name' => 'Speed-Boost Edibles',
                'color' => 'speed',
                'rowline' => 3,
                'colspan' => 3,
                'items' => array(
                    'speed-pellet', 'speed-capsule', 'speed-booster',
                    )
                ),
            'super-boost' => array(
                'name' => 'Omni-Boost Edibles',
                'color' => 'shield',
                'rowline' => 4,
                'colspan' => 3,
                'items' => array(
                    'super-pellet', 'super-capsule', 'field-booster',
                    )
                ),
            ),
        ),

    // -- STEP (REDIRECT [FORM]) -- //
    array(
        'name' => 'Redirect',
        'label' => 'Form',
        'groups' => array(
            'queue-rotators' => array(
                'name' => 'Queue Rotators',
                'color' => 'energy_weapons',
                'rowline' => 1,
                'colspan' => 3,
                'items' => array(
                    'mecha-whistle', 'extra-life', 'yashichi',
                    ),
                ),
            'attack-diverter' => array(
                'name' => 'Attack Diverter',
                'color' => 'defense_speed',
                'rowline' => 2,
                'colspan' => 1,
                'items' => array(
                    'attack-diverter',
                    )
                ),
            'defense-diverter' => array(
                'name' => 'Defense Diverter',
                'color' => 'speed_attack',
                'rowline' => 2,
                'colspan' => 1,
                'items' => array(
                    'defense-diverter',
                    )
                ),
            'speed-diverter' => array(
                'name' => 'Speed Diverter',
                'color' => 'attack_defense',
                'rowline' => 2,
                'colspan' => 1,
                'items' => array(
                    'speed-diverter',
                    )
                ),
            'queue-mods' => array(
                'name' => 'Queue Mods',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 3,
                'items' => array(
                    'hyperscan-module', 'copycat-module', 'reverse-module',
                    ),
                ),
            ),
        ),

    // -- STEP (DISTORT [CONTEXT]) -- //
    array(
        'name' => 'Distort',
        'label' => 'Context',
        'groups' => array(
            'junk' => array(
                'name' => 'Junk Items',
                'color' => 'time',
                'rowline' => 1,
                'colspan' => 4,
                'items' => array(
                    'guard-module', 'persist-module', 'xtreme-module', 'overkill-module',
                    'hourglass-module', 'magnet-module', 'transport-module', 'bulwark-module',
                    )
                ),
            'elemental-mods' => array(
                'name' => 'Elemental Mods',
                'color' => 'time',
                'rowline' => 2,
                'colspan' => 3,
                'items' => array(
                    'battery-circuit', 'sponge-circuit', 'forge-circuit',
                    'sapling-circuit', 'chrono-circuit', 'cosmo-circuit',
                    )
                ),
            'power-balancers' => array(
                'name' => 'Power Balancers',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 1,
                'items' => array(
                    'uptick-module', 'siphon-module',
                    )
                ),
            'field-mods' => array(
                'name' => 'Field Mods',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 1,
                'items' => array(
                    'repair-module', 'gambit-module',
                    )
                ),
            'field-mods2' => array(
                'name' => 'Field Mods 2',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 1,
                'items' => array(
                    'alchemy-module', 'distill-module',
                    )
                ),
            ),
        ),

    );

// Disable certain void items until they have a purpose (TEMP PROBABLY)
$void_items_disabled = array(
    'salvage-module', 'growth-module', 'fortune-module',
    'field-booster',
    'hyperscan-module', 'copycat-module', 'reverse-module',
    'guard-module', 'persist-module', 'xtreme-module', 'overkill-module',
    'hourglass-module', 'magnet-module', 'transport-module', 'bulwark-module',
    'battery-circuit', 'sponge-circuit', 'forge-circuit',
    'sapling-circuit', 'chrono-circuit', 'cosmo-circuit',
    'uptick-module', 'siphon-module',
    'repair-module', 'gambit-module',
    'alchemy-module', 'distill-module',
    );

?>