<?
if (!isset($mmrpg_worlds)){ $mmrpg_worlds = []; }
//---------------------------------//
// PROTOTYPE WORLD 2k25 (2025-11-06)
//---------------------------------//
$this_world = 'prototype';
$mmrpg_worlds[$this_world] = [];
$mmrpg_worlds[$this_world]['areas'] = [];
function mmrpg_prototype_world_areas($world, &$index){
    //--------------------------//
    $index['prototype-area-0'] = [
        'name' => 'PROTOTYPE Area 0',
        'position' => '12-7',
        'level' => 1,
        'field' => 'field',
        'encounters' => ['trill'],
        'mechas' => ['met'],
        'items' => ['mecha-whistle'],
        'tags' => ['start'],
        'exits' => ['north', 'south']
        ];
    //--------------------------//
    $index['light-area-1'] = [
        'name' => 'LIGHT Area 1',
        'position' => '12-8',
        'level' => 5,
        'element' => 'copy',
        'field' => 'light-laboratory',
        'objects' => ['player-platform'],
        'encounters' => ['dr-light', 'mega-man'],
        'encounters2' => ['met', 'eddie'],
        'items' => ['light-program', 'light-heart', 'copy-core'],
        'tags' => ['central', 'player', 'main', 'spawn'],
        'exits' => ['north', 'east', 'south', 'west']
        ];
    $index['light-area-1e'] = [
        'name' => 'LIGHT Area 1E',
        'position' => '13-8',
        'level' => 10,
        'element' => 'copy',
        'field' => 'gentle-countryside',
        'encounters' => [],
        'encounters2' => ['met', 'sniper-joe'],
        'items' => ['equip-codes'],
        'pickups' => ['small-screw', 'energy-pellet', 'energy-capsule'],
        'tags' => ['central', 'player'],
        'exits' => ['east', 'west']
        ];
    $index['light-area-1s'] = [
        'name' => 'LIGHT Area 1S',
        'position' => '12-9',
        'level' => 10,
        'element' => 'copy',
        'field' => 'gentle-countryside',
        'encounters' => [],
        'encounters2' => ['met', 'sniper-joe'],
        'rescues' => ['roll'],
        'pickups' => ['small-screw', 'weapon-pellet', 'energy-pellet'],
        'tags' => ['central', 'player'],
        'exits' => ['north', 'south']
        ];
    $index['light-area-1w'] = [
        'name' => 'LIGHT Area 1W',
        'position' => '11-8',
        'level' => 10,
        'element' => 'copy',
        'field' => 'gentle-countryside',
        'encounters' => [],
        'encounters2' => ['met', 'sniper-joe'],
        'items' => ['item-codes'],
        'pickups' => ['small-screw', 'weapon-pellet', 'weapon-capsule'],
        'tags' => ['central', 'player'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['wily-area-1'] = [
        'name' => 'WILY Area 1',
        'position' => '19-14',
        'level' => 60,
        'element' => 'copy',
        'field' => 'wily-castle',
        'objects' => ['player-platform'],
        'encounters' => ['dr-wily', 'bass'],
        'encounters2' => ['met', 'heel-bot'],
        'items' => ['wily-program', 'wily-heart', 'copy-core'],
        'tags' => ['central', 'player', 'main', 'spawn'],
        'exits' => ['north', 'east', 'west']
        ];
    $index['wily-area-1n'] = [
        'name' => 'WILY Area 1N',
        'position' => '19-13',
        'level' => 80,
        'element' => 'copy',
        'field' => 'maniacal-hideaway',
        'encounters' => [],
        'encounters2' => ['met', 'skeleton-joe'],
        'items' => ['ability-codes'],
        'pickups' => ['small-screw', 'energy-pellet', 'energy-capsule'],
        'tags' => ['central', 'player'],
        'exits' => ['north', 'south']
        ];
    $index['wily-area-1w'] = [
        'name' => 'WILY Area 1W',
        'position' => '18-14',
        'level' => 40,
        'element' => 'copy',
        'field' => 'maniacal-hideaway',
        'encounters' => ['trill'],
        'encounters2' => ['met', 'skeleton-joe'],
        'rescues' => ['disco'],
        'pickups' => ['small-screw', 'weapon-pellet', 'energy-pellet'],
        'tags' => ['central', 'player'],
        'exits' => ['east', 'west']
        ];
    $index['wily-area-1w2'] = [
        'name' => 'WILY Area 1W2',
        'position' => '17-14',
        'level' => 20,
        'element' => 'copy',
        'field' => 'maniacal-hideaway',
        'encounters' => [],
        'encounters2' => ['met', 'skeleton-joe'],
        'items' => ['weapon-codes'],
        'pickups' => ['small-screw', 'weapon-pellet', 'weapon-capsule'],
        'tags' => ['central', 'player'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['cossack-area-1'] = [
        'name' => 'COSSACK Area 1',
        'position' => '5-14',
        'level' => 60,
        'element' => 'copy',
        'field' => 'cossack-citadel',
        'objects' => ['player-platform'],
        'encounters' => ['dr-cossack', 'proto-man'],
        'encounters2' => ['met', 'heal-bot'],
        'items' => ['cossack-program', 'cossack-heart', 'copy-core'],
        'tags' => ['central', 'player', 'main', 'spawn'],
        'exits' => ['north', 'east', 'west']
        ];
    $index['cossack-area-1n'] = [
        'name' => 'COSSACK Area 1N',
        'position' => '5-13',
        'level' => 80,
        'element' => 'copy',
        'field' => 'wintry-forefront',
        'encounters' => [],
        'encounters2' => ['met', 'crystal-joe'],
        'items' => ['master-codes'],
        'pickups' => ['small-screw', 'energy-pellet', 'energy-capsule'],
        'tags' => ['central', 'player'],
        'exits' => ['north', 'south']
        ];
    $index['cossack-area-1e'] = [
        'name' => 'COSSACK Area 1E',
        'position' => '6-14',
        'level' => 40,
        'element' => 'copy',
        'field' => 'wintry-forefront',
        'encounters' => ['trill'],
        'encounters2' => ['met', 'crystal-joe'],
        'rescues' => ['rhythm'],
        'pickups' => ['small-screw', 'weapon-pellet', 'energy-pellet'],
        'tags' => ['central', 'player'],
        'exits' => ['east', 'west']
        ];
    $index['cossack-area-1e2'] = [
        'name' => 'COSSACK Area 1E2',
        'position' => '7-14',
        'level' => 20,
        'element' => 'copy',
        'field' => 'wintry-forefront',
        'encounters' => [],
        'encounters2' => ['met', 'crystal-joe'],
        'items' => ['dress-codes'],
        'pickups' => ['small-screw', 'weapon-pellet', 'weapon-capsule'],
        'tags' => ['central', 'player'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['lalinde-area-1'] = [
        'name' => 'LALINDE Area 1',
        'position' => '12-18',
        'level' => 100,
        'element' => 'copy',
        'field' => '',
        'objects' => ['player-platform'],
        'encounters' => ['dr-lalinde'],
        'items' => ['lalinde-program', 'empty-heart', 'empty-core', 'mecha-whistle'],
        'tags' => ['central', 'player', 'main', 'spawn'],
        'exits' => ['north', 'east', 'west']
        ];
    $index['lalinde-area-1n'] = [
        'name' => 'LALINDE Area 1N',
        'position' => '12-17',
        'level' => 90,
        'element' => 'copy',
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['beat'],
        'items' => ['field-booster'],
        'pickups' => ['small-screw', 'energy-pellet', 'weapon-pellet'],
        'tags' => ['central', 'player'],
        'exits' => ['east', 'west']
        ];
    $index['lalinde-area-1e'] = [
        'name' => 'LALINDE Area 1E',
        'position' => '13-18',
        'level' => 120,
        'element' => 'copy',
        'field' => '',
        'encounters' => [],
        'pickups' => ['small-screw', 'energy-pellet', 'energy-capsule'],
        'tags' => ['central', 'player'],
        'exits' => ['west']
        ];
    $index['lalinde-area-1w'] = [
        'name' => 'LALINDE Area 1W',
        'position' => '11-18',
        'level' => 120,
        'element' => 'copy',
        'field' => '',
        'encounters' => [],
        'pickups' => ['small-screw', 'weapon-pellet', 'weapon-capsule'],
        'tags' => ['central', 'player'],
        'exits' => ['east']
        ];
    //--------------------------//
    $index['storage-area-1'] = [
        'name' => 'STORAGE Area 1',
        'position' => '23-7',
        'level' => 120,
        'field' => 'robot-museum',
        'encounters' => ['doc-robot'],
        'encounters2' => ['sniper-joe', 'skeleton-joe', 'crystal-joe'],
        'items' => ['extra-life'],
        'tags' => ['outer', 'main'],
        'exits' => ['north', 'west']
        ];
    $index['storage-area-2'] = [
        'name' => 'STORAGE Area 2',
        'position' => '23-6',
        'level' => 140,
        'field' => 'robot-museum',
        'encounters' => ['trill'],
        'rescues' => ['treble'],
        'items' => ['speed-booster', 'hyper-screw'],
        'tags' => ['outer', 'main2'],
        'exits' => ['south']
        ];
    //--------------------------//
    $index['storage-area-1s'] = [
        'name' => 'STORAGE Area 1S',
        'position' => '23-9',
        'level' => 160,
        'field' => 'robot-museum',
        'encounters' => ['weapon-archivist'],
        'items' => ['defense-booster', 'hyper-screw'],
        'tags' => ['outer'],
        'exits' => ['west']
        ];
    //--------------------------//
    $index['control-area-1'] = [
        'name' => 'CONTROL Area 1',
        'position' => '1-7',
        'level' => 120,
        'field' => 'royal-palace',
        'encounters' => ['king'],
        'encounters2' => ['pyre-fly', 'flea'],
        'items' => ['extra-life'],
        'tags' => ['outer', 'main'],
        'exits' => ['north', 'east']
        ];
    $index['control-area-2'] = [
        'name' => 'CONTROL Area 2',
        'position' => '1-6',
        'level' => 140,
        'field' => 'royal-palace',
        'encounters' => ['trill'],
        'rescues' => ['tango'],
        'items' => ['speed-diverter', 'hyper-screw'],
        'tags' => ['outer', 'main2'],
        'exits' => ['south']
        ];
    //--------------------------//
    $index['control-area-1s'] = [
        'name' => 'CONTROL Area 1S',
        'position' => '1-9',
        'level' => 160,
        'field' => 'royal-palace',
        'encounters' => ['weapon-archivist'],
        'items' => ['defense-diverter', 'hyper-screw'],
        'tags' => ['outer'],
        'exits' => ['east']
        ];
    //--------------------------//
    $index['security-area-1'] = [
        'name' => 'SECURITY Area 1',
        'position' => '14-16',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'items' => ['yashichi'],
        'tags' => ['outer', 'main'],
        'exits' => ['east', 'west']
        ];
    $index['security-area-2'] = [
        'name' => 'SECURITY Area 2',
        'position' => '15-16',
        'level' => 120,
        'field' => '',
        'encounters' => [],
        'tags' => ['outer'],
        'exits' => ['east', 'west']
        ];
    $index['security-area-3'] = [
        'name' => 'SECURITY Area 3',
        'position' => '16-16',
        'level' => 140,
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['reggae'],
        'items' => ['attack-booster', 'hyper-screw'],
        'tags' => ['outer'],
        'exits' => ['west']
        ];
    //--------------------------//
    $index['research-area-1'] = [
        'name' => 'RESEARCH Area 1',
        'position' => '10-16',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'items' => ['yashichi'],
        'tags' => ['outer', 'main'],
        'exits' => ['east', 'west']
        ];
    $index['research-area-2'] = [
        'name' => 'RESEARCH Area 2',
        'position' => '9-16',
        'level' => 120,
        'field' => '',
        'encounters' => [],
        'tags' => ['outer'],
        'exits' => ['east', 'west']
        ];
    $index['research-area-3'] = [
        'name' => 'RESEARCH Area 3',
        'position' => '8-16',
        'level' => 140,
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['auto'],
        'items' => ['attack-diverter', 'hyper-screw
        '],
        'tags' => ['outer'],
        'exits' => ['east']
        ];
    //--------------------------//
    $index['hunter-area-1'] = [
        'name' => 'HUNTER Area 1',
        'position' => '17-4',
        'level' => 140,
        'element' => 'shadow',
        'field' => 'hunter-compound',
        'encounters' => ['enker'],
        'encounters2' => ['mouslider', 'picket-man'],
        'tags' => ['upper', 'main'],
        'exits' => ['north', 'west']
        ];
    $index['hunter-area-2'] = [
        'name' => 'HUNTER Area 2',
        'position' => '17-3',
        'level' => 150,
        'element' => 'shadow',
        'field' => 'hunter-compound',
        'encounters2' => ['mouslider', 'picket-man'],
        'encounters' => ['punk'],
        'tags' => ['upper'],
        'exits' => ['north', 'south']
        ];
    $index['hunter-area-3'] = [
        'name' => 'HUNTER Area 3',
        'position' => '17-2',
        'level' => 160,
        'element' => 'shadow',
        'field' => 'hunter-compound',
        'encounters' => ['ballade'],
        'encounters2' => ['mouslider', 'picket-man'],
        'items' => ['hyper-screw'],
        'tags' => ['upper'],
        'exits' => ['south']
        ];
    //--------------------------//
    $index['genesis-area-1'] = [
        'name' => 'GENESIS Area 1',
        'position' => '7-4',
        'level' => 140,
        'element' => 'shadow',
        'field' => 'genesis-tower',
        'encounters' => ['buster-rod-g'],
        'encounters2' => ['beak', 'pooker', 'moller'],
        'tags' => ['upper', 'main'],
        'exits' => ['north', 'east']
        ];
    $index['genesis-area-2'] = [
        'name' => 'GENESIS Area 2',
        'position' => '7-3',
        'level' => 150,
        'element' => 'shadow',
        'field' => 'genesis-tower',
        'encounters' => ['mega-water-s'],
        'encounters2' => ['moller', 'colton', 'shield-attacker-gtr'],
        'tags' => ['upper'],
        'exits' => ['north', 'south']
        ];
    $index['genesis-area-3'] = [
        'name' => 'GENESIS Area 3',
        'position' => '7-2',
        'level' => 160,
        'element' => 'shadow',
        'field' => 'genesis-tower',
        'encounters' => ['hyper-storm-h'],
        'encounters2' => ['beak', 'pooker', 'shield-attacker-gtr', 'propeller-eye'],
        'items' => ['hyper-screw'],
        'tags' => ['upper'],
        'exits' => ['south']
        ];
    //--------------------------//
    $index['hifi-area-1'] = [
        'name' => 'HiFi Area 1',
        'position' => '20-4',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'tags' => ['upper', 'path'],
        'exits' => ['north', 'west']
        ];
    $index['hifi-area-2'] = [
        'name' => 'HiFi Area 2',
        'position' => '20-3',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'tags' => ['upper', 'path'],
        'exits' => ['north', 'south']
        ];
    //--------------------------//
    $index['stellar-area-1'] = [
        'name' => 'STELLAR Area 1',
        'position' => '22-2',
        'level' => 120,
        'element' => 'space',
        'field' => 'stardroid-base',
        'encounters' => ['mercury', 'venus', 'neptune', 'jupiter'],
        'encounters2' => ['malmet', 'covercannon', 'biribaree'],
        'tags' => ['upper', 'special', 'main'],
        'exits' => ['east', 'west']
        ];
    $index['stellar-area-2'] = [
        'name' => 'STELLAR Area 2',
        'position' => '23-2',
        'level' => 140,
        'element' => 'space',
        'field' => 'stardroid-base',
        'encounters' => ['mars', 'saturn', 'uranus', 'pluto'],
        'encounters2' => ['malmet', 'handoo', 'biribaree'],
        'tags' => ['upper', 'special'],
        'exits' => ['north', 'west']
        ];
    $index['stellar-area-3'] = [
        'name' => 'STELLAR Area 3',
        'position' => '23-1',
        'level' => 160,
        'element' => 'space',
        'field' => 'stardroid-base',
        'encounters' => ['sunstar'],
        'encounters2' => ['novamite', 'malmet'],
        'items' => ['cosmo-circuit'],
        'tags' => ['upper', 'special'],
        'exits' => ['south']
        ];
    //--------------------------//
    $index['lofi-area-1'] = [
        'name' => 'LoFi Area 1',
        'position' => '4-4',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'tags' => ['upper', 'path'],
        'exits' => ['north', 'east']
        ];
    $index['lofi-area-2'] = [
        'name' => 'LoFi Area 2',
        'position' => '4-3',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'tags' => ['upper', 'path'],
        'exits' => ['north', 'south']
        ];
    //--------------------------//
    $index['paradox-area-1'] = [
        'name' => 'PARADOX Area 1',
        'position' => '2-2',
        'level' => 120,
        'element' => 'shadow',
        'field' => 'prototype-complete',
        'encounters' => ['piano'],
        'tags' => ['upper', 'special', 'main'],
        'exits' => ['east', 'west']
        ];
    $index['paradox-area-2'] = [
        'name' => 'PARADOX Area 2',
        'position' => '1-2',
        'level' => 140,
        'element' => 'shadow',
        'field' => 'prototype-complete',
        'encounters' => [],
        'tags' => ['upper', 'special'],
        'exits' => ['north', 'east']
        ];
    $index['paradox-area-3'] = [
        'name' => 'PARADOX Area 3',
        'position' => '1-1',
        'level' => 160,
        'element' => 'shadow',
        'field' => 'prototype-complete',
        'encounters' => ['quint'],
        'items' => ['gambit-module'],
        'tags' => ['upper', 'special'],
        'exits' => ['south']
        ];
    //--------------------------//
    $index['explode-area-1'] = [
        'name' => 'EXPLODE Area 1',
        'position' => '15-8',
        'level' => 30,
        'element' => 'explode',
        'field' => 'pipe-station',
        'encounters' => ['crash-man'],
        'encounters2' => ['killer-bullet', 'prop-top', 'spring-head', 'crazy-cannon'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['east', 'south', 'west']
        ];
    $index['explode-area-1e'] = [
        'name' => 'EXPLODE Area 1E',
        'position' => '16-8',
        'level' => 40,
        'element' => 'explode',
        'field' => '',
        'encounters' => ['blast-man'],
        'encounters2' => ['shimobey', 'killer-bullet', 'oni-robo', 'bikky-bomb'],
        'items' => ['extra-life'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['explode-area-1s'] = [
        'name' => 'EXPLODE Area 1S',
        'position' => '15-9',
        'level' => 40,
        'element' => 'explode',
        'field' => 'trenchwork-depot',
        'encounters' => ['napalm-man', 'grenade-man'],
        'encounters2' => ['bombardier', 'moller', 'kakinba-tank'],
        'items' => ['xtreme-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north']
        ];
    $index['explode-area-1w'] = [
        'name' => 'EXPLODE Area 1W',
        'position' => '14-8',
        'level' => 20,
        'element' => 'explode',
        'field' => 'orb-city',
        'encounters' => ['bomb-man'],
        'encounters2' => ['bombomb', 'met', 'sniper-joe', 'killer-bullet'],
        'items' => ['energy-tank'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['impact-area-1'] = [
        'name' => 'IMPACT Area 1',
        'position' => '12-11',
        'level' => 30,
        'element' => 'impact',
        'field' => 'rocky-plateau',
        'encounters' => ['hard-man'],
        'encounters2' => ['monking-r', 'hammer-joe', 'needle-ned'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'east', 'south', 'west']
        ];
    $index['impact-area-1n'] = [
        'name' => 'IMPACT Area 1N',
        'position' => '12-10',
        'level' => 20,
        'element' => 'impact',
        'field' => 'mountain-mines',
        'encounters' => ['guts-man'],
        'encounters2' => ['picket-man', 'met', 'killer-bullet', 'bunby-heli'],
        'items' => ['guard-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'south']
        ];
    $index['impact-area-1e'] = [
        'name' => 'IMPACT Area 1E',
        'position' => '13-11',
        'level' => 40,
        'element' => 'impact',
        'field' => '',
        'encounters' => ['strike-man'],
        'encounters2' => ['pitchan', 'monking-r', 'goriblue'],
        'items' => ['weapon-upgrade'],
        'tags' => ['central', 'elemental'],
        'exits' => ['west']
        ];
    $index['impact-area-1w'] = [
        'name' => 'IMPACT Area 1W',
        'position' => '11-11',
        'level' => 40,
        'element' => 'impact',
        'field' => 'waterworks-dam',
        'encounters' => ['concrete-man', 'impact-man'],
        'encounters2' => ['bombomboy', 'picket-man'],
        'items' => ['energy-upgrade'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east']
        ];
    //--------------------------//
    $index['cutter-area-1'] = [
        'name' => 'CUTTER Area 1',
        'position' => '9-8',
        'level' => 30,
        'element' => 'cutter',
        'field' => 'industrial-facility',
        'encounters' => ['metal-man'],
        'encounters2' => ['pierrobot', 'drill-mole', 'spring-head', 'arc-weldy'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['east', 'south', 'west']
        ];
    $index['cutter-area-1e'] = [
        'name' => 'CUTTER Area 1E',
        'position' => '10-8',
        'level' => 20,
        'element' => 'cutter',
        'field' => 'abandoned-warehouse',
        'encounters' => ['cut-man', 'big-eye'],
        'encounters2' => ['flea', 'met', 'spine', 'killer-bullet', 'beak', 'adhering-suzy'],
        'items' => ['weapon-tank'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['cutter-area-1s'] = [
        'name' => 'CUTTER Area 1S',
        'position' => '9-9',
        'level' => 40,
        'element' => 'cutter',
        'field' => '',
        'encounters' => ['blade-man', 'yamato-man', 'knight-man'],
        'encounters2' => ['merserker', 'moller', 'kabuton', 'ben-k', 'shield-attacker-gtr'],
        'items' => ['repair-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north']
        ];
    $index['cutter-area-1w'] = [
        'name' => 'CUTTER Area 1W',
        'position' => '8-8',
        'level' => 40,
        'element' => 'cutter',
        'field' => 'construction-site',
        'encounters' => ['needle-man'],
        'encounters2' => ['needle-ned', 'hammer-joe', 'elec-n'],
        'items' => ['extra-life'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['flame-area-1'] = [
        'name' => 'FLAME Area 1',
        'position' => '18-8',
        'level' => 60,
        'element' => 'flame',
        'field' => 'atomic-furnace',
        'encounters' => ['heat-man'],
        'encounters2' => ['telly', 'popo-heli', 'nitron-r'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'east', 'south', 'west']
        ];
    $index['flame-area-1e'] = [
        'name' => 'FLAME Area 1E',
        'position' => '19-8',
        'level' => 70,
        'element' => 'flame',
        'field' => 'egyptian-excavation',
        'encounters' => ['pharaoh-man', 'sword-man'],
        'encounters2' => ['pyre-fly', 'mummira', 'shield-attacker'],
        'items' => ['forge-circuit'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['flame-area-1s'] = [
        'name' => 'FLAME Area 1S',
        'position' => '18-9',
        'level' => 70,
        'element' => 'flame',
        'field' => '',
        'encounters' => ['solar-man'],
        'encounters2' => ['sola-rei', 'petal-anne_alt2', 'shield-attacker-trl', 'cline_alt'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'south']
        ];
    $index['flame-area-1w'] = [
        'name' => 'FLAME Area 1W',
        'position' => '17-8',
        'level' => 50,
        'element' => 'flame',
        'field' => 'steel-mill',
        'encounters' => ['fire-man', 'torch-man'],
        'encounters2' => ['tackle-fire', 'bombomb', 'telly', 'arc-weldy'],
        'items' => ['uptick-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['swift-area-1'] = [
        'name' => 'SWIFT Area 1',
        'position' => '12-13',
        'level' => 50,
        'element' => 'swift',
        'field' => 'spinning-greenhouse',
        'encounters' => ['top-man', 'spring-man'],
        'encounters2' => ['spin-fiend', 'peterchy'],
        'items' => ['persist-module'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'east', 'south', 'west']
        ];
    $index['swift-area-1n'] = [
        'name' => 'SWIFT Area 1N',
        'position' => '12-12',
        'level' => 40,
        'element' => 'swift',
        'field' => 'underground-laboratory',
        'encounters' => ['quick-man'],
        'encounters2' => ['spring-head', 'tackle-fire'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'south']
        ];
    $index['swift-area-1e'] = [
        'name' => 'SWIFT Area 1E',
        'position' => '13-13',
        'level' => 60,
        'element' => 'swift',
        'field' => '',
        'encounters' => ['bounce-man'],
        'encounters2' => ['tosanaizer-v', 'ballonboo', 'pukapunter', 'coil-n'],
        'items' => ['weapon-tank'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['swift-area-1w'] = [
        'name' => 'SWIFT Area 1W',
        'position' => '11-13',
        'level' => 60,
        'element' => 'swift',
        'field' => 'sonic-highway',
        'encounters' => ['turbo-man', 'nitro-man'],
        'encounters2' => ['robo-transport', 'cannon-roader', 'trio-the-wheel'],
        'items' => ['energy-tank'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['freeze-area-1'] = [
        'name' => 'FREEZE Area 1',
        'position' => '6-8',
        'level' => 60,
        'element' => 'freeze',
        'field' => '',
        'encounters' => ['tundra-man'],
        'encounters2' => ['peng', 'nitron-b', 'curlinger'],
        'items' => ['bulwark-module'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'east', 'south', 'west']
        ];
    $index['freeze-area-1e'] = [
        'name' => 'FREEZE Area 1E',
        'position' => '7-8',
        'level' => 50,
        'element' => 'freeze',
        'field' => 'arctic-jungle',
        'encounters' => ['ice-man', 'blizzard-man', 'big-eye'],
        'encounters2' => ['peng', 'adhering-suzy', 'foot-holder', 'bomb-sleigh'],
        'items' => ['charge-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['freeze-area-1s'] = [
        'name' => 'FREEZE Area 1S',
        'position' => '6-9',
        'level' => 70,
        'element' => 'freeze',
        'field' => '',
        'encounters' => ['cold-man', 'frost-man'],
        'encounters2' => ['penpen-ev', 'nitron-b', 'frosty-throwman'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'south']
        ];
    $index['freeze-area-1w'] = [
        'name' => 'FREEZE Area 1W',
        'position' => '5-8',
        'level' => 70,
        'element' => 'freeze',
        'field' => 'glacier-cradle',
        'encounters' => ['chill-man', 'freeze-man'],
        'encounters2' => ['frosty-throwman', 'tel-tel'],
        'items' => ['resetchi'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['wind-area-1'] = [
        'name' => 'WIND Area 1',
        'position' => '18-6',
        'level' => 80,
        'element' => 'wind',
        'field' => '',
        'encounters' => ['tornado-man', 'cloud-man'],
        'encounters2' => ['ballonboo', 'skater-boy', 'parabeak', 'tel-tel'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['east', 'south', 'west']
        ];
    $index['wind-area-1e'] = [
        'name' => 'WIND Area 1E',
        'position' => '19-6',
        'level' => 90,
        'element' => 'wind',
        'field' => '',
        'encounters' => ['wind-man', 'gyro-man'],
        'encounters2' => ['pandeeta', 'krushdiver', 'bombardier', 'pukapunter'],
        'rescues' => ['quake-woman'],
        'items' => ['transport-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'west']
        ];
    $index['wind-area-1s'] = [
        'name' => 'WIND Area 1S',
        'position' => '18-7',
        'level' => 70,
        'element' => 'wind',
        'field' => 'sky-ridge',
        'encounters' => ['air-man'],
        'encounters2' => ['fan-fiend', 'foot-holder', 'parabeak'],
        'items' => ['resetchi'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'south']
        ];
    $index['wind-area-1w'] = [
        'name' => 'WIND Area 1W',
        'position' => '17-6',
        'level' => 90,
        'element' => 'wind',
        'field' => 'rusty-scrapheap',
        'encounters' => ['dust-man', 'junk-man'],
        'encounters2' => ['lady-blader', 'up-n-down', 'gockroach-s'],
        'items' => ['salvage-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east']
        ];
    //--------------------------//
    $index['shadow-area-1'] = [
        'name' => 'SHADOW Area 1',
        'position' => '21-8',
        'level' => 90,
        'element' => 'shadow',
        'field' => 'robosaur-boneyard',
        'encounters' => ['skull-man'],
        'encounters2' => ['skullmet', 'skeleton-joe'],
        'items' => ['overkill-module'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'south']
        ];
    $index['shadow-area-1n'] = [
        'name' => 'SHADOW Area 1N',
        'position' => '21-7',
        'level' => 100,
        'element' => 'shadow',
        'field' => 'haunted-mansion',
        'encounters' => ['shade-man'],
        'encounters2' => ['astro-zombieg', 'batton', 'anti-eddie'],
        'items' => ['siphon-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'south']
        ];
    $index['shadow-area-1s'] = [
        'name' => 'SHADOW Area 1S',
        'position' => '21-9',
        'level' => 100,
        'element' => 'shadow',
        'field' => '',
        'encounters' => ['magic-man', 'clown-man'],
        'encounters2' => ['romper', 'pierrobot', 'shimobey'],
        'items' => ['weapon-tank'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'north']
        ];
    $index['shadow-area-1w'] = [
        'name' => 'SHADOW Area 1W',
        'position' => '20-8',
        'level' => 80,
        'element' => 'shadow',
        'field' => 'septic-system',
        'encounters' => ['shadow-man'],
        'encounters2' => ['ribbitron', 'hammer-joe', 'spin-fiend'],
        'items' => ['super-capsule'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['electric-area-1'] = [
        'name' => 'ELECTRIC Area 1',
        'position' => '18-11',
        'level' => 90,
        'element' => 'electric',
        'field' => 'power-plant',
        'encounters' => ['spark-man', 'plug-man', 'giant-spring-head'],
        'encounters2' => ['elec-n', 'hammer-joe', 'nitron-y', 'peterchy'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'east', 'west']
        ];
    $index['electric-area-1n'] = [
        'name' => 'ELECTRIC Area 1N',
        'position' => '18-10',
        'level' => 80,
        'element' => 'electric',
        'field' => 'electrical-tower',
        'encounters' => ['elec-man', 'big-eye'],
        'encounters2' => ['spine', 'flea', 'beak', 'adhering-suzy'],
        'items' => ['copycat-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'south']
        ];
    $index['electric-area-1e'] = [
        'name' => 'ELECTRIC Area 1E',
        'position' => '19-11',
        'level' => 100,
        'element' => 'electric',
        'field' => '',
        'encounters' => ['dynamo-man', 'fuse-man'],
        'encounters2' => ['electriri', 'plasma-plus', 'elec-n'],
        'items' => ['resetchi'],
        'tags' => ['central', 'elemental'],
        'exits' => ['south', 'west']
        ];
    $index['electric-area-1w'] = [
        'name' => 'ELECTRIC Area 1W',
        'position' => '17-11',
        'level' => 100,
        'element' => 'electric',
        'field' => '',
        'encounters' => ['gravity-man', 'sheep-man'],
        'encounters2' => ['pointan', 'flipping-suzy', 'pukapunter'],
        'items' => ['battery-circuit'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east']
        ];
    //--------------------------//
    $index['crystal-area-1'] = [
        'name' => 'CRYSTAL Area 1',
        'position' => '15-13',
        'level' => 80,
        'element' => 'crystal',
        'field' => '',
        'encounters' => ['trill'],
        'encounters2' => ['diaymon'],
        'rescues' => ['meddy'],
        'items' => ['super-capsule'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'south', 'west']
        ];
    $index['crystal-area-1n'] = [
        'name' => 'CRYSTAL Area 1N',
        'position' => '15-12',
        'level' => 90,
        'element' => 'crystal',
        'field' => 'gemstone-cavern',
        'encounters' => ['jewel-man'],
        'encounters2' => ['diaymon', 'spike-pusher', 'crystal-joe'],
        'items' => ['distill-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['south']
        ];
    $index['crystal-area-1s'] = [
        'name' => 'CRYSTAL Area 1S',
        'position' => '15-14',
        'level' => 90,
        'element' => 'crystal',
        'field' => 'reflection-chamber',
        'encounters' => ['gemini-man'],
        'encounters2' => ['nitron-r', 'nitron-b', 'nitron-y', 'spin-fiend', 'ribbitron'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'east']
        ];
    $index['crystal-area-1w'] = [
        'name' => 'CRYSTAL Area 1W',
        'position' => '14-13',
        'level' => 70,
        'element' => 'crystal',
        'field' => 'crystal-catacombs',
        'encounters' => ['crystal-man'],
        'encounters2' => ['crystal-joe', 'tatepakkan', 'diaymon'],
        'items' => ['hyperscan-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    //--------------------------//
    $index['time-area-1'] = [
        'name' => 'TIME Area 1',
        'position' => '12-15',
        'level' => 80,
        'element' => 'time',
        'field' => 'photon-collider',
        'encounters' => ['flash-man'],
        'encounters2' => ['crazy-cannon', 'blocky'],
        'items' => ['chrono-circuit'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'east', 'west']
        ];
    $index['time-area-1n'] = [
        'name' => 'TIME Area 1N',
        'position' => '12-14',
        'level' => 70,
        'element' => 'time',
        'field' => 'clock-citadel',
        'encounters' => ['time-man'],
        'encounters2' => ['flutter-fly', 'fooley'],
        'items' => ['extra-life'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'south']
        ];
    $index['time-area-1e'] = [
        'name' => 'TIME Area 1E',
        'position' => '13-15',
        'level' => 90,
        'element' => 'time',
        'field' => '',
        'encounters' => ['centaur-man'],
        'encounters2' => ['pelicanu', 'pooker', 'kabuton'],
        'items' => ['hourglass-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['south', 'west']
        ];
    $index['time-area-1w'] = [
        'name' => 'TIME Area 1W',
        'position' => '11-15',
        'level' => 90,
        'element' => 'time',
        'field' => 'lighting-control',
        'encounters' => ['bright-man'],
        'encounters2' => ['bulb-blaster', 'electriri'],
        'items' => ['resetchi'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'south']
        ];
    //--------------------------//
    $index['missile-area-1'] = [
        'name' => 'MISSILE Area 1',
        'position' => '9-13',
        'level' => 80,
        'element' => 'missile',
        'field' => '',
        'encounters' => ['search-man'],
        'encounters2' => ['goriblue', 'sniper-joe', 'eggalodon'],
        'items' => ['super-capsule'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'east', 'south']
        ];
    $index['missile-area-1n'] = [
        'name' => 'MISSILE Area 1N',
        'position' => '9-12',
        'level' => 90,
        'element' => 'missile',
        'field' => 'submerged-armory',
        'encounters' => ['dive-man'],
        'encounters2' => ['manta-missile', 'covercannon'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental'],
        'exits' => ['south']
        ];
    $index['missile-area-1e'] = [
        'name' => 'MISSILE Area 1E',
        'position' => '10-13',
        'level' => 70,
        'element' => 'missile',
        'field' => 'magnetic-generator',
        'encounters' => ['magnet-man'],
        'encounters2' => ['mag-fly', 'elec-n', 'peterchy'],
        'items' => ['magnet-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['missile-area-1s'] = [
        'name' => 'MISSILE Area 1S',
        'position' => '9-14',
        'level' => 90,
        'element' => 'missile',
        'field' => 'minefield-dunes',
        'encounters' => ['commando-man'],
        'encounters2' => ['antlin-g', 'bombardier', 'dodonpa-cannon'],
        'items' => ['target-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'west']
        ];
    //--------------------------//
    $index['nature-area-1'] = [
        'name' => 'NATURE Area 1',
        'position' => '6-6',
        'level' => 80,
        'element' => 'nature',
        'field' => 'serpent-column',
        'encounters' => ['snake-man'],
        'encounters2' => ['petit-snakey', 'ribbitron'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['east', 'south', 'west']
        ];
    $index['nature-area-1e'] = [
        'name' => 'NATURE Area 1E',
        'position' => '7-6',
        'level' => 90,
        'element' => 'nature',
        'field' => '',
        'encounters' => ['slash-man', 'tengu-man'],
        'encounters2' => ['eggalodon', 'cline', 'shururun', 'bombomboy'],
        'items' => ['sapling-circuit'],
        'tags' => ['central', 'elemental'],
        'exits' => ['west']
        ];
    $index['nature-area-1s'] = [
        'name' => 'NATURE Area 1S',
        'position' => '6-7',
        'level' => 70,
        'element' => 'nature',
        'field' => 'preserved-forest',
        'encounters' => ['wood-man', 'burner-man'],
        'encounters2' => ['batton', 'kabuton', 'blocky', 'tank-oven'],
        'items' => ['resetchi'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'south']
        ];
    $index['nature-area-1w'] = [
        'name' => 'NATURE Area 1W',
        'position' => '5-6',
        'level' => 90,
        'element' => 'nature',
        'field' => '',
        'encounters' => ['plant-man', 'hornet-man'],
        'encounters2' => ['kabuton', 'propeller-eye', 'petal-anne'],
        'rescues' => ['vesper-woman'],
        'items' => ['growth-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'east']
        ];
    //--------------------------//
    $index['water-area-1'] = [
        'name' => 'WATER Area 1',
        'position' => '6-11',
        'level' => 90,
        'element' => 'water',
        'field' => 'rainy-sewers',
        'encounters' => ['toad-man', 'pump-man'],
        'encounters2' => ['robo-fishtot', 'piper-n', 'lady-blader', 'up-n-down'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'east', 'west']
        ];
    $index['water-area-1n'] = [
        'name' => 'WATER Area 1N',
        'position' => '6-10',
        'level' => 80,
        'element' => 'water',
        'field' => 'waterfall-institute',
        'encounters' => ['bubble-man'],
        'encounters2' => ['snapper', 'octone'],
        'items' => ['spreader-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'south']
        ];
    $index['water-area-1e'] = [
        'name' => 'WATER Area 1E',
        'position' => '7-11',
        'level' => 100,
        'element' => 'water',
        'field' => '',
        'encounters' => ['burst-man', 'aqua-man', 'acid-man'],
        'encounters2' => ['shell-n', 'pipetto', 'bombomb'],
        'items' => ['alchemy-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['west']
        ];
    $index['water-area-1w'] = [
        'name' => 'WATER Area 1W',
        'position' => '5-11',
        'level' => 100,
        'element' => 'water',
        'field' => '',
        'encounters' => ['splash-woman', 'wave-man', 'pirate-man'],
        'encounters2' => ['ballonboo', 'octone', 'bui-boi'],
        'items' => ['sponge-circuit'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'south']
        ];
    //--------------------------//
    $index['earth-area-1'] = [
        'name' => 'EARTH Area 1',
        'position' => '3-8',
        'level' => 90,
        'element' => 'earth',
        'field' => '',
        'encounters' => ['block-man', 'stone-man'],
        'encounters2' => ['rockscotch', 'air-stone', 'beetle-borg'],
        'tags' => ['central', 'elemental', 'main'],
        'exits' => ['north', 'east', 'south']
        ];
    $index['earth-area-1n'] = [
        'name' => 'EARTH Area 1N',
        'position' => '3-7',
        'level' => 100,
        'element' => 'earth',
        'field' => 'mineral-quarry',
        'encounters' => ['drill-man', 'ground-man'],
        'encounters2' => ['drill-mole', 'moller'],
        'items' => ['fortune-module'],
        'tags' => ['central', 'elemental'],
        'exits' => ['south', 'west']
        ];
    $index['earth-area-1e'] = [
        'name' => 'EARTH Area 1E',
        'position' => '4-8',
        'level' => 80,
        'element' => 'earth',
        'field' => 'oil-wells',
        'encounters' => ['oil-man', 'flame-man'],
        'encounters2' => ['beetle-borg', 'gockroach-s', 'antlin-g', 'pooker'],
        'items' => ['super-capsule'],
        'tags' => ['central', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['earth-area-1s'] = [
        'name' => 'EARTH Area 1S',
        'position' => '3-9',
        'level' => 100,
        'element' => 'earth',
        'field' => 'sunset-gulch',
        'encounters' => ['tomohawk-man', 'charge-man', 'magma-man'],
        'encounters2' => ['colton', 'merserker', 'power-muscler'],
        'items' => ['energy-tank'],
        'tags' => ['central', 'elemental'],
        'exits' => ['north', 'west']
        ];
    //--------------------------//
    $index['space-area-1'] = [
        'name' => 'SPACE Area 1',
        'position' => '12-5',
        'level' => 100,
        'element' => 'space',
        'field' => '',
        'encounters' => ['trill'],
        'encounters2' => ['novamite', 'adamski'],
        'rescues' => ['rush'],
        'items' => ['reverse-module'],
        'tags' => ['upper', 'elemental', 'main'],
        'exits' => ['north', 'south']
        ];
    //--------------------------//
    $index['space-area-1e'] = [
        'name' => 'SPACE Area 1E',
        'position' => '13-4',
        'level' => 110,
        'element' => 'space',
        'field' => 'satellite-deck',
        'encounters' => ['star-man'],
        'encounters2' => ['novamite', 'shururun', 'malmet', 'pukapunter'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['space-area-1e2'] = [
        'name' => 'SPACE Area 1E2',
        'position' => '14-4',
        'level' => 120,
        'element' => 'space',
        'field' => 'satellite-deck',
        'encounters' => ['galaxy-man'],
        'encounters2' => ['novamite', 'shururun', 'flipping-suzy', 'pukapunter'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['space-area-1e3'] = [
        'name' => 'SPACE Area 1E3',
        'position' => '15-4',
        'level' => 130,
        'element' => 'space',
        'field' => 'satellite-deck',
        'encounters2' => ['novamite', 'shururun', 'malmet', 'flipping-suzy', 'trille-bot'],
        'encounters' => ['terra'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['north', 'east', 'west']
        ];
    //--------------------------//
    $index['space-area-1w'] = [
        'name' => 'SPACE Area 1W',
        'position' => '11-4',
        'level' => 110,
        'element' => 'space',
        'field' => 'space-simulator',
        'encounters' => ['ring-man', 'gachappon'],
        'encounters2' => ['novamite', 'ring-ring', 'adamski', 'tamp'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['space-area-1w2'] = [
        'name' => 'SPACE Area 1W2',
        'position' => '10-4',
        'level' => 120,
        'element' => 'space',
        'field' => 'space-simulator',
        'encounters' => ['astro-man'],
        'encounters2' => ['novamite', 'ring-ring', 'malmet', 'tamp',],
        'tags' => ['upper', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['space-area-1w3'] = [
        'name' => 'SPACE Area 1W3',
        'position' => '9-4',
        'level' => 130,
        'element' => 'space',
        'field' => 'space-simulator',
        'encounters' => ['ra-thor'],
        'encounters2' => ['novamite', 'ring-ring', 'adamski', 'malmet', 'trille-bot'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['north', 'east', 'west']
        ];
    //--------------------------//
    $index['space-area-2'] = [
        'name' => 'SPACE Area 2',
        'position' => '12-2',
        'level' => 200,
        'element' => 'space',
        'field' => 'final-destination-3',
        'encounters' => ['slur'],
        'encounters2' => ['trille-bot'],
        'tags' => ['upper', 'elemental', 'main', 'final'],
        'exits' => ['north', 'east', 'west']
        ];
    $index['space-area-2n'] = [
        'name' => 'SPACE Area 2N',
        'position' => '12-1',
        'level' => 999,
        'element' => 'space',
        'field' => 'final-destination-3',
        'encounters' => ['proxy'],
        'items' => ['empty-core'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['south']
        ];
    //--------------------------//
    $index['space-area-2e'] = [
        'name' => 'SPACE Area 2E',
        'position' => '14-2',
        'level' => 150,
        'element' => 'space',
        'field' => 'final-destination-2',
        'encounters' => ['dark-man-4'],
        'encounters2' => ['trille-bot', 'shimobey', 'romper', 'shururun'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['space-area-2e2'] = [
        'name' => 'SPACE Area 2E2',
        'position' => '15-2',
        'level' => 140,
        'element' => 'space',
        'field' => 'final-destination',
        'encounters' => ['dark-man', 'dark-man-2', 'dark-man-3'],
        'encounters2' => ['trille-bot', 'shimobey', 'romper', 'shururun'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['south', 'west']
        ];
    //--------------------------//
    $index['space-area-2w'] = [
        'name' => 'SPACE Area 2W',
        'position' => '10-2',
        'level' => 150,
        'element' => 'space',
        'field' => 'final-destination-2',
        'encounters' => ['quake-woman-ds'],
        'encounters2' => ['trille-bot', 'mummira', 'astro-zombieg', 'ring-ring'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['east', 'west']
        ];
    $index['space-area-2w2'] = [
        'name' => 'SPACE Area 2W2',
        'position' => '9-2',
        'level' => 140,
        'element' => 'space',
        'field' => 'final-destination',
        'encounters' => ['mega-man-ds', 'bass-ds', 'proto-man-ds'],
        'encounters2' => ['trille-bot', 'mummira', 'astro-zombieg', 'ring-ring'],
        'tags' => ['upper', 'elemental'],
        'exits' => ['east', 'south']
        ];
    //--------------------------//
    $index['laser-area-1'] = [
        'name' => 'LASER Area 1',
        'position' => '21-16',
        'level' => 300,
        'element' => 'laser',
        'field' => '',
        'encounters' => ['laser-man'],
        'encounters2' => ['sola-rei', 'beak', 'propeller-eye', 'malmet'],
        'items' => ['hyper-screw'],
        'tags' => ['outer', 'special', 'elemental', 'main'],
        'exits' => ['east']
        ];
    $index['laser-area-1e'] = [
        'name' => 'LASER Area 1E',
        'position' => '22-16',
        'level' => 250,
        'element' => 'laser',
        'field' => '',
        'encounters' => [],
        'encounters2' => ['sola-rei', 'beak'],
        'tags' => ['outer', 'special', 'elemental'],
        'exits' => ['north', 'west']
        ];
    $index['laser-area-1ne'] = [
        'name' => 'LASER Area 1NE',
        'position' => '22-15',
        'level' => 200,
        'element' => 'laser',
        'field' => '',
        'encounters' => [],
        'encounters2' => ['beak'],
        'items' => ['extra-life'],
        'tags' => ['outer', 'special', 'elemental'],
        'exits' => ['north', 'south']
        ];
    //--------------------------//
    $index['shield-area-1'] = [
        'name' => 'SHIELD Area 1',
        'position' => '3-16',
        'level' => 300,
        'element' => 'shield',
        'field' => '',
        'encounters' => ['shield-man'],
        'encounters2' => ['vavaliant', 'shield-attacker', 'shield-attacker-gtr', 'shield-attacker-trl'],
        'items' => ['hyper-screw'],
        'tags' => ['outer', 'special', 'elemental', 'main'],
        'exits' => ['west']
        ];
    $index['shield-area-1w'] = [
        'name' => 'SHIELD Area 1W',
        'position' => '2-16',
        'level' => 250,
        'element' => 'shield',
        'field' => '',
        'encounters' => [],
        'encounters2' => ['shield-attacker', 'shield-attacker-gtr'],
        'tags' => ['outer', 'special', 'elemental'],
        'exits' => ['north', 'east']
        ];
    $index['shield-area-1nw'] = [
        'name' => 'SHIELD Area 1NW',
        'position' => '2-15',
        'level' => 200,
        'element' => 'shield',
        'field' => '',
        'encounters' => [],
        'encounters2' => ['shield-attacker'],
        'items' => ['extra-life'],
        'tags' => ['outer', 'special', 'elemental'],
        'exits' => ['north', 'south']
        ];
    //--------------------------//
    $index['star-gate-10'] = [
        'name' => 'STAR Gate 10',
        'position' => '12-6',
        'level' => 10,
        'tags' => ['stargate'],
        'exits' => ['north', 'south']
        ];
    $index['star-gate-20n'] = [
        'name' => 'STAR Gate 20N',
        'position' => '12-4',
        'level' => 20,
        'tags' => ['stargate'],
        'exits' => ['east', 'south', 'west']
        ];
    $index['star-gate-20e'] = [
        'name' => 'STAR Gate 20E',
        'position' => '22-7',
        'level' => 20,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-20se'] = [
        'name' => 'STAR Gate 20SE',
        'position' => '19-12',
        'level' => 20,
        'tags' => ['stargate'],
        'exits' => ['north', 'south']
        ];
    $index['star-gate-20sse'] = [
        'name' => 'STAR Gate 20SSE',
        'position' => '16-14',
        'level' => 20,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-20w'] = [
        'name' => 'STAR Gate 20W',
        'position' => '2-7',
        'level' => 20,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-20sw'] = [
        'name' => 'STAR Gate 20SW',
        'position' => '5-12',
        'level' => 20,
        'tags' => ['stargate'],
        'exits' => ['north', 'south']
        ];
    $index['star-gate-20ssw'] = [
        'name' => 'STAR Gate 20SSW',
        'position' => '8-14',
        'level' => 20,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-30ne'] = [
        'name' => 'STAR Gate 30NE',
        'position' => '15-3',
        'level' => 30,
        'tags' => ['stargate'],
        'exits' => ['north', 'south']
        ];
    $index['star-gate-30e'] = [
        'name' => 'STAR Gate 30E',
        'position' => '22-9',
        'level' => 30,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-30se'] = [
        'name' => 'STAR Gate 30SE',
        'position' => '13-16',
        'level' => 30,
        'tags' => ['stargate'],
        'exits' => ['east', 'north']
        ];
    $index['star-gate-30nw'] = [
        'name' => 'STAR Gate 30NW',
        'position' => '9-3',
        'level' => 30,
        'tags' => ['stargate'],
        'exits' => ['north', 'south']
        ];
    $index['star-gate-30w'] = [
        'name' => 'STAR Gate 30W',
        'position' => '2-9',
        'level' => 30,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-30sw'] = [
        'name' => 'STAR Gate 30SW',
        'position' => '11-16',
        'level' => 30,
        'tags' => ['stargate'],
        'exits' => ['north', 'west']
        ];
    $index['star-gate-40ne'] = [
        'name' => 'STAR Gate 40NE',
        'position' => '13-2',
        'level' => 40,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-40e'] = [
        'name' => 'STAR Gate 40E',
        'position' => '19-5',
        'level' => 40,
        'tags' => ['stargate'],
        'exits' => ['north', 'south']
        ];
    $index['star-gate-40se'] = [
        'name' => 'STAR Gate 40SE',
        'position' => '13-17',
        'level' => 40,
        'tags' => ['stargate'],
        'exits' => ['north', 'west']
        ];
    $index['star-gate-40nw'] = [
        'name' => 'STAR Gate 40NW',
        'position' => '11-2',
        'level' => 40,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-40w'] = [
        'name' => 'STAR Gate 40W',
        'position' => '5-5',
        'level' => 40,
        'tags' => ['stargate'],
        'exits' => ['north', 'south']
        ];
    $index['star-gate-40sw'] = [
        'name' => 'STAR Gate 40SW',
        'position' => '11-17',
        'level' => 40,
        'tags' => ['stargate'],
        'exits' => ['north', 'east']
        ];
    $index['star-gate-50e'] = [
        'name' => 'STAR Gate 50E',
        'position' => '19-4',
        'level' => 50,
        'tags' => ['stargate'],
        'exits' => ['east', 'south']
        ];
    $index['star-gate-50w'] = [
        'name' => 'STAR Gate 50W',
        'position' => '5-4',
        'level' => 50,
        'tags' => ['stargate'],
        'exits' => ['south', 'west']
        ];
    $index['star-gate-60e'] = [
        'name' => 'STAR Gate 60E',
        'position' => '20-2',
        'level' => 60,
        'tags' => ['stargate'],
        'exits' => ['east', 'south']
        ];
    $index['star-gate-60w'] = [
        'name' => 'STAR Gate 60W',
        'position' => '4-2',
        'level' => 60,
        'tags' => ['stargate'],
        'exits' => ['south', 'west']
        ];
    $index['star-gate-70ne'] = [
        'name' => 'STAR Gate 70NE',
        'position' => '21-2',
        'level' => 70,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-70se'] = [
        'name' => 'STAR Gate 70SE',
        'position' => '20-14',
        'level' => 70,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-70nw'] = [
        'name' => 'STAR Gate 70NW',
        'position' => '3-2',
        'level' => 70,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-70sw'] = [
        'name' => 'STAR Gate 70SW',
        'position' => '4-14',
        'level' => 70,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-80ne'] = [
        'name' => 'STAR Gate 80NE',
        'position' => '16-4',
        'level' => 80,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-80se'] = [
        'name' => 'STAR Gate 80SE',
        'position' => '21-14',
        'level' => 80,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-80nw'] = [
        'name' => 'STAR Gate 80NW',
        'position' => '8-4',
        'level' => 80,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-80sw'] = [
        'name' => 'STAR Gate 80SW',
        'position' => '3-14',
        'level' => 80,
        'tags' => ['stargate'],
        'exits' => ['east', 'west']
        ];
    $index['star-gate-90e'] = [
        'name' => 'STAR Gate 90E',
        'position' => '22-14',
        'level' => 90,
        'tags' => ['stargate'],
        'exits' => ['south', 'west']
        ];
    $index['star-gate-90w'] = [
        'name' => 'STAR Gate 90W',
        'position' => '2-14',
        'level' => 90,
        'tags' => ['stargate'],
        'exits' => ['east', 'south']
        ];
    //--------------------------//
    // Automatically append any data that's common for all of them
    foreach ($index AS $token => $info){
        $info['world'] = $world;
        $index[$token] = $info;
    }
    //--------------------------//
}
mmrpg_prototype_world_areas($this_world, $mmrpg_worlds[$this_world]['areas']);

?>