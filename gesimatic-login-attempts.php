<?php
/**
 * Plugin Name
 * 
 * @package           Gesimatic-Login-Attempts
 * @author            Carmelo Andrés
 * @copyright         2026 Carmelo Andrés
 * @license           GPL-2.0-or-later
 * 
 * @wordpress-plugin
 * Plugin Name: Gesimatic-Login-Attempts
 * Plugin URI:  https://gesimatic.com/wordpress/plugin/gesimatic-login-attempts
 * Description: Gesimatic module/plugin to protect login access by limiting attempts
 * Version:     01
 * Requires at least: 	6.2
 * Requires PHP:      	7.0
 * Author:      Carmelo Andrés
 * Author URI:  https://carmeloandres.com
 * Text Domain: gesimatic-login-attempts
 * Domain Path:	/languages
 * License:     GPLv2 or later
 * License URI:       	https://www.gnu.org/licenses/gpl-2.0.html 
 * Requires Plugins: gesimatic
 */

// Prevent direct access
defined( 'ABSPATH' ) || exit;

// Plugin constants
define('GESIMATIC_LOGIN_ATTEMPTS_VERSION',23);
define('GESIMATIC_LOGIN_ATTEMPTS_PATH',plugin_dir_path(__FILE__));
define('GESIMATIC_LOGIN_ATTEMPTS_URL',plugin_dir_url(__FILE__));

/**
 * Autoload dependencies via Composer.
 */
require_once GESIMATIC_LOGIN_ATTEMPTS_PATH . 'vendor/autoload.php';

// Load plugin core logic
$gesimatic_login_attempts = new \GesimaticLoginAttempts\Core\Core();

// activate the plugin
register_activation_hook(__FILE__,[\GesimaticLoginAttempts\Core\Setup::class, 'activate']);