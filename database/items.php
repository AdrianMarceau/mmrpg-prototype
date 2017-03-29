<?

// ITEM DATABASE

// Define the index of hidden items to not appear in the database
$hidden_database_items = array();
$hidden_database_items_count = !empty($hidden_database_items) ? count($hidden_database_items) : 0;

// Define the hidden item query condition
$temp_condition = '';
$temp_condition .= "AND items.item_class <> 'system' ";
if (!empty($hidden_database_items)){
    $temp_tokens = array();
    foreach ($hidden_database_items AS $token){ $temp_tokens[] = "'".$token."'"; }
    $temp_condition .= 'AND items.item_token NOT IN ('.implode(',', $temp_tokens).') ';
}
// If additional database filters were provided
$temp_condition_unfiltered = $temp_condition;
if (isset($mmrpg_database_items_filter)){
    if (!preg_match('/^\s?(AND|OR)\s+/i', $mmrpg_database_items_filter)){ $temp_condition .= 'AND ';  }
    $temp_condition .= $mmrpg_database_items_filter;
}

// If we're specifically on the items page, collect records
$temp_joins = '';
$temp_item_fields = '';
if ($this_current_sub == 'items'){
    $temp_item_fields .= ",
        (CASE WHEN uitems1.item_unlocked IS NOT NULL THEN uitems1.item_unlocked ELSE 0 END) AS item_record_users_unlocked,
        (CASE WHEN uitems2.item_equipped IS NOT NULL THEN uitems2.item_equipped ELSE 0 END) AS item_record_robots_equipped,
        (CASE WHEN uitems3.item_total IS NOT NULL THEN uitems3.item_total ELSE 0 END) AS item_record_current_total
        ";
    $temp_joins .= "
            LEFT JOIN (SELECT
                uitems.item_token,
                COUNT(*) AS item_unlocked
                FROM mmrpg_users_items AS uitems
                GROUP BY uitems.item_token
                ) AS uitems1 ON uitems1.item_token = items.item_token
            LEFT JOIN (SELECT
                urobots.robot_item AS item_token,
                COUNT(*) AS item_equipped
                FROM mmrpg_users_robots AS urobots
                GROUP BY urobots.robot_item
                ) AS uitems2 ON uitems2.item_token = items.item_token
            LEFT JOIN (SELECT
                uitems.item_token,
                SUM(item_quantity) AS item_total
                FROM mmrpg_users_items AS uitems
                GROUP BY uitems.item_token
                ) AS uitems3 ON uitems3.item_token = items.item_token
            ";
}

// Collect the relevant index fields
$item_fields = rpg_item::get_index_fields(true, 'items');

// Define the query for the global items index
$temp_items_index_query = "SELECT
    {$item_fields}
    {$temp_item_fields}
    FROM mmrpg_index_items AS items
    {$temp_joins}
    WHERE
    items.item_flag_published = 1
    AND (items.item_flag_hidden = 0 OR items.item_token = '{$this_current_token}')
    {$temp_condition}
    ORDER BY
    items.item_order ASC
    ;";

// Define the query for the global items count
$temp_items_count_query = "SELECT
    COUNT(items.item_id) AS item_count
    FROM mmrpg_index_items AS items
    WHERE
    items.item_flag_published = 1
    AND items.item_flag_hidden = 0
    {$temp_condition_unfiltered}
    ;";

// Define the query for the global item numbers
$temp_items_numbers_query = "SELECT
    items.item_token,
    (@item_row_number:=@item_row_number + 1) AS item_key
    FROM mmrpg_index_items AS items
    WHERE
    items.item_flag_published = 1
    {$temp_condition_unfiltered}
    ORDER BY
    items.item_flag_hidden ASC,
    items.item_order ASC
    ;";

// Execute generated queries and collect return value
$db->query("SET @item_row_number = 0;");
$mmrpg_database_items = $db->get_array_list($temp_items_index_query, 'item_token');
$mmrpg_database_items_count = $db->get_value($temp_items_count_query, 'item_count');
$mmrpg_database_items_numbers = $db->get_array_list($temp_items_numbers_query, 'item_token');

// DEBUG
//echo('<pre>$temp_items_index_query = '.print_r($temp_items_index_query, true).'</pre>');
//echo('<pre>$temp_items_count_query = '.print_r($temp_items_count_query, true).'</pre>');
//echo('<pre>$temp_items_numbers_query = '.print_r($temp_items_numbers_query, true).'</pre>');
//echo('<pre>$mmrpg_database_items = '.print_r($mmrpg_database_items, true).'</pre>');
//echo('<pre>$mmrpg_database_items_count = '.print_r($mmrpg_database_items_count, true).'</pre>');
//echo('<pre>$mmrpg_database_items_numbers = '.print_r($mmrpg_database_items_numbers, true).'</pre>');
//exit();

// Remove unallowed items from the database, and increment counters
foreach ($mmrpg_database_items AS $temp_token => $temp_info){

    // Define first item token if not set
    if (!isset($first_item_token)){ $first_item_token = $temp_token; }

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

// Loop through the database and generate the links for these items
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_items_links = '';
$mmrpg_database_items_links_index = array();
$mmrpg_database_items_links_counter = 0;
$mmrpg_database_items_count_complete = 0;

// Loop through the results and generate the links for these items
foreach ($mmrpg_database_items AS $item_key => $item_info){

    //if (!preg_match('/^star-/i', $item_info['item_token'])){ continue; }

    // If a type filter has been applied to the item page
    $temp_item_types = array();
    if (!empty($item_info['item_type'])){ $temp_item_types[] = $item_info['item_type']; }
    if (!empty($item_info['item_type2'])){ $temp_item_types[] = $item_info['item_type2']; }
    if (preg_match('/^(red|blue|green|purple)-score-ball$/i', $item_info['item_token'])){ $temp_item_types[] = 'bonus'; }
    elseif (preg_match('/^super-(pellet|capsule)$/i', $item_info['item_token'])){ $temp_item_types[] = 'multi'; }
    if (empty($temp_item_types)){ $temp_item_types[] = 'none'; }
    if (isset($this_current_filter) && !in_array($this_current_filter, $temp_item_types)){ $key_counter++; continue; }

    // If this is the first in a new group
    $game_code = !empty($item_info['item_group']) ? $item_info['item_group'] : (!empty($item_info['item_game']) ? $item_info['item_game'] : 'MMRPG');
    if ($game_code != $last_game_code){
        if ($key_counter != 0){ $mmrpg_database_items_links .= '</div>'; }
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
    $item_title_text = $item_info['item_name']; //.' | '.$item_info['item_game'].' | '.$item_info['item_group'];;
    //$item_image_path = 'images/items/'.$item_image_token.'/icon_right_'.$item_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
    if (false && !empty($item_info['item_description'])){
        $temp_description = $item_info['item_description'];
        $temp_description = str_replace('{DAMAGE}', $item_info['item_damage'], $temp_description);
        $temp_description = str_replace('{DAMAGE2}', $item_info['item_damage2'], $temp_description);
        $temp_description = str_replace('{RECOVERY}', $item_info['item_recovery'], $temp_description);
        $temp_description = str_replace('{RECOVERY2}', $item_info['item_recovery2'], $temp_description);
        $item_title_text .= '|| [['.$temp_description.']]';
    }
    $item_image_path = 'images/items/'.$item_image_token.'/icon_right_'.$item_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
    $item_type_class = !empty($item_info['item_type']) ? $item_info['item_type'] : 'none';
    if ($item_type_class != 'none' && !empty($item_info['item_type2'])){ $item_type_class .= '_'.$item_info['item_type2']; }
    elseif ($item_type_class == 'none' && !empty($item_info['item_type2'])){ $item_type_class = $item_info['item_type2'];  }

    // Start the output buffer and collect the generated markup
    ob_start();
    ?>
    <div title="<?= $item_title_text ?>" data-token="<?= $item_info['item_token'] ?>" class="float left link type <?= ($item_image_incomplete ? 'inactive ' : '').($item_type_class) ?>">
        <a class="sprite item link mugshot size<?= $item_image_size.($item_key == $first_item_token ? ' current' : '') ?>" href="<?= 'database/items/'.preg_replace('/^/i', '', $item_info['item_token']) ?>/" rel="<?= $item_image_incomplete ? 'nofollow' : 'follow' ?>">
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
    $mmrpg_database_items_links .= $temp_markup;
    $mmrpg_database_items_links_counter++;
    $key_counter++;

}

// End the groups, however many there were
$mmrpg_database_items_links .= '</div>';

?>