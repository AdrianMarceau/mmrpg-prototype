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

// Pull the list of areas from the database
$mmrpg_prototype_world_areas = $db->get_array_list("SELECT *
    FROM `mmrpg_index_areas`
    WHERE `area_world` = 'prototype'
    ORDER BY `area_id` ASC
    ;", 'area_token');

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

echo('<pre>'.PHP_EOL);
{
    //echo('$world_map_filepath = '.print_r($world_map_filepath, true).PHP_EOL);
    //echo('$world_map_filedir = '.print_r($world_map_filedir, true).PHP_EOL);
    //echo('$mmrpg_prototype_world_areas ='.print_r($mmrpg_prototype_world_areas, true).PHP_EOL);
    echo('$mmrpg_prototype_world_areas:first-child = '.print_r($mmrpg_prototype_world_areas[array_keys($mmrpg_prototype_world_areas)[0]], true).PHP_EOL);

    // Loop through the list of areas and create their map files (overwrite existing)
    if (!empty($mmrpg_prototype_world_areas)){

        // Pre-loop through the world area and map the positions for every single area
        $area_positions_index = array();
        $map_area_positions_markup = array();
        $map_area_positions_markup[] = '#---------------------------#';
        foreach ($mmrpg_prototype_world_areas AS $area_token => $area_info){
            if (empty($area_info['area_position'])){ continue; }
            $position = $area_info['area_position'];
            list($prefix) = explode('-', $area_token);
            $area_positions_index[$position] = $area_token;
            if (!isset($last_prefix) || $last_prefix !== $prefix){
                $map_area_positions_markup[] = '#---------------------------#';
                $last_prefix = $prefix;
                }
            $map_area_positions_markup[] = '@areas[]  = '.$area_token.'('.$position.')';
        }
        $map_area_positions_markup[] = '#---------------------------#';
        $map_area_positions_markup = implode(PHP_EOL, $map_area_positions_markup).PHP_EOL;
        //echo('$area_positions_index = '.print_r($area_positions_index, true).PHP_EOL);
        echo('$map_area_positions_markup = '.PHP_EOL.print_r($map_area_positions_markup, true).PHP_EOL);

        // Loop through the world area again and this time actually generate the file markup
        foreach ($mmrpg_prototype_world_areas AS $area_token => $area_info){
            echo('Generating '.$area_info['area_name'].' ('.$area_token.') ... '.PHP_EOL);
            $area_file_name = $area_token.'.map';
            $area_file_dir = $world_map_filedir.$area_file_name;

            $area_sheet = 'mmrpg-overworld-2k25-v3';
            $area_name = !empty($area_info['area_name']) ? $area_info['area_name'] : '[Undefined]';
            $area_type = !empty($area_info['area_element']) ? $area_info['area_element'] : 'none';
            $area_type_name = ucfirst($area_type === 'none' ? 'neutral' : $area_type);
            $area_type_text = strtoupper($area_type_name);
            $area_level = !empty($area_info['area_level']) ? $area_info['area_level'] : 1;
            $area_tags = !empty($area_info['area_tags']) ? explode(',', trim($area_info['area_tags'], ',')) : array();
            $area_position = !empty($area_info['area_position']) ? $area_info['area_position'] : '1-1';
            $area_position_xy = explode('-', $area_position);
            $area_position_col = intval($area_position_xy[0]);
            $area_position_row = intval($area_position_xy[1]);
            $area_size = !empty($area_info['area_size']) ? $area_info['area_size'] : '21 x 21';
            if (in_array('stargate', $area_tags)){ $area_size = '11 x 11';  }
            $area_size_xy = explode(' x ', $area_size);
            $area_size_width = intval($area_size_xy[0]);
            $area_size_height = intval($area_size_xy[1]);
            $area_middle_col = ceil($area_size_width / 2);
            $area_middle_row = ceil($area_size_height / 2);
            $area_spawn = $area_middle_col.'-'.$area_middle_row;
            $area_field = !empty($area_info['area_field']) ? $area_info['area_field'] : 'field';
            $area_field_tile = $area_field === 'field' ? 'plain-field' : $area_field;
            $area_exits = !empty($area_info['area_exits']) ? explode(',', trim($area_info['area_exits'], ',')) : array();
            $area_pickups = !empty($area_info['area_pickups']) ? explode(',', trim($area_info['area_pickups'], ',')) : array();
            $area_static_encounters = !empty($area_info['area_encounters']) ? explode(',', trim($area_info['area_encounters'], ',')) : array();
            $area_random_encounters = !empty($area_info['area_encounters2']) ? explode(',', trim($area_info['area_encounters2'], ',')) : array();

            // Unlink the existing file if it already exists so we can start fresh
            if (file_exists($area_file_dir)){ unlink($area_file_dir); }

            // Initialize the inline coordinate picker with spawn point excluded by default
            $exclude_coords = [$area_spawn];
            $getRandomCoord = createCoordinatePicker($area_size_width, $area_size_height, 6, $exclude_coords);

            // Initialize as an array
            $area_file_markup = [];

            // Push each line as a new array element
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '# '.$area_name.' ('.$area_type_text.')';
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '@token      = '.$area_token;
            $area_file_markup[] = '@name       = '.$area_name;
            $area_file_markup[] = '@type       = '.$area_type;
            $area_file_markup[] = '@level      = '.$area_level;
            $area_file_markup[] = '@size       = '.$area_size;
            $area_file_markup[] = '@sheet      = '.$area_sheet;
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '@field      = '.$area_field;
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '@aliases[]  = 10:plain';
            $area_file_markup[] = '@aliases[]  = 20:type-'.$area_type;
            $area_file_markup[] = '@aliases[]  = 30:'.'plain-field'; //$area_field_tile;
            $area_file_markup[] = '@aliases[]  = 90:water';
            $area_file_markup[] = '#---------------------------#';
            $area_file_markup[] = '@portals[]  = spawn('.$area_spawn.', hidden)';
            // Generate the EXITS for this area of the map with their destinations
            $area_file_markup[] = '#---------------------------#';
            {
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
                    }
                }

            }
            // Generate the PICKUPS for this area along with their map positions where relevant
            if (!empty($area_pickups) || !empty($area_items)){
                $area_file_markup[] = '#---------------------------#';

                // Add the basic randomized pickup data if it exists
                $random_pickups_string = array();
                $num_random_pickups = count($area_pickups);
                foreach ($area_pickups AS $key => $pickup){ $random_pickups_string[] = $pickup.'('.($num_random_pickups - $key).')'; }
                $random_pickups_string = implode(',', $random_pickups_string);
                $area_file_markup[] = '@pickups  = '.$random_pickups_string;

                // Loop through the static item pickups and add them here too
                // ...

            }
            // Generate the ENCOUNTERS for this area along with their map positions where relevant
            if (!empty($area_random_encounters) || !empty($area_static_encounters)){
                $area_file_markup[] = '#---------------------------#';

                // Add the basic encounters string for randomized support mecha if it exists
                $random_encounters_string = array();
                $num_random_encounters = count($area_random_encounters);
                foreach ($area_random_encounters AS $key => $encounter){ $random_encounters_string[] = $encounter.'('.($num_random_encounters - $key).')'; }
                $random_encounters_string = implode(',', $random_encounters_string);
                if (!empty($random_encounters_string)){ $area_file_markup[] = '@encounters  = '.$random_encounters_string; }

                // Loop through the static encounters and add them here too
                // ...

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
                $padding = 2;

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
            $has_water_layer = $area_type === 'water' || $area_type === 'freeze' || $area_type === 'nature';
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