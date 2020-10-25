<?

// Start the output buffer to ensure stuff is printed in the right order
if (!ob_get_status()){ ob_start(); }

// Define a function for echoing debug info with a quick toggle var
$is_debug = true;
function debug_echo($echo){ global $is_debug; if ($is_debug){ echo($echo.PHP_EOL); } }

// Define a function for exiting the script with status first, debug last
function exit_action($status_line){ $output = trim(ob_get_clean()); echo(trim($status_line).PHP_EOL); echo(!empty($output) ? str_repeat('-', 50).PHP_EOL.$output : ''); exit(); }


?>