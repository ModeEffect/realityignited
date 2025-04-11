<?php

/**
 * Output field under "Permissions" tab
 * 
 * @param object $product
 * @param void
 */
add_action( 'mepr_products_permissions_tab', function ( $product ) {
    if ( $product instanceof MeprProduct ) {
        ?>
        <div class="coming_soon" style="margin-top: 15px">
            <input type="checkbox" name="_coming_soon" id="_coming_soon" <?php checked( get_post_meta( $product->ID, '_coming_soon', true) ); ?> />
            <label for="_coming_soon"><?php _e('Is "Coming soon"?', 'memberpress'); ?></label>
        </div>
        <?php
    }   
});

/**
 * Save the '_coming_soon' post meta
 * 
 * @param object $product
 * @return void
 */
add_action( 'mepr-membership-save-meta', function ( $product ) {
    if ( $product instanceof MeprProduct ) {
        update_post_meta( 
            $product->ID, 
            '_coming_soon', 
            $_POST['_coming_soon'] === 'on', 
            get_post_meta( $product->ID, '_coming_soon', true )
        );
    }
});