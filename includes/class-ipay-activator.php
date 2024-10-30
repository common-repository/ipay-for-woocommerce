<?php

/**
 * iPay activator
 */

class IPay_GW_WC_Activator {

    public static function on_ipay_activation(){

        if(!current_user_can( 'activate_plugins' )) {
            return;
        }
    
        $random_string = IpayUtils::generate_random(20);
    
        if(!get_option('ipay_global_gw_wc_api_string')){
            add_option('ipay_global_gw_wc_api_string', $random_string);
        }
    
    }

}

