<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Circlepay' ) ) :

	/**
	 * Main Circlepay Class.
	 *
	 * @package		CIRCLEPAY
	 * @subpackage	Classes/Circlepay
	 * @since		1.0.0
	 * @author		Mohamed Yassin
	 */
	final class Circlepay {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Circlepay
		 */
		private static $instance;

		/**
		 * CIRCLEPAY helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Circlepay_Helpers
		 */
		public $helpers;

		/**
		 * CIRCLEPAY settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Circlepay_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	Void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'circlepay' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	Void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'circlepay' ), '1.0.0' );
		}

		/**
		 * Main Circlepay Instance.
		 *
		 * Insures that only one instance of Circlepay exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Circlepay	The one true Circlepay
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Circlepay ) ) {
				self::$instance					= new Circlepay;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Circlepay_Helpers();
				self::$instance->settings		= new Circlepay_Settings();

				//Fire the plugin logic
				new Circlepay_Run;

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'CIRCLEPAY/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  Void
		 */
		private function includes() {
			require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-helpers.php';
			require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-settings.php';
			require_once CIRCLEPAY_PLUGIN_DIR . 'core/classes/class-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  Void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  Void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'circlepay', FALSE, dirname( plugin_basename( CIRCLEPAY_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.