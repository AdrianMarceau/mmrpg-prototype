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