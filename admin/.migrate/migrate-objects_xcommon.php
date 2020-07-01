<?

// Collect an index of contributors for ID translations
$contributor_fields = rpg_user::get_contributor_index_fields(true, 'contributors');
$contributor_index = $db->get_array_list("SELECT
    {$contributor_fields},
    users.user_id
    FROM mmrpg_users_contributors AS contributors
    LEFT JOIN mmrpg_users AS users ON users.contributor_id = contributors.contributor_id
    ORDER BY contributor_id ASC
    ;", 'contributor_id');

// Create a cross-reference for translating user to contributor IDs
$user_ids_to_contributor_ids = array();
$contributor_ids_to_user_ids = array();
foreach ($contributor_index AS $key => $data){
    $user_ids_to_contributor_ids[$data['user_id']] = $data['contributor_id'];
    $user_ids_to_contributor_ids[$data['contributor_id']] = $data['user_id'];
}



?>