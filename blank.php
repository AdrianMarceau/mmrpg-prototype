<?
// Change the content header to that of HTML
$cache_time = 60 * 60 * 24;
header("Content-type: text/html; charset=UTF-8");
header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s", (time()+$cache_time)) . " GMT");
header("Cache-control: public, max-age={$cache_time}, must-revalidate");
header("Pragma: cache");
exit();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Blank</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
</head>
<body id="mmrpg">
</body>
</html>