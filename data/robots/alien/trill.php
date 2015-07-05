<?
// TRILL
$robot = array(
  'robot_number' => 'EXN-00X',
  'robot_game' => 'MMEXE',
  'robot_name' => 'Trill',
  'robot_token' => 'trill',
  'robot_class' => 'boss',
  'robot_core' => 'space',
  'robot_image_editor' => 412,
  'robot_image_alts' => array(
    array('token' => 'alt', 'name' => 'Trill (Attack Alt)', 'summons' => 100),
    array('token' => 'alt2', 'name' => 'Trill (Defense Alt)', 'summons' => 200),
    array('token' => 'alt3', 'name' => 'Trill (Speed Alt)', 'summons' => 300),
    ),
  'robot_description' => 'Galactic Assistant Robot',
  'robot_energy' => 100,
  'robot_attack' => 100,
  'robot_defense' => 100,
  'robot_speed' => 100,
  'robot_weapons' => 15,
  'robot_weaknesses' => array(),
  'robot_resistances' => array('space', 'water', 'electric'),
  'robot_affinities' => array('freeze', 'flame'),
  'robot_immunities' => array('copy'),
  'robot_abilities' => array(
  	//'plant-barrier',
  	'buster-shot', 'buster-charge',
  	'attack-boost', 'attack-break', 'attack-swap', 'attack-mode',
  	'defense-boost', 'defense-break', 'defense-swap', 'defense-mode',
    'speed-boost', 'speed-break', 'speed-swap', 'speed-mode',
    'energy-boost', 'energy-break', 'energy-swap', 'repair-mode',
    'field-support', 'mecha-support',
    'light-buster', 'wily-buster', 'cossack-buster'
    ),
  'robot_rewards' => array(
    'abilities' => array(
        array('level' => 0, 'token' => 'buster-shot'),
        //array('level' => 0, 'token' => 'space-shot'),
        //array('level' => 0, 'token' => 'space-buster'),
        //array('level' => 0, 'token' => 'space-overdrive'),
        array('level' => 15, 'token' => 'trill-aura'),
        array('level' => 30, 'token' => 'trill-slasher'),
        array('level' => 45, 'token' => 'trill-teranova')
      )
    ),
  'robot_quotes' => array(
    'battle_start' => '',
    'battle_taunt' => '',
    'battle_victory' => '',
    'battle_defeat' => ''
    ),
  'robot_function_onload' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    $this_battle->events_create(false, false, 'testme', 'noreally');

    return true;

    },
  'robot_function_choices_abilities' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    // Collect this robot's remaining life energy
    $robot_energy_percent = ceil(($this_robot->robot_energy / $this_robot->robot_base_energy) * 100);

    // Define actions based on the current turn
    if ($this_robot->robot_weapons == 0){ return 'space-shot'; }
    elseif ($this_battle->counters['battle_turn'] <= 1){ return 'space-buster'; }
    elseif ($this_battle->counters['battle_turn'] >= 2){
      if ($robot_energy_percent <= 30){ return 'space-overdrive'; }
      elseif ($robot_energy_percent <= 60){ return 'space-buster'; }
      else { return 'space-shot'; }
    }

    }
  );
?>