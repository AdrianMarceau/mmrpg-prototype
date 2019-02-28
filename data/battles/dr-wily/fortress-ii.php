<?
// PROTOTYPE BATTLE 5 : VS PUNK
$battle = array(
    'battle_name' => 'Chapter Five Final Battle 1/3',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the resurrected Punk in battle!',
    'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination', 'field_name' => 'Final Destination', 'field_music' => 'final-destination-punk', 'field_mechas' => array('batton', 'crazy-cannon', 'fan-fiend', 'killer-bullet', 'pierrobot', 'snapper', 'spring-head', 'telly')),
    'battle_target_player' => array(
        'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
        'player_token' => 'player',
        'player_robots' => array(
            array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'beak', 'robot_image' => 'beak_alt', 'robot_level' => 30, 'robot_abilities' => array('beak-shot', 'attack-boost', 'defense-boost', 'speed-boost'), 'flags' => array('hide_from_mission_select' => true)),
            array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'beak', 'robot_image' => 'beak_alt', 'robot_level' => 30, 'robot_abilities' => array('beak-shot', 'attack-break', 'defense-break', 'speed-break'), 'flags' => array('hide_from_mission_select' => true)),
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3),
                'robot_token' => 'punk',
                'robot_level' => 30,
                'robot_item' => 'weapon-upgrade',
                'robot_abilities' => array(
                    'buster-shot', 'buster-charge',
                    'energy-break', 'attack-break', 'defense-break', 'speed-break',
                    'rising-cutter', 'shadow-blade', 'hard-knuckle', 'spark-shock', 'bright-burst', 'metal-press',
                    'cutter-shot', 'cutter-buster', 'cutter-overdrive',
                    'mecha-support'
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