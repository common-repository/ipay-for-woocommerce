<?php

class Ipay_Notifications{

    public function unsupported_currency_type() {
        ?>
            <div class="notice notice-error">
                <p><?php _e( 'Unsupported currency type, iPay currently supports LKR (Srilankan Rupees) only, Please change your currency type.'); ?></p>
            </div>
        <?php
    }

    public function switch_to_ipg(){
        ?>
            <div class="notice notice-warning">
                <p><?php _e( 'Normal iPay web payments will be deprecated soon, please switch to IPG.'); ?></p>
            </div>
        <?php
    }

    public function show_notifications(){
        if(!WC_Payment_Gateway_iPay::currency_check()){
            add_action('admin_notices', array($this, 'unsupported_currency_type'));
        }
        if(get_option( 'is_ipg_mode_enabled', 'no' ) == 'no'){
            add_action('admin_notices', array($this, 'switch_to_ipg'));
        }
    }

}