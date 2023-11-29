<?php
/**
 * Functions to trigger payment processing using iPay Gateway.
 *
 * @since 1.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Listen iPay IPN
 *
 * @since 1.0
 */
function give_ipay_callback() {

	if (isset($_GET['give-listener']) && 'ITN' === $_GET['give-listener']) {

		/**
		 * Action Hook to verify iPay ITN.
		 *
		 * @since 1.0
		 */
		do_action('give_verify_ipay_ipn');
	}

}

add_action('init', 'give_ipay_callback');

/**
 * Process iPay IPN
 *
 * @since 1.0
 */
function give_ipay_process_ipn() {

	// Verify that data received using POST Method only.
	if (isset($_SERVER['REQUEST_METHOD']) && 'GET' !== $_SERVER['REQUEST_METHOD']) {
		return false;
	}

	// Let iPay Know that IPN is received.
	// header('HTTP/1.0 200 OK');
	// flush();

	// Define Required Variables.
	$ipay_error = false;
	$ipay_done = false;
	$processed_data = array();
	$ipay_error_message = '';

	// Posted variables from IPN.
	$processed_data = wp_parse_args(give_clean($_GET), $processed_data);

	// Bail Out, if there is no processed data from iPay.
	if (false === $processed_data) {
		$ipay_error = true;
		$ipay_error_message = __('iPay Response is empty.', 'give-ipay');
	}

	// Bail Out, if amount doesn't match.
	$donation = new Give_Payment($processed_data['id']);
	if (abs(floatval($donation->total) - floatval($processed_data['mc'])) > 0.01) {
		$ipay_error = true;
		$ipay_error_message = __('Actual Donation Amount and iPay Response Amount doesn\'t match', 'give-ipay');
	}

	if ($ipay_error) {

		give_record_gateway_error(
			__('iPay Error', 'give-ipay'),
			$ipay_error_message,
			absint($processed_data['m_payment_id'])
		);

	} elseif (!$ipay_done) {

		$donation_id = absint($processed_data['id']);
		$transaction_id = $processed_data['id'];

		// Get Merchant Details.
		$merchant = give_ipay_get_merchant_credentials();

		print_r($merchant);

		$val1 = sanitize_text_field($processed_data['id']);
		$val2 = sanitize_text_field($processed_data['ivm']);
		$val3 = sanitize_text_field($processed_data['qwh']);
		$val4 = sanitize_text_field($processed_data['afd']);
		$val5 = sanitize_text_field($processed_data['poi']);
		$val6 = sanitize_text_field($processed_data['uyt']);
		$val7 = sanitize_text_field($processed_data['ifd']);

		$ipn_base = "";

		switch ($merchant['merchant_country']) {
		case 'ke':
			$ipn_base = "https://www.ipayafrica.com/ipn";
			break;

		// case 'tz':
		// 	$ipn_base = "https://payments.elipa.co.tz/v3/tz/ipn";
		// 	break;

		case 'ug':
			$ipn_base = "https://payments.elipa.co.ug/v3/ug/ipn";
			break;

		case 'tg':
			$ipn_base = "https://payments.elipa.tg/v1/tg/index.php/ipn/check";
			break;

		default:
			echo "Unknown country of operation";
			exit();
		
		}

		$ipnurl = $ipn_base . "?vendor=" . $merchant['vendor_id'] . "&id=" . $val1 . "&ivm=" . $val2 . "&qwh=" . $val3 . "&afd=" . $val4 . "&poi=" . $val5 . "&uyt=" . $val6 . "&ifd=" . $val7;

		$status = '';

		if ($merchant['live'] == 0) {
			$status = 'aei7p7yrx4ae34';
		} else {
			$fp = fopen($ipnurl, "rb");
			$status = stream_get_contents($fp, -1, -1);
			fclose($fp);
		}

		switch ($status) {
		case 'fe2707etr5s4wq':
		case 'dtfi4p7yty45wq':
			// Failed or Less
			// Record Payment Gateway Error.
			give_record_gateway_error(
				__('iPay Error. Transaction Failed for donation %s.', 'give-ipay'),
				$donation_id
			);

			// Set Donation Status to Failed.
			give_update_payment_status($donation_id, 'failed');

			// Remove Scheduled Cron Hook, when donation is failed.
			wp_clear_scheduled_hook('give_ipay_set_donation_abandoned', array($donation_id));

			// Send Donor to Failed Donation Page.
			wp_redirect(give_get_failed_transaction_uri());
			break;
		case 'aei7p7yrx4ae34':
		case 'eq3i7p5yt7645e':
			// Successful or More
			// Insert Donation Note.
			give_insert_payment_note(
				$donation_id,
				sprintf(
					__('Transaction Successful. iPay Transaction ID: %s', 'give-ipay'),
					$transaction_id
				)
			);

			// Set Transaction ID to Processed Donation.
			give_set_payment_transaction_id($donation_id, $transaction_id);

			// Set Donation Status to Complete.
			give_update_payment_status($donation_id, 'complete');

			// Remove Scheduled Cron Hook, when donation is complete.
			wp_clear_scheduled_hook('give_ipay_set_donation_abandoned', array($donation_id));

			// Send Donor to Success Page.
			give_send_to_success_page();
			break;
		case 'bdi6p2yy76etrs':
			// Pending
			// Record Payment Gateway Error.
			give_record_gateway_error(
				__('iPay Error. Transaction Pending for donation %s', 'give-ipay'),
				$donation_id
			);

			// Remove Scheduled Cron Hook, when donation is pending.
			wp_clear_scheduled_hook('give_ipay_set_donation_abandoned', array($donation_id));

			// Send Donor to Failed Donation Page.
			wp_redirect(give_get_failed_transaction_uri());
			break;
		case 'cr5i3pgy9867e1':
			// Used
			# code...
			break;

		default:
			# code...
			break;
		}
	}

	wp_redirect(home_url());
	exit;
}

add_action('give_verify_ipay_ipn', 'give_ipay_process_ipn');



/**
 * Processes the payment
 *
 * @param array $data List of Donation Data.
 *
 * @since  1.0
 */
function give_ipay_process_donation($data) {

	// Check for any stored errors.
	$errors = give_get_errors();
	if (!$errors) {

		// Setup the donation details which need to send to iPay.
		$data_to_send = array(
			'price' => $data['price'],
			'give_form_title' => $data['post_data']['give-form-title'],
			'give_form_id' => intval($data['post_data']['give-form-id']),
			'give_price_id' => isset($data['post_data']['give-price-id']) ? $data['post_data']['give-price-id'] : '',
			'date' => $data['date'],
			'user_email' => $data['user_email'],
			'purchase_key' => $data['purchase_key'],
			'currency' => give_get_currency(),
			'user_info' => $data['user_info'],
			'status' => 'pending',
			'gateway' => $data['gateway'],
		);

		// Record the pending payment.
		$donation = give_insert_payment($data_to_send);

		// Verify donation payment.
		if (!$donation) {

			// Record the error.
			give_record_gateway_error(
				__('Payment Error', 'give-ipay'),
				/* translators: %s: payment data */
				sprintf(__('Payment creation failed before process iPay. Payment data: %s', 'give-ipay'), wp_json_encode($data)),
				$donation
			);

			// Problems? Send back.
			give_send_back_to_checkout('?payment-mode=' . $data['post_data']['give-ipay']);
		}

		// Auto set payment to abandoned in one hour if donor is not able to donate in that time.
		wp_schedule_single_event(current_time('timestamp', 1) + HOUR_IN_SECONDS, 'give_ipay_set_donation_abandoned', array($donation));

		// Get Merchant Details.
		$merchant = give_ipay_get_merchant_credentials();

		$param = array();

		$mer = $merchant['merchant_name'];
		$tel = $data['post_data']['give_phone'];

		$tel = str_replace("-", "", $tel);
		$tel = str_replace(array(' ', '<', '>', '&', '{', '}', '*', "+", '!', '@', '#', "$", '%', '^', '&'), "", $tel);
		$eml = $data['post_data']['give_email'];
		$live = $merchant['live'];
		$vid = $merchant['vendor_id'];
		$oid = $donation;
		$inv = $oid;
		$p1 = '';
		$p2 = '';
		$p3 = '';
		$p4 = '';

		$curr = give_get_currency();

		$ttl = $data['price'];

		$crl = '0';
		$cst = '1';
		$callbk = get_site_url() . '/?give-listener=ITN';
		$cbk = $callbk;
		$hsh = $merchant['hash_key'];

		$datastring = $live . $oid . $inv . $ttl . $tel . $eml . $vid . $curr . $p1 . $p2 . $p3 . $p4 . $cbk . $cst . $crl;
		$hash_string = hash_hmac('sha1', $datastring, $hsh);
		$hash = $hash_string;

		// $ipayUrl = '';

		// switch ($merchant['merchant_country']) {
		// case 'ke':
		// 	$ipayUrl = 'https://payments.ipayafrica.com/v3/ke';
		// 	break;
		// case 'ug':
		// 	$ipayUrl = 'https://payments.elipa.co.ug/v3/ug';
		// 	break;
		// case 'tz':
		// 	wp_redirect(get_permalink(get_page_by_path('donate-now')->ID));
		// 	exit();
		// 	break;
		// case 'tg':
		// 	$ipayUrl = 'https://payments.elipa.tg/v1/v3/index.php/togo';
		// 	break;
		// }

		// $url = $ipayUrl . "?live=" . $live . "&oid=" . $oid . "&inv=" . $inv . "&ttl=" . $ttl . "&tel=" . $tel . "&eml=" . $eml . "&vid=" . $vid . "&curr=" . $curr . "&p1=" . $p1 . "&p2=" . $p2 . "&p3=" . $p3 . "&p4=" . $p4 . "&cbk=" . $cbk . "&cst=" . $cst . "&crl=" . $crl . "&hsh=" . $hash . "&autopay=" . $autopay;

		// wp_redirect($url);
		exit();
	}

	// If errors are present, send the user back to the purchase page so they can be corrected.
	give_send_back_to_checkout("?payment-mode={$data['gateway']}&form-id={$data['post_data']['give-form-id']}");
}

add_action('give_gateway_ipay', 'give_ipay_process_donation');


