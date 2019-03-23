<?
// DANGER BOMB
$ability = array(
    'ability_name' => 'Danger Bomb',
    'ability_token' => 'danger-bomb',
    'ability_game' => 'MM01',
    //'ability_group' => 'MM01/Weapons/006',
    'ability_group' => 'MM01/Weapons/003T2',
    'ability_description' => 'The user throws a powerful bomb toward the target that explodes mid-air to inflict massive damage! This ability sharply lowers the defense stats of both the target and user so detonate with extreme caution.',
    'ability_type' => 'explode',
    'ability_energy' => 8,
    'ability_damage' => 50,
    'ability_accuracy' => 86,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's first attachment token
        $this_attachment_token_one = 'ability_'.$this_ability->ability_token.'_one';
        $this_attachment_info_one = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 1,
            'ability_frame_animate' => array(1),
            'ability_frame_offset' => array('x' => 120, 'y' => 20, 'z' => 10)
            );

        // Define this ability's second attachment token
        $this_attachment_token_two = 'ability_'.$this_ability->ability_token.'_two';
        $this_attachment_info_two = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 2,
            'ability_frame_animate' => array(2),
            'ability_frame_offset' => array('x' => 270, 'y' => 5, 'z' => 10)
            );

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'kickback' => array(0, 0, 0),
            'success' => array(0, 160, 15, 10, $this_robot->print_name().' throws the '.$this_ability->print_name().'!'),
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $target_robot->robot_attachments[$this_attachment_token_one] = $this_attachment_info_one;
        $target_robot->robot_attachments[$this_attachment_token_two] = $this_attachment_info_two;
        $target_robot->update_session();
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'frame' => 'damage',
            'kickback' => array(15, 0, 0),
            'success' => array(2, -30, 0, 10, $target_robot->print_name().' was damaged by the blast!'),
            'failure' => array(2, -65, 0, -10, $target_robot->print_name().' avoided the blast&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(2, -30, 0, 10, $target_robot->print_name().' was invigorated by the blast!'),
            'failure' => array(2, -65, 0, -10, $target_robot->print_name().' avoided the blast&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
        unset($target_robot->robot_attachments[$this_attachment_token_one]);
        unset($target_robot->robot_attachments[$this_attachment_token_two]);
        $target_robot->update_session();

        // Ensure the target is not disabled before apply a stat change
        if ($target_robot->robot_status != 'disabled'
            && $this_ability->ability_results['this_result'] != 'failure'){

            // Call the global stat break function with customized options
            rpg_ability::ability_function_stat_break($target_robot, 'defense', 3);

        }

        // Call the global stat break function with customized options
        rpg_ability::ability_function_stat_break($this_robot, 'defense', 3);

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