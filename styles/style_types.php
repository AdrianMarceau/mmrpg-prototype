<?
// Loop through every type in the database to generate dynamic styles
$mmrpg_index_types = rpg_type::get_index(true, true, true);
foreach ($mmrpg_index_types AS $type_token => $type_info){
    ?>
    #mmrpg .type.<?= $type_info['type_token'] ?>,
    #mmrpg .type_<?= $type_info['type_token'] ?>,
    #mmrpg .item_type_<?= $type_info['type_token'] ?>,
    #mmrpg .ability_type_<?= $type_info['type_token'] ?>,
    #mmrpg .battle_type_<?= $type_info['type_token'] ?>,
    #mmrpg .field_type_<?= $type_info['type_token'] ?>,
    #mmrpg .player_type_<?= $type_info['type_token'] ?>,
    #mmrpg .robot_type_<?= $type_info['type_token'] ?> {
        border-color: rgb(<?= implode(',', $type_info['type_colour_dark']) ?>) !important;
        background-color: rgb(<?= implode(',', $type_info['type_colour_light']) ?>) !important;
    }
    #mmrpg .color.<?= $type_info['type_token'] ?>,
    #mmrpg .color_<?= $type_info['type_token'] ?> {
        color: rgb(<?= implode(',', $type_info['type_colour_light']) ?>) !important;
    }
    <?
    // Loop through all the types again for the dual-type ability styles
    foreach ($mmrpg_index_types AS $type2_token => $type2_info){
        ?>
        #mmrpg .type.<?= $type_info['type_token'] ?>.<?= $type2_info['type_token'] ?>,
        #mmrpg .type.<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
        #mmrpg .type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
        #mmrpg .item_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
        #mmrpg .ability_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
        #mmrpg .battle_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
        #mmrpg .field_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
        #mmrpg .player_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
        #mmrpg .robot_type_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?> {
            border-color: rgb(<?= implode(',', $type_info['type_colour_dark']) ?>) !important;
            background-color: rgb(<?= implode(',', $type_info['type_colour_light']) ?>) !important;
            background-image: -webkit-gradient(
                linear,
                left top,
                right top,
                color-stop(0, rgb(<?= implode(',', $type_info['type_colour_light']) ?>)),
                color-stop(1, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>))
            ) !important;
            background-image: -o-linear-gradient(right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
            background-image: -moz-linear-gradient(right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
            background-image: -webkit-linear-gradient(right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
            background-image: -ms-linear-gradient(right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
            background-image: linear-gradient(to right, rgb(<?= implode(',', $type_info['type_colour_light']) ?>) 0%, rgb(<?= implode(',', $type2_info['type_colour_light']) ?>) 100%) !important;
        }
        #mmrpg .color.<?= $type_info['type_token'] ?>.<?= $type2_info['type_token'] ?>,
        #mmrpg .color.<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?>,
        #mmrpg .color_<?= $type_info['type_token'] ?>_<?= $type2_info['type_token'] ?> {
            color: rgb(<?= implode(',', $type_info['type_colour_light']) ?>) !important;
        }
        <?

    }
}
?>