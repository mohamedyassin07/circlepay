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


	public function __construct()
	{
		$this->set_connection_basics();
	}

	private function set_connection_basics()
	{
		$circlepay_data 		= get_option( 'woocommerce_circlepay_settings');
		$this->account_key 		= sanitize_text_field( $circlepay_data['account_key'] );
		$this->account_token 	= sanitize_text_field( $circlepay_data['account_token'] );
		$this->merchant_token 	= sanitize_text_field( $circlepay_data['merchant_token'] );
		$this->sandbox 			= sanitize_text_field( $circlepay_data['sandbox'] ) !== 'yes' ? false :  true;
		$this->api_url 			= $this->sandbox ? 'https://sandbox-openapi.circlepay.ai/' : 'https://sandbox-openapi.circlepay.ai/';
	}

	public function get_connection_url( $endpoint_base , $id = false )
	{
		return $this->api_url . trim( $endpoint_base ) . ( $id ? '/' . $id : '' ) ;
	}

	/**
	 * Execute a connection to CirclePay API
	 * @access	public
	 * @since	1.6.0
	 * @return	array|string	needed data as array or string error message
	 */
	public function create_connection( $endpoint_url, $type, $body = array() )
	{
		// $response = $this->create_wp_remote_connection( $endpoint_url, $type, $body = array() );

		// if( $response === null ){
		// 	echo "second</br>";
		// 	$response = $this->curl_create_connection( $endpoint_url, $type, $body );
		// }

		$response = $this->curl_create_connection( $endpoint_url, $type, $body );

		if( $this->is_response_error( $response ) ){
			return $this->error_obj( $response );
		}

		return $response;
	}
	public function is_connection_error( $response )
	{
		if(
			( is_array( $response) && isset( $response['error'] ) && ! empty( $response['error'] ) && $response['status']  )
			||
			( is_object( $response ) && property_exists( $response, 'error') &&  ! empty( $response->error  ) && $response->status )
		){
			return true;
		}
	}

	public function is_response_error( $response )
	{
		return isset( $response['isError'] ) && $response['isError'] ? true : false;
	}

	public function error_obj( $response )
	{
		if( is_wp_error( $response ) ){
			$error_obj 				= new stdObject();
			$error_obj->message 	= __( 'Something went wrong in setup the connection: ', 'circlepay' );
			$error_obj->details 	= $response->get_error_message();
			$error_obj->errorCode 	= "cpp000";
		}

		if( $this->is_response_error( $response ) ){
			$error_obj =  (object) $response;
		}

		return $error_obj;
	}

	public function plugin_error_obj( $code , $message, $details = false ){
		$error = array (
			'errorCode' => 'cpp' . $code,
			'message' => $message,
			'details'=> $details
		);
		return $this->error_obj( $error );
	}

	public function error_full_message( $error )
	{
		return "({$error->errorCode}) {$error->message} : $error->details";
	}

	public function create_wp_remote_connection(  $endpoint_url, $type, $body = array() )
	{
		$headers = array(
			'Content-Type'		=> 'application/json',
			'Accept'			=> 'application/json',
			'account-key'		=> $this->account_key,	
			'account-token'		=> 'Bearer ' . $this->account_token,	
			'merchant-token'	=> 'Bearer ' . $this->merchant_token
		);

		$response = wp_remote_request( 
			$endpoint_url,
			array(
				'body' => $body,
				'method' => $type,
				'headers' => $headers,
				'timeout' => 30,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $this->error_obj( $response ) ;
		}
		
		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	public function curl_create_connection( $endpoint_url, $type, $body = array() )
	{
		$curl = curl_init();

		curl_setopt_array(
			$curl, 
			array(
				CURLOPT_URL => $endpoint_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => $type,
				CURLOPT_POSTFIELDS => json_encode( $body ),
				CURLOPT_HTTPHEADER => array(
					'merchant-token: Bearer ' . $this->merchant_token,
					'account-token: Bearer ' . $this->account_token,
					'account-key: ' . $this->account_key,
					'Content-Type: application/json'
				),
			)
		);

		$response = curl_exec($curl);

		curl_close($curl);
		return json_decode( $response , true );
	}

	public function payment_gateways(){
		$url = $this->get_connection_url( 'payment/gateway/list' );
		return $this->create_connection( $url, 'GET');
	}

	public function enabled_payment_gateways(){
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
		$url 		= $this->get_connection_url( 'customer/create' );
		return $this->create_connection( $url, 'POST', $data );
	}
	
	public function create_invoice( $data ){
		$url = $this->get_connection_url( 'invoice/create' );
		return $this->create_connection( $url, 'POST' , $data );
	}

	public function pay_invoice( $data ){
		$url = $this->get_connection_url( 'invoice/pay' );
		return $this->create_connection( $url, 'POST' , $data );
	}

	
	
}
