<?php

class WT_CUSTOM_REGISTRATION {

  private $page_slug = 'registration';
  private $page_permalink = null;
  private $shortcode_name = 'custom_registration';
  
  /* =================================================== 
				INSTANCE
	=================================================== */ 
  protected static $_instance = null;
  public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
  }

  /* =================================================== 
        CONSTRUCT
  =================================================== */
  public function __construct() {
    add_action( 'init', [ $this, 'init' ] );
  }
  
  /* =================================================== 
        ACCESS MODIFIER
  =================================================== */
  public function set_register_page_permalink( $permalink ) { $this->page_permalink = $permalink; }
  public function get_register_page_permalink( ) { return $this->page_permalink;}

  /* =================================================== 
        RENDERERS
  =================================================== */
  public function render_custom_registration_page() {
    $template_path = woo_template_get_partial_path_of_local_dir(__DIR__) . '/templates/custom_registration_page';
    get_template_part( $template_path );  
  }
  public function render_register_form_in_my_account() {
    $template_path = woo_template_get_partial_path_of_local_dir(__DIR__) . '/templates/register_form_in_my_account';
    get_template_part( $template_path );  
  }
  
  /* =================================================== 
        INIT
  =================================================== */
  public function init() {
    $this->maybe_create_register_page();
    $this->set_register_page_permalink( get_page_permalink_by_slug( $this->page_slug ) );

    $this->initialize_shortcode();
    $this->initialize_hooks();
    $this->initialize_ajax_assets();
  }

  public function maybe_create_register_page() {
    $page_name    = $this->page_slug;
    $page_content = '[' . $this->shortcode_name . ']';
    woo_template_maybe_create_page( $page_name, $page_content );
  }
  public function initialize_shortcode() {
    add_shortcode( $this->shortcode_name, [ $this, 'render_custom_registration_page' ]);  
  }
  public function initialize_hooks() {
    add_filter( 'woo_template_register_page_permalink', [ $this, 'get_register_page_permalink' ] );
    add_action( 'woo_template_maybe_custom_register_form_in_my_account_html', [ $this, 'get_register_form_in_my_account_html' ] );
  }
  public function initialize_ajax_assets() {
    add_filter( 'woo_template_all_frontend_scripts_filter', [ $this, 'add_scripts' ] );
  }

  /* =================================================== 
        ASSETS
  =================================================== */
  public function add_scripts( $scripts ) {
    
    $handle = 'registration-script';
    $src    = woo_template_get_partial_path_of_local_dir(__DIR__) . '/ajax_assets/registration.js';		
    
    $scripts[$handle] = array(
      'src'               => $src,
      'src_need_prefix'   => true,
      'auto_load'         => true,
    );
    
    return $scripts;
  
  }

  /* =================================================== 
        HOOKS
  =================================================== */    
  public function get_register_form_in_my_account_html( $html ) {
    ob_start();
    $this->render_register_form_in_my_account();
    return ob_get_clean();
  }

}

function WT_CUSTOM_REGISTRATION() {
  return WT_CUSTOM_REGISTRATION::instance();
}

?>