<?
// HARK KNUCKLE
$ability = array(
    'ability_name' => 'Hard Knuckle',
    'ability_token' => 'hard-knuckle',
    'ability_game' => 'MM03',
    //'ability_group' => 'MM03/Weapons/020',
    'ability_group' => 'MM03/Weapons/019T1',
    'ability_description' => 'The user fires a powerful but slow-moving fist at the target to deal massive damage and lower their defense stat!',
    'ability_type' => 'impact',
    'ability_energy' => 4,
    'ability_speed' => -2,
    'ability_damage' => 18,
    'ability_accuracy' => 96,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_offset' => array('x' => 120, 'y' => 0, 'z' => 10)
            );

        // Attach this ability attachment to the robot using it
        $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $this_robot->update_session();

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => ($this_robot->robot_token == 'hard-man' ? 'throw' : 'shoot'),
            'success' => array(2, 60, ($this_robot->robot_token == 'hard-man' ? 10 : 0), -10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Attach this ability attachment to the robot using it
        unset($this_robot->robot_attachments[$this_attachment_token]);
        $this_robot->update_session();

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(60, 0, 0),
            'success' => array(0, 50, 0, 10, 'The '.$this_ability->print_name().' crashes into the target!'),
            'failure' => array(0, -120, 0, -10, 'The '.$this_ability->print_name().' flew past the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(60, 0, 0),
            'success' => array(0, 50, 0, 10, 'The '.$this_ability->print_name().' crashes into the target!'),
            'failure' => array(0, -120, 0, -10, 'The '.$this_ability->print_name().' flew past the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Trigger a defense break if the ability was successful
        if ($target_robot->robot_status != 'disabled'
            && $this_ability->ability_results['this_result'] != 'failure'){

            // Call the global stat break function with customized options
            rpg_ability::ability_function_stat_break($target_robot, 'defense', 1);

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