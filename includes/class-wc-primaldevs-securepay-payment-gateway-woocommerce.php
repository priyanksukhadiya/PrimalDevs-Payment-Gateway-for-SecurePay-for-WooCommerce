<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://profiles.wordpress.org/priyanksukhadiya/
 * @since      1.0.0
 *
 * @package    PrimalDevs_Payment_Gateway_for_SecurePay_WooCommerce
 * @subpackage PrimalDevs_Payment_Gateway_for_SecurePay_WooCommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    PrimalDevs_Payment_Gateway_for_SecurePay_WooCommerce
 * @subpackage PrimalDevs_Payment_Gateway_for_SecurePay_WooCommerce/includes
 * @author     Priyank Sukhadiya <priyanksukhadiya2001@gmail.com>
 */

class PrimalDevs_Payment_Gateway_for_SecurePay_WooCommerce extends WC_Payment_Gateway
{
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    public $ShowLogo = 'no';

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */

    public function __construct()
    {
        if (defined('PRIMALDEVS_PAYMENT_GATEWAT_FOR_SECUREPAY_WOOCOMMERCE_VERSION')) {
            $this->version = PRIMALDEVS_PAYMENT_GATEWAT_FOR_SECUREPAY_WOOCOMMERCE_VERSION;
        } else {
            $this->version = '1.0.0';
        }

        $this->plugin_name = 'wc-primaldevs-payment-gateway-securepay';
        $this->id = 'wc-primaldevs-payment-gateway-securepay';
        $this->method_title = __('SecurePay WC Payment Gateway', 'wc-primaldevs-payment-gateway-securepay');
        $this->method_description = __('Provide customers with the opportunity to use the SecurePay WC Payment Gateway to safely pay with their credit cards.', 'wc-primaldevs-payment-gateway-securepay');
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->merchantId = $this->get_option('merchantId');
        $this->password = $this->get_option('password');
        $this->showLogo = $this->get_option('showLogo');
        $this->developmentMode = $this->get_option('developmentMode');
        $this->holderName = $this->get_option('holderName');
        $this->logging = $this->get_option('logging') === 'yes';


        if ($this->showLogo == 'yes') {
            $this->icon = plugin_dir_url(dirname(__FILE__)) . 'assets/securepay_payment_wc_logo.png';
        }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);

        // Hook for enqueuing styles and scripts on the frontend
        add_action('wp_enqueue_scripts', [$this, 'securepay_enqueue_styles']);
        add_action('wp_enqueue_scripts', [$this, 'securepay_enqueue_scripts']);
    }

    /**
     * Initialise Gateway Settings Form Fields
     */

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => __('Enable/Disable', 'wc-primaldevs-payment-gateway-securepay'),
                'type' => 'checkbox',
                'label' => __('Enable SecurePay WC payment gateway', 'wc-primaldevs-payment-gateway-securepay'),
                'default' => 'yes',
            ],
            'title' => [
                'title' => __('Payment method name', 'wc-primaldevs-payment-gateway-securepay'),
                'type' => 'text',
                'placeholder' => 'For eg: Pay by Credit Card',
                'description' => __('Customer will see this method name during checkout.', 'wc-primaldevs-payment-gateway-securepay'),
                'default' => __('SecurePay WC Payment', 'wc-primaldevs-payment-gateway-securepay'),
            ],
            'description' => [
                'title' => __('Checkout Description', 'wc-primaldevs-payment-gateway-securepay'),
                'type' => 'textarea',
                'placeholder' => 'For eg: Please enter your Credit Card details',
                'description' => __('The description be displayed to the customer during the checkout process.', 'wc-primaldevs-payment-gateway-securepay'),
                'default' => __('Enter your credit card details below', 'wc-primaldevs-payment-gateway-securepay'),
            ],
            'developmentMode' => [
                'title' => __('Test environment', 'wc-primaldevs-payment-gateway-securepay'),
                'type' => 'checkbox',
                'label' => __('Enable SecurePay test environment (Please untick for Live transaction)', 'wc-primaldevs-payment-gateway-securepay'),
                'default' => 'yes',
            ],
            'merchantId' => [
                'title' => __('SecurePay Merchant ID', 'wc-primaldevs-payment-gateway-securepay'),
                'type' => 'text',
                'placeholder' => 'Eg: ABC0001',
                'description' => __('Enter SecurePay merchant ID', 'wc-primaldevs-payment-gateway-securepay'),
                'default' => '',
            ],
            'password' => [
                'title' => __('API Transaction Password', 'wc-primaldevs-payment-gateway-securepay'),
                'type' => 'password',
                'placeholder' => '********',
                'description' => __('API Transaction Password and your SecurePay user account password are different.<br>You can find the API transaction password from SecurePay portal under <u>Manage</u> > <u>API Transaction Password</u> menu.', 'wc-primaldevs-payment-gateway-securepay'),
                'default' => '',
            ],
            'logging' => [
                'title' => __('Logging', 'wc-primaldevs-payment-gateway-securepay'),
                'label' => __('Log debug messages', 'wc-primaldevs-payment-gateway-securepay'),
                'type' => 'checkbox',
                // Translators: %s is the file path for the WooCommerce System Status log file.
                'description' => sprintf(__('Save debug messages to the WooCommerce System Status log file <code>%s</code>.', 'wc-primaldevs-payment-gateway-securepay'), WC_Log_Handler_File::get_log_file_path('wc-primaldevs-payment-gateway-securepay')),
                'default' => 'no',
            ],
            'showLogo' => [
                'title' => __('SecurePay icon', 'wc-primaldevs-payment-gateway-securepay'),
                'type' => 'checkbox',
                'label' => __('Display the SecurePay icon while checking out', 'wc-primaldevs-payment-gateway-securepay'),
                'default' => 'yes',
            ],
            'holderName' => [
                'title' => __('Card holder name field', 'wc-primaldevs-payment-gateway-securepay'),
                'type' => 'checkbox',
                'label' => __('Enable credit card holder name field', 'wc-primaldevs-payment-gateway-securepay'),
                'default' => 'no',
            ],
        ];
    }

    /**
     * Admin Options Notice Show
     *
     * @since 1.0.0
     */
    public function admin_options()
    {
        echo '<h3>' . esc_html__('SecurePay payment gateway options', 'wc-primaldevs-payment-gateway-securepay') . '</h3>';
        echo '<p>' . esc_html__('Note: For test transactions, please use Merchant ID: ', 'wc-primaldevs-payment-gateway-securepay') . '<strong>' . esc_html('ABC0001') . '</strong> ' . esc_html__('and Password: ', 'wc-primaldevs-payment-gateway-securepay') . '<strong>' . esc_html('abc123') . '</strong></p>';
        // Call the parent admin_options to render the options table
        ?>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table>
        <?php
    }

    /**
     * Enqueue plugin styles.
     */
    public function securepay_enqueue_styles()
    {
        wp_enqueue_style(
            'securepay_plugin_styles',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/securepay-wc-admin.css',
            array(),
            $this->version
        );
    }

    /**
     * Enqueue plugin scripts.
     */
    public function securepay_enqueue_scripts()
    {
        if (is_checkout()) {
            wp_enqueue_script(
                'securepay_plugin_scripts',
                plugin_dir_url(dirname(__FILE__)) . 'assets/js/securepay-wc-admin.js',
                array('jquery'),
                $this->version,
                true // Load in footer
            );
        }
    }


    /**
     * Display payment fields on the checkout page.
     *
     * This function outputs the necessary fields for the customer to enter payment information.
     * Typically, this would include credit card details, but it could be customized as needed.
     */
    public function payment_fields()
    {
        if (!empty($this->description)) {
            // Sanitize the description to allow certain HTML tags
            $sanitized_description = wp_kses_post($this->description);

            echo esc_html($sanitized_description); // Render the HTML correctly
            // Format the description (adds <p> tags where needed)
            // $formatted_description = wpautop($sanitized_description);

            // Output the final formatted description safely
            // echo esc_html($formatted_description); // Render the HTML correctly
        }


        ?>
        <?php wp_nonce_field('securepay_payment', 'securepay_nonce'); ?>
        <?php if ($this->holderName == 'yes') { ?>
            <p class="form-row form-row-wide">
                <label><?php echo esc_html__("Name on the Card", 'wc-primaldevs-payment-gateway-securepay'); ?>
                    <span class="required">*</span>
                </label>
                <input class="input-text" type="text" id="cardHolderName" name="cardHolderName" required />
            </p>
        <?php } ?>
        <div class="clear"></div>

        <p class="form-row form-row-wide">
            <label><?php echo esc_html__("Credit card Number", 'wc-primaldevs-payment-gateway-securepay') ?>
                <span class="required">*</span>
            </label>
            <input class="input-text" type="text" id="ccardNumber" name="ccardNumber" maxlength="19" placeholder="" required />
        </p>
        <div class="clear"></div>

        <p class="form-row form-row-first">
            <label><?php echo esc_html__("Card Expiration Date", 'wc-primaldevs-payment-gateway-securepay') ?>
                <span class="required">*</span>
            </label>
        <div class="form-row form-row-first expiration-fields">
            <select name="exyear" id="exyear" class="woocommerce-select woocommerce-cc-year" required>
                <option selected disabled><?php esc_html_e('Year', 'wc-primaldevs-payment-gateway-securepay'); ?></option>
                <?php
                $currentYr = (int) gmdate('Y', time());
                for ($i = 0; $i < 10; $i++) { ?>
                    <option value="<?php echo esc_attr($currentYr); ?>"><?php echo esc_html($currentYr); ?></option>
                    <?php $currentYr++;
                } ?>
            </select>
            <select name="exmonth" id="exmonth" class="woocommerce-select woocommerce-cc-month" required>
                <option selected disabled><?php esc_html_e('Month', 'wc-primaldevs-payment-gateway-securepay'); ?></option>
                <?php
                $months = [
                    '01' => 'January',
                    '02' => 'February',
                    '03' => 'March',
                    '04' => 'April',
                    '05' => 'May',
                    '06' => 'June',
                    '07' => 'July',
                    '08' => 'August',
                    '09' => 'September',
                    '10' => 'October',
                    '11' => 'November',
                    '12' => 'December'
                ];
                foreach ($months as $key => $value) {
                    echo "<option value='" . esc_attr($key) . "'>" . esc_html($value) . "</option>";
                }
                ?>
            </select>

        </div>
        </p>

        <div class="clear"></div>
        <p class="form-row form-row-wide">
            <label><?php echo esc_html__("Card CVV number", 'wc-primaldevs-payment-gateway-securepay') ?>
                <span class="required">*</span>
            </label>
            <input type="text" id="ccvv" class="input-text" maxlength="3" name="ccvv" required />
        </p>
        <div class="clear"></div>
        <?php
    }


    /**
     * Process a payment for the given order.
     *
     * This function handles the payment processing for the specified WooCommerce order ID.
     *
     * @param int $order_id The ID of the order being processed.
     * @return array|WP_Error An array containing the result of the payment process, or a WP_Error object on failure.
     */
    public function process_payment($order_id)
    {
        global $woocommerce;

        // Check for nonce verification
        if (!isset($_POST['securepay_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['securepay_nonce'])), 'securepay_payment')) {
            wc_add_notice(__('Nonce verification failed.', 'wc-primaldevs-payment-gateway-securepay'), 'error');
            return;
        }

        $order = new WC_Order($order_id);
        $apiUrl = $this->securepaywc_get_api_url($this->developmentMode);

        $timeStamp = gmdate("YdmHisB" . "000+660");

        $mId = time();

        $this->log("SecurePay Payment Gateway WC Info: Beginning processing payment for order $order_id for the amount of {$order->get_total()}");

        // Sanitize and validate input data
        $ccardNumber = isset($_POST["ccardNumber"]) ? sanitize_text_field(wp_unslash($_POST["ccardNumber"])) : '';
        $exmonth = isset($_POST["exmonth"]) ? sanitize_text_field(wp_unslash($_POST["exmonth"])) : '';
        $exyear = isset($_POST["exyear"]) ? sanitize_text_field(wp_unslash($_POST["exyear"])) : '';
        $ccvv = isset($_POST["ccvv"]) ? sanitize_text_field(wp_unslash($_POST["ccvv"])) : '';
        $cardHolderName = isset($_POST["cardHolderName"]) ? sanitize_text_field(wp_unslash($_POST["cardHolderName"])) : '';

        // Ensure required fields are present
        // if (empty($ccardNumber) || empty($exmonth) || empty($exyear) || empty($ccvv) || empty($cardHolderName)) {
        //     wc_add_notice(__('All fields are required.', 'wc-primaldevs-payment-gateway-securepay'), 'error');
        //     return;
        // }

        $ccardNumber = str_replace(' ', '', $ccardNumber);
        // Assuming $exmonth and $exyear are already formatted as '04' and '2026'
        $exyear = substr($exyear, -2);

        // Create XML request
        $xmlRequest = '<?xml version="1.0" encoding="UTF-8"?>
            <SecurePayMessage>
                <MessageInfo>
                    <messageID>' . esc_xml($mId) . '</messageID>
                    <messageTimestamp>' . esc_xml($timeStamp) . '</messageTimestamp>
                    <timeoutValue>60</timeoutValue>
                    <apiVersion>xml-4.2</apiVersion>
                </MessageInfo>
                <MerchantInfo>
                    <merchantID>' . esc_xml($this->merchantId) . '</merchantID>
                    <password>' . esc_xml($this->password) . '</password>
                </MerchantInfo>
                <RequestType>Payment</RequestType>
                <Payment>
                    <TxnList count="1">
                        <Txn ID="1">
                            <txnType>0</txnType>
                            <txnSource>23</txnSource>
                            <amount>' . esc_xml($order->get_total() * 100) . '</amount>
                            <currency>' . esc_xml(get_option('woocommerce_currency')) . '</currency>
                            <purchaseOrderNo>' . esc_xml($order_id) . '</purchaseOrderNo>
                            <CreditCardInfo>
                                <cardNumber>' . esc_xml($ccardNumber) . '</cardNumber>
                                <expiryDate>' . esc_xml($exmonth . '/' . $exyear) . '</expiryDate>
                                <cvv>' . esc_xml($ccvv) . '</cvv>
                                <cardHolderName>' . esc_xml($cardHolderName) . '</cardHolderName>
                            </CreditCardInfo>
                        </Txn>
                    </TxnList>
                </Payment>
            </SecurePayMessage>';



        $response = wp_remote_post(
            $apiUrl,
            array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array('content-type' => 'text/xml'),
                'body' => $xmlRequest,
                'cookies' => array()
            )
        );

        if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {

            $apiResp = $response['body'];

            $xml = simplexml_load_string($apiResp);

            if (isset($xml->Status->statusCode) && $xml->Status->statusCode != '000') {

                $responsecode = $xml->Status->statusCode;
                $responsetext = $xml->Status->statusDescription;

            } elseif (isset($xml->Payment->TxnList->Txn->approved)) {

                $responsecode = $xml->Payment->TxnList->Txn->responseCode;
                $responsetext = $xml->Payment->TxnList->Txn->responseText;
                $transactionId = $xml->Payment->TxnList->Txn->txnID;

            } else {
                $responsecode = false;
            }

            $successResponseCode = array('00', '08', '11', '16', '77');
            if (in_array($responsecode, $successResponseCode) == true) {

                // Translators: %s is the transaction ID
                $complete_message = sprintf(__('Payment is successfully completed. Transaction ID: %s.', 'wc-primaldevs-payment-gateway-securepay'), $transactionId);
                $order->add_order_note($complete_message);
                $this->log("Success: $complete_message");

                $order->payment_complete();
                $woocommerce->cart->empty_cart();
                $redirectionUrl = $this->get_return_url($order);

                return array(
                    'result' => 'success',
                    'redirect' => $redirectionUrl
                );

            } else {
                wc_add_notice(__('Payment can not be processed ', 'wc-primaldevs-payment-gateway-securepay') . '(' . $responsetext . ')', $notice_type = 'error');
                // Translators: %s is the error message from SecurePay
                $this->log(sprintf(__('SecurePay Payment Gateway WC Error: %s', 'wc-primaldevs-payment-gateway-securepay'), $responsetext));
            }

        } else {
            wc_add_notice(__('Payment Gateway Error.', 'wc-primaldevs-payment-gateway-securepay'), $notice_type = 'error');
        }
    }


    /**
     * Get API URL based on mode for PrimalDevs Payment Gateway for SecurePay for WooCommerce.
     *
     * @param string $mode The mode of the gateway ('yes' for test, anything else for live).
     * @return string The corresponding API URL.
     */
    public function securepaywc_get_api_url($mode)
    {
        if ('yes' === $mode) {
            return 'https://test.securepay.com.au/xmlapi/payment';
        } else {
            return 'https://api.securepay.com.au/xmlapi/payment';
        }
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Log of PrimalDevs Payment Gateway for SecurePay for WooCommerce
     *
     * @since 1.0.0
     *
     * @param string $message
     */
    public function log($message)
    {
        if ($this->logging) {
            Primaldevs_SecurePay_Integration_WooCommerce_Logger::log($message);
        }
    }
}
