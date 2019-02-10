<?

// Require the application top file
define('MMRPG_ADMIN_PANEL', true);
require_once('../top.php');

//echo('<pre>$_POST = '.print_r($_POST, true).'</pre>'.PHP_EOL);
//echo('<pre>$_FILES = '.print_r($_FILES, true).'</pre>'.PHP_EOL);

// Define a function for existing with a JS post to the parent
function js_exit($status, $message = '', $details = ''){
    $output = ob_get_clean();
    $details = str_replace("'", "\\'", $details);
    ?>
    <!DOCTYPE html>
    <html><head></head><body>
    <script type="text/javascript">
    var thisWindow = window.top != window.self ? window.top : window.self;
    if (typeof thisWindow.onUpdateImageComplete === 'function'){ thisWindow.onUpdateImageComplete('<?= $status ?>', '<?= $message ?>', '<?= $details ?>'); }
    else if (typeof thisWindow.console !== 'undefined'){ thisWindow.console.log('onUpdateImageComplete', '<?= $status ?>', '<?= $message ?>', '<?= $details ?>'); }
    else { alert('onUpdateImageComplete = <?= $status.'|'.$message.(!empty($details) ? '|'.$details : '') ?>'); }
    </script>
    <pre><?= $status.'|'.$message.(!empty($details) ? '|'.$details : '') ?></pre>
    <?= !empty($output) ? '<div class="output">'.$output.'</div>' : '' ?>
    </body></html>
    <?
    exit();
}

// Start the output buffer just in case
ob_start();

// Collect required fields from the POST and FILE data before continuing
$image_data = array();
$image_data['path'] = !empty($_POST['file_path']) && preg_match('/^[-_0-9a-z\.\/]+$/i', $_POST['file_path']) ? trim($_POST['file_path']) : false;
$image_data['name'] = !empty($_POST['file_name']) && preg_match('/^[-_0-9a-z\.]+$/i', $_POST['file_name']) ? trim($_POST['file_name']) : false;
$image_data['action'] = !empty($_POST['file_action']) && in_array($_POST['file_action'], array('upload', 'delete')) ? trim($_POST['file_action']) : false;
$image_data['fileinfo'] = !empty($_FILES['file_info']) && is_array($_FILES['file_info']) && $_FILES['file_info']['error'] === UPLOAD_ERR_OK ? $_FILES['file_info'] : false;

// Collect the optional validation fields from the POST data just in case
$image_data['req_kind'] = !empty($_POST['file_kind']) && preg_match('/^[-_0-9a-z]+\/[-_0-9a-z]+$/i', $_POST['file_kind']) ? trim($_POST['file_kind']) : false;
$image_data['req_width'] = !empty($_POST['file_width']) && is_numeric($_POST['file_width']) ? (int)(trim($_POST['file_width'])) : false;
$image_data['req_height'] = !empty($_POST['file_height']) && is_numeric($_POST['file_height']) ? (int)(trim($_POST['file_height'])) : false;

// Check the hash to see if this action is approved and validated
$actual_hash = md5($image_data['action'].'/'.$image_data['path'].$image_data['name'].'/'.MMRPG_SETTINGS_PASSWORD_SALT);
$provided_hash = !empty($_POST['file_hash']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['file_hash']) ? trim($_POST['file_hash']) : false;
//echo('<pre>$actual_hash = '.print_r($actual_hash, true).'</pre>'.PHP_EOL);
//echo('<pre>$provided_hash = '.print_r($provided_hash, true).'</pre>'.PHP_EOL);
$image_data['approved'] = $actual_hash === $provided_hash ? true : false;
if (!$image_data['approved']){ js_exit('error', 'auth-token-mismatch'); }

// Check to see if the target image already exists or not
$image_data['dirpath'] = MMRPG_CONFIG_ROOTDIR.$image_data['path'].$image_data['name'];
$image_data['exists'] = file_exists($image_data['dirpath']) ? true : false;
if ($image_data['action'] == 'upload' && $image_data['exists']){ js_exit('error', 'file-exists'); }
elseif ($image_data['action'] == 'delete' && !$image_data['exists']){ js_exit('error', 'file-not-exists'); }

// DEBUG DEBUG DEBUG
//echo('<pre>$image_data = '.print_r($image_data, true).'</pre>'.PHP_EOL);

// Ensure the main folder is created for this file
if (!file_exists(MMRPG_CONFIG_ROOTDIR.$image_data['path'])){
    @mkdir(MMRPG_CONFIG_ROOTDIR.$image_data['path']);
    @chown(MMRPG_CONFIG_ROOTDIR.$image_data['path'], 'mmrpgworld');
}

// If this is an UPLOAD request, validate the file and then move it if possible
if ($image_data['action'] == 'upload'){

    // Validate the image type if a type filter was provided
    if (!empty($image_data['req_kind']) && !strstr($image_data['fileinfo']['type'], 'image/')){ js_exit('error', 'invalid-file-type', 'required: '.$image_data['req_kind'].' \nprovided: '.$image_data['fileinfo']['type']); }

    // Collect this image's size (and REAL type details)
    $image_size = getimagesize($image_data['fileinfo']['tmp_name']);
    $image_size['width'] = isset($image_size[0]) ? $image_size[0] : 0;
    $image_size['height'] = isset($image_size[1]) ? $image_size[1] : 0;
    //echo('<pre>$image_size = '.print_r($image_size, true).'</pre>'.PHP_EOL);

    // Validate the image type AGAIN if a type filter was provided
    $file_mime_type = str_replace('/jpeg', '/jpg', $image_size['mime']);
    if (!empty($image_data['req_kind']) && $file_mime_type != $image_data['req_kind']){ js_exit('error', 'invalid-image-type', 'required: '.$image_data['req_kind'].' \nprovided: '.$file_mime_type); }

    // Validate if the image is the correct width and/or height
    if ((!empty($image_data['req_width']) && $image_size['width'] != $image_data['req_width'])
        || (!empty($image_data['req_height']) && $image_size['height'] != $image_data['req_height'])){
        js_exit('error', 'wrong-file-size',
            'required: '.$image_data['req_width'].'px by '.$image_data['req_height'].'px \n'.
            'provided: '.$image_size['width'].'px by '.$image_size['height'].'px'
            );
    }

    // Attempt to move the image and return the status of the action (we can move temp file directly)
    $move_status = move_uploaded_file($image_data['fileinfo']['tmp_name'], $image_data['dirpath']);
    if (file_exists($image_data['dirpath'])){ js_exit('success', 'file-uploaded', $move_status); }
    else { js_exit('error', 'upload-error', 'error-code-'.$move_status);  }

}
// Else if this is a DELETE request, relocate the file to the backup folder w/ a rename
elseif ($image_data['action'] == 'delete'){

    // Ensure the backup folder is created for this file
    $backup_path = str_replace('/images/', '/images/backups/', MMRPG_CONFIG_ROOTDIR.$image_data['path']);
    if (!file_exists($backup_path)){
        @mkdir($backup_path);
        @chown($backup_path, 'mmrpgworld');
    }

    // Move the file to the backup folder, renaming the file with the timestamp
    $bak_append = '.bak'.date('YmdHi');
    $old_location = $image_data['dirpath'];
    $new_location = $backup_path.preg_replace('/(\.[a-z0-9]{3,})$/i', $bak_append.'$1', $image_data['name']);

    //echo('<pre>$bak_append = '.print_r($bak_append, true).'</pre>'.PHP_EOL);
    //echo('<pre>$old_location = '.print_r($old_location, true).'</pre>'.PHP_EOL);
    //echo('<pre>$new_location = '.print_r($new_location, true).'</pre>'.PHP_EOL);

    // Attempt to copy the image and return the status of the action (remove old file if successful)
    $copy_status = copy($old_location, $new_location);
    if (file_exists($new_location)){ @unlink($old_location); js_exit('success','file-removed'); }
    else { js_exit('error','remove-error', 'error-code-'.$copy_status);  }

}

// Exit now if we haven't already
$output = ob_get_clean();
echo($output);
exit();

?>