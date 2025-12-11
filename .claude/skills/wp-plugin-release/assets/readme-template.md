# {{PLUGIN_NAME}}

{{PLUGIN_DESCRIPTION}}

<!-- OPTIONAL:FEATURES - Include if plugin has notable features worth highlighting -->

## Features

-   **Feature 1** - Description of feature 1
-   **Feature 2** - Description of feature 2
-   **Feature 3** - Description of feature 3
<!-- END:FEATURES -->

## Requirements

-   WordPress {{MIN_WP_VERSION}} or higher
-   PHP {{MIN_PHP_VERSION}} or higher
<!-- OPTIONAL:DEPENDENCIES - List any plugin dependencies -->
-   [Dependency Plugin](https://example.com) - Description of why needed
<!-- END:DEPENDENCIES -->

## Installation

<!-- OPTIONAL:INSTALL_DEPENDENCIES - Include if plugin has dependencies -->

### 1. Install Dependencies

First, install the required plugins:

1. **Dependency Plugin** - Download and activate from [GitHub](https://github.com/example/dependency)
 <!-- END:INSTALL_DEPENDENCIES -->

### Install {{PLUGIN_NAME}}

**Option A: From Release (Recommended)**

1. Download the latest release zip file (`{{PLUGIN_SLUG}}-latest.zip`)
2. Go to WordPress Admin > Plugins > Add New > Upload Plugin
3. Upload the zip file and click "Install Now"
4. Activate the plugin

**Option B: From Source**

1. Clone this repository to `wp-content/plugins/{{PLUGIN_SLUG}}`
 <!-- OPTIONAL:COMPOSER_INSTALL - Include if plugin uses Composer -->
2. Run composer to install dependencies:
    ```bash
    cd wp-content/plugins/{{PLUGIN_SLUG}}
    composer install
    ```
    <!-- END:COMPOSER_INSTALL -->
3. Activate the plugin in WordPress admin

<!-- OPTIONAL:MCP_SETUP - Include for Abilities API plugins with MCP integration -->

## MCP Setup Guide

The MCP Adapter bridges WordPress abilities with AI clients like Claude Desktop, Cursor, or VS Code. There are two transport methods available:

### Option A: STDIO Transport (Recommended for Local Development)

Uses WP-CLI to communicate directly with WordPress. Best for local development environments.

**Prerequisites:**

-   [WP-CLI](https://wp-cli.org/) installed and accessible in your PATH

**Configuration:**

Add this to your MCP client configuration file:

**Claude Desktop** (`~/Library/Application Support/Claude/claude_desktop_config.json` on macOS):

```json
{
    "mcpServers": {
        "wordpress": {
            "command": "wp",
            "args": ["--path=/path/to/your/wordpress", "mcp-adapter", "serve", "--server=mcp-adapter-default-server", "--user=admin"]
        }
    }
}
```

**Claude Code** (`.claude/settings.local.json` in your project or `~/.claude.json` globally):

```json
{
    "mcpServers": {
        "wordpress": {
            "command": "wp",
            "args": ["--path=/path/to/your/wordpress", "mcp-adapter", "serve", "--server=mcp-adapter-default-server", "--user=admin"]
        }
    }
}
```

Replace:

-   `/path/to/your/wordpress` with the absolute path to your WordPress installation
-   `admin` with a valid WordPress username that has appropriate permissions

### Option B: HTTP Transport (For Remote Sites)

Uses HTTP requests to communicate with WordPress. Works with any WordPress site accessible via URL.

**Prerequisites:**

-   Node.js and npm installed
-   An Application Password for your WordPress user

**Creating an Application Password:**

1. Go to WordPress Admin > Users > Profile
2. Scroll down to "Application Passwords"
3. Enter a name (e.g., "MCP Adapter") and click "Add New Application Password"
4. Copy the generated password (you won't see it again)

**Configuration:**

```json
{
    "mcpServers": {
        "wordpress-http": {
            "command": "npx",
            "args": ["-y", "@automattic/mcp-wordpress-remote@latest"],
            "env": {
                "WP_API_URL": "https://your-site.com/wp-json/mcp/mcp-adapter-default-server",
                "WP_API_USERNAME": "your-username",
                "WP_API_PASSWORD": "your-application-password"
            }
        }
    }
}
```

Replace:

-   `https://your-site.com` with your WordPress site URL
-   `your-username` with your WordPress username
-   `your-application-password` with the Application Password you created

### Verifying the Setup

After configuring your MCP client, restart it and verify the connection:

1. The MCP client should show the WordPress server as connected
2. You should see tools available from the `{{PLUGIN_SLUG}}` namespace
3. Try discovering abilities:
    ```
    Use the mcp-adapter-discover-abilities tool to list available abilities
    ```
    <!-- END:MCP_SETUP -->

<!-- OPTIONAL:ABILITIES - Include for plugins that register WordPress Abilities -->

## Available Abilities

All abilities use the `{{PLUGIN_SLUG}}` namespace.

### Read Operations

| Ability        | Description                 | Permission |
| -------------- | --------------------------- | ---------- |
| `ability-name` | Description of what it does | `read`     |

### Write Operations

| Ability        | Description                 | Permission  |
| -------------- | --------------------------- | ----------- |
| `ability-name` | Description of what it does | `edit_post` |

<!-- END:ABILITIES -->

<!-- OPTIONAL:CONFIGURATION - Include if plugin has settings or filter hooks -->

## Configuration

### Settings

Navigate to **Settings > {{PLUGIN_NAME}}** in the WordPress admin to configure:

-   **Setting 1** - Description of setting 1
-   **Setting 2** - Description of setting 2

### Filter Hooks

```php
// Example filter hook
add_filter( '{{PLUGIN_SLUG}}_example_filter', function( $value ) {
    // Modify value
    return $value;
});
```

<!-- END:CONFIGURATION -->

<!-- OPTIONAL:USAGE_EXAMPLES - Include with realistic examples for the plugin -->

## Usage Examples

Here are example use cases for {{PLUGIN_NAME}}:

### Example 1

```
Description of example usage
```

### Example 2

```
Description of another example usage
```

<!-- END:USAGE_EXAMPLES -->

<!-- OPTIONAL:TROUBLESHOOTING - Include common issues and solutions -->

## Troubleshooting

### Issue 1

Solution for issue 1.

### Issue 2

Solution for issue 2.

<!-- END:TROUBLESHOOTING -->

<!-- OPTIONAL:BUILDING - Include when build.sh is generated -->

## Building for Distribution

### Local Build

To create a standalone distributable zip file locally:

```bash
# Make sure you're in the plugin directory
cd wp-content/plugins/{{PLUGIN_SLUG}}

# Run the build script
./build.sh
```

The script will:

1. Create a clean build directory
2. Copy only the necessary plugin files
3. Install production dependencies via Composer (excludes dev dependencies)
4. Create a zip archive in the `dist/` directory

**Output files:**

-   `dist/{{PLUGIN_SLUG}}-{version}.zip` - Versioned release
-   `dist/{{PLUGIN_SLUG}}-latest.zip` - Latest release (convenience copy)

**Requirements:**

-   [Composer](https://getcomposer.org/) must be installed and available in your PATH
-   Bash shell (macOS, Linux, or WSL on Windows)

### GitHub Releases (Automated)

This repository includes a GitHub Actions workflow that automatically builds and publishes releases when you push a version tag.

**To create a new release:**

1. Update the version number in `{{MAIN_FILE}}` (both in the header and the constant)

2. Commit your changes:

    ```bash
    git add .
    git commit -m "Bump version to X.Y.Z"
    ```

3. Create and push a version tag:

    ```bash
    git tag vX.Y.Z
    git push origin main --tags
    ```

4. GitHub Actions will automatically:
    - Build the plugin
    - Create a new GitHub Release
    - Attach the zip files to the release
    - Generate release notes from commits

**View releases:** Go to your repository's "Releases" page on GitHub to download the built plugin zip files.

<!-- END:BUILDING -->

## License

{{LICENSE}}

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
