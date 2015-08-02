<?
/*
 * INDEX PAGE : ABOUT
 */

// Define the SEO variables for this page
$this_seo_title = 'Story | About | '.$this_seo_title;
$this_seo_description = 'The Story of the Mega Man RPG Prototype. The year is 20XX. It has been several years since the Roboenza outbreak, and no one has heard from Dr. Wily since.  Dr. Light and Dr. Cossack, thinking about the past and looking toward the future, agreed to work on a massive robot database...';

// Define the Open Graph variables for this page
$this_graph_data['title'] = 'The Story So Far';
$this_graph_data['description'] = 'The year 20XX.  It has been several years since the Roboenza outbreak, and no one has heard from Dr. Wily since.  Dr. Light and Dr. Cossack, thinking about the past and looking toward the future, agreed to work on a massive robot database...';
//$this_graph_data['image'] = MMRPG_CONFIG_ROOTURL.'images/assets/mmrpg-prototype-logo.png';
//$this_graph_data['type'] = 'website';


// Define the MARKUP variables for this page
$this_markup_header = 'Mega Man RPG Prototype Story';

// Start generating the page markup
ob_start();
?>

<div style="overflow: hidden;">
<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">Story Overview</h2>
<div class="subbody" style="min-height: 85px; overflow: visible;">
  <div class="float float_right" style="width: 80px; height: 80px; position: relative;">
    <div class="sprite sprite_80x80 sprite_80x80_<?= mmrpg_battle::random_robot_frame() ?>" style="position: absolute; left: -18px; bottom: -6px; z-index: 2; background-image: url(images/robots/trill/sprite_left_80x80.png);"></div>
    <div class="sprite sprite_160x160 sprite_160x160_<?= mmrpg_battle::random_robot_frame() ?>" style="position: absolute; left: -30px; bottom: -6px; z-index: 1; background-image: url(images/robots/slur/sprite_left_160x160.png);"></div>
  </div>
  <p class="text">The <strong>Mega Man RPG Prototype</strong> is a constantly changing and evolving project, and because we always put mechanics before story the narrative has been revised countless times over the past several years to accomodate new characters, new mechanics, and new missions structures.  The current working version of the story - or at the very least the game's prologue - is laid out below.  Inspirations and influences come from a variety of sources, but the basic premise for the prototype was created by me and the text below was carefully written out by    </p>
</div>
</div>

<h2 class="subheader field_type_<?= MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">The Story So Far&hellip;</h2>
<div class="subbody community bodytext" style="padding-top: 10px; padding-bottom: 10px;">

  <div class="field field_panel field_panel_background" style="background-image: url(images/fields/light-laboratory/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>);">
    <div class="wrap">
      <div class="field field_panel field_panel_foreground" style="background-image: url(images/fields/light-laboratory/battle-field_foreground_base.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">
        <div class="wrap">

          <span style="position: relative; top: -3px;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_01"><span><img src="images/abilities/super-arm-7/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
              <span class="sprite_image sprite_image_40x40 sprite_image_40x40_03" style="margin: 0 -4px; opacity: 0.6;"><span><img src="images/abilities/copy-shot/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_01"><span><img src="images/abilities/super-arm-7/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
          </span>

          <span style="position: relative; top: 6px; padding: 0 9%;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_taunt"><span><img src="images/players/dr-cossack/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dr. Cossack" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_base"><span><img src="images/shops/kalinka/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Kalinka" /></span></span>
            <span style="position: relative; top: 6px; padding: 0 6%;">
              <span class="sprite_image sprite_image_40x40 sprite_image_40x40_victory"><span><img src="images/players/dr-light/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dr. Light" /></span></span>
              <span class="sprite_image sprite_image_40x40 sprite_image_40x40_taunt"><span><img src="images/robots/mega-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Mega Man" /></span></span>
            </span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_base"><span><img src="images/robots/roll/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Roll" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_base"><span><img src="images/shops/auto/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Auto" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_victory" style="position: relative; top: -3px;"><span><img src="images/robots/proto-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Proto Man" /></span></span>
          </span>

          <span style="position: relative; top: -3px;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_09"><span><img src="images/abilities/super-arm-7/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
              <span class="sprite_image sprite_image_40x40 sprite_image_40x40_06" style=" opacity: 0.6;"><span><img src="images/abilities/copy-shot/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_09"><span><img src="images/abilities/super-arm-7/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
          </span>

        </div>
      </div>
    </div>
  </div>

  <p class="text" style="margin-bottom: 20px;">The year 20XX.  It has been several years since the Roboenza outbreak, and no one has heard from Dr. Wily since.  Dr. Light and Dr. Cossack, thinking about the past and looking toward the future, agreed to work on a massive robot database, which would compile all the robot data throughout the world into a single, detailed system so people could reference it in the future.  Robots would be able to "upload" themselves into this database, and hone their skills against the data, which would lead robotkind to a stronger, smarter future.</p>


  <div class="field field_panel field_panel_background" style="background-position: center top; background-image: url(images/fields/cossack-citadel/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>);">
    <div class="wrap">
      <div class="field field_panel field_panel_foreground" style="background-image: url(images/fields/cossack-citadel/battle-field_foreground_base.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">
        <div class="wrap">

          <span style="position: relative; top: 12px;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_defend"><span><img src="images/robots/disco/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Disco" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_03" style="margin: 0 -10px 0 -18px;"><span><img src="images/players/dr-wily/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dr. Wily" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_defend"><span><img src="images/robots/bass/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Bass" /></span></span>
          </span>

          <span style="position: relative; top: 6px; padding: 9%;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_defend"><span><img src="images/robots/roll/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Roll" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_defend" style="margin-left: -10px;"><span><img src="images/robots/proto-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Proto Man" /></span></span>
            <span style="position: relative; top: -6px; padding: 0 3%;">
              <span class="sprite_image sprite_image_40x40 sprite_image_40x40_01"><span><img src="images/abilities/super-arm-7/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
                <span class="sprite_image sprite_image_40x40 sprite_image_40x40_09"><span><img src="images/abilities/super-arm-7/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
                  <span class="sprite_image sprite_image_40x40 sprite_image_40x40_03" style="opacity: 0.4;"><span><img src="images/abilities/copy-shot/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
                  <span class="sprite_image sprite_image_40x40 sprite_image_40x40_06" style="margin: 0 -10px; opacity: 0.3;"><span><img src="images/abilities/copy-shot/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
                  <span class="sprite_image sprite_image_40x40 sprite_image_40x40_09" style="opacity: 0.4;"><span><img src="images/abilities/copy-shot/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
                <span class="sprite_image sprite_image_40x40 sprite_image_40x40_09"><span><img src="images/abilities/super-arm-7/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
              <span class="sprite_image sprite_image_40x40 sprite_image_40x40_01"><span><img src="images/abilities/super-arm-7/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt=""></span></span>
            </span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_02"><span><img src="images/players/dr-cossack/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dr. Cossack" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_05" style="margin-left: -10px;"><span><img src="images/players/dr-light/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dr. Light" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_shoot" style="margin-left: -10px;"><span><img src="images/robots/mega-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Mega Man" /></span></span>
          </span>

          <span style="position: relative; top: 12px;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_01"><span><img src="images/shops/kalinka/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Kalinka" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_01" style="margin: 0 -9px 0 -12px;"><span><img src="images/shops/auto/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Auto" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_01"><span><img src="images/shops/reggae/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Reggae" /></span></span>
          </span>

          <span style="position: absolute; top: 0; right: 0; left: 0; bottom: 0; z-index: 10;">
            <span style="position: relative; top: 32px; left: -18px">
              <span class="sprite_image sprite_image_40x40 sprite_image_40x40_shoot"><span><img src="images/robots/trill/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Trill" /></span></span>
              <span class="sprite_image sprite_image_80x80 sprite_image_80x80_base" style="margin-left: -4px;"><span><img src="images/robots/slur/sprite_right_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Slur" /></span></span>
            </span>
          </span>

        </div>
      </div>
    </div>
  </div>

  <p class="text" style="margin-bottom: 20px;">After months of finding and compiling this data, Light and Cossack are prepared to unveil a prototype of the database to the public and demonstrate its features (Even if the system still had some bugs).  Little did they know that there were two surprises waiting for them there that night, the first of which was Dr. Wily hiding in the audience, watching the unveil.  The second surprise would prove to be much more devastating.  The public unveil seemed to be going fine at first, but in the middle of a test run, a pair of curious alien robots appeared!</p>


  <div class="field field_panel field_panel_background" style="background-position: center -30px; background-image: url(images/fields/intro-field/battle-field_background_base.gif?<?= MMRPG_CONFIG_CACHE_DATE ?>);">
    <div class="wrap">
      <div class="field field_panel field_panel_foreground" style="background-image: url(images/fields/intro-field/battle-field_foreground_base.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);">
        <div class="wrap">

          <span style="position: absolute; top: 0; right: 0; left: 0; bottom: 0; z-index: -10; overflow: hidden;">
            <span style="position: relative; top: -28px; left: -16px">
              <span class="sprite_image sprite_image_160x160 sprite_image_160x160_base" style="margin-left: -4px;"><span><img src="images/objects/intro-field-light/sprite_right_160x160.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Laboratory" /></span></span>
            </span>
          </span>

          <span style="position: relative; top: -12px; z-index: 1; padding-right: 0;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 21px; margin-left: 0; z-index: 8;"><span><img src="images/robots/metal-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Metal Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 18px; margin-left: -9px; z-index: 7;"><span><img src="images/robots/bubble-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Bubble Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 15px; margin-left: -9px; z-index: 6;"><span><img src="images/robots/flash-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Flash Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 12px; margin-left: -9px; z-index: 5;"><span><img src="images/robots/crash-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Crash Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 9px; margin-left: -9px; z-index: 4;"><span><img src="images/robots/wood-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Wood Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 6px; margin-left: -9px; z-index: 3;"><span><img src="images/robots/heat-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Heat Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 3px; margin-left: -9px; z-index: 2;"><span><img src="images/robots/quick-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Quick Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 0; margin-left: -9px; z-index: 1;"><span><img src="images/robots/air-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Air Man" /></span></span>
          </span>
          <span style="position: relative; top: 6px; left: -18px; z-index: 2; padding-right: 2%;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_taunt"><span><img src="images/robots/disco/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Disco" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_05" style="position: relative; top: 3px; margin: 0 -12px;"><span><img src="images/players/dr-wily/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dr. Wily" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_victory"><span><img src="images/robots/bass/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Bass" /></span></span>
          </span>

          <span style="position: relative; top: 9px;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_defend"><span><img src="images/robots/roll/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Roll" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_05" style="position: relative; top: 3px; margin: 0 -14px 0 -8px;"><span><img src="images/players/dr-light/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dr. Light" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_defend"><span><img src="images/robots/mega-man/sprite_right_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Mega Man" /></span></span>
          </span>

          <span style="position: relative; top: 6px; right: -18px; z-index: 2; padding-left: 2%;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_shoot"><span><img src="images/robots/proto-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Proto Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_05" style="position: relative; top: 3px; margin: 0 -12px 0 -14px;"><span><img src="images/players/dr-cossack/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dr. Cossack" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_taunt"><span><img src="images/robots/rhythm/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Rhythm" /></span></span>
          </span>
          <span style="position: relative; top: -12px; z-index: 1; padding-left: 0;">
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 0; margin-left: -9px; z-index: 1;"><span><img src="images/robots/drill-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Drill Man" /></span></span>
            <span class="sprite_image sprite_image_80x80 sprite_image_80x80_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 3px; margin-left: -9px; z-index: 2;"><span><img src="images/robots/toad-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Toad Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 6px; margin-left: -9px; z-index: 3;"><span><img src="images/robots/bright-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Bright Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 9px; margin-left: -9px; z-index: 4;"><span><img src="images/robots/pharaoh-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Pharaoh Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 12px; margin-left: -9px; z-index: 5;"><span><img src="images/robots/ring-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Ring Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 15px; margin-left: -9px; z-index: 6;"><span><img src="images/robots/dust-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dust Man" /></span></span>
            <span class="sprite_image sprite_image_80x80 sprite_image_80x80_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 18px; margin-left: -9px; z-index: 7;"><span><img src="images/robots/dive-man/sprite_left_80x80.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Dive Man" /></span></span>
            <span class="sprite_image sprite_image_40x40 sprite_image_40x40_<?= mmrpg_battle::weighted_chance_static(array('shoot', 'summon', 'taunt', 'defend'), array(3, 3, 2, 2)) ?>" style="position: relative; top: 21px; margin-left: 0; z-index: 8;"><span><img src="images/robots/skull-man/sprite_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>" alt="Skull Man" /></span></span>
          </span>

        </div>
      </div>
    </div>
  </div>

  <p class="text" style="margin-bottom: 0;">The robots Light and Cossack had assembled there were helpless against them, and once defeated, they use an arcane power to digitize robots and humans all over the world into the Prototype!  The robots proclaimed that humanity would have to fight one another to the top and defeat them in order to reclaim their freedom.  And thus began a mad scramble for humanity to raise their own digital robot armies and lead them into combat against one another...</p>

</div>

<?
// Collect the buffer and define the page markup
$this_markup_body = trim(preg_replace('#\s+#', ' ', ob_get_clean()));
?>