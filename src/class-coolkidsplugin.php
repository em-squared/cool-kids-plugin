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
	 * Constructor function.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct() {
		$this->registration = new Registration();
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
	}
}
