<? ob_start(); ?>

    <div class="breadcrumb">
        <a href="admin.php">Admin Panel</a>
        &raquo; <a href="admin.php?action=home">Home</a>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <? print_form_messages() ?>

    <? /*
    <pre>$this_admininfo = <?= print_r($this_admininfo, true) ?></pre>
    <pre>$this_adminaccess = <?= print_r($this_adminaccess, true) ?></pre>
    */ ?>

    <?
    /* -- USER CONTROLS -- */
    if (true){
        $temp_group_name = 'User Controls';
        ob_start();
        if (in_array('*', $this_adminaccess)
            || in_array('edit_users', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=edit_users">Moderate Users</a>
                <em>update or modify user account info and permissions</em>
            </li>
            <?
        }
        $temp_item_markup = trim(ob_get_clean());
        if (!empty($temp_item_markup)){
            ?>
            <ul class="adminhome">
                <li class="top">
                    <strong><?= $temp_group_name ?></strong>
                </li>
                <?= $temp_item_markup ?>
            </ul>
            <?
        }
    }
    ?>

    <?
    /* -- GAME EDITORS -- */
    if (true){
        $temp_group_name = 'Game Editors';
        ob_start();
        if (in_array('*', $this_adminaccess)
            || in_array('edit_robots', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=edit_robots">Edit Robots</a>
                <em>edit the stats, abilities, and details of database robots</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('edit_challenges', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=edit_challenges">Edit Challenges</a>
                <em>edit and create challenge missions for the post-game</em>
            </li>
            <?
        }
        $temp_item_markup = trim(ob_get_clean());
        if (!empty($temp_item_markup)){
            ?>
            <ul class="adminhome">
                <li class="top">
                    <strong><?= $temp_group_name ?></strong>
                </li>
                <?= $temp_item_markup ?>
            </ul>
            <?
        }
    }
    ?>

    <?
    /* -- IMPORT SCRIPTS -- */
    if (true){
        $temp_group_name = 'Import Scripts';
        ob_start();
        if (in_array('*', $this_adminaccess)
            || in_array('import_players', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=import_players">Refresh Player Database</a>
                <em>rescan players directory then purge + update the database</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('import_abilities', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=import_abilities">Refresh Ability Database</a>
                <em>rescan players abilities then purge + update the database</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('import_items', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=import_items">Refresh Item Database</a>
                <em>rescan items directory then purge + update the database</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('import_fields', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=import_fields">Refresh Field Database</a>
                <em>rescan fields directory then purge + update the database</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('import_robots', $this_adminaccess)){
            ?>
            <li class="item">
                <a data-old-href="admin.php?action=import_robots"><del>Refresh Robot Databases</del></a>
                <em><del>rescan robots directory then purge + update the database</del></em>
            </li>
            <?
        }
        $temp_item_markup = trim(ob_get_clean());
        if (!empty($temp_item_markup)){
            ?>
            <ul class="adminhome">
                <li class="top">
                    <strong><?= $temp_group_name ?></strong>
                </li>
                <?= $temp_item_markup ?>
            </ul>
            <?
        }
    }
    ?>

    <?
    /* -- MISC TOOLS -- */
    if (true){
        $temp_group_name = 'Misc Tools';
        ob_start();
        if (in_array('*', $this_adminaccess)
            || in_array('refresh_leaderboard', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=update&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>&amp;patch=recalculate_all_battle_points&amp;incognito=true&amp;force=true">Refresh Leaderboard</a>
                <em>recalculate battle points for all idle users</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('delete_cache', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=delete_cache">Delete Cached Files</a>
                <em>delete cached markup and database objects</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('purge_bogus', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin.php?action=purge&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>">Purge Bogus Users</a>
                <em>purge user accounts with zero progress</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('clear_sessions', $this_adminaccess)){
            ?>
            <li class="item">
                <a data-href="admin.php?action=clear_sessions"><del>Clear All Sessions</del></a>
                <em><del>clear sessions and log out all users</del></em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('patch_saves', $this_adminaccess)){
            ?>
            <li class="item">
                <a data-href="admin.php?action=update"><del>Patch Save Files</del></a>
                <em><del>apply specific patches to existing save files</del></em>
            </li>
            <?
        }
        $temp_item_markup = trim(ob_get_clean());
        if (!empty($temp_item_markup)){
            ?>
            <ul class="adminhome">
                <li class="top">
                    <strong><?= $temp_group_name ?></strong>
                </li>
                <?= $temp_item_markup ?>
            </ul>
            <?
        }
    }
    ?>

<? $this_page_markup .= ob_get_clean(); ?>