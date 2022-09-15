<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class CirclePay_Sample_Method_Class
 *
 * This class will be used to generate new classes
 * for the available methods dynamically so we cover the current dozen of current methods and all the methods will be added in the future
 *
 * @package		CIRCLEPAY
 * @subpackage	Classes/CirclePay_Sample_Method_Class
 * @author		Payzaty
 * @since		1.0.0
 */
class CirclePay_Sample_Method_Class extends WC_Payment_Gateway {

	public function __construct(){
		$this->id = 'sample_method_id';
		$this->icon = 'sample_method_icon';
		$this->has_fields = false ;
		$this->method_title = 'sample_method_title';
		$this->title = 'sample_method_title';
	}

	/**
	 * Process the payment
	 *
	 * @access	public
	 * @since	1.0.0
	 * @param	string $order_id is the current order id
	 * @return	array data of the payment process opened for this order
	 */
	public function process_payment( $order_id ) {
		require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-circlepay-methods-order-handler.php';
		$handler = new CirclePay_Methods_Order_Handler( $order_id );
		return $handler->process_payment();
	}

}