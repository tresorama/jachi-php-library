<?php
$register_permalink = WT_CUSTOM_REGISTRATION()->get_register_page_permalink();
$register_text = __( 'Register', 'woocommerce-template' );
?>

<div class="woocommerce-form woocommerce-form-register register">
  
  <?php WT_USER_WHY_REGISTER_IS_GOOD::render(); ?>
  
  <a class="button" href="<?php echo esc_url( $register_permalink ); ?>"><?php echo esc_html( $register_text ); ?></a>

</div>