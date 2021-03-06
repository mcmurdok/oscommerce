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

  require('includes/applications/categories/classes/categories.php');
  require('includes/applications/products/classes/products.php');
  require('includes/classes/category_tree.php');

  class osC_Application_Categories extends osC_Template_Admin {

/* Protected variables */

    protected $_module = 'categories',
              $_page_title,
              $_page_contents = 'main.php';

/* Class constructor */

    function __construct() {
      global $osC_Language, $osC_MessageStack, $cPath_array, $current_category_id, $osC_CategoryTree;

      $this->_page_title = $osC_Language->get('heading_title');

      if ( !isset($_GET['action']) ) {
        $_GET['action'] = '';
      }

      if ( !isset($_GET['page']) || ( isset($_GET['page']) && !is_numeric($_GET['page']) ) ) {
        $_GET['page'] = 1;
      }

      if ( !isset($_GET['cPath']) ) {
        $_GET['cPath'] = '';
      }

      if ( !isset($_GET['search']) ) {
        $_GET['search'] = '';
      }

// check if the categories image directory exists
      if ( is_dir(realpath('../images/categories')) ) {
        if ( !is_writeable(realpath('../images/categories')) ) {
          $osC_MessageStack->add('header', sprintf($osC_Language->get('ms_error_image_directory_not_writable'), realpath('../images/categories')), 'error');
        }
      } else {
        $osC_MessageStack->add('header', sprintf($osC_Language->get('ms_error_image_directory_non_existant'), realpath('../images/categories')), 'error');
      }

// calculate category path
      if ( !empty($_GET['cPath']) ) {
        $cPath_array = osc_parse_category_path($_GET['cPath']);
        $_GET['cPath'] = implode('_', $cPath_array);
        $current_category_id = end($cPath_array);
      } else {
        $current_category_id = 0;
      }

      $osC_CategoryTree = new osC_CategoryTree_Admin();
      $osC_CategoryTree->setSpacerString('&nbsp;', 2);

      if ( !empty($_GET['action']) ) {
        switch ($_GET['action']) {
          case 'save':
            if ( isset($_GET['cID']) && is_numeric($_GET['cID']) ) {
              $this->_page_contents = 'edit.php';
            } else {
              $this->_page_contents = 'new.php';
            }

            if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
              $data = array('name' => $_POST['categories_name'],
                            'image' => isset($_FILES['categories_image']) ? $_FILES['categories_image'] : null,
                            'sort_order' => $_POST['sort_order']);

              if ( !isset($_GET['cID']) ) {
                $data['parent_id'] = $_POST['parent_id'];
              }

              $error = false;

              foreach ( $data['name'] as $key => $value ) {
                if ( empty($value) ) {
                  $osC_MessageStack->add($this->_module, sprintf($osC_Language->get('ms_warning_category_name_empty'), $osC_Language->getData($key, 'name')), 'warning');

                  $error = true;
                }
              }

              if ( $error === false ) {
                if ( osC_Categories_Admin::save((isset($_GET['cID']) && is_numeric($_GET['cID']) ? $_GET['cID'] : null), $data) ) {
                  $osC_MessageStack->add($this->_module, $osC_Language->get('ms_success_action_performed'), 'success');
                } else {
                  $osC_MessageStack->add($this->_module, $osC_Language->get('ms_error_action_not_performed'), 'error');
                }

                osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search']));
              }
            }

            break;

          case 'delete':
            $this->_page_contents = 'delete.php';

            if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
              if ( osC_Categories_Admin::delete($_GET['cID']) ) {
                $osC_MessageStack->add($this->_module, $osC_Language->get('ms_success_action_performed'), 'success');
              } else {
                $osC_MessageStack->add($this->_module, $osC_Language->get('ms_error_action_not_performed'), 'error');
              }

              osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search']));
            }

            break;

          case 'move':
            $this->_page_contents = 'move.php';

            if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
              if ( osC_Categories_Admin::move($_GET['cID'], $_POST['new_category_id']) ) {
                $osC_MessageStack->add($this->_module, $osC_Language->get('ms_success_action_performed'), 'success');
              } else {
                $osC_MessageStack->add($this->_module, ERROR_CANNOT_MOVE_CATEGORY_TO_PARENT, 'error');
              }

              osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search']));
            }

            break;

          case 'batchDelete':
            if ( isset($_POST['batch']) && is_array($_POST['batch']) && !empty($_POST['batch']) ) {
              $this->_page_contents = 'batch_delete.php';

              if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
                $error = false;

                foreach ($_POST['batch'] as $id) {
                  if ( !osC_Categories_Admin::delete($id) ) {
                    $error = true;
                    break;
                  }
                }

                if ( $error === false ) {
                  $osC_MessageStack->add($this->_module, $osC_Language->get('ms_success_action_performed'), 'success');
                } else {
                  $osC_MessageStack->add($this->_module, $osC_Language->get('ms_error_action_not_performed'), 'error');
                }

                osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search']));
              }
            }

            break;

          case 'batchMove':
            if ( isset($_POST['batch']) && is_array($_POST['batch']) && !empty($_POST['batch']) ) {
              $this->_page_contents = 'batch_move.php';

              if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
                $error = false;

                foreach ($_POST['batch'] as $id) {
                  if ( !osC_Categories_Admin::move($id, $_POST['new_category_id']) ) {
                    $error = true;
                    break;
                  }
                }

                if ( $error === false ) {
                  $osC_MessageStack->add($this->_module, $osC_Language->get('ms_success_action_performed'), 'success');
                } else {
                  $osC_MessageStack->add($this->_module, ERROR_CANNOT_MOVE_CATEGORY_TO_PARENT, 'error');
                }

                osc_redirect_admin(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&page=' . $_GET['page'] . '&cPath=' . $_GET['cPath'] . '&search=' . $_GET['search']));
              }
            }

            break;
        }
      }
    }
  }
?>
