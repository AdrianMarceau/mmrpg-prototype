<?
// BUSTER SHOT
$ability = array(
    'ability_name' => 'Buster Shot',
    'ability_token' => 'buster-shot',
    'ability_game' => 'MM00',
    'ability_group' => 'MM00/Weapons/T0',
    'ability_description' => 'The user fires a small plasma shot at the target to inflict damage. This ability\'s power increases if the user if holding a buster charge.',
    'ability_energy' => 0,
    'ability_speed' => 2,
    'ability_damage' => 12,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update the ability's target options and trigger
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 105, 0, 10, $this_robot->print_name().' fires a '.$this_ability->print_name().'!')
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

        // Loop through any attachments and boost power by 10% for each buster charge
        $temp_new_damage = $this_ability->ability_base_damage;
        $temp_new_damage_booster = 0;
        foreach ($this_robot->robot_attachments AS $this_attachment_token => $this_attachment_info){
            if (preg_match('/-buster$/i', $this_attachment_token)){ $temp_new_damage_booster += 1; }
        }
        $temp_new_damage += $temp_new_damage * ($temp_new_damage_booster / 7);
        $temp_new_damage = ceil($temp_new_damage);

        // Update the ability's damage with the new amount
        $this_ability->set_damage($temp_new_damage);

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>