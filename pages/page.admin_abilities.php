<?php
/*
 * INDEX PAGE : ABILITIES
 */


// Collect the ability ID from the request header
$ability_id = isset($_GET['num']) && is_numeric($_GET['num']) ? (int)($_GET['num']) : false;

// Collect ability info based on the ID if available
$ability_fields = rpg_ability::get_index_fields(true);
if (!empty($ability_id)){ $ability_info = $db->get_array("SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE ability_id = {$ability_id};"); }
elseif ($ability_id === 0){ $ability_info = $db->get_array("SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE ability_token = 'ability';"); }
else { $ability_info = array(); }

// Parse the ability info if it was collected
if (!empty($ability_info)){ $ability_info = rpg_ability::parse_index_info($ability_info); }

//echo('$ability_info['.$ability_id.'] = <pre>'.print_r($ability_info, true).'</pre><hr />');
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
require_once(MMRPG_CONFIG_ROOTDIR.'pages/page.admin_abilities_actions.php');

// -- PLAYER EDITOR -- //

// If a ability ID was provided, we should show the editor
if ($ability_id !== false && !empty($ability_info)){

    // Define the SEO variables for this page
    $this_seo_title = $ability_info['ability_name'].' | Ability Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin ability editor for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/">Ability Index</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/<?= $ability_id ?>/"><?= 'ID '.$ability_info['ability_id'] ?> : <?= $ability_info['ability_name'] ?></a>
    </h2>
    <div class="subbody">
        <p class="text">Use the <strong>Ability Editor</strong> to update the details and stats of playable characters in the Mega Man RPG Prototype.  Please be careful and don't forget to save your work.</p>
    </div>

    <form class="editor abilities" action="admin/<?= $this_current_sub ?>/<?= $ability_id ?>/" method="post" enctype="multipart/form-data">
        <div class="subbody">

            <div class="section inputs">
                <div class="field field_ability_id">
                    <label class="label">Ability ID</label>
                    <input class="text" type="text" name="ability_id" value="<?= $ability_info['ability_id'] ?>" disabled="disabled" />
                    <input class="hidden" type="hidden" name="ability_id" value="<?= $ability_info['ability_id'] ?>" />
                </div>
                <div class="field field_ability_token">
                    <label class="label">Ability Token</label>
                    <input class="text" type="text" name="ability_token" value="<?= $ability_info['ability_token'] ?>" maxlength="64" />
                </div>
                <div class="field field_ability_name">
                    <label class="label">Ability Name</label>
                    <input class="text" type="text" name="ability_name" value="<?= $ability_info['ability_name'] ?>" maxlength="64" />
                </div>
                <div class="field field_ability_type">
                    <label class="label">Ability Type</label>
                    <select class="select" name="ability_type">
                        <?= str_replace('value="'.$ability_info['ability_type'].'"', 'value="'.$ability_info['ability_type'].'" selected="selected"', $stat_index_options) ?>
                    </select>
                </div>
            </div>

            <div class="section actions">
                <div class="buttons">
                    <input type="submit" class="save" name="save" value="Save Changes" />
                    <input type="button" class="delete" name="delete" value="Delete Ability" />
                </div>
            </div>

        </div>
    </form>

    <div class="subbody">
        <pre>$ability_info = <?= print_r($ability_info, true) ?></pre>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

// -- PLAYER INDEX -- //

// Else if no ability ID was provided, we should show the index
else {

    // Define the SEO variables for this page
    $this_seo_title = 'Ability Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin ability editor index for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Define the robot header columns for looping through
    // token => array(name, class, width, directions)
    $table_columns['id'] = array('ID', 'id', '75', 'asc/desc');
    $table_columns['name'] = array('Ability Name', 'name', '', 'asc/desc');
    $table_columns['group'] = array('Group', 'group', '', 'asc/desc');
    $table_columns['type'] = array('Type', 'types', '100', 'asc/desc');
    $table_columns['hidden'] = array('Hidden', 'flags hidden', '90', 'desc/asc');
    $table_columns['complete'] = array('Complete', 'flags complete', '90', 'desc/asc');
    $table_columns['published'] = array('Published', 'flags published', '100', 'desc/asc');
    $table_columns['actions'] = array('', 'actions', '120', '');

    // Collect a list of all users in the database
    $ability_fields = rpg_ability::get_index_fields(true);
    $ability_query = "SELECT {$ability_fields} FROM mmrpg_index_abilities WHERE ability_id <> 0 AND ability_token <> 'ability' ORDER BY {$query_sort};";
    $ability_index = $db->get_array_list($ability_query, 'ability_id');
    $ability_count = !empty($ability_index) ? count($ability_index) : 0;

    // Collect a list of completed ability sprite tokens
    $random_sprite = $db->get_value("SELECT ability_image FROM mmrpg_index_abilities WHERE ability_image <> 'ability' AND ability_class = 'master' AND ability_image_size = 40 AND ability_flag_complete = 1 ORDER BY RAND() LIMIT 1;", 'ability_image');

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/abilities/">Ability Index</a>
        <span class="count">( <?= $ability_count != 1 ? $ability_count.' Abilities' : '1 Ability' ?> )</span>
    </h2>

    <div class="section full">
        <div class="subbody">
            <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_command" style="background-image: url(images/abilities/<?= $random_sprite ?>/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); background-position: 0 16px;"></div></div>
            <p class="text">Use the ability index below to search and filter through all the playable characters in the game and either view or edit using the provided links.</p>
        </div>
    </div>

    <div class="section full">
        <div class="subbody">
            <table data-table="abilities" class="full">
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
                                $link = 'admin/abilities/sort='.$token.'-';
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
                    // Loop through collected abilities and list their details
                    if (!empty($ability_index)){
                        foreach ($ability_index AS $ability_id => $ability_info){
                            // Parse the ability info before displaying it
                            $ability_info = rpg_ability::parse_index_info($ability_info);
                            // Collect the display fields from the array
                            $ability_token = $ability_info['ability_token'];
                            $ability_name = $ability_info['ability_name'];
                            $ability_group = '<span class="token">'.$ability_info['ability_group'].'</span>';
                            $ability_type1 = !empty($ability_info['ability_type']) && !empty($type_index[$ability_info['ability_type']]) ? $type_index[$ability_info['ability_type']] : $type_index['none'];
                            $ability_type2 = !empty($ability_info['ability_type2']) && !empty($type_index[$ability_info['ability_type2']]) ? $type_index[$ability_info['ability_type2']] : false;
                            if (!empty($ability_type2)){ $type_string = '<span class="type '.$ability_type1['type_token'].'_'.$ability_type2['type_token'].'">'.$ability_type1['type_name'].' / '.$ability_type2['type_name'].'</span>'; }
                            else { $type_string = '<span class="type '.$ability_type1['type_token'].'">'.$ability_type1['type_name'].'</span>'; }
                            $edit_link = 'admin/abilities/'.$ability_id.'/';
                            $view_link = 'database/abilities/'.$ability_token.'/';
                            $complete = $ability_info['ability_flag_complete'] ? true : false;
                            $published = $ability_info['ability_flag_published'] ? true : false;
                            $hidden = $ability_info['ability_flag_hidden'] ? true : false;
                            // Print out the ability info as a table row
                            ?>
                            <tr class="object<?= !$published ? ' unpublished' : '' ?><?= !$complete ? ' incomplete' : '' ?>">
                                <td class="id"><?= $ability_id ?></td>
                                <td class="name"><a class="link_inline" href="<?= $edit_link ?>" title="Edit <?= $ability_name ?>" target="_editAbility<?= $ability_id ?>"><?= $ability_name ?></a></td>
                                <td class="group"><?= $ability_group ?></td>
                                <td class="types"><?= $type_string ?></td>
                                <td class="flags hidden"><?= $hidden ? '<span class="true">&#9745;</span>' : '<span class="false">&#9744;</span>' ?></td>
                                <td class="flags complete"><?= $complete ? '<span class="true">&#x2713;</span>' : '<span class="false">&#x2717;</span>' ?></td>
                                <td class="flags published"><?= $published ? '<span class="type nature">Yes</span>' : '<span class="type flame">No</span>' ?></td>
                                <td class="actions">
                                    <a class="link_inline edit" href="<?= $edit_link ?>" target="_editAbility<?= $ability_id ?>">Edit</a>
                                    <? if ($published): ?>
                                        <a class="link_inline view" href="<?= $view_link ?>" target="_viewAbility<?= $ability_token ?>">View</a>
                                    <? endif; ?>
                                </td>
                            </tr>
                            <?
                        }
                    }
                    // Otherwise if ability index is empty show an empty table
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
        <p class="text right"><?= $ability_count != 1 ? $ability_count.' Abilities' : '1 Ability' ?> Total</p>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

?>