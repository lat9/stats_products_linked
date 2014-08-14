<?php
// -----
// Part of the Linked Products - Categories List report plugin, provided by Vinos de Frutas Tropicales (lat9)
// Copyright (C) 2014, Vinos de Frutas Tropicales.
//
require('includes/application_top.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<style type="text/css">
<!--
table { border-collapse: collapse; }
.unknown { color: red; font-weight: bold; }
#main td, #main th { border: 1px solid black; padding: 3px; }
-->
</style>
<script type="text/javascript" src="includes/menu.js"></script>
<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onload="init();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" id="main">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MASTER_CATEGORY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LINKED_CATEGORIES; ?></td>
              </tr>
<?php
// -----
// Local class used to "cache" the names of the the categories.
//
class category_names extends base {
  var $name_list;
  function __constructor () {
    $this->name_list = array ();
    
  }
  
  function set_name ($categories_id) {
    if (!isset ($this->name_list[$categories_id])) {
      $categories = array ( $categories_id );
      zen_get_parent_categories ($categories, $categories_id);
      $categories_name = '';
      foreach ($categories as $current_category) {
        $categories_name = zen_get_category_name ($current_category, (int)$_SESSION['languages_id']) . ' :: ' . $categories_name;
        
      }
      if (strlen ($categories_name) > 4) {
        $this->name_list[$categories_id] = substr ($categories_name, 0, -4);
        
      } else {
        $this->name_list[$categories_id] = "==> Invalid categories ID ($categories_id)";
        
      }
    }
  }
  
  function get_name ($categories_id) {
    return (isset ($this->name_list[$categories_id])) ? $this->name_list[$categories_id] : '';
    
  }
  
  function get_set_name ($categories_id) {
    $this->set_name ($categories_id);
    return $this->get_name ($categories_id);
    
  }
  
  function sort_names () {
    asort ($this->name_list);
    
  }
}
// -----
// Start main processing ...
//
$type_handler_info = $db->Execute ("SELECT type_id, type_handler FROM " . TABLE_PRODUCT_TYPES);
$product_type_handlers = array ();
while (!$type_handler_info->EOF) {
  $product_type_handlers[$type_handler_info->fields['type_id']] = $type_handler_info->fields['type_handler'];
  $type_handler_info->MoveNext ();
  
}
unset ($type_handler_info);

$linked_products = array ();
$last_product_id = 0;
$category_names = new category_names;
$linked_products_info = $db->Execute ("SELECT * FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ORDER BY products_id ASC");
while (!$linked_products_info->EOF) {
  $products_id = $linked_products_info->fields['products_id'];
  if ($last_product_id != $products_id) {
    if (count ($linked_products[$last_product_id]['categories']) == 1) {
      unset ($linked_products[$last_product_id]);
      
    } elseif ($last_product_id != 0) {
      $linked_products[$last_product_id]['master_category_id'] = zen_get_products_category_id ($last_product_id);
      $linked_products[$last_product_id]['name'] = zen_get_products_name ($last_product_id);
      $linked_products[$last_product_id]['products_type'] = zen_get_products_type ($last_product_id);
      $category_names->set_name ($linked_products[$last_product_id]['master_category_id']);  //-Cache the category's name
      
    }
    $linked_products[$products_id] = array ( 'categories' => array () );
    
  }
  $linked_products[$products_id]['categories'][] = $linked_products_info->fields['categories_id'];
  
  $last_product_id = $products_id;
  $linked_products_info->MoveNext ();
  
}
unset ($linked_products_info);
if (count ($linked_products[$last_product_id]['categories']) == 1) {
  unset ($linked_products[$last_product_id]);
  
} else {
  $linked_products[$last_product_id]['master_category_id'] = zen_get_products_category_id ($last_product_id);
  $linked_products[$last_product_id]['name'] = zen_get_products_name ($last_product_id);
  $linked_products[$last_product_id]['products_type'] = zen_get_products_type ($last_product_id);
  $category_names->set_name ($linked_products[$last_product_id]['master_category_id']);  //-Cache the category's name
  
}

$category_names->sort_names ();
$categories_base_link = zen_href_link (FILENAME_CATEGORIES);
$category_name_list = $category_names->name_list;
foreach ($category_name_list as $master_category_id => $category_name) {
  foreach ($linked_products as $products_id => $product_info) {
    if ($master_category_id == $product_info['master_category_id']) {
?>
              <tr class="dataTableRow">
<?php
      if (strpos ($category_names->get_name ($master_category_id), '==>') === 0) {
?>
                <td class="dataTableContent"><span class="unknown"><?php echo $category_name; ?></span></td>
<?php
      } else {
?>
                <td class="dataTableContent"><a href="<?php echo $categories_base_link . "?cPath=$master_category_id"; ?>"><?php echo $category_name; ?></a></td>
<?php
      }
?>
                <td class="dataTableContent"><a href="<?php echo zen_href_link ($product_type_handlers[$product_info['products_type']], 'product_type=' . $product_info['products_type'] . "&amp;cPath=$master_category_id&amp;pID=$products_id&amp;action=new_product"); ?>"><?php echo $product_info['name']; ?></a></td>
                <td class="dataTableContent">
<?php
      foreach ($product_info['categories'] as $current_category) {
        if ($current_category != $master_category_id) {
          echo '<a href="' . $categories_base_link . "?cPath=$current_category" . '">' . $category_names->get_set_name ($current_category) . '</a><br />';
      
        }
      }
?>
                </td>
              </tr>
<?php
    }
  }
}
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
