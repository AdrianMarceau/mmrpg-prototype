<?
// CHAPTER ONE INTRO BATTLE : VS METS (x2)
$battle = array(
    'battle_name' => 'Chapter One Intro Battle',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the Mets that are attacking Wily Castle!',
    'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERMECHA * 1),
    'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 10 * 1),
    'battle_field_base' => array(
        'field_id' => 100,
        'field_token' => 'intro-field',
        'field_name' => 'Intro Field',
        'field_music' => 'boss-theme-mm02',
        'field_foreground_attachments_append' => array(
            'object_intro-field-wily' => array(
                'class' => 'object',
                'size' => 160,
                'offset_x' => 12,
                'offset_y' => 121,
                'offset_z' => 1,
                'object_token' => 'intro-field-wily',
                'object_frame' => array(0),
                'object_direction' => 'right'
                ),
            'robot_disco-support' => array(
                'class' => 'robot',
                'size' => 40,
                'offset_x' => 91,
                'offset_y' => 118,
                'robot_token' => 'disco',
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
                'robot_level' => 10,
                'robot_abilities' => array(
                    0 => 'met-shot'
                    ),
                'robot_abilities_choices' =>
                    'always:(0)'
                ),
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2),
                'robot_token' => 'met',
                'robot_level' => 10,
                'robot_abilities' => array(
                    0 => 'met-shot'
                    ),
                'robot_abilities_choices' =>
                    'always:(0)'
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