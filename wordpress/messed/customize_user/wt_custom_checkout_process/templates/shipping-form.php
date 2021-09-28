<div class="woocommerce-address-fields">

  <?php $load_address = 'shipping'; ?>

  <?php 
  $current_user = wp_get_current_user();
  $load_address = sanitize_key( $load_address );
  $country      = get_user_meta( get_current_user_id(), $load_address . '_country', true );
  
  if ( ! $country ) {
    $country = WC()->countries->get_base_country();
  }
  if ( 'billing' === $load_address ) {
    $allowed_countries = WC()->countries->get_allowed_countries();
    if ( ! array_key_exists( $country, $allowed_countries ) ) {
      $country = current( array_keys( $allowed_countries ) );
    }
  }
  
  $address = WC()->countries->get_address_fields( $country, $load_address . '_' );
        
  // Prepare values.
  foreach ( $address as $key => $field ) {
    $value = get_user_meta( get_current_user_id(), $key, true );
    if ( ! $value ) {
      switch ( $key ) {
        case 'billing_email':
        case 'shipping_email':
          $value = $current_user->user_email;
        break;
      }
    }
    $address[ $key ]['value'] = apply_filters( 'woocommerce_my_account_edit_address_field_value', $value, $key, $load_address );
  }
  
  do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

  <div class="woocommerce-address-fields__field-wrapper">
    <?php
    foreach ( $address as $key => $field ) {
      woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
    }
    ?>
  </div>

  <?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

</div>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>