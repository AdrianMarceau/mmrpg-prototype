<?
// ENERGY SWAP
$ability = array(
    'ability_name' => 'Energy Swap',
    'ability_token' => 'energy-swap',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Energy',
    'ability_description' => 'The user triggers an exploit in the prototype to swap their own life energy with the target\'s!  However the weapon energy cost for this ability increases after each use.',
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

        // Target this robot's self
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 0, 10, -10, $this_robot->print_name().' triggered an '.$this_ability->print_name().' with '.$target_robot->print_name().'!')
            ));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Remove this ability from the target
        unset($target_robot->robot_attachments[$this_attachment_token]);
        $target_robot->update_session();

        // If this robot happens to be targeting itself, nothing happens
        if ($this_robot->robot_id == $target_robot->robot_id){

            // Update the ability's target options and trigger
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(0, 0, 0, 10, '&hellip;but nothing happened.')
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));
            return;

        }

        // Create a function that increases or decreases a robot's energy to target
        $temp_energy_function = function($this_robot, $this_ability, $temp_this_energy, $temp_target_energy){
            global $this_battle;

            // Collect the target's current energy amount
            //$temp_this_energy = $this_robot->robot_energy.'/'.$this_robot->robot_base_energy;

            //$this_battle->events_create(false, false, 'DEBUG '.__LINE__, '$temp_this_energy = '.$temp_this_energy.', $temp_target_energy = '.$temp_target_energy);

            // Only continue if this robot and the target's energy are not equal
            if ($temp_this_energy != $temp_target_energy){

                // Break apart the energy into its current and base amounts
                list($temp_energy, $temp_base_energy) = explode('/', $temp_target_energy);

                // Update this robot's values with the random data
                $this_robot->robot_energy = $temp_energy;
                $this_robot->robot_base_energy = $temp_base_energy;
                $this_robot->update_session();

                // Target this robot's self
                $is_her = in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm', 'splash-woman')) ? true : false;
                $is_mecha = $this_robot->robot_class == 'mecha' ? true : false;
                $this_ability->target_options_update(array(
                    'frame' => 'defend',
                    'success' => array(9, 0, 10, -10, $this_robot->print_name().'&#39;s life energy was modified&hellip;<br /> '.($is_her ? 'Her' : ($is_mecha ? 'Its' : 'His')).' new energy stats are '.$this_robot->print_energy().' / '.$this_robot->print_robot_base_energy().'!')
                    ));
                $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

            }
            // Otherwise, if the two already have equal energy amounts
            else {

                // Target this robot's self and show the ability failing
                $this_ability->target_options_update(array(
                    'frame' => 'defend',
                    'success' => array(9, 0, 0, -10, $this_robot->print_name().'&#39;s life energy was not affected&hellip;')
                    ));
                $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

                // Return true on success (well, failure, but whatever)
                return true;

            }

        };

        // Collect the target's current energy amount
        $temp_this_energy = $this_robot->robot_energy.'/'.$this_robot->robot_base_energy;
        // Collect this robot's current energy amount
        $temp_target_energy = $target_robot->robot_energy.'/'.$target_robot->robot_base_energy;

        // Update this robot's energy to that of the target's
        $temp_energy_function($this_robot, $this_ability, $temp_this_energy, $temp_target_energy);
        // Update the target's energy to that of this robot
        $temp_energy_function($target_robot, $this_ability, $temp_target_energy, $temp_this_energy);

        // Return true on success
        return true;

    },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If used by support robot OR the has a Target Module, allow opponent targetting
        $temp_support_robots = array('roll', 'disco', 'rhythm');
        if (in_array($this_robot->robot_token, $temp_support_robots)
            || $this_robot->has_item('target-module')){ $this_ability->set_target('select_this'); }
        else { $this_ability->set_target('auto'); }

        // Check to see if this ability has been used already, and if so increase the cost
        if (!empty($this_robot->history['triggered_abilities'])){
            $trigger_counts = array_count_values($this_robot->history['triggered_abilities']);
            if (!empty($trigger_counts[$this_ability->ability_token])){
                $trigger_count = $trigger_counts[$this_ability->ability_token];
                $new_energy_cost = $this_ability->ability_base_energy * ($trigger_count + 1);
                $this_ability->set_energy($new_energy_cost);
            }
        }

        // Return true on success
        return true;

        }
    );
?>