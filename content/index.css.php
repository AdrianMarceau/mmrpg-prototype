<?

// Collect base directory for reference
$base_dir = rtrim(dirname(dirname(__FILE__)), '/').'/';

// Require the top file and the content index
$auto_parse_fields = false;
require($base_dir.'top.php');

// Set the content type to the appropriate format and make sure it's cached
header('Content-Type: text/css');
header('Cache-Control: public, max-age=86400');
header('Expires: '.gmdate('D, d M Y H:i:s', time() + 86400).' GMT');
header('Pragma: public');

// If there's already a locally-cached version of this file, grab that instead
$cached_files_enabled = true;
if (!empty($_REQUEST['refresh']) && $_REQUEST['refresh'] === 'true'){ $cached_files_enabled = false; }
$cached_files_dir = MMRPG_CONFIG_ROOTDIR.'.cache/indexes/';
$cached_date_cutoff = substr(MMRPG_CONFIG_CACHE_DATE, 0, 8);
$cached_markup_file = 'cache.mmrpg-content-styles.css';
if ($cached_files_enabled
    && file_exists($cached_files_dir.$cached_markup_file)
    && date('Ymd', filemtime($cached_files_dir.$cached_markup_file)) >= $cached_date_cutoff){
    //error_log(basename(__FILE__).' is pulling mmrpg content index css from cache !');
    header('HTTP/1.1 200 OK');
    $content_index_css = file_get_contents($cached_files_dir.$cached_markup_file);
    echo(trim($content_index_css).PHP_EOL);
    exit();
}

// Otherwise we will have to generate it from scratch at runtime
require($base_dir.'content/all.php');
//error_log(basename(__FILE__).' is generating mmrpg content index css from scratch ...');
if (!empty($mmrpg_indexes)){
    header('HTTP/1.1 200 OK');
    ob_start();
    echo('/* -- MMRPG CONTENT STYLES | Updated: '.MMRPG_CONFIG_CACHE_DATE.' -- */'.PHP_EOL);
    //echo('/* $mmrpg_indexes = '.print_r($mmrpg_indexes, true).' */'.PHP_EOL.PHP_EOL);
    // Loop through all the index objects that have sprites and generate the styles for them
    rpg_world::preload_indexes($mmrpg_indexes);
    $object_sprite_kinds = array('player', 'robot', 'ability', 'item');
    $object_styles_common = '';
    $object_styles_sprites = '';
    $object_styles_sprites_alts = '';
    $get_index_styles = function($object_kind) use ($mmrpg_indexes, &$object_styles_common, &$object_styles_sprites, &$object_styles_sprites_alts){
        $object_xkind = rpg_world::get_xkind($object_kind);
        if (!empty($mmrpg_indexes[$object_xkind])){
            $object_index = $mmrpg_indexes[$object_xkind];
            // Check if this is one of the sprite types that uses one large composite sprite sheet and set a flag
            $use_composite = $object_xkind === 'items' || $object_xkind === 'abilities' ? true : false;
            // Define the allowed prefixes for this object kind (the first index [0] acts as the default)
            $prefixes = ($object_kind === 'player' || $object_kind === 'robot') ? array('sprite', 'mug') : array('icon', 'sprite');
            // If we're using a composite sprite sheet, generate base common styles for each applicable prefix
            if ($use_composite){
                foreach ($prefixes as $prefix) {
                    $inner_styles = array();
                    if ($object_xkind === 'items'){ $pseudo_meta = rpg_world::get_sprite_meta($object_kind, 'small-screw', '', 'left', $prefix); }
                    elseif ($object_xkind === 'abilities'){ $pseudo_meta = rpg_world::get_sprite_meta($object_kind, 'buster-shot', '', 'left', $prefix); }

                    if (!empty($pseudo_meta['sheetPath'])){ $inner_styles['background-image'] = 'url(../'.$pseudo_meta['sheetPath'].'?'.MMRPG_CONFIG_CACHE_DATE.')'; }
                    if (!empty($pseudo_meta['sheetSize'])){ $inner_styles['background-size'] = $pseudo_meta['sheetSize'][0].'px '.$pseudo_meta['sheetSize'][1].'px'; }
                    $inner_styles_string = !empty($inner_styles) ? implode('; ', array_map(function($v, $k){ return $k.': '.$v.' !important'; }, $inner_styles, array_keys($inner_styles))) : '';

                    if (!empty($inner_styles_string)){
                        $base_selector = '#mmrpg .sprite.'.$object_kind;
                        $selectors = array();
                        if ($prefix === $prefixes[0]) {
                            $selectors[] = $base_selector.':not([data-kind]) .wrap .sprite';
                            $selectors[] = $base_selector.'[data-kind="'.$prefix.'"] .wrap .sprite';
                        } else {
                            $selectors[] = $base_selector.'[data-kind="'.$prefix.'"] .wrap .sprite';
                        }
                        $object_styles_common .= (implode(', ', $selectors).' { '.$inner_styles_string.' }'.PHP_EOL);
                    }
                }
            }
            // Now we can loop through the actual objects of this type and generate their individual styles where applicable
            foreach ($object_index AS $object_token => $object_info){
                if ($object_token === $object_kind){ continue; }
                if ($object_info[$object_kind.'_class'] === 'system'){ continue; }
                if (empty($object_info[$object_kind.'_flag_published'])){ continue; }
                if (empty($object_info[$object_kind.'_flag_complete'])){ continue; }
                $object_alt = ''; // TODO: maybe expand this later?
                $is_event_sprite = !empty($object_info[$object_kind.'_subclass']) && $object_info[$object_kind.'_subclass'] === 'event' ? true : false;
                // Fetch the base metadata (using default prefix) to pull global variables (like speed) to prevent duplication
                $base_meta = rpg_world::get_sprite_meta($object_kind, $object_token, $object_alt, 'left', $prefixes[0]);
                if (empty($base_meta)){ continue; }
                $outer_styles = array();
                if (!empty($base_meta['spriteSpeed']) && $base_meta['spriteSpeed'] !== 1){ $outer_styles['--sprite-speed'] = $base_meta['spriteSpeed']; }
                $outer_styles_string = !empty($outer_styles) ? implode('; ', array_map(function($v, $k){ return $k.': '.$v.' !important'; }, $outer_styles, array_keys($outer_styles))) : '';
                if (!empty($outer_styles_string)){
                    // Set once on the outer wrap without the [data-kind] dependency
                    $object_styles_sprites .= ('#mmrpg .sprite.'.$object_kind.'[data-token="'.$object_token.'"] { '.$outer_styles_string.' }'.PHP_EOL);
                }
                // Loop through all allowed prefixes to fetch targeted sheet info and attach the corresponding selector strings
                foreach ($prefixes as $prefix) {
                    $sprite_meta = rpg_world::get_sprite_meta($object_kind, $object_token, $object_alt, 'left', $prefix);
                    //error_log('-> $sprite_meta ('.$prefix.') = '.print_r($sprite_meta, true));
                    if (empty($sprite_meta)){ continue; }

                    $inner_styles = array();
                    if ($use_composite && !empty($sprite_meta['sheetOffset'])){ $inner_styles['background-position'] = $sprite_meta['sheetOffset'][0].'px '.$sprite_meta['sheetOffset'][1].'px'; }
                    if (!$use_composite && !empty($sprite_meta['sheetPath'])){ $inner_styles['background-image'] = 'url(../'.$sprite_meta['sheetPath'].'?'.MMRPG_CONFIG_CACHE_DATE.')'; }
                    $inner_styles_string = !empty($inner_styles) ? implode('; ', array_map(function($v, $k){ return $k.': '.$v.' !important'; }, $inner_styles, array_keys($inner_styles))) : '';

                    if (!empty($inner_styles_string)){
                        $data_token = '[data-token="'.$object_token.'"]';
                        $base_selector = '#mmrpg .sprite.'.$object_kind;
                        $selectors = array();

                        // Combine :not([data-kind]) with the explicit default if we're evaluating the base prefix
                        if ($prefix === $prefixes[0]){
                            $selectors[] = $base_selector.':not([data-kind])'.$data_token.' .wrap .sprite';
                            $selectors[] = $base_selector.'[data-kind="'.$prefix.'"]'.$data_token.' .wrap .sprite';
                            if ($is_event_sprite){
                                $data_token2 = '[data-token^="'.$object_token.'__"]';
                                $selectors[] = $base_selector.':not([data-kind])'.$data_token2.' .wrap .sprite';
                                $selectors[] = $base_selector.'[data-kind="'.$prefix.'"]'.$data_token2.' .wrap .sprite';
                            }
                        } else {
                            $selectors[] = $base_selector.'[data-kind="'.$prefix.'"]'.$data_token.' .wrap .sprite';
                            if ($is_event_sprite){
                                $data_token2 = '[data-token^="'.$object_token.'__"]';
                                $selectors[] = $base_selector.'[data-kind="'.$prefix.'"]'.$data_token2.' .wrap .sprite';
                            }
                        }
                        $object_styles_sprites .= (implode(', ', $selectors).' { '.$inner_styles_string.' }'.PHP_EOL);
                    }

                    // If this particular object has any image alts defined, generate mapped styles for them using the current prefix
                    if (!$use_composite && !empty($object_info[$object_kind.'_image_alts'])){
                        $object_image_alts = $object_info[$object_kind.'_image_alts'];
                        foreach ($object_image_alts AS $alt_info){
                            $alt_token = $alt_info['token'];
                            $alt_meta = rpg_world::get_sprite_meta($object_kind, $object_token, $alt_token, 'left', $prefix);
                            $alt_inner_styles = array();
                            if (!empty($alt_meta['sheetPath'])){ $alt_inner_styles['background-image'] = 'url(../'.$alt_meta['sheetPath'].'?'.MMRPG_CONFIG_CACHE_DATE.')'; }
                            $alt_inner_styles_string = !empty($alt_inner_styles) ? implode('; ', array_map(function($v, $k){ return $k.': '.$v.' !important'; }, $alt_inner_styles, array_keys($alt_inner_styles))) : '';

                            if (!empty($alt_inner_styles_string)){
                                $alt_data_token = '[data-token="'.$object_token.'"]';
                                $alt_data_alt = '[data-alt="'.$alt_token.'"]';
                                $base_selector = '#mmrpg .sprite.'.$object_kind;
                                $alt_selectors = array();

                                if ($prefix === $prefixes[0]){
                                    $alt_selectors[] = $base_selector.':not([data-kind])'.$alt_data_token.$alt_data_alt.' .wrap .sprite';
                                    $alt_selectors[] = $base_selector.'[data-kind="'.$prefix.'"]'.$alt_data_token.$alt_data_alt.' .wrap .sprite';
                                    if ($is_event_sprite){
                                        $alt_data_token2 = '[data-token^="'.$object_token.'__"]';
                                        $alt_selectors[] = $base_selector.':not([data-kind])'.$alt_data_token2.$alt_data_alt.' .wrap .sprite';
                                        $alt_selectors[] = $base_selector.'[data-kind="'.$prefix.'"]'.$alt_data_token2.$alt_data_alt.' .wrap .sprite';
                                    }
                                } else {
                                    $alt_selectors[] = $base_selector.'[data-kind="'.$prefix.'"]'.$alt_data_token.$alt_data_alt.' .wrap .sprite';
                                    if ($is_event_sprite){
                                        $alt_data_token2 = '[data-token^="'.$object_token.'__"]';
                                        $alt_selectors[] = $base_selector.'[data-kind="'.$prefix.'"]'.$alt_data_token2.$alt_data_alt.' .wrap .sprite';
                                    }
                                }
                                $object_styles_sprites_alts .= (implode(', ', $alt_selectors).' { '.$alt_inner_styles_string.' }'.PHP_EOL);
                            }
                        }
                    }
                }
            }
        }
    };
    foreach ($object_sprite_kinds AS $object_kind){ $get_index_styles($object_kind); }
    ob_start();
    echo(trim($object_styles_common).PHP_EOL);
    echo(trim($object_styles_sprites).PHP_EOL);
    echo(trim($object_styles_sprites_alts).PHP_EOL);
    $object_styles_combined = trim(ob_get_clean());
    $object_styles_combined = preg_replace('/\s+/', ' ', $object_styles_combined);
    echo($object_styles_combined.PHP_EOL);
    $content_index_css = ob_get_clean();
    if (!empty($content_index_css)){
        if (file_exists($cached_files_dir.$cached_markup_file)){ @unlink($cached_files_dir.$cached_markup_file); }
        $f = fopen($cached_files_dir.$cached_markup_file, 'w');
        fwrite($f, $content_index_css);
        fclose($f);
    }
    echo(trim($content_index_css).PHP_EOL);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo('/* No index data found in database! */'.PHP_EOL);
}
exit();

?>