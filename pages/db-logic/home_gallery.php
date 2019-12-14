<?

// Require the gallery data for display
$this_display_limit_default = 16;
require_once(MMRPG_CONFIG_ROOTDIR.'includes/gallery.php');

// Parse the pseudo-code tag <!-- MMRPG_HOME_GALLERY_MARKUP -->
$find = '<!-- MMRPG_HOME_GALLERY_MARKUP -->';
if (strstr($page_content_parsed, $find)){
    ob_start();
    // Define the gallery displauy limit
    $gallery_display_limit = 16;
    // Print out the generated gallery markup
    if (!empty($this_gallery_markup)){
        foreach ($this_gallery_markup AS $key => $gallery_markup){
            // If we're over the limit, break
            if ($key >= $gallery_display_limit){ break; }
            // Display this gallery image's markup
            echo $gallery_markup;
        }
        unset($this_gallery_markup);
    }
    $replace = ob_get_clean();
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

?>