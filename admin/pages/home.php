<? ob_start(); ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="admin/home/">Home</a>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <? print_form_messages() ?>

    <? /*
    <pre>$this_admininfo = <?= print_r($this_admininfo, true) ?></pre>
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
        if (rpg_user::current_user_has_permission('edit-user-accounts')){
            $this_option = array(
                'link' => array('url' => 'admin/edit-users/', 'text' => 'User Accounts', 'bullet' => 'users'),
                'desc' => 'Manage user account information and permissions'
                );
            $this_group_options[] = $this_option;
        }
        if (rpg_user::current_user_has_permission('edit-user-challenges')){
            $this_option = array(
                'link' => array('url' => 'admin/edit-user-challenges/', 'text' => 'User Challenges', 'bullet' => 'users-cog'),
                'desc' => 'Manage user-created challenge missions for the post-game'
                );
            $this_group_options[] = $this_option;
        }
        if (rpg_user::current_user_has_permission('edit-private-messages')){
            $this_option = array(
                'link' => array('url' => 'admin/edit-messages/', 'text' => 'Personal Messages', 'bullet' => 'envelope'),
                'desc' => 'Review and moderate personal message invites between users'
                );
            $this_group_options[] = $this_option;
            $this_option = array(
                'link' => array('url' => 'admin/edit-message-replies/', 'text' => 'Message Replies', 'bullet' => 'stream'), //envelope-square
                'desc' => 'Review and moderate individual replies within personal messages'
                );
            $this_group_options[] = $this_option;
        }

        // Define the group name subtext for this section
        $this_group_name_subtext = '';
        if (MMRPG_CONFIG_SERVER_ENV !== MMRPG_CONFIG_PULL_LIVE_DATA_FROM
            && MMRPG_CONFIG_SERVER_ENV !== 'prod'){
            $this_group_name_subtext = '<p class="env-notice warning">'.
                'Note that changes to personal messages and replies on the '.MMRPG_CONFIG_SERVER_ENV.' build may be overwritten. <br /> '.
                'This section is available in the '.ucfirst(MMRPG_CONFIG_SERVER_ENV).' Admin Panel for testing purposes only.'.
            '</p>';
            }

        // Print out the group title and options, assuming there are any available
        echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options, $this_group_name_subtext);

    }

    /* -- COMMUNITY CONTROLS (LOCAL/DEV/STAGE/PROD) -- */
    if (true){

        // Define the group name and options array
        $this_group_name = 'Community Moderation';
        $this_group_options = array();

        // Populate the group options array with relevant pages and buttons
        if (rpg_user::current_user_has_permission('edit-community-threads')){
            $this_option = array(
                'link' => array('url' => 'admin/edit-threads/', 'text' => 'Community Threads', 'bullet' => 'comment-alt'),
                'desc' => 'Review and moderate community forum threads'
                );
            $this_group_options[] = $this_option;
            $this_option = array(
                'link' => array('url' => 'admin/edit-thread-comments/', 'text' => 'Thread Comments', 'bullet' => 'stream'), //comments
                'desc' => 'Review and moderate individual comments within community threads'
                );
            $this_group_options[] = $this_option;
        }

        // Define the group name subtext for this section
        $this_group_name_subtext = '';
        if (MMRPG_CONFIG_SERVER_ENV !== MMRPG_CONFIG_PULL_LIVE_DATA_FROM
            && MMRPG_CONFIG_SERVER_ENV !== 'prod'){
            $this_group_name_subtext = '<p class="env-notice warning">'.
                'Note that changes to community threads and comments on the '.MMRPG_CONFIG_SERVER_ENV.'-build may be overwritten. <br /> '.
                'This section is available in the '.ucfirst(MMRPG_CONFIG_SERVER_ENV).' Admin Panel for testing purposes only.'.
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
            $common_group_kinds['game_database'] = array('types', 'players', 'robots', 'abilities', 'skills', 'items', 'fields');
            $common_group_kinds['post_game_content'] = array('stars', 'challenges');
            $common_group_kinds['website_pages'] = array('pages');

            // Create an array to hold the actual options for each group
            $common_group_kinds_options = array();

            // Loop through the common group kinds one-by-one and generate options
            foreach ($common_group_kinds AS $kind_token => $allowed_content_types){

                // Create the sub-array to hold all the options for this specific group
                if (!isset($common_group_kinds_options[$kind_token])){ $common_group_kinds_options[$kind_token] = array(); }

                // Populate the group options array with relevant pages and buttons
                if (rpg_user::current_user_has_permission('pull-game-content')){
                    $option_buttons = array();

                    // Loop through the content types index and append permissible buttons
                    foreach ($content_types_index AS $type_key => $type_info){
                        if ($type_info['token'] === 'sql'){ continue; }
                        elseif (!in_array($type_info['xtoken'], $allowed_content_types)){ continue; }
                        // Check to see if current user allowed to edit this content type
                        if (rpg_user::current_user_has_permission('edit-'.$type_info['xtoken'])){
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
                    && rpg_user::current_user_has_permission('push-content')){
                    $option_buttons = array();

                    // Loop through the content types index and append permissible buttons
                    foreach ($content_types_index AS $type_key => $type_info){
                        if ($type_info['token'] === 'sql'){ continue; }
                        elseif (!in_array($type_info['xtoken'], $allowed_content_types)){ continue; }
                        // Check to see if current user allowed to edit this content type
                        if (rpg_user::current_user_has_permission('edit-'.$type_info['xtoken'])){
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

            if (rpg_user::current_user_has_permission('edit-players')){
                $option_name = 'Player Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-players/', 'text' => $option_name, 'bullet' => 'mask'),
                    'desc' => 'Manage in-game player character details and images',
                    'repo' => array(
                        'name' => 'players',
                        'data' => array('prefix' => 'player'),
                        'path' => MMRPG_CONFIG_PLAYERS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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

            if (rpg_user::current_user_has_permission('edit-robot-masters')){
                $option_name = 'Robot Master Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-robot-masters/', 'text' => $option_name, 'bullet' => 'robot'),
                    'desc' => 'Manage in-game robot master details and images',
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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
            if (rpg_user::current_user_has_permission('edit-master-abilities')){
                $option_name = 'Master Ability Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-master-abilities/', 'text' => $option_name, 'bullet' => 'fire-alt', 'class' => 'edit-master-abilities'),
                    'desc' => 'Manage robot master ability details and images',
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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

            if (rpg_user::current_user_has_permission('edit-support-mechas')){
                $option_name = 'Support Mecha Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-support-mechas/', 'text' => $option_name, 'bullet' => 'ghost'),
                    'desc' => 'Manage in-game support mecha details and images',
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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
            if (rpg_user::current_user_has_permission('edit-mecha-abilities')){
                $option_name = 'Mecha Ability Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-mecha-abilities/', 'text' => $option_name, 'bullet' => 'fire', 'class' => 'edit-mecha-abilities'),
                    'desc' => 'Manage support mecha ability details and images',
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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

            if (rpg_user::current_user_has_permission('edit-fortress-bosses')){
                $option_name = 'Fortress Boss Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-fortress-bosses/', 'text' => $option_name, 'bullet' => 'skull'),
                    'desc' => 'Manage in-game fortress boss details and images',
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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
            if (rpg_user::current_user_has_permission('edit-boss-abilities')){
                $option_name = 'Boss Ability Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-boss-abilities/', 'text' => $option_name, 'bullet' => 'meteor', 'class' => 'edit-boss-abilities'),
                    'desc' => 'Manage fortress boss ability details and images',
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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

            if (rpg_user::current_user_has_permission('edit-skills')){
                $option_name = 'Skill Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-skills/', 'text' => $option_name, 'bullet' => 'dna'),
                    'desc' => 'Manage in-game passive skill details and effects',
                    'repo' => array(
                        'name' => 'skills',
                        'data' => array('prefix' => 'skill'),
                        'path' => MMRPG_CONFIG_SKILLS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'revert',
                                'data-kind' => 'skills',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            ),
                        array(
                            'text' => 'Commit All',
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
                            'attributes' => array(
                                'data-button' => 'git',
                                'data-action' => 'commit',
                                'data-kind' => 'skills',
                                'data-token' => 'all',
                                'data-source' => 'github'
                                )
                            )
                        )
                    );
                $this_group_options[] = $this_option;
            }

            if (rpg_user::current_user_has_permission('edit-items')){
                $option_name = 'Item Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-items/', 'text' => $option_name, 'bullet' => 'flask'),
                    'desc' => 'Manage in-game item details and images',
                    'repo' => array(
                        'name' => 'items',
                        'data' => array('prefix' => 'item'),
                        'path' => MMRPG_CONFIG_ITEMS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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

            if (rpg_user::current_user_has_permission('edit-fields')){
                $option_name = 'Field Editor';
                $this_option = array(
                    'link' => array('url' => 'admin/edit-fields/', 'text' => $option_name, 'bullet' => 'map'),
                    'desc' => 'Manage in-game battle field details and images',
                    'repo' => array(
                        'name' => 'fields',
                        'data' => array('prefix' => 'field'),
                        'path' => MMRPG_CONFIG_FIELDS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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

            if (rpg_user::current_user_has_permission('edit-music')
                && defined('MMRPG_CONFIG_CDN_ROOTDIR')
                && file_exists(MMRPG_CONFIG_CDN_ROOTDIR)){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-music/', 'text' => 'Music Editor', 'bullet' => 'music'),
                    'desc' => 'Manage in-game music track details and sound files',
                    );
                $this_group_options[] = $this_option;
            }

            if (
                rpg_user::current_user_has_permission('edit-players')
                || rpg_user::current_user_has_permission('edit-robots')
                || rpg_user::current_user_has_permission('edit-abilities')
                || rpg_user::current_user_has_permission('edit-items')
                || rpg_user::current_user_has_permission('edit-fields')
                ){
                $this_option = array(
                    'link' => array('url' => 'admin/view-sprites/', 'text' => 'Game Sprites', 'target' => '_blank', 'bullet' => 'running'),
                    'desc' => 'Quickly view game sprites all together in bulk',
                    );
                $this_group_options[] = $this_option;
            }

            if (
                rpg_user::current_user_has_permission('edit-players')
                || rpg_user::current_user_has_permission('edit-robots')
                || rpg_user::current_user_has_permission('edit-abilities')
                || rpg_user::current_user_has_permission('edit-items')
                || rpg_user::current_user_has_permission('edit-fields')
                ){
                $this_option = array(
                    'link' => array('url' => 'admin/view-banners/?kind=events&refresh=false', 'text' => 'Event Banners', 'target' => '_blank', 'bullet' => 'rectangle-wide'),
                    'desc' => 'Quickly review all in-game event banners at once',
                    );
                $this_group_options[] = $this_option;
            }

            if (
                rpg_user::current_user_has_permission('edit-players')
                || rpg_user::current_user_has_permission('edit-robots')
                || rpg_user::current_user_has_permission('edit-abilities')
                || rpg_user::current_user_has_permission('edit-items')
                || rpg_user::current_user_has_permission('edit-fields')
                ){
                $this_option = array(
                    'link' => array('url' => 'admin/view-banners/?kind=challenges&refresh=false&max=10&page=1', 'text' => 'Challenge Banners', 'target' => '_blank', 'bullet' => 'rectangle-wide'),
                    'desc' => 'Quickly review challenge mission banners at once',
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
            if (rpg_user::current_user_has_permission('edit-stars')){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-stars/', 'text' => 'Rogue Stars', 'bullet' => 'star'),
                    'desc' => 'Schedule and manage post-game rogue star appearances',
                    'repo' => array(
                        'name' => 'stars',
                        'data' => array('prefix' => 'star'),
                        'path' => MMRPG_CONFIG_STARS_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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
            if (rpg_user::current_user_has_permission('edit-event-challenges')){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-event-challenges/', 'text' => 'Event Challenges', 'bullet' => 'trophy'),
                    'desc' => 'Create or modify post-game event-based challenge missions',
                    'repo' => array(
                        'name' => 'challenges',
                        'data' => array('prefix' => 'challenge'),
                        'path' => MMRPG_CONFIG_CHALLENGES_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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
            if (rpg_user::current_user_has_permission('edit-pages')){
                $this_option = array(
                    'link' => array('url' => 'admin/edit-pages/', 'text' => 'Website Pages', 'bullet' => 'sitemap'),
                    'desc' => 'Manage text and images on various website pages',
                    'repo' => array(
                        'name' => 'pages',
                        'data' => array('prefix' => 'page'),
                        'path' => MMRPG_CONFIG_PAGES_CONTENT_PATH
                        ),
                    'buttons' => array(
                        array(
                            'text' => 'Revert Uncommitted',
                            'condition' => array('uncommitted' => true, 'permissions' => 'revert-changes'),
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
                            'condition' => array('uncommitted' => true, 'permissions' => 'commit-changes'),
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
        if (rpg_user::current_user_has_permission('pull-content-updates')){
            $this_option = array(
                'link' => array('url' => 'admin/scripts/update-game-content.php?return=html', 'text' => 'Pull Content Updates', 'target' => '_blank', 'bullet' => 'cloud-download-alt'),
                'desc' => 'Apply published game content updates to this build'
                );
            $this_group_options[] = $this_option;
        }
        if (rpg_user::current_user_has_permission('pull-core-updates')){
            $this_option = array(
                'link' => array('url' => 'admin/scripts/update-core.php?return=html', 'text' => 'Pull Core Updates', 'target' => '_blank', 'bullet' => 'caret-square-down'),
                'desc' => 'Apply master code updates, typically for lead dev use'
                );
            $this_group_options[] = $this_option;
        }
        if (MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== false
            && MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== MMRPG_CONFIG_SERVER_ENV){
            if (rpg_user::current_user_has_permission('pull-user-data')){
                $this_option = array(
                    'link' => array('url' => 'admin/scripts/pull-live-user-data.php?return=html', 'text' => 'Pull Live User Data', 'target' => '_blank', 'bullet' => 'arrow-alt-circle-down'),
                    'desc' => 'Overwrite '.MMRPG_CONFIG_SERVER_ENV.' user data with '.MMRPG_CONFIG_PULL_LIVE_DATA_FROM.' build data'
                    );
                $this_group_options[] = $this_option;
            }
        }
        if (MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== false
            && MMRPG_CONFIG_PULL_LIVE_DATA_FROM !== MMRPG_CONFIG_SERVER_ENV){
            if (rpg_user::current_user_has_permission('pull-content-updates')
                || rpg_user::current_user_has_permission('push-content-updates')){
                $this_option = array(
                    'link' => array('url' => 'admin/scripts/publish-game-content.php?return=html&kind=all&token=all&source=github', 'text' => 'Push Content Updates', 'target' => '_blank', 'bullet' => 'cloud-upload-alt'),
                    'desc' => 'Published game content updates to the cloud'
                    );
                $this_group_options[] = $this_option;
            }
        }
        if (rpg_user::current_user_has_permission('delete-cached-files')){
            $this_option = array(
                'link' => array('url' => 'admin/delete-cached-files/', 'text' => 'Delete Cached Files', 'target' => '_blank', 'bullet' => 'trash'),
                'desc' => 'Remove cached markup, objects, timeouts, indexes, etc.'
                );
            $this_group_options[] = $this_option;
        }
        if ((MMRPG_CONFIG_SERVER_ENV === 'local'
                || MMRPG_CONFIG_PULL_LIVE_DATA_FROM === false)
            && rpg_user::current_user_has_permission('refresh-leaderboard')){
            $this_option = array(
                'link' => array('url' => 'admin/scripts/refresh-battle-points.php?limit=10&offset=0&return=html', 'text' => 'Refresh Leaderboard', 'target' => '_blank', 'bullet' => 'sync-alt'),
                'desc' => 'Recalculate battle points and update leaderboard for specified users'
                );
            /* $this_option = array(
                'link' => array('url' => 'admin/refresh-leaderboard/incognito=true&amp;force=true', 'text' => 'Refresh Leaderboard', 'target' => '_blank', 'bullet' => 'sync-alt'),
                'desc' => 'recalculate battle points for all idle users and refresh leaderboard'
                ); */
            $this_group_options[] = $this_option;
        }
        if ((MMRPG_CONFIG_SERVER_ENV === 'local'
                || MMRPG_CONFIG_PULL_LIVE_DATA_FROM === false)
            && rpg_user::current_user_has_permission('purge-bogus-users')){
            $this_option = array(
                'link' => array('url' => 'admin/purge-bogus-users/limit=10', 'text' => 'Purge Bogus Users', 'target' => '_blank', 'bullet' => 'bomb'),
                'desc' => 'Remove user accounts with no progress or login history'
                );
            $this_group_options[] = $this_option;
        }
        if (rpg_user::current_user_has_permission('watch-error-logs')){
            $this_option = array(
                'link' => array('url' => 'admin/watch-error-log/', 'text' => 'Watch Error Log', 'target' => '_blank', 'bullet' => 'bug'),
                'desc' => 'Monitor error log for development, debugging, and testing purposes'
                );
            $this_group_options[] = $this_option;
        }
        if (rpg_user::current_user_has_permission('watch-error-logs')
            || rpg_user::current_user_has_permission('check-git-status')){
            $this_option = array(
                'link' => array('url' => 'content/git-status.php', 'text' => 'Check Git Status', 'target' => '_blank', 'bullet' => 'code-branch'),
                'desc' => 'Run a git permission check and status diagnostic on the server'
                );
            $this_group_options[] = $this_option;
        }

        // Print out the group title and options, assuming there are any available
        echo cms_admin::print_admin_home_group_options($this_group_name, $this_group_options);

    }

    ?>

<? $this_page_markup .= ob_get_clean(); ?>