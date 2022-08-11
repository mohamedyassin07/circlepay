<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class {{Method_Name_GateWay_Name}}
 *
 * This class will be used to generate new classes
 * for the available methods dynamically so we cover the current dozen of current methods and all the methods will be added in the future
 *
 * @package		CIRCLEPAY
 * @subpackage	Classes/{{Method_Name_GateWay_Name}}
 * @author		Payzaty
 * @since		1.6.0
 */
class {{Method_Name_GateWay_Name}} extends WC_Payment_Gateway {

	public function __construct(){
		$this->id = '{{method_name_gateWay_name_id}}';
		$this->icon = '{{method_name_gateWay_name_icon}}';
		$this->has_fields = false ;
		$this->method_title = '{{method_name_gateWay_name_id}}';
		$this->title = '{{method_name_gateWay_name_title}}';
	}

}