<?

// Require the top file for all admin scripts
require_once('common/top.php');

function context_echo($string){
    debug_echo($string);
    if (php_sapi_name() === 'cli'){ ob_flush(); }
}

// Collect the request limit and user ID if provided
$request_limit = 1;
$request_offset = 0;
$request_user_id = false;
if (!empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) && $_REQUEST['limit'] > 0){ $request_limit = intval($_REQUEST['limit']); }
if (!empty($_REQUEST['offset']) && is_numeric($_REQUEST['offset']) && $_REQUEST['offset'] > 0){ $request_offset = intval($_REQUEST['offset']); }
if (!empty($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id']) && $_REQUEST['user_id'] > 0){ $request_user_id = intval($_REQUEST['user_id']); }

context_echo('==========================');
context_echo('Refresh Battle Points 2k19');
context_echo('==========================');
context_echo('Limit: '.$request_limit.' | Offset: '.$request_offset);
if (!empty($request_user_id)){ context_echo('User ID: '.$request_user_id); }
context_echo('--------------------------');
context_echo('');

// Collect a list of active Users to run this refresh script on
$this_where_query = '';
if (!empty($request_user_id)){ $this_where_query .= "AND users.user_id = {$request_user_id} "; }
$active_user_ids = $db->get_array_list("SELECT
    saves.user_id,
    saves.save_cache_date,
    leaderboard.board_points,
    users.user_name_clean
    FROM mmrpg_saves AS saves
    LEFT JOIN mmrpg_leaderboard AS leaderboard ON leaderboard.user_id = saves.user_id
    LEFT JOIN mmrpg_users AS users ON users.user_id = saves.user_id
    WHERE leaderboard.board_points > 0 {$this_where_query}
    ORDER BY
    users.user_last_login ASC,
    leaderboard.board_points DESC
    ;", 'user_id');
$num_active_user_ids = count($active_user_ids);
context_echo('Found '.$num_active_user_ids.' Active Users');
context_echo('');

// Collect reference indexes for players, robots, abilities, items, and fields
$mmrpg_index_players = rpg_player::get_index();
$mmrpg_index_robots = rpg_robot::get_index();
$mmrpg_index_abilities = rpg_ability::get_index();
$mmrpg_index_items = rpg_item::get_index();
$mmrpg_index_fields = rpg_field::get_index();
$mmrpg_index_players_tokens = array_keys($mmrpg_index_players);
$mmrpg_index_robots_tokens = array_keys($mmrpg_index_robots);
$mmrpg_index_abilities_tokens = array_keys($mmrpg_index_abilities);
$mmrpg_index_items_tokens = array_keys($mmrpg_index_items);
$mmrpg_index_fields_tokens = array_keys($mmrpg_index_fields);

// Collect a detailed points breakdown for this user given their ID
$num_users_processed = 0;
$actual_num_users_processed = 0;
context_echo('Looping through active users...');
$points_index = array();
foreach ($active_user_ids AS $user_id => $user_data){
    $num_users_processed++;
    $actual_num_users_processed = $num_users_processed - $request_offset;
    $num_of_total = $num_users_processed.' of '.$num_active_user_ids;
    $percent_of_total = round((($num_users_processed / $num_active_user_ids) * 100), 2).'%';
    context_echo('------');
    context_echo('Processing user ID '.$user_id.' ('.$num_of_total.') ['.$percent_of_total.'] ... ');
    if (!empty($request_offset)
        && $request_offset > ($num_users_processed - 1)){
        context_echo('... skipped! ');
        continue;
    }
    $old_battle_points = $user_data['board_points'];
    $new_battle_points = mmrpg_prototype_calculate_battle_points_2k19($user_data['user_id'], $points_index);
    $diff_points = $new_battle_points - $old_battle_points;
    if (!empty($new_battle_points)){
        $db->update('mmrpg_leaderboard',
            array(
                'board_points' => $new_battle_points,
                'board_robots_count' => count($points_index['robots_unlocked']),
                'board_items' => count($points_index['items_unlocked']),
                'board_abilities' => count($points_index['abilities_unlocked'])
                ),
            array('user_id' => $user_data['user_id'])
            );
        }
    context_echo('Old Points: '.number_format($old_battle_points, 0, '.', ',').
        ' | New Points: '.number_format($new_battle_points, 0, '.', ',').
        ' | Diff: '.($diff_points > 0 ? '+' : '').number_format($diff_points, 0, '.', ','));
    if ($actual_num_users_processed >= $request_limit){ break; }
}
context_echo('------');
context_echo('');

context_echo('...Done!');

// Print the success message with the returned output
if (php_sapi_name() !== 'cli'){
    exit_action('success|Updated battle points for all active users!');
}

?>