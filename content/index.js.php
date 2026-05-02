<?

// Collect base directory for reference
$base_dir = rtrim(dirname(dirname(__FILE__)), '/').'/';

// Require the top file and the content index
require($base_dir.'top.php');

// Set the content type to the appropriate format and make sure it's cached
header('Content-Type: text/javascript');
header('Cache-Control: public, max-age=86400');
header('Expires: '.gmdate('D, d M Y H:i:s', time() + 86400).' GMT');
header('Pragma: public');

// If there's already a locally-cached version of this file, grab that instead
$cached_files_enabled = true;
$cached_files_dir = MMRPG_CONFIG_ROOTDIR.'.cache/indexes/';
$cached_date_cutoff = substr(MMRPG_CONFIG_CACHE_DATE, 0, 8);
$cached_markup_file = 'cache.mmrpg-content-objects.js';
if ($cached_files_enabled
    && file_exists($cached_files_dir.$cached_markup_file)
    && date('Ymd', filemtime($cached_files_dir.$cached_markup_file)) >= $cached_date_cutoff){
    //error_log(basename(__FILE__).' is pulling mmrpg content index js from cache !');
    header('HTTP/1.1 200 OK');
    $content_index_js = file_get_contents($cached_files_dir.$cached_markup_file);
    echo(trim($content_index_js).PHP_EOL);
    exit();
}

// Otherwise we will have to generate it from scratch at runtime
require($base_dir.'content/all.php');
//error_log(basename(__FILE__).' is generating mmrpg content index js from scratch ...');
if (!empty($mmrpg_indexes)){
    header('HTTP/1.1 200 OK');
    ob_start();
    echo('/* -- MMRPG CONTENT INDEX | Updated: '.MMRPG_CONFIG_CACHE_DATE.' -- */'.PHP_EOL);
    echo('let mmrpgIndex = {};'.PHP_EOL);
    foreach ($mmrpg_indexes AS $kind => $index){
        echo('mmrpgIndex.'.$kind.' = '.json_encode($index, JSON_NUMERIC_CHECK).';'.PHP_EOL);
    }
    $content_index_js = ob_get_clean();
    if (!empty($content_index_js)){
        if (file_exists($cached_files_dir.$cached_markup_file)){ @unlink($cached_files_dir.$cached_markup_file); }
        $f = fopen($cached_files_dir.$cached_markup_file, 'w');
        fwrite($f, $content_index_js);
        fclose($f);
    }
    echo(trim($content_index_js).PHP_EOL);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo('/* No index data found in database! */'.PHP_EOL);
}
exit();

?>