<?php

/**
 * Plugin Name:         wp-datanova-cities
 * Plugin URI:          https://github.com/globalis-ms/wp-datanova-cities
 * Description:         Datanova laposte_hexasmal wrapper for WordPress
 * Author:              Pierre Dargham, Globalis Media Systems
 * Author URI:          https://www.globalis-ms.com/
 * License:             GPL2
 *
 * Version:             0.1.0
 * Requires at least:   4.0.0
 * Tested up to:        4.9.10
 */

namespace Globalis\WP\Datanova;

require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Cities.php';

register_activation_hook(__FILE__, function () {
    if (!Database::instance()->tableExists()) {
        Database::instance()->update();
    }
});

Database::instance()->hooks();
Cities::instance()->hooks();
