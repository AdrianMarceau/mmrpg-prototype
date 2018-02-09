<?
// INTRO BATTLE 2 : VS SNIPER JOE
$battle = array(
    'battle_name' => 'Chapter One Intro Battle II',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the Sniper Joe that\'s taken over the laboratory!',
    'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 1),
    'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 2 * 1),
    'battle_field_base' => array('field_id' => 100, 'field_token' => 'light-laboratory', 'field_name' => 'Light Laboratory', 'field_music' => 'light-laboratory'),
    'battle_target_player' => array(
        'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
        'player_token' => 'player',
        'player_switch' => 1,
        'player_robots' => array(
            array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'sniper-joe', 'robot_level' => 2, 'robot_abilities' => array('joe-shot', 'joe-shield'))
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