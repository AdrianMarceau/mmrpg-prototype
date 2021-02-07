<?

// Require the top file for all admin scripts
require_once('common/top.php');

// Make sure this is being run from a compatible build of the game
if (MMRPG_CONFIG_PULL_LIVE_DATA_FROM === false
    || MMRPG_CONFIG_PULL_LIVE_DATA_FROM === MMRPG_CONFIG_SERVER_ENV){
    exit_action('error|Live user data cannot be pulled to the '.MMRPG_CONFIG_SERVER_ENV.' build!');
}

// Collect the name of the current db and the live one we'll be pulling from
$this_db_name = MMRPG_CONFIG_DBNAME;
$live_db_name = MMRPG_CONFIG_IS_LIVE === true ? 'mmrpg_live' : 'mmrpg_local_prod';
//debug_echo('$this_db_name = '.$this_db_name);
//debug_echo('$live_db_name = '.$live_db_name);

// Define the names of the tables we'll be copying over verbatim one-by-one
$copy_db_tables = array(
    // user tables
    'mmrpg_users',
    'mmrpg_users_contributors',
    'mmrpg_users_permissions',
    'mmrpg_saves',
    'mmrpg_leaderboard',
    'mmrpg_sessions',
    'mmrpg_users_records_robots',
    'mmrpg_users_unlocked_items',
    'mmrpg_users_unlocked_abilities',
    'mmrpg_users_unlocked_stars',
    // community tables
    'mmrpg_threads',
    'mmrpg_posts',
    // game progress tables
    'mmrpg_battles',
    // challenge mission tables
    'mmrpg_challenges_leaderboard',
    'mmrpg_challenges_waveboard',
    'mmrpg_users_challenges',
    'mmrpg_users_challenges_leaderboard',
    // game records tables
    'mmrpg_records_abilities',
    'mmrpg_records_robots'
    );

// Loop through the above tables and import data from each of them
echo("User data from `{$live_db_name}` will be copied to `{$this_db_name}`...".PHP_EOL);
foreach ($copy_db_tables AS $key => $table_name){
    echo("Importing table data for `{$table_name}` ...");
    $truncate_query = "TRUNCATE TABLE `{$this_db_name}`.`{$table_name}`; ";
    $db->query($truncate_query);
    $insert_query = "INSERT INTO `{$this_db_name}`.`{$table_name}` SELECT * from `{$live_db_name}`.`{$table_name}`; ";
    $db->query($insert_query);
    echo(" done!".PHP_EOL);
}

// Fix the legacy user_id problem for the guest pseudo-account
$guest_id = MMRPG_SETTINGS_GUEST_ID;
$db->query("UPDATE `{$this_db_name}`.mmrpg_users SET user_id = {$guest_id} WHERE user_name_clean = 'guest';");

// Refresh the logged-in user's accountin case anything changed
$this_admin_id = intval($_SESSION['admin_id']);
$this_admin_data = $db->get_array("SELECT user_id, user_name, user_name_public, user_name_clean FROM mmrpg_users WHERE user_id = '{$this_admin_id}';");                // Save account credentials to the session
if (!empty($this_admin_data)){
    $_SESSION['admin_id'] = $this_admin_data['user_id'];
    $_SESSION['admin_username'] = $this_admin_data['user_name_clean'];
    $_SESSION['admin_username_display'] = !empty($this_admin_data['user_name_public']) ? $this_admin_data['user_name_public'] : $this_admin_data['user_name'];
}

// Print the success message with the returned output
exit_action('success|Live user data for MMRPG has been pulled into this build!');

?>