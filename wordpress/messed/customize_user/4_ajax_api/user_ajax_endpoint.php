<?php

class WT_AJAX_ENDPOINT_USER {
  
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
    $this->initialize_validation();
	}

  public function initialize_validation() {
    $path = STYLESHEETPATH . woo_template_get_partial_path_of_local_dir(__DIR__) . '/user_ajax_validation.php';
    require_once $path;
  }
 
  
  public function init() {
    $this->initialize_ajax_assets();
    $this->initialize_ajax();
  }
  
  public function initialize_ajax_assets() {
    add_filter( 'woo_template_all_frontend_scripts_filter', [ $this, 'add_scripts' ] );
  }
  
  public function initialize_ajax() {
    add_action( 'wc_ajax_user_maybe_exists',        [ $this, 'user_maybe_exists' ] );
    add_action( 'wc_ajax_nopriv_user_maybe_exists', [ $this, 'user_maybe_exists' ] );
    
    add_action( 'wc_ajax_user_login',        [ $this, 'user_login' ] );
    add_action( 'wc_ajax_nopriv_user_login', [ $this, 'user_login' ] );
    
    add_action( 'wc_ajax_user_create_user', [ $this, 'user_create_user' ] );
    add_action( 'wc_ajax_nopriv_user_create_user', [ $this, 'user_create_user' ] );
  }

  /* =================================================== 
        ASSETS
  =================================================== */
  
  public function add_scripts( $scripts ) {
    
    $handle = 'user_ajax';
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/ajax_assets/user_ajax.js';
    
    $scripts[$handle] = array(
      'src'               => $src,
      'src_need_prefix'   => true,
      'auto_load'         => true,
      'params' => array(
        'wc_ajax_url'     => WC_AJAX::get_endpoint( '%%endpoint%%' ),
        'endpoints'       => [
          'maybe_exists'   => 'user_maybe_exists',
          'login'          => 'user_login',
          'create_user'    => 'user_create_user',
        ],
      ),
    );
    
    return $scripts;
  
  }

  /* =================================================== 
        AJAX
  =================================================== */
  public function user_maybe_exists() {
    
    if (!isset( $_POST['maybe_exists'] )) {
      return;
    }
    
    if ( isset( $_POST['email'] ) ) {
      
      $email = $_POST['email'];
      $user_id = email_exists( $email );
      
      if ( false === $user_id ) {
        $user_id = username_exists( $email );
      }
      
      $data = [
        'exists' => false === $user_id ? false : true,
        'email' => $email,
      ];
    
    }
    
    $response = [
      'data' => $data ? $data : [],
    ];
    
    echo json_encode($response);
    wp_die();
    
  }

  public function user_login() {
    
    if ( !isset( $_POST['login'] )) {
      return;
    }

    if ( isset($_POST['email']) && isset($_POST['password']) ) {
      
      $error = false;
      
      $email    = $_POST['email'];
      $password = $_POST['password'];
      $user_obj = get_user_by_email( $email );
      
      if ( is_wp_error( $user_obj )) {
        // error - no user exists with given email
        $error = $user_obj;
      }
      else {      
        
        // lets try to login
        $creds = array(
          'user_login'    => $user_obj->to_array()['user_login'],
          'user_password' => $password,
          'remember'      => true
        );
        $user_obj = wp_signon( $creds, is_ssl() );
                
        // login succeded ?
        if ( is_wp_error( $user_obj ) ) {
          // error - incorrect password
          $error = $user_obj;
        }
        else {     
          // login OK
          $user_id = $user_obj->to_array()['ID'];

          // set current user
          wp_set_current_user( $user_id );

          // maybe additional things do after login succeded
          $fragments = apply_filters( 'woo_template_ajax_user_login_after_login', array() );
       
          // maybe redirect to some page
          $redirect = ! empty( $_POST['redirect'] ) ? wp_unslash( $_POST['redirect'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

          // response data
          $data = [
            'success'   => true,
            'redirect'  => $redirect,
            'user'      => woo_template_get_all_user_info( $user_id ),
            'fragments' => $fragments,
          ];
        }
      }
    }


    if ( $error ) {
      // something failed
      $data = [
        'success'   => false,
        'messages'  => $error->errors,
      ];
    }

    $response = [
      'data' => $data,
    ];

    echo json_encode( $response );
    wp_die();

  }

  public function user_create_user() {

    if (!isset( $_POST['create_user'] )) {
      return;
    }


    // prepare data received from registration form
    $email      = $_POST['email']      ? $_POST['email'] : false;
    $password   = $_POST['password']   ? $_POST['password'] : false;
    $first_name = $_POST['first_name'] ? $_POST['first_name'] : false;
    $last_name  = $_POST['last_name']  ? $_POST['last_name'] : false;
    $birth      = $_POST['birth']      ? $_POST['birth'] : false;
    $sex        = $_POST['sex']        ? $_POST['sex'] : false;


    // create optional data of the user to pass to "wp_insert_user()"...
    $insert_user_args = [
      'first_name'  => $first_name,
      'last_name'   => $last_name,
    ];

    // create a new costumer
    $user_id = woo_template_create_new_customer( $email, '', $password, $insert_user_args );

      
    //check if there are no errors
    if ( is_wp_error( $user_id ) ) {
      $data = [
        'success'   => false,
        'messages'  => $user_id->errors,
      ];
        
    }
    else {
      
      // insert meta
      $user_meta = [
        'birth'                   => $birth,
        'sex'                     => $sex,
        // 'shipping_first_name'     => $first_name,
        // 'shipping_last_name'      => $last_name,
        // 'billing_first_name'      => $first_name,
        // 'billing_last_name'       => $last_name,
        // 'billing_email'           => $email,
      ];
  
      foreach( $user_meta as $key => $val ) {
        update_user_meta( $user_id, $key, $val );
      }


      // login user
      $creds = array(
        'user_login'    => $email,
        'user_password' => $password,
        'remember'      => true
      );
      $user_obj = wp_signon( $creds, 'porco_dio' );

      // set current user
      wp_set_current_user( $user_id );

      // maybe additional things do after login succeded
      do_action( 'woo_template_ajax_user_create_user_after_create_user' );

      //retrieve all user data
      $user_data = woo_template_get_all_user_info( $user_id );
        
      // create response data
      $data = [
        'success' => true,
        'user' => $user_data,
      ];
    
    }

    $response = [
      'data' => $data,
    ];
      
    echo json_encode( $response );
    wp_die();

  }

}

function WT_AJAX_ENDPOINT_USER() {
  return WT_AJAX_ENDPOINT_USER::instance();
}

?>