# WordPress Plugin Structure Reference

## Plugin Header Format

Every WordPress plugin requires a header comment in the main PHP file:

```php
<?php
/**
 * Plugin Name: My Plugin Name
 * Plugin URI: https://example.com/my-plugin
 * Description: A brief description of what the plugin does.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Author Name
 * Author URI: https://example.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: my-plugin
 * Domain Path: /languages
 *
 * @package MyPlugin
 */
```

### Required Headers

- **Plugin Name** - The name displayed in the Plugins list
- **Version** - Current plugin version (semantic versioning recommended)

### Recommended Headers

- **Description** - Brief description (max ~140 characters for best display)
- **Author** - Plugin author name
- **License** - License identifier (GPL-2.0-or-later is standard for WordPress)
- **Text Domain** - Used for internationalization, should match plugin slug
- **Requires at least** - Minimum WordPress version
- **Requires PHP** - Minimum PHP version

## Common Directory Structures

### Simple Plugin (Single File)

```
my-plugin/
└── my-plugin.php
```

### Standard Plugin

```
my-plugin/
├── my-plugin.php           # Main plugin file
├── includes/               # PHP classes and functions
│   ├── class-main.php
│   └── functions.php
├── assets/                 # Frontend assets
│   ├── css/
│   └── js/
├── templates/              # Template files
├── languages/              # Translation files
├── README.md
└── composer.json           # (optional)
```

### Modern Plugin with PSR-4 Autoloading

```
my-plugin/
├── my-plugin.php           # Bootstrap file
├── src/                    # PSR-4 autoloaded classes
│   ├── Plugin.php          # Main plugin class
│   ├── Admin/              # Admin-related classes
│   ├── Frontend/           # Frontend-related classes
│   └── API/                # REST API endpoints
├── vendor/                 # Composer dependencies
├── assets/
├── languages/
├── composer.json
├── composer.lock
└── README.md
```

### WordPress Abilities API Plugin

```
my-abilities-plugin/
├── my-abilities-plugin.php  # Bootstrap file
├── src/
│   ├── Plugin.php           # Main plugin class (singleton)
│   ├── AbilityRegistrar.php # Registers abilities with WP Abilities API
│   ├── Settings.php         # Plugin settings
│   ├── Abilities/           # Ability classes
│   │   ├── GetDataAbility.php
│   │   └── UpdateDataAbility.php
│   ├── Services/            # Business logic services
│   │   └── DataService.php
│   ├── Schemas/             # JSON Schema definitions
│   │   ├── GetDataSchema.php
│   │   └── UpdateDataSchema.php
│   └── Errors/              # Error handling
│       ├── ErrorCodes.php
│       └── ErrorFactory.php
├── tests/                   # Unit tests
├── vendor/
├── composer.json
└── README.md
```

## Version Constant Patterns

Plugins typically define a version constant for use throughout the codebase:

```php
// Standard pattern
define( 'MY_PLUGIN_VERSION', '1.0.0' );

// With prefix matching plugin slug
define( 'MY_AWESOME_PLUGIN_VERSION', '1.0.0' );

// Common constant names
define( 'PLUGIN_NAME_VERSION', '1.0.0' );
define( 'PLUGIN_NAME_VER', '1.0.0' );
```

The constant name typically follows the pattern:
- Convert plugin slug to SCREAMING_SNAKE_CASE
- Append `_VERSION`
- Example: `internal-links-api` → `INTERNAL_LINKS_API_VERSION`

## Other Common Constants

```php
// File and path constants
define( 'MY_PLUGIN_FILE', __FILE__ );
define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
```

## PSR-4 Autoloading with Composer

Standard composer.json autoload configuration:

```json
{
  "name": "vendor/plugin-name",
  "type": "wordpress-plugin",
  "license": "GPL-2.0-or-later",
  "require": {
    "php": ">=7.4"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0"
  },
  "autoload": {
    "psr-4": {
      "MyPlugin\\": "src/"
    }
  },
  "scripts": {
    "test": "phpunit"
  }
}
```

## WordPress Hooks Pattern

Standard initialization pattern:

```php
namespace MyPlugin;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load autoloader
if ( file_exists( MY_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
    require_once MY_PLUGIN_PATH . 'vendor/autoload.php';
}

// Initialize on plugins_loaded
add_action( 'plugins_loaded', function() {
    Plugin::get_instance();
} );

// Activation/Deactivation hooks
register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate' );
```

## License Types

Common WordPress-compatible licenses:

| License | SPDX Identifier |
|---------|-----------------|
| GNU General Public License v2 | GPL-2.0-only |
| GNU General Public License v2 or later | GPL-2.0-or-later |
| GNU General Public License v3 | GPL-3.0-only |
| GNU General Public License v3 or later | GPL-3.0-or-later |
| MIT License | MIT |

Most WordPress plugins use `GPL-2.0-or-later` to maintain compatibility with WordPress core.
