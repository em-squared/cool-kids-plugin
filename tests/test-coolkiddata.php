<?php
use CoolKids\CoolKidData;
use CoolKids\CoolRoles;

class CoolKidDataTest extends WP_UnitTestCase {
	private $cool_kid_data;

	protected function setUp(): void {
		parent::setUp();
		$this->cool_kid_data = new CoolKidData();
		add_role( CoolRoles::COOL_KID, 'Cool Kid' );
	}

	public function testRegisterShortcode(): void {
		$this->cool_kid_data->register_shortcode();
		$this->assertTrue( shortcode_exists( 'cool_kids_user_data' ) );
	}

	public function testDisplayCoolKidData(): void {
		// Test not logged in
		$this->assertStringContainsString( 'Please log in to view user data', $this->cool_kid_data->display_cool_kid_data() );

		// Test logged in as Cool Kid
		$user_id = $this->factory->user->create( [ 
			'role' => CoolRoles::COOL_KID,
			'first_name' => 'John',
			'last_name' => 'Doe',
			'user_email' => 'john@example.com',
		] );
		update_user_meta( $user_id, 'country', 'USA' );
		wp_set_current_user( $user_id );

		$output = $this->cool_kid_data->display_cool_kid_data();
		$this->assertStringContainsString( 'Your Cool Data', $output );
		$this->assertStringContainsString( 'John', $output );
		$this->assertStringContainsString( 'Doe', $output );
		$this->assertStringContainsString( 'USA', $output );
		$this->assertStringContainsString( 'cool-kid', $output );
	}
}