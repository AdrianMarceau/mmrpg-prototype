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
// Collect a list and count of all threads in this category
$this_threads_array = mmrpg_website_community_category_threads($this_category_info);
$this_threads_count = !empty($this_threads_array) ? count($this_threads_array) : 0;
//die('<pre>'.print_r($this_threads_array, true).'</pre>');

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype '.$this_category_info['category_name'].'';


?>
<h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    <a class="link" style="display: inline;" href="<?= str_replace($this_category_info['category_token'].'/', '', $_GET['this_current_url']) ?>">Community</a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
    <a class="link" style="display: inline;" href="<?= $_GET['this_current_url'] ?>"><?= $this_category_info['category_name'] ?></a>
    <span class="count float"><?= $this_threads_count == '1' ? '1 '.($this_category_info['category_id'] != 0 ? 'Discussion' : 'Message') : $this_threads_count.' '.($this_category_info['category_id'] != 0 ? 'Discussions' : 'Messages')  ?></span>
</h2>
<?
// Only display the category body if not personal
if ($this_category_info['category_id'] != 0){
    ?>
    <div class="subbody">
    <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_0<?= mt_rand(0, 2) ?>" style="background-image: url(images/robots/<?= MMRPG_SETTINGS_CURRENT_FIELDMECHA ?>/sprite_left_80x80.png);">Met</div></div>
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
    if($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_userinfo['role_level'] >= $this_category_info['category_level'] && $community_battle_points >= 10000){
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

// Loop through the thread array and display its contents
if (!empty($this_threads_array)){
    foreach ($this_threads_array AS $this_thread_key => $this_thread_info){

        // Print out the thread link block
        echo mmrpg_website_community_thread_linkblock($this_thread_key, $this_thread_info);

    }
} else {
    ?>
    <div class="subbody">
    <p class="text">- there are no <?= $this_category_info['category_id'] != 0 ? 'threads' : 'messages' ?> to display -</p>
    </div>
    <?
}
?>