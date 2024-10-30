<form method="POST" action="<?php echo esc_attr($form_url) ?>">
    <input type="hidden" name="merchantWebToken" value="<?php echo esc_attr($mw_token) ?>"/>
    <input type="hidden" name="orderId" value="<?php echo esc_attr($order_id) ?>"/>
    <input type="hidden" name="returnUrl" value="<?php echo esc_attr($return_url) ?>"/>
    <input type="hidden" name="cancelUrl" value="<?php echo esc_attr($cancel_url) ?>"/>
    <input type="hidden" name="totalAmount" value="<?php echo esc_attr($total_amount) ?>"/>
    <input type="hidden" name="customerName" value="<?php echo esc_attr($customer_name) ?>"/>
    <input type="hidden" name="customerMobile" value="<?php echo esc_attr($customer_phone) ?>"/>
    <input type="hidden" name="customerEmail" value="<?php echo esc_attr($customer_email) ?>"/>
    <input type="hidden" name="subMerchantReference" value="<?php echo esc_attr($sub_merchant_reference) ?>"/>
    <input type="submit" value="Submit" id="submission_trigger">
</form>