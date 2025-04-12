<?php

/**
 * Check if product 'Coming soon'
 * 
 * @param object|null $product
 * @return bool
 */
function is_product_coming_soon( $product = null ) {
    if ( $product instanceof MeprProduct ) {
        return get_post_meta( $product->ID, '_coming_soon', true );
    }
    
    return false;
}

/**
 * Generate the banner HTML
 * 
 * @param void
 * @return string
 */
function get_coming_soon_banner_html() {
    return sprintf('<div class="coming-soon-banner">%s</div>', '🚧 This product is coming soon! Stay tuned.');
}

/**
 * If product is restricted for purchasing
 * if this product has '_coming_soon' meta value === true
 * 
 * @param bool|null $value
 * @param object $product
 * @return bool|null
 */
add_filter( 'mepr-can-you-buy-me-override', function (bool|null $value, $product) {
    if ( $product instanceof MeprProduct && is_product_coming_soon( $product ) ) {
        return false;
    }

    return $value;
}, 11, 2 );

/**
 * Tweak custom caption about
 * not having permissions 
 * to buy a specific product
 * if product has "_coming_soon" meta value === true
 * 
 * @param string $output
 * @param object $product
 * @return string $output
 */
add_filter( 'mepr-product-cant-purchase-string', function (string $output, $product) {
    if ( $product instanceof MeprProduct && is_product_coming_soon( $product ) ) {
        return false;
    }

    return $output;
}, 11, 2 );

/**
 * Remove the error message from the content
 * if product is coming soon and display just 
 * the ordinary content
 * 
 * @param string $content
 * @return string $content
 */
add_filter('the_content', function ($content) {
    $object_id = get_queried_object_id();
    $product = new MeprProduct($object_id);

    if ( $product->ID !== null && is_product_coming_soon( $product ) ) {
        remove_filter('the_content', 'MeprProductsCtrl::display_registration_form', 10);
    }

    return $content;
}, 9);

/**
 * Display the coming soon banner
 * if product is coming soon
 * 
 * @param string $content
 * @return string $content
 */
add_filter('the_content', function (string $content) {
    $object_id = get_queried_object_id();
    $product = new MeprProduct($object_id);

    $mepr_coupon_code = false;
    $payment_required = false;

    if ( $product->ID !== null && is_product_coming_soon( $product ) ) {
        ob_start();
        ?>
        
        <div class="mepr-checkout-container mp_wrapper">
            <?php echo get_coming_soon_banner_html(); ?>
            <div class="invoice-wrapper">
                <h3 class="invoice-heading"><?php printf(esc_html_x('Pay %s', 'ui', 'memberpress'), get_bloginfo('name')); ?></h3>

                <div class="mp-form-row mepr_bold mepr_price">
                    <div class="mepr_price_cell invoice-amount">
                        <?php MeprProductsHelper::display_invoice($product, $mepr_coupon_code); ?>
                    </div>
                </div>

                <div class="mepr-product-rows">
                    <?php if ($payment_required || ! empty($product->plan_code)) : ?>
                        <?php if ($mepr_options->coupon_field_enabled) : ?>
                    <a class="have-coupon-link" data-prdid="<?php echo $product->ID; ?>" href="">
                            <?php echo MeprCouponsHelper::show_coupon_field_link_content($mepr_coupon_code); ?>
                    </a>
                    <div class="mp-form-row mepr_coupon mepr_coupon_<?php echo $product->ID; ?> mepr-hidden">
                    <div class="mp-form-label">
                    <!-- <label for="mepr_coupon_code<?php // echo $unique_suffix; ?>"><?php // _ex( 'Coupon Code:', 'ui', 'memberpress' ); ?></label> -->
                    <span class="mepr-coupon-loader mepr-hidden">
                        <img src="<?php echo includes_url('js/thickbox/loadingAnimation.gif'); ?>"
                        alt="<?php _e('Loading...', 'memberpress'); ?>"
                        title="<?php _ex('Loading icon', 'ui', 'memberpress'); ?>" width="100" height="10" />
                    </span>
                    <span class="cc-error"><?php _ex('Invalid Coupon', 'ui', 'memberpress'); ?></span>
                    <span class="cc-success"><?php _ex('Coupon applied successfully', 'ui', 'memberpress'); ?></span>
                    </div>
                    <input type="text" id="mepr_coupon_code<?php echo $unique_suffix; ?>" class="mepr-form-input mepr-coupon-code"
                    placeholder="<?php _ex('Coupon Code:', 'ui', 'memberpress'); ?>"
                    name="mepr_coupon_code"
                    value="<?php echo ( isset($mepr_coupon_code) ) ? esc_attr(stripslashes($mepr_coupon_code)) : ''; ?>"
                    data-prdid="<?php echo $product->ID; ?>" />
                    </div>
                        <?php else : ?>
                    <input type="hidden" name="mepr_coupon_code"
                    value="<?php echo ( isset($mepr_coupon_code) ) ? esc_attr(stripslashes($mepr_coupon_code)) : ''; ?>" />
                        <?php endif; ?>
                    <?php endif ?>


                    <div class="mepr-transaction-invoice-wrapper" style="padding-top:10px">
                        <span class="mepr-invoice-loader mepr-hidden">
                        <img src="<?php echo includes_url('js/thickbox/loadingAnimation.gif'); ?>"
                            alt="<?php _e('Loading...', 'memberpress'); ?>"
                            title="<?php _ex('Loading icon', 'ui', 'memberpress'); ?>" width="100" height="10" />
                        </span>
                        <div><?php MeprProductsHelper::display_spc_invoice($product, $mepr_coupon_code); ?></div>
                    </div>
                </div>

            </div>
        </div>

        <?php
        $content = $content . ob_get_clean();
    }

    return $content;
});

/**
 * Add the coming soon class to the body
 * if product is coming soon
 * 
 * @param array $classes
 * @return array $classes
 */
add_filter('body_class', function (array $classes) {
    $object_id = get_queried_object_id();
    $product = new MeprProduct($object_id);

    if ( $product->ID !== null && is_product_coming_soon( $product ) ) {
        $classes[] = 'coming-soon';
    }

    return $classes;
});