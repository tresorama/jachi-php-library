<?php

define( 'WT_CUSTOM_CHECKOUT', true);


class WT_CUSTOM_CHECKOUT_PROCESS {

  protected $ajax_endpoints = [
    'shipping_info_update' => 'checkout_shipping_info_submit',
    'billing_info_update' => 'checkout_billing_info_submit',
  ];
  
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
 
  public function init() {
    //$this->initialize_shortcode();
    if ( defined( 'WT_CUSTOM_CHECKOUT') && WT_CUSTOM_CHECKOUT === true ) {
      $this->initialize_hooks();
      $this->initialize_ajax_assets();
      $this->initialize_ajax();
      $this->initialize_assets();
    }
  }

  public function initialize_shortcode() {
    add_shortcode( 'checkout_steps', [ $this, 'shortcode_custom_checkout_page' ]);   
  }
  public function initialize_hooks() {
    // tell to maybe custom checkout to render the custom checkout
    add_filter( 'woo_template_custom_checkout_template_part', [ $this, 'return_template_part_path' ] );   
    // after user logged via ajax ...
    add_filter( 'woo_template_ajax_user_login_after_login', [ $this, 'maybe_manage_cart_after_ajax_login' ]);
    // after user registered via ajx ...
    add_filter( 'woo_template_ajax_user_create_user_after_create_user', [ $this, 'maybe_manage_cart_after_ajax_create_user' ]);
    // after user updated shipping info via ajx ...
    add_filter( 'woo_template_ajax_checkout_shipping_info_submit_after_update_meta', [ $this, 'maybe_manage_cart_after_ajax_checkout_shipping_info_submitted' ]);
    // after user updated billing info via ajx ...
    add_filter( 'woo_template_ajax_checkout_billing_info_submit_after_update_meta', [ $this, 'maybe_manage_cart_after_ajax_checkout_billing_info_submitted' ]);
  } 
  public function initialize_ajax_assets() {
    add_filter( 'woo_template_all_frontend_scripts_filter', [ $this, 'add_ajax_scripts' ] );
    add_action( 'wp_footer', [ $this, 'print_js_params' ] );
  }
  public function initialize_assets() {
    add_filter( 'woo_template_all_frontend_scripts_filter', [ $this, 'add_scripts' ] );
  }
  public function initialize_ajax() {
    $ep = $this->ajax_endpoints;
    
    add_action( 'wc_ajax_' . $ep['shipping_info_update'],        [ $this, 'checkout_shipping_info_submit' ] );
    add_action( 'wc_ajax_nopriv_' . $ep['shipping_info_update'], [ $this, 'checkout_shipping_info_submit' ] );
    
    add_action( 'wc_ajax_' . $ep['billing_info_update'],        [ $this, 'checkout_billing_info_submit' ] );
    add_action( 'wc_ajax_nopriv_' . $ep['billing_info_update'], [ $this, 'checkout_billing_info_submit' ] );
  }

  /* =================================================== 
        AJAX ASSETS
  =================================================== */
  
  public function add_ajax_scripts( $scripts ) {
    
    // checkout script
    $handle = 'checkout_step';		
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/ajax_assets/checkout_steps.js';		
    
    $scripts[$handle] = array(
      'src'               => $src,
      'src_need_prefix'   => true,
      'auto_load'         => true,
      'deps'              => array('jquery'),
    );
    
    // paypal plugin fixes
    $handle = 'wt_ppec_fixes';		
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/ajax_assets/wt_ppec_fixes.js';		
    
    $scripts[$handle] = array(
      'src'               => $src,
      'src_need_prefix'   => true,
      'auto_load'         => true,
      'deps'              => array('jquery'),
    );
    
    return $scripts;

  }
  public function print_js_params() {
    
    $params = array(
      'wc_ajax_url'     => WC_AJAX::get_endpoint( '%%endpoint%%' ),
      'endpoints'       => $this->ajax_endpoints,
    );
    
    ob_start();?>
    <script>
      window.checkout_step_params = <?php woo_template_print_array_as_js_object( $params ); ?>
    </script>
    <?php echo ob_get_clean();
  }
  /* =================================================== 
        ASSETS
  =================================================== */
  
  public function add_scripts( $scripts ) {
    
    // checkout script
    $handle = 'checkout_validation';		
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/checkout_validation.js';		
    
    $scripts[$handle] = array(
      'src'               => $src,
      'src_need_prefix'   => true,
      'auto_load'         => true,
    );
        
    return $scripts;

  }

  /* =================================================== 
        AJAX
  =================================================== */
  
  public function checkout_shipping_info_submit() {

    if ( ! isset( $_POST['shipping_info'] )) {
      return;
    }

    // get user id to update
    $user_id = wp_get_current_user()->ID;

    // prepare data received from shipping info form
    $first_name = !empty( $_POST['first_name'] )  ? $_POST['first_name']  : false;
    $last_name  = !empty( $_POST['last_name'] )   ? $_POST['last_name']   : false;
    $country    = !empty( $_POST['country'] )     ? $_POST['country']     : false;
    $state      = !empty( $_POST['state'] )       ? $_POST['state']       : false;
    $city       = !empty( $_POST['city'] )        ? $_POST['city']        : false;
    $postcode   = !empty( $_POST['postcode'] )    ? $_POST['postcode']    : false;
    $address    = !empty( $_POST['address'] )     ? $_POST['address']     : false;
    $co         = !empty( $_POST['co'] )          ? $_POST['co']          : false;
    $tel        = !empty( $_POST['tel'] )         ? $_POST['tel']         : false;

    $shipping = [
      'shipping_first_name'   => $first_name,
      'shipping_last_name'    => $last_name,
      'shipping_country'      => $country,
      'shipping_state'        => $state,
      'shipping_city'         => $city,
      'shipping_postcode'     => $postcode,
      'shipping_address_1'    => $address,
    ];
    
    $billing = [
      'billing_first_name'  => $first_name,
      'billing_last_name'   => $last_name,
      'billing_country'     => $country,
      'billing_state'       => $state,
      'billing_city'        => $city,
      'billing_postcode'    => $postcode,
      'billing_address_1'   => $address,
      'billing_phone'       => $tel,
    ];

    // update shipping metas
    woo_template_update_customer_shipping_fields( $user_id, $shipping );
    
    // billing address already exists ???
    //$has_saved_billing = woo_template_customer_has_a_billing_address( $user_id );    
    
    // update billing metas, if user has not already a saved one billing address...
    //$has_saved_billing ? null : woo_template_update_customer_billing_fields( $user_id, $billing );
    
    // set user again
    wp_set_current_user( $user_id );
    
    $fragments = apply_filters( 'woo_template_ajax_checkout_shipping_info_submit_after_update_meta', array() );
    
    // get new user data, updated
    $user_data = woo_template_get_all_user_info( $user_id );

    // create response data
    $data = [
      'success'   => true,
      'user'      => $user_data,
      'fragments' => $fragments,
    ];

    $response = [
      'data' => $data,
    ];
      
    echo json_encode( $response );
    wp_die();

  }
  public function checkout_billing_info_submit() {

    if ( ! isset( $_POST['billing_info'] )) {
      return;
    }

    // get user id to update
    $user_id = wp_get_current_user()->ID;

    // prepare data received from shipping info form
    $first_name = !empty( $_POST['first_name'] )  ? $_POST['first_name']  : false;
    $last_name  = !empty( $_POST['last_name'] )   ? $_POST['last_name']   : false;
    $country    = !empty( $_POST['country'] )     ? $_POST['country']     : false;
    $state      = !empty( $_POST['state'] )       ? $_POST['state']       : false;
    $city       = !empty( $_POST['city'] )        ? $_POST['city']        : false;
    $postcode   = !empty( $_POST['postcode'] )    ? $_POST['postcode']    : false;
    $address    = !empty( $_POST['address'] )     ? $_POST['address']     : false;
    $co         = !empty( $_POST['co'] )          ? $_POST['co']          : false;
    $tel        = !empty( $_POST['tel'] )         ? $_POST['tel']         : false;
    
    $billing = [
      'billing_first_name'  => $first_name,
      'billing_last_name'   => $last_name,
      'billing_country'     => $country,
      'billing_state'       => $state,
      'billing_city'        => $city,
      'billing_postcode'    => $postcode,
      'billing_address_1'   => $address,
      'billing_phone'       => $tel,
    ];

    // update billing metas
    woo_template_update_customer_billing_fields( $user_id, $billing );
    
    // set user again
    wp_set_current_user( $user_id );
    
    $fragments = apply_filters( 'woo_template_ajax_checkout_billing_info_submit_after_update_meta', array() );
    
    // get new user data, updated
    $user_data = woo_template_get_all_user_info( $user_id );

    // create response data
    $data = [
      'success'   => true,
      'user'      => $user_data,
      'fragments' => $fragments,
    ];

    $response = [
      'data' => $data,
    ];
      
    echo json_encode( $response );
    wp_die();

  }

  /* =================================================== 
        FRAGMENTS
  =================================================== */

  public function get_order_review_fragments() {
    $fragments_to_add = [];
    $path = woo_template_get_partial_path_of_local_dir(__DIR__) . '/templates';
    ob_start();
    get_template_part( $path . '/order-review-short-cart-subtotal');
    $fragments_to_add['.cart-subtotal'] = ob_get_clean();
    ob_start();
    get_template_part( $path . '/order-review-short-shipping-subtotal');
    $fragments_to_add['.shipping-subtotal'] = ob_get_clean();
    ob_start();
    get_template_part( $path . '/order-review-short-order-total');
    $fragments_to_add['.order-total'] = ob_get_clean();
    return $fragments_to_add;
  }

  /* =================================================== 
        MANAGE COSTUMER - CART - SESSION 
  =================================================== */
  
  public function update_wc_session() {
    // WC()->session = new WC_Session_Handler();
    // WC()->session->init();
    WC()->customer = new WC_Customer( get_current_user_id(), true );
    // WC()->cart = new WC_Cart();

    // calculate shipping based on yet saved shipping data
    $this->maybe_calculate_shipping();

    // Also calc totals before we check items so subtotals etc are up to date.
    WC()->cart->calculate_totals();
    
    // Check cart items are valid.
		do_action( 'woocommerce_check_cart_items' );

		// Calc totals.
		WC()->cart->calculate_totals();

    // wc_load_cart();
  }

  public function maybe_calculate_shipping() {

    // get user shipping data
    $user_data = woo_template_get_all_user_info( wp_get_current_user() );
    $shipping = $user_data['shipping'];   

    // if user has saved a complete shipping address, we can calclate shipping
    switch (true) {
      case empty( $shipping['shipping_country'] ):
      case empty( $shipping['shipping_state'] ):
      case empty( $shipping['shipping_postcode'] ):
      case empty( $shipping['shipping_city'] ):
        $all_field_have_value = false;
        break;      
      default:
        $all_field_have_value = true;
        break;
    }

    if ( !$all_field_have_value ) {
      return; // early abort, we dont have enough data
    }

    $_POST['calc_shipping_country']   = $shipping['shipping_country'];
    $_POST['calc_shipping_state']     = $shipping['shipping_state'];
    $_POST['calc_shipping_postcode']  = $shipping['shipping_postcode'];
    $_POST['calc_shipping_city']      = $shipping['shipping_city'];

    // calculate shipping
    WC_Shortcode_Cart::calculate_shipping();

  }

  /* =================================================== 
        HOOKS
  =================================================== */
  
  public function return_template_part_path() {
    return woo_template_get_partial_path_of_local_dir(__DIR__) . '/templates/checkout_step';
  }
  
  public function maybe_manage_cart_after_ajax_login( $fragments ) {
    if ( !empty( $_POST['checkout_step']) ) {      
      // update wc cart - costumer - session , totals ...
      $this->update_wc_session();
      // save current cart as persistent, overriding any previous cart
      if ( function_exists( 'WT_CART_MANAGER')) {
        WT_CART_MANAGER()->save_cart_session_as_persistent();
      }
      // get fragments
      $fragments = array_merge( $fragments, $this->get_order_review_fragments() );
    }
    return $fragments;
  }
  
  public function maybe_manage_cart_after_ajax_create_user( $fragments ) {
    if ( !empty( $_POST['checkout_step']) ) {      
      // update wc cart - costumer - session , totals ...
      $this->update_wc_session();
      // save current cart as persistent
      if ( function_exists( 'WT_CART_MANAGER')) {
        WT_CART_MANAGER()->save_cart_session_as_persistent();
      }
      // get fragments
      $fragments = array_merge( $fragments, $this->get_order_review_fragments() );
    }
    return $fragments;
  }

  public function maybe_manage_cart_after_ajax_checkout_shipping_info_submitted( $fragments ) {
    if ( !empty( $_POST['checkout_step']) ) {
      // update wc cart - costumer - session , totals ...
      $this->update_wc_session();
      // get fragments
      $fragments = array_merge( $fragments, $this->get_order_review_fragments() );
    }
    return $fragments;
  }
  public function maybe_manage_cart_after_ajax_checkout_billing_info_submitted( $fragments ) {
    if ( !empty( $_POST['checkout_step']) ) {
      // update wc cart - costumer - session , totals ...
      $this->update_wc_session();
      // get fragments
      $fragments = array_merge( $fragments, $this->get_order_review_fragments() );
    }
    return $fragments;
  }

  /* =================================================== 
        SHORTCODE
  =================================================== */
  
  public function shortcode_custom_checkout_page() {
    
    /* all the stuff before get_template_part is clone from default checkot shortcode of WC */
    
    // Show non-cart errors.
		do_action( 'woocommerce_before_checkout_form_cart_notices' );

		// Check cart has contents.
		if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_redirect_empty_cart', true ) ) {
			return;
		}

		// Check cart contents for errors.
		do_action( 'woocommerce_check_cart_items' );

		// Calc totals.
		WC()->cart->calculate_totals();

		// Get checkout object.
		$checkout = WC()->checkout();

		if ( empty( $_POST ) && wc_notice_count( 'error' ) > 0 ) { // WPCS: input var ok, CSRF ok.

			wc_get_template( 'checkout/cart-errors.php', array( 'checkout' => $checkout ) );
			wc_clear_notices();

    } 
    else {

			$non_js_checkout = ! empty( $_POST['woocommerce_checkout_update_totals'] ); // WPCS: input var ok, CSRF ok.

			if ( wc_notice_count( 'error' ) === 0 && $non_js_checkout ) {
				wc_add_notice( __( 'The order totals have been updated. Please confirm your order by pressing the "Place order" button at the bottom of the page.', 'woocommerce' ) );
      }     
      
			// wc_get_template( 'checkout/form-checkout.php', array( 'checkout' => $checkout ) );

      $path = woo_template_get_partial_path_of_local_dir(__DIR__) . '/templates/checkout_step';
      get_template_part_with_query_vars( $path , [ '_tpv_checkout' => $_tpv_checkout ] );

		}

  }

}

function WT_CUSTOM_CHECKOUT_PROCESS() {
  return WT_CUSTOM_CHECKOUT_PROCESS::instance();
}

?>