<?

// Define the allowed request kinds for game content actions
$allowed_kinds = array(
    'players', 'robots', 'fields', 'abilities', 'items',
    'stars', 'challenges',
    'pages'
    );
// Define the allowed request subkinds where applicable
$allowed_subkinds = array(
    'robots' => array('masters', 'mechas', 'bosses')
    );

// Define the allowed request sources for game content actions
$allowed_sources = array(
    'github'
    );

?>