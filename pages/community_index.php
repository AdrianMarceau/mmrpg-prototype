<?

/*
 * COMMUNITY INDEX VIEW
 */

// Loop through the different categories and collect their threads one by one
$this_category_key = 0;
$total_threads_count = 0;
foreach ($this_categories_index AS $this_category_id => $this_category_info){

    // If this is the personal message center, do not display on index
    if ($this_category_info['category_id'] == 0 || $this_category_info['category_token'] == 'chat'){ continue; }

    // Collect a list of recent threads for this category
    $this_threads_array = mmrpg_website_community_category_threads($this_category_info, true, false, MMRPG_SETTINGS_THREADS_RECENT);
    $this_threads_count = mmrpg_website_community_category_threads_count($this_category_info, true, false);
    $this_threads_count_more = $this_threads_count - MMRPG_SETTINGS_THREADS_RECENT;
    $total_threads_count += $this_threads_count;

    // If this is the news category, ensure the threads are arranged by date only
    if ($this_category_info['category_token'] == 'news'){
        function temp_community_news_sort($thread1, $thread2){
            if ($thread1['thread_date'] > $thread2['thread_date']){ return -1; }
            elseif ($thread1['thread_date'] < $thread2['thread_date']){ return 1; }
            else { return 0; }
        }
        usort($this_threads_array, 'temp_community_news_sort');
    }

    // Define the extra links array for the header
    $temp_header_links = array();
    // If there are more threads in this category to display, show the more link
    if($this_threads_count_more > 0){
        $temp_header_links[] = array(
            'href' => 'community/'.$this_category_info['category_token'].'/',
            'title' => 'View '.($this_threads_count_more == '1' ? '1 More '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message') : $this_threads_count_more.' More '.($this_category_info['category_id'] != 0 ? 'Discussions' : 'Messages')),
            'class' => 'field_type field_type_none'
            );
    }
    // If this user has the necessary permissions, show the new thread link
    if($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_userinfo['role_level'] >= $this_category_info['category_level'] && $community_battle_points >= 10000){
        $temp_header_links[] = array(
            'href' => 'community/'.$this_category_info['category_token'].'/0/new/',
            'title' => 'Create New '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message'),
            'class' => 'field_type field_type_none'
            );
    }
    // If there are new threads in this category, show the new/recent link
    $this_threads_count_new = !empty($_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']]) ? $_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']] : 0;
    if ($this_threads_count_new > 0){
        $temp_header_links[] = array(
            'href' => 'community/'.$this_category_info['category_token'].'/new/',
            'title' => 'View '.($this_threads_count_new == '1' ? '1 Updated Thread' : $this_threads_count_new.' Updated Threads'),
            'class' => 'field_type field_type_electric'
            );
    }
    // Reverse them for display purposes
    $temp_header_links = array_reverse($temp_header_links);
    // Loop through and generate the appropriate markup to display
    if (!empty($temp_header_links)){
        foreach ($temp_header_links AS $key => $info){
            $temp_header_links[$key] = '<a class="float_link float_link2 '.(!empty($info['class']) ? $info['class'] : '').'" style="right: '.(10 + (135 * $key)).'px;" href="'.$info['href'].'">'.$info['title'].' &raquo;</a>';
        }
    }

    ?>
    <h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="clear: both; <?= $this_category_key > 0 ? 'margin-top: 6px; ' : '' ?>">
        <a class="link" href="<?= 'community/'.$this_category_info['category_token'].'/' ?>" style="display: inline;"><?= $this_category_info['category_name'] ?></a>
        <span class="count">( <?= ($this_threads_count > MMRPG_SETTINGS_THREADS_RECENT  ? MMRPG_SETTINGS_THREADS_RECENT.' of ' : '').($this_threads_count == '1' ? '1 '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message') : $this_threads_count.' '.($this_category_info['category_id'] != 0 ? 'Discussions' : 'Messages'))  ?> )</span>
        <?= !empty($temp_header_links) ? implode("\n", $temp_header_links) : '' ?>
    </h2>
    <div class="subbody threads" style="overflow: hidden; margin-bottom: 25px;">
        <?

        // Define the current date group
        $this_date_group = '';

        // Define the temporary timeout variables
        $this_time = time();
        $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

        // Loop through the thread array and display its contents
        if (!empty($this_threads_array)){
            foreach ($this_threads_array AS $this_thread_key => $this_thread_info){

                // Print out the thread link block
                echo mmrpg_website_community_thread_linkblock($this_thread_key, $this_thread_info, true, true);

            }
        }

        ?>
        <div class="link_wrapper">
            <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
        </div>
    </div>
    <?
    $this_category_key++;
}

// Define the MARKUP count variable for this page
//$this_markup_counter = '<span class="count count_header">( '.($total_threads_count == 1 ? '1 Discussion' : $total_threads_count.' Discussions').' )</span>';

?>