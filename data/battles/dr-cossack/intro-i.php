<?
// CHAPTER ONE INTRO BATTLE : VS METS (x3)
$battle = array(
    'battle_name' => 'Chapter One Intro Battle',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the Mets that are attacking Cossack Citadel!',
    'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERMECHA * 3),
    'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 20 * 1),
    'battle_field_base' => array(
        'field_id' => 100,
        'field_token' => 'wintry-forefront',
        'field_name' => 'Wintry Forefront',
        'field_music' => 'boss-theme-mm04',
        'field_foreground_attachments_append' => array(
            'robot_rhythm-support' => array(
                'class' => 'robot',
                'size' => 40,
                'offset_x' => 91,
                'offset_y' => 118,
                'robot_token' => 'rhythm',
                'robot_frame' => array(8,0,8,0,0),
                'robot_direction' => 'right',
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
                'robot_token' => 'met',
                'robot_level' => 20,
                'robot_abilities' => array(
                    0 => 'met-shot',
                    1 => 'attack-boost',
                    2 => 'attack-break'
                    ),
                'robot_abilities_choices' =>
                    'always:(1,0,2,0,0,0)'
                ),
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2),
                'robot_token' => 'met',
                'robot_level' => 20,
                'robot_abilities' => array(
                    0 => 'met-shot',
                    1 => 'defense-boost',
                    2 => 'defense-break'
                    ),
                'robot_abilities_choices' =>
                    'always:(2,0,1,0,0,0)'
                ),
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3),
                'robot_token' => 'met',
                'robot_level' => 20,
                'robot_abilities' => array(
                    0 => 'met-shot',
                    1 => 'speed-boost',
                    2 => 'speed-break'
                    ),
                'robot_abilities_choices' =>
                    'always:(1,1,0,2,2,0)'
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