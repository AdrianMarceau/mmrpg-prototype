<?

// Collect base directory for reference
$base_dir = rtrim(dirname(dirname(__FILE__)), '/').'/';

// Require the top file and the content index
require($base_dir.'top.php');
require($base_dir.'content/all.php');

// Return the content in the appropriate format and make sure it's cached
header('Content-Type: text/javascript');
header('Cache-Control: public, max-age=86400');
header('Expires: '.gmdate('D, d M Y H:i:s', time() + 86400).' GMT');
header('Pragma: public');
if (!empty($mmrpg_indexes)){
    header('HTTP/1.1 200 OK');
    echo('/* -- MMRPG CONTENT INDEX | Updated: '.MMRPG_CONFIG_CACHE_DATE.' -- */'.PHP_EOL);
    echo('let mmrpgIndex = {};'.PHP_EOL);
    foreach ($mmrpg_indexes AS $kind => $index){
        echo('mmrpgIndex.'.$kind.' = '.json_encode($index, JSON_NUMERIC_CHECK).';'.PHP_EOL);
    }
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo('/* No index data found in database! */'.PHP_EOL);
}
exit();

?>