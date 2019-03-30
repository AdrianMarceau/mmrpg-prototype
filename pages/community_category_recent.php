<?

/*
 * COMMUNITY CATEGORY VIEW
 */

// Cannot view "recent" personal messages, too complicated
if ($this_category_info['category_id'] == 0){
    header('Location: '.MMRPG_CONFIG_ROOTURL.'community/personal/');
    exit();
}

// Update the SEO variables for this page
$this_seo_title = 'New Comments | '.$this_category_info['category_name'].' | '.$this_seo_title;
$this_seo_description = strip_tags($this_category_info['category_description']);

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Community Forums | '.$this_category_info['category_name'];
$this_graph_data['description'] = strip_tags($this_category_info['category_description']);
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png?'.MMRPG_CONFIG_CACHE_DATE;
//$this_graph_data['type'] = 'website';

// Collect the recently updated posts for this player / guest
if ($this_userinfo['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $temp_last_login = $this_userinfo['user_backup_login']; }
else { $temp_last_login = time() - MMRPG_SETTINGS_UPDATE_TIMEOUT; }

// Remove any threads that have been viewed from the array
$temp_exclude_ids = array();
$temp_thread_view_times = array();
//$temp_max_time = 0;
if (!empty($_SESSION['COMMUNITY']['threads_viewed'])){
    foreach ($_SESSION['COMMUNITY']['threads_viewed'] AS $key => $string){
        if (empty($string)){ continue; }
        list($temp_id, $temp_time) = explode('_', $string);
        $temp_thread_view_times[$temp_id] = $temp_time;
        $temp_exclude_ids[] = $temp_id;
    }
}

// Collect a list and count of all threads in this category
$this_threads_array = mmrpg_website_community_category_threads($this_category_info, true, true, false, $temp_exclude_ids);
$this_threads_count = !empty($this_threads_array) ? count($this_threads_array) : 0;
//die('<pre>'.print_r($this_threads_array, true).'</pre>');

// Collect the different thread IDs so we can collect all posts as once
$temp_thread_ids = array();
foreach ($this_threads_array AS $array){ $temp_thread_ids[] = $array['thread_id']; }
$temp_thread_ids = implode(',', array_unique($temp_thread_ids));

// Collect any posts for this specific thread from the database
if (!empty($temp_thread_ids)){

    $this_posts_query = "SELECT

        posts.post_id,
        posts.category_id,
        posts.thread_id,
        posts.user_id,
        posts.user_ip,
        posts.post_body,
        posts.post_frame,
        posts.post_date,
        posts.post_mod,
        posts.post_deleted,
        posts.post_votes,
        posts.post_target,

        users.user_id,
        users.user_name,
        users.user_name_public,
        users.user_name_clean,
        users.user_background_path,
        users.user_colour_token,
        users.user_image_path,
        users.user_date_modified,
        users.user_flag_postpublic,

        roles.role_id,
        roles.role_name,
        roles.role_token,
        roles.role_level,
        roles.role_icon

        FROM mmrpg_posts AS posts

        LEFT JOIN mmrpg_users AS users ON posts.user_id = users.user_id

        LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id

        WHERE posts.thread_id IN ({$temp_thread_ids})

        AND posts.post_deleted = 0

        ORDER BY posts.post_date ASC

        ;";
    $temp_posts_array = $db->get_array_list($this_posts_query);
    $this_posts_array = array();
    if (!empty($temp_posts_array)){
        foreach ($temp_posts_array AS $key => $array){
            $this_posts_array[$array['thread_id']][] = $array;
            unset($temp_posts_array[$key]);
        }
    }

} else {

    $this_posts_array = array();

}

// Collect the thread counts for all users in an index
$this_user_countindex = $db->get_array_list("SELECT

    users.user_id,

    leaderboard.board_points,

    threads.thread_count,

    posts.post_count

    FROM mmrpg_users AS users

    LEFT JOIN mmrpg_leaderboard AS leaderboard
        ON leaderboard.user_id = users.user_id

    LEFT JOIN (SELECT
        user_id,
        COUNT(thread_id) AS thread_count
        FROM mmrpg_threads
        WHERE mmrpg_threads.thread_target = 0 AND thread_published = 1
        GROUP BY mmrpg_threads.user_id
        ) AS threads
        ON threads.user_id = users.user_id

    LEFT JOIN (SELECT
        user_id,
        COUNT(post_id) AS post_count
        FROM mmrpg_posts
        WHERE mmrpg_posts.post_target = 0 AND post_deleted = 0
        GROUP BY mmrpg_posts.user_id
        ) AS posts
        ON posts.user_id = users.user_id

    WHERE
        leaderboard.board_points > 0 AND
        (post_count > 0 OR thread_count > 0)

    ;", 'user_id');

// Check if the thread creator is currently online
$temp_leaderboard_online = mmrpg_prototype_leaderboard_online();

?>
<h2 class="subheader thread_name field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    <a class="link" style="display: inline;" href="<?= str_replace($this_category_info['category_token'].'/new/', '', $_GET['this_current_url']) ?>">Community</a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
    <a class="link" style="display: inline;" href="<?= str_replace('new/', '', $_GET['this_current_url']) ?>"><?= $this_category_info['category_name'] ?></a><a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
    <a class="link" style="display: inline;" href="<?= $_GET['this_current_url'] ?>">New Comments</a></a>
    <span style="float: right; opacity: 0.25;"><?= $this_threads_count == '1' ? '1 Updated Thread' : $this_threads_count.' Updated Threads'  ?></span>
</h2>
<div class="subbody">
    <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_0<?= mt_rand(0, 2) ?>" style="background-image: url(images/robots/<?= MMRPG_SETTINGS_CURRENT_FIELDMECHA ?>/sprite_left_80x80.png);">Met</div></div>
    <p class="text"><?= $this_category_info['category_description'] ?></p>
    <?
    // Add a new thread option to the end of the list if allowed
    if($this_userid != MMRPG_SETTINGS_GUEST_ID && $this_userinfo['role_level'] >= $this_category_info['category_level'] && $community_battle_points >= 10000){
        ?>
        <div class="subheader thread_name" style="float: right; clear: right; margin: 0; overflow: hidden; text-align: center; border: 1px solid rgba(0, 0, 0, 0.30); ">
            <a class="link" href="community/<?= $this_category_info['category_token'] ?>/0/new/" style="margin-top: 0;">Create New Discussion &raquo;</a>
        </div>
        <?
    }
    ?>
</div>
<?

// Define the current date group
$this_date_group = '';

// Define the temporary timeout variables
$this_time = time();
$this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

// Loop through the thread array and display its contents
$temp_posts_shown = 0;
$temp_threads_shown = 0;
$temp_update_session_ids = array();
if (!empty($this_threads_array)){
    foreach ($this_threads_array AS $this_thread_key => $this_thread_info){

        // Print out the thread link block
        echo mmrpg_website_community_thread_linkblock($this_thread_key, $this_thread_info, true);

        // Define this thread's session tracker token
        $temp_session_token = $this_thread_info['thread_id'].'_';
        $temp_session_token .= !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $this_thread_info['thread_date'];

        // Check if this thread has already been viewed this session
        $temp_session_viewed = in_array($temp_session_token, $_SESSION['COMMUNITY']['threads_viewed']) ? true : false;
        if (!MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['COMMUNITY']['threads_viewed'][] = $temp_session_token; }
        $temp_update_session_ids[] = $this_thread_info['thread_id'];

        // Collect all the posts for this specific thread from the global array
        $this_thread_posts_array = !empty($this_posts_array[$this_thread_info['thread_id']]) ? $this_posts_array[$this_thread_info['thread_id']] : array();

        // Loop through all the posts and display them in reverse order
        if (!empty($this_thread_posts_array)){
            ?>
            <div class="posts_body">
            <?
            // Define the temporary timeout variables
            $this_time = time();
            $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
            // Loop through each of the posts and display their markup
            foreach ($this_thread_posts_array AS $this_post_key => $this_post_info){

                // Define this post's overall float direction based on if PM
                $this_post_float = 'left';
                $this_post_direction = 'right';
                if ($this_post_info['post_target'] == $this_userinfo['user_id']){
                    $this_post_float = 'right';
                    $this_post_direction = 'left';
                }

                // Define the temporary display variables
                $temp_post_guest = $this_post_info['user_id'] == MMRPG_SETTINGS_GUEST_ID ? true : false;
                $temp_post_author = !empty($this_post_info['user_name_public']) && !empty($this_post_info['user_flag_postpublic']) ? $this_post_info['user_name_public'] : $this_post_info['user_name'];
                $temp_reply_name = $temp_post_author;
                $temp_reply_colour = !empty($this_post_info['user_colour_token']) ? $this_post_info['user_colour_token'] : 'none';
                $temp_post_date = !empty($this_post_info['post_date']) ? $this_post_info['post_date'] : mktime(0, 0, 1, 1, 1, 2011);
                $temp_post_date_full = 'Posted on '.date('F jS, Y', $temp_post_date).' at '.date('g:ia', $temp_post_date);
                $temp_post_date_short = 'Posted '.date('Y/m/d', $temp_post_date).' at '.date('g:ia', $temp_post_date);
                $temp_post_mod = !empty($this_post_info['post_mod']) && $this_post_info['post_mod'] != $this_post_info['post_date'] ? $this_post_info['post_mod'] : false;
                $temp_post_mod_full = !empty($temp_post_mod) ? 'Edited on '.date('Y/m/d', $temp_post_mod).' at '.date('g:ia', $temp_post_mod).'' : false;
                $temp_post_mod_short = !empty($temp_post_mod) ? 'Edited '.date('Y/m/d', $temp_post_mod).' at '.date('g:ia', $temp_post_mod).'' : false;
                $temp_post_body = $this_post_info['post_body'];
                $temp_post_title = '#'.$this_post_info['user_id'].' : '.$temp_post_author;
                $temp_post_timestamp = !empty($this_post_info['post_mod']) ? $this_post_info['post_mod'] : $this_post_info['post_date'];

                // Define if this post is new to the logged in user or not
                $temp_is_new = false;
                // Supress the new flag if thread has already been viewed
                if ($this_userinfo['user_id'] != MMRPG_SETTINGS_GUEST_ID
                    && $this_post_info['user_id'] != $this_userinfo['user_id']
                    && $temp_post_timestamp > $this_userinfo['user_backup_login']){
                    $temp_is_new = true;
                } elseif ($this_userinfo['user_id'] == MMRPG_SETTINGS_GUEST_ID
                    && (($this_time - $temp_post_timestamp) <= MMRPG_SETTINGS_UPDATE_TIMEOUT)){
                    $temp_is_new = true;
                }
                // If this message is older than the last viewed time, hide it
                if (!empty($temp_thread_view_times[$this_thread_info['thread_id']])){
                    if ($temp_thread_view_times[$this_thread_info['thread_id']] >= $temp_post_timestamp){
                        $temp_is_new = false;
                    }
                }
                // If this is not new, continue
                if (!$temp_is_new){ continue; }

                // Define the avatar class and path variables
                $temp_avatar_frame = !empty($this_post_info['post_frame']) ? $this_post_info['post_frame'] : '00';
                $temp_avatar_path = !empty($this_post_info['user_image_path']) ? $this_post_info['user_image_path'] : 'robots/mega-man/40';
                $temp_background_path = !empty($this_post_info['user_background_path']) ? $this_post_info['user_background_path'] : 'fields/intro-field';
                list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
                list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
                $temp_avatar_class = 'avatar avatar_40x40 float float_left ';
                $temp_avatar_colour = !empty($this_post_info['user_colour_token']) ? $this_post_info['user_colour_token'] : 'none';
                $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
                $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_right_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;

                $temp_is_contributor = in_array($this_post_info['role_token'], array('developer', 'administrator', 'moderator', 'contributor')) ? true : false;
                if ($temp_is_contributor){
                    $temp_item_class = 'sprite sprite_40x40 sprite_40x40_00';
                    $temp_item_path = 'images/items/'.(!empty($this_post_info['role_icon']) ? $this_post_info['role_icon'] : 'energy-pellet' ).'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
                    $temp_item_title = !empty($this_post_info['role_name']) ? $this_post_info['role_name'] : 'Contributor';
                }

                // Define the temporary online variables
                $temp_last_modified = !empty($this_post_info['user_date_modified']) ? $this_post_info['user_date_modified'] : 0;

                // Check if the thread creator is currently online
                $temp_is_online = false;
                foreach ($temp_leaderboard_online AS $key => $info){ if ($info['id'] == $this_post_info['user_id']){ $temp_is_online = true; break; } }

                // Collect the thread count for this user
                if ($this_post_info['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $this_post_info['thread_count'] = !empty($this_user_threadcount[$this_post_info['user_id']]) ? $this_user_threadcount[$this_post_info['user_id']]['thread_count'] : 0; }
                else { $this_post_info['thread_count'] = false; }
                // Collect the post count for this user
                if ($this_post_info['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $this_post_info['post_count'] = !empty($this_user_postcount[$this_post_info['user_id']]) ? $this_user_postcount[$this_post_info['user_id']]['post_count'] : 0; }
                else { $this_post_info['post_count'] = false; }

                // Collect the reply data for this user
                $temp_reply_name = $temp_post_author;
                $temp_reply_colour = !empty($this_post_info['user_colour_token']) ? $this_post_info['user_colour_token'] : 'none';

                // Check if this is a system message
                if (empty($this_post_info['user_id'])){ $is_system_post = true; }
                else { $is_system_post = false; }

                //die(print_r($this_user_postcount[$this_userinfo['user_id']], true));
                //echo print_r($this_user_postcount[$this_post_info['user_id']], true);

                ?>
                <div id="post-<?= $this_post_info['post_id'] ?>" data-key="<?= $this_post_key ?>" title="<?= !empty($this_post_info['post_deleted']) ? ($temp_post_author.' on '.str_replace(' ', '&nbsp;', $temp_post_date)) : '' ?>" class="subbody post_subbody post_subbody_left <?= !empty($this_post_info['post_deleted']) ? 'post_subbody_deleted' : '' ?> post_left" style="<?= !empty($this_post_info['post_deleted']) ? 'margin-top: 0; padding: 0 10px; background-color: transparent; float: left; ' : 'clear: left; ' ?>">
                    <? if(empty($this_post_info['post_deleted'])): ?>
                        <div class="userblock player_type_<?= !empty($this_post_info['user_colour_token']) ? $this_post_info['user_colour_token'] : 'none' ?>">
                            <div class="name">
                                <?= !$temp_post_guest ? '<a href="leaderboard/'.$this_post_info['user_name_clean'].'/">' : '' ?>
                                <strong title="<?= $temp_post_author.($temp_is_contributor ? ' | '.$temp_item_title : ' | Player').($temp_is_online ? ' | Online' : '') ?>" style="<?= $temp_is_online ? 'text-shadow: 0 0 2px rgba(0, 255, 0, 0.20); ' : '' ?>"><?= $temp_post_author ?></strong>
                                <?= !$temp_post_guest ? '</a>' : '' ?>
                            </div>
                            <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 100px 100px;">
                                &nbsp;
                            </div>
                            <div class="<?= $temp_avatar_class ?> avatar_userimage avatar_userinfo_left" style="">
                                <? if($temp_is_contributor): ?><div class="<?= $temp_item_class ?>" style="background-image: url(<?= $temp_item_path ?>); position: absolute; top: -10px; <?= $this_post_float ?>: -14px;" title="<?= $temp_item_title ?>"><?= $temp_item_title ?></div><? endif; ?>
                                <div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>);"><?= $temp_post_author ?></div>
                            </div>

                            <? $temp_stat = !empty($this_user_countindex[$this_post_info['user_id']]['board_points']) ? $this_user_countindex[$this_post_info['user_id']]['board_points'] : 0; ?>
                            <div class="counter points_counter"><?= number_format($temp_stat, 0, '.', ',').' BP' ?></div>
                            <div class="counter community_counters">
                                <? $temp_stat = !empty($this_user_countindex[$this_post_info['user_id']]['thread_count']) ? $this_user_countindex[$this_post_info['user_id']]['thread_count'] : 0; ?>
                                <span class="thread_counter"><?= $temp_stat.' TP' ?></span> <span class="pipe">|</span>
                                <? $temp_stat = !empty($this_user_countindex[$this_post_info['user_id']]['post_count']) ? $this_user_countindex[$this_post_info['user_id']]['post_count'] : 0; ?>
                                <span class="post_counter"><?= $temp_stat.' PP' ?></span>
                            </div>

                        </div>
                        <div class="postblock">
                            <div class="published" title="<?= $temp_post_author.' on '.str_replace(' ', '&nbsp;', $temp_post_date) ?>" style="<?= $is_system_post ? 'margin-left: 0; ' : '' ?>">
                                <div class="full">
                                    <strong class="date"><?= $temp_post_date_full ?></strong>
                                    <?= !empty($temp_post_mod) ? '<span class="modified">'.$temp_post_mod_full.'</span>' : '' ?>
                                    <?= $temp_is_new ? '<strong class="new">New!</strong>' : '' ?>
                                </div>
                                <div class="short">
                                    <strong class="date"><?= $temp_post_date_short ?></strong>
                                    <?= !empty($temp_post_mod) ? '<span class="modified">'.$temp_post_mod_short.'</span>' : '' ?>
                                    <?= $temp_is_new ? '<strong class="new">New!</strong>' : '' ?>
                                </div>
                                <span class="key right">#<?= $this_post_key + 1 ?></span>
                                <? if(!$temp_post_guest && $this_userinfo['user_id'] == $this_post_info['user_id']): ?>
                                    <span class="options">[ <a class="edit" rel="noindex,nofollow" href="<?= $_GET['this_current_url'].'action=edit&amp;post_id='.$this_post_info['post_id'].'#comment-form' ?>">edit</a> | <a class="delete" rel="noindex,nofollow" href="<?= $_GET['this_current_url'] ?>" data-href="<?= $_GET['this_current_url'].'action=delete&amp;post_id='.$this_post_info['post_id'].'#comment-form' ?>">delete</a> ]</span>
                                <? endif; ?>
                            </div>
                            <div class="bodytext"><?= mmrpg_formatting_decode($temp_post_body) ?></div>
                        </div>
                        <? if($this_userid != MMRPG_SETTINGS_GUEST_ID && empty($this_thread_info['thread_locked']) && $community_battle_points > 5000): ?>
                            <a class="postreply right" rel="nofollow" href="<?= 'community/'.$this_category_info['category_token'].'/'.$this_thread_info['thread_id'].'/'.$this_thread_info['thread_token'].'/#comment-form:'.$temp_reply_name.':'.$temp_reply_colour ?>">@ Reply</a>
                        <? endif; ?>
                        <a class="postscroll right" href="#top">^ Top</a>
                    <? else: ?>
                        <span style="color: #464646;">- deleted -</span>
                    <? endif; ?>
                </div>
                <?
            }
            ?>
            </div>
            <?

            // Increment the posts show counter
            $temp_posts_shown++;

        }

        // Increment the threads show counter
        $temp_threads_shown++;

    }

    // Update all the threads that require it with a view count
    if (!MMRPG_CONFIG_DEBUG_MODE){
        $temp_update_session_ids = implode(', ', $temp_update_session_ids);
        $temp_update_session = $db->query("UPDATE mmrpg_threads SET thread_views = (thread_views + 1) WHERE thread_id IN ({$temp_update_session_ids});");
    }


}

// Otherwise, if there's nothing to show
if (($temp_posts_shown + $temp_threads_shown) < 1){
    ?>
    <div id="post-0" data-key="0" class="subbody post_subbody post_subbody_deleted post_left" style="clear: left; ">
    - no new comments to display -
    </div>
    <?
}
?>