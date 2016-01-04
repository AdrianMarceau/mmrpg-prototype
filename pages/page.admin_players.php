<?php
/*
 * INDEX PAGE : PLAYERS
 */


// Collect the player ID from the request header
$player_id = isset($_GET['num']) && is_numeric($_GET['num']) ? (int)($_GET['num']) : false;

// Collect player info based on the ID if available
$player_fields = rpg_player::get_index_fields(true);
if (!empty($player_id)){ $player_info = $db->get_array("SELECT {$player_fields} FROM mmrpg_index_players WHERE player_id = {$player_id};"); }
elseif ($player_id === 0){ $player_info = $db->get_array("SELECT {$player_fields} FROM mmrpg_index_players WHERE player_token = 'player';"); }
else { $player_info = array(); }

// Parse the player info if it was collected
if (!empty($player_info)){ $player_info = rpg_player::parse_index_info($player_info); }

//echo('$player_info['.$player_id.'] = <pre>'.print_r($player_info, true).'</pre><hr />');
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

// Generate form select options for the stat sub-index
$stat_tokens = array('energy', 'weapons', 'attack', 'defense', 'speed');
$stat_index_options = '';
$stat_index_options .= '<option value="">Neutral</option>'.PHP_EOL; // Manually add 'none' up top
foreach ($stat_tokens AS $type_token){
    $type_info = $type_index[$type_token];
    $stat_index_options .= '<option value="'.$type_token.'">'.$type_info['type_name'].'</option>'.PHP_EOL;
}


// Require actions file for form processing
require_once(MMRPG_CONFIG_ROOTDIR.'pages/page.admin_players_actions.php');

// -- PLAYER EDITOR -- //

// If a player ID was provided, we should show the editor
if ($player_id !== false && !empty($player_info)){

    // Define the SEO variables for this page
    $this_seo_title = $player_info['player_name'].' | Player Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin player editor for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/">Player Index</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/<?= $player_id ?>/"><?= 'ID '.$player_info['player_id'] ?> : <?= $player_info['player_name'] ?></a>
    </h2>
    <div class="subbody">
        <p class="text">Use the <strong>Player Editor</strong> to update the details and stats of playable characters in the Mega Man RPG Prototype.  Please be careful and don't forget to save your work.</p>
    </div>

    <form class="editor players" action="admin/<?= $this_current_sub ?>/<?= $player_id ?>/" method="post" enctype="multipart/form-data">
        <div class="subbody">

            <div class="section inputs">
                <div class="field field_player_id">
                    <label class="label">Player ID</label>
                    <input class="text" type="text" name="player_id" value="<?= $player_info['player_id'] ?>" disabled="disabled" />
                    <input class="hidden" type="hidden" name="player_id" value="<?= $player_info['player_id'] ?>" />
                </div>
                <div class="field field_player_token">
                    <label class="label">Player Token</label>
                    <input class="text" type="text" name="player_token" value="<?= $player_info['player_token'] ?>" maxlength="64" />
                </div>
                <div class="field field_player_name">
                    <label class="label">Player Name</label>
                    <input class="text" type="text" name="player_name" value="<?= $player_info['player_name'] ?>" maxlength="64" />
                </div>
                <div class="field field_player_type">
                    <label class="label">Player Type</label>
                    <select class="select" name="player_type">
                        <?= str_replace('value="'.$player_info['player_type'].'"', 'value="'.$player_info['player_type'].'" selected="selected"', $stat_index_options) ?>
                    </select>
                </div>
            </div>

            <div class="section actions">
                <div class="buttons">
                    <input type="submit" class="save" name="save" value="Save Changes" />
                    <input type="button" class="delete" name="delete" value="Delete Player" />
                </div>
            </div>

        </div>
    </form>

    <div class="subbody">
        <pre>$player_info = <?= print_r($player_info, true) ?></pre>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

// -- PLAYER INDEX -- //

// Else if no player ID was provided, we should show the index
else {

    // Define the SEO variables for this page
    $this_seo_title = 'Player Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin player editor index for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Define the robot header columns for looping through
    // token => array(name, class, width, directions)
    $table_columns['id'] = array('ID', 'id', '75', 'asc/desc');
    $table_columns['name'] = array('Player Name', 'name', '', 'asc/desc');
    $table_columns['group'] = array('Group', 'group', '', 'asc/desc');
    $table_columns['type'] = array('Type', 'types', '100', 'asc/desc');
    $table_columns['hidden'] = array('Hidden', 'flags hidden', '90', 'desc/asc');
    $table_columns['complete'] = array('Complete', 'flags complete', '90', 'desc/asc');
    $table_columns['published'] = array('Published', 'flags published', '100', 'desc/asc');
    $table_columns['actions'] = array('', 'actions', '120', '');

    // Collect a list of all users in the database
    $player_fields = rpg_player::get_index_fields(true);
    $player_query = "SELECT {$player_fields} FROM mmrpg_index_players WHERE player_id <> 0 AND player_token <> 'player' ORDER BY {$query_sort};";
    $player_index = $db->get_array_list($player_query, 'player_id');
    $player_count = !empty($player_index) ? count($player_index) : 0;

    // Collect a list of completed player sprite tokens
    $random_sprite = $db->get_value("SELECT player_image FROM mmrpg_index_players WHERE player_image <> 'player' AND player_image_size = 40 AND player_flag_complete = 1 ORDER BY RAND() LIMIT 1;", 'player_image');

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/players/">Player Index</a>
        <span class="count">( <?= $player_count != 1 ? $player_count.' Players' : '1 Player' ?> )</span>
    </h2>

    <div class="section full">
        <div class="subbody">
            <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_command" style="background-image: url(images/players/<?= $random_sprite ?>/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div></div>
            <p class="text">Use the player index below to search and filter through all the playable characters in the game and either view or edit using the provided links.</p>
        </div>
    </div>

    <div class="section full">
        <div class="subbody">
            <table data-table="players" class="full">
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
                                $link = 'admin/players/sort='.$token.'-';
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
                    // Loop through collected players and list their details
                    if (!empty($player_index)){
                        foreach ($player_index AS $player_id => $player_info){
                            // Parse the player info before displaying it
                            $player_info = rpg_player::parse_index_info($player_info);
                            // Collect the display fields from the array
                            $player_token = $player_info['player_token'];
                            $player_name = $player_info['player_name'];
                            $player_group = '<span class="token">'.$player_info['player_group'].'</span>';
                            $player_type1 = !empty($player_info['player_type']) && !empty($type_index[$player_info['player_type']]) ? $type_index[$player_info['player_type']] : $type_index['none'];
                            $type_string = '<span class="type '.$player_type1['type_token'].'">'.$player_type1['type_name'].'</span>';
                            $edit_link = 'admin/players/'.$player_id.'/';
                            $view_link = 'database/players/'.$player_token.'/';
                            $complete = $player_info['player_flag_complete'] ? true : false;
                            $published = $player_info['player_flag_published'] ? true : false;
                            $hidden = $player_info['player_flag_hidden'] ? true : false;
                            // Print out the player info as a table row
                            ?>
                            <tr class="object<?= !$published ? ' unpublished' : '' ?><?= !$complete ? ' incomplete' : '' ?>">
                                <td class="id"><?= $player_id ?></td>
                                <td class="name"><a class="link_inline" href="<?= $edit_link ?>" title="Edit <?= $player_name ?>" target="_editPlayer<?= $player_id ?>"><?= $player_name ?></a></td>
                                <td class="group"><?= $player_group ?></td>
                                <td class="types"><?= $type_string ?></td>
                                <td class="flags hidden"><?= $hidden ? '<span class="true">&#9745;</span>' : '<span class="false">&#9744;</span>' ?></td>
                                <td class="flags complete"><?= $complete ? '<span class="true">&#x2713;</span>' : '<span class="false">&#x2717;</span>' ?></td>
                                <td class="flags published"><?= $published ? '<span class="type nature">Yes</span>' : '<span class="type flame">No</span>' ?></td>
                                <td class="actions">
                                    <a class="link_inline edit" href="<?= $edit_link ?>" target="_editPlayer<?= $player_id ?>">Edit</a>
                                    <? if ($published): ?>
                                        <a class="link_inline view" href="<?= $view_link ?>" target="_viewPlayer<?= $player_token ?>">View</a>
                                    <? endif; ?>
                                </td>
                            </tr>
                            <?
                        }
                    }
                    // Otherwise if player index is empty show an empty table
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
        <p class="text right"><?= $player_count != 1 ? $player_count.' Players' : '1 Player' ?> Total</p>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

?>