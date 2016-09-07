<?
// SUPER ARM
$ability = array(
    'ability_name' => 'Super Arm',
    'ability_token' => 'super-arm',
    'ability_game' => 'MM01',
    'ability_group' => 'MM01/Weapons/004',
    'ability_image_sheets' => 8,
    'ability_description' => 'The user creates a blockade using the surrounding environment to bolster shields and reduce damage by {RECOVERY2}%!  The blockade can also be thrown at the target for massive damage!',
    'ability_type' => 'impact',
    'ability_type2' => 'shield',
    'ability_energy' => 8,
    'ability_damage' => 30,
    'ability_recovery2' => 60,
    'ability_recovery_percent2' => true,
    'ability_accuracy' => 98,
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
        $this_gender = preg_match('/^(roll|disco|rhythm|[-a-z]+woman)$/i', $this_robot->robot_token) ? 'female' : 'male';
        $this_effect_multiplier = 1 - ($this_ability->ability_recovery2 / 100);
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability->ability_token,
            'ability_image' => 'super-arm'.($this_sprite_sheet > 1 ? '-'.$this_sprite_sheet : ''),
            'attachment_sticky' => true,
            'attachment_damage_input_breaker' => $this_effect_multiplier,
            'attachment_weaknesses' => $this_object_weaknesses,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'kickback' => array(0, 0, 0),
                'success' => array($this_target_frame, 105, 0, 10,
                    $this_robot->print_name().' shielded '.($this_gender == 'female' ? 'herself' : 'himself').' with the '.$this_object_name.'!<br /> '.
                    $this_robot->print_name().'\'s defenses were bolstered!'
                    ),
                'failure' => array($this_target_frame, 105, 0, 10,
                    $this_robot->print_name().' shielded '.($this_gender == 'female' ? 'herself' : 'himself').' with the '.$this_object_name.'!<br /> '.
                    $this_robot->print_name().'\'s defenses were bolstered!'
                    )
                ),
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type2' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'kickback' => array(0, 0, 0),
                'success' => array(-1, -10, 0, -10,
                    'The '.$this_ability->print_name().'\'s '.$this_object_name.' was destroyed!<br /> '.
                    $this_robot->print_name().' is no longer protected...'
                    ),
                'failure' => array(-1, -10, 0, -10,
                    'The '.$this_ability->print_name().'\'s '.$this_object_name.' was destroyed!<br /> '.
                    $this_robot->print_name().' is no longer protected...'
                    )
                ),
            'ability_frame' => $this_target_frame,
            'ability_frame_animate' => array($this_target_frame),
            'ability_frame_offset' => array('x' => 105, 'y' => 0, 'z' => -10)
            );

        // Update the ability's image in the session
        $this_ability->ability_image = $this_attachment_info['ability_image'];
        $this_ability->update_session();

        // If the ability flag was not set, super arm resists damage
        if (!isset($this_robot->robot_attachments[$this_attachment_token])){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array($this_target_frame, 105, 0, 10, 'The '.$this_ability->print_name().' created '.(preg_match('/^(a|e|i|o|u)/i', $this_object_name) ? 'an '.$this_object_name : 'a '.$this_object_name).'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

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
                'success' => array($this_impact_frame, 175, 15, 10, $this_ability->print_name().' throws the '.$this_object_name.'!')
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

            // Decrease this robot's defense stat
            $this_ability->target_options_update($this_attachment_info['attachment_destroy']);
            $this_robot->trigger_target($this_robot, $this_ability);

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