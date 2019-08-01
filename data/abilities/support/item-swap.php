<?
// ITEM SWAP
$ability = array(
    'ability_name' => 'Item Swap',
    'ability_token' => 'item-swap',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Item',
    'ability_description' => 'The user triggers an exploit in the prototype\'s code to instantly swap their held item with the target! When used by a support robot, this ability can target allies instead! Use sparingly as this skill becomes more costly with each use...',
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

        // Define the defaults for an item attachment arrays
        $base_item_attachment_info = array(
            'class' => 'item',
            'sticky' => true,
            'attachment_token' => 'item',
            'item_token' => 'item',
            'item_frame' => 0,
            'item_frame_animate' => array(0),
            'item_frame_offset' => array('x' => 0, 'y' => 60, 'z' => 20),
            );

        // If the user has an item, show it above their head
        $this_item_attachment_token = false;
        if (!empty($this_robot->robot_item)){
            $this_item_attachment_token = 'item_'.$this_robot->robot_item;
            $this_item_attachment_info = $base_item_attachment_info;
            $this_item_attachment_info['attachment_token'] = $this_item_attachment_token;
            $this_item_attachment_info['item_token'] = $this_robot->robot_item;
            $this_robot->set_attachment($this_item_attachment_token, $this_item_attachment_info );
        }

        // If the target has an item, show it above their head
        $target_item_attachment_token = false;
        if (!empty($target_robot->robot_item)){
            $target_item_attachment_token = 'item_'.$target_robot->robot_item;
            $target_item_attachment_info = $base_item_attachment_info;
            $target_item_attachment_info['attachment_token'] = $target_item_attachment_token;
            $target_item_attachment_info['item_token'] = $target_robot->robot_item;
            $target_robot->set_attachment($target_item_attachment_token, $target_item_attachment_info );
        }

        // Check if this robot is targetting itself
        $has_target_self = $this_robot->robot_id == $target_robot->robot_id ? true : false;

        // Target this robot's self to initiate ability
        $target_name_text = $has_target_self ? 'itself' : $target_robot->print_name();
        $this_ability->target_options_update(array('frame' => 'summon', 'success' => array(0, 0, 10, -10, $this_robot->print_name().' triggered an '.$this_ability->print_name().' with '.$target_name_text.'!')));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Remove this ability from the target
        unset($target_robot->robot_attachments[$this_attachment_token]);
        $target_robot->update_session();

        // Remove item attachment from user if applicable
        if (!empty($this_item_attachment_token)){ $this_robot->unset_attachment($this_item_attachment_token); }

        // Remove item attachment from target if applicable
        if (!empty($target_item_attachment_token)){ $target_robot->unset_attachment($target_item_attachment_token); }

        // Check to ensure at least one of the targets has an item
        $this_item_token = $this_robot->robot_item;
        $target_item_token = $target_robot->robot_item;
        $this_item_index_info = !empty($this_item_token) ? rpg_item::get_index_info($this_item_token) : false;
        $target_item_index_info = !empty($target_item_token) ? rpg_item::get_index_info($target_item_token) : false;

        // Collect this robot's stat mods and the target's so we can swap them
        //$stat_token = 'attack';
        //$this_stat_mods = $this_robot->counters[$stat_token.'_mods'];
        //$target_stat_mods = $target_robot->counters[$stat_token.'_mods'];

        // If this robot happens to be targeting itself or the item are the same, do nothing and return now
        if ($has_target_self || $this_item_token === $target_item_token || $target_robot->robot_status != 'active'){

            // Update the ability's target options and trigger
            $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(0, 0, 0, 10, '&hellip;but nothing happened.')));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));
            return;

        }

        // Swap the two robot's held items now and then save the changes
        $this_robot->set_item($target_item_token);
        $target_robot->set_item($this_item_token);

        // Collect the user's new item token if set, then update
        $this_new_item_token = $this_robot->robot_item;
        //$this_battle->events_create(false, false, 'debug', 'The following item was stolen: '.$this_new_item_token);
        if (!empty($this_new_item_token)){

            // If the this robot was the player, we gotta update the session
            if ($this_player->player_side == 'left'
                && empty($this_battle->flags['player_battle'])
                && empty($this_battle->flags['challenge_battle'])){
                $ptoken = $this_player->player_token;
                $rtoken = $this_robot->robot_token;
                if (!empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken])){
                    $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = $this_new_item_token;
                }
            }

            // IF CORE OR UPGRADE WE NEED TO ADJUST STATS!
            // If the new item has effects that need manual application, do so now
            if ($this_new_item_token == 'energy-upgrade'){
                //$this_battle->events_create(false, false, 'debug', 'The stolen item was a life energy upgrade!');
                $this_current_energy = $this_robot->robot_energy;
                $this_current_base_energy = $this_robot->robot_base_energy;
                $this_new_energy = ceil($this_current_energy * 2);
                $this_new_base_energy = ceil($this_current_base_energy * 2);
                /*
                //$this_battle->events_create(false, false, 'debug',
                    '$this_current_energy = '.$this_current_energy.
                    ' / '.'$this_current_base_energy = '.$this_current_base_energy.
                    ' <br /> '.'$this_new_energy = '.$this_new_energy.
                    ' / '.'$this_new_base_energy = '.$this_new_base_energy.
                    '');
                */
                $this_robot->robot_energy = $this_new_energy;
                $this_robot->robot_base_energy = $this_new_base_energy;
                $this_robot->update_session();
            } elseif ($this_new_item_token == 'weapon-upgrade'){
                //$this_battle->events_create(false, false, 'debug', 'The stolen item was a weapon energy upgrade!');
                $this_current_energy = $this_robot->robot_weapons;
                $this_current_base_energy = $this_robot->robot_base_weapons;
                $this_new_energy = ceil($this_current_energy * 2);
                $this_new_base_energy = ceil($this_current_base_energy * 2);
                /*
                //$this_battle->events_create(false, false, 'debug',
                    '$this_current_energy = '.$this_current_energy.
                    ' / '.'$this_current_base_energy = '.$this_current_base_energy.
                    ' <br /> '.'$this_new_energy = '.$this_new_energy.
                    ' / '.'$this_new_base_energy = '.$this_new_base_energy.
                    '');
                    */
                $this_robot->robot_weapons = $this_new_energy;
                $this_robot->robot_base_weapons = $this_new_base_energy;
                $this_robot->update_session();
            } elseif (strstr($this_new_item_token, '-core')){
                //$this_battle->events_create(false, false, 'debug', 'The stolen item was a robot core!');
                $new_core_type = preg_replace('/-core$/', '', $this_new_item_token);
                $existing_shields = !empty($this_robot->robot_attachments) ? substr_count(implode('|', array_keys($this_robot->robot_attachments)), 'ability_core-shield_') : 0;
                $shield_info = rpg_ability::get_static_core_shield($new_core_type, 3, $existing_shields);
                $shield_token = $shield_info['attachment_token'];
                $shield_duration = $shield_info['attachment_duration'];
                if (!isset($this_robot->robot_attachments[$shield_token])){ $this_robot->robot_attachments[$shield_token] = $shield_info; }
                else { $this_robot->robot_attachments[$shield_token]['attachment_duration'] += $shield_duration; }
                $this_robot->update_session();
            }
        }

        // Collect the target's new item token if set, then update
        $target_new_item_token = $target_robot->robot_item;
        //$this_battle->events_create(false, false, 'debug', 'The following item was stolen: '.$target_new_item_token);
        if (!empty($target_new_item_token)){

            // If the this robot was the player, we gotta update the session
            if ($target_player->player_side == 'left'
                && empty($this_battle->flags['player_battle'])
                && empty($this_battle->flags['challenge_battle'])){
                $ptoken = $target_player->player_token;
                $rtoken = $target_robot->robot_token;
                if (!empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken])){
                    $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = $target_new_item_token;
                }
            }

            // IF CORE OR UPGRADE WE NEED TO ADJUST STATS!
            // If the new item has effects that need manual application, do so now
            if ($target_new_item_token == 'energy-upgrade'){
                //$this_battle->events_create(false, false, 'debug', 'The stolen item was a life energy upgrade!');
                $target_current_energy = $target_robot->robot_energy;
                $target_current_base_energy = $target_robot->robot_base_energy;
                $target_new_energy = ceil($target_current_energy * 2);
                $target_new_base_energy = ceil($target_current_base_energy * 2);
                /*
                //$this_battle->events_create(false, false, 'debug',
                    '$target_current_energy = '.$target_current_energy.
                    ' / '.'$target_current_base_energy = '.$target_current_base_energy.
                    ' <br /> '.'$target_new_energy = '.$target_new_energy.
                    ' / '.'$target_new_base_energy = '.$target_new_base_energy.
                    '');
                */
                $target_robot->robot_energy = $target_new_energy;
                $target_robot->robot_base_energy = $target_new_base_energy;
                $target_robot->update_session();
            } elseif ($target_new_item_token == 'weapon-upgrade'){
                //$this_battle->events_create(false, false, 'debug', 'The stolen item was a weapon energy upgrade!');
                $target_current_energy = $target_robot->robot_weapons;
                $target_current_base_energy = $target_robot->robot_base_weapons;
                $target_new_energy = ceil($target_current_energy * 2);
                $target_new_base_energy = ceil($target_current_base_energy * 2);
                /*
                //$this_battle->events_create(false, false, 'debug',
                    '$target_current_energy = '.$target_current_energy.
                    ' / '.'$target_current_base_energy = '.$target_current_base_energy.
                    ' <br /> '.'$target_new_energy = '.$target_new_energy.
                    ' / '.'$target_new_base_energy = '.$target_new_base_energy.
                    '');
                    */
                $target_robot->robot_weapons = $target_new_energy;
                $target_robot->robot_base_weapons = $target_new_base_energy;
                $target_robot->update_session();
            } elseif (strstr($target_new_item_token, '-core')){
                //$this_battle->events_create(false, false, 'debug', 'The stolen item was a robot core!');
                $new_core_type = preg_replace('/-core$/', '', $target_new_item_token);
                $existing_shields = !empty($target_robot->robot_attachments) ? substr_count(implode('|', array_keys($target_robot->robot_attachments)), 'ability_core-shield_') : 0;
                $shield_info = rpg_ability::get_static_core_shield($new_core_type, 3, $existing_shields);
                $shield_token = $shield_info['attachment_token'];
                $shield_duration = $shield_info['attachment_duration'];
                if (!isset($target_robot->robot_attachments[$shield_token])){ $target_robot->robot_attachments[$shield_token] = $shield_info; }
                else { $target_robot->robot_attachments[$shield_token]['attachment_duration'] += $shield_duration; }
                $target_robot->update_session();
            }
        }

        // Check to see if the target's stats got better or worse
        $this_item_object = !empty($this_robot->robot_item) ? rpg_game::get_item($this_battle, $this_player, $this_robot, array('item_token' => $this_robot->robot_item)) : false;
        $this_item_pronoun = !empty($this_robot->robot_item) && preg_match('/^(a|e|i|o|u)/i', $this_robot->robot_item) ? 'an' : 'a';
        $target_item_object = !empty($target_robot->robot_item) ? rpg_game::get_item($this_battle, $target_player, $target_robot, array('item_token' => $target_robot->robot_item)) : false;
        $target_item_pronoun = !empty($target_robot->robot_item) && preg_match('/^(a|e|i|o|u)/i', $target_robot->robot_item) ? 'an' : 'a';
        if ($target_robot->robot_item === ''){
            $effect_text = $target_name_text.'\'s item was stolen! <br /> ';
            $effect_text .= $this_robot->print_name().' got '.$this_item_pronoun.' '.$this_item_object->print_name().'! ';
            $effect_frame = 'taunt';
        } elseif ($this_robot->robot_item === ''){
            $effect_text = $this_robot->print_name().' gave away '.$this_robot->get_pronoun('possessive2').' item! <br /> ';
            $effect_text .= $target_robot->print_name().' got '.$target_item_pronoun.' '.$target_item_object->print_name().'!';
            $effect_frame = 'damage';
        } else {
            $effect_text = $this_robot->print_name().' and '.$target_name_text.'\'s items were swapped! <br /> ';
            $effect_text .= $this_robot->print_name().' got '.$this_item_pronoun.' '.$this_item_object->print_name().'! ';
            $effect_text .= $target_robot->print_name().' got '.$target_item_pronoun.' '.$target_item_object->print_name().'!';
            $effect_frame = 'defend';
        }

        // If the user has an item, show it above their head
        $this_item_attachment_token = false;
        if (!empty($this_robot->robot_item)){
            $this_item_attachment_token = 'item_'.$this_robot->robot_item;
            $this_item_attachment_info = $base_item_attachment_info;
            $this_item_attachment_info['attachment_token'] = $this_item_attachment_token;
            $this_item_attachment_info['item_token'] = $this_robot->robot_item;
            $this_robot->set_attachment($this_item_attachment_token, $this_item_attachment_info );
        }

        // If the target has an item, show it above their head
        $target_item_attachment_token = false;
        if (!empty($target_robot->robot_item)){
            $target_item_attachment_token = 'item_'.$target_robot->robot_item;
            $target_item_attachment_info = $base_item_attachment_info;
            $target_item_attachment_info['attachment_token'] = $target_item_attachment_token;
            $target_item_attachment_info['item_token'] = $target_robot->robot_item;
            $target_robot->set_attachment($target_item_attachment_token, $target_item_attachment_info );
        }

        // Generate an event showing the stat swap was successful
        $this_ability->target_options_update(array('frame' => $effect_frame, 'success' => array(9, 0, 10, -10, $effect_text)));
        $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

        // Remove item attachment from user if applicable
        if (!empty($this_item_attachment_token)){ $this_robot->unset_attachment($this_item_attachment_token); }

        // Remove item attachment from target if applicable
        if (!empty($target_item_attachment_token)){ $target_robot->unset_attachment($target_item_attachment_token); }

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
            foreach ($this_robot->history['triggered_abilities'] AS $ta_token){ if ($ta_token == $this_ability->ability_token){ $new_energy_cost += ceil($this_ability->ability_base_energy / 2); } }
            $this_ability->set_energy($new_energy_cost);
        }

        // Return true on success
        return true;

        }
    );
?>