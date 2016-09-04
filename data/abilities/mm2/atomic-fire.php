<?
// ATOMIC FIRE
$ability = array(
    'ability_name' => 'Atomic Fire',
    'ability_token' => 'atomic-fire',
    'ability_game' => 'MM02',
    'ability_description' => 'The user unleashes a barrage of fire at the target, dealing damage and raising the user\'s attack by {RECOVERY2}%! This ability has three levels of charge, with damage multiplying on each successive barrage until the limit is reached or another technique is used.',
    'ability_type' => 'flame',
    'ability_energy' => 4,
    'ability_damage' => 10,
    'ability_recovery2' => 10,
    'ability_recovery2_percent' => true,
    'ability_accuracy' => 95,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        if (!isset($this_robot->robot_attachments[$this_attachment_token])){
            $this_attachment_info = array(
                'class' => 'ability',
                'ability_token' => $this_ability->ability_token,
                'attachment_token' => $this_attachment_token,
                'attachment_duration' => 1,
                'attachment_power' => 0,
                'attachment_weaknesses' => array('water', 'freeze', 'earth'),
                'attachment_create' => array(
                    'kind' => 'attack',
                    'percent' => true,
                    'modifiers' => false,
                    'frame' => 'taunt',
                    'rates' => array(100, 0, 0),
                    'success' => array(0, 0, 0, -9999, $this_robot->print_name().'&#39;s flame was bolstered!'),
                    'failure' => array(0, 0, 0, -9999, $this_robot->print_name().'&#39;s flame was bolstered!')
                    ),
                'attachment_destroy' => array(
                    'trigger' => 'special',
                    'kind' => '',
                    'frame' => 'defend',
                    'rates' => array(100, 0, 0),
                    'success' => array(0, 0, 0, -9999,  'The '.$this_ability->print_name().'&#39;s flame was lost&hellip;'),
                    'failure' => array(0, 0, 0, -9999, $this_robot->print_name().'&#39;s flame was lost&hellip;')
                    ),
                'ability_frame' => 3,
                'ability_frame_animate' => array(3, 4),
                'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
                );
        } else {
            $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
            $this_attachment_info['attachment_duration'] = 1;
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();
        }

        // Create the attachment object for this ability
        $this_attachment = new rpg_ability($this_battle, $this_player, $this_robot, $this_attachment_info);

        // Collect the shot power counter if set, otherwise default to level one
        $shot_power = !empty($this_attachment_info['attachment_power']) ? $this_attachment_info['attachment_power'] : 0;

        // Reward successive uses of this ability with boosts in power
        if (!empty($this_robot->history['triggered_abilities'])){
            // Collect up to the last three triggered abilities
            $ability_history_count = count($this_robot->history['triggered_abilities']);
            if ($ability_history_count <= 3){ $recent_ability_history = $this_robot->history['triggered_abilities']; }
            else { $recent_ability_history = array_slice($this_robot->history['triggered_abilities'], -3, 3, false); }
            $recent_ability_history = array_reverse($recent_ability_history, false);
            // If this ability was used last turn, increment the base power
            if (isset($recent_ability_history[1]) && $recent_ability_history[1] == $this_ability->ability_token){ $shot_power++; }
            else { $shot_power = 1; }
        }

        // Update this ability's internal shot power counter
        $this_attachment_info['attachment_power'] = $shot_power;

        // Update the text and animation frames
        if ($shot_power == 1){ $shot_power_frame = 0; $shot_power_text = 'A flare '; }
        elseif ($shot_power == 2){ $shot_power_frame = 1; $shot_power_text = 'A powerful flare '; }
        elseif ($shot_power >= 3){ $shot_power_frame = 2; $shot_power_text = 'A massive flare '; }

        // If the shot power is charging, attach this ability to the robot
        if ($shot_power == 1){ $this_attachment_info['ability_frame_animate'] = array(3, 4); }
        elseif ($shot_power == 2){ $this_attachment_info['ability_frame_animate'] = array(5, 6); }
        elseif ($shot_power >= 3){ $this_attachment_info['ability_frame_animate'] = array(9); }
        $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $this_robot->update_session();

        // Update the ability's target options and trigger
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array($shot_power_frame, 100 + (30 * $shot_power), 0, 10, $this_robot->print_name().' throws an '.$this_ability->print_name().'!') // [shot_power='.$shot_power.'|attachment_defense='.$this_attachment_info['attachment_defense'].']
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(($shot_power * 10), 0, 0),
            'success' => array($shot_power_frame, (-20 - (40 * $shot_power)), 0, 10, $shot_power_text.' hit the target!'),
            'failure' => array($shot_power_frame, (-50 - (60 * $shot_power)), 0, -10, $this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array($shot_power_frame, (-20 - (40 * $shot_power)), 0, 10, $shot_power_text.' ignited the target!'),
            'failure' => array($shot_power_frame, (-50 - (60 * $shot_power)), 0, -10, $this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Update the damage and recovery options for the ability
        $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
        $this_ability->recovery_options_update($this_attachment_info['attachment_create'], true);

        // Trigger an attack boost if the ability was successful
        if ($this_robot->robot_status != 'disabled' && $this_ability->ability_results['this_result'] == 'success'){
            $attack_recovery_amount = ceil($this_robot->robot_defense * ($this_ability->ability_recovery2 / 100));
            $trigger_options = array('apply_modifiers' => false);
            $this_robot->trigger_recovery($this_robot, $this_ability, $attack_recovery_amount, true, $trigger_options);
        }

        // If the shot power was at maximum, remove the attachment from the robot
        if ($shot_power >= 3){
            unset($this_robot->robot_attachments[$this_attachment_token]);
            unset($this_attachment->counters['shot_power']);
            $this_robot->update_session();
            $this_attachment->update_session();
        }

        // Either way, update this ability's settings to prevent recovery
        $this_attachment->damage_options_update($this_attachment_info['attachment_destroy'], true);
        $this_attachment->recovery_options_update($this_attachment_info['attachment_destroy'], true);
        $this_attachment->update_session();

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update this ability's damage if it's already attached to the robot
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        if (isset($this_robot->robot_attachments[$this_attachment_token])){
            $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
            $shot_power = !empty($this_attachment_info['attachment_power']) ? $this_attachment_info['attachment_power'] + 1 : 1;
            $ability_damage = ceil($this_ability->ability_base_damage * $shot_power);
            $ability_recovery2 = ceil($this_ability->ability_base_recovery2 * $shot_power);
            $this_ability->set_damage($ability_damage);
            $this_ability->set_recovery2($ability_recovery2);
            if ($shot_power > 1){ $this_ability->set_name($this_ability->ability_base_name.' '.$shot_power); }
            else { $this_ability->set_name($this_ability->ability_base_name); }
        } else {
            $this_ability->set_damage($this_ability->ability_base_damage);
            $this_ability->set_recovery2($this_ability->ability_base_recovery2);
            $this_ability->set_name($this_ability->ability_base_name);
        }

        // Return true on success
        return true;

        }
    );
?>