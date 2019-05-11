<?

/*
 * COMMUNITY CATEGORY VIEW
 */

// Update the SEO variables for this page
$this_seo_title = $this_category_info['category_name'].' | '.$this_seo_title;
$this_seo_description = strip_tags($this_category_info['category_description']);

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Community Forums | '.$this_category_info['category_name'];
$this_graph_data['description'] = strip_tags($this_category_info['category_description']);
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png?'.MMRPG_CONFIG_CACHE_DATE;
//$this_graph_data['type'] = 'website';

// Count the total number of threads for this category
$this_threads_count = mmrpg_website_community_category_threads_count($this_category_info, false, false);

// Calc how many pages are required and collect current (fix if invalid)
$num_pages_required = ceil($this_threads_count / MMRPG_SETTINGS_THREADS_PERPAGE);
$current_page_num = $this_current_num;
if ($current_page_num <= 1){ $current_page_num = 1; }
elseif ($current_page_num > $num_pages_required){ $current_page_num = $num_pages_required; }

// Collect a list and count of all threads in this category (with offset given current page num)
$this_threads_array = mmrpg_website_community_category_threads($this_category_info, false, false, MMRPG_SETTINGS_THREADS_PERPAGE, (($current_page_num - 1) * MMRPG_SETTINGS_THREADS_PERPAGE));
//die('<pre>'.print_r($this_threads_array, true).'</pre>');

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype '.$this_category_info['category_name'].'';


?>
<h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    <a class="link" style="display: inline;" href="<?= str_replace($this_category_info['category_token'].'/', '', $_GET['this_current_url']) ?>">Community</a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
    <a class="link" style="display: inline;" href="<?= $_GET['this_current_url'] ?>"><?= $this_category_info['category_name'] ?></a>
    <span class="count float"><?= $this_threads_count == '1' ? '1 '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message') : (count($this_threads_array).' of ').$this_threads_count.' '.($this_category_info['category_id'] != 0 ? 'Discussions' : 'Messages')  ?></span>
</h2>
<?
// Only display the category body if not personal
if ($this_category_info['category_id'] != 0){
    ?>
    <div class="subbody">
    <?= mmrpg_website_text_float_robot_markup(MMRPG_SETTINGS_CURRENT_FIELDMECHA, 'right', '0'.mt_rand(0, 2)) ?>
    <p class="text"><?= $this_category_info['category_description'] ?></p>
    <div style="clear:both;">&nbsp;</div>
    <?
    // Add the new threads option if there are new threads to view
    $this_threads_count_new = !empty($_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']]) ? $_SESSION['COMMUNITY']['threads_new_categories'][$this_category_info['category_id']] : 0;
    if ($this_threads_count_new > 0){
        ?>
        <div class="subheader thread_name field_type field_type_electric" style="float: right; margin: 0 0 0 10px; overflow: hidden; text-align: center; border: 1px solid rgba(0, 0, 0, 0.30); ">
            <a class="link" href="community/<?= $this_category_info['category_token'] ?>/new/" style="margin-top: 0;"><?= $this_threads_count_new == 1 ? 'View 1 Updated Thread' : 'View '.$this_threads_count_new.' Updated Threads' ?> &raquo;</a>
        </div>
        <?
    }
    // Add a new thread option to the end of the list if allowed
    if($this_userid != MMRPG_SETTINGS_GUEST_ID
        && $this_userinfo['role_level'] >= $this_category_info['category_level']
        && $community_battle_points >= 10000
        && !empty($this_userinfo['user_flag_postpublic'])
        ){
        ?>
        <div class="subheader thread_name" style="float: right; margin: 0 0 0 10px; overflow: hidden; text-align: center; border: 1px solid rgba(0, 0, 0, 0.30); ">
            <a class="link" href="community/<?= $this_category_info['category_token'] ?>/0/new/" style="margin-top: 0;">Create New Discussion &raquo;</a>
        </div>
        <?
    }
    ?>
    </div>
    <?
}

// Define the current date group
$this_date_group = '';

// Define the temporary timeout variables
$this_time = time();
$this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

// Ensure there are threads to display before looping
if (!empty($this_threads_array)){

    /*
    // Generate a list of page links given the number of threads to display
    $pg_link_markup = array();
    if ($num_pages_required > 1){
        for ($pg = 1; $pg <= $num_pages_required; $pg++){ $pg_link_markup[] = '<a class="link'.($pg == $current_page_num ? ' active' : '').'" href="community/'.$this_category_info['category_token'].'/'.($pg > 1 ? $pg.'/' : '').'">'.$pg.'</a>'; }
        $pg_link_markup = '<div class="pg_links"><ul><li>'.implode('</li><li>', $pg_link_markup).'</li></ul></div>';
    }
    */

    // Define the variable to hold the pagelink markup
    $pagelink_markup = '';
    $pagelink_base_href = 'community/'.$this_category_info['category_token'].'/';
    if ($num_pages_required > 1){

        // Define the start and end pages based on total numbers
        $display_range = 2;
        $display_range2 = 8;
        $first_page_num = 1;
        $last_page_num = $num_pages_required;

        // Start the output buffer to collect markup
        ob_start();

        // If we're not on the first page, create a link to go back one
        if ($current_page_num > 1){
            $previous_page_num = $current_page_num - 1;
            echo '<a class="link prev" href="'.$pagelink_base_href.($previous_page_num > 1 ? $previous_page_num.'/' : '').'" >&laquo; Prev</a>';
        }

        // If not displaying all players, create a link to show more
        if ($current_page_num < $num_pages_required){
            $next_page_num = $current_page_num + 1;
            echo '<a class="link next" href="'.$pagelink_base_href.($next_page_num > 1 ? $next_page_num.'/' : '').'" >Next &raquo;</a>';
        }
        // If we're already on the last page, display a link to go to the first
        elseif ($current_page_num >= $num_pages_required){
            echo '<a class="link next" href="'.$pagelink_base_href.'">First &raquo;</a>';
        }

        // Loop through and generate the page number markup
        for ($this_page_num = $first_page_num; $this_page_num <= $num_pages_required; $this_page_num++){
            $show_page_num = false;
            if ($this_page_num == $current_page_num){ $show_page_num = true; }
            elseif ($this_page_num <= $current_page_num + $display_range && $this_page_num >= $current_page_num - $display_range){ $show_page_num = true; }
            elseif ($this_page_num <= $first_page_num + $display_range2 && $this_page_num >= $first_page_num - $display_range2){ $show_page_num = true; }
            elseif ($this_page_num <= $last_page_num + $display_range2 && $this_page_num >= $last_page_num - $display_range2){ $show_page_num = true; }
            $show_num_text = $show_page_num ? $this_page_num : '.';
            $show_num_type = $show_page_num ? 'number' : 'bullet';
            if ($current_page_num == $this_page_num){ echo '<a class="link '.$show_num_type.' active"><span>'.$this_page_num.'</span></a>'; }
            else { echo '<a class="link '.$show_num_type.' '.'" href="'.$pagelink_base_href.($this_page_num > 1 ? $this_page_num.'/' : '').'" ><span>'.$this_page_num.'</span></a>'; }
        }

        // Collect the pagelink markup
        $pagelink_markup = trim(ob_get_clean());

    }

    // Loop through the thread array and display its contents
    echo '<div class="pagelinks head">'.$pagelink_markup.'</div>'.PHP_EOL;
    foreach ($this_threads_array AS $this_thread_key => $this_thread_info){ echo mmrpg_website_community_thread_linkblock($this_thread_key, $this_thread_info); }
    echo '<div class="pagelinks foot">'.$pagelink_markup.'</div>'.PHP_EOL;



} else {
    ?>
    <div class="subbody">
    <p class="text">- there are no <?= $this_category_info['category_id'] != 0 ? 'threads' : 'messages' ?> to display -</p>
    </div>
    <?
}
?>