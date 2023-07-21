<? if (defined('MMRPG_CONFIG_GA4_ACCOUNTID') && MMRPG_CONFIG_IS_LIVE): ?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= MMRPG_CONFIG_GA4_ACCOUNTID ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= MMRPG_CONFIG_GA4_ACCOUNTID ?>');
</script>
<? endif; ?>