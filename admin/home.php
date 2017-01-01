<? ob_start(); ?>
    <div style="margin: 0 auto 20px; font-weight: bold;">
        <a href="admin.php">Admin Panel</a> &raquo;
        <a href="admin.php?action=home">Main Menu</a> &raquo;
    </div>
    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>
    <ul style="margin: 0 auto 20px; font-weight: bold;">
        <li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;">
            <a target="_blank" href="admin.php?action=update&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>&limit=1">Update Save Files</a>
            <em style="font-weight: normal; float: right; width: 60%;">apply specific patches to existing save files</em>
        </li>
        <li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;">
            <a target="_blank" href="admin.php?action=import_players&amp;limit=10">Update Player Database</a>
            <em style="font-weight: normal; float: right; width: 60%;">rescan the players directory and update the database</em>
        </li>
        <li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;">
            <a target="_blank" href="admin.php?action=import_robots&amp;limit=10">Update Robot Databases</a>
            <em style="font-weight: normal; float: right; width: 60%;">rescan the robots directory and update the database</em>
        </li>
        <li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;">
            <a target="_blank" href="admin.php?action=import_abilities&amp;limit=10">Update Ability Database</a>
            <em style="font-weight: normal; float: right; width: 60%;">rescan the abilities directory and update the database</em>
        </li>
        <li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;">
            <a target="_blank" href="admin.php?action=import_items&amp;limit=10">Update Item Database</a>
            <em style="font-weight: normal; float: right; width: 60%;">rescan the items directory and update the database</em>
        </li>
        <li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;">
            <a target="_blank" href="admin.php?action=import_fields&amp;limit=10">Update Field Database</a>
            <em style="font-weight: normal; float: right; width: 60%;">rescan the fields directory and update the database</em>
        </li>
        <li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;">
            <a target="_blank" href="admin.php?action=delete_cache">Delete Cached Files</a>
            <em style="font-weight: normal; float: right; width: 60%;">delete cached markup and database objects</em>
        </li>
        <li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;">
            <a target="_blank" href="admin.php?action=clear_sessions">Clear All Sessions</a>
            <em style="font-weight: normal; float: right; width: 60%;">clear sessions and log out all users</em>
        </li>
        <li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;">
            <a target="_blank" href="admin.php?action=purge&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>&limit=10">Purge Inactive Members</a>
            <em style="font-weight: normal; float: right; width: 60%;">purge user accounts with zero progress</em>
        </li>
    </ul>
<? $this_page_markup .= ob_get_clean(); ?>