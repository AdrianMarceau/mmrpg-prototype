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

} elseif ($request_kind === 'community-formatting-preview'){

    // Collect and parse the raw markup for the post/thread
    $raw_markup = !empty($_POST['rawMarkup']) ? strip_tags(trim($_POST['rawMarkup'])) : '';
    $parsed_markup = normalize_line_endings($raw_markup);
    $parsed_markup = nl2br(mmrpg_formatting_decode($parsed_markup));

    // Return the markup for the community formatting guide
    header('Content-type: text/plain; charset=UTF-8');
    echo('<div class="community bodytext">'.PHP_EOL);
        echo($parsed_markup);
    echo('</div>');
    exit();

} else {

    // Return a 404 header as this is an undefined request
    http_response_code(404);
    exit();

}

?>
