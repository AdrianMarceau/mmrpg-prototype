<?

// Require the gallery data for display
$this_gallery_kind = 'screenshot';
$this_gallery_folder = 'screenshots';
require_once(MMRPG_CONFIG_ROOTDIR.'includes/gallery.php');

// Generate the gallery markup specific to this page (entire gallery)
$gallery_display_limit = 20;
$this_gallery_markup = '';
if (!empty($mmrpg_gallery_index)){

    // Loop through the gallery index, group by group, generating markup
    foreach ($mmrpg_gallery_index AS $gallery_group => $gallery_images){

        // Now loop through the individual gallert images and append the markup for each of them
        $image_count = 0;
        foreach ($gallery_images AS $image_key => $image_info){
            $this_gallery_markup .= mmrpg_get_gallery_thumb_markup(
                $image_info,
                $gallery_group,
                $this_gallery_kind,
                $this_gallery_folder
                );
            $image_count++;
            if ($image_count >= $gallery_display_limit){ break; }
        }

        // Break after the first group (we only need to show one here)
        break;

    }

    // If a date group row was started, make sure we cap it off with a closing tag
    if (!empty($current_date_group)){ $this_gallery_markup .= '</div>'.PHP_EOL; }

}

// Parse the pseudo-code tag <!-- MMRPG_HOME_GALLERY_MARKUP -->
$find = '<!-- MMRPG_HOME_GALLERY_MARKUP -->';
if (strstr($page_content_parsed, $find)){
    $replace = !empty($this_gallery_markup) ? $this_gallery_markup : '<div class="nocontent">- no '.$this_gallery_folder.' to display -</div>';
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

?>