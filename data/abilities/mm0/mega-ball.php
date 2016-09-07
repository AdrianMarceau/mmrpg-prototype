<?
// MEGA BALL
$ability = array(
    'ability_name' => 'Mega Ball',
    'ability_token' => 'mega-ball',
    'ability_game' => 'MM08',
    'ability_group' => 'MM00/Weapons/Mega',
    'ability_description' => 'The user creates a ball-shaped weapon that rocks back and forth along the ground until being kicked at any target for massive damage!',
    'ability_type' => '',
    'ability_energy' => 4,
    'ability_damage' => 60,
    'ability_accuracy' => 94,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'attachment_token' => $this_attachment_token,
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 7,
            'ability_frame_animate' => array(7, 6, 5, 4, 3, 2, 1, 0),
            'ability_frame_offset' => array('x' => 60, 'y' => 0, 'z' => 28)
            );

        // Create the attachment object for this ability
        $this_attachment = new rpg_ability($this_battle, $this_player, $this_robot, $this_attachment_info);

        // If the ability flag was not set, this ability begins charging
        if (!isset($this_robot->robot_attachments[$this_attachment_token])){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(7, 60, 0, 28, $this_robot->print_name().' generates a '.$this_ability->print_name().'!<br /> The '.$this_ability->print_name().' started rolling in place&hellip;')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Update this ability's targetting setting
            $this_ability->ability_target = 'select_target';
            $this_ability->update_session();

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

        }
        // Else if the ability flag was set, the ability is released at the target
        else {

            // Remove this ability attachment to the robot using it
            unset($this_robot->robot_attachments[$this_attachment_token]);
            $this_robot->update_session();

            // Update this ability's target options and trigger
            $this_ability->target_options_update(array(
                'frame' => 'slide',
                'kickback' => array(60, 0, 0),
                'success' => array(8, 120, 100, 28, $this_robot->print_name().' kicks the '.$this_ability->print_name().' at the target!'),
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(24, 0, 0),
                'success' => array(9, -30, 0, 28, 'The '.$this_ability->print_name().' collided with the target!'),
                'failure' => array(9, -60, 0, -10, 'The '.$this_ability->print_name().' bounced past the target&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // Update this ability's targetting setting
            $this_ability->ability_target = 'auto';
            $this_ability->update_session();

        }

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;

        // If the ability flag had already been set, reduce the weapon energy to zero
        if (isset($this_robot->robot_attachments[$this_attachment_token])){ $this_ability->ability_energy = 0; }
        // Otherwise, return the weapon energy back to default
        else { $this_ability->ability_energy = $this_ability->ability_base_energy; }

        // If the ability attachment is already there, change target to select
        if (isset($this_robot->robot_attachments[$this_attachment_token])){

            // Update this ability's targetting setting
            $this_ability->ability_target = 'select_target';
            $this_ability->update_session();

        }
        // Else if the ability attachment is not there, change the target back to auto
        else {

            // Update this ability's targetting setting
            $this_ability->ability_target = 'auto';
            $this_ability->update_session();

        }

        // Return true on success
        return true;

        }
    );
?>