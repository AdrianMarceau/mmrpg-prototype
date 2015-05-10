<?

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while updating!');  }

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=delete_cache">Delete Cached Files</a> &raquo;
</div>
<?
$this_page_markup .= ob_get_clean();

// Empty the cache, simple as that
$files = glob(MMRPG_CONFIG_ROOTDIR.'data/cache/*'); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file))
    unlink($file); // delete file
}

$this_page_markup .= '<p style="padding: 6px; background-color: rgb(218, 255, 218);"><strong>ALL CACHED FILES HAVE BEEN DELETED</strong></p>';

?>