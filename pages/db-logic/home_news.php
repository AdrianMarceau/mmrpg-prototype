<?

// Collect a list and count of all threads in this category
$thread_display_limit = 6;
$thread_display_count = 0;
$this_category_info = array('category_id' => 1, 'category_token' => 'news');
$this_threads_array = mmrpg_website_community_category_threads($this_category_info, true, false, $thread_display_limit);
$this_threads_count = !empty($this_threads_array) ? count($this_threads_array) : 0;

// Parse the pseudo-code tag <!-- MMRPG_HOME_NEWS_MARKUP -->
$find = '<!-- MMRPG_HOME_NEWS_MARKUP -->';
if (strstr($page_content_parsed, $find)){
    ob_start();
    // Define the current date group
    $this_date_group = '';
    $this_date_group_count = 0;
    // Define the temporary timeout variables
    $this_time = time();
    $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
    // Loop through the thread array and display its contents
    if (!empty($this_threads_array)){
        foreach ($this_threads_array AS $this_thread_key => $this_thread_info){

            // Print out the thread link block
            echo mmrpg_website_community_thread_linkblock($this_thread_key, $this_thread_info, true, true);
            $thread_display_count++;

            // Break if over the limit
            if ($thread_display_count >= $thread_display_limit){ break; }

        }
    }
    $replace = ob_get_clean();
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

?>