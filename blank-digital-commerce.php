<?php

/*
Plugin Name: Blank Digital Commerce
Version: 1.0
Author: Blank Digital
Author URI: https://blank-digital.com
*/

/* Simple Meta Fields */
require_once(__DIR__ . '/lib/rudr-simple-fields/rudr-simple-fields.php');
update_option('simple_meta_fields_license', 'simon.blank@blank-digital.com');

/* Product */
require_once(__DIR__ . '/product/product.php');