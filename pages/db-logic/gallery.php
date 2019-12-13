<?

// Require the main gallery file so we have the files and markup
require_once(MMRPG_CONFIG_ROOTDIR.'includes/gallery.php');
$this_markup_header .= ' <span class="count">( '.(!empty($this_file_count) ? ($this_file_count == 1 ? '1 Image' : $this_file_count.' Images') : '0 Images').' )</span>';

// Parse the pseudo-code tag <!-- MMRPG_SCREENSHOT_GALLERY_MARKUP -->
$find = '<!-- MMRPG_SCREENSHOT_GALLERY_MARKUP -->';
if (strstr($page_content_parsed, $find)){
    $replace = !empty($this_gallery_markup) ? implode(PHP_EOL, $this_gallery_markup) : '';
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

?>