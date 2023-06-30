<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../top.php');
require_once(MMRPG_CONFIG_ROOTDIR.'classes/cms_admin.php');

// Define the page title and markup variables
$this_page_title = 'MMRPG '.cms_admin::print_env_name(MMRPG_CONFIG_SERVER_ENV, true).' Admin Panel';
$this_page_markup = '';

// Collect the current action from the URL if set
$this_page_action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'home';
$this_page_tabtitle = cms_admin::print_env_name(MMRPG_CONFIG_SERVER_ENV, true).' Admin';

// Define strings to hold custom script and style links and/or markup
$admin_include_stylesheets = '';
$admin_include_javascript = '';

// Define arrays to hold any common scripts or styles to be included later
$admin_include_common_styles = array();
$admin_include_common_scripts = array();


/*
 * SAVE UPDATES REQUEST
 * If this is a save updating request, get to it!
 */

// Prevent timeouts and memory leakages
@ini_set('memory_limit', '128M'); //100MB
@ini_set('max_execution_time', 300); //300 seconds = 5 minutes

//echo('<pre>$_GET = '.print_r($_GET, true).'</pre>');
//echo('<pre>$_SERVER = '.print_r($_SERVER, true).'</pre>');
//exit();

// Define the form messages and collect any from session
$form_messages = array();
if (!empty($_SESSION['mmrpg_admin']['form_messages'])){
    $form_messages = $_SESSION['mmrpg_admin']['form_messages'];
}

// Define the form messages and collect any from session
$backup_form_data = array();
if (!empty($_SESSION['mmrpg_admin']['form_data'])){
    $backup_form_data = $_SESSION['mmrpg_admin']['form_data'];
    unset($_SESSION['mmrpg_admin']['form_data']);
}

// Define a function for saving form messages to session
function backup_form_messages(){
    global $form_messages;
    $_SESSION['mmrpg_admin']['form_messages'] = $form_messages;
}

// Define a function for saving form messages to session
function backup_form_data(){
    global $form_data;
    $_SESSION['mmrpg_admin']['form_data'] = $form_data;
}

// Define a function for generating form messages
function print_form_messages($print = true, $clear = true){
    global $form_messages;
    $this_message_markup = '';
    if (!empty($form_messages)){
        $this_message_markup .= '<ul class="list">'.PHP_EOL;
        foreach ($form_messages AS $key => $message){
            list($type, $text) = $message;
            $this_message_markup .= '<li class="message '.$type.'">';
                //$this_message_markup .= ucfirst($type).' : ';
                $this_message_markup .= $text;
            $this_message_markup .= '</li>'.PHP_EOL;
        }
        $this_message_markup .= '</ul>'.PHP_EOL;
        if ($clear){ $_SESSION['mmrpg_admin']['form_messages'] = array(); }
    }
    if (!empty($this_message_markup)){
        $this_message_markup = '<div class="messages">'.$this_message_markup.'</div>';
    }
    if ($print){ echo $this_message_markup; }
    else { return $this_message_markup; }
}

// Define a function for exiting a form action
function redirect_form_action($location){
    backup_form_messages();
    backup_form_data();
    if (!empty($location)){
        if (!preg_match('/^https?:\/\//', $location)
            && !strstr($location, MMRPG_CONFIG_ROOTURL)){
            $location = MMRPG_CONFIG_ROOTURL.ltrim($location, '/');
        }
        header('Location: '.$location);
    }
    exit();
}

// Define a function for exiting a form action
function exit_form_action($output = ''){
    backup_form_messages();
    backup_form_data();
    exit($output);
}

// Collect details for this admin user from the database
$this_admininfo = array();
if (!empty($_SESSION['admin_id'])){
    $this_admin_id = intval($_SESSION['admin_id']);
    $this_admininfo = $db->get_array("SELECT
        users.user_id,
        users.user_name,
        users.user_name_public,
        users.user_name_clean,
        roles.role_id,
        roles.role_name,
        roles.role_token,
        roles.role_level
        FROM mmrpg_users AS users
        LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
        WHERE
        users.user_id = '{$this_admin_id}'
        ORDER BY
        user_id ASC
        ;");
}


/*
// If we're not logged in yet
if (!MMRPG_CONFIG_ADMIN_MODE){
    // Require the admin home file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/login.php');
}
// Else if we're logging out now
elseif ($this_page_action == 'exit'){
    // Unset session variables and refresh page
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_username_display']);
    redirect_form_action('admin/');
}
// If this is the HOME request
elseif ($this_page_action == 'home'){
    // Require the admin home file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/home.php');
}
// Else if this is an REFRESH LEADERBOARD request
elseif ($this_page_action == 'refresh-leaderboard'){
    // Require the update file
    $_REQUEST['date'] = MMRPG_CONFIG_CACHE_DATE;
    $_REQUEST['patch'] = 'recalculate_all_battle_points';
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/update.php');
}
// Else if this is a PRURGE BOGUS USERS request
elseif ($this_page_action == 'purge-bogus-users'){
    // Require the purge file
    $_REQUEST['date'] = MMRPG_CONFIG_CACHE_DATE;
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/purge.php');
}
// Else if this is an DELETE CACHED FILES request
elseif ($this_page_action == 'delete-cached-files'){
    // Require the delete cache file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/delete-cache.php');
}
// Else if this is an CLEAR ACTIVE SESSIONS request
elseif ($this_page_action == 'clear-active-sessions'){
    // Require the clear sessions file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/clear-sessions.php');
}
// Else if this is an EDIT USERS request
elseif ($this_page_action == 'edit-users'){
    // Require the edit users file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-users.php');
}
// Else if this is an EDIT PLAYER CHARACTERS request
elseif ($this_page_action == 'edit-players'){
    // Require the edit robots file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-players.php');
}
// Else if this is an EDIT ROBOT MASTERS request
elseif ($this_page_action == 'edit-robot-masters'){
    // Require the edit robots file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-robots_masters.php');
}
// Else if this is an EDIT SUPPORT MECHAS request
elseif ($this_page_action == 'edit-support-mechas'){
    // Require the edit robots file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-robots_mechas.php');
}
// Else if this is an EDIT FORTRESS BOSSES request
elseif ($this_page_action == 'edit-fortress-bosses'){
    // Require the edit robots file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-robots_bosses.php');
}
// Else if this is an EDIT ROBOT MASTER ABILITIES request
elseif ($this_page_action == 'edit-master-abilities'
    || $this_page_action == 'edit-robot-master-abilities'){
    // Require the edit abilities file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-abilities_master.php');
}
// Else if this is an EDIT SUPPORT MECHA ABILITIES request
elseif ($this_page_action == 'edit-mecha-abilities'
    || $this_page_action == 'edit-support-mecha-abilities'){
    // Require the edit abilities file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-abilities_mecha.php');
}
// Else if this is an EDIT FORTRESS BOSS ABILITIES request
elseif ($this_page_action == 'edit-boss-abilities'
    || $this_page_action == 'edit-fortress-boss-abilities'){
    // Require the edit abilities file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-abilities_boss.php');
}
// Else if this is an EDIT BATTLE FIELDS request
elseif ($this_page_action == 'edit-fields'){
    // Require the edit fields file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-fields.php');
}
// Else if this is an EDIT ITEMS request
elseif ($this_page_action == 'edit-items'){
    // Require the edit players file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-items.php');
}
// Else if this is an EDIT PASSIVE SKILLS request
elseif ($this_page_action == 'edit-skills'){
    // Require the edit skills file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-skills.php');
}
// Else if this is an EDIT MUSIC TRACKS request
elseif ($this_page_action == 'edit-music'
    && defined('MMRPG_CONFIG_CDN_ROOTDIR')
    && file_exists(MMRPG_CONFIG_CDN_ROOTDIR)){
    // Require the edit fields file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-music.php');
}
// Else if this is an EDIT EVENT CHALLENGES request
elseif ($this_page_action == 'edit-event-challenges'){
    // Require the edit event challenges file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-challenges_event.php');
}
// Else if this is an EDIT USER CHALLENGES request
elseif ($this_page_action == 'edit-user-challenges'){
    // Require the edit event challenges file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-challenges_user.php');
}
// Else if this is an EDIT ROGUE STARS request
elseif ($this_page_action == 'edit-stars'){
    // Require the edit stars file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-stars.php');
}
// Else if this is an EDIT WEBSITE PAGES request
elseif ($this_page_action == 'edit-pages'){
    // Require the edit pages file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-pages.php');
}
// Else if this is an VIEW ERROR LOG request
elseif ($this_page_action == 'watch-error-log'){
    // Require the edit pages file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/error-log.php');
}
// Else if this is an EDIT PRIVATE MESSAGE requests
elseif ($this_page_action == 'edit-messages'){ // personal messages (private)
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-threads_private.php');
}
// Else if this is an EDIT COMMUNITY THREAD requests
elseif ($this_page_action == 'edit-threads'){ // community threads (public)
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-threads_public.php');
}
elseif ($this_page_action == 'edit-message-replies'){
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-posts_private.php');
}
elseif ($this_page_action == 'edit-thread-comments'){
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/edit-posts_public.php');
}
// Otherwise, not a valid page
else {
    // Define error 404 text to print
    $this_error_markup = '<strong>Error 404</strong><br />Page Not Found<br />invalid action: '.$this_page_action.'<br />';
    // Require the admin home file
    require(MMRPG_CONFIG_ROOTDIR.'admin/pages/home.php');
}

// Unset the database variable
unset($db);
*/




// Define the root location of all admin scripts
$adminPagesRoot = MMRPG_CONFIG_ROOTDIR . 'admin/pages/';

// Define an array for mapping URL actions with script filenames
$actionMap = [
    'home' => 'home.php',
    'exit' => null,
    'edit-users' => 'edit-users.php',
    'edit-messages' => 'edit-threads_private.php',
    'edit-message-replies' => 'edit-posts_private.php',
    'edit-threads' => 'edit-threads_public.php',
    'edit-thread-comments' => 'edit-posts_public.php',
    'edit-players' => 'edit-players.php',
    'edit-fields' => 'edit-fields.php',
    'edit-items' => 'edit-items.php',
    'edit-skills' => 'edit-skills.php',
    'edit-robot-masters' => 'edit-robots_masters.php',
    'edit-support-mechas' => 'edit-robots_mechas.php',
    'edit-fortress-bosses' => 'edit-robots_bosses.php',
    'edit-master-abilities' => 'edit-abilities_master.php',
    'edit-robot-master-abilities' => 'edit-abilities_master.php',
    'edit-mecha-abilities' => 'edit-abilities_mecha.php',
    'edit-support-mecha-abilities' => 'edit-abilities_mecha.php',
    'edit-boss-abilities' => 'edit-abilities_boss.php',
    'edit-fortress-boss-abilities' => 'edit-abilities_boss.php',
    'edit-event-challenges' => 'edit-challenges_event.php',
    'edit-user-challenges' => 'edit-challenges_user.php',
    'edit-stars' => 'edit-stars.php',
    'edit-pages' => 'edit-pages.php',
    'edit-music' => 'edit-music.php',
    'watch-error-log' => 'error-log.php',
    'view-sprites' => 'view-sprites.php',
    'generate-prompts' => 'generate-prompts.php',
    'purge-bogus-users' => 'purge.php',
    'delete-cached-files' => 'delete-cache.php',
    'clear-active-sessions' => 'clear-sessions.php',
    'refresh-leaderboard' => 'update.php'
    ];

// Include the relevant admin page based on the action
if (!MMRPG_CONFIG_ADMIN_MODE) {
    require($adminPagesRoot . 'login.php');
} elseif ($this_page_action == 'exit') {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_username_display']);
    redirect_form_action('admin/');
} elseif (array_key_exists($this_page_action, $actionMap)) {
    $file = $actionMap[$this_page_action];
    if ($this_page_action == 'edit-music') {
        if (defined('MMRPG_CONFIG_CDN_ROOTDIR') && file_exists(MMRPG_CONFIG_CDN_ROOTDIR)) {
            require($adminPagesRoot . $file);
        } else {
            $this_error_markup = '<strong>Error 404</strong><br />MMRPG_CONFIG_CDN_ROOTDIR NOT FOUND<br />';
            require($adminPagesRoot . 'home.php');
        }
    } elseif ($this_page_action == 'refresh-leaderboard') {
        $_REQUEST['date'] = MMRPG_CONFIG_CACHE_DATE;
        $_REQUEST['patch'] = 'recalculate_all_battle_points';
        require($adminPagesRoot . $file);
    } else {
        require($adminPagesRoot . $file);
    }
} else {
    $this_error_markup = '<strong>Error 404</strong><br />Page Not Found<br />invalid action: '.$this_page_action.'<br />';
    require($adminPagesRoot . 'home.php');
}

// Unset the database variable
unset($db);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title><?= $this_page_tabtitle ?> | Mega Man RPG Prototype | Last Updated <?= mmrpg_print_cache_date() ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link rel="apple-touch-icon" sizes="72x72" href="images/assets/ipad-icon-2k19_72x72.png" />
<meta name="viewport" content="user-scalable=yes, width=device-width, initial-scale=1.0">
<link rel="shortcut icon" type="image/x-icon" href="images/assets/<?= mmrpg_get_favicon() ?>">
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />
<? if (!empty($admin_include_stylesheets)){ ?>
    <?= $admin_include_stylesheets ?>
<? } ?>
<? if (in_array('codemirror', $admin_include_common_styles)){ ?>
    <link rel="stylesheet" href=".libs/codemirror/lib/codemirror.css?<?= MMRPG_CONFIG_CACHE_DATE ?>">
    <link rel="stylesheet" href=".libs/codemirror/addon/dialog/dialog.css?<?= MMRPG_CONFIG_CACHE_DATE ?>">
<? } ?>
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/file.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<link type="text/css" href="admin/styles/admin.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="admin/styles/admin-responsive.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
</head>
<body id="mmrpg">
    <div id="admin">
        <h1 class="header">
            <i class="fas fa-cog env <?= MMRPG_CONFIG_SERVER_ENV ?>"></i>
            <strong><?= $this_page_title ?></strong>
            <i class="fas fa-cog env <?= MMRPG_CONFIG_SERVER_ENV ?>"></i>
        </h1>
        <div class="content">
            <? if (!empty($_SESSION['admin_username_display'])): ?>
                <div class="userinfo">
                    <strong class="welcome">Welcome, <?= $_SESSION['admin_username_display'] ?></strong>
                    <span class="pipe">|</span>
                    <a class="link" href="admin/exit/">Exit</a>
                </div>
            <? endif; ?>
            <?= $this_page_markup ?>
        </div>
    </div>
    <? if(false){ ?>
        <pre style="text-align: left; padding: 20px;">
        <? foreach ($_SESSION['GAME']['values']['battle_settings'] AS $player_token => $battle_settings){
            echo '<h1>'.$player_token.'</h1>'."\n";
            echo htmlentities(print_r($battle_settings), ENT_QUOTES, 'UTF-8', true);
        } ?>
        <?= htmlentities(print_r($_REQUEST), ENT_QUOTES, 'UTF-8', true) ?>
        </pre>
    <? } ?>
<script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
<? if (!empty($admin_include_javascript)){ ?>
    <?= $admin_include_javascript ?>
<? } ?>
<? if (in_array('codemirror', $admin_include_common_scripts)){ ?>
    <script type="text/javascript" src=".libs/codemirror/lib/codemirror.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/addon/edit/matchbrackets.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/addon/search/search.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/addon/search/searchcursor.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/addon/search/jump-to-line.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/addon/dialog/dialog.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/mode/htmlmixed/htmlmixed.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/mode/xml/xml.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/mode/javascript/javascript.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/mode/css/css.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/mode/clike/clike.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
    <script type="text/javascript" src=".libs/codemirror/mode/php/php.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
<? } ?>
<? if (in_array('sortable', $admin_include_common_scripts)){ ?>
    <script type="text/javascript" src=".libs/jquery-ui-sortable/jquery.sortable.min.js"></script>
<? } ?>
<script type="text/javascript" src="admin/scripts/admin.js?<?= MMRPG_CONFIG_CACHE_DATE ?>"></script>
<script type="text/javascript">
    thisRootURL = '<?= MMRPG_CONFIG_ROOTURL ?>';
</script>
<? if (!empty($admin_inline_javascript)){ ?>
    <?= $admin_inline_javascript ?>
<? } ?>
</body>
</html>