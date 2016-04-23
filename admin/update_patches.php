<?

// -- STAT MAX LIMIT UPDATE -- //

// Define a patch function for applying the max robot stats update
$token = 'stat_max_limit_update';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Balancing Max Limits for Robot Stats';
$update_patch_details[$token] = "Robot stats are no longer capped at a max of 9999 and are instead based on each robot's base values.";
$update_patch_details[$token] .= "\nEach stat can now only be boosted to maximum of ".(MMRPG_SETTINGS_STATS_BONUS_MAX + 1)."x its base value at level 100.";
$update_patch_details[$token] .= "\n".'Any overflow of stats that are now considered too high will be converted to zenny for the player.';
function mmrpg_patch_stat_max_limit_update($_GAME){

    // Pull in global variables
    global $DB;

    // Parse out robot data from the player rewards and make a copy
    $player_robots_tokens = array();
    $player_robots_rewards = array();
    if ($_GAME['values']['battle_rewards']){
        foreach ($_GAME['values']['battle_rewards'] AS $player_token => $player_rewards){
            if (!empty($player_rewards['player_robots'])){
                foreach ($player_rewards['player_robots'] AS $robot_token => $robot_rewards){
                    $player_robots_rewards[$player_token][$robot_token] = $robot_rewards;
                    $player_robots_tokens[] = $robot_token;
                }
            }
        }
    }

    //echo('<pre>----------------------------------------</pre>');
    //echo('<pre>$player_robots_rewards(before) = '.print_r($player_robots_rewards, true).'</pre>');
    //echo('<pre>----------------------------------------</pre>');

    // Define the stats to loop through and alter
    $stat_tokens = array('energy', 'attack', 'defense', 'speed');

    // Collect a mini robot index for the ones unlocked by the player
    $robot_index = mmrpg_robot::get_index($player_robots_tokens);

    //echo('<pre>$stat_tokens = '.print_r($stat_tokens, true).'</pre>');
    //echo('<pre>$robot_index = '.print_r($robot_index, true).'</pre>');

    // Define a variable for collecting total stat overflow
    $stat_overflow_total = 0;
    $stat_overflow_reward_total = 0;

    echo('<pre>');

    // Loop through each robot and see if it needs to be limited
    foreach ($player_robots_rewards AS $player_token => $player_robots){
        foreach ($player_robots AS $robot_token => $robot_rewards){
            if (isset($robot_index[$robot_token])){

                // Collect this robot's index info for reference
                $robot_info = $robot_index[$robot_token];
                echo("[b]Updating {$robot_info['robot_name']}[/b]\n");

                // Collect and calculate this robot's stat details
                $level = isset($robot_rewards['robot_level']) ? $robot_rewards['robot_level'] : 1;
                $robot_stats = mmrpg_robot::calculate_stat_values($level, $robot_info, $robot_rewards);

                //echo('$robot_stats => '.print_r($robot_stats, true)."\n");

                // Any of this robot's stat are over the limit, we need to adjust
                $stat_overflow = 0;
                $stat_overflow_reward = 0;
                foreach ($stat_tokens AS $key => $stat){
                    if (!empty($robot_stats[$stat]['over'])){
                        echo("\n");
                        echo("{$robot_info['robot_name']}'s {$stat} bonus of {$robot_rewards['robot_'.$stat]} is over the max by {$robot_stats[$stat]['over']}!\n");
                        $robot_rewards['robot_'.$stat] -= $robot_stats[$stat]['over'];
                        $player_robots_rewards[$player_token][$robot_token]['robot_'.$stat] = $robot_rewards['robot_'.$stat];
                        echo("- ".ucfirst($stat)." bonus has been capped at {$robot_stats[$stat]['bonus_max']}.\n");
                        echo("- {$robot_info['robot_name']}'s {$stat} stat is now at ".($robot_stats[$stat]['current'] - $robot_stats[$stat]['over']).".\n");
                        $stat_overflow += $robot_stats[$stat]['over'];
                    }
                }

                // If this robot had any overflow, we need to reward the player
                if (!empty($stat_overflow)){
                    echo("\n");
                    $stat_overflow_reward = $stat_overflow * 10;
                    echo("{$robot_info['robot_name']} had an overflow of ".number_format($stat_overflow, 0, '.', ',')." points.\n");
                    echo("{$robot_info['robot_name']} earned the player ".number_format($stat_overflow_reward, 0, '.', ',')."z!\n");
                    $stat_overflow_total += $stat_overflow;
                    $stat_overflow_reward_total += $stat_overflow_reward;
                } else {
                    echo("{$robot_info['robot_name']}'s stats are on target!\n");
                }

                // Print line break at end of robot
                echo("\n");

            } else {
                unset($player_robots_rewards[$player_token][$robot_token]);
                continue;
            }
        }
    }

    // Print out the totals for the overflow and rewards
    echo("[b]Updates Complete![/b]\n");
    echo("Stat Overflow Total    : ".number_format($stat_overflow_total, 0, '.', ',')."\n");
    echo("Overflow Reward Total  : ".number_format($stat_overflow_reward_total, 0, '.', ',')."z\n");

    // Loop through the modifed robot values and update the game
    foreach ($player_robots_rewards AS $player_token => $player_robots){
        foreach ($player_robots AS $robot_token => $robot_rewards){
            $_GAME['values']['battle_rewards'][$player_token]['player_robots'][$robot_token] = $robot_rewards;
        }
    }

    // And add the reward zenny to the player's total amount
    if (!isset($_GAME['counters']['battle_zenny'])){ $_GAME['counters']['battle_zenny'] = 0; }
    $_GAME['counters']['battle_zenny'] += $stat_overflow_reward_total;

    //echo("----------------------------------------\n");
    //echo("\$_GAME = ".print_r($_GAME, true)."\n");
    //echo("\$_GAME[values][battle_rewards] = ".print_r($_GAME['values']['battle_rewards'], true)."\n");
    //echo("----------------------------------------\n");
    //echo("\$player_robots_rewards(after) = ".print_r($player_robots_rewards, true)."\n");
    //echo("----------------------------------------\n");

    // Return the updated game array
    return $_GAME;

}


// -- BATTLE POINT REBOOT 2k16 -- //

// Define a patch function for applying the max robot stats update
$token = 'battle_point_reboot_2k16';
$update_patch_tokens[] = $token;
$update_patch_names[$token] = 'Battle Point Reboot of 2016';
$update_patch_details[$token] = "Battle points are no longer some arbituary number you can grind for all eternity.";
$update_patch_details[$token] .= "\nFrom now on, battle points will be equal to the total sum of all your *best* mission scores combined.";
$update_patch_details[$token] .= "\nWhile replaying missions for better scores is abolutely encouraged, it is no longer necessary to return over and over simply to grind points.";
function mmrpg_patch_battle_point_reboot_2k16($_GAME){

    // Pull in global variables
    global $DB;

    // Define the variable to hold the current battle point totals
    $new_battle_points = 0;
    $new_player_battle_points = array();

    echo("Recalculating battle point totals...\n");

    // Define search and replace variables for later display
    $numerals_find = array('Iv', 'Iii', 'Ii');
    $numerals_replace = array('IV', 'III', 'II');

    // If the player has completed battles, loop through them and add up points
    if (!empty($_GAME['values']['battle_complete'])){
        $player_tokens = array('dr-light', 'dr-wily', 'dr-cossack');
        foreach ($player_tokens AS $key => $player_token){
            $new_player_battle_points[$player_token] = 0;
            if (empty($_GAME['values']['battle_complete'][$player_token])){ continue; }
            else { $player_battles = $_GAME['values']['battle_complete'][$player_token]; }
            if (!empty($player_battles)){
                $last_player = '';
                //asort($player_battles);
                foreach ($player_battles AS $battle_token => $battle_records){
                    $corrupt_mission = false;
                    if (!empty($battle_records['battle_max_points'])){
                        $max_points = $battle_records['battle_max_points'];
                        $battle_name = array_slice(explode('-', $battle_token), 1);
                        $player_name = array_shift($battle_name);
                        if ('dr-'.$player_name != $player_token){ $corrupt_mission = true; }
                        $player_name = 'Dr. '.ucfirst($player_name);
                        $phase_name = ucwords(array_shift($battle_name));
                        $battle_name = implode(' / ', $battle_name);
                        $battle_name = ucwords($battle_name);
                        $battle_name = str_replace($numerals_find, $numerals_replace, $battle_name);
                        if ($last_player != $player_name){ echo("\n"); }
                        if (!$corrupt_mission){
                            $new_battle_points += $max_points;
                            $new_player_battle_points[$player_token] += $max_points;
                            echo("+".print_r(number_format($max_points, 0, '.', ','), true)." | {$player_name} | {$phase_name} | {$battle_name} \n");
                        } else {
                            echo("[s]+0 | {$player_name} | {$phase_name} | {$battle_name}[/s] (?????) \n");
                            unset($_GAME['values']['battle_complete'][$player_token][$battle_token]);
                        }
                        $last_player = $player_name;
                    }
                }
            }
        }
        echo("\n");
    } else {
        echo("No battles have been completed...\n");
    }


    echo("----------------------------------------\n\n");

    // Define the variable to hold the current battle point totals
    $reward_battle_zenny = 0;
    $reward_player_battle_points = array();

    // Loop through the players and calculate their new battles points and rewards
    foreach ($new_player_battle_points AS $player => $points){
        $player_name = ucwords(str_replace('dr-', 'Dr. ', $player));
        $old_points = $_GAME['values']['battle_rewards'][$player]['player_points'];
        $new_points = $points;
        $points_diff = $old_points - $new_points;
        $zenny_reward = ceil($points_diff / 1000);
        echo("[b]{$player_name}[/b]\n");
        echo("Old battle points : ".print_r(number_format($old_points, 0, '.', ','), true)."\n");
        echo("New battle points : ".print_r(number_format($new_points, 0, '.', ','), true)."\n");
        echo("Difference : -".print_r(number_format($points_diff, 0, '.', ','), true)."\n");
        echo("Compensation : ".print_r(number_format($zenny_reward, 0, '.', ','), true)."z\n");
        echo("\n");
        $reward_player_battle_points[$player] = $zenny_reward;
        $reward_battle_zenny += $zenny_reward;
    }

    echo("[b]Battle Point Totals[/b]\n");
    $old_points = $_GAME['counters']['battle_points'];
    $new_points = $new_battle_points;
    $points_diff = $old_points - $new_points;
    echo("Old battle point total : ".print_r(number_format($old_points, 0, '.', ','), true)."\n");
    echo("New battle point total : ".print_r(number_format($new_points, 0, '.', ','), true)."\n");
    echo("Difference total : -".print_r(number_format($points_diff, 0, '.', ','), true)."\n");
    echo("Compensation total : [b]".print_r(number_format($reward_battle_zenny, 0, '.', ','), true)."z[/b]\n");
    echo("\n");

    echo("----------------------------------------\n\n");

    //echo("\$_GAME['counters'] = ".print_r($_GAME['counters'], true)."\n");
    //echo("\$_GAME['values']['battle_complete'] = ".print_r($_GAME['values']['battle_complete'], true)."\n");

    //echo("----------------------------------------\n");

    // Update the game file and leadboard with these new changes
    $old_battle_points = $_GAME['counters']['battle_points'];
    $_GAME['counters']['battle_points'] = $new_battle_points;
    $DB->query("UPDATE mmrpg_leaderboard SET board_points_legacy = {$old_battle_points}, board_points = {$new_battle_points} WHERE user_id = {$_GAME['user_id']};");
    foreach ($new_player_battle_points AS $player => $new_points){
        $dbplayer = str_replace('-', '_', $player);
        $old_points = $_GAME['values']['battle_rewards'][$player]['player_points'];
        $_GAME['values']['battle_rewards'][$player]['player_points'] = $new_points;
        $DB->query("UPDATE mmrpg_leaderboard SET board_points_{$dbplayer}_legacy = {$old_points}, board_points_{$dbplayer} = {$new_points} WHERE user_id = {$_GAME['user_id']};");
    }

    // Increment the player's zenny total with the compensation reward
    $_GAME['counters']['battle_points'] += $reward_battle_zenny;

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
    global $DB;


    // Return the updated game array
    return $_GAME;

}

*/

?>