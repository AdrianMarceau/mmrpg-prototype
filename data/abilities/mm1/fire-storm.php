<?
// FIRE STORM
$ability = array(
    'ability_name' => 'Fire Storm',
    'ability_token' => 'fire-storm',
    'ability_game' => 'MM01',
    'ability_group' => 'MM01/Weapons/007',
    'ability_description' => 'The user surrounds itself in flames then unleashes a blast of fire at the target to deal damage. This ability grows stronger with consecutive use, culminating in a drastic boost to the user\'s defense stat if uninterrupted.',
    'ability_type' => 'flame',
    'ability_energy' => 4,
    'ability_damage' => 12,
    'ability_accuracy' => 96,
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
                'attachment_weaknesses' => array('water', 'freeze', 'wind'),
                'attachment_create' => array(
                    'kind' => 'defense',
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
                    'success' => array(0, 0, 0, -9999, $this_robot->print_name().'&#39;s flame was lost&hellip;'),
                    'failure' => array(0, 0, 0, -9999, $this_robot->print_name().'&#39;s flame was lost&hellip;')
                    ),
                    'ability_frame' => 3,
                    'ability_frame_animate' => array(3, 4, 5, 6),
                    'ability_frame_offset' => array('x' => -15, 'y' => -5, 'z' => -10)
                );
        } else {
            $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
            $this_attachment_info['attachment_duration'] = 1;
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();
        }

        // Create the attachment object for this ability
        $this_attachment = rpg_game::get_ability($this_battle, $this_player, $this_robot, $this_attachment_info);

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
        if ($shot_power == 1){ $this_attachment_info['ability_frame_animate'] = array(3, 4, 5, 6); }
        elseif ($shot_power == 2){ $this_attachment_info['ability_frame_animate'] = array(7, 8); }
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

        // If this was the final shot and the attack was successful, we can boost their stats
        if ($shot_power >= 3
            && $this_robot->robot_status != 'disabled'
            && $this_ability->ability_results['this_result'] == 'success'){

            // Call the global stat boost function with customized options
            rpg_ability::ability_function_stat_boost($this_robot, 'defense', 3);

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

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Update this ability's damage if it's already attached to the robot
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        if (isset($this_robot->robot_attachments[$this_attachment_token])){
            $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
            $shot_power = !empty($this_attachment_info['attachment_power']) ? $this_attachment_info['attachment_power'] + 1 : 1;
            $shot_numeral = $shot_power == 3 ? 'III' : 'II';
            $ability_damage = ceil($this_ability->ability_base_damage + (($shot_power - 1) * 2));
            $this_ability->set_damage($ability_damage);
            if ($shot_power > 1){ $this_ability->set_name($this_ability->ability_base_name.' '.$shot_numeral); }
            else { $this_ability->set_name($this_ability->ability_base_name); }
        } else {
            $this_ability->set_damage($this_ability->ability_base_damage);
            $this_ability->set_name($this_ability->ability_base_name);
        }

        // Return true on success
        return true;

        }
    );
?>