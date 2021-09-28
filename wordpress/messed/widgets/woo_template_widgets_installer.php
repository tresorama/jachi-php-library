<?php

// import Woo_Template => Widgets
include_once 'woo_template_widget_product_filter_attr.php';
include_once 'woo_template_widget_multifilter_apply_reset.php';

// import Woo_Template => Widgets Area - Product List Filters
include_once 'woo_template_widget_area_product_list_filters_installer.php';


/**
 * Register widget area
 */
function woo_template_register_custom_widget_area_product_list_filters() {
    
    register_sidebar( 
        [ 
            'name'          => __( 'Product List Filters', 'textdomain' ),
            'id'            => 'product-list-filters',
            'description'   => __( 'Add here widget used for filtering products grid.', 'textdomain' ),
            'before_widget' => '<div id="product-list-filters-area__%1$s" class="product-list-filters-area__widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>'
        ]
    );

}
add_action( 'widgets_init', 'woo_template_register_custom_widget_area_product_list_filters' );

/**
 * Register widgets .
 */
function woo_template_register_custom_widgets() {
    
    register_widget( 'woo_template_product_attribute_filter' );
    register_widget( 'woo_template_multifilter_apply_reset' );
    
}
add_action( 'widgets_init', 'woo_template_register_custom_widgets' );


?>