<?php
/**
 * Class Give_Ipay_Admin_Settings
 *
 * @since 1.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Give_Ipay_Admin_Settings')):

	class Give_Ipay_Admin_Settings {
		/**
		 * Instance.
		 *
		 * @since  1.0
		 * @access static
		 *
		 * @var object $instance
		 */
		static private $instance;

		/**
		 * Payment gateways ID
		 *
		 * @since 1.0
		 *
		 * @var string $gateways_id
		 */
		private $gateways_id = '';

		/**
		 * Payment gateways label
		 *
		 * @since 1.0
		 *
		 * @var string $gateways_label
		 */
		private $gateways_label = '';

		/**
		 * Singleton pattern.
		 *
		 * @since  1.0
		 * @access private
		 *
		 * Give_Ipay_Admin_Settings constructor.
		 */
		private function __construct() {
		}

		/**
		 * Get instance.
		 *
		 * @since  1.0
		 * @access static
		 *
		 * @return static
		 */
		static function get_instance() {
			if (null === static::$instance) {
				self::$instance = new static();
			}

			return self::$instance;
		}

		/**
		 * Setup hooks
		 *
		 * @since  1.0
		 * @access public
		 */
		public function setup() {

			$this->gateways_id = 'ipay';
			$this->gateways_label = __('Ipay', 'give-ipay');

			add_filter('give_payment_gateways', array($this, 'register_gateway'));
			add_filter('give_get_settings_gateways', array($this, 'add_settings'));
			add_filter('give_get_sections_gateways', array($this, 'add_gateways_section'));

		}

		/**
		 * Registers the Ipay Payment Gateway.
		 *
		 * @param array $gateways Payment Gateways List.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return mixed
		 */
		public function register_gateway($gateways) {
			$gateways[$this->gateways_id] = array(
				'admin_label' => $this->gateways_label,
				'checkout_label' => give_ipay_get_payment_method_label(),
			);

			return $gateways;
		}

		/**
		 * Adds the Ipay Settings to the Payment Gateways.
		 *
		 * @param array $settings Payment Gateway Settings.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return array
		 */
		public function add_settings($settings) {

			if ($this->gateways_id !== give_get_current_setting_section()) {
				return $settings;
			}

			$ipay_settings = array(
				array(
					'id' => $this->gateways_id,
					'type' => 'title',
				),
				array(
					'id' => 'give_ipay_country',
					'name' => __('Merchant Country', 'give-ipay'),
					'desc' => 'Select the country of operation',
					'type' => 'select',
					'options' => [
						'ke' => 'Kenya',
						'ug' => 'Uganda',
						'tz' => 'Tanzania',
						'tg' => 'Togo',
					],
				),
				array(
					'id' => 'give_ipay_vendor_id',
					'name' => __('Vendor ID', 'give-ipay'),
					'desc' => __('Vendor id parameter provided by iPay', 'give-ipay'),
					'type' => 'text',
					'size' => 'regular',
				),
				array(
					'id' => 'give_ipay_hash_key',
					'name' => __('Hash Key', 'give-ipay'),
					'desc' => __('Hash key parameter provided by iPay', 'give-ipay'),
					'type' => 'api_key',
					'size' => 'regular',
				),
				array(
					'id' => 'give_ipay_merchant_name',
					'name' => __('Merchant Name', 'give-ipay'),
					'desc' => __('This is the name of your business venture', 'give-ipay'),
					'type' => 'text',
					'size' => 'regular',
				),
				array(
					'title' => __('Mode', 'give-ipay'),
					'id' => 'give_ipay_mode',
					'type' => 'radio_inline',
					'options' => array(
						'test' => __('Test', 'give-ipay'),
						'live' => __('Live', 'give-ipay'),
					),
					'default' => 'live',
					'description' => __('This option allows testing iPay integration if set to Test and Live for real transactions.', 'give-ipay'),
				),
				array(
					'title' => __('Collect Billing Details', 'give-ipay'),
					'id' => 'give_ipay_billing_details',
					'type' => 'radio_inline',
					'options' => array(
						'enabled' => __('Enabled', 'give-ipay'),
						'disabled' => __('Disabled', 'give-ipay'),
					),
					'default' => 'disabled',
					'description' => __('This option will enable the billing details section for iPay which requires the donor\'s address to complete the donation. These fields are not required by iPay to process the transaction, but you may have the need to collect the data.', 'give-ipay'),
				),
				array(
					'id' => $this->gateways_id,
					'type' => 'sectionend',
				),
			);

			return $ipay_settings;
		}

		/**
		 * Add iPay to payment gateway section
		 *
		 * @param array $section Payment Gateway Sections.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return mixed
		 */
		public function add_gateways_section($section) {
			$section[$this->gateways_id] = __('iPay', 'give-ipay');
			return $section;
		}
	}

endif;

// Initialize settings.
Give_Ipay_Admin_Settings::get_instance()->setup();
