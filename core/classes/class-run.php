<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Circlepay_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		CIRCLEPAY
 * @subpackage	Classes/Circlepay_Run
 * @author		Mohamed Yassin
 * @since		1.0.0
 */
class Circlepay_Run{

	/**
	 * Our Circlepay_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct()
	{
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	Void
	 */
	private function add_hooks()
	{
		add_action( 'plugin_action_links_' . CIRCLEPAY_PLUGIN_BASE, array( $this, 'add_plugin_action_link' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts_and_styles' ), 20 );
		add_action( 'woocommerce_after_register_post_type', array( $this, 'includes' ) );
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	* Adds action links to the plugin list table
	*
	* @access	public
	* @since	1.0.0
	*
	* @param	array	$links An array of plugin action links.
	*
	* @return	array	An array of plugin action links.
	*/
	public function add_plugin_action_link( $links )
	{
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=circlepay' ) . '" aria-label="' . esc_attr__( 'View CirclePay settings', 'circlepay' ) . '">' . esc_html__( 'Settings', 'circlepay' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Enqueue the backend related scripts and styles for this plugin.
	 * All of the added scripts andstyles will be available on every page within the backend.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	Void
	 */
	public function enqueue_backend_scripts_and_styles()
	{
		wp_enqueue_style( 'circlepay-backend-styles', CIRCLEPAY_PLUGIN_URL . 'core/includes/assets/css/backend-styles.css', array(), CIRCLEPAY_VERSION, 'all' );
		wp_enqueue_script( 'circlepay-backend-scripts', CIRCLEPAY_PLUGIN_URL . 'core/includes/assets/js/backend-scripts.js', array(), CIRCLEPAY_VERSION, false );
		wp_localize_script( 'circlepay-backend-scripts', 'circlepay', array(
			'plugin_name'   	=> __( CIRCLEPAY_NAME, 'circlepay' ),
		));
	}

	/**
	 * Include required classes
	 *
	 * @access	public
	 * @since	1.0.0
	 */
	public function includes()
	{
		require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-available-methods.php';
		new CirclePay_Available_Methods;

		if( isset( $_GET['order_token'] ) && ! empty( $_GET['order_token'] ) ){
			require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-circlepay-methods-order-handler.php';
			new CirclePay_Methods_Order_Handler;
		}
	}

}