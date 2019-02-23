<?
// CORE LASER
$ability = array(
    'ability_name' => 'Core Laser',
    'ability_token' => 'core-laser',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Weapons/Core',
    'ability_description' => 'The user releases an elemental beam that burns through the target\'s armor to deal damage. This ability\'s typing appears to be influenced by the energy of nearby cores.',
    'ability_type' => 'laser',
    'ability_energy' => 8,
    'ability_damage' => 36,
    'ability_accuracy' => 96,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update the ability's target options and trigger
        if ($this_robot->robot_gender == 'female'){ $pronoun = 'her'; }
        elseif ($this_robot->robot_gender == 'male'){ $pronoun = 'his'; }
        else { $pronoun = 'its'; }
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(1, -10, 0, -1, $this_robot->print_name().' taps into '.$pronoun.' core power...', 1)
            ));
        $target_options = array('prevent_default_text' => true);
        $this_robot->trigger_target($target_robot, $this_ability, $target_options);

        // Update the ability's target options and trigger
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(2, 120, -20, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!', 2)
            ));
        $target_options = array('prevent_default_text' => true);
        $this_robot->trigger_target($target_robot, $this_ability, $target_options);

        // Update ability options and trigger damage on the target
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(15, 0, 0),
            'success' => array(4, -140, -20, 10, 'The '.$this_ability->print_name().' burned through the target!', 3),
            'failure' => array(4, -140, -20, -10, 'The '.$this_ability->print_name().' missed the target...', 3)
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(4, -120, -20, 10, 'The '.$this_ability->print_name().' invigorated the target!', 3),
            'failure' => array(4, -120, -20, -10, 'The '.$this_ability->print_name().' missed the target...', 3)
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect this robots core and item types
        $ability_base_type = !empty($this_ability->ability_base_type) ? $this_ability->ability_base_type : '';
        $robot_core_type = !empty($this_robot->robot_core) ? $this_robot->robot_core : '';
        $robot_item_type = !empty($this_robot->robot_item) && strstr($this_robot->robot_item, '-core') ? str_replace('-core', '', $this_robot->robot_item) : '';

        // Define the types for this ability
        $ability_types = array();
        $ability_types[] = $ability_base_type;
        if (!empty($robot_core_type) && $robot_core_type != 'copy' && !in_array($robot_core_type, $ability_types)){ $ability_types[] = $robot_core_type; }
        if (!empty($robot_item_type) && $robot_item_type != 'copy' && !in_array($robot_item_type, $ability_types)){ $ability_types[] = $robot_item_type; }
        $ability_types = array_reverse($ability_types);
        $ability_types = array_slice($ability_types, 0, 2);

        // Collect this robot's primary type and change its image if necessary
        $this_ability->set_image($this_ability->ability_token.'_'.$ability_types[0]);
        $this_ability->set_type($ability_types[0]);
        if (!empty($ability_types[1])){
            $this_ability->set_image2($this_ability->ability_token.'_'.$ability_types[1].'2');
            $this_ability->set_type2($ability_types[1]);
        } else {
            $this_ability->set_image2('');
            $this_ability->set_type2('');
        }

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>