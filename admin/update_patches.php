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

    echo("Converting session objects into database objects for user ID {$_GAME['user_id']}... \n\n");

    // Trigger the session to database function to do all the work
    legacy_rpg_game::session_to_database($_GAME, true);

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