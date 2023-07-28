<?

// Require the global config file
require('../top.php');

// Pre-collect request arguments provided in a different syntax
$request_args = !empty($_GET['args']) ? $_GET['args'] : array();
//error_log('$request_args (before) = '.print_r($request_args, true));
if (!empty($request_args)){
    $new_args = array();
    $raw_args = explode(' ', $request_args);
    //error_log('$raw_args = '.print_r($raw_args, true));
    if (!empty($raw_args)){ foreach ($raw_args as $raw_arg){
        $raw_arg = trim($raw_arg);
        $raw_arg_parts = explode(':', $raw_arg);
        if (!empty($raw_arg_parts[0]) && !empty($raw_arg_parts[1])){
            $token = $raw_arg_parts[0];
            $value = $raw_arg_parts[1];
            $new_args[$token] = $value;
        }
    }};
    $request_args = $new_args;
    foreach ($request_args as $arg_token => $arg_value){
        if (isset($_GET[$arg_token])){ unset($_GET[$arg_token]); }
        $_GET[$arg_token] = $arg_value;
    }
}
//error_log('$request_args (before) = '.print_r($request_args, true));

// Collect required parameters from the request header (kind, type, file)
$allowed_kinds = array('composite');
$allowed_types = array('players', 'robots', 'abilities', 'items', 'skills', 'fields');
$allowed_file_regex = '/^([-_a-z0-9]+)\.([a-z0-9]{3,})$/i';
$request_kind = isset($_GET['kind']) && in_array($_GET['kind'], $allowed_kinds) ? $_GET['kind'] : false;
$request_type = isset($_GET['type']) && in_array($_GET['type'], $allowed_types) ? $_GET['type'] : false;
$request_token = isset($_GET['token']) && preg_match('/^[-_a-z0-9,]+$/i', $_GET['token']) ? $_GET['token'] : false;
$request_editor = isset($_GET['editor']) && preg_match('/^[-_a-z0-9,]+$/', $_GET['editor']) ? $_GET['editor'] : false;
$request_file = isset($_GET['file']) && preg_match($allowed_file_regex, $_GET['file']) ? $_GET['file'] : false;
$request_size = !empty($_GET['size']) && is_numeric($_GET['size']) ? (int)($_GET['size']) : false;
$request_alt = !empty($_GET['alt']) && preg_match('/^[-_a-z0-9]+$/', $_GET['alt']) ? $_GET['alt'] : 0;
$request_crop = !empty($_GET['crop']) && $_GET['crop'] === 'false' ? false : true;
$request_frame = !empty($_GET['frame']) && is_numeric($_GET['frame']) ? (int)($_GET['frame']) : 0;
$force_refresh = !empty($_GET['refresh']) && $_GET['refresh'] === 'true' ? true : false;
if (is_numeric($request_alt)){ $request_alt = (int)($request_alt); }
if (is_numeric($request_editor)){ $request_editor = (int)($request_editor); }
if (!$request_crop){ $request_frame = 'all'; }
//error_log('$request_kind = '.print_r($request_kind, true));
//error_log('$request_type = '.print_r($request_type, true));
//error_log('$request_token = '.print_r($request_token, true));
//error_log('$request_editor = '.print_r($request_editor, true));
//error_log('$request_file = '.print_r($request_file, true));
//error_log('$request_size = '.print_r($request_size, true));
//error_log('$request_alt = '.print_r($request_alt, true));
//error_log('$request_crop = '.print_r($request_crop, true));
//error_log('$request_frame = '.print_r($request_frame, true));
//error_log('$force_refresh = '.print_r($force_refresh, true));

// If required fields were empty or not provided, immediately error out with a 404 header
if (empty($request_kind) || empty($request_type) || empty($request_file)){
    http_response_code(404);
    exit();
}

// We have everything we need, so let's process the request now
//error_log('Success! We can continue...');

// Explode the file into name and extension
list($request_file_name, $request_file_ext) = explode('.', $request_file);
//error_log('$request_file_name = '.print_r($request_file_name, true));
//error_log('$request_file_ext = '.print_r($request_file_ext, true));

// Check to see if this is an image request or an index request
if ($request_file_ext === 'json'){ $request_format = 'index'; }
else { $request_format = 'image'; }
//error_log('$request_format = '.print_r($request_format, true));

// Given what we know above, construct the filename for the cached file
$composite_base_path = MMRPG_CONFIG_CACHE_PATH.'sprites/';
if (!file_exists($composite_base_path)){ mkdir($composite_base_path, 0777, true); }
$composite_base_token = str_replace('_', '-', $request_file_name);
if (!empty($request_size)){ $composite_base_token .= '_s-'.$request_size; }
if (!empty($request_alt)){ $composite_base_token .= '_a-'.$request_alt; }
if (!empty($request_frame)){ $composite_base_token .= '_f-'.$request_frame; }
if (!empty($request_editor)){ $composite_base_token .= '_e-'.$request_editor; }
if (!empty($request_token)){ $composite_base_token .= '_t-'.preg_replace('/[^-a-z0-9]+/i', '-', $request_token); }
$composite_image_binary_path = $request_type.'_'.$composite_base_token.'.png';
$composite_image_index_path = $request_type.'_'.$composite_base_token.'.json';
//error_log('$composite_base_path = '.print_r($composite_base_path, true));
//error_log('$composite_image_binary_path = '.print_r($composite_image_binary_path, true));
//error_log('$composite_image_index_path = '.print_r($composite_image_index_path, true));

// Define the sprite object path given the request type
$sprite_object_dir = MMRPG_CONFIG_CONTENT_PATH.$request_type.'/';

// Collect the global cache time and break it down to an exact time
list($new_cache_date, $new_cache_time) = explode('-', MMRPG_CONFIG_CACHE_DATE);
$yyyy = substr($new_cache_date, 0, 4); $mm = substr($new_cache_date, 4, 2); $dd = substr($new_cache_date, 6, 2);
$hh = substr($new_cache_time, 0, 2); $ii = substr($new_cache_time, 2, 2);
$mmrpg_config_cache_time = mktime($hh, $ii, 0, $mm, $dd, $yyyy);
//error_log('$mmrpg_config_cache_time = '.print_r($mmrpg_config_cache_time, true));

// If the files already exists but they're too old, delete them
$delete_existing = false;
$full_binary_path = $composite_base_path.$composite_image_binary_path;
$full_index_path = $composite_base_path.$composite_image_index_path;
//error_log('$full_binary_path = '.print_r($full_binary_path, true));
//error_log('$full_index_path = '.print_r($full_index_path, true));
if (file_exists($full_binary_path)){
    $composite_image_ftime = filemtime($full_binary_path);
    //error_log('existing $composite_image_ftime = '.print_r($composite_image_ftime, true));
    if ($force_refresh || $composite_image_ftime < $mmrpg_config_cache_time){
        //error_log('deleting old $composite_image_ftime so we can generate anew');
        $delete_existing = true;
    }
}
if (file_exists($full_index_path)){
    $composite_image_index_ftime = filemtime($full_index_path);
    //error_log('existing $composite_image_index_ftime = '.print_r($composite_image_index_ftime, true));
    if ($force_refresh || $composite_image_index_ftime < $mmrpg_config_cache_time){
        //error_log('deleting old $composite_image_index_ftime so we can generate anew');
        $delete_existing = true;
    }
}
if (!empty($delete_existing)){
    if (file_exists($full_binary_path)){ unlink($full_binary_path); }
    if (file_exists($full_index_path)){ unlink($full_index_path); }
}

// Check to see if we need to regenerate anything in this context
$must_regenerate = false;
$must_regenerate_binary = $request_format === 'image' && !file_exists($composite_base_path.$composite_image_binary_path) ? true : false;
$must_regenerate_index = $request_format === 'index' && !file_exists($composite_base_path.$composite_image_index_path) ? true : false;
if ($must_regenerate_binary || $must_regenerate_index){
    $must_regenerate = true;
    $must_regenerate_binary = true;
    $must_regenerate_index = true;
}

// First, pull in the index of all objects given the type requested
$composite_objects = array();
$composite_index = array();
$object_xname = $request_type;
$object_name = rtrim($object_xname, 's');
$object_frame_index = array();
if ($request_type === 'abilities'){ $object_name = 'ability'; }
if ($request_type === 'players'){ $object_frame_index = MMRPG_SETTINGS_PLAYER_FRAMEINDEX; }
elseif ($request_type === 'robots'){ $object_frame_index = MMRPG_SETTINGS_ROBOT_FRAMEINDEX; }
elseif ($request_type === 'abilities'){ $object_frame_index = MMRPG_SETTINGS_ABILITY_FRAMEINDEX; }
elseif ($request_type === 'items'){ $object_frame_index = MMRPG_SETTINGS_ITEM_FRAMEINDEX; }
elseif ($request_type === 'skills'){ $object_frame_index = MMRPG_SETTINGS_SKILL_FRAMEINDEX; }
elseif ($request_type === 'fields'){ $object_frame_index = 'base'; }
$object_frame_index = strstr($object_frame_index, '/') ? explode('/', $object_frame_index) : array($object_frame_index);

// If we must regenerate everything, we should do it now
if ($must_regenerate){

    // First, pull in the index of all objects given the type requested
    if ($request_type === 'players'){
        $composite_objects = rpg_player::get_index(true);
    } elseif ($request_type === 'robots'){
        $composite_objects = rpg_robot::get_index(true);
    } elseif ($request_type === 'abilities'){
        $composite_objects = rpg_ability::get_index(true);
    } elseif ($request_type === 'items'){
        $composite_objects = rpg_item::get_index(true);
    } elseif ($request_type === 'skills'){
        $composite_objects = rpg_skill::get_index(true);
    } elseif ($request_type === 'fields'){
        $composite_objects = rpg_field::get_index(true);
    }

    // If the user requested only a specific token, filter the list
    if (!empty($request_token)
        && !empty($composite_objects)){
        $request_tokens = strstr($request_token, ',') ? explode(',', $request_token) : array($request_token);
        //error_log('filter by $request_tokens = '.print_r(array_keys($request_tokens), true));
        $composite_objects = array_filter($composite_objects, function($object) use ($object_name, $request_tokens){
            $allow = false;
            if (in_array($object[$object_name.'_token'], $request_tokens)){ $allow = true; }
            return $allow;
            });
    }

    // If the user has requested a specific editor, we need to create a lookup table and filter
    if (!empty($request_editor)
        && !empty($composite_objects)){
        $request_editors = strstr($request_editor, ',') ? explode(',', $request_editor) : array($request_editor);
        //error_log('filter by $request_editors (before) = '.print_r(array_keys($request_editors), true));

        // Collect an index of editors so we can translate the user's request to legit IDs
        $editor_index = $db->get_array_list("SELECT
            `contributors`.`contributor_id` AS `xref_id`,
            `contributors`.`user_name_clean` AS `editor_token`,
            `users`.`user_id` AS `editor_user_id`,
            `contributors`.`contributor_id` AS `editor_contributor_id`
            FROM `mmrpg_users_contributors` AS `contributors`
            LEFT JOIN `mmrpg_users` AS `users` ON `users`.`user_name_clean` = `contributors`.`user_name_clean`
            WHERE
            `users`.`user_id` <> 0
            ORDER BY
            `users`.`user_id` ASC
            ;", 'xref_id');
        $editor_index_by_token = array();
        $editor_index_by_user_id = array();
        $editor_index_by_contributor_id = array();
        foreach ($editor_index AS $editor_id => $editor_info){
            $editor_index_by_token[$editor_info['editor_token']] = $editor_info;
            $editor_index_by_user_id[$editor_info['editor_user_id']] = $editor_info;
            $editor_index_by_contributor_id[$editor_info['editor_contributor_id']] = $editor_info;
        }

        // Translate requested editor IDs to their real values (editor_contributor_id) so they match-up with image_editor values
        // We do this by accepting either a string (which is the editor token) or an integer (which is the editor user id) and go from there
        $request_editors = array_map(function($editor) use ($editor_index_by_token, $editor_index_by_user_id, $editor_index_by_contributor_id){
            $editor = trim($editor);
            if (is_numeric($editor) && isset($editor_index_by_user_id[$editor])){ $editor = $editor_index_by_user_id[$editor]['editor_contributor_id']; }
            elseif (isset($editor_index_by_token[$editor])){ $editor = $editor_index_by_token[$editor]['editor_contributor_id']; }
            else { $editor = false; }
            return $editor;
            }, $request_editors);
        $request_editors = array_filter($request_editors);
        //error_log('filter by $request_editors (after) = '.print_r(array_keys($request_editors), true));

        // And now finally we can filter the objects by the requested editors
        $composite_objects = array_filter($composite_objects, function($object) use ($object_name, $request_editors){
            $allow = false;
            if (in_array($object[$object_name.'_image_editor'], $request_editors)){ $allow = true; }
            if (isset($object[$object_name.'_image_editor2']) && in_array($object[$object_name.'_image_editor'], $request_editors)){ $allow = true; }
            return $allow;
            });

    }

    //error_log('$composite_objects (raw tokens) = '.print_r(array_keys($composite_objects), true));

    // Loop through the objects and re-key all the fields so they're standardized
    //error_log('$composite_objects (before) = '.print_r($composite_objects, true));
    if (!empty($composite_objects)){
        foreach ($composite_objects AS $object_token => $object_info){
            $new_object_info = array();
            foreach ($object_info AS $field_name => $field_value){
                $new_field_name = str_replace($object_name.'_', '', $field_name);
                $new_object_info[$new_field_name] = $field_value;
            }
            $composite_objects[$object_token] = $new_object_info;
        }
    }
    //error_log('$composite_objects (after) = '.print_r($composite_objects, true));

    // Loop through the objects and filter out any that shouldn't be here (incomplete, etc.)
    if (!empty($composite_objects)){
        foreach ($composite_objects AS $object_token => $object_info){
            // Unset if any of the easy stuff if missing
            if (empty($object_info['image'])
                || $object_info['image'] === $object_name
                || $object_info['class'] === 'system'
                || empty($object_info['flag_published'])
                || empty($object_info['flag_complete'])
                || (isset($object_info['image_sheets']) && empty($object_info['image_sheets']))
                || ($request_type === 'abilities' && $object_info['class'] === 'mecha')
                ){
                unset($composite_objects[$object_token]);
                continue;
            }
            // Now check to see if the file actually exists, else unset for that too
            else {
                $size_string = $object_info['image_size'].'x'.$object_info['image_size'];
                $src_folder = $request_alt > 0 ? 'sprites_alt'.($request_alt > 1 ? $request_alt : '') : 'sprites';
                $src_base = $sprite_object_dir.$object_info['image'].'/'.$src_folder.'/';
                $src_file = preg_replace('/([0-9]{1,3})x([0-9]{1,3})/', $size_string, $request_file_name).'.png';
                $source_path_full = $src_base.$src_file;
                if (!file_exists($source_path_full)){
                    unset($composite_objects[$object_token]);
                    continue;
                }
            }
        }
    }
    //error_log('$composite_objects (filtered) = '.print_r($composite_objects, true));

    // Loop through the objects one more time to find the max width and height
    $max_sprite_width = 0;
    $max_sprite_height = 0;
    if (!empty($composite_objects)){
        foreach ($composite_objects AS $object_token => $object_info){
            if ($object_info['image_size'] > $max_sprite_width){ $max_sprite_width = $object_info['image_size']; }
            if ($object_info['image_size'] > $max_sprite_height){ $max_sprite_height = $object_info['image_size']; }
        }
    }
    //error_log('$max_sprite_width = '.print_r($max_sprite_width, true));
    //error_log('$max_sprite_height = '.print_r($max_sprite_height, true));

    // Set the target width and height for the sprites
    $target_sprite_size = !empty($request_size) ? $request_size : $max_sprite_height;
    $target_sprite_height = !empty($request_size) ? $request_size : $max_sprite_height;
    if ($request_crop
        || strstr($request_file_name, 'icon_')
        || strstr($request_file_name, 'mug_')){
        $target_sprite_width = !empty($request_size) ? $request_size : $max_sprite_width;
    } else {
        $num_frames = count($object_frame_index);
        $target_sprite_width = $target_sprite_height * $num_frames;
    }
    //error_log('$target_sprite_height = '.print_r($target_sprite_height, true));
    //error_log('$target_sprite_width = '.print_r($target_sprite_width, true));

    // Preliminary loop to count the total number of objects, including alts
    $sprite_objects_num = 0;
    if (!empty($composite_objects)){
        foreach ($composite_objects AS $object_token => $object_info){
            $sprite_objects_num++; // count the object itself
            if ($request_alt === 'all' && !empty($object_info['image_alts'])){
                // Count all the alts for this object
                $sprite_objects_num += count($object_info['image_alts']);
            }
        }
    }
    //error_log('$sprite_objects_num = '.print_r($sprite_objects_num, true));

    // If the user has requested too many objects, return a 404 so we don't break the server
    $max_objects_num = 1000;
    if (!$request_crop){ $max_objects_num = ceil($max_objects_num / 10); }
    if ($sprite_objects_num > $max_objects_num){
        $status = 'HTTP/1.0 404 Not Found';
        $message = 'Details: Composite Image'.($request_format === 'index' ? ' Index' : '').' Could Not Be Generated';
        $message .= PHP_EOL.'(Too Many Objects [Total:'.$sprite_objects_num.' vs Limit:'.$max_objects_num.'])';
        header($status);
        header($message);
        echo('<strong>'.$status.'</strong><br />'.PHP_EOL.'<p>'.nl2br($message).'</p>');
        exit;
    }

    // Calculate the grid size based on the total count
    $sprite_objects_sqrt = sqrt($sprite_objects_num);
    if ($request_crop){
        $sprite_objects_grid_width = floor($sprite_objects_sqrt);
        $sprite_objects_grid_height = $sprite_objects_grid_width;
        while ($sprite_objects_grid_width * $sprite_objects_grid_height < $sprite_objects_num){ $sprite_objects_grid_width++; }
    } else {
        $sprite_objects_grid_width = 1;
        $sprite_objects_grid_height = $sprite_objects_num;
    }
    //error_log('$sprite_objects_grid_width = '.print_r($sprite_objects_grid_width, true));
    //error_log('$sprite_objects_grid_height = '.print_r($sprite_objects_grid_height, true));

    // Create a lambda function to calculate col/row/x/y
    $calculate_position = function($object_key, $sprite_objects_grid_width, $sprite_objects_grid_height, $target_sprite_width, $target_sprite_height) {
        $col = $object_key % $sprite_objects_grid_width;
        $row = floor($object_key / $sprite_objects_grid_width);
        $x = $col * $target_sprite_width;
        $y = $row * $target_sprite_height;
        return array('col' => $col, 'row' => $row, 'x' => $x, 'y' => $y);
        };

    // Loop through the objects again and pull out relevant image data for the index
    if (!empty($composite_objects)){
        $object_key = -1;
        foreach ($composite_objects AS $object_token => $object_info){
            $object_key++;
            $position = $calculate_position($object_key, $sprite_objects_grid_width, $sprite_objects_grid_height, $target_sprite_width, $target_sprite_height);
            $size_string = $object_info['image_size'].'x'.$object_info['image_size'];
            $src_folder = $request_alt > 0 ? 'sprites_alt'.($request_alt > 1 ? $request_alt : '') : 'sprites';
            $src_base = $sprite_object_dir.$object_info['image'].'/'.$src_folder.'/';
            $src_file = preg_replace('/([0-9]{1,3})x([0-9]{1,3})/', $size_string, $request_file_name).'.png';
            $source_path_full = $src_base.$src_file;
            $source_path_relative = str_replace(MMRPG_CONFIG_ROOTDIR, '', $source_path_full);
            $composite_config = array(
                'token' => $object_info['token'],
                'image' => $object_info['image'],
                'file' => $request_file_name,
                'source' => $source_path_relative,
                'size' => $object_info['image_size'],
                'position' => array('col' => $position['col'], 'row' => $position['row']),
                'offset' => array('x' => $position['x'], 'y' => $position['y'])
                );
            $composite_index[$object_token] = $composite_config;
            // If the player has requested all alts for this object, we have to add them
            if ($request_alt === 'all'
                && !empty($object_info['image_alts'])){
                foreach ($object_info['image_alts'] AS $alt_key => $alt_info){
                    $object_key++;
                    $position = $calculate_position($object_key, $sprite_objects_grid_width, $sprite_objects_grid_height, $target_sprite_width, $target_sprite_height);
                    $composite_alt_token = $object_token.'_'.$alt_info['token'];
                    $composite_alt_config = $composite_config;
                    $composite_alt_config['token'] = $composite_config['token'].'_'.$alt_info['token'];
                    $composite_alt_config['image'] = $composite_config['image'].'_'.$alt_info['token'];
                    $composite_alt_config['source'] = str_replace('/sprites/', '/sprites_'.$alt_info['token'].'/', $composite_config['source']);
                    $composite_alt_config['position'] = array('col' => $position['col'], 'row' => $position['row']);
                    $composite_alt_config['offset'] = array('x' => $position['x'], 'y' => $position['y']);
                    $composite_index[$composite_alt_token] = $composite_alt_config;
                }
            }
        }
    }
    //error_log('$composite_index = '.print_r($composite_index, true));

}

// Only continue if the composite index is not empty
if ($must_regenerate && !empty($composite_index)){

    // If this is a request for the IMAGE and it doesn't exist, generate it now
    if (true){
        if (file_exists($full_binary_path)){ unlink($full_binary_path); }
        //error_log('Time to generate the composite image binary file!');
        //error_log('$full_binary_path = '.print_r($full_binary_path, true));

        // Create a new true color image canvas with the correct size
        // Use the size of the grid times the size of an individual sprite
        $composite_image_width = $sprite_objects_grid_width * $target_sprite_width;
        $composite_image_height = $sprite_objects_grid_height * $target_sprite_size;
        $composite_image = imagecreatetruecolor($composite_image_width, $composite_image_height);

        // Maintain transparency in the new composite image
        imagealphablending($composite_image, false);
        imagesavealpha($composite_image, true);
        $transparent = imagecolorallocatealpha($composite_image, 0, 0, 0, 127);
        imagefill($composite_image, 0, 0, $transparent);

        // Set the flag for aligning sprites to center
        $align_sprites = true;

        // Set the alignment method ('center-center', 'bottom-center', 'bottom-left')
        $align_method = 'center-center';
        if (strstr($request_file_name, 'sprite')){
            $align_method = 'bottom-center';
            if (!$request_crop){ $align_method = 'bottom-left'; }
        }
        // /error_log('$align_method = '.print_r($align_method, true));

        // Define an array for required source frames, defaulting to only the first
        $required_source_frames = array();
        if ($request_frame === 'all'){ $required_source_frames = range(0, (count($object_frame_index) - 1)); }
        elseif (is_numeric($request_frame)){ $required_source_frames[] = $request_frame; }
        else { $required_source_frames[] = 0; }
        //error_log('$required_source_frames = '.print_r($required_source_frames, true));

        // Loop through all individual icon images and copy them into the composite image
        foreach ($composite_index as $sprite){
            //error_log('exporting $sprite = '.print_r($sprite, true));

            // Load the individual icon image
            $iconImage = imagecreatefrompng(MMRPG_CONFIG_ROOTDIR.$sprite['source']);
            imagealphablending($iconImage, false);
            imagesavealpha($iconImage, true);

            // Loop through required source frames so we can add them to the canvas
            foreach ($required_source_frames AS $required_frame_key => $required_frame_int){
                //error_log('exporting $required_frame_int = '.print_r($required_frame_int, true));

                // Calculate the position where the icon should be copied w/ middle-align if requested
                $destX = $sprite['offset']['x'];
                $destY = $sprite['offset']['y'];
                $sourceX = 0;
                $sourceY = 0;
                $sourceHeight = $sprite['size'];
                $sourceWidth = $sprite['size'];
                //$sourceWidth = $sprite['size'] * (!$request_crop ? count($object_frame_index) : 1);
                //error_log('$sourceWidth (before) = '.print_r($sourceWidth, true));
                //error_log('$sourceHeight (before) = '.print_r($sourceHeight, true));
                if ($align_sprites) {
                    if ($align_method) {
                        if ($align_method === 'center-center'){
                            if ($sprite['size'] > $target_sprite_width) {
                                $sourceX = ($sprite['size'] - $target_sprite_width) / 2;
                                $sourceWidth = $target_sprite_width;
                            } else {
                                $destX += ($target_sprite_width - $sprite['size']) / 2;
                            }
                            if ($sprite['size'] > $target_sprite_height) {
                                $sourceY = ($sprite['size'] - $target_sprite_height) / 2;
                                $sourceHeight = $target_sprite_height;
                            } else {
                                $destY += ($target_sprite_height - $sprite['size']) / 2;
                            }
                        }
                        elseif ($align_method === 'bottom-center'){
                            if ($sprite['size'] > $target_sprite_width) {
                                $sourceX = ($sprite['size'] - $target_sprite_width) / 2;
                                $sourceWidth = $target_sprite_width;
                            } else {
                                $destX += ($target_sprite_width - $sprite['size']) / 2;
                            }
                            if ($sprite['size'] > $target_sprite_height) {
                                $sourceY = $sprite['size'] - $target_sprite_height;
                                $sourceHeight = $target_sprite_height;
                            } else {
                                $destY += ($target_sprite_height - $sprite['size']);
                            }
                        }
                        elseif ($align_method === 'bottom-left'){
                            if ($sprite['size'] <= $target_sprite_width) {
                                // $destX should centered to make up the difference
                                $destX += ($target_sprite_size - $sprite['size']) / 2;
                            } else {
                                $sourceX = 0; // Take from the left of the sprite
                                $sourceWidth = $target_sprite_width; // Crop to the width of the target area
                            }
                            if ($sprite['size'] <= $target_sprite_height) {
                                $destY += ($target_sprite_height - $sprite['size']); // Move sprite to the bottom
                            } else {
                                $sourceY = $sprite['size'] - $target_sprite_height; // Take from the bottom of the sprite
                                $sourceHeight = $target_sprite_height; // Crop to the height of the target area
                            }
                        }
                    }
                }
                //error_log('$sourceWidth (after) = '.print_r($sourceWidth, true));
                //error_log('$sourceHeight (after) = '.print_r($sourceHeight, true));

                // If a different frame of animation was requested, shift the source offset appropriately
                if (!empty($required_frame_int)
                    && is_numeric($required_frame_int)
                    && $required_frame_int > 0){
                    $sourceX += ($sprite['size'] * $required_frame_int);
                }

                // If we're showing multiple frames for this sheet, we may have to adjust the destination offset
                if ($required_frame_key > 0){
                    $destX += ($target_sprite_size * $required_frame_key);
                }

                // Copy the icon image into the composite image at the correct position
                //error_log('-> placing a '.$sprite['token'].' sprite (frame '.$required_frame_int.') to the canvas');
                //error_log('-> imagecopy($composite_image, $iconImage, $destX:'.$destX.', $destY:'.$destY.', $sourceX:'.$sourceX.', $sourceY:'.$sourceY.', $sourceWidth:'.$sourceWidth.', $sourceHeight'.$sourceHeight.');');
                imagecopy($composite_image, $iconImage, $destX, $destY, $sourceX, $sourceY, $sourceWidth, $sourceHeight);


            }

            // Now that we're done, free the memory associated with the icon image
            imagedestroy($iconImage);

        }

        // Save the composite image to a file in PNG format
        imagepng($composite_image, $full_binary_path);

        // Free the memory associated with the composite image
        imagedestroy($composite_image);


    }

    // If this is a request for the INDEX and it doesn't exist, generate it now
    if (true){
        if (file_exists($full_index_path)){ unlink($full_index_path); }
        //error_log('Time to generate the composite image index file!');
        //error_log('$full_index_path = '.print_r($full_index_path, true));

        // Write out the generated index to a JSON file for later use
        $json_index = json_encode($composite_index);
        $json_handle = fopen($full_index_path, 'w');
        fwrite($json_handle, $json_index);
        fclose($json_handle);

    }

}


// If the requested file already exists, we can just return it verbatim
if ($request_format === 'image' && file_exists($composite_base_path.$composite_image_binary_path)){

    // Gather the composite image properties, update the headers, return the file
    $full_path = $composite_base_path.$composite_image_binary_path;
    $image_mime_type = mime_content_type($full_path);
    $image_file_size = filesize($full_path);
    $image_last_modified = date(DATE_RFC2822, filemtime($full_path));
    header('Content-type: '.$image_mime_type);
    header('Content-Length: '.$image_file_size);
    header('Last-Modified: '.$image_last_modified);
    readfile($full_path);
    exit();


} elseif ($request_format === 'index' && file_exists($composite_base_path.$composite_image_index_path)){

    // Pull the file's content into memory, update the headers, return the file
    $full_path = $composite_base_path.$composite_image_index_path;
    $index_mime_type = 'application/json'; //mime_content_type($full_path);
    $index_file_size = filesize($full_path);
    $index_last_modified = date(DATE_RFC2822, filemtime($full_path));
    header('Content-type: '.$index_mime_type);
    header('Content-Length: '.$index_file_size);
    header('Last-Modified: '.$index_last_modified);
    readfile($full_path);
    exit();

} else {

    // If not file exists, we have a problem and should exit now
    $status = 'HTTP/1.0 404 Not Found';
    $message = 'Details: Composite Image'.($request_format === 'index' ? ' Index' : '').' Could Not Be Generated (Unknown Reason)';
    header($status);
    header($message);
    echo('<strong>'.$status.'</strong><br />'.PHP_EOL.'<p>'.$message.'</p>');
    exit;

}



?>