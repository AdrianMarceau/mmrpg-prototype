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

    <?

    // Start the output buffer to collect generated item markup
    ob_start();

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

        // TEMP TEMP TEMP (hyper-screw placeholder)
        if (true){
            $copy_from = 'large-screw';
            $pseudo_item = array();
            $pseudo_item['item_token'] = 'hyper-screw';
            $pseudo_item['item_name'] = 'Hyper Screw';
            $pseudo_item['item_image'] = $copy_from;
            $pseudo_item = array_merge($mmrpg_index_items[$copy_from], $pseudo_item);
            $mmrpg_index_items['hyper-screw'] = $pseudo_item;
        }

        // Re-arrange certain groups of keys to make them better-suited for display
        array_rearrange_keys($mmrpg_index_items, array('none-core', 'cutter-core'));
        array_rearrange_keys($mmrpg_index_items, array('energy-tank', 'weapon-tank', 'energy-upgrade', 'weapon-upgrade', 'extra-life', 'yashichi'));
        array_rearrange_keys($mmrpg_index_items, array('field-booster', 'attack-booster', 'defense-booster', 'speed-booster'));
        array_rearrange_keys($mmrpg_index_items, array('charge-module', 'target-module'));

        // Pre-define the order of the groups for display purposes
        $group_display_order = array(
            'left' => array(
                'screws', 'special',
                'pellets',
                'capsules',
                'energies', 'circuits',
                ),
            'right' => array(
                'cores',
                'diverters', 'boosters',
                'modules', 'fillers',
                'other'
                )
            );

        // Loop through and display a list of all the items available to add to the void
        $elements_html_grouped = array();
        foreach($mmrpg_index_items as $item_token => $item_info){
            // Break apart the item token into individual words so we can better determine its group
            $item_tokens = strstr($item_token, '-') ? explode('-', $item_token) : array($item_token);
            if (!isset($item_tokens[1])){ $item_tokens[1] = '';  }
            // Default to the 'other' group but then check tokens to better categorize
            $group_token = 'other';
            if ($item_tokens[1] === 'screw'){ $group_token = 'screws'; }
            elseif ($item_tokens[1] === 'core'){ $group_token = 'cores'; }
            elseif (in_array($item_token, array('charge-module', 'target-module'))){ $group_token = 'special'; }
            elseif (in_array($item_token, array('energy-tank', 'weapon-tank', 'energy-upgrade', 'weapon-upgrade', 'extra-life', 'yashichi'))){ $group_token = 'energies'; }
            elseif (in_array($item_token, array('field-booster'))){ $group_token = 'fillers'; }
            elseif ($item_tokens[1] === 'pellet'){ $group_token = 'pellets'; }
            elseif ($item_tokens[1] === 'capsule'){ $group_token = 'capsules'; }
            elseif ($item_tokens[1] === 'tank'){ $group_token = 'tanks'; }
            elseif ($item_tokens[1] === 'upgrade'){ $group_token = 'upgrades'; }
            elseif ($item_tokens[1] === 'booster'){ $group_token = 'boosters'; }
            elseif ($item_tokens[1] === 'diverter'){ $group_token = 'diverters'; }
            elseif ($item_tokens[1] === 'module'){ $group_token = 'modules'; }
            elseif ($item_tokens[1] === 'circuit'){ $group_token = 'circuits'; }
            // Collect the quantity and other details we need to display the button markup
            $item_quantity = mt_rand(0, 99);
            $item_name = $item_info['item_name'];
            $item_name_br = str_replace(' ', '<br />', $item_name);
            $item_is_oneline = !strstr($item_name, ' ');
            $item_image = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_token;
            $icon_url = '/images/items/'.$item_image.'/icon_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
            // TEMP TEMP TEMP (hyper-screw placeholder)
            if ($item_token === 'hyper-screw'){ $item_quantity = 0; }
            // Generate the markup using all the collected data
            ob_start();
            echo('<div class="item" '.
                'data-key="0" '.
                'data-token="'.$item_token.'" '.
                'data-group="'.$group_token.'" '.
                'data-quantity="'.$item_quantity.'" '.
                'style="z-index: 0;" '.
                '>');
                echo('<div class="icon"><img class="has_pixels" src="'.$icon_url.'" alt="'.$item_name.'"></div>');
                echo('<div class="name '.($item_is_oneline ? 'one-line' : '').'">'.$item_name_br.'</div>');
                echo('<div class="quantity">'.$item_quantity.'</div>');
            echo('</div>');
            $markup = ob_get_clean();
            if (!isset($elements_html_grouped[$group_token])){ $elements_html_grouped[$group_token] = array(); }
            $elements_html_grouped[$group_token][] = $markup;
        }

        // Loop through the different groups and display their float wrappers and then their markup
        $num_items_total = 0;
        foreach ($group_display_order AS $group_align => $group_tokens){
            echo('<div class="wrapper float-'.$group_align.'">'.PHP_EOL);
            foreach ($group_tokens AS $group_key => $group_token){
                if (!isset($elements_html_grouped[$group_token])){ continue; }
                $group_html = $elements_html_grouped[$group_token];
                $num_items = count($group_html);
                foreach ($group_html as $item_index => $item_html){
                    $z_index = $num_items - $item_index;
                    $item_html = str_replace('data-key="0"', 'data-key="'.$z_index.'"', $item_html);
                    $item_html = str_replace('z-index: 0;', 'z-index: '.$z_index.';', $item_html);
                    $group_html[$item_index] = $item_html;
                }
                echo('<div class="group '.$group_token.'" data-group="'.$group_token.'" data-count="'.$num_items.'">'.PHP_EOL);
                    echo(implode(PHP_EOL, $group_html));
                echo('</div>'.PHP_EOL);
                $num_items_total += $num_items;
            }
            echo('</div>'.PHP_EOL);
        }

    // Collect the generated markup for the item palette from the buffer and save it to a variable
    $items_palette_markup = ob_get_clean();
    $items_palette_count = $num_items_total;

    ?>

    <div id="void-recipe">
        <div class="title">
            <strong>The Void Cauldron: Procedural Mission Generator</strong>
        </div>
        <div class="creation">
            <div class="mission-details">
                <span class="loading">&hellip;</span>
            </div>
            <div class="target-list">
                <span class="loading">&hellip;</span>
            </div>
            <div class="battle-field">
                <div class="sprite background"
                    style="background-image: url(/images/fields/prototype-subspace/battle-field_preview.png?20241104-0121);"
                    >&nbsp;</div>
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
        <div class="palette">
            <div class="item-list" data-count="<?= $items_palette_count ?>" data-select="*">
                <?= $items_palette_markup ?>
            </div>
        </div>
    </div>

</div>

<? ob_start(); ?>
    <style type="text/css">

        /* -- PARENT PAGE STYLES -- */

        #window .page .subbody .legend {
            display: block;
            margin: 0 auto;
            font-size: 80%;
        }
        #window .page .body .subbody > .subbody {
            margin-bottom: 20px;
        }
        #window .page .body .subbody #void-recipe {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        /* -- VOID RECIPE CALCULATOR -- */

        #void-recipe {
            display: block;
            box-sizing: border-box;
            width: 700px;
            height: auto;
            background-color: #262626;
            border: 1px solid #1A1A1A;
            border-radius: 3px;
            padding: 8px;
            margin: 0 auto;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.25);
        }

        /* -- BASIC STRUCTURES -- */

        #void-recipe .title,
        #void-recipe .creation,
        #void-recipe .selection,
        #void-recipe .palette {
            display: block;
            width: auto;
            height: auto;
            text-align: center;
            margin: 4px auto;
            position: relative;
        }
        #void-recipe .title {
            margin-top: 0;
        }
        #void-recipe .creation {
            z-index: 1;
        }
        #void-recipe .selection {
            z-index: 2;
        }
        #void-recipe .palette {
            z-index: 3;
        }

        #void-recipe .title:after,
        #void-recipe .palette:after,
        #void-recipe .void:after,
        #void-recipe .mission:after,
        #void-recipe .palette .item-list:after,
        #void-recipe .palette .item-list .wrapper:after,
        #void-recipe .palette .item-list .wrapper .group:after,
        #void-recipe .selection .item-list:after,
        #void-recipe .selection .item-list .wrapper:after,
        #void-recipe .creation .target-list:after,
        #void-recipe .creation .mission-details:after {
            content: "";
            display: block;
            clear: both;
        }

        #void-recipe .title {

        }
        #void-recipe .title strong {
            display: block;
            width: auto;
            margin: 0 auto;
            padding: 6px 12px;
            border: 1px solid #1A1A1A;
            background-color: #1e1e1e;
            border-radius: 3px;
            color: #fefefe;
            font-size: 12px;
            line-height: 16px;
            text-align: center;
        }

        #void-recipe .creation:before,
        #void-recipe .selection:before,
        #void-recipe .palette:before {
            content: "...";
            display: block;
            position: absolute;
            z-index: 10;
            left: 0;
            right: 0;
            top: 0;
            font-size: 9px;
            line-height: 12px;
            color: #bababa;
            border-radius: 3px 3px 0 0;
            border: 0 none transparent;
            background-color: #1a1a1a;
            background-color: rgba(30, 30, 30, 0.8);
            padding: 2px 6px;
        }
        #void-recipe .creation:before {
            content: "Mission Preview";
        }
        #void-recipe .selection:before {
            content: "Selected Items";
        }
        #void-recipe .palette:before {
            content: "Your Inventory";
        }
        #void-recipe .creation .loading,
        #void-recipe .palette .loading,
        #void-recipe .selection .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.6;
        }
        #void-recipe .creation .mission-details,
        #void-recipe .selection .item-list,
        #void-recipe .palette .item-list {
            padding-top: 16px;
        }
        #void-recipe .selection .item-list .wrapper,
        #void-recipe .palette .item-list .wrapper {
            top: 16px;
        }

        /* -- ITEM LISTS -- */

        #void-recipe .item-list {
            display: block;
            margin: 0 auto;
            width: auto;
            height: auto;
            min-width: 180px;
            min-height: 50px;
            position: relative;
            overflow: visible;
            background-color: #2a2a2a;
            border: 1px solid #1a1a1a;
            border-radius: 6px;
        }
        #void-recipe .selection .item-list {
            width: 680px;
            height: 48px;
            box-shadow: inset 2px 14px 10px rgba(0, 0, 0, 0.1);
        }
        #void-recipe .palette .item-list {
            width: 680px;
            height: 282px;
            box-shadow: inset -2px -4px 10px rgba(0, 0, 0, 0.3);
        }
        #void-recipe .item-list .wrapper {
            display: block;
            position: absolute;
            box-sizing: border-box;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 6px;
        }

        /* -- ITEM LIST || ITEMS -- */

        #void-recipe .item-list .item {
            display: block;
            user-select: none;
            float: left;
            width: 54px;
            height: 34px;
            margin: 0 2px 2px 0;
            border: 1px solid #1A1A1A;
            background-color: #262626;
            border-radius: 3px;
            position: relative;
            cursor: pointer;
            filter: opacity(1.0) brightness(1.0);
            box-shadow: 0 0 2px rgba(0, 0, 0, 0);
            transition: filter 0.3s, background-color 0.3s, box-shadow 0.3s, transform 0.3s;
        }
        #void-recipe .selection .item-list .item {
            width: 62px;
            width: calc((100% / 10) - 6px);
            margin: 0 4px 4px 0;
        }
        #void-recipe .item-list .item:hover {
            background-color: #333333;
            box-shadow: 0 0 2px rgba(0, 0, 0, 0.6);
            z-index: 99 !important;
        }
        #void-recipe .item-list .item:before {
            content: "";
            display: block;
            position: absolute;
            z-index: 2;
            top: 4px;
            left: 4px;
            right: 4px;
            bottom: 4px;
            background-color: #363636;
            border-radius: 3px;
            transition: top 0.1s, right 0.1s, bottom 0.1s, left 0.1s, background-color 0.1s;
        }
        #void-recipe .item-list .item:hover:before {
            top: 2px;
            bottom: 6px;
            background-color: #434343;
        }
        #void-recipe .item-list .item.active {
            filter: brightness(1.5);
            outline: 2px solid rgba(255, 255, 255, 0.6);
        }
        #void-recipe .item-list .item[data-quantity="0"] {
            filter: opacity(0.6) brightness(0.9);
            pointer-events: none;
            cursor: not-allowed;
        }
        #void-recipe .item-list .item[data-quantity="0"][data-base-quantity="0"] .icon {
            filter: brightness(0);
            opacity: 0.6;
        }
        #void-recipe .item-list[data-select="active"] .item:not(.active) {
            filter: opacity(0.6) brightness(0.9);
            pointer-events: none;
            cursor: not-allowed;
        }
        #void-recipe .item-list[data-select="active"] .item:not(.active) .icon {
            filter: brightness(0.4);
            opacity: 0.6;
        }

        #void-recipe .item-list .item.placeholder {
            filter: opacity(0.6) brightness(0.9);
            pointer-events: none;
            cursor: not-allowed;
            border-color: transparent;
            background-color: #202020;
            box-shadow: inset 1px 1px 2px rgba(0, 0, 0, 0.6);
        }
        #void-recipe .item-list .item.placeholder:before {
            background-color: #202020;
            display: none;
        }
        #void-recipe .item-list .item.placeholder .icon {
            filter: brightness(0);
            opacity: 0.6;
        }

        #void-recipe .item-list .item .name {
            display: block;
            position: absolute;
            pointer-events: none;
            z-index: 1;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            line-height: 12px;
            height: auto;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            padding: 6px 6px 36px;
            background-color: #262626;
            border: 1px solid #1A1A1A;
            box-shadow: 0 0 6px rgba(0, 0, 0, 0);
            border-radius: 6px;
            pointer-events: none;
            opacity: 0;
            height: 0;
            transition: height 0.4s, bottom 0.2s, left 0.2s, right 0.2s, opacity 0.2s, background-color 0.2s, box-shadow 0.2s;
        }
        #void-recipe .item-list .item .name.one-line {
            line-height: 26px;
        }
        #void-recipe .item-list .item:hover .name {
            bottom: -3px;
            left: -3px;
            right: -3px;
            opacity: 1;
            height: 26px;
            padding-bottom: 36px;
            background-color: #333333;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        #void-recipe .item-list .item .icon {
            display: block;
            position: absolute;
            z-index: 3;
            top: -3px;
            left: -4px;
            pointer-events: none;
            transform: translate(0, 0) scale(1.0);
            transition: transform 0.2s;
        }
        #void-recipe .item-list .item .icon img {
            display: block;
            margin: 0;
        }
        #void-recipe .item-list .item:hover .icon {
            transform: translate(0, -2px);
        }

        #void-recipe .item-list .item .quantity {
            display: block;
            position: absolute;
            z-index: 4;
            top: 10px;
            right: 8px;
            text-align: right;
            padding: 2px;
            font-size: 9px;
            line-height: 11px;
            color: #ffffff;
            transform: translate(0, 0);
            transition: transform 0.2s;
        }
        #void-recipe .item-list .item .quantity:before {
            content: "\0000d7";
        }
        #void-recipe .item-list .item:hover .quantity {
            transform: translate(0, -2px);
        }

        /* -- ITEM LIST || GROUPS -- */

        #void-recipe .item-list .group {
            display: block;
            width: auto;
            padding: 4px 2px 2px 4px;
            margin: 0 auto 6px;
            overflow: visible;
            background-color: rgba(77, 77, 77, 0.2);
            border-radius: 3px;
        }
        #void-recipe .item-list .wrapper.float-left .group {
            float: left;
            margin-left: 0;
            margin-right: 6px;
        }
        #void-recipe .item-list .wrapper.float-right .group {
            float: right;
            margin-right: 0;
            margin-left: 6px;
        }
        #void-recipe .item-list .group .item {
            transition: all 0.3s;
        }
        #void-recipe .item-list .group:hover .item:not(:hover) {
            border-color: transparent;
            background-color: #2e2e2e;
        }
        #void-recipe .item-list .group:hover .item:not(:hover) .quantity {
            color: #cacaca;
        }

        #void-recipe .item-list .group.pellets {
            margin-bottom: 6px;
        }
        #void-recipe .item-list .group.cores {
            margin-bottom: 18px;
        }
        #void-recipe .item-list .group.boosters,
        #void-recipe .item-list .group.diverters {
            margin-bottom: 6px;
        }
        #void-recipe .item-list .group.screws,
        #void-recipe .item-list .group.pellets,
        #void-recipe .item-list .group.capsules,
        #void-recipe .item-list .group.energies {
            clear: left;
        }
        #void-recipe .item-list .group.cores,
        #void-recipe .item-list .group.diverters,
        #void-recipe .item-list .group.modules {
            clear: right;
        }
        #void-recipe .item-list .group.other {
            clear: both;
        }

        #void-recipe .item-list .group.screws {
            width: calc(((54px) + 4px) * 3);
        }
        #void-recipe .item-list .group.special {
            width: calc(((54px) + 4px) * 2);
        }
        #void-recipe .item-list .group.pellets,
        #void-recipe .item-list .group.capsules {
            width: calc(((54px) + 4px) * 6);
        }
        #void-recipe .item-list .group.tanks {
            width: calc(((54px) + 4px) * 2);
        }
        #void-recipe .item-list .group.energies {
            width: calc(((54px) + 4px) * 2);
        }
        #void-recipe .item-list .group.circuits {
            width: calc(((54px) + 4px) * 2);
        }
        #void-recipe .item-list .group.cores {
            width: calc(((54px) + 4px) * 5);
        }
        #void-recipe .item-list .group.fillers {
            width: calc(((54px) + 4px) * 1);
        }
        #void-recipe .item-list .group.boosters {
            width: calc(((54px) + 4px) * 3);
        }
        #void-recipe .item-list .group.diverters {
            width: calc(((54px) + 4px) * 3);
        }
        #void-recipe .item-list .group.modules {
            width: calc(((54px) + 4px) * 5);
        }

        /* -- SELECTION || ITEM LIST & BUTTONS -- */

        #void-recipe .selection {

        }
        #void-recipe .selection .item-list {

        }
        #void-recipe .selection .item-list .item {
            transform: translate(0, 0);
        }
        #void-recipe .selection .item-list .item .icon {
            transform: translate(0, 0) scale(2.0);
        }

        #void-recipe .selection .item-list .item.recent {
            transform: scale(1.0);
            animation: void-recipe-item-recent 0.5s;
        }
        @keyframes void-recipe-item-recent {
            0% { transform: scale(1.0); }
            50% { transform: scale(1.4); }
            100% { transform: scale(1.0); }
        }

        #void-recipe .selection .reset {
            display: block;
            position: absolute;
            z-index: 30;
            top: 0;
            right: 0;
            width: 24px;
            height: 24px;
            font-size: 18px;
            line-height: 24px;
            text-align: center;
            vertical-align: middle;
            color: #a1a1a1;
            cursor: pointer;
            transform: scale(1.0);
            transition: transform 0.2s, color 0.2s;
        }
        #void-recipe .selection .reset:hover {
            transform: scale(1.2);
            color: #efefef;
        }
        #void-recipe .selection .reset > i {
            display: block;
            margin: 0;
        }
        #void-recipe .selection .reset:not(.visible) {
            pointer-events: none;
            display: none;
        }

        /* -- CREATION || TARGET LIST & MISSION DETAILS -- */

        #void-recipe .creation {
            border-radius: 6px;
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.2);
        }
        #void-recipe .creation .mission-details,
        #void-recipe .creation .target-list,
        #void-recipe .creation .battle-field {
            display: block;
            margin: 0 auto;
            width: auto;
            height: auto;
            min-width: 180px;
            min-height: 50px;
            overflow: visible;
            border: 1px solid #1A1A1A;
            border-radius: 6px;
            position: relative;
            z-index: 3;
        }

        #void-recipe .creation .battle-field {
            overflow: hidden;
            border: 0 none transparent;
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 1;
            pointer-events: none;
        }
        #void-recipe .creation .battle-field .sprite.background,
        #void-recipe .creation .battle-field .sprite.foreground {
            position: absolute;
            margin: 0 auto;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
        }
        #void-recipe .creation .battle-field .sprite.background {
            background-repeat: repeat;
            background-position: center center;
        }
        #void-recipe .creation .battle-field .sprite.foreground {
            top: auto;
            height: 20%;
        }

        #void-recipe .creation .mission-details {
            height: 60px;
            border-radius: 6px 6px 0 0;
            border-bottom: 0;
        }
        #void-recipe .creation .mission-details .powers-list {
            display: block;
            width: auto;
        }
        #void-recipe .creation .mission-details .powers-list ul {
            display: block;
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            margin: 0 auto;
            padding: 3px;
            font-size: 11px;
            line-height: 13px;
            color: #efefef;
        }
        #void-recipe .creation .mission-details .powers-list ul li {
            display: block;
            box-sizing: border-box;
            float: left;
            width: calc(100% / 5);
            text-align: center;
            padding: 2px 3px 3px;
        }
        #void-recipe .creation .mission-details .powers-list ul:after,
        #void-recipe .creation .mission-details .powers-list ul li:after {
            content: "";
            display: block;
            clear: both;
        }
        #void-recipe .creation .mission-details .powers-list ul li:nth-child(odd) {
            background-color: rgba(255, 255, 255, 0.02);
        }
        #void-recipe .creation .mission-details .powers-list ul li .token,
        #void-recipe .creation .mission-details .powers-list ul li .value {
            display: inline-block;
            margin: 0 4px;
        }
        #void-recipe .creation .mission-details .powers-list ul li .token {
            text-decoration: underline;
        }
        #void-recipe .creation .mission-details .powers-list ul li .value {
            font-weight: bold;
        }
        #void-recipe .creation .mission-details .powers-list ul li .value .overflow {
            display: inline-block;
            margin-left: 4px;
            opacity: 0.4;
            font-weight: normal;
        }

        #void-recipe .creation .target-list {
            height: 80px;
            border-radius: 0 0 6px 6px;
            border-top: 0;
            text-align: center;
            vertical-align: middle;
        }
        #void-recipe .creation .target-list .target {
            display: inline-block;
            position: relative;
            box-sizing: border-box;
            text-align: center;
            vertical-align: middle;
            margin: 6px;
            padding: 0;
            width: 60px;
            height: 60px;
            width: calc((100% / 9) - 12px);
            height: calc(100% - 12px);
            border-radius: 3px;
            text-align: center;
            background-color: transparent;
            /* background-color: #2d2c39;  */
            /* background-color: rgba(255, 0, 100, 0.1);  */
        }
        #void-recipe .creation .target-list .target > .type {
            display: block;
            position: absolute;
            z-index: 1;
            width: auto;
            height: auto;
            bottom: 9px;
            left: 50%;
            transform: translate(-50%, 0);
            width: 50px;
            height: 50px;
            border: 1px solid transparent;
            border-radius: 50%;
            filter: opacity(1.0) brightness(0.7) saturate(0.8);
            pointer-events: none;
        }
        #void-recipe .creation .target-list .target > .type.empty {
            filter: none;
            border-color: #2e2e2e !important;
        }
        #void-recipe .creation .target-list .target .image {
            display: block;
            position: absolute;
            z-index: 2;
            width: 40px;
            height: 40px;
            bottom: 14px;
            left: 50%;
            transform: translate(-50%, -50%) scale(2.0);
            cursor: pointer
            /* background-color: rgba(100, 0, 255, 0.1);  */
        }
        #void-recipe .creation .target-list .target .label {
            display: block;
            position: absolute;
            z-index: 3;
            width: 100%;
            height: auto;
            bottom: 0;
            left: 50%;
            transform: translate(-50%, 0);
            font-size: 9px;
            line-height: 13px;
            background-color: #22222b;
            border: 1px solid #1d1d26;
            border-radius: 2px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            /* background-color: rgba(100, 100, 0, 0.1); */
        }
        #void-recipe .creation .target-list .target .label .name {
            display: block;
            margin: 0 auto;
            width: 90%;
            width: calc(100% - 8px);
            font-size: inherit;
            line-height: inherit;
            font-weight: normal;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }
        #void-recipe .creation .target-list .target .image .sprite {
            display: block;
            pointer-events: none;
            position: relative;
            margin: 0;
            top: 0;
            left: 0;
            transform: translateY(0px);
            animation: void-target-sprite-bounce 0.6s steps(1) infinite;
            /* background-color: rgba(0, 200, 100, 0.1);  */
        }
        @keyframes void-target-sprite-bounce {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-1px); }
            100% { transform: translateY(0px); }
        }
        #void-recipe .creation .target-list .target .image .sprite_40x40 {
            top: 0;
            left: 0;
        }
        #void-recipe .creation .target-list .target .image .sprite_80x80 {
            top: -40px;
            left: -20px;
        }
        #void-recipe .creation .target-list .target .image .sprite_160x160 {
            top: -80px;
            left: -40px;
        }
        #void-recipe .creation .target-list .target .image:hover .sprite_40x40 {
            background-position: -40px 0;
        }
        #void-recipe .creation .target-list .target .image:hover .sprite_80x80 {
            background-position: -80px 0;
        }
        #void-recipe .creation .target-list .target .image:hover .sprite_160x160 {
            background-position: -160px 0;
        }

        /* -- PANEL & GROUP COLORS -- */

        #void-recipe .creation .mission-details {
            background-color: #2d2c3a;
            background-color: rgb(45, 44, 58, 0.9);
        }
        #void-recipe .creation .target-list {
            background-color: #292834;
            background-color: rgb(41, 40, 52, 0.8);
        }
        #void-recipe .selection .item-list {
            background-color: #25232e;
        }
        #void-recipe .palette .item-list {
            background-color: #353144;
        }

        #void-recipe .item-list .group.screws {
            /* speed purple  */
            /* border-color: rgb(105, 87, 117);  */
            /* background-color: rgb(139, 115, 155);  */
            /* modded speed purple  */
            border-color: rgb(90, 49, 114);
            background-color: rgb(116, 75, 139);
        }
        #void-recipe .item-list .group.pellets,
        #void-recipe .item-list .group.capsules {
            /* defense blue  */
            border-color: rgb(62, 76, 105);
            background-color: rgb(80, 99, 138);
        }
        #void-recipe .item-list .group.cores {
            /* attack red  */
            border-color: rgb(111, 54, 54);
            background-color: rgb(146, 73, 73);
        }
        #void-recipe .item-list .group.special {
            /* defense blue + attack red  */
            border-color: rgb(62, 76, 105) !important;
            background-color: rgb(80, 99, 138) !important;
            background-image: -webkit-gradient(linear, left top, right top, color-stop(0, rgb(80, 99, 138)), color-stop(1, rgb(146, 73, 73))) !important;
            background-image: -o-linear-gradient(right, rgb(80, 99, 138) 0%, rgb(146, 73, 73) 100%) !important;
            background-image: -moz-linear-gradient(right, rgb(80, 99, 138) 0%, rgb(146, 73, 73) 100%) !important;
            background-image: -webkit-linear-gradient(right, rgb(80, 99, 138) 0%, rgb(146, 73, 73) 100%) !important;
            background-image: -ms-linear-gradient(right, rgb(80, 99, 138) 0%, rgb(146, 73, 73) 100%) !important;
            background-image: linear-gradient(to right, rgb(80, 99, 138) 0%, rgb(146, 73, 73) 100%) !important;
        }
        #void-recipe .item-list .group.energies {
            /* energy green */
            border-color: rgb(68, 105, 59);
            background-color: rgb(89, 138, 78);
        }
        #void-recipe .item-list .group.circuits {
            /* nature green */
            border-color: rgb(37, 155, 51);
            background-color: rgb(28, 122, 39);
        }
        #void-recipe .item-list .group.boosters,
        #void-recipe .item-list .group.diverters {
            /* shield green  */
            border-color: rgb(102, 146, 120);
            background-color: rgb(95, 136, 112);
        }
        #void-recipe .item-list .group.modules {
            /* electric yellow */
            background-color: #807c18;
        }
        #void-recipe .item-list .group.fillers {
            /* cutter grey  */
            border-color: rgb(109, 109, 109);
            background-color: rgb(118, 120, 126);
        }
        .foo {
            background-color: #807c18; /* electric yellow */
            background-color: #6b854b; /* energy green */
            background-color: #44795a; /* shield green */
            background-color: #783078; /* time purple  */
            background-color: #107981; /* freeze blue  */
            background-color: #58589b; /* space purple  */
            background-color: #8d386e; /* crystal pink  */
            background-color: #93521d; /* explode orange  */
            background-color: #883030; /* flame red  */
        }



    </style>
<? $website_include_stylesheets .= ob_get_clean(); ?>

<? ob_start(); ?>
    <script type="text/javascript">
        (function(){

            // Predefine some object arrays to hold our information
            gameSettings.customIndex.contentIndex = {};
            var mmrpgIndex = gameSettings.customIndex.contentIndex;
            var mmrpgQueue = {};
            var mmrpgMission = {};

            // Add the void mission data to the global object
            mmrpgIndex.types = <?= json_encode($mmrpg_index_types) ?>;
            mmrpgIndex.players = <?= json_encode($mmrpg_index_players) ?>;
            mmrpgIndex.robots = <?= json_encode($mmrpg_index_robots) ?>;
            mmrpgIndex.abilities = <?= json_encode($mmrpg_index_abilities) ?>;
            mmrpgIndex.items = <?= json_encode($mmrpg_index_items) ?>;
            mmrpgIndex.fields = <?= json_encode($mmrpg_index_fields) ?>;
            console.log('mmrpgIndex:', typeof mmrpgIndex, mmrpgIndex);

            // Check to see if the Void Recipe calculator is available
            var $voidRecipeWizard = $('#void-recipe');
            if ($voidRecipeWizard.length > 0){
                (function(){

                    //console.log('voidRecipeWizard:', $voidRecipeWizard);

                    // Create a VOID RECIPE WIZARD so we can easily add/remove and recalculate on-the-stop
                    var voidRecipeWizard = {
                        init: function($container){
                            console.log('%c' + 'voidRecipeWizard.init()', 'color: magenta;');
                            //console.log('-> w/ $container:', typeof $container, $container.length, $container);
                            const _self = this;
                            _self.name = 'voidRecipeWizard';
                            _self.version = '1.0.0';
                            _self.maxItems = 10;
                            _self.maxTargets = 8;
                            _self.minQuantaPerClass = {'mecha': 25, 'master': 50, 'boss': 500};
                            _self.reset(false);
                            _self.setup($container);
                            _self.recalculate();
                            _self.regenerate();
                            _self.refresh();
                            console.log('voidRecipeWizard is ' + ('%c' + 'ready'), 'color: lime;');
                            console.log('=> voidRecipeWizard:', _self);
                            // end of voidRecipeWizard.init()
                            },
                        reset: function(refresh){
                            console.log('%c' + 'voidRecipeWizard.reset()', 'color: magenta;');
                            if (typeof refresh === 'undefined'){ refresh = true; }
                            const _self = this;
                            _self.items = {};
                            _self.powers = {};
                            _self.mission = {};
                            _self.history = [];
                            if (!refresh){ return; }
                            _self.recalculate();
                            _self.regenerate();
                            _self.refresh();
                            // end of voidRecipeWizard.reset()
                            },
                        setup: function($container){
                            console.log('%c' + 'voidRecipeWizard.setup()', 'color: magenta;');
                            //console.log('-> w/ $container:', typeof $container, $container.length, $container);

                            // Backup a reference to the parent object
                            const _self = this;

                            // Predefine some parent variables for the class
                            _self.xrefs = {};
                            _self.items = {};
                            _self.powers = {};
                            _self.mission = {};
                            _self.history = [];
                            _self.indexes = {};

                            // Pre-define a list of item tokens we can use later
                            const mmrpgIndexItems = mmrpgIndex.items;
                            var mmrpgTypeTokens = Object.keys(mmrpgIndexItems);
                            _self.indexes.itemTokens = mmrpgTypeTokens;
                            //console.log('mmrpgTypeTokens:', mmrpgTypeTokens);

                            // Pre-define a list of stat tokens we can use later
                            var mmrpgStatTokens = ['energy', 'weapons', 'attack', 'defense', 'speed'];
                            _self.indexes.statTokens = mmrpgStatTokens;
                            //console.log('mmrpgStatTokens:', mmrpgStatTokens);

                            // Pre-collect a list of type tokens we can use later
                            const mmrpgIndexTypes = mmrpgIndex.types;
                            var mmrpgTypeTokens = Object.keys(mmrpgIndexTypes);
                            mmrpgTypeTokens = mmrpgTypeTokens.filter(function(token){
                                var info = mmrpgIndexTypes[token];
                                if (token === 'none'){ return true; }
                                else if (info.type_class === 'normal'){ return true; }
                                return false;
                                });
                            _self.indexes.typeTokens = mmrpgTypeTokens;
                            //console.log('mmrpgTypeTokens:', mmrpgTypeTokens);

                            // Pre-collect a list of robot tokens that we can use later
                            const mmrpgIndexRobots = mmrpgIndex.robots;
                            var mmrpgRobotTokens = Object.keys(mmrpgIndexRobots);
                            mmrpgRobotTokens = mmrpgRobotTokens.filter(function(token){
                                var info = mmrpgIndexRobots[token];
                                //console.log('checking info for ', token, ' | info:', info);
                                if (!info.robot_flag_published){ return false; }
                                else if (!info.robot_flag_complete){ return false; }
                                else if (info.robot_flag_hidden){ return false; }
                                else if (info.robot_class === 'system'){ return false; }
                                return true;
                                });
                            _self.indexes.robotTokens = mmrpgRobotTokens;
                            //console.log('mmrpgRobotTokens:', mmrpgRobotTokens);

                            // Create sub-lists of robot tokens for each class for later
                            var filterToClass = function(tokens, className){
                                return tokens.filter(function(token){
                                    var info = mmrpgIndexRobots[token];
                                    if (info.robot_class === className){ return true; }
                                    return false;
                                    });
                                };
                            var mmrpgRobotMechaTokens = filterToClass(mmrpgRobotTokens, 'mecha');
                            var mmrpgRobotMasterTokens = filterToClass(mmrpgRobotTokens, 'master');
                            var mmrpgRobotBossTokens = filterToClass(mmrpgRobotTokens, 'boss');
                            _self.indexes.robotMechaTokens = mmrpgRobotMechaTokens;
                            _self.indexes.robotMasterTokens = mmrpgRobotMasterTokens;
                            _self.indexes.robotBossTokens = mmrpgRobotBossTokens;
                            //console.log('mmrpgRobotMechaTokens:', mmrpgRobotMechaTokens);
                            //console.log('mmrpgRobotMasterTokens:', mmrpgRobotMasterTokens);
                            //console.log('mmrpgRobotBossTokens:', mmrpgRobotBossTokens);

                            // Collect references to key and parent elements on the page
                            var $parentDiv = $container;
                            var $missionTargets = $('.creation .target-list', $parentDiv);
                            var $missionDetails = $('.creation .mission-details', $parentDiv);
                            var $itemsPalette = $('.palette .item-list', $parentDiv);
                            var $itemsSelected = $('.selection .item-list', $parentDiv);
                            var $resetButton = $('.selection .reset', $parentDiv);

                            // Save the references to the object for later use
                            var xrefs = _self.xrefs;
                            xrefs.parentDiv = $parentDiv;
                            xrefs.missionTargets = $missionTargets;
                            xrefs.missionDetails = $missionDetails;
                            xrefs.itemsPalette = $itemsPalette;
                            xrefs.itemsSelected = $itemsSelected;
                            xrefs.resetButton = $resetButton;
                            //console.log('xrefs:', xrefs);

                            // Backup every item's base quantity so we can do dynamic calulations in realt-time
                            $('.item[data-quantity]:not([data-base-quantity])', $parentDiv).each(function(){
                                var $item = $(this);
                                var quantity = parseInt($item.attr('data-quantity'));
                                $item.attr('data-base-quantity', quantity);
                                });

                            // Bind ADD ITEM click events to the palette area's item list buttons
                            $('.item[data-token]', $itemsPalette).live('click', function(e){
                                console.log('palette button clicked! \n-> add-item:', $(this).attr('data-token'));
                                e.preventDefault();
                                var $item = $(this);
                                var itemToken = $item.attr('data-token');
                                var itemGroup = $item.attr('data-group');
                                var itemQuantity = parseInt($item.attr('data-quantity'));
                                var itemIndex = parseInt($item.attr('data-key'));
                                var itemInfo = {token: itemToken, group: itemGroup, quantity: itemQuantity, index: itemIndex};
                                //console.log('item clicked:', $item);
                                //console.log('item details:', itemInfo);
                                if (itemQuantity <= 0){ return; }
                                _self.addItem(itemInfo);
                                });

                            // Bind REMOVE ITEM click events to the selection area's item list buttons
                            $('.item[data-token]', $itemsSelected).live('click', function(e){
                                console.log('section button clicked! \n-> remove-item:', $(this).attr('data-token'));
                                e.preventDefault();
                                var $item = $(this);
                                var itemToken = $item.attr('data-token');
                                var itemGroup = $item.attr('data-group');
                                var itemQuantity = parseInt($item.attr('data-quantity'));
                                var itemIndex = parseInt($item.attr('data-key'));
                                var itemInfo = {token: itemToken, group: itemGroup, quantity: itemQuantity, index: itemIndex};
                                //console.log('item clicked:', $item);
                                //console.log('item details:', itemInfo);
                                _self.removeItem(itemInfo);
                                });

                            // Bind RESET ITEMS click events to the selection area's reset button
                            $resetButton.live('click', function(e){
                                console.log('reset button clicked! \n-> reset-items');
                                e.preventDefault();
                                _self.reset();
                                });

                            // end of voidRecipeWizard.setup()
                            },
                        recalculate: function(){
                            console.log('%c' + 'voidRecipeWizard.recalculate()', 'color: magenta;');

                            // Backup a reference to the parent object
                            const _self = this;

                            // Collect a reference to the void values object and reset
                            var voidItems = _self.items;
                            var voidItemsTokens = Object.keys(voidItems);

                            // Define a variable to hold the calculated powers of all the items
                            var voidPowers = {};
                            voidPowers.powers = {};
                            voidPowers.flags = {};
                            voidPowers.getPowers = function(){ return voidPowers.powers; };
                            voidPowers.getPower = function(token, fallback){ return voidPowers.powers[token] || fallback || 0; };
                            voidPowers.setPower = function(token, value){ voidPowers.powers[token] = Math.round(value * 100) / 100; };
                            voidPowers.incPower = function(token, value){ voidPowers.setPower(token, voidPowers.getPower(token) + value); };
                            voidPowers.decPower = function(token, value){ voidPowers.setPower(token, voidPowers.getPower(token) - value); };
                            voidPowers.modPower = function(token, value, fallback){ voidPowers.setPower(token, voidPowers.getPower(token, fallback) * value); };
                            voidPowers.powers.delta = 0;
                            voidPowers.powers.quanta = 0;
                            voidPowers.powers.spread = 0;
                            voidPowers.powers.level = 0;
                            voidPowers.powers.forte = 0;
                            voidPowers.powers.effort = 0;
                            voidPowers.powers.reward = 0;
                            voidPowers.flags.guard = false;
                            voidPowers.flags.reverse = false;
                            voidPowers.flags.extreme = false;

                            // Loop through all the items, one-by-one, and parse their intrinsic values
                            for (var i = 0; i < voidItemsTokens.length; i++){
                                var itemToken = voidItemsTokens[i];
                                var itemQuantity = voidItems[itemToken];
                                _self.parseItem({token: itemToken}, itemQuantity, voidPowers);
                                //for (var j = 0; j < itemQuantity; j++){ }
                                }

                            // As long as items are present, we should make keep certain values in scope
                            if (voidItemsTokens.length){
                                // Ensure the quanta is always at least zero if there are items present
                                if (voidPowers.powers.quanta < 0){ voidPowers.powers.quanta = 0; }
                                // Ensure the spread always within range when there are items present
                                if (voidPowers.powers.spread < 1){ voidPowers.powers.spread = 1; }
                                // Ensure the level is always at least one if there are items present
                                if (voidPowers.powers.level < 1){ voidPowers.powers.level = 1; }
                                }

                            //console.log('voidPowers have been updated!');
                            _self.powers = {};
                            var voidPowersList = voidPowers.getPowers();
                            var voidPowerKeys = Object.keys(voidPowersList);
                            var voidPowersRequired = ['delta', 'quanta', 'spread', 'level', 'forte'];
                            for (var i = 0; i < voidPowerKeys.length; i++){
                                var powerToken = voidPowerKeys[i];
                                var powerValue = voidPowersList[powerToken];
                                if (powerValue === 0 && voidPowersRequired.indexOf(powerToken) === -1){ continue; }
                                _self.powers[powerToken] = powerValue;
                                //console.log('-> voidPowers.' + powerToken + ' =', powerValue);
                                }

                            // end of voidRecipeWizard.recalculate()
                            },
                        regenerate: function(){
                            console.log('%c' + 'voidRecipeWizard.regenerate()', 'color: magenta;');

                            // Backup a reference to the parent object
                            const _self = this;

                            // Collect reference to the void powers so we can reference them
                            var voidPowersList = _self.powers;
                            var voidPowersKeys = Object.keys(voidPowersList);

                            // If we don't have any powers, we can't generate anything
                            if (!voidPowersKeys.length){
                                //console.log('%c' + '-> no powers to generate from!', 'color: orange;');
                                return;
                                }

                            // Collect the base amounts of quanta and spread for later reference
                            var baseQuanta = voidPowersList['quanta'] || 0;
                            var baseSpread = voidPowersList['spread'] || 0;
                            //console.log('-> baseQuanta:', baseQuanta, 'baseSpread:', baseSpread);

                            // Likewise if we don't have any quanta material or a defined spread limit, we can't generate either
                            if (baseQuanta < 1 || baseSpread < 1){
                                if (baseQuanta < 1 && baseSpread < 1){
                                    //console.log('%c' + '-> no quanta materia nor spread limit to generate from!', 'color: red;');
                                    } else if (baseQuanta < 1){
                                    //console.log('%c' + '-> no quanta materia to generate with!', 'color: red;');
                                    } else if (baseSpread < 1){
                                    //console.log('%c' + '-> no spread limit to generate within!', 'color: red;');
                                    }
                                return;
                                }

                            // First we set-up the different target slots given quanta vs spread
                            // using predefined thresholds to determine each target's class
                            var effectiveSpread = baseSpread >= _self.maxTargets ? _self.maxTargets : (baseSpread < 1 ? 1 : Math.trunc(baseSpread));
                            var distributedQuanta = _self.distributeQuanta(baseQuanta, effectiveSpread, true);
                            //console.log('-> effectiveSpread:', effectiveSpread, 'distributedQuanta:', distributedQuanta);

                            // Pull a filtered list of stat powers and type powers for easier looping
                            var statPowersList = _self.filterStatPowers(voidPowersList);
                            var typePowersList = _self.filterTypePowers(voidPowersList);
                            //console.log('-> statPowersList:', statPowersList);
                            //console.log('-> typePowersList:', typePowersList);

                            // Loop through and check to see which classes are represented
                            var maxTierLevel = 0;
                            for (var i = 0; i < distributedQuanta.length; i++){
                                if (!distributedQuanta[i].tier){ continue; }
                                var tier = distributedQuanta[i].tier;
                                if (tier === 'boss'){ maxTierLevel = Math.max(maxTierLevel, 3); }
                                if (tier === 'master'){ maxTierLevel = Math.max(maxTierLevel, 2); }
                                if (tier === 'mecha'){ maxTierLevel = Math.max(maxTierLevel, 1); }
                                }
                            //console.log('-> maxTierLevel:', maxTierLevel);

                            // Generate a queue of mechas, masters, and bosses given the powers available
                            var targetRobotQueue = {};
                            targetRobotQueue['mecha'] = maxTierLevel >= 1 ? _self.generateTargetQueue((_self.indexes.robotMechaTokens || []), typePowersList, statPowersList) : [];
                            targetRobotQueue['master'] = maxTierLevel >= 2 ? _self.generateTargetQueue((_self.indexes.robotMasterTokens || []), typePowersList, statPowersList) : [];
                            targetRobotQueue['boss'] = maxTierLevel >= 3 ? _self.generateTargetQueue((_self.indexes.robotBossTokens || []), typePowersList, statPowersList) : [];
                            //console.log('-> targetRobotQueue[mecha]:', targetRobotQueue['mecha']);
                            //console.log('-> targetRobotQueue[master]:', targetRobotQueue['master']);
                            //console.log('-> targetRobotQueue[boss]:', targetRobotQueue['boss']);

                            // Use calculated quanta-per-target to set-up the different target slots
                            var missionTargets = [];
                            var numTargetSlots = effectiveSpread; //distributedQuanta.length;
                            for (var slotKey = 0; slotKey < numTargetSlots; slotKey++){
                                var slotTemplate = distributedQuanta[slotKey];
                                //console.log('--> calculating slotKey:', slotKey, 'w/ slotTemplate:', slotTemplate);
                                var targetRobot = {};
                                var targetTier = slotTemplate.tier;
                                var targetClass = slotTemplate.class;
                                var targetQuanta = slotTemplate.amount;
                                targetRobot.token = '';
                                targetRobot.class = targetClass;
                                targetRobot.quanta = targetQuanta;
                                targetRobot.level = 1;
                                if (targetTier.length){
                                    var queueOrder = [];
                                    if (targetTier === 'boss'){ queueOrder.push('boss', 'master', 'mecha'); }
                                    if (targetTier === 'master'){ queueOrder.push('master', 'mecha'); }
                                    if (targetTier === 'mecha'){ queueOrder.push('mecha'); }
                                    for (var i = 0; i < queueOrder.length; i++){
                                        var queueToken = queueOrder[i];
                                        if (targetRobotQueue[queueToken].length){
                                            targetRobot.token = targetRobotQueue[queueToken].shift();
                                            targetRobotQueue[queueToken].push(targetRobot.token);
                                            break;
                                            }
                                        }
                                    }

                                // If a token for this slot count not be found, default to a dark frag
                                if (!targetRobot.token.length){
                                    targetRobot.token = 'dark-frag';
                                    }

                                // Add the target robot to the mission targets list
                                missionTargets.push(targetRobot);
                                //console.log('--> pushed new target!', '\n-> targetRobot:', targetRobot);

                                }

                            // Update the mission details with the new targets
                            _self.mission = {};
                            _self.mission.targets = missionTargets;

                            // end of voidRecipeWizard.regenerate()
                            },
                        refresh: function(){
                            console.log('%c' + 'voidRecipeWizard.refresh()', 'color: magenta;');

                            // Backup a reference to the parent object
                            const _self = this;

                            // Collect a reference to the void values
                            var voidItems = _self.items;
                            var voidHistory = _self.history;
                            var $itemsSelected = _self.xrefs.itemsSelected;
                            var $itemsPalette = _self.xrefs.itemsPalette;
                            var $resetButton = _self.xrefs.resetButton;
                            var $missionDetails = _self.xrefs.missionDetails;
                            var $targetList = _self.xrefs.missionTargets;

                            // Check to see which was the last item token added
                            var lastItemToken = '';
                            if (voidHistory.length){
                                lastItemToken = voidHistory[voidHistory.length - 1].token;
                                }

                            // Clear the item selection area and then rebuild it with the new items
                            var $selectedWrapper = $('.wrapper', $itemsSelected);
                            var $paletteWrappers = $('.wrapper', $itemsPalette);
                            var $paletteItems = $('.item[data-token]', $itemsPalette);
                            var usedItemTokens = Object.keys(voidItems);
                            var numSlotsAvailable = _self.maxItems;
                            var numSlotsUsed = usedItemTokens.length;
                            $selectedWrapper.html('');
                            $paletteItems.removeClass('active');
                            if (usedItemTokens.length > 0){
                                const mmrpgIndexItems = mmrpgIndex.items;
                                for (var i = 0; i < usedItemTokens.length; i++){
                                    // Generate the markup for the item then add to the selection area
                                    var itemToken = usedItemTokens[i];
                                    var itemInfo = mmrpgIndexItems[itemToken];
                                    var itemName = itemInfo.item_name;
                                    var itemNameBr = itemName.replace(' ', '<br />');
                                    var itemQuantity = voidItems[itemToken] || 0;
                                    var itemImage = itemInfo.item_image || itemToken;
                                    var itemClass = 'item' + (itemToken === lastItemToken ? ' recent' : '');
                                    var itemIcon = '/images/items/'+itemImage+'/icon_right_40x40.png?'+gameSettings.cacheDate;
                                    var itemMarkup = '<div class="'+itemClass+'" data-token="'+itemToken+'" data-quantity="'+itemQuantity+'">';
                                        itemMarkup += '<div class="icon"><img class="has_pixels" src="'+itemIcon+'" alt="'+itemName+'"></div>';
                                        itemMarkup += '<div class="name">'+itemNameBr+'</div>';
                                        itemMarkup += '<div class="quantity">'+itemQuantity+'</div>';
                                    itemMarkup += '</div>';
                                    $selectedWrapper.append(itemMarkup);
                                    // Update the parent button in the palette area to show that its active
                                    $paletteItems.filter('.item[data-token="'+itemToken+'"]').addClass('active');
                                    }
                                }

                            // Fill empty slots with item-placeholder elements for visual clarity,
                            // otherwise if all slots are full we should disable further selections
                            if (numSlotsUsed < numSlotsAvailable){
                                //console.log('there are empty slots!', (numSlotsAvailable - numSlotsUsed));
                                $itemsPalette.attr('data-select', '*');
                                var emptySlots = numSlotsAvailable - numSlotsUsed;
                                for (var i = 0; i < emptySlots; i++){
                                    var placeholderMarkup = '<div class="item placeholder"></div>';
                                    $selectedWrapper.append(placeholderMarkup);
                                    }
                                } else {
                                //console.log('all slots are full!');
                                $itemsPalette.attr('data-select', 'active');
                                }

                            // Check and update the displayed quantities of any items visible in the palette
                            var itemsToUpdate = _self.indexes.itemTokens;
                            if (itemsToUpdate.length > 0){
                                const mmrpgIndexItems = mmrpgIndex.items;
                                for (var i = 0; i < itemsToUpdate.length; i++){
                                    var itemToken = itemsToUpdate[i];
                                    var itemInfo = mmrpgIndexItems[itemToken];
                                    var $paletteButton = $('.item[data-token="'+itemToken+'"]', $itemsPalette);
                                    var baseQuantity = parseInt($paletteButton.attr('data-base-quantity'));
                                    var addedQuantity = voidItems[itemToken] || 0;
                                    var newQuantity = baseQuantity - addedQuantity;
                                    $paletteButton.attr('data-quantity', newQuantity);
                                    $paletteButton.find('.quantity').text(newQuantity);
                                    //console.log('updating', itemToken, 'button in palette w/', {baseQuantity: baseQuantity, addedQuantity: addedQuantity, newQuantity: newQuantity});
                                    }
                                }

                            // Show or hide the reset button depending on whether or not there's a selection to reset
                            if (numSlotsUsed > 0){ $resetButton.addClass('visible'); }
                            else { $resetButton.removeClass('visible'); }

                            // Let us also update the list of void powers to show any changes
                            var voidPowers = _self.powers;
                            var voidPowersKeys = Object.keys(voidPowers);
                            //console.log('voidPowers:', voidPowers);
                            //console.log('voidPowersKeys:', voidPowersKeys);
                            $missionDetails.html('');
                            if (voidPowersKeys.length){
                                var powersListMarkup = '';
                                powersListMarkup += '<div class="powers-list">';
                                    powersListMarkup += '<ul class="wrapper">';
                                    for (var i = 0; i < voidPowersKeys.length; i++){
                                        var powerToken = voidPowersKeys[i];
                                        var powerValue = voidPowers[powerToken];
                                        if (powerToken === ''){ continue; }
                                        // format the power value differently per kind
                                        var powerValueText = '';
                                        if (powerToken === 'delta'){
                                            // unsigned, fine as-is
                                            powerValueText = '' + powerValue;
                                            }
                                        else if (powerToken === 'quanta'){
                                            // quantity so use the times symbol
                                            powerValueText = '&times;' + powerValue;
                                            }
                                        else if (powerToken === 'spread'){
                                            // unsigned, but must stay within limit, so truncate
                                            // also make sure overflow is visible for the tooltip
                                            var scopedValue = roundedValue = Math.trunc(powerValue), overflow = 0;
                                            if (scopedValue > _self.maxTargets){ scopedValue = _self.maxTargets; }
                                            if (roundedValue > scopedValue){ overflow = roundedValue - scopedValue; }
                                            powerValueText = '&times;' + scopedValue + (overflow ? ' <span class="overflow">(&plus;' + overflow + ')</span>' : '');
                                            }
                                        else if (powerToken === 'level'){
                                            // unsigned, fine as-is
                                            powerValueText = '' + powerValue;
                                            }
                                        else {
                                            // signed, so display the correct one
                                            var roundedValue = Math.round(powerValue);
                                            powerValueText = (roundedValue > 0 ? '&plus;' : (roundedValue < 0 ? '&minus;' : '')) + Math.abs(roundedValue);
                                            }
                                        // add the power to the list
                                        powersListMarkup += '<li class="power">';
                                            powersListMarkup += '<span class="token">'+powerToken+'</span> ';
                                            powersListMarkup += '<span class="value">'+powerValueText+'</span>';
                                        powersListMarkup += '</li>';
                                        }
                                    powersListMarkup += '</ul>';
                                powersListMarkup += '</div>';
                                $missionDetails.append(powersListMarkup);
                                } else {
                                $missionDetails.append('<span class="loading">&hellip;</span>');
                                }

                            // Update the list of target robots in the panel if any have been generated
                            var missionInfo = _self.mission;
                            var missionTargets = missionInfo.targets || [];
                            $targetList.html('');
                            if (missionTargets.length){
                                //console.log('updating mission target list!', '\n-> missionInfo:', missionInfo, '\n-> missionTargets:', missionTargets);
                                const mmrpgIndexRobots = mmrpgIndex.robots;
                                const frameTokenByKey = {0: 'base', 1: 'defense', 2: 'base2', 3: 'defend', 4: 'base', 5: 'defend', 6: 'base2', 7: 'defend'};
                                for (var i = 0; i < missionTargets.length; i++){
                                    var targetKey = i;
                                    var targetRobot = missionTargets[i];
                                    //console.log('-> targetRobot:', targetRobot);
                                    var targetRobotToken = targetRobot.token;
                                    var targetRobotInfo = mmrpgIndexRobots[targetRobotToken] || false;
                                    if (!targetRobotInfo){ continue; }
                                    var targetRobotClass = targetRobot.class;
                                    var targetRobotQuanta = targetRobot.quanta;
                                    var targetRobotLevel = targetRobot.level;
                                    var targetRobotName = targetRobotInfo['robot_name'] || targetRobotToken;
                                    var targetRobotImage = targetRobotInfo['robot_image'] || targetRobotToken;
                                    var targetRobotTypes = targetRobotInfo['robot_core'] || 'none';
                                    if (targetRobotInfo['robot_core'] && targetRobotInfo['robot_core2']){ targetRobotTypes += '_'+targetRobotInfo['robot_core2']; }
                                    var targetRobotImageSize = targetRobotInfo['robot_image_size'] || 40;
                                    var targetRobotImageSizeX = targetRobotImageSize + 'x' + targetRobotImageSize;
                                    var targetRobotFrame = frameTokenByKey[targetKey] || '00';
                                    var targetRobotSprite = '/images/robots/'+targetRobotImage+'/sprite_left_'+targetRobotImageSizeX+'.png?'+gameSettings.cacheTime;
                                    var targetRobotMarkup = '<div class="target">';
                                        targetRobotMarkup += '<div class="image">';
                                            targetRobotMarkup += '<div '
                                                + 'class="sprite sprite_'+targetRobotImageSizeX+' sprite_'+targetRobotImageSizeX+'_'+targetRobotFrame+'" '
                                                + 'style="background-image: url('+targetRobotSprite+');" '
                                                + 'data-size="'+targetRobotSprite+'" '
                                                + 'data-frame="'+targetRobotFrame+'" '
                                                + '>'+targetRobotName+'</div>';
                                        targetRobotMarkup += '</div>';
                                        targetRobotMarkup += '<div class="label">';
                                            targetRobotMarkup += '<span class="name">'+targetRobotName+'</span>';
                                        targetRobotMarkup += '</div>';
                                        targetRobotMarkup += '<i class="type '+targetRobotTypes+'"></i>';
                                    targetRobotMarkup += '</div>';
                                    $targetList.append(targetRobotMarkup);
                                    }
                                } else {
                                $targetList.append('<span class="loading">&hellip;</span>');
                                }

                            // end of voidRecipeWizard.refresh()
                            },
                        addItem: function(item){
                            console.log('%c' + 'voidRecipeWizard.addItem() w/ ' + item.token, 'color: magenta;');
                            //console.log('-> w/ item:', item);
                            const _self = this;
                            var token = item.token;
                            var existing = Object.keys(_self.items).length;
                            var exists = Object.keys(_self.items).indexOf(token) >= 0;
                            if (!exists && existing >= _self.maxItems){ return; }
                            if (!exists){ _self.items[token] = 0; }
                            _self.items[token] += 1;
                            _self.history.push({ token: token, action: 'add' });
                            _self.recalculate();
                            _self.regenerate();
                            _self.refresh();
                            // end of voidRecipeWizard.addItem()
                            },
                        removeItem: function(item){
                            console.log('%c' + 'voidRecipeWizard.removeItem() w/ ' + item.token, 'color: magenta;');
                            //console.log('-> w/ item:', item);
                            const _self = this;
                            var token = item.token;
                            var exists = Object.keys(_self.items).indexOf(token) >= 0;
                            if (!exists){ return; }
                            _self.items[token] -= 1;
                            if (_self.items[token] <= 0){ delete _self.items[token]; }
                            _self.history.push({ token: token, action: 'remove' });
                            _self.recalculate();
                            _self.regenerate();
                            _self.refresh();
                            // end of voidRecipeWizard.removeItem()
                            },
                        parseItem: function(item, quantity, powers){
                            console.log('%c' + 'voidRecipeWizard.parseItem() w/ ' + item.token + ' x' + quantity, 'color: magenta;');
                            //console.log('-> w/ item:', item, 'quantity:', quantity, 'powers:', powers);

                            // Backup a reference to the parent object
                            const _self = this;

                            // Collect the item token and then also break it apart for reference
                            var itemToken = item.token;
                            var itemTokens = itemToken.split('-');
                            var itemPrefix = itemTokens[0] || '';
                            var itemSuffix = itemTokens[1] || '';
                            var itemIsSmall = itemPrefix === 'small';
                            var itemIsLarge = itemPrefix === 'large';
                            var itemIsHyper = itemPrefix === 'hyper';
                            var itemIsEnergy = itemPrefix === 'energy';
                            var itemIsWeapons = itemPrefix === 'weapons';
                            var itemIsAttack = itemPrefix === 'attack';
                            var itemIsDefense = itemPrefix === 'defense';
                            var itemIsSpeed = itemPrefix === 'speed';
                            var itemIsSuper = itemPrefix === 'super';
                            var itemIsScrew = itemSuffix === 'screw';
                            var itemIsShard = itemSuffix === 'shard';
                            var itemIsCore = itemSuffix === 'core';
                            var itemIsPellet = itemSuffix === 'pellet';
                            var itemIsCapsule = itemSuffix === 'capsule';
                            var itemIsTank = itemSuffix === 'tank';
                            var itemIsUpgrade = itemSuffix === 'upgrade';
                            var itemIsMythic = itemSuffix === 'mythic';
                            var itemIsBooster = itemSuffix === 'booster';
                            var itemIsDiverter = itemSuffix === 'diverter';
                            var itemIsModule = itemSuffix === 'module';
                            var itemIsCircuit = itemSuffix === 'circuit';
                            if (itemToken === 'extra-life'){ itemIsEnergy = true; itemIsMythic = true; }
                            if (itemToken === 'yashichi'){ itemIsWeapons = true; itemIsMythic = true; }

                            // Increase the delta by one, always, for each item added
                            powers.incPower('delta', 1 * quantity);

                            // Check to see which group the item belongs to and then parse its values

                            // -- CYBER SCREWS w/ QUANTA + SPREAD
                            if (itemIsScrew){
                                var quanta = 0, spread = 0;
                                if (itemIsSmall){ quanta = 5.0, spread = 0.15; }
                                else if (itemIsLarge){ quanta = 10.0, spread = 0.25; }
                                else if (itemIsHyper){ quanta = 100.0, spread = 0.35; }
                                powers.incPower('quanta', quanta * quantity);
                                powers.incPower('spread', spread * quantity);
                                }
                            // -- PELLETS & CAPSULES w/ ^QUANTA + ~SPREAD [+ STATS]
                            else if (itemIsPellet || itemIsCapsule){
                                var statToken = itemPrefix;
                                if (!itemIsSuper){
                                    var quanta = (itemIsPellet ? 4.0 : 0) + (itemIsCapsule ? 9.0 : 0);
                                    var spread = (itemIsPellet ? 0.1 : 0) + (itemIsCapsule ? 0.2 : 0);
                                    var statValue = (itemIsPellet ? 2.0 : 0) + (itemIsCapsule ? 5.0 : 0);
                                    powers.incPower('quanta', quanta * quantity);
                                    powers.incPower('spread', spread * quantity);
                                    powers.incPower(statToken, statValue * quantity);
                                    } else {
                                    var statTokens = !itemIsSuper ? [statToken] : ['attack', 'defense', 'speed'];
                                    var quanta = (itemIsPellet ? 5.0 : 0) + (itemIsCapsule ? 10.0 : 0);
                                    var spread = (itemIsPellet ? 0.2 : 0) + (itemIsCapsule ? 0.4 : 0);
                                    powers.incPower('quanta', quanta * quantity);
                                    powers.incPower('spread', spread * quantity);
                                    for (var j = 0; j < statTokens.length; j++){
                                        var subStatToken = statTokens[j];
                                        var subStatValue = (itemIsPellet ? 1.0 : 0) + (itemIsCapsule ? 2.5 : 0);
                                        powers.incPower(subStatToken, subStatValue * quantity);
                                        }
                                    }
                                }
                            // -- ELEMENTAL CORES w/ ~QUANTA + ^SPREAD [+ TYPES]
                            else if (itemIsCore){
                                var typeToken = itemPrefix;
                                var quanta = 1.0, spread = 0.6, typeValue = 5.0;
                                powers.incPower('quanta', quanta * quantity);
                                powers.incPower('spread', spread * quantity);
                                powers.incPower(typeToken, typeValue * quantity);
                                }

                            // -- TANKS & UPGRADES & MYTHICS w/ LEVEL + FORTE [+ ~STATS]
                            else if (itemIsTank || itemIsUpgrade || itemIsMythic){
                                var statToken = itemIsEnergy ? 'energy' : 'weapons';
                                var statPower = (itemIsTank ? 10 : 0) + (itemIsUpgrade ? 20 : 0) + (itemIsMythic ? 50 : 0);
                                var boostKind = itemIsEnergy ? 'level' : 'forte';
                                var boostPower = itemPrefix === 'field' ? 1 : statPower;
                                powers.incPower(statToken, statPower * quantity);
                                powers.incPower(boostKind, boostPower * quantity);
                                }

                            // -- BOOSTERS & DIVERTERS w/ STATS [+ STAT-MODS]
                            else if (itemIsBooster){
                                var boostKind = itemPrefix !== 'field' ? itemPrefix : 'field';
                                var boostPower = itemPrefix !== 'field' ? 6 : 1;
                                powers.incPower(boostKind, boostPower * quantity);
                                }
                            else if (itemIsDiverter){
                                var divertOrder = [], divertValues = [10, 5, 5];
                                if (itemIsAttack){ divertOrder = ['attack', 'defense', 'speed']; }
                                else if (itemIsDefense){ divertOrder = ['defense', 'attack', 'speed']; }
                                else if (itemIsSpeed){ divertOrder = ['speed', 'attack', 'defense']; }
                                powers.decPower(divertOrder[0], divertValues[0] * quantity);
                                powers.incPower(divertOrder[1], divertValues[1] * quantity);
                                powers.incPower(divertOrder[2], divertValues[2] * quantity);
                                }

                            // ELEMENTAL CIRCUITS w/ TYPES [+ TYPE-MODS]
                            else if (itemIsCircuit){
                                var opposingTypes = [], opposingValues = [10, 10];
                                if (itemPrefix === 'battery'){ opposingTypes = ['electric', 'nature']; }
                                else if (itemPrefix === 'sponge'){ opposingTypes = ['water', 'electric']; }
                                else if (itemPrefix === 'forge'){ opposingTypes = ['flame', 'water']; }
                                else if (itemPrefix === 'sapling'){ opposingTypes = ['nature', 'flame']; }
                                else if (itemPrefix === 'chrono'){ opposingTypes = ['time', 'space']; }
                                else if (itemPrefix === 'cosmo'){ opposingTypes = ['space', 'time']; }
                                powers.incPower(opposingTypes[0], opposingValues[0] * quantity);
                                powers.decPower(opposingTypes[1], opposingValues[1] * quantity);
                                }

                            // -- MODULE ITEMS w/ SPECIAL EFFECTS
                            else if (itemIsModule){
                                if (itemPrefix === 'charge'){
                                    var quanta = powers.getPower('quanta') * 1.00;
                                    powers.incPower('quanta', quanta * quantity);
                                    }
                                else if (itemPrefix === 'target'){
                                    var spread = 1.00;
                                    powers.decPower('spread', spread * quantity);
                                    }
                                else if (itemPrefix === 'growth'){
                                    var effort = 1;
                                    powers.incPower('effort', effort * quantity);
                                    }
                                else if (itemPrefix === 'fortune'){
                                    var reward = 1;
                                    powers.incPower('reward', reward * quantity);
                                    }
                                else if (itemPrefix === 'guard'){
                                    powers.flags.guard = true;
                                    }
                                else if (itemPrefix === 'reverse'){
                                    powers.flags.reverse = true;
                                    }
                                else if (itemPrefix === 'extreme'){
                                    powers.flags.extreme = true;
                                    }
                                }

                            // end of voidRecipeWizard.parseItem()
                            },
                        filterStatPowers: function(powers, sort){
                            sort = typeof sort === 'undefined' ? true : sort;
                            console.log('%c' + 'voidRecipeWizard.filterStatPowers()', 'color: magenta;');
                            //console.log('-> w/ powers:', powers, 'sort:', sort);
                            // parse out powers that represent stats and then order them highest first
                            const _self = this;
                            var mmrpgStats = _self.indexes.statTokens;
                            var statPowers = {};
                            for (var i = 0; i < mmrpgStats.length; i++){
                                var statToken = mmrpgStats[i];
                                var statValue = powers[statToken] || 0;
                                if (statValue > 0){ statPowers[statToken] = statValue; }
                                }
                            //console.log('=> statPowers:', statPowers);
                            if (!sort){ return statPowers; }
                            // re-sort the stat powers based on their values w/ highest first
                            var statPowersKeys = Object.keys(statPowers);
                            statPowersKeys.sort(function(a, b){ return statPowers[b] - statPowers[a]; });
                            var sortedStatPowers = {};
                            for (var i = 0; i < statPowersKeys.length; i++){
                                var statToken = statPowersKeys[i];
                                var statValue = statPowers[statToken];
                                sortedStatPowers[statToken] = statValue;
                                }
                            //console.log('=> sortedStatPowers:', sortedStatPowers);
                            return sortedStatPowers;
                            // end of voidRecipeWizard.filterStatPowers()
                            },
                        filterTypePowers: function(powers, sort){
                            sort = typeof sort === 'undefined' ? true : sort;
                            console.log('%c' + 'voidRecipeWizard.filterTypePowers()', 'color: magenta;');
                            //console.log('-> w/ powers:', powers, 'sort:', sort);
                            // parse out powers that represent types and then order them highest first
                            const _self = this;
                            var mmrpgTypes = _self.indexes.typeTokens;
                            var typePowers = {};
                            for (var i = 0; i < mmrpgTypes.length; i++){
                                var typeToken = mmrpgTypes[i];
                                var typeValue = powers[typeToken] || 0;
                                if (typeValue > 0){ typePowers[typeToken] = typeValue; }
                                }
                            //console.log('=> typePowers:', typePowers);
                            if (!sort){ return typePowers; }
                            // re-sort the type powers based on their values w/ highest first
                            var typePowersKeys = Object.keys(typePowers);
                            typePowersKeys.sort(function(a, b){ return typePowers[b] - typePowers[a]; });
                            var sortedTypePowers = {};
                            for (var i = 0; i < typePowersKeys.length; i++){
                                var typeToken = typePowersKeys[i];
                                var typeValue = typePowers[typeToken];
                                sortedTypePowers[typeToken] = typeValue;
                                }
                            //console.log('=> sortedTypePowers:', sortedTypePowers);
                            return sortedTypePowers;
                            // end of voidRecipeWizard.filterTypePowers()
                            },
                        distributeQuanta: function(quanta, spread) {
                            console.log('%c' + 'voidRecipeWizard.distributeQuanta() w/ quanta: ' + quanta + ', spread: ' + spread, 'color: magenta;');
                            //console.log('-> w/ quanta:', quanta, 'spread:', spread);

                            // Define the main thresholds for primary slots
                            const _self = this;
                            const thresholds = { mecha: 25, master: 100, boss: 500 };
                            const tiers = Object.keys(thresholds);
                            const targets = [];

                            // Predefine variables to hold needed quanta and spread values
                            var numTargetSlots = spread;
                            var quantaAvailable = quanta;
                            var quantaRemaining = quantaAvailable;
                            //console.log('-> numTargetSlots:', numTargetSlots);
                            //console.log('-> quantaAvailable:', quantaAvailable);
                            //console.log('-> quantaRemaining:', quantaRemaining);

                            // We know the spread, so let's pre-populate with empty slots
                            //console.log('-> [step-1] populate targets array with placeholders!');
                            for (let i = 0; i < numTargetSlots; i++) {
                                targets.push({ tier: '', class: 'mecha', amount: 0 });
                                }
                            //console.log('-> step-1 // targets:', JSON.stringify(targets));
                            //console.log('-> step-1 // quantaAvailable:', quantaAvailable);
                            //console.log('-> step-1 // quantaRemaining:', quantaRemaining);

                            // Now let's loop through each tier, in order, and try to upgrade each slot
                            //console.log('-> [step-2] upgrade targets in array to upper tiers!');
                            for (let i = 0; i < tiers.length; i++){
                                let tier = tiers[i];
                                let threshold = thresholds[tier];
                                //console.log('-> processing tier:', tier, 'w/ threshold:', threshold);
                                for (let j = 0; j < targets.length; j++){
                                    let target = targets[j];
                                    let currentTier = target.tier;
                                    let currentAmount = target.amount;
                                    let needed = threshold - currentAmount;
                                    //console.log('-> processing target:', target, 'w/ currentTier:', currentTier, 'currentAmount:', currentAmount);
                                    //console.log('-> checking needed:', needed, 'vs. quantaRemaining:', quantaRemaining);
                                    if (needed <= 0){ continue; }
                                    if (quantaRemaining >= needed){
                                        //console.log('-> quantaRemaining >= needed!');
                                        quantaRemaining -= needed;
                                        targets[j] = { tier: tier, class: tier, amount: threshold };
                                        //console.log('-> updated target to tier:', targets[j].tier, 'class:', targets[j].class, 'amount:', targets[j].amount);
                                        }
                                    }
                                }
                            //console.log('-> step-2 // targets:', JSON.stringify(targets));
                            //console.log('-> step-2 // quantaAvailable:', quantaAvailable);
                            //console.log('-> step-2 // quantaRemaining:', quantaRemaining);

                            // If there's any remaining quanta, distribute it evenly across the slots
                            //console.log('-> [step-3] distribute remaining quanta evenly across slots!');
                            if (quantaRemaining > 0){
                                let quantaPerSlot = Math.floor(quantaRemaining / numTargetSlots);
                                let quantaOverflow = quantaRemaining % numTargetSlots;
                                //console.log('-> quantaPerSlot:', quantaPerSlot, 'quantaOverflow:', quantaOverflow);
                                for (let i = 0; i < targets.length; i++){
                                    let target = targets[i];
                                    let currentAmount = target.amount;
                                    let newAmount = currentAmount + quantaPerSlot;
                                    if (quantaOverflow > 0){
                                        newAmount += 1;
                                        quantaOverflow -= 1;
                                        }
                                    targets[i].amount = newAmount;
                                    //console.log('-> updated target:', targets[i]);
                                    }
                                }
                            //console.log('-> step-3 // targets:', JSON.stringify(targets));
                            //console.log('-> step-3 // quantaAvailable:', quantaAvailable);
                            //console.log('-> step-3 // quantaRemaining:', quantaRemaining);

                            // Return the list of generated targets
                            return targets;

                            // end of voidRecipeWizard.distributeQuanta()
                            },
                        generateTargetQueue: function(robots, types, stats){
                            console.log('%c' + 'voidRecipeWizard.generateTargetQueue()', 'color: magenta;');
                            //console.log('-> w/ robots:', robots, 'types:', types, 'stats:', stats);
                            // Collect important refs and indexes for processing
                            const _self = this;
                            const mmrpgIndexRobots = mmrpgIndex.robots;
                            const mmrpgIndexRobotsTokens = Object.keys(mmrpgIndexRobots);
                            var typePowers = types;
                            var statPowers = stats;
                            var allowTypes = Object.keys(types);
                            var sortByStats = Object.keys(stats);
                            var sortByTypes = Object.keys(types);
                            var targetQueue = Object.values(robots);
                            //console.log('=> targetQueue (base):', targetQueue);
                            // First we filter-out any robots that don't have elemental energy
                            //console.log('~> filtering targetQueue by core types....');
                            targetQueue = targetQueue.filter(function(token){
                                var types = [];
                                var info = mmrpgIndexRobots[token];
                                if (info.robot_core !== ''){ types.push(info.robot_core); }
                                if (types.length && info.robot_core2 !== ''){ types.push(info.robot_core2); }
                                if (!types.length){ types.push('none'); }
                                return allowTypes.indexOf(types[0]) !== -1 || allowTypes.indexOf(types[1]) !== -1;
                                });
                            //console.log('=> targetQueue (filtered):', targetQueue);
                            // First we sort the queue based on database order just to make everything consistent
                            //console.log('~> sorting targetQueue by database order....');
                            targetQueue.sort(function(a, b){
                                var orderValueA = mmrpgIndexRobotsTokens.indexOf(a);
                                var orderValueB = mmrpgIndexRobotsTokens.indexOf(b);
                                //console.log('-> comparing', a, 'w/ order:', orderValueA, 'vs.', b, 'w/ order:', orderValueB);
                                if (orderValueA !== orderValueB){ return orderValueA - orderValueB; }
                                return 0;
                                });
                            //console.log('=> targetQueue (sorted-by-order):', targetQueue);
                            // Last we re-sort the queue based on each robot's stats given stat-order priority w/ type-power bonuses
                            if (sortByStats.length || sortByTypes.length){
                                //console.log('~> sorting targetQueue by stats and/or types....');
                                targetQueue.sort(function(a, b){
                                    //console.log('--> comparing', a, 'vs.', b, '...');
                                    var tokenA = a, robotA = mmrpgIndexRobots[a];
                                    var tokenB = b, robotB = mmrpgIndexRobots[b];
                                    var robotValueA = 100, robotValueB = 100;
                                    if (sortByStats.length){
                                        //console.log('---> start-loop for sortByStats', sortByStats);
                                        for (var i = 0; i < sortByStats.length; i++){
                                            // Collect the stats for this robot so we can compare them
                                            var statToken = sortByStats[i];
                                            var statValueA = robotA['robot_' + statToken] || 0;
                                            var statValueB = robotB['robot_' + statToken] || 0;
                                            //console.log('----> comparing', tokenA, statToken, 'w/', statValueA, 'vs.', tokenB, statToken, 'w/', statValueB);
                                            robotValueA += (robotValueA * (statValueA / 100));
                                            robotValueB += (robotValueB * (statValueB / 100));
                                            }
                                        //console.log('---> after stat-compare', tokenA, 'robotValueA:', robotValueA, 'vs.', tokenB, 'robotValueB:', robotValueB);
                                        }
                                    if (sortByTypes.length){
                                        //console.log('---> start looping through sortByTypes', sortByTypes);
                                        for (var i = 0; i < sortByTypes.length; i++){
                                            // Then collect type value(s) for this robot so we can compare
                                            var typeToken = sortByTypes[i];
                                            var typeValue = typePowers[typeToken] || 0;
                                            var robotA_Type1 = robotA['robot_core'] || 'none';
                                            var robotA_Type2 = robotA['robot_core'] && robotA['robot_core2'] ? robotA['robot_core2'] : '';
                                            var robotB_Type1 = robotB['robot_core'] || 'none';
                                            var robotB_Type2 = robotB['robot_core'] && robotB['robot_core2'] ? robotB['robot_core2'] : '';
                                            var robotA_TypeValue = (robotA_Type1 === typeToken ? typeValue : 0) + (robotA_Type2 === typeToken ? typeValue : 0);
                                            var robotB_TypeValue = (robotB_Type1 === typeToken ? typeValue : 0) + (robotB_Type2 === typeToken ? typeValue : 0);
                                            //console.log('----> comparing', tokenA, typeToken, 'w/', robotA_TypeValue, 'vs.', tokenB, typeToken, 'w/', robotB_TypeValue);
                                            robotValueA += (robotValueA * (robotA_TypeValue / 100));
                                            robotValueB += (robotValueB * (robotB_TypeValue / 100));
                                            }
                                        //console.log('---> after type-compare', tokenA, 'robotValueA:', robotValueA, 'vs.', tokenB, 'robotValueB:', robotValueB);
                                        }
                                    //console.log('---> FINAL compare for ', tokenA, 'robotValueA:', robotValueA, 'vs.', tokenB, 'robotValueB:', robotValueB);
                                    if (robotValueA !== robotValueB){ return robotValueB - robotValueA; }
                                    return 0;
                                    });
                                //console.log('=> targetQueue (sorted-by-stats)[+type]:', targetQueue);
                                }
                            return targetQueue;
                            // end of voidRecipeWizard.generateTargetQueue()
                            },
                        };

                    // Initialize the void recipe calculator
                    //console.log('%c' + 'Initializing the voidRecipeWizard()', 'color: green;');
                    voidRecipeWizard.init($voidRecipeWizard);

                    })();
                }

        })();
    </script>
<? $website_include_javascript .= ob_get_clean(); ?>