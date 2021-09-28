<div id="cs-payment" class="cs-payment">

	<form class="fake-woocommerce-checkout-form">	
		
		<input type="hidden" name="billing_first_name" />
		<input type="hidden" name="billing_last_name" />
		<input type="hidden" name="billing_company" />
		<input type="hidden" name="billing_country" />
		<input type="hidden" name="billing_address_1" />
		<input type="hidden" name="billing_address_2" />
		<input type="hidden" name="billing_city" />
		<input type="hidden" name="billing_state" />
		<input type="hidden" name="billing_postcode" />
		<input type="hidden" name="billing_phone" />
		<input type="hidden" name="billing_email" />
		

		<input type="hidden" name="shipping_first_name" />
		<input type="hidden" name="shipping_last_name" />
		<input type="hidden" name="shipping_company" />
		<input type="hidden" name="shipping_country" />
		<input type="hidden" name="shipping_address_1" />
		<input type="hidden" name="shipping_address_2" />
		<input type="hidden" name="shipping_city" />
		<input type="hidden" name="shipping_state" />
		<input type="hidden" name="shipping_postcode" />


		<?php
		if ( WC()->cart->needs_payment() ) {
			$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
			WC()->payment_gateways()->set_current_gateway( $available_gateways );
		}
		else {
			$available_gateways = array();
		}
		
		wc_get_template(
			'checkout/payment.php',
			array(
				'checkout'           => WC()->checkout(),
				'available_gateways' => $available_gateways,
				'order_button_text'  => apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) ),
			),
		);
		?>

	</form>

</div>