<?php
if (!defined('ABSPATH')) exit;

class AC_Emailer {

    public function __construct() {
        // code is awesome
    }
	
	private function create_coupon($amount='10', $type='percent') {
		if ( ! class_exists('WC_Coupon') ) return '';

		$code = 'ACR-' . strtoupper( wp_generate_password(6, false, false) );

		$coupon = new WC_Coupon();
		try {
			$coupon->set_code( $code );
			$coupon->set_amount( $amount );
			$coupon->set_discount_type( $type === 'percent' ? 'percent' : 'fixed_cart' );
			$coupon->set_date_expires( ( time() + 7 * DAY_IN_SECONDS ) ); // 7 days
			$coupon->set_individual_use( true );
			$coupon->set_usage_limit(1);
			$coupon->save();
			return $code;
		} catch ( Exception $e ) {
			return '';
		}
	}

    public function send_abandoned_cart_email($cart_data) {
        $to = isset($cart_data['email']) ? $cart_data['email'] : '';
        if ( ! $to ) return false;

        // prepare variables for template
        $cart_items = isset($cart_data['cart']) ? $cart_data['cart'] : array();
        $customer_name = isset($cart_data['name']) ? $cart_data['name'] : '';

        ob_start();
        // make $cart_items and $customer_name available to template
        include AC_PLUGIN_PATH . 'includes/templates/email-abandoned-cart.php';
        $body = ob_get_clean();

        $subject = apply_filters('ac_email_subject', __('You left items in your cart', 'wc-acr'));

        $headers = array('Content-Type: text/html; charset=UTF-8');

        return wp_mail($to, $subject, $body, $headers);
    }
}
