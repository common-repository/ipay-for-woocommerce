<?php
/*
Plugin Name: iPay for WooCommerce
Description: Plugin to integrate with iPay.
Plugin URI:  https://ipay.lk/integrate-with-us
Author:      iPay
Author URI:  https://ipay.lk
Text Domain: ipay-global-payment-gateway-woocommerce
WC tested up to: 8.8.3
WC requires at least: 7.0.0

Version:     1.2.0
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.txt
*/


include 'includes/constants.php';
include 'includes/class-ipay-utils.php';

include 'includes/class-ipay-activator.php';

include 'statuscheck.php';
include 'includes/payment-status-update.php';

include 'includes/class-ipay-notifications.php';

function init_ipay_global_gateway_woocommerce() {
	include 'class-wc-gateway-ipay.php';
}

function add_ipay_global_gateway_woocommerce($methods){
	$methods[] = 'WC_Payment_Gateway_iPay';
	return $methods;
}

function ipay_gw_wc_links($actions){
	$links = array(
		'<a href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=ipay_gw').'">Settings</a>'
	);
	$actions = array_merge($links, $actions);
	return $actions;
}

function on_ipay_gw_wc_activation(){
	IPay_GW_WC_Activator::on_ipay_activation();
}

function on_init_ipay_gw_wc(){
	$notifications = new Ipay_Notifications();
	$notifications->show_notifications();
}

add_action('plugins_loaded', 'init_ipay_global_gateway_woocommerce');

add_filter('woocommerce_payment_gateways', 'add_ipay_global_gateway_woocommerce');

/**
 * Woocommerce HPOS declaration
 */

 add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_action('rest_api_init', 'register_ipay_global_gw_wc_notification_route');

/**
 * Adding settings page link in plugin page
 */

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'ipay_gw_wc_links');

register_activation_hook( __FILE__, 'on_ipay_gw_wc_activation' );

add_action('admin_init', 'on_init_ipay_gw_wc');