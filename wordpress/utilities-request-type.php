<?php

/* =================================================== 
			UTILITIES => REQUEST TYPE
=================================================== */

/**
 * What type of request is this?
 *
 * @param  string $type admin, ajax, cron or frontend.
 * @return bool
 */
function xxx_is_request( $type ) {
	
	$is_admin = is_admin();
	$is_ajax  = defined( 'DOING_AJAX' );
	$is_cron  = defined( 'DOING_CRON' );
	$is_api   = xxx_is_rest_api_request();
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
function xxx_is_rest_api_request() {
	
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}

	$rest_prefix         = trailingslashit( rest_get_url_prefix() );
	$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $is_rest_api_request;

}