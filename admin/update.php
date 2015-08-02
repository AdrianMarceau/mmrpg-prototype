<?
// Define a function for updating user save files
function mmrpg_admin_update_save_file($key, $data){
  global $DB, $this_save_filepath;
  // Start the markup variable
  $this_page_markup = '';
  // Expand this save files data into full arrays and update the session
  $_SESSION['GAME']['CACHE_DATE'] = $data['save_cache_date'];
  $cache_date_backup = $data['save_cache_date'];

  // If the CACHE DATE was BEFORE the serialization purge, collect with special care
  if ($_SESSION['GAME']['CACHE_DATE'] < '20140501-01'){

    //$_SESSION['GAME']['USER']['userid'] = $data['user_id'];
    $_SESSION['GAME']['flags'] = !empty($data['save_flags']) ? unserialize($data['save_flags']) : array();
    $_SESSION['GAME']['values'] = !empty($data['save_values']) ? unserialize($data['save_values']) : array();

    if (!empty($data['save_values_battle_index'])){ $_SESSION['GAME']['values']['battle_index'] = array(); /*unserialize($data['save_values_battle_index']);*/ }
    elseif (!isset($_SESSION['GAME']['values']['battle_index'])){ $_SESSION['GAME']['values']['battle_index'] = array(); }

    if (!empty($data['save_values_battle_complete'])){ $_SESSION['GAME']['values']['battle_complete'] = unserialize($data['save_values_battle_complete']); }
    elseif (!isset($_SESSION['GAME']['values']['battle_complete'])){ $_SESSION['GAME']['values']['battle_complete'] = array(); }

    if (!empty($data['save_values_battle_failure'])){ $_SESSION['GAME']['values']['battle_failure'] = unserialize($data['save_values_battle_failure']); }
    elseif (!isset($_SESSION['GAME']['values']['battle_failure'])){ $_SESSION['GAME']['values']['battle_failure'] = array(); }

    if (!empty($data['save_values_battle_settings'])){ $_SESSION['GAME']['values']['battle_settings'] = unserialize($data['save_values_battle_settings']); }
    elseif (!isset($_SESSION['GAME']['values']['battle_settings'])){ $_SESSION['GAME']['values']['battle_settings'] = array(); }

    if (!empty($data['save_values_battle_rewards'])){ $_SESSION['GAME']['values']['battle_rewards'] = unserialize($data['save_values_battle_rewards']); }
    elseif (!isset($_SESSION['GAME']['values']['battle_rewards'])){ $_SESSION['GAME']['values']['battle_rewards'] = array(); }

    if (!empty($data['save_values_battle_items'])){ $_SESSION['GAME']['values']['battle_items'] = unserialize($data['save_values_battle_items']); }
    elseif (!isset($_SESSION['GAME']['values']['battle_items'])){ $_SESSION['GAME']['values']['battle_items'] = array(); }

    if (!empty($data['save_values_battle_stars'])){ $_SESSION['GAME']['values']['battle_stars'] = unserialize($data['save_values_battle_stars']); }
    elseif (!isset($_SESSION['GAME']['values']['battle_stars'])){ $_SESSION['GAME']['values']['battle_stars'] = array(); }

    if (!empty($data['save_values_robot_database'])){ $_SESSION['GAME']['values']['robot_database'] = unserialize($data['save_values_robot_database']); }
    elseif (!isset($_SESSION['GAME']['values']['robot_database'])){ $_SESSION['GAME']['values']['robot_database'] = array(); }

    $_SESSION['GAME']['counters'] = !empty($data['save_counters']) ? unserialize($data['save_counters']) : array();

  }
  // Otherwise, if this save is AFTER the serialization purge, collect normally
  else {

    //$_SESSION['GAME']['USER']['userid'] = $data['user_id'];
    $_SESSION['GAME']['flags'] = !empty($data['save_flags']) ? json_decode($data['save_flags'], true) : array();
    $_SESSION['GAME']['values'] = !empty($data['save_values']) ? json_decode($data['save_values'], true) : array();

    if (!empty($data['save_values_battle_index'])){ $_SESSION['GAME']['values']['battle_index'] = array(); }
    elseif (!isset($_SESSION['GAME']['values']['battle_index'])){ $_SESSION['GAME']['values']['battle_index'] = array(); }

    if (!empty($data['save_values_battle_complete'])){ $_SESSION['GAME']['values']['battle_complete'] = json_decode($data['save_values_battle_complete'], true); }
    elseif (!isset($_SESSION['GAME']['values']['battle_complete'])){ $_SESSION['GAME']['values']['battle_complete'] = array(); }

    if (!empty($data['save_values_battle_failure'])){ $_SESSION['GAME']['values']['battle_failure'] = json_decode($data['save_values_battle_failure'], true); }
    elseif (!isset($_SESSION['GAME']['values']['battle_failure'])){ $_SESSION['GAME']['values']['battle_failure'] = array(); }

    if (!empty($data['save_values_battle_settings'])){ $_SESSION['GAME']['values']['battle_settings'] = json_decode($data['save_values_battle_settings'], true); }
    elseif (!isset($_SESSION['GAME']['values']['battle_settings'])){ $_SESSION['GAME']['values']['battle_settings'] = array(); }

    if (!empty($data['save_values_battle_rewards'])){ $_SESSION['GAME']['values']['battle_rewards'] = json_decode($data['save_values_battle_rewards'], true); }
    elseif (!isset($_SESSION['GAME']['values']['battle_rewards'])){ $_SESSION['GAME']['values']['battle_rewards'] = array(); }

    if (!empty($data['save_values_battle_items'])){ $_SESSION['GAME']['values']['battle_items'] = json_decode($data['save_values_battle_items'], true); }
    elseif (!isset($_SESSION['GAME']['values']['battle_items'])){ $_SESSION['GAME']['values']['battle_items'] = array(); }

    if (!empty($data['save_values_battle_stars'])){ $_SESSION['GAME']['values']['battle_stars'] = json_decode($data['save_values_battle_stars'], true); }
    elseif (!isset($_SESSION['GAME']['values']['battle_stars'])){ $_SESSION['GAME']['values']['battle_stars'] = array(); }

    if (!empty($data['save_values_robot_database'])){ $_SESSION['GAME']['values']['robot_database'] = json_decode($data['save_values_robot_database'], true); }
    elseif (!isset($_SESSION['GAME']['values']['robot_database'])){ $_SESSION['GAME']['values']['robot_database'] = array(); }

    $_SESSION['GAME']['counters'] = !empty($data['save_counters']) ? json_decode($data['save_counters'], true) : array();

  }

  // Include the file updates list
  $_SESSION['TEMP']['temp_update_user_id'] = $data['user_id'];
  $_SESSION['TEMP']['temp_update_user_name_clean'] = $data['user_name_clean'];
  require('file_updates.php');

  // Recompress and prepare the save data for the database
  $temp_values = $_SESSION['GAME']['values'];
  unset($temp_values['battle_index'], $temp_values['battle_complete'], $temp_values['battle_failure'],
  $temp_values['battle_rewards'], $temp_values['battle_settings'], $temp_values['battle_items'],
  $temp_values['battle_stars'], $temp_values['robot_database']);
  $update_array = array(
    'save_cache_date' => MMRPG_CONFIG_CACHE_DATE,
    'save_flags' => mmrpg_admin_encode_save_data($_SESSION['GAME']['flags'], $cache_date_backup),
    'save_values' => mmrpg_admin_encode_save_data($temp_values, $cache_date_backup),
    'save_values_battle_index' => mmrpg_admin_encode_save_data($_SESSION['GAME']['values']['battle_index'], $cache_date_backup),
    'save_values_battle_complete' => mmrpg_admin_encode_save_data($_SESSION['GAME']['values']['battle_complete'], $cache_date_backup),
    'save_values_battle_failure' => mmrpg_admin_encode_save_data($_SESSION['GAME']['values']['battle_failure'], $cache_date_backup),
    'save_values_battle_rewards' => mmrpg_admin_encode_save_data($_SESSION['GAME']['values']['battle_rewards'], $cache_date_backup),
    'save_values_battle_settings' => mmrpg_admin_encode_save_data($_SESSION['GAME']['values']['battle_settings'], $cache_date_backup),
    'save_values_battle_items' => mmrpg_admin_encode_save_data($_SESSION['GAME']['values']['battle_items'], $cache_date_backup),
    'save_values_battle_stars' => mmrpg_admin_encode_save_data($_SESSION['GAME']['values']['battle_stars'], $cache_date_backup),
    'save_values_robot_database' => mmrpg_admin_encode_save_data($_SESSION['GAME']['values']['robot_database'], $cache_date_backup),
    'save_counters' => mmrpg_admin_encode_save_data($_SESSION['GAME']['counters'], $cache_date_backup)
    );

  //die('$_SESSION[\'GAME\'][\'values\']('.$data['user_id'].':'.$data['user_name_clean'].') => <pre>'.print_r($_SESSION['GAME']['values'], true).'</pre>');
  //die('$update_array('.$data['user_id'].':'.$data['user_name_clean'].') => <pre>'.print_r($update_array, true).'</pre>');
  //die('$update_array[\'save_values\'] : '.$update_array['save_values']);

  // Update the database with the recent changes
  $temp_success = $DB->update('mmrpg_saves', $update_array, "save_id = {$data['save_id']}");
  // If there was an error, print it, else continue

  // DEBUG
  $this_page_markup .= '<p style="margin: 2px auto; padding: 6px; background-color: '.($temp_success === false ? 'rgb(255, 218, 218)' : 'rgb(218, 255, 218)').';">';

    // Print the debug headers
    $this_page_markup .= '<strong>$this_update_list['.$key.']['.$data['user_name_clean'].']</strong><br />';
    $this_page_markup .= 'Save ID:'.$data['save_id'].'<br />';

    // Check to see which fields have been updated
    if ($update_array['save_cache_date'] != $data['save_cache_date']){ $this_page_markup .= 'Save cache date has been changed...<br />'; }
    if ($update_array['save_flags'] != $data['save_flags']){ $this_page_markup .= 'Save flags have been changed...<br />'; }
    if ($update_array['save_values'] != $data['save_values']){ $this_page_markup .= 'Save values have been changed...<br />'; }
    if ($update_array['save_values_battle_index'] != $data['save_values_battle_index']){ $this_page_markup .= 'Save values battle index has been changed...<br />'; }
    if ($update_array['save_values_battle_complete'] != $data['save_values_battle_complete']){ $this_page_markup .= 'Save values battle complete has been changed...<br />'; }
    if ($update_array['save_values_battle_failure'] != $data['save_values_battle_failure']){ $this_page_markup .= 'Save values battle failure has been changed...<br />'; }
    if ($update_array['save_values_battle_rewards'] != $data['save_values_battle_rewards']){ $this_page_markup .= 'Save values battle rewards has been changed...<br />'; }
    if ($update_array['save_values_battle_settings'] != $data['save_values_battle_settings']){ $this_page_markup .= 'Save values battle settings has been changed...<br />'; }
    if ($update_array['save_values_battle_items'] != $data['save_values_battle_items']){ $this_page_markup .= 'Save values battle items has been changed...<br />'; }
    if ($update_array['save_values_battle_stars'] != $data['save_values_battle_stars']){ $this_page_markup .= 'Save values battle stars has been changed...<br />'; }
    if ($update_array['save_values_robot_database'] != $data['save_values_robot_database']){ $this_page_markup .= 'Save values robot_database has been changed...<br />'; }
    if ($update_array['save_counters'] != $data['save_counters']){ $this_page_markup .= 'Save counters have been changed...<br />'; }
    //$this_page_markup .= '<pre>$_SESSION[\'GAME\'][\'values\'] : '.print_r($_SESSION['GAME']['values'], true).'</pre><br /><hr /><br />';
    if ($temp_success === false){ $this_page_markup .= '...Failure!'; }
    else { $this_page_markup .= '...'.(!empty($temp_success) ? 'Success!' : 'Skipped!'); }
    unset($update_array);

  $this_page_markup .= '</p><hr />';

  // Reset everything back to default
  mmrpg_reset_game_session($this_save_filepath);

  // Return generated page markup
  return $this_page_markup;
}

// Define a function for search and replacing typos before re-encoding
function mmrpg_admin_encode_save_data($data, $cache){
  static $typo_find_replace;
  if (empty($typo_find_replace)){
    $typo_find_replace = array();

    // If this was before the May 2014 serialization and typo purge
    if ($cache < '20140501-01'){
      $typo_find_replace = array(
        'Lightning ' => 'Lighting ',
        'lightning-' => 'lighting-',
        'Crystal ' => 'Photon ',
        'crystal-' => 'photon-',
        ' Cave' => ' Collider',
        '-cave' => '-collider'
        );
    }

    // If this was before the projected 2k15 update's release
    if ($cache < '20160101-01'){
      $typo_find_replace = array(
        'dive-torpedo' => 'dive-missile',
        'pharaoh-shot' => 'pharaoh-soul'
        );
    }

  }
  $typo_find = array_keys($typo_find_replace);
  $typo_replace = array_values($typo_find_replace);
  if (is_array($data)){ $data = json_encode($data); }
  $data = str_replace($typo_find, $typo_replace, $data);
  return $data;
}

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while updating!');  }

// Collect any extra request variables for the update
$this_cache_date = !empty($_REQUEST['date']) && preg_match('/^([0-9]{8})-([0-9]{2})$/', $_REQUEST['date']) ? $_REQUEST['date'] : MMRPG_CONFIG_CACHE_DATE;
$this_update_limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;
$this_request_type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'index';
$this_return_markup = '';

// Collect any save files that have a cache date less than the current one // AND mmrpg_saves.user_id = 110
$this_update_query = "SELECT mmrpg_saves.*, mmrpg_leaderboard.board_points, mmrpg_users.user_name_clean FROM mmrpg_saves
	LEFT JOIN mmrpg_leaderboard ON mmrpg_leaderboard.user_id = mmrpg_saves.user_id
	LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_saves.user_id
	WHERE save_cache_date < '{$this_cache_date}' AND board_points > 0
	AND mmrpg_users.user_id <> ".MMRPG_SETTINGS_GUEST_ID."
	".(!empty($_REQUEST['user_id']) ? "AND mmrpg_users.user_id = {$_REQUEST['user_id']} " : '')."
	ORDER BY board_points DESC
	LIMIT {$this_update_limit}";
//die($this_update_query);
$this_total_query = "SELECT mmrpg_saves.user_id, mmrpg_saves.save_cache_date, mmrpg_leaderboard.board_points, mmrpg_users.user_name_clean FROM mmrpg_saves
	LEFT JOIN mmrpg_leaderboard ON mmrpg_leaderboard.user_id = mmrpg_saves.user_id
	LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_saves.user_id
	WHERE save_cache_date < '{$this_cache_date}' AND board_points > 0
	AND mmrpg_users.user_id <> ".MMRPG_SETTINGS_GUEST_ID."
	".(!empty($_REQUEST['user_id']) ? "AND mmrpg_users.user_id = {$_REQUEST['user_id']} " : '')."
	ORDER BY board_points DESC";
//die($this_update_query);
$this_update_list = $DB->get_array_list($this_update_query);
$this_total_list = $DB->get_array_list($this_total_query);
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
    <a href="admin.php?action=update&date=<?=$this_cache_date?>&limit=<?=$this_update_limit?>">Update Save Files</a> &raquo;
    <br />
    <a href="admin.php?action=update&date=<?=$this_cache_date?>&limit=1" data-limit="1">x1</a>
    | <a href="admin.php?action=update&date=<?=$this_cache_date?>&limit=10" data-limit="10">x10</a>
    | <a href="admin.php?action=update&date=<?=$this_cache_date?>&limit=50" data-limit="50">x50</a>
    | <a href="admin.php?action=update&date=<?=$this_cache_date?>&limit=100" data-limit="100">x100</a>
    | <a href="admin.php?action=update&date=<?=$this_cache_date?>&limit=200" data-limit="200">x200</a>
    | <a href="admin.php?action=update&date=<?=$this_cache_date?>&limit=500" data-limit="500">x500</a>
    | <a href="admin.php?action=update&date=<?=$this_cache_date?>&limit=1000" data-limit="1000">x1000</a>
  </div>
  <?
  $this_page_markup .= ob_get_clean();
}

// DEBUG
if ($this_request_type == 'index'){
  $this_page_markup .= '<p style="margin-bottom: 10px;"><strong>$this_update_list</strong><br />';
  $this_page_markup .= 'Query: <span>'.$this_update_query.'</span><br />';
  $this_page_markup .= '<strong>Count: <span id="count_pending" style="color: #9C9C9C;">0</span> / <span id="count_completed">'.$this_update_count.'</span> / <span id="count_total">'.$this_total_count.'</span></strong><br />';
  $this_page_markup .= '</p>';
  $this_page_markup .= '<div id="results"></div>';
}
elseif ($this_request_type == 'ajax'){
  //$this_return_markup .= "query/".preg_replace('/\s+/', ' ', $this_update_query)."\n";
  $this_return_markup .= "query/".md5($this_update_query)."\n";
  $this_return_markup .= "count/{$this_update_count}/{$this_total_count}\n";
}


// Loop through each of the player save files
if (!empty($this_update_list) && $this_request_type == 'ajax'){
  foreach ($this_update_list AS $key => $data){
    // maybe exit on memory overload?
    //if ($this_request_type == 'index'){ $this_page_markup .= mmrpg_admin_update_save_file($key, $data); }
    //if ($this_request_type == 'ajax') { $this_return_markup .= preg_replace('/\s+/', ' ', mmrpg_admin_update_save_file($key, $data))."\n"; }
    //elseif ($this_request_type == 'ajax') { $this_return_markup .= mmrpg_admin_update_save_file($key, $data) ? "update/success/{$key}\n" : "update/failure/{$key}\n"; }
    if ($data['user_id'] == MMRPG_SETTINGS_GUEST_ID){ continue; }
    //die($this_return_markup."\n".print_r($data, true));
    $this_return_markup .= preg_replace('/\s+/', ' ', mmrpg_admin_update_save_file($key, $data))."\n";
  }
  $key = $data = false;
  // DEBUG
  //$this_page_markup .= '<strong>$this_update_list</strong><br />';
  //$this_page_markup .= 'Query:'.$this_update_query.'<br />';
  //$this_page_markup .= 'Count:'.count($this_update_list).'<br />';
  //$this_page_markup .= '<pre>'.print_r($this_update_list, true).'</pre><br /><hr /><br />';
}
// Otherwise, if empty, we're done!
elseif (empty($this_update_list) && $this_request_type == 'ajax'){
  //if ($this_request_type == 'index'){ $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL SAVE FILES UPDATED!</strong></p>'; }
  //elseif ($this_request_type == 'ajax'){ $this_return_markup .= "update/complete/--\n"; }
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
var thisCacheDate = '<?= $this_cache_date ?>';
var thisContent = false;
var thisMenu = false;
var thisResults = false;
var thisPendingCounter = false;
var thisCompletedCounter = false;

$(document).ready(function(){

  thisContent = $('#admin .content');
  thisMenu = $('#menu', thisContent);
  thisResults = $('#results', thisContent);
  thisPendingCounter = $('#count_pending', thisContent);
  thisCompletedCounter = $('#count_completed', thisContent);

  $('a[data-limit]', thisMenu).click(function(e){
    e.preventDefault();
    if (pendingUpdates > 0){ return false; }
    var thisLimit = parseInt($(this).attr('data-limit'));
    if (completedUpdates + thisLimit <= totalUpdates){ pendingUpdates = thisLimit;  }
    else { pendingUpdates = totalUpdates - completedUpdates; }
    if (pendingUpdates != 0){ admin_trigger_update(); }
    });

});

function admin_trigger_update(){
  if (pendingUpdates > 0){

      // Define the post data array
      var postData = {date:thisCacheDate,limit:1};

      // Post this change back to the server
      $.ajax({
        type: 'POST',
        url: 'admin.php?action=update&type=ajax',
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

            dataContent = $(dataContent);
            dataContent.css({height:'1px',overflow:'hidden',opacity:0});
            dataContent.prependTo(thisResults).animate({height:'100%',opacity:1},200,'swing');

            pendingUpdates--;
            completedUpdates++;
            thisPendingCounter.html(pendingUpdates);
            thisCompletedCounter.html(completedUpdates);

            if (pendingUpdates > 0){
              var thisTimeout = setTimeout(function(){ return admin_trigger_update(); }, 500);
              return true;
              } else {
              thisCompletedCounter.css({color:'rgb(0, 139, 0)',opacity:0.5}).animate({opacity:1.0},1000,'swing',function(){ thisCompletedCounter.css({color:'rgb(0, 0, 0)'}); });
              return true;
              }

            }

        }});
  }
}

</script>
  <?
  $this_page_markup .= ob_get_clean();
}

?>