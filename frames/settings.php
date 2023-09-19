<?php
// Include the TOP file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// This should never appear for remove viewing
if (defined('MMRPG_REMOTE_GAME')){ exit('You should not be here...'); }
// This should never appear for users not logged in
if (!rpg_user::is_member()){ exit('You should not be here...'); }

// Collect the session token
$session_token = mmrpg_game_token();

// Include the DATABASE file
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require(MMRPG_CONFIG_ROOTDIR.'database/players.php');
require(MMRPG_CONFIG_ROOTDIR.'database/robots.php');
require(MMRPG_CONFIG_ROOTDIR.'database/items.php');

// Collect the editor flag if set
$global_allow_editing = !defined('MMRPG_REMOTE_GAME') ? true : false;
if (isset($_GET['edit']) && $_GET['edit'] == 'false'){ $global_allow_editing = false; }
$global_frame_source = !empty($_GET['source']) ? trim($_GET['source']) : 'prototype';

// Collect the current user's details from the database
$temp_user_fields = rpg_user::get_index_fields(true, 'users');
$temp_user_role_fields = rpg_user_role::get_index_fields(true, 'roles');
$current_user_id = rpg_user::get_current_userid();
$current_user_info = $db->get_array("SELECT
    {$temp_user_fields},
    {$temp_user_role_fields}
    FROM mmrpg_users AS users
    LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
    WHERE user_id = {$current_user_id}
    ;");


// Collect options markup for various dropdowns later
$profile_avatar_options_markup = mmrpg_prototype_get_profile_avatar_options($current_user_info, $allowed_avatar_options);
$profile_colour_options_markup = mmrpg_prototype_get_profile_colour_options($current_user_info, $allowed_colour_options);
$profile_background_options_markup = mmrpg_prototype_get_profile_background_options($current_user_info, $allowed_background_options);


// -- PROCESS FORM ACTIONS -- //

// Process any form actions that have been submit
$form_messages = mmrpg_init_form_messages();
$form_actions = !empty($_POST['form_actions']) ? $_POST['form_actions'] : array();
if (!empty($form_actions)){

    // Define an array to hold the names of any tabs that have been updated
    $updated_tabs = array();

    // Define a boolean flag for whether this form was saved successfully
    $form_success = true;

    // Check to make sure we've updating data for the right user ID
    if (empty($_POST['user_id']) || intval($_POST['user_id']) !== intval($current_user_id)){
        $form_messages[] = array('error', 'User ID was not provided or does not match logged-in user!');
        $form_success = false;
    }

    // Define an array to hold update functions for the various settings tabs
    $update_functions = array();

    // Define an update function for the "Account Settings" tab
    $update_functions['account_settings'] = function() use (&$updated_tabs, &$form_messages, &$form_data){
        global $db, $current_user_id, $current_user_info;

        // Primary Account Data

        $form_data = array();
        $form_data['user_name_public'] = !empty($_POST['user_name_public']) && preg_match('/^[-_0-9a-z\.\s]+$/i', $_POST['user_name_public']) ? trim($_POST['user_name_public']) : '';
        $form_data['user_gender'] = !empty($_POST['user_gender']) && preg_match('/^(male|female|other)$/', $_POST['user_gender']) ? trim(strtolower($_POST['user_gender'])) : '';
        //$form_data['user_date_birth'] = !empty($_POST['user_date_birth']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_POST['user_date_birth']) ? trim($_POST['user_date_birth']) : '';
        $form_data['user_email_address'] = !empty($_POST['user_email_address']) && preg_match('/^[-_0-9a-z\.]+@[-_0-9a-z\.]+\.[-_0-9a-z\.]+$/i', $_POST['user_email_address']) ? trim(strtolower($_POST['user_email_address'])) : '';

        if (empty($form_data['user_name_public']) && !empty($_POST['user_name_public'])){
            $form_messages[] = array('warning', 'Display username was invalid and will not be updated');
            unset($form_data['user_name_public']);
        }
        /*
        if (empty($form_data['user_date_birth']) && !empty($_POST['user_date_birth'])){
            $form_messages[] = array('warning', 'Date of birth was invalid and will not be updated');
            unset($form_data['user_date_birth']);
        }
        */
        if (empty($form_data['user_gender']) && !empty($_POST['user_gender'])){
            $form_messages[] = array('warning', 'Gender identity was invalid and will not be updated');
            unset($form_data['user_gender']);
        }
        if (empty($form_data['user_email_address']) && !empty($_POST['user_email_address'])){
            $form_messages[] = array('warning', 'Email address was invalid and will not be updated');
            unset($form_data['user_email_address']);
        }

        if (empty($form_data)){ return false; }

        /*
        if (!empty($form_data['user_date_birth'])){
            list($yyyy, $mm, $dd) = explode('-', $form_data['user_date_birth']);
            $form_data['user_date_birth'] = mktime(0, 0, 0, $mm, $dd, $yyyy);
        }
        */

        $update_results = $db->update('mmrpg_users',
            array_merge($form_data, array('user_date_modified' => time())),
            array('user_id' => $current_user_id)
            );

        // Optional Password Change

        $user_password_new = !empty($_POST['user_password_new']) ? trim($_POST['user_password_new']) : '';
        $user_password_new2 = !empty($_POST['user_password_new2']) ? trim($_POST['user_password_new2']) : '';

        if (!empty($user_password_new)){
            $update_password = true;
            if (empty($user_password_new2)){
                $form_messages[] = array('warning', 'You must enter your new password twice to validate the change');
                $update_password = false;
            } elseif ($user_password_new != $user_password_new2){
                $form_messages[] = array('warning', 'The passwords were not the same and will not be updated');
                $update_password = false;
            } elseif ($user_password_new == $user_password_new2){
                if (strlen($user_password_new) < 6){
                    $form_messages[] = array('warning', 'The new password was too short and will not be updated');
                    $update_password = false;
                } elseif (strlen($user_password_new) > 32){
                    $form_messages[] = array('warning', 'The new password was too long and will not be updated');
                    $update_password = false;
                }
            }
            if ($update_password){
                $form_data = array();
                $form_data['user_password_encoded'] = md5(MMRPG_SETTINGS_PASSWORD_SALT.$user_password_new);
                $form_messages[] = array('alert', 'The account password was updated successfully');
                $update_results = $db->update('mmrpg_users',
                    array_merge($form_data, array('user_date_modified' => time())),
                    array('user_id' => $current_user_id)
                    );
            }
        }

        return true;

        };

    // Define an update function for the "Profile Settings" tab
    $update_functions['profile_settings'] = function() use (&$updated_tabs, &$form_messages, &$form_data){
        global $db, $current_user_id, $current_user_info;
        global $allowed_avatar_options, $allowed_background_options, $allowed_colour_options;

        $form_data = array();

        $form_data['user_image_path'] = !empty($_POST['user_image_path']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['user_image_path']) ? trim(strtolower($_POST['user_image_path'])) : '';
        $form_data['user_background_path'] = !empty($_POST['user_background_path']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['user_background_path']) ? trim(strtolower($_POST['user_background_path'])) : '';
        $form_data['user_colour_token'] = !empty($_POST['user_colour_token']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['user_colour_token']) ? trim(strtolower($_POST['user_colour_token'])) : '';
        $form_data['user_colour_token2'] = !empty($_POST['user_colour_token2']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['user_colour_token2']) ? trim(strtolower($_POST['user_colour_token2'])) : '';

        $form_data['user_website_address'] = !empty($_POST['user_website_address']) && preg_match('/^(https?:\/\/)?[-_0-9a-z\.]+\.[-_0-9a-z\.]+/i', $_POST['user_website_address']) ? trim(strtolower($_POST['user_website_address'])) : '';


        if (!empty($form_data['user_image_path']) && !in_array($form_data['user_image_path'], $allowed_avatar_options)){
            $form_messages[] = array('warning', 'Player avatar was not allowed and will not be updated');
            unset($form_data['user_image_path']);
        } elseif (empty($form_data['user_image_path']) && !empty($_POST['user_image_path'])){
            $form_messages[] = array('warning', 'Player avatar was invalid and will not be updated');
            unset($form_data['user_image_path']);
        }

        if (!empty($form_data['user_background_path']) && !in_array($form_data['user_background_path'], $allowed_background_options)){
            $form_messages[] = array('warning', 'Player background was not allowed and will not be updated');
            unset($form_data['user_background_path']);
        } elseif (empty($form_data['user_background_path']) && !empty($_POST['user_background_path'])){
            $form_messages[] = array('warning', 'Player background was invalid and will not be updated');
            unset($form_data['user_background_path']);
        }

        if (!empty($form_data['user_colour_token']) && !in_array($form_data['user_colour_token'], $allowed_colour_options)){
            $form_messages[] = array('warning', 'Player colour was not allowed and will not be updated');
            unset($form_data['user_colour_token']);
        } elseif (empty($form_data['user_colour_token']) && !empty($_POST['user_colour_token'])){
            $form_messages[] = array('warning', 'Player colour was invalid and will not be updated');
            unset($form_data['user_colour_token']);
        }

        if (!empty($form_data['user_colour_token2']) && !in_array($form_data['user_colour_token2'], $allowed_colour_options)){
            $form_messages[] = array('warning', 'Secondary player colour was not allowed and will not be updated');
            unset($form_data['user_colour_token2']);
        } elseif (empty($form_data['user_colour_token2']) && !empty($_POST['user_colour_token2'])){
            $form_messages[] = array('warning', 'Secondary player colour was invalid and will not be updated');
            unset($form_data['user_colour_token2']);
        }

        if (empty($form_data['user_website_address']) && !empty($_POST['user_website_address'])){
            $form_messages[] = array('warning', 'Website address was invalid and will not be updated');
            unset($form_data['user_website_address']);
        }

        if (empty($form_data)){ return false; }

        if (!empty($form_data['user_website_address'])){
            $website = $form_data['user_website_address'];
            $website = preg_replace('/^https?:\/\//i', '', trim($website));
            if (!strstr($website, '/')){ $website .= '/'; }
            $website = 'http://'.$website;
            $form_data['user_website_address'] = $website;
        }

        $update_results = $db->update('mmrpg_users',
            array_merge($form_data, array('user_date_modified' => time())),
            array('user_id' => $current_user_id)
            );

        return true;

        };

    // Define an update function for the "Omega Settings" tab
    $update_functions['omega_settings'] = function() use (&$updated_tabs, &$form_messages, &$form_data){
        global $db, $current_user_id, $current_user_info;

        $form_data = array();

        $user_omega_seed = !empty($_POST['user_omega_seed']) ? trim(preg_replace('/[^-_0-9a-z\.\s\,\?\!]+/i', '', $_POST['user_omega_seed'])) : '';
        $user_omega_seed = preg_replace('/\s+/', ' ', $user_omega_seed);
        if (!empty($user_omega_seed) && strlen($user_omega_seed) < 6){ $user_omega_seed = ''; }
        elseif (!empty($user_omega_seed) && strlen($user_omega_seed) > 32){ $user_omega_seed = ''; }

        if (empty($user_omega_seed) && !empty($_POST['user_omega_seed'])){
            $form_messages[] = array('warning', 'Omega Seed value was invalid and will be ignored');
        }
        elseif (!empty($user_omega_seed)){

            // Generate the new omega sequence from the seed value and update
            $user_omega_sequence = md5(MMRPG_SETTINGS_OMEGA_SEED.$user_omega_seed);
            $form_data['user_omega'] = $user_omega_sequence;
            $form_messages[] = array('alert', 'Your Omega Sequence was regenerated successfully');

        }

        if (empty($form_data)){ return false; }

        $update_results = $db->update('mmrpg_users',
            array_merge($form_data, array('user_date_modified' => time())),
            array('user_id' => $current_user_id)
            );

        return true;

        };

    // Define an update function for the "Extra Settings" tab
    $update_functions['game_settings'] = function() use (&$updated_tabs, &$form_messages, &$form_data){
        global $db, $current_user_id, $current_user_info;

        $form_data = array();

        $form_data['masterVolume'] = !empty($_POST['masterVolume']) && is_numeric($_POST['masterVolume']) ? $_POST['masterVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_MASTERVOLUME;
        $form_data['musicVolume'] = isset($_POST['musicVolume']) && is_numeric($_POST['musicVolume']) ? $_POST['musicVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_MUSICVOLUME;
        $form_data['effectVolume'] = isset($_POST['effectVolume']) && is_numeric($_POST['effectVolume']) ? $_POST['effectVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_EFFECTVOLUME;

        $allowed_render_modes = array('default', 'crisp-edges', 'pixelated');
        $form_data['spriteRenderMode'] = !empty($_POST['spriteRenderMode']) && in_array($_POST['spriteRenderMode'], $allowed_render_modes) ? $_POST['spriteRenderMode'] : $allowed_render_modes[0];

        $allowed_button_modes = array('default', 'classic');
        $form_data['battleButtonMode'] = !empty($_POST['battleButtonMode']) && in_array($_POST['battleButtonMode'], $allowed_button_modes) ? $_POST['battleButtonMode'] : $allowed_button_modes[0];

        //error_log('$form_data = '.print_r($form_data, true));

        $audioBalanceConfig = array();
        $audioBalanceConfig['masterVolume'] = $form_data['masterVolume'] >= 0 && $form_data['masterVolume'] <= 1 ? $form_data['masterVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_MASTERVOLUME;
        $audioBalanceConfig['musicVolume'] = $form_data['musicVolume'] >= 0 && $form_data['musicVolume'] <= 1 ? $form_data['musicVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_MUSICVOLUME;
        $audioBalanceConfig['effectVolume'] = $form_data['effectVolume'] >= 0 && $form_data['effectVolume'] <= 1 ? $form_data['effectVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_EFFECTVOLUME;
        //error_log('$audioBalanceConfig = '.print_r($audioBalanceConfig, true));

        $spriteRenderMode = !empty($form_data['spriteRenderMode']) ? $form_data['spriteRenderMode'] : $allowed_render_modes[0];
        //error_log('$spriteRenderMode = '.print_r($spriteRenderMode, true));

        $battleButtonMode = !empty($form_data['battleButtonMode']) ? $form_data['battleButtonMode'] : $allowed_button_modes[0];
        //error_log('$battleButtonMode = '.print_r($battleButtonMode, true));

        $session_token = rpg_game::session_token();
        $_SESSION[$session_token]['battle_settings']['audioBalanceConfig'] = $audioBalanceConfig;
        $_SESSION[$session_token]['battle_settings']['spriteRenderMode'] = $spriteRenderMode;
        $_SESSION[$session_token]['battle_settings']['battleButtonMode'] = $battleButtonMode;

        //error_log('(A) $_SESSION[$session_token][\'battle_settings\'][\'audioBalanceConfig\'] = '.print_r($_SESSION[$session_token]['battle_settings']['audioBalanceConfig'], true));

        return true;

        };

    /*
    // Define an update function for the "Extra Settings" tab
    $update_functions['extra_settings'] = function() use (&$updated_tabs, &$form_messages, &$form_data){
        global $db, $current_user_id, $current_user_info;

        };
    */

    // Process allowed form actions if they've been provided
    foreach ($update_functions AS $tab_token => $update_function){
        if ($form_success && in_array($tab_token, $form_actions)){
            if ($update_function()){ $updated_tabs[] = $tab_token; }
        }
    }

    // If we weren't able to update any data for the form, produce an additional error message
    if (!$form_success || empty($updated_tabs)){ $form_messages[] = array('error', 'Unable to save changes! Please try again.'); }
    elseif (!empty($updated_tabs)){ $form_messages[] = array('success', 'Success! Changes have been saved.'); }

    // Now that we're done submitting the form, redirect to prevent double-submission
    if (!empty($_REQUEST['current_tab'])){ $_SESSION['mmrpg_forms']['current_tab'] = $_REQUEST['current_tab']; }
    $temp_user_fields = rpg_user::get_index_fields(true, 'users');
    $temp_user_role_fields = rpg_user_role::get_index_fields(true, 'roles');
    $this_userinfo = $db->get_array("SELECT {$temp_user_fields}, {$temp_user_role_fields} FROM mmrpg_users AS users LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id WHERE users.user_id = '{$this_userid}' LIMIT 1");
    $_SESSION['GAME']['USER'] = mmrpg_prototype_format_user_data_for_session($this_userinfo);
    mmrpg_save_game_session();
    mmrpg_redirect_form_action(MMRPG_CONFIG_ROOTURL.'frames/settings.php');


}


// -- GENERATE TAB MARKUP -- //

// Define an array to hold settings tabs and content
$settings_tabs = array();

// Generate markup for GAME SETTINGS if applicable
if (true){

    // Define the markup for this section
    $tab_token = 'game_settings';
    $tab_name = 'Game Settings';
    ob_start();
    ?>

        <div class="game-settings">

            <?

            // Collect current values if they exist so we can display them as such
            $session_token = rpg_game::session_token();
            $battleSettings = $_SESSION[$session_token]['battle_settings'];
            $spriteRenderMode = isset($battleSettings['spriteRenderMode']) ? $battleSettings['spriteRenderMode'] : 'default';
            $battleButtonMode = isset($battleSettings['battleButtonMode']) ? $battleSettings['battleButtonMode'] : 'default';
            $audioBalanceConfig = isset($battleSettings['audioBalanceConfig']) ? $battleSettings['audioBalanceConfig'] : array(
                'masterVolume' => MMRPG_SETTINGS_AUDIODEFAULT_MASTERVOLUME,
                'musicVolume' => MMRPG_SETTINGS_AUDIODEFAULT_MUSICVOLUME,
                'effectVolume' => MMRPG_SETTINGS_AUDIODEFAULT_EFFECTVOLUME,
                );

            ?>

            <div class="field" data-setting="audioBalanceConfig">
                <div class="label">
                    <strong>Audio Balancing</strong>
                </div>
                <div class="subfield input-group">
                    <div class="subfield">
                        <label class="label" for="masterVolume">Master Volume</label>
                        <input class="slider" type="range" name="masterVolume" min="0" max="1" step="0.01" value="<?= $audioBalanceConfig['masterVolume'] ?>">
                    </div>
                    <div class="subfield">
                        <label class="label" for="musicVolume">Music Volume</label>
                        <input class="slider" type="range" name="musicVolume" min="0" max="1" step="0.01" value="<?= $audioBalanceConfig['musicVolume'] ?>">
                    </div>
                    <div class="subfield">
                        <label class="label" for="effectVolume">SFX Volume <sup class="help">* on supported devices</sup></label>
                        <input class="slider" type="range" name="effectVolume" min="0" max="1" step="0.01" value="<?= $audioBalanceConfig['effectVolume'] ?>">
                    </div>
                </div>
            </div>

            <div class="field" data-setting="spriteRenderMode">
                <div class="label">
                    <strong>Sprite Rendering</strong>
                </div>
                <div class="subfield input-group">
                    <? $active = empty($spriteRenderMode) || $spriteRenderMode === 'default'; ?>
                    <div class="radiofield <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="spriteRenderMode" value="default" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="default">Auto</label>
                    </div>
                    <? $active = $spriteRenderMode === 'crisp-edges'; ?>
                    <div class="radiofield <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="spriteRenderMode" value="crisp-edges" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="crisp-edges">Crisp Edges</label>
                    </div>
                    <? $active = $spriteRenderMode === 'pixelated'; ?>
                    <div class="radiofield <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="spriteRenderMode" value="pixelated" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="pixelated">Pixelated</label>
                    </div>
                </div>
                <div id="canvas" class="samples">
                    <?
                    // Print out some sample sprites to show how things look
                    $samples = array();
                    $samples[40] = 'images/robots/mega-man/sprite_right_40x40.png';
                    $samples[80] = 'images/robots/proto-man/sprite_right_80x80.png';
                    foreach ($samples AS $size => $path){
                        ?>
                        <div class="group of2" data-base="<?= $size ?>">
                            <?
                            for ($i = 0; $i <= 3; $i++){
                                $s = 40 + (20 * ($i * 1));
                                ?>
                                <div class="sprite" style="
                                    background-image: url('<?= $path ?>');
                                    width: <?= $s ?>px;
                                    height: <?= $s ?>px;
                                    bottom: 0;
                                    left: <?= -10 + ($i * ($s / 2)) - ($i * $i * 3) ?>px;
                                    "></div>
                                <?
                            }
                            ?>
                        </div>
                        <?
                    }
                    ?>
                </div>
            </div>

            <div class="field" data-setting="battleButtonMode">
                <div class="label">
                    <strong>Mission Buttons</strong>
                </div>
                <div class="subfield input-group">
                    <? $active = empty($battleButtonMode) || $battleButtonMode === 'default'; ?>
                    <div class="radiofield <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="battleButtonMode" value="default" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="default">Default &nbsp;(Aesthetic)</label>
                    </div>
                    <? $active = $battleButtonMode === 'classic'; ?>
                    <div class="radiofield <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="battleButtonMode" value="classic" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="classic">Classic &nbsp;(Detailed)</label>
                    </div>
                </div>
            </div>

        </div>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs[] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup
        );
    }
}

// Generate markup for ACCOUNT SETTINGS if applicable
if (true){

    // Define the markup for this section
    $tab_token = 'account_settings';
    $tab_name = 'Account Settings';
    ob_start();
    ?>

        <div class="field required">
            <div class="label">
                <strong>Login Username</strong>
                <em>cannot be changed</em>
            </div>
            <input type="hidden" name="user_name_clean" value="<?= encode_form_value($current_user_info['user_name_clean']) ?>" readonly="readonly" />
            <input class="textbox" type="text" name="user_name" value="<?= encode_form_value($current_user_info['user_name']) ?>" maxlength="64" readonly="readonly" disabled="disabled" />
        </div>

        <? /*
        <div class="field">
            <div class="label">
                <strong>Account Type</strong>
            </div>
            <input type="hidden" name="role_name" value="<?= $current_user_info['role_id'] ? >" readonly="readonly" />
            <input class="textbox" type="text" name="role_name" value="<?= $current_user_info['role_name_full'] ? >" maxlength="64" readonly="readonly" disabled="disabled" />
        </div>
        */ ?>

        <div class="field">
            <div class="label">
                <strong>Display Username</strong>
            </div>
            <input class="textbox" type="text" name="user_name_public" value="<?= encode_form_value($current_user_info['user_name_public']) ?>" maxlength="64" />
        </div>

        <div class="field required">
            <div class="label">
                <strong>Email Address</strong>
                <em>used for account validation</em>
            </div>
            <input class="textbox" type="email" name="user_email_address" value="<?= encode_form_value($current_user_info['user_email_address']) ?>" maxlength="128" required="required" />
        </div>

        <? /*
        <div class="field required">
            <div class="label">
                <strong>Date of Birth</strong>
                <em>used for age verification</em>
            </div>
            <input class="textbox" type="date" name="user_date_birth" value="<?= !empty($current_user_info['user_date_birth']) ? date('Y-m-d', $current_user_info['user_date_birth']) : '' ? >" required="required" maxlength="10" placeholder="YYYY-MM-DD" />
        </div>
        */ ?>

        <div class="field">
            <div class="label">
                <strong>Gender Identity</strong>
            </div>
            <select class="select" name="user_gender">
                <option value="" <?= empty($current_user_info['user_gender']) ? 'selected="selected"' : '' ?>>-</option>
                <option value="male" <?= $current_user_info['user_gender'] == 'male' ? 'selected="selected"' : '' ?>>Male</option>
                <option value="female" <?= $current_user_info['user_gender'] == 'female' ? 'selected="selected"' : '' ?>>Female</option>
                <option value="other" <?= $current_user_info['user_gender'] == 'other' ? 'selected="selected"' : '' ?>>Other</option>
                <option value="none" <?= $current_user_info['user_gender'] == 'none' ? 'selected="selected"' : '' ?>>None</option>
            </select>
        </div>

        <div class="field">
            <div class="label">
                <strong>Change Password</strong>
                <em>6 - 32 characters</em>
            </div>
            <input class="textbox" type="password" name="user_password_new" value="" minlength="6" maxlength="32" autocomplete="new-password" />
        </div>

        <div class="field">
            <div class="label">
                <strong>Retype Password</strong>
                <em>if changing</em>
            </div>
            <input class="textbox" type="password" name="user_password_new2" value="" minlength="6" maxlength="32" autocomplete="new-password" />
        </div>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs[] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup
        );
    }
}

// Generate markup for PROFILE SETTINGS if applicable
if (true){

    // Define the markup for this section
    $tab_token = 'profile_settings';
    $tab_name = 'Profile Settings';
    ob_start();
    ?>

        <div class="field">
            <strong class="label">Profile Avatar</strong>
            <select class="select" name="user_image_path">
                <?= str_replace(
                    'value="'.$current_user_info['user_image_path'].'"',
                    'value="'.$current_user_info['user_image_path'].'" selected="selected"',
                    $profile_avatar_options_markup
                    ) ?>
            </select>
        </div>

        <div class="field">
            <strong class="label">Profile Background</strong>
            <select class="select" name="user_background_path">
                <?= str_replace(
                    'value="'.$current_user_info['user_background_path'].'"',
                    'value="'.$current_user_info['user_background_path'].'" selected="selected"',
                    $profile_background_options_markup
                    ) ?>
            </select>
        </div>

        <?
        $prototype_complete = mmrpg_prototype_complete();
        $profile_colour_two_unlocked = $prototype_complete >= 3 ? true : false;
        ?>

        <div class="field">
            <strong class="label">Profile Colour <?= $profile_colour_two_unlocked ? '#1' : '' ?></strong>
            <select class="select" name="user_colour_token">
                <?= str_replace(
                    'value="'.$current_user_info['user_colour_token'].'"',
                    'value="'.$current_user_info['user_colour_token'].'" selected="selected"',
                    $profile_colour_options_markup
                    ) ?>
            </select>
        </div>

        <? if ($profile_colour_two_unlocked){ ?>
            <div class="field">
                <strong class="label">Profile Colour #2</strong>
                <select class="select" name="user_colour_token2">
                    <?= str_replace(
                        'value="'.$current_user_info['user_colour_token2'].'"',
                        'value="'.$current_user_info['user_colour_token2'].'" selected="selected"',
                        $profile_colour_options_markup
                        ) ?>
                </select>
            </div>
        <? } ?>

        <div class="field">
            <div class="label">
                <strong>Website Address</strong>
            </div>
            <input class="textbox" type="text" name="user_website_address" value="<?= encode_form_value($current_user_info['user_website_address']) ?>" maxlength="128" />
        </div>

        <? /*
        <div class="field fullsize">
            <div class="label">
                <strong>Profile Text</strong>
                <em>public, also displayed on leaderboard page</em>
            </div>
            <textarea class="textarea" name="user_profile_text" rows="6"><?= encode_form_value($current_user_info['user_profile_text']) ?></textarea>
        </div>
        */ ?>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs[] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup
        );
    }

}

// Generate markup for ADVANCED SETTINGS if applicable
if (true){

    // Define the markup for this section
    $tab_token = 'advanced_settings';
    $tab_name = 'Advanced Settings';
    ob_start();
    ?>

        <p class="description">
            <strong>Stop!</strong> Please be careful.
            The options below should only be used if you know what you're doing.
            Proceed at your own risk!
        </p>

        <div class="field buttons">
            <div class="label">
                <strong>Save File Options</strong>
            </div>
            <div class="wrapper">
                <input class="button type type_flame button_reset" type="button" name="reset" value="Reset Entire Game" onclick="javascript:parent.window.mmrpg_trigger_reset(true);" />
            </div>
        </div>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs[] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup,
        'icon' => '<i class="fa fas fa-exclamation-triangle"></i>',
        'class' => 'float_right icon_only hide_tab_buttons'
        );
    }

}

// Generate markup for OMEGA SETTINGS if applicable
if (mmrpg_prototype_item_unlocked('omega-seed')){

    // Define the markup for this section
    $tab_token = 'omega_settings';
    $tab_name = 'Omega Settings';
    ob_start();
    ?>

        <p class="description">
            Your <strong>Omega Sequence</strong> influences which <em>Omega Factors</em> are assigned to the doctors, robots, and shop keepers in your game.
            Omega Factors are mysterious elemental forces that affect different characters and abilities in different ways.
        </p>

        <p class="description">
            Your default Omega Sequence is based on the username you first signed up with, but you can generate a new one by entering a custom <strong>Omega Seed</strong> value below.
            Check the robot editor and shop tabs to see which Omega Factors have been assigned to which characters.
        </p>

        <div>

            <div class="field">
                <div class="label">
                    <strong>Omega Seed</strong>
                    <em>enter new to regenerate</em>
                </div>
                <input class="textbox" type="text" name="user_omega_seed" value="" minlength="6" maxlength="32" />
            </div>

            <div class="field">
                <div class="label">
                    <strong>Omega Sequence</strong>
                </div>
                <input type="hidden" name="user_omega" value="<?= $current_user_info['user_omega'] ?>" />
                <input class="textbox" type="text" name="user_omega" value="<?= $current_user_info['user_omega'] ?>" disabled="disabled" maxlength="32" />
            </div>


        </div>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs[] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup,
        'icon' => '<i class="fa fas fa-greek-omega"></i>',
        'class' => 'float_right icon_only'
        );
    }

}

/*
// Generate markup for EXTRA SETTINGS if applicable
if (true){

    // Define the markup for this section
    $tab_token = 'extra_settings';
    $tab_name = 'Extra Settings';
    ob_start();
    ?>

        <p>[extra settings]</p>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs[] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup
        );
    }

}
*/

// Check to see which tab token is the "current" one
$current_tab_token = $settings_tabs[0]['token'];
$allowed_tab_tokens = array_map(function($a){ return $a['token']; }, $settings_tabs);
if (!empty($_REQUEST['current_tab']) && in_array($_REQUEST['current_tab'], $allowed_tab_tokens)){
    $current_tab_token = $_REQUEST['current_tab'];
} elseif (!empty($_SESSION['mmrpg_forms']['current_tab']) && in_array($_SESSION['mmrpg_forms']['current_tab'], $allowed_tab_tokens)){
    $current_tab_token = $_SESSION['mmrpg_forms']['current_tab'];
    unset($_SESSION['mmrpg_forms']['current_tab']);
}

// DEBUG DEBUG DEBUG
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Game Settings | Mega Man RPG Prototype | Last Updated <?= mmrpg_print_cache_date() ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="items" content="noindex,nofollow" />
<meta name="darkreader-lock" content="already-dark-mode" />
<meta name="format-detection" content="telephone=no" />
<link rel="shortcut icon" type="image/x-icon" href="images/assets/<?= mmrpg_get_favicon() ?>">
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/solid.css" rel="stylesheet" />
<link type="text/css" href=".libs/fontawesome/v5.6.3/css/fontawesome.css" rel="stylesheet" />
<link type="text/css" href=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.css" rel="stylesheet" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/settings.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
</head>
<body id="mmrpg" class="iframe" data-frame="settings" data-mode="<?= $global_allow_editing ? 'editor' : 'viewer' ?>" data-source="<?= $global_frame_source ?>">
    <div id="prototype" class="hidden" style="opacity: 0;">
        <div id="settings" class="menu">

            <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                <span class="count">
                    <i class="fa fas fa-cog"></i>
                    Game Settings
                </span>
            </span>

            <div class="settings_panel">
                <div class="wrapper">
                    <form class="form" method="post">

                        <input type="hidden" name="user_id" value="<?= $current_user_id ?>" />
                        <input type="hidden" name="current_tab" value="<?= $current_tab_token ?>" />

                        <div class="tab_links">
                            <? foreach ($settings_tabs AS $tab_key => $tab_info){ ?>
                                <a class="link<?= $tab_info['token'] === $current_tab_token ? ' active' : '' ?><?= !empty($tab_info['class']) ? ' '.$tab_info['class'] : '' ?>" data-tab="<?= $tab_info['token'] ?>">
                                    <span class="name"><?= $tab_info['name'] ?></span>
                                    <?= !empty($tab_info['icon']) ? '<span class="icon">'.$tab_info['icon'].'</span>' : '' ?>
                                </a>
                            <? } ?>
                        </div>

                        <div class="tab_sections">
                            <? mmrpg_print_form_messages() ?>
                            <? foreach ($settings_tabs AS $tab_key => $tab_info){ ?>
                                <div class="section<?= $tab_info['token'] === $current_tab_token ? ' active' : '' ?>" data-tab="<?= $tab_info['token'] ?>">
                                    <input type="hidden" name="form_actions[]" value="<?= $tab_info['token'] ?>" />
                                    <div>
                                        <?= $tab_info['markup'] ?>
                                    </div>
                                </div>
                            <? } ?>
                        </div>

                        <div class="tab_buttons">
                            <input class="button save clickonce type type_nature" type="submit" value="Save Changes" />
                            <input class="button reset clickonce type type_flame" type="reset" value="Discard Changes" onclick="javascript: window.location.href = window.location.href;" />
                        </div>

                    </form>
                </div>
            </div>

            <? /*
            <pre><?= '$current_user_info = '.print_r($current_user_info, true) ?></pre>
            */ ?>

        </div>
    </div>
    <script type="text/javascript" src=".libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
    <script type="text/javascript" src=".libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script>
    <script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript" src="scripts/settings.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
    <script type="text/javascript">
    // Update game settings for this page
    <? require_once(MMRPG_CONFIG_ROOTDIR.'scripts/gamesettings.js.php'); ?>
    gameSettings.autoScrollTop = false;
    </script>
    <script type="text/javascript">
    // Print out profile settings in case we need to update parent frame
    var profileSettings = <?= json_encode(array(
        'user_name_display' => (!empty($current_user_info['user_name_public']) ? $current_user_info['user_name_public'] : $current_user_info['user_name']),
        'user_image_path' => $current_user_info['user_image_path'],
        'user_background_path' => $current_user_info['user_background_path'],
        'user_colour_token' => $current_user_info['user_colour_token'],
        'user_colour_token2' => $current_user_info['user_colour_token2']
        )) ?>;
    </script>
    <?
    // Google Analytics
    if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'includes/analytics.php'); }
    ?>
</body>
</html>
<?
// Unset the database variable
unset($db);
?>