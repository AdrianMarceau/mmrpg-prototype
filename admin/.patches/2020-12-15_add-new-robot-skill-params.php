<?php

// Collect a list of existing field names in the robots index table
$robots_table = 'mmrpg_index_robots';
$robots_table_columns = $db->table_column_list($robots_table);

// If `robot_skill_description` does not exist yet
if (!in_array('robot_skill_description', $robots_table_columns)){
    echo('- adding new column `robot_skill_description` to '.$robots_table.PHP_EOL);
    $db->query("ALTER TABLE `{$robots_table}`
      ADD `robot_skill_description` varchar(256) NOT NULL DEFAULT '' COMMENT 'Robot Skill Description'
        AFTER `robot_skill_name`
        ;");
}

// If `robot_skill_description2` does not exist yet
if (!in_array('robot_skill_description2', $robots_table_columns)){
    echo('- adding new column `robot_skill_description2` to '.$robots_table.PHP_EOL);
    $db->query("ALTER TABLE `{$robots_table}`
      ADD `robot_skill_description2` text NOT NULL COMMENT 'Robot Skill Description (Long)'
        AFTER `robot_skill_description`
        ;");
}

// If `robot_skill_parameters` does not exist yet
if (!in_array('robot_skill_parameters', $robots_table_columns)){
    echo('- adding new column `robot_skill_parameters` to '.$robots_table.PHP_EOL);
    $db->query("ALTER TABLE `{$robots_table}`
      ADD `robot_skill_parameters` text NOT NULL COMMENT 'Robot Skill Parameters'
        AFTER `robot_skill_description2`
        ;");
}

// Collect a list of existing field names in the skills index table
$skills_table = 'mmrpg_index_skills';
$skills_table_columns = $db->table_column_list($skills_table);

// If `robot_skill_parameters` does not exist yet
if (!in_array('skill_parameters', $skills_table_columns)){
    echo('- adding new column `skill_parameters` to '.$skills_table.PHP_EOL);
    $db->query("ALTER TABLE `{$skills_table}`
      ADD `skill_parameters` text NOT NULL COMMENT 'Skill Parameters'
        AFTER `skill_description2`
        ;");
}

?>
