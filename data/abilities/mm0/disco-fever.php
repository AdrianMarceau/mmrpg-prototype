<?
// DISCO FEVER
$ability = array(
    'ability_name' => 'Disco Fever',
    'ability_token' => 'disco-fever',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MM00/Weapons/Disco',
    'ability_image_sheets' => 2,
    'ability_description' => 'The user summons a spinning disco ball that hovers above the target to divert its attention. The target deals half damage from attacks while distracted by the ball.',
    'ability_type' => 'laser',
    'ability_energy' => 4,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's overlay effect token
        $this_overlay_token = 'effect_'.$this_ability->ability_token.'_'.$target_robot->robot_id;
        $this_overlay_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => 'ability',
            'ability_image' => 'ability-effect_black-overlay',
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1),
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -20),
            'ability_frame_classes' => 'sprite_fullscreen ',
            'attachment_token' => $this_overlay_token
            );

        // Create the attachment object for this ability
        $this_overlay = rpg_game::get_ability($this_battle, $target_player, $target_robot, $this_overlay_info);

        // Add the black background overlay attachment
        $target_robot->robot_attachments[$this_overlay_token] = $this_overlay_info;
        $target_robot->update_session();

        // Predefine attachment create and destroy text for later
        $this_create_text = ($target_robot->print_name().' found '.$target_robot->get_pronoun('reflexive').' behind a spinning '.rpg_type::print_span('laser', 'Disco Ball').'!<br /> '.
            $target_robot->print_name().'\'s damage output has been compromised!'
            );
        $this_refresh_text = ('The '.rpg_type::print_span('laser', 'Disco Ball').' in front of '.$target_robot->print_name().' keeps spinning!<br /> '.
            ucfirst($target_robot->get_pronoun('possessive2')).' damage output is still compromised!'
            );

        // Define this ability's attachment token
        $static_attachment_key = $target_robot->get_static_attachment_key();
        $static_attachment_duration = 6;
        $this_attachment_info = rpg_ability::get_static_disco_ball($static_attachment_key, $static_attachment_duration);
        $this_attachment_token = $this_attachment_info['attachment_token'];

        // Update the attachment image if a special robot is using it
        $static_attachment_image = in_array($this_robot->robot_image, array('disco_alt', 'disco_alt3', 'disco_alt5')) ? $this_ability->ability_token.'-2' : $this_ability->ability_image;
        $this_attachment_info['ability_image'] = $static_attachment_image;

        // Create the attachment object for this ability
        $this_attachment = rpg_game::get_ability($this_battle, $target_player, $target_robot, $this_attachment_info);

        // If the ability flag was not set, attach the hazard to the target position
        if (!isset($this_battle->battle_attachments[$static_attachment_key][$this_attachment_token])){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -10, 0, -18, $this_robot->print_name().' started a '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $this_attachment_info['ability_frame_animate'] = array(0, 1, 2, 1);
            $this_battle->battle_attachments[$static_attachment_key][$this_attachment_token] = $this_attachment_info;
            $this_battle->update_session();

            // Target this robot's self
            $this_robot->robot_frame = 'base';
            $this_robot->update_session();
            $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(0, -9999, -9999, -9999, $this_create_text)));
            $target_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

        }
        // Else if the ability flag was set, reinforce the fever by one more duration point
        else {

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -10, 0, -18, $this_robot->print_name().' continued the '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Collect the attachment from the robot to back up its info
            $this_attachment_info = $this_battle->battle_attachments[$static_attachment_key][$this_attachment_token];
            if (empty($this_attachment_info['attachment_duration'])
                || $this_attachment_info['attachment_duration'] < $static_attachment_duration){
                $this_attachment_info['attachment_duration'] = $static_attachment_duration;
                $this_battle->battle_attachments[$static_attachment_key][$this_attachment_token] = $this_attachment_info;
                $this_battle->update_session();
            }
            if ($target_robot->robot_status != 'disabled'){
                $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(0, -9999, -9999, -9999, $this_refresh_text)));
                $target_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));
            }

        }

        // Add the black background overlay attachment
        unset($target_robot->robot_attachments[$this_overlay_token]);
        $target_robot->update_session();

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

        // Update the ability image if the user is in their alt image
        $alt_image_triggers = array('disco_alt', 'disco_alt3', 'disco_alt5');
        if (in_array($this_robot->robot_image, $alt_image_triggers)){ $this_ability->set_image($this_ability->ability_token.'-2'); }

        // Return true on success
        return true;

        }
    );
?>