<?

// Define the array to hold all the game conversation
$game_conversion_table = array();

// By GAME
$game_conversion_table['MM00'] = 'MM1'; // *** Misc
$game_conversion_table['MM01'] = 'MM1';
$game_conversion_table['MM02'] = 'MM2';
$game_conversion_table['MM03'] = 'MM3';
$game_conversion_table['MM04'] = 'MM4';
$game_conversion_table['MM05'] = 'MM5';
$game_conversion_table['MM06'] = 'MM6';
$game_conversion_table['MM07'] = 'MM7';
$game_conversion_table['MM08'] = 'MM8';
$game_conversion_table['MM085'] = 'RnF';
$game_conversion_table['MM09'] = 'MM9';
$game_conversion_table['MM10'] = 'MM10';
$game_conversion_table['MM11'] = 'MM11';
$game_conversion_table['MM19'] = ''; // *** Cache bots
$game_conversion_table['MM20'] = ''; // *** Killers
$game_conversion_table['MM21'] = 'MMWW';
$game_conversion_table['MM30'] = 'MMV';
$game_conversion_table['MMEXE'] = 'MMBN1'; // *** Misc EXE Series
$game_conversion_table['MMRPG'] = 'MMRPG'; // Keep as-is
$game_conversion_table['MMRPG2'] = 'MMRPGPR';


// By PLAYER TOKEN

$game_conversion_table['players']['dr-light'] = 'MM1';
$game_conversion_table['players']['dr-wily'] = 'MM1';
$game_conversion_table['players']['dr-cossack'] = 'MM4';
$game_conversion_table['players']['dr-lalinde'] = 'MMARCHIE';


// By ROBOT TOKEN

$game_conversion_table['robots']['bass'] = 'MM7';
$game_conversion_table['robots']['disco'] = 'MMRPGP';
$game_conversion_table['robots']['mega-man'] = 'MM1';
$game_conversion_table['robots']['met'] = 'MM1';
$game_conversion_table['robots']['proto-man'] = 'MM3';
$game_conversion_table['robots']['rhythm'] = 'MMRPGP';
$game_conversion_table['robots']['rock'] = 'MM1';
$game_conversion_table['robots']['roll'] = 'MM1';

$game_conversion_table['robots']['enker'] = 'MMI';
$game_conversion_table['robots']['quint'] = 'MMII';
$game_conversion_table['robots']['punk'] = 'MMIII';
$game_conversion_table['robots']['ballade'] = 'MMIV';

$game_conversion_table['robots']['blossom-woman'] = 'MMRPGPR';
$game_conversion_table['robots']['desert-man'] = 'MMRPGPR/MMBN3';
$game_conversion_table['robots']['hallow-man'] = 'MMRPGPR';
$game_conversion_table['robots']['laser-man'] = 'MMRPGPR/MMBN4';
$game_conversion_table['robots']['portal-man'] = 'MMRPGPR';
$game_conversion_table['robots']['prism-man'] = 'MMRPGPR';
$game_conversion_table['robots']['shark-man'] = 'MMRPGPR/MMBN1';
$game_conversion_table['robots']['shield-man'] = 'MMRPGPR';
$game_conversion_table['robots']['target-man'] = 'MMRPGPR';
$game_conversion_table['robots']['zephyr-woman'] = 'MMRPGPR';
$game_conversion_table['robots']['cache'] = 'MMRPGPR/EXEPoN';

$game_conversion_table['robots']['solo'] = 'MMRPGPR/MMSF3';
$game_conversion_table['robots']['duo'] = 'MM8';
$game_conversion_table['robots']['duo-2'] = 'MM8';
$game_conversion_table['robots']['trio'] = 'MMRPGPR';
$game_conversion_table['robots']['trio-2'] = 'MMRPGPR';
$game_conversion_table['robots']['trio-3'] = 'MMRPGPR';

$game_conversion_table['robots']['cosmo-man'] = 'MMRPGPR/MMBN5';
$game_conversion_table['robots']['planet-man'] = 'MMRPGPR/MMBN2';

$game_conversion_table['robots']['trill'] = 'MMRPGP/MMBNTV4';
$game_conversion_table['robots']['slur'] = 'MMRPGP/MMBNTV3';

$game_conversion_table['robots']['mega-man-ds'] = 'MMRPGP/MM1';
$game_conversion_table['robots']['proto-man-ds'] = 'MMRPGP/MM3';
$game_conversion_table['robots']['bass-ds'] = 'MMRPGP/MM7';

$game_conversion_table['robots']['doc-robot'] = 'MM3';
$game_conversion_table['robots']['fake-man'] = 'MM9';

$game_conversion_table['robots']['dark-man'] = 'MM5';
$game_conversion_table['robots']['dark-man-2'] = 'MM5';
$game_conversion_table['robots']['dark-man-3'] = 'MM5';
$game_conversion_table['robots']['dark-man-4'] = 'MM5';

$game_conversion_table['robots']['bond-man'] = 'MM1';

$game_conversion_table['robots']['flutter-fly'] = 'MMRPGP';
$game_conversion_table['robots']['beetle-borg'] = 'RnFWS';


// By ABILITY TOKEN

$game_conversion_table['abilities']['met-shot'] = 'MM1';

$game_conversion_table['abilities']['buster-shot'] = 'MM1';
$game_conversion_table['abilities']['mega-buster'] = 'MM4';
$game_conversion_table['abilities']['proto-buster'] = 'MM3';
$game_conversion_table['abilities']['bass-buster'] = 'MM7';

$game_conversion_table['abilities']['sakugarne-bounce'] = $game_conversion_table['robots']['quint'];
$game_conversion_table['abilities']['sakugarne-hammer'] = $game_conversion_table['robots']['quint'];
$game_conversion_table['abilities']['mirror-buster'] = $game_conversion_table['robots']['enker'];
$game_conversion_table['abilities']['screw-crusher'] = $game_conversion_table['robots']['punk'];
$game_conversion_table['abilities']['ballade-cracker'] = $game_conversion_table['robots']['ballade'];

$game_conversion_table['abilities']['sticky-bond'] = $game_conversion_table['robots']['bond-man'];
$game_conversion_table['abilities']['sticky-shot'] = $game_conversion_table['robots']['bond-man'];

$game_conversion_table['abilities']['energy-fist'] = $game_conversion_table['robots']['duo'];
$game_conversion_table['abilities']['comet-attack'] = $game_conversion_table['robots']['duo'];
$game_conversion_table['abilities']['meteor-knuckle'] = $game_conversion_table['robots']['duo'];

$game_conversion_table['abilities']['trill-aura'] = $game_conversion_table['robots']['trill'];
$game_conversion_table['abilities']['trill-slasher'] = $game_conversion_table['robots']['trill'];
$game_conversion_table['abilities']['trill-teranova'] = $game_conversion_table['robots']['trill'];

$game_conversion_table['abilities']['slur-aura'] = $game_conversion_table['robots']['slur'];
$game_conversion_table['abilities']['slur-twister'] = $game_conversion_table['robots']['slur'];
$game_conversion_table['abilities']['slur-supernova'] = $game_conversion_table['robots']['slur'];

$game_conversion_table['abilities']['dark-boost'] = 'MMRPGPR';
$game_conversion_table['abilities']['dark-break'] = 'MMRPGPR';
$game_conversion_table['abilities']['dark-drain'] = 'MMRPGPR';

$game_conversion_table['abilities']['copy-shot'] = 'MMRPGP';
$game_conversion_table['abilities']['copy-soul'] = 'MMRPGP';

$game_conversion_table['abilities']['buster-charge'] = 'MMRPGP';
$game_conversion_table['abilities']['buster-relay'] = 'MMRPGP';

$game_conversion_table['abilities']['action-*'] = 'MMRPG';

$game_conversion_table['abilities']['mega-*'] = 'MMRPGP';
$game_conversion_table['abilities']['bass-*'] = 'MMRPGP';
$game_conversion_table['abilities']['proto-*'] = 'MMRPGP';

$game_conversion_table['abilities']['roll-*'] = 'MMRPGP';
$game_conversion_table['abilities']['rhythm-*'] = 'MMRPGP';
$game_conversion_table['abilities']['disco-*'] = 'MMRPGP';

$game_conversion_table['abilities']['*-shot'] = 'MMRPGP';
$game_conversion_table['abilities']['*-buster'] = 'MMRPGP';
$game_conversion_table['abilities']['*-overdrive'] = 'MMRPGP';

$game_conversion_table['abilities']['*-boost'] = 'MMRPGP';
$game_conversion_table['abilities']['*-break'] = 'MMRPGP';
$game_conversion_table['abilities']['*-swap'] = 'MMRPGP';

$game_conversion_table['abilities']['*-support'] = 'MMRPGP';
$game_conversion_table['abilities']['*-assault'] = 'MMRPGP';
$game_conversion_table['abilities']['*-shuffle'] = 'MMRPGP';

$game_conversion_table['abilities']['*-mode'] = 'MMRPGP';

$game_conversion_table['abilities']['*-breaker'] = 'MMRPGP';
$game_conversion_table['abilities']['*-booster'] = 'MMRPGP';

$game_conversion_table['abilities']['omega-*'] = 'MMRPGP';
$game_conversion_table['abilities']['core-*'] = 'MMRPGP';



// By ITEM TOKEN

$game_conversion_table['items']['energy-pellet'] = 'MM1';
$game_conversion_table['items']['energy-capsule'] = 'MM1';
$game_conversion_table['items']['energy-tank'] = 'MM2';

$game_conversion_table['items']['weapon-pellet'] = 'MM1';
$game_conversion_table['items']['weapon-capsule'] = 'MM1';
$game_conversion_table['items']['weapon-tank'] = 'MMIV';

$game_conversion_table['items']['extra-life'] = 'MM1';
$game_conversion_table['items']['yashichi'] = 'MM1';

$game_conversion_table['items']['omega-seed'] = 'MMRPGP';

$game_conversion_table['items']['attack-*'] = 'MMRPGP';
$game_conversion_table['items']['defense-*'] = 'MMRPGP';
$game_conversion_table['items']['speed-*'] = 'MMRPGP';
$game_conversion_table['items']['energy-*'] = 'MMRPGP';
$game_conversion_table['items']['weapon-*'] = 'MMRPGP';
$game_conversion_table['items']['super-*'] = 'MMRPGP';

$game_conversion_table['items']['*-shard'] = 'MMRPGP';
$game_conversion_table['items']['*-core'] = 'MMRPGP';

$game_conversion_table['items']['*-star'] = 'MMRPGP';
$game_conversion_table['items']['star'] = 'MMRPGP';

$game_conversion_table['items']['*-program'] = 'MMRPGP';
$game_conversion_table['items']['*-link'] = 'MMRPGP';
$game_conversion_table['items']['*-codes'] = 'MMRPGP';

$game_conversion_table['items']['*-upgrade'] = 'MMRPGP';
$game_conversion_table['items']['*-module'] = 'MMRPGP';
$game_conversion_table['items']['*-circuit'] = 'MMRPGP';
$game_conversion_table['items']['*-booster'] = 'MMRPGP';


// By FIELD TOKEN

$game_conversion_table['fields']['gentle-countryside'] = 'MM2';
$game_conversion_table['fields']['maniacal-hideaway'] = 'MM2';
$game_conversion_table['fields']['wintry-forefront'] = 'MM4';

$game_conversion_table['fields']['light-laboratory'] = 'MM3';
$game_conversion_table['fields']['wily-castle'] = 'MM1';
$game_conversion_table['fields']['cossack-citadel'] = 'MM4';

$game_conversion_table['fields']['intro-field'] = 'MMRPG';

$game_conversion_table['fields']['final-destination'] = 'MMRPGP';
$game_conversion_table['fields']['final-destination-2'] = 'MMRPGP';
$game_conversion_table['fields']['final-destination-3'] = 'MMRPGP';

$game_conversion_table['fields']['prototype-complete'] = 'MMRPGP';


// Define a function for converting an objects game data to the new format
function migrate_object_game_settings_to_new_format($object_kind, $object_xkind, $object_info){
    global $game_conversion_table;
    $token = $object_info[$object_kind.'_token'];
    $token_frags = array_pad(explode('-', $token), 3, '');
    $game = $object_info[$object_kind.'_game'];
    $new_game = $new_source = '';
    if ($token === $object_kind){ $new_game = 'MMRPG'; }
    elseif (!empty($game_conversion_table[$object_xkind][$token])){ $new_game = $game_conversion_table[$object_xkind][$token]; }
    elseif (!empty($game_conversion_table[$object_xkind][$token_frags[0].'-*'])){ $new_game = $game_conversion_table[$object_xkind][$token_frags[0].'-*']; }
    elseif (!empty($game_conversion_table[$object_xkind]['*-'.$token_frags[1]])){ $new_game = $game_conversion_table[$object_xkind]['*-'.$token_frags[1]]; }
    elseif (!empty($game_conversion_table[$game])){ $new_game = $game_conversion_table[$game]; }
    if (!empty($new_game) && strstr($new_game, '/')){ list($new_game, $new_source) = explode('/', $new_game); }
    $new_game_info = array($object_kind.'_token' => $token, $object_kind.'_game' => $new_game, $object_kind.'_source' => $new_source, 'backup' => $game);
    return $new_game_info;
}


?>