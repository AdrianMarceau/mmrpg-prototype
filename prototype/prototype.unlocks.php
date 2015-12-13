<?php
/*
 * DR. LIGHT UNLOCKS
 */

// UNLOCK EVENT : PHASE ZERO START (LIGHT VS MET)

// If Dr. Light has completed all of his second phase, open Dr. Wily's second
if ($battle_complete_counter_light >= 0 && $battle_complete_counter_light < 1){
  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'unlocked-cutscene_dr-light_phase-zero-start';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/intro-field/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/intro-field/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_160x160 sprite_160x160_00" style="background-image: url(images/objects/intro-field-light/sprite_right_160x160.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 60px; left: 10px; z-index: 0;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/abilities/buster-shot/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 54px; left: 150px; z-index: 10;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_shoot" style="background-image: url(images/robots/met/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 60px; left: 180px; z-index: 10;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_defend" style="background-image: url(images/robots/roll/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 290px; z-index: 10;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 36px; left: 340px; z-index: 102;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_defend" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 400px; z-index: 10;"></div>';

    $temp_console_markup = '<p>Mega Man! Roll! Thank goodness you\'re both okay!  I&#39;m not sure how it happened, but it looks like we&#39;ve been digitized by that alien robot and transported <em>inside</em> the prototype!</p>';
    $temp_console_markup .= '<p>We have to find a way out of here, but first we must secure the area and activate the laboratory program. Engage the mecha virus that\'s attacking the lab and defeat it in battle. Please be careful, Mega Man!</p>';

    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

    $temp_game_flags['events'][$temp_event_flag] = true;
    //$_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK EVENT : PHASE ZERO COMPLETE (LIGHT VS SNIPERJOE)

// If Dr. Light has completed all of his second phase, open Dr. Wily's second
if ($battle_complete_counter_light >= 1 && $battle_complete_counter_light < 2){
  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'unlocked-cutscene_dr-light_phase-zero-vs-sniperjoe';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/intro-field/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/intro-field/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_160x160 sprite_160x160_00" style="background-image: url(images/objects/intro-field-light/sprite_right_160x160.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 60px; left: 10px; z-index: 0;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_base" style="background-image: url(images/robots/roll/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 160px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_base" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 230px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_base" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 180px;"></div>';

    $temp_console_markup = '<p>Thank you, Mega Man! This world and its battles may be digital, but I fear the danger of us being deleted while inside are indeed very real. We should all be careful.</p>';
    $temp_console_markup .= '<p>Now that the three of us are all safe and accounted for, we should locate Dr. Cossack and Proto Man.  Let\'s head inside the laboratory and look around.</p>';

    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));
  }

}

// UNLOCK EVENT : PHASE ZERO COMPLETE (LIGHT VS TRILL)

// If Dr. Light has completed all of his second phase, open Dr. Wily's second
if ($battle_complete_counter_light >= 2 && $battle_complete_counter_light < 3){
  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'unlocked-cutscene_dr-light_phase-zero-vs-trill';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -15px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_base" style="background-image: url(images/robots/roll/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 160px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_03" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 230px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_base" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 180px;"></div>';

    $temp_console_markup = '<p>The Light Laboratory field was one of the first three locations programmed into the prototype, and we made it a priority to include as many details from real lab as we could.</p>';
    $temp_console_markup .= '<p>Now that we\'ve taken it back, we should be able to use its equipment to survey the surrounding area. Now let\'s see if I remember how to work everything&hellip;</p>';

    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/field/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -15px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_damage" style="background-image: url(images/robots/roll/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 160px; opacity: 0.6; filter: alpha(opacity=60);"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_05" style="background-image: url(images/players/dr-light/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 210px; opacity: 0.6; filter: alpha(opacity=60);"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_damage" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 250px; opacity: 0.6; filter: alpha(opacity=60);"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_summon" style="background-image: url(images/robots/trill_alt/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 36px; left: 400px; z-index: 10; opacity: 0.3; filter: alpha(opacity=30);"></div>';

    $temp_console_markup = '<p>&nbsp;</p>';
    $temp_console_markup .= '<p style="text-align: center;">!</p>';
    $temp_console_markup .= '<p>&nbsp;</p>';

    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-subspace/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-subspace/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_defend" style="background-image: url(images/robots/roll/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 160px; opacity: 0.9; filter: alpha(opacity=90);"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 210px; opacity: 0.9; filter: alpha(opacity=90);"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_defend" style="background-image: url(images/robots/mega-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 250px; opacity: 0.9; filter: alpha(opacity=90);"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_taunt" style="background-image: url(images/robots/trill_alt/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 36px; left: 400px; z-index: 10; opacity: 0.6; filter: alpha(opacity=60);"></div>';

    $temp_console_markup = '<p>&nbsp;</p>';
    $temp_console_markup .= '<p>&nbsp;</p>';
    $temp_console_markup .= '<p>&nbsp;</p>';

    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-subspace/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-subspace/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_defend" style="background-image: url(images/robots/roll/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 100px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_04" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 185px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_shoot" style="background-image: url(images/robots/mega-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 290px;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_shoot" style="background-image: url(images/robots/trill_alt/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 36px; left: 400px; z-index: 10;"></div>';

    $temp_console_markup = '<p>What..?! Who are you? Where have you taken us?</p>';
    $temp_console_markup .= '<p style="text-align: right; text-shadow: 1px 1px 2px purple;">Greetings, Light Unit.  My designation is <strong>Trill</strong>. I have taken you and your robots to <strong>Prototype Subspace</strong>, the final resting place of discarded data in the prototype.  The master has requested your prompt deletion and I am ready to oblige.</p>';
    $temp_console_markup .= '<p>I don\'t understand&hellip;  but I assure you we\'re not going down without a fight!</p>';
    $temp_console_markup .= '<p style="text-align: right; text-shadow: 1px 1px 2px purple">Very well.  I will not hold back.</p>';

    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));
  }

}

// UNLOCK EVENT : PHASE ONE START (LIGHT VS EIGHT ROBOTS)

// If Dr. Light has completed all of his second phase, open Dr. Wily's second
if ($battle_complete_counter_light >= 3 && $battle_complete_counter_light < 4){
  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'unlocked-cutscene_dr-light_phase-one-start';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -15px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_base" style="background-image: url(images/robots/roll/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 160px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 230px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_base" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 180px;"></div>';

    $temp_console_markup .= '<p>Mega Man!  Roll!  Fantastic work back there!  It looks like we made it back to the laboratory, so let us continue getting this equipment online&hellip;</p>';

    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: -208px -46px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url(images/robots/cut-man/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 110px; left: 312px; z-index: 0; width: 20px; height: 20px; background-size: 100%;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url(images/robots/bomb-man/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 110px; left: 332px; z-index: 0; width: 20px; height: 20px; background-size: 100%;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url(images/robots/ice-man/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 110px; left: 352px; z-index: 0; width: 20px; height: 20px; background-size: 100%;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url(images/robots/fire-man/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 110px; left: 372px; z-index: 0; width: 20px; height: 20px; background-size: 100%;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url(images/robots/guts-man/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 90px; left: 312px; z-index: 0; width: 20px; height: 20px; background-size: 100%;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url(images/robots/time-man/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 90px; left: 332px; z-index: 0; width: 20px; height: 20px; background-size: 100%;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url(images/robots/elec-man/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 90px; left: 352px; z-index: 0; width: 20px; height: 20px; background-size: 100%;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_40x40 sprite_40x40_base" style="background-image: url(images/robots/oil-man/mug_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 90px; left: 372px; z-index: 0; width: 20px; height: 20px; background-size: 100%;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_taunt" style="background-image: url(images/robots/roll/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 30px; left: 140px; z-index: 10;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_defend" style="background-image: url(images/robots/mega-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 34px; left: 200px; z-index: 10;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/dr-light/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 418px; z-index: 102;"></div>';

    $temp_console_markup = '<p>Wonderful, the equipment is still functional! I was able to identify eight different robot master signatures in the data clusters nearby. They appear to have been activated by the alien robot, so we should approach with caution.</p>';
    $temp_console_markup .= '<p>It\'s possible that Proto Man and Cossack are tracking these signatures as well, so we\'ll start our search for them in those areas. Be careful Mega Man, and don\'t forget that Roll is here to help!.</p>';

    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));
  }

}

// UNLOCK EVENT : PHASE ONE FIRST VICTORY (LIGHT)

// If Dr. Light has completed all of his second phase, open Dr. Wily's second
if ($battle_complete_counter_light >= 4 && $battle_complete_counter_light < 5){
  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'unlocked-cutscene_dr-light_phase-one-first-victory';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;

    $mm1_master_unlocked = '';
    foreach ($this_omega_factors_one AS $factor){
      if (rpg_prototype::event_complete('unlocked-robot_'.$factor['robot'])){
        $mm1_master_unlocked = $factor['robot'];
        break;
      }
    }

    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -15px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/light-laboratory/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;"></div>';

    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/dr-light/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 180px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_taunt" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 180px;"></div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_victory" style="background-image: url(images/robots/roll/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 240px;"></div>';

    $temp_console_markup = '<p>Fantastic work Mega Man! And you too Roll! I\'m so proud of both of you.</p>';
    $temp_console_markup .= '<p>One down, seven more to go. We might just make it out of here in one piece! And best of all, we now have '.ucwords(str_replace('-', ' ', $mm1_master_unlocked)).' to take with us on our missions!</p>';
    $temp_console_markup .= '<p>It seems if we disable a robot master using <em style="color: #39BAF8;">only neutral type abilities</em> we can salvage its data and <em style="color: #39BAF8;">unlock the robot master</em> for use in battle!</p>';
    $temp_console_markup .= '<p>We can still use elemental abilities for an easier fight, but we\'ll only be able to download their special weapon if you do. Good luck everyone!</p>';

    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));
  }

}


// UNLOCK ROBOT : ROLL

// If the player has failured at least one battle, unlock Roll as a playable character
if ($battle_failure_counter_light >= 1 && !rpg_game::robot_unlocked(false, 'roll')){
  // Unlock Roll as a playable character
  $unlock_player_info = $mmrpg_index['players']['dr-light'];
  $unlock_robot_info = rpg_robot::get_index_info('roll');
  $unlock_robot_info['robot_level'] = 3;
  $unlock_robot_info['robot_experience'] = 0;
  rpg_game::unlock_robot($unlock_player_info, $unlock_robot_info, true, true);

}

// UNLOCK EVENT : PHASE TWO CHAPTERS (WILY)

// If Dr. Light has completed all of his second phase, open Dr. Wily's second
if ($battle_complete_counter_light >= 14){
  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'completed-phase_dr-light_one';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK EVENT : PROTOTYPE COMPLETE (LIGHT)

// If the player has completed the entire prototype campaign, display window event
if ($battle_complete_counter_light >= 17){
  // Display the prototype complete message, showing Dr. Light and Mega Man
  $temp_event_flag = 'completed-campaign_dr-light_prototype-new';
  if (empty($temp_game_flags['events'][$temp_event_flag])){

    $temp_game_flags['events'][$temp_event_flag] = true;

    // Define the player's battle points total, battles complete, and other details
    $player_token = 'dr-light';
    $player_info = $mmrpg_index['players'][$player_token];
    $player_info['player_points'] = rpg_game::player_points($player_token);
    $player_info['player_battles_complete'] = rpg_prototype::battles_complete($player_token);
    $player_info['player_battles_complete_total'] = rpg_prototype::battles_complete($player_token, false);
    $player_info['player_battles_failure'] = rpg_prototype::battles_failure($player_token);
    $player_info['player_battles_failure_total'] = rpg_prototype::battles_failure($player_token, false);
    $player_info['player_robots_count'] = 0;
    $player_info['player_abilities_count'] = rpg_game::abilities_unlocked($player_token);
    $player_info['player_field_stars'] = rpg_game::stars_unlocked($player_token, 'field');
    $player_info['player_fusion_stars'] = rpg_game::stars_unlocked($player_token, 'fusion');
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
              if (!empty($temp_robot_info['robot_level'])){ $player_info['player_experience'] += $temp_robot_info['robot_level'] * MMRPG_SETTINGS_EXPERIENCE_MIN; }
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
    <?php
    $temp_player_data = ob_get_clean();
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-complete/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-complete/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/dr-light/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 200px;">Dr. Light</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/mega-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">Mega Man</div>';
    $temp_console_markup = '<p><strong>Congratulations, '.(!empty($_SESSION[$session_token]['USER']['displayname']) ? $_SESSION[$session_token]['USER']['displayname'] : $_SESSION[$session_token]['USER']['username']).'!</strong>  You\'ve completed the <strong>Mega Man RPG Prototype</strong> using <strong>Dr. Light</strong> and <strong>Mega Man</strong>! '.rpg_functions::get_random_victory_quote().'! Your completion records are as follows :</p>';
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
    $temp_console_markup .= '<p>A new <strong>Starforce</strong> mechanic has also been unlocked, allowing you to find and collect powerful <strong>Field Stars</strong> and <strong>Fusion Stars</strong> in battle that boost your robots\' elemental abilities.  Use the newly upgraded <strong>Player Editor</strong> to customize missions and share fields between players - doing so is an excellent way to hunt down extra <strong>Starforce</strong> energy.</p>';
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

// If Dr. Light has completed the first three chapters of his campaign, unlock Dr. Wily
if (rpg_prototype::event_complete('completed-chapter_dr-light_three') && !$unlock_flag_wily){
  // Unlock Dr. Wily as a playable character
  rpg_game::unlock_player($mmrpg_index['players']['dr-wily'], false, true);
  $_SESSION[$session_token]['values']['battle_rewards']['dr-wily']['player_points'] = 0;

  // Ensure Bass hasn't already been unlocked by the player
  if (!rpg_game::robot_unlocked(false, 'bass')){
    // Unlock Bass as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-wily'];
    $unlock_robot_info = rpg_robot::get_index_info('bass');
    $unlock_robot_info['robot_level'] = 1;
    $unlock_robot_info['robot_experience'] = rpg_prototype::calculate_experience_required(1) - 1;
    rpg_game::unlock_robot($unlock_player_info, $unlock_robot_info, true, false);
    //$_SESSION[$session_token]['values']['battle_rewards']['dr-wily']['player_robots']['bass']['robot_experience'] = 4000;
  }
  // If Bass has already been unlocked by another doctor, reassign it to Wily's team
  elseif (rpg_game::robot_unlocked(false, 'bass') &&
    !rpg_game::robot_unlocked('dr-wily', 'bass')){
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
  $temp_event_flag = 'unlocked-player_dr-wily';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/wily-castle/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Wily Castle</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/wily-castle/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Wily Castle</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-wily/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 160px;">Dr. Wily</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_taunt" style="background-image: url(images/robots/bass/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 160px;">Bass</div>';
    $temp_console_markup = '<p>Congratulations! <strong>Dr. Wily</strong> has been unlocked as a playable character!</p>';
    $temp_console_markup .= '<p>Play through the game as <strong>Dr. Wily</strong> and <strong>Bass</strong> to experience the events from their perspective, and unlock new robots and abilities as you fight your way through an army of robot opponents&hellip; again!</p>';
    $temp_console_markup .= '<p>Use the <strong>robots</strong> option in the main menu to transfer robots between <strong>Dr. Light</strong> and <strong>Dr. Wily</strong> to gain access to even more abilities and battle combinations! Robots receive twice the experience points in battle when used by another player, so don\'t be afraid to mix it up and have fun!</p>';
    $temp_console_markup .= '<p style="font-size: 10px;">Note : Dr. Wily may take part in robot transfers only after completing Chapter One of his own campaign</p>';
    array_unshift($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));
  }

  // If Wily has been unlocked but somehow Bass was not
  if (!rpg_game::robot_unlocked(false, 'bass')){
    // Unlock Bass as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-wily'];
    $unlock_robot_info = rpg_robot::get_index_info('bass');
    $unlock_robot_info['robot_level'] = 11;
    $unlock_robot_info['robot_experience'] = 999;
    rpg_game::unlock_robot($unlock_player_info, $unlock_robot_info, true, true);
  }

}

// UNLOCK ROBOT : DISCO

// If the player has failed at least two battles, unlock Disco as a playable character
if ($battle_failure_counter_wily >= 2 && !rpg_game::robot_unlocked(false, 'disco')){
  // Unlock Disco as a playable character
  $unlock_player_info = $mmrpg_index['players']['dr-wily'];
  $unlock_robot_info = rpg_robot::get_index_info('disco');
  $unlock_robot_info['robot_level'] = 11;
  $unlock_robot_info['robot_experience'] = 999;
  rpg_game::unlock_robot($unlock_player_info, $unlock_robot_info, true, true);

}

// UNLOCK EVENT : PHASE THREE CHAPTERS (COSSACK)

// If Dr. Wily has completed all of his second phase, open Dr. Cossack's third
if ($battle_complete_counter_wily >= 14){
  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'completed-phase_dr-wily_one';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK EVENT : PROTOTYPE COMPLETE (WILY)

// If the player completed the first battle and leveled up, display window event
if ($battle_complete_counter_wily >= 17){
  // Display the prototype complete message, showing Dr. Wily and Bass
  $temp_event_flag = 'completed-campaign_dr-wily_prototype-new';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;

    // Define the player's battle points total, battles complete, and other details
    $player_token = 'dr-wily';
    $player_info = $mmrpg_index['players'][$player_token];
    $player_info['player_points'] = rpg_game::player_points($player_token);
    $player_info['player_battles_complete'] = rpg_prototype::battles_complete($player_token);
    $player_info['player_battles_complete_total'] = rpg_prototype::battles_complete($player_token, false);
    $player_info['player_battles_failure'] = rpg_prototype::battles_failure($player_token);
    $player_info['player_battles_failure_total'] = rpg_prototype::battles_failure($player_token, false);
    $player_info['player_robots_count'] = 0;
    $player_info['player_abilities_count'] = rpg_game::abilities_unlocked($player_token);
    $player_info['player_field_stars'] = rpg_game::stars_unlocked($player_token, 'field');
    $player_info['player_fusion_stars'] = rpg_game::stars_unlocked($player_token, 'fusion');
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
              if (!empty($temp_robot_info['robot_level'])){ $player_info['player_experience'] += $temp_robot_info['robot_level'] * MMRPG_SETTINGS_EXPERIENCE_MIN; }
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
    <?php
    $temp_player_data = ob_get_clean();
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-complete/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-complete/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/dr-wily/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 200px;">Dr. Wily</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/bass/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">Bass</div>';
    $temp_console_markup = '<p><strong>Congratulations, '.(!empty($_SESSION[$session_token]['USER']['displayname']) ? $_SESSION[$session_token]['USER']['displayname'] : $_SESSION[$session_token]['USER']['username']).'!</strong>  You\'ve completed the <strong>Mega Man RPG Prototype</strong> using <strong>Dr. Wily</strong> and <strong>Bass</strong>! '.rpg_functions::get_random_victory_quote().'! Your completion records are as follows :</p>';
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
    $temp_console_markup .= '<p>A new <strong>Starforce</strong> mechanic has also been unlocked, allowing you to find and collect powerful <strong>Field Stars</strong> and <strong>Fusion Stars</strong> in battle that boost your robots\' elemental abilities.  Use the newly upgraded <strong>Player Editor</strong> to customize missions and share fields between players - doing so is an excellent way to hunt down extra <strong>Starforce</strong> energy.</p>';
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

// If Dr. Wily has completed the first three chapters of his campaign, unlock Dr. Cossack
if (rpg_prototype::event_complete('completed-chapter_dr-wily_three') && !$unlock_flag_cossack){
  // Unlock Dr. Cossack as a playable character
  rpg_game::unlock_player($mmrpg_index['players']['dr-cossack'], false, true);
  $_SESSION[$session_token]['values']['battle_rewards']['dr-cossack']['player_points'] = 0;

  // Ensure Proto Man hasn't already been unlocked by the player
  if (!rpg_game::robot_unlocked(false, 'proto-man')){
    // Unlock Proto Man as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-cossack'];
    $unlock_robot_info = rpg_robot::get_index_info('proto-man');
    $unlock_robot_info['robot_level'] = 1;
    $unlock_robot_info['robot_experience'] = rpg_prototype::calculate_experience_required(1) - 1;
    //$unlock_robot_info['robot_experience'] = 4000;
    rpg_game::unlock_robot($unlock_player_info, $unlock_robot_info, true, false);
    //$_SESSION[$session_token]['values']['battle_rewards']['dr-cossack']['player_robots']['proto-man']['robot_experience'] = 4000;
  }
  // If Proto Man has already been unlocked by another doctor, reassign it to Cossack's team
  elseif (rpg_game::robot_unlocked(false, 'proto-man') &&
    !rpg_game::robot_unlocked('dr-cossack', 'proto-man')){
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
  $temp_event_flag = 'unlocked-player_dr-cossack';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/cossack-citadel/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Cossack Citadel</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/cossack-citadel/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Cossack Citadel</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/players/dr-cossack/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 160px;">Dr. Cossack</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_taunt" style="background-image: url(images/robots/proto-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 160px;">Proto Man</div>';
    $temp_console_markup = '<p>Congratulations! <strong>Dr. Cossack</strong> has been unlocked as a playable character!</p>';
    $temp_console_markup .= '<p>Play through the game as <strong>Dr. Cossack</strong> and <strong>Proto Man</strong> to experience the events from their perspective, and unlock new robots and abilities as you fight your way through an army of robot opponents&hellip; again!</p>';
    $temp_console_markup .= '<p>Use the <strong>robots</strong> option in the main menu to transfer robots between <strong>Dr. Light</strong>, <strong>Dr. Wily</strong> and <strong>Dr. Cossack</strong> and gain access to even more abilities and battle combinations! Robots receive twice the experience points in battle when used by another player, so don\'t be afraid to mix it up and have fun!</p>';
    $temp_console_markup .= '<p style="font-size: 10px;">Note : Dr. Cossack may take part in robot transfers only after completing Chapter One of his own campaign</p>';
    array_unshift($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));
  }

  // If Cossack has been unlocked but somehow Proto Man was not
  if (!rpg_game::robot_unlocked(false, 'proto-man')){
    // Unlock Proto Man as a playable character
    $unlock_player_info = $mmrpg_index['players']['dr-cossack'];
    $unlock_robot_info = rpg_robot::get_index_info('proto-man');
    $unlock_robot_info['robot_level'] = 21;
    $unlock_robot_info['robot_experience'] = 999;
    rpg_game::unlock_robot($unlock_player_info, $unlock_robot_info, true, true);
  }

}

// UNLOCK ROBOT : RHYTHM

// If the player has failed at least three battles, unlock Rhythm as a playable character
if ($battle_failure_counter_cossack >= 3 && !rpg_game::robot_unlocked(false, 'rhythm')){
  // Unlock Rhythm as a playable character
  $unlock_player_info = $mmrpg_index['players']['dr-cossack'];
  $unlock_robot_info = rpg_robot::get_index_info('rhythm');
  $unlock_robot_info['robot_level'] = 21;
  $unlock_robot_info['robot_experience'] = 999;
  rpg_game::unlock_robot($unlock_player_info, $unlock_robot_info, true, true);

}

// UNLOCK EVENT : PHASE TWO CHAPTERS (LIGHT)

// If Dr. Cossack has completed all of his first phase, open Dr. Light's second
if ($battle_complete_counter_cossack >= 10){
  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'dr-cossack_event-97_phase-one-complete';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK EVENT : PHASE THREE CHAPTERS (ALL)

// If Dr. Cossack has completed all of his second phase, open all other third
if ($battle_complete_counter_cossack >= 14){
  // Create the event flag and unset the player select variable to force main menu
  $temp_event_flag = 'dr-cossack_event-97_phase-two-complete';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;
    $_SESSION[$session_token]['battle_settings']['this_player_token'] = false;
  }

}

// UNLOCK EVENT : PROTOTYPE COMPLETE (COSSACK)

// If the player completed the first battle and leveled up, display window event
if ($battle_complete_counter_cossack >= 17){
  // Display the prototype complete message, showing Dr. Cossack and Proto Man
  $temp_event_flag = 'dr-cossack_event-99_prototype-complete-new';
  if (empty($temp_game_flags['events'][$temp_event_flag])){
    $temp_game_flags['events'][$temp_event_flag] = true;

    // Define the player's battle points total, battles complete, and other details
    $player_token = 'dr-cossack';
    $player_info = $mmrpg_index['players'][$player_token];
    $player_info['player_points'] = rpg_game::player_points($player_token);
    $player_info['player_battles_complete'] = rpg_prototype::battles_complete($player_token);
    $player_info['player_battles_complete_total'] = rpg_prototype::battles_complete($player_token, false);
    $player_info['player_battles_failure'] = rpg_prototype::battles_failure($player_token);
    $player_info['player_battles_failure_total'] = rpg_prototype::battles_failure($player_token, false);
    $player_info['player_robots_count'] = 0;
    $player_info['player_abilities_count'] = rpg_game::abilities_unlocked($player_token);
    $player_info['player_field_stars'] = rpg_game::stars_unlocked($player_token, 'field');
    $player_info['player_fusion_stars'] = rpg_game::stars_unlocked($player_token, 'fusion');
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
              if (!empty($temp_robot_info['robot_level'])){ $player_info['player_experience'] += $temp_robot_info['robot_level'] * MMRPG_SETTINGS_EXPERIENCE_MIN; }
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
    <?php
    $temp_player_data = ob_get_clean();
    $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-complete/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/prototype-complete/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">Prototype Complete</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/dr-cossack/sprite_left_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 200px;">Dr. Cossack</div>';
    $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/robots/proto-man/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">Proto Man</div>';
    $temp_console_markup = '<p><strong>Congratulations, '.(!empty($_SESSION[$session_token]['USER']['displayname']) ? $_SESSION[$session_token]['USER']['displayname'] : $_SESSION[$session_token]['USER']['username']).'!</strong>  You\'ve completed the <strong>Mega Man RPG Prototype</strong> using <strong>Dr. Cossack</strong> and <strong>Proto Man</strong>! '.rpg_functions::get_random_victory_quote().'! Your completion records are as follows :</p>';
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
    $temp_console_markup .= '<p>A new <strong>Starforce</strong> mechanic has also been unlocked, allowing you to find and collect powerful <strong>Field Stars</strong> and <strong>Fusion Stars</strong> in battle that boost your robots\' elemental abilities.  Use the newly upgraded <strong>Player Editor</strong> to customize missions and share fields between players - doing so is an excellent way to hunt down extra <strong>Starforce</strong> energy.</p>';
    $temp_console_markup .= '<p>We hope you enjoyed this game prototype, and look forward to the final version some day!  Oh, and <a href="'.MMRPG_CONFIG_ROOTURL.'contact/" target="_blank">please leave feedback</a> if you can! We love feedback! :D</p>';
    array_push($_SESSION[$session_token]['EVENTS'], array(
      'canvas_markup' => $temp_canvas_markup,
      'console_markup' => $temp_console_markup
      ));

  }

}

?>