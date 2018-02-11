<?
// CHAPTER ONE INTRO BATTLE III : VS TRILL (SPEED)
$battle = array(
    'battle_name' => 'Chapter One Intro Battle III',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the alien robot Trill and escape Prototype Subspace!',
    'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERBOSS * 1),
    'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 3 * 1),
    'battle_field_base' => array(
        'field_id' => 100,
        'field_token' => 'prototype-subspace',
        'field_name' => 'Prototype Subspace',
        'field_music' => 'prototype-subspace'
        ),
    'battle_target_player' => array(
        'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
        'player_token' => 'player',
        'player_switch' => 1,
        'player_robots' => array(
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1),
                'robot_token' => 'trill',
                'robot_level' => 3,
                'robot_abilities' => array(
                    0 => 'space-shot',
                    1 => 'space-buster',
                    2 => 'space-overdrive',
                    3 => 'speed-mode'
                    ),
                'robot_abilities_choices' =>
                    'start:(3)'.
                    '|once:(1)'.
                    '|high-energy:(0)'.
                    '|medium-energy:(1)'.
                    '|once:(2)'.
                    '|low-energy:(0,1)'
                )
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