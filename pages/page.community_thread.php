<?
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

/*
 * COMMUNITY THREAD VIEW
 */

// Define the temporary timeout variables
$this_time = time();
$this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;

// If this is a PERSONAL thread, we have to do some security
if ($this_category_info['category_token'] == 'personal'){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    // Ensure the user is logged in, else redirect to login
    if ($_SESSION['GAME']['USER']['userid'] == MMRPG_SETTINGS_GUEST_ID){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        header('Location: '.MMRPG_CONFIG_ROOTURL.'file/load/');
        exit();
    }
    // Ensure the user is actually part of the thread, else redirect to community index
    elseif ($_SESSION['GAME']['USER']['userid'] != $this_thread_info['user_id']
        && $_SESSION['GAME']['USER']['userid'] != $this_thread_info['thread_target']){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        header('Location: '.MMRPG_CONFIG_ROOTURL.'community/personal/');
        exit();
    }
}

// Update the SEO variables for this page
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$this_seo_title = $this_thread_info['thread_name'].' | '.$this_category_info['category_name'].' | '.$this_seo_title;
$this_seo_description = strip_tags(mmrpg_formatting_decode($this_thread_info['thread_body']));
if (strlen($this_seo_description) > 200){ $this_seo_description = substr($this_seo_description, 0, 200).'...'; }

// Define the Open Graph variables for this page
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$this_graph_data['title'] = $this_category_info['category_name'].' Discussions | '.$this_thread_info['thread_name'];
$this_graph_data['description'] = strip_tags(mmrpg_formatting_decode($this_thread_info['thread_body']));
if (strlen($this_graph_data['description']) > 200){ $this_graph_data['description'] = substr($this_graph_data['description'], 0, 200).'...'; }
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png?'.MMRPG_CONFIG_CACHE_DATE;
$this_graph_data['type'] = 'article';
$this_graph_data['article__published_time'] = !empty($this_thread_info['thread_date']) ? $this_thread_info['thread_date'] : mktime(0, 0, 1, 1, 1, 2011);
$this_graph_data['article__published_time'] = date('Y-m-d', $this_graph_data['article__published_time']).'T'.date('H:i', $this_graph_data['article__published_time']);
$this_graph_data['article__modified_time'] = !empty($this_thread_info['thread_date']) ? $this_thread_info['thread_date'] : mktime(0, 0, 1, 1, 1, 2011);
$this_graph_data['article__modified_time'] = date('Y-m-d', $this_graph_data['article__modified_time']).'T'.date('H:i', $this_graph_data['article__modified_time']);
$this_graph_data['article__author'] = !empty($this_thread_info['user_name_public']) ? $this_thread_info['user_name_public'] : $this_thread_info['user_name'];

/*
article:published_time - datetime - When the article was first published.
article:modified_time - datetime - When the article was last changed.
article:expiration_time - datetime - When the article is out of date after.
article:author - profile array - Writers of the article.
article:section - string - A high-level section name. E.g. Technology
article:tag - string array - Tag words associated with this article.
*/

// Update the MARKUP variables for this page
//$this_markup_header = $this_thread_info['thread_name']; //.' | '.$this_markup_header;

// Define this thread's session tracker token
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$thread_session_token = $this_thread_info['thread_id'].'_';
$thread_session_token .= !empty($this_thread_info['thread_mod_date']) ? $this_thread_info['thread_mod_date'] : $this_thread_info['thread_date'];
// Check if this thread has already been viewed this session
$thread_session_viewed = in_array($thread_session_token, $_SESSION['COMMUNITY']['threads_viewed']) ? true : false;

// Check to see if this is a message thread, and then if being viewed by creator
$is_personal_message = $this_thread_info['thread_target'] != 0 ? true : false;
$is_personal_message_creator = $is_personal_message && $this_thread_info['user_id'] == $this_userinfo['user_id'] ? true : false;
$is_personal_query_condition = $is_personal_message ? "AND (posts.user_id = {$this_userinfo['user_id']} OR posts.post_target = {$this_userinfo['user_id']}) " : '';

// Count the number of posts for this specific thread in the database
$this_posts_query = "SELECT
    COUNT(*) as post_count
    FROM mmrpg_posts AS posts
    LEFT JOIN mmrpg_users AS users ON posts.user_id = users.user_id
    LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
    WHERE
    posts.thread_id = '{$this_thread_info['thread_id']}'
    {$is_personal_query_condition}
    ORDER BY posts.post_date ASC
    ;";
$this_posts_count = $DB->get_value($this_posts_query, 'post_count');
if (!is_numeric($this_posts_count)){ $this_posts_count = 0; }

// Define the post/comment limit, page count, and offset variables
$comment_post_limit = MMRPG_SETTINGS_POSTS_PERPAGE;
$comment_post_pages = ceil($this_posts_count / $comment_post_limit);
if ($comment_post_pages < 1){ $comment_post_pages = 1; }
$comment_post_offset = $this_current_num > 1 ? ($this_current_num - 1) * $comment_post_limit : 0;

// If the user has somehow requested a page out-of-range, redirect to last
if ($this_current_num > $comment_post_pages){
    $redirect = preg_replace('/\/[0-9]+\/$/', '/', $this_current_url);
    $redirect .= $comment_post_pages.'/';
    header('Location: '.$redirect);
    exit();
}

// Now collect all posts (in full) for this specific thread in the database
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

    roles.role_id,
    roles.role_name,
    roles.role_token,
    roles.role_level,
    roles.role_icon

    FROM mmrpg_posts AS posts

    LEFT JOIN mmrpg_users AS users ON posts.user_id = users.user_id

    LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id

    WHERE
        posts.thread_id = '{$this_thread_info['thread_id']}'
        {$is_personal_query_condition}

    ORDER BY posts.post_date ASC

    LIMIT {$comment_post_limit}

    OFFSET {$comment_post_offset}

    ;";
$this_posts_array = $DB->get_array_list($this_posts_query);
if (empty($this_posts_array)){ $this_posts_array = array(); }

// Define the array of user ids to collect information for
$temp_user_ids = array();
if (!empty($this_thread_info['user_id'])){ $temp_user_ids[] = $this_thread_info['user_id']; }
if (!empty($this_thread_info['thread_target'])){ $temp_user_ids[] = $this_thread_info['thread_target']; }
foreach ($this_posts_array AS $key => $array){
    if (!empty($array['user_id'])){ $temp_user_ids[] = $array['user_id'];  }
    if (!empty($array['post_target'])){ $temp_user_ids[] = $array['post_target'];  }
}
$temp_user_ids = array_unique($temp_user_ids);

// If the current post count is somehow higher than the view count, fix it up
if ($this_posts_count >= $this_thread_info['thread_views']){
    $this_thread_info['thread_views'] += $this_posts_count;
    $DB->query("UPDATE mmrpg_threads SET thread_views = {$this_thread_info['thread_views']} WHERE thread_id = {$this_thread_info['thread_id']}");
}

// Collect the thread counts for all users in an index
if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
$this_user_countindex = $DB->get_array_list('SELECT
    mmrpg_users.user_id,
    mmrpg_leaderboard.board_points,
    mmrpg_threads.thread_count,
    mmrpg_posts.post_count
    FROM mmrpg_users
    LEFT JOIN mmrpg_leaderboard ON mmrpg_leaderboard.user_id = mmrpg_users.user_id
    LEFT JOIN (
    SELECT user_id, COUNT(thread_id) AS thread_count FROM mmrpg_threads WHERE mmrpg_threads.thread_target = 0 AND thread_published = 1 GROUP BY mmrpg_threads.user_id
    ) mmrpg_threads ON mmrpg_threads.user_id = mmrpg_users.user_id
    LEFT JOIN (
    SELECT user_id, COUNT(post_id) AS post_count FROM mmrpg_posts WHERE mmrpg_posts.post_target = 0 AND post_deleted = 0 GROUP BY mmrpg_posts.user_id
    ) mmrpg_posts ON mmrpg_posts.user_id = mmrpg_users.user_id
    WHERE mmrpg_leaderboard.board_points > 0 AND mmrpg_users.user_id IN ('.implode(', ', $temp_user_ids).')
    ;', 'user_id');




// Define the temporary display variables
$temp_thread_guest = $this_thread_info['user_id'] == MMRPG_SETTINGS_GUEST_ID ? true : false;
$temp_thread_name = $this_thread_info['thread_name'];
$temp_thread_author = !empty($this_thread_info['user_name_public']) ? $this_thread_info['user_name_public'] : $this_thread_info['user_name'];
$temp_thread_date = !empty($this_thread_info['thread_date']) ? $this_thread_info['thread_date'] : mktime(0, 0, 1, 1, 1, 2011);
$temp_thread_date = date('F jS, Y', $temp_thread_date).' at '.date('g:ia', $temp_thread_date);
$temp_thread_body = $this_thread_info['thread_body'];
$temp_thread_views = !empty($this_thread_info['thread_views']) ? $this_thread_info['thread_views'] : 0;

// If this is a PM, collect the target's info
if ($is_personal_message){
    $temp_thread_targetinfo = $DB->get_array("SELECT user_id, user_name, user_name_public, user_name_clean FROM mmrpg_users WHERE user_id = {$this_thread_info['thread_target']} LIMIT 1");
    $temp_thread_target = !empty($temp_thread_targetinfo['user_name_public']) ? $temp_thread_targetinfo['user_name_public'] : $temp_thread_targetinfo['user_name'];
}

// Define the avatar class and path variables
$temp_avatar_frame = !empty($this_thread_info['thread_frame']) ? $this_thread_info['thread_frame'] : '00';
$temp_avatar_path = !empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
$temp_background_path = !empty($this_thread_info['user_background_path']) ? $this_thread_info['user_background_path'] : 'fields/intro-field';
list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
$temp_avatar_size = $temp_avatar_size * 2;
list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
$temp_avatar_class = 'avatar avatar_80x80 float float_'.($is_personal_message_creator ? 'left' : 'right').' ';
$temp_avatar_colour = !empty($this_thread_info['user_colour_token']) ? $this_thread_info['user_colour_token'] : 'none';
$temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
$temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.($is_personal_message_creator ? 'right' : 'left').'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
$temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;
$temp_is_contributor = in_array($this_thread_info['role_token'], array('developer', 'administrator', 'contributor', 'moderator')) ? true : false;
if ($temp_is_contributor){
    $temp_item_class = 'sprite sprite_80x80 sprite_80x80_00';
    $temp_item_path = 'images/abilities/item-'.(!empty($this_thread_info['role_icon']) ? $this_thread_info['role_icon'] : 'energy-pellet' ).'/icon_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
    $temp_item_title = !empty($this_thread_info['role_name']) ? $this_thread_info['role_name'] : 'Contributor';
}

// Check if the thread creator is currently online
$temp_is_online = false;
$temp_leaderboard_online = mmrpg_prototype_leaderboard_online();
foreach ($temp_leaderboard_online AS $key => $info){ if ($info['id'] == $this_thread_info['user_id']){ $temp_is_online = true; break; } }

// Collect the thread count for this user
if ($this_thread_info['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $this_thread_info['thread_count'] = !empty($this_user_countindex[$this_thread_info['user_id']]['thread_count']) ? $this_user_countindex[$this_thread_info['user_id']]['thread_count'] : 0; }
else { $this_thread_info['thread_count'] = false; }
// Collect the thread count for this user
if ($this_thread_info['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $this_thread_info['post_count'] = !empty($this_user_countindex[$this_thread_info['user_id']]['post_count']) ? $this_user_countindex[$this_thread_info['user_id']]['post_count'] : 0; }
else { $this_thread_info['post_count'] = false; }

//die('<pre>'.print_r($this_thread_info, true).'</pre>');

?>
<h2 class="subheader thread_name field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="">
    <span class="thread_namewrapper" style="">
        <a class="link" style="" href="<?= str_replace($this_category_info['category_token'].'/'.$this_current_id.'/'.$this_current_token.'/', '', $_GET['this_current_url']) ?>">Community</a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
        <a class="link" style="" href="<?= str_replace($this_current_id.'/'.$this_current_token.'/', '', $_GET['this_current_url']) ?>"><?= $this_category_info['category_name'] ?></a> <span class="pipe">&nbsp;&raquo;&nbsp;</span>
        <a class="link" style="" href="<?= $_GET['this_current_url'] ?>" title="<?= $temp_thread_name ?>"><?= $temp_thread_name ?></a>
    </span>
    <span style="float: right; opacity: 0.50;"><?= $temp_thread_date ?></span>
</h2>
<div class="subbody thread_subbody thread_subbody_full thread_subbody_full_<?= $is_personal_message_creator ? 'left' : 'right' ?> thread_<?= $is_personal_message_creator ? 'left' : 'right' ?>" style="text-align: left; position: relative; padding-bottom: 60px;">

    <? if($is_personal_message_creator): ?>

        <div data-user="<?= $this_thread_info['user_id'] ?>" class="<?= $temp_avatar_class ?> avatar_fieldback player_type_<?= $temp_avatar_colour ?>" style="border-width: 1px;">
            <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 100px 100px;">
                &nbsp;
            </div>
        </div>
        <div class="<?= $temp_avatar_class ?> avatar_userimage">
            <?/*<div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/mega-man/sprite_left_40x40.png);"><?= $temp_thread_author ?></div>*/?>
            <? if($temp_is_contributor): ?><div class="<?= $temp_item_class ?>" style="background-image: url(<?= $temp_item_path ?>); position: absolute; top: -22px; left: -30px;" title="<?= $temp_item_title ?>"><?= $temp_item_title ?></div><? endif; ?>
            <div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>);"><?= $temp_thread_author ?></div>
            <div class="userblock">
                <?= !$temp_thread_guest ? '<a href="leaderboard/'.$this_thread_info['user_name_clean'].'/">' : '' ?>
                <strong data-tooltip-type="player_type_<?= $temp_avatar_colour ?>" title="<?= $temp_thread_author.($temp_is_contributor ? ' | '.$temp_item_title : ' | Player').($temp_is_online ? ' | Online' : '') ?>" class="name thread_username"><?= $temp_thread_author ?></strong>
                <?= !$temp_thread_guest ? '</a>' : '' ?>
                <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['board_points']) ? $this_user_countindex[$this_thread_info['user_id']]['board_points'] : 0; ?>
                <div class="counter points_counter"><?= number_format($temp_stat, 0, '.', ',').' BP' ?></div>
                <div class="counter community_counters">
                    <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['thread_count']) ? $this_user_countindex[$this_thread_info['user_id']]['thread_count'] : 0; ?>
                    <span class="thread_counter"><?= $temp_stat.' TP' ?></span> <span class="pipe">|</span>
                    <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['post_count']) ? $this_user_countindex[$this_thread_info['user_id']]['post_count'] : 0; ?>
                    <span class="post_counter"><?= $temp_stat.' PP' ?></span>
                </div>
            </div>
        </div>

    <? else: ?>

        <div data-user="<?= $this_thread_info['user_id'] ?>" class="<?= $temp_avatar_class ?> avatar_fieldback player_type_<?= $temp_avatar_colour ?>" style="border-width: 1px;">
            <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 100px 100px;">
                &nbsp;
            </div>
        </div>
        <div class="<?= $temp_avatar_class ?> avatar_userimage">
            <?/*<div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/mega-man/sprite_left_40x40.png);"><?= $temp_thread_author ?></div>*/?>
            <? if($temp_is_contributor): ?><div class="<?= $temp_item_class ?>" style="background-image: url(<?= $temp_item_path ?>); position: absolute; top: -22px; right: -30px;" title="<?= $temp_item_title ?>"><?= $temp_item_title ?></div><? endif; ?>
            <div class="<?= $temp_sprite_class ?>" style="background-image: url(<?= $temp_sprite_path ?>);"><?= $temp_thread_author ?></div>
            <div class="userblock">
                <?= !$temp_thread_guest ? '<a href="leaderboard/'.$this_thread_info['user_name_clean'].'/">' : '' ?>
                <strong data-tooltip-type="player_type_<?= $temp_avatar_colour ?>" title="<?= $temp_thread_author.($temp_is_contributor ? ' | '.$temp_item_title : ' | Player').($temp_is_online ? ' | Online' : '') ?>" class="name thread_username"><?= $temp_thread_author ?></strong>
                <?= !$temp_thread_guest ? '</a>' : '' ?>
                <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['board_points']) ? $this_user_countindex[$this_thread_info['user_id']]['board_points'] : 0; ?>
                <div class="counter points_counter"><?= number_format($temp_stat, 0, '.', ',').' BP' ?></div>
                <div class="counter community_counters">
                    <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['thread_count']) ? $this_user_countindex[$this_thread_info['user_id']]['thread_count'] : 0; ?>
                    <span class="thread_counter"><?= $temp_stat.' TP' ?></span> <span class="pipe">|</span>
                    <? $temp_stat = !empty($this_user_countindex[$this_thread_info['user_id']]['post_count']) ? $this_user_countindex[$this_thread_info['user_id']]['post_count'] : 0; ?>
                    <span class="post_counter"><?= $temp_stat.' PP' ?></span>
                </div>

                <?/* if(!empty($this_thread_info['thread_count'])): ?>
                <div class="counter thread_counter" style="">
                    <?= $this_thread_info['thread_count'] == 1 ? '1 Thread' : $this_thread_info['thread_count'].' Threads' ?>
                </div>
                <? endif; ?>
                <? if(!empty($this_thread_info['post_count'])): ?>
                <div class="counter post_counter">
                    <?= $this_thread_info['post_count'] == 1 ? '1 Post' : $this_thread_info['post_count'].' Posts' ?>
                </div>
                <? endif; */?>
            </div>
        </div>

    <? endif; ?>

    <div class="bodytext"><?= mmrpg_formatting_decode($temp_thread_body) ?></div>
    <? if((COMMUNITY_VIEW_MODERATOR || $this_userinfo['user_id'] == $this_thread_info['user_id']) && $this_thread_info['category_id'] != 0): ?>
        <? if($this_thread_info['thread_target'] == 0): ?>
        <div class="published" style="position: absolute; bottom: 10px; right: 10px;">
            <?/*<strong><?= $temp_thread_author ?></strong> on <strong><?= $temp_thread_date ?></strong>*/?>
                <span class="options">[ <a class="edit" rel="noindex,nofollow" href="<?= $_GET['this_current_url'].'action=edit&amp;thread_id='.$this_thread_info['thread_id'].'#discussion-form' ?>">edit</a> ]</span>
        </div>
        <? endif; ?>
    <? endif; ?>
    <div class="viewed" style="position: absolute; bottom: 12px; left: 14px; right: 14px; font-size: 10px; line-height: 13px; color: #565656; text-shadow: 0 0 0 transparent; border-top: 1px solid #252424; padding-top: 6px; width: 90%; ">
        <?
        // If this is a personal message, only display the time
        if ($this_category_info['category_id'] == 0){ echo 'Sent by '.$temp_thread_author.' to '.$temp_thread_target.' on '.$temp_thread_date; }
        // Otherwise display extended details about the post
        else { echo $temp_thread_name.'<br /> Posted by '.$temp_thread_author.' on '.$temp_thread_date.'<br /> '.($temp_thread_views == 1 ? 'Viewed 1 Time' : 'Viewed '.$temp_thread_views.' Times'); }
        ?>
    </div>

</div>
<div class="posts_body">

    <?
    // Loop through all the posts and display them in reverse order
    if (!empty($this_posts_count)){

        // Generate the comment listing's header details
        $comment_header_class = 'subheader thread_posts_count field_type_empty '; //.(!empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE).' ';
        if ($this_category_info['category_id'] == 0){ $comment_header_title = $this_posts_count == 1 ? '1 Reply' : $this_posts_count.' Replies'; }
        else { $comment_header_title = $this_posts_count == 1 ? '1 Comment' : $this_posts_count.' Comments'; }

        // Define the base URI to generate links off of
        $base_uri = preg_replace('/\/[0-9]+\/$/', '/', $this_current_uri);

        // Define an array to hold header links
        $comment_header_links = array();

        // Only bother with page links if there's more than one page
        if ($comment_post_pages > 1){

            // Generate a PREV link if applicable
            if ($this_current_num > 1){
                $num = $this_current_num - 1;
                $link = ' href="'.$base_uri.($num > 1 ? $num.'/' : '').'"';
                $class = ' class="prev"';
                $comment_header_links[] = "<a {$class}{$link}>&laquo;</a>";
            }

            // Generate links for each individual page
            for ($num = 1; $num <= $comment_post_pages; $num++){
                $link = ' href="'.$base_uri.($num > 1 ? $num.'/' : '').'"';
                $class = $this_current_num == $num ? ' class="active"' : '';
                $comment_header_links[] = "<a {$class}{$link}>{$num}</a>";
            }

            // Generate a PREV link if applicable
            if ($this_current_num < $comment_post_pages){
                $num = $this_current_num + 1;
                $link = ' href="'.$base_uri.($num > 1 ? $num.'/' : '').'"';
                $class = ' class="next"';
                $comment_header_links[] = "<a {$class}{$link}>&raquo;</a>";
            }

            // Append the haslinks class to the header
            $comment_header_class .= 'haslinks ';

        }

        // Print out the comment listing's header and links
        ?>
        <div id="comment-listing" class="<?= $comment_header_class ?>">
            <h3 class="thread_posts_total"><?= $comment_header_title ?></h3>
            <? if (!empty($comment_header_links)){ ?>
                <div class="thread_posts_pages">
                    Page : <?= implode("\n", $comment_header_links) ?>
                </div>
            <? } ?>
        </div>
        <?

        // Loop through each of the posts and display their markup
        foreach ($this_posts_array AS $this_post_key => $this_post_info){

            // If this is a personal message, we should check stuff
            if ($is_personal_message){
                if ($this_post_info['user_id'] != $this_userinfo['user_id']
                    && $this_post_info['post_target'] != $this_userinfo['user_id']){
                        continue;
                    }
            }

            // Define this post's overall float direction based on if PM
            $this_post_float = 'left';
            $this_post_direction = 'right';
            if ($this_post_info['post_target'] == $this_userinfo['user_id']){
                $this_post_float = 'right';
                $this_post_direction = 'left';
            }

            // Define the temporary display variables
            $temp_post_guest = $this_post_info['user_id'] == MMRPG_SETTINGS_GUEST_ID ? true : false;
            $temp_post_author = !empty($this_post_info['user_name_public']) ? $this_post_info['user_name_public'] : $this_post_info['user_name'];
            $temp_post_date = !empty($this_post_info['post_date']) ? $this_post_info['post_date'] : mktime(0, 0, 1, 1, 1, 2011);
            $temp_post_date = date('F jS, Y', $temp_post_date).' at '.date('g:ia', $temp_post_date);
            $temp_post_mod = !empty($this_post_info['post_mod']) && $this_post_info['post_mod'] != $this_post_info['post_date'] ? $this_post_info['post_mod'] : false;
            $temp_post_mod = !empty($temp_post_mod) ? '( Edited : '.date('Y/m/d', $temp_post_mod).' at '.date('g:ia', $temp_post_mod).' )' : false;
            $temp_post_body = $this_post_info['post_body'];
            $temp_post_title = '#'.$this_post_info['user_id'].' : '.$temp_post_author;
            $temp_post_timestamp = !empty($this_post_info['post_mod']) ? $this_post_info['post_mod'] : $this_post_info['post_date'];

            // Define the avatar class and path variables
            $temp_avatar_frame = !empty($this_post_info['post_frame']) ? $this_post_info['post_frame'] : '00';
            $temp_avatar_path = !empty($this_post_info['user_image_path']) ? $this_post_info['user_image_path'] : 'robots/mega-man/40';
            $temp_background_path = !empty($this_post_info['user_background_path']) ? $this_post_info['user_background_path'] : 'fields/intro-field';
            list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
            list($temp_background_kind, $temp_background_token) = explode('/', $temp_background_path);
            $temp_avatar_class = 'avatar avatar_40x40 float float_'.$this_post_float.' ';
            $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
            $temp_avatar_colour = !empty($this_post_info['user_colour_token']) ? $this_post_info['user_colour_token'] : 'none';
            $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_'.$this_post_direction.'_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $temp_background_path = 'images/'.$temp_background_kind.'/'.$temp_background_token.'/battle-field_avatar.png?'.MMRPG_CONFIG_CACHE_DATE;

            $temp_is_contributor = in_array($this_post_info['role_token'], array('developer', 'administrator', 'moderator', 'contributor')) ? true : false;
            if ($temp_is_contributor){
                $temp_item_class = 'sprite sprite_40x40 sprite_40x40_00';
                $temp_item_path = 'images/abilities/item-'.(!empty($this_post_info['role_icon']) ? $this_post_info['role_icon'] : 'energy-pellet' ).'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
                $temp_item_title = !empty($this_post_info['role_name']) ? $this_post_info['role_name'] : 'Contributor';
            }

            // Define the temporary online variables
            $temp_last_modified = !empty($this_post_info['user_date_modified']) ? $this_post_info['user_date_modified'] : 0;
            // Check if the thread creator is currently online
            $temp_is_online = false;
            foreach ($temp_leaderboard_online AS $key => $info){ if ($info['id'] == $this_post_info['user_id']){ $temp_is_online = true; break; } }

            // Define if this post is new to the logged in user or not
            $temp_is_new = false;
            // Supress the new flag if thread has already been viewed
            if (!$thread_session_viewed && $this_category_info['category_id'] != 0){
                if ($this_userinfo['user_id'] != MMRPG_SETTINGS_GUEST_ID
                    && $this_post_info['user_id'] != $this_userinfo['user_id']
                    && $temp_post_timestamp > $this_userinfo['user_backup_login']){
                    $temp_is_new = true;
                } elseif ($this_userinfo['user_id'] == MMRPG_SETTINGS_GUEST_ID
                    && (($this_time - $temp_post_timestamp) <= MMRPG_SETTINGS_UPDATE_TIMEOUT)){
                    $temp_is_new = true;
                }
            }
            // Collect the thread count for this user
            if ($this_post_info['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $this_post_info['thread_count'] = !empty($this_user_countindex[$this_post_info['user_id']]['thread_count']) ? $this_user_countindex[$this_post_info['user_id']]['thread_count'] : 0; }
            else { $this_post_info['thread_count'] = false; }
            // Collect the post count for this user
            if ($this_post_info['user_id'] != MMRPG_SETTINGS_GUEST_ID){ $this_post_info['post_count'] = !empty($this_user_countindex[$this_post_info['user_id']]['thread_count']) ? $this_user_countindex[$this_post_info['user_id']]['post_count'] : 0; }
            else { $this_post_info['post_count'] = false; }

            // Collect the reply data for this user
            $temp_reply_name = $temp_post_author;
            $temp_reply_colour = !empty($this_post_info['user_colour_token']) ? $this_post_info['user_colour_token'] : 'none';

            ?>
            <div id="post-<?= $this_post_info['post_id'] ?>" data-key="<?= $this_post_key ?>" data-user="<?= $this_post_info['user_id'] ?>" title="<?= !empty($this_post_info['post_deleted']) ? ($temp_post_author.' on '.str_replace(' ', '&nbsp;', $temp_post_date)) : '' ?>" class="subbody post_subbody post_subbody_<?= $this_post_float ?> <?= !empty($this_post_info['post_deleted']) ? 'post_subbody_deleted' : '' ?> post_<?= $this_post_float ?>" style="<?= !empty($this_post_info['post_deleted']) ? 'margin-top: 0; padding: 0 10px; background-color: transparent; float: '.$this_post_float.'; ' : 'clear: '.$this_post_float.'; ' ?>">
                <? if(empty($this_post_info['post_deleted'])): ?>
                    <div class="userblock player_type_<?= $temp_avatar_colour ?>">
                        <div class="name">
                            <?= !$temp_post_guest ? '<a href="leaderboard/'.$this_post_info['user_name_clean'].'/">' : '' ?>
                            <strong data-tooltip-type="player_type_<?= $temp_avatar_colour ?>" title="<?= $temp_post_author.($temp_is_contributor ? ' | '.$temp_item_title : ' | Player').($temp_is_online ? ' | Online' : '') ?>" style="<?= $temp_is_online ? 'text-shadow: 0 0 2px rgba(0, 255, 0, 0.20); ' : '' ?>"><?= $temp_post_author ?></strong>
                            <?= !$temp_post_guest ? '</a>' : '' ?>
                        </div>
                        <div class="<?= $temp_avatar_class ?> avatar_fieldback" style="background-image: url(<?= !empty($temp_background_path) ? $temp_background_path : 'images/fields/'.MMRPG_SETTINGS_CURRENT_FIELDTOKEN.'/battle-field_avatar.png' ?>?<?=MMRPG_CONFIG_CACHE_DATE?>); background-size: 100px 100px;">
                            &nbsp;
                        </div>
                        <div class="<?= $temp_avatar_class ?> avatar_userimage" style="">
                            <?/*<div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/robots/mega-man/sprite_left_40x40.png);"><?= $temp_thread_author ?></div>*/?>
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
                        <div class="published" title="<?= $temp_post_author.' on '.str_replace(' ', '&nbsp;', $temp_post_date) ?>">
                            <strong>Posted on <?= $temp_post_date ?></strong> <span style="float: <?= $this_post_direction ?>; color: #565656; padding-left: 6px;">#<?= $this_post_key + 1 ?></span>
                            <?= !empty($temp_post_mod) ? '<span style="padding-left: 20px; color: rgb(119, 119, 119); letter-spacing: 1px; font-size: 10px;">'.$temp_post_mod.'</span>' : '' ?>
                            <?= $temp_is_new ? '<strong style="padding-left: 10px; color: rgb(187, 184, 115); letter-spacing: 1px;">(New!)</strong>' : '' ?>
                            <? if(!$temp_post_guest && (COMMUNITY_VIEW_MODERATOR || $this_userinfo['user_id'] == $this_post_info['user_id'])): ?>
                                <? if($this_thread_info['thread_target'] == 0): ?>
                                    <span class="options">[ <a class="edit" rel="noindex,nofollow" href="<?= $_GET['this_current_url'].'action=edit&amp;post_id='.$this_post_info['post_id'].'#comment-form' ?>">edit</a> | <a class="delete" rel="noindex,nofollow" href="<?= $_GET['this_current_url'] ?>" data-href="<?= $_GET['this_current_url'].'action=delete&amp;post_id='.$this_post_info['post_id'].'#comment-form' ?>">delete</a> ]</span>
                                <? endif; ?>
                            <? endif; ?>
                        </div>
                        <div class="bodytext"><?= mmrpg_formatting_decode($temp_post_body) ?></div>
                    </div>
                    <? if($this_userid != MMRPG_SETTINGS_GUEST_ID && empty($this_thread_info['thread_locked']) && $community_battle_points > MMRPG_SETTINGS_POST_MINPOINTS && $this_category_info['category_token'] != 'personal'): ?>
                        <a class="postreply" rel="nofollow" href="<?= 'community/'.$this_category_info['category_token'].'/'.$this_thread_info['thread_id'].'/'.$this_thread_info['thread_token'].'/#comment-form:'.$temp_reply_name.':'.$temp_reply_colour ?>" style="<?= $this_post_direction ?>: 46px;">@ Reply</a>
                    <? endif; ?>
                    <a class="postscroll" href="#top" style="<?= $this_post_direction ?>: 12px;">^ Top</a>
                <? else: ?>
                    <span style="color: #464646;">- deleted -</span>
                <? endif; ?>
            </div>
            <?
        }

        // Print out the comment listing's footer and links
        ?>
        <div class="<?= $comment_header_class ?>">
            <strong class="thread_posts_total"><?= $comment_header_title ?></strong>
            <? if (!empty($comment_header_links)){ ?>
                <div class="thread_posts_pages">
                    Page : <?= implode("\n", $comment_header_links) ?>
                </div>
            <? } ?>
        </div>
        <?

    }

    ?>

    <? if(($this_userid != MMRPG_SETTINGS_GUEST_ID && empty($this_thread_info['thread_locked']) && $community_battle_points >= MMRPG_SETTINGS_POST_MINPOINTS) || $this_category_info['category_token'] == 'personal'): ?>
    <h2 id="comment-form" class="subheader thread_posts_count field_type_<?= !empty($this_thread_info['thread_colour']) ? $this_thread_info['thread_colour'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>"><?= !empty($_REQUEST['post_id']) ? 'Edit' : 'Post' ?> Comment <?= !empty($_REQUEST['post_id']) ? '<a class="link" style="float: right; color: rgb(146, 146, 146); " href="'.$_GET['this_current_url'].'#comment-listing">Cancel</a>' : '' ?></h2>
    <div class="subbody thread_posts_form post_subbody">
        <form class="form" action="<?= $_GET['this_current_url'].(!empty($_REQUEST['post_id']) ? 'post_id='.$_REQUEST['post_id'] : '').(!empty($_REQUEST['post_id']) ? '#post-'.$_REQUEST['post_id'] : '#comment-listing') ?>" method="post">
            <? if (defined('COMMENT_POST_SUCCESSFUL') && COMMENT_POST_SUCCESSFUL === true): ?>
                <p class="text" style="color: #65C054; margin: 0;">(!) Thank you, your comment has been <?= !empty($_REQUEST['post_id']) ? 'edited' : 'posted' ?>!<br />Would you like to <a style="color: #65C054;" href="<?= $_GET['this_current_url'] ?>">reload the page</a>?</p>
                <? if (!empty($this_formerrors)){ foreach ($this_formerrors AS $error_text){ echo '<p class="text" style="color: #969696; font-size: 90%; margin: 0;">- '.$error_text.'</p>'; } } echo '<br />'; ?>
            <? elseif (defined('COMMENT_POST_SUCCESSFUL') && COMMENT_POST_SUCCESSFUL === false): ?>
                <p class="text" style="color: #E43131; margin: 0;">(!) Your comment could not be posted. Please review and correct the errors below.</p>
                <? if (!empty($this_formerrors)){ foreach ($this_formerrors AS $error_text){ echo '<p class="text" style="color: #969696; font-size: 90%; margin: 0;">- '.$error_text.'</p>'; } } echo '<br />'; ?>
            <? endif;?>
            <? if (!defined('COMMENT_POST_SUCCESSFUL') || (defined('COMMENT_POST_SUCCESSFUL') && COMMENT_POST_SUCCESSFUL === false)): ?>
                <?
                // Define and display the avatar variables
                $temp_avatar_guest = $this_userid == MMRPG_SETTINGS_GUEST_ID ? true : false;
                $temp_avatar_name = (!empty($this_userinfo['user_name_public']) ? $this_userinfo['user_name_public'] : $this_userinfo['user_name']);
                $temp_avatar_title = '#'.$this_userid.' : '.$temp_avatar_name;

                // Define the avatar class and path variables
                $temp_avatar_path = !$temp_avatar_guest ? (!empty($this_userinfo['user_image_path']) ? $this_userinfo['user_image_path'] : 'robots/mega-man/40') : 'robots/robot/40';  //!empty($this_thread_info['user_image_path']) ? $this_thread_info['user_image_path'] : 'robots/mega-man/40';
                $temp_post_id = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0;
                $temp_post_body = isset($_POST['post_body']) ? htmlentities($_POST['post_body'], ENT_QUOTES, 'UTF-8', true) : '';
                $temp_avatar_frame = isset($_REQUEST['post_frame']) ? $_REQUEST['post_frame'] : '00';
                if (!empty($temp_post_id)){
                    $temp_post_info = $DB->get_array("SELECT mmrpg_posts.*, mmrpg_users.user_image_path FROM mmrpg_posts LEFT JOIN mmrpg_users on mmrpg_users.user_id = mmrpg_posts.user_id WHERE post_id = {$temp_post_id} ".(!COMMUNITY_VIEW_MODERATOR ? " AND mmrpg_posts.user_id = {$this_userid}" : ''));
                    //die('$temp_post_info = <pre>'.print_r($temp_post_info, true).'</pre>');
                    $temp_post_body = !empty($temp_post_info['post_body']) ? htmlentities($temp_post_info['post_body'], ENT_QUOTES, 'UTF-8', true) : '';
                    $temp_avatar_path = !empty($temp_post_info['user_image_path']) ? $temp_post_info['user_image_path'] : $temp_avatar_path;
                    $temp_avatar_frame = !empty($temp_post_info['post_frame']) ? $temp_post_info['post_frame'] : $temp_avatar_frame;
                }
                list($temp_avatar_kind, $temp_avatar_token, $temp_avatar_size) = explode('/', $temp_avatar_path);
                $temp_avatar_size = $temp_avatar_size * 2;
                $temp_avatar_frames_count = $temp_avatar_kind == 'players' ? 6 : 10;
                $temp_avatar_frames = array();
                for ($i = 0; $i < $temp_avatar_frames_count; $i++){ $temp_avatar_frames[] = str_pad($i, 2, '0', STR_PAD_LEFT); }
                $temp_avatar_frames = implode(',', $temp_avatar_frames);
                $temp_avatar_class = 'avatar avatar_80x80 float float_left avatar_selector ';
                $temp_sprite_class = 'sprite sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.' sprite_'.$temp_avatar_size.'x'.$temp_avatar_size.'_'.$temp_avatar_frame;
                $temp_sprite_path = 'images/'.$temp_avatar_kind.'/'.$temp_avatar_token.'/sprite_right_'.$temp_avatar_size.'x'.$temp_avatar_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;

                // Collect the post target if applicable
                $temp_post_target = 0;
                if ($this_thread_info['user_id'] != $this_userinfo['user_id']){ $temp_post_target = $this_thread_info['user_id']; }
                elseif (!empty($this_thread_info['thread_target']) && $this_thread_info['thread_target'] != $this_userinfo['user_id']){ $temp_post_target = $this_thread_info['thread_target']; }

                ?>
                <input type="hidden" class="hidden" name="formaction" value="post" />
                <input type="hidden" class="hidden" name="category_id" value="<?= $this_category_info['category_id'] ?>" />
                <input type="hidden" class="hidden" name="thread_id" value="<?= $this_thread_info['thread_id'] ?>" />
                <input type="hidden" class="hidden" name="user_id" value="<?= COMMUNITY_VIEW_MODERATOR && !empty($temp_post_info['user_id']) ? $temp_post_info['user_id'] : $this_userinfo['user_id'] ?>" />
                <input type="hidden" class="hidden" name="post_id" value="<?= $temp_post_id ?>" />
                <input type="hidden" class="hidden" name="user_ip" value="<?= !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0' ?>" />
                <input type="hidden" class="hidden" name="post_frame" value="<?= $temp_avatar_frame ?>" />
                <input type="hidden" class="hidden" name="post_time" value="<?= time() ?>" />
                <?= $this_category_info['category_id'] == 0 ? '<input type="hidden" class="hidden" name="post_target" value="'.$temp_post_target.'" />' : '' ?>
                <div class="field field_post_info" style="overflow: hidden; font-size: 11px;">
                    <label class="label" style="float: left;"><?= $temp_avatar_guest ? 'Posting as' : 'Logged in as' ?> <strong><?= $temp_avatar_name ?></strong> :</label>
                </div>
                <div class="field field_post_body">
                    <div class="<?= $temp_avatar_class ?>" style="">
                        <div class="<?= $temp_sprite_class ?>" data-frames="<?=$temp_avatar_frames?>" style="background-image: url(<?= $temp_sprite_path ?>); "><?= $temp_avatar_title ?></div>
                        <a class="back">&#9668;</a>
                        <a class="next">&#9658;</a>
                    </div>
                    <textarea class="textarea" name="post_body" rows="10"><?= str_replace("\n", '\\n', $temp_post_body) ?></textarea>
                </div>
                <div class="field field_post_info" style="clear: left; overflow: hidden; font-size: 11px;">
                    <?= mmrpg_formatting_help() ?>
                </div>
                <div class="buttons buttons_active" data-submit="<?= !empty($_REQUEST['post_id']) ? 'Edit' : 'Post' ?> Comment">
                    <label class="counter"><span class="current">0</span> / <span class="maximum"><?= MMRPG_SETTINGS_COMMENT_MAXLENGTH ?></span> Characters</label>
                </div>
            <? endif; ?>
        </form>
    </div>
    <? elseif($this_userid != MMRPG_SETTINGS_GUEST_ID && empty($this_thread_info['thread_locked']) && $community_battle_points < MMRPG_SETTINGS_POST_MINPOINTS && $this_category_info['category_token'] != 'personal'): ?>
    <h2 id="comment-form" class="subheader thread_posts_count" style="opacity: 0.5; filter: alpha(opacity = 50);">- you need at least <?= number_format(MMRPG_SETTINGS_POST_MINPOINTS, 0, '.', ',') ?> battle points to post a comment -</h2>
    <? elseif($this_userid == MMRPG_SETTINGS_GUEST_ID && empty($this_thread_info['thread_locked'])): ?>
    <h2 id="comment-form" class="subheader thread_posts_count" style="opacity: 0.5; filter: alpha(opacity = 50);">- <a href="file/load/return=<?= htmlentities($_GET['this_current_uri'].(!empty($_REQUEST['post_id']) ? 'post_id='.$_REQUEST['post_id'] : '').(!empty($_REQUEST['post_id']) ? '#comment-listing' : '')) ?>" rel="noindex,nofollow" style="color: #FFFFFF;">login to comment</a> -</h2>
    <? else: ?>
    <h2 id="comment-form" class="subheader thread_posts_count" style="opacity: 0.5; filter: alpha(opacity = 50);">- comments disabled -</h2>
    <? endif; ?>

</div>
<?

// Add this thread to the community session tracker array
if (!in_array($thread_session_token, $_SESSION['COMMUNITY']['threads_viewed'])){
    $_SESSION['COMMUNITY']['threads_viewed'][] = $thread_session_token;
}

if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
?>