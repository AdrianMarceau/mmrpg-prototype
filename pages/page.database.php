<?
/*
 * INDEX PAGE : DATABASE
 */

// Require the database top include file
if ($this_current_sub == 'mechas' || $this_current_sub == 'fields'){ define('DATA_DATABASE_SHOW_MECHAS', true); }
if ($this_current_sub == 'bosses' || $this_current_sub == 'fields'){ define('DATA_DATABASE_SHOW_BOSSES', true); }
require_once('data/database.php');

//die('<pre>'.print_r($_REQUEST, true).'</pre>');

// Start generating the page markup
ob_start();

// Define the allowed sub-pages
  $allowed_sub_pages = array('players', 'robots', 'mechas', 'bosses', 'abilities', 'fields', 'types', 'items');

  // If we're viewing the INDEX page
  if (empty($this_current_sub) || !in_array($this_current_sub, $allowed_sub_pages)){

    // Define the SEO variables for this page
    $this_seo_title = 'Database | '.$this_seo_title;
    $this_seo_description = 'The database contains detailed information on the Mega Man RPG Prototype\'s robots, players, and abilities including each robot\'s base stats, weaknesses, resistances, affinities, immunities, unlockable abilities, robot and player battle quotes, hundreds of sprite sheets, and so much more. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

    // Define the Open Graph variables for this page
    $this_graph_data['title'] = 'Prototype Database';
    $this_graph_data['description'] = 'The database contains detailed information on the Mega Man RPG Prototype\'s robots, players, and abilities including each robot\'s base stats, weaknesses, resistances, affinities, immunities, unlockable abilities, robot and player battle quotes, hundreds of sprite sheets, and so much more.';
    //$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
    //$this_graph_data['type'] = 'website';

    // Define the MARKUP variables for this page
    $this_markup_header = 'Mega Man RPG Prototype Database';

    ?>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <span class="subheader_typewrapper">
        <a class="inline_link" href="database/players/">Player Database </a>
        <span class="count">( <?= $mmrpg_database_players_count_complete ?> / <?= !empty($mmrpg_database_players_count) ? ($mmrpg_database_players_count == 1 ? '1 Player' : $mmrpg_database_players_count.' Players') : '0 Players' ?> )</span>
        <a class="float_link" href="database/players/">View the Player Database &raquo;</a>
      </span>
    </h2>
    <div class="subbody subbody_databaselinks subbody_databaselinks_noajax" data-class="players" data-class-single="player">
      <?/*<div class="float float_right"><a class="sprite sprite_80x80 sprite_80x80_02"  href="database/players/" style="background-image: url(images/players/dr-light/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Dr. Light</a></div>*/?>
      <p class="text">The player database contains detailed information on the playable characters in the prototype, including unlockable abilities, quotes, and sprite sheets.</p>
      <div class="text iconwrap"><?= $mmrpg_database_players_links ?></div>
    </div>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <span class="subheader_typewrapper">
        <a class="inline_link" href="database/robots/">Robot Database</a>
        <span class="count">( <?= $mmrpg_database_robots_count_complete ?> / <?= !empty($mmrpg_database_robots_count) ? ($mmrpg_database_robots_count == 1 ? '1 Robot' : $mmrpg_database_robots_count.' Robots') : '0 Robots' ?> )</span>
        <a class="float_link" href="database/robots/">View the Robot Database &raquo;</a>
      </span>
    </h2>
    <div class="subbody subbody_databaselinks subbody_databaselinks_noajax" data-class="robots" data-class-single="robot">
      <?/*<div class="float float_right"><a class="sprite sprite_80x80 sprite_80x80_02" href="database/robots/" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Mega Man</a></div>*/?>
      <p class="text">The robot database contains detailed information on the unlockable robot masters in the prototype, including abilities, stats, quotes, and sprite sheets.</p>
      <div class="text iconwrap"><?= $mmrpg_database_robots_links ?></div>
    </div>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <span class="subheader_typewrapper">
        <a class="inline_link" href="database/mechas/">Mecha Database</a>
        <span class="count">( <?= $mmrpg_database_mechas_count_complete ?> / <?= !empty($mmrpg_database_mechas_count) ? ($mmrpg_database_mechas_count == 1 ? '1 Mecha' : $mmrpg_database_mechas_count.' Mechas') : '0 Mechas' ?> )</span>
        <a class="float_link" href="database/mechas/">View the Mecha Database &raquo;</a>
      </span>
    </h2>
    <div class="subbody subbody_databaselinks subbody_databaselinks_noajax" data-class="mechas" data-class-single="mecha">
      <?/*<div class="float float_right"><a class="sprite sprite_80x80 sprite_80x80_07" href="database/mechas/" style="background-image: url(images/robots/met/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Met</a></div>*/?>
      <p class="text">The mecha database contains detailed information on the various support mechas in the prototype, including abilities, stats, quotes, and sprite sheets.</p>
      <div class="text iconwrap"><?= $mmrpg_database_mechas_links ?></div>
    </div>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <span class="subheader_typewrapper">
        <a class="inline_link" href="database/bosses/">Boss Database</a>
        <span class="count">( <?= $mmrpg_database_bosses_count_complete ?> / <?= !empty($mmrpg_database_bosses_count) ? ($mmrpg_database_bosses_count == 1 ? '1 Boss' : $mmrpg_database_bosses_count.' Bosses') : '0 Bosses' ?> )</span>
        <a class="float_link" href="database/bosses/">View the Boss Database &raquo;</a>
      </span>
    </h2>
    <div class="subbody subbody_databaselinks subbody_databaselinks_noajax" data-class="bosses" data-class-single="boss">
      <?/*<div class="float float_right"><a class="sprite sprite_80x80 sprite_80x80_08" href="database/bosses/" style="background-image: url(images/robots/trill/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Trill</a></div>*/?>
      <p class="text">The boss database contains detailed information on the various fortress bosses in the prototype, including abilities, stats, quotes, and sprite sheets.</p>
      <div class="text iconwrap"><?= $mmrpg_database_bosses_links ?></div>
    </div>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <span class="subheader_typewrapper">
        <a class="inline_link" href="database/abilities/">Ability Database</a>
        <span class="count">( <?= $mmrpg_database_abilities_count_complete ?> / <?= !empty($mmrpg_database_abilities_count) ? ($mmrpg_database_abilities_count == 1 ? '1 Ability' : $mmrpg_database_abilities_count.' Abilities') : '0 Abilities' ?> )</span>
        <a class="float_link" href="database/abilities/">View the Ability Database &raquo;</a>
      </span>
    </h2>
    <div class="subbody subbody_databaselinks subbody_databaselinks_noajax" data-class="abilities" data-class-single="ability">
      <?/*<div class="float float_right"><a class="sprite sprite_80x80 sprite_80x80_00" href="database/abilities/" style="margin: -10px 0 5px; background-image: url(images/abilities/mega-slide/icon_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Mega Slide</a></div>*/?>
      <p class="text">The ability database contains detailed information on the unlockable abilities in the prototype, including compatible robots, stats, and sprite sheets.</p>
      <div class="text iconwrap"><?= $mmrpg_database_abilities_links ?></div>
    </div>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <span class="subheader_typewrapper">
        <a class="inline_link" href="database/items/">Item Database</a>
        <span class="count">( <?= $mmrpg_database_items_count_complete ?> / <?= !empty($mmrpg_database_items_count) ? ($mmrpg_database_items_count == 1 ? '1 Item' : $mmrpg_database_items_count.' Items') : '0 Items' ?> )</span>
        <a class="float_link" href="database/items/">View the Item Database &raquo;</a>
      </span>
    </h2>
    <div class="subbody subbody_databaselinks subbody_databaselinks_noajax" data-class="items" data-class-single="item">
      <?/*<div class="float float_right"><a class="sprite sprite_80x80 sprite_80x80_00" href="database/items/" style="margin: -10px 0 5px; background-image: url(images/abilities/item-extra-life/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Extra Life</a></div>*/?>
      <p class="text">The item database contains detailed information on the collectable items in the prototype, including stats, descriptions, and sprite sheets.</p>
      <div class="text iconwrap"><?= $mmrpg_database_items_links ?></div>
    </div>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <span class="subheader_typewrapper">
        <a class="inline_link" href="database/fields/">Field Database </a>
        <span class="count">( <?= $mmrpg_database_fields_count_complete ?> / <?= !empty($mmrpg_database_fields_count) ? ($mmrpg_database_fields_count == 1 ? '1 Field' : $mmrpg_database_fields_count.' Fields') : '0 Fields' ?> )</span>
        <a class="float_link" href="database/fields/">View the Field Database &raquo;</a>
      </span>
    </h2>
    <div class="subbody subbody_databaselinks subbody_databaselinks_noajax" data-class="fields" data-class-single="field">
      <?/*<div class="float float_right" style="overflow: hidden;"><a class="sprite sprite_80x80 sprite_80x80_00" href="database/fields/" style="margin: 0; background-image: url(images/fields/intro-field/battle-field_avatar.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">Intro Field</a></div>*/?>
      <p class="text">The field database contains detailed information on the battle fields of the prototype, including robot masters, mechas, stats, and sprite sheets.</p>
      <div class="text iconwrap"><?= $mmrpg_database_fields_links ?></div>
    </div>

    <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
      <span class="subheader_typewrapper">
        <a class="inline_link" href="database/types/">Type Database</a>
        <span class="count">( <?= !empty($mmrpg_database_types_count) ? $mmrpg_database_types_count.' Types' : '0 Types' ?> )</span>
        <a class="float_link" href="database/types/">View the Type Database &raquo;</a>
      </span>
    </h2>
    <div class="subbody">
      <p class="text">The type database contains a detailed breakdown of the elemental type distribution in the prototype, including ability counts, robot counts, and more.</p>
      <ul class="iconwrap" style="padding: 4px 0 6px;">
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

    <?

  }
  // Otherwise, if we're viewing the PLAYER DATABASE
  elseif ($this_current_sub == 'players'){

    // Require the database players page
    require_once('page.database_players.php');

  }
  // Otherwise, if we're viewing the ROBOT DATABASE
  elseif ($this_current_sub == 'robots'){

    // Require the database robots page
    require_once('page.database_robots.php');

  }
  // Otherwise, if we're viewing the MECHA DATABASE
  elseif ($this_current_sub == 'mechas'){

    // Require the database mechas page
    require_once('page.database_mechas.php');

  }
  // Otherwise, if we're viewing the BOSS DATABASE
  elseif ($this_current_sub == 'bosses'){

    // Require the database bosses page
    require_once('page.database_bosses.php');

  }
  // Otherwise, if we're viewing the ABILITY DATABASE
  elseif ($this_current_sub == 'abilities'){

    // Require the database abilities page
    require_once('page.database_abilities.php');

  }
  // Otherwise, if we're viewing the FIELD DATABASE
  elseif ($this_current_sub == 'fields'){

    // Require the database fields page
    require_once('page.database_fields.php');

  }
  // Otherwise, if we're viewing the ITEM DATABASE
  elseif ($this_current_sub == 'items'){

    // Require the database items page
    require_once('page.database_items.php');

  }
  // Otherwise, if we're viewing the TYPE DATABASE
  elseif ($this_current_sub == 'types'){

    // Require the database abilities page
    require_once('page.database_types.php');

  }

?>
  <div class="subbody" style="margin-top: 8px;">
    <p class="text" style="font-size: 11px; line-height: 16px; color: #747474;">(!) Please note that the names, stats, types, and descriptions of any playable characters, robots, or abilities that appear in this database are <em>not finalized</em> and are subject to change without notice as development progresses on the game itself.  That being said, the data on this page is pulled directly from the prototype's internal variables and will therefore always be in sync with the prototype itself. Database pages that do not have sprites represent incomplete but planned, future content and do not currently appear in-game.</p>
  </div>
<?


// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('/\s+/', ' ', ob_get_clean()));
//$this_markup_body = trim(ob_get_clean());
?>