<?php
/*
 * INDEX PAGE : ITEMS
 */


// Collect the item ID from the request header
$item_id = isset($_GET['num']) && is_numeric($_GET['num']) ? (int)($_GET['num']) : false;

// Collect item info based on the ID if available
$item_fields = rpg_item::get_index_fields(true);
if (!empty($item_id)){ $item_info = $db->get_array("SELECT {$item_fields} FROM mmrpg_index_items WHERE item_id = {$item_id};"); }
elseif ($item_id === 0){ $item_info = $db->get_array("SELECT {$item_fields} FROM mmrpg_index_items WHERE item_token = 'item';"); }
else { $item_info = array(); }

// Parse the item info if it was collected
if (!empty($item_info)){ $item_info = rpg_item::parse_index_info($item_info); }

//echo('$item_info['.$item_id.'] = <pre>'.print_r($item_info, true).'</pre><hr />');
//exit();

// Collect the type index for display and looping
$type_index = rpg_type::get_index(true);

// Generate form select options for the type index
$type_tokens = array_keys($type_index);
$type_index_options = '';
$type_index_options .= '<option value="">Neutral</option>'.PHP_EOL; // Manually add 'none' up top
foreach ($type_tokens AS $type_token){
    if ($type_token == 'none'){ continue; } // We already added 'none' above
    $type_info = $type_index[$type_token];
    $type_index_options .= '<option value="'.$type_token.'">'.$type_info['type_name'].'</option>'.PHP_EOL;
}


// Require actions file for form processing
require_once(MMRPG_CONFIG_ROOTDIR.'pages/page.admin_items_actions.php');

// -- PLAYER EDITOR -- //

// If a item ID was provided, we should show the editor
if ($item_id !== false && !empty($item_info)){

    // Define the SEO variables for this page
    $this_seo_title = $item_info['item_name'].' | Item Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin item editor for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/">Item Index</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/<?= $item_id ?>/"><?= 'ID '.$item_info['item_id'] ?> : <?= $item_info['item_name'] ?></a>
    </h2>
    <div class="subbody">
        <p class="text">Use the <strong>Item Editor</strong> to update the details and stats of playable characters in the Mega Man RPG Prototype.  Please be careful and don't forget to save your work.</p>
    </div>

    <form class="editor items" action="admin/<?= $this_current_sub ?>/<?= $item_id ?>/" method="post" enctype="multipart/form-data">
        <div class="subbody">

            <div class="section inputs">
                <div class="field field_item_id">
                    <label class="label">Item ID</label>
                    <input class="text" type="text" name="item_id" value="<?= $item_info['item_id'] ?>" disabled="disabled" />
                    <input class="hidden" type="hidden" name="item_id" value="<?= $item_info['item_id'] ?>" />
                </div>
                <div class="field field_item_token">
                    <label class="label">Item Token</label>
                    <input class="text" type="text" name="item_token" value="<?= $item_info['item_token'] ?>" maxlength="64" />
                </div>
                <div class="field field_item_name">
                    <label class="label">Item Name</label>
                    <input class="text" type="text" name="item_name" value="<?= $item_info['item_name'] ?>" maxlength="64" />
                </div>
                <div class="field field_item_type">
                    <label class="label">Item Type</label>
                    <select class="select" name="item_type">
                        <?= str_replace('value="'.$item_info['item_type'].'"', 'value="'.$item_info['item_type'].'" selected="selected"', $stat_index_options) ?>
                    </select>
                </div>
            </div>

            <div class="section actions">
                <div class="buttons">
                    <input type="submit" class="save" name="save" value="Save Changes" />
                    <input type="button" class="delete" name="delete" value="Delete Item" />
                </div>
            </div>

        </div>
    </form>

    <div class="subbody">
        <pre>$item_info = <?= print_r($item_info, true) ?></pre>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

// -- PLAYER INDEX -- //

// Else if no item ID was provided, we should show the index
else {

    // Define the SEO variables for this page
    $this_seo_title = 'Item Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin item editor index for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Define the robot header columns for looping through
    // token => array(name, class, width, directions)
    $table_columns['id'] = array('ID', 'id', '75', 'asc/desc');
    $table_columns['name'] = array('Item Name', 'name', '', 'asc/desc');
    $table_columns['group'] = array('Group', 'group', '', 'asc/desc');
    $table_columns['type'] = array('Type', 'types', '100', 'asc/desc');
    $table_columns['hidden'] = array('Hidden', 'flags hidden', '90', 'desc/asc');
    $table_columns['complete'] = array('Complete', 'flags complete', '90', 'desc/asc');
    $table_columns['published'] = array('Published', 'flags published', '100', 'desc/asc');
    $table_columns['actions'] = array('', 'actions', '120', '');

    // Collect a list of all users in the database
    $item_fields = rpg_item::get_index_fields(true);
    $item_query = "SELECT {$item_fields} FROM mmrpg_index_items WHERE item_id <> 0 AND item_token <> 'item' ORDER BY {$query_sort};";
    $item_index = $db->get_array_list($item_query, 'item_id');
    $item_count = !empty($item_index) ? count($item_index) : 0;

    // Collect a list of completed item sprite tokens
    $random_sprite = $db->get_value("SELECT item_image FROM mmrpg_index_items WHERE item_image <> 'item' AND item_image_size = 40 AND item_flag_complete = 1 ORDER BY RAND() LIMIT 1;", 'item_image');

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/items/">Item Index</a>
        <span class="count">( <?= $item_count != 1 ? $item_count.' Items' : '1 Item' ?> )</span>
    </h2>

    <div class="section full">
        <div class="subbody">
            <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_command" style="background-image: url(images/items/<?= $random_sprite ?>/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div></div>
            <p class="text">Use the item index below to search and filter through all the playable characters in the game and either view or edit using the provided links.</p>
        </div>
    </div>

    <div class="section full">
        <div class="subbody">
            <table data-table="items" class="full">
                <colgroup>
                    <?
                    // Loop through and display column widths
                    foreach ($table_columns AS $token => $info){
                        list($name, $class, $width, $directions) = $info;
                        echo '<col width="'.$width.'" />'.PHP_EOL;
                    }
                    ?>
                </colgroup>
                <thead>
                    <tr class="head">
                        <?
                        // Loop through and display column headers
                        foreach ($table_columns AS $token => $info){
                            list($name, $class, $width, $directions) = $info;
                            if (!empty($name)){
                                $active = $sort_column == $token ? true : false;
                                $directions = explode('/', $directions);
                                $class .= $active ? ' active' : '';
                                $link = 'admin/items/sort='.$token.'-';
                                $link .= ($active && $sort_direction == $directions[0]) ? $directions[1] : $directions[0];
                                echo '<th class="'.$class.'">';
                                    echo '<a class="link_inline" href="'.$link.'">'.$name.'</a>';
                                    if ($active && $sort_direction == 'asc'){ echo ' <sup>&#8595;</sup>'; }
                                    elseif ($active && $sort_direction == 'desc'){ echo ' <sup>&#8593;</sup>'; }
                                echo '</th>'.PHP_EOL;
                            } else {
                                echo '<th class="'.$class.'">&nbsp;</th>'.PHP_EOL;
                            }
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?
                    // Loop through collected items and list their details
                    if (!empty($item_index)){
                        foreach ($item_index AS $item_id => $item_info){
                            // Parse the item info before displaying it
                            $item_info = rpg_item::parse_index_info($item_info);
                            // Collect the display fields from the array
                            $item_token = $item_info['item_token'];
                            $item_name = $item_info['item_name'];
                            $item_group = '<span class="token">'.$item_info['item_group'].'</span>';
                            $item_type1 = !empty($item_info['item_type']) && !empty($type_index[$item_info['item_type']]) ? $type_index[$item_info['item_type']] : $type_index['none'];
                            $type_string = '<span class="type '.$item_type1['type_token'].'">'.$item_type1['type_name'].'</span>';
                            $edit_link = 'admin/items/'.$item_id.'/';
                            $view_link = 'database/items/'.$item_token.'/';
                            $complete = $item_info['item_flag_complete'] ? true : false;
                            $published = $item_info['item_flag_published'] ? true : false;
                            $hidden = $item_info['item_flag_hidden'] ? true : false;
                            // Print out the item info as a table row
                            ?>
                            <tr class="object<?= !$published ? ' unpublished' : '' ?><?= !$complete ? ' incomplete' : '' ?>">
                                <td class="id"><?= $item_id ?></td>
                                <td class="name"><a class="link_inline" href="<?= $edit_link ?>" title="Edit <?= $item_name ?>" target="_editItem<?= $item_id ?>"><?= $item_name ?></a></td>
                                <td class="group"><?= $item_group ?></td>
                                <td class="types"><?= $type_string ?></td>
                                <td class="flags hidden"><?= $hidden ? '<span class="true">&#9745;</span>' : '<span class="false">&#9744;</span>' ?></td>
                                <td class="flags complete"><?= $complete ? '<span class="true">&#x2713;</span>' : '<span class="false">&#x2717;</span>' ?></td>
                                <td class="flags published"><?= $published ? '<span class="type nature">Yes</span>' : '<span class="type flame">No</span>' ?></td>
                                <td class="actions">
                                    <a class="link_inline edit" href="<?= $edit_link ?>" target="_editItem<?= $item_id ?>">Edit</a>
                                    <? if ($published): ?>
                                        <a class="link_inline view" href="<?= $view_link ?>" target="_viewItem<?= $item_token ?>">View</a>
                                    <? endif; ?>
                                </td>
                            </tr>
                            <?
                        }
                    }
                    // Otherwise if item index is empty show an empty table
                    else {
                        // Print an empty table row
                        ?>
                            <tr class="object incomplete">
                                <td class="id">-</td>
                                <td class="name">-</td>
                                <td class="types">-</td>
                                <td class="flags" colspan="3">-</td>
                                <td class="actions">-</td>
                            </tr>
                        <?
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="subbody">
        <p class="text right"><?= $item_count != 1 ? $item_count.' Items' : '1 Item' ?> Total</p>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

?>