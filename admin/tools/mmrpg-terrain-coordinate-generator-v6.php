<?php

// ==========================================
// CONFIGURATION
// ==========================================
$startId      = 100;
$idIncrement  = 1;

// The X and Y coordinates of the very first autotile grid slot.
// Common sprites start are 40x40 for padding but additional terrain
// sprites have no padding and start at 0x0 always
$defaultStartX       = 0;
$defaultStartY       = 0;

// The size of a full 4x4 autotile block (80px * 4 tiles)
$blockSizeX   = 320;
$blockSizeY   = 320;

// ==========================================
// GRID DEFINITION
// ==========================================
// Map out your spritesheet here!
// - Use empty strings ('') for spaces where there is no tileset.
// - Add flags by using a colon (e.g., 'water:not-walkable').
$grid = [
    // GROUP 1 (Starts at Y = 360)
    [
        'type-none:not-walkable',      // Col 0
        'type-cutter:not-walkable',    // Col 1
        'type-impact:not-walkable',    // Col 2
        'type-freeze:not-walkable',    // Col 3
        'type-explode:not-walkable',   // Col 4
        'type-flame:not-walkable',     // Col 5
        'type-electric:not-walkable',  // Col 6
        'type-time:not-walkable',      // Col 7
        'type-earth:not-walkable',     // Col 8
        'type-wind:not-walkable',      // Col 9
    ],

    // GROUP 2 (Starts at Y = 680)
    [
        'type-water:not-walkable',     // Col 0
        'type-swift:not-walkable',     // Col 1
        'type-nature:not-walkable',    // Col 2
        'type-missile:not-walkable',   // Col 3
        'type-crystal:not-walkable',   // Col 4
        'type-shadow:not-walkable',    // Col 5
        'type-space:not-walkable',     // Col 6
        'type-shield:not-walkable',    // Col 7
        'type-laser:not-walkable',     // Col 8
        'type-copy:not-walkable',      // Col 9
    ],

    // GROUP 3 (Starts at Y = 1000)
    [
        'water:not-walkable',           // Col 0
        'magma:not-walkable',           // Col 1
        'plain',                        // Col 2
        'beach',                        // Col 3
        'grass',                        // Col 4
        'ground',                       // Col 5
        'tundra',                       // Col 6
        'clouds:not-walkable',          // Col 7
    ],

    // GROUP 4 (Starts at Y = 1320)
    [
        'darkness',
        'prototype-subspace',
        'plain-field',
        'bonus-field',
        'gentle-countryside',
        'maniacal-hideaway',
        'wintry-forefront',
        'light-laboratory',
        'wily-castle',
        'cossack-citadel',
    ],

    // GROUP 5
    [
        'lightness',
        'prototype-subspace-ii',
        'final-destination',
        'final-destination-ii',
        'final-destination-iii',
        'robot-museum',
        'hunter-compound',
        'royal-palace',
        'genesis-tower',
        'stardroid-base',
    ],

    // GROUP 6
    [
        'abandoned-warehouse',
        'mountain-mines',
        'arctic-jungle',
        'orb-city',
        'steel-mill',
        'electrical-tower',
        'clock-citadel',
        'oil-wells',
        'industrial-facility',
        'sky-ridge',
    ],

    // GROUP 7
    [
        'waterfall-institute',
        'underground-laboratory',
        'pipe-station',
        'photon-collider',
        'atomic-furnace',
        'preserved-forest',
        'construction-site',
        'magnetic-generator',
        'reflection-chamber',
        'rocky-plateau',
    ],

    // GROUP 8
    [
        'spinning-greenhouse',
        'serpent-column',
        'power-plant',
        'septic-system',
        'lighting-control',
        'rainy-sewers',
        'mineral-quarry',
        'egyptian-excavation',
        'space-simulator',
        'rusty-scrapheap',
    ],

    // GROUP 9
    [
        'submerged-armory',
        'robosaur-boneyard',
        'satellite-deck',
        'trenchwork-depot',
        'haunted-mansion',
        'sunset-gulch',
        'waterwork-damn',
        'minefield-dunes',
        'glacier-cradle',
        'sonic-highway',
    ],

];

// ==========================================
// GENERATOR LOGIC (Do not edit below)
// ==========================================
$subOffsets = [
    [0, 0], [0, 240], [80, 0], [80, 240],
    [0, 80], [0, 160], [80, 80], [80, 160],
    [240, 0], [240, 240], [160, 0], [160, 240],
    [240, 80], [240, 160], [160, 80], [160, 160]
];

$currentId = $startId;
$letters = range('a', 'p');

// DEFAULT TILES (Do not edit below)
ob_start();
?>
#---------------------------#
@tiles[]  = void(00, 40, 40, not-walkable)
@tiles[]  = dotted(01, 360, 40, not-walkable)
#---------------------------#
@sprites[]  = grid(120, 40)
@sprites[]  = hover(120, 40)
@sprites[]  = outline(360, 40)
@sprites[]  = focus(760, 40)
@sprites[]  = active(280, 40)
@sprites[]  = active-up(40, 120)
@sprites[]  = active-up-right(120, 120)
@sprites[]  = active-right(200, 120)
@sprites[]  = active-down-right(280, 120)
@sprites[]  = active-down(360, 120)
@sprites[]  = active-down-left(440, 120)
@sprites[]  = active-left(520, 120)
@sprites[]  = active-up-left(600, 120)
#---------------------------#
<?
$defaultMarkup = trim(ob_get_clean());

// Wrap output in <pre> tags so it's readable if viewed in a web browser
echo (php_sapi_name() !== 'cli') ? "<pre>\n" : "";
echo "#---------------------------# \n";
echo "# MMRPG OVERWORLD 2k25 SHEET \n";
echo "#---------------------------# \n";
echo "@token    = mmrpg-overworld-2k25-v6 \n";
echo "@name     = MMRPG Overworld (2k25) (v6) \n";
echo "@image    = mmrpg-overworld-2k25_terrain-tiles-v6.png \n";
echo "@files    = mmrpg-overworld-2k25_terrain-tiles-v6/ \n";
echo "@size     = 80 x 80 \n";
echo "#---------------------------#\n";
echo $defaultMarkup." \n";
echo "#---------------------------#\n";

foreach ($grid as $rowIndex => $row) {
    foreach ($row as $colIndex => $cellData) {

        // Skip empty columns
        if (empty(trim($cellData))) {
            continue;
        }

        // Parse names and flags (e.g. "water:not-walkable")
        $parts = explode(':', $cellData);
        $name = array_shift($parts);

        // If there are flags left over, format them with leading commas
        $flags = count($parts) > 0 ? ", " . implode(", ", $parts) : "";

        // Calculate absolute top-left X/Y for this block
        $baseX = $defaultStartX; //$defaultStartX + ($colIndex * $blockSizeX);
        $baseY = $defaultStartY; //$defaultStartY + ($rowIndex * $blockSizeY);

        // 1. Output the main container tile
        echo "@tiles[]    = {$name}({$currentId}, {$baseX}, {$baseY}, {$name}.png{$flags})\n";

        // 2. Output the 16 sub-tiles
        foreach ($subOffsets as $i => $offset) {
            $subChar = $letters[$i];
            $subX = $baseX + $offset[0];
            $subY = $baseY + $offset[1];

            echo "@tiles[]    = {$name}-{$i}({$currentId}{$subChar}, {$subX}, {$subY}, {$name}.png{$flags})\n";
        }

        echo "#---------------------------#\n";

        // Increment ID for the next valid tile
        $currentId += $idIncrement;
    }
}

echo "#---------------------------#\n";
echo (php_sapi_name() !== 'cli') ? "</pre>\n" : "";

?>