<?php
if (!defined('ABSPATH')) exit;

class AC_Cron {

    public function __construct() {
        add_action('ac_check_abandoned_carts', array($this, 'process_carts'));
        if ( ! wp_next_scheduled('ac_check_abandoned_carts') ) {
            wp_schedule_event(time(), 'hourly', 'ac_check_abandoned_carts');
        }
    }

    public function process_carts() {
        global $wpdb;

        $option_like = $wpdb->esc_like('_transient_ac_cart_') . '%';
        $rows = $wpdb->get_results($wpdb->prepare("
            SELECT option_name FROM $wpdb->options
            WHERE option_name LIKE %s
        ", $option_like));

        if ( empty($rows) ) return;

        $emailer = new AC_Emailer();

        foreach ( $rows as $row ) {
            $option_name = $row->option_name;
            $transient_key = str_replace('_transient_', '', $option_name);
            $data = get_transient($transient_key);
            if ( ! $data ) continue;

            // if older than 1 hour -> send (this is configurable later in admin)
            if ( isset($data['timestamp']) && (time() - $data['timestamp']) > HOUR_IN_SECONDS ) {
                // do not send if no email
                if ( empty($data['email']) ) {
                    continue;
                }
                $sent = $emailer->send_abandoned_cart_email($data);
                if ( $sent ) {
                    // mark sent by deleting or setting a flag
                    delete_transient($transient_key);
                }
            }
        }
    }
}
