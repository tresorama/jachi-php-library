<?php

if ( empty( $_tpv_cart_item ) ) {
  return null;
}

// extract data
$_cart_item     = $_tpv_cart_item['cart_item'];
$_cart_item_key = $_tpv_cart_item['cart_item_key'];

// get product object and id
$_product       = apply_filters( 'woocommerce_cart_item_product', $_cart_item['data'], $_cart_item, $_cart_item_key );
$_product_id    = apply_filters( 'woocommerce_cart_item_product_id', $_cart_item['product_id'], $_cart_item, $_cart_item_key );

if ( !$_product ) {
  return null;
}
if ( false === $_product->exists() ) {
  return null;
}
if ( false === $_cart_item['quantity'] > 0) {
  return null;
}
if ( false === apply_filters( 'woocommerce_cart_item_visible', true, $_cart_item, $_cart_item_key )) {
  return null;
}

// wrapper classes
$_classes        = 'cart-item';

// link
$_product_link   = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $_cart_item ) : '', $_cart_item, $_cart_item_key );

// remove button
$_product_remove = apply_filters(
  'woocommerce_cart_item_remove_link', 
  sprintf(
    '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
    esc_url( wc_get_cart_remove_url( $_cart_item_key ) ),
    esc_html__( 'Remove from cart', 'woocommerce-template' ),
    esc_attr( $_product_id ),
    esc_attr( $_product->get_sku() )
  ),
  $_cart_item_key
);

// image
$_product_image = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $_cart_item, $_cart_item_key );

ob_start();
printf( '<a href="%s">%s</a>', esc_url( $_product_link ), $_product_image );
$_product_image_with_link = ob_get_clean();


// name infos:
//              title
$_product_title = $_product->get_name();
$_product_title = ($_product_link) ? wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $_product_link ), $_product_title ), $_cart_item, $_cart_item_key ) ) : wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product_title, $_cart_item, $_cart_item_key ) . '&nbsp;' );

//              additional info
ob_start();
do_action( 'woocommerce_after_cart_item_name', $_cart_item, $_cart_item_key );
$_product_additional_info = ob_get_clean();

//              meta data
$_product_meta_data = wc_get_formatted_cart_item_data( $_cart_item );

//              backorder notification
$_backorder_notification = null;
if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $_cart_item['quantity'] ) ) {
  $_backorder_notification =  wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $_product_id ) );
}

// price
$_product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $_cart_item, $_cart_item_key );

// quantity
if ( $_product->is_sold_individually() ) {
  $_product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $_cart_item_key );
}
else {
  // $_product_quantity = woocommerce_quantity_input(
  //   array(
  //     'input_name'   => "cart[{$_cart_item_key}][qty]",
  //     'input_value'  => $_cart_item['quantity'],
  //     'max_value'    => $_product->get_max_purchase_quantity(),
  //     'min_value'    => '0',
  //     'product_name' => $_product->get_name(),
  //   ),
  //   $_product,
  //   false
  // );
  $_product_quantity = woo_template_product_quantity_select(
    array(
      'input_name'   => "cart[{$_cart_item_key}][qty]",
      'input_value'  => $_cart_item['quantity'],
      'max_value'    => $_product->get_max_purchase_quantity(),
      'min_value'    => '0',
      'product_name' => $_product->get_name(),
    ),
    $_product,
    false
  );
}
$_product_quantity = apply_filters( 'woocommerce_cart_item_quantity', $_product_quantity, $_cart_item_key, $_cart_item );

// subtotal
$_product_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $_cart_item['quantity'] ), $_cart_item, $_cart_item_key );

?>

<article class="<?php echo $_classes ?>">
  
  <section class="image">
        
    <div class="product-thumbnail"><?php echo $_product_image; ?></div>
  
    <?php /*<div class="product-remove"><?php echo $_product_remove; ?></div> */?>

  </section>
  
  <section class="details">
    
    <?php if ($_product_title) : ?>
      <div class="product-title" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>" ><?php echo $_product_title; ?></div>
    <?php endif; ?>
    
    <?php if ($_product_additional_info) : ?>
      <div class=product-additional-info><?php echo $_product_additional_info; ?></div>
    <?php endif; ?>
    
    <?php if ($_product_meta_data) : ?>
      <div class=product-meta-data><?php echo $_product_meta_data; ?></div>
    <?php endif; ?>
    
    <?php if ($_backorder_notification) : ?>
      <div class=product-backorder-notification><?php echo $_backorder_notification; ?></div>
    <?php endif; ?>
      
    <?php /*
    <div class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>"><?php echo $_product_price; ?></div>
    */
    ?>
    <div class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>"><?php echo $_product_subtotal; ?></div>

    <div class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>"><?php echo $_product_quantity; ?></div>   
  
  </section>

</article>