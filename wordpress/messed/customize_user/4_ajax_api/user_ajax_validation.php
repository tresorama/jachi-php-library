<?php

class WT_AJAX_VALIDATION_USER {
  
  private static $self = 'WT_AJAX_VALIDATION_USER';
  
  /* =================================================== 
        INIT
  =================================================== */
  
  public static function init() {
    self::initialize_assets();
  }

  public static function initialize_assets() {
    add_filter( 'woo_template_all_frontend_scripts_filter', [ self::$self, 'add_scripts' ]);
  }

  /* =================================================== 
        ASSETS
  =================================================== */
  
  public static function add_scripts( $scripts ) {
  
    $handle = 'user_validation';
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/user_validation.js';
    
    $scripts[$handle] = array(
      'src'               => $src,
      'src_need_prefix'   => true,
      'auto_load'         => true,
    );
    
    $handle = 'user_server_message';		
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/user_server_message.js';

    $scripts[$handle] = array(
      'src'               => $src,
      'src_need_prefix'   => true,
      'auto_load'         => true,
    );
    
    return $scripts;
  
  }

}

add_action( 'init', [ 'WT_AJAX_VALIDATION_USER', 'init' ] );

?>