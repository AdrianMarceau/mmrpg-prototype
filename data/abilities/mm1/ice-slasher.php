<?
// ICE SLASHER
$ability = array(
    'ability_name' => 'Ice Slasher',
    'ability_token' => 'ice-slasher',
    'ability_game' => 'MM01',
    'ability_description' => 'The user fires a blast of razor-sharp ice at the target, inflicting damage and occasionally lowering their speed by {DAMAGE2}%!',
    'ability_type' => 'freeze',
    'ability_type2' => 'cutter',
    'ability_energy' => 8,
    'ability_damage' => 26,
    'ability_damage2' => 10,
    'ability_damage2_percent' => true,
    'ability_accuracy' => 96,
    'ability_function' => function($objects){

        // Call a global ability function with customized options
        return mmrpg_ability::ability_function_forward_attack($objects,
            // Target options
            array(
                'robot_frame' => 'shoot',
                'robot_kickback' => array(-10, 0, 0),
                'ability_frame' => 0,
                'ability_offset' => array(110, 0, 10),
                'ability_text' => '{this_robot_name} fires an {this_ability_name}!'
                ),
            // Damage options
            array(
                'robot_kickback' => array(15, 0, 0),
                'ability_success_frame' => 0,
                'ability_success_offset' => array(-90, 0, 10),
                'ability_success_text' => 'The {this_ability_name} cut into the target!',
                'ability_failure_frame' => 0,
                'ability_failure_offset' => array(-100, 0, -10),
                'ability_failure_text' => 'The {this_ability_name} missed...'
                ),
            // Recovery options
            array(
                'robot_kickback' => array(10, 0, 0),
                'ability_success_frame' => 0,
                'ability_success_offset' => array(-90, 0, 10),
                'ability_success_text' => 'The {this_ability_name} was absorbed by the target!',
                'ability_failure_frame' => 0,
                'ability_failure_offset' => array(-100, 0, -10),
                'ability_failure_text' => 'The {this_ability_name} had no effect...'
                ),
            // Effect options
            array(
                'stat_kind' => 'speed',
                'damage_text' => '{this_robot_name}\'s mobility was damaged!',
                'recovery_text' => '{this_robot_name}\'s mobility improved!',
                'effect_chance' => 50
                )
            );


        }
    );
?>