<?
// PROTOTYPE BATTLE 5 : VS ENKER
$battle = array(
    'battle_name' => 'Chapter Five Final Battle 1/3',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the resurrected Enker in battle!',
    'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 1),
    'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 35 * 1),
    'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination', 'field_name' => 'Final Destination', 'field_music' => 'final-destination-enker', 'field_mechas' => array('beak', 'beetle-borg', 'tackle-fire', 'flea', 'flutter-fly', 'picket-man', 'peng', 'spine')),
    'battle_target_player' => array(
        'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
        'player_token' => 'player',
        'player_robots' => array(
            array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'beak', 'robot_image' => 'beak', 'robot_level' => 30, 'robot_abilities' => array('beak-shot', 'attack-boost', 'defense-boost', 'speed-boost'), 'flags' => array('hide_from_mission_select' => true)),
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2),
                'robot_token' => 'enker',
                'robot_level' => 35,
                'robot_item' => 'super-capsule',
                'robot_abilities' => array(
                    'buster-shot', 'buster-charge',
                    'energy-boost', 'attack-boost', 'defense-boost', 'speed-boost',
                    'mega-slide', 'bass-baroque', 'proto-strike',
                    'mecha-support'
                    ),
                'values' => array(
                    'robot_rewards' => array(
                        'robot_attack' => 100,
                        'robot_defense' => 100,
                        'robot_speed' => 100
                        )
                    )
                )
            )
        ),
    'battle_rewards' => array(
        'abilities' => array(
            ),
        'items' => array(
            )
        )
    );
?>