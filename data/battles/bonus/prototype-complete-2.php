<?
// PROTOTYPE BATTLE 5 : VS BONUS FIELD 2
$battle = array(
  'battle_name' => 'Robot Master Bonus Battle',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_counts' => false,
  'battle_description' => 'You\'ve completed the MMRPG Prototype! Here\'s a bonus robot master battle as thanks for playing to the end! :D',
  'battle_level' => 30,
  'battle_turns' => 24,
  'battle_points' => 60000,
  'battle_field_base' => array('field_id' => 1000, 'field_token' => 'prototype-complete'),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_switch' => 2,
    'player_robots' => array(

      // MEGAMAN 1 ROBOT MASTERS
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'cut-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'ice-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'fire-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'elec-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 5), 'robot_token' => 'oil-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 6), 'robot_token' => 'time-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 7), 'robot_token' => 'guts-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 8), 'robot_token' => 'bomb-man', 'robot_level' => 30),

      // MEGAMAN 2 ROBOT MASTERS
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 9), 'robot_token' => 'air-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 10), 'robot_token' => 'heat-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 11), 'robot_token' => 'crash-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 12), 'robot_token' => 'quick-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 13), 'robot_token' => 'metal-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 14), 'robot_token' => 'wood-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 15), 'robot_token' => 'bubble-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 16), 'robot_token' => 'flash-man', 'robot_level' => 30),

      // MEGAMAN 3 ROBOT MASTERS
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 17), 'robot_token' => 'snake-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 18), 'robot_token' => 'needle-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 19), 'robot_token' => 'magnet-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 20), 'robot_token' => 'top-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 21), 'robot_token' => 'shadow-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 22), 'robot_token' => 'spark-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 23), 'robot_token' => 'hard-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 24), 'robot_token' => 'gemini-man', 'robot_level' => 30),

      // MEGAMAN 4 ROBOT MASTERS
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 25), 'robot_token' => 'bright-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 26), 'robot_token' => 'toad-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 27), 'robot_token' => 'drill-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 28), 'robot_token' => 'pharaoh-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 29), 'robot_token' => 'ring-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 30), 'robot_token' => 'dust-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 31), 'robot_token' => 'dive-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 32), 'robot_token' => 'skull-man', 'robot_level' => 30),

      // MEGAMAN 5 ROBOT MASTERS
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 33), 'robot_token' => 'gravity-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 34), 'robot_token' => 'stone-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 35), 'robot_token' => 'wave-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 36), 'robot_token' => 'gyro-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 37), 'robot_token' => 'star-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 38), 'robot_token' => 'charge-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 39), 'robot_token' => 'napalm-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 40), 'robot_token' => 'crystal-man', 'robot_level' => 30),

      // MEGAMAN 6 ROBOT MASTERS
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 41), 'robot_token' => 'blizzard-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 42), 'robot_token' => 'centaur-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 43), 'robot_token' => 'flame-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 44), 'robot_token' => 'knight-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 45), 'robot_token' => 'plant-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 46), 'robot_token' => 'tomahawk-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 47), 'robot_token' => 'wind-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 48), 'robot_token' => 'yamato-man', 'robot_level' => 30),

      // MEGAMAN 7 ROBOT MASTERS
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 49), 'robot_token' => 'freeze-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 50), 'robot_token' => 'junk-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 51), 'robot_token' => 'burst-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 52), 'robot_token' => 'cloud-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 53), 'robot_token' => 'spring-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 54), 'robot_token' => 'slash-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 55), 'robot_token' => 'shade-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 56), 'robot_token' => 'turbo-man', 'robot_level' => 30),

      // MEGAMAN 8 ROBOT MASTERS
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 57), 'robot_token' => 'tengu-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 58), 'robot_token' => 'astro-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 59), 'robot_token' => 'sword-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 60), 'robot_token' => 'clown-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 61), 'robot_token' => 'search-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 62), 'robot_token' => 'forst-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 63), 'robot_token' => 'grenade-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 64), 'robot_token' => 'aqua-man', 'robot_level' => 30),

      // MEGAMAN 9 ROBOT MASTERS
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 65), 'robot_token' => 'concrete-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 66), 'robot_token' => 'tornado-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 67), 'robot_token' => 'splash-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 68), 'robot_token' => 'plug-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 69), 'robot_token' => 'jewel-man', 'robot_level' => 30),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 70), 'robot_token' => 'hornet-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 71), 'robot_token' => 'magma-man', 'robot_level' => 30),
      //array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 72), 'robot_token' => 'galaxy-man', 'robot_level' => 30),

      ),
    'player_quotes' => array(
      'battle_start' => 'They\'re not very strong, but they\'re all I have at the moment...',
      'battle_taunt' => 'Please don\'t hurt any more of my robots...',
      'battle_victory' => 'I... I can\'t believe we made it! Great work, robots!',
      'battle_defeat' => 'I have nothing left to fight with...'
      )
    ),
  'battle_rewards' => array(
    'abilities' => array(
      array('token' => 'field-support')
      )
    )
  );
?>