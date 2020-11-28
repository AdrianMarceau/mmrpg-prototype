<?

// Collect user data for all contributors in the database
$contributor_fields = rpg_user::get_contributor_index_fields(true, 'contributors');
$user_roles_fields = rpg_user_role::get_index_fields(true, 'uroles');
$contributor_index = $db->get_array_list("SELECT
    {$contributor_fields},
    {$user_roles_fields},
    users.user_id,
    (CASE
        WHEN contributors.user_date_created <> 0
        THEN contributors.user_date_created
        ELSE users.user_date_created
        END) AS user_date_created,
    users.user_last_login
    FROM mmrpg_users_contributors AS contributors
    LEFT JOIN mmrpg_roles AS uroles ON contributors.role_id = uroles.role_id
    LEFT JOIN mmrpg_users AS users ON contributors.contributor_id = users.contributor_id
    WHERE contributors.contributor_flag_showcredits = 1
    ;", 'contributor_id');
//die('<pre>'.print_r($contributor_index, true).'</pre>');
if (empty($contributor_index)){ $contributor_index = array(); }
function temp_sort_by_date($u1, $u2){
    global $contributor_ids;
    if ($u1['role_level'] > $u2['role_level']){ return -1; }
    elseif ($u1['role_level'] < $u2['role_level']){ return 1; }
    elseif ($u1['user_date_created'] < $u2['user_date_created']){ return -1; }
    elseif ($u1['user_date_created'] > $u2['user_date_created']){ return 1; }
    else { return 0; }
}
uasort($contributor_index, 'temp_sort_by_date');
$contributor_ids = array_keys($contributor_index);

// Additionally collect an index of sprite counts for each contributor
$join_id_field = MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD === 'contributor_id' ? 'contributors.contributor_id' : 'users.user_id';
$contributor_sprites_index = $db->get_array_list("
    SELECT
        contributor_id,
        user_id,
        user_player_image_count,
        user_robot_image_count,
        user_ability_image_count,
        user_item_image_count,
        user_field_image_count,
        (user_player_image_count
            + user_robot_image_count
            + user_ability_image_count
            + user_item_image_count
            + user_field_image_count) AS user_total_image_count
    FROM (
        SELECT
        contributors.contributor_id,
        users.user_id AS user_id,
        (CASE WHEN player_editors.player_image_count IS NOT NULL THEN player_editors.player_image_count ELSE 0 END)
            + (CASE WHEN player_editors2.player_image_count2 IS NOT NULL THEN player_editors2.player_image_count2 ELSE 0 END) AS user_player_image_count,
        (CASE WHEN robot_editors.robot_image_count IS NOT NULL THEN robot_editors.robot_image_count ELSE 0 END)
            + (CASE WHEN robot_editors2.robot_image_count2 IS NOT NULL THEN robot_editors2.robot_image_count2 ELSE 0 END) AS user_robot_image_count,
        (CASE WHEN ability_editors.ability_image_count IS NOT NULL THEN ability_editors.ability_image_count ELSE 0 END) AS user_ability_image_count,
        (CASE WHEN item_editors.item_image_count IS NOT NULL THEN item_editors.item_image_count ELSE 0 END) AS user_item_image_count,
        (CASE WHEN field_editors.field_image_count IS NOT NULL THEN field_editors.field_image_count ELSE 0 END) AS user_field_image_count
        FROM
        mmrpg_users_contributors AS contributors
        LEFT JOIN mmrpg_users AS users ON users.contributor_id = contributors.contributor_id
        LEFT JOIN mmrpg_roles AS uroles ON uroles.role_id = users.role_id
        -- JOIN PLAYER IMAGES
        LEFT JOIN (SELECT
            player_image_editor AS player_editor_id,
            COUNT(player_image_editor) AS player_image_count
            FROM mmrpg_index_players
            GROUP BY player_image_editor) AS player_editors ON player_editors.player_editor_id = {$join_id_field}
        LEFT JOIN (SELECT
            player_image_editor2 AS player_editor_id,
            COUNT(player_image_editor2) AS player_image_count2
            FROM mmrpg_index_players
            GROUP BY player_image_editor2) AS player_editors2 ON player_editors2.player_editor_id = {$join_id_field}
        -- JOIN ROBOT IMAGES
        LEFT JOIN (SELECT
            robot_image_editor AS robot_editor_id,
            COUNT(robot_image_editor) AS robot_image_count
            FROM mmrpg_index_robots
            GROUP BY robot_image_editor) AS robot_editors ON robot_editors.robot_editor_id = {$join_id_field}
        LEFT JOIN (SELECT
            robot_image_editor2 AS robot_editor_id,
            COUNT(robot_image_editor2) AS robot_image_count2
            FROM mmrpg_index_robots
            GROUP BY robot_image_editor2) AS robot_editors2 ON robot_editors2.robot_editor_id = {$join_id_field}
        -- JOIN ABILITY IMAGES
        LEFT JOIN (SELECT
            ability_image_editor AS ability_editor_id,
            COUNT(ability_image_editor) AS ability_image_count
            FROM mmrpg_index_abilities
            GROUP BY ability_image_editor) AS ability_editors ON ability_editors.ability_editor_id = {$join_id_field}
        -- JOIN ITEM IMAGES
        LEFT JOIN (SELECT
            item_image_editor AS item_editor_id,
            COUNT(item_image_editor) AS item_image_count
            FROM mmrpg_index_items
            GROUP BY item_image_editor) AS item_editors ON item_editors.item_editor_id = {$join_id_field}
        -- JOIN FIELD IMAGES
        LEFT JOIN (SELECT
            field_image_editor AS field_editor_id,
            COUNT(field_image_editor) AS field_image_count
            FROM mmrpg_index_fields
            GROUP BY field_image_editor) AS field_editors ON field_editors.field_editor_id = {$join_id_field}
        WHERE
        contributors.contributor_flag_showcredits = 1
        AND (1 = 0
            OR player_editors.player_image_count IS NOT NULL
            OR player_editors2.player_image_count2 IS NOT NULL
            OR robot_editors.robot_image_count IS NOT NULL
            OR robot_editors2.robot_image_count2 IS NOT NULL
            OR ability_editors.ability_image_count IS NOT NULL
            OR item_editors.item_image_count IS NOT NULL
            OR field_editors.field_image_count IS NOT NULL
            )
        ORDER BY
        uroles.role_level DESC,
        contributors.user_name_clean ASC
    ) AS contributors
    ;", MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD);

// Collect the elemental types index
$mmrpg_types_index = rpg_type::get_index(true);

// Parse the pseudo-code tag <!-- MMRPG_CONTRIBUTORS_INDEX_MARKUP -->
$find = '<!-- MMRPG_CONTRIBUTORS_INDEX_MARKUP -->';
if (strstr($page_content_parsed, $find)){
    ob_start();
    if (!empty($contributor_ids)){
        foreach ($contributor_ids AS $id){
            $temp_userinfo = $contributor_index[$id];
            if (!empty($temp_userinfo['user_name_public'])){
                $temp_public_hash = preg_replace('/[^a-z0-9]+/i', '', strtolower($temp_userinfo['user_name_public']));
                $temp_base_hash = preg_replace('/[^a-z0-9]+/i', '', strtolower($temp_userinfo['user_name']));
                $temp_displayname = $temp_public_hash != $temp_base_hash ? $temp_userinfo['user_name_public'].' / '.$temp_userinfo['user_name'] : $temp_userinfo['user_name_public'];
            } else {
                $temp_displayname = $temp_userinfo['user_name'];
            }
            $temp_role_id = !empty($temp_userinfo['role_id']) ? $temp_userinfo['role_id'] : 3;
            $temp_displayname_short = !empty($temp_userinfo['user_name_public']) ? $temp_userinfo['user_name_public'] : $temp_userinfo['user_name'];
            $temp_displayline = !empty($temp_userinfo['user_credit_line']) ? $temp_userinfo['user_credit_line'] : 'Miscellaneous Contributions';
            $temp_displaytext = !empty($temp_userinfo['user_credit_text']) ? $temp_userinfo['user_credit_text'] : $temp_displayname_short.' joined the prototype on '.date('F jS, Y', $temp_userinfo['user_date_created']).' and has since become a contributor.';
            $temp_background = !empty($temp_userinfo['user_background_path']) ? $temp_userinfo['user_background_path'] : 'fields/'.rpg_player::get_intro_field();
            $temp_websitelink = !empty($temp_userinfo['user_website_address']) ? $temp_userinfo['user_website_address'] : false;
            $temp_playertype = !empty($temp_userinfo['user_colour_token']) ? $temp_userinfo['user_colour_token'] : 'none';
            //$temp_textcolour = !in_array($temp_playertype, array('empty', 'shadow')) ? 'rgb('.implode(', ', $mmrpg_types_index[$temp_playertype]['type_colour_light']).')' : 'rgb(97, 97, 97)';
            $temp_imagepath = !empty($temp_userinfo['user_image_path']) ? $temp_userinfo['user_image_path'] : 'robots/mega-man/40';
            $temp_itemkind = !empty($temp_userinfo['role_icon']) ? $temp_userinfo['role_icon'] : 'energy-pellet';
            list($temp_class, $temp_token, $temp_size) = explode('/', $temp_imagepath);
            ?>
            <div class="subbody creditblock">
                <div class="float float_left" style="background-image: url(<?= 'images/'.$temp_background.'/battle-field_avatar.png' ?>);"><div class="sprite sprite_<?= $temp_size.'x'.$temp_size ?> sprite_<?= $temp_size.'x'.$temp_size ?>_02" style="background-image: url(images/<?= $temp_class.'/'.$temp_token.'/' ?>/sprite_right_<?= $temp_size.'x'.$temp_size ?>.png); <?= $temp_size == 80 ? 'margin-left: -22px; margin-top: -60px; ' : '' ?>"><?= $temp_displayname ?></div></div>
                <div class="text ">
                    <div class="details">

                        <div class="name">
                            <a class="link_inline player_type player_type_<?= $temp_playertype ?>" style="background-image: url(images/items/<?= $temp_itemkind ?>/icon_left_40x40.png)" href="leaderboard/<?= $temp_userinfo['user_name_clean'] ?>/"><strong><?= $temp_displayname ?></strong></a>
                        </div>

                        <div class="title">
                            <span class="label">Title :</span>
                            <?= $temp_userinfo['role_name_full'] ?>
                        </div>

                        <div class="credits">
                            <span class="label">Credits :</span>
                            <em class="reason"><?= $temp_displayline ?></em>
                        </div>

                        <?
                        // Check to see if we have sprite contribution stats for this user
                        if (!empty($temp_userinfo[MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD])
                            && !empty($contributor_sprites_index[$temp_userinfo[MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD]])){
                            $sprite_stats = $contributor_sprites_index[$temp_userinfo[MMRPG_CONFIG_IMAGE_EDITOR_ID_FIELD]];
                            $sprite_stats_text = array();
                            foreach ($sprite_stats AS $field => $value){
                                if (empty($value) || !strstr($field, '_count') || strstr($field, '_total')){ continue; }
                                $kind = str_replace(array('user_', '_image_count'), '', $field);
                                $xkind = substr($kind, -1, 1) === 'y' ? substr($kind, 0, -1).'ies' : $kind.'s';
                                $sprite_stats_text[] = $value.' '.ucfirst($value > 1 ? $xkind : $kind);
                            }
                            ?>
                            <div class="credits">
                                <span class="label">Sprites :</span>
                                <em class="reason"><?= implode(', ', $sprite_stats_text) ?></em>
                            </div>
                            <?
                        }
                        ?>

                        <div class="date">
                            <span class="label">Tenure :</span>
                            <span class="time"><?
                                $d1 = new DateTime(date('Y-m-d', $temp_userinfo['user_date_created']));
                                $d2 = new DateTime(date('Y-m-d', (!empty($temp_userinfo['user_last_login']) ? $temp_userinfo['user_last_login'] : $temp_userinfo['user_date_modified'])));
                                $diff = $d2->diff($d1);
                                $yyyy = $diff->y;
                                $mm = $diff->m;
                                echo $yyyy.' Years'.(!empty($mm) ? ', '.$mm.' Months' : '');
                                ?></span>
                                (<span class="from"><?= date('F Y', $temp_userinfo['user_date_created']) ?></span> to
                                <span class="to"><?= date('F Y', $temp_userinfo['user_last_login']) ?></span>)
                        </div>

                        <? if(!empty($temp_websitelink)): ?>
                            <div class="website">
                                <span class="label">Website :</span>
                                <a class="link_inline" href="<?= $temp_websitelink ?>" target="_blank" rel="contributor"><?= $temp_websitelink ?></a>
                            </div>
                        <? endif;?>

                    </div>

                    <div class="text description">
                        <?= str_replace('<br /><br />', '</div><div>', mmrpg_formatting_decode($temp_displaytext)) ?>
                    </div>

                </div>
            </div>
            <?
        }
    }
    $replace = ob_get_clean();
    $page_content_parsed = str_replace($find, $replace, $page_content_parsed);
}

?>