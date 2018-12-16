<?
// LIGHT BUSTER
$ability = array(
    'ability_name' => 'Light Buster',
    'ability_token' => 'light-buster',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MM00/Weapons/00/Doctors',
    'ability_description' => 'The user charges to build power and raise its defense stat. If used again after charging this ability can release a powerful energy shot to inflict massive damage! This ability\'s power increases when used by a robot belonging to Dr. Light.',
    'ability_player' => 'dr-light',
    'ability_energy' => 4,
    'ability_damage' => 34,
    'ability_accuracy' => 100,
    'ability_target' => 'auto',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If this ability is being used by a robot of a matching original player, boost power
        if (!empty($this_robot->robot_original_player) && $this_robot->robot_original_player == 'dr-light'){
            $this_ability->set_name($this_ability->ability_base_name . ' Δ');
            $this_ability->set_damage(ceil($this_ability->ability_base_damage * 1.2));
        } else {
            $this_ability->reset_name();
            $this_ability->reset_damage();
        }

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(1, 2, 1, 0),
            'ability_frame_offset' => array('x' => -10, 'y' => -10, 'z' => -20)
            );

        // Loop through each existing attachment and alter the start frame by one
        foreach ($this_robot->robot_attachments AS $key => $info){ array_push($this_attachment_info['ability_frame_animate'], array_shift($this_attachment_info['ability_frame_animate'])); }

        // Check if this ability is already charged
        $is_charged = isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

        // If the user is holding a Charge Module, auto-charge the ability
        if ($this_robot->has_item('charge-module')){ $is_charged = true; }

        // If the ability flag was not set, this ability begins charging
        if (!$is_charged){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(1, -10, 0, -10, $this_robot->print_name().' charges the '.$this_ability->print_name().'&hellip;')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Call the global stat boost function with customized options
            rpg_ability::ability_function_stat_boost($this_robot, 'defense', 1);

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
                'frame' => 'shoot',
                'kickback' => array(-5, 0, 0),
                'success' => array(3, 100, -15, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!'),
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(20, 0, 0),
                'success' => array(3, -110, -15, 10, 'A massive energy shot hit the target!'),
                'failure' => array(3, -110, -15, -10, 'The '.$this_ability->print_name().' shot missed&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        }

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;

        // Check if this ability is already charged
        $is_charged = isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

        // If the ability flag had already been set, reduce the weapon energy to zero
        if ($is_charged){ $this_ability->set_energy(0); }
        // Otherwise, return the weapon energy back to default
        else { $this_ability->reset_energy(); }

        // If the user is holding a Charge Module, auto-charge the ability
        if ($this_robot->has_item('charge-module')){ $is_charged = true; }

        // If the user is holding a Target Module, allow bench targeting
        if ($is_charged && $this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // If this ability is being used by a robot of a matching original player, boost power
        if (!empty($this_robot->robot_original_player) && $this_robot->robot_original_player == 'dr-light'){
            $this_ability->set_name($this_ability->ability_base_name . ' Δ');
            $this_ability->set_damage(ceil($this_ability->ability_base_damage * 1.2));
        } else {
            $this_ability->reset_name();
            $this_ability->reset_damage();
        }

        // Return true on success
        return true;

        }
    );
?>