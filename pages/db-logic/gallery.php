<?

// Require the main gallery file so we have the files and markup
$this_gallery_kind = 'screenshot';
$this_gallery_folder = 'screenshots';
require_once(MMRPG_CONFIG_ROOTDIR.'includes/gallery.php');
$this_markup_header .= ' <span class="count">( '.(!empty($mmrpg_gallery_size) ? ($mmrpg_gallery_size == 1 ? '1 Image' : $mmrpg_gallery_size.' Images') : '0 Images').' )</span>';

// Generate the gallery markup specific to this page (entire gallery)
$current_date_group = '';
$this_gallery_markup = '';
if (!empty($mmrpg_gallery_index)){

    // Loop through the gallery index, group by group, generating markup
    foreach ($mmrpg_gallery_index AS $gallery_group => $gallery_images){

        // Collect the date group (year-month only)
        list($this_date_year, $this_date_month, $this_date_day) = explode('/', $gallery_group);
        $this_date_month_name = date('F', mktime(0, 0, 0, ((int)($this_date_month)), 1));
        $this_date_group = $this_date_year.'-'.$this_date_month;

        // If the date group has changed (or started) generate the dateblock markup
        if (empty($current_date_group) || $current_date_group != $this_date_group){
            if (!empty($current_date_group)){ $this_gallery_markup .= '</div>'.PHP_EOL; }
            $current_date_group = $this_date_group;
            $this_gallery_markup .= '<div class="row">'.PHP_EOL;
                $this_gallery_markup .= '<a class="dateblock">'.PHP_EOL;
                    $this_gallery_markup .= '<span class="month">'.$this_date_month_name.'</span>';
                    $this_gallery_markup .= ' <span class="year">'.$this_date_year.'</span>';
                $this_gallery_markup .= '</a>'.PHP_EOL;
        }

        // Now loop through the individual gallert images and append the markup for each of them
        foreach ($gallery_images AS $image_key => $image_info){
            $this_gallery_markup .= mmrpg_get_gallery_thumb_markup(
                $image_info,
                $gallery_group,
                $this_gallery_kind,
                $this_gallery_folder
                );
        }

    }

    // If a date group row was started, make sure we cap it off with a closing tag
    if (!empty($current_date_group)){ $this_gallery_markup .= '</div>'.PHP_EOL; }

}

// Parse the pseudo-code tag <!-- MMRPG_SCREENSHOT_GALLERY_MARKUP -->
$find = '<!-- MMRPG_SCREENSHOT_GALLERY_MARKUP -->';
if (strstr($page_content_parsed, $find)){
    $replace = !empty($this_gallery_markup) ? $this_gallery_markup : '<div class="nocontent">- no '.$this_gallery_folder.' to display -</div>';
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

?>