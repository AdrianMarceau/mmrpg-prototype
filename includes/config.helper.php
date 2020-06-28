<?

// Define approved domains this project can run on
if (!isset($approved_domains)){ $approved_domains = array('localhost'); }

// Check if we're running in CLI mode and prep as required
if (php_sapi_name() === 'cli'){
    // Concatenate and parse string into the $_REQUEST var for compatibility
    if (!empty($argv)){
        parse_str(implode('&', array_slice($argv, 1)), $_REQUEST);
    }
    // If it's empty, manually set the HTTP_HOST variable with existing domains
    if (empty($_SERVER['HTTP_HOST'])){
        $_SERVER['HTTP_HOST'] = $approved_domains[0];
    }
    // If it's empty, manually set the REMOTE_ADDR variable to the localhost default
    if (empty($_SERVER['REMOTE_ADDR'])){
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }
}

// Collect the current domain for environment testing
@preg_match('#^([-~a-z0-9\.]+)#i', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']), $current_domain);
$current_domain = isset($current_domain[0]) ? $current_domain[0] : false;

// If project not running under approved domain, exit now
if (empty($current_domain) || !in_array($current_domain, $approved_domains)){
    exit('MMRPG running on illegal domain '.(!empty($current_domain) ? '"'.$current_domain.'"' : '[undefined]').'!');
}

// Check to see if we're in HTTPS mode for secure sign-in
$is_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? true : false;

?>