<?

// Generate the markup for the action target panel
ob_start();

    // Define and start the order counter
    $button_order = 1;

    // Display container for the main actions
    ?><div class="main_actions main_actions_hastitle"><span class="main_actions_title">Select {thisPanel} Target</span><?

    // Ensure there are robots to display
    if (!empty($target_player->player_robots)){

        // Count the total number of robots
        $num_robots = count($target_player->player_robots);

        // Collect the target robot options and sort them
        $target_player_robots = $target_player->player_robots;
        usort($target_player_robots, 'rpg_prototype::sort_robots_for_battle_menu');

        // Loop through each robot and display its target button
        foreach ($target_player_robots AS $robot_key => $target_robotinfo){

            // Ensure this is an actual switch in the index
            if (!empty($target_robotinfo['robot_token'])){

                // Create the robot object using available data then use it to generate and print the buton
                $robot = rpg_game::get_robot($this_battle, $target_player, $target_robotinfo);
                $robot_button_markup = rpg_prototype::print_robot_for_battle_menu($robot, 'target_target', $robot_key, $button_order);
                echo $robot_button_markup;

            }
        }

        // If there were less than 8 robots, fill in the empty spaces
        if ($num_robots < 8){
            for ($i = $num_robots; $i < 8; $i++){
                // Display an empty button placeholder
                ?><a class="button action_target button_disabled block_<?= $i + 1 ?>" type="button">&nbsp;</a><?
            }
        }

    }

    // End the main action container tag
    ?></div><?

    // Display the back button by default
    ?><div class="sub_actions"><a data-order="<?=$button_order?>" class="button action_back" type="button" data-panel="ability"><label>Back</label></a></div><?

    // Increment the order counter
    $button_order++;

$actions_markup['target_target'] = trim(ob_get_clean());
$actions_markup['target_target'] = preg_replace('#\s+#', ' ', $actions_markup['target_target']);
?>