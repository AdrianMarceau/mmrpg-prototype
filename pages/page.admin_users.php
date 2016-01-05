<?php
/*
 * INDEX PAGE : USERS
 */


// Collect the user ID from the request header
$user_id = isset($_GET['num']) && is_numeric($_GET['num']) ? (int)($_GET['num']) : false;

// Collect user info based on the ID if available
$user_fields = rpg_user::get_fields(true);
if (!empty($user_id)){ $user_info = $db->get_array("SELECT {$user_fields} FROM mmrpg_users WHERE user_id = {$user_id};"); }
elseif ($user_id === 0){ $user_info = $db->get_array("SELECT {$user_fields} FROM mmrpg_users WHERE user_name_clean = 'guest';"); }
else { $user_info = array(); }

// Parse the user info if it was collected
if (!empty($user_info)){ $user_info = rpg_user::parse_info($user_info); }

//echo('$user_info['.$user_id.'] = <pre>'.print_r($user_info, true).'</pre><hr />');
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
require_once(MMRPG_CONFIG_ROOTDIR.'pages/page.admin_users_actions.php');

// -- PLAYER EDITOR -- //

// If a user ID was provided, we should show the editor
if ($user_id !== false && !empty($user_info)){

    // Define the SEO variables for this page
    $this_seo_title = $user_info['user_name'].' | User Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin user editor for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/">User Index</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/<?= $this_current_sub ?>/<?= $user_id ?>/"><?= 'ID '.$user_info['user_id'] ?> : <?= $user_info['user_name'] ?></a>
    </h2>
    <div class="subbody">
        <p class="text">Use the <strong>User Editor</strong> to update the details and stats of playable characters in the Mega Man RPG Prototype.  Please be careful and don't forget to save your work.</p>
    </div>

    <form class="editor users" action="admin/<?= $this_current_sub ?>/<?= $user_id ?>/" method="post" enctype="multipart/form-data">
        <div class="subbody">

            <div class="section inputs">
                <div class="field field_user_id">
                    <label class="label">User ID</label>
                    <input class="text" type="text" name="user_id" value="<?= $user_info['user_id'] ?>" disabled="disabled" />
                    <input class="hidden" type="hidden" name="user_id" value="<?= $user_info['user_id'] ?>" />
                </div>
                <div class="field field_user_token">
                    <label class="label">User Token</label>
                    <input class="text" type="text" name="user_token" value="<?= $user_info['user_token'] ?>" maxlength="64" />
                </div>
                <div class="field field_user_name">
                    <label class="label">User Name</label>
                    <input class="text" type="text" name="user_name" value="<?= $user_info['user_name'] ?>" maxlength="64" />
                </div>
                <div class="field field_user_type">
                    <label class="label">User Type</label>
                    <select class="select" name="user_type">
                        <?= str_replace('value="'.$user_info['user_type'].'"', 'value="'.$user_info['user_type'].'" selected="selected"', $stat_index_options) ?>
                    </select>
                </div>
            </div>

            <div class="section actions">
                <div class="buttons">
                    <input type="submit" class="save" name="save" value="Save Changes" />
                    <input type="button" class="delete" name="delete" value="Delete User" />
                </div>
            </div>

        </div>
    </form>

    <div class="subbody">
        <pre>$user_info = <?= print_r($user_info, true) ?></pre>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

// -- PLAYER INDEX -- //

// Else if no user ID was provided, we should show the index
else {

    // Define the SEO variables for this page
    $this_seo_title = 'User Index | Admin Panel | '.$this_seo_title;
    $this_seo_description = 'Admin user editor index for the Mega Man RPG Prototype.';
    $this_seo_robots = '';

    // Define the MARKUP variables for this page
    $this_markup_header = '';

    // Collect the sort, show, and page properties from the URL if they exist
    $raw_sort = !empty($_GET['sort']) && preg_match('/^([a-z0-9]+)-([a-z0-9]+)$/i', $_GET['sort']) ? $_GET['sort'] : 'id-asc';
    list($sort_column, $sort_direction) = explode('-', strtolower($raw_sort));
    $show_limit = !empty($_GET['show']) && is_numeric($_GET['show']) ? $_GET['show'] : 50;
    $sheet_number = !empty($_GET['sheet']) && is_numeric($_GET['sheet']) ? $_GET['sheet'] : 1;
    $row_offset = $show_limit * ($sheet_number - 1);

    // Define the robot header column array for looping through
    // token => array(name, class, width, directions)
    $table_columns = array();
    $table_columns['id'] = array('ID', 'id', '75', 'asc/desc');
    $table_columns['name'] = array('User Name', 'name', '', 'asc/desc');
    $table_columns['email'] = array('Email', 'group', '', 'asc/desc');
    $table_columns['role'] = array('Role', 'role', '120', 'desc/asc');
    $table_columns['accessed'] = array('Online', 'date accessed', '120', 'desc/asc');
    $table_columns['approved'] = array('Approved', 'flags approved', '90', 'desc/asc');
    $table_columns['played'] = array('Played', 'flags played', '100', 'desc/asc');
    $table_columns['actions'] = array('', 'actions', '120', '');

    // Collect the sort properties from the URL if they exist
    $sort_flags = array('approved');
    $sort_dates = array('created', 'accessed', 'modified');
    $sort_direction_upper = strtoupper($sort_direction);
    $other_direction_upper = $sort_direction_upper != 'ASC' ? 'ASC' : 'DESC';
    if (in_array($sort_column, $sort_flags)){ $query_sort = 'users.user_flag_'.$sort_column.' '.$sort_direction_upper; }
    elseif (in_array($sort_column, $sort_dates)){ $query_sort = 'users.user_date_'.$sort_column.' '.$sort_direction_upper; }
    elseif ($sort_column == 'role'){ $query_sort = 'roles.role_level '.$sort_direction_upper; }
    elseif ($sort_column == 'name'){ $query_sort = 'users.user_name <> \'\' '.$other_direction_upper.', users.user_name '.$sort_direction_upper; }
    elseif ($sort_column == 'email'){ $query_sort = 'users.user_email_address <> \'\' '.$other_direction_upper.', users.user_email_address '.$sort_direction_upper; }
    elseif ($sort_column == 'type'){ $query_sort = 'users.user_colour_token '.$sort_direction_upper; }
    elseif ($sort_column == 'played'){ $query_sort = 'user_flag_played '.$sort_direction_upper; }
    else { $query_sort = 'users.user_'.$sort_column.' '.$sort_direction_upper; }

    // Count the total number of users first
    $user_total_count = $db->get_value("SELECT count(user_id) AS total FROM mmrpg_users AS users WHERE users.user_id <> 0;", 'total');

    // If the requested page would go over the limit, floor it
    if (ceil($show_limit * $sheet_number) > $user_total_count){
        $sheet_number = ceil($user_total_count / $show_limit);
        $row_offset = $show_limit * ($sheet_number - 1);
    }

    // Collect a list of all users in the database
    $user_fields = rpg_user::get_fields(true, 'users');
    $user_roles_fields = rpg_user_role::get_fields(true, 'roles');
    $user_query = "SELECT
        {$user_fields},
        {$user_roles_fields},
        (CASE WHEN leaderboard.board_points > 0 THEN 1 ELSE 0 END) AS user_flag_played
        FROM mmrpg_users AS users
        LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
        LEFT JOIN mmrpg_leaderboard AS leaderboard ON leaderboard.user_id = users.user_id
        WHERE users.user_id <> 0
        ORDER BY {$query_sort}
        LIMIT {$row_offset}, {$show_limit}
        ;";
    $user_index = $db->get_array_list($user_query, 'user_id');
    $user_index_count = !empty($user_index) ? count($user_index) : 0;

    // Collect a leaderboard index so we can check if published
    $leaderboard_tokens = rpg_prototype::leaderboard_index_tokens();

    // Collect a list of completed user sprite tokens
    $random_sprite = 'kalinka';

    // Calculate the number of sheets to display
    $num_sheets = ceil($user_total_count / $show_limit);
    // Define a function for generating user sheet links
    $gen_page_link = function($i, $show_active = true, $show_text = false) use ($sort_column, $sort_direction, $show_limit, $sheet_number, $num_sheets){
        $active = $show_active && $i == $sheet_number ? true : false;
        $visible = $i == 1 || $i == $num_sheets || abs($i - $sheet_number) <= 5 ? true : false;
        $link = 'admin/users/sort='.$sort_column.'-'.$sort_direction.'&amp;show='.$show_limit.'&amp;sheet='.$i;
        $class = 'link_inline'.($active ? ' active' : '').(!$visible ? ' compact' : '');
        $text = !empty($show_text) ? $show_text : ($visible ? $i : '.');
        return '<a class="'.$class.'" href="'.$link.'">'.$text.'</a>'.PHP_EOL;
        };

    // Generate links for prev, next, and any pages in between
    $sheet_link_markup = '';
    if ($sheet_number > 1){ $sheet_link_markup .= $gen_page_link($sheet_number - 1, false, '&laquo Prev'); }
    for ($i = 1; $i <= $num_sheets; $i++){ $sheet_link_markup .= $gen_page_link($i); }
    if ($sheet_number < $num_sheets){ $sheet_link_markup .= $gen_page_link($sheet_number + 1, false, 'Next &raquo;'); }

    // Start generating the page markup
    ob_start();
    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <a class="inline_link" href="admin/">Admin Panel</a> <span class="crumb">&raquo;</span>
        <a class="inline_link" href="admin/users/">User Index</a>
        <span class="count">( <?= $user_total_count != 1 ? $user_total_count.' Users' : '1 User' ?> )</span>
        <a class="float_link float_link2" href="admin/users/0/" target="_blank">Add New User &raquo;</a>
    </h2>

    <div class="section full">
        <div class="subbody">
            <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/shops/<?= $random_sprite ?>/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div></div>
            <p class="text">Use the user index below to search and filter through all the playable characters in the game and either view or edit using the provided links.</p>
            <p class="text">You can also search for users by typing their user name, email address, or identification number into the input field below.</p>
            <div class="text">
                <form class="search" data-search="users">
                    <div class="inputs">
                        <div class="field text">
                            <input class="text" type="text" name="text" value="" placeholder="User Name, Email, or ID" />
                        </div>
                    </div>
                    <div class="results"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="subbody dataheader">
        <p class="text left counts">
            <?= $user_index_count != $user_total_count ? 'Showing '.$user_index_count.' of ' : '' ?>
            <?= $user_total_count != 1 ? $user_total_count.' Users' : '1 User' ?> Total
        </p>
        <p class="text right sheets">
            <?= $sheet_link_markup ?>
        </p>
    </div>

    <div class="section full">
        <div class="subbody">
            <table data-table="users" class="full">
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
                                $link = 'admin/users/sort='.$token.'-';
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
                    // Loop through collected users and list their details
                    if (!empty($user_index)){
                        $filter = '/[^a-z0-9]+/i';
                        foreach ($user_index AS $user_id => $user_info){
                            // Parse the user info before displaying it
                            $user_info = rpg_user::parse_info($user_info);
                            // Collect the display fields from the array
                            $user_token = $user_info['user_name_clean'];
                            $user_name = $user_info['user_name'];
                            $user_name2 = $user_info['user_name_public'];
                            $user_names_match = preg_replace($filter, '', $user_name) == preg_replace($filter, '', $user_name2) ? true : false;
                            if (!empty($user_name2) && !$user_names_match){ $user_name .= ' / '.$user_name2; }
                            $user_email = $user_info['user_email_address'];
                            $user_accessed = !empty($user_info['user_date_accessed']) ? $user_info['user_date_accessed'] : $user_info['user_date_created'];
                            $user_role = '<span class="type '.$user_info['role_colour'].'">'.$user_info['role_name'].'</span>';
                            $edit_link = 'admin/users/'.$user_id.'/';
                            $view_link = 'leaderboard/'.$user_token.'/';
                            $approved = $user_info['user_flag_approved'] ? true : false;
                            $played = $user_info['user_flag_played'] ? true : false;
                            // Print out the user info as a table row
                            ?>
                            <tr class="object<?= !$played && !$approved ? ' incomplete' : '' ?>">
                                <td class="id"><?= $user_id ?></td>
                                <td class="name"><a class="link_inline" href="<?= $edit_link ?>" title="Edit <?= $user_name ?>" target="_editUser<?= $user_id ?>"><?= $user_name ?></a></td>
                                <td class="email"><a class="link_inline" <?= !empty($user_email) ? 'href="mailto:'.$user_email.'"' : '' ?> title="<?= $user_email ?>" target="_blank"><?= $user_email ?></a></td>
                                <td class="role"><?= $user_role ?></td>
                                <td class="date accessed"><?= $user_accessed ? date('M jS, Y', $user_accessed) : '-' ?></td>
                                <td class="flags approved"><?= $approved ? '<span class="true">&#x2713;</span>' : '<span class="false">&#x2717;</span>' ?></td>
                                <td class="flags played"><?= $played ? '<span class="type nature">Yes</span>' : '<span class="type flame">No</span>' ?></td>
                                <td class="actions">
                                    <a class="link_inline edit" href="<?= $edit_link ?>" target="_editUser<?= $user_id ?>">Edit</a>
                                    <? if ($active): ?>
                                        <a class="link_inline view" href="<?= $view_link ?>" target="_viewUser<?= $user_token ?>">View</a>
                                    <? endif; ?>
                                </td>
                            </tr>
                            <?
                        }
                    }
                    // Otherwise if user index is empty show an empty table
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

    <div class="subbody dataheader">
        <p class="text left counts">
            <?= $user_index_count != $user_total_count ? 'Showing '.$user_index_count.' of ' : '' ?>
            <?= $user_total_count != 1 ? $user_total_count.' Users' : '1 User' ?> Total
        </p>
        <p class="text right sheets">
            <?= $sheet_link_markup ?>
        </p>
    </div>


    <?php
    // Collect the buffer and define the page markup
    $this_markup_body = trim(ob_get_clean());

}

?>