<?php
if (!defined('ABSPATH')) exit;

class AC_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu'));
    }

    public function add_menu() {
        add_submenu_page('woocommerce', 'Abandoned Carts', 'Abandoned Carts', 'manage_woocommerce', 'ac-abandoned-carts', array($this, 'render_page'));
    }
	
	public function register_settings() {
		register_setting('ac_settings_group', 'ac_delay_hours', array('type'=>'integer','sanitize_callback'=>'intval','default'=>1));
		register_setting('ac_settings_group', 'ac_email_subject', array('type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'You left items in your cart'));
		register_setting('ac_settings_group', 'ac_email_body', array('type'=>'string','sanitize_callback'=>'wp_kses_post','default'=> "<p>We noticed you left items in your cart. Return to complete your purchase.</p>"));
		register_setting('ac_settings_group', 'ac_coupon_enable', array('type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'no'));
		register_setting('ac_settings_group', 'ac_coupon_amount', array('type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'10'));
		register_setting('ac_settings_group', 'ac_coupon_type', array('type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'percent'));
		register_setting('ac_settings_group', 'ac_wishlist_integration_enable', array('type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'no'));
	}

    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Abandoned Carts', 'wc-acr'); ?></h1>
            <p><?php esc_html_e('This is a list view. The cron sends reminder emails for carts inactive for 1 hour.', 'wc-acr'); ?></p>
            <table class="widefat">
                <thead><tr><th><?php esc_html_e('Transient Key', 'wc-acr'); ?></th><th><?php esc_html_e('Email', 'wc-acr'); ?></th><th><?php esc_html_e('Items', 'wc-acr'); ?></th><th><?php esc_html_e('Saved At', 'wc-acr'); ?></th></tr></thead>
                <tbody>
                <?php
                global $wpdb;
                $option_like = $wpdb->esc_like('_transient_ac_cart_') . '%';
                $rows = $wpdb->get_results($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", $option_like));
                if ( $rows ) {
                    foreach ( $rows as $row ) {
                        $transient_key = str_replace('_transient_', '', $row->option_name);
                        $data = get_transient($transient_key);
                        if ( ! $data ) continue;
                        $items = is_array($data['cart']) ? count($data['cart']) : 0;
                        echo '<tr>';
                        echo '<td>' . esc_html($transient_key) . '</td>';
                        echo '<td>' . esc_html(isset($data['email']) ? $data['email'] : '') . '</td>';
                        echo '<td>' . esc_html($items) . '</td>';
                        echo '<td>' . esc_html( date('Y-m-d H:i:s', isset($data['timestamp']) ? $data['timestamp'] : 0) ) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="4">' . esc_html__('No abandoned carts found.', 'wc-acr') . '</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
