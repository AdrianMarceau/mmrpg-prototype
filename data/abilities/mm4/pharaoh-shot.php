<?
// PHARAOH SHOT
$ability = array(
    'ability_name' => 'Pharaoh Shot',
    'ability_token' => 'pharaoh-shot',
    'ability_game' => 'MM04',
    //'ability_group' => 'MM04/Weapons/028',
    'ability_group' => 'MM04/Weapons/025T1',
    'ability_description' => 'The user charges itself with a solar energy that doubles the damage of its own attacks for up to three turns.  If this ability is used again after charging, the held solar energy will instead be released at the target to inflict massive damage!',
    'ability_type' => 'flame',
    'ability_energy' => 4,
    'ability_damage' => 30,
    'ability_accuracy' => 96,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define the base attachment duration
        $base_attachment_duration = 3;
        $base_attachment_multiplier = 2.0;

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(2, 3, 4, 5, 6),
            'ability_frame_offset' => array('x' => 4, 'y' => 50, 'z' => 12),
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => $base_attachment_duration + 1,
            'attachment_damage_output_booster' => $base_attachment_multiplier,
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(2, -24, 10, -18,  'The '.$this_ability->print_name().' faded away!<br /> '.$target_robot->print_name().'\'s weapons returned to normal!'),
                'failure' => array(2, -24, 10, -18, 'The '.$this_ability->print_name().' faded away!<br /> '.$target_robot->print_name().'\'s weapons returned to normal!')
                )
            );

        // Check if this ability is already charged
        $is_charged = isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

        // If the user is holding a Charge Module, auto-charge the ability
        if ($this_robot->has_item('charge-module')){ $is_charged = true; }

        // If the ability flag was not set, this ability begins charging
        if (!$is_charged){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(1, 4, 50, 12, $this_robot->print_name().' charges the '.$this_ability->print_name().'&hellip;')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

        }
        // Else if the ability flag was set, the ability is released at the target
        else {

            // Hide the attachment before we remove it (so the effect still applies)
            $this_robot->robot_attachments[$this_attachment_token]['ability_frame'] = 9;
            $this_robot->robot_attachments[$this_attachment_token]['ability_frame_animate'] = array(9);
            $this_robot->robot_attachments[$this_attachment_token]['ability_frame_offset']['z'] = -9999;
            $this_robot->update_session();

            // Update this ability's target options and trigger
            $this_ability->target_options_update(array(
                'frame' => 'throw',
                'kickback' => array(-5, 0, 0),
                'success' => array(5, 100, 25, 12, $this_robot->print_name().' releases the '.$this_ability->print_name().'!'),
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(20, 0, 0),
                'success' => array(6, -110, 25, 12, 'A massive energy shot hit the target!'),
                'failure' => array(6, -110, 25, -12, 'The '.$this_ability->print_name().' missed&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // Remove this ability attachment to the robot using it
            unset($this_robot->robot_attachments[$this_attachment_token]);
            $this_robot->update_session();

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

        // Return true on success
        return true;

        }
    );
?>