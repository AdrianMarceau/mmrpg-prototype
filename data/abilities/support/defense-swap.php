<?
// DEFENSE SWAP
$ability = array(
    'ability_name' => 'Defense Swap',
    'ability_token' => 'defense-swap',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Defense',
    'ability_description' => 'The user triggers an exploit in the prototype to swap their own defense stat changes with the target! However, the weapon energy cost for this ability increases after each use.',
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

        // Target this robot's self to initiate ability
        $target_name_text = $has_target_self ? 'itself' : $target_robot->print_name();
        $this_ability->target_options_update(array('frame' => 'summon', 'success' => array(0, 0, 10, -10, $this_robot->print_name().' triggered a '.$this_ability->print_name().' with '.$target_name_text.'!')));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Remove this ability from the target
        unset($target_robot->robot_attachments[$this_attachment_token]);
        $target_robot->update_session();

        // Collect this robot's stat mods and the target's so we can swap them
        $stat_token = 'defense';
        $this_stat_mods = $this_robot->counters[$stat_token.'_mods'];
        $target_stat_mods = $target_robot->counters[$stat_token.'_mods'];

        // If this robot happens to be targeting itself or the stats are otherwise the same, do nothing and return now
        if ($has_target_self || $this_stat_mods === $target_stat_mods || $target_robot->robot_status != 'active'){

            // Update the ability's target options and trigger
            $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(0, 0, 0, 10, '&hellip;but nothing happened.')));
            $target_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));
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

        // Swap the two robot's stat values now and then save the changes
        $this_robot->counters[$stat_token.'_mods'] = $target_stat_mods;
        $target_robot->counters[$stat_token.'_mods'] = $this_stat_mods;
        $this_robot->update_session();
        $target_robot->update_session();

        // Check to see if the target's stats got better or worse
        $effect_text = 'changed';
        if ($target_robot->counters[$stat_token.'_mods'] === 0){
            $diff = 0;
            $effect = 'reset';
            $effect_text = 'returned to normal';
            $effect_frame = $this_stat_mods > $target_stat_mods ? 'taunt' : 'defend';
        } elseif ($this_stat_mods > $target_stat_mods){
            $diff = $this_stat_mods - $target_stat_mods;
            $effect = 'rose';
            if ($diff >= 3){ $effect_text = 'rose drastically'; }
            elseif ($diff >= 2){ $effect_text = 'sharply rose'; }
            else { $effect_text = 'rose'; }
            $effect_frame = 'taunt';
        } elseif ($this_stat_mods < $target_stat_mods){
            $diff = $target_stat_mods - $this_stat_mods;
            $effect = 'fell';
            if ($diff >= 3){ $effect_text = 'severely fell'; }
            elseif ($diff >= 2){ $effect_text = 'harshly fell'; }
            else { $effect_text = 'fell'; }
            $effect_frame = 'defend';
        }

        // Generate an event showing the stat swap was successful
        $this_ability->target_options_update(array('frame' => $effect_frame, 'success' => array(9, 0, 10, -10, $target_name_text.'&#39;s '.$stat_token.' stat '.$effect_text.'!')));
        $target_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

        // Return true on success
        return true;

    },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If used by support robot OR the has a Target Module, allow opponent targetting
        $temp_support_robots = array('roll', 'disco', 'rhythm');
        if ($this_robot->robot_class == 'mecha'
            || in_array($this_robot->robot_token, $temp_support_robots)
            || $this_robot->has_item('target-module')){ $this_ability->set_target('select_this_ally'); }
        else { $this_ability->set_target('auto'); }

        // Check to see if this ability has been used already, and if so increase the cost
        if (!empty($this_robot->history['triggered_abilities'])){
            $new_energy_cost = $this_ability->ability_base_energy;
            foreach ($this_robot->history['triggered_abilities'] AS $ta_token){ if ($ta_token == $this_ability->ability_token){ $new_energy_cost += $this_ability->ability_base_energy; } }
            $this_ability->set_energy($new_energy_cost);
        }

        // Return true on success
        return true;

        }
    );
?>