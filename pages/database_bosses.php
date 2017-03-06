<?php
/*
 * BOSS DATABASE AJAX
 */

// If an explicit return request for the index was provided
if (!empty($_REQUEST['return']) && $_REQUEST['return'] == 'index'){
    // Exit with only the database link markup
    exit($mmrpg_database_bosses_links);
}


/*
 * BOSS DATABASE PAGE
 */

// Define the SEO variables for this page
$this_seo_title = 'Bosses '.(!empty($this_current_filter) ? '('.$this_current_filter_name.' Type) ' : '').'| Database | '.$this_seo_title;
$this_seo_description = 'The boss database contains detailed information about the Mega Man RPG Prototype\'s fortress bosses including their equippable abilities, battle quotes, base stats, weaknesses, resistances, affinities, immunities, and sprite sheets. The Mega Man RPG Prototype is a browser-based fangame that combines the bossnics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Boss Database'.(!empty($this_current_filter) ? ' ('.$this_current_filter_name.' Type) ' : '');
$this_graph_data['description'] = 'The boss database contains detailed information about the Mega Man RPG Prototype\'s fortress bosses including their equippable abilities, battle quotes, base stats, weaknesses, resistances, affinities, immunities, and sprite sheets.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
//$this_markup_header = 'Mega Man RPG Prototype Boss Database <span class="count">( '.(!empty($mmrpg_database_bosses_count) ? ($mmrpg_database_bosses_count == 1 ? '1 Boss' : $mmrpg_database_bosses_count.' Bosses') : '0 Bosses').' )';
$this_markup_header = 'Mega Man RPG Prototype Boss Database';
//$this_markup_counter = '<span class="count count_header">( '.(!empty($mmrpg_database_bosses_links_counter) ? ($mmrpg_database_bosses_links_counter == 1 ? '1 Boss' : $mmrpg_database_bosses_links_counter.' Bosses') : '0 Bosses').' )</span>';

// If a specific boss has NOT been defined, show the quick-switcher
reset($mmrpg_database_bosses);
if (!empty($this_current_token)){ $first_boss_key = $this_current_token; }
else { $first_boss_key = key($mmrpg_database_bosses); }

/*
// GENERATE BOSS QUOTES CSV
$temp_quotes = array('start', 'taunt', 'victory', 'defeat');
$temp_csv = array();
$temp_csv[] = array('Boss Name', 'Boss Token', 'Start Quote', 'Taunt Quote', 'Victory Quote', 'Defeat Quote');
foreach ($mmrpg_database_bosses AS $key => $info){
    $row = array($info['robot_number'].' '.$info['robot_name'], $info['robot_token']);
    foreach ($temp_quotes AS $type){ $row[] = $info['robot_quotes']['battle_'.$type]; }
    $temp_csv[] = $row;
}
foreach ($temp_csv AS $key => $row){
    $row2 = array();
    foreach ($row AS $val){ $row2[] = '"'.str_replace('"', '\\"', $val).'"'; }
    $temp_csv[$key] = implode(',', $row2);
}
$temp_csv = implode(",\n", $temp_csv);
header('Content-type: text/plain; charset=UTF-8');
die(print_r($temp_csv, true));
*/

/*
// GENERATE BOSS DESCRIPITIONS CSV
$temp_csv = array();
$temp_csv[] = array('Boss Name', 'Boss Token', 'Boss Class', 'Boss Description');
foreach ($mmrpg_database_bosses AS $key => $info){
    $row = array($info['robot_number'].' '.$info['robot_name'], $info['robot_token'], $info['robot_description'], $info['robot_description2']);
    $temp_csv[] = $row;
}
foreach ($temp_csv AS $key => $row){
    $row2 = array();
    foreach ($row AS $val){ $row2[] = '"'.str_replace('"', '\\"', $val).'"'; }
    $temp_csv[$key] = implode(',', $row2);
}
$temp_csv = implode(",\n", $temp_csv);
header('Content-type: text/plain; charset=UTF-8');
die(print_r($temp_csv, true));
*/

// Only show the next part of a specific boss was requested
if (!empty($this_current_token)){

    // Loop through the boss database and display the appropriate data
    $key_counter = 0;
    $this_current_key = false;
    foreach($mmrpg_database_bosses AS $boss_key => $boss_info){
        //die('<pre>$mmrpg_database_bosses[$boss_key] = '.print_r($mmrpg_database_bosses[$boss_key], true).'</pre>');

        // If a specific boss has been requested and it's not this one
        if (!empty($this_current_token) && $this_current_token != $boss_info['robot_token']){ $key_counter++; continue; }
        //elseif ($key_counter > 0){ continue; }

        // If this is THE specific boss requested (and one was specified)
        if (!empty($this_current_token) && $this_current_token == $boss_info['robot_token']){
            $this_current_key = $boss_key;

            $this_boss_image = !empty($boss_info['robot_image']) ? $boss_info['robot_image'] : $boss_info['robot_token'];
            $this_boss_image_size = (!empty($boss_info['robot_image_size']) ? $boss_info['robot_image_size'] : 40) * 2;
            $this_boss_image_size_text = $this_boss_image_size.'x'.$this_boss_image_size;
            if ($this_boss_image == 'boss'){ $this_seo_bosses = 'noindex'; }

            // Check if this is a boss and prepare extra text
            $boss_info['robot_name_append'] = '';

            // Define the SEO variables for this page
            $this_seo_title_backup = $this_seo_title;
            $this_seo_title = $boss_info['robot_name'].$boss_info['robot_name_append'].' | '.$this_seo_title;
            $this_seo_description = $boss_info['robot_number'].' '.$boss_info['robot_name'].', the '.$boss_info['robot_description'].', ';
            $this_seo_description .= 'is a '.rpg_robot::get_best_stat_desc($boss_info).' ';
            $this_seo_description .= !empty($boss_info['robot_core']) ? ucwords($boss_info['robot_core']).' ' : 'Neutral ';
            if (!empty($boss_info['robot_core2'])){ $this_seo_description .= '/ '.ucfirst($boss_info['robot_core2']).' '; }
            $this_seo_description .= 'Core foretress boss from the Mega Man RPG Prototype. ';

            // Define the Open Graph variables for this page
            $this_graph_data['title'] .= ' | '.$boss_info['robot_name'];
            $this_graph_data['description'] = $this_seo_description;
            $this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/robots/'.$boss_info['robot_token'].'/mug_right_'.$this_robot_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;

        }

        // Collect the markup for this boss and print it to the browser
        //die('<pre>$boss_info = '.print_r($boss_info, true).'</pre>');
        $temp_boss_markup = rpg_robot::print_database_markup($boss_info, array('show_key' => $key_counter));
        echo $temp_boss_markup;
        $key_counter++;
        break;

    }

}

// Only show the header if a specific boss has not been selected
if (empty($this_current_token)){
    ?>
    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="database/bosses/">Boss Database</a>
            <span class="count">( <?= $mmrpg_database_bosses_count_complete ?> / <?= $mmrpg_database_bosses_count == 1 ? '1 Boss' : $mmrpg_database_bosses_count.' Bosses' ?> )</span>
            <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Core )</span>' : '' ?>
        </span>
    </h2>
    <?php
}

?>

<div class="subbody subbody_databaselinks <?= empty($this_current_token) ? 'subbody_databaselinks_noajax' : '' ?>" data-class="bosses" data-class-single="boss" data-basetitle="<?= isset($this_seo_title_backup) ? $this_seo_title_backup : $this_seo_title ?>" data-current="<?= !empty($this_current_token) ? $this_current_token : '' ?>">
    <? if(empty($this_current_token)): ?>

        <div class="<?= !empty($this_current_token) ? 'toggle_body' : '' ?>" style="<?= !empty($this_current_token) ? 'display: none;' : '' ?>">
            <p class="text" style="clear: both;">
                The boss database contains detailed information on <?= $mmrpg_database_bosses_links_counter == 1 ? 'the' : 'all' ?> <?= isset($this_current_filter) ? $mmrpg_database_bosses_links_counter.' <span class="type_span robot_type robot_type_'.$this_current_filter.'">'.$this_current_filter_name.' Type</span> ' : $mmrpg_database_bosses_links_counter.' ' ?><?= $mmrpg_database_bosses_links_counter == 1 ? 'fortress boss that appears ' : 'fortress bosses that appear ' ?> or will appear in the prototype, including <?= $mmrpg_database_bosses_links_counter == 1 ? 'its' : 'each boss\'s' ?> base stats, weaknesses, resistances, affinities, immunities, signature abilities, battle quotes, sprite sheets, and more.
                Click <?= $mmrpg_database_bosses_links_counter == 1 ? 'the mugshot below to scroll to the' : 'any of the mugshots below to scroll to a' ?> boss's summarized database entry and click the more link to see its full page with sprites and extended info. <?= isset($this_current_filter) ? 'If you wish to reset the boss type filter, <a href="database/bosses/">please click here</a>.' : '' ?>
            </p>
            <div class="text iconwrap"><?= preg_replace('/data-token="([-_a-z0-9]+)"/', 'data-anchor="$1"', $mmrpg_database_bosses_links) ?></div>
        </div>
        <div style="clear: both;">&nbsp;</div>

    <? else: ?>

        <?
        // Collect the prev and next robot tokens
        $prev_link = false;
        $next_link = false;
        if (!empty($this_current_key)){
            $key_index = array_keys($mmrpg_database_bosses);
            $min_key = 0;
            $max_key = count($key_index) - 1;
            $current_key_position = array_search($this_current_key, $key_index);
            $prev_key_position = $current_key_position - 1;
            $next_key_position = $current_key_position + 1;
            $find = array('href="', '<a ', '</a>', '<div ', '</div>');
            $replace = array('data-href="', '<span ', '</span>', '<span ', '</span>');
            // If prev key was in range, generate
            if ($prev_key_position >= $min_key){
                $prev_key = $key_index[$prev_key_position];
                $prev_info = $mmrpg_database_bosses[$prev_key];
                $prev_link = 'database/bosses/'.$prev_info['robot_token'].'/';
                $prev_link_image = $mmrpg_database_bosses_links_index[$prev_key];
                $prev_link_image = str_replace($find, $replace, $prev_link_image);
            }
            // If next key was in range, generate
            if ($next_key_position <= $max_key){
                $next_key = $key_index[$next_key_position];
                $next_info = $mmrpg_database_bosses[$next_key];
                $next_link = 'database/bosses/'.$next_info['robot_token'].'/';
                $next_link_image = $mmrpg_database_bosses_links_index[$next_key];
                $next_link_image = str_replace($find, $replace, $next_link_image);
            }

        }

        ?>

        <div class="link_nav">
            <? if (!empty($prev_link)): ?>
                <a class="link link_prev" href="<?= $prev_link ?>"><?= $prev_link_image ?></a>
            <? endif; ?>
            <? if (!empty($next_link)): ?>
                <a class="link link_next" href="<?= $next_link ?>"><?= $next_link_image ?></a>
            <? endif; ?>
            <a class="link link_return" href="database/bosses/">Return to Boss Index</a>
        </div>

    <? endif; ?>
</div>

<?php

// Only show the header if a specific boss has not been selected
if (empty($this_current_token)){
    ?>
    <h2 class="subheader field_type_<?= isset($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
        Boss Listing
        <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Type )</span>' : '' ?>
    </h2>
    <?php
}

// If we're in the index view, loop through and display all bosses
if (empty($this_current_token)){
    // Loop through the boss database and display the appropriate data
    $key_counter = 0;
    foreach($mmrpg_database_bosses AS $boss_key => $boss_info){
        // If a type filter has been applied to the boss page
        if (isset($this_current_filter) && $this_current_filter == 'none' && $boss_info['robot_core'] != ''){ $key_counter++; continue; }
        elseif (isset($this_current_filter) && $this_current_filter != 'none' && $boss_info['robot_core'] != $this_current_filter && $boss_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }
        // Collect information about this boss
        $this_robot_image = !empty($boss_info['robot_image']) ? $boss_info['robot_image'] : $boss_info['robot_token'];
        if ($this_robot_image == 'robot'){ $this_seo_robots = 'noindex'; }
        // Collect the markup for this robot and print it to the browser
        $temp_boss_markup = rpg_robot::print_database_markup($boss_info, array('layout_style' => 'website_compact', 'show_key' => $key_counter));
        echo $temp_boss_markup;
        $key_counter++;
    }
}

?>