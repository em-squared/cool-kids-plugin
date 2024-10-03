<?php
/**
 * Class CoolRolesTest
 *
 * @package CoolKidsPlugin
 */

use CoolKids\CoolRoles;

/**
 * CoolRoles Test Case.
 */
class CoolRolesTest extends WP_UnitTestCase {
	/**
	 * Checks that cool roles don't exist before activation,
	 * then checks that they exist after activation.
	 * @return void
	 */
	public function test_activation(): void {
		remove_role( CoolRoles::COOL_KID );
		remove_role( CoolRoles::COOLER_KID );
		remove_role( CoolRoles::COOLEST_KID );

		CoolRoles::activate();

		$this->assertTrue( wp_roles()->is_role( CoolRoles::COOL_KID ) );
		$this->assertTrue( wp_roles()->is_role( CoolRoles::COOLER_KID ) );
		$this->assertTrue( wp_roles()->is_role( CoolRoles::COOLEST_KID ) );
	}

	/**
	 * Activate the plugin to add new cool roles,
	 * then deactivate to check that the cool roles are removed.
	 * @return void
	 */
	public function test_deactivation(): void {
		CoolRoles::activate();

		CoolRoles::deactivate();

		$this->assertFalse( wp_roles()->is_role( CoolRoles::COOL_KID ) );
		$this->assertFalse( wp_roles()->is_role( CoolRoles::COOLER_KID ) );
		$this->assertFalse( wp_roles()->is_role( CoolRoles::COOLEST_KID ) );
	}
}
