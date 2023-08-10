<? ob_start(); ?>

    <?

    // Predefine variables to hold markup for later
    $html_markup = '';
    $styles_markup = '';
    $scripts_markup = '';

    // Collect the URL arguments
    $allowed_kinds = array('events', 'challenges');
    $allowed_kinds_singular = array('event', 'challenge');
    $kind = !empty($_GET['kind']) && in_array($_GET['kind'], $allowed_kinds) ? $_GET['kind'] : false;
    $kind_singular = $allowed_kinds_singular[array_search($kind, $allowed_kinds)];
    $force_refresh = !empty($_GET['refresh']) && $_GET['refresh'] === 'true' ? true : false;

    // Error message if `kind` or `class` is not provided
    if (!$kind) { die("Error: Missing required `kind` parameter."); }

    // Update the tab name with the page name
    $this_page_tabtitle = 'View '.ucfirst($kind_singular).' Banners | '.$this_page_tabtitle;

    // Generate the markup for the sprite filters
    ob_start();
    ?>
        <div class="banner-container">
            <!-- Dynamically Generated Banners -->
            <script>
                const players = ['dr-light', 'dr-wily', 'dr-cossack'];
                <? if ($kind === 'events'){ ?>

                    const chapters = [
                        'chapter-1-unlocked',
                        'chapter-2-unlocked',
                        'chapter-3-unlocked',
                        'chapter-4-unlocked',
                        'chapter-5-unlocked',
                        'chapter-random-unlocked',
                        'chapter-stars-unlocked',
                        'chapter-players-unlocked',
                        'chapter-challenges-unlocked'
                    ];
                    chapters.forEach(chapter => {
                        players.forEach(player => {
                            let imgSrc = `<?= MMRPG_CONFIG_ROOTURL ?>scripts/get-banner.php?kind=event&event=${chapter}&player=${player}<?= $force_refresh ? '&refresh=true' : '' ?>`;
                            let btnHref = `<?= MMRPG_CONFIG_ROOTURL ?>images/events/event-banner_${chapter}_${player}.png`;
                            document.write(`
                                <div class="banner-cell">
                                    <strong class="banner-caption">${chapter}</strong>
                                    <a class="banner-image" href="${btnHref}" target="_blank">
                                        <img  src="${imgSrc}" alt="${chapter} - ${player}" />
                                    </a>
                                </div>
                            `);
                        });
                    });

                <? } elseif ($kind === 'challenges') { ?>

                    <?

                    // Define the max, page, and offset for the results
                    $challenges_to_display_max = !empty($_GET['max']) && is_numeric($_GET['max']) ? $_GET['max'] : 10;
                    $challenges_to_display_page = !empty($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
                    $challenges_to_display_offset = ($challenges_to_display_page - 1) * $challenges_to_display_max;
                    //error_log('$challenges_to_display_max = '.$challenges_to_display_max);
                    //error_log('$challenges_to_display_page = '.$challenges_to_display_page);
                    //error_log('$challenges_to_display_offset = '.$challenges_to_display_offset);

                    // Pre-collect a list of IDs, names, and tokens for reference
                    $published_challenges_index = $db->get_array_list("SELECT
                        `challenge_id` AS `id`,
                        `challenge_name` AS `name`
                        FROM `mmrpg_challenges`
                        WHERE
                        `challenge_kind` = 'event'
                        AND `challenge_flag_published` = 1
                        ORDER BY `challenge_date_created` ASC
                        LIMIT {$challenges_to_display_offset}, {$challenges_to_display_max}
                        ;", 'id');
                    $published_challenges_index = !empty($published_challenges_index) ? array_map(function($info){
                        $token = $info['name'];
                        $token = strtolower($token);
                        $token = preg_replace('/\s+/', '-', $token);
                        $token = preg_replace('/[^-a-z0-9]+/i', '', $token);
                        $info['token'] = $token;
                        return $info;
                        }, $published_challenges_index) : array();
                    //error_log('$published_challenges_index = '.print_r($published_challenges_index, true));
                    $challenges_to_display_total = $db->get_value("SELECT
                        COUNT(`challenge_id`) AS `total`
                        FROM `mmrpg_challenges`
                        WHERE
                        `challenge_kind` = 'event'
                        AND `challenge_flag_published` = 1
                        ;", 'total');

                    // Given the max, page, offset, and total collected, define a prev link and next link variable and then fill each individually w/ a link as appropriate
                    $challenges_to_display_base_href = 'admin/view-banners/?kind=challenges';
                    $challenges_to_display_base_href .= '&refresh='.($force_refresh ? 'true' : 'false');
                    $challenges_to_display_base_href .= '&max='.$challenges_to_display_max;
                    //$challenges_to_display_base_href .= '&page='.$challenges_to_display_page;
                    $challenges_to_display_prev_href = false;
                    $challenges_to_display_next_href = false;
                    if ($challenges_to_display_page > 1){
                        $challenges_to_display_prev_href = $challenges_to_display_base_href;
                        $challenges_to_display_prev_href .= '&page='.($challenges_to_display_page - 1);
                    }
                    if ($challenges_to_display_total > ($challenges_to_display_offset + $challenges_to_display_max)){
                        $challenges_to_display_next_href = $challenges_to_display_base_href;
                        $challenges_to_display_next_href .= '&page='.($challenges_to_display_page + 1);
                    }

                    //error_log('$challenges_to_display_total = '.$challenges_to_display_total);
                    //error_log('$challenges_to_display_prev_href = '.$challenges_to_display_prev_href);
                    //error_log('$challenges_to_display_next_href = '.$challenges_to_display_next_href);


                    // Generate a list of challenge tokens to display
                    $challenges_to_display = array();
                    foreach ($published_challenges_index AS $id => $info){
                        if (count($challenges_to_display) >= $challenges_to_display_max){ break; }
                        $challenges_to_display[] = $info['id'].'-'.$info['token'];
                    }

                    ?>
                    const challenges = <?= json_encode($challenges_to_display) ?>;

                    <? if (!empty($challenges_to_display_prev_href)){ ?>
                        document.write(`
                            <div class="link-cell">
                                <a class="banner-link" href="<?= $challenges_to_display_prev_href ?>">Prev</a>
                            </div>
                        `);
                    <? } else { ?>
                        document.write(`
                            <div class="link-cell">
                                <span class="placeholder">&nbsp;</span>
                            </div>
                        `);
                    <? } ?>

                    <? if (!empty($challenges_to_display_next_href)){ ?>
                        document.write(`
                            <div class="link-cell">
                                <a class="banner-link" href="<?= $challenges_to_display_next_href ?>">Next</a>
                            </div>
                        `);
                    <? } else { ?>
                        document.write(`
                            <div class="link-cell">
                                <span class="placeholder">&nbsp;</span>
                            </div>
                        `);
                    <? } ?>

                    <? if (empty($published_challenges_index)){ ?>
                        document.write(`
                            <div class="banner-cell">
                                <strong class="banner-caption">none-found</strong>
                                <p class="banner-text">Error: No published challenges found w/ max at <?= $challenges_to_display_max ?> and page at <?= $challenges_to_display_page ?>!</p>
                            </div>
                        `);
                    <? } else { ?>
                        challenges.forEach(challenge => {
                            players.forEach(player => {
                                let imgSrc = `<?= MMRPG_CONFIG_ROOTURL ?>scripts/get-banner.php?kind=challenge&challenge=${challenge}&player=${player}<?= $force_refresh ? '&refresh=true' : '' ?>`;
                                let btnHref = `<?= MMRPG_CONFIG_ROOTURL ?>images/events/challenge-banner_${challenge}_${player}.png`;
                                document.write(`
                                    <div class="banner-cell">
                                        <strong class="banner-caption">${challenge}</strong>
                                        <a class="banner-image" href="${btnHref}" target="_blank">
                                            <img src="${imgSrc}" alt="${challenge} - ${player}" />
                                        </a>
                                    </div>
                                `);
                            });
                        });
                    <? } ?>

                <? } ?>
            </script>
        </div>
    <?
    $html_markup .= ob_get_clean();
    ob_start();
    ?>
    <style type="text/css">
        html {
            background-color: #262626;
            font-size: 16px;
            line-height: 1.6;
            font-family: monospace;
        }
        #mmrpg,
        #admin {
            width: auto;
            max-width: none;
            background-color: transparent;
        }
        #mmrpg #admin > .header,
        #mmrpg #admin > .content > .userinfo {
            display: none;
        }
        #mmrpg #admin > .content {
            padding: 0;
            background-color: transparent;
        }

        .banner-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .banner-cell {
            width: 33.3333%;
            padding: 5px;
            box-sizing: border-box;
        }
        .banner-cell .banner-caption {
            display: block;
            margin: 0 auto 5px;
            width: 100%;
            height: auto;
            font-weight: normal;
            color: white;
            font-size: 90%;
        }
        .banner-cell .banner-text {
            display: block;
            margin: 0 auto;
            width: 100%;
            height: auto;
            font-weight: normal;
            color: white;
            font-size: 90%;
        }
        .banner-cell .banner-image {
            display: block;
            width: 100%;
            height: auto;
            border: 1px solid #1a1a1a;
            border-radius: 5px;
            overflow: hidden;
        }
        .banner-cell .banner-image img {
            display: block;
            box-sizing: border-box;
            width: 100%;
            height: auto;
            margin: 0 auto;
            border: 0 none transparent;
        }
        .link-cell {
            width: 50%;
            padding: 5px;
            box-sizing: border-box;
        }
        .link-cell .banner-link,
        .link-cell .placeholder {
            display: block;
            box-sizing: border-box;
            margin: 0 auto;
            padding: 3px 6px;
            background-color: rgba(255, 255, 255, 0.05);
            color: #efefef;
            text-align: center;
            font-size: 110%;
            line-height: 1.6;
        }
        .link-cell .banner-link {
            background-color: rgba(255, 255, 255, 0.05);
            transition: background-color 0.3s, color 0.3s;
            cursor: pointer;
        }
        .link-cell .placeholder {
            background-color: rgba(255, 255, 255, 0.02);
        }
        .link-cell .banner-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
    </style>
    <?
    $styles_markup .= trim(ob_get_clean());
    ob_start();
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {

            /* ... */

        });
    </script>
    <?
    $scripts_markup .= trim(ob_get_clean());

    // Echo all the markup we've generated here
    echo($styles_markup.PHP_EOL);
    echo($scripts_markup.PHP_EOL);
    echo($html_markup.PHP_EOL);
    //exit();

    ?>

<? $this_page_markup .= ob_get_clean(); ?>
