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
	}

	public function set_generel_settings()
	{
		$this->id = CIRCLEPAY_SLUG;
		$this->has_fields = false ;
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
}