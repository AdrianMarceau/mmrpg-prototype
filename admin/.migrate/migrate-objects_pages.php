<?

ob_echo('');
ob_echo('====================================');
ob_echo('|   START WEBSITE PAGE MIGRATION   |');
ob_echo('====================================');
ob_echo('');

// Collect an index of all valid pages from the database
$page_index = $db->get_array_list("SELECT * FROM mmrpg_website_pages ORDER BY page_id ASC;", 'page_id');
//echo('<pre>$page_index = '.print_r($page_index, true).'</pre>');
//exit();

// Create a "fake" website page with empty felds as is standard
$pseudo_page = array(
    'parent_id' => 0,
    'page_id' => 0,
    'page_token' => 'page',
    'page_name' => 'Page',
    'page_url' => 'page/',
    'page_title' => 'Page',
    'page_content' => '<p>Lorem ipsum dolar sit amet</p>',
    'page_seo_title' => 'Page',
    'page_seo_keywords' => 'lorem,ipsum,dolar,sit,amet',
    'page_seo_description' => 'Lorem ipsum dolar sit amet',
    'page_date_created' => 0,
    'page_date_modified' => 0,
    'page_flag_hidden' => 0,
    'page_flag_published' => 0,
    'page_order' => 0
    );

// Prepend the empty website page to beginning of array
$page_index = array_merge(array(0 => $pseudo_page), $page_index);

//echo('<pre>$page_index = '.print_r($page_index, true).'</pre>');
//exit();

// Pre-define the base page content dir
define('MMRPG_CHALLENGES_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/pages/');

// Count the number of pages that we'll be looping through
$page_index_size = count($page_index);
$count_pad_length = strlen($page_index_size);

// Print out the stats before we start
ob_echo('Total Website Pages in Index: '.$page_index_size);
ob_echo('');

sleep(1);

$event_pages_exported = array();

// MIGRATE ACTUAL CHALLENGES
$page_key = -1; $page_num = 0;
foreach ($page_index AS $page_data){
    $page_key++; $page_num++;
    $count_string = '('.$page_num.' of '.$page_index_size.')';

    $page_id = $page_data['page_id'];
    //$page_token = 'page-'.str_pad($page_id, 4, '0', STR_PAD_LEFT);
    $page_token = str_replace('/', '_', trim($page_data['page_url'], '/'));

    ob_echo('----------');
    ob_echo('Processing website page ID '.$page_id.' '.$count_string);
    ob_flush();

    $content_path = MMRPG_CHALLENGES_NEW_CONTENT_DIR.(empty($page_id) ? '.page' : $page_token).'/';
    ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deletedir_or_exit($content_path); }
    mkdir_or_exit($content_path);

    // Export the page content as a separate HTML file as that's what it is
    $content_html_path = $content_path.'content.html';
    $content_html_markup = normalize_file_markup($page_data['page_content']);
    unset($page_data['page_content']);
    ob_echo('- export page markup to '.clean_path($content_html_path));
    $h = fopen($content_html_path, 'w');
    fwrite($h, trim($content_html_markup).PHP_EOL);
    fclose($h);

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = clean_json_content_array('page', $page_data, false, true);
    ob_echo('- export other page data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, normalize_file_markup(json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK)));
    fclose($h);
    if (file_exists($content_json_path)){ $event_pages_exported[] = basename($content_json_path); }

    if ($migration_limit && $page_num >= $migration_limit){ break; }

}


ob_echo('----------');

ob_echo('');
ob_echo('Website Pages Exported: '.count($event_pages_exported).' / '.$page_index_size);

sleep(1);

ob_echo('');
ob_echo('====================================');
ob_echo('|    END WEBSITE PAGE MIGRATION    |');
ob_echo('====================================');
ob_echo('');

?>