<?php

class WT_AJAX_CLIENT_CONTROLLER {

  public static $self = 'WT_AJAX_CLIENT_CONTROLLER';

  /* =================================================== 
        INIT
  =================================================== */
  public static function init() {

    if ( woo_template_is_request( 'frontend' ) ) {
      self::initialize_assets();
      WT_AJAX_CLIENT_ERROR_HANDLER::init();
    }

  }

  public static function initialize_assets() {
    add_filter( 'woo_template_all_frontend_scripts_filter',  [ self::$self, 'add_scripts'] );
  }

  /* =================================================== 
        ASSETS
  =================================================== */

  /**
   * Ajax Search - Add styles
   *
   * @param array $scripts
   * @return array $scripts
   */
  public static function add_scripts( $scripts = array() ) {

    // add client ajax controller
    $handle = 'wt_ajax_client_controller';
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/wt_ajax_client_controller.js';

    $scripts[$handle] = array(
      'src'             => $src,
      'src_need_prefix' => true,
      'auto_load'       => true,
    );

    return $scripts;

  }

}
class WT_AJAX_CLIENT_ERROR_HANDLER {

  public static $self = 'WT_AJAX_CLIENT_ERROR_HANDLER';

  private static $ajax_action = 'save_client_error';
  
  private static $table_name = "wt_client_error_store";

  private static $table_exists = false;

  /* =================================================== 
        INIT
  =================================================== */
  public static function init() {

    if ( woo_template_is_request( 'frontend' ) ) {
      self::maybe_update_db();
      self::initialize_assets();
      self::initialize_ajax();
      self::initialize_ajax_assets();
    }

  }

  public static function maybe_update_db() {
    self::maybe_create_db_error_table();
  }

  public static function initialize_assets() {
    add_filter( 'woo_template_all_frontend_scripts_filter',  [ self::$self, 'add_scripts'] );
  }
  public static function initialize_ajax_assets() {
    add_action( 'wp_footer' , [ self::$self, 'print_ajax_endpoint'] );   
  }
  public static function initialize_ajax() {
    add_action( 'wp_ajax_' . self::$ajax_action ,       [ self::$self, 'ajax_save_client_error' ] );
    add_action( 'wp_ajax_nopriv_' . self::$ajax_action, [ self::$self, 'ajax_save_client_error'] );
  }

  /* =================================================== 
        AJAX ASSETS
  =================================================== */
  public static function print_ajax_endpoint() {
    global $wp;
    $endpoint = admin_url('admin-ajax.php');
    $action   = self::$ajax_action;
    $page     = [
      'permalink'     => get_permalink(),
      'partial'       => $wp->request,
      'request_uri'   => $_SERVER['REQUEST_URI'],
    ];
    
    ob_start();?>
    <script>
      window.WT_AJAX_SAVE_CLIENT_ERROR_PARAMS = {
        url:"<?php echo esc_url( $endpoint); ?>",
        action: "<?php echo $action; ?>",
        page: {
          permalink: "<?php echo esc_url( $page['permalink'] ); ?>",
          partial: "<?php echo $page['partial']; ?>",
          request_uri: "<?php echo esc_url( $page['request_uri'] ); ?>",
        }
      };
    </script>
    <?php echo ob_get_clean();
  }

  /* =================================================== 
        INTERNAL FUNCTIONS
  =================================================== */
  private static function maybe_create_db_error_table() {

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    global $wpdb;
    
    $tablename        = $wpdb->prefix . self::$table_name;
    $charset_collate  = $wpdb->get_charset_collate();
    
    // maybe create table if not exists
    $sql = "CREATE TABLE $tablename (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      error text NOT NULL,
      date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      date_gmt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    
    //dbDelta( $sql );
    
    $table_exists = maybe_create_table( $tablename, $sql );

    self::$table_exists = $table_exists;
  
  } 
  private static function add_error_to_db($error) {

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    global $wpdb;
    
    $tablename = $wpdb->prefix . self::$table_name;
    
    // insert row
    $date_now = self::create_current_time_for_mysql();
    $row = [
      // 'error'     => maybe_serialize($error),
      'error'     => json_encode($error),
      'date'      => $date_now['date'],
      'date_gmt'  => $date_now['date_gmt'],
    ];
    $format = [
      '%s',
      '%d',
      '%d'
    ];

    $row_exists = $wpdb->insert( $tablename, $row , null );

    return $row_exists === 1;
  }
  private static function create_current_time_for_mysql() {

    $date = current_time( 'mysql' );

    // Validate the date.
    $mm         = substr( $date, 5, 2 );
    $jj         = substr( $date, 8, 2 );
    $aa         = substr( $date, 0, 4 );
    $valid_date = wp_checkdate( $mm, $jj, $aa, $date );
    if ( $valid_date ) {
      $date_gmt = get_gmt_from_date( $date );
    }
    else {
      $date = '0000-00-00 00:00:00';
      $date_gmt = '0000-00-00 00:00:00';
    }

    return [
      'date'      => $date,
      'date_gmt'  => $date_gmt,
    ];
  }

  /* =================================================== 
        AJAX
  =================================================== */
  public static function ajax_save_client_error() {
    if ( $_REQUEST['action'] === self::$ajax_action ) {
      
      $error = $_REQUEST['client_error'];

      $response = [
        'action'  => self::$ajax_action,
        'success' => false,
      ];
      
      // save to DB      
      $table_exists = self::$table_exists;
      if ( $table_exists ) {
        $error_inserted = self::add_error_to_db($error);
      }
  
      // respond to JS
      $response['success'] = $error_inserted;
      echo json_encode( $response );
      wp_die();
    
    }
  }


  /* =================================================== 
        ASSETS
  =================================================== */

  /**
   * Ajax Search - Add styles
   *
   * @param array $scripts
   * @return array $scripts
   */
  public static function add_scripts( $scripts = array() ) {

    // add client error handler
    $handle = 'wt_ajax_client_error_handler';
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/wt_ajax_client_error_handler.js';

    $scripts[$handle] = array(
      'src'             => $src,
      'src_need_prefix' => true,
      'auto_load'       => true,
    );

    return $scripts;

  }

}

add_action( 'init', [ 'WT_AJAX_CLIENT_CONTROLLER', 'init' ] );

?>