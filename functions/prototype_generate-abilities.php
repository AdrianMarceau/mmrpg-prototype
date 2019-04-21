<?
// Define a function for generating an ability set for a given robot
function mmrpg_prototype_generate_abilities($robot_info, $robot_level = 1, $ability_num = 1, $robot_item = ''){
    global $db;

    // Define the static variables for the ability lists
    static $mmrpg_prototype_core_abilities;
    static $mmrpg_prototype_master_support_abilities;
    static $mmrpg_prototype_mecha_support_abilities;
    static $mmrpg_prototype_darkness_abilities;

    // Collect the ability index for calculation purposes
    static $this_ability_index;
    if (empty($this_ability_index)){
        $db_ability_fields = rpg_ability::get_index_fields(true);
        $this_ability_index = $db->get_array_list("SELECT $db_ability_fields FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
    }

    // Define all the core and support abilities to be used in generating
    if (empty($mmrpg_prototype_core_abilities)){
        $mmrpg_prototype_core_abilities = array(
            rpg_ability::get_tier_one_abilities(),
            rpg_ability::get_tier_two_abilities(),
            rpg_ability::get_tier_three_abilities()
            );
    }
    if (empty($mmrpg_prototype_master_support_abilities)){
        $mmrpg_prototype_master_support_abilities = array(
            array(
                'buster-shot', 'buster-charge', 'buster-relay'
                ),
            array(
                'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
                ),
            array(
                'attack-break', 'defense-break', 'speed-break', 'energy-break',
                ),
            array(
                'attack-swap', 'defense-swap', 'speed-swap', 'energy-swap',
                ),
            array(
                'attack-mode', 'defense-mode', 'speed-mode', 'energy-mode',
                ),
            array(
                'attack-support', 'defense-support', 'speed-support', 'energy-support',
                'attack-assault', 'defense-assault', 'speed-assault', 'energy-assault',
                ),
            array(
                'mecha-support', 'field-support'
                ),
            array(
                'experience-booster', 'recovery-booster', 'damage-booster',
                'experience-breaker', 'recovery-breaker', 'damage-breaker',
                )
            );
    }
    if (empty($mmrpg_prototype_mecha_support_abilities)){
        $mmrpg_prototype_mecha_support_abilities = array(
            array(
                'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost',
                ),
            array(
                'attack-break', 'defense-break', 'speed-break', 'energy-break',
                ),
            array(
                'attack-mode', 'defense-mode', 'speed-mode', 'energy-mode',
                )
            );
    }
    if (empty($mmrpg_prototype_darkness_abilities)){
        $mmrpg_prototype_darkness_abilities = array(
            array(
                'dark-boost', 'dark-break', 'dark-drain'
                )
            );
    }

    // Ensure we always have at least one ability
    if ($ability_num < 1){ $ability_num = 1; }

    // Define the array for holding all of this robot's abilities
    $this_robot_abilities = array();

    // Loop through this robot's level-up abilities looking for one
    $robot_index_info = $robot_info;
    if (!empty($robot_index_info['robot_rewards']['abilities'])){
        foreach ($robot_index_info['robot_rewards']['abilities'] AS $info){
            // If this is the buster shot or too high of a level, continue
            if ($info['level'] > $robot_level){ continue; }
            elseif (!isset($this_ability_index[$info['token']])){ continue; }
            elseif (!$this_ability_index[$info['token']]['ability_flag_complete']){ continue; }
            // If this is an incomplete master ability, continue
            if ($robot_index_info['robot_class'] == 'master'){
                if (!in_array($info['token'], $mmrpg_prototype_core_abilities[0])
                    && !in_array($info['token'], $mmrpg_prototype_core_abilities[1])
                    && !in_array($info['token'], $mmrpg_prototype_core_abilities[2])){
                    continue;
                }
            }
            // Add this ability token the list
            $this_robot_abilities[] = $info['token'];
        }
    }

    // Define a new array to hold all the addon abilities
    $this_robot_abilities_addons = array('base' => $this_robot_abilities, 'weapons' => array(), 'support' => array());

    // Remove abilities from the list that are not yet complete
    foreach ($this_robot_abilities AS $key => $token){
        if (isset($this_ability_index[$token]) && !empty($this_ability_index[$token]['ability_flag_complete'])){ continue; }
        else { unset($this_robot_abilities[$key]); }
    }

    // Re-key the base ability list just in case
    $this_robot_abilities = array_values($this_robot_abilities);

    // If we have already enough abilities, we have nothing more to do
    if (count($this_robot_abilities) >= $ability_num){

        // Simple slice to make sure we don't go over eight
        $this_robot_abilities = array_slice($this_robot_abilities, 0, $ability_num);

    }
    // Otherwise, if we need more abilities, we generate them dynamically
    else {

        // Define the number of additional abilities to add
        $remaining_abilities = $ability_num - count($this_robot_abilities);

        // Check if this robot is holding a core
        $robot_item_core = !empty($robot_item) && preg_match('/-core$/i', $robot_item) ? preg_replace('/-core$/i', '', $robot_item) : '';

        // Define the number of core and support abilities for the robot
        if ($robot_index_info['robot_class'] == 'master' || $robot_index_info['robot_class'] == 'boss'){
            foreach ($mmrpg_prototype_core_abilities AS $group_key => $group_abilities){
                if (!empty($this_robot_abilities) && floor($robot_level / 10) < ($group_key + 1)){ continue; }
                foreach ($group_abilities AS $ability_key => $ability_token){
                    if (in_array($ability_token, $this_robot_abilities)){ continue; }
                    $ability_info = $this_ability_index[$ability_token];
                    $is_compatible = false;
                    if (!$is_compatible && in_array($ability_token, $robot_index_info['robot_abilities'])){
                        $is_compatible = true;
                    }
                    if (!$is_compatible && !empty($robot_index_info['robot_core'])){
                        if ($robot_index_info['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
                        elseif (!empty($ability_info['ability_type']) && $robot_index_info['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
                        elseif (!empty($ability_info['ability_type2']) && $robot_index_info['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
                    }
                    if (!$is_compatible && !empty($robot_item_core)){
                        if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
                        elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
                        elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
                    }
                    if ($is_compatible){ $this_robot_abilities_addons['weapons'][] = $ability_token; }
                }
                unset($ability_info);
            }
        }

        // Ensure this is not an empty robot as their mechanics are different
        if ($robot_index_info['robot_core'] != 'empty'){

            // Collect a list of global abilities to reference
            $temp_global_abilities = rpg_ability::get_global_abilities();

            // Define the number of core and master support abilities for the robot
            if ($robot_index_info['robot_class'] != 'mecha'){
                foreach ($mmrpg_prototype_master_support_abilities AS $group_key => $group_abilities){
                    if (!empty($this_robot_abilities) && floor($robot_level / 10) < ($group_key + 1)){ continue; }
                    foreach ($group_abilities AS $ability_key => $ability_token){
                        if (in_array($ability_token, $this_robot_abilities)){ continue; }
                        $ability_info = $this_ability_index[$ability_token];
                        $is_compatible = false;
                        if (!$is_compatible && (in_array($ability_token, $robot_index_info['robot_abilities']) || in_array($ability_token, $temp_global_abilities))){
                            $is_compatible = true;
                        }
                        if (!$is_compatible && !empty($robot_index_info['robot_core'])){
                            if ($robot_index_info['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $robot_index_info['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $robot_index_info['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if (!$is_compatible && !empty($robot_item_core)){
                            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
                    }
                    unset($ability_info);
                }
            }
            // Define the number of core and mecha support abilities for the robot
            elseif ($robot_index_info['robot_class'] == 'mecha'){
                foreach ($mmrpg_prototype_mecha_support_abilities AS $group_key => $group_abilities){
                    if (!empty($this_robot_abilities) && floor($robot_level / 20) < ($group_key + 1)){ continue; }
                    foreach ($group_abilities AS $ability_key => $ability_token){
                        if (in_array($ability_token, $this_robot_abilities)){ continue; }
                        $ability_info = $this_ability_index[$ability_token];
                        $is_compatible = false;
                        if (!$is_compatible && (in_array($ability_token, $robot_index_info['robot_abilities']) || in_array($ability_token, $temp_global_abilities))){
                            $is_compatible = true;
                        }
                        if (!$is_compatible && empty($ability_info['ability_type'])){
                            $is_compatible = true;
                        }
                        if (!$is_compatible && !empty($ability_info['ability_type'])){
                            if ($robot_index_info['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $robot_index_info['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $robot_index_info['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if (!$is_compatible && !empty($robot_item_core)){
                            if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
                            elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
                        }
                        if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
                    }
                    unset($ability_info);
                }
            }


        }

        // Define the number of darkness abilities for the robot
        if ($robot_index_info['robot_core'] == 'empty'){
            foreach ($mmrpg_prototype_darkness_abilities AS $group_key => $group_abilities){
                if (!empty($this_robot_abilities) && floor($robot_level / 10) < ($group_key + 1)){ continue; }
                foreach ($group_abilities AS $ability_key => $ability_token){
                    if (in_array($ability_token, $this_robot_abilities)){ continue; }
                    $ability_info = rpg_ability::parse_index_info($this_ability_index[$ability_token]);
                    $is_compatible = false;
                    if (!$is_compatible && in_array($ability_token, $robot_index_info['robot_abilities'])){
                        $is_compatible = true;
                    }
                    if (!$is_compatible && !empty($robot_index_info['robot_core'])){
                        if ($robot_index_info['robot_core'] == 'copy' && $ability_info['ability_type'] != 'empty'){ $is_compatible = true; }
                        elseif (!empty($ability_info['ability_type']) && $robot_index_info['robot_core'] == $ability_info['ability_type']){ $is_compatible = true; }
                        elseif (!empty($ability_info['ability_type2']) && $robot_index_info['robot_core'] == $ability_info['ability_type2']){ $is_compatible = true; }
                    }
                    if (!$is_compatible && !empty($robot_item_core)){
                        if ($robot_item_core == 'copy' && $ability_info['ability_type'] == 'copy'){ $is_compatible = true; }
                        elseif (!empty($ability_info['ability_type']) && $robot_item_core == $ability_info['ability_type']){ $is_compatible = true; }
                        elseif (!empty($ability_info['ability_type2']) && $robot_item_core == $ability_info['ability_type2']){ $is_compatible = true; }
                    }
                    if ($is_compatible){ $this_robot_abilities_addons['support'][] = $ability_token; }
                }
                unset($ability_info);
            }
        }

        // Shuffle the weapons and support arrays
        $this_robot_abilities_addons['weapons'] = array_unique($this_robot_abilities_addons['weapons']);
        $this_robot_abilities_addons['support'] = array_unique($this_robot_abilities_addons['support']);
        shuffle($this_robot_abilities_addons['weapons']);
        shuffle($this_robot_abilities_addons['support']);

        /*
        // If there were no main abilities, give them an addons
        if (empty($this_robot_abilities) && !empty($this_robot_abilities_addons['weapons'])){
            $temp_token = array_shift($this_robot_abilities_addons['weapons']);
            $this_robot_abilities[] = $temp_token;
            $this_robot_abilities_addons['base'][] = $temp_token;
        }
        */

        // Define the last addon array which will have alternating values
        $temp_addons_final = array();
        $temp_count_limit = count($this_robot_abilities_addons['weapons']) + count($this_robot_abilities_addons['support']);
        for ($i = 0; $i < $temp_count_limit; $i++){
            if (isset($this_robot_abilities_addons['weapons'][$i]) || isset($this_robot_abilities_addons['support'][$i])){
                if (isset($this_robot_abilities_addons['support'][$i])){ $temp_addons_final[] = $this_robot_abilities_addons['support'][$i]; }
                if (isset($this_robot_abilities_addons['weapons'][$i])){ $temp_addons_final[] = $this_robot_abilities_addons['weapons'][$i]; }
            } else {
                break;
            }
        }

        // Combine the two arrays into one again
        //$this_robot_abilities = array_merge($this_robot_abilities_addons['base'], $this_robot_abilities_addons['weapons'], $this_robot_abilities_addons['support']);
        $this_robot_abilities = array_merge($this_robot_abilities, $temp_addons_final);
        // Crop the array to the requested length
        $this_robot_abilities = array_slice($this_robot_abilities, 0, $ability_num);

    }

    // If this robot truly has no abilities (which is bad), give them the buster shot
    if (empty($this_robot_abilities)){ $this_robot_abilities[] = 'buster-shot'; }

    // Return the ability array, whatever it was
    return $this_robot_abilities;
}

?>