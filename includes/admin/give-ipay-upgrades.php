<?php
/**
 * iPay Upgrades
 *
 * @package     Give
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.1
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Upgrade script for Give-iPay.
 *
 * @since 1.1
 */
function give_ipay_do_automatic_upgrades() {
	// Already done?
	$did_upgrade = false;
	$give_ipay_version = preg_replace('/[^0-9.].*/', '', get_option('give_ipay_version'));
	$give_ipay_version = !$give_ipay_version ? $give_ipay_version : '1.0';

	// Update gateway label.
	if (version_compare($give_ipay_version, '1.1', '<')) {
		give_ipay_gateway_label_callback();
		$did_upgrade = true;
	}

	if ($did_upgrade) {
		update_option('give_ipay_version', preg_replace('/[^0-9.].*/', '', GIVE_IPAY_VERSION));
	}
}

// Automatic upgrade hook.
add_action('admin_init', 'give_ipay_do_automatic_upgrades');

/**
 * Copy the old iPay gateway label to Give 2.1.0 gateway list option.
 *
 * @since 1.1
 */
function give_ipay_gateway_label_callback() {
	// Get iPay method label.
	$gateway_label_old = give_get_option('give_ipay_checkout_label');

	if ($gateway_label_old) {
		// Gateway's labels.
		$give_gateway_labels = give_get_option('gateways_label');

		if (array_key_exists('ipay', $give_gateway_labels)) {

			// Replace gateway label as per Give 2.1.0 functionality.
			$give_gateway_labels['ipay'] = $gateway_label_old;

			// Update gateway labels.
			give_update_option('gateways_label', $give_gateway_labels);

			// Delete old label Permanently.
			give_delete_option('give_ipay_checkout_label');
		}
	}
}