<?

// Prevent updating if logged into a file
if (!rpg_user::is_guest()){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while updating!');  }

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin/">Admin Panel</a> &raquo;
<a href="admin/delete-cached-files/">Delete Cached Files</a> &raquo;
</div>
<?
$this_page_markup .= ob_get_clean();

$this_page_markup .= '<p style="padding: 6px; background-color: #DEDEDE;"><strong>DELETING ALL CACHED FILES...</strong></p>';

// Empty the cache, simple as that
ob_start();
$files = glob(MMRPG_CONFIG_CACHE_PATH.'*'); // get all file names
$deleted = 0;
//echo('$files = <pre>'.print_r($files, true).'</pre>');
echo('<pre>'.PHP_EOL);
foreach($files as $file){ // iterate files
    if (is_file($file) && substr(basename($file), 0, 1) !== '.'){
        unlink($file); // delete the cache file
        echo('delete '.str_replace(MMRPG_CONFIG_ROOTDIR, '', $file).''.PHP_EOL);
        $deleted++;
    } elseif (is_dir($file)){
        $subdir = rtrim($file, '/').'/';
        $subfiles = glob($subdir.'*'); // get all subfile names
        //echo('$subfiles = <pre>'.print_r($subfiles, true).'</pre>');
        foreach($subfiles as $subfile){ // iterate files
            if (is_file($subfile) && substr(basename($subfile), 0, 1) !== '.'){
                unlink($subfile); // delete the cache file
                echo('delete '.str_replace(MMRPG_CONFIG_ROOTDIR, '', $subfile).''.PHP_EOL);
                $deleted++;
            }
        }
    }
}
if (empty($deleted)){ echo('...'.PHP_EOL); }
echo('</pre>'.PHP_EOL);
$this_page_markup .= ob_get_clean();

if ($deleted > 0){
    $this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>...ALL CACHED FILES HAVE BEEN DELETED!</strong></p>';
} else {

    $this_page_markup .= '<p style="padding: 6px; background-color: #DEDEDE;"><strong>...THERE WAS NOTHING TO DELETE!</strong></p>';
}



?>