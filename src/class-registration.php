<?php
/**
 * Registration Class File
 *
 * @class Registration
 * @version 1.0.0
 * @since 1.0.0
 * @package CoolKidsPlugin
 * @author Maxime Moraine
 */

namespace CoolKids;

/**
 * Registration Class
 *
 * @class Registration
 * @version 1.0.0
 * @since 1.0.0
 */
class Registration {
	/**
	 * NONCE_ACTION Const for form security
	 *
	 * @var string
	 * @since 1.0.0
	 */
	const NONCE_ACTION = 'cool_kids_registration_nonce';

	/**
	 * NONCE_FIELD Const for form security
	 *
	 * @var string
	 * @since 1.0.0
	 */
	const NONCE_FIELD = 'cool_kids_nonce';

	/**
	 * Shortcode registration
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_shortcode(): void {
		add_shortcode( 'cool_kids_registration_form', array( $this, 'registration_form' ) );
	}

	/**
	 * Registration form html body
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function registration_form(): string {
		if ( is_user_logged_in() ) {
			return 'You are already a cool kid!';
		}

		$output = '
        <form id="cool-kids-registration" method="post">
            <input type="email" name="email" required>
            ' . wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD, true, false ) . '
            <input type="submit" value="Register">
        </form>';

		// Check if form was submitted.
		$email = $this->is_valid_form_submission();
		if ( $email ) {
			$output .= $this->process_registration( $email );
		}

		return $output;
	}

	/**
	 * Check if the submitted form is valid
	 *
	 * @since 1.0.0
	 * @return mixed (string of bool)
	 */
	private function is_valid_form_submission(): mixed {
		if ( isset( $_SERVER['REQUEST_METHOD'] )
			&& 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$post_data = wp_unslash( $_POST );
			if ( isset( $post_data['email'] )
				&& isset( $post_data[ self::NONCE_FIELD ] )
				&& wp_verify_nonce( $post_data[ self::NONCE_FIELD ], self::NONCE_ACTION ) ) {
				return sanitize_email( $post_data['email'] );
			}
		}
		return false;
	}

	/**
	 * Registration handling after form submit
	 *
	 * @param mixed $email
	 * @since 1.0.0
	 * @return string
	 */
	private function process_registration( $email ): string {
		if ( ! is_email( $email ) ) {
			return '<p>Invalid email address.</p>';
		}

		if ( email_exists( $email ) ) {
			return '<p>Email address already registered.</p>';
		}

		try {
			$user_data = $this->get_random_user_data();
		} catch ( \Exception $e ) {
			error_log( 'Cool Kids Plugin - Random User API Error: ' . $e->getMessage() );
			// Fallback to default user data.
			$user_data = $this->get_fallback_user_data();
		}

		$user_id = wp_create_user( $email, 'password', $email );

		if ( is_wp_error( $user_id ) ) {
			error_log( 'Cool Kids Plugin - User Creation Error: ' . $user_id->get_error_message() );
			return '<p>Error creating user. Please try again later.</p>';
		}

		$user = new \WP_User( $user_id );
		$user->set_role( CoolRoles::COOL_KID );

		update_user_meta( $user_id, 'first_name', $user_data['first_name'] );
		update_user_meta( $user_id, 'last_name', $user_data['last_name'] );
		update_user_meta( $user_id, 'country', $user_data['country'] );

		return '<p>Cool! You are now registered! You can now log in!</p>';
	}

	/**
	 * Cool Character generation from randomuser API
	 *
	 * @throws \Exception
	 * @since 1.0.0
	 * @return array
	 */
	private function get_random_user_data(): array {
		$response = wp_remote_get(
			'https://randomuser.me/api/',
			array(
				'timeout' => 5,  // 5 seconds timeout
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new \Exception( 'Failed to contact Random User API: ' . esc_html( $response->get_error_message() ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			throw new \Exception( 'Random User API returned unexpected status code: ' . esc_html( $response_code ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \Exception( 'Failed to parse Random User API response: ' . esc_html( json_last_error_msg() ) );
		}

		if ( ! isset( $data['results'][0]['name']['first'] ) ||
			! isset( $data['results'][0]['name']['last'] ) ||
			! isset( $data['results'][0]['location']['country'] ) ) {
			throw new \Exception( 'Random User API returned unexpected data structure' );
		}

		return array(
			'first_name' => $data['results'][0]['name']['first'],
			'last_name'  => $data['results'][0]['name']['last'],
			'country'    => $data['results'][0]['location']['country'],
		);
	}

	/**
	 * Cool Character generation fallback in case of randomuser failing
	 *
	 * @since 1.0.0
	 * @return string[]
	 */
	private function get_fallback_user_data(): array {
		$fallback_first_names = array( 'Matt', 'Jessica', 'Wade', 'Bruce', 'Peter' );
		$fallback_last_names  = array( 'Murdock', 'Jones', 'Wilson', 'Banner', 'Parker' );
		$fallback_countries   = array( 'United States', 'Canada', 'United Kingdom', 'France', 'Asgard' );

		return array(
			'first_name' => $fallback_first_names[ array_rand( $fallback_first_names ) ],
			'last_name'  => $fallback_last_names[ array_rand( $fallback_last_names ) ],
			'country'    => $fallback_countries[ array_rand( $fallback_countries ) ],
		);
	}
}
