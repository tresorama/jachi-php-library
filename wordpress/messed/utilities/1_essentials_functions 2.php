<?php

/* =================================================== 
			REQUEST UTILITIES
=================================================== */

/**
 * What type of request is this?
 *
 * @param  string $type admin, ajax, cron or frontend.
 * @return bool
 */
function woo_template_is_request_wc( $type ) {
	
	$is_admin = is_admin();
	$is_ajax  = defined( 'DOING_AJAX' );
	$is_cron  = defined( 'DOING_CRON' );
	$is_api   = woo_template_is_rest_api_request_wc();
	$is_frontend = ( ! $is_admin || $is_ajax ) && ! $is_cron && ! $is_api;

	$name = 'is_' . $type;
	return $$name;

}

/**
 * Returns true if the request is a non-legacy REST API request.
 *
 * Legacy REST requests should still run some extra code for backwards compatibility.
 *
 * @todo: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
 *
 * @return bool
 */
function woo_template_is_rest_api_request_wc() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}

	$rest_prefix         = trailingslashit( rest_get_url_prefix() );
	$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return apply_filters( 'woocommerce_is_rest_api_request', $is_rest_api_request );
}

/* =================================================== 
			WOOCOMMERCE GENERAL UTILITIES
=================================================== */

/**
 * Woocommerce Plugin is Active ?
 *
 * @return bool
 */
function woo_template_woocommerce_is_active() {
	return woo_template_plugin_is_active('woocommerce');
}

/**
 * is a Woocommerce Page
 */
function woo_template_is_woocommerce() {
	
	$is_woo_page = false;
	$woo_active = woo_template_woocommerce_is_active();

	if ( $woo_active ) {

		/*_________________________________________________________________________		
		fix when this functions is called before wp query finish its run.
		in that case "is_woocommerce()" produce an exception during execution 
		because $wp_query->>queried_object is null. */		
		global $wp_query;	
		if ( null === $wp_query->get_queried_object() ) {		
			return woo_template_is_woocommerce_safe();
		}
		/*_________________________________________________________________________ */
	
		
		if ( is_woocommerce() ) {
			$is_woo_page = true;
		}
	
	}
	
	return $is_woo_page;

}

/**
 * is a Woocommerce Page Safe
 * 
 * Don't call this func directly, use "woo_template_is_woocomerce" instead.
 * 
 * 
 * When you call "woo_template_is_woocomerce()" , 
 * if wp_query not finisched its run this func will be called.
 * 
 * Because in that case,
 * "is_woocommerce()" __- used inside "woo_template_is_woocomerce()" -__ 
 * produce an exception during execution , due to the fact that
 * $wp_query->queried_object is null.
 * 
 * This func check directly in wp query if post type is product.
 */
function woo_template_is_woocommerce_safe() {
	
	$is_woo_page = false;

	global $wp_query;		
	$post_type = isset( $wp_query->query['post_type'] ) ? $wp_query->query['post_type'] : null;
	
	if ( 'product' === $post_type ) {
		$is_woo_page = true;
	}
	
	return $is_woo_page;
	
}

/**
 * is a Woocommerce Archive Product Page
 *
 * @return bool
 */
function woo_template_is_product_archive() {
	
	$is_woo_page = woo_template_is_woocommerce();
	
	if ( $is_woo_page ) {
		global $wp_query;
		$is_archive = $wp_query->is_archive;
		
		if ( true === $is_archive ) {
			return true;
		}
	}
	
	return false;

}

/**
 * is a Woocommerce Single Product Page
 *
 * @return bool
 */
function woo_template_is_product_single() {
	
	$is_woo_page = woo_template_is_woocommerce();
	
	if ( $is_woo_page ) {
		global $wp_query;
		$is_single = $wp_query->is_single;
		
		if ( true === $is_single ) {
			return true;
		}
	}
	
	return false;

}


/* =================================================== 
			CART UTILITIES
=================================================== */
/**
 * Get quantity amount of "product_id" in the cart, if not present return (int) 0 
 *
 * @param [type] $product_id
 * @param string $cart_item_key
 * @return int
 */
function woo_template_get_in_cart_quantity( $product_id , $cart_item_key = '' ) {
	
	global $woocommerce;
	$running_qty = 0; // iniializing quantity to 0

	$cart = (!empty($woocommerce)) ? $woocommerce->cart->get_cart() : WC()->cart->get_cart();

	// search the cart for the product in and calculate quantity.
	foreach( $cart as $current_cart_item_keys => $values ) {
		if ( $product_id == $values['product_id'] ) {
			if ( $cart_item_key == $current_cart_item_keys ) {continue;}
			$running_qty += (int) $values['quantity'];
		}
	}

	return $running_qty;
}


/* =================================================== 
			QUANTITY SELECT RENDER
=================================================== */
function woo_template_product_quantity_select( $args = array(), $product = null, $echo = true ) {
	
	if ( is_null( $product ) ) {
		$product = $GLOBALS['product'];
	}

	$defaults = array(
		'input_id'     => uniqid( 'quantity_' ),
		'input_name'   => 'quantity',
		'input_value'  => '1',
		'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text' ), $product ),
		'max_value'    => apply_filters( 'woocommerce_quantity_input_max', -1, $product ),
		'min_value'    => apply_filters( 'woocommerce_quantity_input_min', 0, $product ),
		'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
		'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
		'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),
		'product_name' => $product ? $product->get_title() : '',
		'placeholder'  => apply_filters( 'woocommerce_quantity_input_placeholder', '', $product ),
	);

	$args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), $product );

	// Apply sanity to min/max args - min cannot be lower than 0.
	$args['min_value'] = max( $args['min_value'], 0 );
	$args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : '';

	// Max cannot be lower than min if defined.
	if ( '' !== $args['max_value'] && $args['max_value'] < $args['min_value'] ) {
		$args['max_value'] = $args['min_value'];
	}


	// extract vars
	extract($args);
	

	ob_start();	
	if ( $max_value && $min_value === $max_value ) { ?>
	<div class="quantity hidden">
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
	</div>
	<?php 
	} 
	else {
		/* translators: %s: Quantity. */
		$label = ! empty( $args['product_name'] ) ? sprintf( esc_html__( '%s quantity', 'woocommerce' ), wp_strip_all_tags( $args['product_name'] ) ) : esc_html__( 'Quantity', 'woocommerce' );
		?>
	<div class="quantity">
		<?php do_action( 'woocommerce_before_quantity_input_field' ); ?>
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $label ); ?></label>				
		<label for="quantity"><?php echo esc_html( __( 'Quantity', 'woocommerce-template' )); ?></label>
		<select name="quantity" id="select-<?php echo esc_attr( $input_id ); ?>">
		<?php
		$options = intval( $max_value );
		for ( $i = 0; $i <= $options; $i++ ) { ?>
			<option value="<?php echo esc_attr( $i ); ?>" <?php echo ( $input_value === $i) ? 'selected="selected"': null ;?>><?php echo esc_html( $i ); ?></option>
		<?php } ?>
		</select>
		<script>
		jQuery(document).ready(function ($) {
			let script = {
				input_name: "<?php echo esc_attr( $input_name ); ?>",
				select_id: "select-<?php echo esc_attr( $input_id ); ?>",
				GrabFromDom: function () {
					this.input = document.querySelector('input[name*="'+ this.input_name+'"]');					
					if ( !this.input){
						return false;
					}
					this.select_id = 'select-'+this.input.id;
					this.select = document.querySelector('#'+ this.select_id);
					if ( !this.select){
						return false;
					}
					this.options = [...this.select.querySelectorAll('option')];
					if ( !this.options){
						return false;
					}
					return true;
				},
				SelectCallBack: function(e) {
					const value = this.GetValueFromEvent(e);
					this.UpdateInputFromValue(value);
					this.UpdateSelectFromValue(value);
				},
				GetValueFromEvent: function(e) {
					return e.currentTarget.value;
				},
				GetValueFromInput: function() {
					return this.input.value;
				},
				GetValueFromSelect: function() {
					const optionActive = this.options.filter( o => {
						return o.hasAttribute('selected') && o.getAttribute('selected') === 'selected';
					});
					return optionActive[0];
				},
				UpdateSelectFromValue: function(value) {
					this.DisableAllOptions();
					this.EnableOptionWithValue(value);
				},
				UpdateInputFromValue: function(value) {
					this.input.value = value;
					this.input.setAttribute('value', value );
				},
				Init: function() {
					// Grab Element From Dom
					const iMustRun = this.GrabFromDom();
					if ( false === iMustRun) {
						return;
					}
					// update select value based on input value
					this.UpdateSelectFromValue(this.GetValueFromInput());
					this.select.addEventListener('change', this.SelectCallBack.bind(this));
					$(document.body).on("updated_wc_div", function() {
						this.Init();
					}.bind(this))
				},
				DisableAllOptions: function() {
					this.options.forEach( o => {
						o.removeAttribute("selected");
					});
				},
				EnableOptionWithValue: function(value) {
					const option = this.GetOptionWithValue(value);
					option.setAttribute("selected", "selected");
				},
				GetInputValue: function(e) {
					return this.input.value;
				},
				GetOptionWithValue: function(value) {
					const result = this.options.filter((o) => {return o.value === value;});
					if ( result.length !== 1 ) {
						return null;
					}
					return result[0];
				},
			};

			script.Init();
			
		});
		</script>		
		<input
			style="display:none"
			type="number"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
			step="<?php echo esc_attr( $step ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
			size="4"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			inputmode="<?php echo esc_attr( $inputmode ); ?>" />
		<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
	</div>
	<?php
	}

	if ( $echo ) {
		echo ob_get_clean(); // WPCS: XSS ok.
	} else {
		return ob_get_clean();
	}
	?>

			<input
			type="number"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
			step="<?php echo esc_attr( $step ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
			size="4"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			inputmode="<?php echo esc_attr( $inputmode ); ?>" />
		<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>

		<?php

}



/* =================================================== 
      PRODUCT IMAGE UTILITIES
=================================================== */

function woo_template_template_get_product_image( $product = null, $size = 'woocommerce_thumbnail' ) {
  $image = $product ? $product->get_image( $size ) : '';
  return $image;
}

function woo_template_template_get_product_gallery_image_with_index( $product = null, $index = 0, $size = 'woocommerce_thumbnail' ) {

  // $gallery_images_ids = $product ? $product->get_gallery_attachment_ids() : []; // deprecated function
  $gallery_images_ids = $product ? $product->get_gallery_image_ids() : [];      // actual function
  
  if ( count( $gallery_images_ids ) < ( $index + 1 ) ) {
    return '';
  }
  
  $image = wp_get_attachment_image( $gallery_images_ids[$index], $size );
  
  return $image;
 
}




?>