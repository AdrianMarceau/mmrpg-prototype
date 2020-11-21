<?

// Update the title for this error log page
$this_page_tabtitle = 'Error Log | '.$this_page_tabtitle;

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin/">Admin Panel</a> &raquo;
<a href="admin/watch-error-log/">Watch Error Log</a> &raquo;
</div>
<?
$this_page_markup .= ob_get_clean();

// Start a new output buffer to collect content for the page
ob_start();

//echo('<pre>$_GET = '.print_r($_GET, true).'</pre>'.PHP_EOL);

// Define a function to use when formatting a given error log line
function log_string_to_list_item($line_number, $log_string){
    static $trace_pattern_one = '/^(\[[^\[\]]+\])\s+(PHP\s+Stack\sTrace:\s?)$/i';
    static $trace_pattern_two = '/^(\[[^\[\]]+\])\s+(PHP\s+[0-9]+\.\s+)/i';
    $is_trace_string = false;
    if (preg_match($trace_pattern_one, $log_string)){
        $is_trace_string = true;
        $log_string = preg_replace($trace_pattern_one, '$2', $log_string);
    } elseif (preg_match($trace_pattern_two, $log_string)){
        $is_trace_string = true;
        $log_string = preg_replace($trace_pattern_two, '$2', $log_string);
    }
    $item_markup = '<li class="line'.($is_trace_string ? ' trace' : '').'" data-line="'.$line_number.'">'.$log_string.'</li>';
    return $item_markup;
}

// Define the path for the error log we'll be collecting lines from
$error_log_dir = rtrim(dirname(MMRPG_CONFIG_ROOTDIR), '/').'/_logs/';
$error_log_name = 'error.log';
$error_log_path = $error_log_dir.$error_log_name;
//echo('<pre>$error_log_dir = '.print_r($error_log_dir, true).'</pre>'.PHP_EOL);
//echo('<pre>$error_log_name = '.print_r($error_log_name, true).'</pre>'.PHP_EOL);
//echo('<pre>$error_log_path = '.print_r($error_log_path, true).'</pre>'.PHP_EOL);

// Regardless of what's next, count how many lines there are in the file
$error_log_size = cms_admin::count_lines_in_file($error_log_path);
//echo('<pre>$error_log_size = '.print_r($error_log_size, true).'</pre>'.PHP_EOL);

// If a "get-lines" request was sent, return any lines since the last shown on page
$this_page_subaction = !empty($_REQUEST['subaction']) ? trim($_REQUEST['subaction']) : false;
if ($this_page_subaction === 'get-lines'){
    $since_last_line = !empty($_REQUEST['since']) && is_numeric($_REQUEST['since']) ? intval($_REQUEST['since']) : 0;
    if (!empty($since_last_line) && $since_last_line <= $error_log_size){
        // Update the content type to simple json
        ob_clean();
        header('Content-type: text/json; charset=utf-8');
        // If there were new lines since the last update, return them, else return empty array
        $return_array = array();
        if ($since_last_line < $error_log_size){
            $return_slice = $error_log_size - $since_last_line;
            $error_log_markup = cms_admin::get_last_lines_of_file($error_log_path, $return_slice);
            $error_log_lines = explode(PHP_EOL, $error_log_markup);
            $line_number = $error_log_size - count($error_log_lines);
            foreach ($error_log_lines AS $key => $log_string){
                $line_number++;
                $return_array[] = log_string_to_list_item($line_number, $log_string);
            }
        }
        // Either way exit with just the above
        echo(json_encode($return_array));
        exit();
    }
}

// Collect the markup for the error log and print it out
$error_log_slice = 50;
$error_log_markup = cms_admin::get_last_lines_of_file($error_log_path, $error_log_slice);
//echo('<pre>$error_log_slice = '.print_r($error_log_slice, true).'</pre>'.PHP_EOL);
//echo('<pre>$error_log_markup = '.print_r($error_log_markup, true).'</pre>'.PHP_EOL);

// Print out the menu header so we know where we are
?>
<div class="adminform error-log">

    <div class="editor">

        <h3 class="header type_span type_empty">
            <span class="title">Watch Error Log</span>
            <span class="loading"><img src="admin/images/ajax-loader_on-black.gif" alt="Loading" /></span>
        </h3>

        <div class="field fullsize">

            <?
            // Break apart the error log lines and print them in a list for scrolling
            $error_log_lines = explode(PHP_EOL, $error_log_markup);
            ob_start();
            $last_line_number = 0;
            if (!empty($error_log_lines)){
                $line_number = $error_log_size - count($error_log_lines);
                foreach ($error_log_lines AS $key => $log_string){
                    $line_number++;
                    echo(log_string_to_list_item($line_number, $log_string).PHP_EOL);
                    $last_line_number = $line_number;
                }
            }
            $error_log_lines_markup = ob_get_clean();
            echo('<ul class="log-list" '.
                'data-size-total="'.$error_log_size.'" '.
                'data-size-visible="'.$error_log_slice.'" '.
                'data-last-line="'.$last_line_number.'" '.
                '>'.PHP_EOL);
                echo($error_log_lines_markup.PHP_EOL);
            echo('</ul>'.PHP_EOL);

            ?>

        </div>

        <div class="buttons">
            <a class="button pause hidden" title="Pause"><span>Pause</span><i class="fas fa-pause"></i></a>
            <a class="button play" title="Play"><span>Watch</span><i class="fas fa-play"></i></a>
            <a class="button clear" title="Clear"><span>Clear</span><i class="fas fa-eraser"></i></a>
        </div>

    </div>

</div>
<?

// Collect generated markup and add it to the page string
$this_page_markup .= ob_get_clean();

?>