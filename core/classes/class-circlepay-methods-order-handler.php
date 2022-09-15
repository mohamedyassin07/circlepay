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
 * @since		1.0.0
 */

class CirclePay_Methods_Order_Handler{

	/**
	 * Order ID
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	public $order_id;

	/**
	 * Order Object
	 *
	 * @var		Object
	 * @since   1.0.0
	 */
	public $order;

	/**
	 * Invoice Number
	 *
	 * @var		String
	 * @since   1.0.0
	 */
	public $invoice_number;

	/**
	 * Invoice URL
	 *
	 * @var		String
	 * @since   1.0.0
	 */
	public $invoice_url;

	/**
	 * Connection Object
	 *
	 * @var		Object
	 * @since   1.0.0
	 */
	public $connection;

	/**
	 * WooComerce API Webhook url slug
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	public $webhook_slug;
	
	/**
	 * Set the order handler basics
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct( $order_id = false )
	{
		if( ! $order_id  && ( ! isset( $_GET['order_token'] ) || empty( $_GET['order_token'] ) ) ){
			return;
		}

		$this->set_order( $order_id );
		$this->set_webhook_slug();
		$this->set_connection();
		
		if( isset( $_GET['order_token'] ) ){
			add_action( 'woocommerce_api_' . $this->webhook_slug , array( $this , 'order_confirmation_webhook' ) );
		}
	}

	/**
	 * Set Order ID
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function set_order( $order_id )
	{
		if( ! $order_id ){
			$order_id = (int)$this->order_id_from_token( $_GET['order_token'] );
		}

		if( ! $order_id || get_post_type( $order_id ) !== 'shop_order' ){
			wp_die( __( 'You Can\'t access' , 'circlepay') );
		}

		$this->order_id = $order_id;
		$this->order 	= wc_get_order( $this->order_id );
	}

	/**
	 * Decrypt the token to get order ID
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	protected function order_id_from_token( $token )
	{
		require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-token-cryptor.php';
		return Token_Cryptor::decrypt( $token );
	}

	/**
	 * Set webhook slug
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function set_webhook_slug()
	{
		$this->webhook_slug = 'circlepay_order_confirmation';
	}

	/**
	 * Set CirclePay connection
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function set_connection()
	{
		require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-api-connection.php';
		$this->connection = new CirclePay_API;
	}

	/**
	 * Process the Payment transaction
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function process_payment()
	{
		
		if( ! $this->order ){
			return;
		}

		if( $this->mybe_create_customer() !== true ){
			return;
		}

		if( $this->create_invoice() !== true ){
			return;
		}

		if( $this->pay_invoice() !== true ){
			return;
		}

		if( $this->every_thing_done() ){
			return;
		}

		return array(
			'result' => 'success',
			'redirect' => $this->invoice_url
		);
	}

	/**
	 * Create a Customer if not exsist
	 * @access	public
	 * @since	1.0.0
	 * @return	true|Error_Object	true if created or exsist, or error message
	 */
	public function mybe_create_customer()
	{
		$customer_data = array(
			'first_name'    => $this->order->get_billing_first_name(),
			'last_name'     => $this->order->get_billing_last_name(),
			'email'         => $this->order->get_billing_email(),
			'mobile_number' => $this->order->get_billing_phone(),
		);

		$response = $this->connection->create_customer( $customer_data );
		if( is_object( $response ) && $response->errorCode !== 3111 ){
			return wc_add_notice( $this->connection->error_full_message( $response ) , 'error' );
		}
		
		return true;
	}

	/**
	 * Create invoice
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  Boolean
	 */
	public function create_invoice()
	{
		$data = array(
			'invoice' => array(
				'customer_mobile' => $this->order->get_billing_phone(),
				'items' => array(
					// due to that circlepay need every thing detailed 
					// then it will calculate the total itself
					// which not suitable with 100% of the plugins 
					// which perhaps change the prices, copouns ,totals dynamically
					// so we will all items as one item
					array(
						'name' 		=> $this->order->get_item_count() . ' ' . __( 'Products' ,  'circlepay' ),
						'price' 	=> $this->order->get_total(),
						'quantity' 	=> 1
					)
				),
				'due_date' => date( 'Y-m-d', strtotime('+1 day') ),
				'discount_type' => 'string',
				'discount_value' => 0,
				'discount_value_calculated' => 0,
				'extra_notes' => 'string',
				'return_policy' => 'string',
				'shipping_fees' => 0,
				'shipping_policy' => 'string',
				'status' => 0,
				'tax' => 0,
				'tax_value' => 0
			)
		);

		$response = $this->connection->create_invoice( $data ) ;

		if( ! $this->connection->is_response_error( $response ) && isset( $response['data'][0]['invoice_number'] ) ){
			$this->invoice_number = $response['data'][0]['invoice_number'];
			return update_post_meta( $this->order_id , 'circlepay_invoice_number', $response['data'][0]['invoice_number'] );
		}

		$error =  $this->connection->plugin_error_obj( '003' , __('Something Went wrong' , 'circlepay' ) );
		return wc_add_notice( $this->connection->error_full_message( $error ) , 'error' );
	}

	/**
	 * Pay Invoice
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  Boolean
	 */
	public function pay_invoice()
	{
		$data = array(
			'customer_mobile'	=> $this->order->get_billing_phone(),
			'invoice_number'	=> $this->invoice_number,
			'payment_method_id'	=> $this->order->get_payment_method(),
			'redirect_url' 		=> $this->return_url(),
		);

		$response = $this->connection->pay_invoice( $data ) ;

		if( ! $this->connection->is_response_error( $response ) && isset( $response['data'][0]['invoice_url'] ) ){
			$this->invoice_url = $response['data'][0]['invoice_url'];
			return update_post_meta( $this->order_id , 'circlepay_transaction_id', $response['data'][0]['transaction_id'] );
		}

		$error =  $this->connection->plugin_error_obj( '002' , __('Something Went wrong' , 'circlepay' ) );
		return wc_add_notice( $this->connection->error_full_message( $error ) , 'error' );
	}

	/**
	 * Check if all done that we have an invoice url
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  Boolean
	 */
	public function every_thing_done()
	{
		if( ! $this->invoice_url ){
			$error =  $this->connection->plugin_error_obj( '001' , __('Something Went wrong' , 'circlepay' ) );
			return wc_add_notice( $this->connection->error_full_message( $error ) , 'error' );
		}
	}

	/**
	 * Get the return url
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  String
	 */
	public function return_url()
	{
		$token = $this->genereate_token();
		update_post_meta( $this->order_id , 'circlepay_token', $token );
		return WC()->api_request_url( $this->webhook_slug ) . '?order_token=' . $token ;
	}

	/**
	 * Genereate order token
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  String
	 */
	public function genereate_token()
	{
		require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-token-cryptor.php';
		return Token_Cryptor::encrypt( $this->order_id );
	}

	/**
	 * Create invoice V2
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  Boolean
	 */
	public function create_invoice_v2()
	{
		$data = array (
			'payment_method_id'	=> $this->order->get_payment_method(),
			'redirect_url'		=> $this->return_url(),
			'status'			=> 0,
			'sub_total_value'	=> $this->order->get_subtotal(),
			'tax'				=> $this->order->get_taxes(),
			'tax_value'			=> $this->order->get_tax_totals(),
			'total_value'		=> $this->order->get_total(),

			'customer' => array(
				'address'		=> $this->order->get_billing_address_1(), 
				'city'			=> $this->order->get_billing_city(),
				'country'		=> $this->order->get_billing_country(),
				'email'			=> $this->order->get_billing_email(),
				'first_name'	=> $this->order->get_billing_first_name(),
				'last_name'		=> $this->order->get_billing_last_name(),
				'mobile_number'	=> $this->order->get_billing_phone()
			),

			'invoice' => array(
				'custom_attributes' => array(
					'cust_1' => 'val_1',
					'cust_2' => 'val_2'
				),
				'due_date' 					=> date( 'Y-m-d', strtotime('+1 day') ),
				'discount_value_calculated' => 0,
				'items' 					=> array(
					// due to that circlepay need every thing detailed 
					// then it will calculate the total itself
					// which not suitable with 100% of the plugins 
					// which perhaps change the prices, copouns ,totals dynamically
					// so we will add all items as one item
					array(
						'name'		=> $this->order->get_item_count() . ' ' . __( 'Products' ,  'circlepay' ),
						'price'		=> $this->order->get_total(),
						'quantity'	=> 1
					)
				),

			),
		);

		$response = $this->connection->create_invoice_v2( $data ) ;

		if( ! $this->connection->is_response_error( $response ) && isset( $response['data'][0]['transaction_id'] ) ){
			$this->transaction_id = $response['data'][0]['transaction_id'];
			$this->invoice_url = $response['data'][0]['invoice_url'];
			return true;
		}

		if( is_object( $response ) ){
			return wc_add_notice( $this->connection->error_full_message( $response ) , 'error' );
		}

		$unexpected_error =  $this->connection->plugin_error_obj( '005' , __('Something Went wrong' , 'circlepay' ) );
		return wc_add_notice( $this->connection->error_full_message( $unexpected_error ) , 'error' );
	}

	/**
	 * Order confirmation webhook Callback
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function order_confirmation_webhook()
	{
		if( $this->is_paid_circlepay_invoice() ){
			$this->complete_the_order();
			wp_redirect( $this->order->get_checkout_order_received_url() );
		}else {
			// if something went wrong
			// or not paid yet
			$this->fail_the_order();
		}
	}

	/**
	 * Check if the CirclePay invoice is paid
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  Boolean
	 */
	private function is_paid_circlepay_invoice()
	{
		$invoice_num 	= get_post_meta( $this->order_id ,'circlepay_invoice_number' , true  );
		$response 		= $this->connection->get_invoice( $invoice_num );

		if( $this->connection->is_response_error( $response ) ){
			wp_die(__( 'Not allowed to complete order confirmation process due to an API related error' ,'circlepay') );
		}

		if( $response['data'][0]['status'] === 2 ){
			return true;
		}

		return false;
	}

	/**
	 * Set the order as completed
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	private function complete_the_order()
	{
		$this->order->payment_complete();
		wc_reduce_stock_levels( $this->order_id );
		
		global $woocommerce;
		$woocommerce->cart->empty_cart(); 
	}

	/**
	 * Set the order as failed
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	private function fail_the_order()
	{
		$this->order->update_status( 'failed' );
		wp_die( __( 'Payment didn\'t completed/paid correctly' , 'circlepay') );
	}
}

