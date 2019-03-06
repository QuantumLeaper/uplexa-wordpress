<?php
/*
Plugin Name: uPlexa Woocommerce Gateway
Plugin URI: https://github.com/uplexa/uplexa-wordpress
Description: Extends WooCommerce by adding a uPlexa Gateway
Version: 1.0.0
Tested up to: 4.9.8
Author: uPlexa
Author URI: https://uplexa.com
*/

defined( 'ABSPATH' ) || exit;

// Constants, you can edit these if you fork this repo
define('UPLEXA_GATEWAY_MAINNET_EXPLORER_URL', 'https://explorer.uplexa.com');
define('UPLEXA_GATEWAY_TESTNET_EXPLORER_URL', 'https://explorer.uplexa.com');
define('UPLEXA_GATEWAY_ADDRESS_PREFIX', 0x161f23);
define('UPLEXA_GATEWAY_ADDRESS_PREFIX_INTEGRATED', 0x1661a3);
define('UPLEXA_GATEWAY_ATOMIC_UNITS', 2);
define('UPLEXA_GATEWAY_ATOMIC_UNIT_THRESHOLD', 2); // Amount under in atomic units payment is valid
define('UPLEXA_GATEWAY_DIFFICULTY_TARGET', 120);

// Do not edit these constants
define('UPLEXA_GATEWAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UPLEXA_GATEWAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UPLEXA_GATEWAY_ATOMIC_UNITS_POW', pow(10, UPLEXA_GATEWAY_ATOMIC_UNITS));
define('UPLEXA_GATEWAY_ATOMIC_UNITS_SPRINTF', '%.'.UPLEXA_GATEWAY_ATOMIC_UNITS.'f');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'uplexa_init', 1);
function uplexa_init() {

    // If the class doesn't exist (== WooCommerce isn't installed), return NULL
    if (!class_exists('WC_Payment_Gateway')) return;

    // If we made it this far, then include our Gateway Class
    require_once('include/class-uplexa-gateway.php');

    // Create a new instance of the gateway so we have static variables set up
    new uPlexa_Gateway($add_action=false);

    // Include our Admin interface class
    require_once('include/admin/class-uplexa-admin-interface.php');

    add_filter('woocommerce_payment_gateways', 'uplexa_gateway');
    function uplexa_gateway($methods) {
        $methods[] = 'uPlexa_Gateway';
        return $methods;
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'uplexa_payment');
    function uplexa_payment($links) {
        $plugin_links = array(
            '<a href="'.admin_url('admin.php?page=uplexa_gateway_settings').'">'.__('Settings', 'uplexa_gateway').'</a>'
        );
        return array_merge($plugin_links, $links);
    }

    add_filter('cron_schedules', 'uplexa_cron_add_one_minute');
    function uplexa_cron_add_one_minute($schedules) {
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display' => __('Once every minute', 'uplexa_gateway')
        );
        return $schedules;
    }

    add_action('wp', 'uplexa_activate_cron');
    function uplexa_activate_cron() {
        if(!wp_next_scheduled('uplexa_update_event')) {
            wp_schedule_event(time(), 'one_minute', 'uplexa_update_event');
        }
    }

    add_action('uplexa_update_event', 'uplexa_update_event');
    function uplexa_update_event() {
        uPlexa_Gateway::do_update_event();
    }

    add_action('woocommerce_thankyou_'.uPlexa_Gateway::get_id(), 'uplexa_order_confirm_page');
    add_action('woocommerce_order_details_after_order_table', 'uplexa_order_page');
    add_action('woocommerce_email_after_order_table', 'uplexa_order_email');

    function uplexa_order_confirm_page($order_id) {
        uPlexa_Gateway::customer_order_page($order_id);
    }
    function uplexa_order_page($order) {
        if(!is_wc_endpoint_url('order-received'))
            uPlexa_Gateway::customer_order_page($order);
    }
    function uplexa_order_email($order) {
        uPlexa_Gateway::customer_order_email($order);
    }

    add_action('wc_ajax_uplexa_gateway_payment_details', 'uplexa_get_payment_details_ajax');
    function uplexa_get_payment_details_ajax() {
        uPlexa_Gateway::get_payment_details_ajax();
    }

    add_filter('woocommerce_currencies', 'uplexa_add_currency');
    function uplexa_add_currency($currencies) {
        $currencies['uPlexa'] = __('uPlexa', 'uplexa_gateway');
        return $currencies;
    }

    add_filter('woocommerce_currency_symbol', 'uplexa_add_currency_symbol', 10, 2);
    function uplexa_add_currency_symbol($currency_symbol, $currency) {
        switch ($currency) {
        case 'uPlexa':
            $currency_symbol = 'UPX';
            break;
        }
        return $currency_symbol;
    }

    if(uPlexa_Gateway::use_uplexa_price()) {

        // This filter will replace all prices with amount in uPlexa (live rates)
        add_filter('wc_price', 'uplexa_live_price_format', 10, 3);
        function uplexa_live_price_format($price_html, $price_float, $args) {
            if(!isset($args['currency']) || !$args['currency']) {
                global $woocommerce;
                $currency = strtoupper(get_woocommerce_currency());
            } else {
                $currency = strtoupper($args['currency']);
            }
            return uPlexa_Gateway::convert_wc_price($price_float, $currency);
        }

        // These filters will replace the live rate with the exchange rate locked in for the order
        // We must be careful to hit all the hooks for price displays associated with an order,
        // else the exchange rate can change dynamically (which it should for an order)
        add_filter('woocommerce_order_formatted_line_subtotal', 'uplexa_order_item_price_format', 10, 3);
        function uplexa_order_item_price_format($price_html, $item, $order) {
            return uPlexa_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_formatted_order_total', 'uplexa_order_total_price_format', 10, 2);
        function uplexa_order_total_price_format($price_html, $order) {
            return uPlexa_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_order_item_totals', 'uplexa_order_totals_price_format', 10, 3);
        function uplexa_order_totals_price_format($total_rows, $order, $tax_display) {
            foreach($total_rows as &$row) {
                $price_html = $row['value'];
                $row['value'] = uPlexa_Gateway::convert_wc_price_order($price_html, $order);
            }
            return $total_rows;
        }

    }

    add_action('wp_enqueue_scripts', 'uplexa_enqueue_scripts');
    function uplexa_enqueue_scripts() {
        if(uPlexa_Gateway::use_uplexa_price())
            wp_dequeue_script('wc-cart-fragments');
        if(uPlexa_Gateway::use_qr_code())
            wp_enqueue_script('uplexa-qr-code', UPLEXA_GATEWAY_PLUGIN_URL.'assets/js/qrcode.min.js');

        wp_enqueue_script('uplexa-clipboard-js', UPLEXA_GATEWAY_PLUGIN_URL.'assets/js/clipboard.min.js');
        wp_enqueue_script('uplexa-gateway', UPLEXA_GATEWAY_PLUGIN_URL.'assets/js/uplexa-gateway-order-page.js');
        wp_enqueue_style('uplexa-gateway', UPLEXA_GATEWAY_PLUGIN_URL.'assets/css/uplexa-gateway-order-page.css');
    }

    // [uplexa-price currency="USD"]
    // currency: BTC, GBP, etc
    // if no none, then default store currency
    function uplexa_price_func( $atts ) {
        global  $woocommerce;
        $a = shortcode_atts( array(
            'currency' => get_woocommerce_currency()
        ), $atts );

        $currency = strtoupper($a['currency']);
        $rate = uPlexa_Gateway::get_live_rate($currency);
        if($currency == 'BTC')
            $rate_formatted = sprintf('%.8f', $rate / 1e8);
        else
            $rate_formatted = sprintf('%.5f', $rate / 1e8);

        return "<span class=\"uplexa-price\">1 UPX = $rate_formatted $currency</span>";
    }
    add_shortcode('uplexa-price', 'uplexa_price_func');


    // [uplexa-accepted-here]
    function uplexa_accepted_func() {
        return '<img src="'.UPLEXA_GATEWAY_PLUGIN_URL.'assets/images/uplexa-accepted-here.png" />';
    }
    add_shortcode('uplexa-accepted-here', 'uplexa_accepted_func');

}

register_deactivation_hook(__FILE__, 'uplexa_deactivate');
function uplexa_deactivate() {
    $timestamp = wp_next_scheduled('uplexa_update_event');
    wp_unschedule_event($timestamp, 'uplexa_update_event');
}

register_activation_hook(__FILE__, 'uplexa_install');
function uplexa_install() {
    global $wpdb;
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "uplexa_gateway_quotes";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               order_id BIGINT(20) UNSIGNED NOT NULL,
               payment_id VARCHAR(120) DEFAULT '' NOT NULL,
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               paid TINYINT NOT NULL DEFAULT 0,
               confirmed TINYINT NOT NULL DEFAULT 0,
               pending TINYINT NOT NULL DEFAULT 1,
               created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (order_id)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "uplexa_gateway_quotes_txids";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               payment_id VARCHAR(120) DEFAULT '' NOT NULL,
               txid VARCHAR(64) DEFAULT '' NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               height MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
               PRIMARY KEY (id),
               UNIQUE KEY (payment_id, txid, amount)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "uplexa_gateway_live_rates";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (currency)
               ) $charset_collate;";
        dbDelta($sql);
    }
}
