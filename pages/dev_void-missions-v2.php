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
require_once('pages/dev_void-missions-v2_data.php');

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
        Void Recipe Calculator <sup style="margin-left: 4px; font-size: 60%; position: relative; bottom: 6px;">v-0.2.x</sup>
    </h3>
    <div class="subbody">
        <div class="text">
            <p>
                <u>Void Missions</u> are procedurally-generated missions (maybe) coming to MMRPG Prototype.
            </p>
            <p>
                <strong><u>TLDR</u>; Throw items into The Void to generate a new mission.</strong>
                <br />&bull;  Required items are <u>Screws</u> (for <i>quanta</i> material) and <u>Cores</u> (for elemental affinity).
                <br />&bull;  <i>Quanta</i> can optionally be <i>spread</i> to multiple slots via the <u>Spreader Modules</u>.
                <br />&bull;  Distrubted <i>Quanta</i> can be <i>focused</i> to the active-position via the <u>Target Modules</u>.
                <br />&bull;  Kind, quantity, and order of items-added determine exact targets, location, rewards, etc.
                <br />&bull;  Available targets have <u>power-scaled quanta requirements</u> determined by their <i>BSTs</i>.
                <br /> <em style="display: inline-block; padding-left: 20px; font-size: 80%; color: #cacaca;">
                        (!) Base Stat Totals (BSTs) are the combined sum of a robot's Energy, Weapons, Attack, Defense, Speed.
                        <br /> Weapon values are multiplied by 10 for normalization purposes. There are more than three tiers.
                        </em>
                <br />&bull;  This is an always-changing work-in-progress <em>concept</em> of an idea, please be kind/patient.
                <br />&bull;  Otherwise, feel free to experiment and thanks for <a href="#void-recipe_anchor">checking it out</a>!
            </p>
        </div>
        <a id="void-recipe_anchor"></a>
    </div>

    <div id="void-recipe">
        <div id="vcr_upper">
            <div id="vcr_title">
                <strong class="main">Void Cauldron</strong>
                <em class="sub">&bull; Select Mix Items to Generate New Mission &bull;</em>
                <span class="state"></span>
            </div>
            <div id="vcr_field">
                <div class="sprite background memory-filter"
                    data-token="prototype-subspace"
                    style="background-image: url(/images/fields/prototype-subspace/battle-field_background_base.gif?20241104-0121);"
                    >&nbsp;</div>
                <div class="sprite foreground memory-filter"
                    data-token="prototype-subspace"
                    style="background-image: url(/images/fields/prototype-subspace/battle-field_foreground_base.png?20241104-0121);"
                    >&nbsp;</div>
            </div>
            <div id="vcr_details"></div>
            <div id="vcr_targets"></div>
        </div>
        <div id="vcr_lower">
            <div id="vcr_selection"></div>
            <div id="vcr_palette"></div>
        </div>
        <div id="vcr_effects">
            <div class="black-hole">
                <div class="layer first"></div>
                <div class="layer second"></div>
                <div class="layer third"></div>
                <div class="layer fourth"></div>
            </div>
        </div>
    </div>

    <div id="vcr_debug"></div>

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
require_once('pages/dev_void-missions-v2_styles.css.php');
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
require_once('pages/dev-scripts/void-cauldron.dev-v2.js');
require_once('pages/dev_void-missions-v2_scripts.js.php');
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
