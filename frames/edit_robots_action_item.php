<?

// ROBOT ACTIONS : CHANGE ITEM

// Include the necessary database files
require(MMRPG_CONFIG_ROOTDIR.'database/types.php');
require(MMRPG_CONFIG_ROOTDIR.'database/items.php');
require(MMRPG_CONFIG_ROOTDIR.'database/abilities.php');

// Make sure the battle items array has been set before continuing
if (!isset($_SESSION[$session_token]['values']['battle_items'])){
    $_SESSION[$session_token]['values']['battle_items'] = array();
}

// Collect the item variables from the request header, if they exist
$temp_player = !empty($_REQUEST['player']) ? $_REQUEST['player'] : '';
$temp_robot = !empty($_REQUEST['robot']) ? $_REQUEST['robot'] : '';
$temp_item = !empty($_REQUEST['item']) ? $_REQUEST['item'] : '';

// If key variables are not provided, kill the script in error
if (empty($temp_player) || empty($temp_robot)){ die('error|request-error|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// Collect the current robot favourites for this user
$temp_player_info = $allowed_edit_data[$temp_player];
$temp_robot_info = $allowed_edit_data[$temp_player]['player_robots'][$temp_robot];

// If player or robot info was not found, kill the script in error
if (empty($temp_player_info) || empty($temp_robot_info)){ die('error|request-notfound|'.preg_replace('/\s+/', ' ', print_r($_REQUEST, true))); }

// If the robot is already holding an item, remove previous and add back to inventory
$temp_item_current = mmrpg_prototype_get_robot_battle_item($temp_player, $temp_robot);
if (!empty($temp_item_current)){
    mmrpg_prototype_inc_battle_item_count($temp_item_current, 1);
    mmrpg_prototype_unset_robot_battle_item($temp_player, $temp_robot);
    // If the item we added to the inventory was a shard, we may need to generate a new core
    if (strstr($temp_item_current, '-shard')){
        //error_log('returned '.$temp_item_current.' to inventory');
        $type_token = str_replace('-shard', '', $temp_item_current);
        $shard_token = $type_token.'-shard';
        $core_token = $type_token.'-core';
        $num_shards = mmrpg_prototype_get_battle_item_count($shard_token);
        $num_cores = mmrpg_prototype_get_battle_item_count($core_token);
        //error_log('$num_'.$type_token.'_shards = '.$num_shards);
        //error_log('$num_'.$type_token.'cores = '.$num_cores);
        while ($num_shards >= MMRPG_SETTINGS_SHARDS_MAXQUANTITY){
            //error_log('create new '.$type_token.' core from '.$type_token.' shards...');
            mmrpg_prototype_dec_battle_item_count($shard_token, MMRPG_SETTINGS_SHARDS_MAXQUANTITY);
            mmrpg_prototype_inc_battle_item_count($core_token, 1);
            $num_shards = mmrpg_prototype_get_battle_item_count($shard_token);
            $num_cores = mmrpg_prototype_get_battle_item_count($core_token);
            //error_log('$num_'.$type_token.'shards = '.$num_shards);
            //error_log('$num_'.$type_token.'cores = '.$num_cores);
        }
    }
}

// If the new hold item was not empty, remove from inventory and attach to robot
if (!empty($temp_item)){
    mmrpg_prototype_dec_battle_item_count($temp_item, 1);
    mmrpg_prototype_set_robot_battle_item($temp_player, $temp_robot, $temp_item);
}

// Now that we have a new item attached, we should re-evaluate compatibile abilities
$robot_ability_settings = !empty($_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities']) ? $_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] : array();
$player_ability_rewards = !empty($_SESSION[$session_token]['values']['battle_abilities']) ? $_SESSION[$session_token]['values']['battle_abilities']: array('buster-shot' => array('ability_token' => 'buster-shot'));
$allowed_ability_ids = array();
if (!empty($player_ability_rewards)){
    foreach ($player_ability_rewards AS $key => $ability_token){
        if (empty($ability_token)){ continue; }
        elseif ($ability_token == '*'){ continue; }
        elseif ($ability_token == 'ability'){ continue; }
        elseif (!isset($mmrpg_database_abilities[$ability_token])){ continue; }
        elseif (!rpg_robot::has_ability_compatibility($temp_robot_info['robot_token'], $ability_token, $temp_item)){
            if (isset($robot_ability_settings[$ability_token])){ unset($robot_ability_settings[$ability_token]); }
            continue;
        }
        $ability_id = $mmrpg_database_abilities[$ability_token]['ability_id'];
        $allowed_ability_ids[] = $ability_id;
    }
}
$allowed_ability_ids = implode(',', $allowed_ability_ids);
if (empty($robot_ability_settings)){ $robot_ability_settings['buster-shot'] = array('buster-shot' => array('ability_token' => 'buster-shot')); }
$_SESSION[$session_token]['values']['battle_settings'][$temp_player]['player_robots'][$temp_robot]['robot_abilities'] = $robot_ability_settings;

// Regardless of what happened before, update this robot's item in the session and save
rpg_game::save_session();
exit('success|item-updated|'.$temp_item.'|'.$allowed_ability_ids);
//exit('success|item-updated|'.$temp_item);

?>