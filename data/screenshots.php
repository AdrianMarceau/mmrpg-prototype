<?

// Define the album link for the screenshots
$mmrpg_screenshots_album = 'http://imgur.com/a/MJCVi';

// Define the SCREENSHOTS create function for population
function mmrpg_screenshots_create($yyyymmdd, $title, $token){
  // Append this screenshot to the global array
  global $mmrpg_screenshots_array;
  $this_screenshot = array(
    'date_yyyymmdd' => $yyyymmdd,
    'image_title' => $title,
    'image_token' => $token
    );
  $mmrpg_screenshots_array[$yyyymmdd][] = $this_screenshot;
}
// Define the SCREENSHOTS array and populate with data
$mmrpg_screenshots_array = array();

// Define the screenshots for 2013/01/17
mmrpg_screenshots_create('20130117', 'Dr. Light Mission Select', 'CBbif');

// Define the screenshots for 2013/01/13
//mmrpg_screenshots_create('20130113', 'Dr. Light Mission Select', 'KyLsf');
mmrpg_screenshots_create('20130113', 'Proto Man vs Wood Man', 'HDKXD');
mmrpg_screenshots_create('20130113', 'Proto Man vs Quick Man', 'rXFvt');
mmrpg_screenshots_create('20130113', 'Proto Man vs Heat Man', 'gOcAo');
mmrpg_screenshots_create('20130113', 'Proto Man vs Air Man', 'xWaZA');

// Define the screenshots for 2012/12/02
mmrpg_screenshots_create('20121202', 'Mega Man vs Cut Man', 'L3Ur3');
mmrpg_screenshots_create('20121202', 'Quick Man vs Bubble Man', '6QRha');
mmrpg_screenshots_create('20121202', 'Mega Man vs Air Man', 'XU3Pk');
mmrpg_screenshots_create('20121202', 'Cut Man vs Bomb Man', 'ukCDk');
mmrpg_screenshots_create('20121202', 'Mega Man vs Oil Man', 'r354H');

?>