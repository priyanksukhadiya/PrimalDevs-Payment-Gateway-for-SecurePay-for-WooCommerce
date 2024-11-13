<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://profiles.wordpress.org/priyanksukhadiya/
 * @since             1.0.0
 * @package           PrimalDevs_Payment_Gateway_for_SecurePay_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       PrimalDevs Payment Gateway for SecurePay for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/wc-primaldevs-payment-gateway-securepay
 * Description:       PrimalDevs Payment Gateway for SecurePay for WooCommerce plugin enables store owners to effortlessly accept credit card payments using the SecurePay Gateway
 * Version:           1.0.0
 * Author:            Priyank Sukhadiya
 * Author URI:        https://profiles.wordpress.org/priyanksukhadiya/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-primaldevs-payment-gateway-securepay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PRIMALDEVS_PAYMENT_GATEWAT_FOR_SECUREPAY_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * Initialize PrimalDevs Payment Gateway for SecurePay for WooCommerce after plugins are loaded.
 */
add_action('plugins_loaded', 'primaldevs_securepay_payment_gateway_wc_init');
function primaldevs_securepay_payment_gateway_wc_init()
{
    // Ensure WooCommerce payment gateway classes are available
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    // Load plugin text domain for translations
    load_plugin_textdomain('wc-primaldevs-payment-gateway-securepay', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Include the credit card gateway file
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-primaldevs-securepay-payment-gateway-woocommerce.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-primaldevs-securepay-wc-logger.php';


    /**
     * Register the PrimalDevs Payment Gateway for SecurePay for WooCommerce.
     *
     * @param array $gateways List of available payment gateways.
     * @return array Updated list of payment gateways.
     */
    function primaldevs_securepay_payment_gateway_woocommerce($gateways)
    {
        $gateways[] = 'PrimalDevs_Payment_Gateway_for_SecurePay_WooCommerce';

        return $gateways;
    }
    add_filter('woocommerce_payment_gateways', 'primaldevs_securepay_payment_gateway_woocommerce');
}

