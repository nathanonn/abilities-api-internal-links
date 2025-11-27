# Installation

Until the Abilities API is merged into WordPress core, it must be installed before it can be used.

## As a plugin

The easiest way to try and use the Abilities API is to install it as a plugin by downloading the latest release from the [GitHub Releases page](https://github.com/WordPress/abilities-api/releases/latest).

### With WP-CLI

```bash
wp plugin install https://github.com/WordPress/abilities-api/releases/latest/download/abilities-api.zip
```

### With WP-Env

```jsonc
// .wp-env.json
{
  "$schema": "https://schemas.wp.org/trunk/wp-env.json",
  // ... other config ...
  "plugins": [
    "WordPress/abilities-api"
    // ... other plugins ...
  ]
  // ... more config ...
}
```

## As a dependency

Plugin authors and developers may wish to rely on the Abilities API as a dependency in their own projects, before it is merged into core. You can do that in one of the following ways.

### As a Plugin Dependency (Recommended)

The best way to ensure the Abilities API is available for your plugins is to include it as one of your `Requires Plugins` in your [Plugin header](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/). For example:

```diff
# my-plugin.php
/*
 *
 * Plugin Name:       My Plugin
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * {all the other plugin header fields...}
+ * Requires Plugins:  abilities-api
 */
```

While this is enough to ensure the Abilities API is loaded before your plugin, if you need to ensure specific version requirements or provide users guidance on installing the plugin, you can use the methods described [later on](#checking-availability-with-code)

### As a Composer dependency

```bash
composer require wordpress/abilities-api
```

## Checking availability with code

To ensure the Abilities API is loaded in your plugin:

```php
if ( ! class_exists( 'WP_Ability' ) ) {
  // E.g. add an admin notice about the missing dependency.
  add_action( 'admin_notices', static function() {
    wp_admin_notice(
      // If it's a Composer dependency, you might want to suggest running `composer install` instead.
      esc_html__( 'This plugin requires the Abilities API to use. Please install and activate it.', 'my-plugin' ),
      'error'
    );
  } );
  return;
}
```

You can also check for specific versions of the Abilities API using the `WP_ABILITIES_API_VERSION` constant:

```php
if ( ! defined( 'WP_ABILITIES_API_VERSION' ) || version_compare( WP_ABILITIES_API_VERSION, '0.1.0', '<' ) ) {
  // E.g. add an admin notice about the required version.
  add_action( 'admin_notices', static function() {
    wp_admin_notice(
      esc_html__( 'This plugin requires Abilities API version 0.1.0 or higher. Please update the plugin dependency.', 'my-plugin' ),
      'error'
    );
  } );
  return;
}
```
