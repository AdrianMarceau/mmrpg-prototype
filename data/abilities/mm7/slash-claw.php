<?
// SLASH CLAW
$ability = array(
    'ability_name' => 'Slash Claw',
    'ability_token' => 'slash-claw',
    'ability_game' => 'MM07',
    'ability_group' => 'MM07/Weapons/054',
    'ability_description' => 'The user slashes at the target with razor-sharp claws to inflict damage and worsen any negative stat changes afflicting them!',
    'ability_type' => 'cutter',
    'ability_energy' => 4,
    'ability_damage' => 12,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'kickback' => array(-5, 0, 0),
            'success' => array(1, 100, 0, 10, $this_robot->print_name().' uses the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(50, 0, 0),
            'success' => array(0, -90, 0, 10, 'The '.$this_ability->print_name().' cut into the target!'),
            'failure' => array(2, -100, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(-10, 0, 0),
            'success' => array(0, -90, 0, 10, 'The '.$this_ability->print_name().' was enjoyed by the target!'),
            'failure' => array(2, -100, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Ensure the target is not disabled before apply a stat change
        if ($target_robot->robot_status != 'disabled'
            && $this_ability->ability_results['this_result'] != 'failure'){

            // Check all three stats to see if they should be reversed
            $check_stats = array('attack', 'defense', 'speed');
            foreach ($check_stats AS $stat){
                if ($target_robot->counters[$stat.'_mods'] >= 0){ continue; }

                // Call the global stat break function with customized options
                rpg_ability::ability_function_stat_break($target_robot, $stat, ($target_robot->counters[$stat.'_mods'] * -1));

            }

        }

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>