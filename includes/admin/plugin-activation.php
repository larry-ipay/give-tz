<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Give Display Donors Activation Banner
 *
 * Includes and initializes Give activation banner class.
 *
 * @since 1.0
 */
function give_ipay_activation_banner() {

	// Check for if give plugin activate or not.
	$is_give_active = defined('GIVE_PLUGIN_BASENAME') ? is_plugin_active(GIVE_IPAY_BASENAME) : false;

	// Check to see if Give is activated, if it isn't deactivate and show a banner.
	if (current_user_can('activate_plugins') && !$is_give_active) {

		add_action('admin_notices', 'give_ipay_inactive_notice');

		// Don't let this plugin activate.
		deactivate_plugins(GIVE_IPAY_BASENAME);

		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}

		return false;

	}

	// Check for activation banner inclusion.
	if (
		!class_exists('Give_Addon_Activation_Banner')
		&& file_exists(GIVE_IPAY_DIR . 'includes/admin/class-addon-activation-banner.php')
	) {
		include GIVE_IPAY_DIR . 'includes/admin/class-addon-activation-banner.php';
	}

	// Initialize activation welcome banner.
	if (class_exists('Give_Addon_Activation_Banner')) {

		$args = array(
			'file' => GIVE_IPAY_FILE,
			'name' => __('iPay Gateway', 'give-ipay'),
			'version' => GIVE_IPAY_VERSION,
			'settings_url' => admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=ipay'),
			'documentation_url' => 'http://docs.givewp.com/addon-ipay',
			'support_url' => 'https://givewp.com/support/',
			'testing' => false, // Never leave true.
		);

		new Give_Addon_Activation_Banner($args);
	}

	return false;

}

add_action('admin_init', 'give_ipay_activation_banner');

/**
 * Notice for No Core Activation
 *
 * @since 1.0
 */
function give_ipay_inactive_notice() {
	echo '<div class="error"><p>' . __('<strong>Activation Error:</strong> You must have the <a href="https://givewp.com/" target="_blank">Give</a> plugin installed and activated for the iPay add-on to activate.', 'give-ipay') . '</p></div>';
}

/**
 * Notice for min. version violation.
 *
 * @since 1.0
 */
function give_ipay_version_notice() {
	echo '<div class="error"><p>' . sprintf(__('<strong>Activation Error:</strong> You must have <a href="%1$s" target="_blank">Give</a> minimum version %2$s for the iPay add-on to activate.', 'give-ipay'), 'https://givewp.com', GIVE_IPAY_MIN_GIVE_VER) . '</p></div>';
}

/**
 * Plugins row action links
 *
 * @since 1.0
 *
 * @param array $actions An array of plugin action links.
 *
 * @return array An array of updated action links.
 */
function give_ipay_plugin_action_links($actions) {
	$new_actions = array(
		'settings' => sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=ipay'),
			__('Settings', 'give-ipay')
		),
	);

	return array_merge($new_actions, $actions);
}

add_filter('plugin_action_links_' . GIVE_IPAY_BASENAME, 'give_ipay_plugin_action_links');
