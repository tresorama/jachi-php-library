<?php

$path = woo_template_get_partial_path_of_local_dir(__DIR__);

?>

<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

<div id="order_review" class="woocommerce-checkout-review-order">
  
  <?php // do_action( 'woocommerce_checkout_order_review' ); ?>

  <?php // wc_get_template( 'checkout/review-order.php', [ 'checkout' => WC()->checkout() ] ); ?>

  <table class="short-version">
    <tfoot>

    <?php get_template_part( $path . '/order-review-short-cart-subtotal' ); ?>
    <?php get_template_part( $path . '/order-review-short-shipping-subtotal' ); ?>
    <?php get_template_part( $path . '/order-review-short-order-total' ); ?>
      
    </tfoot>
  </table>
  
</div>

<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>