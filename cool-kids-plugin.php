<?php
/**
 * Plugin Name: Cool Kids Plugin
 * Description: A WordPress plugin for Cool Kids Network. A network where kids are really cool!
 * Version: 1.0.0
 * Author: Maxime Moraine
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use CoolKids\CoolKidsPlugin;

function run_cool_kids_plugin()
{
    $plugin = new CoolKidsPlugin();
    $plugin->run();
}

run_cool_kids_plugin();