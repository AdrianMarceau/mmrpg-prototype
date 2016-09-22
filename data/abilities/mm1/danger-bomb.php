<?
// DANGER BOMB
$ability = array(
    'ability_name' => 'Danger Bomb',
    'ability_token' => 'danger-bomb',
    'ability_game' => 'MM01',
    'ability_group' => 'MM01/Weapons/006',
    'ability_description' => 'The user throws a dangerous and powerful bomb that explodes mid-air to inflict massive damage on the target! The user of this devasting attack receives {DAMAGE2}% recoil damage, so use with extreme caution.',
    'ability_type' => 'explode',
    'ability_energy' => 8,
    'ability_damage' => 50,
    'ability_damage2' => 20,
    'ability_damage2_percent' => true,
    'ability_accuracy' => 70,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's first attachment token
        $this_attachment_token_one = 'ability_'.$this_ability->ability_token.'_one';
        $this_attachment_info_one = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 1,
            'ability_frame_animate' => array(1),
            'ability_frame_offset' => array('x' => 120, 'y' => 20, 'z' => 10)
            );

        // Define this ability's second attachment token
        $this_attachment_token_two = 'ability_'.$this_ability->ability_token.'_two';
        $this_attachment_info_two = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 2,
            'ability_frame_animate' => array(2),
            'ability_frame_offset' => array('x' => 270, 'y' => 5, 'z' => 10)
            );

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'kickback' => array(0, 0, 0),
            'success' => array(0, 160, 15, 10, $this_robot->print_name().' throws the '.$this_ability->print_name().'!'),
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $target_robot->robot_attachments[$this_attachment_token_one] = $this_attachment_info_one;
        $target_robot->robot_attachments[$this_attachment_token_two] = $this_attachment_info_two;
        $target_robot->update_session();
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'frame' => 'damage',
            'kickback' => array(15, 0, 0),
            'success' => array(2, -30, 0, 10, $target_robot->print_name().' was damaged by the blast!'),
            'failure' => array(2, -65, 0, -10, $target_robot->print_name().' avoided the blast&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(2, -30, 0, 10, $target_robot->print_name().' was invigorated by the blast!'),
            'failure' => array(2, -65, 0, -10, $target_robot->print_name().' avoided the blast&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
        unset($target_robot->robot_attachments[$this_attachment_token_one]);
        unset($target_robot->robot_attachments[$this_attachment_token_two]);
        $target_robot->update_session();

        // If the ability was successful against the target, this robot gets exactly half damage
        if ($this_ability->ability_results['this_result'] != 'failure'
            && $this_ability->ability_results['this_amount'] > 0){

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'frame' => 'damage',
                'type' => $this_ability->ability_type,
                'percent' => true,
                'modifiers' => false,
                'kickback' => array(15, 0, 0),
                'success' => array(3, -30, 0, 10, $this_robot->print_name().' was damaged by the blast!'),
                'failure' => array(3, -65, 0, -10, $this_robot->print_name().' avoided the blast&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'type' => '$this_ability->ability_type',
                'percent' => true,
                'modifiers' => false,
                'kickback' => array(0, 0, 0),
                'success' => array(3, -30, 0, 10, $this_robot->print_name().' was invigorated by the blast!'),
                'failure' => array(3, -65, 0, -10, $this_robot->print_name().' avoided the blast&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_results['this_amount'];
            $energy_damage_amount += !empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0;
            $energy_damage_amount = round($energy_damage_amount * ($this_ability->ability_damage2 / 100));
            //$energy_damage_amount = round($energy_damage_amount * (2 / 3));
            $this_robot->trigger_damage($target_robot, $this_ability, $energy_damage_amount, false);

        }
        // Otherwise, if the ability missed or was absored somehow, treat as attack from target
        else {

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'frame' => 'damage',
                'type' => '',
                'kickback' => array(15, 0, 0),
                //'success' => array(3, -30, 0, 10, $this_robot->print_name().' was damaged by the blast!'),
                'success' => array(3, -65, 0, -10, $this_robot->print_name().' avoided the blast&hellip;'),
                'failure' => array(3, -65, 0, -10, $this_robot->print_name().' avoided the blast&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'type' => '',
                'kickback' => array(0, 0, 0),
                //'success' => array(3, -30, 0, 10, $this_robot->print_name().' was invigorated by the blast!'),
                'success' => array(3, -65, 0, -10, $this_robot->print_name().' avoided the blast&hellip;'),
                'failure' => array(3, -65, 0, -10, $this_robot->print_name().' avoided the blast&hellip;')
                ));
            $energy_damage_amount = 0; //ceil($this_ability->ability_damage * ($this_robot->robot_attack / $this_robot->robot_defense));
            $this_robot->trigger_damage($target_robot, $this_ability, $energy_damage_amount, false);

        }

        // Update the player session quickly

        // If this robot is no longer active, find a new active robot for this player
        $this_active_robot = $this_robot;
        if ($this_robot->robot_energy < 1 || $this_robot->robot_status == 'disabled'){
            foreach ($this_player->values['robots_active'] AS $key => $info){
                if ($info['robot_position'] != 'bench'){
                        $this_active_robot = rpg_game::get_robot($this_battle, $this_player, array('robot_id' => $info['robot_id'], 'robot_token' => $info['robot_token']));
                    }
            }
        }

        // Trigger the disabled event on the target robot now if necessary
        if ($target_robot->robot_energy < 1 || $target_robot->robot_status == 'disabled'){
            $target_robot->trigger_disabled($this_active_robot);

        }

        // Trigger the disabled event on this robot now if necessary
        if ($this_robot->robot_energy < 1 || $this_robot->robot_status == 'disabled'){
            //$this_robot->robot_energy = 1;
            //$this_robot->robot_status = 'active';
            //$this_robot->robot_frame = 'defeat';
            //$this_robot->update_session();
            $this_robot->trigger_disabled($target_robot);
            //$this_battle->actions_empty();
            //$this_robot->robot_energy = 0;
            //$this_robot->robot_status = 'disabled';
            //$this_robot->update_session();
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