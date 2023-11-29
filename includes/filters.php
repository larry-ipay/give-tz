<?php
/**
 * General Filter Hooks
 *
 * @since 1.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Show iPay transaction error.
 *
 * @since 1.0
 *
 * @return bool
 */
function give_ipay_show_error($content) {
	if (
		!isset($_GET['give-ipay-payment'])
		|| 'failed' !== $_GET['give-ipay-payment']
		|| !isset($_GET['give-ipay-error-message'])
		|| empty($_GET['give-ipay-error-message'])
		|| !give_is_failed_transaction_page()
	) {
		return $content;
	}

	return give_output_error(sprintf('Payment Error: %s', base64_decode($_GET['give-ipay-error-message'])), false, 'error') . $content;
}

add_filter('the_content', 'give_ipay_show_error');

