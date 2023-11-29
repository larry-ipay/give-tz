<?php
/**
 * List of general function used to process iPay Payment Gateway
 *
 * @since 1.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Check whether iPay gateway is in test mode or not.
 *
 * @since 1.0
 *
 * @return bool
 */
function give_ipay_is_test_mode() {

	$live = 1;

	if (give_get_option('give_ipay_mode', '') == 'test') {
		$live = 0;
	}
	// return apply_filters('give_ipay_is_test_mode', give_is_test_mode());
	return $live;
}

/**
 * Get Payment Method Label.
 *
 * @since 1.0
 *
 * @return string
 */
function give_ipay_get_payment_method_label() {
	return give_get_option('ipay_checkout_label', __('iPay', 'give-ipay'));
}

/**
 * Get iPay merchant credentials.
 *
 * @since 1.0
 *
 * @return array
 */
function give_ipay_get_merchant_credentials() {
	$credentials = array(
		'vendor_id' => give_get_option('give_ipay_vendor_id', ''),
		'hash_key' => give_get_option('give_ipay_hash_key', ''),
		'live' => give_ipay_is_test_mode(),
		'merchant_name' => give_get_option('give_ipay_merchant_name', ''),
		'merchant_country' => give_get_option('give_ipay_country', ''),
	);

	return $credentials;

}


