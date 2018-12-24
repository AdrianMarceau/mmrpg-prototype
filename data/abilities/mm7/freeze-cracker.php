<?
// FREEZE CRACKER
$ability = array(
    'ability_name' => 'Freeze Cracker',
    'ability_token' => 'freeze-cracker',
    'ability_game' => 'MM07',
    'ability_group' => 'MM07/Weapons/049',
    'ability_description' => 'The user fires a snowflake-like projectile at the target that explodes on contact to deal damage.  If this ability is used on a benched robot, the explosion of ice shards can hit adjacent foes as well.',
    'ability_type' => 'freeze',
    'ability_type2' => 'explode',
    'ability_energy' => 8,
    'ability_damage' => 24,
    'ability_accuracy' => 96,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Check to see if we're hitting other robots
        $will_hit_multiple = $target_robot->robot_position === 'bench' ? true : false;

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 95, 0, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(4, -10, 0, 10, 'The '.$this_ability->print_name().' hit the target!'),
            'failure' => array(1, -75, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(10, 0, 0),
            'success' => array(4, -10, 0, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(1, -75, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, ($will_hit_multiple ? false : true));

        // If the target was a benched robot, we should hit the one before and after
        if ($will_hit_multiple){

            // Collect the target's key and the before/after keys as well
            $target_key = $target_robot->robot_key;
            $side_key_before = $target_key - 1;
            $side_key_after = $target_key + 1;
            if ($side_key_before < 0){ $side_key_before = false; }
            if ($side_key_after > (MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX - 1)){ $side_key_after = false; }

            // Loop through target robots and damage ones that match above keys
            $allowed_keys = array($side_key_before, $side_key_after);
            $backup_target_robots_active = $target_player->values['robots_active'];
            foreach ($backup_target_robots_active AS $key => $info){
                if ($info['robot_id'] == $target_robot->robot_id){ continue; }
                $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $info);
                if (!in_array($temp_target_robot->robot_key, $allowed_keys)){ continue; }
                elseif ($temp_target_robot->robot_position === 'active'){ continue; }
                $this_ability->ability_results_reset();
                $temp_positive_word = rpg_battle::random_positive_word();
                $temp_negative_word = rpg_battle::random_negative_word();
                $this_ability->damage_options_update(array(
                    'kind' => 'energy',
                    'modifiers' => true,
                    'kickback' => array(5, 0, 0),
                    'success' => array(2, -5, 0, 99, ($target_player->player_side === 'right' ? $temp_positive_word : $temp_negative_word).' Another robot was hit by the ice shards!'),
                    'failure' => array(2, -5, 0, 99, 'The attack had no effect on '.$temp_target_robot->print_name().'&hellip;')
                    ));
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'modifiers' => true,
                    'frame' => 'taunt',
                    'kickback' => array(5, 0, 0),
                    'success' => array(2, -5, 0, 9, ($target_player->player_side === 'right' ? $temp_negative_word : $temp_positive_word).' The ice shards was absorbed by the target!'),
                    'failure' => array(2, -5, 0, 9, 'The attack had no effect on '.$temp_target_robot->print_name().'&hellip;')
                    ));
                $energy_damage_amount = ceil($this_ability->ability_damage / 2);
                $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
            }

            // Trigger the disabled event on the targets now if necessary
            if ($target_robot->robot_status == 'disabled'){ $target_robot->trigger_disabled($this_robot); }
            else { $target_robot->robot_frame = 'base'; }
            $target_robot->update_session();
            foreach ($backup_target_robots_active AS $key => $info){
                if ($info['robot_id'] == $target_robot->robot_id){ continue; }
                $info2 = array('robot_id' => $info['robot_id'], 'robot_token' => $info['robot_token']);
                $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $info2);
                if ($temp_target_robot->robot_energy <= 0 || $temp_target_robot->robot_status == 'disabled'){ $temp_target_robot->trigger_disabled($this_robot); }
                else { $temp_target_robot->robot_frame = 'base'; }
                $temp_target_robot->update_session();
            }

        }

        // Return true on success
        return true;

    }
    );
?>