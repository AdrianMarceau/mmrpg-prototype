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
                    'text' => 'Pull from MMRPG-'.ucfirst(MMRPG_CONFIG_PULL_LIVE_DATA_FROM)
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
                    'text' => 'Pull from MMRPG-'.ucfirst(MMRPG_CONFIG_PULL_LIVE_DATA_FROM)
                    );
                }
            $this_group_options[] = $this_option;
        }

        // Define the group name subtext for this section
        $this_group_name_subtext = '';
        if (MMRPG_CONFIG_SERVER_ENV !== MMRPG_CONFIG_PULL_LIVE_DATA_FROM){
            $this_group_name_subtext = '<p class="env-notice warning">'.
                'Changes made to user accounts and content on the '.MMRPG_CONFIG_SERVER_ENV.'-build may be overwritten by '.cms_admin::print_env_name(MMRPG_CONFIG_PULL_LIVE_DATA_FROM).' data at any time. <br /> '.
                'This section is available in the '.ucfirst(MMRPG_CONFIG_SERVER_ENV).' Admin Panel for testing purposes only, so please be mindful.'.
            '</p>';
            }

        // Print out the group title and options, assuming there are any available
        echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options, $this_group_name_subtext);

    }

    /* -- GITHUB TOOLS (common to LOCAL/DEV/STAGE/PROD) -- */
    if (true){

        // Pull in the content types repo so we don't have to redeclare stuff
        require_once(MMRPG_CONFIG_ROOTDIR.'content/index.php');

        // Define the common kind values for each group
        $common_group_kinds = array();
        $common_group_kinds['game_database'] = array('types', 'players', 'robots', 'abilities', 'items', 'fields');
        $common_group_kinds['post_game_content'] = array('stars', 'challenges');
        $common_group_kinds['website_pages'] = array('pages');

        // Create an array to hold the actual options for each group
        $common_group_kinds_options = array();

        // Loop through the common group kinds one-by-one and generate options
        foreach ($common_group_kinds AS $kind_token => $allowed_content_types){

            // Create the sub-array to hold all the options for this specific group
            if (!isset($common_group_kinds_options[$kind_token])){ $common_group_kinds_options[$kind_token] = array(); }

            // Populate the group options array with relevant pages and buttons
            if ((in_array('*', $this_adminaccess) || in_array('pull-from-github', $this_adminaccess))){
                $option_buttons = array();

                // Loop through the content types index and append permissible buttons
                foreach ($content_types_index AS $type_key => $type_info){
                    if ($type_info['token'] === 'sql'){ continue; }
                    elseif (!in_array($type_info['xtoken'], $allowed_content_types)){ continue; }
                    // Check to see if current user allowed to edit this content type
                    if (in_array('*', $this_adminaccess)
                        || in_array('edit-'.$type_info['xtoken'], $this_adminaccess)){
                        $repo_base_path = MMRPG_CONFIG_CONTENT_PATH.$type_info['content_path'].'/';
                        $git_pull_required = cms_admin::git_pull_required($repo_base_path);
                        if (!empty($git_pull_required)){
                            $git_pull_allowed = cms_admin::git_pull_allowed($repo_base_path);
                            if ($type_info['token'] === 'sql'){ $button_text = 'Update Misc'; }
                            else { $button_text = 'Update '.ucfirst($type_info['xtoken']); }
                            $option_buttons[] = array(
                                'text' => $button_text,
                                'disabled' => !$git_pull_allowed ? true : false,
                                'attributes' => $git_pull_allowed
                                    ? array(
                                        'data-button' => 'git',
                                        'data-action' => 'update',
                                        'data-kind' => $type_info['xtoken'],
                                        'data-token' => 'all',
                                        'data-source' => 'github'
                                        )
                                    : array(
                                        'disabled' => 'disabled',
                                        'title' => ucfirst($type_info['token']).' changes must be committed first!'
                                        )
                                );
                        }
                    }
                }

                // If there weren't any buttons generated, skip this loop
                if (empty($option_buttons)){ continue; }

                // Generate the pull-from-github option with any relevant buttons
                $this_option = array(
                    'link' => array('text' => 'Pull from GitHub', 'icon' => 'cloud-download-alt'),
                    'desc' => 'pull committed changes from github repos and update',
                    'buttons' => $option_buttons
                    );

                // Add the option to the appropriate group for later
                $common_group_kinds_options[$kind_token][] = $this_option;

            }

            // Populate the group options array with relevant pages and buttons
            if ((MMRPG_CONFIG_SERVER_ENV === 'local' || MMRPG_CONFIG_SERVER_ENV === 'dev')
                && (in_array('*', $this_adminaccess) || in_array('publish-to-github', $this_adminaccess))){
                $option_buttons = array();

                // Loop through the content types index and append permissible buttons
                foreach ($content_types_index AS $type_key => $type_info){
                    if ($type_info['token'] === 'sql'){ continue; }
                    elseif (!in_array($type_info['xtoken'], $allowed_content_types)){ continue; }
                    // Check to see if current user allowed to edit this content type
                    if (in_array('*', $this_adminaccess)
                        || in_array('edit-'.$type_info['xtoken'], $this_adminaccess)){
                        // Collect git details for the repo to see if button necessary
                        $repo_base_path = MMRPG_CONFIG_CONTENT_PATH.$type_info['content_path'];
                        $committed_changes = cms_admin::git_get_committed_changes($repo_base_path);
                        // If there are changes to publish, add the appropriate button
                        if (!empty($committed_changes)){
                            $uncommitted_changes = cms_admin::git_get_uncommitted_changes($repo_base_path);
                            $git_pull_required = cms_admin::git_pull_required($repo_base_path);
                            $button_allowed = empty($uncommitted_changes) && !$git_pull_required ? true : false;
                            if (!$button_allowed){
                                if (!empty($uncommitted_changes) && $git_pull_required){ $disabled_message = ucfirst($type_info['token']).' changes must be committed first, then updates must be pulled!'; }
                                elseif (!empty($uncommitted_changes)){ $disabled_message = ucfirst($type_info['token']).' changes must be committed first!'; }
                                elseif ($git_pull_required){ $disabled_message = ucfirst($type_info['token']).' updates must be pulled first!'; }
                            }
                            if ($type_info['token'] === 'sql'){ $button_text = 'Publish Misc'; }
                            else { $button_text = 'Publish '.ucfirst($type_info['xtoken']); }
                            $option_buttons[] = array(
                                'text' => $button_text,
                                'disabled' => !$button_allowed ? true : false,
                                'attributes' => $button_allowed
                                    ? array(
                                        'data-button' => 'git',
                                        'data-action' => 'publish',
                                        'data-kind' => $type_info['xtoken'],
                                        'data-token' => 'all',
                                        'data-source' => 'github'
                                        )
                                    : array(
                                        'disabled' => 'disabled',
                                        'title' => $disabled_message
                                        )
                                );
                        }
                    }
                }

                // If there weren't any buttons generated, skip this loop
                if (empty($option_buttons)){ continue; }

                // Generate the push-to-github option with any relevant buttons
                $this_option = array(
                    'link' => array('text' => 'Push to GitHub', 'icon' => 'cloud-upload-alt'),
                    'desc' => 'push committed changes to github repos and publish',
                    'buttons' => $option_buttons
                    );

                // Add the option to the appropriate group for later
                $common_group_kinds_options[$kind_token][] = $this_option;

            }

        }

    }

    /* -- (LOCAL/DEV ONLY) -- */
    if (in_array(MMRPG_CONFIG_SERVER_ENV, array('local', 'dev'))){

        /* -- GAME DATABASE -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Game Database';
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
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'players',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Commit All',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'commit',
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
                            'condition' => array('uncommitted' => true),
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
                            'text' => 'Commit All',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'commit',
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
                            'condition' => array('uncommitted' => true),
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
                            'text' => 'Commit All',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'commit',
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
                            'condition' => array('uncommitted' => true),
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
                            'text' => 'Commit All',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'commit',
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
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'fields',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Commit All',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'commit',
                                'data-kind' => 'fields',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }

            // Merge in the common game database group options to all envs
            $this_group_options = array_merge($this_group_options, $common_group_kinds_options['game_database']);

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

        /* -- POST-GAME CONTENT -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Post-Game Content';
            $this_group_options = array();

            // Populate the group options array with relevant pages and buttons
            if (in_array('*', $this_adminaccess)
                || in_array('edit-stars', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-stars/', 'text' => 'Edit Rogue Stars'),
                    'desc' => 'schedule and manage rogue star appearances in the post-game',
                    'repo' => array(
                        'name' => 'stars',
                        'data' => array('prefix' => 'star'),
                        'path' => MMRPG_CONFIG_STARS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert All',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'stars',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Commit All',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'commit',
                                'data-kind' => 'stars',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
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

            // Merge in the common game database group options to all envs
            $this_group_options = array_merge($this_group_options, $common_group_kinds_options['post_game_content']);

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

        /* -- WEBSITE PAGES -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Website Pages';
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

            // Merge in the common game database group options to all envs
            $this_group_options = array_merge($this_group_options, $common_group_kinds_options['website_pages']);

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

    }
    /* -- (STAGE/PROD ONLY) -- */
    elseif (in_array(MMRPG_CONFIG_SERVER_ENV, array('stage', 'prod'))){

        /* -- GAME DATABASE -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Game Database';
            $this_group_options = array();

            // ... No options specific to the stage/prod versions....

            // Merge in the common game database group options to all envs
            $this_group_options = array_merge($this_group_options, $common_group_kinds_options['game_database']);

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

        /* -- POST-GAME CONTENT -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Post-Game Content';
            $this_group_options = array();

            // ... No options specific to the stage/prod versions....

            // Merge in the common game database group options to all envs
            $this_group_options = array_merge($this_group_options, $common_group_kinds_options['post_game_content']);

            // Print out the group title and options, assuming there are any available
            echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

        }

        /* -- WEBSITE PAGES -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Website Pages';
            $this_group_options = array();

            // ... No options specific to the stage/prod versions....

            // Merge in the common game database group options to all envs
            $this_group_options = array_merge($this_group_options, $common_group_kinds_options['website_pages']);

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