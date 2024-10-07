<?php
/**
 * Role Endpoint Class File
 *
 * @class API\RoleEndpoint
 * @version 1.0.0
 * @since 1.0.0
 * @package CoolKidsPlugin
 * @author Maxime Moraine
 */

namespace CoolKids\API;

use CoolKids\CoolRoles;

/**
 * Role Endpoint Class
 *
 * @class API\RoleEndpoint
 * @version 1.0.0
 * @since 1.0.0
 */
class RoleEndpoint {
	/**
	 * Add a new API route endpoint to update a user role.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			'cool-kids/v1',
			'/update-role',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_role' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Check the capability of the request user to update roles.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Updates the role of a given user.
	 *
	 * @since 1.0.0
	 * @param mixed $request
	 * @return mixed
	 */
	public function update_role( $request ): mixed {
		$params = $request->get_params();

		// Check that the request has the rights params : a role that is either a cool-kid, a cooler-kid or a coolest-kid.
		if ( ! isset( $params['role'] ) || ! in_array( $params['role'], CoolRoles::get_valid_roles() ) ) {
			return new \WP_Error( 'invalid_role', 'Specified role is no cool.', array( 'status' => 400 ) );
		}

		// Get the user to update role by either the email of firstname+lastname.
		$user = $this->get_user( $params );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$user->set_role( $params['role'] );

		return array(
			'success' => true,
			'message' => 'User role updated successfully.',
		);
	}

	/**
	 * Get a user given an email of firstname+lastname.
	 *
	 * @since 1.0.0
	 * @param mixed $params
	 * @return mixed
	 */
	private function get_user( $params ): mixed {
		// Get user by email.
		if ( isset( $params['email'] ) ) {
			$user = get_user_by( 'email', $params['email'] );
		} elseif ( isset( $params['first_name'] ) && isset( $params['last_name'] ) ) { // Get user by firstname+lastname.
			$users = get_users(
				array(
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'   => 'first_name',
							'value' => $params['first_name'],
						),
						array(
							'key'   => 'last_name',
							'value' => $params['last_name'],
						),
					),
				)
			);

			// Check that there's only 1 match.
			if ( count( $users ) === 1 ) {
				$user = $users[0];
			} else {
				return new \WP_Error( 'user_not_found', 'User not found or multiple users match the criteria.', array( 'status' => 404 ) );
			}
		} else {
			return new \WP_Error( 'invalid_params', 'Invalid parameters. Provide either email or first and last name.', array( 'status' => 400 ) );
		}

		if ( ! $user ) {
			return new \WP_Error( 'user_not_found', 'User not found.', array( 'status' => 404 ) );
		}

		return $user;
	}
}
