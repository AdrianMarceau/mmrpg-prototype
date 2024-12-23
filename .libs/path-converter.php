<?php
// https://github.com/matthiasmullie/path-converter
$lib = basename(__FILE__, '.php');
$path = rtrim(dirname(__FILE__), '/').'/'.$lib.'/';
//error_log('$lib = '.print_r($lib, true));
//error_log('$path = '.print_r($path, true));
require_once($path.'src/ConverterInterface.php');
require_once($path.'src/Converter.php');

?>
