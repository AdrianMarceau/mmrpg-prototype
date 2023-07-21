<?

// ROBOT ACTIONS : CHANGE ABILITY

// Collect the ability variables from the request header, if they exist
$request_player = isset($_REQUEST['player']) && preg_match('/^[-_a-z0-9]+$/i', $_REQUEST['player']) ? $_REQUEST['player'] : null;
$request_robot = isset($_REQUEST['robot']) && preg_match('/^[-_a-z0-9]+$/i', $_REQUEST['robot']) ? $_REQUEST['robot'] : null;
$request_ability = isset($_REQUEST['ability']) && (empty($_REQUEST['ability']) || preg_match('/^[-_a-z0-9]+$/i', $_REQUEST['ability'])) ? $_REQUEST['ability'] : null;
$request_key = isset($_REQUEST['key']) && is_numeric($_REQUEST['key']) ? (int)$_REQUEST['key'] : null;

// If key variables are not provided, kill the script in error
if (empty($request_player)
    || empty($request_robot)){
    die('error|request-error-'.__LINE__.'|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true)));
}

// Collect object indexes for the robots, players, and abilities
$mmrpg_index_players = rpg_player::get_index();
$mmrpg_index_robots = rpg_robot::get_index();
$mmrpg_index_abilities = rpg_ability::get_index(true, false, 'master');

// Collect a list of the ability tokens for validation, but make sure they're all complete
$mmrpg_index_abilities_complete = array_keys($mmrpg_index_abilities);
$mmrpg_index_abilities_complete = array_filter($mmrpg_index_abilities_complete,
    function($token) use ($mmrpg_index_abilities, $request_robot){
        if (preg_match('/^(energy|attack|defense|speed)-shuffle$/i', $token)){ return false; }
        if ($request_robot !== 'rhythm' && preg_match('/^(energy|attack|defense|speed)-swap/i', $token)){ return false; }
        if (in_array($token, array('repair-mode'))){ return false; }
        return $mmrpg_index_abilities[$token]['ability_flag_complete'];
    });
//echo('$mmrpg_index_abilities_complete = '.print_r($mmrpg_index_abilities_complete, true).PHP_EOL);

// Validate that the provided player and robot are real
if (!isset($mmrpg_index_players[$request_player])
    || !isset($mmrpg_index_robots[$request_robot])){
    die('error|request-error-'.__LINE__.'|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true)));
}

// Validate that the provided abliity, if provided, is real
if (!empty($request_ability)
    && !isset($mmrpg_index_abilities[$request_ability])){
    die('error|request-error-'.__LINE__.'|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true)));
}

// Collect the preset variables if provided in the request
$allowed_presets = array('reset', 'level-up', 'offense', 'support', 'balanced', 'random');
$request_preset = isset($_REQUEST['preset']) && in_array($_REQUEST['preset'], $allowed_presets) ? $_REQUEST['preset'] : null;

// Collect the current settings for the requested robot
$request_robot_rewards = rpg_game::robot_rewards($request_player, $request_robot);
$request_robot_settings = rpg_game::robot_settings($request_player, $request_robot);
//echo('$request_robot_rewards = '.print_r($request_robot_rewards, true).PHP_EOL);
//echo('$request_robot_settings = '.print_r($request_robot_settings, true).PHP_EOL);

// Now that we know they're real, collect info for the player and robot
$request_player_info = $mmrpg_index_players[$request_player];
$request_robot_info = $mmrpg_index_robots[$request_robot];
//echo('$request_player_info = '.print_r($request_player_info, true).PHP_EOL);
//echo('$request_robot_info = '.print_r($request_robot_info, true).PHP_EOL);

// Create a key-based array to hold the ability settings in and populate it
$request_robot_abilities = array();
foreach ($request_robot_settings['robot_abilities'] AS $temp_info){ $request_robot_abilities[] = $temp_info['ability_token']; }

// Crop the ability settings if they've somehow exceeded the eight limit
if (count($request_robot_abilities) > 8){ $request_robot_abilities = array_slice($request_robot_abilities, 0, 8, true); }

// Collect some key details about this particular player robot
$request_robot_token = $request_robot_info['robot_token'];
$request_robot_level = isset($request_robot_rewards['robot_level']) && is_numeric($request_robot_rewards['robot_level']) ? (int)($request_robot_rewards['robot_level']) : 1;
$request_robot_abilities_current = $request_robot_abilities;
$request_robot_item_equipped = isset($request_robot_settings['robot_item']) && !empty($request_robot_settings['robot_item']) ? $request_robot_settings['robot_item'] : '';

// Create a new version of the above array, but this time filtered to only compatible abilities for the current robot
$mmrpg_index_abilities_compatible = array_filter($mmrpg_index_abilities_complete,
    function($request_ability_token) use ($mmrpg_index_abilities, $request_player, $request_robot_token, $request_robot_item_equipped){
        return rpg_robot::has_ability_compatibility($request_robot_token, $request_ability_token, $request_robot_item_equipped);
    });

// Further filter the compatible abilities by ones actually unlocked by the player (sorry guys you gotta do it)
$mmrpg_index_abilities_allowed = array_filter($mmrpg_index_abilities_compatible,
    function($request_ability_token) use ($request_player, $mmrpg_index_abilities){
        return mmrpg_prototype_ability_unlocked($request_player, '', $request_ability_token);
    });

// If a PRESET was explicitly provided, we can process it now
if (!empty($request_preset)){
    //error_log('$_REQUEST = '.print_r($_REQUEST, true));
    //echo('$request_preset = '.print_r($request_preset, true).PHP_EOL);

    // Empty the existing array because we don't need it anymore
    $request_robot_abilities = array();

    // If the preset is LEVEL-UP, set the default abilities
    if ($request_preset == 'reset'){

        // Everyone starts with Buster Shot and then we go from there
        $request_robot_abilities[] = 'buster-shot';

    }
    // If the preset is LEVEL-UP, set the default abilities
    elseif ($request_preset == 'level-up'){

        // Everyone starts with Buster Shot and then we go from there
        $request_robot_abilities[] = 'buster-shot';

        // Loop through the level-up rewards, assuming they exist, and append
        if (!empty($request_robot_info['robot_rewards'])
            && !empty($request_robot_info['robot_rewards']['abilities'])){
            foreach ($request_robot_info['robot_rewards']['abilities'] AS $key => $info){
                $level = isset($info['level']) && is_numeric($info['level']) ? (int)($info['level']) : 0;
                $token = isset($info['token']) && preg_match('/^[-_a-z0-9]+$/i', $info['token']) ? $info['token'] : '';
                if (empty($token) || !isset($mmrpg_index_abilities[$token])){ continue; }
                if (in_array($token, $request_robot_abilities)){ continue; }
                if ($level > $request_robot_level){ continue; }
                $request_robot_abilities[] = $token;
            }
        }

    }
    // If if any other (OFFENSE, SUPPORT, BALANCED, RANDOM), we have to auto generate
    else {

        // Collect and then shuffle the list of all ability tokens
        $shuffled_ability_tokens = $mmrpg_index_abilities_allowed;
        shuffle($shuffled_ability_tokens);
        //echo('$shuffled_ability_tokens = '.print_r($shuffled_ability_tokens, true).PHP_EOL);

        // Collect the list of support abilities for reference later
        $list_of_support_abilities = rpg_ability::get_global_support_abilities();
        //echo('$list_of_support_abilities = '.print_r($list_of_support_abilities, true).PHP_EOL);

        // Group the shuffled tokens by energy so we can pick through them
        $shuffled_grouped_by_energy = array();
        $shuffled_grouped_by_energy_support = array();
        foreach ($shuffled_ability_tokens AS $temp_token){
            $temp_info = $mmrpg_index_abilities[$temp_token];
            $temp_energy = isset($temp_info['ability_energy']) && is_numeric($temp_info['ability_energy']) ? (int)($temp_info['ability_energy']) : 0;
            $temp_energy_adjusted = $temp_energy > 4 ? 2 : 1;
            $is_support = false;
            if (in_array($temp_token, $list_of_support_abilities)){ $is_support = true; }
            if (!$is_support){
                if (!isset($shuffled_grouped_by_energy[$temp_energy_adjusted])){ $shuffled_grouped_by_energy[$temp_energy_adjusted] = array(); }
                $shuffled_grouped_by_energy[$temp_energy_adjusted][] = $temp_token;
            } else {
                if (!isset($shuffled_grouped_by_energy_support[$temp_energy_adjusted])){ $shuffled_grouped_by_energy_support[$temp_energy_adjusted] = array(); }
                $shuffled_grouped_by_energy_support[$temp_energy_adjusted][] = $temp_token;
            }
        }
        ksort($shuffled_grouped_by_energy);
        ksort($shuffled_grouped_by_energy_support);
        //echo('$shuffled_grouped_by_energy = '.print_r($shuffled_grouped_by_energy, true).PHP_EOL);
        //echo('$shuffled_grouped_by_energy_support = '.print_r($shuffled_grouped_by_energy_support, true).PHP_EOL);

        // Define a function to pull from available energy groups
        $pull_from_energy_groups = function (&$request_robot_abilities, &$tokens_grouped, $num_required = 1){
            $current_group_key = 0;
            $num_added = 0;
            $has_doctor_buster = false;
            $has_omega_ability = false;
            $doctor_buster_tokens = array('light-buster', 'wily-buster', 'cossack-buster');
            $omega_ability_tokens = array('omega-pulse', 'omega-wave');
            while ($num_added < $num_required && !empty($tokens_grouped)){
                //echo('next $current_group_key = '.print_r($current_group_key, true).PHP_EOL);
                //echo('available $tokens_grouped = '.print_r($tokens_grouped, true).PHP_EOL);
                $next_ability = array_pop($tokens_grouped[$current_group_key]);
                if (in_array($next_ability, $request_robot_abilities)){
                    continue;
                }
                if (in_array($next_ability, $doctor_buster_tokens)){
                    if (!$has_doctor_buster){ $has_doctor_buster = true; }
                    else { continue; }
                }
                if (in_array($next_ability, $omega_ability_tokens)){
                    if (!$has_omega_ability){ $has_omega_ability = true; }
                    else { continue; }
                }
                $request_robot_abilities[] = $next_ability;
                $num_added++;
                if (empty($tokens_grouped[$current_group_key])){
                    unset($tokens_grouped[$current_group_key]);
                    $tokens_grouped = array_values($tokens_grouped);
                } else {
                    $current_group_key++;
                }
                if (!isset($tokens_grouped[$current_group_key])){
                    $current_group_key = 0;
                }
            }
        };

        // Define variables that decide how much of each type of ability to pull
        $pull_ability_max = 8;
        $pull_weapon_amount = mt_rand(1, $pull_ability_max);
        $pull_support_amount = ($pull_ability_max - $pull_weapon_amount);
        if ($request_preset == 'balanced'){
            $pull_weapon_amount = 6;
            $pull_support_amount = 2;
        } elseif ($request_preset == 'offense'){
            $pull_weapon_amount = 7;
            $pull_support_amount = 1;
        } elseif ($request_preset == 'support'){
            $pull_weapon_amount = 1;
            $pull_support_amount = 7;
        }

        // Now that we've grouped stuff, let's pick a little from the places we want them
        $weapon_tokens_grouped = array_values($shuffled_grouped_by_energy);
        $support_tokens_grouped = array_values($shuffled_grouped_by_energy_support);
        $pull_from_energy_groups($request_robot_abilities, $weapon_tokens_grouped, ($pull_weapon_amount - count($request_robot_abilities)));
        $pull_from_energy_groups($request_robot_abilities, $support_tokens_grouped, ($pull_ability_max - count($request_robot_abilities)));
        if (count($request_robot_abilities) < $pull_ability_max){
            $remaining_tokens = array();
            foreach ($weapon_tokens_grouped AS $temp_group){ $remaining_tokens = array_merge($remaining_tokens, $temp_group); }
            foreach ($support_tokens_grouped AS $temp_group){ $remaining_tokens = array_merge($remaining_tokens, $temp_group); }
            $remaining_tokens = array_filter(array_values($remaining_tokens));
            //error_log('$request_robot_abilities = '.print_r($request_robot_abilities, true));
            //error_log('$weapon_tokens_grouped = '.print_r($weapon_tokens_grouped, true));
            //error_log('$support_tokens_grouped = '.print_r($support_tokens_grouped, true));
            //error_log('$remaining_tokens = '.print_r($remaining_tokens, true));
            if (!empty($remaining_tokens)){
                $remaining_tokens = array(0 => $remaining_tokens);
                $pull_from_energy_groups($request_robot_abilities, $remaining_tokens, ($pull_ability_max - count($request_robot_abilities)));
            }
        }

        // Only add the Buster Shot if it's not already there
        if (($request_preset === 'balanced' || $request_preset === 'support')
            && !in_array('buster-shot', $request_robot_abilities)){
            array_pop($request_robot_abilities); // take off end, more likely to be support
            array_unshift($request_robot_abilities, 'buster-shot');
        }
        // Only add the Buster Charge if it's not already there
        if ($request_preset === 'balanced'
            && !in_array('buster-charge', $request_robot_abilities)
            && mmrpg_prototype_ability_unlocked(false, false, 'buster-charge')){
            array_pop($request_robot_abilities); // take off end, more likely to be support
            $request_robot_abilities[] = 'buster-charge';
        }

        // Now that everything is all pretty, let's kindly sort the tokens by type
        //error_log('$request_robot_abilities = '.print_r($request_robot_abilities, true).PHP_EOL);
        $sort_abilities = true;
        if ($sort_abilities){
            //echo('$request_robot_abilities(before) = '.print_r($request_robot_abilities, true).PHP_EOL);
            usort($request_robot_abilities, function($a, $b) use ($mmrpg_index_abilities, $list_of_support_abilities){
                $a_info = $mmrpg_index_abilities[$a];
                $b_info = $mmrpg_index_abilities[$b];
                $a_type = !empty($a_info['ability_type']) ? $a_info['ability_type'] : '';
                $b_type = !empty($b_info['ability_type']) ? $b_info['ability_type'] : '';
                $a_energy = !empty($a_info['ability_energy']) ? $a_info['ability_energy'] : 0;
                $b_energy = !empty($b_info['ability_energy']) ? $b_info['ability_energy'] : 0;
                $a_is_support = in_array($a, $list_of_support_abilities);
                $b_is_support = in_array($b, $list_of_support_abilities);
                // if either ability is buster-shot, that immediately comes first,
                // if either ability is buster-charge, that has to come last
                // then non-support abilities will always come next,
                // then, we priorities abilities that actually have a type,
                // then we sort by energy w/ lower coming before higher
                if ($a == 'buster-shot'){ return -1; }
                elseif ($b == 'buster-shot'){ return 1; }
                elseif ($a == 'buster-charge'){ return 1; }
                elseif ($b == 'buster-charge'){ return -1; }
                elseif (!$a_is_support && $b_is_support){ return -1; }
                elseif ($a_is_support && !$b_is_support){ return 1; }
                elseif (!empty($a_type) && empty($b_type)){ return -1; }
                elseif (empty($a_type) && !empty($b_type)){ return 1; }
                elseif ($a_energy == $b_energy){ return 0; }
                elseif ($a_energy < $b_energy){ return -1; }
                elseif ($a_energy > $b_energy){ return 1; }
                return 0;
                });
        }

    }

    //exit('NEW $request_robot_abilities = '.print_r($request_robot_abilities, true).PHP_EOL);

    // Create a new array to hold the full ability settings and populate
    $request_robot_abilities = array_unique($request_robot_abilities);
    $request_robot_abilities_new = array();
    foreach ($request_robot_abilities AS $temp_token){ $request_robot_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
    // Update the new ability settings in the session variable
    $_SESSION[$session_token]['values']['battle_settings'][$request_player]['player_robots'][$request_robot]['robot_abilities'] = $request_robot_abilities_new;
    // Save, produce the success message with the new ability order
    rpg_game::save_session();
    exit('success|ability-preset|'.implode(',', $request_robot_abilities));


}
// Else if an EMPTY STRING was provided as the new ability, REMOVE the previous value
elseif (empty($request_ability)){
    // If this was the last ability, do nothing with this request
    if (count($request_robot_abilities) <= 1){ die('success|remove-last|'.implode(',', $request_robot_abilities)); }
    // Unset the requested key in the array
    unset($request_robot_abilities[$request_key]);
    // Create a new array to hold the full ability settings and populate
    $request_robot_abilities_new = array();
    foreach ($request_robot_abilities AS $temp_token){ $request_robot_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
    // Update the new ability settings in the session variable
    $_SESSION[$session_token]['values']['battle_settings'][$request_player]['player_robots'][$request_robot]['robot_abilities'] = $request_robot_abilities_new;
    // Save, produce the success message with the new ability order
    rpg_game::save_session();
    exit('success|ability-removed|'.implode(',', $request_robot_abilities));
}
// Otherwise, if there was a NEW ABILITY provided, update it in the array
elseif (!in_array($request_ability, $request_robot_abilities)){
    // Update this position in the array with the new ability
    $request_robot_abilities[$request_key] = $request_ability;
    // Create a new array to hold the full ability settings and populate
    $request_robot_abilities_new = array();
    foreach ($request_robot_abilities AS $temp_token){ $request_robot_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
    // Update the new ability settings in the session variable
    $_SESSION[$session_token]['values']['battle_settings'][$request_player]['player_robots'][$request_robot]['robot_abilities'] = $request_robot_abilities_new;
    // Save, produce the success message with the new ability order
    rpg_game::save_session();
    exit('success|ability-updated|'.implode(',', $request_robot_abilities));
}
// Otherwise, if ability is ALREADY EQUIPPED, swap positions in the array
elseif (in_array($request_ability, $request_robot_abilities)){
    // Update this position in the array with the new ability
    $this_slot_key = $request_key;
    $this_slot_value = $request_robot_abilities[$request_key];
    $copy_slot_value = $request_ability;
    $copy_slot_key = array_search($request_ability, $request_robot_abilities);
    // Update this slot with new value
    $request_robot_abilities[$this_slot_key] = $copy_slot_value;
    // Update copy slot with new value
    $request_robot_abilities[$copy_slot_key] = $this_slot_value;
    // Create a new array to hold the full ability settings and populate
    $request_robot_abilities_new = array();
    foreach ($request_robot_abilities AS $temp_token){ $request_robot_abilities_new[$temp_token] = array('ability_token' => $temp_token); }
    // Update the new ability settings in the session variable
    $_SESSION[$session_token]['values']['battle_settings'][$request_player]['player_robots'][$request_robot]['robot_abilities'] = $request_robot_abilities_new;
    // Save, produce the success message with the new ability order
    rpg_game::save_session();
    exit('success|ability-updated|'.implode(',', $request_robot_abilities));
} else {
    // Produce an error show this ability has already been selected
    exit('error|ability-exists|'.implode(',', $request_robot_abilities));
}

?>