<?
// SUPER ARM
$ability = array(
    'ability_name' => 'Super Arm',
    'ability_token' => 'super-arm',
    'ability_game' => 'MM01',
    //'ability_group' => 'MM01/Weapons/004',
    'ability_group' => 'MM01/Weapons/003T2',
    'ability_image_sheets' => 8,
    'ability_description' => 'The user creates a blockade using the surrounding environment to bolster shields and reduce damage by half!  The blockade can also be thrown at the target for massive damage!',
    'ability_type' => 'impact',
    'ability_type2' => 'shield',
    'ability_energy' => 8,
    'ability_damage' => 30,
    'ability_recovery2' => 50,
    'ability_recovery_percent2' => true,
    'ability_accuracy' => 98,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define the sprite sheet and animation defaults
        $this_field_token = $this_battle->battle_field->field_background;
        $this_sprite_sheet = 1;
        $this_target_frame = 0;
        $this_impact_frame = 1;
        $this_object_name = 'boulder';

        // Collect the sprite index for this ability
        $this_sprite_index = rpg_ability::get_super_block_sprite_index();

        // If the field token has a place in the index, update values
        if (isset($this_sprite_index[$this_field_token])){
            $this_sprite_sheet = $this_sprite_index[$this_field_token][0];
            $this_target_frame = $this_sprite_index[$this_field_token][1];
            $this_impact_frame = $this_sprite_index[$this_field_token][2];
            $this_object_name = $this_sprite_index[$this_field_token][3];
        }

        // Upper-case object name while being sensitive to of/the/a/etc.
        $this_object_name = ucwords($this_object_name);
        $this_object_name = str_replace(array(' A ', ' An ', ' Of ', ' The '), array(' a ', ' an ', ' of ', ' the '), $this_object_name);
        $this_object_name_span = rpg_type::print_span('impact', $this_object_name);

        // Define this ability's attachment token
        $static_attachment_key = $this_robot->get_static_attachment_key();
        $static_attachment_duration = 10;
        $this_attachment_info = rpg_ability::get_static_super_block($static_attachment_key, $static_attachment_duration);
        $this_attachment_token = $this_attachment_info['attachment_token'];

        // Create the attachment object for this ability
        $this_attachment = rpg_game::get_ability($this_battle, $this_player, $this_robot, $this_attachment_info);
        $this_attachment->set_image($this_attachment_info['ability_image']);

        // Update the image of the actual ability so it matches
        $this_ability->set_image($this_attachment_info['ability_image']);

        // Check if this ability is already summoned to the field
        $is_summoned = isset($this_battle->battle_attachments[$static_attachment_key][$this_attachment_token]) ? true : false;

        // If the user is holding a Charge Module, auto-summon the ability
        if ($this_robot->has_item('charge-module')){ $is_summoned = true; }

        // If the ability flag was not set, this ability begins charging
        if (!$is_summoned){

            // Attach this ability attachment to the battle field itself
            $this_attachment_info['ability_frame_styles'] = 'opacity: 0.1; ';
            $this_battle->battle_attachments[$static_attachment_key][$this_attachment_token] = $this_attachment_info;
            $this_battle->update_session();

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array($this_target_frame, -9999, -9999, 0, $this_robot->print_name().' uses the '.$this_ability->print_name().' technique! ')
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

            // Attach this ability attachment to the battle field itself
            $this_attachment_info['ability_frame_styles'] = '';
            $this_battle->battle_attachments[$static_attachment_key][$this_attachment_token] = $this_attachment_info;
            $this_battle->update_session();

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array($this_target_frame, -9999, -9999, 0, 'The '.$this_ability->print_name().' created '.
                    (preg_match('/^(a|e|i|o|u)/i', $this_object_name) ? 'an ' : 'a ').
                    $this_object_name_span.
                    ' as a shield!<br /> '.
                    'Damage from incoming attacks will be reduced!'
                    )
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

        }
        // Else if the ability flag was set, the block is thrown and the attachment goes away
        else {

            // Remove this ability attachment from the battle field itself
            unset($this_battle->battle_attachments[$static_attachment_key][$this_attachment_token]);
            $this_battle->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'throw',
                'success' => array($this_impact_frame, 175, 15, 10, $this_ability->print_name().' throws the '.$this_object_name_span.'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(20, 0, 0),
                'success' => array($this_impact_frame, -125, 5, 10, 'The '.$this_object_name_span.' crashed into the target!'),
                'failure' => array($this_impact_frame, -125, 5, -10, 'The '.$this_object_name_span.' missed the target&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 0, 0),
                'success' => array($this_impact_frame, -125, 5, 10, 'The '.$this_object_name_span.' crashed into the target!'),
                'failure' => array($this_impact_frame, -125, 5, -10, 'The '.$this_object_name_span.' missed the target&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        }

        // Either way, update this ability's settings to prevent recovery
        $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
        $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
        $this_ability->update_session();

        // Return true on success
        return true;

    },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $static_attachment_key = $this_robot->get_static_attachment_key();
        $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$static_attachment_key;

        // Check if this ability is already summoned to the field
        $is_summoned = isset($this_battle->battle_attachments[$static_attachment_key][$this_attachment_token]) ? true : false;

        // Check if this ability has a true core-match
        $is_corematch = $this_robot->robot_core == $this_ability->ability_type ? true : false;

        // If the ability flag had already been set, reduce the weapon energy to zero
        if ($is_summoned){ $this_ability->set_energy(0); }
        // Otherwise, return the weapon energy back to default
        else { $this_ability->reset_energy(); }

        // If the user is holding a Charge Module, auto-charge the ability
        if ($this_robot->has_item('charge-module')){ $is_summoned = true; }

        // If the ability is already summoned and is core-match or Target Module, allow bench targeting
        if ($is_summoned && ($is_corematch || $this_robot->has_item('target-module'))){ $this_ability->set_target('select_target'); }
        else { $this_ability->set_target('auto'); }

        // Define the sprite sheet and animation defaults
        $this_field_token = $this_battle->battle_field->field_background;
        $this_sprite_sheet = 1;

        // Collect the sprite index for this ability
        $this_sprite_index = rpg_ability::get_super_block_sprite_index();

        // If the field token has a place in the index, update values
        if (isset($this_sprite_index[$this_field_token])){ $this_sprite_sheet = $this_sprite_index[$this_field_token][0]; }

        // Update the ability's image in the session
        $this_ability->set_image($this_ability->ability_token.($this_sprite_sheet > 1 ? '-'.$this_sprite_sheet : ''));

        // Return true on success
        return true;

        }
    );
?>