<?php


add_action('template_redirect', function() {

  /**
  * rendering function
  */
  function woo_template_plfwa_render() {
    
    $_widget_area_has_widget_attached = is_active_sidebar('product-list-filters');
    
    if ( ! $_widget_area_has_widget_attached ) {
      return null;// no widget attached , so dont render
    }

    woo_template_plfwa_additional_mod_install();

    ob_start();?>
    <div class="product-list-filters-area wrapper" data-plfwa>
      <?php dynamic_sidebar('product-list-filters'); ?>
    </div>
    <?php echo ob_get_clean();

    woo_template_plfwa_additional_mod_uninstall();

  }
  
  
  
  /**
   * 1 - add script .
   */
  // function woo_template_plfwa_add_script() {
  //   $handle = 'widget-area-product-list-filters';
  //   $path = get_stylesheet_directory_uri() .'/assets/widget-area-product-list-filters/scripts.js';
  //   $deps = array("jquery");
  //   $version = '1.0';
  //   $in_footer = true;
  //   wp_register_script( $handle, $path, $deps, $version, $in_footer );
  //   wp_enqueue_script( $handle, $path, $deps, $version, $in_footer );   
  // }
  // add_action('wp_enqueue_scripts', 'woo_template_plfwa_add_script' );
  
  function woo_template_plfwa_add_script( $scripts) {
    $handle = 'widget-area-product-list-filters';
    $src = woo_template_get_partial_path_of_local_dir(__DIR__) . '/assets/widget_area_product_list_filters.js';

    $scripts[$handle] = [
      'src'             => $src,
      'src_need_prefix' => true,
      'auto_load'       => true,
      'deps'            => array('jquery'),
    ];
    return $scripts;
  }
  add_filter( 'woo_template_all_frontend_scripts_filter', 'woo_template_plfwa_add_script' );

  

  /**
   * 2 - add widget area rendering ACTION with HOOKS .
   */
  //add_action('woocommerce_archive_description', 'woo_template_plfwa_render', 25 );



  //___________________ Widget Area - Woo Template Product List Filters additional code ______________________
  
  if (!function_exists('woo_uti_sql_doSql_returnArrayOfArray')) {
    function woo_uti_sql_doSql_returnArrayOfArray($sql = '') {
      if ( empty($sql) ) {return [];}
      global $wpdb;
      $result = $wpdb->get_results($sql);
      $result = woo_uti_sql_convertSqlResultToArrayOfArray($result);
      if ( empty($result) ) {return [];}
      return $result;
    }
  }
    
  if (!function_exists('woo_uti_sql_convertSqlResultToArrayOfArray')) {
    function woo_uti_sql_convertSqlResultToArrayOfArray($result) {
      return array_map( function($a) {return (array)$a;}, $result );
    }
  }

  if (!function_exists('woo_uti_fromArrayOfArray_returnArrayOfChosenProperty')) {
    function woo_uti_fromArrayOfArray_returnArrayOfChosenProperty($array,$property_name = '') {
      if ( empty($property_name) ) {return [];}
      if ( empty($array) ) {return [];}
      // exclude array elements that not have $property_name property.
      $array = array_filter( $array, function($a) use($property_name) {
        if ( !isset($a[$property_name]) ) {return false;}
        return true;
      });
      $result = array_map( function($a) use($property_name) { return $a[$property_name]; }, $array );
      return $result;
    }
  }

  /**
  * Woocommerce Widget Price Filter
  *  change min price limit inside price filter
  */
  function woo_template_plfwa_wc_price_filter_min_amount_filter( $min ) {
    return 0;
  }
  
  /**
  * Woocommerce Widget Price Filter
  *   change  max price limit inside price filter
  */  
  function woo_template_plfwa_wc_price_filter_max_amount_filter( $max ) {
    
    function adjustPrice_multipleOfTen_addedTen($price) {
      // this function return the price
      // rounded at nearest multiple of 10
      // with 10 added

      $price = (int)$price;
      $price = (int)(10 * floor( $price/10 ));
      $price = $price + 10;
      return $price;
    }

    $return_default = true;
    $new_max_price = null;

    // in taxonomy page do...
    if ( is_tax() ) {
      
      $_tax = get_queried_object();
      
      if ( !empty($_tax) ) {
        $_tax_type = $_tax->taxonomy;
        $_tax_slug = $_tax->slug;
        $_tax_id = $_tax->term_id;
      }
      
      if ( !empty($_tax_id) ) {
        global $wpdb;
        $sql = "SELECT object_id from wp_term_relationships where term_taxonomy_id = '".$_tax_id."'";
        $all_product_ids_from_tax = woo_uti_sql_doSql_returnArrayOfArray($sql);
        if ( !empty($all_product_ids_from_tax) ) {
          $all_product_ids_from_tax = woo_uti_fromArrayOfArray_returnArrayOfChosenProperty($all_product_ids_from_tax,'object_id');
          $all_product_ids_from_tax = '('.implode(",", $all_product_ids_from_tax).')';

          $sql = "SELECT meta_value, post_id from wp_postmeta where post_id IN ".$all_product_ids_from_tax." AND meta_key = '_price' ORDER BY CAST(meta_value AS UNSIGNED) DESC";
          $all_prices_from_tax = woo_uti_sql_doSql_returnArrayOfArray($sql);

          if ( !empty($all_prices_from_tax) ) {
            $all_prices_from_tax = woo_uti_fromArrayOfArray_returnArrayOfChosenProperty($all_prices_from_tax,'meta_value');
            $new_max_price = $all_prices_from_tax[0];
            $return_default = false;
          }
        }
      }      
    
    }
    // in shop base page do..
    else if (is_shop()) {
      global $wpdb;      
    
      $sql = "SELECT MAX(meta_value), post_id from wp_postmeta where meta_key = '_price'";
      $result = $wpdb->get_results($sql);
    
      if (!empty($result)) {
        
        $result = woo_uti_sql_convertSqlResultToArrayOfArray($result)[0];
        $id = $result['post_id'];
        
        if (!empty($id)) {
          $_product = wc_get_product( (int)$id );
          if ($_product && $_product->is_visible()) {
            $new_max_price = $result['MAX(meta_value)'];
            $return_default = false;
          }       
        }        
      }
    
    }

    if ($return_default === false && is_numeric((int)$new_max_price)) {
      return adjustPrice_multipleOfTen_addedTen($new_max_price);
    }
    
    return $max;
  
  }
  
  /**
  * Woocommerce Widget Price Filter
  *   change step value amount
  */  
  function woo_template_plfwa_wc_price_filter_widget_step_filter( $step ) {
    return 5;
  }



  function woo_template_plfwa_additional_mod_install() {

    add_filter( 'woocommerce_price_filter_widget_min_amount', 'woo_template_plfwa_wc_price_filter_min_amount_filter' );
    add_filter( 'woocommerce_price_filter_widget_max_amount', 'woo_template_plfwa_wc_price_filter_max_amount_filter' );
    add_filter( 'woocommerce_price_filter_widget_step', 'woo_template_plfwa_wc_price_filter_widget_step_filter' );

  }
  function woo_template_plfwa_additional_mod_uninstall() {

    remove_filter( 'woocommerce_price_filter_widget_min_amount', 'woo_template_plfwa_wc_price_filter_min_amount_filter' );
    remove_filter( 'woocommerce_price_filter_widget_max_amount', 'woo_template_plfwa_wc_price_filter_max_amount_filter' );
    remove_filter( 'woocommerce_price_filter_widget_step', 'woo_template_plfwa_wc_price_filter_widget_step_filter' );

  }






})

?>