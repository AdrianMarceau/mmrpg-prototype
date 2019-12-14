<?

// Collect user data for all contributors in the database
$contributor_ids = array(
    412,  // AdrianMarceau (Developer)
    3842,  // MegaBossMan (Administrator)
    4117,  // Rhythm_BCA (Contributor)
    //1330, // TheDoc (Moderator)
    //6455, // Shiver (Moderator)
    110,  // EliteP1 / MMX100 (Contributor)
    2,  // Brorman (Contributor)
    435,  // Spinstrike (Contributor)
    18,  // MetalMan (Contributor)
    7469,  // Brash Buster (Contributor)
    5161,  // The Zion (Contributor)
    //4307,  // Reisrat (Moderator)
    //4091,  // CHAOSFANTAZY (Moderator)
    //4831,  // ThatGuyNamedMikey (Moderator)
    // 92,  // ChillPenguin (Administrator)
    // 484, // Ephnee (Early Tester)
    );
$user_fields = rpg_user::get_index_fields(true, 'users');
$user_roles_fields = rpg_user_role::get_index_fields(true, 'uroles');
$contributor_index = $db->get_array_list("SELECT
    {$user_fields},
    {$user_roles_fields}
    FROM mmrpg_users AS users
    LEFT JOIN mmrpg_roles AS uroles ON users.role_id = uroles.role_id
    WHERE users.user_id IN (".implode(', ', $contributor_ids).")
    ;", 'user_id');
//die('<pre>'.print_r($contributor_index, true).'</pre>');
if (empty($contributor_index)){ $contributor_index = array(); }
function temp_sort_by_date($u1, $u2){
    global $contributor_ids;
    if ($u1['user_id'] == 412){ return -1; }
    elseif ($u2['user_id'] == 412){ return 1; }
    elseif (array_search($u1['user_id'], $contributor_ids) < array_search($u2['user_id'], $contributor_ids)){ return -1; }
    elseif (array_search($u1['user_id'], $contributor_ids) > array_search($u2['user_id'], $contributor_ids)){ return 1; }
    elseif ($u1['user_date_created'] < $u2['user_date_created']){ return -1; }
    elseif ($u1['user_date_created'] > $u2['user_date_created']){ return 1; }
    else { return 0; }
}
uasort($contributor_index, 'temp_sort_by_date');
$contributor_ids = array_keys($contributor_index);

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
            $temp_background = !empty($temp_userinfo['user_background_path']) ? $temp_userinfo['user_background_path'] : 'fields/intro-field';
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

                        <div class="date">
                            <span class="label">Tenure :</span>
                            <span class="time"><?
                                $d1 = new DateTime(date('Y-m-d', $temp_userinfo['user_date_created']));
                                $d2 = new DateTime(date('Y-m-d', $temp_userinfo['user_last_login']));
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