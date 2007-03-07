<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  $osC_ObjectInfo = new osC_ObjectInfo(osC_AdministratorsLog::getData($_GET['lID']));
?>

<h1><?php echo osc_link_object(osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule()), $osC_Template->getPageTitle()); ?></h1>

<?php
  if ( $osC_MessageStack->size($osC_Template->getModule()) > 0 ) {
    echo $osC_MessageStack->output($osC_Template->getModule());
  }
?>
<p align="right"><?php echo '<input type="button" value="' . IMAGE_BACK . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&fm=' . $_GET['fm'] . '&fu=' . $_GET['fu']) . '\';" class="operationButton" />'; ?></p>

<div class="infoBoxHeading"><?php echo osc_icon('info.png', IMAGE_INFO) . ' ' . $osC_ObjectInfo->get('user_name') . ' &raquo; ' . $osC_ObjectInfo->get('module_action') . ' &raquo; ' . $osC_ObjectInfo->get('module') . ' &raquo; ' . $osC_ObjectInfo->get('module_id'); ?></div>
<div class="infoBoxContent">
  <p><?php echo '<b>' . TEXT_DATE . '</b> ' . date('d M Y H:i:s', $osC_ObjectInfo->get('datestamp')); ?></p>
</div>

<br />

<table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTable">
  <thead>
    <tr>
      <th><?php echo TABLE_HEADING_FIELD; ?></th>
      <th><?php echo TABLE_HEADING_OLD_VALUE; ?></th>
      <th><?php echo TABLE_HEADING_NEW_VALUE; ?></th>
    </tr>
  </thead>
  <tbody>

<?php
  $Qentries = $osC_Database->query('select action, field_key, old_value, new_value from :table_administrators_log where id = :id');
  $Qentries->bindTable(':table_administrators_log', TABLE_ADMINISTRATORS_LOG);
  $Qentries->bindInt(':id', $osC_ObjectInfo->get('id'));
  $Qentries->execute();

  while ( $Qentries->next() ) {
    switch ( $Qentries->value('action') ) {
      case 'delete':
        $bgColor = '#E23832';

        break;

      case 'insert':
        $bgColor = '#96E97A';

        break;

      default:
        $bgColor = '#FFC881';

        break;
    }
?>

    <tr>
      <td valign="top" style="background-color: <?php echo $bgColor; ?>;"><?php echo $Qentries->valueProtected('field_key'); ?></td>
      <td valign="top" style="background-color: <?php echo $bgColor; ?>;"><?php echo nl2br($Qentries->valueProtected('old_value')); ?></td>
      <td valign="top" style="background-color: <?php echo $bgColor; ?>;"><?php echo nl2br($Qentries->valueProtected('new_value')); ?></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>