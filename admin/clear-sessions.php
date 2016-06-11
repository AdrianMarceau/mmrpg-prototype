<?

// Prevent updating if logged into a file
if ($this_user['userid'] != MMRPG_SETTINGS_GUEST_ID){ die('<strong>FATAL UPDATE ERROR!</strong><br /> You cannot be logged in while updating!');  }

// Print out the menu header so we know where we are
ob_start();
?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=delete_cache">Clear All Sessions</a> &raquo;
</div>
<?
$this_page_markup .= ob_get_clean();

// Empty all sessions, simple as that
ini_set('session.gc_max_lifetime', 0);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1);
session_destroy();

$this_page_markup .= '<div style="padding: 6px; background-color: rgb(218, 255, 218);">';
    $this_page_markup .= '<p><strong>NOW CLEARING ACTIVE SESSION DATA...</strong></p>';

    // Collect a list of all files in the session path
    $session_path = rtrim(session_save_path(), '/').'/';
    $session_files = scandir($session_path);
    $session_files = array_diff($session_files, array('.', '..'));

    //$this_page_markup .= '<pre>$session_path = '.print_r($session_path, true).'</pre>';
    //$this_page_markup .= '<pre>$session_files = '.print_r($session_files, true).'</pre>';

    // Filter out files that are not actually session files
    foreach ($session_files AS $key => $filename){
        if (!preg_match('/^sess_/i', $filename)){
            unset($session_files[$key]);
        }
    }

    // Re-key the session files for looping
    $session_files = array_values($session_files);

    //$this_page_markup .= '<pre>$session_files = '.print_r($session_files, true).'</pre>';

    // And now we can delete the session files from the system
    $this_page_markup .= '<pre>'."\n";
    foreach ($session_files AS $key => $filename){
        ob_start();
        echo 'Deleting session file '.($key + 1).' '.print_r($filename, true);
        unlink($session_path.$filename);
        $this_page_markup .= ob_get_clean()."\n";
    }
    $this_page_markup .= '</pre>'."\n";

    $this_page_markup .= '<p><strong>ALL ACTIVE SESSIONS HAVE BEEN CLEARED</strong></p>';
$this_page_markup .= '</div>';

?>