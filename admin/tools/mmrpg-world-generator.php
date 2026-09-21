<?

// Require the global top file
require('../../top.php');

// Print out the title regardless of if confirmed or not
echo('<strong>MMRPG World Area Generator Script</strong><br />'.PHP_EOL);

// If this is not local it should not run
if (!defined('MMRPG_CONFIG_IS_LIVE')
    || MMRPG_CONFIG_IS_LIVE == true){
    echo('<p>This script can only be used locally! Aborting.</p>'.PHP_EOL);
    exit();
}

// Pull some indexes we might need for later
$mmrpg_index_players = rpg_player::get_index(true);
$mmrpg_index_robots = rpg_robot::get_index(true);
$mmrpg_index_items = rpg_item::get_index(true);

/*
// Pull the list of areas from the database
$mmrpg_prototype_world_areas = $db->get_array_list("SELECT *
    FROM `mmrpg_index_areas`
    WHERE `area_world` = 'prototype'
    ORDER BY `area_id` ASC
    ;", 'area_token');
*/

// Pull the list of areas from the worldinfo file so it's always fresh
$this_world = 'prototype';
include(MMRPG_CONFIG_ROOTDIR.'prototype/worldinfo/prototype.php');
$mmrpg_prototype_world_areas = isset($this_world)
    && !empty($mmrpg_worlds[$this_world]['areas'])
    ? $mmrpg_worlds[$this_world]['areas']
    : false;

// Define the base directory for this world's files
$world_map_filepath = 'prototype/worldmaps/prototype/';
$world_map_filedir = MMRPG_CONFIG_ROOTDIR.$world_map_filepath;

// Define a quick function for grabbing random map coordinates
function createCoordinatePicker($width, $height, $padding, $exclude = []) {
    $valid_positions = [];
    // Calculate the walkable boundaries based on padding
    $min_x = $padding + 1;
    $max_x = $width - $padding;
    $min_y = $padding + 1;
    $max_y = $height - $padding;
    // Build an array of every single walkable coordinate
    for ($x = $min_x; $x <= $max_x; $x++) {
        for ($y = $min_y; $y <= $max_y; $y++) {
            $pos = $x . '-' . $y;
            if (!in_array($pos, $exclude)) {
                $valid_positions[] = $pos;
            }
        }
    }
    // Shuffle the array to randomize the order
    shuffle($valid_positions);
    // Return an inline function that pops a coordinate off the list
    return function() use (&$valid_positions){
        return array_pop($valid_positions); // Returns null if the map is completely full!
        };
}
// Define quick functions for generating coordinate buffers around interactive objects
function getBufferBox($pos, $radius, $max_w, $max_h){
    list($x, $y) = explode('-', $pos);
    $min_x = max(1, $x - $radius);
    $min_y = max(1, $y - $radius);
    $max_x = min($max_w, $x + $radius);
    $max_y = min($max_h, $y + $radius);
    return [
        'range' => $min_x.'-'.$min_y.'...'.$max_x.'-'.$max_y,
        'min_x' => $min_x, 'min_y' => $min_y, 'max_x' => $max_x, 'max_y' => $max_y
    ];
}
function getBufferCoords($box){
    $coords = [];
    for ($x = $box['min_x']; $x <= $box['max_x']; $x++){
        for ($y = $box['min_y']; $y <= $box['max_y']; $y++){
            $coords[] = $x.'-'.$y;
        }
    }
    return $coords;
}

echo('<pre>'.PHP_EOL);
{
    //echo('$world_map_filepath = '.print_r($world_map_filepath, true).PHP_EOL);
    //echo('$world_map_filedir = '.print_r($world_map_filedir, true).PHP_EOL);
    //echo('$mmrpg_prototype_world_areas ='.print_r($mmrpg_prototype_world_areas, true).PHP_EOL);
    //echo('$mmrpg_prototype_world_areas:first-child = '.print_r($mmrpg_prototype_world_areas[array_keys($mmrpg_prototype_world_areas)[0]], true).PHP_EOL);

    // Loop through the list of areas and create their map files (overwrite existing)
    if (!empty($mmrpg_prototype_world_areas)){

        // Pre-loop through the world area and map the positions for every single area
        $area_positions_index = array();
        $quanta_positions_index = array();
        $map_area_positions_markup = array();
        $map_area_positions_markup[] = '#---------------------------#';
        foreach ($mmrpg_prototype_world_areas AS $area_token => $area_info){
            if (empty($area_info['position'])){ continue; }
            $position = $area_info['position'];
            $objects = $area_info['objects'];
            list($prefix) = explode('-', $area_token);
            $area_positions_index[$position] = $area_token;
            if (in_array('quanta-flower', $objects)){ $quanta_positions_index[$position] = explode('-', $area_token)[0]; }
            if (!isset($last_prefix) || $last_prefix !== $prefix){
                $map_area_positions_markup[] = '#---------------------------#';
                $last_prefix = $prefix;
                }
            $map_area_positions_markup[] = '@areas[]  = '.$area_token.'('.$position.')';
        }
        $map_area_positions_markup[] = '#---------------------------#';
        $map_area_positions_markup[] = '#---------------------------#';
        $map_area_positions_markup = implode(PHP_EOL, $map_area_positions_markup).PHP_EOL;
        //echo('$area_positions_index = '.print_r($area_positions_index, true).PHP_EOL);
        echo('Generating map area positions... '.PHP_EOL.print_r($map_area_positions_markup, true).'...found '.count($mmrpg_prototype_world_areas).' areas.'.PHP_EOL.PHP_EOL);
        echo('Generating quanta flower positions... '.PHP_EOL.print_r($quanta_positions_index, true).'...found '.count($quanta_positions_index).' quanta-flowers.'.PHP_EOL.PHP_EOL);

        // Define which types go with which portal colours so we don't have to later
        $portal_colour_index = [];
        $portal_colour_index['red'] = ['flame', 'laser', 'crystal'];
        $portal_colour_index['blue'] = ['water', 'freeze', 'shield'];
        $portal_colour_index['yellow'] = ['electric', 'missile', 'earth'];
        $portal_colour_index['green'] = ['nature', 'wind'];
        $portal_colour_index['purple'] = ['time', 'space', 'shadow'];
        $portal_colour_index['orange'] = ['explode', 'swift', 'impact'];
        $portal_colour_index['black'] = ['cutter'];

        // TEMP TEMP TEMP
        // Define a list of fields that don't have textures made yet (and/or map isn't done)
        $map_tiles_missing_for = [
            'crystal-catacombs', 'gemstone-cavern'
            ];
        // TEMP TEMP TEMP

        // Loop through the world area again and this time actually generate the file markup
        foreach ($mmrpg_prototype_world_areas AS $area_token => $area_info){
            echo('Generating '.$area_info['name'].' ('.$area_token.') ... '.PHP_EOL);
            $area_file_name = $area_token.'.map';
            $area_file_dir = $world_map_filedir.$area_file_name;

            $area_sheet = 'mmrpg-overworld-2k25-v3';
            $area_name = !empty($area_info['name']) ? $area_info['name'] : '[Undefined]';
            $area_subname = !empty($area_info['subname']) ? $area_info['subname'] : '';
            $area_type = !empty($area_info['element']) ? $area_info['element'] : 'none';
            $area_type_name = ucfirst($area_type === 'none' ? 'neutral' : $area_type);
            $area_type_text = strtoupper($area_type_name);
            $area_level = !empty($area_info['level']) ? $area_info['level'] : 1;
            $area_objects = !empty($area_info['objects']) ? $area_info['objects'] : array();
            $area_tags = !empty($area_info['tags']) ? $area_info['tags'] : array();
            $area_position = !empty($area_info['position']) ? $area_info['position'] : '1-1';
            $area_position_xy = explode('-', $area_position);
            $area_position_col = intval($area_position_xy[0]);
            $area_position_row = intval($area_position_xy[1]);
            $area_size = !empty($area_info['size']) ? $area_info['size'] : '21 x 21';
            if (in_array('stargate', $area_tags)){ $area_size = '11 x 11';  }
            elseif (in_array('path', $area_tags)){ $area_size = '13 x 13';  }
            elseif (in_array('start', $area_tags)){ $area_size = '29 x 29';  }
            elseif (!in_array('elemental', $area_tags)){ $area_size = '15 x 15';  }
            elseif (in_array('main', $area_tags)){ $area_size = '31 x 31'; }
            $area_size_xy = explode(' x ', $area_size);
            $area_size_width = intval($area_size_xy[0]);
            $area_size_height = intval($area_size_xy[1]);
            $area_middle_col = ceil($area_size_width / 2);
            $area_middle_row = ceil($area_size_height / 2);
            $area_spawn = $area_middle_col.'-'.$area_middle_row;
            $area_spawn_visible = in_array('start', $area_tags) || in_array('player-platform', $area_objects) ? true : false;
            $area_padding = !empty($area_info['padding']) ? intval($area_info['padding']) : 3;
            if (in_array('elemental', $area_tags)){ $area_padding += 1;  }
            $area_field = !empty($area_info['field']) ? $area_info['field'] : 'field';
            $area_field_tile = $area_field === 'field' ? 'plain-field' : $area_field;
            $area_field_tile = str_replace('-1', '-i', $area_field_tile);
            $area_field_tile = str_replace('-2', '-ii', $area_field_tile);
            $area_field_tile = str_replace('-3', '-iii', $area_field_tile);
            if (in_array($area_field_tile, $map_tiles_missing_for)){ $area_field_tile = 'plain-field'; }

            $area_exits = !empty($area_info['exits']) ? $area_info['exits'] : array();
            $area_random_pickups = !empty($area_info['pickups']) ? $area_info['pickups'] : array();
            $area_static_pickups = !empty($area_info['items']) ? $area_info['items'] : array();
            $area_static_encounters = !empty($area_info['encounters']) ? $area_info['encounters'] : array();
            $area_random_encounters = !empty($area_info['encounters2']) ? $area_info['encounters2'] : array();
            $area_static_rescues = !empty($area_info['rescues']) ? $area_info['rescues'] : array();

            // Collect or define the inset (area border + padding + extra tile[s])
            $encounter_inset = isset($area_info['encounter_inset']) ? intval($area_info['encounter_inset']) : $area_padding + 1;
            // Safety Check: Ensure the map isn't too small for this inset (prevents inverted zones)
            $min_dimension = min($area_size_width, $area_size_height);
            if ($min_dimension - ($encounter_inset * 2) < 1){ $encounter_inset = floor($min_dimension / 2) - 1; }
            if ($encounter_inset < 0){ $encounter_inset = 0; }

            // Unlink the existing file if it already exists so we can start fresh
            if (file_exists($area_file_dir)){ unlink($area_file_dir); }

            // Initialize an array to track positions that need a protective buffer
            $protected_zones = [];

            // Initialize as an array
            $area_file_markup = [];

            // Push each line as a new array element
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '# '.$area_name.' ('.$area_type_text.')';
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '@token      = '.$area_token;
            $area_file_markup[] = '@name       = '.$area_name;
            if ($area_subname){ $area_file_markup[] = '@subname    = '.$area_subname; }
            $area_file_markup[] = '@type       = '.$area_type;
            $area_file_markup[] = '@level      = '.$area_level;
            $area_file_markup[] = '@size       = '.$area_size;
            $area_file_markup[] = '@sheet      = '.$area_sheet;
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '@field      = '.$area_field;
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '@aliases[]  = 10:plain';
            $area_file_markup[] = '@aliases[]  = 20:type-'.$area_type;
            $area_file_markup[] = '@aliases[]  = 30:'.$area_field_tile;
            $area_file_markup[] = '@aliases[]  = 90:water';
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '@portals[]  = spawn('.$area_spawn.(!$area_spawn_visible ? ', hidden' : '').')';
            $protected_zones['spawn-buffer'] = $area_spawn;
            // Generate the EXITS for this area of the map with their destinations
            if (!empty($area_exits)){
                $area_file_markup[] = '#---------------------------#';

                // If there's a north exit, make sure we define it here
                if (in_array('north', $area_exits)){
                    $exit_name = 'north-exit';
                    $exit_direction = 'up-only';
                    $dest_area_portal = 'south-exit';
                    $exit_position = $area_middle_col.'-1';
                    $exit_position_xy = explode('-', $exit_position);
                    $dest_position_xy = array($area_position_col, $area_position_row - 1);
                    $dest_position = implode('-', $dest_position_xy);
                    $dest_area_token = !empty($area_positions_index[$dest_position]) ? $area_positions_index[$dest_position] : '';
                    if (!empty($dest_area_token)){
                        $exit_destination = $dest_area_token.'__'.$dest_area_portal;
                        $area_file_markup[] = '@portals[]  = '.$exit_name.'('.$exit_position.', '.$exit_destination.', '.$exit_direction.')';
                        $protected_zones[$exit_name.'-buffer'] = $exit_position;
                    }
                }

                // If there's a south exit, make sure we define it here
                if (in_array('south', $area_exits)){
                    $exit_name = 'south-exit';
                    $exit_direction = 'down-only';
                    $dest_area_portal = 'north-exit';
                    $exit_position = $area_middle_col.'-'.$area_size_height;
                    $exit_position_xy = explode('-', $exit_position);
                    $dest_position_xy = array($area_position_col, $area_position_row + 1);
                    $dest_position = implode('-', $dest_position_xy);
                    $dest_area_token = !empty($area_positions_index[$dest_position]) ? $area_positions_index[$dest_position] : '';
                    if (!empty($dest_area_token)){
                        $exit_destination = $dest_area_token.'__'.$dest_area_portal;
                        $area_file_markup[] = '@portals[]  = '.$exit_name.'('.$exit_position.', '.$exit_destination.', '.$exit_direction.')';
                        $protected_zones[$exit_name.'-buffer'] = $exit_position;
                    }
                }

                // If there's a west exit, make sure we define it here
                if (in_array('west', $area_exits)){
                    $exit_name = 'west-exit';
                    $exit_direction = 'left-only';
                    $dest_area_portal = 'east-exit';
                    $exit_position = '1-'.$area_middle_row;
                    $dest_position_xy = array($area_position_col - 1, $area_position_row);
                    $dest_position = implode('-', $dest_position_xy);
                    $dest_area_token = !empty($area_positions_index[$dest_position]) ? $area_positions_index[$dest_position] : '';
                    if (!empty($dest_area_token)){
                        $exit_destination = $dest_area_token.'__'.$dest_area_portal;
                        $area_file_markup[] = '@portals[]  = '.$exit_name.'('.$exit_position.', '.$exit_destination.', '.$exit_direction.')';
                        $protected_zones[$exit_name.'-buffer'] = $exit_position;
                    }
                }

                // If there's an east exit, make sure we define it here
                if (in_array('east', $area_exits)){
                    $exit_name = 'east-exit';
                    $exit_direction = 'right-only';
                    $dest_area_portal = 'west-exit';
                    $exit_position = $area_size_width.'-'.$area_middle_row;
                    $dest_position_xy = array($area_position_col + 1, $area_position_row);
                    $dest_position = implode('-', $dest_position_xy);
                    $dest_area_token = !empty($area_positions_index[$dest_position]) ? $area_positions_index[$dest_position] : '';
                    if (!empty($dest_area_token)){
                        $exit_destination = $dest_area_token.'__'.$dest_area_portal;
                        $area_file_markup[] = '@portals[]  = '.$exit_name.'('.$exit_position.', '.$exit_destination.', '.$exit_direction.')';
                        $protected_zones[$exit_name.'-buffer'] = $exit_position;
                    }
                }

            }
            // If this area has a QUANTA FLOWER, we should add it to the middle of the area
            if (in_array('quanta-flower', $area_objects)){
                $area_file_markup[] = '#---------------------------#';

                $portal_colour = 'black';
                foreach ($portal_colour_index AS $colour => $types){ if (in_array($area_type, $types)){ $portal_colour = $colour; break; } }
                $portal_position = $area_middle_col.'-'.$area_middle_row;
                $protected_zones['quanta-flower-buffer'] = $portal_position;
                $area_file_markup[] = '@portals[]  = subspace-portal('.$portal_position.', prototype-subspace__'.$area_type.'-portal, '.$portal_colour.'-alt)';
                $area_file_markup[] = '@locks[]    = '.$area_type.'-portal-flower('.$portal_position.', portal-flower, '.$area_type.', items:'.$area_type.'-core, x10, locked)';

            }
            // If this area has any STAR GATES, we should add it to them to the area at exits
            if (in_array('star-gate', $area_objects)){
                $area_file_markup[] = '#---------------------------#';

                if (!empty($area_exits)){
                    foreach ($area_exits AS $exit_key => $exit_token){
                        $exit_num = $exit_key + 1;
                        $exit_num_padded = str_pad($exit_num, 2, '0', STR_PAD_LEFT);
                        if ($exit_token === 'north' || $exit_token === 'south'){

                            $gate_block_dir = $exit_token === 'north' ? 'horz-up' : 'horz-down';
                            $gate_door_dir = 'horizontal';
                            $gate_position_x = $area_middle_col;
                            $gate_position_y = $exit_token === 'north' ? ($area_padding + 1) : $area_size_height - ($area_padding);
                            $gate_position_b1 = ($gate_position_x - 1).'-'.$gate_position_y;
                            $gate_position_g1 = ($gate_position_x).'-'.$gate_position_y;
                            $gate_position_b2 = ($gate_position_x + 1).'-'.$gate_position_y;
                            $protected_zones['star-gate-'.$exit_num_padded.'-b1-buffer'] = $gate_position_b1;
                            $protected_zones['star-gate-'.$exit_num_padded.'-g1-buffer'] = $gate_position_g1;
                            $protected_zones['star-gate-'.$exit_num_padded.'-b2-buffer'] = $gate_position_b2;
                            $area_file_markup[] = '@blocks[]    = star-gate-'.$exit_num_padded.'-b1('.$gate_position_b1.', star-block, '.$gate_block_dir.', x'.$area_level.')';
                            $area_file_markup[] = '@gates[]     = star-gate-'.$exit_num_padded.'-g1('.$gate_position_g1.', star-gate, '.$gate_door_dir.', x'.$area_level.')';
                            $area_file_markup[] = '@blocks[]    = star-gate-'.$exit_num_padded.'-b2('.$gate_position_b2.', star-block, '.$gate_block_dir.', x'.$area_level.')';

                        }
                        elseif ($exit_token === 'west' || $exit_token === 'east'){

                            $gate_block_dir = $exit_token === 'west' ? 'vert-left' : 'vert-right';
                            $gate_door_dir = 'vertical';
                            $gate_position_x = $exit_token === 'west' ? ($area_padding + 1) : $area_size_width - ($area_padding);
                            $gate_position_y = $area_middle_row;
                            $gate_position_b1 = $gate_position_x.'-'.($gate_position_y - 1);
                            $gate_position_g1 = $gate_position_x.'-'.($gate_position_y);
                            $gate_position_b2 = $gate_position_x.'-'.($gate_position_y + 1);
                            $protected_zones['star-gate-'.$exit_num_padded.'-b1-buffer'] = $gate_position_b1;
                            $protected_zones['star-gate-'.$exit_num_padded.'-g1-buffer'] = $gate_position_g1;
                            $protected_zones['star-gate-'.$exit_num_padded.'-b2-buffer'] = $gate_position_b2;
                            $area_file_markup[] = '@blocks[]    = star-gate-'.$exit_num_padded.'-b1('.$gate_position_b1.', star-block, '.$gate_block_dir.', x'.$area_level.')';
                            $area_file_markup[] = '@gates[]     = star-gate-'.$exit_num_padded.'-g1('.$gate_position_g1.', star-gate, '.$gate_door_dir.', x'.$area_level.')';
                            $area_file_markup[] = '@blocks[]    = star-gate-'.$exit_num_padded.'-b2('.$gate_position_b2.', star-block, '.$gate_block_dir.', x'.$area_level.')';
                        }

                    }
                }

            }
            // If this area is the START area, generate all the parallel quanta flowers here!
            if (in_array('start', $area_tags) && !empty($quanta_positions_index)){
                $vertical_offset = 4;
                $area_file_markup[] = '#---------------------------#';
                foreach ($quanta_positions_index AS $world_pos => $flower_type){
                    // Translate world coordinates to local coordinates (accounting for padding)
                    list($world_x, $world_y) = explode('-', $world_pos);
                    $local_x = intval($world_x) + $area_padding;
                    $local_y = intval($world_y) + $area_padding + $vertical_offset;
                    $local_pos = $local_x.'-'.$local_y;
                    // Get the destination area token based on the world position
                    $dest_area_token = isset($area_positions_index[$world_pos]) ? $area_positions_index[$world_pos] : '';
                    if (!empty($dest_area_token)){
                        // Determine the correct colour for this element
                        $portal_colour = 'black';
                        foreach ($portal_colour_index AS $colour => $types){ if (in_array($flower_type, $types)){ $portal_colour = $colour; break; } }
                        // Register this position so other objects don't spawn on it
                        $protected_zones[$flower_type.'-flower-buffer'] = $local_pos;
                        // Add the portal and lock to the markup
                        $portal_name = $flower_type.'-portal';
                        $exit_destination = $dest_area_token.'__subspace-portal';
                        $area_file_markup[] = '@portals[]  = '.$portal_name.'('.$local_pos.', '.$exit_destination.', '.$portal_colour.'-alt)';
                        $area_file_markup[] = '@locks[]    = '.$flower_type.'-portal-flower('.$local_pos.', portal-flower, '.$flower_type.', items:'.$flower_type.'-core, x10, locked)';
                    }
                }
            }
            // If this area has a PLAYER PLATFORM, we should add its parts and accompanying panels
            if (in_array('player-platform', $area_objects)
                && count($area_static_pickups) >= 3
                && strstr($area_static_pickups[0], '-program')
                && strstr($area_static_pickups[1], '-heart')
                && strstr($area_static_pickups[2], '-core')){
                $area_file_markup[] = '#---------------------------#';
                $ptoken = explode('-', $area_token)[0];
                $itoken1 = trim($area_static_pickups[0], '! ');
                $itoken2 = trim($area_static_pickups[1], '! ');
                $itoken3 = trim($area_static_pickups[2], '! ');
                $positionx = $area_middle_col;
                $positiony = $area_middle_row - 2;
                $position1 = ($positionx - 1).'-'.($positiony);
                $position2 = ($positionx + 0).'-'.($positiony);
                $position3 = ($positionx + 1).'-'.($positiony);
                $healpad_pos = ($positionx - 3).'-'.($positiony - 1);
                $resetpad_pos = ($positionx + 3).'-'.($positiony - 1);
                $portal_x = $area_middle_col;
                $portal_y = $positiony + 5;
                $portal_pos = $portal_x.'-'.$portal_y;
                $area_file_markup[] = '@events[]   = '.$ptoken.'-platform-left('.$position1.', '.$ptoken.'pad-left, any, drop-zone, item:'.$itoken1.', activate-player-platform)';
                $area_file_markup[] = '@events[]   = '.$ptoken.'-platform-middle('.$position2.', '.$ptoken.'pad-middle, any, drop-zone, item:'.$itoken2.', activate-player-platform)';
                $area_file_markup[] = '@events[]   = '.$ptoken.'-platform-right('.$position3.', '.$ptoken.'pad-right, any, drop-zone, item:'.$itoken3.', activate-player-platform)';
                $area_file_markup[] = '@events[]   = auto-heal('.$healpad_pos.', healpad, any, trigger-effects, restore-team-energy, restore-team-weapons)';
                $area_file_markup[] = '@events[]   = auto-reset('.$resetpad_pos.', resetpad, any, trigger-effects, reset-team-attack, reset-team-defense, reset-team-speed)';
                $area_file_markup[] = '@portals[]  = exit-portal('.$portal_pos.', prototype-area-0__spawn, void-alt, confirm)';
                $protected_zones['player-platform-left-buffer'] = $position1;
                $protected_zones['player-platform-middle-buffer'] = $position2;
                $protected_zones['player-platform-right-buffer'] = $position3;
                $protected_zones['player-healpad-buffer'] = $healpad_pos;
                $protected_zones['player-resetpad-buffer'] = $resetpad_pos;
                $protected_zones['player-return-portal-buffer'] = $portal_pos;
            }

            // Extract anchored pickups vs regular pickups
            $anchored_pickups = [];
            $regular_pickups = [];
            if (!empty($area_static_pickups)){
                foreach ($area_static_pickups AS $key => $pickup){
                    if (strstr($pickup, '!!')) { $anchored_pickups[$key] = $pickup; }
                    else { $regular_pickups[$key] = $pickup; }
                }
            }

            // Pre-place anchored pickups so they can generate their own protected zones
            $static_pickup_strings = [];
            if (!empty($anchored_pickups)){
                foreach ($anchored_pickups AS $key => $pickup){
                    $pickup_clean = trim($pickup, '! ');
                    if (strstr($pickup_clean, '__')){ list($item, $num) = explode('__', $pickup_clean); }
                    else { $item = $pickup_clean; $num = ''; }
                    if (!isset($mmrpg_index_items[$item])){ continue; }
                    // Rebuild temporary exclusions based on CURRENT protected zones
                    $tmp_exclude_coords = [];
                    foreach ($protected_zones as $zone_name => $pos){
                        $box = getBufferBox($pos, 1, $area_size_width, $area_size_height);
                        $tmp_exclude_coords = array_merge($tmp_exclude_coords, getBufferCoords($box));
                    }
                    $tmp_exclude_coords = array_unique($tmp_exclude_coords);
                    // Pick a safe coordinate just for this anchored item
                    $tmpPicker = createCoordinatePicker($area_size_width, $area_size_height, $encounter_inset, $tmp_exclude_coords);
                    $position = $tmpPicker();
                    if (!$position){ continue; } // Skip if no space left
                    // Register this item as a protected zone!
                    $item_num = $key + 1;
                    //$protected_zones['anchored-item-'.$item_num] = $position;
                    // Save the markup string for later
                    $static_pickup_key = 'static-pickup-'.$item_num;
                    if (in_array('player', $area_tags)
                        && preg_match('/\-(program|heart|core)(__[0-9]+)?$/i', $pickup_clean, $matches)){
                        $ptoken = explode('-', $area_token)[0]; $itoken = $matches[1];
                        $static_pickup_key = $ptoken.'-init-'.$itoken;
                        }
                    $static_pickup_strings[] = '@items[]    = '.$static_pickup_key.'('.$position.', '.$pickup_clean.', anchored)';
                }
            }

            // Pre-place the Anti-Eddie "Purple Reset Guy" if the area has random encounters
            $actor_strings = [];
            if (!empty($area_static_encounters)
                || !empty($area_random_encounters)
                || !empty($area_static_rescues)){
                // Calculate the bottom-right corner of the walkable area
                $npc_x = $area_size_width - $area_padding;
                $npc_y = $area_size_height - $area_padding;
                $npc_pos = $npc_x.'-'.$npc_y;
                $npc_ver = in_array('player-platform', $area_objects) ? 'first-encounter' : 'default';
                $actor_strings[] = '@actors[]   = purple-reset-dude('.$npc_pos.', robot, anti-eddie, -, '.$npc_ver.')';
                // Add him to the protected zones so encounters/items don't spawn on him
                $protected_zones['purple-reset-dude-buffer'] = $npc_pos;
            }

            // Process ALL protected zones (structural + newly added anchored items)
            $exclude_coords = [];
            $no_encounter_strings = [];
            foreach ($protected_zones as $zone_name => $pos){
                // Get the bounding box, constrained to the edges of the map
                $box = getBufferBox($pos, 1, $area_size_width, $area_size_height);
                // Add all tiles in this 3x3 box to the exclusion list for static drops
                $coords = getBufferCoords($box);
                $exclude_coords = array_merge($exclude_coords, $coords);
                // Build the no-encounters markup for the dynamic system
                $no_encounter_strings[] = '@groups[]   = no-encounters_'.$zone_name.'(' . $box['range'] . ')';
            }
            $exclude_coords = array_unique($exclude_coords);

            // Initialize the permanent inline coordinate picker for all remaining randomized entities
            $getRandomCoord = createCoordinatePicker($area_size_width, $area_size_height, $encounter_inset, $exclude_coords);

            // Generate RANDOM SUPER-BLOCKS for elemental areas (10% of remaining tiles)
            $block_strings = [];
            if (in_array('elemental', $area_tags)) {

                // Calculate the total number of walkable tiles inside the inset
                $zone_w = max(0, $area_size_width - ($encounter_inset * 2));
                $zone_h = max(0, $area_size_height - ($encounter_inset * 2));
                $total_zone_tiles = $zone_w * $zone_h;

                // Count how many of our excluded coordinates actually fall inside this random zone
                $excluded_in_zone = 0;
                foreach ($exclude_coords as $ex_pos) {
                    list($ex_x, $ex_y) = explode('-', $ex_pos);
                    if ($ex_x > $encounter_inset && $ex_x <= ($area_size_width - $encounter_inset) &&
                        $ex_y > $encounter_inset && $ex_y <= ($area_size_height - $encounter_inset)) {
                        $excluded_in_zone++;
                    }
                }

                // Calculate 10% of the strictly available tiles
                $remaining_walkable = max(0, $total_zone_tiles - $excluded_in_zone);
                $num_super_blocks = (int)floor($remaining_walkable * 0.10);

                // Pop coordinates and generate the blocks!
                for ($i = 1; $i <= $num_super_blocks; $i++) {
                    $position = $getRandomCoord();
                    if (!$position) break; // Failsafe if the map is stuffed

                    // Generate standard super-block markup without the specific image alt
                    $block_strings[] = '@blocks[]   = super-block-'.$i.'('.$position.', super-block)';
                }
            }

            // Generate the PICKUPS for this area along with their map positions where relevant
            if (!empty($area_random_pickups) || !empty($regular_pickups) || !empty($static_pickup_strings)){

                // Add the basic randomized pickup data if it exists
                $random_pickups_string = array();
                $num_random_pickups = count($area_random_pickups);
                foreach ($area_random_pickups AS $key => $pickup){ $random_pickups_string[] = $pickup.'('.($num_random_pickups - $key).')'; }
                $random_pickups_string = implode(',', $random_pickups_string);
                if (!empty($random_pickups_string)){
                    $area_file_markup[] = '#---------------------------#';
                    $area_file_markup[] = '@pickups    = '.$random_pickups_string;
                }

                // Loop through the regular static pickups and place them
                if (!empty($regular_pickups)){
                    foreach ($regular_pickups AS $key => $pickup){
                        $pickup_clean = trim($pickup, '! ');
                        if (strstr($pickup_clean, '__')){ list($item, $num) = explode('__', $pickup_clean); }
                        else { $item = $pickup_clean; $num = ''; }
                        if (!isset($mmrpg_index_items[$item])){ continue; }
                        $position = $getRandomCoord();
                        if (!$position){ continue; }
                        $item_num = $key + 1;
                        $static_pickup_strings[] = '@items[]    = static-pickup-'.$item_num.'('.$position.', '.$pickup_clean.')';
                    }
                }

                if (!empty($static_pickup_strings)){
                    $area_file_markup[] = '#---------------------------#';
                    $area_file_markup[] = implode(PHP_EOL, $static_pickup_strings);
                }

            }

            // Generate the ENCOUNTER-ZONE for random encouners and random pickups
            if (!empty($area_random_pickups) || !empty($area_random_encounters)){
                $area_file_markup[] = '#---------------------------#';
                // Calculate the bounding box for the encounter zone using the $encounter_inset defined above
                $zone_min_x = 1 + $encounter_inset;
                $zone_min_y = 1 + $encounter_inset;
                $zone_max_x = $area_size_width - $encounter_inset;
                $zone_max_y = $area_size_height - $encounter_inset;
                // Build the group syntax
                $encounter_zone_string = '@groups[]   = encounter-zone_main-area(' . $zone_min_x . '-' . $zone_min_y . '...' . $zone_max_x . '-' . $zone_max_y . ')';
                $area_file_markup[] = $encounter_zone_string;
                // Punch holes in the encounter zone for portals and protected objects
                if (!empty($no_encounter_strings)) {
                    $area_file_markup = array_merge($area_file_markup, $no_encounter_strings);
                }
            }
            // Generate the ENCOUNTERS for this area along with their map positions where relevant
            if (!empty($area_random_encounters) || !empty($area_static_encounters)){

                // Add the basic encounters string for randomized support mecha if it exists
                $random_encounters_string = array();
                $num_random_encounters = count($area_random_encounters);
                foreach ($area_random_encounters AS $key => $encounter){ $random_encounters_string[] = $encounter.'('.($num_random_encounters - $key).')'; }
                $random_encounters_string = !empty($random_encounters_string) ? implode(',', $random_encounters_string) : '';
                if (!empty($random_encounters_string)){
                    $area_file_markup[] = '#---------------------------#';
                    $area_file_markup[] = '@encounters = '.$random_encounters_string;
                }

                // Loop through the static encounters and add them here too
                $static_encounter_strings = array();
                $num_static_encounters = count($area_static_encounters);
                foreach ($area_static_encounters AS $key => $encounter){
                    if (strstr($encounter, '_')){ list($robot, $alt) = explode('_', $encounter); }
                    else { $robot = $encounter; $alt = ''; }
                    if (!isset($mmrpg_index_robots[$robot])){ continue; }
                    $position = $getRandomCoord();
                    $robot_num = $key + 1;
                    $robot_info = $mmrpg_index_robots[$robot];
                    $robot_class = !empty($robot_info['robot_class']) ? $robot_info['robot_class'] : '';
                    $robot_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
                    if ($robot_core === 'copy' && $robot_class === 'master'){ continue; }
                    if ($robot_class === 'master'){
                        $static_encounter_strings[] = '@bosses[]  = boss-robo-'.$robot_num.'('.$position.', '.$encounter.')';
                        if (!empty($robot_core)){ $static_encounter_strings[] = '@items[]    = boss-star-'.$robot_num.'('.$position.', '.$robot_core.'-star, '.$encounter.')'; }
                    } elseif ($robot_class === 'mecha'){
                        $static_encounter_strings[] = '@bosses[]   = boss-mech-'.$robot_num.'('.$position.', '.$encounter.')';
                    } elseif ($robot_class === 'boss'){
                        $static_encounter_strings[] = '@bosses[]   = boss-boss-'.$robot_num.'('.$position.', '.$encounter.')';
                    }
                }
                if (!empty($static_encounter_strings)){
                    $area_file_markup[] = '#---------------------------#';
                    $area_file_markup[] = implode(PHP_EOL, $static_encounter_strings);
                }

                // Loop through the static rescues and add them here too
                $static_rescue_strings = array();
                $num_static_rescues = count($area_static_rescues);
                foreach ($area_static_rescues AS $key => $rescue){
                    if (strstr($rescue, '_')){ list($robot, $alt) = explode('_', $rescue); }
                    else { $robot = $rescue; $alt = ''; }
                    if (!isset($mmrpg_index_robots[$robot])){ continue; }
                    $position = $getRandomCoord();
                    $robot_num = $key + 1;
                    $robot_info = $mmrpg_index_robots[$robot];
                    $robot_class = !empty($robot_info['robot_class']) ? $robot_info['robot_class'] : '';
                    $robot_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : '';
                    $static_rescue_strings[] = '@rescues[]  = rescue-robo-'.$robot_num.'('.$position.', '.$rescue.')';
                }
                if (!empty($static_rescue_strings)){
                    $area_file_markup[] = '#---------------------------#';
                    $area_file_markup[] = implode(PHP_EOL, $static_rescue_strings);
                }

            }

            // Generate the ACTORS for this area (like the Purple Reset Guy)
            if (!empty($actor_strings)){
                $area_file_markup[] = '#---------------------------#';
                $area_file_markup[] = implode(PHP_EOL, $actor_strings);
            }

            // Generate the OVERWORLD BLOCKS for this area
            if (!empty($block_strings)){
                $area_file_markup[] = '#---------------------------#';
                $area_file_markup[] = implode(PHP_EOL, $block_strings);
            }

            // Generate layer tiles for LAYER 0 and LAYER -1
            if (!empty($area_size_width) && !empty($area_size_height)){
                $area_file_markup[] = '#---------------------------#';

                // Cast float results from ceil() to integers for strict equality
                $area_middle_col = (int)ceil($area_size_width / 2);
                $area_middle_row = (int)ceil($area_size_height / 2);

                // Trim whitespace from the database array (e.g. ' south' -> 'south')
                $clean_area_exits = array_map('trim', $area_exits);

                // Define how many tiles of void space ([__]) exist between the border and the inner field
                $padding = $area_padding - 1;

                $layer_0_tiles = [];
                $layer_minus_1_tiles = [];

                for ($r = 1; $r <= $area_size_height; $r++) {
                    $row_0 = [];
                    $row_minus_1 = [];
                    for ($c = 1; $c <= $area_size_width; $c++) {

                        // Calculate distance from the current cell to the nearest edges
                        $distY = min($r - 1, $area_size_height - $r);
                        $distX = min($c - 1, $area_size_width - $c);

                        // Determine the base terrain type for this cell
                        $isEdge = ($distY === 0 || $distX === 0);
                        $isPadding = (!$isEdge && ($distY <= $padding || $distX <= $padding));

                        $tile_0 = '[__]';
                        $tile_minus_1 = '[__]';

                        if ($isEdge) {
                            $tile_minus_1 = '[20]'; // 1-tile outer border moved to layer -1
                        } elseif (!$isPadding) {
                            $tile_0 = '[30]'; // Inner walkable field stays on layer 0
                        }

                        // Check if we are aligned with the middle row or column
                        $isMiddleRow = ($r === $area_middle_row);
                        $isMiddleCol = ($c === $area_middle_col);

                        // Override tiles to create paths and gaps if they connect to a valid exit
                        $isPath = false;
                        if ($isMiddleCol) {
                            if ($r <= $padding + 1 && in_array('north', $clean_area_exits)) $isPath = true;
                            if ($r >= $area_size_height - $padding && in_array('south', $clean_area_exits)) $isPath = true;
                        }
                        if ($isMiddleRow) {
                            if ($c <= $padding + 1 && in_array('west', $clean_area_exits)) $isPath = true;
                            if ($c >= $area_size_width - $padding && in_array('east', $clean_area_exits)) $isPath = true;
                        }

                        if ($isPath) {
                            $tile_0 = '[30]'; // Draw the path on layer 0
                            $tile_minus_1 = '[__]'; // Create a gap in the layer -1 border
                        }

                        // Push the tiles to their respective rows
                        $row_0[] = $tile_0;
                        $row_minus_1[] = $tile_minus_1;
                    }

                    // Implode the rows and push them to the layer arrays
                    $layer_0_tiles[] = implode(',', $row_0);
                    $layer_minus_1_tiles[] = implode(',', $row_minus_1);
                }

                // Append Layer 0 to markup
                $area_file_markup[] = '@layer = 0';
                $area_file_markup[] = '#---------------------------#';
                $area_file_markup = array_merge($area_file_markup, $layer_0_tiles);
                $area_file_markup[] = '#---------------------------#';

                // Append Layer -1 to markup
                $area_file_markup[] = '@layer = -1';
                $area_file_markup[] = '#---------------------------#';
                $area_file_markup = array_merge($area_file_markup, $layer_minus_1_tiles);
                $area_file_markup[] = '#---------------------------#';
            }
            $area_file_markup[] = '#---------------------------#';
            // Append an extra WATER LAYER if this is a relevant field with water
            $has_water_layer = $area_type === 'water' || $area_type === 'freeze';
            if ($has_water_layer){
                $area_file_markup[] = '@layer = 1';
                $area_file_markup[] = '#---------------------------#';
                for ($i = 1; $i <= $area_size_height; $i++){
                    $area_file_markup[] = trim(str_repeat('[90],', $area_size_width), ',');
                }
                $area_file_markup[] = '#---------------------------#';
            }

            // Implode with PHP_EOL, and tack one on the very end for formatting
            $final_markup_string = implode(PHP_EOL, $area_file_markup) . PHP_EOL;
            echo('<div style="border: 1px dotted #dedede; padding: 10px;">'.$final_markup_string.'</div>'.PHP_EOL.PHP_EOL);

            // (Re)create the file and add the newly generated map markup to it
            $area_file = fopen($area_file_dir, 'w');
            fwrite($area_file, $final_markup_string);
            fclose($area_file);

        }
        echo('...Done! '.PHP_EOL);
    }

}
echo('</pre>'.PHP_EOL);

exit();

?>