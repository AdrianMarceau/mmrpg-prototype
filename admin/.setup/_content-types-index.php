<?

// Define an index array to hold all the content type details
$content_types_index = array();

// Populate the content type index with names, paths, git URLs, etc.
$content_types_index['types'] = array(
    'token' => 'type',
    'xtoken' => 'types',
    'object_class' => 'rpg_type',
    'database_table' => 'mmrpg_index_types',
    'content_path' => 'content/types/',
    'github_repo' => array(
        'name' => 'mmrpg-prototype_types',
        'http' => 'https://github.com/AdrianMarceau/mmrpg-prototype_types.git',
        'ssh' => 'git@github.com:AdrianMarceau/mmrpg-prototype_types.git'
        )
    );
$content_types_index['players'] = array(
    'token' => 'player',
    'xtoken' => 'players',
    'object_class' => 'rpg_player',
    'database_table' => 'mmrpg_index_players',
    'content_path' => 'content/players/',
    'github_repo' => array(
        'name' => 'mmrpg-prototype_players',
        'http' => 'https://github.com/AdrianMarceau/mmrpg-prototype_players.git',
        'ssh' => 'git@github.com:AdrianMarceau/mmrpg-prototype_players.git'
        )
    );
$content_types_index['robots'] = array(
    'token' => 'robot',
    'xtoken' => 'robots',
    'object_class' => 'rpg_robot',
    'database_table' => 'mmrpg_index_robots',
    'content_path' => 'content/robots/',
    'github_repo' => array(
        'name' => 'mmrpg-prototype_robots',
        'http' => 'https://github.com/AdrianMarceau/mmrpg-prototype_robots.git',
        'ssh' => 'git@github.com:AdrianMarceau/mmrpg-prototype_robots.git'
        )
    );
$content_types_index['abilities'] = array(
    'token' => 'ability',
    'xtoken' => 'abilities',
    'object_class' => 'rpg_ability',
    'database_table' => 'mmrpg_index_abilities',
    'content_path' => 'content/abilities/',
    'github_repo' => array(
        'name' => 'mmrpg-prototype_abilities',
        'http' => 'https://github.com/AdrianMarceau/mmrpg-prototype_abilities.git',
        'ssh' => 'git@github.com:AdrianMarceau/mmrpg-prototype_abilities.git'
        )
    );
$content_types_index['items'] = array(
    'token' => 'item',
    'xtoken' => 'items',
    'object_class' => 'rpg_item',
    'database_table' => 'mmrpg_index_items',
    'content_path' => 'content/items/',
    'github_repo' => array(
        'name' => 'mmrpg-prototype_items',
        'http' => 'https://github.com/AdrianMarceau/mmrpg-prototype_items.git',
        'ssh' => 'git@github.com:AdrianMarceau/mmrpg-prototype_items.git'
        )
    );
$content_types_index['fields'] = array(
    'token' => 'field',
    'xtoken' => 'fields',
    'object_class' => 'rpg_fields',
    'database_table' => 'mmrpg_index_fields',
    'content_path' => 'content/fields/',
    'github_repo' => array(
        'name' => 'mmrpg-prototype_fields',
        'http' => 'https://github.com/AdrianMarceau/mmrpg-prototype_fields.git',
        'ssh' => 'git@github.com:AdrianMarceau/mmrpg-prototype_fields.git'
        )
    );
$content_types_index['battles'] = array(
    'token' => 'battle',
    'xtoken' => 'battles',
    'object_class' => 'rpg_battle',
    'database_table' => 'mmrpg_index_battles',
    'content_path' => 'content/battles/',
    'github_repo' => array(
        'name' => 'mmrpg-prototype_battles',
        'http' => 'https://github.com/AdrianMarceau/mmrpg-prototype_battles.git',
        'ssh' => 'git@github.com:AdrianMarceau/mmrpg-prototype_battles.git'
        )
    );

?>