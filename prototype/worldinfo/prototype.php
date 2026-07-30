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
        'encounters' => [],
        'tags' => ['start']
        ];
    //--------------------------//
    $index['light-area-1'] = [
        'name' => 'LIGHT Area 1',
        'position' => '12-8',
        'level' => 5,
        'field' => 'light-laboratory',
        'encounters' => ['dr-light', 'mega-man'],
        'tags' => ['central', 'player', 'main', 'spawn']
        ];
    $index['light-area-1e'] = [
        'name' => 'LIGHT Area 1E',
        'position' => '13-8',
        'level' => 10,
        'field' => 'gentle-countryside',
        'encounters' => [],
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
        'tags' => ['central', 'player']
        ];
    //--------------------------//
    $index['wily-area-1'] = [
        'name' => 'WILY Area 1',
        'position' => '19-14',
        'level' => 60,
        'field' => 'wily-castle',
        'encounters' => ['dr-wily', 'bass'],
        'tags' => ['central', 'player', 'main', 'spawn']
        ];
    $index['wily-area-1n'] = [
        'name' => 'WILY Area 1N',
        'position' => '19-13',
        'level' => 80,
        'field' => 'maniacal-hideaway',
        'encounters' => [],
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
        'tags' => ['central', 'player']
        ];
    //--------------------------//
    $index['cossack-area-1'] = [
        'name' => 'COSSACK Area 1',
        'position' => '5-14',
        'level' => 60,
        'field' => 'cossack-citadel',
        'encounters' => ['dr-cossack', 'proto-man'],
        'tags' => ['central', 'player', 'main', 'spawn']
        ];
    $index['cossack-area-1n'] = [
        'name' => 'COSSACK Area 1N',
        'position' => '5-13',
        'level' => 80,
        'field' => 'wintry-forefront',
        'encounters' => [],
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
        'tags' => ['central', 'player']
        ];
    //--------------------------//
    $index['lalinde-area-1'] = [
        'name' => 'LALINDE Area 1',
        'position' => '12-18',
        'level' => 100,
        'field' => '',
        'encounters' => ['dr-lalinde'],
        'tags' => ['central', 'player', 'main', 'spawn']
        ];
    $index['lalinde-area-1n'] = [
        'name' => 'LALINDE Area 1N',
        'position' => '12-17',
        'level' => 90,
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['beat'],
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
        'tags' => ['outer', 'main']
        ];
    $index['storage-area-2'] = [
        'name' => 'STORAGE Area 2',
        'position' => '23-6',
        'level' => 140,
        'field' => 'robot-museum',
        'encounters' => ['trill'],
        'rescues' => ['treble'],
        'tags' => ['outer', 'main2']
        ];
    //--------------------------//
    $index['storage-area-1s'] = [
        'name' => 'STORAGE Area 1S',
        'position' => '23-9',
        'level' => 160,
        'field' => 'robot-museum',
        'encounters' => ['weapon-archivist'],
        'tags' => ['outer']
        ];
    //--------------------------//
    $index['control-area-1'] = [
        'name' => 'CONTROL Area 1',
        'position' => '1-7',
        'level' => 120,
        'field' => 'royal-palace',
        'encounters' => ['king'],
        'tags' => ['outer', 'main']
        ];
    $index['control-area-2'] = [
        'name' => 'CONTROL Area 2',
        'position' => '1-6',
        'level' => 140,
        'field' => 'royal-palace',
        'encounters' => ['trill'],
        'rescues' => ['tango'],
        'tags' => ['outer', 'main2']
        ];
    //--------------------------//
    $index['control-area-1s'] = [
        'name' => 'CONTROL Area 1S',
        'position' => '1-9',
        'level' => 160,
        'field' => 'royal-palace',
        'encounters' => ['weapon-archivist'],
        'tags' => ['outer']
        ];
    //--------------------------//
    $index['security-area-1'] = [
        'name' => 'SECURITY Area 1',
        'position' => '14-16',
        'level' => 100,
        'field' => '',
        'encounters' => [],
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
        'tags' => ['outer']
        ];
    //--------------------------//
    $index['research-area-1'] = [
        'name' => 'RESEARCH Area 1',
        'position' => '10-16',
        'level' => 100,
        'field' => '',
        'encounters' => [],
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
        'tags' => ['outer']
        ];
    //--------------------------//
    $index['hunter-area-1'] = [
        'name' => 'HUNTER Area 1',
        'position' => '17-4',
        'level' => 140,
        'field' => 'hunter-compound',
        'encounters' => ['enker'],
        'tags' => ['upper', 'main']
        ];
    $index['hunter-area-2'] = [
        'name' => 'HUNTER Area 2',
        'position' => '17-3',
        'level' => 150,
        'field' => 'hunter-compound',
        'encounters' => ['punk'],
        'tags' => ['upper']
        ];
    $index['hunter-area-3'] = [
        'name' => 'HUNTER Area 3',
        'position' => '17-2',
        'level' => 160,
        'field' => 'hunter-compound',
        'encounters' => ['ballade'],
        'tags' => ['upper']
        ];
    //--------------------------//
    $index['genesis-area-1'] = [
        'name' => 'GENESIS Area 1',
        'position' => '7-4',
        'level' => 140,
        'field' => 'genesis-tower',
        'encounters' => ['buster-rod-g'],
        'tags' => ['upper', 'main']
        ];
    $index['genesis-area-2'] = [
        'name' => 'GENESIS Area 2',
        'position' => '7-3',
        'level' => 150,
        'field' => 'genesis-tower',
        'encounters' => ['mega-water-s'],
        'tags' => ['upper']
        ];
    $index['genesis-area-3'] = [
        'name' => 'GENESIS Area 3',
        'position' => '7-2',
        'level' => 160,
        'field' => 'genesis-tower',
        'encounters' => ['hyper-storm-h'],
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
        'tags' => ['upper', 'special', 'main']
        ];
    $index['stellar-area-2'] = [
        'name' => 'STELLAR Area 2',
        'position' => '23-2',
        'level' => 140,
        'field' => 'stardroid-base',
        'encounters' => ['mars', 'saturn', 'uranus', 'pluto'],
        'tags' => ['upper', 'special']
        ];
    $index['stellar-area-3'] = [
        'name' => 'STELLAR Area 3',
        'position' => '23-1',
        'level' => 160,
        'field' => 'stardroid-base',
        'encounters' => ['sunstar'],
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
        'tags' => ['upper', 'special']
        ];
    //--------------------------//
    $index['explode-area-1'] = [
        'name' => 'EXPLODE Area 1',
        'position' => '15-8',
        'level' => 30,
        'field' => 'pipe-station',
        'encounters' => ['crash-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['explode-area-1e'] = [
        'name' => 'EXPLODE Area 1E',
        'position' => '16-8',
        'level' => 40,
        'field' => '',
        'encounters' => ['blast-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['explode-area-1s'] = [
        'name' => 'EXPLODE Area 1S',
        'position' => '15-9',
        'level' => 40,
        'field' => 'trenchwork-depot',
        'encounters' => ['napalm-man', 'grenade-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['explode-area-1w'] = [
        'name' => 'EXPLODE Area 1W',
        'position' => '14-8',
        'level' => 20,
        'field' => 'orb-city',
        'encounters' => ['bomb-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['impact-area-1'] = [
        'name' => 'IMPACT Area 1',
        'position' => '12-11',
        'level' => 30,
        'field' => 'rocky-plateau',
        'encounters' => ['hard-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['impact-area-1n'] = [
        'name' => 'IMPACT Area 1N',
        'position' => '12-10',
        'level' => 20,
        'field' => 'mountain-mines',
        'encounters' => ['guts-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['impact-area-1e'] = [
        'name' => 'IMPACT Area 1E',
        'position' => '13-11',
        'level' => 40,
        'field' => '',
        'encounters' => ['strike-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['impact-area-1w'] = [
        'name' => 'IMPACT Area 1W',
        'position' => '11-11',
        'level' => 40,
        'field' => 'waterworks-dam',
        'encounters' => ['concrete-man', 'impact-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['cutter-area-1'] = [
        'name' => 'CUTTER Area 1',
        'position' => '9-8',
        'level' => 30,
        'field' => 'industrial-facility',
        'encounters' => ['metal-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['cutter-area-1e'] = [
        'name' => 'CUTTER Area 1E',
        'position' => '10-8',
        'level' => 20,
        'field' => 'abandoned-warehouse',
        'encounters' => ['cut-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['cutter-area-1s'] = [
        'name' => 'CUTTER Area 1S',
        'position' => '9-9',
        'level' => 40,
        'field' => '',
        'encounters' => ['blade-man', 'yamato-man', 'knight-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['cutter-area-1w'] = [
        'name' => 'CUTTER Area 1W',
        'position' => '8-8',
        'level' => 40,
        'field' => 'construction-site',
        'encounters' => ['needle-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['flame-area-1'] = [
        'name' => 'FLAME Area 1',
        'position' => '18-8',
        'level' => 60,
        'field' => 'atomic-furnace',
        'encounters' => ['heat-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['flame-area-1e'] = [
        'name' => 'FLAME Area 1E',
        'position' => '19-8',
        'level' => 70,
        'field' => 'egyptian-excavation',
        'encounters' => ['pharaoh-man', 'sword-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['flame-area-1s'] = [
        'name' => 'FLAME Area 1S',
        'position' => '18-9',
        'level' => 70,
        'field' => '',
        'encounters' => ['solar-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['flame-area-1w'] = [
        'name' => 'FLAME Area 1W',
        'position' => '17-8',
        'level' => 50,
        'field' => 'steel-mill',
        'encounters' => ['fire-man', 'torch-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['swift-area-1'] = [
        'name' => 'SWIFT Area 1',
        'position' => '12-13',
        'level' => 50,
        'field' => 'spinning-greenhouse',
        'encounters' => ['top-man', 'spring-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['swift-area-1n'] = [
        'name' => 'SWIFT Area 1N',
        'position' => '11-13',
        'level' => 40,
        'field' => 'underground-laboratory',
        'encounters' => ['quick-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['swift-area-1e'] = [
        'name' => 'SWIFT Area 1E',
        'position' => '13-13',
        'level' => 60,
        'field' => '',
        'encounters' => ['bounce-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['swift-area-1w'] = [
        'name' => 'SWIFT Area 1W',
        'position' => '11-13',
        'level' => 60,
        'field' => 'sonic-highway',
        'encounters' => ['turbo-man', 'nitro-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['freeze-area-1'] = [
        'name' => 'FREEZE Area 1',
        'position' => '6-8',
        'level' => 60,
        'field' => '',
        'encounters' => ['tundra-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['freeze-area-1e'] = [
        'name' => 'FREEZE Area 1E',
        'position' => '7-8',
        'level' => 50,
        'field' => 'arctic-jungle',
        'encounters' => ['ice-man', 'blizzard-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['freeze-area-1s'] = [
        'name' => 'FREEZE Area 1S',
        'position' => '6-9',
        'level' => 70,
        'field' => '',
        'encounters' => ['cold-man', 'frost-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['freeze-area-1w'] = [
        'name' => 'FREEZE Area 1W',
        'position' => '5-8',
        'level' => 70,
        'field' => 'glacier-cradle',
        'encounters' => ['chill-man', 'freeze-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['wind-area-1'] = [
        'name' => 'WIND Area 1',
        'position' => '18-6',
        'level' => 80,
        'field' => '',
        'encounters' => ['tornado-man', 'cloud-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['wind-area-1e'] = [
        'name' => 'WIND Area 1E',
        'position' => '19-6',
        'level' => 90,
        'field' => '',
        'encounters' => ['wind-man', 'gyro-man'],
        'rescues' => ['quake-woman'],
        'tags' => ['central', 'elemental']
        ];
    $index['wind-area-1s'] = [
        'name' => 'WIND Area 1S',
        'position' => '18-7',
        'level' => 70,
        'field' => 'sky-ridge',
        'encounters' => ['air-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['wind-area-1w'] = [
        'name' => 'WIND Area 1W',
        'position' => '17-6',
        'level' => 90,
        'field' => 'rusty-scrapheap',
        'encounters' => ['dust-man', 'junk-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['shadow-area-1'] = [
        'name' => 'SHADOW Area 1',
        'position' => '21-8',
        'level' => 90,
        'field' => 'robosaur-boneyard',
        'encounters' => ['skull-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['shadow-area-1n'] = [
        'name' => 'SHADOW Area 1N',
        'position' => '21-7',
        'level' => 100,
        'field' => 'haunted-mansion',
        'encounters' => ['shade-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['shadow-area-1s'] = [
        'name' => 'SHADOW Area 1S',
        'position' => '21-9',
        'level' => 100,
        'field' => '',
        'encounters' => ['magic-man', 'clown-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['shadow-area-1w'] = [
        'name' => 'SHADOW Area 1W',
        'position' => '20-8',
        'level' => 80,
        'field' => 'septic-system',
        'encounters' => ['shadow-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['electric-area-1'] = [
        'name' => 'ELECTRIC Area 1',
        'position' => '18-11',
        'level' => 90,
        'field' => 'power-plant',
        'encounters' => ['spark-man', 'plug-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['electric-area-1n'] = [
        'name' => 'ELECTRIC Area 1N',
        'position' => '18-10',
        'level' => 80,
        'field' => 'electrical-tower',
        'encounters' => ['elec-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['electric-area-1e'] = [
        'name' => 'ELECTRIC Area 1E',
        'position' => '19-11',
        'level' => 100,
        'field' => '',
        'encounters' => ['dynamo-man', 'fuse-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['electric-area-1w'] = [
        'name' => 'ELECTRIC Area 1W',
        'position' => '17-11',
        'level' => 100,
        'field' => '',
        'encounters' => ['gravity-man', 'sheep-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['crystal-area-1'] = [
        'name' => 'CRYSTAL Area 1',
        'position' => '15-13',
        'level' => 80,
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['meddy'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['crystal-area-1n'] = [
        'name' => 'CRYSTAL Area 1N',
        'position' => '15-12',
        'level' => 90,
        'field' => 'gemstone-cavern',
        'encounters' => ['jewel-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['crystal-area-1s'] = [
        'name' => 'CRYSTAL Area 1S',
        'position' => '15-14',
        'level' => 90,
        'field' => 'reflection-chamber',
        'encounters' => ['gemini-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['crystal-area-1w'] = [
        'name' => 'CRYSTAL Area 1W',
        'position' => '14-13',
        'level' => 70,
        'field' => 'crystal-catacombs',
        'encounters' => ['crystal-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['time-area-1'] = [
        'name' => 'TIME Area 1',
        'position' => '12-15',
        'level' => 80,
        'field' => 'photon-collider',
        'encounters' => ['flash-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['time-area-1n'] = [
        'name' => 'TIME Area 1N',
        'position' => '12-14',
        'level' => 70,
        'field' => 'clock-citadel',
        'encounters' => ['time-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['time-area-1e'] = [
        'name' => 'TIME Area 1E',
        'position' => '13-15',
        'level' => 90,
        'field' => '',
        'encounters' => ['centaur-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['time-area-1w'] = [
        'name' => 'TIME Area 1W',
        'position' => '11-15',
        'level' => 90,
        'field' => 'lighting-control',
        'encounters' => ['bright-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['missile-area-1'] = [
        'name' => 'MISSILE Area 1',
        'position' => '9-13',
        'level' => 80,
        'field' => '',
        'encounters' => ['search-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['missile-area-1n'] = [
        'name' => 'MISSILE Area 1N',
        'position' => '9-12',
        'level' => 90,
        'field' => 'submerged-armory',
        'encounters' => ['dive-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['missile-area-1e'] = [
        'name' => 'MISSILE Area 1E',
        'position' => '10-13',
        'level' => 70,
        'field' => 'magnetic-generator',
        'encounters' => ['magnet-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['missile-area-1s'] = [
        'name' => 'MISSILE Area 1S',
        'position' => '9-14',
        'level' => 90,
        'field' => 'minefield-dunes',
        'encounters' => ['commando-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['nature-area-1'] = [
        'name' => 'NATURE Area 1',
        'position' => '6-6',
        'level' => 80,
        'field' => 'serpent-column',
        'encounters' => ['snake-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['nature-area-1e'] = [
        'name' => 'NATURE Area 1E',
        'position' => '7-6',
        'level' => 90,
        'field' => '',
        'encounters' => ['slash-man', 'tengu-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['nature-area-1s'] = [
        'name' => 'NATURE Area 1S',
        'position' => '6-7',
        'level' => 70,
        'field' => 'preserved-forest',
        'encounters' => ['wood-man', 'burner-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['nature-area-1w'] = [
        'name' => 'NATURE Area 1W',
        'position' => '5-6',
        'level' => 90,
        'field' => '',
        'encounters' => ['plant-man', 'hornet-man'],
        'rescues' => ['vesper-woman'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['water-area-1'] = [
        'name' => 'WATER Area 1',
        'position' => '6-11',
        'level' => 90,
        'field' => 'rainy-sewers',
        'encounters' => ['toad-man', 'pump-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['water-area-1n'] = [
        'name' => 'WATER Area 1N',
        'position' => '6-10',
        'level' => 80,
        'field' => 'waterfall-institute',
        'encounters' => ['bubble-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['water-area-1e'] = [
        'name' => 'WATER Area 1E',
        'position' => '7-11',
        'level' => 100,
        'field' => '',
        'encounters' => ['burst-man', 'aqua-man', 'acid-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['water-area-1w'] = [
        'name' => 'WATER Area 1W',
        'position' => '5-11',
        'level' => 100,
        'field' => '',
        'encounters' => ['splash-woman', 'wave-man', 'pirate-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['earth-area-1'] = [
        'name' => 'EARTH Area 1',
        'position' => '3-8',
        'level' => 90,
        'field' => '',
        'encounters' => ['block-man', 'stone-man'],
        'tags' => ['central', 'elemental', 'main']
        ];
    $index['earth-area-1n'] = [
        'name' => 'EARTH Area 1N',
        'position' => '3-7',
        'level' => 100,
        'field' => 'mineral-quarry',
        'encounters' => ['drill-man', 'ground-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['earth-area-1e'] = [
        'name' => 'EARTH Area 1E',
        'position' => '4-8',
        'level' => 80,
        'field' => 'oil-wells',
        'encounters' => ['oil-man', 'flame-man'],
        'tags' => ['central', 'elemental']
        ];
    $index['earth-area-1s'] = [
        'name' => 'EARTH Area 1S',
        'position' => '3-9',
        'level' => 100,
        'field' => 'sunset-gulch',
        'encounters' => ['tomohawk-man', 'charge-man', 'magma-man'],
        'tags' => ['central', 'elemental']
        ];
    //--------------------------//
    $index['space-area-1'] = [
        'name' => 'SPACE Area 1',
        'position' => '12-5',
        'level' => 100,
        'field' => '',
        'encounters' => ['trill'],
        'rescues' => ['rush'],
        'tags' => ['upper', 'elemental', 'main']
        ];
    //--------------------------//
    $index['space-area-1e'] = [
        'name' => 'SPACE Area 1E',
        'position' => '13-4',
        'level' => 110,
        'field' => 'satellite-deck',
        'encounters' => ['star-man'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-1e2'] = [
        'name' => 'SPACE Area 1E2',
        'position' => '14-4',
        'level' => 120,
        'field' => 'satellite-deck',
        'encounters' => ['galaxy-man'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-1e3'] = [
        'name' => 'SPACE Area 1E3',
        'position' => '15-4',
        'level' => 130,
        'field' => 'satellite-deck',
        'encounters' => ['terra'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['space-area-1w'] = [
        'name' => 'SPACE Area 1W',
        'position' => '11-4',
        'level' => 110,
        'field' => 'space-simulator',
        'encounters' => ['ring-man'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-1w2'] = [
        'name' => 'SPACE Area 1W2',
        'position' => '10-4',
        'level' => 120,
        'field' => 'space-simulator',
        'encounters' => ['astro-man'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-1w3'] = [
        'name' => 'SPACE Area 1W3',
        'position' => '9-4',
        'level' => 130,
        'field' => 'space-simulator',
        'encounters' => ['ra-thor'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['space-area-2'] = [
        'name' => 'SPACE Area 2',
        'position' => '12-2',
        'level' => 200,
        'field' => 'final-destination-3',
        'encounters' => ['slur'],
        'tags' => ['upper', 'elemental', 'main', 'final']
        ];
    $index['space-area-2n'] = [
        'name' => 'SPACE Area 2N',
        'position' => '12-1',
        'level' => 999,
        'field' => 'final-destination-3',
        'encounters' => ['proxy'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['space-area-2e'] = [
        'name' => 'SPACE Area 2E',
        'position' => '14-2',
        'level' => 150,
        'field' => 'final-destination-2',
        'encounters' => ['dark-man-4'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-2e2'] = [
        'name' => 'SPACE Area 2E2',
        'position' => '15-2',
        'level' => 140,
        'field' => 'final-destination',
        'encounters' => ['dark-man', 'dark-man-2', 'dark-man-3'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['space-area-2w'] = [
        'name' => 'SPACE Area 2W',
        'position' => '10-2',
        'level' => 150,
        'field' => 'final-destination-2',
        'encounters' => ['quake-woman-ds'],
        'tags' => ['upper', 'elemental']
        ];
    $index['space-area-2w2'] = [
        'name' => 'SPACE Area 2W2',
        'position' => '9-2',
        'level' => 140,
        'field' => 'final-destination',
        'encounters' => ['mega-man-ds', 'bass-ds', 'proto-man-ds'],
        'tags' => ['upper', 'elemental']
        ];
    //--------------------------//
    $index['laser-area-1'] = [
        'name' => 'LASER Area 1',
        'position' => '21-16',
        'level' => 300,
        'field' => '',
        'encounters' => ['laser-man'],
        'tags' => ['outer', 'special', 'elemental', 'main']
        ];
    $index['laser-area-1e'] = [
        'name' => 'LASER Area 1E',
        'position' => '22-16',
        'level' => 250,
        'field' => '',
        'encounters' => [],
        'tags' => ['outer', 'special', 'elemental']
        ];
    $index['laser-area-1ne'] = [
        'name' => 'LASER Area 1NE',
        'position' => '22-15',
        'level' => 200,
        'field' => '',
        'encounters' => [],
        'tags' => ['outer', 'special', 'elemental']
        ];
    //--------------------------//
    $index['shield-area-1'] = [
        'name' => 'SHIELD Area 1',
        'position' => '3-16',
        'level' => 300,
        'field' => '',
        'encounters' => ['shield-man'],
        'tags' => ['outer', 'special', 'elemental', 'main']
        ];
    $index['shield-area-1s'] = [
        'name' => 'SHIELD Area 1S',
        'position' => '3-17',
        'level' => 250,
        'field' => '',
        'encounters' => [],
        'tags' => ['outer', 'special', 'elemental']
        ];
    $index['shield-area-1se'] = [
        'name' => 'SHIELD Area 1SE',
        'position' => '4-17',
        'level' => 200,
        'field' => '',
        'encounters' => [],
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