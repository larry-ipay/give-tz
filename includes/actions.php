<?php
/**
 * General Action Hooks
 *
 * @since 1.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Print cc field in donation form conditionally.
 *
 * @param int $form_id Donation Form ID.
 *
 * @since 1.0
 *
 * @return bool
 */
function give_ipay_cc_form_callback($form_id) {

	if (give_is_setting_enabled(give_get_option('ipay_billing_details'))) {
		give_default_cc_address_fields($form_id);
		return true;
	}

	return false;
}

add_action('give_ipay_cc_form', 'give_ipay_cc_form_callback');

/**
 * Auto set pending payment to abandoned.
 *
 * @since 1.0
 *
 * @param int $payment_id
 */
function give_ipay_set_donation_abandoned_callback($payment_id) {
	/**
	 * @var Give_Payment $payment Payment object.
	 */
	$payment = new Give_Payment($payment_id);

	if ('pending' === $payment->status) {
		$payment->update_status('abandoned');
	}
}

add_action('give_ipay_set_donation_abandoned', 'give_ipay_set_donation_abandoned_callback');

/**
 * Add donor meta field to form
 *
 * @param int $form_id Donation Form ID.
 */
function give_add_donor_phone_form_field($form_id) {
	?>
	<p id="give-email-wrap" class="form-row form-row-wide">
		<label class="give-label" for="give-email">
			<?php esc_html_e('Phone', 'give');?>
			<?php if (give_field_is_required('give_phone', $form_id)): ?>
				<span class="give-required-indicator">*</span>
			<?php endif?>
			<?php echo Give()->tooltips->render_help(__('We will use this as well to personalize your account experience.', 'give')); ?>
		</label>

		<input
			class="give-input required"
			type="text"
			name="give_phone"
			autocomplete="phone"
			placeholder="<?php esc_html_e('Phone', 'give');?>"
			id="give-email"
			value="<?php isset($_POST['give_phone']) ? give_clean($_POST['give_phone']) : '';?>"
			required=""
			aria-required="true"
		>

	</p>
	<?php
}
add_action('give_donation_form_after_email', 'give_add_donor_phone_form_field');

/**
 * Set donor phone form field as required
 *
 * @param array $required_fields List of required fields.
 * @param int   $form_id         Donation Form ID.
 *
 * @return array
 */
function give_required_donor_phone_form_field($required_fields, $form_id) {

	$required_fields['give_phone'] = array(
		'error_id' => 'invalid_phone',
		'error_message' => __('Please enter phone number.', 'give'),
	);

	return $required_fields;
}
add_action('give_donation_form_required_fields', 'give_required_donor_phone_form_field', 10, 2);

/**
 * Save phone number to donation and donor meta
 * Note: donor phone will update in donor meta if donor changes the phone number.
 * So on a second donation with a new number, the old number will be changed in the DONOR meta,
 * but the donation meta of the first donation will have the old number.
 *
 * @param int $donation_id Donation ID.
 */
function give_save_donor_phone_number($donation_id) {

	$donor_id = give_get_payment_donor_id($donation_id);
	$new_phone_number = give_clean($_POST['give_phone']);
	$phone_numbers = Give()->donor_meta->get_meta($donor_id, 'give_phone');

	// Add phone number to donor meta only if not exist.
	if (!in_array($new_phone_number, $phone_numbers, true)) {
		Give()->donor_meta->add_meta($donor_id, 'give_phone', $new_phone_number);
	}

	// Save phone number to donation meta.
	Give()->payment_meta->update_meta($donation_id, '_give_phone', $new_phone_number);
}
add_action('give_insert_payment', 'give_save_donor_phone_number', 10);

/**
 * Show donor phone numbers on donor profile
 *
 * @param Give_Donor $donor Donor Object.
 */
function give_show_donor_phone_numbers($donor) {
	$phone_numbers = $donor->get_meta('give_phone', false);
	?>
	<div id="donor-address-wrapper" class="donor-section clear">
		<h3><?php esc_html_e('Phone Numbers', 'give');?></h3>

		<div class="postbox">
			<div class="inside">
				<?php if (empty($phone_numbers)): ?>
					<?php esc_html_e('This donor does not have any phone number saved.', 'give');?>
				<?php else: ?>
					<?php foreach ($phone_numbers as $phone_number): ?>
						<?php echo $phone_number; ?><br>
					<?php endforeach;?>
				<?php endif;?>
			</div>
		</div>
	</div>
	<?php
}
add_action('give_donor_before_address', 'give_show_donor_phone_numbers');

/**
 * This function will add a new custom field to export.
 *
 * @param array $default_columns List of default columns.
 *
 * @return array
 */
function give_add_custom_column_to_export_donor($default_columns) {

	$default_columns['phone_number'] = esc_html__('Phone Number', 'give');

	return $default_columns;
}
add_filter('give_export_donors_get_default_columns', 'give_add_custom_column_to_export_donor');

/**
 * This function will be used to set the value of new custom field which will be displayed in exported CSV.
 *
 * @param array      $data  List of data which is displayed in exported CSV.
 * @param Give_Donor $donor Donor Object.
 *
 * @return mixed
 */
function give_export_set_custom_donor_data($data, $donor) {

	$phone_number = Give()->donor_meta->get_meta($donor->id, 'give_phone', true);
	$data['phone_number'] = !empty($phone_number) ? $phone_number : '- N/A - ';

	return $data;
}
add_filter('give_export_set_donor_data', 'give_export_set_custom_donor_data', 10, 2);






/**
 * Enqueue TZ SDK
 */
function enqueue_tz_sdk(){

	wp_enqueue_script('jquery');

	$donation_page = get_page_by_path('donate-now');

	if($donation_page && is_page($donation_page->ID)){
		error_log("enQ Tz SDK");
		wp_enqueue_script('tz_script','https://www.tz.elipa.global/js_sdk/v1/tz-sdk-v1.js', array('jquery'),'1.0',true);
	}
}

add_action('wp_enqueue_scripts','enqueue_tz_sdk');

function register_checkout_custom_script(){

	wp_enqueue_script('jquery');

	wp_register_script('checkout-script',plugins_url('/../',__FILE__).'js/custom_checkout.js', array('jquery'),'1.0',true);

}

add_action('wp_enqueue_scripts','register_checkout_custom_script');

/**
 * Enqeue script for TZ SDK
 */

 function enqueue_custom_script($data){

	$donation_page = get_page_by_path('donate-now');

	if($donation_page && is_page($donation_page->ID)){
		error_log(json_encode($donation_page));
		error_log("custom script before page");
		error_log("enQ custom script within page");
	wp_enqueue_script('checkout-script',plugins_url('/../',__FILE__).'js/custom_checkout.js',array('jquery'),'1.0',true);

	// $data_to_send = array(
	// 	'price' => $data['price'],
	// 	'give_form_title' => $data['post_data']['give-form-title'],
	// 	'give_form_id' => intval($data['post_data']['give-form-id']),
	// 	'give_price_id' => isset($data['post_data']['give-price-id']) ? $data['post_data']['give-price-id'] : '',
	// 	'date' => $data['date'],
	// 	'user_email' => $data['user_email'],
	// 	'purchase_key' => $data['purchase_key'],
	// 	'currency' => give_get_currency(),
	// 	'user_info' => $data['user_info'],
	// 	'status' => 'pending',
	// 	'gateway' => $data['gateway'],
	// );

	// // Record the pending payment.
	// $donation = give_insert_payment($data_to_send);

	// // Verify donation payment.
	// if (!$donation) {

	// 	// Record the error.
	// 	give_record_gateway_error(
	// 		__('Payment Error', 'give-ipay'),
	// 		/* translators: %s: payment data */
	// 		sprintf(__('Payment creation failed before process iPay. Payment data: %s', 'give-ipay'), wp_json_encode($data)),
	// 		$donation
	// 	);

	// 	// Problems? Send back.
	// 	give_send_back_to_checkout('?payment-mode=' . $data['post_data']['give-ipay']);
	// }
	// $merchant = give_ipay_get_merchant_credentials();

	// $orderDetails = array(
	// 	'total' => $data['price'],
	// 	'oid'=>$donation,
	// 	'user_email' => $data['user_email'],
	// 	'currency' => $data_to_send['currency'],
	// 	'vid'=> $merchant['vendor_id'],
	// 	'ajax_url' => admin_url("admin-ajax.php"),
	// 	'hashKey' => $merchant['hash_key'],
	// 	'site_url'=>get_site_url(),
	// 	'nonce'=>wp_create_nonce(10),
	// );

	// wp_localize_script('checkout-script','paymentData',$orderDetails);
	}
}

// if(is_page(get_option('donation_page_id'))){
	add_action('wp_enqueue_scripts','enqueue_custom_script');
// }
