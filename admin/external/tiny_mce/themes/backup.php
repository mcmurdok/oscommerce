<?php
/*
  $Id: backup.php 1076 2006-10-25 15:37:50Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/
?>

<h1><?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule()), $osC_Template->getPageTitle()); ?></h1>

<?php
  if ($osC_MessageStack->size($osC_Template->getModule()) > 0) {
    echo $osC_MessageStack->output($osC_Template->getModule());
  }
?>

<div id="infoBox_bDefault" <?php if (!empty($_GET['action'])) { echo 'style="display: none;"'; } ?>>
  <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTable">
    <thead>
      <tr>
        <th><?php echo TABLE_HEADING_TITLE; ?></th>
        <th><?php echo TABLE_HEADING_FILE_DATE; ?></th>
        <th><?php echo TABLE_HEADING_FILE_SIZE; ?></th>
        <th><?php echo TABLE_HEADING_ACTION; ?></th>
      </tr>
    </thead>
    <tbody>

<?php
  if (is_dir(DIR_FS_BACKUP) && is_writeable(DIR_FS_BACKUP)) {
    $contents = array();
    $dir = dir(DIR_FS_BACKUP);
    while ($file = $dir->read()) {
      if (!is_dir(DIR_FS_BACKUP . $file)) {
        $contents[] = $file;
      }
    }
    $dir->close();

    rsort($contents);

    for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
      $entry = $contents[$i];

      if (!isset($buInfo) && (!isset($_GET['file']) || (isset($_GET['file']) && ($_GET['file'] == $entry)))) {
        $file_array['file'] = $entry;
        $file_array['date'] = osC_DateTime::getShort(filemtime(DIR_FS_BACKUP . $entry), true);
        $file_array['size'] = number_format(filesize(DIR_FS_BACKUP . $entry)) . ' bytes';

        switch (substr($entry, -3)) {
          case 'zip':
            $file_array['compression'] = 'ZIP';
            break;

          case '.gz':
            $file_array['compression'] = 'GZIP';
            break;

          default:
            $file_array['compression'] = TEXT_NO_EXTENSION;
            break;
        }

        $buInfo = new objectInfo($file_array);
      }
?>

      <tr onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">
        <td><?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule() . '&action=download&file=' . $entry), osc_icon('save.png', ICON_FILE_DOWNLOAD) . '&nbsp;' . $entry); ?></td>
        <td><?php echo osC_DateTime::getShort(osC_DateTime::fromUnixTimestamp(filemtime(DIR_FS_BACKUP . $entry)), true); ?></td>
        <td><?php echo number_format(filesize(DIR_FS_BACKUP . $entry)); ?> bytes</td>
        <td align="right">

<?php
      if (isset($buInfo) && ($entry == $buInfo->file)) {
        echo osc_link_object('#', osc_icon('tape.png', IMAGE_RESTORE), 'onclick="toggleInfoBox(\'bRestore\');"') . '&nbsp;' .
             osc_link_object('#', osc_icon('trash.png', IMAGE_DELETE), 'onclick="toggleInfoBox(\'bDelete\');"');
      } else {
        echo osc_link_object(osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule() . '&file=' . $entry . '&action=bRestore'), osc_icon('tape.png', IMAGE_RESTORE)) . '&nbsp;' .
             osc_link_object(osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule() . '&file=' . $entry . '&action=bDelete'), osc_icon('trash.png', IMAGE_DELETE));
      }
?>

        </td>
      </tr>

<?php
    }
  }
?>

    </tbody>
  </table>

  <table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr>
      <td><?php echo TEXT_BACKUP_DIRECTORY . ' ' . DIR_FS_BACKUP; ?></td>
      <td align="right"><?php if (isset($dir)) { echo '<input type="button" value="' . IMAGE_BACKUP . '" class="infoBoxButton" onclick="toggleInfoBox(\'bBackup\');">&nbsp;<input type="button" value="' . IMAGE_RESTORE . '" class="infoBoxButton" onclick="toggleInfoBox(\'bRestoreLocal\');">'; } ?></td>
    </tr>
  </table>

<?php
  if (defined('DB_LAST_RESTORE')) {
?>

  <p><?php echo TEXT_LAST_RESTORATION . ' ' . DB_LAST_RESTORE . ' ' . osc_link_object(osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule() . '&action=forget'), TEXT_FORGET); ?></p>

<?php
  }
?>

</div>

<div id="infoBox_bBackup" <?php if ($_GET['action'] != 'bBackup') { echo 'style="display: none;"'; } ?>>
  <div class="infoBoxHeading"><?php echo osc_icon('new.png', IMAGE_INSERT) . ' ' . TEXT_INFO_HEADING_NEW_BACKUP; ?></div>
  <div class="infoBoxContent">
    <form name="bBackup" action="<?php echo osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule() . '&action=backupnow'); ?>" method="post">

    <p><?php echo TEXT_INFO_NEW_BACKUP; ?></p>

    <p>

<?php
  $compress_array = array(array('id' => 'no', 'text' => TEXT_INFO_USE_NO_COMPRESSION));

  if (file_exists(LOCAL_EXE_GZIP)) {
    $compress_array[] = array('id' => 'gzip', 'text' => TEXT_INFO_USE_GZIP);
  }

  if (file_exists(LOCAL_EXE_ZIP)) {
    $compress_array[] = array('id' => 'zip', 'text' => TEXT_INFO_USE_ZIP);
  }

  echo osc_draw_radio_field('compress', $compress_array, 'no', null, '<br />');
?>

    </p>

    <p>

<?php
  if (is_dir(DIR_FS_BACKUP) && is_writeable(DIR_FS_BACKUP)) {
    echo osc_draw_checkbox_field('download', array(array('id' => 'yes', 'text' => TEXT_INFO_DOWNLOAD_ONLY))) . '*<br /><br />*' . TEXT_INFO_BEST_THROUGH_HTTPS;
  } else {
    echo osc_draw_radio_field('download', array(array('id' => 'yes', 'text' => TEXT_INFO_DOWNLOAD_ONLY)), true) . '*<br /><br />*' . TEXT_INFO_BEST_THROUGH_HTTPS;
  }
?>

    </p>

    <p align="center"><?php echo '<input type="submit" value="' . IMAGE_BACKUP . '" class="operationButton"> <input type="button" value="' . IMAGE_CANCEL . '" onclick="toggleInfoBox(\'bDefault\');" class="operationButton">'; ?></p>

    </form>
  </div>
</div>

<div id="infoBox_bRestoreLocal" <?php if ($_GET['action'] != 'bRestoreLocal') { echo 'style="display: none;"'; } ?>>
  <div class="infoBoxHeading"><?php echo osc_icon('new.png', IMAGE_INSERT) . ' ' . TEXT_INFO_HEADING_RESTORE_LOCAL; ?></div>
  <div class="infoBoxContent">
    <form name="bRestoreLocal" action="<?php echo osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule() . '&action=restorelocalnow'); ?>" method="post" enctype="multipart/form-data">

    <p><?php echo TEXT_INFO_RESTORE_LOCAL; ?></p>

    <p><?php echo osc_draw_file_field('sql_file', true); ?></p>

    <p><?php echo TEXT_INFO_RESTORE_LOCAL_RAW_FILE; ?></p>

    <p align="center"><?php echo '<input type="submit" value="' . IMAGE_RESTORE . '" class="operationButton"> <input type="button" value="' . IMAGE_CANCEL . '" onclick="toggleInfoBox(\'bDefault\');" class="operationButton">'; ?></p>

    </form>
  </div>
</div>

<?php
  if (isset($buInfo)) {
?>

<div id="infoBox_bRestore" <?php if ($_GET['action'] != 'bRestore') { echo 'style="display: none;"'; } ?>>
  <div class="infoBoxHeading"><?php echo osc_icon('new.png', IMAGE_INSERT) . ' ' . $buInfo->file; ?></div>
  <div class="infoBoxContent">
    <p><?php echo sprintf(TEXT_INFO_RESTORE, DIR_FS_BACKUP . (($buInfo->compression != TEXT_NO_EXTENSION) ? substr($buInfo->file, 0, strrpos($buInfo->file, '.')) : $buInfo->file), ($buInfo->compression != TEXT_NO_EXTENSION) ? TEXT_INFO_UNPACK : ''); ?></p>

    <p align="center"><?php echo '<input type="button" value="' . IMAGE_RESTORE . '" class="operationButton" onclick="document.location.href=\'' . osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule() . '&file=' . $buInfo->file . '&action=restorenow') . '\';"> <input type="button" value="' . IMAGE_CANCEL . '" onclick="toggleInfoBox(\'bDefault\');" class="operationButton">'; ?></p>
  </div>
</div>

<div id="infoBox_bDelete" <?php if ($_GET['action'] != 'bDelete') { echo 'style="display: none;"'; } ?>>
  <div class="infoBoxHeading"><?php echo osc_icon('trash.png', IMAGE_DELETE) . ' ' . $buInfo->file; ?></div>
  <div class="infoBoxContent">
    <p><?php echo TEXT_DELETE_INTRO; ?></p>
    <p><?php echo '<b>' . $buInfo->file . '</b>'; ?></p>

    <p align="center"><?php echo '<input type="button" value="' . IMAGE_DELETE . '" class="operationButton" onclick="document.location.href=\'' . osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule() . '&file=' . $buInfo->file . '&action=deleteconfirm') . '\';"> <input type="button" value="' . IMAGE_CANCEL . '" onclick="toggleInfoBox(\'bDefault\');" class="operationButton">'; ?></p>
  </div>
</div>

<?php
  }
?>
