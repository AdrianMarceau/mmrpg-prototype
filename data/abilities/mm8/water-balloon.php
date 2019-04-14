<?
// WATER BALLOON
$ability = array(
    'ability_name' => 'Water Balloon',
    'ability_token' => 'water-balloon',
    'ability_game' => 'MM08',
    //'ability_group' => 'MM08/Weapons/064',
    'ability_group' => 'MM08/Weapons/057T1',
    'ability_description' => 'The user pummels the target with water-filled balloons that burst on contact to deal damage!  This ability will continue shooting balloons until either the target is disabled or the user runs out of weapon energy.',
    'ability_type' => 'water',
    'ability_energy' => 4,
    'ability_damage' => 10,
    'ability_accuracy' => 98,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's first attachment token
        $splash_attachment_token = 'ability_'.$this_ability->ability_token.'_splash';
        $splash_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 4,
            'ability_frame_animate' => array(4),
            'ability_frame_offset' => array('x' => 20, 'y' => 0, 'z' => 10),
            'attachment_token' => $splash_attachment_token
            );

        // Define the ability's Y-offset given the user
        $x_offset = 140;
        $y_offset = 0;
        if ($this_robot->robot_token == 'aqua-man'){ $y_offset = -10; }

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, $x_offset, $y_offset, 10, $this_robot->print_name().' shoots a '.$this_ability->print_name().'!'),
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Add the splash attachment to the target robot
        $target_robot->robot_attachments[$splash_attachment_token] = $splash_attachment_info;
        $target_robot->update_session();

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(20, 0, 0),
            'success' => array(1, 15, $y_offset, 10, 'The '.$this_ability->print_name().' burst on contact!'),
            'failure' => array(0, -65, $y_offset, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'frame' => 'taunt',
            'kickback' => array(10, 0, 0),
            'success' => array(1, 15, $y_offset, 10, 'The '.$this_ability->print_name().' burst on contact!'),
            'failure' => array(0, -65, $y_offset, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Remove the splash attachment to the target robot
        unset($target_robot->robot_attachments[$splash_attachment_token]);
        $target_robot->update_session();

        // Calculate how much WE is required for repeated attacks
        $weapon_energy_required = $this_robot->calculate_weapon_energy($this_ability, $this_ability->ability_energy, $temp_ability_energy_mods);

        // Continue triggering the attack until target disabled OR user runs out of weapon energy
        while ($target_robot->robot_status != 'disabled'
            && $this_robot->robot_weapons >= $weapon_energy_required){

            // Decrement required weapon energy from this robot
            $this_robot->robot_weapons -= $weapon_energy_required;
            if ($this_robot->robot_weapons < 0){ $this_robot->robot_weapons = 0; }
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'shoot',
                'success' => array(0, $x_offset, $y_offset, 10, $this_robot->print_name().' shoots another '.$this_ability->print_name().'!'),
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Add the splash attachment to the target robot
            $target_robot->robot_attachments[$splash_attachment_token] = $splash_attachment_info;
            $target_robot->update_session();

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(20, 0, 0),
                'success' => array(1, 15, $y_offset, 10, 'The '.$this_ability->print_name().' burst on contact!'),
                'failure' => array(0, -65, $y_offset, -10, 'The '.$this_ability->print_name().' missed&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'frame' => 'taunt',
                'kickback' => array(10, 0, 0),
                'success' => array(1, 15, $y_offset, 10, 'The '.$this_ability->print_name().' burst on contact!'),
                'failure' => array(0, -65, $y_offset, -10, 'The '.$this_ability->print_name().' missed&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // Remove the splash attachment to the target robot
            unset($target_robot->robot_attachments[$splash_attachment_token]);
            $target_robot->update_session();

        }


        // Return true on success
        return true;

    }
    );
?>