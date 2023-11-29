<?php
/**
 * Plugin Name: Give - iPay Payment Gateway
 * Plugin URI: https://ipayafrica.com/api/
 * Description: Process online donations via the iPay payment gateway.
 * Author: Moses King'ori
 * Author URI: https://ipayafrica.com
 * Version: 1.0.2
 * Text Domain: give-ipay
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WordImpress/Give-Ipay
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Give_Ipay')):

	final class Give_Ipay {
		/**
		 * Instance.
		 *
		 * @since  1.0
		 * @access static
		 * @var
		 */
		static private $instance;

		/**
		 * Singleton pattern.
		 *
		 * @since  1.0
		 * @access private
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
		 * Setup constants.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return Give_Ipay
		 */
		public function setup_constants() {

			if (!defined('GIVE_IPAY_VERSION')) {
				define('GIVE_IPAY_VERSION', '1.0.2');
			}

			if (!defined('GIVE_IPAY_MIN_GIVE_VER')) {
				define('GIVE_IPAY_MIN_GIVE_VER', '2.1.0');
			}

			if (!defined('GIVE_IPAY_FILE')) {
				define('GIVE_IPAY_FILE', __FILE__);
			}

			if (!defined('GIVE_IPAY_BASENAME')) {
				define('GIVE_IPAY_BASENAME', plugin_basename(GIVE_IPAY_FILE));
			}

			if (!defined('GIVE_IPAY_URL')) {
				define('GIVE_IPAY_URL', plugins_url('/', GIVE_IPAY_FILE));
			}

			if (!defined('GIVE_IPAY_DIR')) {
				define('GIVE_IPAY_DIR', plugin_dir_path(GIVE_IPAY_FILE));
			}

			add_action('init', array($this, 'load_textdomain'));

			return self::$instance;
		}

		/**
		 * Load the text domain.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @return void
		 */
		public function load_textdomain() {

			// Set filter for plugin's languages directory.
			$lang_dir = dirname(GIVE_IPAY_BASENAME) . '/languages/';
			$lang_dir = apply_filters('give_ipay_languages_directory', $lang_dir);

			// Traditional WordPress plugin locale filter.
			$locale = apply_filters('plugin_locale', get_locale(), 'give-ipay');
			$mo_file = sprintf('%1$s-%2$s.mo', 'give-ipay', $locale);

			// Setup paths to current locale file.
			$local_mo_file = $lang_dir . $mo_file;
			$global_mo_file = WP_LANG_DIR . '/give-ipay/' . $mo_file;

			if (file_exists($global_mo_file)) {
				load_textdomain('give-ipay', $global_mo_file);
			} elseif (file_exists($local_mo_file)) {
			load_textdomain('give-ipay', $local_mo_file);
		} else {
			// Load the default language files.
			load_plugin_textdomain('give-ipay', false, $lang_dir);
		}

	}

	/**
	 * Set hooks
	 *
	 * @since  1.0
	 * @access public
	 */
	public function setup() {

		require_once GIVE_IPAY_DIR . 'includes/functions.php';
		require_once GIVE_IPAY_DIR . 'includes/admin/class-admin-settings.php';
		require_once GIVE_IPAY_DIR . 'includes/payment-processing.php';
		require_once GIVE_IPAY_DIR . 'includes/actions.php';
		require_once GIVE_IPAY_DIR . 'includes/filters.php';
		require_once GIVE_IPAY_DIR . 'includes/admin/give-ipay-upgrades.php';
	}
}

endif;

/**
 * Initialize iPay Payment Gateway.
 *
 * @since 1.0
 */
function give_init_ipay() {

	$ipay = Give_Ipay::get_instance();

	// Load constants.
	$ipay->setup_constants();

	if (is_admin()) {
		// Process plugin dependency.
		require_once GIVE_IPAY_DIR . 'includes/admin/plugin-activation.php';
	}

	// Setup plugin.
	if (class_exists('Give')) {
		Give_Ipay::get_instance()->setup();
	}

	// Setup licence.
	if (class_exists('Give_License')) {
		new Give_License(GIVE_IPAY_FILE, 'Ipay', GIVE_IPAY_VERSION, '');
	}
}

add_action('plugins_loaded', 'give_init_ipay');
