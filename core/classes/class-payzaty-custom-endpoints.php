<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Payzaty_Custom_End_Points
 *
 * This class contains repetitive functions that
 * are create the api related functions
 *
 * @package		PAYZATY
 * @subpackage	Classes/Payzaty_Custom_End_Points
 * @author		Payzaty
 * @since		1.6.0
 */

class Payzaty_Custom_End_Points  {

	public function __construct(){
		add_action( 'rest_api_init', array( $this, 'payzaty_confirmation_endpoint' ) , 10000 );
	}

	/**
	 * get the data of the website url which prepared to recieve the payzaty response
	 * check the class-payzaty-api
	 * 
	 * @access	public
	 * @since	1.6.0
	 * @return	array	An array of the endpoint data
	 */
	public static function confirmation_endpoint_data(){
		return array( 'namespace' => 'wc/v3' , 'route' => 'payzaty_confirmation' );
	}

	/**
	 * get the website order url which prepared to recieve the payzaty response
	 * 
	 * @access	public
	 * @since	1.6.0
	 * @return	string	the full order payment confirmation URL
	 */
	public function get_confirmation_endpoint_url($order_id = 0){
		return get_rest_url(). $this->confirmation_endpoint_url_base(). $order_id;
	}

	/**
	 * Create endpoint for order payment confirmation
	 * Payzaty need this endpoint to forward the payment process result.
	 * 
	 * @access	public
	 * @since	1.6.0
	 * @return	void
	 */
	public function payzaty_confirmation_endpoint(){
		$endpint_data =  $this->confirmation_endpoint_data();
		register_rest_route( $endpint_data['namespace'], $endpint_data['route'].'/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => array( $this, 'payzaty_confirmation_endpoint_callback' ),
			'permission_callback' => '__return_true'
		));
	}
	
	/**
	 * get the payzaty settings data
	 * 
	 * @access	public
	 * @since	1.6.0
	 * @return	array	all base date needed in the payzaty API request
	 */
	public function get_payment_method_data(){
		$data = WC()->payment_gateways->get_available_payment_gateways()['payzaty']->settings;
		return array( 'sandbox' => $data['sandbox'], 'no' => $data['merchant_id'], 'key' => $data['secret_key'] );
	}

	/**
	 * The Logic done when Payzaty response on order process endpoint
	 * 
	 * @access	public
	 * @since	1.6.0
	 * @return	void
	 */
	public function payzaty_confirmation_endpoint_callback( $request ) {
		$order_id = $request->get_params()['id'];
		$order = new WC_Order($order_id);

		$checkout_id =  get_post_meta($order_id, 'payzaty_checkout_id' ,true);

		if(!isset($_GET['checkoutId']) || $checkout_id !== $_GET['checkoutId']){
			return array( __("Something went wrong", 'payzaty' ));
		}

		$method_data	= $this->get_payment_method_data();
		$connection		= new Payzaty_Gate_Way_API_Connecting($method_data['sandbox'], $method_data['no'], $method_data['key']);
		$status 		= $connection->get_checkout_status( $checkout_id );

		if($status['success'] === true && $status['IsPaid'] === true){
			$order->payment_complete();
			wp_redirect($order->get_checkout_order_received_url());
		} else {
			$order->update_status('cancelled');
			wp_redirect($order->get_cancel_order_url());
		}
		exit;
	}
}