<?

/*
 * DEV TESTS / VOID MISSIONS
 */

// Define the constant that puts the front-end in compact mode
define('MMRPG_INDEX_COMPACT_MODE', true);

// Define the SEO variables for this page
$this_seo_title = 'Void Mission Generator V1 | '.$this_seo_title;
$this_seo_description = 'An experimental void mission generator for the MMRPG.';
$this_seo_robots = 'noindex,nofollow';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Void Mission Generator V1';
$this_graph_data['description'] = 'An experimental void mission generator for the MMRPG.';

// Pre-collect the indexes once up here so we don't do it again
$mmrpg_index_types = rpg_type::get_index(true);
$mmrpg_index_players = rpg_player::get_index(true);
$mmrpg_index_robots = rpg_robot::get_index(true);
$mmrpg_index_abilities = rpg_ability::get_index(true);
$mmrpg_index_items = rpg_item::get_index(true);
$mmrpg_index_fields = rpg_field::get_index(true);

// Define a function for debugging this script, printing to error log w/ line number
if (!function_exists('console_log')){
    function console_log($line, $msg = 'checkpoint'){
        error_log('('.(basename(dirname(__FILE__)).'/'.basename(__FILE__)).'::'.$line.') '.$msg);
    }
}

// Define a function that inserts a new element into an associative array after a given key position
if (!function_exists('array_insert_after_key')){
    function array_insert_after_key(&$parent_array, $parent_key, $child_array, $child_key) {
        $new_array = [];
        foreach ($parent_array as $key => $value) {
            $new_array[$key] = $value;
            if ($key === $parent_key){ $new_array[$child_key] = $child_array; }
        }
        $parent_array = $new_array;
    }
}

// Define a function that inserts a new element into an associative array before a given key position
if (!function_exists('array_insert_before_key')){
    function array_insert_before_key(&$parent_array, $parent_key, $child_array, $child_key) {
        $new_array = [];
        foreach ($parent_array as $key => $value) {
            if ($key === $parent_key) { $new_array[$child_key] = $child_array; }
            $new_array[$key] = $value;
        }
        $parent_array = $new_array;
    }
}

// Define a function that takes a parent array and they rearranges the provided keys in the order provided
if (!function_exists('array_rearrange_keys')){
    function array_rearrange_keys(&$parent_array, $ordered_keys) {
        $new_array = [];
        $ordered_items = [];
        // Collect items by the specified order
        foreach ($ordered_keys as $key) {
            if (array_key_exists($key, $parent_array)) {
                $ordered_items[$key] = $parent_array[$key];
                unset($parent_array[$key]);
            }
        }
        // Build the new array with ordered items in specified sequence
        $keys_added = false;
        foreach ($parent_array as $key => $value) {
            if (!$keys_added && empty($new_array)) {
                $new_array = array_merge($ordered_items, $new_array);
                $keys_added = true;
            }
            $new_array[$key] = $value;
        }
        // If $ordered_keys appear later in $parent_array, append them to the end
        if (!$keys_added) {
            $new_array = array_merge($new_array, $ordered_items);
        }
        $parent_array = $new_array;
    }
}

// Pre-filter the list of items compatible with void recipes for later
$mmrpg_index_items_filtered = array();
foreach($mmrpg_index_items as $item_token => $item_info){
    // Skip this item if it's an event item or a special token
    if (empty($item_info['item_flag_published'])){ continue; }
    elseif (empty($item_info['item_flag_complete'])){ continue; }
    elseif (!empty($item_info['item_flag_hidden'])){ continue; }
    elseif ($item_info['item_subclass'] === 'event'){ continue; }
    elseif (substr($item_token, -6) === '-shard'){ continue; }
    elseif (substr($item_token, -5) === '-star'){ continue; }
    // Otherwise add it to the filtered list of items
    $mmrpg_index_items_filtered[$item_token] = $item_info;
}
$mmrpg_index_items = $mmrpg_index_items_filtered;

// Predefine the item groups to be used in the void cauldron item palette
$void_item_groups_index = array(

    // -- STEP ONE (MANIFEST [DATA]) -- //
    array(
        'name' => 'Manifest',
        'label' => 'Data',
        'groups' => array(
            'quanta-screws' => array(
                'name' => 'Quanta Screws',
                'color' => 'water',
                'rowline' => 1,
                'colspan' => 3,
                'items' => array(
                    'small-screw', 'large-screw', 'hyper-screw',
                    )
                ),
            'spread-cores' => array(
                'name' => 'Spread Cores',
                'color' => 'laser',
                'rowline' => 2,
                'colspan' => 5,
                'items' => array(
                    'cutter-core', 'impact-core', 'freeze-core', 'explode-core', 'flame-core',
                    'electric-core', 'time-core', 'earth-core', 'wind-core', 'water-core',
                    'swift-core', 'nature-core', 'missile-core', 'crystal-core', 'shadow-core',
                    'space-core', 'shield-core', 'laser-core', 'copy-core', 'none-core',
                    )
                ),
            'quanta-balancers' => array(
                'name' => 'Quanta Balancers',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 1,
                'items' => array(
                    'charge-module',
                    )
                ),
            'spread-balancers' => array(
                'name' => 'Spread Balancers',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 2,
                'items' => array(
                    'spreader-module', 'target-module',
                    )
                ),
            'view-mods' => array(
                'name' => 'View Mods',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 1,
                'items' => array(
                    'hyperscan-module',
                    ),
                ),
            ),
        ),

    // -- STEP TWO (REDIRECT [FORM]) -- //
    array(
        'name' => 'Redirect',
        'label' => 'Form',
        'groups' => array(
            'specstat-upgrades' => array(
                'name' => 'SpecStat Upgrades',
                'color' => 'defense_energy',
                'rowline' => 1,
                'colspan' => 2,
                'items' => array(
                    'energy-upgrade',
                    'weapon-upgrade',
                    )
                ),
            'tristat-mods' => array(
                'name' => 'TriStat Mods',
                'color' => 'speed_attack',
                'rowline' => 2,
                'colspan' => 3,
                'items' => array(
                    'attack-booster', 'defense-booster', 'speed-booster',
                    'attack-diverter', 'defense-diverter', 'speed-diverter',
                    )
                ),
            'queue-rotators' => array(
                'name' => 'Queue Rotators',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 3,
                'items' => array(
                    'mecha-whistle', 'extra-life', 'yashichi',
                    ),
                ),
            'queue-rotators2' => array(
                'name' => 'Queue Rotators 2',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 1,
                'items' => array(
                    'field-booster',
                    )
                ),
            'queue-mods' => array(
                'name' => 'Queue Mods',
                'color' => 'copy',
                'rowline' => 4,
                'colspan' => 2,
                'items' => array(
                    'copycat-module', 'reverse-module',
                    ),
                ),
            ),
        ),

    // -- STEP THREE (UPGRADE [POWER]) -- //
    array(
        'name' => 'Upgrade',
        'label' => 'Power',
        'groups' => array(
            'specstat-eats' => array(
                'name' => 'SpecStat Edibles',
                'color' => 'electric',
                'rowline' => 1,
                'colspan' => 3,
                'items' => array(
                    'energy-pellet', 'energy-capsule', 'energy-tank',
                    'weapon-pellet', 'weapon-capsule', 'weapon-tank',
                    )
                ),
            'tristat-eats' => array(
                'name' => 'TriStat Edibles',
                'color' => 'shield',
                'rowline' => 2,
                'colspan' => 4,
                'items' => array(
                    'attack-pellet', 'defense-pellet', 'speed-pellet', 'super-pellet',
                    'attack-capsule', 'defense-capsule', 'speed-capsule', 'super-capsule',
                    )
                ),
            'reward-mods' => array(
                'name' => 'Reward Mods',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 3,
                'items' => array(
                    'salvage-module', 'growth-module', 'fortune-module',
                    )
                ),
            ),
        ),

    // -- STEP FOUR (DISTORT [CONTEXT]) -- //
    array(
        'name' => 'Distort',
        'label' => 'Context',
        'groups' => array(
            'elemental-mods' => array(
                'name' => 'Elemental Mods',
                'color' => 'time',
                'rowline' => 1,
                'colspan' => 3,
                'items' => array(
                    'battery-circuit', 'sponge-circuit', 'forge-circuit',
                    'sapling-circuit', 'chrono-circuit', 'cosmo-circuit',
                    )
                ),
            'junk' => array(
                'name' => 'Junk Items',
                'color' => 'time',
                'rowline' => 2,
                'colspan' => 4,
                'items' => array(
                    'guard-module', 'persist-module', 'xtreme-module', 'overkill-module',
                    'hourglass-module', 'magnet-module', 'transport-module', 'bulwark-module',
                    )
                ),
            'power-balancers' => array(
                'name' => 'Power Balancers',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 1,
                'items' => array(
                    'uptick-module', 'siphon-module',
                    )
                ),
            'field-mods' => array(
                'name' => 'Field Mods',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 1,
                'items' => array(
                    'repair-module', 'gambit-module',
                    )
                ),
            'field-mods2' => array(
                'name' => 'Field Mods 2',
                'color' => 'copy',
                'rowline' => 3,
                'colspan' => 1,
                'items' => array(
                    'alchemy-module', 'distill-module',
                    )
                ),
            ),
        ),

    );

?>
<div class="header">
    <div class="header_wrapper">
        <h1 class="title"><span class="brand">Mega Man RPG</span><span> Void Missions</span></h1>
    </div>
</div>
<h2 class="subheader field_type_<?= !empty($this_field_info['field_type']) ? $this_field_info['field_type'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    Experimental Procedural Mission Generator
</h2>

<div class="subbody">

    <h3 class="subheader">
        Void Recipe Calculator <sup style="margin-left: 4px; font-size: 60%; position: relative; bottom: 6px;">v1</sup>
    </h3>
    <div class="subbody">
        <div class="text">
            <p>
                Void Missions are procedurally generated missions being added to MMRPG.
                Throw items into The Void to generate a temporary customized mission.
                Each kinds and quantities have different effects on mission targets.
            </p>
        </div>
    </div>

    <?

    // Start the output buffer to collect generated item markup
    ob_start();

        // NEW VERSION:
        // Loop through each of the steps in the index (then each of the groups within those steps), to generate
        // the markup for the item-pallet's wrappers, group containers, and item buttons that will be clicked on
        $num_items_total = 0;
        $curr_item_rowline = 0;
        $group_markup_by_step = array();
        foreach ($void_item_groups_index AS $step_key => $step_info){
            $step_num = $step_key + 1;
            $step_name = $step_info['name'];
            $step_label = $step_info['label'];
            $step_groups = $step_info['groups'];
            if (empty($step_groups)){ continue; }
            $group_markup_by_step[$step_key] = array();
            foreach ($step_groups AS $group_token => $group_info){
                $group_name = $group_info['name'];
                $group_color = $group_info['color'];
                $group_items = $group_info['items'];
                $group_rowline = $group_info['rowline'];
                $group_colspan = $group_info['colspan'];
                if (empty($group_items)){ continue; }
                $group_items_markup = array();
                foreach ($group_items AS $item_key => $item_token){
                    if (!isset($mmrpg_index_items[$item_token])){ continue; }
                    $item_info = $mmrpg_index_items[$item_token];
                    $item_name = $item_info['item_name'];
                    $item_name_br = str_replace(' ', '<br />', $item_name);
                    $item_is_oneline = !strstr($item_name, ' ');
                    $item_quantity = mt_rand(33, 99); //mt_rand(0, 99);
                    $item_image = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_token;
                    $icon_url = '/images/items/'.$item_image.'/icon_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
                    ob_start();
                    echo('<div class="item" '.
                        'data-key="'.$item_key.'" '.
                        'data-token="'.$item_token.'" '.
                        'data-group="'.$group_token.'" '.
                        'data-quantity="'.$item_quantity.'" '.
                        'style="z-index: 0;" '.
                        '>');
                        echo('<div class="icon"><img class="has_pixels" src="'.$icon_url.'" alt="'.$item_name.'"></div>');
                        echo('<div class="name '.($item_is_oneline ? 'one-line' : '').'">'.$item_name_br.'</div>');
                        echo('<div class="quantity">'.$item_quantity.'</div>');
                    echo('</div>');
                    $group_items_markup[] = ob_get_clean();
                }
                if (empty($group_items_markup)){ continue; }
                $added_so_far = count($group_markup_by_step[$step_key]);
                $add_newline = $group_rowline !== $curr_item_rowline && $added_so_far >= 1 ? true : false;
                $group_markup = implode(PHP_EOL, $group_items_markup);
                $group_markup_class = 'group '.$group_token.' type '.$group_color;
                $group_markup_attrs = 'data-group="'.$group_token.'" data-count="'.count($group_items).'"';
                $group_markup_attrs .= ' data-rowline="'.$group_rowline.'" data-colspan="'.$group_colspan.'"';
                $wrapped_group_markup = '<div class="'.$group_markup_class.'" '.$group_markup_attrs.'>'.PHP_EOL.$group_markup.PHP_EOL.'</div>';
                if ($add_newline){ $wrapped_group_markup = '<div class="clear"></div>'.PHP_EOL.$wrapped_group_markup; }
                $group_markup_by_step[$step_key][] = $wrapped_group_markup;
                //console_log(__LINE__, 'adding wrapped group markup for '.$group_token.' to step '.$step);
                $num_items_total += count($group_items);
                $curr_item_rowline = $group_rowline;
            }
        }
        $z_index = count($group_markup_by_step) + 11;
        foreach ($group_markup_by_step AS $step_key => $wrapped_group_markup){
            ob_start();
            $step_info = $void_item_groups_index[$step_key];
            $step_num = $step_key + 1;
            $step_name = $step_info['name'];
            $step_label = $step_info['label'];
            $wrapper_class = 'wrapper'.($step_num === 1 ? ' active' : '');
            $wrapper_attrs = 'data-step="'.$step_num.'"';
            echo('<div class="'.$wrapper_class.'" '.$wrapper_attrs.'>'.PHP_EOL);
                echo('<div class="label">'.$step_name.' ('.$step_label.')</div>'.PHP_EOL);
                echo(implode(PHP_EOL, $wrapped_group_markup).PHP_EOL);
            echo('</div>'.PHP_EOL);
            $group_items_markup = ob_get_clean();
            if (!empty($group_items_markup)){
                echo($group_items_markup);
            }
        }
        //console_log(__LINE__, '$void_item_groups_index = '.print_r($void_item_groups_index, true));
        //console_log(__LINE__, '$group_markup_by_step = '.print_r($group_markup_by_step, true));

    // Collect the generated markup for the item palette from the buffer and save it to a variable
    $items_palette_markup = ob_get_clean();
    $items_palette_count = $num_items_total;

    ?>

    <div id="void-recipe">
        <div class="title">
            <strong class="main">Void Cauldron</strong>
            <em class="sub">Procedural Mission Generator</em>
        </div>
        <div class="creation">
            <div class="mission-details">
                <span class="loading">&hellip;</span>
            </div>
            <div class="target-list">
                <span class="loading">&hellip;</span>
            </div>
            <div class="battle-field">
                <div class="sprite background memory-filter"
                    data-token="prototype-subspace"
                    style="background-image: url(/images/fields/gentle-countryside/battle-field_preview.png?20241104-0121);"
                    >&nbsp;</div>
            </div>
        </div>
        <div class="palette">
            <div class="item-list" data-count="<?= $items_palette_count ?>" data-select="*" data-step="1">
                <?= $items_palette_markup ?>
            </div>
        </div>
        <div class="selection">
            <div class="item-list" data-count="0">
                <div class="wrapper float-left">
                    <span class="loading">&hellip;</span>
                </div>
            </div>
            <a class="reset"><i class="fa fas fa-undo"></i></a>
        </div>
    </div>

    <div class="subbody">
        <div class="legend">
            <ul>
                <li>SCREWS = BALANCED QUANTA/SPREAD</li>
                <li>CORES = TYPE-FILTERING + HIGH-SPREAD/LOW-QUANTA</li>
                <li>EDIBLES = STAT-SORTING + HIGH-QUANTA/LOW-SPREAD</li>
                <li>CIRCUITS = TYPE-FILTERING || BOOSTERS = STAT-SORTING</li>
                <li>MYTHICS = LEVEL/FORTE || MODULES = MISC EFFECTS</li>
            </ul>
        </div>
    </div>

</div>

<?

// Include the stylesheet markup for this page as if it were inline
ob_start();
echo('<style type="text/css">'.PHP_EOL);
require_once('pages/dev_void-missions-v1_styles.css.php');
echo('</style>'.PHP_EOL);
$website_include_stylesheets .= ob_get_clean();

// Include the javascript marup for this page as if it were inline
ob_start();
echo('<script type="text/javascript">'.PHP_EOL);
require_once('pages/dev_void-missions-v1_scripts.js.php');
echo('</script>'.PHP_EOL);
$website_include_javascript .= ob_get_clean();

?>