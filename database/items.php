<?

// ITEM DATABASE

// Define the index of hidden items to not appear in the database
$hidden_database_items = array();
/*
$hidden_database_items = array_merge($hidden_database_items, array(
    'heart', //'star',
    'empty-core', 'empty-shard', 'empty-star',
    'energy-core', 'energy-shard', 'energy-star',
    'attack-core', 'attack-shard', 'attack-star',
    'defense-core', 'defense-shard', 'defense-star',
    'speed-core', 'speed-shard', 'speed-star',
    'none-star', 'copy-star'
    ));
*/
$hidden_database_items_count = !empty($hidden_database_items) ? count($hidden_database_items) : 0;

// Collect the item database files from the cache or manually
$cache_token = md5('database/items/website');
$cached_index = rpg_object::load_cached_index('database.items', $cache_token);
if (!empty($cached_index)){

    // Collect the cached data for items, item count, and item numbers
    $mmrpg_database_items = $cached_index['mmrpg_database_items'];
    $mmrpg_database_items_count = $cached_index['mmrpg_database_items_count'];
    $mmrpg_database_items_numbers = $cached_index['mmrpg_database_items_numbers'];
    unset($cached_index);

} else {

    // Collect the database items
    $item_fields = rpg_item::get_index_fields(true, 'items');
    $mmrpg_database_items = $db->get_array_list("SELECT
        {$item_fields},
        groups.group_token AS item_group,
        tokens.token_order AS item_order
        FROM mmrpg_index_items AS items
        LEFT JOIN mmrpg_index_items_groups_tokens AS tokens ON tokens.item_token = items.item_token
        LEFT JOIN mmrpg_index_items_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = items.item_class
        WHERE items.item_id <> 0
        AND items.item_class <> 'system'
        AND items.item_flag_published = 1
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'item_token');

    // Count the database items in total (without filters)
    $mmrpg_database_items_count = $db->get_value("SELECT
        COUNT(items.item_id) AS item_count
        FROM mmrpg_index_items AS items
        WHERE items.item_id <> 0
        AND items.item_class <> 'system'
        AND items.item_flag_published = 1
        AND items.item_flag_hidden = 0
        ;", 'item_count');

    // Select an ordered list of all items and then assign row numbers to them
    $mmrpg_database_items_numbers = $db->get_array_list("SELECT
        items.item_token,
        0 AS item_key
        FROM mmrpg_index_items AS items
        LEFT JOIN mmrpg_index_items_groups_tokens AS tokens ON tokens.item_token = items.item_token
        LEFT JOIN mmrpg_index_items_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = items.item_class
        WHERE items.item_id <> 0
        AND items.item_class <> 'system'
        AND items.item_flag_published = 1
        ORDER BY
        groups.group_order ASC,
        tokens.token_order ASC
        ;", 'item_token');
    $item_key = 1;
    foreach ($mmrpg_database_items_numbers AS $token => $info){
        $mmrpg_database_items_numbers[$token]['item_key'] = $item_key++;
    }

    // Remove unallowed items from the database, and increment counters
    if (!empty($mmrpg_database_items)){
        foreach ($mmrpg_database_items AS $temp_token => $temp_info){

            // Send this data through the item index parser
            $temp_info = rpg_item::parse_index_info($temp_info);

            // Collect this item's key in the index
            $temp_info['item_key'] = $mmrpg_database_items_numbers[$temp_token]['item_key'];

            // Ensure this item's image exists, else default to the placeholder
            $temp_image_token = isset($temp_info['item_image']) ? $temp_info['item_image'] : $temp_token;
            if ($temp_info['item_flag_complete']){ $mmrpg_database_items[$temp_token]['item_image'] = $temp_image_token; }
            else { $mmrpg_database_items[$temp_token]['item_image'] = 'item'; }

            // Update the main database array with the changes
            $mmrpg_database_items[$temp_token] = $temp_info;

        }
    }


    // Save the cached data for items, item count, and item numbers
    rpg_object::save_cached_index('database.items', $cache_token, array(
        'mmrpg_database_items' => $mmrpg_database_items,
        'mmrpg_database_items_count' => $mmrpg_database_items_count,
        'mmrpg_database_items_numbers' => $mmrpg_database_items_numbers
        ));
}

// If a filter function has been provided for this context, run it now
if (isset($filter_mmrpg_database_items)
    && is_callable($filter_mmrpg_database_items)){
    $mmrpg_database_items = array_filter($mmrpg_database_items, $filter_mmrpg_database_items);
}

// If an update function gas been provided for this context, run it now
if (isset($update_mmrpg_database_items)
    && is_callable($update_mmrpg_database_items)){
    $mmrpg_database_items = array_map($update_mmrpg_database_items, $mmrpg_database_items);
}

// Loop through and remove hidden items unless they're being viewed explicitly
if (!empty($mmrpg_database_items)){
    foreach ($mmrpg_database_items AS $temp_token => $temp_info){
        if (!empty($temp_info['item_flag_hidden'])
            && $temp_info['item_token'] !== $this_current_token){
            unset($mmrpg_database_items[$temp_token]);
        }
    }
}

// Loop through the database and generate the links for these items
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_items_links = '';
$mmrpg_database_items_links_index = array();
$mmrpg_database_items_links_counter = 0;
$mmrpg_database_items_count_complete = 0;

// Loop through the results and generate the links for these items
if (!empty($mmrpg_database_items)){
    foreach ($mmrpg_database_items AS $item_key => $item_info){
        if (!isset($first_item_key)){ $first_item_key = $item_key; }

        //if (!preg_match('/^star-/i', $item_info['item_token'])){ continue; }

        // Do not show incomplete items in the link list
        $show_in_link_list = true;
        if (!$item_info['item_flag_complete'] && $item_info['item_token'] !== $this_current_token){ $show_in_link_list = false; }

        // If a type filter has been applied to the item page
        $temp_item_types = array();
        if (!empty($item_info['item_type'])){ $temp_item_types[] = $item_info['item_type']; }
        if (!empty($item_info['item_type2'])){ $temp_item_types[] = $item_info['item_type2']; }
        if (preg_match('/^super-(pellet|capsule)$/i', $item_info['item_token'])){ $temp_item_types[] = 'multi'; }
        if (empty($temp_item_types)){ $temp_item_types[] = 'none'; }
        if (isset($this_current_filter) && !in_array($this_current_filter, $temp_item_types)){ $key_counter++; continue; }

        // If this is the first in a new group
        $game_code = !empty($item_info['item_group']) ? $item_info['item_group'] : (!empty($item_info['item_game']) ? $item_info['item_game'] : 'MMRPG');
        if ($show_in_link_list && $game_code != $last_game_code){
            if (!empty($mmrpg_database_items_links)){ $mmrpg_database_items_links .= '</div>'; }
            $mmrpg_database_items_links .= '<div class="float link group" data-game="'.$game_code.'">';
            $last_game_code = $game_code;
        }

        // Collect the item sprite dimensions
        $item_token = $item_info['item_token'];
        $item_flag_complete = !empty($item_info['item_flag_complete']) ? true : false;
        $item_image_size = !empty($item_info['item_image_size']) ? $item_info['item_image_size'] : 40;
        $item_image_size_text = $item_image_size.'x'.$item_image_size;
        $item_image_token = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_token;
        $item_image_incomplete = $item_image_token == 'item' ? true : false;
        $item_is_active = !empty($this_current_token) && $this_current_token == $item_info['item_token'] ? true : false;
        $item_title_text = $item_info['item_name'];
        $item_image_path = 'images/items/'.$item_image_token.'/icon_right_'.$item_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $item_type_class = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
        if ($item_type_class != 'none' && !empty($item_info['item_type2'])){ $item_type_class .= '_'.$item_info['item_type2']; }
        elseif ($item_type_class == 'none' && !empty($item_info['item_type2'])){ $item_type_class = $item_info['item_type2'];  }

        // Start the output buffer and collect the generated markup
        ob_start();
        ?>
        <div title="<?= $item_title_text ?>" data-token="<?= $item_info['item_token'] ?>" class="float left link type <?= ($item_image_incomplete ? 'inactive ' : '').($item_type_class) ?>">
            <a class="sprite item link mugshot size<?= $item_image_size.($item_key == $first_item_key ? ' current' : '') ?>" href="<?= 'database/items/'.preg_replace('/^/i', '', $item_info['item_token']) ?>/" rel="<?= $item_image_incomplete ? 'nofollow' : 'follow' ?>">
                <?php if($item_image_token != 'item'): ?>
                    <img src="<?= $item_image_path ?>" width="<?= $item_image_size ?>" height="<?= $item_image_size ?>" alt="<?= $item_title_text ?>" />
                <?php else: ?>
                    <span><?= $item_info['item_name'] ?></span>
                <?php endif; ?>
            </a>
        </div>
        <?php
        if ($item_flag_complete){ $mmrpg_database_items_count_complete++; }
        $temp_markup = ob_get_clean();
        $mmrpg_database_items_links_index[$item_key] = $temp_markup;
        if ($show_in_link_list){ $mmrpg_database_items_links .= $temp_markup; }
        $mmrpg_database_items_links_counter++;
        $key_counter++;

    }
}

// End the groups, however many there were
$mmrpg_database_items_links .= '</div>';

?>