<?
// OMEGA WAVE
$ability = array(
    'ability_name' => 'Omega Wave',
    'ability_token' => 'omega-wave',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Weapons/Omega',
    'ability_description' => 'The user taps into its hidden powers to generate a wave of elemental energy. This ability\'s types appear to differ between robots and players.',
    'ability_energy' => 8,
    'ability_damage' => 32,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update the ability's target options and trigger
        if ($this_robot->robot_gender == 'female'){ $pronoun = 'her'; }
        elseif ($this_robot->robot_gender == 'male'){ $pronoun = 'his'; }
        else { $pronoun = 'its'; }
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, -10, 0, -1, $this_robot->print_name().' taps into '.$pronoun.' hidden power...', 1)
            ));
        $target_options = array('prevent_default_text' => true);
        $this_robot->trigger_target($target_robot, $this_ability, $target_options);

        // Update the ability's target options and trigger
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(2, 140, -20, 10, $this_robot->print_name().' releases an '.$this_ability->print_name().'!', 3)
            ));
        $target_options = array('prevent_default_text' => true);
        $this_robot->trigger_target($target_robot, $this_ability, $target_options);

        // Update ability options and trigger damage on the target
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(30, 0, 0),
            'success' => array(5, -220, -20, 10, 'The '.$this_ability->print_name().' collided with the target!', 3),
            'failure' => array(5, -220, -20, -10, 'The '.$this_ability->print_name().' missed the target...', 3)
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'kickback' => array(20, 0, 0),
            'success' => array(5, -180, -20, 10, 'The '.$this_ability->print_name().' invigorated the target!', 3),
            'failure' => array(5, -180, -20, -10, 'The '.$this_ability->print_name().' missed the target...', 3)
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect possible hidden power types
        $hidden_power_types = rpg_type::get_hidden_powers();

        // Generate this robot's omega string, collect it's hidden power, and update type1
        $robot_omega_string = rpg_game::generate_omega_string($this_player->user_token, 'player', $this_robot->robot_token);
        $robot_hidden_power = rpg_game::select_omega_value($robot_omega_string, $hidden_power_types);
        $robot_ability_image = $this_ability->get_base_image().'_'.$robot_hidden_power;
        $this_ability->set_type($robot_hidden_power);
        $this_ability->set_image($robot_ability_image);

        // Generate this player's omega string, collect their hidden power, and update type2
        $player_omega_string = rpg_game::generate_omega_string($this_player->user_token, $this_player->player_token, 'robot');
        $player_hidden_power = rpg_game::select_omega_value($player_omega_string, $hidden_power_types);
        $player_ability_image = $this_ability->get_base_image().'_'.$player_hidden_power.'2';
        if ($player_hidden_power != $robot_hidden_power){
            $this_ability->set_type2($player_hidden_power);
            $this_ability->set_image2($player_ability_image);
        } else {
            $this_ability->set_type2('');
            $this_ability->set_image2('');
        }

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>