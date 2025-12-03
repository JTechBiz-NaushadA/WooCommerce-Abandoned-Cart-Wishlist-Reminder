<?php
if (!defined('ABSPATH')) exit;

class AC_Cart_Tracker {

    public function __construct() {
        add_action('woocommerce_add_to_cart', array($this, 'save_cart'), 10, 6);
        add_action('woocommerce_cart_updated', array($this, 'save_cart'));
        add_action('woocommerce_checkout_order_processed', array($this, 'clear_cart_on_purchase'), 10, 1);
        // capture guest email on checkout fields
        add_action('woocommerce_checkout_update_order_meta', array($this, 'maybe_store_guest_email'), 10, 2);
		
		if ( has_action('yith_wcwl_added_to_wishlist') !== false ) {
			add_action('yith_wcwl_added_to_wishlist', array($this, 'track_wishlist'), 10, 2);
		}
		if ( has_action('tinvwl_added_to_wishlist') !== false ) {
			add_action('tinvwl_added_to_wishlist', array($this, 'track_wishlist'), 10, 2);
		}
    }

    public function save_cart() {
        if ( ! WC()->cart ) return;
        if ( WC()->cart->is_empty() ) return;

        $data = array(
            'cart' => $this->serialize_cart( WC()->cart->get_cart() ),
            'timestamp' => time(),
            'email' => $this->get_user_email(),
        );

        $key = 'ac_cart_' . $this->get_cart_key();
        set_transient($key, $data, 48 * HOUR_IN_SECONDS);
    }

    private function serialize_cart($cart) {
        $out = array();
        foreach ($cart as $key => $item) {
            // minimal cart item info
            $out[] = array(
                'product_id' => $item['product_id'],
                'variation_id' => isset($item['variation_id']) ? $item['variation_id'] : 0,
                'quantity' => $item['quantity'],
                'name' => isset($item['data']) ? $item['data']->get_name() : '',
            );
        }
        return $out;
    }

    private function get_user_email() {
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            return $user->user_email;
        }
        // try session (guest) - plugin stores guest email at checkout via maybe_store_guest_email
        if ( isset(WC()->session) ) {
            $guest = WC()->session->get('ac_guest_email');
            if ( $guest ) return $guest;
        }
        return '';
    }

    private function get_cart_key() {
        if ( is_user_logged_in() ) {
            return 'user_' . get_current_user_id();
        }
        if ( isset(WC()->session) ) {
            return 'guest_' . WC()->session->get_session_id();
        }
        return 'unknown_' . md5( wp_rand() );
    }

    public function maybe_store_guest_email($order_id, $posted = null) {
        // store billing email into session to attribute cart for guests
        $order = wc_get_order($order_id);
        if ( $order ) {
            $email = $order->get_billing_email();
            if ( $email && isset(WC()->session) ) {
                WC()->session->set('ac_guest_email', $email);
                // also save cart transient immediately
                $this->save_cart();
            }
        }
    }

    public function clear_cart_on_purchase($order_id) {
        // Clear transient for the purchaser
        if ( is_user_logged_in() ) {
            $key = 'ac_cart_user_' . get_current_user_id();
        } else {
            $key = 'ac_cart_guest_' . ( isset(WC()->session) ? WC()->session->get_session_id() : '' );
        }
        delete_transient($key);
    }
	public function track_wishlist() {
		$args = func_get_args();
		$product_id = 0;
		foreach ($args as $a) {
			if ( is_numeric($a) && $a > 0 ) {
				$product_id = intval($a);
				break;
			} elseif ( is_object($a) && isset($a->ID) ) {
				$product_id = intval($a->ID);
				break;
			} elseif ( is_array($a) && isset($a['product_id']) ) {
				$product_id = intval($a['product_id']);
				break;
			}
		}

		$user_email = '';
		$user_id_key = '';
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$user_email = $user->user_email;
			$user_id_key = 'user_' . $user->ID;
		} else if ( isset(WC()->session) ) {
			$user_email = WC()->session->get('ac_guest_email');
			$user_id_key = 'guest_' . WC()->session->get_session_id();
		} else {
			$user_id_key = 'guest_' . md5(time() . rand());
		}

		$key = 'ac_wishlist_' . $user_id_key;
		$data = get_transient($key);
		if ( ! is_array($data) ) $data = array('items'=>array());
		$data['items'][] = array('product_id' => $product_id, 'added_at' => time());
		if ( ! empty($user_email) ) $data['email'] = $user_email;
		$data['timestamp'] = time();

		set_transient($key, $data, 30 * DAY_IN_SECONDS);
	}

}
