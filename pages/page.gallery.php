<?
/*
 * INDEX PAGE : GALLERY
 */

// Define the SEO variables for this page
$this_seo_title = 'Gallery | '.$this_seo_title;
$this_seo_description = 'The Mega Man RPG Prototype has gone through many, many changes over the last few years and - luckily - I\'m pretty good at taking screenshots! Each of these images is a window into the game\'s development and progress at that point in time, and together they provide a clearer picture of how far the prototype since it\'s early days of a single-button main menu and one hours-long eight-vs-eight battle. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Screenshot Gallery';
$this_graph_data['description'] = 'The Mega Man RPG Prototype has gone through many, many changes over the last few years and - luckily - I\'m pretty good at taking screenshots! Each of these images is a window into the game\'s development and progress at that point in time, and together they provide a clearer picture of how far the prototype since it\'s early days of a single-button main menu and one hours-long eight-vs-eight battle.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Require the gallery data file
require_once('data/gallery.php');

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Gallery <span class="count">( '.(!empty($this_file_count) ? ($this_file_count == 1 ? '1 Image' : $this_file_count.' Images') : '0 Images').' )</span>';

// Start generating the page markup
ob_start();
?>
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Mega Man RPG Prototype Screenshots</h2>
<div class="subbody">
  
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_04" style="background-image: url(images/robots/fire-man/sprite_left_80x80.png);">Fire Man</div></div>
  <p class="text">The <strong>Mega Man RPG Prototype</strong> has gone through many, many changes over the last few years and - luckily - I'm pretty good at taking screenshots! Each of these images is a window into the game's development and progress at that point in time, and together they provide a clearer picture of how far the prototype since it's early days of a single-button main menu and one hours-long eight-vs-eight battle. :P  Hover over any of the thumbnails below to see a brief description of its contents and date, or click it to view the screenshot at full resolution.  Please enjoy the images, and <a href="contact/">let me know</a> if you have any questions.</p>
  
</div>

<div class="gallery">
  <div class="wrapper">
  <?
  
  // Print out the generated gallery markup
  //echo $this_gallery_markup;
  //die('<pre>'.print_r($this_gallery_markup, true).'</pre>');
  if (!empty($this_gallery_markup)){
    foreach ($this_gallery_markup AS $key => $gallery_markup){
      // Display this gallery image's markup
      echo $gallery_markup;
    }
  }

  ?>
  
  </div>
</div>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>