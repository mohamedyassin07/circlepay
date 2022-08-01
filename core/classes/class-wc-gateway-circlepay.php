<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WC_Gateway_CirclePay
 *
 * This class extends the WC_Payment_Gateway Class
 * to add CirclePay payment method to the woocomerce system
 *
 * @package		CIRCLEPAY
 * @subpackage	Classes/WC_Gateway_CirclePay
 * @author		CirclePay
 * @since		1.6.0
 */

class WC_Gateway_CirclePay extends WC_Payment_Gateway {

	/**
	 * checkbox true value
	 *
	 * @var		string
	 * @since   1.6.0
	 */
	private $checkbox_true_val =  'yes';

	public function __construct(){

		$this->set_generel_settings();
		$this->init_form_fields();
		$this->init_settings();
		$this->set_frontend_settings();
		$this->add_hooks();
	}


	public function set_generel_settings()
	{
		$this->id = 'circlepay';
		$this->has_fields =  false ;
		$this->method_title = __( 'CirclePay', 'circlepay' );
		$this->method_description = __( 'CirclePay Settings', 'circlepay' );
		$this->supports = array(
			'products'
		);	
	}

	public function set_frontend_settings()
	{
		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->icon = CIRCLEPAY_PLUGIN_URL .'/assets/images/circlepay-logo.png';
	}

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.6.0
	 */
	private function add_hooks(){
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_wc_gateway_paypal', array( $this, 'check_ipn_response' ) );
	}

	/**
	 * Registers WooCommerce Admin Fields
	 * 
	 * @access	public
	 * @since	1.6.0
	 * @return	void
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'circlepay' ),
				'type' => 'checkbox',
				'label' => __( 'Enable CirclePay', 'circlepay' ),
				'default' => $this->checkbox_true_val,
			),
			'title' => array(
				'title' => __( 'Title', 'circlepay' ),
				'type' => 'text',
				'description' => __( 'This controls the title which will appears in chechout page.', 'circlepay' ),
				'default' => __( 'CirclePay', 'circlepay' ),
			),
			'description' => array(
				'title'       => __( 'Description', 'circlepay' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'circlepay' ),
				'default'     => __( 'Pay with your credit card via our super-cool payment gateway.', 'circlepay' ),
			),	
			'sandbox' => array(
				'title' => __( 'Enable Sandbox', 'circlepay' ),
				'type' => 'checkbox',
				'label' => __( 'Sandbox enables a testing environment to test the whole process before you go production.', 'circlepay' ),
				'default' => $this->checkbox_true_val,
			),
			'account_info' => array(
				'title' => __( 'Account Info', 'circlepay' ),
				'type' => 'title',
			),
			'account_key' => array(
				'title' => __( 'Account Key', 'circlepay' ),
				'type' => 'text',
			),
			'account_token' => array(
				'title' => __( 'Account Token', 'circlepay' ),
				'type' => 'text',
			),
			'merchant_token' => array(
				'title' => __( 'Merchant Token', 'circlepay' ),
				'type' => 'text',
			),			
		);			
	}

	/**
	 * Process the payment
	 *
	 * @access	public
	 * @since	1.6.0
	 * @param	string $order_id is the current order id
	 * @return	array data of the payment process opened for this order
	 */
	public function process_payment( $order_id ) {
		$billing_details =  $this->billing_details($order_id);

		if($billing_details ==  false){
			wc_add_notice( __('Missing Data' , 'circlepay')  , 'error' );
			return;
		}

		$connection = new CirclePay_Gate_Way_API_Connecting( $this->get_option('sandbox'), $this->get_option('merchant_id'), $this->get_option('secret_key') );
		$checkout_data = $connection->create_new_chechout_order( $billing_details, $order_id);
		
		if($checkout_data === false){
			wc_add_notice( __('Some things went wrong when connecting to CirclePay, Please try again.' , 'circlepay')  , 'error' );
			return;
		}

		update_post_meta( $order_id, 'circlepay_checkout_id', $checkout_data['checkout_id'] );

		return array(
			'result' => 'success',
			'redirect' => $checkout_data['url']
		);
	}

	/**
	 * billing_details 
	 * 
	 * @access public
	 * @since	1.6.0
	 * @param	string $order_id is the current order id
	 * @return	array prepared array of the billings details contains all the required data
	 */
	public function billing_details( $order_id ){
		$order = new WC_Order( $order_id );

		return array(
			'Name'  => $order->get_billing_first_name(). ' ' . $order->get_billing_last_name(),
			'Email' => $order->get_billing_email(),
			'PhoneCode' => '000', // no more codes untill now
			'PhoneNumber' => $order->get_billing_phone(),
			'Amount' => $order->get_total(),
			'CurrencyID' => 1, // no more curruncies untill now
			'UDF1' => $order_id,
			'UDF2' => '',
			'UDF3' => '',
		);
	}

}