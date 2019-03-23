<?
// METAL PRESS
$ability = array(
    'ability_name' => 'Metal Press',
    'ability_token' => 'metal-press',
    'ability_game' => 'MM02',
    //'ability_group' => 'MM02/Weapons/009',
    'ability_group' => 'MM02/Weapons/009T2',
    'ability_description' => 'The user summons a giant spike-covered press that drops down on the target at the end of the turn, dealing damage and lowering all stats by one stage!',
    'ability_type' => 'cutter',
    'ability_type2' => 'impact',
    'ability_energy' => 8,
    'ability_speed' => -2,
    'ability_damage' => 22,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If this ability has not been summoned yet, do the action and then queue a conclusion move
        $summoned_flag_token = $this_ability->ability_token.'_summoned';
        if (empty($this_robot->flags[$summoned_flag_token])){

            // Set the summoned flag on this robot and save
            $this_robot->flags[$summoned_flag_token] = true;
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, 0, 150, 30, $this_robot->print_name().' summons the '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Queue another use of this ability at the end of turn
            $this_battle->actions_append(
                $this_player,
                $this_robot,
                $target_player,
                $target_robot,
                'ability',
                $this_ability->ability_id.'_'.$this_ability->ability_token
                );

        }
        // The ability has already been summoned, so we can finish executing it now and deal damage
        else {

            // Remove the summoned flag from this robot and save
            unset($this_robot->flags[$summoned_flag_token]);
            $this_robot->update_session();

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(5, 25, 0),
                'success' => array(1, 0, -50, 30, 'The '.$this_ability->print_name().' crushed the target with spikes!'),
                'failure' => array(1, 0, -50, -10, 'The '.$this_ability->print_name().' somehow missed the target&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 15, 0),
                'success' => array(1, 0, -30, 30, 'The '.$this_ability->print_name().' crushed the target but...'),
                'failure' => array(1, 0, -30, -10, 'The '.$this_ability->print_name().' somehow missed the target&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $this_robot->robot_frame = 'throw';
            $this_robot->update_session();
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
            $this_robot->robot_frame = 'base';
            $this_robot->update_session();

            // Only lower the target's stats of the ability was successful
            if ($target_robot->robot_status != 'disabled'
                && $this_ability->ability_results['this_result'] != 'failure'){

                // Call the global stat break functions with customized options
                rpg_ability::ability_function_stat_break($target_robot, 'attack', 1);
                rpg_ability::ability_function_stat_break($target_robot, 'defense', 1);
                rpg_ability::ability_function_stat_break($target_robot, 'speed', 1);

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

        // If the ability has already been summoned earlier this turn, decrease WE to zero
        $summoned_flag_token = $this_ability->ability_token.'_summoned';
        if (!empty($this_robot->flags[$summoned_flag_token])){ $this_ability->set_energy(0); }
        else { $this_ability->reset_energy(); }

        // Return true on success
        return true;

        }
    );
?>