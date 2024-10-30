<?php

class WC_Payment_Gateway_iPay extends WC_Payment_Gateway{

    const IPAY_GLOBAL_GW_WC_URL = 'https://ipay.lk/webpay/checkout';
    const IPAY_GLOBAL_GW_WC_SANDBOX_URL = 'https://sandbox.ipay.lk/webpay/checkout';

    const IPAY_GLOBAL_GW_IPG_WC_URL = 'https://ipay.lk/ipg/checkout';
    const IPAY_GLOBAL_GW_IPG_WC_SANDBOX_URL = 'https://sandbox.ipay.lk/ipg/checkout';

    public static $ipay_secret;

    public function __construct(){
        
        $this->id = IPAY_GLOBAL_GW_WC_GATEWAY_ID;
        $this->method_title = IPAY_GLOBAL_GW_WC_DISPLAY_NAME;
        $this->icon = IPAY_GLOBAL_GW_WC_ADMIN_ICON;
        $this->has_fields = false;
        $this->method_description = IPAY_GLOBAL_GW_WC_DESCRIPTION;

        $this->has_fields = false;
        
        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->settings['enabled'];
        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];
        $this->sandbox_mode = $this->settings['sandbox_mode'];
        $this->ipg_mode_enabled = $this->settings['ipg_mode_enabled'];
        $this->merchant_web_token = $this->settings['merchant_web_token'];

        self::$ipay_secret = $this->settings['secret'];

        $this->sub_merchant_reference = $this->settings['sub_merchant_reference'];

        $this->return_page = $this->settings['return_page'];
        $this->cancel_page = $this->settings['cancel_page'];

        $this->ipay_gw_submission_form_allowed_html = array(
            'form' => array(
                'method' => array(),
                'action' => array()
            ),
            'input' => array(
                'type' => array(),
                'name' => array(),
                'value' => array(),
                'id'=>array()
            )
        );

        /**
         * Save hook
         */
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

        add_action('admin_enqueue_scripts', array($this, 'ipay_custom_scripts'));

        if($this->ipg_mode_enabled != null && get_option( 'is_ipg_mode_enabled', null ) == null){
            add_option( 'is_ipg_mode_enabled', $this->ipg_mode_enabled);
        }
        if($this->ipg_mode_enabled != null && get_option( 'is_ipg_mode_enabled', null ) != null){
            update_option( 'is_ipg_mode_enabled', $this->ipg_mode_enabled);
        }

        if($this->settings['secret'] != null && get_option( 'ipay_gw_sec', null ) == null){
            add_option( 'ipay_gw_sec', $this->settings['secret']);
        }
        if($this->settings['secret'] != null && get_option( 'ipay_gw_sec', null ) != null){
            update_option( 'ipay_gw_sec', $this->settings['secret']);
        }
        
    }

    public function init_form_fields() {

        $this->form_fields = array(
            'general_settings' => array(
                'title' => __('General Settings', 'ipay_global_gw_wc'),
                'type' => 'title',
                'description' => __('Configure the plugin behaviour', 'ipay_global_gw_wc')
            ),
            'enabled' => array(
                'title' => __('Enable/Disable', 'ipay_global_gw_wc'),
                'type' => 'checkbox',
                'label' => __('Enables payments via iPay', 'ipay_global_gw_wc'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Title', 'ipay_global_gw_wc'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during the chekout.', 'ipay_global_gw_wc'),
                'default' => __('iPay', 'ipay_global_gw_wc')
            ),
            'description' => array(
                'title' => __('Description', 'ipay_global_gw_wc'),
                'type' => 'textarea',
                'description' => __('The description which the user sees during the checkout.', 'ipay_global_gw_wc'),
                'default' => __('Pay via iPay', 'ipay_global_gw_wc')
            ),
            'sandbox_mode' => array(
                'title' => __('Sandbox Mode', 'ipay_global_gw_wc'),
                'type' => 'checkbox',
                'label' => __('Enables sandbox integration', 'ipay_global_gw_wc'),
                'default' => 'no'
            ),
            'return_page' => array(
                'title' => __('Return URL', 'ipay_global_gw_wc'),
                'type' => 'select',
                'options' => $this->get_page_list(),
                'description' => __('Page to return when transaction is success.', 'ipay_global_gw_wc')
            ),
            'cancel_page' => array(
                'title' => __('Cancel Page', 'ipay_global_gw_wc'),
                'type' => 'select',
                'options' => $this->get_page_list(),
                'description' => __('Page to return when transaction is declined or cancelled.', 'ipay_global_gw_wc')
            ),
            'connectivity' => array(
                'title' => __('Connectivity', 'ipay_global_gw_wc'),
                'type' => 'title',
                'description' => __('These should be configured for a succesfull connection.', 'ipay_global_gw_wc')
            ),
            'ipg_mode_enabled' => array(
                'title' => __('Enforce IPG', 'ipay_global_gw_wc'),
                'type' => 'checkbox',
                'label' => __('Enables IPG', 'ipay_global_gw_wc'),
                'description' => __('It is recommended to switch to IPG since old web payments will be deprecated soon.'),
                'default' => 'no',
                'class' => 'ipg-checkbox'
            ),
            'merchant_web_token' => array(
                'title' => __('Merchant Web Token', 'ipay_global_gw_wc'),
                'type' => 'text',
                'description' => __('The web token generated in the merchant admin portal.', 'ipay_global_gw_wc'),
                'default' => ''
            ),
            'secret' => array(
                'title' => __('Secret', 'ipay_global_gw_wc'),
                'type' => 'ipaytext',
                'description' => __('This secret will be used to generate the checksum.', 'ipay_global_gw_wc'),
                'default' => IpayUtils::generate_random(20),
                'genbtn' => true,
		        'genbtn_cat' => 'secret'
            ),
            'sub_merchant_reference' => array(
                'title' => __('Sub Merchant Reference', 'ipay_global_gw_wc'),
                'type' => 'text',
                'description' => __('Applicable for aggregator merchants only.', 'ipay_global_gw_wc'),
                'default' => ''
            ),
            'callback_url' => array(
                'title' => __('Callback Url', 'ipay_global_gw_wc'),
                'type' => 'ipaytext',
                'description' => __('This should be updated in the merchant portal, This will not be updated.', 'ipay_global_gw_wc'),
                'default' => $this->generate_ipay_callback_url(),
                'genbtn' => true,
                'genbtn_cat' => 'redirect_url'
            )
        );

    }

    public function admin_options() {
        ?>
            <div>
                <div class="ipay-admin-header">
                    <div class="ipay-logo-container">
                        <img src="<?php echo esc_url(IPAY_GLOBAL_GW_WC_ADMIN_ICON); ?>" alt="ipay_logo"/>
                    </div>
                    <div class="ipay-desc-container">
                        <p id="ipay-title-text"><?php esc_html_e(IPAY_GLOBAL_GW_WC_DISPLAY_NAME, 'ipay_global_gw_wc'); ?></p>
                        <p id="ipay-sub-title-text"><?php esc_html_e(IPAY_GLOBAL_GW_WC_LONG_DESCRIPTION, 'ipay_global_gw_wc'); ?></p>
                    </div>
                </div>
            </div>
            
            <table class="form-table">
                <?php $this->generate_settings_html(); ?>
            </table> 
        <?php
    }

    /**
     * Outputs submission form
     */
    public function receipt_page( $order ){
        echo '<p>'. __('Thank you for your order. You will be automatically redirected to the payment page.', 'ipay_global_gw_wc') .'</p>';
        echo wp_kses($this->generate_ipay_web_form($order), $this->ipay_gw_submission_form_allowed_html);
    }

    /**
     * Generates the submission form
     */

    private function generate_ipay_web_form($order_id){

        $order = new WC_Order($order_id);

        if($this->ipg_mode_enabled != null && $this->ipg_mode_enabled == 'yes'){
            if($this->sandbox_mode == 'no'){
                $form_url = self::IPAY_GLOBAL_GW_IPG_WC_URL;
            }else{
                $form_url = self::IPAY_GLOBAL_GW_IPG_WC_SANDBOX_URL;
            }
        }else{
            if($this->sandbox_mode == 'no'){
                $form_url = self::IPAY_GLOBAL_GW_WC_URL;
            }else{
                $form_url = self::IPAY_GLOBAL_GW_WC_SANDBOX_URL;
            }
        }
       

        $default_redirect_url = $order->get_checkout_order_received_url();

        $mw_token = $this->merchant_web_token;
        $sm_reference = $this->sub_merchant_reference;

        $firstname = $order->get_billing_first_name();
        $lastname = $order->get_billing_last_name();

        $total_amount = str_replace(',', '.', $order->get_total());
        $customer_name = $firstname. ' ' .$lastname;
        $customer_phone = $order->get_billing_phone();
        $customer_email = $order->get_billing_email();

        $return_url = $this->get_ipay_page_url($this->return_page, $default_redirect_url);
        $cancel_url = $this->get_ipay_page_url($this->cancel_page, $default_redirect_url);

        wc_enqueue_js('
            $.blockUI({
                message: "' . esc_js( __( 'You will be redirecting...' ) ) . '",
                overlayCSS:
                {
                    background: "#fff",
                    opacity: 0.6
                },
                css: {
                    border:         "3px solid #aaa",
                    color:          "#555",
                    paddingRight:   "10%",
                    paddingLeft:    "10%",
                    paddingTop:     "60px",
                    paddingBottom:  "130px",
                    textAlign:      "center",
                    backgroundColor:"#fff",
                    zindex:         "9999",
                    fontWeight: "bold"
                }
            });
            jQuery("#submission_trigger").click();
        ');

        if($this->ipg_mode_enabled != null && $this->ipg_mode_enabled == 'yes'){
            $secret = get_option( 'ipay_gw_sec', null );
            $fmtTxnAmount = number_format((float)$total_amount, 2, '.', '');

            $msgString = $mw_token . $order_id . $fmtTxnAmount;
            $hv = hash_hmac('sha256', $msgString, $secret, true);
            $additional_sec_checksum = base64_encode($hv);

            ob_start();
                include plugin_dir_path( __FILE__ ) . '/templates/submission-form-ipg.php';
            return ob_get_clean();
        }else{
            ob_start();
                include plugin_dir_path( __FILE__ ) . '/templates/submission-form.php';
            return ob_get_clean();
        }

    }

    public function process_payment($order_id){

        global $woocommerce;
        $order = new WC_Order( $order_id );

        $order->update_status('pending');
        $order->add_order_note('Redirected to the iPay checkout page. Order will be processed after the confirmation.');

        return array(
            'result' 	=> 'success',
            'redirect'	=>  add_query_arg( 
                array(
                    'key'=>$order->order_key,
                    'order'=>$order->get_id()
                ),
                $order->get_checkout_payment_url(true)
             )
        );
    
    }

    /**
     * Retrieves the page list
     */

    private function get_page_list(){

        $pages = get_pages(array(
            'sort_column' => 'post_title',
            'sort_order' => 'ASC'
        ));

        $page_list = array();
        array_push($page_list, 'Default Page');

        foreach($pages as $page){
            $page_id = $page->ID;
            $index = $page_id+1;
            $page_list[$index] = $page->post_title;
        }

        return $page_list;

    }

    /**
     * Retrieve the page url
     */

     private function get_ipay_page_url($id, $default_url){

        $page_url = '';

        if($id == 0){
            $page_url = $default_url;
        }else{
            $page_id = $id-1;
            $page_url = get_page_link($page_id);
        }

        return $page_url;

     }

    public static function get_secret(){
        return self::$ipay_secret;
    }

    public function ipay_custom_scripts(){
        wp_enqueue_style( 'ipay-styles', plugins_url( '/assets/css/ipay-styles.css', __FILE__ ));
        wp_enqueue_script('ipay-script', plugins_url( '/assets/js/ipay-script.js', __FILE__ ));
    }

    private function generate_ipay_callback_url(){

        $random_string = get_option('ipay_global_gw_wc_api_string');

        return get_rest_url( null, IPAY_GLOBAL_GW_WC_NAMESPACE . '/' . IPAY_GLOBAL_GW_WC_CALLBACK . '/' . $random_string);
    }

    public static function currency_check(){
        /**
         * Check for the supported currency
         */
        $active_currency = get_woocommerce_currency();

        $return_status = false;

        if(strcmp($active_currency, 'LKR') == 0){
            $return_status = true;
        }

        return $return_status;

    }

    public function generate_ipaytext_html($key, $data){

        /**
         * Generates custom setting field which ables to generate a random number and to copy to clipboard.
         */

        $field_key = $this->get_field_key($key);

        $defaults = array(
            'title' => '',
            'disabled' => false,
            'class' => '',
            'css' => '',
            'placeholder' => '',
            'type' => '',
            'desc_tip' => '',
            'description' => '',
            'genbtn' => false,
            'genbtn_cat' => '',
            'custom_attributes' => array(),
            'readonly' => false
        );
        
        $data = wp_parse_args($data, $defaults);

        ob_start();

        ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> 
                        <?php 
                            $ipay_gw_wc_allowed_html_tooltip = array(
                                'span' => array(
                                    'class' => array(),
                                    'data-tip' => array()
                                )
                            );
                            echo wp_kses($this->get_tooltip_html($data), $ipay_gw_wc_allowed_html_tooltip);
                        ?>
                    </label>
                </th>
                <td class="forminp">
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
                        <div class="ipay-ipaytext-container">
                            <input class="<?php echo esc_attr($data['class']); ?>" type="text" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" value="<?php echo esc_attr($this->get_option($key)); ?>" placeholder="<?php echo esc_attr($data['placeholder']); ?>" <?php disabled($data['disabled'], true); ?> <?php if($data['readonly']){echo "readonly";}  ?>/>
                            <div class="ipaytext-cp-btn" id="<?php echo 'copy-btn' . esc_attr($field_key) ?>"><span class = "copy-tool-tip ipay-custom-hidden" id="<?php echo 'tool-tip' . esc_attr($field_key) ?>">Copy to Clipboard</span><img width="18px" src="<?php echo esc_attr(plugins_url('/assets/img/copy.svg', __FILE__)); ?>" alt="copy-icon"/></div>
                            <?php
                                $ipay_gw_wc_allowed_html_for_gen_btn = array(
                                    'div' => array(
                                        'class' => array(),
                                        'id' => array(),
                                        'data-url' => array()
                                    )
                                );
                                if($data['genbtn']){
                                    if($data['genbtn_cat'] == 'secret'){
                                        $syntax_html = '<div class="ipaytext-gen-btn" id="gen-btn' .esc_attr($field_key). '">Generate</div>';
                                        echo wp_kses($syntax_html, $ipay_gw_wc_allowed_html_for_gen_btn);
                                    }
                                    if($data['genbtn_cat'] == 'redirect_url'){
                                        $syntax_html = '<div class="ipaytext-gen-btn-ru" id="gen-btn-ru' .esc_attr($field_key). '" data-url="'.$this->generate_ipay_callback_url().'">Generate</div>';
                                        echo wp_kses($syntax_html, $ipay_gw_wc_allowed_html_for_gen_btn);
                                    }
                                }
                            ?>
                        </div>
                        <?php
                            $allowed_html_for_ipay_text_desc = array(
                                'p' => array(
                                    'class' => array()
                                )
                            ); 
                            echo wp_kses($this->get_description_html( $data ), $allowed_html_for_ipay_text_desc);
                        ?>
                    </fieldset>
                </td>    
            </tr>
        <?php

        return ob_get_clean();

    }



}