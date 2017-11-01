<?

// Require the update actions file
set_time_limit(30);
$update_patch_tokens = array();
$update_patch_tokens_disabled = array();
require_once('update_actions.php');
require_once('update_patches.php');

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while updating!');  }

// Collect any extra request variables for the update
$this_cache_date = !empty($_REQUEST['date']) && preg_match('/^([0-9]{8})-([0-9]{2})$/', $_REQUEST['date']) ? $_REQUEST['date'] : MMRPG_CONFIG_CACHE_DATE;
$this_update_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;
$this_request_type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'index';
$this_request_id = !empty($_REQUEST['id']) && is_numeric($_REQUEST['id']) ? $_REQUEST['id'] : 0;
$this_request_patch = !empty($_REQUEST['patch']) ? trim($_REQUEST['patch']) : '';
$this_request_force = isset($_REQUEST['force']) && $_REQUEST['force'] == 'true' ? true : false;
$this_request_print = isset($_REQUEST['print']) && $_REQUEST['print'] == 'true' ? true : false;
$this_return_markup = '';

// If we're in an ajax request, set the ADMIN constant
$this_ajax_request_feedback = '';
if ($this_request_type == 'ajax'){
    define('MMRPG_ADMIN_AJAX_REQUEST', true);
}

// Prevent undefined patches from being applied
if (!in_array($this_request_patch, $update_patch_tokens)){
    $this_request_type = 'index';
    $this_request_patch = '';
    $this_request_id = 0;
}

// Define a WHERE clause for queries if user ID provided
$this_select_query = '';
$this_join_query = '';
$this_where_query = '';
if (!empty($this_request_id)){
    $this_where_query .= "AND saves.user_id = {$this_request_id} ";
}
if (!$this_request_force && !empty($this_request_patch)){
    $this_select_query .= "spusers.patch_token, ";
    $this_join_query .= "LEFT JOIN mmrpg_saves_patches_users AS spusers ON spusers.user_id = saves.user_id AND spusers.patch_token = '{$this_request_patch}' ";
    $this_where_query .= "AND spusers.patch_token IS NULL ";
}

// Collect any save files that have a cache date less than the current one
/*
$this_update_query = "SELECT
    mmrpg_saves.*,
    mmrpg_leaderboard.board_points,
    mmrpg_users.user_name_clean
    FROM mmrpg_saves
    LEFT JOIN mmrpg_leaderboard
        ON mmrpg_leaderboard.user_id = mmrpg_saves.user_id
    LEFT JOIN mmrpg_users
        ON mmrpg_users.user_id = mmrpg_saves.user_id
    WHERE
        board_points > 0 {$this_where_query}
    ORDER BY
        board_points DESC
    LIMIT
        {$this_update_limit}
        ;";
        */

$this_update_query = "SELECT
    {$this_select_query}
    saves.save_id,
    saves.user_id,
    saves.save_counters,
    saves.save_values,
    saves2.save_values_battle_complete,
    saves2.save_values_battle_failure,
    saves2.save_values_battle_rewards,
    saves2.save_values_battle_settings,
    saves2.save_values_battle_items,
    saves2.save_values_battle_abilities,
    saves2.save_values_battle_stars,
    saves2.save_values_robot_database,
    saves2.save_values_robot_alts,
    saves.save_flags,
    saves.save_settings,
    saves.save_cache_date,
    saves.save_date_created,
    saves.save_date_accessed,
    saves.save_date_modified,
    GROUP_CONCAT(patches.patch_token) AS save_patches_applied,
    lboard.board_points,
    users.user_name_clean
    FROM mmrpg_saves AS saves
    LEFT JOIN mmrpg_saves_legacy AS saves2 ON saves2.user_id = saves.user_id
    LEFT JOIN mmrpg_leaderboard AS lboard ON lboard.user_id = saves.user_id
    LEFT JOIN mmrpg_users AS users ON users.user_id = saves.user_id
    LEFT JOIN (SELECT user_id, patch_token FROM mmrpg_saves_patches_users) AS patches ON patches.user_id = saves.user_id
    {$this_join_query}
    WHERE
    1 = 1
    {$this_where_query}
    ORDER BY
    lboard.board_points DESC
    LIMIT {$this_update_limit}
    ;";

//echo('<pre>$this_update_query = '.print_r($this_update_query, true).'</pre>'.PHP_EOL);

$this_update_list = $db->get_array_list($this_update_query);
$this_update_count = $this_request_type == 'ajax' && !empty($this_update_list) ? count($this_update_list) : 0;
$this_update_list = !empty($this_update_list) ? $this_update_list : array();

/*
$this_total_query = "SELECT
    mmrpg_saves.user_id,
    mmrpg_saves.save_cache_date,
    mmrpg_leaderboard.board_points,
    mmrpg_users.user_name_clean
    FROM mmrpg_saves
    LEFT JOIN mmrpg_leaderboard
        ON mmrpg_leaderboard.user_id = mmrpg_saves.user_id
    LEFT JOIN mmrpg_users
        ON mmrpg_users.user_id = mmrpg_saves.user_id
    WHERE
        board_points > 0 {$this_where_query}
    ORDER BY
        board_points DESC
        ;";
*/

$this_total_query = "SELECT
    COUNT(*) AS total_users
    FROM mmrpg_saves AS saves
    LEFT JOIN mmrpg_leaderboard AS lboard ON lboard.user_id = saves.user_id
    LEFT JOIN mmrpg_users AS users ON users.user_id = saves.user_id
    {$this_join_query}
    WHERE
    lboard.board_points > 0
    {$this_where_query}
    ORDER BY
    lboard.board_points DESC
    ;";

$this_total_count = $db->get_value($this_total_query, 'total_users');

// If the request type is ajax, clear the generated page markup
if ($this_request_type == 'ajax'){ $this_page_markup = ''; }

// Print out the menu header so we know where we are
if ($this_request_type != 'ajax'){
    ob_start();
    ?>
    <div id="menu" style="margin: 0 auto 20px; font-weight: bold;">
        <a href="admin.php">Admin Panel</a> &raquo;
        <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=<?=$this_update_limit?>">Update Save Files</a> &raquo;
        <? if (empty($this_request_patch)){ ?>
            <br /><br /><strong>Select Patch</strong> :<br />
            <? foreach ($update_patch_tokens AS $key => $patch){ ?>
                <? if (!in_array($patch, $update_patch_tokens_disabled)): ?>
                    + <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=<?=$this_update_limit?>&amp;patch=<?=$patch?>"><?= $update_patch_names[$patch] ?></a>
                <? else: ?>
                    + <a style="text-decoration: line-through;"><?= $update_patch_names[$patch] ?></a>
            <? endif; ?>
                <br />
            <? } ?>
        <? } else { ?>
            <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=<?=$this_update_limit?>&amp;patch=<?=$this_request_patch?>"><?= $update_patch_names[$this_request_patch] ?></a> &raquo;
            <br />
            <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=1&amp;patch=<?=$this_request_patch?>" data-limit="1">x1</a>
            |  <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=10&amp;patch=<?=$this_request_patch?>" data-limit="10">x10</a>
            | <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=50&amp;patch=<?=$this_request_patch?>" data-limit="50">x50</a>
            | <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=100&amp;patch=<?=$this_request_patch?>" data-limit="100">x100</a>
            | <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=200&amp;patch=<?=$this_request_patch?>" data-limit="200">x200</a>
            | <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=500&amp;patch=<?=$this_request_patch?>" data-limit="500">x500</a>
            | <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=1000&amp;patch=<?=$this_request_patch?>" data-limit="1000">x1000</a>
            | <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=2500&amp;patch=<?=$this_request_patch?>" data-limit="2500">x2500</a>
            | <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=5000&amp;patch=<?=$this_request_patch?>" data-limit="5000">x5000</a>
        <? } ?>
    </div>
    <?
    $this_page_markup .= ob_get_clean();
}

// DEBUG
if ($this_request_type == 'index' && !empty($this_request_patch)){
    $this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$this_update_list</strong><br />';
    $this_page_markup .= 'Query: <span>'.$this_update_query.'</span><br />';
    $this_page_markup .= '<strong>';
        $this_page_markup .= 'Count: ';
        $this_page_markup .= '<span id="count_pending" style="color: #9C9C9C;">0</span>';
        $this_page_markup .= ' / <span id="count_completed">'.$this_update_count.'</span>';
        $this_page_markup .= ' / <span id="count_total">'.$this_total_count.'</span>';
        $this_page_markup .= ' / <span id="count_percent">0%</span>';
        $this_page_markup .= ' / <span id="count_loading"><span class="img"><img src="images/ajax-loader_admin.gif" width="16" height="16" /></span><span class="dash">-</span></span>';
    $this_page_markup .= '</strong><br />';
    $this_page_markup .= '</p>';
    $this_page_markup .= '<div id="results"></div>';
}
elseif ($this_request_type == 'ajax'){
    $this_return_markup .= "query/".md5($this_update_query)."\n";
    $this_return_markup .= "count/{$this_update_count}/{$this_total_count}\n";
}




// Loop through each of the player save files
if (!empty($this_update_list) && $this_request_type == 'ajax'){
    //echo('<pre>$this_update_list = '.print_r($this_update_list, true).'</pre>'.PHP_EOL);
    foreach ($this_update_list AS $key => $data){
        $applied_patches =  !empty($data['save_patches_applied']) ? explode(',', $data['save_patches_applied']) : array();
        if ($this_request_force || !in_array($this_request_patch, $applied_patches)){
            $temp_markup = mmrpg_admin_update_save_file($key, $data, $this_request_patch);
            $this_return_markup .= preg_replace('/\s+/', ' ', $temp_markup)."\n";
        }
    }
    $key = $data = false;
}
// Otherwise, if empty, we're done!
elseif (empty($this_update_list) && $this_request_type == 'ajax'){
    $this_return_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL SAVE FILES UPDATED!</strong></p>';
}

// If this was an ajax request, flush the previous buffer and exit with the return markup
if ($this_request_type == 'ajax'){
    ob_end_clean();
    header('Content-type: text/plain;');
    echo $this_return_markup;
    exit();
}
// Otherwise, if this was an index request, let's write some javascript :)
elseif ($this_request_type == 'index'){
    ob_start();
    ?>
<script type="text/javascript">
var totalUpdates = <?= $this_total_count ?>;
var pendingUpdates = 0;
var completedUpdates = 0;
var updatePercent = 0;
var thisCacheDate = '<?= $this_cache_date ?>';
var thisContent = false;
var thisMenu = false;
var thisResults = false;
var thisPendingCounter = false;
var thisCompletedCounter = false;
var thisPercentCounter = false;

$(document).ready(function(){

    thisContent = $('#admin .content');
    thisMenu = $('#menu', thisContent);
    thisResults = $('#results', thisContent);
    thisPendingCounter = $('#count_pending', thisContent);
    thisCompletedCounter = $('#count_completed', thisContent);
    thisPercentCounter = $('#count_percent', thisContent);
    thisLoadingIcon = $('#count_loading', thisContent);

    thisLoadingIcon.find('.img').css({display:'none'});
    thisLoadingIcon.find('.dash').css({display:''});

    $('a[data-limit]', thisMenu).click(function(e){
        e.preventDefault();

        if (pendingUpdates > 0){ return false; }
        var thisLink = $(this);
        var thisHref = thisLink.attr('href') + '&type=ajax';
        var thisLimit = parseInt(thisLink.attr('data-limit'));

        if (completedUpdates + thisLimit <= totalUpdates){ pendingUpdates = thisLimit;  }
        else { pendingUpdates = totalUpdates - completedUpdates; }

        updatePercent = ((completedUpdates / totalUpdates) * 100).toFixed(2);
        var updatePercentColour = 'red';
        if (updatePercent > 33.33){ updatePercentColour = 'orange'; }
        if (updatePercent > 66.66){ updatePercentColour = 'green'; }
        thisPendingCounter.html(pendingUpdates);
        thisCompletedCounter.html(completedUpdates);
        thisPercentCounter.html(updatePercent+'%').css({color:updatePercentColour});

        if (pendingUpdates != 0){ admin_trigger_update(thisHref); }
        });

});

function admin_trigger_update(thisHref){
    if (pendingUpdates > 0){

            // Define the post data array
            var postData = {date:thisCacheDate,limit:1};

            // Post this change back to the server
            trigger_now_loading();
            $.ajax({
                type: 'POST',
                url: thisHref, //'admin.php?action=update&type=ajax',
                data: postData,
                success: function(data, status){
                    trigger_done_loading();

                    // Break apart the response into parts
                    var data = data.split('\n');
                    var dataQuery = data[0] != undefined ? data[0] : false;
                    var dataCount = data[1] != undefined ? data[1] : false;
                    var dataContent = data[2] != undefined ? data[2] : false;

                    // DEBUG
                    //console.log('dataQuery = '+dataQuery+', dataCount = '+dataCount+', dataContent = '+dataContent+'; ');

                    // If the ability change was a success, flash the box green
                    if (dataContent != false){

                        dataContent = $('<div>'+dataContent+'</div>');
                        dataContent.css({height:'1px',overflow:'hidden',opacity:0});
                        dataContent.prependTo(thisResults);
                        dataContent.animate({height:'100%',opacity:1},200,'swing');

                        pendingUpdates--;
                        completedUpdates++;
                        updatePercent = ((completedUpdates / totalUpdates) * 100).toFixed(2);
                        var updatePercentColour = 'red';
                        if (updatePercent > 33.33){ updatePercentColour = 'orange'; }
                        if (updatePercent > 66.66){ updatePercentColour = 'green'; }
                        thisPendingCounter.html(pendingUpdates);
                        thisCompletedCounter.html(completedUpdates);
                        thisPercentCounter.html(updatePercent+'%').css({color:updatePercentColour});


                        if (pendingUpdates > 0){
                            var thisTimeout = setTimeout(function(){ return admin_trigger_update(thisHref); }, 100);
                            return true;
                            } else {
                            thisCompletedCounter.css({color:'rgb(0, 139, 0)',opacity:0.5}).animate({opacity:1.0},1000,'swing',function(){ thisCompletedCounter.css({color:'rgb(0, 0, 0)'}); });
                            return true;
                            }

                        }

                    }
                });
    }
}

function trigger_now_loading(){
    $('#menu').css({pointerEvents:'none',opacity:0.5});
    thisLoadingIcon.find('.img').css({display:''});
    thisLoadingIcon.find('.dash').css({display:'none'});
}
function trigger_done_loading(){
    $('#menu').css({pointerEvents:'',opacity:1.0});
    thisLoadingIcon.find('.img').css({display:'none'});
    thisLoadingIcon.find('.dash').css({display:''});
}

</script>
    <?
    $this_page_markup .= ob_get_clean();
}

?>