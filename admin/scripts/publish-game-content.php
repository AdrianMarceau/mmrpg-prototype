<?

// Require the top file for all admin scripts
require_once('common/top.php');

// Require the top file for all admin git scripts
$request_action = 'publish';
$allow_empty_kind = true;
$allow_empty_subkind = true;
require_once('common/git-top.php');
//debug_echo('publish-game-content'.PHP_EOL);

// Require the git parameters file so we know which pulls are allowed
require_once('common/git-params.php');

// Require the global content type index for reference
require_once(MMRPG_CONFIG_CONTENT_PATH.'index.php');

// If this is the first run, we should queue up content updates and wait for a refresh
if (empty($_GET['complete']) || $_GET['complete'] !== 'true'){

    // Loop through the content types one-by-one and queue pulling updates for each
    session_write_close();
    foreach ($content_types_index AS $content_key => $content_type_info){
        //error_log('$content_key = '.print_r($content_key, true).PHP_EOL.'$content_type_info = '.print_r($content_type_info, true));

        // Collect the content kind as we'll use it a lot
        $content_kind = $content_type_info['xtoken'];

        // If the request kind is not empty, skip if not matching
        if (!empty($request_kind) && $request_kind !== 'all' && $request_kind !== $content_kind){ continue; }

        // If this is not an allowed kind, skip now
        if (!in_array($content_kind, $allowed_kinds)){ continue; }

        // Append this content directory to the git update queue
        $file_token = "git-push";
        $project_path = MMRPG_CONFIG_CONTENT_PATH.$content_type_info['content_path'];
        $project_path_clean = (MMRPG_CONFIG_IS_LIVE === true ? str_replace(MMRPG_CONFIG_ROOTDIR, '/', $project_path) : $project_path);
        $committed_changes = cms_admin::git_get_committed_changes($project_path);
        //error_log('$committed_changes ='.print_r($committed_changes, true));
        if (!empty($committed_changes)){
            echo('$ '.$file_token.' '.$project_path_clean.' '.PHP_EOL);
            queue_git_updates($file_token, $project_path);
        }

    }

    // Define the message to display based on request
    if (!empty($request_kind) && $request_kind !== 'all'){ $whats_being_published = ucfirst($request_kind).' Content'; }
    else { $whats_being_published = 'Game Content'; }

    // If the request was made via a regular browser tab, print out success but with a javascript status checker
    if ($return_kind === 'html'){
        print_cron_status_checker('git-push', true, true);
        exit_action('success|MMRPG '.$whats_being_published.' Is Publishing...');
    }

    // Otherwise, we should just print the success message and exit
    exit_action('pending|MMRPG '.$whats_being_published.' Is Publishing...');

}
// Otherwise, we can run any post-update functionality now that pulling is complete
elseif ($_GET['complete'] === 'true') {

    // If the request was made via a regular browser tab, print out javascript status checker
    if ($return_kind === 'html'){
        echo('You can close this window now. :)');
    }

    // Print the success message with the returned output
    exit_action('success|MMRPG Game Content Has Been Published');

}

?>