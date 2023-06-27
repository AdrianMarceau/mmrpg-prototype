<?
// Generate the markup for the action switch panel
ob_start();

    // Check if the switch should be disabled
    $this_switch_disabled = false;
    if ($this_robot->robot_status != 'disabled' && $this_robot->robot_position == 'active'){
        $this_robot_attachments = $this_robot->get_current_attachments();
        if (!empty($this_robot_attachments)){
            foreach ($this_robot_attachments AS $attachment_token => $attachment_info){
                if (isset($attachment_info['attachment_switch_disabled']) && $attachment_info['attachment_switch_disabled'] == 1){ $this_switch_disabled = true; }
            }
        }
    }

    // Check to see if a switch SHOULD be allowed
    $this_switch_required = false;
    if ($this_robot->robot_status == 'disabled' && $this_robot->robot_position == 'active'){
        $this_switch_required = true;
    }

    // Define and start the order counter
    $button_order = 1;

    // Display container for the main actions
    ?><div class="main_actions main_actions_hastitle"><span class="main_actions_title" style="<?= !empty($this_player->flags['switch_used_this_turn']) ? 'text-decoration: line-through;' : '' ?>">Select Switch Target <?= $this_switch_disabled ? '(Disabled)' : '' ?></span><?

    // Ensure there are robots to display
    if (!empty($this_player->player_robots)){

        // Count the total number of robots
        $num_robots = count($this_player->player_robots);

        // Collect the target robot options and sort them
        $switch_player_robots = $this_player->player_robots;
        usort($switch_player_robots, 'rpg_prototype::sort_robots_for_battle_menu');

        // Loop through each robot and display its switch button
        foreach ($switch_player_robots AS $robot_key => $switch_robotinfo){

            // Ensure this is an actual switch in the index
            if (!empty($switch_robotinfo['robot_token'])){

                // Create the robot object using available data then use it to generate and print the buton
                $robot = rpg_game::get_robot($this_battle, $target_player, $switch_robotinfo);
                $robot_button_markup = rpg_prototype::print_robot_for_battle_menu($robot, 'switch', $robot_key, $button_order, array(
                    'this_switch_disabled' => $this_switch_disabled,
                    'this_switch_required' => $this_switch_required
                    ));
                echo $robot_button_markup;

            }

        }

        // If there were less than 8 robots, fill in the empty spaces
        if ($num_robots < 8){
            for ($i = $num_robots; $i < 8; $i++){
                // Display an empty button placeholder
                ?><a class="button action_switch button_disabled block_<?= $i + 1 ?>" type="button">&nbsp;</a><?
            }
        }

    }

    // End the main action container tag
    ?></div><?

    // Display the back button by default
    $allow_back_button = $this_robot->robot_position == 'active' && $this_robot->robot_status != 'disabled' ? true : false;
    ?><div class="sub_actions"><a <?= $allow_back_button ? 'data-order="'.$button_order.'"' : '' ?> class="button action_back <?= !$allow_back_button ? 'button_disabled' : '' ?>" type="button" <?= $allow_back_button ? 'data-panel="battle"' : '' ?>><?= $allow_back_button ? '<label>Back</label>' : '&nbsp;' ?></a></div><?

    // Increment the order counter
    $button_order++;

$actions_markup['switch'] = trim(ob_get_clean());
$actions_markup['switch'] = preg_replace('#\s+#', ' ', $actions_markup['switch']);
?>