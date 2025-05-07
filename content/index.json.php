<?

// Collect base directory for reference
$base_dir = rtrim(dirname(dirname(__FILE__)), '/').'/';

// Require the top file and the content index
require($base_dir.'top.php');
require($base_dir.'content/all.php');

// Return the content in the appropriate format
header('Content-Type: application/json');
$return_array = array();
$return_array['status'] = '';
$return_array['message'] = '';
$return_array['data'] = array();
$return_array['time'] = time();
$return_array['cache'] = MMRPG_CONFIG_CACHE_DATE;
if (!empty($mmrpg_indexes)){
    header('HTTP/1.1 200 OK');
    $return_array['status'] = 'success';
    $return_array['message'] = 'Index data returned successfully.';
    $return_array['data'] = $mmrpg_indexes;
} else {
    header('HTTP/1.1 500 Internal Server Error');
    $return_array['status'] = 'error';
    $return_array['message'] = 'No index data found in database.';
}
header('Content-Length: '.strlen(json_encode($return_array)));
echo(json_encode($return_array));
exit();

?>