<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Token_Cryptor
 *
 * @package		CIRCLEPAY
 * @subpackage	Classes/Token_Cryptor
 * @author		Mohamed Yassin
 * @since		1.0.0
 */
class Token_Cryptor{

	/**
	 * Cription string lenght
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected static $str_len;

	/**
	 * Cription initialization vector
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected static $iv;

	/**
	 * Cription key
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected static $key;

	/**
	 * Cription ciphering
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected static $ciphering;

	/**
	 * Cription options
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected static $options;

	
	/**
	 * Set the cription defaults
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	protected static function set_cription_basics()
	{
		self::$ciphering	= "AES-128-CTR";
		self::$str_len		= openssl_cipher_iv_length( self::$ciphering );
		self::$options		= 0;

		self::set_iv();
		self::set_key();
	}

	protected static function set_iv()
	{
		$iv = get_option( 'circlepay_cription_iv' );

		if( ! $iv || strlen( $iv ) !== self::$str_len ){
			$iv = wp_generate_password( self::$str_len, true, true );
			update_option( 'circlepay_cription_iv', $iv, false );
		}

		self::$iv = $iv;
	}

	protected static function set_key()
	{
		$key = get_option( 'circlepay_cription_key' );

		if( ! $key || strlen( $key ) !== self::$str_len ){
			$key = wp_generate_password( self::$str_len, true, true );
			update_option( 'circlepay_cription_key', $key, false );
		}

		self::$key = $key;
	}

	public static function encrypt( $string )
	{
		self::set_cription_basics();
		return openssl_encrypt(
			$string,
			self::$ciphering,
			self::$key,
			self::$options,
			self::$iv
		);
	}

	public static function decrypt( $encryption )
	{
		self::set_cription_basics();
		return openssl_decrypt(
			$encryption,
			self::$ciphering,
			self::$key,
			self::$options,
			self::$iv
		);
	}
}