<?
// Require the application top
define('MMRPG_INDEX_STYLES', true);
require_once('../top.php');

// Change the content header to that of CSS
$cache_time = 60 * 60 * 24;
header("Content-type: text/css; charset=UTF-8");
header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
header("Cache-control: public, max-age={$cache_time}, must-revalidate");
header("Pragma: cache");

// Collect the cache style name from the headers, else die if not set
$base_style_dir = MMRPG_CONFIG_ROOTDIR.'styles/';
$base_style_name = !empty($_REQUEST['file']) && preg_match('/^([-_a-z0-9\.]+)\.css$/i', $_REQUEST['file']) ? $_REQUEST['file'] : '';
$base_style_names = !empty($_REQUEST['files']) && preg_match('/^([,-_a-z0-9\.]+)\.css$/i', $_REQUEST['files']) ? explode(',', $_REQUEST['files']) : array($base_style_name);
if (empty($base_style_names)){ die('/* -- Invalid file name(s)! -- */'); }

// Define the cache file name and path given everything we've learned
$cache_file_name = 'cache.'.str_replace('.css', '', implode('-', $base_style_names)).'.css';
$cache_file_path = MMRPG_CONFIG_CACHE_PATH.'styles/'.$cache_file_name;
// Check to see if a file already exists and collect its last-modified date
if (file_exists($cache_file_path)){ $cache_file_exists = true; $cache_file_date = date('Ymd-Hi', filemtime($cache_file_path)); }
else { $cache_file_exists = false; $cache_file_date = '00000000-0000'; }

// LOAD FROM CACHE if data exists and is current, otherwise continue so script can refresh and replace
$flag_cache_styles = defined('MMRPG_CONFIG_CACHE_STYLES') ? MMRPG_CONFIG_CACHE_STYLES : MMRPG_CONFIG_CACHE_INDEXES;
if ($flag_cache_styles === true && $cache_file_exists && $cache_file_date >= MMRPG_CONFIG_CACHE_DATE){
    $cache_file_markup = file_get_contents($cache_file_path);
    header('Content-type: text/css; charset=UTF-8');
    echo($cache_file_markup);
    exit();
}

// Define the variable to hold style markup and prepend to append
$cache_style_markup = '';

// Loop through the collected file name(s) and append markup
foreach ($base_style_names AS $base_style_name){

    // Require the static CSS file if the exists
    ob_start();
    $static_file_path = $base_style_dir.$base_style_name;
    if (file_exists($static_file_path)){ require_once($static_file_path); }
    $cache_style_markup .= ob_get_clean();

    // Require the dynamic CSS additions if such a file exists
    ob_start();
    $dynamic_file_path = $base_style_dir.'style_'.str_replace('.css', '.php', $base_style_name);
    if (file_exists($dynamic_file_path)){ require_once($dynamic_file_path); }
    $cache_style_markup .= ob_get_clean();

}

// If there wasn't any markup collected so far, produce a 404 error
if (empty($cache_style_markup)){
    header('HTTP/1.0 404 Not Found');
    echo('/* -- Stylesheet does not exist! -- */');
    exit();
}

// Compress the CSS markup before saving it
$cache_style_markup = preg_replace('/\s+/', ' ', $cache_style_markup);
$cache_style_markup = str_replace('; ', ';', $cache_style_markup);

// Generate the stylesheet header comment and prepend to markup
$cache_style_headers = '';
$cache_style_headers .= '/* '.PHP_EOL;
$cache_style_headers .= ' * MMRPG Prototype Stylesheet '.PHP_EOL;
$cache_style_headers .= ' * Filename'.(count($base_style_names) > 1 ? 's' : '').': '.implode(', ', $base_style_names).' '.PHP_EOL;
$cache_style_headers .= ' * Updated: '.($cache_file_exists ? $cache_file_date : date('Ymd-Hi')).' '.PHP_EOL;
$cache_style_headers .= ' */'.PHP_EOL;
$cache_style_markup = $cache_style_headers.trim($cache_style_markup);

// Write the index to a cache file, if caching is enabled
if ($flag_cache_styles === true){
    // Write the index to a cache file, if caching is enabled
    $this_cache_file = fopen($cache_file_path, 'w');
    fwrite($this_cache_file, $cache_style_markup);
    fclose($this_cache_file);
}

// Print out the final generated CSS markup
echo $cache_style_markup;

?>