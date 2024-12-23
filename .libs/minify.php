<?php
// https://github.com/matthiasmullie/minify
$lib = basename(__FILE__, '.php');
$path = rtrim(dirname(__FILE__), '/').'/'.$lib.'/';
error_log('$lib = '.print_r($lib, true));
error_log('$path = '.print_r($path, true));
require_once($path.'src/Minify.php');
require_once($path.'src/CSS.php');
require_once($path.'src/JS.php');
require_once($path.'src/Exception.php');
require_once($path.'src/Exceptions/BasicException.php');
require_once($path.'src/Exceptions/FileImportException.php');
require_once($path.'src/Exceptions/IOException.php');
//use MatthiasMullie\Minify;

?>
