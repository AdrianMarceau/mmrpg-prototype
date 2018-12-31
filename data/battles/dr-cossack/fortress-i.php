<?
// PROTOTYPE BATTLE 5 : VS DR LIGHT
$battle = array(
    'battle_name' => 'Chapter Three Rival Battle',
    'battle_size' => '1x4',
    'battle_encore' => true,
    'battle_description' => 'Defeat the dark Mega Man and Roll clones!',
    'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 2),
    'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 30 * 2),
    'battle_field_base' => array('field_id' => 100, 'field_token' => 'light-laboratory', 'field_name' => 'Light Laboratory', 'field_music' => 'light-laboratory'),
    'battle_target_player' => array(
        'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
        'player_token' => 'player',
        'player_switch' => 1.5,
        'player_robots' => array(
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1),
                'robot_token' => 'mega-man',
                'robot_name' => 'Mega Man Σ',
                'robot_image' => 'mega-man_alt9',
                'robot_level' => 30,
                'robot_item' => 'weapon-tank',
                'robot_abilities' => array('mega-buster', 'mega-ball', 'mega-slide', 'buster-shot', 'rolling-cutter', 'super-throw', 'time-arrow', 'thunder-strike', 'oil-shooter', 'fire-storm', 'ice-breath', 'hyper-bomb'),
                'robot_quotes' => array(
                    'battle_start' => 'My orders are clear. You shall not pass.',
                    'battle_taunt' => 'Your power level is too low. Give up.',
                    'battle_victory' => 'Resistance was futile. You could not win.',
                    'battle_defeat' => 'I should not have lost. I don\'t understand.'
                    )
                ),
            array(
                'robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2),
                'robot_token' => 'roll',
                'robot_name' => 'Roll Σ',
                'robot_image' => 'roll_alt9',
                'robot_level' => 30,
                'robot_item' => 'energy-tank',
                'robot_abilities' => array('roll-buster', 'buster-shot', 'attack-boost', 'defense-boost', 'speed-boost', 'energy-boost'),
                'robot_quotes' => array(
                    'battle_start' => 'My role here is support. Do not try to distract me.',
                    'battle_taunt' => 'I am not the robot you think I am. I will not hold back.',
                    'battle_victory' => 'Your efforts were in vain. You will not win.',
                    'battle_defeat' => 'I have failed my mission. I should be recycled.'
                    )
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