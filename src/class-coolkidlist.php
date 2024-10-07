<?php
/**
 * Cool Kid List Class File
 *
 * @class CoolKidList
 * @version 1.0.0
 * @since 1.0.0
 * @package CoolKidsPlugin
 * @author Maxime Moraine
 */

namespace CoolKids;

use CoolKids\CoolRoles;

/**
 * Cool Kid List Class
 *
 * @class Cool Kid List
 * @version 1.0.0
 * @since 1.0.0
 */
class CoolKidList {

	/**
	 * Shortcode registration
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_shortcode(): void {
		add_shortcode( 'cool_kids_list', array( $this, 'display_cool_kid_list' ) );
	}

	/**
	 * Return either the current logged in user data or a login form
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function display_cool_kid_list(): string {
		if ( ! is_user_logged_in() ) {
			return '<p>You must be logged in.</p>';
		}

		$current_user = wp_get_current_user();

		// If the user is not a Cooler Kid or a Coolest Kid, don't show the list.
		// Yes, even if they are an administrator. Administrators are no cool.
		if ( CoolRoles::COOLER_KID !== $current_user->roles[0] && CoolRoles::COOLEST_KID !== $current_user->roles[0] ) {
			return '<p>You must be at least a Cooler Kid to see this.</p>';
		}

		$output = $this->user_list( $current_user );

		return $output;
	}

	/**
	 * The HTML user list data
	 *
	 * @since 1.0.0
	 * @param mixed $current_user
	 * @return string
	 */
	private function user_list( $current_user ): string {
		// Get only cool users.
		$cool_users = get_users( array( 'role__in' => array( CoolRoles::COOL_KID, CoolRoles::COOLER_KID, CoolRoles::COOLEST_KID ) ) );

		$output  = '<h2>All Cool Kids</h2>';
		$output .= '
            <table style="width:100%;">
                <thead style="text-align:left;">
                    <tr>
                        <th>First name</th>
                        <th>Last name</th>
                        <th>Country</th>
        ';

		if ( in_array( CoolRoles::COOLEST_KID, $current_user->roles ) ) {
			$output .= '
                        <th>Email</th>
                        <th>Role</th>
            ';
		}

		$output .= '
                    </tr>
                </thead>
                <tbody>
        ';

		foreach ( $cool_users as $cool_user ) {
			$output .= "
                    <tr>
                        <td>{$cool_user->first_name}</td>
                        <td>{$cool_user->last_name}</td>
                        <td>" . get_user_meta( $cool_user->ID, 'country', true ) . '</td>';

			if ( in_array( CoolRoles::COOLEST_KID, $current_user->roles ) ) {
				$output .= "
                        <td>{$cool_user->user_email}</td>
                        <td>{$cool_user->roles[0]}</td>
                ";
			}
		}

		$output .= '
                </tbody>
            </table>
        ';

		return $output;
	}
}
