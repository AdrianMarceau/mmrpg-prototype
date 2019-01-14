<? ob_start(); ?>

    <div class="breadcrumb">
        <a href="admin.php">Admin Panel</a>
        &raquo; <a href="admin.php?action=home">Home</a>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <? print_form_messages() ?>

    <ul class="adminhome">
        <li class="top">
            <strong>User Controls</strong>
        </li>
        <li class="item">
            <a href="admin.php?action=edit_users">Moderate Users</a>
            <em>update or modify user account info and permissions</em>
        </li>
        <li class="item">
            <a href="admin.php?action=purge&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>">Purge Inactive Users</a>
            <em>purge user accounts with zero progress</em>
        </li>
        <li class="item">
            <a href="admin.php?action=update">Patch Save Files</a>
            <em>apply specific patches to existing save files</em>
        </li>
        <li class="item">
            <a href="admin.php?action=update&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>&amp;patch=recalculate_all_battle_points&amp;incognito=true&amp;force=true">Refresh Leaderboard</a>
            <em>recalculate battle points for all inactive users</em>
        </li>
    </ul>

    <ul class="adminhome">
        <li class="top">
            <strong>Database Update</strong>
        </li>
        <li class="item">
            <a href="admin.php?action=import_players">Refresh Player Database</a>
            <em>rescan the players directory and update the database</em>
        </li>
        <li class="item">
            <a href="admin.php?action=import_robots">Refresh Robot Databases</a>
            <em>rescan the robots directory and update the database</em>
        </li>
        <li class="item">
            <a href="admin.php?action=import_abilities">Refresh Ability Database</a>
            <em>rescan the abilities directory and update the database</em>
        </li>
        <li class="item">
            <a href="admin.php?action=import_items">Refresh Item Database</a>
            <em>rescan the items directory and update the database</em>
        </li>
        <li class="item">
            <a href="admin.php?action=import_fields">Refresh Field Database</a>
            <em>rescan the fields directory and update the database</em>
        </li>
    </ul>

    <ul class="adminhome">
        <li class="top">
            <strong>Misc Tools</strong>
        </li>
        <li class="item">
            <a href="admin.php?action=delete_cache">Delete Cached Files</a>
            <em>delete cached markup and database objects</em>
        </li>
        <li class="item">
            <a href="admin.php?action=clear_sessions">Clear All Sessions</a>
            <em>clear sessions and log out all users</em>
        </li>
    </ul>

<? $this_page_markup .= ob_get_clean(); ?>