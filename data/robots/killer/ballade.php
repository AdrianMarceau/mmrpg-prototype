<?
// BALLADE
$robot = array(
    'robot_number' => 'MKN-003',
    'robot_class' => 'boss',
    'robot_game' => 'MM20',
    'robot_group' => 'MMRPG2',
    'robot_name' => 'Ballade',
    'robot_token' => 'ballade',
    'robot_image_sheets' => 2,
    'robot_image_editor' => 18,
    'robot_image_size' => 80,
    'robot_core' => 'explode',
    'robot_description' => 'Elite Megaman Hunter',
    'robot_description2' => 'Ballade was made to be the last in the Mega Man Killer unit and are very powerful, having great speed and power. They are equipped with the Ballade Cracker, a very explosive bomb capable of taking out multiple robots. They also have a second form, boosting their abilities even more. They only fight strong robots and believe themselves to be the strongest. They follow orders better than the Punk unit but are still very reckless. Although they believe themselves to be the strongest, they have great reason to see it that way.',
    'robot_field' => 'final-destination',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('cutter', 'earth'),
    'robot_resistances' => array('shadow'),
    'robot_abilities' => array(
        'ballade-cracker',
        'buster-shot',
        'attack-boost', 'attack-break', 'attack-swap', 'attack-mode',
        'defense-boost', 'defense-break', 'defense-swap', 'defense-mode',
        'speed-boost', 'speed-break', 'speed-swap', 'speed-mode',
        'energy-boost', 'energy-break', 'energy-swap', 'energy-mode',
        'field-support', 'mecha-support',
        'light-buster', 'wily-buster', 'cossack-buster'
        ),
    'robot_rewards' => array(
        'abilities' => array(
                array('level' => 0, 'token' => 'ballade-cracker')
            )
        ),
    'robot_quotes' => array(
        'battle_start' => '',
        'battle_taunt' => '',
        'battle_victory' => '',
        'battle_defeat' => ''
        ),
    'robot_function_ondamage' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If this robot is already disabled, do nothing
        if ($this_robot->get_status() == 'disabled'){ return true; }

        // Collect this robot's current energy to see if form change necessary
        $energy_current = $this_robot->get_energy();
        $energy_base = $this_robot->get_base_energy();

        // Change form if this robot's life energy is below a certain threshold
        $form_change_flag = $this_robot->get_flag('robot_form_changed');
        if (empty($form_change_flag) && $energy_current <= ($energy_base * 0.50)){

            // Push this robot into the summon pose first
            $this_robot->set_frame('summon');
            $this_battle->events_create($this_robot, false, '', '');

            // Update this robot's image to be the alt version
            $base_image_token = $this_robot->get_base_image();
            if (empty($base_image_token)){ $base_image_token = $this_robot->get_token(); }
            $new_image_token = $base_image_token.'_alt';
            $this_robot->set_image($new_image_token);

            // Print a message about the change in form
            $event_header = $this_robot->get_name().'\'s Form Change';
            $event_body = $this_robot->print_name().' triggered a form change!<br /> ';
            $event_body .= $this_robot->print_name().'\'s stats were boosted! ';
            $event_options = array();
            $this_robot->set_frame('victory');
            $this_battle->events_create($this_robot, false, $event_header, $event_body, $event_options);
            $this_robot->set_frame('base');

            // Create a temporary ability object
            $this_ability = rpg_game::get_ability($this_battle, $this_player, $this_robot, array('ability_token' => 'ability'));

            // Increase the target robot's energy stat
            $energy_current = $this_robot->get_energy();
            $energy_base = $this_robot->get_base_energy();
            if ($energy_current < $energy_base){
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'percent' => true,
                    'modifiers' => false,
                    'frame' => 'taunt',
                    'success' => array(0, -2, 0, -10, $this_robot->print_name().'&#39;s energy was restored!'),
                    'failure' => array(9, -2, 0, -10, $this_robot->print_name().'&#39;s energy was not affected&hellip;')
                    ));
                $energy_recovery_amount = $energy_base - $energy_current;
                $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);
            }

            // Loop through and double all other stats and increase
            $stats = array('attack', 'defense', 'speed');
            $systems = array('weapons were', 'shields were', 'mobility was');
            $frames = array('shoot', 'defend', 'slide');
            foreach ($stats AS $key => $stat){
                $system = $systems[$key];
                $frame = $frames[$key];
                $stat_base = $this_robot->get_info('robot_base_'.$stat);
                $this_ability->recovery_options_update(array(
                    'kind' => $stat,
                    'percent' => true,
                    'modifiers' => false,
                    'frame' => $frame,
                    'success' => array(0, -2, 0, -10, $this_robot->print_name().'&#39;s '.$system.' boosted!'),
                    'failure' => array(9, -2, 0, -10, $this_robot->print_name().'&#39;s '.$system.' not affected&hellip;')
                    ));
                $this_robot->trigger_recovery($this_robot, $this_ability, $stat_base);
            }

            // Set the flag so we don't display this message again
            $this_robot->set_flag('robot_form_changed', true);

        }

        // Return true on success
        return true;

        }
    );
?>