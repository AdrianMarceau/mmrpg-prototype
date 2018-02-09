<?
// INTRO BATTLE 3 : VS TRILL (SPEED)
$battle = array(
    'battle_name' => 'Chapter One Intro Battle III',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the alien robot Trill and escape Prototype Subspace!',
    'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 1),
    'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 2 * 1),
    'battle_field_base' => array('field_id' => 100, 'field_token' => 'prototype-subspace', 'field_name' => 'Prototype Subspace', 'field_music' => 'prototype-subspace'),
    'battle_target_player' => array(
        'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
        'player_token' => 'player',
        'player_switch' => 1,
        'player_robots' => array(
            array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'trill', 'robot_image' => 'trill_alt3', 'robot_level' => 3, 'robot_abilities' => array('space-shot', 'space-buster', 'space-overdrive'))
            )
        ),
    'battle_rewards' => array(
        'robots' => array(
            ),
        'abilities' => array(
            ),
        'items' => array(
            )
        )
    );
?>