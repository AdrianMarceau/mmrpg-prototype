<?
// GEMINI CLONE
$ability = array(
    'ability_name' => 'Gemini Clone',
    'ability_token' => 'gemini-clone',
    'ability_game' => 'MM03',
    //'ability_group' => 'MM03/Weapons/019',
    'ability_group' => 'MM03/Weapons/019T1',
    'ability_description' => 'The user generates a clone of itself that fights alongside for up to three turns! The clone automatically mirrors the user\'s compatible abilities as long as there\'s enough weapon energy to perform them again!',
    'ability_type' => 'crystal',
    'ability_energy' => 4,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define the base attachment duration
        $base_attachment_duration = 3;

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'ability_image' => 'ability',
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => 1 + $base_attachment_duration, // +1 for summoning turn
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(0, -9999, -9999, -9999,  'The '.$this_ability->print_name().' faded away&hellip;'),
                'failure' => array(0, -9999, -9999, -9999, 'The '.$this_ability->print_name().' faded away&hellip;')
                )
            );

        // Check to see if a clone already existed
        $clone_existed = false;
        if (isset($this_robot->robot_attachments[$this_attachment_token])){
            $this_robot->unset_attachment($this_attachment_token);
            $clone_existed = true;
        }

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, -5, 70, 20,
                $this_robot->print_name().' used the '.$this_ability->print_name().' technique... '
                )
            ));
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

        // Create the attachment object for this ability
        $this_attachment = rpg_game::get_ability($this_battle, $this_player, $this_robot, $this_attachment_info);
        $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(2, 15, 65, 20,
                $this_robot->print_name().' created a '.($clone_existed ? 'new ' : '').'clone of '.$this_robot->get_pronoun('reflexive').'!'
                )
            ));
        $this_robot->set_flag('gemini-clone_is_using_ability', true);
        $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));
        $this_robot->unset_flag('gemini-clone_is_using_ability');


        // Return true on success
        return true;

    }
    );
?>