<? ob_start(); ?>

    <?

    // Predefine variables to hold markup for later
    $html_markup = '';
    $styles_markup = '';
    $scripts_markup = '';

    // Collect the URL arguments
    $allowed_kinds = array('players', 'robots', 'abilities', 'items', 'fields');
    $allowed_kinds_singular = array('player', 'robot', 'ability', 'item', 'field');
    $kind = !empty($_GET['kind']) && in_array($_GET['kind'], $allowed_kinds) ? $_GET['kind'] : 'players';
    $kind_singular = $allowed_kinds_singular[array_search($kind, $allowed_kinds)];
    $allowed_classes = array();
    $allowed_classes_tiers = array('master', 'mecha', 'boss');
    if ($kind === 'robots' || $kind === 'abilities'){ $allowed_classes = $allowed_classes_tiers; }
    $class = !empty($_GET['class']) && in_array($_GET['class'], $allowed_classes) ? $_GET['class'] : '';
    $hidden = !empty($_GET['hidden']) && $_GET['hidden'] === 'true' ? true : false;
    $key_min = !empty($_GET['min']) && is_numeric($_GET['min']) ? $_GET['min'] : 0;
    $key_max = !empty($_GET['max']) && is_numeric($_GET['max']) ? $_GET['max'] : 99;
    $key_offset = !empty($_GET['offset']) && is_numeric($_GET['offset']) ? $_GET['offset'] : 0;
    $direction = !empty($_GET['dir']) && $_GET['dir'] === 'left' ? 'left' : 'right';
    $max_columns = !empty($_GET['cols']) && is_numeric($_GET['cols']) && $_GET['cols'] > 0 ? $_GET['cols'] : 4;
    $required_rows = !empty($_GET['rows']) && is_numeric($_GET['rows']) && $_GET['rows'] > 0 ? $_GET['rows'] : 0;
    $sprite_padding = isset($_GET['pad']) && is_numeric($_GET['pad']) && $_GET['pad'] >= 0 ? $_GET['pad'] : 2;
    $sheet_gutters = isset($_GET['pad2']) && is_numeric($_GET['pad2']) && $_GET['pad2'] >= 0 ? $_GET['pad2'] : 10;
    $sheet_spacing = isset($_GET['pad3']) && is_numeric($_GET['pad3']) && $_GET['pad3'] >= 0 ? $_GET['pad3'] : 20;

    // Error message if `kind` or `class` is not provided
    if (!$kind) { die("Error: Missing required `kind` parameter."); }
    if (!empty($allowed_classes) && !$class) { $class = $allowed_classes[0]; }

    // Generate the markup for the sprite filters
    ob_start();
    ?>
    <form class="sprite-filters" action="/admin/view-sprites/" method="get">
        <div class="section main-fields">
            <div class="field" data-field="kind">
                <label for="kind">Kind:</label>
                <select name="kind" id="kind">
                    <?php foreach ($allowed_kinds as $allowed_kind): ?>
                        <option value="<?= $allowed_kind ?>" <?= $kind === $allowed_kind ? 'selected' : '' ?>><?= ucfirst($allowed_kind) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field <?= empty($allowed_classes) ? 'disabled' : '' ?>" data-field="class">
                <label for="class">Class:</label>
                <select name="class" id="class" <?= empty($allowed_classes) ? 'disabled' : '' ?>>
                    <option value="" <?= empty($class) ? 'selected' : '' ?>>-</option>
                    <?php foreach ($allowed_classes_tiers AS $allowed_class): ?>
                        <option value="<?= $allowed_class ?>" <?= $class === $allowed_class ? 'selected' : '' ?>><?= ucfirst($allowed_class) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" data-field="dir">
                <label for="direction">Direction:</label>
                <select name="dir" id="direction">
                    <option value="right" <?= $direction === 'right' ? 'selected' : '' ?>>Right</option>
                    <option value="left" <?= $direction === 'left' ? 'selected' : '' ?>>Left</option>
                </select>
            </div>
        </div>
        <div class="section additional-fields">
            <div class="field" data-field="cols">
                <label for="cols">Columns:</label>
                <input type="number" name="cols" id="cols" value="<?= $max_columns ?>" min="1" step="1" />
            </div>
            <div class="field" data-field="rows">
                <label for="rows">Rows:</label>
                <input type="number" name="rows" id="rows" value="<?= $required_rows ?>" min="0" step="1" />
            </div>
            <div class="field" data-field="pad">
                <label for="pad" title="Sprite Padding">Padding:</label>
                <input type="number" name="pad" id="pad" value="<?= $sprite_padding ?>" min="0" step="1" />
            </div>
            <div class="field" data-field="pad2">
                <label for="pad2" title="Sheet Padding">Gutters:</label>
                <input type="number" name="pad2" id="pad2" value="<?= $sheet_gutters ?>" min="0" step="1" />
            </div>
            <div class="field" data-field="pad3">
                <label for="pad3" title="Sheet Spacing">Spacing:</label>
                <input type="number" name="pad3" id="pad3" value="<?= $sheet_spacing ?>" min="0" step="1" />
            </div>
        </div>
        <div class="section form-buttons">
            <button type="reset">Reset</button>
            <button type="submit">Regenerate</button>
        </div>
    </form>
    <?
    $html_markup .= ob_get_clean();
    ob_start();
    ?>
    <style type="text/css">
        .sprite-filters {
            position: relative;
            display: block;
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin-bottom: 1rem;
            padding-right: 150px;
            padding: 6px;
            background-color: #ebebeb;
        }
        .sprite-filters .section {
            display: block;
            margin: 0 0 0.5rem 0;
        }
        .sprite-filters .section:after {
            content: "";
            display: block;
            clear: both;
        }
        .sprite-filters .field {
            display: block;
            float: left;
            padding: 0 1rem 0 0;
            margin: 0 0 0.5rem 0;
        }
        .sprite-filters .field.disabled {
            opacity: 0.6;
        }
        .sprite-filters .field label {
            display: block;
            margin: 0 auto 0.2rem;
            clear: both;
        }
        .sprite-filters label {
            display: inline-block;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        .sprite-filters select,
        .sprite-filters input[type="number"] {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 6px 9px;
            font-family: inherit;
            font-size: inherit;
            outline: none;
            transition: border-color 0.2s;
            max-width: 6rem;
        }
        .sprite-filters select:focus,
        .sprite-filters input[type="number"]:focus {
            border-color: #007bff;
        }

        .sprite-filters .section.main-fields {
            font-size: 120%;
        }
        .sprite-filters .section.main-fields .field[data-field="kind"] select {
            min-width: 10rem;
        }
        .sprite-filters .section.main-fields .field[data-field="class"] select {
            min-width: 8rem;
        }
        .sprite-filters .section.main-fields .field[data-field="dir"] select {
            min-width: 6rem;
        }

        .sprite-filters .section.additional-fields {
            font-size: 100%;
        }
        .sprite-filters .section.additional-fields select,
        .sprite-filters .section.additional-fields input[type="number"] {
            max-width: 5rem;
        }

        .sprite-filters button {
            background-color: #4c8aab;
            border: 0 solid #427794;
            color: #fff;
            border-radius: 4px;
            padding: 6px 12px;
            font-family: inherit;
            font-size: inherit;
            line-height: 1.3;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-left: auto;
            position: absolute;
            bottom: 6px;
            width: 140px;
        }
        .sprite-filters button[type="submit"] {
            font-size: 120%;
            right: 6px;
        }
        .sprite-filters button[type="reset"] {
            right: 150px;
            font-size: 110%;
            background-color: #c85151;
            border-color: #b63e3e;
            width: 100px;
        }
        .sprite-filters button:hover {
            background-color: #5d97b6;
        }
        .sprite-filters button[type="reset"]:hover {
            background-color: #d55c5c;
        }
        @media (max-width: 768px) {
            .sprite-filters button[type="submit"],
            .sprite-filters button[type="reset"] {
                position: static;
                width: auto;
                padding: 0.5rem 3rem;
                font-size: 120%;
            }
        }
        .sprite-filters .field[data-field="rows"] {
            display: none;
        }

    </style>
    <?
    $styles_markup .= trim(ob_get_clean());
    ob_start();
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Add functionality to the kind/class dropdowns with visbility rules
            const kindField = document.getElementById('kind');
            const classField = document.getElementById('class');
            const classFieldWrapper = document.querySelector('.field[data-field="class"]');
            function updateClassField() {
                const kindValue = kindField.value;
                if (kindValue === 'robots' || kindValue === 'abilities') {
                    classField.disabled = false;
                    classFieldWrapper.classList.remove('disabled');
                } else {
                    classField.disabled = true;
                    classFieldWrapper.classList.add('disabled');
                    classField.selectedIndex = 0;
                }
            }
            kindField.addEventListener('change', updateClassField);
            updateClassField(); // Call once on page load to set the initial state
            // Add some functionality to the reset button
            const resetButton = document.querySelector('.sprite-filters button[type="reset"]');
            resetButton.addEventListener('click', function(event) {
                event.preventDefault();
                const formElement = event.target.closest('form');
                const formAction = formElement.getAttribute('action');
                window.location.href = formAction;
            });
        });
    </script>
    <?
    $scripts_markup .= trim(ob_get_clean());

    // Define a function for collecting indexes based on kind and class
    function get_mmrpg_index($kind, $class = null) {
        global $hidden;
        if ($kind === 'players') { return rpg_player::get_index($hidden, false); }
        elseif ($kind === 'robots') { return rpg_robot::get_index($hidden, false, $class); }
        elseif ($kind === 'abilities') { return rpg_ability::get_index($hidden, false, $class); }
        elseif ($kind === 'items') { return rpg_item::get_index($hidden, false); }
        elseif ($kind === 'fields') { return rpg_field::get_index($hidden, false); }
        else { return array(); }
    }

    // Collect the index given the provided type and class, sheet error if empty
    $mmrpg_index = get_mmrpg_index($kind, $class);
    if (empty($mmrpg_index)) { die("Error: The mmrpg index was empty for kind:{$kind} and class:{$class}"); }

    // Calculate how many rows are required
    $sprite_image_size = 40;
    $sprite_image_size2 = $sprite_image_size;
    $sprite_container_size = 80;
    $sprite_container_size2 = $sprite_container_size;
    $sheet_wrapper_border = 0;
    $num_sprites_per_object = 12;
    $temp_frame_string = '';
    if ($kind === 'players'){ $temp_frame_string = 'mug/'.MMRPG_SETTINGS_PLAYER_FRAMEINDEX; }
    if ($kind === 'robots'){ $temp_frame_string = 'mug/'.MMRPG_SETTINGS_ROBOT_FRAMEINDEX; }
    if ($kind === 'abilities'){ $temp_frame_string = 'icon/'.MMRPG_SETTINGS_ABILITY_FRAMEINDEX; }
    if ($kind === 'items'){ $temp_frame_string = 'icon/base'; }
    if (!empty($temp_frame_string)){ $num_sprites_per_object = count(array_filter(explode('/', $temp_frame_string), function($f){ return $f !== '*'; })); }

    // Manually define the filenames for the fields index images (they're not the same as everything else)
    $field_image_index = array();
    $field_image_index[] = array('name' => 'battle-field_avatar.png', 'size' => array('width' => 100, 'height' => 100));
    $field_image_index[] = array('name' => 'battle-field_foreground_base.png', 'size' => array('width' => 1124, 'height' => 248));
    $field_image_index[] = array('name' => 'battle-field_preview.png', 'size' => array('width' => 1124, 'height' => 248));
    $field_image_index[] = array('name' => 'battle-field_background_base.gif', 'size' => array('width' => 1124, 'height' => 248));
    //$field_image_index[] = array('name' => 'battle-field_background_base.png', 'size' => array('width' => 1124, 'height' => null));
    if ($kind === 'fields'){
        $sprite_image_size = 1124;
        $sprite_image_size2 = 248;
        $sprite_container_size = 1124;
        $sprite_container_size2 = 248;
        $num_sprites_per_object = count($field_image_index);
    }

    // If number of sprites is less than columns count, adjust
    if ($num_sprites_per_object < $max_columns){ $max_columns = $num_sprites_per_object; }

    // Define constants to be used in sheet logic
    $required_rows = !empty($required_rows) ? $required_rows : ceil($num_sprites_per_object / $max_columns);

    $grid_sheet_inner_width = ($sprite_container_size * $max_columns) + ($sprite_padding * ($max_columns - 1));
    $grid_sheet_element_width = $grid_sheet_inner_width + ($sheet_gutters * 2);
    $sheet_wrapper_width = $grid_sheet_inner_width + ($sheet_wrapper_border * 2);

    $grid_sheet_inner_height = ($sprite_container_size2 * $required_rows) + ($sprite_padding * ($required_rows - 1));
    $grid_sheet_element_height = $grid_sheet_inner_height + ($sheet_gutters * 2);
    $sheet_wrapper_height = $grid_sheet_inner_height + ($sheet_wrapper_border * 2);


    // Generate the markup for the sprite wrappers
    ob_start();
    ?>
    <div class="sprite-sheets">
        <?php
        $object_key = -1;
        foreach ($mmrpg_index as $object_token => $object_info) {
            if (empty($object_info[$kind_singular . '_flag_complete'])){ continue; }
            $object_key++;
            if ($object_key < $key_min) { continue; }
            if ($object_key > $key_max) { break; }
            $src_path = MMRPG_CONFIG_ROOTURL . 'images/' . $kind . '/' . $object_token . '/';
            if ($kind === 'fields') { $src_size = $sprite_image_size; }
            else { $src_size = (int)($object_info[$kind_singular . '_image_size']); }
            $src_size_x = $src_size . 'x' . $src_size;
            echo('<div class="sheet '.$kind.'" id="'.$object_token.'">' . PHP_EOL); // Add sheet container
                echo('<div class="wrapper">' . PHP_EOL);
                for ($i = 0; $i < $num_sprites_per_object; $i++) {
                    $current_column = $i % $max_columns;
                    $current_row = floor($i / $max_columns);
                    $col_class = $current_column % 2 === 0 ? 'even-col' : 'odd-col';
                    $row_class = $current_row % 2 === 0 ? 'even-row' : 'odd-row';
                    $img_style = '';
                    if ($kind === 'fields') {
                        $src_img = $field_image_index[$i]['name'];
                        $img_style = '';
                    } else {
                        $icon_kind = $kind === 'players' || $kind === 'robots' ? 'mug' : 'icon';
                        $src_img = ($i === 0 ? $icon_kind : 'sprite') . '_' . $direction . '_' . $src_size_x . '.png';
                        $shift_value = $i > 0 ? (($i + -1) * $src_size) : 0;
                        $img_style .= 'margin-left: ' . (-1 * $shift_value) . 'px';
                    }
                    echo('<div class="sprite ' . $row_class . ' ' . $col_class . '" data-size="' . $src_size . '" data-index="' . $i . '">' . PHP_EOL);
                        echo('<div class="wrap"><img src="' . $src_path . $src_img . '?' . MMRPG_CONFIG_CACHE_DATE .  '" style="' . $img_style . '" /></div>' . PHP_EOL);
                    echo('</div>' . PHP_EOL);
                }
                echo('</div>' . PHP_EOL);
            echo('</div>' . PHP_EOL);
        }
        ?>
    </div>
    <?
    $html_markup .= trim(ob_get_clean());
    ob_start();
    ?>
    <style type="text/css">
        .sprite-sheets {
            display: grid;
            grid-template-columns: repeat(auto-fill, <?= $grid_sheet_element_width ?>px);
            grid-gap: <?= $sheet_spacing ?>px;
        }
        .sprite-sheets .sheet {
            position: relative;
            width: <?= $grid_sheet_element_width ?>px;
            height: <?= $grid_sheet_element_height ?>px;
            background-color: #292929;
        }
        .sprite-sheets .sheet > .wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: grid;
            grid-template-columns: repeat(<?= $max_columns ?>, <?= $sprite_container_size ?>px);
            grid-template-rows: repeat(<?= $required_rows ?>, <?= $sprite_container_size2 ?>px);
            grid-gap: <?= $sprite_padding ?>px;
            width: <?= $sheet_wrapper_width ?>px;
            height: <?= $sheet_wrapper_height ?>px;
            border: 0 solid transparent;
            background-color: transparent;
        }
        .sprite-sheets .wrapper .sprite {
            position: relative;
            width: <?= $sprite_container_size ?>px;
            height: <?= $sprite_container_size2 ?>px;
            background-color: transparent;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            overflow: hidden;
        }
        .sprite-sheets .wrapper .sprite:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 9px, #000 1px, transparent),
                repeating-linear-gradient(90deg, transparent, transparent 9px, #000 1px, transparent);
            background-size: 10px 10px;
            opacity: 0.2;
        }
        .sprite-sheets .wrapper .sprite .wrap {
            display: block;
            overflow: hidden;
            position: absolute;
            z-index: 2;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        .sprite-sheets .wrapper .sprite img {
            display: block;
            margin: 0;
            image-rendering: -webkit-optimize-contrast; /* Safari */
            image-rendering: crisp-edges;               /* Firefox */
            image-rendering: pixelated;                 /* Chrome, Edge, and Opera */
        }
        .sprite-sheets .wrapper .sprite[data-index="0"] .wrap {
            left: 50%;
            bottom: 50%;
            transform: translate(-50%, 50%);
        }
        .sprite-sheets .wrapper .sprite[data-size="<?= $sprite_image_size  ?>"] .wrap {
            width: <?= $sprite_image_size ?>px;
            height: <?= $sprite_image_size2 ?>px;
        }
        .sprite-sheets .wrapper .sprite[data-size="<?= ($sprite_image_size * 2) ?>"] .wrap {
            width: <?= ($sprite_image_size * 2) ?>px;
            height: <?= ($sprite_image_size2 * 2) ?>px;
        }
        .sprite-sheets .wrapper .sprite[data-size="<?= ($sprite_image_size * 3) ?>"] .wrap {
            width: <?= ($sprite_image_size * 3) ?>px;
            height: <?= ($sprite_image_size2 * 3) ?>px;
        }
        .sprite-sheets .wrapper .sprite.even-row.even-col { background-color: #343332; }
        .sprite-sheets .wrapper .sprite.even-row.odd-col { background-color: #444444; }
        .sprite-sheets .wrapper .sprite.odd-row.even-col { background-color: #444444; }
        .sprite-sheets .wrapper .sprite.odd-row.odd-col { background-color: #343332; }

        .sprite-sheets .sheet.fields .sprite[data-index="0"] .wrap {
            width: <?= $field_image_index[0]['size']['width'] ?>px;
            height: <?= $field_image_index[0]['size']['height'] ?>px;
        }

    </style>
    <?
    $styles_markup .= trim(ob_get_clean());
    ob_start();
    ?>
    <script type="text/javascript">
        // nothing yet...
    </script>
    <?
    $scripts_markup .= trim(ob_get_clean());

    echo($styles_markup.PHP_EOL);
    echo($scripts_markup.PHP_EOL);
    echo($html_markup.PHP_EOL);
    exit();

    ?>

<? $this_page_markup .= ob_get_clean(); ?>

