<?
/*
 * TYPES DATABASE PAGE
 */

// Define the SEO variables for this page
$this_seo_title = 'Types | Database | '.$this_seo_title;
$this_seo_description = 'The robots, abilities, fields, and many other aspects of the Mega Man RPG are designed around '.($mmrpg_database_types_count_actual + 1).' predefined "types" that represent various elemental affinities and/or methods of attack in the game. Knowing these types can mean the difference between victory and defeat in certain situations, so using this database and the in-game scan option are encouraged. The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'Type Database';
$this_graph_data['description'] = 'The robots, abilities, fields, and many other aspects of the Mega Man RPG are designed around '.($mmrpg_database_types_count_actual + 1).' predefined "types" that represent various elemental affinities and/or methods of attack in the game. Knowing these types can mean the difference between victory and defeat in certain situations, so using this database and the in-game scan option are encouraged.';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';

// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Type Database <span class="count">( '.(!empty($mmrpg_database_types_count) ? ($mmrpg_database_types_count == 1 ? '1 Type' : ($mmrpg_database_types_count).' Types') : '0 Types').' )';

?>
<div id="types">
  <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Types Overview</h2>
  <div class="subbody">
    <p class="text">The robots, abilities, fields, and many other aspects of the Mega Man RPG are designed around <?= $mmrpg_database_types_count ?> predefined &quot;types&quot; that represent various elemental affinities and/or methods of attack in the game.  The <span class="type_span ability_type ability_type_cutter">Cutter</span> type is generally used to describe robots or abilities that cut or slice in some way, the <span class="type_span ability_type ability_type_freeze">Freeze</span> type is used to describe things that have a freezing action or are otherwise cold, and so on. These typing distinctions play a central role in battle, where each robot has a different set of weakness and/or resistance to specific abilities.  Using <span class="type_span ability_type ability_type_flame">Fire Storm</span> on a robot with a weakness to <span class="type_span ability_type ability_type_flame">Flame</span> would deal twice the amount of damage it would normally, while using <span class="type_span ability_type ability_type_water">Bubble Lead</span> on a robot with a resistance to <span class="type_span ability_type ability_type_water">Water</span> would only do half as much damage.  Knowing these types can mean the difference between victory and defeat in certain situations, so using this database and the in-game &quot;Scan&quot; option are encouraged. Below, please find an alphabetized list of all the types in the game:</p>
    <ul style="overflow: hidden; padding: 4px 0 6px;">
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
  <h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Type Distributions</h2>
  <div class="subbody">
    <p class="text">Given the importance of these types classifications, knowing the distribution of them in the game might be useful in a few ways.  As an example, knowing there are many robots with a weakness to <span class="type_span ability_type ability_type_cutter">Cutter</span> attacks might prompt one to equip more of those types of abilities.  Additionally, knowing the type distribution will also make it easier to balance the game from the development side and may help prevent certain types from getting ignored or abused.  As such, a series of bar-graphs have been created to show how and where these types are being used in the RPG so far.  These bar-graphs are linked directly to the game's logic, so they will always be in sync with recent additions and updates to the project.  Please feel free to use these as reference when preparing for battle, and let me know if you have any questions.</p>
    <p class="text" style="">Please note that robots / abilities without a type are not included in the bar-graphs below, and for the weakness / resistance / affinity / immunity graphs the types which have no entries are skipped entirely.  Lastly, these stats are based on all current <em>and future</em> content and may not represent the current conditions perfectly.</p>
    <div style="overflow: hidden; padding-top: 10px;">

      <? foreach($mmrpg_database_robots_types AS $type_mechanic => $type_array): ?>
        <? $this_overall_count = $type_mechanic == 'cores' ? $mmrpg_database_types_count : $mmrpg_database_types_count - 1;  ?>
        <div class="type_chart type_chart_cores">
          <?
          // ROBOT CORES
          if ($type_mechanic == 'cores'){
            $temp_mechanic_data = $mmrpg_database_robots_types[$type_mechanic];
            asort($temp_mechanic_data);
            $temp_mechanic_data = array_reverse($temp_mechanic_data);
            ?>
            <strong class="category">Robot Cores</strong>
            <?/*<p class="text">Almost all robots in the Mega Man RPG posses an elemental &quot;core&quot; of some kind.  This core determines which abilities a robot can be equipped with as well as how they use those abilities in battle.  When a robot uses an ability of the same type as its core, the power of that ability is boosted by 25%.  The bar-graph below shows how many robots there are in the game with cores of each type.  Robots without a core are not included in the calculations.</p>*/?>
            <div class="text wrapper">
              <? $key_counter = 0; ?>
              <table width="100%">
                <colgroup>
                  <? foreach($temp_mechanic_data AS $type_token => $type_count): ?>
                  <? if (empty($type_count) || $type_token == 'none'){ continue; } ?>
                  <? $this_percent = round(($type_count / $mmrpg_database_robots_count) * 100, 1);  ?>
                  <col width="<?= $this_percent.'%' ?>">
                  <? endforeach; ?>
                </colgroup>
                <tbody>
                  <tr>
                    <? foreach($temp_mechanic_data AS $type_token => $type_count): ?>
                    <? if (empty($type_count) || $type_token == 'none'){ continue; } ?>
                    <? $this_percent = round(($type_count / $mmrpg_database_robots_count) * 100, 1);  ?>
                    <? $this_title = ucfirst($type_token).' Type | '.$type_count.' / '.$mmrpg_database_robots_count.' Robots | '.$this_percent.'% ';  ?>
                    <td><div class="type_percent ability_type ability_type_<?= $type_token ?>" title="<?= $this_title ?>"><?= $this_percent.'%' ?></div><div class="type_label" title="<?= $this_title ?>"><?=ucfirst($type_token)?></div></td>
                    <? $key_counter++; ?>
                    <? endforeach; ?>
                  </tr>
                </tbody>
              </table>
            </div>
            <?
          }
          // ROBOT WEAKNESSES / RESISTANCES / AFFINITIES / IMMUNITIES
          else {
            $temp_mechanic_data = $mmrpg_database_robots_types[$type_mechanic];
            unset($temp_mechanic_data['none']);
            asort($temp_mechanic_data);
            $temp_mechanic_data = array_reverse($temp_mechanic_data);
            $temp_mechanic_count = 0;
            foreach ($temp_mechanic_data AS $type_token => $type_count){ $temp_mechanic_count += $type_count; }
            ?>
            <strong class="category">Robot <?=ucfirst($type_mechanic)?></strong>
            <div class="text wrapper">
              <? $key_counter = 0; ?>
              <table width="100%">
                <colgroup>
                  <? foreach($temp_mechanic_data AS $type_token => $type_count): ?>
                  <? if (empty($type_count)){ continue; } ?>
                  <? $this_percent = round(($type_count / $temp_mechanic_count) * 100, 1);  ?>
                  <col width="<?= $this_percent.'%' ?>">
                  <? endforeach; ?>
                </colgroup>
                <tbody>
                  <tr>
                    <? foreach($temp_mechanic_data AS $type_token => $type_count): ?>
                    <? if (empty($type_count)){ continue; } ?>
                    <? $this_percent = round(($type_count / $temp_mechanic_count) * 100, 1);  ?>
                    <? $this_title = ucfirst($type_token).' Type | '.$type_count.' / '.$mmrpg_database_robots_count.' Robots | '.$this_percent.'% ';  ?>
                    <td><div class="type_percent ability_type ability_type_<?= $type_token ?>" title="<?= $this_title ?>"><?= $this_percent.'%' ?></div><div class="type_label" title="<?= $this_title ?>"><?=ucfirst($type_token)?></div></td>
                    <? $key_counter++; ?>
                    <? endforeach; ?>
                  </tr>
                </tbody>
              </table>
            </div>
            <?
          }
          ?>
        </div>
      <? endforeach; ?>

      <div class="type_chart type_chart_cores">
        <strong class="category">Ability Types</strong>
        <div class="wrapper">
          <?
          $key_counter = 0;
          //die('$mmrpg_database_abilities_types = <pre>'.print_r($mmrpg_database_abilities_types, true).'</pre>');
          $temp_mechanic_data = $mmrpg_database_abilities_types;
          unset($temp_mechanic_data['none']);
          asort($temp_mechanic_data);
          $temp_mechanic_data = array_reverse($temp_mechanic_data);
          ?>
          <table width="100%">
            <colgroup>
              <? foreach($temp_mechanic_data AS $type_token => $type_count): ?>
              <? if (empty($type_count) || $type_token == 'none'){ continue; } ?>
              <? $this_percent = round(($type_count / $mmrpg_database_abilities_count) * 100, 1);  ?>
              <col width="<?= $this_percent.'%' ?>">
              <? endforeach; ?>
            </colgroup>
            <tbody>
              <tr>
                <? foreach($temp_mechanic_data AS $type_token => $type_count): ?>
                <? if (empty($type_count) || $type_token == 'none'){ continue; } ?>
                <? $this_percent = round(($type_count / $mmrpg_database_abilities_count) * 100, 1);  ?>
                <? $this_title = ucfirst($type_token).' Type | '.$type_count.' / '.$mmrpg_database_abilities_count.' Abilities | '.$this_percent.'% ';  ?>
                <td><div class="type_percent ability_type ability_type_<?= $type_token ?>" title="<?= $this_title ?>"><?= $this_percent.'%' ?></div><div class="type_label" title="<?= $this_title ?>"><?=ucfirst($type_token)?></div></td>
                <? $key_counter++; ?>
                <? endforeach; ?>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
<?

?>