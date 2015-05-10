<?php
// Require the application top to access files
require_once('top.php');
// Update the document to to XML and print the header
header("Content-type: text/xml; charset=utf-8");
echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
?>
<browsergameshub version="0.1">
  <name>Mega Man RPG Prototype</name>
  <site_url>http://megamanpoweredup.net/rpg2k11/</site_url>
  <logo_url>http://megamanpoweredup.net/rpg2k11/images/assets/ipad-icon_72x72.png</logo_url>
  <genre>rpg</genre>
  <setting>future</setting>
  <effort>average</effort>
  <players>1</players>
  <status>beta</status>
  <payment>free</payment>
  <timing>rounds</timing>
  <descriptions>
    <description lang="en">Dr. Light's robots have been stolen again, and it's up to Mega Man to fight his way through the eight stages and bring his brothers home! Defeat robot masters to steal their powers and unlock them for use in battle, then take on Dr. Wily for (another) final showdown! Fight! For everlasting peace!</description>
    <description lang="en">Battle through more than thirty robot masters in classic RPG style with either Dr. Light and Mega Man, Dr. Wily and Bass, or Dr. Cossack and Proto Man!  The Mega Man RPG Prototype is a browser-based fangame that combines the mechanics of both the Pokémon and Mega Man series of video games into one strange and wonderful little time waster.</description>
    <description lang="en">The Mega Man RPG Prototype is an ongoing fangame project with the goal of creating a no-install, no-download, cross-platform, browser-based, progress-saving, Mega Man RPG (or what some would call a PBBG) that combines the addictive collection and battle mechanics of the Pokémon series with the beloved robots and special weapons of the classic Mega Man series. Fight your way through more than sixteen thirty-two robot masters in a turn-based battle system reminiscent of both play-by-post forum games and early 8-bit role-playing games.</description>
  </descriptions>
  <servers>
    <server>
      <id>001</id>
      <name>Prototype Server</name>
      <version><?php
        // Print the version number
        echo preg_replace('/([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})/', '$1.$2.$3', MMRPG_CONFIG_CACHE_DATE);
        ?></version>
      <game_url>http://megamanpoweredup.net/rpg2k11/prototype/</game_url>
      <ranking_url>http://megamanpoweredup.net/rpg2k11/mmrpg-ranking.xml</ranking_url>
      <players><?php
        // Require the gallery data for display
        require_once('data/leaderboard.php');
        echo !empty($this_leaderboard_count) ? $this_leaderboard_count : 0;
        ?></players>
      <status>open</status>
			<descriptions>
				<description lang="en">Primary prototype server - all users are beta testers.</description>
			</descriptions>
    </server>
  </servers>
  <screenshots><?php
    // Require the gallery data for display
    require_once('data/gallery.php');
    foreach ($this_gallery_xml AS $key => $xml){
      if ($key >= 10){ break; }
      echo $xml."\n";
    }
    ?></screenshots>
</browsergameshub>