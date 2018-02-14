<?
// CHAPTER ONE INTRO BATTLE II : VS JOE (SNIPER)
$battle = array(
    'battle_name' => 'Chapter One Intro Battle II',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the Sniper Joe that\'s taken over Wily Castle!',
    'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 1),
    'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 11 * 1),
    'battle_field_base' => array(
        'field_id' => 100,
        'field_token' => 'wily-castle',
        'field_name' => 'Wily Castle',
        'field_music' => 'wily-castle',
        'field_background_attachments_append' => array(
            'robot_disco-support' => array(
                'class' => 'robot',
                'size' => 40,
                'offset_x' => 244,
                'offset_y' => 169,
                'robot_token' => 'disco',
                'robot_frame' => array(0,6,8),
                'robot_direction' => 'left',
                'hide_if_unlocked' => true
                )
            ),
        ),
    'battle_target_player' => array(
        'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
        'player_token' => 'player',
        'player_switch' => 1,
        'player_robots' => array(
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1),
                'robot_token' => 'sniper-joe',
                'robot_level' => 11,
                'robot_abilities' => array(
                    0 => 'joe-shot',
                    1 => 'joe-shield'
                    ),
                'robot_abilities_choices' =>
                    'always:loop(1,0)'
                )
            )
        ),
    'battle_rewards' => array(
        'robots' => array(
            array('token' => 'disco', 'level' => 12)
            ),
        'abilities' => array(
            ),
        'items' => array(
            )
        )
    );
?>