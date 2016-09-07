<?
// LASER TRIDENT
$ability = array(
    'ability_name' => 'Laser Trident',
    'ability_token' => 'laser-trident',
    'ability_game' => 'MM09',
    'ability_group' => 'MM09/Weapons/067',
    'ability_description' => 'The user fires a powerful three-pronged energy beam at the target to deal damage and reduce their attack power by {DAMAGE2}%.',
    'ability_type' => 'water',
    'ability_type2' => 'laser',
    'ability_energy' => 8,
    'ability_damage' => 20,
    'ability_damage2' => 20,
    'ability_accuracy' => 94,
    'ability_function' => function($objects){

        // Call a global ability function with customized options
        return rpg_ability::ability_function_forward_attack($objects,
            // Target options
            array(
                'robot_frame' => 'shoot',
                'robot_kickback' => array(-10, 0, 0),
                'ability_frame' => 0,
                'ability_offset' => array(120, 0, 10),
                'ability_text' => '{this_robot_name} fires the {this_ability_name}!'
                ),
            // Damage options
            array(
                'robot_kickback' => array(-15, 0, 0),
                'ability_success_frame' => 0,
                'ability_success_offset' => array(-95, 0, 10),
                'ability_success_text' => 'The {this_ability_name} burns through the target!',
                'ability_failure_frame' => 0,
                'ability_failure_offset' => array(-105, 0, -10),
                'ability_failure_text' => 'The {this_ability_name} was evaded by target...'
                ),
            // Recovery options
            array(
                'robot_kickback' => array(-15, 0, 0),
                'ability_success_frame' => 0,
                'ability_success_offset' => array(-95, 0, 10),
                'ability_success_text' => 'The {this_ability_name}\'s power invigorated the target!',
                'ability_failure_frame' => 0,
                'ability_failure_offset' => array(-105, 0, -10),
                'ability_failure_text' => 'The {this_ability_name} had no effect on the target...'
                ),
            // Effect options
            array(
                'stat_kind' => 'attack',
                'damage_text' => '{this_robot_name}\'s weapons were damaged!',
                'recovery_text' => '{this_robot_name}\'s weapons improved!',
                'effect_chance' => 100
                )
            );

    }
);
?>