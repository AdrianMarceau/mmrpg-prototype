<?
/*
 * DEMO MISSION SELECT
 */
if (!empty($_SESSION[$session_token]['DEMO'])){
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  
  // DEBUG
  //echo 'checkpoint_'.__LINE__.'<br />';
  
  // Only print out Light's data if conditions allow or do not exist
  if (empty($this_data_condition) || in_array('this_player_token=dr-light', $this_data_condition)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  
    // DEBUG
    //echo 'checkpoint_'.__LINE__.'<br />';
    
    // Print out the demo mode's mission select screen for Dr. Light
    echo '<div class="option_wrapper option_wrapper_hidden '.($prototype_data['demo']['battles_complete'] >= 4 ? 'option_wrapper_complete ' : 'option_wrapper_default ').'" data-condition="this_player_token=dr-light" data-music="'.$prototype_data['demo']['missions_music'].'">'."\n";
    echo $prototype_data['demo']['missions_markup']."\n";
    echo '<a class="option option_1x4 option_spacer" style="visibility: hidden;">&nbsp;</a>'."\n";
    echo '</div>'."\n";
    
  }
  
}
/*
 * NORMAL MISSION SELECT
 */
else {
  if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  
  // DEBUG
  //echo 'checkpoint_'.__LINE__.'<br />';
  
  // Only print out Light's data if conditions allow or do not exist
  if (empty($this_data_condition) || in_array('this_player_token=dr-light', $this_data_condition)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
  
    // DEBUG
    //echo 'checkpoint_'.__LINE__.'<br />';
    
    // Print out the normal mode's mission select screen for Dr. Light
    if ($unlock_flag_light){
      if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
      
      // DEBUG
      //echo 'checkpoint_'.__LINE__.('$prototype_start_link = '.$prototype_start_link.'').'<br />';
      
      $chapters_display_count = 0;
      $chapters_display_markup = '';
      ob_start();
      if ($chapters_unlocked_light['6'] && empty($_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_6'])){ unset($_SESSION[$session_token]['battle_settings']['light_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_6'] = true; }
      if ($chapters_unlocked_light['5'] && empty($_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_5'])){ unset($_SESSION[$session_token]['battle_settings']['light_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_5'] = true; }
      if ($chapters_unlocked_light['4a'] && empty($_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_4'])){ unset($_SESSION[$session_token]['battle_settings']['light_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_4'] = true; }
      if ($chapters_unlocked_light['3'] && empty($_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_3'])){ unset($_SESSION[$session_token]['battle_settings']['light_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_3'] = true; }
      if ($chapters_unlocked_light['2'] && empty($_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_2'])){ unset($_SESSION[$session_token]['battle_settings']['light_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_2'] = true; }
      if ($chapters_unlocked_light['1'] && empty($_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_1'])){ unset($_SESSION[$session_token]['battle_settings']['light_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_1'] = true; }
      if ($chapters_unlocked_light['0'] && empty($_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_0'])){ unset($_SESSION[$session_token]['battle_settings']['light_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['light_unlocked_chapter_0'] = true; }
      $temp_last_chapter = isset($_SESSION[$session_token]['battle_settings']['light_current_chapter']) ? $_SESSION[$session_token]['battle_settings']['light_current_chapter'] : false;
      if ($temp_last_chapter === false){
        if ($chapters_unlocked_light['6']){ $temp_last_chapter = '6'; }
        elseif ($chapters_unlocked_light['5']){ $temp_last_chapter = '5'; }
        elseif ($chapters_unlocked_light['4a']){ $temp_last_chapter = '4'; }
        elseif ($chapters_unlocked_light['3']){ $temp_last_chapter = '3'; }
        elseif ($chapters_unlocked_light['2']){ $temp_last_chapter = '2'; }
        elseif ($chapters_unlocked_light['1']){ $temp_last_chapter = '1'; }
        elseif ($chapters_unlocked_light['0']){ $temp_last_chapter = '0'; }
      }
      if ($chapters_unlocked_light['0']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '0' ? 'chapter_link_active ' : '').'" href="#" data-chapter="0">Chapter One</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_light['1']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '1' ? 'chapter_link_active ' : '').'" href="#" data-chapter="1">Chapter Two</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_light['2']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '2' ? 'chapter_link_active ' : '').'" href="#" data-chapter="2">Chapter Three</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_light['3']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '3' ? 'chapter_link_active ' : '').'" href="#" data-chapter="3">Chapter Four</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_light['4a']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '4' ? 'chapter_link_active ' : '').'" href="#" data-chapter="4">Chapter Five</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_light['5']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '5' ? 'chapter_link_active ' : '').'" href="#" data-chapter="5">Bonus Chapter</a>'; } //else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_light['6']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '6' ? 'chapter_link_active ' : '').'" href="#" data-chapter="6">Player Battles</a>'; } //else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      $chapters_display_markup = ob_get_clean();
      
      echo '<div class="option_wrapper option_wrapper_missions option_wrapper_hidden '.($prototype_data['dr-light']['prototype_complete'] ? 'option_wrapper_complete ' : 'option_wrapper_default ').'" data-condition="this_player_token=dr-light" data-music="'.$prototype_data['dr-light']['missions_music'].'">'."\n";
      echo '<div class="chapter_select chapter_select_'.$chapters_display_count.'" data-player="light">';
        echo $chapters_display_markup;
      echo '</div>';
      if ($prototype_start_link == 'home' && !defined('MMRPG_SCRIPT_REQUEST') && empty($_SESSION[$session_token]['battle_settings']['this_player_token'])){
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo $prototype_data['dr-light']['missions_markup']."\n";
      }
      elseif ($prototype_start_link == 'home' && !defined('MMRPG_SCRIPT_REQUEST') && !empty($_SESSION[$session_token]['battle_settings']['this_player_token']) && $_SESSION[$session_token]['battle_settings']['this_player_token'] == 'dr-light'){
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo $prototype_data['dr-light']['missions_markup']."\n";
      }
      elseif (defined('MMRPG_SCRIPT_REQUEST')){
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo $prototype_data['dr-light']['missions_markup']."\n";
      }
      else {
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo '';
      }
      echo '<a class="option option_1x4 option_spacer" style="visibility: hidden;">&nbsp;</a>'."\n";
      echo '</div>'."\n";
      
    }
    
  }

  // Only print out Wily's data if conditions allow or do not exist
  if (empty($this_data_condition) || in_array('this_player_token=dr-wily', $this_data_condition)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    
    // DEBUG
    //echo 'checkpoint_'.__LINE__.'<br />';
    
    // Print out the normal mode's mission select screen for Dr. Wily
    if ($unlock_flag_wily){
      if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
      
      // DEBUG
      //echo 'checkpoint_'.__LINE__.('$prototype_start_link = '.$prototype_start_link.'').'<br />';
      
      $chapters_display_count = 0;
      $chapters_display_markup = '';
      ob_start();
      if ($chapters_unlocked_wily['6'] && empty($_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_6'])){ unset($_SESSION[$session_token]['battle_settings']['wily_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_6'] = true; }
      if ($chapters_unlocked_wily['5'] && empty($_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_5'])){ unset($_SESSION[$session_token]['battle_settings']['wily_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_5'] = true; }
      if ($chapters_unlocked_wily['4a'] && empty($_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_4'])){ unset($_SESSION[$session_token]['battle_settings']['wily_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_4'] = true; }
      if ($chapters_unlocked_wily['3'] && empty($_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_3'])){ unset($_SESSION[$session_token]['battle_settings']['wily_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_3'] = true; }
      if ($chapters_unlocked_wily['2'] && empty($_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_2'])){ unset($_SESSION[$session_token]['battle_settings']['wily_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_2'] = true; }
      if ($chapters_unlocked_wily['1'] && empty($_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_1'])){ unset($_SESSION[$session_token]['battle_settings']['wily_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_1'] = true; }
      if ($chapters_unlocked_wily['0'] && empty($_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_0'])){ unset($_SESSION[$session_token]['battle_settings']['wily_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['wily_unlocked_chapter_0'] = true; }
      $temp_last_chapter = isset($_SESSION[$session_token]['battle_settings']['wily_current_chapter']) ? $_SESSION[$session_token]['battle_settings']['wily_current_chapter'] : false;
      if ($temp_last_chapter === false){
        if ($chapters_unlocked_wily['6']){ $temp_last_chapter = '6'; }
        elseif ($chapters_unlocked_wily['5']){ $temp_last_chapter = '5'; }
        elseif ($chapters_unlocked_wily['4a']){ $temp_last_chapter = '4'; }
        elseif ($chapters_unlocked_wily['3']){ $temp_last_chapter = '3'; }
        elseif ($chapters_unlocked_wily['2']){ $temp_last_chapter = '2'; }
        elseif ($chapters_unlocked_wily['1']){ $temp_last_chapter = '1'; }
        elseif ($chapters_unlocked_wily['0']){ $temp_last_chapter = '0'; }
      }
      if ($chapters_unlocked_wily['0']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '0' ? 'chapter_link_active ' : '').'" href="#" data-chapter="0">Chapter One</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_wily['1']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '1' ? 'chapter_link_active ' : '').'" href="#" data-chapter="1">Chapter Two</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_wily['2']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '2' ? 'chapter_link_active ' : '').'" href="#" data-chapter="2">Chapter Three</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_wily['3']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '3' ? 'chapter_link_active ' : '').'" href="#" data-chapter="3">Chapter Four</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_wily['4a']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '4' ? 'chapter_link_active ' : '').'" href="#" data-chapter="4">Chapter Five</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_wily['5']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '5' ? 'chapter_link_active ' : '').'" href="#" data-chapter="5">Bonus Chapter</a>'; } //else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_wily['6']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '6' ? 'chapter_link_active ' : '').'" href="#" data-chapter="6">Player Battles</a>'; } //else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      $chapters_display_markup = ob_get_clean();
      
      echo '<div class="option_wrapper option_wrapper_missions option_wrapper_hidden '.($prototype_data['dr-wily']['prototype_complete'] ? 'option_wrapper_complete ' : 'option_wrapper_default ').'" data-condition="this_player_token=dr-wily" data-music="'.$prototype_data['dr-wily']['missions_music'].'">'."\n";
      echo '<div class="chapter_select chapter_select_'.$chapters_display_count.'" data-player="wily">';
        echo $chapters_display_markup;
      echo '</div>';
      if ($prototype_start_link == 'home' && !defined('MMRPG_SCRIPT_REQUEST') && !empty($_SESSION[$session_token]['battle_settings']['this_player_token']) && $_SESSION[$session_token]['battle_settings']['this_player_token'] == 'dr-wily'){
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo $prototype_data['dr-wily']['missions_markup']."\n";
      }
      elseif (defined('MMRPG_SCRIPT_REQUEST')){
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo $prototype_data['dr-wily']['missions_markup']."\n";
      }
      else {
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo '';
      }
      echo '<a class="option option_1x4 option_spacer" style="visibility: hidden;">&nbsp;</a>'."\n";
      echo '</div>'."\n";
      
    }
    
  }

  // Only print out Cossack's data if conditions allow or do not exist
  if (empty($this_data_condition) || in_array('this_player_token=dr-cossack', $this_data_condition)){
    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
    
    // DEBUG
    //echo 'checkpoint_'.__LINE__.'<br />';
    
    // Print out the normal mode's mission select screen for Dr. Cossack
    if ($unlock_flag_cossack){
      if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
      
      // DEBUG
      //echo 'checkpoint_'.__LINE__.('$prototype_start_link = '.$prototype_start_link.'').'<br />';
      
      $chapters_display_count = 0;
      $chapters_display_markup = '';
      ob_start();
      if ($chapters_unlocked_cossack['6'] && empty($_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_6'])){ unset($_SESSION[$session_token]['battle_settings']['cossack_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_6'] = true; }
      if ($chapters_unlocked_cossack['5'] && empty($_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_5'])){ unset($_SESSION[$session_token]['battle_settings']['cossack_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_5'] = true; }
      if ($chapters_unlocked_cossack['4a'] && empty($_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_4'])){ unset($_SESSION[$session_token]['battle_settings']['cossack_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_4'] = true; }
      if ($chapters_unlocked_cossack['3'] && empty($_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_3'])){ unset($_SESSION[$session_token]['battle_settings']['cossack_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_3'] = true; }
      if ($chapters_unlocked_cossack['2'] && empty($_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_2'])){ unset($_SESSION[$session_token]['battle_settings']['cossack_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_2'] = true; }
      if ($chapters_unlocked_cossack['1'] && empty($_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_1'])){ unset($_SESSION[$session_token]['battle_settings']['cossack_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_1'] = true; }
      if ($chapters_unlocked_cossack['0'] && empty($_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_0'])){ unset($_SESSION[$session_token]['battle_settings']['cossack_current_chapter']); $_SESSION[$session_token]['battle_settings']['flags']['cossack_unlocked_chapter_0'] = true; }
      $temp_last_chapter = isset($_SESSION[$session_token]['battle_settings']['cossack_current_chapter']) ? $_SESSION[$session_token]['battle_settings']['cossack_current_chapter'] : false;
      if ($temp_last_chapter === false){
        if ($chapters_unlocked_cossack['6']){ $temp_last_chapter = '6'; }
        elseif ($chapters_unlocked_cossack['5']){ $temp_last_chapter = '5'; }
        elseif ($chapters_unlocked_cossack['4a']){ $temp_last_chapter = '4'; }
        elseif ($chapters_unlocked_cossack['3']){ $temp_last_chapter = '3'; }
        elseif ($chapters_unlocked_cossack['2']){ $temp_last_chapter = '2'; }
        elseif ($chapters_unlocked_cossack['1']){ $temp_last_chapter = '1'; }
        elseif ($chapters_unlocked_cossack['0']){ $temp_last_chapter = '0'; }
      }
      if ($chapters_unlocked_cossack['0']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '0' ? 'chapter_link_active ' : '').'" href="#" data-chapter="0">Chapter One</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_cossack['1']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '1' ? 'chapter_link_active ' : '').'" href="#" data-chapter="1">Chapter Two</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_cossack['2']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '2' ? 'chapter_link_active ' : '').'" href="#" data-chapter="2">Chapter Three</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_cossack['3']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '3' ? 'chapter_link_active ' : '').'" href="#" data-chapter="3">Chapter Four</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_cossack['4a']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '4' ? 'chapter_link_active ' : '').'" href="#" data-chapter="4">Chapter Five</a>'; } else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_cossack['5']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '5' ? 'chapter_link_active ' : '').'" href="#" data-chapter="5">Bonus Chapter</a>'; } //else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      if ($chapters_unlocked_cossack['6']){ $chapters_display_count++; echo '<a class="chapter_link '.($temp_last_chapter === '6' ? 'chapter_link_active ' : '').'" href="#" data-chapter="6">Player Battles</a>'; } //else { $chapters_display_count++; echo '<a class="chapter_link chapter_link_disabled">???</a>'; }
      $chapters_display_markup = ob_get_clean();
      
      echo '<div class="option_wrapper option_wrapper_missions option_wrapper_hidden '.($prototype_data['dr-cossack']['prototype_complete'] ? 'option_wrapper_complete ' : 'option_wrapper_default ').'" data-condition="this_player_token=dr-cossack" data-music="'.$prototype_data['dr-cossack']['missions_music'].'">'."\n";
      echo '<div class="chapter_select chapter_select_'.$chapters_display_count.'" data-player="cossack">';
        echo $chapters_display_markup;
      echo '</div>';
      if ($prototype_start_link == 'home' && !defined('MMRPG_SCRIPT_REQUEST') && !empty($_SESSION[$session_token]['battle_settings']['this_player_token']) && $_SESSION[$session_token]['battle_settings']['this_player_token'] == 'dr-cossack'){
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo $prototype_data['dr-cossack']['missions_markup']."\n";
      }
      elseif (defined('MMRPG_SCRIPT_REQUEST')){
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo $prototype_data['dr-cossack']['missions_markup']."\n";
      }
      else {
        // DEBUG
        //echo 'checkpoint_'.__LINE__.'<br />';
        
        echo '';
      }
      echo '<a class="option option_1x4 option_spacer" style="visibility: hidden;">&nbsp;</a>'."\n";
      echo '</div>'."\n";
      
    }
    
  }
  
}

?>