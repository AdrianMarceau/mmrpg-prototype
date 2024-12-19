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

?>