<?
/*
 * DR. LIGHT UNLOCKS
 */

// UNLOCK EVENT : PHASE ONE ROBOTS (LIGHT)

// If Dr. Light has completed all of his second phase, open Dr. Wily's second
if ($battle_complete_counter_light >= 1 && $battle_complete_counter_light < 2){

  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'dr-light-event-00_phase-zero-complete';
  if (empty($temp_game_flags['events'][$temp_event_flag])){

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/intro-field/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -15px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Intro Field</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/intro-field/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Intro Field</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 180px;">Dr. Light</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_victory" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 180px;">Mega Man</div>';
    $temp_console_markup = '<p>With me by your side, you will engage the robots one by one and defeat them in battle!</p>';
    $temp_console_markup .= '<p>Simply disabling a robot master will download its special weapon data for you, but if you can disable it <em>using only <span class="ability_type ability_type_none">Neutral Type</span> abilities</em> we might be able to download even more!</p>';
    $temp_console_markup .= '<p>Let&#39;s go Mega Man! The whole world is counting on us!</p>';
    array_unshift($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

    $temp_game_flags['events'][$temp_event_flag] = true;
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/intro-field/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Intro Field</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/intro-field/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Intro Field</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 180px;">Dr. Light</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_defend" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 180px;">Mega Man</div>';
    $temp_console_markup = '<p>Mega Man! Thank you for saving us! I&#39;m not sure how it happened, but it looks like we&#39;ve been digitized and transported <em>inside</em> of the prototype! That Met was made of pure data, and it looks like we are now too&hellip;</p>';
    $temp_console_markup .= '<p>We have to find Dr. Cossack and make our way back to the real world, but I&#39;m afraid it won\'t be easy. Sensors detect a high concentration of enemy robot data active on this server, and it&#39;s likely they will come after us if we do not go after them first.</p>';
    array_unshift($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

    //$temp_game_flags['events'][$temp_event_flag] = true;
    //$_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK ROBOT : ROLL

// If the player has failured at least one battle, unlock Roll as a playable character
if ($battle_failure_counter_light >= 1 && !mmrpg_prototype_robot_unlocked(false, 'roll')){

  // Unlock Roll as a playable character
  $unlock_player_info = $mmrpg_index['players']['dr-light'];
  $unlock_robot_info = rpg_robot::get_index_info('roll');
  $unlock_robot_info['robot_level'] = 1;
  $unlock_robot_info['robot_experience'] = 999;
  mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, true);

}

// UNLOCK EVENT : PHASE TWO CHAPTERS (WILY)

// If Dr. Light has completed all of his second phase, open Dr. Wily's second
if ($battle_complete_counter_light >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){

  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'dr-light-event-97_phase-one-complete';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK EVENT : PROTOTYPE COMPLETE (LIGHT)

// If the player has completed the entire prototype campaign, display window event
if ($battle_complete_counter_light >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){

  // Display the prototype complete message, showing Dr. Light and Mega Man
  $temp_event_flag = 'dr-light-event-99_prototype-complete-new';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;

    // Define the player's battle points total, battles complete, and other details
    $player_token = 'dr-light';
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
        <h2 class="header header_left player_type player_type_defense" style="margin-right: 0; margin-left: 0; ">
          Dr. Light&#39;s Records <div class="header_core robot_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;">Defense Type</div>
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
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/dr-light/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 200px;">Dr. Light</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/mega-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">Mega Man</div>';
    $temp_console_markup = '<p><strong>Congratulations, '.(!empty($_SESSION[$session_token]['USER']['displayname']) ? $_SESSION[$session_token]['USER']['displayname'] : $_SESSION[$session_token]['USER']['username']).'!</strong>  You\'ve completed the <strong>Mega Man RPG Prototype</strong> using <strong>Dr. Light</strong> and <strong>Mega Man</strong>! '.rpg_battle::random_victory_quote().'! Your completion records are as follows :</p>';
    $temp_console_markup .= '<div id="console" style="width: auto; height: auto;"><div class="extra"><div class="extra2">'.preg_replace('/\s+/', ' ', $temp_player_data).'</div></div></div>';
    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/final-destination-3/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -32px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto; opacity: 0.2; filter: alpha(opacity=20); ">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/final-destination-3/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 150px;">Dr. Light</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/abilities/item-star-fusion-1/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 52px; left: 248px;">Field Star</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/abilities/item-star-base-1/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 52px; left: 248px;">Field Star</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 150px;">Mega Man</div>';
    $temp_console_markup = '<p>As thanks for playing to the end, <strong>Dr. Light</strong>\'s campaign has been upgraded with several new features and mechanics to keep things interesting while you replay missions.</p>';
    $temp_console_markup .= '<p>Two new bonus chapters containing special missions have been added to the main menu, and preview data for future robot masters will now appear in all fusion field missions. Try one of the <strong>Player Battles</strong> against another member\'s ghost data for a real challenge!</p>';
    $temp_console_markup .= '<p>A new <strong>Star Force</strong> mechanic has also been unlocked, allowing you to find and collect powerful <strong>Field Stars</strong> and <strong>Fusion Stars</strong> in battle that boost your robots\' elemental abilities.  Use the newly upgraded <strong>Player Editor</strong> to customize missions and share fields between players - doing so is an excellent way to hunt down extra <strong>Star Force</strong> energy.</p>';
    $temp_console_markup .= '<p>We hope you enjoyed this game prototype, and look forward to the final version some day!  Oh, and <a href="'.MMRPG_CONFIG_ROOTURL.'contact/" target="_blank">please leave feedback</a> if you can! We love feedback! :D</p>';
    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

  }

}



/*
 * DR. WILY OPTIONS
 */

// UNLOCK PLAYER : DR. WILY

// If Dr. Light has completed phase1 of his battles, unlock Dr. Wily
if ($battle_complete_counter_light >= MMRPG_SETTINGS_CHAPTER3_MISSIONCOUNT && !$unlock_flag_wily){

  // Unlock Dr. Wily as a playable character
  mmrpg_game_unlock_player($mmrpg_index['players']['dr-wily'], false, true);
  $_SESSION[$session_token]['values']['battle_rewards']['dr-wily']['player_points'] = 0;

  // Ensure Bass hasn't already been unlocked by the player
  if (!mmrpg_prototype_robot_unlocked(false, 'bass')){
    // Unlock Bass as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-wily'];
    $unlock_robot_info = rpg_robot::get_index_info('bass');
    $unlock_robot_info['robot_level'] = 11;
    $unlock_robot_info['robot_experience'] = 999;
    //$unlock_robot_info['robot_experience'] = 4000;
    mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, false);
    //$_SESSION[$session_token]['values']['battle_rewards']['dr-wily']['player_robots']['bass']['robot_experience'] = 4000;
  }
  // If Bass has already been unlocked by another doctor, reassign it to Wily's team
  elseif (mmrpg_prototype_robot_unlocked(false, 'bass') &&
    !mmrpg_prototype_robot_unlocked('dr-wily', 'bass')){
    // Loop through the player rewards and collect Bass' info
    foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $temp_player => $temp_playerinfo){
      if ($temp_player == 'dr-wily'){ continue; }
      foreach ($temp_playerinfo['player_robots'] AS $temp_robot => $temp_robotinfo){
        if ($temp_robot != 'bass'){ continue; }
        // Bass was found, so collect the rewards and settings
        $temp_robotinfo_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_robot];
        $temp_robotinfo_settings = $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot];
        // Assign Bass's rewards and settings to Dr. Wily's player array
        $_SESSION[$session_token]['values']['battle_rewards']['dr-wily']['player_robots'][$temp_robot] = $temp_robotinfo_rewards;
        $_SESSION[$session_token]['values']['battle_settings']['dr-wily']['player_robots'][$temp_robot] = $temp_robotinfo_settings;
        // Unset the original Bass data from this player's session
        unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_robot]);
        unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]);
        // Break now that we're done
        break;
      }
    }
  }

  // Redirect back to this page to recalculate menus
  $unlock_flag_wily = true;
  unset($_SESSION[$session_token]['battle_settings']['this_player_token']);
  header('Location: prototype.php?wap='.($flag_wap ? 'true' : 'false'));
  exit();

} elseif ($unlock_flag_wily){

  // Display the first level-up event showing Bass and the Proto Buster
  $temp_event_flag = 'dr-wily-event-00_player-unlocked';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/wily-castle/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Wily Castle</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/wily-castle/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Wily Castle</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-wily/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 160px;">Dr. Wily</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_taunt" style="background-image: url(images/robots/bass/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 160px;">Bass</div>';
    $temp_console_markup = '<p>Congratulations!  <strong>Dr. Wily</strong> has been unlocked as a playable character!</p>';
    $temp_console_markup .= '<p>Play through the game as <strong>Dr. Wily</strong> and <strong>Bass</strong> to experience the events from their perspective, and unlock new robots and abilities as you fight your way through an army of robot opponents&hellip; again!</p>';
    $temp_console_markup .= '<p>Use the <strong>robots</strong> option in the main menu to transfer robots between <strong>Dr. Light</strong> and <strong>Dr. Wily</strong> to gain access to even more abilities and battle combinations! Robots receive twice the experience points in battle when used by another player, so don\'t be afraid to mix it up and have fun!</p>';
    $temp_console_markup .= '<p style="font-size: 10px;">Note : Dr. Wily may take part in robot transfers only after completing the Intro Field on his own</p>';
    array_unshift($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));
  }

  // If Wily has been unlocked but somehow Bass was not
  if (!mmrpg_prototype_robot_unlocked(false, 'bass')){
    // Unlock Bass as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-wily'];
    $unlock_robot_info = rpg_robot::get_index_info('bass');
    $unlock_robot_info['robot_level'] = 11;
    $unlock_robot_info['robot_experience'] = 999;
    mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, true);
  }

}

// UNLOCK ROBOT : DISCO

// If the player has failed at least two battles, unlock Disco as a playable character
if ($battle_failure_counter_wily >= 2 && !mmrpg_prototype_robot_unlocked(false, 'disco')){

  // Unlock Disco as a playable character
  $unlock_player_info = $mmrpg_index['players']['dr-wily'];
  $unlock_robot_info = rpg_robot::get_index_info('disco');
  $unlock_robot_info['robot_level'] = 11;
  $unlock_robot_info['robot_experience'] = 999;
  mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, true);

}

// UNLOCK EVENT : PHASE THREE CHAPTERS (COSSACK)

// If Dr. Wily has completed all of his second phase, open Dr. Cossack's third
if ($battle_complete_counter_wily >= MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT){

  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'dr-wily-event-97_phase-one-complete';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK EVENT : PROTOTYPE COMPLETE (WILY)

// If the player completed the first battle and leveled up, display window event
if ($battle_complete_counter_wily >= MMRPG_SETTINGS_CHAPTER5_MISSIONCOUNT){

  // Display the prototype complete message, showing Dr. Wily and Bass
  $temp_event_flag = 'dr-wily-event-99_prototype-complete-new';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;

    // Define the player's battle points total, battles complete, and other details
    $player_token = 'dr-wily';
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
        <h2 class="header header_left player_type player_type_attack" style="margin-right: 0; margin-left: 0; ">
          Dr. Wily&#39;s Records <div class="header_core robot_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;">Attack Type</div>
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
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/dr-wily/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 200px;">Dr. Wily</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/bass/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">Bass</div>';
    $temp_console_markup = '<p><strong>Congratulations, '.(!empty($_SESSION[$session_token]['USER']['displayname']) ? $_SESSION[$session_token]['USER']['displayname'] : $_SESSION[$session_token]['USER']['username']).'!</strong>  You\'ve completed the <strong>Bass RPG Prototype</strong> using <strong>Dr. Wily</strong> and <strong>Bass</strong>! '.rpg_battle::random_victory_quote().'! Your completion records are as follows :</p>';
    $temp_console_markup .= '<div id="console" style="width: auto; height: auto;"><div class="extra"><div class="extra2">'.preg_replace('/\s+/', ' ', $temp_player_data).'</div></div></div>';
    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/final-destination-3/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -32px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto; opacity: 0.2; filter: alpha(opacity=20); ">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/final-destination-3/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-wily/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 150px;">Dr. Wily</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/abilities/item-star-fusion-1/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 52px; left: 248px;">Field Star</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/abilities/item-star-base-1/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 52px; left: 248px;">Field Star</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/robots/bass/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 150px;">Bass</div>';
    $temp_console_markup = '<p>As thanks for playing to the end, <strong>Dr. Wily</strong>\'s campaign has been upgraded with several new features and mechanics to keep things interesting while you replay missions.</p>';
    $temp_console_markup .= '<p>Two new bonus chapters containing special missions have been added to the main menu, and preview data for future robot masters will now appear in all fusion field missions. Try one of the <strong>Player Battles</strong> against another member\'s ghost data for a real challenge!</p>';
    $temp_console_markup .= '<p>A new <strong>Star Force</strong> mechanic has also been unlocked, allowing you to find and collect powerful <strong>Field Stars</strong> and <strong>Fusion Stars</strong> in battle that boost your robots\' elemental abilities.  Use the newly upgraded <strong>Player Editor</strong> to customize missions and share fields between players - doing so is an excellent way to hunt down extra <strong>Star Force</strong> energy.</p>';
    $temp_console_markup .= '<p>We hope you enjoyed this game prototype, and look forward to the final version some day!  Oh, and <a href="'.MMRPG_CONFIG_ROOTURL.'contact/" target="_blank">please leave feedback</a> if you can! We love feedback! :D</p>';
    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

  }

}


/*
 * DR. COSSACK OPTIONS
 */

// UNLOCK PLAYER : DR. COSSACK

// If Dr. Light has completed phase1 of his battles, unlock Dr. Cossack
if ($battle_complete_counter_wily >= MMRPG_SETTINGS_CHAPTER3_MISSIONCOUNT && !$unlock_flag_cossack){

  // Unlock Dr. Cossack as a playable character
  mmrpg_game_unlock_player($mmrpg_index['players']['dr-cossack'], false, true);
  $_SESSION[$session_token]['values']['battle_rewards']['dr-cossack']['player_points'] = 0;

  // Ensure Proto Man hasn't already been unlocked by the player
  if (!mmrpg_prototype_robot_unlocked(false, 'proto-man')){
    // Unlock Proto Man as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-cossack'];
    $unlock_robot_info = rpg_robot::get_index_info('proto-man');
    $unlock_robot_info['robot_level'] = 21;
    $unlock_robot_info['robot_experience'] = 999;
    //$unlock_robot_info['robot_experience'] = 4000;
    mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, false);
    //$_SESSION[$session_token]['values']['battle_rewards']['dr-cossack']['player_robots']['proto-man']['robot_experience'] = 4000;
  }
  // If Proto Man has already been unlocked by another doctor, reassign it to Cossack's team
  elseif (mmrpg_prototype_robot_unlocked(false, 'proto-man') &&
    !mmrpg_prototype_robot_unlocked('dr-cossack', 'proto-man')){
    // Loop through the player rewards and collect Proto Man' info
    foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $temp_player => $temp_playerinfo){
      if ($temp_player == 'dr-cossack'){ continue; }
      foreach ($temp_playerinfo['player_robots'] AS $temp_robot => $temp_robotinfo){
        if ($temp_robot != 'proto-man'){ continue; }
        // Proto Man was found, so collect the rewards and settings
        $temp_robotinfo_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_robot];
        $temp_robotinfo_settings = $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot];
        // Assign Proto Man's rewards and settings to Dr. Cossack's player array
        $_SESSION[$session_token]['values']['battle_rewards']['dr-cossack']['player_robots'][$temp_robot] = $temp_robotinfo_rewards;
        $_SESSION[$session_token]['values']['battle_settings']['dr-cossack']['player_robots'][$temp_robot] = $temp_robotinfo_settings;
        // Unset the original Proto Man data from this player's session
        unset($_SESSION[$session_token]['values']['battle_rewards'][$temp_player]['player_robots'][$temp_robot]);
        unset($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]);
        // Break now that we're done
        break;
      }
    }
  }

  // Redirect back to this page to recalculate menus
  $unlock_flag_cossack = true;
  unset($_SESSION[$session_token]['battle_settings']['this_player_token']);
  header('Location: prototype.php?wap='.($flag_wap ? 'true' : 'false'));
  exit();

} elseif ($unlock_flag_cossack){

  // Display the first level-up event showing Proto Man and the Proto Buster
  $temp_event_flag = 'dr-cossack-event-00_player-unlocked';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/wily-castle/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Cossack Citadel</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/wily-castle/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Cossack Citadel</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-cossack/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 160px;">Dr. Cossack</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_taunt" style="background-image: url(images/robots/proto-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 160px;">Proto Man</div>';
    $temp_console_markup = '<p>Congratulations!  <strong>Dr. Cossack</strong> has been unlocked as a playable character!</p>';
    $temp_console_markup .= '<p>Play through the game as <strong>Dr. Cossack</strong> and <strong>Proto Man</strong> to experience the events from their perspective, and unlock new robots and abilities as you fight your way through an army of robot opponents&hellip; again!</p>';
    $temp_console_markup .= '<p>Use the <strong>robots</strong> option in the main menu to transfer robots between <strong>Dr. Light</strong>, <strong>Dr. Wily</strong> and <strong>Dr. Cossack</strong> and gain access to even more abilities and battle combinations! Robots receive twice the experience points in battle when used by another player, so don\'t be afraid to mix it up and have fun!</p>';
    $temp_console_markup .= '<p style="font-size: 10px;">Note : Dr. Cossack may take part in robot transfers only after completing the Intro Field on his own</p>';
    array_unshift($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));
  }

  // If Cossack has been unlocked but somehow Proto Man was not
  if (!mmrpg_prototype_robot_unlocked(false, 'proto-man')){
    // Unlock Proto Man as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-cossack'];
    $unlock_robot_info = rpg_robot::get_index_info('proto-man');
    $unlock_robot_info['robot_level'] = 21;
    $unlock_robot_info['robot_experience'] = 999;
    mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, true);
  }

}

// UNLOCK ROBOT : RHYTHM

// If the player has failed at least three battles, unlock Rhythm as a playable character
if ($battle_failure_counter_cossack >= 3 && !mmrpg_prototype_robot_unlocked(false, 'rhythm')){

  // Unlock Rhythm as a playable character
  $unlock_player_info = $mmrpg_index['players']['dr-cossack'];
  $unlock_robot_info = rpg_robot::get_index_info('rhythm');
  $unlock_robot_info['robot_level'] = 21;
  $unlock_robot_info['robot_experience'] = 999;
  mmrpg_game_unlock_robot($unlock_player_info, $unlock_robot_info, true, true);

}

// UNLOCK EVENT : PHASE TWO CHAPTERS (LIGHT)

// If Dr. Cossack has completed all of his first phase, open Dr. Light's second
if ($battle_complete_counter_cossack >= MMRPG_SETTINGS_CHAPTER3_MISSIONCOUNT){

  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'dr-cossack-event-97_phase-one-complete';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK EVENT : PHASE THREE CHAPTERS (ALL)

// If Dr. Cossack has completed all of his second phase, open all other third
if ($battle_complete_counter_cossack >= MMRPG_SETTINGS_CHAPTER4_MISSIONCOUNT){

  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'dr-cossack-event-97_phase-two-complete';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK EVENT : PROTOTYPE COMPLETE (COSSACK)

// If the player completed the first battle and leveled up, display window event
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
    $temp_console_markup = '<p><strong>Congratulations, '.(!empty($_SESSION[$session_token]['USER']['displayname']) ? $_SESSION[$session_token]['USER']['displayname'] : $_SESSION[$session_token]['USER']['username']).'!</strong>  You\'ve completed the <strong>Proto Man RPG Prototype</strong> using <strong>Dr. Cossack</strong> and <strong>Proto Man</strong>! '.rpg_battle::random_victory_quote().'! Your completion records are as follows :</p>';
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
    $temp_console_markup = '<p>As thanks for playing to the end, <strong>Dr. Cossack</strong>\'s campaign has been upgraded with several new features and mechanics to keep things interesting while you replay missions.</p>';
    $temp_console_markup .= '<p>Two new bonus chapters containing special missions have been added to the main menu, and preview data for future robot masters will now appear in all fusion field missions. Try one of the <strong>Player Battles</strong> against another member\'s ghost data for a real challenge!</p>';
    $temp_console_markup .= '<p>A new <strong>Star Force</strong> mechanic has also been unlocked, allowing you to find and collect powerful <strong>Field Stars</strong> and <strong>Fusion Stars</strong> in battle that boost your robots\' elemental abilities.  Use the newly upgraded <strong>Player Editor</strong> to customize missions and share fields between players - doing so is an excellent way to hunt down extra <strong>Star Force</strong> energy.</p>';
    $temp_console_markup .= '<p>We hope you enjoyed this game prototype, and look forward to the final version some day!  Oh, and <a href="'.MMRPG_CONFIG_ROOTURL.'contact/" target="_blank">please leave feedback</a> if you can! We love feedback! :D</p>';
    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

  }

}


/*
 * COMMON OPTIONS
 */

// UNLOCK EVENT : PLAYER BATTLE REWARDS

// Collect any outstanding battle rewards from the database
$temp_battles_query = "SELECT mmrpg_battles.*, mmrpg_users.user_name AS this_user_name, mmrpg_users.user_name_clean AS this_user_name_clean, mmrpg_users.user_name_public AS this_user_name_public FROM mmrpg_battles ";
$temp_battles_query .= "LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_battles.this_user_id ";
$temp_battles_query .= "WHERE mmrpg_battles.target_user_id = {$this_userid} AND mmrpg_battles.target_reward_pending = 1";
$temp_battles_list = $DB->get_array_list($temp_battles_query);
$temp_userinfo = $this_userinfo; //$DB->get_array("SELECT users.*, roles.* FROM mmrpg_users AS users LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id WHERE users.user_id = '{$this_userid}' LIMIT 1");

// If the player has pending battle rewards, loop through and display window events
if (!empty($temp_battles_list)){

  // Loop through each of the battles and display it to the user
  foreach ($temp_battles_list AS $temp_key => $temp_battleinfo){

    // Collect temp playerinfo for this and the target
    $temp_this_playerinfo = $mmrpg_index['players'][$temp_battleinfo['this_player_token']];
    $temp_this_playername = !empty($temp_battleinfo['this_user_name_public']) ? $temp_battleinfo['this_user_name_public'] : $temp_battleinfo['this_user_name'];
    $temp_this_playerrobots = !empty($temp_battleinfo['this_player_robots']) ? explode(',', $temp_battleinfo['this_player_robots']) : array();
    if (!empty($temp_this_playerrobots)){ foreach ($temp_this_playerrobots AS $key => $info){ list($token, $level) = explode(':',trim($info,'[]')); $temp_this_playerrobots[$key] = array('token' => $token, 'level' => $level); } }
    $temp_target_playerinfo = $mmrpg_index['players'][$temp_battleinfo['target_player_token']];
    $temp_target_playername = !empty($temp_userinfo['user_name_public']) ? $temp_userinfo['user_name_public'] : $temp_userinfo['user_name'];
    $temp_target_playerrobots = !empty($temp_battleinfo['target_player_robots']) ? explode(',', $temp_battleinfo['target_player_robots']) : array();
    if (!empty($temp_target_playerrobots)){ foreach ($temp_target_playerrobots AS $key => $info){ list($token, $level) = explode(':',trim($info,'[]')); $temp_target_playerrobots[$key] = array('token' => $token, 'level' => $level); } }

    //die('<pre>'.print_r($temp_this_playerrobots, true).'</pre>');

    // Collect the appropriate player points to increment
    $temp_inc_zenny = ceil($temp_battleinfo['target_player_points'] / 1000);
    $temp_inc_player = str_replace('-', '_', $temp_target_playerinfo['player_token']);

    // Display the player battle reward message, showing the doctor and his robots
    $temp_console_markup = '';
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$temp_battleinfo['battle_field_background'].'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$temp_battleinfo['battle_field_name'].'</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$temp_battleinfo['battle_field_foreground'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$temp_battleinfo['battle_field_name'].'</div>';
    foreach ($temp_target_playerrobots AS $key => $info){ $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_'.$temp_battleinfo['target_player_result'].'" style="background-image: url(images/robots/'.$info['token'].'/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: '.(25 + $key * 3).'px; left: '.(-10 + $key * 20).'px; z-index: '.(10 - $key).';">'.$info['token'].' (Lv.'.$info['level'].')</div>'; }
    foreach ($temp_this_playerrobots AS $key => $info){ $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_'.$temp_battleinfo['this_player_result'].'" style="background-image: url(images/robots/'.$info['token'].'/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: '.(25 + $key * 3).'px; right: '.(-10 + $key * 20).'px; z-index: '.(10 - $key).';">'.$info['token'].' (Lv.'.$info['level'].')</div>'; }
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_'.$temp_battleinfo['target_player_result'].'" style="background-image: url(images/players/'.$temp_target_playerinfo['player_token'].'/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 25px; left: 110px; z-index: 20;">'.$temp_target_playerinfo['player_name'].'</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_'.$temp_battleinfo['this_player_result'].'" style="background-image: url(images/players/'.$temp_this_playerinfo['player_token'].'/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 25px; right: 110px; z-index: 20;">'.$temp_target_playerinfo['player_name'].'</div>';

      // If this player claimed victory in the battle
      if ($temp_battleinfo['target_player_result'] == 'victory'){
        //$temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_04" style="background-image: url(images/robots/mega-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 170px;">Mega Man</div>';
        $temp_console_markup .= '<p><strong class="player_type player_type_nature" style="display: block; padding: 1px 5px;">Congratulations! Your '.$temp_target_playerinfo['player_name'].' Claimed Victory in a Player Battle!</strong></p>';
        $temp_console_markup .= '<p>Another player by the name of <strong>'.$temp_this_playername.'</strong> challenged your '.$temp_target_playerinfo['player_name'].' to battle&hellip; and lost! Your team of robots were victorious!</p>';
        $temp_console_markup .= '<p>Your '.$temp_target_playerinfo['player_name'].' claimed victory in '.$temp_battleinfo['battle_turns'].' turns and as a reward for being such a tough opponent, collected <strong>'.number_format($temp_inc_zenny, 0, '.', ',').'</strong> zenny for his victory.</p>';
      }
      // Else if this player suffered defeat in the battle
      elseif ($temp_battleinfo['target_player_result'] == 'defeat'){
        //$temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_04" style="background-image: url(images/robots/mega-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 170px;">Mega Man</div>';
        $temp_console_markup .= '<p><strong class="player_type player_type_flame" style="display: block; padding: 1px 5px;">Condolences&hellip; Your '.$temp_target_playerinfo['player_name'].' Suffered Defeat in a Player Battle.</strong></p>';
        $temp_console_markup .= '<p>Another player by the name of <strong>'.$temp_this_playername.'</strong> challenged your '.$temp_target_playerinfo['player_name'].' to battle&hellip; and won! Your team of robots were defeated&hellip;</p>';
        $temp_console_markup .= '<p>Your '.$temp_target_playerinfo['player_name'].' avoided defeat for '.$temp_battleinfo['battle_turns'].' turns and as a reward for being such a good sport, collected <strong>'.number_format($temp_inc_zenny, 0, '.', ',').'</strong> zenny for his participation.</p>';
      }

    // Display the rest of the reward message
    if ($battle_complete_counter_light >= 29){ $temp_console_markup .= '<p>Now that you\'ve unlocked the Player Battle bonus mode, you too can challenge other players and earn even more experience and battle points!</p>'; }
    else { $temp_console_markup .= '<p>Complete all of '.$temp_target_playerinfo['player_name'].'\'s missions to beat the game and you too can challenge other players to battle for tons of experience and more battle points!</p><p>The phrase &quot;Chapter&nbsp;Get&nbsp;:&nbsp;Player&nbsp;Battles&quot; might also be of some use&hellip;</p>'; }

    // Add this mesage to the event session IF VICTORY (Only good news now!)
    if ($temp_battleinfo['target_player_result'] == 'victory'){
      array_unshift($_SESSION[$session_token]['EVENTS'], array(
        'canvas_markup' => $temp_canvas_markup,
        'console_markup' => $temp_console_markup
        ));
    }

    // DEBUG DEBUG DEBUG

    // Increment this user and player's battle zenny counters
    $_SESSION[$session_token]['counters']['battle_zenny'] += $temp_inc_zenny;

    // Update the leaderboard to remove the pending points and add them to the totals
    $DB->update('mmrpg_battles', array(
      'target_reward_pending' => 0
      ), "battle_id = {$temp_battleinfo['battle_id']}");

  }

}

?>