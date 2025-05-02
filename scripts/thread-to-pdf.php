<?php

// THREAD TO PDF CONVERSION SCRIPT
// This script assumes and uses a page-width of 1040px or approx 780pt for the document

// Require the application top file
define('MMRPG_EXCLUDE_GAME_LOGIC', true);
define('MMRPG_EXCLUDE_COMMUNITY_LOGIC', true);
require_once('../top.php');

// Start the output buffer to collect debug info
ob_start();

// Print out some debug styles to make writing this script a bit easier
echo('<pre style="color: magenta;">First test on line '.__LINE__.'!</pre>'.PHP_EOL);
echo('<style> pre { max-width: 100%; } pre:not(.array) { white-space: normal; } pre.array > data { display: block; max-height: 200px; overflow: auto; } </style>'.PHP_EOL);

/* -- COLLECT THREAD INFO -- */

// Define some helper functions for the export process
function exportKeyGen($src, $dst, $mod){
    return substr(md5(implode('##'.MMRPG_SETTINGS_EXPORTAUTH_SALT.'##', array($src, $dst, $mod))), 6, 6);
}

// Collect the thread ID from the URL path if available
echo('<pre style="color: magenta;">Testing on line '.__LINE__.'!</pre>'.PHP_EOL);
$thread_id = 0;
$thread_token = '';
$thread_published = 0;
$thread_updated = 0;
$category_id = 0;
$category_token = '';
$base_thread_url = '';
if (!empty($_GET['thread'])
    && is_numeric($_GET['thread'])
    && $_GET['thread'] > 0){
    $thread_id = intval($_GET['thread']);
    $thread_info = cms_thread::get_thread_info($thread_id, true);
    echo('<pre>$thread_id = '.print_r($thread_id, true).'</pre>'.PHP_EOL);
    echo('<pre class="array">$thread_info = <data>'.print_r($thread_info, true).'</data></pre>'.PHP_EOL);
    if (empty($thread_info)){ die("Error: Thread not found for ID &quot;{$thread_id}&quot;."); }
    $thread_token = $thread_info['thread_token'];
    $thread_published = !empty($thread_info['thread_date']) ? $thread_info['thread_date'] : 0;
    $thread_updated = !empty($thread_info['thread_mod_date']) ? $thread_info['thread_mod_date'] : $thread_info['thread_date'];
    $category_id = $thread_info['category_id'];
    $category_token = $thread_info['category_token'];
    if (empty($thread_token)){ die("Error: Thread token not found for ID &quot;{$thread_id}&quot;."); }
    if (empty($thread_info['thread_published'])){ die("Error: Thread not published for ID &quot;{$thread_id}&quot;."); }
    if (empty($category_id)){ die("Error: Thread category not found for ID &quot;{$thread_id}&quot;."); }
    if (empty($category_token)){ die("Error: Thread category not found for ID &quot;{$thread_id}&quot;."); }
    if ($thread_info['category_token'] == 'personal'){ die("Error: Thread category not allowed for ID &quot;{$thread_id}&quot;."); }
}
if (empty($thread_id)){ die("Error: Thread ID not found in URL path."); }
elseif (empty($thread_token)){ die("Error: Thread token not found for ID &quot;{$thread_id}&quot;."); }
else { echo('<pre style="color: lime;">Thread found w/ ID &quot;'.$thread_id.'&quot; and token &quot;'.$thread_token.'&quot;!</pre>'.PHP_EOL); }
$base_thread_url = 'community/'.$category_token.'/'.$thread_id.'/'.$thread_token.'/';
echo('<pre>$base_thread_url = '.print_r($base_thread_url, true).'</pre>'.PHP_EOL);
if (empty($base_thread_url)){ die("Error: Base thread URL could not be generated for thread ID &quot;{$thread_id}&quot;."); }

// Now that we have that information, let's build a manifest of all the thread/comment URLs we'll need
$src_base = $base_thread_url.'view=print&limit=1';
$dst_base = 'threads/'.$category_token.'/'.$thread_id; //.'/'.str_pad($thread_id, 6, '0', STR_PAD_LEFT); //.'_'.$thread_token;
$post_ids = !empty($thread_info['thread_post_ids']) ? explode(',', $thread_info['thread_post_ids']) : array();
$post_ids_count = count($post_ids);
$export_manifest = array();
$id_pad = function($id){ return str_pad($id, 6, '0', STR_PAD_LEFT); };
$mk_dst = function($id) use ($dst_base, $id_pad, $thread_id){ return $dst_base.'/'.$id_pad($thread_id).'_'.$id_pad($id); };
$export_manifest[] = array('src' => $src_base.'&offset=-1', 'dst' => $mk_dst(0), 'name' => 'Thread-'.$thread_id, 'mod' => $thread_info['thread_date']);
foreach ($post_ids AS $key => $id){ $export_manifest[] = array('src' => $src_base.'&offset=0&range='.$id, 'dst' => $mk_dst($id), 'name' => 'Comment-'.$id.'', 'mod' => $thread_info['thread_date']); }
echo('<pre>$src_base = '.print_r($src_base, true).'</pre>'.PHP_EOL);
echo('<pre>$dst_base = '.print_r($dst_base, true).'</pre>'.PHP_EOL);
echo('<pre class="array">$post_ids = <data>'.print_r($post_ids, true).'</data></pre>'.PHP_EOL);
echo('<pre class="array">$export_manifest = <data>'.print_r($export_manifest, true).'</data></pre>'.PHP_EOL);

echo('<pre style="color: magenta;">Final test on line '.__LINE__.'!</pre>'.PHP_EOL);

// Collect output from the buffer and clear it
$debug_output = ob_get_clean();

// Predefine variables to hold the HTML content for the page
$html_content_head = '';
$html_content_body = '';
$html_content_styles = '';
$html_content_scripts = '';

// Generate the HTML <head> part of the page
ob_start();
?>
    <meta charset="utf-8">
    <base href="<?= MMRPG_CONFIG_ROOTURL ?>">
    <title>Thread to PDF | Community Export Tool | Mega Man RPG Prototype</title>
    <style> html, body { font-family: Arial, sans-serif; text-align: center; font-size: 13px; line-height: 1.6; color: #efefef; background-color: #262626; margin: 0; padding: 0; } body { padding: 20px; } </style>
    <style> a { color: #007cf4; text-decoration: underline; } a:hover { color: #3cc0fc; text-decoration: none; } i, b, u { font-style: normal; font-weight: normal; text-decoration: none; } h1 { font-size: 200%; margin: 0 auto 20px; } h2 { font-size: 150%; margin: 0 auto 20px; } h1 + h2 { margin-top: -20px; } </style>
    <meta name="darkreader-lock" content="already-dark-mode" />
    <meta name="robots" content="noindex,nofollow" />
<?
$html_content_head .= ob_get_clean();

// Generate the HTML <body> part of the page
ob_start();
?>
    <div id="intro-text">
        <h1>Thread to PDF Converter</h1>
        <h2>MMRPG Community Thread Export Tool</h2>
        <p>Using this script will export the requested community thread and any comments into a new PDF file for download.</p>
        <ul>
            <li class="category"><strong>Category</strong>: <?= $category_token ?> [<?= $category_id ?>]</li>
            <li class="id"><strong>ID</strong>: <?= $thread_id ?></li>
            <li class="token"><strong>Token</strong>: <?= $thread_token ?></li>
            <li class="name"><strong>Name</strong>: &quot;<?= $thread_info['thread_name'] ?>&quot;</li>
            <li class="published"><strong>Published</strong>: <?= date('Y-m-d H:i:s', $thread_info['thread_date']) ?></li>
            <li class="updated"><strong>Updated</strong>: <?= date('Y-m-d H:i:s', $thread_updated) ?></li>
            <li class="comments"><strong>Comments</strong>: <?= $post_ids_count ?></li>
            <li class="url"><strong>URL</strong>: <a href="<?= $base_thread_url ?>" target="_blank"><?= $base_thread_url ?></a></li>
        </ul>
        <p>Click the "Generate" button to get started and then use the "Download" button to grab your file once the export is complete.</p>
        <p>Please be patient while the script generates each section of the document and then assembles them together. Thank you.</p>
    </div>
    <?
    $manifest_total = !empty($export_manifest) ? count($export_manifest) : 0;
    $manifest_base_dir = $dst_base.'/';
    $manifest_file_path = $manifest_base_dir.'manifest.json';
    $manifest_file_list = array();
    $src_base_url = MMRPG_CONFIG_ROOTURL;
    $dst_base_dir = MMRPG_CONFIG_ROOTDIR.'.cache/';
    $dst_base_url = MMRPG_CONFIG_ROOTURL.'.cache/';
    //$final_export_path = $dst_base_dir.$dst_base.$id_pad($thread_id).'.pdf';
    $final_export_path = $dst_base_dir.$dst_base.'/export.pdf';
    $final_export_href = $dst_base_url.$dst_base.'/export.pdf';
    //error_log('$final_export_path = '.$final_export_path);
    //error_log('$final_export_href = '.$final_export_href);
    $final_export_exists = file_exists($final_export_path);
    $final_export_status = $final_export_exists ? 'complete' : '';
    ?>
    <div id="thread-to-pdf"
        data-thread-id="<?= $thread_id ?>"
        data-thread-token="<?= $thread_token ?>"
        data-category-id="<?= $category_id ?>"
        data-category-token="<?= $category_token ?>"
        data-src-base="<?= $src_base ?>"
        data-dst-base="<?= $dst_base ?>"
        data-export-items="<?= $manifest_total ?>"
        data-export-path="<?= $final_export_path ?>"
        data-status="<?= $final_export_status ?>">
        <div id="buttons">
            <? if ($final_export_exists){ ?>
                <button id="start" class="button" disabled>Generate PDF</button>
                <button id="stop" class="button" disabled>Stop Generating</button>
                <button id="download" class="button" href="<?= $final_export_href ?>">Download PDF</button>
            <? } else { ?>
                <button id="start" class="button">Generate PDF</button>
                <button id="stop" class="button" disabled>Stop Generating</button>
                <button id="download" class="button" disabled>Download PDF</button>
            <? } ?>
            <i class="status"></i>
        </div>
        <div id="manifest">
        <?
        if (!empty($export_manifest)){
            $list_items = array();
            $list_items['pending'] = array();
            $list_items['complete'] = array();
            $rel_src_base_url = str_replace(MMRPG_CONFIG_ROOTURL, '', $src_base_url);
            $rel_dst_base_url = str_replace(MMRPG_CONFIG_ROOTURL, '', $dst_base_url);
            foreach ($export_manifest AS $key => $item){
                if (empty($item['src']) || empty($item['dst'])){ continue; }
                $item_num = ($key + 1); //$item_num = preg_replace('/^([0]+)?/', '<b>$1</b>', str_pad(($key + 1), 3, '0', STR_PAD_LEFT));
                $name = (!empty($item['name']) ? $item['name'] : 'Item '.($key + 1));
                $src_path = (!empty($item['src']) ? $item['src'] : '');
                $dst_path = (!empty($item['dst']) ? $item['dst'] : '');
                $mod_time = (!empty($item['mod']) ? $item['mod'] : time());
                $auth_key = exportKeyGen($src_path, $dst_path, $mod_time);
                $src_href = $rel_src_base_url.$src_path;
                $dst_href = $rel_dst_base_url.$dst_path;
                $dst_img_type = 'png';
                $dst_img_path = $dst_path.'.'.$dst_img_type;
                $dst_img_href = $rel_dst_base_url.$dst_img_path;
                $dst_img_exists = file_exists($dst_base_dir.$dst_img_path);
                $dst_doc_type = 'pdf';
                $dst_doc_path = $dst_path.'.'.$dst_doc_type;
                $dst_doc_href = $rel_dst_base_url.$dst_doc_path;
                $dst_doc_exists = file_exists($dst_base_dir.$dst_doc_path);
                $manifest_file_list[] = basename($dst_path);
                //$item_status = '';
                //if ($dst_img_exists && $dst_doc_exists){ $item_status = 'complete'; }
                //elseif ($dst_img_exists || $dst_doc_exists){ $item_status = 'pending'; }
                $item_status = $dst_img_exists && $dst_doc_exists ? 'complete' : 'pending';
                ob_start();
                echo('<li class="item"'.
                    ' data-src="'.$src_path.'"'.
                    ' data-dst="'.$dst_path.'"'.
                    ' data-mod="'.$mod_time.'"'.
                    ' data-auth="'.$auth_key.'"'.
                    ' data-status="'.$item_status.'"'.
                    '>');
                    echo('<i class="bullet">&raquo;</i>');
                    echo('<strong class="num">'.$item_num.' <b>/ '.$manifest_total.'</b></strong>');
                    echo('<a class="name" href="'.$src_href.'" target="_blank">'.$name.'</a>');
                    if ($dst_img_exists){ echo('<a class="file img" href="'.$dst_img_href.'" target="_blank">'.strtoupper($dst_img_type).'</a>'); }
                    else { echo('<a class="file img" target="_blank"></a>'); }
                    if ($dst_doc_exists){ echo('<a class="file doc" href="'.$dst_doc_href.'" target="_blank">'.strtoupper($dst_doc_type).'</a>'); }
                    else { echo('<a class="file doc" target="_blank"></a>'); }
                    echo('<i class="status"></i>');
                echo('</li>'.PHP_EOL);
                $list_items[$item_status][] = ob_get_clean();
            }
            foreach ($list_items AS $status => $items){
                $num_items = count($items);
                $ratio_vs_total = ($num_items / $manifest_total);
                $percent_of_total = round($ratio_vs_total * 100, 0);
                echo('<ol class="list" data-status="'.$status.'">'.PHP_EOL);
                    echo('<li class="title">');
                        echo('<strong class="name">'.ucfirst($status).'</strong>');
                        echo('<label class="count">'.$num_items.' / '.$manifest_total.'</label>');
                        echo('<progress class="progress" value="'.$num_items.'" max="'.$manifest_total.'">'.$percent_of_total.'%</progress>');
                    echo('</li>'.PHP_EOL);
                    if (!empty($items)){
                        if ($status == 'complete'){ $items = array_reverse($items); }
                        foreach ($items AS $item){ echo($item); }
                    } else {
                        echo('<li class="item empty">&nbsp;</li>'.PHP_EOL);
                    }
                echo('</ol>'.PHP_EOL);
            }
        }
        //error_log('$manifest_file_path: '.print_r($manifest_file_path, true));
        //error_log('$manifest_file_list: '.print_r($manifest_file_list, true));
        $manifest_file_json = array();
        $manifest_file_json['title'] = $thread_info['thread_name'];
        $manifest_file_json['author'] = !empty($thread_info['author_name_public']) ? $thread_info['author_name_public'] : $thread_info['author_name'];
        $manifest_file_json['published'] = $thread_published;
        $manifest_file_json['updated'] = $thread_updated;
        $manifest_file_json['items'] = $manifest_file_list;
        if (!is_dir($manifest_base_dir)){ recurseMakeDir($manifest_base_dir, $dst_base_dir); }
        if (file_exists($dst_base_dir.$manifest_file_path)){ unlink($dst_base_dir.$manifest_file_path); }
        $h = fopen($dst_base_dir.$manifest_file_path, 'w');
        fwrite($h, json_encode($manifest_file_json, JSON_PRETTY_PRINT));
        fclose($h);
        ?>
        </div>
    </div>
    <div id="debug-output">
        <strong class="title">Debug Output</strong>
        <div class="output hide-on-blur"><?= $debug_output ?></div>
    </div>
<?
$html_content_body .= ob_get_clean();

// Generate the HTML <scripts> for the page
ob_start();
?>
    <script src=".libs/jquery/jquery-1.6.1.min.js"></script>
    <script type="text/javascript">

        // -- Thread to PDF Conversion Script -- //
        (function(){

            // Define the manifest in json so we can access it later
            let exportManifest = <?= json_encode($export_manifest) ?>;
            let manifestTotal = <?= json_encode($manifest_total) ?>;

            // Define some paths for scripts we'll use later
            let exportScriptPath = 'scripts/export-to-pdf.php';
            let mergeScriptPath = 'scripts/merge-pdfs.php';
            let baseExportHref = '<?= $dst_base_url ?>';
            let baseExportPath = '<?= $dst_base ?>';
            let threadID = '<?= $thread_id ?>';
            let threadToken = '<?= $thread_token ?>';
            let categoryID = '<?= $category_id ?>';
            let categoryToken = '<?= $category_token ?>';

            // Pull in references to the main elements we'll be working with
            let $ = jQuery;
            let $context,
                $manifest,
                $pending,
                $pendingTitle,
                $complete,
                $completeTitle,
                $buttons,
                $start,
                $stop,
                $download
                ;

            // Wait until the document is ready before indexing data or delegating events
            $(document).ready(function(){

                // This is mostly for reference, but we can use it to track progress
                //console.log('Export Manifest:', exportManifest);

                // Pull in references to the main elements we'll be working with
                $context = $('#thread-to-pdf');
                $manifest = $('#manifest', $context);
                $pending = $('.list[data-status="pending"]', $manifest);
                $pendingTitle = $pending.find('.title');
                $complete = $('.list[data-status="complete"]', $manifest);
                $completeTitle = $complete.find('.title');
                $buttons = $('#buttons', $context);
                $start = $('#start', $context);
                $stop = $('#stop', $context);
                $download = $('#download', $context);

                // Attach the click event to the start & stop buttons
                let allowNextExport = false;
                let startExport = function(){
                    $stop.prop('disabled', false);
                    allowNextExport = true;
                    startNextExport();
                    };
                let stopExport = function(){
                    $stop.prop('disabled', true);
                    allowNextExport = false;
                    };
                $start.bind('click', function(event){
                    event.preventDefault();
                    //console.log('Start button clicked!');
                    startExport();
                    });
                $stop.bind('click', function(event){
                    event.preventDefault();
                    //console.log('Stop button clicked!');
                    stopExport();
                    });

                // Define a quick function for encoding a string for a URL parameter
                let urlEncode = function(str){ return encodeURIComponent(str).replace(/%20/g, '+'); };

                // Define a function to start the next export in the manifest
                let startNextExport = function(){

                    // Loop through each item in the manifest and update the status
                    if (!allowNextExport){ stopExport(); return false; }
                    let $itemsToDo = $pending.find('.item[data-status="pending"]');
                    let mergeExists = false;
                    let readyToMerge = $itemsToDo.length == 0 ? true : false;
                    //console.log('Items to do:', $itemsToDo.length);

                    // If there are still items to do, let's do that, else stop the auto
                    if ($itemsToDo.length){
                        //console.log('Items to do:', $itemsToDo.length);
                        $itemsToDo.each(function(index, element){
                            //console.log('Searching for next export item...');
                            let $item = $(element);
                            let itemStatus = $item.attr('data-status');
                            //console.log('Item:', $item, 'Status:', itemStatus);
                            if (itemStatus !== 'pending'){ return true; } // continue the loop
                            else { exportItem($item); } // start the export
                            return false; // stop the loop
                            });
                        } else {
                        stopExport();
                        }
                    // If ready to merge them, let's attempt to merge them
                    if (!mergeExists && readyToMerge){
                        //console.log('Ready to merge!');
                        mergeItems();
                        return false;
                        }
                    // Otherwise export is complete and we can update the button
                    else if (mergeExists){
                        //console.log('Download link should be enabled by now...');
                        return false;
                        }
                    };

                // Define a trigger function for the item export functionality
                let exportItem = function($item){
                    //console.log('Exporting item:', $item);
                    if (!$item.length){ return false; }
                    let itemStatus = $item.attr('data-status');
                    let itemNum = $item.find('.num').text();
                    let itemName = $item.find('.name').text();
                    let srcPath = $item.attr('data-src');
                    let dstPath = $item.attr('data-dst');
                    let modTime = $item.attr('data-mod');
                    let authCode = $item.attr('data-auth');
                    let scriptUrl = exportScriptPath + '?' + [
                        'src='+urlEncode(srcPath),
                        'dst='+urlEncode(dstPath),
                        'mod='+urlEncode(modTime),
                        'auth='+urlEncode(authCode),
                        'debug=false',
                        'return=json'
                        ].join('&');
                    //console.log('export scriptUrl:', scriptUrl);
                    let $imgFile = $item.find('.file.img');
                    let $docFile = $item.find('.file.doc');
                    let $status = $item.find('.status');
                    //console.log('Item:', itemNum, itemName, srcPath, dstPath, scriptUrl, itemStatus, '\n' + 'Status:', $status, $imgFile, $docFile);
                    if (!srcPath || !dstPath || !scriptUrl){ return false; }
                    $item.attr('data-status', 'loading');
                    //console.log('Start the export request for item '+itemNum+' ('+itemName+')...');
                    $.ajax({
                        url: scriptUrl,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response){
                            //console.log('Export success:', response);
                            if ($imgFile.length){ $imgFile.attr('href', baseExportHref+dstPath+'.png').html('PNG'); }
                            if ($docFile.length){ $docFile.attr('href', baseExportHref+dstPath+'.pdf').html('PDF'); }
                            $item.insertAfter($completeTitle);
                            $item.attr('data-status', 'complete');
                            $item.addClass('slide-in');
                            let numPending = $pending.find('.item').length;
                            $pendingTitle.find('.progress').attr('value', numPending);
                            $pendingTitle.find('.count').text(numPending + ' / ' + manifestTotal);
                            let numComplete = $complete.find('.item').length;
                            $completeTitle.find('.progress').attr('value', numComplete);
                            $completeTitle.find('.count').text(numComplete + ' / ' + manifestTotal);
                            setTimeout(function(){ $item.removeClass('slide-in'); }, 1000);
                            startNextExport();
                            },
                        error: function(response){
                            //console.log('Export error:', response);
                            let responseData = response.responseText ? JSON.parse(response.responseText) : null;
                            //console.log('Export error data:', responseData);
                            $item.attr('data-status', 'error');
                            let msg = 'An error occurred while exporting item '+itemNum+' ('+itemName+').';
                            if (responseData && responseData.message){ msg += '\n' + 'Error: ' + responseData.message; }
                            msg += '\n' + 'Would you like to continue to the next?';
                            if (confirm(msg)){ startNextExport(); }
                            else { stopExport(); }
                            }
                        });
                    };

                // Define a trigger function for the item merge functionality
                let mergeItems = function(){
                    //console.log('Merging items...');
                    let $itemsToMerge = $complete.find('.item[data-status="complete"]');
                    let itemsToMerge = [];
                    if ($itemsToMerge.length){
                        $itemsToMerge.each(function(index, element){ itemsToMerge.push($(element).find('.name').text()); });
                        //console.log('Items to merge:', itemsToMerge);
                        // add a new pseudo-item to the pending list so the user can see we're doing something
                        let $item = $('<li class="item slide-in" data-status="loading">'
                                + '<i class="bullet">&raquo;</i>'
                                + '<strong class="num">&hellip;</strong>'
                                + '<strong class="name">Merging ' + itemsToMerge.length + ' Items</strong>'
                                + '<a class="file img" target="_blank">&hellip;</a>'
                                + '<a class="file doc" target="_blank">&hellip;</a>'
                                + '<i class="status"></i>'
                            + '</li>'
                            );
                        $item.insertAfter($pendingTitle);
                        setTimeout(function(){ $item.removeClass('slide-in'); }, 1000);
                        // define the merge script URL given what we know and send an ajax request
                        // (we don't need to send the list, it has a manifest to work from, we only need to send the base dst string)
                        let showDebug = false;
                        let scriptUrl = mergeScriptPath + '?' + [
                            'path='+urlEncode(baseExportPath),
                            'debug='+(showDebug ? 'true' : 'false'),
                            'return=json'
                            ].join('&');
                        if (showDebug){
                            // debug debug debug
                            // open above in new tab/window
                            //console.log('merge scriptUrl:', scriptUrl);
                            window.open(scriptUrl, '_blank');
                            } else {
                            // do the ajax request and update the downloadButtonHref
                            // with the "document" href when it comes through in the data
                            $context.attr('data-status', 'pending');
                            $.ajax({
                                url: scriptUrl,
                                type: 'GET',
                                dataType: 'json',
                                success: function(response){
                                    //console.log('Merge success:', response);
                                    if (response && response.status == 'success'){
                                        let data = response.data || {};
                                        $context.attr('data-status', 'complete');
                                        $item.attr('data-status', 'complete');
                                        $item.remove();
                                        //$item.insertAfter($completeTitle);
                                        //if (data.image){ $item.find('.img').attr('href', data.image).html('PNG'); }
                                        //if (data.document){ $item.find('.doc').attr('href', data.document).html('PDF'); }
                                        //$item.addClass('slide-in');
                                        let numComplete = $complete.find('.item').length;
                                        $completeTitle.find('.progress').attr('value', numComplete);
                                        $completeTitle.find('.count').text(numComplete + ' / ' + manifestTotal);
                                        // update the download button href
                                        let downloadButtonHref = data.document;
                                        $download.attr('href', downloadButtonHref);
                                        //console.log('Download button href:', downloadButtonHref);
                                        $download.prop('disabled', false);
                                        } else {
                                        $item.attr('data-status', 'error');
                                        let msg = 'An error occurred while merging items.';
                                        if (response && response.message){ msg += '\n' + 'Error: ' + response.message; }
                                        msg += '\n' + 'Would you like to continue to the next?';
                                        if (confirm(msg)){ startNextExport(); }
                                        else { stopExport(); }
                                        }
                                    },
                                error: function(response){
                                    //console.log('Merge error:', response);
                                    let responseData = response.responseText ? JSON.parse(response.responseText) : null;
                                    //console.log('Merge error data:', responseData);
                                    $item.attr('data-status', 'error');
                                    let msg = 'An error occurred while merging items.';
                                    if (responseData && responseData.message){ msg += '\n' + 'Error: ' + responseData.message; }
                                    msg += '\n' + 'Would you like to continue to the next?';
                                    if (confirm(msg)){ startNextExport(); }
                                    else { stopExport(); }
                                    }
                                });
                            }
                        } else {
                        //console.log('No items to merge!');
                        $download.prop('disabled', false);
                        }
                    };

                // Attach the click event to the download button
                $download.bind('click', function(event){
                    event.preventDefault();
                    //console.log('Download button clicked!');
                    let downloadButtonHref = $(this).attr('href');
                    if (!downloadButtonHref){ return false; }
                    window.open(downloadButtonHref, '_blank');
                    });

                // Make sure we mark the context area as ready for animation purposes
                $context.addClass('ready');

               });

        })();

    </script>
<?
$html_content_scripts .= ob_get_clean();

// Generate the HTML <styles> for the page
ob_start();
?>
    <style type="text/css">

        /* General styles */

        #intro-text,
        #thread-to-pdf,
        #debug-output {
            display: block;
            box-sizing: border-box;
            width: 100%;
            max-width: 1024px;
            overflow: auto;
            border: 1px solid #464646;
            padding: 10px;
            margin: 0 auto 20px;
            border-radius: 6px;
            text-align: left;
            font-size: 13px;
            line-height: 1.6;
        }

        #intro-text:after,
        #intro-text ul:after,
        #thread-to-pdf:after,
        #thread-to-pdf #manifest:after,
        #thread-to-pdf #manifest .list:after,
        #thread-to-pdf #manifest .item:after,
        #thread-to-pdf #buttons:after,
        #debug-output:after {
            content: "";
            display: block;
            clear: both;
        }

        /* Intro text styles */

        #intro-text {
            background-color: #1e1e1e;
            padding: 12px 24px;
        }
        #intro-text ul {
            padding: 0;
            margin: 0 auto;
            list-style-type: none;
            font-family: monospace;
        }
        #intro-text li {
            display: block;
            float: left;
            margin: 0 5px 5px 0;
            padding: 0 5px 5px 0;
            width: auto;
        }
        #intro-text li.published,
        #intro-text li.url {
            clear: left;
        }

        /* Thread-to-pdf container */

        #thread-to-pdf {
            min-width: 740px;
            background-color: #262626;
            padding: 6px;
        }
        #thread-to-pdf,
        #thread-to-pdf * {
            box-sizing: border-box;
        }

        /* Manifest styles */

        #thread-to-pdf #manifest {
            display: block;
            max-width: 100%;
            overflow: auto;
            padding: 0;
            margin: 0;
        }
        #thread-to-pdf #manifest .list {
            color: #fff;
            list-style-type: none;
            margin: 0;
            padding: 0;
            float: left;
            clear: none;
            width: calc((100% / 2) - 16px);
        }

        #thread-to-pdf #manifest .title {
            display: block;
            position: relative;
            padding: 9px 24px 12px;
            margin: 0 3px 3px;
            font-weight: normal;
            font-size: 13px;
            line-height: 1;
            color: #efefef;
            background-color: #1e1e1e;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.2);
            border-width: 1px 0 0 1px;
            box-shadow: inset -1px -1px 0 rgba(255, 255, 255, 0.05);
        }
        #thread-to-pdf #manifest .title .name {
            font-weight: bold;
        }
        #thread-to-pdf #manifest .title .count {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 70px;
            font-size: 11px;
            padding: 0 6px;
            margin: 0;
            text-align: right;
            color: #efefef;
            background-color: #161616;
            border-radius: 3px;
        }
        #thread-to-pdf #manifest .title .progress {
            position: absolute;
            top: 4px;
            right: 86px;
            width: 100px;
        }

        #thread-to-pdf #manifest .item {
            display: block;
            position: relative;
            min-width: 300px;
            min-height: 34px;
            max-width: 100%;
            overflow: auto;
            margin: 0 6px 3px;
            padding: 3px;
            padding-left: calc(6px + 12px);
            padding-right: calc(6px + 1.6rem);
            background-color: transparent;
            line-height: 1.6;
        }
        #thread-to-pdf #manifest .item:hover {
            background-color: #525252;
        }
        #thread-to-pdf.ready #manifest .item {
            transition: background-color 0.3s;
        }


        #thread-to-pdf #manifest .item.slide-in {
            transform: scaleY(0.1);
            margin-top: -38px;
            animation: item-slide-in 1s forwards;
        }
        @keyframes item-slide-in {
            0% { transform: scaleY(0.1); margin-top: -38px; }
            100% { transform: scaleY(1); margin-top: 0; }
        }


        #thread-to-pdf #manifest .item.empty {
            text-align: center;
            padding: 6px 24px;
            margin: 0 9px 9px;
            color: #efefef;
            background-color: #1e1e1e;
        }

        #thread-to-pdf #manifest .item .num,
        #thread-to-pdf #manifest .item .bullet,
        #thread-to-pdf #manifest .item .name,
        #thread-to-pdf #manifest .item .file,
        #thread-to-pdf #manifest .item .status {
            display: inline-block;
            box-sizing: border-box;
            vertical-align: middle;
            text-align: center;
            font-weight: normal;
        }
        #thread-to-pdf #manifest .item .num,
        #thread-to-pdf #manifest .item .bullet,
        #thread-to-pdf #manifest .item .name,
        #thread-to-pdf #manifest .item .file {
            margin: 0 6px 0 0;
            padding: 3px 6px;
            min-width: 28px;
            min-height: 28px;
            border-radius: 3px;
        }

        #thread-to-pdf #manifest .item .num,
        #thread-to-pdf #manifest .item .name {
            /*  background-color: #1e1e1e; */
            /* background-color: #242424; */
            background-color: #1e1e1e;
            color: #b4b4b4;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.2);
            border-width: 1px 0 0 1px;
            box-shadow: inset -1px -1px 0 rgba(255, 255, 255, 0.05);
        }
        #thread-to-pdf #manifest .item:hover .status,
        #thread-to-pdf #manifest .item:hover .file {
            box-shadow: 1px 1px 0 #1e1e1e;
        }

        #thread-to-pdf #manifest .item .num {
            width: 70px;
        }
        #thread-to-pdf #manifest .item .num > b {
            opacity: 0.5;
        }
        #thread-to-pdf #manifest .item .bullet {
            position: absolute;
            top: 3px;
            left: 3px;
            margin: 0;
            padding: 3px 0;
            min-width: 0;
            width: 12px;
        }
        #thread-to-pdf #manifest .item .name {
            min-width: calc(100% - 154px);
        }
        #thread-to-pdf #manifest .item .bullet + .name {
            min-width: calc(100% - 78px);
        }

        #thread-to-pdf #manifest .item .file {
            padding: 1px;
            min-width: 2rem;
            /* background-color: #696969;  */
            background-color: #1e1e1e;
            text-decoration: none;
            color: #ccc;
            font-size: 11px;
            line-height: 2;
        }
        #thread-to-pdf #manifest .item .file[href] {
            background-color: #898989;
            color: #fff;
        }
        #thread-to-pdf #manifest .item .file[href].img { background-color: #007cf4; }
        #thread-to-pdf #manifest .item .file[href].img:hover { background-color: #3cc0fc; }
        #thread-to-pdf #manifest .item .file[href].doc { background-color: #ae4fc8; }
        #thread-to-pdf #manifest .item .file[href].doc:hover { background-color: #d77afc; }

        #thread-to-pdf #manifest .item .status {
            position: absolute;
            top: 4px;
            right: 6px;
            padding: 0;
            margin: 0;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background-color: #696969;
            transform: scale(1.0);
        }
        @keyframes item-status-pulse {
            0% { transform: scale(1.0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1.0); }
        }
        #thread-to-pdf[data-status="pending"] .title,
        #thread-to-pdf [data-status="pending"] .title { color: #d8ac29 !important; }
        #thread-to-pdf[data-status="pending"] .status,
        #thread-to-pdf [data-status="pending"] .status { background-color: #d8ac29 !important; }
        #thread-to-pdf[data-status="loading"] .title,
        #thread-to-pdf [data-status="loading"] .title { color: #c46f31 !important; }
        #thread-to-pdf[data-status="loading"] .status,
        #thread-to-pdf [data-status="loading"] .status { background-color: #c46f31 !important; animation: item-status-pulse 1s infinite; }
        #thread-to-pdf[data-status="complete"] .title,
        #thread-to-pdf [data-status="complete"] .title { color: #3eaf3e !important; }
        #thread-to-pdf[data-status="complete"] .status,
        #thread-to-pdf [data-status="complete"] .status { background-color: #3eaf3e !important; }
        #thread-to-pdf[data-status="error"] .title,
        #thread-to-pdf [data-status="error"] .title { color: #ba3b3b !important; }
        #thread-to-pdf[data-status="error"] .status,
        #thread-to-pdf [data-status="error"] .status { background-color: #ba3b3b !important; }

        /* Buttons section */

        #thread-to-pdf #buttons {
            display: block;
            max-width: 100%;
            overflow: auto;
            border-bottom: 1px dotted #464646;
            padding: 9px 0 12px;
            margin: 0 6px 12px;
            position: relative;
            padding-right: 42px;
        }
        #thread-to-pdf #buttons .button {
            display: inline-block;
            width: 150px;
            overflow: auto;
            padding: 10px;
            margin: 0 6px 0 0;
            background-color: #007cf4;
            border: 1px solid #007cf4;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 3px;
            cursor: pointer;
            filter: brightness(1) saturate(1);
        }
        #thread-to-pdf.ready #buttons .button {
            transition: background-color 0.3s, border-color 0.3s, filter 0.3s;
        }
        #thread-to-pdf #buttons .button:hover {
            filter: brightness(1.1) saturate(1.1);
        }
        #thread-to-pdf #buttons .button#start {
            background-color: #007cf4;
            border-color: #007cf4;
        }
        #thread-to-pdf #buttons .button#stop {
            background-color: #ff0000;
            border-color: #ff0000;
        }
        #thread-to-pdf #buttons .button#download {
            background-color: #008001;
            border-color: #008001;
            float: right;
        }
        #thread-to-pdf #buttons .button[disabled] {
            background-color: #696969;
            border-color: #696969;
            color: #ccc;
            cursor: not-allowed;
            filter: brightness(1) saturate(0);
        }
        #thread-to-pdf #buttons .status {
            display: block;
            position: absolute;
            top: 10px;
            right: 5px;
            padding: 0;
            margin-top: 2px;
            width: 30px;
            height: 30px;
            background-color: #696969;
            border-radius: 50%;
            transform: scale(1.0);
        }
        @keyframes export-status-pulse {
            0% { transform: scale(1.0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1.0); }
        }

        /* Debug styles */

        #debug-output {
            display: block;
            overflow: auto;
            border-style: dotted;
            padding: 0;
            margin: 10px auto 0;
            text-align: left;
            font-size: 13px;
            line-height: 1.6;
        }
        #debug-output pre:not(.array) {
            white-space: normal;
        }
        #debug-output > .title {
            display: block;
            background-color: #1e1e1e;
            padding: 9px 12px;
        }
        #debug-output > .output {
            padding: 10px;
            font-family: monospace;
        }
        #debug-output:not(:hover) .hide-on-blur {
            display: none;
        }

    </style>
<?
$html_content_styles .= ob_get_clean();

// And now we can assemble the final HTML page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $html_content_head ?>
    <?= $html_content_styles ?>
    <?= $html_content_scripts ?>
</head>
<body>
    <?= $html_content_body ?>
</body>
</html>
