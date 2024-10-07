<?php
/**
 * Class CoolKidsPluginTest
 *
 * @package CoolKidsPlugin
 */

/**
 * CoolKidsPlugin Test Case.
 */
class CoolKidsPluginTest extends WP_UnitTestCase {

	private $plugin_slug = 'cool-kids-plugin/cool-kids-plugin.php';

	/**
	 * Check that the plugin properly activates
	 * @return void
	 */
	public function test_plugin_activation(): void {
		// Ensure the plugin is deactivated before the test.
		deactivate_plugins( $this->plugin_slug );
		$this->assertFalse( is_plugin_active( $this->plugin_slug ) );

		// Activate the plugin.
		activate_plugin( $this->plugin_slug );

		// Check if the plugin is activated.
		$this->assertTrue( is_plugin_active( $this->plugin_slug ) );
	}

	/**
	 * Check that the plugin activation throws no errors.
	 * @return void
	 */
	public function test_plugin_activation_status(): void {
		// Deactivate the plugin first.
		deactivate_plugins( $this->plugin_slug );

		// If the activation throws errors, the activation returns a WP_Error object.
		$activation_result = activate_plugin( $this->plugin_slug );

		// If null, there is no errors.
		$this->assertNull( $activation_result );
	}
}
