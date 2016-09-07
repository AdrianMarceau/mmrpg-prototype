<?
// THUNDER BEAM
$ability = array(
    'ability_name' => 'Thunder Beam',
    'ability_token' => 'thunder-beam',
    'ability_game' => 'MM01',
    'ability_group' => 'MM01/Weapons/008',
    'ability_description' => 'The user throws a powerful bolt of electricity at the target, inflicting damage and occasionally raising the user\'s attack by {RECOVERY2}%!',
    'ability_type' => 'electric',
    'ability_type2' => 'laser',
    'ability_energy' => 8,
    'ability_damage' => 24,
    'ability_recovery2' => 20,
    'ability_recovery2_percent' => true,
    'ability_accuracy' => 85,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(0, 95, 0, 10, $this_robot->print_name().' throws a '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(15, 0, 0),
            'success' => array(1, -65, 0, 10, 'The '.$this_ability->print_name().' zapped the target!'),
            'failure' => array(1, -95, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(1, -65, 0, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(1, -95, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Randomly trigger an attack boost if the ability was successful
        if ($this_robot->robot_status != 'disabled'
            && $this_robot->robot_attack < MMRPG_SETTINGS_STATS_MAX
            && $this_ability->ability_results['this_result'] != 'failure'
            && $this_ability->ability_results['this_amount'] > 0
            && $this_battle->critical_chance(50)){
            // Decrease the target robot's attack stat
            $this_ability->damage_options_update(array(
                'kind' => 'attack',
                'frame' => 'defend',
                'percent' => true,
                'kickback' => array(0, 0, 0),
                'success' => array(2, -5, -5, -10, $this_robot->print_name().'&#39;s weapons were damaged!'),
                'failure' => array(3, 0, 0, -9999, '')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'attack',
                'frame' => 'taunt',
                'percent' => true,
                'kickback' => array(0, 0, 0),
                'success' => array(2, -5, -5, -10, $this_robot->print_name().'&#39;s weapons improved!'),
                'failure' => array(3, 0, 0, -9999, '')
                ));
            $attack_recovery_amount = ceil($this_robot->robot_attack * ($this_ability->ability_recovery2 / 100));
            $trigger_options = array('apply_modifiers' => false);
            $this_robot->trigger_recovery($this_robot, $this_ability, $attack_recovery_amount, true, $trigger_options);
        }

        // Return true on success
        return true;

    }
    );
?>