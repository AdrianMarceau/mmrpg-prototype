<?
// RISING CUTTER
$ability = array(
  'ability_name' => 'Rising Cutter',
  'ability_token' => 'rising-cutter',
  'ability_game' => 'MM01',
  'ability_group' => 'MM01/Weapons/003',
  'ability_master' => 'cut-man',
  'ability_number' => 'DLN-003',
  'ability_description' => 'The user summons a giant pair of scissor-like blades below the target that rise up to inflict massive damage and lower defense by {DAMAGE2}%! This powerful ability is unaffected by the target\'s resistances or immunities and always hits with perfect accuracy.',
  'ability_type' => 'cutter',
  'ability_energy' => 8,
  'ability_damage' => 26,
  'ability_damage2' => 10,
  'ability_damage2_percent' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Target the opposing robot
    $this_ability->target_options_update(array(
      'frame' => 'defend',
      'success' => array(0, 430, 0, 10, $this_robot->print_robot_name().' summons the '.$this_ability->print_ability_name().'!')
      ));
    $this_robot->trigger_target($target_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'energy',
      'kickback' => array(0, 15, 0),
      'success' => array(1, 0, 30, 10, 'The '.$this_ability->print_ability_name().' sliced through the target!'),
      'failure' => array(1, 0, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;'),
      'options' => array(
        'apply_resistance_modifiers' => false,
        'apply_immunity_modifiers' => false,
        )
      ));
    $this_ability->recovery_options_update(array(
      'kind' => 'energy',
      'frame' => 'taunt',
      'kickback' => array(0, 0, 0),
      'success' => array(1, 0, 30, 10, 'The '.$this_ability->print_ability_name().' sliced through the target!'),
      'failure' => array(1, 0, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target&hellip;'),
      'options' => array(
        'apply_resistance_modifiers' => false,
        'apply_immunity_modifiers' => false,
        )
      ));
    $energy_damage_amount = $this_ability->ability_damage;
    $this_robot->robot_frame = 'summon';
    $this_robot->update_session();
    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
    $this_robot->robot_frame = 'base';
    $this_robot->update_session();

    // Randomly trigger a defense break if the ability was successful
    if ($target_robot->robot_status != 'disabled'
      && $target_robot->robot_defense > 0
      && $this_ability->ability_results['this_result'] != 'failure'
      && $this_ability->ability_results['this_amount'] > 0){
      // Decrease the target robot's defense stat
      $this_ability->damage_options_update(array(
        'kind' => 'defense',
        'percent' => true,
        'frame' => 'defend',
        'kickback' => array(10, 0, 0),
        'success' => array(8, 0, -6, 10, $target_robot->print_robot_name().'&#39;s shields were damaged!'),
        'failure' => array(8, 0, -6, -10, '')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'defense',
        'percent' => true,
        'frame' => 'taunt',
        'kickback' => array(0, 0, 0),
        'success' => array(8, 0, -6, 10, $target_robot->print_robot_name().'&#39;s shields improved!'),
        'failure' => array(8, 0, -6, -9999, '')
        ));
      $defense_damage_amount = ceil($target_robot->robot_defense * ($this_ability->ability_damage2 / 100));
      $target_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount);
    }

    // Return true on success
    return true;

  },
  'ability_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // If this robot is holding a Target Module, allow target selection
    if ($this_robot->robot_item == 'item-target-module'){
      $this_ability->ability_target = 'select_target';
    } else {
      $this_ability->ability_target = $this_ability->ability_base_target;
    }

    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>