<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class CirclePay_Available_Methods
 *
 * @package		CIRCLEPAY
 * @subpackage	Classes/CirclePay_Available_Methods
 * @author		Mohamed Yassin
 * @since		1.0.0
 */
class CirclePay_Available_Methods{

	/**
	 * The API Enviroment Status
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected $sandbox;


	public function __construct()
	{
		add_filter( 'woocommerce_payment_gateways', array( $this,'add_circlepay_method_to_wc' ) );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'add_circlepay_available_methods_to_wc' ) );
		add_filter( 'woocommerce_checkout_fields' , array( $this, 'override_checkout_fields' ) );
	}

	public function add_circlepay_method_to_wc( $gateways ) {
		require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-wc-gateway-circlepay.php';
		$gateways[] = 'WC_Gateway_CirclePay'; 
		return $gateways;
	}

	public function add_circlepay_available_methods_to_wc( $available_gateways )
	{
		// we need this function to work only
		// in the checkout page 
		// so the user will only deal with the CirclePay
		// available methods in the checkout page
		if( ! is_checkout() ){
			return $available_gateways;
		}

		// remove circlepay itself as a payment method
		if( array_key_exists( CIRCLEPAY_SLUG ,  $available_gateways ) ){
			unset ( $available_gateways[ CIRCLEPAY_SLUG ] );
		}
		
		$available_gateways = array_merge( $available_gateways , $this->circlepay_available_methods() );
		
		return $available_gateways;
	}

	public function circlepay_available_methods()
	{
		$available_methods = [];

		// add the CirclePay available methods		
		require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-api-connection.php';
		require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-methods-class-generator.php';

		$connection = new CirclePay_API;
		$methods 	= $connection->enabled_payment_methods();

		if( isset( $methods['data']) ){
			$generator = new Methods_Class_Generator( $methods['data'] );
			$new_methods = $generator->get_available_methods();

			foreach ( $new_methods as $key => $method) {
				include_once ( $method['file'] );

				if( class_exists( $method['class_name'] )){
					$available_methods[ $key ] =  new $method['class_name'];
				}  
			
			}
		}

		update_option( 'circlepay_available_methods' , $available_methods );
		return $available_methods ;
	}
	public function override_checkout_fields( $fields ) {

		if( isset( $fields['billing']['billing_phone'] ) ){
			$fields['billing']['billing_phone']['label'] 		= __( 'Pone (International Format)', 'circlepay' );
			$fields['billing']['billing_phone']['placeholder'] 	= __( 'Must be inetrnational format : +20123456789', 'circlepay' );
		}else{
			$fields['billing']['billing_phone']['label'] 		= __( 'Pone (International Format)', 'circlepay' );
			$fields['billing']['billing_phone']['placeholder'] 	= __( 'Must be inetrnational format : +20123456789', 'circlepay' );
			$fields['billing']['billing_phone']['required'] 	= true;
			$fields['billing']['billing_phone']['required'] 	= true;
			$fields['billing']['billing_phone']['type'] 		= 'tel';
			$fields['billing']['billing_phone']['class'] 		= array('form-row-wide');
			$fields['billing']['billing_phone']['validate'] 	= array('phone');
			$fields['billing']['billing_phone']['autocomplete'] = 'tel';
			$fields['billing']['billing_phone']['priority'] 	= 100;
		}
	
		return $fields;
	}
	
}