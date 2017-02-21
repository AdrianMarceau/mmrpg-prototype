<?
// OMEGA WAVE
$ability = array(
    'ability_name' => 'Omega Wave',
    'ability_token' => 'omega-wave',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Weapons/Xtra',
    'ability_description' => 'The user taps into its hidden powers to generate a wave of elemental energy, sending it toward the target to inflict massive damage. This ability\'s elemental types appear to be unique for each player robot combo.',
    'ability_energy' => 8,
    'ability_damage' => 30,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 1,
            'ability_frame_span' => 1,
            'ability_frame_animate' => array(1, 0),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -1),
            'attachment_token' => $this_attachment_token
            );

        // Add the background attachment to the user
        $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

        // Update the ability's target options and trigger
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(2, 120, -30, 10, $this_robot->print_name().' generates an '.$this_ability->print_name().'!', 3)
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Update ability options and trigger damage on the target
        $this_robot->set_frame('throw');
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(20, 0, 0),
            'success' => array(5, -200, -20, 10, 'The '.$this_ability->print_name().' collided with its target!', 3),
            'failure' => array(5, -200, -20, -10, 'The '.$this_ability->print_name().' missed its target&hellip;', 3)
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'kickback' => array(15, 0, 0),
            'success' => array(5, -180, -20, 10, 'The '.$this_ability->print_name().' invigorated its target!', 3),
            'failure' => array(5, -180, -20, -10, 'The '.$this_ability->print_name().' missed its target...', 3)
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
        $this_robot->set_frame('base');

        // Remove the background attachment from the user
        $this_robot->unset_attachment($this_attachment_token);

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
        $this_ability->set_type($robot_hidden_power);

        // Generate this player's omega string, collect their hidden power, and update type2
        $player_omega_string = rpg_game::generate_omega_string($this_player->user_token, $this_player->player_token, 'robot');
        $player_hidden_power = rpg_game::select_omega_value($player_omega_string, $hidden_power_types);
        $this_ability->set_type2($player_hidden_power);

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>