<?php
// Require the application top to access files
require_once('top.php');
// Update the document to to XML and print the header
header("Content-type: text/xml; charset=utf-8");
echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
// Collect some quick-access variables for populating the sitemap
$global_rooturl = MMRPG_CONFIG_ROOTURL;
$global_lastmod = preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})$/', '$1-$2-$3', MMRPG_CONFIG_CACHE_DATE);
// Define the gallery variables for the sitemap
$iterator = new DirectoryIterator('images/gallery/screenshots/thumbs/');
$gallery_mtime = -1;
$file;
foreach ($iterator as $fileinfo){
  if ($fileinfo->isFile()){
    if ($fileinfo->getMTime() > $gallery_mtime){
      $file = $fileinfo->getFilename();
      $gallery_mtime = $fileinfo->getMTime();
    }
  }
}
// Include the global database include file
require_once('data/database.php');
?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<url>
		<loc><?= $global_rooturl ?></loc>
		<lastmod><?= $global_lastmod ?></lastmod>
		<changefreq>daily</changefreq>
		<priority>0.8</priority>
	</url>
	<url>
		<loc><?= $global_rooturl ?>about/</loc>
		<lastmod><?= date('Y-m-d', filemtime('pages/page.about.php')) ?></lastmod>
		<changefreq>monthly</changefreq>
		<priority>0.7</priority>
	</url>
  <url>
    <loc><?= $global_rooturl ?>gallery/</loc>
    <lastmod><?= date('Y-m-d', $gallery_mtime) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
  <url>
    <loc><?= $global_rooturl ?>database/</loc>
    <lastmod><?= $global_lastmod ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc><?= $global_rooturl ?>database/players/</loc>
    <lastmod><?= $global_lastmod ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.9</priority>
  </url>
  <?
  // Loop through the database players
  foreach ($mmrpg_database_players AS $player_key => $player_info){
    ?>
    <url>
      <loc><?= $global_rooturl ?>database/players/<?= $player_info['player_token'] ?>/</loc>
      <lastmod><?= $global_lastmod ?></lastmod>
      <changefreq>monthly</changefreq>
      <priority>0.8</priority>
    </url>
    <?
  }
  ?>
  <url>
    <loc><?= $global_rooturl ?>database/robots/</loc>
    <lastmod><?= $global_lastmod ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.9</priority>
  </url>
  <?
  // Loop through the database robots
  foreach ($mmrpg_database_robots AS $robot_key => $robot_info){
    ?>
    <url>
      <loc><?= $global_rooturl ?>database/robots/<?= $robot_info['robot_token'] ?>/</loc>
      <lastmod><?= $global_lastmod ?></lastmod>
      <changefreq>monthly</changefreq>
      <priority>0.8</priority>
    </url>
    <?
  }
  ?>
  <url>
    <loc><?= $global_rooturl ?>database/abilities/</loc>
    <lastmod><?= $global_lastmod ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.9</priority>
  </url>
  <?
  // Loop through the database abilities
  foreach ($mmrpg_database_abilities AS $ability_key => $ability_info){
    ?>
    <url>
      <loc><?= $global_rooturl ?>database/abilities/<?= $ability_info['ability_token'] ?>/</loc>
      <lastmod><?= $global_lastmod ?></lastmod>
      <changefreq>monthly</changefreq>
      <priority>0.8</priority>
    </url>
    <?
  }
  ?>
  <url>
    <loc><?= $global_rooturl ?>database/types/</loc>
    <lastmod><?= date('Y-m-d', filemtime('pages/page.database_types.php')) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.3</priority>
  </url>
  <url>
    <loc><?= $global_rooturl ?>community/</loc>
    <lastmod><?= date('Y-m-d', time()) ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc><?= $global_rooturl ?>community/news/</loc>
    <lastmod><?= date('Y-m-d', time()) ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc><?= $global_rooturl ?>community/general/</loc>
    <lastmod><?= date('Y-m-d', time()) ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc><?= $global_rooturl ?>community/development/</loc>
    <lastmod><?= date('Y-m-d', time()) ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc><?= $global_rooturl ?>community/bugs/</loc>
    <lastmod><?= date('Y-m-d', time()) ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc><?= $global_rooturl ?>leaderboard/</loc>
    <lastmod><?= date('Y-m-d', time()) ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>
	<url>
		<loc><?= $global_rooturl ?>prototype/</loc>
		<lastmod><?= $global_lastmod ?></lastmod>
		<changefreq>weekly</changefreq>
		<priority>0.1</priority>
	</url>
  <url>
    <loc><?= $global_rooturl ?>credits/</loc>
    <lastmod><?= date('Y-m-d', filemtime('pages/page.credits.php')) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.2</priority>
  </url>
	<url>
		<loc><?= $global_rooturl ?>contact/</loc>
		<lastmod><?= date('Y-m-d', filemtime('pages/page.contact.php')) ?></lastmod>
		<changefreq>monthly</changefreq>
		<priority>0.3</priority>
	</url>
</urlset>