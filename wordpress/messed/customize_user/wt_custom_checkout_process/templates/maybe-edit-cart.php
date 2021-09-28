<?php

$cart_page_permalink = wc_get_cart_url();

?>

<div class="maybe-edit-cart" >
  <a href="<?php echo esc_url( $cart_page_permalink ); ?>">MODIFICA</a>
</div>