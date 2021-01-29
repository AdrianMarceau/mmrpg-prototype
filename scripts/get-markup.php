<?php

// Require the global config file
require('../top.php');

// Return markup based on provided arguments
$request_kind = !empty($_REQUEST['kind']) ? $_REQUEST['kind'] : false;
if ($request_kind === 'community-formatting-help'){

    // Return the markup for the community formatting guide
    header('Content-type: text/plain; charset=UTF-8');
    echo(mmrpg_formatting_help_markup());
    exit();

} else {

    // Return a 404 header as this is an undefined request
    http_response_code(404);
    exit();

}

?>
