<?
// TIME SLOW
$ability = array(
    'ability_name' => 'Time Slow',
    'ability_token' => 'time-slow',
    'ability_game' => 'MM01',
    'ability_group' => 'MM01/Weapons/00A',
    'ability_description' => 'The user charges itself with temporal energy to boost the power of Time type attacks. If used again after charging this ability can slow a single target and severely lower their speed stat!',
    'ability_type' => 'time',
    'ability_energy' => 8,
    'ability_recovery2' => 33,
    'ability_recovery2_percent' => true,
    'ability_damage2' => 33,
    'ability_damage2_percent' => true,
    'ability_accuracy' => 100,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_boost_modifier = 1 + ($this_ability->ability_recovery2 / 100);
        $this_attachment_break_modifier = 1 - ($this_ability->ability_damage2 / 100);
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(1, 0),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10),
            'attachment_damage_output_booster_'.$this_ability->ability_type => $this_attachment_boost_modifier,
            'attachment_damage_input_breaker_'.$this_ability->ability_type => $this_attachment_break_modifier,
            'attachment_recovery_output_booster_'.$this_ability->ability_type => $this_attachment_boost_modifier,
            'attachment_recovery_input_breaker_'.$this_ability->ability_type => $this_attachment_break_modifier
            );

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
                'frame' => 'summon',
                'kickback' => array(0, 0, 0),
                'success' => array(5, 5, 70, 10, $this_robot->print_name().' releases the '.$this_ability->print_name().'!'),
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Update this ability's target options and trigger
            $this_ability->target_options_update(array(
                'frame' => 'damage',
                'kickback' => array(-10, 0, 0),
                'success' => array(3, 5, 70, -10, 'The '.$this_ability->print_name().' looms behind '.$target_robot->print_name().'&hellip;'),
                ));
            $target_robot->trigger_target($this_robot, $this_ability);

            // Call the global stat break function with customized options
            rpg_ability::ability_function_stat_break($target_robot, 'speed', 3);

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

        // If the ability is already charged, allow bench targeting
        if ($is_charged){ $this_ability->set_target('select_target'); }
        else { $this_ability->set_target('auto'); }

        // Return true on success
        return true;

        }
    );
?>