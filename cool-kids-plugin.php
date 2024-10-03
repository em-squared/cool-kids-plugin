<?php
/**
 * Plugin Name: Cool Kids Plugin
 * Description: A WordPress plugin for Cool Kids Network. A network where kids are really cool!
 * Version: 1.0.0
 * Author: Maxime Moraine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use CoolKids\CoolKidsPlugin;
use CoolKids\CoolRoles;

// Register activation hook that will add the new cool roles.
register_activation_hook( __FILE__, [ CoolRoles::class, 'activate' ] );

// Register deactivation hook that will remove the cool roles.
register_deactivation_hook( __FILE__, [ CoolRoles::class, 'deactivate' ] );

function run_cool_kids_plugin(): void {
	$plugin = new CoolKidsPlugin();
	$plugin->run();
}

run_cool_kids_plugin();