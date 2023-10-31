<?
// Define the class that will act as the mmrpg website file
class cms_website {

    public static function foo($token){


    }


    // Define a function to pulling a given markup cache file if it exists and it's too old
    public static function load_cached_markup($markup_kind, $cache_token, $cache_time = null){

        // Define the path for this markup given the context
        $cache_base_path = MMRPG_CONFIG_CACHE_PATH.'markup/';
        if (!file_exists($cache_base_path)){ mkdir($cache_base_path, 0777, true); }
        //error_log('$cache_base_path = '.print_r($cache_base_path, true));

        // Define the filename given the object kind and cache token
        $cache_filename = 'cache.'.$markup_kind.'.markup-'.$cache_token.'.html';
        //error_log('cache_filename = '.print_r($cache_filename, true));

        // Collect the global cache time and break it down to an exact time
        if (empty($cache_time)){
            list($new_cache_date, $new_cache_time) = explode('-', MMRPG_CONFIG_CACHE_DATE);
            $yyyy = substr($new_cache_date, 0, 4); $mm = substr($new_cache_date, 4, 2); $dd = substr($new_cache_date, 6, 2);
            $hh = substr($new_cache_time, 0, 2); $ii = substr($new_cache_time, 2, 2);
            $mmrpg_config_cache_time = mktime($hh, $ii, 0, $mm, $dd, $yyyy);
            //error_log('$mmrpg_config_cache_time = '.print_r($mmrpg_config_cache_time, true));
            $cache_time = $mmrpg_config_cache_time;
        }

        // If the files already exists but they're too old, delete them
        $delete_existing = false;
        if (file_exists($cache_base_path.$cache_filename)){
            $filemtime = filemtime($cache_base_path.$cache_filename);
            //error_log('$filemtime = '.print_r($filemtime, true));
            if ($filemtime < $cache_time){ $delete_existing = true; }
            elseif (!MMRPG_CONFIG_CACHE_INDEXES){ $delete_existing = true; }
        }
        if (!empty($delete_existing)){ unlink($cache_base_path.$cache_filename); }

        // If the file still exists, we can load it and decode it
        if (file_exists($cache_base_path.$cache_filename)){
            //error_log('Loading cache file: '.$cache_filename);
            $markup_content = file_get_contents($cache_base_path.$cache_filename);
            //error_log('$markup_content = '.print_r($markup_content, true));
            return $markup_content;
        }

        // Return false if we weren't able to find an appropriate file
        return false;

    }

    // Define a function for saving a given markup cache into a file so that we can load it later
    public static function save_cached_markup($markup_kind, $cache_token, $markup_content){

        // If we're not supposed to be caching markup then don't save them
        if (!MMRPG_CONFIG_CACHE_INDEXES){ return false; }

        // Define the path for this markup given the context
        $cache_base_path = MMRPG_CONFIG_CACHE_PATH.'markup/';
        if (!file_exists($cache_base_path)){ mkdir($cache_base_path, 0777, true); }
        //error_log('$cache_base_path = '.print_r($cache_base_path, true));

        // Define the filename given the object kind and cache token
        $cache_filename = 'cache.'.$markup_kind.'.markup-'.$cache_token.'.html';
        //error_log('cache_filename = '.print_r($cache_filename, true));

        // Save the markup data to a file
        file_put_contents($cache_base_path.$cache_filename, $markup_content);
        //error_log('Saving cache file: '.$cache_filename);

        // Return true on success
        return true;

    }

}
?>