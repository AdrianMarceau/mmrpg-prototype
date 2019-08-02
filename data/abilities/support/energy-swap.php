<?
// ENERGY SWAP
$ability = array(
    'ability_name' => 'Energy Swap',
    'ability_token' => 'energy-swap',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Energy',
    'ability_description' => 'The user triggers an exploit in the prototype\'s code to instantly swap their own life energy with the target! When used by a support robot, this ability will swap with allies instead of the enemy!',
    'ability_energy' => 8,
    'ability_accuracy' => 100,
    'ability_target' => 'auto',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
            );

        // Attach this ability to the target temporarily
        $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
        $target_robot->update_session();

        // Check if this robot is targetting itself
        $has_target_self = $this_robot->robot_id == $target_robot->robot_id ? true : false;

        // Target this robot's self
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 0, 10, -10, $this_robot->print_name().' triggered an '.$this_ability->print_name().' with '.$target_robot->print_name().'!')
            ));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Remove this ability from the target
        unset($target_robot->robot_attachments[$this_attachment_token]);
        $target_robot->update_session();

        // Collect the current life energy for this and the target robot
        $this_current_life_energy = $this_robot->robot_energy;
        $target_current_life_energy = $target_robot->robot_energy;

        // If this robot happens to be targeting itself, nothing happens
        if ($has_target_self
            || $target_robot->robot_status != 'active'
            || $this_current_life_energy === $target_current_life_energy){

            // Update the ability's target options and trigger
            $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(0, 0, 0, 10, '...but nothing happened.')));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));
            return;

        }

        // If the target is holding a Guard Module, we are not allowed to modify stats
        if ($target_robot->robot_item == 'guard-module'){

            // Create a temp item object so we can show it resisting stat swaps
            $temp_item = rpg_game::get_item($this_battle, $target_player, $target_robot, array('item_token' => $target_robot->robot_item));
            $temp_message = '&hellip;but the held '.$temp_item->print_name().' kicked in! ';
            $temp_message .= '<br /> '.$target_robot->print_name().'\'s item protects '.$target_robot->get_pronoun('object').' from stat changes!';
            $temp_item->target_options_update(array( 'frame' => 'defend', 'success' => array(9, 0, 0, 10, $temp_message)));
            $target_robot->trigger_target($this_robot, $temp_item, array('prevent_default_text' => true));
            return;

        }

        // Apply the target's life energy to the user, keep track of change, crop if too high
        $this_new_life_energy = $target_current_life_energy;
        if ($this_new_life_energy > $this_robot->robot_base_energy){ $this_new_life_energy = $this_robot->robot_base_energy;  }
        $this_energy_change = 'stayed the same';
        if ($this_new_life_energy > $this_robot->robot_energy){  $this_energy_change = 'was increased'; }
        elseif ($this_new_life_energy < $this_robot->robot_energy){ $this_energy_change = 'was decreased'; }
        $this_robot->set_energy($this_new_life_energy);
        $this_robot_event_text = $this_robot->print_name().'\'s remaining life energy '.$this_energy_change.'! ';
        $this_robot_event_text .= '<br /> '.$this_robot->get_pronoun('possessive2').' energy is now at '.$this_robot->print_energy().' / '.$this_robot->print_robot_base_energy().'!';


        // Apply this robot's life energy to the target, keep track of change, crop if too high
        $target_new_life_energy = $this_current_life_energy;
        if ($target_new_life_energy > $target_robot->robot_base_energy){ $target_new_life_energy = $target_robot->robot_base_energy;  }
        $target_energy_change = 'stayed the same';
        if ($target_new_life_energy > $target_robot->robot_energy){  $target_energy_change = 'was increased'; }
        elseif ($target_new_life_energy < $target_robot->robot_energy){ $target_energy_change = 'was decreased'; }
        $target_robot->set_energy($target_new_life_energy);
        $target_robot_event_text = $target_robot->print_name().'\'s remaining life energy '.$target_energy_change.'! ';
        $target_robot_event_text .= '<br /> '.$target_robot->get_pronoun('possessive2').' energy is now at '.$target_robot->print_energy().' / '.$target_robot->print_robot_base_energy().'!';

        // Print out this robot's new energy amounts
        $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(9, 0, 10, -10, $this_robot_event_text)));
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

        // Print out this robot's new energy amounts
        $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(9, 0, 10, -10, $target_robot_event_text)));
        $target_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

        // Return true on success
        return true;

    },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Support robots can target allies, while others target the enemy (inlcuding bench w/ Target Module)
        if ($this_robot->robot_core === '' || $this_robot->robot_class == 'mecha'){ $this_ability->set_target('select_this_ally'); }
        elseif ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->set_target('auto'); }

        // Return true on success
        return true;

        }
    );
?>