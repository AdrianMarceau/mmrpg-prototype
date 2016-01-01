<?php
/*
 * INDEX PAGE : ROBOTS
 */


// Collect the robot ID from the request header
$robot_id = isset($_GET['num']) && is_numeric($_GET['num']) ? (int)($_GET['num']) : false;

// Collect robot info based on the ID if available
if (!empty($robot_id)){ $robot_info = $this_database->get_array("SELECT * FROM mmrpg_index_robots WHERE robot_id = {$robot_id};"); }
elseif ($robot_id === 0){ $robot_info = $this_database->get_array("SELECT * FROM mmrpg_index_robots WHERE robot_token = 'robot';"); }
else { $robot_info = array(); }

// Parse the robot info if it was collected
if (!empty($robot_info)){ $robot_info = rpg_robot::parse_index_info($robot_info); }

//echo('$robot_info['.$robot_id.'] = <pre>'.print_r($robot_info, true).'</pre><hr />');
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
require_once(MMRPG_CONFIG_ROOTDIR.'pages/page.admin_robots_actions.php');

// -- ROBOT EDITOR -- //

// If a robot ID was provided, we should show the editor
if ($robot_id !== false && !empty($robot_info)){

    // Define the SEO variables for this page
    $this_seo_title = $robot_info['robot_name'].' | Robot Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin robot editor for the Mega Man RPG Prototype.';
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
        <a class="inline_link" href="admin/robots/">Robot Index</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/robots/<?= $robot_id ?>/"><?= 'ID '.$robot_info['robot_id'] ?> : <?= $robot_info['robot_name'] ?></a>
    </h2>
    <div class="subbody">
        <p class="text">Use the <strong>Robot Editor</strong> to update the details and stats of playable characters in the Mega Man RPG Prototype.  Please be careful and don't forget to save your work.</p>
    </div>

    <form class="editor robots" action="admin/robots/<?= $robot_id ?>/" method="post" enctype="multipart/form-data">
        <div class="subbody">

            <div class="section inputs">
                <div class="field field_robot_id">
                    <label class="label">Robot ID</label>
                    <input class="text" type="text" name="robot_id" value="<?= $robot_info['robot_id'] ?>" disabled="disabled" />
                    <input class="hidden" type="hidden" name="robot_id" value="<?= $robot_info['robot_id'] ?>" />
                </div>
                <div class="field field_robot_token">
                    <label class="label">Robot Token</label>
                    <input class="text" type="text" name="robot_token" value="<?= $robot_info['robot_token'] ?>" maxlength="64" />
                </div>
                <div class="field field_robot_name">
                    <label class="label">Robot Name</label>
                    <input class="text" type="text" name="robot_name" value="<?= $robot_info['robot_name'] ?>" maxlength="64" />
                </div>
                <div class="field field_robot_type">
                    <label class="label">Robot Type</label>
                    <select class="select" name="robot_type">
                        <?= str_replace('value="'.$robot_info['robot_type'].'"', 'value="'.$robot_info['robot_type'].'" selected="selected"', $stat_index_options) ?>
                    </select>
                </div>
            </div>

            <div class="section actions">
                <div class="buttons">
                    <input type="submit" class="save" name="save" value="Save Changes" />
                    <input type="button" class="delete" name="delete" value="Delete Robot" />
                </div>
            </div>

        </div>
    </form>

    <div class="subbody">
        <pre>$robot_info = <?= print_r($robot_info, true) ?></pre>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

// -- ROBOT INDEX -- //

// Else if no robot ID was provided, we should show the index
else {

    // Define the SEO variables for this page
    $this_seo_title = 'Robot Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin robot editor index for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Collect a list of all users in the database
    $robot_fields = rpg_robot::get_index_fields(true);
    $robot_index = $this_database->get_array_list("SELECT {$robot_fields} FROM mmrpg_index_robots WHERE robot_id <> 0 AND robot_token <> 'robot' AND robot_class = 'master' ORDER BY robot_id ASC", 'robot_id');
    $robot_count = !empty($robot_index) ? count($robot_index) : 0;

    // Collect a list of completed robot sprite tokens
    $random_sprite = $this_database->get_value("SELECT robot_image FROM mmrpg_index_robots WHERE robot_image <> 'robot' AND robot_class = 'master' AND robot_image_size = 40 AND robot_flag_complete = 1 ORDER BY RAND() LIMIT 1;", 'robot_image');

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/robots/">Robot Index</a>
        <span class="count">( <?= $robot_count != 1 ? $robot_count.' Robots' : '1 Robot' ?> )</span>
    </h2>

    <div class="section full">
        <div class="subbody">
            <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_command" style="background-image: url(images/robots/<?= $random_sprite ?>/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div></div>
                <p class="text">Use the robot index below to search and filter through all the unlockable robots in the game and either view, edit, or delete using the provided links.</p>            <div class="text">
                <p class="text">You can also jump to specific robots by typing their name or identification number into the input field below and clicking their link in the dropdown.</p>
                <form class="search" data-search="robots">
                    <div class="inputs">
                        <div class="field text">
                            <input class="text" type="text" name="text" value="" placeholder="Robot Name or ID" />
                        </div>
                    </div>
                    <div class="results"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="section full">
        <div class="subbody">
            <table data-table="robots" class="full">
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
                        <th class="name">Robot Name</th>
                        <th class="types">Robot Type</th>
                        <th class="flags complete">Complete</th>
                        <th class="flags published">Published</th>
                        <th class="flags hidden">Hidden</th>
                        <th class="actions">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    // Loop through collected robots and list their details
                    if (!empty($robot_index)){
                        foreach ($robot_index AS $robot_id => $robot_info){
                            // Parse the robot info before displaying it
                            $robot_info = rpg_robot::parse_index_info($robot_info);
                            // Collect the display fields from the array
                            $robot_token = $robot_info['robot_token'];
                            $robot_name = $robot_info['robot_name'];
                            $robot_type1 = !empty($robot_info['robot_core']) && !empty($type_index[$robot_info['robot_core']]) ? $type_index[$robot_info['robot_core']] : $type_index['none'];
                            $type_string = '<span class="type '.$robot_type1['type_token'].'">'.$robot_type1['type_name'].'</span>';
                            $edit_link = 'admin/robots/'.$robot_id.'/';
                            $view_link = 'database/robots/'.$robot_token.'/';
                            $complete = $robot_info['robot_flag_complete'] ? true : false;
                            $published = $robot_info['robot_flag_published'] ? true : false;
                            $hidden = $robot_info['robot_flag_hidden'] ? true : false;
                            // Print out the robot info as a table row
                            ?>
                            <tr class="object <?= !$complete ? 'incomplete' : '' ?>">
                                <td class="id"><?= $robot_id ?></td>
                                <td class="name"><a class="link_inline" href="<?= $edit_link ?>" title="Edit <?= $robot_name ?>" target="_editRobot<?= $robot_id ?>"><?= $robot_name ?></a></td>
                                <td class="types"><?= $type_string ?></td>
                                <td class="flags complete"><?= $complete ? '&#x2713;' : '&#x2717;' ?></td>
                                <td class="flags published"><?= $published ? '&#9745;' : '&#9744;' ?></td>
                                <td class="flags hidden"><?= $hidden ? '&#9745;' : '&#9744;' ?></td>
                                <td class="actions">
                                    <a class="link_inline edit" href="<?= $edit_link ?>" target="_editRobot<?= $robot_id ?>">Edit</a>
                                    <a class="link_inline view" href="<?= $view_link ?>" target="_viewRobot<?= $robot_token ?>">View</a>
                                    <a class="link_inline delete" target="_blank">Delete</a>
                                </td>
                            </tr>
                            <?
                        }
                    }
                    // Otherwise if robot index is empty show an empty table
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
        <p class="text right"><?= $robot_count != 1 ? $robot_count.' Robots' : '1 Robot' ?> Total</p>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

?>