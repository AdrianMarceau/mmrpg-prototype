<?php
/*
 * INDEX PAGE : PLAYERS
 */


// Collect the player ID from the request header
$player_id = isset($_GET['num']) && is_numeric($_GET['num']) ? (int)($_GET['num']) : false;

// Collect player info based on the ID if available
if (!empty($player_id)){ $player_info = $this_database->get_array("SELECT * FROM mmrpg_index_players WHERE player_id = {$player_id};"); }
elseif ($player_id === 0){ $player_info = $this_database->get_array("SELECT * FROM mmrpg_index_players WHERE player_token = 'player';"); }
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

    // Count the numer of users in the database
    $mmrpg_user_count = $this_database->get_value("SELECT COUNT(user_id) AS user_count FROM mmrpg_users WHERE user_id <> 0;", 'user_count');

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/players/">Player Index</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/players/<?= $player_id ?>/"><?= 'ID '.$player_info['player_id'] ?> : <?= $player_info['player_name'] ?></a>
    </h2>
    <div class="subbody">
        <p class="text">Use the <strong>Player Editor</strong> to update the details and stats of playable characters in the Mega Man RPG Prototype.  Please be careful and don't forget to save your work.</p>
    </div>

    <form class="editor players" action="admin/players/<?= $player_id ?>/" method="post" enctype="multipart/form-data">
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

    // Collect a list of all users in the database
    $player_fields = rpg_player::get_index_fields(true);
    $player_index = $this_database->get_array_list("SELECT {$player_fields} FROM mmrpg_index_players WHERE player_id <> 0 AND player_token <> 'player' ORDER BY player_id ASC", 'player_id');
    $player_count = !empty($player_index) ? count($player_index) : 0;

    // Collect a list of completed player sprite tokens
    $sprite_tokens = $this_database->get_array_list("SELECT player_image FROM mmrpg_index_players WHERE player_image <> 'player' AND player_image_size = 40 AND player_flag_complete = 1 ORDER BY player_image ASC;", 'player_image');
    $sprite_tokens = !empty($sprite_tokens) ? array_keys($sprite_tokens) : array('player');
    $random_sprite = $sprite_tokens[mt_rand(0, (count($sprite_tokens) - 1))];

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
                <p class="text">Use the player index below to search and filter through all the playable characters in the game and either view, edit, or delete using the provided links.</p>            <div class="text">
                <p class="text">You can also jump to specific players by typing their name or identification number into the input field below and clicking their link in the dropdown.</p>
                <form class="search" data-search="players">
                    <div class="inputs">
                        <div class="field text">
                            <input class="text" type="text" name="text" value="" placeholder="Player Name or ID" />
                        </div>
                    </div>
                    <div class="results"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="section full">
        <div class="subbody">
            <table data-table="players" class="full">
                <colgroup>
                    <col width="10%" />
                    <col width="" />
                    <col width="20%" />
                    <col width="3%" />
                    <col width="3%" />
                    <col width="3%" />
                    <col width="15%" />
                </colgroup>
                <thead>
                    <tr class="head">
                        <th class="id">ID</th>
                        <th class="name">Player Name</th>
                        <th class="types">Player Type</th>
                        <th class="flags complete">Complete</th>
                        <th class="flags published">Published</th>
                        <th class="flags hidden">Hidden</th>
                        <th class="actions">&nbsp;</th>
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
                            $player_type1 = !empty($player_info['player_type']) && !empty($type_index[$player_info['player_type']]) ? $type_index[$player_info['player_type']] : $type_index['none'];
                            $type_string = '<span class="type '.$player_type1['type_token'].'">'.$player_type1['type_name'].'</span>';
                            $edit_link = 'admin/players/'.$player_id.'/';
                            $view_link = 'database/players/'.$player_token.'/';
                            $complete = $player_info['player_flag_complete'] ? true : false;
                            $published = $player_info['player_flag_published'] ? true : false;
                            $hidden = $player_info['player_flag_hidden'] ? true : false;
                            // Print out the player info as a table row
                            ?>
                            <tr class="object <?= !$complete ? 'incomplete' : '' ?>">
                                <td class="id"><?= $player_id ?></td>
                                <td class="name"><a class="link_inline" href="<?= $edit_link ?>" title="Edit <?= $player_name ?>" target="_editPlayer<?= $player_id ?>"><?= $player_name ?></a></td>
                                <td class="types"><?= $type_string ?></td>
                                <td class="flags complete"><?= $complete ? '&#x2713;' : '&#x2717;' ?></td>
                                <td class="flags published"><?= $published ? '&#9745;' : '&#9744;' ?></td>
                                <td class="flags hidden"><?= $hidden ? '&#9745;' : '&#9744;' ?></td>
                                <td class="actions">
                                    <a class="link_inline edit" href="<?= $edit_link ?>" target="_editPlayer<?= $player_id ?>">Edit</a>
                                    <a class="link_inline view" href="<?= $view_link ?>" target="_viewPlayer<?= $player_token ?>">View</a>
                                    <a class="link_inline delete" target="_blank">Delete</a>
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