<?
// QUICK STRIKE
$ability = array(
    'ability_name' => 'Quick Strike',
    'ability_token' => 'quick-strike',
    'ability_game' => 'MM02',
    'ability_group' => 'MM02/Weapons/012',
    'ability_description' => 'The user strikes at the target with blinding speed, dealing damage and causing them to drop any held item in their possesion! This ability always goes first.',
    'ability_type' => 'swift',
    'ability_energy' => 4,
    'ability_speed' => 6,
    'ability_damage' => 12,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'slide',
            'kickback' => array(180, 0, 0),
            'success' => array(0, -40, 0, -10, $this_robot->print_name().' uses the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Move the user forward so it looks like their swining the weapon
        $this_robot->set_frame('defend');
        $this_robot->set_frame_offset('x', 100);
        $this_robot->set_frame_styles('transform: scaleX(-1); -moz-transform: scaleX(-1); -webkit-transform: scaleX(-1); ');

        // Inflict damage on the opposing robot with a broom
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(20, 0, 0),
            'success' => array(1, 130, 0, 10, 'The '.$this_ability->print_name().' surprised the target!'),
            'failure' => array(1, 130, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // If this attack was successful, remove the target's held item from use (not permanently)
        if ($this_ability->ability_results['this_result'] != 'failure'
            && $target_robot->robot_status != 'disabled'
            && !empty($target_robot->robot_item)){

            // Change this robot's frame to a summon now
            $this_robot->set_frame('taunt');
            $this_robot->set_frame_offset('x', 80);
            $this_robot->set_frame_styles('');

            // Define this ability's attachment token
            $this_attachment_token = 'ability_'.$this_ability->ability_token;
            $this_attachment_info = array(
                'class' => 'ability',
                'attachment_token' => $this_attachment_token,
                'ability_token' => $this_ability->ability_token,
                'ability_frame' => 2,
                'ability_frame_animate' => array(2),
                'ability_frame_offset' => array('x' => 0, 'y' => 60, 'z' => 20)
                );

            // Remove the item from the target robot and update w/ attachment info
            $old_item_token = $target_robot->robot_item;
            $old_item = rpg_game::get_item($this_battle, $target_player, $target_robot, array('item_token' => $old_item_token));
            $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $target_robot->robot_item = '';
            $target_robot->update_session();

            // Update the ability's target options and trigger
            $temp_rotate_amount = 45;
            $old_item->set_frame_styles('opacity: 0.5; transform: rotate('.$temp_rotate_amount.'deg); -webkit-transform: rotate('.$temp_rotate_amount.'deg); -moz-transform: rotate('.$temp_rotate_amount.'deg); ');
            $old_item->target_options_update(array(
                'frame' => 'defend',
                'success' => array(0, -90, 0, 20,
                    $target_robot->print_name().' dropped '.$target_robot->get_pronoun('possessive2').' held item!'.
                    '<br /> The '.$old_item->print_name().' was lost!'
                    )
                ));
            $target_robot->trigger_target($target_robot, $old_item, array('prevent_default_text' => true));

            // Remove the visual icon attachment from the target
            unset($target_robot->robot_attachments[$this_attachment_token]);
            $target_robot->update_session();

        }

        // Reset the offset and move the user back to their position
        $this_robot->set_frame('base');
        $this_robot->set_frame_offset('x', 0);
        $this_robot->set_frame_styles('');

        // Return true on success
        return true;

    }
    );
?>