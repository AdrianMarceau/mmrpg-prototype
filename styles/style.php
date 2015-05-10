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
$this_cache_filename = 'cache.style.'.$this_cache_stamp.'.css';
$this_cache_filedir = $this_cache_dir.$this_cache_filename;
$this_file_index = array();
$this_file_count = count($this_file_index);
$temp_css_markup = array();
if (MMRPG_CONFIG_CACHE_INDEXES && file_exists($this_cache_filedir)){

  $temp_css_markup = mmrpg_load_style_markup($this_cache_filedir);

} else {

  // Print out the PHP header in a comment
  $temp_css_markup = '/* -- MMRPG Prototype Stylesheet, Last Updated '.MMRPG_CONFIG_CACHE_DATE.' -- */'."\n";

  // Require the master CSS file without any of the dynamic additions
  ob_start();
  require_once('style.css');
  $temp_css_markup .= ob_get_clean();

  /* -- TYPE STYLES -- */

  // Loop through every type in the database
  ob_start();
  $u_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
  $u_explorer = preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent) ? 1 : 0;
  $u_firefox = preg_match('/Firefox/i',$u_agent) ? 1 : 0;
  $u_chrome = preg_match('/Chrome/i',$u_agent) ? 1 : 0;
  $u_safari = preg_match('/Safari/i',$u_agent) ? 1 : 0;
  $u_opera = preg_match('/Opera/i',$u_agent) ? 1 : 0;

  foreach ($mmrpg_index['types'] AS $type_token => $type_info){
    ?>
    #mmrpg .type.<?= $type_info['type_token'] ?>,
    #mmrpg .type_<?= $type_info['type_token'] ?>,
    #mmrpg .ability_type_<?= $type_info['type_token'] ?>,
    #mmrpg .battle_type_<?= $type_info['type_token'] ?>,
    #mmrpg .field_type_<?= $type_info['type_token'] ?>,
    #mmrpg .player_type_<?= $type_info['type_token'] ?>,
    #mmrpg .robot_type_<?= $type_info['type_token'] ?> {
      border-color: rgb(<?= implode(',', $type_info['type_colour_dark']) ?>) !important;
      background-color: rgb(<?= implode(',', $type_info['type_colour_light']) ?>) !important;
    }
    #mmrpg .tooltip .typelist .type_<?= $type_info['type_token'] ?>:before {
      content: "<?= $type_info['type_name'] ?> ";
    }
    <?
    // Loop through all the types again for the dual-type ability styles
    foreach ($mmrpg_index['types'] AS $type2_token => $type2_info){
      if ($type_token == $type2_token){ continue; }
      ?>
      #mmrpg .type.<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .ability_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .battle_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .field_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .player_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .robot_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?> {
        border-color: rgb(<?= implode(',', $type_info['type_colour_dark']) ?>) !important;
        background-color: rgb(<?= implode(',', $type_info['type_colour_light']) ?>) !important;
        <? if($u_opera): ?>
          background-image: -o-linear-gradient(right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
        <? elseif($u_firefox): ?>
          background-image: -moz-linear-gradient(right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
        <? elseif($u_chrome || $u_safari): ?>
          background-image: -webkit-gradient(
            linear,
            left top,
            right top,
            color-stop(0, rgb(<?= implode(',', $type_info['type_colour_light']) ?>)),
            color-stop(1, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>))
          ) !important;
          background-image: -webkit-linear-gradient(right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
        <? elseif($u_explorer): ?>
          background-image: -ms-linear-gradient(right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
        <? endif; ?>
        background-image: linear-gradient(to right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
      }
      <?
      /*
      ?>
      #mmrpg .ability_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .battle_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .field_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .player_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
      #mmrpg .robot_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?> {
        border-color: rgb(<?= implode(',', $type_info['type_colour_dark']) ?>) !important;
        background-color: rgb(<?= implode(',', $type_info['type_colour_light']) ?>) !important;
        background-image: linear-gradient(left top, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 20%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 80%) !important;
        background-image: -o-linear-gradient(left top, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 20%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 80%) !important;
        background-image: -moz-linear-gradient(left top, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 20%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 80%) !important;
        background-image: -webkit-linear-gradient(left top, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 20%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 80%) !important;
        background-image: -ms-linear-gradient(left top, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 20%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 80%) !important;
        background-image: -webkit-gradient(
          linear,
          left top,
          right bottom,
          color-stop(0.2, rgb(<?= implode(',', $type_info['type_colour_light']) ?>)),
          color-stop(0.8, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>))
        ) !important;
      }
      <?
      */
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