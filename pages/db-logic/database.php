<?

// Define ROBOT preview fields if we're not in a specific sub-index
if ($this_current_sub != 'robots'){
    // If we're not on the full robot index, only show preview robots
    $mmrpg_database_robots_filter = "AND (robots.robot_token IN ('mega-man', 'bass', 'proto-man') OR robots.robot_game IN ('MM01')) ";
}

// Define MECHA preview fields if we're not in a specific sub-index
if ($this_current_sub != 'mechas'){
    // If we're not on the full mecha index, only show preview mechas
    $mmrpg_database_mechas_filter = "AND robots.robot_game IN ('MM00', 'MM01') ";
}

// Define BOSS preview fields if we're not in a specific sub-index
if ($this_current_sub != 'bosses'){
    // If we're not on the full boss index, only show preview bosses
    $mmrpg_database_bosses_filter = "AND (robots.robot_token IN ('doc-robot', 'trill') OR robots.robot_game IN ('MM20')) ";
}

// Define ABILITY preview fields if we're not in a specific sub-index
if ($this_current_sub != 'abilities'){
    // If we're not on the full ability index, only show preview abilities
    $mmrpg_database_abilities_filter = "AND (abilities.ability_token IN ('buster-shot') OR (
        (abilities.ability_master IN ('mega-man', 'bass', 'proto-man') AND abilities.ability_energy = 2) OR
        (abilities.ability_game = 'MM01' AND abilities.ability_energy = 4)
        )) ";
}

// Define ITEM preview fields if we're not in a specific sub-index
if ($this_current_sub != 'items'){
    // If we're not on the full item index, only show preview items
    $mmrpg_database_items_filter = "AND (items.item_token IN ('small-screw', 'large-screw') OR (
        (items.item_game = 'MM00' AND items.item_group LIKE 'MM00/%')
        )) ";
}

// Define FIELD preview fields if we're not in a specific sub-index
if ($this_current_sub != 'fields'){
    // If we're not on the full field index, only show preview fields
    $mmrpg_database_fields_filter = "AND (fields.field_token IN ('gentle-countryside', 'maniacal-hideaway', 'wintry-forefront') OR fields.field_game IN ('MM00', 'MM01')) ";
}


// Require the database top include file
if ($this_current_sub != 'types'){
    if ($this_current_sub == 'mechas' || $this_current_sub == 'fields'){ define('DATA_DATABASE_SHOW_MECHAS', true); }
    if ($this_current_sub == 'bosses' || $this_current_sub == 'fields'){ define('DATA_DATABASE_SHOW_BOSSES', true); }
    require_once('database/include.php');
} elseif ($this_current_sub == 'types'){
    require_once('database/types.php');
}

// Start generating the page markup
ob_start();

    // Define the allowed sub-pages
    $allowed_sub_pages = array('players', 'robots', 'mechas', 'bosses', 'abilities', 'fields', 'types', 'items');

    // If we're viewing the INDEX page
    if (empty($this_current_sub) || !in_array($this_current_sub, $allowed_sub_pages)){
        require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database_index.php');
    }
    // Otherwise, if we're viewing the PLAYER DATABASE
    elseif ($this_current_sub == 'players'){
        require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database_players.php');
    }
    // Otherwise, if we're viewing the ROBOT DATABASE
    elseif ($this_current_sub == 'robots'){
        require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database_robots.php');
    }
    // Otherwise, if we're viewing the MECHA DATABASE
    elseif ($this_current_sub == 'mechas'){
        require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database_mechas.php');
    }
    // Otherwise, if we're viewing the BOSS DATABASE
    elseif ($this_current_sub == 'bosses'){
        require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database_bosses.php');
    }
    // Otherwise, if we're viewing the ABILITY DATABASE
    elseif ($this_current_sub == 'abilities'){
        require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database_abilities.php');
    }
    // Otherwise, if we're viewing the FIELD DATABASE
    elseif ($this_current_sub == 'fields'){
        require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database_fields.php');
    }
    // Otherwise, if we're viewing the ITEM DATABASE
    elseif ($this_current_sub == 'items'){
        require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database_items.php');
    }
    // Otherwise, if we're viewing the TYPE DATABASE
    elseif ($this_current_sub == 'types'){
        require(MMRPG_CONFIG_ROOTDIR.'pages/db-logic/database_types.php');
    }

// Ensure we append a notice at the bottom of all database pages
// regarding the dynamic nature of our content development strategy
ob_start();
?>
    <div class="subbody" style="margin-top: 8px;">
        <p class="text" style="font-size: 11px; line-height: 16px; color: #747474;">(!) Please note that the names, stats, types, and descriptions of any playable characters, robots, or abilities that appear in this database are <em>not finalized</em> and are subject to change without notice as development progresses on the game itself.  That being said, the data on this page is pulled directly from the prototype's internal variables and will therefore always be in sync with the prototype itself. Database pages that do not have sprites represent incomplete but planned, future content and do not currently appear in-game.</p>
    </div>
<?
$page_content_parsed .= PHP_EOL.ob_get_clean();

?>