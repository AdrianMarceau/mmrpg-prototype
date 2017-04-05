<?
// ACTION : CHARGE WEAPONS
$ability = array(
    'ability_name' => 'Charge Weapons',
    'ability_token' => 'action-charge-weapons',
    'ability_class' => 'system',
    'ability_description' => 'The user enters a charging state that helps to recover a small amount of depleted weapon energy.',
    'ability_type' => 'weapons',
    'ability_energy' => 0,
    'ability_damage' => 0,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self and show the ability triggering
        $temp_weapons_current = $this_robot->robot_weapons;
        $temp_weapons_lost = $this_robot->robot_base_weapons - $this_robot->robot_weapons;
        $temp_weapons_recovery = ceil($this_robot->robot_base_weapons * 0.10);
        if ($temp_weapons_recovery > $temp_weapons_lost){ $temp_weapons_recovery = $temp_weapons_lost; }
        $temp_weapons_new = $temp_weapons_current + $temp_weapons_recovery;

        // Trigger the charging message to show the action being used
        $this_ability->target_options_update(array(
            'frame' => 'defend',
            'success' => array(0, 0, 0, 10, $this_robot->print_name().' started charging...'),
            'failure' => array(0, 0, 0, 10, $this_robot->print_name().' started charging...')
            ));
        $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

        // Trigger the recovery function if applicable, else show nothing happened
        if ($temp_weapons_recovery > 0){
            $this_ability->recovery_options_update(array(
                'kind' => 'weapons',
                'frame' => 'summon',
                'percent' => true,
                'modifiers' => false,
                'kickback' => array(0, 0, 0),
                'success' => array(0, 0, 0, 0, 'The charging restored a bit of ammo!'),
                'failure' => array(0, 0, 0, 0, 'The charging restored a bit of ammo!')
                ));
            $this_robot->trigger_recovery($this_robot, $this_ability, $temp_weapons_recovery);
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