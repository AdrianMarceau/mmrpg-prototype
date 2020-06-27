<?
// Loop through every robot in the database to generate dynamic styles
$mmrpg_index_robots = rpg_robot::get_index();
foreach ($mmrpg_index_robots AS $robot_token => $robot_info){
    $image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : 'robot';
    $sprite_sizes = array($robot_info['robot_image_size'], ($robot_info['robot_image_size'] * 2));
    foreach ($sprite_sizes AS $sprite_size){
        $sprite_sizex = $sprite_size.'x'.$sprite_size;
        ?>
        #mmrpg .sprite_<?= $sprite_sizex ?>.sprite_left.sprite_<?= $robot_token ?> {
            background-image: url(../images/robots/<?= $image_token ?>/sprite_left_<?= $sprite_sizex ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);
        }
        #mmrpg .sprite_<?= $sprite_sizex ?>.sprite_left.sprite_<?= $robot_token ?>.sprite_shadow {
            background-image: url(../images/robots_shadows/<?= $image_token ?>/sprite_left_<?= $sprite_sizex ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);
        }
        #mmrpg .sprite_<?= $sprite_sizex ?>.sprite_right.sprite_<?= $robot_token ?> {
            background-image: url(../images/robots/<?= $image_token ?>/sprite_left_<?= $sprite_sizex ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);
        }
        #mmrpg .sprite_<?= $sprite_sizex ?>.sprite_right.sprite_<?= $robot_token ?>.sprite_shadow {
            background-image: url(../images/robots_shadows/<?= $image_token ?>/sprite_left_<?= $sprite_sizex ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);
        }
        <?
    }
}
?>