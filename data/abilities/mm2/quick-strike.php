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

            // If the old item happened to be a life or weapon energy upgrade, recalculate stats
            if ($old_item_token == 'energy-upgrade'){
                //$this_battle->events_create(false, false, 'debug', 'The knocked-off item was a life energy upgrade!');
                $target_current_energy = $target_robot->robot_energy;
                $target_current_base_energy = $target_robot->robot_base_energy;
                $target_current_damage = $target_current_base_energy - $target_current_energy;
                $target_new_base_energy = $target_current_base_energy / 2;
                $target_new_energy = $target_new_base_energy - $target_current_damage;
                if ($target_new_energy > $target_new_base_energy){ $target_new_energy = $target_new_base_energy; }
                /*
                $this_battle->events_create(false, false, 'debug',
                    '$target_current_energy = '.$target_current_energy.
                    ' / '.'$target_current_base_energy = '.$target_current_base_energy.
                    ' <br /> '.'$target_current_damage = '.$target_current_damage.
                    ' <br /> '.'$target_new_energy = '.$target_new_energy.
                    ' / '.'$target_new_base_energy = '.$target_new_base_energy.
                    '');
                */
                $target_robot->robot_energy = $target_new_energy;
                $target_robot->robot_base_energy = $target_new_base_energy;
                $target_robot->update_session();
            } elseif ($old_item_token == 'weapon-upgrade'){
                //$this_battle->events_create(false, false, 'debug', 'The knocked-off item was a weapon energy upgrade!');
                $target_current_energy = $target_robot->robot_weapons;
                $target_current_base_energy = $target_robot->robot_base_weapons;
                $target_current_damage = $target_current_base_energy - $target_current_energy;
                $target_new_base_energy = $target_current_base_energy / 2;
                $target_new_energy = $target_new_base_energy - $target_current_damage;
                if ($target_new_energy > $target_new_base_energy){ $target_new_energy = $target_new_base_energy; }
                /*
                $this_battle->events_create(false, false, 'debug',
                    '$target_current_energy = '.$target_current_energy.
                    ' / '.'$target_current_base_energy = '.$target_current_base_energy.
                    ' <br /> '.'$target_current_damage = '.$target_current_damage.
                    ' <br /> '.'$target_new_energy = '.$target_new_energy.
                    ' / '.'$target_new_base_energy = '.$target_new_base_energy.
                    '');
                    */
                $target_robot->robot_weapons = $target_new_energy;
                $target_robot->robot_base_weapons = $target_new_base_energy;
                $target_robot->update_session();
            } elseif (strstr($old_item_token, '-core')){
                //$this_battle->events_create(false, false, 'debug', 'The knocked-off item was a robot core!');
                $lost_core_type = preg_replace('/-core$/', '', $old_item_token);
                $possible_attachment_token = 'ability_core-shield_'.$lost_core_type;
                if (!empty($target_robot->robot_attachments[$possible_attachment_token])){
                    $target_robot->robot_attachments[$possible_attachment_token]['attachment_duration'] = 0;
                    $target_robot->update_session();
                }
            }

            // Remove the visual icon attachment from the target
            unset($target_robot->robot_attachments[$this_attachment_token]);
            $target_robot->update_session();

        }

        // Reset the offset and move the user back to their position
        $this_robot->set_frame('base');
        $this_robot->set_frame_offset('x', 0);
        $this_robot->set_frame_styles('');

        // Loop through all robots on the target side and disable any that need it
        $target_robots_active = $target_player->get_robots();
        foreach ($target_robots_active AS $key => $robot){
            if ($robot->robot_id == $target_robot->robot_id){ $temp_target_robot = $target_robot; }
            else { $temp_target_robot = $robot; }
            if (($temp_target_robot->robot_energy < 1 || $temp_target_robot->robot_status == 'disabled')
                && empty($temp_target_robot->flags['apply_disabled_state'])){
                $temp_target_robot->trigger_disabled($this_robot);
            }
        }

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>