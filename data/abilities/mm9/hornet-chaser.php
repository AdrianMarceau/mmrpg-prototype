<?
// HORNET CHASER
$ability = array(
    'ability_name' => 'Hornet Chaser',
    'ability_token' => 'hornet-chaser',
    'ability_game' => 'MM09',
    //'ability_group' => 'MM09/Weapons/070',
    'ability_group' => 'MM09/Weapons/065T2',
    'ability_description' => 'The user releases a robotic hornet drone that races across field to sting the target and deal damage!  If the target is disabled by this ability and was holding an item, the drone may steal and return that item to the user instead.',
    'ability_type' => 'nature',
    'ability_type2' => 'missile',
    'ability_energy' => 8,
    'ability_damage' => 22,
    'ability_accuracy' => 98,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect session token for later
        $session_token = rpg_game::session_token();

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(3, 120, 0, 10, $this_robot->print_name().' releases a '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Check to see if the target is holding an item
        $target_has_item = !empty($target_robot->robot_item) ? true : false;
        $removed_target_item = false;
        if ($target_has_item
            && !$target_robot->has_immunity($this_ability->ability_type)
            && !$target_robot->has_immunity($this_ability->ability_type2)){

            // Collect the item token
            $old_item_token = $target_robot->robot_item;

            // Define this ability's attachment token
            $temp_rotate_amount = 25;
            $item_attachment_token = 'item_'.$old_item_token;
            $item_attachment_info = array(
                'class' => 'item',
                'sticky' => true,
                'attachment_token' => $item_attachment_token,
                'item_token' => $old_item_token,
                'item_frame' => 0,
                'item_frame_animate' => array(0),
                'item_frame_offset' => array('x' => 0, 'y' => 60, 'z' => 20),
                'item_frame_styles' => 'opacity: 0.75; transform: rotate('.$temp_rotate_amount.'deg); -webkit-transform: rotate('.$temp_rotate_amount.'deg); -moz-transform: rotate('.$temp_rotate_amount.'deg); '
                );

             // Remove the item from the target robot and update w/ attachment info
            $old_item = rpg_game::get_item($this_battle, $target_player, $target_robot, array('item_token' => $old_item_token));
            $old_item->update_session();
            $target_robot->robot_attachments[$item_attachment_token] = $item_attachment_info;
            $target_robot->robot_item = '';
            $target_robot->update_session();
            $removed_target_item = true;

        }

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(4, -55, 0, 10, 'The '.$this_ability->print_name().' hit the target!'),
            'failure' => array(4, -75, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(10, 0, 0),
            'success' => array(4, -35, 0, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(4, -75, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        // Remove the visual icon attachment from the target
        if ($removed_target_item){

            // Remove the item attachment from view
            unset($target_robot->robot_attachments[$item_attachment_token]);
            $target_robot->update_session();

            // If the target robot was disabled by the attack, steal the item
            if ($target_robot->robot_status == 'disabled'
                || $target_robot->robot_energy <= 0){

                // If the target robot was the player, we gotta update the session
                if ($target_player->player_side == 'left'
                    && empty($this_battle->flags['player_battle'])
                    && empty($this_battle->flags['challenge_battle'])){
                    $ptoken = $target_player->player_token;
                    $rtoken = $target_robot->robot_token;
                    if (!empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                        $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = '';
                    }
                }

                // Make sure the target looks disabled right now
                $target_robot->robot_frame = 'disabled';
                $target_robot->update_session();

                // If the user does NOT have a held item already
                if (empty($this_robot->robot_item)){

                    // Define this ability's attachment token
                    $ability_attachment_token = 'ability_'.$this_ability->ability_token;
                    $ability_attachment_info = array(
                        'class' => 'ability',
                        'attachment_token' => $ability_attachment_token,
                        'ability_token' => $this_ability->ability_token,
                        'ability_frame' => 3,
                        'ability_frame_animate' => array(3),
                        'ability_frame_offset' => array('x' => 160, 'y' => 30, 'z' => 21),
                        'ability_frame_styles' => 'transform: scaleX(-1); -moz-transform: scaleX(-1); -webkit-transform: scaleX(-1); '
                        );

                    // Attach a sprite of this ability to the user returning from its trip
                    $this_robot->robot_attachments[$ability_attachment_token] = $ability_attachment_info;
                    $this_robot->update_session();

                     // Make a duplicate of the target's item for the user to show it being taken
                    $new_item_token = $old_item_token;
                    $new_item = rpg_game::get_item($this_battle, $this_player, $this_robot, array('item_token' => $new_item_token));
                    $new_item->update_session();

                    // Update the ability's target options and trigger
                    $temp_rotate_amount = 45;
                    $new_item->target_options_update(array(
                        'frame' => 'taunt',
                        'success' => array(0, 145, 10, 20,
                            'The '.$this_ability->print_name().' stole '.$target_robot->print_name().'\'s held item!'.
                            '<br /> The '.$old_item->print_name().' was returned to '.$this_robot->print_name().'!'
                            )
                        ));
                    $this_robot->trigger_target($this_robot, $new_item, array('prevent_default_text' => true));

                    // Give the cloned item to the user of the ability
                    $this_robot->robot_item = $new_item_token;
                    $this_robot->update_session();

                    // If the target robot was the player, we gotta update the session
                    if ($this_player->player_side == 'left'
                        && empty($this_battle->flags['player_battle'])
                        && empty($this_battle->flags['challenge_battle'])){
                        $ptoken = $this_player->player_token;
                        $rtoken = $this_robot->robot_token;
                        if (!empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken])){
                            $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = $new_item_token;
                        }
                    }

                    // IF CORE OR UPGRADE WE NEED TO ADJUST STATS!
                    // If the new item has effects that need manual application, do so now
                    if ($new_item_token == 'energy-upgrade'){
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
                    } elseif ($new_item_token == 'weapon-upgrade'){
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
                    } elseif (strstr($new_item_token, '-core')){
                        //$this_battle->events_create(false, false, 'debug', 'The stolen item was a robot core!');
                        $new_core_type = preg_replace('/-core$/', '', $old_item_token);
                        $existing_shields = !empty($this_robot->robot_attachments) ? substr_count(implode('|', array_keys($this_robot->robot_attachments)), 'ability_core-shield_') : 0;
                        $shield_info = rpg_ability::get_static_core_shield($new_core_type, 3, $existing_shields);
                        $shield_token = $shield_info['attachment_token'];
                        $shield_duration = $shield_info['attachment_duration'];
                        if (!isset($this_robot->robot_attachments[$shield_token])){ $this_robot->robot_attachments[$shield_token] = $shield_info; }
                        else { $this_robot->robot_attachments[$shield_token]['attachment_duration'] += $shield_duration; }
                        $this_robot->update_session();
                        if ($this_robot->robot_base_core == 'copy'
                            && $this_robot->robot_image == $this_robot->robot_token){
                            $this_robot->robot_image = $this_robot->robot_token.'_'.$new_core_type;
                            $this_robot->update_session();
                            if ($this_player->player_side == 'left'
                                && empty($this_battle->flags['player_battle'])
                                && empty($this_battle->flags['challenge_battle'])){
                                $ptoken = $this_player->player_token;
                                $rtoken = $this_robot->robot_token;
                                if (!empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken])){
                                    $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_image'] = $this_robot->robot_image;
                                }
                            }
                        }
                    }


                    // Adjust the position of the ability attachment and show it moving before removing
                    $ability_attachment_info['ability_frame_offset'] = array('x' => -90, 'y' => 30, 'z' => -21);
                    $this_robot->robot_attachments[$ability_attachment_token] = $ability_attachment_info;
                    $this_robot->update_session();
                    $this_battle->events_create(false, false, '', '');

                    // Remove the ability attachment from view and give the item to the user
                    unset($this_robot->robot_attachments[$ability_attachment_token]);
                    $this_robot->update_session();

                }
                // Otherwise, if already has an item, do nothing
                else {

                    // Do nothing further

                }

            }
            // Otherwise, put the item back and place and continue
            else {

                // Re-attach the item to the target robot
                $target_robot->robot_item = $old_item_token;
                $target_robot->update_session();

            }
        }

        // If the target was disabled, trigger approptiate action
        if ($target_robot->robot_status == 'disabled'
            || $target_robot->robot_energy <= 0){
            $target_robot->trigger_disabled($this_robot);
        }

        // Return true on success
        return true;

    }
    );
?>