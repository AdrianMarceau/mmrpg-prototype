<?php
/*
 * INDEX PAGE : ADMIN
 */

// Require actions file for form processing
require_once(MMRPG_CONFIG_ROOTDIR.'pages/page.admin_index_actions.php');

// Define the SEO variables for this page
$this_seo_title = 'Admin | '.$this_seo_title;
$this_seo_description = 'Admin control panel for the Mega Man RPG Prototype.';
$this_seo_robots = '';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Admin';

// Count the numer of users in the database
$mmrpg_user_count = $this_database->get_value("SELECT COUNT(user_id) AS user_count FROM mmrpg_users WHERE user_id <> 0;", 'user_count');

// Count the numer of users in the database
$mmrpg_robot_count = $this_database->get_value("SELECT COUNT(robot_id) AS robot_count FROM mmrpg_index_robots WHERE robot_id <> 0;", 'robot_count');

// Start generating the page markup
ob_start();
?>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Admin Panel Index</h2>
<div class="subbody">
    <p class="text">The <strong>Admin Panel</strong> can be used by developers to add, edit, and even delete content from the <strong>Mega Man RPG Prototype</strong> website and game with ease.  This should facilitate more frequent updates and the ability to add new robots, abilities, items, and even players to the game even without access to the source code.  Use this section with caution and if you are unsure about any specific functionality please ask another developer for help.  Thank you!</p>
</div>

<h3 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    <span class="subheader_typewrapper">
        <a class="inline_link" href="admin/users/">User Database </a>
        <span class="count">( <?= $mmrpg_user_count ?> Users )</span>
        <a class="float_link" href="admin/users/">View the User Index &raquo;</a>
    </span>
</h3>
<div class="subbody">
    <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/shops/kalinka/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Robot</div></div>
    <p class="text">Search for users by their user name, email address, or identification number using the input below then click a result to continue to the editor.</p>
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

<h3 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    <span class="subheader_typewrapper">
        <a class="inline_link" href="admin/robots/">Robot Database </a>
        <span class="count">( <?= $mmrpg_robot_count ?> Robots )</span>
        <a class="float_link" href="admin/robots/">View the Robot Index &raquo;</a>
    </span>
</h3>
<div class="subbody">
    <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_<?= rpg_robot::random_frame() ?>" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Mega Man</div></div>
    <p class="text">Search for robots, mechas, and bosses by their name, type, or serial number using the input below then click a result to continue to the editor.</p>
    <div class="text">
    <div class="text">
        <form class="search" data-search="robots">
            <div class="inputs">
                <div class="field text">
                    <input class="text" type="text" name="text" value="" placeholder="Robot Name, Type, or ID" />
                </div>
            </div>
            <div class="results"></div>
        </form>
    </div>
    </div>
</div>

<?php
// Collect the buffer and define the page markup
$this_markup_body = trim(ob_get_clean());
?>