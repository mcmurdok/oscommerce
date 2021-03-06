<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $categories_array = array();

  foreach ($osC_CategoryTree->getArray() as $value) {
    $categories_array[] = array('id' => $value['id'],
                                'text' => $value['title']);
  }
?>

<h1><?php echo osc_link_object(osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule()), $osC_Template->getPageTitle()); ?></h1>

<?php
  if ( $osC_MessageStack->size($osC_Template->getModule()) > 0 ) {
    echo $osC_MessageStack->get($osC_Template->getModule());
  }
?>

<form name="search" action="<?php echo osc_href_link_admin(FILENAME_DEFAULT); ?>" method="get"><?php echo osc_draw_hidden_field($osC_Template->getModule()); ?>

<p align="right">

<?php
  echo $osC_Language->get('operation_title_search') . ' ' . osc_draw_input_field('search') . osc_draw_pull_down_menu('cPath', array_merge(array(array('id' => '', 'text' => $osC_Language->get('top_category'))), $categories_array)) . '<input type="submit" value="GO" class="operationButton" />' .
       '<input type="button" value="' . $osC_Language->get('button_insert') . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search'] . '&action=save') . '\';" class="infoBoxButton" />';
?>

</p>

</form>

<?php
  if ( $current_category_id > 0 ) {
    $osC_CategoryTree->setBreadcrumbUsage(false);

    $in_categories = array($current_category_id);

    foreach($osC_CategoryTree->getArray($current_category_id) as $category) {
      $in_categories[] = $category['id'];
    }

    $Qproducts = $osC_Database->query('select distinct p.products_id, pd.products_name, p.products_quantity, p.products_price, p.products_date_added, p.products_last_modified, p.products_status, p.has_children from :table_products p, :table_products_description pd, :table_products_to_categories p2c where p.parent_id = 0 and p.products_id = pd.products_id and pd.language_id = :language_id and p.products_id = p2c.products_id and p2c.categories_id in (:categories_id)');
    $Qproducts->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
    $Qproducts->bindRaw(':categories_id', implode(',', $in_categories));
  } else {
    $Qproducts = $osC_Database->query('select p.products_id, pd.products_name, p.products_quantity, p.products_price, p.products_date_added, p.products_last_modified, p.products_status, p.has_children from :table_products p, :table_products_description pd where p.parent_id = 0 and p.products_id = pd.products_id and pd.language_id = :language_id');
  }

  if ( !empty($_GET['search']) ) {
    $Qproducts->appendQuery('and pd.products_name like :products_name');
    $Qproducts->bindValue(':products_name', '%' . $_GET['search'] . '%');
  }

  $Qproducts->appendQuery('and p.parent_id = :parent_id order by pd.products_name');
  $Qproducts->bindTable(':table_products', TABLE_PRODUCTS);
  $Qproducts->bindTable(':table_products_description', TABLE_PRODUCTS_DESCRIPTION);
  $Qproducts->bindInt(':language_id', $osC_Language->getID());
  $Qproducts->bindInt(':parent_id', 0);
  $Qproducts->setBatchLimit($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, (!empty($_GET['search']) ? 'distinct p.products_id' : ''));
  $Qproducts->execute();
?>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td><?php echo $Qproducts->getBatchTotalPages($osC_Language->get('batch_results_number_of_entries')); ?></td>
    <td align="right"><?php echo $Qproducts->getBatchPageLinks('page', $osC_Template->getModule() . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search'], false); ?></td>
  </tr>
</table>

<form name="batch" action="#" method="post">

<table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTable">
  <thead>
    <tr>
      <th><?php echo $osC_Language->get('table_heading_products'); ?></th>
      <th><?php echo $osC_Language->get('table_heading_price'); ?></th>
      <th><?php echo $osC_Language->get('table_heading_quantity'); ?></th>
      <th width="150"><?php echo $osC_Language->get('table_heading_action'); ?></th>
      <th align="center" width="20"><?php echo osc_draw_checkbox_field('batchFlag', null, null, 'onclick="flagCheckboxes(this);"'); ?></th>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <th align="right" colspan="4"><?php echo '<input type="image" src="' . osc_icon_raw('copy.png') . '" title="' . $osC_Language->get('icon_copy') . '" onclick="document.batch.action=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search'] . '&action=batchCopy') . '\';" />&nbsp;<input type="image" src="' . osc_icon_raw('trash.png') . '" title="' . $osC_Language->get('icon_trash') . '" onclick="document.batch.action=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search'] . '&action=batchDelete') . '\';" />'; ?></th>
      <th align="center" width="20"><?php echo osc_draw_checkbox_field('batchFlag', null, null, 'onclick="flagCheckboxes(this);"'); ?></th>
    </tr>
  </tfoot>
  <tbody>

<?php
  while ($Qproducts->next()) {
    $price = $osC_Currencies->format($Qproducts->value('products_price'));

    if ( $Qproducts->valueInt('has_children') === 1 ) {
      $Qvariants = $osC_Database->query('select min(products_price) as min_price, max(products_price) as max_price, sum(products_quantity) as total_quantity, min(products_status) as products_status from :table_products where parent_id = :parent_id');
      $Qvariants->bindTable(':table_products', TABLE_PRODUCTS);
      $Qvariants->bindInt(':parent_id', $Qproducts->valueInt('products_id'));
      $Qvariants->execute();

      $product_active = ($Qvariants->valueInt('products_status') === 1);
      $quantity = '(' . $Qvariants->valueInt('total_quantity') . ')';

      $price = $osC_Currencies->format($Qvariants->value('min_price'));

      if ( $Qvariants->value('min_price') != $Qvariants->value('max_price') ) {
        $price .= '&nbsp;-&nbsp;' . $osC_Currencies->format($Qvariants->value('max_price'));
      }
    } else {
      $product_active = ($Qproducts->valueInt('products_status') === 1);
      $quantity = $Qproducts->valueInt('products_quantity');
    }
?>

    <tr onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);" <?php echo (($product_active !== true) ? 'class="deactivatedRow"' : '') ?>>
      <td><?php echo osc_link_object(osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search'] . '&pID=' . $Qproducts->valueInt('products_id') . '&action=preview'), (($Qproducts->valueInt('has_children') === 1) ? osc_icon('attach.png') : osc_icon('products.png')) . '&nbsp;' . $Qproducts->value('products_name')); ?></td>
      <td align="right"><?php echo $price; ?></td>
      <td align="right"><?php echo $quantity; ?></td>
      <td align="right">

<?php
    echo osc_link_object(osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search'] . '&pID=' . $Qproducts->valueInt('products_id') . '&action=save'), osc_icon('edit.png')) . '&nbsp;' .
         osc_link_object(osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search'] . '&pID=' . $Qproducts->valueInt('products_id') . '&action=copy'), osc_icon('copy.png')) . '&nbsp;' .
         osc_link_object(osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search'] . '&pID=' . $Qproducts->valueInt('products_id') . '&action=delete'), osc_icon('trash.png'));
?>

      </td>
      <td align="center"><?php echo osc_draw_checkbox_field('batch[]', $Qproducts->valueInt('products_id'), null, 'id="batch' . $Qproducts->valueInt('products_id') . '"'); ?></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

</form>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td style="opacity: 0.5; filter: alpha(opacity=50);"><?php echo '<b>' . $osC_Language->get('table_action_legend') . '</b> ' . osc_icon('edit.png') . '&nbsp;' . $osC_Language->get('icon_edit') . '&nbsp;&nbsp;' . osc_icon('copy.png') . '&nbsp;' . $osC_Language->get('icon_copy') . '&nbsp;&nbsp;' . osc_icon('trash.png') . '&nbsp;' . $osC_Language->get('icon_trash'); ?></td>
    <td align="right"><?php echo $Qproducts->getBatchPagesPullDownMenu('page', $osC_Template->getModule() . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search']); ?></td>
  </tr>
</table>
