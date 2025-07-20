<?php
/**
 * Plugin Name: Auto-Complete Virtual Orders for WooCommerce
 * Description: Automatically completes WooCommerce orders if they only contain virtual/downloadable products.
 * Version: 1.1
 * Author: Michael Patrick
 * License: GPLv2 or later
 * Requires Plugins: woocommerce
 */

// Hook into plugins_loaded to check for WooCommerce
add_action('plugins_loaded', 'acvo_init_plugin');

function acvo_init_plugin() {
    // Exit if WooCommerce is not active
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'acvo_missing_woocommerce_notice');
        return;
    }

    // Hook into WooCommerce only if it's present
    add_action('woocommerce_thankyou', 'acvo_auto_complete_virtual_orders');
}

function acvo_auto_complete_virtual_orders($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    // Skip if already completed
    if ($order->get_status() === 'completed') return;

    $virtual_order = true;

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (!$product || !$product->is_virtual() || !$product->is_downloadable()) {
            $virtual_order = false;
            break;
        }
    }

    // Auto-complete if all products are virtual/downloadable
    if ($virtual_order) {
        $order->update_status('completed', 'Order auto-completed because it contains only virtual/downloadable products.');
    }
}

function acvo_missing_woocommerce_notice() {
    echo '<div class="notice notice-error"><p><strong>Auto-Complete Virtual Orders for WooCommerce</strong> requires WooCommerce to be installed and active.</p></div>';
}

