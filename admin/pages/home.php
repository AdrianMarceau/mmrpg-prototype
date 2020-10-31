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

    // Pre-scan all content directories as we'll need them all eventually
    // (Not applicable on stage or live home as git trackers don't exist there)
    if (MMRPG_CONFIG_SERVER_ENV === 'local' || MMRPG_CONFIG_SERVER_ENV === 'dev'){
        cms_admin::git_scan_content_directories();
    }

    /* -- USER CONTROLS (LOCAL/DEV/STAGE/PROD) -- */
    if (true){

        // Define the group name and options array
        $this_group_name = 'User Controls';
        $this_group_options = array();

        // Populate the group options array with relevant pages and buttons
        if (in_array('*', $this_adminaccess)
            || in_array('edit-users', $this_adminaccess)){
            $this_option = array(
                'link' => array('url' => 'admin/edit-users/', 'text' => 'Moderate Users', 'bullet' => 'users'),
                'desc' => 'update or modify user account info and permissions'
                );
            $this_group_options[] = $this_option;
        }
        if (in_array('*', $this_adminaccess)
            || in_array('edit-content', $this_adminaccess)
            || in_array('edit-challenges', $this_adminaccess)
            || in_array('edit-user-challenges', $this_adminaccess)){
            $this_option = array(
                'link' => array('url' => 'admin/edit-user-challenges/', 'text' => 'Moderate User Challenges', 'bullet' => 'users-cog'),
                'desc' => 'update or modify user-created challenge missions for the post-game'
                );
            $this_group_options[] = $this_option;
        }

        // Define the group name subtext for this section
        $this_group_name_subtext = '';
        if (MMRPG_CONFIG_SERVER_ENV !== MMRPG_CONFIG_PULL_LIVE_DATA_FROM){
            $this_group_name_subtext = '<p class="env-notice warning">'.
                'Changes made to user accounts and content on the '.MMRPG_CONFIG_SERVER_ENV.'-build may be overwritten at any time. <br /> '.
                'This section is available in the '.ucfirst(MMRPG_CONFIG_SERVER_ENV).' Admin Panel for testing purposes only, so please be mindful.'.
            '</p>';
            }

        // Print out the group title and options, assuming there are any available
        echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options, $this_group_name_subtext);

    }

    /* -- (LOCAL/DEV ONLY) -- */
    if (in_array(MMRPG_CONFIG_SERVER_ENV, array('local', 'dev'))){

        /* -- GITHUB TOOLS (common to LOCAL/DEV) -- */
        if (true){

            // Pull in the content types repo so we don't have to redeclare stuff
            require(MMRPG_CONFIG_ROOTDIR.'content/index.php');

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
                if ((in_array('*', $this_adminaccess)
                    || in_array('pull-content', $this_adminaccess)
                    || in_array('pull-from-github', $this_adminaccess))){
                    $option_buttons = array();

                    // Loop through the content types index and append permissible buttons
                    foreach ($content_types_index AS $type_key => $type_info){
                        if ($type_info['token'] === 'sql'){ continue; }
                        elseif (!in_array($type_info['xtoken'], $allowed_content_types)){ continue; }
                        // Check to see if current user allowed to edit this content type
                        if (in_array('*', $this_adminaccess)
                            || in_array('edit-content', $this_adminaccess)
                            || in_array('edit-'.$type_info['xtoken'], $this_adminaccess)){
                            $repo_base_path = MMRPG_CONFIG_CONTENT_PATH.rtrim($type_info['content_path'], '/').'/';
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

                    // Only add the option if buttons were actually generated
                    if (!empty($option_buttons)){

                        // Generate the pull-from-github option with any relevant buttons
                        $this_option = array(
                            'link' => array('text' => 'Pull from GitHub', 'icon' => 'cloud-download-alt'),
                            'desc' => 'pull committed changes from github repos and update',
                            'buttons' => $option_buttons
                            );

                        // Add the option to the appropriate group for later
                        $common_group_kinds_options[$kind_token][] = $this_option;

                    }

                }

                // Populate the group options array with relevant pages and buttons
                if ((MMRPG_CONFIG_SERVER_ENV === 'local' || MMRPG_CONFIG_SERVER_ENV === 'dev')
                    && (in_array('*', $this_adminaccess)
                        || in_array('push-content', $this_adminaccess)
                        || in_array('publish-to-github', $this_adminaccess))){
                    $option_buttons = array();

                    // Loop through the content types index and append permissible buttons
                    foreach ($content_types_index AS $type_key => $type_info){
                        if ($type_info['token'] === 'sql'){ continue; }
                        elseif (!in_array($type_info['xtoken'], $allowed_content_types)){ continue; }
                        // Check to see if current user allowed to edit this content type
                        if (in_array('*', $this_adminaccess)
                            || in_array('edit-content', $this_adminaccess)
                            || in_array('edit-'.$type_info['xtoken'], $this_adminaccess)){
                            // Collect git details for the repo to see if button necessary
                            $repo_base_path = MMRPG_CONFIG_CONTENT_PATH.rtrim($type_info['content_path'], '/').'/';
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

                    // Only add the option if buttons were actually generated
                    if (!empty($option_buttons)){

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

        }

        /* -- GAME DATABASE -- */
        if (true){

            // Define the group name and options array
            $this_group_name = 'Game Database';
            $this_group_options = array();

            // Populate the group options array with relevant pages and buttons

            if (in_array('*', $this_adminaccess)
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-players', $this_adminaccess)){
                $option_name = 'Edit Players';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-players/', 'text' => $option_name, 'bullet' => 'mask'),
                    'desc' => 'edit the details and images of the in-game player characters',
                    'repo' => array(
                        'name' => 'players',
                        'data' => array('prefix' => 'player'),
                        'path' => MMRPG_CONFIG_PLAYERS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-robots', $this_adminaccess)
                || in_array('edit-robot-master', $this_adminaccess)){
                $option_name = 'Edit Robot Masters';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-robot-masters/', 'text' => $option_name, 'bullet' => 'robot'),
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
                            'text' => 'Revert Uncommitted',
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-robots', $this_adminaccess)
                || in_array('edit-support-mechas', $this_adminaccess)){
                $option_name = 'Edit Support Mechas';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-support-mechas/', 'text' => $option_name, 'bullet' => 'ghost'),
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
                            'text' => 'Revert Uncommitted',
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-robots', $this_adminaccess)
                || in_array('edit-fortress-bosses', $this_adminaccess)){
                $option_name = 'Edit Fortress Bosses';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-fortress-bosses/', 'text' => $option_name, 'bullet' => 'skull'),
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
                            'text' => 'Revert Uncommitted',
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-abilities', $this_adminaccess)
                || in_array('edit-master-abilities', $this_adminaccess)){
                $option_name = 'Edit Master Abilities';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-master-abilities/', 'text' => $option_name, 'bullet' => 'fire-alt'),
                    'desc' => 'edit the details and images of the abilities used by robot masters',
                    'repo' => array(
                        'name' => 'abilities',
                        'data' => array('prefix' => 'ability'),
                        'path' => MMRPG_CONFIG_ABILITIES_CONTENT_PATH,
                        'filter' => array(
                            'table' => 'mmrpg_index_abilities',
                            'token' => 'ability_token',
                            'extra' => array('ability_class' => 'master')
                            )
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'abilities',
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
                                'data-kind' => 'abilities',
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-abilities', $this_adminaccess)
                || in_array('edit-mecha-abilities', $this_adminaccess)){
                $option_name = 'Edit Mecha Abilities';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-mecha-abilities/', 'text' => $option_name, 'bullet' => 'fire'),
                    'desc' => 'edit the details and images of abilities used by support mechas',
                    'repo' => array(
                        'name' => 'abilities',
                        'data' => array('prefix' => 'ability'),
                        'path' => MMRPG_CONFIG_ABILITIES_CONTENT_PATH,
                        'filter' => array(
                            'table' => 'mmrpg_index_abilities',
                            'token' => 'ability_token',
                            'extra' => array('ability_class' => 'mecha')
                            )
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'abilities',
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
                                'data-kind' => 'abilities',
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-abilities', $this_adminaccess)
                || in_array('edit-boss-abilities', $this_adminaccess)){
                $option_name = 'Edit Boss Abilities';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-boss-abilities/', 'text' => $option_name, 'bullet' => 'meteor'),
                    'desc' => 'edit the details and images of abilities used by fortress bosses',
                    'repo' => array(
                        'name' => 'abilities',
                        'data' => array('prefix' => 'ability'),
                        'path' => MMRPG_CONFIG_ABILITIES_CONTENT_PATH,
                        'filter' => array(
                            'table' => 'mmrpg_index_abilities',
                            'token' => 'ability_token',
                            'extra' => array('ability_class' => 'boss')
                            )
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'abilities',
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
                                'data-kind' => 'abilities',
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-items', $this_adminaccess)){
                $option_name = 'Edit Items';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-items/', 'text' => $option_name, 'bullet' => 'flask'),
                    'desc' => 'edit the details and images of the in-game items',
                    'repo' => array(
                        'name' => 'items',
                        'data' => array('prefix' => 'item'),
                        'path' => MMRPG_CONFIG_ITEMS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'items',
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
                                'data-kind' => 'items',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }

            if (in_array('*', $this_adminaccess)
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-fields', $this_adminaccess)){
                $option_name = 'Edit Fields';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-fields/', 'text' => $option_name, 'bullet' => 'map'),
                    'desc' => 'edit the details and images of the in-game battle fields',
                    'repo' => array(
                        'name' => 'fields',
                        'data' => array('prefix' => 'field'),
                        'path' => MMRPG_CONFIG_FIELDS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-stars', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-stars/', 'text' => 'Edit Rogue Stars', 'bullet' => 'star'),
                    'desc' => 'schedule and manage rogue star appearances in the post-game',
                    'repo' => array(
                        'name' => 'stars',
                        'data' => array('prefix' => 'star'),
                        'path' => MMRPG_CONFIG_STARS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-challenges', $this_adminaccess)
                || in_array('edit-event-challenges', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-event-challenges/', 'text' => 'Edit Event Challenges', 'bullet' => 'trophy'),
                    'desc' => 'create or modify event-based challenge missions for the post-game',
                    'repo' => array(
                        'name' => 'challenges',
                        'data' => array('prefix' => 'challenge'),
                        'path' => MMRPG_CONFIG_CHALLENGES_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'challenges',
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
                                'data-kind' => 'challenges',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
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
                || in_array('edit-content', $this_adminaccess)
                || in_array('edit-pages', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-pages/', 'text' => 'Edit Website Pages', 'bullet' => 'sitemap'),
                    'desc' => 'edit the text and images on various website pages',
                    'repo' => array(
                        'name' => 'pages',
                        'data' => array('prefix' => 'page'),
                        'path' => MMRPG_CONFIG_PAGES_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'pages',
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
                                'data-kind' => 'pages',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
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

        /* --
        Nothing to show here at the moment
        -- */

    }

    /* -- MISC TOOLS (LOCAL/DEV/STAGE/PROD) -- */
    if (true){

        // Define the group name and options array
        $this_group_name = 'Misc Tools';
        $this_group_options = array();

        // Populate the group options array with relevant pages and buttons
        if (in_array('*', $this_adminaccess)
            || in_array('pull-content', $this_adminaccess)){
            $this_option = array(
                'link' => array('url' => 'admin/scripts/pull-all-game-content.php?return=html', 'text' => 'Pull Content Updates', 'target' => '_blank', 'bullet' => 'cloud-download-alt'),
                'desc' => 'pull published updates to game content and apply to this build'
                );
            $this_group_options[] = $this_option;
        }
        if (MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== false
            && MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== MMRPG_CONFIG_SERVER_ENV){
            if (in_array('*', $this_adminaccess)
                || in_array('edit-users', $this_adminaccess)){
                $this_option = array(
                    'link' => array('url' => 'admin/scripts/pull-live-user-data.php?return=html', 'text' => 'Pull Live User Data', 'target' => '_blank', 'bullet' => 'arrow-alt-circle-down'),
                    'desc' => 'pull current users and their data from live build to overwrite existing'
                    );
                $this_group_options[] = $this_option;
            }
        }
        if (in_array('*', $this_adminaccess)
            || in_array('pull-core', $this_adminaccess)){
            $this_option = array(
                'link' => array('url' => 'admin/scripts/update-core.php?return=html', 'text' => 'Pull Core Updates', 'target' => '_blank', 'bullet' => 'caret-square-down'),
                'desc' => 'pull updates to the master code, typically used only by the lead dev'
                );
            $this_group_options[] = $this_option;
        }
        if (in_array('*', $this_adminaccess)
            || in_array('delete-cached-files', $this_adminaccess)){
            $this_option = array(
                'link' => array('url' => 'admin/delete-cached-files/', 'text' => 'Delete Cached Files', 'target' => '_blank', 'bullet' => 'trash'),
                'desc' => 'delete cached markup, objects, timeouts, indexes, and more'
                );
            $this_group_options[] = $this_option;
        }
        if ((MMRPG_CONFIG_SERVER_ENV === 'local'
                || MMRPG_CONFIG_PULL_LIVE_DATA_FROM === false)
            && (in_array('*', $this_adminaccess)
                || in_array('refresh-leaderboard', $this_adminaccess))){
            $this_option = array(
                'link' => array('url' => 'admin/refresh-leaderboard/incognito=true&amp;force=true', 'text' => 'Refresh Leaderboard', 'target' => '_blank', 'bullet' => 'sync-alt'),
                'desc' => 'recalculate battle points for all idle users and refresh leaderboard'
                );
            $this_group_options[] = $this_option;
        }
        if ((MMRPG_CONFIG_SERVER_ENV === 'local'
                || MMRPG_CONFIG_PULL_LIVE_DATA_FROM === false)
            && (in_array('*', $this_adminaccess)
                || in_array('purge-bogus-users', $this_adminaccess))){
            $this_option = array(
                'link' => array('url' => 'admin/purge-bogus-users/limit=10', 'text' => 'Purge Bogus Users', 'target' => '_blank', 'bullet' => 'bomb'),
                'desc' => 'purge user accounts with zero progress and no login history'
                );
            $this_group_options[] = $this_option;
        }
        if (in_array('*', $this_adminaccess)
            || in_array('view-logs', $this_adminaccess)){
            $this_option = array(
                'link' => array('url' => 'admin/watch-error-log/', 'text' => 'Watch Error Log', 'target' => '_blank', 'bullet' => 'bug'),
                'desc' => 'watch the error log to help with dev, debug, and testing'
                );
            $this_group_options[] = $this_option;
        }

        // Print out the group title and options, assuming there are any available
        echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

    }

    ?>

<? $this_page_markup .= ob_get_clean(); ?>