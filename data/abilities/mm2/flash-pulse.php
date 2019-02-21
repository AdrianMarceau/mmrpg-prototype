<?
// FLASH PULSE
$ability = array(
    'ability_name' => 'Flash Pulse',
    'ability_token' => 'flash-pulse',
    'ability_game' => 'MM02',
    'ability_group' => 'MM02/Weapons/014',
    'ability_description' => 'The user sends a temporal shockwave toward at the target, dealing damage and switching to a benched robot while the opponent is distracted by the attack!',
    'ability_type' => 'time',
    'ability_energy' => 4,
    'ability_damage' => 16,
    'ability_accuracy' => 98,
    'ability_target' => 'select_this_ally',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'attachment_token' => $this_attachment_token,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(0),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -1)
            );

        // Swap around the 'target' and 'ally' robot variables
        $benched_robot = false;
        if ($target_robot->player->player_side == $this_robot->player->player_side
            && $target_robot->robot_id != $this_robot->robot_id
            && $target_robot->robot_status != 'disabled'){
            $benched_robot = $target_robot;
        }

        // Collect the actual target player and robot, regardless of selection
        $actual_target_player = rpg_game::find_player(array('player_side' => $this_player->player_side), true);
        $actual_target_robot = $actual_target_player->get_active_robot();

        // Attach the ability sprite to the user and bench if exists
        $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $this_robot->update_session();
        if (!empty($benched_robot)){
            $benched_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $benched_robot->update_session();
        }

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(1, 120, 0, 10, $this_robot->print_name().' generates a '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($actual_target_robot, $this_ability, array('prevent_default_text' => true));

        // If we're going to be switching, put this robot on the bench now
        if (!empty($benched_robot)){

            // Swap positions of the two robots
            $this_robot->set_position('bench');
            $benched_robot->set_position('active');

            // Set both robots to their react frame
            $this_robot->set_frame('taunt');
            $benched_robot->set_frame('summon');
        }

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(15, 0, 0),
            'success' => array(1, -65, 0, 10, 'The '.$this_ability->print_name().' shocked into the target!'),
            'failure' => array(1, -95, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(10, 0, 0),
            'success' => array(1, -65, 0, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(1, -95, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $actual_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        // Remove the ability sprite from the user
        unset($this_robot->robot_attachments[$this_attachment_token]);
        $this_robot->update_session();

        // If we're going to be switching, put this robot on the bench now
        if (!empty($benched_robot)){

            // Remove the ability sprite from the benched robot
            unset($benched_robot->robot_attachments[$this_attachment_token]);
            $benched_robot->update_session();

            // Set both robots to their next frame
            $this_robot->set_frame('defend');
            $benched_robot->set_frame('taunt');

            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(9, 0, 0, -99, $this_robot->print_name().' swapped places with '.$benched_robot->print_name().'!')
                ));
            $this_robot->trigger_target($benched_robot, $this_ability, array('prevent_default_text' => true));

            // Reset robots their base position
            $this_robot->set_frame('base');
            $benched_robot->set_frame('base');

        }

        // Check to see if the target was disabled and apply the status if so
        if (($actual_target_robot->robot_energy < 1 || $actual_target_robot->robot_status == 'disabled')
            && empty($actual_target_robot->flags['apply_disabled_state'])){
            $actual_target_robot->trigger_disabled($this_robot);
        }

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Adjust the target selection option based on available benched robots
        if ($this_player->counters['robots_active'] > 1){ $this_ability->set_target('select_this_ally'); }
        else { $this_ability->set_target('auto'); }

        // Return true on success
        return true;

        }
    );
?>