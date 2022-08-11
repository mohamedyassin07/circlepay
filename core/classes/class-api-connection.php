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
	protected static $account_key;

	/**
	 * The API account token
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected static $account_token;

	/**
	 * The API merchant token
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected static $merchant_token;

	/**
	 * The endpoint url
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected static $connection_url;

	/**
	 * The API Enviroment Status
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected static $sandbox;


	private static function get_api_url()
	{
		return self::$sandbox ? 'https://sandbox-openapi.circlepay.ai/' : 'https://sandbox-openapi.circlepay.ai/';
	}

	public static function set_connection_url( $endpoint_base )
	{
		self::$connection_url = self::get_api_url() . trim( $endpoint_base ) ;
	}

	public static function get_endpoint_base( $endpoint_base , $id = false )
	{
		return trim( $endpoint_base ) . $id ?: '/' . $id ;
	}

	private static function set_authentication_info()
	{
		$data =  WC()->payment_gateways->get_available_payment_gateways()[ CIRCLEPAY_SLUG ]->settings;

		self::$account_key 		= sanitize_key( $data['account_key'] );
		self::$account_token 	= sanitize_key( $data['account_token'] );
		self::$merchant_token 	= sanitize_key( $data['merchant_token'] );
		self::$sandbox 			= sanitize_key( $data['sandbox'] ) !== 'yes' ? false :  true ;
	}

	/**
	 * Execute a connection to CirclePay API
	 * @access	public
	 * @since	1.6.0
	 * @return	array|string	needed data as array or string error message
	 */
	public static function create_connection( $endpoint_url, $type = 'POST', $body = array() )
	{
		self::set_authentication_info();
		self::set_connection_url( $endpoint_url );

		$headers = array(
			'Content-Type'		=> 'application/json',
			'Accept'			=> 'application/json',
			'account-key'		=> self::$account_key,	
			'account-token'		=> 'Bearer ' . self::$account_token,	
			'merchant-token'	=> 'Bearer ' . self::$merchant_token
		);

		$response = wp_remote_post( 
			self::$connection_url,
			array(
				'method' => $type,
				'headers' => $headers,
				'timeout' => 30,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			echo __( 'Something went wrong in setup the connection: ', 'circlepay' ) . $response->get_error_message() ;
			return false;
		}

		$response =  json_decode( wp_remote_retrieve_body( $response ), true );

		if( isset( $response['isError'] ) && $response['isError'] === true ){
			echo $response['errorCode'] . ' : ' . $response['message'] . ' : ' . $response['details'];
			return false;
		}

		return $response;
	}


	public static function payment_gateways(){
		$base = self::get_endpoint_base( 'payment/gateway/list' );
		return self::create_connection( $base, 'GET');
	}

	public static function enabled_payment_gateways(){
		$base = self::get_endpoint_base( 'merchants/payment/gateway/list' );
		return self::create_connection( $base, 'GET');
	}

}
