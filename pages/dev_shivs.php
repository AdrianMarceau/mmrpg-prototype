<?php

/*
* ---------------------------
* DEV SHIVS
* ---------------------------
* Use this file to store methods, fallbacks, and other such code that does
* not-yet exist in the main codebase at the time of development but intends
* to be added in the future. Please make judicial use of function_exists to
* prevent conflicts with the main codebase when it is eventually added.
* ---------------------------
*/


// VOID MISSIONS / VOID CAULDRON 2k24

// Define a function for debugging this script, printing to error log w/ line number
if (!function_exists('console_log')){
    function console_log($line, $msg = 'checkpoint'){
        error_log('('.(basename(dirname(__FILE__)).'/'.basename(__FILE__)).'::'.$line.') '.$msg);
    }
}

// Define a function that inserts a new element into an associative array after a given key position
if (!function_exists('array_insert_after_key')){
    function array_insert_after_key(&$parent_array, $parent_key, $child_array, $child_key) {
        $new_array = [];
        foreach ($parent_array as $key => $value) {
            $new_array[$key] = $value;
            if ($key === $parent_key){ $new_array[$child_key] = $child_array; }
        }
        $parent_array = $new_array;
    }
}

// Define a function that inserts a new element into an associative array before a given key position
if (!function_exists('array_insert_before_key')){
    function array_insert_before_key(&$parent_array, $parent_key, $child_array, $child_key) {
        $new_array = [];
        foreach ($parent_array as $key => $value) {
            if ($key === $parent_key) { $new_array[$child_key] = $child_array; }
            $new_array[$key] = $value;
        }
        $parent_array = $new_array;
    }
}

// Define a function that takes a parent array and they rearranges the provided keys in the order provided
if (!function_exists('array_rearrange_keys')){
    function array_rearrange_keys(&$parent_array, $ordered_keys) {
        $new_array = [];
        $ordered_items = [];
        // Collect items by the specified order
        foreach ($ordered_keys as $key) {
            if (array_key_exists($key, $parent_array)) {
                $ordered_items[$key] = $parent_array[$key];
                unset($parent_array[$key]);
            }
        }
        // Build the new array with ordered items in specified sequence
        $keys_added = false;
        foreach ($parent_array as $key => $value) {
            if (!$keys_added && empty($new_array)) {
                $new_array = array_merge($ordered_items, $new_array);
                $keys_added = true;
            }
            $new_array[$key] = $value;
        }
        // If $ordered_keys appear later in $parent_array, append them to the end
        if (!$keys_added) {
            $new_array = array_merge($new_array, $ordered_items);
        }
        $parent_array = $new_array;
    }
}

// Extend the Font Awesome (5) stylesheet / CSS library to allow
// for custom HTML entities to be used in place of icons
if ((int)(substr(MMRPG_CONFIG_CACHE_DATE, 0, 4)) <= 2024){
    ob_start();
    ?>
    <style type="text/css">
        /* Extended Font Awesome 5 */
        /* Custom HTML Entity */
        .fa-entity {
            font-family: inherit; /* Use the default font or specify one */
            font-style: normal;
            font-weight: normal;
            display: inline-block;
            text-align: center;
            line-height: 1; /* Adjust to match FA5 icons */
            vertical-align: middle; /* Align to FA icons */
        }
        .fa-entity > span {
            display: block;
            margin: 0 auto;
            padding: 0;
            line-height: 1; /* Adjust to match FA5 icons */
            vertical-align: middle; /* Align to FA icons */
            user-select: none; /* Prevent text selection */
        }
    </style>
    <?
    $markup = preg_replace('/\n\s+/', "\n", trim(ob_get_clean()));
    $website_include_stylesheets .= $markup.PHP_EOL;
}

?>