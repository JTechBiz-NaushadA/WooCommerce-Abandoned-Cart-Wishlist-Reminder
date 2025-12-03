<?php
if (!defined('ABSPATH')) exit;

class AC_Activator {
    public static function activate() {
        if (!wp_next_scheduled('ac_check_abandoned_carts')) {
            wp_schedule_event(time(), 'hourly', 'ac_check_abandoned_carts');
        }
    }
}
