<?php

/*
 * WEBSITE FUNCTIONS
 */

// Define a function for generating the float player markup for the various website pages
function mmrpg_website_text_float_player_markup($player_token, $float_side = 'right', $frame_key = '00', $player_image_size = 40, $player_alt_token = '', $player_item_token = '', $extra_float_classes = ''){
    $player_info = rpg_player::get_index_info($player_token);
    $zoom_size = $player_image_size * 2;
    $zoom_size_text = $zoom_size.'x'.$zoom_size;
    $player_direction = $float_side != 'left' ? 'left' : 'right';
    $player_image_name = $player_token.(!empty($player_alt_token) ? '_'.$player_alt_token : '');
    $player_image_path = 'images/players/'.$player_image_name.'/sprite_'.$player_direction.'_'.$player_image_size.'x'.$player_image_size.'.png';
    $player_image_path .= '?'.MMRPG_CONFIG_CACHE_DATE;
    $player_image_styles = 'background-image: url('.$player_image_path.'); background-size: auto '.$zoom_size.'px; ';
    if ($player_image_size >= 80){ $player_image_styles .= 'margin: -'.($player_image_size + 10).'px auto 0 -'.($player_image_size / 2).'px; '; }
    else { $player_image_styles .= 'margin: -10px auto 0 0; '; }
    $player_image_styles .= 'z-index: 1; ';
    $animation_duration = 1.0;
    if ($player_info['player_type'] === 'attack'){ $animation_duration = 1.2; }
    elseif ($player_info['player_type'] === 'defense'){ $animation_duration = 0.9; }
    elseif ($player_info['player_type'] === 'speed'){ $animation_duration = 0.6; }
    $player_image_styles .= 'animation-duration: '.$animation_duration.'s; ';
    $player_image_classes = 'sprite sprite_'.$zoom_size_text.' sprite_'.$zoom_size_text.'_'.$frame_key;
    $player_image_classes .= ' sprite_animated';
    if (!empty($player_item_token)){
        $item_image_size = 40;
        $zoom_size = $item_image_size * 2;
        $zoom_size_text = $zoom_size.'x'.$zoom_size;
        $item_image_path = 'images/items/'.$player_item_token.'/icon_'.$player_direction.'_'.$item_image_size.'x'.$item_image_size.'.png';
        $item_image_path .= '?'.MMRPG_CONFIG_CACHE_DATE;
        $item_image_styles = 'background-image: url('.$item_image_path.'); background-size: auto '.$zoom_size.'px; ';
        $item_image_styles .= 'position: absolute; top: -10px; right: -30px; ';
        $item_image_styles .= 'z-index: 2; ';
        $item_image_classes = 'sprite sprite_'.$zoom_size_text.' sprite_'.$zoom_size_text.'_00';
    }
    return '<div class="float float_'.$float_side.' float_80x80 '.$extra_float_classes.'" title="'.
                (ucwords(str_replace('-', ' ', $player_token))).
                (!empty($player_item_token) ? ' w/ '.ucwords(str_replace('-', ' ', $player_item_token)) : '').
                '">'.
            '<div class="'.$player_image_classes.'" style="'.$player_image_styles.'"></div>'.
            (isset($item_image_classes) && isset($item_image_styles) ? '<div class="'.$item_image_classes.'" style="'.$item_image_styles.'"></div>' : '').
        '</div>';
}

// Define a function for generating the float robot markup for the various website pages
function mmrpg_website_text_float_robot_markup($robot_token, $float_side = 'right', $frame_key = '00', $robot_image_size = 40, $robot_alt_token = '', $robot_item_token = '', $extra_float_classes = ''){
    $robot_info = rpg_robot::get_index_info($robot_token);
    $zoom_size = $robot_image_size * 2;
    $zoom_size_text = $zoom_size.'x'.$zoom_size;
    $robot_direction = $float_side != 'left' ? 'left' : 'right';
    $robot_image_name = $robot_token.(!empty($robot_alt_token) ? '_'.$robot_alt_token : '');
    $robot_image_path = 'images/robots/'.$robot_image_name.'/sprite_'.$robot_direction.'_'.$robot_image_size.'x'.$robot_image_size.'.png';
    $robot_image_path .= '?'.MMRPG_CONFIG_CACHE_DATE;
    $robot_image_styles = 'background-image: url('.$robot_image_path.'); background-size: auto '.$zoom_size.'px; ';
    if ($robot_image_size >= 80){ $robot_image_styles .= 'margin: -'.($robot_image_size + 10).'px auto 0 -'.($robot_image_size / 2).'px; '; }
    else { $robot_image_styles .= 'margin: -10px auto 0 0; '; }
    $robot_image_styles .= 'z-index: 1; ';
    $animation_duration = rpg_robot::get_css_animation_duration($robot_info);
    $robot_image_styles .= 'animation-duration: '.$animation_duration.'s; ';
    $robot_image_classes = 'sprite sprite_'.$zoom_size_text.' sprite_'.$zoom_size_text.'_'.$frame_key;
    $robot_image_classes .= ' sprite_animated';
    if (!empty($robot_item_token)){
        $item_image_size = 40;
        $zoom_size = $item_image_size * 2;
        $zoom_size_text = $zoom_size.'x'.$zoom_size;
        $item_image_path = 'images/items/'.$robot_item_token.'/icon_'.$robot_direction.'_'.$item_image_size.'x'.$item_image_size.'.png';
        $item_image_path .= '?'.MMRPG_CONFIG_CACHE_DATE;
        $item_image_styles = 'background-image: url('.$item_image_path.'); background-size: auto '.$zoom_size.'px; ';
        $item_image_styles .= 'position: absolute; top: -10px; right: -30px; ';
        $item_image_styles .= 'z-index: 2; ';
        $item_image_classes = 'sprite sprite_'.$zoom_size_text.' sprite_'.$zoom_size_text.'_00';
    }
    return '<div class="float float_'.$float_side.' float_80x80 '.$extra_float_classes.'" title="'.
                (ucwords(str_replace('-', ' ', $robot_token))).
                (!empty($robot_item_token) ? ' w/ '.ucwords(str_replace('-', ' ', $robot_item_token)) : '').
                '">'.
            '<div class="'.$robot_image_classes.'" style="'.$robot_image_styles.'"></div>'.
            (isset($item_image_classes) && isset($item_image_styles) ? '<div class="'.$item_image_classes.'" style="'.$item_image_styles.'"></div>' : '').
        '</div>';
}

// Define a function for parsing formatting code from a string
function mmrpg_formatting_decode($string){

    // Define the static formatting array variable
    static $mmrpg_formatting_array = array();
    static $mmrpg_types_array_string = '';

    // If the formatting array has not been populated, do so
    if (empty($mmrpg_formatting_array)){

        // Collect the robot and ability index from the database
        global $db;
        static $temp_robots_index, $temp_abilities_index, $temp_types_index;
        if (empty($temp_robots_index)){ $temp_robots_index = rpg_robot::get_index(true); }
        if (empty($temp_abilities_index)){ $temp_abilities_index = rpg_ability::get_index(true); }
        if (empty($temp_types_index)){ $temp_types_index = rpg_type::get_index(true, false, true); }

        // Define the array to hold the images of larger size than default
        $mmrpg_large_robot_images = array();
        //$mmrpg_large_ability_images = array();

        // Loop through robots and abilities and collect tokens of large ones
        if (!empty($temp_robots_index)){ foreach ($temp_robots_index AS $token => $info){ if ($info['robot_image_size'] == 80){ $mmrpg_large_robot_images[] = $info['robot_token']; } } }
        //if (!empty($temp_abilities_index)){ foreach ($temp_abilities_index AS $token => $info){ if ($info['ability_image_size'] == 80){ $mmrpg_large_ability_images[] = $info['ability_token']; } } }

        // Create strings for the large robot and ability patterns by imploding the arrays
        $mmrpg_large_robot_images_string = implode('|', $mmrpg_large_robot_images);
        //$mmrpg_large_ability_images_string = implode('|', $mmrpg_large_ability_images);

        // Collect the types array from the index
        $mmrpg_types_array = array_keys($temp_types_index);
        $mmrpg_types_array_string = implode('|', $mmrpg_types_array);
        $mmrpg_types_array_string .= '|neutral';

        // Define a string of acceptable player/robot/ability/item "frames" for formatting
        $player_frames_string = 'base|taunt|victory|defeat|command|damage|base2|01|02|03|04|05|06|07|08|09|10';
        $robot_frames_string = 'base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|01|02|03|04|05|06|07|08|09|10';
        $ability_frames_string = 'base|01|02|03|04|05|06|07|08|09|10';
        $item_frames_string = 'base|01|02|03|04|05|06|07|08|09|10';
        $shop_frames_string = 'base|01|02|03|04|05|06|07|08|09|10';
        $object_frames_string = 'base|01|02|03|04|05|06|07|08|09|10';

        // Fix any instances of objects that used to be items for our users' convenience
        $string = preg_replace('/\[item([\:a-z0-9]+)?\]\{challenge-marker_([-_a-z0-9]+)?\}/i', '[object$1]{challenge-markers/$2}', $string);
        $string = preg_replace('/\[item([\:a-z0-9]+)?\]\{challenge-marker?\}/i', '[object$1]{challenge-markers}', $string);

        // Pull in the global index formatting variables
        $mmrpg_formatting_array = array();

        $mmrpg_formatting_array += array(
            // code font
            '/\s?\[code\]\s?(.*?)\s?\[\/code\]\s+/is' => '$1',  // code
            );
        $mmrpg_formatting_array += array(
            // spacers
            '/\[tab\]/i' => '&nbsp;&nbsp;',
            '/\s{2,}[-]{5,}\s{2,}/i' => '<hr class="line_divider line_divider_bigger" />',
            '/\s?[-]{5,}\s?/i' => '<hr class="line_divider" />',
            '/\s\|\s/i' => '&nbsp;<span class="pipe">|</span>&nbsp;',
            );
        $mmrpg_formatting_array += array(
            // font block formatting
            '/\s{2,}\[font="?([-_a-z0-9\s]+)"?\](?:\s+)?(.*?)(?:\s+)?\[\/font\]\s{2,}/ism' => '<div class="font_block" style="font-family: \'$1\';">$2</div>',
            '/\s?\[font="?([-_a-z0-9\s]+)"?\](?:\s+)?(.*?)(?:\s+)?\[\/font\]/ism' => '<span class="font_inline" style="font-family: \'$1\';">$2</span>',
            // system block formatting
            '/\s{2,}\[system\](?:\s+)?(.*?)(?:\s+)?\[\/system\]\s{2,}/ism' => '<div class="code">$1</div>',
            '/\s?\[system\](?:\s+)?(.*?)(?:\s+)?\[\/system\]/ism' => '<span class="code">$1</span>',
            );
        $mmrpg_formatting_array += array(
            // image-inline (no hover, no link)
            '/\[image\]\((.*?).(jpg|jpeg|gif|png|bmp)\)/i' => '<span class="link_image_inline"><img src="$1.$2" /></span>',
            );
        $mmrpg_formatting_array += array(
            // sprite 40x40
            '/\[sprite\]/i' => '<span class="sprite_image sprite_image_40x40"></span>',
            );
        $mmrpg_formatting_array += array(

            // player 40x40
            '/\[player\]\{(.*?)\}/i' => '<span class="sprite_image player sprite_image_40x40"><img src="images/players/$1/mug_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[player:(left|right)\]\{(.*?)\}/i' => '<span class="sprite_image player sprite_image_40x40"><img src="images/players/$2/mug_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[player:(left|right):('.$player_frames_string.')\]\{(.*?)\}/i' => '<span class="sprite_image player sprite_image_40x40 sprite_image_40x40_$2"><span><img src="images/players/$3/sprite_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            '/\[player:left:('.$player_frames_string.'):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$1" style="right: $2px; bottom: $3px; z-index: $4;"><span><img src="images/players/$5/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[player:right:('.$player_frames_string.'):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$1" style="left: $2px; bottom: $3px; z-index: $4;"><span><img src="images/players/$5/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[player:left:('.$player_frames_string.'):(-?[0-9]+),(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$1" style="right: $2px; bottom: $3px;"><span><img src="images/players/$4/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[player:right:('.$player_frames_string.'):(-?[0-9]+),(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$1" style="left: $2px; bottom: $3px;"><span><img src="images/players/$4/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[player:left:('.$player_frames_string.'):(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$1" style="right: $2px;"><span><img src="images/players/$3/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            '/\[player:right:('.$player_frames_string.'):(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$1" style="left: $2px;"><span><img src="images/players/$3/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',

            '/"images\/players\/(https?:\/\/(?:[^"]+))\/(?:sprite|mug|icon)_(?:left|right)_(?:40x40|80x80|160x160).png\?(?:[-0-9]+)"/i' => '"$1"',
            '/alt="https?:\/\/(?:[^"]+)"/i' => 'alt=""',

            );
        $mmrpg_formatting_array += array(

            // robot 80x80 (official)
            '/\[(robot|mecha|boss)\]\{((?:'.$mmrpg_large_robot_images_string.')(?:_[a-z0-9]+)?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80"><img src="images/robots/$2/mug_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[(robot|mecha|boss):(left|right)\]\{((?:'.$mmrpg_large_robot_images_string.')(?:_[a-z0-9]+)?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80"><img src="images/robots/$3/mug_$2_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span>',
            '/\[(robot|mecha|boss):(left|right):('.$robot_frames_string.')\]\{((?:'.$mmrpg_large_robot_images_string.')(?:_[a-z0-9]+)?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$3"><span><img src="images/robots/$4/sprite_$2_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[(robot|mecha|boss):left:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\]\{((?:'.$mmrpg_large_robot_images_string.')(?:_[a-z0-9]+)?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="right: $3px; bottom: $4px; z-index: $5;"><span><img src="images/robots/$6/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$6" /></span></span>',
            '/\[(robot|mecha|boss):right:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\]\{((?:'.$mmrpg_large_robot_images_string.')(?:_[a-z0-9]+)?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="left: $3px; bottom: $4px; z-index: $5;"><span><img src="images/robots/$6/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$6" /></span></span>',
            '/\[(robot|mecha|boss):left:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+)\]\{((?:'.$mmrpg_large_robot_images_string.')(?:_[a-z0-9]+)?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="right: $3px; bottom: $4px;"><span><img src="images/robots/$5/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[(robot|mecha|boss):right:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+)\]\{((?:'.$mmrpg_large_robot_images_string.')(?:_[a-z0-9]+)?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="left: $3px; bottom: $4px;"><span><img src="images/robots/$5/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[(robot|mecha|boss):left:('.$robot_frames_string.'):(-?[0-9]+)\]\{((?:'.$mmrpg_large_robot_images_string.')(?:_[a-z0-9]+)?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="right: $3px;"><span><img src="images/robots/$4/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[(robot|mecha|boss):right:('.$robot_frames_string.'):(-?[0-9]+)\]\{((?:'.$mmrpg_large_robot_images_string.')(?:_[a-z0-9]+)?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="left: $3px;"><span><img src="images/robots/$4/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',

            // robot 80x80 (custom)
            '/\[(robot|mecha|boss)\]\[80\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80"><img src="images/robots/$2/mug_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[(robot|mecha|boss):(left|right)\]\[80\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80"><img src="images/robots/$3/mug_$2_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span>',
            '/\[(robot|mecha|boss):(left|right):('.$robot_frames_string.')\]\[80\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$3"><span><img src="images/robots/$4/sprite_$2_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[(robot|mecha|boss):left:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\]\[80\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="right: $3px; bottom: $4px; z-index: $5;"><span><img src="images/robots/$6/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$6" /></span></span>',
            '/\[(robot|mecha|boss):right:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\]\[80\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="left: $3px; bottom: $4px; z-index: $5;"><span><img src="images/robots/$6/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$6" /></span></span>',
            '/\[(robot|mecha|boss):left:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+)\]\[80\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="right: $3px; bottom: $4px;"><span><img src="images/robots/$5/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[(robot|mecha|boss):right:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+)\]\[80\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="left: $3px; bottom: $4px;"><span><img src="images/robots/$5/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[(robot|mecha|boss):left:('.$robot_frames_string.'):(-?[0-9]+)\]\[80\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="right: $3px;"><span><img src="images/robots/$4/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[(robot|mecha|boss):right:('.$robot_frames_string.'):(-?[0-9]+)\]\[80\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_80x80 sprite_image_80x80_$2" style="left: $3px;"><span><img src="images/robots/$4/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',

            // robot 40x40 (official / custom)
            '/\[(robot|mecha|boss)\](?:\[40\])?\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40"><img src="images/robots/$2/mug_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[(robot|mecha|boss):(left|right)\](?:\[40\])?\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40"><img src="images/robots/$3/mug_$2_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span>',
            '/\[(robot|mecha|boss):(left|right):('.$robot_frames_string.')\](?:\[40\])?\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$3"><span><img src="images/robots/$4/sprite_$2_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[(robot|mecha|boss):left:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\](?:\[40\])?\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="right: $3px; bottom: $4px; z-index: $5;"><span><img src="images/robots/$6/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$6" /></span></span>',
            '/\[(robot|mecha|boss):right:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\](?:\[40\])?\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="left: $3px; bottom: $4px; z-index: $5;"><span><img src="images/robots/$6/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$6" /></span></span>',
            '/\[(robot|mecha|boss):left:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+)\](?:\[40\])?\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="right: $3px; bottom: $4px;"><span><img src="images/robots/$5/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[(robot|mecha|boss):right:('.$robot_frames_string.'):(-?[0-9]+),(-?[0-9]+)\](?:\[40\])?\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="left: $3px; bottom: $4px;"><span><img src="images/robots/$5/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[(robot|mecha|boss):left:('.$robot_frames_string.'):(-?[0-9]+)\](?:\[40\])?\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="right: $3px;"><span><img src="images/robots/$4/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[(robot|mecha|boss):right:('.$robot_frames_string.'):(-?[0-9]+)\](?:\[40\])?\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="left: $3px;"><span><img src="images/robots/$4/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',

            '/"images\/robots\/(https?:\/\/(?:[^"]+))\/(?:sprite|mug|icon)_(?:left|right)_(?:40x40|80x80|160x160).png\?(?:[-0-9]+)"/i' => '"$1"',
            '/alt="https?:\/\/(?:[^"]+)"/i' => 'alt=""',

            );

        $mmrpg_formatting_array += array(

            // ability/item/object/shop 40x40
            '/\[(ability|item|object|shop)\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40"><img src="images/$1/$2/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[(ability|item|object|shop):(left|right)\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40"><img src="images/$1/$3/icon_$2_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span>',
            '/\[(ability|item|object|shop):(left|right):([a-z0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$3"><span><img src="images/$1/$4/sprite_$2_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[(ability|item|object|shop):left:([a-z0-9]+):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="right: $3px; bottom: $4px; z-index: $5;"><span><img src="images/$1/$6/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$6" /></span></span>',
            '/\[(ability|item|object|shop):right:([a-z0-9]+):(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="left: $3px; bottom: $4px; z-index: $5;"><span><img src="images/$1/$6/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$6" /></span></span>',
            '/\[(ability|item|object|shop):left:([a-z0-9]+):(-?[0-9]+),(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="right: $3px; bottom: $4px;"><span><img src="images/$1/$5/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[(ability|item|object|shop):right:([a-z0-9]+):(-?[0-9]+),(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="left: $3px; bottom: $4px;"><span><img src="images/$1/$5/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$5" /></span></span>',
            '/\[(ability|item|object|shop):left:([a-z0-9]+):(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="right: $3px;"><span><img src="images/$1/$4/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',
            '/\[(ability|item|object|shop):right:([a-z0-9]+):(-?[0-9]+)\]\{(.*?)\}/i' => '<span class="sprite_image $1 sprite_image_40x40 sprite_image_40x40_$2" style="left: $3px;"><span><img src="images/$1/$4/sprite_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$4" /></span></span>',

            '/images\/ability\//i' => 'images/abilities/',
            '/images\/item\//i' => 'images/items/',
            '/images\/object\//i' => 'images/objects/',
            '/images\/shop\//i' => 'images/shops/',

            '/"images\/(?:abilities|items|objects|shops)\/(https?:\/\/(?:[^"]+))\/(?:sprite|mug|icon)_(?:left|right)_(?:40x40|80x80|160x160).png\?(?:[-0-9]+)"/i' => '"$1"',
            '/alt="https?:\/\/(?:[^"]+)"/i' => 'alt=""',

            );

        $mmrpg_formatting_array += array(

            // spoiler tags
            '/\[([^\[\]]+)\]\{spoiler\}/i' => '<span class="type type_span ability_type ability_type_space spoiler_span"><i class="fa fas fa-eye"></i> $1</span>',

            /*

            // inline colours
            '/\[([^\[\]]+)\]\{#([a-f0-9]{6})\}/i' => '<span class="colour_inline" style="color: #$2;">$1</span>',
            '/\[([^\[\]]+)\]\{([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3})\}/i' => '<span class="colour_inline" style="color: rgb($2, $3, $4);">$1</span>',

            // inline text with link to image
            '/\[([^\[\]]+)\]\((.*?).(jpg|jpeg|gif|png|bmp)\:text\)/i' => '<a class="link_inline" href="$2.$3" target="_blank">$1</a>',
            '/\[([^\[\]]+)\]\((.*?).(jpg|jpeg|gif|png|bmp)\:image\)/i' => '<a class="link_image_inline" href="$2.$3" target="_blank"><img src="$2.$3" alt="$1" title="$1" /></a>',

            // elemental type
            '/\[([^\[\]]+)\]\{(.*?)\}/i' => '<span class="type type_span ability_type ability_type_$2">$1</span>',

            */

            );

    }

    // -- REPLACE CODE BLOCKS -- //

    // Define the newline string
    $nl = "\n";

    // Strip any illegal HTML from the string
    $string = strip_tags($string);
    $string = preg_replace('/\[\/code\]\[\/code\]/i', '[&#47;code][/code]', $string);
    //$string = preg_replace('/\s+/', ' ', $string);
    // Loop through each find, and replace with the appropriate replacement
    $code_matches = array();
    $has_code = preg_match_all('/\[code\]\s?(.*?)\s?\[\/code\]/is', $string, $code_matches);
    //if ($has_code){ echo('<pre style="background-color: white; clear: both; width: 100%; white-space: normal; color: #000000; margin: 0 auto 20px;">$code_matches = '.print_r($code_matches[0], true).'</pre>'); }
    if ($has_code){ foreach ($code_matches[0] AS $key => $match){ $string = str_replace($match, '##CODE'.$key.'##', $string); } }
    //if ($has_code){ echo('<pre style="background-color: white; clear: both; width: 100%; white-space: normal; color: #000000; margin: 0 auto 20px;">$string = '.print_r($string, true).'</pre>'); }


    // -- REPLACE STANDARD FORMATTING -- //

    // Replace all the other, inline formatting with its markup
    foreach ($mmrpg_formatting_array AS $find_pattern => $replace_pattern){ $string = preg_replace($find_pattern, $replace_pattern, $string); }

    // Start off the count variable for later
    $count = 0;

    // Replace special types of line breaks with one single type
    //$string = str_replace("\r\n", "\n", $string);
    $string = str_replace(array("\r\n", "\r"), "\n", $string);

    // -- REPLACE BBCODE BLOCKS/SPANS -- //

    // Recusively replace all the BOLD blocks with their div markup
    do { $string = preg_replace('/\[b\](.*?)\[\/b\]/is', '<strong class="bold">$1</strong>', $string, -1, $count); }
    while ($count > 0);

    // Recusively replace all the ITALIC blocks with their div markup
    do { $string = preg_replace('/\[i\](.*?)\[\/i\]/is', '<em class="italic">$1</em>', $string, -1, $count); }
    while ($count > 0);

    // Recusively replace all the UNDERLINE blocks with their div markup
    do { $string = preg_replace('/\[u\](.*?)\[\/u\]/is', '<span class="underline">$1</span>', $string, -1, $count); }
    while ($count > 0);

    // Recusively replace all the STRIKE blocks with their div markup
    do { $string = preg_replace('/\[s\](.*?)\[\/s\]/is', '<span class="strike">$1</span>', $string, -1, $count); }
    while ($count > 0);

    // -- REPLACE SIZE/COLOR SPANS -- //

    // Recusively replace all the size spans with their span markup
    do { $string = preg_replace('/\[([^\[\]]+)\]\{(small|medium|large)\}/i', '<span class="size_$2">$1</span>', $string, -1, $count); }
    while ($count > 0);

    // Recusively replace all the colour(hex) spans with their span markup
    do { $string = preg_replace('/\[([^\[\]]+)\]\{(#[a-f0-9]{6}|rgb\([0-9]+,[0-9]+,[0-9]+\)|rgba\([0-9]+,[0-9]+,[0-9]+,[.0-9]+\))\}/i', '<span class="colour_inline" style="color: $2;">$1</span>', $string, -1, $count); }
    while ($count > 0);

    // -- REPLACE BLOCK/SPAN GIVEN WHITESPACE -- //

    $block_span_kinds = array();
    $block_span_kinds[] = array('content' => '(\n+(?:.*?)\n+)', 'element' => 'div', 'class' => 'panel');
    $block_span_kinds[] = array('content' => '(.*?)', 'element' => 'span', 'class' => 'span');
    foreach ($block_span_kinds AS $key => $info){
        $content = $info['content'];
        $element = $info['element'];
        $class = $info['class'];

        // -- REPLACE TYPE BLOCKS -- //

        // Replace type blocks code with relavant markup [type-name:width,height][/type]
        do { $string = preg_replace('/\[type(?:-|=|\:)([_a-z]+)(?:-|=|\:)([0-9]+%|[0-9]+px),([0-9]+%|[0-9]+px)\]'.$content.'\[\/type(?:-\1)?\]/is', '<'.$element.' class="type type_'.$class.' type_$1" style="width: $2; height: $3;">$4</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace type blocks code with relavant markup [type-name:width][/type]
        do { $string = preg_replace('/\[type(?:-|=|\:)([_a-z]+)(?:-|=|\:)([0-9]+%|[0-9]+px)\]'.$content.'\[\/type(?:-\1)?\]/is', '<'.$element.' class="type type_'.$class.' type_$1" style="width: $2;">$3</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace type blocks code with relavant markup [type-name][/type]
        do { $string = preg_replace('/\[type(?:-|=|\:)([_a-z]+)\]'.$content.'\[\/type(?:-\1)?\]/is', '<'.$element.' class="type type_'.$class.' type_$1">$2</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace type blocks code with relavant markup [type][/type]
        do { $string = preg_replace('/\[type\]'.$content.'\[\/type\]/is', '<'.$element.' class="type type_'.$class.' type_none">$1</'.$element.'>', $string, -1, $count); }
        while ($count > 0);

        // -- REPLACE COLOUR BLOCKS/SPANS (LEGACY) -- //

        // Replace colour blocks code with relavant markup [color-value:width,height][/color]
        do { $string = preg_replace('/\[color(?:-|=|\:)([0-9]+,\s?[0-9]+,\s?[0-9]+)(?:-|=|\:)([0-9]+%|[0-9]+px),([0-9]+%|[0-9]+px)\]'.$content.'\[\/color(?:-\1)?\]/is', '<'.$element.' class="colour_'.$class.'" style="color: rgb($1); width: $2; height: $3;">$4</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace colour blocks code with relavant markup [color-value:width][/color]
        do { $string = preg_replace('/\[color(?:-|=|\:)([0-9]+,\s?[0-9]+,\s?[0-9]+)(?:-|=|\:)([0-9]+%|[0-9]+px)\]'.$content.'\[\/color(?:-\1)?\]/is', '<'.$element.' class="colour_'.$class.'" style="color: rgb($1); width: $2;">$3</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace colour blocks code with relavant markup [color-value][/color]
        do { $string = preg_replace('/\[color(?:-|=|\:)([0-9]+,\s?[0-9]+,\s?[0-9]+)\]'.$content.'\[\/color(?:-\1)?\]/is', '<'.$element.' class="colour_'.$class.'" style="color: rgb($1);">$2</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace colour blocks code with relavant markup [color][/color]
        do { $string = preg_replace('/\[color\]'.$content.'\[\/color\]/is', '<'.$element.' class="colour_'.$class.'" style="color: #FFFFFF;">$1</'.$element.'>', $string, -1, $count); }
        while ($count > 0);

        // -- REPLACE COLOUR BLOCKS/SPANS (NEW) -- //

        // Replace colour blocks code with relavant markup [color-value:width,height][/color]
        do { $string = preg_replace('/\[color(?:-|=|\:)(#[a-f0-9]{6}|[a-z]+|rgb\([0-9]+,\s?[0-9]+,\s?[0-9]+\)|rgba\([0-9]+,\s?[0-9]+,\s?[0-9]+,\s?[.0-9]+\))(?:-|=|\:)([0-9]+%|[0-9]+px),([0-9]+%|[0-9]+px)\]'.$content.'\[\/color(?:-\1)?\]/is', '<'.$element.' class="colour_'.$class.'" style="color: $1; width: $2; height: $3;">$4</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace colour blocks code with relavant markup [color-value:width][/color]
        do { $string = preg_replace('/\[color(?:-|=|\:)(#[a-f0-9]{6}|[a-z]+|rgb\([0-9]+,\s?[0-9]+,\s?[0-9]+\)|rgba\([0-9]+,\s?[0-9]+,\s?[0-9]+,\s?[.0-9]+\))(?:-|=|\:)([0-9]+%|[0-9]+px)\]'.$content.'\[\/color(?:-\1)?\]/is', '<'.$element.' class="colour_'.$class.'" style="color: $1; width: $2;">$3</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace colour blocks code with relavant markup [color-value][/color]
        do { $string = preg_replace('/\[color(?:-|=|\:)(#[a-f0-9]{6}|[a-z]+|rgb\([0-9]+,\s?[0-9]+,\s?[0-9]+\)|rgba\([0-9]+,\s?[0-9]+,\s?[0-9]+,\s?[.0-9]+\))\]'.$content.'\[\/color(?:-\1)?\]/is', '<'.$element.' class="colour_'.$class.'" style="color: $1;">$2</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace colour blocks code with relavant markup [color][/color]
        do { $string = preg_replace('/\[color\]'.$content.'\[\/color\]/is', '<'.$element.' class="colour_'.$class.'" style="color: #FFFFFF;">$1</'.$element.'>', $string, -1, $count); }
        while ($count > 0);

        // -- REPLACE SIZE BLOCKS -- //

        // Replace size blocks code with relavant markup [size-keyword:width,height][/size]
        do { $string = preg_replace('/\[size(?:-|=|\:)(small|medium|large)(?:-|=|\:)([0-9]+%|[0-9]+px),([0-9]+%|[0-9]+px)\]'.$content.'\[\/size(?:-\1)?\]/is', '<'.$element.' class="size_$1" style="width: $2; height: $3;">$4</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace size blocks code with relavant markup [size-keyword:width][/size]
        do { $string = preg_replace('/\[size(?:-|=|\:)(small|medium|large)(?:-|=|\:)([0-9]+%|[0-9]+px)\]'.$content.'\[\/size(?:-\1)?\]/is', '<'.$element.' class="size_$1" style="width: $2;">$3</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace size blocks code with relavant markup [size-keyword][/size]
        do { $string = preg_replace('/\[size(?:-|=|\:)(small|medium|large)\]'.$content.'\[\/size(?:-\1)?\]/is', '<'.$element.' class="size_$1">$2</'.$element.'>', $string, -1, $count); }
        while ($count > 0);
        // Replace size blocks code with relavant markup [size][/size]
        do { $string = preg_replace('/\[size\]'.$content.'\[\/size\]/is', '<'.$element.' class="size_medium">$1</'.$element.'>', $string, -1, $count); }
        while ($count > 0);

    }

    // -- REPLACE IMAGES -- //

    // Recusively replace all the inline images with their span markup
    do { $string = preg_replace('/\[([^\[\]]+)\]\(([^\s]+).(jpg|jpeg|gif|png|bmp)\)/i', '<a class="link_image_inline" href="$2.$3" target="_blank"><img src="$2.$3" alt="$1" title="$1" /></a>', $string, -1, $count); }
    while ($count > 0);

    // -- REPLACE LINKS -- //

    // Recusively replace all the standard text links with their markup
    do { $string = preg_replace('/\[([^\[\]]+)\]\(([^\s]+)\)/i', '<a class="link_inline" href="$2" target="_blank">$1</a>', $string, -1, $count); }
    while ($count > 0);

    // -- REPLACE TYPE SPANS -- //

    // Recusively replace all the dual type spans with their span markup
    do { $string = preg_replace('/\[([^\[\]]+)\]\{('.$mmrpg_types_array_string.')_('.$mmrpg_types_array_string.')\}/i', '<span class="type type_span type_$2_$3">$1</span>', $string, -1, $count); }
    while ($count > 0);

    // Recusively replace all the single type spans with their span markup
    do { $string = preg_replace('/\[([^\[\]]+)\]\{('.$mmrpg_types_array_string.')\}/i', '<span class="type type_span type_$2">$1</span>', $string, -1, $count); }
    while ($count > 0);

    // Replace any instances of "neutral" type blocks to "none" as they should be
    $string = preg_replace('/(\s|")type_([a-z]+_)?(neutral)(_[a-z]+)?(\s|")/i', '$1type_$2none$4$5', $string);

    // -- REPLACE BACKGROUND BLOCKS -- //

    // Replace background blocks code with relavant markup [background-name:posx,posy:width,height][/background]
    do { $string = preg_replace('/\[background(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(left|right|center|[0-9]+%|[0-9]+px),(top|bottom|center|[0-9]+%|[0-9]+px)(?:-|=|\:)([0-9]+%|[0-9]+px),([0-9]+%|[0-9]+px)\](.*?)\[\/background(?:-\1)?(?:-\2)\]/is', '<div class="field field_panel field_panel_background" style="background-position: $2 $3; width: $4; height: $5; background-image: url(images/fields/$1/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$6</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace background blocks code with relavant markup [background-name:posx,posy:width][/background]
    do { $string = preg_replace('/\[background(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(left|right|center|[0-9]+%|[0-9]+px),(top|bottom|center|[0-9]+%|[0-9]+px)(?:-|=|\:)([0-9]+%|[0-9]+px)\](.*?)\[\/background(?:-\1)?(?:-\2)\]/is', '<div class="field field_panel field_panel_background" style="background-position: $2 $3; width: $4; background-image: url(images/fields/$1/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$5</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace background blocks code with relavant markup [background-name:posx,posy][/background]
    do { $string = preg_replace('/\[background(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(left|right|center|[0-9]+%|[0-9]+px),(top|bottom|center|[0-9]+%|[0-9]+px)\](.*?)\[\/background(?:-\1)?(?:-\2)?\]/is', '<div class="field field_panel field_panel_background" style="background-position: $2 $3; background-image: url(images/fields/$1/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$4</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace background blocks code with relavant markup [background-name:posy][/background]
    do { $string = preg_replace('/\[background(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(top|bottom)\](.*?)\[\/background(?:-\1)(?:-\2)?\]/is', '<div class="field field_panel field_panel_background" style="background-position: center $2; background-image: url(images/fields/$1/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$3</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace background blocks code with relavant markup [background-name:posx][/background]
    do { $string = preg_replace('/\[background(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(left|right|center|[0-9]+%|[0-9]+px)\](.*?)\[\/background(?:-\1)?(?:-\2)?\]/is', '<div class="field field_panel field_panel_background" style="background-position: $2 center; background-image: url(images/fields/$1/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$3</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace background blocks code with relavant markup [background-name][/background]
    do { $string = preg_replace('/\[background(?:-|=|\:)([-_a-z0-9]+)\](.*?)\[\/background(?:-\1)?(?:-\2)?\]/is', '<div class="field field_panel field_panel_background" style="background-image: url(images/fields/$1/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$2</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace background blocks code with relavant markup [background][/background]
    do { $string = preg_replace('/\[background\](.*?)\[\/background\]/is', '<div class="field field_panel field_panel_background"><div class="wrap">$1</div></div>', $string, -1, $count); }
    while ($count > 0);

    // -- REPLACE FOREGROUND BLOCKS -- //

    // Replace foreground blocks code with relavant markup [foreground-name:posx,posy:width,height][/foreground]
    do { $string = preg_replace('/\[foreground(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(left|right|center|[0-9]+%|[0-9]+px),(top|bottom|center|[0-9]+%|[0-9]+px)(?:-|=|\:)([0-9]+%|[0-9]+px),([0-9]+%|[0-9]+px)\](.*?)\[\/foreground(?:-\1)?(?:-\2)?\]/is', '<div class="field field_panel field_panel_foreground" style="background-position: $2 $3; width: $4; height: $5; background-image: url(images/fields/$1/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$6</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace foreground blocks code with relavant markup [foreground-name:posx,posy:width][/foreground]
    do { $string = preg_replace('/\[foreground(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(left|right|center|[0-9]+%|[0-9]+px),(top|bottom|center|[0-9]+%|[0-9]+px)(?:-|=|\:)([0-9]+%|[0-9]+px)\](.*?)\[\/foreground(?:-\1)?(?:-\2)?\]/is', '<div class="field field_panel field_panel_foreground" style="background-position: $2 $3; width: $4; background-image: url(images/fields/$1/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$5</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace foreground blocks code with relavant markup [foreground-name:posx,posy][/foreground]
    do { $string = preg_replace('/\[foreground(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(left|right|center|[0-9]+%|[0-9]+px),(top|bottom|center|[0-9]+%|[0-9]+px)\](.*?)\[\/foreground(?:-\1)?(?:-\2)?\]/is', '<div class="field field_panel field_panel_foreground" style="background-position: $2 $3; background-image: url(images/fields/$1/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$4</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace foreground blocks code with relavant markup [foreground-name:posy][/foreground]
    do { $string = preg_replace('/\[foreground(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(top|bottom)\](.*?)\[\/foreground(?:-\1)?(?:-\2)?\]/is', '<div class="field field_panel field_panel_foreground" style="background-position: center $2; background-image: url(images/fields/$1/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$3</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace foreground blocks code with relavant markup [foreground-name:posx][/foreground]
    do { $string = preg_replace('/\[foreground(?:-|=|\:)([-_a-z0-9]+)(?:-|=|\:)(left|right|center|[0-9]+%|[0-9]+px)\](.*?)\[\/foreground(?:-\1)?(?:-\2)?\]/is', '<div class="field field_panel field_panel_foreground" style="background-position: $2 center; background-image: url(images/fields/$1/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$3</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace foreground blocks code with relavant markup [foreground-name][/foreground]
    do { $string = preg_replace('/\[foreground(?:-|=|\:)([-_a-z0-9]+)\](.*?)\[\/foreground(?:-\1)?(?:-\2)?\]/is', '<div class="field field_panel field_panel_foreground" style="background-image: url(images/fields/$1/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$2</div></div>', $string, -1, $count); }
    while ($count > 0);
    // Replace foreground blocks code with relavant markup [foreground][/foreground]
    do { $string = preg_replace('/\[foreground\](.*?)\[\/foreground\]/is', '<div class="field field_panel field_panel_foreground"><div class="wrap">$1</div></div>', $string, -1, $count); }
    while ($count > 0);

    // -- REPLACE LAYER WRAPPERS -- //

    // Replace layer wrappers code with relavant markup [layer][/layer]
    do { $string = preg_replace('/\[layer(?:-|=|\:)([0-9]+)%\](.*?)\[\/layer\]/is', '<div class="layer" style="opacity: 0.$1;">$2</div>', $string, -1, $count); }
    while ($count > 0);

    // Replace layer wrappers code with relavant markup [layer][/layer]
    do { $string = preg_replace('/\[layer\](.*?)\[\/layer\]/is', '<div class="layer">$1</div>', $string, -1, $count); }
    while ($count > 0);

    // -- REPLACE ALIGN BLOCKS -- //

    // Replace align blocks code with relavant markup [align-direction:width,height][/align]
    do { $string = preg_replace('/\s?\[align(?:-|=)(left|right|center)(?:-|=|\:)([0-9]+%|[0-9]+px),([0-9]+%|[0-9]+px)\](.*?)\[\/align(?:-\1)?\]\s?/is', '<div class="align_panel align_$1" style="width: $2; height: $3;">$4</div>', $string, -1, $count); }
    while ($count > 0);
    // Replace align blocks code with relavant markup [align-direction:width][/align]
    do { $string = preg_replace('/\s?\[align(?:-|=)(left|right|center)(?:-|=|\:)([0-9]+%|[0-9]+px)\](.*?)\[\/align(?:-\1)?\]\s?/is', '<div class="align_panel align_$1" style="width: $2;">$3</div>', $string, -1, $count); }
    while ($count > 0);
    // Replace align blocks code with relavant markup [align-direction][/align]
    do { $string = preg_replace('/\s?\[align(?:-|=)(left|right|center)\](.*?)\[\/align(?:-\1)?\]\s?/is', '<div class="align_panel align_$1">$2</div>', $string, -1, $count); }
    while ($count > 0);
    // Replace align blocks code with relavant markup [align][/align]
    do { $string = preg_replace('/\s?\[align\](.*?)\[\/align\]\s?/is', '<div class="align_panel align_left">$1</div>', $string, -1, $count); }
    while ($count > 0);

    // -- REPLACE FLOAT BLOCKS -- //

    // Replace float blocks code with relavant markup [float-direction:width,height][/float]
    do { $string = preg_replace('/\s?\[float(?:-|=|\:)(left|right|none)(?:-|=|\:)([0-9]+%|[0-9]+px),([0-9]+%|[0-9]+px)\](.*?)\[\/float(?:-\1)?\]\s?/is', '<div class="float_panel float_$1" style="width: $2; height: $3;">$4</div>', $string, -1, $count); }
    while ($count > 0);
    // Replace float blocks code with relavant markup [float-direction:width][/float]
    do { $string = preg_replace('/\s?\[float(?:-|=|\:)(left|right|none)(?:-|=|\:)([0-9]+%|[0-9]+px)\](.*?)\[\/float(?:-\1)?\]\s?/is', '<div class="float_panel float_$1" style="width: $2;">$3</div>', $string, -1, $count); }
    while ($count > 0);
    // Replace float blocks code with relavant markup [float-direction][/float]
    do { $string = preg_replace('/\s?\[float(?:-|=|\:)(left|right|none)\](.*?)\[\/float(?:-\1)?\]\s?/is', '<div class="float_panel float_$1">$2</div>', $string, -1, $count); }
    while ($count > 0);
    // Replace float blocks code with relavant markup [float][/float]
    do { $string = preg_replace('/\s?\[float\](.*?)\[\/float\]\s?/is', '<div class="float_left">$1</div>', $string, -1, $count); }
    while ($count > 0);

    // -- REPLACE COMIC BLOCKS -- //

    // Replace comic layout code with relavant markup

    do { $string = preg_replace('/\s?\[comic(?:-layout)?\](.*?)\[\/comic(?:-layout)?\]\s?/is', '<div class="comic_layout">$1</div>', $string, -1, $count); }
    while ($count > 0);

    do { $string = preg_replace('/\s?\[(?:comic-)?panel\](.*?)\[\/(?:comic-)?panel\]\s?/is', '<div class="comic_panel">$1</div>', $string, -1, $count); }
    while ($count > 0);

    do { $string = preg_replace('/\s{0,}\[intro(?:-text)?\](.*?)\[\/intro(?:-text)?\]\s?/is', '<div class="align_panel align_left intro">$1</div>', $string, -1, $count); }
    while ($count > 0);

    do { $string = preg_replace('/\s{0,}\[quote(?:-left)?\](.*?)\[\/quote(?:-left)?\]\s?/is', '<div class="align_panel align_left quote">$1</div>', $string, -1, $count); }
    while ($count > 0);

    do { $string = preg_replace('/\s{0,}\[quote-right?\](.*?)\[\/quote(?:-right)?\]\s?/is', '<div class="align_panel align_right quote">$1</div>', $string, -1, $count); }
    while ($count > 0);


    // -- RE-INSERT CODE BLOCKS -- //

    // Loop through any code blocks and replace their contents with the original markup
    if ($has_code){ foreach ($code_matches[1] AS $key => $match){ $string = str_replace('##CODE'.$key.'##', '<span class="code">'.$match.'</span>', $string); } }

    // -- REPLACE LINE BREAKS -- //

    // Change line breaks to actual breaks by grouping into paragraphs
    $string = str_replace("\n", '<br />', $string);
    $string = preg_replace('/<div( class="[^"]+")?( style="[^"]+")?><br \/>/is', '<div$1$2>', $string);
    $string = preg_replace('/<br \/><\/div>/is', '</div>', $string);
    //$string = preg_replace('<br /><', '<', $string);
    $string = '<div>'.$string.'</div>';


    // If we'e in HTTPS mode, manually rewrite common external sources with proper protocols
    if (defined('MMRPG_IS_HTTPS')
        && MMRPG_IS_HTTPS === true){
        // Replace any internal links with HTTPS urls
        $string = preg_replace('/http:\/\/((?:[a-z0-9]+)\.mmrpg-world.net\/)/i', 'https://$1', $string);
        // Replace any photobucket links with HTTPS urls
        $string = preg_replace('/http:\/\/((?:[a-z0-9]+)\.photobucket\.com\/)/i', 'https://$1', $string);
        // Replace any other image links with HTTPS urls
        if (preg_match_all('/src="http:\/\/((?:[-_a-z0-9\.\/\+]+)\.(?:jpg|jpeg|png|ico|bmp|svg)(?:\?(.*)?)?)"/i', $string, $matches)){
            $proxy_script = MMRPG_CONFIG_ROOTURL.'scripts/imageproxy.php';
            foreach ($matches[1] AS $key => $src){
                $find = $matches[0][$key];
                $src_encoded = urlencode($src);
                $src_hash = md5(MMRPG_SETTINGS_IMAGEPROXY_SALT . $src);
                $replace = 'src="'.$proxy_script.'?url='.$src_encoded.'&hash='.$src_hash.'"';
                $string = str_replace($find, $replace, $string);
            }
        }
    }

    // Return the decoded string
    return $string;

}
// Define a function for encoding and HTML string with formatting code
function mmrpg_formatting_encode($string){
    // Pull in the global index formatting variables
    $mmrpg_encoding_find = array(
        '/<strong>(.*?)<\/strong>/i', // bold
        '/<em>(.*?)<\/em>/i',  // italic
        '/<u>(.*?)<\/u>/i',  // underline
        '/<del>(.*?)<\/del>/i',  // strike
        '/\[code\]\s?(.*?)\s?\[\/code\]\s?/is',  // code
        '/<a href="([^"]+)"\s?(?:target="_blank")?>(.*?)<\/a>/i',  // link
        '/&quote;/i',  // quote
        '/&amp;/i',  // amp
        '/<span class="pipe">|<\/span>/i'  // amp
        );
    $mmrpg_encoding_replace = array(
        '[b]$1[/b]', // bold
        '[i]$1[/i]',  // italic
        '[u]$1[/u]',  // underline
        '[s]$1[/s]',  // strike
        '[code]$1[/code]',  // code
        '[$2]($1)',  // link
        '"',  // quote
        '&',  // amp
        ' | '  // amp
        );
    // Loop through each find, and replace with the appropriate replacement
    foreach ($mmrpg_encoding_find AS $key => $find_pattern){
        $replace_pattern = $mmrpg_encoding_replace[$key];
        $string = preg_replace($find_pattern, $replace_pattern, $string);
    }
    // Strip any remaining HTML from the string
    $string = strip_tags($string);

    // Change line breaks to actual breaks by grouping into paragraphs
    $string = str_replace('<br />', "\r\n", $string);

    // Return the encoded string
    return $string;
}

// Define a function for printing out the formatting options in text
function mmrpg_formatting_help_markup(){
    //error_log('mmrpg_formatting_help_markup()');

    // Attempt to pull this markup from the cache first
    $cache_token = 'formatting-help';
    $cached_markup = cms_website::load_cached_markup('community', $cache_token);
    if (!empty($cached_markup)){ return $cached_markup; }

    // Start the output buffer and prepare to collect contents
    ob_start();
    // Include the website formatting text file for reference
    require(MMRPG_CONFIG_ROOTDIR.'functions/website_formatting.v2.php');
    // Collect the output buffer contents into a variable
    $this_formatting = ob_get_clean();
    $this_formatting = normalize_line_endings($this_formatting);
    $this_formatting = nl2br(mmrpg_formatting_decode($this_formatting));

    // Start the output buffer and prepare to collect contents
    ob_start();
    ?>
    <div class="community bodytext">
        <div class="formatting">
            <h3>Community Formatting Guide</h3>
            <div class="wrapper">
                <?= $this_formatting ?>
            </div>
        </div>
    </div>
    <?
    // Collect the output buffer contents into a variable
    $this_markup = ob_get_clean();
    cms_website::save_cached_markup('community', $cache_token, $this_markup);

    // Return the collected output buffer contents
    return $this_markup;

}

// Define a function for printing out the formatting options in text
function mmrpg_formatting_help($context = 'post'){

    // Generate and return markup for a community formatting link// Start the output buffer and prepare to collect contents
    ob_start();
    ?>
    <div class="community bodytext">
        <div class="formatting">
            <a class="link_inline" data-popup="community-formatting-help">+ Show Formatting Guide</a>
            <a class="link_inline" data-popup="community-formatting-preview">+ Preview <?= ucfirst($context) ?> Formatting</a>
        </div>
    </div>
    <?
    $this_markup = ob_get_clean();
    return $this_markup;

}

// Define a function for generating the number suffix
function mmrpg_number_suffix($value, $concatenate = true, $superscript = false){
    if (!is_numeric($value) || !is_int($value)){ return $value; }
    if (substr($value, -2, 2) == 11 || substr($value, -2, 2) == 12 || substr($value, -2, 2) == 13){ $suffix = "th"; }
    else if (substr($value, -1, 1) == 1){ $suffix = "st"; }
    else if (substr($value, -1, 1) == 2){ $suffix = "nd"; }
    else if (substr($value, -1, 1) == 3){ $suffix = "rd"; }
    else { $suffix = "th"; }
    if ($superscript){ $suffix = "<sup>".$suffix."</sup>"; }
    if ($concatenate){ return $value.$suffix; }
    else { return $suffix; }
}

// Define a function for printout out currently online or viewing players
function mmrpg_website_print_online($this_leaderboard_online_players = array(), $filter_userids = array()){
    if (empty($this_leaderboard_online_players)){ $this_leaderboard_online_players = mmrpg_prototype_leaderboard_online(); }
    ob_start();
    foreach ($this_leaderboard_online_players AS $key => $info){
        if (!empty($filter_userids) && !in_array($info['id'], $filter_userids)){ continue; }
        if (empty($info['image'])){ $info['image'] = 'robots/mega-man/40'; }
        list($path, $token, $size) = explode('/', $info['image']);
        $frame = $info['placeint'] <= 3 ? 'victory' : 'base';
        if ($key > 0 && $key % 5 == 0){ echo '<br />'; }
        echo ' <a data-playerid="'.$info['id'].'" class="player_type player_type_'.$info['colour'].'" href="leaderboard/'.$info['token'].'/" style="text-decoration: none; line-height: 20px; padding-right: 12px; margin: 0 0 0 6px;">';
            echo '<span style="pointer-events: none; display: inline-block; width: 34px; height: 14px; position: relative;"><span class="sprite sprite_'.$size.'x'.$size.' sprite_'.$size.'x'.$size.'_'.$frame.'" style="margin: 0; position: absolute; left: '.($size == 40 ? -4 : -26).'px; bottom: 0; background-image: url(images/'.$path.'/'.$token.'/sprite_left_'.$size.'x'.$size.'.png?'.MMRPG_CONFIG_CACHE_DATE.');">&nbsp;</span></span>';
            echo '<span style="vertical-align: top; line-height: 18px;">'.strip_tags($info['place']).' : '.$info['name'].'</span>';
        echo '</a>';
    }
    $temp_markup = ob_get_clean();
    return $temp_markup;
}

// Define a function for collecting active sessions, optionally filtered by page
function mmrpg_website_sessions_active($session_href = '', $session_timeout = 3, $strict_filtering = false){

    // Import required global variables
    global $db, $this_userid;

    // Pull all active sessions from the DB if not done so already
    static $saved_active_sessions = false;
    if ($saved_active_sessions === false){
        $this_time = time();
        $min_time = strtotime('-'.$session_timeout.' minutes', $this_time);
        $saved_active_sessions = $db->get_array_list("SELECT
            DISTINCT user_id,
            session_href
            FROM mmrpg_sessions
            WHERE session_access >= {$min_time}
            ORDER BY session_access ASC
            ;", 'user_id');
        if (empty($saved_active_sessions)){ $saved_active_sessions = array(); }
    }

    // Clone the saved active sessions arrray, then filter
    $active_sessions = !empty($saved_active_sessions) ? $saved_active_sessions : array();
    if (!empty($active_sessions) && !empty($session_href)){
        if ($strict_filtering){ $session_href_pattern = '/^'.str_replace("/", "\/", rtrim($session_href, '/')).'\/$/i'; }
        else { $session_href_pattern = '/^'.str_replace("/", "\/", rtrim($session_href, '/')).'\//i'; }
        foreach ($active_sessions As $key => $session){
            if (!preg_match($session_href_pattern, $session['session_href'])){
                unset($active_sessions[$key]);
            }
        }
    }

    // Return the active session count if not empty
    return !empty($active_sessions) ? $active_sessions : array();
}

// Define a function for updating a user's session in the database
function mmrpg_website_session_update($session_href){
    // Import required global variables
    global $db, $this_userid;
    // Collect the session ID from the system
    $session_key = session_id();
    // Attempt to collect the current database row if it exists
    $temp_session = $db->get_array("SELECT
        session_id, user_id,
        session_key, session_href,
        session_start, session_access,
        session_ip
        FROM mmrpg_sessions
        WHERE
        user_id = '{$this_userid}'
        AND session_key = '{$session_key}'
        AND session_href = '{$session_href}'
        LIMIT 1
        ;");
    // If an existing session for this page was found, update it
    if (!empty($temp_session['session_id'])){
        $update_array = array();
        $update_array['session_href'] = $session_href;
        $update_array['session_access'] = time();
        $update_array['session_ip'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $db->update('mmrpg_sessions', $update_array, array('session_id' => $temp_session['session_id']));
    }
    // Else if first visit to this page during this session, insert it
    else {
        $insert_array = array();
        $insert_array['user_id'] = $this_userid;
        $insert_array['session_key'] = $session_key;
        $insert_array['session_href'] = $session_href;
        $insert_array['session_start'] = time();
        $insert_array['session_access'] = time();
        $insert_array['session_ip'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $db->insert('mmrpg_sessions', $insert_array);
    }
    // Return true on success
    return true;
}

// Define a function for collecting (and storing) data about the website categories
function mmrpg_website_community_index(){
    global $db;
    // Check to see if the community category has already been pulled or not
    $cache_token = md5(MMRPG_BUILD);
    $cached_index = rpg_object::load_cached_index('community.categories', $cache_token);
    if (!empty($cached_index)){
        $this_categories_index = $cached_index;
        unset($cached_index);
    } else {
        // Collect the community catetories from the database
        // Collect all the categories from the index
        $this_categories_query = "SELECT
            category_id, category_level,
            category_name, category_token, category_description,
            category_published, category_order
            FROM mmrpg_categories AS categories
            WHERE
            categories.category_published = 1
            ORDER BY
            categories.category_order ASC
            ;";
        $this_categories_index = $db->get_array_list($this_categories_query, 'category_token');
        rpg_object::save_cached_index('community.categories', $cache_token, $this_categories_index);
    }
    // Return the collected community categories
    return $this_categories_index;
}

// Define a function for selecting a list of threads in a category
function mmrpg_website_community_category_threads($this_category_info, $filter_locked = false, $filter_recent = false, $row_limit = false, $row_offset = false, $filter_ids = array()){

    // Pull in global variables
    global $db, $this_userinfo;

    // Collect the recently updated posts for this player / guest
    if (!rpg_user::is_guest()){ $temp_last_login = $this_userinfo['user_backup_login']; }
    else { $temp_last_login = time() - MMRPG_SETTINGS_UPDATE_TIMEOUT; }
    $temp_user_id = !rpg_user::is_guest() ? rpg_user::get_current_userid() : MMRPG_SETTINGS_GUEST_ID;

    // Define the ORDER BY string based on category key
    if ($this_category_info['category_token'] != 'news'){ $temp_order_by = 'threads.thread_sticky DESC, threads.thread_mod_date DESC, threads.thread_date DESC'; }
    else { $temp_order_by = 'threads.thread_sticky DESC, threads.thread_date DESC'; }

    // Define the extra WHERE string based on arguments
    $temp_where_filter = array();

    // Only return threads in this category
    $temp_where_filter[] = "threads.category_id = {$this_category_info['category_id']}";

    // Only return published threads
    $temp_where_filter[] = "threads.thread_published = 1";

    // Append to where query if locked threads should be excluded
    if ($filter_locked == true){ $temp_where_filter[] = "threads.thread_locked = 0"; }

    // Append to where query if a list of filter IDs has been provided
    if (!empty($filter_ids)){ $temp_where_filter[] = "threads.thread_id NOT IN(".implode(',', $filter_ids).")"; }

    // Append to where query if filtering by recent threads only
    if ($filter_recent == true){
        $temp_where_filter[] = "threads.thread_mod_date > {$temp_last_login}";
        $temp_where_filter[] = "threads.thread_mod_user <> {$temp_user_id}";
    }

    // Append a default to the where query if empty
    if (empty($temp_where_filter)){
        $temp_where_filter[] = "1 = 1";
    }

    // Implode generated where query into a string
    $temp_where_filter = implode(" AND \n", $temp_where_filter)."\n";

    // If a row limit has been defined, generate the string for it
    $temp_limit_string = '';
    if (!empty($row_limit) && is_numeric($row_limit)){ $temp_limit_string .= "LIMIT {$row_limit} "; }
    if (!empty($row_offset) && is_numeric($row_offset)){ $temp_limit_string .= "OFFSET {$row_offset} "; }

    // Generate the query for collecting discussion threads for a given category
    $this_threads_query = "SELECT

        threads.thread_id,
        threads.category_id,
        threads.user_id,
        threads.user_ip,
        threads.thread_name,
        threads.thread_token,
        threads.thread_body,
        threads.thread_frame,
        threads.thread_colour,
        threads.thread_date,
        threads.thread_mod_date,
        threads.thread_mod_user,
        threads.thread_published,
        threads.thread_locked,
        threads.thread_sticky,
        threads.thread_views,
        threads.thread_votes,
        threads.thread_target,

        users.user_id,
        users.user_name,
        users.user_name_public,
        users.user_name_clean,
        users.user_background_path,
        users.user_colour_token,
        users.user_image_path,
        users.user_date_modified,
        users.user_flag_postpublic,

        users2.mod_user_id,
        users2.mod_user_name,
        users2.mod_user_name_public,
        users2.mod_user_name_clean,
        users2.mod_user_background_path,
        users2.mod_user_colour_token,
        users2.mod_user_image_path,
        users2.mod_user_flag_postpublic,

        posts.post_count,
        posts_new.new_post_count

        FROM mmrpg_threads AS threads

        LEFT JOIN mmrpg_users AS users
            ON threads.user_id = users.user_id

        LEFT JOIN mmrpg_roles AS roles
            ON roles.role_id = users.role_id

        LEFT JOIN (
            SELECT
            user_id AS mod_user_id,
            user_name AS mod_user_name,
            user_name_clean AS mod_user_name_clean,
            user_name_public AS mod_user_name_public,
            user_colour_token AS mod_user_colour_token,
            user_image_path AS mod_user_image_path,
            user_background_path AS mod_user_background_path,
            user_flag_postpublic AS mod_user_flag_postpublic
            FROM mmrpg_users
            ) AS users2
            ON threads.thread_mod_user = users2.mod_user_id

        LEFT JOIN (
            SELECT
            posts.thread_id,
            count(posts.thread_id) AS post_count
            FROM mmrpg_posts AS posts
            WHERE posts.category_id = {$this_category_info['category_id']} AND posts.post_deleted = 0
            GROUP BY posts.thread_id
            ) AS posts
            ON threads.thread_id = posts.thread_id

          LEFT JOIN (
            SELECT
            posts2.thread_id,
            posts2.post_mod,
            count(posts2.thread_id) AS new_post_count
            FROM mmrpg_posts AS posts2
            WHERE posts2.category_id = {$this_category_info['category_id']} AND posts2.post_deleted = 0 AND posts2.post_mod > {$temp_last_login}
            GROUP BY posts2.thread_id
            ) AS posts_new
            ON threads.thread_id = posts_new.thread_id

        WHERE
            {$temp_where_filter} AND
            (threads.thread_target = 0 OR
                threads.thread_target = {$temp_user_id} OR
                threads.user_id = {$temp_user_id}
                )

        ORDER BY
            threads.thread_locked ASC,
            {$temp_order_by}

        {$temp_limit_string}

            ;";

    // Collect all the threads for this category from the database
    //exit($this_threads_query);
    $this_threads_array = $db->get_array_list($this_threads_query);

    // Return the threads array if not empty
    return !empty($this_threads_array) ? $this_threads_array : array();

}

// Define a function for selecting a list of threads in a category
function mmrpg_website_community_category_threads_count($this_category_info, $filter_locked = false, $filter_recent = false, $filter_ids = array()){

    // Pull in global variables
    global $db, $this_userinfo;

    // Collect the recently updated posts for this player / guest
    if (!rpg_user::is_guest()){ $temp_last_login = $this_userinfo['user_backup_login']; }
    else { $temp_last_login = time() - MMRPG_SETTINGS_UPDATE_TIMEOUT; }
    $temp_user_id = !rpg_user::is_guest() ? rpg_user::get_current_userid() : MMRPG_SETTINGS_GUEST_ID;

    // Define the ORDER BY string based on category key
    if ($this_category_info['category_token'] != 'news'){ $temp_order_by = 'threads.thread_sticky DESC, threads.thread_mod_date DESC, threads.thread_date DESC'; }
    else { $temp_order_by = 'threads.thread_sticky DESC, threads.thread_date DESC'; }

    // Define the extra WHERE string based on arguments
    $temp_where_filter = array();

    // Append to where query if locked threads should be excluded
    if ($filter_locked == true){
        $temp_where_filter[] = "threads.thread_locked = 0";
    }
    // Append to where query if filtering by recent threads only
    if ($filter_recent == true){
        $temp_where_filter[] = "threads.thread_mod_date > {$temp_last_login}";
        $temp_where_filter[] = "threads.thread_mod_user <> {$temp_user_id}";
    }
    // Append to where query if a list of filter IDs has been provided
    if (!empty($filter_ids)){
        $temp_where_filter[] = "threads.thread_id NOT IN(".implode(',', $filter_ids).")";
    }
    // Append a default to the where query if empty
    if (empty($temp_where_filter)){
        $temp_where_filter[] = "1 = 1";
    }

    // Implode generated where query into a string
    $temp_where_filter = implode(" AND \n", $temp_where_filter)."\n";

    // Generate the query for collecting discussion threads for a given category
    $this_threads_query = "SELECT

        COUNT(threads.thread_id) AS thread_count

        FROM mmrpg_threads AS threads

        LEFT JOIN mmrpg_users AS users
            ON threads.user_id = users.user_id

        LEFT JOIN mmrpg_roles AS roles
            ON roles.role_id = users.role_id

        LEFT JOIN (
            SELECT
            user_id AS mod_user_id,
            user_name AS mod_user_name,
            user_name_clean AS mod_user_name_clean,
            user_name_public AS mod_user_name_public,
            user_colour_token AS mod_user_colour_token,
            user_image_path AS mod_user_image_path,
            user_background_path AS mod_user_background_path,
            user_flag_postpublic AS mod_user_flag_postpublic
            FROM mmrpg_users
            ) AS users2
            ON threads.thread_mod_user = users2.mod_user_id

        LEFT JOIN (
            SELECT
            posts.thread_id,
            count(posts.thread_id) AS post_count
            FROM mmrpg_posts AS posts
            WHERE posts.category_id = {$this_category_info['category_id']} AND posts.post_deleted = 0
            GROUP BY posts.thread_id
            ) AS posts
            ON threads.thread_id = posts.thread_id

          LEFT JOIN (
            SELECT
            posts2.thread_id,
            posts2.post_mod,
            count(posts2.thread_id) AS new_post_count
            FROM mmrpg_posts AS posts2
            WHERE posts2.category_id = {$this_category_info['category_id']} AND posts2.post_deleted = 0 AND posts2.post_mod > {$temp_last_login}
            GROUP BY posts2.thread_id
            ) AS posts_new
            ON threads.thread_id = posts_new.thread_id

        WHERE
            threads.category_id = {$this_category_info['category_id']} AND
            threads.thread_published = 1 AND
            (threads.thread_target = 0 OR
                threads.thread_target = {$temp_user_id} OR
                threads.user_id = {$temp_user_id}
                ) AND
            {$temp_where_filter}

        ORDER BY
            threads.thread_locked ASC,
            {$temp_order_by}

            ;";

    // Collect all the threads for this category from the database
    $this_threads_array = $db->get_array($this_threads_query);

    // Return the threads array if not empty
    return !empty($this_threads_array['thread_count']) ? $this_threads_array['thread_count'] : 0;

}

// Define a function for generating a community thread block
function mmrpg_website_community_thread_linkblock($this_thread_key, $this_thread_info, $header_mode = false, $compact_mode = false, $show_category = false){

    // Pull in global variables necessary for the linkblock
    global $this_userinfo, $this_category_info;

    // Merge the category info back into the thread info
    $this_thread_info = array_merge($this_thread_info, $this_category_info);

    // Collect or define the temporary timeout variables
    global $this_time, $this_online_timeout;
    if (empty($this_time)){ $this_time = time(); }
    if (empty($this_online_timeout)){ $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT; }

    // Assign a static variable for the date group token
    static $this_date_group = '';

    // Start the output buffer and start generating markup
    ob_start();
    $this_markup = '';

        // Define this thread's session tracker token
        $temp_session_token = $this_thread_info['thread_id'].'_';
        $temp_session_token .= !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $this_thread_info['thread_date'];
        // Check if this thread has already been viewed this session
        $temp_session_viewed = in_array($temp_session_token, $_SESSION['COMMUNITY']['threads_viewed']) ? true : false;

        // Update the temp date group if necessary
        $temp_thread_date = !empty($this_thread_info['thread_date']) ? $this_thread_info['thread_date'] : mktime(0, 0, 1, 1, 1, 2011);
        $temp_thread_mod_date = !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $temp_thread_date;

        // Check if this thread's date group is different then previous
        $temp_date_group = $this_thread_info['category_token'] == 'news' ? date('Y-m', $temp_thread_date) : date('Y-m', $temp_thread_mod_date);
        if (!empty($this_thread_info['thread_locked'])){ $temp_date_group = 'locked'; }
        elseif (!empty($this_thread_info['thread_sticky'])){ $temp_date_group = 'sticky'; }

        // If the date group was different, update the static variable
        if ($temp_date_group != $this_date_group){
            $this_date_group = $temp_date_group;
            /*
            // ONly show group separators if not in header mode
            if (!$header_mode){
                if ($temp_date_group == 'locked'){
                    echo '<h3 id="date-'.$temp_date_group.'" data-group="'.$temp_date_group.'" class="subheader category_date_group" style="color: #464646;">Locked Threads</h3>';
                } elseif ($temp_date_group == 'sticky'){
                    echo '<h3 id="date-'.$temp_date_group.'" data-group="'.$temp_date_group.'" class="subheader category_date_group">Sticky Threads</h3>';
                } else {
                    echo '<h3 id="date-'.$temp_date_group.'" data-group="'.$temp_date_group.'" class="subheader category_date_group">'.date('F Y', $temp_thread_mod_date).'</h3>';
                }
            }
            */
        }

        // Define the temporary display variables
        $temp_category_id = $this_thread_info['category_id'];
        $temp_category_token = $this_thread_info['category_token'];
        $temp_thread_id = $this_thread_info['thread_id'];
        $temp_thread_token = $this_thread_info['thread_token'];
        $temp_thread_name = $this_thread_info['thread_name'];
        $temp_thread_author = !empty($this_thread_info['user_name_public']) && !empty($this_thread_info['user_flag_postpublic']) ? $this_thread_info['user_name_public'] : $this_thread_info['user_name'];
        $temp_thread_author_colour = !empty($this_thread_info['user_colour_token']) ? $this_thread_info['user_colour_token'] : 'none';
        $temp_thread_date = date('F jS, Y', $temp_thread_date).' at '.date('g:ia', $temp_thread_date);
        $temp_thread_mod_user = !empty($this_thread_info['mod_user_name_public']) && !empty($this_thread_info['mod_user_flag_postpublic']) ? $this_thread_info['mod_user_name_public'] : $this_thread_info['mod_user_name'];
        $temp_thread_mod_date = !empty($this_thread_info['thread_mod_date']) && $this_thread_info['thread_mod_date'] != $this_thread_info['thread_date'] ? $this_thread_info['thread_mod_date'] : false;
        $temp_thread_mod_date = !empty($temp_thread_mod_date) ? 'Updated by '.$temp_thread_mod_user : false;
        $temp_thread_body = strlen($this_thread_info['thread_body']) > 255 ? substr($this_thread_info['thread_body'], 0, 255).'&hellip;' : $this_thread_info['thread_body'];
        $temp_posts_count = !empty($this_thread_info['post_count']) ? $this_thread_info['post_count'] : 0;
        $temp_thread_timestamp = !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $this_thread_info['thread_date'];
        $temp_thread_link = 'community/'.$temp_category_token.'/'.$temp_thread_id.'/'.$temp_thread_token.'/';

        // Check if this is a system thread
        if ($temp_category_token == 'personal' && empty($this_thread_info['user_id'])){ $is_system_thread = true; }
        else { $is_system_thread = false; }

        // If this was a system thread, alter values
        if ($is_system_thread){
            $temp_thread_author = 'System Bot';
            $temp_thread_author_colour = 'empty';
        }

        // If there are comments, update link to point to last page
        $temp_comments_perpage = $is_system_thread ? 1 : MMRPG_SETTINGS_POSTS_PERPAGE;
        $temp_thread_link_comments = $temp_thread_link;
        if ($temp_posts_count >= $temp_comments_perpage){
            $temp_posts_pages_max = ceil($temp_posts_count / $temp_comments_perpage);
            $temp_thread_link_comments .= $temp_posts_pages_max.'/';
        }
        $temp_thread_link_comments .= !empty($temp_posts_count) ? '#comment-listing' : '#comment-form';

        // Define the target option text
        $temp_target_thread_author = '?';
        $temp_target_thread_author_colour = 'none';
        if ($temp_category_token == 'personal'){
            $temp_target_thread_author = !empty($this_userinfo['user_name_public']) && !empty($this_userinfo['user_flag_postpublic']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name'];
            $temp_target_thread_author_colour = !empty($this_userinfo['user_colour_token']) ? $this_userinfo['user_colour_token'] : 'none';
        }

        // Define if this post is new to the logged in user or not
        $temp_is_new = false;
        // Supress the new flag if thread has already been viewed
        if (!$temp_session_viewed){
            if (!rpg_user::is_guest()
                //&& $this_thread_info['user_id'] != $this_userinfo['user_id']
                && $this_thread_info['thread_mod_user'] != $this_userinfo['user_id']
                && $temp_thread_timestamp > $this_userinfo['user_backup_login']){
                $temp_is_new = true;
            } elseif (rpg_user::is_guest()
                && (($this_time - $temp_thread_timestamp) <= MMRPG_SETTINGS_UPDATE_TIMEOUT)){
                $temp_is_new = true;
            }
        }

        // If this thread is excessivly old, is not sticky, and has not been replied to for a while, lock it
        if (!$this_thread_info['thread_sticky']){
            if ($this_category_info['category_token'] === 'news'){ $last_mod_date = $this_thread_info['thread_date']; }
            else { $last_mod_date = !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $this_thread_info['thread_date']; }
            $thread_time_inactive = time() - $last_mod_date;
            if ($thread_time_inactive >= MMRPG_SETTINGS_LEGACY_TIMEOUT){
                $this_thread_info['thread_locked'] = true;
            }
        }

        // Collect the defined thread colour if set, otherwise (if personal messages) use the author colour
        $temp_thread_colour = !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : 'none';
        if ($temp_category_token == 'personal'
            && $temp_thread_colour === 'none'
            && $temp_thread_author_colour !== 'none'){
            $temp_thread_colour = $temp_thread_author_colour;
        }

        ?>
        <div id="thread-<?= $temp_thread_id ?>" data-group="<?= $temp_date_group ?>" class="subbody thread_subbody thread_subbody_small <?= $header_mode ? 'thread_subbody_small_nohover' : '' ?> <?= $temp_date_group == 'sticky' ? 'thread_is_sticky' : '' ?> <?= $compact_mode ? 'thread_subbody_compact' : '' ?> thread_right field_type_<?= $temp_thread_colour ?>">
            <?
            // If this thread has a specific target, display their avatar to the right
            if ($this_thread_info['thread_target'] != 0){

                // Define the avatar class and path variables
                $temp_avatar_float = $this_thread_info['user_id'] == $this_userinfo['user_id'] ? 'left' : 'right';
                $temp_avatar_direction = $temp_avatar_float == 'left' ? 'right' : 'left';
                $temp_avatar_frame = $this_thread_info['user_id'] != $this_thread_info['thread_target'] && !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
                $temp_avatar_path = !empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
                $temp_background_path = !empty($this_thread_info['user_background_path']) ? $this_thread_info['user_background_path'] : 'fields/gentle-countryside';
                if ($is_system_thread){ $temp_avatar_path = 'robots/robot/40'; $temp_background_path = 'fields/field'; }
                list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
                if ($temp_avatar_kind === 'players' && (int)($temp_avatar_frame) > 6){ $temp_avatar_frame = '06'; }
                list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
                $temp_avatar_class = 'avatar avatar_40x40 float float_'.$temp_avatar_float.' ';
                $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
                $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.$temp_avatar_direction.'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;

                ?>
                <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 60px 60px;">&nbsp;</div>
                <div class="<?= $temp_avatar_class ?> avatar_userimage avatar_userimage_<?= $temp_avatar_float ?>"><div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE ?>);"><?= $temp_thread_author ?></div></div>
                <?

                // Define the avatar class and path variables
                //$temp_avatar_frame = '00';
                $temp_avatar_float = $temp_avatar_float == 'left' ? 'right' : 'left';
                $temp_avatar_direction = $temp_avatar_float == 'left' ? 'right' : 'left';
                $temp_avatar_frame =  $this_thread_info['user_id'] == $this_thread_info['thread_target'] && !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
                $temp_avatar_path = !empty($this_thread_info['target_user_image_path']) ? $this_thread_info['target_user_image_path'] : 'robots/mega-man/40';
                $temp_background_path = !empty($this_thread_info['target_user_background_path']) ? $this_thread_info['target_user_background_path'] : 'fields/gentle-countryside';
                //if ($is_system_thread){ $temp_avatar_path = 'robots/robot/40'; $temp_background_path = 'fields/field'; }
                list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
                if ($temp_avatar_kind === 'players' && (int)($temp_avatar_frame) > 6){ $temp_avatar_frame = '06'; }
                list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
                $temp_avatar_class = 'avatar avatar_40x40 float float_'.$temp_avatar_float.' ';
                $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
                $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.$temp_avatar_direction.'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;

                ?>
                <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 60px 60px;">&nbsp;</div>
                <div class="<?= $temp_avatar_class ?> avatar_userimage avatar_userimage_<?= $temp_avatar_float ?>"><div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE ?>);"><?= $temp_thread_author ?></div></div>
                <?
            }
            // Otherwise if this is a totally normal community post
            else {

                // Define the avatar class and path variables
                $temp_avatar_float = 'left';
                $temp_avatar_direction = 'right';
                $temp_avatar_frame = !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
                $temp_avatar_path = !empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
                $temp_background_path = !empty($this_thread_info['user_background_path']) ? $this_thread_info['user_background_path'] : 'fields/gentle-countryside';
                list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
                list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
                $temp_avatar_class = 'avatar avatar_40x40 float float_'.$temp_avatar_float.' ';
                $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
                $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.$temp_avatar_direction.'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;

                ?>
                <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 60px 60px;">&nbsp;</div>
                <div class="<?= $temp_avatar_class ?> avatar_userimage avatar_userimage_<?= $temp_avatar_float ?>"><div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE ?>);"><?= $temp_thread_author ?></div></div>
                <?

            }

            ?>
            <?= !empty($this_thread_info['thread_sticky']) ? '<i class="icon sticky fa fas fa-thumbtack" title="Thread is Sticky"></i>' : '' ?>
            <?= !empty($this_thread_info['thread_locked']) ? '<i class="icon locked fa fas fa-lock" title="Thread is Locked"></i>' : '' ?>
            <div class="text thread_linkblock thread_linkblock_<?= $this_thread_info['thread_target'] != 0 && $this_thread_info['user_id'] != $this_userinfo['user_id'] ? 'right' : 'left' ?>">
                <? if ($show_category){ ?>
                    <div class="link" href="<?= $temp_thread_link ?>">
                        <a href="<?= 'community/'.$temp_category_token.'/' ?>"><?= ucfirst($this_category_info['category_token']) ?></a> &raquo;
                        <a href="<?= $temp_thread_link ?>"><?= $temp_thread_name ?></a>
                    </div>
                <? } else { ?>
                    <a class="link" href="<?= $temp_thread_link ?>">
                        <span><?= $temp_thread_name ?></span>
                    </a>
                <? } ?>
                <div class="info">
                    <strong class="player_type player_type_<?= $temp_thread_author_colour ?>"><?= $temp_thread_author ?></strong>
                    <?= $this_thread_info['thread_target'] != 0 ? 'to <strong class="player_type player_type_'.$temp_target_thread_author_colour.'">'.$temp_target_thread_author.'</strong>' : '' ?>
                    on <strong><?= $temp_thread_date ?></strong>
                </div>
                <div class="count" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <a class="comments <?= !empty($temp_posts_count) ? 'field_type field_type_none' : '' ?>" href="<?= $temp_thread_link_comments ?>">
                        <?= !empty($temp_posts_count) ? ($temp_posts_count == 1 ? '1 Comment' : $temp_posts_count.' Comments ') : 'No Comments ' ?>
                    </a>
                    <?= $temp_is_new ? '<strong class="newpost field_type field_type_electric">New!</strong> ' : '' ?>
                    <?= !empty($temp_thread_mod_date) ? '<span class="newpost author">'.$temp_thread_mod_date.'</span> ' : '' ?>
                </div>
            </div>
        </div>
        <?

    // Collect the markup for this link block and return
    $this_markup = trim(ob_get_clean());
    return $this_markup;

}


// Define a function for printing out a community post block given its info
function mmrpg_website_community_postblock($this_post_key, $this_post_info, $this_thread_info, $this_category_info = array(), $display_style = 'full'){

    // Pull in global variables
    global $this_userid, $this_userinfo;
    global $this_date_group, $this_time, $this_online_timeout, $community_battle_points;
    global $temp_leaderboard_online, $thread_session_viewed, $this_post_key;
    if (empty($community_battle_points)){ $community_battle_points = 0; }

    // Start the output buffer
    ob_start();

    // If category info was not provided
    if (empty($this_date_group)){ $this_date_group = ''; }
    if (empty($this_time)){ $this_time = time(); }
    if (empty($this_online_timeout)){ $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT; }
    if (empty($this_category_info)){ $this_category_info = array('category_token' => ''); }

    // Check to see if this is a message thread, and then if being viewed by creator
    $is_personal_message = !empty($this_thread_info['thread_target']) && $this_thread_info['thread_target'] != 0 ? true : false;
    $is_personal_message_creator = $is_personal_message && $this_thread_info['user_id'] == $this_userinfo['user_id'] ? true : false;

    // If this is a personal message, we should check stuff
    if ($is_personal_message){
        if ($this_post_info['user_id'] != $this_userinfo['user_id']
            && $this_post_info['post_target'] != $this_userinfo['user_id']){
                return;
            }
    }

    // Define this post's overall float direction based on if PM
    $this_post_float = 'left';
    $this_post_direction = 'right';
    if ($this_post_info['post_target'] == $this_userinfo['user_id']){
        $this_post_float = 'right';
        $this_post_direction = 'left';
    }

    // Define the temporary display variables
    $temp_post_guest = $this_post_info['user_id'] == MMRPG_SETTINGS_GUEST_ID ? true : false;
    $temp_post_author = !empty($this_post_info['user_name_public']) ? $this_post_info['user_name_public'] : $this_post_info['user_name'];
    $temp_post_date = !empty($this_post_info['post_date']) ? $this_post_info['post_date'] : mktime(0, 0, 1, 1, 1, 2011);
    $temp_post_date = date('F jS, Y', $temp_post_date).' at '.date('g:ia', $temp_post_date);
    $temp_post_mod = !empty($this_post_info['post_mod']) && $this_post_info['post_mod'] != $this_post_info['post_date'] ? $this_post_info['post_mod'] : false;
    $temp_post_mod = !empty($temp_post_mod) ? '( Edited : '.date('Y/m/d', $temp_post_mod).' at '.date('g:ia', $temp_post_mod).' )' : false;
    $temp_post_body = $this_post_info['post_body'];
    $temp_post_title = '#'.$this_post_info['user_id'].' : '.$temp_post_author;
    $temp_post_timestamp = !empty($this_post_info['post_mod']) ? $this_post_info['post_mod'] : $this_post_info['post_date'];

    // Define the avatar class and path variables
    $temp_avatar_frame = !empty($this_post_info['post_frame']) ? $this_post_info['post_frame'] : '00';
    $temp_avatar_path = !empty($this_post_info['user_image_path']) ? $this_post_info['user_image_path'] : 'robots/mega-man/40';
    $temp_background_path = !empty($this_post_info['user_background_path']) ? $this_post_info['user_background_path'] : 'fields/intro-field';
    list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
    list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
    $temp_avatar_class = 'avatar avatar_40x40 float float_'.$this_post_float.' ';
    $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
    $temp_avatar_colour = !empty($this_post_info['user_colour_token']) ? $this_post_info['user_colour_token'] : 'none';
    $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.$this_post_direction.'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
    $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;

    $temp_is_contributor = in_array($this_post_info['role_token'], array('developer', 'administrator', 'moderator', 'contributor')) ? true : false;
    if ($temp_is_contributor){
        $temp_item_class = 'sprite sprite_40x40 sprite_40x40_00';
        $temp_item_path = 'images/items/'.(!empty($this_post_info['role_icon']) ? $this_post_info['role_icon'] : 'energy-pellet' ).'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $temp_item_title = !empty($this_post_info['role_name']) ? $this_post_info['role_name'] : 'Contributor';
    }

    // Define the temporary online variables
    $temp_last_modified = !empty($this_post_info['user_date_modified']) ? $this_post_info['user_date_modified'] : 0;
    // Check if the thread creator is currently online
    $temp_is_online = false;
    foreach ($temp_leaderboard_online AS $key => $info){ if ($info['id'] == $this_post_info['user_id']){ $temp_is_online = true; break; } }

    // Define if this post is new to the logged in user or not
    $temp_is_new = false;
    // Supress the new flag if thread has already been viewed
    if (!$thread_session_viewed && $this_category_info['category_id'] != 0){
        if ($this_userinfo['user_id'] != MMRPG_SETTINGS_GUEST_ID
            && $this_post_info['user_id'] != $this_userinfo['user_id']
            && $temp_post_timestamp > $this_userinfo['user_backup_login']){
            $temp_is_new = true;
        } elseif ($this_userinfo['user_id'] == MMRPG_SETTINGS_GUEST_ID
            && (($this_time - $temp_post_timestamp) <= MMRPG_SETTINGS_UPDATE_TIMEOUT)){
            $temp_is_new = true;
        }
    }

    // Collect the reply data for this user
    $temp_reply_name = $temp_post_author;
    $temp_reply_colour = !empty($this_post_info['user_colour_token']) ? $this_post_info['user_colour_token'] : 'none';

    ?>
    <div id="post-<?= $this_post_info['post_id'] ?>" data-key="<?= $this_post_key ?>" data-user="<?= $this_post_info['user_id'] ?>" title="<?= !empty($this_post_info['post_deleted']) ? ($temp_post_author.' on '.str_replace(' ', '&nbsp;', $temp_post_date)) : '' ?>" class="subbody post_subbody post_subbody_<?= $this_post_float ?> <?= !empty($this_post_info['post_deleted']) ? 'post_subbody_deleted' : '' ?> post_<?= $this_post_float ?>" style="<?= !empty($this_post_info['post_deleted']) ? 'margin-top: 0; padding: 0 10px; background-color: transparent; float: '.$this_post_float.'; ' : 'clear: '.$this_post_float.'; ' ?>">

        <? if(empty($this_post_info['post_deleted'])): ?>
            <div class="userblock player_type_<?= $temp_avatar_colour ?>">
                <div class="name">
                    <?= !$temp_post_guest ? '<a href="leaderboard/'.$this_post_info['user_name_clean'].'/">' : '' ?>
                    <strong data-tooltip-type="player_type player_type_<?= $temp_avatar_colour ?>" title="<?= $temp_post_author.($temp_is_contributor ? ' | '.$temp_item_title : ' | Player').($temp_is_online ? ' | Online' : '') ?>" style="<?= $temp_is_online ? 'text-shadow: 0 0 2px rgba(0, 255, 0, 0.20); ' : '' ?>"><?= $temp_post_author ?></strong>
                    <?= !$temp_post_guest ? '</a>' : '' ?>
                </div>
                <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 100px 100px;">
                    &nbsp;
                </div>
                <div class="<?= $temp_avatar_class ?> avatar_userimage" style="">
                    <?/*<div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/mega-man/sprite_left_40x40.png);"><?= $temp_thread_author ?></div>*/?>
                    <? if($temp_is_contributor): ?><div class="<?= $temp_item_class ?>" style="background-image: url(<?= $temp_item_path ?>); position: absolute; top: -10px; <?= $this_post_float ?>: -14px;" title="<?= $temp_item_title ?>"><?= $temp_item_title ?></div><? endif; ?>
                    <div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>);"><?= $temp_post_author ?></div>
                </div>

                <? $temp_stat = !empty($this_post_info['user_board_points']) ? $this_post_info['user_board_points'] : 0; ?>
                <div class="counter points_counter"><?= number_format($temp_stat, 0, '.', ',').' BP' ?></div>
                <div class="counter community_counters">
                    <? $temp_stat = !empty($this_post_info['user_thread_count']) ? $this_post_info['user_thread_count'] : 0; ?>
                    <span class="thread_counter"><?= $temp_stat.' TP' ?></span> <span class="pipe">|</span>
                    <? $temp_stat = !empty($this_post_info['user_post_count']) ? $this_post_info['user_post_count'] : 0; ?>
                    <span class="post_counter"><?= $temp_stat.' PP' ?></span>
                </div>

            </div>
            <div class="postblock">

                <? if (!empty($this_post_info['post_is_thread'])): ?>
                    <div class="subheader field_type field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : 'none' ?>" title="<?= $this_thread_info['thread_name'] ?>">
                        <a class="link" style="display: inline; float: none;" href="community/<?= $this_thread_info['category_token'] ?>/<?= $this_thread_info['thread_id'] ?>/<?= $this_thread_info['thread_token'] ?>/"><?= $this_thread_info['thread_name'] ?></a>
                    </div>
                <? endif; ?>

                <div class="published" title="<?= $temp_post_author.' on '.str_replace(' ', '&nbsp;', $temp_post_date) ?>">
                    <strong>
                        Posted on <?= $temp_post_date ?>
                        <? if(empty($this_post_info['post_is_thread']) && ($this_category_info['category_token'] == 'search' || $this_category_info['category_token'] == 'leaderboard')): ?>
                            in &quot;<a class="link" href="community/<?= $this_thread_info['category_token'] ?>/<?= $this_thread_info['thread_id'] ?>/<?= $this_thread_info['thread_token'] ?>/"><?= $this_thread_info['thread_name'] ?></a>&quot;
                        <? endif; ?>
                    </strong>

                    <? if (true || $this_category_info['category_token'] != 'search' && $this_category_info['category_token'] != 'leaderboard'): ?>
                        <span style="float: <?= $this_post_direction ?>; color: #565656; padding-left: 6px;">#<?= $this_post_key + 1 ?></span>
                    <? endif; ?>

                    <?= !empty($temp_post_mod) ? '<span style="padding-left: 20px; color: rgb(119, 119, 119); letter-spacing: 1px; font-size: 10px;">'.$temp_post_mod.'</span>' : '' ?>
                    <?= $temp_is_new ? '<strong style="padding-left: 10px; color: rgb(187, 184, 115); letter-spacing: 1px;">(New!)</strong>' : '' ?>
                    <? if(!$temp_post_guest && (COMMUNITY_VIEW_MODERATOR || $this_userinfo['user_id'] == $this_post_info['user_id'])): ?>
                        <? if($this_thread_info['thread_target'] == 0): ?>
                            <span class="options">[ <a class="edit" rel="noindex,nofollow" href="<?= $_GET['this_current_url'].'action=edit&amp;post_id='.$this_post_info['post_id'].'#comment-form' ?>">edit</a> | <a class="delete" rel="noindex,nofollow" href="<?= $_GET['this_current_url'] ?>" data-href="<?= $_GET['this_current_url'].'action=delete&amp;post_id='.$this_post_info['post_id'].'#comment-form' ?>">delete</a> ]</span>
                        <? endif; ?>
                    <? endif; ?>
                </div>

                <div class="bodytext">
                    <?= mmrpg_formatting_decode($temp_post_body) ?>
                </div>

            </div>
            <? if($this_userid != MMRPG_SETTINGS_GUEST_ID && empty($this_thread_info['thread_locked']) && $community_battle_points > MMRPG_SETTINGS_POST_MINPOINTS && $this_category_info['category_token'] != 'personal'): ?>
                <a class="postreply" rel="nofollow" href="<?= 'community/'.$this_category_info['category_token'].'/'.$this_thread_info['thread_id'].'/'.$this_thread_info['thread_token'].'/#comment-form:'.$temp_reply_name.':'.$temp_reply_colour ?>" style="<?= $this_post_direction ?>: 46px;">@ Reply</a>
            <? endif; ?>
            <a class="postscroll" href="#top" style="<?= $this_post_direction ?>: 12px;">^ Top</a>
        <? else: ?>
            <span style="color: #464646;">- deleted -</span>
        <? endif; ?>

        <?
        $this_post_votes = mt_rand(0, 99);
        if (mt_rand(0,1) == 0){ $this_post_votes = $this_post_votes * -1; }
        $this_post_votes_text = $this_post_votes;
        $this_post_votes_type = '';
        if ($this_post_votes > 0){
            $this_post_votes_text = '+'.$this_post_votes_text;
            $this_post_votes_type = 'positive';
        } elseif ($this_post_votes < 0){
            //$this_post_votes_text = '-'.$this_post_votes_text;
            $this_post_votes_type = 'negative';
        }
        /*
        <div class="postvotes">
            <a class="upvote <?= mt_rand(0,9) == 6 ? 'active' : '' ?>" href="#">&#x25B2;</a>
            <strong class="votes <?= $this_post_votes_type ?>"><?= $this_post_votes_text ?></strong>
            <a class="downvote <?= mt_rand(0,9) == 3 ? 'active' : '' ?>" href="#">&#x25BC;</a>
        </div>
         */
        ?>

    </div>
    <?

    // Collect and return the generated markup
    $temp_markup = trim(ob_get_clean());
    return $temp_markup;

}

// Get a list of all community thread fields as an array or, optionally, imploded into a string
function mmrpg_community_thread_index_fields($implode = false, $table = ''){

    // Define the various table fields for user objects
    $thread_fields = array(
        'thread_id',
        'category_id',
        'user_id',
        'user_ip',
        'thread_name',
        'thread_token',
        'thread_body',
        'thread_frame',
        'thread_colour',
        'thread_date',
        'thread_mod_date',
        'thread_mod_user',
        'thread_published',
        'thread_locked',
        'thread_sticky',
        'thread_views',
        'thread_votes',
        'thread_target'
        );

    // Add table name to each field string if requested
    if (!empty($table)){
        foreach ($thread_fields AS $key => $field){
            $thread_fields[$key] = $table.'.'.$field;
        }
    }

    // Implode the table fields into a string if requested
    if ($implode){
        $thread_fields = implode(', ', $thread_fields);
    }

    // Return the table fields, array or string
    return $thread_fields;

}

// Get a list of all community post fields as an array or, optionally, imploded into a string
function mmrpg_community_post_index_fields($implode = false, $table = ''){

    // Define the various table fields for user objects
    $post_fields = array(
        'post_id',
        'category_id',
        'thread_id',
        'user_id',
        'user_ip',
        'post_body',
        'post_frame',
        'post_date',
        'post_mod',
        'post_deleted',
        'post_votes',
        'post_target'
        );

    // Add table name to each field string if requested
    if (!empty($table)){
        foreach ($post_fields AS $key => $field){
            $post_fields[$key] = $table.'.'.$field;
        }
    }

    // Implode the table fields into a string if requested
    if ($implode){
        $post_fields = implode(', ', $post_fields);
    }

    // Return the table fields, array or string
    return $post_fields;

}

// Define a function for generating gallery image thumb + link markup
function mmrpg_get_gallery_thumb_markup($file_info, $file_date = '', $thumb_class = 'image', $thumb_rel = 'images', $base_path = ''){
    if (empty($file_date)){ $file_date = date('Y/m/d', $file_info['time']); }
    $markup = '';
    if (defined('MMRPG_CONFIG_CDN_ENABLED') && MMRPG_CONFIG_CDN_ENABLED === true){ $base_path = MMRPG_CONFIG_CDN_ROOTURL.$base_path; }
    $markup .= '<a class="'.$thumb_class.'" href="'.$base_path.$file_info['href'].'" target="_blank" rel="'.$thumb_rel.'">';
        $markup .= '<span class="wrap" style="background-image: url('.$base_path.$file_info['thumb'].');">';
            $markup .= '<img class="image" src="'.$base_path.$file_info['thumb'].'" alt="Mega Man RPG Prototype | '.$file_info['title'].'" />';
            $markup .= '<span class="title" title="'.$file_info['title'].'">'.$file_info['title'].'</span>';
            $markup .= '<span class="date">'.$file_date.'</span>';
        $markup .= '</span>';
    $markup .= '</a>'.PHP_EOL;
    return $markup;
}

// Define a function for getting the current favicon filename given environment
function mmrpg_get_favicon(){
    $filename = 'favicon';
    if (defined('MMRPG_CONFIG_SERVER_ENV') && MMRPG_CONFIG_SERVER_ENV !== 'prod'){ $filename .= '-'.MMRPG_CONFIG_SERVER_ENV; }
    $filename .= '.ico';
    return $filename;
}

// Define a function for initializing form messages
function mmrpg_init_form_messages(){
    $form_messages = array();
    if (!empty($_SESSION['mmrpg_forms']['form_messages'])){
        $form_messages = $_SESSION['mmrpg_forms']['form_messages'];
    }
    return $form_messages;
}

// Define a function for saving form messages to session
function mmrpg_backup_form_messages(){
    global $form_messages;
    $_SESSION['mmrpg_forms']['form_messages'] = $form_messages;
}

// Define a function for initializing form messages
function mmrpg_clear_form_messages(){
    global $form_messages;
    $form_messages = array();
    $_SESSION['mmrpg_forms']['form_messages'] = array();
}

// Define a function for generating form messages
function mmrpg_print_form_messages($print = true, $clear = true){
    global $form_messages;
    $this_message_markup = '';
    if (!empty($form_messages)){
        $this_message_markup .= '<ul class="list">'.PHP_EOL;
        foreach ($form_messages AS $key => $message){
            list($type, $text) = $message;
            $this_message_markup .= '<li class="message '.$type.'">';
                //$this_message_markup .= ucfirst($type).' : ';
                $this_message_markup .= $text;
            $this_message_markup .= '</li>'.PHP_EOL;
        }
        $this_message_markup .= '</ul>'.PHP_EOL;
    }
    if (!empty($this_message_markup)){
        $this_message_markup = '<div class="messages">'.$this_message_markup.'</div>';
    }
    if ($clear){ mmrpg_clear_form_messages(); }
    if ($print){ echo $this_message_markup; }
    else { return $this_message_markup; }
}

// Define a function for exiting a form action
function mmrpg_redirect_form_action($location){
    mmrpg_backup_form_messages();
    if (!empty($location)){
        if (!preg_match('/^https?:\/\//', $location)
            && !strstr($location, MMRPG_CONFIG_ROOTURL)){
            $location = MMRPG_CONFIG_ROOTURL.ltrim($location, '/');
        }
        header('Location: '.$location);
    }
    exit();
}

// Define a function for deleting a directory
function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("{$dirPath} must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

// Define a function for recursively making directories given a path (within base_path limit)
function recurseMakeDir($full_path, $base_path = ''){
    if (empty($base_path)){ $base_path = MMRPG_CONFIG_ROOTDIR; }
    elseif (!strstr($base_path, MMRPG_CONFIG_ROOTDIR)){ $base_path = MMRPG_CONFIG_ROOTDIR.ltrim($base_path, '/'); }
    $full_path = preg_replace('/\/+/', '/', $full_path);
    $base_path = preg_replace('/\/+/', '/', $base_path);
    $rel_path = str_replace($base_path, '', $full_path);
    $rel_path_parts = explode('/', trim($rel_path, '/'));
    $make_path = $base_path;
    foreach ($rel_path_parts AS $path){
        $make_path .= $path.'/';
        if (!is_dir($make_path)){
            mkdir($make_path);
            if (!is_dir($make_path)){
                return false;
            }
        }
    }
    return is_dir($full_path) ? true : false;
}

// Define a function for recursively copying files from one dir to another
// via https://stackoverflow.com/a/2050909
function recurseCopy($src, $dst, $blacklist = array(), $whitelist = array()) {
    if (empty($blacklist) || !is_array($blacklist)){ $blacklist = false; }
    if (empty($whitelist) || !is_array($whitelist)){ $whitelist = false; }
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if ( ($file != '.') && ($file != '..') ) {
            if (!empty($blacklist) && in_array($file, $blacklist)){ continue; }
            if (!empty($whitelist) && !in_array($file, $whitelist)){ continue; }
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy(rtrim($src, '/').'/'.$file, rtrim($dst, '/').'/'.$file);
            }
        }
    }
    closedir($dir);
}

// Define a function for recursively copying files from one dir to another with a blacklist of banned files
function recurseCopyWithBlacklist($src, $dst, $blacklist) {
    return recurseCopy($src, $dst, $blacklist, false);
}

// Define a function for recursively copying files from one dir to another with a whitelist of allowable files
function recurseCopyWithWhitelist($src, $dst, $whitelist) {
    return recurseCopy($src, $dst, false, $whitelist);
}

// Define a function for "cleaning" a directory/path of it's MMRPG root dir/url
function mmrpg_clean_path($path){
    return str_replace(array(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL), '/', $path);
}

// Define a function for getting a list of directory contents, recursively
// via https://stackoverflow.com/a/24784144/1876397
function getDirContents($dir, &$results = array()){
    if (!file_exists($dir)){ return $results; }
    $files = scandir($dir);
    foreach($files as $key => $value){
        $path = str_replace('\\', '/', realpath($dir.DIRECTORY_SEPARATOR.$value));
        if(!is_dir($path)) {
            $results[] = $path;
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }
    return $results;
}

// Define a function for sorting a list of files given their modified times
function getSortedDirContents($dir, $method = 'date'){
    $results = getDirContents($dir);
    if ($method === 'date'){
        static $ftimes = array();
        usort($results, function($f1, $f2) use($ftimes){
            if (isset($ftimes[$f1])){ $f1_time = $ftimes[$f1]; }
            else { $f1_time = $ftimes[$f1] = filemtime($f1); }
            if (isset($ftimes[$f2])){ $f2_time = $ftimes[$f2]; }
            else { $f2_time = $ftimes[$f2] = filemtime($f2); }
            if ($f1_time > $f2_time){ return -1; }
            elseif ($f1_time < $f2_time){ return 1; }
            else { return 0; }
            });
    } elseif ($method === 'name'){
        natsort($results);
    }
    return $results;
}

// Define a function for normalizing line endings to unix
function normalize_line_endings($s, $max_nl = 3){
    $s = str_replace(array("\r\n", "\r", "\n"), "\n", $s); // Convert all line-endings to UNIX format.
    if (!empty($max_nl)){ $s = preg_replace("/\n{".$max_nl.",}/", "\n\n", $s); } // Don't allow out-of-control blank lines.
    return $s;
}

// Define a function for forcing a newline at the end of a file
function normalize_file_markup($code, $normalize_line_endings = true, $force_eof_newline = true){
    if ($force_eof_newline){ $code = trim($code).PHP_EOL; }
    if ($normalize_line_endings){ $code = normalize_line_endings($code); }
    return $code;
}

// Define a function for selecting an element from an array by number (not key) **with rollover**
function select_from_array_with_rollover($array, $position){
    $count = count($array);
    $key = $position > 1 ? (($position - 1) % $count) : 0;
    return $array[$key];
}

// Define a function that returns a given array after removing specified keys
function array_remove_keys($array, $keys = array()){
    if (is_string($keys)){ $keys = array($keys); }
    $new_array = $array;
    foreach ($keys AS $key){ unset($new_array[$key]); }
    return $new_array;
}

// Define a function checking to see if two arrays have the same keys and values as each other
function arrays_match($array1, $array2){
    ksort($array1);
    ksort($array2);
    return $array1 == $array2;
}

// Define a function for comparing two arrays while ignoring specified keys
function arrays_match_ignore_keys($array1, $array2, $keys = array()){
    if (is_string($keys)){ $keys = array($keys); }
    $new_array1 = array_remove_keys($array1, $keys);
    $new_array2 = array_remove_keys($array2, $keys);
    return arrays_match($new_array1, $new_array2);
}

// Define an alias function for htmlentities that automatically fills defaults
function encode_form_value($string, $double_encode = true){
    return htmlentities($string, ENT_QUOTES, 'UTF-8', $double_encode);
}

// Define a function for imploding a list with oxford commas and a final "and" between last two
function implode_with_oxford_comma($list){
    if (empty($list)){ return ''; }
    $size = count($list);
    if ($size === 1){ return implode('', $list); }
    elseif ($size === 2){ return implode(' and ', $list); }
    else { return implode(', ', array_slice($list, 0, -1)).', and '.$list[$size - 1]; }
}

// Check to see if a number is plural or not (accounting for formatting)
function number_is_plural($number, $zero_is_plural = true){
    $real_number = floatval(str_replace(',', '', $number));
    if (empty($real_number) && $zero_is_plural){ return true; }
    else { return $real_number !== 1 ? true : false; }
}

// -- ARRAY MANIPULATION FUNCTIONS -- //

// Define a recursive function for flattening a nested array into one depth w/ a separator
function flatten_nested_array_recursive(array &$out, $key, array $in, $glue = '_'){
    foreach($in as $k=>$v){
        if(is_array($v)){
            flatten_nested_array_recursive($out, $key.$k.$glue, $v, $glue);
        }else{
            $out[$key.$k] = $v;
        }
    }
}

// Define a function for flattening a nested array into one depth w/ a separator
function flatten_nested_array(array $in, $glue = '_'){
    $out = array();
    flatten_nested_array_recursive($out, '', $in, $glue);
    return $out;
}

// Define a function for inflating a previously-flattened array into a nested one w/ separator
function inflate_nested_array($arr, $divider_char = "/") {
    if (!is_array($arr)){ return false; }
    $split = '/' . preg_quote($divider_char, '/') . '/';
    $ret = array();
    foreach ($arr as $key => $val) {
        $parts = preg_split($split, $key, -1, PREG_SPLIT_NO_EMPTY);
        $leafpart = array_pop($parts);
        $parent = &$ret;
        foreach ($parts as $part) {
            if (!isset($parent[$part])) {
                $parent[$part] = array();
            } elseif (!is_array($parent[$part])) {
                $parent[$part] = array();
            }
            $parent = &$parent[$part];
        }
        if (empty($parent[$leafpart])) {
            $parent[$leafpart] = $val;
        }
    }
    return $ret;
}

// -- TIME CONVERSION FUNCTIONS -- //

// Define a function for converting integer-based minutes, seconds, and frames into just milliseconds
function convert_to_milliseconds($minutes = 0, $seconds = 0, $frames = 0){
    $minutes = (int) $minutes;
    $seconds = (int) $seconds;
    $frames = (int) $frames;
    return (($minutes * 60 + $seconds) * 1000) + ($frames * 1000 / 24);
}

// Define a function for converting milliseconds to a string
function convert_from_milliseconds($totalMilliseconds = 0) {
    $totalMilliseconds = (int) $totalMilliseconds;
    $minutes = floor($totalMilliseconds / 60000);
    $totalMilliseconds %= 60000;
    $seconds = floor($totalMilliseconds / 1000);
    $frames = round(($totalMilliseconds % 1000) * 24 / 1000);
    return array('minutes' => $minutes, 'seconds' => $seconds, 'frames' => $frames);
}

// Define a function that takes a string-based timestamp, separates it, and then returns it in milliseconds
function get_milliseconds_from_timestamp($timestamp = ''){
    $parts = explode(':', $timestamp);
    $minutes = intval($parts[0]);
    $seconds = intval($parts[1]);
    $frames = intval($parts[2]);
    return convert_to_milliseconds($minutes, $seconds, $frames);
}

// Define a function that takes milliseconds and returns a string-based timestamp
function get_timestamp_from_milliseconds($milliseconds = 0){
    $parts = convert_from_milliseconds($milliseconds);
    return sprintf('%02d:%02d:%02d', $parts['minutes'], $parts['seconds'], $parts['frames']);
}

?>
