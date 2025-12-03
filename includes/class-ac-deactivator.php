<?php
if (!defined('ABSPATH')) exit;

class AC_Deactivator {
    public static function deactivate() {
        wp_clear_scheduled_hook('ac_check_abandoned_carts');
    }
}
