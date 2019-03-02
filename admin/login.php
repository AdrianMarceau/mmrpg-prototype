<? ob_start(); ?>

    <?

    /* -- Form Setup Actions -- */

    // Define a function for exiting a user edit action
    function exit_login_action($action = ''){
        if (!empty($user_id)){ $location = 'admin.php?action='.$action; }
        else { $location = 'admin.php?action=home'; }
        header('Location: '.$location);
        exit_form_action();
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $post_action =  !empty($_POST['action']) ? $_POST['action'] : '';

    // If the login action was posted, we can process the form
    $form_data = array();
    $form_success = true;
    if ($post_action){

        // Collect the username and password from the request
        $form_data['user_name'] = !empty($_POST['user_name']) && preg_match('/^[-_0-9a-z\.\*]+$/i', $_POST['user_name']) ? trim($_POST['user_name']) : '';
        $form_data['user_password'] = !empty($_POST['user_password']) ? trim($_POST['user_password']) : '';

        // If the required USERNAME field was empty, complete form failure
        if (empty($form_data['user_name'])){
            $form_messages[] = array('error', 'Username was not provided or was invalid');
            $form_success = false;
        }
        // If the required PASSWORD field was empty, complete form failure
        if (empty($form_data['user_password'])){
            $form_messages[] = array('error', 'Password was not provided or was invalid');
            $form_success = false;
        }

        // If there were no errors, we can contiue
        if ($form_success){

            // Create the cleaned user name string for comparing
            $user_name_clean = preg_replace('/[^-a-z0-9]+/i', '', strtolower($form_data['user_name']));

            // Create the encoded password string for comparing
            $user_password_encoded = md5(MMRPG_SETTINGS_PASSWORD_SALT.$form_data['user_password']);

            // Check to ensure the two fields validate in the database
            $login_data = $db->get_array("SELECT
                users.user_id,
                users.user_name,
                users.user_name_public,
                users.user_name_clean,
                roles.role_id,
                roles.role_name,
                roles.role_level
                FROM mmrpg_users AS users
                LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
                WHERE
                users.user_name_clean = '{$user_name_clean}'
                AND users.user_password_encoded = '{$user_password_encoded}'
                ORDER BY
                user_id ASC
                ;");

            // If the login data was empty, no users match that data
            if (empty($login_data)){
                $form_messages[] = array('error', 'The username and password combination were invalid');
                $form_success = false;
            }

            // If there were no errors, we can contiue
            if ($form_success){

                // Check to see what the allowable admin IDs are
                $allowed_admin_ids = defined('MMRPG_CONFIG_ADMIN_LIST') && MMRPG_CONFIG_ADMIN_LIST !== '' ? explode(',', MMRPG_CONFIG_ADMIN_LIST) : array();

                // Check to ensure the user is of the appropriate admin level
                if ($login_data['role_level'] < 4 || !in_array($login_data['user_id'], $allowed_admin_ids)){
                    $form_messages[] = array('error', 'Your account does not have required access permissions');
                    $form_success = false;
                }

                // If there were no errors, we can contiue
                if ($form_success){

                    // Save account credentials to the session
                    $_SESSION['admin_id'] = $login_data['user_id'];
                    $_SESSION['admin_username'] = $login_data['user_name_clean'];
                    $_SESSION['admin_username_display'] = !empty($login_data['user_name_public']) ? $login_data['user_name_public'] : $login_data['user_name'];

                    // Creat a success message and redirect to home
                    $form_messages[] = array('success', 'Thank you for logging in, '.$_SESSION['admin_username_display']);
                    exit_login_action();

                }


            }

        }


    }


    ?>

    <div class="breadcrumb">
        <a href="admin.php">Admin Panel</a>
        &raquo; <a href="admin.php?action=login">Login</a>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform">

        <? if ($this_user['userid'] == MMRPG_SETTINGS_GUEST_ID): ?>

            <h3 class="header">Please Log in</h3>

            <? print_form_messages() ?>

            <div class="login editor">
                <form class="form" method="post">

                    <input type="hidden" name="action" value="login" />
                    <input type="hidden" name="redirect" value="<?= !empty($_SERVER['REQUERT_URI']) ? htmlentities($_SERVER['REQUERT_URI'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />

                    <div class="field">
                        <strong class="label">Username</strong>
                        <input class="textbox" type="text" name="user_name" value="<?= !empty($_GET['user_name']) ? htmlentities(strip_tags(trim($_GET['user_name'])), ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">Password</strong>
                        <input class="textbox" type="password" name="user_password" value="" />
                    </div>

                    <div class="buttons">
                        <input class="button login" type="submit" value="Submit" />
                    </div>

                </form>
            </div>

        <? else: ?>

            <h3 class="header">FATAL ERROR!</h3>

            <?= !empty($this_message_markup) ? '<div class="messages">'.$this_message_markup.'</div>' : '' ?>

            <p>You cannot be logged into the game while using the admin panel!</p>
            <p>Please logout and <a href="file/exit/">exit your game</a> before trying again.</p>

        <? endif; ?>

    </div>

<? $this_page_markup .= ob_get_clean(); ?>