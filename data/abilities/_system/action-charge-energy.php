<?
// ACTION : CHARGE ENERGY
$ability = array(
    'ability_name' => 'Charge Energy',
    'ability_token' => 'action-charge-energy',
    'ability_class' => 'system',
    'ability_description' => 'The user enters a charging state that helps to recover a small amount of depleted life energy.',
    'ability_type' => 'energy',
    'ability_energy' => 0,
    'ability_damage' => 0,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self and show the ability triggering
        $temp_energy_current = $this_robot->robot_energy;
        $temp_energy_lost = $this_robot->robot_base_energy - $this_robot->robot_energy;
        $temp_energy_recovery = ceil($this_robot->robot_base_energy * 0.10);
        if ($temp_energy_recovery > $temp_energy_lost){ $temp_energy_recovery = $temp_energy_lost; }
        $temp_energy_new = $temp_energy_current + $temp_energy_recovery;

        // Trigger the charging message to show the action being used
        $this_ability->target_options_update(array(
            'frame' => 'defend',
            'success' => array(0, 0, 0, 10, $this_robot->print_name().' started charging...'),
            'failure' => array(0, 0, 0, 10, $this_robot->print_name().' started charging...')
            ));
        $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

        // Trigger the recovery function if applicable, else show nothing happened
        if ($temp_energy_recovery > 0){
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'summon',
                'percent' => true,
                'modifiers' => false,
                'kickback' => array(0, 0, 0),
                'success' => array(0, 0, 0, 0, 'The charging restored a bit of health!'),
                'failure' => array(0, 0, 0, 0, 'The charging restored a bit of health!')
                ));
            $this_robot->trigger_recovery($this_robot, $this_ability, $temp_energy_recovery);
        } else {
            $this_ability->target_options_update(array(
                'frame' => 'base',
                'success' => array(0, 0, 0, 10, '...but nothing happened.'),
                'failure' => array(0, 0, 0, 10, '...but nothing happened.')
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));
        }

        // Return true on success
        return true;

        }
    );
?>