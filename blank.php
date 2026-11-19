<?

// Change the content header to that of HTML
$cache_time = 60 * 60 * 24;
header("Content-type: text/html; charset=UTF-8");
header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
header("Cache-control: public, max-age={$cache_time}, must-revalidate");
header("Pragma: cache");
echo('<!DOCTYPE html>'.PHP_EOL);
echo('<html></html>'.PHP_EOL);
exit();

?>
