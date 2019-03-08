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
    </ul>

    <ul class="adminhome">
        <li class="top">
            <strong>Game Editors</strong>
        </li>
        <li class="item">
            <a href="admin.php?action=edit_robots">Edit Robot Database</a>
            <em>edit the stats, abilities, and details of database robots</em>
        </li>
        <li class="item">
            <a href="admin.php?action=edit_robots">Edit Challenge Missions</a>
            <em>edit and create various challenge missions for the post-game</em>
        </li>
    </ul>

    <ul class="adminhome">
        <li class="top">
            <strong>Import Scripts</strong>
        </li>
        <li class="item">
            <a href="admin.php?action=import_players">Refresh Player Database</a>
            <em>rescan players directory then purge + update the database</em>
        </li>
        <li class="item">
            <a href="admin.php?action=import_abilities">Refresh Ability Database</a>
            <em>rescan players abilities then purge + update the database</em>
        </li>
        <li class="item">
            <a href="admin.php?action=import_items">Refresh Item Database</a>
            <em>rescan items directory then purge + update the database</em>
        </li>
        <li class="item">
            <a href="admin.php?action=import_fields">Refresh Field Database</a>
            <em>rescan fields directory then purge + update the database</em>
        </li>
        <li class="item">
            <a data-old-href="admin.php?action=import_robots"><del>Refresh Robot Databases</del></a>
            <em><del>rescan robots directory then purge + update the database</del></em>
        </li>
    </ul>

    <ul class="adminhome">
        <li class="top">
            <strong>Misc Tools</strong>
        </li>
        <li class="item">
            <a href="admin.php?action=update&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>&amp;patch=recalculate_all_battle_points&amp;incognito=true&amp;force=true">Refresh Leaderboard</a>
            <em>recalculate battle points for all idle users</em>
        </li>
        <li class="item">
            <a href="admin.php?action=delete_cache">Delete Cached Files</a>
            <em>delete cached markup and database objects</em>
        </li>
        <li class="item">
            <a href="admin.php?action=purge&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>">Purge Bogus Users</a>
            <em>purge user accounts with zero progress</em>
        </li>
        <li class="item">
            <a data-href="admin.php?action=clear_sessions"><del>Clear All Sessions</del></a>
            <em><del>clear sessions and log out all users</del></em>
        </li>
        <li class="item">
            <a data-href="admin.php?action=update"><del>Patch Save Files</del></a>
            <em><del>apply specific patches to existing save files</del></em>
        </li>
    </ul>

<? $this_page_markup .= ob_get_clean(); ?>