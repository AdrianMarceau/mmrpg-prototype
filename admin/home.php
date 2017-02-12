<? ob_start(); ?>
    <div class="breadcrumb">
        <a href="admin.php">Admin Panel</a>
        &raquo; <a href="admin.php?action=home">Main Menu</a>
    </div>
    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>
    <ul class="adminhome">
        <li>
            <a href="admin.php?action=edit_users">Moderate Users</a>
            <em>update or modify user account info and permissions</em>
        </li>
        <li>
            <a href="admin.php?action=purge&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>&limit=10">Purge Inactive Users</a>
            <em>purge user accounts with zero progress</em>
        </li>
        <li>
            <a href="admin.php?action=import_players&amp;limit=10">Refresh Player Database</a>
            <em>rescan the players directory and update the database</em>
        </li>
        <li>
            <a href="admin.php?action=import_robots&amp;limit=10">Refresh Robot Databases</a>
            <em>rescan the robots directory and update the database</em>
        </li>
        <li>
            <a href="admin.php?action=import_abilities&amp;limit=10">Refresh Ability Database</a>
            <em>rescan the abilities directory and update the database</em>
        </li>
        <li>
            <a href="admin.php?action=import_items&amp;limit=10">Refresh Item Database</a>
            <em>rescan the items directory and update the database</em>
        </li>
        <li>
            <a href="admin.php?action=import_fields&amp;limit=10">Refresh Field Database</a>
            <em>rescan the fields directory and update the database</em>
        </li>
        <li>
            <a href="admin.php?action=delete_cache">Delete Cached Files</a>
            <em>delete cached markup and database objects</em>
        </li>
        <li>
            <a href="admin.php?action=clear_sessions">Clear All Sessions</a>
            <em>clear sessions and log out all users</em>
        </li>
        <li>
            <a href="admin.php?action=update&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>&limit=1">Patch Save Files</a>
            <em>apply specific patches to existing save files</em>
        </li>
    </ul>
<? $this_page_markup .= ob_get_clean(); ?>