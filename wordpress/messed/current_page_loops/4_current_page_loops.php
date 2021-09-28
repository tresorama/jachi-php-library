<?php

class WOO_TEMPLATE_CURRENT_PAGE_DATA {

  private $page_data = [];

  private $page_loops = [];

  
  // internal functions  
  
  public function save_page_data() {
  
    global $wp;

    $_queried_object_id = get_queried_object_id();
    $_current_url       = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
    
    $this->page_data = array(
      "page_id"  => $_queried_object_id,
      'page_url' => $_current_url
    );
  
  }
  private function update_page_loops( $new_page_loops ) {
    $this->page_loops = $new_page_loops;
  }
  public function page_loops_add_key_value( $key = '', $value = null ) {
    
    if ( empty($key) ) {
      return;
    }
    if ( empty($value) ) {
      return;
    }

    $page_loops = $this->page_loops;  
    
    if ( isset( $page_loops[$key] ) ) { 
      // se cera gia...    
      if ( isset($page_loops[$key.'_old']) ) {
        // se cera gia un OLD value...      
        if ( $page_loops[$key.'_old'] !== $page_loops[$key]) {
          $page_loops[$key.'_old'] = $page_loops[$key];
        }
      }
      else {     
        $page_loops[$key.'_old'] = $page_loops[$key];
      }
    }
    
    $page_loops[$key] = $value;

    $this->update_page_loops( $page_loops );

  }
  public function page_loops_remove_key( $key = '' ) {
    
    if ( empty($key) ) {
      return;
    }
    
    $page_loops = $this->page_loops;  

    if ( !isset($page_loops[$key]) ) {
      return;
    }
  
    unset($page_loops[$key]); 
    if (isset($page_loops[$key.'_old'])) {
      $page_loops[$key] = $page_loops[$key.'_old'];
      unset($page_loops[$key.'_old']);
    }

    $this->update_page_loops( $page_loops );

  }
  public function page_loops_get_loop_data( $key = '' ) {

    if ( empty($key) ) {
      return null;
    }

    $page_loops = $this->page_loops;  

    if ( !isset($page_loops[$key])) {
      return null;
    }
    else if ( empty($page_loops[$key])) {
      return null;
    }

    $loop_data = $page_loops[$key];
      
    return $loop_data;

  }
  public function page_loops_get_current_loop_data() {

    $loop_type = $this->page_loops_get_current_loop_type();
    $loop_data = $this->page_loops_get_loop_data($loop_type);
    return $loop_data;

  }
  public function page_loops_get_current_loop_type() {

    $page_loops = $this->page_loops;
    
    if ( !isset($page_loops['current_loop_type']) ) {
      return null;
    }
    if ( empty($page_loops['current_loop_type']) ) {
      return null;
    }
    
    return $page_loops['current_loop_type'];

  }
  public function page_loops_get_current_loop_index( $id_to_check = null ) {
    
    $loop_data = $this->page_loops_get_current_loop_data();

    if ( empty( $loop_data ) ) {
      return null;
    }

    // cerca gli items che stai renderizzando
    if ( ! isset( $loop_data['items'] ) ) {
      return null;
    }
    if ( empty( $loop_data['items'] ) ) {
      return null;
    }
    
    $loop_items = $loop_data['items'];

    if ( null === $id_to_check ) {
      global $post;
      $current_rendering_post_id = $post->ID;
    }

    $i = -1;
    foreach ( $loop_items as $key => $item) {
      $i++;
      $item_id = null;
      if ( function_exists( $item->get_id() ) ) {
        $item_id = $item->get_id();
      }
      else if ( isset( $item->ID ) ) {
        $item_id = $item->ID;
      }
      else if ( isset( $item->id ) ) {
        $item_id = $item->id;
      }
      else if ( isset( $item['data']->id ) ) {
        $item_id = $item['data']->id;
      }
      // else if ( isset($item['product_id']) ) {
      //   $item_id = $item['product_id'];
      // }
      if ( $current_rendering_post_id !== $item_id ) {
        continue;
      }
      
      return $i;
    
    }
    
    // se non l'ho trovato
    return null;
  }
  public function page_loops_get_current_loop_total() {
    
    $loop_data = $this->page_loops_get_current_loop_data();

    if ( empty($loop_data) ) {
      return null;
    }

    // cerca quanti sono gli items che stai renderizzando ...
    if ( !isset($loop_data['total_to_show']) ) {
      return null;
    }
    if ( empty($loop_data['total_to_show']) ) {
      return null;
    }
    $total_to_show = $loop_data['total_to_show'];

    return $total_to_show;
    
  }



  // default loops saved functions...

  public function init() {
    
    add_action( 'template_redirect', [ $this, 'save_page_data'], 5 );

    // Posts

    // Archive Post
    add_action( 'woo_template_before_rendering_archive_post', [ $this, 'archive_post_on'] );
    add_action( 'woo_template_after_rendering_archive_post', [ $this, 'archive_post_off'] );


    // Woocommerce

    // Single Product Page => The Product
    add_action( 'woocommerce_before_single_product', [ $this, 'woocommerce_the_product_on'] );
    add_action( 'woocommerce_after_single_product', [ $this, 'woocommerce_the_product_off'], 99 );

    // Single Product Page => Related Products
    add_action( 'woocommerce_before_template_part', [ $this, 'woocommerce_related_products_on'], 10, 4 );

    // Single Product Page => Upsells Product
    add_action( 'woocommerce_before_template_part', [ $this, 'woocommerce_upsells_products_on'], 10, 4 );

    // Cart Items
    add_action( 'woo_template_before_rendering_cart_items', [ $this, 'woocommerce_cart_items_on'] );
    add_action( 'woo_template_after_rendering_cart_items', [ $this, 'woocommerce_cart_items_off'], 99 );
    
    // Archive Product
    add_action( 'woocommerce_before_shop_loop', [ $this, 'woocommerce_archive_product_on'] );
    add_action( 'woocommerce_after_shop_loop', [ $this, 'woocommerce_archive_product_off'] );

  }



  //__________________________________SINGLE-PRODUCT-PAGE _________________________________________
  //______________________________________ .... => THE_PRODUCT ______________________________________

  public function woocommerce_the_product_on() {
    
    global $post;
    $value = array(
      "items"=> [$post],
      "total_to_show" => 1,
    );

    $this->page_loops_add_key_value( 'current_loop_type', 'the_product' ); 
    $this->page_loops_add_key_value( 'the_product', $value );

  }

  public function woocommerce_the_product_off() {
    $this->page_loops_remove_key( 'current_loop_type' );
  }
  
  //____________________________________ ... => RELATED-PRODUCTS _________________________________

  public function woocommerce_related_products_on( $template_name, $template_path, $located, $args ) {
    
    if ( 'single-product/related.php' !== $template_name ) {
      return;
    }

    $already_saved = 'related_products' === $this->page_loops_get_current_loop_type();
    if ( $already_saved ) {
      return;
    }

    $related = $args['related_products'];

    $related_only_visible = array_filter( $related, function ($pr) {
      if ( $pr->is_visible() ) {
        return $pr;
      }
    });

    $value = array(
      "items"         => $related_only_visible,
      "total_to_show" => count($related_only_visible),
      "limit"         => $args['posts_per_page'],
      "columns"       => $args['columns'],
      "orderby"       => $args['orderby'],
      "order"         => $args['order'],
    );

    $this->page_loops_add_key_value( 'current_loop_type', 'related_products' ); 
    $this->page_loops_add_key_value( 'related_products', $value );

    add_action( 'woocommerce_after_template_part', [ $this, 'woocommerce_related_products_off'], 10, 4 );

  }

  public function woocommerce_related_products_off( $template_name, $template_path, $located, $args ) {
  
    if ( 'single-product/related.php' !== $template_name ) {
      return;
    }
    
    $this->page_loops_remove_key( 'current_loop_type' );
    
    remove_action( 'woocommerce_before_template_part', [ $this, 'woocommerce_related_products_on'], 10, 4 );
    remove_action( 'woocommerce_after_template_part', [ $this, 'woocommerce_related_products_off'], 10, 4 );

  }
  
  //____________________________________ ... => UPSELLS-PRODUCTS __________________________________

  public function woocommerce_upsells_products_on( $template_name, $template_path, $located, $args ) {
    
    if ( 'single-product/up-sells.php' !== $template_name ) {
      return;
    }

    $already_saved = 'upsells_products' === $this->page_loops_get_current_loop_type();
    if ( $already_saved ) {
      return;
    }

    $upsells = $args['upsells'];

    $upsells_only_visible = array_filter( $upsells, function ($item) {
      if ( $item->is_visible() ) {
        return $item;
      }
    });

    $value = array(
      "items"         => $upsells_only_visible,
      "total_to_show" => count($upsells_only_visible),
      "limit"         => $args['posts_per_page'],
      "columns"       => $args['columns'],
      "orderby"       => $args['orderby'],
    );
    
    
    $this->page_loops_add_key_value( 'current_loop_type', 'upsells_products' ); 
    $this->page_loops_add_key_value( 'upsells_products', $value );

    add_action( 'woocommerce_after_template_part', [ $this, 'woocommerce_upsells_products_off'], 10, 4 );


  }

  public function woocommerce_upsells_products_off( $template_name, $template_path, $located, $args ) {
    
    if ( 'single-product/up-sells.php' !== $template_name ) {
      return;
    }

    $this->page_loops_remove_key( 'current_loop_type' );
    
    remove_action( 'woocommerce_before_template_part', [ $this, 'woocommerce_upsells_products_on'], 10, 4 );
    remove_action( 'woocommerce_after_template_part', [ $this, 'woocommerce_upsells_products_off'], 10, 4 );

  }
  
  
  //__________________________________ CART-PAGE _________________________________________

  public function woocommerce_cart_items_on( $cart_items ) {

    $cart_products_only_visible = array_filter( $cart_items, function($ca) {
      $product = $ca['data'];
      if ( $product->is_visible() ) {
        return $ca;
      } 
    });

    $value = array(
      'items'         => $cart_products_only_visible,
      'total_to_show' => count($cart_products_only_visible)
    );

    $this->page_loops_add_key_value( 'current_loop_type', 'cart_items' ); 
    $this->page_loops_add_key_value( 'cart_items', $value );

  }

  public function woocommerce_cart_items_off() {
    $this->page_loops_remove_key( 'current_loop_type' );
  }
  
  //__________________________________ ARCHIVE-POST PAGE _________________________________________

  public function archive_post_on() {
    
    global $wp_query;
    $archive_posts = $wp_query->posts;
    $archive_posts_only_visible = array_filter( $archive_posts, function( $po ) {
      if ( 'publish' === $po->post_status ) {
        return $po;
      }
    });

    $value = array(
      'items' => $archive_posts_only_visible,
      'total_to_show' => count($archive_posts_only_visible)
    );

    $this->page_loops_add_key_value( 'current_loop_type', 'archive_posts' ); 
    $this->page_loops_add_key_value( 'archive_posts', $value );

  }

  public function archive_post_off() {
    $this->page_loops_remove_key( 'current_loop_type' );
  }


  //__________________________________ ARCHIVE-PRODUCT PAGE _________________________________________

  public function woocommerce_archive_product_on() {
    
    global $wp_query;
    $archive_products = $wp_query->posts;
    $archive_products_only_visible = array_filter( $archive_products, function( $pr ){
      if ( 'publish' === $pr->post_status ) { 
        return $pr;
      }
    });

    $value = array(
      'items' => $archive_products_only_visible,
      'total_to_show' => count($archive_products_only_visible)
    );

    $this->page_loops_add_key_value( 'current_loop_type', 'archive_products' ); 
    $this->page_loops_add_key_value( 'archive_products', $value );

  }


  public function woocommerce_archive_product_off() {
    $this->page_loops_remove_key( 'current_loop_type' );
  }


}


// crate the class and save into global
$GLOBALS['WT_CURRENT_PAGE_DATA'] = new WOO_TEMPLATE_CURRENT_PAGE_DATA();
// install !
$GLOBALS['WT_CURRENT_PAGE_DATA']->init();



// save functions for simple use

function woo_template_get_current_loop_type() {
  return $GLOBALS['WT_CURRENT_PAGE_DATA']->page_loops_get_current_loop_type();
}
function woo_template_get_current_loop_data() {
  return $GLOBALS['WT_CURRENT_PAGE_DATA']->page_loops_get_current_loop_data();
}

function woo_template_get_current_loop_index( $id_to_check = null ) {
  return $GLOBALS['WT_CURRENT_PAGE_DATA']->page_loops_get_current_loop_index( $id_to_check );
}
function woo_template_get_current_loop_total() {
  return $GLOBALS['WT_CURRENT_PAGE_DATA']->page_loops_get_current_loop_total();
}



?>