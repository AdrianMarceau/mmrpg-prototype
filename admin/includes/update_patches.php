<?

// -- STAT MAX LIMIT UPDATE -- //

// Define a patch function for applying the max robot stats update
$token = 'stat_max_limit_update';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Balancing Max Limits for Robot Stats';
$update_patch_details[$token] = "Robot stats are no longer capped at a max of 9999 and are instead based on each robot's base values.";
$update_patch_details[$token] .= "\nEach stat can now only be boosted to maximum of ".(MMRPG_SETTINGS_STATS_BONUS_MAX + 1)."x its base value at level 100.";
$update_patch_details[$token] .= "\n".'Any overflow of stats that are now considered too high will be converted to zenny for the player.';
function mmrpg_patch_stat_max_limit_update($_GAME){ /* ... */ }


// -- BATTLE POINT REBOOT 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'battle_point_reboot_2k16';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Battle Point Reboot of 2016';
$update_patch_details[$token] = "Battle points are no longer some arbituary number you can grind for all eternity.";
$update_patch_details[$token] .= "\nFrom now on, battle points will be equal to the total sum of all your *best* mission scores combined.";
$update_patch_details[$token] .= "\nWhile replaying missions for better scores is abolutely encouraged, it is no longer necessary to return over and over simply to grind points.";
function mmrpg_patch_battle_point_reboot_2k16($_GAME){ /* ... */ }


// -- PLAYER ABILITY MERGE 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'player_ability_merge_2k16';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Player Ability Merge of 2016';
$update_patch_details[$token] = "Abilities unlocked by any player can now be used by all players. ";
$update_patch_details[$token] .= "\nIt is no longer necessary to trade robots back and forth to customize ";
$update_patch_details[$token] .= "\nthe perfect moveset, and I hope everyone will enjoy this new change. :) ";
function mmrpg_patch_player_ability_merge_2k16($_GAME){ /* ... */ }


// -- PLAYER ITEM / ABILITY SPLIT 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'ability_item_split_2k16';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Ability / Item Split of 2016';
$update_patch_details[$token] = "Abilities and items were technically the same thing in the coding of the game \n";
$update_patch_details[$token] .= "and it required a lot of extra coding to get them to work.  In an effort to \n";
$update_patch_details[$token] .= "optimize the game items are being split into their own separate category of \n";
$update_patch_details[$token] .= "objects. This patch simply re-organizes some of that data in your game file. ";
function mmrpg_patch_ability_item_split_2k16($_GAME){ /* ... */ }


// -- STAR FORCE REBOOT 2k16 -- //

// Define a patch function for applying the next update
$token = 'star_force_reboot_2k16';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Star Force Reboot of 2016';
$update_patch_details[$token] = "Field and Fusion stars now boost battle points instead of boosting elemental \n";
$update_patch_details[$token] .= "damage and recovery power in battle.  As a result, battle points for all players \n";
$update_patch_details[$token] .= "must be recalculated. ";
function mmrpg_patch_star_force_reboot_2k16($_GAME){ /* ... */ }


// -- BATTLE POINT REBOOT 2k19 -- //

// Define a patch function for applying the next update
$token = 'battle_point_reboot_2k19';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Battle Point Reboot of 2019';
$update_patch_details[$token] = "As part of a major update to the game's mechanics, all battle points and leaderboard \n";
$update_patch_details[$token] .= "standings have been reset and recalculated. Your score is no longer based on your \n";
$update_patch_details[$token] .= "turn count in missions, but rather the progress you've made in the game and the \n";
$update_patch_details[$token] .= "items, robots, abilities, etc. that you've collected. Please check the \"Points\" \n";
$update_patch_details[$token] .= "tab on your leaderboard profile for more details and thank you for playing!  ";
function mmrpg_patch_battle_point_reboot_2k19($_GAME){

    // Pull in global variables
    global $db;

    // Return now if the user ID is somehow empty
    if (empty($_GAME['user_id'])){ return false; }

    // Update the user's "legacy2" score if it's not been set already
    $db->query("UPDATE
        mmrpg_leaderboard AS board
        SET board.board_points_legacy2 = board.board_points
        WHERE board.board_points_legacy2 = 0 AND board.user_id = {$_GAME['user_id']}
        ;");

    // Collect the current leaderboard data for this player
    $legacy2_board_points = $db->get_value("SELECT
        board.board_points_legacy2
        FROM mmrpg_leaderboard AS board
        WHERE board.user_id = {$_GAME['user_id']}
        ;", 'board_points_legacy2');

    // Recalculate battle points using the new system
    $new_points_index = array();
    $new_board_points = mmrpg_prototype_calculate_battle_points_2k19($_GAME['user_id'], $new_points_index);

    // Print out the variables for the user to see
    echo('Old Battle Point Total = '.number_format($legacy2_board_points, 0, '.', ',').PHP_EOL);
    echo('New Battle Point Total = '.number_format($new_board_points, 0, '.', ',').PHP_EOL);

    // Update the battle points of the actual game file
    $_GAME['counters']['battle_points'] = $new_board_points;

    // Update battle points and other details of the leaderboard row
    $update_array = array();
    $update_array['board_points'] = $new_board_points;
    $update_array['board_robots'] = !empty($new_points_index['robots_unlocked']) ? '['.implode('],[', $new_points_index['robots_unlocked']).']' : '';
    $update_array['board_robots_count'] = count($new_points_index['robots_unlocked']);
    $update_array['board_abilities'] = count($new_points_index['abilities_unlocked']);
    $update_array['board_items'] = count($new_points_index['items_unlocked']);
    $update_array['board_stars'] = count($new_points_index['field_stars_collected']) + count($new_points_index['fusion_stars_collected']);
    $db->update('mmrpg_leaderboard', $update_array, array('user_id' => $_GAME['user_id']));

    // Return the updated game array
    return $_GAME;

}


// -- [[MULTI-USE]] RECALCULATE ALL BATTLE POINTS -- //

// Define a patch function for applying the next update
$token = 'recalculate_all_battle_points';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Recalculate All Battle Points';
$update_patch_details[$token] = "The battle point values for certain items and/or events have changed and a quick \n";
$update_patch_details[$token] .= "recalculation of your score was necessary. This update has been applied to all \n";
$update_patch_details[$token] .= "save files and your leaderboard standing may change. Thank you for playing. ";
function mmrpg_patch_recalculate_all_battle_points($_GAME){

    // Pull in global variables
    global $db;

    // Return now if the user ID is somehow empty
    if (empty($_GAME['user_id'])){ return false; }

    // Do not recalculate if the user is still logged in
    $now_time = time();
    $last_access_time = $db->get_value("SELECT
        users.user_date_accessed
        FROM mmrpg_users AS users
        WHERE users.user_id = {$_GAME['user_id']}
        ;", 'user_date_accessed');

    // If the user has logged in recently (last half-hour), we should skip their file
    $one_hour = 60 * 30;
    $last_login = $now_time - $last_access_time;
    if ($last_login < $one_hour){
        $mins = (int)(gmdate("i", $last_login));
        echo('Save file accessed only '.($mins !== 1 ? $mins.' minutes' : '1 minute').' ago!'.PHP_EOL);
        return false;
    }

    // Recalculate battle points using the new system
    $old_board_points = !empty($_GAME['counters']['battle_points']) ? $_GAME['counters']['battle_points'] : 0;
    $new_points_index = array();
    $new_board_points = mmrpg_prototype_calculate_battle_points_2k19($_GAME['user_id'], $new_points_index);

    // Print out the variables for the user to see
    echo('Old Battle Point Total = '.number_format($old_board_points, 0, '.', ',').' BP'.PHP_EOL);
    echo('New Battle Point Total = '.number_format($new_board_points, 0, '.', ',').' BP'.PHP_EOL);
    if ($old_board_points != $new_board_points){
        $diff_board_points = $new_board_points - $old_board_points;
        echo('Difference = '.($diff_board_points >= 0 ? '+' : '').number_format($diff_board_points, 0, '.', ',').' BP'.PHP_EOL);
    }

    // Update the battle points of the actual game file
    $_GAME['counters']['battle_points'] = $new_board_points;

    // Update battle points and other details of the leaderboard row
    $update_array = array();
    $update_array['board_points'] = $new_board_points;
    $update_array['board_robots'] = !empty($new_points_index['robots_unlocked']) ? '['.implode('],[', $new_points_index['robots_unlocked']).']' : '';
    $update_array['board_robots_count'] = count($new_points_index['robots_unlocked']);
    $update_array['board_abilities'] = count($new_points_index['abilities_unlocked']);
    $update_array['board_items'] = count($new_points_index['items_unlocked']);
    $update_array['board_stars'] = count($new_points_index['field_stars_collected']) + count($new_points_index['fusion_stars_collected']);
    //$update_array['board_date_modified'] = $now_time;
    $db->update('mmrpg_leaderboard', $update_array, array('user_id' => $_GAME['user_id']));

    // Return the updated game array
    return $_GAME;

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