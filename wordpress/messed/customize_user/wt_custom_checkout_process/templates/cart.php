<?php 

// this directory
$path  = woo_template_get_partial_path_of_local_dir(__DIR__);

// get cart items
$_cart_items = WC()->cart->get_cart();

if ( empty( $_cart_items ) ) {
  return null;
}

?>

<div class="checkout-cart-list">

<?php
  
  foreach ( $_cart_items as $_cart_item_key => $_cart_item) {
    
    $_tpv_cart_item = [
      "cart_item_key" => $_cart_item_key,
      "cart_item"     => $_cart_item,
    ];
    
    get_template_part_with_query_vars( $path . '/cart-item' , array( '_tpv_cart_item' => $_tpv_cart_item ) );
  
  }

?>
</div>