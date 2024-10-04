<?php
use CoolKids\CoolKidList;
use CoolKids\CoolRoles;

class CoolKidListTest extends WP_UnitTestCase {
	private $cool_kid_list;

	protected function setUp(): void {
		parent::setUp();
		$this->cool_kid_list = new CoolKidList();
		add_role( CoolRoles::COOL_KID, 'Cool Kid' );
		add_role( CoolRoles::COOLER_KID, 'Cooler Kid' );
		add_role( CoolRoles::COOLEST_KID, 'Coolest Kid' );

		// Create somme cool kid to make a list
		$user_id = $this->factory->user->create( [ 
			'role' => CoolRoles::COOL_KID,
			'first_name' => 'Bob',
			'last_name' => 'Kid',
			'user_email' => 'cool@example.com',
		] );
		update_user_meta( $user_id, 'country', 'USA' );
	}

	public function testRegisterShortcode(): void {
		$this->cool_kid_list->register_shortcode();
		$this->assertTrue( shortcode_exists( 'cool_kids_list' ) );
	}

	public function testDisplayCoolKidList(): void {
		// Test not logged in
		$this->assertStringContainsString( 'You must be logged in.', $this->cool_kid_list->display_cool_kid_list() );

		// Test logged in as Cool Kid
		$user_id = $this->factory->user->create( [ 
			'role' => CoolRoles::COOL_KID,
			'first_name' => 'Alice',
			'last_name' => 'Tester',
			'user_email' => 'cool-tester@example.com',
		] );
		update_user_meta( $user_id, 'country', 'USA' );
		$user = wp_set_current_user( $user_id );

		$output = $this->cool_kid_list->display_cool_kid_list();
		$this->assertStringContainsString( 'You must be at least a Cooler Kid to see this.', $output );

		// Test logged in as a Cooler Kid
		$user->set_role( CoolRoles::COOLER_KID );

		$output = $this->cool_kid_list->display_cool_kid_list();
		$this->assertStringContainsString( 'Bob', $output );
		$this->assertStringContainsString( 'Alice', $output );
		$this->assertStringNotContainsString( 'cool@example.com', $output );
		$this->assertStringNotContainsString( 'cool-tester@example.com', $output );
		$this->assertStringNotContainsString( 'cool-kid', $output );
		$this->assertStringNotContainsString( 'cooler-kid', $output );

		// Test logged in as a Coolest Kid
		$user->set_role( CoolRoles::COOLEST_KID );

		$output = $this->cool_kid_list->display_cool_kid_list();
		$this->assertStringContainsString( 'Bob', $output );
		$this->assertStringContainsString( 'Alice', $output );
		$this->assertStringContainsString( 'cool@example.com', $output );
		$this->assertStringContainsString( 'cool-tester@example.com', $output );
		$this->assertStringContainsString( 'cool-kid', $output );
		$this->assertStringContainsString( 'coolest-kid', $output );
	}
}