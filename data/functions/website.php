<?php

/*
 * WEBSITE FUNCTIONS
 */

// Define a function for parsing formatting code from a string
function mmrpg_formatting_decode($string){
    // Define the static formatting array variable
    static $mmrpg_formatting_array = array();
    // If the formatting array has not been populated, do so
    if (empty($mmrpg_formatting_array)){

        // Collect the robot and ability index from the database
        global $DB, $mmrpg_index;
        $temp_robots_index = $DB->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
        $temp_abilities_index = $DB->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
        // Define the array to hold the images of larger size than default
        $mmrpg_large_robot_images = array();
        $mmrpg_large_ability_images = array();
        // Loop through robots and abilities and collect tokens of large ones
        if (!empty($temp_robots_index)){ foreach ($temp_robots_index AS $token => $info){ if ($info['robot_image_size'] == 80){ $mmrpg_large_robot_images[] = $info['robot_token']; } } }
        if (!empty($temp_abilities_index)){ foreach ($temp_abilities_index AS $token => $info){ if ($info['ability_image_size'] == 80){ $mmrpg_large_ability_images[] = $info['ability_token']; } } }

        // Pull in the global index formatting variables
        $mmrpg_formatting_array = array();

        $mmrpg_formatting_array += array(
            // code font
            '/\s?\[code\]\s?(.*?)\s?\[\/code\]\s+/is' => '$1',  // code
            );
        // Define each of the different types of formatting options
        $mmrpg_formatting_array += array(
            '/\[b\](.*?)\[\/b\]/i' => '<strong class="bold">$1</strong>', // bold
            '/\[i\](.*?)\[\/i\]/i' => '<em class="italic">$1</em>',  // italic
            '/\[u\](.*?)\[\/u\]/i' => '<span class="underline">$1</span>',  // underline
            '/\[s\](.*?)\[\/s\]/i' => '<span class="strike">$1</span>',  // strike
            );
        $mmrpg_formatting_array += array(
            // spacers
            '/\[tab\]/i' => '&nbsp;&nbsp;',
            '/\s{2,}[-]{5,}\s{2,}/i' => '<hr class="line_divider line_divider_bigger" />',
            '/\s?[-]{5,}\s?/i' => '<hr class="line_divider" />',
            '/\s\|\s/i' => '&nbsp;<span class="pipe">|</span>&nbsp;',
            );
        $mmrpg_formatting_array += array(
            // left/right/center formatting
            '/\s{2,}\[size-large\]\s?(.*?)\s?\[\/size-large\]\s{2,}/is' => '<div class="size_large">$1</div>',
            '/\s{2,}\[size-medium\]\s?(.*?)\s?\[\/size-medium\]\s{2,}/is' => '<div class="size_medium">$1</div>',
            '/\s{2,}\[size-small\]\s?(.*?)\s?\[\/size-small\]\s{2,}/is' => '<div class="size_small">$1</div>',
            '/\s?\[size-large\]\s?(.*?)\s?\[\/size-large\]/is' => '<span class="size_large">$1</span>',
            '/\s?\[size-medium\]\s?(.*?)\s?\[\/size-medium\]/is' => '<span class="size_medium">$1</span>',
            '/\s?\[size-small\]\s?(.*?)\s?\[\/size-small\]/is' => '<span class="size_small">$1</span>',
            // colour block formatting
            '/\s{2,}\[color=#([a-f0-9]{6})\](?:\s+)?(.*?)(?:\s+)?\[\/color\]\s{2,}/is' => '<div class="color_block" style="color: #$1;">$2</div>',
            '/\s{2,}\[color=([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3})\](?:\s+)?(.*?)(?:\s+)?\[\/color\]\s{2,}/is' => '<div class="color_block" style="color: rgb($1, $2, $3);">$4</div>',
            '/\s?\[color=#([a-f0-9]{6})\](?:\s+)?(.*?)(?:\s+)?\[\/color\]/is' => '<span class="color_inline" style="color: #$1;">$2</span>',
            '/\s?\[color=([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3})\](?:\s+)?(.*?)(?:\s+)?\[\/color\]/is' => '<span class="color_inline" style="color: rgb($1, $2, $3);">$4</span>',
            //'/\s?\[size-(large|medium|small)\]\s?(.*?)\s?\[\/size-\1\]/is' => '<span class="size_$1">$2</span>',
            );
        $mmrpg_formatting_array += array(
            '/\s?\[align-left\]\s?(.*?)\s?\[\/align-left\]/is' => '<div class="align_left">$1</div>',
            '/\s?\[align-right\]\s?(.*?)\s?\[\/align-right\]/is' => '<div class="align_right">$1</div>',
            '/\s?\[align-center\]\s?(.*?)\s?\[\/align-center\]/is' => '<div class="align_center">$1</div>',
            '/\s?\[float-left\]\s?(.*?)\s?\[\/float-left\]/is' => '<div class="float_left">$1</div>',
            '/\s?\[float-right\]\s?(.*?)\s?\[\/float-right\]/is' => '<div class="float_right">$1</div>',
            '/\s?\[float-none\]\s?(.*?)\s?\[\/float-none\]/is' => '<div class="float_none">$1</div>',
            //'/\s?\[align-(left|right|center)\]\s?(.*?)\s?\[\/align-\1\]/is' => '<span class="align_$1">$2</span>',
            );
        /*
        foreach ($mmrpg_index['types'] AS $key => $info){
            $mmrpg_formatting_array += array('/\s?\[type-'.$info['type_token'].'\]\s?(.*?)\s?\[\/type-'.$info['type_token'].'\]/is' => '<div class="type type_panel ability_type ability_type_'.$info['type_token'].'">$1</div>');
            foreach ($mmrpg_index['types'] AS $key2 => $info2){
                $mmrpg_formatting_array += array('/\s?\[type-'.$info['type_token'].'_'.$info2['type_token'].'\]\s?(.*?)\s?\[\/type-'.$info['type_token'].'_'.$info2['type_token'].'\]/is' => '<div class="type type_panel ability_type ability_type_'.$info['type_token'].'_'.$info2['type_token'].'">$1</div>');
            }
        }
        */
        $mmrpg_formatting_array += array(
            '/\s{2,}\[type-([_a-z]+)\]\s?(.*?)\s?\[\/type-\1\]\s{2,}/is' => '<div class="type type_panel ability_type ability_type_$1">$2</div>',
            '/\s?\[type-([_a-z]+)\]\s?(.*?)\s?\[\/type-\1\]\s?/is' => '<span class="type type_panel ability_type ability_type_$1">$2</span>',
            '/\s?\[background-([-_a-z0-9]+)-(top|center|bottom)\]\s?(.*?)\s?\[\/background-\1-\2\]\s?/is' => '<div class="field field_panel field_panel_background" style="background-position: center $2; background-image: url(images/fields/$1/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$3</div></div>',
            '/\s?\[foreground-([-_a-z0-9]+)-(top|center|bottom)\]\s?(.*?)\s?\[\/foreground-\1-\2\]\s?/is' => '<div class="field field_panel field_panel_foreground" style="background-position: center $2; background-image: url(images/fields/$1/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$3</div></div>',
            '/\s?\[background-([-_a-z0-9]+)\]\s?(.*?)\s?\[\/background-\1\]\s?/is' => '<div class="field field_panel field_panel_background" style="background-image: url(images/fields/$1/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$2</div></div>',
            '/\s?\[foreground-([-_a-z0-9]+)\]\s?(.*?)\s?\[\/foreground-\1\]\s?/is' => '<div class="field field_panel field_panel_foreground" style="background-image: url(images/fields/$1/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.');"><div class="wrap">$2</div></div>',
            );
        $mmrpg_formatting_array += array(
            // image-inline (no hover, no link)
            '/\[image\]\((.*?).(jpg|jpeg|gif|png|bmp)\)/i' => '<span class="link_image_inline"><img src="$1.$2" /></span>',
            );
        $mmrpg_formatting_array += array(
            // player 80x80
            //'/\[player\]\{('.implode('|', $mmrpg_large_player_images).')\}/i' => '<span class="sprite_image sprite_image_80x80"><img src="images/players/$1/mug_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            //'/\[player:(left|right)\]\{('.implode('|', $mmrpg_large_player_images).')\}/i' => '<span class="sprite_image sprite_image_80x80"><img src="images/players/$2/mug_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            //'/\[player:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|01|02|03|04|05|06|07|08|09|10)\]\{('.implode('|', $mmrpg_large_player_images).')\}/i' => '<span class="sprite_image sprite_image_80x80 sprite_image_80x80_$2"><span><img src="images/players/$3/sprite_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            // player 40x40
            '/\[player\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/players/$1/mug_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[player:(left|right)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/players/$2/mug_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[player:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|command|01|02|03|04|05|06|07|08|09|10)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$2"><span><img src="images/players/$3/sprite_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            );
        $mmrpg_formatting_array += array(
            // robot 80x80 Alts
            '/\[robot\]\{('.implode('|', $mmrpg_large_robot_images).')_([-_a-z0-9]+)\}/i' => '<span data-test="1" class="sprite_image sprite_image_80x80"><img src="images/robots/$1_$2/mug_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[robot:(left|right)\]\{('.implode('|', $mmrpg_large_robot_images).')_([-_a-z0-9]+)\}/i' => '<span data-test="2" class="sprite_image sprite_image_80x80"><img src="images/robots/$2_$3/mug_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[robot:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|01|02|03|04|05|06|07|08|09|10)\]\{('.implode('|', $mmrpg_large_robot_images).')_([-_a-z0-9]+)\}/i' => '<span data-test="3" class="sprite_image sprite_image_80x80 sprite_image_80x80_$2"><span><img src="images/robots/$3_$4/sprite_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            // robot 40x40 Alts
            '/\[robot\]\{([-a-z0-9]+)_([-_a-z0-9]+)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/robots/$1_$2/mug_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[robot:(left|right)\]\{([-a-z0-9]+)_([-_a-z0-9]+)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/robots/$2_$3/mug_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[robot:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|command|01|02|03|04|05|06|07|08|09|10)\]\{([-a-z0-9]+)_([-_a-z0-9]+)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$2"><span><img src="images/robots/$3_$4/sprite_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            // robot 80x80
            '/\[robot\]\{('.implode('|', $mmrpg_large_robot_images).')\}/i' => '<span class="sprite_image sprite_image_80x80"><img src="images/robots/$1/mug_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[robot:(left|right)\]\{('.implode('|', $mmrpg_large_robot_images).')\}/i' => '<span class="sprite_image sprite_image_80x80"><img src="images/robots/$2/mug_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[robot:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|01|02|03|04|05|06|07|08|09|10)\]\{('.implode('|', $mmrpg_large_robot_images).')\}/i' => '<span class="sprite_image sprite_image_80x80 sprite_image_80x80_$2"><span><img src="images/robots/$3/sprite_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            // robot 40x40
            '/\[robot\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/robots/$1/mug_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[robot:(left|right)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/robots/$2/mug_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[robot:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|command|01|02|03|04|05|06|07|08|09|10)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$2"><span><img src="images/robots/$3/sprite_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            );
        $mmrpg_formatting_array += array(
            // mecha 80x80
            //'/\[mecha\]\{('.implode('|', $mmrpg_large_mecha_images).')\}/i' => '<span class="sprite_image sprite_image_80x80"><img src="images/mechas/$1/mug_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            //'/\[mecha:(left|right)\]\{('.implode('|', $mmrpg_large_mecha_images).')\}/i' => '<span class="sprite_image sprite_image_80x80"><img src="images/mechas/$2/mug_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            //'/\[mecha:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|01|02|03|04|05|06|07|08|09|10)\]\{('.implode('|', $mmrpg_large_mecha_images).')\}/i' => '<span class="sprite_image sprite_image_80x80 sprite_image_80x80_$2"><span><img src="images/mechas/$3/sprite_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            // mecha 40x40
            '/\[mecha\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/robots/$1/mug_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[mecha:(left|right)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/robots/$2/mug_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[mecha:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|command|01|02|03|04|05|06|07|08|09|10)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$2"><span><img src="images/robots/$3/sprite_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            );
        $mmrpg_formatting_array += array(
            // ability 80x80
            '/\[ability\]\{('.implode('|', $mmrpg_large_ability_images).')\}/i' => '<span class="sprite_image sprite_image_80x80"><img src="images/abilities/$1/icon_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[ability:(left|right)\]\{('.implode('|', $mmrpg_large_ability_images).')\}/i' => '<span class="sprite_image sprite_image_80x80"><img src="images/abilities/$2/icon_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[ability:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|command|01|02|03|04|05|06|07|08|09|10)\]\{('.implode('|', $mmrpg_large_ability_images).')\}/i' => '<span class="sprite_image sprite_image_80x80 sprite_image_80x80_$2"><span><img src="images/abilities/$3/sprite_$1_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            // ability 40x40
            '/\[ability\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/abilities/$1/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[ability:(left|right)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/abilities/$2/icon_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[ability:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|command|01|02|03|04|05|06|07|08|09|10)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$2"><span><img src="images/abilities/$3/sprite_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            );
        $mmrpg_formatting_array += array(
            // item 40x40
            '/\[item\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/abilities/item-$1/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$1" /></span>',
            '/\[item:(left|right)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40"><img src="images/abilities/item-$2/icon_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$2" /></span>',
            '/\[item:(left|right):(base|taunt|victory|defeat|shoot|throw|summon|slide|defend|damage|base2|command|01|02|03|04|05|06|07|08|09|10)\]\{(.*?)\}/i' => '<span class="sprite_image sprite_image_40x40 sprite_image_40x40_$2"><span><img src="images/abilities/item-$3/sprite_$1_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'" alt="$3" /></span></span>',
            );
        $mmrpg_formatting_array += array(


            // spoiler tags
            '/\[([^\[\]]+)\]\{spoiler\}/i' => '<span class="type type_span ability_type ability_type_space" style="background-image: none; color: rgb(54,57,90);">$1</span>',

            // inline colours
            '/\[([^\[\]]+)\]\{#([a-f0-9]{6})\}/i' => '<span class="colour_inline" style="color: #$2;">$1</span>',
            '/\[([^\[\]]+)\]\{([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3})\}/i' => '<span class="colour_inline" style="color: rgb($2, $3, $4);">$1</span>',
            // inline text with link to image
            '/\[([^\[\]]+)\]\((.*?).(jpg|jpeg|gif|png|bmp)\:text\)/i' => '<a class="link_inline" href="$2.$3" target="_blank">$1</a>',
            '/\[([^\[\]]+)\]\((.*?).(jpg|jpeg|gif|png|bmp)\:image\)/i' => '<a class="link_image_inline" href="$2.$3" target="_blank"><img src="$2.$3" alt="$1" title="$1" /></a>',
            // inline image with hover and link
            '/\[([^\[\]]+)\]\((.*?).(jpg|jpeg|gif|png|bmp)\)/i' => '<a class="link_image_inline" href="$2.$3" target="_blank"><img src="$2.$3" alt="$1" title="$1" /></a>',
            // standard link
            '/\[([^\[\]]+)\]\((.*?)\)/i' => '<a class="link_inline" href="$2" target="_blank">$1</a>',
            // elemental type
            '/\[([^\[\]]+)\]\{(.*?)\}/i' => '<span class="type type_span ability_type ability_type_$2">$1</span>',
            );

    }

    //die('<pre>$mmrpg_formatting_array = '.print_r($mmrpg_formatting_array, true).'</pre>');

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
    foreach ($mmrpg_formatting_array AS $find_pattern => $replace_pattern){ $string = preg_replace($find_pattern, $replace_pattern, $string); }
    if ($has_code){ foreach ($code_matches[1] AS $key => $match){ $string = str_replace('##CODE'.$key.'##', '<span class="code">'.$match.'</span>', $string); } }
    // Change line breaks to actual breaks by grouping into paragraphs
    $string = str_replace("\r\n", '<br />', $string);
    //$string = '<p>'.preg_replace('/(<br\s?\/>\s?){2,}/i', '</p><p>', $string).'</p>';
    $string = '<div>'.$string.'</div>';
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
function mmrpg_formatting_help(){

    // Start the output buffer and prepare to collect contents
    ob_start();
    // Include the website formatting text file for reference
    require('website_formatting.php');
    // Collect the output buffer contents into a variable
    $this_formatting = nl2br(mmrpg_formatting_decode(ob_get_clean()));

    // Start the output buffer and prepare to collect contents
    ob_start();
    ?>
    <div class="community bodytext">
        <div class="formatting formatting_expanded">
            <a class="link_inline toggle" href="#">- Hide Formatting Options</a>
            <div class="wrapper">
                <?= $this_formatting ?>
            </div>
        </div>
    </div>
    <?
    // Collect the output buffer contents into a variable
    $this_markup = ob_get_clean();

    // Return the collected output buffer contents
    return $this_markup;

}

// Define a function for generating the number suffix
function mmrpg_number_suffix($value, $concatenate = true, $superscript = false){
    if (!is_numeric($value) || !is_int($value)){ return false; }
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
    global $DB, $this_userid;
    // Define the timeouts for active sessions
    $this_time = time();
    $min_time = strtotime('-'.$session_timeout.' minutes', $this_time);
    // If we're not using strict filtering, just collect normally
    if (!$strict_filtering){
        // Collect any sessions that are active and match the query
        $inner_href_query = !empty($session_href) ? "AND session_href LIKE '{$session_href}%'" : '';
        $active_sessions = $DB->get_array_list("SELECT DISTINCT user_id, session_href FROM mmrpg_sessions WHERE session_access >= {$min_time} {$inner_href_query} ORDER BY session_access DESC", 'user_id');
    }
    // Otherwise, we have to excluce users who have since visited other pages
    else {
        // Collect any sessions that are active and match the query
        $active_sessions = $DB->get_array_list("SELECT DISTINCT user_id, session_href FROM mmrpg_sessions WHERE session_access >= {$min_time} ORDER BY session_access ASC", 'user_id');
        if (!empty($active_sessions) && !empty($session_href)){
            foreach ($active_sessions AS $key => $session){
                if (!preg_match('/^'.str_replace("/", "\/", $session_href).'/i', $session['session_href'])){
                    unset($active_sessions[$key]);
                }
            }
        }
    }
    // Return the active session count if not empty
    return !empty($active_sessions) ? $active_sessions : array();
}

// Define a function for updating a user's session in the database
function mmrpg_website_session_update($session_href){
    // Import required global variables
    global $DB, $this_userid;
    // Collect the session ID from the system
    $session_key = session_id();
    // Attempt to collect the current database row if it exists
    $temp_session = $DB->get_array("SELECT * FROM mmrpg_sessions WHERE user_id = '{$this_userid}' AND session_key = '{$session_key}' AND session_href = '{$session_href}' LIMIT 1");
    // If an existing session for this page was found, update it
    if (!empty($temp_session['session_id'])){
        $update_array = array();
        $update_array['session_href'] = $session_href;
        $update_array['session_access'] = time();
        $update_array['session_ip'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $DB->update('mmrpg_sessions', $update_array, array('session_id' => $temp_session['session_id']));
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
        $DB->insert('mmrpg_sessions', $insert_array);
    }
    // Return true on success
    return true;
}

// Define a function for collecting (and storing) data about the website categories
function mmrpg_website_community_index(){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'mmrpg_website_community_categories()');  }
    global $DB;
    // Check to see if the community category has already been pulled or not
    if (false && !empty($_SESSION['COMMUNITY']['categories'])){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_categories_index = json_decode($_SESSION['COMMUNITY']['categories'], true);
    } else {
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        // Collect the community catetories from the database
        // Collect all the categories from the index
        $this_categories_query = "SELECT * FROM mmrpg_categories AS categories WHERE categories.category_published = 1 ORDER BY categories.category_order ASC";
        $this_categories_index = $DB->get_array_list($this_categories_query, 'category_token');
        // Update the database index cache
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $_SESSION['COMMUNITY']['categories'] = json_encode($this_categories_index);
    }
    // Return the collected community categories
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    return $this_categories_index;
}

// Define a function for selecting a list of threads in a category
function mmrpg_website_community_category_threads($this_category_info, $filter_locked = false, $filter_recent = false, $row_limit = false, $filter_ids = array()){

    // Pull in global variables
    global $DB, $this_userinfo;

    // Collect the recently updated posts for this player / guest
    if ($this_userinfo['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $temp_last_login = $this_userinfo['user_backup_login']; }
    else { $temp_last_login = time() - MMRPG_SETTINGS_UPDATE_TIMEOUT; }

    // Define the ORDER BY string based on category key
    if ($this_category_info['category_token'] != 'news'){ $temp_order_by = 'threads.thread_sticky DESC, threads.thread_mod_date DESC, threads.thread_date DESC'; }
    else { $temp_order_by = 'threads.thread_sticky DESC, threads.thread_date DESC'; }

    // Define the extra WHERE string based on arguments
    $temp_where_filter = array();
    if ($filter_locked == true){ $temp_where_filter[] = "threads.thread_locked = 0"; }
    if ($filter_recent == true){ $temp_where_filter[] = "threads.thread_mod_date > {$temp_last_login}"; }
    if (!empty($filter_ids)){ $temp_where_filter[] = "threads.thread_id NOT IN(".implode(',', $filter_ids).")"; }
    if (empty($temp_where_filter)){ $temp_where_filter[] = "1 = 1"; }
    $temp_where_filter = implode(" AND \n", $temp_where_filter)."\n";

    // If a row limit has been defined, generate the string for it
    if (!empty($row_limit) && is_numeric($row_limit)){ $temp_limit_string = "LIMIT {$row_limit} "; }
    else { $temp_limit_string = ''; }

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

        users2.mod_user_id,
        users2.mod_user_name,
        users2.mod_user_name_public,
        users2.mod_user_name_clean,
        users2.mod_user_background_path,
        users2.mod_user_colour_token,
        users2.mod_user_image_path,

        users3.target_user_id,
        users3.target_user_name,
        users3.target_user_name_public,
        users3.target_user_name_clean,
        users3.target_user_background_path,
        users3.target_user_colour_token,
        users3.target_user_image_path,

        categories.category_id,
        categories.category_level,
        categories.category_name,
        categories.category_token,
        categories.category_description,
        categories.category_published,
        categories.category_order,

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
            user_background_path AS mod_user_background_path
            FROM mmrpg_users
            ) AS users2
            ON threads.thread_mod_user = users2.mod_user_id

        LEFT JOIN (
            SELECT
            user_id AS target_user_id,
            user_name AS target_user_name,
            user_name_clean AS target_user_name_clean,
            user_name_public AS target_user_name_public,
            user_colour_token AS target_user_colour_token,
            user_image_path AS target_user_image_path,
            user_background_path AS target_user_background_path
            FROM mmrpg_users
            ) AS users3
            ON threads.thread_target = users3.target_user_id

        LEFT JOIN mmrpg_categories
            AS categories
            ON threads.category_id = categories.category_id

        LEFT JOIN (
            SELECT
            posts.thread_id,
            count(*) AS post_count
            FROM mmrpg_posts AS posts
            GROUP BY posts.thread_id
            ) AS posts
            ON threads.thread_id = posts.thread_id

          LEFT JOIN (
            SELECT
            posts2.thread_id,
            posts2.post_mod,
            count(*) AS new_post_count
            FROM mmrpg_posts AS posts2
            WHERE posts2.post_mod > {$temp_last_login}
            GROUP BY posts2.thread_id
            ) AS posts_new
            ON threads.thread_id = posts_new.thread_id

        WHERE
            threads.category_id = {$this_category_info['category_id']} AND
            threads.thread_published = 1 AND
            threads.thread_mod_user <> {$this_userinfo['user_id']} AND
            (threads.thread_target = 0 OR
                threads.thread_target = {$this_userinfo['user_id']} OR
                threads.user_id = {$this_userinfo['user_id']}
                ) AND
            {$temp_where_filter}

        ORDER BY
            threads.thread_locked ASC,
            {$temp_order_by}

        {$temp_limit_string}

            ;";

    // Collect all the threads for this category from the database
    $this_threads_array = $DB->get_array_list($this_threads_query);

    // Return the threads array if not empty
    return !empty($this_threads_array) ? $this_threads_array : array();

}

// Define a function for generating a community thread block
function mmrpg_website_community_thread_linkblock($this_thread_key, $this_thread_info, $header_mode = false, $compact_mode = false){

    // Pull in global variables necessary for the linkblock
    global $this_userinfo;

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
        $temp_date_group = date('Y-m', $temp_thread_mod_date);
        if (!empty($this_thread_info['thread_locked'])){ $temp_date_group = 'locked'; }
        elseif (!empty($this_thread_info['thread_sticky'])){ $temp_date_group = 'sticky'; }
        // If the date group was different, update the static variable
        if ($temp_date_group != $this_date_group){
            $this_date_group = $temp_date_group;
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

        }

        // Define the temporary display variables
        $temp_category_id = $this_thread_info['category_id'];
        $temp_category_token = $this_thread_info['category_token'];
        $temp_thread_id = $this_thread_info['thread_id'];
        $temp_thread_token = $this_thread_info['thread_token'];
        $temp_thread_name = $this_thread_info['thread_name'];
        $temp_thread_author = !empty($this_thread_info['user_name_public']) ? $this_thread_info['user_name_public'] : $this_thread_info['user_name'];
        $temp_thread_author_colour = !empty($this_thread_info['user_colour_token']) ? $this_thread_info['user_colour_token'] : 'none';
        $temp_thread_date = date('F jS, Y', $temp_thread_date).' at '.date('g:ia', $temp_thread_date);
        $temp_thread_mod_user = !empty($this_thread_info['mod_user_name_public']) ? $this_thread_info['mod_user_name_public'] : $this_thread_info['mod_user_name'];
        $temp_thread_mod_date = !empty($this_thread_info['thread_mod_date']) && $this_thread_info['thread_mod_date'] != $this_thread_info['thread_date'] ? $this_thread_info['thread_mod_date'] : false;
        $temp_thread_mod_date = !empty($temp_thread_mod_date) ? 'Updated by '.$temp_thread_mod_user : false;
        $temp_thread_body = strlen($this_thread_info['thread_body']) > 255 ? substr($this_thread_info['thread_body'], 0, 255).'&hellip;' : $this_thread_info['thread_body'];
        $temp_posts_count = !empty($this_thread_info['post_count']) ? $this_thread_info['post_count'] : 0;
        $temp_thread_timestamp = !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $this_thread_info['thread_date'];
        $temp_thread_link = 'community/'.$temp_category_token.'/'.$temp_thread_id.'/'.$temp_thread_token.'/';

        // If there are comments, update link to point to last page
        $temp_thread_link_comments = $temp_thread_link;
        if ($temp_posts_count >= MMRPG_SETTINGS_POSTS_PERPAGE){
            $temp_posts_pages_max = ceil($temp_posts_count / MMRPG_SETTINGS_POSTS_PERPAGE);
            $temp_thread_link_comments .= $temp_posts_pages_max.'/';
        }
        $temp_thread_link_comments .= !empty($temp_posts_count) ? '#comment-listing' : '#comment-form';

        // Define the target option text
        $temp_target_thread_author = !empty($this_thread_info['target_user_name_public']) ? $this_thread_info['target_user_name_public'] : $this_thread_info['target_user_name'];
        $temp_target_thread_author_colour = !empty($this_thread_info['target_user_colour_token']) ? $this_thread_info['target_user_colour_token'] : 'none';

        // Define if this post is new to the logged in user or not
        $temp_is_new = false;
        // Supress the new flag if thread has already been viewed
        if (!$temp_session_viewed){
            if ($this_userinfo['user_id'] != MMRPG_SETTINGS_GUEST_ID
                //&& $this_thread_info['user_id'] != $this_userinfo['user_id']
                && $this_thread_info['thread_mod_user'] != $this_userinfo['user_id']
                && $temp_thread_timestamp > $this_userinfo['user_backup_login']){
                $temp_is_new = true;
            } elseif ($this_userinfo['user_id'] == MMRPG_SETTINGS_GUEST_ID
                && (($this_time - $temp_thread_timestamp) <= MMRPG_SETTINGS_UPDATE_TIMEOUT)){
                $temp_is_new = true;
            }
        }

        ?>
        <div id="thread-<?= $temp_thread_id ?>" data-group="<?= $temp_date_group ?>" class="subbody thread_subbody thread_subbody_small <?= $header_mode ? 'thread_subbody_small_nohover' : '' ?> <?= $compact_mode ? 'thread_subbody_compact' : '' ?> thread_right field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : 'none' ?>">
            <?
            // If this thread has a specific target, display their avatar to the right
            if ($this_thread_info['thread_target'] != 0){

                // Define the avatar class and path variables
                $temp_avatar_float = $this_thread_info['user_id'] == $this_userinfo['user_id'] ? 'left' : 'right';
                $temp_avatar_direction = $temp_avatar_float == 'left' ? 'right' : 'left';
                $temp_avatar_frame = $this_thread_info['user_id'] != $this_thread_info['thread_target'] && !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
                $temp_avatar_path = !empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
                $temp_background_path = !empty($this_thread_info['user_background_path']) ? $this_thread_info['user_background_path'] : 'fields/intro-field';
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

                // Define the avatar class and path variables
                //$temp_avatar_frame = '00';
                $temp_avatar_float = $temp_avatar_float == 'left' ? 'right' : 'left';
                $temp_avatar_direction = $temp_avatar_float == 'left' ? 'right' : 'left';
                $temp_avatar_frame =  $this_thread_info['user_id'] == $this_thread_info['thread_target'] && !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
                $temp_avatar_path = !empty($this_thread_info['target_user_image_path']) ? $this_thread_info['target_user_image_path'] : 'robots/mega-man/40';
                $temp_background_path = !empty($this_thread_info['target_user_background_path']) ? $this_thread_info['target_user_background_path'] : 'fields/intro-field';
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
            // Otherwise if this is a totally normal community post
            else {

                // Define the avatar class and path variables
                $temp_avatar_float = 'left';
                $temp_avatar_direction = 'right';
                $temp_avatar_frame = !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
                $temp_avatar_path = !empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
                $temp_background_path = !empty($this_thread_info['user_background_path']) ? $this_thread_info['user_background_path'] : 'fields/intro-field';
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
            <div class="text thread_linkblock thread_linkblock_<?= $this_thread_info['thread_target'] != 0 && $this_thread_info['user_id'] != $this_userinfo['user_id'] ? 'right' : 'left' ?>">
                <a class="link" href="<?= $temp_thread_link ?>"><span><?= $temp_thread_name ?></span></a>
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
                    <?= !empty($temp_thread_mod_date) ? '<span class="newpost" style="letter-spacing: 0;">'.$temp_thread_mod_date.'</span> ' : '' ?>
                </div>
            </div>
        </div>
        <?

    // Collect the markup for this link block and return
    $this_markup = trim(ob_get_clean());
    return $this_markup;

}

// Define a function for deleting a directory
function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
                throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
                if (is_dir($file)) {
                        self::deleteDir($file);
                } else {
                        unlink($file);
                }
        }
        rmdir($dirPath);
}

?>