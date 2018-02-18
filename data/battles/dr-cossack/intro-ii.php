<?
// CHAPTER ONE INTRO BATTLE II : VS JOE (SNIPER / SHOULD BE CRYSTAL)
$battle = array(
    'battle_name' => 'Chapter One Intro Battle II',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Clear out the Crystal Joe that have taken over Cossack Citadel!',
    'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 1),
    'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 21 * 1),
    'battle_field_base' => array(
        'field_id' => 100,
        'field_token' => 'cossack-citadel',
        'field_name' => 'Cossack Citadel',
        'field_music' => 'cossack-citadel',
        'field_mechas' => array('crystal-joe'),
        'field_background_attachments_append' => array(
            'robot_rhythm-support' => array(
                'class' => 'robot',
                'size' => 40,
                'offset_x' => 176,
                'offset_y' => 176,
                'robot_token' => 'rhythm',
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
                'robot_token' => 'crystal-joe',
                'robot_level' => 21,
                'robot_abilities' => array(
                    0 => 'joe-shot',
                    1 => 'buster-shot'
                    ),
                'robot_abilities_choices' =>
                    'always:loop(1,0)'
                )
            )
        ),
    'battle_rewards' => array(
        'robots' => array(
            array('token' => 'rhythm', 'level' => 22)
            ),
        'abilities' => array(
            ),
        'items' => array(
            )
        )
    );
?>