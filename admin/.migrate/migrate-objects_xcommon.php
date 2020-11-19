<?

// Collect an index of contributors for ID translations
$contributor_fields = rpg_user::get_contributor_index_fields(true, 'contributors');
$contributor_index = $db->get_array_list("SELECT
    {$contributor_fields},
    users.user_id,
    users.user_name_clean
    FROM mmrpg_users_contributors AS contributors
    LEFT JOIN mmrpg_users AS users ON users.contributor_id = contributors.contributor_id
    ORDER BY contributor_id ASC
    ;", 'contributor_id');

// Create a cross-reference for translating user to contributor IDs
$user_ids_to_contributor_ids = array();
$user_ids_to_contributor_usernames = array();
foreach ($contributor_index AS $key => $data){
    $user_ids_to_contributor_ids[$data['user_id']] = $data['contributor_id'];
    $user_ids_to_contributor_usernames[$data['user_id']] = $data['user_name_clean'];
}

// Include the game migration data here so we don't have to later
require_once(MMRPG_CONFIG_ROOTDIR.'admin/.migrate/migrate-objects_games.php');

// Collect a music index from the CDN so we can translate legacy paths to new ones
$raw_music_index = rpg_game::get_music_index();
$music_data_index = array();
$music_paths_legacy_to_new = array();
if (!empty($raw_music_index['index'])){
    foreach ($raw_music_index['index'] AS $key => $info){
        $legacy_path = $info['legacy_music_path'];
        $new_path = $info['music_path'];
        $music_data_index[$new_path] = $info;
        if (!empty($legacy_path) && !empty($new_path)){
            $music_paths_legacy_to_new[$legacy_path] = $new_path;
        }
    }
}

?>