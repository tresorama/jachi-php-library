<?php
/**
 * Shipping Calculator
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/shipping-calculator.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

//do_action( 'woocommerce_before_shipping_calculator' ); ?>

<div class="woocommerce-shipping-calculator" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	
	<section class="shipping-calculator-form" >

		<?php // SHIPPING COUNTRY
		if ( apply_filters( 'woocommerce_shipping_calculator_enable_country', true ) ) : ?>
			<p class="form-row form-row-wide" id="calc_shipping_country_field">
				<select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state country_select" rel="calc_shipping_state">
					<option value=""><?php esc_html_e( 'Select a country / region&hellip;', 'woocommerce' ); ?></option>
					<?php
					$current_costumer_country = WC()->customer->get_shipping_country();
					foreach ( WC()->countries->get_shipping_countries() as $country_code => $country_nice_name ) {
						echo '<option value="' . esc_attr( $country_code ) . '" ' . selected( $current_costumer_country, esc_attr( $country_code ), false ) . '>' . esc_html( $country_nice_name ) . '</option>';
					} ?>
				</select>
			</p>
		<?php endif; ?>

		<?php // SHIPPING STATE/PROVINCIA/REGION
		if ( apply_filters( 'woocommerce_shipping_calculator_enable_state', true ) ) : ?>		
			<p class="form-row form-row-wide" id="calc_shipping_state_field">
				<?php
				$current_costumer_country = WC()->customer->get_shipping_country();
				$current_costumer_state  	= WC()->customer->get_shipping_state();
				$states     							= WC()->countries->get_states( $current_costumer_country );

				if ( is_array( $states ) && empty( $states ) ) : ?>
					
					<input type="hidden" name="calc_shipping_state" id="calc_shipping_state" placeholder="<?php esc_attr_e( 'State / County', 'woocommerce' ); ?>" />
				
				<?php 
				elseif ( is_array( $states ) ) : ?>
					
					<span>
						<select name="calc_shipping_state" class="state_select" id="calc_shipping_state" data-placeholder="<?php esc_attr_e( 'State / County', 'woocommerce' ); ?>">
							<option value=""><?php esc_html_e( 'Select an option&hellip;', 'woocommerce' ); ?></option>
								<?php
								foreach ( $states as $state_code => $state_nice_name ) {
									echo '<option value="' . esc_attr( $state_code ) . '" ' . selected( $current_costumer_state, $state_code, false ) . '>' . esc_html( $state_nice_name ) . '</option>';
								}
								?>
						</select>
					</span>
				
				<?php
				else : ?>
				
					<input type="text" class="input-text" value="<?php echo esc_attr( $current_costumer_state ); ?>" placeholder="<?php esc_attr_e( 'State / County', 'woocommerce' ); ?>" name="calc_shipping_state" id="calc_shipping_state" />
				
				<?php
				endif; ?>
			</p>
		
		<?php endif; ?>

		<?php // SHIPPING CITY
		if ( apply_filters( 'woocommerce_shipping_calculator_enable_city', true ) ) : ?>
			<p class="form-row form-row-wide" id="calc_shipping_city_field">
				<input type="text" class="input-text" value="<?php echo esc_attr( WC()->customer->get_shipping_city() ); ?>" placeholder="<?php esc_attr_e( 'City', 'woocommerce' ); ?>" name="calc_shipping_city" id="calc_shipping_city" />
			</p>
		<?php endif; ?>

		<?php // SHIPPING POST CODE
		if ( apply_filters( 'woocommerce_shipping_calculator_enable_postcode', true ) ) : ?>
			<p class="form-row form-row-wide" id="calc_shipping_postcode_field">
				<input type="text" class="input-text" value="<?php echo esc_attr( WC()->customer->get_shipping_postcode() ); ?>" placeholder="<?php esc_attr_e( 'Postcode / ZIP', 'woocommerce' ); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" />
			</p>
		<?php endif; ?>

	</section>

</div>

<?php // do_action( 'woocommerce_after_shipping_calculator' ); ?>