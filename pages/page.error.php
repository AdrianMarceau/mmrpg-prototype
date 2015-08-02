<?
/*
 * INDEX PAGE : ERROR
 */

// Define the SEO variables for this page
$this_seo_title = 'Critical Error | '.$this_seo_title;
//$this_seo_description = 'The Mega Man RPG Prototype is an ongoing fan-game project with the goal of creating a no-install, cross-platform, browser-based, progress-saving, Mega Man RPG that combines the addictive collection and battle mechanics of the Pokémon series with the beloved robots and special weapons of the classic Mega Man series. Battle through more than thirty robot masters in classic RPG style with either Dr. Light and Mega Man or Dr. Wily and Proto Man in the wonderful and strange little time waster.';

// Define the markup header
$this_markup_header = 'Critical Prototype Error';

// Start generating the page markup
ob_start();
?>
<a name="overview" class="anchor">&nbsp;</a>
<div class="subbody">
  
  <div class="float float_right"><div class="sprite sprite_80x80 sprite_80x80_defeat" style="background-image: url(images/robots/mega-man/sprite_left_80x80.png);">Mega Man</div></div>
  <p class="text">
    <strong>Yikes!</strong>  Something has gone horribly wrong on the server and the prototype is completely down at the moment.
    Please hold tight while we look into the issue, and remember that this game is still in development and stuff happens occasionally.
    If this page has been here for a long time, it <em>probably</em> means the issue is serious and may require extra time to fix.
    In events of extended downtime you should <a href="http://facebook.com/megamanrpgprototype/" target="_blank">check the Facebook page</a> for updates.
    Thank you for playing and you we're sorry for the inconvenience.
  </p>
  
</div>
<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>