<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Virgool
 *
 * @wordpress-plugin
 * Plugin Name:       Virgool
 * Plugin URI:        https://github.com/nekofar/virgool
 * Description:       Virgool lets you publish posts automatically to a Virgool profile.
 * Version:           1.0.0
 * Author:            Milad Nekofar
 * Author URI:        https://milad.nekofar.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       virgool
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'VIRGOOL_VERSION', '1.0.0' );

/**
 * Absolute path for the plugin file.
 */
define( 'VIRGOOL_PLUGIN_FILE', __FILE__ );

/**
 * Absolute path for the plugin directory.
 */
define( 'VIRGOOL_PLUGIN_DIR', dirname( __FILE__ ) . '/' );

/**
 * The plugin URL root.
 */
define( 'VIRGOOL_PLUGIN_URL', plugins_url( '/', __FILE__ ) );

/**
 * The core plugin class that 5is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once VIRGOOL_PLUGIN_DIR . 'includes/class-virgool.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function virgool_run() {
	$plugin = new Virgool();
	$plugin->run();
}

virgool_run();
