<?php

class WT_USER_PLEASE_REGISTER extends WT_COMPONENT {

  private static $self = 'WT_USER_PLEASE_REGISTER';

  protected static $__DIR__ = __DIR__;

  protected static $template = '/templates/please_register';

  /* =================================================== 
        INIT
  =================================================== */

  public static function init() {
    if ( woo_template_is_request( 'frontend' ) ) {
      self::initialize_assets();
    }
  }

  public static function initialize_assets() {
    add_filter( 'woo_template_all_frontend_styles_filter', [ self::$self, 'add_styles'] );
    add_filter( 'woo_template_all_frontend_scripts_filter', [ self::$self, 'add_scripts'] );
  }

  /* =================================================== 
        ASSETS
  =================================================== */
  
  public static function add_styles( $styles ) {
    
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/please_register.css';

    $styles['please_register_css'] = [
      'src'             => $src,
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    return $styles;
  }
  

  public static function add_scripts( $scripts ) {
    
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/please_register.js';
    
    $scripts['please_register_js'] = [
      'src'             => $src,
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    return $scripts;
  }
  
}

add_action( 'init', [ 'WT_USER_PLEASE_REGISTER' , 'init'] );

?>