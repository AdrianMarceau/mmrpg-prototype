<?

// Update the MARKUP variables for this page
$this_markup_header .= ' <span class="count">( Last Updated '.preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2,4})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE).' )</span>';

// Parse the pseudo-code tag <!-- MMRPG_LOAD_HOME_LEADERBOARD() -->
$find = '<!-- MMRPG_LOAD_HOME_LEADERBOARD() -->';
if (strstr($page_content_parsed, $find)){
    $page_content_parsed = str_replace($find, '', $page_content_parsed);
    require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/home_leaderboard.php');
}

// Parse the pseudo-code tag <!-- MMRPG_LOAD_HOME_GALLERY() -->
$find = '<!-- MMRPG_LOAD_HOME_GALLERY() -->';
if (strstr($page_content_parsed, $find)){
    $page_content_parsed = str_replace($find, '', $page_content_parsed);
    require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/home_gallery.php');
}

// Parse the pseudo-code tag <!-- MMRPG_LOAD_HOME_NEWS() -->
$find = '<!-- MMRPG_LOAD_HOME_NEWS() -->';
if (strstr($page_content_parsed, $find)){
    $page_content_parsed = str_replace($find, '', $page_content_parsed);
    require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/home_news.php');
}

?>