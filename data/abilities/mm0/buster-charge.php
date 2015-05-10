<?
// BUSTER CHARGE
$ability = array(
  'ability_name' => 'Buster Charge',
  'ability_token' => 'buster-charge',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Weapons/T0',
  'ability_image_sheets' => 0,
  'ability_description' => 'The user takes a defensive stance and charges themselves to restore depleted weapon energy by {RECOVERY2}%.',
  'ability_energy' => 0,
  'ability_recovery2' => 40,
  'ability_recovery_percent2' => true,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Update the ability's target options and trigger
    $this_ability->target_options_update(array(
      'frame' => 'defend',
      'success' => array(0, 0, 0, 10, $this_robot->print_robot_name().' starts charging energy&hellip;')
      ));
    $this_robot->trigger_target($this_robot, $this_ability);

    // Inflict damage on the opposing robot
    $this_ability->damage_options_update(array(
      'kind' => 'weapons',
      'kickback' => array(10, 0, 0),
      'success' => array(0, 0, 0, 10, 'The '.$this_ability->print_ability_name().' restored depleted power!'),
      'failure' => array(0, 0, 0, -10, 'The '.$this_ability->print_ability_name().' had no effect&hellip;')
      ));
    $weapons_recovery_amount = $this_ability->ability_recovery;
    $this_robot->trigger_recovery($this_robot, $this_ability, $weapons_recovery_amount);

    // Return true on success
    return true;

    },
  'ability_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Define this ability's attachment token
    $this_buster_attachment_tokens = array(
      'ability_mega-buster', 'ability_bass-buster', 'ability_proto-buster',
      'ability_light-buster', 'ability_wily-buster', 'ability_cossack-buster',
      'ability_roll-buster', 'ability_disco-buster', 'ability_rhythm-buster'
      );

    // Loop through any attachments and boost power by 10% for each buster charge
    $temp_new_damage = $this_ability->ability_base_damage;
    $temp_new_damage_booster = 0;
    foreach ($this_robot->robot_attachments AS $this_attachment_token => $this_attachment_info){
      if (in_array($this_attachment_token, $this_buster_attachment_tokens)){ $temp_new_damage_booster += 1; }
    }
    $temp_new_damage += $temp_new_damage * ($temp_new_damage_booster / 7);
    $temp_new_damage = ceil($temp_new_damage);
    // Update the ability's damage with the new amount
    $this_ability->ability_damage = $temp_new_damage;
    // Update the ability session
    $this_ability->update_session();

    // Return true on success
    return true;

    }
  );
?>