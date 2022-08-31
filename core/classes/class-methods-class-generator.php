<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Methods_Class_Generator
 *
 * @package		CIRCLEPAY
 * @subpackage	Classes/Methods_Class_Generator
 * @author		Mohamed Yassin
 * @since		1.0.0
 */
class Methods_Class_Generator{

	/**
	 * Available CirclePay methods
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected $available_methods = [];


	public function __construct( $methods = array() )
	{
		if( empty( $methods) ){
			return;
		}

		$this->set_defaults();

		if( ! is_dir( $this->methods_dir )  ){
			$this->create_methods_dir( $this->methods_dir );
		}

		foreach ( $methods as $method ) {
			$this->maybe_generate_class_file( $method  );
		}

	}

	public function set_defaults()
	{
		$this->methods_dir 		= trailingslashit ( WP_PLUGIN_DIR . '/' . CIRCLEPAY_SLUG . '_available_methods/classes' );
		$this->sample_file 		= CIRCLEPAY_PLUGIN_DIR . 'assets/php/class-wc-method-class-sample.php';
		$this->sample_content 	= file_get_contents( $this->sample_file );
	}

	public function create_methods_dir()
	{
		$files = array(
			array(
				'base'    => $this->methods_dir . '../',
				'file'    => 'index.html',
				'content' => '',
			),
			array(
				'base'    => $this->methods_dir . '../',
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => $this->methods_dir,
				'file'    => 'index.html',
				'content' => '',
			),
			array(
				'base'    => $this->methods_dir,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( $file['base'] . $file['file'] ) ) {
				$this->create_file( $file['base'] . $file['file'] , $file['content'] );
			}
		}
	}

	private function create_file( $path , $content )
	{
		$file_handle = @fopen( $path , 'wb'  ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
		if ( $file_handle ) {
			fwrite( $file_handle, $content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
			return fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		}
	}

	public function maybe_generate_class_file( $method_data = []  )
	{
		$method_file = $this->method_file_path( $method_data );
		if( is_file( $method_file )  ){
			$this->add_method_to_the_available ( $method_data , $method_file );
			//return;
		}

		$this->generate_class_file( $method_file, $method_data );
	}

	public function add_method_to_the_available( $method_data , $method_file = false )
	{
		if( !$method_file ){
			$method_file = $this->method_file_path( $method_data );
		}

		$replace_date = $this->replace_date ( $method_data );
		$this->available_methods[ $replace_date[1] ] =  array(
			'class_name' => $replace_date[0],
			'file' 		=> $method_file,
		);

	}

	public function method_file_path( $method_data )
	{
		return $this->methods_dir . '/' . $this->method_file_name( $method_data  );
	}

	public function method_file_name( $method_data )
	{
		$method = str_replace( ' ', '-' ,  strtolower( trim( $method_data['name'] ) ) ) ;
		$gateway = isset( $method_data['gateway'] ) ? str_replace( ' ', '-' ,  strtolower( trim( $method_data['gateway'] ) ) ) : 'circlepay' ;
		return 'class-' . $method . '-' . $gateway . '.php';
	}
	
	public function generate_class_file( $method_file ,  $method_data ){
		if( $this->create_file ( $method_file , $this->method_file_content( $method_data )) ){
			$this->add_method_to_the_available ( $method_data , $method_file );
		}
	}

	public function method_file_content( $method_data )
	{
		$search = array(
			'{{Method_Name_GateWay_Name}}',
			'{{method_name_gateWay_name_id}}',
			'{{method_name_gateWay_name_title}}',
			'{{method_name_gateWay_name_icon}}',
		);

		$replace = $this->replace_date( $method_data );
		return str_replace( $search, $replace, $this->sample_content );
	}

	private function replace_date( $method_data ){
		$name 	= str_replace( ' ' , '_' , $method_data['name'] ) .'_'; 
		$name 	.= isset( $method_data['gateway'] ) ? $method_data['gateway'] : 'CirclePay';

		$id		= $method_data['id'];

		$title 	= $method_data['name'] .' ';
		$title	.= isset( $method_data['gateway'] ) ? $method_data['gateway'] : 'CirclePay';

		$icon	= isset( $method_data['icon'] ) ? $method_data['icon'] : false;
		$icon  	= $icon ?: CIRCLEPAY_PLUGIN_URL .'/assets/images/circlepay-logo.png';
		
		return array( $name, $id, $title, $icon );
	}

	public function get_available_methods()
	{
		return $this->available_methods;
	}

}
