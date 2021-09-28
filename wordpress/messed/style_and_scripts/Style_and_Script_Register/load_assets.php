<?php

/* =================================================== 
      SCRIPTS
=================================================== */

if ( defined( 'WT_DEV_MODE' ) && WT_DEV_MODE === true ) { 
  function woo_template_this_project_add_scripts( $scripts ) {

    if (false === woo_template_is_request( 'frontend') ) {
      return $scripts;
    }

    $js_path  = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/js';

    $scripts['100_navigation'] = [
      'src'             => $js_path . '/100_navigation.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['0_get_viewport_height_available_Installazione'] = [
      'src'             => $js_path . '/0_get_viewport_height_available_Installazione.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['1_site_wrapper_top_value_Installazione'] = [
      'src'             => $js_path . '/1_site_wrapper_top_value_Installazione.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['2_site_content_real_time_width_css_variable_Installazione'] = [
      'src'             => $js_path . '/2_site_content_real_time_width_css_variable_Installazione.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['3_menu_Installazione'] = [
      'src'             => $js_path . '/3_menu_Installazione.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['4_layers_Installazione'] = [
      'src'             => $js_path . '/4_layers_Installazione.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['6_showHide_installazione'] = [
      'src'             => $js_path . '/6_showHide_installazione.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['7_hero_animate'] = [
      'src'             => $js_path . '/7_hero_animate.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['9_search_FullScript'] = [
      'src'             => $js_path . '/9_search_FullScript.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['10_module_grid_catalogue_css_var_Installazione'] = [
      'src'             => $js_path . '/10_module_grid_catalogue_css_var_Installazione.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['11_button_invert_color'] = [
      'src'             => $js_path . '/11_button_invert_color.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $scripts['12_autoGrow_TextArea'] = [
      'src'             => $js_path . '/12_autoGrow_TextArea.js',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    return $scripts;

  }
}
else {
  function woo_template_this_project_add_scripts( $scripts ) {

    if (false === woo_template_is_request( 'frontend') ) {
      return $scripts;
    }

    $base_path = '/assets/dist';
    $src = $base_path . '/bundle.js';

    $scripts['bundle_js'] = [
      'src'               => $src,
      'src_need_prefix'   => true,
      'auto_load'         => true,
    ];
    
    return $scripts;
  
  }
}

/* =================================================== 
      STYLES
=================================================== */

if ( defined( 'WT_DEV_MODE' ) && WT_DEV_MODE === true ) { 
  function woo_template_this_project_add_styles( $styles ) {

    if (false === woo_template_is_request( 'frontend') ) {
      return $styles;
    }

    $css_path  = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/css';

    $styles['0_helpers'] = [
      'src'             => $css_path . '/0_helpers.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];
    $styles['0_css_var'] = [
      'src'             => $css_path . '/0_css_var.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];
    $styles['3_elements'] = [
      'src'             => $css_path . '/3_elements.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['4_elements_woocommerce'] = [
      'src'             => $css_path . '/4_elements_woocommerce.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['7_mini_wishlist'] = [
      'src'             => $css_path . '/7_mini_wishlist.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['8_yith_wishlist'] = [
      'src'             => $css_path . '/8_yith_wishlist.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['9_font_face'] = [
      'src'             => $css_path . '/9_font_face.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];
    
    $styles['10_main'] = [
      'src'             => $css_path . '/10_main.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['11_header'] = [
      'src'             => $css_path . '/11_header.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['12_hidden_bar'] = [
      'src'             => $css_path . '/12_hidden_bar.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['13_footer'] = [
      'src'             => $css_path . '/13_footer.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];


    $styles['15_button'] = [
      'src'             => $css_path . '/15_button.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['16_archive_pages'] = [
      'src'             => $css_path . '/16_archive_pages.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['17_single_product_page-rifatto'] = [
      'src'             => $css_path . '/17_single_product_page-rifatto.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['18_search_box_twenty_twenty'] = [
      'src'             => $css_path . '/18_search_box_twenty_twenty.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['19_cart_page'] = [
      'src'             => $css_path . '/19_cart_page.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['20_tabs_opener'] = [
      'src'             => $css_path . '/20_tabs_opener.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['21_home_page_only'] = [
      'src'             => $css_path . '/21_home_page_only.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['22_fade_in_page'] = [
      'src'             => $css_path . '/22_fade_in_page.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];
    
    $styles['100_header_with_show_hide'] = [
      'src'             => $css_path . '/100_header_with_show_hide.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['101_header_with_layers'] = [
      'src'             => $css_path . '/101_header_with_layers.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    $styles['1000_COLOR_MAP'] = [
      'src'             => $css_path . '/1000_COLOR_MAP.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];
    
    $styles['1000_header_flat'] = [
      'src'             => $css_path . '/1000_header_flat.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];
    
    $styles['1000_wp_admin_fix'] = [
      'src'             => $css_path . '/1000_wp_admin_fix.css',
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    return $styles;

  }
}
else {
  function woo_template_this_project_add_styles( $styles ) {

    if (false === woo_template_is_request( 'frontend') ) {
      return $styles;
    }

    $base_path = '/assets/dist';
    $src = $base_path . '/bundle.css';

    $styles['bundle_css'] = [
      'src'               => $src,
      'src_need_prefix'   => true,
      'auto_load'         => true,
    ];
    
    return $styles;
  
  }
}

function woo_template_this_project_load_assets() {
  add_filter( 'woo_template_all_frontend_scripts_filter', 'woo_template_this_project_add_scripts' );
  add_filter( 'woo_template_all_frontend_styles_filter', 'woo_template_this_project_add_styles' );
}
add_action( 'init', 'woo_template_this_project_load_assets', 99 );


?>