<?
// ROLL SWING
$ability = array(
    'ability_name' => 'Roll Swing',
    'ability_token' => 'roll-swing',
    'ability_game' => 'MM08',
    'ability_group' => 'MM00/Weapons/Roll',
    'ability_description' => 'The user swings a hand-held weapon at the target to deal damage! The exact weapon used for this ability and the resulting damage appear to rotate with each turn that passes...',
    'ability_type' => '',
    'ability_energy' => 8,
    'ability_damage' => 10,
    'ability_accuracy' => 98,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect the battle turn and its counter position
        $this_battle_turn = $this_battle->counters['battle_turn'];
        if ($this_battle_turn > 3){ $this_battle_turn = $this_battle_turn % 3; }

        // Define which type of weapon will be generated and power
        if ($this_battle_turn % 3 == 0){
            $this_swing_weapon = 'vaccuum';
            $this_ability->set_image($this_ability->ability_token.'-3');
            $this_ability->set_damage($this_ability->ability_base_damage * 3);
        }
        elseif ($this_battle_turn % 2 == 0){
            $this_swing_weapon = 'umbrella';
            $this_ability->set_image($this_ability->ability_token.'-2');
            $this_ability->set_damage($this_ability->ability_base_damage * 2);
        }
        else {
            $this_swing_weapon = 'broom';
            $this_ability->set_image($this_ability->ability_token.'-1');
            $this_ability->reset_damage();
        }

        // Update the ability image if the user is in their alt image
        $alt_image_triggers = array('roll_alt', 'roll_alt3', 'roll_alt5');
        if (in_array($this_robot->robot_image, $alt_image_triggers)){
            $this_ability->set_image($this_ability->ability_image.'-b');
        }

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'kickback' => array(15, 0, 0),
            'success' => array(0, 30, 10, 10,
                $this_robot->print_name().' uses the '.$this_ability->print_name().
                ' to generate '.(preg_match('/^(a|e|i|o|u)/i', $this_swing_weapon) ? 'an' : 'a').' '.
                $this_swing_weapon.'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

        // Move the user forward so it looks like their swining the weapon
        $this_robot->set_frame('throw');
        $this_robot->set_frame_offset('x', 310);

        // Inflict damage on the opposing robot with a broom
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(20, 0, 0),
            'success' => array(1, 30, 0, 10, 'The '.$this_ability->print_name().'\'s '.$this_swing_weapon.' smashed the target!'),
            'failure' => array(1, -60, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        // Reset the offset and move the user back to their position
        $this_robot->set_frame('base');
        $this_robot->set_frame_offset('x', 0);

        // Disable the target if this ability brought them to zero
        if ($target_robot->robot_energy <= 0){ $target_robot->trigger_disabled($this_robot); }

        // Return true on success
        return true;


        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        /*
        // If this robot is holding a Target Module, allow target selection
        if ($this_robot->robot_item == 'target-module'){
            $this_ability->set_target('select_target');
        } else {
            $this_ability->reset_target();
        }
        */

        // Update the ability damage and image based on turn
        $next_battle_turn = $this_battle->counters['battle_turn'] + 1;
        if ($next_battle_turn > 3){ $next_battle_turn = $next_battle_turn % 3; }

        if ($next_battle_turn % 3 == 0){
            $this_ability->set_image($this_ability->ability_token.'-3');
            $this_ability->set_damage($this_ability->ability_base_damage * 3);
        } elseif ($next_battle_turn % 2 == 0){
            $this_ability->set_image($this_ability->ability_token.'-2');
            $this_ability->set_damage($this_ability->ability_base_damage * 2);
        } else {
            $this_ability->set_image($this_ability->ability_token.'-1');
            $this_ability->reset_damage();
        }

        // Update the ability image if the user is in their alt image
        $alt_image_triggers = array('roll_alt', 'roll_alt3', 'roll_alt5');
        if (in_array($this_robot->robot_image, $alt_image_triggers)){
            $this_ability->set_image($this_ability->ability_image.'-b');
        }

        // Return true on success
        return true;

        }
    );
?>