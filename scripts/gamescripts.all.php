<?

// Require the top file if not already included
require_once(dirname(dirname(__FILE__)).'/top.php');

// Define default include flags and check for existence of override variables
$game_howlerRequired = isset($game_howlerRequired) && is_bool($game_howlerRequired) ? $game_howlerRequired : true;
$game_scrollbarRequired = isset($game_scrollbarRequired) && is_bool($game_scrollbarRequired) ? $game_scrollbarRequired : true;
$game_chartsRequired = isset($game_chartsRequired) && is_bool($game_chartsRequired) ? $game_chartsRequired : false;
$game_sortingRequired = isset($game_sortingRequired) && is_bool($game_sortingRequired) ? $game_sortingRequired : false;
$game_prototypeRequired = isset($game_prototypeRequired) && is_bool($game_prototypeRequired) ? $game_prototypeRequired : true;

// Print out the include markup for each of the prototype game scripts
?>
<script type="text/javascript" src="/.libs/jquery/jquery-<?= MMRPG_CONFIG_JQUERY_VERSION ?>.min.js"></script>
<? if ($game_howlerRequired){
    ?><script type="text/javascript" src="/.libs/howler-js/howler.core.min.js"></script><? echo PHP_EOL;
    ?><script type="text/javascript" src="/.libs/howler-js/howler.min.js"></script><? echo PHP_EOL;
    } ?>
<? if ($game_scrollbarRequired){
    ?><script type="text/javascript" src="/.libs/jquery-perfect-scrollbar/jquery.scrollbar.min.js"></script><? echo PHP_EOL;
    } ?>
<? if ($game_chartsRequired){
    ?><script type="text/javascript" src="/.libs/chart-js/Chart-2.4.0.min.js"></script><? echo PHP_EOL;
    } ?>
<? if ($game_sortingRequired){
    ?><script type="text/javascript" src="/.libs/jquery-ui-sortable/jquery.sortable.min.js"></script><? echo PHP_EOL;
    } ?>
<? if (true){
    ?><script type="text/javascript" src="/scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script><? echo PHP_EOL;
    } ?>
<? if ($game_prototypeRequired){
    ?><script type="text/javascript" src="/scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script><? echo PHP_EOL;
    } ?>
