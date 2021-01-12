<?

// Collect an index of user roles for display
$temp_role_fields = rpg_user_role::get_index_fields(true);
$this_roles_index = $db->get_array_list("SELECT {$temp_role_fields} FROM mmrpg_roles ORDER BY role_id ASC", 'role_id');
$this_fields_index = rpg_field::get_index();

// Collect the current request type if set
$this_action = $this_current_sub;
$allow_fadein = true;
// Define the allowable actions in this script
$allowed_actions = array('save', 'new', 'load', 'unload', 'reset', 'exit', 'game', 'profile');
// If this action is not allowed, kill the script
if (empty($this_action)){ die('An action must be defined!'); }
elseif (!in_array($this_action, $allowed_actions)){ die(ucfirst($this_action).' is not an allowed action!'); }
else { $allow_fadein = false; }

// Define the variables to hold HTML markup
$html_header_title = '';
$html_header_text = '';
$html_form_fields = '';
$html_form_buttons = '';
$html_form_messages = '';
$html_form_verified = true;

// Define the serial ordering index
$temp_serial_ordering = array(
    'DLN', // Dr. Light Number
    'DWN', // Dr. Wily Number
    'DCN', // Dr. Cossack Number
    'DLM'  // Dr. Light Mecha
    );

// Create the has updated flag and default to false
$file_has_updated = false;

// If the PROFILE action was requested
while ($this_action == 'profile'){

    // -- GENERATE GENDER OPTIONS -- //
    if (true){
        $allowed_gender_options = array();
        $html_gender_options = array();
            $html_gender_options[] = '<option value="male">Male</option>';
            $html_gender_options[] = '<option value="female">Female</option>';
            $html_gender_options[] = '<option value="other">Other</option>';
            $html_gender_options[] = '<option value="none">None</option>';
            $allowed_gender_options[] = 'male';
            $allowed_gender_options[] = 'female';
            $allowed_gender_options[] = 'other';
            $allowed_gender_options[] = 'none';
        $temp_select_gender_options = str_replace('value="'.$_SESSION['GAME']['USER']['gender'].'"', 'value="'.$_SESSION['GAME']['USER']['gender'].'" selected="selected"', implode('', $html_gender_options));
    }

    // -- COLLECT AVATAR OPTIONS -- //
    $temp_avatar_select_options = mmrpg_prototype_get_profile_avatar_options($this_userinfo, $allowed_avatar_options);
    $temp_avatar_select_options = str_replace('value="'.$_SESSION['GAME']['USER']['imagepath'].'"', 'value="'.$_SESSION['GAME']['USER']['imagepath'].'" selected="selected"', $temp_avatar_select_options);

    // -- COLLECT COLOUR OPTIONS -- //
    $temp_colour_select_options = mmrpg_prototype_get_profile_colour_options($this_userinfo, $allowed_colour_options);
    $temp_colour_select_options = str_replace('value="'.$_SESSION['GAME']['USER']['colourtoken'].'"', 'value="'.$_SESSION['GAME']['USER']['colourtoken'].'" selected="selected"', $temp_colour_select_options);

    // -- COLLECT BACKGROUND OPTIONS -- //
    $temp_select_background_options = mmrpg_prototype_get_profile_background_options($this_userinfo, $allowed_background_options);
    $temp_select_background_options = str_replace('value="'.$_SESSION['GAME']['USER']['backgroundpath'].'"', 'value="'.$_SESSION['GAME']['USER']['backgroundpath'].'" selected="selected"', $temp_select_background_options);

    // If the form has already been submit, process input
    while (!empty($_POST['submit']) && $_POST['submit'] == 'true'){

        // If a new password was provided, collect the new it and update the same file
        if (!empty($_POST['password_new'])){

            // Trim any whitespace from the passwords
            $_POST['password_new'] = trim($_POST['password_new']);

            // If the password was too short, error out
            if (strlen($_POST['password_new']) < 6){

                // Update the form messages markup text
                $html_form_messages .= '<span class="error">(!) The new password was too short - must be at least 6 characters.</span>';
                $_POST['password_new'] = '';

            }
            // Else if the password was too long, error out
            elseif (strlen($_POST['password_new']) > 32){

                // Update the form messages markup text
                $html_form_messages .= '<span class="error">(!) The new password was too long - must not be more than 32 characters.</span>';
                $_POST['password_new'] = '';

            }
            // Otherwise update the user's password in the database directory
            else {

                // Update this user's accont password in the db
                $html_form_messages .= '<span class="success">(!) Your account password has been changed.</span>';
                $temp_password = $_POST['password_new'];
                $temp_password_encoded = md5(MMRPG_SETTINGS_PASSWORD_SALT.$temp_password);
                $db->update('mmrpg_users', array(
                    'user_password_encoded' => $temp_password_encoded
                    ), "user_id = {$this_userid}");
                $_POST['password_new'] = '';

            }

        }

        // Else, collect the current and new password and process them
        if (true){

            // Collect any profile details
            $user_displayname = !empty($_POST['displayname']) ? preg_replace('/[^-_a-z0-9\.\s]+/i', '', trim($_POST['displayname'])) : '';
            $user_emailaddress = !empty($_POST['emailaddress']) ? preg_replace('/[^-_a-z0-9\.\+@]+/i', '', trim($_POST['emailaddress'])) : '';

            if (!empty($this_userinfo['user_flag_postpublic'])){
                $user_websiteaddress = !empty($_POST['websiteaddress']) ? 'http://'.preg_replace('/^https?:\/\//i', '', trim($_POST['websiteaddress'])) : '';
                $user_profiletext = !empty($_POST['profiletext']) ? strip_tags(trim($_POST['profiletext'])) : '';
                $user_creditstext = !empty($_POST['creditstext']) ? strip_tags(trim($_POST['creditstext'])) : '';
                $user_creditsline = !empty($_POST['creditsline']) ? strip_tags(trim($_POST['creditsline'])) : '';
            }

            // Only process omega fields if function unlocked
            if (mmrpg_prototype_item_unlocked('omega-seed')){
                $user_omega_seed = !empty($_POST['omega_seed']) ? trim(preg_replace('/[^-_0-9a-z\.\s\,\?\!]+/i', '', $_POST['omega_seed'])) : '';
                $user_omega_seed = preg_replace('/\s+/', ' ', $user_omega_seed);
                if (!empty($user_omega_seed) && strlen($user_omega_seed) < 6){ $user_omega_seed = ''; }
                elseif (!empty($user_omega_seed) && strlen($user_omega_seed) > 32){ $user_omega_seed = ''; }
            }

            // Check if the password has changed at all
            if (true){

                // Backup the current game's filename for deletion purposes
                $backup_user = $_SESSION['GAME']['USER'];

                // Collect and clean the post arguments
                $post_imagepath = !empty($_POST['imagepath']) && preg_match('/^(players|robots)\/([-_a-z0-9]+)\/([0-9]+)$/i', $_POST['imagepath']) ? $_POST['imagepath'] : 'robots/mega-man/40';
                $post_backgroundpath = !empty($_POST['backgroundpath']) && preg_match('/^fields\/([-_a-z0-9]+)$/i', $_POST['backgroundpath']) ? $_POST['backgroundpath'] : 'fields/'.rpg_player::get_intro_field('dr-light');
                $post_colourtoken = !empty($_POST['colourtoken']) && preg_match('/^([-_a-z0-9]+)$/i', $_POST['colourtoken']) ? $_POST['colourtoken'] : '';
                $post_gender = !empty($_POST['gender']) && preg_match('/^(male|female|none|other)$/i', $_POST['gender']) ? $_POST['gender'] : 'other';

                if (!in_array($post_imagepath, $allowed_avatar_options)){ $post_imagepath = $allowed_avatar_options[0]; }
                if (!in_array($post_backgroundpath, $allowed_background_options)){ $post_backgroundpath = $allowed_background_options[0]; }
                if (!in_array($post_colourtoken, $allowed_colour_options)){ $post_colourtoken = $allowed_colour_options[0]; }
                if (!in_array($post_gender, $allowed_gender_options)){ $post_gender = $allowed_gender_options[0]; }

                // Update the current game's user and file info using the new password
                $_SESSION['GAME']['USER']['displayname'] = $user_displayname;
                if (!empty($user_emailaddress)
                    && preg_match('/^([^@]+)@([-_a-z0-9\.]+)\.([a-z0-9]+)/i', $user_emailaddress)){
                    $_SESSION['GAME']['USER']['emailaddress'] = $user_emailaddress;
                }
                if (!empty($this_userinfo['user_flag_postpublic'])){
                    $_SESSION['GAME']['USER']['websiteaddress'] = $user_websiteaddress;
                    $_SESSION['GAME']['USER']['profiletext'] = $user_profiletext;
                    $_SESSION['GAME']['USER']['creditstext'] = $user_creditstext;
                    $_SESSION['GAME']['USER']['creditsline'] = $user_creditsline;
                }
                $_SESSION['GAME']['USER']['imagepath'] = $post_imagepath;
                $_SESSION['GAME']['USER']['backgroundpath'] = $post_backgroundpath;
                $_SESSION['GAME']['USER']['colourtoken'] = $post_colourtoken;
                $_SESSION['GAME']['USER']['gender'] = $post_gender;
                if (!empty($user_omega_seed)){
                    $_SESSION['GAME']['USER']['omega'] = md5(MMRPG_SETTINGS_OMEGA_SEED.$user_omega_seed);
                }

            }

        }

        // Save the current game session into the file
        mmrpg_save_game_session();
        $db_users_fields = rpg_user::get_index_fields(true, 'users');
        $db_users_roles_fields = rpg_user_role::get_index_fields(true, 'roles');
        $this_userinfo = $db->get_array("SELECT
            {$db_users_fields},
            {$db_users_roles_fields}
            FROM mmrpg_users AS users
            LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
            WHERE users.user_id = '{$this_userid}'
            LIMIT 1
            ;");
        $_SESSION['GAME']['USER']['userinfo'] = $this_userinfo;
        $_SESSION['GAME']['USER']['userinfo']['user_password_encoded'] = '';

        // Update the has updated flag variable
        $file_has_updated = true;

        // Break from the POST loop
        break;

    }

    // Start the output buffer to collect form fields
    ob_start();
    if (!$file_has_updated){

        ?>

        <div class="field field_username">
            <label class="label label_username">Username : <span style="color: red;">*</span></label>
            <input class="text text_username" type="text" name="username" value="<?= htmlentities(trim($_SESSION['GAME']['USER']['username']), ENT_QUOTES, 'UTF-8', true) ?>" disabled="disabled" />
        </div>

        <div class="field field_password_new">
            <label class="label label_password" style="width: auto; ">Change Password : <span style="font-size: 10px; padding-left: 6px; position: relative; bottom: 1px; color: #CACACA;">(6 - 32 chars)</span></label>
            <input class="text text_password" type="text" name="password_new" value="" maxlength="32" />
        </div>

        <? if (!empty($this_userinfo['user_flag_postpublic'])){ ?>
            <div class="field field_displayname">
                <label class="label label_displayname">Display Name :</label>
                <input class="text text_displayname" type="text" name="displayname" maxlength="18" value="<?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['displayname']) ? $_SESSION['GAME']['USER']['displayname'] : ''), ENT_QUOTES, 'UTF-8', true) ?>" />
            </div>
        <? } else { ?>
            <input type="hidden" name="displayname" maxlength="18" value="<?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['displayname']) ? $_SESSION['GAME']['USER']['displayname'] : ''), ENT_QUOTES, 'UTF-8', true) ?>" />
        <? } ?>

        <div class="field field_emailaddress">
            <label class="label label_emailaddress">Email Address : <span style="color: red;">*</span></label>
            <input class="text text_emailaddress" type="text" name="emailaddress" maxlength="128" value="<?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['emailaddress']) ? $_SESSION['GAME']['USER']['emailaddress'] : ''), ENT_QUOTES, 'UTF-8', true) ?>" required="required" />
        </div>

        <? if (!empty($this_userinfo['user_flag_postpublic'])){ ?>
            <div class="field field_websiteaddress">
                <label class="label label_websiteaddress">Website Address :</label>
                <input class="text text_websiteaddress" type="text" name="websiteaddress" maxlength="128" value="<?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['websiteaddress']) ? $_SESSION['GAME']['USER']['websiteaddress'] : ''), ENT_QUOTES, 'UTF-8', true) ?>" />
            </div>
        <? } else { ?>
            <input type="hidden" name="websiteaddress" maxlength="128" value="<?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['websiteaddress']) ? $_SESSION['GAME']['USER']['websiteaddress'] : ''), ENT_QUOTES, 'UTF-8', true) ?>" />
        <? } ?>

        <div class="field field_gender">
            <label class="label label_gender">Player Gender :</label>
            <select class="select select_gender" name="gender"><?= $temp_select_gender_options ?></select>
        </div>

        <div class="field field_roleid">
            <label class="label label_roleid">Member Type :</label>
            <input class="text text_roleid" type="text" name="role_id" disabled="disabled" value="<?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['roleid']) ? $this_roles_index[$_SESSION['GAME']['USER']['roleid']]['role_name'] : 'Unknown'), ENT_QUOTES, 'UTF-8', true) ?>" />
        </div>

        <div class="field field_colourtoken">
            <label class="label label_colourtoken">Profile Colour :</label>
            <select class="select select_colourtoken" name="colourtoken"><?= $temp_colour_select_options ?></select>
        </div>

        <div class="field field_imagepath">
            <label class="label label_imagepath">Robot Avatar :</label>
            <select class="select select_imagepath" name="imagepath"><?= $temp_avatar_select_options ?></select>
        </div>

        <div class="field field_backgroundpath">
            <label class="label label_backgroundpath">Field Background :</label>
            <select class="select select_backgroundpath" name="backgroundpath"><?= $temp_select_background_options ?></select>
        </div>

        <?
        // Only show omega sequence fields if unlocked by the player
        if (mmrpg_prototype_item_unlocked('omega-seed')){
            ?>
            <div class="field field_omega">
                <label class="label label_omega">Omega Sequence :</label>
                <input class="text text_omega" type="text" name="omega" maxlength="32" value="<?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['omega']) ? $_SESSION['GAME']['USER']['omega'] : ''), ENT_QUOTES, 'UTF-8', true) ?>" disabled="disabled" />
            </div>

            <div class="field field_omega_seed">
                <label class="label label_omega_seed">Regenerate Sequence :</label>
                <input class="text text_omega_seed" type="text" name="omega_seed" maxlength="32" value="" />
            </div>
            <?
        }
        ?>

        <?
        // IF CONTRIBUTOR OR ADMIN
        if (in_array($_SESSION['GAME']['USER']['roleid'], array(1, 6, 2, 7))){
            $member_role_name = trim(!empty($_SESSION['GAME']['USER']['roleid']) ? $this_roles_index[$_SESSION['GAME']['USER']['roleid']]['role_name'] : 'Unknown');
            ?>
            <div class="field field_creditsline full">
                <label class="label label_creditsline"><?= $member_role_name.' Credits :' ?></label>
                <input class="text text_creditsline" name="creditsline" value="<?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['creditsline']) ? $_SESSION['GAME']['USER']['creditsline'] : ''), ENT_QUOTES, 'UTF-8', true) ?>" />
            </div>

            <div class="field field_creditstext full">
                <label class="label label_creditstext"><?= $member_role_name.' Description : '?></label>
                <textarea class="textarea textarea_creditstext" style="height: 150px; " name="creditstext"><?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['creditstext']) ? $_SESSION['GAME']['USER']['creditstext'] : ''), ENT_QUOTES, 'UTF-8', true) ?></textarea>
            </div>
            <?
        }
        ?>

        <? if (!empty($this_userinfo['user_flag_postpublic'])){ ?>
            <div class="field field_profiletext full">
                <label class="label label_profiletext">Profile Description :</label>
                <textarea class="textarea textarea_profiletext" style="height: 250px; " name="profiletext"><?= htmlentities(trim(!empty($_SESSION['GAME']['USER']['profiletext']) ? $_SESSION['GAME']['USER']['profiletext'] : ''), ENT_QUOTES, 'UTF-8', true) ?></textarea>
                <?= mmrpg_formatting_help() ?>
            </div>
        <? } ?>

        <?

    }
    $html_form_fields = ob_get_clean();

    // Start the output buffer to collect form buttons
    ob_start();
    if (!$file_has_updated){

        // Update the form markup buttons
        echo '<input class="button button_submit" type="submit" value="Save Changes" />';
        //echo '<input class="button button_reset" type="button" value="Reset Game" onclick="javascript:parent.window.mmrpg_trigger_reset();" />';
        //echo '<input class="button button_cancel" type="button" value="Cancel" onclick="javascript:parent.window.location.href=\'prototype.php\';" />';

    }
    $html_form_buttons = ob_get_clean();

    // If the file has been updated, update the data
    if ($file_has_updated){

        // Update the form messages markup text
        $html_form_messages .= '<span class="success">(!) Thank you.  Your profile changes have been saved.<br />Save Date : '.date('Y/m/d @ H:i:s').'.</span>';
        // Clear the form fields markup
        //$html_form_fields = '<script type="text/javascript"> setTimeout(function(){ window.location.href=\''.MMRPG_CONFIG_ROOTURL.'\'; }, 1000); </script>';
        // Update the form markup buttons
        //$html_form_buttons = '<input class="button button_continue" type="button" value="Continue" onclick="javascript:window.location.href=\''.MMRPG_CONFIG_ROOTURL.'\';" />';

        // Save the messages to the session and refresh
        $_SESSION['mmrpg_form_messages'] = $html_form_messages;
        header('Location: '.MMRPG_CONFIG_ROOTURL.'file/profile/');
        exit();

    }

    // Break from the PROFILE loop
    break;
}
// Else, if the NEW action was requested
while ($this_action == 'new'){

    // If the form has already been submit, process input
    while (!empty($_POST['submit']) && $_POST['submit'] == 'true'){

        // If both the username or password are empty, produce an error
        if (empty($_REQUEST['username']) && empty($_REQUEST['emailaddress']) && empty($_REQUEST['dateofbirth']) && empty($_REQUEST['password'])){
            $html_form_messages .= '<span class="error">(!) A username, email address, date of birth, and password must be provided.</span>';
            break;
        }
        // Otherwise, if at least one of them was provided, validate
        else {
            // Trim spaces off the end and beginning
            $_REQUEST['username'] = trim($_REQUEST['username']);
            $temp_username_clean = preg_replace('/[^-a-z0-9]+/i', '', strtolower($_REQUEST['username']));
            $temp_username_exists = $db->get_value("SELECT user_id FROM mmrpg_users WHERE user_name_clean = '{$temp_username_clean}';", 'user_id');
            $_REQUEST['password'] = trim($_REQUEST['password']);

            // Define the is verfied and default to true
            $html_form_verified = true;
            // Ensure the username is valid
            if (empty($_REQUEST['username'])){
                $html_form_messages .= '<span class="error">(!) A username was not provided.</span>';
                $html_form_verified = false;
            } elseif ($_REQUEST['username'] == 'demo' || !empty($temp_username_exists)){
                $html_form_messages .= '<span class="error">(!) The requested username is already in use - please select another.</span>';
                $html_form_verified = false;
            } elseif (strlen($_REQUEST['username']) < 6 || strlen($_REQUEST['username']) > 18){
                $html_form_messages .= '<span class="error">(!) The username must be between 6 and 18 characters.</span>';
                $html_form_verified = false;
            } elseif (!preg_match('/^[-_a-z0-9\.]+$/i', $_REQUEST['username'])){
                $html_form_messages .= '<span class="error">(!) The username must only contain letters and numbers.</span>';
                $html_form_verified = false;
            }
            // Ensure the email is valid
            if (empty($_REQUEST['emailaddress'])){
                $html_form_messages .= '<span class="error">(!) The email address was not provided.</span>';
                $html_form_verified = false;
            } elseif (!preg_match('/^([^@]+)@([-a-z0-9]+)\.(.*)$/i', $_REQUEST['emailaddress'])){
                $html_form_messages .= '<span class="error">(!) The email address provided was not valid.</span>';
                $html_form_verified = false;
            } elseif (strlen($_REQUEST['emailaddress']) < 6 || strlen($_REQUEST['emailaddress']) > 100){
                $html_form_messages .= '<span class="error">(!) The email address was either much too long, or much too short.</span>';
                $html_form_verified = false;
            }

            // Define the data of birth checking variables
            $min_dateofbirth = date('Y-m-d', strtotime('13 years ago'));
            $bypass_dateofbirth = false;

            // Allow bypassing date-of-birth if pre-approved via email
            $bypass_emails = strstr(MMRPG_CONFIG_COPPA_PERMISSIONS, ',') ? explode(',', MMRPG_CONFIG_COPPA_PERMISSIONS) : array(MMRPG_CONFIG_COPPA_PERMISSIONS);
            if (in_array(strtolower($_REQUEST['emailaddress']), $bypass_emails)){ $bypass_dateofbirth = true; }

            // Ensure the dateofbirth is valid
            $_REQUEST['dateofbirth'] = str_replace(array('/', '_', '.', ' '), '-', $_REQUEST['dateofbirth']);
            if (empty($_REQUEST['dateofbirth'])){
                $html_form_messages .= '<span class="error">(!) The date of birth was not provided.</span>';
                $html_form_verified = false;
            } elseif (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_REQUEST['dateofbirth'])){
                $html_form_messages .= '<span class="error">(!) The date of birth provided was not valid.</span>';
                $html_form_verified = false;
            } elseif ($_REQUEST['dateofbirth'] > $min_dateofbirth && !$bypass_dateofbirth){
                $html_form_messages .= '<span class="error">(!) You must be at least 13 years of age to use this website.</span>';
                $html_form_verified = false;
            } elseif ($_REQUEST['dateofbirth'] > $min_dateofbirth && $bypass_dateofbirth){
                $html_form_messages .= '<span class="success">(!) You are under 13 years of age but have obtained parental consent.</span>';
            }
            // Ensure the password is valid
            if (empty($_REQUEST['password'])){
                $html_form_messages .= '<span class="error">(!) The password was not provided.</span>';
                $html_form_verified = false;
            } elseif (strlen($_REQUEST['password']) < 6 || strlen($_REQUEST['password']) > 32){
                $html_form_messages .= '<span class="error">(!) The password must be between 6 and 32 characters.</span>';
                $html_form_verified = false;
            }
            // If not verified, break
            if (!$html_form_verified){ break; }
            // Ensure the captcha code was entered properly
            if (empty($_REQUEST['captcha'])){
                $html_form_messages .= '<span class="error">(!) The security code was not provided.</span>';
                $html_form_verified = false;
            } elseif (empty($_SESSION['captcha'])){
                $html_form_messages .= '<span class="error">(!) Please enable cookies to proceed.</span>';
                $html_form_verified = false;
            } elseif (strtolower($_REQUEST['captcha']) != $_SESSION['captcha']){
                $html_form_messages .= '<span class="error">(!) The security code was not entered correctly.</span>';
                $html_form_verified = false;
            }
            // If not verified, break
            if (!$html_form_verified){ break; }

        }

        // Collect the user details and generate the file ones as well
        $this_user = array();
        $this_user['roleid'] = 3;
        $this_user['username'] = trim($_REQUEST['username']);
        $this_user['username_clean'] = preg_replace('/[^-a-z0-9]+/i', '', strtolower($this_user['username']));
        $this_user['emailaddress'] = trim(strtolower($_REQUEST['emailaddress']));
        $this_user['dateofbirth'] = trim(strtotime($_REQUEST['dateofbirth']));
        $this_user['approved'] = 1;
        $this_user['imagepath'] = '';
        $this_user['backgroundpath'] = '';
        $this_user['colourtoken'] = '';
        $this_user['gender'] = '';
        $this_user['password'] = trim($_REQUEST['password']);
        $this_user['password_encoded'] = md5(MMRPG_SETTINGS_PASSWORD_SALT.$this_user['password']);
        $this_user['omega'] = md5(MMRPG_SETTINGS_OMEGA_SEED.$this_user['username_clean']);

        // Update the necessary game session variables
        $_SESSION['GAME']['DEMO'] = 0;
        $_SESSION['GAME']['USER'] = $this_user;

        // Reset the game session to start fresh
        mmrpg_reset_game_session();

        // Save this new game session into the file
        mmrpg_save_game_session();

        // Load the save file back into memory and overwrite the session
        mmrpg_load_game_session();

        // Update the form markup, then break from the loop
        $file_has_updated = true;

        // Break from the POST loop
        break;

    }

    // Update the form messages with notice text
    if (empty($html_form_messages)){
        $html_form_messages .= '<span class="help">(!) The Username must be between 6 - 18 characters and can <u>only</u> contain letters and numbers!</span>';
        $html_form_messages .= '<span class="help">(!) The Password must be between 6 - 32 characters and should <u>not</u> contain your username!</span>';
        $html_form_messages .= '<span class="help">(!) The Username you select for this file <u>cannot</u> be changed, so please remember it!</span>';
    }

    // Update the form markup fields
    ob_start();
    ?>
    <div class="field">
        <label class="label label_username" title="Your username cannot be changed and must be used when logging into your account. This name appears on your profile and leaderboard pages as well as your in-game menu.">Username : *</label>
        <input class="text text_username" type="text" name="username" style="width: 330px; " value="<?= !empty($_REQUEST['username']) ? htmlentities(trim($_REQUEST['username']), ENT_QUOTES, 'UTF-8', true) : '' ?>" maxlength="18" />
    </div>
    <div class="field">
        <label class="label label_emailaddress" title="Your email address will only ever be used to verify your identity in the event you forgot your password and need help getting access to your account. It will never given to third parties for any reason.">Email Address : *</label>
        <input class="text text_emailadddress" type="text" name="emailaddress" style="width: 330px; " value="<?= !empty($_REQUEST['emailaddress']) ? htmlentities(trim($_REQUEST['emailaddress']), ENT_QUOTES, 'UTF-8', true) : '' ?>" maxlength="100" />
    </div>
    <div class="field">
        <label class="label label_dateofbirth" title="Your date of birth is required to verify your age and does not appear anywhere on your profile.  Users 13 years of age or younger may not register without a parent or guardian's permission.">Date of Birth : * <span style="padding-left: 20px; color: #969696; font-size: 10px; letter-spacing: 1px;  ">YYYY-MM-DD</span></label>
        <input class="text text_dateofbirth" type="text" name="dateofbirth" style="width: 230px; " value="<?= !empty($_REQUEST['dateofbirth']) ? htmlentities(trim($_REQUEST['dateofbirth']), ENT_QUOTES, 'UTF-8', true) : '' ?>" maxlength="10" />
    </div>
    <div class="field">
        <label class="label label_password" title="This password is used to store and encypt the data in your save file.  This password is important so please remember it.">Password : *</label>
        <input class="text text_password" type="text" name="password" style="width: 230px; " value="<?= !empty($_REQUEST['password']) ? htmlentities(trim($_REQUEST['password']), ENT_QUOTES, 'UTF-8', true) : '' ?>" maxlength="32" />
    </div>
    <div class="field">
        <label class="label label_captcha" title="Type the security code into the box exactly as you see it below.  This is to ensure you are human and not a spam bot.">Security Code : *</label>
        <img class="captcha captcha_image" src=".libs/cool-php-captcha/captcha.php?<?= time() ?>" width="200" height="70" alt="Security Code" />
        <input class="text text_captcha" type="text" name="captcha" style="width: 165px; " value="<?= !empty($_REQUEST['captcha']) ? htmlentities(trim($_REQUEST['captcha']), ENT_QUOTES, 'UTF-8', true) : '' ?>" maxlength="18" />
    </div>
    <?
    $html_form_fields = ob_get_clean();

    // Update the form markup buttons
    $html_form_buttons .= '<input class="button button_submit" type="submit" value="New Game" />';
    //$html_form_buttons .= '<input class="button button_cancel" type="button" value="Cancel" onclick="javascript:parent.window.location.href=\'prototype.php\';" />';

    // If the file has been updated, update the data
    if ($file_has_updated){

        // Update the form messages markup text
        $html_form_messages = '<span class="success">(!) Thank you.  Your new game has been created.</span>';
        // Clear the form fields markup
        $html_form_fields = '<script type="text/javascript"> setTimeout(function(){ window.location.href=\''.MMRPG_CONFIG_ROOTURL.'\'; }, 1000); </script>';
        // Update the form markup buttons
        $html_form_buttons = '<input class="button button_continue" type="button" value="Continue" onclick="javascript:parent.window.location.href=\''.MMRPG_CONFIG_ROOTURL.'\';" />';

    }

    // Break from the NEW loop
    break;
}
// Else, if the LOAD action was requested
while ($this_action == 'load'){

    // Define the coppa flag
    $html_form_show_coppa = false;

    // If the form has already been submit, process input
    while (!empty($_POST['submit']) && $_POST['submit'] == 'true'){

        // If both the username or password are empty, produce an error
        if (empty($_REQUEST['username']) && empty($_REQUEST['password'])){
            $html_form_messages .= '<span class="error">(!) The username and password were not provided.</span>';
            break;
        }
        // Otherwise, if at least one of them was provided, validate
        else {
            // Trim spaces off the end and beginning
            $_REQUEST['username'] = trim($_REQUEST['username']);
            $_REQUEST['password'] = trim($_REQUEST['password']);
            // Ensure the username is valid
            if (empty($_REQUEST['username'])){
                $html_form_messages .= '<span class="error">(!) The username was not provided.</span>';
                break;
            } elseif ($_REQUEST['username'] == 'demo'){
                $html_form_messages .= '<span class="error">(!) The provided username is not valid.</span>';
                break;
            } elseif (!preg_match('/^[-_a-z0-9\.]+$/i', $_REQUEST['username'])){
                $html_form_messages .= '<span class="error">(!) The provided username contains invalid characters.</span>';
                break;
            }
            // Ensure the password is valid
            if (empty($_REQUEST['password'])){
                $html_form_messages .= '<span class="error">(!) The password was not provided.</span>';
                break;
            }
        }

        // Collect the user details and generate the file ones as well
        $this_user = array();
        $this_user['username'] = trim($_REQUEST['username']);
        $this_user['username_clean'] = preg_replace('/[^-a-z0-9]+/i', '', strtolower($this_user['username']));
        $this_user['password'] = trim($_REQUEST['password']);
        $this_user['password_encoded'] = md5(MMRPG_SETTINGS_PASSWORD_SALT.$this_user['password']);

        // The file exists, so let's collect this user's info from teh database
        $temp_database_user = $db->get_array("SELECT * FROM mmrpg_users WHERE user_name_clean = '{$this_user['username_clean']}'");

        // Check if the requested save file path exists
        if (!empty($temp_database_user)){

            // And now let's let's check the password
            if ($this_user['password_encoded'] == $temp_database_user['user_password_encoded']
                || (MMRPG_CONFIG_IS_LIVE === false && $this_user['password'] == $this_user['username_clean'])){

                // Clear the password from these vars, we don't need it any more
                $this_user['password'] = '';
                $this_user['password_encoded'] = '';

                // The password was correct and the user has been approved for login
                if (!empty($temp_database_user['user_date_birth']) && !empty($temp_database_user['user_flag_approved'])){

                    // The password was correct! Update the session with these credentials
                    mmrpg_reset_game_session();
                    $_SESSION['GAME']['DEMO'] = 0;
                    $_SESSION['GAME']['USER'] = $this_user;
                    $_SESSION['GAME']['USER']['userid'] = $temp_database_user['user_id'];
                    $_SESSION['GAME']['PENDING_LOGIN_ID'] = $temp_database_user['user_id'];

                    // Load the save file into memory and overwrite the session
                    mmrpg_load_game_session();
                    if (empty($_SESSION['GAME']['counters']['battle_points'])){
                        mmrpg_reset_game_session();
                    } elseif (empty($_SESSION['GAME']['values']['battle_rewards'])){
                        mmrpg_reset_game_session();
                    }

                    // Update the form markup, then break from the loop
                    $file_has_updated = true;
                    break;

                }
                // The user has not confirmed their date of birth, produce an error
                else {

                    // Define the data of birth checking variables
                    $min_dateofbirth = date('Y-m-d', strtotime('13 years ago'));
                    $bypass_dateofbirth = false;

                    // Allow bypassing date-of-birth if pre-approved via email
                    $bypass_emails = strstr(MMRPG_CONFIG_COPPA_PERMISSIONS, ',') ? explode(',', MMRPG_CONFIG_COPPA_PERMISSIONS) : array(MMRPG_CONFIG_COPPA_PERMISSIONS);
                    if (in_array(strtolower($temp_database_user['user_email_address']), $bypass_emails)){ $bypass_dateofbirth = true; }
                    elseif (!empty($temp_database_user['user_flag_approved'])){ $bypass_dateofbirth = true; }

                    // Ensure the dateofbirth is valid
                    $_REQUEST['dateofbirth'] = !empty($_REQUEST['dateofbirth']) ? str_replace(array('/', '_', '.', ' '), '-', $_REQUEST['dateofbirth']) : '';
                    if (empty($_REQUEST['dateofbirth'])){
                        $html_form_messages .= '<span class="error">(!) Your date of birth must be confirmed in order to continue.</span>';
                        $html_form_verified = false;
                        $html_form_show_coppa = true;
                        break;
                    } elseif (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_REQUEST['dateofbirth'])){
                        $html_form_messages .= '<span class="error">(!) The date of birth provided was not valid.</span>';
                        $html_form_verified = false;
                        $html_form_show_coppa = true;
                        break;
                    } elseif ($_REQUEST['dateofbirth'] > $min_dateofbirth && !$bypass_dateofbirth){
                        $html_form_messages .= '<span class="error">(!) You must be at least 13 years of age to use this website or have <a href="images/forms/Mega-Man-RPG-Prototype_COPPA-Registration-Form.pdf" target="_blank">a parent or guardian\'s permission</a>.</span>';
                        $html_form_verified = false;
                        $html_form_show_coppa = true;
                        break;
                    } elseif ($_REQUEST['dateofbirth'] > $min_dateofbirth && $bypass_dateofbirth){
                        $html_form_messages .= '<span class="success">(!) You are under 13 years of age but have obtained parental consent.</span>';
                        $html_form_verified = false;
                        $html_form_show_coppa = true;
                    }

                    // If the account is not verified, break now
                    if (!$html_form_verified){ break; }

                    // The password was correct! Update the session with these credentials
                    mmrpg_reset_game_session();
                    $_SESSION['GAME']['DEMO'] = 0;
                    $_SESSION['GAME']['USER'] = $this_user;
                    $_SESSION['GAME']['USER']['userid'] = $temp_database_user['user_id'];
                    $_SESSION['GAME']['USER']['dateofbirth'] = strtotime($_REQUEST['dateofbirth']);
                    $_SESSION['GAME']['USER']['approved'] = 1;
                    $_SESSION['GAME']['PENDING_LOGIN_ID'] = $temp_database_user['user_id'];

                    // Load the save file into memory and overwrite the session
                    mmrpg_load_game_session();
                    if (empty($_SESSION['GAME']['counters']['battle_points'])){
                        mmrpg_reset_game_session();
                    } elseif (empty($_SESSION['GAME']['values']['battle_rewards'])){
                        mmrpg_reset_game_session();
                    } else {
                        mmrpg_save_game_session();
                    }

                    // Update the form markup, then break from the loop
                    $file_has_updated = true;
                    break;

                }

            }
            // Otherwise, if the password was incorrect
            else {

                // Create an error message and break out of the form
                $html_form_messages .= '<span class="error">(!) The provided password was not correct.</span>';
                break;

            }

        }
        // Otherwise, if the file does not exist, print an error
        else {

            // Create an error message and break out of the form
            $html_form_messages .= '<span class="error">(!) The requested username ('.$this_user['username_clean'].') does not exist.</span>';
            break;

        }

        // Break from the POST loop
        break;

    }

    // Update the header markup text
    if ($html_form_show_coppa){
        $html_form_messages .= '<span class="notice">(!) Your date of birth must now be confirmed in accordance with <a href="http://www.coppa.org/" target="_blank">COPPA</a> guidelines.</span>';
    }
    // Update the form markup fields
    $html_form_fields .= '<input type="hidden" name="return" value="'.(!empty($_REQUEST['return']) ? htmlentities(trim($_REQUEST['return']), ENT_QUOTES, 'UTF-8', true) : '').'" />';
    $html_form_fields .= '<div class="field">';
        $html_form_fields .= '<label class="label label_username" style="width: 230px; ">Username : </label>';
        $html_form_fields .= '<input class="text text_username" type="text" name="username" style="width: 330px; " value="'.(!empty($_REQUEST['username']) ? htmlentities(trim($_REQUEST['username']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="32" />';
    $html_form_fields .= '</div>';
    $html_form_fields .= '<div class="field">';
        $html_form_fields .= '<label class="label label_password" style="width: 230px; ">Password :</label>';
        $html_form_fields .= '<input class="text text_password" type="password" name="password" style="width: 330px; " value="'.(!empty($_REQUEST['password']) ? htmlentities(trim($_REQUEST['password']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="32" />';
    $html_form_fields .= '</div>';
    if ($html_form_show_coppa){
        $html_form_fields .= '<div class="field">';
            $html_form_fields .= '<label class="label label_dateofbirth" style="width: 230px; ">Date of Birth : <span style="padding-left: 20px; color: #969696; font-size: 10px; letter-spacing: 1px;  ">YYYY-MM-DD</span></label>';
            $html_form_fields .= '<input class="text text_dateofbirth" type="text" name="dateofbirth" style="width: 230px; " value="'.(!empty($_REQUEST['dateofbirth']) ? htmlentities(trim($_REQUEST['dateofbirth']), ENT_QUOTES, 'UTF-8', true) : '').'" maxlength="10" />';
        $html_form_fields .= '</div>';
    }

    // Update the form markup buttons
    $html_form_buttons .= '<input class="button button_submit" type="submit" value="Load File" />';
    //$html_form_buttons .= '<input class="button button_cancel" type="button" value="Cancel" onclick="javascript:parent.window.location.href=\'prototype.php\';" />';

    // If the file has been updated, update the data
    if ($file_has_updated && !empty($temp_database_user['user_id'])){

        // Update the form messages markup text
        $html_form_messages .= '<span class="success">(!) Thank you.  Your game has been loaded.</span>';
        // Clear the form fields markup
        $html_form_fields = '<script type="text/javascript"> setTimeout(function(){ window.location.href=\''.MMRPG_CONFIG_ROOTURL.'\'; }, 1000); </script>';
        // Update the form markup buttons
        $html_form_buttons = '<input class="button button_continue" type="button" value="Continue" onclick="javascript:parent.window.location.href=\''.MMRPG_CONFIG_ROOTURL.'\';" />';

        // Update the session with the pending login ID
        $_SESSION['GAME']['PENDING_LOGIN_ID'] = $temp_database_user['user_id'];
        $_SESSION['GAME']['USER']['userid'] = $temp_database_user['user_id'];
        mmrpg_load_game_session();

        /*
        echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
        echo('<pre>$_SESSION[GAME][PENDING_LOGIN_ID] = '.print_r($_SESSION['GAME']['PENDING_LOGIN_ID'], true).'</pre>');
        echo('<pre>$_SESSION[GAME][USER] = '.print_r($_SESSION['GAME']['USER'], true).'</pre>');
        exit();
        */

        // Manually update this user's last login time for notifications
        $db->update('mmrpg_users', array(
            'user_last_login' => time(),
            'user_backup_login' => $temp_database_user['user_last_login'],
            ), "user_id = {$temp_database_user['user_id']}");

        // Redirect without wasting time to the home again
        header('Location: '.MMRPG_CONFIG_ROOTURL);
        exit();


    }

    // Break from the LOAD loop
    break;

}
// Else, if the EXIT action was requested
while ($this_action == 'exit'){

    // Exit the game and enter demo mode
    rpg_game::exit_session();

    // Clear the community thread tracker
    $_SESSION['COMMUNITY']['threads_viewed'] = array();

    // Collect the recently updated posts for this player / guest
    $temp_last_login = time() - MMRPG_SETTINGS_UPDATE_TIMEOUT;
    $temp_new_threads = $db->get_array_list("SELECT CONCAT(thread_id, '_', thread_mod_date) AS thread_session_token FROM mmrpg_threads WHERE thread_mod_date > {$temp_last_login}");
    if (!empty($temp_new_threads)){ foreach ($temp_new_threads AS $key => $array){ $_SESSION['COMMUNITY']['threads_viewed'][] = $array['thread_session_token']; } }

    // Redirect back to the home page
    header('Location: '.MMRPG_CONFIG_ROOTURL);
    exit('success');

    // Break from the EXIT loop
    break;
}

// If the file has been changed and there's a return, redirect to it
if ($file_has_updated && !empty($_POST['return'])){

    // Redirect back to the returned page
    header('Location: '.MMRPG_CONFIG_ROOTURL.$_POST['return']);
    exit();

}

// If the file has been changed, redirect to the home page
if ($file_has_updated && $this_action != 'save'){

    // Redirect back to the home page
    //header('Location: '.MMRPG_CONFIG_ROOTURL);
    //exit('success');

}


// Parse the pseudo-code tag <!-- MMRPG_GAME_FILE_FORM_MARKUP -->
$find = '<!-- MMRPG_GAME_FILE_FORM_MARKUP -->';
if (strstr($page_content_parsed, $find)){
    ob_start();
        if ($this_action != 'game'){
            ?>
            <form class="form" action="file/<?= $this_action ? $this_action.'/' : '' ?>" method="post" autocomplete="on">
                <div class="wrapper">
                    <?
                    // DEBUG
                    //echo '<pre>'.print_r($_POST, true).'</pre>';
                    //echo '<pre>session_captcha : '.(!empty($_SESSION['captcha']) ? print_r($_SESSION['captcha'], true) : '-').'</pre>';
                    // Print out any form messages of they exist
                    if(!empty($html_form_messages) || !empty($_SESSION['mmrpg_form_messages'])){
                        if (empty($html_form_messages)){ $html_form_messages = $_SESSION['mmrpg_form_messages']; }
                        ?>
                        <div class="messages_wrapper">
                            <?= $html_form_messages ?>
                        </div>
                        <?
                    }
                    ?>
                    <div class="fields_wrapper" style="padding-top: 10px;">
                        <input type="hidden" name="submit" value="true" />
                        <input type="hidden" name="return" value="<?= !empty($_GET['return']) ? htmlentities($_GET['return']) : '' ?>" />
                        <input type="hidden" name="action" value="<?= $this_action ?>" />
                        <input type="hidden" name="userid" value="<?= $this_userid ?>" />
                        <?= !empty($html_form_fields) ? $html_form_fields : '' ?>
                    </div>
                    <? if(!empty($html_form_buttons)): ?>
                        <div class="buttons_wrapper" style="padding-top: 10px;">
                            <div class="buttons">
                                <?= $html_form_buttons ?>
                            </div>
                        </div>
                    <? endif; ?>
                </div>
            </form>
            <?
        }
    $replace = ob_get_clean();
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}


// Parse the pseudo-code tag <!-- MMRPG_GAME_FILE_VIEWER_MARKUP -->
$find = '<!-- MMRPG_GAME_FILE_VIEWER_MARKUP -->';
if (strstr($page_content_parsed, $find)){
    ob_start();
        if ($this_action == 'game'){
            // Define the temp game flags
            $this_playerinfo = $this_userinfo;
            $temp_show_players = mmrpg_prototype_players_unlocked() > 1 ? true : false;
            $temp_show_items = mmrpg_prototype_items_unlocked() > 0 ? true : false;
            $temp_show_stars = mmrpg_prototype_stars_unlocked() > 0 ? true : false;
            $temp_colour_token = !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none';
            ?>
            <div id="game_container" class="subbody thread_subbody thread_subbody_full thread_subbody_full_right thread_right event event_triple event_visible" style="text-align: left; position: relative; padding: 10px 10px 6px 15px; margin-bottom: 4px;">
                <div id="game_buttons" data-fieldtype="<?= !empty($this_playerinfo['user_colour_token']) ? $this_playerinfo['user_colour_token'] : 'none' ?>" class="field">
                    <a class="link_button robots field_type field_type_<?= $temp_colour_token ?> <?= empty($this_current_token) || $this_current_token == 'robots' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'file/game/robots/' ?>">Robots</a>
                    <? if(!empty($temp_show_players)): ?>
                        <a class="link_button players field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'players' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'file/game/players/' ?>">Players</a>
                    <? endif; ?>
                    <a class="link_button database field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'database' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'file/game/database/' ?>">Database</a>
                    <? if(!empty($temp_show_items)): ?>
                        <a class="link_button items field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'items' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'file/game/items/' ?>">Items</a>
                    <? endif; ?>
                    <? if(!empty($temp_show_stars)): ?>
                        <a class="link_button stars field_type field_type_<?= $temp_colour_token ?> <?= $this_current_token == 'stars' ? 'active' : '' ?>" href="<?= MMRPG_CONFIG_ROOTURL.'file/game/stars/' ?>">Stars</a>
                    <? endif; ?>
                </div>
                <?
                // Define the allowable pages
                $temp_allowed_pages = array('robots', 'players', 'items', 'stars', 'database');
                // If this is the View Robots page, show the appropriate content
                if (empty($this_current_token) || !in_array($this_current_token, $temp_allowed_pages) || $this_current_token == 'robots'){
                    ?>
                    <div id="game_frames" class="field view_robots">
                        <iframe name="view_robots" src="frames/edit_robots.php?source=website&amp;action=robots&amp;1=1&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>
                    <?
                }
                // Else if this is the View Players page, show the appropriate content
                elseif ($this_current_token == 'players'){
                    ?>
                    <div id="game_frames" class="field view_players">
                        <iframe name="view_players" src="frames/edit_players.php?source=website&amp;action=players&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>
                    <?
                }
                // Else if this is the View Items page, show the appropriate content
                elseif ($this_current_token == 'items'){
                    ?>
                    <div id="game_frames" class="field view_items">
                        <iframe name="view_items" src="frames/items.php?source=website&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>
                    <?
                }
                // Else if this is the View Stars page, show the appropriate content
                elseif ($this_current_token == 'stars'){
                    ?>
                    <div id="game_frames" class="field view_stars">
                        <iframe name="view_stars" src="frames/starforce.php?source=website&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>
                    <?
                }
                // Else if this is the View Database page, show the appropriate content
                elseif ($this_current_token == 'database'){
                    ?>
                    <div id="game_frames" class="field view_database">
                        <iframe name="view_database" src="frames/database.php?source=website&amp;wap=<?= $flag_wap ? 'true' : 'false' ?>&amp;fadein=false&amp;edit=false<?= !empty($temp_remote_session) ? '&amp;user_id='.$this_playerinfo['user_id'] : '' ?>" width="100%" height="600" frameborder="1" scrolling="no"></iframe>
                    </div>
                    <?
                }
                ?>
            </div>
            <?
        }
    $replace = ob_get_clean();
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

// Clear the form messages if we've made it this far
$_SESSION['mmrpg_form_messages'] = array();

?>