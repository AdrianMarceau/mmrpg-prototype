<?

/*
 * DEV TESTS / VOID MISSIONS
 */

// Define the constant that puts the front-end in compact mode
define('MMRPG_INDEX_COMPACT_MODE', true);

// Require the temporary shivs file for the void missions
require_once('pages/dev_shivs.php');

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

// Pull in the void items index and other data we'll need momentaryily
$void_item_groups_index = array();
$void_items_disabled = array();
require_once('pages/dev_void-missions-v1_data.php');

/*
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
*/

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
        $all_items_x99 = !empty($_GET['allx99']) ? true : false;
        $num_items_total = 0;
        $curr_item_rowline = 0;
        $group_markup_by_step = array();
        $void_item_groups_count = count($void_item_groups_index);
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
                    $item_is_disabled = in_array($item_token, $void_items_disabled);
                    $item_quantity = $all_items_x99 ? 99 : mt_rand(33, 99); //mt_rand(0, 99);
                    $item_image = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_token;
                    $icon_url = '/images/items/'.$item_image.'/icon_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
                    ob_start();
                    echo('<div class="item'.($item_is_disabled ? ' disabled' : '').'" '.
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
            $step_layer = $step_num; // changes dynamically
            $step_side = 'middle';
            if ($step_num < round($void_item_groups_count / 2)){ $step_side = 'left'; }
            if ($step_num > round($void_item_groups_count / 2)){ $step_side = 'right'; }
            $step_is_active = $step_num === 1 ? true : false;
            $wrapper_attrs = '';
            $wrapper_attrs .= 'data-step="'.$step_num.'"';
            $wrapper_attrs .= 'data-side="'.$step_side.'"';
            $wrapper_attrs .= 'data-layer="'.$step_layer.'"';
            $wrapper_class = 'wrapper'.($step_is_active ? ' active' : '');
            echo('<div class="'.$wrapper_class.'" '.$wrapper_attrs.'>'.PHP_EOL);
                echo('<div class="label">'.PHP_EOL);
                    echo('<strong>'.$step_name.' ('.$step_label.')</strong>'.PHP_EOL);
                echo('</div>'.PHP_EOL);
                echo('<div class="groups">'.implode(PHP_EOL, $wrapped_group_markup).'</div>'.PHP_EOL);
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
            <div class="black-hole">
                <div class="wrapper">
                    <div class="layer first"></div>
                    <div class="layer second"></div>
                    <div class="layer third"></div>
                    <div class="layer fourth"></div>
                </div>
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
            <a class="button reset"><i class="fa fas fa-undo"></i></a>
            <a class="button code"><i class="fa fas fa-code"></i></a>
        </div>
    </div>

    <div class="subbody">
        <div class="legend">
            <ul>
                <li>SCREWS = QUANTA</li>
                <li>CORES = SPREAD (+TYPE)</li>
                <li>OTHERS = ???</li>
            </ul>
        </div>
    </div>

</div>

<?

// Check to see if we should be minifying the inline dependencies
$minify_inline_markup = false;
if (MMRPG_CONFIG_IS_LIVE){ $minify_inline_markup = true; }
//elseif (true){ $minify_inline_markup = true; } // TEMP TEMP TEMP override for testing

// Require and instantiate the minify library in case we need it
require_once(MMRPG_CONFIG_ROOTDIR.'.libs/minify.php');
require_once(MMRPG_CONFIG_ROOTDIR.'.libs/path-converter.php');
use MatthiasMullie\Minify;

// Include the stylesheet markup for this page as if it were inline
ob_start();
require_once('pages/dev_void-missions-v1_styles.css.php');
$custom_styles = trim(ob_get_clean());
if ($minify_inline_markup){
    $minifier = new Minify\CSS();
    $minifier->add($custom_styles);
    $custom_styles = $minifier->minify();
    }
$website_include_stylesheets .= (
    '<style type="text/css">'.PHP_EOL.
    '    '.$custom_styles.PHP_EOL.
    '</style>'.PHP_EOL
    );

// Include the javascript marup for this page as if it were inline
ob_start();
require_once('pages/dev_void-missions-v1_scripts.js.php');
$custom_scripts = ob_get_clean();
if ($minify_inline_markup){
    $minifier = new Minify\JS();
    $minifier->add($custom_scripts);
    $custom_scripts = $minifier->minify();
    }
$website_include_javascript .= (
    '<script type="text/javascript">'.PHP_EOL.
    '    '.$custom_scripts.PHP_EOL.
    '</script>'.PHP_EOL
    );

?>
