<? ob_start(); ?>

    <?

    // Predefine variables to hold markup for later
    $html_markup = '';
    $styles_markup = '';
    $scripts_markup = '';

    // Collect the URL arguments
    $allowed_kinds = array('events');
    $allowed_kinds_singular = array('event');
    $kind = !empty($_GET['kind']) && in_array($_GET['kind'], $allowed_kinds) ? $_GET['kind'] : $allowed_kinds[0];
    $kind_singular = $allowed_kinds_singular[array_search($kind, $allowed_kinds)];
    $force_refresh = !empty($_GET['refresh']) && $_GET['refresh'] === 'true' ? true : false;

    // Error message if `kind` or `class` is not provided
    if (!$kind) { die("Error: Missing required `kind` parameter."); }

    // Generate the markup for the sprite filters
    ob_start();
    ?>
        <div class="banner-container">
            <!-- Dynamically Generated Banners -->
            <script>
                const players = ['dr-light', 'dr-wily', 'dr-cossack'];
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
                        document.write(`
                            <div class="banner-cell">
                                <strong class="banner-caption">${chapter}</strong>
                                <img class="banner-image" src="${imgSrc}" alt="${chapter} - ${player}" />
                            </div>
                        `);
                    });
                });
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
            width: 100%;
            height: auto;
            font-weight: normal;
            color: white;
            font-size: 90%;
        }
        .banner-cell .banner-image {
            width: 100%;
            height: auto;
            border: 1px solid #1a1a1a;
            border-radius: 5px;
            overflow: hidden;
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
    exit();

    ?>

<? $this_page_markup .= ob_get_clean(); ?>

