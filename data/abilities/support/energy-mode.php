<?
// ENERGY MODE
$ability = array(
    'ability_name' => 'Energy Mode',
    'ability_token' => 'energy-mode',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Energy2',
    'ability_description' => 'The user harshly lowers its own attack, defense, and speed stats in exchange for fully recovering life energy and returning to perfect health!',
    'ability_energy' => 8,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self and init ability
        $this_ability->target_options_update(array('frame' => 'summon','success' => array(9, 0, 0, -10, $this_robot->print_name().' enters '.$this_ability->print_name().'!')));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Define the the stats to lower for this mode
        $lower_stats = array('attack', 'defense', 'speed');

        // Check to see if this robot is allowed to use the move or not
        $allow_move = true;
        $stats_wont_go = '';
        if ($this_robot->robot_energy >= $this_robot->robot_base_energy){ $allow_move = false; $stats_wont_go = ' is already a full health'; }
        elseif ($this_robot->counters[$lower_stats[0].'_mods'] <= MMRPG_SETTINGS_STATS_MOD_MIN){ $allow_move = false; $stats_wont_go = '&#39;s '.$lower_stats[0].' stat wont go any lower'; }
        elseif ($this_robot->counters[$lower_stats[1].'_mods'] <= MMRPG_SETTINGS_STATS_MOD_MIN){ $allow_move = false; $stats_wont_go = '&#39;s '.$lower_stats[1].' stat wont go any lower'; }
        elseif ($this_robot->counters[$lower_stats[2].'_mods'] <= MMRPG_SETTINGS_STATS_MOD_MIN){ $allow_move = false; $stats_wont_go = '&#39;s '.$lower_stats[2].' stat wont go any lower'; }

        // If move is not allowed, do nothing right now
        if (!$allow_move){

            // Target this robot's self to show the failure message
            $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(9, -2, 0, -10, 'But '.$this_robot->print_name().$stats_wont_go.'!')));
            $this_robot->trigger_target($this_robot, $this_ability);
            return;

        }

        // Call the global stat break function with customized options
        rpg_ability::ability_function_stat_break($this_robot, $lower_stats[0], 2, $this_ability, 1);

        // Call the global stat break function with customized options
        rpg_ability::ability_function_stat_break($this_robot, $lower_stats[1], 2, $this_ability, 2);

        // Call the global stat break function with customized options
        rpg_ability::ability_function_stat_break($this_robot, $lower_stats[2], 2, $this_ability, 3);

        // Increase this robot's life energy stat to max
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'modifiers' => false,
            'frame' => 'taunt',
            'success' => array(0, 0, 0, -10, $this_robot->print_name().'&#39;s life energy was fully restored!'),
            'failure' => array(9, 0, 0, -9999, $this_robot->print_name().'&#39;s life energy was not affected&hellip;')
            ));
        $energy_recovery_amount = $this_robot->robot_base_energy - $this_robot->robot_energy;
        $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);

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