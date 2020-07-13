<? ob_start(); ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="admin/home/">Home</a>
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
            || in_array('edit-users', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-users/">Moderate Users</a>
                <em>update or modify user account info and permissions</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('edit-challenges', $this_adminaccess)
            || in_array('edit-user-challenges', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-user-challenges/">Moderate User Challenges</a>
                <em>update or modify user-created challenge missions for the post-game</em>
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
    /* -- POST-GAME CONTENT -- */
    if (true){
        $temp_group_name = 'Game Content Editors';
        ob_start();
        if (in_array('*', $this_adminaccess)
            || in_array('edit-stars', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-stars/">Edit Rogue Stars</a>
                <em>schedule and manage rogue star appearances in the post-game</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('edit-challenges', $this_adminaccess)
            || in_array('edit-event-challenges', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-event-challenges/">Edit Event Challenges</a>
                <em>create or modify event-based challenge missions for the post-game</em>
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
        $temp_group_name = 'Game Object Editors';
        ob_start();
        if (in_array('*', $this_adminaccess)
            || in_array('edit-players', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-players/">Edit Player Characters</a>
                <em>edit the details and images of the in-game player characters</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('edit-robots', $this_adminaccess)
            || in_array('edit-robot-master', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-robot-masters/">Edit Robot Masters</a>
                <em>edit the details and images of the in-game robot masters</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('edit-robots', $this_adminaccess)
            || in_array('edit-support-mechas', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-support-mechas/">Edit Support Mechas</a>
                <em>edit the details and images of the in-game support mechas</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('edit-robots', $this_adminaccess)
            || in_array('edit-fortress-bosses', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-fortress-bosses/">Edit Fortress Bosses</a>
                <em>edit the details and images of the in-game fortress bosses</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('edit-fields', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-fields/">Edit Battle Fields</a>
                <em>edit the details and images of the in-game battle fields</em>
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
    /* -- PAGE EDITORS -- */
    if (true){
        $temp_group_name = 'Website Editor';
        ob_start();
        if (in_array('*', $this_adminaccess)
            || in_array('edit-pages', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/edit-pages/">Edit Website Pages</a>
                <em>edit the text and images on various website pages</em>
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
            || in_array('refresh-leaderboard', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/refresh-leaderboard/incognito=true&amp;force=true" target="_blank">Refresh Leaderboard</a>
                <em>recalculate battle points for all idle users</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('delete-cached-files', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/delete-cached-files/">Delete Cached Files</a>
                <em>delete cached markup and database objects</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('purge-bogus-users', $this_adminaccess)){
            ?>
            <li class="item">
                <a href="admin/purge-bogus-users/limit=10">Purge Bogus Users</a>
                <em>purge user accounts with zero progress</em>
            </li>
            <?
        }
        if (in_array('*', $this_adminaccess)
            || in_array('patch-save-files', $this_adminaccess)){
            ?>
            <li class="item">
                <a data-href="admin/patch-save-files/"><del>Patch Save Files</del></a>
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