<?
// SLASH CLAW
$ability = array(
    'ability_name' => 'Slash Claw',
    'ability_token' => 'slash-claw',
    'ability_game' => 'MM07',
    'ability_description' => 'The user slashes at the target with razor-sharp claws, launching a wave of energy that inflicts damage with perfect accuracy and increases attack by {RECOVERY2}%!',
    'ability_type' => 'cutter',
    'ability_energy' => 4,
    'ability_damage' => 10,
    'ability_recovery2' => 15,
    'ability_recovery2_percent' => true,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Call a global ability function with customized options
        return mmrpg_ability::ability_function_forward_attack($objects,
            // Target options
            array(
                'robot_frame' => 'throw',
                'robot_kickback' => array(-5, 0, 0),
                'ability_frame' => 1,
                'ability_offset' => array(100, 0, 10),
                'ability_text' => '{this_robot_name} uses the {this_ability_name}!'
                ),
            // Damage options
            array(
                'robot_kickback' => array(50, 0, 0),
                'ability_success_frame' => 0,
                'ability_success_offset' => array(-90, 0, 10),
                'ability_success_text' => 'The {this_ability_name} cut into the target!',
                'ability_failure_frame' => 2,
                'ability_failure_offset' => array(-100, 0, -10),
                'ability_failure_text' => 'The {this_ability_name} missed...'
                ),
            // Recovery options
            array(
                'robot_kickback' => array(-10, 0, 0),
                'ability_success_frame' => 0,
                'ability_success_offset' => array(-90, 0, 10),
                'ability_success_text' => 'The {this_ability_name} was enjoyed by the target!',
                'ability_failure_frame' => 2,
                'ability_failure_offset' => array(-100, 0, -10),
                'ability_failure_text' => 'The {this_ability_name} had no effect...'
                ),
            // Effect options
            array(
                'stat_kind' => 'attack',
                'recovery_text' => '{this_robot_name}\'s weapons improved!',
                'damage_text' => '{this_robot_name}\'s weapons were damaged!',
                'effect_chance' => 100,
                'effect_target' => 'user'
                )
            );


        }
    );
?>