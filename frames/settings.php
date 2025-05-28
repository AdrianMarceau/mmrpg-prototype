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
//require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
//require(MMRPG_CONFIG_ROOTDIR.'database/players.php');
//require(MMRPG_CONFIG_ROOTDIR.'database/robots.php');
//require(MMRPG_CONFIG_ROOTDIR.'database/items.php');
$mmrpg_database_types = rpg_type::get_index(true);
$mmrpg_database_players = rpg_player::get_index(true);
$mmrpg_database_robots = rpg_robot::get_index(true);
$mmrpg_database_items = rpg_item::get_index(true);

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
    FROM `mmrpg_users` AS `users`
    LEFT JOIN `mmrpg_roles` AS `roles` ON `roles`.`role_id` = `users`.`role_id`
    WHERE `user_id` = {$current_user_id}
    ;");


// Collect options markup for various dropdowns later
$profile_avatar_options_markup = mmrpg_prototype_get_profile_avatar_options($current_user_info, $allowed_avatar_options);
$profile_colour_options_markup = mmrpg_prototype_get_profile_colour_options($current_user_info, $allowed_colour_options);
$profile_background_options_markup = mmrpg_prototype_get_profile_background_options($current_user_info, $allowed_background_options);

// If the option has been unlocked, collect the proxy options as well
$current_proxy_info = array();
if (mmrpg_prototype_item_unlocked('light-program')){

    // Collect available proxy options given this user's current data and progress
    $proxy_image_options_markup = mmrpg_prototype_get_proxy_image_options($current_user_info, $allowed_proxy_image_options);
    $proxy_bonus_options_markup = mmrpg_prototype_get_proxy_bonus_options($current_user_info, $allowed_proxy_bonus_options);
    $proxy_field_options_markup = mmrpg_prototype_get_proxy_field_options($current_user_info, $allowed_proxy_field_options);
    $proxy_robot_options_markup = mmrpg_prototype_get_proxy_robot_options($current_user_info, $allowed_proxy_robot_options);

    // Collect the current proxy info from the database if it exists
    $current_proxy_info = $db->get_array("SELECT
        `proxies`.`proxy_id`,
        `proxies`.`user_id`,
        `proxies`.`proxy_player`,
        `proxies`.`proxy_image`,
        `proxies`.`proxy_bonus`,
        `proxies`.`proxy_fields`,
        `proxies`.`proxy_robots`,
        `proxies`.`proxy_date_created`,
        `proxies`.`proxy_date_modified`,
        `proxies`.`proxy_flag_enabled`
        FROM `mmrpg_users_proxies` AS `proxies`
        WHERE `user_id` = {$current_user_id}
        ;");

    // If a proxy for this user doesn't exist yet, create a template array
    if (empty($current_proxy_info)){
        $current_proxy_info = array(
            'proxy_id' => 0,
            'user_id' => $current_user_id,
            'proxy_player' => 'player',
            'proxy_image' => 'player',
            'proxy_bonus' => '',
            'proxy_fields' => '',
            'proxy_robots' => '',
            'proxy_date_created' => time(),
            'proxy_date_modified' => 0,
            'proxy_flag_enabled' => 0,
            );
    }

    // Break apart any json-encoded proxy fields
    $current_proxy_info['proxy_fields'] = !empty($current_proxy_info['proxy_fields']) ? json_decode($current_proxy_info['proxy_fields'], true) : array();
    $current_proxy_info['proxy_robots'] = !empty($current_proxy_info['proxy_robots']) ? json_decode($current_proxy_info['proxy_robots'], true) : array();
    $current_proxy_info['proxy_fields'] = array_values($current_proxy_info['proxy_fields']);
    $current_proxy_info['proxy_robots'] = array_values($current_proxy_info['proxy_robots']);

}


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

    // Define an update function for the "Game Settings" tab
    $update_functions['misc_settings'] = function() use (&$updated_tabs, &$form_messages, &$form_data){
        global $db, $current_user_id, $current_user_info;

        $form_data = array();

        $form_data['masterVolume'] = !empty($_POST['masterVolume']) && is_numeric($_POST['masterVolume']) ? $_POST['masterVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_MASTERVOLUME;
        $form_data['musicVolume'] = isset($_POST['musicVolume']) && is_numeric($_POST['musicVolume']) ? $_POST['musicVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_MUSICVOLUME;
        $form_data['effectVolume'] = isset($_POST['effectVolume']) && is_numeric($_POST['effectVolume']) ? $_POST['effectVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_EFFECTVOLUME;

        $allowed_button_modes = array('default', 'classic');
        $form_data['battleButtonMode'] = !empty($_POST['battleButtonMode']) && in_array($_POST['battleButtonMode'], $allowed_button_modes) ? $_POST['battleButtonMode'] : $allowed_button_modes[0];

        //error_log('$form_data = '.print_r($form_data, true));

        $audioBalanceConfig = array();
        $audioBalanceConfig['masterVolume'] = $form_data['masterVolume'] >= 0 && $form_data['masterVolume'] <= 1 ? $form_data['masterVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_MASTERVOLUME;
        $audioBalanceConfig['musicVolume'] = $form_data['musicVolume'] >= 0 && $form_data['musicVolume'] <= 1 ? $form_data['musicVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_MUSICVOLUME;
        $audioBalanceConfig['effectVolume'] = $form_data['effectVolume'] >= 0 && $form_data['effectVolume'] <= 1 ? $form_data['effectVolume'] : MMRPG_SETTINGS_AUDIODEFAULT_EFFECTVOLUME;
        //error_log('$audioBalanceConfig = '.print_r($audioBalanceConfig, true));

        $battleButtonMode = !empty($form_data['battleButtonMode']) ? $form_data['battleButtonMode'] : $allowed_button_modes[0];
        //error_log('$battleButtonMode = '.print_r($battleButtonMode, true));

        $session_token = rpg_game::session_token();
        $_SESSION[$session_token]['battle_settings']['audioBalanceConfig'] = $audioBalanceConfig;
        $_SESSION[$session_token]['battle_settings']['battleButtonMode'] = $battleButtonMode;

        //error_log('(A) $_SESSION[$session_token][\'battle_settings\'][\'audioBalanceConfig\'] = '.print_r($_SESSION[$session_token]['battle_settings']['audioBalanceConfig'], true));

        return true;

        };

    // Define an update function for the "Player Settings" tab
    $update_functions['proxy_settings'] = function() use (&$updated_tabs, &$form_messages, &$form_data, &$current_proxy_info){
        global $db, $current_user_id, $current_user_info;

        $form_data = array();

        $form_data['proxy_image'] = !empty($_POST['user_proxy_image']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['user_proxy_image']) ? trim(strtolower($_POST['user_proxy_image'])) : '';
        $form_data['proxy_bonus'] = !empty($_POST['user_proxy_bonus']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['user_proxy_bonus']) ? trim(strtolower($_POST['user_proxy_bonus'])) : '';
        $form_data['proxy_fields'] = !empty($_POST['user_proxy_fields']) && is_array($_POST['user_proxy_fields']) ? $_POST['user_proxy_fields'] : array();
        $form_data['proxy_robots'] = !empty($_POST['user_proxy_robots']) && is_array($_POST['user_proxy_robots']) ? $_POST['user_proxy_robots'] : array();

        $form_data['proxy_fields'] = array_unique(array_filter($form_data['proxy_fields']));
        $form_data['proxy_robots'] = array_unique(array_filter($form_data['proxy_robots']));

        //error_log('$form_data = '.print_r($form_data, true));

        if (!empty($form_data['proxy_robots'])){
            $form_data['proxy_robots'] = array_filter($form_data['proxy_robots'], function($robot_token){
                global $allowed_proxy_robot_options;
                return in_array($robot_token, $allowed_proxy_robot_options);
                });
            if (empty($form_data['proxy_robots'])){
                $form_messages[] = array('warning', 'Some of the selected robots were invalid and have been removed');
            }
        }

        if (empty($form_data)){ return false; }

        $form_data['proxy_image'] = preg_replace('/_base$/i', '', $form_data['proxy_image']);
        $form_data['proxy_fields'] = json_encode($form_data['proxy_fields']);
        $form_data['proxy_robots'] = json_encode($form_data['proxy_robots']);

        if (!empty($current_proxy_info['proxy_id'])){
            $update_results = $db->update('mmrpg_users_proxies',
                array_merge($form_data, array('proxy_date_modified' => time())),
                array('user_id' => $current_user_id)
                );
        } else {
            $form_data['user_id'] = $current_user_id;
            $form_data['proxy_date_created'] = time();
            $form_data['proxy_date_modified'] = time();
            $form_data['proxy_flag_enabled'] = 1;
            $update_results = $db->insert('mmrpg_users_proxies', $form_data);
        }

        return true;

        };

    // Define an update function for the "Performance Settings" tab
    $update_functions['performance_settings'] = function() use (&$updated_tabs, &$form_messages, &$form_data){
        global $db, $current_user_id, $current_user_info;

        $session_token = rpg_game::session_token();
        $form_data = array();
        //error_log('$_POST = '.print_r($_POST, true));

        // spriteRenderMode
        $allowed_render_modes = array('default', 'crisp-edges', 'pixelated');
        $form_data['spriteRenderMode'] = !empty($_POST['spriteRenderMode']) && in_array($_POST['spriteRenderMode'], $allowed_render_modes) ? $_POST['spriteRenderMode'] : $allowed_render_modes[0];
        //error_log('$form_data = '.print_r($form_data, true));
        $_SESSION[$session_token]['battle_settings']['spriteRenderMode'] = $form_data['spriteRenderMode'];

        // allowReadyRoomSprites, readyRoomSpriteMotion, readyRoomSpriteLimit
        if (isset($_POST['allowReadyRoomSprites'])){ $form_data['allowReadyRoomSprites'] = $_POST['allowReadyRoomSprites'] === '0' ? 0 : 1; }
        if (isset($_POST['readyRoomSpriteMotion'])){ $form_data['readyRoomSpriteMotion'] = $_POST['readyRoomSpriteMotion'] === '0' ? 0 : 1; }
        if (isset($_POST['readyRoomSpriteLimit'])){
            $form_data['readyRoomSpriteLimit'] = intval($_POST['readyRoomSpriteLimit']);
            if ($form_data['readyRoomSpriteLimit'] <= 0){ $form_data['readyRoomSpriteLimit'] = 1; }
            if ($form_data['readyRoomSpriteLimit'] >= 100){ $form_data['readyRoomSpriteLimit'] = 100; }
        }
        $readyRoomConfig = rpg_game::get_readyRoomConfig(true);
        $readyRoomConfig['allowReadyRoomSprites'] = $form_data['allowReadyRoomSprites'];
        $readyRoomConfig['readyRoomSpriteMotion'] = $form_data['readyRoomSpriteMotion'];
        $readyRoomConfig['readyRoomSpriteLimit'] = $form_data['readyRoomSpriteLimit'];
        //error_log('$readyRoomConfig = '.print_r($readyRoomConfig, true));
        $_SESSION[$session_token]['battle_settings']['readyRoomConfig'] = $readyRoomConfig;

        // allowMenuButtonSprites, menuButtonSpriteMotion, menuButtonSpriteLimit
        if (isset($_POST['allowMenuButtonSprites'])){ $form_data['allowMenuButtonSprites'] = $_POST['allowMenuButtonSprites'] === '0' ? 0 : 1; }
        if (isset($_POST['menuButtonSpriteMotion'])){ $form_data['menuButtonSpriteMotion'] = $_POST['menuButtonSpriteMotion'] === '0' ? 0 : 1; }
        if (isset($_POST['menuButtonSpriteLimit'])){
            $form_data['menuButtonSpriteLimit'] = intval($_POST['menuButtonSpriteLimit']);
            if ($form_data['menuButtonSpriteLimit'] <= 0){ $form_data['menuButtonSpriteLimit'] = 1; }
            if ($form_data['menuButtonSpriteLimit'] >= 100){ $form_data['menuButtonSpriteLimit'] = 100; }
        }
        $menuButtonConfig = rpg_game::get_menuButtonConfig(true);
        $menuButtonConfig['allowMenuButtonSprites'] = $form_data['allowMenuButtonSprites'];
        $menuButtonConfig['menuButtonSpriteMotion'] = $form_data['menuButtonSpriteMotion'];
        $menuButtonConfig['menuButtonSpriteLimit'] = $form_data['menuButtonSpriteLimit'];
        //error_log('$menuButtonConfig = '.print_r($menuButtonConfig, true));
        $_SESSION[$session_token]['battle_settings']['menuButtonConfig'] = $menuButtonConfig;

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
$settings_tabs_index = array();
$settings_tabs_order = array('account_settings', 'profile_settings', 'proxy_settings', 'audio_settings', 'performance_settings', 'misc_settings', 'advanced_settings', 'omega_settings');


// Generate markup for AUDIO SETTINGS if applicable
if (true){

    // Define the markup for this section
    $tab_token = 'audio_settings';
    $tab_name = 'Audio'; // 'Audio Settings';
    ob_start();
    ?>

        <div class="game-settings audio-settings">

            <?

            // Collect current values if they exist so we can display them as such
            $session_token = rpg_game::session_token();
            $battleSettings = $_SESSION[$session_token]['battle_settings'];
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
                        <input class="slider" type="range" name="masterVolume" min="0" max="1" step="0.01" data-percent="true" data-min-text="Muted" value="<?= $audioBalanceConfig['masterVolume'] ?>">
                    </div>
                    <div class="subfield">
                        <label class="label" for="musicVolume">Music Volume</label>
                        <input class="slider" type="range" name="musicVolume" min="0" max="1" step="0.01" data-percent="true" data-min-text="Muted" value="<?= $audioBalanceConfig['musicVolume'] ?>">
                    </div>
                    <div class="subfield">
                        <label class="label" for="effectVolume">SFX Volume <sup class="help">* on supported devices</sup></label>
                        <input class="slider" type="range" name="effectVolume" min="0" max="1" step="0.01" data-percent="true" data-min-text="Muted" value="<?= $audioBalanceConfig['effectVolume'] ?>">
                    </div>
                </div>
            </div>

        </div>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs_index[$tab_token] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup
        );
    }

}

// Generate markup for PERFORMACE TWEAKS if applicable
if (true){

    // Define the markup for this section
    $tab_token = 'performance_settings';
    $tab_name = 'Performance'; // 'Performance Tweaks';
    ob_start();
    ?>

        <div class="game-settings performance-settings">

            <?

            // Collect current values if they exist so we can display them as such
            $session_token = rpg_game::session_token();
            $battleSettings = $_SESSION[$session_token]['battle_settings'];
            $spriteRenderMode = isset($battleSettings['spriteRenderMode']) ? $battleSettings['spriteRenderMode'] : 'default';
            $battleButtonMode = isset($battleSettings['battleButtonMode']) ? $battleSettings['battleButtonMode'] : 'default';
            $readyRoomConfig = rpg_game::get_readyRoomConfig(true);
            $menuButtonConfig = rpg_game::get_menuButtonConfig(true);
            //error_log('$readyRoomConfig = '.print_r($readyRoomConfig, true));
            //error_log('$menuButtonConfig = '.print_r($menuButtonConfig, true));

            ?>

            <div class="field" data-setting="performanceTweaks">

                <div class="label">
                    <strong>Home / Main Menu</strong>
                </div>

                <div class="subfield input-group is-yes-no">
                    <label class="label" for="allowReadyRoomSprites">Ready Room</label>
                    <? $active = !empty($readyRoomConfig['allowReadyRoomSprites']); ?>
                    <div class="radiofield is-yes <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="allowReadyRoomSprites" value="1" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="allowReadyRoomSprites[0]">Enabled</label>
                    </div>
                    <? $active = empty($readyRoomConfig['allowReadyRoomSprites']); ?>
                    <div class="radiofield is-no <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="allowReadyRoomSprites" value="0" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="allowReadyRoomSprites[1]">Disabled</label>
                    </div>
                </div>
                <div class="subfield input-group is-yes-no">
                    <label class="label" for="readyRoomSpriteMotion">Ready Room Sprite Motion</label>
                    <? $active = !empty($readyRoomConfig['readyRoomSpriteMotion']); ?>
                    <div class="radiofield is-yes <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="readyRoomSpriteMotion" value="1" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="readyRoomSpriteMotion[0]">Yes</label>
                    </div>
                    <? $active = empty($readyRoomConfig['readyRoomSpriteMotion']); ?>
                    <div class="radiofield is-no <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="readyRoomSpriteMotion" value="0" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="readyRoomSpriteMotion[1]">No</label>
                    </div>
                </div>
                <div class="subfield input-group">
                    <div class="subfield">
                        <label class="label" for="readyRoomSpriteLimit">Ready Room Sprite Limit <sup class="help">(per player)</sup></label>
                        <input class="slider" type="range" name="readyRoomSpriteLimit" min="1" max="100" data-max-text="No Limit" step="1" value="<?= $readyRoomConfig['readyRoomSpriteLimit'] ?>">
                    </div>
                </div>

                <div class="subfield input-group is-yes-no">
                    <label class="label" for="allowMenuButtonSprites">Menu Sprites</label>
                    <? $active = !empty($menuButtonConfig['allowMenuButtonSprites']); ?>
                    <div class="radiofield is-yes <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="allowMenuButtonSprites" value="1" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="allowMenuButtonSprites[0]">Enabled</label>
                    </div>
                    <? $active = empty($menuButtonConfig['allowMenuButtonSprites']); ?>
                    <div class="radiofield is-no <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="allowMenuButtonSprites" value="0" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="allowMenuButtonSprites[1]">Disabled</label>
                    </div>
                </div>
                <div class="subfield input-group is-yes-no">
                    <label class="label" for="menuButtonSpriteMotion">Menu Sprite Motion</label>
                    <? $active = !empty($menuButtonConfig['menuButtonSpriteMotion']); ?>
                    <div class="radiofield is-yes <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="menuButtonSpriteMotion" value="1" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="menuButtonSpriteMotion[0]">Yes</label>
                    </div>
                    <? $active = empty($menuButtonConfig['menuButtonSpriteMotion']); ?>
                    <div class="radiofield is-no <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="menuButtonSpriteMotion" value="0" <?= $active ? 'checked="checked"' : '' ?> />
                        <label for="menuButtonSpriteMotion[1]">No</label>
                    </div>
                </div>
                <div class="subfield input-group">
                    <div class="subfield">
                        <label class="label" for="menuButtonSpriteLimit">Menu Sprite Limit <sup class="help">(per button)</sup></label>
                        <input class="slider" type="range" name="menuButtonSpriteLimit" min="1" max="100" data-max-text="No Limit" step="1" value="<?= $menuButtonConfig['menuButtonSpriteLimit'] ?>">
                    </div>
                </div>

            </div>

            <div class="field" data-setting="spriteRenderMode">
                <div class="label">
                    <strong>Sprite Rendering</strong>
                </div>
                <div class="subfield input-group">
                    <label class="label full" for="spriteRenderMode">Scaling Method</label>
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
                                $px = 40 + (20 * ($i * 1));
                                $os = $size === 40 ? 'left' : 'right';
                                $opx = -10 + ($i * ($px / 2)) - ($i * $i * 3);
                                if ($size === 40){ $opx += 12; }
                                elseif ($size === 80){ $opx += 18; }
                                ?>
                                <div class="sprite" style="
                                    background-image: url('<?= $path ?>');
                                    width: <?= $px ?>px;
                                    height: <?= $px ?>px;
                                    bottom: 0;
                                    <?= $os ?>: <?= $opx ?>px;
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
        $settings_tabs_index[$tab_token] = array(
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
    $tab_name = 'Account'; //'Account Settings';
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
        $settings_tabs_index[$tab_token] = array(
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
    $tab_name = 'Profile'; // 'Profile Settings';
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
        $settings_tabs_index[$tab_token] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup
        );
    }

}

// Generate markup for PROXY SETTINGS if applicable
if (mmrpg_prototype_item_unlocked('light-program')){

    // Define the markup for this section
    //error_log('$current_proxy_info = '.print_r($current_proxy_info, true));
    $tab_token = 'proxy_settings';
    $tab_name = 'Proxy'; // 'Proxy Settings';
    ob_start();
    ?>

        <div class="game-settings proxy-settings">

            <p class="description">
                Your <strong>Proxy Settings</strong> are used whenever another user challenges your ghost data to a Player Battle.
                Use the fields below to customize how your proxy behaves, or leave the dropdowns blank to let the system bots
                automatically decide for you.  Have fun!
            </p>

            <div class="subwrap">

                <div class="field player-avatar">
                    <strong class="label">Player Avatar</strong>
                    <select class="select" name="user_proxy_image">
                        <?= str_replace(
                            'value="'.$current_proxy_info['proxy_image'].'"',
                            'value="'.$current_proxy_info['proxy_image'].'" selected="selected"',
                            $proxy_image_options_markup
                            ) ?>
                    </select>
                    <div class="preview">
                        <?
                        $existing_player_image = $current_proxy_info['proxy_image'];
                        if (empty($existing_player_image)){ $existing_player_image = 'player'; }
                        $player_sprite_path = 'images/players/'.$existing_player_image.'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
                        $preview_sprite_class = 'sprite sprite_40x40 sprite_40x40_base';
                        $preview_sprite_styles = 'background-image: url('.$player_sprite_path.'); ';
                        echo('<div class="'.$preview_sprite_class.'" style="'.$preview_sprite_styles.'"></div>');
                        ?>
                    </div>
                </div>

                <div class="field player-bonus">
                    <strong class="label">Player Bonus</strong>
                    <select class="select" name="user_proxy_bonus">
                        <?= str_replace(
                            'value="'.$current_proxy_info['proxy_bonus'].'"',
                            'value="'.$current_proxy_info['proxy_bonus'].'" selected="selected"',
                            $proxy_bonus_options_markup
                            ) ?>
                    </select>
                </div>

                <div class="field player-field">
                    <strong class="label">Battle Field Base <em>background</em></strong>
                    <select class="select" name="user_proxy_fields[]">
                        <?= !empty($current_proxy_info['proxy_fields'][0]) ? str_replace(
                            'value="'.$current_proxy_info['proxy_fields'][0].'"',
                            'value="'.$current_proxy_info['proxy_fields'][0].'" selected="selected"',
                            $proxy_field_options_markup
                            ) : $proxy_field_options_markup ?>
                    </select>
                </div>

                <div class="field player-field">
                    <strong class="label">Battle Field Fusion <em>foreground</em></strong>
                    <select class="select" name="user_proxy_fields[]">
                        <?= !empty($current_proxy_info['proxy_fields'][1]) ? str_replace(
                            'value="'.$current_proxy_info['proxy_fields'][1].'"',
                            'value="'.$current_proxy_info['proxy_fields'][1].'" selected="selected"',
                            $proxy_field_options_markup
                            ) : $proxy_field_options_markup ?>
                    </select>
                </div>

                <div class="field fullsize player-robots">
                    <strong class="label">Robot Team Roster <em>stats, items, alts, and abilities are automatically pulled from your save</em></strong>
                    <div class="subfield" style="padding-right: 0;">
                        <? for ($i = 0; $i < 6; $i++){ ?>
                            <div style="float: left; margin: 0 10px 5px 0; width: calc(33% - 10px);"><select class="select" name="user_proxy_robots[]">
                                <?= !empty($current_proxy_info['proxy_robots'][$i]) ? str_replace(
                                    'value="'.$current_proxy_info['proxy_robots'][$i].'"',
                                    'value="'.$current_proxy_info['proxy_robots'][$i].'" selected="selected"',
                                    $proxy_robot_options_markup
                                    ) : $proxy_robot_options_markup ?>
                            </select></div>
                        <? } ?>
                    </div>
                </div>

            </div>

        </div>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs_index[$tab_token] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup
        );
    }

}

/*
// Generate markup for MISC SETTINGS if applicable
if (true){

    // Define the markup for this section
    $tab_token = 'misc_settings';
    $tab_name = 'Misc'; // 'Game Settings';
    ob_start();
    ?>

        <div class="game-settings misc-settings">

            <p>&hellip;</p>

        </div>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs_index[$tab_token] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup
        );
    }

}
*/

// Generate markup for ADVANCED SETTINGS if applicable
if (true){

    // Pre-check to see if we have any progress to actually reset
    $session_token = rpg_game::session_token();

    // Check to see if we can show the New Game + section at all
    $completed_campaigns = mmrpg_prototype_complete();
    $battles_complete = mmrpg_prototype_battle_tokens_complete();
    //error_log('$battles_complete = '.print_r($battles_complete, true));
    $new_game_plus_visible = $completed_campaigns >= 1 ? true : false;
    if (MMRPG_CONFIG_IS_LIVE === false){ $new_game_plus_visible = true; } // Force visible for local testing
    $new_game_plus_available = array('dr-light', 'dr-wily', 'dr-cossack', 'dr-lalinde', 'proxy');
    $new_game_plus_options = '';
    if ($new_game_plus_visible){

        // Start the output buffer to collect new game plus options
        ob_start();
        ?>
            <?
            // Loop through available players and display active or disabled buttoned given allowances
            foreach ($new_game_plus_available AS $temp_playerkey => $temp_playertoken){
            //foreach ($mmrpg_database_players AS $temp_playertoken => $temp_playerinfo){
            //foreach ($new_game_plus_available AS $new_game_player => $new_game_allowed){
                $new_game_player = $temp_playertoken;
                $new_game_player_short = preg_replace('/^([a-z0-9]+\-)/', '', $new_game_player);
                $new_game_player_info = array();
                $new_game_allowed = false;
                $temp_buttonsize = $new_game_player === 'proxy' ? 'fullsize' : 'halfsize';
                if (isset($mmrpg_database_players[$new_game_player])){
                    $new_game_player_info = $mmrpg_database_players[$new_game_player];
                    $new_game_allowed = mmrpg_prototype_complete($new_game_player) ? true : false;
                }
                if ($new_game_allowed && !empty($new_game_player_info)){
                    ?>
                    <button type="button"
                        class="button <?= $temp_buttonsize ?> no-fade type type_<?= $new_game_player_info['player_type'] ?> button_reset button_reset_player button_reset_<?= $new_game_player_short ?>"
                        onclick="javascript:parent.window.mmrpg_trigger_new_game_plus(this, '<?= $new_game_player ?>', '<?= $new_game_player_info['player_name'] ?>');"
                        >
                        <strong><?= $new_game_player_info['player_name'] ?> &bull; New Game <i class="fa fas fa-plus-circle"></i></strong>
                        <em>(restart campaign but keep all other progress)</em>
                    </button>
                    <?
                } else {
                    ?>
                    <button type="button" class="button <?= $temp_buttonsize ?> type type_empty button_reset button_reset_player button_reset_<?= $new_game_player_short ?> disabled" disabled="disabled">
                        <strong>???</strong>
                        <em>(&hellip;)</em>
                    </button>
                    <?
                }
            }
            ?>

        <?
        // Collect the output buffer and add it to the main output buffer
        $new_game_plus_options .= trim(ob_get_clean());

    }

    // Only show the next part if the user has unlocked completed at least one game
    $reset_game_sub_options = '';
    if (true){

        // Start the output buffer to collect new game plus options
        ob_start();
        ?>
            <button type="button"
                name="reset"
                class="button fullsize type type_flame button_reset button_reset_all"
                onclick="javascript:parent.window.mmrpg_trigger_reset(true);"
                >
                <strong><i class="fa fas fa-trash"></i> Reset Game</strong>
                <em>(delete literally everything and start from scratch)</em>
            </button>
        <?
        // Collect the output buffer and add it to the main output buffer
        $reset_game_sub_options .= trim(ob_get_clean());

    }

    // Define the markup for this section
    $tab_token = 'advanced_settings';
    $tab_name = 'Advanced'; // 'Advanced Settings';
    ob_start();
    ?>

        <div class="game-settings advanced-settings">

            <p class="description" style="text-align: center;">
                <i class="fa fas fa-exclamation-triangle"></i> <strong>Stop!</strong>
                Please be careful when using the game options below;
                their effects are permanent and cannot be undone!
            </p>

            <div class="field buttons sub-buttons" data-setting="newGamePlus">
                <? /*<div class="label">
                    <i class="fas fa-cogs"></i> &nbsp; <strong>Advanced Game Options</strong>
                </div> */ ?>
                <div class="wrapper">
                    <?= $new_game_plus_options ?>
                    <?= $reset_game_sub_options ?>
                </div>
            </div>

        </div>

    <?
    $tab_markup = trim(ob_get_clean());
    if (!empty($tab_markup)){
        $settings_tabs_index[$tab_token] = array(
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
    $tab_name = 'Omega'; // 'Omega Settings';
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
        $settings_tabs_index[$tab_token] = array(
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
        $settings_tabs_index[$tab_token] = array(
        'token' => $tab_token,
        'name' => $tab_name,
        'markup' => $tab_markup
        );
    }

}
*/

// Check to see which tab token is the "current" one
$current_tab_token = $settings_tabs_order[0];
$allowed_tab_tokens = array_map(function($a){ return $a['token']; }, $settings_tabs_index);
if (!empty($_REQUEST['current_tab']) && in_array($_REQUEST['current_tab'], $allowed_tab_tokens)){
    $current_tab_token = $_REQUEST['current_tab'];
} elseif (!empty($_SESSION['mmrpg_forms']['current_tab']) && in_array($_SESSION['mmrpg_forms']['current_tab'], $allowed_tab_tokens)){
    $current_tab_token = $_SESSION['mmrpg_forms']['current_tab'];
    unset($_SESSION['mmrpg_forms']['current_tab']);
}


// DEBUG DEBUG DEBUG
//$current_tab_token = 'advanced_settings';
//error_log('$settings_tabs_order = '.print_r($settings_tabs_order, true));
//error_log('$settings_tabs_index = '.print_r($settings_tabs_index, true));

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Settings | Prototype | Mega Man RPG Prototype</title>
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
                            <? foreach ($settings_tabs_order AS $tab_key => $tab_token){ ?>
                                <? if (!isset($settings_tabs_index[$tab_token])){ continue; } ?>
                                <? $tab_info = $settings_tabs_index[$tab_token]; ?>
                                <a class="link<?= $tab_info['token'] === $current_tab_token ? ' active' : '' ?><?= !empty($tab_info['class']) ? ' '.$tab_info['class'] : '' ?>" data-tab="<?= $tab_info['token'] ?>">
                                    <span class="name"><?= $tab_info['name'] ?></span>
                                    <?= !empty($tab_info['icon']) ? '<span class="icon">'.$tab_info['icon'].'</span>' : '' ?>
                                </a>
                            <? } ?>
                        </div>

                        <div class="tab_sections">
                            <? mmrpg_print_form_messages() ?>
                            <? foreach ($settings_tabs_order AS $tab_key => $tab_token){ ?>
                                <? if (!isset($settings_tabs_index[$tab_token])){ continue; } ?>
                                <? $tab_info = $settings_tabs_index[$tab_token]; ?>
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