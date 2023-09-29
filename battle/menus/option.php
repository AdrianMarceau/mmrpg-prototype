<?
// Generate the markup for the action option panel
ob_start();

	// Define the markup for the option buttons

	$temp_options = array();
	$block_num = 0;

	// Display the option for SKIP TURN
	$block_num++;
	$temp_options[] = '<a data-order="'.$block_num.'" class="button action_option block_'.$block_num.' ability_type_speed" type="button" data-action="ability_8_action-noweapons"><label><span class="multi">Skip<br />Turn</span></label></a>';

	// Display the option for CHARGE WEAPONS
	$block_num++;
	$temp_options[] = '<a data-order="'.$block_num.'" class="button action_option block_'.$block_num.' ability_type_defense" type="button" data-action="ability_9_action-chargeweapons"><label><span class="multi">Manual<br />Recharge</span></label></a>';

	// Display the option for RESTART BATTLE
	$block_num++;
	$temp_options[] = '<a data-order="'.$block_num.'" class="button action_option block_'.$block_num.' ability_type_shield" type="button" data-action="restart"><label><span class="multi">Restart<br />Battle</span></label></a>';

	// Display the option for RETURN TO MAIN MENU
	$block_num++;
	$temp_options[] = '<a data-order="'.$block_num.'" class="button action_option block_'.$block_num.' ability_type_attack" type="button" data-action="prototype"><label><span class="multi">Return&nbsp;To<br />Main&nbsp;Menu</span></label></a>';

    // Display the option for TOGGLE OVERLAYS
    $block_num++;
    $temp_options[] = '<a data-order="'.$block_num.'" class="button action_option block_'.$block_num.' ability_type_space" type="button" onclick="window.mmrpg_toggle_screenshot_mode();"><label><span class="multi">Toggle<br />Overlay</span></label></a>';

    // Display the option for RESTART MUSIC
    $block_num++;
    $temp_options[] = '<a data-order="'.$block_num.'" class="button action_option block_'.$block_num.' ability_type_space" type="button" onclick="parent.mmrpg_music_load(\''.(!strstr($this_field->field_music, '/') ? 'fields/'.$this_field->field_music : $this_field->field_music).'\', true);"><label><span class="multi">Restart<br />Music</span></label></a>';

    // Display the option for ANIMATION SETTINGS
    $block_num++;
    $temp_options[] = '<a data-order="'.$block_num.'" class="button action_option block_'.$block_num.' ability_type_space" type="button" data-panel="settings_animationEffects"><label><span class="multi">Animation<br />Settings</span></label></a>';

    // Display a SPACERs in this slot
    $block_num++;
    $temp_options[] = '<a data-order="'.$block_num.'" class="button action_option button_disabled block_'.$block_num.'" type="button">&nbsp;</a>';


    // Display a SPACER in this slot
    //$block_num++;
    //$temp_options[] = '<a data-order="'.$block_num.'" class="button action_option button_disabled block_'.$block_num.'" type="button">&nbsp;</a>';

	// If we're on the LOCAL or DEV build, display exrta options
	if (MMRPG_CONFIG_SERVER_ENV === 'local' || MMRPG_CONFIG_SERVER_ENV === 'dev'){

		// Add enough padding to push these options to another page
		if ($block_num < 8){
			for ($block_num++; $block_num <= 8; $block_num++){
				$temp_options[] = '<a data-order="'.$block_num.'" class="button action_option button_disabled block_'.$block_num.'" type="button">&nbsp;</a>';
			}
		}

		// Display the option for DEBUG MODE
		$block_num++;
		$current_debug_value = !empty($_SESSION['GAME']['debug_mode']) ? 1 : 0;
		$temp_options[] = '<a data-order="'.$block_num.'" class="button action_option block_'.$block_num.' ability_type_space" type="button" onclick="mmrpg_toggle_debug_mode(this);" data-value="'.$current_debug_value.'"><label><span class="multi"><span class="title">Debug Mode</span><br /><span class="value type type_'.($current_debug_value ? 'nature' : 'flame').'">'.($current_debug_value ? 'ON' : 'OFF').'</span></span></label></a>';

		// Display the DEVPOWER option for CLEAR MISSION
		$block_num++;
		$temp_options[] = '<a data-order="'.$block_num.'" class="button action_option block_'.$block_num.' ability_type_space" type="button" data-action="ability_10_action-devpower-clearmission"><label><span class="multi"><span class="title">Dev Power</span><br /><span class="value type type_shield">Clear Mission</span></span></label></a>';

	}

	// Count the number of items the player has and determine pages
	$current_options_count = count($temp_options);
	$current_options_pages = ceil($current_options_count / 8);

	// Display container for the main actions
	?>
	<div class="main_actions main_actions_hastitle">
		<span class="main_actions_title">
            <?
            // If there were more than eight items, print the page numbers
            if ($current_options_count > 8){
                $temp_selected_page = 1;
                echo '<span class="float_title">Select Option</span> ';
                echo '<span class="float_links">';
                    echo '<span class="page">Page</span>';
                    for ($i = 1; $i <= $current_options_pages; $i++){ echo '<a class="button num'.($i == $temp_selected_page ? ' active' : '').'" href="#'.$i.'">'.$i.'</a>'; }
                    if (MMRPG_CONFIG_SERVER_ENV === 'local' || MMRPG_CONFIG_SERVER_ENV === 'dev'){
                    	echo '<a class="button num" data-action="ability_10_action-devpower-clearmission" style="position: absolute; left: 420px; top: 0px; z-index: 9;"><i class="fa fas fa-skull"></i></a>';
                    }
                echo '</span> ';
            }
            // Otherwise, simply print the item select text label
            else {
                echo 'Select Option';
            }
            ?>
		</span>
	<?
	// Ensure there are options to display
	if (!empty($temp_options)){
		// Count the total number of options
		$num_options = count($temp_options);
		// Loop through each option and display its button markup
		foreach ($temp_options AS $key => $option_markup){
			// Display the option button's generated markup
			echo $option_markup;
		}
        // If there were less than 8 items, fill in the empty spaces
        if ($num_options % 8 != 0){
            $temp_padding_amount = 8 - ($num_options % 8);
            $temp_last_key = $num_options + $temp_padding_amount;
            for ($i = $num_options; $i < $temp_last_key; $i++){
                // Display an empty button placeholder
                ?><a class="button action_option button_disabled block_<?= $i + 1 ?>" type="button">&nbsp;</a><?
            }
        }
	}
	// End the main action container tag
	?></div><?
	// Display the back button by default
	?><div class="sub_actions"><a data-order="7" class="button action_back" type="button" data-panel="battle"><label>Back</label></a></div><?
$actions_markup['option'] = trim(ob_get_clean());
$actions_markup['option'] = preg_replace('#\s+#', ' ', $actions_markup['option']);
?>