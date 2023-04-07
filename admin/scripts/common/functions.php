<?

// Start the output buffer to ensure stuff is printed in the right order
if (!ob_get_status()){ ob_start(); }

// Define a function for echoing debug info with a quick toggle var
$is_debug = true;
function debug_echo($echo){
    global $is_debug;
    if ($is_debug){ echo($echo.PHP_EOL); }
}

// Define a function for exiting the script with status first, debug last
function exit_action($status_line, $output = '', $data = array()){
    global $return_kind;
    $data = array();
    if (empty($data) && is_array($output)){ $data = $output; }
    if (!is_string($output)){ $output = ''; }
    if (!empty($output)){ $output .= PHP_EOL; }
    $output .= trim(ob_get_clean());
    if ($return_kind === 'html'){
        list($status_name, $status_text) = explode('|', $status_line);
        $status_colour = $status_name === 'success' ? 'green' : 'red';
        echo('<pre>'.PHP_EOL);
        echo('<strong style="color: '.$status_colour.';">'.$status_text.'</strong>'.PHP_EOL);
        echo(!empty($output) ? str_repeat('-', 50).PHP_EOL.$output : '');
        echo('</pre>'.PHP_EOL);
    } elseif ($return_kind === 'json'){
        $status_frags = explode('|', $status_line);
        list($status_name, $status_text) = explode('|', $status_line);
        header('Content-Type: application/json');
        echo(json_encode(array(
            'status' => !empty($status_frags[0]) ? $status_frags[0] : 'undefined',
            'message' => !empty($status_frags[1]) ? $status_frags[1] : '',
            'output' => $output,
            'data' => $data
            )));
    } else {
        echo(trim($status_line).PHP_EOL);
        echo(!empty($output) ? str_repeat('-', 50).PHP_EOL.$output : '');
    }
    exit();
}

// Define a function for appending a project directory to the git update queue
function queue_git_updates($file_token, $project_path){
    // Set the path to the list file
    $list_file = ".cache/admin/cron_{$file_token}-pending.list";
    $list_file_dir = MMRPG_CONFIG_ROOTDIR.$list_file;
    // Check if the list file exists
    if (!file_exists($list_file_dir)) {
        // Create the file and add the entry
        file_put_contents($list_file_dir, $project_path . PHP_EOL);
    } else {
        // Read the content of the list file
        $content = file_get_contents($list_file_dir);
        // Check if the entry already exists in the file
        $entries = explode(PHP_EOL, $content);
        if (!in_array($project_path, $entries)) {
            // Append the entry to the file
            file_put_contents($list_file_dir, $project_path . PHP_EOL, FILE_APPEND);
        }
    }
}

// Define a function for printing the cron status checker response
function print_cron_status_checker($cron_kind, $print = true, $reload = false){
    ob_start();

    $cron_name = ucwords(str_replace('-', ' ', $cron_kind));
    $cron_path = MMRPG_CONFIG_ROOTDIR.'admin/scripts/cron_'.$cron_kind.'-wrapper.sh';
    $jquery_path = MMRPG_CONFIG_ROOTURL.'.libs/jquery/jquery-1.6.1.min.js';
    $checker_path = MMRPG_CONFIG_ROOTURL.'admin/scripts/cron_check-git-status.php';

    echo('<script src="'.$jquery_path.'"></script>'.PHP_EOL);
    echo('<div id="cron-status-div">'.$cron_name.' Status: Pending</div>'.PHP_EOL);

    $displayed_cron_text = '';
    $displayed_cron_path = MMRPG_CONFIG_IS_LIVE !== true ? $cron_path : str_replace(MMRPG_CONFIG_ROOTDIR, '', $cron_path);
    $displayed_cron_cmd = $displayed_cron_path.' '.$cron_kind.' '.MMRPG_CONFIG_SERVER_USER;
    if (MMRPG_CONFIG_IS_LIVE !== true){
        $displayed_cron_text = 'On localhost, please run the following command:';
    } else {
        $displayed_cron_text = 'Waiting for the cron job to run the following command:';
    }
    echo('<div class="cron-help">'.$displayed_cron_text.'</div>'.PHP_EOL);
    echo('<div class="cron-help">$  <span>'.$displayed_cron_cmd.'</span></div>'.PHP_EOL);

    ?>
    <script>
        // Define a function for checking the cron job status
        function checkCronStatus() {
            $.ajax({
                data: {kind: '<?= $cron_kind ?>'},
                url: '<?= $checker_path ?>',
                success: function(response) {
                    if (response === "completed") {
                        $("#cron-status-div").text("<?= $cron_name ?> Status: Completed");
                        $(".cron-help").remove();
                        clearTimeout(checkCronStatus);
                        <? if ($reload === true){ ?>
                        setTimeout(function(){
                            var newHref = window.location.href;
                            if (newHref.indexOf('&complete=true') === -1){ newHref += '&complete=true'; }
                            window.location = newHref;
                            }, 2000);
                        <? } ?>
                    } else {
                        $("#cron-status-div").append('.');
                        setTimeout(checkCronStatus, 5000); // Check the status again after 5 seconds
                    }
                },
                error: function() {
                    $("#cron-status-div").append('!');
                    setTimeout(checkCronStatus, 5000); // Check the status again after 5 seconds in case of an error
                }
            });
        }
        // Start checking the cron job status
        $(document).ready(function(){
            $("#cron-status-div").append('.');
            checkCronStatus();
        });
    </script>
    <?

    $output = ob_get_clean();
    if ($print){ echo($output); }
    else { return $output; }

}

?>