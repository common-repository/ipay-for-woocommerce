<?php

const IPAY_GW_WC_INVALID_REQUEST = 'INVALID_REQUEST';
const IPAY_GW_WC_MISSING_REQ_FIELDS = 'Missing required fields.';
const IPAY_GW_WC_INVALID_DATA = 'Invalid Data.';
const IPAY_GW_WC_VERIFICATION_FAILED = 'Verification Unsuccessfull';
const IPAY_GW_WC_GATEWAY_ERROR = 'IPAY_GW_ERROR';
const IPAY_GW_WC_PAYMENT_DECLINED = 'Order Payment Declined';

/**
 * Updates the order status
 */

function ipay_global_gw_wc_payment_status_update($data) {

    global $woocommerce;

    $ipay_inv_req_error = false;

    if(!isset($data['orderId']))
        $ipay_inv_req_error = true;

    if(!isset($data['transactionReference']))
        $ipay_inv_req_error = true;

    if(!isset($data['transactionAmount']))
        $ipay_inv_req_error = true;

    if(!isset($data['creditedAmount']))
        $ipay_inv_req_error = true;

    if(!isset($data['transactionStatus']))
        $ipay_inv_req_error = true;
    
    if(!isset($data['transactionMessage']))
        $ipay_inv_req_error = true;

    if(!isset($data['transactionTimeInMillis']))
        $ipay_inv_req_error = true;

    if(!isset($data['checksum']))
        $ipay_inv_req_error = true;

    if($ipay_inv_req_error)
        return new WP_Error(IPAY_GW_WC_INVALID_REQUEST, IPAY_GW_WC_MISSING_REQ_FIELDS);
        
    try{
        $order = wc_get_order($data['orderId']);
    }catch(Exception $e){
        return new WP_Error(IPAY_GW_WC_INVALID_REQUEST, IPAY_GW_WC_INVALID_DATA);
    }

    if($order == null)
        return new WP_Error(IPAY_GW_WC_INVALID_REQUEST, IPAY_GW_WC_INVALID_DATA);

    $transactionReference = sanitize_text_field($data['transactionReference']);
    $orderTransactionAmount = sanitize_text_field($order->get_total());
    $transactionTimeInMillis = sanitize_text_field($data['transactionTimeInMillis']);
    $transactionStatus = sanitize_text_field($data['transactionStatus']);

    $formattedTransactionAmount = number_format((float)$orderTransactionAmount, 2, '.', '');

    $ipay_secret = WC_Payment_Gateway_iPay::get_secret();

    $generated_checksum = generate_ipay_global_gw_wc_order_checksum($ipay_secret, $transactionReference, $order->get_id(), $transactionTimeInMillis, $formattedTransactionAmount, $transactionStatus);


    if(strcmp($transactionStatus, 'A') == 0){

        /**
         * Payment accepted and successfull.
         */

        if(strcmp($generated_checksum, $data['checksum']) == 0){

            $order->add_order_note('Order payment successful via iPay. Payment Reference ID - ' . $transactionReference);
            $order->update_meta_data('_ipay_reference_id', $transactionReference);
            $order->payment_complete();
            $order->save();

            return rest_ensure_response( array(
                'status'=>'SUCCESS'
            ));

        }else{

            $order->add_order_note('Customer has been charged by iPay, but order payment verification is unsuccesful. Please contact iPay. Payment Reference ID - ' . $transactionReference);
            $order->update_meta_data('_ipay_reference_id_2', $transactionReference);
            $order->update_status('on-hold');
            $order->save();

            return new WP_Error(IPAY_GW_WC_INVALID_REQUEST, IPAY_GW_WC_VERIFICATION_FAILED);
            
        }
        

    }
    else if(strcmp($transactionStatus, 'P') == 0){

        /**
         * Pending (Money successfully deducted from the customer bank account, 
         * but unable to transfer to merchant’s account. The amount will be transferred at the End of Day)
         */
        
        if(strcmp($generated_checksum, $data['checksum']) == 0){

            $order->add_order_note('Money successfully deducted from the customer bank account. The amount will be transferred at the End of the Day. Payment Reference ID - ' . $transactionReference);
            $order->update_meta_data('_ipay_reference_id', $transactionReference);
            $order->payment_complete();
            $order->save();

            return rest_ensure_response( array(
                'status'=>'PENDING'
            ));

        }else{
            
            $order->add_order_note('Customer has been charged by iPay, but order payment verification is unsuccesful. Please contact iPay. Payment Reference ID - ' . $transactionReference);
            $order->update_meta_data('_ipay_reference_id_2', $transactionReference);
            $order->update_status('on-hold');
            $order->save();

            return new WP_Error(IPAY_GW_WC_INVALID_REQUEST, IPAY_GW_WC_VERIFICATION_FAILED);
        }

    }
    else if(strcmp($transactionStatus, 'D') == 0){

        if(strcmp($generated_checksum, $data['checksum']) == 0){

            $order->add_order_note('Order payment via iPay failed.');
            $order->update_meta_data('_ipay_reference_id', $transactionReference);
            $order->update_status('failed');
            $order->save();

            return new WP_Error(IPAY_GW_WC_GATEWAY_ERROR, IPAY_GW_WC_PAYMENT_DECLINED);

        }else{

            $order->add_order_note('Declined notification recieved, but order verification failed. Please contact iPay.');
            $order->update_meta_data('_ipay_reference_id_2', $transactionReference);
            $order->update_status('pending');
            $order->save();

            return new WP_Error(IPAY_GW_WC_INVALID_REQUEST, IPAY_GW_WC_VERIFICATION_FAILED);

        }

    }
    else{

        /**
         * Undefined status code
         */

        return new WP_Error(IPAY_GW_WC_INVALID_REQUEST, IPAY_GW_WC_INVALID_DATA);
    }

}

function generate_ipay_global_gw_wc_order_checksum($secret, $transactionReference, $orderId, $transactionTimeInMillis, $transactionAmount, $transactionStatus){
    
    /**
     * Generate sha256 checksum
     */

    $message = $transactionReference . $orderId . $transactionTimeInMillis . $transactionAmount . $transactionStatus;

    $hashed_value = hash_hmac('sha256', $message, $secret, true);
    return base64_encode($hashed_value);

}

function register_ipay_global_gw_wc_notification_route() {

    $random_string = get_option('ipay_global_gw_wc_api_string');

    register_rest_route( IPAY_GLOBAL_GW_WC_NAMESPACE. '/' .IPAY_GLOBAL_GW_WC_CALLBACK, '/' .$random_string, array(
        'methods'  => WP_REST_Server::EDITABLE,
        'callback' => 'ipay_global_gw_wc_payment_status_update',
        'permission_callback' => '__return_true',
    ) );
    
}