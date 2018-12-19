<?
// MAGIC CARD
$ability = array(
    'ability_name' => 'Magic Card',
    'ability_token' => 'magic-card',
    'ability_game' => 'MM085',
    'ability_group' => 'MM10B/Weapons/006',
    'ability_description' => 'The user throws pair of magical cards at an unlucky target to deal damage and steal life energy, restoring the user\'s own by up to {RECOVERY2}% of the damage dealt!',
    'ability_type' => 'shadow',
    'ability_energy' => 4,
    'ability_damage' => 12,
    'ability_recovery2' => 100,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(4, 125, 0, 10, $this_robot->print_name().' throws a pair of '.$this_ability->print_name(true).'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(5, -100, 0, 10, 'The '.$this_ability->print_name(true).' drained the target!'),
            'failure' => array(2, -125, 0, -10, 'The '.$this_ability->print_name(true).' missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(10, 0, 0),
            'success' => array(5, -75, 0, 10, 'The '.$this_ability->print_name(true).' emboldened the target!'),
            'failure' => array(2, -100, 0, -10, 'The '.$this_ability->print_name(true).' missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Restore life energy if the ability was successful
        if ($this_robot->robot_energy < $this_robot->robot_base_energy
            && $this_ability->ability_results['this_result'] != 'failure'
            && $this_ability->ability_results['this_amount'] > 0){

            // Increase the target robot's energy stat
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'percent' => true,
                'kickback' => array(0, 0, 0),
                'success' => array(2, -5, -5, -10, $this_robot->print_name().'\'s life energy was restored!'),
                'failure' => array(3, 0, 0, -9999, '')
                ));
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'frame' => 'defend',
                'percent' => true,
                'kickback' => array(0, 0, 0),
                'success' => array(2, -5, -5, -10, $this_robot->print_name().'\'s life energy was lowered!'),
                'failure' => array(3, 0, 0, -9999, '')
                ));
            $energy_recovery_amount = ceil($this_ability->ability_results['this_amount'] * ($this_ability->ability_recovery2 / 100));
            if ($this_robot->robot_energy + $energy_recovery_amount > $this_robot->robot_base_energy){ $energy_recovery_amount = $this_robot->robot_base_energy - $this_robot->robot_energy; }
            $trigger_options = array('apply_modifiers' => false);
            $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount, true, $trigger_options);

        }

        // Return true on success
        return true;

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