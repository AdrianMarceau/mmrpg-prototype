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
        'tags' => ['start']
        ];
    //--------------------------//
    $index['light-area-1'] = [
        'name' => 'LIGHT Area 1',
        'position' => '12-8',
        'level' => 5,
        'field' => 'light-laboratory',
        'objects' => ['player-platform'],
        'encounters' => ['dr-light', 'mega-man'],
        'items' => ['light-program', 'light-heart', 'copy-core'],
        'tags' => ['central', 'player', 'main', 'spawn']
        ];
    $index['light-area-1e'] = [
        'name' => 'LIGHT Area 1E',
        'position' => '13-8',
        'level' => 10,
        'field' => 'gentle-countryside',
        'encounters' => [],
        'items' => ['equip-codes'],
        'tags' => ['central', 'player']
        ];
    $index['light-area-1s'] = [
        'name' => 'LIGHT Area 1S',
        'position' => '12-9',
        'level' => 10,
        'field' => 'gentle-countryside',
        'rescues' => ['roll'],
        'tags' => ['central', 'player']
        ];
    $index['light-area-1w'] = [
        'name' => 'LIGHT Area 1W',
        'position' => '11-8',
        'level' => 10,
        'field' => 'gentle-countryside',
        'encounters' => [],
        'items' => ['item-codes'],
        'tags' => ['central', 'player']
        ];
    //--------------------------//
    $index['wily-area-1'] = [
        'name' => 'WILY Area 1',
        'position' => '19-14',
        'level' => 60,
        'field' => 'wily-castle',
        'objects' => ['player-platform'],
        'encounters' => ['dr-wily', 'bass'],
        'items' => ['wily-program', 'wily-heart', 'copy-core'],
        'tags' => ['central', 'player', 'main', 'spawn']
        ];
    $index['wily-area-1n'] = [
        'name' => 'WILY Area 1N',
        'position' => '19-13',
        'level' => 80,
        'field' => 'maniacal-hideaway',
        'encounters' => [],
        'items' => ['ability-codes'],
        'tags' => ['central', 'player']
        ];
    $index['wily-area-1w'] = [
        'name' => 'WILY Area 1W',
        'position' => '18-14',
        'level' => 40,
        'field' => 'maniacal-hideaway',
        'encounters' => ['trill'],
        'rescues' => ['disco'],
        'tags' => ['central', 'player']
        ];
    $index['wily-area-1w2'] = [
        'name' => 'WILY Area 1W2',
        'position' => '17-14',
        'level' => 20,
        'field' => 'maniacal-hideaway',
        'encounters' => [],
        'items' => ['weapon-codes'],
        'tags' => ['central', 'player']
        ];
    //--------------------------//
    $index['cossack-area-1'] = [
        'name' => 'COSSACK Area 1',
        'position' => '5-14',
        'level' => 60,
        'field' => 'cossack-citadel',
        'objects' => ['player-platform'],
        'encounters' => ['dr-cossack', 'proto-man'],
        'items' => ['cossack-program', 'cossack-heart', 'copy-core'],
        'tags' => ['central', 'player', 'main', 'spawn']
        ];
    $index['cossack-area-1n'] = [
        'name' => 'COSSACK Area 1N',
        'position' => '5-13',
        'level' => 80,
        'field' => 'wintry-forefront',
        'encounters' => [],
        'items' => ['master-codes'],
        'tags' => ['central', 'player']
        ];
    $index['cossack-area-1e'] = [
        'name' => 'COSSACK Area 1E',
        'position' => '6-14',
        'level' => 40,
        'field' => 'wintry-forefront',
        'encounters' => ['trill'],
        'rescues' => ['rhythm'],
        'tags' => ['central', 'player']
        ];
    $index['cossack-area-1e2'] = [
        'name' => 'COSSACK Area 1E2',
        'position' => '7-14',
        'level' => 20,
        'field' => 'wintry-forefront',
        'encounters' => [],
        'items' => ['dress-codes'],
        'tags' => ['central', 'player']
        ];
    //--------------------------//
    $index['lalinde-area-1'] = [
        'name' => 'LALINDE Area 1',
        'position' => '12-18',
        'level' => 100,
        'field' => '',
        'objects' => ['player-platform'],
        'encounters' => ['dr-lalinde'],
        'items' => ['lalinde-program', 'empty-heart', 'empty-core', 'mecha-whistle'],
        'tags' => ['central', 'player', 'main', 'spawn']
        ];
    $index['lalinde-area-1n'] = [
        'name' => 'LALINDE Area 1N',
        'position' => '12-17',
        'level' => 90,
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['beat'],
        'items' => ['field-booster'],
        'tags' => ['central', 'player']
        ];
    $index['lalinde-area-1e'] = [
        'name' => 'LALINDE Area 1E',
        'position' => '13-18',
        'level' => 120,
        'field' => '',
        'encounters' => [],
        'tags' => ['central', 'player']
        ];
    $index['lalinde-area-1w'] = [
        'name' => 'LALINDE Area 1W',
        'position' => '11-18',
        'level' => 120,
        'field' => '',
        'encounters' => [],
        'tags' => ['central', 'player']
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
        'tags' => ['outer', 'main']
        ];
    $index['storage-area-2'] = [
        'name' => 'STORAGE Area 2',
        'position' => '23-6',
        'level' => 140,
        'field' => 'robot-museum',
        'encounters' => ['trill'],
        'rescues' => ['treble'],
        'items' => ['speed-booster', 'hyper-screw'],
        'tags' => ['outer', 'main2']
        ];
    //--------------------------//
    $index['storage-area-1s'] = [
        'name' => 'STORAGE Area 1S',
        'position' => '23-9',
        'level' => 160,
        'field' => 'robot-museum',
        'encounters' => ['weapon-archivist'],
        'items' => ['defense-booster', 'hyper-screw'],
        'tags' => ['outer']
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
        'tags' => ['outer', 'main']
        ];
    $index['control-area-2'] = [
        'name' => 'CONTROL Area 2',
        'position' => '1-6',
        'level' => 140,
        'field' => 'royal-palace',
        'encounters' => ['trill'],
        'rescues' => ['tango'],
        'items' => ['speed-diverter', 'hyper-screw'],
        'tags' => ['outer', 'main2']
        ];
    //--------------------------//
    $index['control-area-1s'] = [
        'name' => 'CONTROL Area 1S',
        'position' => '1-9',
        'level' => 160,
        'field' => 'royal-palace',
        'encounters' => ['weapon-archivist'],
        'items' => ['defense-diverter', 'hyper-screw'],
        'tags' => ['outer']
        ];
    //--------------------------//
    $index['security-area-1'] = [
        'name' => 'SECURITY Area 1',
        'position' => '14-16',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'items' => ['yashichi'],
        'tags' => ['outer', 'main']
        ];
    $index['security-area-2'] = [
        'name' => 'SECURITY Area 2',
        'position' => '15-16',
        'level' => 120,
        'field' => '',
        'encounters' => [],
        'tags' => ['outer']
        ];
    $index['security-area-3'] = [
        'name' => 'SECURITY Area 3',
        'position' => '16-16',
        'level' => 140,
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['reggae'],
        'items' => ['attack-booster', 'hyper-screw'],
        'tags' => ['outer']
        ];
    //--------------------------//
    $index['research-area-1'] = [
        'name' => 'RESEARCH Area 1',
        'position' => '10-16',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'items' => ['yashichi'],
        'tags' => ['outer', 'main']
        ];
    $index['research-area-2'] = [
        'name' => 'RESEARCH Area 2',
        'position' => '9-16',
        'level' => 120,
        'field' => '',
        'encounters' => [],
        'tags' => ['outer']
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
        'tags' => ['outer']
        ];
    //--------------------------//
    $index['hunter-area-1'] = [
        'name' => 'HUNTER Area 1',
        'position' => '17-4',
        'level' => 140,
        'field' => 'hunter-compound',
        'encounters' => ['enker'],
        'encounters2' => ['mouslider', 'picket-man'],
        'tags' => ['upper', 'main']
        ];
    $index['hunter-area-2'] = [
        'name' => 'HUNTER Area 2',
        'position' => '17-3',
        'level' => 150,
        'field' => 'hunter-compound',
        'encounters2' => ['mouslider', 'picket-man'],
        'encounters' => ['punk'],
        'tags' => ['upper']
        ];
    $index['hunter-area-3'] = [
        'name' => 'HUNTER Area 3',
        'position' => '17-2',
        'level' => 160,
        'field' => 'hunter-compound',
        'encounters' => ['ballade'],
        'encounters2' => ['mouslider', 'picket-man'],
        'items' => ['hyper-screw'],
        'tags' => ['upper']
        ];
    //--------------------------//
    $index['genesis-area-1'] = [
        'name' => 'GENESIS Area 1',
        'position' => '7-4',
        'level' => 140,
        'field' => 'genesis-tower',
        'encounters' => ['buster-rod-g'],
        'encounters2' => ['beak', 'pooker', 'moller'],
        'tags' => ['upper', 'main']
        ];
    $index['genesis-area-2'] = [
        'name' => 'GENESIS Area 2',
        'position' => '7-3',
        'level' => 150,
        'field' => 'genesis-tower',
        'encounters' => ['mega-water-s'],
        'encounters2' => ['moller', 'colton', 'shield-attacker-gtr'],
        'tags' => ['upper']
        ];
    $index['genesis-area-3'] = [
        'name' => 'GENESIS Area 3',
        'position' => '7-2',
        'level' => 160,
        'field' => 'genesis-tower',
        'encounters' => ['hyper-storm-h'],
        'encounters2' => ['beak', 'pooker', 'shield-attacker-gtr', 'propeller-eye'],
        'items' => ['hyper-screw'],
        'tags' => ['upper']
        ];
    //--------------------------//
    $index['hifi-area-1'] = [
        'name' => 'HiFi Area 1',
        'position' => '20-4',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'tags' => ['upper', 'path']
        ];
    $index['hifi-area-2'] = [
        'name' => 'HiFi Area 2',
        'position' => '20-3',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'tags' => ['upper', 'path']
        ];
    //--------------------------//
    $index['stellar-area-1'] = [
        'name' => 'STELLAR Area 1',
        'position' => '22-2',
        'level' => 120,
        'field' => 'stardroid-base',
        'encounters' => ['mercury', 'venus', 'neptune', 'jupiter'],
        'encounters2' => ['malmet', 'covercannon', 'biribaree'],
        'tags' => ['upper', 'special', 'main']
        ];
    $index['stellar-area-2'] = [
        'name' => 'STELLAR Area 2',
        'position' => '23-2',
        'level' => 140,
        'field' => 'stardroid-base',
        'encounters' => ['mars', 'saturn', 'uranus', 'pluto'],
        'encounters2' => ['malmet', 'covercannon', 'biribaree'],
        'tags' => ['upper', 'special']
        ];
    $index['stellar-area-3'] = [
        'name' => 'STELLAR Area 3',
        'position' => '23-1',
        'level' => 160,
        'field' => 'stardroid-base',
        'encounters' => ['sunstar'],
        'encounters2' => ['novamite', 'malmet'],
        'items' => ['cosmo-circuit'],
        'tags' => ['upper', 'special']
        ];
    //--------------------------//
    $index['lofi-area-1'] = [
        'name' => 'LoFi Area 1',
        'position' => '4-4',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'tags' => ['upper', 'path']
        ];
    $index['lofi-area-2'] = [
        'name' => 'LoFi Area 2',
        'position' => '4-3',
        'level' => 100,
        'field' => '',
        'encounters' => [],
        'tags' => ['upper', 'path']
        ];
    //--------------------------//
    $index['paradox-area-1'] = [
        'name' => 'PARADOX Area 1',
        'position' => '2-2',
        'level' => 120,
        'field' => 'prototype-complete',
        'encounters' => ['piano'],
        'tags' => ['upper', 'special', 'main']
        ];
    $index['paradox-area-2'] = [
        'name' => 'PARADOX Area 2',
        'position' => '1-2',
        'level' => 140,
        'field' => 'prototype-complete',
        'encounters' => [],
        'tags' => ['upper', 'special']
        ];
    $index['paradox-area-3'] = [
        'name' => 'PARADOX Area 3',
        'position' => '1-1',
        'level' => 160,
        'field' => 'prototype-complete',
        'encounters' => ['quint'],
        'items' => ['gambit-module'],
        'tags' => ['upper', 'special']
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
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['explode-area-1e'] = [
        'name' => 'EXPLODE Area 1E',
        'position' => '16-8',
        'level' => 40,
        'element' => 'explode',
        'field' => '',
        'encounters' => ['blast-man'],
        'items' => ['extra-life'],
        'tags' => ['central', 'elemental']
        ];
    $index['explode-area-1s'] = [
        'name' => 'EXPLODE Area 1S',
        'position' => '15-9',
        'level' => 40,
        'element' => 'explode',
        'field' => 'trenchwork-depot',
        'encounters' => ['napalm-man', 'grenade-man'],
        'encounters2' => ['bombardier', 'moller'],
        'items' => ['xtreme-module'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental', 'main']
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
        'tags' => ['central', 'elemental']
        ];
    $index['impact-area-1e'] = [
        'name' => 'IMPACT Area 1E',
        'position' => '13-11',
        'level' => 40,
        'element' => 'impact',
        'field' => '',
        'encounters' => ['strike-man'],
        'items' => ['weapon-upgrade'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['cutter-area-1'] = [
        'name' => 'CUTTER Area 1',
        'position' => '9-8',
        'level' => 30,
        'element' => 'cutter',
        'field' => 'industrial-facility',
        'encounters' => ['metal-man'],
        'encounters2' => ['pierrobot', 'drill-mole', 'spring-head'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental', 'main']
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
        'tags' => ['central', 'elemental']
        ];
    $index['cutter-area-1s'] = [
        'name' => 'CUTTER Area 1S',
        'position' => '9-9',
        'level' => 40,
        'element' => 'cutter',
        'field' => '',
        'encounters' => ['blade-man', 'yamato-man', 'knight-man'],
        'items' => ['repair-module'],
        'tags' => ['central', 'elemental']
        ];
    $index['cutter-area-1w'] = [
        'name' => 'CUTTER Area 1W',
        'position' => '8-8',
        'level' => 40,
        'element' => 'cutter',
        'field' => 'construction-site',
        'encounters' => ['needle-man'],
        'encounters2' => ['needle-ned', 'hammer-joe', 'elecin'],
        'items' => ['extra-life'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['flame-area-1'] = [
        'name' => 'FLAME Area 1',
        'position' => '18-8',
        'level' => 60,
        'element' => 'flame',
        'field' => 'atomic-furnace',
        'encounters' => ['heat-man'],
        'encounters2' => ['telly', 'popo-heli'],
        'tags' => ['central', 'elemental', 'main']
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
        'tags' => ['central', 'elemental']
        ];
    $index['flame-area-1s'] = [
        'name' => 'FLAME Area 1S',
        'position' => '18-9',
        'level' => 70,
        'element' => 'flame',
        'field' => '',
        'encounters' => ['solar-man'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental']
        ];
    $index['flame-area-1w'] = [
        'name' => 'FLAME Area 1W',
        'position' => '17-8',
        'level' => 50,
        'element' => 'flame',
        'field' => 'steel-mill',
        'encounters' => ['fire-man', 'torch-man'],
        'encounters2' => ['tackle-fire', 'bombomb', 'telly'],
        'items' => ['uptick-module'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental', 'main']
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
        'tags' => ['central', 'elemental']
        ];
    $index['swift-area-1e'] = [
        'name' => 'SWIFT Area 1E',
        'position' => '13-13',
        'level' => 60,
        'element' => 'swift',
        'field' => '',
        'encounters' => ['bounce-man'],
        'items' => ['weapon-tank'],
        'tags' => ['central', 'elemental']
        ];
    $index['swift-area-1w'] = [
        'name' => 'SWIFT Area 1W',
        'position' => '11-13',
        'level' => 60,
        'element' => 'swift',
        'field' => 'sonic-highway',
        'encounters' => ['turbo-man', 'nitro-man'],
        'encounters2' => ['robo-transport', 'cannon-roader'],
        'items' => ['energy-tank'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['freeze-area-1'] = [
        'name' => 'FREEZE Area 1',
        'position' => '6-8',
        'level' => 60,
        'element' => 'freeze',
        'field' => '',
        'encounters' => ['tundra-man'],
        'items' => ['bulwark-module'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['freeze-area-1e'] = [
        'name' => 'FREEZE Area 1E',
        'position' => '7-8',
        'level' => 50,
        'element' => 'freeze',
        'field' => 'arctic-jungle',
        'encounters' => ['ice-man', 'blizzard-man', 'big-eye'],
        'encounters2' => ['peng', 'spine', 'adhering-suzy', 'foot-holder'],
        'items' => ['charge-module'],
        'tags' => ['central', 'elemental']
        ];
    $index['freeze-area-1s'] = [
        'name' => 'FREEZE Area 1S',
        'position' => '6-9',
        'level' => 70,
        'element' => 'freeze',
        'field' => '',
        'encounters' => ['cold-man', 'frost-man'],
        'items' => ['mecha-whistle'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['wind-area-1'] = [
        'name' => 'WIND Area 1',
        'position' => '18-6',
        'level' => 80,
        'element' => 'wind',
        'field' => '',
        'encounters' => ['tornado-man', 'cloud-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['wind-area-1e'] = [
        'name' => 'WIND Area 1E',
        'position' => '19-6',
        'level' => 90,
        'element' => 'wind',
        'field' => '',
        'encounters' => ['wind-man', 'gyro-man'],
        'rescues' => ['quake-woman'],
        'items' => ['transport-module'],
        'tags' => ['central', 'elemental']
        ];
    $index['wind-area-1s'] = [
        'name' => 'WIND Area 1S',
        'position' => '18-7',
        'level' => 70,
        'element' => 'wind',
        'field' => 'sky-ridge',
        'encounters' => ['air-man'],
        'encounters2' => ['fan-fiend', 'foot-holder'],
        'items' => ['resetchi'],
        'tags' => ['central', 'elemental']
        ];
    $index['wind-area-1w'] = [
        'name' => 'WIND Area 1W',
        'position' => '17-6',
        'level' => 90,
        'element' => 'wind',
        'field' => 'rusty-scrapheap',
        'encounters' => ['dust-man', 'junk-man'],
        'encounters2' => ['lady-blader', 'upndown'],
        'items' => ['salvage-module'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['shadow-area-1n'] = [
        'name' => 'SHADOW Area 1N',
        'position' => '21-7',
        'level' => 100,
        'element' => 'shadow',
        'field' => 'haunted-mansion',
        'encounters' => ['shade-man'],
        'encounters2' => ['astro-zombieg', 'batton'],
        'items' => ['siphon-module'],
        'tags' => ['central', 'elemental']
        ];
    $index['shadow-area-1s'] = [
        'name' => 'SHADOW Area 1S',
        'position' => '21-9',
        'level' => 100,
        'element' => 'shadow',
        'field' => '',
        'encounters' => ['magic-man', 'clown-man'],
        'items' => ['weapon-tank'],
        'tags' => ['central', 'elemental']
        ];
    $index['shadow-area-1w'] = [
        'name' => 'SHADOW Area 1W',
        'position' => '20-8',
        'level' => 80,
        'element' => 'shadow',
        'field' => 'septic-system',
        'encounters' => ['shadow-man'],
        'encounters2' => ['ribbitron', 'hammer-joe', 'spin-fiend', 'elecin'],
        'items' => ['super-capsule'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['electric-area-1'] = [
        'name' => 'ELECTRIC Area 1',
        'position' => '18-11',
        'level' => 90,
        'element' => 'electric',
        'field' => 'power-plant',
        'encounters' => ['spark-man', 'plug-man', 'giant-spring-head'],
        'encounters2' => ['elecin', 'hammer-joe', 'nitron-y', 'peterchy'],
        'tags' => ['central', 'elemental', 'main']
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
        'tags' => ['central', 'elemental']
        ];
    $index['electric-area-1e'] = [
        'name' => 'ELECTRIC Area 1E',
        'position' => '19-11',
        'level' => 100,
        'element' => 'electric',
        'field' => '',
        'encounters' => ['dynamo-man', 'fuse-man'],
        'items' => ['resetchi'],
        'tags' => ['central', 'elemental']
        ];
    $index['electric-area-1w'] = [
        'name' => 'ELECTRIC Area 1W',
        'position' => '17-11',
        'level' => 100,
        'element' => 'electric',
        'field' => '',
        'encounters' => ['gravity-man', 'sheep-man'],
        'items' => ['battery-circuit'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['crystal-area-1'] = [
        'name' => 'CRYSTAL Area 1',
        'position' => '15-13',
        'level' => 80,
        'element' => 'crystal',
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['meddy'],
        'items' => ['super-capsule'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['crystal-area-1n'] = [
        'name' => 'CRYSTAL Area 1N',
        'position' => '15-12',
        'level' => 90,
        'element' => 'crystal',
        'field' => 'gemstone-cavern',
        'encounters' => ['jewel-man'],
        'encounters2' => ['diaymon', 'spike-pusher'],
        'items' => ['distill-module'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental']
        ];
    $index['crystal-area-1w'] = [
        'name' => 'CRYSTAL Area 1W',
        'position' => '14-13',
        'level' => 70,
        'element' => 'crystal',
        'field' => 'crystal-catacombs',
        'encounters' => ['crystal-man'],
        'encounters2' => ['crystal-joe', 'tatepakkan'],
        'items' => ['hyperscan-module'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['time-area-1n'] = [
        'name' => 'TIME Area 1N',
        'position' => '12-14',
        'level' => 70,
        'element' => 'time',
        'field' => 'clock-citadel',
        'encounters' => ['time-man'],
        'encounters2' => ['flutter-fly'],
        'items' => ['extra-life'],
        'tags' => ['central', 'elemental']
        ];
    $index['time-area-1e'] = [
        'name' => 'TIME Area 1E',
        'position' => '13-15',
        'level' => 90,
        'element' => 'time',
        'field' => '',
        'encounters' => ['centaur-man'],
        'items' => ['hourglass-module'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['missile-area-1'] = [
        'name' => 'MISSILE Area 1',
        'position' => '9-13',
        'level' => 80,
        'element' => 'missile',
        'field' => '',
        'encounters' => ['search-man'],
        'items' => ['super-capsule'],
        'tags' => ['central', 'elemental', 'main']
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
        'tags' => ['central', 'elemental']
        ];
    $index['missile-area-1e'] = [
        'name' => 'MISSILE Area 1E',
        'position' => '10-13',
        'level' => 70,
        'element' => 'missile',
        'field' => 'magnetic-generator',
        'encounters' => ['magnet-man'],
        'encounters2' => ['mag-fly', 'elecin', 'peterchy'],
        'items' => ['magnet-module'],
        'tags' => ['central', 'elemental']
        ];
    $index['missile-area-1s'] = [
        'name' => 'MISSILE Area 1S',
        'position' => '9-14',
        'level' => 90,
        'element' => 'missile',
        'field' => 'minefield-dunes',
        'encounters' => ['commando-man'],
        'encounters2' => ['antlion-g', 'bombardier'],
        'items' => ['target-module'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['nature-area-1e'] = [
        'name' => 'NATURE Area 1E',
        'position' => '7-6',
        'level' => 90,
        'element' => 'nature',
        'field' => '',
        'encounters' => ['slash-man', 'tengu-man'],
        'items' => ['sapling-circuit'],
        'tags' => ['central', 'elemental']
        ];
    $index['nature-area-1s'] = [
        'name' => 'NATURE Area 1S',
        'position' => '6-7',
        'level' => 70,
        'element' => 'nature',
        'field' => 'preserved-forest',
        'encounters' => ['wood-man', 'burner-man'],
        'encounters2' => ['batton', 'kabuton', 'blocky'],
        'items' => ['resetchi'],
        'tags' => ['central', 'elemental']
        ];
    $index['nature-area-1w'] = [
        'name' => 'NATURE Area 1W',
        'position' => '5-6',
        'level' => 90,
        'element' => 'nature',
        'field' => '',
        'encounters' => ['plant-man', 'hornet-man'],
        'rescues' => ['vesper-woman'],
        'items' => ['growth-module'],
        'tags' => ['central', 'elemental']
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
        'tags' => ['central', 'elemental', 'main']
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
        'tags' => ['central', 'elemental']
        ];
    $index['water-area-1e'] = [
        'name' => 'WATER Area 1E',
        'position' => '7-11',
        'level' => 100,
        'element' => 'water',
        'field' => '',
        'encounters' => ['burst-man', 'aqua-man', 'acid-man'],
        'items' => ['alchemy-module'],
        'tags' => ['central', 'elemental']
        ];
    $index['water-area-1w'] = [
        'name' => 'WATER Area 1W',
        'position' => '5-11',
        'level' => 100,
        'element' => 'water',
        'field' => '',
        'encounters' => ['splash-woman', 'wave-man', 'pirate-man'],
        'items' => ['sponge-circuit'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['earth-area-1'] = [
        'name' => 'EARTH Area 1',
        'position' => '3-8',
        'level' => 90,
        'element' => 'earth',
        'field' => '',
        'encounters' => ['block-man', 'stone-man'],
        'tags' => ['central', 'elemental', 'main']
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
        'tags' => ['central', 'elemental']
        ];
    $index['earth-area-1e'] = [
        'name' => 'EARTH Area 1E',
        'position' => '4-8',
        'level' => 80,
        'element' => 'earth',
        'field' => 'oil-wells',
        'encounters' => ['oil-man', 'flame-man'],
        'encounters2' => ['beetle-borg'],
        'items' => ['super-capsule'],
        'tags' => ['central', 'elemental']
        ];
    $index['earth-area-1s'] = [
        'name' => 'EARTH Area 1S',
        'position' => '3-9',
        'level' => 100,
        'element' => 'earth',
        'field' => 'sunset-gulch',
        'encounters' => ['tomohawk-man', 'charge-man', 'magma-man'],
        'encounters2' => ['colton', 'merserker'],
        'items' => ['energy-tank'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['space-area-1'] = [
        'name' => 'SPACE Area 1',
        'position' => '12-5',
        'level' => 100,
        'element' => 'space',
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['rush'],
        'items' => ['reverse-module'],
        'tags' => ['upper', 'elemental', 'main']
        ];
    //--------------------------//
    $index['space-area-1e'] = [
        'name' => 'SPACE Area 1E',
        'position' => '13-4',
        'level' => 110,
        'element' => 'space',
        'field' => 'satellite-deck',
        'encounters' => ['star-man'],
        'encounters2' => ['novamite', 'shururun', 'flipping-suzy', 'mouslider', 'pukapunter'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-1e2'] = [
        'name' => 'SPACE Area 1E2',
        'position' => '14-4',
        'level' => 120,
        'element' => 'space',
        'field' => 'satellite-deck',
        'encounters' => ['galaxy-man'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-1e3'] = [
        'name' => 'SPACE Area 1E3',
        'position' => '15-4',
        'level' => 130,
        'element' => 'space',
        'field' => 'satellite-deck',
        'encounters' => ['terra'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['space-area-1w'] = [
        'name' => 'SPACE Area 1W',
        'position' => '11-4',
        'level' => 110,
        'element' => 'space',
        'field' => 'space-simulator',
        'encounters' => ['ring-man', 'gachappon'],
        'encounters2' => ['ring-ring', 'adamski'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-1w2'] = [
        'name' => 'SPACE Area 1W2',
        'position' => '10-4',
        'level' => 120,
        'element' => 'space',
        'field' => 'space-simulator',
        'encounters' => ['astro-man'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-1w3'] = [
        'name' => 'SPACE Area 1W3',
        'position' => '9-4',
        'level' => 130,
        'element' => 'space',
        'field' => 'space-simulator',
        'encounters' => ['ra-thor'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['space-area-2'] = [
        'name' => 'SPACE Area 2',
        'position' => '12-2',
        'level' => 200,
        'element' => 'space',
        'field' => 'final-destination-3',
        'encounters' => ['slur'],
        'tags' => ['upper', 'elemental', 'main', 'final']
        ];
    $index['space-area-2n'] = [
        'name' => 'SPACE Area 2N',
        'position' => '12-1',
        'level' => 999,
        'element' => 'space',
        'field' => 'final-destination-3',
        'encounters' => ['proxy'],
        'items' => ['empty-core'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['space-area-2e'] = [
        'name' => 'SPACE Area 2E',
        'position' => '14-2',
        'level' => 150,
        'element' => 'space',
        'field' => 'final-destination-2',
        'encounters' => ['dark-man-4'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-2e2'] = [
        'name' => 'SPACE Area 2E2',
        'position' => '15-2',
        'level' => 140,
        'element' => 'space',
        'field' => 'final-destination',
        'encounters' => ['dark-man', 'dark-man-2', 'dark-man-3'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['space-area-2w'] = [
        'name' => 'SPACE Area 2W',
        'position' => '10-2',
        'level' => 150,
        'element' => 'space',
        'field' => 'final-destination-2',
        'encounters' => ['quake-woman-ds'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-2w2'] = [
        'name' => 'SPACE Area 2W2',
        'position' => '9-2',
        'level' => 140,
        'element' => 'space',
        'field' => 'final-destination',
        'encounters' => ['mega-man-ds', 'bass-ds', 'proto-man-ds'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['laser-area-1'] = [
        'name' => 'LASER Area 1',
        'position' => '21-16',
        'level' => 300,
        'element' => 'laser',
        'field' => '',
        'encounters' => ['laser-man'],
        'items' => ['hyper-screw'],
        'tags' => ['outer', 'special', 'elemental', 'main']
        ];
    $index['laser-area-1e'] = [
        'name' => 'LASER Area 1E',
        'position' => '22-16',
        'level' => 250,
        'element' => 'laser',
        'field' => '',
        'encounters' => [],
        'tags' => ['outer', 'special', 'elemental']
        ];
    $index['laser-area-1ne'] = [
        'name' => 'LASER Area 1NE',
        'position' => '22-15',
        'level' => 200,
        'element' => 'laser',
        'field' => '',
        'encounters' => [],
        'items' => ['extra-life'],
        'tags' => ['outer', 'special', 'elemental']
        ];
    //--------------------------//
    $index['shield-area-1'] = [
        'name' => 'SHIELD Area 1',
        'position' => '3-16',
        'level' => 300,
        'element' => 'shield',
        'field' => '',
        'encounters' => ['shield-man'],
        'items' => ['hyper-screw'],
        'tags' => ['outer', 'special', 'elemental', 'main']
        ];
    $index['shield-area-1s'] = [
        'name' => 'SHIELD Area 1W',
        'position' => '2-16',
        'level' => 250,
        'element' => 'shield',
        'field' => '',
        'encounters' => [],
        'tags' => ['outer', 'special', 'elemental']
        ];
    $index['shield-area-1se'] = [
        'name' => 'SHIELD Area 1NW',
        'position' => '2-15',
        'level' => 200,
        'element' => 'shield',
        'field' => '',
        'encounters' => [],
        'items' => ['extra-life'],
        'tags' => ['outer', 'special', 'elemental']
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