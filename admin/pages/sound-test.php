<? ob_start(); ?>

    <?

    // Predefine variables to hold markup for later
    $html_markup = '';
    $styles_markup = '';
    $scripts_markup = '';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Sound Test | '.$this_page_tabtitle;

    // Require howler scripts and styles for this page
    $admin_include_common_styles[] = 'howler';
    $admin_include_common_scripts[] = 'howler';

    // Define the root paths for the music and sound files
    $mmrpg_music_path = 'prototype/sounds/';
    $mmrpg_music_rootdir = MMRPG_CONFIG_CDN_ROOTDIR.$mmrpg_music_path;
    $mmrpg_music_rooturl = MMRPG_CONFIG_CDN_ROOTURL.$mmrpg_music_path;

    // Collect the sound effects index from the file and then output to the JS
    $this_sound_effects_path = $mmrpg_music_rootdir.'misc/sound-effects-curated/';
    $this_sound_effects_index = array();
    $this_sound_effects_index_raw = file_exists($this_sound_effects_path.'audio.json') ? json_decode(file_get_contents($this_sound_effects_path.'audio.json'), true) : array();
    if (!empty($this_sound_effects_index_raw['resources'])
        && !empty($this_sound_effects_index_raw['spritemap'])){
        $this_sound_effects_index['src'] = array();
        $this_sound_effects_index['sprite'] = array();
        foreach ($this_sound_effects_index_raw['resources'] AS $key => $resource){
            if (!preg_match('/\.(ogg|mp3)$/i', $resource)){ continue; }
            $source = 'misc/'.$resource.'?'.MMRPG_CONFIG_CACHE_DATE;
            $this_sound_effects_index['src'][] = $source;
        }
        foreach ($this_sound_effects_index_raw['spritemap'] AS $token => $spritemap){
            $sprite = array();
            $sprite['start'] = ceil($spritemap['start'] * 1000);
            $sprite['end'] = ceil($spritemap['end'] * 1000);
            $sprite['duration'] = $sprite['end'] - $sprite['start'];
            $this_sound_effects_index['sprite'][$token] = $sprite;
        }
    }
    //error_log('$this_sound_effects_index ='.print_r($this_sound_effects_index, true));
    $this_formatted_sound_effect_sprite = array();
    foreach ($this_sound_effects_index['sprite'] AS $alias => $details){
        $this_formatted_sound_effect_sprite[$alias] = array($details['start'], $details['duration'], false);
    }
    //error_log('$this_formatted_sound_effect_sprite ='.print_r($this_sound_effects_index, true));

    // Collect the categorized sound effect data and generate aliases index from the file
    $raw_json = trim(file_get_contents(MMRPG_CONFIG_ROOTDIR.'includes/sounds.json'));
    $raw_json = !empty($raw_json) ? preg_replace('!//.*$!m', '', $raw_json) : '';
    $this_sound_effects_categorized = !empty($raw_json) ? json_decode($raw_json, true) : array();
    $this_sound_effects_aliases = array();
    if (!empty($this_sound_effects_categorized)){
        foreach ($this_sound_effects_categorized AS $sfx_category => $sfx_category_info){
            if (!empty($sfx_category_info['index'])){
                $this_sound_effects_aliases = array_merge($this_sound_effects_aliases, $sfx_category_info['index']);
            }
        }
    }
    //error_log('$this_sound_effects_categorized ='.print_r($this_sound_effects_categorized, true));
    //error_log('$this_sound_effects_aliases ='.print_r($this_sound_effects_aliases, true));

    // Generate the markup for the sprite filters
    ob_start();
    ?>
        <div class="sound-test">

            <h1>MMRPG Sound Test</h1>

            <?
            // Loop through the sound test array and display the markup
            if (!empty($this_sound_effects_categorized)){
                foreach ($this_sound_effects_categorized as $category => $category_data) {
                    echo '<div class="sound-category">';
                        echo '<h2 class="category-title">'.$category_data['label'].'</h2>';
                        echo '<ul class="sound-list">';
                        foreach ($category_data['index'] as $sound_key => $sound_value) {
                            echo '<li class="sound-item" data-alias="'.$sound_key.'" data-value="'.$sound_value.'">';
                                echo '<strong class="sound-key">'.$sound_key.'</strong>';
                                echo '<a class="sound-button">';
                                    echo '<i class="fa fas fa-play"></i>';
                                echo '</a>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    echo '</div>';
                }
            }
            ?>

        </div>
    <?
    $html_markup .= ob_get_clean();
    ob_start();
    ?>
    <style type="text/css">
        html {
            background-color: #262626;
            color: #efefef;
            font-size: 16px;
            line-height: 1.6;
            font-family: monospace;
        }
        #mmrpg,
        #admin {
            width: auto;
            max-width: none;
            background-color: transparent;
        }
        #mmrpg #admin > .header,
        #mmrpg #admin > .content > .userinfo {
            display: none;
        }
        #mmrpg #admin > .content {
            padding: 0;
            background-color: transparent;
        }

        #mmrpg .sound-test {
            display: block;
            margin: 0 auto 20px;
            color: #efefef;
        }
        #mmrpg .sound-test a {
            color: inherit;
            text-decoration: none;
        }

        #mmrpg .sound-test h1 {
            display: block;
            padding-bottom: 20px;
            margin: 0 auto 20px;
            border-bottom: 1px solid #dedede;
        }

        #mmrpg .sound-test .sound-category {
            margin-bottom: 20px;
        }

        #mmrpg .sound-test .category-title {
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        #mmrpg .sound-test .sound-list {
            list-style: none;
            padding-left: 0;
        }
        #mmrpg .sound-test .sound-list:after {
            content: "";
            display: block;
            clear: both;
        }
        #mmrpg .sound-test .sound-list .sound-item {
            display: block;
            float: left;
            margin: 0 5px 5px 0;
            width: auto;
            padding: 6px 9px;
            border: 1px solid #4d4d4d;
        }

        #mmrpg .sound-test .sound-key {
            display: inline-block;
            padding: 0 6px 0 0;
        }

        #mmrpg .sound-test .sound-button {
            display: inline-block;
            padding: 3px 6px;
            cursor: pointer;
            width: 3em;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.1);
            transition: background-color 0.2s ease-in-out;
        }
        #mmrpg .sound-test .sound-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }


    </style>
    <?
    $styles_markup .= trim(ob_get_clean());
    ob_start();
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var soundPlayer = new Howl({
                src: [
                    '<?= $mmrpg_music_rooturl.$this_sound_effects_index['src'][0] ?>',
                    '<?= $mmrpg_music_rooturl.$this_sound_effects_index['src'][1] ?>'
                    ],
                sprite: <?= json_encode($this_formatted_sound_effect_sprite, JSON_PRETTY_PRINT) ?>,
                autoplay: false,
                volume: 0.5,
                rate: 1,
                loop: false,
                pool: 8
                });
            var $soundItems = $('.sound-item');
            $soundItems.each(function(player){
                var $thisItem = $(this);
                var $thisButton = $thisItem.find('.sound-button');
                var soundAlias = $thisItem.attr('data-alias');
                var soundValue = $thisItem.attr('data-value');
                $thisButton.bind('click', function(e){
                    e.preventDefault();
                    //console.log('playing sound: '+soundAlias+' ('+soundValue+')');
                    soundPlayer.stop();
                    soundPlayer.play(soundValue);
                    });
                });
        });
    </script>
    <?
    $scripts_markup .= trim(ob_get_clean());

    // Echo all the markup we've generated here
    echo($styles_markup.PHP_EOL);
    echo($scripts_markup.PHP_EOL);
    echo($html_markup.PHP_EOL);
    //exit();

    ?>

<? $this_page_markup .= ob_get_clean(); ?>
