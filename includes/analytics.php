<? if (defined('MMRPG_CONFIG_GA4_ACCOUNTID') && MMRPG_CONFIG_IS_LIVE): ?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= MMRPG_CONFIG_GA4_ACCOUNTID ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?= MMRPG_CONFIG_GA4_ACCOUNTID ?>');
    <?
    // If any gtag events have been defined, include them in the page view
    if (isset($include_gtag_events) && !empty($include_gtag_events)){
        foreach ($include_gtag_events AS $event_name => $event_data){
            $event_data = json_encode($event_data, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
            echo "gtag('event', '{$event_name}', {$event_data});\n";
        }
    }
    ?>
</script>
<? endif; ?>