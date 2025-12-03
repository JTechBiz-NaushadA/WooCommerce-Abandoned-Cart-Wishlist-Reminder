<?php
if (!defined('ABSPATH')) exit;
// $cart_items (array) and $customer_name are expected in scope
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php esc_html_e('You left items in your cart', 'wc-acr'); ?></title>
</head>
<body>
    <p><?php esc_html_e('Hi there,', 'wc-acr'); ?></p>
    <p><?php esc_html_e('We noticed you left some items in your cart. Here is what you left behind:', 'wc-acr'); ?></p>
    <ul>
        <?php if ( ! empty($cart_items) ): ?>
            <?php foreach ( $cart_items as $item ): ?>
                <li><?php echo esc_html( isset($item['name']) ? $item['name'] : sprintf(__('Product #%d', 'wc-acr'), isset($item['product_id']) ? $item['product_id'] : 0) ); ?> (x<?php echo esc_html(isset($item['quantity']) ? $item['quantity'] : 1); ?>)</li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><?php esc_html_e('No product details available', 'wc-acr'); ?></li>
        <?php endif; ?>
    </ul>
	
	<?php if ( ! empty($coupon_code) ): ?>
		<p><?php printf( esc_html__('Use coupon code %s at checkout for a discount. Expires in 7 days.', 'wc-acr'),
			 '<strong>' . esc_html($coupon_code) . '</strong>' ); ?></p>
	<?php endif; ?>

    <p><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e('Return to your cart and complete checkout', 'wc-acr'); ?></a></p>

    <p><?php esc_html_e('If you have any questions, reply to this email and we will help.', 'wc-acr'); ?></p>

    <p><?php esc_html_e('Thank you,', 'wc-acr'); ?><br><?php esc_html_e('Store Team', 'wc-acr'); ?></p>
</body>
</html>
