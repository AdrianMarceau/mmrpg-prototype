<? ob_start(); ?>
<div style="margin: 0 auto 20px; font-weight: bold;">
<a href="admin.php">Admin Panel</a> &raquo;
<a href="admin.php?action=home">Main Menu</a> &raquo;
</div>
<?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>
<ul style="margin: 0 auto 20px; font-weight: bold;">
<li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;"><a href="admin.php?action=update&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>&limit=1">Update Save Files</a></li>
<li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;"><a href="admin.php?action=import_robots&amp;limit=10">Update Robot Database</a></li>
<li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;"><a href="admin.php?action=import_abilities&amp;limit=10">Update Ability Database</a></li>
<li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;"><a href="admin.php?action=import_items&amp;limit=10">Update Item Database</a></li>
<li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;"><a href="admin.php?action=import_fields&amp;limit=10">Update Field Database</a></li>
<li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;"><a href="admin.php?action=purge&amp;date=<?=MMRPG_CONFIG_CACHE_DATE?>&limit=10">Purge Inactive Members</a></li>
<li style="padding: 5px; background-color: #F2F2F2; margin-bottom: 4px;"><a href="admin.php?action=delete_cache">Delete Cached Files</a></li>
</ul>
<? $this_page_markup .= ob_get_clean(); ?>