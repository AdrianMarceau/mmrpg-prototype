<?

ob_echo('');
ob_echo('============================');
ob_echo('|   START PAGE MIGRATION   |');
ob_echo('============================');
ob_echo('');

// Collect an index of all valid pages from the database
$page_fields = cms_website_page::get_fields(true);
$page_index = $db->get_array_list("SELECT {$page_fields} FROM mmrpg_website_pages ORDER BY page_order ASC", 'page_id');

// If there's a filter present, remove all tokens not in the filter
if (!empty($migration_filter)){
    $old_page_index = $page_index;
    $page_index = array();
    foreach ($migration_filter AS $page_token){
        if (isset($old_page_index[$page_token])){
            $page_index[$page_token] = $old_page_index[$page_token];
        }
    }
    unset($old_page_index);
}

// Pre-define the base page content dir
define('MMRPG_PAGES_NEW_CONTENT_DIR', MMRPG_CONFIG_ROOTDIR.'content/pages/');

// Count the number of pages that we'll be looping through
$page_index_size = count($page_index);
$count_pad_length = strlen($page_index_size);

// Print out the stats before we start
ob_echo('Total Pages in Database: '.$page_index_size);
ob_echo('');

sleep(1);

$page_data_files_exported = array();

// MIGRATE ACTUAL PAGES
$page_key = -1; $page_num = 0;
foreach ($page_index AS $page_id => $page_data){
    $page_key++; $page_num++;
    $count_string = '('.$page_num.' of '.$page_index_size.')';

    $page_token = $page_data['page_token'];
    $page_url = $page_data['page_url'];

    ob_echo('----------');
    ob_echo('Processing page "'.$page_url.'" '.$count_string);
    ob_flush();


    $content_path = MMRPG_PAGES_NEW_CONTENT_DIR.(str_replace('/', '_', trim($page_url, '/'))).'/';
    //ob_echo('-- $content_path = '.clean_path($content_path));
    if (file_exists($content_path)){ deleteDir($content_path); }
    mkdir($content_path);

    // And then write the rest of the non-function data into a json file
    $content_json_path = $content_path.'data.json';
    $content_json_data = array();
    $content_json_data['parent_token'] = !empty($page_data['parent_id']) ? $page_index[$page_data['parent_id']]['page_token'] : '';
    $content_json_data = array_merge($content_json_data, clean_json_content_array('page', $page_data));
    unset($content_json_data['parent_id']);
    ob_echo('- export all other data to '.clean_path($content_json_path));
    $h = fopen($content_json_path, 'w');
    fwrite($h, json_encode($content_json_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
    fclose($h);

    // Add to the list of data files exported
    $page_data_files_exported[] = basename($content_json_path);

    if ($migration_limit && $page_num >= $migration_limit){ break; }

}


ob_echo('----------');

ob_echo('');
ob_echo('Page Data Files Copied: '.count($page_data_files_exported).' / '.$page_index_size);


sleep(1);

ob_echo('');
ob_echo('============================');
ob_echo('|    END PAGE MIGRATION    |');
ob_echo('============================');
ob_echo('');

?>