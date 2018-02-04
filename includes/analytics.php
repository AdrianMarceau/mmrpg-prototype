<? if (MMRPG_CONFIG_IS_LIVE): ?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= LEGACY_MMRPG_GA_ACCOUNTID ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= LEGACY_MMRPG_GA_ACCOUNTID ?>');
</script>
<? endif; ?>