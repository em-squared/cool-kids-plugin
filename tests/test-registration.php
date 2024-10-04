<?php
/**
 * Class RegistrationTest
 *
 * @package CoolKidsPlugin
 */

use CoolKids\Registration;

/**
 * Registration Test Case.
 */
class RegistrationTest extends WP_UnitTestCase {
	/**
	 * Class to be reflected for tests
	 * @var Registration
	 */
	private $registration;

	/**
	 * Setup for tests
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->registration = new Registration();
	}

	/**
	 * Check that the shortcode is registered.
	 * @return void
	 */
	public function testRegisterShortcode(): void {
		$this->registration->register_shortcode();
		$this->assertTrue( shortcode_exists( 'cool_kids_registration_form' ) );
	}

	/**
	 * Check that the HTML body of the form contains nonce field.
	 * @return void
	 */
	public function testRegistrationFormIncludesNonce(): void {
		$output = $this->registration->registration_form();
		$this->assertStringContainsString( 'name="' . Registration::NONCE_FIELD . '"', $output );
	}

	/**
	 * Check the truth of a valid form submission.
	 * @return void
	 */
	public function testValidFormSubmission(): void {
		// Set up valid POST data
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['email'] = 'cool-email@kid.cool';
		$_POST[ Registration::NONCE_FIELD ] = wp_create_nonce( Registration::NONCE_ACTION );

		$reflection = new ReflectionClass( $this->registration );
		$method = $reflection->getMethod( 'is_valid_form_submission' );
		$method->setAccessible( true );

		$this->assertSame( $_POST['email'], $method->invoke( $this->registration ) );

		// Clean up
		unset( $_SERVER['REQUEST_METHOD'] );
		unset( $_POST['email'] );
		unset( $_POST[ Registration::NONCE_FIELD ] );
	}

	/**
	 * Check the different failure scenarios of a form submission.
	 * @return void
	 */
	public function testInvalidFormSubmission() {
		$reflection = new ReflectionClass( $this->registration );
		$method = $reflection->getMethod( 'is_valid_form_submission' );
		$method->setAccessible( true );

		// Test missing REQUEST_METHOD
		$this->assertFalse( $method->invoke( $this->registration ) );

		// Test wrong REQUEST_METHOD
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$this->assertFalse( $method->invoke( $this->registration ) );

		// Test missing email
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->assertFalse( $method->invoke( $this->registration ) );

		// Test missing nonce
		$_POST['email'] = 'test@example.com';
		$this->assertFalse( $method->invoke( $this->registration ) );

		// Test invalid nonce
		$_POST[ Registration::NONCE_FIELD ] = 'invalid_nonce';
		$this->assertFalse( $method->invoke( $this->registration ) );

		// Clean up
		unset( $_SERVER['REQUEST_METHOD'] );
		unset( $_POST['email'] );
		unset( $_POST[ Registration::NONCE_FIELD ] );
	}

	/**
	 * Check that the random user generation returns a correct set of data
	 * @return void
	 */
	public function testGetRandomUserDataSuccess(): void {
		$reflection = new ReflectionClass( $this->registration );
		$method = $reflection->getMethod( 'get_random_user_data' );
		$method->setAccessible( true );

		// We don't really call the randomuser.me API, we simulate its response
		// We don't want the test to fail because the API is unreachable for example
		add_filter( 'pre_http_request', function () {
			return [ 
				'response' => [ 'code' => 200 ],
				'body' => json_encode( [ 
					'results' => [ 
						[ 
							'name' => [ 
								'first' => 'John',
								'last' => 'Doe'
							],
							'location' => [ 
								'country' => 'United States'
							]
						]
					]
				] )
			];
		} );

		$result = $method->invoke( $this->registration );
		$this->assertEquals( 'John', $result['first_name'] );
		$this->assertEquals( 'Doe', $result['last_name'] );
		$this->assertEquals( 'United States', $result['country'] );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Check that the random user generation throws correct errors in case of randomuser.me API failure.
	 * @return void
	 */
	public function testGetRandomUserDataApiFailure(): void {
		$reflection = new ReflectionClass( $this->registration );
		$get_random_data_method = $reflection->getMethod( 'get_random_user_data' );
		$get_random_data_method->setAccessible( true );

		$get_fallback_data_method = $reflection->getMethod( 'get_fallback_user_data' );
		$get_fallback_data_method->setAccessible( true );

		// We don't call the actual API, it could not fail, duh!
		add_filter( 'pre_http_request', function () {
			return new WP_Error( 'http_request_failed', 'API request failed' );
		} );

		try {
			$get_random_data_method->invoke( $this->registration );
			$this->fail( 'Expected exception was not thrown' );
		} catch (\Exception $e) {
			$this->assertStringContainsString( 'Failed to contact Random User API', $e->getMessage() );
		}

		// When the API fails, there's a fallback data generation, way much cooler
		$fallback_data = $get_fallback_data_method->invoke( $this->registration );
		$this->assertArrayHasKey( 'first_name', $fallback_data );
		$this->assertArrayHasKey( 'last_name', $fallback_data );
		$this->assertArrayHasKey( 'country', $fallback_data );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Check that a cool user is properly created
	 * @return void
	 */
	public function testProcessRegistration(): void {
		$reflection = new ReflectionClass( $this->registration );
		$method = $reflection->getMethod( 'process_registration' );
		$method->setAccessible( true );

		// We don't create a real user, just simulate the wp_create_user return value
		add_filter( 'wp_create_user', function () {
			return 1; // Return a valid user ID
		} );

		$result = $method->invokeArgs( $this->registration, [ 'cool-email@kid.cool' ] );
		$this->assertEquals( '<p>Cool! You are now registered! You can now log in!</p>', $result );

		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'wp_create_user' );
	}
}