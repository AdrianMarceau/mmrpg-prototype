<?
// SUPER ARM
$ability = array(
  'ability_name' => 'Super Arm',
  'ability_token' => 'super-arm',
  'ability_game' => 'MM01',
  'ability_image_sheets' => 8,
  'ability_description' => 'The user creates a blockade using the surrounding environment to bolster shields and raise defense by {RECOVERY2}%!  The blockade can also be thrown at the target for massive damage! This ability\'s second type appears to change based on which battle field it is being used on.',
  'ability_type' => 'impact',
  'ability_energy' => 8,
  'ability_damage' => 30,
  'ability_recovery2' => 38,
  'ability_recovery_percent2' => true,
  'ability_accuracy' => 95,
  'ability_function' => function($objects){

    // Extract all objects into the current scope
    extract($objects);

    /*
    // Define the sprite sheet index for the fields for internal reference
    Sheet 1 : field, intro-field, wily-castle/light-laboratory/cossack-citadel, final-destination, prototype-complete
    Sheet 2 : mountain-mines, arctic-jungle, steel-mill, electrical-tower, abandoned-warehouse
    Sheet 3 : oil-wells, clock-citadel, orb-city, pipe-station, atomic-furnace
    Sheet 4 : industrial-facility, underground-laboratory, preserved-forest, photon-collider, waterfall-institute
    Sheet 5 : sky-ridge, mineral-quarry,
    Sheet 6 :
    Sheet 7 :
    Sheet 8 :
     */
    
    // Define the sprite sheet and animation defaults
    $this_field_token = $this_battle->battle_field->field_background;
    $this_sprite_sheet = 1;
    $this_target_frame = 0;
    $this_impact_frame = 1;
    $this_object_name = 'boulder';
    $this_object_weaknesses = array('explode');
            
    // Define the sprite sheets and the stages they contain
    $this_sprite_index = !empty($this_ability->values['this_sprite_index']) ? $this_ability->values['this_sprite_index'] : array();
    
    // If the field token has a place in the index, update values
    if (isset($this_sprite_index[$this_field_token])){
      $this_sprite_sheet = $this_sprite_index[$this_field_token][0];
      $this_target_frame = $this_sprite_index[$this_field_token][1];
      $this_impact_frame = $this_sprite_index[$this_field_token][2];
      $this_object_name = $this_sprite_index[$this_field_token][3];
      //$this_object_weaknesses[] = '';
    }
    
    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    $this_attachment_info = array(
    	'class' => 'ability',
      'sticky' => true,
    	'ability_token' => $this_ability->ability_token,
      'ability_image' => 'super-arm'.($this_sprite_sheet > 1 ? '-'.$this_sprite_sheet : ''),
      'attachment_sticky' => true,
      //'attachment_duration' => 3,
      'attachment_defense' => 0,
    	'attachment_weaknesses' => $this_object_weaknesses,
    	'attachment_create' => array(
        'kind' => 'defense',
        'percent' => true,
        'frame' => 'defend',
        'rates' => array(100, 0, 0),
        'kickback' => array(0, 0, 0),
        'success' => array($this_target_frame, 105, 0, 10, $this_robot->print_robot_name().' shielded '.($this_robot->robot_token == 'roll' ? 'herself' : 'himself').' with the '.$this_object_name.'!'),
        'failure' => array($this_target_frame, 105, 0, 10, $this_robot->print_robot_name().'&#39;s defenses were not affected&hellip;')
        ),
    	'attachment_destroy' => array(
        'kind' => 'defense',
        'type' => '',
        'type2' => '',
        'percent' => true,
        'modifiers' => false,
        'frame' => 'defend',
        'rates' => array(100, 0, 0),
        'kickback' => array(0, 0, 0),
        'success' => array(0, 0, -9999, -9999,  'The '.$this_object_name.'&#39;s protection was lost!'),
        'failure' => array(0, 0, -9999, -9999, $this_robot->print_robot_name().'&#39;s defenses were not affected&hellip;')
        ),
      'ability_frame' => $this_target_frame,
      'ability_frame_animate' => array($this_target_frame),
      'ability_frame_offset' => array('x' => 105, 'y' => 0, 'z' => -10)
      );
      
    // Update the ability's image in the session
    $this_ability->ability_image = $this_attachment_info['ability_image'];
    $this_ability->update_session();
    
    // If the ability flag was not set, super arm raises defense by 30%
    if (!isset($this_robot->robot_attachments[$this_attachment_token])){
      
      // Define the defense mod amount for this ability
      $this_attachment_info['attachment_defense'] = ceil($this_robot->robot_defense * ($this_ability->ability_recovery2 / 100));
      if (($this_robot->robot_defense + $this_attachment_info['attachment_defense']) > MMRPG_SETTINGS_STATS_MAX){ $this_attachment_info['attachment_defense'] = MMRPG_SETTINGS_STATS_MAX - $this_robot->robot_defense; }
      
      // Target this robot's self
      $this_ability->target_options_update(array(
        'frame' => 'summon',
        'success' => array($this_target_frame, 105, 0, 10, 'The '.$this_ability->print_ability_name().' created '.(preg_match('/^(a|e|i|o|u)/i', $this_object_name) ? 'an '.$this_object_name : 'a '.$this_object_name).'!')
        ));
      $this_robot->trigger_target($target_robot, $this_ability);
      
      // Increase this robot's defense stat
      $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
      $this_ability->recovery_options_update($this_attachment_info['attachment_create'], true);
      $defense_recovery_amount = $this_attachment_info['attachment_defense']; //ceil($this_robot->robot_defense * ($this_ability->ability_recovery / 100));
      $this_robot->trigger_recovery($this_robot, $this_ability, $defense_recovery_amount);
      
      // Attach this ability attachment to the robot using it
      $this_attachment_info['attachment_defense'] = $this_ability->ability_results['this_amount'];
      $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
      $this_robot->update_session();
      
      // DEBUG
      //$this_battle->events_create(false, false, 'DEBUG', '<div>'.preg_replace('/\s+/', ' ', htmlentities(print_r($this_attachment_info, true), ENT_QUOTES, 'UTF-8', true)).'</div>');
      
      // Update this ability to allow target selection
      //$this_ability->ability_target = 'select';
      //$this_ability->update_session();

    }
    // Else if the ability flag was set, leaf shield is thrown and defense is lowered by 30%
    else {
      
      // Collect the attachment from the robot to back up its info
      $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
      // Remove this ability attachment to the robot using it
      unset($this_robot->robot_attachments[$this_attachment_token]);
      $this_robot->update_session();
      
      // Target the opposing robot
      $this_ability->target_options_update(array(
        'frame' => 'throw',
        'success' => array($this_impact_frame, 175, 15, 10, $this_ability->print_ability_name().' throws the '.$this_object_name.'!')
        ));
      $this_robot->trigger_target($target_robot, $this_ability);
  
      // Inflict damage on the opposing robot
      $this_ability->damage_options_update(array(
        'kind' => 'energy',
        'kickback' => array(20, 0, 0),
        'success' => array($this_impact_frame, -125, 5, 10, 'The '.$this_object_name.' crashed into the target!'),
        'failure' => array($this_impact_frame, -125, 5, -10, 'The '.$this_object_name.' missed the target&hellip;')
        ));
      $this_ability->recovery_options_update(array(
        'kind' => 'energy',
        'frame' => 'taunt',
        'kickback' => array(0, 0, 0),
        'success' => array($this_impact_frame, -125, 5, 10, 'The '.$this_object_name.' crashed into the target!'),
        'failure' => array($this_impact_frame, -125, 5, -10, 'The '.$this_object_name.' missed the target&hellip;')
        ));
      $energy_damage_amount = $this_ability->ability_damage;
      $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
      
      // DEBUG
      //$this_battle->events_create(false, false, 'DEBUG', '<div>'.preg_replace('/\s+/', ' ', htmlentities(print_r($this_attachment_info, true), ENT_QUOTES, 'UTF-8', true)).'</div>');
      
      // Decrease this robot's defense stat
      $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
      $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
      $defense_damage_amount = $this_attachment_info['attachment_defense']; //ceil($this_robot->robot_defense * ($this_ability->ability_recovery / 100));
      $trigger_options = array('apply_modifiers' => false);
      $this_robot->trigger_damage($this_robot, $this_ability, $defense_damage_amount, true, $trigger_options);
      
      // Update this ability to disable target selection
      //$this_ability->ability_target = 'auto';
      //$this_ability->update_session();
      
    }
    
    // Either way, update this ability's settings to prevent recovery
    $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
    $this_ability->update_session();

    // Return true on success
    return true;

  },
  'ability_function_onload' => function($objects){
    
    // Extract all objects into the current scope
    extract($objects);
    
    // Define this ability's attachment token
    $this_attachment_token = 'ability_'.$this_ability->ability_token;
    
    // If the current field has a type, apply it to this ability
    if (!empty($this_field->field_type) && $this_field->field_type != $this_ability->ability_type){
      $this_ability->ability_type2 = $this_field->field_type;
      $this_ability->update_session();
    } else {
      $this_ability->ability_type2 = '';
      $this_ability->update_session();
    }
    
    // If this ability is being used by a robot with the same core type AND it's already summoned, allow targetting
    if ((!empty($this_robot->robot_core) && $this_robot->robot_core == $this_ability->ability_type)
      && isset($this_robot->robot_attachments[$this_attachment_token])
      ){
      
      // Update this ability's targetting setting
      $this_ability->ability_target = 'select_target';
      $this_ability->update_session();

    }
    // Else if the ability attachment is not there, change the target back to auto
    else {
      
      // Update this ability's targetting setting
      $this_ability->ability_target = 'auto';
      $this_ability->update_session();
      
    }
    
    // Define this abilities internal index if not already created
    if (empty($this_ability->values['this_sprite_index'])){
      // Define the sprite sheets and the stages they contain
      $this_sprite_index = array();
      // Sheet ONE
      $this_sprite_index['field'] = array(1, 0, 1, 'plain block');
      $this_sprite_index['intro-field'] = array(1, 2, 3, 'piece of fence');
      //$this_sprite_index['light-laboratory'] = array(1, 4, 5, 'heavy metal block');
      //$this_sprite_index['wily-castle'] = array(1, 4, 5, 'heavy metal block');
      //$this_sprite_index['cossack-citadel'] = array(1, 4, 5, 'heavy metal block');
      $this_sprite_index['final-destination'] = array(1, 6, 7, 'shiny metal block');
      $this_sprite_index['final-destination-2'] = array(1, 6, 7, 'shiny metal block');
      $this_sprite_index['final-destination-3'] = array(1, 6, 7, 'shiny metal block');
      $this_sprite_index['prototype-complete'] = array(1, 8, 9, 'rocky boulder');
      // Sheet TWO
      $this_sprite_index['mountain-mines'] = array(2, 0, 1, 'heavy boulder');
      $this_sprite_index['arctic-jungle'] = array(2, 2, 3, 'frozen pillar');
      $this_sprite_index['steel-mill'] = array(2, 4, 5, 'heated pillar');
      $this_sprite_index['electrical-tower'] = array(2, 6, 7, 'charged pillar');
      $this_sprite_index['abandoned-warehouse'] = array(2, 8, 9, 'concrete block');
      // Sheet THREE
      $this_sprite_index['oil-wells'] = array(3, 0, 1, 'bucket blockade');
      $this_sprite_index['clock-citadel'] = array(3, 2, 3, 'emerald pillar');
      $this_sprite_index['orb-city'] = array(3, 4, 5, 'explosive pillar');
      $this_sprite_index['pipe-station'] = array(3, 6, 7, 'bundle of pipebombs');
      $this_sprite_index['atomic-furnace'] = array(3, 8, 9, 'heated pillar');
      // Sheet FOUR
      $this_sprite_index['industrial-facility'] = array(4, 0, 1, 'titanium block');
      $this_sprite_index['underground-laboratory'] = array(4, 2, 3, 'smooth platform');
      $this_sprite_index['preserved-forest'] = array(4, 4, 5, 'wooden platform');
      $this_sprite_index['photon-collider'] = array(4, 6, 7, 'crystal pillar');
      $this_sprite_index['waterfall-institute'] = array(4, 8, 9, 'moss-covered platform');
      // Sheet FIVE
      $this_sprite_index['sky-ridge'] = array(5, 0, 1, 'windy pillar');
      $this_sprite_index['mineral-quarry'] = array(5, 2, 3, 'mineral pillar');
      $this_sprite_index['lighting-control'] = array(5, 4, 5, 'charged platform');
      $this_sprite_index['robosaur-boneyard'] = array(5, 6, 7, 'boney pillar');
      $this_sprite_index['space-station'] = array(5, 8, 9, 'crystal blockade');
      // Sheet SIX
      $this_sprite_index['submerged-armory'] = array(6, 0, 1, 'iron blockade');
      $this_sprite_index['egyptian-excavation'] = array(6, 2, 3, 'ancient stone');
      $this_sprite_index['rusty-scrapheap'] = array(6, 4, 5, 'rusty scrapheap');
      $this_sprite_index['rainy-sewers'] = array(6, 6, 7, 'slippery pillar');
      $this_sprite_index['construction-site'] = array(6, 8, 9, 'block platform');
      // Sheet SEVEN
      $this_sprite_index['magnetic-generator'] = array(7, 0, 1, 'large battery');
      $this_sprite_index['power-plant'] = array(7, 2, 3, 'charged platform');
      $this_sprite_index['reflection-chamber'] = array(7, 4, 5, 'pulsing platform');
      $this_sprite_index['rocky-plateau'] = array(7, 6, 7, 'large beam');
      $this_sprite_index['septic-system'] = array(7, 8, 9, 'purifying unit');
      // Sheet EIGHT
      $this_sprite_index['serpent-column'] = array(8, 0, 1, 'serpentine column');
      $this_sprite_index['spinning-greenhouse'] = array(8, 2, 3, 'compact greenhouse');
      $this_sprite_index['wily-castle'] = array(8, 4, 5, 'heavy metal block');
      $this_sprite_index['light-laboratory'] = array(8, 6, 7, 'heavy metal block');
      $this_sprite_index['cossack-citadel'] = array(8, 8, 9, 'heavy metal block');
      
      // Update the session
      $this_ability->values['this_sprite_index'] = $this_sprite_index;
      $this_ability->update_session();
    }
    
    // Define the sprite sheet and animation defaults
    $this_field_token = $this_battle->battle_field->field_background;
    $this_sprite_sheet = 1;
    
    // If the field token has a place in the index, update values
    if (isset($this_sprite_index[$this_field_token])){
      $this_sprite_sheet = $this_sprite_index[$this_field_token][0];
    }
    
    // If the ability flag had already been set, reduce the weapon energy to zero
    if (isset($this_robot->robot_attachments[$this_attachment_token])){ $this_ability->ability_energy = 0; }
    // Otherwise, return the weapon energy back to default
    else { $this_ability->ability_energy = $this_ability->ability_base_energy; }
      
    // Update the ability's image in the session
    $this_ability->ability_image = 'super-arm'.($this_sprite_sheet > 1 ? '-'.$this_sprite_sheet : '');
    $this_ability->update_session();
    
    // Return true on success
    return true;
      
    }
  );
?>