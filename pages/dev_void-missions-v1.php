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
$mmrpg_index_types = rpg_type::get_index();
$mmrpg_index_players = rpg_player::get_index();
$mmrpg_index_robots = rpg_robot::get_index();
$mmrpg_index_abilities = rpg_ability::get_index();
$mmrpg_index_items = rpg_item::get_index();
$mmrpg_index_fields = rpg_field::get_index();

?>
<div class="header">
    <div class="header_wrapper">
        <h1 class="title"><span class="brand">Mega Man RPG</span><span> Void Missions</span></h1>
    </div>
</div>
<h2 class="subheader field_type_<?= !empty($this_field_info['field_type']) ? $this_field_info['field_type'] : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
    Experimental Proceedural Mission Generator
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
                <li>SCREWS = QUANTITY/CLASS</li>
                <li>COREES = ELEMENT(S)</li>
                <li>EDIBLES = SORT/SOURCE</li>
                <li>SPECIAL = LEVEL/STATS</li>
                <li>PARTS = ITEMS/BUFFS/DEBUFFS</li>
            </ul>
        </div>
    </div>

    <?

    // Start the output buffer to collect generated item markup
    ob_start();

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
            // Skip this item if it's an event item or a special token
            if ($item_info['item_subclass'] === 'event'){ continue; }
            elseif (substr($item_token, -6) === '-shard'){ continue; }
            elseif (substr($item_token, -5) === '-star'){ continue; }
            // Break apart the item token into individual words so we can better determine its group
            $item_tokens = strstr($item_token, '-') ? explode('-', $item_token) : array($item_token);
            if (!isset($item_tokens[1])){ $item_tokens[1] = '';  }
            // Default to the 'other' group but then check tokens to better categorize
            $group_token = 'other';
            if ($item_tokens[1] === 'screw'){ $group_token = 'screws'; }
            elseif ($item_tokens[1] === 'core'){ $group_token = 'cores'; }
            elseif (in_array($item_token, array('growth-module', 'fortune-module'))){ $group_token = 'special'; }
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
            <strong>The Void Cauldron: Proceedural Mission Generator</strong>
        </div>
        <div class="creation">
            <div class="target-list">
                <span class="loading">&hellip;</span>
            </div>
            <div class="mission-details">
                <span class="loading">&hellip;</span>
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
            <div class="item-list" data-count="<?= $items_palette_count ?>">
                <?= $items_palette_markup ?>
            </div>
        </div>
    </div>

    <?

    // Generate a mission index using the collected robot and hazard data
    $mmrpg_void_mission = array();

    echo('<pre style="font-size: 11px;">$mmrpg_void_mission = '.print_r($mmrpg_void_mission, true).'</pre>');
    //echo('<pre>$mmrpg_robots_cores = '.print_r($mmrpg_robots_cores, true).'</pre>');

    ?>

    <

</div>

<? ob_start(); ?>
<style type="text/css">


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
    #void-recipe .creation .target-list,
    #void-recipe .selection .item-list,
    #void-recipe .palette .item-list {
        padding-top: 16px;
    }
    #void-recipe .selection .item-list .wrapper,
    #void-recipe .palette .item-list .wrapper {
        top: 16px;
    }

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
    #void-recipe .item-list .item[data-quantity="0"] {
        filter: opacity(0.6) brightness(0.9);
        pointer-events: none;
        cursor: not-allowed;
    }
    #void-recipe .item-list .item[data-quantity="0"][data-base-quantity="0"] .icon {
        filter: brightness(0);
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

    #void-recipe .item-list .group.screws {
        background-color: #807c18;
    }
    #void-recipe .item-list .group.special {
        background-color: #6b854b;
    }
    #void-recipe .item-list .group.pellets,
    #void-recipe .item-list .group.capsules {
        background-color: #44795a;
    }
    #void-recipe .item-list .group.circuits {
        background-color: #783078;
    }
    #void-recipe .item-list .group.energies {
        background-color: #107981;
    }
    #void-recipe .item-list .group.cores {
        background-color: #58589b;
    }
    #void-recipe .item-list .group.boosters,
    #void-recipe .item-list .group.diverters {
        background-color: #8d386e;
    }
    #void-recipe .item-list .group.modules {
        background-color: #93521d;
    }
    #void-recipe .item-list .group.fillers {
        background-color: #883030;
    }

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


    #void-recipe .creation {
        border-radius: 6px;
        box-shadow: 0 0 6px rgba(0, 0, 0, 0.2);
    }
    #void-recipe .creation .target-list,
    #void-recipe .creation .mission-details {
        display: block;
        margin: 0 auto;
        width: auto;
        height: auto;
        min-width: 180px;
        min-height: 50px;
        position: relative;
        overflow: visible;
        border: 1px solid #1A1A1A;
        border-radius: 6px;
    }
    #void-recipe .creation .target-list {
        height: 90px;
        border-radius: 6px 6px 0 0;
        border-bottom: 0;
    }
    #void-recipe .creation .mission-details {
        height: 60px;
        border-radius: 0 0 6px 6px;
        border-top: 0;
    }

    #void-recipe .creation .target-list {
        background-color: #292834;
    }
    #void-recipe .creation .mission-details {
        background-color: #2d2c3a;
    }
    #void-recipe .selection .item-list {
        background-color: #25232e;
    }
    #void-recipe .palette .item-list {
        background-color: #353144;
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

                // Pre-collect a list of robot tokens that we can use later
                var mmrpgIndexRobots = mmrpgIndex.robots;
                var mmrpgIndexRobotsTokens = Object.keys(mmrpgIndexRobots);

                // Create a VOID RECIPE WIZARD so we can easily add/remove and recalculate on-the-stop
                var voidRecipeWizard = {
                    init: function($container){
                        console.log('voidRecipeWizard.init()');
                        console.log('-> w/ $container:', typeof $container, $container.length, $container);
                        var _self = this;
                        _self.reset(false);
                        _self.setup($container);
                        _self.regenerate();
                        _self.refresh();
                        },
                    reset: function(refresh){
                        console.log('voidRecipeWizard.reset()');
                        if (typeof refresh === 'undefined'){ refresh = true; }
                        var _self = this;
                        _self.items = [];
                        _self.values = {};
                        _self.mission = {};
                        _self.history = [];
                        if (!refresh){ return; }
                        _self.regenerate();
                        _self.refresh();
                        },
                    setup: function($container){
                        console.log('voidRecipeWizard.setup()');
                        console.log('-> w/ $container:', typeof $container, $container.length, $container);

                        // Backup a reference to the parent object
                        var _self = this;

                        // Predefine some parent variables for the class
                        _self.xrefs = {};
                        _self.items = [];
                        _self.values = {};
                        _self.mission = {};
                        _self.history = [];

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
                        console.log('xrefs:', xrefs);

                        // Backup every item's base quantity so we can do dynamic calulations in realt-time
                        $('.item[data-quantity]:not([data-base-quantity])', $parentDiv).each(function(){
                            var $item = $(this);
                            var quantity = parseInt($item.attr('data-quantity'));
                            $item.attr('data-base-quantity', quantity);
                            });

                        // Bind ADD ITEM click events to the palette area's item list buttons
                        $('.item[data-token]', $itemsPalette).live('click', function(e){
                            console.log('palette button clicked! add-item');
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
                            _self.add(itemInfo);
                            });

                        // Bind REMOVE ITEM click events to the selection area's item list buttons
                        $('.item[data-token]', $itemsSelected).live('click', function(e){
                            console.log('section button clicked! remove-item');
                            e.preventDefault();
                            var $item = $(this);
                            var itemToken = $item.attr('data-token');
                            var itemGroup = $item.attr('data-group');
                            var itemQuantity = parseInt($item.attr('data-quantity'));
                            var itemIndex = parseInt($item.attr('data-key'));
                            var itemInfo = {token: itemToken, group: itemGroup, quantity: itemQuantity, index: itemIndex};
                            //console.log('item clicked:', $item);
                            //console.log('item details:', itemInfo);
                            _self.remove(itemInfo);
                            });

                        // Bind RESET ITEMS click events to the selection area's reset button
                        $resetButton.live('click', function(e){
                            console.log('reset button clicked! reset-items');
                            e.preventDefault();
                            _self.reset();
                            });

                        },
                    add: function(item){
                        //console.log('voidRecipeWizard.add()', item);
                        var _self = this;
                        var token = item.token;
                        _self.items.push(token);
                        _self.history.push({ token: token, action: 'add' });
                        _self.regenerate();
                        _self.refresh();
                        },
                    remove: function(item){
                        //console.log('voidRecipeWizard.remove()', item);
                        var _self = this;
                        var token = item.token;
                        var index = this.items.lastIndexOf(token);
                        _self.items.splice(index, 1);
                        _self.history.push({ token: token, action: 'remove' });
                        _self.regenerate();
                        _self.refresh();
                        },
                    regenerate: function(){
                        console.log('voidRecipe.regenerate()');

                        // Backup a reference to the parent object
                        var _self = this;

                        // Collect a reference to the void values object and reset
                        var voidItems = _self.items;
                        var voidValues = _self.values;
                        voidValues.added = {};
                        voidValues.classes = {};
                        voidValues.types = {};
                        voidValues.stats = {};
                        voidValues.shift = 0;

                        // Loop through all the items, one-by-one, and parse their intrinsic values
                        for (var i = 0; i < voidItems.length; i++){

                            // Collect the item token and then also break it apart for reference
                            var itemToken = voidItems[i];
                            var itemTokens = itemToken.split('-');
                            if (typeof itemTokens[1] === 'undefined'){ itemTokens[1] = ''; }

                            // Always increment the added counter (just for reference)
                            voidValues.added[itemToken] = voidValues.added[itemToken] || 0;
                            voidValues.added[itemToken] += 1;

                            // Check to see which group the item belongs to and then parse its values
                            if (itemTokens[1] === 'screw'){
                                if (itemTokens[0] === 'small'){ var classToken = 'mecha'; }
                                else if (itemTokens[0] === 'large'){ var classToken = 'master'; }
                                else if (itemTokens[0] === 'hyper'){ var classToken = 'boss'; }
                                voidValues.classes[classToken] = voidValues.classes[classToken] || 0;
                                voidValues.classes[classToken] += 1;
                                }
                            else if (itemTokens[1] === 'core'){
                                var typeToken = itemTokens[0];
                                voidValues.types[typeToken] = voidValues.classes[typeToken] || 0;
                                voidValues.types[typeToken] += 1;
                                }
                            else if (itemTokens[1] === 'pellet' || itemTokens[1] === 'capsule'){
                                var statToken = itemTokens[0];
                                var statTokens = !isSuper ? [statToken] : ['attack', 'defense', 'speed'];
                                var isSuper = itemTokens[0] === 'super';
                                var isPellet = itemTokens[1] === 'pellet';
                                var isCapsule = itemTokens[1] === 'capsule';
                                var statValue = (!isSuper ? (isPellet ? 2 : 5) : (isPellet ? 1 : 3));
                                var shiftValue = (!isSuper ? (isPellet ? 1 : 3) : (isPellet ? 2 : 5));
                                for (var j = 0; j < statTokens.length; j++){
                                    var token = statTokens[j];
                                    voidValues.stats[token] = voidValues.classes[token] || 0;
                                    voidValues.stats[token] += statValue;
                                    }
                                voidValues.shift += shiftValue;
                                }
                        }

                        //console.log('-> items:\n', voidItems.join(', '));
                        //console.log('-> values:', voidValues);

                        // Sort the stats object so we can easily calculate the target values
                        var sortedStats = Object.keys(voidValues.stats).sort(function(a, b){
                            return voidValues.stats[b] - voidValues.stats[a];
                            });
                        voidValues.stats = sortedStats;

                        // Now that we have the values sorted out, let's calculate our target queue
                        var targetQueue = [];

                        // Define a sample of targets to use for this creation and the sort or filter as needed
                        var targetTokenPool = [];
                        if (voidItems.length){
                            var allowedClasses = Object.keys(voidValues.classes) || [];
                            var allowedTypes = Object.keys(voidValues.types) || [];
                            var allowedStats = Object.keys(voidValues.stats) || [];
                            for (var i = 0; i < mmrpgIndexRobotsTokens.length; i++){
                                var robotToken = mmrpgIndexRobotsTokens[i];
                                var robotInfo = mmrpgIndexRobots[robotToken];
                                var robotClass = robotInfo['robot_class'];
                                var robotTypes = [robotInfo['robot_core'], robotInfo['robot_core2']];
                                if (allowedClasses.indexOf(robotClass) === -1){ continue; }
                                if (allowedTypes.indexOf(robotTypes[0]) === -1 && allowedTypes.indexOf(robotTypes[1]) === -1){ continue; }
                                targetTokenPool.push(robotToken);
                                }
                            if (!targetTokenPool.length){ targetTokenPool.push('dark-frag'); }
                            }

                        // Loop through the available slots and attempt to fill them
                        var slotClasses = Object.keys(voidValues.classes);
                        for (var i = 0; i < slotClasses.length; i++){
                            var slotClass = slotClasses[i];
                            var slotValue = voidValues.classes[slotClass];
                            for (var j = 0; j < slotValue; j++){
                                targetData = '';
                                targetData += '{'+slotClass+'}';
                                targetQueue.push(targetData);
                                }
                            }
                        //console.log('-> targetQueue:\n', targetQueue);
                        //console.log('-> targetTokenPool:\n', targetTokenPool);

                        //console.log('-> mission:', _self.mission);

                        },
                    refresh: function(){
                        console.log('voidRecipe.refresh()');

                        // Backup a reference to the parent object
                        var _self = this;

                        // Collect a reference to the void values
                        var voidItems = _self.items;
                        var voidHistory = _self.history;
                        var voidValues = _self.values;
                        var $itemsSelected = _self.xrefs.itemsSelected;
                        var $itemsPalette = _self.xrefs.itemsPalette;
                        var $resetButton = _self.xrefs.resetButton;

                        // Check to see which was the last item token added
                        var lastItemToken = '';
                        if (voidHistory.length){
                            lastItemToken = voidHistory[voidHistory.length - 1].token;
                            }

                        // Clear the item selection area and then rebuild it with the new items
                        var $selectedWrapper = $('.wrapper', $itemsSelected);
                        $selectedWrapper.html('');
                        var usedItemTokens = Object.keys(voidValues.added);
                        var numSlotsAvailable = 10;
                        var numSlotsUsed = usedItemTokens.length;
                        if (usedItemTokens.length > 0){
                            for (var i = 0; i < usedItemTokens.length; i++){
                                var itemToken = usedItemTokens[i];
                                var itemInfo = mmrpgIndex.items[itemToken];
                                var itemName = itemInfo.item_name;
                                var itemNameBr = itemName.replace(' ', '<br />');
                                var itemQuantity = voidValues.added[itemToken];
                                var itemImage = itemInfo.item_image || itemToken;
                                var itemClass = 'item' + (itemToken === lastItemToken ? ' recent' : '');
                                var itemIcon = '/images/items/'+itemImage+'/icon_right_40x40.png?'+gameSettings.cacheDate;
                                var itemMarkup = '<div class="'+itemClass+'" data-token="'+itemToken+'" data-quantity="'+itemQuantity+'">';
                                    itemMarkup += '<div class="icon"><img class="has_pixels" src="'+itemIcon+'" alt="'+itemName+'"></div>';
                                    itemMarkup += '<div class="name">'+itemNameBr+'</div>';
                                    itemMarkup += '<div class="quantity">'+itemQuantity+'</div>';
                                itemMarkup += '</div>';
                                $selectedWrapper.append(itemMarkup);
                                }
                            }
                        if (numSlotsUsed < numSlotsAvailable){
                            var emptySlots = numSlotsAvailable - numSlotsUsed;
                            for (var i = 0; i < emptySlots; i++){
                                var placeholderMarkup = '<div class="item placeholder"></div>';
                                $selectedWrapper.append(placeholderMarkup);
                                }
                            }

                        // Check and update the displayed quantities of any items that have been interacted with
                        var itemsWithHistory = [];
                        for (var i = 0; i < voidHistory.length; i++){
                            var itemToken = voidHistory[i].token;
                            if (itemsWithHistory.indexOf(itemToken) !== -1){ continue; }
                            else { itemsWithHistory.push(itemToken); }
                            }
                        if (itemsWithHistory.length > 0){
                            for (var i = 0; i < itemsWithHistory.length; i++){
                                var itemToken = itemsWithHistory[i];
                                var itemInfo = mmrpgIndex.items[itemToken];
                                var $paletteButton = $('.item[data-token="'+itemToken+'"]', $itemsPalette);
                                var $selectButton = $('.item[data-token="'+itemToken+'"]', $itemsSelected);
                                var baseQuantity = parseInt($paletteButton.attr('data-base-quantity'));
                                var addedQuantity = voidValues.added[itemToken] || 0;
                                var newQuantity = baseQuantity - addedQuantity;
                                $paletteButton.attr('data-quantity', newQuantity);
                                $paletteButton.find('.quantity').text(newQuantity);
                                //console.log('updating', itemToken, 'button in palette w/', {baseQuantity: baseQuantity, addedQuantity: addedQuantity, newQuantity: newQuantity});
                                }
                            }

                        // Show or hide the reset button depending on whether or not there's a selection to reset
                        if (numSlotsUsed > 0){ $resetButton.addClass('visible'); }
                        else { $resetButton.removeClass('visible'); }

                        },
                    };

                // Initialize the void recipe calculator
                voidRecipeWizard.init($voidRecipeWizard);


                })();
            }

    })();
</script>
<? $website_include_javascript .= ob_get_clean(); ?>