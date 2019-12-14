<?

// Collect the tokens for all the preview types to display
$mmrpg_database_preview_types = array();
$mmrpg_database_preview_types_count = 1;
foreach ($mmrpg_database_robots AS $token => $info){
    if (!empty($info['robot_core']) && !in_array($info['robot_core'], $mmrpg_database_preview_types)){
        $mmrpg_database_preview_types[] = $info['robot_core'];
        $mmrpg_database_preview_types_count++;
    }
}

// Parse the pseudo-code tag <!-- MMRPG_DATABASE_PLAYERS_COUNT -->
$find = '<!-- MMRPG_DATABASE_PLAYERS_COUNT -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_players_count_complete.' / '.$mmrpg_database_players_count;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}
// Parse the pseudo-code tag <!-- MMRPG_DATABASE_PLAYERS_LINKS -->
$find = '<!-- MMRPG_DATABASE_PLAYERS_LINKS -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_players_links;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

// Parse the pseudo-code tag <!-- MMRPG_DATABASE_ROBOTS_COUNT -->
$find = '<!-- MMRPG_DATABASE_ROBOTS_COUNT -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_robots_count_complete.' / '.$mmrpg_database_robots_count;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}
// Parse the pseudo-code tag <!-- MMRPG_DATABASE_ROBOTS_LINKS -->
$find = '<!-- MMRPG_DATABASE_ROBOTS_LINKS -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_robots_links;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

// Parse the pseudo-code tag <!-- MMRPG_DATABASE_MECHAS_COUNT -->
$find = '<!-- MMRPG_DATABASE_MECHAS_COUNT -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_mechas_count_complete.' / '.$mmrpg_database_mechas_count;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}
// Parse the pseudo-code tag <!-- MMRPG_DATABASE_MECHAS_LINKS -->
$find = '<!-- MMRPG_DATABASE_MECHAS_LINKS -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_mechas_links;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

// Parse the pseudo-code tag <!-- MMRPG_DATABASE_BOSSES_COUNT -->
$find = '<!-- MMRPG_DATABASE_BOSSES_COUNT -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_bosses_count_complete.' / '.$mmrpg_database_bosses_count;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}
// Parse the pseudo-code tag <!-- MMRPG_DATABASE_BOSSES_LINKS -->
$find = '<!-- MMRPG_DATABASE_BOSSES_LINKS -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_bosses_links;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

// Parse the pseudo-code tag <!-- MMRPG_DATABASE_ABILITIES_COUNT -->
$find = '<!-- MMRPG_DATABASE_ABILITIES_COUNT -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_abilities_count_complete.' / '.$mmrpg_database_abilities_count;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}
// Parse the pseudo-code tag <!-- MMRPG_DATABASE_ABILITIES_LINKS -->
$find = '<!-- MMRPG_DATABASE_ABILITIES_LINKS -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_abilities_links;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

// Parse the pseudo-code tag <!-- MMRPG_DATABASE_ITEMS_COUNT -->
$find = '<!-- MMRPG_DATABASE_ITEMS_COUNT -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_items_count_complete.' / '.$mmrpg_database_items_count;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}
// Parse the pseudo-code tag <!-- MMRPG_DATABASE_ITEMS_LINKS -->
$find = '<!-- MMRPG_DATABASE_ITEMS_LINKS -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_items_links;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

// Parse the pseudo-code tag <!-- MMRPG_DATABASE_FIELDS_COUNT -->
$find = '<!-- MMRPG_DATABASE_FIELDS_COUNT -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_fields_count_complete.' / '.$mmrpg_database_fields_count;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}
// Parse the pseudo-code tag <!-- MMRPG_DATABASE_FIELDS_LINKS -->
$find = '<!-- MMRPG_DATABASE_FIELDS_LINKS -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_fields_links;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

// Parse the pseudo-code tag <!-- MMRPG_DATABASE_TYPES_COUNT -->
$find = '<!-- MMRPG_DATABASE_TYPES_COUNT -->';
if (strstr($page_content_parsed, $find)){
    $replace = $mmrpg_database_preview_types_count.' / '.$mmrpg_database_types_count;
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}
// Parse the pseudo-code tag <!-- MMRPG_DATABASE_TYPES_LIST -->
$find = '<!-- MMRPG_DATABASE_TYPES_LIST -->';
if (strstr($page_content_parsed, $find)){
    ob_start();
    echo '<li><strong class="type_block ability_type ability_type_none">Neutral</strong></li>';
    foreach ($mmrpg_database_types AS $type_token => $type_array){
        if ($type_token == 'none' || !in_array($type_token, $mmrpg_database_preview_types)){ continue; }
        echo '<li><strong class="type_block ability_type ability_type_'.$type_token.'">'.$type_array['type_name'].'</strong></li>';
    }
    $replace = ob_get_clean();
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

?>