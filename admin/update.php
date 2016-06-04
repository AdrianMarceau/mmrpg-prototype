<?

// Require the update actions file
$update_patch_tokens = array();
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
$this_return_markup = '';

// Define a WHERE clause for queries if user ID provided
$this_where_query = '';
if (!empty($this_request_id)){ $this_where_query .= "AND mmrpg_saves.user_id = {$this_request_id} "; }
if (!empty($this_request_patch)){ $this_where_query .= "AND mmrpg_saves.save_patches_applied NOT LIKE '%\"{$this_request_patch}\"%' "; }

// Collect any save files that have a cache date less than the current one // AND mmrpg_saves.user_id = 110
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
//die($this_update_query);
//die($this_update_query);
$this_update_list = $db->get_array_list($this_update_query);
$this_total_list = $db->get_array_list($this_total_query);
$this_update_count = $this_request_type == 'ajax' && !empty($this_update_list) ? count($this_update_list) : 0;
$this_total_count = !empty($this_total_list) ? count($this_total_list) : 0;
$this_update_list = !empty($this_update_list) ? $this_update_list : array();
$this_total_list = array();
//die($this_update_query);

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
                + <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=<?=$this_update_limit?>&amp;patch=<?=$patch?>"><?= $update_patch_names[$patch] ?></a>
                <br />
            <? } ?>
        <? } else { ?>
            <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=<?=$this_update_limit?>&amp;patch=<?=$this_request_patch?>"><?= $update_patch_names[$this_request_patch] ?></a> &raquo;
            <br />
            <a href="admin.php?action=update&amp;date=<?=$this_cache_date?>&amp;limit=10&amp;patch=<?=$this_request_patch?>" data-limit="10">x10</a>
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
    $this_page_markup .= '<strong>Count: <span id="count_pending" style="color: #9C9C9C;">0</span> / <span id="count_completed">'.$this_update_count.'</span> / <span id="count_total">'.$this_total_count.'</span> / <span id="count_percent">0%</span></strong><br />';
    $this_page_markup .= '</p>';
    $this_page_markup .= '<div id="results"></div>';
}
elseif ($this_request_type == 'ajax'){
    $this_return_markup .= "query/".md5($this_update_query)."\n";
    $this_return_markup .= "count/{$this_update_count}/{$this_total_count}\n";
}




// Loop through each of the player save files
if (!empty($this_update_list) && $this_request_type == 'ajax'){
    foreach ($this_update_list AS $key => $data){
        $applied_patches =  !empty($data['save_patches_applied']) ? json_decode($data['save_patches_applied'], true) : array();
        if (!in_array($this_request_patch, $applied_patches)){
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

    $('a[data-limit]', thisMenu).click(function(e){
        e.preventDefault();
        if (pendingUpdates > 0){ return false; }
        var thisLink = $(this);
        var thisHref = thisLink.attr('href') + '&type=ajax';
        var thisLimit = parseInt(thisLink.attr('data-limit'));
        if (completedUpdates + thisLimit <= totalUpdates){ pendingUpdates = thisLimit;  }
        else { pendingUpdates = totalUpdates - completedUpdates; }
        if (pendingUpdates != 0){ admin_trigger_update(thisHref); }
        });

});

function admin_trigger_update(thisHref){
    if (pendingUpdates > 0){

            // Define the post data array
            var postData = {date:thisCacheDate,limit:1};

            // Post this change back to the server
            $.ajax({
                type: 'POST',
                url: thisHref, //'admin.php?action=update&type=ajax',
                data: postData,
                success: function(data, status){

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

</script>
    <?
    $this_page_markup .= ob_get_clean();
}

?>