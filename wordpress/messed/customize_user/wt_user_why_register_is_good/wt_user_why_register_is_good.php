<?php

class WT_USER_WHY_REGISTER_IS_GOOD extends WT_COMPONENT {

  private static $self = 'WT_USER_WHY_REGISTER_IS_GOOD';

  protected static $__DIR__ = __DIR__;

  protected static $template = '/templates/why_register_is_good';

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
  }

  /* =================================================== 
        ASSETS
  =================================================== */
  
  public static function add_styles( $styles ) {
    
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/why_register_is_good.css';

    $styles['why_register_is_good_css'] = [
      'src'             => $src,
      'src_need_prefix' => true,
      'auto_load'       => true,
    ];

    return $styles;
  }
  
}

add_action( 'init', [ 'WT_USER_WHY_REGISTER_IS_GOOD' , 'init'] );

?>