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
$image_data['req_extras'] = !empty($_POST['file_extras']) && preg_match('/^[-_0-9a-z\,]+$/i', $_POST['file_extras']) ? explode(',', (trim($_POST['file_extras']))) : array();

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
//echo('<pre>$_GET = '.print_r($_GET, true).'</pre>'.PHP_EOL);
//echo('<pre>$_POST = '.print_r($_POST, true).'</pre>'.PHP_EOL);
//echo('<pre>$image_data = '.print_r($image_data, true).'</pre>'.PHP_EOL);
//exit();

// Ensure the main folder is created for this file
if (!file_exists(MMRPG_CONFIG_ROOTDIR.$image_data['path'])){
    @mkdir(MMRPG_CONFIG_ROOTDIR.$image_data['path']);
    @chown(MMRPG_CONFIG_ROOTDIR.$image_data['path'], 'mmrpgworld');
}

// Initialize the image utility object
$cms_image = new cms_image();

// Define an array to hold any updated files and what was done with them
$updated_image_files = array();
function index_updated_image_file($dirpath, $exists){
    global $updated_image_files;
    $rel_dirpath = str_replace(MMRPG_CONFIG_ROOTDIR, '', $dirpath);
    $updated_image_files[$rel_dirpath] = $exists;
}

// If this is an UPLOAD request, validate the file and then move it if possible
if ($image_data['action'] == 'upload'){

    // Validate the image type if a type filter was provided
    if (!empty($image_data['req_kind']) && !strstr($image_data['fileinfo']['type'], 'image/')){ js_exit('error', 'invalid-file-type', 'required: '.$image_data['req_kind'].' \nprovided: '.$image_data['fileinfo']['type']); }

    // Collect this image's size (and REAL type details)
    $image_size = getimagesize($image_data['fileinfo']['tmp_name']);
    $image_size['width'] = isset($image_size[0]) ? $image_size[0] : 0;
    $image_size['height'] = isset($image_size[1]) ? $image_size[1] : 0;
    $image_size['xsize'] = $image_size['height'].'x'.$image_size['height'];
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
    $move_success = file_exists($image_data['dirpath']) ? true : false;
    if ($move_success){ index_updated_image_file($image_data['dirpath'], true); }

    // Only apply extra filters/actions if the move was a success
    if ($move_success){
        //echo('<div style="padding:5px;border:1px solid #dedede;">base-image:<br /><img src="'.str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, $image_data['dirpath']).'" /></div>'.PHP_EOL);

        // Check to see if the "AUTO-ZOOM-X2" extra has been requested
        if (in_array('auto-zoom-x2', $image_data['req_extras'])){
            //echo('AUTO-ZOOM-X2 requested for uploaded image!<br />'.PHP_EOL);
            $zoom_width = $image_size['width'] * 2;
            $zoom_height = $image_size['height'] * 2;
            $zoom_xsize = $zoom_height.'x'.$zoom_height;
            $zoom_find_pattern = '/(^|$|_|\.)'.$image_size['xsize'].'(^|$|_|\.)/';
            $zoom_replace_pattern = '${1}'.$zoom_xsize.'${2}';
            $zoom_filename = preg_replace($zoom_find_pattern, $zoom_replace_pattern, $image_data['name']);
            $zoom_dirpath = preg_replace($zoom_find_pattern, $zoom_replace_pattern, $image_data['dirpath']);
            //echo('<pre>$zoom_width = '.print_r($zoom_width, true).'</pre>'.PHP_EOL);
            //echo('<pre>$zoom_height = '.print_r($zoom_height, true).'</pre>'.PHP_EOL);
            //echo('<pre>$zoom_xsize = '.print_r($zoom_xsize, true).'</pre>'.PHP_EOL);
            //echo('<pre>$zoom_filename = '.print_r($zoom_filename, true).'</pre>'.PHP_EOL);
            //echo('<pre>$zoom_dirpath = '.print_r($zoom_dirpath, true).'</pre>'.PHP_EOL);
            if (file_exists($zoom_dirpath)){ @unlink($zoom_dirpath); }
            $cms_image->image_create(
                $image_data['dirpath'], // source_path
                $zoom_dirpath, // export_path
                '', // export_type
                $zoom_width, // export_width
                $zoom_height, // export_height
                array(), // options
                array() // filters
                );
            $create_success = file_exists($zoom_dirpath) ? true : false;
            if ($create_success){ index_updated_image_file($zoom_dirpath, true); }
            //echo('<div style="padding:5px;border:1px solid #dedede;">auto-zoom-x2:<br /> <img src="'.str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, $zoom_dirpath).'" /></div>'.PHP_EOL);
        }

        // Check to see if the "AUTO-SHADOWS" extra has been requested
        if (in_array('auto-shadows', $image_data['req_extras'])){
            //echo('AUTO-SHADOWS requested for uploaded image!<br />'.PHP_EOL);
            $shadow_find_pattern = '/\/content\/(abilities|fields|items|players|robots)\/([-_a-z0-9]+)\/sprites(_[-_a-z0-9]+)?\//';
            $shadow_replace_pattern = '/content/${1}/${2}/shadows${3}/';
            $shadow_dirpath = preg_replace($shadow_find_pattern, $shadow_replace_pattern, $image_data['dirpath']);
            //echo('<pre>$shadow_dirpath = '.print_r($shadow_dirpath, true).'</pre>'.PHP_EOL);
            $shadow_dirname = dirname($shadow_dirpath);
            if (!file_exists($shadow_dirname)){ @mkdir($shadow_dirname); @chown($shadow_dirname, 'mmrpgworld'); }
            if (file_exists($shadow_dirpath)){ @unlink($shadow_dirpath); }
            $cms_image->image_create(
                $image_data['dirpath'], // source_path
                $shadow_dirpath, // export_path
                '', // export_type
                $image_size['width'], // export_width
                $image_size['height'], // export_height
                array(), // options
                array(
                    array(IMG_FILTER_BRIGHTNESS, -255),
                    array(IMG_FILTER_ALPHA, 0.3),
                    ) // filters
                );
            $create_success = file_exists($shadow_dirpath) ? true : false;
            if ($create_success){ index_updated_image_file($shadow_dirpath, true); }
            //echo('<div style="padding:5px;border:1px solid #dedede;">auto-shadow:<br /> <img src="'.str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, $shadow_dirpath).'" /></div>'.PHP_EOL);
        }

        // Check to see if the "AUTO-ZOOM-X2" and "AUTO-SHADOWS" extras were requested
        if (in_array('auto-zoom-x2', $image_data['req_extras'])
            && in_array('auto-shadows', $image_data['req_extras'])){
            //echo('AUTO-ZOOM-X2+AUTO-SHADOWS requested for uploaded image!<br />'.PHP_EOL);
            $zoom_shadow_find_pattern = '/\/content\/(abilities|fields|items|players|robots)\/([-_a-z0-9]+)\/sprites(_[-_a-z0-9]+)?\//';
            $zoom_shadow_replace_pattern = '/content/${1}/${2}/shadows${3}/';
            $zoom_shadow_dirpath = preg_replace($zoom_shadow_find_pattern, $zoom_shadow_replace_pattern, $zoom_dirpath);
            //echo('<pre>$zoom_shadow_dirpath = '.print_r($zoom_shadow_dirpath, true).'</pre>'.PHP_EOL);
            if (file_exists($zoom_shadow_dirpath)){ @unlink($zoom_shadow_dirpath); }
            $cms_image->image_create(
                $zoom_dirpath, // source_path
                $zoom_shadow_dirpath, // export_path
                '', // export_type
                $zoom_width, // export_width
                $zoom_height, // export_height
                array(), // options
                array(
                    array(IMG_FILTER_BRIGHTNESS, -255),
                    array(IMG_FILTER_ALPHA, 0.3),
                    ) // filters
                );
            $create_success = file_exists($zoom_shadow_dirpath) ? true : false;
            if ($create_success){ index_updated_image_file($zoom_shadow_dirpath, true); }
            //echo('<div style="padding:5px;border:1px solid #dedede;">auto-zoom-x2+auto-shadow:<br /> <img src="'.str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, $zoom_shadow_dirpath).'" /></div>'.PHP_EOL);
        }

    }

    // Return success of error by checking that the file has been added to the destination
    if ($move_success){ js_exit('success', 'file-uploaded', json_encode(array('updated' => $updated_image_files))); }
    else { js_exit('error', 'upload-error', 'error-code-'.$move_status);  }

}
// Else if this is a DELETE request, relocate the file to the backup folder w/ a rename
elseif ($image_data['action'] == 'delete'){

    // Ensure the backup folder is created for this file
    $backup_path = preg_replace('/\/content\/(abilities|fields|items|players|robots)\//', '/images/backups/${1}/', MMRPG_CONFIG_ROOTDIR.$image_data['path']);
    if (!file_exists($backup_path)){
        recurseMakeDir($backup_path, 'images/backups/');
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
    $copy_success = file_exists($new_location) ? true : false;
    $unlink_status = $copy_success ? @unlink($old_location) : false;
    $unlink_success = $copy_success ? (!file_exists($old_location) ? true : false) : false;
    if ($copy_success && $unlink_success){ index_updated_image_file($old_location, false); }

    // Only process extra actions if the copy and unlink were a success
    if ($copy_success && $unlink_success){
        //echo('<div style="padding:5px;border:1px solid #dedede;">base-image:<br /><img src="'.str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, $old_location).'" /></div>'.PHP_EOL);

        // Collect this image's size (and REAL type details)
        $image_size = getimagesize($new_location);
        $image_size['width'] = isset($image_size[0]) ? $image_size[0] : 0;
        $image_size['height'] = isset($image_size[1]) ? $image_size[1] : 0;
        $image_size['xsize'] = $image_size['height'].'x'.$image_size['height'];
        //echo('<pre>$image_size = '.print_r($image_size, true).'</pre>'.PHP_EOL);

        // Check to see if the "AUTO-ZOOM-X2" extra has been requested
        if (in_array('auto-zoom-x2', $image_data['req_extras'])){
            //echo('AUTO-ZOOM-X2 must also be deleted!<br />'.PHP_EOL);
            $zoom_width = $image_size['width'] * 2;
            $zoom_height = $image_size['height'] * 2;
            $zoom_xsize = $zoom_height.'x'.$zoom_height;
            $zoom_find_pattern = '/(^|$|_|\.)'.$image_size['xsize'].'(^|$|_|\.)/';
            $zoom_replace_pattern = '${1}'.$zoom_xsize.'${2}';
            $zoom_filename = preg_replace($zoom_find_pattern, $zoom_replace_pattern, $image_data['name']);
            $zoom_dirpath = preg_replace($zoom_find_pattern, $zoom_replace_pattern, $image_data['dirpath']);
            //echo('<pre>$zoom_data = '.print_r($zoom_data, true).'</pre>'.PHP_EOL);
            $old_zoom_location = preg_replace($zoom_find_pattern, $zoom_replace_pattern, $old_location);
            $new_zoom_location = preg_replace($zoom_find_pattern, $zoom_replace_pattern, $new_location);
            //echo('<pre>$old_zoom_location = '.print_r($old_zoom_location, true).'</pre>'.PHP_EOL);
            //echo('<pre>$new_zoom_location = '.print_r($new_zoom_location, true).'</pre>'.PHP_EOL);
            if (file_exists($old_zoom_location)){
                $copy_status2 = copy($old_zoom_location, $new_zoom_location);
                $copy_success2 = file_exists($new_zoom_location) ? true : false;
                $unlink_status2 = $copy_success2 ? @unlink($old_zoom_location) : false;
                $unlink_success2 = $copy_success2 ? (!file_exists($old_zoom_location) ? true : false) : false;
                if ($copy_success2 && $unlink_success2){ index_updated_image_file($old_zoom_location, false); }
            }
            //echo('<div style="padding:5px;border:1px solid #dedede;">auto-zoom-x2:<br /><img src="'.str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, $old_zoom_location).'" /></div>'.PHP_EOL);
        }

        // Check to see if the "AUTO-SHADOWS" extra has been requested
        if (in_array('auto-shadows', $image_data['req_extras'])){
            //echo('AUTO-SHADOWS must also be deleted!<br />'.PHP_EOL);
            $shadow_find_pattern = '/\/content\/(abilities|fields|items|players|robots)\/([-_a-z0-9]+)\/sprites(_[-_a-z0-9]+)?\//';
            $shadow_replace_pattern = '/content/${1}/${2}/shadows${3}/';
            $old_shadow_location = preg_replace($shadow_find_pattern, $shadow_replace_pattern, $old_location);
            $new_shadow_location = preg_replace($shadow_find_pattern, $shadow_replace_pattern, $new_location);
            //echo('<pre>$old_shadow_location = '.print_r($old_shadow_location, true).'</pre>'.PHP_EOL);
            //echo('<pre>$new_shadow_location = '.print_r($new_shadow_location, true).'</pre>'.PHP_EOL);
            if (file_exists($old_shadow_location)){
                $copy_status2 = copy($old_shadow_location, $new_shadow_location);
                $copy_success2 = file_exists($new_shadow_location) ? true : false;
                $unlink_status2 = $copy_success2 ? @unlink($old_shadow_location) : false;
                $unlink_success2 = $copy_success2 ? (!file_exists($old_shadow_location) ? true : false) : false;
                if ($copy_success2 && $unlink_success2){ index_updated_image_file($old_shadow_location, false); }
            }
            //echo('<div style="padding:5px;border:1px solid #dedede;">auto-shadow:<br /><img src="'.str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, $old_shadow_location).'" /></div>'.PHP_EOL);
        }

        // Check to see if the "AUTO-ZOOM-X2" and "AUTO-SHADOWS" extras were requested
        if (in_array('auto-zoom-x2', $image_data['req_extras'])
            && in_array('auto-shadows', $image_data['req_extras'])){
            //echo('AUTO-ZOOM-X2+AUTO-SHADOWS must also be deleted!<br />'.PHP_EOL);
            $zoom_shadow_find_pattern = '/\/content\/(abilities|fields|items|players|robots)\/([-_a-z0-9]+)\/sprites(_[-_a-z0-9]+)?\//';
            $zoom_shadow_replace_pattern = '/content/${1}/${2}/shadows${3}/';
            $old_zoom_shadow_location = preg_replace($zoom_shadow_find_pattern, $zoom_shadow_replace_pattern, $old_zoom_location);
            $new_zoom_shadow_location = preg_replace($zoom_shadow_find_pattern, $zoom_shadow_replace_pattern, $new_zoom_location);
            //echo('<pre>$old_zoom_shadow_location = '.print_r($old_zoom_shadow_location, true).'</pre>'.PHP_EOL);
            //echo('<pre>$new_zoom_shadow_location = '.print_r($new_zoom_shadow_location, true).'</pre>'.PHP_EOL);
            if (file_exists($old_zoom_shadow_location)){
                $copy_status2 = copy($old_zoom_shadow_location, $new_zoom_shadow_location);
                $copy_success2 = file_exists($new_zoom_shadow_location) ? true : false;
                $unlink_status2 = $copy_success2 ? @unlink($old_zoom_shadow_location) : false;
                $unlink_success2 = $copy_success2 ? (!file_exists($old_zoom_shadow_location) ? true : false) : false;
                if ($copy_success2 && $unlink_success2){ index_updated_image_file($old_zoom_shadow_location, false); }
            }
            //echo('<div style="padding:5px;border:1px solid #dedede;">auto-zoom-x2+auto-shadow:<br /><img src="'.str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, $old_zoom_shadow_location).'" /></div>'.PHP_EOL);
        }

    }

    // Return success of error by checking that the file has been added to the destination
    if ($copy_success && $unlink_success){ js_exit('success','file-removed', json_encode(array('updated' => $updated_image_files))); }
    else { js_exit('error','remove-error', 'error-code-'.$copy_status);  }

}

// Exit now if we haven't already
$output = ob_get_clean();
echo($output);
exit();

?>