<?
// ATTACK SHUFFLE
$ability = array(
    'ability_name' => 'Attack Shuffle',
    'ability_token' => 'attack-shuffle',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Attack2',
    'ability_description' => 'The user triggers a dangerous glitch in the prototype that randomly increases or decreases their own attack stat!',
    'ability_energy' => 4,
    'ability_speed' => -1,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
            );

        /*
         * SHOW ABILITY TRIGGER
         */

        // Target this robot's self
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(9, 0, 10, -10, $this_robot->print_name().' triggered an '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Check to see if boost or break randomly
        if (mt_rand(0, 1) == 0){

            // Call the global stat boost function with customized options
            rpg_ability::ability_function_stat_boost($this_robot, 'attack', mt_rand(1, 10));

        } else {

            // Call the global stat break function with customized options
            rpg_ability::ability_function_stat_break($target_robot, 'attack', mt_rand(1, 10));

        }

        // Return true on success
        return true;

    }
    );
?>