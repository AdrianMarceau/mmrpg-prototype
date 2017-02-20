<?
// Omega Wave
$ability = array(
    'ability_name' => 'Omega Wave',
    'ability_token' => 'omega-wave',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Weapons/Xtra',
    'ability_description' => 'The user taps into its hidden powers to generate a wave of elemental energy, sending it toward the target to inflict massive damage on contact. This ability\'s elemental types appear to be unique for each player robot combo.',
    'ability_energy' => 8,
    'ability_damage' => 24,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update the ability's target options and trigger
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 105, 0, 10, $this_robot->print_name().' uses its '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(0, -60, 0, 10, 'The '.$this_ability->print_name().' hit the target!'),
            'failure' => array(0, -60, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
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
        $robot_omega_string = rpg_game::generate_omega_string($this_player->user_token, $this_player->player_token, $this_robot->robot_token);
        $robot_hidden_power = rpg_game::select_omega_value($robot_omega_string, $hidden_power_types);
        $this_ability->set_type($robot_hidden_power);

        // Generate this player's omega string, collect their hidden power, and update type2
        $player_omega_string = rpg_game::generate_omega_string($this_player->user_token, $this_player->player_token);
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