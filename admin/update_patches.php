<?

// -- STAT MAX LIMIT UPDATE -- //

// Define a patch function for applying the max robot stats update
$token = 'stat_max_limit_update';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Balancing Max Limits for Robot Stats';
$update_patch_details[$token] = "Robot stats are no longer capped at a max of 9999 and are instead based on each robot's base values.";
$update_patch_details[$token] .= "\nEach stat can now only be boosted to maximum of ".(MMRPG_SETTINGS_STATS_BONUS_MAX + 1)."x its base value at level 100.";
$update_patch_details[$token] .= "\n".'Any overflow of stats that are now considered too high will be converted to zenny for the player.';
function mmrpg_patch_stat_max_limit_update($_GAME){ /* removed */ }


// -- BATTLE POINT REBOOT 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'battle_point_reboot_2k16';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Battle Point Reboot of 2016';
$update_patch_details[$token] = "Battle points are no longer some arbituary number you can grind for all eternity.";
$update_patch_details[$token] .= "\nFrom now on, battle points will be equal to the total sum of all your *best* mission scores combined.";
$update_patch_details[$token] .= "\nWhile replaying missions for better scores is abolutely encouraged, it is no longer necessary to return over and over simply to grind points.";
function mmrpg_patch_battle_point_reboot_2k16($_GAME){ /* removed */ }


// -- PLAYER ABILITY MERGE 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'player_ability_merge_2k16';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Player Ability Merge of 2016';
$update_patch_details[$token] = "Abilities unlocked by any player can now be used by all players. ";
$update_patch_details[$token] .= "\nIt is no longer necessary to trade robots back and forth to customize ";
$update_patch_details[$token] .= "\nthe perfect moveset, and I hope everyone will enjoy this new change. :) ";
function mmrpg_patch_player_ability_merge_2k16($_GAME){ /* removed */ }


// -- PLAYER ITEM / ABILITY SPLIT 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'ability_item_split_2k16';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Ability / Item Split of 2016';
$update_patch_details[$token] = "Abilities and items were technically the same thing in the coding of the game \n";
$update_patch_details[$token] .= "and it required a lot of extra coding to get them to work.  In an effort to \n";
$update_patch_details[$token] .= "optimize the game items are being split into their own separate category of \n";
$update_patch_details[$token] .= "objects. This patch simply re-organizes some of that data in your game file. ";
function mmrpg_patch_ability_item_split_2k16($_GAME){ /* removed */ }


// -- STAR FORCE REBOOT 2k16 -- //

// Define a patch function for applying the next update
$token = 'star_force_reboot_2k16';
$update_patch_tokens[] = $token;
$update_patch_tokens_disabled[] = $token;
$update_patch_names[$token] = 'Star Force Reboot of 2016';
$update_patch_details[$token] = "Field and Fusion stars now boost battle points instead of boosting elemental \n";
$update_patch_details[$token] .= "damage and recovery power in battle.  As a result, battle points for all players \n";
$update_patch_details[$token] .= "must be recalculated. ";
function mmrpg_patch_star_force_reboot_2k16($_GAME){ /* removed */ }


// -- DB USER OBJECTS 2k17 -- //

// Define a patch function for applying the next update
$token = 'db_user_objects_2k17';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Database User Objects 2k17';
$update_patch_details[$token] = "The previous storage format for unlocked players, robots, and abilities \n";
$update_patch_details[$token] .= "was ineffecient and needed to be optimized.  This patch should have no  \n";
$update_patch_details[$token] .= "effect on user experience or gameplay and is only for internal purposes. ";
function mmrpg_patch_db_user_objects_2k17($_GAME){

    /*

        // STUFF TO FINISH BEFORE WE LAUNCH THE UPDATE

        -  Make sure all nested arrays in global values are saved in dedicated tables instead

            -  Make sure the shop writes directly to the database on buy/sell actions
            -  Make sure the player editor writes directly to the database on omega factor changes

        -  ???

     */

    /*

        // QUERIES TO RUN BEFORE THE UPDATE!!!!

        -- (STEP 00) BACK UP EVERYTHING JUST IN CASE

        -- No really, DO IT


        -- (STEP 01) CREATE THE LEGACY SAVE DATA TABLE

        CREATE TABLE `mmrpg_saves_legacy` (
            `save_id` MEDIUMINT(8) NOT NULL AUTO_INCREMENT COMMENT 'Save ID',
            `user_id` MEDIUMINT(8) NOT NULL COMMENT 'User ID',
            `save_values_battle_index` MEDIUMTEXT NOT NULL COMMENT 'Battle Index',
            `save_values_battle_complete` MEDIUMTEXT NOT NULL COMMENT 'Battle Complete',
            `save_values_battle_failure` MEDIUMTEXT NOT NULL COMMENT 'Battle Failure',
            `save_values_battle_rewards` MEDIUMTEXT NOT NULL COMMENT 'Battle Rewards',
            `save_values_battle_settings` MEDIUMTEXT NOT NULL COMMENT 'Battle Settings',
            `save_values_battle_items` MEDIUMTEXT NOT NULL COMMENT 'Battle Items',
            `save_values_battle_abilities` MEDIUMTEXT NOT NULL COMMENT 'Battle Abilities',
            `save_values_battle_shops` MEDIUMTEXT NOT NULL COMMENT 'Battle Shops',
            `save_values_battle_fields` MEDIUMTEXT NOT NULL COMMENT 'Battle Fields',
            `save_values_battle_stars` MEDIUMTEXT NOT NULL COMMENT 'Battle Stars',
            `save_values_robot_database` MEDIUMTEXT NOT NULL COMMENT 'Robot Database',
            `save_values_robot_alts` MEDIUMTEXT NOT NULL COMMENT 'Robot Alts',
            `save_values_battle_hearts` MEDIUMTEXT NOT NULL COMMENT 'Battle Hearts',
            `save_values_raw` MEDIUMTEXT NOT NULL COMMENT 'Save Values (Raw)',
            `save_file_name` VARCHAR(128) NOT NULL COMMENT 'Save File Name',
            `save_file_path` VARCHAR(128) NOT NULL COMMENT 'Save File Path',
            `save_patches_applied` TEXT NOT NULL COMMENT 'Save Patches Applied',
            PRIMARY KEY (`save_id`),
            UNIQUE INDEX `user_id` (`user_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=DYNAMIC
            AUTO_INCREMENT=0
            ;


        -- (STEP 02) COPY OVER (RAW) LEGACY SAVE DATA TO THE NEW TABLE

        INSERT INTO mmrpg_saves_legacy (
            save_id,
            user_id,
            save_values_battle_index,
            save_values_battle_complete,
            save_values_battle_failure,
            save_values_battle_rewards,
            save_values_battle_settings,
            save_values_battle_items,
            save_values_battle_abilities,
            save_values_battle_shops,
            save_values_battle_fields,
            save_values_battle_stars,
            save_values_robot_database,
            save_values_robot_alts,
            save_values_battle_hearts,
            save_values_raw,
            save_file_name,
            save_file_path,
            save_patches_applied
            )
        SELECT (
            save_id,
            user_id,
            save_values_battle_index,
            save_values_battle_complete,
            save_values_battle_failure,
            save_values_battle_rewards,
            save_values_battle_settings,
            save_values_battle_items,
            save_values_battle_abilities,
            '', -- shops
            '', -- fields
            save_values_battle_stars,
            save_values_robot_database,
            save_values_robot_alts,
            '', -- hearts
            save_values, -- raw
            save_file_name,
            save_file_path,
            save_patches_applied
            )
        FROM mmrpg_saves
        ORDER BY
            mmrpg_saves.user_id ASC


        -- (STEP 03) DROP LEGACY FIELDS FROM CURRENT SAVE DATA TABLE

        ALTER TABLE mmrpg_saves
            DROP COLUMN save_values_battle_index,
            DROP COLUMN save_values_battle_complete,
            DROP COLUMN save_values_battle_failure,
            DROP COLUMN save_values_battle_rewards,
            DROP COLUMN save_values_battle_settings,
            DROP COLUMN save_values_battle_items,
            DROP COLUMN save_values_battle_abilities,
            DROP COLUMN save_values_battle_stars,
            DROP COLUMN save_values_robot_database,
            DROP COLUMN save_values_robot_alts,
            DROP COLUMN save_file_name,
            DROP COLUMN save_file_path,
            DROP COLUMN save_patches_applied
            ;


        -- (STEP 04) CREATE THE NEW SHOP INDEX

        CREATE TABLE IF NOT EXISTS `mmrpg_index_shops` (
          `shop_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Shop ID',
          `shop_token` varchar(100) NOT NULL DEFAULT '' COMMENT 'Shop Token',
          `shop_name` varchar(100) NOT NULL DEFAULT '' COMMENT 'Shop Name',
          `shop_class` varchar(16) NOT NULL DEFAULT '' COMMENT 'Shop Class',
          `shop_colour` varchar(16) NOT NULL DEFAULT '' COMMENT 'Shop Colour',
          `shop_products_selling` varchar(64) NOT NULL DEFAULT '' COMMENT 'Shop Products Selling',
          `shop_products_buying` varchar(64) NOT NULL DEFAULT '' COMMENT 'Shop Products Buying',
          `shop_flag_hidden` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Shop Flag Hidden',
          `shop_flag_published` smallint(1) NOT NULL DEFAULT '1' COMMENT 'Shop Flag Published',
          `shop_order` smallint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Shop Order',
          PRIMARY KEY (`shop_token`),
          KEY `shop_id` (`shop_id`),
          KEY `shop_token_shop_flag_hidden` (`shop_token`,`shop_flag_hidden`),
          KEY `shop_token_shop_flag_published` (`shop_token`,`shop_flag_published`),
          KEY `shop_token_shop_flag_hidden_shop_flag_published` (`shop_token`,`shop_flag_hidden`,`shop_flag_published`),
          KEY `shop_class` (`shop_class`)
        ) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


        -- (STEP 05) POPULATE THE NEW SHOP INDEX

        INSERT INTO `mmrpg_index_shops` (`shop_id`, `shop_token`, `shop_name`, `shop_class`, `shop_colour`, `shop_products_selling`, `shop_products_buying`, `shop_flag_hidden`, `shop_flag_published`, `shop_order`) VALUES
            (0, 'shop', 'Shop', 'system', 'none', '', '', 1, 0, 0),
            (1, 'auto', 'Auto\'s Shop', 'normal', 'nature', 'items,alts', 'items', 0, 1, 1),
            (2, 'reggae', 'Reggae\'s Shop', 'normal', 'explode', 'abilities,weapons', 'cores', 0, 1, 2),
            (3, 'kalinka', 'Kalinka\'s Shop', 'normal', 'electric', 'items,fields', 'stars', 0, 1, 3);


        -- (STEP 06) ??????

    */

    // Pull in global variables
    global $db;

    // Save the current game session to legacy database fields
    if (defined('MMRPG_UPDATE_GAME_RESET')){
        legacy_rpg_game::session_to_fields($_GAME, $_GAME['user_id']);
    }

    // Collect legacy session vars into current game array
    $legacy_session_vars = legacy_rpg_game::get_session_vars($_GAME['user_id']);
    $_GAME['flags'] = $legacy_session_vars['flags'];
    $_GAME['counters'] = $legacy_session_vars['counters'];
    $_GAME['values'] = $legacy_session_vars['values'];

    // Manually append MM1, MM2, and MM4 fields to unlock list they should have been unlocked
    legacy_rpg_game::fix_battle_fields_array($_GAME);

    // Manually fix the player rewards array by making sure its in the current format
    legacy_rpg_game::fix_player_rewards_array($_GAME);

    // Manually fix the player omega array by making sure it's in the current format
    legacy_rpg_game::fix_player_omega_array($_GAME);

    // Manually migrate any legacy value arrays to the legacy table before proceeding
    if (true){

        // Extract a fresh, unaltered copy of the save values array from the database
        $raw_save_values = $db->get_value("SELECT save_values FROM mmrpg_saves WHERE user_id = {$_GAME['user_id']};", 'save_values');
        $raw_save_values = !empty($raw_save_values) ? json_decode($raw_save_values, true) : array();

        // If an entry for BATTLE SHOPS exists, we should copy to legacy table and remove from current
        if (!empty($raw_save_values['battle_shops'])){

            // Compress the shop data into JSON and then add to the legacy save table row
            $raw_battle_shops = $raw_save_values['battle_shops'];
            $save_values_battle_shops = json_encode($raw_battle_shops);
            $db->update('mmrpg_saves_legacy', array(
                'save_values_battle_shops' => $save_values_battle_shops
                ), array('user_id' => $_GAME['user_id'], 'save_values_battle_shops' => ''));

            // Remove shops from original array to prevent it from being added to values again
            unset($raw_save_values['battle_shops']);

            // Add the collected battle shops to the in-memory values array
            $_GAME['values']['battle_shops'] = $raw_battle_shops;

        }

        // If an entry for BATTLE FIELDS exists, we should copy to legacy table and remove from current
        if (!empty($raw_save_values['battle_fields'])){

            // Compress the shop data into JSON and then add to the legacy save table row
            $raw_battle_fields = $raw_save_values['battle_fields'];
            $save_values_battle_fields = json_encode($raw_battle_fields);
            $db->update('mmrpg_saves_legacy', array(
                'save_values_battle_fields' => $save_values_battle_fields
                ), array('user_id' => $_GAME['user_id'], 'save_values_battle_fields' => ''));

            // Remove fields from original array to prevent it from being added to values again
            unset($raw_save_values['battle_fields']);

            // Add the collected battle fields to the in-memory values array
            $_GAME['values']['battle_fields'] = $raw_battle_fields;

        }

        // Remove any OTHER entries that had no business being there in the first place
        unset($raw_save_values['dr-light_this-item-omega_prototype']);
        unset($raw_save_values['dr-wily_this-item-omega_prototype']);
        unset($raw_save_values['dr-cossack_this-item-omega_prototype']);
        unset($raw_save_values['battle_complete_hash']);
        unset($raw_save_values['battle_failure_hash']);

        // Remove entries that will be added to their own dedicated tables later in the script
        unset($raw_save_values['dr-light_target-robot-omega_prototype']);
        unset($raw_save_values['dr-wily_target-robot-omega_prototype']);
        unset($raw_save_values['dr-cossack_target-robot-omega_prototype']);

    }

    echo("Converting session objects into database objects for user ID {$_GAME['user_id']}... \n\n");

    // Trigger the session to database function to do all the work
    $success = legacy_rpg_game::session_to_database($_GAME, true);

    // Re-compress the newly cleaned values array and update in the current save table row
    if ($success !== false && !empty($raw_save_values)){
        $raw_save_values = json_encode($raw_save_values);
        $db->update('mmrpg_saves', array(
            'save_values' => $raw_save_values
            ), array('user_id' => $_GAME['user_id']));
    }

    // Print the final message of success
    echo("...done! Thank you for your time. :) \n\n");

    // Return the updated game array
    return $_GAME;

    /*

    // QUERIES TO RUN AFTER THE UPDATE!!!!

    -- FIND ALL USER ROBOTS WITH INCORRECT OR MISSING ORIGINAL PLAYERS
    SELECT
        urobots.user_id,
        irobots.robot_token,
        irobots.robot_game,
        urobots.robot_player,
        urobots.robot_player_original,
        iplayers.player_token AS correct_player_original
        FROM mmrpg_users_robots AS urobots
        LEFT JOIN mmrpg_index_robots AS irobots ON irobots.robot_token = urobots.robot_token
        LEFT JOIN mmrpg_index_players AS iplayers ON (
            iplayers.player_game = irobots.robot_game
            OR (iplayers.player_token = 'dr-light' AND irobots.robot_token = 'mega-man')
            OR (iplayers.player_token = 'dr-wily' AND irobots.robot_token = 'bass')
            OR (iplayers.player_token = 'dr-cossack' AND irobots.robot_token = 'proto-man')
            )
        WHERE
        irobots.robot_class = 'master'
        AND irobots.robot_game IN ('MM00', 'MM01', 'MM02', 'MM04')
        AND (
            urobots.robot_player_original = ''
            OR (
                urobots.robot_player_original <> iplayers.player_token
                AND irobots.robot_token IN ('mega-man', 'bass', 'proto-man', 'roll', 'disco', 'rhythm')
                )
            )
        ORDER BY
        urobots.user_id ASC,
        iplayers.player_order ASC,
        irobots.robot_order ASC
        ;


    -- FIX ALL USER ROBOTS WITH INCORRECT OR MISSING ORIGINAL PLAYERS
    UPDATE
        mmrpg_users_robots AS urobots
        LEFT JOIN mmrpg_index_robots AS irobots ON irobots.robot_token = urobots.robot_token
        LEFT JOIN mmrpg_index_players AS iplayers ON (
            iplayers.player_game = irobots.robot_game
            OR (iplayers.player_token = 'dr-light' AND irobots.robot_token = 'mega-man')
            OR (iplayers.player_token = 'dr-wily' AND irobots.robot_token = 'bass')
            OR (iplayers.player_token = 'dr-cossack' AND irobots.robot_token = 'proto-man')
            )
        SET urobots.robot_player_original = iplayers.player_token
        WHERE
        iplayers.player_token IS NOT NULL
        AND (
            urobots.robot_player_original = ''
            OR urobots.robot_player_original IS NULL
            OR (
                urobots.robot_player_original <> iplayers.player_token
                AND irobots.robot_token IN ('mega-man', 'bass', 'proto-man', 'roll', 'disco', 'rhythm')
                )
            )
        ;

     */

}


/*

// -- PATCH FUNCTION TEMPLATE -- //

// Define a patch function for applying the max robot stats update
$token = 'template';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Template Patch Name';
$update_patch_details[$token] = "Template patch details.  Use this area to describe the purpose and procedure.';
function mmrpg_patch_template($_GAME){

    // Pull in global variables
    global $db;


    // Return the updated game array
    return $_GAME;

}

*/

?>