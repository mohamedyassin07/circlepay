<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Circlepay_API
 *
 * @package		CIRCLEPAY
 * @subpackage	Classes/Circlepay_API
 * @author		Mohamed Yassin
 * @since		1.0.0
 */
class CirclePay_API{

	/**
	 * The API account Unique key
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected $account_key;

	/**
	 * The API account token
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected $account_token;

	/**
	 * The API merchant token
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected $merchant_token;

	/**
	 * The API Enviroment Status
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected $sandbox;

	/**
	 * The API URL
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected $api_url;

	/**
	 * Connection constructor 
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->set_connection_basics();
	}

	/**
	 * Set the connection basics info
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	protected function set_connection_basics()
	{
		$circlepay_data 		= get_option( 'woocommerce_circlepay_settings');
		$this->account_key 		= sanitize_text_field( $circlepay_data['account_key'] );
		$this->account_token 	= sanitize_text_field( $circlepay_data['account_token'] );
		$this->merchant_token 	= sanitize_text_field( $circlepay_data['merchant_token'] );
		$this->sandbox 			= sanitize_text_field( $circlepay_data['sandbox'] ) !== 'yes' ? false :  true;
		$this->api_url 			= $this->sandbox ? 'https://sandbox-openapi.circlepay.ai/' : 'https://sandbox-openapi.circlepay.ai/';
	}

	/**
	 * Execute a connection to CirclePay API
	 * @access	public
	 * @since	1.0.0
	 * @return	Array|String needed data as array or string error message
	 */
	public function create_connection( $endpoint_url, $type, $body = array() )
	{

		$headers = array(
			'Content-Type'		=> 'application/json',
			'Accept'			=> 'application/json',
			'account-key'		=> $this->account_key,	
			'account-token'		=> 'Bearer ' . $this->account_token,	
			'merchant-token'	=> 'Bearer ' . $this->merchant_token,
		);

		$response = @wp_remote_request( 
			$endpoint_url,
			array(
				'body' 			=> json_encode( $body ),
				'method' 		=> $type,
				'headers' 		=> $headers,
				'timeout' 		=> 30,
				'sslverify' 	=> true,
				'data_format' 	=> 'body'
			)
		);

		if( $this->is_response_error( $response ) ){
			return $this->error_obj( $response );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if( empty( $response ) ){
			return $this->plugin_error_obj( '004' , __('Empty response body from CirclePay server' , 'circlepay' ) );
		}

		return $response;
	}
		
	/**
	 * Check if their is an error in the response
	 * @access	public
	 * @since	1.0.0
	 * @return	Boolean
	 */
	public function is_response_error( $response )
	{
		if(
			is_wp_error( $response )
			||
			( is_object( $response ) && property_exists( $response, 'error') &&  ! empty( $response->error  ) && $response->status )
			||
			( isset( $response['isError'] ) && $response['isError'] )
			||
			( is_array( $response) && isset( $response['error'] ) && ! empty( $response['error'] ) && $response['status']  )
			||
			( is_array( $response) && isset( $response['errorCode'] ) && $response['errorCode'] !== 0 )
		){
			return true;
		}
	}

	/**
	 * Check if their is an error in the response
	 * @access	public
	 * @since	1.0.0
	 * @return	Object	error object
	 */
	public function error_obj( $response )
	{		
		if( is_wp_error( $response ) ){
			$error_obj 				= new stdClass;
			$error_obj->message 	= __( 'Something went wrong in setup the connection: ', 'circlepay' );
			$error_obj->details 	= $response->get_error_message();
			$error_obj->errorCode 	= "cpp000";
		}

		if( $this->is_response_error( $response ) ){
			$error_obj =  (object) $response;
		}

		return $error_obj;
	}

	/**
	 * create a custom error object
	 * @access	public
	 * @since	1.0.0
	 * @return	Object	error object
	 */
	public function plugin_error_obj( $code , $message, $details = '' ){
		$error = array (
			'errorCode' => 'cpp' . $code, //{c}ircle{p}ay{p}lugin
			'message' => $message,
			'details'=> $details
		);
		return $this->error_obj( $error );
	}

	/**
	 * Get Connection url
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  String
	 */
	public function get_connection_url( $endpoint_base , $id = false )
	{
		return $this->api_url . trim( $endpoint_base ) . ( $id ? '/' . $id : '' ) ;
	}

	/**
	 * Full error Message
	 * @access	public
	 * @since	1.0.0
	 * @return	String
	 */
	public function error_full_message( $error )
	{
		return "({$error->errorCode}) {$error->message} : $error->details";
	}

	/*
	|--------------------------------------------------------------------------
	| CirclePay endpoints connections
	|--------------------------------------------------------------------------
	*/

	public function payment_gateways()
	{
		$url = $this->get_connection_url( 'payment/gateway/list' );
		return $this->create_connection( $url, 'GET');
	}

	public function enabled_payment_gateways()
	{
		$url = $this->get_connection_url( 'merchants/payment/gateway/list' );
		return $this->create_connection( $url, 'GET');
	}

	public function enabled_payment_methods()
	{
		$url = $this->get_connection_url( 'merchants/payment/method/list' );
		return $this->create_connection( $url, 'GET');
	}

	public function create_customer( $data )
	{
		$url = $this->get_connection_url( 'customer/create' );
		return $this->create_connection( $url, 'POST', $data );
	}
	
	public function create_invoice( $data )
	{
		$url = $this->get_connection_url( 'invoice/create' );
		return $this->create_connection( $url, 'POST' , $data );
	}

	public function pay_invoice( $data )
	{
		$url = $this->get_connection_url( 'invoice/pay' );
		return $this->create_connection( $url, 'POST' , $data );
	}

	public function get_invoice( $invoice_number )
	{
		$url = $this->get_connection_url( 'invoice/get', $invoice_number );
		return $this->create_connection( $url, 'GET' );
	}	
}