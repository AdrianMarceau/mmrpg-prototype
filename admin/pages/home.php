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

    /* -- USER CONTROLS (LOCAL/DEV/STAGE/PROD) -- */
    if (true){

        // Define the group name and options array
        $this_group_name = 'User Controls';
        $this_group_options = array();

        // Populate the group options array with relevant pages and buttons
        if (in_array('*', $this_adminaccess)
            || in_array('edit-users', $this_adminaccess)){
            $this_option = array(
                'link' => array('url' => 'admin/edit-users/', 'text' => 'Moderate Users'),
                'desc' => 'update or modify user account info and permissions'
                );
            if (MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== false
                && MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== MMRPG_CONFIG_SERVER_ENV){
                if (!isset($this_option['buttons'])){ $this_option['buttons'] = array(); }
                $this_option['buttons'][] = array(
                    'action' => 'scripts/pull-table-data.php?kind=users&source='.MMRPG_CONFIG_PULL_LIVE_DATA_FROM,
                    'text' => 'Pull from '.cms_admin::print_env_name(MMRPG_CONFIG_PULL_LIVE_DATA_FROM)
                    );
                }
            $this_group_options[] = $this_option;
        }
        if (in_array('*', $this_adminaccess)
            || in_array('edit-challenges', $this_adminaccess)
            || in_array('edit-user-challenges', $this_adminaccess)){
            $this_option = array(
                'link' => array('url' => 'admin/edit-user-challenges/', 'text' => 'Moderate User Challenges'),
                'desc' => 'update or modify user-created challenge missions for the post-game'
                );
            if (MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== false
                && MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== MMRPG_CONFIG_SERVER_ENV){
                if (!isset($this_option['buttons'])){ $this_option['buttons'] = array(); }
                $this_option['buttons'][] = array(
                    'action' => 'scripts/pull-table-data.php?kind=user-challenges&source='.MMRPG_CONFIG_PULL_LIVE_DATA_FROM,
                    'text' => 'Pull from '.cms_admin::print_env_name(MMRPG_CONFIG_PULL_LIVE_DATA_FROM)
                    );
                }
            $this_group_options[] = $this_option;
        }

        // Define the group name subtext for this section
        $this_group_name_subtext = '';
        if (MMRPG_CONFIG_SERVER_ENV !== MMRPG_CONFIG_PULL_LIVE_DATA_FROM){
            $this_group_name_subtext = '<p class="env-notice warning">'.
                ucfirst(MMRPG_CONFIG_SERVER_ENV).' changes made to user accounts and content may be overwritten by '.cms_admin::print_env_name(MMRPG_CONFIG_PULL_LIVE_DATA_FROM).' data at any time. <br /> '.
                'This section is available in the '.ucfirst(MMRPG_CONFIG_SERVER_ENV).' Admin Panel for testing purposes only, so please be mindful.'.
            '</p>';
            }

        // Print out the group title and options, assuming there are any available
        echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options, $this_group_name_subtext);

    }

    /* -- (LOCAL/DEV ONLY) -- */
    if (in_array(MMRPG_CONFIG_SERVER_ENV, array('local', 'dev'))){

        /* -- GAME CONTENT EDITORS -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Game Content Editors';
            $this_group_options = array();

            // Populate the group options array with relevant pages and buttons
            if (in_array('*', $this_adminaccess)
                || in_array('edit-stars', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-stars/', 'text' => 'Edit Rogue Stars'),
                    'desc' => 'schedule and manage rogue star appearances in the post-game'
                    );
                $this_group_options[] = $this_option;
            }
            if (in_array('*', $this_adminaccess)
                || in_array('edit-challenges', $this_adminaccess)
                || in_array('edit-event-challenges', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-event-challenges/', 'text' => 'Edit Event Challenges'),
                    'desc' => 'create or modify event-based challenge missions for the post-game'
                    );
                $this_group_options[] = $this_option;
            }

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

        /* -- GAME OBJECT EDITORS -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Game Object Editors';
            $this_group_options = array();

            // Populate the group options array with relevant pages and buttons
            if (in_array('*', $this_adminaccess)
                || in_array('edit-players', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-players/', 'text' => 'Edit Player Characters'),
                    'desc' => 'edit the details and images of the in-game player characters',
                    'repo' => array(
                        'name' => 'players',
                        'data' => array('prefix' => 'player'),
                        'path' => MMRPG_CONFIG_PLAYERS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'players',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Commit & Publish All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'publish',
                                'data-kind' => 'players',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Update All',
                            'condition' => array('status' => 'unpulled_updates'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'pull',
                                'data-kind' => 'players',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }
            if (in_array('*', $this_adminaccess)
                || in_array('edit-robots', $this_adminaccess)
                || in_array('edit-robot-master', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-robot-masters/', 'text' => 'Edit Robot Masters'),
                    'desc' => 'edit the details and images of the in-game robot masters',
                    'repo' => array(
                        'name' => 'robots',
                        'data' => array('prefix' => 'robot'),
                        'path' => MMRPG_CONFIG_ROBOTS_CONTENT_PATH,
                        'filter' => array(
                            'table' => 'mmrpg_index_robots',
                            'token' => 'robot_token',
                            'extra' => array('robot_class' => 'master')
                            )
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'robots',
                                'data-subkind' => 'masters',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Commit & Publish All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'publish',
                                'data-kind' => 'robots',
                                'data-subkind' => 'masters',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Update All',
                            'condition' => array('status' => 'unpulled_updates'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'pull',
                                'data-kind' => 'robots',
                                'data-subkind' => 'masters',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }
            if (in_array('*', $this_adminaccess)
                || in_array('edit-robots', $this_adminaccess)
                || in_array('edit-support-mechas', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-support-mechas/', 'text' => 'Edit Support Mechas'),
                    'desc' => 'edit the details and images of the in-game support mechas',
                    'repo' => array(
                        'name' => 'robots',
                        'data' => array('prefix' => 'robot'),
                        'path' => MMRPG_CONFIG_ROBOTS_CONTENT_PATH,
                        'filter' => array(
                            'table' => 'mmrpg_index_robots',
                            'token' => 'robot_token',
                            'extra' => array('robot_class' => 'mecha')
                            )
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'robots',
                                'data-subkind' => 'mechas',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Commit & Publish All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'publish',
                                'data-kind' => 'robots',
                                'data-subkind' => 'mechas',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Update All',
                            'condition' => array('status' => 'unpulled_updates'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'pull',
                                'data-kind' => 'robots',
                                'data-subkind' => 'mechas',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }
            if (in_array('*', $this_adminaccess)
                || in_array('edit-robots', $this_adminaccess)
                || in_array('edit-fortress-bosses', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-fortress-bosses/', 'text' => 'Edit Fortress Bosses'),
                    'desc' => 'edit the details and images of the in-game fortress bosses',
                    'repo' => array(
                        'name' => 'robots',
                        'data' => array('prefix' => 'robot'),
                        'path' => MMRPG_CONFIG_ROBOTS_CONTENT_PATH,
                        'filter' => array(
                            'table' => 'mmrpg_index_robots',
                            'token' => 'robot_token',
                            'extra' => array('robot_class' => 'boss')
                            )
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'robots',
                                'data-subkind' => 'bosses',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Commit & Publish All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'publish',
                                'data-kind' => 'robots',
                                'data-subkind' => 'bosses',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Update All',
                            'condition' => array('status' => 'unpulled_updates'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'pull',
                                'data-kind' => 'robots',
                                'data-subkind' => 'bosses',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }
            if (in_array('*', $this_adminaccess)
                || in_array('edit-fields', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-fields/', 'text' => 'Edit Battle Fields'),
                    'desc' => 'edit the details and images of the in-game battle fields',
                    'repo' => array(
                        'name' => 'fields',
                        'data' => array('prefix' => 'field'),
                        'path' => MMRPG_CONFIG_FIELDS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'fields',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Commit & Publish All',
                            'condition' => array('status' => 'uncommitted_changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'publish',
                                'data-kind' => 'fields',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Update All',
                            'condition' => array('status' => 'unpulled_updates'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'pull',
                                'data-kind' => 'fields',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

        /* -- WEBSITE PAGE EDITORS -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Website Editors';
            $this_group_options = array();

            // Populate the group options array with relevant pages and buttons
            if (in_array('*', $this_adminaccess)
                || in_array('edit-pages', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-pages/', 'text' => 'Edit Website Pages'),
                    'desc' => 'edit the text and images on various website pages'
                    );
                $this_group_options[] = $this_option;
            }

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

    }
    /* -- (STAGE/PROD ONLY) -- */
    elseif (in_array(MMRPG_CONFIG_SERVER_ENV, array('stage', 'prod'))){

        /* -- UPDATE GAME CONTENT -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Update Game Content';
            $this_group_options = array();

            // Populate the group options array with relevant pages and buttons
            if (in_array('*', $this_adminaccess)
                || in_array('edit-stars', $this_adminaccess)){
                $this_option = array(
                    'link' => array('text' => 'Update Rogue Stars'),
                    'desc' => 'pull rogue star appearance data published to github and update',
                    'buttons' => array(
                        array(
                            'action' => 'scripts/pull-game-content.php?kind=stars&source=github',
                            'text' => 'Pull from GitHub'
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }
            if (in_array('*', $this_adminaccess)
                || in_array('edit-challenges', $this_adminaccess)
                || in_array('edit-event-challenges', $this_adminaccess)){
                $this_option = array(
                    'link' => array('text' => 'Update Event Challenges'),
                    'desc' => 'pull event-based challenge missions published to github and update',
                    'buttons' => array(
                        array(
                            'action' => 'scripts/pull-game-content.php?kind=challenges&source=github',
                            'text' => 'Pull from GitHub'
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

        /* -- UPDATE GAME OBJECTS -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Update Game Objects';
            $this_group_options = array();

            // Populate the group options array with relevant pages and buttons
            if (in_array('*', $this_adminaccess)
                || in_array('edit-players', $this_adminaccess)){
                $this_option = array(
                    'link' => array('text' => 'Update Player Characters'),
                    'desc' => 'pull changes to the in-game player characters from github and update',
                    'buttons' => array(
                        array(
                            'action' => 'scripts/pull-game-content.php?kind=players&source=github',
                            'text' => 'Pull from GitHub'
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }
            if (in_array('*', $this_adminaccess)
                || in_array('edit-robots', $this_adminaccess)
                || in_array('edit-robot-master', $this_adminaccess)
                || in_array('edit-support-mechas', $this_adminaccess)
                || in_array('edit-fortress-bosses', $this_adminaccess)){
                $this_option = array(
                    'link' => array('text' => 'Update Robots / Mechas / Bosses'),
                    'desc' => 'pull changes in-game robots, mechas, and bosses from github and update',
                    'buttons' => array(
                        array(
                            'action' => 'scripts/pull-game-content.php?kind=robots&source=github',
                            'text' => 'Pull from GitHub'
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }
            if (in_array('*', $this_adminaccess)
                || in_array('edit-fields', $this_adminaccess)){
                $this_option = array(
                    'link' => array('text' => 'Update Battle Fields'),
                    'desc' => 'pull changes to the in-game battle fields from github and update',
                    'buttons' => array(
                        array(
                            'action' => 'scripts/pull-game-content.php?kind=fields&source=github',
                            'text' => 'Pull from GitHub'
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

        /* -- UPDATE WEBSITE PAGES -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Update Website';
            $this_group_options = array();

            // Populate the group options array with relevant pages and buttons
            if (in_array('*', $this_adminaccess)
                || in_array('edit-pages', $this_adminaccess)){
                $this_option = array(
                    'link' => array('text' => 'Update Website Pages'),
                    'desc' => 'pull changes to the various website pages from github and update',
                    'buttons' => array(
                        array(
                            'action' => 'scripts/pull-game-content.php?kind=website-pages&source=github',
                            'text' => 'Pull from GitHub'
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

    }

    /* -- MISC TOOLS (LOCAL/DEV/STAGE/PROD) -- */
    if (true){

        // Define the group name and options array
        $this_group_name = 'Misc Tools';
        $this_group_options = array();

        // Populate the group options array with relevant pages and buttons
        if (in_array('*', $this_adminaccess)
            || in_array('delete-cached-files', $this_adminaccess)){
            $this_option = array(
                'link' => array('url' => 'admin/delete-cached-files/', 'text' => 'Delete Cached Files', 'target' => '_blank'),
                'desc' => 'delete cached markup and database objects'
                );
            $this_group_options[] = $this_option;
        }
        if ((MMRPG_CONFIG_SERVER_ENV === 'local'
                || MMRPG_CONFIG_PULL_LIVE_DATA_FROM === false)
            && (in_array('*', $this_adminaccess)
                || in_array('refresh-leaderboard', $this_adminaccess))){
            $this_option = array(
                'link' => array('url' => 'admin/refresh-leaderboard/incognito=true&amp;force=true', 'text' => 'Refresh Leaderboard', 'target' => '_blank'),
                'desc' => 'recalculate battle points for all idle users and refresh leaderboard'
                );
            $this_group_options[] = $this_option;
        }
        if ((MMRPG_CONFIG_SERVER_ENV === 'local'
                || MMRPG_CONFIG_PULL_LIVE_DATA_FROM === false)
            && (in_array('*', $this_adminaccess)
                || in_array('purge-bogus-users', $this_adminaccess))){
            $this_option = array(
                'link' => array('url' => 'admin/purge-bogus-users/limit=10', 'text' => 'Purge Bogus Users', 'target' => '_blank'),
                'desc' => 'purge user accounts with zero progress and no login history'
                );
            $this_group_options[] = $this_option;
        }

        // Print out the group title and options, assuming there are any available
        echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

    }

    ?>

<? $this_page_markup .= ob_get_clean(); ?>