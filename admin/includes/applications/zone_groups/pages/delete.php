<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $osC_ObjectInfo = new osC_ObjectInfo(osC_ZoneGroups_Admin::getData($_GET['zID']));
?>

<h1><?php echo osc_link_object(osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule()), $osC_Template->getPageTitle()); ?></h1>

<?php
  if ( $osC_MessageStack->exists($osC_Template->getModule()) ) {
    echo $osC_MessageStack->get($osC_Template->getModule());
  }
?>

<div class="infoBoxHeading"><?php echo osc_icon('trash.png') . ' ' . $osC_ObjectInfo->getProtected('geo_zone_name'); ?></div>
<div class="infoBoxContent">
  <form name="zDelete" class="dataForm" action="<?php echo osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&zID=' . $osC_ObjectInfo->get('geo_zone_id') . '&action=delete'); ?>" method="post">

<?php
  $Qcheck = $osC_Database->query('select tax_zone_id from :table_tax_rates where tax_zone_id = :tax_zone_id limit 1');
  $Qcheck->bindTable(':table_tax_rates', TABLE_TAX_RATES);
  $Qcheck->bindInt(':tax_zone_id', $osC_ObjectInfo->getInt('geo_zone_id'));
  $Qcheck->execute();

  if ( $Qcheck->numberOfRows() > 0 ) {
?>

  <p><?php echo '<b>' . sprintf($osC_Language->get('delete_warning_group_in_use_tax_rate'), $Qcheck->numberOfRows()) . '</b>'; ?></p>

  <p align="center"><?php echo '<input type="button" value="' . $osC_Language->get('button_back') . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page']) . '\';" class="operationButton" />'; ?></p>

<?php
  } else {
    $zone_group_name = $osC_ObjectInfo->getProtected('geo_zone_name');

    if ( $osC_ObjectInfo->get('total_entries') > 0 ) {
      $zone_group_name .= ' (' . sprintf($osC_Language->get('total_entries'), $osC_ObjectInfo->getInt('total_entries')) . ')';
    }
?>

  <p><?php echo $osC_Language->get('introduction_delete_zone_group'); ?></p>

  <p><?php echo '<b>' . $zone_group_name . '</b>'; ?></p>

  <p align="center"><?php echo osc_draw_hidden_field('subaction', 'confirm') . '<input type="submit" value="' . $osC_Language->get('button_delete') . '" class="operationButton" /> <input type="button" value="' . $osC_Language->get('button_cancel') . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page']) . '\';" class="operationButton" />'; ?></p>

<?php
  }
?>

  </form>
</div>
