<?php

// ==========================================
// CONFIGURATION
// ==========================================
$startId      = 100;
$idIncrement  = 1;

// The X and Y coordinates of the very first autotile grid slot.
// Starting Y at 360 leaves a 320px gap at the top for your custom sprites/UI!
$startX       = 40;
$startY       = 360;

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
    // ROW 1 (Starts at Y = 360)
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

    // ROW 2 (Starts at Y = 680)
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

    // ROW 3 (Starts at Y = 1000)
    [
        'water:not-walkable',           // Col 0
        'magma:not-walkable',           // Col 1
        'clouds:not-walkable',          // Col 2
        'beach',                        // Col 3
    ],

    // ROW 4 (Starts at Y = 1320)
    [
        'darkness',       // Col 0
        'subspace',       // Col 1
        'bonus',          // Col 2
        'plain',          // Col 3
        'grass',          // Col 4
        'ground',         // Col 5
        'tundra',         // Col 6
    ]
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

// Wrap output in <pre> tags so it's readable if viewed in a web browser
echo (php_sapi_name() !== 'cli') ? "<pre>\n" : "";
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
        $baseX = $startX + ($colIndex * $blockSizeX);
        $baseY = $startY + ($rowIndex * $blockSizeY);

        // 1. Output the main container tile
        echo "@tiles[]    = {$name}({$currentId}, {$baseX}, {$baseY}{$flags})\n";

        // 2. Output the 16 sub-tiles
        foreach ($subOffsets as $i => $offset) {
            $subChar = $letters[$i];
            $subX = $baseX + $offset[0];
            $subY = $baseY + $offset[1];

            echo "@tiles[]    = {$name}-{$i}({$currentId}{$subChar}, {$subX}, {$subY}{$flags})\n";
        }

        echo "#---------------------------#\n";

        // Increment ID for the next valid tile
        $currentId += $idIncrement;
    }
}

echo (php_sapi_name() !== 'cli') ? "</pre>\n" : "";

?>