<?php


/**
 * Create a unique username for a new customer.
 *
 * @since 3.6.0
 * @param string $email New customer email address.
 * @param array  $new_user_args Array of new user args, maybe including first and last names.
 * @param string $suffix Append string to username to make it unique.
 * @return string Generated username.
 */
function woo_template_create_new_customer_username( $email, $new_user_args = array(), $suffix = '' ) {
	$username_parts = array();

	if ( isset( $new_user_args['first_name'] ) ) {
		$username_parts[] = sanitize_user( $new_user_args['first_name'], true );
	}

	if ( isset( $new_user_args['last_name'] ) ) {
		$username_parts[] = sanitize_user( $new_user_args['last_name'], true );
	}

	// Remove empty parts.
	$username_parts = array_filter( $username_parts );

	// If there are no parts, e.g. name had unicode chars, or was not provided, fallback to email.
	if ( empty( $username_parts ) ) {
		$email_parts    = explode( '@', $email );
		$email_username = $email_parts[0];

		// Exclude common prefixes.
		if ( in_array(
			$email_username,
			array(
				'sales',
				'hello',
				'mail',
				'contact',
				'info',
			),
			true
		) ) {
			// Get the domain part.
			$email_username = $email_parts[1];
		}

		$username_parts[] = sanitize_user( $email_username, true );
	}

	$username = wc_strtolower( implode( '.', $username_parts ) );

	if ( $suffix ) {
		$username .= $suffix;
	}

	/**
	 * WordPress 4.4 - filters the list of blacklisted usernames.
	 *
	 * @since 3.7.0
	 * @param array $usernames Array of blacklisted usernames.
	 */
	$illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );

	// Stop illegal logins and generate a new random username.
	if ( in_array( strtolower( $username ), array_map( 'strtolower', $illegal_logins ), true ) ) {
		$new_args = array();

		/**
		 * Filter generated customer username.
		 *
		 * @since 3.7.0
		 * @param string $username      Generated username.
		 * @param string $email         New customer email address.
		 * @param array  $new_user_args Array of new user args, maybe including first and last names.
		 * @param string $suffix        Append string to username to make it unique.
		 */

		$new_args['first_name'] = apply_filters(
			'woocommerce_generated_customer_username',
			'woo_user_' . zeroise( wp_rand( 0, 9999 ), 4 ),
			$email,
			$new_user_args,
			$suffix
		);

		return woo_template_create_new_customer_username( $email, $new_args, $suffix );
	}

	if ( username_exists( $username ) ) {
		// Generate something unique to append to the username in case of a conflict with another user.
		$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
		return woo_template_create_new_customer_username( $email, $new_user_args, $suffix );
  }
  

	/**
	 * Filter new customer username.
	 *
	 * @since 3.7.0
	 * @param string $username      Customer username.
	 * @param string $email         New customer email address.
	 * @param array  $new_user_args Array of new user args, maybe including first and last names.
	 * @param string $suffix        Append string to username to make it unique.
	 */
	return apply_filters( 'woocommerce_new_customer_username', $username, $email, $new_user_args, $suffix );


}


/**
 * Create a new customer.
 *
 * @param  string $email    Customer email.
 * @param  string $username Customer username.
 * @param  string $password Customer password.
 * @param  array  $args     List of arguments to pass to `wp_insert_user()`.
 * @return int|WP_Error Returns WP_Error on failure, Int (user ID) on success.
 */
function woo_template_create_new_customer( $email, $username = '', $password = '', $args = array() ) {
  if ( empty( $email ) || ! is_email( $email ) ) {
    return new WP_Error( 'registration-error-invalid-email', __( 'Please provide a valid email address.', 'woocommerce' ) );
  }

  if ( email_exists( $email ) ) {
    return new WP_Error( 'registration-error-email-exists', apply_filters( 'woocommerce_registration_error_email_exists', __( 'An account is already registered with your email address. Please log in.', 'woocommerce' ), $email ) );
  }

  if ( empty( $username ) ) {
    $username = woo_template_create_new_customer_username( $email, $args );
  }

  $username = sanitize_user( $username );

  if ( empty( $username ) || ! validate_username( $username ) ) {
    return new WP_Error( 'registration-error-invalid-username', __( 'Please enter a valid account username.', 'woocommerce' ) );
  }

  if ( username_exists( $username ) ) {
    return new WP_Error( 'registration-error-username-exists', __( 'An account is already registered with that username. Please choose another.', 'woocommerce' ) );
  }

  // Handle password creation.
  $password_generated = false;
  if ( empty( $password ) ) {
    $password           = wp_generate_password();
    $password_generated = true;
  }

  if ( empty( $password ) ) {
    return new WP_Error( 'registration-error-missing-password', __( 'Please enter an account password.', 'woocommerce' ) );
  }

  // Use WP_Error to handle registration errors.
  $errors = new WP_Error();

  do_action( 'woocommerce_register_post', $username, $email, $errors );

  $errors = apply_filters( 'woocommerce_registration_errors', $errors, $username, $email );
  $errors = apply_filters( 'woo_template_registration_errors', $errors, $username, $email );

  if ( $errors->get_error_code() ) {
    return $errors;
  }

  $new_customer_data = apply_filters(
    'woocommerce_new_customer_data',
    array_merge(
      $args,
      array(
        'user_login' => $username,
        'user_pass'  => $password,
        'user_email' => $email,
        'role'       => 'customer',
      )
    )
  );

  $customer_id = wp_insert_user( $new_customer_data );

  if ( is_wp_error( $customer_id ) ) {
    return $customer_id;
  }

  do_action( 'woocommerce_created_customer', $customer_id, $new_customer_data, $password_generated );

  return $customer_id;
}




function woo_template_get_all_user_info( $user_id , $with_password = false ) {

  // we received user_id or user_object ???
  if ( $user_id instanceof WP_User ) {
    $user = $user_id;
  }
  else {
    $user = get_userdata( $user_id );
  }

  if ( is_wp_error( $user ) ) {
    return $user;
  }
  
  // in any case re get the user id
  $user_id = intval($user->ID);

  // get user data array
  $user_data = $user->to_array();
  
  // if password must not be included remove it
  if ( !$with_password ) {
    unset( $user_data['user_pass'] );
  }

  // retrieve meta data
  $shipping = [
    'shipping_first_name'   => get_user_meta( $user_id, 'shipping_first_name' , true),
    'shipping_last_name'    => get_user_meta( $user_id, 'shipping_last_name' , true),
    'shipping_address_1'    => get_user_meta( $user_id, 'shipping_address_1' , true),
    'shipping_city'         => get_user_meta( $user_id, 'shipping_city' , true),
    'shipping_state'        => get_user_meta( $user_id, 'shipping_state' , true),
    'shipping_postcode'     => get_user_meta( $user_id, 'shipping_postcode' , true),
    'shipping_country'      => get_user_meta( $user_id, 'shipping_country' , true),
  ];
      
  $billing = [
    'billing_first_name'  => get_user_meta( $user_id, 'billing_first_name' , true),
    'billing_last_name'   => get_user_meta( $user_id, 'billing_last_name' , true),
    'billing_address_1'   => get_user_meta( $user_id, 'billing_address_1' , true),
    'billing_city'        => get_user_meta( $user_id, 'billing_city' , true),
    'billing_state'       => get_user_meta( $user_id, 'billing_state' , true),
    'billing_postcode'    => get_user_meta( $user_id, 'billing_postcode' , true),
    'billing_country'     => get_user_meta( $user_id, 'billing_country' , true),
    'billing_email'       => get_user_meta( $user_id, 'billing_email' , true),
    'billing_phone'       => get_user_meta( $user_id, 'billing_phone' , true),
  ];

  $custom_meta = [
    'sex'                 => get_user_meta( $user_id, 'sex' , true),
    'birth'               => get_user_meta( $user_id, 'birth' , true),
  ];

  // return merged

  return array(
    'main'        => $user_data,
    'shipping'    => $shipping,
    'billing'     => $billing,
    'custom_meta' => $custom_meta,
  );
    

}

function woo_template_customer_has_a_billing_address( $user_id ) {
    
  // get user data from db
  $userdata = woo_template_get_all_user_info( $user_id );

  // billing address already exists ???
  $all_array_props_empty = function( $array ) {
    foreach ($array as $key => $value) {
      if ( !empty($value)) {
        return false;
      }
    }
    return true;
  };
  $has_saved_billing = !$all_array_props_empty( $userdata['billing'] );
  return $has_saved_billing;
}

function woo_template_update_customer_shipping_fields( $user_id, $shipping ) {
  foreach( $shipping as $key => $val ) {
    update_user_meta( $user_id, $key, $val );
  }
}
function woo_template_update_customer_billing_fields( $user_id, $billing ) {
  foreach( $billing as $key => $val ) {
    update_user_meta( $user_id, $key, $val );
  }
}

?>