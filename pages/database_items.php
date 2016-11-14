<?php
/*
 * ITEMS DATABASE AJAX
 */

// If an explicit return request for the index was provided
if (!empty($_REQUEST['return']) && $_REQUEST['return'] == 'index'){
    // Exit with only the database link markup
    exit($mmrpg_database_items_links);
}



/*
 * ITEM DATABASE PAGE
 */

// Define the SEO variables for this page
$this_seo_title = 'Items '.(!empty($this_current_filter) ? '('.$this_current_filter_name.' Type) ' : '').'| Database | '.$this_seo_title;
$this_seo_description = 'The item database contains detailed information about the Mega Man RPG Prototype\'s equippable items including their base stats, types, descriptions, and sprite sheets. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Item Database'.(!empty($this_current_filter) ? ' ('.$this_current_filter_name.' Type) ' : '');
$this_graph_data['description'] = 'The item database contains detailed information about the Mega Man RPG Prototype\'s collectable items including their stats, descriptions, and sprite sheets.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Item Database';
//$this_markup_counter = '<span class="count count_header">( '.(!empty($mmrpg_database_items_links_counter) ? ($mmrpg_database_items_links_counter == 1 ? '1 Item' : $mmrpg_database_items_links_counter.' Items') : '0 Items').' )</span>';

// If a specific item has NOT been defined, show the quick-switcher
reset($mmrpg_database_items);
if (!empty($this_current_token)){ $first_item_key = $this_current_token; }
else { $first_item_key = key($mmrpg_database_items); }

// Only show the next part of a specific item was requested
if (!empty($this_current_token)){

    // Loop through the item database and display the appropriate data
    $key_counter = 0;
    $this_current_key = false;
    foreach($mmrpg_database_items AS $item_key => $item_info){

        // If a specific item has been requested and it's not this one
        if (!empty($this_current_token) && $this_current_token != $item_info['item_token']){ $key_counter++; continue; }
        //elseif ($key_counter > 0){ continue; }

        // If this is THE specific item requested (and one was specified)
        if (!empty($this_current_token) && $this_current_token == $item_info['item_token']){
            $this_current_key = $item_key;

            $this_item_image = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_info['item_token'];
            if ($this_item_image == 'item'){ $this_seo_robots = 'noindex'; }
            $this_temp_description = 'The '.$item_info['item_name'].' is ';
            if (!empty($item_info['item_type'])){
                if (empty($item_info['item_type2'])){ $this_temp_description .= (preg_match('/^(a|e|i|o|u)/', $item_info['item_type']) ? 'an ' : 'a ').ucfirst($item_info['item_type']).' type'; }
                else { $this_temp_description .= (preg_match('/^(a|e|i|o|u)/', $item_info['item_type']) ? 'an ' : 'a ').ucfirst($item_info['item_type']).' and '.ucfirst($item_info['item_type2']).' type'; }
            } else {
                $this_temp_description .= 'a neutral type';
            }
            $this_temp_description .= ' item in the Mega Man RPG Prototype.';
            // Define the SEO variables for this page
            $this_seo_title_backup = $this_seo_title;
            $this_seo_title = $item_info['item_name'].' | '.$this_seo_title;
            $this_seo_description = $this_temp_description.'  '.$item_info['item_description'].'  '.$this_seo_description;
            // Define the Open Graph variables for this page
            $this_graph_data['title'] .= ' | '.$item_info['item_name'];
            $this_graph_data['description'] = $this_temp_description.'  '.$item_info['item_description'].'  '.$this_graph_data['description'];
            $this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/items/'.$item_info['item_token'].'/icon_right_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;

        }

        // Collect the markup for this item and print it to the browser
        $temp_item_markup = rpg_item::print_database_markup($item_info, array('show_key' => $key_counter));
        echo $temp_item_markup;
        $key_counter++;
        break;

    }

}

// Only show the header if a specific item has not been selected
if (empty($this_current_token)){
    ?>
    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
        <span class="subheader_typewrapper">
            <a class="inline_link" href="database/items/">Item Database</a>
            <span class="count">( <?= $mmrpg_database_items_count_complete ?> / <?= $mmrpg_database_items_count == 1 ? '1 Item' : $mmrpg_database_items_count.' Items' ?> )</span>
            <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Type )</span>' : '' ?>
        </span>
    </h2>
    <?php
}

?>

<div class="subbody subbody_databaselinks <?= empty($this_current_token) ? 'subbody_databaselinks_noajax' : '' ?>" data-class="items" data-class-single="item" data-basetitle="<?= isset($this_seo_title_backup) ? $this_seo_title_backup : $this_seo_title ?>" data-current="<?= !empty($this_current_token) ? $this_current_token : '' ?>">
    <? if(empty($this_current_token)): ?>

        <div class="<?= !empty($this_current_token) ? 'toggle_body' : '' ?>" style="<?= !empty($this_current_token) ? 'display: none;' : '' ?>">
            <p class="text" style="clear: both;">
                The item database contains detailed information on <?= $mmrpg_database_items_links_counter == 1 ? 'the' : 'all' ?> <?= isset($this_current_filter) ? $mmrpg_database_items_links_counter.' <span class="type_span robot_type robot_type_'.($this_current_filter == 'multi' ? 'shield' : ($this_current_filter == 'bonus' ? 'laser' : $this_current_filter)).'">'.$this_current_filter_name.' Type</span> ' : $mmrpg_database_items_links_counter.' ' ?><?= $mmrpg_database_items_links_counter == 1 ? 'collectable item that appears ' : 'collectable items that appear ' ?> or will appear in the prototype, including <?= $mmrpg_database_items_links_counter == 1 ? 'its' : 'each item\'s' ?> stats, description, and sprite sheets.
                Click <?= $mmrpg_database_items_links_counter == 1 ? 'the icon below to scroll to the' : 'any of the icons below to scroll to an' ?> item's summarized database entry and click the more link to see its full page with sprites and extended info. <?= isset($this_current_filter) ? 'If you wish to reset the item type filter, <a href="database/items/">please click here</a>.' : '' ?>
            </p>
            <div class="text iconwrap"><?= preg_replace('/data-token="([-_a-z0-9]+)"/', 'data-anchor="$1"', $mmrpg_database_items_links) ?></div>
        </div>
        <div style="clear: both;">&nbsp;</div>

    <? else: ?>

        <?
        // Collect the prev and next item tokens
        $prev_link = false;
        $next_link = false;
        if (!empty($this_current_key)){
            $key_index = array_keys($mmrpg_database_items);
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
                $prev_info = $mmrpg_database_items[$prev_key];
                $prev_link = 'database/items/'.$prev_info['item_token'].'/';
                $prev_link_image = $mmrpg_database_items_links_index[$prev_key];
                $prev_link_image = str_replace($find, $replace, $prev_link_image);
            }
            // If next key was in range, generate
            if ($next_key_position <= $max_key){
                $next_key = $key_index[$next_key_position];
                $next_info = $mmrpg_database_items[$next_key];
                $next_link = 'database/items/'.$next_info['item_token'].'/';
                $next_link_image = $mmrpg_database_items_links_index[$next_key];
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
            <a class="link link_return" href="database/items/">Return to Item Index</a>
        </div>

    <? endif; ?>
</div>

<?php

// Only show the header if a specific item has not been selected
if (empty($this_current_token)){
    ?>
    <h2 class="subheader field_type_<?= isset($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
        Item Listing
        <?= isset($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Type )</span>' : '' ?>
    </h2>
    <?php
}

// If we're in the index view, loop through and display all items
if (empty($this_current_token)){
    // Loop through the item database and display the appropriate data
    $key_counter = 0;
    foreach($mmrpg_database_items AS $item_key => $item_info){
        // If a type filter has been applied to the item page
        $temp_item_types = array();
        if (!empty($item_info['item_type'])){ $temp_item_types[] = $item_info['item_type']; }
        if (!empty($item_info['item_type2'])){ $temp_item_types[] = $item_info['item_type2']; }
        if (preg_match('/^(red|blue|green|purple)-score-ball$/i', $item_info['item_token'])){ $temp_item_types[] = 'bonus'; }
        elseif (preg_match('/^super-(pellet|capsule)$/i', $item_info['item_token'])){ $temp_item_types[] = 'multi'; }
        if (empty($temp_item_types)){ $temp_item_types[] = 'none'; }
        if (isset($this_current_filter) && !in_array($this_current_filter, $temp_item_types)){ $key_counter++; continue; }
        // Collect information about this item
        $this_item_image = !empty($item_info['item_image']) ? $item_info['item_image'] : $item_info['item_token'];
        if ($this_item_image == 'item'){ $this_seo_items = 'noindex'; }
        // Collect the markup for this item and print it to the browser
        $temp_item_markup = rpg_item::print_database_markup($item_info, array('layout_style' => 'website_compact', 'show_key' => $key_counter));
        echo $temp_item_markup;
        $key_counter++;
    }
}


?>