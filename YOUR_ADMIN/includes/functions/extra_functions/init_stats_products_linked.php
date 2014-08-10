<?php
// -----
// Part of the Linked Products - Categories List report plugin, provided by Vinos de Frutas Tropicales (lat9)
// Copyright (C) 2014, Vinos de Frutas Tropicales.
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
} 

//----
// If the installation supports admin-page registration (i.e. v1.5.0 and later), then
// register the New Tools tool into the admin menu structure.
//
if (function_exists('zen_register_admin_page')) {
  if (!zen_page_key_exists('statsProductsLinked')) {
    zen_register_admin_page ('statsProductsLinked', 'BOX_REPORTS_PRODUCTS_LINKED', 'FILENAME_STATS_PRODUCTS_LINKED','' ,'reports', 'Y', 20);
  }    
}