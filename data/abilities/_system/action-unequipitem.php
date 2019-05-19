<?
// ACTION : UNEQUIP ITEM
$ability = array(
    'ability_name' => 'Unequip Item',
    'ability_token' => 'action-unequipitem',
    'ability_class' => 'system',
    'ability_description' => 'The user opts to skip their turn and instead returns their held item to the inventory.',
    'ability_energy' => 0,
    'ability_damage' => 0,
    'ability_speed' => 10,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect session token for later
        $session_token = rpg_game::session_token();

        // If this robot is holding an item (and we're allowed), return to inventory
        if ($this_player->player_side == 'left'
            && !empty($this_robot->robot_item)
            && empty($this_battle->flags['player_battle'])
            && empty($this_battle->flags['challenge_battle'])
            && (empty($_SESSION[$session_token]['values']['battle_items'][$this_robot->robot_item])
                || $_SESSION[$session_token]['values']['battle_items'][$this_robot->robot_item] < MMRPG_SETTINGS_ITEMS_MAXQUANTITY)){

            // Create inventory slot if not exists yet then add one of these items to it
            if (empty($_SESSION[$session_token]['values']['battle_items'][$this_robot->robot_item])){ $_SESSION[$session_token]['values']['battle_items'][$this_robot->robot_item] = 0; }
            $temp_item_quantity_old = $_SESSION[$session_token]['values']['battle_items'][$this_robot->robot_item];
            $_SESSION[$session_token]['values']['battle_items'][$this_robot->robot_item] += 1;
            $temp_item_quantity_new = $_SESSION[$session_token]['values']['battle_items'][$this_robot->robot_item];

            // Remove the item from this robot in the session
            $ptoken = $this_player->player_token;
            $rtoken = $this_robot->robot_token;
            if (!empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = '';
            }

            // Remove the item from the robot and create an object out of it
            $old_item_token = $this_robot->robot_item;
            $old_item = rpg_game::get_item($this_battle, $this_player, $this_robot, array('item_token' => $old_item_token));
            $this_robot->robot_item = '';
            $this_robot->update_session();

            // If the old item happened to be a life or weapon energy upgrade, recalculate stats
            if ($old_item_token == 'energy-upgrade'){
                //$this_battle->events_create(false, false, 'debug', 'The knocked-off item was a life energy upgrade!');
                $this_current_energy = $this_robot->robot_energy;
                $this_current_base_energy = $this_robot->robot_base_energy;
                $this_current_damage = $this_current_base_energy - $this_current_energy;
                $this_new_base_energy = $this_current_base_energy / 2;
                $this_new_energy = $this_new_base_energy - $this_current_damage;
                if ($this_new_energy > $this_new_base_energy){ $this_new_energy = $this_new_base_energy; }
                /*
                $this_battle->events_create(false, false, 'debug',
                    '$this_current_energy = '.$this_current_energy.
                    ' / '.'$this_current_base_energy = '.$this_current_base_energy.
                    ' <br /> '.'$this_current_damage = '.$this_current_damage.
                    ' <br /> '.'$this_new_energy = '.$this_new_energy.
                    ' / '.'$this_new_base_energy = '.$this_new_base_energy.
                    '');
                */
                $this_robot->robot_energy = $this_new_energy;
                $this_robot->robot_base_energy = $this_new_base_energy;
                $this_robot->update_session();
            } elseif ($old_item_token == 'weapon-upgrade'){
                //$this_battle->events_create(false, false, 'debug', 'The knocked-off item was a weapon energy upgrade!');
                $this_current_energy = $this_robot->robot_weapons;
                $this_current_base_energy = $this_robot->robot_base_weapons;
                $this_current_damage = $this_current_base_energy - $this_current_energy;
                $this_new_base_energy = $this_current_base_energy / 2;
                $this_new_energy = $this_new_base_energy - $this_current_damage;
                if ($this_new_energy > $this_new_base_energy){ $this_new_energy = $this_new_base_energy; }
                /*
                $this_battle->events_create(false, false, 'debug',
                    '$this_current_energy = '.$this_current_energy.
                    ' / '.'$this_current_base_energy = '.$this_current_base_energy.
                    ' <br /> '.'$this_current_damage = '.$this_current_damage.
                    ' <br /> '.'$this_new_energy = '.$this_new_energy.
                    ' / '.'$this_new_base_energy = '.$this_new_base_energy.
                    '');
                    */
                $this_robot->robot_weapons = $this_new_energy;
                $this_robot->robot_base_weapons = $this_new_base_energy;
                $this_robot->update_session();
            } elseif (strstr($old_item_token, '-core')){
                //$this_battle->events_create(false, false, 'debug', 'The knocked-off item was a robot core!');
                $lost_core_type = preg_replace('/-core$/', '', $old_item_token);
                $possible_attachment_token = 'ability_core-shield_'.$lost_core_type;
                if (!empty($this_robot->robot_attachments[$possible_attachment_token])){
                    $this_robot->robot_attachments[$possible_attachment_token]['attachment_duration'] = 0;
                    unset($this_robot->robot_attachments[$possible_attachment_token]);
                    $this_robot->update_session();
                }
                if ($this_robot->robot_base_core == 'copy'
                    && $this_robot->robot_image == $this_robot->robot_token.'_'.$lost_core_type){
                    $this_robot->robot_image = $this_robot->robot_token;
                    $this_robot->update_session();
                    if ($this_player->player_side == 'left'
                        && empty($this_battle->flags['player_battle'])
                        && empty($this_battle->flags['challenge_battle'])){
                        $ptoken = $this_player->player_token;
                        $rtoken = $this_robot->robot_token;
                        if (!empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_image'])){
                            unset($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_image']);
                        }
                    }
                }
            }

            // Update the ability's target options and trigger
            $temp_rotate_amount = 45;
            //$old_item->set_name('Unequip Item');
            $old_item->set_frame_styles('opacity: 0.5; transform: rotate('.$temp_rotate_amount.'deg); -webkit-transform: rotate('.$temp_rotate_amount.'deg); -moz-transform: rotate('.$temp_rotate_amount.'deg); ');
            $old_item->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -10, 40, 20,
                    $this_robot->print_name().' unequipped '.$this_robot->get_pronoun('possessive2').' held item!'.
                    '<br /> The '.$old_item->print_name().' was added to the inventory! '.
                    '<span class="item_stat item_type item_type_none">'.$temp_item_quantity_old.' <sup style="bottom: 2px;">&raquo;</sup> '.$temp_item_quantity_new.'</span>'
                    )
                ));
            $this_robot->trigger_target($this_robot, $old_item, array('prevent_default_text' => true));
            //$old_item->reset_name();

        }
        // Otherwise this action will always fail (and should have been disabled elsewhere anyway)
        else {

            // Print the failure message for the ability
            $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(9, 0, 0, -999, 'But nothing happened...')));
            $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

        }

        // Return true on success
        return true;

        }
    );
?>