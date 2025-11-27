<?php
/**
 * Plugin Name: Internal Links API
 * Plugin URI: https://example.com/internal-links-api
 * Description: Expose REST API abilities for managing internal links in WordPress posts, pages, and custom post types, designed for LLM integration via MCP server.
 * Version: 1.0.0
 * Requires at least: 6.9
 * Requires PHP: 7.4
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: internal-links-api
 * Domain Path: /languages
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'INTERNAL_LINKS_API_VERSION', '1.0.0' );
define( 'INTERNAL_LINKS_API_FILE', __FILE__ );
define( 'INTERNAL_LINKS_API_PATH', plugin_dir_path( __FILE__ ) );
define( 'INTERNAL_LINKS_API_URL', plugin_dir_url( __FILE__ ) );
define( 'INTERNAL_LINKS_API_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader.
if ( file_exists( INTERNAL_LINKS_API_PATH . 'vendor/autoload.php' ) ) {
	require_once INTERNAL_LINKS_API_PATH . 'vendor/autoload.php';
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function internal_links_api_init() {
	// Check for Abilities API dependency.
	if ( ! class_exists( 'WP_Abilities_Registry' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\internal_links_api_missing_dependency_notice' );
		return;
	}

	// Initialize the plugin.
	Plugin::get_instance();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\internal_links_api_init' );

/**
 * Display admin notice when Abilities API is not active.
 *
 * @return void
 */
function internal_links_api_missing_dependency_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			esc_html_e(
				'Internal Links API requires the WordPress Abilities API plugin to be installed and activated.',
				'internal-links-api'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Activation hook.
 *
 * @return void
 */
function internal_links_api_activate() {
	// Set default options.
	if ( false === get_option( 'internal_links_api_settings' ) ) {
		add_option(
			'internal_links_api_settings',
			array(
				'post_types' => array( 'post', 'page' ),
			)
		);
	}
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\\internal_links_api_activate' );

/**
 * Deactivation hook.
 *
 * @return void
 */
function internal_links_api_deactivate() {
	// Clean up if needed.
}
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\internal_links_api_deactivate' );
