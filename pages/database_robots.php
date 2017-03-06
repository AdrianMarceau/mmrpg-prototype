<?php
/*
 * ROBOTS DATABASE AJAX
 */

// If an explicit return request for the index was provided
if (!empty($_REQUEST['return']) && $_REQUEST['return'] == 'index'){
    // Exit with only the database link markup
    exit($mmrpg_database_robots_links);
}



/*
 * ROBOT DATABASE PAGE
 */

// Define the SEO variables for this page
$this_seo_title = 'Robots '.(!empty($this_current_filter) ? '('.$this_current_filter_name.' Core) ' : '').'| Database | '.$this_seo_title;
$this_seo_description = 'The robot database contains detailed information about the Mega Man RPG Prototype\'s unlockable robots including their equippable abilities, battle quotes, base stats, weaknesses, resistances, affinities, immunities, and sprite sheets. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Robot Database'.(!empty($this_current_filter) ? ' ('.$this_current_filter_name.' Core) ' : '');
$this_graph_data['description'] = 'The robot database contains detailed information about the Mega Man RPG Prototype\'s unlockable robots including their equippable abilities, battle quotes, base stats, weaknesses, resistances, affinities, immunities, and sprite sheets.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Robot Database';
//$this_markup_counter = '<span class="count count_header">( '.(!empty($mmrpg_database_robots_links_counter) ? ($mmrpg_database_robots_links_counter == 1 ? '1 Robot' : $mmrpg_database_robots_links_counter.' Robots') : '0 Robots').' )</span>';

// If a specific robot has NOT been defined, show the quick-switcher
reset($mmrpg_database_robots);
if (!empty($this_current_token)){ $first_robot_key = $this_current_token; }
else { $first_robot_key = key($mmrpg_database_robots); }

/*
// GENERATE ROBOT QUOTES CSV
$temp_quotes = array('start', 'taunt', 'victory', 'defeat');
$temp_csv = array();
$temp_csv[] = array('Robot Name', 'Robot Token', 'Start Quote', 'Taunt Quote', 'Victory Quote', 'Defeat Quote');
foreach ($mmrpg_database_robots AS $key => $info){
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
// GENERATE ROBOT DESCRIPITIONS CSV
$temp_csv = array();
$temp_csv[] = array('Robot Name', 'Robot Token', 'Robot Class', 'Robot Description');
foreach ($mmrpg_database_robots AS $key => $info){
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

// Only show the next part of a specific robot was requested
if (!empty($this_current_token)){

    // Loop through the robot database and display the appropriate data
    $key_counter = 0;
    $this_current_key = false;
    foreach($mmrpg_database_robots AS $robot_key => $robot_info){

        // If a specific robot has been requested and it's not this one
        if (!empty($this_current_token) && $this_current_token != $robot_info['robot_token']){ $key_counter++; continue; }
        //elseif ($key_counter > 0){ continue; }

        // If this is THE specific robot requested (and one was specified)
        if (!empty($this_current_token) && $this_current_token == $robot_info['robot_token']){
            $this_current_key = $robot_key;

            $this_robot_image = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
            $this_robot_image_size = (!empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40) * 2;
            $this_robot_image_size_text = $this_robot_image_size.'x'.$this_robot_image_size;
            if ($this_robot_image == 'robot'){ $this_seo_robots = 'noindex'; }

            // Check if this is a mecha and prepare extra text
            $robot_info['robot_name_append'] = '';

            // Define the SEO variables for this page
            $this_seo_title_backup = $this_seo_title;
            $this_seo_title = $robot_info['robot_name'].$robot_info['robot_name_append'].' | '.$this_seo_title;
            $this_seo_description = $robot_info['robot_number'].' '.$robot_info['robot_name'].', the '.$robot_info['robot_description'].', ';
            $this_seo_description .= 'is a '.rpg_robot::get_best_stat_desc($robot_info).' ';
            $this_seo_description .= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core']).' ' : 'Neutral ';
            if (!empty($robot_info['robot_core2'])){ $this_seo_description .= '/ '.ucfirst($robot_info['robot_core2']).' '; }
            $this_seo_description .= 'Core robot master from the Mega Man RPG Prototype. ';

            // Define the Open Graph variables for this page
            $this_graph_data['title'] .= ' | '.$robot_info['robot_name'];
            $this_graph_data['description'] = $this_seo_description;
            $this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/robots/'.$robot_info['robot_token'].'/mug_right_'.$this_robot_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;

        }

        // Collect the markup for this robot and print it to the browser
        $temp_robot_markup = rpg_robot::print_database_markup($robot_info, array('show_key' => $key_counter));
        echo $temp_robot_markup;
        $key_counter++;
        break;

    }

}


// Only show the header if a specific robot has not been selected
if (empty($this_current_token)){
    ?>
    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="database/robots/">Robot Database</a>
            <span class="count">( <?= $mmrpg_database_robots_count_complete ?> / <?= $mmrpg_database_robots_count == 1 ? '1 Robot' : $mmrpg_database_robots_count.' Robots' ?> )</span>
            <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Core )</span>' : '' ?>
        </span>
    </h2>
    <?php
}

?>

<div class="subbody subbody_databaselinks <?= empty($this_current_token) ? 'subbody_databaselinks_noajax' : '' ?>" data-class="robots" data-class-single="robot" data-basetitle="<?= isset($this_seo_title_backup) ? $this_seo_title_backup : $this_seo_title ?>" data-current="<?= !empty($this_current_token) ? $this_current_token : '' ?>">
    <? if(empty($this_current_token)): ?>

        <div class="<?= !empty($this_current_token) ? 'toggle_body' : '' ?>" style="<?= !empty($this_current_token) ? 'display: none;' : '' ?>">
            <p class="text" style="clear: both;">
                The robot database contains detailed information on <?= $mmrpg_database_robots_links_counter == 1 ? 'the' : 'all' ?> <?= isset($this_current_filter) ? $mmrpg_database_robots_links_counter.' <span class="type_span robot_type robot_type_'.$this_current_filter.'">'.$this_current_filter_name.' Core</span> ' : $mmrpg_database_robots_links_counter.' ' ?><?= $mmrpg_database_robots_links_counter == 1 ? 'robot master that appears ' : 'robot masters that appear ' ?> or will appear in the prototype, including <?= $mmrpg_database_robots_links_counter == 1 ? 'its' : 'each robot\'s' ?> base stats, weaknesses, resistances, affinities, immunities, unlockable abilities, battle quotes, sprite sheets, and more.
                Click <?= $mmrpg_database_robots_links_counter == 1 ? 'the mugshot below to scroll to the' : 'any of the mugshots below to scroll to a' ?> robot's summarized database entry and click the more link to see its full page with sprites and extended info. <?= isset($this_current_filter) ? 'If you wish to reset the core type filter, <a href="database/robots/">please click here</a>.' : '' ?>
            </p>
            <div class="text iconwrap"><?= preg_replace('/data-token="([-_a-z0-9]+)"/', 'data-anchor="$1"', $mmrpg_database_robots_links) ?></div>
        </div>
        <div style="clear: both;">&nbsp;</div>

    <? else: ?>

        <?
        // Collect the prev and next robot tokens
        $prev_link = false;
        $next_link = false;
        if (!empty($this_current_key)){
            $key_index = array_keys($mmrpg_database_robots);
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
                $prev_info = $mmrpg_database_robots[$prev_key];
                $prev_link = 'database/robots/'.$prev_info['robot_token'].'/';
                $prev_link_image = $mmrpg_database_robots_links_index[$prev_key];
                $prev_link_image = str_replace($find, $replace, $prev_link_image);
            }
            // If next key was in range, generate
            if ($next_key_position <= $max_key){
                $next_key = $key_index[$next_key_position];
                $next_info = $mmrpg_database_robots[$next_key];
                $next_link = 'database/robots/'.$next_info['robot_token'].'/';
                $next_link_image = $mmrpg_database_robots_links_index[$next_key];
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
            <a class="link link_return" href="database/robots/">Return to Robot Index</a>
        </div>

    <? endif; ?>
</div>

<?php

// Only show the header if a specific robot has not been selected
if (empty($this_current_token)){
    ?>
    <h2 class="subheader field_type_<?= isset($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
        Robot Listing
        <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Core )</span>' : '' ?>
    </h2>
    <?php
}

// If we're in the index view, loop through and display all robots
if (empty($this_current_token)){
    // Loop through the robot database and display the appropriate data
    $key_counter = 0;
    foreach($mmrpg_database_robots AS $robot_key => $robot_info){
        // If a type filter has been applied to the robot page
    if (isset($this_current_filter) && $this_current_filter == 'none' && $robot_info['robot_core'] != ''){ $key_counter++; continue; }
    elseif (isset($this_current_filter) && $this_current_filter != 'none' && $robot_info['robot_core'] != $this_current_filter && $robot_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }
        // Collect information about this robot
        $this_robot_image = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
        if ($this_robot_image == 'robot'){ $this_seo_robots = 'noindex'; }
        // Collect the markup for this robot and print it to the browser
        $temp_robot_markup = rpg_robot::print_database_markup($robot_info, array('layout_style' => 'website_compact', 'show_key' => $key_counter));
        echo $temp_robot_markup;
        $key_counter++;
    }
}

?>