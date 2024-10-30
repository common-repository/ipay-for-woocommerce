<?php

if (!defined('ABSPATH')) {
    exit;
}

if(!in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins') ))){
    add_action( 'admin_notices', 'ipay_global_gw_wc_woocommerce_not_available' );
}

function ipay_global_gw_wc_woocommerce_not_available() {
    ?>
        <div class="notice notice-warning">
            <p><?php _e( 'iPay is enabled, please make sure WooCommerce is active.'); ?></p>
        </div>
    <?php
}