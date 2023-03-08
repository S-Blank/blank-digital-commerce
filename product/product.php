<?php
add_action('init', function () {
    register_post_type('bd_product',
        array(
            'labels' => array(
                'name' => 'Products',
                'singular_name' => 'Product',
                'search_items' => 'Search Products',
                'all_items' => 'All Products',
                'parent_item' => 'Parent Product',
                'parent_item_colon' => 'Parent Product:',
                'edit_item' => 'Edit Product',
                'update_item' => 'Update Product',
                'add_new_item' => 'Add New Product',
                'new_item_name' => 'New Product Name',
                'menu_name' => 'Product',
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'products'),
            'show_in_rest' => false,
            'menu_icon' => 'dashicons-cart',
            'supports' => array('title', 'editor'),
        )
    );

    register_taxonomy('bd_product_cat', ['bd_product'], array(
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Product Categories',
            'singular_name' => 'Product Category',
            'search_items' => 'Search Product Categories',
            'all_items' => 'All Product Categories',
            'parent_item' => 'Parent Product Category',
            'parent_item_colon' => 'Parent Product Category:',
            'edit_item' => 'Edit Product Category',
            'update_item' => 'Update Product Category',
            'add_new_item' => 'Add New Product Category',
            'new_item_name' => 'New Product Category Name',
            'menu_name' => 'Product Category',
        ),
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'product_category'],
    ));
});

// This function disables the block editor for the bd_product post type
add_filter('use_block_editor_for_post_type', function ($current_status, $post_type) {

    // Set the post type to be disabled
    $disabled_post_type = 'bd_product';

    // If the current post type is the disabled post type, set $current_status to false
    if ($post_type === $disabled_post_type) {
        $current_status = false;
    }

    // Return the modified status
    return $current_status;

}, 10, 2);

// Meta Fields product post type
add_filter('simple_register_metaboxes', function ($metaboxes) {
    // Price
    $metaboxes[] = array(
        'id' => 'bd_product_price',
        'name' => 'Pricing',
        'post_type' => array('bd_product'),
        'fields' => array(
            array(
                'id' => 'bd_product_price',
                'label' => 'Price',
                'type' => 'number',
                'short_description' => '€',
                'default' => 0,
                'step' => 0.01,
                'class' => array('regular-text')
            ),
            array(
                'id' => 'bd_product_comparison_price',
                'label' => 'Comparison Price',
                'type' => 'number',
                'short_description' => '€',
                'description' => 'To show a reduced price, enter a value higher than your price.',
                'default' => 0,
                'step' => 0.01,
                'maxlength' => 'regular',
                'class' => array('regular-text')
            ),
            array(
                'id' => 'bd_product_vat',
                'label' => 'VAT',
                'type' => 'select',
                'placeholder' => 'Select...',
                'options' => array(
                    '19%' => '19 %',
                    '7%' => '7 %',
                    '0%' => '0 %',
                ),
            ),
            array(
                'id' => 'bd_product_show_base_price',
                'label' => 'Show base price for this product',
                'description' => 'The basic price must be indicated for all products sold by weight, volume, length or area.',
                'type' => 'checkbox',
                'default' => false
            ),
            array(
                'id' => 'bd_product_unit',
                'label' => 'Product Unit',
                'default' => 0,
                'type' => 'select',
                'placeholder' => 'Select...',
                'options' => array(
                    'ml' => 'Milliliter',
                    'cl' => 'Centiliter',
                    'l' => 'Liter',
                    'm3' => 'Cubic meters',
                    'mg' => 'Milligram',
                    'g' => 'Gram',
                    'kg' => 'Kilogram',
                    'mm' => 'Millimeter',
                    'cm' => 'Centimeter',
                    'm' => 'Meter',
                    'm2' => 'Square meter'
                ),
                'show_if' => array(
                    'id' => 'bd_product_show_base_price',
                    'value' => 'yes',
                ),
            ),
            array(
                'id' => 'bd_product_dimensions',
                'label' => 'Dimensions whole product',
                'default' => 0,
                'type' => 'number',
                'step' => 0.01,
                'show_if' => array(
                    'id' => 'bd_product_show_base_price',
                    'value' => 'yes',
                ),
            ),
            array(
                'id' => 'bd_product_base',
                'label' => 'Base',
                'default' => 0,
                'type' => 'number',
                'step' => 0.01,
                'show_if' => array(
                    'id' => 'bd_product_show_base_price',
                    'value' => 'yes',
                ),
            ),
        )
    );

    // Gallery
    $metaboxes[] = array(
        'id' => 'bd_product_media',
        'name' => 'Media',
        'post_type' => array('bd_product'),
        'fields' => array(
            array(
                'id' => 'bd_product_image',
                'label' => 'Product Image',
                'type' => 'image',
                'description' => 'The product image is the default image for the product. It is displayed as the first image in the product gallery and serves as the thumbnail for the product preview.'
            ),
            array(
                'id' => 'bd_product_gallery',
                'label' => 'Product Gallery',
                'type' => 'gallery',
                'description' => 'To select multiple images it is necessary to hold down the Shift key, the Cmd key (Mac) or the Ctrl key (Windows).'
            )
        )
    );

    // Inventory and Shipping
    $metaboxes[] = array(
        'id' => 'bd_product_inventory',
        'name' => 'Inventory',
        'post_type' => array('bd_product'),
        'fields' => array(
            array(
                'id' => 'bd_product_sku',
                'label' => 'SKU (Stock Keeping Unit)',
                'type' => 'text',
            ),
            array(
                'id' => 'bd_product_is_virtual',
                'label' => 'Is virtual product?',
                'type' => 'checkbox',
                'default' => false
            ),
            array(
                'id' => 'bd_product_shipping_weight',
                'label' => 'Shipping weight',
                'type' => 'number',
                'short_description' => 'kg',
                'default' => 0,
                'step' => 0.01,
                'show_if' => array(
                    'id' => 'bd_product_is_virtual',
                    'value' => 'no',
                ),
            ),
            array(
                'id' => 'bd_product_stock',
                'label' => 'Stock',
                'default' => 0,
                'type' => 'number',
                'min' => 0,
                'max' => 10000,
                'step' => 2,
                'show_if' => array(
                    'id' => 'bd_product_is_virtual',
                    'value' => 'no',
                ),
            ),
        )
    );

    return $metaboxes;
});

// Meta fields product category taxonomy
add_filter('simple_register_taxonomy_settings', function ($settings) {

    $settings[] = array(
        'id' => 'bd_product_cat_fields',
        'taxonomy' => array('bd_product_cat'),
        'fields' => array(
            array(
                'id' => 'bd_product_cat_image',
                'label' => 'Product Category Image',
                'description' => 'The image is displayed in the menu in some themes.',
                'type' => 'image'
            )
        )
    );

    return $settings;

});

