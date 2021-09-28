<?php

/**
 * Given a __DIR__ returns the same path but as URL, 
 * form '/User/xx/path' to 'http://site.com/path'
 *
 * @param [type] $__DIR__
 * @return void
 */
function woo_template_get_live_url_of_local_dir( $__DIR__ ) {
  return get_stylesheet_directory_uri() . woo_template_get_partial_path_of_local_dir( $__DIR__ );
}

/**
 * Given a __DIR__ returns the same path but without the initial part until theme directory
 * form '/User/x/x/x/theme-dir/path' to '/path'
 *
 * @param [type] $__DIR__
 * @return void
 */
function woo_template_get_partial_path_of_local_dir( $__DIR__ ) {
	$this_folder_partial = str_replace ( TEMPLATEPATH, '', $__DIR__ );
	return $this_folder_partial;
}


/**
 * Unset Query var
 *
 * @param string $name
 * @return void
 */
function unset_query_var($name = '') {
	
	global $wp_query;
	if ( !$wp_query ) { return;}
	if ( !is_string($name) || empty($name) ) {return;}
	if ( !isset($wp_query->query_vars) ) {return;}
	if ( !isset($wp_query->query_vars[$name]) ) {return;}
	
	unset($wp_query->query_vars[$name]);

}

/**
 * Get Option, return true or false (bool).
 *
 * @param string $option_id
 * @return bool
 */
function woo_template_get_option_bool( $option_id = '' , $default_if_not_exists = false ) {

	/*Tips for developing this function
	A option of type bool: 
		never saved to db    returns (bool) false
		saved active         retuns (string) "1"
		saved inactive       retuns (string) ""
	*/

	$state = get_option( $option_id );
	
	if ( false === $state) {
		// never saved
		return $default_if_not_exists;
	}
	
	if ( '1' === $state ) {
		//active
		return true;
	}
	
	if ( '' === $state ) {
		//inactive
		return false;
	}


}



/**
 * Get Current Page "Slug"
 *
 * @return string
 */
function get_current_page_slug() {
	$slug = null;
  if (is_front_page()) {
    $slug = 'home';
    return $slug;
  }
  global $wp;
  $slug = $wp->request;
  return $slug;
}


/**
 * Get PAge ID by slug
 *
 * @return string
 */
function get_page_id_by_slug($page_slug) {
	$page = get_page_by_path($page_slug);
	if ($page) {
		return $page->ID;
	} else {
		return null;
	}
}

/**
 * Get Permalink of Page by Slug
 *
 * @return string
 */
function get_page_permalink_by_slug( $page_slug ) {
	$page_id = get_page_id_by_slug( $page_slug );
	if (null === $page_id ) {
		return null;
	}
	$permalink = get_permalink( $page_id );
	return $permalink;
}


/**
 * Remove slash from end of a link (string), if any, and return
 *
 * @param [type] $link_string
 * @return string
 */
function ensure_link_ends_without_slash( $link_string ) {
	$to_remove = ['/', '\\'];
	foreach ($to_remove as $char) {
		if ( $link_string[ (strlen($link_string) - 1) ] === $char ) {
			$link_string = substr( $link_string, 0, ( strlen($link_string) - 1 ) );
		}
	}
	return $link_string;
}


/**
 * Generate Random Number - with digits count passed.Default 6 digits. 
 *
 * @param integer $digits
 * @return integer
 */
function woo_template_generate_random_number( $digits = 6 ) {

	$one   = random_int(100000, 999999);
	$two   = random_int(100000, 999999);
	$three = random_int(25, 180);

	$final = $one * $two * $three;
	$final = intval( $final );
	$final = strval( $final );

	$length = strlen( $final); 

	if ( $length === $digits) {
	}
	else if ( $length < $digits ) {
		$missing = $digits - $length;
		for ( $i = 0; $i < $missing; $i++ ) {
			$final .= strval(random_int(0, 9));
		}
	} 
	else if ( $length > $digits ) {
		$final = substr( $final, 0, $digits );
	}

	return intval($final);



}


/**
 * Call Get Template Part, but before, set some query vars, and after, remove them
 *
 * @param [type] $template
 * @param array $args
 * @return void
 */
function get_template_part_with_query_vars( $template, $args = array() ) {
	if ( !empty( $args )) {
		foreach ($args as $name => $value) {
			set_query_var( $name, $value );
		}
	}

	get_template_part( $template );

	if ( !empty( $args )) {
		foreach ($args as $name => $value) {
			unset_query_var( $name );
		}
	}
}


/**
 * This Plugin is Actived ?
 *
 * @param string $plugin_name
 * @return void
 */
function woo_template_plugin_is_active( $plugin_name = '' ) {

	$plugin_name = strtolower( trim( $plugin_name ) );

	$plugin_file_name = '';

	switch ($plugin_name) {
		case 'wc':
		case 'woocommerce':
			$plugin_file_name = 'woocommerce/woocommerce.php';
			break;
		case 'yith-wishlist':
		case 'yithwishlist':
			$plugin_file_name = 'yith-woocommerce-wishlist/init.php';
			break;
		case 'acf':
			$plugin_file_name = 'advanced-custom-fields/acf.php';
			break;
		case 'acfpro':
		case 'acf-pro':
			$plugin_file_name = 'advanced-custom-fields-pro/acf.php';
			break;			
	}

	if ( empty( $plugin_file_name ) ) {
		return false;
	}

	$is_active = in_array( $plugin_file_name, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );

	return $is_active;

}


/**
 * What type of request is this?
 *
 * @param  string $type admin, ajax, cron or frontend.
 * @return bool
 */
function woo_template_is_request( $type ) {
	
	$is_admin = is_admin();
	$is_ajax  = defined( 'DOING_AJAX' );
	$is_cron  = defined( 'DOING_CRON' );
	$is_api   = woo_template_is_rest_api_request();
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
function woo_template_is_rest_api_request() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}

	$rest_prefix         = trailingslashit( rest_get_url_prefix() );
	$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $is_rest_api_request;
}

/* =================================================== 
			CREATE PAGE PROGRAMMAICALLY
=================================================== */
function woo_template_maybe_create_page( $page_title, $page_content ) {
	
	if ( empty($page_title) ) {
		return false;
	}

	// prepare data
  $page_title = ucwords( $page_title );
  $page_name  = strtolower( str_replace( ' ', '-', trim( $page_title ) ) );
  
  // Check if the page already exists
	$page = get_page_by_title( $page_title, 'OBJECT', 'page' );
	
	$page_exists = !empty( $page );
  if ( $page_exists ) {
    return $page->ID;
	}
	
	// create page

  $args = array(
    'comment_status' => 'close',
    'ping_status'    => 'close',
    'post_author'    => 1,
    'post_title'     => $page_title,
    'post_name'      => $page_name,
    'post_status'    => 'publish',
    'post_content'   => $page_content,
    'post_type'      => 'page',
    // 'post_parent'    => 'id_of_the_parent_page_if_it_available'
  );

	$page_id = wp_insert_post( $args );
	
	return $page_id;

}


?>