<?

// Include the TOP file
require_once('../top.php');

// Collect the hash and the url from the query, else die
$image_url = !empty($_GET['url']) ? $_GET['url'] : false;
$image_hash = !empty($_GET['hash']) ? $_GET['hash'] : false;
if (empty($image_url) || empty($image_hash)){
    header('HTTP/1.0 404 Not Found');
    header('Details: Image URL or Hash Not Provided');
    exit;
}

// If the URL in question is not a valid image file, die now
$type_matches = array();
if (!preg_match('/\.(jpg|jpeg|png|ico|bmp|svg)(\?(.*)?)?$/i', $image_url, $type_matches)){
    header('HTTP/1.0 404 Not Found');
    header('Details: Not a Valid Image URL');
    exit;
}

// Check to see what the actual image hash is, die if incorrect
$actual_image_hash = md5(MMRPG_SETTINGS_IMAGEPROXY_SALT . $image_url);
if ($image_hash !== $actual_image_hash){
    header('HTTP/1.0 404 Not Found');
    header('Details: Invalid Image Hash');
    exit;
}

// Otherwise, define the hash path for where the image should be
$image_hash_path = MMRPG_CONFIG_CACHE_PATH.'imageproxy/'.$image_hash.$type_matches[0];

// If the file does NOT exist yet, we need to try to download it
if (!file_exists($image_hash_path)){

    // Collect the headers for the image URL to ensure it's real and die if not image
    $image_headers = @get_headers('http://'.$image_url, 1);
    $image_content_type = !empty($image_headers['Content-Type']) ? $image_headers['Content-Type'] : false;
    if (is_array($image_content_type)){ $image_content_type = array_pop($image_content_type); }
    if (!strstr($image_content_type, 'image/')){
        header('HTTP/1.0 404 Not Found');
        header('Details: Image Not Valid or No Longer Exists');
        exit;
    }

    // We need to try and download the image locally before we can serve it
    file_put_contents($image_hash_path, file_get_contents('http://'.$image_url));

}

// If not file exists, we have a problem and should exit now
if (!file_exists($image_hash_path)){
    header('HTTP/1.0 404 Not Found');
    header('Details: Image Could Not Be Downloaded');
    exit;
}

// And now we can serve the image as if it were our own
$image_mime_type = mime_content_type($image_hash_path);
$image_file_size = filesize($image_hash_path);
$image_last_modified = date(DATE_RFC2822, filemtime($image_hash_path));
header('Content-type: '.$image_mime_type);
header('Content-Length: '.$image_file_size);
header('Last-Modified: '.$image_last_modified);
readfile($image_hash_path);
exit();

?>