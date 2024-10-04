<?php
/**
 * Cool Kid Data Class File
 *
 * @class CoolKidData
 * @version 1.0.0
 * @since 1.0.0
 * @package CoolKidsPlugin
 * @author Maxime Moraine
 */

namespace CoolKids;

/**
 * Cool Kid Data Class
 *
 * @class Cool Kid Data
 * @version 1.0.0
 * @since 1.0.0
 */
class CoolKidData {
	/**
	 * NONCE_ACTION Const for form security
	 *
	 * @var string
	 * @since 1.0.0
	 */
	const NONCE_ACTION = 'cool_kids_login_nonce';

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
		add_shortcode( 'cool_kids_user_data', array( $this, 'display_cool_kid_data' ) );
	}

	/**
	 * Return either the current logged in user data or a login form
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function display_cool_kid_data(): string {
		if ( ! is_user_logged_in() ) {
			return $this->login_form();
		}

		$current_user = wp_get_current_user();
		$output       = $this->user_data( $current_user );

		return $output;
	}

	/**
	 * The HTML login form
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function login_form(): string {
		$output = '';

		$output .= '
            <h2>Please log in to view user data</h2>
            <form method="POST">
                <p>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </p>
                <p>
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </p>
                ' . wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD, true, false ) . '
                <p>
                    <input type="submit" value="Log In">
                </p>
            </form>
        ';

		// Check if form was submitted.
		$credentials = $this->is_valid_form_submission();
		if ( $credentials && $credentials['email'] && $credentials['password'] ) {
			$output .= $this->process_login( $credentials );
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
				&& isset( $_POST['password'] )
				&& isset( $post_data[ self::NONCE_FIELD ] )
				&& wp_verify_nonce( $post_data[ self::NONCE_FIELD ], self::NONCE_ACTION ) ) {
				return array(
					'email'    => sanitize_text_field( $post_data['email'] ),
					'password' => $_POST['password'],
				);
			}
		}
		return false;
	}

	/**
	 * Login handling after form submit
	 *
	 * @param mixed $credentials
	 * @since 1.0.0
	 * @return string
	 */
	private function process_login( $credentials ): mixed {
		$login_creds = array();
		if ( is_email( $credentials['email'] ) ) {
			// Get the user by email.
			$user_by_email = get_user_by( 'email', $credentials['email'] );
			if ( $user_by_email ) {
				$login_creds['user_login']    = $user_by_email->user_login;
				$login_creds['user_password'] = $credentials['password'];
				$login_creds['remember']      = true;
			} else {
				return '<p>Login failed.</p>';
			}
		} else {
			return '<p>Invalid email.</p>';
		}

		// Attempt to log the user in.
		$user = wp_signon( $login_creds, false );

		// Check if login was successful.
		if ( is_wp_error( $user ) ) {
			return '<p>Login failed.</p>';
		}

		// Reload the page.
		wp_safe_redirect( get_permalink() );
		exit;
	}

	/**
	 * The HTML current logged in user data
	 *
	 * @since 1.0.0
	 * @param mixed $user
	 * @return string
	 */
	private function user_data( $user ): string {
		return "
            <h2>Your Cool Data</h2>
            <p>Email: {$user->user_email}</p>
            <p>First Name: {$user->first_name}</p>
            <p>Last Name: {$user->last_name}</p>
            <p>Country: " . get_user_meta( $user->ID, 'country', true ) . "</p>
            <p>Role: {$user->roles[0]}</p>
        ";
	}
}
