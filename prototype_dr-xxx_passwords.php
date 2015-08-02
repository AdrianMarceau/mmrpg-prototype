<?
// Collect a reference to the password string in session
if (!isset($_SESSION['GAME']['values']['battle_passwords'])){
  $_SESSION['GAME']['values']['battle_passwords'] = array();
  foreach ($mmrpg_index['players'] AS $token => $info){
    if ($token != 'player'){
      $_SESSION['GAME']['values']['battle_passwords'][$token] = array();
    }
  }
}

// Loop through each of the password arrays and process them
$battle_password_arrays = !empty($_SESSION['GAME']['values']['battle_passwords']) ? $_SESSION['GAME']['values']['battle_passwords'] : array();
//die('<pre>$battle_password_arrays = '.print_r($battle_password_arrays, true).'</pre>');
foreach ($battle_password_arrays AS $player_token => $password_array){
  // Collect info about this ability
  if (!isset($mmrpg_index['players'][$player_token])){ continue; }
  $player_info = $mmrpg_index['players'][$player_token];
  // Ensure there are passwords to process before proceeding
  if (!empty($password_array)){
    foreach ($password_array AS $password_token => $password_value){

      // -- ABILITY UNLOCKS -- //

      // Unlock Password : Ability Get
      // Check to see if this is an ability password
      if (preg_match('/^abilityget/i', $password_token)){
        //die('<pre>$password_token = '.print_r($password_token, true).'</pre>');

        // Break apart the password apart to grab the token
        $token = preg_replace('/^abilityget/i', '', $password_token);
        $ability_token = '';
        //die('<pre>$password_token = '.print_r($password_token, true).', $token = '.$token.'</pre>');

        // Copy Shot : Now I've Got Your Power!
        if ($token == 'nowivegotyourpower'){ $ability_token = 'copy-shot'; }

        // Light Buster : Thomas' Defense
        elseif ($token == 'thomasdefense' && $player_token == 'dr-light'){ $ability_token = 'light-buster'; }
        // Wily Buster : Albert's Attack
        elseif ($token == 'albertsattack' && $player_token == 'dr-wily'){ $ability_token = 'wily-buster'; }
        // Cossack Buster : Mikhail's Speed
        elseif ($token == 'mikhailsspeed' && $player_token == 'dr-cossack'){ $ability_token = 'cossack-buster'; }

        // Needle Cannon : Eye of the Needle!
        elseif ($token == 'eyeoftheneedle'){ $ability_token = 'needle-cannon'; }
        // Magnet Missile : Rules of Attraction!
        elseif ($token == 'bulletofattraction'){ $ability_token = 'magnet-missile'; }
        // Gemini Laser : Memory of Lightwaves!
        elseif ($token == 'memoryoflightwaves'){ $ability_token = 'gemini-laser'; }
        // Hard Knuckle : I'm Gonna Wreck It!
        elseif ($token == 'imgonnawreckit'){ $ability_token = 'hard-knuckle'; }
        // Top Spin : You Spin Me Right Round!
        elseif ($token == 'youspinmerightround'){ $ability_token = 'top-spin'; }
        // Search Snake : Snake in the Grass!
        elseif ($token == 'snakeinthegrass'){ $ability_token = 'search-snake'; }
        // Spark Shock : Electrical Communication!
        elseif ($token == 'electricalcommunication'){ $ability_token = 'spark-shock'; }
        // Shadow Blade : Cutter of Darkness!
        elseif ($token == 'cutterofdarkness'){ $ability_token = 'shadow-blade'; }

        // Bubble Bomb : Bubble Bombs Away!
        elseif ($token == 'bubblebombsaway'){ $ability_token = 'bubble-bomb'; }

        // If the ability token was not empty, let's unlock it
        if (!empty($ability_token) && !isset($_SESSION['GAME']['flags']['events']['password-item_'.$player_token.'_'.$password_token])){
          //die('<pre>$ability_token = '.print_r($ability_token, true).'</pre>');

          // Only unlock the ability if it's not already unlocked
          if (!mmrpg_prototype_ability_unlocked($player_token, false, $ability_token)){
            //die('<pre>mmrpg_prototype_ability_unlocked('.$player_token.', false, '.$ability_token.')</pre>');

            // Collect info about this ability
            $ability_info = mmrpg_ability::get_index_info($ability_token);

            // Unlock it as an equippable ability
            mmrpg_game_unlock_ability($player_info, false, $ability_info, true);
            //exit('ability unlocked');

          }

          // Now that we finished parsing the password, let's remove it
          $_SESSION['GAME']['flags']['events']['password-ability_'.$password_token] = true;
          unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

          //exit('we unlocked the ability, apparently');

          // Redirect back to the prototype menu and exit
          header('Location: prototype.php');
          exit();

        }
        // Otheriwse, if a valid abilty token was not found
        else {

          // Remove the password from the list without parsing it
          unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

        }

      }

      // -- ROBOT UNLOCKS -- //

      // Unlock Password : Robot Get
      // Check to see if this is an robot password
      elseif (preg_match('/^robotget/i', $password_token)){
        //die('<pre>$password_token = '.print_r($password_token, true).'</pre>');

        // Break apart the password apart to grab the token
        $token = preg_replace('/^robotget/i', '', $password_token);
        $robot_token = '';
        //die('<pre>$password_token = '.print_r($password_token, true).', $token = '.$token.'</pre>');

        // Bond Man : Bonded for Life!
        if ($token == 'bondedforlife'){ $robot_token = 'bond-man'; }

        // Roll : Let's Rock 'n Roll!
        elseif ($token == 'letsrocknroll' && $player_token == 'dr-light'){ $robot_token = 'roll'; }
        // Disco : Panic at the Disco!
        elseif ($token == 'panicatthedisco' && $player_token == 'dr-wily'){ $robot_token = 'disco'; }
        // Rhythm : Rhythm is a Dancer!
        elseif ($token == 'rhythmisadancer' && $player_token == 'dr-cossack'){ $robot_token = 'rhythm'; }

        // If the robot token was not empty, let's unlock it
        if (!empty($robot_token)){
          //die('<pre>$robot_token = '.print_r($robot_token, true).'</pre>');

          // Only unlock the robot if it's not already unlocked
          if (!mmrpg_prototype_robot_unlocked(false, $robot_token)){
            //die('<pre>mmrpg_prototype_robot_unlocked('.$player_token.', false, '.$robot_token.')</pre>');

            // Collect info about this robot
            $robot_info = mmrpg_robot::get_index_info($robot_token);

            // Unlock Bubble Bomb as an equippable robot
            mmrpg_game_unlock_robot($player_info, $robot_info, true, true);
            //exit('robot unlocked');

          }

          // Now that we finished parsing the password, let's remove it
          $_SESSION['GAME']['flags']['events']['password-robot_'.$password_token] = true;
          unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

          //exit('we unlocked the robot, apparently');

          // Redirect back to the prototype menu and exit
          header('Location: prototype.php');
          exit();

        }
        // Otheriwse, if a valid abilty token was not found
        else {

          // Remove the password from the list without parsing it
          unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

        }

      }

      // -- ITEM UNLOCKS -- //

      // Unlock Password : Item Get
      // Check to see if this is an item password
      elseif (preg_match('/^itemget/i', $password_token)){
        //die('<pre>$password_token = '.print_r($password_token, true).'</pre>');

        // Break the password apart to grab the token
        $token = preg_replace('/^itemget/i', '', $password_token);
        $item_token = '';
        $item_quantity = 0;
        //die('<pre>$password_token = '.print_r($password_token, true).', $token = '.$token.'</pre>');

        // Neutral Core x 1 : Clear Core / Cores of the Clear
        if ($token == 'clearcore'){ $item_token = 'item-core-none'; $item_quantity = 1; }
        elseif ($token == 'coresoftheclear'){ $item_token = 'item-core-none'; $item_quantity = 3; }

        // Cutter Core x 1 : Blade Core / Cores of the Blade
        elseif ($token == 'bladecore'){ $item_token = 'item-core-cutter'; $item_quantity = 1; }
        elseif ($token == 'coresoftheblade'){ $item_token = 'item-core-cutter'; $item_quantity = 3; }

        // Impact Core x 1 : Force Core / Cores of the Force
        elseif ($token == 'forcecore'){ $item_token = 'item-core-impact'; $item_quantity = 1; }
        elseif ($token == 'coresoftheforce'){ $item_token = 'item-core-impact'; $item_quantity = 3; }

        // Freeze Core x 1 : Frost Core / Cores of the Frost
        elseif ($token == 'frostcore'){ $item_token = 'item-core-freeze'; $item_quantity = 1; }
        elseif ($token == 'coresofthefrost'){ $item_token = 'item-core-freeze'; $item_quantity = 3; }

        // Explode Core x 1 : Blast Core / Cores of the Blast
        elseif ($token == 'blastcore'){ $item_token = 'item-core-explode'; $item_quantity = 1; }
        elseif ($token == 'coresoftheblast'){ $item_token = 'item-core-explode'; $item_quantity = 3; }

        // Flame Core x 1 : Ember Core / Cores of the Ember
        elseif ($token == 'embercore'){ $item_token = 'item-core-flame'; $item_quantity = 1; }
        elseif ($token == 'coresoftheember'){ $item_token = 'item-core-flame'; $item_quantity = 3; }

        // Electric Core x 1 : Shock Core / Cores of the Shock
        elseif ($token == 'shockcore'){ $item_token = 'item-core-electric'; $item_quantity = 1; }
        elseif ($token == 'coresoftheshock'){ $item_token = 'item-core-electric'; $item_quantity = 3; }

        // Time Core x 1 : Clock Core / Cores of the Clock
        elseif ($token == 'clockcore'){ $item_token = 'item-core-time'; $item_quantity = 1; }
        elseif ($token == 'coresoftheclock'){ $item_token = 'item-core-time'; $item_quantity = 3; }

        // Earth Core x 1 : Stone Core / Cores of the Stone
        elseif ($token == 'stonecore'){ $item_token = 'item-core-earth'; $item_quantity = 1; }
        elseif ($token == 'coresofthestone'){ $item_token = 'item-core-earth'; $item_quantity = 3; }

        // Wind Core x 1 : Squall Core / Cores of the Squall
        elseif ($token == 'squallcore'){ $item_token = 'item-core-wind'; $item_quantity = 1; }
        elseif ($token == 'coresofthesquall'){ $item_token = 'item-core-wind'; $item_quantity = 3; }

        // Water Core x 1 : Ocean Core / Cores of the Ocean
        elseif ($token == 'oceancore'){ $item_token = 'item-core-water'; $item_quantity = 1; }
        elseif ($token == 'coresoftheocean'){ $item_token = 'item-core-water'; $item_quantity = 3; }

        // Swift Core x 1 : Quick Core / Cores of the Quick
        elseif ($token == 'quickcore'){ $item_token = 'item-core-swift'; $item_quantity = 1; }
        elseif ($token == 'coresofthequick'){ $item_token = 'item-core-swift'; $item_quantity = 3; }

        // Nature Core x 1 : Plant Core / Cores of the Plant
        elseif ($token == 'plantcore'){ $item_token = 'item-core-nature'; $item_quantity = 1; }
        elseif ($token == 'coresoftheplant'){ $item_token = 'item-core-nature'; $item_quantity = 3; }

        // Missile Core x 1 : Target Core / Cores of the Target
        elseif ($token == 'targetcore'){ $item_token = 'item-core-missile'; $item_quantity = 1; }
        elseif ($token == 'coresofthetarget'){ $item_token = 'item-core-missile'; $item_quantity = 3; }

        // Crystal Core x 1 : Prism Core / Cores of the Prism
        elseif ($token == 'prismcore'){ $item_token = 'item-core-crystal'; $item_quantity = 1; }
        elseif ($token == 'coresoftheprism'){ $item_token = 'item-core-crystal'; $item_quantity = 3; }

        // Shadow Core x 1 : Night Core / Cores of the Night
        elseif ($token == 'nightcore'){ $item_token = 'item-core-shadow'; $item_quantity = 1; }
        elseif ($token == 'coresofthenight'){ $item_token = 'item-core-shadow'; $item_quantity = 3; }

        // Space Core x 1 : Cosmos Core / Cores of the Cosmos
        elseif ($token == 'cosmoscore'){ $item_token = 'item-core-space'; $item_quantity = 1; }
        elseif ($token == 'coresofthecosmos'){ $item_token = 'item-core-space'; $item_quantity = 3; }

        // Shield Core x 1 : Guard Core / Cores of the Guard
        elseif ($token == 'guardcore'){ $item_token = 'item-core-shield'; $item_quantity = 1; }
        elseif ($token == 'coresoftheguard'){ $item_token = 'item-core-shield'; $item_quantity = 3; }

        // Laser Core x 1 : Light Core / Cores of the Light
        elseif ($token == 'lightcore'){ $item_token = 'item-core-laser'; $item_quantity = 1; }
        elseif ($token == 'coresofthelight'){ $item_token = 'item-core-laser'; $item_quantity = 3; }

        // Copy Core x 1 : Mirror Core / Cores of the Mirror
        elseif ($token == 'mirrorcore'){ $item_token = 'item-core-copy'; $item_quantity = 1; }
        elseif ($token == 'coresofthemirror'){ $item_token = 'item-core-copy'; $item_quantity = 3; }

        // If the item token was not empty, let's unlock it
        if (!empty($item_token) && !empty($item_quantity)){
          //die('<pre>$item_token = '.print_r($item_token, true).'</pre>');

          // Collect info about this item
          $item_info = mmrpg_ability::get_index_info($item_token);

          // Only unlock the item if it exists
          if (!empty($item_info) && !isset($_SESSION['GAME']['flags']['events']['password-item_'.$password_token])){

            // Grant the player +1 of this item in their session
            $is_new = false;
            if (!isset($_SESSION['GAME']['values']['battle_items'][$item_token])){ $_SESSION['GAME']['values']['battle_items'][$item_token] = 0; $is_new = true; }
            $_SESSION['GAME']['values']['battle_items'][$item_token] += $item_quantity;

            // Generate the attributes and text variables for this ability unlock
            $this_player_token = $player_info['player_token'];
            $item_info_size = isset($item_info['ability_image_size']) ? $item_info['ability_image_size'] * 2 : 40 * 2;
            $item_info_size_token = $item_info_size.'x'.$item_info_size;
            $this_name = $item_info['ability_name'];
            $this_type_token = !empty($item_info['ability_type']) ? $item_info['ability_type'] : '';
            if (!empty($item_info['ability_type2'])){ $this_type_token .= '_'.$item_info['ability_type2']; }
            if (empty($this_type_token)){ $this_type_token = 'none'; }
            $this_description = !empty($item_info['ability_description']) && $item_info['ability_description'] != '...' ? $item_info['ability_description'] : '';
            $this_find = array('{this_player}', '{this_ability}', '{target_player}', '{target_ability}');
            $this_replace = array($player_info['player_name'], $item_info['ability_name'], $player_info['player_name'], ($this_player_token == 'dr-light' ? 'Mega Man' : ($this_player_token == 'dr-wily' ? 'Bass' : ($this_player_token == 'dr-cossack' ? 'Proto Man' : 'Robot'))));
            $this_field = array('field_token' => 'intro-field', 'field_name' => 'Intro Field'); //mmrpg_field::get_index_info('field'); //mmrpg_field::get_index_info(!empty($item_info['ability_field']) ? $item_info['ability_field'] : 'intro-field');

            // Generate the window event's canvas and message markup then append to the global array
            $temp_canvas_markup = '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$this_field['field_token'].'/battle-field_background_base.gif?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -50px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$this_field['field_name'].'</div>';
            $temp_canvas_markup .= '<div class="sprite sprite_80x80" style="background-image: url(images/fields/'.$this_field['field_token'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); background-position: center -45px; top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto;">'.$this_field['field_name'].'</div>';

            $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_02" style="background-image: url(images/players/'.$player_info['player_token'].'/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; left: 220px;">'.$player_info['player_name'].'</div>';

            $temp_canvas_markup .= '<div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/abilities/'.str_replace('dr-', '', $player_info['player_token']).'-buster/sprite_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); bottom: 40px; right: 200px;">&nbsp;</div>';
            $temp_canvas_markup .= '<div class="ability_type ability_type_'.$this_type_token.' sprite sprite_40x40 sprite_40x40_00" style="
              position: absolute;
              bottom: 52px;
              right: 212px;
              padding: 4px;
              -moz-border-radius: 10px;
              -webkit-border-radius: 10px;
              border-radius: 10px;
              border-style: solid;
              border-color: #181818;
              border-width: 4px;
              box-shadow: inset 1px 1px 6px rgba(0, 0, 0, 0.8);
              ">&nbsp;</div>';
            $temp_canvas_markup .= '<div class="sprite" style="
              bottom: 57px;
              right: 217px;
              width: 44px;
              height: 44px;
              overflow: hidden;
              background-color: rgba(13,13,13,0.33);
              -moz-border-radius: 6px;
              -webkit-border-radius: 6px;
              border-radius: 6px;
              border-style: solid;
              border-color: #292929;
              border-width: 1px;
              box-shadow: 0 0 6px rgba(255, 255, 255, 0.6);
              "><div class="sprite sprite_'.$item_info_size_token.' sprite_'.$item_info_size_token.'_base" style="
              background-image: url(images/abilities/'.$item_info['ability_token'].'/icon_right_'.$item_info_size_token.'.png?'.MMRPG_CONFIG_CACHE_DATE.');
              bottom: -18px;
              right: -18px;
              ">'.$item_info['ability_name'].'</div></div>';

            $temp_console_markup = '<p>Congratulations! <strong>'.$player_info['player_name'].'</strong> found '.($item_quantity == 1 ? (preg_match('/^(a|e|i|o|u)/i', $this_name) ? 'an' : 'a') : $item_quantity).' <strong>'.$this_name.($item_quantity > 1 ? 's' : '').'</strong>! The '.($is_new ? 'new ' : '').($item_quantity > 1 ? 'items were' : 'item was').' added to the inventory.</p>'; //<strong>'.$this_name.'</strong> is '.(!empty($item_info['ability_type']) ? (preg_match('/^(a|e|i|o|u|y)/i', $item_info['ability_type']) ? 'an ' : 'a ').'<strong data-class="ability_type ability_type_'.$item_info['ability_type'].(!empty($item_info['ability_type2']) ? '_'.$item_info['ability_type2'] : '').'">'.ucfirst($item_info['ability_type']).(!empty($item_info['ability_type2']) ? ' and '.ucfirst($item_info['ability_type2']) : '').' Type</strong> ' : '<strong data-class="ability_type ability_type_none">Neutral Type</strong> ').'ability. <strong>'.$this_name.'</strong>&#39;s data was '.($temp_data_existed ? 'updated in ' : 'added to ' ).' the <strong>Robot Database</strong>.
            $temp_console_markup .= '<div id="console" style="width: auto; height: auto;"><div class="extra"><div class="extra2">'.preg_replace('/\s+/', ' ', mmrpg_ability::print_database_markup($item_info, array('layout_style' => 'event'))).'</div></div></div>';
            //die(''.$this_ability_token.': '.$temp_console_markup);

            $_SESSION[$session_token]['EVENTS'][] = array(
              'canvas_markup' => preg_replace('/\s+/', ' ', $temp_canvas_markup),
              'console_markup' => $temp_console_markup
              );

          }

          // Now that we finished parsing the password, let's remove it
          $_SESSION['GAME']['flags']['events']['password-item_'.$password_token] = true;
          unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

          //exit('we unlocked the item, apparently');

          // Redirect back to the prototype menu and exit
          header('Location: prototype.php');
          exit();

        }
        // Otheriwse, if a valid abilty token was not found
        else {

          // Remove the password from the list without parsing it
          unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

        }

      }

      // -- MISSION UNLOCKS (PLAYER BATTLES) -- //

      // Unlock Password : Mission Reset (Vs)
      // Check to see if this is a player battle reset
      elseif (preg_match('/^missiongetvsreset$/i', $password_token)){
        //die('<pre>$password_token = '.print_r($password_token, true).'</pre>');

        // Reset the custom vs array for the requested player
        $_SESSION['GAME']['values']['battle_targets'][$player_token] = array();

        // Now that we finished parsing the password, let's remove it
        unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

        //exit('we reset the battle targets for this player');

        // Redirect back to the prototype menu and exit
        header('Location: prototype.php');
        exit();


      }
      // Unlock Password : Mission Get (Vs)
      // Check to see if this is player battle password
      elseif (preg_match('/^missiongetvs/i', $password_token)){
        //die('<pre>$password_token = '.print_r($password_token, true).'</pre>');

        // Break apart the password apart to grab the token
        $token = preg_replace('/^missiongetvs/i', '', $password_token);
        $user_token = '';
        //die('<pre>$password_token = '.print_r($password_token, true).', $token = '.$token.'</pre>');

        // Vs Player Battle
        if (!empty($token)){ $user_array = $DB->get_array("SELECT user_id, user_name_clean FROM mmrpg_users WHERE (Replace(user_name, '-', '') LIKE '{$token}') OR (Replace(user_name_public, '-', '') LIKE '{$token}') LIMIT 1"); }

        // If the user token was not empty, let's unlock it
        if (!empty($user_array) && $user_array['user_id'] != $this_userid){
          $user_id = $user_array['user_id'];
          $user_token = $user_array['user_name_clean'];

          // A valid username has been collected, add it to the custom player battle list
          if (!isset($_SESSION['GAME']['values']['battle_targets'][$player_token])){ $_SESSION['GAME']['values']['battle_targets'][$player_token] = array(); }
          elseif (!is_array($_SESSION['GAME']['values']['battle_targets'][$player_token])){ $_SESSION['GAME']['values']['battle_targets'][$player_token] = array($_SESSION['GAME']['values']['battle_targets'][$player_token]); }
          $battle_targets = $_SESSION['GAME']['values']['battle_targets'][$player_token];
          $battle_targets[] = $user_token;
          $battle_targets = array_unique($battle_targets);
          $temp_search = array_search($this_userinfo['user_name_clean'], $battle_targets);
          if ($temp_search !== false){ unset($battle_targets[$temp_search]); $battle_targets = array_values($battle_targets); }
          if (count($battle_targets) > 6){ $battle_targets = array_slice($battle_targets, 0, 6); }
          $_SESSION['GAME']['values']['battle_targets'][$player_token] = $battle_targets;

          // Now that we finished parsing the password, let's remove it
          unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

          //exit('we unlocked the robot, apparently');

          // Redirect back to the prototype menu and exit
          header('Location: prototype.php');
          exit();

        }
        // Otheriwse, if a valid abilty token was not found
        else {

          // Remove the password from the list without parsing it
          unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

        }

      }

      // Otherwise, if this was an unrecognized password
      else {

        // Remove the password from the list without parsing it
        unset($_SESSION['GAME']['values']['battle_passwords'][$player_token][$password_token]);

      }


    }
  }
}

//die('oh no');
//die('<pre>$battle_password_arrays = '.print_r($battle_password_arrays, true).'</pre>');

?>