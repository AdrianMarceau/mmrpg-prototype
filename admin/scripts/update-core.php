<?

// Require the top file for all admin scripts
require_once('common/top.php');

// If this is the first run, we should queue up content updates and wait for a refresh
if (empty($_GET['complete']) || $_GET['complete'] !== 'true'){

    // Append this directory to the git update queue
    $file_token = "git-pull";
    $project_path = MMRPG_CONFIG_ROOTDIR;
    $project_path_clean = (MMRPG_CONFIG_IS_LIVE === true ? str_replace(MMRPG_CONFIG_ROOTDIR, '/', $project_path) : $project_path);
    echo('$ '.$file_token.' '.$project_path_clean.' '.PHP_EOL);
    queue_git_updates($file_token, $project_path);

    // If the request was made via a regular browser tab, print out javascript status checker
    if ($return_kind === 'html'){
        print_cron_status_checker('git-pull', true, true);
    }

    // Print the success message with the returned output
    exit_action('success|MMRPG Core Updates Have Been Queued');

}
// Otherwise, we can run any post-update functionality now that pulling is complete
elseif ($_GET['complete'] === 'true') {

    // Update the global cache timestamp to ensure things are refreshed
    $cache_date = date('Ymd');
    $cache_time = date('Hi');
    $db->update('mmrpg_config', array('config_value' => $cache_date), array('config_group' => 'global', 'config_name' => 'cache_date'));
    $db->update('mmrpg_config', array('config_value' => $cache_time), array('config_group' => 'global', 'config_name' => 'cache_time'));

    // We are not done so we can print the cache date and time
    echo('MMRPG is now on version '.$cache_date.'-'.$cache_time.PHP_EOL);

    // Print the success message with the returned output
    exit_action('success|MMRPG Game Core Has Been Updated');

}

?>