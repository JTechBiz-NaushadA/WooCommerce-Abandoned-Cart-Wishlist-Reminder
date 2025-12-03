<?php
/**
 * Plugin Name: WooCommerce Abandoned Cart & Wishlist Reminder
 * Description: Sends automatic email reminders for abandoned carts and wishlists.
 * Version: 1.0.0
 * Author: Naushad A.
 * Plugin Url: https://github.com/JTechBiz-NaushadA/
 */

if (!defined('ABSPATH')) exit;

define('AC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AC_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Abandoned Cart Settings – WooCommerce Settings Tab
 */
function my_ac_get_settings() {
    return [
        [
            'title' => 'Abandoned Cart Settings',
            'type'  => 'title',
            'id'    => 'my_ac_settings_title',
        ],

        [
            'title'   => 'Reminder Delay (hours)',
            'id'      => 'my_ac_delay_hours',
            'type'    => 'number',
            'default' => '1',
            'desc'    => 'Time to wait before sending abandoned cart email.',
        ],

        [
            'title'   => 'Email Subject',
            'id'      => 'my_ac_email_subject',
            'type'    => 'text',
            'default' => 'You left something in your cart!',
        ],

        [
            'title'   => 'Email Body',
            'id'      => 'my_ac_email_body',
            'type'    => 'textarea',
            'default' => 'Hi {customer_name}, you left items in your cart...',
            'desc'    => 'You can use tags: {customer_name}, {cart_items}, {coupon_code}',
        ],

        [
            'type' => 'sectionend',
            'id'   => 'my_ac_settings_title',
        ],
    ];
}
add_filter('woocommerce_settings_tabs_array', function ($tabs) {
    $tabs['abandoned_carts'] = 'Abandoned Carts';
    return $tabs;
}, 50);
add_action('woocommerce_settings_tabs_abandoned_carts', function () {
    woocommerce_admin_fields(my_ac_get_settings());
});
add_action('woocommerce_update_options_abandoned_carts', function () {
    woocommerce_update_options(my_ac_get_settings());
});


// Include files
require_once AC_PLUGIN_PATH . 'includes/class-ac-activator.php';
require_once AC_PLUGIN_PATH . 'includes/class-ac-deactivator.php';
require_once AC_PLUGIN_PATH . 'includes/class-ac-cart-tracker.php';
require_once AC_PLUGIN_PATH . 'includes/class-ac-emailer.php';
require_once AC_PLUGIN_PATH . 'includes/class-ac-cron.php';
require_once AC_PLUGIN_PATH . 'includes/class-ac-admin.php';
register_activation_hook(__FILE__, array('AC_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('AC_Deactivator', 'deactivate'));

// Initialize plugin
add_action('plugins_loaded', function() {
    // Ensure WooCommerce is active or not
    if ( class_exists('WooCommerce') ) {
        new AC_Cart_Tracker();
        new AC_Emailer();
        new AC_Cron();
        new AC_Admin();
    } else {
        // Admin notice: Regarding WooCommerce not active
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>WooCommerce Abandoned Cart & Wishlist Reminder</strong> requires WooCommerce to be installed and active.</p></div>';
        });
    }
});
