<?

// Ensure object group vars for this page are set
if (!isset($object_group_kind)){ exit('$object_group_kind was undefined!'); }
if (!isset($object_group_class)){ exit('$object_group_class was undefined!'); }
if (!isset($object_group_editor_url)){ exit('$object_group_editor_url was undefined!'); }
if (!isset($object_group_editor_name)){ exit('$object_group_editor_name was undefined!'); }

// Require sortable script for drag-n-drop
$admin_include_common_scripts[] = 'sortable';

// Collect the array of object groups from the admin class
$object_groups = cms_admin::get_object_groups_from_database($object_group_kind, $object_group_class);
$object_index = call_user_func(array('rpg_'.$object_group_kind, 'get_index'), true, true);

?>
<div class="editor groups">

    <h3 class="header type_span type_none">
        <span class="title">Edit <?= $object_group_editor_name ?></span>
    </h3>

    <? print_form_messages() ?>

    <form method="post">
        <input type="hidden" name="formaction" value="edit_groups" />
        <ul class="groups">
            <?
            // Loop through and display list items for each group
            $this_group_list = $object_groups[$object_group_class];
            $this_group_list['Template'] = array();
            $this_group_key = -1;
            foreach ($this_group_list AS $group_token => $group_data){
                $this_group_key += 1;
                $this_data_Key = 'obj-'.$this_group_key;
                $this_html_value = $group_token;
                $this_html_class = 'group';

                // Check if this is a read-only group thath cannot be moved/renamed
                $readonly = false;
                $disabled = false;

                // If this is the unsorted group, adjust the settings appropriately
                if ($group_token === 'Unsorted'){
                    $readonly = true;
                    $this_data_Key = 'unsorted';
                    $this_html_class .= ' unsorted';
                }

                // If this is the template group, adjust the settings appropriately
                if ($group_token === 'Template'){
                    $readonly = true;
                    $disabled = true;
                    $this_data_Key = '{group-key}';
                    $this_html_value = '{group-token}';
                    $this_html_class .= ' template';
                }

                // Adjust the class based on above settings
                if ($readonly){ $this_html_class .= ' readonly'; }

                // Print out the opening list item tag for this group
                echo('<li class="'.$this_html_class.'" data-key="'.$this_data_Key.'">'.PHP_EOL);
                echo('<div class="field fullsize">'.PHP_EOL);

                    // Define the base input name for this group
                    $base_input_name = 'object_groups['.$this_data_Key.']';

                    // Print out the name field for this group
                    //echo('<strong class="label">Group Name</strong>'.PHP_EOL);
                    $input_attrs = '';
                    $input_attrs .= 'class="textbox group_name" ';
                    $input_attrs .= 'type="text" ';
                    $input_attrs .= 'name="'.$base_input_name.'[group_token]" ';
                    $input_attrs .= 'value="'.$this_html_value.'"';
                    $input_attrs .= 'maxlength="32"';
                    if ($readonly){ $input_attrs .= 'readonly="readonly"'; }
                    if ($disabled){ $input_attrs .= 'disabled="disabled"'; }
                    echo('<input '.$input_attrs.' />'.PHP_EOL);

                    // Loop through and print out individual child tokens for this group
                    //echo('<strong class="label">Group Children</strong>'.PHP_EOL);
                    echo('<ul class="children">'.PHP_EOL);
                        echo('<li class="child spacer"></li>'.PHP_EOL);
                        $group_child_tokens = !empty($group_data['group_child_tokens']) ? $group_data['group_child_tokens'] : array();
                        foreach ($group_child_tokens AS $group_child_token){
                            $group_child_name = $group_child_token;
                            $group_child_number = '';
                            if (isset($object_index[$group_child_token])){
                                $group_child_info = $object_index[$group_child_token];
                                if (isset($group_child_info[$object_group_kind.'_name'])){ $group_child_name = $group_child_info[$object_group_kind.'_name']; }
                                if (isset($group_child_info[$object_group_kind.'_number'])){ $group_child_number = $group_child_info[$object_group_kind.'_number']; }
                                if (!empty($group_child_info[$object_group_kind.'_flag_hidden'])){ $group_child_visibility = '<i class="fas fa-eye-slash" title="Hidden"></i>'; }
                            }
                            echo('<li class="child group_child_token">'.PHP_EOL);
                                echo('<strong class="token">'.$group_child_name.'</strong>'.PHP_EOL);
                                if (!empty($group_child_number)){ echo('<em class="number">'.$group_child_number.'</em>'.PHP_EOL); }
                                if (!empty($group_child_visibility)){ echo('<em class="visibility">'.$group_child_visibility.'</em>'.PHP_EOL); }
                                $input_attrs = '';
                                $input_attrs .= 'class="hidden" ';
                                $input_attrs .= 'type="hidden" ';
                                $input_attrs .= 'name="'.$base_input_name.'[group_child_tokens][]" ';
                                $input_attrs .= 'value="'.$group_child_token.'" ';
                                echo('<input '.$input_attrs.' />'.PHP_EOL);
                            echo('</li>'.PHP_EOL);
                        }
                    echo('</ul>'.PHP_EOL);

                    // Print out two move buttons for going up and/or down in the list
                    echo('<a class="move-handle" data-direction="up" title="Move Up"></a>'.PHP_EOL);
                    echo('<a class="move-handle" data-direction="down" title="Move Down"></a>'.PHP_EOL);

                // Print out the closing list item tag for this group
                echo('</div>'.PHP_EOL);
                echo('</li>'.PHP_EOL);

            }
            ?>
        </ul>
        <div class="formfoot">
            <div class="buttons">
                <a class="button new">
                    <i class="fas fa-plus"></i>
                    <span class="label">Add Another Group</span>
                </a>
                <input class="button save" type="submit" value="Save Changes">
            </div>
        </div>
    </form>

</div>
<?

// DEBUG DEBUG DEBUG
//echo('<pre>$object_groups = '.print_r($object_groups, true).'</pre>');
//echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');

?>