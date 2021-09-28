<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Frontend and Backend Styles and Script manager.
 * 
 * Dont call this class diretly. It self runs.
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 * 
 * 
 * 
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Come aggiungere un STYLE :
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 * _____________________________________________________________________________________
 *  STEP 1     Frontend
 * _____________________________________________________________________________________
 * 

add_filter( ''woo_template_all_frontend_styles_filter', function($styles) {
	
	$styles[$handle] = array(
		'src'               => '..././*.css',        (required),
		'src_need_prefix'    => true | false,         (not-required) (default false )
		'auto_load'         => true | false,         (not-required) (default false )
		'deps'              => array(),              (not-required) (default array() )
		'version'           => (string),             (not-required) (default '1.0' )
		'media'             => (string),             (not-required) (default 'all' )
		'has_rtl'           => true | false,         (not-required) (default false )
	);
	
	return $styles;

});

 * 
 * _____________________________________________________________________________________
 *  OPPURE       Backend
 * _____________________________________________________________________________________
 * 

add_filter( ''woo_template_all_backend_styles_filter', .... );

 * 
 * 
 * 
 * 
 * _____________________________________________________________________________________
 * _STEP 2_________ nel caso auto load non venisse passato , o passato = false _________
 * _____________________________________________________________________________________
 * 

add_filter( 'woo_template_accoda_frontend_styles_on_condition', function( $styles_queue ){
	
	if ( true === condition() ) {
		$styles_queue[] = $handle;
	}
	
	return $styles_queue;

} );

* 
 * _____________________________________________________________________________________
 *  OPPURE       Backend
 * _____________________________________________________________________________________
 * 

add_filter( ''woo_template_accoda_backend_styles_on_condition', .... );

* 
 * 
 * 
 * 
 * 
 * 
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Come aggiungere un SCRIPT :
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 * _____________________________________________________________________________________
 *  STEP 1     Frontend
 * _____________________________________________________________________________________
 * 

add_filter( 'woo_template_all_frontend_scripts_filter', function($scripts) {
		
		$scripts[$handle] = array(
			'src'               => '..././*.css',        (required),
			'src_need_prefix    => true | false,         (not-required) (default false )
			'auto_load'         => true | false,         (not-required) (default false )
			'deps'              => array(),              (not-required) (default array() )
			'version'           => (string),             (not-required) (default '1.0' )
			'in_footer'         => true | false,         (not-required) (default true )
			'params'            => array() | false,      (not-required) (default false )
		);

		// params example:
				'params'    => array(
					'ajax_url'                => WC()->ajax_url(),
					'wc_ajax_url'             => WC_AJAX::get_endpoint( '%%endpoint%%' ),
					'i18n_view_cart'          => esc_attr__( 'View cart', 'woocommerce' ),
					'cart_url'                => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ),
					'is_cart'                 => is_cart(),
					'cart_redirect_after_add' => get_option( 'woocommerce_cart_redirect_after_add' ),
				),

		
		return $scripts;

});

 * 
 * _____________________________________________________________________________________
 *  OPPURE       Backend
 * _____________________________________________________________________________________
 * 

add_filter( ''woo_template_all_backend_scripts_filter', .... );

* 
 * 
 * 
 * _____________________________________________________________________________________
 * _STEP 2_________ nel caso auto load non venisse passato , o passato = false _________
 * _____________________________________________________________________________________

add_filter( 'woo_template_accoda_frontend_scripts_on_condition', function( $styles_queue ){
 		
 		if ( true === condition() ) {
 			$scripts_queue[] = $handle;
 		}
 		
 		return $scripts_queue;
 
 } );

 * _____________________________________________________________________________________
 * _OPPURE________________________________ Backend ____________________________________
 * _____________________________________________________________________________________

add_filter( ''woo_template_accoda_backend_scripts_on_condition', .... );

* 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * There is also a filter for params named :
 * 		$handle. "_params"
 * 
 * 
 * 
 * 
 * 
*/

class WOO_Template_Style_And_Script_Register {
	

	private static $frontend_styles              = array();
	private static $frontend_styles_queue        = array();


	private static $frontend_scripts             = array();
	private static $frontend_scripts_queue       = array();
	private static $frontend_wp_localize_scripts = array();


	private static $backend_styles               = array();
	private static $backend_styles_queue         = array();


	private static $backend_scripts              = array();
	private static $backend_scripts_queue        = array();
	private static $backend_wp_localize_scripts  = array();


	/**
	 * INIT.
	*/
	public static function init() {

		// DISABLE DEAFULT
		add_action( 'wp_print_styles', array( __CLASS__, 'dequeue_default_stylesheets'), 100 );
		add_action( 'wp_print_scripts', array( __CLASS__, 'dequeue_default_scripts'), 100 );


		// BACKEND
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_all_backend_styles' ) );
		add_filter( 'woo_template_accoda_backend_styles_on_condition', array( __CLASS__, 'accoda_on_condition_all_default_backend_styles' ) );
		add_action( 'admin_enqueue_scripts',    array( __CLASS__, 'create_coda_all_backend_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_all_backend_styles' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_all_backend_scripts' ) );
		add_filter( 'woo_template_accoda_backend_scripts_on_condition', array( __CLASS__, 'accoda_on_condition_all_default_backend_scripts' ) );
		add_action( 'admin_enqueue_scripts',    array( __CLASS__, 'create_coda_all_backend_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_all_backend_scripts' ) );
		
		// FRONTEND
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_all_frontend_styles' ) );
		add_filter( 'woo_template_accoda_frontend_styles_on_condition', array( __CLASS__, 'accoda_on_condition_all_default_frontend_styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'create_coda_all_frontend_styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_all_frontend_styles' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_all_frontend_scripts' ) );
		add_filter( 'woo_template_accoda_frontend_scripts_on_condition', array( __CLASS__, 'accoda_on_condition_all_default_frontend_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'create_coda_all_frontend_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_all_frontend_scripts' ) );


		// EDIT OF STYLES AND SCRIPTS
		add_filter( 'script_loader_tag', array( __CLASS__, 'customize_my_scripts_id_and_defer' ), 100, 3 );
    //add_filter( 'clean_url', array( __CLASS__, 'async_scripts' ), 11, 1 );
    //add_filter( 'clean_url', array( __CLASS__, 'defer_scripts' ), 11, 1 );
		
	}




















	/**
	 * Dequeue Default Styles
	*/
	public static function dequeue_default_stylesheets() {

		$wp = array(
			'wp-block-library',
			'wc-block-style',
			// 'font-awesome'
		);
		$wc = array(
			// 'photoswipe',
			// 'photoswipe-default-skin',
			// 'select2',
			// 'woocommerce_prettyPhoto_css',
			// 'woocommerce-inline',
			'woocommerce-general',
			'woocommerce-smallscreen',
			'woocommerce-layout',
		);
		$plugin = array(
		);

		$wp     = apply_filters( 'woo_template_disable_default_wp_styles' , $wp );
		$wc     = apply_filters( 'woo_template_disable_default_wc_styles', $wc );
		$plugin = apply_filters( 'woo_template_disable_default_plugin_styles', $plugin );

		$wp     = array_unique( $wp );
		$wc     = array_unique( $wc );
		$plugin = array_unique( $plugin );


		$all_default_styles_to_disable = array_merge( $wp, $wc , $plugin );

		foreach ( $all_default_styles_to_disable as $handle ) {
			wp_dequeue_style( $handle );
		};
	
	}

	/**
	 * Dequeue Default Scripts
	*/
	public static function dequeue_default_scripts() {

		$wp = array(
		);

		$wc = array(
			// 'flexslider',
			// 'js-cookie',
			// 'jquery-blockui',
			// 'jquery-cookie',
			// 'jquery-payment',
			// 'photoswipe',
			// 'photoswipe-ui-default',
			// 'prettyPhoto',
			// 'prettyPhoto-init',
			// 'select2',
			// 'selectWoo',
			// 'wc-address-i18n',
			// 'wc-add-payment-method',
			// 'wc-cart',
			// 'wc-cart-fragments',
			// 'wc-checkout',
			// 'wc-country-select',
			// 'wc-credit-card-form',
			// 'wc-add-to-cart',
			// 'wc-add-to-cart-variation',
			// 'wc-geolocation',
			// 'wc-lost-password',
			// 'wc-password-strength-meter',
			// 'wc-single-product',
			// 'woocommerce',
			// 'zoom'
		);

		$plugin = array(
		);

		$wp     = apply_filters( 'woo_template_disable_default_wp_scripts', $wp );
		$wc     = apply_filters( 'woo_template_disable_default_wc_scripts', $wc );
		$plugin = apply_filters( 'woo_template_disable_default_plugin_scripts', $plugin );

		$wp     = array_unique( $wp );
		$wc     = array_unique( $wc );
		$plugin = array_unique( $plugin );
		
		$all_default_scripts_to_disable = array_merge( $wp, $wc, $plugin );
		
		foreach ( $all_default_scripts_to_disable as $handle ) {
			wp_dequeue_script( $handle );
		};	
	
	}

	/**
	 * Edit Styles and scripts
	*/
	public static function customize_my_scripts_id_and_defer( $tag, $handle, $src ) {
		return $tag;

		// if the script is not a custom theme one skip this process
		$is_custom = strpos( $src, get_stylesheet_directory_uri() );
		if (false === $is_custom) {
			return $tag;
		}


		global $wp_scripts;
		$groups = $wp_scripts->groups;

		

		if ( false === isset($groups[$handle])) {
			return $tag;
		}

		if ( $groups[$handle] === 0 ) { 
			$in_footer = false;
		}
		else if ( $groups[$handle] === 1 ) {
			$in_footer = true;
		}
		else {
			$in_footer = null;
		}

		if ( false === $in_footer ) { // head
			return $tag =  "\n" . '<script id="' . $handle . '" src="' . $src . '" type="text/javascript" ></script>' . "\n";
		}
		if ( true === $in_footer ) { // footer
			return $tag = '<script id="' . $handle . '" src="' . $src . '" type="text/javascript" defer ></script>';
		}
		if ( null === $in_footer ) { // footer
			return $tag;
		}
	
	}
	
	/**
   * Async scripts to improve performance
  */
  public static function async_scripts( $url ) {
		if ( strpos( $url, '#asyncload') === false ) {
			return $url;
		}
		else if ( is_admin() ) {
			return str_replace( '?#asyncload', '', $url );
		}
		else {
			return str_replace( '?#asyncload', '', $url )."' async='async";
		}
	}
	/**
   * Defer scripts to improve performance
  */
  public static function defer_scripts( $url ) {
		if ( strpos( $url, '#deferload') === false ) {
			return $url;
		}
		else if ( is_admin() ) {
			return str_replace( '#deferload', '', $url );
		}
		else {
			return str_replace( '#deferload', '', $url )."' defer='defer";
		}
	}




















	/**
	 * Caller Functions
	*/	
	public static function register_all_backend_styles() {
		self::register_all_styles( 'backend' );
	}
	public static function accoda_on_condition_all_default_backend_styles( $styles_queue = array() ) {
		$styles_queue = self::accoda_on_condition_all_default_styles( $styles_queue, 'backend' );
		return $styles_queue;
	}
	public static function create_coda_all_backend_styles() {
		self::create_coda_all_styles( 'backend' );
	}
	public static function enqueue_all_backend_styles() {
		self::enqueue_all_styles( 'backend' );
	}

	public static function register_all_backend_scripts() {
		self::register_all_scripts( 'backend' );
	}
	public static function accoda_on_condition_all_default_backend_scripts( $scripts_queue = array() ) {
		$scripts_queue = self::accoda_on_condition_all_default_scripts( $scripts_queue, 'backend' );
		return $scripts_queue;
	}
	public static function create_coda_all_backend_scripts() {
		self::create_coda_all_scripts( 'backend' );
	}
	public static function enqueue_all_backend_scripts() {
		self::enqueue_all_scripts( 'backend' );
	}

	public static function register_all_frontend_styles() {
		self::register_all_styles( 'frontend' );
	}
	public static function accoda_on_condition_all_default_frontend_styles( $styles_queue = array() ) {
		$styles_queue = self::accoda_on_condition_all_default_styles( $styles_queue, 'frontend' );
		return $styles_queue;
	}
	public static function create_coda_all_frontend_styles() {
		self::create_coda_all_styles( 'frontend' );
	}
	public static function enqueue_all_frontend_styles() {
		self::enqueue_all_styles( 'frontend' );
	}

	public static function register_all_frontend_scripts() {
		self::register_all_scripts( 'frontend' );
	}
	public static function accoda_on_condition_all_default_frontend_scripts( $scripts_queue = array() ) {
		$scripts_queue = self::accoda_on_condition_all_default_scripts( $scripts_queue, 'frontend' );
		return $scripts_queue;
	}
	public static function create_coda_all_frontend_scripts() {
		self::create_coda_all_scripts( 'frontend' );
	}
	public static function enqueue_all_frontend_scripts() {
		self::enqueue_all_scripts( 'frontend' );
	}




















	/**
	 * Get styles
	*/
	public static function get_all_styles( $type ) {

		$add_not_passed_props_for_default = function ($style) {
			$style['src_need_prefix'] = isset($style['src_need_prefix']) ? $style['src_need_prefix'] : true;
			$style['deps']      = isset($style['deps'])      ? $style['deps']      : array();
			$style['version']   = isset($style['version'])   ? $style['version']   : '1.0';
			$style['media']     = isset($style['media'])     ? $style['media']     : 'all';
			$style['has_rtl']   = isset($style['has_rtl'])   ? $style['has_rtl']   : false;
			$style['auto_load'] = isset($style['auto_load']) ? $style['auto_load'] : false;
			return $style;
		};
		$add_not_passed_props_for_custom = function ($style) {
			$style['src_need_prefix'] = isset($style['src_need_prefix']) ? $style['src_need_prefix'] : false;
			$style['deps']      = isset($style['deps'])      ? $style['deps']      : array();
			$style['version']   = isset($style['version'])   ? $style['version']   : '1.0';
			$style['media']     = isset($style['media'])     ? $style['media']     : 'all';
			$style['has_rtl']   = isset($style['has_rtl'])   ? $style['has_rtl']   : false;
			$style['auto_load'] = isset($style['auto_load']) ? $style['auto_load'] : false;
			return $style;
		};

		$get_minified_if_possible = function( $style ) {
			if (defined( 'SCRIPT_DEBUG') && SCRIPT_DEBUG === true ) {
				return $style;
			}
			if ($style['src_need_prefix'] === false) {
				return $style;
			}
			$original_src  = $style['src'];
			$potential_min = ($original_src[0] === '/') ? ltrim( $original_src, $original_src[0]) : $original_src;
			$potential_min = str_replace( '.css', '.min.css', $potential_min );
			$min_exists    = locate_template( $potential_min ) !== '';
			$final_src     = ( $min_exists ) ? $potential_min : $original_src;
			$style['src'] = $final_src;
			return $style;
		};
		
		$correct_src_path = function ($style) {
			if ( $style['src_need_prefix'] === false ) {
				return $style;
			}
			$style['src'] = self::get_asset_url( $style['src'] );
			return $style;
		};



		
		
		// check type
		if ( 'backend' !== $type && 'frontend' !== $type ) {	
			return array(); // wrong $type value passed	
		}

		
		if ( 'backend' === $type ) {			
			
			// load predifened styles...
			$default_styles = array(
				// 'woocommerce-layout'      => [
				// 	'src'     => 'assets/css/woocommerce-layout.css',
				// 	'deps'    => '',
				// 	'version' => WC_VERSION,
				// 	'media'   => 'all',
				// 	'has_rtl' => true,
				// ],
			);
					
		}
		if ( 'frontend' === $type ) {
			
			$twenty_twenty_styles = array(
				"search_box" => [
					'src'       => "assets/search-box-from-twenty-twenty/search-box.css",
					'auto_load' => true,
				],
			);
			$default_styles = array(
				// "_0_show_hide"										=> [
				// 	'src'       => "/assets/css/_0_show_hide.css",
				// 	'auto_load' => true,
				// ],
				// "1000_header_with_show_hide"      => [
				// 	'src'       => "/assets/css/1000_header_with_show_hide.css",
				// 	'auto_load' => true,
				// ],
				// "1000_header_with_layers"        	=> [
				// 	'src'       => "/assets/css/1000_header_with_layers.css",
				// 	'auto_load' => true,
				// ],
				// "_0_elements"                     => [
				// 	'src'       => "/assets/css/_0_elements.css",
				// 	'auto_load' => true,
				// ],
				// "_0_elements_woocommerce"         => [
				// 	'src'       => "/assets/css/_0_elements_woocommerce.css",
				// 	'auto_load' => true,
				// ],
				// "_0_header_info_bar"              => [
				// 	'src'       => "/assets/css/_0_header_info_bar.css",
				// 	'auto_load' => true,
				// ],
				// "0_layers"                        => [
				// 	'src'       => "/assets/css/0_layers.css",
				// 	'auto_load' => true,
				// ],
				// "0_mini_wishlist"                 => [
				// 	'src'       => "/assets/css/0_mini_wishlist.css",
				// 	'auto_load' => true,
				// ],
				// "0_yith_wishlist"                 => [
				// 	'src'       => "/assets/css/0_yith_wishlist.css",
				// 	'auto_load' => true,
				// ],
				// "1_font_face"                     => [
				// 	'src'       => "/assets/css/1_font_face.css",
				// 	'auto_load' => true,
				// ],
				// "2_icon_pure_css"                 => [
				// 	'src'       => "/assets/css/2_icon_pure_css.css",
				// 	'auto_load' => true,
				// ],
				// "4_header"                        => [
				// 	'src'       => "/assets/css/4_header.css",
				// 	'auto_load' => true,
				// ],
				// "4_header_nav"                    => [
				// 	'src'       => "/assets/css/4_header_nav.css",
				// 	'auto_load' => true,
				// ],
				// "4_footer"                        => [
				// 	'src'       => "/assets/css/4_footer.css",
				// 	'auto_load' => true,
				// ],
				// "5_main"                          => [
				// 	'src'       => "/assets/css/5_main.css",
				// 	'auto_load' => true,
				// ],
				// "6_button"                        => [
				// 	'src'       => "/assets/css/6_button.css",
				// 	'auto_load' => true,
				// ],
				// "10_archive_pages"                => [
				// 	'src'       => "/assets/css/10_archive_pages.css",
				// 	'auto_load' => true,
				// ],
				// "10_single_product_page-rifatto"	=> [
				// 	'src'       => "/assets/css/10_single_product_page-rifatto.css",
				// 	'auto_load' => true,
				// ],
				// "11_search_box_twenty_twenty"			=> [
				// 	'src'       => "/assets/css/11_search_box_twenty_twenty.css",
				// 	'auto_load' => true,
				// ],
				// "17_cart_page"										=> [
				// 	'src'       => "/assets/css/17_cart_page.css",
				// 	'auto_load' => true,
				// ],
				// "0_tabs_opener"                           => [
				// 	'src'       => "/assets/css/0_tabs_opener.css",
				// 	'auto_load' => true,
				// ],
				// "_0_home_page_only"                           => [
				// 	'src'       => "/assets/css/_0_home_page_only.css",
				// 	'auto_load' => true,
				// ],
				// "1000_fade_in_page"                           => [
				// 	'src'       => "/assets/css/1000_fade_in_page.css",
				// 	'auto_load' => true,
				// ],
			);
			$merged_one_file = array(
				"merged"                           => [
					'src'       => "/assets/css_merged/merged.css",
					'auto_load' => true,
				],
			);

			// $default_styles = $twenty_twenty_styles;
			// $default_styles = $merged_one_file;
					
		}

		// for each default styles add not passed props
		$default_styles = array_map( $add_not_passed_props_for_default, $default_styles );

		// load custom via filter, and add not passed props
		$custom_styles = apply_filters( 'woo_template_all_' . $type . '_styles_filter', array() );
		$custom_styles = array_map( $add_not_passed_props_for_custom, $custom_styles );

		// merge
		$all_styles = array_merge( $default_styles, $custom_styles );

		// use minified or non minified based on availability
		$all_styles = array_map( $get_minified_if_possible, $all_styles );
		// correct src path
		$all_styles = array_map( $correct_src_path, $all_styles );

		
		// FOR DEBUG ONLY
		$test_if_exists = function( $style ) {
			
			$original_src  		= $style['src'];
			$site_url 				= get_site_url();// get web home url ( www.my--site.it )			
			$should_be_local 	= strpos( $original_src, $site_url ) !== false;
			if (!$should_be_local) {
				$style['_EXISTS'] = 'external';
				return $style;
			}

			$test_path 				= str_replace( $site_url . '/', ABSPATH , $original_src );
			$test_is_dir 			= is_dir( $test_path );
			$test_file_exists = file_exists( $test_path );
			$exists 					= $test_is_dir === false && $test_file_exists === true;

			$style['_EXISTS'] = $exists;
			return $style;
		};
		$remove_who_exists = function( $style ) {
			return $style['_EXISTS'] === false;
		};			
		$STYLES_LOCAL_THAT_DO_NOT_EXISTS = array_filter( array_map( $test_if_exists , $all_styles ), $remove_who_exists );


		
		return $all_styles;

	
	}

	/**
	 * Get scripts
	*/
	public static function get_all_scripts( $type = '' ) {

		$add_not_passed_props_for_default = function ($script) {
			$script['src_need_prefix'] = isset($script['src_need_prefix']) ? $script['src_need_prefix'] : true;
			$script['auto_load'] = isset($script['auto_load']) ? $script['auto_load'] : false;
			$script['deps']      = isset($script['deps'])      ? $script['deps']      : array();
			$script['version']   = isset($script['version'])   ? $script['version']   : '1.0';
			$script['in_footer'] = isset($script['in_footer']) ? $script['in_footer'] : true;
			$script['params']    = isset($script['params'])    ? $script['params']    : false;
			return $script;
		};
		$add_not_passed_props_for_custom = function ($script) {
			$script['src_need_prefix'] = isset($script['src_need_prefix']) ? $script['src_need_prefix'] : false;
			$script['auto_load'] = isset($script['auto_load']) ? $script['auto_load'] : false;
			$script['deps']      = isset($script['deps'])      ? $script['deps']      : array();
			$script['version']   = isset($script['version'])   ? $script['version']   : '1.0';
			$script['in_footer'] = isset($script['in_footer']) ? $script['in_footer'] : true;
			$script['params']    = isset($script['params'])    ? $script['params']    : false;
			return $script;
		};

		$get_minified_if_possible = function( $script ) {
			if (defined( 'SCRIPT_DEBUG') && SCRIPT_DEBUG === true ) {
				return $script;
			}
			if ($script['src_need_prefix'] === false) {
				return $script;
			}
			$original_src  = $script['src'];
			$potential_min = str_replace( '.js', '.min.js', $original_src );
			$potential_min = ($potential_min[0] === '/') ? ltrim( $potential_min, $potential_min[0]) : $potential_min;
			$min_exists    = locate_template( $potential_min ) !== '';
			$final_src     = ( $min_exists ) ? $potential_min : $original_src;
			$script['src'] = $final_src;
			return $script;
		};

		$correct_src_path = function ($script) {
			if ( $script['src_need_prefix'] === false ) {
				return $script;
			}
			$script['src'] = self::get_asset_url( $script['src'] );
			return $script;
		};

		$enable_defer = function ($script) {
			if (!isset($script['disable_defer'])) {
				$script['src'] .= '#deferload';
			}
			return $script;
		};

		
		// check type
		if ( 'backend' !== $type && 'frontend' !== $type ) {	
			return array(); // wrong $type value passed	
		}
		
		
		if ( 'backend' === $type ) {			
			
			$default_scripts = array(
			);
		
		}
		
		if ( 'frontend' === $type ) {
			
			$twenty_twenty_scripts = array(
				"search-box"                => [
					'src'       => "assets/search-box-from-twenty-twenty/search-box.js",
					'auto_load' => true,
				],
			);
			$default_scripts = array(
				// '0_jachi_js_library'        => [
				// 	'src'       => 'assets/js/0_jachi-js-library.js',
				// 	'auto_load' => true,
				// ],
				// '0_jachi_js_library_complex'        => [
				// 	'src'       => 'assets/js/0_jachi_js_library_complex.js',
				// 	'auto_load' => true,
				// ],
				// '1_navigation'        => [
				// 	'src'       => 'assets/js/1_navigation.js',
				// 	'auto_load' => true,
				// ],
				// '0_tabsOpener_fullScript'        => [
				// 	'src'       => 'assets/js/0_tabsOpener_fullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_get_viewport_height_available_fullScript'        => [
				// 	'src'       => 'assets/js/0_get_viewport_height_available_fullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_get_viewport_height_available_Installazione'        => [
				// 	'src'       => 'assets/js/0_get_viewport_height_available_Installazione.js',
				// 	'auto_load' => true,
				// ],
				// '0_site_wrapper_top_value_fullScript'        => [
				// 	'src'       => 'assets/js/0_site_wrapper_top_value_fullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_site_wrapper_top_value_Installazione'        => [
				// 	'src'       => 'assets/js/0_site_wrapper_top_value_Installazione.js',
				// 	'auto_load' => true,
				// ],
				// '0_DOM_element_on_window_resize_update_css_var_fullScript'        => [
				// 	'src'       => 'assets/js/0_DOM_element_on_window_resize_update_css_var_fullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_site_content_real_time_width_css_variable_Installazione'        => [
				// 	'src'       => 'assets/js/0_site_content_real_time_width_css_variable_Installazione.js',
				// 	'auto_load' => true,
				// ],
				// '0_layers_fullScript'        => [
				// 	'src'       => 'assets/js/0_layers_fullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_layers_Installazione'        => [
				// 	'src'       => 'assets/js/0_layers_installazione.js',
				// 	'auto_load' => false,
				// ],
				// '0_showHide_fullScrips'        => [
				// 	'src'       => 'assets/js/0_showHide_fullScrips.js',
				// 	'auto_load' => true,
				// ],
				// '0_showHide_installazione'        => [
				// 	'src'       => 'assets/js/0_showHide_installazione.js',
				// 	'auto_load' => false,
				// ],
				// '0_menu_fullScript'        => [
				// 	'src'       => 'assets/js/0_menu_FullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_menu_Installazione'        => [
				// 	'src'       => 'assets/js/0_menu_Installazione.js',
				// 	'auto_load' => true,
				// ],
				// '0_hero-animate'        => [
				// 	'src'       => 'assets/js/0_hero-animate.js',
				// 	'auto_load' => true,
				// ],
				// '0_miniCartLike_fullScript'        => [
				// 	'src'       => 'assets/js/0_miniCartLike_fullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_search_fullScript'        => [
				// 	'src'       => 'assets/js/0_search_fullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_showHide_fullScrips'        => [
				// 	'src'       => 'assets/js/0_showHide_fullScrips.js',
				// 	'auto_load' => true,
				// ],
				// '0_showHide_installazione'        => [
				// 	'src'       => 'assets/js/0_showHide_installazione.js',
				// 	'auto_load' => true,
				// ],
				// 'woo-wc-add-to-cart'        => [
				// 	'src'       => 'assets/woo-wc-add-to-cart/woo-wc-add-to-cart.js',
				// 	'deps'      => array( 'jquery', 'jquery-blockui' ),
				// 	'params'    => array(
				// 		'ajax_url'                => WC()->ajax_url(),
				// 		'wc_ajax_url'             => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 		'i18n_view_cart'          => esc_attr__( 'View cart', 'woocommerce' ),
				// 		'cart_url'                => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ),
				// 		'is_cart'                 => is_cart(),
				// 		'cart_redirect_after_add' => get_option( 'woocommerce_cart_redirect_after_add' ),
				// 	),
				// ],
				// 'woo-cart-fragments'        => [
				// 	'src'       => 'assets/woo-wc-add-to-cart/woo-cart-fragments.js',
				// 	'deps'      => array( 'jquery', 'jquery-blockui' ),
				// 	'params'    => array(
				// 		'ajax_url'                => WC()->ajax_url(),
				// 		'wc_ajax_url'             => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 		'cart_hash_key'           => apply_filters( 'woocommerce_cart_hash_key', 'wc_cart_hash_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				// 		'fragment_name'           => apply_filters( 'woocommerce_cart_fragment_name', 'wc_fragments_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				// 		'request_timeout'         => 5000,
				// 	),
				// ],
				// 'woo-wishlist-fragments'        => [
				// 	'src'       => 'assets/yith-wishlist-additional-scripts/woo-wishlist-fragments.js',
				// 	'deps'      => array( 'jquery', 'jquery-blockui' ),
				// 	'auto_load' => true,
 				// 	'params'    => array(
				// 		'ajax_url'                => admin_url( 'admin-ajax.php', 'relative' ),
				// 		'wc_ajax_url'             => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 		'wishlist_hash_key'       => apply_filters( 'woo_template_wishlist_hash_key', 'woo_mini_wishlist_hash_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				// 		'fragment_name'           => apply_filters( 'woo_template_wishlist_fragment_name', 'woo_mini_wishlist_fragments_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				// 		'request_timeout'         => 5000,
				// 		'redirect_to_cart'           => get_option( 'yith_wcwl_redirect_cart' ),
				// 		'multi_wishlist'             => false,
				// 		'hide_add_button'            => apply_filters( 'yith_wcwl_hide_add_button', true ),
				// 		'enable_ajax_loading'        => 'yes' == get_option( 'yith_wcwl_ajax_enable', 'no' ),
				// 		'ajax_loader_url'            => YITH_WCWL_URL . 'assets/images/ajax-loader-alt.svg',
				// 		'remove_from_wishlist_after_add_to_cart' => get_option( 'yith_wcwl_remove_after_add_to_cart' ) == 'yes',
				// 		'is_wishlist_responsive'     => apply_filters( 'yith_wcwl_is_wishlist_responsive', true ),
				// 		'labels'                     => array(
				// 			'cookie_disabled' => __( 'We are sorry, but this feature is available only if cookies on your browser are enabled.', 'yith-woocommerce-wishlist' ),
				// 			'added_to_cart_message' => sprintf( '<div class="woocommerce-notices-wrapper"><div class="woocommerce-message" role="alert">%s</div></div>', apply_filters( 'yith_wcwl_added_to_cart_message', __( 'Product added to cart successfully', 'yith-woocommerce-wishlist' ) ) )
				// 		),
				// 		'actions'                    => array(
				// 			'add_to_wishlist_action' => 'add_to_wishlist',
				// 			'remove_from_wishlist_action' => 'remove_from_wishlist',
				// 			'reload_wishlist_and_adding_elem_action'  => 'reload_wishlist_and_adding_elem',
				// 			'load_mobile_action' => 'load_mobile',
				// 			'delete_item_action' => 'delete_item',
				// 			'save_title_action' => 'save_title',
				// 			'save_privacy_action' => 'save_privacy',
				// 			'load_fragments' => 'load_fragments'
				// 		),
				// 	),
				// ],
				// '0_module_grid_catalogue_css_var_Installazione'        => [
				// 	'src'       => 'assets/js/0_module_grid_catalogue_css_var_Installazione.js',
				// 	'auto_load' => true,
				// ],
				// '0_addClassToElement_fullScript'        => [
				// 	'src'       => 'assets/js/0_addClassToElement_fullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_button_invert_color'        => [
				// 	'src'       => 'assets/js/0_button_invert_color.js',
				// 	'auto_load' => true,
				// ],
				// '0_autoHeightElement_fullScript'        => [
				// 	'src'       => 'assets/js/0_autoHeightElement_fullScript.js',
				// 	'auto_load' => true,
				// ],
				// '0_autoGrow_TextArea'        => [
				// 	'src'       => 'assets/js/0_autoGrow_TextArea.js',
				// 	'auto_load' => true,
				// ],
			);
			
			// $default_scripts = $twenty_twenty_scripts;
		
		}

		// for each default scripts add intial part of src
		$default_scripts = array_map( $add_not_passed_props_for_default, $default_scripts );

		// load custom via filter, and correct if needed src path ...
		$custom_scripts = apply_filters( 'woo_template_all_' . $type . '_scripts_filter', array() );
		$custom_scripts = array_map( $add_not_passed_props_for_custom, $custom_scripts );

		// merge
		$all_scripts = array_merge( $default_scripts, $custom_scripts );

		// use minified or non minified based on availability
		$all_scripts = array_map( $get_minified_if_possible, $all_scripts );
		// correct src path
		$all_scripts = array_map( $correct_src_path, $all_scripts );
		// enable defer mode
		//$all_scripts = array_map( $enable_defer, $all_scripts );
		
		
		return $all_scripts;


	}









	/**
	 * Register styles.
	*/
	public static function register_all_styles( $type ) {		
		
		global $post;
		if ( woo_template_woocommerce_is_active() && ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}
		// ottieni tutti gli styles, default + custom
		$all_styles = self::get_all_styles( $type );
		// salvali nella var
		$all_styles_var        = $type.'_styles';
		self::$$all_styles_var = $all_styles;


		// per ognuno registralo come disponibile per wp..
		foreach ( $all_styles as $handle => $args ) {

			$i_must_register = true;

			if ( $type === 'frontend' ) {
				// SE SIAMO IN DEVELOPMENT
				if ( defined('WT_DEV_MODE') && WT_DEV_MODE === true ) {
					$i_must_register = true;
				}
				// SE SIAMO IN PRODUCTION
				else {			
					$i_must_register = WT_PRODUCTION_HELPER()->reg_style( [
						'handle' 	=> $handle,
						'args'		=> $args,
					]);
				}
			}
			

			if ( $i_must_register ) {
				self::register_single_style( $type, $handle, $args['src'], $args['deps'], $args['version'], $args['media'], $args['has_rtl'] );
			}

		}

	}
	
	/**
	 * Accoda on condition styles.
	*/
	public static function accoda_on_condition_all_default_styles( $styles_queue = array(), $type ) {
		
		global $post;
		if ( woo_template_woocommerce_is_active() && ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		// check type
		if ( 'backend' !== $type && 'frontend' !== $type ) {	
			return $styles_queue;// wrong $type value passed	
		}


		if ( 'backend' === $type ) {			
		}
		
		if ( 'frontend' === $type ) {
		}

		return $styles_queue;

	}
	
	/**
	 * Create Coda styles.
	*/
	public static function create_coda_all_styles( $type ) {		
		
		global $post;
		if ( woo_template_woocommerce_is_active() && ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}
		// ottieni tutti gli styles registrati, default + custom
		$all_styles_var   = $type.'_styles';
		$all_styles       = self::$$all_styles_var;
		// crea un array per la coda 
		$all_styles_queue = array();

		// per ognuno
		foreach ( $all_styles as $handle => $args ) {
			// se va caricato subito aggiungo alla coda di questa classe..
			$auto_load = $args['auto_load'];
			if ( true === $auto_load ) {
				$all_styles_queue[] = $handle;
			}
		}

		// aggiungi alla coda quelli su condizione, via filter
		$filter_name                 = 'woo_template_accoda_'. $type . '_styles_on_condition';
		$prefilter                   = $all_styles_queue;
		$filtered                    = apply_filters( $filter_name, $prefilter);
		// salva la coda nella var
		$all_styles_queue_var        = $type.'_styles_queue';
		self::$$all_styles_queue_var = $filtered;

	}

	/**
	 * Enqueue styles.
	*/
	public static function enqueue_all_styles( $type ) {
		
		global $post;
		if ( woo_template_woocommerce_is_active() && ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}
		// ottieni tutti gli styles, default + custom just for debug purpose
		$all_styles_var       = $type.'_styles';
		$all_styles           = self::$$all_styles_var;
		// ottieni coda 
		$all_styles_queue_var = $type.'_styles_queue';
		$all_styles_queue     = self::$$all_styles_queue_var;

		// scorri la coda e enqueue tramite wp i presenti...
		foreach ( $all_styles_queue as $handle ) {
			
			$i_must_enqueue = true;

			if ( $type === 'frontend' ) {
				
				// SE SIAMO IN DEVELOPMENT
				if ( defined('WT_DEV_MODE') && WT_DEV_MODE === true ) {
					$i_must_enqueue = true;
				}
				else {
					$i_must_enqueue = WT_PRODUCTION_HELPER()->enq_script( $handle );
				}
			
			}
			
			if ( $i_must_enqueue ) {				
				self::enqueue_single_style( $type, $handle );
			}

		}

	}

	
	
	



	
	/**
	 * Register scripts.
	*/
	public static function register_all_scripts( $type ) {
		
		global $post;

		// ottieni tutti gli scripts, default + custom
		$all_scripts = self::get_all_scripts( $type );
		
		// salvali nella var
		$all_scripts_var        = $type.'_scripts';
		self::$$all_scripts_var = $all_scripts;

		// per ognuno
		foreach ( $all_scripts as $handle => $args ) {
			
			// SE SIAMO IN DEVELOPMENT
			if ( defined('WT_DEV_MODE') && WT_DEV_MODE === true ) {
				$i_must_register = true;
			}
			// SE SIAMO IN PRODUCTION
			else {			
				$i_must_register = WT_PRODUCTION_HELPER()->reg_script( [
					'handle' 	=> $handle,
					'args'		=> $args,
				]);
			}

			if ( $i_must_register ) {
				// registralo come disponibile per wp..
				self::register_single_script( $type, $handle, $args['src'], $args['deps'], $args['version'], $args['in_footer'] );
				// localize if params is not false
				$params = $args['params'];
				if ( false !== $params ) {
					self::localize_single_script( $type, $handle, $params );
				}
			}
		}

	}

	/**
	 * Accoda on condition scripts.
	*/
	public static function accoda_on_condition_all_default_scripts( $scripts_queue = array(), $type ) {
		
		global $post;
		
		if ( woo_template_woocommerce_is_active() && ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}
		
		// check type
		if ( 'backend' !== $type && 'frontend' !== $type ) {	
			return $scripts_queue;// wrong $type value passed	
		}


		if ( 'backend' === $type ) {

		}
		
		if ( 'frontend' === $type ) {
			
			// if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
			// 	$scripts_queue[] = 'woo-wc-add-to-cart';
			// 	$scripts_queue[] = 'woo-cart-fragments';
			// }
			
		}

		return $scripts_queue;

	}

	/**
	 * Create Coda scripts.
	*/
	public static function create_coda_all_scripts( $type ) {		
		
		global $post;
		if ( woo_template_woocommerce_is_active() && ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}
		// ottieni tutti gli scripts registrati, default + custom
		$all_scripts_var       = $type.'_scripts';
		$all_scripts           = self::$$all_scripts_var;
		// crea un array per la coda
		$all_scripts_queue = array(); 

		// per ognuno
		foreach ( $all_scripts as $handle => $args ) {
			// se va caricato subito aggiungo alla coda di questa classe..
			$auto_load = $args['auto_load'];
			if ( true === $auto_load ) {
				$all_scripts_queue[] = $handle;
			}
		}

		// aggiungi alla coda quelli su condizione, via filter
		$prefilter                    = $all_scripts_queue;
		$filtered                     = apply_filters( 'woo_template_accoda_'. $type . '_scripts_on_condition', $prefilter);
		// salva la coda nella var
		$all_scripts_queue_var        = $type.'_scripts_queue';
		self::$$all_scripts_queue_var = $filtered;

	}

	/**
	 * Enqueue scripts.
	*/
	public static function enqueue_all_scripts( $type ) {
		
		global $post;
		if ( woo_template_woocommerce_is_active() && ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		// ottieni gli scripts, for debug purpose
		$all_scripts_var       = $type.'_scripts';
		$all_scripts           = self::$$all_scripts_var;
		// ottieni coda 
		$all_scripts_queue_var = $type.'_scripts_queue';
		$all_scripts_queue     = self::$$all_scripts_queue_var;

		// scorri la coda e enqueue tramite wp i presenti...
		foreach ( $all_scripts_queue as $handle ) {
			
			// SE SIAMO IN DEVELOPMENT
			if ( defined('WT_DEV_MODE') && WT_DEV_MODE === true ) {
				$i_must_enqueue = true;
			}
			else {
				$i_must_enqueue = WT_PRODUCTION_HELPER()->enq_script( $handle );
			}
			
			if ( $i_must_enqueue ) {				
				self::enqueue_single_script( $type, $handle );
			}
		
		}


	}






















	/**
	 * Return asset URL.
	 *
	 * @param string $path Assets path.
	 * @return string
	*/
	private static function get_asset_url( $path = '' ) {

		// prefix - deve finire con /
		$theme_base = get_stylesheet_directory_uri();
		$last_char = $theme_base[strlen( $theme_base ) - 1];
		if ( '/' !== $last_char ) {
			$theme_base .= '/';
		}
		// path - deve iniziare senza /
		if ( '/' === $path[0] ) {
			$path = ltrim( $path, $path[0]); 
		}
		
		return $theme_base . $path;
	
	}


	/**
	 * Register a style for use.
	 *
	 * @uses   wp_register_style()
	 * @param  string   $type    Type of => "backend" or "frontend".
	 * @param  string   $handle  Name of the stylesheet. Should be unique.
	 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
	 * @param  string   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
	 * @param  boolean  $has_rtl If has RTL version to load too.
	 */
	private static function register_single_style( $type = '', $handle, $path, $deps = array(), $version = '1.0', $media = 'all', $has_rtl = false ) {
		wp_register_style( $handle, $path, $deps, $version, $media );		
		if ( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 *  Enqueue a styles for use.
	 *
	 * @uses   wp_enqueue_style()
	 * @param  string   $type    Type of => "backend" or "frontend".
	 * @param  string   $handle  Name of the stylesheet. Should be unique.
	 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
	 * @param  string   $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
	 * @param  boolean  $has_rtl If has RTL version to load too.
	 */
	private static function enqueue_single_style( $type = '', $handle = '' ) {
		wp_enqueue_style( $handle );	
	}

	/**
	 * Register a script for use.
	 *
	 * @uses   wp_register_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	private static function register_single_script( $type = '', $handle, $path, $deps = array(), $version = '1.0', $in_footer = true ) {
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Enqueue a script for use.
	 *
	 * @uses   wp_enqueue_script()
	 * @param  string   $handle    Name of the script. Should be unique.
	 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param  string[] $deps      An array of registered script handles this script depends on.
	 * @param  string   $version   String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes. If version is set to false, a version number is automatically added equal to current installed WordPress version. If set to null, no version is added.
	 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
	 */
	private static function enqueue_single_script( $type = '', $handle = '' ) {
		wp_enqueue_script( $handle );
	}


	/**
	 * Localize a script once.
	 *
	 * @since 2.3.0 this needs less wp_script_is() calls due to https://core.trac.wordpress.org/ticket/28404 being added in WP 4.0.
	 * @param string $handle Script handle the data will be attached to.
	 */
	private static function localize_single_script( $type = '', $handle = '', $params = array() ) {

		$all_localized_scripts_var = $type . '_wp_localize_scripts';
		
		if ( $params === array() ) {
			return; // wrong call on this func, thers no data to localize..
		}

		$params = apply_filters( 'woo_template_get_script_data', $params, $handle );

		$name = str_replace( '-', '_', $handle ) . '_params';			
		wp_localize_script( $handle, $name, apply_filters( $name, $params ) );
		self::$$all_localized_scripts_var[] = $handle;
	
	}

	/**
	 * Return data for script handles.
	 *
	 * @param  string $handle Script handle the data will be attached to.
	 * @return array|bool
	 */
	private static function get_script_data( $type = '', $handle ) {
		
		global $wp;

		if ( 'backend' === $type ) {
			
			switch ( $handle ) {
				// case 'woocommerce':
				// 	$params = array(
				// 		'ajax_url'    => WC()->ajax_url(),
				// 		'wc_ajax_url' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 	);
				// 	break;
				// case 'wc-geolocation':
				// 	$params = array(
				// 		'wc_ajax_url'  => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 		'home_url'     => remove_query_arg( 'lang', home_url() ), // FIX for WPML compatibility.
				// 		'is_available' => ! ( is_cart() || is_account_page() || is_checkout() || is_customize_preview() ) ? '1' : '0',
				// 		'hash'         => isset( $_GET['v'] ) ? wc_clean( wp_unslash( $_GET['v'] ) ) : '', // WPCS: input var ok, CSRF ok.
				// 	);
				// 	break;
				// case 'wc-single-product':
				// 	$params = array(
				// 		'i18n_required_rating_text' => esc_attr__( 'Please select a rating', 'woocommerce' ),
				// 		'review_rating_required'    => wc_review_ratings_required() ? 'yes' : 'no',
				// 		'flexslider'                => apply_filters(
				// 			'woocommerce_single_product_carousel_options',
				// 			array(
				// 				'rtl'            => is_rtl(),
				// 				'animation'      => 'slide',
				// 				'smoothHeight'   => true,
				// 				'directionNav'   => false,
				// 				'controlNav'     => 'thumbnails',
				// 				'slideshow'      => false,
				// 				'animationSpeed' => 500,
				// 				'animationLoop'  => false, // Breaks photoswipe pagination if true.
				// 				'allowOneSlide'  => false,
				// 			)
				// 		),
				// 		'zoom_enabled'              => apply_filters( 'woocommerce_single_product_zoom_enabled', get_theme_support( 'wc-product-gallery-zoom' ) ),
				// 		'zoom_options'              => apply_filters( 'woocommerce_single_product_zoom_options', array() ),
				// 		'photoswipe_enabled'        => apply_filters( 'woocommerce_single_product_photoswipe_enabled', get_theme_support( 'wc-product-gallery-lightbox' ) ),
				// 		'photoswipe_options'        => apply_filters(
				// 			'woocommerce_single_product_photoswipe_options',
				// 			array(
				// 				'shareEl'               => false,
				// 				'closeOnScroll'         => false,
				// 				'history'               => false,
				// 				'hideAnimationDuration' => 0,
				// 				'showAnimationDuration' => 0,
				// 			)
				// 		),
				// 		'flexslider_enabled'        => apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) ),
				// 	);
				// 	break;
				// case 'wc-checkout':
				// 	$params = array(
				// 		'ajax_url'                  => WC()->ajax_url(),
				// 		'wc_ajax_url'               => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 		'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
				// 		'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
				// 		'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
				// 		'option_guest_checkout'     => get_option( 'woocommerce_enable_guest_checkout' ),
				// 		'checkout_url'              => WC_AJAX::get_endpoint( 'checkout' ),
				// 		'is_checkout'               => is_checkout() && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] ) ? 1 : 0,
				// 		'debug_mode'                => defined( 'WP_DEBUG' ) && WP_DEBUG,
				// 		'i18n_checkout_error'       => esc_attr__( 'Error processing checkout. Please try again.', 'woocommerce' ),
				// 	);
				// 	break;
				// case 'wc-address-i18n':
				// 	$params = array(
				// 		'locale'             => wp_json_encode( WC()->countries->get_country_locale() ),
				// 		'locale_fields'      => wp_json_encode( WC()->countries->get_country_locale_field_selectors() ),
				// 		'i18n_required_text' => esc_attr__( 'required', 'woocommerce' ),
				// 		'i18n_optional_text' => esc_html__( 'optional', 'woocommerce' ),
				// 	);
				// 	break;
				// case 'wc-cart':
				// 	$params = array(
				// 		'ajax_url'                     => WC()->ajax_url(),
				// 		'wc_ajax_url'                  => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 		'update_shipping_method_nonce' => wp_create_nonce( 'update-shipping-method' ),
				// 		'apply_coupon_nonce'           => wp_create_nonce( 'apply-coupon' ),
				// 		'remove_coupon_nonce'          => wp_create_nonce( 'remove-coupon' ),
				// 	);
				// 	break;
				// case 'wc-cart-fragments':
				// 	$params = array(
				// 		'ajax_url'        => WC()->ajax_url(),
				// 		'wc_ajax_url'     => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 		'cart_hash_key'   => apply_filters( 'woocommerce_cart_hash_key', 'wc_cart_hash_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				// 		'fragment_name'   => apply_filters( 'woocommerce_cart_fragment_name', 'wc_fragments_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
				// 		'request_timeout' => 5000,
				// 	);
				// 	break;
				// case 'woo-wc-add-to-cart':
				// 	$params = array(
				// 		'ajax_url'                => WC()->ajax_url(),
				// 		'wc_ajax_url'             => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 		'i18n_view_cart'          => esc_attr__( 'View cart', 'woocommerce' ),
				// 		'cart_url'                => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ),
				// 		'is_cart'                 => is_cart(),
				// 		'cart_redirect_after_add' => get_option( 'woocommerce_cart_redirect_after_add' ),
				// 	);
				// 	break;
				// case 'wc-add-to-cart-variation':
				// 	// We also need the wp.template for this script :).
				// 	wc_get_template( 'single-product/add-to-cart/variation.php' );

				// 	$params = array(
				// 		'wc_ajax_url'                      => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				// 		'i18n_no_matching_variations_text' => esc_attr__( 'Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce' ),
				// 		'i18n_make_a_selection_text'       => esc_attr__( 'Please select some product options before adding this product to your cart.', 'woocommerce' ),
				// 		'i18n_unavailable_text'            => esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ),
				// 	);
				// 	break;
				// case 'wc-country-select':
				// 	$params = array(
				// 		'countries'                 => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
				// 		'i18n_select_state_text'    => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
				// 		'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				// 		'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
				// 		'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
				// 		'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
				// 		'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
				// 		'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
				// 		'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
				// 		'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
				// 		'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
				// 		'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
				// 	);
				// 	break;
				// case 'wc-password-strength-meter':
				// 	$params = array(
				// 		'min_password_strength' => apply_filters( 'woocommerce_min_password_strength', 3 ),
				// 		'stop_checkout'         => apply_filters( 'woocommerce_enforce_password_strength_meter_on_checkout', false ),
				// 		'i18n_password_error'   => esc_attr__( 'Please enter a stronger password.', 'woocommerce' ),
				// 		'i18n_password_hint'    => esc_attr( wp_get_password_hint() ),
				// 	);
				// 	break;
				default:
					$params = false;
			}
		
		}
		else if ( 'frontend' === $type ) {

			switch ( $handle ) {
				default:
					$params = false;
			}

		}

	
	}

}



// Install it!
WOO_Template_Style_And_Script_Register::init();


// Production Helper Class
class WT_PRODUCTION_HELPER {

  public $scripts_registered 			= [];
  public $scripts_registered_ajax = [];
  public $scripts_enqueued 				= [];
	
	public $styles_registered 			= [];
  public $styles_enqueued 				= [];


	protected static $_instance = null;
	
	/* =================================================== 
				INSTANCE
	=================================================== */
  
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/* =================================================== 
				INTERNAL FUNCTIONS
	=================================================== */
	private function is_external( $src ) {
		return strpos( $src, get_site_url() ) === false;
	}
	private function is_node_modules( $src ) {
		return strpos( $src, 'node_modules' ) !== false;
	}
	private function is_ajax_assets( $src ) {
		return strpos( $src, '/ajax_assets' ) !== false;
	}
	private function is_the_production_bundle( $src ) {
		return strpos( $src, '/bundle' ) !== false;
	}

	/* =================================================== 
				API
	=================================================== */
	public function reg_script( $script_data ) {
		
		// extract data
		$handle = $script_data['handle'];
		$args 	= $script_data['args'];
		$src		= $args['src'];
				
		// save in my self
		$this->scripts_registered[$handle] = $args;

		// is an external script - IS NOT PART OF THIS APP - ???
		$is_external = $this->is_external( $src );
		
		// is a node_modules ???
		$is_node_modules = $this->is_node_modules( $src );
		
		// is an "ajax_assets" script ???
		$is_ajax_assets = $this->is_ajax_assets( $src );

		// is the "bundle" script ???
		$is_the_bundle = $this->is_the_production_bundle( $src );

		if ( $is_external ) {
			return true;
		}
		if ( $is_node_modules ) {
			return true;
		}
		if ( $is_ajax_assets ) {
			$this->scripts_registered_ajax[$handle] = $args;
			return true;
		}
		if ( $is_the_bundle ) {
			return true;
		}
		return false;
	}

	public function enq_script( $handle ) {
		$this->scripts_enqueued[] = $handle;
		return true;
	}


	public function reg_style( $style_data ) {
		
		// extract data
		$handle = $style_data['handle'];
		$args 	= $style_data['args'];
		$src		= $args['src'];
				
		// save in my self
		$this->styles_registered[$handle] = $args;

		// is an external style - IS NOT PART OF THIS APP - ???
		$is_external = $this->is_external( $src );

		// is the "bundle" style ???
		$is_the_bundle = $this->is_the_production_bundle( $src );

		if ( $is_external ) {
			return true;
		}
		if ( $is_the_bundle ) {
			return true;
		}
		return false;
	}

	public function enq_style( $handle ) {
		$this->styles_enqueued[] = $handle;
		return true;
	}


	
}

function WT_PRODUCTION_HELPER() {
	return WT_PRODUCTION_HELPER::instance();
}


?>