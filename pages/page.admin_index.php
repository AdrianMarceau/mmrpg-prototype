<?php
/*
 * INDEX PAGE : ADMIN
 */

// Require actions file for form processing
require_once(MMRPG_CONFIG_ROOTDIR.'pages/page.admin_index_actions.php');

// Define the SEO variables for this page
$this_seo_title = 'Admin Index | '.$this_seo_title;
$this_seo_description = 'Admin control panel index for the Mega Man RPG Prototype.';
$this_seo_robots = '';

// Define the MARKUP variables for this page
$this_markup_header = '';

// Count the numer of users in the database
$mmrpg_user_count = $db->get_value("SELECT COUNT(user_id) AS user_count FROM mmrpg_users WHERE user_id <> 0;", 'user_count');

// Count the numer of players in the database
$mmrpg_players = rpg_player::get_index();
$mmrpg_player_count = count($mmrpg_players);

// Count the numer of mechas in the database
$mmrpg_mecha_count = $db->get_value("SELECT COUNT(robot_id) AS robot_count FROM mmrpg_index_robots WHERE robot_id <> 0 AND robot_class IN ('mecha');", 'robot_count');

// Count the numer of robots in the database
$mmrpg_robot_count = $db->get_value("SELECT COUNT(robot_id) AS robot_count FROM mmrpg_index_robots WHERE robot_id <> 0 AND robot_class IN ('master');", 'robot_count');

// Count the numer of bosses in the database
$mmrpg_boss_count = $db->get_value("SELECT COUNT(robot_id) AS robot_count FROM mmrpg_index_robots WHERE robot_id <> 0 AND robot_class IN ('boss');", 'robot_count');

// Count the numer of abilities in the database
$mmrpg_ability_count = $db->get_value("SELECT COUNT(ability_id) AS ability_count FROM mmrpg_index_abilities WHERE ability_id <> 0;", 'ability_count');

// Count the numer of items in the database
$mmrpg_item_count = $db->get_value("SELECT COUNT(item_id) AS item_count FROM mmrpg_index_items WHERE item_id <> 0;", 'item_count');

// Count the numer of fields in the database
$mmrpg_field_count = $db->get_value("SELECT COUNT(field_id) AS field_count FROM mmrpg_index_fields WHERE field_id <> 0;", 'field_count');

// Start generating the page markup
ob_start();
?>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    <a class="inline_link" href="admin/">Admin Panel</a>
</h2>
<div class="subbody">
    <p class="text">The <strong>Admin Panel</strong> can be used by developers to add, edit, and even delete content from the <strong>Mega Man RPG Prototype</strong> website and game with ease.  This should facilitate more frequent updates and the ability to add new robots, abilities, items, and even players to the game even without access to the source code.  Use this section with caution and if you are unsure about any specific functionality please ask another developer for help.  Thank you!</p>
</div>

<div class="section">
    <h3 class="subheader field_type_cutter">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="admin/users/">User Database</a>
            <span class="count">( <?= $mmrpg_user_count ?> Users )</span>
            <a class="float_link float_link2" href="admin/users/0/" target="_blank">Add New User &raquo;</a>
        </span>
    </h3>
    <div class="subbody">
        <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_01" style="background-image: url(images/shops/kalinka/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div></div>
        <p class="text">Search for users by typing their user name, email address, or identification number into the input field below.</p>
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

<div class="section">
    <h3 class="subheader field_type_shield">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="admin/players/">Player Database</a>
            <span class="count">( <?= $mmrpg_player_count ?> Players )</span>
            <a class="float_link float_link2" href="admin/players/0/" target="_blank">Add New Player &raquo;</a>
        </span>
    </h3>
    <div class="subbody">
        <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_command" style="background-image: url(images/players/dr-light/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div></div>
        <p class="text">Search for player characters by typing their name or identification number into the input field below.</p>
        <div class="text">
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

<div class="section">
    <h3 class="subheader field_type_freeze">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="admin/mechas/">Mecha Database</a>
            <span class="count">( <?= $mmrpg_mecha_count ?> Mechas )</span>
            <a class="float_link float_link2" href="admin/mechas/0/" target="_blank">Add New Mecha &raquo;</a>
        </span>
    </h3>
    <div class="subbody">
        <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_taunt" style="background-image: url(images/robots/met/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div></div>
        <p class="text">Search for support mechas by typing their name, core, serial, or identification number into the input field below.</p>
        <div class="text">
            <form class="search" data-search="mechas">
                <div class="inputs">
                    <div class="field text">
                        <input class="text" type="text" name="text" value="" placeholder="Mecha Name, Core, Number, or ID" />
                    </div>
                </div>
                <div class="results"></div>
            </form>
        </div>
    </div>
</div>

<div class="section">
    <h3 class="subheader field_type_water">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="admin/robots/">Robot Database</a>
            <span class="count">( <?= $mmrpg_robot_count ?> Robots )</span>
            <a class="float_link float_link2" href="admin/robots/0/" target="_blank">Add New Robot &raquo;</a>
        </span>
    </h3>
    <div class="subbody">
        <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_victory" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div></div>
        <p class="text">Search for robots masters by typing their name, core, serial, or identification number into the input field below.</p>
        <div class="text">
            <form class="search" data-search="robots">
                <div class="inputs">
                    <div class="field text">
                        <input class="text" type="text" name="text" value="" placeholder="Robot Name, Core, Number, or ID" />
                    </div>
                </div>
                <div class="results"></div>
            </form>
        </div>
    </div>
</div>

<div class="section">
    <h3 class="subheader field_type_space">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="admin/bosses/">Boss Database</a>
            <span class="count">( <?= $mmrpg_boss_count ?> Bosses )</span>
            <a class="float_link float_link2" href="admin/bosses/0/" target="_blank">Add New Boss &raquo;</a>
        </span>
    </h3>
    <div class="subbody">
        <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_summon" style="background-image: url(images/robots/trill/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"></div></div>
        <p class="text">Search for fortress bosses by typing their name, core, serial, or identification number into the input field below.</p>
        <div class="text">
            <form class="search" data-search="bosses">
                <div class="inputs">
                    <div class="field text">
                        <input class="text" type="text" name="text" value="" placeholder="Boss Name, Core, Number, or ID" />
                    </div>
                </div>
                <div class="results"></div>
            </form>
        </div>
    </div>
</div>

<div class="section">
    <h3 class="subheader field_type_laser">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="admin/abilities/">Ability Database</a>
            <span class="count">( <?= $mmrpg_ability_count ?> Abilities )</span>
            <a class="float_link float_link2" href="admin/abilities/0/" target="_blank">Add New Ability &raquo;</a>
        </span>
    </h3>
    <div class="subbody">
        <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/abilities/rolling-cutter/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); margin: -8px 0 -6px;"></div></div>
        <p class="text">Search for abilities by typing their name, type, or identification number into the input field below.</p>
        <div class="text">
            <form class="search" data-search="abilities">
                <div class="inputs">
                    <div class="field text">
                        <input class="text" type="text" name="text" value="" placeholder="Ability Name, Type, or ID" />
                    </div>
                </div>
                <div class="results"></div>
            </form>
        </div>
    </div>
</div>

<div class="section">
    <h3 class="subheader field_type_electric">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="admin/items/">Item Database</a>
            <span class="count">( <?= $mmrpg_item_count ?> Items )</span>
            <a class="float_link float_link2" href="admin/items/0/" target="_blank">Add New Item &raquo;</a>
        </span>
    </h3>
    <div class="subbody">
        <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/items/energy-capsule/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); margin: -8px 0 -6px;"></div></div>
        <p class="text">Search for items by typing their name, type, or identification number into the input field below.</p>
        <div class="text">
            <form class="search" data-search="items">
                <div class="inputs">
                    <div class="field text">
                        <input class="text" type="text" name="text" value="" placeholder="Item Name, Type, or ID" />
                    </div>
                </div>
                <div class="results"></div>
            </form>
        </div>
    </div>
</div>

<div class="section">
    <h3 class="subheader field_type_nature">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="admin/fields/">Field Database</a>
            <span class="count">( <?= $mmrpg_field_count ?> Fields )</span>
            <a class="float_link float_link2" href="admin/fields/0/" target="_blank">Add New Field &raquo;</a>
        </span>
    </h3>
    <div class="subbody">
        <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_00" style="background-image: url(images/fields/intro-field/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); margin: -8px 0 -6px; background-size: 45px 45px; background-position: center center;"></div></div>
        <p class="text">Search for fields by typing their name, type, or identification number into the input field below.</p>
        <div class="text">
            <form class="search" data-search="fields">
                <div class="inputs">
                    <div class="field text">
                        <input class="text" type="text" name="text" value="" placeholder="Field Name, Type, or ID" />
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