<? $footer_context = isset($footer_context) ? $footer_context : ''; ?>
<div id="credits">
        <strong>MMRPG Prototype</strong> by <a href="http://plutolighthouse.net/" target="_blank"><strong>Ageman20XX</strong></a>
        | <strong>Mega Man</strong> Trademarks &amp Characters
            &copy; <a href="http://www.capcom.com/" target="_blank" rel="nofollow"><strong>Capcom</strong></a> 1986 - <?= date('Y') ?>
    <br />
    This fangame was created by <a href="https://adrianmarceau.ca/" target="_blank" rel="author"><em>Adrian Marceau</em></a> and <a href="credits/">his team</a>.
    It is not affiliated with or endorsed by <a href="http://www.capcom.com/" target="_blank" rel="nofollow"><em>Capcom</em></a>.
    <br />
    <? if ($footer_context === 'base'){ ?>
        <? if ($this_current_page != 'home'): ?>
            <a href="<?= MMRPG_CONFIG_ROOTURL ?>">&laquo; Back to Home</a> |
        <? endif; ?>
    <? } ?>
    <? if ($footer_context === 'game'){ ?>
        <a href="<?= MMRPG_CONFIG_ROOTURL ?>">&laquo; Back to Website</a> |
    <? } ?>
    <a href="<?= MMRPG_CONFIG_ROOTURL ?>cookies/">Cookie Policy</a>
    | <a rel="nofollow" href="<?= MMRPG_CONFIG_ROOTURL ?>api/v2/" target="_blank">Data API</a>
    | <a href="<?= MMRPG_CONFIG_ROOTURL ?>contact/">Contact &amp; Feedback</a>
    <? if ($footer_context === 'base'){ ?>
        | <a href="<?= MMRPG_CONFIG_ROOTURL ?>prototype/" target="_blank">Play the Prototype &raquo;</a>
    <? } ?>
</div>