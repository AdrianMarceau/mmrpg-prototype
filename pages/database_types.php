<?
/*
 * TYPES DATABASE PAGE
 */

// Define the SEO variables for this page
$this_seo_title = 'Types | Database | '.$this_seo_title;
$this_seo_description = 'The robots, abilities, fields, and many other aspects of the Mega Man RPG are designed around '.($mmrpg_database_types_count_actual + 1).' predefined "types" that represent various elemental affinities and/or methods of attack in the game. Knowing these types can mean the difference between victory and defeat in certain situations, so using this database and the in-game scan option are encouraged. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Type Database';
$this_graph_data['description'] = 'The robots, abilities, fields, and many other aspects of the Mega Man RPG are designed around '.($mmrpg_database_types_count_actual + 1).' predefined "types" that represent various elemental affinities and/or methods of attack in the game. Knowing these types can mean the difference between victory and defeat in certain situations, so using this database and the in-game scan option are encouraged.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Type Database';
//$this_markup_counter = '<span class="count">( '.(!empty($mmrpg_database_types_count) ? ($mmrpg_database_types_count == 1 ? '1 Type' : ($mmrpg_database_types_count).' Types') : '0 Types').' )';

// If arguments were included in the header, apply them
$allow_class = array('all', 'master', 'mecha', 'boss');
$filter_incomplete = isset($_GET['incomplete']) && $_GET['incomplete'] == 'include' ? false : true;
$filter_class = !empty($_GET['class']) && in_array($_GET['class'], $allow_class) ? $_GET['class'] : 'all';

// Generate filter strings for the robot queries
$robot_filters = '';
if ($filter_incomplete){ $robot_filters .= "AND robot_flag_complete = 1 "; }
if ($filter_class != 'all'){ $robot_filters .= "AND robot_class = '{$filter_class}' "; }

// Generate filter strings for the ability queries
$ability_filters = '';
if ($filter_incomplete){ $ability_filters .= "AND ability_flag_complete = 1 "; }
if ($filter_class != 'all'){ $ability_filters .= "AND ability_class = '{$filter_class}' "; }

// Collect an array of type statistics for distribution of robot cores, ability types, weaknesses, etc.
$filtered_type_stats = $db->get_array_list("SELECT
    types.type_id,
    (CASE WHEN types.type_name = 'None' THEN 'Neutral' ELSE types.type_name END) AS type_name,
    types.type_token,
    types.type_colour_light,
    types.type_colour_dark,

    -- Robot Cores
    -- IFNULL(robots.robot_count, 0) AS robot_count,
    -- IFNULL(robots2.robot_count, 0) AS robot_count2,
    @robot_count := (IFNULL(robots.robot_count, 0) + IFNULL(robots2.robot_count, 0)) AS robot_count,
    ROUND((((@robot_count) / robots3.robot_total) * 100), 1) AS robot_percent,

    -- Ability Types
    -- IFNULL(abilities.ability_count, 0) AS ability_count,
    -- IFNULL(abilities2.ability_count, 0) AS ability_count2,
    @ability_count := (IFNULL(abilities.ability_count, 0) + IFNULL(abilities2.ability_count, 0)) AS ability_count,
    ROUND((((@ability_count) / abilities3.ability_total) * 100), 1) AS ability_percent,

    -- Robot Weaknesses
    @weakness_count := (SELECT
        COUNT(*) AS robot_count
        FROM mmrpg_index_robots AS robots
        WHERE
        robot_id > 0
        {$robot_filters}
        AND robot_weaknesses LIKE CONCAT('%\"', types.type_token, '\"%')
        ) AS weakness_count,
    ROUND((((@weakness_count) / robots3.robot_total) * 100), 1) AS weakness_percent,

    -- Robot Resistances
    @resistance_count := (SELECT
        COUNT(*) AS robot_count
        FROM mmrpg_index_robots AS robots
        WHERE
        robot_id > 0
        {$robot_filters}
        AND robot_resistances LIKE CONCAT('%\"', types.type_token, '\"%')
        ) AS resistance_count,
    ROUND((((@resistance_count) / robots3.robot_total) * 100), 1) AS resistance_percent,

    -- Robot Affinities
    @affinity_count := (SELECT
        COUNT(*) AS robot_count
        FROM mmrpg_index_robots AS robots
        WHERE
        robot_id > 0
        {$robot_filters}
        AND robot_affinities LIKE CONCAT('%\"', types.type_token, '\"%')
        ) AS affinity_count,
    ROUND((((@affinity_count) / robots3.robot_total) * 100), 1) AS affinity_percent,

    -- Robot Immunities
    @immunity_count := (SELECT
        COUNT(*) AS robot_count
        FROM mmrpg_index_robots AS robots
        WHERE
        robot_id > 0
        {$robot_filters}
        AND robot_immunities LIKE CONCAT('%\"', types.type_token, '\"%')
        ) AS immunity_count,
    ROUND((((@immunity_count) / robots3.robot_total) * 100), 1) AS immunity_percent,

    types.type_class

    FROM mmrpg_index_types AS types

    -- Robot Cores
    LEFT JOIN (
        SELECT
        DISTINCT robot_core,
        COUNT(*) AS robot_count
        FROM mmrpg_index_robots AS robots
        WHERE
        robot_id > 0
        {$robot_filters}
        GROUP BY robot_core
        ORDER BY robot_core
        ) AS robots ON (
            robots.robot_core = types.type_token
            OR robots.robot_core = '' AND types.type_token = 'none'
            )
    LEFT JOIN (
        SELECT
        DISTINCT robot_core2,
        COUNT(*) AS robot_count
        FROM mmrpg_index_robots AS robots
        WHERE
        robot_id > 0
        {$robot_filters}
        AND robot_core2 <> ''
        GROUP BY robot_core2
        ORDER BY robot_core2
        ) AS robots2 ON robots2.robot_core2 = types.type_token
    CROSS JOIN (
        SELECT
        COUNT(*) AS robot_total
        FROM mmrpg_index_robots AS robots
        WHERE
        robot_id > 0
        {$robot_filters}
        ) AS robots3

    -- Ability Types
    LEFT JOIN (
        SELECT
        DISTINCT ability_type,
        COUNT(*) AS ability_count
        FROM mmrpg_index_abilities AS abilities
        WHERE
        ability_id > 0
        {$ability_filters}
        GROUP BY ability_type
        ORDER BY ability_type
        ) AS abilities ON (
            abilities.ability_type = types.type_token
            OR abilities.ability_type = '' AND types.type_token = 'none'
            )
    LEFT JOIN (
        SELECT
        DISTINCT ability_type2,
        COUNT(*) AS ability_count
        FROM mmrpg_index_abilities AS abilities
        WHERE
        ability_id > 0
        {$ability_filters}
        AND ability_type <> ''
        AND ability_type2 <> ''
        GROUP BY ability_type2
        ORDER BY ability_type2
        ) AS abilities2 ON abilities2.ability_type2 = types.type_token
    CROSS JOIN (
        SELECT
        COUNT(*) AS ability_total
        FROM mmrpg_index_abilities AS abilities
        WHERE
        ability_id > 0
        {$ability_filters}
        ) AS abilities3

    WHERE
    type_id > 0
    AND (types.type_class = 'normal' OR types.type_token = 'none')
    ORDER BY
    types.type_order ASC
    ;", 'type_token');

// Count the total number of filtered robots represented
$filtered_robot_total = $db->get_value("SELECT
    COUNT(*) AS robot_count
    FROM mmrpg_index_robots AS robots
    WHERE
    robot_id > 0
    {$robot_filters}
    ;", 'robot_count');

// Count the total number of abilities represented
$filtered_ability_total = $db->get_value("SELECT
    COUNT(*) AS ability_count
    FROM mmrpg_index_abilities AS abilities
    WHERE
    ability_id > 0
    {$ability_filters}
    ;", 'ability_count');

// Define the stat types we should be tracking
$stat_categories = array('robot', 'ability', 'weakness', 'resistance', 'affinity', 'immunity');
// Loop through collected type stats and break down into categories
$type_stats_index = array();
foreach ($filtered_type_stats AS $type_token => $type_info){
    foreach ($stat_categories AS $category_key => $category_token){
        if (!isset($type_stats_index[$category_token])){ $type_stats_index[$category_token] = array(); }
        if (empty($type_info[$category_token.'_count'])){ continue; }
        $type_stats_index[$category_token][$type_token] = $type_info[$category_token.'_count'];
    }
}
// Loop through and sort each category with largest first
foreach ($type_stats_index AS $category_token => $category_counts){
    arsort($category_counts);
    $type_stats_index[$category_token] = $category_counts;
}

//echo('<pre>$filtered_robot_total = '.print_r($filtered_robot_total, true).'</pre>');
//echo('<pre>$filtered_ability_total = '.print_r($filtered_ability_total, true).'</pre>');
//echo('<pre>$filtered_type_stats = '.print_r($filtered_type_stats, true).'</pre>');
//echo('<pre>$stat_categories = '.print_r($stat_categories, true).'</pre>');
//echo('<pre>$type_stats_index = '.print_r($type_stats_index, true).'</pre>');
//die();

?>
<div id="types">
    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Types Overview</h2>
    <div class="subbody">
        <p class="text">
            Robots, abilities, and even locations in the <strong>Mega Man RPG Prototype</strong> are represented by and categorized into <?= $mmrpg_database_types_count ?> different elemental types.  These types play an important role in the game and have notable effects in and out of battle.  Some types are more common than others, but all have their uses.
        </p>
        <p class="text">
            A robot's core type determines which abilities it can equip and its proficiency with those abilities in battle.  Ability types can trigger the weaknesses, resistances, and even immunities of target robots to influence damage and recovery amounts in battle. Even fields have their own elemental affinities!
        </p>
        <p class="text">
            While many robots and abilities fall under one type it is not uncommon for a character or weapon to have a secondary element. Diverse type combos and matchups are at the heart of the <strong>Prototype</strong> and experimentation is highly encouraged.
        </p>
        <ul style="overflow: hidden; padding: 4px 0 6px;">
            <?
            // Loop through and display all the types to the user
            echo '<li><strong class="type_block ability_type ability_type_none">Neutral</strong></li>';
            foreach ($mmrpg_database_types AS $type_token => $type_array){
                if ($type_token == 'none'){ continue; }
                echo '<li><strong class="type_block ability_type ability_type_'.$type_token.'">'.ucfirst($type_token).'</strong></li>';
            }
            ?>
        </ul>
    </div>
    <a class="anchor" id="charts"></a>
    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Type Distributions</h2>
    <div class="subbody">
        <p class="text">Given the importance of these elemental types, knowing their in-game distribution may be useful.  The stats and values in the graphs below pull directly from the game's database and can be used as a reference when preparing for battle.</p>
        <?
        // Define a function for generating filter URLs
        function get_filter_url($args){
            global $filter_incomplete;
            //global $filter_neutral;
            global $filter_class;
            $filter_url = '';
            $filter_url .= 'database/types/';
            if (!isset($args['incomplete'])){ $args['incomplete'] = $filter_incomplete; }
            //if (!isset($args['neutral'])){ $args['neutral'] = $filter_neutral; }
            if (!isset($args['class'])){ $args['class'] = $filter_class; }
            if (!$args['incomplete']){ $filter_url .= '&incomplete=include'; }
            //if (!$args['neutral']){ $filter_url .= '&neutral=show'; }
            if (!empty($args['class'])){$filter_url .= '&class='.$args['class'];  }
            $filter_url .= '#charts';
            return $filter_url;
        }
        ?>
        <script type="text/javascript">
            var typeChartData = {};
        </script>
        <div class="type_chart_filters">
            <div class="classes">
                <a class="link <?= $filter_class == 'all' ? 'active' : '' ?>" href="<?= get_filter_url(array('class' => 'all')) ?>" rel="noindex,nofollow"><span>Show</span> All</a>
                <span class="pipe">|</span>
                <a class="link <?= $filter_class == 'master' ? 'active' : '' ?>" href="<?= get_filter_url(array('class' => 'master')) ?>" rel="noindex,nofollow"><span>Robot</span> Masters</a>
                <span class="pipe">|</span>
                <a class="link <?= $filter_class == 'mecha' ? 'active' : '' ?>" href="<?= get_filter_url(array('class' => 'mecha')) ?>" rel="noindex,nofollow"><span>Support</span> Mechas</a>
                <span class="pipe">|</span>
                <a class="link <?= $filter_class == 'boss' ? 'active' : '' ?>" href="<?= get_filter_url(array('class' => 'boss')) ?>" rel="noindex,nofollow"><span>Fortress</span> Bosses</a>
            </div>
            <div class="flags">
                <? if ($filter_incomplete == true): ?>
                    <a class="link" href="<?= get_filter_url(array('incomplete' => false)) ?>" rel="noindex,nofollow"><span class="check">&#9744;</span> <span>Include</span> Incomplete</a>
                <? else: ?>
                    <a class="link" href="<?= get_filter_url(array('incomplete' => true)) ?>" rel="noindex,nofollow"><span class="check">&#9745;</span> <span>Include</span> Incomplete</a>
                <? endif; ?>
            </div>
        </div>
        <div class="type_chart_wrapper">

            <?
            // Loop through and print markup for the various type stats
            $category_key = 0;
            foreach ($type_stats_index AS $category_token => $category_counts){

                // Print a divider at a specific point in the rows
                if ($category_key == 2){
                    ?>
                    <div class="link_wrapper" style="overflow: hidden;">
                        <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    </div>
                    <?
                }

                // Define the class and title based on category
                if ($filter_class == 'all'){ $title_prefix = 'Robot'; }
                elseif ($filter_class == 'master'){ $title_prefix = 'Master'; }
                elseif ($filter_class == 'mecha'){ $title_prefix = 'Mecha'; }
                elseif ($filter_class == 'boss'){ $title_prefix = 'Boss'; }
                if ($category_token == 'robot'){
                    $chart_class = 'cores';
                    $chart_title = $title_prefix.' Core Types';
                }
                elseif ($category_token == 'ability'){
                    $chart_class = 'abilities';
                    $chart_title = $title_prefix.' Ability Types';
                }
                elseif ($category_token == 'weakness'){
                    $chart_class = 'weaknesses';
                    $chart_title = $title_prefix.' Weaknesses';
                }
                elseif ($category_token == 'resistance'){
                    $chart_class = 'resistances';
                    $chart_title = $title_prefix.' Resistances';
                }
                elseif ($category_token == 'affinity'){
                    $chart_class = 'affinities';
                    $chart_title = $title_prefix.' Afffiities';
                }
                elseif ($category_token == 'immunity'){
                    $chart_class = 'immunities';
                    $chart_title = $title_prefix.' Immunities';
                }

                // Define other attributes based on category
                if ($category_token == 'ability'){
                    $chart_total = $filtered_ability_total;
                    $chart_object = 'ability';
                    $chart_objects = 'abilities';
                } else {
                    $chart_total = $filtered_robot_total;
                    $chart_object = 'robot';
                    $chart_objects = 'robots';
                }

                // Define the count and percent tokens
                $count_key = $category_token.'_count';
                $percent_key = $category_token.'_percent';

                ?>

                <div class="type_chart type_chart_<?= $chart_class ?>">
                    <strong class="category"><?= $chart_title ?></strong>
                    <? if ($category_token == 'robot' || $category_token == 'ability'): ?>
                        <span class="counter"><?= $chart_total != 1 ? $chart_total.' '.ucfirst($chart_objects) : '1 '.ucfirst($chart_object)  ?> Total</span>
                    <? endif; ?>
                    <div class="text wrapper">

                        <div class="chart_wrapper">
                            <canvas class="chart_canvas" data-kind="<?= $category_token ?>" width="400" height="400"></canvas>
                            <script type="text/javascript">

                                <?

                                // Define the chart type and options
                                if ($category_token == 'robot' || $category_token == 'ability'){
                                    $chart_type = 'bar';
                                    $chart_options = '{
                                        legend: {
                                            display: false
                                            },
                                        scales: {
                                            yAxes: [{
                                                ticks: {
                                                    beginAtZero:true
                                                    }
                                                }]
                                            }
                                        }';

                                } else {
                                    $chart_type = 'pie';
                                    $chart_options = '{
                                        legend: {
                                            display: false,
                                            position: \'bottom\'
                                            }
                                        }';
                                }

                                // Collect the data for this type chart
                                $type_labels = array();
                                $type_counts = array();
                                $type_backgrounds = array();
                                $type_borders = array();
                                foreach($type_stats_index[$category_token] AS $type_token => $type_count){
                                    $type_info = $filtered_type_stats[$type_token];
                                    $type_labels[] = $type_info['type_name'];
                                    $type_counts[] = $type_info[$count_key];
                                    $type_backgrounds[] = 'rgba('.trim($type_info['type_colour_light'], '[]').', 1.0)';
                                    $type_borders[] = 'rgba('.trim($type_info['type_colour_dark'], '[]').', 1.0)';
                                }

                                ?>

                                typeChartData['<?= $category_token ?>'] = {
                                    type: '<?= $chart_type ?>',
                                    data: {
                                        labels: <?= json_encode($type_labels) ?>,
                                        datasets: [{
                                            label: '<?= ucfirst($chart_class) ?>',
                                            data: <?= json_encode($type_counts) ?>,
                                            backgroundColor: <?= json_encode($type_backgrounds) ?>,
                                            borderColor: <?= json_encode($type_borders) ?>,
                                            borderWidth: 1
                                            }]
                                        },
                                    options: <?= $chart_options ?>
                                    };

                            </script>
                        </div>

                    </div>
                </div>

                <?
                $category_key++;
            }
            ?>

            <div class="link_wrapper">
                <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
            </div>

        </div>
    </div>
</div>
<?
?>