<?php

// Require the global top file
require('../top.php');
require('api-functions.php');
critical_api_error('Request Error | Unsupported request or malformed URL structure!', __FILE__, __LINE__);

?>