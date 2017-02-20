<?
// OMEGA PULSE
$ability = array(
    'ability_name' => 'Omega Pulse',
    'ability_token' => 'omega-pulse',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Weapons/Xtra',
    'ability_description' => 'The user taps into its hidden power to generate a pulse of elemental energy, sending it toward the target to inflict massive damage. This ability\'s elemental type appears to be unique for each robot.',
    'ability_energy' => 4,
    'ability_damage' => 14,
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
        $robot_omega_string = rpg_game::generate_omega_string($this_player->user_token, 'player', $this_robot->robot_token);
        $robot_hidden_power = rpg_game::select_omega_value($robot_omega_string, $hidden_power_types);
        $this_ability->set_type($robot_hidden_power);

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>