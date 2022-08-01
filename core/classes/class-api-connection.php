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
	 * The API URL base
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected $api_url;

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
	protected $api_sandbox;

	
	/**
	 * Our Circlepay_API constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->set_api_url();
		$this->set_authentication_info();
	}

	private function set_api_url( )
	{
		if( $this->api_url === null ){
			return $this->api_sandbox ? 'https://sandbox-openapi.circlepay.ai/' : 'https://sandbox-openapi.circlepay.ai/';
		}
	}

	private function get_api_url( ){
		return $this->api_url;
	}

	private function get_endpoitn_url( $endpoint_base ){
		return $this->get_api_url() . trim( $endpoint_base ) . '/';
	}

	private function set_authentication_info( )
	{
		$this->account_key 		= '6414152f-b48d-3670-a18b-475698095080';
		$this->account_token 	= 'eyJhbGciOiJkaXIiLCJlbmMiOiJBMTI4Q0JDLUhTMjU2Iiwia2lkIjpudWxsLCJjdHkiOiJKV1QifQ..g_XK26VXQw0V8SeKsgsF8w.xUFTVCRfRY3tba66QuCjAAcjdRDKPH0jRynRyifbyzaibMQtXkxT0dqywXZ-sLhBWLZb3JGSPn8wehnh1qpq0jtZIzOhIkPUvg797jUlJpVFajcWiFbOMoFlo8W-ZtNJfr4NL_rX8XZSHD56OmTHEjxlgTzWfayysIyFZyGEKhKiORit38fSrmLWyRA_xfHzd_PlY_VJufj01Pid2yP0A-4Yo27KmrjcUpeapsdyurFh0yvvH2CzYrflOJywOqTq8ihCUKg3hoTGd8zVMzUeKzy53-Cz9u2nZpdwrirzaCFbZTPgH0sXmwm-xx9EJ7JtRe7Gz4KweZIXez-0ggGXjFzEGKeGdanmtC0S6JCXNcN3thXUQsjtAwxzEEVEpOar.1YpsgEh0PczzFjpX5NStlw';
		$this->merchant_token 	= 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MTQwLCJpc1ZlcmlmaWVkIjpmYWxzZSwidG9rZW4iOiIiLCJmb3JnZXRQYXNzd29yZHRva2VuIjoiIiwiZ29vZ2xlT2F1dGhJRCI6IiIsImZhY2Vib29rT2F0aElkIjoiIiwiYXV0aHlfaWQiOiIiLCJzdGF0dXMiOiIwIiwiYWRkcmVzcyI6IiIsImJ1c2luZXNzTmFtZSI6IiIsImZpcnN0TmFtZSI6ImZpcnN0X25hbWVfaW5mbyIsImxhc3ROYW1lIjoibGFzdF9uYW1lX2luZm8iLCJlbWFpbCI6ImVtYWlsQGluZm8uY29tIiwibW9iaWxlIjoiKzIwMTAwMDAwMDAwMSIsImJ1c2luZXNzQWRkcmVzcyI6ImJ1c2luZXNzX2FkZHJlc3NfaW5mbyIsInVzZXJuYW1lIjpudWxsLCJ1cGRhdGVkQXQiOiIyMDIyLTA3LTI5VDE2OjI3OjIzLjA5NloiLCJjcmVhdGVkQXQiOiIyMDIyLTA3LTI5VDE2OjI3OjIzLjA5NloiLCJhcHBfbmFtZSI6Indvb2NvbW1lcmNlIiwiaWF0IjoxNjU5MTEyMDQzfQ.2BNlJcT-sqVs-HWHd6ZL-ImJlDmUhtWvFdaJ3so5Ohs';
	}

	/**
	 * Execute a connection to Payzaty API
	 * @access	public
	 * @since	1.6.0
	 * @return	array	needed data from the connection
	 */
	public function create_connection( $url, $type = 'POST', $body = array() )
	{
		$headers = array(
		  'X-Source' => 8, // 8:WooCommerce
		  'X-Build' => 1,
		  'X-Version' => 1,
		  'X-Language' => 'ar',
		  'X-MerchantNo' => $this->merchant_no,
		  'X-SecretKey' => $this->merchant_secret , 
		  'Content-Type' => 'application/x-www-form-urlencoded',
		);
		$response = wp_remote_post( 
		  $url,
		  array(
			'method' => $type,
			'headers' => $headers,
			'timeout' => 10,
			'body' => $body,
		  )
		);
		return json_decode( wp_remote_retrieve_body( $response ), true );

		if( $body['success'] == true && isset($body['checkoutUrl']) )
	{
			return array( 'id' => $body['checkoutId'], 'url' => $body['checkoutUrl'], 'checkout_id' => $body['checkoutId'] );
		}
		return false;
	}

	/**
	 * Get the full URL the connection will use
	 * 
	 * @access	public
	 * @since	1.6.0
	 * @return	string	url to the sandbox/live enviroment and the required end point
	 */
	public function get_url( $path = '' )
	{
		return $this->sandbox === CHECKBOX_TRUE_VAL ? 'https://sandbox.payzaty.com/payment/'.$path : 'https://www.payzaty.com/payment/'.$path;
	}

	/**
	 * Get checkout status from payzaty API
	 * 
	 * @access	public
	 * @since	1.6.0
	 * @return	array	paymanet process status
	 */
	public function get_checkout_status( $code )
	{
		$url = $this->get_url('status/'. $code );
		return $this->create_connection( $url, 'GET');
	}

	/**
	 * create a new checkout request
	 * 
	 * @access	public
	 * @since	1.6.0
	 * @return	array	new chechout request data
	 */
	public function create_new_chechout_order( $body, $order_id )
	{
		$confirmation_endpoint_data = Payzaty_Custom_End_Points::confirmation_endpoint_data();
		$body['ResponseUrl'] = get_rest_url(). $confirmation_endpoint_data['namespace']. '/'. $confirmation_endpoint_data['route']. '/'. $order_id;

		$url 		= $this->get_url('checkout');
		$response 	= $this->create_connection($url, 'POST', $body);

		if( $response['success'] == true && isset($response['checkoutUrl']) )
	{
			return array( 'id' => $response['checkoutId'], 'url' => $response['checkoutUrl'], 'checkout_id' => $response['checkoutId'] );
		}

		return false;
	}


}
