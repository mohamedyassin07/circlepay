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

class WC_Gateway_CirclePay extends WC_Payment_Gateway {

	/**
	 * checkbox true value
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	private $checkbox_true_val =  'yes';

	/**
	 * Define the payment gateway defults
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	public function __construct()
	{
		$this->set_generel_settings();
		$this->init_form_fields();
		$this->init_settings();
		$this->set_frontend_settings();
		$this->saves_the_settings();
	}

	/**
	 * Set generel settings
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	public function set_generel_settings()
	{
		$this->id = CIRCLEPAY_SLUG;
		$this->has_fields = false ;
		$this->method_title = __( 'CirclePay', 'circlepay' );
		$this->method_description = __( 'CirclePay is a financial service hub for e-commerce helping  merchants to connect with multiple payment gateways through a single plugin.', 'circlepay' );
		$this->supports = array(
			'products'
		);	
	}

	/**
	 * Registers WooCommerce Admin Fields
	 * 
	 * @access	public
	 * @since	1.0.0
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'gateway_settings' => array(
				'title' => __( 'Gateway Settings', 'circlepay' ),
				'type' => 'title',
			),
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'circlepay' ),
				'type' => 'checkbox',
				'label' => __( 'Enable CirclePay Generally', 'circlepay' ),
				'default' => $this->checkbox_true_val,
			),
			'title' => array(
				'title' => __( 'Title', 'circlepay' ),
				'type' => 'text',
				'description' => __( 'ُExtra title will appear on the WooCommerce payment methods page', 'circlepay' ),
				'default' => __( 'CirclePay', 'circlepay' ),
			),
			'account_info' => array(
				'title' => __( 'Account Info', 'circlepay' ),
				'type' => 'title',
			),
			'sandbox' => array(
				'title' => __( 'Enable Sandbox', 'circlepay' ),
				'type' => 'checkbox',
				'label' => __( 'Sandbox enables a testing environment to test the whole process before you go production.', 'circlepay' ),
				'default' => $this->checkbox_true_val,
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
	 * Set frontend settings
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	public function set_frontend_settings()
	{
		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->icon = CIRCLEPAY_PLUGIN_URL .'assets/images/circlepay-logo.jpg';
	}

	/**
	 * Save the gateway settings
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	public function saves_the_settings()
	{
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}
}