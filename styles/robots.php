<?
// Require the application top
define('MMRPG_INDEX_STYLES', true);
require_once('../top.php');

// Change the content header to that of CSS
$cache_time = 60 * 60 * 24;
header("Content-type: text/css; charset=UTF-8");
header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
header("Cache-control: public, max-age={$cache_time}, must-revalidate");
header("Pragma: cache");


// Define a function for saving the style cache
function mmrpg_save_style_markup($this_cache_filedir, $temp_css_markup){
    // Generate the save data by serializing the session variable
    $this_cache_content = $temp_css_markup;
    // Write the index to a cache file, if caching is enabled
    $this_cache_file = fopen($this_cache_filedir, 'w');
    fwrite($this_cache_file, $this_cache_content);
    fclose($this_cache_file);
    // Return true on success
    return true;
}
// Define a function for loading the style cache
function mmrpg_load_style_markup($this_cache_filedir){
    // Generate the save data by serializing the session variable
    $this_cache_content = file_get_contents($this_cache_filedir);
    $this_cache_content = $this_cache_content;
    // Return true on success
    return $this_cache_content;
}


// Loop through the save file directory and generate an index
$this_cache_stamp = MMRPG_CONFIG_CACHE_DATE; //.'_'.date('Ymd'); //201301012359
$this_cache_filename = 'cache.robots.'.$this_cache_stamp.'.css';
$this_cache_filedir = $this_cache_dir.$this_cache_filename;
$this_file_index = array();
$this_file_count = count($this_file_index);
$temp_css_markup = array();
if (MMRPG_CONFIG_CACHE_INDEXES && file_exists($this_cache_filedir)){

    $temp_css_markup = mmrpg_load_style_markup($this_cache_filedir);

} else {

    // Print out the PHP header in a comment
    $temp_css_markup = '/* -- MMRPG Prototype Robot Stylesheet, Last Updated '.MMRPG_CONFIG_CACHE_DATE.' -- */'."\n";

    /* -- ROBOT STYLES -- */

    // Loop through every robot in the database
    ob_start();
    $mmrpg_index_robots = rpg_robot::get_index();
    foreach ($mmrpg_index_robots AS $robot_token => $robot_info){
        $image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : 'robot';
        $sprite_sizes = array($robot_info['robot_image_size'], ($robot_info['robot_image_size'] * 2));
        foreach ($sprite_sizes AS $sprite_size){
            $sprite_sizex = $sprite_size.'x'.$sprite_size;
            ?>
            #mmrpg .sprite_<?= $sprite_sizex ?>.sprite_left.sprite_<?= $robot_token ?> {
                background-image: url(../images/robots/<?= $image_token ?>/sprite_left_<?= $sprite_sizex ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);
            }
            #mmrpg .sprite_<?= $sprite_sizex ?>.sprite_left.sprite_<?= $robot_token ?>.sprite_shadow {
                background-image: url(../images/robots_shadows/<?= $image_token ?>/sprite_left_<?= $sprite_sizex ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);
            }
            #mmrpg .sprite_<?= $sprite_sizex ?>.sprite_right.sprite_<?= $robot_token ?> {
                background-image: url(../images/robots/<?= $image_token ?>/sprite_left_<?= $sprite_sizex ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);
            }
            #mmrpg .sprite_<?= $sprite_sizex ?>.sprite_right.sprite_<?= $robot_token ?>.sprite_shadow {
                background-image: url(../images/robots_shadows/<?= $image_token ?>/sprite_left_<?= $sprite_sizex ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);
            }
            <?

        }

    }
    $temp_css_markup .= ob_get_clean();

    // Compress the CSS markup before saving it
    $temp_css_markup = preg_replace('/\s+/', ' ', $temp_css_markup);
    $temp_css_markup = str_replace('; ', ';', $temp_css_markup);

    // Update the style cache files
    mmrpg_save_style_markup($this_cache_filedir, $temp_css_markup);

}

// Print out the final generated CSS markup
echo $temp_css_markup;

?>