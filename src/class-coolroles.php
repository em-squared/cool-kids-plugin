<?php
/**
 * CoolRoles Class File
 *
 * @class CoolRoles
 * @version 1.0.0
 * @since 1.0.0
 * @package CoolKidsPlugin
 * @author Maxime Moraine
 */

namespace CoolKids;

/**
 * CoolRoles Class
 *
 * @class CoolRoles
 * @version 1.0.0
 * @since 1.0.0
 */
class CoolRoles {
	const COOL_KID    = 'cool-kid';
	const COOLER_KID  = 'cooler-kid';
	const COOLEST_KID = 'coolest-kid';

	/**
	 * Register new cool roles on plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {
		add_role( self::COOL_KID, 'Cool Kid', array( 'read' => true ) );
		add_role( self::COOLER_KID, 'Cooler Kid', array( 'read' => true ) );
		add_role( self::COOLEST_KID, 'Coolest Kid', array( 'read' => true ) );
	}

	/**
	 * Remove cool roles on plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		remove_role( self::COOL_KID );
		remove_role( self::COOLER_KID );
		remove_role( self::COOLEST_KID );
	}
}
