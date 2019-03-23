<?
// ICE SLASHER
$ability = array(
    'ability_name' => 'Ice Slasher',
    'ability_token' => 'ice-slasher',
    'ability_game' => 'MM01',
    //'ability_group' => 'MM01/Weapons/005',
    'ability_group' => 'MM01/Weapons/003T2',
    'ability_description' => 'The user fires a blast of razor-sharp ice at the target to inflict damage and remove any boosts to their speed stat!',
    'ability_type' => 'freeze',
    'ability_type2' => 'cutter',
    'ability_energy' => 8,
    'ability_damage' => 26,
    'ability_accuracy' => 94,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 80, 0, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(15, 0, 0),
            'success' => array(4, -65, 0, 10, 'The '.$this_ability->print_name().' cut into the target!'),
            'failure' => array(0, -95, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(10, 0, 0),
            'success' => array(4, -65, 0, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(0, -95, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Ensure the target is not disabled before apply a stat change
        if ($target_robot->robot_status != 'disabled'
            && $this_ability->ability_results['this_result'] != 'failure'
            && $target_robot->counters['speed_mods'] > 0){

            // Call the global stat break function with customized options
            rpg_ability::ability_function_stat_break($target_robot, 'speed', $target_robot->counters['speed_mods']);

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