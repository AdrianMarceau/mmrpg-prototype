<?
// PROTOTYPE BATTLE 5 : VS BALLADE
$battle = array(
    'battle_name' => 'Chapter Five Final Battle 1/3',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the resurrected Ballade in battle!',
    'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination', 'field_name' => 'Final Destination', 'field_music' => 'final-destination-ballade', 'field_mechas' => array('bulb-blaster', 'robo-fishtot', 'lady-blader', 'manta-missile', 'drill-mole', 'pyre-fly', 'ring-ring', 'skullmet')),
    'battle_target_player' => array(
        'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
        'player_token' => 'player',
        'player_robots' => array(
            array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'beak', 'robot_image' => 'beak_alt2', 'robot_level' => 40, 'robot_abilities' => array('beak-shot', 'attack-boost', 'defense-boost', 'speed-boost'), 'flags' => array('hide_from_mission_select' => true)),
            array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'beak', 'robot_image' => 'beak_alt2', 'robot_level' => 40, 'robot_abilities' => array('beak-shot', 'attack-break', 'defense-break', 'speed-break'), 'flags' => array('hide_from_mission_select' => true)),
            array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'beak', 'robot_image' => 'beak_alt2', 'robot_level' => 40, 'robot_abilities' => array('beak-shot', 'attack-mode', 'defense-mode', 'speed-mode'), 'flags' => array('hide_from_mission_select' => true)),
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4),
                'robot_token' => 'ballade',
                'robot_level' => 45,
                'robot_item' => 'extra-life',
                'robot_abilities' => array(
                    'buster-shot', 'buster-charge',
                    'energy-support', 'attack-support', 'defense-support', 'speed-support',
                    'energy-assault', 'attack-assault', 'defense-assault', 'speed-assault',
                    'cutter-shot', 'freeze-shot', 'flame-shot',
                    'electric-shot',  'crystal-shot',  'space-shot',
                    'shield-shot', 'laser-shot',
                    'mecha-support'
                    ),
                'values' => array(
                    'robot_rewards' => array(
                        'robot_attack' => 300,
                        'robot_defense' => 300,
                        'robot_speed' => 300
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