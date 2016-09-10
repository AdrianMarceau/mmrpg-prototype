<?

// -- ITEM BATTLE ACTION -- //

// Create the temporary item object for this player's robot
$temp_iteminfo = array();
list($temp_iteminfo['item_id'], $temp_iteminfo['item_token']) = explode('_', $this_action_token); //array('item_token' => $this_action_token);
$temp_thisitem = new rpg_item($this_battle, $this_player, $this_robot, $temp_iteminfo);

// Queue up an this robot's action first, because it's faster
$this_battle->actions_append($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_action_token);

// Now execute the stored actions (and any created in the process of executing them!)
$this_battle->actions_execute();

// Update the sesions I guess
$this_robot->update_session();
$target_robot->update_session();

// If this item was an ITEM, decrease it's quantity in the player's session
if (preg_match('/^([0-9]+)_/i', $this_action_token)){
    // Decrease the quantity of this item from the player's inventory
    list($temp_item_id, $temp_item_token) = explode('_', $this_action_token);
    if (!empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){
        $temp_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_item_token];
        $temp_quantity -= 1;
        if ($temp_quantity < 0){ $temp_quantity = 0; }
        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = $temp_quantity;
    }
}

// Create a flag on this player, preventing multiple items per turn
$this_player->flags['item_used_this_turn'] = true;
$this_player->update_session();

// Now execute the stored actions (and any created in the process of executing them!)
$this_battle->actions_execute();

// -- END OF TURN ACTIONS -- //

// If the battle has not concluded, check the robot attachments
if ($target_robot->robot_status == 'disabled'){

    // If this the player's last robot
    if ($this_player->counters['robots_active'] == 0){
        // Trigger the battle complete event
        $this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot, '', '');
    }
    // Else if the target player's on their last robot
    elseif ($target_player->counters['robots_active'] == 0){
        // Trigger the battle complete event
        $this_battle->battle_complete_trigger($this_player, $this_robot, $target_player, $target_robot, '', '');
    }

}



?>