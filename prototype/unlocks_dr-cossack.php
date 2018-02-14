<?

/*
 * DR. COSSACK OPTIONS
 */


/* -- UNLOCKABLE EVENTS -- */

// NEW EVENT : PROTOTYPE PHASE ONE COMPLETE
// If Dr. Cossack has completed all of the missions in phase one, trigger an event
if ($battle_complete_counter_cossack >= MMRPG_SETTINGS_CHAPTER3_MISSIONCOUNT){

    // Create the event flag and unset the player select variable to force main menu
    $temp_event_flag = 'dr-cossack-event-97_phase-one-complete';
    if (empty($temp_game_flags['events'][$temp_event_flag])){
        $temp_game_flags['events'][$temp_event_flag] = true;
        $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
    }

}

// NEW EVENT : PROTOTYPE PHASE TWO COMPLETE
// If Dr. Cossack has completed all of the missions in phase two, trigger an event
if ($battle_complete_counter_cossack >= MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT){

    // Create the event flag and unset the player select variable to force main menu
    $temp_event_flag = 'dr-cossack-event-97_phase-two-complete';
    if (empty($temp_game_flags['events'][$temp_event_flag])){
        $temp_game_flags['events'][$temp_event_flag] = true;
        $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
    }

}

// NEW EVENT : PROTOTYPE CAMPAIGN COMPLETE
// If Dr. Cossack has completed his entire prototype campaign, trigger an event
if ($battle_complete_counter_cossack >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){

    // Display the prototype complete message, showing Dr. Cossack and Proto Man
    $temp_event_flag = 'dr-cossack-event-99_prototype-complete-new';
    if (empty($temp_game_flags['events'][$temp_event_flag])){
        $temp_game_flags['events'][$temp_event_flag] = true;

        // Define the player's battle points total, battles complete, and other details
        $player_token = 'dr-cossack';
        $player_info = $mmrpg_index['players'][$player_token];
        $player_info['player_points'] = mmrpg_prototype_player_points($player_token);
        $player_info['player_battles_complete'] = mmrpg_prototype_battles_complete($player_token);
        $player_info['player_battles_complete_total'] = mmrpg_prototype_battles_complete($player_token, false);
        $player_info['player_battles_failure'] = mmrpg_prototype_battles_failure($player_token);
        $player_info['player_battles_failure_total'] = mmrpg_prototype_battles_failure($player_token, false);
        $player_info['player_robots_count'] = 0;
        $player_info['player_abilities_count'] = mmrpg_prototype_abilities_unlocked($player_token);
        $player_info['player_field_stars'] = mmrpg_prototype_stars_unlocked($player_token, 'field');
        $player_info['player_fusion_stars'] = mmrpg_prototype_stars_unlocked($player_token, 'fusion');
        $player_info['player_screw_counter'] = 0;
        $player_info['player_heart_counter'] = 0;
        // Define the player's experience points total
        $player_info['player_experience'] = 0;
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
            foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $temp_player => $temp_player_info){
                    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'])){
                        $temp_player_robot_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'];
                        $temp_player_robot_settings = $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'];
                        if (empty($temp_player_robot_rewards) || empty($temp_player_robot_settings)){
                            unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots']);
                            unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots']);
                            continue;
                        }
                        foreach ($temp_player_robot_rewards AS $temp_key => $temp_robot_info){
                            if (empty($temp_robot_info['robot_token'])){
                                unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_key]);
                                unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_key]);
                                continue;
                            }
                            $temp_robot_settings = $temp_player_robot_settings[$temp_robot_info['robot_token']];
                            $temp_robot_rewards = $temp_player_robot_settings[$temp_robot_info['robot_token']];
                            if (empty($temp_robot_settings['original_player']) && $temp_player != $player_token){ continue; }
                            if ($temp_robot_settings['original_player'] != $player_token){ continue; }
                            $player_info['player_robots_count']++;
                            if (!empty($temp_robot_info['robot_level'])){ $player_info['player_experience'] += $temp_robot_info['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL; }
                            if (!empty($temp_robot_info['robot_experience'])){ $player_info['player_experience'] += $temp_robot_info['robot_experience']; }
                        }
                    }
            }
        }

        // Define the actual markup for the unlock event
        ob_start();
        ?>
        <div class="database_container database_robot_container">
            <div class="subbody event event_double event_visible" style="margin: 0 !important; ">
                <h2 class="header header_left player_type player_type_speed" style="margin-right: 0; margin-left: 0; ">
                    Dr. Cossack&#39;s Records <div class="header_core robot_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;">Speed Type</div>
                </h2>
                <div class="body body_left" style="margin-left: 0; margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: auto; font-size: 10px; min-height: 90px; ">
                    <table class="full" style="margin: 5px auto -2px;">
                        <colgroup>
                                <col width="52%" />
                                <col width="1%" />
                                <col width="47%" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Exp Points :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_experience']) ? 'cutter' : 'none' ?>"><?= number_format($player_info['player_experience'], 0, '.', ',') ?> EXP</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Unlocked Robots :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_robots_count']) ? 'cutter' : 'none' ?>"><?= $player_info['player_robots_count'].' '.($player_info['player_robots_count'] == 1 ? 'Robot' : 'Robots') ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Battle Points :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_points']) ? 'cutter' : 'none' ?>"><?= number_format($player_info['player_points'], 0, '.', ',') ?> BP</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Unlocked Abilities :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_abilities_count']) ? 'cutter' : 'none' ?>"><?= $player_info['player_abilities_count'].' '.($player_info['player_abilities_count'] == 1 ? 'Ability' : 'Abilities') ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Missions Completed :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_complete']) ? 'energy' : 'none' ?>"><?= $player_info['player_battles_complete'] ?> Missions</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Total Victories :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_complete_total']) ? 'energy' : 'none' ?>"><?= $player_info['player_battles_complete_total'] ?> Victories</span>
                                </td>
                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Missions Failed :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_failure']) ? 'attack' : 'none' ?>"><?= $player_info['player_battles_failure'] ?> Missions</span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Total Defeats :</label>
                                    <span class="player_stat player_type player_type_<?= !empty($player_info['player_battles_failure_total']) ? 'attack' : 'none' ?>"><?= $player_info['player_battles_failure_total'] ?> Defeats</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?
        $temp_player_data = ob_get_clean();
        $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-complete/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-complete/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/dr-cossack/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 200px;">Dr. Cossack</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/proto-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">Proto Man</div>';
        $temp_console_markup = '<p><strong>Congratulations, '.(!empty($_SESSION[$session_token]['USER']['displayname']) ? $_SESSION[$session_token]['USER']['displayname'] : $_SESSION[$session_token]['USER']['username']).'!</strong>  You\'ve completed the <strong>Mega Man RPG Prototype</strong> using <strong>Dr. Cossack</strong> and <strong>Proto Man</strong>! '.rpg_battle::random_victory_quote().'! Your completion records are as follows :</p>';
        $temp_console_markup .= '<div id="console" style="width: auto; height: auto;"><div class="extra"><div class="extra2">'.preg_replace('/\s+/', ' ', $temp_player_data).'</div></div></div>';
        array_push($_SESSION[$session_token]['EVENTS'], array(
            'canvas_markup' => $temp_canvas_markup,
            'console_markup' => $temp_console_markup
            ));

        $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/final-destination-3/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -32px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto; opacity: 0.2; filter: alpha(opacity=20); ">Prototype Complete</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/final-destination-3/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-cossack/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 150px;">Dr. Cossack</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/abilities/item-star-fusion-1/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 52px; left: 248px;">Field Star</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/abilities/item-star-base-1/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 52px; left: 248px;">Field Star</div>';
        $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/robots/proto-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 150px;">Proto Man</div>';
        $temp_console_markup = '';
        $temp_console_markup .= '<p><strong>Dr Cossack\'s</strong> journey through the prototype has now come to an end, but there\'s still more to discover.  As thanks for playing, two new chapters have been added to his game.</p>';
        $temp_console_markup .= '<p><strong>Bonus Chapter</strong> missions contain a randomized assortment of robot targets, alt outfits, and field multipliers.  These missions are great if you\'re looking for a good time.</p>';
        $temp_console_markup .= '<p><strong>Player Battle</strong> missions contain the ghost-data of other members and their armies of customized robot targets.  These missions are perfect if you\'re looking for a challenge.</p>';
        $temp_console_markup .= '<p>We hope you enjoyed this game prototype, and look forward to the final version some day!  Oh, and <a href="http://local.rpg.megamanpoweredup.net/contact/" target="_blank">please leave feedback</a> if you can! We love feedback! :D</p>';
        array_push($_SESSION[$session_token]['EVENTS'], array(
            'canvas_markup' => $temp_canvas_markup,
            'console_markup' => $temp_console_markup
            ));

    }

}


/* -- UNLOCKABLE ROBOTS -- */

// ...not anymore!


/* -- UNLOCKABLE PLAYERS -- */

// ... none yet!


/* -- UNLOCKABLE ITEMS -- */

// Unlock the KALINKA LINK after Dr. Cossack has completed at least half of Chapter Two
$required_missions = MMRPG_SETTINGS_CHAPTER1_MISSIONS;
$required_missions += round(MMRPG_SETTINGS_CHAPTER2_MISSIONS / 2);
if (!mmrpg_prototype_item_unlocked('kalinka-link')
    && mmrpg_prototype_battles_complete('dr-cossack') >= $required_missions
    ){

    // Unlock the Kalinka Link and generate the required event details
    mmrpg_game_unlock_item('kalinka-link', array(
        'event_text' => '{player} made contact! The {item} has been established!',
        'player_token' => 'dr-cossack',
        'shop_token' => 'kalinka',
        'show_images' => array('player', 'shop')
        ));

    // Unlock the EQUIP CODES immediately after the Kalinka Link has been unlocked
    if (!mmrpg_prototype_item_unlocked('equip-codes')){

        // Unlock the Equip Codes and generate the required event details
        mmrpg_game_unlock_item('equip-codes', array(
            'event_text' => '{shop} already made a discovery! The {item} have been unlocked!',
            'player_token' => 'dr-cossack',
            'shop_token' => 'kalinka',
            'show_images' => array('shop')
            ));

    }

}

// Unlock the COSSACK/SEARCH PROGRAM after Dr. Cossack has finished Chapter Three
$required_missions = MMRPG_SETTINGS_CHAPTER1_MISSIONS;
$required_missions += MMRPG_SETTINGS_CHAPTER2_MISSIONS;
$required_missions += MMRPG_SETTINGS_CHAPTER3_MISSIONS;
if (!mmrpg_prototype_item_unlocked('cossack-program')
    && mmrpg_prototype_battles_complete('dr-cossack') >= $required_missions
    ){

    // Unlock the Cossack Program and generate the required event details
    mmrpg_game_unlock_item('cossack-program', array(
        'event_text' => '{player} discovered how to search! The {item} has been activated!',
        'player_token' => 'dr-cossack',
        'show_images' => array('player')
        ));

}

// Unlock the FIELD CODES after Dr. Cossack has completed at least half of Chapter Four
$required_missions = MMRPG_SETTINGS_CHAPTER1_MISSIONS;
$required_missions += MMRPG_SETTINGS_CHAPTER2_MISSIONS;
$required_missions += MMRPG_SETTINGS_CHAPTER3_MISSIONS;
$required_missions += round(MMRPG_SETTINGS_CHAPTER4_MISSIONS / 2);
if (!mmrpg_prototype_item_unlocked('field-codes')
    && mmrpg_prototype_battles_complete('dr-cossack') >= $required_missions
    ){

    // Unlock the Field Codes and generate the required event details
    mmrpg_game_unlock_item('field-codes', array(
        'event_text' => '{shop} made another discovery! The {item} have been unlocked!',
        'player_token' => 'dr-cossack',
        'shop_token' => 'kalinka',
        'show_images' => array('shop')
        ));

}


?>