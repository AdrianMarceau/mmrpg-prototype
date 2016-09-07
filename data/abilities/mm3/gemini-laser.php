<?
// GEMINI LASER
$ability = array(
    'ability_name' => 'Gemini Laser',
    'ability_token' => 'gemini-laser',
    'ability_game' => 'MM03',
    'ability_group' => 'MM03/Weapons/019',
    'ability_description' => 'The user fires a powerful laser that bounces back and forth across the battle field, damaging all target robots until it runs out of power!',
    'ability_type' => 'crystal',
    'ability_type2' => 'laser',
    'ability_energy' => 4,
    'ability_damage' => 12,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 150, 0, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!'),
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $temp_offset = $target_player->counters['robots_active'] > 1 ? -250 : -150;
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(15, 5, 0),
            'success' => array(0, $temp_offset, 0, 10, 'The '.$this_ability->print_name().' burned through the target!'),
            'failure' => array(0, ($temp_offset - 50), 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'frame' => 'taunt',
            'kickback' => array(5, 0, 0),
            'success' => array(0, $temp_offset, 0, 10, 'The '.$this_ability->print_name().' energy was absorbed by the target!'),
            'failure' => array(0, ($temp_offset - 50), 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        // Randomly trigger a bench damage if the ability was successful
        $backup_robots_active = $target_player->values['robots_active'];
        $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
        if (true){ //$this_ability->ability_results['this_result'] != 'failure'

            // Loop through the target's benched robots, inflicting les and less damage to each
            $target_key = 0;
            foreach ($backup_robots_active AS $key => $info){
                if ($info['robot_id'] == $target_robot->robot_id){ continue; }
                if (!$this_battle->critical_chance($this_ability->ability_accuracy)){ continue; }
                $this_ability->ability_results_reset();
                $temp_target_robot = new rpg_robot($this_battle, $target_player, $info);
                // Update the ability options text
                $temp_frame = $target_key == 0 || $target_key % 2 == 0 ? 1 : 0;
                $temp_kickback = ($target_key == 0 || $target_key % 2 == 0 ? -1 : 1) * (10 + (5 * $target_key));
                $temp_offset = 100 - ($target_key * 10);
                $temp_offset = $temp_frame == 0 ? $temp_offset * -1 : ceil($temp_offset * 0.75);
                $this_ability->damage_options_update(array(
                    'kickback' => array($temp_kickback, 0, 0),
                    'success' => array($temp_frame, $temp_offset, 0, 10, 'The '.$this_ability->print_name().' burned through the target!'),
                    'failure' => array($temp_frame, ($temp_offset * 2), 0, 10, '')
                    ));
                $this_ability->recovery_options_update(array(
                    'kickback' => array($temp_kickback, 0, 0),
                    'success' => array($temp_frame, $temp_offset, 0, 10, 'The '.$this_ability->print_name().'&#39;s energy was absorbed by the target!'),
                    'failure' => array($temp_frame, $temp_offset * 2, 0, 10, '')
                    ));
                //$energy_damage_amount = ceil($this_ability->ability_damage / ($key + 2));
                $energy_damage_amount = ceil($this_ability->ability_damage / ($target_robot->robot_key + 2));
                $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
                $target_key++;
            }

        }

        // Trigger the disabled event on the targets now if necessary
        if ($target_robot->robot_status == 'disabled'){
            $target_robot->trigger_disabled($this_robot);
        }
        foreach ($backup_robots_active AS $key => $info){
            if ($info['robot_id'] == $target_robot->robot_id){ continue; }
            $temp_target_robot = new rpg_robot($this_battle, $target_player, $info);
            if ($temp_target_robot->robot_energy <= 0 || $temp_target_robot->robot_status == 'disabled'){
                $temp_target_robot->trigger_disabled($this_robot);
            }
        }

        // Return true on success
        return true;

    }
    );
?>