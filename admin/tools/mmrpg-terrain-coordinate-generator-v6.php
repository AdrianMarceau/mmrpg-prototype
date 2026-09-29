<?php

// Require the global top file
define('MMRPG_EXTERNAL_TOP_INCLUDE', true);
define('MMRPG_EXCLUDE_SESSION', true);
require_once('../../top.php');

// Collect the list of master fields
$mmrpg_index_fields = rpg_field::get_index();
//echo('<pre>$mmrpg_index_fields = '.print_r($mmrpg_index_fields, true).'</pre>'.PHP_EOL);
//exit();

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
        'type-none:not-walkable',
        'type-cutter:not-walkable',
        'type-impact:not-walkable',
        'type-freeze:not-walkable',
        'type-explode:not-walkable',
        'type-flame:not-walkable',
        'type-electric:not-walkable',
        'type-time:not-walkable',
        'type-earth:not-walkable',
        'type-wind:not-walkable',
        'type-water:not-walkable',
        'type-swift:not-walkable',
        'type-nature:not-walkable',
        'type-missile:not-walkable',
        'type-crystal:not-walkable',
        'type-shadow:not-walkable',
        'type-space:not-walkable',
        'type-shield:not-walkable',
        'type-laser:not-walkable',
        'type-copy:not-walkable',
    ],

    // GROUP 3 (Starts at Y = 1000)
    [
        'water:not-walkable',
        'magma:not-walkable',
        'clouds:not-walkable',
        'plain',
        'beach',
        'grass',
        'ground',
        'tundra',
        'darkness',
        'lightness',
    ],

    // GROUP 4 (Starts at Y = 1320)
    [
        'plain-field',
        'bonus-field',
    ],

];

// Loop through fields and manually add them to the grid list
$grid_database_fields = array();
foreach ($mmrpg_index_fields AS $field_token => $field_info){
    if (empty($field_info['field_flag_published'])){ continue; }
    elseif (empty($field_info['field_flag_complete'])){ continue; }
    $terrain_token = $field_token;
    $terrain_token = str_replace('-3', '-iii', $terrain_token);
    $terrain_token = str_replace('-2', '-ii', $terrain_token);
    $terrain_token = str_replace('-1', '-i', $terrain_token);
    $grid_database_fields[] = $terrain_token;
    if ($terrain_token === 'prototype-subspace'){ $grid_database_fields[] = $terrain_token.'-ii'; }
}
$grid[] = $grid_database_fields;

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
        echo "@tiles[]    = {$name}({$currentId}, {$baseX}, {$baseY}, {$name}{$flags})\n";

        // 2. Output the 16 sub-tiles
        foreach ($subOffsets as $i => $offset) {
            $subChar = $letters[$i];
            $subX = $baseX + $offset[0];
            $subY = $baseY + $offset[1];

            echo "@tiles[]    = {$name}-{$i}({$currentId}{$subChar}, {$subX}, {$subY}, {$name}{$flags})\n";
        }

        echo "#---------------------------#\n";

        // Increment ID for the next valid tile
        $currentId += $idIncrement;
    }
}

echo "#---------------------------#\n";
echo (php_sapi_name() !== 'cli') ? "</pre>\n" : "";

?>