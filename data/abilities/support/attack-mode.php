<?
// ATTACK MODE
$ability = array(
    'ability_name' => 'Attack Mode',
    'ability_token' => 'attack-mode',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Attack2',
    'ability_description' => 'The user harshly lowers its own defense and speed stats in exchange for a maxed-out attack stat to improve weapons! However the weapon energy cost for this ability increases after each use.',
    'ability_energy' => 8,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self and init ability
        $this_ability->target_options_update(array('frame' => 'summon','success' => array(9, 0, 0, -10, $this_robot->print_name().' enters '.$this_ability->print_name().'!')));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Define the the stats to lower and raise for this mode
        $raise_stat = 'attack';
        $lower_stats = array('defense', 'speed');

        // Check to see if this robot is allowed to use the move or not
        $allow_move = true;
        $stats_wont_go = '';
        if ($this_robot->counters[$raise_stat.'_mods'] >= MMRPG_SETTINGS_STATS_MOD_MAX){ $allow_move = false; $stats_wont_go = $raise_stat.' stat wont go any higher'; }
        elseif ($this_robot->counters[$lower_stats[0].'_mods'] <= MMRPG_SETTINGS_STATS_MOD_MIN){ $allow_move = false; $stats_wont_go = $lower_stats[0].' stat wont go any lower'; }
        elseif ($this_robot->counters[$lower_stats[1].'_mods'] <= MMRPG_SETTINGS_STATS_MOD_MIN){ $allow_move = false; $stats_wont_go = $lower_stats[1].' stat wont go any lower'; }

        // If move is not allowed, do nothing right now
        if (!$allow_move){

            // Target this robot's self to show the failure message
            $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(9, -2, 0, -10, 'But '.$this_robot->print_name().'&#39;s '.$stats_wont_go.'!')));
            $this_robot->trigger_target($this_robot, $this_ability);
            return;

        }

        // Call the global stat break function with customized options
        rpg_ability::ability_function_stat_break($this_robot, $lower_stats[0], 2, $this_ability, 1);

        // Call the global stat break function with customized options
        rpg_ability::ability_function_stat_break($this_robot, $lower_stats[1], 2, $this_ability, 2);

        // Call the global stat boost function with customized options
        rpg_ability::ability_function_stat_boost($this_robot, $raise_stat, 5, $this_ability, 0);

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Check to see if this ability has been used already, and if so increase the cost
        if (!empty($this_robot->history['triggered_abilities'])){
            $trigger_counts = array_count_values($this_robot->history['triggered_abilities']);
            if (!empty($trigger_counts[$this_ability->ability_token])){
                $trigger_count = $trigger_counts[$this_ability->ability_token];
                $new_energy_cost = $this_ability->ability_base_energy * ($trigger_count + 1);
                $this_ability->set_energy($new_energy_cost);
            }
        }

        // Return true on success
        return true;

        }
    );
?>