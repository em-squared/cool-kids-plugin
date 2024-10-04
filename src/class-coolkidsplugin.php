<?php
/**
 * Main CoolKidsPlugin Class File
 *
 * @class CoolKidsPlugin
 * @version 1.0.0
 * @since 1.0.0
 * @package CoolKidsPlugin
 * @author Maxime Moraine
 */

namespace CoolKids;

/**
 * Main CoolKidsPlugin Class
 *
 * @class CoolKidsPlugin
 * @version 1.0.0
 * @since 1.0.0
 */
class CoolKidsPlugin {
	/**
	 * Registration variable
	 *
	 * @since 1.0.0
	 * @var Registration.
	 */
	private $registration;

	/**
	 * Current logged in Cool Kid Data
	 *
	 * @var CoolKidData
	 */
	private $cool_kid_data;

	/**
	 * Constructor function.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct() {
		$this->registration  = new Registration();
		$this->cool_kid_data = new CoolKidData();
	}

	/**
	 * Run function.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function run(): void {
		add_action( 'init', array( $this->registration, 'register_shortcode' ) );
		add_action( 'init', array( $this->cool_kid_data, 'register_shortcode' ) );
	}
}
