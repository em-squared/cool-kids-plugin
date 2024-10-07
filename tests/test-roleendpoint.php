<?php

use CoolKids\API\RoleEndpoint;
use CoolKids\CoolRoles;

class RoleEndpointTest extends WP_UnitTestCase {
	private $server;
	private $endpoint;

	protected function setUp(): void {
		parent::setUp();

		// Register roles.
		CoolRoles::activate();

		global $wp_rest_server;

		// Init WP REST Server to handle requests.
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		$this->endpoint = new RoleEndpoint();
		$this->endpoint->register_routes();
	}

	public function testUpdateRole(): void {
		// Create a test user.
		$user_id = $this->factory->user->create( [ 
			'role' => CoolRoles::COOL_KID,
			'user_email' => 'test@example.com',
			'first_name' => 'Test',
			'last_name' => 'User',
		] );

		// Create an admin user.
		$admin_id = $this->factory->user->create( [ 
			'role' => 'administrator',
		] );
		wp_set_current_user( $admin_id );

		// Test updating role by email.
		$request = new WP_REST_Request( 'POST', '/cool-kids/v1/update-role' );
		$request->set_param( 'email', 'test@example.com' );
		$request->set_param( 'role', CoolRoles::COOLER_KID );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );

		$user = get_user_by( 'ID', $user_id );
		$this->assertTrue( in_array( CoolRoles::COOLER_KID, $user->roles ) );

		// Test updating role by first and last name.
		$request = new WP_REST_Request( 'POST', '/cool-kids/v1/update-role' );
		$request->set_param( 'first_name', 'Test' );
		$request->set_param( 'last_name', 'User' );
		$request->set_param( 'role', CoolRoles::COOLEST_KID );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 200, $response->get_status() );

		$user = get_user_by( 'ID', $user_id );
		$this->assertTrue( in_array( CoolRoles::COOLEST_KID, $user->roles ) );

		// Test invalid role.
		$request = new WP_REST_Request( 'POST', '/cool-kids/v1/update-role' );
		$request->set_param( 'email', 'test@example.com' );
		$request->set_param( 'role', 'invalid_role' );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 400, $response->get_status() );

		// Test non-existent user.
		$request = new WP_REST_Request( 'POST', '/cool-kids/v1/update-role' );
		$request->set_param( 'email', 'nonexistent@example.com' );
		$request->set_param( 'role', CoolRoles::COOL_KID );

		$response = $this->server->dispatch( $request );
		$this->assertEquals( 404, $response->get_status() );
	}
}