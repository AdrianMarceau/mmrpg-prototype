<?php
/*
 * INDEX PAGE : FIELDS
 */


// Collect the field ID from the request header
$field_id = isset($_GET['num']) && is_numeric($_GET['num']) ? (int)($_GET['num']) : false;

// Collect field info based on the ID if available
$field_fields = rpg_field::get_index_fields(true);
if (!empty($field_id)){ $field_info = $db->get_array("SELECT {$field_fields} FROM mmrpg_index_fields WHERE field_id = {$field_id};"); }
elseif ($field_id === 0){ $field_info = $db->get_array("SELECT {$field_fields} FROM mmrpg_index_fields WHERE field_token = 'field';"); }
else { $field_info = array(); }

// Parse the field info if it was collected
if (!empty($field_info)){ $field_info = rpg_field::parse_index_info($field_info); }

//echo('$field_info['.$field_id.'] = <pre>'.print_r($field_info, true).'</pre><hr />');
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
require_once(MMRPG_CONFIG_ROOTDIR.'pages/page.admin_fields_actions.php');

// -- PLAYER EDITOR -- //

// If a field ID was provided, we should show the editor
if ($field_id !== false && !empty($field_info)){

    // Define the SEO variables for this page
    $this_seo_title = $field_info['field_name'].' | Field Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin field editor for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/">Field Index</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/<?= $field_id ?>/"><?= 'ID '.$field_info['field_id'] ?> : <?= $field_info['field_name'] ?></a>
    </h2>
    <div class="subbody">
        <p class="text">Use the <strong>Field Editor</strong> to update the details and stats of playable characters in the Mega Man RPG Prototype.  Please be careful and don't forget to save your work.</p>
    </div>

    <form class="editor fields" action="admin/<?= $this_current_sub ?>/<?= $field_id ?>/" method="post" enctype="multipart/form-data">
        <div class="subbody">

            <div class="section inputs">
                <div class="field field_field_id">
                    <label class="label">Field ID</label>
                    <input class="text" type="text" name="field_id" value="<?= $field_info['field_id'] ?>" disabled="disabled" />
                    <input class="hidden" type="hidden" name="field_id" value="<?= $field_info['field_id'] ?>" />
                </div>
                <div class="field field_field_token">
                    <label class="label">Field Token</label>
                    <input class="text" type="text" name="field_token" value="<?= $field_info['field_token'] ?>" maxlength="64" />
                </div>
                <div class="field field_field_name">
                    <label class="label">Field Name</label>
                    <input class="text" type="text" name="field_name" value="<?= $field_info['field_name'] ?>" maxlength="64" />
                </div>
                <div class="field field_field_type">
                    <label class="label">Field Type</label>
                    <select class="select" name="field_type">
                        <?= str_replace('value="'.$field_info['field_type'].'"', 'value="'.$field_info['field_type'].'" selected="selected"', $stat_index_options) ?>
                    </select>
                </div>
            </div>

            <div class="section actions">
                <div class="buttons">
                    <input type="submit" class="save" name="save" value="Save Changes" />
                    <input type="button" class="delete" name="delete" value="Delete Field" />
                </div>
            </div>

        </div>
    </form>

    <div class="subbody">
        <pre>$field_info = <?= print_r($field_info, true) ?></pre>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

// -- PLAYER INDEX -- //

// Else if no field ID was provided, we should show the index
else {

    // Define the SEO variables for this page
    $this_seo_title = 'Field Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin field editor index for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Define the robot header columns for looping through
    // token => array(name, class, width, directions)
    $table_columns['id'] = array('ID', 'id', '75', 'asc/desc');
    $table_columns['name'] = array('Field Name', 'name', '', 'asc/desc');
    $table_columns['group'] = array('Group', 'group', '', 'asc/desc');
    $table_columns['type'] = array('Type', 'types', '100', 'asc/desc');
    $table_columns['hidden'] = array('Hidden', 'flags hidden', '90', 'desc/asc');
    $table_columns['complete'] = array('Complete', 'flags complete', '90', 'desc/asc');
    $table_columns['published'] = array('Published', 'flags published', '100', 'desc/asc');
    $table_columns['actions'] = array('', 'actions', '120', '');

    // Collect a list of all users in the database
    $field_fields = rpg_field::get_index_fields(true);
    $field_query = "SELECT {$field_fields} FROM mmrpg_index_fields WHERE field_id <> 0 AND field_token <> 'field' ORDER BY {$query_sort};";
    $field_index = $db->get_array_list($field_query, 'field_id');
    $field_count = !empty($field_index) ? count($field_index) : 0;

    // Collect a list of completed field sprite tokens
    $random_sprite = $db->get_value("SELECT field_image FROM mmrpg_index_fields WHERE field_image <> 'field' AND field_flag_complete = 1 ORDER BY RAND() LIMIT 1;", 'field_image');

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/fields/">Field Index</a>
        <span class="count">( <?= $field_count != 1 ? $field_count.' Fields' : '1 Field' ?> )</span>
    </h2>

    <div class="section full">
        <div class="subbody">
            <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/fields/<?= $random_sprite ?>/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); margin: -8px 0 -6px; background-size: 45px 45px; background-position: center center;"></div></div>
            <p class="text">Use the field index below to search and filter through all the playable characters in the game and either view or edit using the provided links.</p>
        </div>
    </div>

    <div class="section full">
        <div class="subbody">
            <table data-table="fields" class="full">
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
                                $link = 'admin/fields/sort='.$token.'-';
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
                    // Loop through collected fields and list their details
                    if (!empty($field_index)){
                        foreach ($field_index AS $field_id => $field_info){
                            // Parse the field info before displaying it
                            $field_info = rpg_field::parse_index_info($field_info);
                            // Collect the display fields from the array
                            $field_token = $field_info['field_token'];
                            $field_name = $field_info['field_name'];
                            $field_group = '<span class="token">'.$field_info['field_group'].'</span>';
                            $field_type1 = !empty($field_info['field_type']) && !empty($type_index[$field_info['field_type']]) ? $type_index[$field_info['field_type']] : $type_index['none'];
                            $type_string = '<span class="type '.$field_type1['type_token'].'">'.$field_type1['type_name'].'</span>';
                            $edit_link = 'admin/fields/'.$field_id.'/';
                            $view_link = 'database/fields/'.$field_token.'/';
                            $complete = $field_info['field_flag_complete'] ? true : false;
                            $published = $field_info['field_flag_published'] ? true : false;
                            $hidden = $field_info['field_flag_hidden'] ? true : false;
                            // Print out the field info as a table row
                            ?>
                            <tr class="object<?= !$published ? ' unpublished' : '' ?><?= !$complete ? ' incomplete' : '' ?>">
                                <td class="id"><?= $field_id ?></td>
                                <td class="name"><a class="link_inline" href="<?= $edit_link ?>" title="Edit <?= $field_name ?>" target="_editField<?= $field_id ?>"><?= $field_name ?></a></td>
                                <td class="group"><?= $field_group ?></td>
                                <td class="types"><?= $type_string ?></td>
                                <td class="flags hidden"><?= $hidden ? '<span class="true">&#9745;</span>' : '<span class="false">&#9744;</span>' ?></td>
                                <td class="flags complete"><?= $complete ? '<span class="true">&#x2713;</span>' : '<span class="false">&#x2717;</span>' ?></td>
                                <td class="flags published"><?= $published ? '<span class="type nature">Yes</span>' : '<span class="type flame">No</span>' ?></td>
                                <td class="actions">
                                    <a class="link_inline edit" href="<?= $edit_link ?>" target="_editField<?= $field_id ?>">Edit</a>
                                    <? if ($published): ?>
                                        <a class="link_inline view" href="<?= $view_link ?>" target="_viewField<?= $field_token ?>">View</a>
                                    <? endif; ?>
                                </td>
                            </tr>
                            <?
                        }
                    }
                    // Otherwise if field index is empty show an empty table
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
        <p class="text right"><?= $field_count != 1 ? $field_count.' Fields' : '1 Field' ?> Total</p>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

?>